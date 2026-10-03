<?php
/**
 * FindeWerkstatt.de — Haupt-Themenfunktionen & Bootstrap
 * 
 * Modulare Funktionen für das deutsche Kfz-Werkstattverzeichnis.
 * 
 * @package FindeWerkstatt
 * @version 3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1. Modulare Bibliotheken laden
require_once get_template_directory() . '/inc/multilingual.php';
require_once get_template_directory() . '/inc/workshop-languages.php';
require_once get_template_directory() . '/inc/german-data.php';
require_once get_template_directory() . '/inc/germany-geo-hierarchy.php';
require_once get_template_directory() . '/inc/post-types-taxonomies.php';
require_once get_template_directory() . '/inc/meta-boxes.php';
require_once get_template_directory() . '/inc/schema-seo.php';
require_once get_template_directory() . '/inc/helpers.php';
require_once get_template_directory() . '/inc/site-settings.php';
require_once get_template_directory() . '/inc/branding.php';
require_once get_template_directory() . '/inc/sample-data-seeder.php';
require_once get_template_directory() . '/inc/programmatic-seo.php';
require_once get_template_directory() . '/inc/city-indexing.php';
require_once get_template_directory() . '/inc/city-migration.php';
require_once get_template_directory() . '/inc/theme-upgrade.php';
require_once get_template_directory() . '/inc/site-assets.php';
require_once get_template_directory() . '/inc/form-handlers.php';
require_once get_template_directory() . '/inc/membership.php';
require_once get_template_directory() . '/inc/membership-workshops.php';
require_once get_template_directory() . '/inc/membership-admin.php';
require_once get_template_directory() . '/inc/member-dashboard.php';
require_once get_template_directory() . '/inc/city-directory.php';
require_once get_template_directory() . '/inc/location-search.php';
require_once get_template_directory() . '/inc/location-picker.php';
require_once get_template_directory() . '/inc/location-copy.php';
require_once get_template_directory() . '/inc/location-content.php';
require_once get_template_directory() . '/inc/import-compat.php';
require_once get_template_directory() . '/inc/workshop-importer-admin.php';

// 2. Theme-Setup
function findewerkstatt_setup() {
    load_theme_textdomain( 'findewerkstatt', get_template_directory() . '/languages' );
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
    $theme_dir = get_template_directory();
    $style_ver = file_exists( $theme_dir . '/style.css' ) ? (string) filemtime( $theme_dir . '/style.css' ) : '2.1.0';
    $header_ver = file_exists( $theme_dir . '/assets/css/header.css' ) ? (string) filemtime( $theme_dir . '/assets/css/header.css' ) : $style_ver;
    $membership_ver = file_exists( $theme_dir . '/assets/css/membership.css' ) ? (string) filemtime( $theme_dir . '/assets/css/membership.css' ) : $style_ver;
    $js_ver = file_exists( $theme_dir . '/assets/js/main.js' ) ? (string) filemtime( $theme_dir . '/assets/js/main.js' ) : $style_ver;

    wp_enqueue_style( 'findewerkstatt-main', get_stylesheet_uri(), array(), $style_ver );
    wp_enqueue_style( 'findewerkstatt-header', get_template_directory_uri() . '/assets/css/header.css', array( 'findewerkstatt-main' ), $header_ver );
    if ( is_page( array( 'pakete', 'mein-konto', 'werkstatt-anmelden' ) ) || is_page_template( array( 'page-pakete.php', 'page-mein-konto.php', 'page-werkstatt-anmelden.php' ) ) ) {
        wp_enqueue_style( 'findewerkstatt-membership', get_template_directory_uri() . '/assets/css/membership.css', array( 'findewerkstatt-main', 'findewerkstatt-header' ), $membership_ver );
    }

    // Theme JS
    if ( file_exists( $theme_dir . '/assets/js/main.js' ) ) {
        wp_enqueue_script( 'findewerkstatt-main', get_template_directory_uri() . '/assets/js/main.js', array(), $js_ver, true );
    }

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

        $query->set( 'post_type', 'mechanic' );
        // Validate both stages, retaining support for previous fw_city-only links.
        $location_filter = findewerkstatt_resolve_location_filter( $_GET );
        if ( '' !== $location_filter['location'] ) {
            $tax_query[] = array(
                'taxonomy' => 'mechanic_city',
                'field'    => 'slug',
                'terms'    => $location_filter['location'],
                'include_children' => true,
            );
        }

        // Filter nach Kategorie / Service
        if ( ! empty( $_GET['fw_service'] ) ) {
            $tax_query[] = array(
                'taxonomy' => 'service_type',
                'field'    => 'slug',
                'terms'    => is_string( $_GET['fw_service'] ) ? sanitize_title( wp_unslash( $_GET['fw_service'] ) ) : '__invalid__',
            );
        }

        // Filter nach Automarke
        if ( ! empty( $_GET['fw_brand'] ) ) {
            $tax_query[] = array(
                'taxonomy' => 'car_brand',
                'field'    => 'slug',
                'terms'    => is_string( $_GET['fw_brand'] ) ? sanitize_title( wp_unslash( $_GET['fw_brand'] ) ) : '__invalid__',
            );
        }

        if ( count( $tax_query ) > 1 ) {
            $existing = $query->get( 'tax_query' );
            if ( ! empty( $existing ) ) { $tax_query[] = $existing; }
            $query->set( 'tax_query', $tax_query );
        }
    }
}
add_action( 'pre_get_posts', 'findewerkstatt_archive_query_filter' );

add_filter( 'document_title_parts', function ( $parts ) {
    if ( is_404() ) { $parts['title'] = 'Seite nicht gefunden'; }
    elseif ( is_search() ) { $parts['title'] = 'Werkstattsuche: ' . get_search_query( false ); }
    return $parts;
}, 30 );
