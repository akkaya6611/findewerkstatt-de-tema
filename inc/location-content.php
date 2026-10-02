<?php
/** Verified location context shared by visible directory content and SEO metadata. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function findewerkstatt_location_content_terms() {
    static $terms = null;
    if ( null === $terms ) {
        $all = get_terms( array( 'taxonomy' => 'mechanic_city', 'hide_empty' => false, 'orderby' => 'name', 'update_term_meta_cache' => true ) );
        $terms = array();
        if ( ! is_wp_error( $all ) ) { foreach ( $all as $term ) { $terms[ $term->term_id ] = $term; } }
    }
    return $terms;
}

function findewerkstatt_location_display_name( $term ) {
    if ( 'berlin-mitte' === $term->slug ) { return 'Mitte'; }
    $official_counties = array(
        'lk-rendsburg-eckernfoerde' => 'Kreis Rendsburg-Eckernförde',
        'lk-schleswig-flensburg' => 'Kreis Schleswig-Flensburg',
        'lk-siegen-wittgenstein' => 'Kreis Siegen-Wittgenstein',
    );
    if ( isset( $official_counties[ $term->slug ] ) ) { return $official_counties[ $term->slug ]; }
    $name = function_exists( 'findewerkstatt_location_label' ) ? findewerkstatt_location_label( $term ) : $term->name;
    return preg_replace( '/\s*\(Stadt\)$/u', '', $name );
}

function findewerkstatt_location_child_terms( $term_id ) {
    static $children = null;
    if ( null === $children ) {
        $children = array();
        foreach ( findewerkstatt_location_content_terms() as $term ) { $children[ $term->parent ][] = $term; }
    }
    return $children[ $term_id ] ?? array();
}

function findewerkstatt_location_context( $term ) {
    static $contexts = array();
    $type = get_term_meta( $term->term_id, '_fw_location_type', true );
    $cache_key = $term->term_id . ':' . $term->parent . ':' . $term->name . ':' . $type;
    if ( isset( $contexts[ $cache_key ] ) ) { return $contexts[ $cache_key ]; }
    $terms = findewerkstatt_location_content_terms();
    $terms[ $term->term_id ] = $term;
    $root = $term; $seen = array();
    while ( $root->parent && isset( $terms[ $root->parent ] ) ) {
        if ( isset( $seen[ $root->term_id ] ) ) { break; }
        $seen[ $root->term_id ] = true; $root = $terms[ $root->parent ];
    }
    $known = array_column( FindeWerkstatt_German_Data::get_bundeslaender(), null, 'slug' );
    $state = ! $root->parent && isset( $known[ $root->slug ] ) ? $root : null;
    $parent = $terms[ $term->parent ] ?? null;
    $kind = 'city';
    if ( ! $term->parent ) {
        $kind = $state ? ( in_array( $term->slug, array( 'berlin', 'hamburg' ), true ) ? 'city_state' : 'state' ) : 'region';
    } elseif ( 'district' === $type ) { $kind = 'district';
    } elseif ( $parent && ! $parent->parent && in_array( $parent->slug, array( 'berlin', 'hamburg' ), true )
        && str_starts_with( $term->slug, $parent->slug . '-' ) ) { $kind = 'borough';
    } elseif ( preg_match( '/^(?:Region|Städteregion|Regionalverband)\b|region$/iu', $term->name ) ) { $kind = 'region';
    } elseif ( str_starts_with( $term->slug, 'lk-' ) || preg_match( '/^(?:Landkreis|Kreis)\b|kreis$/iu', $term->name ) ) { $kind = 'county'; }
    $children = findewerkstatt_location_child_terms( $term->term_id );
    $child_names = array();
    foreach ( $children as $child ) {
        if ( in_array( $kind, array( 'state', 'city_state' ), true ) && findewerkstatt_city_directory_is_region( $child ) ) { continue; }
        $child_names[] = findewerkstatt_location_display_name( $child );
        if ( count( $child_names ) === 6 ) { break; }
    }
    return $contexts[ $cache_key ] = array(
        'kind' => $kind, 'name' => findewerkstatt_location_display_name( $term ),
        'term' => $term, 'state' => $state, 'parent' => $parent,
        'state_name' => $state ? findewerkstatt_location_display_name( $state ) : '',
        'parent_name' => $parent ? findewerkstatt_location_display_name( $parent ) : '',
        'children' => $children, 'child_names' => $child_names,
    );
}

/** Label actual assigned places and their state without guessing missing locations. */
function findewerkstatt_workshop_location_links( $context ) {
    if ( ! is_array( $context ) || empty( $context['term'] ) ) { return array(); }
    $links = array();
    $add = function ( $term, $label ) use ( &$links ) {
        if ( ! $term || is_wp_error( $term ) || isset( $links[ $term->term_id ] ) ) { return; }
        $url = get_term_link( $term );
        $links[ $term->term_id ] = array( 'label' => findewerkstatt_location_translate( $label ), 'name' => findewerkstatt_location_display_name( $term ), 'url' => is_wp_error( $url ) ? '' : $url );
    };
    $kind = $context['kind'];
    if ( 'city_state' === $kind ) {
        $add( $context['term'], 'Ort und Bundesland' );
    } elseif ( 'state' === $kind ) {
        $add( $context['term'], 'Bundesland' );
    } elseif ( in_array( $kind, array( 'district', 'borough' ), true ) ) {
        $add( $context['term'], 'borough' === $kind ? 'Bezirk' : 'Ortsteil' );
        if ( $context['parent'] ) {
            $parent_context = findewerkstatt_location_context( $context['parent'] );
            if ( in_array( $parent_context['kind'], array( 'city', 'city_state' ), true ) ) {
                $add( $context['parent'], 'city_state' === $parent_context['kind'] ? 'Ort und Bundesland' : 'Stadt / Ort' );
            }
        }
    } else {
        $add( $context['term'], 'city' === $kind ? 'Stadt / Ort' : 'Gebiet' );
    }
    $add( $context['state'], 'Bundesland' );
    return array_values( $links );
}

