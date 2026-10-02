<?php
/**
 * FindeWerkstatt.de — Standard-Seitenvorlage (Page Template)
 * 
 * @package FindeWerkstatt
 * @version 2.0.0
 */

get_header();
?>

<main id="main-content" class="fw-container fw-page-content">
    <?php while ( have_posts() ) : the_post(); ?>
        <article class="fw-box" style="max-width:860px; margin:0 auto;">
            <h1 style="font-size:32px; font-weight:900; color:var(--fw-primary); margin-bottom:24px; padding-bottom:16px; border-bottom:1px solid var(--fw-border);">
                <?php the_title(); ?>
            </h1>

            <div class="fw-prose">
                <?php the_content(); ?>
            </div>
            <?php wp_link_pages( array( 'before' => '<nav class="fw-pagination" aria-label="' . esc_attr( findewerkstatt_t( 'Seiten dieses Inhalts' ) ) . '">' . esc_html( findewerkstatt_t( 'Seiten:' ) ) . ' ', 'after' => '</nav>' ) ); ?>
        </article>
    <?php endwhile; ?>
</main>

<?php get_footer();
