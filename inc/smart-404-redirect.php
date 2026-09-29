<?php
/**
 * OtoTamirciBul - Akıllı 404 ve Eski Tema URL 301 Kalıcı Yönlendirme Motoru
 * 
 * Amaç:
 * Tema veya URL yapısı değişiminden kaynaklanan ve Google Search Console / Canlı Ziyaretçi
 * kayıtlarında görülen tüm 404 (Sayfa Bulunamadı) hatalarını otomatik olarak analiz eder:
 * 1. Eski /firma/{slug}, /mechanic/{slug}, /tamirci/{slug} linklerini yeni /yol-yardim/ veya /oto-tamirci/ linkine 301 ile yönlendirir.
 * 2. Eski SEO sayfalarını (örn: /mus-ili-varto-ilcesinde-oto..., /malatya-ilinde-hizmet-veren...) ilgili il ve ilçe sayfalarına 301 ile aktarır.
 * 3. Eski taksonomi ve kategori linklerini (mechanic_city, service_type, sehir, il, ilce, firmalar, rehber) karşılar.
 * 4. Silinmiş veya değişmiş firma slug'larında il/ilçe adını ayıklayarak ziyaretçiyi ve Google botunu doğru şehir listesine ulaştırır.
 * 
 * @package OtoTamir360
 * @version 1.4.60
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OtoTamir_Smart_404_Redirect {

    public static function init() {
        // template_redirect kancasında en erken öncelikte (priority 1) çalıştır
        add_action( 'template_redirect', array( __CLASS__, 'handle_redirects' ), 1 );
    }

    public static function handle_redirects() {
        if ( is_admin() || wp_doing_ajax() ) {
            return;
        }

        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $path = trim( parse_url( $request_uri, PHP_URL_PATH ), '/' );

        if ( empty( $path ) ) {
            return;
        }

        global $wpdb;
        require_once get_template_directory() . '/inc/seo-geo.php';

        // -------------------------------------------------------------
        // 1. ESKİ FİRMA VE USTA LİNKLERİ (/firma/, /firmalar/, /mechanic/, /tamirci/, /usta/)
        // -------------------------------------------------------------
        if ( preg_match( '#^(?:firma|firmalar|mechanic|tamirci|usta)/([^/]+)/?$#i', $path, $matches ) ) {
            $slug = sanitize_title( $matches[1] );
            
            // A) Birebir post_name eşleşmesi ara
            $post_id = $wpdb->get_var( $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'mechanic' AND post_status = 'publish' AND post_name = %s LIMIT 1",
                $slug
            ) );

            // B) Bulunamadıysa, slug başından benzerlik ara (örn: sonundaki rastgele id veya sehir eki degismis olabilir)
            if ( ! $post_id ) {
                $clean_prefix = preg_replace( '/-[0-9]{3,6}$/', '', $slug );
                $post_id = $wpdb->get_var( $wpdb->prepare(
                    "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'mechanic' AND post_status = 'publish' AND (post_name LIKE %s OR post_name LIKE %s) LIMIT 1",
                    $clean_prefix . '%',
                    '%' . $clean_prefix . '%'
                ) );
            }

            if ( $post_id ) {
                $target_url = get_permalink( $post_id );
                if ( $target_url ) {
                    wp_safe_redirect( $target_url, 301 );
                    exit;
                }
            }

            // C) Firma tamamen silinmişse dahi slug içindeki il/ilçeyi yakala ve o şehrin ustalarına yönlendir
            $detected_city = self::detect_city_from_text( $slug );
            if ( $detected_city ) {
                $redirect_url = home_url( '/ustalar/?mechanic_city=' . urlencode( $detected_city['slug'] ) );
                wp_safe_redirect( $redirect_url, 301 );
                exit;
            }

            // D) Hiçbir şey bulunamazsa ana usta listesine aktar
            wp_safe_redirect( home_url( '/ustalar/' ), 301 );
            exit;
        }

        // -------------------------------------------------------------
        // 2. ESKİ ŞEHİR & İLÇE SEO SAYFALARI
        // Örn: /mus-ili-varto-ilcesinde-oto-tamirciler/
        // Örn: /malatya-ilinde-hizmet-veren-oto-tamirciler/
        // Örn: /istanbul-oto-tamirci/
        // -------------------------------------------------------------
        if ( preg_match( '#^([a-z0-9\-]+)-(?:ili|ilinde)(?:-([a-z0-9\-]+)-ilcesinde)?.*#ui', $path, $matches ) ) {
            $raw_city = $matches[1] ?? '';
            $raw_dist = $matches[2] ?? '';

            $is_tow = ( stripos( $path, 'cekici' ) !== false || stripos( $path, 'kurtarma' ) !== false || stripos( $path, 'yol-yardim' ) !== false );
            $base_url = $is_tow ? home_url( '/oto-cekici-yol-yardim/' ) : home_url( '/ustalar/' );

            $city_geo = function_exists( 'ototamir_get_city_geo' ) ? ototamir_get_city_geo( $raw_city ) : null;
            $city_param = $city_geo ? $city_geo['slug'] : ( function_exists( 'ototamir_clean_turkish_slug' ) ? ototamir_clean_turkish_slug( $raw_city ) : sanitize_title( $raw_city ) );

            $args = array( 'mechanic_city' => $city_param );
            if ( ! empty( $raw_dist ) ) {
                $args['mechanic_district'] = ucfirst( $raw_dist );
            }

            $target_url = add_query_arg( $args, $base_url );
            wp_safe_redirect( $target_url, 301 );
            exit;
        }

        // -------------------------------------------------------------
        // 3. ESKİ TAKSONOMİ VE KATEGORİ URL'LERİ
        // -------------------------------------------------------------
        // Şehir taksonomileri: /mechanic_city/{sehir}/ veya /sehir/{sehir}/ veya /il/{sehir}/
        if ( preg_match( '#^(?:mechanic_city|sehir|il)/([^/]+)/?$#i', $path, $matches ) ) {
            $c_slug = function_exists( 'ototamir_clean_turkish_slug' ) ? ototamir_clean_turkish_slug( $matches[1] ) : sanitize_title( $matches[1] );
            wp_safe_redirect( home_url( '/ustalar/?mechanic_city=' . urlencode( $c_slug ) ), 301 );
            exit;
        }

        // İlçe taksonomileri: /mechanic_district/{ilce}/ veya /ilce/{ilce}/
        if ( preg_match( '#^(?:mechanic_district|ilce)/([^/]+)/?$#i', $path, $matches ) ) {
            wp_safe_redirect( home_url( '/ustalar/?district=' . urlencode( $matches[1] ) ), 301 );
            exit;
        }

        // Hizmet türü taksonomileri: /service_type/{hizmet}/ veya /hizmet/{hizmet}/
        if ( preg_match( '#^(?:service_type|hizmet|kategori)/([^/]+)/?$#i', $path, $matches ) ) {
            $srv = sanitize_title( $matches[1] );
            if ( in_array( $srv, array( 'oto-cekici-yol-yardim', 'oto-kurtarma', 'yol-yardim', 'cekici' ), true ) ) {
                wp_safe_redirect( home_url( '/oto-cekici-yol-yardim/' ), 301 );
            } else {
                wp_safe_redirect( home_url( '/ustalar/?service=' . urlencode( $srv ) ), 301 );
            }
            exit;
        }

        // Araç markası: /car_brand/{marka}/ -> /marka/{marka}/
        if ( preg_match( '#^car_brand/([^/]+)/?$#i', $path, $matches ) ) {
            wp_safe_redirect( home_url( '/marka/' . sanitize_title( $matches[1] ) . '/' ), 301 );
            exit;
        }

        // Eski Genel Sayfa Alias'ları
        if ( in_array( $path, array( 'firmalar', 'tamirciler', 'rehber', 'oto-tamirciler', 'oto-servisler' ), true ) ) {
            wp_safe_redirect( home_url( '/ustalar/' ), 301 );
            exit;
        }

        if ( in_array( $path, array( 'cekici', 'oto-cekici', 'oto-kurtarma', 'yol-yardim' ), true ) ) {
            wp_safe_redirect( home_url( '/oto-cekici-yol-yardim/' ), 301 );
            exit;
        }

        // -------------------------------------------------------------
        // 4. GENEL 404 KONTROLÜ VE AKILLI İÇERİK EŞLEŞTİRME
        // -------------------------------------------------------------
        if ( is_404() ) {
            // A) URL'nin son segmenti bir mechanic post_name'i olabilir mi?
            $segments = explode( '/', $path );
            $last_segment = end( $segments );
            if ( ! empty( $last_segment ) ) {
                $clean_last = sanitize_title( $last_segment );
                $found_id = $wpdb->get_var( $wpdb->prepare(
                    "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'mechanic' AND post_status = 'publish' AND post_name = %s LIMIT 1",
                    $clean_last
                ) );
                if ( $found_id ) {
                    wp_safe_redirect( get_permalink( $found_id ), 301 );
                    exit;
                }
            }

            // B) URL içinde Türkiye illerinden biri geçiyor mu?
            $detected_city = self::detect_city_from_text( $path );
            if ( $detected_city ) {
                $is_tow = ( stripos( $path, 'cekici' ) !== false || stripos( $path, 'kurtarma' ) !== false || stripos( $path, 'yol-yardim' ) !== false );
                $target = $is_tow ? home_url( '/oto-cekici-yol-yardim/?mechanic_city=' . $detected_city['slug'] ) : home_url( '/ustalar/?mechanic_city=' . $detected_city['slug'] );
                wp_safe_redirect( $target, 301 );
                exit;
            }

            // C) Marka geçiyor mu? (Tam kelime/segment kontrolü: "audit" asla "audi" ile eşleşmemeli)
            $brands = array( 'fiat', 'renault', 'ford', 'volkswagen', 'toyota', 'hyundai', 'opel', 'peugeot', 'honda', 'bmw', 'mercedes-benz', 'audi', 'citroen', 'dacia', 'skoda', 'seat', 'kia', 'nissan', 'chevrolet', 'volvo' );
            $segments = explode( '/', $path );
            $all_tokens = array();
            foreach ( $segments as $seg ) {
                $all_tokens = array_merge( $all_tokens, explode( '-', $seg ) );
            }
            $all_tokens = array_map( 'strtolower', $all_tokens );

            foreach ( $brands as $b ) {
                if ( in_array( $b, $all_tokens, true ) ) {
                    wp_safe_redirect( home_url( '/marka/' . $b . '/' ), 301 );
                    exit;
                }
            }

            // D) Eğer geçerli bir eski URL veya şehir/marka eşleşmesi yoksa, 
            // zoraki 301 Soft-404 yönlendirmesi YAPMA! Ziyaretçiye ve Google'a temiz 404 sayfası sun.
            return;
        }
    }

    /**
     * Verilen metin veya slug içerisinden Türkiye'nin 81 ilini güvenle tespit eder
     */
    private static function detect_city_from_text( $text ) {
        if ( empty( $text ) || ! function_exists( 'ototamir_get_provinces_geo_data' ) ) {
            return null;
        }

        $provinces = ototamir_get_provinces_geo_data();
        $clean_text = '-' . trim( sanitize_title( $text ), '-' ) . '-';

        // Uzun isimlerden kısaya doğru ara (Örn: Afyonkarahisar önce, Afyon sonra)
        usort( $provinces, function( $a, $b ) {
            return strlen( $b['slug'] ) - strlen( $a['slug'] );
        } );

        foreach ( $provinces as $p ) {
            if ( strpos( $clean_text, '-' . $p['slug'] . '-' ) !== false ) {
                return $p;
            }
        }

        return null;
    }
}

OtoTamir_Smart_404_Redirect::init();
