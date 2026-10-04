<?php
/**
 * Private workshop accounts. A package request never grants a paid package.
 *
 * @package FindeWerkstatt
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Only this role is managed here; existing administrator/editor roles are untouched. */
function findewerkstatt_membership_register_role() {
    $role = get_role( 'fw_workshop_member' );
    if ( ! $role ) { $role = add_role( 'fw_workshop_member', 'Werkstattmitglied', array( 'read' => true ) ); }
    if ( $role ) {
        foreach ( $role->capabilities as $capability => $enabled ) {
            if ( 'read' !== $capability ) { $role->remove_cap( $capability ); }
        }
        if ( ! $role->has_cap( 'read' ) ) { $role->add_cap( 'read' ); }
    }
}
add_action( 'init', 'findewerkstatt_membership_register_role', 8 );

function findewerkstatt_is_workshop_member( $user_id = 0 ) {
    $user = get_userdata( $user_id ? absint( $user_id ) : get_current_user_id() );
    return $user && in_array( 'fw_workshop_member', (array) $user->roles, true );
}

/** Saved commercial settings cannot introduce roles, capabilities or arbitrary plan IDs. */
function findewerkstatt_membership_plans() {
    $defaults = array(
        'free' => array( 'name' => 'Kostenlos', 'monthly_price' => '0', 'listing_limit' => 1 ),
        'plus' => array( 'name' => 'Plus', 'monthly_price' => '', 'listing_limit' => 3 ),
        'professional' => array( 'name' => 'Professional', 'monthly_price' => '', 'listing_limit' => 10 ),
    );
    $settings = get_option( 'findewerkstatt_membership_settings', array() );
    $saved = is_array( $settings ) && is_array( $settings['plans'] ?? null ) ? $settings['plans'] : array();
    foreach ( $defaults as $id => &$plan ) {
        $row = is_array( $saved[ $id ] ?? null ) ? $saved[ $id ] : array();
        if ( is_string( $row['name'] ?? null ) ) {
            $name = sanitize_text_field( $row['name'] );
            if ( '' !== $name && findewerkstatt_form_length( $name ) <= 60 ) { $plan['name'] = $name; }
        }
        $limit = $row['listing_limit'] ?? null;
        if ( is_scalar( $limit ) && preg_match( '/^\d{1,3}$/', (string) $limit ) ) { $plan['listing_limit'] = max( 1, min( 100, (int) $limit ) ); }
        if ( 'free' !== $id && is_string( $row['monthly_price'] ?? null ) ) {
            $price = str_replace( ',', '.', trim( $row['monthly_price'] ) );
            if ( preg_match( '/^\d{1,5}(?:\.\d{1,2})?$/', $price ) ) { $plan['monthly_price'] = $price; }
        }
    }
    unset( $plan );
    return $defaults;
}

function findewerkstatt_member_plan( $user_id = 0 ) {
    $user_id = $user_id ? absint( $user_id ) : get_current_user_id();
    $plan = $user_id ? get_user_meta( $user_id, '_fw_member_plan', true ) : '';
    return is_string( $plan ) && isset( findewerkstatt_membership_plans()[ $plan ] ) ? $plan : 'free';
}

function findewerkstatt_member_listing_limit( $user_id = 0 ) {
    $plans = findewerkstatt_membership_plans();
    return $plans[ findewerkstatt_member_plan( $user_id ) ]['listing_limit'];
}

/** A post author from an import is never proof of business ownership. */
function findewerkstatt_member_owns_workshop( $post_id, $user_id = 0 ) {
    $user_id = $user_id ? absint( $user_id ) : get_current_user_id();
    if ( ! $user_id || ( get_current_user_id() !== $user_id && ! current_user_can( 'manage_options' ) ) ) { return false; }
    $post = get_post( absint( $post_id ) );
    return $post && 'mechanic' === $post->post_type && 'trash' !== $post->post_status && $user_id === (int) get_post_meta( $post->ID, '_fw_owner_user_id', true );
}

function findewerkstatt_member_owned_workshops( $user_id = 0 ) {
    $user_id = $user_id ? absint( $user_id ) : get_current_user_id();
    if ( ! $user_id || ( get_current_user_id() !== $user_id && ! current_user_can( 'manage_options' ) ) ) { return array(); }
    return get_posts( array(
        'post_type' => 'mechanic', 'post_status' => array( 'publish', 'pending', 'draft', 'private', 'future' ),
        'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC',
        'meta_key' => '_fw_owner_user_id', 'meta_value' => $user_id, 'meta_compare' => '=', 'meta_type' => 'NUMERIC',
        'no_found_rows' => true,
    ) );
}

function findewerkstatt_member_can_add_workshop( $user_id = 0 ) {
    $user_id = $user_id ? absint( $user_id ) : get_current_user_id();
    if ( ! $user_id || ! get_userdata( $user_id ) || ( get_current_user_id() !== $user_id && ! current_user_can( 'manage_options' ) ) ) { return false; }
    return count( findewerkstatt_member_owned_workshops( $user_id ) ) < findewerkstatt_member_listing_limit( $user_id );
}

