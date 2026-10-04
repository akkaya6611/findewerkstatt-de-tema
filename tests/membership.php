<?php
/**
 * Run with WP-CLI eval-file --user=<administrator> against a backed-up local site.
 * Fixtures use unique @example.invalid accounts and UUID-labelled posts only.
 * No existing users, business records or package settings are changed.
 */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI || ! defined( 'ABSPATH' ) || ! current_user_can( 'manage_options' ) ) {
    throw new RuntimeException( 'Run this test through WP-CLI as an administrator.' );
}
require_once ABSPATH . 'wp-admin/includes/user.php';

global $fw_membership_test_checks;
$fw_membership_test_checks = array();
function fw_membership_test_assert( $condition, $label ) {
    global $fw_membership_test_checks;
    if ( ! $condition ) { throw new RuntimeException( 'FAIL: ' . $label ); }
    $fw_membership_test_checks[] = $label;
}

$initial_user = get_current_user_id();
$initial_globals = array( 'post' => $_POST, 'get' => $_GET, 'cookie' => $_COOKIE, 'server' => $_SERVER );
$run_id = str_replace( '-', '', wp_generate_uuid4() );
$login_prefix = 'fwmtest_' . $run_id . '_';
$post_prefix = 'FWTEST Member ' . $run_id;
$fixture_users = array();
$fixture_posts = array();
$fixture_notices = array();
$fixture_tokens = array();
$mail_calls = 0;
$failure = null;
$_SERVER['REMOTE_ADDR'] = '2001:db8:' . substr( $run_id, 0, 4 ) . '::' . substr( $run_id, -4 );
$test_address = $_SERVER['REMOTE_ADDR'];
$mail_filter = function ( $pre, $atts ) use ( &$mail_calls ) { $mail_calls++; return true; };
$post_capture = function ( $id, $post ) use ( $post_prefix, &$fixture_posts ) {
    if ( in_array( $post->post_type, array( 'mechanic', 'fw_member_request' ), true ) && false !== strpos( $post->post_title, $post_prefix ) ) {
        $fixture_posts[] = (int) $id;
        add_post_meta( $id, '_fw_membership_test_marker', $post_prefix, true );
    }
};
$user_capture = function ( $id ) use ( $login_prefix, $run_id, $post_prefix, &$fixture_users ) {
    $user = get_userdata( $id );
    if ( $user && ( 0 === strpos( $user->user_login, $login_prefix ) || 0 === strpos( $user->user_email, 'fw-member-' . $run_id . '-' ) ) && str_ends_with( $user->user_email, '@example.invalid' ) ) {
        $fixture_users[] = (int) $id;
        update_user_meta( $id, '_fw_membership_test_marker', $post_prefix );
    }
};
add_filter( 'pre_wp_mail', $mail_filter, PHP_INT_MAX, 2 );
add_action( 'wp_insert_post', $post_capture, 10, 2 );
add_action( 'user_register', $user_capture, 10 );

$cleanup = function () use ( &$fixture_users, &$fixture_posts, &$fixture_notices, &$fixture_tokens, $post_prefix, $login_prefix, $initial_user, $test_address ) {
    wp_set_current_user( $initial_user );
    foreach ( array_unique( $fixture_posts ) as $id ) {
        $post = get_post( $id );
        if ( $post && in_array( $post->post_type, array( 'mechanic', 'fw_member_request' ), true ) && $post_prefix === get_post_meta( $id, '_fw_membership_test_marker', true ) ) { wp_delete_post( $id, true ); }
        delete_option( 'fw_member_lock_review_' . $id );
    }
    foreach ( array_unique( $fixture_users ) as $id ) {
        foreach ( array( 'workshop', 'plan', 'account' ) as $purpose ) { delete_option( 'fw_member_lock_' . $purpose . '_' . $id ); }
        $user = get_userdata( $id );
        if ( $user && $post_prefix === get_user_meta( $id, '_fw_membership_test_marker', true ) && str_ends_with( $user->user_email, '@example.invalid' ) ) { wp_delete_user( $id, $initial_user ); }
    }
    foreach ( $fixture_notices as $token ) { delete_transient( 'fw_member_notice_' . $token ); }
    foreach ( $fixture_tokens as $token ) {
        $token_hash = hash( 'sha256', $token );
        delete_transient( 'fw_form_token_' . $token_hash );
        delete_option( 'fw_form_lock_' . $token_hash );
        wp_clear_scheduled_hook( 'fw_form_cleanup_lock', array( 'fw_form_lock_' . $token_hash ) );
    }
    delete_transient( 'fw_rate_' . hash_hmac( 'sha256', 'registration|' . $test_address, wp_salt( 'nonce' ) ) );
    delete_transient( 'fw_rate_' . hash_hmac( 'sha256', 'member_workshop|' . $test_address, wp_salt( 'nonce' ) ) );
    delete_transient( 'fw_rate_' . hash_hmac( 'sha256', 'member_signup|' . $test_address, wp_salt( 'nonce' ) ) );
};
register_shutdown_function( $cleanup );

$make_post = function ( $suffix, $status = 'publish', $owner = 0 ) use ( $post_prefix, $initial_user ) {
    $id = wp_insert_post( array( 'post_type' => 'mechanic', 'post_status' => $status, 'post_title' => $post_prefix . ' ' . $suffix, 'post_author' => $initial_user ), true );
    if ( is_wp_error( $id ) || ! $id ) { throw new RuntimeException( 'Could not create an isolated workshop fixture.' ); }
    if ( $owner ) { update_post_meta( $id, '_fw_owner_user_id', (int) $owner ); }
    update_post_meta( $id, '_mechanic_phone', '+49309999999' );
    update_post_meta( $id, '_mechanic_is_verified', 'no' );
    update_post_meta( $id, '_mechanic_rating_avg', '4.7' );
    return (int) $id;
};

