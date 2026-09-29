<?php get_header(); ?>

<style>
/* ===================================================
   TEKİL BLOG YAZISI STİLLERİ (SINGLE.PHP)
=================================================== */
.single-post-hero {
    background: linear-gradient(135deg, #1a0533 0%, #0d1b4b 35%, #0c2d6b 65%, #0f172a 100%);
    padding: 70px 20px 85px;
    color: white;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.single-post-hero .container {
    max-width: 860px;
    margin: 0 auto;
    position: relative;
    z-index: 2;
}
.single-post-meta {
    display: inline-flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 20px;
    font-size: 14px;
    color: #cbd5e1;
    flex-wrap: wrap;
    justify-content: center;
}
.single-post-cat-badge {
    background: linear-gradient(135deg, rgba(249,115,22,0.25), rgba(139,92,246,0.25));
    color: #fdba74;
    border: 1px solid rgba(249,115,22,0.35);
    padding: 6px 16px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 700;
}
.single-post-hero h1 {
    font-size: 40px;
    font-weight: 800;
    line-height: 1.25;
    margin: 0 0 16px;
    letter-spacing: -0.5px;
}

/* Makale Gövdesi */
.single-post-layout {
    padding: 60px 20px 90px;
    background: #f8fafc;
}
.single-post-container {
    max-width: 860px;
    margin: 0 auto;
}
.single-post-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 6px 24px rgba(0,0,0,0.04);
    margin-bottom: 40px;
}
.single-featured-image {
    width: 100%;
    height: auto;
    aspect-ratio: 860 / 440;
    max-height: 440px;
    object-fit: cover;
    display: block;
}
.single-post-content {
    padding: 50px;
    color: #334155;
    font-size: 16.5px;
    line-height: 1.85;
}
.single-post-content h2 {
    font-size: 26px;
    font-weight: 800;
    color: #0f172a;
    margin: 36px 0 16px;
    line-height: 1.3;
}
.single-post-content h3 {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin: 28px 0 14px;
}
.single-post-content p {
    margin-bottom: 22px;
}
.single-post-content ul,
.single-post-content ol {
    margin: 0 0 24px 24px;
    padding: 0;
}
.single-post-content li {
    margin-bottom: 10px;
}

/* Paylaş ve Geri Dön Barı */
.single-post-footer-bar {
    padding: 24px 50px 36px;
    border-top: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px;
}
.back-to-blog-link {
    color: #f97316;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 15px;
}
.back-to-blog-link:hover {
    color: #ea580c;
}
.post-share-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
}
.share-label {
    font-size: 13px;
    color: #64748b;
    font-weight: 600;
}
.share-btn {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #f1f5f9;
    color: #475569;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.2s;
    font-size: 15px;
}
.share-btn:hover {
    background: #f97316;
    color: white;
    transform: scale(1.08);
}

/* CTA KUTUSU */
.post-mechanic-cta {
    background: linear-gradient(135deg, #0f172a, #1e293b);
    border-radius: 20px;
    padding: 36px;
    color: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    box-shadow: 0 10px 30px rgba(15,23,42,0.15);
}
.cta-info h3 {
    margin: 0 0 8px;
    font-size: 22px;
    font-weight: 800;
}
.cta-info p {
    margin: 0;
    color: #94a3b8;
    font-size: 15px;
}
.cta-action-btn {
    background: linear-gradient(135deg, #f97316, #ea580c);
    color: white;
    font-weight: 700;
    font-size: 15px;
    padding: 14px 28px;
    border-radius: 12px;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.25s;
    box-shadow: 0 4px 14px rgba(249,115,22,0.4);
}
.cta-action-btn:hover {
    background: linear-gradient(135deg, #ea580c, #c2410c);
    box-shadow: 0 6px 18px rgba(249,115,22,0.55);
    transform: translateY(-2px);
}
.cta-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(14,165,233,0.6);
}

/* ToC (İçindekiler Tablosu) Stilleri */
.post-toc-box {
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    padding: 20px 24px;
    margin: 0 0 35px 0;
}
.toc-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    font-weight: 700;
    font-size: 15.5px;
    color: #0f172a;
    user-select: none;
}
.toc-list {
    margin: 14px 0 0 0;
    padding-left: 20px;
    line-height: 1.8;
}
.toc-list.collapsed {
    display: none;
}
.toc-list li {
    margin-bottom: 6px;
    font-size: 14.5px;
}
.toc-list li a {
    color: #334155;
    text-decoration: none;
    transition: color 0.2s;
}
.toc-list li a:hover {
    color: #f97316;
    text-decoration: underline;
}
.toc-item-h3 {
    margin-left: 18px;
    list-style-type: circle;
}

/* E-E-A-T Yazar & Doğrulama Kutusu */
.single-post-author-box {
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 18px;
    padding: 24px;
    margin: 30px 0 10px;
    display: flex;
    gap: 20px;
    align-items: flex-start;
}
.author-avatar-img {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #f97316;
    flex-shrink: 0;
}
.author-info-wrap {
    flex: 1;
}
.author-trust-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
    font-size: 11.5px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    margin-bottom: 6px;
}
.author-name-title {
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 2px;
}
.author-role-sub {
    font-size: 12.5px;
    color: #f97316;
    font-weight: 600;
    margin-bottom: 8px;
}
.author-bio-p {
    font-size: 13.5px;
    color: #64748b;
    line-height: 1.6;
    margin: 0;
}

