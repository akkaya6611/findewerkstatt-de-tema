<?php
/**
 * Zusammenführen bekannter Stadt-Dubletten mit Sicherung und alten URL-Aliasen.
 *
 * @package FindeWerkstatt
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Kanonischer Stadtslug; unbekannte oder zyklische Aliase bleiben unverändert. */
function findewerkstatt_normalize_city_slug( $slug ) {
    if ( ! is_string( $slug ) ) { return '__invalid__'; }
    $original = sanitize_title( $slug );
    $aliases = get_option( 'findewerkstatt_city_aliases', array() );
    if ( ! is_array( $aliases ) ) { return $original; }
    $resolved = $original;
    $seen = array();
    while ( isset( $aliases[ $resolved ] ) ) {
        if ( isset( $seen[ $resolved ] ) || ! is_string( $aliases[ $resolved ] ) ) { return $original; }
        $seen[ $resolved ] = true;
        $resolved = sanitize_title( $aliases[ $resolved ] );
    }
    if ( $resolved !== $original ) {
        $term = get_term_by( 'slug', $resolved, 'mechanic_city' );
        if ( ! $term || is_wp_error( $term ) ) { return $original; }
    }
    return $resolved;
}

/** Sicherung eines Terms einschließlich kompletter Ortszuordnungen seiner Objekte. */
function findewerkstatt_snapshot_city_term( $source, $canonical, $slug, $aliases, &$snapshots, $key_suffix = '' ) {
    $key = $source->term_id . ':' . $slug . $key_suffix;
    if ( isset( $snapshots[ $key ] ) ) { return true; }
    $objects = get_objects_in_term( $source->term_id, 'mechanic_city' );
    $children = get_terms( array( 'taxonomy' => 'mechanic_city', 'parent' => $source->term_id, 'hide_empty' => false ) );
    if ( is_wp_error( $objects ) ) { return $objects; }
    if ( is_wp_error( $children ) ) { return $children; }
    $object_terms = array();
    foreach ( $objects as $object_id ) {
        $ids = wp_get_object_terms( $object_id, 'mechanic_city', array( 'fields' => 'ids' ) );
        if ( is_wp_error( $ids ) ) { return $ids; }
        $object_terms[ (int) $object_id ] = array_map( 'intval', $ids );
    }
    $child_snapshots = array();
    foreach ( $children as $child ) {
        $child_snapshots[ $child->term_id ] = array( 'term' => (array) $child, 'meta' => get_term_meta( $child->term_id ) );
    }
    $snapshots[ $key ] = array(
        'created_at' => gmdate( 'c' ), 'source' => (array) $source,
        'source_meta' => get_term_meta( $source->term_id ),
        'canonical_before' => (array) $canonical,
        'canonical_meta_before' => get_term_meta( $canonical->term_id ),
        'canonical_slug' => $slug, 'aliases_before' => $aliases,
        'objects' => array_map( 'intval', $objects ), 'object_terms' => $object_terms, 'children' => $child_snapshots,
    );
    update_option( 'findewerkstatt_city_merge_snapshots', $snapshots, false );
    $saved = get_option( 'findewerkstatt_city_merge_snapshots', array() );
    return isset( $saved[ $key ] ) ? true : new WP_Error( 'fw_city_backup_failed', 'Die Sicherung der Stadtbegriffe konnte nicht gespeichert werden.' );
}

function findewerkstatt_save_city_alias( $old_slug, $new_slug, &$aliases ) {
    if ( $old_slug === $new_slug ) { return true; }
    $aliases[ $old_slug ] = $new_slug;
    update_option( 'findewerkstatt_city_aliases', $aliases, false );
    $saved = get_option( 'findewerkstatt_city_aliases', array() );
    return is_array( $saved ) && ( $saved[ $old_slug ] ?? '' ) === $new_slug ? true
        : new WP_Error( 'fw_city_alias_failed', 'Der alte Stadtslug konnte nicht als Alias gespeichert werden.' );
}

/** Regionen werden nicht anhand ähnlicher Städtenamen zusammengeführt. */
function findewerkstatt_city_migration_is_region( $term ) {
    return (int) $term->parent === 0
        || preg_match( '/^lk-|(?:kreis|region)$/iu', $term->slug )
        || preg_match( '/^(?:Landkreis|Kreis|Region|Städteregion|Regionalverband)\b|(?:kreis|region)$/iu', $term->name );
}

