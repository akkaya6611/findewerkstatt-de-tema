<?php
/** Standalone location-context/provenance checks; no database or network writes. */
if ( PHP_SAPI !== 'cli' ) { http_response_code( 404 ); exit; }
define( 'ABSPATH', __DIR__ . '/' );

class WP_Error {}
class WP_Term {
    public $term_id; public $parent; public $slug; public $name; public $taxonomy = 'mechanic_city';
    public function __construct( $id, $parent, $slug, $name ) {
        $this->term_id = $id; $this->parent = $parent; $this->slug = $slug; $this->name = $name;
    }
}
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function get_terms( $args ) {
    ++$GLOBALS['term_batches'];
    $terms = array_values( $GLOBALS['terms'] );
    usort( $terms, function ( $a, $b ) { return strcmp( $a->name, $b->name ); } );
    return $terms;
}
function get_term_meta( $id, $key, $single ) { return $GLOBALS['term_meta'][ $id ][ $key ] ?? ''; }
function get_post_meta( $id, $key, $single ) { return $GLOBALS['post_meta'][ $id ][ $key ] ?? ''; }
function wp_list_pluck( $values, $key ) { return array_map( function ( $value ) use ( $key ) { return $value->$key; }, $values ); }
function wp_get_object_terms( $ids, $taxonomy, $args ) {
    $GLOBALS['object_term_calls'][] = array( 'ids' => $ids, 'taxonomy' => $taxonomy );
    if ( $GLOBALS['object_terms_error'] ) { return new WP_Error(); }
    $labels = array();
    foreach ( $ids as $id ) {
        foreach ( $GLOBALS['object_terms'][ $id ][ $taxonomy ] ?? array() as $label ) { $labels[ $label ] = $label; }
    }
    sort( $labels );
    return array_map( function ( $label ) { return (object) array( 'name' => $label ); }, array_slice( $labels, 0, $args['number'] ) );
}
function remove_accents( $value ) { return strtr( $value, array( 'ä' => 'a', 'ö' => 'o', 'ü' => 'u', 'Ä' => 'A', 'Ö' => 'O', 'Ü' => 'U', 'ß' => 's' ) ); }
function findewerkstatt_city_directory_data() { return $GLOBALS['dataset']; }
function findewerkstatt_city_directory_is_region( $term ) {
    return str_starts_with( $term->slug, 'lk-' ) || preg_match( '/^(?:Landkreis|Kreis|Region|Städteregion|Regionalverband)\b|(?:kreis|region)$/iu', $term->name );
}
function findewerkstatt_location_label( $term ) {
    return str_starts_with( $term->slug, 'lk-' ) && ! preg_match( '/kreis|region|regionalverband/iu', $term->name ) ? 'Landkreis ' . $term->name : $term->name;
}
require dirname( __DIR__ ) . '/inc/german-data.php';
require dirname( __DIR__ ) . '/inc/location-copy.php';
require dirname( __DIR__ ) . '/inc/location-content.php';

$checks = 0; $term_batches = 0; $terms = array(); $term_meta = array();
$post_meta = array(); $object_terms = array(); $object_term_calls = array(); $object_terms_error = false;
$dataset = array( 'verified_source_url' => 'https://www.destatis.de/fixture', 'verified_source_date' => '2024-12-31', 'cities' => array() );
function content_check( $condition, $message ) {
    ++$GLOBALS['checks'];
    if ( ! $condition ) { throw new RuntimeException( $message ); }
}
function content_term( $id, $parent, $slug, $name, $meta = array() ) {
    $GLOBALS['terms'][ $id ] = new WP_Term( $id, $parent, $slug, $name );
    $GLOBALS['term_meta'][ $id ] = $meta;
}
function content_source( $id, $row, $state, $ags, $lat, $lng, $extra = array() ) {
    $GLOBALS['term_meta'][ $id ]['_fw_city_source_row'] = $row;
    $GLOBALS['term_meta'][ $id ]['_fw_city_ags'] = $ags;
    $GLOBALS['dataset']['cities'][] = array_merge( array( 'source_row' => $row, 'state_slug' => $state, 'ags' => $ags, 'lat' => $lat, 'lng' => $lng ), $extra );
}

