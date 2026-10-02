<?php
/** Moderated ownership and profile changes. Imported records never grant account access. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Keep private member names out of public author archives and the posts REST API. */
function findewerkstatt_membership_operator_id() {
    $ids = get_users( array( 'role' => 'administrator', 'number' => 1, 'orderby' => 'ID', 'order' => 'ASC', 'fields' => 'ID' ) );
    return $ids ? (int) $ids[0] : 0;
}

add_action( 'init', function () {
    register_post_type( 'fw_member_request', array(
        'labels' => array( 'name' => findewerkstatt_t( 'Eintragsanfragen' ), 'singular_name' => findewerkstatt_t( 'Eintragsanfrage' ), 'edit_item' => findewerkstatt_t( 'Eintragsanfrage prüfen' ), 'all_items' => findewerkstatt_t( 'Eintragsanfragen' ) ),
        'public' => false, 'publicly_queryable' => false, 'show_ui' => true, 'show_in_rest' => false,
        'exclude_from_search' => true, 'supports' => false, 'menu_icon' => 'dashicons-shield',
        'capabilities' => array_fill_keys( array( 'edit_post', 'read_post', 'delete_post', 'edit_posts', 'edit_others_posts', 'publish_posts', 'read_private_posts', 'delete_posts', 'delete_private_posts', 'delete_published_posts', 'delete_others_posts', 'edit_private_posts', 'edit_published_posts' ), 'manage_options' ) + array( 'create_posts' => 'do_not_allow' ),
        'map_meta_cap' => false,
    ) );
} );

function findewerkstatt_member_create_workshop_request( $workshop_id, $kind, $payload ) {
    $user_id = get_current_user_id();
    if ( ! findewerkstatt_is_workshop_member() || ! in_array( $kind, array( 'claim', 'change' ), true ) ) { return new WP_Error( 'permission', findewerkstatt_t( 'Bitte melden Sie sich mit Ihrem Werkstattkonto an.' ) ); }
    $post = get_post( absint( $workshop_id ) );
    if ( ! $post || 'mechanic' !== $post->post_type || 'trash' === $post->post_status ) { return new WP_Error( 'workshop', findewerkstatt_t( 'Dieser Betriebseintrag ist nicht verfügbar.' ) ); }
    if ( 'claim' === $kind && ( 'publish' !== $post->post_status || get_post_meta( $post->ID, '_fw_owner_user_id', true ) ) ) { return new WP_Error( 'claim', findewerkstatt_t( 'Dieser Eintrag kann nicht zugeordnet werden. Bitte kontaktieren Sie uns bei Rückfragen.' ) ); }
    if ( 'change' === $kind && ! findewerkstatt_member_owns_workshop( $post->ID ) ) { return new WP_Error( 'permission', findewerkstatt_t( 'Dieser Eintrag ist Ihrem Konto nicht zugeordnet.' ) ); }
    $lock = findewerkstatt_member_acquire_lock( $user_id, 'account' );
    if ( is_wp_error( $lock ) ) { return $lock; }
    try {
        $existing = get_posts( array( 'post_type' => 'fw_member_request', 'post_status' => 'private', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_query' => array(
            array( 'key' => '_fw_request_user', 'value' => $user_id, 'type' => 'NUMERIC' ),
            array( 'key' => '_fw_request_workshop', 'value' => $post->ID, 'type' => 'NUMERIC' ),
            array( 'key' => '_fw_request_kind', 'value' => $kind ), array( 'key' => '_fw_request_state', 'value' => 'pending' ),
        ) ) );
        if ( $existing ) { return (int) $existing[0]; }
        if ( 'change' === $kind ) {
            $payload = findewerkstatt_member_validate_workshop_change( $payload );
            if ( is_wp_error( $payload ) ) { return $payload; }
        } else {
            $evidence = is_string( $payload['evidence'] ?? null ) ? sanitize_textarea_field( $payload['evidence'] ) : '';
            if ( findewerkstatt_form_length( $evidence ) < 20 || findewerkstatt_form_length( $evidence ) > 2000 ) { return new WP_Error( 'evidence', findewerkstatt_t( 'Bitte erläutern Sie mit 20 bis 2.000 Zeichen, wie Sie Ihre Berechtigung nachweisen können.' ) ); }
            $payload = array( 'evidence' => $evidence );
        }
        $id = wp_insert_post( array( 'post_type' => 'fw_member_request', 'post_status' => 'private', 'post_author' => findewerkstatt_membership_operator_id(), 'post_title' => ( 'claim' === $kind ? findewerkstatt_t( 'Zuordnung: ' ) : findewerkstatt_t( 'Änderung: ' ) ) . $post->post_title ), true );
        if ( is_wp_error( $id ) || ! $id ) { return new WP_Error( 'save', findewerkstatt_t( 'Ihre Anfrage konnte gerade nicht gespeichert werden.' ) ); }
        foreach ( array( '_fw_request_user' => $user_id, '_fw_request_workshop' => $post->ID, '_fw_request_kind' => $kind, '_fw_request_state' => 'pending', '_fw_request_payload' => $payload, '_fw_request_uuid' => wp_generate_uuid4() ) as $key => $value ) { update_post_meta( $id, $key, wp_slash( $value ) ); }
        return $id;
    } finally { findewerkstatt_member_release_lock( $lock ); }
}

