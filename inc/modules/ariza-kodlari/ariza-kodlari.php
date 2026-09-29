<?php
/**
 * Plugin Name: Arıza Kodları
 * Description: OBD arıza kodlarını Groq AI ile analiz eden WordPress eklentisi. Kısa kod: [ariza_kodlari]
 * Version:     1.0.0
 * Author:      Serkan
 * Text Domain: ariza-kodlari
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'AK_PATH' ) ) {
    define( 'AK_PATH', trailingslashit( wp_normalize_path( dirname( __FILE__ ) ) ) );
}
if ( ! defined( 'AK_URL' ) ) {
    define( 'AK_URL',  trailingslashit( get_template_directory_uri() . '/inc/modules/ariza-kodlari' ) );
}

if ( ! class_exists( 'AK_Settings' ) ) {
    require_once AK_PATH . 'includes/class-db.php';
    require_once AK_PATH . 'includes/data-obd-codes.php';
    require_once AK_PATH . 'includes/class-settings.php';
    require_once AK_PATH . 'includes/class-groq.php';
    require_once AK_PATH . 'includes/class-blog.php';
    require_once AK_PATH . 'includes/class-ajax.php';
    require_once AK_PATH . 'includes/class-shortcode.php';

    // Sadece admin panelinde ve versiyon değişiminde çalışır (ziyaretçi isteklerini yormaz)
    add_action( 'admin_init', function() {
        if ( get_option( 'ak_db_seeded' ) !== AK_DB::VERSION ) {
            AK_DB::install();
        }
    } );

    new AK_Settings();
    new AK_Ajax();
    new AK_Shortcode();
}
