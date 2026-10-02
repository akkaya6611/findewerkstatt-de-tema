<?php
/** Public forms and a private, administrator-only contact inbox. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function findewerkstatt_register_contact_inbox() {
    register_post_type( 'fw_contact_message', array(
        'labels' => array( 'name' => 'Kontaktanfragen', 'singular_name' => 'Kontaktanfrage', 'menu_name' => 'Kontaktanfragen', 'edit_item' => findewerkstatt_t( 'Kontaktanfrage lesen' ) ),
        'public' => false, 'publicly_queryable' => false, 'exclude_from_search' => true,
        'show_ui' => true, 'show_in_rest' => false, 'menu_icon' => 'dashicons-email-alt',
        'supports' => array( 'title', 'editor' ), 'map_meta_cap' => false,
        'capabilities' => array(
            'edit_post' => 'manage_options', 'read_post' => 'manage_options', 'delete_post' => 'manage_options',
            'edit_posts' => 'manage_options', 'edit_others_posts' => 'manage_options', 'publish_posts' => 'manage_options',
            'read_private_posts' => 'manage_options', 'delete_posts' => 'manage_options',
            'delete_private_posts' => 'manage_options', 'delete_published_posts' => 'manage_options',
            'delete_others_posts' => 'manage_options', 'edit_private_posts' => 'manage_options',
            'edit_published_posts' => 'manage_options', 'create_posts' => 'do_not_allow',
        ),
    ) );
    if ( ! wp_next_scheduled( 'fw_contact_retention' ) ) {
        wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'fw_contact_retention' );
    }
}
add_action( 'init', 'findewerkstatt_register_contact_inbox' );

/** Old enquiries go to the recoverable trash, never straight to permanent deletion. */
function findewerkstatt_contact_retention() {
    $ids = get_posts( array(
        'post_type' => 'fw_contact_message', 'post_status' => 'private', 'posts_per_page' => 100,
        'fields' => 'ids', 'orderby' => 'date', 'order' => 'ASC',
        'date_query' => array( array( 'column' => 'post_date_gmt', 'before' => gmdate( 'Y-m-d H:i:s', time() - 180 * DAY_IN_SECONDS ) ) ),
    ) );
    foreach ( $ids as $id ) { wp_trash_post( $id ); }
}
add_action( 'fw_contact_retention', 'findewerkstatt_contact_retention' );

/** Only scalar fields enter processing; bound retained data before sanitization. */
function findewerkstatt_form_text( $key, $limit = 200, $multiline = false ) {
    if ( ! isset( $_POST[ $key ] ) || ! is_string( $_POST[ $key ] ) ) { return ''; }
    $value = wp_unslash( $_POST[ $key ] );
    $value = function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $limit + 1 ) : substr( $value, 0, $limit + 1 );
    return trim( $multiline ? sanitize_textarea_field( $value ) : sanitize_text_field( $value ) );
}
function findewerkstatt_form_length( $value ) {
    return function_exists( 'mb_strlen' ) ? mb_strlen( $value ) : strlen( $value );
}
function findewerkstatt_form_checked( $key ) { return findewerkstatt_form_text( $key, 5 ) === '1'; }

/** Personal data stays in short-lived server-side notices, never in the URL. */
function findewerkstatt_form_redirect( $kind, $type, $message, $values = array(), $errors = array(), $received = false ) {
    $token = wp_generate_password( 32, false, false );
    set_transient( 'fw_form_notice_' . $token, compact( 'kind', 'type', 'message', 'values', 'errors', 'received' ), 15 * MINUTE_IN_SECONDS );
    $slug = $kind === 'contact' ? 'kontakt' : 'werkstatt-anmelden';
    wp_safe_redirect( add_query_arg( 'fw_notice', $token, findewerkstatt_page_url( $slug ) ), 303 );
    exit;
}
function findewerkstatt_get_form_notice( $kind ) {
    nocache_headers();
    if ( ! isset( $_GET['fw_notice'] ) || ! is_string( $_GET['fw_notice'] ) ) { return array(); }
    $token = wp_unslash( $_GET['fw_notice'] );
    if ( ! preg_match( '/^[a-zA-Z0-9]{32}$/', $token ) ) { return array(); }
    $notice = get_transient( 'fw_form_notice_' . $token );
    if ( ! is_array( $notice ) || ( $notice['kind'] ?? '' ) !== $kind ) { return array(); }
    delete_transient( 'fw_form_notice_' . $token );
    return $notice;
}
function findewerkstatt_form_submission_token( $kind ) {
    $token = wp_generate_password( 32, false, false );
    set_transient( 'fw_form_token_' . hash( 'sha256', $token ), $kind, 2 * HOUR_IN_SECONDS );
    return $token;
}
function findewerkstatt_form_cleanup_lock( $key ) {
    if ( is_string( $key ) && strpos( $key, 'fw_form_lock_' ) === 0 ) { delete_option( $key ); }
}
add_action( 'fw_form_cleanup_lock', 'findewerkstatt_form_cleanup_lock' );