/** Accept only business fields; status, ownership, ratings and verification are never client-controlled. */
function findewerkstatt_member_validate_workshop_change( $input ) {
    if ( ! is_array( $input ) ) { return new WP_Error( 'fields', findewerkstatt_t( 'Bitte prüfen Sie Ihre Betriebsangaben.' ) ); }
    $fields = array( 'company_name' => 160, 'description' => 5000, 'phone' => 60, 'whatsapp' => 60, 'email' => 254, 'website' => 250, 'street' => 200, 'plz' => 5 );
    $values = array();
    foreach ( $fields as $key => $limit ) {
        if ( ! is_string( $input[ $key ] ?? null ) || findewerkstatt_form_length( $input[ $key ] ) > $limit ) { return new WP_Error( 'fields', findewerkstatt_t( 'Bitte prüfen Sie die Länge Ihrer Betriebsangaben.' ) ); }
        $values[ $key ] = 'description' === $key ? sanitize_textarea_field( $input[ $key ] ) : sanitize_text_field( $input[ $key ] );
    }
    if ( '' === $values['company_name'] ) { return new WP_Error( 'name', findewerkstatt_t( 'Bitte geben Sie den Namen Ihres Betriebs an.' ) ); }
    $phone = findewerkstatt_normalize_phone( $values['phone'] );
    $whatsapp = '' !== $values['whatsapp'] ? findewerkstatt_normalize_phone( $values['whatsapp'] ) : '';
    if ( ! $phone || ( '' !== $values['whatsapp'] && ! $whatsapp ) ) { return new WP_Error( 'phone', findewerkstatt_t( 'Bitte prüfen Sie die Telefonnummern.' ) ); }
    if ( '' !== $values['email'] && ! is_email( $values['email'] ) ) { return new WP_Error( 'email', findewerkstatt_t( 'Bitte geben Sie eine gültige betriebliche E-Mail-Adresse an oder lassen Sie das Feld frei.' ) ); }
    if ( '' !== $values['plz'] && ! preg_match( '/^\d{5}$/', $values['plz'] ) ) { return new WP_Error( 'plz', findewerkstatt_t( 'Eine deutsche Postleitzahl besteht aus fünf Ziffern.' ) ); }
    $website = '' !== $values['website'] ? findewerkstatt_get_website_url( $values['website'] ) : '';
    if ( '' !== $values['website'] && ! $website ) { return new WP_Error( 'website', findewerkstatt_t( 'Bitte prüfen Sie die Website-Adresse.' ) ); }
    $spoken_languages = findewerkstatt_validate_workshop_languages( $input['spoken_languages'] ?? array() );
    if ( is_wp_error( $spoken_languages ) ) { return $spoken_languages; }
    return array( 'title' => $values['company_name'], 'content' => $values['description'], 'meta' => array(
        '_mechanic_phone' => $phone, '_mechanic_whatsapp' => $whatsapp, '_mechanic_email' => sanitize_email( $values['email'] ),
        '_mechanic_email_public' => ! empty( $input['public_email'] ) && '' !== $values['email'] ? 'yes' : 'no',
        '_mechanic_website' => $website, '_mechanic_address' => $values['street'], '_mechanic_plz' => $values['plz'],
        '_mechanic_languages' => $spoken_languages,
    ) );
}

/** Only an eligible public claim or the current owner's profile may retain submitted notice values. */
function findewerkstatt_member_workshop_notice_values( $kind, $input ) {
    if ( ! findewerkstatt_is_workshop_member() || ! is_array( $input ) || ! in_array( $kind, array( 'workshop_claim', 'workshop_change' ), true ) ) { return array(); }
    $id = is_scalar( $input['workshop_id'] ?? null ) ? absint( $input['workshop_id'] ) : 0;
    $post = $id ? get_post( $id ) : null;
    if ( ! $post || 'mechanic' !== $post->post_type || 'trash' === $post->post_status ) { return array(); }
    if ( 'workshop_claim' === $kind && ( 'publish' !== $post->post_status || get_post_meta( $id, '_fw_owner_user_id', true ) ) ) { return array(); }
    if ( 'workshop_change' === $kind && ! findewerkstatt_member_owns_workshop( $id ) ) { return array(); }
    $safe = array( 'workshop_id' => $id );
    $fields = 'workshop_claim' === $kind ? array( 'evidence' => 2000 ) : array( 'company_name' => 160, 'description' => 5000, 'phone' => 60, 'whatsapp' => 60, 'email' => 254, 'website' => 250, 'street' => 200, 'plz' => 5 );
    foreach ( $fields as $key => $limit ) {
        if ( ! is_string( $input[ $key ] ?? null ) ) { continue; }
        $value = in_array( $key, array( 'description', 'evidence' ), true ) ? sanitize_textarea_field( $input[ $key ] ) : sanitize_text_field( $input[ $key ] );
        $safe[ $key ] = function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $limit ) : substr( $value, 0, $limit );
    }
    if ( 'workshop_change' === $kind && isset( $input['public_email'] ) ) { $safe['public_email'] = true === $input['public_email']; }
    if ( 'workshop_change' === $kind && isset( $input['spoken_languages'] ) ) {
        $languages = findewerkstatt_validate_workshop_languages( $input['spoken_languages'] );
        if ( ! is_wp_error( $languages ) ) { $safe['spoken_languages'] = $languages; }
    }
    return $safe;
}

