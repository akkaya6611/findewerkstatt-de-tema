<?php
/** Isolated URL regressions; no WordPress database, firms or settings are changed. */
if ( PHP_SAPI !== 'cli' ) { http_response_code( 404 ); exit; }
define( 'ABSPATH', __DIR__ . '/' );
function add_action( ...$args ) {}
function home_url( $path = '' ) { return 'https://example.test/verzeichnis' . $path; }
function untrailingslashit( $value ) { return rtrim( $value, '/' ); }
function sanitize_title( $slug ) { return strtolower( trim( $slug ) ); }
class WP_Error {}
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function get_page_by_path( $slug ) { return $GLOBALS['pages'][ $slug ] ?? null; }
function get_posts( $args ) { return $GLOBALS['templates'][ $args['meta_value'] ] ?? array(); }
function get_permalink( $page ) { return $page->permalink; }
function get_post_type_archive_link( $type ) { return home_url( '/?post_type=mechanic' ); }
function findewerkstatt_normalize_city_slug( $slug ) { return 'munich-old' === $slug ? 'muenchen' : $slug; }
function get_term_by( $field, $slug, $taxonomy ) { return $GLOBALS['terms'][ $taxonomy ][ $slug ] ?? false; }
function get_term_link( $term ) { return $term->url; }
function get_template_directory() { return dirname( __DIR__ ); }
require dirname( __DIR__ ) . '/inc/theme-upgrade.php';
require dirname( __DIR__ ) . '/inc/site-assets.php';
$checks = 0;
function fw_navigation_check( $value, $expected, $label ) {
    global $checks;
    if ( $value !== $expected ) { throw new RuntimeException( $label . ': ' . var_export( $value, true ) ); }
    ++$checks;
}
function fw_navigation_page( $status, $path ) { return (object) array( 'post_status' => $status, 'permalink' => home_url( $path ) ); }
$GLOBALS['pages']['kontakt'] = fw_navigation_page( 'publish', '/?page_id=18' );
$GLOBALS['pages']['datenschutz'] = fw_navigation_page( 'draft', '/alte-privatsphaere/' );
$GLOBALS['templates']['page-datenschutz.php'] = array( fw_navigation_page( 'publish', '/recht/datenschutz-neu/' ) );
$GLOBALS['templates']['page-impressum.php'] = array( fw_navigation_page( 'publish', '/legal/imprint/' ) );
$GLOBALS['pages']['agb'] = fw_navigation_page( 'draft', '/bedingungen/' );
$GLOBALS['terms']['mechanic_city']['muenchen'] = (object) array( 'url' => home_url( '/stadt/bayern/muenchen/' ) );
$GLOBALS['terms']['mechanic_city']['invalid-link'] = (object) array( 'url' => new WP_Error );
$GLOBALS['terms']['service_type']['abschleppdienst-pannenhilfe'] = (object) array( 'url' => home_url( '/?service_type=abschleppdienst-pannenhilfe' ) );
$GLOBALS['terms']['service_type']['invalid-term'] = new WP_Error;
fw_navigation_check( findewerkstatt_page_url( 'kontakt' ), home_url( '/?page_id=18' ), 'Plain WordPress permalink' );
fw_navigation_check( findewerkstatt_page_url( 'datenschutz' ), home_url( '/recht/datenschutz-neu/' ), 'Published replacement beats old draft' );
fw_navigation_check( findewerkstatt_page_url( 'impressum' ), home_url( '/legal/imprint/' ), 'Renamed template page' );
fw_navigation_check( findewerkstatt_page_url( 'agb' ), home_url( '/' ), 'Draft-only page does not produce a broken link' );
fw_navigation_check( findewerkstatt_page_url( 'missing' ), home_url( '/' ), 'Missing page has a valid fallback' );
fw_navigation_check( findewerkstatt_term_url( 'munich-old', 'mechanic_city' ), home_url( '/stadt/bayern/muenchen/' ), 'City alias has canonical parent path' );
fw_navigation_check( findewerkstatt_term_url( 'missing', 'mechanic_city' ), get_post_type_archive_link( 'mechanic' ), 'Missing term falls back to directory' );
fw_navigation_check( findewerkstatt_term_url( 'invalid-link', 'mechanic_city' ), get_post_type_archive_link( 'mechanic' ), 'Term link error falls back to directory' );
fw_navigation_check( findewerkstatt_term_url( 'invalid-term', 'service_type' ), get_post_type_archive_link( 'mechanic' ), 'Term lookup error falls back to directory' );
fw_navigation_check( findewerkstatt_legacy_redirect_url( '/kontakt/' ), home_url( '/?page_id=18' ), 'Legacy contact redirects to actual permalink' );
fw_navigation_check( findewerkstatt_legacy_redirect_url( '/impressum/' ), home_url( '/legal/imprint/' ), 'Legacy target follows renamed page' );
fw_navigation_check( findewerkstatt_legacy_redirect_url( '/#bundeslaender' ), home_url( '/#bundeslaender' ), 'Subdirectory anchor is preserved' );
fw_navigation_check( findewerkstatt_legacy_redirect_url( '/service/abschleppdienst-pannenhilfe/' ), home_url( '/?service_type=abschleppdienst-pannenhilfe' ), 'Legacy service follows WordPress taxonomy URL' );
$document = findewerkstatt_site_asset_contents( 'llms.txt' );
fw_navigation_check( str_contains( $document, '(' . get_post_type_archive_link( 'mechanic' ) . ')' ), true, 'Public document directory link works with plain permalinks' );
fw_navigation_check( str_contains( $document, '(' . home_url( '/?page_id=18' ) . ')' ), true, 'Public document contact uses actual permalink' );
fw_navigation_check( str_contains( $document, '(' . home_url( '/legal/imprint/' ) . ')' ), true, 'Public document follows renamed pages' );
fw_navigation_check( str_contains( $document, '(' . home_url( '/' ) . ')' ), true, 'Public document home respects subdirectory' );
fw_navigation_check( str_contains( $document, 'https://findewerkstatt.de/' ), false, 'Public document has no hardcoded production links' );
fw_navigation_check( findewerkstatt_site_asset_contents( '../wp-config.php' ), false, 'Only declared public documents are readable' );
fw_navigation_check( str_contains( findewerkstatt_site_asset_contents( 'ads.txt' ), 'Derzeit ist kein Werbeanbieter' ), true, 'Ads document is available' );
echo 'Navigation: ' . $checks . " checks passed. No database writes.\n";