/** The option claim is atomic, so even concurrent clicks cannot reuse a form token. */
function findewerkstatt_form_claim( $kind ) {
    $token = findewerkstatt_form_text( 'fw_submission_token', 32 );
    if ( ! preg_match( '/^[a-zA-Z0-9]{32}$/', $token ) ) {
        return new WP_Error( 'expired', findewerkstatt_t( 'Das Formular ist abgelaufen. Bitte prüfen Sie Ihre Angaben und senden Sie es erneut.' ) );
    }
    $hash = hash( 'sha256', $token );
    $key = 'fw_form_lock_' . $hash;
    $lock = get_option( $key );
    if ( is_array( $lock ) && ( $lock['kind'] ?? '' ) === $kind ) {
        return new WP_Error( ( $lock['status'] ?? '' ) === 'done' ? 'duplicate' : 'processing', findewerkstatt_t( 'Ihre Anfrage wird bereits bearbeitet. Bitte warten Sie einen Moment.' ) );
    }
    if ( get_transient( 'fw_form_token_' . $hash ) !== $kind ) {
        return new WP_Error( 'expired', findewerkstatt_t( 'Das Formular ist abgelaufen. Bitte prüfen Sie Ihre Angaben und senden Sie es erneut.' ) );
    }
    if ( ! add_option( $key, array( 'kind' => $kind, 'status' => 'processing' ), '', false ) ) {
        return new WP_Error( 'processing', findewerkstatt_t( 'Ihre Anfrage wird bereits bearbeitet. Bitte warten Sie einen Moment.' ) );
    }
    wp_schedule_single_event( time() + DAY_IN_SECONDS, 'fw_form_cleanup_lock', array( $key ) );
    return $key;
}
function findewerkstatt_form_complete( $key, $kind ) {
    update_option( $key, array( 'kind' => $kind, 'status' => 'done' ), false );
    delete_transient( 'fw_form_token_' . substr( $key, strlen( 'fw_form_lock_' ) ) );
}