/** Eine bereits durch exakte Namen und Elternbegriff bestimmte Gruppe zusammenführen. */
function findewerkstatt_merge_city_group( $candidates, $canonical_slug, $canonical_name, &$aliases, &$snapshots, &$stats ) {
    if ( ! $candidates ) { return true; }
    usort( $candidates, function ( $a, $b ) { return $a->term_id <=> $b->term_id; } );
    $canonical = null;
    if ( $canonical_slug ) {
        $found = get_term_by( 'slug', $canonical_slug, 'mechanic_city' );
        if ( $found ) {
            foreach ( $candidates as $candidate ) {
                if ( (int) $candidate->term_id === (int) $found->term_id ) { $canonical = $found; break; }
            }
            if ( ! $canonical ) {
                return new WP_Error( 'fw_city_slug_conflict', 'Der Stadtslug ' . $canonical_slug . ' gehört zu einem anderen Gebiet.' );
            }
        }
    }
    if ( ! $canonical ) {
        // Ohne vorgegebenen Datenslug bleibt die Identität der Stadt ohne Zusatz erhalten.
        foreach ( $candidates as $candidate ) {
            if ( $candidate->name === $canonical_name ) { $canonical = $candidate; break; }
        }
        $canonical = $canonical ?: $candidates[0];
    }
    $canonical_slug = $canonical_slug ?: $canonical->slug;

    if ( $canonical->slug !== $canonical_slug || $canonical->name !== $canonical_name ) {
        $saved = findewerkstatt_snapshot_city_term( $canonical, $canonical, $canonical_slug, $aliases, $snapshots, ':canonical' );
        if ( is_wp_error( $saved ) ) { return $saved; }
        $old_slug = $canonical->slug;
        $name_changed = $canonical->name !== $canonical_name;
        $saved = findewerkstatt_save_city_alias( $old_slug, $canonical_slug, $aliases );
        if ( is_wp_error( $saved ) ) { return $saved; }
        $result = wp_update_term( $canonical->term_id, 'mechanic_city', array( 'slug' => $canonical_slug, 'name' => $canonical_name ) );
        if ( is_wp_error( $result ) ) { return $result; }
        $canonical = get_term( $canonical->term_id, 'mechanic_city' );
        if ( ! $canonical || is_wp_error( $canonical ) || $canonical->slug !== $canonical_slug || $canonical->name !== $canonical_name ) {
            return new WP_Error( 'fw_city_rename_failed', 'Der Stadtbegriff konnte nicht aktualisiert werden.' );
        }
        if ( $old_slug !== $canonical_slug ) { ++$stats['renamed']; }
        if ( $name_changed ) { ++$stats['names_updated']; }
    }

    foreach ( $candidates as $candidate ) {
        if ( (int) $candidate->term_id === (int) $canonical->term_id ) { continue; }
        $source = get_term( $candidate->term_id, 'mechanic_city' );
        if ( ! $source || is_wp_error( $source ) ) { continue; }
        $saved = findewerkstatt_snapshot_city_term( $source, $canonical, $canonical_slug, $aliases, $snapshots );
        if ( is_wp_error( $saved ) ) { return $saved; }
        $objects = get_objects_in_term( $source->term_id, 'mechanic_city' );
        $children = get_terms( array( 'taxonomy' => 'mechanic_city', 'parent' => $source->term_id, 'hide_empty' => false ) );
        if ( is_wp_error( $objects ) ) { return $objects; }
        if ( is_wp_error( $children ) ) { return $children; }
        foreach ( $objects as $object_id ) {
            $result = wp_set_object_terms( (int) $object_id, array( (int) $canonical->term_id ), 'mechanic_city', true );
            if ( is_wp_error( $result ) ) { return $result; }
        }
        foreach ( $children as $child ) {
            $result = wp_update_term( $child->term_id, 'mechanic_city', array( 'parent' => $canonical->term_id ) );
            if ( is_wp_error( $result ) ) { return $result; }
        }
        $destination_meta = get_term_meta( $canonical->term_id );
        foreach ( get_term_meta( $source->term_id ) as $key => $values ) {
            foreach ( $values as $value ) {
                $value = maybe_unserialize( $value );
                $known = array_map( 'maybe_unserialize', $destination_meta[ $key ] ?? array() );
                if ( ! in_array( $value, $known, true ) ) {
                    $meta_id = add_term_meta( $canonical->term_id, $key, wp_slash( $value ) );
                    if ( is_wp_error( $meta_id ) || false === $meta_id ) {
                        return new WP_Error( 'fw_city_meta_failed', 'Stadt-Metadaten konnten nicht vollständig übernommen werden.' );
                    }
                    $destination_meta[ $key ][] = $value;
                }
            }
        }
        $saved = findewerkstatt_save_city_alias( $source->slug, $canonical_slug, $aliases );
        if ( is_wp_error( $saved ) ) { return $saved; }
        $result = wp_delete_term( $source->term_id, 'mechanic_city' );
        if ( is_wp_error( $result ) || ! $result ) {
            return new WP_Error( 'fw_city_merge_failed', 'Ein doppelter Stadtbegriff konnte nicht entfernt werden.' );
        }
        ++$stats['merged'];
    }
    return true;
}