/** Source rows verify municipality centres, not workshop addresses. */
function findewerkstatt_location_source_record( $term ) {
    static $rows = null;
    if ( null === $rows ) {
        $rows = array();
        $data = findewerkstatt_city_directory_data();
        if ( ! is_wp_error( $data ) ) {
            foreach ( $data['cities'] as $city ) {
                $city['source_url'] = $city['verified_source_url'] ?? $data['verified_source_url'] ?? '';
                $city['source_date'] = $data['verified_source_date'] ?? '';
                $rows[ $city['source_row'] ] = $city;
            }
        }
    }
    $row = (int) get_term_meta( $term->term_id, '_fw_city_source_row', true );
    $city = $rows[ $row ] ?? null;
    if ( ! $city ) { return null; }
    $context = findewerkstatt_location_context( $term );
    if ( ! $context['state'] || $context['state']->slug !== $city['state_slug'] ) { return null; }
    if ( ! empty( $city['ags'] ) && (string) get_term_meta( $term->term_id, '_fw_city_ags', true ) !== $city['ags'] ) { return null; }
    if ( ! empty( $city['parent_ags'] ) && ( ! $context['parent']
        || get_term_meta( $context['parent']->term_id, '_fw_city_ags', true ) !== $city['parent_ags'] ) ) { return null; }
    return $city;
}

function findewerkstatt_location_valid_geo( $lat, $lng ) {
    return is_numeric( $lat ) && is_numeric( $lng ) && is_finite( (float) $lat ) && is_finite( (float) $lng )
        && $lat >= 47 && $lat <= 56 && $lng >= 5 && $lng <= 16;
}

