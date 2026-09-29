<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class OTB_AI_Shortcode {

    public function __construct() {
        add_shortcode( 'otb_ai_teshis', [ $this, 'form' ] );
        add_shortcode( 'otb_ai_kredi',  [ $this, 'credits' ] );
    }

    public function credits() {
        if ( ! is_user_logged_in() ) return '<p>Kredi bilgisi için giriş yap.</p>';
        if ( current_user_can( 'manage_options' ) ) return '<p>Teşhis hakkı: <strong>∞</strong></p>';
        return '<p>Teşhis hakkı: <strong>' . OTB_AI_Credits::get( get_current_user_id() ) . '</strong></p>';
    }

    public function form() {
        $require_login = OTB_AI_Settings::get( OTB_AI_Settings::REQ_LOGIN, '1' );

        if ( $require_login === '1' && ! is_user_logged_in() ) {
            ob_start(); ?>
            <div class="otb-wrap">
                <div class="otb-card">
                    <div class="otb-locked">
                        <span class="otb-locked-icon">🔒</span>
                        <h3>Giriş Gerekli</h3>
                        <p>Yapay zekâ teşhis özelliğini kullanmak için giriş yap veya ücretsiz kayıt ol.</p>
                        <div class="otb-auth-btns">
                            <a href="<?php echo esc_url( home_url( '/giris/?redirect_to=' . urlencode( get_permalink() ) ) ); ?>" class="otb-btn otb-btn-primary">Giriş Yap</a>
                            <a href="<?php echo esc_url( home_url( '/kayit/' ) ); ?>" class="otb-btn otb-btn-secondary">Kayıt Ol</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php return ob_get_clean();
        }

        $uid     = is_user_logged_in() ? get_current_user_id() : 0;
        $credits = $uid ? OTB_AI_Credits::get( $uid ) : 0;
        $display = ( $uid && current_user_can( 'manage_options' ) ) ? '∞' : $credits;
        $cost    = max( 1, (int) OTB_AI_Settings::get( OTB_AI_Settings::COST, 3 ) );
        $nonce   = wp_create_nonce( 'otb_ai_nonce' );
        $ajax    = admin_url( 'admin-ajax.php' );

        ob_start();
        include OTB_AI_PATH . 'templates/form.php';
        return ob_get_clean();
    }
}
