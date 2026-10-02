<?php
/** Preserve source listing links and make incomplete imported records reviewable. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function findewerkstatt_import_review_labels() {
    return array(
        'copied_unreliable_company_name_review_before_publish' => 'Der Firmenname wurde im Export mehrfach für verschiedene Adressen verwendet. Bitte den tatsächlichen Namen prüfen.',
        'source_name_indicates_inactive_business' => 'Der Quellname kennzeichnet den Betrieb als inaktiv oder dauerhaft geschlossen.',
        'unresolved' => 'Eine vollständige, zuordenbare Betriebsadresse fehlt.',
        'foreign_country' => 'Die Betriebsadresse liegt außerhalb Deutschlands.',
        'business_type_needs_individual_review' => 'Die Zuordnung zum Kfz-Werkstattverzeichnis muss geprüft werden.',
        'bicycle_business_name' => 'Der Quellname bezeichnet einen Fahrradbetrieb.',
        'adac_branch_office_not_towing_provider' => 'Der Quellname bezeichnet eine ADAC-Geschäftsstelle oder ein Reisebüro. Eine eigene Pannen- oder Abschleppleistung ist nicht belegt.',
        'toy_store_name_no_automotive_business' => 'Der Quellname bezeichnet ein Spielwarengeschäft.',
    );
}

add_action( 'admin_notices', function () {
    $screen = get_current_screen();
    if ( ! $screen || 'post' !== $screen->base || 'mechanic' !== $screen->post_type ) { return; }
    $id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
    if ( ! $id || ! current_user_can( 'edit_post', $id ) ) { return; }
    $reasons = get_post_meta( $id, '_fw_import_review_reasons', true );
    if ( ! is_array( $reasons ) || ! $reasons ) { return; }
    $labels = findewerkstatt_import_review_labels();
    echo '<div class="notice notice-warning"><p><strong>Importangaben prüfen</strong></p><ul>';
    foreach ( $reasons as $reason ) { echo '<li>' . esc_html( $labels[ $reason ] ?? 'Die Angaben im Quellimport sind unvollständig oder widersprüchlich.' ) . '</li>'; }
    echo '</ul><p>Dieser Eintrag wurde zur Prüfung gespeichert. Ergänzen oder korrigieren Sie die Angaben vor der Veröffentlichung.</p></div>';
} );

add_action( 'template_redirect', function () {
    if ( ! is_404() || ! in_array( $_SERVER['REQUEST_METHOD'] ?? 'GET', array( 'GET', 'HEAD' ), true ) ) { return; }
    $path = trim( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
    $base = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
    if ( $base && str_starts_with( $path, $base . '/' ) ) { $path = substr( $path, strlen( $base ) + 1 ); }
    if ( ! preg_match( '#^listing/([^/]+)/?$#', $path, $matches ) ) { return; }
    $slug = sanitize_title( rawurldecode( $matches[1] ) );
    $ids = get_posts( array( 'post_type' => 'mechanic', 'post_status' => 'publish', 'posts_per_page' => 1,
        'fields' => 'ids', 'meta_key' => '_fw_import_legacy_slug', 'meta_value' => $slug ) );
    if ( $ids ) { wp_safe_redirect( get_permalink( $ids[0] ), 301 ); exit; }
}, 4 );
