<?php
/**
 * OtoTamirciBul - Hızlı İndeksleme & Search Engine Push Motoru (IndexNow & Google Ping)
 * 
 * Özellikler:
 * 1. Otomatik IndexNow Protokolü (Bing, Yandex, Seznam, Naver anında bildirim)
 * 2. Yeni/Güncellenen İlan ve Yazılarda Anında İndeks İsteği (save_post)
 * 3. Otomatik Google ve Bing Sitemap Ping Mekanizması
 * 4. WP-Admin Üzerinden Tek Tıkla Tüm Siteyi (700+ İlan ve Şehir) Toplu Gönderme Aracı
 * 5. Canlı Doğrulama Anahtarı (/{key}.txt) Sunumu
 * 
 * @package OtoTamirciBul
 * @version 1.4.32
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OtoTamir_IndexNow {

    const OPTION_KEY       = 'ototamir_indexnow_api_key';
    const OPTION_LAST_SENT = 'ototamir_indexnow_last_batch_time';
    const OPTION_SENT_LOG  = 'ototamir_indexnow_recent_log';

    public static function init() {
        // Rewrite kuralı ve doğrudan /{key}.txt yakalama
        add_action( 'init', array( __CLASS__, 'handle_key_txt_request' ), 1 );
        add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ) );
        add_filter( 'query_vars', array( __CLASS__, 'add_query_vars' ) );
        add_action( 'template_redirect', array( __CLASS__, 'handle_key_txt_request' ), 1 );

        // Post / İlan yayınlandığında veya güncellendiğinde otomatik gönder
        add_action( 'transition_post_status', array( __CLASS__, 'on_post_status_transition' ), 10, 3 );

        // Admin Menüsü
        add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );

        // AJAX Toplu Gönderim
        add_action( 'wp_ajax_ototamir_indexnow_batch_submit', array( __CLASS__, 'ajax_batch_submit' ) );

        // Fiziksel anahtar dosyasını kontrol et ve gerekirse yaz
        add_action( 'admin_init', array( __CLASS__, 'ensure_key_file' ) );
    }

    /**
     * Benzersiz 32 Karakterli IndexNow Anahtarını Al veya Üret
     */
    public static function get_api_key() {
        $key = get_option( self::OPTION_KEY );
        if ( empty( $key ) || strlen( $key ) !== 32 ) {
            $salt = defined( 'AUTH_KEY' ) ? AUTH_KEY : 'ototamir_indexnow_default_salt';
            $key = md5( home_url() . $salt . time() );
            update_option( self::OPTION_KEY, $key );
        }
        return $key;
    }

    /**
     * Anahtar Doğrulama URL'si
     */
    public static function get_key_location() {
        $key = self::get_api_key();
        return home_url( '/' . $key . '.txt' );
    }

    /**
     * Rewrite Kuralı
     */
    public static function add_rewrite_rules() {
        $key = self::get_api_key();
        add_rewrite_rule( '^([a-f0-9]{32})\.txt$', 'index.php?ototamir_indexnow_verify=$matches[1]', 'top' );
    }

    public static function add_query_vars( $vars ) {
        $vars[] = 'ototamir_indexnow_verify';
        return $vars;
    }

    /**
     * Web Kök Dizinine {key}.txt Fiziksel Dosyası Yaz (Nginx/Apache Hızlı Yanıtı)
     */
    public static function ensure_key_file() {
        $key = self::get_api_key();
        $file_path = ABSPATH . $key . '.txt';
        if ( ! file_exists( $file_path ) && is_writable( ABSPATH ) ) {
            @file_put_contents( $file_path, $key );
        }
    }

    /**
     * /{key}.txt İstendiğinde Düz Metin Olarak Anahtarı Döndür
     */
    public static function handle_key_txt_request() {
        $req_key = get_query_var( 'ototamir_indexnow_verify' );
        
        if ( empty( $req_key ) ) {
            $req_uri = $_SERVER['REQUEST_URI'] ?? '';
            $path = trim( parse_url( $req_uri, PHP_URL_PATH ), '/' );
            if ( preg_match( '/^([a-f0-9]{32})\.txt$/', $path, $m ) ) {
                $req_key = $m[1];
            }
        }

        if ( ! empty( $req_key ) ) {
            $my_key = self::get_api_key();
            if ( $req_key === $my_key ) {
                status_header( 200 );
                header( 'Content-Type: text/plain; charset=utf-8' );
                header( 'X-Robots-Tag: noindex', true );
                echo esc_html( $my_key );
                exit;
            }
        }
    }

    /**
     * Bir veya Birden Fazla URL'yi IndexNow ve Arama Motorlarına İlet
     */
    public static function submit_urls( $urls = array() ) {
        if ( empty( $urls ) ) {
            return array( 'success' => false, 'message' => 'Gönderilecek URL listesi boş.' );
        }

        if ( is_string( $urls ) ) {
            $urls = array( $urls );
        }

        $urls = array_values( array_unique( array_filter( $urls ) ) );
        $key = self::get_api_key();
        $key_location = self::get_key_location();
        $host = parse_url( home_url(), PHP_URL_HOST );

        // 1. IndexNow API İsteği Hazırla
        $payload = array(
            'host'        => $host,
            'key'         => $key,
            'keyLocation' => $key_location,
            'urlList'     => array_slice( $urls, 0, 10000 ),
        );

        $body_json = wp_json_encode( $payload );

        $endpoints = array(
            'https://api.indexnow.org/indexnow',
            'https://www.bing.com/indexnow',
        );

        $statuses = array();

        foreach ( $endpoints as $endpoint ) {
            $resp = wp_remote_post( $endpoint, array(
                'headers'     => array(
                    'Content-Type' => 'application/json; charset=utf-8',
                    'User-Agent'   => 'OtoTamir360-IndexNow/1.0',
                ),
                'body'        => $body_json,
                'timeout'     => 12,
                'sslverify'   => true,
            ) );

            if ( is_wp_error( $resp ) ) {
                $statuses[] = array( 'endpoint' => $endpoint, 'code' => 500, 'error' => $resp->get_error_message() );
            } else {
                $code = wp_remote_retrieve_response_code( $resp );
                $statuses[] = array( 'endpoint' => $endpoint, 'code' => $code );
            }
        }

        // 2. Google & Bing Sitemap Ping
        self::ping_search_engines();

        // 3. Log Kaydet
        $log_entry = array(
            'time'  => current_time( 'mysql' ),
            'count' => count( $urls ),
            'first' => $urls[0],
        );
        update_option( self::OPTION_LAST_SENT, current_time( 'timestamp' ) );

        $logs = get_option( self::OPTION_SENT_LOG, array() );
        array_unshift( $logs, $log_entry );
        $logs = array_slice( $logs, 0, 15 );
        update_option( self::OPTION_SENT_LOG, $logs );

        return array(
            'success'  => true,
            'count'    => count( $urls ),
            'statuses' => $statuses,
        );
    }

    /**
     * Google & Bing Sitemap Ping Tetikle
     */
    public static function ping_search_engines() {
        $sitemap_url = urlencode( home_url( '/sitemap.xml' ) );
        
        $pings = array(
            'https://www.google.com/ping?sitemap=' . $sitemap_url,
            'https://www.bing.com/ping?sitemap=' . $sitemap_url,
        );

        foreach ( $pings as $ping_url ) {
            wp_remote_get( $ping_url, array(
                'timeout'   => 4,
                'blocking'  => false, // Arka planda non-blocking çalışır, sayfayı yavaşlatmaz
                'sslverify' => false,
            ) );
        }
    }

    /**
     * Yeni İlan / Post Yayınlandığında Otomatik Gönder
     */
    public static function on_post_status_transition( $new_status, $old_status, $post ) {
        if ( ! in_array( $post->post_type, array( 'mechanic', 'post', 'page' ), true ) ) {
            return;
        }

        // Sadece yayına alındığında veya yayındayken güncellendiğinde
        if ( $new_status === 'publish' ) {
            $url = get_permalink( $post->ID );
            if ( $url ) {
                self::submit_urls( array( $url ) );
            }
        }
    }

    /**
     * Admin Menü Kaydı
     */
    public static function add_admin_menu() {
        add_menu_page(
            __( 'Hızlı İndeksleme & SEO', 'ototamir' ),
            __( '🚀 Hızlı İndeks', 'ototamir' ),
            'manage_options',
            'ototamir-indexnow',
            array( __CLASS__, 'render_admin_page' ),
            'dashicons-performance',
            32
        );
    }

    /**
     * Admin Yönetim Sayfası Arayüzü
     */
    public static function render_admin_page() {
        $key = self::get_api_key();
        $key_loc = self::get_key_location();
        $last_sent = get_option( self::OPTION_LAST_SENT );
        $logs = get_option( self::OPTION_SENT_LOG, array() );
        $sitemap_url = home_url( '/sitemap.xml' );

        // Toplam indekslenebilir içerik sayımı
        global $wpdb;
        $total_mechs = (int) $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'mechanic' AND post_status = 'publish'" );
        $total_posts = (int) $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish'" );
        $total_cities = 81;
        $total_districts = 970;
        $total_pages = (int) $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status = 'publish'" );
        $estimated_urls = $total_mechs + $total_posts + $total_cities + $total_districts + $total_pages;
        ?>
        <div class="wrap" style="max-width: 1080px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
            <div style="background: linear-gradient(135deg, #0b1329 0%, #1e293b 100%); color: white; padding: 28px 34px; border-radius: 16px; margin: 20px 0 26px; box-shadow: 0 10px 30px rgba(15,23,42,0.15);">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:18px;">
                    <div>
                        <div style="display:inline-flex; align-items:center; gap:8px; background:rgba(249,115,22,0.2); color:#fed7aa; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700; margin-bottom:10px;">
                            <span style="width:8px; height:8px; border-radius:50%; background:#22c55e;"></span> Search Engine Direct Indexing Engine
                        </div>
                        <h1 style="margin:0 0 8px; font-size:26px; font-weight:900; color:white; letter-spacing:-0.4px;">
                            🚀 Hızlı Arama Motoru İndeksleme (IndexNow & Google)
                        </h1>
                        <p style="margin:0; font-size:14px; color:#cbd5e1; max-width:650px; line-height:1.5;">
                            Google, Microsoft Bing, Yandex ve Seznam botlarının sitenizdeki 81 il, ilçe ve usta sayfalarını haftalarca beklemeden <strong>dakikalar içinde keşfedip indekslemesini</strong> sağlayan protokol ve otomatik XML harita dağıtıcısı.
                        </p>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:10px; align-items:flex-end;">
                        <button type="button" id="btn-batch-indexnow" class="button button-primary" style="background:linear-gradient(135deg, #f97316 0%, #ea580c 100%); border:none; padding:12px 24px; height:auto; font-size:15px; font-weight:800; border-radius:12px; box-shadow:0 6px 20px rgba(234,88,12,0.4); cursor:pointer;">
                            ⚡ Tüm Siteyi Arama Motorlarına İlet (<?php echo esc_html( $estimated_urls ); ?>+ URL)
                        </button>
                        <div style="display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end;">
                            <button type="button" id="btn-sync-all-cities" class="button" style="background:#0284c7; color:white; border:none; padding:8px 16px; height:auto; font-size:13px; font-weight:700; border-radius:10px; cursor:pointer; box-shadow:0 4px 12px rgba(2,132,199,0.3);">
                                🇹🇷 81 İl ve Tüm İlçeleri Senkronize Et & İndeksle
                            </button>
                            <button type="button" id="btn-sync-bingol" class="button" style="background:rgba(255,255,255,0.15); color:white; border:1px solid rgba(255,255,255,0.3); padding:8px 16px; height:auto; font-size:13px; font-weight:700; border-radius:10px; cursor:pointer;">
                                📍 Bingöl Ustaları
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DURUM BİLGİLENDİRME PANELİ -->
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; margin-bottom: 26px;">
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                    <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">İndekslenebilir İçerik</div>
                    <div style="font-size: 26px; font-weight: 900; color: #0f172a; margin: 4px 0;"><?php echo number_format_i18n( $estimated_urls ); ?> URL</div>
                    <div style="font-size: 12.5px; color: #059669; font-weight: 600;">✓ <?php echo esc_html( $total_mechs ); ?> Usta + 81 İl + Sayfalar</div>
                </div>

                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                    <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">IndexNow API Anahtarı</div>
                    <div style="font-size: 15px; font-weight: 800; color: #0f172a; margin: 8px 0 6px; font-family:monospace; word-break:break-all;"><?php echo esc_html( $key ); ?></div>
                    <div style="font-size: 12.5px; color: #3b82f6;">
                        <a href="<?php echo esc_url( $key_loc ); ?>" target="_blank" style="text-decoration:none; font-weight:700;">✓ Anahtarı Test Et (.txt)</a>
                    </div>
                </div>

                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                    <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Son Gönderim Zamanı</div>
                    <div style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 8px 0 6px;">
                        <?php echo $last_sent ? human_time_diff( $last_sent, current_time( 'timestamp' ) ) . ' önce' : 'Henüz toplu gönderilmedi'; ?>
                    </div>
                    <div style="font-size: 12.5px; color: #64748b;">
                        <a href="<?php echo esc_url( $sitemap_url ); ?>" target="_blank" style="text-decoration:none; font-weight:700; color:#ea580c;">🔗 sitemap.xml Görüntüle</a>
                    </div>
                </div>
            </div>

            <!-- BİLGİLENDİRME VE ADIMLAR KUTUSU -->
            <div style="background: white; border: 1px solid #e2e8f0; border-radius: 14px; padding: 24px; margin-bottom: 26px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                <h3 style="margin: 0 0 14px; font-size: 17px; font-weight: 800; color: #0f172a;">
                    🎯 Google'da Çok Hızlı Listelenmek İçin Yapılması Gereken 3 Altın Adım
                </h3>
                <ol style="margin: 0; padding-left: 20px; line-height: 1.8; font-size: 14px; color: #334155;">
                    <li>
                        <strong>Google Search Console Doğrulaması & Sitemap:</strong> 
                        <a href="https://search.google.com/search-console" target="_blank" style="color: #2563eb; font-weight: 700;">Google Search Console</a> paneline girin, sol menüden <strong>Site Haritaları</strong> sekmesine tıklayın ve <code>sitemap.xml</code> adresinizi ekleyerek Gönder butonuna basın.
                    </li>
                    <li>
                        <strong>Ana Sayfayı & Önemli İlleri "URL Denetimi" ile Hemen İndekse Gönderin:</strong>
                        Search Console üst arama çubuğuna ana sayfanızı (<code><?php echo esc_url( home_url('/') ); ?></code>) ve en yoğun şehir sayfalarınızı (örn: <code><?php echo esc_url( home_url('/ustalar/?mechanic_city=istanbul') ); ?></code>) yazıp <strong>"Dizine Eklenmesini İste"</strong> butonuna basın. Googlebot dakikalar içinde taramaya gelir.
                    </li>
                    <li>
                        <strong>IndexNow ile Tek Tıkla Bildirin:</strong> 
                        Yukarıdaki <strong>"Tüm Siteyi Arama Motorlarına İlet"</strong> butonuna tıkladığınızda veritabanınızdaki tüm yayınlanmış işletmeler, şehirler ve hizmet sayfaları paket halinde anında arama motoru havuzuna iletilir.
                    </li>
                </ol>
            </div>

            <!-- CANLI GÖNDERİM SONUÇ ALANI -->
            <div id="indexnow-result-box" style="display:none; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 14px; padding: 20px 24px; margin-bottom: 26px; color: #166534;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <span class="dashicons dashicons-yes-alt" style="font-size:28px; width:28px; height:28px; color:#16a34a;"></span>
                    <div>
                        <h4 style="margin:0 0 4px; font-size:16px; font-weight:800; color:#15803d;" id="indexnow-result-title">İşlem Tamamlandı</h4>
                        <p style="margin:0; font-size:13.5px;" id="indexnow-result-desc"></p>
                    </div>
                </div>
            </div>

            <!-- GEÇMİŞ GÖNDERİM LOGLARI -->
            <div style="background: white; border: 1px solid #e2e8f0; border-radius: 14px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                <h3 style="margin: 0 0 16px; font-size: 16px; font-weight: 800; color: #0f172a;">Son Gönderim Hareketleri (Log)</h3>
                <?php if ( ! empty( $logs ) ) : ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width:200px;">Tarih / Saat</th>
                                <th style="width:140px;">İletilen URL Sayısı</th>
                                <th>Örnek Gönderilen URL</th>
                                <th style="width:120px;">Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $logs as $l ) : ?>
                                <tr>
                                    <td><strong><?php echo esc_html( $l['time'] ); ?></strong></td>
                                    <td><span style="background:#ecfdf5; color:#047857; padding:3px 8px; border-radius:12px; font-weight:700;"><?php echo intval( $l['count'] ); ?> URL</span></td>
                                    <td style="font-family:monospace; font-size:12px; color:#64748b;"><?php echo esc_html( $l['first'] ); ?></td>
                                    <td><span style="color:#059669; font-weight:700;">✓ Başarılı</span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p style="color:#64748b; font-style:italic; margin:0;">Henüz kayıtlı bir gönderim bulunmuyor. Yukarıdaki butona tıklayarak ilk toplu iletimi gerçekleştirebilirsiniz.</p>
                <?php endif; ?>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('btn-batch-indexnow');
            const resultBox = document.getElementById('indexnow-result-box');
            const resultTitle = document.getElementById('indexnow-result-title');
            const resultDesc = document.getElementById('indexnow-result-desc');

            if (btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (btn.disabled) return;

                    btn.disabled = true;
                    const oldText = btn.innerHTML;
                    btn.innerHTML = '⏳ Arama motorlarına iletiliyor...';

                    const formData = new FormData();
                    formData.append('action', 'ototamir_indexnow_batch_submit');
                    formData.append('nonce', '<?php echo wp_create_nonce( 'ototamir_indexnow_batch_nonce' ); ?>');

                    fetch(ajaxurl, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(json => {
                        btn.disabled = false;
                        btn.innerHTML = oldText;

                        if (json && json.success) {
                            resultBox.style.display = 'block';
                            resultBox.style.background = '#f0fdf4';
                            resultBox.style.borderColor = '#bbf7d0';
                            resultBox.style.color = '#166534';
                            resultTitle.innerText = 'Tebrikler! İndeksleme İsteği Başarıyla Gönderildi';
                            resultDesc.innerHTML = `Toplam <strong>${json.data.count} adet URL</strong> IndexNow API ve Google/Bing Sitemap Ping havuzuna iletildi. Botlar sayfalarınızı otomatik olarak sıraya alacaktır.`;
                        } else {
                            resultBox.style.display = 'block';
                            resultBox.style.background = '#fef2f2';
                            resultBox.style.borderColor = '#fecaca';
                            resultBox.style.color = '#991b1b';
                            resultTitle.innerText = 'Gönderim Sırasında Hata';
                            resultDesc.innerText = (json && json.data && json.data.message) ? json.data.message : 'Bir hata oluştu.';
                        }
                    })
                    .catch(err => {
                        btn.disabled = false;
                        btn.innerHTML = oldText;
                        alert('Bağlantı hatası oluştu: ' + err);
                    });
                });
            }

            const btnAllCities = document.getElementById('btn-sync-all-cities');
            if (btnAllCities) {
                btnAllCities.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (btnAllCities.disabled) return;

                    btnAllCities.disabled = true;
                    const oldAllText = btnAllCities.innerHTML;
                    btnAllCities.innerHTML = '⏳ 81 il ve ilçeler taranıyor...';

                    const formData = new FormData();
                    formData.append('action', 'ototamir_sync_all_cities');
                    formData.append('nonce', '<?php echo wp_create_nonce( 'ototamir_all_cities_nonce' ); ?>');

                    fetch(ajaxurl, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(json => {
                        btnAllCities.disabled = false;
                        btnAllCities.innerHTML = oldAllText;

                        if (json && json.success) {
                            resultBox.style.display = 'block';
                            resultBox.style.background = '#f0fdf4';
                            resultBox.style.borderColor = '#bbf7d0';
                            resultBox.style.color = '#166534';
                            resultTitle.innerText = '81 İl ve Tüm İlçeler Başarıyla Senkronize Edildi!';
                            resultDesc.innerHTML = json.data.message;
                        } else {
                            resultBox.style.display = 'block';
                            resultBox.style.background = '#fef2f2';
                            resultBox.style.borderColor = '#fecaca';
                            resultBox.style.color = '#991b1b';
                            resultTitle.innerText = 'Senkronizasyon Sırasında Hata';
                            resultDesc.innerText = (json && json.data && json.data.message) ? json.data.message : 'Bir hata oluştu.';
                        }
                    })
                    .catch(err => {
                        btnAllCities.disabled = false;
                        btnAllCities.innerHTML = oldAllText;
                        alert('Bağlantı hatası oluştu: ' + err);
                    });
                });
            }

            const btnBingol = document.getElementById('btn-sync-bingol');
            if (btnBingol) {
                btnBingol.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (btnBingol.disabled) return;

                    btnBingol.disabled = true;
                    const oldBingolText = btnBingol.innerHTML;
                    btnBingol.innerHTML = '⏳ Bingöl ustaları aktarılıyor...';

                    const formData = new FormData();
                    formData.append('action', 'ototamir_sync_bingol_mechanics');
                    formData.append('nonce', '<?php echo wp_create_nonce( 'ototamir_bingol_sync_nonce' ); ?>');

                    fetch(ajaxurl, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(json => {
                        btnBingol.disabled = false;
                        btnBingol.innerHTML = oldBingolText;

                        if (json && json.success) {
                            resultBox.style.display = 'block';
                            resultBox.style.background = '#f0fdf4';
                            resultBox.style.borderColor = '#bbf7d0';
                            resultBox.style.color = '#166534';
                            resultTitle.innerText = 'Bingöl Ustaları Başarıyla Senkronize Edildi!';
                            resultDesc.innerHTML = json.data.message;
                        } else {
                            resultBox.style.display = 'block';
                            resultBox.style.background = '#fef2f2';
                            resultBox.style.borderColor = '#fecaca';
                            resultBox.style.color = '#991b1b';
                            resultTitle.innerText = 'Senkronizasyon Sırasında Hata';
                            resultDesc.innerText = (json && json.data && json.data.message) ? json.data.message : 'Bir hata oluştu.';
                        }
                    })
                    .catch(err => {
                        btnBingol.disabled = false;
                        btnBingol.innerHTML = oldBingolText;
                        alert('Bağlantı hatası oluştu: ' + err);
                    });
                });
            }
        });
        </script>
        <?php
    }

    /**
     * AJAX: Toplu URL İletimi
     */
    public static function ajax_batch_submit() {
        check_ajax_referer( 'ototamir_indexnow_batch_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Yetkiniz yetersiz.' ) );
        }

        global $wpdb;

        $urls = array();

        // 1. Ana Sayfa & Listeleme
        $urls[] = home_url( '/' );
        $urls[] = home_url( '/ustalar/' );
        $urls[] = home_url( '/nobetci-oto-tamirciler/' );
        $urls[] = home_url( '/hizmetler/' );
        $urls[] = home_url( '/iller/' );

        // 2. Yayındaki Tüm Ustalar
        $mechs = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'mechanic' AND post_status = 'publish' LIMIT 5000" );
        foreach ( $mechs as $m_id ) {
            $urls[] = get_permalink( $m_id );
        }

        // 3. 81 İl Sayfaları
        $cities = get_terms( array( 'taxonomy' => 'mechanic_city', 'hide_empty' => false ) );
        if ( ! is_wp_error( $cities ) && ! empty( $cities ) ) {
            foreach ( $cities as $c ) {
                $urls[] = home_url( '/ustalar/?mechanic_city=' . $c->slug );
            }
        }

        // 3.1. 81 İl ve Tüm Resmi İlçeler (970+ URL)
        if ( class_exists( 'OtoTamir_All_Cities_Migration' ) ) {
            $all_districts = OtoTamir_All_Cities_Migration::get_all_districts();
            foreach ( $all_districts as $c_name => $d_list ) {
                $c_slug = sanitize_title( $c_name );
                foreach ( $d_list as $d_name ) {
                    $urls[] = home_url( '/ustalar/?mechanic_city=' . $c_slug . '&mechanic_district=' . urlencode( $d_name ) );
                }
            }
        }

        // 4. Hizmet Kategorileri
        $services = get_terms( array( 'taxonomy' => 'service_type', 'hide_empty' => false ) );
        if ( ! is_wp_error( $services ) && ! empty( $services ) ) {
            foreach ( $services as $s ) {
                $link = get_term_link( $s );
                if ( ! is_wp_error( $link ) ) {
                    $urls[] = $link;
                }
            }
        }

        // 5. Blog Yazıları & Statik Sayfalar
        $posts = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type IN ('post', 'page') AND post_status = 'publish' LIMIT 500" );
        foreach ( $posts as $p_id ) {
            // Profil ve giriş sayfalarını hariç tut
            $slug = get_post_field( 'post_name', $p_id );
            if ( in_array( $slug, array( 'profil', 'giris-yap', 'kayit-ol' ), true ) ) {
                continue;
            }
            $urls[] = get_permalink( $p_id );
        }

        $urls = array_values( array_unique( array_filter( $urls ) ) );

        $result = self::submit_urls( $urls );

        if ( $result['success'] ) {
            wp_send_json_success( array(
                'count'   => $result['count'],
                'message' => 'Başarıyla iletildi.',
            ) );
        } else {
            wp_send_json_error( array(
                'message' => $result['message'] ?? 'Gönderim tamamlanamadı.',
            ) );
        }
    }
}

OtoTamir_IndexNow::init();