// All fixtures precede the first call: the implementation deliberately caches the hierarchy/source table.
content_term( 1, 0, 'bayern', 'Bayern', array( '_geo_lat' => 48.137, '_geo_lng' => 11.575 ) );
content_term( 2, 0, 'nordrhein-westfalen', 'Nordrhein-Westfalen' );
content_term( 3, 0, 'hessen', 'Hessen' );
content_term( 4, 0, 'berlin', 'Berlin' );
content_term( 5, 0, 'hamburg', 'Hamburg' );
content_term( 6, 0, 'bremen', 'Bremen' );
content_term( 7, 0, 'saarland', 'Saarland' );
content_term( 10, 1, 'lk-muenchen', 'München' );
content_term( 11, 1, 'region-nuernberg', 'Region Nürnberg' );
content_term( 12, 2, 'lk-staedteregion-aachen', 'Städteregion Aachen' );
content_term( 13, 1, 'alb-donau-kreis', 'Alb-Donau-Kreis' );
content_term( 14, 1, 'lk-nuernberger-land', 'Nürnberger Land' );
content_term( 20, 1, 'muenchen', 'München', array( '_fw_location_type' => 'city', '_geo_lat' => 48.137, '_geo_lng' => 11.575 ) );
content_source( 20, 100, 'bayern', '09162000', 48.1351, 11.582 );
content_term( 21, 10, 'dachau', 'Dachau', array( '_fw_location_type' => 'city' ) );
content_source( 21, 101, 'bayern', '09174115', 48.26, 11.4333 );
content_term( 22, 11, 'nuernberg', 'Nürnberg', array( '_fw_location_type' => 'city' ) );
content_source( 22, 102, 'bayern', '09564000', 49.45, 11.07 );
content_term( 23, 1, 'regensburg', 'Regensburg' );
content_source( 23, 103, 'bayern', '09362000', 49.01, 12.1 );
content_term( 24, 1, 'ort-ohne-koordinaten', 'Ort ohne Koordinaten' );
content_term( 30, 2, 'dortmund', 'Dortmund' );
content_source( 30, 110, 'nordrhein-westfalen', '05913000', 51.5136, 7.4653 );
content_term( 31, 30, 'derne', 'Derne', array( '_fw_location_type' => 'district' ) );
content_source( 31, 111, 'nordrhein-westfalen', null, null, null, array( 'parent_ags' => '05913000', 'verified_source_url' => 'https://www.dortmund.de/fixture' ) );
content_term( 40, 3, 'hanau', 'Hanau' );
content_source( 40, 120, 'hessen', '06435014', 50.133, 8.917 );
content_term( 41, 40, 'steinheim-am-main', 'Steinheim am Main', array( '_fw_location_type' => 'district' ) );
content_source( 41, 121, 'hessen', null, null, null, array( 'parent_ags' => '06435014' ) );
content_term( 42, 30, 'falscher-stadtteil', 'Falscher Stadtteil', array( '_fw_location_type' => 'district' ) );
content_source( 42, 122, 'nordrhein-westfalen', null, 51.5, 7.4, array( 'parent_ags' => '00000000' ) );
content_term( 43, 3, 'falsches-bundesland', 'Falsches Bundesland', array( '_fw_city_source_row' => 100, '_fw_city_ags' => '09162000' ) );
content_term( 44, 1, 'falscher-ags', 'Falscher AGS', array( '_fw_city_source_row' => 100, '_fw_city_ags' => '00000000' ) );
content_term( 45, 1, 'falsche-quellzeile', 'Falsche Quellzeile', array( '_fw_city_source_row' => 9999 ) );
content_term( 46, 1, 'invalides-quellzentrum', 'Invalides Quellzentrum' );
content_source( 46, 123, 'bayern', '00000123', 999, 11 );
content_term( 50, 1, 'manuell-bestaetigt', 'Manuell bestätigt', array( '_fw_city_coordinate_source' => 'https://www.example.org/centre', '_geo_lat' => '48.2', '_geo_lng' => '11.6' ) );
content_term( 51, 1, 'unbelegt', 'Unbelegt', array( '_geo_lat' => 48.2, '_geo_lng' => 11.6 ) );
content_term( 52, 1, 'ausserhalb', 'Außerhalb', array( '_fw_city_coordinate_source' => 'https://www.example.org/centre', '_geo_lat' => 46, '_geo_lng' => 11.6 ) );
content_term( 53, 1, 'unvollstaendig', 'Unvollständig', array( '_fw_city_coordinate_source' => 'https://www.example.org/centre', '_geo_lat' => 48.2 ) );
content_term( 60, 4, 'berlin-mitte', 'Berlin Mitte' );
content_term( 61, 4, 'berlin-pankow', 'Pankow' );
content_term( 62, 5, 'hamburg-altona', 'Altona' );
content_term( 63, 5, 'hamburg-eimsbuettel', 'Eimsbüttel' );
content_term( 64, 5, 'hamburg-harburg', 'Harburg' );
content_source( 4, 130, 'berlin', '11000000', 52.52, 13.405 );
content_source( 5, 131, 'hamburg', '02000000', 53.5511, 9.9937 );
content_term( 65, 6, 'bremen-stadt', 'Bremen (Stadt)' );
content_term( 70, 9999, 'verwaist', 'Verwaist' );
content_term( 71, 72, 'zyklus-a', 'Zyklus A' );
content_term( 72, 71, 'zyklus-b', 'Zyklus B' );

