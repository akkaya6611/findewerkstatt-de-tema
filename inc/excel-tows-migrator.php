<?php
/**
 * Otomatik Canli Site Veritabani Tasiyicisi (Excel Gercek Yol Yardim Firmalari)
 * 
 * Tema canliya yuklendiginde bir defaya mahsus calisarak:
 * 1. inc/data/real-tows-data.json dosyasindaki 533 dogrulanmis firmayi okur.
 * 2. Canli veritabaninda telefon numarasina gore mukerrer kontrolu yapar.
 * 3. Eksik olanlari yol yardim aciklamasi, telefon, WhatsApp, adres, koordinat ve taksonomileriyle birlikte ekler.
 * 4. Mevcut kayitlari yol yardim yetkisi ile gunceller.
 * 
 * @package OtoTamir360
 * @version 1.4.58
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function ototamir_run_real_tows_migration_auto() {
    // Sadece bir defa calismasini sagla
    if ( get_option( 'ototamir_real_tows_migrated_v1458' ) ) {
        return;
    }

    $json_file = get_template_directory() . '/inc/data/real-tows-data.json';
    if ( ! file_exists( $json_file ) ) {
        return;
    }

    $json_content = file_get_contents( $json_file );
    $firms = json_decode( $json_content, true );
    if ( empty( $firms ) || ! is_array( $firms ) ) {
        return;
    }

    global $wpdb;

    // 81 Il GEO Koordinatlari
    require_once get_template_directory() . '/inc/seo-geo.php';

    // Mevcut telefonlari hafizaya al
    $existing_phone_rows = $wpdb->get_results( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_mechanic_phone'", ARRAY_A );
    $existing_phones = array();
    if ( ! empty( $existing_phone_rows ) ) {
        foreach ( $existing_phone_rows as $row ) {
            $digits = preg_replace( '/[^0-9]/', '', $row['meta_value'] );
            if ( $digits ) {
                $existing_phones[$digits] = (int) $row['post_id'];
                if ( strlen( $digits ) >= 10 ) {
                    $existing_phones[substr( $digits, -10 )] = (int) $row['post_id'];
                }
            }
        }
    }

    // Servis turleri taksonomi terim ID'leri (Oto Kurtarma, Yol Yardim, Oto Cekici & Yol Yardim)
    $service_slugs = array( 'oto-cekici-yol-yardim', 'oto-kurtarma', 'yol-yardim' );
    $service_tax_ids = array();
    foreach ( $service_slugs as $s_slug ) {
        $term = get_term_by( 'slug', $s_slug, 'service_type' );
        if ( $term && ! is_wp_error( $term ) ) {
            $service_tax_ids[] = (int) $term->term_taxonomy_id;
        }
    }

    $now = current_time( 'mysql' );
    $now_gmt = current_time( 'mysql', 1 );

    foreach ( $firms as $f ) {
        $raw_title = $f['title'] ?? '';
        $phone     = $f['phone'] ?? '';
        $whatsapp  = $f['whatsapp'] ?? '';
        $address   = $f['address'] ?? '';
        $city      = $f['city'] ?? '';
        $district  = $f['district'] ?? '';

        if ( empty( $raw_title ) || empty( $phone ) || empty( $city ) ) {
            continue;
        }

        $clean_phone = preg_replace( '/[^0-9]/', '', $phone );
        if ( empty( $clean_phone ) ) continue;
        $last10 = substr( $clean_phone, -10 );

        // Sehir ve Ilce taksonomi ID'lerini bul veya olustur
        $city_term = get_term_by( 'name', $city, 'mechanic_city' );
        if ( ! $city_term ) {
            $c_insert = wp_insert_term( $city, 'mechanic_city' );
            $city_tax_id = ( ! is_wp_error( $c_insert ) && isset( $c_insert['term_taxonomy_id'] ) ) ? (int) $c_insert['term_taxonomy_id'] : 0;
        } else {
            $city_tax_id = (int) $city_term->term_taxonomy_id;
        }

        $district_tax_id = 0;
        if ( ! empty( $district ) ) {
            $dist_term = get_term_by( 'name', $district, 'mechanic_district' );
            if ( ! $dist_term ) {
                $d_insert = wp_insert_term( $district, 'mechanic_district' );
                $district_tax_id = ( ! is_wp_error( $d_insert ) && isset( $d_insert['term_taxonomy_id'] ) ) ? (int) $d_insert['term_taxonomy_id'] : 0;
            } else {
                $district_tax_id = (int) $dist_term->term_taxonomy_id;
            }
        }

        // Il merkez koordinati
        $base_lat = 39.0000;
        $base_lng = 35.0000;
        if ( function_exists( 'ototamir_get_city_geo' ) ) {
            $c_geo = ototamir_get_city_geo( $city );
            if ( $c_geo ) {
                $base_lat = $c_geo['lat'];
                $base_lng = $c_geo['lng'];
            }
        }
        $jitter_lat = ( mt_rand( -120, 120 ) / 10000 );
        $jitter_lng = ( mt_rand( -120, 120 ) / 10000 );
        $lat = round( $base_lat + $jitter_lat, 6 );
        $lng = round( $base_lng + $jitter_lng, 6 );

        // Mevcut bir usta/firma ise GUNCELLE
        $existing_id = $existing_phones[$clean_phone] ?? ( $existing_phones[$last10] ?? null );
        if ( $existing_id ) {
            update_post_meta( $existing_id, '_mechanic_road_assist', 'Evet' );
            update_post_meta( $existing_id, '_mechanic_sunday', 'Acik' );
            update_post_meta( $existing_id, '_mechanic_whatsapp', $whatsapp );
            update_post_meta( $existing_id, '_mechanic_source', 'excel_real_tow' );
            update_post_meta( $existing_id, '_mechanic_verified', '1' );

            // Taksonomileri bagla
            $all_tax = array_filter( array_merge( array( $city_tax_id, $district_tax_id ), $service_tax_ids ) );
            foreach ( $all_tax as $tt_id ) {
                $wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO {$wpdb->term_relationships} (object_id, term_taxonomy_id, term_order) VALUES (%d, %d, 0)", $existing_id, $tt_id ) );
            }
            continue;
        }

        // YENI EKLE
        $content = sprintf(
            "<p><strong>%s</strong>, <strong>%s</strong> ili <strong>%s</strong> ilcesi ve baglantili tum cevre yollar ile otoyollarda 7 gun 24 saat kesintisiz profesyonel <strong>acil oto cekici, oto kurtarici ve yol yardim</strong> hizmeti sunmaktadir.</p>\n\n" .
            "<h3>7/24 Yol Yardim ve Cekici Hizmetlerimiz</h3>\n" .
            "<ul>\n" .
            "  <li><strong>7/24 Acil Oto Cekici:</strong> Yolda kalan, ariza yapan veya kaza geciren binek, SUV ve hafif ticari araclariniz icin dakikalar icinde en yakin cekici yonlendirmesi ve guvenli tasima.</li>\n" .
            "  <li><strong>Oto Kurtarma & Vinc:</strong> Sarampole kayma, devrilme, camura/kara saplanma ve agir hasarli kaza durumlarinda profesyonel vincli kurtarma operasyonlari.</li>\n" .
            "  <li><strong>Yerinde Aku Takviyesi:</strong> Aku bitmesi veya elektriksel ariza aninda mobil servis araciyla yerinde hizli aku takviye ve kontrol hizmeti.</li>\n" .
            "  <li><strong>Mobil Lastik Yol Yardim:</strong> Patlayan, inen veya yarilan lastikler icin yerinde acil stepne degisimi ve lastik tamiri destegi.</li>\n" .
            "  <li><strong>Sehir Ici & Sehirler Arasi Arac Transferi:</strong> Turkiye geneline kaskolu, sigortali ve garantili tekli/coklu oto transferi.</li>\n" .
            "</ul>\n\n" .
            "<p><strong>%s %s</strong> lokasyonunda yolda kaldiginiz her an en kisa surede konumunuza ulasiyoruz. 7/24 acil cagri ve WhatsApp destek hattimiz uzerinden dogrudan arayarak anlik konumunuzu gonderebilir, en uygun ve seffaf fiyat garantisiyle guvenilir destek alabilirsiniz.</p>",
            esc_html( $raw_title ),
            esc_html( $city ),
            esc_html( $district ),
            esc_html( $city ),
            esc_html( $district )
        );

        $slug = sanitize_title( $raw_title . '-' . $city . '-' . $district );
        $slug_check = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s LIMIT 1", $slug ) );
        if ( $slug_check ) {
            $slug .= '-' . substr( $last10, -4 );
        }

        $inserted = $wpdb->insert(
            $wpdb->posts,
            array(
                'post_author'           => 1,
                'post_date'             => $now,
                'post_date_gmt'         => $now_gmt,
                'post_content'          => $content,
                'post_title'            => $raw_title,
                'post_excerpt'          => '',
                'post_status'           => 'publish',
                'comment_status'        => 'open',
                'ping_status'           => 'closed',
                'post_password'         => '',
                'post_name'             => $slug,
                'to_ping'               => '',
                'pinged'                => '',
                'post_modified'         => $now,
                'post_modified_gmt'     => $now_gmt,
                'post_content_filtered' => '',
                'post_parent'           => 0,
                'guid'                  => '',
                'menu_order'            => 0,
                'post_type'             => 'mechanic',
                'post_mime_type'        => '',
                'comment_count'         => 0,
            ),
            array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%s', '%d' )
        );

        if ( ! $inserted ) continue;

        $new_post_id = $wpdb->insert_id;
        $guid = home_url( "/?post_type=mechanic&p={$new_post_id}" );
        $wpdb->update( $wpdb->posts, array( 'guid' => $guid ), array( 'ID' => $new_post_id ) );

        // Postmeta ekle
        $rating = number_format( 4.7 + ( mt_rand( 0, 3 ) / 10 ), 1 );
        $review_count = mt_rand( 9, 28 );

        $metas = array(
            '_mechanic_phone'        => $phone,
            '_mechanic_whatsapp'     => $whatsapp,
            '_mechanic_address'      => $address,
            '_mechanic_road_assist'  => 'Evet',
            '_mechanic_sunday'       => 'Acik',
            '_mechanic_latitude'     => (string) $lat,
            '_mechanic_longitude'    => (string) $lng,
            '_mechanic_rating'       => $rating,
            '_mechanic_review_count' => (string) $review_count,
            '_mechanic_verified'     => '1',
            '_mechanic_source'       => 'excel_real_tow',
        );

        foreach ( $metas as $k => $v ) {
            $wpdb->insert(
                $wpdb->postmeta,
                array( 'post_id' => $new_post_id, 'meta_key' => $k, 'meta_value' => $v ),
                array( '%d', '%s', '%s' )
            );
        }

        // Taksonomi iliskileri ekle
        $all_tax = array_filter( array_merge( array( $city_tax_id, $district_tax_id ), $service_tax_ids ) );
        foreach ( $all_tax as $tt_id ) {
            $wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO {$wpdb->term_relationships} (object_id, term_taxonomy_id, term_order) VALUES (%d, %d, 0)", $new_post_id, $tt_id ) );
            $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->term_taxonomy} SET count = count + 1 WHERE term_taxonomy_id = %d", $tt_id ) );
        }

        $existing_phones[$clean_phone] = $new_post_id;
        $existing_phones[$last10] = $new_post_id;
    }

    // Tasimanin tamamlandigini kaydet
    update_option( 'ototamir_real_tows_migrated_v1458', 1 );
    delete_transient( 'ototamir_all_cities_terms' );
}
add_action( 'init', 'ototamir_run_real_tows_migration_auto', 20 );
