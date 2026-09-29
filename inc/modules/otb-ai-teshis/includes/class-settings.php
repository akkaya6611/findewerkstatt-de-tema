<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class OTB_AI_Settings {

    const API_KEY    = 'otb_ai_groq_key';
    const MODEL      = 'otb_ai_groq_model';
    const REQ_LOGIN  = 'otb_ai_require_login';
    const DEF_CREDIT = 'otb_ai_default_credits';
    const COST       = 'otb_ai_cost_per_use';
    const BLOG_ON    = 'otb_ai_blog_enabled';
    const BLOG_STATUS= 'otb_ai_blog_status';
    const BLOG_CAT   = 'otb_ai_blog_category';

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'menu' ] );
        add_action( 'admin_init', [ $this, 'register' ] );
    }

    public function menu() {
        add_options_page( 'OTB AI Teşhis', 'OTB AI Teşhis', 'manage_options', 'otb-ai-teshis', [ $this, 'page' ] );
    }

    public function register() {
        $opts = [ self::API_KEY, self::MODEL, self::REQ_LOGIN, self::DEF_CREDIT, self::COST, self::BLOG_ON, self::BLOG_STATUS, self::BLOG_CAT ];
        foreach ( $opts as $o ) register_setting( 'otb_ai_group', $o );
    }

    public function page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $cats = get_categories( [ 'hide_empty' => 0 ] );
        ?>
        <div class="wrap">
            <h1>OTB AI Teşhis Ayarları</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'otb_ai_group' ); ?>
                <table class="form-table">
                    <tr><th>Groq API Anahtarı</th>
                        <td><input type="password" name="<?php echo self::API_KEY; ?>" value="<?php echo esc_attr( get_option( self::API_KEY ) ); ?>" style="width:420px"></td></tr>
                    <tr><th>Model</th>
                        <td><input type="text" name="<?php echo self::MODEL; ?>" value="<?php echo esc_attr( get_option( self::MODEL, 'llama-3.3-70b-versatile' ) ); ?>" style="width:220px">
                        <p class="description">Örn: <code>llama-3.3-70b-versatile</code> veya <code>llama-3.1-8b-instant</code></p></td></tr>
                    <tr><th>Giriş zorunlu?</th>
                        <td><label><input type="checkbox" name="<?php echo self::REQ_LOGIN; ?>" value="1" <?php checked( get_option( self::REQ_LOGIN, '1' ), '1' ); ?>> Evet</label></td></tr>
                    <tr><th>Başlangıç kredisi</th>
                        <td><input type="number" name="<?php echo self::DEF_CREDIT; ?>" value="<?php echo (int) get_option( self::DEF_CREDIT, 6 ); ?>" min="0" style="width:80px"></td></tr>
                    <tr><th>Teşhis başına kredi</th>
                        <td><input type="number" name="<?php echo self::COST; ?>" value="<?php echo (int) get_option( self::COST, 3 ); ?>" min="1" style="width:80px"></td></tr>
                    <tr><th>Blog üretimi</th>
                        <td><label><input type="checkbox" name="<?php echo self::BLOG_ON; ?>" value="1" <?php checked( get_option( self::BLOG_ON, '0' ), '1' ); ?>> Kullanıcı onayıyla teşhisleri blog yazısına dönüştür</label></td></tr>
                    <tr><th>Blog durumu</th>
                        <td><select name="<?php echo self::BLOG_STATUS; ?>">
                            <option value="draft"   <?php selected( get_option( self::BLOG_STATUS, 'draft' ), 'draft' ); ?>>Taslak</option>
                            <option value="publish" <?php selected( get_option( self::BLOG_STATUS, 'draft' ), 'publish' ); ?>>Yayında</option>
                        </select></td></tr>
                    <tr><th>Blog kategorisi</th>
                        <td><select name="<?php echo self::BLOG_CAT; ?>">
                            <option value="0">— Seçilmesin —</option>
                            <?php foreach ( $cats as $c ) : ?>
                                <option value="<?php echo (int) $c->term_id; ?>" <?php selected( (int) get_option( self::BLOG_CAT, 0 ), (int) $c->term_id ); ?>><?php echo esc_html( $c->name ); ?></option>
                            <?php endforeach; ?>
                        </select></td></tr>
                </table>
                <?php submit_button(); ?>
            </form>
            <hr>
            <p><strong>Shortcode:</strong> <code>[otb_ai_teshis]</code> &nbsp; <code>[otb_ai_kredi]</code></p>
        </div>
        <?php
    }

    public static function get( $key, $default = '' ) {
        return get_option( $key, $default );
    }
}