/** Serialize quota checks and requests; the caller must release in a finally block. */
function findewerkstatt_member_acquire_lock( $user_id, $purpose = 'workshop' ) {
    $user_id = absint( $user_id );
    if ( ! $user_id || ! in_array( $purpose, array( 'workshop', 'plan', 'account' ), true ) || ( get_current_user_id() !== $user_id && ! current_user_can( 'manage_options' ) ) ) {
        return new WP_Error( 'member_permission', findewerkstatt_t( 'Diese Anfrage konnte nicht verarbeitet werden.' ) );
    }
    $key = 'fw_member_lock_' . $purpose . '_' . $user_id;
    return findewerkstatt_member_lock_key( $key );
}

/** Compare-and-delete prevents an expired lock's owner from deleting its replacement. */
function findewerkstatt_member_lock_key( $key ) {
    if ( ! preg_match( '/^fw_member_lock_(?:workshop|plan|account|review)_\d+$/', $key ) ) { return new WP_Error( 'member_lock', findewerkstatt_t( 'Ungültige Anfrage.' ) ); }
    $existing = get_option( $key );
    $created = is_array( $existing ) ? (int) ( $existing['time'] ?? 0 ) : ( is_numeric( $existing ) ? (int) $existing : 0 );
    if ( $existing && $created < time() - 2 * MINUTE_IN_SECONDS ) {
        findewerkstatt_member_release_lock( array( 'key' => $key, 'value' => $existing ) );
    }
    $value = array( 'token' => wp_generate_uuid4(), 'time' => time() );
    if ( ! add_option( $key, $value, '', false ) ) { return new WP_Error( 'member_busy', findewerkstatt_t( 'Ihre Anfrage wird bereits bearbeitet. Bitte versuchen Sie es gleich erneut.' ) ); }
    return array( 'key' => $key, 'value' => $value );
}

function findewerkstatt_member_release_lock( $lock ) {
    global $wpdb;
    if ( ! is_array( $lock ) || ! is_string( $lock['key'] ?? null ) || ! preg_match( '/^fw_member_lock_(?:workshop|plan|account|review)_\d+$/', $lock['key'] ) || ! isset( $lock['value'] ) ) { return; }
    $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s", $lock['key'], maybe_serialize( $lock['value'] ) ) );
    wp_cache_delete( $lock['key'], 'options' );
    wp_cache_delete( 'notoptions', 'options' );
}

function findewerkstatt_member_pending_request( $user_id = 0 ) {
    $user_id = $user_id ? absint( $user_id ) : get_current_user_id();
    if ( ! $user_id || ( get_current_user_id() !== $user_id && ! current_user_can( 'manage_options' ) ) ) { return array(); }
    $request = get_user_meta( $user_id, '_fw_member_plan_request', true );
    if ( ! is_array( $request ) || ! is_string( $request['id'] ?? null ) || ! preg_match( '/^[a-f0-9-]{36}$/', $request['id'] ) || ! is_string( $request['plan'] ?? null ) || ! isset( findewerkstatt_membership_plans()[ $request['plan'] ] ) || ! in_array( $request['status'] ?? '', array( 'pending', 'approved', 'rejected', 'cancelled' ), true ) ) { return array(); }
    return $request;
}

/** Requests are private. Only the administrator approval handler may alter the active plan. */
function findewerkstatt_member_plan_request( $target, $user_id = 0 ) {
    $user_id = $user_id ? absint( $user_id ) : get_current_user_id();
    if ( ! $user_id || ! findewerkstatt_is_workshop_member( $user_id ) || ( get_current_user_id() !== $user_id && ! current_user_can( 'manage_options' ) ) ) {
        return new WP_Error( 'member_permission', findewerkstatt_t( 'Bitte melden Sie sich mit Ihrem Werkstattkonto an, um ein Paket anzufragen.' ) );
    }
    if ( ! is_string( $target ) || ! isset( findewerkstatt_membership_plans()[ $target ] ) ) { return new WP_Error( 'member_plan', findewerkstatt_t( 'Bitte wählen Sie ein verfügbares Paket aus.' ) ); }
    if ( $target === findewerkstatt_member_plan( $user_id ) ) { return new WP_Error( 'member_plan_active', findewerkstatt_t( 'Dieses Paket ist bereits für Ihr Konto aktiv.' ) ); }
    $lock = findewerkstatt_member_acquire_lock( $user_id, 'plan' );
    if ( is_wp_error( $lock ) ) { return $lock; }
    try {
        wp_cache_delete( $user_id, 'user_meta' );
        if ( $target === findewerkstatt_member_plan( $user_id ) ) { return new WP_Error( 'member_plan_active', findewerkstatt_t( 'Dieses Paket ist bereits für Ihr Konto aktiv.' ) ); }
        $existing = findewerkstatt_member_pending_request( $user_id );
        if ( 'pending' === ( $existing['status'] ?? '' ) && $target === $existing['plan'] ) { return $existing; }
        $request = array(
            'id' => wp_generate_uuid4(), 'plan' => $target, 'previous_plan' => findewerkstatt_member_plan( $user_id ),
            'status' => 'pending', 'created_at' => current_time( 'mysql', true ),
        );
        if ( ! update_user_meta( $user_id, '_fw_member_plan_request', $request ) ) { return new WP_Error( 'member_save', findewerkstatt_t( 'Ihre Paketanfrage konnte gerade nicht gespeichert werden. Bitte versuchen Sie es später erneut.' ) ); }
        return $request;
    } finally { findewerkstatt_member_release_lock( $lock ); }
}

