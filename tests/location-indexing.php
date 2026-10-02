<?php
/** Standalone checks for empty local directory indexing and sitemap eligibility. */
define( 'ABSPATH', __DIR__ . '/' );

function add_filter() {}
function add_action() {}
function is_404() { return $GLOBALS['location_test_404']; }
function is_tax() { return $GLOBALS['location_test_is_tax']; }
function get_option( $name ) { return 'blog_public' === $name ? $GLOBALS['location_test_public'] : null; }
function get_terms( $args ) {
    ++$GLOBALS['location_test_batches'];
    $GLOBALS['location_test_term_args'] = $args;
    return $GLOBALS['location_test_terms'];
}
function is_wp_error( $value ) { return false; }
function wp_parse_id_list( $values ) {
    if ( is_string( $values ) ) { $values = preg_split( '/[\s,]+/', $values ); }
    return array_values( array_unique( array_map( 'intval', (array) $values ) ) );
}

class FindeWerkstatt_Programmatic_SEO {
    public static function get_location_term( $query ) { return $GLOBALS['location_test_current']; }
}

function location_test_term( $id, $parent, $kind, $count = 0 ) {
    return (object) array( 'term_id' => $id, 'parent' => $parent, 'name' => $kind, 'taxonomy' => 'mechanic_city', 'count' => $count );
}

$location_test_terms = array(
    location_test_term( 1, 0, 'Bundesland' ),
    location_test_term( 2, 0, 'Berlin: Stadtstaat' ),
    location_test_term( 3, 0, 'Hamburg: Stadtstaat' ),
    location_test_term( 4, 1, 'Stadt ohne Importmetadaten' ),
    location_test_term( 5, 1, 'Landkreis' ),
    location_test_term( 6, 1, 'Region' ),
    location_test_term( 7, 2, 'Bezirk' ),
    location_test_term( 8, 4, 'Stadtteil' ),
    location_test_term( 9, 5, 'Belegte Stadt', 1 ),
    location_test_term( 10, 9, 'Leerer Stadtteil' ),
    location_test_term( 11, 3, 'Bezirk ohne Importmetadaten' ),
);
$location_test_batches = 0;
$location_test_public = true;
$location_test_404 = false;
$location_test_is_tax = true;
$location_test_current = null;
$wp_query = (object) array( 'found_posts' => 0 );
$location_test_checks = 0;

function location_check( $condition, $message ) {
    ++$GLOBALS['location_test_checks'];
    if ( ! $condition ) { throw new RuntimeException( $message ); }
}

require dirname( __DIR__ ) . '/inc/city-indexing.php';

foreach ( array( 4, 5, 6, 7, 8, 10, 11 ) as $id ) {
    $location_test_current = $location_test_terms[ $id - 1 ];
    $wp_query->found_posts = 0;
    $robots = FindeWerkstatt_City_Indexing::filter_robots( array( 'index' => true, 'max-image-preview' => 'large' ) );
    location_check( ! empty( $robots['noindex'] ) && empty( $robots['index'] ), 'Empty nonroot directory must be noindex: ' . $id );
    location_check( ! empty( $robots['follow'] ) && 'large' === $robots['max-image-preview'], 'Empty directory must preserve other robots directives: ' . $id );
    $wp_query->found_posts = 1;
    location_check( array() === FindeWerkstatt_City_Indexing::filter_robots( array() ), 'Populated local directory may be indexed: ' . $id );
}

foreach ( array( 1, 2, 3 ) as $id ) {
    $location_test_current = $location_test_terms[ $id - 1 ];
    $wp_query->found_posts = 0;
    location_check( array() === FindeWerkstatt_City_Indexing::filter_robots( array() ), 'Empty state navigation root remains eligible: ' . $id );
}

$location_test_current = $location_test_terms[3];
$location_test_public = false;
$robots = FindeWerkstatt_City_Indexing::filter_robots( array( 'noindex' => true, 'nofollow' => true ) );
location_check( ! empty( $robots['noindex'] ) && ! empty( $robots['nofollow'] ) && empty( $robots['follow'] ), 'Global staging/privacy robots restrictions take precedence.' );
$location_test_public = true;
$robots = FindeWerkstatt_City_Indexing::filter_robots( array( 'nofollow' => true ) );
location_check( ! empty( $robots['nofollow'] ) && empty( $robots['follow'] ), 'Explicit nofollow directive must not be reversed.' );

$location_test_404 = true;
location_check( array() === FindeWerkstatt_City_Indexing::filter_robots( array() ), '404 robots directives remain untouched.' );
$location_test_404 = false;
$location_test_is_tax = false;
location_check( array() === FindeWerkstatt_City_Indexing::filter_robots( array() ), 'Nontaxonomy robots directives remain untouched.' );
$location_test_is_tax = true;
$location_test_current = (object) array( 'taxonomy' => 'service_type', 'parent' => 1 );
location_check( array() === FindeWerkstatt_City_Indexing::filter_robots( array() ), 'Other taxonomies remain untouched.' );

$sitemap = FindeWerkstatt_City_Indexing::filter_sitemap_query_args( array( 'hide_empty' => true, 'exclude' => array( 99 ) ), 'mechanic_city' );
location_check( false === $sitemap['hide_empty'], 'State navigation roots are retained in sitemap query.' );
foreach ( array( 4, 6, 7, 8, 10, 11, 99 ) as $id ) {
    location_check( in_array( $id, $sitemap['exclude'], true ), 'Sitemap excludes every empty local directory and retains previous exclusions: ' . $id );
}
foreach ( array( 1, 2, 3, 5, 9 ) as $id ) {
    location_check( ! in_array( $id, $sitemap['exclude'], true ), 'Sitemap retains roots, populated cities and their ancestors: ' . $id );
}
FindeWerkstatt_City_Indexing::filter_sitemap_query_args( array(), 'mechanic_city' );
location_check( 1 === $location_test_batches, 'Sitemap eligibility uses one term batch per request.' );
location_check( false === $location_test_term_args['update_term_meta_cache'], 'Indexing does not load unused import metadata.' );

$filtered = FindeWerkstatt_City_Indexing::filter_sitemap_query_args( array( 'include' => '4,7,9' ), 'mechanic_city' );
location_check( array( 9 ) === $filtered['include'], 'Include filters cannot reintroduce empty directories.' );
$filtered = FindeWerkstatt_City_Indexing::filter_sitemap_query_args( array( 'include' => array( 4, 7 ) ), 'mechanic_city' );
location_check( array( 0 ) === $filtered['include'], 'Entirely excluded include lists use a no-results sentinel.' );
$other_args = array( 'hide_empty' => true, 'include' => array( 4 ) );
location_check( $other_args === FindeWerkstatt_City_Indexing::filter_sitemap_query_args( $other_args, 'service_type' ), 'Other sitemap taxonomies remain untouched.' );

$location_test_terms[7]->count = 1;
FindeWerkstatt_City_Indexing::clear_request_cache();
$sitemap = FindeWerkstatt_City_Indexing::filter_sitemap_query_args( array(), 'mechanic_city' );
location_check( ! in_array( 8, $sitemap['exclude'], true ) && ! in_array( 4, $sitemap['exclude'], true ), 'Newly populated district and its city return to the sitemap after cache invalidation.' );
location_check( 2 === $location_test_batches, 'Cache invalidation triggers exactly one fresh term batch.' );

echo $location_test_checks . " location indexing checks passed.\n";
