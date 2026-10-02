<?php
/**
 * Isolierte Regressionstests: php tests/seo-routing-schema.php
 * Keine WordPress-Datenbank und keine Änderungen an Profilen oder Permalinks.
 */
if ( PHP_SAPI !== 'cli' ) { http_response_code( 404 ); exit; }
define( 'ABSPATH', __DIR__ . '/' );
$hooks = array();
function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) { $GLOBALS['hooks'][] = array( $hook, $callback ); }
function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) { $GLOBALS['hooks'][] = array( $hook, $callback ); }
function add_rewrite_rule( $pattern, $query, $position ) { $GLOBALS['rules'][ $pattern ] = $query; }
class WP_Error {
    private $message;
    public function __construct( $code, $message ) { $this->message = $message; }
    public function get_error_message() { return $this->message; }
}
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function sanitize_title( $value ) { if ( ! is_string( $value ) ) { throw new TypeError( 'Slug muss eine Zeichenkette sein.' ); } return strtolower( trim( $value, '/' ) ); }
function is_admin() { return false; }
function is_404() { return $GLOBALS['wp_query']->is_404(); }
function is_tax() { return ! is_404() && (bool) $GLOBALS['wp_query']->tax; }
function is_singular( $type = '' ) { return false; }
function is_front_page() { return false; }
function is_post_type_archive( $type = '' ) { return false; }
function get_query_var( $key ) { return $GLOBALS['wp_query']->get( $key ); }
function get_template_directory() { return dirname( __DIR__ ); }
function home_url( $path ) { return 'https://example.test' . $path; }
function user_trailingslashit( $path ) { return rtrim( $path, '/' ) . '/'; }
function get_post_type_archive_link( $type ) { return home_url( '/werkstaetten/' ); }
function get_permalink( $id ) { return home_url( '/werkstatt/testbetrieb-' . $id . '/' ); }
function get_the_title( $id ) { return $GLOBALS['test_titles'][ $id ] ?? 'Testbetrieb ' . $id; }
function has_post_thumbnail( $id ) { return false; }
function get_post_meta( $id, $key, $single ) { return $GLOBALS['meta'][ $id ][ $key ] ?? ''; }
function get_term_meta( $id, $key, $single ) { return $GLOBALS['term_meta'][ $id ][ $key ] ?? ''; }
function get_option( $key ) { return 'blog_public' === $key ? ( $GLOBALS['test_blog_public'] ?? 1 ) : ''; }
function get_terms( $args ) { ++$GLOBALS['test_term_batches']; return array_values( array_filter( $GLOBALS['terms'], function( $term ) use ( $args ) { return $term->taxonomy === $args['taxonomy']; } ) ); }
function wp_parse_id_list( $ids ) { return array_map( 'intval', is_array( $ids ) ? $ids : explode( ',', $ids ) ); }
function trailingslashit( $value ) { return rtrim( $value, '/' ) . '/'; }
function esc_url( $value ) { return esc_attr( $value ); }
function get_ancestors( $id, $taxonomy, $type ) {
    $ancestors = array();
    while ( isset( $GLOBALS['terms'][ $id ] ) && $GLOBALS['terms'][ $id ]->parent ) {
        $id = $GLOBALS['terms'][ $id ]->parent;
        $ancestors[] = $id;
    }
    return $ancestors;
}
function get_term( $id, $taxonomy ) { return $GLOBALS['terms'][ $id ] ?? null; }
function get_term_by( $field, $slug, $taxonomy ) {
    foreach ( $GLOBALS['terms'] as $term ) {
        if ( $term->taxonomy === $taxonomy && $term->slug === $slug ) { return $term; }
    }
    return false;
}
function wp_get_post_terms( $id, $taxonomy ) { return $GLOBALS['assigned'][ $id ][ $taxonomy ] ?? array(); }
function wp_get_object_terms( $ids, $taxonomy, $args = array() ) {
    $terms = array();
    foreach ( $ids as $id ) { foreach ( wp_get_post_terms( $id, $taxonomy ) as $term ) { $terms[ $term->term_id ] = $term; } }
    return array_slice( array_values( $terms ), 0, $args['number'] ?? count( $terms ) );
}
function get_the_terms( $id, $taxonomy ) { return wp_get_post_terms( $id, $taxonomy ); }
function wp_list_pluck( $items, $key ) { return array_map( function( $item ) use ( $key ) { return $item->$key; }, $items ); }
function get_term_link( $term ) { return home_url( '/term/' . $term->slug . '/' ); }
function wp_json_encode( $value, $flags ) { return json_encode( $value, $flags ); }
function esc_attr( $value ) { return htmlspecialchars( $value, ENT_QUOTES ); }
function esc_html( $value ) { return htmlspecialchars( $value, ENT_QUOTES ); }
function esc_html__( $value, $domain ) { return esc_html( $value ); }
function current_user_can( $capability ) { return $GLOBALS['seo_admin_authorized'] ?? true; }
function check_admin_referer( $action ) { return $GLOBALS['seo_nonce_valid'] ?? true; }
function wp_nonce_field( $action ) {}
function wp_die( $message ) { throw new RuntimeException( $message ); }
function term_exists( $slug, $taxonomy ) {
    $GLOBALS['seo_checked_slugs'][] = $slug;
    $term = get_term_by( 'slug', $slug, $taxonomy );
    return $term ? array( 'term_id' => $term->term_id ) : 0;
}
function wp_insert_term( $name, $taxonomy, $args ) {
    $id = max( array_keys( $GLOBALS['terms'] ) ) + 1;
    $GLOBALS['terms'][ $id ] = new WP_Term( $id, $args['parent'] ?? 0, $args['slug'], $name, $taxonomy );
    $GLOBALS['seo_inserted_slugs'][] = $args['slug'];
    return array( 'term_id' => $id );
}
function findewerkstatt_normalize_city_slug( $slug ) { return 'muenchen-stadt' === $slug ? 'muenchen' : $slug; }
function findewerkstatt_city_directory_data() { return array( 'cities' => array() ); }
function findewerkstatt_city_directory_is_region( $term ) {
    return str_starts_with( $term->slug, 'lk-' ) || preg_match( '/^(?:Landkreis|Kreis|Region|Städteregion|Regionalverband)\b|(?:kreis|region)$/iu', $term->name );
}
function findewerkstatt_merge_city_terms() {
    $GLOBALS['seo_merge_calls'] = ( $GLOBALS['seo_merge_calls'] ?? 0 ) + 1;
    return $GLOBALS['seo_merge_result'] ?? array( 'merged' => 0, 'renamed' => 0 );
}
function flush_rewrite_rules( $hard ) { $GLOBALS['seo_flush_calls'] = ( $GLOBALS['seo_flush_calls'] ?? 0 ) + 1; }
class FindeWerkstatt_Germany_Geo_Hierarchy {
    public static function get_states() { return array( 'bayern' => array( 'name' => 'Bayern' ) ); }
    public static function get_districts_by_state() { return array( 'bayern' => array( 'muenchen-stadt' => 'München (Stadt)', 'region-neu' => 'Landkreis Neu' ) ); }
}
class WP_Term {
    public $term_id;
    public $parent;
    public $slug;
    public $name;
    public $taxonomy;
    public $count = 0;
    public function __construct( $id, $parent, $slug, $name, $taxonomy = 'mechanic_city' ) {
        $this->term_id = $id; $this->parent = $parent; $this->slug = $slug; $this->name = $name; $this->taxonomy = $taxonomy;
    }
}
class SEO_Test_Query {
    public $vars;
    public $tax = true;
    public $error = false;
    public $queried = null;
    public $posts = array();
    public $found_posts = 0;
    public function __construct( $vars ) { $this->vars = $vars; }
    public function get( $key ) { return $this->vars[ $key ] ?? ''; }
    public function set( $key, $value ) { $this->vars[ $key ] = $value; }
    public function is_main_query() { return true; }
    public function is_404() { return $this->error; }
    public function is_tax( $taxonomy ) { return $this->tax; }
    public function get_queried_object() { return $this->queried; }
    public function set_404() { $this->error = true; }
}
$terms = array(
    1 => new WP_Term( 1, 0, 'bayern', 'Bayern' ),
    2 => new WP_Term( 2, 1, 'muenchen', 'München' ),
    3 => new WP_Term( 3, 0, 'hessen', 'Hessen' ),
    4 => new WP_Term( 4, 2, 'viertel', 'Viertel' ),
    6 => new WP_Term( 6, 0, 'saarland', 'Saarland' ),
    7 => new WP_Term( 7, 1, 'lk-muenchen', 'Landkreis München' ),
    8 => new WP_Term( 8, 0, 'berlin', 'Berlin' ),
    9 => new WP_Term( 9, 8, 'berlin-mitte', 'Berlin Mitte' ),
    10 => new WP_Term( 10, 0, 'bremsen', 'Bremsenservice', 'service_type' ),
    11 => new WP_Term( 11, 0, 'bmw', 'BMW', 'car_brand' ),
    12 => new WP_Term( 12, 0, 'hamburg', 'Hamburg' ),
    13 => new WP_Term( 13, 12, 'hamburg-altona', 'Altona' ),
    14 => new WP_Term( 14, 7, 'beispielstadt', 'Beispielstadt (Stadt)' ),
);
$meta = array();
$assigned = array();
$term_meta = array();
$test_term_batches = 0;
$wp_query = new SEO_Test_Query( array() );
require dirname( __DIR__ ) . '/inc/helpers.php';
require dirname( __DIR__ ) . '/inc/german-data.php';
require dirname( __DIR__ ) . '/inc/location-copy.php';
require dirname( __DIR__ ) . '/inc/location-content.php';
require dirname( __DIR__ ) . '/inc/programmatic-seo.php';
require dirname( __DIR__ ) . '/inc/schema-seo.php';
require dirname( __DIR__ ) . '/inc/city-indexing.php';
$checks = 0;
function check( $condition, $message ) {
    ++$GLOBALS['checks'];
    if ( ! $condition ) { throw new RuntimeException( $message ); }
}
function route( $path, $old_vars = array( 'error' => '404' ) ) {
    $wp = (object) array( 'request' => $path, 'query_vars' => $old_vars );
    FindeWerkstatt_Programmatic_SEO::parse_request_interceptor( $wp );
    return $wp->query_vars;
}