/** Preserve only a small whitelist of non-sensitive values in a private, single-use notice. */
function findewerkstatt_member_redirect( $type, $message, $values = array(), $errors = array(), $kind = '' ) {
    $type = in_array( $type, array( 'success', 'warning', 'error' ), true ) ? $type : 'error';
    $safe_values = array();
    foreach ( array( 'name' => 160, 'email' => 254, 'selected_plan' => 30 ) as $key => $limit ) {
        if ( is_string( $values[ $key ] ?? null ) ) {
            $value = sanitize_text_field( $values[ $key ] );
            $safe_values[ $key ] = function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $limit ) : substr( $value, 0, $limit );
        }
    }
    if ( isset( $values['remember'] ) ) { $safe_values['remember'] = true === $values['remember']; }
    if ( in_array( $kind, array( 'workshop_claim', 'workshop_change', 'workshop_request' ), true ) && function_exists( 'findewerkstatt_member_workshop_notice_values' ) ) {
        $safe_values = findewerkstatt_member_workshop_notice_values( $kind, $values );
    }
    $safe_errors = array();
    foreach ( array_slice( is_array( $errors ) ? $errors : array(), 0, 12 ) as $error ) {
        if ( is_string( $error ) ) { $safe_errors[] = sanitize_text_field( $error ); }
    }
    $token = wp_generate_password( 32, false, false );
    set_transient( 'fw_member_notice_' . $token, array(
        'type' => $type, 'message' => sanitize_text_field( $message ), 'values' => $safe_values,
        'errors' => $safe_errors, 'kind' => sanitize_key( $kind ), 'user_id' => get_current_user_id(),
    ), 15 * MINUTE_IN_SECONDS );
    nocache_headers();
    $url = add_query_arg( 'fw_member_notice', $token, findewerkstatt_page_url( 'mein-konto' ) );
    if ( in_array( $kind, array( 'login', 'signup' ), true ) ) {
        $intent = absint( findewerkstatt_form_text( 'return_workshop', 12 ) );
        $post = $intent ? get_post( $intent ) : null;
        if ( $post && 'mechanic' === $post->post_type && 'publish' === $post->post_status && ! get_post_meta( $intent, '_fw_owner_user_id', true ) ) { $url = add_query_arg( 'uebernehmen', $intent, $url ); }
    }
    wp_safe_redirect( $url, 303 );
    exit;
}

function findewerkstatt_member_notice() {
    nocache_headers();
    if ( ! is_string( $_GET['fw_member_notice'] ?? null ) ) { return array(); }
    $token = wp_unslash( $_GET['fw_member_notice'] );
    if ( ! preg_match( '/^[a-zA-Z0-9]{32}$/', $token ) ) { return array(); }
    $notice = get_transient( 'fw_member_notice_' . $token );
    if ( ! is_array( $notice ) || (int) ( $notice['user_id'] ?? -1 ) !== get_current_user_id() ) { return array(); }
    delete_transient( 'fw_member_notice_' . $token );
    return $notice;
}

/** Passwords are read verbatim and are never placed in notices, URLs or metadata. */
function findewerkstatt_member_password_field( $key ) {
    if ( ! is_string( $_POST[ $key ] ?? null ) || strlen( $_POST[ $key ] ) > 4096 ) { return ''; }
    return wp_unslash( $_POST[ $key ] );
}

function findewerkstatt_member_valid_post( $action ) {
    if ( ! is_user_logged_in() && in_array( $action, array( 'fw_member_signup', 'fw_member_login', 'fw_member_resend_verification' ), true ) && ( ! is_string( $_COOKIE['fw_member_guest'] ?? null ) || ! preg_match( '/^[a-f0-9]{64}$/', $_COOKIE['fw_member_guest'] ) ) ) { return false; }
    return 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) && wp_verify_nonce( findewerkstatt_form_text( 'fw_member_nonce', 100 ), $action );
}

function findewerkstatt_member_is_email_verified( $user_id = 0 ) {
    $user_id = $user_id ? absint( $user_id ) : get_current_user_id();
    if ( ! $user_id ) { return false; }
    if ( user_can( $user_id, 'manage_options' ) ) { return true; }
    $status = get_user_meta( $user_id, '_fw_email_verified', true );
    if ( '' === $status ) { return true; }
    return 'yes' === $status;
}

