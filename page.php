<?php get_header(); ?>

<main>
    <?php
    while ( have_posts() ) :
        the_post();
        ?>
        <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
            <h1><?php the_title(); ?></h1>
            <div class="content">
                <?php the_content(); ?>
            </div>
        </div>
        <?php
    endwhile; // End of the loop.
    ?>
</main>

<?php get_footer(); ?>
