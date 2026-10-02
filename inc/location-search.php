<?php
/** Validate the dependent Bundesland and city filters without changing locations. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Resolve both the new state-first search and existing fw_city-only links.
 * Invalid input deliberately keeps a non-matching location constraint.
 */
function findewerkstatt_resolve_location_filter( $input ) {
    $result = array( 'state' => '', 'city' => '', 'location' => '__invalid__', 'valid' => false );
    if ( ! is_array( $input ) ) { return $result; }

    $values = array();
    foreach ( array( 'fw_state', 'fw_city' ) as $key ) {
        if ( array_key_exists( $key, $input ) && ! is_scalar( $input[ $key ] ) ) { return $result; }
        $raw = trim( wp_unslash( (string) ( $input[ $key ] ?? '' ) ) );
        $values[ $key ] = sanitize_title( $raw );
        if ( '' !== $raw && '' === $values[ $key ] ) { return $result; }
    }

    $known_states = array_column( FindeWerkstatt_German_Data::get_bundeslaender(), null, 'slug' );
    $state = null;
    if ( '' !== $values['fw_state'] ) {
        if ( ! isset( $known_states[ $values['fw_state'] ] ) ) { return $result; }
        $state = get_term_by( 'slug', $values['fw_state'], 'mechanic_city' );
        if ( ! $state || is_wp_error( $state ) || (int) $state->parent !== 0 ) { return $result; }
        $result['state'] = $state->slug;
    }

    if ( '' === $values['fw_city'] ) {
        $result['location'] = $result['state'];
        $result['valid'] = true;
        return $result;
    }

    $city_slug = function_exists( 'findewerkstatt_normalize_city_slug' )
        ? findewerkstatt_normalize_city_slug( $values['fw_city'] ) : $values['fw_city'];
    $city = get_term_by( 'slug', $city_slug, 'mechanic_city' );
    if ( ! $city || is_wp_error( $city ) ) { return $result; }

    // Districts can be nested below towns or regions; follow the full parent chain.
    $root = $city;
    $seen = array();
    while ( (int) $root->parent !== 0 ) {
        if ( isset( $seen[ $root->term_id ] ) ) { return $result; }
        $seen[ $root->term_id ] = true;
        $root = get_term( (int) $root->parent, 'mechanic_city' );
        if ( ! $root || is_wp_error( $root ) ) { return $result; }
    }
    if ( ! isset( $known_states[ $root->slug ] ) ) { return $result; }
    $actual_state = get_term_by( 'slug', $root->slug, 'mechanic_city' );
    if ( ! $actual_state || is_wp_error( $actual_state ) || (int) $actual_state->parent !== 0
        || (int) $actual_state->term_id !== (int) $root->term_id
        || ( $state && (int) $state->term_id !== (int) $root->term_id ) ) { return $result; }

    $result['state'] = $root->slug;
    $result['location'] = $city->slug;
    // Berlin and Hamburg are both a Bundesland and its whole-city location.
    $result['city'] = (int) $city->parent !== 0 || in_array( $city->slug, array( 'berlin', 'hamburg' ), true ) ? $city->slug : '';
    $result['valid'] = true;
    return $result;
}
