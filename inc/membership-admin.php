<?php
/** Operator approval and configurable membership packages. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function findewerkstatt_membership_install() {
    if ( ! current_user_can( 'manage_options' ) ) { return new WP_Error( 'permission', 'Administratorrechte erforderlich.' ); }
    if ( '3.0.0' === get_option( 'findewerkstatt_membership_version' ) ) { return true; }
    $pages = array( 'pakete' => 'Pakete für Werkstätten', 'mein-konto' => 'Mein Konto' );
    foreach ( $pages as $slug => $title ) {
        $page = get_page_by_path( $slug );
        if ( ! $page ) {
            $id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $title ), true );
            if ( is_wp_error( $id ) ) { return $id; }
        } else {
            $id = $page->ID;
            if ( ! metadata_exists( 'post', $id, '_fw_membership_original_template' ) ) {
                update_post_meta( $id, '_fw_membership_original_template', get_page_template_slug( $id ) );
            }
        }
        update_post_meta( $id, '_wp_page_template', 'page-' . $slug . '.php' );
    }
    update_option( 'findewerkstatt_membership_version', '3.0.0', false );
    return true;
}
add_action( 'admin_init', 'findewerkstatt_membership_install', 30 );
add_action( 'after_switch_theme', 'findewerkstatt_membership_install', 30 );

function findewerkstatt_membership_sanitize_settings( $input ) {
    $plans = findewerkstatt_membership_plans();
    $rows = is_array( $input ) && is_array( $input['plans'] ?? null ) ? $input['plans'] : array();
    foreach ( $plans as $id => &$plan ) {
        $row = is_array( $rows[ $id ] ?? null ) ? $rows[ $id ] : array();
        $name = is_string( $row['name'] ?? null ) ? sanitize_text_field( $row['name'] ) : '';
        if ( '' !== $name && findewerkstatt_form_length( $name ) <= 60 ) { $plan['name'] = $name; }
        else { add_settings_error( 'findewerkstatt_membership_settings', 'name-' . $id, 'Bitte verwenden Sie einen Paketnamen mit 1 bis 60 Zeichen.' ); }
        $limit = is_scalar( $row['listing_limit'] ?? null ) ? (string) $row['listing_limit'] : '';
        if ( preg_match( '/^\d{1,3}$/', $limit ) && (int) $limit >= 1 && (int) $limit <= 100 ) { $plan['listing_limit'] = (int) $limit; }
        else { add_settings_error( 'findewerkstatt_membership_settings', 'limit-' . $id, 'Ein Paket kann 1 bis 100 Betriebsprofile enthalten.' ); }
        if ( 'free' === $id ) { $plan['monthly_price'] = '0'; continue; }
        $price = is_string( $row['monthly_price'] ?? null ) ? str_replace( ',', '.', trim( $row['monthly_price'] ) ) : '';
        if ( '' === $price || preg_match( '/^\d{1,5}(?:\.\d{1,2})?$/', $price ) ) { $plan['monthly_price'] = $price; }
        else { add_settings_error( 'findewerkstatt_membership_settings', 'price-' . $id, 'Bitte geben Sie einen gültigen Preis ein oder lassen Sie das Preisfeld leer.' ); }
    }
    unset( $plan );
    return array( 'plans' => $plans );
}
add_action( 'admin_init', function () {
    register_setting( 'findewerkstatt_memberships', 'findewerkstatt_membership_settings', array( 'type' => 'array', 'sanitize_callback' => 'findewerkstatt_membership_sanitize_settings' ) );
} );
add_action( 'admin_menu', function () {
    add_users_page( 'Mitgliedschaften und Pakete', 'Mitgliedschaften', 'manage_options', 'findewerkstatt-memberships', 'findewerkstatt_membership_admin_page' );
} );

/** Matching the request ID prevents stale buttons from approving a newer request. */
function findewerkstatt_member_review_plan( $user_id, $request_id, $decision ) {
    if ( ! current_user_can( 'manage_options' ) || ! in_array( $decision, array( 'approve', 'reject' ), true ) || ! is_string( $request_id ) ) {
        return new WP_Error( 'permission', 'Diese Freigabe ist nicht zulässig.' );
    }
    $user_id = absint( $user_id );
    if ( ! findewerkstatt_is_workshop_member( $user_id ) ) { return new WP_Error( 'member', 'Das Mitgliedskonto ist nicht verfügbar.' ); }
    $lock = findewerkstatt_member_acquire_lock( $user_id, 'plan' );
    if ( is_wp_error( $lock ) ) { return $lock; }
    try {
        wp_cache_delete( $user_id, 'user_meta' );
        $request = findewerkstatt_member_pending_request( $user_id );
        if ( 'pending' !== ( $request['status'] ?? '' ) || ! hash_equals( $request['id'] ?? '', $request_id ) ) {
            return new WP_Error( 'stale', 'Diese Anfrage wurde bereits bearbeitet oder durch eine neue Anfrage ersetzt.' );
        }
        if ( 'approve' === $decision ) {
            update_user_meta( $user_id, '_fw_member_plan', $request['plan'] );
            if ( $request['plan'] !== findewerkstatt_member_plan( $user_id ) ) { return new WP_Error( 'save', 'Das Paket konnte nicht gespeichert werden.' ); }
        }
        $request['status'] = 'approve' === $decision ? 'approved' : 'rejected';
        $request['reviewed_at'] = current_time( 'mysql', true );
        $request['reviewed_by'] = get_current_user_id();
        update_user_meta( $user_id, '_fw_member_plan_request', $request );
        return true;
    } finally { findewerkstatt_member_release_lock( $lock ); }
}
function findewerkstatt_member_review_plan_submit() {
    if ( ! current_user_can( 'manage_options' ) || 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) { wp_die( 'Keine Berechtigung.', '', array( 'response' => 403 ) ); }
    $user_id = absint( findewerkstatt_form_text( 'member_id', 20 ) );
    $request_id = findewerkstatt_form_text( 'request_id', 36 );
    check_admin_referer( 'fw_review_plan_' . $user_id . '_' . $request_id );
    $result = findewerkstatt_member_review_plan( $user_id, $request_id, findewerkstatt_form_text( 'decision', 10 ) );
    wp_safe_redirect( add_query_arg( 'fw_result', is_wp_error( $result ) ? 'error' : 'saved', admin_url( 'users.php?page=findewerkstatt-memberships' ) ), 303 );
    exit;
}
add_action( 'admin_post_fw_member_review_plan', 'findewerkstatt_member_review_plan_submit' );