function findewerkstatt_member_workshop_submit() {
    $kind = findewerkstatt_form_text( 'request_kind', 20 );
    $action = 'fw_member_workshop_' . $kind;
    if ( ! in_array( $kind, array( 'claim', 'change' ), true ) || ! findewerkstatt_is_workshop_member() || ! findewerkstatt_member_valid_post( $action ) ) { findewerkstatt_member_redirect( 'error', findewerkstatt_t( 'Bitte melden Sie sich an und laden Sie Ihr Konto neu.' ) ); }
    $workshop_id = absint( findewerkstatt_form_text( 'workshop_id', 12 ) );
    $payload = array();
    if ( 'claim' === $kind ) { $payload['evidence'] = findewerkstatt_form_text( 'evidence', 2000, true ); }
    else {
        foreach ( array( 'company_name' => 160, 'description' => 5000, 'phone' => 60, 'whatsapp' => 60, 'email' => 254, 'website' => 250, 'street' => 200, 'plz' => 5 ) as $key => $limit ) { $payload[ $key ] = findewerkstatt_form_text( $key, $limit, 'description' === $key ); }
        $payload['public_email'] = findewerkstatt_form_checked( 'public_email' );
        $payload['spoken_languages'] = isset( $_POST['spoken_languages'] ) ? wp_unslash( $_POST['spoken_languages'] ) : array();
    }
    $notice_values = array( 'workshop_id' => $workshop_id ) + $payload;
    $notice_kind = 'workshop_' . $kind;
    $claim = findewerkstatt_form_claim( 'member_workshop_' . $kind );
    if ( is_wp_error( $claim ) ) { findewerkstatt_member_redirect( 'error', $claim->get_error_message(), $notice_values, array(), $notice_kind ); }
    if ( ! findewerkstatt_form_rate_allowed( 'member_workshop', 10 ) ) { delete_option( $claim ); findewerkstatt_member_redirect( 'error', findewerkstatt_t( 'Bitte versuchen Sie es in einer Stunde erneut.' ), $notice_values, array(), $notice_kind ); }
    $result = findewerkstatt_member_create_workshop_request( $workshop_id, $kind, $payload );
    if ( is_wp_error( $result ) ) { delete_option( $claim ); findewerkstatt_member_redirect( 'error', $result->get_error_message(), $notice_values, array(), $notice_kind ); }
    findewerkstatt_form_complete( $claim, 'member_workshop_' . $kind );
    findewerkstatt_member_redirect( 'success', findewerkstatt_t( 'Ihre Anfrage ist eingegangen. Wir prüfen Ihre Berechtigung und die Angaben vor der Freigabe. Den Status sehen Sie unter „Meine Anfragen“.' ), array(), array(), 'workshop_request' );
}
add_action( 'admin_post_fw_member_workshop', 'findewerkstatt_member_workshop_submit' );
add_action( 'admin_post_nopriv_fw_member_workshop', 'findewerkstatt_member_workshop_submit' );

/** Members can pause their own public profiles; returning a profile always needs operator review. */
function findewerkstatt_member_set_workshop_visibility( $post_id, $operation ) {
    $user_id = get_current_user_id();
    $post_id = is_scalar( $post_id ) ? absint( $post_id ) : 0;
    if ( ! findewerkstatt_is_workshop_member() || ! in_array( $operation, array( 'pause', 'submit' ), true ) || ! findewerkstatt_member_owns_workshop( $post_id ) ) {
        return new WP_Error( 'permission', findewerkstatt_t( 'Dieser Eintrag ist Ihrem Werkstattkonto nicht zugeordnet.' ) );
    }
    // Use the same lock order as ownership approval: first the profile, then the account quota.
    $lock = findewerkstatt_member_lock_key( 'fw_member_lock_review_' . $post_id );
    if ( is_wp_error( $lock ) ) { return $lock; }
    $quota_lock = null;
    try {
        $quota_lock = findewerkstatt_member_acquire_lock( $user_id, 'workshop' );
        if ( is_wp_error( $quota_lock ) ) { return $quota_lock; }
        wp_cache_delete( $post_id, 'posts' ); wp_cache_delete( $post_id, 'post_meta' ); wp_cache_delete( $user_id, 'user_meta' );
        if ( ! findewerkstatt_is_workshop_member() || ! findewerkstatt_member_owns_workshop( $post_id ) ) {
            return new WP_Error( 'permission', findewerkstatt_t( 'Dieser Eintrag ist Ihrem Werkstattkonto nicht mehr zugeordnet.' ) );
        }
        $post = get_post( $post_id );
        $paused = get_post_meta( $post_id, '_fw_member_paused', true );
        if ( 'pause' === $operation && 'publish' !== $post->post_status ) {
            return new WP_Error( 'status', findewerkstatt_t( 'Nur veröffentlichte Einträge können pausiert werden. Bitte laden Sie Ihr Konto neu.' ) );
        }
        if ( 'submit' === $operation && ( 'draft' !== $post->post_status || 'yes' !== $paused ) ) {
            return new WP_Error( 'status', findewerkstatt_t( 'Dieser Eintrag wurde nicht über Ihr Konto pausiert und kann hier nicht erneut eingereicht werden.' ) );
        }
        if ( 'pause' === $operation ) {
            update_post_meta( $post_id, '_fw_member_paused', 'yes' );
            if ( 'yes' !== get_post_meta( $post_id, '_fw_member_paused', true ) ) { return new WP_Error( 'save', findewerkstatt_t( 'Der Eintrag konnte gerade nicht pausiert werden.' ) ); }
        }
        $status = 'pause' === $operation ? 'draft' : 'pending';
        $updated = wp_update_post( array( 'ID' => $post_id, 'post_status' => $status ), true );
        if ( is_wp_error( $updated ) || ! $updated || $status !== get_post_status( $post_id ) ) {
            if ( 'pause' === $operation ) {
                if ( '' === $paused ) { delete_post_meta( $post_id, '_fw_member_paused' ); }
                else { update_post_meta( $post_id, '_fw_member_paused', wp_slash( $paused ) ); }
            }
            return new WP_Error( 'save', findewerkstatt_t( 'Der Veröffentlichungsstatus konnte gerade nicht geändert werden.' ) );
        }
        if ( 'submit' === $operation ) { delete_post_meta( $post_id, '_fw_member_paused' ); }
        return true;
    } finally {
        if ( $quota_lock && ! is_wp_error( $quota_lock ) ) { findewerkstatt_member_release_lock( $quota_lock ); }
        findewerkstatt_member_release_lock( $lock );
    }
}

