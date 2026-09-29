<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AK_Ajax {

    public function __construct() {
        add_action( 'wp_ajax_ak_analyse',        [ $this, 'analyse' ] );
        add_action( 'wp_ajax_nopriv_ak_analyse', [ $this, 'analyse' ] );
    }

    public function analyse() {
        if ( ! isset( $_POST['_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ), 'ak_nonce' ) )
            wp_send_json_error( [ 'message' => 'Güvenlik hatası.' ] );

        $code = sanitize_text_field( wp_unslash( $_POST['code'] ?? '' ) );
        $name = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );

        if ( empty( $code ) ) wp_send_json_error( [ 'message' => 'Kod eksik.' ] );

        // Transient cache – aynı kodu tekrar sorma
        $cache_key = 'ak_' . md5( $code );
        $cached    = get_transient( $cache_key );
        if ( $cached ) wp_send_json_success( $cached );

        $result = AK_Groq::analyse( $code, $name );

        if ( isset( $result['error'] ) )
            wp_send_json_error( [ 'message' => $result['error'] ] );

        set_transient( $cache_key, $result, DAY_IN_SECONDS );
        wp_send_json_success( $result );
    }
}
