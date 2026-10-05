<?php
/**
 * Werkstätten nach Leistung, optional mit Standortfilter.
 *
 * @package FindeWerkstatt
 */

global $wp_query;
$current_term = FindeWerkstatt_Programmatic_SEO::get_query_term( 'service_type', $wp_query );
$term_name = $current_term ? findewerkstatt_t( $current_term->name ) : findewerkstatt_t( 'Kfz-Service' );
$location = get_query_var( 'fw_pseo_location' ) ? FindeWerkstatt_Programmatic_SEO::get_location_term( $wp_query ) : null;
$location_name = $location ? findewerkstatt_location_display_name( $location ) : 'Deutschland';
$location_phrase = $location ? findewerkstatt_location_phrase( findewerkstatt_location_context( $location ) ) : findewerkstatt_t( 'in Deutschland' );
get_header();
?>
<main id="main-content" class="fw-container fw-page-content">
    <nav class="fw-breadcrumbs" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Brotkrümelnavigation' ) ); ?>">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Startseite' ) ); ?></a><span aria-hidden="true">›</span>
        <a href="<?php echo esc_url( get_post_type_archive_link( 'mechanic' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Werkstätten' ) ); ?></a><span aria-hidden="true">›</span>
        <?php if ( $location && $current_term ) : ?>
            <a href="<?php echo esc_url( get_term_link( $current_term ) ); ?>"><?php echo esc_html( $term_name ); ?></a><span aria-hidden="true">›</span>
            <span aria-current="page"><?php echo esc_html( $location_name ); ?></span>
        <?php else : ?>
            <span aria-current="page"><?php echo esc_html( $term_name ); ?></span>
        <?php endif; ?>
    </nav>

    <?php 
    $service_banner = ( $current_term && function_exists( 'findewerkstatt_get_service_banner' ) ) ? findewerkstatt_get_service_banner( $current_term->slug ) : null;
    if ( $service_banner ) : ?>
        <div class="fw-service-hero-banner">
            <img src="<?php echo esc_url( $service_banner['url'] ); ?>" 
                 alt="<?php echo esc_attr( $service_banner['alt'] ); ?>" 
                 width="1024" 
                 height="384" 
                 loading="eager" 
                 fetchpriority="high">
        </div>
    <?php endif; ?>

    <div class="fw-box fw-page-heading">
        <p class="fw-text-muted" style="margin-bottom:8px;"><?php echo esc_html( findewerkstatt_t( 'Werkstätten nach Leistung' ) ); ?></p>
        <h1><?php echo esc_html( $current_term ? findewerkstatt_directory_term_title( $current_term, $location ) : $term_name . ' ' . $location_phrase ); ?></h1>
        <p style="color:var(--fw-text-muted);"><?php echo esc_html( sprintf( findewerkstatt_t( 'Entdecken Sie Betriebe im Fachbereich „%s“. Vergleichen Sie die Angaben im Profil und fragen Sie nach der gewünschten Leistung, freien Terminen und einem Kostenvoranschlag.' ), $term_name ) ); ?></p>
        <?php if ( $location ) : ?>
            <p style="margin-top:16px;"><a href="<?php echo esc_url( get_term_link( $location ) ); ?>"><?php echo esc_html( sprintf( findewerkstatt_t( 'Alle Werkstätten %s ansehen →' ), $location_phrase ) ); ?></a></p>
        <?php endif; ?>
    </div>

    <?php if ( have_posts() ) : ?>
        <p style="margin-bottom:16px;"><?php echo esc_html( sprintf( findewerkstatt_t( 1 === (int) $wp_query->found_posts ? '%s Werkstatt gefunden' : '%s Werkstätten gefunden' ), number_format_i18n( $wp_query->found_posts ) ) ); ?></p>
        <?php if ( function_exists( 'findewerkstatt_render_live_filter_bar' ) ) { findewerkstatt_render_live_filter_bar(); } ?>
        <?php FindeWerkstatt_Programmatic_SEO::render_workshop_cards( $wp_query ); ?>
        <?php findewerkstatt_pagination( array( 'mid_size' => 2, 'prev_text' => findewerkstatt_t( '← Zurück' ), 'next_text' => findewerkstatt_t( 'Weiter →' ) ) ); ?>
    <?php else : ?>
        <div class="fw-box" style="text-align:center;">
            <h2><?php echo esc_html( findewerkstatt_t( 'Für diese Auswahl sind noch keine Werkstätten eingetragen' ) ); ?></h2>
            <p style="margin:12px 0 20px;"><?php echo esc_html( findewerkstatt_t( 'Erweitern Sie Ihre Suche oder tragen Sie Ihren Betrieb mit seinen Leistungen ein.' ) ); ?></p>
            <a href="<?php echo esc_url( get_post_type_archive_link( 'mechanic' ) ); ?>" class="fw-btn fw-btn-outline"><?php echo esc_html( findewerkstatt_t( 'Alle Werkstätten durchsuchen' ) ); ?></a>
        </div>
    <?php endif; ?>

    <?php if ( class_exists( 'FindeWerkstatt_FAQ_Manager' ) ) {
        FindeWerkstatt_FAQ_Manager::render_cost_estimator();
        FindeWerkstatt_FAQ_Manager::render_faq_section();
    } ?>

    <div class="fw-box" style="margin-top:30px; text-align:center;">
        <h2><?php echo esc_html( findewerkstatt_t( 'Ihre Werkstatt fehlt im Verzeichnis?' ) ); ?></h2>
        <p style="margin:12px 0 20px;"><?php echo esc_html( findewerkstatt_t( 'Tragen Sie Ihren Betrieb mit Standort, Leistungen und Kontaktdaten ein.' ) ); ?></p>
        <a href="<?php echo esc_url( findewerkstatt_page_url( 'werkstatt-anmelden' ) ); ?>" class="fw-btn fw-btn-primary"><?php echo esc_html( findewerkstatt_t( 'Werkstatt eintragen' ) ); ?></a>
    </div>
</main>
<?php get_footer();
