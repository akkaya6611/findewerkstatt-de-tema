<?php
/** Filterable workshop directory. */
get_header();
global $wp_query;
$services = FindeWerkstatt_German_Data::get_categories();
$brands = FindeWerkstatt_German_Data::get_car_brands();
$filters = array();
foreach ( array( 'fw_city', 'fw_service', 'fw_brand' ) as $key ) { $filters[ $key ] = isset( $_GET[ $key ] ) && is_string( $_GET[ $key ] ) ? sanitize_title( wp_unslash( $_GET[ $key ] ) ) : ''; }
$location_filter = findewerkstatt_resolve_location_filter( $_GET );
$selected_location = $location_filter['valid'] && $location_filter['location']
    ? get_term_by( 'slug', $location_filter['location'], 'mechanic_city' ) : null;
$location_phrase = $selected_location && ! is_wp_error( $selected_location )
    ? findewerkstatt_location_phrase( findewerkstatt_location_context( $selected_location ) ) : findewerkstatt_t( 'in Deutschland' );
if ( is_tax( 'mechanic_district' ) ) {
    $district = get_queried_object();
    if ( $district instanceof WP_Term ) { $location_phrase = sprintf( findewerkstatt_t( 'im Stadtteil %s' ), $district->name ); }
}
?>
<main id="main-content" class="fw-container fw-page-content">
    <nav class="fw-breadcrumbs" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Brotkrümelnavigation' ) ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Startseite' ) ); ?></a><span aria-hidden="true">›</span><span><?php echo esc_html( findewerkstatt_t( 'Werkstätten' ) ); ?></span></nav>
    <div class="fw-page-heading"><h1><?php echo esc_html( sprintf( findewerkstatt_t( 'Kfz-Werkstätten %s' ), $location_phrase ) ); ?></h1><p><?php echo esc_html( findewerkstatt_t( 'Wählen Sie Bundesland und Ort sowie die gewünschte Leistung oder Fahrzeugmarke.' ) ); ?></p></div>
    <?php if ( ! $location_filter['valid'] ) : ?><p class="fw-form-notice fw-form-notice-error" role="alert"><?php echo esc_html( findewerkstatt_t( 'Bitte prüfen Sie Ihre Ortsauswahl. Die Stadt muss zum ausgewählten Bundesland gehören.' ) ); ?></p><?php endif; ?>
    <form action="<?php echo esc_url( get_post_type_archive_link( 'mechanic' ) ); ?>" method="get" class="fw-filter-form fw-box">
        <input type="hidden" name="post_type" value="mechanic">
        <?php findewerkstatt_render_location_picker( 'filter', $location_filter ); ?>
        <div class="fw-form-field"><label for="filter-service"><?php echo esc_html( findewerkstatt_t( 'Leistung' ) ); ?></label><select id="filter-service" name="fw_service"><option value=""><?php echo esc_html( findewerkstatt_t( 'Alle Leistungen' ) ); ?></option><?php foreach ( $services as $slug => $service ) : ?><option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $filters['fw_service'], $slug ); ?>><?php echo esc_html( findewerkstatt_t( $service['name'] ) ); ?></option><?php endforeach; ?></select></div>
        <div class="fw-form-field"><label for="filter-brand"><?php echo esc_html( findewerkstatt_t( 'Fahrzeugmarke' ) ); ?></label><select id="filter-brand" name="fw_brand"><option value=""><?php echo esc_html( findewerkstatt_t( 'Alle Marken' ) ); ?></option><?php foreach ( $brands as $slug => $brand ) : ?><option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $filters['fw_brand'], $slug ); ?>><?php echo esc_html( $brand ); ?></option><?php endforeach; ?></select></div>
        <?php findewerkstatt_render_workshop_language_filter( isset( $_GET['fw_spoken'] ) && is_string( $_GET['fw_spoken'] ) ? sanitize_key( wp_unslash( $_GET['fw_spoken'] ) ) : '' ); ?>
        <button type="submit" class="fw-btn fw-btn-primary"><?php echo esc_html( findewerkstatt_t( 'Suchen' ) ); ?></button>
    </form>
    <?php if ( have_posts() ) : ?>
        <p class="fw-result-count"><?php echo esc_html( number_format_i18n( $wp_query->found_posts ) ); ?> <?php echo $wp_query->found_posts === 1 ? findewerkstatt_t( 'Werkstatt gefunden' ) : findewerkstatt_t( 'Werkstätten gefunden' ); ?></p>
        <div class="fw-workshops-grid"><?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/workshop-card' ); endwhile; ?></div>
        <div class="fw-pagination"><?php the_posts_pagination( array( 'mid_size' => 2, 'prev_text' => findewerkstatt_t( '← Zurück' ), 'next_text' => findewerkstatt_t( 'Weiter →' ), 'screen_reader_text' => findewerkstatt_t( 'Weitere Ergebnisse' ) ) ); ?></div>
    <?php else : ?>
        <div class="fw-empty-state"><h2><?php echo esc_html( findewerkstatt_t( 'Keine Werkstätten gefunden' ) ); ?></h2><p><?php echo esc_html( findewerkstatt_t( 'Für diese Auswahl sind noch keine Betriebe eingetragen. Ändern Sie die Filter oder tragen Sie Ihre eigene Werkstatt ein.' ) ); ?></p><div class="fw-empty-actions"><a href="<?php echo esc_url( get_post_type_archive_link( 'mechanic' ) ); ?>" class="fw-btn fw-btn-primary"><?php echo esc_html( findewerkstatt_t( 'Filter zurücksetzen' ) ); ?></a><a href="<?php echo esc_url( findewerkstatt_page_url( 'werkstatt-anmelden' ) ); ?>" class="fw-btn fw-btn-outline"><?php echo esc_html( findewerkstatt_t( 'Werkstatt eintragen' ) ); ?></a></div></div>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
