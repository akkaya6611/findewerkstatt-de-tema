<?php
/**
 * FindeWerkstatt.de — Haupt-Themenfunktionen & Bootstrap
 * 
 * Saubere, modulare Architektur für Deutschlands führendes Kfz-Werkstattportal.
 * 
 * @package FindeWerkstatt
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1. Modulare Bibliotheken laden
require_once get_template_directory() . '/inc/german-data.php';
require_once get_template_directory() . '/inc/germany-geo-hierarchy.php';
require_once get_template_directory() . '/inc/post-types-taxonomies.php';
require_once get_template_directory() . '/inc/meta-boxes.php';
require_once get_template_directory() . '/inc/schema-seo.php';
require_once get_template_directory() . '/inc/helpers.php';
require_once get_template_directory() . '/inc/sample-data-seeder.php';
require_once get_template_directory() . '/inc/programmatic-seo.php';

// 2. Theme-Setup
function findewerkstatt_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );

    register_nav_menus( array(
        'primary' => __( 'Hauptnavigation', 'findewerkstatt' ),
        'footer'  => __( 'Footer Navigation', 'findewerkstatt' ),
    ) );
}
add_action( 'after_setup_theme', 'findewerkstatt_setup' );

// 3. Styles & Scripts einbinden
function findewerkstatt_scripts() {
    $ver = wp_get_theme()->get( 'Version' ) ?: '2.0.0';

    wp_enqueue_style( 'findewerkstatt-main', get_stylesheet_uri(), array(), $ver );

    // Theme JS
    if ( file_exists( get_template_directory() . '/assets/js/main.js' ) ) {
        wp_enqueue_script( 'findewerkstatt-main', get_template_directory_uri() . '/assets/js/main.js', array(), $ver, true );
    }

    // Localize Script für AJAX
    wp_localize_script( 'findewerkstatt-main', 'fwData', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'fw_ajax_nonce' ),
    ) );
}
add_action( 'wp_enqueue_scripts', 'findewerkstatt_scripts' );

// 4. Header-Bereinigung & Performance
function findewerkstatt_cleanup_head() {
    remove_action( 'wp_head', 'wp_generator' );
    remove_action( 'wp_head', 'rsd_link' );
    remove_action( 'wp_head', 'wlwmanifest_link' );
    remove_action( 'wp_head', 'wp_shortlink_wp_head' );
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'findewerkstatt_cleanup_head' );

// 5. Angepasste Abfrage für Werkstatt-Archiv & Suche (Filterung nach Stadt, Service, Marke)
function findewerkstatt_archive_query_filter( $query ) {
    if ( ! is_admin() && $query->is_main_query() && ( $query->is_post_type_archive( 'mechanic' ) || $query->is_search() ) ) {
        $query->set( 'posts_per_page', 12 );

        $tax_query = array( 'relation' => 'AND' );

        // Filter nach Bundesland / Stadt
        if ( ! empty( $_GET['fw_city'] ) ) {
            $tax_query[] = array(
                'taxonomy' => 'mechanic_city',
                'field'    => 'slug',
                'terms'    => sanitize_text_field( $_GET['fw_city'] ),
                'include_children' => true,
            );
        }

        // Filter nach Kategorie / Service
        if ( ! empty( $_GET['fw_service'] ) ) {
            $tax_query[] = array(
                'taxonomy' => 'service_type',
                'field'    => 'slug',
                'terms'    => sanitize_text_field( $_GET['fw_service'] ),
            );
        }

        // Filter nach Automarke
        if ( ! empty( $_GET['fw_brand'] ) ) {
            $tax_query[] = array(
                'taxonomy' => 'car_brand',
                'field'    => 'slug',
                'terms'    => sanitize_text_field( $_GET['fw_brand'] ),
            );
        }

        if ( count( $tax_query ) > 1 ) {
            $query->set( 'tax_query', $tax_query );
        }
    }
}
add_action( 'pre_get_posts', 'findewerkstatt_archive_query_filter' );
