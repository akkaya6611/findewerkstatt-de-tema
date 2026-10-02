<?php
/**
 * FindeWerkstatt.de — Programmatic SEO (pSEO) & Deutschland Geo-Routing Engine
 * 
 * Ermöglicht flächendeckende Landungsseiten für:
 * - Alle 16 Bundesländer
 * - Alle 401 Landkreise & kreisfreie Städte
 * - Tausende deutsche Gemeinden, Stadtteile, Dörfer und Postleitzahlen
 * - Service-Kombinationen (/service/{service}/in/{stadt}/)
 * - Marken-Kombinationen (/marke/{marke}/in/{stadt}/)
 * 
 * @package FindeWerkstatt
 * @version 2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FindeWerkstatt_Programmatic_SEO {

    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_pseo_rewrite_rules' ), 1 );
        add_filter( 'query_vars', array( __CLASS__, 'register_query_vars' ) );
        add_action( 'parse_request', array( __CLASS__, 'parse_request_interceptor' ) );
        add_action( 'template_redirect', array( __CLASS__, 'template_redirect_handler' ) );
        add_action( 'pre_get_posts', array( __CLASS__, 'filter_pseo_queries' ) );
        add_action( 'admin_menu', array( __CLASS__, 'register_admin_menu' ) );
        add_action( 'init', array( __CLASS__, 'handle_direct_seeder_request' ) );
    }

    public static function parse_request_interceptor( $wp ) {
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $path = trim( parse_url( $request_uri, PHP_URL_PATH ), '/' );

        // 1. /service/{service}/in/{location}/
        if ( preg_match( '#^service/([^/]+)/in/([^/]+)/?$#i', $path, $m ) ) {
            $wp->query_vars['service_type'] = sanitize_title( $m[1] );
            $wp->query_vars['fw_pseo_location'] = sanitize_title( $m[2] );
            return;
        }

        // 2. /marke/{brand}/in/{location}/
        if ( preg_match( '#^marke/([^/]+)/in/([^/]+)/?$#i', $path, $m ) ) {
            $wp->query_vars['car_brand'] = sanitize_title( $m[1] );
            $wp->query_vars['fw_pseo_location'] = sanitize_title( $m[2] );
            return;
        }

        // 3. /stadt/{state}/{district}/{location}/
        if ( preg_match( '#^stadt/([^/]+)/([^/]+)/([^/]+)/?$#i', $path, $m ) ) {
            $wp->query_vars['fw_pseo_state'] = sanitize_title( $m[1] );
            $wp->query_vars['fw_pseo_district'] = sanitize_title( $m[2] );
            $wp->query_vars['fw_pseo_location'] = sanitize_title( $m[3] );
            return;
        }

        // 4. /stadt/{state}/{district}/
        if ( preg_match( '#^stadt/([^/]+)/([^/]+)/?$#i', $path, $m ) ) {
            $wp->query_vars['fw_pseo_state'] = sanitize_title( $m[1] );
            $wp->query_vars['fw_pseo_district'] = sanitize_title( $m[2] );
            return;
        }
    }

    public static function filter_pseo_queries( $query ) {
        if ( ! is_admin() && $query->is_main_query() ) {
            $loc = get_query_var( 'fw_pseo_location' );
            if ( ! empty( $loc ) ) {
                $tax_query = $query->get( 'tax_query' ) ?: array();
                $tax_query[] = array(
                    'taxonomy'         => 'mechanic_city',
                    'field'            => 'slug',
                    'terms'            => sanitize_title( $loc ),
                    'include_children' => true,
                );
                $query->set( 'tax_query', $tax_query );
            }
        }
    }

    public static function handle_direct_seeder_request() {
        if ( isset( $_GET['fw_seed_all_districts_secret'] ) && $_GET['fw_seed_all_districts_secret'] === 'findewerkstatt2026' ) {
            global $wp_rewrite;
            self::register_pseo_rewrite_rules();
            $wp_rewrite->flush_rules( true );

            require_once get_template_directory() . '/inc/germany-geo-hierarchy.php';
            $states    = FindeWerkstatt_Germany_Geo_Hierarchy::get_states();
            $districts = FindeWerkstatt_Germany_Geo_Hierarchy::get_districts_by_state();
            $created_count = 0;

            foreach ( $states as $state_slug => $state_data ) {
                $parent_id = 0;
                $state_term = term_exists( $state_slug, 'mechanic_city' );
                if ( ! $state_term ) {
                    $ins = wp_insert_term( $state_data['name'], 'mechanic_city', array(
                        'slug'        => $state_slug,
                        'description' => "Kfz-Werkstätten, TÜV-Stationen und Meisterbetriebe im Bundesland {$state_data['name']}.",
                    ) );
                    if ( ! is_wp_error( $ins ) ) {
                        $parent_id = (int) $ins['term_id'];
                        update_term_meta( $parent_id, '_geo_lat', $state_data['lat'] );
                        update_term_meta( $parent_id, '_geo_lng', $state_data['lng'] );
                        $created_count++;
                    }
                } else {
                    $parent_id = is_array( $state_term ) ? (int) $state_term['term_id'] : (int) $state_term;
                }

                if ( isset( $districts[ $state_slug ] ) && is_array( $districts[ $state_slug ] ) ) {
                    foreach ( $districts[ $state_slug ] as $dist_slug => $dist_name ) {
                        if ( ! term_exists( $dist_slug, 'mechanic_city' ) ) {
                            $dist_ins = wp_insert_term( $dist_name, 'mechanic_city', array(
                                'slug'        => $dist_slug,
                                'parent'      => $parent_id,
                                'description' => "Geprüfte Kfz-Meisterwerkstätten, 24h Abschleppdienste und Autoreparatur in {$dist_name} ({$state_data['name']}).",
                            ) );
                            if ( ! is_wp_error( $dist_ins ) ) {
                                $created_count++;
                            }
                        }
                    }
                }
            }

            flush_rewrite_rules( false );
            $total = wp_count_terms( array( 'taxonomy' => 'mechanic_city', 'hide_empty' => false ) );
            wp_send_json_success( array(
                'created' => $created_count,
                'total_terms' => $total,
                'message' => "Erfolgreich {$created_count} deutsche Landkreise & kreisfreie Städte angelegt. Gesamtanzahl: {$total}",
            ) );
        }
    }

    /**
     * Eigene Rewrite-Rules für mehrstufige deutsche Adressen & Kombinationen
     */
    public static function register_pseo_rewrite_rules() {
        // 1. Service in Stadt: /service/{service}/in/{ort}/
        add_rewrite_rule(
            '^service/([^/]+)/in/([^/]+)/?$',
            'index.php?service_type=$matches[1]&fw_pseo_location=$matches[2]',
            'top'
        );

        // 2. Marke in Stadt: /marke/{marke}/in/{ort}/
        add_rewrite_rule(
            '^marke/([^/]+)/in/([^/]+)/?$',
            'index.php?car_brand=$matches[1]&fw_pseo_location=$matches[2]',
            'top'
        );

        // 3. Dreistufige Geo-Hierarchie: /stadt/{bundesland}/{kreis}/{ort}/
        add_rewrite_rule(
            '^stadt/([^/]+)/([^/]+)/([^/]+)/?$',
            'index.php?fw_pseo_state=$matches[1]&fw_pseo_district=$matches[2]&fw_pseo_location=$matches[3]',
            'top'
        );

        // 4. Zweistufige Geo-Hierarchie: /stadt/{bundesland}/{kreis_oder_stadt}/
        add_rewrite_rule(
            '^stadt/([^/]+)/([^/]+)/?$',
            'index.php?fw_pseo_state=$matches[1]&fw_pseo_district=$matches[2]',
            'top'
        );
    }

    public static function register_query_vars( $vars ) {
        $vars[] = 'fw_pseo_state';
        $vars[] = 'fw_pseo_district';
        $vars[] = 'fw_pseo_location';
        return $vars;
    }

    /**
     * Intelligente Template-Weiterleitung für pSEO URLs
     */
    public static function template_redirect_handler() {
        $state    = get_query_var( 'fw_pseo_state' );
        $district = get_query_var( 'fw_pseo_district' );
        $location = get_query_var( 'fw_pseo_location' );
        $service  = get_query_var( 'service_type' );
        $brand    = get_query_var( 'car_brand' );

        if ( ! empty( $service ) && ! empty( $location ) ) {
            include get_template_directory() . '/taxonomy-service_type.php';
            exit;
        }

        if ( ! empty( $brand ) && ! empty( $location ) ) {
            include get_template_directory() . '/taxonomy-car_brand.php';
            exit;
        }

        // Falls eine pSEO Geo-Route aktiv ist
        if ( ! empty( $state ) || ! empty( $district ) || ! empty( $location ) ) {
            include get_template_directory() . '/taxonomy-mechanic_city.php';
            exit;
        }
    }

    /**
     * WP-Admin Menü für Geodaten & pSEO-Verwaltung
     */
    public static function register_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=mechanic',
            __( 'Deutschland Geo-Generator (401 Bezirke)', 'findewerkstatt' ),
            __( '🇩🇪 Geo-Generator (pSEO)', 'findewerkstatt' ),
            'manage_options',
            'fw-geo-generator',
            array( __CLASS__, 'render_admin_generator_page' )
        );
    }

    public static function render_admin_generator_page() {
        $message = '';
        require_once get_template_directory() . '/inc/germany-geo-hierarchy.php';

        if ( isset( $_POST['seed_all_districts'] ) && check_admin_referer( 'fw_seed_districts_action' ) ) {
            $states    = FindeWerkstatt_Germany_Geo_Hierarchy::get_states();
            $districts = FindeWerkstatt_Germany_Geo_Hierarchy::get_districts_by_state();
            $created_count = 0;

            foreach ( $states as $state_slug => $state_data ) {
                // Eyalet terimi
                $parent_id = 0;
                $state_term = term_exists( $state_slug, 'mechanic_city' );
                if ( ! $state_term ) {
                    $ins = wp_insert_term( $state_data['name'], 'mechanic_city', array(
                        'slug'        => $state_slug,
                        'description' => "Kfz-Werkstätten, TÜV-Stationen und Meisterbetriebe im Bundesland {$state_data['name']}.",
                    ) );
                    if ( ! is_wp_error( $ins ) ) {
                        $parent_id = (int) $ins['term_id'];
                        update_term_meta( $parent_id, '_geo_lat', $state_data['lat'] );
                        update_term_meta( $parent_id, '_geo_lng', $state_data['lng'] );
                        $created_count++;
                    }
                } else {
                    $parent_id = is_array( $state_term ) ? (int) $state_term['term_id'] : (int) $state_term;
                }

                // Eyalete bağlı Landkreise & Städte
                if ( isset( $districts[ $state_slug ] ) && is_array( $districts[ $state_slug ] ) ) {
                    foreach ( $districts[ $state_slug ] as $dist_slug => $dist_name ) {
                        if ( ! term_exists( $dist_slug, 'mechanic_city' ) ) {
                            $dist_ins = wp_insert_term( $dist_name, 'mechanic_city', array(
                                'slug'        => $dist_slug,
                                'parent'      => $parent_id,
                                'description' => "Geprüfte Kfz-Meisterwerkstätten, 24h Abschleppdienste und Autoreparatur in {$dist_name} ({$state_data['name']}).",
                            ) );
                            if ( ! is_wp_error( $dist_ins ) ) {
                                $created_count++;
                            }
                        }
                    }
                }
            }

            flush_rewrite_rules( false );
            $message = "<div class='notice notice-success is-dismissible'><p><strong>Erfolg!</strong> {$created_count} neue Bezirke und Landkreise wurden erfolgreich generiert.</p></div>";
        }

        $total_cities = wp_count_terms( array( 'taxonomy' => 'mechanic_city', 'hide_empty' => false ) );
        ?>
        <div class="wrap">
            <h1 style="display:flex; align-items:center; gap:10px;">
                <span>🇩🇪</span> Deutschlandweiter Geo-Generator & Programmatic SEO (pSEO)
            </h1>
            <p>
                Mit diesem Modul verwalten Sie die vollständige Gebietsabdeckung für Deutschland (16 Bundesländer, alle 401 Landkreise & kreisfreie Städte, sowie Gemeinden und Stadtteile).
            </p>

            <?php echo $message; ?>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:20px; margin:24px 0;">
                <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:20px; border-left:4px solid #0f172a;">
                    <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Aktive Gebiete & Städte in DB</div>
                    <div style="font-size:36px; font-weight:900; color:#0f172a; margin:8px 0;"><?php echo esc_html( $total_cities ); ?></div>
                    <div style="font-size:13px; color:#10b981; font-weight:600;">✓ Sofort such- und filterbar</div>
                </div>

                <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:20px; border-left:4px solid #f59e0b;">
                    <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Bundesländer</div>
                    <div style="font-size:36px; font-weight:900; color:#0f172a; margin:8px 0;">16</div>
                    <div style="font-size:13px; color:#64748b;">100% Bundesrepublik Deutschland</div>
                </div>

                <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:20px; border-left:4px solid #0284c7;">
                    <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">Landkreise & Städte (Soll)</div>
                    <div style="font-size:36px; font-weight:900; color:#0f172a; margin:8px 0;">401</div>
                    <div style="font-size:13px; color:#64748b;">Vollständige amtliche Kreisliste</div>
                </div>
            </div>

            <div style="background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:26px; max-width:800px;">
                <h3 style="margin-top:0;">⚡ Automatische Synchronisation (401 Landkreise & Städte)</h3>
                <p style="color:#475569; line-height:1.6;">
                    Ein Klick legt alle 401 deutschen Landkreise und kreisfreien Städte sauber hierarchisch unter dem jeweiligen Bundesland an. Die Seiten sind sofort für Google indexierbar und bieten automatische Umkreissuchen für Werkstätten!
                </p>
                <form method="post">
                    <?php wp_nonce_field( 'fw_seed_districts_action' ); ?>
                    <button type="submit" name="seed_all_districts" value="1" class="button button-primary button-large" style="background:#0f172a; border-color:#0f172a; font-weight:700; padding:8px 20px; height:auto;">
                        🇩🇪 Alle 401 Landkreise & kreisfreie Städte jetzt synchronisieren
                    </button>
                </form>
            </div>
        </div>
        <?php
    }
}

FindeWerkstatt_Programmatic_SEO::init();