/**
 * Admin-Aufruf durch den versionierten Theme-Upgrade.
 * Nur exakte Namensdubletten oder ausdrücklich hinterlegte Stadt-Varianten werden vereinigt.
 */
function findewerkstatt_merge_city_terms() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return new WP_Error( 'fw_city_migration_forbidden', 'Die Stadtmigration erfordert Administratorrechte.' );
    }
    $aliases = get_option( 'findewerkstatt_city_aliases', array() );
    $snapshots = get_option( 'findewerkstatt_city_merge_snapshots', array() );
    $aliases = is_array( $aliases ) ? $aliases : array();
    $snapshots = is_array( $snapshots ) ? $snapshots : array();
    $stats = array( 'merged' => 0, 'renamed' => 0, 'names_updated' => 0 );
    $definitions = array();
    // This official municipality also occurs under its established shortened name.
    // Keep the full canonical slug; similarly named places in other states stay separate.
    $verified_name_variants = array(
        'rheinland-pfalz|Ludwigshafen am Rhein' => array( 'Ludwigshafen' ),
    );
    foreach ( FindeWerkstatt_German_Data::get_top_cities() as $slug => $city ) {
        if ( $slug === $city['land'] ) { continue; }
        $key = $city['land'] . '|' . $city['name'];
        $names = array_merge( array( $city['name'] ), $verified_name_variants[ $key ] ?? array() );
        $definitions[ $key ] = array( 'land' => $city['land'], 'name' => $city['name'], 'names' => $names, 'slug' => $slug, 'top_city' => true );
    }
    if ( class_exists( 'FindeWerkstatt_Germany_Geo_Hierarchy' ) ) {
        foreach ( FindeWerkstatt_Germany_Geo_Hierarchy::get_districts_by_state() as $land => $cities ) {
            foreach ( $cities as $slug => $name ) {
                if ( ! str_ends_with( $name, ' (Stadt)' ) ) { continue; }
                $base_name = substr( $name, 0, -strlen( ' (Stadt)' ) );
                $key = $land . '|' . $base_name;
                if ( isset( $definitions[ $key ] ) ) {
                    $definitions[ $key ]['names'][] = $name;
                } else {
                    // Für diese beiden belegten Identitäten ist der korrigierte Datenslug vorhanden.
                    $preferred = in_array( $slug, array( 'fuerth', 'neumuenster' ), true ) ? $slug : '';
                    $definitions[ $key ] = array( 'land' => $land, 'name' => $base_name, 'names' => array( $base_name, $name ), 'slug' => $preferred, 'top_city' => false );
                }
            }
        }
    }

    foreach ( $definitions as $definition ) {
        $state = get_term_by( 'slug', $definition['land'], 'mechanic_city' );
        if ( ! $state || is_wp_error( $state ) || (int) $state->parent !== 0 ) { continue; }
        $terms = get_terms( array( 'taxonomy' => 'mechanic_city', 'parent' => $state->term_id, 'hide_empty' => false ) );
        if ( is_wp_error( $terms ) ) { return $terms; }
        $candidates = array_values( array_filter( $terms, function ( $term ) use ( $definition ) {
            return in_array( $term->name, $definition['names'], true ) && ! findewerkstatt_city_migration_is_region( $term );
        } ) );
        if ( ! $definition['top_city'] && count( $candidates ) < 2 ) { continue; }
        $result = findewerkstatt_merge_city_group( $candidates, $definition['slug'], $definition['name'], $aliases, $snapshots, $stats );
        if ( is_wp_error( $result ) ) { return $result; }
    }

    // Namen außerhalb der hinterlegten Stadtlisten: ausschließlich exakt gleicher Elternbegriff und Name.
    do {
        $merged_before = $stats['merged'];
        $terms = get_terms( array( 'taxonomy' => 'mechanic_city', 'hide_empty' => false ) );
        if ( is_wp_error( $terms ) ) { return $terms; }
        $groups = array();
        foreach ( $terms as $term ) {
            if ( findewerkstatt_city_migration_is_region( $term ) ) { continue; }
            $key = $term->parent . '|' . $term->name;
            $groups[ $key ][] = $term;
        }
        foreach ( $groups as $group ) {
            if ( count( $group ) < 2 ) { continue; }
            $result = findewerkstatt_merge_city_group( $group, '', $group[0]->name, $aliases, $snapshots, $stats );
            if ( is_wp_error( $result ) ) { return $result; }
        }
        // Beim Umhängen können gleichnamige Kinder zusammenkommen; erneut exakt prüfen.
    } while ( $stats['merged'] > $merged_before );
    return $stats;
}