$vars = route( 'service/bremsen/in/muenchen' );
check( ! isset( $vars['error'] ), 'Gültige Kombination entfernt eine alte Rewrite-404.' );
check( 'bremsen' === $vars['service_type'] && 'muenchen' === $vars['fw_pseo_location'], 'Leistung und Ort bleiben getrennt.' );
$vars = route( 'marke/bmw/in/muenchen/page/2' );
check( 2 === $vars['paged'] && 'bmw' === $vars['car_brand'], 'Marken-Pagination wird erkannt.' );
$vars = route( 'stadt/bayern/muenchen', array( 'fw_pseo_state' => 'bayern', 'fw_pseo_district' => 'muenchen' ) );
check( 'muenchen' === $vars['mechanic_city'] && ! isset( $vars['fw_pseo_district'] ), 'Alte Geo-Regeln werden zur echten Taxonomie.' );
check( ! isset( $vars['post_type'] ), 'Die Ortsroute bleibt eine Taxonomie und kein CPT-Archiv.' );
$vars = route( 'stadt/bayern/muenchen/viertel/page/3' );
check( 'viertel' === $vars['mechanic_city'] && 3 === $vars['paged'], 'Mehrstufige Ortspagination bewahrt die Elternkette.' );
$vars = route( 'stadt/bayern/muenchen/feed' );
check( 'muenchen' === $vars['mechanic_city'] && 'feed' === $vars['feed'], 'Taxonomie-Feeds bleiben auch bei alten Rewrite-Regeln erhalten.' );
check( '404' === route( 'stadt/hessen/muenchen' )['error'], 'Falsches Bundesland wird abgewiesen.' );
check( '404' === route( 'stadt/bayern/erfunden' )['error'], 'Erfundene Orte werden abgewiesen.' );
check( '404' === route( 'service/bremsen/in/erfunden' )['error'], 'Unbekannter Kombinationsort wird abgewiesen.' );
check( '404' === route( 'service/unbekannt/in/muenchen' )['error'], 'Unbekannte Leistungen werden abgewiesen.' );
check( '404' === route( 'service/bremsen/in/muenchen/page/0' )['error'], 'Ungültige Seitennummer wird abgewiesen.' );

