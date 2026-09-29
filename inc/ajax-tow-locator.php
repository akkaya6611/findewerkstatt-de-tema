<?php
/**
 * OtoTamirciBul - Konuma Göre Çekici & Acil Yol Yardım AJAX Motoru
 * 
 * Ziyaretçinin GPS konumuna (veya tespit edilen İl/İlçe bilgisine) göre:
 * 1. O il ve ilçedeki 7/24 çekici ve yol yardım firmalarını sorgular.
 * 2. Haversine formülü ile kullanıcıya olan net kuş uçuşu kilometre mesafesini hesaplar.
 * 3. Firmaları en yakından en uzağa doğru sıralar (Aynı ilçedekileri önceliklendirir ve etiketler).
 * 4. Hem canlı harita (Leaflet) için JSON verisini hem de kart ızgarası için tam HTML'i döndürür.
 * 
 * @package OtoTamir360
 * @version 1.4.51
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Haversine İki Koordinat Arası Kilometre Mesafesi
if ( ! function_exists( 'ototamir_calc_haversine_distance' ) ) {
    function ototamir_calc_haversine_distance( $lat1, $lon1, $lat2, $lon2 ) {
        $earth_radius = 6371; // km
        $dLat = deg2rad( $lat2 - $lat1 );
        $dLon = deg2rad( $lon2 - $lon1 );
        $a = sin( $dLat / 2 ) * sin( $dLat / 2 ) +
             cos( deg2rad( $lat1 ) ) * cos( deg2rad( $lat2 ) ) *
             sin( $dLon / 2 ) * sin( $dLon / 2 );
        $c = 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );
        return $earth_radius * $c;
    }
}

// AJAX Kancaları
add_action( 'wp_ajax_ototamir_get_nearby_tows', 'ototamir_ajax_get_nearby_tows' );
add_action( 'wp_ajax_nopriv_ototamir_get_nearby_tows', 'ototamir_ajax_get_nearby_tows' );

function ototamir_ajax_get_nearby_tows() {
    // Güvenlik: Nonce doğrulaması (LiteSpeed / CDN önbellek uyumlu esnek kontrol)
    if ( ! empty( $_POST['nonce'] ) ) {
        check_ajax_referer( 'ototamir_tow_locator_nonce', 'nonce', false );
    }

    $user_lat = isset( $_POST['lat'] ) ? floatval( $_POST['lat'] ) : 0;
    $user_lng = isset( $_POST['lng'] ) ? floatval( $_POST['lng'] ) : 0;
    $city_raw = isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '';
    $district_raw = isset( $_POST['district'] ) ? sanitize_text_field( wp_unslash( $_POST['district'] ) ) : '';

    require_once get_template_directory() . '/inc/seo-geo.php';

    // 1. Önce GPS koordinatlarından en yakın ili tespit et
    $gps_city_geo = null;
    if ( $user_lat != 0 && $user_lng != 0 && function_exists( 'ototamir_get_provinces_geo_data' ) ) {
        $provinces = ototamir_get_provinces_geo_data();
        $min_d = 999999;
        foreach ( $provinces as $p ) {
            $d = ototamir_calc_haversine_distance( $user_lat, $user_lng, $p['lat'], $p['lng'] );
            if ( $d < $min_d ) {
                $min_d = $d;
                $gps_city_geo = $p;
            }
        }
    }

    // Şehir ismi temizleme
    $city_name = '';
    $city_slug = '';
    if ( ! empty( $city_raw ) ) {
        $clean_city = preg_replace( '/\s+(İli|Province|State|Valiliği)$/ui', '', trim( $city_raw ) );
        $geo_info = function_exists( 'ototamir_get_city_geo' ) ? ototamir_get_city_geo( $clean_city ) : null;
        if ( $geo_info ) {
            $city_name = $geo_info['name'];
            $city_slug = $geo_info['slug'];
        } else {
            // Eğer reverse geocode ismi çözülemediyse ama GPS varsa GPS ilini kullan
            if ( $gps_city_geo ) {
                $city_name = $gps_city_geo['name'];
                $city_slug = $gps_city_geo['slug'];
            } else {
                $city_name = $clean_city;
                $city_slug = function_exists( 'ototamir_clean_turkish_slug' ) ? ototamir_clean_turkish_slug( $clean_city ) : sanitize_title( $clean_city );
            }
        }
    } elseif ( $gps_city_geo ) {
        $city_name = $gps_city_geo['name'];
        $city_slug = $gps_city_geo['slug'];
    }

    // İlçe ismi temizleme
    $district_name = '';
    if ( ! empty( $district_raw ) ) {
        $district_name = preg_replace( '/\s+(İlçesi|Belediyesi|District)$/ui', '', trim( $district_raw ) );
    }

    // 1. Çekici sorgusu kur
    $tax_query = array(
        'relation' => 'AND',
        array(
            'taxonomy' => 'service_type',
            'field'    => 'slug',
            'terms'    => array( 'oto-cekici-yol-yardim', 'oto-kurtarma', 'yol-yardim' ),
        ),
    );

    // Eğer şehir biliniyorsa şehre göre filtrele
    if ( ! empty( $city_slug ) ) {
        $tax_query[] = array(
            'taxonomy' => 'mechanic_city',
            'field'    => 'slug',
            'terms'    => $city_slug,
        );
    }

    $args = array(
        'post_type'      => 'mechanic',
        'post_status'    => 'publish',
        'posts_per_page' => 100,
        'tax_query'      => $tax_query,
    );

    $query = new WP_Query( $args );

    // Eğer seçili şehirde hiç çekici yoksa, tüm Türkiye'den çek ve mesafeye göre sırala
    if ( ! $query->have_posts() && ! empty( $city_slug ) ) {
        $fallback_args = array(
            'post_type'      => 'mechanic',
            'post_status'    => 'publish',
            'posts_per_page' => 100,
            'tax_query'      => array(
                array(
                    'taxonomy' => 'service_type',
                    'field'    => 'slug',
                    'terms'    => array( 'oto-cekici-yol-yardim', 'oto-kurtarma', 'yol-yardim' ),
                )
            ),
        );
        $query = new WP_Query( $fallback_args );
    }

    $tows_data = array();
    $user_map_link = ( $user_lat != 0 && $user_lng != 0 ) ? sprintf( 'https://maps.google.com/?q=%.6f,%.6f', $user_lat, $user_lng ) : '';

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $m_id = get_the_ID();

            $phone = get_post_meta( $m_id, '_mechanic_phone', true );
            $clean_phone = preg_replace( '/[^0-9]/', '', $phone );
            if ( substr( $clean_phone, 0, 1 ) === '0' ) {
                $wp_phone = '90' . substr( $clean_phone, 1 );
            } elseif ( substr( $clean_phone, 0, 2 ) === '90' ) {
                $wp_phone = $clean_phone;
            } else {
                $wp_phone = '90' . $clean_phone;
            }

            $cities_terms = wp_get_post_terms( $m_id, 'mechanic_city' );
            $district_terms = wp_get_post_terms( $m_id, 'mechanic_district' );
            $loc_city = ( $cities_terms && ! is_wp_error( $cities_terms ) ) ? $cities_terms[0]->name : '';
            $loc_dist = ( $district_terms && ! is_wp_error( $district_terms ) ) ? $district_terms[0]->name : '';

            $loc_text = '';
            if ( ! empty( $loc_dist ) ) $loc_text .= $loc_dist . ', ';
            if ( ! empty( $loc_city ) ) $loc_text .= $loc_city;

            // İlçe eşleşmesi kontrolü
            $in_district = false;
            if ( ! empty( $district_name ) && ! empty( $loc_dist ) ) {
                $d_clean1 = mb_strtolower( str_replace( array('ı','İ','ğ','ü','ş','ö','ç'), array('i','i','g','u','s','o','c'), $district_name ), 'UTF-8' );
                $d_clean2 = mb_strtolower( str_replace( array('ı','İ','ğ','ü','ş','ö','ç'), array('i','i','g','u','s','o','c'), $loc_dist ), 'UTF-8' );
                if ( $d_clean1 === $d_clean2 || stripos( $d_clean2, $d_clean1 ) !== false || stripos( $d_clean1, $d_clean2 ) !== false ) {
                    $in_district = true;
                }
            }

            // Koordinatlar
            $lat = get_post_meta( $m_id, '_mechanic_latitude', true );
            $lng = get_post_meta( $m_id, '_mechanic_longitude', true );

            if ( empty( $lat ) || empty( $lng ) || ! is_numeric( $lat ) ) {
                if ( ! empty( $loc_city ) && function_exists( 'ototamir_get_city_geo' ) ) {
                    $c_geo = ototamir_get_city_geo( $loc_city );
                    if ( $c_geo ) {
                        $lat = $c_geo['lat'];
                        $lng = $c_geo['lng'];
                    }
                }
            }

            // Mesafe hesabı (Haversine formülü)
            $distance = 9999;
            if ( $user_lat != 0 && $user_lng != 0 && ! empty( $lat ) && ! empty( $lng ) ) {
                $distance = ototamir_calc_haversine_distance( $user_lat, $user_lng, floatval( $lat ), floatval( $lng ) );
            }

            $sunday = get_post_meta( $m_id, '_mechanic_sunday', true );
            $avatar_img = get_the_post_thumbnail_url( $m_id, 'thumbnail' ) ?: get_post_meta( $m_id, '_custom_mechanic_image', true );

            $tows_data[] = array(
                'id'                 => $m_id,
                'title'              => get_the_title(),
                'lat'                => ! empty( $lat ) ? floatval( $lat ) : null,
                'lng'                => ! empty( $lng ) ? floatval( $lng ) : null,
                'distance'           => round( $distance, 2 ),
                'distance_formatted' => $distance < 9000 ? round( $distance, 1 ) . ' km' : '',
                'in_district'        => $in_district,
                'city'               => $loc_city,
                'district'           => $loc_dist,
                'loc'                => $loc_text ?: 'Türkiye Geneli',
                'phone'              => $clean_phone,
                'wp_phone'           => $wp_phone,
                'avatar'             => $avatar_img,
                'sunday'             => $sunday,
                'url'                => get_permalink( $m_id ),
            );
        }
        wp_reset_postdata();
    }

    // Mesafeye göre artan sırala (En yakın kesin olarak en üstte)
    usort( $tows_data, function( $a, $b ) {
        $da = isset( $a['distance'] ) ? (float) $a['distance'] : 99999.0;
        $db = isset( $b['distance'] ) ? (float) $b['distance'] : 99999.0;
        
        // Eğer mesafeler neredeyse eşitse (< 200m), ilçe içindekini hafifçe öne al
        if ( abs( $da - $db ) < 0.2 ) {
            if ( ! empty( $a['in_district'] ) && empty( $b['in_district'] ) ) return -1;
            if ( empty( $a['in_district'] ) && ! empty( $b['in_district'] ) ) return 1;
            return 0;
        }

        return ( $da < $db ) ? -1 : 1;
    } );

    // HTML Çıktısını Oluştur
    ob_start();
    if ( ! empty( $tows_data ) ) {
        foreach ( $tows_data as $tow ) {
            $m_id         = $tow['id'];
            $clean_phone  = $tow['phone'];
            $wp_phone     = $tow['wp_phone'];
            $loc_text     = $tow['loc'];
            $dist_text    = $tow['distance_formatted'] ? $tow['distance_formatted'] . ' mesafede' : '';
            $in_district  = $tow['in_district'];
            $sunday       = $tow['sunday'];
            $avatar_img   = $tow['avatar'];
            $title        = $tow['title'];
            $lat          = $tow['lat'];
            $lng          = $tow['lng'];

            $wp_text = "Merhaba {$title}, OtoTamirciBul üzerinden ulaşıyorum. Aracım yolda kaldı, acil çekici ihtiyacım var.";
            if ( ! empty( $user_map_link ) ) {
                $wp_text .= " Bulunduğum Canlı Konum: {$user_map_link}";
            }
            $wp_text .= " Lütfen çekici fiyatı ve tahmini varış süresi verebilir misiniz?";
            ?>
            <div class="tow-card <?php echo $in_district ? 'is-in-district' : ''; ?>" 
                 data-id="<?php echo esc_attr( $m_id ); ?>"
                 data-lat="<?php echo esc_attr( $lat ); ?>"
                 data-lng="<?php echo esc_attr( $lng ); ?>"
                 data-name="<?php echo esc_attr( $title ); ?>"
                 data-phone="<?php echo esc_attr( $clean_phone ); ?>"
                 data-wp="<?php echo esc_attr( $wp_phone ); ?>"
                 data-loc="<?php echo esc_attr( $loc_text ); ?>">

                <div>
                    <!-- MESAFE ROZETİ -->
                    <?php if ( ! empty( $dist_text ) ) : ?>
                        <div class="card-distance-badge" id="badge-dist-<?php echo esc_attr( $m_id ); ?>" style="display:inline-flex;">
                            <i class="fa-solid fa-location-dot"></i> <span class="dist-val"><?php echo esc_html( $dist_text ); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="tow-card-header">
                        <div class="tow-avatar">
                            <?php if ( $avatar_img ) : ?>
                                <img src="<?php echo esc_url( $avatar_img ); ?>" alt="<?php echo esc_attr( $title ); ?>">
                            <?php else : ?>
                                <i class="fa-solid fa-truck-pickup"></i>
                            <?php endif; ?>
                        </div>

                        <div class="tow-name-wrap">
                            <h3 title="<?php echo esc_attr( $title ); ?>"><a href="<?php echo esc_url( $tow['url'] ); ?>"><?php echo esc_html( $title ); ?></a></h3>
                            <div class="tow-loc">
                                <i class="fa-solid fa-location-dot" style="color:#ef4444;"></i>
                                <span><?php echo esc_html( $loc_text ?: 'Türkiye Geneli' ); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="tow-features">
                        <span class="tow-pill red"><i class="fa-solid fa-clock"></i> 7/24 Acil Yol Yardım</span>
                        <?php if ( $in_district ) : ?>
                            <span class="tow-pill in-district" style="background:#dcfce7; color:#15803d; border-color:#86efac; font-weight:700;"><i class="fa-solid fa-map-pin"></i> Bulunduğunuz İlçede</span>
                        <?php endif; ?>
                        <?php if ( in_array( $sunday, array( 'Evet', 'yes', '1' ) ) ) : ?>
                            <span class="tow-pill green"><i class="fa-solid fa-calendar-check"></i> Pazar Açık</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="tow-actions">
                    <?php if ( ! empty( $clean_phone ) ) : ?>
                        <a href="tel:<?php echo esc_attr( $clean_phone ); ?>" class="tow-btn-call">
                            <i class="fa-solid fa-phone-volume"></i>
                            <span>Hemen Ara</span>
                        </a>
                        
                        <a href="https://api.whatsapp.com/send?phone=<?php echo esc_attr( $wp_phone ); ?>&text=<?php echo rawurlencode( $wp_text ); ?>" 
                           target="_blank" 
                           rel="noopener" 
                           class="tow-btn-wp js-tow-wp" 
                           data-phone="<?php echo esc_attr( $wp_phone ); ?>" 
                           data-mechanic="<?php echo esc_attr( $title ); ?>"
                           style="background:#047857;">
                            <i class="fa-brands fa-whatsapp" style="font-size:16px;"></i>
                            <span>Konum At</span>
                        </a>
                    <?php else : ?>
                        <a href="<?php echo esc_url( $tow['url'] ); ?>" class="tow-btn-call" style="grid-column: span 2;">
                            <span>Profili & İletişimi Gör</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
    } else {
        ?>
        <div style="grid-column: 1 / -1; background:white; border-radius:16px; padding:40px; text-align:center; border:1px solid #e2e8f0;">
            <i class="fa-solid fa-triangle-exclamation" style="font-size:36px; color:#f59e0b; margin-bottom:12px;"></i>
            <h3 style="margin:0 0 8px; color:#0f172a;">Bu Bölgede Henüz Kayıtlı Çekici Bulunmuyor</h3>
            <p style="color:#64748b; margin:0 0 16px;">Diğer illerdeki 7/24 yol yardım ekiplerini görüntülemek için şehir filtresini sıfırlayabilirsiniz.</p>
        </div>
        <?php
    }
    $rendered_html = ob_get_clean();

    wp_send_json_success( array(
        'city'        => $city_name,
        'city_slug'   => $city_slug,
        'district'    => $district_name,
        'count'       => count( $tows_data ),
        'tows'        => $tows_data,
        'html'        => $rendered_html,
    ) );
}