function findewerkstatt_member_workshop_visibility_submit() {
    $post_id = absint( findewerkstatt_form_text( 'workshop_id', 12 ) );
    if ( ! findewerkstatt_is_workshop_member() || ! findewerkstatt_member_valid_post( 'fw_member_visibility_' . $post_id ) ) {
        findewerkstatt_member_redirect( 'error', findewerkstatt_t( 'Bitte melden Sie sich an und laden Sie Ihr Konto neu.' ), array(), array(), 'visibility' );
    }
    $operation = findewerkstatt_form_text( 'operation', 10 );
    $result = findewerkstatt_member_set_workshop_visibility( $post_id, $operation );
    if ( is_wp_error( $result ) ) { findewerkstatt_member_redirect( 'error', $result->get_error_message(), array(), array(), 'visibility' ); }
    $message = 'pause' === $operation
        ? findewerkstatt_t( 'Ihr Eintrag ist pausiert und wird nicht mehr öffentlich angezeigt. Er bleibt Ihrem Konto zugeordnet und zählt weiterhin zu Ihrer Paketgrenze.' )
        : findewerkstatt_t( 'Ihr Eintrag wurde erneut zur Prüfung eingereicht. Nach der Freigabe durch die Verwaltung wird er wieder öffentlich angezeigt.' );
    findewerkstatt_member_redirect( 'success', $message, array(), array(), 'visibility' );
}
add_action( 'admin_post_fw_member_workshop_visibility', 'findewerkstatt_member_workshop_visibility_submit' );
add_action( 'admin_post_nopriv_fw_member_workshop_visibility', 'findewerkstatt_member_workshop_visibility_submit' );

/** Administrator-only, serialized across both competing claims and the member's quota. */
function findewerkstatt_member_review_workshop( $request_id, $uuid, $decision ) {
    if ( ! current_user_can( 'manage_options' ) || ! in_array( $decision, array( 'approve', 'reject' ), true ) || ! is_string( $uuid ) ) { return new WP_Error( 'permission', findewerkstatt_t( 'Sie dürfen diese Anfrage nicht bearbeiten.' ) ); }
    $request = get_post( absint( $request_id ) );
    if ( ! $request || 'fw_member_request' !== $request->post_type || 'private' !== $request->post_status ) { return new WP_Error( 'request', findewerkstatt_t( 'Die Anfrage ist nicht verfügbar.' ) ); }
    $workshop_id = (int) get_post_meta( $request->ID, '_fw_request_workshop', true );
    $user_id = (int) get_post_meta( $request->ID, '_fw_request_user', true );
    $lock = findewerkstatt_member_lock_key( 'fw_member_lock_review_' . $workshop_id );
    if ( is_wp_error( $lock ) ) { return $lock; }
    $quota_lock = null;
    try {
        wp_cache_delete( $request->ID, 'post_meta' ); wp_cache_delete( $workshop_id, 'post_meta' ); wp_cache_delete( $user_id, 'user_meta' );
        if ( 'pending' !== get_post_meta( $request->ID, '_fw_request_state', true ) || ! hash_equals( (string) get_post_meta( $request->ID, '_fw_request_uuid', true ), $uuid ) ) { return new WP_Error( 'stale', findewerkstatt_t( 'Diese Anfrage wurde bereits bearbeitet. Bitte laden Sie die Seite neu.' ) ); }
        if ( 'approve' === $decision ) {
            if ( ! findewerkstatt_is_workshop_member( $user_id ) ) { return new WP_Error( 'user', findewerkstatt_t( 'Das Werkstattkonto ist nicht mehr verfügbar.' ) ); }
            $workshop = get_post( $workshop_id );
            if ( ! $workshop || 'mechanic' !== $workshop->post_type || 'trash' === $workshop->post_status ) { return new WP_Error( 'workshop', findewerkstatt_t( 'Der Betriebseintrag ist nicht verfügbar.' ) ); }
            $kind = get_post_meta( $request->ID, '_fw_request_kind', true );
            if ( 'claim' === $kind ) {
                $quota_lock = findewerkstatt_member_acquire_lock( $user_id, 'workshop' );
                if ( is_wp_error( $quota_lock ) ) { return $quota_lock; }
                $owner = (int) get_post_meta( $workshop_id, '_fw_owner_user_id', true );
                if ( 'publish' !== $workshop->post_status || ( $owner && $owner !== $user_id ) ) { return new WP_Error( 'owner', findewerkstatt_t( 'Dieser Eintrag ist nicht mehr verfügbar oder bereits einem anderen Konto zugeordnet.' ) ); }
                if ( ! $owner && ! findewerkstatt_member_can_add_workshop( $user_id ) ) { return new WP_Error( 'quota', findewerkstatt_t( 'Die Paketgrenze des Kontos ist erreicht. Prüfen Sie zuerst den Paketwechsel.' ) ); }
                update_post_meta( $workshop_id, '_fw_owner_user_id', $user_id );
                if ( $user_id !== (int) get_post_meta( $workshop_id, '_fw_owner_user_id', true ) ) { return new WP_Error( 'save', findewerkstatt_t( 'Die Zuordnung konnte nicht gespeichert werden.' ) ); }
            } elseif ( 'change' === $kind ) {
                if ( ! findewerkstatt_member_owns_workshop( $workshop_id, $user_id ) ) { return new WP_Error( 'owner', findewerkstatt_t( 'Der Eintrag ist diesem Konto nicht mehr zugeordnet.' ) ); }
                $saved = get_post_meta( $request->ID, '_fw_request_payload', true );
                $meta = is_array( $saved['meta'] ?? null ) ? $saved['meta'] : array();
                // Revalidate the exact allowlist when reviewing persisted data as well.
                $payload = findewerkstatt_member_validate_workshop_change( array(
                    'company_name' => $saved['title'] ?? '', 'description' => $saved['content'] ?? '', 'phone' => $meta['_mechanic_phone'] ?? '',
                    'whatsapp' => $meta['_mechanic_whatsapp'] ?? '', 'email' => $meta['_mechanic_email'] ?? '', 'website' => $meta['_mechanic_website'] ?? '',
                    'street' => $meta['_mechanic_address'] ?? '', 'plz' => $meta['_mechanic_plz'] ?? '', 'public_email' => 'yes' === ( $meta['_mechanic_email_public'] ?? '' ),
                    'spoken_languages' => $meta['_mechanic_languages'] ?? get_post_meta( $workshop_id, '_mechanic_languages', true ) ?: array(),
                ) );
                if ( is_wp_error( $payload ) ) { return $payload; }
                $before = array( 'title' => $workshop->post_title, 'content' => $workshop->post_content, 'meta' => array() );
                foreach ( $payload['meta'] as $key => $value ) { $before['meta'][ $key ] = get_post_meta( $workshop_id, $key, true ); }
                update_post_meta( $request->ID, '_fw_request_before', wp_slash( $before ) );
                $updated = wp_update_post( wp_slash( array( 'ID' => $workshop_id, 'post_title' => $payload['title'], 'post_content' => $payload['content'] ) ), true );
                if ( is_wp_error( $updated ) || ! $updated ) { return new WP_Error( 'save', findewerkstatt_t( 'Die Änderung konnte nicht gespeichert werden.' ) ); }
                foreach ( $payload['meta'] as $key => $value ) { update_post_meta( $workshop_id, $key, wp_slash( $value ) ); }
                foreach ( $payload['meta'] as $key => $value ) {
                    if ( $value !== get_post_meta( $workshop_id, $key, true ) ) {
                        wp_update_post( wp_slash( array( 'ID' => $workshop_id, 'post_title' => $before['title'], 'post_content' => $before['content'] ) ) );
                        foreach ( $before['meta'] as $old_key => $old_value ) { update_post_meta( $workshop_id, $old_key, wp_slash( $old_value ) ); }
                        return new WP_Error( 'save', findewerkstatt_t( 'Die Änderung konnte nicht vollständig gespeichert werden. Bitte versuchen Sie es erneut.' ) );
                    }
                }
            } else { return new WP_Error( 'kind', findewerkstatt_t( 'Ungültige Anfrage.' ) ); }
        }
        update_post_meta( $request->ID, '_fw_request_state', 'approve' === $decision ? 'approved' : 'rejected' );
        update_post_meta( $request->ID, '_fw_request_reviewed_at', current_time( 'mysql', true ) );
        update_post_meta( $request->ID, '_fw_request_reviewed_by', get_current_user_id() );
        return true;
    } finally {
        if ( $quota_lock && ! is_wp_error( $quota_lock ) ) { findewerkstatt_member_release_lock( $quota_lock ); }
        findewerkstatt_member_release_lock( $lock );
    }
}

