<?php
/**
 * Otomatik Araç Marka ve Model Sayfaları Oluşturucu (Database Page Generator)
 * 
 * Türkiye geneli 36 ana otomotiv markası ve 375 model için 
 * "Size En Yakın [Marka] [Model] Oto Tamircileri" WordPress sayfalarını 
 * veritabanına ultra hızlı şekilde ekler ve yönetir.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OtoTamir_Brand_Model_Pages_Generator {

    const OPTION_KEY = 'ototamir_brand_model_pages_v1_created';

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'register_admin_menu' ) );
        add_action( 'wp_ajax_ototamir_sync_brand_pages', array( __CLASS__, 'ajax_sync_pages' ) );
    }

    /**
     * Tüm Marka ve Model Sayfalarını Ultra Hızlı Şekilde Veritabanına Ekle
     */
    public static function generate_all_pages() {
        if ( ! class_exists( 'OtoTamir_Brands_Models_Migration' ) ) {
            return array( 'created' => 0, 'existing' => 0, 'total' => 0 );
        }

        global $wpdb;

        // Mevcut tüm sayfaların slug'larını TEK bir SQL sorgusuyla hafızaya al (O(1) lookup)
        $existing_slugs = $wpdb->get_col( "SELECT post_name FROM {$wpdb->posts} WHERE post_type = 'page'" );
        $existing_map   = is_array( $existing_slugs ) ? array_flip( $existing_slugs ) : array();

        $brands_and_models = OtoTamir_Brands_Models_Migration::get_brands_and_models();
        $created_count  = 0;
        $existing_count = 0;

        foreach ( $brands_and_models as $brand_name => $models ) {
            $brand_slug = sanitize_title( $brand_name );

            // 1. Ana Marka Sayfası (Örn: "Size En Yakın Chevrolet Oto Tamircileri & Özel Servisleri")
            $brand_page_title = "Size En Yakın {$brand_name} Oto Tamircileri & Özel Servisleri";
            $brand_page_slug  = sanitize_title( "size en yakin {$brand_name} oto tamircileri" );

            if ( ! isset( $existing_map[ $brand_page_slug ] ) ) {
                $b_post_id = wp_insert_post( array(
                    'post_title'   => $brand_page_title,
                    'post_name'    => $brand_page_slug,
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                    'post_content' => "<!-- wp:paragraph -->\n<p>{$brand_name} marka aracınız için garantili bakım, orijinal yedek parça, motor mekanik ve periyodik servis hizmeti sunan en yakın uzman oto tamircilerini keşfedin.</p>\n<!-- /wp:paragraph -->",
                ) );

                if ( ! is_wp_error( $b_post_id ) ) {
                    update_post_meta( $b_post_id, '_ototamir_page_type', 'brand_landing' );
                    update_post_meta( $b_post_id, '_ototamir_target_brand', $brand_name );
                    update_post_meta( $b_post_id, '_ototamir_brand_slug', $brand_slug );
                    update_post_meta( $b_post_id, '_wp_page_template', 'page-model-rehberi.php' );
                    $existing_map[ $brand_page_slug ] = $b_post_id;
                    $created_count++;
                }
            } else {
                $existing_count++;
            }

            // 2. Alt Model Sayfaları (Örn: "Size En Yakın Chevrolet Cruze Oto Tamircileri")
            foreach ( $models as $model_name ) {
                $model_slug_full  = sanitize_title( $brand_name . '-' . $model_name );
                $model_page_title = "Size En Yakın {$brand_name} {$model_name} Oto Tamircileri";
                $model_page_slug  = sanitize_title( "size en yakin {$brand_name} {$model_name} oto tamircileri" );

                if ( ! isset( $existing_map[ $model_page_slug ] ) ) {
                    $m_post_id = wp_insert_post( array(
                        'post_title'   => $model_page_title,
                        'post_name'    => $model_page_slug,
                        'post_status'  => 'publish',
                        'post_type'    => 'page',
                        'post_content' => "<!-- wp:paragraph -->\n<p>{$brand_name} {$model_name} aracınız için garantili motor mekanik tamiri, periyodik bakım, fren balata değişimi, bilgisayarlı arıza tespiti ve en yakın uzman oto servislerini keşfedin.</p>\n<!-- /wp:paragraph -->",
                    ) );

                    if ( ! is_wp_error( $m_post_id ) ) {
                        update_post_meta( $m_post_id, '_ototamir_page_type', 'brand_model_landing' );
                        update_post_meta( $m_post_id, '_ototamir_target_brand', $brand_name );
                        update_post_meta( $m_post_id, '_ototamir_target_model', $model_name );
                        update_post_meta( $m_post_id, '_ototamir_brand_slug', $brand_slug );
                        update_post_meta( $m_post_id, '_ototamir_model_slug', $model_slug_full );
                        update_post_meta( $m_post_id, '_wp_page_template', 'page-model-rehberi.php' );
                        $existing_map[ $model_page_slug ] = $m_post_id;
                        $created_count++;
                    }
                } else {
                    $existing_count++;
                }
            }
        }

        $res = array(
            'time'     => time(),
            'created'  => $created_count,
            'existing' => $existing_count,
            'total'    => $created_count + $existing_count,
        );

        update_option( self::OPTION_KEY, $res );

        return $res;
    }

    /**
     * WP-Admin Menüsü Ekle
     */
    public static function register_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=mechanic',
            '🚗 Marka & Model Sayfaları',
            '🚗 Marka & Model Sayfaları',
            'manage_options',
            'ototamir-brand-model-pages',
            array( __CLASS__, 'render_admin_page' )
        );
    }

    /**
     * AJAX Senkronizasyon Tetikleyici
     */
    public static function ajax_sync_pages() {
        check_ajax_referer( 'ototamir_sync_pages_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Yetkisiz erişim.' ) );
        }

        $result = self::generate_all_pages();
        wp_send_json_success( array(
            'message'  => "İşlem tamamlandı! Toplam {$result['total']} sayfa kontrol edildi ({$result['created']} yeni eklendi, {$result['existing']} zaten mevcuttu).",
            'stats'    => $result,
        ) );
    }

    /**
     * WP-Admin Arayüzü
     */
    public static function render_admin_page() {
        $nonce = wp_create_nonce( 'ototamir_sync_pages_nonce' );

        global $wpdb;
        $total_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_ototamir_page_type'" );

        // Veritabanındaki marka/model sayfalarını çek
        $pages = get_posts( array(
            'post_type'      => 'page',
            'posts_per_page' => 50,
            'post_status'    => 'publish',
            'meta_key'       => '_ototamir_page_type',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );
        ?>
        <div class="wrap" style="max-width: 1200px;">
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; margin: 20px 0 24px;">
                <div>
                    <h1 style="margin:0; font-size: 26px; font-weight:800; color: #0f172a; display:flex; align-items:center; gap:10px;">
                        <span>🚗</span> Araç Marka & Model Sayfaları Veritabanı
                    </h1>
                    <p style="margin: 6px 0 0 0; color: #64748b; font-size: 14px;">
                        Türkiye genelindeki 36 ana otomotiv markası ve 375 popüler model için oluşturulan SEO uyumlu landing sayfaları.
                    </p>
                </div>
                <div style="display:flex; gap:10px;">
                    <a href="<?php echo esc_url( admin_url('edit.php?post_type=page') ); ?>" class="button button-secondary" style="height:42px; display:inline-flex; align-items:center; gap:6px; font-weight:600;">
                        <span class="dashicons dashicons-admin-page"></span> Tüm Sayfaları WP-Admin'de Aç
                    </a>
                    <button type="button" id="btnSyncBrandPages" onclick="syncBrandModelPages();" class="button button-primary" style="height:42px; display:inline-flex; align-items:center; gap:6px; font-weight:700; background:#ea580c; border-color:#c2410c;">
                        <span class="dashicons dashicons-update"></span> Sayfaları Senkronize Et & Oluştur
                    </button>
                </div>
            </div>

            <div id="syncStatusNotice" style="display:none; padding:12px 18px; border-radius:8px; margin-bottom:20px; font-weight:600;"></div>

            <!-- KPI Kartları -->
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:18px; margin-bottom: 26px;">
                <div style="background:white; border:1px solid #e2e8f0; border-radius:12px; padding:20px; box-shadow:0 2px 6px rgba(0,0,0,0.03);">
                    <div style="font-size:13px; color:#64748b; font-weight:700; text-transform:uppercase;">Toplam Oluşturulan Sayfa</div>
                    <div style="font-size:32px; font-weight:900; color:#0f172a; margin-top:6px;" id="statTotalPages"><?php echo number_format_i18n( $total_count ); ?></div>
                    <div style="font-size:12px; color:#16a34a; margin-top:4px;"><span class="dashicons dashicons-yes-alt" style="font-size:16px;"></span> Veritabanında (wp_posts) yayında</div>
                </div>

                <div style="background:white; border:1px solid #e2e8f0; border-radius:12px; padding:20px; box-shadow:0 2px 6px rgba(0,0,0,0.03);">
                    <div style="font-size:13px; color:#64748b; font-weight:700; text-transform:uppercase;">Kapsanan Otomotiv Markası</div>
                    <div style="font-size:32px; font-weight:900; color:#ea580c; margin-top:6px;">36</div>
                    <div style="font-size:12px; color:#64748b; margin-top:4px;">Renault, Fiat, VW, Ford, Chevrolet vb.</div>
                </div>

                <div style="background:white; border:1px solid #e2e8f0; border-radius:12px; padding:20px; box-shadow:0 2px 6px rgba(0,0,0,0.03);">
                    <div style="font-size:13px; color:#64748b; font-weight:700; text-transform:uppercase;">Kapsanan Araç Modeli</div>
                    <div style="font-size:32px; font-weight:900; color:#2563eb; margin-top:6px;">375</div>
                    <div style="font-size:12px; color:#64748b; margin-top:4px;">Cruze, Egea, Megane, Golf, Focus vb.</div>
                </div>
            </div>

            <!-- Tablo -->
            <div style="background:white; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                <div style="padding:16px 20px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
                    <strong style="font-size:15px; color:#0f172a;">Örnek Marka & Model Sayfaları (İlk 50)</strong>
                    <span style="font-size:13px; color:#64748b;">Gutenberg / Klasik düzenleyici ile her bir sayfanın metnini dilediğiniz gibi özelleştirebilirsiniz.</span>
                </div>

                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="font-weight:700; width:45%;">Sayfa Başlığı</th>
                            <th style="font-weight:700; width:20%;">Hedef Marka / Model</th>
                            <th style="font-weight:700; width:20%;">Kalıcı Bağlantı (URL)</th>
                            <th style="font-weight:700; width:15%; text-align:right;">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( ! empty( $pages ) ) : ?>
                            <?php foreach ( $pages as $p ) : 
                                $b_val = get_post_meta( $p->ID, '_ototamir_target_brand', true );
                                $m_val = get_post_meta( $p->ID, '_ototamir_target_model', true );
                                $tag_text = $m_val ? "{$b_val} &raquo; {$m_val}" : "{$b_val} (Genel Marka)";
                            ?>
                                <tr>
                                    <td>
                                        <strong><a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>"><?php echo esc_html( $p->post_title ); ?></a></strong>
                                    </td>
                                    <td>
                                        <span style="background:#f1f5f9; padding:3px 8px; border-radius:6px; font-size:12px; font-weight:600; color:#334155;">
                                            <?php echo wp_kses_post( $tag_text ); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <code style="font-size:12px; background:#f8fafc; padding:3px 6px;"><?php echo esc_html( '/' . $p->post_name . '/' ); ?></code>
                                    </td>
                                    <td style="text-align:right;">
                                        <a href="<?php echo esc_url( get_permalink( $p->ID ) ); ?>" target="_blank" class="button button-small" style="font-weight:600;">
                                            <span class="dashicons dashicons-visibility" style="font-size:14px; margin-top:2px;"></span> Görüntüle
                                        </a>
                                        <a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>" class="button button-small" style="font-weight:600;">
                                            Düzenle
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="4" style="text-align:center; padding:24px; color:#64748b;">
                                    Henüz sayfa oluşturulmamış. Lütfen yukarıdaki <strong>"Sayfaları Senkronize Et & Oluştur"</strong> butonuna basınız.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <script>
        function syncBrandModelPages() {
            var btn = document.getElementById('btnSyncBrandPages');
            var notice = document.getElementById('syncStatusNotice');
            if (!btn) return;

            btn.disabled = true;
            btn.innerHTML = '<span class="dashicons dashicons-update" style="animation: rotation 1s infinite linear;"></span> Oluşturuluyor...';

            var formData = new FormData();
            formData.append('action', 'ototamir_sync_brand_pages');
            formData.append('nonce', '<?php echo esc_js( $nonce ); ?>');

            fetch(ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(json) {
                btn.disabled = false;
                btn.innerHTML = '<span class="dashicons dashicons-update"></span> Sayfaları Senkronize Et & Oluştur';

                if (notice) {
                    notice.style.display = 'block';
                    if (json.success) {
                        notice.style.background = '#f0fdf4';
                        notice.style.border = '1px solid #bbf7d0';
                        notice.style.color = '#15803d';
                        notice.innerHTML = '<span class="dashicons dashicons-yes-alt"></span> ' + (json.data && json.data.message ? json.data.message : 'Sayfalar başarıyla oluşturuldu!');
                        if (json.data && json.data.stats && document.getElementById('statTotalPages')) {
                            document.getElementById('statTotalPages').innerText = json.data.stats.total;
                        }
                        setTimeout(function() { window.location.reload(); }, 1500);
                    } else {
                        notice.style.background = '#fef2f2';
                        notice.style.border = '1px solid #fecaca';
                        notice.style.color = '#991b1b';
                        notice.innerHTML = '<span class="dashicons dashicons-warning"></span> ' + (json.data && json.data.message ? json.data.message : 'Hata oluştu.');
                    }
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.innerHTML = '<span class="dashicons dashicons-update"></span> Sayfaları Senkronize Et & Oluştur';
                if (notice) {
                    notice.style.display = 'block';
                    notice.style.background = '#fef2f2';
                    notice.style.border = '1px solid #fecaca';
                    notice.style.color = '#991b1b';
                    notice.innerHTML = '<span class="dashicons dashicons-warning"></span> Bağlantı hatası oluştu.';
                }
            });
        }
        </script>
        <style>
        @keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }
        </style>
        <?php
    }
}

OtoTamir_Brand_Model_Pages_Generator::init();
