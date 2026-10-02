<?php
/** Presentation helpers for the authenticated workshop dashboard. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function findewerkstatt_member_dashboard_icon( $name ) {
    $paths = array(
        'business' => '<path d="M3 10h18M5 10v10h14V10M4 4h16l1 6H3l1-6ZM9 20v-6h6v6"/>',
        'claim' => '<path d="M12 3v12m-4-4 4 4 4-4M5 17v4h14v-4"/>',
        'requests' => '<path d="M5 4h14v17H5zM8 8h8M8 12h8M8 16h5"/>',
        'package' => '<path d="m12 3 9 5v9l-9 5-9-5V8l9-5ZM3 8l9 5 9-5M12 13v9M8 5l9 5"/>',
        'settings' => '<path d="M4 6h16M4 12h16M4 18h16M8 3v6M16 9v6M10 15v6"/>',
        'help' => '<path d="M4 13v-1a8 8 0 0 1 16 0v1M4 12H2v7h4v-7H4Zm16 0h2v7h-4v-7h2ZM18 19v2h-6"/>',
        'check' => '<path d="m6 12 4 4 8-9"/><circle cx="12" cy="12" r="10"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'eye' => '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
        'edit' => '<path d="m16 3 5 5-11 11-7 2 2-7L16 3ZM13 6l5 5"/>',
        'pause' => '<path d="M8 5v14M16 5v14"/>',
        'plus' => '<path d="M12 4v16M4 12h16"/>',
        'arrow' => '<path d="M4 12h16m-6-6 6 6-6 6"/>',
        'logout' => '<path d="M9 4H4v16h5M9 12h12m-5-5 5 5-5 5"/>',
        'admin' => '<path d="M12 3 3 7v6c0 5 9 9 9 9s9-4 9-9V7l-9-4Z"/><path d="m8 12 3 3 5-6"/>',
    );
    return isset( $paths[ $name ] ) ? '<svg class="fw-dashboard-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths[ $name ] . '</svg>' : '';
}

function findewerkstatt_member_dashboard_url( $section, $args = array() ) {
    return add_query_arg( array_merge( array( 'bereich' => $section ), $args ), findewerkstatt_page_url( 'mein-konto' ) );
}

function findewerkstatt_member_dashboard_view( $notice_kind = '' ) {
    $notice_sections = array( 'workshop_claim' => 'uebernehmen', 'workshop_change' => 'bearbeiten', 'workshop_request' => 'anfragen' );
    if ( isset( $notice_sections[ $notice_kind ] ) ) { return $notice_sections[ $notice_kind ]; }
    $allowed = array( 'betriebe', 'uebernehmen', 'anfragen', 'paket', 'einstellungen', 'hilfe', 'bearbeiten' );
    $section = is_string( $_GET['bereich'] ?? null ) ? sanitize_key( wp_unslash( $_GET['bereich'] ) ) : '';
    if ( isset( $_GET['werkstatt'] ) && is_scalar( $_GET['werkstatt'] ) && absint( $_GET['werkstatt'] ) ) { return 'bearbeiten'; }
    if ( isset( $_GET['uebernehmen'] ) && is_scalar( $_GET['uebernehmen'] ) && absint( $_GET['uebernehmen'] ) ) { return 'uebernehmen'; }
    if ( in_array( $section, $allowed, true ) ) { return $section; }
    if ( isset( $_GET['plan'] ) && is_string( $_GET['plan'] ) && isset( findewerkstatt_membership_plans()[ $_GET['plan'] ] ) ) { return 'paket'; }
    if ( 'account' === $notice_kind ) { return 'einstellungen'; }
    if ( 'plan' === $notice_kind ) { return 'paket'; }
    return 'betriebe';
}

function findewerkstatt_member_dashboard_open_requests() {
    if ( ! get_current_user_id() ) { return 0; }
    $query = new WP_Query( array( 'post_type' => 'fw_member_request', 'post_status' => 'private', 'posts_per_page' => 1, 'fields' => 'ids', 'no_found_rows' => false, 'meta_query' => array(
        array( 'key' => '_fw_request_user', 'value' => get_current_user_id(), 'type' => 'NUMERIC' ),
        array( 'key' => '_fw_request_state', 'value' => 'pending' ),
    ) ) );
    $plan = findewerkstatt_member_pending_request();
    return (int) $query->found_posts + ( 'pending' === ( $plan['status'] ?? '' ) ? 1 : 0 );
}

function findewerkstatt_member_dashboard_status( $workshop ) {
    $labels = array( 'publish' => findewerkstatt_t( 'Veröffentlicht' ), 'pending' => findewerkstatt_t( 'In Prüfung' ), 'draft' => findewerkstatt_t( 'Entwurf' ), 'private' => findewerkstatt_t( 'Nicht öffentlich' ), 'future' => 'Geplant' );
    if ( 'draft' === $workshop->post_status && 'yes' === get_post_meta( $workshop->ID, '_fw_member_paused', true ) ) { return findewerkstatt_t( 'Pausiert' ); }
    return $labels[ $workshop->post_status ] ?? findewerkstatt_t( 'In Prüfung' );
}

function findewerkstatt_member_dashboard_workshop_card( $workshop ) {
    if ( ! findewerkstatt_member_owns_workshop( $workshop->ID ) ) { return; }
    $title = get_the_title( $workshop->ID );
    $image = get_the_post_thumbnail( $workshop->ID, 'thumbnail', array( 'loading' => 'lazy', 'alt' => findewerkstatt_t( 'Bild zum Betriebsprofil' ) ) );
    $city = findewerkstatt_get_city_term( $workshop->ID );
    $status = findewerkstatt_member_dashboard_status( $workshop );
    $change_url = findewerkstatt_member_dashboard_url( 'bearbeiten', array( 'werkstatt' => $workshop->ID ) ) . '#betrieb-bearbeiten';
    ?><li class="fw-dashboard-business-card">
        <div class="fw-dashboard-business-photo"><?php if ( $image ) { echo $image; } else { ?><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/placeholder-mechanic.webp' ); ?>" width="450" height="300" alt="<?php echo esc_attr( findewerkstatt_t( 'Symbolbild: Arbeiten an einem Motor' ) ); ?>" loading="lazy"><span><?php echo esc_html( findewerkstatt_t( 'Symbolbild' ) ); ?></span><?php } ?></div>
        <div class="fw-dashboard-business-info"><h3><?php echo esc_html( $title ); ?></h3>
            <?php if ( $city ) { get_template_part( 'template-parts/workshop-location', null, array( 'context' => findewerkstatt_location_context( $city ) ) ); } ?>
            <p class="fw-dashboard-business-date"><?php echo esc_html( findewerkstatt_t( 'Erstellt am' ) ); ?> <?php echo esc_html( get_the_date( 'd.m.Y', $workshop ) ); ?></p>
            <span class="fw-dashboard-status fw-dashboard-status-<?php echo esc_attr( $workshop->post_status ); ?>"><?php echo findewerkstatt_member_dashboard_icon( 'publish' === $workshop->post_status ? 'check' : 'clock' ); ?><?php echo esc_html( $status ); ?></span>
        </div>
        <div class="fw-dashboard-business-actions">
            <?php if ( 'publish' === $workshop->post_status ) : ?><a class="fw-btn fw-btn-outline fw-btn-sm" href="<?php echo esc_url( get_permalink( $workshop->ID ) ); ?>"><?php echo findewerkstatt_member_dashboard_icon( 'eye' ); ?><?php echo esc_html( findewerkstatt_t( 'Profil ansehen' ) ); ?></a><?php endif; ?>
            <a class="fw-btn fw-btn-sm fw-dashboard-edit" href="<?php echo esc_url( $change_url ); ?>"><?php echo findewerkstatt_member_dashboard_icon( 'edit' ); ?><?php echo esc_html( findewerkstatt_t( 'Änderung einreichen' ) ); ?></a>
            <?php if ( 'publish' === $workshop->post_status || ( 'draft' === $workshop->post_status && 'yes' === get_post_meta( $workshop->ID, '_fw_member_paused', true ) ) ) :
                $operation = 'publish' === $workshop->post_status ? 'pause' : 'submit'; ?>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><?php findewerkstatt_language_field(); ?>
                <input type="hidden" name="action" value="fw_member_workshop_visibility"><input type="hidden" name="workshop_id" value="<?php echo (int) $workshop->ID; ?>"><input type="hidden" name="operation" value="<?php echo esc_attr( $operation ); ?>">
                <?php wp_nonce_field( 'fw_member_visibility_' . $workshop->ID, 'fw_member_nonce' ); ?>
                <button type="submit" class="fw-btn fw-btn-sm <?php echo 'pause' === $operation ? 'fw-dashboard-pause' : 'fw-btn-outline'; ?>"><?php echo findewerkstatt_member_dashboard_icon( 'pause' === $operation ? 'pause' : 'arrow' ); ?><?php echo 'pause' === $operation ? findewerkstatt_t( 'Anzeige pausieren' ) : findewerkstatt_t( 'Erneut prüfen lassen' ); ?></button>
            </form><?php endif; ?>
        </div>
    </li><?php
}
