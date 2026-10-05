<?php
/** Germany-wide workshop directory homepage. */
get_header();
$states = FindeWerkstatt_German_Data::get_bundeslaender();
$services = FindeWerkstatt_German_Data::get_categories();
$brands = FindeWerkstatt_German_Data::get_car_brands();
?>
<main id="main-content">
<section class="fw-hero">
    <div class="fw-container"><div class="fw-hero-content">
        <div class="fw-hero-intro">
            <div class="fw-hero-copy">
                <div class="fw-hero-badge"><?php echo esc_html( findewerkstatt_t( 'Kfz-Werkstätten und Autoservices in Deutschland' ) ); ?></div>
                <h1><?php echo esc_html( findewerkstatt_t( 'Die passende Werkstatt in Ihrer Nähe finden' ) ); ?></h1>
                <p><?php echo esc_html( findewerkstatt_t( 'Vergleichen Sie Werkstätten, Autoservices und Pannenhilfe in Ihrer Region.' ) ); ?></p>
            </div>
            <div class="fw-mascot-art fw-hero-mascot" aria-hidden="true">
                <img src="<?php echo esc_url( findewerkstatt_brand_asset_url( 'mascot_welcome' ) ); ?>" alt="" width="1024" height="1024" fetchpriority="high">
            </div>
        </div>
        <form action="<?php echo esc_url( get_post_type_archive_link( 'mechanic' ) ); ?>" method="get" class="fw-search-box">
            <input type="hidden" name="post_type" value="mechanic">
            <?php findewerkstatt_render_location_picker( 'home', array(), true ); ?>
            <div class="fw-search-field"><span class="fw-field-icon" aria-hidden="true">🔧</span><label for="home-service"><?php echo esc_html( findewerkstatt_t( 'Leistung' ) ); ?></label><select id="home-service" name="fw_service"><option value=""><?php echo esc_html( findewerkstatt_t( 'Alle Leistungen' ) ); ?></option><?php foreach ( $services as $slug => $service ) : ?><option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( findewerkstatt_t( $service['name'] ) ); ?></option><?php endforeach; ?></select></div>
            <div class="fw-search-field"><span class="fw-field-icon" aria-hidden="true">🚗</span><label for="home-brand"><?php echo esc_html( findewerkstatt_t( 'Fahrzeugmarke' ) ); ?></label><select id="home-brand" name="fw_brand"><option value=""><?php echo esc_html( findewerkstatt_t( 'Alle Marken' ) ); ?></option><?php foreach ( $brands as $slug => $brand ) : ?><option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $brand ); ?></option><?php endforeach; ?></select></div>
            <div class="fw-search-actions">
                <button type="submit" class="fw-btn fw-btn-accent fw-btn-lg"><?php echo esc_html( findewerkstatt_t( 'Werkstatt finden' ) ); ?></button>
                <button type="button" class="fw-btn fw-btn-near-me fw-btn-lg fw-btn-gps" data-archive-url="<?php echo esc_url( get_post_type_archive_link( 'mechanic' ) ); ?>" title="<?php echo esc_attr( findewerkstatt_t( 'Werkstätten in meiner Nähe per GPS finden' ) ); ?>">
                    <span class="fw-near-me-icon" aria-hidden="true">📍</span>
                    <span><?php echo esc_html( findewerkstatt_t( 'Standort verwenden' ) ); ?></span>
                </button>
            </div>
        </form>
        <div class="fw-hero-popular"><span><?php echo esc_html( findewerkstatt_t( 'Städte entdecken:' ) ); ?></span><?php foreach ( array( 'berlin' => 'Berlin', 'hamburg' => 'Hamburg', 'muenchen' => 'München', 'koeln' => 'Köln', 'frankfurt-am-main' => 'Frankfurt am Main', 'stuttgart' => 'Stuttgart' ) as $slug => $name ) : ?><a href="<?php echo esc_url( findewerkstatt_term_url( $slug, 'mechanic_city' ) ); ?>"><?php echo esc_html( $name ); ?></a><?php endforeach; ?></div>
    </div></div>
</section>

