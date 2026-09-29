<?php
/**
 * OtoTamirciBul - Guvenlik Basliklari, HTTPS Zorlama, Onbellek Korumasi & Tekil Canonical
 * 
 * @package OtoTamir360
 * @version 1.5.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OtoTamir_Security_And_Cache_Manager {

    public static function init() {
        // 1. HTTP -> HTTPS Zorlamasi
        add_action( 'template_redirect', array( __CLASS__, 'enforce_https_redirect' ), 1 );

        // 2. HTTP Guvenlik Basliklari
        add_action( 'send_headers', array( __CLASS__, 'send_security_headers' ) );

        // 3. Giris/Kayit/Hesap Sayfalarinda No-Cache
        add_action( 'template_redirect', array( __CLASS__, 'prevent_auth_page_caching' ), 2 );

        // 4. Cift Canonical Etiketi Temizligi ve Standartlastirilmasi
        remove_action( 'wp_head', 'rel_canonical' );
        add_action( 'wp_head', array( __CLASS__, 'render_unified_canonical' ), 5 );
    }

    public static function enforce_https_redirect() {
        if ( is_ssl() ) {
            return;
        }

        $host = $_SERVER['HTTP_HOST'] ?? '';

        // Yerel geliştirme ortamlarında (localhost, 127.0.0.1, .local, .test) HTTPS zorlamasını devre dışı bırak
        if ( empty( $host ) || strpos( $host, 'localhost' ) !== false || strpos( $host, '127.0.0.1' ) !== false || strpos( $host, '.local' ) !== false || strpos( $host, '.test' ) !== false ) {
            return;
        }

        if (
            ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ||
            ( isset( $_SERVER['HTTP_X_FORWARDED_SSL'] ) && 'on' === $_SERVER['HTTP_X_FORWARDED_SSL'] ) ||
            ( isset( $_SERVER['HTTP_CF_VISITOR'] ) && strpos( $_SERVER['HTTP_CF_VISITOR'], 'https' ) !== false )
        ) {
            return;
        }

        if ( ! is_admin() && ! wp_doing_cron() ) {
            $uri  = $_SERVER['REQUEST_URI'] ?? '/';
            $redirect_url = 'https://' . ( $host ?: 'findewerkstatt.de' ) . $uri;
            wp_safe_redirect( $redirect_url, 301 );
            exit;
        }
    }

    public static function send_security_headers() {
        if ( headers_sent() ) {
            return;
        }

        $host = $_SERVER['HTTP_HOST'] ?? '';
        $is_local = ( empty( $host ) || strpos( $host, 'localhost' ) !== false || strpos( $host, '127.0.0.1' ) !== false || strpos( $host, '.local' ) !== false || strpos( $host, '.test' ) !== false );

        if ( ! $is_local && ( is_ssl() || ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ) ) {
            header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains', false );
        }

        header( 'X-Content-Type-Options: nosniff', false );
        header( 'X-Frame-Options: SAMEORIGIN', false );
        header( 'Referrer-Policy: strict-origin-when-cross-origin', false );
        header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(self)', false );
    }

    public static function prevent_auth_page_caching() {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $path = trim( (string) parse_url( $uri, PHP_URL_PATH ), '/' );

        $sensitive_slugs = array(
            'giris',
            'kayit',
            'profil',
            'sifremi-unuttum',
            'hesabim',
            'cikis',
            'usta-ekle',
        );

        $is_sensitive = in_array( $path, $sensitive_slugs, true ) ||
                        is_page( array( 'giris', 'kayit', 'profil', 'sifremi-unuttum', 'hesabim', 'usta-ekle' ) ) ||
                        is_user_logged_in();

        if ( $is_sensitive ) {
            nocache_headers();

            if ( ! headers_sent() ) {
                header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private', true );
                header( 'Pragma: no-cache', true );
                header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT', true );
                header( 'X-LiteSpeed-Cache-Control: no-cache', true );
                header( 'CDN-Cache-Control: no-store', true );
            }
        }
    }

    /**
     * Tekil ve standart canonical yonetimi.
     * Cift basimlari ve sonu egik cizgisiz/cizgili celiskilerini cozer.
     */
    public static function render_unified_canonical() {
        if ( is_admin() || is_feed() || is_trackback() ) {
            return;
        }

        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $path = trim( (string) parse_url( $uri, PHP_URL_PATH ), '/' );
        $noindex_slugs = array( 'giris', 'kayit', 'profil', 'sifremi-unuttum', 'hesabim' );

        // Giris ve kayit gibi form sayfalarinda indekslemeyi engelle
        if ( in_array( $path, $noindex_slugs, true ) || is_page( $noindex_slugs ) || is_404() || is_search() ) {
            echo '<meta name="robots" content="noindex, nofollow" />' . "
";
            return;
        }

        $canonical = '';
        if ( is_front_page() || is_home() ) {
            $canonical = home_url( '/' );
        } elseif ( is_singular() ) {
            $canonical = get_permalink();
        } elseif ( is_tax() || is_category() || is_tag() ) {
            $term_link = get_term_link( get_queried_object() );
            if ( ! is_wp_error( $term_link ) ) {
                $canonical = $term_link;
            }
        } elseif ( is_post_type_archive( 'mechanic' ) ) {
            $canonical = home_url( '/ustalar/' );
        } elseif ( ! empty( $path ) ) {
            $canonical = home_url( '/' . $path . '/' );
        }

        if ( ! empty( $canonical ) && ! is_wp_error( $canonical ) ) {
            // Trailing slash garantisi
            if ( substr( $canonical, -1 ) !== '/' && strpos( basename( $canonical ), '.' ) === false ) {
                $canonical .= '/';
            }
            echo '<link rel="canonical" href="' . esc_url( $canonical ) . '" />' . "
";
        }
    }
}

OtoTamir_Security_And_Cache_Manager::init();
