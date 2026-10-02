<?php
/** Pure German directory-copy checks; no WordPress database required. */
define( 'ABSPATH', __DIR__ );
require dirname( __DIR__ ) . '/inc/location-copy.php';

$checks = 0;
function copy_check( $condition, $message ) {
    global $checks;
    ++$checks;
    if ( ! $condition ) { fwrite( STDERR, 'FAIL: ' . $message . "\n" ); exit( 1 ); }
}
function copy_text( $copy ) {
    $text = $copy['title'] . ' ' . $copy['intro'] . ' ' . $copy['meta_description'] . ' ' . $copy['cta_heading'] . ' ' . $copy['cta_text'];
    foreach ( $copy['sections'] as $section ) { $text .= ' ' . $section['heading'] . ' ' . $section['text']; }
    foreach ( $copy['faqs'] as $faq ) { $text .= ' ' . $faq['question'] . ' ' . $faq['answer']; }
    return $text;
}

$phrases = array(
    array( array( 'kind' => 'state', 'name' => 'Bayern' ), 'in Bayern' ),
    array( array( 'kind' => 'state', 'name' => 'Saarland' ), 'im Saarland' ),
    array( array( 'kind' => 'state', 'name' => 'Bremen' ), 'im Bundesland Bremen' ),
    array( array( 'kind' => 'city', 'name' => 'Bremen' ), 'in Bremen' ),
    array( array( 'kind' => 'city_state', 'name' => 'Berlin' ), 'in Berlin' ),
    array( array( 'kind' => 'city', 'name' => 'München' ), 'in München' ),
    array( array( 'kind' => 'county', 'name' => 'Landkreis München' ), 'im Landkreis München' ),
    array( array( 'kind' => 'county', 'name' => 'Alb-Donau-Kreis' ), 'im Alb-Donau-Kreis' ),
    array( array( 'kind' => 'county', 'name' => 'Märkischer Kreis' ), 'im Märkischen Kreis' ),
    array( array( 'kind' => 'county', 'name' => 'Oberbergischer Kreis' ), 'im Oberbergischen Kreis' ),
    array( array( 'kind' => 'county', 'name' => 'Rheinisch-Bergischer Kreis' ), 'im Rheinisch-Bergischen Kreis' ),
    array( array( 'kind' => 'county', 'name' => 'Altenburger Land' ), 'im Altenburger Land' ),
    array( array( 'kind' => 'county', 'name' => 'Städteregion Aachen' ), 'in der Städteregion Aachen' ),
    array( array( 'kind' => 'region', 'name' => 'Region Hannover' ), 'in der Region Hannover' ),
    array( array( 'kind' => 'region', 'name' => 'Städteregion Aachen' ), 'in der Städteregion Aachen' ),
    array( array( 'kind' => 'region', 'name' => 'Regionalverband Saarbrücken' ), 'im Regionalverband Saarbrücken' ),
    array( array( 'kind' => 'county', 'name' => 'Uckermark' ), 'für das Gebiet Uckermark' ),
    array( array( 'kind' => 'borough', 'name' => 'Altona', 'parent_name' => 'Hamburg' ), 'im Bezirk Altona in Hamburg' ),
    array( array( 'kind' => 'district', 'name' => 'Derne', 'parent_name' => 'Dortmund' ), 'im Stadtteil Derne in Dortmund' ),
    array( array( 'kind' => 'district', 'name' => 'Derne' ), 'für das Gebiet Derne' ),
    array( array(), 'in Deutschland' ),
);
foreach ( $phrases as $case ) { copy_check( $case[1] === findewerkstatt_location_phrase( $case[0] ), 'German location grammar: ' . $case[1] ); }