/* İlgili Yazılar Grid */
.related-posts-section {
    margin-top: 50px;
}
.related-posts-title {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.related-posts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}
.related-post-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
    text-decoration: none;
    transition: all 0.25s;
    display: flex;
    flex-direction: column;
}
.related-post-card:hover {
    transform: translateY(-4px);
    border-color: #f97316;
    box-shadow: 0 8px 24px rgba(0,0,0,0.06);
}
.related-post-img {
    width: 100%;
    height: 150px;
    object-fit: cover;
}
.related-post-info {
    padding: 16px;
}
.related-post-info h4 {
    margin: 0 0 8px;
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.4;
}
.related-post-info span {
    font-size: 12px;
    color: #64748b;
}

@media (max-width: 768px) {
    .single-post-hero h1 { font-size: 24px; }
    .single-featured-image { aspect-ratio: 16 / 9; height: auto; max-height: 240px; }
    .single-post-content { padding: 22px 18px; font-size: 15.5px; }
    .single-post-footer-bar { padding: 18px 20px; }
    .single-post-author-box { flex-direction: column; align-items: center; text-align: center; }
    .related-posts-grid { grid-template-columns: 1fr; }
    .post-mechanic-cta { flex-direction: column; text-align: center; padding: 26px 20px; }
    .cta-action-btn { width: 100%; text-align: center; }
}
</style>

<?php
while(have_posts()): the_post();
    $post_id = get_the_ID();
    $post_img = function_exists('ototamir_get_smart_post_image') 
        ? ototamir_get_smart_post_image($post_id, 1200, 630) 
        : (get_post_meta($post_id, '_custom_blog_image', true) ?: (get_the_post_thumbnail_url($post_id, 'full') ?: 'https://images.unsplash.com/photo-1487754180451-c456f719a1fc?auto=format&fit=crop&w=1200&q=80'));
    $cats = get_the_category();
    $cat_name = !empty($cats) ? $cats[0]->name : 'Oto Rehber';
    $cat_id = !empty($cats) ? $cats[0]->term_id : 0;
    $read_time = get_post_meta($post_id, '_blog_read_time', true) ?: '4 dk okuma';

    // Otomatik ToC ve İçerik Ayrıştırma
    $raw_content = apply_filters('the_content', get_the_content());
    $parsed_content_data = function_exists('ototamir_generate_toc_and_content') 
        ? ototamir_generate_toc_and_content($raw_content) 
        : array('toc' => '', 'content' => $raw_content);
?>

<?php
$single_blog_breadcrumbs = array(
    array(
        '@type'    => 'ListItem',
        'position' => 1,
        'name'     => 'Ana Sayfa',
        'item'     => home_url( '/' ),
    ),
    array(
        '@type'    => 'ListItem',
        'position' => 2,
        'name'     => 'Blog',
        'item'     => home_url( '/blog' ),
    ),
);

