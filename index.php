<?php
/**
 * FindeWerkstatt.de — Index Fallback Template
 * 
 * @package FindeWerkstatt
 * @version 2.0.0
 */

if ( is_singular() ) {
    get_header();
    ?>
    <main id="main-content" class="fw-container fw-page-content">
    <?php
    while ( have_posts() ) : the_post();
        ?>
            <article class="fw-box" style="max-width:860px; margin:0 auto;">
                <div class="fw-page-heading"><h1><?php the_title(); ?></h1></div>
                <div class="fw-prose"><?php the_content(); ?></div>
                <?php wp_link_pages( array( 'before' => '<nav class="fw-pagination" aria-label="' . esc_attr( findewerkstatt_t( 'Seiten dieses Beitrags' ) ) . '">' . esc_html( findewerkstatt_t( 'Seiten:' ) ) . ' ', 'after' => '</nav>' ) ); ?>
            </article>
        <?php
    endwhile;
    ?>
    </main>
    <?php } else { get_template_part( 'archive' ); return; } get_footer();