function findewerkstatt_member_send_verification_email( $user_id, $language = '' ) {
    $user = get_userdata( absint( $user_id ) );
    if ( ! $user || ! is_email( $user->user_email ) ) { return false; }
    $language = $language ?: ( get_user_meta( $user_id, '_fw_member_language', true ) ?: ( function_exists( 'findewerkstatt_language' ) ? findewerkstatt_language() : 'de' ) );
    $token = wp_generate_password( 48, false, false );
    update_user_meta( $user_id, '_fw_email_verification_token', $token );
    update_user_meta( $user_id, '_fw_email_verification_expires', time() + 48 * HOUR_IN_SECONDS );
    update_user_meta( $user_id, '_fw_member_language', $language );

    $verify_url = add_query_arg( array( 'fw_verify_email' => $token ), findewerkstatt_page_url( 'mein-konto' ) );
    if ( function_exists( 'findewerkstatt_localize_url' ) ) {
        $verify_url = findewerkstatt_localize_url( $verify_url, $language );
    }

    $name = $user->display_name ?: $user->user_email;

    switch ( $language ) {
        case 'tr':
            $subject = '[FindeWerkstatt] E-posta adresinizi onaylayın';
            $body  = "Merhaba " . $name . ",\n\n";
            $body .= "FindeWerkstatt.de platformuna kaydolduğunuz için teşekkür ederiz.\n\n";
            $body .= "Hesabınızı etkinleştirmek ve işletmenizi yönetmeye başlamak için lütfen aşağıdaki onay bağlantısına tıklayın:\n";
            $body .= $verify_url . "\n\n";
            $body .= "Bu onay bağlantısı 48 saat boyunca geçerlidir.\n\n";
            $body .= "Eğer bu hesabı siz oluşturmadıysanız, bu e-postayı dikkate almayabilirsiniz.\n\n";
            $body .= "Saygılarımızla,\nFindeWerkstatt.de Ekibi\n" . home_url( '/' );
            break;

        case 'en':
            $subject = '[FindeWerkstatt] Please verify your email address';
            $body  = "Hello " . $name . ",\n\n";
            $body .= "Thank you for registering at FindeWerkstatt.de.\n\n";
            $body .= "Please click the link below to verify your email address and activate your account:\n";
            $body .= $verify_url . "\n\n";
            $body .= "This verification link is valid for 48 hours.\n\n";
            $body .= "If you did not create this account, you can safely ignore this email.\n\n";
            $body .= "Best regards,\nYour FindeWerkstatt.de Team\n" . home_url( '/' );
            break;

        case 'ru':
            $subject = '[FindeWerkstatt] Подтвердите ваш адрес электронной почты';
            $body  = "Здравствуйте, " . $name . "!\n\n";
            $body .= "Спасибо за регистрацию на сайте FindeWerkstatt.de.\n\n";
            $body .= "Пожалуйста, перейдите по ссылке ниже, чтобы подтвердить ваш адрес электронной почты и активировать аккаунт:\n";
            $body .= $verify_url . "\n\n";
            $body .= "Ссылка действительна в течение 48 часов.\n\n";
            $body .= "Если вы не регистрировались на сайте, просто проигнорируйте это сообщение.\n\n";
            $body .= "С уважением,\nКоманда FindeWerkstatt.de\n" . home_url( '/' );
            break;

        case 'de':
        default:
            $subject = '[FindeWerkstatt] Bitte bestätigen Sie Ihre E-Mail-Adresse';
            $body  = "Hallo " . $name . ",\n\n";
            $body .= "vielen Dank für Ihre Registrierung bei FindeWerkstatt.de.\n\n";
            $body .= "Bitte klicken Sie auf den folgenden Link, um Ihre E-Mail-Adresse zu bestätigen und Ihr Konto zu aktivieren:\n";
            $body .= $verify_url . "\n\n";
            $body .= "Dieser Link ist 48 Stunden gültig.\n\n";
            $body .= "Falls Sie dieses Konto nicht erstellt haben, können Sie diese E-Mail einfach ignorieren.\n\n";
            $body .= "Mit freundlichen Grüßen,\nIhr FindeWerkstatt.de Team\n" . home_url( '/' );
            break;
    }

    $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
    return (bool) wp_mail( $user->user_email, $subject, $body, $headers );
}