$local_query = new SEO_Test_Query( array( 'service_type' => 'bremsen', 'fw_pseo_location' => 'muenchen' ) );
$wp_query = new SEO_Test_Query( array( 'service_type' => 'unbekannt', 'fw_pseo_location' => 'hessen' ) );
FindeWerkstatt_Programmatic_SEO::filter_pseo_queries( $local_query );
check( ! $local_query->is_404(), 'Filter liest die übergebene Abfrage statt globaler Query-Werte.' );
check( 'AND' === $local_query->get( 'tax_query' )['relation'], 'Leistung und Standort werden mit AND verknüpft.' );
check( 'service_type' === $local_query->get( 'tax_query' )[0]['taxonomy'] && 2 === $local_query->get( 'tax_query' )[1]['terms'], 'Die Leistung bleibt erste Taxonomie und der Ort ist konkret.' );
check( 'mechanic' === $local_query->get( 'post_type' ) && 12 === $local_query->get( 'posts_per_page' ), 'Kombinationen zeigen paginierte Werkstattprofile.' );
$wp_query = $local_query;
$wp_query->queried = $terms[2];
check( 'Bremsenservice' === FindeWerkstatt_Programmatic_SEO::get_query_term( 'service_type' )->name, 'Falscher globaler Taxonomie-Kontext ersetzt nicht die Leistung.' );
check( false !== strpos( FindeWerkstatt_Programmatic_SEO::template_include_handler( '/index.php' ), 'taxonomy-service_type.php' ), 'Kombination nutzt den normalen Template-Lader.' );
check( 'https://example.test/marke/bmw/in/muenchen/page/2/' === FindeWerkstatt_Programmatic_SEO::get_combination_url( $terms[11], $terms[2], 2 ), 'Kanonische paginierte Kombination bewahrt Marke und Ort.' );
check( ! method_exists( 'FindeWerkstatt_Programmatic_SEO', 'handle_direct_seeder_request' ), 'Öffentlicher Seeder ist entfernt.' );
$wp_query = new SEO_Test_Query( array( 'car_brand' => 'bmw', 'fw_pseo_location' => 'muenchen' ) );
$title_parts = FindeWerkstatt_Programmatic_SEO::filter_document_title( array( 'title' => 'BMW' ) );
check( 'BMW-Werkstätten in München' === $title_parts['title'], 'Marken-Titel verwendet die deutsche Zusammensetzung mit Bindestrich.' );
$wp_query = $local_query;

