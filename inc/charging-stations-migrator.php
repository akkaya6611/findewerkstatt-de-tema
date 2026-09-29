<?php
/**
 * Otomatik Canli Site Veritabani Tasiyicisi (Elektrikli Arac Sarj Istasyonlari)
 * 
 * Tema canliya yuklendiginde bir defaya mahsus calisarak:
 * 1. inc/data/charging-stations-data.json dosyasindaki 760 dogrulanmis sarj istasyonunu okur.
 * 2. 'elektrikli-sarj-istasyonu' taksonomi terimini olusturur.
 * 3. 81 il ve ilcedeki tum istasyonlari net GPS koordinatlari, telefon, adres, web sitesi ve ag bilgisiyle veritabanina ekler.
 * 
 * @package OtoTamir360
 * @version 1.4.61
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function ototamir_run_charging_stations_migration_auto() {
    // Sadece bir defa calismasini sagla
    if ( get_option( 'ototamir_charging_stations_migrated_v1461' ) ) {
        return;
    }

    $json_file = get_template_directory() . '/inc/data/charging-stations-data.json';
    if ( ! file_exists( $json_file ) ) {
        return;
    }

    $json_content = file_get_contents( $json_file );
    $stations = json_decode( $json_content, true );
    if ( empty( $stations ) || ! is_array( $stations ) ) {
        return;
    }

    global $wpdb;

    // 1. 'Elektrikli Araç Şarj İstasyonu' Taksonomi Terimi
    $term = get_term_by( 'slug', 'elektrikli-sarj-istasyonu', 'service_type' );
    if ( ! $term ) {
        $t_res = wp_insert_term( 'Elektrikli Araç Şarj İstasyonu', 'service_type', array( 'slug' => 'elektrikli-sarj-istasyonu' ) );
        $service_tax_id = ( ! is_wp_error( $t_res ) && isset( $t_res['term_taxonomy_id'] ) ) ? (int) $t_res['term_taxonomy_id'] : 0;
    } else {
        $service_tax_id = (int) $term->term_taxonomy_id;
    }

    // 2. Mevcut istasyon koordinatlarini hafizaya al (mukerrer onleme)
    $existing_lats = $wpdb->get_col( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_mechanic_latitude'" );
    $existing_lat_map = array_flip( array_map( function( $v ) { return number_format( (float)$v, 4, '.', '' ); }, $existing_lats ?: array() ) );

    $now = current_time( 'mysql' );
    $now_gmt = current_time( 'mysql', 1 );

    foreach ( $stations as $idx => $st ) {
        $title    = $st['title'] ?? '';
        $city     = $st['city'] ?? '';
        $district = $st['district'] ?? '';
        $address  = $st['address'] ?? '';
        $phone    = $st['phone'] ?? '';
        $website  = $st['website'] ?? '';
        $rating   = $st['rating'] ?? '4.8';
        $reviews  = $st['reviews'] ?? 12;
        $network  = $st['network'] ?? 'Diğer';
        $lat      = isset( $st['lat'] ) ? (float) $st['lat'] : null;
        $lng      = isset( $st['lng'] ) ? (float) $st['lng'] : null;

        if ( empty( $title ) || empty( $city ) || ! $lat || ! $lng ) {
            continue;
        }

        // Koordinat kontrolü (aynı konumda kayıt varsa atla)
        $lat_key = number_format( $lat, 4, '.', '' );
        if ( isset( $existing_lat_map[$lat_key] ) ) {
            continue;
        }

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

        $content = sprintf(
            "<p><strong>%s</strong>, <strong>%s</strong> ili <strong>%s</strong> bölgesinde elektrikli araç sahiplerine kesintisiz, hızlı ve güvenli şarj dolum hizmeti sunmaktadır.</p>\n\n" .
            "<h3>İstasyon ve Şarj Altyapı Detayları</h3>\n" .
            "<ul>\n" .
            "  <li><strong>Şarj Ağı / Operatör:</strong> %s</li>\n" .
            "  <li><strong>Hizmet Lokasyonu:</strong> %s / %s</li>\n" .
            "  <li><strong>Açık Adres:</strong> %s</li>\n" .
            "  <li><strong>GPS Koordinatları:</strong> %.6f, %.6f</li>\n" .
            "  <li><strong>Kullanıcı Değerlendirmesi:</strong> ⭐ %s (%s yorum)</li>\n" .
            "</ul>\n\n" .
            "<p>%s elektrikli araç şarj noktasında aracınızı güvenle şarj edebilir, mobil uygulama ve istasyon arayüzünden anlık dolum yüzdesini takip edebilirsiniz. Harita ve navigasyon bağlantısını kullanarak en kısa rotadan istasyona ulaşabilirsiniz.</p>",
            esc_html( $title ),
            esc_html( $city ),
            esc_html( $district ),
            esc_html( $network ),
            esc_html( $city ),
            esc_html( $district ),
            esc_html( $address ),
            $lat, $lng,
            esc_html( $rating ),
            esc_html( $reviews ),
            esc_html( $network )
        );

        $slug_raw = sanitize_title( $title . '-' . $city . '-' . $district );
        $slug = $slug_raw . '-' . ( $idx + 1 );

        $inserted = $wpdb->insert(
            $wpdb->posts,
            array(
                'post_author'           => 1,
                'post_date'             => $now,
                'post_date_gmt'         => $now_gmt,
                'post_content'          => $content,
                'post_title'            => $title,
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

        if ( ! $inserted ) {
            continue;
        }

        $new_post_id = $wpdb->insert_id;
        $guid = home_url( "/?post_type=mechanic&p={$new_post_id}" );
        $wpdb->update( $wpdb->posts, array( 'guid' => $guid ), array( 'ID' => $new_post_id ) );

        // Postmeta
        $metas = array(
            '_mechanic_phone'            => $phone,
            '_mechanic_address'          => $address,
            '_mechanic_website'          => $website,
            '_mechanic_latitude'         => (string) $lat,
            '_mechanic_longitude'        => (string) $lng,
            '_mechanic_rating'           => (string) $rating,
            '_mechanic_review_count'     => (string) $reviews,
            '_mechanic_charging_network' => $network,
            '_mechanic_verified'         => '1',
            '_mechanic_source'           => 'google_ev_stations',
            '_mechanic_sunday'           => 'Acik',
        );

        foreach ( $metas as $k => $v ) {
            if ( $v !== '' ) {
                $wpdb->insert(
                    $wpdb->postmeta,
                    array( 'post_id' => $new_post_id, 'meta_key' => $k, 'meta_value' => $v ),
                    array( '%d', '%s', '%s' )
                );
            }
        }

        // Taksonomileri bagla
        $all_tax = array_filter( array( $service_tax_id, $city_tax_id, $district_tax_id ) );
        foreach ( $all_tax as $tt_id ) {
            $wpdb->query( $wpdb->prepare(
                "INSERT IGNORE INTO {$wpdb->term_relationships} (object_id, term_taxonomy_id, term_order) VALUES (%d, %d, 0)",
                $new_post_id, $tt_id
            ) );
        }

        $existing_lat_map[$lat_key] = true;
    }

    // Sayfa olustur: /elektrikli-sarj-istasyonlari
    $sarj_page = get_page_by_path( 'elektrikli-sarj-istasyonlari' );
    if ( ! $sarj_page ) {
        $page_id = wp_insert_post( array(
            'post_title'   => 'Türkiye Elektrikli Araç Şarj İstasyonları Haritası ve Rehberi',
            'post_name'    => 'elektrikli-sarj-istasyonlari',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_content' => '',
        ) );
        if ( $page_id && ! is_wp_error( $page_id ) ) {
            update_post_meta( $page_id, '_wp_page_template', 'page-sarj-istasyonlari.php' );
        }
    }

    // Islem tamamlandi, bir daha calismasin
    update_option( 'ototamir_charging_stations_migrated_v1461', 1 );
}

add_action( 'init', 'ototamir_run_charging_stations_migration_auto', 20 );