foreach ( array( 1 => 'state', 4 => 'city_state', 5 => 'city_state', 6 => 'state', 10 => 'county', 11 => 'region', 12 => 'region', 13 => 'county', 14 => 'county', 20 => 'city', 21 => 'city', 31 => 'district', 60 => 'borough', 62 => 'borough', 65 => 'city' ) as $id => $kind ) {
    content_check( $kind === findewerkstatt_location_context( $terms[ $id ] )['kind'], 'Administrative classification for ' . $terms[ $id ]->name );
}
content_check( 'Landkreis München' === findewerkstatt_location_context( $terms[10] )['name'], 'Short Landkreis names retain their administrative type.' );
content_check( 'Mitte' === findewerkstatt_location_context( $terms[60] )['name'], 'Official Berlin borough display name is Mitte.' );
content_check( 'Bremen' === findewerkstatt_location_context( $terms[65] )['name'], 'City marker is removed from the public label.' );
$dachau = findewerkstatt_location_context( $terms[21] );
content_check( 1 === $dachau['state']->term_id && 10 === $dachau['parent']->term_id, 'A town below Landkreis follows the full state hierarchy.' );
$derne = findewerkstatt_location_context( $terms[31] );
content_check( 'Dortmund' === $derne['parent_name'] && 'Nordrhein-Westfalen' === $derne['state_name'], 'District context includes actual parent city and state.' );
content_check( 'Kfz-Werkstätten im Stadtteil Derne in Dortmund' === findewerkstatt_location_content( $terms[31], (object) array() )['copy']['title'], 'District German title uses the actual relationship.' );
content_check( 'Kfz-Werkstätten im Bezirk Altona in Hamburg' === findewerkstatt_location_content( $terms[62], (object) array() )['copy']['title'], 'Hamburg title uses Bezirk rather than Stadtteil.' );
content_check( ! in_array( 'Landkreis München', findewerkstatt_location_context( $terms[1] )['child_names'], true ), 'State introductory town examples exclude counties.' );
content_check( in_array( 'Mitte', findewerkstatt_location_context( $terms[4] )['child_names'], true ), 'City-state context includes actual borough names.' );
foreach ( array( 70, 71, 72 ) as $id ) { content_check( null === findewerkstatt_location_context( $terms[ $id ] )['state'], 'Missing/cyclic parents never invent a state.' ); }
content_check( 1 === $term_batches, 'Hierarchy is loaded once for all contexts.' );

