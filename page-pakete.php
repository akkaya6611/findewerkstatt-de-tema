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
    'plus'         => findewerkstatt_t( 'Für wachsende Werkstätten mit mehr Kundenkontakt und Bildergalerie.' ),
    'professional' => findewerkstatt_t( 'Maximale Sichtbarkeit, Top-Platzierung und direkte Angebotsanfragen.' ),
);

$plan_features = array(
    'free' => array(
        findewerkstatt_t( 'Basis-Unternehmensprofil' ),
        findewerkstatt_t( 'Telefonnummer und Adresse' ),
        findewerkstatt_t( 'Öffnungszeiten' ),
        findewerkstatt_t( '1 Kategorie / Fachbereich' ),
    ),
    'plus' => array(
        findewerkstatt_t( 'Priorisierte Listung in Suchergebnissen' ),
        findewerkstatt_t( 'Direkter WhatsApp-Button' ),
        findewerkstatt_t( 'Umfangreiche Bildergalerie' ),
        findewerkstatt_t( 'Alle relevanten Kategorien & Services' ),
        findewerkstatt_t( 'Angebotsanfragen direkt empfangen' ),
    ),
    'professional' => array(
        findewerkstatt_t( 'Top-Platzierung & maximale Sichtbarkeit' ),
        findewerkstatt_t( 'Prioritätsprüfung für „Geprüfter Partner“' ),
        findewerkstatt_t( 'Direkte Angebots- & Terminanfragen' ),
        findewerkstatt_t( 'Hervorgehobenes Profil-Design' ),
        findewerkstatt_t( 'Detaillierte Besucher- & Klick-Statistiken' ),
    ),
);

get_header();
?>
<main id="main-content" class="fw-container fw-page-content fw-member">
    <div class="fw-member-intro">
        <span class="fw-section-tag"><?php echo esc_html( findewerkstatt_t( 'Für Werkstattinhaber' ) ); ?></span>
        <h1><?php echo esc_html( findewerkstatt_t( 'Das passende Paket für Ihre Werkstatt' ) ); ?></h1>
        <p><?php echo esc_html( findewerkstatt_t( 'Präsentieren Sie Ihren Kfz-Betrieb tausenden Autofahrern in Ihrer Region. Wählen Sie das passende Paket für mehr Sichtbarkeit und direkte Kundenanfragen.' ) ); ?></p>
    </div>

    <div class="fw-member-plan-grid">
        <?php foreach ( $plans as $plan_id => $plan ) :
            $is_current = $current_plan === $plan_id;
            $target     = $is_member
                ? add_query_arg( 'plan', $plan_id, findewerkstatt_page_url( 'werkstatt-anmelden' ) ) . '#paket-auswahl'
                : $account_url . ( is_user_logged_in() ? '' : '#registrieren' );
            $price_text = 'free' === $plan_id ? '0 €' : findewerkstatt_t( 'Demnächst verfügbar' );
            $cta_text   = 'free' === $plan_id 
                ? findewerkstatt_t( 'Jetzt starten' ) 
                : ( $is_member ? findewerkstatt_t( 'Paket anfragen' ) : findewerkstatt_t( 'Eintrag beanspruchen' ) );
            $features   = $plan_features[ $plan_id ] ?? array();
            ?>
            <article class="fw-member-plan<?php echo $is_current ? ' fw-member-plan-current' : ( 'plus' === $plan_id ? ' fw-member-plan-featured' : '' ); ?>" aria-labelledby="fw-plan-<?php echo esc_attr( $plan_id ); ?>">
                <div class="fw-member-plan-top">
                    <span class="fw-member-plan-label"><?php echo $is_current ? findewerkstatt_t( 'Ihr aktuelles Paket' ) : ( 'free' === $plan_id ? findewerkstatt_t( 'Kostenlos starten' ) : findewerkstatt_t( 'Empfohlen' ) ); ?></span>
                    <h2 id="fw-plan-<?php echo esc_attr( $plan_id ); ?>"><?php echo esc_html( $plan['name'] ); ?></h2>
                    <p class="fw-member-plan-price"><?php echo esc_html( $price_text ); ?></p>
                    <p class="fw-member-plan-description"><?php echo esc_html( $descriptions[ $plan_id ] ?? '' ); ?></p>
                </div>
                <ul class="fw-member-plan-features">
                    <?php foreach ( $features as $feat ) : ?>
                        <li><span class="fw-feat-icon" aria-hidden="true">✓</span> <span><?php echo esc_html( $feat ); ?></span></li>
                    <?php endforeach; ?>
                </ul>
                <a class="fw-btn <?php echo 'free' === $plan_id ? 'fw-btn-outline' : 'fw-btn-primary'; ?> fw-btn-block" href="<?php echo esc_url( $target ); ?>" data-event="membership_plan_click" data-plan="<?php echo esc_attr( $plan_id ); ?>">
                    <?php echo esc_html( $cta_text ); ?>
                </a>
            </article>
        <?php endforeach; ?>
    </div>

    <section class="fw-box fw-member-explainer" aria-labelledby="fw-member-request-heading">
        <h2 id="fw-member-request-heading"><?php echo esc_html( findewerkstatt_t( 'So funktioniert Ihre Mitgliedschaft' ) ); ?></h2>
        <ol>
            <li><strong><?php echo esc_html( findewerkstatt_t( 'Kostenlos registrieren.' ) ); ?></strong> <?php echo esc_html( findewerkstatt_t( 'Erstellen Sie Ihr Benutzerkonto in weniger als 2 Minuten.' ) ); ?></li>
            <li><strong><?php echo esc_html( findewerkstatt_t( 'Betrieb eintragen oder beanspruchen.' ) ); ?></strong> <?php echo esc_html( findewerkstatt_t( 'Tragen Sie einen neuen Betrieb ein oder beanspruchen Sie einen bereits vorhandenen Eintrag.' ) ); ?></li>
            <li><strong><?php echo esc_html( findewerkstatt_t( 'Eintrag prüfen lassen & Kunden gewinnen.' ) ); ?></strong> <?php echo esc_html( findewerkstatt_t( 'Nach einer redaktionellen Prüfung wird Ihr Profil freigeschaltet und ist für Kunden direkt erreichbar.' ) ); ?></li>
        </ol>
        <p><?php echo esc_html( findewerkstatt_t( 'Die kostenpflichtigen Pakete Plus und Professional befinden sich in der Einführung. Eine Voranmeldung ist unverbindlich und kostenlos.' ) ); ?></p>
        <div class="fw-member-legal-links">
            <a href="<?php echo esc_url( findewerkstatt_page_url( 'agb' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Nutzungsbedingungen' ) ); ?></a>
            <a href="<?php echo esc_url( findewerkstatt_page_url( 'datenschutz' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Datenschutzerklärung' ) ); ?></a>
            <a href="<?php echo esc_url( findewerkstatt_page_url( 'kontakt' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Kontakt' ) ); ?></a>
        </div>
    </section>
</main>
<?php get_footer(); ?>