$sb_pos = 3;
if ( $cat_id ) {
    $single_blog_breadcrumbs[] = array(
        '@type'    => 'ListItem',
        'position' => $sb_pos++,
        'name'     => $cat_name,
        'item'     => get_category_link( $cat_id ),
    );
}

$single_blog_breadcrumbs[] = array(
    '@type'    => 'ListItem',
    'position' => $sb_pos++,
    'name'     => get_the_title(),
    'item'     => get_permalink(),
);

$single_blog_schema = array(
    array(
        '@context'         => 'https://schema.org',
        '@type'            => 'BlogPosting',
        'mainEntityOfPage' => array(
            '@type' => 'WebPage',
            '@id'   => get_permalink(),
        ),
        'headline'         => get_the_title(),
        'description'      => wp_trim_words( strip_tags( get_the_excerpt() ), 30 ),
        'image'            => $post_img,
        'datePublished'    => get_the_date( 'c' ),
        'dateModified'     => get_the_modified_date( 'c' ),
        'author'           => array(
            '@type'    => 'Person',
            'name'     => get_the_author(),
            'jobTitle' => 'Otomotiv Teknik Editörü',
            'url'      => get_author_posts_url( get_the_author_meta( 'ID' ) ),
        ),
        'publisher'        => array(
            '@type' => 'Organization',
            'name'  => 'OtoTamirciBul',
            'logo'  => array(
                '@type' => 'ImageObject',
                'url'   => get_template_directory_uri() . '/assets/images/logo-main.png',
            ),
        ),
    ),
    array(
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $single_blog_breadcrumbs,
    ),
);
?>
<!-- Schema.org BlogPosting & BreadcrumbList (JSON-LD) -->
<script type="application/ld+json">
<?php echo wp_json_encode( $single_blog_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ); ?>
</script>

<div class="single-post-hero">
    <div class="container">
        <!-- Semantik Breadcrumbs -->
        <nav class="single-breadcrumbs" style="font-size: 13px; color: rgba(255,255,255,0.7); display:flex; align-items:center; justify-content:center; flex-wrap:wrap; gap:6px; margin-bottom: 14px;" aria-label="Ekmek Kırıntıları">
            <a href="<?php echo esc_url( home_url('/') ); ?>" style="color: rgba(255,255,255,0.85); text-decoration: none;"><i class="fa-solid fa-house" style="font-size:11px;"></i> Ana Sayfa</a>
            <span>/</span>
            <a href="<?php echo esc_url( home_url('/blog') ); ?>" style="color: rgba(255,255,255,0.85); text-decoration: none;">Blog</a>
            <?php if ( $cat_id ) : ?>
                <span>/</span>
                <a href="<?php echo esc_url( get_category_link( $cat_id ) ); ?>" style="color: rgba(255,255,255,0.85); text-decoration: none;"><?php echo esc_html( $cat_name ); ?></a>
            <?php endif; ?>
        </nav>

        <div class="single-post-meta">
            <span class="single-post-cat-badge"><?php echo esc_html($cat_name); ?></span>
            <span><i class="fa-regular fa-clock"></i> <?php echo esc_html($read_time); ?></span>
            <span><i class="fa-regular fa-calendar"></i> <?php echo get_the_date('j F Y'); ?></span>
        </div>
        <h1><?php the_title(); ?></h1>
    </div>
</div>

