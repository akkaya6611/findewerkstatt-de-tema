<?php
/**
 * Werkstätten nach Ort, Region oder Bundesland.
 *
 * @package FindeWerkstatt
 */

global $wp_query;
$current_term = FindeWerkstatt_Programmatic_SEO::get_query_term( 'mechanic_city', $wp_query );
$location_content = $current_term ? findewerkstatt_location_content( $current_term, $wp_query ) : null;
$context = $location_content ? $location_content['context'] : array( 'name' => 'Deutschland', 'kind' => 'region', 'parent_name' => '' );
$copy = $location_content ? $location_content['copy'] : findewerkstatt_location_copy( $context );
$location_name = $context['name'];
$location_phrase = findewerkstatt_location_phrase( $context );
$parent_term = $context['parent'] ?? null;
$child_terms = $context['children'] ?? array();
$geo = $location_content['geo'] ?? null;
$related_terms = $current_term ? findewerkstatt_location_related_terms( $context ) : array();
$services = FindeWerkstatt_German_Data::get_categories();
$brands = FindeWerkstatt_German_Data::get_car_brands();
$visible_workshop_ids = wp_list_pluck( $wp_query->posts, 'ID' );
$visible_services = $visible_workshop_ids ? wp_get_object_terms( $visible_workshop_ids, 'service_type', array( 'orderby' => 'name', 'order' => 'ASC' ) ) : array();
$visible_brands = $visible_workshop_ids ? wp_get_object_terms( $visible_workshop_ids, 'car_brand', array( 'orderby' => 'name', 'order' => 'ASC' ) ) : array();
$visible_services = is_wp_error( $visible_services ) ? array() : $visible_services;
$visible_brands = is_wp_error( $visible_brands ) ? array() : $visible_brands;
$workshop_count = (int) $wp_query->found_posts;
get_header();
?>
<main id="main-content" class="fw-container fw-page-content fw-city-page">
    <nav class="fw-breadcrumbs" aria-label="<?php echo esc_attr( findewerkstatt_location_translate( 'Brotkrümelnavigation' ) ); ?>">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( findewerkstatt_location_translate( 'Startseite' ) ); ?></a><span aria-hidden="true">›</span>
        <a href="<?php echo esc_url( get_post_type_archive_link( 'mechanic' ) ); ?>"><?php echo esc_html( findewerkstatt_location_translate( 'Werkstätten' ) ); ?></a>
        <?php if ( $current_term ) : foreach ( array_reverse( get_ancestors( $current_term->term_id, 'mechanic_city', 'taxonomy' ) ) as $ancestor_id ) :
            $ancestor = get_term( $ancestor_id, 'mechanic_city' );
            if ( ! $ancestor || is_wp_error( $ancestor ) ) { continue; } ?>
            <span aria-hidden="true">›</span><a href="<?php echo esc_url( get_term_link( $ancestor ) ); ?>"><?php echo esc_html( findewerkstatt_location_display_name( $ancestor ) ); ?></a>
        <?php endforeach; endif; ?>
        <span aria-hidden="true">›</span><span aria-current="page"><?php echo esc_html( $location_name ); ?></span>
    </nav>

    <div class="fw-box fw-page-heading" style="margin-bottom:30px;">
        <p class="fw-text-muted" style="margin-bottom:8px;"><?php echo esc_html( findewerkstatt_location_translate( 'Werkstätten nach Standort' ) ); ?></p>
        <h1><?php echo esc_html( $copy['title'] ); ?></h1>
        <p><?php echo esc_html( $copy['intro'] ); ?></p>
        <?php if ( $parent_term ) : ?>
            <p style="margin-top:12px;"><?php echo esc_html( findewerkstatt_location_translate( 'Übergeordnetes Gebiet:' ) ); ?> <a href="<?php echo esc_url( get_term_link( $parent_term ) ); ?>"><?php echo esc_html( $context['parent_name'] ); ?></a></p>
        <?php endif; ?>
        <p class="fw-result-count" style="margin-top:12px; margin-bottom:0;"><?php echo esc_html( sprintf( findewerkstatt_location_translate( '%1$s veröffentlichte Werkstattprofile %2$s' ), number_format_i18n( $workshop_count ), $location_phrase ) ); ?></p>
        <?php if ( $child_terms ) : ?>
            <div style="margin-top:24px; padding-top:20px; border-top:1px solid var(--fw-border);">
                <h2 style="font-size:16px; margin-bottom:12px;"><?php echo esc_html( 'city_state' === $context['kind'] ? sprintf( findewerkstatt_location_translate( 'Bezirke in %s' ), $location_name ) : findewerkstatt_location_translate( 'Weitere Standortverzeichnisse' ) ); ?></h2>
                <?php if ( count( $child_terms ) > 24 ) : ?><details><summary style="cursor:pointer; margin-bottom:12px;"><?php echo esc_html( sprintf( findewerkstatt_location_translate( 'Alle %s Orte und Regionen anzeigen' ), number_format_i18n( count( $child_terms ) ) ) ); ?></summary><?php endif; ?>
                <div style="display:flex; flex-wrap:wrap; gap:8px;" aria-label="<?php echo esc_attr( findewerkstatt_location_translate( 'Ortsverzeichnisse' ) ); ?>">
                    <?php foreach ( $child_terms as $child ) : ?>
                        <a href="<?php echo esc_url( get_term_link( $child ) ); ?>" class="fw-badge fw-badge-service"><?php echo esc_html( findewerkstatt_location_display_name( $child ) ); ?></a>
                    <?php endforeach; ?>
                </div>
                <?php if ( count( $child_terms ) > 24 ) : ?></details><?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ( $current_term ) : ?>
        <section aria-labelledby="city-search-heading" style="margin-bottom:30px;">
            <h2 id="city-search-heading" style="font-size:22px; margin-bottom:16px;"><?php echo esc_html( sprintf( findewerkstatt_location_translate( 'Werkstattsuche %s eingrenzen' ), $location_phrase ) ); ?></h2>
            <form action="<?php echo esc_url( get_post_type_archive_link( 'mechanic' ) ); ?>" method="get" class="fw-filter-form fw-box">
                <input type="hidden" name="post_type" value="mechanic">
                <?php findewerkstatt_render_location_picker( 'city-filter', findewerkstatt_resolve_location_filter( array( 'fw_city' => $current_term->slug ) ) ); ?>
                <div class="fw-form-field"><label for="city-filter-service"><?php echo esc_html( findewerkstatt_location_translate( 'Leistung' ) ); ?></label><select id="city-filter-service" name="fw_service"><option value=""><?php echo esc_html( findewerkstatt_location_translate( 'Alle Leistungen' ) ); ?></option><?php foreach ( $services as $slug => $service ) : ?><option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( findewerkstatt_location_translate( $service['name'] ) ); ?></option><?php endforeach; ?></select></div>
                <div class="fw-form-field"><label for="city-filter-brand"><?php echo esc_html( findewerkstatt_location_translate( 'Fahrzeugmarke' ) ); ?></label><select id="city-filter-brand" name="fw_brand"><option value=""><?php echo esc_html( findewerkstatt_location_translate( 'Alle Marken' ) ); ?></option><?php foreach ( $brands as $slug => $brand ) : ?><option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $brand ); ?></option><?php endforeach; ?></select></div>
                <button type="submit" class="fw-btn fw-btn-primary"><?php echo esc_html( findewerkstatt_location_translate( 'Suchen' ) ); ?></button>
            </form>
        </section>
    <?php endif; ?>

    <section aria-labelledby="city-results-heading">
        <h2 id="city-results-heading" style="font-size:24px; margin-bottom:16px;"><?php echo esc_html( sprintf( findewerkstatt_location_translate( 'Werkstattprofile %s' ), $location_phrase ) ); ?></h2>
    <?php if ( $wp_query->have_posts() ) : ?>
        <?php if ( $current_term && ( $visible_services || $visible_brands ) ) : ?>
            <div class="fw-box" style="margin-bottom:20px;">
                <h3 style="font-size:16px; margin-bottom:12px;"><?php echo esc_html( findewerkstatt_location_translate( 'Leistungen und Marken der angezeigten Betriebe' ) ); ?></h3>
                <div style="display:flex; flex-wrap:wrap; gap:8px;">
                    <?php foreach ( $visible_services as $service ) : ?><a class="fw-badge fw-badge-service" href="<?php echo esc_url( FindeWerkstatt_Programmatic_SEO::get_combination_url( $service, $current_term ) ); ?>"><?php echo esc_html( findewerkstatt_directory_term_title( $service, $current_term ) ); ?></a><?php endforeach; ?>
                    <?php foreach ( $visible_brands as $brand ) : ?><a class="fw-badge fw-badge-service" href="<?php echo esc_url( FindeWerkstatt_Programmatic_SEO::get_combination_url( $brand, $current_term ) ); ?>"><?php echo esc_html( findewerkstatt_directory_term_title( $brand, $current_term ) ); ?></a><?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php FindeWerkstatt_Programmatic_SEO::render_workshop_cards( $wp_query ); ?>
        <div class="fw-pagination"><?php the_posts_pagination( array( 'mid_size' => 2, 'prev_text' => findewerkstatt_location_translate( '← Zurück' ), 'next_text' => findewerkstatt_location_translate( 'Weiter →' ), 'screen_reader_text' => findewerkstatt_location_translate( 'Weitere Werkstätten' ) ) ); ?></div>
    <?php else : ?>
        <div class="fw-empty-state">
            <h3><?php echo esc_html( sprintf( findewerkstatt_location_translate( 'Noch keine Werkstätten %s eingetragen' ), $location_phrase ) ); ?></h3>
            <p style="margin:12px 0 20px;"><?php echo esc_html( findewerkstatt_location_translate( 'Für diesen Standort ist derzeit kein Werkstattprofil veröffentlicht. Unser Verzeichnis wird erweitert. Sie können Ihre Suche auf ein anderes Gebiet ausweiten oder Ihren Betrieb kostenlos eintragen.' ) ); ?></p>
            <div class="fw-empty-actions">
                <?php if ( $parent_term ) : ?><a class="fw-btn fw-btn-outline" href="<?php echo esc_url( get_term_link( $parent_term ) ); ?>"><?php echo esc_html( sprintf( findewerkstatt_location_translate( 'Verzeichnis für %s' ), $context['parent_name'] ) ); ?></a><?php endif; ?>
                <a class="fw-btn fw-btn-outline" href="<?php echo esc_url( get_post_type_archive_link( 'mechanic' ) ); ?>"><?php echo esc_html( findewerkstatt_location_translate( 'Alle Werkstätten durchsuchen' ) ); ?></a>
            </div>
        </div>
    <?php endif; ?>
    </section>

    <section class="fw-location-content" aria-labelledby="city-guide-heading">
        <div class="fw-box fw-location-guide">
            <h2 id="city-guide-heading"><?php echo esc_html( sprintf( findewerkstatt_location_translate( 'Autowerkstatt %s finden' ), $location_phrase ) ); ?></h2>
            <?php foreach ( $copy['sections'] as $section ) : ?>
                <h3><?php echo esc_html( $section['heading'] ); ?></h3>
                <p><?php echo esc_html( $section['text'] ); ?></p>
            <?php endforeach; ?>
        </div>
        <?php if ( $current_term ) :
            $kind_labels = array( 'state' => 'Bundesland', 'city_state' => 'Stadt und Bundesland', 'city' => 'Stadt oder Gemeinde', 'county' => 'Landkreis oder Kreis', 'region' => 'Region', 'borough' => 'Bezirk', 'district' => 'Stadtteil' ); ?>
            <aside class="fw-box fw-location-facts" aria-labelledby="city-location-heading">
                <h2 id="city-location-heading"><?php echo esc_html( findewerkstatt_location_translate( 'Standort im Überblick' ) ); ?></h2>
                <dl>
                    <dt><?php echo esc_html( findewerkstatt_location_translate( 'Standort' ) ); ?></dt><dd><?php echo esc_html( $location_name ); ?></dd>
                    <dt><?php echo esc_html( findewerkstatt_location_translate( 'Gebiet' ) ); ?></dt><dd><?php echo esc_html( findewerkstatt_location_translate( $kind_labels[ $context['kind'] ] ) ); ?></dd>
                    <dt><?php echo esc_html( findewerkstatt_location_translate( 'Land' ) ); ?></dt><dd><?php echo esc_html( findewerkstatt_location_translate( 'Deutschland' ) ); ?></dd>
                    <?php if ( $context['state'] && $context['state']->term_id !== $current_term->term_id ) : ?>
                        <dt><?php echo esc_html( findewerkstatt_location_translate( 'Bundesland' ) ); ?></dt><dd><a href="<?php echo esc_url( get_term_link( $context['state'] ) ); ?>"><?php echo esc_html( $context['state_name'] ); ?></a></dd>
                    <?php endif; ?>
                    <?php if ( $parent_term && ( ! $context['state'] || $parent_term->term_id !== $context['state']->term_id ) ) : ?>
                        <dt><?php echo esc_html( findewerkstatt_location_translate( in_array( $context['kind'], array( 'borough', 'district' ), true ) ? 'Stadt' : 'Übergeordnetes Gebiet' ) ); ?></dt><dd><a href="<?php echo esc_url( get_term_link( $parent_term ) ); ?>"><?php echo esc_html( $context['parent_name'] ); ?></a></dd>
                    <?php endif; ?>
                    <?php if ( $geo ) : ?>
                        <dt><?php echo esc_html( findewerkstatt_location_translate( 'Breitengrad' ) ); ?></dt><dd><?php echo esc_html( number_format_i18n( $geo['lat'], 6 ) . '° ' . findewerkstatt_location_translate( 'N' ) ); ?></dd>
                        <dt><?php echo esc_html( findewerkstatt_location_translate( 'Längengrad' ) ); ?></dt><dd><?php echo esc_html( number_format_i18n( $geo['lng'], 6 ) . '° ' . findewerkstatt_location_translate( 'O' ) ); ?></dd>
                    <?php endif; ?>
                </dl>
                <?php if ( $geo ) : ?>
                    <p class="fw-muted"><?php echo esc_html( findewerkstatt_location_translate( 'Die Koordinaten bezeichnen den geografischen Ortsmittelpunkt. Die genaue Betriebsadresse finden Sie im jeweiligen Werkstattprofil.' ) ); ?></p>
                    <?php if ( $geo['source_url'] ) : ?>
                        <p class="fw-muted"><?php echo esc_html( findewerkstatt_location_translate( 'Quelle:' ) ); ?> <a href="<?php echo esc_url( $geo['source_url'] ); ?>"><?php echo esc_html( findewerkstatt_location_translate( '2024-12-31' === $geo['source_date'] ? 'Destatis-Gemeindeverzeichnis, Stand 31.12.2024' : 'Geprüfte Ortsdaten' ) ); ?></a></p>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if ( $related_terms ) : ?>
                    <h3><?php echo esc_html( 'borough' === $context['kind'] ? sprintf( findewerkstatt_location_translate( 'Weitere Bezirke in %s' ), $context['parent_name'] ) : sprintf( findewerkstatt_location_translate( 'Weitere Städte %s' ), findewerkstatt_location_phrase( findewerkstatt_location_context( $context['state'] ) ) ) ); ?></h3>
                    <ul class="fw-location-links">
                        <?php foreach ( $related_terms as $related ) : ?><li><a href="<?php echo esc_url( get_term_link( $related['term'] ) ); ?>"><?php echo esc_html( $related['name'] ); ?></a></li><?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </aside>
        <?php endif; ?>
    </section>

    <div class="fw-box fw-location-faq">
        <h2><?php echo esc_html( sprintf( findewerkstatt_location_translate( 'Fragen zur Werkstattsuche %s' ), $location_phrase ) ); ?></h2>
        <?php foreach ( $copy['faqs'] as $entry ) : ?>
            <div class="fw-faq-item">
                <button type="button" class="fw-faq-question" aria-expanded="true">
                    <span><?php echo esc_html( $entry['question'] ); ?></span><span class="fw-faq-icon" aria-hidden="true">+</span>
                </button>
                <div class="fw-faq-answer"><?php echo esc_html( $entry['answer'] ); ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="fw-box" style="margin-top:30px; text-align:center;">
        <h2><?php echo esc_html( $copy['cta_heading'] ); ?></h2>
        <p style="margin:12px 0 20px;"><?php echo esc_html( $copy['cta_text'] ); ?></p>
        <a href="<?php echo esc_url( findewerkstatt_page_url( 'werkstatt-anmelden' ) ); ?>" class="fw-btn fw-btn-primary"><?php echo esc_html( findewerkstatt_location_translate( 'Werkstatt eintragen' ) ); ?></a>
    </div>
</main>
<?php get_footer();
