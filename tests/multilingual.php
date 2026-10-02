<?php
/** Local WP-CLI regression checks; no real records are changed. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI || ! current_user_can( 'manage_options' ) ) { throw new Exception( 'Administrator WP-CLI test only' ); }
$checks = 0; $failures = array(); $posts = array();
$check = static function ( $ok, $message ) use ( &$checks, &$failures ) { ++$checks; if ( ! $ok ) { $failures[] = $message; } };
$home = untrailingslashit( get_option( 'home' ) );
$old_uri = $_SERVER['REQUEST_URI'] ?? null;
$old_post = $_POST;
try {
    foreach ( array( 'de', 'tr', 'en', 'ru' ) as $code ) {
        $GLOBALS['fw_test_language'] = $code;
        $prefix = 'de' === $code ? '' : '/' . $code;
        $check( home_url( '/' ) === $home . $prefix . '/', $code . ' homepage URL' );
        $check( findewerkstatt_page_url( 'mein-konto' ) === $home . $prefix . '/mein-konto/', $code . ' account URL cache' );
        $check( findewerkstatt_localize_url( $home . '/tr/stadt/berlin/?fw_spoken=tr#betriebe', $code ) === $home . $prefix . '/stadt/berlin/?fw_spoken=tr#betriebe', $code . ' idempotent path/query/fragment' );
        $check( findewerkstatt_localize_url( 'https://example.org/werkstatt/', $code ) === 'https://example.org/werkstatt/', $code . ' external URLs' );
        $check( findewerkstatt_localize_url( $home . '/wp-admin/admin-post.php', $code ) === $home . '/wp-admin/admin-post.php', $code . ' admin endpoint' );
        $check( findewerkstatt_t( 'DEMO Unique Firm ÄÖ & Straße 12' ) === 'DEMO Unique Firm ÄÖ & Straße 12', $code . ' original firm details' );
        if ( 'de' !== $code ) {
            $check( findewerkstatt_t( 'Im Betrieb gesprochene Sprachen' ) !== 'Im Betrieb gesprochene Sprachen', $code . ' core catalog' );
            $check( findewerkstatt_t( 'Werkstatt finden' ) !== 'Werkstatt finden', $code . ' public catalog' );
        }
    }
    unset( $GLOBALS['fw_test_language'] );
    $_POST = array( 'action' => 'fw_member_login', 'fw_language' => 'ru' );
    $check( 'ru' === findewerkstatt_language(), 'known POST language retained' );
    $_POST['fw_language'] = array( 'ru' ); $check( 'de' === findewerkstatt_language(), 'malformed POST locale ignored' );
    $_POST = array();
    $rules = findewerkstatt_language_rewrite_rules( array( '^werkstatt/([^/]+)/?$' => 'index.php?mechanic=$matches[1]' ) );
    $check( 'index.php?mechanic=$matches[1]' === $rules['^(?:tr|en|ru)/werkstatt/([^/]+)/?$'], 'capture indexes unchanged' );
    $check( $rules === findewerkstatt_language_rewrite_rules( $rules ), 'rewrite rules idempotent' );
    $check( array( 'de', 'tr' ) === findewerkstatt_validate_workshop_languages( array( 'de', 'tr', 'de' ) ), 'declared languages deduplicated' );
    foreach ( array( 'tr', array( 'xx' ), array( array( 'tr' ) ), array( 'tr" OR 1=1' ) ) as $invalid ) { $check( is_wp_error( findewerkstatt_validate_workshop_languages( $invalid ) ), 'invalid declared language rejected' ); }
    $id = wp_insert_post( array( 'post_type' => 'mechanic', 'post_status' => 'pending', 'post_title' => 'FWTEST language declaration ' . wp_generate_uuid4() ) );
    if ( ! $id || is_wp_error( $id ) ) { throw new Exception( 'Fixture unavailable' ); }
    $posts[] = $id;
    $check( array() === findewerkstatt_workshop_language_labels( $id ), 'no inferred languages' );
    update_post_meta( $id, '_mechanic_languages', array( 'de', 'tr' ) );
    $check( array( 'de', 'tr' ) === array_keys( findewerkstatt_workshop_language_labels( $id ) ), 'declared codes shared across UI languages' );
    $GLOBALS['fw_test_language'] = 'en';
    $check( array( 'de' => 'German', 'tr' => 'Turkish' ) === findewerkstatt_workshop_language_labels( $id ), 'declared labels localized' );
    unset( $GLOBALS['fw_test_language'] );
    $server = wp_sitemaps_get_server(); $provider = $server->registry->get_provider( 'languages' );
    $check( $provider instanceof WP_Sitemaps_Provider, 'translated sitemap provider' );
    if ( $provider ) {
        $entries = $provider->get_url_list( 1, 'tr-posts-page' );
        $check( ! empty( $entries ), 'translated page sitemap populated' );
        $check( ! array_filter( $entries, static function ( $entry ) use ( $home ) { return ! str_starts_with( $entry['loc'], $home . '/tr/' ) || str_contains( $entry['loc'], '/mein-konto/' ); } ), 'sitemap locale and private account exclusion' );
        $check( array() === $provider->get_url_list( 1, 'tr-posts-fw_member_request' ), 'private request type excluded' );
    }
} finally {
    foreach ( $posts as $id ) { wp_delete_post( $id, true ); }
    unset( $GLOBALS['fw_test_language'] ); $_POST = $old_post;
    if ( null === $old_uri ) { unset( $_SERVER['REQUEST_URI'] ); } else { $_SERVER['REQUEST_URI'] = $old_uri; }
}
echo wp_json_encode( array( 'checks' => $checks, 'failures' => $failures, 'fixtures_cleaned' => true ), JSON_UNESCAPED_UNICODE ) . "\n";
if ( $failures ) { throw new Exception( 'Multilingual checks failed' ); }
