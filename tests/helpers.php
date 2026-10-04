<?php
/** Standalone regression checks; no WordPress database or network required. */
if ( PHP_SAPI !== 'cli' ) { http_response_code( 404 ); exit; }
define( 'ABSPATH', __DIR__ );
$meta = array();
$hooks = array();
$allowed = true;
$revision = false;
function add_action( ...$args ) { global $hooks; $hooks[] = $args; }
function add_filter( ...$args ) { global $hooks; $hooks[] = $args; }
function get_post_meta( $id, $key, $single = true ) { global $meta; return $meta[ $id ][ $key ] ?? ''; }
function update_post_meta( $id, $key, $value ) { global $meta; $meta[ $id ][ $key ] = is_string( $value ) ? stripslashes( $value ) : $value; }
function delete_post_meta( $id, $key ) { global $meta; unset( $meta[ $id ][ $key ] ); }
function esc_attr( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' ); }
function esc_html( $value ) { return esc_attr( $value ); }
function number_format_i18n( $value ) { return number_format( $value, 0, ',', '.' ); }
function get_the_ID() { return 1; }
function get_the_terms( $id, $taxonomy ) { return array( (object) array( 'term_id' => 10, 'slug' => 'bayern' ), (object) array( 'term_id' => 11, 'slug' => 'muenchen' ) ); }
function get_ancestors( $id, $taxonomy, $type ) { return 11 === $id ? array( 10 ) : array(); }
function is_wp_error( $value ) { return false; }
function wp_verify_nonce( $value, $action ) { return 'valid' === $value; }
function wp_unslash( $value ) { return stripslashes( $value ); }
function wp_slash( $value ) { return addslashes( $value ); }
function current_user_can( ...$args ) { global $allowed; return $allowed; }
function wp_is_post_revision( $id ) { global $revision; return $revision; }
function wp_is_post_autosave( $id ) { return false; }
function get_post_type( $id ) { return 'mechanic'; }
function sanitize_text_field( $value ) { return trim( strip_tags( $value ) ); }
function sanitize_email( $value ) { return filter_var( $value, FILTER_SANITIZE_EMAIL ); }
function esc_url_raw( $value, $protocols = array( 'http', 'https' ) ) { return is_string( $value ) && in_array( strtolower( parse_url( $value, PHP_URL_SCHEME ) ?? '' ), $protocols, true ) ? $value : ''; }
function wp_parse_url( $value, $component = -1 ) { return parse_url( $value, $component ); }
if ( ! function_exists( 'findewerkstatt_t' ) ) { function findewerkstatt_t( $text ) { return $text; } }
require dirname( __DIR__ ) . '/inc/helpers.php';
require dirname( __DIR__ ) . '/inc/meta-boxes.php';
require dirname( __DIR__ ) . '/inc/post-types-taxonomies.php';
require dirname( __DIR__ ) . '/inc/sample-data-seeder.php';

$checks = 0;
function expect_same( $expected, $actual, $label ) {
    global $checks;
    $checks++;
    if ( $expected !== $actual ) {
        throw new RuntimeException( $label . ': expected ' . var_export( $expected, true ) . ', got ' . var_export( $actual, true ) );
    }
}
expect_same( array( 'rating' => null, 'count' => 0 ), findewerkstatt_get_rating( 1 ), 'Absent ratings' );
expect_same( '', findewerkstatt_render_stars(), 'Unknown ratings are omitted' );
$meta[1] = array( '_mechanic_rating_avg' => '4,7', '_mechanic_rating_count' => '0' );
expect_same( array( 'rating' => 4.7, 'count' => 0 ), findewerkstatt_get_rating( 1 ), 'German decimals and zero counts' );
expect_same( 5.0, findewerkstatt_normalize_rating( '9' ), 'Upper rating limit' );
expect_same( 0.0, findewerkstatt_normalize_rating( '-3' ), 'Lower rating limit' );
expect_same( null, findewerkstatt_normalize_rating( '<script>5</script>' ), 'Invalid rating' );
expect_same( true, str_contains( findewerkstatt_render_stars( 4.7, 1 ), 'aria-label="4,7 von 5 Sternen, 1 Bewertung"' ), 'Rating announces scale and singular review count to screen readers' );
expect_same( true, str_contains( findewerkstatt_render_stars( 4.7, 12 ), 'aria-label="4,7 von 5 Sternen, 12 Bewertungen"' ), 'Rating announces plural review count to screen readers' );
expect_same( true, str_contains( findewerkstatt_render_stars( 4.7, 0 ), 'aria-label="4,7 von 5 Sternen"' ) && ! str_contains( findewerkstatt_render_stars( 4.7, 0 ), 'Bewertung' ), 'Unknown review counts add no invented review claim' );
expect_same( 0, findewerkstatt_normalize_review_count( '-3' ), 'Negative count is not reflected to positive' );
expect_same( PHP_INT_MAX, findewerkstatt_normalize_review_count( '1e25' ), 'Huge count does not overflow' );
foreach ( array( '030 1234567', '0049 30 1234567', '+49 (0)30 1234567', '49301234567', '30 1234567' ) as $phone ) {
    expect_same( '+49301234567', findewerkstatt_normalize_phone( $phone ), 'German phone ' . $phone );
}
expect_same( '+43123456789', findewerkstatt_normalize_phone( '+43 123456789' ), 'Explicit international number' );
expect_same( '', findewerkstatt_normalize_phone( '123abc456' ), 'Malformed phone' );
foreach ( array(
    'werkstatt.de' => 'https://werkstatt.de',
    ' www.werkstatt.de/service?brand=bmw#kontakt ' => 'https://www.werkstatt.de/service?brand=bmw#kontakt',
    'werkstatt.de:8443/termin' => 'https://werkstatt.de:8443/termin',
    '//werkstatt.de/termin' => 'https://werkstatt.de/termin',
    'HTTP://werkstatt.de' => 'http://werkstatt.de',
    'https://xn--mller-kva.de' => 'https://xn--mller-kva.de',
    'https://müller.de/termin' => 'https://müller.de/termin',
    'https://203.0.113.8:8443/' => 'https://203.0.113.8:8443/',
    'https://[2001:db8::1]/' => 'https://[2001:db8::1]/',
) as $input => $expected ) { expect_same( $expected, findewerkstatt_get_website_url( $input ), 'Valid business website: ' . $input ); }
foreach ( array( '', null, array(), 'javascript:alert(1)', 'mailto:info@example.de', 'ftp://example.de', 'http:example.de', 'https://user:password@example.de', 'https://@example.de', 'example.de\\path', "https://example.de\r\n", 'https://example.de/%0d%0a', 'https://example .de', 'https://example..de', 'https://-example.de', 'https://example-.de', 'https://example.123', 'https://999.999.9.9', 'https://[203.0.113.8]/', 'https://203.0.113.8]/', 'https://localhost', 'https://example.de:0', 'https://example.de:65536' ) as $input ) {
    expect_same( '', findewerkstatt_get_website_url( $input ), 'Malformed website is omitted: ' . ( is_string( $input ) ? $input : 'non-string' ) );
}
expect_same( '', findewerkstatt_get_whatsapp_url( '', 'Werkstatt' ), 'No empty WhatsApp link' );
expect_same( true, 0 === strpos( findewerkstatt_get_whatsapp_url( '0301234567', 'Werkstatt' ), 'https://wa.me/49301234567?' ), 'WhatsApp country code' );
expect_same( 'muenchen', findewerkstatt_get_city_term( 1 )->slug, 'Assigned city preferred over state' );
expect_same( array( array( 480, 720 ), array( 780, 1080 ) ), findewerkstatt_parse_hours( '08:00–12:00 / 13:00–18:00 Uhr' ), 'Split intervals' );
expect_same( array(), findewerkstatt_parse_hours( 'Geschlossen' ), 'Closed schedule' );
expect_same( 'Geschlossen', findewerkstatt_format_display_hours( 'Geschlossen' ), 'Display hours closed format' );
expect_same( 'Nicht angegeben', findewerkstatt_format_display_hours( '' ), 'Display hours empty format' );
expect_same( null, findewerkstatt_parse_hours( '24:30–25:00' ), 'Invalid clock times' );
expect_same( null, findewerkstatt_parse_hours( 'Nicht 24h geöffnet' ), 'Do not misread negated hours' );
expect_same( array( array( 0, 1440 ) ), findewerkstatt_parse_hours( '24 Stunden geöffnet' ), 'All-day opening' );
$meta[1]['_mechanic_hours_weekday'] = '08:00–12:00, 13:00–18:00';
$meta[1]['_mechanic_hours_saturday'] = 'Geschlossen';
$meta[1]['_mechanic_hours_sunday'] = 'Geschlossen';
expect_same( 'open', findewerkstatt_get_open_state( 1, new DateTimeImmutable( '2026-10-01 09:00:00+02:00' ) ), 'Within opening interval' );
expect_same( 'closed', findewerkstatt_get_open_state( 1, new DateTimeImmutable( '2026-10-01 12:00:00+02:00' ) ), 'Lunch break boundary' );
expect_same( 'open', findewerkstatt_get_open_state( 1, new DateTimeImmutable( '2026-10-01 11:00:00+00:00' ) ), 'Berlin timezone conversion' );
$meta[1]['_mechanic_hours_weekday'] = '22:00–02:00';
expect_same( 'open', findewerkstatt_get_open_state( 1, new DateTimeImmutable( '2026-10-03 01:00:00+02:00' ) ), 'Friday overnight interval continues on Saturday' );
expect_same( 'closed', findewerkstatt_get_open_state( 1, new DateTimeImmutable( '2026-10-03 02:00:00+02:00' ) ), 'Overnight close boundary' );
$meta[1]['_mechanic_hours_weekday'] = '';
expect_same( 'unknown', findewerkstatt_get_open_state( 1, new DateTimeImmutable( '2026-10-01 10:00:00+02:00' ) ), 'Missing hours are unknown' );
$meta[1]['_mechanic_emergency_24h'] = 'yes';
expect_same( 'emergency', findewerkstatt_get_open_state( 1 ), 'Emergency service is a distinct state' );

$meta[1]['_mechanic_is_verified'] = 'yes';
$meta[1]['_mechanic_email_public'] = 'yes';
$_POST = array( 'fw_mechanic_nonce' => 'valid', '_mechanic_rating_count' => '0', '_mechanic_latitude' => '95', '_mechanic_longitude' => '13,4', '_mechanic_address' => "O\'Brien-Straße 1" );
FindeWerkstatt_Meta_Boxes::save_meta_boxes( 1 );
expect_same( 'yes', $meta[1]['_mechanic_is_verified'], 'Partial save preserves omitted checkboxes' );
expect_same( 'yes', $meta[1]['_mechanic_email_public'], 'Partial save preserves email-publication consent' );
expect_same( 0, $meta[1]['_mechanic_rating_count'], 'Zero review count saved' );
expect_same( false, isset( $meta[1]['_mechanic_latitude'] ), 'Out-of-bounds coordinate cleared' );
expect_same( 13.4, $meta[1]['_mechanic_longitude'], 'German coordinate decimal accepted' );
expect_same( "O'Brien-Straße 1", $meta[1]['_mechanic_address'], 'Slashed request preserved correctly' );
$_POST = array( 'fw_mechanic_nonce' => 'valid', 'fw_email_public_box_present' => '1' );
FindeWerkstatt_Meta_Boxes::save_meta_boxes( 1 );
expect_same( 'no', $meta[1]['_mechanic_email_public'], 'Explicit unchecked consent revokes public email' );
$_POST = array( 'fw_mechanic_nonce' => 'valid', 'fw_email_public_box_present' => '1', '_mechanic_email_public' => 'yes' );
FindeWerkstatt_Meta_Boxes::save_meta_boxes( 1 );
expect_same( 'yes', $meta[1]['_mechanic_email_public'], 'Explicit checkbox grants public email' );
$_POST = array( 'fw_mechanic_nonce' => 'valid', 'fw_badges_box_present' => '1' );
FindeWerkstatt_Meta_Boxes::save_meta_boxes( 1 );
expect_same( 'no', $meta[1]['_mechanic_is_verified'], 'Explicit form save can clear checkbox' );
$_POST = array( 'fw_mechanic_nonce' => array( 'invalid' ), '_mechanic_rating_count' => '5' );
FindeWerkstatt_Meta_Boxes::save_meta_boxes( 1 );
expect_same( 0, $meta[1]['_mechanic_rating_count'], 'Malformed nonce ignored' );
$_POST = array( 'fw_mechanic_nonce' => 'valid', '_mechanic_rating_count' => '5' );
$revision = true;
FindeWerkstatt_Meta_Boxes::save_meta_boxes( 1 );
expect_same( 0, $meta[1]['_mechanic_rating_count'], 'Revision save ignored' );
$revision = false;
$allowed = false;
FindeWerkstatt_Meta_Boxes::save_meta_boxes( 1 );
expect_same( 0, $meta[1]['_mechanic_rating_count'], 'Permission check enforced' );
FindeWerkstatt_CPT_Taxonomies::check_and_seed(); // Must return before reading/mutating options.
foreach ( $hooks as $hook ) {
    expect_same( false, 'admin_init' === $hook[0] && is_array( $hook[1] ) && 'FindeWerkstatt_Sample_Seeder' === $hook[1][0], 'No automatic public samples' );
    expect_same( false, 'post_type_link' === $hook[0], 'Core permalink generation preserved' );
}
echo $checks . " regression checks passed.\n";
