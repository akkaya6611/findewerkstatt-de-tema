<?php get_header(); ?>

<main style="text-align: center; padding: 5rem 0;">
    <h1 style="font-size: 4rem; margin-bottom: 1rem; color: #f97316;">404</h1>
    <h2 style="margin-bottom: 2rem;">Sayfa Bulunamadı</h2>
    <p style="margin-bottom: 2rem; color: #6b7280;">Aradığınız tamirci veya sayfa sistemimizde mevcut değil ya da kaldırılmış olabilir.</p>
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="padding: 1rem 2rem; background-color: #1e3a8a; color: white; text-decoration: none; border-radius: 8px; font-weight: bold;">Anasayfaya Dön</a>
</main>

<?php get_footer(); ?>
