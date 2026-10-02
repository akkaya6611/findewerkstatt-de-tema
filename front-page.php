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
                <h1><?php echo wp_kses( findewerkstatt_t( 'Die passende <span>Autowerkstatt</span> in Ihrer Nähe finden' ), array( 'span' => array() ) ); ?></h1>
                <p><?php echo esc_html( findewerkstatt_t( 'Wählen Sie zuerst Ihr Bundesland, dann Ihre Stadt. Finden Sie passende Betriebe nach Leistung und Fahrzeugmarke.' ) ); ?></p>
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
            <button type="submit" class="fw-btn fw-btn-accent fw-btn-lg"><?php echo esc_html( findewerkstatt_t( 'Werkstatt finden' ) ); ?></button>
        </form>
        <div class="fw-hero-popular"><span><?php echo esc_html( findewerkstatt_t( 'Städte entdecken:' ) ); ?></span><?php foreach ( array( 'berlin' => 'Berlin', 'hamburg' => 'Hamburg', 'muenchen' => 'München', 'koeln' => 'Köln', 'frankfurt-am-main' => 'Frankfurt am Main', 'stuttgart' => 'Stuttgart' ) as $slug => $name ) : ?><a href="<?php echo esc_url( findewerkstatt_term_url( $slug, 'mechanic_city' ) ); ?>"><?php echo esc_html( $name ); ?></a><?php endforeach; ?></div>
    </div></div>
</section>
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
<section class="fw-section fw-section-white"><div class="fw-container">
    <div class="fw-section-header">
        <div class="fw-section-tag"><?php echo esc_html( findewerkstatt_t( 'Einfach & Transparent' ) ); ?></div>
        <h2><?php echo esc_html( findewerkstatt_t( 'So finden Sie Ihre Werkstatt' ) ); ?></h2>
        <p><?php echo esc_html( findewerkstatt_t( 'In nur drei Schritten zur passenden Reparatur oder Inspektion' ) ); ?></p>
    </div>
    <div class="fw-benefits-grid">
        <div class="fw-box">
            <div style="display:inline-flex; align-items:center; justify-content:center; width:44px; height:44px; border-radius:12px; background:rgba(5,41,93,0.08); font-size:20px; margin-bottom:16px;">🔍</div>
            <h3><?php echo esc_html( findewerkstatt_t( '1. Passenden Betrieb suchen' ) ); ?></h3>
            <p><?php echo esc_html( findewerkstatt_t( 'Filtern Sie das Verzeichnis nach Stadt, Leistung und Fahrzeugmarke.' ) ); ?></p>
        </div>
        <div class="fw-box">
            <div style="display:inline-flex; align-items:center; justify-content:center; width:44px; height:44px; border-radius:12px; background:rgba(251,96,6,0.12); font-size:20px; margin-bottom:16px;">⭐</div>
            <h3><?php echo esc_html( findewerkstatt_t( '2. Profil ansehen' ) ); ?></h3>
            <p><?php echo esc_html( findewerkstatt_t( 'Informieren Sie sich über die angegebenen Leistungen, Kontaktdaten und Öffnungszeiten.' ) ); ?></p>
        </div>
        <div class="fw-box">
            <div style="display:inline-flex; align-items:center; justify-content:center; width:44px; height:44px; border-radius:12px; background:rgba(16,185,129,0.12); font-size:20px; margin-bottom:16px;">📞</div>
            <h3><?php echo esc_html( findewerkstatt_t( '3. Direkt Kontakt aufnehmen' ) ); ?></h3>
            <p><?php echo esc_html( findewerkstatt_t( 'Fragen Sie direkt beim Betrieb nach einem Termin oder Kostenvoranschlag. Die Suche im Verzeichnis ist kostenlos.' ) ); ?></p>
        </div>
    </div>
</div></section>
<section class="fw-section fw-owner-cta"><div class="fw-container"><div class="fw-owner-layout">
    <div class="fw-owner-copy">
        <div class="fw-hero-badge" style="background:rgba(251,96,6,0.15); border-color:rgba(251,96,6,0.3); color:#fed7aa; margin-bottom:14px;">🛡️ Für Werkstattinhaber</div>
        <h2><?php echo esc_html( findewerkstatt_t( 'Sie betreiben eine Kfz-Werkstatt?' ) ); ?></h2>
        <p><?php echo esc_html( findewerkstatt_t( 'Stellen Sie Ihre Leistungen vor und machen Sie Ihren Betrieb für Autofahrer in Ihrer Region sichtbar. Gewinnen Sie täglich neue Kunden direkt aus Ihrer Stadt.' ) ); ?></p>
        <a href="<?php echo esc_url( findewerkstatt_page_url( 'werkstatt-anmelden' ) ); ?>" class="fw-btn fw-btn-accent fw-btn-lg"><?php echo esc_html( findewerkstatt_t( 'Werkstatt kostenlos eintragen →' ) ); ?></a>
    </div>
    <div class="fw-mascot-art fw-owner-mascot" aria-hidden="true">
        <img src="<?php echo esc_url( findewerkstatt_brand_asset_url( 'mascot_workshop' ) ); ?>" alt="" width="480" height="480" loading="lazy" decoding="async">
    </div>
</div></div></section>
</main>
<?php get_footer(); ?>