<!-- Trust Block (Section 22) -->
<section class="fw-section fw-section-trust">
    <div class="fw-container">
        <div class="fw-section-header">
            <div class="fw-section-tag"><?php echo esc_html( findewerkstatt_t( 'Ihre Vorteile' ) ); ?></div>
            <h2><?php echo esc_html( findewerkstatt_t( 'Warum FindeWerkstatt?' ) ); ?></h2>
            <p><?php echo esc_html( findewerkstatt_t( 'Das transparente Portal für Werkstattsuche, Reparaturvergleich und direkte Angebote.' ) ); ?></p>
        </div>
        <div class="fw-benefits-grid">
            <div class="fw-box fw-trust-item">
                <div class="fw-trust-icon">🎉</div>
                <h3><?php echo esc_html( findewerkstatt_t( 'Für Autofahrer 100% kostenlos' ) ); ?></h3>
                <p><?php echo esc_html( findewerkstatt_t( 'Keine versteckten Gebühren. Vergleichen und kontaktieren Sie Betriebe völlig gebührenfrei.' ) ); ?></p>
            </div>
            <div class="fw-box fw-trust-item">
                <div class="fw-trust-icon">🛡️</div>
                <h3><?php echo esc_html( findewerkstatt_t( 'Geprüfte Werkstattinformationen' ) ); ?></h3>
                <p><?php echo esc_html( findewerkstatt_t( 'Verifizierte Stammdaten, Öffnungszeiten, Kontaktdaten und echte Fachkategorien.' ) ); ?></p>
            </div>
            <div class="fw-box fw-trust-item">
                <div class="fw-trust-icon">⚡</div>
                <h3><?php echo esc_html( findewerkstatt_t( 'Direkter Kontakt ohne Umwege' ) ); ?></h3>
                <p><?php echo esc_html( findewerkstatt_t( 'Rufen Sie direkt an, schreiben Sie per WhatsApp oder fordern Sie unverbindlich Angebote an.' ) ); ?></p>
            </div>
            <div class="fw-box fw-trust-item">
                <div class="fw-trust-icon">⭐</div>
                <h3><?php echo esc_html( findewerkstatt_t( 'Transparente Bewertungen' ) ); ?></h3>
                <p><?php echo esc_html( findewerkstatt_t( 'Klare Trennung zwischen Google-Bewertungen und authentischen Plattform-Erfahrungen.' ) ); ?></p>
            </div>
        </div>
    </div>
</section>

<?php if ( function_exists( 'findewerkstatt_render_banner_slider' ) ) : ?>
<section class="fw-section fw-banner-slider-section">
    <div class="fw-container">
        <?php findewerkstatt_render_banner_slider(); ?>
    </div>
</section>
<?php endif; ?>

<section class="fw-section" id="leistungen"><div class="fw-container">
    <div class="fw-section-header"><div class="fw-section-tag"><?php echo esc_html( findewerkstatt_t( 'Für Ihr Fahrzeug' ) ); ?></div><h2><?php echo esc_html( findewerkstatt_t( 'Leistungen und Fachbereiche' ) ); ?></h2><p><?php echo esc_html( findewerkstatt_t( 'Von der Wartung bis zur Reparatur: Finden Sie einen Betrieb für Ihr Anliegen.' ) ); ?></p></div>
    <div class="fw-categories-grid"><?php foreach ( $services as $slug => $service ) : ?><a href="<?php echo esc_url( findewerkstatt_term_url( $slug, 'service_type' ) ); ?>" class="fw-cat-card"><div class="fw-cat-icon" aria-hidden="true">🔧</div><div class="fw-cat-info"><h3><?php echo esc_html( findewerkstatt_t( $service['name'] ) ); ?></h3><p><?php echo esc_html( wp_trim_words( findewerkstatt_t( $service['desc'] ), 12 ) ); ?></p></div></a><?php endforeach; ?></div>
</div></section>