add_filter( 'manage_fw_member_request_posts_columns', function ( $columns ) {
    $columns['fw_review_state'] = findewerkstatt_t( 'Prüfstatus' );
    $columns['fw_member_account'] = findewerkstatt_t( 'Werkstattkonto' );
    return $columns;
} );
add_action( 'manage_fw_member_request_posts_custom_column', function ( $column, $post_id ) {
    if ( ! current_user_can( 'manage_options' ) ) { return; }
    if ( 'fw_review_state' === $column ) {
        $labels = array( 'pending' => findewerkstatt_t( 'In Prüfung' ), 'approved' => findewerkstatt_t( 'Freigegeben' ), 'rejected' => findewerkstatt_t( 'Abgelehnt' ) );
        echo esc_html( $labels[ get_post_meta( $post_id, '_fw_request_state', true ) ] ?? findewerkstatt_t( 'In Prüfung' ) );
    } elseif ( 'fw_member_account' === $column ) {
        $user = get_userdata( (int) get_post_meta( $post_id, '_fw_request_user', true ) );
        echo esc_html( $user ? $user->display_name : findewerkstatt_t( 'Konto nicht verfügbar' ) );
    }
}, 10, 2 );

add_action( 'admin_post_fw_member_review_workshop', function () {
    if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! current_user_can( 'manage_options' ) ) { wp_die( findewerkstatt_t( 'Nicht erlaubt.' ), '', array( 'response' => 403 ) ); }
    $id = absint( findewerkstatt_form_text( 'request_id', 12 ) );
    $uuid = findewerkstatt_form_text( 'request_uuid', 40 );
    check_admin_referer( 'fw_review_workshop_' . $id . '_' . $uuid );
    $result = findewerkstatt_member_review_workshop( $id, $uuid, findewerkstatt_form_text( 'decision', 10 ) );
    if ( is_wp_error( $result ) ) { wp_die( esc_html( $result->get_error_message() ), findewerkstatt_t( 'Anfrage konnte nicht freigegeben werden' ), array( 'back_link' => true, 'response' => 409 ) ); }
    wp_safe_redirect( get_edit_post_link( $id, 'url' ), 303 ); exit;
} );

add_action( 'add_meta_boxes_fw_member_request', function () {
    add_meta_box( 'fw-member-review', findewerkstatt_t( 'Berechtigung und Betriebsangaben prüfen' ), 'findewerkstatt_member_request_box', 'fw_member_request', 'normal', 'high' );
} );