function findewerkstatt_member_signup_submit() {
    $values = array(
        'name' => findewerkstatt_form_text( 'name', 160 ), 'email' => findewerkstatt_form_text( 'email', 254 ),
    );
    $password = findewerkstatt_member_password_field( 'password' );
    $confirmation = findewerkstatt_member_password_field( 'password_confirm' );
    if ( is_user_logged_in() ) { findewerkstatt_member_redirect( 'warning', findewerkstatt_t( 'Sie sind bereits angemeldet.' ), array(), array(), 'signup' ); }
    if ( ! findewerkstatt_member_valid_post( 'fw_member_signup' ) || '' !== findewerkstatt_form_text( 'company_website' ) ) {
        findewerkstatt_member_redirect( 'error', findewerkstatt_t( 'Bitte laden Sie die Seite neu und versuchen Sie es erneut.' ), $values, array(), 'signup' );
    }
    $errors = array();
    if ( '' === $values['name'] || findewerkstatt_form_length( $values['name'] ) > 160 ) { $errors[] = findewerkstatt_t( 'Bitte geben Sie Ihren Namen mit höchstens 160 Zeichen an.' ); }
    if ( ! is_email( $values['email'] ) || findewerkstatt_form_length( $values['email'] ) > 254 ) { $errors[] = findewerkstatt_t( 'Bitte geben Sie eine gültige E-Mail-Adresse an.' ); }
    if ( findewerkstatt_form_length( $password ) < 12 || findewerkstatt_form_length( $password ) > 128 ) { $errors[] = findewerkstatt_t( 'Bitte wählen Sie ein Passwort mit 12 bis 128 Zeichen.' ); }
    if ( $password !== $confirmation ) { $errors[] = findewerkstatt_t( 'Die beiden Passwörter stimmen nicht überein.' ); }
    if ( ! findewerkstatt_form_checked( 'privacy' ) || ! findewerkstatt_form_checked( 'terms' ) ) { $errors[] = findewerkstatt_t( 'Bitte bestätigen Sie die Datenschutzhinweise und die Nutzungsbedingungen.' ); }
    if ( $errors ) { findewerkstatt_member_redirect( 'error', findewerkstatt_t( 'Bitte prüfen Sie Ihre Angaben.' ), $values, $errors, 'signup' ); }
    $claim = findewerkstatt_form_claim( 'member_signup' );
    if ( is_wp_error( $claim ) ) { findewerkstatt_member_redirect( 'error', $claim->get_error_message(), $values, array(), 'signup' ); }
    if ( ! findewerkstatt_form_rate_allowed( 'member_signup', 5 ) ) {
        delete_option( $claim );
        findewerkstatt_member_redirect( 'error', findewerkstatt_t( 'Bitte versuchen Sie die Registrierung in einer Stunde erneut.' ), $values, array(), 'signup' );
    }
    if ( email_exists( $values['email'] ) ) {
        findewerkstatt_form_complete( $claim, 'member_signup' );
        findewerkstatt_member_redirect( 'error', findewerkstatt_t( 'Das Konto konnte nicht erstellt werden. Wenn Sie bereits ein Konto haben, melden Sie sich bitte an oder setzen Sie Ihr Passwort zurück.' ), $values, array(), 'signup' );
    }
    $user_id = wp_insert_user( array(
        'user_login' => 'fw_' . str_replace( '-', '', wp_generate_uuid4() ), 'user_pass' => $password,
        'user_email' => sanitize_email( $values['email'] ), 'display_name' => $values['name'],
        'nickname' => $values['name'], 'role' => 'fw_workshop_member',
    ) );
    unset( $password, $confirmation );
    if ( is_wp_error( $user_id ) || ! $user_id ) {
        delete_option( $claim );
        findewerkstatt_member_redirect( 'error', findewerkstatt_t( 'Das Konto konnte nicht erstellt werden. Bitte versuchen Sie es später erneut oder nutzen Sie die Anmeldung für ein vorhandenes Konto.' ), $values, array(), 'signup' );
    }
    update_user_meta( $user_id, '_fw_member_plan', 'free' );
    update_user_meta( $user_id, '_fw_member_terms_acknowledged_at', current_time( 'mysql', true ) );
    update_user_meta( $user_id, '_fw_member_privacy_acknowledged_at', current_time( 'mysql', true ) );
    update_user_meta( $user_id, '_fw_member_terms_version', wp_get_theme()->get( 'Version' ) );

    // Mandatory Email Verification
    update_user_meta( $user_id, '_fw_email_verified', 'no' );
    $lang = function_exists( 'findewerkstatt_language' ) ? findewerkstatt_language() : 'de';
    update_user_meta( $user_id, '_fw_member_language', $lang );
    findewerkstatt_member_send_verification_email( $user_id, $lang );

    findewerkstatt_form_complete( $claim, 'member_signup' );
    findewerkstatt_member_redirect( 'success', findewerkstatt_t( 'Ihr Konto wurde erstellt! Wir haben Ihnen eine Bestätigungs-E-Mail gesendet. Bitte klicken Sie auf den Link in der E-Mail, um Ihr Konto zu aktivieren.' ), array( 'email' => $values['email'] ), array(), 'signup_pending' );
}
add_action( 'admin_post_nopriv_fw_member_signup', 'findewerkstatt_member_signup_submit' );
add_action( 'admin_post_fw_member_signup', 'findewerkstatt_member_signup_submit' );

