<?php
/** Useful account access for operators and other WordPress accounts. */
if ( ! defined( 'ABSPATH' ) || ! is_user_logged_in() ) { return; }
$account = wp_get_current_user();
$is_operator = current_user_can( 'manage_options' );
$initial = function_exists( 'mb_strtoupper' ) ? mb_strtoupper( mb_substr( $account->display_name, 0, 1 ) ) : strtoupper( substr( $account->display_name, 0, 1 ) );
$links = array();
if ( $is_operator ) {
    $links = array(
        array( 'business', findewerkstatt_t( 'Werkstätten verwalten' ), admin_url( 'edit.php?post_type=mechanic' ), findewerkstatt_t( 'Betriebsangaben und Veröffentlichungen prüfen.' ) ),
        array( 'package', findewerkstatt_t( 'Mitgliedschaften' ), admin_url( 'users.php?page=findewerkstatt-memberships' ), findewerkstatt_t( 'Paketanfragen prüfen und Paketgrenzen verwalten.' ) ),
        array( 'requests', findewerkstatt_t( 'Eintragsanfragen' ), admin_url( 'edit.php?post_type=fw_member_request' ), findewerkstatt_t( 'Übernahmen und Änderungen von Betriebsprofilen prüfen.' ) ),
        array( 'settings', findewerkstatt_t( 'WordPress-Verwaltung' ), admin_url(), findewerkstatt_t( 'Weitere Einstellungen und Inhalte verwalten.' ) ),
    );
    $counts = wp_count_posts( 'mechanic' );
    $member_query = new WP_User_Query( array( 'role' => 'fw_workshop_member', 'number' => 1, 'fields' => 'ID', 'count_total' => true ) );
    $request_query = new WP_Query( array( 'post_type' => 'fw_member_request', 'post_status' => 'private', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_query' => array( array( 'key' => '_fw_request_state', 'value' => 'pending' ) ) ) );
}
?>
<div class="fw-dashboard-hero">
    <div class="fw-dashboard-identity"><span class="fw-dashboard-avatar" aria-hidden="true"><?php echo esc_html( $initial ); ?></span><div>
        <h1><?php echo $is_operator ? 'Verzeichnisverwaltung' : findewerkstatt_t( 'Mein Konto' ); ?></h1>
        <div class="fw-dashboard-name-row"><p class="fw-dashboard-name"><?php echo esc_html( $account->display_name ); ?></p><?php if ( $is_operator ) : ?><span class="fw-dashboard-plan-badge">Administration</span><?php endif; ?></div>
        <div class="fw-dashboard-identity-details"><span><?php echo esc_html( $account->user_email ); ?></span></div>
    </div></div>
    <div class="fw-dashboard-hero-actions"><form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><?php findewerkstatt_language_field(); ?><input type="hidden" name="action" value="fw_member_logout"><?php wp_nonce_field( 'fw_member_logout', 'fw_member_nonce' ); ?><button class="fw-btn fw-dashboard-logout" type="submit"><?php echo findewerkstatt_member_dashboard_icon( 'logout' ); ?><?php echo esc_html( findewerkstatt_t( 'Abmelden' ) ); ?></button></form></div>
</div>
<?php if ( $is_operator ) : ?>
<div class="fw-dashboard-layout">
    <aside class="fw-dashboard-sidebar"><nav aria-label="<?php echo esc_attr( findewerkstatt_t( 'Verwaltung' ) ); ?>"><p class="fw-dashboard-menu-label"><?php echo esc_html( findewerkstatt_t( 'Verzeichnis und Mitglieder' ) ); ?></p>
        <?php foreach ( $links as $link ) : ?><a class="fw-dashboard-menu-link" href="<?php echo esc_url( $link[2] ); ?>"><?php echo findewerkstatt_member_dashboard_icon( $link[0] ); ?><span><?php echo esc_html( $link[1] ); ?></span></a><?php endforeach; ?>
        <p class="fw-dashboard-menu-label">Konto</p><a class="fw-dashboard-menu-link" href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>"><?php echo findewerkstatt_member_dashboard_icon( 'settings' ); ?><span><?php echo esc_html( findewerkstatt_t( 'Kontoeinstellungen' ) ); ?></span></a>
    </nav></aside>
    <div class="fw-dashboard-content">
        <div class="fw-dashboard-stats" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Verzeichnis im Überblick' ) ); ?>">
            <?php foreach ( array( array( 'check', findewerkstatt_t( 'Veröffentlichte Betriebe' ), (int) $counts->publish, 'green' ), array( 'clock', findewerkstatt_t( 'Betriebe in Prüfung' ), (int) $counts->pending, 'blue' ), array( 'business', 'Werkstattkonten', (int) $member_query->get_total(), 'orange' ), array( 'requests', findewerkstatt_t( 'Offene Eintragsanfragen' ), (int) $request_query->found_posts, 'purple' ) ) as $stat ) : ?>
            <div class="fw-dashboard-stat"><span class="fw-dashboard-stat-icon fw-dashboard-stat-<?php echo esc_attr( $stat[3] ); ?>"><?php echo findewerkstatt_member_dashboard_icon( $stat[0] ); ?></span><div><span class="fw-dashboard-stat-label"><?php echo esc_html( $stat[1] ); ?></span><strong><?php echo esc_html( number_format_i18n( $stat[2] ) ); ?></strong></div></div><?php endforeach; ?>
        </div>
        <section class="fw-box fw-member-auth-panel"><h2><?php echo esc_html( findewerkstatt_t( 'Verzeichnis verwalten' ) ); ?></h2><p class="fw-member-panel-intro"><?php echo esc_html( findewerkstatt_t( 'Prüfen Sie neue Betriebseinträge, die Vertretungsberechtigung bei Übernahmen sowie beantragte Änderungen und Paketwechsel.' ) ); ?></p><div class="fw-dashboard-admin-grid">
            <?php foreach ( $links as $link ) : ?><div class="fw-dashboard-admin-card"><h3><?php echo findewerkstatt_member_dashboard_icon( $link[0] ); ?><?php echo esc_html( $link[1] ); ?></h3><p><?php echo esc_html( $link[3] ); ?></p><a class="fw-btn fw-btn-outline fw-btn-sm" href="<?php echo esc_url( $link[2] ); ?>"><?php echo esc_html( findewerkstatt_t( 'Verwaltung öffnen' ) ); ?> <?php echo findewerkstatt_member_dashboard_icon( 'arrow' ); ?></a></div><?php endforeach; ?>
        </div></section>
    </div>
</div>
<?php else : ?>
<section class="fw-box fw-member-auth-panel"><h2><?php echo esc_html( findewerkstatt_t( 'Dieses Konto ist kein Werkstattkonto' ) ); ?></h2><p class="fw-member-panel-intro"><?php echo esc_html( findewerkstatt_t( 'Für die Verwaltung von Betriebseinträgen ist ein Werkstattkonto erforderlich. Kontaktieren Sie uns, wenn Sie Ihren bestehenden Zugang dafür nutzen möchten.' ) ); ?></p><a class="fw-btn fw-dashboard-add" href="<?php echo esc_url( findewerkstatt_page_url( 'kontakt' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Kontakt aufnehmen' ) ); ?></a><p class="fw-member-form-link"><a href="<?php echo esc_url( wp_lostpassword_url( findewerkstatt_page_url( 'mein-konto' ) ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Passwort zurücksetzen' ) ); ?></a></p></section>
<?php endif; ?>