function findewerkstatt_member_request_box( $post ) {
    if ( ! current_user_can( 'manage_options' ) ) { return; }
    $user = get_userdata( (int) get_post_meta( $post->ID, '_fw_request_user', true ) );
    $workshop = get_post( (int) get_post_meta( $post->ID, '_fw_request_workshop', true ) );
    $payload = get_post_meta( $post->ID, '_fw_request_payload', true );
    $state = get_post_meta( $post->ID, '_fw_request_state', true );
    $uuid = get_post_meta( $post->ID, '_fw_request_uuid', true );
    $labels = array( 'pending' => findewerkstatt_t( 'In Prüfung' ), 'approved' => findewerkstatt_t( 'Freigegeben' ), 'rejected' => findewerkstatt_t( 'Abgelehnt' ) );
    echo ( '<p><strong>' . esc_html( findewerkstatt_t( 'Status:' ) ) . '</strong> ' ) . esc_html( $labels[ $state ] ?? $state ) . '</p>';
    echo '<p><strong>Konto:</strong> ' . esc_html( $user ? $user->display_name . ' (' . $user->user_email . ')' : findewerkstatt_t( 'Konto nicht verfügbar' ) ) . '</p>';
    if ( $workshop ) { echo '<p><strong>Betrieb:</strong> <a href="' . esc_url( get_edit_post_link( $workshop->ID ) ) . '">' . esc_html( $workshop->post_title ) . '</a></p>'; }
    if ( 'claim' === get_post_meta( $post->ID, '_fw_request_kind', true ) ) {
        echo ( '<p>' . esc_html( findewerkstatt_t( 'Vor der Freigabe muss die Vertretungsberechtigung geprüft werden. Eine passende E-Mail-Adresse allein ist kein Nachweis.' ) ) . '</p><p><strong>' . esc_html( findewerkstatt_t( 'Angaben zum Nachweis:' ) ) . '</strong></p><p style="white-space:pre-wrap">' ) . esc_html( $payload['evidence'] ?? '' ) . '</p>';
    } else {
        echo '<table class="widefat striped"><thead><tr><th>Feld</th><th>Bisher</th><th>Beantragt</th></tr></thead><tbody>';
        $labels = array( '_mechanic_phone' => findewerkstatt_t( 'Telefon' ), '_mechanic_whatsapp' => 'WhatsApp', '_mechanic_email' => 'E-Mail', '_mechanic_email_public' => findewerkstatt_t( 'E-Mail öffentlich' ), '_mechanic_website' => findewerkstatt_t( 'Website' ), '_mechanic_address' => findewerkstatt_t( 'Straße' ), '_mechanic_plz' => findewerkstatt_t( 'Postleitzahl' ) );
        $rows = array( findewerkstatt_t( 'Betriebsname' ) => array( $workshop ? $workshop->post_title : '', $payload['title'] ?? '' ), 'Beschreibung' => array( $workshop ? $workshop->post_content : '', $payload['content'] ?? '' ) );
        foreach ( $labels as $key => $label ) { $rows[ $label ] = array( $workshop ? get_post_meta( $workshop->ID, $key, true ) : '', $payload['meta'][ $key ] ?? '' ); }
        $language_names = findewerkstatt_workshop_languages();
        $before_languages = $workshop ? get_post_meta( $workshop->ID, '_mechanic_languages', true ) : array();
        $requested_languages = $payload['meta']['_mechanic_languages'] ?? $before_languages;
        $language_text = static function ( $codes ) use ( $language_names ) { return is_array( $codes ) ? implode( ', ', array_intersect_key( $language_names, array_flip( array_filter( $codes, 'is_string' ) ) ) ) : ''; };
        $rows[ findewerkstatt_t( 'Im Betrieb gesprochene Sprachen' ) ] = array( $language_text( $before_languages ), $language_text( $requested_languages ) );
        foreach ( $rows as $label => $row ) { echo '<tr><th>' . esc_html( $label ) . '</th><td style="white-space:pre-wrap">' . esc_html( $row[0] ) . '</td><td style="white-space:pre-wrap">' . esc_html( $row[1] ) . '</td></tr>'; }
        echo '</tbody></table>';
    }
    if ( 'pending' === $state ) {
        // WordPress's edit screen already has a form: submit separate external forms to avoid nesting.
        echo ( '<p><button type="submit" form="fw-approve-request" class="button button-primary">' . esc_html( findewerkstatt_t( 'Geprüft: freigeben' ) ) . '</button> <button type="submit" form="fw-reject-request" class="button">' . esc_html( findewerkstatt_t( 'Ablehnen' ) ) . '</button></p>' );
    }
}
add_action( 'admin_footer-post.php', function () {
    global $post;
    if ( ! $post || 'fw_member_request' !== $post->post_type || ! current_user_can( 'manage_options' ) || 'pending' !== get_post_meta( $post->ID, '_fw_request_state', true ) ) { return; }
    $uuid = get_post_meta( $post->ID, '_fw_request_uuid', true );
    foreach ( array( 'approve', 'reject' ) as $decision ) {
        echo '<form id="fw-' . esc_attr( $decision ) . '-request" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">'; findewerkstatt_language_field(); echo '<input type="hidden" name="action" value="fw_member_review_workshop"><input type="hidden" name="request_id" value="' . (int) $post->ID . '"><input type="hidden" name="request_uuid" value="' . esc_attr( $uuid ) . '"><input type="hidden" name="decision" value="' . esc_attr( $decision ) . '">';
        wp_nonce_field( 'fw_review_workshop_' . $post->ID . '_' . $uuid ); echo '</form>';
    }
} );