function findewerkstatt_member_login_submit() {
    $values = array( 'email' => findewerkstatt_form_text( 'email', 254 ), 'remember' => findewerkstatt_form_checked( 'remember' ) );
    if ( is_user_logged_in() ) { findewerkstatt_member_redirect( 'success', findewerkstatt_t( 'Sie sind bereits angemeldet.' ), array(), array(), 'login' ); }
    if ( ! findewerkstatt_member_valid_post( 'fw_member_login' ) ) { findewerkstatt_member_redirect( 'error', findewerkstatt_t( 'Bitte laden Sie die Seite neu und melden Sie sich erneut an.' ), $values, array(), 'login' ); }
    if ( ! findewerkstatt_form_rate_allowed( 'member_login', 15 ) ) { findewerkstatt_member_redirect( 'error', findewerkstatt_t( 'Zu viele Anmeldeversuche. Bitte versuchen Sie es in einer Stunde erneut.' ), $values, array(), 'login' ); }
    $password = findewerkstatt_member_password_field( 'password' );
    if ( ! is_email( $values['email'] ) || strlen( $values['email'] ) > 254 || '' === $password ) { findewerkstatt_member_redirect( 'error', findewerkstatt_t( 'Die Anmeldung ist fehlgeschlagen. Bitte prüfen Sie E-Mail-Adresse und Passwort.' ), $values, array(), 'login' ); }
    $user = wp_signon( array( 'user_login' => $values['email'], 'user_password' => $password, 'remember' => $values['remember'] ), is_ssl() );
    unset( $password );
    if ( is_wp_error( $user ) ) { findewerkstatt_member_redirect( 'error', findewerkstatt_t( 'Die Anmeldung ist fehlgeschlagen. Bitte prüfen Sie E-Mail-Adresse und Passwort.' ), $values, array(), 'login' ); }

    // Check mandatory email verification status (exempt administrators)
    if ( ! user_can( $user, 'manage_options' ) ) {
        if ( ! findewerkstatt_member_is_email_verified( $user->ID ) ) {
            wp_logout();
            findewerkstatt_member_redirect( 'warning', findewerkstatt_t( 'Ihre E-Mail-Adresse wurde noch nicht bestätigt. Bitte prüfen Sie Ihren Posteingang oder fordern Sie einen neuen Bestätigungslink an.' ), array( 'email' => $values['email'] ), array(), 'resend_verification' );
        }
    }

    wp_set_current_user( $user->ID );
    findewerkstatt_member_redirect( 'success', findewerkstatt_t( 'Sie sind jetzt angemeldet.' ), array(), array(), 'login' );
}
add_action( 'admin_post_nopriv_fw_member_login', 'findewerkstatt_member_login_submit' );
add_action( 'admin_post_fw_member_login', 'findewerkstatt_member_login_submit' );

/** Verify token when verification link is clicked. */
function findewerkstatt_member_handle_verification() {
    if ( empty( $_GET['fw_verify_email'] ) || ! is_string( $_GET['fw_verify_email'] ) ) { return; }
    $token = sanitize_text_field( wp_unslash( $_GET['fw_verify_email'] ) );
    if ( ! preg_match( '/^[a-zA-Z0-9]{48}$/', $token ) ) {
        findewerkstatt_member_redirect( 'error', findewerkstatt_t( 'Der Bestätigungslink ist ungültig oder abgelaufen.' ), array(), array(), 'login' );
    }

    $users = get_users( array(
        'meta_key'   => '_fw_email_verification_token',
        'meta_value' => $token,
        'number'     => 1,
    ) );

    if ( empty( $users ) ) {
        findewerkstatt_member_redirect( 'error', findewerkstatt_t( 'Der Bestätigungslink ist ungültig oder wurde bereits verwendet.' ), array(), array(), 'login' );
    }

    $user = $users[0];
    $expires = (int) get_user_meta( $user->ID, '_fw_email_verification_expires', true );
    if ( ! $expires || time() > $expires ) {
        findewerkstatt_member_redirect( 'error', findewerkstatt_t( 'Der Bestätigungslink ist abgelaufen. Bitte fordern Sie eine neue Bestätigungs-E-Mail an.' ), array( 'email' => $user->user_email ), array(), 'resend_verification' );
    }

    update_user_meta( $user->ID, '_fw_email_verified', 'yes' );
    update_user_meta( $user->ID, '_fw_email_verified_at', current_time( 'mysql', true ) );
    delete_user_meta( $user->ID, '_fw_email_verification_token' );
    delete_user_meta( $user->ID, '_fw_email_verification_expires' );

    wp_set_current_user( $user->ID );
    wp_set_auth_cookie( $user->ID, false, is_ssl() );
    do_action( 'wp_login', $user->user_login, $user );

    findewerkstatt_member_redirect( 'success', findewerkstatt_t( 'Ihre E-Mail-Adresse wurde erfolgreich bestätigt! Ihr Konto ist jetzt aktiviert.' ), array(), array(), 'account' );
}
add_action( 'template_redirect', 'findewerkstatt_member_handle_verification', 5 );