$city = array( 'kind' => 'city', 'name' => 'München', 'state_name' => 'Bayern', 'parent_name' => 'Bayern', 'child_names' => array( 'Altstadt', 'Schwabing' ), 'service_names' => array( 'Inspektion' ), 'brand_names' => array( 'BMW' ) );
$empty = findewerkstatt_location_copy( $city, 0 );
$one = findewerkstatt_location_copy( $city, 1 );
$many = findewerkstatt_location_copy( $city, 1250 );
copy_check( false !== strpos( $empty['intro'], 'noch kein Werkstattprofil veröffentlicht' ), 'Zero-profile intro states actual availability.' );
copy_check( false !== strpos( $empty['meta_description'], 'Noch kein Profil veröffentlicht' ), 'Zero-profile metadata does not imply available firms.' );
copy_check( false === strpos( copy_text( $empty ), 'Inspektion' ) && false === strpos( copy_text( $empty ), 'BMW' ), 'Stale service and brand context is never presented for zero firms.' );
copy_check( false !== strpos( $one['intro'], 'ein veröffentlichtes Werkstattprofil' ), 'Singular count uses natural German.' );
copy_check( false !== strpos( $one['meta_description'], '1 veröffentlichtes Werkstattprofil' ), 'Singular metadata agrees with the actual count.' );
copy_check( false !== strpos( $many['intro'], '1.250 veröffentlichte Werkstattprofile' ), 'Plural count uses German number formatting.' );
copy_check( false !== strpos( $many['meta_description'], '1.250 veröffentlichte Werkstattprofile' ), 'Plural metadata agrees with the actual count.' );
copy_check( false !== strpos( $one['sections'][1]['text'], 'Inspektion' ) && false !== strpos( $one['sections'][1]['text'], 'BMW' ), 'Actual displayed services and brands are included when a firm exists.' );
copy_check( false !== strpos( $one['sections'][0]['text'], 'Bayern' ) && false !== strpos( $one['sections'][0]['text'], 'Altstadt und Schwabing' ), 'City copy contains actual state and child context.' );
copy_check( false !== strpos( findewerkstatt_location_copy( $city, -1 )['intro'], 'noch kein' ), 'Negative counts cannot produce a false availability claim.' );

$state = findewerkstatt_location_copy( array( 'kind' => 'state', 'name' => 'Bremen', 'child_names' => array( 'Bremen', 'Bremerhaven' ) ), 0 );
$city_state = findewerkstatt_location_copy( array( 'kind' => 'city_state', 'name' => 'Hamburg', 'child_names' => array( 'Altona' ) ), 0 );
$district = findewerkstatt_location_copy( array( 'kind' => 'district', 'name' => 'Derne', 'parent_name' => 'Dortmund', 'state_name' => 'Nordrhein-Westfalen' ), 2 );
copy_check( false !== strpos( $state['sections'][0]['text'], 'Bundesland Bremen' ) && false !== strpos( $state['sections'][0]['text'], 'Bremen und Bremerhaven' ), 'Bremen state copy retains both real towns.' );
copy_check( false === strpos( $state['sections'][0]['text'], 'eine Stadt und zugleich' ), 'Bremen state root does not become a single-city scope.' );
copy_check( false !== strpos( $city_state['sections'][0]['text'], 'eine Stadt und zugleich ein Bundesland' ) && false !== strpos( $city_state['sections'][0]['text'], 'Bezirken gehören Altona' ), 'City-state copy explains the dual role and real boroughs.' );
copy_check( 'Kfz-Werkstätten im Stadtteil Derne in Dortmund' === $district['title'], 'District title includes its actual parent city.' );
copy_check( false !== strpos( $district['sections'][0]['text'], 'Nordrhein-Westfalen' ) && false !== strpos( $district['sections'][0]['text'], 'Verzeichnis für Dortmund' ), 'District copy supplies hierarchy and explains broadening the search.' );

copy_check( 'München, Augsburg und Nürnberg' === findewerkstatt_location_copy_names( array( 'München', ' Augsburg ', 'München', '', 'Nürnberg' ) ), 'Real name lists are deduplicated and joined naturally.' );
copy_check( 'A, B, C, D, E und F' === findewerkstatt_location_copy_names( array( 'A', 'B', 'C', 'D', 'E', 'F', 'G' ) ), 'Visible selection stays bounded to six actual names.' );
copy_check( '' === findewerkstatt_location_copy_names( array( '', null, array() ) ), 'Missing names do not manufacture context.' );

foreach ( array( $empty, $one, $many, $state, $city_state, $district ) as $copy ) {
    copy_check( 5 === count( $copy['faqs'] ) && 4 === count( $copy['sections'] ), 'Useful section and shared FAQ structure is complete.' );
    foreach ( $copy['faqs'] as $faq ) { copy_check( '' !== $faq['question'] && '' !== $faq['answer'], 'FAQ questions and answers are not empty.' ); }
    copy_check( false !== strpos( $copy['faqs'][4]['answer'], 'Werkstattverzeichnis' ) && false !== strpos( $copy['faqs'][4]['answer'], 'direkt mit dem jeweiligen Betrieb' ), 'Directory role stays distinct from workshop repairs and appointments.' );
    copy_check( ! preg_match( '/geprüft|garantiert|beste[nr]?\s+Werkstatt|günstigste|Soforttermin|24\/7|Google[- ]Ranking|GeoCoordinates|latitude|longitude/iu', copy_text( $copy ) ), 'Copy invents neither quality promises, availability, rankings nor geographic coordinates.' );
    $words = preg_split( '/\s+/u', trim( copy_text( $copy ) ), -1, PREG_SPLIT_NO_EMPTY );
    copy_check( count( $words ) >= 250 && count( $words ) <= 500, 'Content remains useful and readable in length.' );
}
echo 'Passed ' . $checks . " location-copy checks.\n";