$munich_geo = findewerkstatt_location_geo( $terms[20] );
content_check( 48.1351 === $munich_geo['lat'] && 11.582 === $munich_geo['lng'], 'Verified source coordinates override older unverified metadata.' );
content_check( 48.137 === $term_meta[20]['_geo_lat'] && 11.575 === $term_meta[20]['_geo_lng'], 'Coordinate presentation leaves existing metadata unchanged.' );
content_check( 'https://www.destatis.de/fixture' === $munich_geo['source_url'] && '2024-12-31' === $munich_geo['source_date'], 'Published coordinates retain source and reference date.' );
content_check( null === findewerkstatt_location_geo( $terms[1] ), 'State capital coordinates are never labelled as the whole-state centre.' );
content_check( null !== findewerkstatt_location_geo( $terms[4] ) && null !== findewerkstatt_location_geo( $terms[5] ), 'Verified city-state centres remain available.' );
content_check( 'https://www.dortmund.de/fixture' === findewerkstatt_location_source_record( $terms[31] )['source_url'], 'District source overrides the general dataset source.' );
foreach ( array( 31, 41, 42, 43, 44, 45, 46, 51, 52, 53, 60 ) as $id ) { content_check( null === findewerkstatt_location_geo( $terms[ $id ] ), 'No fabricated/invalid centre for fixture ' . $id ); }
foreach ( array( 42, 43, 44, 45 ) as $id ) { content_check( null === findewerkstatt_location_source_record( $terms[ $id ] ), 'Source mapping requires consistent row, state, AGS and parent AGS: fixture ' . $id ); }
content_check( null !== findewerkstatt_location_source_record( $terms[41] ), 'A district with matching parent AGS retains its verified hierarchy even without centre coordinates.' );
$manual = findewerkstatt_location_geo( $terms[50] );
content_check( 48.2 === $manual['lat'] && 11.6 === $manual['lng'] && '' === $manual['source_date'], 'Separately documented coordinates may be used without inventing a source date.' );
foreach ( array( array( '48.2', '11.6', true ), array( 47, 5, true ), array( 56, 16, true ), array( 46.99, 11, false ), array( 48, 16.1, false ), array( null, 11, false ), array( 'NaN', 11, false ), array( NAN, 11, false ), array( INF, 11, false ) ) as $case ) {
    content_check( $case[2] === findewerkstatt_location_valid_geo( $case[0], $case[1] ), 'Coordinate bounds reject incomplete, non-finite and foreign centres.' );
}

$query = (object) array( 'found_posts' => 2, 'posts' => array(
    (object) array( 'ID' => 1001, 'post_type' => 'mechanic', 'post_status' => 'publish' ),
    (object) array( 'ID' => 1002, 'post_type' => 'mechanic', 'post_status' => 'publish' ),
    (object) array( 'ID' => 1003, 'post_type' => 'mechanic', 'post_status' => 'draft' ),
    (object) array( 'ID' => 1004, 'post_type' => 'mechanic', 'post_status' => 'publish' ),
    (object) array( 'ID' => 1005, 'post_type' => 'post', 'post_status' => 'publish' ),
) );
$post_meta[1004]['_findewerkstatt_demo'] = 'yes';
$object_terms[1001] = array( 'service_type' => array( 'Reifenservice' ), 'car_brand' => array( 'BMW' ) );
$object_terms[1002] = array( 'service_type' => array( 'Inspektion', 'Reifenservice' ), 'car_brand' => array( 'Volkswagen' ) );
foreach ( array( 1003, 1004, 1005 ) as $id ) { $object_terms[ $id ] = array( 'service_type' => array( 'Erfundene Leistung' ), 'car_brand' => array( 'Erfundene Marke' ) ); }
$content = findewerkstatt_location_content( $terms[20], $query );
content_check( array( 'Inspektion', 'Reifenservice' ) === $content['context']['service_names'], 'Services come only from displayed published real workshop profiles.' );
content_check( array( 'BMW', 'Volkswagen' ) === $content['context']['brand_names'], 'Brands come only from displayed published real workshop profiles.' );
content_check( array( 1001, 1002 ) === $object_term_calls[0]['ids'], 'Drafts, demo records and unrelated post types cannot contribute capability claims.' );
content_check( str_contains( $content['copy']['intro'], '2 veröffentlichte Werkstattprofile' ), 'Visible copy uses the actual filtered query count.' );
content_check( str_contains( $content['copy']['sections'][1]['text'], 'Inspektion und Reifenservice' ), 'Actual profile capabilities appear in directory help content.' );
$object_terms_error = true;
$without_labels = findewerkstatt_location_content( $terms[20], $query );
content_check( array() === $without_labels['context']['service_names'] && array() === $without_labels['context']['brand_names'], 'Taxonomy errors omit optional capability labels safely.' );
$empty = findewerkstatt_location_content( $terms[20], (object) array( 'posts' => array(), 'found_posts' => 0 ) );
content_check( str_contains( $empty['copy']['intro'], 'noch kein Werkstattprofil veröffentlicht' ), 'Empty directory states availability honestly.' );
content_check( array() === $empty['context']['service_names'] && ! str_contains( $empty['copy']['sections'][1]['text'], 'Reifenservice' ), 'Empty pages never claim local capability inventory.' );