/** Resend verification email upon request. */
function findewerkstatt_member_resend_verification_submit() {
    $email = findewerkstatt_form_text( 'email', 254 );
    $values = array( 'email' => $email );
    if ( is_user_logged_in() ) { findewerkstatt_member_redirect( 'success', findewerkstatt_t( 'Sie sind bereits angemeldet.' ), array(), array(), 'login' ); }
    if ( ! findewerkstatt_member_valid_post( 'fw_member_resend_verification' ) ) {
        findewerkstatt_member_redirect( 'error', findewerkstatt_t( 'Bitte laden Sie die Seite neu und versuchen Sie es erneut.' ), $values, array(), 'login' );
    }
    if ( ! findewerkstatt_form_rate_allowed( 'resend_verification', 5 ) ) {
        findewerkstatt_member_redirect( 'error', findewerkstatt_t( 'Zu viele Anfragen. Bitte versuchen Sie es in einer Stunde erneut.' ), $values, array(), 'login' );
    }
    if ( ! is_email( $email ) ) {
        findewerkstatt_member_redirect( 'error', findewerkstatt_t( 'Bitte geben Sie eine gültige E-Mail-Adresse an.' ), $values, array(), 'login' );
    }

    $user = get_user_by( 'email', $email );
    if ( $user && 'no' === get_user_meta( $user->ID, '_fw_email_verified', true ) ) {
        $lang = function_exists( 'findewerkstatt_language' ) ? findewerkstatt_language() : 'de';
        findewerkstatt_member_send_verification_email( $user->ID, $lang );
    }

    findewerkstatt_member_redirect( 'success', findewerkstatt_t( 'Falls ein unbestätigtes Konto mit dieser E-Mail-Adresse existiert, haben wir Ihnen einen neuen Bestätigungslink gesendet.' ), $values, array(), 'login' );
}
add_action( 'admin_post_nopriv_fw_member_resend_verification', 'findewerkstatt_member_resend_verification_submit' );
add_action( 'admin_post_fw_member_resend_verification', 'findewerkstatt_member_resend_verification_submit' );

function findewerkstatt_member_logout_submit() {
    if ( ! findewerkstatt_member_valid_post( 'fw_member_logout' ) || ! is_user_logged_in() ) { findewerkstatt_member_redirect( 'error', findewerkstatt_t( 'Bitte laden Sie die Seite neu und versuchen Sie es erneut.' ), array(), array(), 'logout' ); }
    wp_logout();
    findewerkstatt_member_redirect( 'success', findewerkstatt_t( 'Sie wurden abgemeldet.' ), array(), array(), 'logout' );
}
add_action( 'admin_post_fw_member_logout', 'findewerkstatt_member_logout_submit' );
add_action( 'admin_post_nopriv_fw_member_logout', 'findewerkstatt_member_logout_submit' );

/** Email/password changes use WordPress's authenticated profile and reset verification. */
function findewerkstatt_member_account_submit() {
    $name = findewerkstatt_form_text( 'name', 160 );
    if ( ! is_user_logged_in() || ! findewerkstatt_member_valid_post( 'fw_member_account' ) ) { findewerkstatt_member_redirect( 'error', findewerkstatt_t( 'Bitte laden Sie Ihr Konto neu und versuchen Sie es erneut.' ), array(), array(), 'account' ); }
    if ( '' === $name || findewerkstatt_form_length( $name ) > 160 ) { findewerkstatt_member_redirect( 'error', findewerkstatt_t( 'Bitte geben Sie Ihren Namen mit höchstens 160 Zeichen an.' ), array( 'name' => $name ), array(), 'account' ); }
    $updated = wp_update_user( array( 'ID' => get_current_user_id(), 'display_name' => $name, 'nickname' => $name ) );
    findewerkstatt_member_redirect( is_wp_error( $updated ) ? 'error' : 'success', is_wp_error( $updated ) ? findewerkstatt_t( 'Ihre Angaben konnten gerade nicht gespeichert werden.' ) : findewerkstatt_t( 'Ihre Kontodaten wurden gespeichert.' ), array(), array(), 'account' );
}
add_action( 'admin_post_fw_member_account', 'findewerkstatt_member_account_submit' );
add_action( 'admin_post_nopriv_fw_member_account', 'findewerkstatt_member_account_submit' );

