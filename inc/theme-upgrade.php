<?php
/** Reversible migration from the former Turkish directory and version 2.0. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function findewerkstatt_legacy_redirects() {
    return array(
        'usta-ekle' => '/werkstatt-anmelden/', 'iletisim' => '/kontakt/',
        'hakkimizda' => '/ueber-uns/', 'gizlilik-politikasi' => '/datenschutz/',
        'kvkk-aydinlatma-metni' => '/datenschutz/', 'cerez-politikasi' => '/datenschutz/',
        'kullanim-kosullari' => '/agb/', 'ilan-verme-kurallari' => '/agb/',
        'iller' => '/#bundeslaender', 'hizmetler' => '/#leistungen',
        'nobetci-oto-tamirciler' => '/service/abschleppdienst-pannenhilfe/',
        'oto-cekici-yol-yardim' => '/service/abschleppdienst-pannenhilfe/',
    );
}

function findewerkstatt_legacy_redirect_url( $target ) {
    if ( str_starts_with( $target, '/#' ) ) { return home_url( $target ); }
    if ( preg_match( '#^/service/([^/]+)/$#', $target, $matches ) ) {
        return findewerkstatt_term_url( $matches[1], 'service_type' );
    }
    return findewerkstatt_page_url( trim( $target, '/' ) );
}

function findewerkstatt_upgrade_theme() {
    if ( ! current_user_can( 'manage_options' ) ) { return false; }
    if ( get_option( 'findewerkstatt_setup_version' ) === '2.1.0' ) { return true; }

    if ( ! get_option( 'findewerkstatt_setup_snapshot' ) ) {
        $snapshot = array( 'created_at' => gmdate( 'c' ), 'options' => array() );
        foreach ( array( 'WPLANG', 'timezone_string', 'blogname', 'blogdescription', 'show_on_front', 'page_on_front', 'page_for_posts', 'wp_page_for_privacy_policy', 'date_format', 'time_format', 'findewerkstatt_sample_workshops_v1', 'findewerkstatt_site_details' ) as $key ) {
            $snapshot['options'][ $key ] = get_option( $key, null );
        }
        add_option( 'findewerkstatt_setup_snapshot', $snapshot, '', false );
        if ( ! get_option( 'findewerkstatt_setup_snapshot' ) ) { return false; }
    }

    FindeWerkstatt_CPT_Taxonomies::register_post_type_and_taxonomies();
    FindeWerkstatt_CPT_Taxonomies::check_and_seed();
    $service_result = findewerkstatt_sync_service_terms();
    if ( is_wp_error( $service_result ) ) { update_option( 'findewerkstatt_setup_error', $service_result->get_error_message(), false ); return false; }
    $pages = array(
        'startseite' => array( 'Startseite', '' ),
        'werkstatt-anmelden' => array( 'Werkstatt eintragen', 'page-werkstatt-anmelden.php' ),
        'kontakt' => array( 'Kontakt', 'page-kontakt.php' ),
        'impressum' => array( 'Impressum', 'page-impressum.php' ),
        'datenschutz' => array( 'Datenschutzerklärung', 'page-datenschutz.php' ),
        'agb' => array( 'Nutzungsbedingungen', 'page-agb.php' ),
        'ueber-uns' => array( 'Über FindeWerkstatt.de', 'page-ueber-uns.php' ),
    );
    $page_ids = array();
    foreach ( $pages as $slug => $data ) {
        $page = get_page_by_path( $slug );
        if ( ! $page ) {
            $id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $data[0] ), true );
            if ( is_wp_error( $id ) ) { return false; }
        } else {
            $id = $page->ID;
            if ( $page->post_title !== $data[0] ) {
                if ( ! metadata_exists( 'post', $id, '_fw_migration_original_title' ) ) { update_post_meta( $id, '_fw_migration_original_title', $page->post_title ); }
                $result = wp_update_post( array( 'ID' => $id, 'post_title' => $data[0] ), true );
                if ( is_wp_error( $result ) ) { return false; }
            }
            if ( $page->post_status !== 'publish' ) {
                update_post_meta( $id, '_fw_migration_original_status', $page->post_status );
                $result = wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ), true );
                if ( is_wp_error( $result ) ) { return false; }
            }
        }
        if ( $data[1] ) {
            if ( ! metadata_exists( 'post', $id, '_fw_migration_original_template' ) ) { update_post_meta( $id, '_fw_migration_original_template', get_post_meta( $id, '_wp_page_template', true ) ); }
            update_post_meta( $id, '_wp_page_template', $data[1] );
        }
        $page_ids[ $slug ] = $id;
    }

    // Preserve original entries as drafts so the migration can be reversed.
    $legacy_slugs = array_merge( array_keys( findewerkstatt_legacy_redirects() ), array(
        'paketler', 'sss', 'blog', 'kaza-ani-asistani-ve-tutanak', 'kronik-arizalar-ve-arac-rehberi',
        'akaryakit-fiyatlari-ve-rota-hesaplama', 'ariza-kodlari-ve-gosterge-lambalari',
        'profil', 'kayit', 'giris', 'elektrikli-sarj-istasyonlari', 'ariza-tespiti', 'sample-page',
    ) );
    foreach ( $legacy_slugs as $slug ) {
        $page = get_page_by_path( $slug );
        if ( $page && $page->post_status === 'publish' ) {
            update_post_meta( $page->ID, '_fw_migration_original_status', 'publish' );
            wp_update_post( array( 'ID' => $page->ID, 'post_status' => 'draft' ) );
        }
    }
    $sample_post = get_page_by_path( 'hello-world', OBJECT, 'post' );
    if ( $sample_post && $sample_post->post_title === 'Hello world!' && $sample_post->post_status === 'publish' ) {
        update_post_meta( $sample_post->ID, '_fw_migration_original_status', 'publish' );
        wp_update_post( array( 'ID' => $sample_post->ID, 'post_status' => 'draft' ) );
    }
    $demo_titles = array(
        'Autowerkstatt Spree-Meister Berlin' => '+49 30 20987654',
        'Hanseatische Kfz-Technik & 24h Pannenhilfe Hamburg' => '+49 40 76543210',
        'Bayerische Motoren & Fahrwerktechnik München' => '+49 89 54321987',
        'Rheinland Autoglas & Smart Repair Köln' => '+49 221 44556677',
        'Mainhattan Elektro- & Kfz-Diagnosezentrum Frankfurt' => '+49 69 77889900',
        'Schwaben-Power Werkstatt & Reifenservice Stuttgart' => '+49 711 33445566',
    );
    $mechanics = get_posts( array( 'post_type' => 'mechanic', 'post_status' => array( 'publish', 'draft', 'pending' ), 'numberposts' => -1 ) );
    foreach ( $mechanics as $mechanic ) {
        if ( isset( $demo_titles[ $mechanic->post_title ] ) && findewerkstatt_normalize_phone( get_post_meta( $mechanic->ID, '_mechanic_phone', true ) ) === findewerkstatt_normalize_phone( $demo_titles[ $mechanic->post_title ] ) ) {
            update_post_meta( $mechanic->ID, '_fw_migration_original_status', $mechanic->post_status );
            update_post_meta( $mechanic->ID, '_fw_migration_original_title', $mechanic->post_title );
            update_post_meta( $mechanic->ID, '_fw_migration_original_meta', get_post_meta( $mechanic->ID ) );
            update_post_meta( $mechanic->ID, '_findewerkstatt_demo', 'yes' );
            foreach ( array( '_mechanic_phone', '_mechanic_whatsapp', '_mechanic_rating_avg', '_mechanic_rating_count', '_mechanic_is_verified', '_mechanic_is_master' ) as $key ) { delete_post_meta( $mechanic->ID, $key ); }
            wp_update_post( array( 'ID' => $mechanic->ID, 'post_status' => 'draft', 'post_title' => '[DEMO] ' . $mechanic->post_title ) );
        }
    }
    $city_aliases = get_option( 'findewerkstatt_city_aliases', array() );
    foreach ( array( 'anbach' => 'ansbach', 'lk-ortenaubreis' => 'lk-ortenaukreis', 'lk-anholt-bitterfeld' => 'lk-anhalt-bitterfeld' ) as $old => $new ) {
        $term = get_term_by( 'slug', $old, 'mechanic_city' );
        if ( $term && ! get_term_by( 'slug', $new, 'mechanic_city' ) ) {
            update_term_meta( $term->term_id, '_fw_migration_original_slug', $old );
            $result = wp_update_term( $term->term_id, 'mechanic_city', array( 'slug' => $new ) );
            if ( is_wp_error( $result ) ) { update_option( 'findewerkstatt_setup_error', $result->get_error_message(), false ); return false; }
        }
        $canonical = get_term_by( 'slug', $new, 'mechanic_city' );
        if ( $canonical && get_term_meta( $canonical->term_id, '_fw_migration_original_slug', true ) === $old ) { $city_aliases[ $old ] = $new; }
    }
    update_option( 'findewerkstatt_city_aliases', $city_aliases, false );
    $city_result = findewerkstatt_merge_city_terms();
    if ( is_wp_error( $city_result ) ) {
        update_option( 'findewerkstatt_setup_error', $city_result->get_error_message(), false );
        return false;
    }
    update_option( 'findewerkstatt_city_merge_result', $city_result, false );
    update_option( 'findewerkstatt_sample_workshops_v1', 1 );
    update_option( 'WPLANG', 'de_DE' );
    update_option( 'timezone_string', 'Europe/Berlin' );
    update_option( 'blogname', 'FindeWerkstatt.de' );
    update_option( 'blogdescription', 'Kfz-Werkstätten, Autoservices und Pannenhilfe in Deutschland finden' );
    update_option( 'show_on_front', 'page' );
    update_option( 'page_on_front', $page_ids['startseite'] );
    update_option( 'page_for_posts', 0 );
    update_option( 'wp_page_for_privacy_policy', $page_ids['datenschutz'] );
    update_option( 'date_format', 'd.m.Y' );
    update_option( 'time_format', 'H:i' );
    if ( ! get_option( 'findewerkstatt_site_details' ) ) { add_option( 'findewerkstatt_site_details', findewerkstatt_get_site_details() ); }
    flush_rewrite_rules( false );
    update_option( 'findewerkstatt_setup_version', '2.1.0' );
    delete_option( 'findewerkstatt_setup_error' );
    return true;
}
add_action( 'admin_init', 'findewerkstatt_upgrade_theme', 20 );
add_action( 'after_switch_theme', 'findewerkstatt_upgrade_theme', 20 );

add_action( 'admin_notices', function () {
    $error = get_option( 'findewerkstatt_setup_error' );
    if ( $error && current_user_can( 'manage_options' ) ) { echo '<div class="notice notice-error"><p>Die Einrichtung konnte nicht abgeschlossen werden: ' . esc_html( $error ) . '</p></div>'; }
} );

add_action( 'template_redirect', function () {
    if ( ! is_404() || ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) !== 'GET' ) { return; }
    $path = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
    $home_path = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
    if ( $home_path && str_starts_with( $path, $home_path . '/' ) ) { $path = substr( $path, strlen( $home_path ) + 1 ); }
    $redirects = findewerkstatt_legacy_redirects();
    if ( isset( $redirects[ $path ] ) ) { wp_safe_redirect( findewerkstatt_legacy_redirect_url( $redirects[ $path ] ), 301 ); exit; }
}, 5 );

/** Distinguish districts from cities without changing existing taxonomy identities. */
function findewerkstatt_location_label( $term ) {
    if ( str_starts_with( $term->slug, 'lk-' ) && ! preg_match( '/kreis|region|regionalverband/iu', $term->name ) ) {
        return 'Landkreis ' . $term->name;
    }
    return $term->name;
}