foreach ( array( 'fw_pseo_location', 'service_type', 'car_brand', 'mechanic_city' ) as $key ) {
    check( '404' === route( '', array( $key => array( 'berlin' ) ) )['error'], 'Array im öffentlichen Parameter ' . $key . ' wird vor dem WordPress-Query als 404 verworfen.' );
    $invalid_query = new SEO_Test_Query( array( 'service_type' => 'bremsen', 'fw_pseo_location' => 'muenchen', $key => array( 'berlin' ) ) );
    FindeWerkstatt_Programmatic_SEO::filter_pseo_queries( $invalid_query );
    check( $invalid_query->is_404() && array( 0 ) === $invalid_query->get( 'post__in' ), 'Array in einer später übergebenen Abfrage ' . $key . ' erzeugt keine PHP-Fatal oder Ergebnisse.' );
}
$invalid_query = new SEO_Test_Query( array( 'fw_pseo_location' => array( 'berlin' ) ) );
check( null === FindeWerkstatt_Programmatic_SEO::get_location_term( $invalid_query ), 'Location-Getter übergibt keine Arrays an die Slug-Sanitizer.' );
$invalid_query = new SEO_Test_Query( array( 'service_type' => array( 'bremsen' ) ) );
$invalid_query->queried = $terms[10];
check( null === FindeWerkstatt_Programmatic_SEO::get_query_term( 'service_type', $invalid_query ), 'Ungültige Quelle fällt nicht auf einen scheinbar gültigen globalen Term zurück.' );

$schema_method = new ReflectionMethod( 'FindeWerkstatt_Schema_SEO', 'get_autorepair_schema' );
$schema_method->setAccessible( true );
$test_titles[100] = 'Auto Müller &amp; Söhne &#8211; &#8222;Nord&#8220;';
$schema = $schema_method->invoke( null, 100 );
check( 'Auto Müller & Söhne – „Nord“' === $schema['name'], 'Firmenname im Schema entspricht dem sichtbaren Namen mit Sonderzeichen statt HTML-Entitäten.' );
unset( $test_titles[100] );
$schema = $schema_method->invoke( null, 100 );
foreach ( array( 'telephone', 'geo', 'openingHoursSpecification', 'aggregateRating', 'image', 'paymentAccepted', 'priceRange' ) as $field ) {
    check( ! isset( $schema[ $field ] ), 'Fehlende Angaben werden nicht als ' . $field . ' erfunden.' );
}
$meta[100] = array(
    '_mechanic_phone' => '089 / 1234567',
    '_mechanic_latitude' => '48.1',
    '_mechanic_longitude' => '11.5',
    '_mechanic_hours_weekday' => '09:30 - 12:00 / 13:00 - 17:00 Uhr',
    '_mechanic_hours_saturday' => 'geschlossen',
    '_mechanic_rating_avg' => '4,2',
    '_mechanic_rating_count' => '7',
);
$schema = $schema_method->invoke( null, 100 );
check( '+49891234567' === $schema['telephone'], 'Schema verwendet die tatsächliche normalisierte Rufnummer.' );
check( 48.1 === $schema['geo']['latitude'], 'Schema verwendet die gespeicherte Koordinate.' );
check( 2 === count( $schema['openingHoursSpecification'] ) && '09:30' === $schema['openingHoursSpecification'][0]['opens'], 'Gespeicherte Arbeitszeiten einschließlich Pause werden übernommen.' );
check( 4.2 === $schema['aggregateRating']['ratingValue'] && 7 === $schema['aggregateRating']['reviewCount'], 'Nur gespeicherte Bewertung und Anzahl werden ausgegeben.' );
$meta[100]['_mechanic_latitude'] = '999';
$meta[100]['_mechanic_rating_count'] = '';
$schema = $schema_method->invoke( null, 100 );
check( ! isset( $schema['geo'] ) && ! isset( $schema['aggregateRating'] ), 'Ungültige Koordinaten und unbekannte Bewertungsanzahl werden ausgelassen.' );

