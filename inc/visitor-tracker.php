<?php
/**
 * OtoTamirciBul - İnsan Ziyaretçi Takip Motoru (Botlar Hariç Gerçek Trafik Analitiği)
 * 
 * Özellikler:
 * 1. %100 Anti-Bot Filtreleme: Google, Bing, Yandex botları, Ahrefs, Semrush, Petalbot,
 *    ByteSpider, Python scraper'lar, curl, headless tarayıcılar hariç tutulur.
 * 2. İstemci taraflı async beacon tetikleyicisi (Sadece gerçek JS çalıştıran tarayıcılar sayılır).
 * 3. Anlık Canlı Ziyaretçi Sayacı (Son 5 dakika içinde sitede gezinenler).
 * 4. Günlük Tekil İnsan Ziyaretçisi & Sayfa Görüntüleme Grafiği (Son 14 gün).
 * 5. En Çok Gezilen Sayfalar, Trafik Kaynakları (Google, Sosyal Medya, Direkt) ve Cihaz Dağılımı.
 * 6. Canlı Ziyaret Akışı (Son 50 insan ziyaretçinin maskeli IP, sayfa, kaynak ve cihaz kaydı).
 * 7. Sıfır harici kütüphane, KVKK & GDPR uyumlu, hafif ve veritabanını şişirmeyen yapı.
 * 
 * @package OtoTamirciBul
 * @version 1.5.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OtoTamir_Visitor_Tracker {

    const OPTION_STATS            = 'ototamir_visitor_stats';
    const OPTION_RECENT           = 'ototamir_visitor_recent';
    const OPTION_ONLINE           = 'ototamir_visitor_online_map';
    const OPTION_LEAD_STATS       = 'ototamir_visitor_lead_stats';
    const OPTION_LEAD_RECENT      = 'ototamir_visitor_lead_recent';
    const OPTION_LEAD_LEADERBOARD = 'ototamir_visitor_lead_leaderboard';

    public static function init() {
        // İstemci beacon AJAX uç noktaları (oturum açmış veya açmamış tüm ziyaretçiler)
        add_action( 'wp_ajax_ototamir_log_visit', array( __CLASS__, 'ajax_log_visit' ) );
        add_action( 'wp_ajax_nopriv_ototamir_log_visit', array( __CLASS__, 'ajax_log_visit' ) );

        // İlan İçi Müşteri Aksiyon Takipçisi (Telefon, WhatsApp, Konum / Yol Tarifi)
        add_action( 'wp_ajax_ototamir_log_lead_action', array( __CLASS__, 'ajax_log_lead_action' ) );
        add_action( 'wp_ajax_nopriv_ototamir_log_lead_action', array( __CLASS__, 'ajax_log_lead_action' ) );

        // İstatistikleri sıfırlama AJAX
        add_action( 'wp_ajax_ototamir_reset_visitor_stats', array( __CLASS__, 'ajax_reset_stats' ) );

        // İzleme kodunu footer'a asenkron ekleme
        add_action( 'wp_footer', array( __CLASS__, 'inject_tracker_script' ), 99 );

        // WP-Admin Menüsü
        add_action( 'admin_menu', array( __CLASS__, 'register_admin_menu' ) );
    }

    /**
     * WP-Admin Menüsü Kaydı
     */
    public static function register_admin_menu() {
        // Ana Menü Olarak Ekle
        add_menu_page(
            __( 'Canlı Ziyaretçi Takibi (Botlar Hariç)', 'ototamir' ),
            __( '📈 Ziyaretçi Takibi', 'ototamir' ),
            'manage_options',
            'ototamir-visitors',
            array( __CLASS__, 'render_admin_dashboard' ),
            'dashicons-chart-area',
            6
        );

        // İlanlar Altına da Alt Menü Olarak Ekle (Erişim Kolaylığı)
        add_submenu_page(
            'edit.php?post_type=mechanic',
            __( 'Canlı Ziyaretçi Takibi & İstatistikler', 'ototamir' ),
            __( '📈 Ziyaretçi Takibi', 'ototamir' ),
            'manage_options',
            'ototamir-visitors',
            array( __CLASS__, 'render_admin_dashboard' )
        );
    }

    /**
     * Bot Tespiti ve Ayıklama Motoru
     */
    public static function is_bot( $user_agent ) {
        if ( empty( $user_agent ) || strlen( $user_agent ) < 12 ) {
            return true;
        }

        $bot_signatures = array(
            'googlebot', 'bingbot', 'yandexbot', 'yandeximages', 'baiduspider',
            'duckduckbot', 'slurp', 'sogou', 'exabot', 'facebot',
            'facebookexternalhit', 'ia_archiver', 'ahrefs', 'semrush',
            'mj12bot', 'petalbot', 'dotbot', 'bytespider', 'screaming frog',
            'rogerbot', 'twitterbot', 'slackbot', 'telegrambot', 'discordbot',
            'whatsapp', 'pinterest', 'applebot', 'seznambot', 'megaindex',
            'blexbot', 'zoominfobot', 'curl', 'wget', 'python', 'urllib',
            'guzzle', 'postman', 'headlesschrome', 'phantomjs', 'selenium',
            'puppeteer', 'playwright', 'axios', 'go-http-client', 'java',
            'crawler', 'spider', 'bot/', 'bot;', 'bot ', 'scan', 'archive.org'
        );

        $ua_lower = strtolower( $user_agent );
        foreach ( $bot_signatures as $sig ) {
            if ( strpos( $ua_lower, $sig ) !== false ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cihaz Tipi Tespiti
     */
    public static function detect_device( $ua ) {
        $ua = strtolower( $ua );
        if ( strpos( $ua, 'tablet' ) !== false || strpos( $ua, 'ipad' ) !== false ) {
            return 'tablet';
        }
        if ( strpos( $ua, 'mobile' ) !== false || strpos( $ua, 'android' ) !== false || strpos( $ua, 'iphone' ) !== false ) {
            return 'mobile';
        }
        return 'desktop';
    }

    /**
     * İşletim Sistemi Tespiti
     */
    public static function detect_os( $ua ) {
        $ua_lower = strtolower( $ua );
        if ( strpos( $ua_lower, 'android' ) !== false ) return 'Android';
        if ( strpos( $ua_lower, 'iphone' ) !== false || strpos( $ua_lower, 'ipad' ) !== false || strpos( $ua_lower, 'ios' ) !== false ) return 'iOS';
        if ( strpos( $ua_lower, 'windows' ) !== false ) return 'Windows';
        if ( strpos( $ua_lower, 'macintosh' ) !== false || strpos( $ua_lower, 'mac os' ) !== false ) return 'macOS';
        if ( strpos( $ua_lower, 'linux' ) !== false ) return 'Linux';
        return 'Diğer';
    }

    /**
     * Tarayıcı Tespiti
     */
    public static function detect_browser( $ua ) {
        $ua_lower = strtolower( $ua );
        if ( strpos( $ua_lower, 'edg' ) !== false ) return 'Edge';
        if ( strpos( $ua_lower, 'opr' ) !== false || strpos( $ua_lower, 'opera' ) !== false ) return 'Opera';
        if ( strpos( $ua_lower, 'samsungbrowser' ) !== false ) return 'Samsung';
        if ( strpos( $ua_lower, 'chrome' ) !== false && strpos( $ua_lower, 'safari' ) !== false ) return 'Chrome';
        if ( strpos( $ua_lower, 'safari' ) !== false && strpos( $ua_lower, 'chrome' ) === false ) return 'Safari';
        if ( strpos( $ua_lower, 'firefox' ) !== false ) return 'Firefox';
        return 'Bilinmeyen';
    }

    /**
     * Trafik Kaynağı / Referrer Tespiti
     */
    public static function detect_referrer_source( $ref ) {
        if ( empty( $ref ) ) {
            return array( 'key' => 'direct', 'label' => 'Doğrudan Giriş (Direct)', 'icon' => 'fa-link' );
        }

        $host = strtolower( parse_url( $ref, PHP_URL_HOST ) ?? '' );
        $current_host = strtolower( $_SERVER['HTTP_HOST'] ?? '' );

        if ( $host === $current_host || empty( $host ) ) {
            return array( 'key' => 'internal', 'label' => 'Site İçi Gezinme', 'icon' => 'fa-arrow-rotate-right' );
        }

        if ( strpos( $host, 'google.' ) !== false ) {
            return array( 'key' => 'google', 'label' => 'Google Arama (Organik)', 'icon' => 'fa-brands fa-google' );
        }
        if ( strpos( $host, 'yandex.' ) !== false ) {
            return array( 'key' => 'yandex', 'label' => 'Yandex Arama', 'icon' => 'fa-brands fa-yandex' );
        }
        if ( strpos( $host, 'bing.' ) !== false ) {
            return array( 'key' => 'bing', 'label' => 'Bing Arama', 'icon' => 'fa-brands fa-microsoft' );
        }
        if ( strpos( $host, 'instagram.' ) !== false ) {
            return array( 'key' => 'instagram', 'label' => 'Instagram', 'icon' => 'fa-brands fa-instagram' );
        }
        if ( strpos( $host, 'facebook.' ) !== false || strpos( $host, 'fb.com' ) !== false ) {
            return array( 'key' => 'facebook', 'label' => 'Facebook', 'icon' => 'fa-brands fa-facebook' );
        }
        if ( strpos( $host, 'tiktok.' ) !== false ) {
            return array( 'key' => 'tiktok', 'label' => 'TikTok', 'icon' => 'fa-brands fa-tiktok' );
        }
        if ( strpos( $host, 'twitter.' ) !== false || strpos( $host, 'x.com' ) !== false || strpos( $host, 't.co' ) !== false ) {
            return array( 'key' => 'twitter', 'label' => 'X (Twitter)', 'icon' => 'fa-brands fa-x-twitter' );
        }
        if ( strpos( $host, 'whatsapp.' ) !== false || strpos( $host, 'wa.me' ) !== false ) {
            return array( 'key' => 'whatsapp', 'label' => 'WhatsApp', 'icon' => 'fa-brands fa-whatsapp' );
        }

        return array( 'key' => 'external', 'label' => $host, 'icon' => 'fa-globe' );
    }

    /**
     * IP Maskeleme (KVKK / GDPR Uyumlu)
     */
    public static function mask_ip( $ip ) {
        if ( empty( $ip ) ) return 'Bilinmiyor';
        if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
            $parts = explode( '.', $ip );
            if ( count( $parts ) === 4 ) {
                return $parts[0] . '.' . $parts[1] . '.***.***';
            }
        }
        if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
            $parts = explode( ':', $ip );
            return ( $parts[0] ?? '2001' ) . ':****:****';
        }
        return '***.***.***.***';
    }

    /**
     * Gerçek Ziyaretçi IP'sini Al
     */
    public static function get_client_ip() {
        $headers = array(
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        );
        foreach ( $headers as $h ) {
            if ( ! empty( $_SERVER[ $h ] ) ) {
                $ips = explode( ',', $_SERVER[ $h ] );
                $clean = trim( $ips[0] );
                if ( filter_var( $clean, FILTER_VALIDATE_IP ) ) {
                    return $clean;
                }
            }
        }
        return '127.0.0.1';
    }

    /**
     * AJAX: İstemciden Gelen Ziyaret Kaydını İşle
     */
    public static function ajax_log_visit() {
        check_ajax_referer( 'ototamir_visitor_nonce', 'nonce' );

        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip = self::get_client_ip();

        // 1. Bot Filtresi Kontrolü
        if ( self::is_bot( $ua ) ) {
            self::increment_blocked_bot_count();
            wp_send_json_success( array( 'logged' => false, 'reason' => 'bot_filtered' ) );
        }

        // 2. Yönetici Ziyaretlerini İsteğe Bağlı Hariç Tutma
        if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
            $exclude_admin = get_option( 'ototamir_visitor_exclude_admins', '1' );
            if ( $exclude_admin === '1' ) {
                wp_send_json_success( array( 'logged' => false, 'reason' => 'admin_excluded' ) );
            }
        }

        // 3. Verileri Temizle ve Hazırla
        $raw_url   = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : home_url('/');
        $raw_title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : 'OtoTamirciBul';
        $raw_ref   = isset( $_POST['referrer'] ) ? esc_url_raw( wp_unslash( $_POST['referrer'] ) ) : '';

        // Başlığı sadeleştir
        $clean_title = preg_replace( '/\s*\|\s*OtoTamirciBul.*$/iu', '', $raw_title );
        $clean_title = trim( $clean_title ) ?: 'Ana Sayfa';

        // URL Path'ini çıkar (Domain hariç)
        $url_path = parse_url( $raw_url, PHP_URL_PATH ) ?: '/';

        $device   = self::detect_device( $ua );
        $os       = self::detect_os( $ua );
        $browser  = self::detect_browser( $ua );
        $ref_data = self::detect_referrer_source( $raw_ref );

        $now   = time();
        $today = date( 'Y-m-d', $now );

        // Ziyaretçi Salt ve Tekil Hash (Günlük tekil ziyaretçi hesaplaması için)
        $salt = get_option( 'ototamir_visitor_salt' );
        if ( ! $salt ) {
            $salt = wp_generate_password( 24, false );
            update_option( 'ototamir_visitor_salt', $salt, false );
        }
        $session_hash = md5( $salt . '_' . $ip . '_' . $today );

        // 4. Anlık Canlı Ziyaretçi Haritasını Güncelle (Son 300 saniye = 5 dakika)
        self::update_online_map( $session_hash, $now, $clean_title, $device );

        // 5. Ana İstatistikleri Güncelle
        $stats = get_option( self::OPTION_STATS, array(
            'total_pageviews' => 0,
            'total_uniques'   => 0,
            'blocked_bots'    => 0,
            'daily'           => array(), // [ 'Y-m-d' => [ 'views' => X, 'uniques' => Y ] ]
            'pages'           => array(), // [ 'path' => [ 'title' => T, 'views' => X ] ]
            'sources'         => array(), // [ 'google' => X, 'direct' => Y, ... ]
            'devices'         => array( 'mobile' => 0, 'desktop' => 0, 'tablet' => 0 ),
            'browsers'        => array(),
            'os'              => array(),
        ) );

        $stats['total_pageviews'] = (int) ( $stats['total_pageviews'] ?? 0 ) + 1;

        // Günlük İstatistik
        if ( ! isset( $stats['daily'][ $today ] ) ) {
            $stats['daily'][ $today ] = array(
                'views'    => 0,
                'uniques'  => 0,
                '_hashes'  => array(), // transient hash storage
            );
        }

        $stats['daily'][ $today ]['views']++;

        if ( ! in_array( $session_hash, $stats['daily'][ $today ]['_hashes'] ?? array(), true ) ) {
            $stats['daily'][ $today ]['uniques']++;
            $stats['daily'][ $today ]['_hashes'][] = $session_hash;
            $stats['total_uniques'] = (int) ( $stats['total_uniques'] ?? 0 ) + 1;

            // Hash listesini bellek tasarrufu için 1000 ile sınırla
            if ( count( $stats['daily'][ $today ]['_hashes'] ) > 1000 ) {
                array_shift( $stats['daily'][ $today ]['_hashes'] );
            }
        }

        // Sayfa Dağılımı
        if ( ! isset( $stats['pages'][ $url_path ] ) ) {
            $stats['pages'][ $url_path ] = array(
                'title' => $clean_title,
                'url'   => $raw_url,
                'views' => 0,
            );
        }
        $stats['pages'][ $url_path ]['views']++;
        if ( ! empty( $clean_title ) && $clean_title !== 'Ana Sayfa' ) {
            $stats['pages'][ $url_path ]['title'] = $clean_title;
        }

        // Trafik Kaynağı Dağılımı (Internal gezinmeler hariç)
        if ( $ref_data['key'] !== 'internal' ) {
            $s_key = $ref_data['key'];
            $stats['sources'][ $s_key ] = (int) ( $stats['sources'][ $s_key ] ?? 0 ) + 1;
        }

        // Cihaz & Tarayıcı & OS
        $stats['devices'][ $device ] = (int) ( $stats['devices'][ $device ] ?? 0 ) + 1;
        $stats['browsers'][ $browser ] = (int) ( $stats['browsers'][ $browser ] ?? 0 ) + 1;
        $stats['os'][ $os ] = (int) ( $stats['os'][ $os ] ?? 0 ) + 1;

        // Son 30 günden eski günleri buda (hafif kalması için)
        if ( count( $stats['daily'] ) > 30 ) {
            $stats['daily'] = array_slice( $stats['daily'], -30, null, true );
        }

        // En çok okunan sayfaları sınırla (top 50)
        if ( count( $stats['pages'] ) > 60 ) {
            uasort( $stats['pages'], function($a, $b) {
                return ($b['views'] ?? 0) <=> ($a['views'] ?? 0);
            } );
            $stats['pages'] = array_slice( $stats['pages'], 0, 50, true );
        }

        update_option( self::OPTION_STATS, $stats, false );

        // 6. Canlı Son Ziyaretler Akışına Ekle (Son 50 İnsan)
        $recent_entry = array(
            'time'        => $now,
            'ip'          => self::mask_ip( $ip ),
            'title'       => $clean_title,
            'url'         => $raw_url,
            'path'        => $url_path,
            'device'      => $device,
            'os'          => $os,
            'browser'     => $browser,
            'source'      => $ref_data['label'],
            'source_key'  => $ref_data['key'],
            'source_icon' => $ref_data['icon'],
        );

        $recent_logs = get_option( self::OPTION_RECENT, array() );
        if ( ! is_array( $recent_logs ) ) {
            $recent_logs = array();
        }
        array_unshift( $recent_logs, $recent_entry );
        $recent_logs = array_slice( $recent_logs, 0, 50 ); // Sabit 50 kayıt
        update_option( self::OPTION_RECENT, $recent_logs, false );

        wp_send_json_success( array( 'logged' => true ) );
    }

    /**
     * Anlık Canlı Çevrimiçi Haritasını Güncelle
     */
    private static function update_online_map( $session_hash, $time, $page, $device ) {
        $online_map = get_option( self::OPTION_ONLINE, array() );
        if ( ! is_array( $online_map ) ) {
            $online_map = array();
        }

        $threshold = $time - 300; // Son 5 dakika

        // Eski oturumları temizle
        foreach ( $online_map as $hash => $data ) {
            if ( ( $data['time'] ?? 0 ) < $threshold ) {
                unset( $online_map[ $hash ] );
            }
        }

        // Güncel oturumu yaz
        $online_map[ $session_hash ] = array(
            'time'   => $time,
            'page'   => $page,
            'device' => $device,
        );

        update_option( self::OPTION_ONLINE, $online_map, false );
    }

    /**
     * Engellenen Bot Sayacını Artır
     */
    private static function increment_blocked_bot_count() {
        $stats = get_option( self::OPTION_STATS, array() );
        $stats['blocked_bots'] = (int) ( $stats['blocked_bots'] ?? 0 ) + 1;
        update_option( self::OPTION_STATS, $stats, false );
    }

    /**
     * İstatistikleri Sıfırla (AJAX)
     */
    public static function ajax_reset_stats() {
        check_ajax_referer( 'ototamir_reset_visitor_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Yetkisiz erişim.' );
        }

        delete_option( self::OPTION_STATS );
        delete_option( self::OPTION_RECENT );
        delete_option( self::OPTION_ONLINE );
        delete_option( self::OPTION_LEAD_STATS );
        delete_option( self::OPTION_LEAD_RECENT );
        delete_option( self::OPTION_LEAD_LEADERBOARD );

        wp_send_json_success();
    }

    /**
     * AJAX: İlan İçi Müşteri Eylemlerini Kaydet (Telefon, WhatsApp, Konum, Yol Tarifi)
     */
    public static function ajax_log_lead_action() {
        check_ajax_referer( 'ototamir_visitor_nonce', 'nonce' );

        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip = self::get_client_ip();

        if ( self::is_bot( $ua ) ) {
            wp_send_json_success( array( 'logged' => false, 'reason' => 'bot' ) );
        }

        if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
            $exclude_admin = get_option( 'ototamir_visitor_exclude_admins', '1' );
            if ( $exclude_admin === '1' ) {
                wp_send_json_success( array( 'logged' => false, 'reason' => 'admin_excluded' ) );
            }
        }

        $post_id     = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
        $action_type = isset( $_POST['action_type'] ) ? sanitize_key( $_POST['action_type'] ) : '';
        $raw_url     = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : ( $post_id ? get_permalink( $post_id ) : '' );
        $title       = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : ( $post_id ? get_the_title( $post_id ) : 'İlan' );

        // Başlığı sadeleştir
        $clean_title = preg_replace( '/\s*\|\s*OtoTamirciBul.*$/iu', '', $title );
        $clean_title = trim( $clean_title ) ?: ( $post_id ? get_the_title( $post_id ) : 'İlan' );

        $valid_actions = array( 'call', 'whatsapp', 'directions', 'location_wp' );
        if ( ! in_array( $action_type, $valid_actions, true ) ) {
            wp_send_json_error( array( 'message' => 'Geçersiz eylem' ) );
        }

        // Sunucu taraflı çift tıklama / mükerrer kilit koruması (3 saniye kilit)
        $debounce_key = 'ot_lead_lock_' . md5( $ip . '_' . $post_id . '_' . $action_type );
        if ( get_transient( $debounce_key ) ) {
            wp_send_json_success( array( 'logged' => false, 'reason' => 'debounced' ) );
        }
        set_transient( $debounce_key, 1, 3 );

        $now     = time();
        $today   = date( 'Y-m-d', $now );
        $device  = self::detect_device( $ua );
        $os      = self::detect_os( $ua );
        $browser = self::detect_browser( $ua );

        // İlan bilgilerini çek
        $phone   = $post_id ? get_post_meta( $post_id, '_mechanic_phone', true ) : '';
        $address = $post_id ? get_post_meta( $post_id, '_mechanic_address', true ) : '';

        // Nereye tıkladığına dair kristal net detay metni üret
        if ( $action_type === 'call' ) {
            $action_detail = '📞 "Hemen Ara" butonuna / numaraya tıkladı' . ( $phone ? ' (' . $phone . ')' : '' );
        } elseif ( $action_type === 'whatsapp' ) {
            $action_detail = '💬 "WhatsApp\'tan Yaz" butonuna tıklayıp sohbet başlattı' . ( $phone ? ' (' . $phone . ')' : '' );
        } elseif ( $action_type === 'directions' ) {
            $action_detail = '📍 "Açık Adres / Haritada Yol Tarifi Al" butonuna tıklayıp rota çizdi' . ( $address ? ' (' . wp_trim_words($address, 5) . ')' : '' );
        } elseif ( $action_type === 'location_wp' ) {
            $action_detail = '🚨 "📍 Konumumu WhatsApp\'la Gönder" butonuna basarak acil GPS konumu paylaştı';
        } else {
            $action_detail = 'İlan üzerinde müşteri etkileşimi gerçekleştirdi';
        }

        // 1. İlan Meta Sayaçlarını ve Özel Tıklama Geçmişini Güncelle
        if ( $post_id > 0 ) {
            $meta_key = '_mechanic_' . $action_type . '_count';
            $current_count = (int) get_post_meta( $post_id, $meta_key, true );
            update_post_meta( $post_id, $meta_key, $current_count + 1 );

            $total_leads = (int) get_post_meta( $post_id, '_mechanic_leads_total', true );
            update_post_meta( $post_id, '_mechanic_leads_total', $total_leads + 1 );

            // Bu ilan için son 30 tıklama günlüğü
            $listing_lead_logs = get_post_meta( $post_id, '_mechanic_lead_logs', true );
            if ( ! is_array( $listing_lead_logs ) ) {
                $listing_lead_logs = array();
            }
            array_unshift( $listing_lead_logs, array(
                'time'        => $now,
                'action_type' => $action_type,
                'detail'      => $action_detail,
                'ip'          => self::mask_ip( $ip ),
                'device'      => $device . ' · ' . $browser,
            ) );
            if ( count( $listing_lead_logs ) > 30 ) {
                $listing_lead_logs = array_slice( $listing_lead_logs, 0, 30 );
            }
            update_post_meta( $post_id, '_mechanic_lead_logs', $listing_lead_logs );

            // Liderler Tablosu (Leaderboard) Güncellemesi
            $leaderboard = get_option( self::OPTION_LEAD_LEADERBOARD, array() );
            if ( ! is_array( $leaderboard ) ) {
                $leaderboard = array();
            }

            if ( ! isset( $leaderboard[ $post_id ] ) ) {
                $leaderboard[ $post_id ] = array(
                    'post_id'     => $post_id,
                    'title'       => $clean_title,
                    'url'         => $raw_url,
                    'phone'       => $phone,
                    'call'        => 0,
                    'whatsapp'    => 0,
                    'directions'  => 0,
                    'location_wp' => 0,
                    'total'       => 0,
                    'last_action' => $now,
                );
            }

            $leaderboard[ $post_id ][ $action_type ] = (int) ( $leaderboard[ $post_id ][ $action_type ] ?? 0 ) + 1;
            $leaderboard[ $post_id ]['total']        = (int) ( $leaderboard[ $post_id ]['total'] ?? 0 ) + 1;
            $leaderboard[ $post_id ]['last_action']  = $now;
            $leaderboard[ $post_id ]['title']        = $clean_title;
            if ( ! empty( $phone ) ) {
                $leaderboard[ $post_id ]['phone'] = $phone;
            }

            uasort( $leaderboard, function( $a, $b ) {
                return ( $b['total'] ?? 0 ) <=> ( $a['total'] ?? 0 );
            } );
            $leaderboard = array_slice( $leaderboard, 0, 50, true );
            update_option( self::OPTION_LEAD_LEADERBOARD, $leaderboard, false );
        }

        // 2. Global Lead İstatistiklerini Güncelle
        $lead_stats = get_option( self::OPTION_LEAD_STATS, array(
            'total_actions' => 0,
            'call'          => 0,
            'whatsapp'      => 0,
            'directions'    => 0,
            'location_wp'   => 0,
            'daily'         => array(),
        ) );

        $lead_stats['total_actions'] = (int) ( $lead_stats['total_actions'] ?? 0 ) + 1;
        $lead_stats[ $action_type ]  = (int) ( $lead_stats[ $action_type ] ?? 0 ) + 1;

        if ( ! isset( $lead_stats['daily'][ $today ] ) ) {
            $lead_stats['daily'][ $today ] = array(
                'total'       => 0,
                'call'        => 0,
                'whatsapp'    => 0,
                'directions'  => 0,
                'location_wp' => 0,
            );
        }
        $lead_stats['daily'][ $today ]['total']++;
        $lead_stats['daily'][ $today ][ $action_type ] = (int) ( $lead_stats['daily'][ $today ][ $action_type ] ?? 0 ) + 1;

        if ( count( $lead_stats['daily'] ) > 30 ) {
            $lead_stats['daily'] = array_slice( $lead_stats['daily'], -30, null, true );
        }

        update_option( self::OPTION_LEAD_STATS, $lead_stats, false );

        // 3. Canlı Müşteri Eylemleri Akışına Ekle (Son 60 işlem)

        $action_labels = array(
            'call'        => array( 'label' => 'Telefon Numarasına Tıkladı', 'icon' => 'fa-phone', 'color' => '#ea580c', 'bg' => '#ffedd5' ),
            'whatsapp'    => array( 'label' => 'WhatsApp Butonuna Tıkladı', 'icon' => 'fa-whatsapp', 'color' => '#10b981', 'bg' => '#dcfce7' ),
            'directions'  => array( 'label' => 'Açık Adres / Haritada Konum Aldı', 'icon' => 'fa-diamond-turn-right', 'color' => '#0284c7', 'bg' => '#e0f2fe' ),
            'location_wp' => array( 'label' => 'Acil Konum Paylaşımına Bastı', 'icon' => 'fa-location-crosshairs', 'color' => '#dc2626', 'bg' => '#fee2e2' ),
        );

        $action_info = $action_labels[ $action_type ] ?? array( 'label' => 'Etkileşim', 'icon' => 'fa-bolt', 'color' => '#475569', 'bg' => '#f1f5f9' );

        $lead_entry = array(
            'time'         => $now,
            'post_id'      => $post_id,
            'title'        => $clean_title,
            'url'          => $raw_url,
            'action_type'  => $action_type,
            'action_label' => $action_info['label'],
            'action_detail'=> $action_detail,
            'action_icon'  => $action_info['icon'],
            'action_color' => $action_info['color'],
            'action_bg'    => $action_info['bg'],
            'ip'           => self::mask_ip( $ip ),
            'device'       => $device,
            'os'           => $os,
            'browser'      => $browser,
        );

        $recent_leads = get_option( self::OPTION_LEAD_RECENT, array() );
        if ( ! is_array( $recent_leads ) ) {
            $recent_leads = array();
        }
        array_unshift( $recent_leads, $lead_entry );
        if ( count( $recent_leads ) > 60 ) {
            $recent_leads = array_slice( $recent_leads, 0, 60 );
        }
        update_option( self::OPTION_LEAD_RECENT, $recent_leads, false );

        wp_send_json_success( array( 'logged' => true, 'action' => $action_type ) );
    }

    /**
     * Footer'a Asenkron Takip Kodu Enjeksiyonu
     */
    public static function inject_tracker_script() {
        // WP-Admin içinde çalışma
        if ( is_admin() ) return;

        static $injected = false;
        if ( $injected ) return;
        $injected = true;

        $nonce = wp_create_nonce( 'ototamir_visitor_nonce' );
        $ajax_url = admin_url( 'admin-ajax.php' );
        $current_post_id = is_singular( 'mechanic' ) ? get_the_ID() : 0;
        ?>
        <!-- OtoTamirciBul Real-Time Human Visitor Beacon & Lead Action Tracker -->
        <script>
        (function() {
            if (typeof window === 'undefined' || typeof document === 'undefined') return;
            if (window.__ototamirTrackerInjected) return;
            window.__ototamirTrackerInjected = true;

            window.ototamirTrackingPostId = <?php echo intval( $current_post_id ); ?>;
            var ajaxUrl = '<?php echo esc_url( $ajax_url ); ?>';
            var nonce   = '<?php echo esc_js( $nonce ); ?>';

            // 1. Sayfa Açılış Ziyaret Kaydı (700ms sonra kullanıcıyı bekletmeden)
            window.addEventListener('load', function() {
                setTimeout(function() {
                    try {
                        var payload = new FormData();
                        payload.append('action', 'ototamir_log_visit');
                        payload.append('nonce', nonce);
                        payload.append('url', window.location.href);
                        payload.append('title', document.title);
                        payload.append('referrer', document.referrer);

                        if (navigator.sendBeacon) {
                            navigator.sendBeacon(ajaxUrl, payload);
                        } else if (window.fetch) {
                            fetch(ajaxUrl, {
                                method: 'POST',
                                body: payload,
                                credentials: 'same-origin',
                                keepalive: true
                            }).catch(function(){});
                        }
                    } catch (e) {}
                }, 700);
            });

            // 2. İlan İçi Müşteri Aksiyonlarını Yakalama (Telefon, WhatsApp, Konum / Yol Tarifi)
            document.addEventListener('click', function(e) {
                var target = e.target.closest('a, button, [data-track-action]');
                if (!target) return;

                var actionType = '';
                var href = target.getAttribute('href') || '';

                if (target.dataset && target.dataset.trackAction) {
                    actionType = target.dataset.trackAction;
                } else if (href.indexOf('tel:') === 0 || target.classList.contains('btn-call')) {
                    actionType = 'call';
                } else if (target.id === 'btn-share-location-wp' || target.classList.contains('btn-location-wp')) {
                    actionType = 'location_wp';
                } else if (href.indexOf('wa.me') !== -1 || href.indexOf('whatsapp.com') !== -1 || target.classList.contains('btn-whatsapp')) {
                    actionType = 'whatsapp';
                } else if (target.classList.contains('btn-maps-route') || href.indexOf('google.com/maps') !== -1 || href.indexOf('maps.google') !== -1) {
                    actionType = 'directions';
                }

                if (actionType) {
                    var now = Date.now();
                    var pId = target.getAttribute('data-post-id') || window.ototamirTrackingPostId || 0;
                    var actionKey = actionType + '_' + pId;

                    // Global pencere seviyesinde çift tıklama / mükerrer kilit koruması (2 saniye)
                    if (window.__ototamirLastActionKey === actionKey && (now - (window.__ototamirLastActionTime || 0)) < 2000) {
                        return;
                    }
                    window.__ototamirLastActionTime = now;
                    window.__ototamirLastActionKey  = actionKey;

                    try {
                        var payload = new FormData();
                        payload.append('action', 'ototamir_log_lead_action');
                        payload.append('nonce', nonce);
                        payload.append('post_id', pId);
                        payload.append('action_type', actionType);
                        payload.append('title', document.title);
                        payload.append('url', window.location.href);

                        if (navigator.sendBeacon) {
                            navigator.sendBeacon(ajaxUrl, payload);
                        } else if (window.fetch) {
                            fetch(ajaxUrl, {
                                method: 'POST',
                                body: payload,
                                credentials: 'same-origin',
                                keepalive: true
                            }).catch(function(){});
                        }
                    } catch (err) {}
                }
            }, true);
        })();
        </script>
        <?php
    }

    /**
     * WP-Admin Gösterge Paneli
     */
    public static function render_admin_dashboard() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Bu sayfaya erişim yetkiniz yok.' );
        }

        $stats       = get_option( self::OPTION_STATS, array() );
        $recent_logs = get_option( self::OPTION_RECENT, array() );
        $online_map  = get_option( self::OPTION_ONLINE, array() );
        $lead_stats  = get_option( self::OPTION_LEAD_STATS, array() );
        $recent_leads= get_option( self::OPTION_LEAD_RECENT, array() );

        // İlan İçi Dönüşüm İstatistikleri
        $total_calls       = (int) ( $lead_stats['call'] ?? 0 );
        $total_whatsapp    = (int) ( $lead_stats['whatsapp'] ?? 0 );
        $total_directions  = (int) ( $lead_stats['directions'] ?? 0 );
        $total_location_wp = (int) ( $lead_stats['location_wp'] ?? 0 );
        $total_leads       = (int) ( $lead_stats['total_actions'] ?? 0 );

        // Canlı Ziyaretçi Sayısı (Son 5 dakika)
        $now = time();
        $threshold = $now - 300;
        $live_count = 0;
        if ( is_array( $online_map ) ) {
            foreach ( $online_map as $h => $d ) {
                if ( ( $d['time'] ?? 0 ) >= $threshold ) {
                    $live_count++;
                }
            }
        }

        $today = date( 'Y-m-d', $now );
        $today_views   = $stats['daily'][ $today ]['views'] ?? 0;
        $today_uniques = $stats['daily'][ $today ]['uniques'] ?? 0;
        $total_views   = $stats['total_pageviews'] ?? 0;
        $total_uniques = $stats['total_uniques'] ?? 0;
        $blocked_bots  = $stats['blocked_bots'] ?? 0;

        $devices = $stats['devices'] ?? array( 'mobile' => 0, 'desktop' => 0, 'tablet' => 0 );
        $total_devices = array_sum( $devices );
        $mobile_pct = $total_devices > 0 ? round( ( ( $devices['mobile'] ?? 0 ) / $total_devices ) * 100 ) : 0;
        $desktop_pct = $total_devices > 0 ? round( ( ( $devices['desktop'] ?? 0 ) / $total_devices ) * 100 ) : 0;

        // Son 14 Gün Verisi
        $last_14_days = array();
        for ( $i = 13; $i >= 0; $i-- ) {
            $d_key = date( 'Y-m-d', strtotime( "-{$i} days", $now ) );
            $d_label = date( 'd M', strtotime( "-{$i} days", $now ) );
            $last_14_days[ $d_key ] = array(
                'label'   => $d_label,
                'views'   => $stats['daily'][ $d_key ]['views'] ?? 0,
                'uniques' => $stats['daily'][ $d_key ]['uniques'] ?? 0,
            );
        }

        $max_daily_views = 1;
        foreach ( $last_14_days as $d ) {
            if ( $d['views'] > $max_daily_views ) {
                $max_daily_views = $d['views'];
            }
        }

        // En Çok Okunan Sayfalar
        $pages = $stats['pages'] ?? array();
        uasort( $pages, function($a, $b) {
            return ($b['views'] ?? 0) <=> ($a['views'] ?? 0);
        } );
        $top_pages = array_slice( $pages, 0, 10, true );

        // Trafik Kaynakları
        $sources = $stats['sources'] ?? array();
        arsort( $sources );
        $total_sources = array_sum( $sources );

        $reset_nonce = wp_create_nonce( 'ototamir_reset_visitor_nonce' );
        ?>
        <div class="wrap" style="max-width: 1240px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">

            <!-- ÜST BANNER (Canlı Nabız Göstergesi) -->
            <div style="background: linear-gradient(135deg, #090d16 0%, #111827 50%, #1e293b 100%); color: white; padding: 26px 32px; border-radius: 18px; margin: 20px 0 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); position:relative; overflow:hidden;">
                <div style="position:absolute; right:-40px; top:-40px; width:260px; height:260px; background:radial-gradient(circle, rgba(34,197,94,0.18) 0%, rgba(0,0,0,0) 70%); border-radius:50%; pointer-events:none;"></div>

                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; position:relative; z-index:2;">
                    <div>
                        <div style="display:inline-flex; align-items:center; gap:8px; background:rgba(34,197,94,0.15); border:1px solid rgba(34,197,94,0.4); color:#86efac; padding:4px 14px; border-radius:20px; font-size:12px; font-weight:700; margin-bottom:10px;">
                            <span style="width:9px; height:9px; border-radius:50%; background:#22c55e; display:inline-block; box-shadow: 0 0 10px #22c55e; animation: pulseGreen 2s infinite;"></span>
                            %100 Gerçek İnsan Trafiği & Anti-Bot Koruması
                        </div>
                        <h1 style="margin:0 0 6px; font-size:26px; font-weight:900; color:white; letter-spacing:-0.4px;">
                            📈 Canlı Ziyaretçi Takip & Trafik Analiz Paneli
                        </h1>
                        <p style="margin:0; font-size:13.5px; color:#94a3b8; max-width:700px;">
                            Googlebot, Bing, arama motoru örümcekleri ve otomatik botlar tamamen ayıklanarak sadece <strong>gerçek kullanıcıların</strong> ziyaretleri, baktıkları araç sayfaları ve trafik kaynakları listelenir.
                        </p>
                    </div>

                    <!-- Canlı Sayaç Kartı -->
                    <div style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.15); padding:16px 24px; border-radius:14px; text-align:center; min-width:180px;">
                        <div style="display:flex; align-items:center; justify-content:center; gap:8px; margin-bottom:4px;">
                            <span style="width:10px; height:10px; border-radius:50%; background:#22c55e; display:inline-block; animation: pulseGreen 1.5s infinite;"></span>
                            <span style="font-size:12px; font-weight:700; color:#86efac; text-transform:uppercase; letter-spacing:0.5px;">Şu An Sitede</span>
                        </div>
                        <span style="font-size:38px; font-weight:900; color:#ffffff; line-height:1; display:block;">
                            <?php echo intval( $live_count ); ?>
                        </span>
                        <span style="font-size:11.5px; color:#94a3b8;">Canlı İnsan Ziyaretçi</span>
                    </div>
                </div>
            </div>

            <!-- İLAN MÜŞTERİ AKSİYONLARI & DÖNÜŞÜM METRİKLERİ (TELEFON, WHATSAPP, KONUM) -->
            <div style="background:linear-gradient(135deg, #fff7ed 0%, #fff 100%); border:2px solid #fed7aa; border-radius:18px; padding:24px; margin-bottom:28px; box-shadow:0 4px 15px rgba(234,88,12,0.06);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:12px; border-bottom:1px solid #fed7aa; padding-bottom:14px;">
                    <div>
                        <div style="display:inline-flex; align-items:center; gap:6px; background:#ffedd5; color:#c2410c; padding:3px 10px; border-radius:12px; font-size:11.5px; font-weight:800; margin-bottom:6px; text-transform:uppercase;">
                            <i class="fa-solid fa-crosshairs"></i> MÜŞTERİ DÖNÜŞÜM & LEAD RADARI
                        </div>
                        <h2 style="margin:0 0 4px; font-size:22px; font-weight:900; color:#0f172a; display:flex; align-items:center; gap:8px;">
                            🎯 Kim, Hangi İlanda Nereye Tıkladı?
                        </h2>
                        <p style="margin:0; font-size:13.5px; color:#64748b;">
                            İlan detay sayfalarına giren ziyaretçilerin açık adrese, telefon numarasına veya WhatsApp'a tıklama hareketleri.
                        </p>
                    </div>
                    <span style="background:#ea580c; color:#ffffff; font-size:13px; font-weight:800; padding:8px 16px; border-radius:24px; box-shadow:0 3px 8px rgba(234,88,12,0.3);">
                        Toplam <?php echo number_format_i18n( $total_leads ); ?> Müşteri Aksiyonu
                    </span>
                </div>

                <!-- 4 METRİK KARTI -->
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:22px;">
                    <!-- TELEFON -->
                    <div style="background:white; border:1px solid #fed7aa; border-radius:12px; padding:18px; box-shadow:0 2px 6px rgba(0,0,0,0.02); border-left:4px solid #ea580c;">
                        <div style="font-size:12px; font-weight:700; color:#9a3412; text-transform:uppercase; display:flex; justify-content:space-between; align-items:center;">
                            <span>📞 Telefonanrufe</span>
                            <span style="background:#ffedd5; color:#ea580c; width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px;">
                                <i class="fa-solid fa-phone"></i>
                            </span>
                        </div>
                        <div style="font-size:32px; font-weight:900; color:#0f172a; margin:8px 0 2px;">
                            <?php echo number_format_i18n( $total_calls ); ?>
                        </div>
                        <div style="font-size:12px; color:#64748b;">
                            Klicks auf "Jetzt anrufen"
                        </div>
                    </div>

                    <!-- WHATSAPP -->
                    <div style="background:white; border:1px solid #bbf7d0; border-radius:12px; padding:18px; box-shadow:0 2px 6px rgba(0,0,0,0.02); border-left:4px solid #10b981;">
                        <div style="font-size:12px; font-weight:700; color:#166534; text-transform:uppercase; display:flex; justify-content:space-between; align-items:center;">
                            <span>💬 WhatsApp-Anfragen</span>
                            <span style="background:#dcfce7; color:#10b981; width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px;">
                                <i class="fa-brands fa-whatsapp" style="font-size:16px;"></i>
                            </span>
                        </div>
                        <div style="font-size:32px; font-weight:900; color:#0f172a; margin:8px 0 2px;">
                            <?php echo number_format_i18n( $total_whatsapp ); ?>
                        </div>
                        <div style="font-size:12px; color:#64748b;">
                            Klicks auf "Per WhatsApp kontaktieren"
                        </div>
                    </div>

                    <!-- YOL TARİFİ / KONUM -->
                    <div style="background:white; border:1px solid #bae6fd; border-radius:12px; padding:18px; box-shadow:0 2px 6px rgba(0,0,0,0.02); border-left:4px solid #0284c7;">
                        <div style="font-size:12px; font-weight:700; color:#075985; text-transform:uppercase; display:flex; justify-content:space-between; align-items:center;">
                            <span>📍 Routenplaner &amp; Anfahrt</span>
                            <span style="background:#e0f2fe; color:#0284c7; width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px;">
                                <i class="fa-solid fa-diamond-turn-right"></i>
                            </span>
                        </div>
                        <div style="font-size:32px; font-weight:900; color:#0f172a; margin:8px 0 2px;">
                            <?php echo number_format_i18n( $total_directions ); ?>
                        </div>
                        <div style="font-size:12px; color:#64748b;">
                            Klicks auf Google Maps Routenplanung
                        </div>
                    </div>

                    <!-- ACİL YOL YARDIM -->
                    <div style="background:white; border:1px solid #fecaca; border-radius:12px; padding:18px; box-shadow:0 2px 6px rgba(0,0,0,0.02); border-left:4px solid #dc2626;">
                        <div style="font-size:12px; font-weight:700; color:#991b1b; text-transform:uppercase; display:flex; justify-content:space-between; align-items:center;">
                            <span>🚨 Notdienst-Standort</span>
                            <span style="background:#fee2e2; color:#dc2626; width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px;">
                                <i class="fa-solid fa-location-crosshairs"></i>
                            </span>
                        </div>
                        <div style="font-size:32px; font-weight:900; color:#0f172a; margin:8px 0 2px;">
                            <?php echo number_format_i18n( $total_location_wp ); ?>
                        </div>
                        <div style="font-size:12px; color:#64748b;">
                            Live-Standortfreigaben bei Pannen
                        </div>
                    </div>
                </div>

                <!-- 🏆 EN ÇOK ARANAN & WHATSAPP'TAN TIKLANAN ŞAMPİYON FİRMALAR (LİDERLER TABLOSU) -->
                <?php
                $leaderboard = get_option( self::OPTION_LEAD_LEADERBOARD, array() );
                if ( ! is_array( $leaderboard ) ) {
                    $leaderboard = array();
                }

                // Veritabanındaki kayıtları da çekip birleştir (eksik veri kalmasın)
                $db_top_posts = get_posts( array(
                    'post_type'      => 'mechanic',
                    'post_status'    => 'publish',
                    'posts_per_page' => 20,
                    'meta_key'       => '_mechanic_leads_total',
                    'orderby'        => 'meta_value_num',
                    'order'          => 'DESC',
                    'meta_query'     => array(
                        array(
                            'key'     => '_mechanic_leads_total',
                            'value'   => 0,
                            'compare' => '>',
                            'type'    => 'NUMERIC',
                        ),
                    ),
                ) );

                if ( ! empty( $db_top_posts ) ) {
                    foreach ( $db_top_posts as $dp ) {
                        $p_id = $dp->ID;
                        $p_total = (int) get_post_meta( $p_id, '_mechanic_leads_total', true );
                        if ( $p_total > 0 ) {
                            $p_calls       = (int) get_post_meta( $p_id, '_mechanic_call_count', true );
                            $p_whatsapp    = (int) get_post_meta( $p_id, '_mechanic_whatsapp_count', true );
                            $p_directions  = (int) get_post_meta( $p_id, '_mechanic_directions_count', true );
                            $p_location_wp = (int) get_post_meta( $p_id, '_mechanic_location_wp_count', true );
                            $p_phone       = get_post_meta( $p_id, '_mechanic_phone', true );

                            if ( ! isset( $leaderboard[ $p_id ] ) ) {
                                $leaderboard[ $p_id ] = array(
                                    'post_id'     => $p_id,
                                    'title'       => $dp->post_title,
                                    'url'         => get_permalink( $p_id ),
                                    'phone'       => $p_phone,
                                    'call'        => $p_calls,
                                    'whatsapp'    => $p_whatsapp,
                                    'directions'  => $p_directions,
                                    'location_wp' => $p_location_wp,
                                    'total'       => $p_total,
                                    'last_action' => get_post_modified_time( 'U', false, $dp ),
                                );
                            } else {
                                $leaderboard[ $p_id ]['call']        = max( $leaderboard[ $p_id ]['call'] ?? 0, $p_calls );
                                $leaderboard[ $p_id ]['whatsapp']    = max( $leaderboard[ $p_id ]['whatsapp'] ?? 0, $p_whatsapp );
                                $leaderboard[ $p_id ]['directions']  = max( $leaderboard[ $p_id ]['directions'] ?? 0, $p_directions );
                                $leaderboard[ $p_id ]['location_wp'] = max( $leaderboard[ $p_id ]['location_wp'] ?? 0, $p_location_wp );
                                $leaderboard[ $p_id ]['total']       = max( $leaderboard[ $p_id ]['total'] ?? 0, $p_total );
                                if ( empty( $leaderboard[ $p_id ]['phone'] ) ) {
                                    $leaderboard[ $p_id ]['phone'] = $p_phone;
                                }
                            }
                        }
                    }
                }

                uasort( $leaderboard, function( $a, $b ) {
                    return ( $b['total'] ?? 0 ) <=> ( $a['total'] ?? 0 );
                } );

                $top_champion = ! empty( $leaderboard ) ? reset( $leaderboard ) : null;
                $top_list     = array_slice( $leaderboard, 0, 10, true );
                ?>

                <!-- 🌟 ŞAMPİYON FİRMA SPOTLIGHT VURGU KARTI -->
                <?php if ( $top_champion && ( $top_champion['total'] ?? 0 ) > 0 ) : 
                    $champ_post_id = $top_champion['post_id'] ?? 0;
                    $is_vip = get_post_meta( $champ_post_id, '_mechanic_is_featured', true ) === '1';
                    $city_terms = wp_get_post_terms( $champ_post_id, 'mechanic_city', array('fields' => 'names') );
                    $champ_city = ! empty( $city_terms ) && ! is_wp_error( $city_terms ) ? $city_terms[0] : '';
                    $service_terms = wp_get_post_terms( $champ_post_id, 'service_type', array('fields' => 'names') );
                    $champ_service = ! empty( $service_terms ) && ! is_wp_error( $service_terms ) ? $service_terms[0] : '';
                ?>
                    <div style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 50%, #fff7ed 100%); border: 2px solid #f59e0b; border-radius: 16px; padding: 22px 26px; margin-bottom: 24px; box-shadow: 0 4px 20px rgba(245, 158, 11, 0.15); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
                        <div style="display: flex; align-items: center; gap: 18px; flex: 1; min-width: 280px;">
                            <div style="width: 64px; height: 64px; border-radius: 16px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 30px; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.35); flex-shrink: 0;">
                                🏆
                            </div>
                            <div>
                                <div style="display: inline-flex; align-items: center; gap: 6px; background: #fef08a; color: #854d0e; padding: 2px 10px; border-radius: 10px; font-size: 11px; font-weight: 800; text-transform: uppercase; margin-bottom: 5px;">
                                    👑 SİTENİN EN ÇOK ARANAN & TIKLANAN 1. ŞAMPİYON FİRMASI
                                </div>
                                <h3 style="margin: 0 0 4px; font-size: 20px; font-weight: 900; color: #0f172a;">
                                    🚗 <?php echo esc_html( $top_champion['title'] ); ?>
                                    <?php if ( $is_vip ) : ?>
                                        <span style="font-size: 11.5px; background: #fef3c7; color: #b45309; border: 1px solid #fcd34d; padding: 2px 7px; border-radius: 6px; font-weight: 800; vertical-align: middle;">VIP Vitrin</span>
                                    <?php endif; ?>
                                </h3>
                                <div style="font-size: 12.5px; color: #64748b; display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
                                    <?php if ( ! empty( $top_champion['phone'] ) ) : ?>
                                        <span style="color: #0f172a; font-weight: 700;">📞 <?php echo esc_html( $top_champion['phone'] ); ?></span>
                                    <?php endif; ?>
                                    <?php if ( $champ_city ) : ?>
                                        <span>📍 <?php echo esc_html( $champ_city ); ?></span>
                                    <?php endif; ?>
                                    <?php if ( $champ_service ) : ?>
                                        <span style="background: #e2e8f0; padding: 1px 7px; border-radius: 4px; font-size: 11.5px;"><?php echo esc_html( $champ_service ); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Şampiyon Metrikleri & Hızlı Butonlar -->
                        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                            <div style="display: flex; gap: 8px;">
                                <div style="background: white; border: 1px solid #fcd34d; border-radius: 10px; padding: 8px 12px; text-align: center;">
                                    <div style="font-size: 10px; font-weight: 800; color: #ea580c; text-transform: uppercase;">Arama</div>
                                    <div style="font-size: 18px; font-weight: 900; color: #0f172a;">📞 <?php echo esc_html( $top_champion['call'] ?? 0 ); ?></div>
                                </div>
                                <div style="background: white; border: 1px solid #fcd34d; border-radius: 10px; padding: 8px 12px; text-align: center;">
                                    <div style="font-size: 10px; font-weight: 800; color: #16a34a; text-transform: uppercase;">WhatsApp</div>
                                    <div style="font-size: 18px; font-weight: 900; color: #0f172a;">💬 <?php echo esc_html( $top_champion['whatsapp'] ?? 0 ); ?></div>
                                </div>
                                <div style="background: white; border: 1px solid #fcd34d; border-radius: 10px; padding: 8px 12px; text-align: center;">
                                    <div style="font-size: 10px; font-weight: 800; color: #0284c7; text-transform: uppercase;">Harita/Yol</div>
                                    <div style="font-size: 18px; font-weight: 900; color: #0f172a;">📍 <?php echo esc_html( $top_champion['directions'] ?? 0 ); ?></div>
                                </div>
                                <div style="background: #0f172a; color: white; border-radius: 10px; padding: 8px 16px; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                                    <div style="font-size: 10px; font-weight: 800; color: #f59e0b; text-transform: uppercase;">Toplam İletişim</div>
                                    <div style="font-size: 20px; font-weight: 900; color: #fff;">🎯 <?php echo esc_html( $top_champion['total'] ?? 0 ); ?></div>
                                </div>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                <a href="<?php echo esc_url( get_edit_post_link( $champ_post_id ) ); ?>" class="button button-primary" style="background: #d97706; border-color: #b45309; font-weight: 700; text-align: center;">
                                    ✏ Düzenle
                                </a>
                                <?php if ( ! empty( $top_champion['url'] ) ) : ?>
                                    <a href="<?php echo esc_url( $top_champion['url'] ); ?>" target="_blank" class="button" style="font-size: 11.5px; text-align: center;">
                                        İlanı İncele ↗
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 📊 EN ÇOK TIKLANAN / İLETİŞİM ALAN FİRMALAR SIRALAMASI (TOP 10 LİDER TABLOSU) -->
                <div style="background:white; border:1px solid #e2e8f0; border-radius:14px; overflow:hidden; margin-bottom:24px;">
                    <div style="padding:16px 20px; background:#fafafa; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                        <div>
                            <h4 style="margin:0 0 2px; font-size:16px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px;">
                                🏆 En Çok Aranan & WhatsApp'tan Tıklama Alan Firmalar (Lider Tablosu)
                            </h4>
                            <p style="margin:0; font-size:12.5px; color:#64748b;">
                                Müşterilerin sitede en çok telefon açtığı, WhatsApp'tan yazdığı ve dükkanına rota çizdiği şampiyon tamirciler.
                            </p>
                        </div>
                        <span style="font-size:11.5px; font-weight:700; background:#f1f5f9; color:#475569; padding:4px 10px; border-radius:12px;">
                            Top <?php echo count( $top_list ); ?> Popüler Firma
                        </span>
                    </div>

                    <?php if ( ! empty( $top_list ) ) : ?>
                        <div style="overflow-x:auto;">
                            <table class="wp-list-table widefat fixed striped" style="border:none;">
                                <thead>
                                    <tr>
                                        <th style="font-weight:700; width:70px; text-align:center;">Sıra</th>
                                        <th style="font-weight:700;">Firma / Usta Adı</th>
                                        <th style="font-weight:700; width:130px; text-align:center;">📞 Telefon Arama</th>
                                        <th style="font-weight:700; width:130px; text-align:center;">💬 WhatsApp</th>
                                        <th style="font-weight:700; width:120px; text-align:center;">📍 Harita / Rota</th>
                                        <th style="font-weight:700; width:140px; text-align:center;">🎯 Toplam Müşteri</th>
                                        <th style="font-weight:700; width:120px; text-align:center;">İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $rank = 1;
                                    foreach ( $top_list as $firm ) : 
                                        $f_id = $firm['post_id'] ?? 0;
                                        $f_vip = get_post_meta( $f_id, '_mechanic_is_featured', true ) === '1';
                                        
                                        $rank_badge = '';
                                        if ( $rank === 1 ) {
                                            $rank_badge = '<span style="font-size:18px;">🥇</span>';
                                        } elseif ( $rank === 2 ) {
                                            $rank_badge = '<span style="font-size:18px;">🥈</span>';
                                        } elseif ( $rank === 3 ) {
                                            $rank_badge = '<span style="font-size:18px;">🥉</span>';
                                        } else {
                                            $rank_badge = '<strong style="color:#64748b; font-size:14px;">#' . $rank . '</strong>';
                                        }
                                    ?>
                                        <tr>
                                            <td style="text-align:center; vertical-align:middle;">
                                                <?php echo $rank_badge; ?>
                                            </td>
                                            <td style="vertical-align:middle;">
                                                <div style="font-weight:800; font-size:14.5px; color:#0f172a; display:flex; align-items:center; gap:8px;">
                                                    <a href="<?php echo esc_url( get_edit_post_link( $f_id ) ); ?>" style="text-decoration:none; color:#0f172a;">
                                                        <?php echo esc_html( $firm['title'] ); ?>
                                                    </a>
                                                    <?php if ( $f_vip ) : ?>
                                                        <span style="font-size:10px; background:#fef3c7; color:#92400e; padding:1px 6px; border-radius:4px; font-weight:800;">VIP</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div style="font-size:12px; color:#64748b; margin-top:2px;">
                                                    <?php if ( ! empty( $firm['phone'] ) ) : ?>
                                                        <span>📞 <?php echo esc_html( $firm['phone'] ); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td style="text-align:center; vertical-align:middle;">
                                                <span style="background:#ffedd5; color:#c2410c; padding:4px 10px; border-radius:8px; font-weight:800; font-size:13px; display:inline-block; min-width:35px;">
                                                    <?php echo (int) ( $firm['call'] ?? 0 ); ?>
                                                </span>
                                            </td>
                                            <td style="text-align:center; vertical-align:middle;">
                                                <span style="background:#dcfce7; color:#15803d; padding:4px 10px; border-radius:8px; font-weight:800; font-size:13px; display:inline-block; min-width:35px;">
                                                    <?php echo (int) ( $firm['whatsapp'] ?? 0 ); ?>
                                                </span>
                                            </td>
                                            <td style="text-align:center; vertical-align:middle;">
                                                <span style="background:#e0f2fe; color:#0369a1; padding:4px 10px; border-radius:8px; font-weight:800; font-size:13px; display:inline-block; min-width:35px;">
                                                    <?php echo (int) ( $firm['directions'] ?? 0 ); ?>
                                                </span>
                                            </td>
                                            <td style="text-align:center; vertical-align:middle;">
                                                <span style="background:#0f172a; color:#f8fafc; padding:5px 12px; border-radius:20px; font-weight:900; font-size:13.5px; display:inline-block; box-shadow:0 2px 6px rgba(0,0,0,0.1);">
                                                    🎯 <?php echo (int) ( $firm['total'] ?? 0 ); ?>
                                                </span>
                                            </td>
                                            <td style="text-align:center; vertical-align:middle;">
                                                <div style="display:inline-flex; gap:6px;">
                                                    <a href="<?php echo esc_url( get_edit_post_link( $f_id ) ); ?>" class="button button-small" title="Düzenle">
                                                        ✏ Düzenle
                                                    </a>
                                                    <?php if ( ! empty( $firm['url'] ) ) : ?>
                                                        <a href="<?php echo esc_url( $firm['url'] ); ?>" target="_blank" class="button button-small" title="Görüntüle">
                                                            ↗
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php 
                                        $rank++;
                                    endforeach; 
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div style="text-align:center; padding:35px 20px; color:#94a3b8; background:#fff;">
                            <i class="fa-solid fa-trophy" style="font-size:32px; margin-bottom:10px; color:#fde68a; display:block;"></i>
                            <h4 style="margin:0 0 6px; font-size:15px; color:#475569;">Henüz Tıklama Alan Lider Firma Yok</h4>
                            <p style="margin:0; font-size:12.5px;">Ziyaretçiler tamircilerin telefon numaralarına veya WhatsApp butonlarına tıkladıkça en çok aranan firmalar burada liderlik tablosunda listelenir.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- CANLI MÜŞTERİ EYLEMLERİ AKIŞI TABLOSU -->
                <div style="background:white; border:1px solid #e2e8f0; border-radius:14px; overflow:hidden;">
                    <div style="padding:14px 20px; background:#f8fafc; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
                        <h4 style="margin:0; font-size:15px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px;">
                            <i class="fa-solid fa-bolt" style="color:#f59e0b;"></i> Son Gerçekleşen Müşteri Eylemleri Günlüğü
                        </h4>
                        <span style="font-size:12px; color:#64748b;">Anlık Otomatik Takip Edilir</span>
                    </div>

                    <?php if ( ! empty( $recent_leads ) ) : ?>
                        <div style="overflow-x:auto;">
                            <table class="wp-list-table widefat fixed striped" style="border:none;">
                                <thead>
                                    <tr>
                                        <th style="font-weight:700; width:130px;">Zaman</th>
                                        <th style="font-weight:700; width:240px;">Hangi İlan / Firma?</th>
                                        <th style="font-weight:700;">Nereye Tıkladı & Ne Yaptı?</th>
                                        <th style="font-weight:700; width:160px;">Cihaz & Tarayıcı</th>
                                        <th style="font-weight:700; width:130px;">Maskeli IP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ( $recent_leads as $lead ) : 
                                        $time_diff = human_time_diff( $lead['time'], $now ) . ' önce';
                                        $dev_icon = $lead['device'] === 'mobile' ? 'fa-mobile-screen' : ( $lead['device'] === 'tablet' ? 'fa-tablet-screen-button' : 'fa-laptop' );
                                    ?>
                                        <tr>
                                            <td>
                                                <span style="font-size:12px; font-weight:700; color:#0f172a; display:block;"><?php echo esc_html( $time_diff ); ?></span>
                                                <small style="color:#94a3b8; font-size:11px;"><?php echo date_i18n( 'H:i:s', $lead['time'] ); ?></small>
                                            </td>
                                            <td>
                                                <div style="font-weight:800; font-size:14px; color:#0f172a;">
                                                    🚗 <?php echo esc_html( $lead['title'] ); ?>
                                                </div>
                                                <?php if ( ! empty( $lead['url'] ) ) : ?>
                                                    <a href="<?php echo esc_url( $lead['url'] ); ?>" target="_blank" style="font-size:11.5px; color:#0284c7; text-decoration:none; font-weight:600;">
                                                        İlanı Yeni Sekmede Gör ↗
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div style="display:inline-block; margin-bottom:4px;">
                                                    <span style="background:<?php echo esc_attr( $lead['action_bg'] ?? '#f1f5f9' ); ?>; color:<?php echo esc_attr( $lead['action_color'] ?? '#334155' ); ?>; padding:4px 8px; border-radius:6px; font-size:11.5px; font-weight:800; display:inline-flex; align-items:center; gap:6px;">
                                                        <i class="fa-solid <?php echo esc_attr( $lead['action_icon'] ?? 'fa-bolt' ); ?>"></i>
                                                        <?php echo esc_html( $lead['action_label'] ?? 'Etkileşim' ); ?>
                                                    </span>
                                                </div>
                                                <div style="font-size:13px; font-weight:700; color:#0f172a;">
                                                    <?php echo esc_html( $lead['action_detail'] ?? $lead['action_label'] ); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span style="display:inline-flex; align-items:center; gap:6px; font-size:12px; color:#475569;">
                                                    <i class="fa-solid <?php echo esc_attr( $dev_icon ); ?>" style="color:#ea580c;"></i>
                                                    <?php echo esc_html( ( $lead['os'] ?? '' ) . ' · ' . ( $lead['browser'] ?? '' ) ); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <code style="font-size:11.5px; background:#f8fafc; padding:2px 6px; border-radius:4px; color:#64748b;"><?php echo esc_html( $lead['ip'] ?? '' ); ?></code>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div style="text-align:center; padding:35px 20px; color:#94a3b8; background:#fff;">
                            <i class="fa-solid fa-bullseye" style="font-size:32px; margin-bottom:10px; color:#cbd5e1; display:block;"></i>
                            <h4 style="margin:0 0 6px; font-size:15px; color:#475569;">Henüz Kayıtlı Müşteri Eylemi Yok</h4>
                            <p style="margin:0; font-size:12.5px;">Ziyaretçiler bir firmanın telefonuna, WhatsApp'ına veya açık adresine tıkladığında burada canlı olarak görünecektir.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 4'LÜ TEMEL METRİK KARTLARI (GENEL TRAFİK) -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(230px, 1fr)); gap:18px; margin-bottom:26px;">
                <!-- Bugün Tekil -->
                <div style="background:white; border:1px solid #e2e8f0; border-radius:14px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,0.02);">
                    <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; display:flex; justify-content:space-between;">
                        <span>👤 Bugünün Tekil İnsanı</span>
                        <i class="fa-solid fa-user-check" style="color:#10b981;"></i>
                    </div>
                    <div style="font-size:30px; font-weight:900; color:#0f172a; margin:8px 0 2px;">
                        <?php echo number_format_i18n( $today_uniques ); ?>
                    </div>
                    <div style="font-size:12px; color:#10b981; font-weight:600;">
                        Toplam: <?php echo number_format_i18n( $total_uniques ); ?> tekil ziyaretçi
                    </div>
                </div>

                <!-- Bugün Sayfa Gösterimi -->
                <div style="background:white; border:1px solid #e2e8f0; border-radius:14px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,0.02);">
                    <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; display:flex; justify-content:space-between;">
                        <span>📄 Bugün Sayfa Gösterimi</span>
                        <i class="fa-solid fa-eye" style="color:#3b82f6;"></i>
                    </div>
                    <div style="font-size:30px; font-weight:900; color:#0f172a; margin:8px 0 2px;">
                        <?php echo number_format_i18n( $today_views ); ?>
                    </div>
                    <div style="font-size:12px; color:#3b82f6; font-weight:600;">
                        Toplam: <?php echo number_format_i18n( $total_views ); ?> gösterim
                    </div>
                </div>

                <!-- Mobil Oranı -->
                <div style="background:white; border:1px solid #e2e8f0; border-radius:14px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,0.02);">
                    <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; display:flex; justify-content:space-between;">
                        <span>📱 Mobil Ziyaretçi</span>
                        <i class="fa-solid fa-mobile-screen" style="color:#f97316;"></i>
                    </div>
                    <div style="font-size:30px; font-weight:900; color:#ea580c; margin:8px 0 2px;">
                        %<?php echo $mobile_pct; ?>
                    </div>
                    <div style="font-size:12px; color:#64748b;">
                        Masaüstü: %<?php echo $desktop_pct; ?>
                    </div>
                </div>

                <!-- Engellenen Bot Sayısı -->
                <div style="background:white; border:1px solid #e2e8f0; border-radius:14px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,0.02);">
                    <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; display:flex; justify-content:space-between;">
                        <span>🛡️ Ayıklanan Bot / Örümcek</span>
                        <i class="fa-solid fa-shield-halved" style="color:#8b5cf6;"></i>
                    </div>
                    <div style="font-size:30px; font-weight:900; color:#6366f1; margin:8px 0 2px;">
                        <?php echo number_format_i18n( $blocked_bots ); ?>
                    </div>
                    <div style="font-size:12px; color:#8b5cf6; font-weight:600;">
                        İstatistikleri kirletmeden engellendi
                    </div>
                </div>
            </div>

            <!-- SON 14 GÜNÜN TRAFİK GRAFİĞİ -->
            <div style="background:white; border:1px solid #e2e8f0; border-radius:16px; padding:24px; margin-bottom:28px; box-shadow:0 2px 8px rgba(0,0,0,0.02);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
                    <div>
                        <h3 style="margin:0 0 4px; font-size:17px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px;">
                            <i class="fa-solid fa-chart-column" style="color:#ea580c;"></i> Son 14 Günün Günlük Trafik Dağılımı
                        </h3>
                        <p style="margin:0; font-size:13px; color:#64748b;">Gün bazında tekil insan ziyaretçi ve toplam sayfa görüntüleme trendi.</p>
                    </div>
                    <div style="display:flex; align-items:center; gap:16px; font-size:12.5px; font-weight:700;">
                        <span style="display:inline-flex; align-items:center; gap:6px; color:#0f172a;">
                            <span style="width:12px; height:12px; border-radius:3px; background:#ea580c;"></span> Sayfa Görüntüleme
                        </span>
                        <span style="display:inline-flex; align-items:center; gap:6px; color:#64748b;">
                            <span style="width:12px; height:12px; border-radius:3px; background:#38bdf8;"></span> Tekil İnsan
                        </span>
                    </div>
                </div>

                <!-- CSS Bar Chart -->
                <div style="display:flex; align-items:flex-end; gap:10px; height:180px; padding:10px 0 24px; border-bottom:1px solid #e2e8f0;">
                    <?php foreach ( $last_14_days as $d_key => $d_val ) : 
                        $pct_views = $max_daily_views > 0 ? round( ($d_val['views'] / $max_daily_views) * 100 ) : 0;
                        $pct_views = max( 4, $pct_views ); // En azından 4% yükseklik
                    ?>
                        <div style="flex:1; display:flex; flex-direction:column; align-items:center; height:100%; justify-content:flex-end; position:relative;" title="<?php echo esc_attr( $d_val['label'] . ': ' . $d_val['views'] . ' Görüntüleme, ' . $d_val['uniques'] . ' Tekil Ziyaretçi' ); ?>">
                            <div style="font-size:10.5px; color:#64748b; font-weight:700; margin-bottom:4px;">
                                <?php echo intval( $d_val['views'] ); ?>
                            </div>
                            <div style="width:100%; max-width:28px; height:<?php echo $pct_views; ?>%; background:linear-gradient(180deg, #ea580c 0%, #f97316 100%); border-radius:4px 4px 0 0; transition:all 0.2s;"></div>
                            <span style="font-size:10.5px; color:#94a3b8; margin-top:6px; white-space:nowrap; position:absolute; bottom:-20px;">
                                <?php echo esc_html( $d_val['label'] ); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- İKİ KOLONLU DAĞILIM: EN ÇOK GEZİLEN SAYFALAR & TRAFİK KAYNAKLARI -->
            <div style="display:grid; grid-template-columns: 1.4fr 1fr; gap:22px; margin-bottom:28px;">
                <!-- En Çok Gezilen Sayfalar -->
                <div style="background:white; border:1px solid #e2e8f0; border-radius:16px; padding:22px; box-shadow:0 2px 8px rgba(0,0,0,0.02);">
                    <h3 style="margin:0 0 16px; font-size:16px; font-weight:800; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:12px; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-fire" style="color:#ef4444;"></i> En Çok Ziyaret Edilen Sayfalar
                    </h3>
                    <?php if ( ! empty( $top_pages ) ) : ?>
                        <div style="display:flex; flex-direction:column; gap:12px;">
                            <?php foreach ( $top_pages as $p_path => $p_item ) : 
                                $p_pct = $total_views > 0 ? round( ( $p_item['views'] / $total_views ) * 100 ) : 0;
                            ?>
                                <div>
                                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:13px; margin-bottom:4px; gap:8px;">
                                        <a href="<?php echo esc_url( home_url( $p_path ) ); ?>" target="_blank" style="color:#0f172a; text-decoration:none; font-weight:700; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:380px;">
                                            <?php echo esc_html( $p_item['title'] ?: $p_path ); ?>
                                            <small style="color:#94a3b8; font-weight:400; font-size:11px;">(<?php echo esc_html( $p_path ); ?>)</small>
                                        </a>
                                        <span style="font-weight:800; color:#ea580c; white-space:nowrap;">
                                            <?php echo number_format_i18n( $p_item['views'] ); ?> <small style="color:#64748b; font-weight:400;">(%<?php echo $p_pct; ?>)</small>
                                        </span>
                                    </div>
                                    <div style="height:5px; background:#f1f5f9; border-radius:3px; overflow:hidden;">
                                        <div style="width:<?php echo $p_pct; ?>%; height:100%; background:#ea580c; border-radius:3px;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p style="color:#94a3b8; font-size:13.5px; margin:0;">Henüz sayfa ziyareti kaydedilmedi.</p>
                    <?php endif; ?>
                </div>

                <!-- Trafik Kaynakları (Referrer) -->
                <div style="background:white; border:1px solid #e2e8f0; border-radius:16px; padding:22px; box-shadow:0 2px 8px rgba(0,0,0,0.02);">
                    <h3 style="margin:0 0 16px; font-size:16px; font-weight:800; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:12px; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-compass" style="color:#0284c7;"></i> Ziyaretçiler Nereden Geliyor?
                    </h3>
                    <?php if ( ! empty( $sources ) ) : ?>
                        <div style="display:flex; flex-direction:column; gap:12px;">
                            <?php 
                            $source_map_names = array(
                                'direct'    => 'Doğrudan Giriş (Direct)',
                                'google'    => 'Google Arama (Organik)',
                                'yandex'    => 'Yandex Arama',
                                'instagram' => 'Instagram',
                                'facebook'  => 'Facebook',
                                'tiktok'    => 'TikTok',
                                'twitter'   => 'X (Twitter)',
                                'whatsapp'  => 'WhatsApp',
                            );
                            foreach ( $sources as $src_key => $src_count ) : 
                                $src_name = $source_map_names[ $src_key ] ?? ucfirst( $src_key );
                                $src_pct = $total_sources > 0 ? round( ( $src_count / $total_sources ) * 100 ) : 0;
                            ?>
                                <div>
                                    <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:700; color:#1e293b; margin-bottom:4px;">
                                        <span><?php echo esc_html( $src_name ); ?></span>
                                        <span><?php echo number_format_i18n( $src_count ); ?> <small style="color:#64748b; font-weight:400;">(%<?php echo $src_pct; ?>)</small></span>
                                    </div>
                                    <div style="height:5px; background:#f1f5f9; border-radius:3px; overflow:hidden;">
                                        <div style="width:<?php echo $src_pct; ?>%; height:100%; background:linear-gradient(90deg, #0284c7, #38bdf8); border-radius:3px;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p style="color:#94a3b8; font-size:13.5px; margin:0;">Henüz trafik kaynağı verisi toplanmadı.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- CANLI ZİYARET AKIŞI (SON 50 İNSAN ZİYARETÇİ) -->
            <div style="background:white; border:1px solid #e2e8f0; border-radius:16px; padding:24px; box-shadow:0 2px 8px rgba(0,0,0,0.02);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; flex-wrap:wrap; gap:12px;">
                    <div>
                        <h3 style="margin:0 0 4px; font-size:17px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px;">
                            <i class="fa-solid fa-clock-rotate-left" style="color:#ea580c;"></i> Canlı Ziyaret Akışı (Son 50 İnsan)
                        </h3>
                        <p style="margin:0; font-size:13px; color:#64748b;">Botlar hariç siteyi anlık ziyaret eden gerçek kullanıcıların hareket dökümü.</p>
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <button type="button" onclick="window.location.reload();" class="button button-secondary" style="font-weight:600; display:inline-flex; align-items:center; gap:6px;">
                            <i class="fa-solid fa-arrows-rotate"></i> Yenile
                        </button>
                        <button type="button" onclick="ototamirResetVisitorStats();" class="button" style="color:#ef4444; border-color:#fca5a5; font-weight:600;">
                            <i class="fa-solid fa-trash-can"></i> İstatistikleri Sıfırla
                        </button>
                    </div>
                </div>

                <?php if ( ! empty( $recent_logs ) ) : ?>
                    <div style="overflow-x:auto;">
                        <table class="wp-list-table widefat fixed striped" style="border:none;">
                            <thead>
                                <tr>
                                    <th style="font-weight:700; width:130px;">Zaman</th>
                                    <th style="font-weight:700;">Ziyaret Edilen Sayfa</th>
                                    <th style="font-weight:700; width:170px;">Geliş Kaynağı</th>
                                    <th style="font-weight:700; width:130px;">Cihaz & Tarayıcı</th>
                                    <th style="font-weight:700; width:130px;">Maskeli IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $recent_logs as $entry ) : 
                                    $time_diff = human_time_diff( $entry['time'], $now ) . ' önce';
                                    $dev_icon = $entry['device'] === 'mobile' ? 'fa-mobile-screen' : ( $entry['device'] === 'tablet' ? 'fa-tablet-screen-button' : 'fa-laptop' );
                                ?>
                                    <tr>
                                        <td>
                                            <span style="font-size:12px; font-weight:700; color:#0f172a; display:block;"><?php echo esc_html( $time_diff ); ?></span>
                                            <small style="color:#94a3b8; font-size:11px;"><?php echo date_i18n( 'H:i:s', $entry['time'] ); ?></small>
                                        </td>
                                        <td>
                                            <a href="<?php echo esc_url( $entry['url'] ); ?>" target="_blank" style="font-weight:700; color:#0f172a; text-decoration:none;">
                                                <?php echo esc_html( $entry['title'] ); ?>
                                            </a>
                                            <div style="color:#64748b; font-size:11px;"><?php echo esc_html( $entry['path'] ); ?></div>
                                        </td>
                                        <td>
                                            <span style="background:#f1f5f9; padding:4px 8px; border-radius:6px; font-size:11.5px; font-weight:600; color:#334155; display:inline-flex; align-items:center; gap:5px;">
                                                <i class="<?php echo esc_attr( $entry['source_icon'] ?? 'fa-globe' ); ?>" style="color:#ea580c; font-size:11px;"></i>
                                                <?php echo esc_html( $entry['source'] ); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span style="display:inline-flex; align-items:center; gap:6px; font-size:12px; color:#475569;">
                                                <i class="fa-solid <?php echo esc_attr( $dev_icon ); ?>" style="color:#ea580c;"></i>
                                                <?php echo esc_html( $entry['os'] . ' · ' . $entry['browser'] ); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <code style="font-size:11.5px; background:#f8fafc; padding:2px 6px; border-radius:4px; color:#64748b;"><?php echo esc_html( $entry['ip'] ); ?></code>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <div style="text-align:center; padding:40px 20px; color:#94a3b8;">
                        <i class="fa-solid fa-radar" style="font-size:36px; margin-bottom:12px; color:#cbd5e1; display:block;"></i>
                        <h4 style="margin:0 0 6px; font-size:16px; color:#475569;">Henüz Canlı Ziyaret Kaydı Yok</h4>
                        <p style="margin:0; font-size:13px;">Siteye bir ziyaretçi girdiğinde burada otomatik olarak belirecektir.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <style>
        @keyframes pulseGreen {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }
        </style>

        <script>
        function ototamirResetVisitorStats() {
            if (!confirm('Tüm ziyaretçi ve trafik istatistiklerini sıfırlamak istediğinize emin misiniz? Bu işlem geri alınamaz.')) {
                return;
            }
            var btn = event.target;
            btn.disabled = true;
            btn.innerText = 'Sıfırlanıyor...';

            var data = new FormData();
            data.append('action', 'ototamir_reset_visitor_stats');
            data.append('nonce', '<?php echo esc_js( $reset_nonce ); ?>');

            fetch(ajaxurl, {
                method: 'POST',
                body: data
            })
            .then(function(res) { return res.json(); })
            .then(function(resp) {
                if (resp.success) {
                    alert('İstatistikler başarıyla sıfırlandı.');
                    window.location.reload();
                } else {
                    alert('Hata: ' + (resp.data || 'Bir sorun oluştu.'));
                    btn.disabled = false;
                }
            })
            .catch(function() {
                alert('Sunucu ile bağlantı kurulamadı.');
                btn.disabled = false;
            });
        }
        </script>
        <?php
    }
}

OtoTamir_Visitor_Tracker::init();
