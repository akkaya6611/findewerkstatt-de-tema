<?php
/** Local, backed-up WordPress integration: synchronises the supplied city directory. */
if ( PHP_SAPI !== 'cli' ) { http_response_code( 404 ); exit; }
$wp_root = dirname( __DIR__ );
while ( ! is_file( $wp_root . '/wp-load.php' ) ) {
    $parent = dirname( $wp_root );
    if ( $parent === $wp_root ) { fwrite( STDERR, "WordPress not found.\n" ); exit( 1 ); }
    $wp_root = $parent;
}
define( 'DISABLE_WP_CRON', true );
$_SERVER['REQUEST_METHOD'] = 'GET'; $_SERVER['REQUEST_URI'] = '/';
require_once $wp_root . '/wp-includes/plugin.php';
add_filter( 'pre_wp_mail', '__return_false', PHP_INT_MAX );
require $wp_root . '/wp-load.php';
$checks = 0; $fixtures = array();
function fw_city_check( $ok, $label ) { global $checks; if ( ! $ok ) { throw new RuntimeException( $label ); } ++$checks; }
function fw_city_test_cleanup() {
    global $fixtures;
    foreach ( $fixtures as $id ) {
        if ( str_starts_with( (string) get_the_title( $id ), 'FWCITYTEST ' ) ) { wp_delete_post( $id, true ); }
    }
}
register_shutdown_function( 'fw_city_test_cleanup' );
try {
    $before = get_terms( array( 'taxonomy' => 'mechanic_city', 'hide_empty' => false ) );
    fw_city_check( ! is_wp_error( $before ), 'Read original locations' );
    $original = array();
    foreach ( $before as $term ) { $original[ $term->term_id ] = array( 'name' => $term->name, 'slug' => $term->slug, 'parent' => $term->parent, 'objects' => get_objects_in_term( $term->term_id, 'mechanic_city' ) ); }
    if ( ! empty( $argv[1] ) ) { file_put_contents( $argv[1], wp_json_encode( $original, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) ); }
    wp_set_current_user( 0 );
    fw_city_check( is_wp_error( findewerkstatt_sync_city_directory() ), 'Visitors cannot synchronise cities' );
    $admins = get_users( array( 'role' => 'administrator', 'fields' => 'ID', 'number' => 1 ) );
    fw_city_check( ! empty( $admins ), 'Administrator exists' ); wp_set_current_user( $admins[0] );
    $first = findewerkstatt_sync_city_directory( true );
    fw_city_check( ! is_wp_error( $first ), is_wp_error( $first ) ? $first->get_error_message() : 'Synchronise directory' );
    $data = findewerkstatt_city_directory_data();
    fw_city_check( count( $first['term_ids'] ) === count( $data['cities'] ), 'Every verified source row maps to a location' );
    fw_city_check( count( array_unique( $first['term_ids'] ) ) === count( $data['cities'] ), 'Distinct verified source identities remain distinct' );
    foreach ( $original as $id => $saved ) {
        $term = get_term( $id, 'mechanic_city' );
        fw_city_check( $term && ! is_wp_error( $term ) && $term->name === $saved['name'] && $term->slug === $saved['slug'] && $term->parent === $saved['parent'], 'Original location identity and hierarchy preserved: ' . $id );
        fw_city_check( get_objects_in_term( $id, 'mechanic_city' ) === $saved['objects'], 'Original workshop assignments preserved: ' . $id );
    }
    foreach ( $data['cities'] as $city ) {
        $term = get_term( $first['term_ids'][ $city['source_row'] ], 'mechanic_city' );
        $state = get_term_by( 'slug', $city['state_slug'], 'mechanic_city' );
        fw_city_check( $term->term_id === $state->term_id || in_array( $state->term_id, get_ancestors( $term->term_id, 'mechanic_city', 'taxonomy' ), true ), 'Correct state: ' . $city['name'] );
        $resolved = findewerkstatt_registration_location( $city['name'], $city['state_slug'] );
        fw_city_check( ! is_wp_error( $resolved ) && $resolved['term_id'] === $term->term_id, 'Registration reuses correct city: ' . $city['name'] );
    }
    $second = findewerkstatt_sync_city_directory( true );
    fw_city_check( ! is_wp_error( $second ) && $second['created'] === 0 && $second['term_ids'] === $first['term_ids'], 'Second run is idempotent' );
    $bavaria = findewerkstatt_registration_location( 'Friedberg', 'bayern' );
    $hessen = findewerkstatt_registration_location( 'Friedberg', 'hessen' );
    fw_city_check( ! is_wp_error( $bavaria ) && ! is_wp_error( $hessen ) && $bavaria['term_id'] !== $hessen['term_id'], 'Same-name cities in two states remain distinct' );
    fw_city_check( is_wp_error( findewerkstatt_registration_location( 'Neustadt', 'bayern' ) ), 'Ambiguous shorthand requires full city name' );
    fw_city_check( is_wp_error( findewerkstatt_registration_location( 'München', 'hessen' ) ), 'Wrong state rejected' );
    $bremen = findewerkstatt_registration_location( 'Bremen', 'bremen' );
    fw_city_check( $bremen['term_id'] !== $bremen['state_id'], 'Bremen city remains separate from state and Bremerhaven' );
    $derne = findewerkstatt_registration_location( 'Derne', 'nordrhein-westfalen' );
    $dortmund = findewerkstatt_registration_location( 'Dortmund', 'nordrhein-westfalen' );
    fw_city_check( (int) get_term( $derne['term_id'] )->parent === $dortmund['term_id'], 'Derne belongs to Dortmund' );
    // Local test businesses verify exact-city, state and combined-filter results.
    $cities = array( $bavaria['term_id'], $hessen['term_id'] );
    foreach ( $cities as $id ) {
        $post_id = wp_insert_post( array( 'post_type' => 'mechanic', 'post_status' => 'publish', 'post_title' => 'FWCITYTEST ' . wp_generate_uuid4() ), true );
        fw_city_check( ! is_wp_error( $post_id ), 'Create fixture' ); $fixtures[] = $post_id;
        wp_set_object_terms( $post_id, array( $id ), 'mechanic_city' );
    }
    $local = new WP_Query( array( 'post_type' => 'mechanic', 'post_status' => 'publish', 'posts_per_page' => -1, 'tax_query' => array( array( 'taxonomy' => 'mechanic_city', 'field' => 'term_id', 'terms' => $bavaria['term_id'] ) ) ) );
    $local_ids = wp_list_pluck( $local->posts, 'ID' );
    fw_city_check( in_array( $fixtures[0], $local_ids, true ) && ! in_array( $fixtures[1], $local_ids, true ), 'City shows only its assigned firms' );
    $term = get_term( $bavaria['term_id'], 'mechanic_city' );
    $response = wp_remote_get( get_term_link( $term ), array( 'timeout' => 20 ) );
    fw_city_check( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ), 'Populated city serves HTTP 200' );
    $html = wp_remote_retrieve_body( $response );
    fw_city_check( strpos( $html, get_the_title( $fixtures[0] ) ) !== false && strpos( $html, get_the_title( $fixtures[1] ) ) === false, 'Rendered city lists its own firm and excludes other-state namesake' );
    fw_city_check( strpos( $html, '"@type":"CollectionPage"' ) !== false && strpos( $html, '"numberOfItems":1' ) !== false, 'Rendered populated city has factual CollectionPage and ItemList' );
    $actual_copy = findewerkstatt_location_content( $term, $local );
    fw_city_check( strpos( $html, esc_html( $actual_copy['copy']['intro'] ) ) !== false, 'Visible introduction uses actual published profile count' );
    fw_city_check( strpos( $html, esc_attr( $actual_copy['copy']['meta_description'] ) ) !== false, 'Meta description uses actual published profile count' );
    foreach ( $actual_copy['copy']['faqs'] as $entry ) {
        fw_city_check( strpos( $html, esc_html( $entry['question'] ) ) !== false && strpos( $html, esc_html( $entry['answer'] ) ) !== false, 'Five contextual FAQs are visible on populated city' );
    }
    $archive = get_post_type_archive_link( 'mechanic' );
    foreach ( array(
        array( 'fw_state' => 'bayern' ),
        array( 'fw_state' => 'bayern', 'fw_city' => $term->slug ),
        array( 'fw_city' => $term->slug ),
    ) as $search ) {
        $response = wp_remote_get( add_query_arg( $search, $archive ), array( 'timeout' => 20 ) );
        $search_html = wp_remote_retrieve_body( $response );
        fw_city_check( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response )
            && strpos( $search_html, get_the_title( $fixtures[0] ) ) !== false
            && strpos( $search_html, get_the_title( $fixtures[1] ) ) === false, 'State, dependent city and legacy search stay in the correct state' );
    }
    $response = wp_remote_get( add_query_arg( array( 'fw_state' => 'hessen', 'fw_city' => $term->slug ), $archive ), array( 'timeout' => 20 ) );
    $search_html = wp_remote_retrieve_body( $response );
    fw_city_check( ! is_wp_error( $response ) && strpos( $search_html, get_the_title( $fixtures[0] ) ) === false
        && strpos( $search_html, get_the_title( $fixtures[1] ) ) === false, 'A mismatched state and city never widens the search' );
    $service = get_term_by( 'slug', 'freie-werkstatt', 'service_type' );
    $brand = get_term_by( 'slug', 'bmw', 'car_brand' );
    wp_set_object_terms( $fixtures[0], array( (int) $service->term_id ), 'service_type' );
    wp_set_object_terms( $fixtures[0], array( (int) $brand->term_id ), 'car_brand' );
    $response = wp_remote_get( get_term_link( $term ), array( 'timeout' => 20 ) );
    $profile_html = wp_remote_retrieve_body( $response );
    fw_city_check( ! is_wp_error( $response ) && strpos( $profile_html, 'In den angezeigten Profilen werden folgende Leistungen genannt: ' . esc_html( $service->name ) ) !== false, 'Local guide cites only services from its actual displayed profiles' );
    fw_city_check( strpos( $profile_html, 'Genannte Fahrzeugmarken sind ' . esc_html( $brand->name ) ) !== false, 'Local guide cites actual displayed vehicle brands' );
    $response = wp_remote_get( FindeWerkstatt_Programmatic_SEO::get_combination_url( $service, $term ), array( 'timeout' => 20 ) );
    fw_city_check( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) && strpos( wp_remote_retrieve_body( $response ), get_the_title( $fixtures[0] ) ) !== false, 'Actual service-city route lists assigned firm' );
    $response = wp_remote_get( FindeWerkstatt_Programmatic_SEO::get_combination_url( $brand, $term ), array( 'timeout' => 20 ) );
    fw_city_check( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) && strpos( wp_remote_retrieve_body( $response ), get_the_title( $fixtures[0] ) ) !== false, 'Actual brand-city route lists assigned firm' );
    $wp_query = new WP_Query(); $wp_query->is_tax = true; $wp_query->queried_object = $term; $wp_query->set( 'mechanic_city', $term->slug ); $wp_query->found_posts = 1;
    fw_city_check( empty( FindeWerkstatt_City_Indexing::filter_robots( array() )['noindex'] ), 'Published firm removes city noindex' );
    FindeWerkstatt_City_Indexing::clear_request_cache();
    $sitemap_args = FindeWerkstatt_City_Indexing::filter_sitemap_query_args( array(), 'mechanic_city' );
    fw_city_check( ! in_array( $term->term_id, $sitemap_args['exclude'], true ), 'Populated city eligible for sitemap' );
    fw_city_test_cleanup(); $fixtures = array();
    $wp_query->found_posts = 0;
    fw_city_check( ! empty( FindeWerkstatt_City_Indexing::filter_robots( array() )['noindex'] ), 'Empty city noindex after fixture cleanup' );
    FindeWerkstatt_City_Indexing::clear_request_cache();
    $sitemap_args = FindeWerkstatt_City_Indexing::filter_sitemap_query_args( array(), 'mechanic_city' );
    fw_city_check( in_array( $term->term_id, $sitemap_args['exclude'], true ), 'Empty city absent from sitemap' );
    if ( ! empty( $argv[2] ) ) {
        $manifest = array();
        foreach ( $data['cities'] as $city ) {
            $place = get_term( $first['term_ids'][ $city['source_row'] ], 'mechanic_city' );
            $manifest[] = array( 'name' => $place->name, 'state' => $city['state_slug'], 'source_row' => $city['source_row'], 'url' => get_term_link( $place ), 'type' => $city['location_type'] );
        }
        file_put_contents( $argv[2], wp_json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
    }
    echo wp_json_encode( array( 'passed' => $checks, 'imported_rows' => $first['total'], 'created_locations' => $first['created'], 'reused_locations' => $first['reused'], 'second_run_created' => $second['created'], 'unresolved' => count( $data['unresolved'] ?? array() ), 'test_businesses_removed' => true, 'external_mail_sent' => 0 ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
} catch ( Throwable $error ) { fw_city_test_cleanup(); fwrite( STDERR, $error->getMessage() . "\n" ); exit( 1 ); }