$related = findewerkstatt_location_related_terms( findewerkstatt_location_context( $terms[20] ), 50 );
$related_ids = array_map( function ( $item ) { return $item['term']->term_id; }, $related );
content_check( array( 50, 21, 23, 22 ) === array_slice( $related_ids, 0, 4 ), 'Nearby verified centres order same-state city links ahead of unverified centres.' );
foreach ( $related as $item ) {
    $context = findewerkstatt_location_context( $item['term'] );
    content_check( 'city' === $context['kind'] && 1 === $context['state']->term_id && 20 !== $item['term']->term_id, 'Related town links never include another state, counties or the current page.' );
}
content_check( 2 === count( findewerkstatt_location_related_terms( findewerkstatt_location_context( $terms[20] ), 2 ) ), 'Related links respect the requested display limit.' );
content_check( array() === findewerkstatt_location_related_terms( findewerkstatt_location_context( $terms[1] ) ), 'State overview does not suggest unrelated town lists.' );
content_check( array() === findewerkstatt_location_related_terms( findewerkstatt_location_context( $terms[4] ) ), 'City-state overview already owns its borough navigation.' );
$boroughs = findewerkstatt_location_related_terms( findewerkstatt_location_context( $terms[62] ), 50 );
content_check( array( 63, 64 ) === array_map( function ( $item ) { return $item['term']->term_id; }, $boroughs ), 'Borough links show only siblings within the same city, ordered by name when centres are unverified.' );
foreach ( array( 62 => array( 53.555, 9.943 ), 63 => array( 53.565, 9.967 ), 64 => array( 53.45, 9.96 ) ) as $id => $centre ) {
    $term_meta[ $id ]['_fw_city_coordinate_source'] = 'https://www.hamburg.de/fixture';
    $term_meta[ $id ]['_geo_lat'] = $centre[0]; $term_meta[ $id ]['_geo_lng'] = $centre[1];
}
$verified_boroughs = findewerkstatt_location_related_terms( findewerkstatt_location_context( $terms[62] ), 50 );
content_check( 63 === $verified_boroughs[0]['term']->term_id && 64 === $verified_boroughs[1]['term']->term_id
    && is_finite( $verified_boroughs[0]['distance'] ) && $verified_boroughs[0]['distance'] < $verified_boroughs[1]['distance'], 'Separately verified borough centres order nearby siblings without crossing city boundaries.' );
content_check( array() === findewerkstatt_location_related_terms( findewerkstatt_location_context( $terms[70] ) ), 'Orphan location never invents nearby recommendations.' );
echo 'Passed ' . $checks . " location-content checks.\n";
