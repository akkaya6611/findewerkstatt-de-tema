<?php
/*
Template Name: Blog
*/
get_header(); ?>

<style>
/* ===================================================
   BLOG SAYFASI STİLLERİ
=================================================== */
.blog-hero {
    background: linear-gradient(135deg, #1a0533 0%, #0d1b4b 35%, #0c2d6b 65%, #0f172a 100%);
    padding: 85px 20px 105px;
    text-align: center;
    color: white;
    position: relative;
    overflow: hidden;
}
.blog-hero::before {
    content: '';
    position: absolute;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(139,92,246,0.22) 0%, transparent 70%);
    top: -120px;
    left: -80px;
    border-radius: 50%;
}
.blog-hero::after {
    content: '';
    position: absolute;
    width: 450px;
    height: 450px;
    background: radial-gradient(circle, rgba(249,115,22,0.18) 0%, transparent 70%);
    bottom: -100px;
    right: -80px;
    border-radius: 50%;
}
.blog-hero .container {
    position: relative;
    z-index: 2;
    max-width: 860px;
    margin: 0 auto;
}
.blog-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, rgba(249,115,22,0.2), rgba(139,92,246,0.2));
    color: #fdba74;
    border: 1px solid rgba(249,115,22,0.3);
    padding: 8px 22px;
    border-radius: 50px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 22px;
    backdrop-filter: blur(4px);
}
.blog-hero h1 {
    font-size: 46px;
    font-weight: 800;
    line-height: 1.2;
    margin: 0 0 18px;
    letter-spacing: -1px;
}
.blog-hero h1 .hl {
    background: linear-gradient(90deg, #fb923c, #f472b6, #818cf8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.blog-hero p {
    font-size: 17px;
    color: #cbd5e1;
    line-height: 1.7;
    margin: 0 auto;
    max-width: 650px;
}

/* ===================================================
   BLOG İÇERİK ALANI
=================================================== */
.blog-main-section {
    padding: 80px 20px 100px;
    background: #f8fafc;
}
.blog-container {
    max-width: 1200px;
    margin: 0 auto;
}

/* Kategori Tabları */
.blog-cat-nav {
    display: flex;
    justify-content: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 50px;
}
.blog-cat-tab {
    padding: 10px 20px;
    border-radius: 50px;
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    color: #475569;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.25s ease;
}
.blog-cat-tab:hover,
.blog-cat-tab.active {
    background: #f97316;
    border-color: #f97316;
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(249, 115, 22, 0.35);
}

/* Öne Çıkan Yazı Kartı */
.featured-post-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 24px;
    overflow: hidden;
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    margin-bottom: 50px;
    box-shadow: 0 6px 24px rgba(0,0,0,0.04);
    transition: all 0.3s ease;
}
.featured-post-card:hover {
    border-color: #f97316;
    box-shadow: 0 16px 36px rgba(249, 115, 22, 0.12);
    transform: translateY(-4px);
}
.featured-post-img-wrap {
    position: relative;
    min-height: 380px;
    overflow: hidden;
}
.featured-post-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}
.featured-post-card:hover .featured-post-img-wrap img {
    transform: scale(1.04);
}
.featured-badge-float {
    position: absolute;
    top: 20px;
    left: 20px;
    background: linear-gradient(135deg, #f59e0b, #ef4444);
    color: white;
    font-size: 12px;
    font-weight: 700;
    padding: 6px 14px;
    border-radius: 50px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
.featured-post-body {
    padding: 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.post-meta-row {
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 13px;
    color: #64748b;
    margin-bottom: 14px;
}
.post-category-tag {
    background: #f0f9ff;
    color: #ea580c;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 6px;
    text-decoration: none;
}
.featured-post-body h2 {
    font-size: 28px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.3;
    margin: 0 0 16px;
}
.featured-post-body h2 a {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s;
}
.featured-post-body h2 a:hover {
    color: #f97316;
}
.featured-post-body p {
    font-size: 15.5px;
    color: #475569;
    line-height: 1.7;
    margin: 0 0 24px;
}
.read-more-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #f97316;
    font-weight: 700;
    font-size: 15px;
    text-decoration: none;
    transition: all 0.2s;
}
.read-more-btn:hover {
    color: #ea580c;
    gap: 12px;
}

/* Blog Grid */
.blog-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
}
.blog-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 20px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 4px 16px rgba(0,0,0,0.03);
    transition: all 0.3s ease;
}
.blog-card:hover {
    border-color: #f97316;
    transform: translateY(-6px);
    box-shadow: 0 16px 36px rgba(249, 115, 22, 0.12);
}
.blog-card-img-wrap {
    position: relative;
    height: 220px;
    overflow: hidden;
    background: #e2e8f0;
}
.blog-card-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.35s ease;
}
.blog-card:hover .blog-card-img-wrap img {
    transform: scale(1.06);
}
.blog-card-body {
    padding: 24px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}
