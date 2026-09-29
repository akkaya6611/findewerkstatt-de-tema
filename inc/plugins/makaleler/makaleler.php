<?php
/**
 * Plugin Name: Makaleler - Otomotiv Trend SEO İcerik Motoru & AI Makale Studyosu
 * Plugin URI: https://ototamircibul.com.tr
 * Description: Google'da en cok aranan otomotiv ariza, bakim ve ikaz isigi aramalarini Featured Snippet uyumlu, 2026 fiyat tablolu, insan dili ve usta agziyla zengin makalelere donusturen yapay zeka icerik motoru. (Dual Failover: Gemini + Groq)
 * Version: 2.4.2
 * Author: OtoTamirciBul
 * Author URI: https://ototamircibul.com.tr
 * License: GPL-2.0+
 * Text Domain: makaleler
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( defined( 'MAKALELER_PLUGIN_LOADED' ) || class_exists( 'Makaleler_Plugin' ) ) {
    return;
}
define( 'MAKALELER_PLUGIN_LOADED', true );

if ( ! defined( 'MAKALELER_VERSION' ) ) {
    define( 'MAKALELER_VERSION', '2.4.2' );
}
if ( ! defined( 'MAKALELER_PATH' ) ) {
    define( 'MAKALELER_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'MAKALELER_URL' ) ) {
    define( 'MAKALELER_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! class_exists( 'Makaleler_Plugin' ) ) {
class Makaleler_Plugin {

    const OPTION_SEEDED      = 'makaleler_trend_posts_seeded_v1';
    const OPTION_GEMINI_KEY  = 'makaleler_gemini_api_key';
    const OPTION_GROQ_KEY    = 'makaleler_groq_api_key';

    public static function init() {
        // Eklenti etkinlestirildiginde otomatik icerik uret
        register_activation_hook( __FILE__, array( __CLASS__, 'activate' ) );

        // Admin menusu
        add_action( 'admin_menu', array( __CLASS__, 'register_admin_menu' ) );

        // Manuel yayinlama / yeniden senkronizasyon eylemi
        add_action( 'admin_post_makaleler_sync_now', array( __CLASS__, 'handle_sync_action' ) );

        // AJAX: AI Makale Uretimi
        add_action( 'wp_ajax_makaleler_generate_ai_article', array( __CLASS__, 'ajax_generate_ai_article' ) );
        add_action( 'wp_ajax_makaleler_fetch_google_trends', array( __CLASS__, 'ajax_fetch_google_trends' ) );

        // Admin bildirimleri
        add_action( 'admin_notices', array( __CLASS__, 'render_admin_notices' ) );

        // Otomatik Fiyat Tablosu Yasal Uyari & Usta Yonlendirme Filtresi
        add_filter( 'the_content', array( __CLASS__, 'filter_append_price_disclaimer' ), 20 );
    }

    public static function activate() {
        self::seed_articles();
    }

    public static function register_admin_menu() {
        add_menu_page(
            'Makaleler SEO Motoru',
            'Makaleler',
            'manage_options',
            'makaleler-dashboard',
            array( __CLASS__, 'render_dashboard' ),
            'dashicons-welcome-write-blog',
            26
        );
    }

    public static function handle_sync_action() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Yetkisiz erisim.' );
        }
        check_admin_referer( 'makaleler_sync_nonce' );

        $result = self::seed_articles( true );

        $redirect = add_query_arg(
            array(
                'page'     => 'makaleler-dashboard',
                'synced'   => 1,
                'created'  => $result['created'],
                'existing' => $result['existing'],
            ),
            admin_url( 'admin.php' )
        );

        wp_safe_redirect( $redirect );
        exit;
    }

    public static function render_admin_notices() {
        if ( isset( $_GET['synced'] ) && $_GET['synced'] == 1 ) {
            $created = isset( $_GET['created'] ) ? intval( $_GET['created'] ) : 0;
            $existing = isset( $_GET['existing'] ) ? intval( $_GET['existing'] ) : 0;
            ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>✅ Makaleler Senkronize Edildi:</strong> <?php echo esc_html( $created ); ?> yeni makale basariyla yayina alindi. (<?php echo esc_html( $existing ); ?> makale zaten mevcuttu).</p>
            </div>
            <?php
        }
    }

    /**
     * AJAX: Google Canli Trend & Arama Tamamlama Sorgularini Cek
     */
    /**
     * Otomatik Fiyat Tablosu Yasal Uyari ve Usta Yonlendirme Filtresi
     * Tum makalelerde (gecmis ve yeni) fiyat tablosunun hemen altina baglayici olmayan yasal bildirim ekler
     */
    public static function filter_append_price_disclaimer( $content ) {
        if ( is_singular( 'post' ) && strpos( $content, 'blog-price-table' ) !== false && strpos( $content, 'blog-price-disclaimer' ) === false ) {
            $base_url = rtrim( home_url(), '/' );
            $disclaimer = '<div class="blog-price-disclaimer">'
                . '<i class="fa-solid fa-triangle-exclamation" style="color:#d97706; margin-right:6px;"></i>'
                . '<strong>Fiyatlandırma & Yasal Bilgilendirme:</strong> Yukarıdaki parça ve işçilik tutarları, 2026 yılı Türkiye geneli sanayi ve özel servis piyasa ortalamalarına dayalı <u>tahmini verilerdir</u>. Aracınızın marka, model yılı, orijinal/yan sanayi parça tercihi ve ustanızın işçilik tarifesine göre değişiklik gösterebilir. Kesin ve bağlayıcı fiyat tespiti için lütfen ustanızla görüşün: '
                . '<a href="' . esc_url( $base_url . '/ustalar/' ) . '">En Yakın Tamirciyi Bulun ↗</a>'
                . '</div>';

            $content = preg_replace( '#(</table>)#i', '$1' . $disclaimer, $content, 1 );
        }
        return $content;
    }

    public static function ajax_fetch_google_trends() {
        check_ajax_referer( 'makaleler_ai_nonce', 'security' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Yetkisiz erisim.' );
        }

        $keyword  = sanitize_text_field( $_POST['keyword'] ?? '' );
        $category = sanitize_text_field( $_POST['category'] ?? 'all' );

        $queries_to_search = array();

        if ( ! empty( $keyword ) ) {
            $queries_to_search[] = $keyword;
            $queries_to_search[] = $keyword . ' neden';
            $queries_to_search[] = $keyword . ' arızası';
            $queries_to_search[] = $keyword . ' çözümü';
            $queries_to_search[] = $keyword . ' nasıl';
        } else {
            switch ( $category ) {
                case 'sanziman':
                    $queries_to_search = array( 'otomatik vites neden', 'şanzıman arıza', 'vuruntu yapıyor', 'dsg arıza', 'debriyaj kaçırıyor' );
                    break;
                case 'motor':
                    $queries_to_search = array( 'motor arıza lambası', 'araba tekleme yapıyor', 'hararet neden yükselir', 'yağ yakma', 'su eksiltme' );
                    break;
                case 'fren':
                    $queries_to_search = array( 'fren pedalı yumuşadı', 'frenden ses geliyor', 'abs lambası neden yanar', 'fren tutmuyor' );
                    break;
                case 'elektrik':
                    $queries_to_search = array( 'akü bitti', 'marş basmıyor', 'şarj dinamosu arıza', 'araba elektrik kesiyor' );
                    break;
                default:
                    $queries_to_search = array(
                        'araba neden',
                        'motor arıza lambası',
                        'hararet neden',
                        'otomatik vites',
                        'fren tutmuyor',
                        'egzozdan duman',
                        'triger kayışı koparsa'
                    );
                    break;
            }
        }

        $all_suggestions = array();
        $seen = array();

        foreach ( $queries_to_search as $search_query ) {
            $url = 'https://suggestqueries.google.com/complete/search?client=chrome&hl=tr&gl=tr&q=' . rawurlencode( $search_query );
            $response = wp_remote_get( $url, array(
                'headers' => array(
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                ),
                'timeout' => 5
            ) );

            if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
                $body = wp_remote_retrieve_body( $response );
                $data = json_decode( $body, true );
                if ( ! empty( $data[1] ) && is_array( $data[1] ) ) {
                    foreach ( $data[1] as $item ) {
                        $clean = trim( $item );
                        if ( mb_strlen( $clean ) < 5 ) {
                            continue;
                        }
                        $clean_lower = mb_strtolower( $clean, 'UTF-8' );
                        if ( ! isset( $seen[ $clean_lower ] ) ) {
                            $seen[ $clean_lower ] = true;

                            $badge = '📈 Popüler Trend';
                            if ( strpos( $clean_lower, 'neden' ) !== false || strpos( $clean_lower, 'nasıl' ) !== false ) {
                                $badge = '🔥 Yüksek Tıklanma';
                            } elseif ( strpos( $clean_lower, 'fiyat' ) !== false || strpos( $clean_lower, 'kaç' ) !== false ) {
                                $badge = '💰 2026 Fiyat';
                            } elseif ( strpos( $clean_lower, 'arızas' ) !== false || strpos( $clean_lower, 'vuruntu' ) !== false || strpos( $clean_lower, 'uyarı' ) !== false ) {
                                $badge = '⚠️ Kritik Arıza';
                            }

                            $all_suggestions[] = array(
                                'query' => $clean,
                                'badge' => $badge
                            );
                        }
                    }
                }
            }
        }

        // Yedek fallback listesi (Eger sunucu dis baglantida sorun yasarsa bos kalmasin)
        if ( empty( $all_suggestions ) ) {
            $fallbacks = array(
                array( 'query' => 'otomatik vites vuruntu yapıyor', 'badge' => '⚠️ Kritik Arıza' ),
                array( 'query' => 'motor arıza lambası neden sarı yanar', 'badge' => '🔥 Yüksek Tıklanma' ),
                array( 'query' => 'araba neden hararet yapar', 'badge' => '🔥 Yüksek Tıklanma' ),
                array( 'query' => 'fren pedalı sertleşti ne yapmalıyım', 'badge' => '⚠️ Kritik Arıza' ),
                array( 'query' => 'triger kayışı ne zaman değişir 2026 fiyatı', 'badge' => '💰 2026 Fiyat' ),
                array( 'query' => 'dsg şanzıman aşırı sıcak uyarısı çözümü', 'badge' => '⚠️ Kritik Arıza' ),
                array( 'query' => 'araba neden su eksiltir', 'badge' => '🔥 Yüksek Tıklanma' ),
                array( 'query' => 'akü bitti araba nasıl çalıştırılır', 'badge' => '📈 Popüler Trend' ),
            );
            $all_suggestions = $fallbacks;
        }

        $results = array_slice( $all_suggestions, 0, 15 );

        wp_send_json_success( array(
            'count'  => count( $results ),
            'trends' => $results,
            'source' => 'Google Canlı Türkiye Aramaları'
        ) );
    }

    /**
     * AJAX: Insan Dili & Usta Agzi Yapay Zeka Makalesi Uret (Dual Failover)
     */
    public static function ajax_generate_ai_article() {
        check_ajax_referer( 'makaleler_ai_nonce', 'security' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Yetkisiz erisim.' );
        }

        $topic       = sanitize_text_field( $_POST['topic'] ?? '' );
        $vehicle     = sanitize_text_field( $_POST['vehicle'] ?? '' );
        $category_in = sanitize_text_field( $_POST['category'] ?? '' );
        $post_status = in_array( $_POST['post_status'] ?? '', array( 'publish', 'draft' ) ) ? $_POST['post_status'] : 'publish';
        
        $gemini_key  = trim( sanitize_text_field( $_POST['gemini_key'] ?? '' ) );
        $groq_key    = trim( sanitize_text_field( $_POST['groq_key'] ?? '' ) );

        if ( empty( $topic ) ) {
            wp_send_json_error( 'Lutfen bir makale konusu veya aranan terim girin.' );
        }

        // Gemini Anahtari Kontrolu ve Kaydi
        if ( ! empty( $gemini_key ) ) {
            update_option( self::OPTION_GEMINI_KEY, $gemini_key );
        } else {
            $gemini_key = trim( get_option( self::OPTION_GEMINI_KEY, '' ) );
            if ( empty( $gemini_key ) ) {
                $old_single = trim( get_option( 'makaleler_ai_api_key', '' ) );
                if ( strpos( $old_single, 'AIza' ) === 0 ) {
                    $gemini_key = $old_single;
                }
            }
        }

        // Groq Anahtari Kontrolu ve Kaydi
        if ( ! empty( $groq_key ) ) {
            update_option( self::OPTION_GROQ_KEY, $groq_key );
        } else {
            $groq_key = trim( get_option( self::OPTION_GROQ_KEY, '' ) );
            if ( empty( $groq_key ) ) {
                $groq_key = trim( get_option( 'otb_ai_groq_key', '' ) );
            }
            if ( empty( $groq_key ) ) {
                $old_single = trim( get_option( 'makaleler_ai_api_key', '' ) );
                if ( strpos( $old_single, 'gsk_' ) === 0 ) {
                    $groq_key = $old_single;
                }
            }
        }

        if ( empty( $gemini_key ) && empty( $groq_key ) ) {
            wp_send_json_error( 'Lutfen en az bir adet Google Gemini veya Groq API anahtari girin.' );
        }

        // Dual Failover ile AI'dan makale uret
        $article_data = self::call_ai_with_dual_failover( $topic, $vehicle, $category_in, $gemini_key, $groq_key );

        if ( is_wp_error( $article_data ) ) {
            wp_send_json_error( $article_data->get_error_message() );
        }

        // Kategori olustur veya ID al
        $cat_name = ! empty( $category_in ) ? $category_in : ( $article_data['category'] ?? 'Ariza Rehberi' );
        $cat_id = 1;
        $term = get_term_by( 'name', $cat_name, 'category' );
        if ( $term && ! is_wp_error( $term ) ) {
            $cat_id = $term->term_id;
        } else {
            $inserted = wp_insert_term( $cat_name, 'category' );
            if ( ! is_wp_error( $inserted ) && isset( $inserted['term_id'] ) ) {
                $cat_id = $inserted['term_id'];
            }
        }

        $base = rtrim( home_url(), '/' );
        $content = str_replace( '{{BASE_URL}}', $base, $article_data['content'] );

        // WordPress'e makaleyi kaydet
        $post_id = wp_insert_post( array(
            'post_title'   => $article_data['title'],
            'post_name'    => sanitize_title( $article_data['slug'] ?? $article_data['title'] ),
            'post_content' => $content,
            'post_excerpt' => $article_data['excerpt'] ?? '',
            'post_status'  => $post_status,
            'post_type'    => 'post',
            'post_category'=> array( $cat_id ),
        ) );

        if ( is_wp_error( $post_id ) ) {
            wp_send_json_error( 'Makale kaydedilirken hata olustu: ' . $post_id->get_error_message() );
        }

        // Meta bilgileri
        $image = ! empty( $article_data['image'] ) ? esc_url_raw( $article_data['image'] ) : self::get_random_auto_image();
        $read_time = ! empty( $article_data['read_time'] ) ? sanitize_text_field( $article_data['read_time'] ) : '6 dk okuma';

        update_post_meta( $post_id, '_custom_blog_image', $image );
        update_post_meta( $post_id, '_blog_read_time', $read_time );
        update_post_meta( $post_id, '_makaleler_managed', 'yes' );
        update_post_meta( $post_id, '_makaleler_ai_generated', 'yes' );

        wp_send_json_success( array(
            'post_id'   => $post_id,
            'title'     => $article_data['title'],
            'slug'      => get_post_field( 'post_name', $post_id ),
            'status'    => $post_status === 'publish' ? 'Yayinda' : 'Taslak',
            'provider'  => $article_data['provider_used'] ?? 'Yapay Zeka',
            'view_url'  => get_permalink( $post_id ),
            'edit_url'  => get_edit_post_link( $post_id, 'raw' ),
            'excerpt'   => $article_data['excerpt'] ?? '',
        ) );
    }

        /**
     * DUAL FAILOVER YONETICISI: Gemini ve Groq / xAI Grok arasinda dinamik gecis
     */
    private static function call_ai_with_dual_failover( $topic, $vehicle, $category, $gemini_key, $groq_key ) {
        $vehicle_context = ! empty( $vehicle ) ? "Hedef Arac / Model: " . $vehicle . "." : "Genel otomotiv (Turkiye piyasasindaki populer araclar ornek verilsin: Egea, Megane, Corolla, Clio, Passat, Focus).";
        $category_hint   = ! empty( $category ) ? "Kategori: " . $category . "." : "";

        $prompt = "Sen 25 yillik tecrubeli bir motor ve mekanik ustasisin; ayni zamanda samimi ve profesyonel bir otomotiv yazarisin.\n"
            . "Suruculerin dilinden anlayan, sanayi jargonuna hakim, dogrudan sonuca giden, guven veren ve lafi gevelemeyen bir uslupla konusursun.\n\n"
            . "KONU: " . $topic . "\n"
            . $vehicle_context . "\n"
            . $category_hint . "\n\n"
            . "USLUP VE DIL KURALLARI (KESINLIKLE UYULACAK):\n"
            . "1. Kesinlikle robotik veya yapay zeka kokan kaliplar KULLANMA! 'Sonuc olarak', 'Ozetle', 'Gunumuz teknolojisinde...', 'Adeta', 'Unutulmamalidir ki', 'Goz ardi edilmemelidir' gibi kaliplar KESINLIKLE YASAKTIR.\n"
            . "2. Sikici ansiklopedik tanimlarla baslama. Dogrudan surucunun yolda veya sabah marsa bastiginda basina gelen olayla, hissettigi sarsintiyla veya duydugu sesle basla.\n"
            . "3. 'Ustaniz size sunu derse hemen parca degistirmeyin, once su 2 dakikalik kontrolu yapin' gibi gercek sanayi tuyolari ver.\n"
            . "4. Fiyatlar 2026 yili Turkiye sanayi ve servis ortalamasina uygun gercekci Turk Lirasi (₺) olsun.\n\n"
            . "ZORUNLU HTML BILESENLERI:\n"
            . "1. Sayfanin basinda Featured Snippet icin HIZLI TESHiS KUTUSU:\n"
            . "<div class=\"blog-callout-box\"><strong>Hizli Teshis:</strong> [Google'in one cikaracagi 2-3 cumlelik net cozum ve aciklama]</div>\n"
            . "2. H2 ve H3 basliklariyla akici anlatim, belirtiler, evde yapilabilecek testler ve cozumu.\n"
            . "3. 2026 GERCEKCi FIYAT VE ISCiLIK TABLOSU:\n"
            . "<table class=\"blog-price-table\"><thead><tr><th>Islem / Parca</th><th>2026 Ortalama Fiyat</th><th>Islem Suresi</th></tr></thead><tbody>... en az 3-4 satir fiyat tablosu ...</tbody></table>\n". "<div class=\"blog-price-disclaimer\"><i class=\"fa-solid fa-triangle-exclamation\"></i> <strong>Fiyatlandırma & Bilgilendirme:</strong> Tablodaki tutarlar 2026 yılı Türkiye geneli sanayi ve servis ortalamalarına dayalı tahmini verilerdir. Kesin ve net fiyat için lütfen ustanızla görüşün: <a href=\"{{BASE_URL}}/ustalar/\">En Yakın Tamirciyi Bulun ↗</a></div>\n"
            . "4. DONUSUM BUTONLARI (CTA KUTUSU):\n"
            . "<div class=\"blog-cta-box\"><h3>[Surucuye Samimi Cagri Basligi]</h3><p>[Aciklama]</p><div class=\"blog-cta-buttons\"><a href=\"{{BASE_URL}}/ariza-tespiti/\" class=\"blog-cta-btn-primary\"><i class=\"fa-solid fa-robot\"></i> Yapay Zeka Ariza Analizi</a><a href=\"{{BASE_URL}}/ustalar/\" class=\"blog-cta-btn-secondary\"><i class=\"fa-solid fa-wrench\"></i> En Yakin Tamirciyi Bul</a></div></div>\n"
            . "5. SIKCA SORULAN SORULAR (SSS): H2 basligi altinda 2-3 onemli soru ve usta cevabi.\n\n"
            . "SADECE VE SADECE ASAGIDAKI JSON FORMATINDA CEVAP VER, BASKA HiCBiR SEY YAZMA:\n"
            . "{\"title\":\"Tıklanma garantili SEO basligi (2026 Cozum Rehberi)\",\"slug\":\"url-dostu-slug\",\"category\":\"Ariza Rehberi\",\"read_time\":\"6 dk okuma\",\"excerpt\":\"Google meta aciklamasi (150 karakter)\",\"image\":\"https://images.unsplash.com/photo-1486006920555-c77dce18193b?auto=format&fit=crop&w=1200&q=80\",\"content\":\"Tam HTML icerik...\"}";

        $attempt_errors = array();

        // 1. ADIM: Once Google Gemini'yi dene (Varsa)
        if ( ! empty( $gemini_key ) ) {
            $res = self::try_gemini( $prompt, $gemini_key );
            if ( ! is_wp_error( $res ) ) {
                $res['provider_used'] = 'Google Gemini (' . ( $res['model_used'] ?? '2.5 Flash' ) . ')';
                return $res;
            } else {
                $attempt_errors[] = 'Google Gemini: ' . $res->get_error_message();
            }
        }

        // 2. ADIM: Gemini basarisiz olduysa veya yoksa Groq / xAI Grok'u dene (Varsa)
        if ( ! empty( $groq_key ) ) {
            $res = self::try_groq_or_grok( $prompt, $groq_key );
            if ( ! is_wp_error( $res ) ) {
                $res['provider_used'] = $res['model_used'] ?? 'Groq';
                return $res;
            } else {
                $attempt_errors[] = 'Groq / Grok: ' . $res->get_error_message();
            }
        }

        // 3. ADIM: Her iki servis de yanit veremediyse detayli hata goster
        $err_summary = implode( '<br>', $attempt_errors );
        return new WP_Error( 'all_failed', 'Her iki yapay zeka servisi de denendi ancak yanit alinamadi:<br>' . $err_summary );
    }

    /**
     * GOOGLE GEMINI MOTORU (2026 Guncel Modeller & Dinamik Kesif)
     */
    private static function try_gemini( $prompt, $api_key ) {
        $models = array();

        // 1. Kullanicinin API anahtarina acik modelleri dinamik sorgula
        $list_url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . urlencode( $api_key );
        $list_res = wp_remote_get( $list_url, array( 'timeout' => 8 ) );
        if ( ! is_wp_error( $list_res ) && wp_remote_retrieve_response_code( $list_res ) === 200 ) {
            $list_data = json_decode( wp_remote_retrieve_body( $list_res ), true );
            if ( ! empty( $list_data['models'] ) && is_array( $list_data['models'] ) ) {
                $discovered = array();
                foreach ( $list_data['models'] as $m ) {
                    $m_name = preg_replace( '#^models/#', '', $m['name'] ?? '' );
                    $methods = $m['supportedGenerationMethods'] ?? array();
                    if ( in_array( 'generateContent', $methods ) ) {
                        $discovered[] = $m_name;
                    }
                }
                // Once 2.5 Flash ailesini sirala
                $priority = array();
                foreach ( array( 'gemini-2.5-flash', 'gemini-2.5-flash-lite', 'gemini-2.0-flash', 'gemini-1.5-flash' ) as $p_mod ) {
                    if ( in_array( $p_mod, $discovered ) ) {
                        $priority[] = $p_mod;
                    }
                }
                foreach ( $discovered as $d_mod ) {
                    if ( ! in_array( $d_mod, $priority ) && stripos( $d_mod, 'flash' ) !== false ) {
                        $priority[] = $d_mod;
                    }
                }
                if ( ! empty( $priority ) ) {
                    $models = $priority;
                }
            }
        }

        // 2. Dinamik model listesi alinamadiysa 2026 resmi modellerini sirayla dene
        if ( empty( $models ) ) {
            $models = array(
                'gemini-2.5-flash',
                'gemini-2.5-flash-lite',
                'gemini-2.0-flash',
                'gemini-1.5-flash',
            );
        }

        $attempt_errors = array();

        foreach ( $models as $model ) {
            $clean_model = preg_replace( '#^models/#', '', $model );
            $url  = "https://generativelanguage.googleapis.com/v1beta/models/{$clean_model}:generateContent?key=" . urlencode( $api_key );
            $body = array(
                'contents'         => array( array( 'parts' => array( array( 'text' => $prompt ) ) ) ),
                'generationConfig' => array(
                    'responseMimeType' => 'application/json',
                    'temperature'      => 0.7,
                ),
            );

            $response = wp_remote_post( $url, array(
                'headers' => array( 'Content-Type' => 'application/json' ),
                'body'    => wp_json_encode( $body ),
                'timeout' => 60,
            ) );

            if ( is_wp_error( $response ) ) {
                $attempt_errors[] = "[$clean_model: " . $response->get_error_message() . "]";
                continue;
            }

            $code = wp_remote_retrieve_response_code( $response );
            $raw  = wp_remote_retrieve_body( $response );

            if ( $code === 200 ) {
                $decoded  = json_decode( $raw, true );
                $raw_text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';
                if ( ! empty( $raw_text ) ) {
                    $cleaned = preg_replace( '/^```(?:json)?\s*/i', '', trim( $raw_text ) );
                    $cleaned = preg_replace( '/```\s*$/i', '', $cleaned );
                    $json = json_decode( trim( $cleaned ), true );
                    if ( json_last_error() === JSON_ERROR_NONE && isset( $json['title'] ) ) {
                        $json['model_used'] = $clean_model;
                        return $json;
                    }
                }
            } else {
                $err = json_decode( $raw, true );
                $err_msg = $err['error']['message'] ?? "HTTP $code";
                $attempt_errors[] = "[$clean_model: $err_msg]";
            }
        }

        return new WP_Error( 'gemini_error', ! empty( $attempt_errors ) ? implode( ', ', $attempt_errors ) : 'Gemini yaniti alinamadi.' );
    }

    /**
     * GROQ CLOUD VEYA xAI GROK MOTORU (2026 Modelleri & Dinamik Tespiti)
     */
    private static function try_groq_or_grok( $prompt, $api_key ) {
        $api_key = trim( $api_key );
        $is_xai  = ( strpos( $api_key, 'xai-' ) === 0 );

        if ( $is_xai ) {
            $endpoint      = 'https://api.x.ai/v1/chat/completions';
            $provider_name = 'xAI Grok';
            $models        = array( 'grok-2-latest', 'grok-beta', 'grok-2' );
        } else {
            // Groq Cloud
            $endpoint      = 'https://api.groq.com/openai/v1/chat/completions';
            $provider_name = 'Groq Cloud';
            $models        = array();

            // 1. Groq modellerini dinamik sorgula
            $list_res = wp_remote_get( 'https://api.groq.com/openai/v1/models', array(
                'headers' => array( 'Authorization' => 'Bearer ' . $api_key ),
                'timeout' => 8,
            ) );
            if ( ! is_wp_error( $list_res ) && wp_remote_retrieve_response_code( $list_res ) === 200 ) {
                $list_data = json_decode( wp_remote_retrieve_body( $list_res ), true );
                if ( ! empty( $list_data['data'] ) && is_array( $list_data['data'] ) ) {
                    $available = array();
                    foreach ( $list_data['data'] as $m ) {
                        if ( isset( $m['active'] ) && ! $m['active'] ) {
                            continue;
                        }
                        $mid = $m['id'] ?? '';
                        if ( $mid ) {
                            $available[] = $mid;
                        }
                    }

                    // 2026 guncel modellerine gore onceliklendir
                    $priority = array();
                    foreach ( array(
                        'openai/gpt-oss-20b',
                        'meta-llama/llama-4-scout-17b-16e-instruct',
                        'meta-llama/llama-4-maverick-17b-128e-instruct',
                        'qwen/qwen3.6-27b',
                        'llama-3.3-70b-versatile',
                    ) as $preferred ) {
                        if ( in_array( $preferred, $available ) ) {
                            $priority[] = $preferred;
                        }
                    }
                    foreach ( $available as $av_mod ) {
                        if ( ! in_array( $av_mod, $priority ) && stripos( $av_mod, 'whisper' ) === false && stripos( $av_mod, 'guard' ) === false ) {
                            $priority[] = $av_mod;
                        }
                    }
                    if ( ! empty( $priority ) ) {
                        $models = $priority;
                    }
                }
            }

            // 2. Dinamik liste basarisiz olursa 2026 guncel ucretsiz Groq modellerini dene
            if ( empty( $models ) ) {
                $models = array(
                    'openai/gpt-oss-20b',
                    'meta-llama/llama-4-scout-17b-16e-instruct',
                    'qwen/qwen3.6-27b',
                    'llama-3.3-70b-versatile',
                    'llama-3.1-8b-instant',
                );
            }
        }

        $attempt_errors = array();

        foreach ( $models as $model ) {
            $formats_to_try = array( true, false );

            foreach ( $formats_to_try as $use_json_mode ) {
                $body = array(
                    'model'       => $model,
                    'messages'    => array(
                        array( 'role' => 'user', 'content' => $prompt )
                    ),
                    'temperature' => 0.7,
                );

                if ( $use_json_mode ) {
                    $body['response_format'] = array( 'type' => 'json_object' );
                }

                $response = wp_remote_post( $endpoint, array(
                    'headers' => array(
                        'Content-Type'  => 'application/json',
                        'Authorization' => 'Bearer ' . $api_key,
                    ),
                    'body'    => wp_json_encode( $body ),
                    'timeout' => 60,
                ) );

                if ( is_wp_error( $response ) ) {
                    $attempt_errors[] = "[$model: " . $response->get_error_message() . "]";
                    break;
                }

                $code = wp_remote_retrieve_response_code( $response );
                $raw  = wp_remote_retrieve_body( $response );

                if ( $code === 200 ) {
                    $decoded  = json_decode( $raw, true );
                    $raw_text = $decoded['choices'][0]['message']['content'] ?? '';
                    if ( ! empty( $raw_text ) ) {
                        $cleaned = preg_replace( '/^```(?:json)?\s*/i', '', trim( $raw_text ) );
                        $cleaned = preg_replace( '/```\s*$/i', '', $cleaned );
                        $json = json_decode( trim( $cleaned ), true );
                        if ( json_last_error() === JSON_ERROR_NONE && isset( $json['title'] ) ) {
                            $json['model_used'] = "$provider_name ($model)";
                            return $json;
                        }
                    }
                } else {
                    $err = json_decode( $raw, true );
                    $err_msg = $err['error']['message'] ?? "HTTP $code";

                    // Eger sadece json_object hatasiysa, formati kapatip hemen tekrar dene
                    if ( $use_json_mode && ( stripos( $err_msg, 'response_format' ) !== false || stripos( $err_msg, 'json_object' ) !== false ) ) {
                        continue;
                    }

                    $attempt_errors[] = "[$model: $err_msg]";
                    break;
                }
            }
        }

        return new WP_Error( 'groq_error', ! empty( $attempt_errors ) ? implode( ', ', $attempt_errors ) : "$provider_name yaniti alinamadi." );
    }

    private static function get_random_auto_image() {
        $images = array(
            'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1486006920555-c77dce18193b?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1517524008697-84bbe3c3fd98?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1580273916550-e323be2ae537?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1502877338535-766e1452684a?auto=format&fit=crop&w=1200&q=80',
        );
        return $images[ array_rand( $images ) ];
    }

    public static function seed_articles( $force = false ) {
        $articles = self::get_articles_catalog();
        $base = rtrim( home_url(), '/' );
        $created_count = 0;
        $existing_count = 0;

        foreach ( $articles as $art ) {
            $existing = get_page_by_path( $art['slug'], OBJECT, 'post' );
            if ( $existing && ! $force ) {
                $existing_count++;
                continue;
            }

            // Kategori bul veya guvenli olustur (wp-includes/taxonomy.php)
            $cat_id = 1;
            $term = get_term_by( 'name', $art['category'], 'category' );
            if ( $term && ! is_wp_error( $term ) ) {
                $cat_id = $term->term_id;
            } else {
                $inserted = wp_insert_term( $art['category'], 'category' );
                if ( ! is_wp_error( $inserted ) && isset( $inserted['term_id'] ) ) {
                    $cat_id = $inserted['term_id'];
                }
            }

            // Icerikteki URL dinamiklestirme
            $content = str_replace( '{{BASE_URL}}', $base, $art['content'] );

            if ( $existing && $force ) {
                // Guncelle
                wp_update_post( array(
                    'ID'           => $existing->ID,
                    'post_title'   => $art['title'],
                    'post_content' => $content,
                    'post_excerpt' => $art['excerpt'],
                    'post_category'=> array( $cat_id ),
                ) );
                $post_id = $existing->ID;
                $existing_count++;
            } else {
                // Yeni Ekle
                $post_id = wp_insert_post( array(
                    'post_title'   => $art['title'],
                    'post_name'    => $art['slug'],
                    'post_content' => $content,
                    'post_excerpt' => $art['excerpt'],
                    'post_status'  => 'publish',
                    'post_type'    => 'post',
                    'post_category'=> array( $cat_id ),
                ) );
                $created_count++;
            }

            if ( ! is_wp_error( $post_id ) ) {
                update_post_meta( $post_id, '_custom_blog_image', $art['image'] );
                update_post_meta( $post_id, '_blog_read_time', $art['read_time'] );
                update_post_meta( $post_id, '_makaleler_managed', 'yes' );
            }
        }

        update_option( self::OPTION_SEEDED, 'yes' );

        return array(
            'created'  => $created_count,
            'existing' => $existing_count,
            'total'    => count( $articles ),
        );
    }

    public static function render_dashboard() {
        $catalog = self::get_articles_catalog();
        $total_in_catalog = count( $catalog );
        $published_count = 0;

        foreach ( $catalog as $k => $c ) {
            $p = get_page_by_path( $c['slug'], OBJECT, 'post' );
            $catalog[$k]['post_obj'] = $p;
            if ( $p && $p->post_status === 'publish' ) {
                $published_count++;
            }
        }

        $saved_gemini_key = get_option( self::OPTION_GEMINI_KEY, '' );
        if ( empty( $saved_gemini_key ) ) {
            $old = get_option( 'makaleler_ai_api_key', '' );
            if ( strpos( $old, 'AIza' ) === 0 ) $saved_gemini_key = $old;
        }

        $saved_groq_key = get_option( self::OPTION_GROQ_KEY, '' );
        if ( empty( $saved_groq_key ) ) {
            $saved_groq_key = get_option( 'otb_ai_groq_key', '' );
            if ( empty( $saved_groq_key ) ) {
                $old = get_option( 'makaleler_ai_api_key', '' );
                if ( strpos( $old, 'gsk_' ) === 0 ) $saved_groq_key = $old;
            }
        }
        ?>
        <div class="wrap" style="max-width: 1100px; margin-top: 20px;">
            <!-- Header Banner -->
            <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white; padding: 32px 36px; border-radius: 16px; margin-bottom: 28px; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
                    <div>
                        <div style="display:inline-block; background:rgba(234,88,12,0.25); color:#fdba74; padding:4px 12px; border-radius:50px; font-size:12px; font-weight:700; margin-bottom:8px; border:1px solid rgba(234,88,12,0.4);">
                            🚀 DUAL AI MOTORU (GEMINI + GROQ) v<?php echo MAKALELER_VERSION; ?>
                        </div>
                        <h1 style="color:white; font-size:28px; font-weight:800; margin:0 0 8px;">Makaleler - Otomotiv Trend SEO & AI Makale Studyosu</h1>
                        <p style="color:#94a3b8; margin:0; font-size:14.5px;">Google Gemini ve Groq hibrit calisir; biri hata veya limit verirse digeri otomatik devreye girer. Kesintisiz usta dili icerik uretimi.</p>
                    </div>
                    <div>
                        <a href="<?php echo esc_url( home_url('/blog/') ); ?>" target="_blank" class="button button-hero" style="background:#2563eb; border-color:#2563eb; color:white; font-weight:700; border-radius:8px; display:inline-flex; align-items:center; gap:8px;">
                            <span class="dashicons dashicons-external" style="margin-top:4px;"></span> Blog Sayfasını Gör
                        </a>
                    </div>
                </div>
            </div>

            <!-- BÖLÜM 1: 🤖 YAPAY ZEKA MAKALE STÜDYOSU -->
            <div style="background:white; border-radius:16px; border:1px solid #e2e8f0; padding:28px 32px; margin-bottom:32px; box-shadow:0 4px 20px rgba(0,0,0,0.04);">
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
                    <div style="background:#eff6ff; color:#2563eb; width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:22px;">
                        🤖
                    </div>
                    <div>
                        <h2 style="font-size:20px; font-weight:800; margin:0; color:#0f172a;">İnsan Dili & Usta Ağzı Yapay Zeka Makale Üretici</h2>
                        <p style="color:#64748b; font-size:13.5px; margin:2px 0 0;">Yapay zeka klişelerinden uzak; usta dili, 2026 fiyat tablosu, Google Snippet kutusu ve iç linkleme ile tam donanımlı makale üretir.</p>
                    </div>
                </div>

                                <!-- 🔥 GOOGLE CANLI TREND VE ARAMA SORGULARI CEKICI -->
                <div style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border:1px solid #bbf7d0; border-radius:14px; padding:20px 24px; margin-bottom:24px; box-shadow:0 2px 10px rgba(16,185,129,0.06);">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-bottom:14px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="background:#dcfce7; color:#16a34a; width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px;">
                                📈
                            </div>
                            <div>
                                <h3 style="margin:0; font-size:16px; font-weight:800; color:#14532d;">Google Canlı Trend & Arama Avcısı (0.1 sn Canlı)</h3>
                                <p style="margin:2px 0 0; font-size:12.5px; color:#166534;">Türkiye'de sürücülerin Google arama kutusuna yazdığı en popüler ve tıklanma garantili arıza sorguları.</p>
                            </div>
                        </div>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <input type="text" id="google-trend-keyword" placeholder="Kelime veya Araç (Örn: DSG, Clio, Hararet...)" style="padding:8px 14px; font-size:13px; border-radius:8px; border:1px solid #86efac; min-width:230px; background:white;">
                            <button type="button" id="btn-fetch-google-trends" class="button button-primary" style="background:#16a34a; border-color:#15803d; font-weight:700; border-radius:8px; padding:4px 14px; display:inline-flex; align-items:center; gap:6px;">
                                <span class="dashicons dashicons-search" style="margin-top:4px;"></span> Canlı Trendleri Çek
                            </button>
                        </div>
                    </div>

                    <!-- Hızlı Kategori Filtre Butonları -->
                    <div style="display:flex; flex-wrap:wrap; gap:6px; align-items:center;">
                        <span style="font-size:12px; font-weight:700; color:#166534; margin-right:4px;">Hızlı Filtre:</span>
                        <button type="button" class="trend-quick-cat active-cat" data-cat="all" style="background:#16a34a; color:white; border:none; border-radius:20px; padding:4px 12px; font-size:12px; font-weight:700; cursor:pointer;">🔥 En Çok Arananlar</button>
                        <button type="button" class="trend-quick-cat" data-cat="motor" style="background:white; color:#166534; border:1px solid #bbf7d0; border-radius:20px; padding:4px 12px; font-size:12px; font-weight:600; cursor:pointer;">🚗 Motor & Mekanik</button>
                        <button type="button" class="trend-quick-cat" data-cat="sanziman" style="background:white; color:#166534; border:1px solid #bbf7d0; border-radius:20px; padding:4px 12px; font-size:12px; font-weight:600; cursor:pointer;">⚙️ Şanzıman & Vites</button>
                        <button type="button" class="trend-quick-cat" data-cat="fren" style="background:white; color:#166534; border:1px solid #bbf7d0; border-radius:20px; padding:4px 12px; font-size:12px; font-weight:600; cursor:pointer;">🛑 Fren & Yürüyen</button>
                        <button type="button" class="trend-quick-cat" data-cat="elektrik" style="background:white; color:#166534; border:1px solid #bbf7d0; border-radius:20px; padding:4px 12px; font-size:12px; font-weight:600; cursor:pointer;">⚡ Akü & Elektrik</button>
                    </div>

                    <!-- Canlı Sonuç Rozetleri -->
                    <div id="google-trends-spinner" style="display:none; text-align:center; padding:16px 0; color:#15803d; font-size:13px; font-weight:700;">
                        <div class="spinner is-active" style="float:none; display:inline-block; vertical-align:middle; margin-right:8px;"></div> Google Türkiye canlı arama tamamlamaları çekiliyor...
                    </div>

                    <div id="google-trends-results" style="display:none; margin-top:14px; padding-top:14px; border-top:1px dashed #86efac;">
                        <div style="font-size:12px; font-weight:700; color:#15803d; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
                            <span>👇 Beğendiğiniz konuya tıklayın; otomatik olarak makale formuna doldurulsun:</span>
                            <span id="google-trends-status-tag" style="background:#dcfce7; color:#166534; padding:2px 8px; border-radius:12px; font-size:11px;"></span>
                        </div>
                        <div id="google-trends-badges" style="display:flex; flex-wrap:wrap; gap:8px;"></div>
                    </div>
                </div>

<form id="makaleler-ai-form" style="margin-top:20px;">
                    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:16px; margin-bottom:16px;">
                        <div>
                            <label style="display:block; font-weight:700; font-size:13.5px; color:#1e293b; margin-bottom:6px;">Makale Konusu veya Arama Terimi <span style="color:#ef4444;">*</span></label>
                            <input type="text" id="ai_topic" required placeholder="Örn: Tamirciyi Mahkemeye Vermek veya Otomatik vites vuruntu yapıyor" style="width:100%; padding:10px 14px; font-size:14px; border-radius:8px; border:1px solid #cbd5e1;">
                        </div>
                        <div>
                            <label style="display:block; font-weight:700; font-size:13.5px; color:#1e293b; margin-bottom:6px;">Hedef Araç / Model <span style="font-weight:400; color:#64748b;">(Opsiyonel)</span></label>
                            <input type="text" id="ai_vehicle" placeholder="Örn: Renault Megane 4 veya Genel" style="width:100%; padding:10px 14px; font-size:14px; border-radius:8px; border:1px solid #cbd5e1;">
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:16px;">
                        <div>
                            <label style="display:block; font-weight:700; font-size:13.5px; color:#1e293b; margin-bottom:6px;">Kategori</label>
                            <select id="ai_category" style="width:100%; padding:9px 12px; font-size:14px; border-radius:8px; border:1px solid #cbd5e1;">
                                <option value="Arıza Rehberi">Arıza Rehberi</option>
                                <option value="Periyodik Bakım">Periyodik Bakım</option>
                                <option value="Gösterge İkaz Işıkları">Gösterge İkaz Işıkları</option>
                                <option value="Şanzıman & Vites">Şanzıman & Vites</option>
                                <option value="Motor & Mekanik">Motor & Mekanik</option>
                                <option value="Oto Elektrik">Oto Elektrik</option>
                                <option value="Hukuk & Sigorta">Hukuk & Sigorta</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; font-weight:700; font-size:13.5px; color:#1e293b; margin-bottom:6px;">Yayın Durumu</label>
                            <select id="ai_status" style="width:100%; padding:9px 12px; font-size:14px; border-radius:8px; border:1px solid #cbd5e1;">
                                <option value="publish">Doğrudan Yayına Al (Publish)</option>
                                <option value="draft">Taslak Olarak Kaydet (Draft)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Dual API Anahtarları (Gemini + Groq) -->
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:18px 20px; margin-bottom:20px;">
                        <div style="font-weight:700; font-size:14px; color:#0f172a; margin-bottom:12px; display:flex; align-items:center; gap:8px;">
                            <span>🔑</span> Yapay Zeka API Anahtarlarınız (Yedekli Çalışma)
                        </div>
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                            <div>
                                <label style="display:block; font-weight:600; font-size:12.5px; color:#334155; margin-bottom:4px;">
                                    Google Gemini API Anahtarı (AIzaSy...)
                                    <a href="https://aistudio.google.com/app/apikey" target="_blank" style="color:#2563eb; font-weight:700; float:right; text-decoration:underline;">Ücretsiz Al ↗</a>
                                </label>
                                <input type="password" id="ai_gemini_key" value="<?php echo esc_attr( $saved_gemini_key ); ?>" placeholder="AIzaSy..." style="width:100%; padding:9px 12px; font-size:13.5px; border-radius:6px; border:1px solid #cbd5e1;">
                            </div>
                            <div>
                                <label style="display:block; font-weight:600; font-size:12.5px; color:#334155; margin-bottom:4px;">
                                    Groq Cloud / xAI Grok API Anahtarı (gsk_... veya xai-...)
                                    <a href="https://console.groq.com/keys" target="_blank" style="color:#ea580c; font-weight:700; text-decoration:underline;">Groq Al ↗</a> <a href="https://console.x.ai/" target="_blank" style="color:#0284c7; font-weight:700; margin-left:8px; text-decoration:underline;">xAI Grok ↗</a>
                                </label>
                                <input type="password" id="ai_groq_key" value="<?php echo esc_attr( $saved_groq_key ); ?>" placeholder="gsk_..." style="width:100%; padding:9px 12px; font-size:13.5px; border-radius:6px; border:1px solid #cbd5e1;">
                            </div>
                        </div>
                        <div style="font-size:12px; color:#64748b; margin-top:10px;">
                            ⚡ <strong>Akıllı Failover:</strong> İki anahtarı da girdiğinizde sistem önce Gemini'yi dener; kota veya geçersiz anahtar hatası alırsa <u>kesintisiz olarak Groq'a geçer</u>.
                        </div>
                    </div>

                    <div style="display:flex; justify-content:flex-end; align-items:center;">
                        <button type="submit" id="makaleler-ai-submit" class="button button-primary button-hero" style="background:#ea580c; border-color:#ea580c; font-weight:700; border-radius:8px; padding:10px 24px; font-size:15px; display:inline-flex; align-items:center; gap:8px;">
                            <span class="dashicons dashicons-superhero" style="margin-top:2px;"></span> İnsan Diliyle Makale Üret & Kaydet
                        </button>
                    </div>
                </form>

                <!-- Canlı İlerleme Durumu -->
                <div id="makaleler-ai-loading" style="display:none; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:24px; margin-top:20px; text-align:center;">
                    <div class="spinner is-active" style="float:none; margin:0 auto 12px auto; width:30px; height:30px;"></div>
                    <div id="makaleler-ai-status-text" style="font-size:15px; font-weight:700; color:#0f172a;">1/3 Yapay zeka servisleri bağlanıyor ve en uygun model seçiliyor...</div>
                    <p style="color:#64748b; font-size:13px; margin:6px 0 0;">Yapay zeka robotik kalıpları ayıklıyor, 2026 fiyat tablosu ve Google Snippet bloklarını hazırlıyor.</p>
                </div>

                <!-- Sonuç Kutusu -->
                <div id="makaleler-ai-result" style="display:none;"></div>
            </div>

            <!-- BÖLÜM 2: 📚 HAZIR TREND MAKALE KÜTÜPHANESİ -->
            <div style="background:white; border-radius:16px; border:1px solid #e2e8f0; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.04); margin-bottom:32px;">
                <div style="padding:24px 32px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
                    <div>
                        <h2 style="font-size:19px; font-weight:800; margin:0 0 4px; color:#0f172a;">Hazır Google Trend Makaleleri (450.000+ Aylık Arama)</h2>
                        <p style="color:#64748b; font-size:13.5px; margin:0;">Türkiye'de en çok aranan 5 kritik arıza konusu için önceden hazırlanmış zengin makaleler.</p>
                    </div>
                    <div>
                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0;">
                            <?php wp_nonce_field( 'makaleler_sync_nonce' ); ?>
                            <input type="hidden" name="action" value="makaleler_sync_now">
                            <button type="submit" class="button button-secondary" style="font-weight:700; border-radius:6px; display:inline-flex; align-items:center; gap:6px;">
                                <span class="dashicons dashicons-update" style="margin-top:2px;"></span> Tümünü Senkronize Et
                            </button>
                        </form>
                    </div>
                </div>

                <!-- İstatistikler -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; padding:20px 32px; background:#f8fafc; border-bottom:1px solid #f1f5f9;">
                    <div>
                        <div style="font-size:12px; color:#64748b; font-weight:700; text-transform:uppercase;">Katalogdaki Konu</div>
                        <div style="font-size:26px; font-weight:800; color:#0f172a; margin:4px 0;"><?php echo esc_html( $total_in_catalog ); ?></div>
                    </div>
                    <div>
                        <div style="font-size:12px; color:#64748b; font-weight:700; text-transform:uppercase;">Yayındaki Makaleler</div>
                        <div style="font-size:26px; font-weight:800; color:#ea580c; margin:4px 0;"><?php echo esc_html( $published_count ); ?> / <?php echo esc_html( $total_in_catalog ); ?></div>
                    </div>
                    <div>
                        <div style="font-size:12px; color:#64748b; font-weight:700; text-transform:uppercase;">Hedef Aylık Hacim</div>
                        <div style="font-size:26px; font-weight:800; color:#2563eb; margin:4px 0;">450.000+</div>
                    </div>
                </div>

                <table class="wp-list-table widefat fixed striped table-view-list" style="border:none;">
                    <thead>
                        <tr>
                            <th style="font-weight:700; padding-left:32px;">Makale Başlığı</th>
                            <th style="width:140px; font-weight:700;">Kategori</th>
                            <th style="width:130px; font-weight:700;">Aylık Hacim</th>
                            <th style="width:110px; font-weight:700;">Durum</th>
                            <th style="width:140px; text-align:right; font-weight:700; padding-right:32px;">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $catalog as $art ): 
                            $is_published = ($art['post_obj'] && $art['post_obj']->post_status === 'publish');
                        ?>
                        <tr>
                            <td style="padding-left:32px;">
                                <strong><?php echo esc_html( $art['title'] ); ?></strong>
                                <div style="font-size:12px; color:#64748b; margin-top:4px;">Slug: <code>/<?php echo esc_html($art['slug']); ?>/</code> | <?php echo esc_html($art['read_time']); ?></div>
                            </td>
                            <td><span class="badge" style="background:#f1f5f9; color:#475569; padding:3px 8px; border-radius:6px; font-size:12px; font-weight:600;"><?php echo esc_html( $art['category'] ); ?></span></td>
                            <td><span style="color:#2563eb; font-weight:700; font-size:12.5px;"><?php echo esc_html( $art['volume'] ); ?></span></td>
                            <td>
                                <?php if ( $is_published ): ?>
                                    <span style="color:#16a34a; font-weight:700; display:inline-flex; align-items:center; gap:4px;"><span class="dashicons dashicons-yes-alt"></span> Yayında</span>
                                <?php else: ?>
                                    <span style="color:#eab308; font-weight:700; display:inline-flex; align-items:center; gap:4px;"><span class="dashicons dashicons-clock"></span> Bekliyor</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:right; padding-right:32px;">
                                <?php if ( $art['post_obj'] ): ?>
                                    <a href="<?php echo esc_url( get_permalink( $art['post_obj']->ID ) ); ?>" target="_blank" class="button button-small" title="Görüntüle">Gör</a>
                                    <a href="<?php echo esc_url( get_edit_post_link( $art['post_obj']->ID ) ); ?>" class="button button-small" title="Düzenle">Düzenle</a>
                                <?php else: ?>
                                    <span style="color:#94a3b8; font-size:12px;">Senkronize Et</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- BÖLÜM 3: 💡 TREND KONU ÖNERİLERİ (TIKLA VE YAZDIR) -->
            <div style="background:#f8fafc; border-radius:16px; border:1px solid #e2e8f0; padding:24px 32px; margin-bottom:32px;">
                <h3 style="font-size:16px; font-weight:800; color:#0f172a; margin:0 0 8px;">💡 Tek Tıkla Yazdırabileceğin Popüler Trend Konu Fikirleri</h3>
                <p style="color:#64748b; font-size:13px; margin:0 0 16px;">Aşağıdaki konulardan birine tıkladığında yukarıdaki form otomatik dolar:</p>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:12px;">
                    <div class="ai-suggest-chip" data-topic="TÜVTÜRK 2026 Araç Muayenesi Ağır Kusurlar Listesi ve Çözümleri" data-category="Periyodik Bakım" style="cursor:pointer; background:white; padding:12px 16px; border-radius:8px; border:1px solid #cbd5e1; font-size:13px; transition:all 0.2s;">
                        📌 <strong>TÜVTÜRK 2026 Ağır Kusurlar Listesi</strong> <span style="float:right; color:#2563eb; font-weight:700;">110K/ay</span>
                    </div>
                    <div class="ai-suggest-chip" data-topic="Oto Klima Neden Soğutmaz? 2026 Gaz Dolum ve Kompresör Fiyatları" data-category="Arıza Rehberi" style="cursor:pointer; background:white; padding:12px 16px; border-radius:8px; border:1px solid #cbd5e1; font-size:13px; transition:all 0.2s;">
                        📌 <strong>Oto Klima Gazı Bittiği Nasıl Anlaşılır?</strong> <span style="float:right; color:#2563eb; font-weight:700;">70K/ay</span>
                    </div>
                    <div class="ai-suggest-chip" data-topic="Otomatik Şanzıman Vuruntu Yapıyor Neden Olur? Selenoid Valf Arızası" data-category="Şanzıman & Vites" style="cursor:pointer; background:white; padding:12px 16px; border-radius:8px; border:1px solid #cbd5e1; font-size:13px; transition:all 0.2s;">
                        📌 <strong>Otomatik Şanzıman Vuruntu Arızası</strong> <span style="float:right; color:#2563eb; font-weight:700;">55K/ay</span>
                    </div>
                    <div class="ai-suggest-chip" data-topic="Tamirciyi Mahkemeye Vermek: Hak Arama, Tüketici Hakem Heyeti ve Bilirkişi Raporu Rehberi" data-category="Hukuk & Sigorta" style="cursor:pointer; background:white; padding:12px 16px; border-radius:8px; border:1px solid #cbd5e1; font-size:13px; transition:all 0.2s;">
                        📌 <strong>Tamirciyi Mahkemeye Vermek & Hak Arama</strong> <span style="float:right; color:#2563eb; font-weight:700;">60K/ay</span>
                    </div>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Öneri kutularına tıklama
            $('.ai-suggest-chip').on('click', function() {
                var topic = $(this).data('topic');
                var cat   = $(this).data('category');
                $('#ai_topic').val(topic);
                if (cat) $('#ai_category').val(cat);
                $('html, body').animate({ scrollTop: $('#makaleler-ai-form').offset().top - 80 }, 300);
                $('#ai_topic').focus();
            }).hover(function() {
                $(this).css({'border-color':'#2563eb', 'box-shadow':'0 2px 8px rgba(37,99,235,0.1)'});
            }, function() {
                $(this).css({'border-color':'#cbd5e1', 'box-shadow':'none'});
            });

            // Form Submit
                        // GOOGLE TRENDS ARAMA AVCI FONKSIYONLARI
            function fetchGoogleTrends(keyword, category) {
                var $spinner = $('#google-trends-spinner');
                var $results = $('#google-trends-results');
                var $badges = $('#google-trends-badges');
                var $status = $('#google-trends-status-tag');

                $spinner.show();
                $results.hide();

                $.post(ajaxurl, {
                    action: 'makaleler_fetch_google_trends',
                    security: '<?php echo wp_create_nonce("makaleler_ai_nonce"); ?>',
                    keyword: keyword || '',
                    category: category || 'all'
                }, function(res) {
                    $spinner.hide();
                    if (res.success && res.data.trends && res.data.trends.length > 0) {
                        $badges.empty();
                        $.each(res.data.trends, function(idx, item) {
                            var badgeHtml = '<button type="button" class="trend-badge-item" data-query="' + $('<div>').text(item.query).html() + '" style="background:white; border:1px solid #86efac; border-radius:8px; padding:7px 12px; font-size:13px; font-weight:600; color:#0f172a; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 1px 3px rgba(0,0,0,0.03); transition:all 0.15s ease;">' +
                                '<span style="color:#16a34a; font-size:11px; background:#dcfce7; padding:2px 6px; border-radius:4px; font-weight:700;">' + item.badge + '</span> ' +
                                '<span>' + $('<div>').text(item.query).html() + '</span>' +
                                '<span style="color:#94a3b8; font-size:11px;">+ Seç</span>' +
                                '</button>';
                            $badges.append(badgeHtml);
                        });
                        $status.text(res.data.count + ' Adet Canlı Trend Bulundu');
                        $results.slideDown(200);
                    } else {
                        $badges.html('<div style="color:#64748b; font-size:13px;">Canlı sorgu bulunamadı, lütfen başka bir kelime deneyin.</div>');
                        $results.slideDown(200);
                    }
                }).fail(function() {
                    $spinner.hide();
                });
            }

            // Trend tıklandığında forma doldur
            $(document).on('click', '.trend-badge-item', function(e) {
                e.preventDefault();
                var query = $(this).data('query');
                $('#ai_topic').val(query);
                
                // Animasyon efekti
                $('#ai_topic').css('background', '#fef9c3').animate({ background: '#ffffff' }, 800);

                // Otomatik araç tespiti
                var qLower = query.toLowerCase();
                var vehicles = ['clio', 'megane', 'egea', 'corolla', 'passat', 'golf', 'focus', 'civic', 'polo', 'fiesta', 'qashqai', 'duster', 'audi', 'bmw', 'mercedes'];
                var detected = '';
                $.each(vehicles, function(i, v) {
                    if (qLower.indexOf(v) !== -1) {
                        detected = v.charAt(0).toUpperCase() + v.slice(1);
                        return false;
                    }
                });
                if (detected) {
                    $('#ai_vehicle').val(detected);
                }

                // Otomatik kategori tespiti
                if (qLower.indexOf('vites') !== -1 || qLower.indexOf('şanzıman') !== -1 || qLower.indexOf('dsg') !== -1 || qLower.indexOf('debriyaj') !== -1) {
                    $('#ai_category').val('Şanzıman & Vites');
                } else if (qLower.indexOf('lamba') !== -1 || qLower.indexOf('ışık') !== -1 || qLower.indexOf('ikaz') !== -1) {
                    $('#ai_category').val('Gösterge İkaz Işıkları');
                } else if (qLower.indexOf('akü') !== -1 || qLower.indexOf('marş') !== -1 || qLower.indexOf('şarj') !== -1 || qLower.indexOf('sigorta') !== -1) {
                    $('#ai_category').val('Oto Elektrik');
                } else if (qLower.indexOf('bakım') !== -1 || qLower.indexOf('yağ değiş') !== -1 || qLower.indexOf('filtre') !== -1) {
                    $('#ai_category').val('Periyodik Bakım');
                } else {
                    $('#ai_category').val('Arıza Rehberi');
                }

                // Form butonuna odaklan
                $('html, body').animate({
                    scrollTop: $('#makaleler-ai-submit').offset().top - 300
                }, 300);
            });

            // Hover efekti
            $(document).on('mouseenter', '.trend-badge-item', function() {
                $(this).css({ 'border-color': '#16a34a', 'background': '#f0fdf4', 'transform': 'translateY(-1px)' });
            }).on('mouseleave', '.trend-badge-item', function() {
                $(this).css({ 'border-color': '#86efac', 'background': 'white', 'transform': 'none' });
            });

            // Buton tıklaması
            $('#btn-fetch-google-trends').on('click', function(e) {
                e.preventDefault();
                var kw = $('#google-trend-keyword').val();
                fetchGoogleTrends(kw, 'all');
            });

            $('#google-trend-keyword').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#btn-fetch-google-trends').click();
                }
            });

            // Kategori chip tıklamaları
            $('.trend-quick-cat').on('click', function(e) {
                e.preventDefault();
                $('.trend-quick-cat').removeClass('active-cat').css({ 'background': 'white', 'color': '#166534', 'border': '1px solid #bbf7d0' });
                $(this).addClass('active-cat').css({ 'background': '#16a34a', 'color': 'white', 'border': 'none' });
                var cat = $(this).data('cat');
                $('#google-trend-keyword').val('');
                fetchGoogleTrends('', cat);
            });

            // Sayfa açıldığında otomatik olarak popüler canlı trendleri çek
            fetchGoogleTrends('', 'all');


            $('#makaleler-ai-form').on('submit', function(e) {
                e.preventDefault();
                var $btn = $('#makaleler-ai-submit');
                var $loading = $('#makaleler-ai-loading');
                var $result = $('#makaleler-ai-result');
                var $statusText = $('#makaleler-ai-status-text');

                $btn.prop('disabled', true).css('opacity', '0.6');
                $loading.show();
                $result.hide();
                $statusText.text('1/3 Yapay zeka servisleri kontrol ediliyor ve en uygun model seciliyor...');

                var timer1 = setTimeout(function() {
                    $statusText.text('2/3 2026 Fiyat tablosu, Google Snippet ve SSS yaziliyor...');
                }, 4500);

                var timer2 = setTimeout(function() {
                    $statusText.text('3/3 Görseller ve ic baglantilar olusturuluyor...');
                }, 9500);

                var data = {
                    action: 'makaleler_generate_ai_article',
                    security: '<?php echo wp_create_nonce("makaleler_ai_nonce"); ?>',
                    topic: $('#ai_topic').val(),
                    vehicle: $('#ai_vehicle').val(),
                    category: $('#ai_category').val(),
                    post_status: $('#ai_status').val(),
                    gemini_key: $('#ai_gemini_key').val(),
                    groq_key: $('#ai_groq_key').val()
                };

                $.post(ajaxurl, data, function(res) {
                    clearTimeout(timer1);
                    clearTimeout(timer2);
                    $btn.prop('disabled', false).css('opacity', '1');
                    $loading.hide();

                    if (res.success) {
                        $result.html(
                            '<div style="background:#f0fdf4; border:1px solid #86efac; border-radius:12px; padding:20px; margin-top:20px;">' +
                            '<div style="display:flex; align-items:center; gap:10px; color:#166534; font-weight:700; font-size:16px;">' +
                            '<span class="dashicons dashicons-yes-alt" style="font-size:24px; width:24px; height:24px;"></span> ' +
                            'Makale Başarıyla Üretildi ve Kaydedildi! (' + res.data.status + ') - <span style="font-size:13px; font-weight:600; color:#2563eb;">' + res.data.provider + '</span>' +
                            '</div>' +
                            '<h3 style="margin:12px 0 6px 0; color:#0f172a; font-size:18px;">' + res.data.title + '</h3>' +
                            '<p style="color:#475569; font-size:14px; margin:0 0 16px;">' + (res.data.excerpt || '') + '</p>' +
                            '<div style="display:flex; gap:12px; flex-wrap:wrap;">' +
                            '<a href="' + res.data.view_url + '" target="_blank" class="button button-primary" style="background:#16a34a; border-color:#16a34a;"><span class="dashicons dashicons-external" style="margin-top:4px;"></span> Makaleyi Canlı Görüntüle</a>' +
                            '<a href="' + res.data.edit_url + '" target="_blank" class="button"><span class="dashicons dashicons-edit" style="margin-top:4px;"></span> WordPress Editöründe Düzenle</a>' +
                            '</div>' +
                            '</div>'
                        ).show();
                        $('#ai_topic').val('');
                        $('#ai_vehicle').val('');
                    } else {
                        $result.html(
                            '<div style="background:#fef2f2; border:1px solid #fca5a5; border-radius:12px; padding:18px; margin-top:20px; color:#991b1b; font-size:14px; line-height:1.6;">' +
                            '<div style="font-weight:700; margin-bottom:4px; font-size:15px;">⚠️ API veya Bağlantı Hatası:</div>' +
                            (res.data || 'Bilinmeyen bir hata oluştu.') +
                            '</div>'
                        ).show();
                    }
                }).fail(function(xhr, status, error) {
                    clearTimeout(timer1);
                    clearTimeout(timer2);
                    $btn.prop('disabled', false).css('opacity', '1');
                    $loading.hide();
                    $result.html(
                        '<div style="background:#fef2f2; border:1px solid #fca5a5; border-radius:12px; padding:18px; margin-top:20px; color:#991b1b;">' +
                        '<strong>Sunucu / Bağlantı Hatası:</strong> İstek zaman aşımına uğradı veya sunucu yanıt vermedi (' + error + ').' +
                        '</div>'
                    ).show();
                });
            });
        });
        </script>
        <?php
    }


    public static function get_articles_catalog() {
        $articles = array();

        // 1. MOTOR ARIZA LAMBASI
        $c1 = <<<'EOD'
<div class="blog-callout-box">
    <strong>Hızlı Teşhis Özeti:</strong> Motor arıza lambası (Check Engine), motor kontrol ünitesinin (ECU) egzoz emisyonu, yakıt-hava karışımı veya ateşleme sisteminde bir anormallik algıladığını gösterir. <strong>Sabit sarı yanıyorsa:</strong> Aracı zorlamadan en yakın servise sürebilirsiniz. <strong>Yanıp sönüyorsa (flaş yapıyorsa):</strong> Silindirlerde tekleme veya katalizöre yakıt kaçağı vardır; aracı hemen durdurmalı ve motoru kapatmalısınız.
</div>

<h2>Motor Arıza Lambasının Renkleri ve Anlamları</h2>
<p>Modern araçlarda motor ikaz lambası iki farklı renkte ve iki farklı çalışma biçiminde yanabilir:</p>
<ul>
    <li><strong>Sabit Sarı Işık:</strong> Kritik olmayan ancak acil incelenmesi gereken emisyon veya sensör uyarısıdır (Oksijen sensörü, MAF sensörü, termostat vb.). Araç genellikle sürülmeye devam edebilir.</li>
    <li><strong>Yanıp Sönen Sarı Işık:</strong> Ağır motor teklemesi (misfire) anlamına gelir. Yanmamış çiğ yakıt egzoza giderek katalitik konvertörü eritebilir. Sürüşe kesinlikle devam edilmemelidir.</li>
    <li><strong>Kırmızı İkaz Lambası:</strong> Yağ basıncı kaybı veya kritik hararet gibi hayati bir tehlikeyi işaret eder. Kontak derhal kapatılmalıdır.</li>
</ul>

<h2>Motor Arıza Lambasını Yakan En Yaygın 7 Sebep</h2>

<h3>1. Oksijen (Lambda) Sensörü Arızası</h3>
<p>Egzoz gazındaki yanmamış oksijen miktarını ölçen bu sensör bozulduğunda araç zengin veya fakir karışımla çalışır. Yakıt tüketimi %25-40 oranında artar ve motor arıza ışığı sabit yanar.</p>

<h3>2. Katalitik Konvertör Tıkanması veya Verimsizliği (P0420 Kodu)</h3>
<p>Zamanla kurum bağlayan veya iç porseleni çatlayan katalizör, gazları temizleyemez hale geldiğinde lamba devreye girer. Egzozdan kötü kükürt/çürük yumurta kokusu duyulabilir.</p>

<h3>3. Buji ve Ateşleme Bobini Problemleri</h3>
<p>Özellikle benzinli ve LPG'li araçlarda bujilerin aşınması veya bobinlerin çatlaması sonucu tekleme meydana gelir. Gaz pedalına bastığınızda araç titrer ve arıza lambası yanıp söner.</p>

<h3>4. Hava Akış Metresi (MAF Sensörü) Kirlenmesi</h3>
<p>Motora giren havanın kütlesini ölçen MAF sensörü toz veya yağ buharı nedeniyle kirlendiğinde rölantide dalgalanma ve gaz yememe sorunu baş gösterir.</p>

<h3>5. EGR Valfi Kurum Bağlaması</h3>
<p>Egzoz gazı devridaim valfi (EGR) özellikle dizel araçlarda kurum dolayısıyla takılı kalabilir. Araç 3000 deviri geçmez ve koruma moduna girer.</p>

<h3>6. Gevşek veya Çatlak Yakıt Depo Kapağı</h3>
<p>Şaşırtıcı şekilde motor lambasının en basit sebeplerinden biridir. Depo kapağı tam oturmadığında buhar tahliye sistemi (EVAP) basınç kaybı algılar ve hata verir.</p>

<h3>7. Termostat ve Hararet Müşürü Hatası</h3>
<p>Termostat açık kaldığında motor ideal çalışma sıcaklığına (90°C) ulaşamaz. ECU motorun sürekli soğuk çalıştığını düşünerek fazla yakıt püskürtür ve arıza kaydı açar.</p>

<div class="blog-alert-warning">
    <strong>⚠️ Efsane: "Akü Kutup Başını Sökünce Işık Söner mi?"</strong><br>
    Akü kutup başını sökmek geçici olarak hafızadaki kodu silebilir; ancak fiziksel arıza devam ettiği için 5-20 km sonra lamba tekrar yanacaktır. Üstelik teyp kodu, saat ve boğaz kelebeği adaptasyonu da sıfırlanabilir. Kalıcı çözüm OBD2 cihazı ile arıza kodunu okumaktır.
</div>

<h2>2026 Motor Arıza Onarım ve Parça Maliyetleri</h2>
<table class="blog-price-table">
    <thead>
        <tr>
            <th>Arıza / İşlem Türü</th>
            <th>Parça Maliyeti</th>
            <th>İşçilik Ücreti</th>
            <th>Ortalama Toplam</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>OBD2 Bilgisayarlı Arıza Tespiti</td>
            <td>—</td>
            <td>400 ₺ - 800 ₺</td>
            <td><strong>500 ₺ - 800 ₺</strong></td>
        </tr>
        <tr>
            <td>Oksijen (Lambda) Sensörü Değişimi</td>
            <td>1.400 ₺ - 3.800 ₺</td>
            <td>500 ₺ - 1.000 ₺</td>
            <td><strong>1.900 ₺ - 4.800 ₺</strong></td>
        </tr>
        <tr>
            <td>Buji & Ateşleme Bobini Takımı</td>
            <td>1.800 ₺ - 5.500 ₺</td>
            <td>600 ₺ - 1.200 ₺</td>
            <td><strong>2.400 ₺ - 6.700 ₺</strong></td>
        </tr>
        <tr>
            <td>EGR Valfi Temizliği / Değişimi</td>
            <td>1.000 ₺ (Temizlik) / 4.500 ₺ (Sıfır)</td>
            <td>1.000 ₺ - 2.000 ₺</td>
            <td><strong>2.000 ₺ - 6.500 ₺</strong></td>
        </tr>
        <tr>
            <td>Katalitik Konvertör Onarımı / Değişimi</td>
            <td>6.000 ₺ - 18.000 ₺</td>
            <td>1.500 ₺ - 3.000 ₺</td>
            <td><strong>7.500 ₺ - 21.000 ₺</strong></td>
        </tr>
    </tbody>
</table>

<div class="blog-cta-box">
    <h3>Aracınızda Motor Arıza Lambası mı Yandı?</h3>
    <p>Belirtilerinizi platformumuzdaki yapay zeka arıza tespit robotuna girerek anında muhtemel sebebi öğrenin veya şehrinizdeki bilgisayarlı oto elektrik ustalarından randevu alın.</p>
    <div class="blog-cta-buttons">
        <a href="{{BASE_URL}}/ariza-tespiti/" class="blog-cta-btn-primary"><i class="fa-solid fa-robot"></i> Yapay Zeka ile Arıza Tespiti Yap</a>
        <a href="{{BASE_URL}}/ariza-kodlari/" class="blog-cta-btn-secondary"><i class="fa-solid fa-barcode"></i> OBD2 Arıza Kodunu Sorgula</a>
        <a href="{{BASE_URL}}/ustalar/" class="blog-cta-btn-outline"><i class="fa-solid fa-wrench"></i> En Yakın Oto Servisi Bul</a>
    </div>
</div>

<h2>Sıkça Sorulan Sorular</h2>
<h3>Motor arıza lambası yanarken araba muayeneden geçer mi?</h3>
<p>Hayır. TÜVTÜRK standartlarına göre gösterge panelinde motor arıza lambası yanması <strong>ağır kusur</strong> sayılır ve araç muayeneden geçemez. Muayeneye gitmeden önce arızanın çözülüp ışığın söndürülmesi zorunludur.</p>

<h3>Motor arıza lambası kendiliğinden söner mi?</h3>
<p>Eğer hata geçici bir yakıt kalitesi veya gevşek depo kapağından kaynaklandıysa, problem çözüldükten sonra araç 3-5 sürüş döngüsü boyunca sorun görmezse lambayı kendiliğinden söndürebilir. Donanımsal arızalarda kendiliğinden sönmez.</p>
EOD;

        $articles[] = array(
            'title'     => 'Motor Arıza Lambası Neden Yanar? Yanıp Sönmesi Ne Anlama Gelir? (2026 Çözüm Rehberi)',
            'slug'      => 'motor-ariza-lambasi-neden-yanar-nasil-sondurulur',
            'category'  => 'Arıza Rehberi',
            'volume'    => '160.000 / ay',
            'image'     => 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?auto=format&fit=crop&w=1200&q=80',
            'read_time' => '6 dk okuma',
            'excerpt'   => 'Motor arıza lambası sarı veya kırmızı yandığında ne yapılmalı? En sık rastlanan 7 arıza nedeni, söndürme yöntemleri ve 2026 tamir maliyeti tablosu.',
            'content'   => $c1,
        );

        // 2. BASKI BALATA
        $c2 = <<<'EOD'
<div class="blog-callout-box">
    <strong>Hızlı Teşhis Özeti:</strong> Baskı balatanın bittiğini gösteren en net belirti; gaza bastığınızda motor devrinin fırlaması ancak aracın aynı oranda hızlanmamasıdır (debriyaj kaçırması). Ayrıca debriyaj pedalının aşırı sertleşmesi, kavrama noktasının en yukarı çıkması ve yokuşta gelen yanık kokusu balatanın son demlerinde olduğunu kesinleştirir.
</div>

<h2>Baskı Balatanın Bittiğini Gösteren 6 Kesin Belirti</h2>

<h3>1. Debriyaj Pedalının Taş Gibi Sertleşmesi</h3>
<p>Baskı şemsiyeleri (yayları) aşındıkça ve balata inceldikçe debriyaj pedalına basmak zorlaşır. Şehir içi trafikte bacağınızı ağrıtacak derecede sert bir pedal, değişimin habercisidir.</p>

<h3>2. Kavrama Noktasının Aşırı Yukarıya Çıkması</h3>
<p>Normal bir araçta pedal tabandan 2-3 parmak yukarıda kavramaya başlar. Eğer ayağınızı debriyaj pedalından neredeyse tamamen çektikten sonra araç hareket ediyorsa balata kalınlığı tükenmiştir.</p>

<h3>3. Yokuşta Bağırıp Gitmeme (Debriyaj Kaçırması)</h3>
<p>Aracınızla yokuş çıkarken gaza yüklendiğinizde devir göstergesi hızla yükseliyor ancak araç hızlanmıyorsa, balata volan yüzeyine tam tutunamıyor ve kaydırıyor demektir.</p>

<h3>4. Ağır Yanık Asbest Kokusu</h3>
<p>Özellikle dik yokuşlarda dur-kalk yaparken veya geri manevralarda araç içine giren keskin, yanmış elektrik/fren benzeri koku balatanın sürtünmeden dolayı aşırı ısındığını gösterir.</p>

<h3>5. Kalkış Anında Titreme ve Silkeleme</h3>
<p>Kavrama yüzeyinin dengesiz aşınması veya volanın yamulması sebebiyle, aracı 1. viteste kaldırırken tüm kasa sallanır ve silkelenir.</p>

<h3>6. 1. Vitese ve Geri Vitese Zor Geçme</h3>
<p>Baskı sistemi tam ayıramadığı için şanzıman milleri dönmeye devam eder ve özellikle 1. vitese takarken zorlanma veya geri viteste dişli sürtme sesi duyulur.</p>

<h2>Evde 2 Dakikada Baskı Balata Testi Nasıl Yapılır?</h2>
<p>Düz ve güvenli bir alanda şu testi uygulayabilirsiniz:</p>
<ol>
    <li>El frenini sonuna kadar çekin.</li>
    <li>Debriyaja basarak vitesi <strong>3. vitese</strong> takın.</li>
    <li>Ayağınızı debriyajdan yavaşça çekerken hafifçe gaz verin.</li>
    <li><strong>Sonuç:</strong> Araç anında stop ediyorsa debriyajınız iyi durumdadır. Eğer motor stop etmiyor, çalışmaya devam ediyor ve devir yükseliyorsa baskı balatanız tamamen bitmiştir.</li>
</ol>

<h2>2026 Marka Bazlı Baskı Balata Değişim Fiyatları</h2>
<table class="blog-price-table">
    <thead>
        <tr>
            <th>Araç Modeli</th>
            <th>Debriyaj Seti (Parça)</th>
            <th>Şanzıman İndirme İşçiliği</th>
            <th>Toplam Maliyet</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Fiat Egea / Linea (1.3 & 1.4)</td>
            <td>2.800 ₺ - 4.500 ₺</td>
            <td>2.500 ₺ - 4.000 ₺</td>
            <td><strong>5.300 ₺ - 8.500 ₺</strong></td>
        </tr>
        <tr>
            <td>Renault Megane / Clio (1.5 dCi)</td>
            <td>3.500 ₺ - 6.000 ₺</td>
            <td>3.000 ₺ - 4.500 ₺</td>
            <td><strong>6.500 ₺ - 10.500 ₺</strong></td>
        </tr>
        <tr>
            <td>Volkswagen Golf / Polo (Manuel)</td>
            <td>4.800 ₺ - 8.500 ₺</td>
            <td>3.500 ₺ - 5.500 ₺</td>
            <td><strong>8.300 ₺ - 14.000 ₺</strong></td>
        </tr>
        <tr>
            <td>Ford Focus / Fiesta</td>
            <td>3.800 ₺ - 7.000 ₺</td>
            <td>3.000 ₺ - 4.500 ₺</td>
            <td><strong>6.800 ₺ - 11.500 ₺</strong></td>
        </tr>
        <tr>
            <td>Toyota Corolla</td>
            <td>4.200 ₺ - 7.500 ₺</td>
            <td>3.000 ₺ - 5.000 ₺</td>
            <td><strong>7.200 ₺ - 12.500 ₺</strong></td>
        </tr>
    </tbody>
</table>

<div class="blog-cta-box">
    <h3>Baskı Balatanız Kaçırıyor mu?</h3>
    <p>Biten balatayla yola devam etmek volanı çizer ve masrafı 3 katına çıkarır. Şehrinizdeki onaylı şanzıman ve mekanik ustalarından hemen ücretsiz fiyat teklifi alın.</p>
    <div class="blog-cta-buttons">
        <a href="{{BASE_URL}}/ustalar/" class="blog-cta-btn-primary"><i class="fa-solid fa-wrench"></i> En Yakın Şanzıman Ustasını Bul</a>
        <a href="{{BASE_URL}}/hizmetler/" class="blog-cta-btn-secondary"><i class="fa-solid fa-list-check"></i> Tamir Hizmetlerini İncele</a>
    </div>
</div>
EOD;

        $articles[] = array(
            'title'     => 'Baskı Balata Bittiği Nasıl Anlaşılır? Debriyaj Kaçırma Belirtileri & 2026 Değişim Fiyatı',
            'slug'      => 'baski-balata-bittigi-nasil-anlasilir-degisim-fiyati',
            'category'  => 'Periyodik Bakım',
            'volume'    => '95.000 / ay',
            'image'     => 'https://images.unsplash.com/photo-1486006920555-c77dce18193b?auto=format&fit=crop&w=1200&q=80',
            'read_time' => '7 dk okuma',
            'excerpt'   => 'Debriyaj kaçırması nasıl test edilir? Baskı balatanın bittiğini gösteren 6 kesin işaret, volan hasarı riskleri ve araç markalarına göre 2026 değişim ücretleri.',
            'content'   => $c2,
        );

        // 3. DPF
        $c3 = <<<'EOD'
<div class="blog-callout-box">
    <strong>Hızlı Teşhis Özeti:</strong> Dizel Partikül Filtresi (DPF), dizel motorların egzozundan çıkan zararlı kurum taneciklerini hapseden seramik petektir. Kısa mesafe şehir içi kullanımda ısınamadığı için dolar. Tıkandığında araç gaz yemez, yakıt sarfiyatı fırlar ve göstergede sarı egzoz/sarmal ikaz ışığı yanar.
</div>

<h2>DPF Tıkanıklığının 5 Kritik Belirtisi</h2>
<ul>
    <li><strong>1. Gösterge Panelinde DPF İkaz Işığı:</strong> Egzoz borusu üzerinde noktacıklar olan sarı renkli simge yanar.</li>
    <li><strong>2. Motorun Koruma Moduna Geçmesi:</strong> Turbo devre dışı kalır gibi araç hantallaşır ve 2500-3000 devirin üstüne çıkamaz.</li>
    <li><strong>3. Yakıt Tüketiminde Ciddi Artış:</strong> Araç sürekli arka planda rejenerasyon yapmaya çalıştığı için fazladan mazot püskürtür.</li>
    <li><strong>4. Yağ Seviyesinin Yükselmesi:</strong> Tamamlanamayan rejenerasyonlar sonucu silindire püskürtülen fazla mazot segmanlardan süzülerek karterdeki motor yağına karışır. Yağ seviyesi yükselir ve motorun ambeleye kalkmasına yol açabilir.</li>
    <li><strong>5. Ağır Egzoz Kokusu ve Fanın Sürekli Çalışması:</strong> Kontağı kapattıktan sonra bile ön kaputtan jet motoru gibi fan sesi gelmesi DPF rejenerasyonunun yarıda kaldığını gösterir.</li>
</ul>

<h2>Yolda DPF Rejenerasyonu (Kendi Kendine Temizleme) Nasıl Yapılır?</h2>
<p>DPF ışığı yeni yandıysa servise gitmeden önce şu adımları uygulayın:</p>
<ol>
    <li>Aracınızı çevre yoluna veya otobana çıkarın.</li>
    <li>Motorun hararet göstergesinin 90°C olduğundan emin olun.</li>
    <li>Vitesi manuel konuma alarak devri <strong>2.500 – 3.000 devir/dakika</strong> aralığında sabitleyin (Örn: 4. viteste 90-100 km/s hızla).</li>
    <li>Bu devirde kesintisiz olarak <strong>20-25 dakika</strong> boyunca sürüş yapın.</li>
    <li>Egzoz sıcaklığı 600°C üzerine çıkacak ve biriken kurumlar kül haline gelerek dışarı atılacaktır. Işık sönecektir.</li>
</ol>

<h2>DPF İptali mi Yoksa Makineyle Temizleme mi?</h2>
<div class="blog-alert-warning">
    <strong>⚠️ DPF İptalinin Riskleri:</strong> DPF\'yi içi boşaltılarak yazılımla iptal ettirmek kısa vadede masrafsız gibi görünse de; 2026 yılı TÜVTÜRK egzoz emisyon standartları ve çevre denetimlerinde araçların <strong>ağır kusurla kalmasına</strong> neden olmaktadır. Ayrıca aracınız arkadan siyah duman atar ve ikinci elde değer kaybeder. En sağlıklı çözüm profesyonel makineyle sulu/ilaçlı temizliktir.
</div>

<h2>2026 DPF Temizlik ve Onarım Fiyatları</h2>
<table class="blog-price-table">
    <thead>
        <tr>
            <th>İşlem Adı</th>
            <th>Ortalama Ücret</th>
            <th>İşlem Süresi</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Cihazla Statik Rejenerasyon</td>
            <td>700 ₺ - 1.200 ₺</td>
            <td>45 dakika</td>
        </tr>
        <tr>
            <td>Sökülerek Makinede Sulu & Kimyasal DPF Yıkama</td>
            <td>2.500 ₺ - 4.500 ₺</td>
            <td>3 - 4 saat</td>
        </tr>
        <tr>
            <td>DPF Fark Basınç Sensörü Değişimi</td>
            <td>1.500 ₺ - 3.200 ₺</td>
            <td>1 saat</td>
        </tr>
        <tr>
            <td>Yeni Orijinal / Yan Sanayi DPF Değişimi</td>
            <td>18.000 ₺ - 55.000 ₺</td>
            <td>1 gün</td>
        </tr>
    </tbody>
</table>

<div class="blog-cta-box">
    <h3>DPF Işığınız Sönmüyor mu?</h3>
    <p>Tıkanmış filtre turbo arızasına ve motor kilitlenmesine neden olabilir. Şehrinizdeki garantili DPF temizleme ve dizel enjektör servislerini anında keşfedin.</p>
    <div class="blog-cta-buttons">
        <a href="{{BASE_URL}}/ustalar/" class="blog-cta-btn-primary"><i class="fa-solid fa-wrench"></i> DPF Temizleme Servisi Bul</a>
        <a href="{{BASE_URL}}/ariza-tespiti/" class="blog-cta-btn-secondary"><i class="fa-solid fa-robot"></i> Arıza Analizi Başlat</a>
    </div>
</div>
EOD;

        $articles[] = array(
            'title'     => 'Dizel Partikül Filtresi (DPF) Tıkandı Belirtileri, Rejenerasyon ve 2026 Temizleme Ücreti',
            'slug'      => 'dizel-partikul-filtresi-dpf-tikandi-belirtileri-temizleme-ucreti',
            'category'  => 'Arıza Rehberi',
            'volume'    => '80.000 / ay',
            'image'     => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1200&q=80',
            'read_time' => '6 dk okuma',
            'excerpt'   => 'Dizel araçlarda DPF tıkanıklığı nasıl anlaşılır? Rejenerasyon nasıl yapılır, makineyle yıkama fiyatı ne kadar ve DPF iptali muayeneden geçer mi?',
            'content'   => $c3,
        );

        // 4. TRİGER
        $c4 = <<<'EOD'
<div class="blog-callout-box">
    <strong>Hızlı Bilgi Özeti:</strong> Triger kayışı ortalama <strong>80.000 – 120.000 km</strong> arasında veya km dolmasa bile <strong>4 – 5 yılda bir</strong> mutlaka değiştirilmelidir. Kauçuk malzeme zamanla kurur ve çatlar. Kayış koptuğu anda supaplar pistonların tepesine çarpar ve motor komple kilitlenir; 80.000 - 150.000 TL arası motor rektifiye faturası çıkar.
</div>

<h2>Triger Kayışı Nedir ve Motorda Ne İşe Yarar?</h2>
<p>Krank mili ile eksantrik milini birbirine bağlayan triger kayışı, motor supaplarının piston hareketleriyle kusursuz bir senkronizasyon içinde açılıp kapanmasını sağlar. Bir nevi motorun kalbidir.</p>

<h2>Triger Kayışı Değişim Zamanı Nasıl Hesaplanır?</h2>
<ul>
    <li><strong>Kilometre Kriteri:</strong> Üreticiye göre değişmekle birlikte genellikle 90.000 km ağır bakım periyodudur.</li>
    <li><strong>Yıl Kriteri:</strong> Araç sadece 30.000 km yapmış olsa bile 5 yıl geçmişse kayış bayatlamıştır ve kopma riski taşır.</li>
    <li><strong>Triger Zincirli Araçlar:</strong> Zincirler genellikle 200.000 – 250.000 km'ye kadar dayanır. İlk çalıştırmada şıkırtı sesi gelmeye başladığında zincir uzamıştır ve değişmelidir.</li>
</ul>

<h2>Triger Koparsa Motorda Ne Olur?</h2>
<p>Seyir halindeyken triger koptuğu an şu zincirleme felaket gerçekleşir:</p>
<ol>
    <li>Eksantrik mili durur ancak tekerleklerin dönme momentiyle krank mili ve pistonlar binlerce devirle yukarı çıkmaya devam eder.</li>
    <li>Açık kalan supaplar piston tepelerine çarpar.</li>
    <li>Supaplar eğrilir, piston tepeleri delinir, silindir kapağı çatlar ve motor bloğu hasar görür.</li>
    <li>Araç anında stop eder ve bir daha çalışmaz.</li>
</ol>

<h2>Devirdaim Su Pompası Neden Trigerle Birlikte Değişmelidir?</h2>
<p>Çoğu motorda devirdaim su pompası triger kayışı üzerinden güç alır. Trigeri sıfırlayıp su pompasını eski bırakırsanız; pompa 15.000 km sonra kilitlendiğinde yeni taktırdığınız trigeri de koparır. Üstelik aynı ağır işçilik ücretini ikinci kez ödemek zorunda kalırsınız.</p>

<h2>2026 Triger Seti & Ağır Bakım Fiyatları</h2>
<table class="blog-price-table">
    <thead>
        <tr>
            <th>Araç Grubu</th>
            <th>Triger Seti + Devirdaim</th>
            <th>Ağır Bakım İşçiliği</th>
            <th>Toplam Masraf</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Fiat / Renault (1.3 & 1.5 Dizel)</td>
            <td>3.200 ₺ - 5.500 ₺</td>
            <td>2.500 ₺ - 4.000 ₺</td>
            <td><strong>5.700 ₺ - 9.500 ₺</strong></td>
        </tr>
        <tr>
            <td>VAG Grubu (1.6 TDI / 1.0 TSI / 1.5 TSI)</td>
            <td>5.500 ₺ - 9.500 ₺</td>
            <td>3.500 ₺ - 6.000 ₺</td>
            <td><strong>9.000 ₺ - 15.500 ₺</strong></td>
        </tr>
        <tr>
            <td>Ford / Peugeot / Citroen (1.5 - 1.6 HDi)</td>
            <td>4.200 ₺ - 7.500 ₺</td>
            <td>3.000 ₺ - 5.000 ₺</td>
            <td><strong>7.200 ₺ - 12.500 ₺</strong></td>
        </tr>
    </tbody>
</table>

<div class="blog-cta-box">
    <h3>Trigerinizin Vakti Geldi mi?</h3>
    <p>Küçük bir bakım masrafından kaçınarak motorunuzu riske atmayın. Şehrinizdeki orijinal parça garantili oto servislerini listeleyin.</p>
    <div class="blog-cta-buttons">
        <a href="{{BASE_URL}}/ustalar/" class="blog-cta-btn-primary"><i class="fa-solid fa-calendar-check"></i> Ağır Bakım Servislerini Gör</a>
        <a href="{{BASE_URL}}/iller/" class="blog-cta-btn-secondary"><i class="fa-solid fa-map-location-dot"></i> İl Rehberine Göz At</a>
    </div>
</div>
EOD;

        $articles[] = array(
            'title'     => "Triger Kayışı Kaç Km'de Değişir? Koparsa Motora Ne Olur? (2026 Değişim Rehberi)",
            'slug'      => 'triger-kayisi-kac-kmde-degisir-koparsa-ne-olur',
            'category'  => 'Periyodik Bakım',
            'volume'    => '85.000 / ay',
            'image'     => 'https://images.unsplash.com/photo-1517524008697-84bbe3c3fd98?auto=format&fit=crop&w=1200&q=80',
            'read_time' => '5 dk okuma',
            'excerpt'   => 'Triger kayışı ve zincir değişim aralıkları nelerdir? Triger kopmasının maliyeti, devirdaim pompasının önemi ve popüler araçların 2026 ağır bakım masrafları.',
            'content'   => $c4,
        );

        // 5. EPC
        $c5 = <<<'EOD'
<div class="blog-callout-box">
    <strong>Hızlı Teşhis Özeti:</strong> EPC (Electronic Power Control), motorun elektronik gaz kelebeği, pedal sensörleri, çekiş kontrol ve ateşleme sistemini yöneten güç kontrol beynidir. EPC ışığı yandığında güvenlik amacıyla araç otomatik olarak "Limp Home" (motor koruma) moduna geçer ve gaz yemez. En sık suçlular: Kirli gaz kelebeği veya bozuk fren pedalı müşürüdür.
</div>

<h2>EPC Lambası Yandığında Araç Neden Gaz Yemez?</h2>
<p>Modern araçlarda gaz pedalı ile motor arasında mekanik tel bağlantısı yoktur; her şey elektronik potansiyometreler ve sinyallerle yönetilir (Drive-by-Wire). Gaz pedalından gelen sinyal ile boğaz kelebeğinin açılma açısı birbirini tutmadığında, motor kontrol ünitesi aracın kendi kendine hızlanmasını engellemek için gazı keser ve devri sınırlar.</p>

<h2>EPC Işığını Yakan En Yaygın 5 Neden</h2>

<h3>1. Gaz Kelebeği (Boğaz Kelebeği) Kirlenmesi</h3>
<p>Zamanla biriken yağ buharı ve kurum kelebeğin tam kapanmasını veya açılmasını engeller. Kelebeğin temizlenip yeniden adaptasyon yapılması genellikle sorunu çözer.</p>

<h3>2. Fren Pedalı Müşürü Arızası (En Yaygın & En Ucuz Arıza!)</h3>
<p>Fren pedalının arkasındaki küçük anahtar bozulduğunda, ECU hem frene hem gaza aynı anda basıldığını sanır ve gazı keser. <strong>İpucu:</strong> EPC yandığında aracın arkasına geçip fren lambalarının yanıp yanmadığını kontrol edin. Hiç yanmıyorsa veya sürekli takılı kalmışsa suçlu fren müşürüdür.</p>

<h3>3. Gaz Pedalı Konum Sensörü Hatası</h3>
<p>Pedalın içindeki elektronik direnç yollarının aşınması nedeniyle ECU pedalın ne kadar basıldığını okuyamaz.</p>

<h3>4. Ateşleme Sistemi (Bobin ve Buji) Kaçakları</h3>
<p>Yüksek devirde ateşleme bobininin atlama yapması EPC ve motor arıza lambasını aynı anda yakabilir.</p>

<h3>5. Turbo Basınç Aktüatörü (Wastegate) Tutukluğu</h3>
<p>Özellikle TSI ve TFSI motorlarda elektronik turbo valfi takılı kaldığında basınç dengesizleşir ve EPC uyarı verir.</p>

<h2>EPC Lambası Yanınca Ne Yapılmalı?</h2>
<ul>
    <li>Aracı güvenli bir kenara çekip kontağı kapatın, 2 dakika bekleyip yeniden çalıştırın.</li>
    <li>Işık söndüyse geçici bir sensör senkronizasyon hatası olabilir.</li>
    <li>Işık sönmüyor ve araç titriyorsa kesinlikle aracı zorlamayın; katalizöre ve şanzımana zarar vermemek için oto çekici çağırarak servise gidin.</li>
</ul>

<h2>2026 EPC Arızası Onarım Fiyatları</h2>
<table class="blog-price-table">
    <thead>
        <tr>
            <th>İşlem / Parça</th>
            <th>Ortalama Maliyet</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Fren Müşürü Değişimi</td>
            <td>450 ₺ - 950 ₺</td>
        </tr>
        <tr>
            <td>Gaz Kelebeği Temizliği & Elektronik Adaptasyon</td>
            <td>800 ₺ - 1.500 ₺</td>
        </tr>
        <tr>
            <td>Gaz Pedalı Sensörü Değişimi</td>
            <td>2.000 ₺ - 4.500 ₺</td>
        </tr>
        <tr>
            <td>Ateşleme Bobini Değişimi (Adet)</td>
            <td>900 ₺ - 2.200 ₺</td>
        </tr>
    </tbody>
</table>

<div class="blog-cta-box">
    <h3>Aracınız Gaz Yemiyor ve Yolda mı Kaldınız?</h3>
    <p>Risk alıp trafiği tehlikeye atmayın. 81 ilde konuma en yakın acil oto çekiciyi çağırın veya bilgisayarlı arıza tespiti yapan oto elektrik ustalarına ulaşın.</p>
    <div class="blog-cta-buttons">
        <a href="{{BASE_URL}}/acil-cekici/" class="blog-cta-btn-primary"><i class="fa-solid fa-truck-pickup"></i> 7/24 Acil Çekici Çağır</a>
        <a href="{{BASE_URL}}/ariza-tespiti/" class="blog-cta-btn-secondary"><i class="fa-solid fa-robot"></i> Yapay Zeka Arıza Tespiti</a>
    </div>
</div>
EOD;

        $articles[] = array(
            'title'     => 'EPC Lambası Neden Yanar? Araç Neden Gaz Yemez? (Elektronik Güç Kontrolü)',
            'slug'      => 'epc-lambasi-neden-yanar-arac-gaz-yemiyor',
            'category'  => 'Gösterge İkaz Işıkları',
            'volume'    => '65.000 / ay',
            'image'     => 'https://images.unsplash.com/photo-1580273916550-e323be2ae537?auto=format&fit=crop&w=1200&q=80',
            'read_time' => '5 dk okuma',
            'excerpt'   => 'VAG grubu (Volkswagen, Audi, Seat, Skoda) ve diğer araçlarda EPC ışığı yandığında motor neden koruma moduna geçer? Gaz kelebeği ve fren müşürü arızası çözümleri.',
            'content'   => $c5,
        );

        return $articles;
    }

}
} // end if ! class_exists

if ( class_exists( 'Makaleler_Plugin' ) ) {
    Makaleler_Plugin::init();
}