/** Store a salted hash instead of the visitor's IP; throttle valid submission attempts. */
function findewerkstatt_form_rate_allowed( $kind, $limit ) {
    $address = isset( $_SERVER['REMOTE_ADDR'] ) && is_string( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
    $key = 'fw_rate_' . hash_hmac( 'sha256', $kind . '|' . $address, wp_salt( 'nonce' ) );
    $state = get_transient( $key );
    if ( ! is_array( $state ) || (int) ( $state['until'] ?? 0 ) <= time() ) { $state = array( 'count' => 0, 'until' => time() + HOUR_IN_SECONDS ); }
    if ( (int) $state['count'] >= $limit ) { return false; }
    $state['count']++;
    set_transient( $key, $state, max( 1, $state['until'] - time() ) );
    return true;
}

function findewerkstatt_contact_submit() {
    $values = array(
        'name' => findewerkstatt_form_text( 'name', 160 ), 'email' => findewerkstatt_form_text( 'email', 254 ),
        'subject' => findewerkstatt_form_text( 'subject', 160 ), 'message' => findewerkstatt_form_text( 'message', 5000, true ),
        'privacy' => findewerkstatt_form_checked( 'privacy' ),
    );
    $errors = array();
    if ( ( $_SERVER['REQUEST_METHOD'] ?? '' ) !== 'POST' || ! wp_verify_nonce( findewerkstatt_form_text( 'fw_kontakt_nonce', 100 ), 'fw_send_kontakt' ) ) {
        $errors[] = findewerkstatt_t( 'Bitte laden Sie das Kontaktformular neu und senden Sie Ihre Nachricht erneut.' );
    }
    if ( findewerkstatt_form_text( 'company_website' ) !== '' ) { $errors[] = findewerkstatt_t( 'Die Anfrage konnte nicht verarbeitet werden. Bitte versuchen Sie es erneut.' ); }
    if ( $values['name'] === '' || findewerkstatt_form_length( $values['name'] ) > 160 ) { $errors[] = findewerkstatt_t( 'Bitte geben Sie Ihren Namen an (höchstens 160 Zeichen).' ); }
    if ( ! is_email( $values['email'] ) || findewerkstatt_form_length( $values['email'] ) > 254 ) { $errors[] = findewerkstatt_t( 'Bitte geben Sie eine gültige E-Mail-Adresse an.' ); }
    if ( findewerkstatt_form_length( $values['subject'] ) > 160 ) { $errors[] = findewerkstatt_t( 'Der Betreff darf höchstens 160 Zeichen enthalten.' ); }
    if ( $values['message'] === '' || findewerkstatt_form_length( $values['message'] ) > 5000 ) { $errors[] = findewerkstatt_t( 'Bitte schreiben Sie eine Nachricht mit höchstens 5.000 Zeichen.' ); }
    if ( ! $values['privacy'] ) { $errors[] = findewerkstatt_t( 'Bitte bestätigen Sie, dass Sie die Datenschutzerklärung gelesen haben.' ); }
    if ( $errors ) { findewerkstatt_form_redirect( 'contact', 'error', findewerkstatt_t( 'Bitte prüfen Sie Ihre Angaben.' ), $values, $errors ); }
    $claim = findewerkstatt_form_claim( 'contact' );
    if ( is_wp_error( $claim ) ) {
        if ( $claim->get_error_code() === 'duplicate' ) { findewerkstatt_form_redirect( 'contact', 'success', findewerkstatt_t( 'Ihre Nachricht ist bereits bei uns eingegangen.' ), array(), array(), true ); }
        findewerkstatt_form_redirect( 'contact', 'error', $claim->get_error_message(), $values );
    }
    if ( ! findewerkstatt_form_rate_allowed( 'contact', 5 ) ) {
        delete_option( $claim );
        findewerkstatt_form_redirect( 'contact', 'error', findewerkstatt_t( 'Sie haben bereits mehrere Nachrichten gesendet. Bitte versuchen Sie es in einer Stunde erneut.' ), $values );
    }
    $subject = $values['subject'] ?: 'Kontaktanfrage';
    $body = "Name: {$values['name']}\nE-Mail: {$values['email']}\nBetreff: {$subject}\n\n{$values['message']}";
    $post_id = wp_insert_post( wp_slash( array( 'post_type' => 'fw_contact_message', 'post_status' => 'private', 'post_title' => $subject . ' – ' . $values['name'], 'post_content' => $body ) ), true );
    if ( is_wp_error( $post_id ) || ! $post_id ) {
        delete_option( $claim );
        findewerkstatt_form_redirect( 'contact', 'error', findewerkstatt_t( 'Ihre Nachricht konnte gerade nicht übermittelt werden. Bitte versuchen Sie es später erneut.' ), $values );
    }
    update_post_meta( $post_id, '_fw_contact_email', wp_slash( sanitize_email( $values['email'] ) ) );
    update_post_meta( $post_id, '_fw_privacy_acknowledged_at', current_time( 'mysql', true ) );
    $details = findewerkstatt_get_site_details();
    $recipient = sanitize_email( $details['email'] ?? '' );
    $mail_sent = is_email( $recipient ) && wp_mail( $recipient, '[FindeWerkstatt] ' . $subject,
        $body . findewerkstatt_t( "\n\nKontaktanfrage im Verwaltungsbereich: " ) . admin_url( 'post.php?post=' . $post_id . '&action=edit' ),
        array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . sanitize_email( $values['email'] ) ) );
    update_post_meta( $post_id, '_fw_notification_sent', $mail_sent ? 'yes' : 'no' );
    findewerkstatt_form_complete( $claim, 'contact' );
    $message = $mail_sent ? findewerkstatt_t( 'Vielen Dank! Ihre Nachricht ist bei uns eingegangen. Wir melden uns bei Ihnen unter der angegebenen E-Mail-Adresse.' ) : findewerkstatt_t( 'Ihre Nachricht ist bei uns eingegangen und bleibt erhalten. Die Benachrichtigung unseres Teams konnte gerade nicht versendet werden.' );
    findewerkstatt_form_redirect( 'contact', $mail_sent ? 'success' : 'warning', $message, array(), array(), true );
}
add_action( 'admin_post_fw_contact', 'findewerkstatt_contact_submit' );
add_action( 'admin_post_nopriv_fw_contact', 'findewerkstatt_contact_submit' );

