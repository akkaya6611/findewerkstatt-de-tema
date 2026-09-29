<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AK_Settings {

    const API_KEY = 'ak_groq_api_key';
    const MODEL   = 'ak_groq_model';

    public function __construct() {
        add_action( 'admin_menu',            [ $this, 'menu' ] );
        add_action( 'admin_init',            [ $this, 'register' ] );
        add_action( 'wp_ajax_ak_gen_blog',   [ $this, 'ajax_gen_blog' ] );
    }

    public function menu() {
        add_options_page( 'Arıza Kodları', 'Arıza Kodları', 'manage_options', 'ariza-kodlari', [ $this, 'page' ] );
    }

    public function register() {
        register_setting( 'ak_settings', self::API_KEY, 'sanitize_text_field' );
        register_setting( 'ak_settings', self::MODEL,   'sanitize_text_field' );
    }

    public function ajax_gen_blog() {
        check_ajax_referer( 'ak_admin_nonce', '_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( [ 'message' => 'Yetkisiz.' ] );

        $category = sanitize_text_field( $_POST['category'] ?? '' );
        if ( ! in_array( $category, [ 'P', 'B', 'C', 'U' ], true ) )
            wp_send_json_error( [ 'message' => 'Geçersiz kategori.' ] );

        $result = AK_Blog::generate( $category );

        if ( isset( $result['error'] ) ) wp_send_json_error( [ 'message' => $result['error'] ] );

        wp_send_json_success( $result );
    }

    public function page() {
        $nonce = wp_create_nonce( 'ak_admin_nonce' );
        $ajax  = admin_url( 'admin-ajax.php' );
        $all_codes = ak_get_obd_codes();

        $cat_info = [
            'P' => [ 'icon' => '⚙️', 'color' => '#3b82f6', 'desc' => 'Motor, Şanzıman, Yakıt' ],
            'B' => [ 'icon' => '🚗', 'color' => '#8b5cf6', 'desc' => 'Kaporta, Hava Yastığı, Konfor' ],
            'C' => [ 'icon' => '🛞', 'color' => '#f59e0b', 'desc' => 'ABS, ESP, Fren, Süspansiyon' ],
            'U' => [ 'icon' => '📡', 'color' => '#10b981', 'desc' => 'CAN Bus, ECU İletişimi' ],
        ];
        ?>
        <div class="wrap">
            <h1 style="margin-bottom:24px">Arıza Kodları – Ayarlar</h1>

            <!-- API Ayarları -->
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;max-width:600px;margin-bottom:28px">
                <h2 style="margin-top:0;font-size:16px">API Ayarları</h2>
                <form method="post" action="options.php">
                    <?php settings_fields( 'ak_settings' ); ?>
                    <table class="form-table" style="margin-top:0">
                        <tr>
                            <th style="width:140px">Groq API Key</th>
                            <td><input type="password" name="<?php echo self::API_KEY; ?>" value="<?php echo esc_attr( get_option( self::API_KEY ) ); ?>" class="regular-text" placeholder="gsk_..."></td>
                        </tr>
                        <tr>
                            <th>Model</th>
                            <td>
                                <input type="text" name="<?php echo self::MODEL; ?>" value="<?php echo esc_attr( get_option( self::MODEL, 'llama-3.3-70b-versatile' ) ); ?>" class="regular-text">
                                <p class="description">Önerilen: llama-3.3-70b-versatile</p>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button( 'Kaydet', 'primary', 'submit', false ); ?>
                </form>
            </div>

            <!-- Makale Üretici -->
            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;max-width:900px">
                <h2 style="margin-top:0;font-size:16px">🤖 AI Makale Üretici</h2>
                <p style="color:#64748b;margin-bottom:20px;font-size:13px">
                    Her OBD kategorisi için Groq AI ile detaylı Türkçe makale oluşturur. Makale taslak olarak kaydedilir, yayınlamadan önce düzenleyebilirsiniz.
                </p>

                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px" id="ak-blog-grid">
                    <?php foreach ( $cat_info as $cat => $info ) :
                        $existing = get_posts( [
                            'post_type'   => 'post',
                            'post_status' => 'any',
                            'meta_key'    => '_ak_obd_category',
                            'meta_value'  => $cat,
                            'numberposts' => 1,
                        ] );
                        $has_post = ! empty( $existing );
                        $post_id  = $has_post ? $existing[0]->ID : null;
                        $status   = $has_post ? get_post_status( $post_id ) : null;
                    ?>
                    <div class="ak-cat-card" id="ak-card-<?php echo $cat; ?>" style="border:1.5px solid #e2e8f0;border-radius:10px;padding:18px;position:relative">
                        <div style="font-size:28px;margin-bottom:8px"><?php echo $info['icon']; ?></div>
                        <div style="font-size:22px;font-weight:800;color:<?php echo $info['color']; ?>;margin-bottom:4px"><?php echo $cat; ?></div>
                        <div style="font-size:12px;color:#64748b;margin-bottom:4px"><?php echo esc_html( $all_codes[$cat]['label'] ); ?></div>
                        <div style="font-size:11px;color:#94a3b8;margin-bottom:14px"><?php echo count( $all_codes[$cat]['codes'] ); ?> kod</div>

                        <?php if ( $has_post ) : ?>
                        <div style="font-size:11px;margin-bottom:10px">
                            <span style="background:<?php echo $status === 'publish' ? '#dcfce7' : '#fef9c3'; ?>;color:<?php echo $status === 'publish' ? '#166534' : '#854d0e'; ?>;padding:2px 8px;border-radius:999px;font-weight:600">
                                <?php echo $status === 'publish' ? '✅ Yayında' : '📝 Taslak'; ?>
                            </span>
                        </div>
                        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px">
                            <a href="<?php echo get_edit_post_link( $post_id ); ?>" target="_blank" style="font-size:11px;color:#3b82f6;text-decoration:none">✏️ Düzenle</a>
                            <?php if ( $status === 'publish' ) : ?>
                            <a href="<?php echo get_permalink( $post_id ); ?>" target="_blank" style="font-size:11px;color:#10b981;text-decoration:none">🔗 Görüntüle</a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <button class="ak-gen-btn button button-primary" data-cat="<?php echo $cat; ?>" data-nonce="<?php echo $nonce; ?>" data-ajax="<?php echo esc_url( $ajax ); ?>" style="width:100%;font-size:12px;background:<?php echo $info['color']; ?>;border-color:<?php echo $info['color']; ?>">
                            <?php echo $has_post ? '🔄 Yeniden Üret' : '✨ Makale Üret'; ?>
                        </button>

                        <div class="ak-gen-status" id="ak-status-<?php echo $cat; ?>" style="display:none;margin-top:10px;font-size:12px;text-align:center;color:#64748b"></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top:16px;padding:12px;background:#f8fafc;border-radius:8px;font-size:12px;color:#64748b">
                    💡 <strong>Not:</strong> Makale üretimi 30-60 saniye sürebilir. Sayfayı kapatmayın. Üretilen makaleler taslak olarak kaydedilir.
                </div>
            </div>
        </div>

        <script>
        document.querySelectorAll('.ak-gen-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var cat    = this.dataset.cat;
                var nonce  = this.dataset.nonce;
                var ajax   = this.dataset.ajax;
                var status = document.getElementById('ak-status-' + cat);

                btn.disabled    = true;
                btn.textContent = '⏳ Üretiliyor...';
                status.style.display = 'block';
                status.textContent   = 'Groq AI makale yazıyor, lütfen bekleyin...';

                var fd = new FormData();
                fd.append('action',   'ak_gen_blog');
                fd.append('_nonce',   nonce);
                fd.append('category', cat);

                fetch(ajax, { method: 'POST', body: fd })
                    .then(function(r) { return r.json(); })
                    .then(function(json) {
                        btn.disabled = false;
                        if (!json.success) {
                            btn.textContent      = '❌ Hata – Tekrar Dene';
                            status.style.color   = '#ef4444';
                            status.textContent   = json.data?.message || 'Bilinmeyen hata.';
                            return;
                        }
                        var d = json.data;
                        btn.textContent      = '🔄 Yeniden Üret';
                        status.style.color   = '#10b981';
                        status.innerHTML     = '✅ Makale oluşturuldu! <a href="' + d.edit_url + '" target="_blank">Düzenle →</a>';
                        // Sayfayı yenile ki durum güncellensin
                        setTimeout(function(){ location.reload(); }, 2000);
                    })
                    .catch(function() {
                        btn.disabled    = false;
                        btn.textContent = '❌ Hata – Tekrar Dene';
                        status.style.color  = '#ef4444';
                        status.textContent  = 'İstek başarısız oldu.';
                    });
            });
        });
        </script>
        <?php
    }

    public static function get( $key, $default = '' ) {
        return get_option( $key, $default );
    }
}
