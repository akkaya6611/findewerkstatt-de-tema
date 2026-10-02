<?php
/** Standalone location-search regression checks; no database or network writes. */
if ( PHP_SAPI !== 'cli' ) { http_response_code( 404 ); exit; }
define( 'ABSPATH', __DIR__ . '/' );

class WP_Error {}
class WP_Term {
    public $term_id; public $slug; public $parent; public $taxonomy = 'mechanic_city';
    public function __construct( $id, $slug, $parent = 0 ) { $this->term_id = $id; $this->slug = $slug; $this->parent = $parent; }
}
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function wp_unslash( $value ) { return stripslashes( $value ); }
function sanitize_title( $value ) { return strtolower( trim( preg_replace( '/[^a-zA-Z0-9-]/', '', str_replace( ' ', '-', $value ) ), '-' ) ); }
function get_term( $id, $taxonomy ) { return $GLOBALS['terms'][ $id ] ?? null; }
function get_term_by( $field, $value, $taxonomy ) {
    foreach ( $GLOBALS['terms'] as $term ) {
        if ( $term->slug === $value && $term->taxonomy === $taxonomy ) { return $term; }
    }
    return false;
}
function findewerkstatt_normalize_city_slug( $slug ) { return $GLOBALS['aliases'][ $slug ] ?? $slug; }
require dirname( __DIR__ ) . '/inc/german-data.php';
require dirname( __DIR__ ) . '/inc/location-search.php';

$terms = array();
$states = array();
foreach ( FindeWerkstatt_German_Data::get_bundeslaender() as $land ) {
    $id = count( $terms ) + 1;
    $terms[ $id ] = new WP_Term( $id, $land['slug'] );
    $states[ $land['slug'] ] = $id;
}
$terms[100] = new WP_Term( 100, 'muenchen', $states['bayern'] );
$terms[101] = new WP_Term( 101, 'viertel', 100 );
$terms[102] = new WP_Term( 102, 'friedberg', $states['bayern'] );
$terms[103] = new WP_Term( 103, 'friedberg-hessen', $states['hessen'] );
$terms[104] = new WP_Term( 104, 'bremen-stadt', $states['bremen'] );
$terms[105] = new WP_Term( 105, 'bremerhaven', $states['bremen'] );
$terms[106] = new WP_Term( 106, 'berlin-mitte', $states['berlin'] );
$terms[107] = new WP_Term( 107, 'hamburg-altona', $states['hamburg'] );
$terms[108] = new WP_Term( 108, 'dortmund', $states['nordrhein-westfalen'] );
$terms[109] = new WP_Term( 109, 'derne', 108 );
$terms[110] = new WP_Term( 110, 'lk-dachau', $states['bayern'] );
$terms[111] = new WP_Term( 111, 'ort-im-landkreis', 110 );
$terms[112] = new WP_Term( 112, 'unbekanntes-gebiet' );
$terms[113] = new WP_Term( 113, 'verwaister-ort', 9999 );
$terms[114] = new WP_Term( 114, 'zyklus-a', 115 );
$terms[115] = new WP_Term( 115, 'zyklus-b', 114 );
$terms[116] = new WP_Term( 116, 'falsche-taxonomie', $states['bayern'] );
$terms[116]->taxonomy = 'service_type';
$aliases = array( 'munchen' => 'muenchen', 'altes-viertel' => 'viertel', 'missing-alias' => 'missing-target' );
$checks = 0;
function check_location( $expected, $input, $label ) {
    global $checks;
    ++$checks;
    $actual = findewerkstatt_resolve_location_filter( $input );
    if ( $actual !== $expected ) {
        throw new RuntimeException( $label . ': ' . json_encode( $actual ) );
    }
}
function valid_location( $state = '', $city = '', $location = '' ) {
    return array( 'state' => $state, 'city' => $city, 'location' => $location, 'valid' => true );
}
function invalid_location( $state = '' ) {
    return array( 'state' => $state, 'city' => '', 'location' => '__invalid__', 'valid' => false );
}