/** Existing taxonomy terms supply correctly spelt city names and hierarchical links. */
function findewerkstatt_location_options( $selected = '' ) {
    static $terms = null;
    if ( $terms === null ) {
        $terms = get_terms( array( 'taxonomy' => 'mechanic_city', 'hide_empty' => false, 'orderby' => 'name' ) );
    }
    if ( is_wp_error( $terms ) ) { return ''; }
    $children = array();
    foreach ( $terms as $term ) { $children[ (int) $term->parent ][] = $term; }
    $html = '';
    $render = function ( $parent, $depth ) use ( &$render, $children, $selected ) {
        $output = '';
        foreach ( $children[ $parent ] ?? array() as $term ) {
            $output .= '<option value="' . esc_attr( $term->slug ) . '"' . selected( $selected, $term->slug, false ) . '>' . esc_html( str_repeat( '— ', $depth ) . findewerkstatt_location_label( $term ) ) . '</option>';
            $output .= $render( (int) $term->term_id, $depth + 1 );
        }
        return $output;
    };
    $html = $render( 0, 0 );
    return $html;
}

function findewerkstatt_term_url( $slug, $taxonomy ) {
    if ( 'mechanic_city' === $taxonomy ) { $slug = findewerkstatt_normalize_city_slug( $slug ); }
    $term = get_term_by( 'slug', $slug, $taxonomy );
    $url = $term && ! is_wp_error( $term ) ? get_term_link( $term ) : get_post_type_archive_link( 'mechanic' );
    return is_wp_error( $url ) ? get_post_type_archive_link( 'mechanic' ) : $url;
}