function findewerkstatt_membership_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) { return; }
    $plans = findewerkstatt_membership_plans();
    $page_number = max( 1, min( 10000, absint( is_scalar( $_GET['paged'] ?? null ) ? $_GET['paged'] : 1 ) ) );
    $query = new WP_User_Query( array( 'role' => 'fw_workshop_member', 'number' => 25, 'offset' => ( $page_number - 1 ) * 25, 'orderby' => 'registered', 'order' => 'DESC', 'count_total' => true ) );
    $members = $query->get_results();
    $states = array( 'pending' => 'Wartet auf Prüfung', 'approved' => 'Freigegeben', 'rejected' => 'Abgelehnt', 'cancelled' => 'Zurückgezogen' );
    ?>
    <div class="wrap">
        <h1>Mitgliedschaften und Pakete</h1>
        <?php if ( 'saved' === ( $_GET['fw_result'] ?? '' ) ) : ?><div class="notice notice-success"><p>Die Paketanfrage wurde bearbeitet.</p></div><?php elseif ( 'error' === ( $_GET['fw_result'] ?? '' ) ) : ?><div class="notice notice-error"><p>Die Anfrage konnte nicht bearbeitet werden. Bitte prüfen Sie den aktuellen Stand und versuchen Sie es erneut.</p></div><?php endif; ?>
        <p>Neue Konten beginnen im kostenlosen Paket. Paketwechsel werden erst nach Ihrer Prüfung freigeschaltet. Klären Sie vor einer Freigabe die Konditionen mit dem Betrieb; hier wird keine Zahlung ausgelöst.</p>
        <p><a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=fw_member_request' ) ); ?>">Übernahme- und Änderungsanfragen prüfen</a> <a class="button" href="<?php echo esc_url( findewerkstatt_page_url( 'pakete' ) ); ?>">Paketübersicht ansehen</a></p>
        <h2>Mitglieder und Paketanfragen</h2>
        <table class="widefat striped"><thead><tr><th>Mitglied</th><th>Aktives Paket / Profile</th><th>Letzte Anfrage</th><th>Entscheidung</th></tr></thead><tbody>
        <?php foreach ( $members as $member ) : $active = findewerkstatt_member_plan( $member->ID ); $request = findewerkstatt_member_pending_request( $member->ID ); ?>
            <tr>
                <td><strong><?php echo esc_html( $member->display_name ); ?></strong><br><?php echo esc_html( $member->user_email ); ?></td>
                <td><?php echo esc_html( $plans[ $active ]['name'] ); ?><br><?php echo esc_html( count( findewerkstatt_member_owned_workshops( $member->ID ) ) . ' / ' . $plans[ $active ]['listing_limit'] ); ?></td>
                <td><?php if ( $request ) : ?><?php echo esc_html( $plans[ $request['plan'] ]['name'] ); ?><br><?php echo esc_html( $states[ $request['status'] ] ?? '' ); ?><?php else : ?>Keine Anfrage<?php endif; ?></td>
                <td><?php if ( 'pending' === ( $request['status'] ?? '' ) ) : ?>
                    <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
                        <input type="hidden" name="action" value="fw_member_review_plan"><input type="hidden" name="member_id" value="<?php echo esc_attr( $member->ID ); ?>"><input type="hidden" name="request_id" value="<?php echo esc_attr( $request['id'] ); ?>">
                        <?php wp_nonce_field( 'fw_review_plan_' . $member->ID . '_' . $request['id'] ); ?>
                        <button type="submit" class="button button-primary" name="decision" value="approve">Paket freigeben</button> <button type="submit" class="button" name="decision" value="reject">Ablehnen</button>
                    </form>
                <?php else : ?>–<?php endif; ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if ( ! $members ) : ?><tr><td colspan="4">Es sind noch keine Werkstattmitglieder registriert.</td></tr><?php endif; ?>
        </tbody></table>
        <?php $pagination = paginate_links( array( 'base' => add_query_arg( 'paged', '%#%', admin_url( 'users.php?page=findewerkstatt-memberships' ) ), 'format' => '', 'current' => $page_number, 'total' => max( 1, (int) ceil( $query->get_total() / 25 ) ) ) ); if ( $pagination ) { echo '<p>' . wp_kses_post( $pagination ) . '</p>'; } ?>
        <h2>Paketnamen und Profilgrenzen</h2>
        <p>Preise und Zahlungszeiträume werden später festgelegt. Bestehende Einträge bleiben bei einer kleineren Profilgrenze erhalten; weitere Profile sind erst möglich, wenn das Konto wieder unter der Grenze liegt.</p>
        <?php settings_errors( 'findewerkstatt_membership_settings' ); ?>
        <form method="post" action="options.php">
            <?php settings_fields( 'findewerkstatt_memberships' ); ?>
            <table class="form-table">
            <?php foreach ( $plans as $id => $plan ) : ?>
                <tr><th scope="row"><?php echo esc_html( $plan['name'] ); ?></th><td>
                    <p><label>Name <input class="regular-text" name="findewerkstatt_membership_settings[plans][<?php echo esc_attr( $id ); ?>][name]" value="<?php echo esc_attr( $plan['name'] ); ?>" maxlength="60" required></label></p>
                    <p><label>Maximale Betriebsprofile <input type="number" min="1" max="100" required name="findewerkstatt_membership_settings[plans][<?php echo esc_attr( $id ); ?>][listing_limit]" value="<?php echo esc_attr( $plan['listing_limit'] ); ?>"></label></p>
                    <p><?php echo 'free' === $id ? '0 € – kostenlos.' : 'Preis folgt – nur unverbindliche Anfragen.'; ?></p>
                </td></tr>
            <?php endforeach; ?>
            </table>
            <?php submit_button( 'Pakete speichern' ); ?>
        </form>
    </div>
    <?php
}
