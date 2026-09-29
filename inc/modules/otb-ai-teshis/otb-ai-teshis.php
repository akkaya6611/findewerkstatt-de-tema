<?php
/**
 * Plugin Name: OTB AI Teşhis
 * Description: Groq AI destekli araç arıza teşhisi, kredi sistemi, servis slider, blog üretimi.
 * Version:     1.1.0
 * Author:      Serkan
 * Text Domain: otb-ai
 */
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'OTB_AI_PATH', trailingslashit( wp_normalize_path( dirname( __FILE__ ) ) ) );
define( 'OTB_AI_URL',  trailingslashit( get_template_directory_uri() . '/inc/modules/otb-ai-teshis' ) );

if ( ! class_exists( 'OTB_AI_Settings' ) ) {
    require_once OTB_AI_PATH . 'includes/class-settings.php';
    require_once OTB_AI_PATH . 'includes/class-credits.php';
    require_once OTB_AI_PATH . 'includes/class-groq.php';
    require_once OTB_AI_PATH . 'includes/class-gemini.php';
    require_once OTB_AI_PATH . 'includes/class-blog.php';
    require_once OTB_AI_PATH . 'includes/class-ajax.php';
    require_once OTB_AI_PATH . 'includes/class-shortcode.php';

    new OTB_AI_Settings();
    new OTB_AI_Ajax();
    new OTB_AI_Shortcode();
}