$term_meta[2] = array( '_fw_location_type' => 'city', '_fw_city_source_row' => 12, '_geo_lat' => '48.137', '_geo_lng' => '11.575', '_fw_city_coordinate_source' => 'https://example.test/verified-centre/' );
$term_meta[1] = array( '_geo_lat' => '48.137', '_geo_lng' => '11.575' );
$terms[5] = new WP_Term( 5, 3, 'frankfurt-am-main', 'Frankfurt am Main' );
$term_meta[5] = array( '_fw_location_type' => 'city', '_fw_city_source_row' => 42 );
$term_meta[4] = array( '_fw_location_type' => 'district', '_fw_city_source_row' => 43 );
$assigned[106]['mechanic_city'] = array( $terms[1], $terms[2] );
$address_schema = $schema_method->invoke( null, 106 )['address'];
check( 'München' === $address_schema['addressLocality'] && 'Bayern' === $address_schema['addressRegion'], 'Firmenadresse nutzt die tiefste Stadt und ihr tatsächliches Bundesland.' );
$assigned[107]['mechanic_city'] = array( $terms[4] );
$address_schema = $schema_method->invoke( null, 107 )['address'];
check( 'München' === $address_schema['addressLocality'] && 'Bayern' === $address_schema['addressRegion'], 'Ein Stadtteil wird nicht fälschlich als eigenständige Stadt ausgegeben.' );
$assigned[108]['mechanic_city'] = array( $terms[9] );
$address_schema = $schema_method->invoke( null, 108 )['address'];
check( 'Berlin' === $address_schema['addressLocality'] && 'Berlin' === $address_schema['addressRegion'], 'Bezirk Mitte verwendet Berlin als postalischen Ort.' );
$assigned[109]['mechanic_city'] = array( $terms[1] );
$address_schema = $schema_method->invoke( null, 109 )['address'];
check( ! isset( $address_schema['addressLocality'] ) && 'Bayern' === $address_schema['addressRegion'], 'Nur zugewiesenes Bundesland erzeugt keine erfundene Stadt.' );
$assigned[110]['mechanic_city'] = array( $terms[7] );
$address_schema = $schema_method->invoke( null, 110 )['address'];
check( ! isset( $address_schema['addressLocality'] ) && 'Bayern' === $address_schema['addressRegion'], 'Landkreis wird nicht als postalische Stadt ausgegeben.' );
$city_query = new SEO_Test_Query( array( 'mechanic_city' => 'muenchen', 'posts_per_page' => 12 ) );
$wp_query = $city_query;
$city_title = FindeWerkstatt_Schema_SEO::filter_city_document_title( array( 'title' => 'München', 'site' => 'FindeWerkstatt' ) );
check( 'Kfz-Werkstätten in München, Bayern' === $city_title['title'] && 'FindeWerkstatt' === $city_title['site'], 'Stadttitel nennt Werkstattsuche und tatsächliches Elterngebiet, ohne den Seitennamen zu entfernen.' );
$collection_method = new ReflectionMethod( 'FindeWerkstatt_Schema_SEO', 'get_city_collection_schema' );
$collection_method->setAccessible( true );
$collection = $collection_method->invoke( null, $terms[2], $city_query );
check( 'CollectionPage' === $collection['@type'] && 'de-DE' === $collection['inLanguage'], 'Ortsseite besitzt eine deutschsprachige CollectionPage.' );
check( 0 === $collection['mainEntity']['numberOfItems'] && array() === $collection['mainEntity']['itemListElement'], 'Leeres Ortsverzeichnis erhält keine erfundenen Schema-Betriebe.' );
check( 'City' === $collection['about']['@type'] && 'Bayern' === $collection['about']['containedInPlace']['name'], 'Importierte Stadt nennt ihren tatsächlichen Elternbegriff.' );
check( 48.137 === $collection['about']['geo']['latitude'], 'Separat verifizierte Stadtmittelpunkte werden übernommen.' );
$state_collection = $collection_method->invoke( null, $terms[1], $city_query );
check( 'AdministrativeArea' === $state_collection['about']['@type'] && ! isset( $state_collection['about']['geo'] ), 'Bundesland übernimmt keine alten Hauptstadtkoordinaten als eigene Position.' );
$district_collection = $collection_method->invoke( null, $terms[4], $city_query );
check( 'Place' === $district_collection['about']['@type'] && 'City' === $district_collection['about']['containedInPlace']['@type'], 'Stadtteil wird als Place mit tatsächlicher Stadtzuordnung beschrieben.' );
check( 'Bayern' === $district_collection['about']['containedInPlace']['containedInPlace']['name']
    && 'Deutschland' === $district_collection['about']['containedInPlace']['containedInPlace']['containedInPlace']['name'], 'Stadtteil-Schema erhält die vollständige Kette Stadt, Bundesland und Deutschland.' );
