<?php
/**
 * OTOTAMİR-360 Google AdSense & Reklam Yönetim Modülü
 *
 * @package OtoTamir_360
 * @version 1.4.18
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OtoTamir_AdSense {

    /**
     * Başlatıcı
     */
    public static function init() {
        // Admin menü ve ayarlar
        add_action( 'admin_menu', [ __CLASS__, 'add_admin_menu' ] );
        add_action( 'admin_init', [ __CLASS__, 'handle_settings_save' ] );

        // Dinamik ads.txt yönlendirmesi
        add_action( 'init', [ __CLASS__, 'serve_ads_txt' ], 1 );

        // AdSense Auto-Ads ve temel kütüphane wp_head enjeksiyonu (Google Resmi Standardı)
        add_action( 'wp_head', [ __CLASS__, 'inject_adsense_head' ], 2 );

        // Ön yüz reklam stilleri
        add_action( 'wp_head', [ __CLASS__, 'inject_ad_styles' ], 99 );

        // AdBlock (Reklam Engelleyici) Ziyaretçi Uyarısı
        add_action( 'wp_footer', [ __CLASS__, 'inject_adblock_modal' ], 99 );
    }

    /**
     * Admin Menüsü Ekle
     */
    public static function add_admin_menu() {
        add_menu_page(
            __( 'Reklam Ayarları', 'ototamir-360' ),
            __( 'Reklam Ayarları', 'ototamir-360' ),
            'manage_options',
            'ototamir-ads',
            [ __CLASS__, 'render_admin_page' ],
            'dashicons-money-alt',
            59
        );
    }

    /**
     * Ayarları Kaydet
     */
    public static function handle_settings_save() {
        if ( ! isset( $_POST['ototamir_ads_nonce'] ) || ! wp_verify_nonce( $_POST['ototamir_ads_nonce'], 'ototamir_save_ads_action' ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $fields = [
            'ototamir_ads_enabled'               => 'int',
            'ototamir_adsense_pub_id'            => 'text',
            'ototamir_adsense_auto_ads'          => 'int',
            'ototamir_ad_header_enabled'         => 'int',
            'ototamir_ad_header_code'            => 'raw',
            'ototamir_ad_infeed_enabled'         => 'int',
            'ototamir_ad_infeed_code'            => 'raw',
            'ototamir_ad_single_sidebar_enabled' => 'int',
            'ototamir_ad_single_sidebar_code'    => 'raw',
            'ototamir_ad_single_content_enabled' => 'int',
            'ototamir_ad_single_content_code'    => 'raw',
            'ototamir_ad_blog_enabled'           => 'int',
            'ototamir_ad_blog_code'              => 'raw',
            'ototamir_adblock_notice_enabled'    => 'int',
            'ototamir_ads_txt'                   => 'raw',
        ];

        foreach ( $fields as $field => $type ) {
            if ( $type === 'int' ) {
                $val = isset( $_POST[ $field ] ) ? 1 : 0;
                update_option( $field, $val );
            } elseif ( $type === 'text' ) {
                $val = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';
                update_option( $field, $val );
            } elseif ( $type === 'raw' ) {
                // Reklam kodları ve ads.txt için HTML/JS serbest bırakılır
                $val = isset( $_POST[ $field ] ) ? wp_unslash( $_POST[ $field ] ) : '';
                update_option( $field, $val );
            }
        }

        // Başarılı yönlendirme
        wp_safe_redirect( add_query_arg( [ 'page' => 'ototamir-ads', 'settings-updated' => 'true' ], admin_url( 'admin.php' ) ) );
        exit;
    }

    /**
     * Dinamik ads.txt Servis Et
     */
    public static function serve_ads_txt() {
        $request_uri = parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
        if ( ! empty( $request_uri ) && untrailingslashit( strtolower( $request_uri ) ) === '/ads.txt' ) {
            // Tampon temizle
            if ( ob_get_length() ) {
                ob_clean();
            }

            header( 'Content-Type: text/plain; charset=utf-8' );
            header( 'X-Robots-Tag: noindex' );
            header( 'Cache-Control: public, max-age=3600' );

            $custom_ads_txt = get_option( 'ototamir_ads_txt', '' );

            if ( ! empty( trim( $custom_ads_txt ) ) ) {
                echo trim( $custom_ads_txt ) . "\n";
            } else {
                // Otomatik AdSense kaydı üret
                $pub_id = trim( get_option( 'ototamir_adsense_pub_id', 'ca-pub-7207931778635058' ) );
                if ( empty( $pub_id ) ) {
                    $pub_id = 'ca-pub-7207931778635058';
                }
                $clean_pub = preg_replace( '/^ca-/', '', $pub_id );
                echo "# OtoTamirciBul Otomatik Google AdSense Dogrulamasi\n";
                echo "google.com, {$clean_pub}, DIRECT, f08c47fec0942fa0\n";
            }
            exit;
        }
    }

    /**
     * Head AdSense Script Enjeksiyonu
     */
    public static function inject_adsense_head() {
        $enabled = (int) get_option( 'ototamir_ads_enabled', 1 );
        if ( ! $enabled ) {
            return;
        }

        $pub_id = trim( get_option( 'ototamir_adsense_pub_id', 'ca-pub-7207931778635058' ) );
        if ( empty( $pub_id ) ) {
            $pub_id = 'ca-pub-7207931778635058';
        }

        if ( ! preg_match( '/^ca-pub-/', $pub_id ) && preg_match( '/^pub-/', $pub_id ) ) {
            $pub_id = 'ca-' . $pub_id;
        }

        $auto_ads = (int) get_option( 'ototamir_adsense_auto_ads', 1 );
        ?>
<!-- Google AdSense (OTOTAMİR-360) -->
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?php echo esc_attr( $pub_id ); ?>" crossorigin="anonymous"<?php echo ( ! $auto_ads ) ? ' data-ad-frequency-hint="none"' : ''; ?>></script>
<!-- End Google AdSense -->
        <?php
    }

    /**
     * Reklam Alanları CSS Stilleri (CLS Koruma ve Tasarım)
     */
    public static function inject_ad_styles() {
        $enabled = (int) get_option( 'ototamir_ads_enabled', 1 );
        if ( ! $enabled ) {
            return;
        }
        ?>
<style id="ototamir-ads-styles">
/* ============================================================
   OTOTAMİR-360 GOOGLE ADSENSE & REKLAM ALANLARI (CLS KORUMALI)
   ============================================================ */
.ototamir-ad-wrapper {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin: 28px auto;
    width: 100%;
    max-width: 100% !important;
    clear: both;
    text-align: center;
    box-sizing: border-box !important;
    overflow: hidden !important;
}
.ototamir-ad-label {
    display: inline-block;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #475569;
    margin-bottom: 6px;
    user-select: none;
}
.ototamir-ad-inner {
    width: 100%;
    max-width: 100% !important;
    display: block;
    text-align: center;
    overflow: hidden !important;
    background: #f8fafc;
    border: 1px dashed #e2e8f0;
    border-radius: 12px;
    transition: border-color 0.2s ease;
    box-sizing: border-box !important;
}
.ototamir-ad-inner ins.adsbygoogle,
.ototamir-ad-inner ins.adsbygoogle iframe {
    display: block !important;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
    overflow: hidden !important;
    margin: 0 auto;
}
.ototamir-ad-inner:hover {
    border-color: #cbd5e1;
}

/* Header Banner Slot */
.ototamir-ad-header {
    max-width: 1200px;
    margin: 15px auto 20px auto;
    padding: 0 16px;
    width: 100% !important;
    box-sizing: border-box !important;
    overflow: hidden !important;
}
.ototamir-ad-header .ototamir-ad-inner {
    min-height: 90px;
}

/* In-Feed Native Slot (Liste Arası) */
.ototamir-ad-infeed {
    margin: 10px 0;
    width: 100%;
}
.ototamir-ad-infeed .ototamir-ad-inner {
    min-height: 250px;
    background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 12px;
}

/* Sidebar Slot */
.ototamir-ad-single_sidebar {
    margin: 20px 0;
}
.ototamir-ad-single_sidebar .ototamir-ad-inner {
    min-height: 250px;
    border-radius: 14px;
    padding: 8px;
}

/* Single Content Slot */
.ototamir-ad-single_content {
    margin: 32px 0 24px 0;
}
.ototamir-ad-single_content .ototamir-ad-inner {
    min-height: 120px;
    border-radius: 14px;
    padding: 12px;
}

/* Blog Slot */
.ototamir-ad-blog {
    margin: 30px 0;
}
.ototamir-ad-blog .ototamir-ad-inner {
    min-height: 140px;
    border-radius: 14px;
    padding: 12px;
}

@media (max-width: 768px) {
    .ototamir-ad-header .ototamir-ad-inner {
        min-height: 60px;
    }
    .ototamir-ad-inner {
        border-radius: 10px;
    }
}

/* AdSense boş döndüğünde (unfilled) veya gizlendiğinde kutuyu ve etiketi tamamen gizle */
.ototamir-ad-wrapper:has(ins[data-ad-status="unfilled"]),
.ototamir-ad-wrapper:has(ins[style*="display: none"]),
.ototamir-ad-wrapper:has(ins[style*="display:none"]) {
    display: none !important;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function ototamirCollapseEmptyAds() {
        var wraps = document.querySelectorAll('.ototamir-ad-wrapper');
        wraps.forEach(function(wrap) {
            var ins = wrap.querySelector('ins.adsbygoogle');
            if (ins) {
                var status = ins.getAttribute('data-ad-status');
                var style = window.getComputedStyle(ins);
                if (status === 'unfilled' || style.display === 'none' || (ins.childNodes.length === 0 && ins.clientHeight === 0)) {
                    wrap.style.display = 'none';
                }
            }
        });
    }
    setTimeout(ototamirCollapseEmptyAds, 2000);
    setTimeout(ototamirCollapseEmptyAds, 4000);
});
</script>
        <?php
    }

    /**
     * Reklam Alanı Çıktısı Üret (Frontend Helper)
     *
     * @param string $slot_name Slot adı ('header', 'infeed', 'single_sidebar', 'single_content', 'blog')
     * @return string HTML çıktısı
     */
    public static function render_ad( $slot_name ) {
        $ads_enabled = (int) get_option( 'ototamir_ads_enabled', 1 );
        if ( ! $ads_enabled ) {
            return '';
        }

        $slot_enabled = (int) get_option( "ototamir_ad_{$slot_name}_enabled", 1 );
        if ( ! $slot_enabled ) {
            return '';
        }

        $ad_code = trim( get_option( "ototamir_ad_{$slot_name}_code", '' ) );

        // Eğer bu alan için özel kod girilmemişse, kullanıcının resmi AdSense responsive birimini (7668474087) otomatik kullan
        if ( empty( $ad_code ) ) {
            $pub_id = trim( get_option( 'ototamir_adsense_pub_id', 'ca-pub-7207931778635058' ) );
            $ad_code = '<!-- OtoTamir Banner -->' . "\n" .
                '<ins class="adsbygoogle"' . "\n" .
                '     style="display:block; width:100%; max-width:100%; min-width:250px; overflow:hidden;"' . "\n" .
                '     data-ad-client="' . esc_attr( $pub_id ) . '"' . "\n" .
                '     data-ad-slot="7668474087"' . "\n" .
                '     data-ad-format="auto"' . "\n" .
                '     data-full-width-responsive="false"></ins>' . "\n" .
                '<script>' . "\n" .
                '     try { (adsbygoogle = window.adsbygoogle || []).push({}); } catch(e) {}' . "\n" .
                '</script>';
        }

        // Eğer adsbygoogle içeriyorsa güvenli try/catch sarmalayıcısı ekle (No slot size for availableWidth=0 hatasını engeller)
        if ( strpos( $ad_code, 'class="adsbygoogle"' ) !== false ) {
            if ( strpos( $ad_code, '.push' ) === false ) {
                $ad_code .= '<script>try { (adsbygoogle = window.adsbygoogle || []).push({}); } catch(e) {}</script>';
            } elseif ( strpos( $ad_code, 'catch' ) === false ) {
                $ad_code = str_replace(
                    '(adsbygoogle = window.adsbygoogle || []).push({});',
                    'try { (adsbygoogle = window.adsbygoogle || []).push({}); } catch(e) {}',
                    $ad_code
                );
            }
        }

        $wrapper_class = 'ototamir-ad-wrapper ototamir-ad-' . esc_attr( $slot_name );

        ob_start();
        ?>
        <div class="<?php echo $wrapper_class; ?>">
            <span class="ototamir-ad-label"><?php esc_html_e( 'Sponsorlu Bağlantı', 'ototamir-360' ); ?></span>
            <div class="ototamir-ad-inner">
                <?php echo $ad_code; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Admin Ayarlar Sayfası Render
     */
    public static function render_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $pub_id = get_option( 'ototamir_adsense_pub_id', 'ca-pub-7207931778635058' );
        if ( empty( $pub_id ) ) {
            $pub_id = 'ca-pub-7207931778635058';
        }
        $ads_enabled = (int) get_option( 'ototamir_ads_enabled', 1 );
        $auto_ads = (int) get_option( 'ototamir_adsense_auto_ads', 1 );

        $header_enabled = (int) get_option( 'ototamir_ad_header_enabled', 0 );
        $header_code    = get_option( 'ototamir_ad_header_code', '' );

        $infeed_enabled = (int) get_option( 'ototamir_ad_infeed_enabled', 1 );
        $infeed_code    = get_option( 'ototamir_ad_infeed_code', '' );

        $sidebar_enabled = (int) get_option( 'ototamir_ad_single_sidebar_enabled', 1 );
        $sidebar_code    = get_option( 'ototamir_ad_single_sidebar_code', '' );

        $content_enabled = (int) get_option( 'ototamir_ad_single_content_enabled', 1 );
        $content_code    = get_option( 'ototamir_ad_single_content_code', '' );

        $blog_enabled    = (int) get_option( 'ototamir_ad_blog_enabled', 1 );
        $blog_code       = get_option( 'ototamir_ad_blog_code', '' );

        $adblock_enabled = (int) get_option( 'ototamir_adblock_notice_enabled', 1 );

        $ads_txt = get_option( 'ototamir_ads_txt', '' );
        $ads_txt_url = home_url( '/ads.txt' );
        $is_demo = ( strpos( $_SERVER['HTTP_HOST'] ?? '', '.local' ) !== false || strpos( $_SERVER['HTTP_HOST'] ?? '', 'localhost' ) !== false || strpos( $_SERVER['HTTP_HOST'] ?? '', '127.0.0.1' ) !== false );
        ?>
        <div class="wrap ototamir-ads-admin-wrap">
            <style>
                .ototamir-ads-admin-wrap { max-width: 1050px; margin-top: 20px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; }
                .ototamir-ads-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 28px 32px; margin-bottom: 24px; box-shadow: 0 4px 16px rgba(0,0,0,0.03); }
                .ototamir-ads-header { display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 18px; margin-bottom: 24px; }
                .ototamir-ads-title { font-size: 22px; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 10px; }
                .ototamir-ads-title span { background: #ea580c; color: #fff; font-size: 12px; padding: 3px 9px; border-radius: 6px; font-weight: 600; }
                .ototamir-badge-active { background: #ecfdf5; color: #059669; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
                .ototamir-badge-warning { background: #fffbeb; color: #d97706; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; }
                .ototamir-toggle-switch { position: relative; display: inline-block; width: 46px; height: 24px; vertical-align: middle; }
                .ototamir-toggle-switch input { opacity: 0; width: 0; height: 0; }
                .ototamir-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .3s; border-radius: 24px; }
                .ototamir-slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%; }
                input:checked + .ototamir-slider { background-color: #ea580c; }
                input:checked + .ototamir-slider:before { transform: translateX(22px); }
                .ototamir-form-row { margin-bottom: 22px; }
                .ototamir-form-row label { display: block; font-weight: 700; color: #1e293b; margin-bottom: 8px; font-size: 14.5px; }
                .ototamir-form-row input[type="text"], .ototamir-form-row textarea { width: 100%; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px; box-sizing: border-box; }
                .ototamir-form-row textarea { font-family: monospace; font-size: 13px; background: #fafafa; }
                .ototamir-form-row input[type="text"]:focus, .ototamir-form-row textarea:focus { border-color: #ea580c; outline: none; box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.15); }
                .ototamir-field-help { font-size: 12.5px; color: #64748b; margin-top: 6px; line-height: 1.5; }
                .ototamir-slot-grid { display: grid; grid-template-columns: 1fr; gap: 20px; }
                .ototamir-slot-item { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 22px; transition: all 0.2s ease; }
                .ototamir-slot-item:hover { border-color: #cbd5e1; }
                .ototamir-slot-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
                .ototamir-slot-title { font-weight: 700; font-size: 15px; color: #0f172a; display: flex; align-items: center; gap: 8px; }
                .ototamir-compliance-box { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 16px 20px; border-radius: 0 10px 10px 0; margin-bottom: 24px; font-size: 13.5px; color: #1e40af; line-height: 1.6; }
                .ototamir-compliance-box ul { margin: 8px 0 0 18px; padding: 0; }
                .ototamir-btn-save { background: #ea580c; border: none; color: #fff; padding: 12px 28px; font-size: 15px; font-weight: 700; border-radius: 8px; cursor: pointer; transition: background 0.2s ease; display: inline-flex; align-items: center; gap: 8px; }
                .ototamir-btn-save:hover { background: #c2410c; }
                .ototamir-btn-outline { background: transparent; border: 1px solid #cbd5e1; color: #475569; padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
                .ototamir-btn-outline:hover { border-color: #94a3b8; color: #0f172a; }
            </style>

            <div class="ototamir-ads-card">
                <div class="ototamir-ads-header">
                    <div>
                        <h1 class="ototamir-ads-title">
                            <span class="dashicons dashicons-google" style="margin-right:2px;"></span> Google AdSense & Reklam Yönetimi <span>v1.4.13</span>
                        </h1>
                        <p style="color:#64748b; font-size:13.5px; margin:6px 0 0 0;">
                            Sitenizde yayınlanacak reklamları, AdSense yayıncı kimliğinizi ve <code>ads.txt</code> dosyanızı bu panelden zahmetsizce yönetin.
                        </p>
                    </div>
                    <div>
                        <?php if ( $ads_enabled && ! empty( $pub_id ) ) : ?>
                            <span class="ototamir-badge-active"><span class="dashicons dashicons-yes-alt"></span> AdSense Aktif</span>
                        <?php else : ?>
                            <span class="ototamir-badge-warning"><span class="dashicons dashicons-info"></span> Yapılandırma Bekleniyor</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ORTAM & CANLI DOMAIN DURUM KARTI -->
                <div style="background: <?php echo $is_demo ? '#fffbeb' : '#ecfdf5'; ?>; border: 1px solid <?php echo $is_demo ? '#fde68a' : '#a7f3d0'; ?>; border-radius: 12px; padding: 18px 22px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <span class="dashicons <?php echo $is_demo ? 'dashicons-laptop' : 'dashicons-admin-site-alt3'; ?>" style="font-size: 28px; width: 28px; height: 28px; color: <?php echo $is_demo ? '#d97706' : '#059669'; ?>;"></span>
                        <div>
                            <strong style="color: <?php echo $is_demo ? '#92400e' : '#065f46'; ?>; font-size: 14.5px; display: block;">
                                <?php echo $is_demo ? 'Şu Anki Ortam: Yerel Demo Modu (' . esc_html( $_SERVER['HTTP_HOST'] ?? 'local' ) . ')' : 'Şu Anki Ortam: Canlı Yayın (ototamircibul.com.tr)'; ?>
                            </strong>
                            <span style="color: <?php echo $is_demo ? '#b45309' : '#047857'; ?>; font-size: 13px;">
                                Resmi Canlı Yayın Alan Adı: <strong style="text-decoration:underline;">https://ototamircibul.com.tr</strong> — Tüm reklam kodları, ads.txt ve politika sözleşmeleri canlı alan adına göre yapılandırılmıştır.
                            </span>
                        </div>
                    </div>
                    <div style="font-size: 12.5px; background: white; border: 1px solid <?php echo $is_demo ? '#fcd34d' : '#6ee7b7'; ?>; padding: 6px 14px; border-radius: 20px; font-weight: 700; color: <?php echo $is_demo ? '#b45309' : '#065f46'; ?>;">
                        <?php echo $is_demo ? '🛠️ Canlıya Taşınmaya Hazır' : '🟢 Canlıda Yayında'; ?>
                    </div>
                </div>

                <?php if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] === 'true' ) : ?>
                    <div class="notice notice-success is-dismissible" style="border-radius: 8px; margin-bottom: 20px;">
                        <p><strong>Başarılı!</strong> Reklam ayarları ve ads.txt güncellendi.</p>
                    </div>
                <?php endif; ?>

                <form method="post" action="">
                    <?php wp_nonce_field( 'ototamir_save_ads_action', 'ototamir_ads_nonce' ); ?>

                    <!-- 1. TEMEL ADSENSE AYARLARI -->
                    <div style="border-bottom: 1px solid #f1f5f9; padding-bottom: 24px; margin-bottom: 24px;">
                        <h2 style="font-size: 17px; font-weight: 700; color: #0f172a; margin: 0 0 16px 0;">1. Temel Google AdSense Entegrasyonu</h2>

                        <div style="display: flex; gap: 30px; align-items: center; margin-bottom: 20px;">
                            <label style="display:flex; align-items:center; gap:12px; font-weight:600; color:#1e293b; cursor:pointer;">
                                <span class="ototamir-toggle-switch">
                                    <input type="checkbox" name="ototamir_ads_enabled" value="1" <?php checked( $ads_enabled, 1 ); ?>>
                                    <span class="ototamir-slider"></span>
                                </span>
                                Reklam Gösterimini Genel Olarak Aç / Kapat
                            </label>

                            <label style="display:flex; align-items:center; gap:12px; font-weight:600; color:#1e293b; cursor:pointer;">
                                <span class="ototamir-toggle-switch">
                                    <input type="checkbox" name="ototamir_adsense_auto_ads" value="1" <?php checked( $auto_ads, 1 ); ?>>
                                    <span class="ototamir-slider"></span>
                                </span>
                                Google Otomatik Reklamlar (Auto-Ads)
                            </label>
                        </div>

                        <div class="ototamir-form-row">
                            <label for="ototamir_adsense_pub_id">Google AdSense Yayıncı Kimliği (Publisher ID):</label>
                            <input type="text" id="ototamir_adsense_pub_id" name="ototamir_adsense_pub_id" value="<?php echo esc_attr( $pub_id ); ?>" placeholder="ca-pub-7207931778635058" style="max-width: 420px;">
                            <div class="ototamir-field-help">
                                AdSense panelinizden aldığınız <code>ca-pub-XXXXXXXXXXXXXXXX</code> kimliğini buraya yapıştırın. Sistem AdSense kütüphanesini otomatik olarak sitenizin <code>&lt;head&gt;</code> alanına yerleştirir.
                            </div>
                        </div>
                    </div>

                    <!-- 2. REKLAM YERLEŞİMLERİ (SLOTS) -->
                    <div style="border-bottom: 1px solid #f1f5f9; padding-bottom: 24px; margin-bottom: 24px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                            <h2 style="font-size: 17px; font-weight: 700; color: #0f172a; margin: 0;">2. Özel Reklam Alanları (Ad Slots)</h2>
                            <span style="font-size: 12.5px; color: #64748b;">* Tüm alanlar CLS (Cumulative Layout Shift) korumalıdır ve sayfa hızını düşürmez.</span>
                        </div>

                        <div class="ototamir-slot-grid">

                            <!-- Slot 1: Header Altı -->
                            <div class="ototamir-slot-item">
                                <div class="ototamir-slot-header">
                                    <span class="ototamir-slot-title">
                                        <span class="dashicons dashicons-align-center" style="color:#ea580c;"></span> Header Altı Yatay Reklam (Leaderboard - 728x90 / Esnek)
                                    </span>
                                    <label class="ototamir-toggle-switch">
                                        <input type="checkbox" name="ototamir_ad_header_enabled" value="1" <?php checked( $header_enabled, 1 ); ?>>
                                        <span class="ototamir-slider"></span>
                                    </label>
                                </div>
                                <textarea name="ototamir_ad_header_code" rows="3" placeholder="AdSense duyarlı veya 728x90 reklam birimi kodunu buraya yapıştırın..."><?php echo esc_textarea( $header_code ); ?></textarea>
                            </div>

                            <!-- Slot 2: Usta Listesi In-Feed (En Çok Tıklanan) -->
                            <div class="ototamir-slot-item">
                                <div class="ototamir-slot-header">
                                    <span class="ototamir-slot-title">
                                        <span class="dashicons dashicons-grid-view" style="color:#ea580c;"></span> Usta Listeleri Arası Doğal Reklam (In-Feed Native - 4. Karttan Sonra)
                                    </span>
                                    <label class="ototamir-toggle-switch">
                                        <input type="checkbox" name="ototamir_ad_infeed_enabled" value="1" <?php checked( $infeed_enabled, 1 ); ?>>
                                        <span class="ototamir-slider"></span>
                                    </label>
                                </div>
                                <textarea name="ototamir_ad_infeed_code" rows="3" placeholder="AdSense 'Feed İçi' (In-Feed) veya Esnek Kare/Yatay reklam kodunu buraya yapıştırın..."><?php echo esc_textarea( $infeed_code ); ?></textarea>
                                <div class="ototamir-field-help">Arama ve kategori sayfalarında (archive.php) usta kartlarının arasında doğal görünümle listelenir.</div>
                            </div>

                            <!-- Slot 3: Usta Detay Sidebar -->
                            <div class="ototamir-slot-item">
                                <div class="ototamir-slot-header">
                                    <span class="ototamir-slot-title">
                                        <span class="dashicons dashicons-sidebar" style="color:#ea580c;"></span> Usta Detayı Yan Panel (Sidebar - 300x250 / 300x600)
                                    </span>
                                    <label class="ototamir-toggle-switch">
                                        <input type="checkbox" name="ototamir_ad_single_sidebar_enabled" value="1" <?php checked( $sidebar_enabled, 1 ); ?>>
                                        <span class="ototamir-slider"></span>
                                    </label>
                                </div>
                                <textarea name="ototamir_ad_single_sidebar_code" rows="3" placeholder="AdSense 300x250 veya dikey reklam birimi kodunu buraya yapıştırın..."><?php echo esc_textarea( $sidebar_code ); ?></textarea>
                            </div>

                            <!-- Slot 4: Usta Detay İçerik Altı -->
                            <div class="ototamir-slot-item">
                                <div class="ototamir-slot-header">
                                    <span class="ototamir-slot-title">
                                        <span class="dashicons dashicons-editor-aligncenter" style="color:#ea580c;"></span> Usta Detayı İçerik Altı / Yorumlar Öncesi
                                    </span>
                                    <label class="ototamir-toggle-switch">
                                        <input type="checkbox" name="ototamir_ad_single_content_enabled" value="1" <?php checked( $content_enabled, 1 ); ?>>
                                        <span class="ototamir-slider"></span>
                                    </label>
                                </div>
                                <textarea name="ototamir_ad_single_content_code" rows="3" placeholder="AdSense içerik altı yatay/esnek reklam kodunu buraya yapıştırın..."><?php echo esc_textarea( $content_code ); ?></textarea>
                            </div>

                            <!-- Slot 5: Blog Makale İçi / Sonu -->
                            <div class="ototamir-slot-item">
                                <div class="ototamir-slot-header">
                                    <span class="ototamir-slot-title">
                                        <span class="dashicons dashicons-welcome-write-blog" style="color:#ea580c;"></span> Blog Makale İçi & Sonu (Makale İçi Reklam)
                                    </span>
                                    <label class="ototamir-toggle-switch">
                                        <input type="checkbox" name="ototamir_ad_blog_enabled" value="1" <?php checked( $blog_enabled, 1 ); ?>>
                                        <span class="ototamir-slider"></span>
                                    </label>
                                </div>
                                <textarea name="ototamir_ad_blog_code" rows="3" placeholder="AdSense 'Makale İçi' (In-Article) veya Esnek reklam kodunu buraya yapıştırın..."><?php echo esc_textarea( $blog_code ); ?></textarea>
                            </div>

                        </div>
                    </div>

                    <!-- 3. ADS.TXT YÖNETİMİ -->
                    <div style="border-bottom: 1px solid #f1f5f9; padding-bottom: 24px; margin-bottom: 24px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <div>
                                <h2 style="font-size: 17px; font-weight: 700; color: #0f172a; margin: 0;">3. ads.txt Yönetimi & Doğrulayıcı</h2>
                                <p style="color:#64748b; font-size:13px; margin:4px 0 0 0;">
                                    Google AdSense gelir kaybı uyarısını ("Earnings at risk") engellemek için dinamik olarak servis edilir.
                                </p>
                            </div>
                            <div>
                                <a href="<?php echo esc_url( $ads_txt_url ); ?>" target="_blank" class="ototamir-btn-outline">
                                    <span class="dashicons dashicons-external"></span> Canlı ads.txt Görüntüle
                                </a>
                            </div>
                        </div>

                        <div class="ototamir-form-row">
                            <textarea id="ototamir_ads_txt" name="ototamir_ads_txt" rows="4" placeholder="google.com, pub-XXXXXXXXXXXXXXXX, DIRECT, f08c47fec0942fa0"><?php echo esc_textarea( $ads_txt ); ?></textarea>
                            <div style="margin-top: 8px; display:flex; justify-content:space-between; align-items:center;">
                                <button type="button" class="ototamir-btn-outline" onclick="ototamirAutoFillAdsTxt()">
                                    <span class="dashicons dashicons-update"></span> Yayıncı Kimliğinden Otomatik Kural Oluştur
                                </button>
                                <span style="font-size:12px; color:#94a3b8;">* Boş bırakılırsa yukarıdaki Yayıncı Kimliği otomatik kullanılır.</span>
                            </div>
                        </div>
                    </div>

                    <!-- 4. ADBLOCK (REKLAM ENGELLEYİCİ) UYARISI -->
                    <div style="border-bottom: 1px solid #f1f5f9; padding-bottom: 24px; margin-bottom: 24px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h2 style="font-size: 17px; font-weight: 700; color: #0f172a; margin: 0; display:flex; align-items:center; gap:8px;">
                                    <span class="dashicons dashicons-shield-alt" style="color:#ea580c;"></span> 4. AdBlock (Reklam Engelleyici) Ziyaretçi Uyarısı
                                </h2>
                                <p style="color:#64748b; font-size:13px; margin:4px 0 0 0;">
                                    Sitenizi reklam engelleyici (AdBlock) ile gezen ziyaretçilere 6 saniye sonra nazik bir destek modalı açarak sitenizi beyaz listeye eklemelerini rica eder. (Kapatılabilir, 24 saat hatırlanır, Google AdSense politikalarına %100 uygundur).
                                </p>
                            </div>
                            <div>
                                <label class="ototamir-toggle-switch">
                                    <input type="checkbox" name="ototamir_adblock_notice_enabled" value="1" <?php checked( $adblock_enabled, 1 ); ?>>
                                    <span class="ototamir-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 5. POLİTİKA & GOOGLE ADSENSE ONAY REHBERİ -->
                    <div class="ototamir-compliance-box">
                        <div style="font-weight: 700; font-size: 14.5px; margin-bottom: 6px; display:flex; align-items:center; gap:6px;">
                            <span class="dashicons dashicons-shield"></span> AdSense Politika Uyumluluk Koruması (Aktif)
                        </div>
                        <p style="margin: 0;">Bu tema Google AdSense onay politikalarıyla %100 uyumludur:</p>
                        <ul>
                            <li><strong>Gizlilik & Çerez Politikası:</strong> Google DoubleClick DART ve üçüncü taraf reklam çerez maddeleri otomatik entegredir.</li>
                            <li><strong>CLS (Sayfa Kayması) Koruması:</strong> Reklam alanları sabit minimum yüksekliklerle sarılmıştır; reklam yüklenirken sayfa içeriği kaymaz.</li>
                            <li><strong>Etiketleme:</strong> Google kuralları gereği reklamların üzerinde "Sponsorlu Bağlantı" ibaresi yer alır.</li>
                        </ul>
                    </div>

                    <!-- KAYDET BUTONU -->
                    <div>
                        <button type="submit" class="ototamir-btn-save">
                            <span class="dashicons dashicons-saved"></span> Değişiklikleri Kaydet
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <script>
        function ototamirAutoFillAdsTxt() {
            var pubInput = document.getElementById('ototamir_adsense_pub_id');
            var adsTxtArea = document.getElementById('ototamir_ads_txt');
            if (!pubInput || !adsTxtArea) return;

            var val = pubInput.value.trim();
            if (!val) {
                alert('Lütfen önce yukarıdaki Google AdSense Yayıncı Kimliği alanını doldurun.');
                pubInput.focus();
                return;
            }

            // ca- prefixini temizle
            var clean = val.replace(/^ca-/, '');
            var rule = 'google.com, ' + clean + ', DIRECT, f08c47fec0942fa0';

            if (adsTxtArea.value.indexOf(clean) === -1) {
                if (adsTxtArea.value.trim().length > 0) {
                    adsTxtArea.value = adsTxtArea.value.trim() + '\n' + rule;
                } else {
                    adsTxtArea.value = rule;
                }
                alert('AdSense kuralı eklendi: ' + rule);
            } else {
                alert('Bu yayıncı kimliği zaten ads.txt listesinde mevcut.');
            }
        }
        </script>
        <?php
    }

    /**
     * AdBlock (Reklam Engelleyici) Ziyaretçi Uyarısı
     *
     * Google AdSense ve Web Yönergelerine %100 uyumludur.
     * Sayfayı kilitlemez, zorlamaz, nazikçe destek rica eder.
     * Kapatıldığında 24 saat boyunca tekrar gösterilmez.
     */
    public static function inject_adblock_modal() {
        if ( is_admin() ) {
            return;
        }

        // Singleton Kilidi: HTML içinde modalın ve ID'lerin iki kez basılmasını kesin olarak engeller
        static $rendered = false;
        if ( $rendered ) {
            return;
        }
        $rendered = true;

        $enabled = (int) get_option( 'ototamir_adblock_notice_enabled', 1 );
        if ( ! $enabled ) {
            return;
        }
        ?>
        <?php if (!empty($_SESSION['page_views']) && $_SESSION['page_views'] >= 2): ?>
<!-- OTOTAMİR-360 ADBLOCK TESPİT YEM ELEMENTİ (Normalde görünür olmalı ki adblock filtreleri gizleyince anlaşılsın) -->
<div id="ototamir-ad-bait" class="adsbox pub_300x250 pub_300x250m pub_728x90 text-ad textAd text_ad text_ads banner-ad banner_ads ad-placement" style="position: absolute !important; left: -9999px !important; top: -9999px !important; width: 10px !important; height: 10px !important; pointer-events: none !important;" aria-hidden="true"></div>

<!-- OTOTAMİR-360 BİLGİLENDİRME & ADBLOCK MODALI -->
<div id="ototamir-adblock-modal" class="ototamir-adblock-overlay" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="ototamir-adblock-title">
    <div class="ototamir-adblock-backdrop" id="ototamir-adblock-backdrop"></div>
    <div class="ototamir-adblock-card">
        <button type="button" class="ototamir-adblock-close" id="ototamir-adblock-close-btn" aria-label="Kapat">&times;</button>
        
        <!-- ADBLOCK MODU İÇERİĞİ -->
        <div id="ototamir-modal-adblock-view" style="display: none;">
            <div class="ototamir-adblock-icon-wrap" style="background: #fff7ed; color: #ea580c; box-shadow: 0 0 0 8px #ffedd5;">
                <svg class="ototamir-adblock-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <path d="M12 8v4"/>
                    <path d="M12 16h.01"/>
                </svg>
            </div>
            <div class="ototamir-adblock-badge" style="background: #fef3c7; color: #b45309;">📢 DESTEK ÇAĞRISI</div>
            <h3 id="ototamir-adblock-title" class="ototamir-adblock-title">Sitemizi Reklamsız mı Ziyaret Ediyorsunuz?</h3>
            <p class="ototamir-adblock-desc"><strong>OtoTamirciBul</strong>; sürücüler ile güvenilir tamircileri buluşturan, Türkiye genelinde <strong>tamamen ücretsiz</strong> sunulan bir rehber ve yapay zeka destekli arıza tespit platformudur.</p>
            <p class="ototamir-adblock-desc-sub">Sunucu, yapay zeka (AI) arıza tespit ve yazılım altyapı giderlerimizi yalnızca sayfamızdaki saygılı reklamlardan karşılıyoruz. Reklam engelleyicinizi bu sitede kapatarak bize büyük destek olabilirsiniz.</p>
            <div class="ototamir-adblock-steps">
                <div class="ototamir-adblock-step">
                    <span class="ototamir-adblock-step-num">1</span>
                    <span>Tarayıcınızın sağ üstündeki <strong>AdBlock / Kalkan</strong> simgesine tıklayın</span>
                </div>
                <div class="ototamir-adblock-step">
                    <span class="ototamir-adblock-step-num">2</span>
                    <span><strong>"Bu sitede duraklat"</strong> veya <strong>"İzin ver"</strong> basın</span>
                </div>
                <div class="ototamir-adblock-step">
                    <span class="ototamir-adblock-step-num">3</span>
                    <span>Aşağıdaki butona basarak sayfayı yenileyin</span>
                </div>
            </div>
            <div class="ototamir-adblock-actions">
                <button type="button" id="ototamir-adblock-whitelist-btn" class="ototamir-adblock-btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 6px;"><polyline points="20 6 9 17 4 12"/></svg>
                    İzin Verdim, Sayfayı Yenile
                </button>
                <button type="button" id="ototamir-adblock-dismiss-btn" class="ototamir-adblock-btn-dismiss">Şimdilik Gezinmeye Devam Et</button>
            </div>
        </div>
        
        <!-- NORMAL KULLANICI İÇİN 1 DEFALIK BİLGİLENDİRME / TEŞEKKÜR MODU -->
        <div id="ototamir-modal-support-view" style="display: none;">
            <div class="ototamir-adblock-icon-wrap" style="background: #f0fdf4; color: #16a34a; box-shadow: 0 0 0 8px #dcfce7;">
                <svg class="ototamir-adblock-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="m9 12 2 2 4-4"/>
                </svg>
            </div>
            <div class="ototamir-adblock-badge" style="background: #dbeafe; color: #1e40af;">🤖 YAPAY ZEKA & ÜCRETSİZ HİZMET</div>
            <h3 class="ototamir-adblock-title">OtoTamirciBul'a Hoş Geldiniz!</h3>
            <p class="ototamir-adblock-desc">Platformumuzdaki <strong>Yapay Zeka ile Arıza Tespiti</strong>, 81 il usta rehberi ve nöbetçi tamirci ağımızın tümü sürücülerimize <strong>tamamen ücretsiz</strong> sunulmaktadır.</p>
            <p class="ototamir-adblock-desc-sub" style="margin-bottom: 22px; font-size: 13.5px; line-height: 1.6;">Gelişmiş yapay zeka modelleme ve sunucu altyapı giderlerimizi sayfamızdaki reklamlardan karşılıyoruz. Siz de <strong>reklamlarımıza gösterdiğiniz ilgiyle bize destek olabilirsiniz</strong>. Anlayışınız için teşekkür ederiz!</p>
            <div class="ototamir-adblock-actions">
                <button type="button" id="ototamir-support-ok-btn" class="ototamir-adblock-btn-primary" style="background: #0f172a; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.25);">Harika, Gezinmeye Devam Et</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

        <style>
        .ototamir-adblock-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            box-sizing: border-box;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .ototamir-adblock-overlay.active {
            opacity: 1;
        }
        .ototamir-adblock-backdrop {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.72);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        .ototamir-adblock-card {
            position: relative;
            z-index: 2;
            background: #ffffff;
            border-radius: 20px;
            max-width: 500px;
            width: 100%;
            padding: 34px 28px 26px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.05);
            text-align: center;
            transform: scale(0.95) translateY(10px);
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-sizing: border-box;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        .ototamir-adblock-overlay.active .ototamir-adblock-card {
            transform: scale(1) translateY(0);
        }
        .ototamir-adblock-close {
            position: absolute;
            top: 14px;
            right: 16px;
            background: #f1f5f9;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            font-size: 20px;
            line-height: 1;
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        .ototamir-adblock-close:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .ototamir-adblock-icon-wrap {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
        }
        .ototamir-adblock-icon {
            width: 30px;
            height: 30px;
        }
        .ototamir-adblock-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.05em;
            padding: 3px 10px;
            border-radius: 9999px;
            margin-bottom: 10px;
        }
        .ototamir-adblock-title {
            font-size: 21px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 10px;
            line-height: 1.3;
        }
        .ototamir-adblock-desc {
            font-size: 14px;
            color: #334155;
            margin: 0 0 8px;
            line-height: 1.6;
        }
        .ototamir-adblock-desc-sub {
            font-size: 12.5px;
            color: #64748b;
            margin: 0 0 18px;
            line-height: 1.5;
        }
        .ototamir-adblock-steps {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 14px;
            text-align: left;
            margin-bottom: 20px;
        }
        .ototamir-adblock-step {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12.5px;
            color: #1e293b;
            padding: 5px 0;
        }
        .ototamir-adblock-step + .ototamir-adblock-step {
            border-top: 1px dashed #e2e8f0;
        }
        .ototamir-adblock-step-num {
            width: 20px;
            height: 20px;
            background: #ea580c;
            color: #ffffff;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10.5px;
            font-weight: 700;
            flex-shrink: 0;
        }
        .ototamir-adblock-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .ototamir-adblock-btn-primary {
            background: #c2410c;
            color: #ffffff;
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 14.5px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s, transform 0.1s;
            box-shadow: 0 4px 12px rgba(194, 65, 12, 0.35);
        }
        .ototamir-adblock-btn-primary:hover {
            background: #9a3412;
            transform: translateY(-1px);
        }
        .ototamir-adblock-btn-dismiss {
            background: transparent;
            color: #64748b;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: color 0.2s;
        }
        .ototamir-adblock-btn-dismiss:hover {
            color: #0f172a;
            text-decoration: underline;
        }
        @media (max-width: 480px) {
            .ototamir-adblock-card {
                padding: 28px 20px 22px;
            }
            .ototamir-adblock-title {
                font-size: 18px;
            }
        }
        </style>

        <script>
        (function() {
            var KEY_ADBLOCK_DISMISSED = 'ototamir_adblock_dismissed_v2';
            var KEY_SUPPORT_SEEN = 'ototamir_ai_support_seen_v1';
            var ADBLOCK_COOLDOWN_MS = 24 * 60 * 60 * 1000; // 24 saat

            function getStorage(k) {
                try { return localStorage.getItem(k); } catch(e) { return null; }
            }
            function setStorage(k, v) {
                try { localStorage.setItem(k, v); } catch(e) {}
            }

            function openModal(mode) {
                var modal = document.getElementById('ototamir-adblock-modal');
                if (!modal || modal.classList.contains('active')) return;

                var adblockView = document.getElementById('ototamir-modal-adblock-view');
                var supportView = document.getElementById('ototamir-modal-support-view');

                if (mode === 'adblock') {
                    if (adblockView) adblockView.style.display = 'block';
                    if (supportView) supportView.style.display = 'none';
                } else {
                    if (adblockView) adblockView.style.display = 'none';
                    if (supportView) supportView.style.display = 'block';
                }

                modal.style.display = 'flex';
                requestAnimationFrame(function() {
                    modal.classList.add('active');
                });

                function closeModal() {
                    if (mode === 'adblock') {
                        setStorage(KEY_ADBLOCK_DISMISSED, Date.now().toString());
                    } else {
                        setStorage(KEY_SUPPORT_SEEN, '1'); // Normal kullanıcıya 1 kez göster
                    }
                    modal.classList.remove('active');
                    setTimeout(function() {
                        if (modal && modal.parentNode) {
                            modal.parentNode.removeChild(modal);
                        }
                    }, 350);
                }

                var dismissBtn = document.getElementById('ototamir-adblock-dismiss-btn');
                var closeBtn = document.getElementById('ototamir-adblock-close-btn');
                var backdrop = document.getElementById('ototamir-adblock-backdrop');
                var whitelistBtn = document.getElementById('ototamir-adblock-whitelist-btn');
                var supportOkBtn = document.getElementById('ototamir-support-ok-btn');

                if (dismissBtn) dismissBtn.addEventListener('click', closeModal);
                if (closeBtn) closeBtn.addEventListener('click', closeModal);
                if (backdrop) backdrop.addEventListener('click', closeModal);
                if (supportOkBtn) supportOkBtn.addEventListener('click', closeModal);
                if (whitelistBtn) whitelistBtn.addEventListener('click', function() {
                    try { localStorage.removeItem(KEY_ADBLOCK_DISMISSED); } catch(e) {}
                    window.location.reload();
                });
            }

            // Sayfa yüklendikten 4 saniye sonra akıllı kontrol
            window.addEventListener('load', function() {
                setTimeout(function() {
                    var bait = document.getElementById('ototamir-ad-bait');
                    var isAdBlocked = false;

                    // 1. Yem elementinin AdBlock tarafından gizlenip gizlenmediğini kontrol et
                    if (!bait) {
                        isAdBlocked = true;
                    } else {
                        var style = window.getComputedStyle(bait);
                        // Yem element AdBlock tarafından display:none veya visibility:hidden yapıldı mı?
                        if (style.display === 'none' || style.visibility === 'hidden' || bait.offsetHeight === 0) {
                            isAdBlocked = true;
                        }
                    }

                    if (isAdBlocked) {
                        handleAdBlockFound();
                    } else {
                        // 2. Aşama: AdSense script network fetch testi
                        fetch('https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js', {
                            method: 'HEAD',
                            mode: 'no-cors',
                            cache: 'no-store'
                        }).then(function() {
                            // AdBlock YOK -> Normal kullanıcı
                            handleNormalUser();
                        }).catch(function() {
                            // AdBlock VAR -> Ağ isteği engellendi
                            handleAdBlockFound();
                        });
                    }

                    function handleAdBlockFound() {
                        var lastDismiss = getStorage(KEY_ADBLOCK_DISMISSED);
                        if (lastDismiss && (Date.now() - parseInt(lastDismiss, 10)) < ADBLOCK_COOLDOWN_MS) {
                            return; // 24 saat geçmedi
                        }
                        openModal('adblock');
                    }

                    function handleNormalUser() {
                        var seen = getStorage(KEY_SUPPORT_SEEN);
                        if (seen === '1') {
                            return; // Bu kullanıcı daha önce 1 defa gördü, bir daha rahatsız etme!
                        }
                        openModal('support');
                    }

                }, 4000);
            });
        })();
        </script>
        <?php
    }
}

// Modülü başlat
OtoTamir_AdSense::init();

/**
 * Global Tema Fonksiyonu: Reklam Alanı Göster
 */
if ( ! function_exists( 'ototamir_render_ad' ) ) {
    function ototamir_render_ad( $slot_name ) {
        return OtoTamir_AdSense::render_ad( $slot_name );
    }
}