function findewerkstatt_german_location_slug( $name ) {
    return sanitize_title( strtr( $name, array( 'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'Ä' => 'Ae', 'Ö' => 'Oe', 'Ü' => 'Ue', 'ß' => 'ss', 'ẞ' => 'SS' ) ) );
}
function findewerkstatt_form_terms( $taxonomy ) {
    $known = $taxonomy === 'service_type' ? FindeWerkstatt_German_Data::get_categories() : FindeWerkstatt_German_Data::get_car_brands();
    $terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false, 'slug' => array_keys( $known ), 'orderby' => 'name' ) );
    $map = array();
    if ( ! is_wp_error( $terms ) ) { foreach ( $terms as $term ) { if ( isset( $known[ $term->slug ] ) ) { $map[ $term->slug ] = $term; } } }
    return $map;
}
function findewerkstatt_form_selected_terms( $field, $allowed, &$errors ) {
    if ( ! isset( $_POST[ $field ] ) ) { return array(); }
    if ( ! is_array( $_POST[ $field ] ) || count( $_POST[ $field ] ) > count( $allowed ) ) { $errors[] = findewerkstatt_t( 'Bitte wählen Sie Leistungen und Marken nur aus den angezeigten Optionen aus.' ); return array(); }
    $selected = array();
    foreach ( $_POST[ $field ] as $raw ) {
        if ( ! is_string( $raw ) ) { $errors[] = findewerkstatt_t( 'Eine ausgewählte Leistung oder Marke ist ungültig.' ); continue; }
        $slug = sanitize_text_field( wp_unslash( $raw ) );
        if ( ! isset( $allowed[ $slug ] ) ) { $errors[] = findewerkstatt_t( 'Eine ausgewählte Leistung oder Marke ist nicht verfügbar. Bitte prüfen Sie die Auswahl.' ); continue; }
        $selected[] = $slug;
    }
    return array_values( array_unique( $selected ) );
}

/** Resolve within the selected state without moving existing cities between states. */
function findewerkstatt_registration_location( $city_name, $state_slug ) {
    $states = array_column( FindeWerkstatt_German_Data::get_bundeslaender(), null, 'slug' );
    if ( ! isset( $states[ $state_slug ] ) ) { return new WP_Error( 'state', findewerkstatt_t( 'Bitte wählen Sie ein Bundesland aus.' ) ); }
    $state = get_term_by( 'slug', $state_slug, 'mechanic_city' );
    if ( ! $state || is_wp_error( $state ) ) { return new WP_Error( 'state_unavailable', findewerkstatt_t( 'Dieses Bundesland ist gerade nicht verfügbar. Bitte kontaktieren Sie uns.' ) ); }
    if ( ! preg_match( "/^[\p{L}\p{M}][\p{L}\p{M}\s.'’()\/-]{1,99}$/u", $city_name ) ) { return new WP_Error( 'city', findewerkstatt_t( 'Bitte geben Sie einen gültigen Ortsnamen mit 2 bis 100 Zeichen an.' ) ); }
    $slug = findewerkstatt_german_location_slug( $city_name );
    // Imported locations are resolved by state and source identity, including shared names.
    $directory = findewerkstatt_city_directory_data();
    $known_elsewhere = false; $known_here = array();
    if ( ! is_wp_error( $directory ) ) {
        foreach ( $directory['cities'] as $city ) {
            $keys = array_map( 'findewerkstatt_city_directory_key', array_unique( array( $city['name'], $city['source_city'] ?? '', $city['official_name'] ?? '' ) ) );
            if ( ! in_array( $slug, $keys, true ) ) { continue; }
            if ( $city['state_slug'] === $state_slug ) { $known_here[] = $city; } else { $known_elsewhere = true; }
        }
        if ( count( $known_here ) > 1 ) { return new WP_Error( 'city_ambiguous', findewerkstatt_t( 'Bitte geben Sie den vollständigen Ortsnamen an, zum Beispiel ' ) . implode( ' oder ', array_column( $known_here, 'name' ) ) . '.' ); }
        if ( $known_here ) {
            $city = $known_here[0];
            $imported = get_terms( array( 'taxonomy' => 'mechanic_city', 'hide_empty' => false, 'meta_key' => '_fw_city_source_row', 'meta_value' => $city['source_row'], 'number' => 1 ) );
            if ( ! is_wp_error( $imported ) && $imported ) {
                $term = $imported[0];
                if ( (int) $term->term_id === (int) $state->term_id || in_array( (int) $state->term_id, array_map( 'intval', get_ancestors( $term->term_id, 'mechanic_city', 'taxonomy' ) ), true ) ) {
                    return array( 'term_id' => (int) $term->term_id, 'state_id' => (int) $state->term_id );
                }
            }
            $city_name = $city['name']; $slug = findewerkstatt_city_directory_key( $city_name );
        } elseif ( $known_elsewhere ) { return new WP_Error( 'city_state', findewerkstatt_t( 'Der angegebene Ort gehört zu einem anderen Bundesland. Bitte prüfen Sie Ihre Auswahl.' ) ); }
    }
    foreach ( FindeWerkstatt_German_Data::get_top_cities() as $known_slug => $known_city ) {
        if ( $slug === $known_slug || $slug === findewerkstatt_german_location_slug( $known_city['name'] ) ) {
            if ( $known_city['land'] !== $state_slug ) {
                if ( $known_here ) { continue; }
                return new WP_Error( 'city_state', findewerkstatt_t( 'Der angegebene Ort gehört zu einem anderen Bundesland. Bitte prüfen Sie Ihre Auswahl.' ) );
            }
            $slug = $known_slug; $city_name = $known_city['name']; break;
        }
    }
    if ( $slug === $state_slug && in_array( $state_slug, array( 'berlin', 'hamburg' ), true ) ) { return array( 'term_id' => (int) $state->term_id, 'state_id' => (int) $state->term_id ); }
    $terms = get_terms( array( 'taxonomy' => 'mechanic_city', 'hide_empty' => false, 'child_of' => (int) $state->term_id ) );
    if ( is_wp_error( $terms ) ) { return new WP_Error( 'city_unavailable', findewerkstatt_t( 'Die Ortsauswahl ist gerade nicht verfügbar. Bitte versuchen Sie es später erneut.' ) ); }
    foreach ( $terms as $term ) {
        $term_name = preg_replace( '/\s*\(Stadt\)$/u', '', $term->name );
        if ( $term->slug === $slug || $term->slug === $slug . '-' . $state_slug || findewerkstatt_german_location_slug( $term_name ) === $slug ) { return array( 'term_id' => (int) $term->term_id, 'state_id' => (int) $state->term_id ); }
    }
    if ( get_term_by( 'slug', $slug, 'mechanic_city' ) ) { $slug .= '-' . $state_slug; }
    return array( 'term_id' => 0, 'state_id' => (int) $state->term_id, 'name' => $city_name, 'slug' => $slug );
}

/** Accepted aliases for the same location identify one pending registration. */
function findewerkstatt_registration_fingerprint( $values, $location ) {
    $slug = $location['slug'] ?? '';
    if ( ! empty( $location['term_id'] ) ) {
        $term = get_term( (int) $location['term_id'], 'mechanic_city' );
        if ( $term && ! is_wp_error( $term ) ) { $slug = $term->slug; }
    }
    $name = trim( preg_replace( '/\s+/u', ' ', $values['company_name'] ) );
    $identity = $name . '|' . trim( $values['email'] );
    $identity = function_exists( 'mb_strtolower' ) ? mb_strtolower( $identity, 'UTF-8' ) : strtolower( $identity );
    return hash( 'sha256', $identity . '|' . $slug . '|' . (int) $location['state_id'] );
}

function findewerkstatt_registration_submit() {
    if ( ! is_user_logged_in() || ( ! findewerkstatt_is_workshop_member() && ! current_user_can( 'manage_options' ) ) ) {
        findewerkstatt_member_redirect( 'error', findewerkstatt_t( 'Bitte erstellen Sie ein Werkstattkonto oder melden Sie sich mit Ihrem Werkstattkonto an, um einen Betrieb einzutragen.' ) );
    }
    $owner_id = get_current_user_id();
    $values = array();
    foreach ( array( 'company_name' => 160, 'contact_name' => 160, 'email' => 254, 'phone' => 60, 'whatsapp' => 60, 'website' => 250, 'street' => 200, 'plz' => 5, 'city' => 100, 'bundesland' => 60 ) as $field => $limit ) { $values[ $field ] = findewerkstatt_form_text( $field, $limit ); }
    $values['description'] = findewerkstatt_form_text( 'description', 5000, true );
    foreach ( array( 'is_master', 'is_24h', 'privacy', 'public_email' ) as $field ) { $values[ $field ] = findewerkstatt_form_checked( $field ); }
    $errors = array();
    $spoken_languages = findewerkstatt_validate_workshop_languages( isset( $_POST['spoken_languages'] ) ? wp_unslash( $_POST['spoken_languages'] ) : array() );
    if ( is_wp_error( $spoken_languages ) ) { $errors[] = $spoken_languages->get_error_message(); }
    else { $values['spoken_languages'] = $spoken_languages; }
    $selected_plan = array_key_exists( 'selected_plan', $_POST ) && is_string( $_POST['selected_plan'] ) ? wp_unslash( $_POST['selected_plan'] ) : findewerkstatt_member_plan( $owner_id );
    if ( ( array_key_exists( 'selected_plan', $_POST ) && ! is_string( $_POST['selected_plan'] ) ) || ! isset( findewerkstatt_membership_plans()[ $selected_plan ] ) ) {
        $errors[] = findewerkstatt_t( 'Bitte wählen Sie ein verfügbares Paket aus.' );
    } else { $values['selected_plan'] = $selected_plan; }
    $service_terms = findewerkstatt_form_terms( 'service_type' ); $brand_terms = findewerkstatt_form_terms( 'car_brand' );
    $values['services'] = findewerkstatt_form_selected_terms( 'services', $service_terms, $errors );
    $values['brands'] = findewerkstatt_form_selected_terms( 'brands', $brand_terms, $errors );
    if ( ( $_SERVER['REQUEST_METHOD'] ?? '' ) !== 'POST' || ! wp_verify_nonce( findewerkstatt_form_text( 'fw_register_nonce', 100 ), 'fw_register_workshop' ) ) { $errors[] = findewerkstatt_t( 'Bitte laden Sie das Anmeldeformular neu und senden Sie Ihre Angaben erneut.' ); }
    if ( findewerkstatt_form_text( 'company_website' ) !== '' ) { $errors[] = findewerkstatt_t( 'Die Anfrage konnte nicht verarbeitet werden. Bitte versuchen Sie es erneut.' ); }
    if ( $values['company_name'] === '' || findewerkstatt_form_length( $values['company_name'] ) > 160 ) { $errors[] = findewerkstatt_t( 'Bitte geben Sie den Namen Ihres Betriebs an (höchstens 160 Zeichen).' ); }
    if ( ! is_email( $values['email'] ) || findewerkstatt_form_length( $values['email'] ) > 254 ) { $errors[] = findewerkstatt_t( 'Bitte geben Sie eine gültige E-Mail-Adresse an.' ); }
    $phone = findewerkstatt_normalize_phone( $values['phone'] );
    $whatsapp = $values['whatsapp'] !== '' ? findewerkstatt_normalize_phone( $values['whatsapp'] ) : '';
    if ( ! $phone ) { $errors[] = findewerkstatt_t( 'Bitte geben Sie eine gültige Telefonnummer an, zum Beispiel 030 1234567 oder +49 30 1234567.' ); }
    if ( $values['whatsapp'] !== '' && ! $whatsapp ) { $errors[] = findewerkstatt_t( 'Bitte prüfen Sie die WhatsApp-Nummer oder lassen Sie das optionale Feld frei.' ); }
    if ( $values['plz'] !== '' && ! preg_match( '/^\d{5}$/', $values['plz'] ) ) { $errors[] = findewerkstatt_t( 'Eine deutsche Postleitzahl besteht aus fünf Ziffern.' ); }
    foreach ( array( 'contact_name' => 160, 'street' => 200, 'description' => 5000 ) as $field => $limit ) { if ( findewerkstatt_form_length( $values[ $field ] ) > $limit ) { $errors[] = findewerkstatt_t( 'Bitte kürzen Sie Ihre Angaben. Die Betriebsbeschreibung darf höchstens 5.000 Zeichen enthalten.' ); } }
    $website = '';
    if ( $values['website'] !== '' ) {
        $website = findewerkstatt_get_website_url( $values['website'] );
        if ( ! $website || findewerkstatt_form_length( $values['website'] ) > 250 ) { $errors[] = findewerkstatt_t( 'Bitte geben Sie eine gültige Website-Adresse an oder lassen Sie das optionale Feld frei.' ); }
    }
    $location = findewerkstatt_registration_location( $values['city'], $values['bundesland'] );
    if ( is_wp_error( $location ) ) { $errors[] = $location->get_error_message(); }
    if ( ! $values['privacy'] ) { $errors[] = findewerkstatt_t( 'Bitte bestätigen Sie, dass Sie die Datenschutzerklärung gelesen haben und den Betrieb vertreten dürfen.' ); }
    if ( $errors ) { findewerkstatt_form_redirect( 'registration', 'error', findewerkstatt_t( 'Bitte prüfen Sie Ihre Angaben.' ), $values, array_values( array_unique( $errors ) ) ); }
    $claim = findewerkstatt_form_claim( 'registration' );
    if ( is_wp_error( $claim ) ) {
        if ( $claim->get_error_code() === 'duplicate' ) { findewerkstatt_form_redirect( 'registration', 'success', findewerkstatt_t( 'Ihre Anmeldung ist bereits eingegangen. Wir prüfen den Eintrag vor der Veröffentlichung.' ), array(), array(), true ); }
        findewerkstatt_form_redirect( 'registration', 'error', $claim->get_error_message(), $values );
    }
    $fingerprint = findewerkstatt_registration_fingerprint( $values, $location );
    $legacy_fingerprint = hash( 'sha256', strtolower( $values['company_name'] . '|' . $values['email'] ) . '|' . findewerkstatt_german_location_slug( $values['city'] ) . '|' . $values['bundesland'] );
    $lock = findewerkstatt_member_acquire_lock( $owner_id, 'workshop' );
    if ( is_wp_error( $lock ) ) { delete_option( $claim ); findewerkstatt_form_redirect( 'registration', 'error', $lock->get_error_message(), $values ); }
    try {
        $result = findewerkstatt_registration_create_pending( $values, $location, $service_terms, $brand_terms, $phone, $whatsapp, $website, $fingerprint, $legacy_fingerprint, $owner_id );
    } finally { findewerkstatt_member_release_lock( $lock ); }
    if ( is_wp_error( $result ) ) { delete_option( $claim ); findewerkstatt_form_redirect( 'registration', 'error', $result->get_error_message(), $values ); }
    findewerkstatt_form_complete( $claim, 'registration' );
    if ( ! empty( $result['duplicate'] ) ) { findewerkstatt_form_redirect( 'registration', 'success', findewerkstatt_t( 'Eine Anmeldung mit diesen Angaben ist bereits eingegangen. Wir prüfen den Eintrag vor der Veröffentlichung.' ), array(), array(), true ); }
    $message = findewerkstatt_t( 'Vielen Dank! Ihre Anmeldung ist eingegangen. Wir prüfen die Angaben und veröffentlichen Ihren Betrieb anschließend im Verzeichnis. Den Status sehen Sie in Ihrem Konto.' );
    $type = 'success';
    if ( ! empty( $result['plan_requested'] ) ) {
        $message .= findewerkstatt_t( ' Ihre Anfrage für das gewählte Paket ist ebenfalls eingegangen. Bis zur Freigabe bleibt Ihr aktuelles Paket aktiv. Es wurde keine Zahlung ausgelöst.' );
    } elseif ( ! empty( $result['plan_request_error'] ) ) {
        $type = 'warning';
        $message .= findewerkstatt_t( ' Die Paketanfrage konnte gerade nicht gespeichert werden. Ihr Betriebseintrag bleibt erhalten. Bitte senden Sie die Paketanfrage in Ihrem Konto erneut; bis zur Freigabe bleibt Ihr aktuelles Paket aktiv.' );
    }
    findewerkstatt_form_redirect( 'registration', $type, $message, array(), array(), true );
}

/** Run while holding the member's workshop lock; never redirect or exit here. */
function findewerkstatt_registration_create_pending( $values, $location, $service_terms, $brand_terms, $phone, $whatsapp, $website, $fingerprint, $legacy_fingerprint, $owner_id ) {
    if ( ! is_user_logged_in() || get_current_user_id() !== (int) $owner_id || ( ! findewerkstatt_is_workshop_member() && ! current_user_can( 'manage_options' ) ) ) {
        return new WP_Error( 'member_permission', findewerkstatt_t( 'Bitte melden Sie sich mit Ihrem Werkstattkonto an.' ) );
    }
    $selected_plan = $values['selected_plan'] ?? findewerkstatt_member_plan( $owner_id );
    $spoken_languages = findewerkstatt_validate_workshop_languages( $values['spoken_languages'] ?? array() );
    if ( is_wp_error( $spoken_languages ) ) { return $spoken_languages; }
    if ( ! is_string( $selected_plan ) || ! isset( findewerkstatt_membership_plans()[ $selected_plan ] ) ) {
        return new WP_Error( 'registration_plan', findewerkstatt_t( 'Bitte wählen Sie ein verfügbares Paket aus.' ) );
    }
    $existing = get_posts( array( 'post_type' => 'mechanic', 'post_status' => 'pending', 'posts_per_page' => 1, 'fields' => 'ids',
        'meta_query' => array( 'relation' => 'OR',
            array( 'key' => '_fw_registration_fingerprint', 'value' => $fingerprint ),
            array( 'key' => '_fw_registration_fingerprint', 'value' => $legacy_fingerprint ),
        ),
    ) );
    // A matching anonymous/imported registration is not automatically assigned to this account.
    if ( $existing ) { return array( 'post_id' => (int) $existing[0], 'duplicate' => true ); }
    if ( ! current_user_can( 'manage_options' ) && ! findewerkstatt_member_can_add_workshop( $owner_id ) ) {
        return new WP_Error( 'member_quota', findewerkstatt_t( 'Die Anzahl der Betriebe in Ihrem aktuellen Paket ist erreicht. In Ihrem Konto können Sie ein anderes Paket anfragen.' ) );
    }
    if ( ! findewerkstatt_form_rate_allowed( 'registration', 3 ) ) {
        return new WP_Error( 'registration_rate', findewerkstatt_t( 'Sie haben bereits mehrere Betriebe angemeldet. Bitte versuchen Sie es in einer Stunde erneut oder kontaktieren Sie uns.' ) );
    }
    $operator_id = findewerkstatt_membership_operator_id();
    if ( ! $operator_id ) { return new WP_Error( 'operator_unavailable', findewerkstatt_t( 'Ihr Eintrag konnte gerade nicht gespeichert werden. Bitte versuchen Sie es später erneut.' ) ); }
    $post_id = wp_insert_post( wp_slash( array( 'post_type' => 'mechanic', 'post_status' => 'pending', 'post_title' => $values['company_name'], 'post_content' => $values['description'], 'post_author' => $operator_id ) ), true );
    if ( is_wp_error( $post_id ) || ! $post_id ) { return new WP_Error( 'registration_save', findewerkstatt_t( 'Ihr Eintrag konnte gerade nicht gespeichert werden. Bitte versuchen Sie es später erneut.' ) ); }
    if ( ! $location['term_id'] ) {
        $term = wp_insert_term( $location['name'], 'mechanic_city', array( 'slug' => $location['slug'], 'parent' => $location['state_id'] ) );
        if ( is_wp_error( $term ) && $term->get_error_code() === 'term_exists' ) {
            $term_id = (int) $term->get_error_data(); $check = get_term( $term_id, 'mechanic_city' );
            if ( $check && ! is_wp_error( $check ) && (int) $check->parent === $location['state_id'] ) { $term = array( 'term_id' => $term_id ); }
        }
        if ( is_wp_error( $term ) ) { wp_delete_post( $post_id, true ); return new WP_Error( 'registration_location', findewerkstatt_t( 'Der Ort konnte gerade nicht zugeordnet werden. Bitte versuchen Sie es später erneut.' ) ); }
        $location['term_id'] = (int) $term['term_id'];
    }
    $assignments = array(
        'mechanic_city' => array( (int) $location['term_id'] ),
        'service_type' => array_map( function( $slug ) use ( $service_terms ) { return (int) $service_terms[ $slug ]->term_id; }, $values['services'] ),
        'car_brand' => array_map( function( $slug ) use ( $brand_terms ) { return (int) $brand_terms[ $slug ]->term_id; }, $values['brands'] ),
    );
    foreach ( $assignments as $taxonomy => $ids ) {
        if ( is_wp_error( wp_set_object_terms( $post_id, $ids, $taxonomy ) ) ) { wp_delete_post( $post_id, true ); return new WP_Error( 'registration_terms', findewerkstatt_t( 'Ihr Eintrag konnte gerade nicht vollständig gespeichert werden. Bitte versuchen Sie es später erneut.' ) ); }
    }
    foreach ( array(
        '_mechanic_phone' => $phone, '_mechanic_whatsapp' => $whatsapp, '_mechanic_email' => sanitize_email( $values['email'] ),
        '_mechanic_email_public' => $values['public_email'] ? 'yes' : 'no', '_mechanic_website' => $website,
        '_mechanic_address' => $values['street'], '_mechanic_plz' => $values['plz'], '_mechanic_contact_person' => $values['contact_name'],
        '_mechanic_is_master' => $values['is_master'] ? 'yes' : 'no', '_mechanic_emergency_24h' => $values['is_24h'] ? 'yes' : 'no',
        '_mechanic_is_verified' => 'no', '_fw_registration_fingerprint' => $fingerprint,
        '_fw_registration_plan' => $selected_plan,
        '_mechanic_languages' => $spoken_languages,
        '_fw_privacy_acknowledged_at' => current_time( 'mysql', true ),
    ) as $key => $value ) { update_post_meta( $post_id, $key, wp_slash( $value ) ); }
    if ( ! add_post_meta( $post_id, '_fw_owner_user_id', (int) $owner_id, true ) ) {
        wp_delete_post( $post_id, true );
        return new WP_Error( 'registration_owner', findewerkstatt_t( 'Ihr Eintrag konnte gerade nicht Ihrem Konto zugeordnet werden. Bitte versuchen Sie es später erneut.' ) );
    }
    $result = array( 'post_id' => (int) $post_id, 'duplicate' => false );
    if ( findewerkstatt_is_workshop_member( $owner_id ) && $selected_plan !== findewerkstatt_member_plan( $owner_id ) ) {
        $request = findewerkstatt_member_plan_request( $selected_plan, $owner_id );
        $result[ is_wp_error( $request ) ? 'plan_request_error' : 'plan_requested' ] = true;
    }
    return $result;
}
add_action( 'admin_post_fw_register_workshop', 'findewerkstatt_registration_submit' );
add_action( 'admin_post_nopriv_fw_register_workshop', 'findewerkstatt_registration_submit' );