check( 'Kfz-Werkstätten im Stadtteil Viertel in München' === $district_collection['name'], 'Sammlungsname entspricht der sichtbaren grammatischen Standortüberschrift.' );
unset( $term_meta[2]['_fw_city_coordinate_source'] );
$collection = $collection_method->invoke( null, $terms[2], $city_query );
check( ! isset( $collection['about']['geo'] ), 'Gespeicherte Ortskoordinaten ohne belegte Quelle werden nicht als geprüft ausgegeben.' );
$term_meta[2]['_fw_city_coordinate_source'] = 'https://example.test/verified-centre/';
unset( $term_meta[2]['_geo_lng'] );
$collection = $collection_method->invoke( null, $terms[2], $city_query );
check( ! isset( $collection['about']['geo'] ), 'Unvollständige Stadtkoordinaten werden ausgelassen.' );
$term_meta[2]['_geo_lng'] = '11.575';
$city_query->posts = array( (object) array( 'ID' => 101, 'post_type' => 'mechanic', 'post_status' => 'publish' ) );
$city_query->found_posts = 13;
$city_query->set( 'paged', 2 );
$test_titles[101] = 'ATU Dresden &#8211; Nickern';
$collection = $collection_method->invoke( null, $terms[2], $city_query );
check( 'ATU Dresden – Nickern' === $collection['mainEntity']['itemListElement'][0]['item']['name'], 'Firmenname in der Ortsliste verwendet denselben lesbaren Gedankenstrich wie das Profil.' );
unset( $test_titles[101] );
check( 13 === $collection['mainEntity']['numberOfItems'] && 1 === count( $collection['mainEntity']['itemListElement'] ), 'Paginierte Liste nennt den tatsächlichen Gesamtbestand und beschreibt nur sichtbare Profile.' );
check( 13 === $collection['mainEntity']['itemListElement'][0]['position'] && str_ends_with( $collection['url'], '/page/2/' ), 'Schema erhält die richtige Seitennummer und fortlaufende Listenposition.' );
check( 'https://example.test/werkstatt/testbetrieb-101/' === $collection['mainEntity']['itemListElement'][0]['item']['url'], 'Listenelement verlinkt das tatsächliche Werkstattprofil.' );
$city_query->posts[] = (object) array( 'ID' => 102, 'post_type' => 'mechanic', 'post_status' => 'pending' );
$city_query->posts[] = (object) array( 'ID' => 103, 'post_type' => 'page', 'post_status' => 'publish' );
$city_query->posts[] = (object) array( 'ID' => 104, 'post_type' => 'mechanic', 'post_status' => 'publish' );
$meta[104]['_findewerkstatt_demo'] = 'yes';
$collection = $collection_method->invoke( null, $terms[2], $city_query );
check( 1 === count( $collection['mainEntity']['itemListElement'] ), 'Schema verwirft ausstehende Profile, Seiten und gekennzeichnete Demobetriebe.' );
ob_start(); FindeWerkstatt_Schema_SEO::render_json_ld_schemas(); $city_json = ob_get_clean();
check( false !== strpos( $city_json, 'CollectionPage' ) && false !== strpos( $city_json, 'FAQPage' ) && false !== strpos( $city_json, 'BreadcrumbList' ), 'Neue Sammlung bleibt mit sichtbaren FAQ und Breadcrumbs im JSON-LD erhalten.' );
preg_match( '#<script type="application/ld\+json">(.*?)</script>#s', $city_json, $json_match );
$city_schemas = json_decode( $json_match[1], true );
$faq_schema = array_values( array_filter( $city_schemas, function ( $schema ) { return 'FAQPage' === $schema['@type']; } ) )[0];
$visible_faqs = findewerkstatt_location_content( $terms[2], $city_query )['copy']['faqs'];
check( 5 === count( $faq_schema['mainEntity'] ), 'Alle fünf sichtbaren Standortfragen werden im FAQ-Schema abgebildet.' );
foreach ( $visible_faqs as $index => $faq ) {
    check( $faq['question'] === $faq_schema['mainEntity'][ $index ]['name'] && $faq['answer'] === $faq_schema['mainEntity'][ $index ]['acceptedAnswer']['text'], 'FAQ-Schema und sichtbarer Text stammen aus derselben Quelle.' );
}
check( 5 === count( FindeWerkstatt_Schema_SEO::get_city_faq_entries( 'München' ) ), 'Frühere FAQ-Aufrufe mit Stadtnamen bleiben unterstützt.' );
$legacy_faq = FindeWerkstatt_Schema_SEO::get_city_faq_entries( 'Berlin' );
check( false === strpos( $legacy_faq[0]['answer'], '13' ) && false === strpos( $legacy_faq[0]['answer'], 'kein Werkstattprofil' ), 'Ein alter Aufruf mit bloßem Ortsnamen übernimmt weder fremde Profilzahlen noch eine unbelegte Leerstandsaussage.' );
ob_start(); FindeWerkstatt_Schema_SEO::render_seo_meta_tags(); $city_meta = ob_get_clean();
check( false !== strpos( $city_meta, esc_attr( findewerkstatt_location_copy( findewerkstatt_location_context( $terms[2] ), 13 )['meta_description'] ) ), 'Metabeschreibung nutzt tatsächlichen Profilbestand und gemeinsame Standorttexte.' );
ob_start(); FindeWerkstatt_Schema_SEO::render_geo_meta_tags(); $city_geo = ob_get_clean();
check( false !== strpos( $city_geo, 'geo.position' ), 'Stadtseite verwendet einen verifizierten Ortsmittelpunkt.' );
$wp_query = new SEO_Test_Query( array( 'mechanic_city' => 'bayern' ) );
ob_start(); FindeWerkstatt_Schema_SEO::render_geo_meta_tags(); $state_geo = ob_get_clean();
check( false === strpos( $state_geo, 'geo.position' ), 'Bundeslandseite erzeugt keine Koordinate aus Hauptstadtmetadaten.' );