check_location( valid_location(), array(), 'Absent filters search nationally' );
check_location( valid_location(), array( 'fw_state' => '', 'fw_city' => '' ), 'Both optional selects empty' );
check_location( valid_location(), array( 'fw_state' => '  ', 'fw_city' => '  ' ), 'Whitespace-only selects are empty' );
check_location( valid_location(), array( 'fw_service' => 'inspektion' ), 'Unrelated filters do not restrict location' );
foreach ( $states as $slug => $id ) {
    check_location( valid_location( $slug, '', $slug ), array( 'fw_state' => $slug ), 'Every actual Bundesland can be searched alone' );
    $city = in_array( $slug, array( 'berlin', 'hamburg' ), true ) ? $slug : '';
    check_location( valid_location( $slug, $city, $slug ), array( 'fw_city' => $slug ), 'Old Bundesland-only fw_city link remains supported' );
}
check_location( valid_location( 'bayern', 'muenchen', 'muenchen' ), array( 'fw_state' => 'bayern', 'fw_city' => 'muenchen' ), 'Chosen city narrows the selected Bundesland' );
check_location( valid_location( 'bayern', 'muenchen', 'muenchen' ), array( 'fw_city' => 'muenchen' ), 'Legacy city link infers state' );
check_location( valid_location( 'bayern', 'viertel', 'viertel' ), array( 'fw_city' => 'viertel' ), 'Nested town district follows all parents' );
check_location( valid_location( 'nordrhein-westfalen', 'derne', 'derne' ), array( 'fw_state' => 'nordrhein-westfalen', 'fw_city' => 'derne' ), 'Imported district remains in its real state' );
check_location( valid_location( 'bayern', 'lk-dachau', 'lk-dachau' ), array( 'fw_city' => 'lk-dachau' ), 'Legacy region stays constrained to its region' );
check_location( valid_location( 'bayern', 'ort-im-landkreis', 'ort-im-landkreis' ), array( 'fw_city' => 'ort-im-landkreis' ), 'Town below Landkreis resolves its state' );
check_location( valid_location( 'bremen', 'bremen-stadt', 'bremen-stadt' ), array( 'fw_state' => 'bremen', 'fw_city' => 'bremen-stadt' ), 'Bremen city does not widen to whole state' );
check_location( valid_location( 'bremen', 'bremerhaven', 'bremerhaven' ), array( 'fw_city' => 'bremerhaven' ), 'Bremerhaven stays separate from Bremen city' );
check_location( valid_location( 'berlin', 'berlin', 'berlin' ), array( 'fw_state' => 'berlin', 'fw_city' => 'berlin' ), 'Berlin whole-city selection' );
check_location( valid_location( 'hamburg', 'hamburg', 'hamburg' ), array( 'fw_state' => 'hamburg', 'fw_city' => 'hamburg' ), 'Hamburg whole-city selection' );
check_location( valid_location( 'berlin', 'berlin-mitte', 'berlin-mitte' ), array( 'fw_city' => 'berlin-mitte' ), 'Berlin district remains selectable' );
check_location( valid_location( 'hamburg', 'hamburg-altona', 'hamburg-altona' ), array( 'fw_city' => 'hamburg-altona' ), 'Hamburg district remains selectable' );
check_location( valid_location( 'bayern', '', 'bayern' ), array( 'fw_state' => 'bayern', 'fw_city' => 'bayern' ), 'Same-state root submitted as city means statewide' );
check_location( valid_location( 'bayern', 'muenchen', 'muenchen' ), array( 'fw_city' => 'munchen' ), 'Old city slug is normalized' );
check_location( valid_location( 'bayern', 'viertel', 'viertel' ), array( 'fw_state' => 'bayern', 'fw_city' => 'altes-viertel' ), 'Nested district alias is normalized' );
check_location( valid_location( 'hessen', 'friedberg-hessen', 'friedberg-hessen' ), array( 'fw_state' => 'hessen', 'fw_city' => 'friedberg-hessen' ), 'Namesake city uses its distinct real term' );

check_location( invalid_location( 'hessen' ), array( 'fw_state' => 'hessen', 'fw_city' => 'muenchen' ), 'Wrong state-city pair cannot silently widen' );
check_location( invalid_location( 'hessen' ), array( 'fw_state' => 'hessen', 'fw_city' => 'friedberg' ), 'Wrong state namesake remains invalid' );
check_location( invalid_location( 'bayern' ), array( 'fw_state' => 'bayern', 'fw_city' => 'berlin' ), 'City-state cannot bypass different selected state' );
check_location( invalid_location( 'bayern' ), array( 'fw_state' => 'bayern', 'fw_city' => 'hessen' ), 'Two different root states are contradictory' );
check_location( invalid_location( 'bayern' ), array( 'fw_state' => 'bayern', 'fw_city' => 'not-a-city' ), 'Unknown city keeps invalid query constraint' );
check_location( invalid_location(), array( 'fw_city' => 'not-a-city' ), 'Unknown legacy city cannot search nationally' );
check_location( invalid_location(), array( 'fw_state' => 'muenchen' ), 'City cannot be supplied as Bundesland' );
check_location( invalid_location(), array( 'fw_state' => 'unknown-state', 'fw_city' => 'muenchen' ), 'Unknown state cannot be ignored in favor of valid city' );
check_location( invalid_location(), array( 'fw_city' => 'unbekanntes-gebiet' ), 'Unknown taxonomy root is not an official state' );
check_location( invalid_location(), array( 'fw_city' => 'verwaister-ort' ), 'Missing parent cannot be inferred' );
check_location( invalid_location(), array( 'fw_city' => 'zyklus-a' ), 'Cyclic parent graph fails without looping' );
check_location( invalid_location(), array( 'fw_city' => 'falsche-taxonomie' ), 'Wrong taxonomy slug cannot become location' );
check_location( invalid_location(), array( 'fw_city' => 'missing-alias' ), 'Alias with nonexistent target remains invalid' );
foreach ( array( array(), array( 'bayern' ), null, new stdClass() ) as $invalid ) {
    check_location( invalid_location(), array( 'fw_state' => $invalid ), 'Non-scalar state rejected' );
    check_location( invalid_location(), array( 'fw_state' => 'bayern', 'fw_city' => $invalid ), 'Non-scalar city rejected' );
}
check_location( invalid_location(), array( 'fw_state' => 123 ), 'Numeric state fails actual state lookup' );
check_location( invalid_location(), array( 'fw_city' => 123 ), 'Numeric city fails actual city lookup' );
check_location( invalid_location(), array( 'fw_city' => '!!!' ), 'Nonblank input sanitizing to empty cannot widen' );
check_location( invalid_location(), 'bayern', 'Resolver input must be an array' );

// Existing data alone is insufficient: the Bundesland must exist as an actual root.
$saved = $terms[ $states['bayern'] ];
unset( $terms[ $states['bayern'] ] );
check_location( invalid_location(), array( 'fw_state' => 'bayern' ), 'Missing root term cannot make a statewide query' );
check_location( invalid_location(), array( 'fw_city' => 'muenchen' ), 'City with missing state term fails safely' );
$terms[ $states['bayern'] ] = $saved;
$saved->parent = $states['hessen'];
check_location( invalid_location(), array( 'fw_state' => 'bayern' ), 'Known state slug below another state is not a root' );
$saved->parent = 0;

echo 'Passed ' . $checks . " location-search checks.\n";