.blog-card-body h3 {
    font-size: 19px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.4;
    margin: 0 0 12px;
}
.blog-card-body h3 a {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s;
}
.blog-card-body h3 a:hover {
    color: #f97316;
}
.blog-card-body p {
    font-size: 14px;
    color: #64748b;
    line-height: 1.65;
    margin: 0 0 20px;
    flex-grow: 1;
}
.blog-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 16px;
    border-top: 1px solid #f1f5f9;
    margin-top: auto;
}
.author-mini {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #64748b;
    font-weight: 600;
}
.author-mini i {
    color: #f97316;
}

/* ===================================================
   RESPONSIVE
=================================================== */
@media (max-width: 992px) {
    .featured-post-card { grid-template-columns: 1fr; }
    .featured-post-img-wrap { min-height: 280px; }
    .blog-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 650px) {
    .blog-hero h1 { font-size: 32px; }
    .blog-grid { grid-template-columns: 1fr; }
    .featured-post-body { padding: 24px; }
}
</style>

<!-- HERO BÖLÜMÜ -->
<section class="blog-hero">
    <div class="container">
        <div class="blog-badge">
            <i class="fa-solid fa-book-open"></i>
            Araç Bakım & Sürücü Rehberi
        </div>
        <h1>Oto Rehber & <br><span class="hl">Faydalı Makaleler</span></h1>
        <p>Aracınızın bakımından arıza teşhisine, tasarruf ipuçlarından güvenli sürüşe kadar uzman usta tavsiyeleri ve bilgilendirici rehberler burada.</p>
    </div>
</section>

