<?php
/**
 * Template Name: Mitgliedschaftspakete
 * @package FindeWerkstatt
 */
$plans        = findewerkstatt_membership_plans();
$account_url  = findewerkstatt_page_url( 'mein-konto' );
$is_member    = findewerkstatt_is_workshop_member();
$current_plan = $is_member ? findewerkstatt_member_plan() : '';
$descriptions = array(
    'free'         => findewerkstatt_t( 'Für einen Betrieb, der sein Profil im Verzeichnis verwalten möchte.' ),
    'plus'         => findewerkstatt_t( 'Für Werkstattinhaber mit mehreren Betrieben oder Standorten.' ),
    'professional' => findewerkstatt_t( 'Für Unternehmen mit einer größeren Anzahl von Standorten.' ),
);
get_header();
?>
<main id="main-content" class="fw-container fw-page-content fw-member">
    <div class="fw-member-intro">
        <span class="fw-section-tag"><?php echo esc_html( findewerkstatt_t( 'Für Werkstattinhaber' ) ); ?></span>
        <h1><?php echo esc_html( findewerkstatt_t( 'Das passende Paket für Ihre Werkstatt' ) ); ?></h1>
        <p><?php echo esc_html( findewerkstatt_t( 'Erstellen Sie zuerst ein kostenloses Konto. Beim Eintragen Ihrer Werkstatt wählen Sie das passende Paket. Behalten Sie Ihre Betriebseinträge im Blick und reichen Sie Änderungen zur Prüfung ein.' ) ); ?></p>
    </div>
    <div class="fw-member-plan-grid">
        <?php foreach ( $plans as $plan_id => $plan ) :
            $limit      = (int) $plan['listing_limit'];
            $is_current = $current_plan === $plan_id;
            $target     = $is_member
                ? add_query_arg( 'plan', $plan_id, findewerkstatt_page_url( 'werkstatt-anmelden' ) ) . '#paket-auswahl'
                : $account_url . ( is_user_logged_in() ? '' : '#registrieren' );
            ?>
            <article class="fw-member-plan<?php echo $is_current ? ' fw-member-plan-current' : ''; ?>" aria-labelledby="fw-plan-<?php echo esc_attr( $plan_id ); ?>">
                <div class="fw-member-plan-top">
                    <span class="fw-member-plan-label"><?php echo $is_current ? findewerkstatt_t( 'Ihr aktuelles Paket' ) : ( 'free' === $plan_id ? findewerkstatt_t( 'Kostenlos starten' ) : findewerkstatt_t( 'Auf Anfrage' ) ); ?></span>
                    <h2 id="fw-plan-<?php echo esc_attr( $plan_id ); ?>"><?php echo esc_html( $plan['name'] ); ?></h2>
                    <p class="fw-member-plan-price"><?php echo 'free' === $plan_id ? '0 €' : findewerkstatt_t( 'Preis folgt' ); ?></p>
                    <p class="fw-member-plan-description"><?php echo esc_html( $descriptions[ $plan_id ] ?? '' ); ?></p>
                </div>
                <ul class="fw-member-plan-features">
                    <li><strong><?php echo esc_html( sprintf( _n( findewerkstatt_t( 'Bis zu %d Betriebseintrag' ), findewerkstatt_t( 'Bis zu %d Betriebseinträge' ), $limit, 'findewerkstatt' ), $limit ) ); ?></strong></li>
                    <li><?php echo esc_html( findewerkstatt_t( 'Betriebsprofile mit Kontaktangaben und Leistungen' ) ); ?></li>
                    <li><?php echo esc_html( findewerkstatt_t( 'Übersicht Ihrer zugeordneten Einträge im Konto' ) ); ?></li>
                    <li><?php echo esc_html( findewerkstatt_t( 'Änderungsanträge mit anschließender Prüfung' ) ); ?></li>
                </ul>
                <a class="fw-btn <?php echo 'free' === $plan_id ? 'fw-btn-outline' : 'fw-btn-primary'; ?> fw-btn-block" href="<?php echo esc_url( $target ); ?>">
                    <?php echo esc_html( $is_member ? findewerkstatt_t( 'Mit diesem Paket Betrieb eintragen' ) : ( is_user_logged_in() ? findewerkstatt_t( 'Zum Konto' ) : findewerkstatt_t( 'Kostenloses Konto erstellen' ) ) ); ?>
                </a>
            </article>
        <?php endforeach; ?>
    </div>
    <section class="fw-box fw-member-explainer" aria-labelledby="fw-member-request-heading">
        <h2 id="fw-member-request-heading"><?php echo esc_html( findewerkstatt_t( 'So funktioniert Ihre Mitgliedschaft' ) ); ?></h2>
        <ol>
            <li><strong><?php echo esc_html( findewerkstatt_t( 'Kostenlos registrieren.' ) ); ?></strong> <?php echo esc_html( findewerkstatt_t( 'Bei der Kontoerstellung wählen Sie kein Paket aus.' ) ); ?></li>
            <li><strong><?php echo esc_html( findewerkstatt_t( 'Betrieb eintragen und Paket wählen.' ) ); ?></strong> <?php echo esc_html( findewerkstatt_t( 'Die Paketauswahl erfolgt im Eintragsformular. Plus und Professional können Sie dort unverbindlich anfragen; sie werden nach einer Prüfung manuell freigeschaltet.' ) ); ?></li>
            <li><strong><?php echo esc_html( findewerkstatt_t( 'Eintrag prüfen lassen.' ) ); ?></strong> <?php echo esc_html( findewerkstatt_t( 'Neue Einträge und Anträge zur Übernahme bestehender Profile werden vor der Freigabe geprüft.' ) ); ?></li>
        </ol>
        <p><?php echo esc_html( findewerkstatt_t( 'Die Preise für Plus und Professional stehen noch nicht fest. Eine Paketanfrage ist unverbindlich und löst keine Zahlung oder kostenpflichtige Bestellung aus. Bis zur Freigabe bleibt Ihr aktuelles Paket bestehen.' ) ); ?></p>
        <p><?php echo esc_html( findewerkstatt_t( 'Jeder Betriebseintrag wird vor der Veröffentlichung geprüft. Die Pakete unterscheiden sich derzeit in der Anzahl der verwaltbaren Einträge; eine bestimmte Platzierung oder Anzahl von Anfragen wird nicht zugesichert.' ) ); ?></p>
        <div class="fw-member-legal-links">
            <a href="<?php echo esc_url( findewerkstatt_page_url( 'agb' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Nutzungsbedingungen' ) ); ?></a>
            <a href="<?php echo esc_url( findewerkstatt_page_url( 'datenschutz' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Datenschutzerklärung' ) ); ?></a>
            <a href="<?php echo esc_url( findewerkstatt_page_url( 'kontakt' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Kontakt' ) ); ?></a>
        </div>
    </section>
</main>
<?php get_footer(); ?>