function findewerkstatt_member_render_workshop_tools( $view = 'all', $notice = array() ) {
    if ( ! findewerkstatt_is_workshop_member() || ! in_array( $view, array( 'all', 'edit', 'claim', 'requests' ), true ) ) { return; }
    $notice_kind = is_array( $notice ) && (int) ( $notice['user_id'] ?? 0 ) === get_current_user_id() && 'error' === ( $notice['type'] ?? '' ) ? ( $notice['kind'] ?? '' ) : '';
    $notice_values = findewerkstatt_member_workshop_notice_values( $notice_kind, $notice['values'] ?? array() );
    $account = findewerkstatt_page_url( 'mein-konto' );
    $editing = isset( $_GET['werkstatt'] ) && is_scalar( $_GET['werkstatt'] ) ? absint( $_GET['werkstatt'] ) : 0;
    if ( 'workshop_change' === $notice_kind && ! empty( $notice_values['workshop_id'] ) ) { $editing = $notice_values['workshop_id']; }
    if ( in_array( $view, array( 'all', 'edit' ), true ) && $editing && findewerkstatt_member_owns_workshop( $editing ) ) {
        $post = get_post( $editing );
        ?><section id="betrieb-bearbeiten" class="fw-box fw-member-workshops"><h2><?php echo esc_html( findewerkstatt_t( 'Änderung einreichen' ) ); ?></h2><p><?php echo esc_html( findewerkstatt_t( 'Ihre Angaben werden vor der Übernahme geprüft. Für Änderungen an Ort, Leistungen oder Marken nutzen Sie bitte das' ) ); ?> <a href="<?php echo esc_url( findewerkstatt_page_url( 'kontakt' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Kontaktformular' ) ); ?></a>.</p>
        <form class="fw-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><?php findewerkstatt_language_field(); ?>
        <input type="hidden" name="action" value="fw_member_workshop"><input type="hidden" name="request_kind" value="change"><input type="hidden" name="workshop_id" value="<?php echo (int) $editing; ?>"><input type="hidden" name="fw_submission_token" value="<?php echo esc_attr( findewerkstatt_form_submission_token( 'member_workshop_change' ) ); ?>"><?php wp_nonce_field( 'fw_member_workshop_change', 'fw_member_nonce' ); ?>
        <div class="fw-form-field"><label for="fw-edit-company"><?php echo esc_html( findewerkstatt_t( 'Betriebsname *' ) ); ?></label><input id="fw-edit-company" name="company_name" value="<?php echo esc_attr( $notice_values['company_name'] ?? $post->post_title ); ?>" maxlength="160" required></div>
        <div class="fw-form-field"><label for="fw-edit-description"><?php echo esc_html( findewerkstatt_t( 'Betriebsbeschreibung' ) ); ?></label><textarea id="fw-edit-description" name="description" rows="5" maxlength="5000"><?php echo esc_textarea( $notice_values['description'] ?? $post->post_content ); ?></textarea></div><div class="fw-form-grid">
        <?php foreach ( array( 'phone' => array( findewerkstatt_t( 'Telefon *' ), '_mechanic_phone', 60, 'tel' ), 'whatsapp' => array( 'WhatsApp', '_mechanic_whatsapp', 60, 'tel' ), 'email' => array( findewerkstatt_t( 'Betriebliche E-Mail-Adresse' ), '_mechanic_email', 254, 'email' ), 'website' => array( findewerkstatt_t( 'Website' ), '_mechanic_website', 250, 'text' ), 'street' => array( findewerkstatt_t( 'Straße und Hausnummer' ), '_mechanic_address', 200, 'text' ), 'plz' => array( findewerkstatt_t( 'Postleitzahl' ), '_mechanic_plz', 5, 'text' ) ) as $key => $field ) : ?>
        <div class="fw-form-field"><label for="fw-edit-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field[0] ); ?></label><input id="fw-edit-<?php echo esc_attr( $key ); ?>" type="<?php echo esc_attr( $field[3] ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $notice_values[ $key ] ?? get_post_meta( $editing, $field[1], true ) ); ?>" maxlength="<?php echo (int) $field[2]; ?>" <?php if ( 'phone' === $key ) { echo 'required'; } ?>></div><?php endforeach; ?></div>
        <?php findewerkstatt_render_workshop_language_choices( $notice_values['spoken_languages'] ?? ( get_post_meta( $editing, '_mechanic_languages', true ) ?: array() ), 'fw-edit-spoken' ); ?>
        <label class="fw-form-check"><input type="checkbox" name="public_email" value="1" <?php checked( $notice_values['public_email'] ?? ( 'yes' === get_post_meta( $editing, '_mechanic_email_public', true ) ) ); ?>><span><?php echo esc_html( findewerkstatt_t( 'Betriebliche E-Mail-Adresse im Profil anzeigen' ) ); ?></span></label><button class="fw-btn fw-btn-primary" type="submit"><?php echo esc_html( findewerkstatt_t( 'Änderung zur Prüfung senden' ) ); ?></button></form></section><?php
    } elseif ( in_array( $view, array( 'all', 'edit' ), true ) && $editing ) {
        echo ( '<section id="betrieb-bearbeiten" class="fw-box fw-member-workshops"><h2>' . esc_html( findewerkstatt_t( 'Eintrag bearbeiten' ) ) . '</h2><p>' . esc_html( findewerkstatt_t( 'Dieser Eintrag ist Ihrem Konto nicht zugeordnet oder nicht mehr verfügbar.' ) ) . '</p></section>' );
    } elseif ( 'edit' === $view ) {
        echo ( '<section id="betrieb-bearbeiten" class="fw-box fw-member-workshops"><h2>' . esc_html( findewerkstatt_t( 'Eintrag bearbeiten' ) ) . '</h2><p>' . esc_html( findewerkstatt_t( 'Wählen Sie in Ihrer Betriebsliste den Eintrag aus, den Sie bearbeiten möchten.' ) ) . '</p></section>' );
    }
    if ( in_array( $view, array( 'all', 'claim' ), true ) ) {
    $search = isset( $_GET['betriebssuche'] ) && is_string( $_GET['betriebssuche'] ) ? sanitize_text_field( wp_unslash( $_GET['betriebssuche'] ) ) : '';
    if ( findewerkstatt_form_length( $search ) > 100 ) { $search = ''; }
    $selected = isset( $_GET['uebernehmen'] ) && is_scalar( $_GET['uebernehmen'] ) ? get_post( absint( $_GET['uebernehmen'] ) ) : null;
    if ( 'workshop_claim' === $notice_kind && ! empty( $notice_values['workshop_id'] ) ) { $selected = get_post( $notice_values['workshop_id'] ); }
    if ( $selected && ( 'mechanic' !== $selected->post_type || 'publish' !== $selected->post_status || get_post_meta( $selected->ID, '_fw_owner_user_id', true ) ) ) { $selected = null; }
    ?><section id="eintrag-uebernehmen" class="fw-box fw-member-workshops"><h2><?php echo esc_html( findewerkstatt_t( 'Bestehenden Eintrag übernehmen' ) ); ?></h2><p><?php echo esc_html( findewerkstatt_t( 'Ist Ihr Betrieb bereits im Verzeichnis? Suchen Sie nach dem Betriebsnamen und beantragen Sie die Zuordnung. Wir prüfen Ihre Vertretungsberechtigung vor der Freigabe.' ) ); ?></p>
    <form method="get" action="<?php echo esc_url( $account ); ?>" class="fw-form"><?php findewerkstatt_language_field(); ?><input type="hidden" name="bereich" value="uebernehmen"><div class="fw-form-field"><label for="fw-claim-search"><?php echo esc_html( findewerkstatt_t( 'Betriebsname' ) ); ?></label><input id="fw-claim-search" name="betriebssuche" value="<?php echo esc_attr( $search ); ?>" minlength="3" maxlength="100" required></div><button class="fw-btn fw-btn-outline" type="submit"><?php echo esc_html( findewerkstatt_t( 'Betrieb suchen' ) ); ?></button></form>
    <?php if ( findewerkstatt_form_length( $search ) >= 3 ) :
        $results = get_posts( array( 'post_type' => 'mechanic', 'post_status' => 'publish', 'posts_per_page' => 20, 's' => $search ) );
        if ( ! $results ) { echo ( '<p>' . esc_html( findewerkstatt_t( 'Keine passenden Einträge gefunden. Sie können Ihren Betrieb neu eintragen.' ) ) . '</p>' ); }
        else {
            echo '<ul class="fw-member-workshop-list">';
            foreach ( $results as $result ) {
                echo '<li><div><h3>' . esc_html( $result->post_title ) . '</h3>';
                $city = findewerkstatt_get_city_term( $result->ID );
                get_template_part( 'template-parts/workshop-location', null, array( 'context' => $city ? findewerkstatt_location_context( $city ) : null ) );
                echo '</div><div>';
                if ( ! get_post_meta( $result->ID, '_fw_owner_user_id', true ) ) { echo '<a class="fw-btn fw-btn-outline fw-btn-sm" href="' . esc_url( add_query_arg( array( 'bereich' => 'uebernehmen', 'uebernehmen' => $result->ID ), $account ) . '#eintrag-uebernehmen' ) . ( '">' . esc_html( findewerkstatt_t( 'Zuordnung anfragen' ) ) . '</a>' ); }
                else { echo ( '<span>' . esc_html( findewerkstatt_t( 'Bereits zugeordnet' ) ) . '</span>' ); }
                echo '</div></li>';
            }
            echo ( '</ul><p class="fw-form-help">' . esc_html( findewerkstatt_t( 'Es werden höchstens 20 Treffer angezeigt. Präzisieren Sie bei Bedarf den Betriebsnamen.' ) ) . '</p>' );
        }
    endif; ?>
    <?php if ( $selected ) : ?><h3><?php echo esc_html( $selected->post_title ); ?></h3><p><a href="<?php echo esc_url( get_permalink( $selected->ID ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Öffentliches Profil ansehen' ) ); ?></a></p><form class="fw-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><?php findewerkstatt_language_field(); ?><input type="hidden" name="action" value="fw_member_workshop"><input type="hidden" name="request_kind" value="claim"><input type="hidden" name="workshop_id" value="<?php echo (int) $selected->ID; ?>"><input type="hidden" name="fw_submission_token" value="<?php echo esc_attr( findewerkstatt_form_submission_token( 'member_workshop_claim' ) ); ?>"><?php wp_nonce_field( 'fw_member_workshop_claim', 'fw_member_nonce' ); ?><div class="fw-form-field"><label for="fw-claim-evidence"><?php echo esc_html( findewerkstatt_t( 'Wie können Sie Ihre Berechtigung nachweisen? *' ) ); ?></label><textarea id="fw-claim-evidence" name="evidence" minlength="20" maxlength="2000" rows="4" required aria-describedby="fw-claim-help"><?php echo esc_textarea( $notice_values['evidence'] ?? '' ); ?></textarea><p id="fw-claim-help" class="fw-form-help"><?php echo esc_html( findewerkstatt_t( 'Nennen Sie Ihre Funktion im Betrieb und eine betriebliche Kontaktmöglichkeit. Senden Sie keine Passwörter oder Ausweiskopien. Diese Angaben sind nur für die interne Prüfung sichtbar.' ) ); ?></p></div><button class="fw-btn fw-btn-primary" type="submit"><?php echo esc_html( findewerkstatt_t( 'Zuordnung zur Prüfung anfragen' ) ); ?></button></form><?php endif; ?></section><?php
    }
    if ( ! in_array( $view, array( 'all', 'requests' ), true ) ) { return; }
    $requests = get_posts( array( 'post_type' => 'fw_member_request', 'post_status' => 'private', 'posts_per_page' => 15, 'meta_key' => '_fw_request_user', 'meta_value' => get_current_user_id(), 'meta_type' => 'NUMERIC' ) );
    if ( $requests ) {
        $statuses = array( 'pending' => findewerkstatt_t( 'In Prüfung' ), 'approved' => findewerkstatt_t( 'Freigegeben' ), 'rejected' => findewerkstatt_t( 'Abgelehnt' ) );
        echo ( '<section class="fw-box fw-member-workshops"><h2>' . esc_html( findewerkstatt_t( 'Meine Eintragsanfragen' ) ) . '</h2><ul class="fw-member-workshop-list">' );
        foreach ( $requests as $request ) { echo '<li><span>' . esc_html( $request->post_title ) . '</span><span class="fw-member-status">' . esc_html( $statuses[ get_post_meta( $request->ID, '_fw_request_state', true ) ] ?? findewerkstatt_t( 'In Prüfung' ) ) . '</span></li>'; }
        echo ( '</ul><p class="fw-form-help">' . esc_html( findewerkstatt_t( 'Hier sehen Sie Ihre letzten 15 Zuordnungs- und Änderungsanfragen.' ) ) . '</p></section>' );
    } elseif ( 'requests' === $view ) {
        echo ( '<section class="fw-box fw-member-workshops"><h2>' . esc_html( findewerkstatt_t( 'Meine Eintragsanfragen' ) ) . '</h2><p>' . esc_html( findewerkstatt_t( 'Sie haben noch keine Zuordnungs- oder Änderungsanfrage eingereicht.' ) ) . '</p></section>' );
    }
}
