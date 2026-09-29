<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AK_Shortcode {

    public function __construct() {
        add_shortcode( 'ariza_kodlari', [ $this, 'render' ] );
    }

    public function render() {
        $nonce = wp_create_nonce( 'ak_nonce' );
        $ajax  = admin_url( 'admin-ajax.php' );
        $codes = ak_get_obd_codes();

        ob_start();
        include AK_PATH . 'templates/list.php';
        return ob_get_clean();
    }
}
