<?php
/**
 * Standortseiten und Kombinationen aus Leistung, Marke und Standort.
 *
 * @package FindeWerkstatt
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FindeWerkstatt_Programmatic_SEO {

    private static $invalid_request = false;

    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_pseo_rewrite_rules' ), 10 );
        add_filter( 'query_vars', array( __CLASS__, 'register_query_vars' ) );
        add_action( 'parse_request', array( __CLASS__, 'parse_request_interceptor' ) );
        add_action( 'pre_get_posts', array( __CLASS__, 'filter_pseo_queries' ), 20 );
        add_filter( 'template_include', array( __CLASS__, 'template_include_handler' ) );
        add_filter( 'document_title_parts', array( __CLASS__, 'filter_document_title' ) );
        add_filter( 'redirect_canonical', array( __CLASS__, 'filter_canonical_redirect' ) );
        add_action( 'admin_menu', array( __CLASS__, 'register_admin_menu' ) );
    }

    public static function register_pseo_rewrite_rules() {
        foreach ( array( 'service' => 'service_type', 'marke' => 'car_brand' ) as $base => $taxonomy ) {
            add_rewrite_rule(
                '^' . $base . '/([^/]+)/in/([^/]+)/page/([1-9][0-9]*)/?$',
                'index.php?' . $taxonomy . '=$matches[1]&fw_pseo_location=$matches[2]&paged=$matches[3]',
                'top'
            );
            add_rewrite_rule(
                '^' . $base . '/([^/]+)/in/([^/]+)/?$',
                'index.php?' . $taxonomy . '=$matches[1]&fw_pseo_location=$matches[2]',
                'top'
            );
        }
        // Hierarchische Stadtseiten einschließlich Pagination übernimmt die Taxonomie.
    }

    public static function register_query_vars( $vars ) {
        $vars[] = 'fw_pseo_location';
        return $vars;
    }

    private static function reject_request( $wp ) {
        self::$invalid_request = true;
        $wp->query_vars = array( 'error' => '404' );
    }

    /**
     * Verarbeitet gültige URLs auch mit älteren gespeicherten Rewrite-Regeln.
     * Unbekannte Orte und falsche Elternketten bleiben Fehlerseiten.
     */
    public static function parse_request_interceptor( $wp ) {
        foreach ( array( 'fw_pseo_location', 'service_type', 'car_brand', 'mechanic_city' ) as $key ) {
            if ( isset( $wp->query_vars[ $key ] ) && ! is_string( $wp->query_vars[ $key ] ) ) {
                self::reject_request( $wp );
                return;
            }
        }
        if ( ! is_string( $wp->request ) ) {
            self::reject_request( $wp );
            return;
        }
        $path = trim( $wp->request, '/' );
        if ( preg_match( '#^(service|marke)/([^/]+)/in/([^/]+)(?:/page/([1-9][0-9]*))?/?$#', $path, $matches ) ) {
            $taxonomy = 'service' === $matches[1] ? 'service_type' : 'car_brand';
            $term = get_term_by( 'slug', sanitize_title( $matches[2] ), $taxonomy );
            $location = get_term_by( 'slug', sanitize_title( $matches[3] ), 'mechanic_city' );
            if ( ! $term || ! $location || is_wp_error( $term ) || is_wp_error( $location ) ) {
                self::reject_request( $wp );
                return;
            }
            $wp->query_vars = array(
                $taxonomy          => $term->slug,
                'fw_pseo_location' => $location->slug,
                'post_type'        => 'mechanic',
                'paged'            => isset( $matches[4] ) ? (int) $matches[4] : 1,
            );
            return;
        }

        if ( 0 === strpos( $path, 'stadt/' ) ) {
            $term_path = substr( $path, 6 );
            $paged = 1;
            $feed = '';
            if ( preg_match( '#/page/([1-9][0-9]*)$#', $term_path, $matches ) ) {
                $paged = (int) $matches[1];
                $term_path = substr( $term_path, 0, -strlen( $matches[0] ) );
            } elseif ( preg_match( '#/(?:feed/)?(feed|rdf|rss|rss2|atom)$#', $term_path, $matches ) ) {
                $feed = $matches[1];
                $term_path = substr( $term_path, 0, -strlen( $matches[0] ) );
            }

            $parts = explode( '/', $term_path );
            $parent_id = 0;
            $term = null;
            foreach ( $parts as $slug ) {
                $term = get_term_by( 'slug', sanitize_title( $slug ), 'mechanic_city' );
                if ( ! $term || is_wp_error( $term ) || (int) $term->parent !== $parent_id ) {
                    self::reject_request( $wp );
                    return;
                }
                $parent_id = (int) $term->term_id;
            }

            // Alte Geo-Regeln dürfen keine Blog-Abfrage oder falschen Standort erzeugen.
            unset( $wp->query_vars['error'], $wp->query_vars['fw_pseo_state'], $wp->query_vars['fw_pseo_district'], $wp->query_vars['fw_pseo_location'], $wp->query_vars['post_type'] );
            $wp->query_vars['mechanic_city'] = $term->slug;
            $wp->query_vars['paged'] = $paged;
            if ( $feed ) {
                $wp->query_vars['feed'] = $feed;
            }
        } elseif ( preg_match( '#^(service|marke)/[^/]+/in/#', $path ) ) {
            self::reject_request( $wp );
        }
    }

    public static function get_query_term( $taxonomy, $query = null ) {
        if ( null === $query ) {
            global $wp_query;
            $query = $wp_query;
        }
        if ( ! $query ) {
            return null;
        }
        $slug = $query->get( $taxonomy );
        if ( ! is_string( $slug ) ) {
            return null;
        }
        if ( '' !== $slug ) {
            $term = get_term_by( 'slug', sanitize_title( basename( $slug ) ), $taxonomy );
            return $term && ! is_wp_error( $term ) ? $term : null;
        }
        $term = $query->get_queried_object();
        return $term instanceof WP_Term && $taxonomy === $term->taxonomy ? $term : null;
    }

    public static function get_location_term( $query = null ) {
        if ( null === $query ) {
            global $wp_query;
            $query = $wp_query;
        }
        $location_slug = $query ? $query->get( 'fw_pseo_location' ) : '';
        if ( ! is_string( $location_slug ) ) {
            return null;
        }
        if ( '' !== $location_slug ) {
            $term = get_term_by( 'slug', sanitize_title( $location_slug ), 'mechanic_city' );
            return $term && ! is_wp_error( $term ) ? $term : null;
        }
        return self::get_query_term( 'mechanic_city', $query );
    }

    public static function filter_pseo_queries( $query ) {
        if ( is_admin() || ! $query->is_main_query() || $query->is_404() ) {
            return;
        }
        foreach ( array( 'fw_pseo_location', 'service_type', 'car_brand', 'mechanic_city' ) as $key ) {
            if ( ! is_string( $query->get( $key ) ) ) {
                self::$invalid_request = true;
                $query->set_404();
                $query->set( 'post__in', array( 0 ) );
                return;
            }
        }
        $location_slug = $query->get( 'fw_pseo_location' );
        if ( ! $location_slug ) {
            if ( $query->is_tax( array( 'mechanic_city', 'service_type', 'car_brand' ) ) ) {
                $query->set( 'posts_per_page', 12 );
            }
            return;
        }

        $taxonomy = $query->get( 'service_type' ) ? 'service_type' : ( $query->get( 'car_brand' ) ? 'car_brand' : '' );
        $term = $taxonomy ? self::get_query_term( $taxonomy, $query ) : null;
        $location = self::get_location_term( $query );
        if ( ! $term || ! $location ) {
            self::$invalid_request = true;
            $query->set_404();
            $query->set( 'post__in', array( 0 ) );
            return;
        }

        $tax_query = array(
            'relation' => 'AND',
            array( 'taxonomy' => $taxonomy, 'field' => 'term_id', 'terms' => $term->term_id, 'include_children' => true ),
            array( 'taxonomy' => 'mechanic_city', 'field' => 'term_id', 'terms' => $location->term_id, 'include_children' => true ),
        );
        $existing = $query->get( 'tax_query' );
        if ( is_array( $existing ) && ! empty( $existing ) ) {
            $tax_query[] = $existing;
        }
        $query->set( 'tax_query', $tax_query );
        $query->set( 'post_type', 'mechanic' );
        $query->set( 'posts_per_page', 12 );
    }

    public static function template_include_handler( $template ) {
        if ( is_404() || ! is_tax() ) {
            return $template;
        }
        foreach ( array( 'service_type', 'car_brand', 'mechanic_city' ) as $taxonomy ) {
            if ( self::get_query_term( $taxonomy ) ) {
                return get_template_directory() . '/taxonomy-' . $taxonomy . '.php';
            }
        }
        return $template;
    }

    public static function filter_document_title( $parts ) {
        if ( ! is_404() && get_query_var( 'fw_pseo_location' ) ) {
            $location = self::get_location_term();
            $term = self::get_query_term( 'service_type' ) ?: self::get_query_term( 'car_brand' );
            if ( $location && $term ) {
                $parts['title'] = findewerkstatt_directory_term_title( $term, $location );
            }
        }
        return $parts;
    }

    public static function filter_canonical_redirect( $redirect ) {
        return self::$invalid_request || get_query_var( 'fw_pseo_location' ) ? false : $redirect;
    }

    public static function get_combination_url( $term, $location, $paged = 1 ) {
        $base = 'car_brand' === $term->taxonomy ? 'marke' : 'service';
        $path = '/' . $base . '/' . $term->slug . '/in/' . $location->slug;
        if ( $paged > 1 ) {
            $path .= '/page/' . (int) $paged;
        }
        return home_url( user_trailingslashit( $path ) );
    }

    public static function render_workshop_cards( $query ) {
        echo '<div class="fw-workshops-grid">';
        while ( $query->have_posts() ) {
            $query->the_post();
            get_template_part( 'template-parts/workshop-card' );
        }
        wp_reset_postdata();
        echo '</div>';
    }
    public static function register_admin_menu() {
        add_submenu_page( 'edit.php?post_type=mechanic', 'Regionen ergänzen', 'Regionen ergänzen', 'manage_options', 'fw-geo-generator', array( __CLASS__, 'render_admin_generator_page' ) );
    }

    public static function render_admin_generator_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Sie haben keine Berechtigung für diese Seite.', 'findewerkstatt' ) );
        }
        $created_count = 0;
        $errors = 0;
        $migration_error = '';
        $submitted = isset( $_POST['seed_all_districts'] );
        if ( $submitted && check_admin_referer( 'fw_seed_districts_action' ) ) {
            $states = FindeWerkstatt_Germany_Geo_Hierarchy::get_states();
            $districts = FindeWerkstatt_Germany_Geo_Hierarchy::get_districts_by_state();
            foreach ( $states as $state_slug => $state_data ) {
                $state = term_exists( $state_slug, 'mechanic_city' );
                if ( ! $state ) {
                    $state = wp_insert_term( $state_data['name'], 'mechanic_city', array( 'slug' => $state_slug ) );
                    if ( is_wp_error( $state ) ) {
                        ++$errors;
                        continue;
                    }
                    ++$created_count;
                }
                $parent_id = is_array( $state ) ? (int) $state['term_id'] : (int) $state;
                foreach ( $districts[ $state_slug ] ?? array() as $slug => $name ) {
                    $canonical_slug = findewerkstatt_normalize_city_slug( $slug );
                    if ( ! term_exists( $canonical_slug, 'mechanic_city' ) ) {
                        $result = wp_insert_term( $name, 'mechanic_city', array( 'slug' => $slug, 'parent' => $parent_id ) );
                        is_wp_error( $result ) ? ++$errors : ++$created_count;
                    }
                }
            }
            $merge_result = findewerkstatt_merge_city_terms();
            if ( is_wp_error( $merge_result ) ) {
                $migration_error = $merge_result->get_error_message();
            } else {
                flush_rewrite_rules( false );
            }
        }
        ?>
        <div class="wrap">
            <h1>Regionen im Werkstattverzeichnis ergänzen</h1>
            <p>Ergänzt Bundesländer und Regionen aus der hinterlegten Liste. Bestehende Einträge bleiben erhalten. Es werden keine Werkstattprofile angelegt.</p>
            <?php if ( $submitted ) : ?>
                <div class="notice <?php echo $migration_error ? 'notice-error' : ( $errors ? 'notice-warning' : 'notice-success' ); ?>"><p><?php echo esc_html( $created_count . ' Regionen ergänzt.' . ( $errors ? ' Bei ' . $errors . ' Einträgen ist ein Fehler aufgetreten.' : '' ) . ( $migration_error ? ' Die Zusammenführung der Stadtbegriffe wurde abgebrochen: ' . $migration_error : '' ) ); ?></p></div>
            <?php endif; ?>
            <form method="post">
                <?php wp_nonce_field( 'fw_seed_districts_action' ); ?>
                <button type="submit" name="seed_all_districts" value="1" class="button button-primary">Fehlende Regionen ergänzen</button>
            </form>
        </div>
        <?php
    }
}

FindeWerkstatt_Programmatic_SEO::init();


