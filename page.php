<?php
/**
 * FindeWerkstatt.de — Standard-Seitenvorlage (Page Template)
 * 
 * @package FindeWerkstatt
 * @version 2.0.0
 */

get_header();
?>

<div class="fw-container" style="padding-top:40px; padding-bottom:70px;">
    <?php while ( have_posts() ) : the_post(); ?>
        <article class="fw-box" style="max-width:860px; margin:0 auto;">
            <h1 style="font-size:32px; font-weight:900; color:var(--fw-primary); margin-bottom:24px; padding-bottom:16px; border-bottom:1px solid var(--fw-border);">
                <?php the_title(); ?>
            </h1>

            <div style="font-size:16px; line-height:1.8; color:#334155;">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; ?>
</div>

<?php
get_footer();