$wp_query = new SEO_Test_Query( array( 'mechanic_city' => 'saarland' ) );
check( 'Kfz-Werkstätten im Saarland' === FindeWerkstatt_Schema_SEO::filter_city_document_title( array() )['title'], 'Bundeslandtitel berücksichtigt die deutsche Form im Saarland.' );
$wp_query = new SEO_Test_Query( array( 'mechanic_city' => 'lk-muenchen' ) );
check( 'Kfz-Werkstätten im Landkreis München, Bayern' === FindeWerkstatt_Schema_SEO::filter_city_document_title( array() )['title'], 'Landkreistitel beschreibt das Gebiet mit korrekter Präposition.' );
$county_collection = $collection_method->invoke( null, $terms[7], $city_query );
check( 'AdministrativeArea' === $county_collection['about']['@type'], 'Landkreise erhalten ihren passenden Verwaltungsgebietstyp.' );
$term_meta[8] = array( '_geo_lat' => '52.52', '_geo_lng' => '13.405', '_fw_city_coordinate_source' => 'https://example.test/berlin-centre/' );
$city_state_collection = $collection_method->invoke( null, $terms[8], $city_query );
check( 'City' === $city_state_collection['about']['@type'] && 52.52 === $city_state_collection['about']['geo']['latitude'], 'Berlin wird als Stadtstaat mit separat verifizierten Stadtkoordinaten beschrieben.' );
$borough_collection = $collection_method->invoke( null, $terms[9], $city_query );
check( 'AdministrativeArea' === $borough_collection['about']['@type'] && 'Mitte' === $borough_collection['about']['name']
    && 'City' === $borough_collection['about']['containedInPlace']['@type'], 'Berlin Mitte wird als Bezirk mit bereinigtem Anzeigenamen und Berlin als Stadt beschrieben.' );
$wp_query = new SEO_Test_Query( array( 'mechanic_city' => 'berlin-mitte' ) );
check( 'Kfz-Werkstätten im Bezirk Mitte in Berlin' === FindeWerkstatt_Schema_SEO::filter_city_document_title( array() )['title'], 'Bezirkstitel verdoppelt den Namen des Stadtstaats nicht.' );
ob_start(); FindeWerkstatt_Schema_SEO::render_json_ld_schemas(); $borough_json = ob_get_clean();
preg_match( '#<script type="application/ld\+json">(.*?)</script>#s', $borough_json, $borough_match );
$borough_schemas = json_decode( $borough_match[1], true );
$borough_breadcrumbs = array_values( array_filter( $borough_schemas, function ( $schema ) { return 'BreadcrumbList' === $schema['@type']; } ) )[0];
check( 'Mitte' === end( $borough_breadcrumbs['itemListElement'] )['name'], 'Breadcrumbs verwenden denselben bereinigten Bezirkstitel wie die sichtbare Seite.' );
$legacy_city = $collection_method->invoke( null, $terms[14], $city_query );
check( 'City' === $legacy_city['about']['@type'] && 'Beispielstadt' === $legacy_city['about']['name'], 'Bestehende Stadtbegriffe ohne Typmarker werden als Stadt ohne verwaltungstechnischen Namenszusatz erkannt.' );
check( 'Landkreis München' === $legacy_city['about']['containedInPlace']['name'] && 'Bayern' === $legacy_city['about']['containedInPlace']['containedInPlace']['name'], 'Mehrstufiges Stadtschema bewahrt Landkreis und Bundesland.' );

$test_term_batches = 0;

