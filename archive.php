<?php
/**
 * FindeWerkstatt.de — Universal Archive Fallback
 * 
 * @package FindeWerkstatt
 * @version 2.0.0
 */

if ( is_post_type_archive( 'mechanic' ) || is_tax( 'mechanic_district' ) ) {
    get_template_part( 'archive-mechanic' );
    return;
}

get_header();
?>
<main id="main-content" class="fw-container fw-page-content">
    <div class="fw-page-heading">
        <h1><?php if ( is_home() ) { echo esc_html( ( get_option( 'page_for_posts' ) ? get_the_title( (int) get_option( 'page_for_posts' ) ) : '' ) ?: findewerkstatt_t( 'Beiträge' ) ); } else { the_archive_title(); } ?></h1>
        <?php if ( ! is_home() ) { the_archive_description( '<div class="fw-prose">', '</div>' ); } ?>
    </div>
    <?php if ( have_posts() ) : ?>
        <?php while ( have_posts() ) : the_post(); ?><article class="fw-box"><h2><a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ?: findewerkstatt_t( 'Beitrag ansehen' ) ); ?></a></h2><?php the_excerpt(); ?></article><?php endwhile; ?>
        <div class="fw-pagination"><?php the_posts_pagination( array( 'prev_text' => findewerkstatt_t( '← Zurück' ), 'next_text' => findewerkstatt_t( 'Weiter →' ), 'screen_reader_text' => findewerkstatt_t( 'Weitere Beiträge' ) ) ); ?></div>
    <?php else : ?><div class="fw-empty-state"><h2><?php echo esc_html( findewerkstatt_t( 'Noch keine Beiträge vorhanden' ) ); ?></h2><p><?php echo esc_html( findewerkstatt_t( 'In diesem Bereich sind derzeit keine Beiträge veröffentlicht.' ) ); ?></p><a class="fw-btn fw-btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Zur Startseite' ) ); ?></a></div><?php endif; ?>
</main>
<?php get_footer();
