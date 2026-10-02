<?php
/** Indexierung aller Ortsverzeichnisse anhand veröffentlichter Betriebe. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class FindeWerkstatt_City_Indexing {
    private static $empty_city_ids = null;

    public static function init() {
        add_filter( 'wp_robots', array( __CLASS__, 'filter_robots' ), 20 );
        add_filter( 'wp_sitemaps_taxonomies_query_args', array( __CLASS__, 'filter_sitemap_query_args' ), 20, 2 );
        add_action( 'clean_term_cache', array( __CLASS__, 'clear_request_cache' ) );
    }

    private static function is_local_directory( $term ) {
        // Bundesländer und Stadtstaaten auf oberster Ebene dienen der Ortsnavigation.
        return $term && 'mechanic_city' === $term->taxonomy && (int) $term->parent > 0;
    }

    public static function filter_robots( $robots ) {
        if ( is_404() || ! is_tax() ) { return $robots; }
        global $wp_query;
        $city = FindeWerkstatt_Programmatic_SEO::get_location_term( $wp_query );
        if ( self::is_local_directory( $city ) && 0 === (int) $wp_query->found_posts ) {
            unset( $robots['index'] );
            $robots['noindex'] = true;
            // Globale Datenschutz-/Staging-Einstellungen bleiben maßgeblich.
            if ( get_option( 'blog_public' ) && empty( $robots['nofollow'] ) ) {
                $robots['follow'] = true;
            }
        }
        return $robots;
    }

    public static function clear_request_cache() {
        self::$empty_city_ids = null;
    }

    /** Ein Batch lädt Begriffe; Belegung wird zu Vorfahren weitergegeben. */
    private static function get_empty_city_ids() {
        if ( null !== self::$empty_city_ids ) { return self::$empty_city_ids; }
        $terms = get_terms( array(
            'taxonomy' => 'mechanic_city',
            'hide_empty' => false,
            'hierarchical' => false,
            'update_term_meta_cache' => false,
        ) );
        self::$empty_city_ids = array();
        if ( is_wp_error( $terms ) ) { return self::$empty_city_ids; }
        $by_id = array();
        $occupied = array();
        foreach ( $terms as $term ) { $by_id[ (int) $term->term_id ] = $term; }
        foreach ( $terms as $term ) {
            if ( (int) $term->count < 1 ) { continue; }
            $id = (int) $term->term_id;
            while ( $id && isset( $by_id[ $id ] ) && ! isset( $occupied[ $id ] ) ) {
                $occupied[ $id ] = true;
                $id = (int) $by_id[ $id ]->parent;
            }
        }
        foreach ( $terms as $term ) {
            if ( self::is_local_directory( $term ) && empty( $occupied[ (int) $term->term_id ] ) ) {
                self::$empty_city_ids[] = (int) $term->term_id;
            }
        }
        return self::$empty_city_ids;
    }

    public static function filter_sitemap_query_args( $args, $taxonomy ) {
        if ( 'mechanic_city' !== $taxonomy ) { return $args; }
        $excluded = self::get_empty_city_ids();
        $args['hide_empty'] = false; // Bundesländer bleiben als Ortsnavigation erreichbar.
        $args['exclude'] = array_values( array_unique( array_merge( array_map( 'intval', (array) ( $args['exclude'] ?? array() ) ), $excluded ) ) );
        // WP_Term_Query ignoriert exclude, sobald include gesetzt ist.
        if ( ! empty( $args['include'] ) ) {
            $included = array_diff( wp_parse_id_list( $args['include'] ), $args['exclude'] );
            $args['include'] = $included ? array_values( $included ) : array( 0 );
        }
        return $args;
    }
}

FindeWerkstatt_City_Indexing::init();