$wp_query = new SEO_Test_Query( array( 'mechanic_city' => 'frankfurt-am-main' ) );
$robots = FindeWerkstatt_City_Indexing::filter_robots( array( 'max-image-preview' => 'large' ) );
check( ! empty( $robots['noindex'] ) && ! empty( $robots['follow'] ), 'Leere importierte Stadt bleibt crawlbar und trägt noindex,follow.' );
$wp_query->found_posts = 1;
$robots = FindeWerkstatt_City_Indexing::filter_robots( array() );
check( ! isset( $robots['noindex'] ) && ! isset( $robots['index'] ), 'Erstes veröffentlichtes Stadtprofil hebt den zusätzlichen noindex-Schalter ohne erzwungenes index auf.' );
$robots = FindeWerkstatt_City_Indexing::filter_robots( array( 'noindex' => true, 'nofollow' => true ) );
check( ! empty( $robots['noindex'] ) && ! empty( $robots['nofollow'] ), 'Bestehende globale Robots-Beschränkungen bleiben nach Veröffentlichung erhalten.' );
$wp_query = new SEO_Test_Query( array( 'service_type' => 'bremsen', 'fw_pseo_location' => 'frankfurt-am-main' ) );
check( array( 'title' => 'Bremsenservice in Frankfurt am Main' ) === FindeWerkstatt_Schema_SEO::filter_city_document_title( array( 'title' => 'Bremsenservice in Frankfurt am Main' ) ), 'Stadttitel-Filter ersetzt keine Leistungs-/Standorttitel.' );
$robots = FindeWerkstatt_City_Indexing::filter_robots( array() );
check( ! empty( $robots['noindex'] ), 'Leere Leistungs-/Stadtkombination wird ebenfalls nicht indexiert.' );
$wp_query = new SEO_Test_Query( array( 'mechanic_city' => 'viertel' ) );
check( ! empty( FindeWerkstatt_City_Indexing::filter_robots( array() )['noindex'] ), 'Importierter leerer Stadtteil erhält dieselbe Indexierungsregel.' );
$wp_query = new SEO_Test_Query( array( 'mechanic_city' => 'bayern' ) );
check( array() === FindeWerkstatt_City_Indexing::filter_robots( array() ), 'Bundesland mit Ortsnavigation bleibt auch ohne Werkstätten indexierbar.' );
$wp_query = new SEO_Test_Query( array( 'mechanic_city' => 'frankfurt-am-main' ) );
$test_blog_public = 0;
$robots = FindeWerkstatt_City_Indexing::filter_robots( array( 'noindex' => true, 'nofollow' => true ) );
check( ! empty( $robots['nofollow'] ) && ! isset( $robots['follow'] ), 'WordPress-Einstellung zum Ausschluss von Suchmaschinen wird nicht überschrieben.' );
$test_blog_public = 1;
$terms[4]->count = 1;
$terms[5]->count = 0;
FindeWerkstatt_City_Indexing::clear_request_cache();
$sitemap_args = FindeWerkstatt_City_Indexing::filter_sitemap_query_args( array( 'hide_empty' => true, 'exclude' => array( 99 ) ), 'mechanic_city' );
check( false === $sitemap_args['hide_empty'] && in_array( 99, $sitemap_args['exclude'], true ) && in_array( 5, $sitemap_args['exclude'], true ), 'Sitemap bewahrt vorherige Ausschlüsse und lässt leere importierte Stadtseiten weg.' );
check( ! in_array( 2, $sitemap_args['exclude'], true ) && ! in_array( 4, $sitemap_args['exclude'], true ) && ! in_array( 1, $sitemap_args['exclude'], true ), 'Sitemap erkennt Stadtbelegung durch einen tatsächlichen untergeordneten Ort und erhält die Bundeslandnavigation.' );
FindeWerkstatt_City_Indexing::filter_sitemap_query_args( array(), 'mechanic_city' );
check( 1 === $test_term_batches, 'Sitemap lädt alle Ortsbegriffe pro Request in einem Batch statt mit Einzelabfragen.' );
$terms[5]->count = 1;
FindeWerkstatt_City_Indexing::clear_request_cache();
$sitemap_args = FindeWerkstatt_City_Indexing::filter_sitemap_query_args( array(), 'mechanic_city' );
check( ! in_array( 5, $sitemap_args['exclude'], true ), 'Nach Term-Cache-Invalidierung erscheint die erstmals belegte Stadt wieder in der Sitemap.' );
$terms[5]->count = 0;
FindeWerkstatt_City_Indexing::clear_request_cache();
$sitemap_args = FindeWerkstatt_City_Indexing::filter_sitemap_query_args( array( 'include' => array( 5 ) ), 'mechanic_city' );
check( array( 0 ) === $sitemap_args['include'], 'Ein include-Filter kann noindex-Städte nicht entgegen den Sitemap-Ausschlüssen wieder einschließen.' );
$other_args = array( 'hide_empty' => true, 'exclude' => array( 99 ) );
check( $other_args === FindeWerkstatt_City_Indexing::filter_sitemap_query_args( $other_args, 'service_type' ), 'Andere Taxonomie-Sitemaps bleiben unverändert.' );

$_POST = array( 'seed_all_districts' => '1' );
$seo_checked_slugs = array();
$seo_inserted_slugs = array();
ob_start();
FindeWerkstatt_Programmatic_SEO::render_admin_generator_page();
$admin_html = ob_get_clean();
check( in_array( 'muenchen', $seo_checked_slugs, true ) && ! in_array( 'muenchen-stadt', $seo_inserted_slugs, true ), 'Admin-Generator respektiert bestehende Stadtslug-Aliase und erzeugt keine gelöschte Dublette.' );
check( array( 'region-neu' ) === $seo_inserted_slugs && 1 === $seo_merge_calls && 1 === $seo_flush_calls, 'Autorisierter Generator ergänzt neue Regionen und führt danach die konservative Migration aus.' );
$seo_merge_result = new WP_Error( 'test_failure', '<fehlgeschlagen>' );
ob_start();
FindeWerkstatt_Programmatic_SEO::render_admin_generator_page();
$admin_html = ob_get_clean();
check( 2 === $seo_merge_calls && 1 === $seo_flush_calls && false !== strpos( $admin_html, 'notice-error' ) && false !== strpos( $admin_html, '&lt;fehlgeschlagen&gt;' ), 'Migration-Fehler wird sicher angezeigt und verhindert die anschließende Rewrite-Aktualisierung.' );
$seo_nonce_valid = false;
ob_start();
FindeWerkstatt_Programmatic_SEO::render_admin_generator_page();
ob_end_clean();
check( 2 === $seo_merge_calls && 1 === count( $seo_inserted_slugs ), 'Ohne gültige Nonce werden weder Terme erzeugt noch Migrationen ausgeführt.' );
$seo_admin_authorized = false;
$denied = false;
try { FindeWerkstatt_Programmatic_SEO::render_admin_generator_page(); } catch ( RuntimeException $error ) { $denied = true; }
check( $denied && 2 === $seo_merge_calls, 'Fehlende Administratorrechte verhindern sämtliche Generator-Aktionen.' );
echo 'SEO-Routing und Schema: ' . $checks . " Prüfungen erfolgreich.\n";

