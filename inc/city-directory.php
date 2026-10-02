<?php
/** Verified workbook locations, added without replacing existing taxonomy identities. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function findewerkstatt_city_directory_data() {
    static $data = null;
    if ( $data === null ) {
        $path = get_template_directory() . '/inc/data/german-cities.json';
        $data = is_readable( $path ) ? json_decode( file_get_contents( $path ), true ) : null;
    }
    return is_array( $data ) && isset( $data['cities'] ) && is_array( $data['cities'] )
        ? $data : new WP_Error( 'fw_city_data', 'Die geprüften Ortsdaten konnten nicht gelesen werden.' );
}

function findewerkstatt_city_directory_key( $name ) {
    $name = preg_replace( '/\s*\(Stadt\)$/u', '', trim( (string) $name ) );
    return findewerkstatt_german_location_slug( $name );
}

/** A Landkreis is never a city just because its shortened name matches. */
function findewerkstatt_city_directory_is_region( $term ) {
    return str_starts_with( $term->slug, 'lk-' )
        || preg_match( '/^(?:Landkreis|Kreis|Region|Städteregion|Regionalverband)\b|(?:kreis|region)$/iu', $term->name );
}

/** Root state slug for a term, using the already loaded hierarchy. */
function findewerkstatt_city_directory_state( $term, $terms ) {
    $seen = array();
    while ( $term->parent && isset( $terms[ $term->parent ] ) ) {
        if ( isset( $seen[ $term->term_id ] ) ) { return ''; }
        $seen[ $term->term_id ] = true;
        $term = $terms[ $term->parent ];
    }
    return $term->parent ? '' : $term->slug;
}

/** Reuse two verified pre-existing short names; never infer ambiguous city aliases. */
function findewerkstatt_city_directory_reuse_term( $city, $data, &$terms, &$slugs, &$ags_terms ) {
    if ( empty( $city['reuse_slug'] ) || ! isset( $slugs[ $city['reuse_slug'] ] ) ) { return null; }
    $target = $terms[ $slugs[ $city['reuse_slug'] ] ];
    $target_ags = (string) get_term_meta( $target->term_id, '_fw_city_ags', true );
    if ( ! $target->parent || findewerkstatt_city_directory_is_region( $target )
        || findewerkstatt_city_directory_state( $target, $terms ) !== $city['state_slug']
        || ( $target_ags && $target_ags !== $city['ags'] ) ) {
        return new WP_Error( 'fw_city_reuse_conflict', 'Der bestehende Ort für ' . $city['name'] . ' ist nicht eindeutig zugeordnet.' );
    }
    $duplicate = $ags_terms[ $city['ags'] ] ?? null;
    if ( $duplicate && (int) $duplicate->term_id !== (int) $target->term_id ) {
        // Only a duplicate created from this precise workbook row may be consolidated.
        if ( $duplicate->slug !== findewerkstatt_city_directory_key( $city['name'] )
            || (int) get_term_meta( $duplicate->term_id, '_fw_city_source_row', true ) !== (int) $city['source_row']
            || get_term_meta( $duplicate->term_id, '_fw_city_source_file', true ) !== $data['source_workbook']
            || findewerkstatt_city_directory_state( $duplicate, $terms ) !== $city['state_slug'] ) {
            return new WP_Error( 'fw_city_reuse_duplicate', 'Der doppelte Ort für ' . $city['name'] . ' benötigt eine eindeutige Herkunft.' );
        }
        $aliases = get_option( 'findewerkstatt_city_aliases', array() );
        $snapshots = get_option( 'findewerkstatt_city_merge_snapshots', array() );
        $merge_stats = array( 'merged' => 0, 'renamed' => 0, 'names_updated' => 0 );
        $merged = findewerkstatt_merge_city_group( array( $target, $duplicate ), $target->slug, $target->name, $aliases, $snapshots, $merge_stats );
        if ( is_wp_error( $merged ) ) { return $merged; }
        unset( $terms[ $duplicate->term_id ], $slugs[ $duplicate->slug ] );
        foreach ( $terms as $id => $term ) {
            if ( (int) $term->parent === (int) $duplicate->term_id ) { $terms[ $id ] = get_term( $id, 'mechanic_city' ); }
        }
        $target = get_term( $target->term_id, 'mechanic_city' );
        $terms[ $target->term_id ] = $target;
    }
    $ags_terms[ $city['ags'] ] = $target;
    return $target;
}

/**
 * Administrator-only additive synchronisation. Public requests never create places.
 * Existing slugs, parent links, descriptions and workshop assignments stay intact.
 */