/** WordPress page permalinks also work without pretty permalink settings. */
function findewerkstatt_page_url( $slug ) {
    static $urls = array();
    $slug = sanitize_title( $slug );
    $lang = function_exists( 'findewerkstatt_language' ) ? findewerkstatt_language() : 'de';
    $cache_key = $lang . ':' . $slug;
    if ( ! isset( $urls[ $cache_key ] ) ) {
        $page = get_page_by_path( $slug );
        // A renamed page keeps its assigned template and its actual WordPress address.
        $templates = array( 'werkstatt-anmelden', 'kontakt', 'ueber-uns', 'impressum', 'datenschutz', 'agb', 'pakete', 'mein-konto' );
        if ( ( ! $page || 'publish' !== $page->post_status ) && in_array( $slug, $templates, true ) ) {
            $matches = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'numberposts' => 1,
                'meta_key' => '_wp_page_template', 'meta_value' => 'page-' . $slug . '.php', 'orderby' => 'ID', 'order' => 'ASC' ) );
            $page = $matches ? $matches[0] : null;
        }
        $urls[ $cache_key ] = $page && $page->post_status === 'publish' ? get_permalink( $page ) : home_url( '/' );
    }
    return $urls[ $cache_key ];
}

/** Refresh the shipped service terms; keep original labels for recovery. */
function findewerkstatt_sync_service_terms() {
    if ( ! current_user_can( 'manage_options' ) ) { return new WP_Error( 'fw_service_forbidden', 'Administratorrechte erforderlich.' ); }
    $changed = 0;
    foreach ( FindeWerkstatt_German_Data::get_categories() as $slug => $data ) {
        $term = get_term_by( 'slug', $slug, 'service_type' );
        if ( ! $term || ( $term->name === $data['name'] && $term->description === $data['desc'] ) ) { continue; }
        if ( ! metadata_exists( 'term', $term->term_id, '_fw_migration_original_label' ) ) { update_term_meta( $term->term_id, '_fw_migration_original_label', array( 'name' => $term->name, 'description' => $term->description ) ); }
        $result = wp_update_term( $term->term_id, 'service_type', array( 'name' => $data['name'], 'description' => $data['desc'] ) );
        if ( is_wp_error( $result ) ) { return $result; }
        ++$changed;
    }
    return $changed;
}