/** Nur gültige Ortsketten und echte Leistung-/Markenkombinationen bekommen einen Alias. */
function findewerkstatt_city_alias_path( $request ) {
    $path = trim( (string) $request, '/' );
    if ( 0 === strpos( $path, 'stadt/' ) ) {
        $term_path = substr( $path, 6 );
        $suffix = '';
        if ( preg_match( '#/(?:page/[1-9][0-9]*|(?:feed/)?(?:feed|rdf|rss|rss2|atom))$#', $term_path, $matches ) ) {
            $suffix = $matches[0];
            $term_path = substr( $term_path, 0, -strlen( $suffix ) );
        }
        $canonical_parts = array();
        $parent_id = 0;
        $changed = false;
        foreach ( explode( '/', $term_path ) as $slug ) {
            $original = sanitize_title( $slug );
            $canonical_slug = findewerkstatt_normalize_city_slug( $original );
            $term = get_term_by( 'slug', $canonical_slug, 'mechanic_city' );
            if ( ! $term || is_wp_error( $term ) || (int) $term->parent !== $parent_id ) { return ''; }
            $changed = $changed || $canonical_slug !== $original;
            $canonical_parts[] = $term->slug;
            $parent_id = (int) $term->term_id;
        }
        return $changed ? 'stadt/' . implode( '/', $canonical_parts ) . $suffix : '';
    }
    if ( preg_match( '#^(service|marke)/([^/]+)/in/([^/]+)(/page/[1-9][0-9]*)?$#', $path, $matches ) ) {
        $taxonomy = 'service' === $matches[1] ? 'service_type' : 'car_brand';
        $term = get_term_by( 'slug', sanitize_title( $matches[2] ), $taxonomy );
        $original = sanitize_title( $matches[3] );
        $canonical_slug = findewerkstatt_normalize_city_slug( $original );
        $location = get_term_by( 'slug', $canonical_slug, 'mechanic_city' );
        if ( ! $term || ! $location || is_wp_error( $term ) || is_wp_error( $location ) || $canonical_slug === $original ) { return ''; }
        return $matches[1] . '/' . $term->slug . '/in/' . $location->slug . ( $matches[4] ?? '' );
    }
    return '';
}

/** Vor der normalen Routenprüfung auch alte gefilterte Such-URLs aktualisieren. */
function findewerkstatt_normalize_city_alias_request( $wp ) {
    $target = findewerkstatt_city_alias_path( $wp->request );
    $query_changed = false;
    foreach ( array( 'fw_city', 'fw_pseo_location' ) as $key ) {
        if ( isset( $_GET[ $key ] ) && is_string( $_GET[ $key ] ) ) {
            $old = sanitize_title( wp_unslash( $_GET[ $key ] ) );
            $new = findewerkstatt_normalize_city_slug( $old );
            if ( $old !== $new ) {
                $_GET[ $key ] = wp_slash( $new );
                $query_changed = true;
            }
        }
        if ( isset( $wp->query_vars[ $key ] ) && is_string( $wp->query_vars[ $key ] ) ) {
            $wp->query_vars[ $key ] = findewerkstatt_normalize_city_slug( $wp->query_vars[ $key ] );
        }
    }
    if ( $target ) { $wp->request = $target; }
    if ( $target || $query_changed ) {
        $GLOBALS['findewerkstatt_city_alias_redirect'] = array(
            'path' => $target ?: trim( (string) $wp->request, '/' ),
            'validated_path_alias' => (bool) $target,
        );
    }
}
add_action( 'parse_request', 'findewerkstatt_normalize_city_alias_request', 5 );

add_action( 'template_redirect', function () {
    $redirect = $GLOBALS['findewerkstatt_city_alias_redirect'] ?? null;
    if ( ! $redirect || ! in_array( $_SERVER['REQUEST_METHOD'] ?? 'GET', array( 'GET', 'HEAD' ), true ) ) { return; }
    // Ein falsches Bundesland bleibt auch mit einem alten Query-Parameter eine Fehlerseite.
    if ( ! $redirect['validated_path_alias'] && is_404() ) { return; }
    $url = home_url( '/' . ( $redirect['path'] ? user_trailingslashit( $redirect['path'] ) : '' ) );
    if ( $_GET ) { $url = add_query_arg( wp_unslash( $_GET ), $url ); }
    wp_safe_redirect( $url, 301 );
    exit;
}, 1 );


