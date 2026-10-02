<?php
/**
 * FindeWerkstatt.de — Custom Post Types & Taxonomien
 * 
 * 1. CPT: mechanic (Kfz-Werkstätten) -> Slug: /werkstatt/%postname%/, Archiv: /werkstaetten/
 * 2. Taxonomie: mechanic_city (Bundesländer & Städte) -> Slug: /stadt/%term%/
 * 3. Taxonomie: mechanic_district (Stadtteile) -> Slug: /stadtteil/%term%/
 * 4. Taxonomie: service_type (Dienstleistungen & Kategorien) -> Slug: /service/%term%/
 * 5. Taxonomie: car_brand (Automarken) -> Slug: /marke/%term%/
 * 
 * @package FindeWerkstatt
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FindeWerkstatt_CPT_Taxonomies {

    const OPTION_SEEDED = 'findewerkstatt_v2_seeded';

    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_post_type_and_taxonomies' ), 5 );
        add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ), 10 );
        add_action( 'after_switch_theme', array( __CLASS__, 'check_and_seed' ) );
        add_action( 'admin_init', array( __CLASS__, 'check_and_seed' ) );
    }

    public static function register_post_type_and_taxonomies() {
        // CPT: mechanic (Kfz-Werkstätten)
        $labels = array(
            'name'               => _x( 'Kfz-Werkstätten', 'Post type general name', 'findewerkstatt' ),
            'singular_name'      => _x( 'Werkstatt', 'Post type singular name', 'findewerkstatt' ),
            'menu_name'          => _x( 'Werkstätten', 'Admin Menu text', 'findewerkstatt' ),
            'name_admin_bar'     => _x( 'Werkstatt', 'Add New on Toolbar', 'findewerkstatt' ),
            'add_new'            => __( 'Neue Werkstatt eintragen', 'findewerkstatt' ),
            'add_new_item'       => __( 'Neue Werkstatt anlegen', 'findewerkstatt' ),
            'new_item'           => __( 'Neue Werkstatt', 'findewerkstatt' ),
            'edit_item'          => __( 'Werkstatt bearbeiten', 'findewerkstatt' ),
            'view_item'          => __( 'Werkstatt ansehen', 'findewerkstatt' ),
            'all_items'          => __( 'Alle Werkstätten', 'findewerkstatt' ),
            'search_items'       => __( 'Werkstatt suchen', 'findewerkstatt' ),
            'not_found'          => __( 'Keine Werkstätten gefunden.', 'findewerkstatt' ),
            'not_found_in_trash' => __( 'Keine Werkstätten im Papierkorb.', 'findewerkstatt' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'has_archive'        => 'werkstaetten',
            'rewrite'            => array( 'slug' => 'werkstatt', 'with_front' => false ),
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-wrench',
            'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'custom-fields', 'author' ),
            'show_in_rest'       => true,
        );
        register_post_type( 'mechanic', $args );

        // Taxonomie: mechanic_city (Bundesländer & Städte)
        register_taxonomy( 'mechanic_city', 'mechanic', array(
            'labels' => array(
                'name'              => __( 'Bundesländer & Städte', 'findewerkstatt' ),
                'singular_name'     => __( 'Stadt / Bundesland', 'findewerkstatt' ),
                'search_items'      => __( 'Städte durchsuchen', 'findewerkstatt' ),
                'all_items'         => __( 'Alle Städte & Bundesländer', 'findewerkstatt' ),
                'parent_item'       => __( 'Übergeordnetes Bundesland', 'findewerkstatt' ),
                'parent_item_colon' => __( 'Übergeordnetes Bundesland:', 'findewerkstatt' ),
                'edit_item'         => __( 'Stadt bearbeiten', 'findewerkstatt' ),
                'add_new_item'      => __( 'Neue Stadt / Bundesland hinzufügen', 'findewerkstatt' ),
            ),
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'stadt', 'with_front' => false, 'hierarchical' => true ),
            'show_in_rest'      => true,
        ) );

        // Taxonomie: mechanic_district (Stadtteile & Bezirke)
        register_taxonomy( 'mechanic_district', 'mechanic', array(
            'labels' => array(
                'name'              => __( 'Stadtteile & Bezirke', 'findewerkstatt' ),
                'singular_name'     => __( 'Stadtteil', 'findewerkstatt' ),
                'search_items'      => __( 'Stadtteil suchen', 'findewerkstatt' ),
                'all_items'         => __( 'Alle Stadtteile', 'findewerkstatt' ),
                'edit_item'         => __( 'Stadtteil bearbeiten', 'findewerkstatt' ),
                'add_new_item'      => __( 'Neuen Stadtteil hinzufügen', 'findewerkstatt' ),
            ),
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'stadtteil', 'with_front' => false ),
            'show_in_rest'      => true,
        ) );

        // Taxonomie: service_type (Dienstleistungen & Kategorien)
        register_taxonomy( 'service_type', 'mechanic', array(
            'labels' => array(
                'name'              => __( 'Dienstleistungen & Kategorien', 'findewerkstatt' ),
                'singular_name'     => __( 'Dienstleistung', 'findewerkstatt' ),
                'search_items'      => __( 'Leistung suchen', 'findewerkstatt' ),
                'all_items'         => __( 'Alle Leistungen', 'findewerkstatt' ),
                'edit_item'         => __( 'Leistung bearbeiten', 'findewerkstatt' ),
                'add_new_item'      => __( 'Neue Leistung hinzufügen', 'findewerkstatt' ),
            ),
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'service', 'with_front' => false ),
            'show_in_rest'      => true,
        ) );

        // Taxonomie: car_brand (Automarken)
        register_taxonomy( 'car_brand', 'mechanic', array(
            'labels' => array(
                'name'              => __( 'Automarken', 'findewerkstatt' ),
                'singular_name'     => __( 'Marke', 'findewerkstatt' ),
                'search_items'      => __( 'Marke suchen', 'findewerkstatt' ),
                'all_items'         => __( 'Alle Marken', 'findewerkstatt' ),
                'edit_item'         => __( 'Marke bearbeiten', 'findewerkstatt' ),
                'add_new_item'      => __( 'Neue Marke hinzufügen', 'findewerkstatt' ),
            ),
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'marke', 'with_front' => false ),
            'show_in_rest'      => true,
        ) );
    }

    public static function add_rewrite_rules() {
        add_rewrite_rule( '^werkstaetten/?$', 'index.php?post_type=mechanic', 'top' );
        add_rewrite_rule( '^werkstaetten/page/([0-9]+)/?$', 'index.php?post_type=mechanic&paged=$matches[1]', 'top' );
        add_rewrite_rule( '^werkstatt/([^/]+)/?$', 'index.php?mechanic=$matches[1]', 'top' );
    }

    /**
     * Automatische Initialisierung von Bundesländern, Städten, Kategorien und Marken
     */
    public static function check_and_seed() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        if ( get_option( self::OPTION_SEEDED ) ) {
            return;
        }

        require_once get_template_directory() . '/inc/german-data.php';

        // 1. Kategorien anlegen
        $categories = FindeWerkstatt_German_Data::get_categories();
        foreach ( $categories as $slug => $data ) {
            if ( ! term_exists( $slug, 'service_type' ) ) {
                wp_insert_term( $data['name'], 'service_type', array(
                    'slug'        => $slug,
                    'description' => $data['desc'],
                ) );
            }
        }

        // 2. Automarken anlegen
        $brands = FindeWerkstatt_German_Data::get_car_brands();
        foreach ( $brands as $slug => $name ) {
            if ( ! term_exists( $slug, 'car_brand' ) ) {
                wp_insert_term( $name, 'car_brand', array( 'slug' => $slug ) );
            }
        }

        // 3. 16 Bundesländer anlegen
        $bundeslaender = FindeWerkstatt_German_Data::get_bundeslaender();
        $land_term_ids = array();
        foreach ( $bundeslaender as $code => $land ) {
            $existing = term_exists( $land['slug'], 'mechanic_city' );
            if ( ! $existing ) {
                $inserted = wp_insert_term( $land['name'], 'mechanic_city', array(
                    'slug'        => $land['slug'],
                    'description' => "Kfz-Werkstätten, TÜV-Stationen und Meisterbetriebe im Bundesland {$land['name']}.",
                ) );
                if ( ! is_wp_error( $inserted ) ) {
                    $land_term_ids[ $land['slug'] ] = (int) $inserted['term_id'];
                    update_term_meta( (int) $inserted['term_id'], '_geo_lat', $land['lat'] );
                    update_term_meta( (int) $inserted['term_id'], '_geo_lng', $land['lng'] );
                    update_term_meta( (int) $inserted['term_id'], '_iso_code', $code );
                }
            } else {
                $land_term_ids[ $land['slug'] ] = is_array( $existing ) ? (int) $existing['term_id'] : (int) $existing;
            }
        }

        // 4. Top Städte anlegen
        $cities = FindeWerkstatt_German_Data::get_top_cities();
        foreach ( $cities as $slug => $city ) {
            if ( ! term_exists( $slug, 'mechanic_city' ) ) {
                $parent_id = isset( $land_term_ids[ $city['land'] ] ) ? $land_term_ids[ $city['land'] ] : 0;
                $inserted = wp_insert_term( $city['name'], 'mechanic_city', array(
                    'slug'        => $slug,
                    'parent'      => $parent_id,
                    'description' => "Verzeichnis für Kfz-Werkstätten, Pannenhilfe und Autoreparatur in {$city['name']}.",
                ) );
                if ( ! is_wp_error( $inserted ) ) {
                    update_term_meta( (int) $inserted['term_id'], '_geo_lat', $city['lat'] );
                    update_term_meta( (int) $inserted['term_id'], '_geo_lng', $city['lng'] );
                }
            }
        }

        // Rewrite-Rules aktualisieren
        flush_rewrite_rules( false );
        update_option( self::OPTION_SEEDED, 1 );
    }
}

FindeWerkstatt_CPT_Taxonomies::init();