<section class="fw-section fw-section-muted"><div class="fw-container">
    <div class="fw-section-header"><div class="fw-section-tag"><?php echo esc_html( findewerkstatt_t( 'Das Verzeichnis wächst' ) ); ?></div><h2><?php echo esc_html( findewerkstatt_t( 'Neue Werkstatteinträge' ) ); ?></h2><p><?php echo esc_html( findewerkstatt_t( 'Entdecken Sie die zuletzt veröffentlichten Betriebe.' ) ); ?></p></div>
    <?php $new_workshops = new WP_Query( array( 'post_type' => 'mechanic', 'post_status' => 'publish', 'posts_per_page' => 6, 'no_found_rows' => true ) ); ?>
    <?php if ( $new_workshops->have_posts() ) : ?><div class="fw-workshops-grid"><?php while ( $new_workshops->have_posts() ) : $new_workshops->the_post(); get_template_part( 'template-parts/workshop-card' ); endwhile; ?></div><?php else : ?><div class="fw-empty-state"><h3><?php echo esc_html( findewerkstatt_t( 'Hier ist Platz für Ihre Werkstatt.' ) ); ?></h3><p><?php echo esc_html( findewerkstatt_t( 'Unser Verzeichnis wird aufgebaut. Tragen Sie Ihren Betrieb kostenlos ein und helfen Sie Autofahrern, Sie zu finden.' ) ); ?></p><a class="fw-btn fw-btn-primary" href="<?php echo esc_url( findewerkstatt_page_url( 'werkstatt-anmelden' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Werkstatt eintragen' ) ); ?></a></div><?php endif; wp_reset_postdata(); ?>
    <div class="fw-section-action"><a href="<?php echo esc_url( get_post_type_archive_link( 'mechanic' ) ); ?>" class="fw-btn fw-btn-primary"><?php echo esc_html( findewerkstatt_t( 'Alle Werkstätten ansehen →' ) ); ?></a></div>
</div></section>

<section class="fw-section" id="bundeslaender"><div class="fw-container">
    <div class="fw-section-header"><div class="fw-section-tag"><?php echo esc_html( findewerkstatt_t( 'Regional suchen' ) ); ?></div><h2><?php echo esc_html( findewerkstatt_t( 'Werkstätten nach Bundesland' ) ); ?></h2><p><?php echo esc_html( findewerkstatt_t( 'Wählen Sie Ihr Bundesland und entdecken Sie Städte und Betriebe in Ihrer Region.' ) ); ?></p></div>
    <div class="fw-bundeslaender-grid"><?php foreach ( $states as $state ) : ?><a href="<?php echo esc_url( findewerkstatt_term_url( $state['slug'], 'mechanic_city' ) ); ?>" class="fw-land-card"><span class="fw-land-name"><?php echo esc_html( $state['name'] ); ?></span><span aria-hidden="true">→</span></a><?php endforeach; ?></div>
</div></section>