try {
    foreach ( array( 'a', 'b', 'c', 'd', 'e' ) as $suffix ) {
        $id = wp_insert_user( array( 'user_login' => $login_prefix . $suffix, 'user_pass' => wp_generate_password( 32, true ), 'user_email' => 'fw-member-' . $run_id . '-' . $suffix . '@example.invalid', 'display_name' => 'Membership Test ' . strtoupper( $suffix ), 'role' => 'fw_workshop_member' ) );
        if ( is_wp_error( $id ) || ! $id ) { throw new RuntimeException( 'Could not create an isolated account fixture.' ); }
        $members[ $suffix ] = (int) $id;
    }
    $a = $members['a']; $b = $members['b']; $c = $members['c'];
    $nonmembers = array();
    foreach ( array( 'administrator', 'subscriber' ) as $role ) {
        $id = wp_insert_user( array( 'user_login' => $login_prefix . $role, 'user_pass' => wp_generate_password( 32, true ), 'user_email' => 'fw-member-' . $run_id . '-' . $role . '@example.invalid', 'display_name' => 'Membership Test ' . $role, 'role' => $role ) );
        if ( is_wp_error( $id ) || ! $id ) { throw new RuntimeException( 'Could not create an isolated nonmember account fixture.' ); }
        $nonmembers[ $role ] = (int) $id;
        wp_set_current_user( $id );
        $nonmember_request = findewerkstatt_member_plan_request( 'plus' );
        fw_membership_test_assert( is_wp_error( $nonmember_request ) && 'member_permission' === $nonmember_request->get_error_code() && ! metadata_exists( 'user', $id, '_fw_member_plan_request' ) && ! metadata_exists( 'user', $id, '_fw_member_plan' ), 'The ' . $role . ' account cannot create a member package request or entitlement through the helper' );

        // Exercise the real POST handler with otherwise valid credentials and token.
        // Capture its redirect before exit so every fixture still reaches cleanup.
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $submission_token = findewerkstatt_form_submission_token( 'member_plan_request' ); $fixture_tokens[] = $submission_token;
        $_POST = array( 'selected_plan' => 'plus', 'fw_submission_token' => $submission_token, 'fw_member_nonce' => wp_create_nonce( 'fw_member_plan_request' ) );
        $redirect_location = '';
        $redirect_capture = function ( $location ) use ( &$redirect_location ) { $redirect_location = $location; throw new RuntimeException( 'membership-test-redirect' ); };
        add_filter( 'wp_redirect', $redirect_capture, PHP_INT_MIN );
        try { findewerkstatt_member_plan_request_submit(); }
        catch ( RuntimeException $redirect ) { if ( 'membership-test-redirect' !== $redirect->getMessage() ) { throw $redirect; } }
        finally { remove_filter( 'wp_redirect', $redirect_capture, PHP_INT_MIN ); }
        $redirect_query = array(); parse_str( wp_parse_url( $redirect_location, PHP_URL_QUERY ) ?? '', $redirect_query );
        $notice_token = $redirect_query['fw_member_notice'] ?? '';
        if ( $notice_token ) { $fixture_notices[] = $notice_token; }
        $_GET = array( 'fw_member_notice' => $notice_token );
        $role_notice = findewerkstatt_member_notice();
        fw_membership_test_assert( 'error' === ( $role_notice['type'] ?? '' ) && 'plan' === ( $role_notice['kind'] ?? '' ) && ! metadata_exists( 'user', $id, '_fw_member_plan_request' ) && 'free' === findewerkstatt_member_plan() && false === get_option( 'fw_form_lock_' . hash( 'sha256', $submission_token ) ), 'The real package POST handler rejects the ' . $role . ' account even with a valid nonce and token, without storing a private request or claim lock' );
        $_POST = $initial_globals['post']; $_GET = $initial_globals['get']; $_SERVER['REQUEST_METHOD'] = $initial_globals['server']['REQUEST_METHOD'] ?? 'GET';
    }
    wp_set_current_user( $nonmembers['administrator'] );
    fw_membership_test_assert( is_wp_error( findewerkstatt_member_plan_request( 'plus', $nonmembers['subscriber'] ) ) && ! metadata_exists( 'user', $nonmembers['subscriber'], '_fw_member_plan_request' ), 'Administrator privileges cannot make a package request for a nonmember target account' );
    $private_type = get_post_type_object( 'fw_member_request' );
    fw_membership_test_assert( $private_type && ! $private_type->public && ! $private_type->publicly_queryable && ! $private_type->show_in_rest && 'manage_options' === $private_type->cap->read_post, 'Private review records are not public or REST-readable and require administrator rights' );
    wp_set_current_user( $a );
    fw_membership_test_assert( findewerkstatt_is_workshop_member() && 'free' === findewerkstatt_member_plan() && current_user_can( 'read' ) && ! current_user_can( 'edit_posts' ) && ! current_user_can( 'publish_posts' ) && ! current_user_can( 'upload_files' ) && ! current_user_can( 'manage_options' ), 'New members have a free package and read-only capabilities' );
    $unowned = $make_post( 'unowned' );
    wp_update_post( array( 'ID' => $unowned, 'post_author' => $a ) );
    fw_membership_test_assert( ! findewerkstatt_member_owns_workshop( $unowned ) && array() === findewerkstatt_member_owned_workshops(), 'A matching post author never grants workshop ownership' );
    wp_update_post( array( 'ID' => $unowned, 'post_author' => $initial_user ) );
    $free_limit = findewerkstatt_member_listing_limit();
    for ( $i = 0; $i < $free_limit; $i++ ) { $make_post( 'owned-' . $i, array( 'pending', 'draft', 'private', 'publish' )[ $i % 4 ], $a ); }
    fw_membership_test_assert( $free_limit === count( findewerkstatt_member_owned_workshops() ) && ! findewerkstatt_member_can_add_workshop(), 'Pending, draft and published owned profiles consume the package quota' );
    wp_set_current_user( $b );
    fw_membership_test_assert( array() === findewerkstatt_member_owned_workshops( $a ) && ! findewerkstatt_member_can_add_workshop( $a ) && is_wp_error( findewerkstatt_member_plan_request( 'plus', $a ) ), 'A member cannot inspect or alter another member account' );

    $claim_b = findewerkstatt_member_create_workshop_request( $unowned, 'claim', array( 'evidence' => 'I am the authorized owner of this isolated test business and can verify my authority.' ) );
    fw_membership_test_assert( ! is_wp_error( $claim_b ) && 'private' === get_post_status( $claim_b ) && ! get_post_meta( $unowned, '_fw_owner_user_id', true ), 'A claim creates a private pending request without automatically granting ownership' );
    fw_membership_test_assert( ! current_user_can( 'read_post', $claim_b ) && ! current_user_can( 'edit_post', $claim_b ) && ! current_user_can( 'read_private_posts' ), 'Members cannot open the administrator review record through normal post capabilities' );
    $duplicate_claim = findewerkstatt_member_create_workshop_request( $unowned, 'claim', array( 'evidence' => 'A repeated authorized test request with sufficient evidence.' ) );
    fw_membership_test_assert( $claim_b === $duplicate_claim, 'Repeated pending claims reuse the same private review record' );
    $uuid_b = get_post_meta( $claim_b, '_fw_request_uuid', true );
    fw_membership_test_assert( is_wp_error( findewerkstatt_member_review_workshop( $claim_b, $uuid_b, 'approve' ) ), 'A member cannot approve their own workshop request' );
    wp_set_current_user( $c );
    $claim_c = findewerkstatt_member_create_workshop_request( $unowned, 'claim', array( 'evidence' => 'This competing test account also requests operator verification of ownership.' ) );
    fw_membership_test_assert( ! is_wp_error( $claim_c ) && $claim_c !== $claim_b, 'Competing accounts have independent claims awaiting operator verification' );
    wp_set_current_user( $initial_user );
    fw_membership_test_assert( is_wp_error( findewerkstatt_member_review_workshop( $claim_b, wp_generate_uuid4(), 'approve' ) ) && ! get_post_meta( $unowned, '_fw_owner_user_id', true ), 'A stale or incorrect request UUID cannot grant ownership' );
    fw_membership_test_assert( true === findewerkstatt_member_review_workshop( $claim_b, $uuid_b, 'approve' ) && $b === (int) get_post_meta( $unowned, '_fw_owner_user_id', true ) && 'approved' === get_post_meta( $claim_b, '_fw_request_state', true ), 'Only administrator approval assigns the verified claimant' );
    fw_membership_test_assert( is_wp_error( findewerkstatt_member_review_workshop( $claim_b, $uuid_b, 'approve' ) ), 'An approved ownership request cannot be replayed' );
    $uuid_c = get_post_meta( $claim_c, '_fw_request_uuid', true );
    fw_membership_test_assert( is_wp_error( findewerkstatt_member_review_workshop( $claim_c, $uuid_c, 'approve' ) ) && $b === (int) get_post_meta( $unowned, '_fw_owner_user_id', true ), 'A competing claim cannot replace an already assigned owner' );
    fw_membership_test_assert( true === findewerkstatt_member_review_workshop( $claim_c, $uuid_c, 'reject' ) && 'rejected' === get_post_meta( $claim_c, '_fw_request_state', true ), 'The operator can reject a competing claim without changing ownership' );
    wp_set_current_user( $a );
    $quota_target = $make_post( 'quota-target' );
    $quota_claim = findewerkstatt_member_create_workshop_request( $quota_target, 'claim', array( 'evidence' => 'An isolated ownership request from a test account already at its package quota.' ) );
    fw_membership_test_assert( ! is_wp_error( $quota_claim ), 'A full account can request verification without receiving an extra profile' );
    wp_set_current_user( $initial_user );
    $quota_result = findewerkstatt_member_review_workshop( $quota_claim, get_post_meta( $quota_claim, '_fw_request_uuid', true ), 'approve' );
    fw_membership_test_assert( is_wp_error( $quota_result ) && 'quota' === $quota_result->get_error_code() && ! get_post_meta( $quota_target, '_fw_owner_user_id', true ), 'Ownership approval enforces the target member quota while holding its workshop lock' );

    $change_input = array( 'company_name' => $post_prefix . ' revised', 'description' => 'An isolated test description.', 'phone' => '030 1234567', 'whatsapp' => '', 'email' => 'business-' . $run_id . '@example.invalid', 'website' => 'https://example.invalid/workshop', 'street' => 'Teststraße 1', 'plz' => '10115', 'public_email' => false, '_fw_owner_user_id' => $a, 'post_status' => 'draft', '_mechanic_rating_avg' => 1, '_mechanic_is_verified' => 'yes' );
    wp_set_current_user( $c );
    fw_membership_test_assert( is_wp_error( findewerkstatt_member_create_workshop_request( $unowned, 'change', $change_input ) ), 'An unrelated member cannot submit changes for a workshop' );
    wp_set_current_user( $b );
    $change = findewerkstatt_member_create_workshop_request( $unowned, 'change', $change_input );
    fw_membership_test_assert( ! is_wp_error( $change ) && '+49309999999' === get_post_meta( $unowned, '_mechanic_phone', true ), 'Submitted business changes remain private until approval' );
    $change_payload = get_post_meta( $change, '_fw_request_payload', true );
    fw_membership_test_assert( ! isset( $change_payload['meta']['_fw_owner_user_id'] ) && ! isset( $change_payload['meta']['_mechanic_rating_avg'] ) && ! isset( $change_payload['meta']['_mechanic_is_verified'] ) && ! isset( $change_payload['post_status'] ), 'Change requests discard ownership, publication, rating and verification fields' );
    wp_set_current_user( $initial_user );
    fw_membership_test_assert( true === findewerkstatt_member_review_workshop( $change, get_post_meta( $change, '_fw_request_uuid', true ), 'approve' ) && '+49301234567' === get_post_meta( $unowned, '_mechanic_phone', true ) && 'publish' === get_post_status( $unowned ) && $b === (int) get_post_meta( $unowned, '_fw_owner_user_id', true ) && '4.7' === get_post_meta( $unowned, '_mechanic_rating_avg', true ) && 'no' === get_post_meta( $unowned, '_mechanic_is_verified', true ), 'Approved changes update only allowed fields and preserve status, ownership, ratings and verification' );
    $before = get_post_meta( $change, '_fw_request_before', true );
    fw_membership_test_assert( '+49309999999' === ( $before['meta']['_mechanic_phone'] ?? '' ), 'The private review record retains the previous business values' );

    $visibility_taxonomies = array( 'mechanic_city', 'mechanic_district', 'service_type', 'car_brand' );
    foreach ( $visibility_taxonomies as $taxonomy ) {
        $term_ids = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false, 'number' => 1, 'fields' => 'ids' ) );
        if ( ! is_wp_error( $term_ids ) && $term_ids ) { wp_set_object_terms( $unowned, array( (int) $term_ids[0] ), $taxonomy ); }
    }
    $visibility_before = array( 'post' => get_post( $unowned, ARRAY_A ), 'meta' => get_post_meta( $unowned ), 'terms' => wp_get_object_terms( $unowned, $visibility_taxonomies, array( 'fields' => 'ids' ) ) );
    wp_set_current_user( $c );
    fw_membership_test_assert( is_wp_error( findewerkstatt_member_set_workshop_visibility( $unowned, 'pause' ) ) && 'publish' === get_post_status( $unowned ), 'Another member cannot pause an owned public workshop' );
    wp_set_current_user( $initial_user );
    fw_membership_test_assert( is_wp_error( findewerkstatt_member_set_workshop_visibility( $unowned, 'pause' ) ) && 'publish' === get_post_status( $unowned ), 'The member visibility helper does not grant an administrator automatic ownership' );
    wp_set_current_user( $b );
    fw_membership_test_assert( is_wp_error( findewerkstatt_member_set_workshop_visibility( $unowned, 'publish' ) ) && is_wp_error( findewerkstatt_member_set_workshop_visibility( array( $unowned ), 'pause' ) ), 'Visibility actions reject unsupported operations and malformed profile IDs' );
    $visibility_review_lock = findewerkstatt_member_lock_key( 'fw_member_lock_review_' . $unowned );
    fw_membership_test_assert( ! is_wp_error( $visibility_review_lock ) && is_wp_error( findewerkstatt_member_set_workshop_visibility( $unowned, 'pause' ) ) && 'publish' === get_post_status( $unowned ), 'Visibility changes cannot bypass an active profile review lock' );
    findewerkstatt_member_release_lock( $visibility_review_lock );
    $visibility_quota_lock = findewerkstatt_member_acquire_lock( $b, 'workshop' );
    fw_membership_test_assert( ! is_wp_error( $visibility_quota_lock ) && is_wp_error( findewerkstatt_member_set_workshop_visibility( $unowned, 'pause' ) ) && false === get_option( 'fw_member_lock_review_' . $unowned ), 'A held member lock prevents visibility changes and releases the intermediate profile lock' );
    findewerkstatt_member_release_lock( $visibility_quota_lock );
    $owned_before_pause = count( findewerkstatt_member_owned_workshops() );
    fw_membership_test_assert( true === findewerkstatt_member_set_workshop_visibility( $unowned, 'pause' ) && 'draft' === get_post_status( $unowned ) && 'yes' === get_post_meta( $unowned, '_fw_member_paused', true ), 'An owner can pause a published profile without deleting it' );
    fw_membership_test_assert( count( findewerkstatt_member_owned_workshops() ) === $owned_before_pause && ! findewerkstatt_member_can_add_workshop(), 'A paused profile stays assigned and continues to consume its package quota' );
    fw_membership_test_assert( $b === (int) get_post_meta( $unowned, '_fw_owner_user_id', true ) && $visibility_before['post']['post_author'] === get_post_field( 'post_author', $unowned ) && $visibility_before['post']['post_title'] === get_post_field( 'post_title', $unowned ) && $visibility_before['post']['post_content'] === get_post_field( 'post_content', $unowned ) && $visibility_before['meta']['_mechanic_rating_avg'] === get_post_meta( $unowned, '_mechanic_rating_avg' ) && $visibility_before['meta']['_mechanic_is_verified'] === get_post_meta( $unowned, '_mechanic_is_verified' ), 'Pausing preserves the operator author, business data, ownership, rating and verification' );
    $visibility_after_terms = wp_get_object_terms( $unowned, $visibility_taxonomies, array( 'fields' => 'ids' ) );
    fw_membership_test_assert( $visibility_before['terms'] === $visibility_after_terms, 'Pausing preserves assigned locations, services and brands' );
    fw_membership_test_assert( is_wp_error( findewerkstatt_member_set_workshop_visibility( $unowned, 'pause' ) ), 'An already paused profile cannot replay a publish-only pause action' );
    fw_membership_test_assert( true === findewerkstatt_member_set_workshop_visibility( $unowned, 'submit' ) && 'pending' === get_post_status( $unowned ) && '' === get_post_meta( $unowned, '_fw_member_paused', true ) && $b === (int) get_post_meta( $unowned, '_fw_owner_user_id', true ), 'Resuming a member-paused profile returns it to operator review without publishing it' );
    fw_membership_test_assert( is_wp_error( findewerkstatt_member_set_workshop_visibility( $unowned, 'submit' ) ) && 'pending' === get_post_status( $unowned ), 'A pending review cannot be replayed into publication' );
    $unmarked_draft = $make_post( 'unmarked-draft', 'draft', $b );
    fw_membership_test_assert( is_wp_error( findewerkstatt_member_set_workshop_visibility( $unmarked_draft, 'submit' ) ) && 'draft' === get_post_status( $unmarked_draft ), 'An imported or arbitrary draft cannot be submitted through the member-paused action' );
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = array( 'fw_member_nonce' => wp_create_nonce( 'fw_member_visibility_' . $unowned ) );
    fw_membership_test_assert( findewerkstatt_member_valid_post( 'fw_member_visibility_' . $unowned ) && ! findewerkstatt_member_valid_post( 'fw_member_visibility_' . $unmarked_draft ), 'Visibility nonces are specific to the owning session and the target profile' );
    $_SERVER['REQUEST_METHOD'] = 'GET';
    fw_membership_test_assert( ! findewerkstatt_member_valid_post( 'fw_member_visibility_' . $unowned ), 'Visibility actions reject GET requests even with a valid nonce' );
    $_SERVER['REQUEST_METHOD'] = 'POST'; $_POST = array( 'fw_member_nonce' => 'invalid' );
    fw_membership_test_assert( ! findewerkstatt_member_valid_post( 'fw_member_visibility_' . $unowned ), 'Visibility actions reject an invalid nonce' );
    $_POST = $initial_globals['post']; $_SERVER['REQUEST_METHOD'] = $initial_globals['server']['REQUEST_METHOD'] ?? 'GET';
    $_GET = array( 'werkstatt' => $unowned );
    ob_start(); findewerkstatt_member_render_workshop_tools( 'edit' ); $edit_markup = ob_get_clean();
    if ( preg_match_all( '/name="fw_submission_token" value="([A-Za-z0-9]+)"/', $edit_markup, $rendered_tokens ) ) { $fixture_tokens = array_merge( $fixture_tokens, $rendered_tokens[1] ); }
    fw_membership_test_assert( false !== strpos( $edit_markup, 'betrieb-bearbeiten' ) && false === strpos( $edit_markup, 'eintrag-uebernehmen' ) && false === strpos( $edit_markup, 'Meine Eintragsanfragen' ), 'The edit dashboard section renders only the owned change form' );
    ob_start(); findewerkstatt_member_render_workshop_tools( 'claim' ); $claim_markup = ob_get_clean();
    fw_membership_test_assert( false !== strpos( $claim_markup, 'eintrag-uebernehmen' ) && false !== strpos( $claim_markup, 'name="bereich" value="uebernehmen"' ) && false === strpos( $claim_markup, 'betrieb-bearbeiten' ) && false === strpos( $claim_markup, 'Meine Eintragsanfragen' ), 'The claim dashboard section preserves its selection and omits edit and history blocks' );
    ob_start(); findewerkstatt_member_render_workshop_tools( 'requests' ); $requests_markup = ob_get_clean();
    fw_membership_test_assert( false !== strpos( $requests_markup, 'Meine Eintragsanfragen' ) && false === strpos( $requests_markup, 'eintrag-uebernehmen' ) && false === strpos( $requests_markup, 'betrieb-bearbeiten' ), 'The request dashboard section renders only the current account review history' );
    $refill_input = array( 'workshop_id' => $unowned, 'company_name' => 'Submitted "Workshop"', 'description' => "Submitted description\nwith another line.", 'phone' => 'invalid submitted phone', 'whatsapp' => '', 'email' => 'invalid submitted email', 'website' => '', 'street' => 'Submitted Straße 9', 'plz' => 'bad', 'public_email' => false, 'password' => 'never preserve this password', '_fw_owner_user_id' => $c, 'post_status' => 'publish' );
    $refill_values = findewerkstatt_member_workshop_notice_values( 'workshop_change', $refill_input );
    fw_membership_test_assert( $unowned === $refill_values['workshop_id'] && $refill_input['company_name'] === $refill_values['company_name'] && $refill_input['description'] === $refill_values['description'] && false === $refill_values['public_email'] && ! isset( $refill_values['password'] ) && ! isset( $refill_values['_fw_owner_user_id'] ) && ! isset( $refill_values['post_status'] ), 'Workshop error notices preserve bounded business values and discard passwords and privileged fields' );
    $refill_notice = array( 'user_id' => $b, 'type' => 'error', 'kind' => 'workshop_change', 'values' => $refill_values );
    $_GET = array();
    ob_start(); findewerkstatt_member_render_workshop_tools( 'edit', $refill_notice ); $refill_markup = ob_get_clean();
    if ( preg_match_all( '/name="fw_submission_token" value="([A-Za-z0-9]+)"/', $refill_markup, $rendered_tokens ) ) { $fixture_tokens = array_merge( $fixture_tokens, $rendered_tokens[1] ); }
    fw_membership_test_assert( false !== strpos( $refill_markup, 'name="workshop_id" value="' . $unowned . '"' ) && false !== strpos( $refill_markup, 'value="' . esc_attr( $refill_input['company_name'] ) . '"' ) && false !== strpos( $refill_markup, esc_textarea( $refill_input['description'] ) ) && false !== strpos( $refill_markup, esc_attr( $refill_input['phone'] ) ), 'An owned change error restores the selected editor and safely escaped submitted values' );
    $refill_notice['user_id'] = $a;
    ob_start(); findewerkstatt_member_render_workshop_tools( 'edit', $refill_notice ); $foreign_refill_markup = ob_get_clean();
    fw_membership_test_assert( false === strpos( $foreign_refill_markup, esc_attr( $refill_input['company_name'] ) ) && false === strpos( $foreign_refill_markup, 'name="workshop_id"' ), 'A workshop error notice from another account cannot choose an editor or refill its fields' );
    $refill_notice['user_id'] = $b; $refill_notice['type'] = 'success';
    ob_start(); findewerkstatt_member_render_workshop_tools( 'edit', $refill_notice ); $success_refill_markup = ob_get_clean();
    fw_membership_test_assert( false === strpos( $success_refill_markup, 'name="workshop_id"' ), 'Only error notices restore submitted editor values' );
    $claim_refill_target = $make_post( 'claim-refill-target' );
    $claim_refill_input = array( 'workshop_id' => $claim_refill_target, 'evidence' => 'Submitted <strong>proof</strong> with a "quoted" reference.', 'password' => 'never preserve this password', 'post_status' => 'publish' );
    $claim_refill_values = findewerkstatt_member_workshop_notice_values( 'workshop_claim', $claim_refill_input );
    fw_membership_test_assert( $claim_refill_target === $claim_refill_values['workshop_id'] && ! isset( $claim_refill_values['password'] ) && ! isset( $claim_refill_values['post_status'] ) && false === strpos( $claim_refill_values['evidence'], '<strong>' ), 'Claim error notices retain only the eligible public profile and sanitized evidence' );
    ob_start(); findewerkstatt_member_render_workshop_tools( 'claim', array( 'user_id' => $b, 'type' => 'error', 'kind' => 'workshop_claim', 'values' => $claim_refill_values ) ); $claim_refill_markup = ob_get_clean();
    if ( preg_match_all( '/name="fw_submission_token" value="([A-Za-z0-9]+)"/', $claim_refill_markup, $rendered_tokens ) ) { $fixture_tokens = array_merge( $fixture_tokens, $rendered_tokens[1] ); }
    fw_membership_test_assert( false !== strpos( $claim_refill_markup, 'name="workshop_id" value="' . $claim_refill_target . '"' ) && false !== strpos( $claim_refill_markup, esc_textarea( $claim_refill_values['evidence'] ) ), 'A claim validation error restores the selected public profile and submitted evidence' );
    $large_evidence = findewerkstatt_member_workshop_notice_values( 'workshop_claim', array( 'workshop_id' => $claim_refill_target, 'evidence' => str_repeat( 'ä', 2100 ) ) );
    fw_membership_test_assert( 2000 === findewerkstatt_form_length( $large_evidence['evidence'] ), 'Saved claim evidence has a strict 2000-character notice limit' );
    fw_membership_test_assert( array() === findewerkstatt_member_workshop_notice_values( 'workshop_claim', array( 'workshop_id' => $unmarked_draft, 'evidence' => 'A draft must never become a selected claim profile.' ) ) && array() === findewerkstatt_member_workshop_notice_values( 'workshop_request', $refill_input ) && array() === findewerkstatt_member_workshop_notice_values( 'workshop_change', array( 'workshop_id' => array( $unowned ) ) ), 'Invalid claim targets, success notices and malformed profile IDs retain no private form values' );
    wp_set_current_user( $c );
    fw_membership_test_assert( array() === findewerkstatt_member_workshop_notice_values( 'workshop_change', $refill_input ), 'A different member cannot retain submitted values for another owner profile' );
    wp_set_current_user( $b );
    $capture_workshop_notice = function ( $kind, $workshop_id, $payload, $valid_nonce = true ) use ( &$fixture_tokens, &$fixture_notices, $initial_globals ) {
        $submission_token = findewerkstatt_form_submission_token( 'member_workshop_' . $kind ); $fixture_tokens[] = $submission_token;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array( 'request_kind' => $kind, 'workshop_id' => (string) $workshop_id, 'fw_submission_token' => $submission_token, 'fw_member_nonce' => $valid_nonce ? wp_create_nonce( 'fw_member_workshop_' . $kind ) : 'invalid' ) + $payload;
        $redirect_location = '';
        $redirect_capture = function ( $location ) use ( &$redirect_location ) { $redirect_location = $location; throw new RuntimeException( 'membership-test-workshop-redirect' ); };
        add_filter( 'wp_redirect', $redirect_capture, PHP_INT_MIN );
        try { findewerkstatt_member_workshop_submit(); }
        catch ( RuntimeException $redirect ) { if ( 'membership-test-workshop-redirect' !== $redirect->getMessage() ) { throw $redirect; } }
        finally { remove_filter( 'wp_redirect', $redirect_capture, PHP_INT_MIN ); }
        $redirect_query = array(); parse_str( wp_parse_url( $redirect_location, PHP_URL_QUERY ) ?? '', $redirect_query );
        $notice_token = $redirect_query['fw_member_notice'] ?? '';
        if ( $notice_token ) { $fixture_notices[] = $notice_token; }
        $_GET = array( 'fw_member_notice' => $notice_token );
        $notice = findewerkstatt_member_notice();
        $_POST = $initial_globals['post']; $_GET = $initial_globals['get']; $_SERVER['REQUEST_METHOD'] = $initial_globals['server']['REQUEST_METHOD'] ?? 'GET';
        return array( 'notice' => $notice, 'url' => $redirect_location, 'token' => $submission_token );
    };
    $invalid_workshop_nonce = $capture_workshop_notice( 'claim', $claim_refill_target, array( 'evidence' => 'Authorized isolated proof with no permission to reuse an invalid nonce.' ), false );
    fw_membership_test_assert( 'error' === ( $invalid_workshop_nonce['notice']['type'] ?? '' ) && '' === ( $invalid_workshop_nonce['notice']['kind'] ?? '' ) && array() === ( $invalid_workshop_nonce['notice']['values'] ?? null ) && false === get_option( 'fw_form_lock_' . hash( 'sha256', $invalid_workshop_nonce['token'] ) ), 'An invalid workshop nonce uses a generic notice and preserves no profile context or submitted proof' );
    $invalid_claim_submission = $capture_workshop_notice( 'claim', $claim_refill_target, array( 'evidence' => 'Too short' ) );
    fw_membership_test_assert( 'error' === ( $invalid_claim_submission['notice']['type'] ?? '' ) && 'workshop_claim' === ( $invalid_claim_submission['notice']['kind'] ?? '' ) && $claim_refill_target === ( $invalid_claim_submission['notice']['values']['workshop_id'] ?? 0 ) && 'Too short' === ( $invalid_claim_submission['notice']['values']['evidence'] ?? '' ), 'The real claim handler returns validation errors to the selected claim form with its submitted evidence' );
    fw_membership_test_assert( false === strpos( $invalid_claim_submission['url'], 'Too' ) && false === strpos( $invalid_claim_submission['url'], (string) $claim_refill_target ), 'Claim redirect URLs contain only an opaque notice token rather than proof or a selected profile ID' );
    $invalid_change_submission = $capture_workshop_notice( 'change', $unowned, $refill_input );
    fw_membership_test_assert( 'error' === ( $invalid_change_submission['notice']['type'] ?? '' ) && 'workshop_change' === ( $invalid_change_submission['notice']['kind'] ?? '' ) && $unowned === ( $invalid_change_submission['notice']['values']['workshop_id'] ?? 0 ) && $refill_input['phone'] === ( $invalid_change_submission['notice']['values']['phone'] ?? '' ) && '+49301234567' === get_post_meta( $unowned, '_mechanic_phone', true ), 'The real change handler returns validation errors to the owned editor without changing public business data' );
    $valid_claim_submission = $capture_workshop_notice( 'claim', $claim_refill_target, array( 'evidence' => 'An authorized isolated ownership request with sufficient proof for manual operator review.' ) );
    fw_membership_test_assert( 'success' === ( $valid_claim_submission['notice']['type'] ?? '' ) && 'workshop_request' === ( $valid_claim_submission['notice']['kind'] ?? '' ) && array() === ( $valid_claim_submission['notice']['values'] ?? null ) && ! get_post_meta( $claim_refill_target, '_fw_owner_user_id', true ), 'A successful claim submission selects request history and keeps ownership pending operator review' );
    $valid_change_submission = $capture_workshop_notice( 'change', $unowned, $change_input );
    fw_membership_test_assert( 'success' === ( $valid_change_submission['notice']['type'] ?? '' ) && 'workshop_request' === ( $valid_change_submission['notice']['kind'] ?? '' ) && array() === ( $valid_change_submission['notice']['values'] ?? null ) && 'pending' === get_post_status( $unowned ), 'A successful change submission selects request history without altering the profile publication status' );
    wp_set_current_user( $c );
    $foreign_change_submission = $capture_workshop_notice( 'change', $unowned, $refill_input );
    fw_membership_test_assert( 'error' === ( $foreign_change_submission['notice']['type'] ?? '' ) && array() === ( $foreign_change_submission['notice']['values'] ?? null ) && $b === (int) get_post_meta( $unowned, '_fw_owner_user_id', true ), 'A valid nonce cannot make a foreign profile available for editing or preserve its submitted form values' );
    wp_set_current_user( $initial_user );
    $nonmember_workshop_submission = $capture_workshop_notice( 'claim', $claim_refill_target, array( 'evidence' => 'An administrator must still use the correct private manual moderation workflow.' ) );
    fw_membership_test_assert( 'error' === ( $nonmember_workshop_submission['notice']['type'] ?? '' ) && '' === ( $nonmember_workshop_submission['notice']['kind'] ?? '' ) && array() === ( $nonmember_workshop_submission['notice']['values'] ?? null ), 'A nonmember workshop handler request cannot obtain contextual form routing even with a valid nonce' );
    wp_set_current_user( $b );
    $_GET = $initial_globals['get'];

    wp_set_current_user( $a );
    $plan_request = findewerkstatt_member_plan_request( 'plus' );
    fw_membership_test_assert( ! is_wp_error( $plan_request ) && 'pending' === $plan_request['status'] && 'free' === findewerkstatt_member_plan(), 'Requesting a paid package leaves the free package active' );
    $same_plan = findewerkstatt_member_plan_request( 'plus' );
    fw_membership_test_assert( $same_plan['id'] === $plan_request['id'], 'Repeated requests for the same pending package preserve its request ID' );
    fw_membership_test_assert( is_wp_error( findewerkstatt_member_plan_request( 'administrator' ) ) && is_wp_error( findewerkstatt_member_review_plan( $a, $plan_request['id'], 'approve' ) ), 'A member cannot introduce a role-like package or approve a package themselves' );
    wp_set_current_user( $initial_user );
    fw_membership_test_assert( is_wp_error( findewerkstatt_member_review_plan( $a, wp_generate_uuid4(), 'approve' ) ) && 'free' === findewerkstatt_member_plan( $a ), 'A stale package approval cannot activate a package' );
    fw_membership_test_assert( true === findewerkstatt_member_review_plan( $a, $plan_request['id'], 'approve' ) && 'plus' === findewerkstatt_member_plan( $a ) && get_userdata( $a )->roles === array( 'fw_workshop_member' ), 'Administrator package approval changes commercial entitlement without granting WordPress privileges' );
    fw_membership_test_assert( is_wp_error( findewerkstatt_member_review_plan( $a, $plan_request['id'], 'approve' ) ), 'A processed package request cannot be replayed' );
    wp_set_current_user( $a );
    $obsolete = findewerkstatt_member_plan_request( 'professional' );
    $replacement = findewerkstatt_member_plan_request( 'free' );
    fw_membership_test_assert( ! is_wp_error( $obsolete ) && ! is_wp_error( $replacement ) && $obsolete['id'] !== $replacement['id'], 'Changing a pending package creates a new identifiable request' );
    wp_set_current_user( $initial_user );
    fw_membership_test_assert( is_wp_error( findewerkstatt_member_review_plan( $a, $obsolete['id'], 'approve' ) ) && 'plus' === findewerkstatt_member_plan( $a ), 'A replaced request cannot activate an obsolete package' );
    fw_membership_test_assert( true === findewerkstatt_member_review_plan( $a, $replacement['id'], 'reject' ) && 'plus' === findewerkstatt_member_plan( $a ) && 'rejected' === findewerkstatt_member_pending_request( $a )['status'], 'Rejection retains the active package and records the decision' );

    wp_set_current_user( $c );
    $lock = findewerkstatt_member_acquire_lock( $c, 'workshop' );
    fw_membership_test_assert( ! is_wp_error( $lock ) && is_wp_error( findewerkstatt_member_acquire_lock( $c, 'workshop' ) ), 'A second operation cannot acquire an already held member lock' );
    $forged = $lock; $forged['value']['token'] = wp_generate_uuid4();
    findewerkstatt_member_release_lock( $forged );
    fw_membership_test_assert( get_option( $lock['key'] ) === $lock['value'], 'A mismatched lock token cannot release the holder lock' );
    $expired = $lock['value']; $expired['time'] = time() - 3 * MINUTE_IN_SECONDS;
    update_option( $lock['key'], $expired, false );
    $new_lock = findewerkstatt_member_acquire_lock( $c, 'workshop' );
    fw_membership_test_assert( ! is_wp_error( $new_lock ) && $new_lock['value']['token'] !== $lock['value']['token'], 'An expired lock can be recovered with a new holder token' );
    findewerkstatt_member_release_lock( $lock );
    fw_membership_test_assert( get_option( $new_lock['key'] ) === $new_lock['value'], 'The expired holder cannot delete its replacement lock' );
    findewerkstatt_member_release_lock( $new_lock );
    fw_membership_test_assert( false === get_option( $new_lock['key'] ), 'The current holder can release its lock' );

    $location = findewerkstatt_registration_location( 'München', 'bayern' );
    fw_membership_test_assert( ! is_wp_error( $location ) && ! empty( $location['term_id'] ), 'Registration tests reuse an existing municipality and create no geographic terms' );
    $registration_values = array( 'company_name' => $post_prefix . ' registration', 'contact_name' => 'Membership Test C', 'email' => get_userdata( $c )->user_email, 'description' => 'An isolated pending registration.', 'services' => array(), 'brands' => array(), 'public_email' => false, 'street' => 'Teststraße 1', 'plz' => '80331', 'is_master' => false, 'is_24h' => false );
    $fingerprint = findewerkstatt_registration_fingerprint( $registration_values, $location );
    $registration_lock = findewerkstatt_member_acquire_lock( $c, 'workshop' );
    if ( is_wp_error( $registration_lock ) ) { throw new RuntimeException( 'Could not acquire fixture registration lock.' ); }
    try {
        $registration = findewerkstatt_registration_create_pending( $registration_values, $location, array(), array(), '+49301234567', '', '', $fingerprint, $fingerprint, $c );
        fw_membership_test_assert( ! is_wp_error( $registration ) && ! $registration['duplicate'], 'An eligible member can create a pending workshop within their quota' );
        $registration_id = $registration['post_id'];
        fw_membership_test_assert( 'pending' === get_post_status( $registration_id ) && $c === (int) get_post_meta( $registration_id, '_fw_owner_user_id', true ) && findewerkstatt_membership_operator_id() === (int) get_post_field( 'post_author', $registration_id ) && 'no' === get_post_meta( $registration_id, '_mechanic_email_public', true ), 'New workshop ownership is private metadata and the public author is the operator' );
        fw_membership_test_assert( 'free' === get_post_meta( $registration_id, '_fw_registration_plan', true ) && ! metadata_exists( 'user', $c, '_fw_member_plan_request' ), 'A legacy listing submission defaults to the active package and creates no package request' );
        $same_registration = findewerkstatt_registration_create_pending( $registration_values, $location, array(), array(), '+49301234567', '', '', $fingerprint, $fingerprint, $c );
        fw_membership_test_assert( ! is_wp_error( $same_registration ) && $same_registration['duplicate'] && $registration_id === $same_registration['post_id'], 'A repeated pending registration returns the existing record even at the profile quota' );
        $duplicate_values = $registration_values; $duplicate_values['selected_plan'] = 'professional';
        $changed_duplicate = findewerkstatt_registration_create_pending( $duplicate_values, $location, array(), array(), '+49301234567', '', '', $fingerprint, $fingerprint, $c );
        fw_membership_test_assert( ! is_wp_error( $changed_duplicate ) && $changed_duplicate['duplicate'] && 'free' === get_post_meta( $registration_id, '_fw_registration_plan', true ) && ! metadata_exists( 'user', $c, '_fw_member_plan_request' ), 'A duplicate listing cannot replace the saved package or introduce a later package request' );
        foreach ( array( 'administrator', array( 'plus' ) ) as $invalid_plan ) {
            $invalid_values = $registration_values; $invalid_values['selected_plan'] = $invalid_plan;
            $invalid_listing = findewerkstatt_registration_create_pending( $invalid_values, $location, array(), array(), '+49301234567', '', '', $fingerprint, $fingerprint, $c );
            fw_membership_test_assert( is_wp_error( $invalid_listing ) && 'registration_plan' === $invalid_listing->get_error_code() && ! metadata_exists( 'user', $c, '_fw_member_plan_request' ), 'Malformed or unknown package input is rejected before saving a listing or package request' );
        }
    } finally { findewerkstatt_member_release_lock( $registration_lock ); }

    // Exercise real listing handlers and their receipt messages with isolated member fixtures.
    $capture_form_notice = function ( $handler, $input, $member_notice = false ) use ( &$fixture_notices ) {
        $_POST = wp_slash( $input ); $_SERVER['REQUEST_METHOD'] = 'POST';
        $redirect_location = '';
        $redirect_capture = function ( $url ) use ( &$redirect_location ) { $redirect_location = $url; throw new RuntimeException( 'membership-package-test-redirect' ); };
        add_filter( 'wp_redirect', $redirect_capture, PHP_INT_MIN );
        try { $handler(); }
        catch ( RuntimeException $redirect ) { if ( 'membership-package-test-redirect' !== $redirect->getMessage() ) { throw $redirect; } }
        finally { remove_filter( 'wp_redirect', $redirect_capture, PHP_INT_MIN ); }
        $query = array(); parse_str( wp_parse_url( $redirect_location, PHP_URL_QUERY ) ?? '', $query );
        $key = $member_notice ? 'fw_member_notice' : 'fw_notice';
        $token = $query[ $key ] ?? '';
        if ( $member_notice ) { $fixture_notices[] = $token; }
        $transient = ( $member_notice ? 'fw_member_notice_' : 'fw_form_notice_' ) . $token;
        $notice = get_transient( $transient ); delete_transient( $transient );
        return is_array( $notice ) ? $notice : array();
    };
    $listing_base = $registration_values + array( 'phone' => '030 1234567', 'whatsapp' => '', 'website' => '', 'city' => 'München', 'bundesland' => 'bayern', 'privacy' => '1' );
    $d = $members['d']; wp_set_current_user( $d );
    foreach ( array( 'unknown', array( 'plus' ), '' ) as $invalid_plan ) {
        $token = findewerkstatt_form_submission_token( 'registration' ); $fixture_tokens[] = $token;
        $input = $listing_base; $input['selected_plan'] = $invalid_plan; $input['fw_submission_token'] = $token; $input['fw_register_nonce'] = wp_create_nonce( 'fw_register_workshop' );
        $notice = $capture_form_notice( 'findewerkstatt_registration_submit', $input );
        fw_membership_test_assert( 'error' === ( $notice['type'] ?? '' ) && ! ( $notice['received'] ?? false ) && array() === findewerkstatt_member_owned_workshops() && ! metadata_exists( 'user', $d, '_fw_member_plan_request' ), 'The listing POST handler rejects an invalid selected package without saving or requesting one' );
    }
    $input = $listing_base; $input['company_name'] = $post_prefix . ' selected-plus'; $input['email'] = get_userdata( $d )->user_email; $input['selected_plan'] = 'plus';
    $token = findewerkstatt_form_submission_token( 'registration' ); $fixture_tokens[] = $token;
    $input['fw_submission_token'] = $token; $input['fw_register_nonce'] = wp_create_nonce( 'fw_register_workshop' );
    $notice = $capture_form_notice( 'findewerkstatt_registration_submit', $input );
    $d_owned = findewerkstatt_member_owned_workshops(); $d_listing = $d_owned ? $d_owned[0]->ID : 0;
    $d_request = findewerkstatt_member_pending_request();
    fw_membership_test_assert( 'success' === ( $notice['type'] ?? '' ) && ( $notice['received'] ?? false ) && false !== strpos( $notice['message'] ?? '', 'Bis zur Freigabe' ) && 'pending' === get_post_status( $d_listing ) && 'plus' === get_post_meta( $d_listing, '_fw_registration_plan', true ) && 'plus' === ( $d_request['plan'] ?? '' ) && 'pending' === ( $d_request['status'] ?? '' ), 'Selecting Plus on a valid new listing saves the private selection and returns a truthful manual-request receipt' );
    fw_membership_test_assert( 'free' === findewerkstatt_member_plan() && ! findewerkstatt_member_can_add_workshop() && get_userdata( $d )->roles === array( 'fw_workshop_member' ) && ! current_user_can( 'publish_posts' ), 'A listing package selection cannot activate the requested package, extend quota or grant capabilities' );
    $duplicate = $input; $duplicate['selected_plan'] = 'professional';
    $notice = $capture_form_notice( 'findewerkstatt_registration_submit', $duplicate );
    fw_membership_test_assert( ( $notice['received'] ?? false ) && $d_request['id'] === findewerkstatt_member_pending_request()['id'] && 'plus' === get_post_meta( $d_listing, '_fw_registration_plan', true ), 'Replaying a completed listing token with a changed package preserves the original request and selection' );
    $token = findewerkstatt_form_submission_token( 'registration' ); $fixture_tokens[] = $token; $duplicate['fw_submission_token'] = $token;
    $notice = $capture_form_notice( 'findewerkstatt_registration_submit', $duplicate );
    fw_membership_test_assert( ( $notice['received'] ?? false ) && $d_request['id'] === findewerkstatt_member_pending_request()['id'] && count( findewerkstatt_member_owned_workshops() ) === 1, 'A fresh-token duplicate cannot replace the pending package request or create an extra profile' );
    $token = findewerkstatt_form_submission_token( 'registration' ); $fixture_tokens[] = $token; $blocked = $duplicate; $blocked['fw_submission_token'] = $token; $blocked['company_name'] .= ' extra';
    $notice = $capture_form_notice( 'findewerkstatt_registration_submit', $blocked );
    fw_membership_test_assert( 'error' === ( $notice['type'] ?? '' ) && ! ( $notice['received'] ?? false ) && $d_request['id'] === findewerkstatt_member_pending_request()['id'] && count( findewerkstatt_member_owned_workshops() ) === 1, 'An exhausted active quota prevents a different listing from creating or replacing a package request' );

    // The same business from an unrelated member is only acknowledged, never assigned or requested.
    $e = $members['e']; wp_set_current_user( $e );
    $token = findewerkstatt_form_submission_token( 'registration' ); $fixture_tokens[] = $token; $foreign = $duplicate; $foreign['fw_submission_token'] = $token; $foreign['fw_register_nonce'] = wp_create_nonce( 'fw_register_workshop' );
    $notice = $capture_form_notice( 'findewerkstatt_registration_submit', $foreign );
    fw_membership_test_assert( ( $notice['received'] ?? false ) && array() === findewerkstatt_member_owned_workshops() && ! metadata_exists( 'user', $e, '_fw_member_plan_request' ) && $d === (int) get_post_meta( $d_listing, '_fw_owner_user_id', true ), 'An unrelated duplicate cannot grant ownership or make a package request for either account' );

    // A saved listing remains received even if its optional package request lock is busy.
    $plan_lock = findewerkstatt_member_acquire_lock( $e, 'plan' );
    if ( is_wp_error( $plan_lock ) ) { throw new RuntimeException( 'Could not acquire fixture package lock.' ); }
    try {
        $token = findewerkstatt_form_submission_token( 'registration' ); $fixture_tokens[] = $token;
        $warning_input = $input; $warning_input['company_name'] = $post_prefix . ' package-busy'; $warning_input['email'] = get_userdata( $e )->user_email; $warning_input['selected_plan'] = 'professional'; $warning_input['fw_submission_token'] = $token; $warning_input['fw_register_nonce'] = wp_create_nonce( 'fw_register_workshop' );
        $notice = $capture_form_notice( 'findewerkstatt_registration_submit', $warning_input );
        $e_owned = findewerkstatt_member_owned_workshops();
        fw_membership_test_assert( 'warning' === ( $notice['type'] ?? '' ) && ( $notice['received'] ?? false ) && false !== strpos( $notice['message'] ?? '', 'Betriebseintrag bleibt erhalten' ) && count( $e_owned ) === 1 && 'professional' === get_post_meta( $e_owned[0]->ID, '_fw_registration_plan', true ) && ! metadata_exists( 'user', $e, '_fw_member_plan_request' ) && 'free' === findewerkstatt_member_plan(), 'A failed package request after saving produces a received warning while preserving the owned pending profile and active package' );
    } finally { findewerkstatt_member_release_lock( $plan_lock ); }

    // No package choice, including hostile legacy fields, affects account creation.
    foreach ( array( 'missing', 'plus', 'administrator', 'array' ) as $signup_case ) {
        wp_set_current_user( 0 ); $_COOKIE['fw_member_guest'] = bin2hex( random_bytes( 32 ) );
        $token = findewerkstatt_form_submission_token( 'member_signup' ); $fixture_tokens[] = $token;
        $signup_input = array( 'name' => 'Membership Signup Fixture', 'email' => 'fw-member-' . $run_id . '-signup-' . $signup_case . '@example.invalid', 'password' => 'Isolated-Membership-Test-Password-12', 'password_confirm' => 'Isolated-Membership-Test-Password-12', 'privacy' => '1', 'terms' => '1', 'fw_submission_token' => $token, 'fw_member_nonce' => wp_create_nonce( 'fw_member_signup' ) );
        if ( 'missing' !== $signup_case ) { $signup_input['selected_plan'] = 'array' === $signup_case ? array( 'professional' ) : $signup_case; }
        $notice = $capture_form_notice( 'findewerkstatt_member_signup_submit', $signup_input, true );
        $signup_user = get_user_by( 'email', $signup_input['email'] );
        fw_membership_test_assert( $signup_user && 'success' === ( $notice['type'] ?? '' ) && 'free' === findewerkstatt_member_plan( $signup_user->ID ) && ! metadata_exists( 'user', $signup_user->ID, '_fw_member_plan_request' ) && $signup_user->roles === array( 'fw_workshop_member' ), 'Account signup ignores the ' . $signup_case . ' legacy package field and creates only a free account without a package request' );
    }

    $notice_token = wp_generate_password( 32, false, false ); $fixture_notices[] = $notice_token;
    set_transient( 'fw_member_notice_' . $notice_token, array( 'type' => 'success', 'message' => 'Private fixture notice', 'user_id' => $a, 'values' => array() ), MINUTE_IN_SECONDS );
    $_GET = array( 'fw_member_notice' => $notice_token );
    wp_set_current_user( $b );
    fw_membership_test_assert( array() === findewerkstatt_member_notice() && false !== get_transient( 'fw_member_notice_' . $notice_token ), 'A notice cannot be consumed by another account' );
    wp_set_current_user( $a );
    fw_membership_test_assert( 'Private fixture notice' === ( findewerkstatt_member_notice()['message'] ?? '' ) && array() === findewerkstatt_member_notice(), 'A private account notice is delivered to its owner once' );
    $_GET = array( 'fw_member_notice' => array( $notice_token ) );
    fw_membership_test_assert( array() === findewerkstatt_member_notice(), 'Array-shaped notice tokens are rejected safely' );

    wp_set_current_user( 0 );
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_COOKIE['fw_member_guest'] = bin2hex( random_bytes( 32 ) );
    $guest_cookie = $_COOKIE['fw_member_guest'];
    $login_nonce = wp_create_nonce( 'fw_member_login' );
    $_POST = array( 'fw_member_nonce' => $login_nonce );
    fw_membership_test_assert( findewerkstatt_member_valid_post( 'fw_member_login' ), 'An anonymous login nonce is valid in its issuing browser session' );
    $_COOKIE['fw_member_guest'] = bin2hex( random_bytes( 32 ) );
    fw_membership_test_assert( ! findewerkstatt_member_valid_post( 'fw_member_login' ), 'A login nonce from another anonymous browser session is rejected' );
    unset( $_COOKIE['fw_member_guest'] );
    fw_membership_test_assert( ! findewerkstatt_member_valid_post( 'fw_member_login' ), 'A login nonce without its browser cookie is rejected' );
    $_POST['fw_member_nonce'] = wp_create_nonce( 'fw_member_login' );
    fw_membership_test_assert( ! findewerkstatt_member_valid_post( 'fw_member_login' ), 'Missing guest cookies are rejected even if a nonce was created without a browser session' );
    $_COOKIE['fw_member_guest'] = $guest_cookie;
    $signup_nonce = wp_create_nonce( 'fw_member_signup' );
    $_POST = array( 'fw_member_nonce' => $signup_nonce );
    fw_membership_test_assert( findewerkstatt_member_valid_post( 'fw_member_signup' ) && ! findewerkstatt_member_valid_post( 'fw_member_login' ), 'Guest signup nonces remain action-specific' );
    fw_membership_test_assert( 4 === $mail_calls, 'Signup sends exactly one email verification message per created account' );
} catch ( Throwable $error ) { $failure = $error->getMessage(); }
finally {
    $cleanup();
    remove_action( 'wp_insert_post', $post_capture, 10 );
    remove_action( 'user_register', $user_capture, 10 );
    remove_filter( 'pre_wp_mail', $mail_filter, PHP_INT_MAX );
    $_POST = $initial_globals['post']; $_GET = $initial_globals['get']; $_COOKIE = $initial_globals['cookie']; $_SERVER = $initial_globals['server'];
    wp_set_current_user( $initial_user );
}
$remaining = array();
foreach ( array_unique( $fixture_posts ) as $id ) { if ( get_post( $id ) ) { $remaining[] = 'post'; } }
foreach ( array_unique( $fixture_users ) as $id ) { if ( get_userdata( $id ) ) { $remaining[] = 'user'; } }
if ( $remaining && ! $failure ) { $failure = 'Fixture cleanup was incomplete.'; }
echo wp_json_encode( array( 'passed' => count( $fw_membership_test_checks ), 'checks' => $fw_membership_test_checks, 'fixture_cleanup' => $remaining ? 'incomplete' : 'complete', 'external_mail_sent' => 0, 'error' => $failure ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . "\n";
if ( $failure ) { exit( 1 ); }