function findewerkstatt_member_plan_request_submit() {
    if ( ! findewerkstatt_is_workshop_member() || ! findewerkstatt_member_valid_post( 'fw_member_plan_request' ) ) { findewerkstatt_member_redirect( 'error', findewerkstatt_t( 'Bitte melden Sie sich mit Ihrem Werkstattkonto an und laden Sie Ihr Konto neu.' ), array(), array(), 'plan' ); }
    $target = findewerkstatt_form_text( 'selected_plan', 30 );
    if ( ! isset( findewerkstatt_membership_plans()[ $target ] ) ) { findewerkstatt_member_redirect( 'error', findewerkstatt_t( 'Bitte wählen Sie ein verfügbares Paket aus.' ), array(), array(), 'plan' ); }
    $claim = findewerkstatt_form_claim( 'member_plan_request' );
    if ( is_wp_error( $claim ) ) {
        $existing = findewerkstatt_member_pending_request();
        if ( 'duplicate' === $claim->get_error_code() && 'pending' === ( $existing['status'] ?? '' ) && $target === $existing['plan'] ) {
            findewerkstatt_member_redirect( 'success', findewerkstatt_t( 'Ihre Paketanfrage ist bereits eingegangen. Wir prüfen sie vor der Freigabe.' ), array(), array(), 'plan' );
        }
        findewerkstatt_member_redirect( 'error', $claim->get_error_message(), array(), array(), 'plan' );
    }
    if ( ! findewerkstatt_form_rate_allowed( 'member_plan_request', 10 ) ) {
        delete_option( $claim );
        findewerkstatt_member_redirect( 'error', findewerkstatt_t( 'Bitte versuchen Sie es in einer Stunde erneut.' ), array(), array(), 'plan' );
    }
    $request = findewerkstatt_member_plan_request( $target );
    if ( is_wp_error( $request ) ) {
        delete_option( $claim );
        findewerkstatt_member_redirect( 'error', $request->get_error_message(), array(), array(), 'plan' );
    }
    findewerkstatt_form_complete( $claim, 'member_plan_request' );
    findewerkstatt_member_redirect( 'success', findewerkstatt_t( 'Ihre Paketanfrage ist eingegangen. Ihr bisheriges Paket bleibt bis zur Freigabe aktiv. Es wurde keine Zahlung ausgelöst.' ), array(), array(), 'plan' );
}
add_action( 'admin_post_fw_member_plan_request', 'findewerkstatt_member_plan_request_submit' );
add_action( 'admin_post_nopriv_fw_member_plan_request', 'findewerkstatt_member_plan_request_submit' );

/** Business accounts use their frontend; core profile/reset and processing endpoints remain available. */
add_action( 'admin_init', function () {
    global $pagenow;
    if ( ! findewerkstatt_is_workshop_member() || current_user_can( 'manage_options' ) || wp_doing_ajax() || in_array( $pagenow, array( 'admin-post.php', 'admin-ajax.php', 'profile.php' ), true ) ) { return; }
    wp_safe_redirect( findewerkstatt_page_url( 'mein-konto' ), 302 );
    exit;
} );
add_filter( 'show_admin_bar', function ( $show ) { return findewerkstatt_is_workshop_member() && ! current_user_can( 'manage_options' ) ? false : $show; } );

/** Account pages contain credentials, personal data and per-visitor nonces; never cache them. */
add_filter( 'nonce_user_logged_out', function ( $uid, $action ) {
    if ( ! in_array( $action, array( 'fw_member_signup', 'fw_member_login' ), true ) ) { return $uid; }
    $guest = $_COOKIE['fw_member_guest'] ?? '';
    if ( ! is_string( $guest ) || ! preg_match( '/^[a-f0-9]{64}$/', $guest ) ) { return -1; }
    return hexdec( substr( hash_hmac( 'sha256', $guest, wp_salt( 'nonce' ) ), 0, 12 ) ) + 1;
}, 10, 2 );
add_action( 'template_redirect', function () {
    if ( is_page( 'mein-konto' ) || is_page_template( 'page-mein-konto.php' ) ) {
        if ( ! defined( 'DONOTCACHEPAGE' ) ) { define( 'DONOTCACHEPAGE', true ); }
        nocache_headers();
        header( 'Referrer-Policy: same-origin' );
        if ( ! is_user_logged_in() && 'GET' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
            $guest = $_COOKIE['fw_member_guest'] ?? '';
            if ( ! is_string( $guest ) || ! preg_match( '/^[a-f0-9]{64}$/', $guest ) ) {
                $guest = bin2hex( random_bytes( 32 ) );
                setcookie( 'fw_member_guest', $guest, array( 'expires' => time() + 30 * MINUTE_IN_SECONDS, 'path' => COOKIEPATH ?: '/', 'secure' => is_ssl(), 'httponly' => true, 'samesite' => 'Lax' ) );
                $_COOKIE['fw_member_guest'] = $guest;
            }
        }
    }
}, 0 );

add_filter( 'wp_robots', function ( $robots ) {
    if ( is_page( 'mein-konto' ) || is_page_template( 'page-mein-konto.php' ) ) { $robots['noindex'] = true; $robots['nofollow'] = true; unset( $robots['index'], $robots['follow'] ); }
    return $robots;
} );
add_filter( 'wp_sitemaps_posts_query_args', function ( $args, $post_type ) {
    if ( 'page' === $post_type ) {
        $account = get_page_by_path( 'mein-konto' );
        if ( $account ) { $args['post__not_in'] = array_unique( array_merge( $args['post__not_in'] ?? array(), array( $account->ID ) ) ); }
    }
    return $args;
}, 10, 2 );