<!-- FAQ Section (Section 35 & 36) -->
<section class="fw-section fw-section-faq" id="faq">
    <div class="fw-container">
        <div class="fw-section-header">
            <div class="fw-section-tag"><?php echo esc_html( findewerkstatt_t( 'Hilfe & Antworten' ) ); ?></div>
            <h2><?php echo esc_html( findewerkstatt_t( 'Häufig gestellte Fragen' ) ); ?></h2>
            <p><?php echo esc_html( findewerkstatt_t( 'Alles Wissenswerte rund um die Werkstattsuche und -eintragung auf FindeWerkstatt.' ) ); ?></p>
        </div>
        <div class="fw-faq-accordion" style="max-width:800px; margin:0 auto;">
            <div class="fw-box fw-faq-item">
                <button type="button" class="fw-faq-question">
                    <span><?php echo esc_html( findewerkstatt_t( 'Wie funktioniert FindeWerkstatt?' ) ); ?></span>
                    <span class="fw-faq-icon" aria-hidden="true">+</span>
                </button>
                <div class="fw-faq-answer">
                    <p><?php echo esc_html( findewerkstatt_t( 'Auf FindeWerkstatt finden Sie qualifizierte Kfz-Werkstätten in ganz Deutschland. Wählen Sie einfach Ihr Bundesland, Ihre Stadt oder nutzen Sie die Standortsuche, um passende Betriebe nach Fachgebiet und Fahrzeugmarke zu vergleichen.' ) ); ?></p>
                </div>
            </div>
            <div class="fw-box fw-faq-item">
                <button type="button" class="fw-faq-question">
                    <span><?php echo esc_html( findewerkstatt_t( 'Ist die Nutzung für Autofahrer kostenlos?' ) ); ?></span>
                    <span class="fw-faq-icon" aria-hidden="true">+</span>
                </button>
                <div class="fw-faq-answer">
                    <p><?php echo esc_html( findewerkstatt_t( 'Ja, die Suche, der Vergleich von Werkstätten und das Absenden von Angebotsanfragen ist für Autofahrer zu 100% kostenfrei und unverbindlich.' ) ); ?></p>
                </div>
            </div>
            <div class="fw-box fw-faq-item">
                <button type="button" class="fw-faq-question">
                    <span><?php echo esc_html( findewerkstatt_t( 'Wie erhalte ich ein Angebot?' ) ); ?></span>
                    <span class="fw-faq-icon" aria-hidden="true">+</span>
                </button>
                <div class="fw-faq-answer">
                    <p><?php echo esc_html( findewerkstatt_t( 'Auf dem Profil der jeweiligen Werkstatt finden Sie das Formular „Angebot anfragen“. Geben Sie dort Ihre Fahrzeugdaten und Ihr Anliegen ein. Der Betrieb setzt sich schnellstmöglich per Telefon, WhatsApp oder E-Mail mit Ihnen in Verbindung.' ) ); ?></p>
                </div>
            </div>
            <div class="fw-box fw-faq-item">
                <button type="button" class="fw-faq-question">
                    <span><?php echo esc_html( findewerkstatt_t( 'Wie kann ich meine Werkstatt eintragen?' ) ); ?></span>
                    <span class="fw-faq-icon" aria-hidden="true">+</span>
                </button>
                <div class="fw-faq-answer">
                    <p><?php echo esc_html( findewerkstatt_t( 'Als Inhaber können Sie Ihren Betrieb kostenlos registrieren oder einen bestehenden Eintrag beanspruchen. Klicken Sie dazu auf „Werkstatt eintragen“ oder „Eintrag beanspruchen“.' ) ); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Owner CTA (Section 23) -->
<section class="fw-section fw-owner-cta"><div class="fw-container"><div class="fw-owner-layout">
    <div class="fw-owner-copy">
        <div class="fw-hero-badge" style="background:rgba(251,96,6,0.15); border-color:rgba(251,96,6,0.3); color:#fed7aa; margin-bottom:14px;">🛡️ Für Werkstattinhaber</div>
        <h2><?php echo esc_html( findewerkstatt_t( 'Sind Sie Werkstattinhaber?' ) ); ?></h2>
        <p><?php echo esc_html( findewerkstatt_t( 'Tragen Sie Ihre Werkstatt ein und erreichen Sie Kunden in Ihrer Region.' ) ); ?></p>
        <div style="display:flex; flex-wrap:wrap; gap:12px; margin-top:16px;">
            <a href="<?php echo esc_url( findewerkstatt_page_url( 'mein-konto' ) . '#eintrag-uebernehmen' ); ?>" class="fw-btn fw-btn-accent fw-btn-lg" data-event="claim_business"><?php echo esc_html( findewerkstatt_t( 'Eintrag beanspruchen' ) ); ?></a>
            <a href="<?php echo esc_url( findewerkstatt_page_url( 'pakete' ) ); ?>" class="fw-btn fw-btn-outline fw-btn-lg" style="color:#fff; border-color:rgba(255,255,255,0.4);"><?php echo esc_html( findewerkstatt_t( 'Pakete ansehen' ) ); ?></a>
        </div>
    </div>
    <div class="fw-mascot-art fw-owner-mascot" aria-hidden="true">
        <img src="<?php echo esc_url( findewerkstatt_brand_asset_url( 'mascot_workshop' ) ); ?>" alt="" width="480" height="480" loading="lazy" decoding="async">
    </div>
</div></div></section>
</main>
<?php get_footer(); ?>
