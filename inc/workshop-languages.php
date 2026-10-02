<?php
/** Languages explicitly declared by a workshop, independent of the website language. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function findewerkstatt_workshop_languages() {
    $languages = array( 'de' => 'Deutsch', 'tr' => 'Türkisch', 'en' => 'Englisch', 'ru' => 'Russisch', 'ar' => 'Arabisch', 'pl' => 'Polnisch', 'uk' => 'Ukrainisch', 'fr' => 'Französisch', 'it' => 'Italienisch', 'ro' => 'Rumänisch' );
    return array_map( 'findewerkstatt_t', $languages );
}
function findewerkstatt_validate_workshop_languages( $raw ) {
    if ( ! is_array( $raw ) || count( $raw ) > 10 ) { return new WP_Error( 'workshop_languages', findewerkstatt_t( 'Bitte wählen Sie gültige Betriebssprachen aus.' ) ); }
    $valid = findewerkstatt_workshop_languages(); $selected = array();
    foreach ( $raw as $code ) { if ( ! is_string( $code ) || ! isset( $valid[ $code ] ) ) { return new WP_Error( 'workshop_languages', findewerkstatt_t( 'Bitte wählen Sie gültige Betriebssprachen aus.' ) ); } $selected[ $code ] = $code; }
    return array_values( $selected );
}
function findewerkstatt_workshop_language_labels( $post_id ) {
    $saved = get_post_meta( $post_id, '_mechanic_languages', true );
    $codes = findewerkstatt_validate_workshop_languages( is_array( $saved ) ? $saved : array() );
    return is_wp_error( $codes ) ? array() : array_intersect_key( findewerkstatt_workshop_languages(), array_flip( $codes ) );
}
function findewerkstatt_render_workshop_language_choices( $selected = array(), $id_prefix = 'fw-spoken' ) {
    $selected = is_array( $selected ) ? $selected : array();
    echo '<fieldset class="fw-form-section"><legend>' . esc_html( findewerkstatt_t( 'Im Betrieb gesprochene Sprachen' ) ) . '</legend><p class="fw-form-help">' . esc_html( findewerkstatt_t( 'Optional: Wählen Sie nur die Sprachen aus, in denen Kunden tatsächlich mit Ihrem Betrieb sprechen können.' ) ) . '</p><div class="fw-form-choices">';
    foreach ( findewerkstatt_workshop_languages() as $code => $label ) { $id = $id_prefix . '-' . $code; echo '<label class="fw-form-check" for="' . esc_attr( $id ) . '"><input id="' . esc_attr( $id ) . '" type="checkbox" name="spoken_languages[]" value="' . esc_attr( $code ) . '" ' . checked( in_array( $code, $selected, true ), true, false ) . '><span>' . esc_html( $label ) . '</span></label>'; }
    echo '</div></fieldset>';
}
function findewerkstatt_render_workshop_language_filter( $selected = '' ) {
    echo '<div class="fw-form-field"><label for="fw-spoken-filter">' . esc_html( findewerkstatt_t( 'Gesprochene Sprache' ) ) . '</label><select id="fw-spoken-filter" name="fw_spoken"><option value="">' . esc_html( findewerkstatt_t( 'Alle Sprachen' ) ) . '</option>';
    foreach ( findewerkstatt_workshop_languages() as $code => $label ) { echo '<option value="' . esc_attr( $code ) . '" ' . selected( $selected, $code, false ) . '>' . esc_html( $label ) . '</option>'; }
    echo '</select></div>';
}
add_action( 'pre_get_posts', static function ( $query ) {
    if ( is_admin() || ! $query->is_main_query() || ! ( $query->is_search() || $query->is_post_type_archive( 'mechanic' ) || $query->is_tax( array( 'mechanic_city', 'service_type', 'car_brand' ) ) ) || ! array_key_exists( 'fw_spoken', $_GET ) || '' === $_GET['fw_spoken'] ) { return; }
    $code = is_string( $_GET['fw_spoken'] ) ? wp_unslash( $_GET['fw_spoken'] ) : '';
    if ( ! isset( findewerkstatt_workshop_languages()[ $code ] ) ) { $query->set( 'post__in', array( 0 ) ); return; }
    $existing = $query->get( 'meta_query' );
    $meta = array( 'relation' => 'AND', array( 'key' => '_mechanic_languages', 'value' => '"' . $code . '"', 'compare' => 'LIKE' ) );
    if ( is_array( $existing ) && $existing ) { $meta[] = $existing; }
    $query->set( 'meta_query', $meta );
}, 30 );

add_filter( 'wp_robots', static function ( $robots ) {
    if ( ! empty( $_GET['fw_spoken'] ) ) { $robots['noindex'] = true; $robots['follow'] = true; unset( $robots['index'] ); }
    return $robots;
} );

add_action( 'add_meta_boxes_mechanic', static function () {
    add_meta_box( 'fw_workshop_languages', 'Im Betrieb gesprochene Sprachen', static function ( $post ) {
        wp_nonce_field( 'fw_workshop_languages', 'fw_workshop_languages_nonce' );
        echo '<input type="hidden" name="fw_workshop_languages_present" value="1">';
        findewerkstatt_render_workshop_language_choices( array_keys( findewerkstatt_workshop_language_labels( $post->ID ) ), 'fw-admin-spoken' );
    }, 'mechanic', 'normal', 'default' );
} );
add_action( 'save_post_mechanic', static function ( $post_id ) {
    if ( ! isset( $_POST['fw_workshop_languages_present'] ) || '1' !== $_POST['fw_workshop_languages_present'] || ! is_string( $_POST['fw_workshop_languages_nonce'] ?? null ) || ! wp_verify_nonce( wp_unslash( $_POST['fw_workshop_languages_nonce'] ), 'fw_workshop_languages' ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) { return; }
    $languages = findewerkstatt_validate_workshop_languages( isset( $_POST['spoken_languages'] ) ? wp_unslash( $_POST['spoken_languages'] ) : array() );
    if ( ! is_wp_error( $languages ) ) { update_post_meta( $post_id, '_mechanic_languages', $languages ); }
} );