function findewerkstatt_sync_city_directory( $force = false ) {
    if ( ! current_user_can( 'manage_options' ) ) { return new WP_Error( 'fw_city_forbidden', 'Administratorrechte erforderlich.' ); }
    $data = findewerkstatt_city_directory_data();
    if ( is_wp_error( $data ) ) { return $data; }
    $version = hash( 'sha256', wp_json_encode( $data ) );
    if ( ! $force && get_option( 'findewerkstatt_city_directory_version' ) === $version ) {
        return array( 'created' => 0, 'reused' => count( $data['cities'] ), 'unchanged' => true );
    }
    $all = get_terms( array( 'taxonomy' => 'mechanic_city', 'hide_empty' => false, 'orderby' => 'term_id' ) );
    if ( is_wp_error( $all ) ) { return $all; }
    $terms = array(); $states = array(); $ags_terms = array(); $slugs = array();
    $known_states = array_column( FindeWerkstatt_German_Data::get_bundeslaender(), null, 'slug' );
    foreach ( $all as $term ) {
        $terms[ $term->term_id ] = $term;
        $slugs[ $term->slug ] = $term->term_id;
        if ( ! $term->parent && isset( $known_states[ $term->slug ] ) ) { $states[ $term->slug ] = $term; }
        $ags = (string) get_term_meta( $term->term_id, '_fw_city_ags', true );
        if ( $ags ) { $ags_terms[ $ags ] = $term; }
    }
    // Validate the entire file and its state parents before performing any mutation.
    $seen_rows = array();
    foreach ( $data['cities'] as $city ) {
        if ( ! is_array( $city ) || empty( $city['name'] ) || empty( $city['source_row'] )
            || ! isset( $states[ $city['state_slug'] ?? '' ] ) || isset( $seen_rows[ $city['source_row'] ] )
            || ! in_array( $city['location_type'] ?? 'city', array( 'city', 'district' ), true )
            || ( ! empty( $city['ags'] ) && ! preg_match( '/^\d{8}$/', $city['ags'] ) ) ) {
            return new WP_Error( 'fw_city_invalid_row', 'Die Ortsdaten enthalten eine ungültige oder doppelte Zeile.' );
        }
        $seen_rows[ $city['source_row'] ] = true;
        if ( ( $city['lat'] ?? null ) !== null || ( $city['lng'] ?? null ) !== null ) {
            if ( ! is_numeric( $city['lat'] ?? null ) || ! is_numeric( $city['lng'] ?? null )
                || $city['lat'] < 47 || $city['lat'] > 56 || $city['lng'] < 5 || $city['lng'] > 16 ) {
                return new WP_Error( 'fw_city_invalid_coordinates', 'Die Ortsdaten enthalten ungültige Deutschland-Koordinaten.' );
            }
        }
    }
    $snapshot_key = 'findewerkstatt_city_directory_backup_' . substr( $version, 0, 12 );
    if ( ! get_option( $snapshot_key ) ) {
        $snapshot = array( 'created_at' => gmdate( 'c' ), 'terms' => array() );
        foreach ( $terms as $term ) {
            $snapshot['terms'][ $term->term_id ] = array( 'term' => (array) $term, 'meta' => get_term_meta( $term->term_id ) );
        }
        add_option( $snapshot_key, $snapshot, '', false );
        if ( ! get_option( $snapshot_key ) ) { return new WP_Error( 'fw_city_backup', 'Die Ortsdatensicherung konnte nicht gespeichert werden.' ); }
    }
    $stats = array( 'created' => 0, 'reused' => 0, 'total' => count( $data['cities'] ), 'term_ids' => array(), 'created_term_ids' => array(), 'source' => $data['source_workbook'] ?? '' );
    // Independent municipalities must exist before their supplied districts are added.
    $cities = $data['cities'];
    usort( $cities, function ( $a, $b ) { return ( ( $a['location_type'] ?? 'city' ) === 'district' ) <=> ( ( $b['location_type'] ?? 'city' ) === 'district' ); } );
    foreach ( $cities as $city ) {
        $state = $states[ $city['state_slug'] ];
        $parent = $state;
        if ( ! empty( $city['parent_ags'] ) ) {
            if ( ! isset( $ags_terms[ $city['parent_ags'] ] ) ) { return new WP_Error( 'fw_city_parent', 'Der übergeordnete Ort für ' . $city['name'] . ' fehlt.' ); }
            $parent = $ags_terms[ $city['parent_ags'] ];
        }
        $term = findewerkstatt_city_directory_reuse_term( $city, $data, $terms, $slugs, $ags_terms );
        if ( is_wp_error( $term ) ) { return $term; }
        if ( ! $term ) { $term = ! empty( $city['ags'] ) && isset( $ags_terms[ $city['ags'] ] ) ? $ags_terms[ $city['ags'] ] : null; }
        $key = findewerkstatt_city_directory_key( $city['name'] );
        if ( ! $term && in_array( $city['state_slug'], array( 'berlin', 'hamburg' ), true ) && $key === $city['state_slug'] ) { $term = $state; }
        if ( ! $term ) {
            foreach ( $terms as $candidate ) {
                if ( ! $candidate->parent || findewerkstatt_city_directory_is_region( $candidate )
                    || findewerkstatt_city_directory_state( $candidate, $terms ) !== $city['state_slug'] ) { continue; }
                if ( ( $city['location_type'] ?? 'city' ) === 'district' && (int) $candidate->parent !== (int) $parent->term_id ) { continue; }
                if ( findewerkstatt_city_directory_key( $candidate->name ) === $key ) { $term = $candidate; break; }
            }
        }
        if ( $term && findewerkstatt_city_directory_state( $term, $terms ) !== $city['state_slug'] ) { return new WP_Error( 'fw_city_state_conflict', 'Der Ort ' . $city['name'] . ' hat eine widersprüchliche Bundesland-Zuordnung.' ); }
        if ( ! $term ) {
            $slug = $key;
            if ( isset( $slugs[ $slug ] ) ) { $slug .= '-' . $city['state_slug']; }
            if ( isset( $slugs[ $slug ] ) ) { $slug .= '-' . ( $city['ags'] ?: $city['source_row'] ); }
            $inserted = wp_insert_term( $city['name'], 'mechanic_city', array( 'slug' => $slug, 'parent' => $parent->term_id ) );
            if ( is_wp_error( $inserted ) ) { return $inserted; }
            $term = get_term( $inserted['term_id'], 'mechanic_city' );
            if ( ! $term || is_wp_error( $term ) ) { return new WP_Error( 'fw_city_insert', 'Ein neuer Ort konnte nicht gelesen werden.' ); }
            $terms[ $term->term_id ] = $term; $slugs[ $term->slug ] = $term->term_id;
            ++$stats['created']; $stats['created_term_ids'][] = $term->term_id;
        } else { ++$stats['reused']; }
        $meta = array(
            '_fw_location_type' => $city['location_type'] ?? 'city',
            '_fw_city_source_row' => (int) $city['source_row'],
            '_fw_city_source_name' => $city['source_city'] ?? $city['name'],
            '_fw_city_source_file' => $data['source_workbook'] ?? '',
            '_fw_city_verified_source' => $city['verified_source_url'] ?? $data['verified_source_url'] ?? '',
        );
        if ( ! empty( $city['ags'] ) ) { $meta['_fw_city_ags'] = $city['ags']; $ags_terms[ $city['ags'] ] = $term; }
        if ( ! empty( $city['parent_ags'] ) ) { $meta['_fw_city_parent_ags'] = $city['parent_ags']; }
        // Do not substitute a town centre for a workshop address or an existing coordinate pair.
        if ( is_numeric( $city['lat'] ?? null ) && is_numeric( $city['lng'] ?? null )
            && ( ! metadata_exists( 'term', $term->term_id, '_geo_lat' ) || ! metadata_exists( 'term', $term->term_id, '_geo_lng' ) ) ) {
            $meta['_geo_lat'] = $city['lat']; $meta['_geo_lng'] = $city['lng'];
            $meta['_fw_city_coordinate_source'] = $data['verified_source_url'] ?? '';
        }
        foreach ( $meta as $meta_key => $value ) {
            update_term_meta( $term->term_id, $meta_key, $value );
            if ( (string) get_term_meta( $term->term_id, $meta_key, true ) !== (string) $value ) { return new WP_Error( 'fw_city_meta', 'Die Ortsangaben für ' . $city['name'] . ' konnten nicht gespeichert werden.' ); }
        }
        $stats['term_ids'][ $city['source_row'] ] = (int) $term->term_id;
    }
    update_option( 'findewerkstatt_city_directory_result', $stats, false );
    update_option( 'findewerkstatt_city_directory_version', $version, false );
    if ( get_option( 'findewerkstatt_city_directory_version' ) !== $version ) { return new WP_Error( 'fw_city_version', 'Der Ortsdatenstand konnte nicht gespeichert werden.' ); }
    delete_option( 'findewerkstatt_city_directory_error' );
    return $stats;
}

add_action( 'admin_init', function () {
    if ( ! current_user_can( 'manage_options' ) ) { return; }
    $result = findewerkstatt_sync_city_directory();
    if ( is_wp_error( $result ) ) { update_option( 'findewerkstatt_city_directory_error', $result->get_error_message(), false ); }
}, 30 );
add_action( 'admin_notices', function () {
    if ( current_user_can( 'manage_options' ) && get_option( 'findewerkstatt_city_directory_error' ) ) {
        echo '<div class="notice notice-error"><p>Die Ortsdaten konnten nicht vollständig ergänzt werden: ' . esc_html( get_option( 'findewerkstatt_city_directory_error' ) ) . '</p></div>';
    }
} );
