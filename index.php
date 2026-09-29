<?php
/**
 * FindeWerkstatt.de — Index Fallback Template
 * 
 * @package FindeWerkstatt
 * @version 2.0.0
 */

get_header();

if ( is_singular() ) {
    while ( have_posts() ) : the_post();
        ?>
        <div class="fw-container" style="padding:40px 20px;">
            <article class="fw-box" style="max-width:860px; margin:0 auto;">
                <h1><?php the_title(); ?></h1>
                <div><?php the_content(); ?></div>
            </article>
        </div>
        <?php
    endwhile;
} else {
    get_template_part( 'archive-mechanic' );
}

get_footer();
