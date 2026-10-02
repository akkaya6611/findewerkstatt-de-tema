<?php
get_header();
global $wp_query;
$search_term = get_search_query( false );
?>
<main id="main-content" class="fw-container fw-page-content">
    <div class="fw-page-heading"><h1><?php echo esc_html( findewerkstatt_t( 'Werkstattsuche' ) ); ?></h1><p><?php echo esc_html( '' !== trim( $search_term ) ? sprintf( findewerkstatt_t( 'Suchergebnisse für „%s“' ), $search_term ) : findewerkstatt_t( 'Suchen Sie nach einem Werkstattnamen oder einem Suchbegriff.' ) ); ?></p></div>
    <form role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" class="fw-search-inline" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Werkstätten nach Namen suchen' ) ); ?>">
        <div class="fw-form-field"><label for="workshop-search"><?php echo esc_html( findewerkstatt_t( 'Werkstattname oder Suchbegriff' ) ); ?></label><input id="workshop-search" type="search" name="s" value="<?php echo esc_attr( $search_term ); ?>" placeholder="<?php echo esc_attr( findewerkstatt_t( 'Werkstattname oder Suchbegriff' ) ); ?>" enterkeyhint="search"></div>
        <input type="hidden" name="post_type" value="mechanic">
        <?php findewerkstatt_render_workshop_language_filter( isset( $_GET['fw_spoken'] ) && is_string( $_GET['fw_spoken'] ) ? sanitize_key( wp_unslash( $_GET['fw_spoken'] ) ) : '' ); ?>
        <button class="fw-btn fw-btn-primary" type="submit"><?php echo esc_html( findewerkstatt_t( 'Suchen' ) ); ?></button>
    </form>
    <?php if ( have_posts() ) : ?>
        <p class="fw-result-count"><?php echo esc_html( number_format_i18n( $wp_query->found_posts ) ); ?> <?php echo $wp_query->found_posts === 1 ? findewerkstatt_t( 'Werkstatt gefunden' ) : findewerkstatt_t( 'Werkstätten gefunden' ); ?></p>
        <div class="fw-workshops-grid"><?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/workshop-card' ); endwhile; ?></div>
        <div class="fw-pagination"><?php the_posts_pagination( array( 'prev_text' => findewerkstatt_t( '← Zurück' ), 'next_text' => findewerkstatt_t( 'Weiter →' ), 'screen_reader_text' => findewerkstatt_t( 'Weitere Ergebnisse' ) ) ); ?></div>
    <?php else : ?><div class="fw-empty-state"><h2><?php echo esc_html( findewerkstatt_t( 'Keine passenden Werkstätten gefunden' ) ); ?></h2><p><?php echo esc_html( findewerkstatt_t( 'Versuchen Sie einen anderen Suchbegriff oder wählen Sie im Werkstattverzeichnis zuerst das Bundesland und anschließend Ihre Stadt. Leistung und Fahrzeugmarke können Sie zusätzlich filtern.' ) ); ?></p><a class="fw-btn fw-btn-primary" href="<?php echo esc_url( get_post_type_archive_link( 'mechanic' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Zum Werkstattverzeichnis' ) ); ?></a></div><?php endif; ?>
</main>
<?php get_footer(); ?>