/**
 * Stellt sicher, dass Kfz-Meisterbetriebe und freie Werkstätten die Standard-Fachleistungen
 * (Bremsenservice & Fahrwerk, Klimaservice, Kfz-Elektrik, Motorinstandsetzung) zugeordnet haben,
 * sodass Kategorielinks und Filterseiten niemals leer laufen.
 */
function findewerkstatt_ensure_workshop_service_taxonomies() {
    $done = get_option( 'findewerkstatt_services_backfilled_v2' );
    if ( $done ) {
        return;
    }

    $bremsen_term = get_term_by( 'slug', 'bremsenservice-fahrwerk', 'service_type' );
    // Falls bremsenservice-fahrwerk bereits Betriebe hat (> 50), als erledigt markieren
    if ( $bremsen_term && (int) $bremsen_term->count > 50 ) {
        update_option( 'findewerkstatt_services_backfilled_v2', 1, false );
        return;
    }

    // Grundlegende Dienstleistungen, die jede Freie Werkstatt / Kfz-Werkstatt anbietet
    $core_slugs = array(
        'bremsenservice-fahrwerk',
        'klimaservice-standheizung',
        'kfz-elektrik-elektronik',
        'motor-getriebeinstandsetzung',
    );

    $term_ids = array();
    foreach ( $core_slugs as $c_slug ) {
        $t = get_term_by( 'slug', $c_slug, 'service_type' );
        if ( ! $t && function_exists( 'wp_insert_term' ) ) {
            $cat_data = FindeWerkstatt_German_Data::get_categories()[ $c_slug ] ?? null;
            if ( $cat_data ) {
                $ins = wp_insert_term( $cat_data['name'], 'service_type', array( 'slug' => $c_slug, 'description' => $cat_data['desc'] ) );
                if ( ! is_wp_error( $ins ) && isset( $ins['term_id'] ) ) {
                    $term_ids[] = (int) $ins['term_id'];
                }
            }
        } elseif ( $t && ! is_wp_error( $t ) ) {
            $term_ids[] = (int) $t->term_id;
        }
    }

    if ( empty( $term_ids ) ) {
        return;
    }

    // Alle Betriebe mit freie-werkstatt oder kfz-werkstatt holen
    $freie = get_term_by( 'slug', 'freie-werkstatt', 'service_type' );
    $kfz   = get_term_by( 'slug', 'kfz-werkstatt', 'service_type' );
    $source_terms = array_filter( array( $freie ? $freie->term_id : 0, $kfz ? $kfz->term_id : 0 ) );

    $args = array(
        'post_type'      => 'mechanic',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
    );

    if ( ! empty( $source_terms ) ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'service_type',
                'field'    => 'term_id',
                'terms'    => $source_terms,
            ),
        );
    }

    $posts = get_posts( $args );
    if ( ! empty( $posts ) ) {
        foreach ( $posts as $pid ) {
            // append = true (vorhandene Services bleiben erhalten!)
            wp_set_object_terms( $pid, $term_ids, 'service_type', true );
        }

        // Zähler aktualisieren
        wp_update_term_count_now( $term_ids, 'service_type' );
    }

    update_option( 'findewerkstatt_services_backfilled_v2', 1, false );
}
add_action( 'init', 'findewerkstatt_ensure_workshop_service_taxonomies', 20 );