<!-- BLOG LİSTESİ BÖLÜMÜ -->
<section class="blog-main-section">
    <div class="blog-container">

        <!-- Kategori Filtre Tabları -->
        <div class="blog-cat-nav">
            <?php
            $current_cat = isset($_GET['cat']) ? intval($_GET['cat']) : 0;
            $categories = get_categories(array('hide_empty' => true));
            ?>
            <a href="<?php echo esc_url(home_url('/blog')); ?>" class="blog-cat-tab <?php echo ($current_cat === 0) ? 'active' : ''; ?>">Tüm Yazılar</a>
            <?php foreach($categories as $cat): ?>
                <a href="<?php echo esc_url(add_query_arg('cat', $cat->term_id, home_url('/blog'))); ?>"
                   class="blog-cat-tab <?php echo ($current_cat === $cat->term_id) ? 'active' : ''; ?>">
                    <?php echo esc_html($cat->name); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $args = array(
            'post_type'      => 'post',
            'posts_per_page' => 10,
            'paged'          => $paged,
            'post_status'    => 'publish'
        );
        if($current_cat > 0) {
            $args['cat'] = $current_cat;
        }
        $blog_query = new WP_Query($args);

        if($blog_query->have_posts()):
            $post_index = 0;
            $featured_post = null;
            $regular_posts = array();

            while($blog_query->have_posts()) {
                $blog_query->the_post();
                if($post_index === 0 && $paged === 1 && $current_cat === 0) {
                    $featured_post = get_post();
                } else {
                    $regular_posts[] = get_post();
                }
                $post_index++;
            }
            wp_reset_postdata();

            // 1. ÖNE ÇIKAN YAZI (Varsa)
            if($featured_post):
                $feat_img = function_exists('ototamir_get_smart_post_image') 
                    ? ototamir_get_smart_post_image($featured_post->ID, 1200, 650) 
                    : (get_post_meta($featured_post->ID, '_custom_blog_image', true) ?: (get_the_post_thumbnail_url($featured_post->ID, 'large') ?: 'https://images.unsplash.com/photo-1487754180451-c456f719a1fc?auto=format&fit=crop&w=1200&q=80'));
                $feat_cats = get_the_category($featured_post->ID);
                $feat_cat_name = !empty($feat_cats) ? $feat_cats[0]->name : 'Genel';
                $feat_read_time = get_post_meta($featured_post->ID, '_blog_read_time', true) ?: '4 dk okuma';
            ?>
            <div class="featured-post-card">
                <div class="featured-post-img-wrap">
                    <img src="<?php echo esc_url($feat_img); ?>" alt="<?php echo esc_attr($featured_post->post_title); ?>" onerror="this.onerror=null;this.src='<?php echo esc_url( get_template_directory_uri() . '/assets/images/placeholder-mechanic.webp' ); ?>';">
                    <span class="featured-badge-float"><i class="fa-solid fa-star"></i> Öne Çıkan Rehber</span>
                </div>
                <div class="featured-post-body">
                    <div class="post-meta-row">
                        <span class="post-category-tag"><?php echo esc_html($feat_cat_name); ?></span>
                        <span><i class="fa-regular fa-clock"></i> <?php echo esc_html($feat_read_time); ?></span>
                        <span><i class="fa-regular fa-calendar"></i> <?php echo get_the_date('j F Y', $featured_post->ID); ?></span>
                    </div>
                    <h2><a href="<?php echo esc_url(get_permalink($featured_post->ID)); ?>"><?php echo esc_html($featured_post->post_title); ?></a></h2>
                    <p><?php echo wp_trim_words($featured_post->post_excerpt ?: $featured_post->post_content, 28, '...'); ?></p>
                    <div>
                        <a href="<?php echo esc_url(get_permalink($featured_post->ID)); ?>" class="read-more-btn">
                            <span>Makaleyi Oku</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- 2. BLOG KARTLARI GRİD -->
            <div class="blog-grid">
                <?php
                foreach($regular_posts as $post_item):
                    $card_img = function_exists('ototamir_get_smart_post_image') 
                        ? ototamir_get_smart_post_image($post_item->ID, 650, 400) 
                        : (get_post_meta($post_item->ID, '_custom_blog_image', true) ?: (get_the_post_thumbnail_url($post_item->ID, 'medium_large') ?: 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?auto=format&fit=crop&w=800&q=80'));
                    $card_cats = get_the_category($post_item->ID);
                    $card_cat_name = !empty($card_cats) ? $card_cats[0]->name : 'Genel';
                    $card_read_time = get_post_meta($post_item->ID, '_blog_read_time', true) ?: '4 dk okuma';
                ?>
                <article class="blog-card">
                    <div class="blog-card-img-wrap">
                        <a href="<?php echo esc_url(get_permalink($post_item->ID)); ?>">
                            <img src="<?php echo esc_url($card_img); ?>" alt="<?php echo esc_attr($post_item->post_title); ?>" onerror="this.onerror=null;this.src='<?php echo esc_url( get_template_directory_uri() . '/assets/images/placeholder-mechanic.webp' ); ?>';">
                        </a>
                    </div>
                    <div class="blog-card-body">
                        <div class="post-meta-row">
                            <span class="post-category-tag"><?php echo esc_html($card_cat_name); ?></span>
                            <span><i class="fa-regular fa-clock"></i> <?php echo esc_html($card_read_time); ?></span>
                        </div>
                        <h3><a href="<?php echo esc_url(get_permalink($post_item->ID)); ?>"><?php echo esc_html($post_item->post_title); ?></a></h3>
                        <p><?php echo wp_trim_words($post_item->post_excerpt ?: $post_item->post_content, 18, '...'); ?></p>
                        <div class="blog-card-footer">
                            <div class="author-mini">
                                <i class="fa-solid fa-user-gear"></i>
                                <span>Oto Rehber</span>
                            </div>
                            <a href="<?php echo esc_url(get_permalink($post_item->ID)); ?>" class="read-more-btn">
                                <span>Oku</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 20px; border: 1.5px solid #e2e8f0;">
                <i class="fa-regular fa-newspaper" style="font-size: 48px; color: #94a3b8; margin-bottom: 16px;"></i>
                <h3 style="color: #0f172a; margin: 0 0 8px;">Bu kategoride henüz yazı bulunmuyor.</h3>
                <p style="color: #64748b; margin: 0 0 20px;">Daha sonra tekrar kontrol edebilir veya diğer kategorileri inceleyebilirsiniz.</p>
                <a href="<?php echo esc_url(home_url('/blog')); ?>" class="blog-cat-tab active">Tüm Yazıları Gör</a>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php get_footer(); ?>
