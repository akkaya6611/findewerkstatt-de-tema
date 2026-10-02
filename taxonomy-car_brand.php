<?php
/**
 * Werkstätten nach Automarke, optional mit Standortfilter.
 *
 * @package FindeWerkstatt
 */

global $wp_query;
$current_term = FindeWerkstatt_Programmatic_SEO::get_query_term( 'car_brand', $wp_query );
$term_name = $current_term ? $current_term->name : findewerkstatt_t( 'Automarke' );
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

    <div class="fw-box fw-page-heading">
        <p class="fw-text-muted" style="margin-bottom:8px;"><?php echo esc_html( findewerkstatt_t( 'Werkstätten nach Automarke' ) ); ?></p>
        <h1><?php echo esc_html( $current_term ? findewerkstatt_directory_term_title( $current_term, $location ) : sprintf( findewerkstatt_t( '%1$s-Werkstätten %2$s' ), $term_name, $location_phrase ) ); ?></h1>
        <p style="color:var(--fw-text-muted);"><?php echo esc_html( sprintf( findewerkstatt_t( 'Finden Sie Betriebe, die Arbeiten an Fahrzeugen der Marke %s anbieten. Vergleichen Sie die Angaben im Profil und fragen Sie nach Erfahrung mit Ihrem Fahrzeugmodell, freien Terminen und einem Kostenvoranschlag.' ), $term_name ) ); ?></p>
        <?php if ( $location ) : ?>
            <p style="margin-top:16px;"><a href="<?php echo esc_url( get_term_link( $location ) ); ?>"><?php echo esc_html( sprintf( findewerkstatt_t( 'Alle Werkstätten %s ansehen →' ), $location_phrase ) ); ?></a></p>
        <?php endif; ?>
    </div>

    <?php if ( have_posts() ) : ?>
        <p style="margin-bottom:16px;"><?php echo esc_html( sprintf( findewerkstatt_t( 1 === (int) $wp_query->found_posts ? '%s Werkstatt gefunden' : '%s Werkstätten gefunden' ), number_format_i18n( $wp_query->found_posts ) ) ); ?></p>
        <?php FindeWerkstatt_Programmatic_SEO::render_workshop_cards( $wp_query ); ?>
        <div style="margin-top:32px;"><?php the_posts_pagination( array( 'mid_size' => 2, 'prev_text' => findewerkstatt_t( '← Zurück' ), 'next_text' => findewerkstatt_t( 'Weiter →' ) ) ); ?></div>
    <?php else : ?>
        <div class="fw-box" style="text-align:center;">
            <h2><?php echo esc_html( findewerkstatt_t( 'Für diese Auswahl sind noch keine Werkstätten eingetragen' ) ); ?></h2>
            <p style="margin:12px 0 20px;"><?php echo esc_html( findewerkstatt_t( 'Erweitern Sie Ihre Suche oder tragen Sie Ihren Betrieb mit seinen Leistungen ein.' ) ); ?></p>
            <a href="<?php echo esc_url( get_post_type_archive_link( 'mechanic' ) ); ?>" class="fw-btn fw-btn-outline"><?php echo esc_html( findewerkstatt_t( 'Alle Werkstätten durchsuchen' ) ); ?></a>
        </div>
    <?php endif; ?>

    <div class="fw-box" style="margin-top:30px; text-align:center;">
        <h2><?php echo esc_html( findewerkstatt_t( 'Ihre Werkstatt fehlt im Verzeichnis?' ) ); ?></h2>
        <p style="margin:12px 0 20px;"><?php echo esc_html( findewerkstatt_t( 'Tragen Sie Ihren Betrieb mit Standort, Leistungen und Kontaktdaten ein.' ) ); ?></p>
        <a href="<?php echo esc_url( findewerkstatt_page_url( 'werkstatt-anmelden' ) ); ?>" class="fw-btn fw-btn-primary"><?php echo esc_html( findewerkstatt_t( 'Werkstatt eintragen' ) ); ?></a>
    </div>
</main>
<?php get_footer();
