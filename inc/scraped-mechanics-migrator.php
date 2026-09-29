<?php
/**
 * Otomatik Canlı Site Veritabanı Taşıyıcısı (2.400+ Gerçek Oto Tamirci & Servis)
 * 
 * Tema canlıya yüklendiğinde bir defaya mahsus çalışarak:
 * 1. inc/data/scraped-mechanics-data.json dosyasındaki 2.423 doğrulanmış oto tamirciyi okur.
 * 2. 52 il ve 122 ilçedeki taksonomileri ve servis kategorilerini hazırlar.
 * 3. Düşük boyutlu (s200-c) profil fotoğrafları, GPS koordinatları, puan ve telefonları veritabanına ekler.
 * 
 * @package OtoTamir360
 * @version 1.4.63
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function ototamir_run_scraped_mechanics_migration_auto() {
    // Sadece bir defa çalışmasını sağla
    if ( get_option( 'ototamir_scraped_mechanics_migrated_v1463' ) ) {
        return;
    }

    $json_file = get_template_directory() . '/inc/data/scraped-mechanics-data.json';
    if ( ! file_exists( $json_file ) ) {
        return;
    }

    $json_content = file_get_contents( $json_file );
    $mechanics = json_decode( $json_content, true );
    if ( empty( $mechanics ) || ! is_array( $mechanics ) ) {
        return;
    }

    global $wpdb;

    // 1. Mevcut mekanik başlık ve koordinatlarını hafızaya al (mükerrer önleme)
    $existing_posts = $wpdb->get_col( "SELECT post_name FROM {$wpdb->posts} WHERE post_type = 'mechanic'" );
    $existing_slug_map = array_flip( $existing_posts ?: array() );

    $existing_lats = $wpdb->get_col( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_mechanic_latitude'" );
    $existing_lat_map = array_flip( array_map( function( $v ) { return number_format( (float)$v, 4, '.', '' ); }, $existing_lats ?: array() ) );

    // 2. Taksonomi önbelleği oluştur
    $tax_cache = array();
    $get_or_create_term = function( $name, $slug, $taxonomy ) use ( &$tax_cache ) {
        $cache_key = $taxonomy . ':' . $slug;
        if ( isset( $tax_cache[$cache_key] ) ) {
            return $tax_cache[$cache_key];
        }

        $term = get_term_by( 'slug', $slug, $taxonomy );
        if ( ! $term ) {
            $inserted = wp_insert_term( $name, $taxonomy, array( 'slug' => $slug ) );
            if ( ! is_wp_error( $inserted ) && isset( $inserted['term_taxonomy_id'] ) ) {
                $tt_id = (int) $inserted['term_taxonomy_id'];
                $tax_cache[$cache_key] = $tt_id;
                return $tt_id;
            }
            return 0;
        }

        $tt_id = (int) $term->term_taxonomy_id;
        $tax_cache[$cache_key] = $tt_id;
        return $tt_id;
    };

    $now = current_time( 'mysql' );
    $now_gmt = current_time( 'mysql', 1 );

    foreach ( $mechanics as $idx => $m ) {
        $title     = $m['title'] ?? '';
        $slug      = $m['slug'] ?? '';
        $city      = $m['city'] ?? '';
        $city_slug = $m['city_slug'] ?? '';
        $district  = $m['district'] ?? '';
        $dist_slug = $m['district_slug'] ?? '';
        $address   = $m['address'] ?? '';
        $phone     = $m['phone'] ?? '';
        $website   = $m['website'] ?? '';
        $image     = $m['image'] ?? '';
        $rating    = $m['rating'] ?? '4.8';
        $reviews   = $m['reviews'] ?? '15';
        $lat       = (float) ( $m['lat'] ?? 0 );
        $lng       = (float) ( $m['lng'] ?? 0 );
        $srv_slug  = $m['service_slug'] ?? 'mekanik-ustasi';
        $srv_name  = $m['service_name'] ?? 'Mekanik Ustaları';

        if ( ! $title || ! $city || ! $lat || ! $lng ) {
            continue;
        }

        // Mükerrer kontrolü
        $lat_key = number_format( $lat, 4, '.', '' );
        if ( isset( $existing_lat_map[$lat_key] ) || isset( $existing_slug_map[$slug] ) ) {
            continue;
        }

        // Taksonomi ID'lerini al/oluştur
        $service_tax_id  = $get_or_create_term( $srv_name, $srv_slug, 'service_type' );
        $city_tax_id     = $get_or_create_term( $city, $city_slug, 'mechanic_city' );
        $district_tax_id = $district ? $get_or_create_term( $district, $dist_slug, 'mechanic_district' ) : 0;

        $desc = "{$title}, {$district}, {$city} bölgesinde profesyonel oto tamir, bakım ve servis hizmeti sunmaktadır. Müşteri puanı: {$rating}/5. İletişim: {$phone}.";

        // Post ekle
        $inserted = $wpdb->insert(
            $wpdb->posts,
            array(
                'post_author'           => 1,
                'post_date'             => $now,
                'post_date_gmt'         => $now_gmt,
                'post_content'          => $desc,
                'post_title'            => $title,
                'post_excerpt'          => '',
                'post_status'           => 'publish',
                'comment_status'        => 'open',
                'ping_status'           => 'closed',
                'post_name'             => $slug,
                'post_modified'         => $now,
                'post_modified_gmt'     => $now_gmt,
                'post_type'             => 'mechanic',
            ),
            array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
        );

        if ( ! $inserted ) {
            continue;
        }

        $new_post_id = $wpdb->insert_id;
        $guid = home_url( "/?post_type=mechanic&p={$new_post_id}" );
        $wpdb->update( $wpdb->posts, array( 'guid' => $guid ), array( 'ID' => $new_post_id ) );

        // Postmeta (Özellikle en düşük boyutlu s200-c profil resmi)
        $metas = array(
            '_mechanic_phone'         => $phone,
            '_mechanic_address'       => $address,
            '_mechanic_website'       => $website,
            '_custom_mechanic_image'  => $image,
            '_mechanic_rating'        => (string) $rating,
            '_mechanic_review_count'  => (string) $reviews,
            '_mechanic_latitude'      => (string) $lat,
            '_mechanic_longitude'     => (string) $lng,
            '_mechanic_verified'      => '1',
            '_mechanic_source'        => 'google_places',
        );

        if ( $srv_slug === 'oto-cekici-yol-yardim' ) {
            $metas['_mechanic_road_assist'] = 'yes';
        }

        foreach ( $metas as $k => $v ) {
            if ( $v !== '' ) {
                $wpdb->insert(
                    $wpdb->postmeta,
                    array( 'post_id' => $new_post_id, 'meta_key' => $k, 'meta_value' => $v ),
                    array( '%d', '%s', '%s' )
                );
            }
        }

        // Taksonomi ilişkileri
        $all_tax = array_filter( array( $service_tax_id, $city_tax_id, $district_tax_id ) );
        foreach ( $all_tax as $tt_id ) {
            $wpdb->query( $wpdb->prepare(
                "INSERT IGNORE INTO {$wpdb->term_relationships} (object_id, term_taxonomy_id, term_order) VALUES (%d, %d, 0)",
                $new_post_id, $tt_id
            ) );
        }

        $existing_lat_map[$lat_key] = true;
        $existing_slug_map[$slug] = true;
    }

    // İşlem tamamlandı, bir daha çalışmasın
    update_option( 'ototamir_scraped_mechanics_migrated_v1463', 1 );
}

add_action( 'init', 'ototamir_run_scraped_mechanics_migration_auto', 25 );