function findewerkstatt_location_geo( $term ) {
    $context = findewerkstatt_location_context( $term );
    if ( ! in_array( $context['kind'], array( 'city', 'city_state', 'district', 'borough' ), true ) ) { return null; }
    $record = findewerkstatt_location_source_record( $term );
    if ( $record && findewerkstatt_location_valid_geo( $record['lat'] ?? null, $record['lng'] ?? null ) ) {
        return array( 'lat' => (float) $record['lat'], 'lng' => (float) $record['lng'], 'source_url' => $record['source_url'], 'source_date' => $record['source_date'] );
    }
    // A separately verified centre may be maintained for a future town or district.
    $source = get_term_meta( $term->term_id, '_fw_city_coordinate_source', true );
    $lat = get_term_meta( $term->term_id, '_geo_lat', true ); $lng = get_term_meta( $term->term_id, '_geo_lng', true );
    if ( $source && findewerkstatt_location_valid_geo( $lat, $lng ) ) {
        return array( 'lat' => (float) $lat, 'lng' => (float) $lng, 'source_url' => $source, 'source_date' => '' );
    }
    return null;
}

function findewerkstatt_location_content( $term, $query = null ) {
    if ( ! $query ) { global $wp_query; $query = $wp_query; }
    $context = findewerkstatt_location_context( $term );
    $ids = array();
    foreach ( (array) ( $query->posts ?? array() ) as $post ) {
        if ( is_object( $post ) && 'mechanic' === $post->post_type && 'publish' === $post->post_status
            && 'yes' !== get_post_meta( $post->ID, '_findewerkstatt_demo', true ) ) { $ids[] = $post->ID; }
    }
    foreach ( array( 'service_type' => 'service_names', 'car_brand' => 'brand_names' ) as $taxonomy => $key ) {
        $labels = $ids ? wp_get_object_terms( $ids, $taxonomy, array( 'orderby' => 'name', 'number' => 6 ) ) : array();
        $context[ $key ] = is_wp_error( $labels ) ? array() : wp_list_pluck( $labels, 'name' );
    }
    return array( 'context' => $context, 'copy' => findewerkstatt_location_copy( $context, max( 0, (int) ( $query->found_posts ?? 0 ) ) ), 'geo' => findewerkstatt_location_geo( $term ) );
}

/** Other actual directories in the same state; centre distance only orders city links. */
function findewerkstatt_location_related_terms( $context, $limit = 6 ) {
    if ( ! $context['state'] || in_array( $context['kind'], array( 'state', 'city_state' ), true ) ) { return array(); }
    $origin = findewerkstatt_location_geo( $context['term'] );
    if ( ! $origin && $context['parent'] ) { $origin = findewerkstatt_location_geo( $context['parent'] ); }
    $related = array();
    foreach ( findewerkstatt_location_content_terms() as $term ) {
        if ( (int) $term->term_id === (int) $context['term']->term_id || ! $term->parent
            || ( $context['parent'] && (int) $term->term_id === (int) $context['parent']->term_id ) ) { continue; }
        $candidate = findewerkstatt_location_context( $term );
        if ( ! $candidate['state'] || $candidate['state']->term_id !== $context['state']->term_id ) { continue; }
        if ( 'borough' === $context['kind'] ) {
            if ( 'borough' !== $candidate['kind'] || $term->parent !== $context['term']->parent ) { continue; }
        } elseif ( 'city' !== $candidate['kind'] ) { continue; }
        $geo = $origin ? findewerkstatt_location_geo( $term ) : null;
        $distance = INF;
        if ( $origin && $geo ) {
            $a = sin( deg2rad( $geo['lat'] - $origin['lat'] ) / 2 ) ** 2
                + cos( deg2rad( $origin['lat'] ) ) * cos( deg2rad( $geo['lat'] ) ) * sin( deg2rad( $geo['lng'] - $origin['lng'] ) / 2 ) ** 2;
            $distance = 2 * asin( sqrt( max( 0, min( 1, $a ) ) ) );
        }
        $related[] = array( 'term' => $term, 'name' => $candidate['name'], 'distance' => $distance );
    }
    usort( $related, function ( $a, $b ) {
        if ( $a['distance'] !== $b['distance'] ) { return $a['distance'] <=> $b['distance']; }
        return strnatcasecmp( remove_accents( $a['name'] ), remove_accents( $b['name'] ) );
    } );
    return array_slice( $related, 0, $limit );
}