<div class="single-post-layout">
    <div class="single-post-container">

        <article class="single-post-card">
            <?php if($post_img): ?>
                <img src="<?php echo esc_url($post_img); ?>" alt="<?php the_title_attribute(); ?>" class="single-featured-image" width="860" height="440" fetchpriority="high" loading="eager" decoding="async" onerror="this.onerror=null;this.src='<?php echo esc_url( get_template_directory_uri() . '/assets/images/placeholder-mechanic.webp' ); ?>';">
            <?php endif; ?>

            <div class="single-post-content">
                <?php 
                // GEO için İçindekiler Tablosu (ToC)
                if ( ! empty( $parsed_content_data['toc'] ) ) {
                    echo $parsed_content_data['toc'];
                }
                // Ayrıştırılmış ve ID çapalı içerik
                echo $parsed_content_data['content']; 

                // Google AdSense Blog Makale İçi / Sonu Reklamı
                if ( function_exists( 'ototamir_render_ad' ) ) {
                    echo ototamir_render_ad( 'blog' );
                }
                ?>

                <!-- E-E-A-T Yazar ve Uzman Doğrulama Kutusu -->
                <div class="single-post-author-box">
                    <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80" alt="<?php echo esc_attr( get_the_author() ); ?>" class="author-avatar-img" loading="lazy">
                    <div class="author-info-wrap">
                        <div class="author-trust-badge">
                            <i class="fa-solid fa-circle-check"></i> Uzman Tarafından Doğrulandı (E-E-A-T)
                        </div>
                        <h3 class="author-name-title"><?php echo esc_html( get_the_author() ); ?></h3>
                        <div class="author-role-sub">Otomotiv Teknik Editörü & Araştırmacı</div>
                        <p class="author-bio-p">
                            Oto mekanik sistemleri, periyodik bakım standartları ve sürücü güvenliği üzerine uzmanlaşmış içerik ekibimiz tarafından incelenmiş ve doğrulanmıştır. Son güncelleme: <?php echo get_the_modified_date('j F Y'); ?>.
                        </p>
                    </div>
                </div>
            </div>

            <div class="single-post-footer-bar">
                <a href="<?php echo esc_url(home_url('/blog')); ?>" class="back-to-blog-link">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Tüm Makalelere Dön</span>
                </a>

                <div class="post-share-wrap">
                    <span class="share-label">Paylaş:</span>
                    <a href="https://api.whatsapp.com/send?text=<?php echo urlencode(get_the_title() . ' ' . get_permalink()); ?>" target="_blank" class="share-btn" title="WhatsApp" rel="noopener">
                        <i class="fa-brands fa-whatsapp"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode(get_the_title()); ?>&url=<?php echo urlencode(get_permalink()); ?>" target="_blank" class="share-btn" title="Twitter" rel="noopener">
                        <i class="fa-brands fa-x-twitter"></i>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" target="_blank" class="share-btn" title="Facebook" rel="noopener">
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>
                </div>
            </div>
        </article>

        <!-- DAHİLİ LİNKLEME: İLGİLİ MAKALELER -->
        <?php
        $related_query = new WP_Query( array(
            'post_type'      => 'post',
            'posts_per_page' => 2,
            'post__not_in'   => array( $post_id ),
            'cat'            => $cat_id,
        ) );
        if ( $related_query->have_posts() ) :
        ?>
        <div class="related-posts-section">
            <h3 class="related-posts-title"><i class="fa-solid fa-book-bookmark" style="color:#f97316;"></i> İlgili Rehber Makaleleri</h3>
            <div class="related-posts-grid">
                <?php while ( $related_query->have_posts() ) : $related_query->the_post(); 
                    $r_img = function_exists('ototamir_get_smart_post_image') 
                        ? ototamir_get_smart_post_image( get_the_ID(), 500, 320 ) 
                        : (get_the_post_thumbnail_url( get_the_ID(), 'medium' ) ?: 'https://images.unsplash.com/photo-1487754180451-c456f719a1fc?auto=format&fit=crop&w=600&q=80');
                ?>
                <a href="<?php the_permalink(); ?>" class="related-post-card">
                    <img src="<?php echo esc_url( $r_img ); ?>" alt="<?php the_title_attribute(); ?>" class="related-post-img" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='<?php echo esc_url( get_template_directory_uri() . '/assets/images/placeholder-mechanic.webp' ); ?>';">
                    <div class="related-post-info">
                        <h4><?php the_title(); ?></h4>
                        <span><i class="fa-regular fa-calendar"></i> <?php echo get_the_date('j F Y'); ?></span>
                    </div>
                </a>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- CTA KUTUSU -->
        <div class="post-mechanic-cta" style="margin-top: 40px;">
            <div class="cta-info">
                <h3>Aracınız İçin Doğru Ustayı Bulun</h3>
                <p>Türkiye genelinde 5.000+ onaylı ve puanlanmış usta tek tık uzağınızda.</p>
            </div>
            <a href="<?php echo esc_url(home_url('/ustalar')); ?>" class="cta-action-btn">
                <i class="fa-solid fa-magnifying-glass"></i> Usta Ara
            </a>
        </div>

    </div>
</div>

<?php endwhile; ?>

<?php get_footer(); ?>

