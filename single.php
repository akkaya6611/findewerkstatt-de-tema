<?php
/**
 * FindeWerkstatt.de — Single Post Template (Redaktioneller Ratgeber-Artikel)
 *
 * Modernes, leserfreundliches Layout für Blogbeiträge und Kfz-Ratgeber.
 * Inklusive Werbeplatzierungen, Werkstatt-Suchboxen, Lesedauer,
 * Social-Share-Funktionen und verwandten Artikeln.
 *
 * @package FindeWerkstatt
 * @version 3.5.5
 */

get_header();

while ( have_posts() ) : the_post();
    $post_id      = get_the_ID();
    $title        = get_the_title();
    $permalink    = get_permalink();
    $reading_time = findewerkstatt_reading_time( get_the_content() );
    $thumb_url    = findewerkstatt_post_thumbnail_url( $post_id, 'full' );
    $categories   = get_the_category();
    $primary_cat  = ! empty( $categories ) ? $categories[0] : null;
    $related_posts = findewerkstatt_get_related_posts( $post_id, 3 );
?>

<main id="main-content" class="fw-container fw-page-content fw-single-article-page">

    <!-- Brotkrümelnavigation -->
    <nav class="fw-breadcrumbs" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Brotkrümelnavigation' ) ); ?>">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Startseite' ) ); ?></a>
        <span aria-hidden="true">›</span>
        <a href="<?php echo esc_url( home_url( '/ratgeber/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Ratgeber' ) ); ?></a>
        <?php if ( $primary_cat ) : ?>
            <span aria-hidden="true">›</span>
            <a href="<?php echo esc_url( get_category_link( $primary_cat ) ); ?>"><?php echo esc_html( $primary_cat->name ); ?></a>
        <?php endif; ?>
        <span aria-hidden="true">›</span>
        <span class="fw-breadcrumb-current"><?php the_title(); ?></span>
    </nav>

    <!-- Zweispaltiges Beitrags-Layout -->
    <div class="fw-article-layout">

        <!-- Hauptartikel -->
        <article class="fw-article-main fw-box" id="post-<?php the_ID(); ?>">

            <header class="fw-article-header">
                <?php if ( $primary_cat ) : ?>
                    <a href="<?php echo esc_url( get_category_link( $primary_cat ) ); ?>" class="fw-article-cat-badge">
                        <?php echo esc_html( $primary_cat->name ); ?>
                    </a>
                <?php endif; ?>

                <h1 class="fw-article-title"><?php the_title(); ?></h1>

                <div class="fw-article-meta-bar">
                    <div class="fw-article-meta-left">
                        <span class="fw-article-meta-item">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                            <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
                        </span>
                        <span class="fw-article-meta-item">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <span><?php echo esc_html( $reading_time ); ?></span>
                        </span>
                    </div>

                    <!-- Social Sharing Buttons -->
                    <div class="fw-article-share">
                        <a href="https://api.whatsapp.com/send?text=<?php echo rawurlencode( $title . ' ' . $permalink ); ?>" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="fw-share-btn fw-share-wa" 
                           aria-label="<?php echo esc_attr( findewerkstatt_t( 'Auf WhatsApp teilen' ) ); ?>" 
                           title="<?php echo esc_attr( findewerkstatt_t( 'Auf WhatsApp teilen' ) ); ?>">
                            💬 WhatsApp
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode( $permalink ); ?>" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="fw-share-btn fw-share-fb" 
                           aria-label="<?php echo esc_attr( findewerkstatt_t( 'Auf Facebook teilen' ) ); ?>" 
                           title="<?php echo esc_attr( findewerkstatt_t( 'Auf Facebook teilen' ) ); ?>">
                            Facebook
                        </a>
                    </div>
                </div>
            </header>

            <!-- Beitragsbild / Banner -->
            <?php if ( $thumb_url ) : ?>
                <div class="fw-article-hero-media">
                    <img src="<?php echo esc_url( $thumb_url ); ?>" 
                         alt="<?php echo esc_attr( get_the_title() ); ?>" 
                         class="fw-article-hero-img" 
                         loading="eager" 
                         decoding="async">
                </div>
            <?php endif; ?>

            <!-- Oberes Werbebanner -->
            <?php if ( function_exists( 'findewerkstatt_render_ad_placement' ) ) : ?>
                <div class="fw-article-ad-top">
                    <?php findewerkstatt_render_ad_placement( 'top_banner' ); ?>
                </div>
            <?php endif; ?>

            <!-- Fließtext des Beitrags -->
            <div class="fw-prose fw-article-content">
                <?php the_content(); ?>
            </div>

            <!-- In-Content Video / Sponsoranzeige -->
            <?php if ( function_exists( 'findewerkstatt_render_ad_placement' ) ) : ?>
                <div class="fw-article-ad-incontent">
                    <?php findewerkstatt_render_ad_placement( 'incontent_video' ); ?>
                </div>
            <?php endif; ?>

            <!-- Interaktive Werkstatt-Lead-Box am Artikelende -->
            <div class="fw-article-lead-box">
                <div class="fw-article-lead-box-content">
                    <div class="fw-article-lead-badge">
                        <span>🔧</span>
                        <span><?php echo esc_html( findewerkstatt_t( 'Reparatur oder Service fällig?' ) ); ?></span>
                    </div>
                    <h3 class="fw-article-lead-title">
                        <?php echo esc_html( findewerkstatt_t( 'Passende Kfz-Werkstatt in Ihrer Nähe finden' ) ); ?>
                    </h3>
                    <p class="fw-article-lead-desc">
                        <?php echo esc_html( findewerkstatt_t( 'Vergleichen Sie zertifizierte Meisterbetriebe, prüfen Sie echte Bewertungen und fordern Sie direkt ein unverbindliches Angebot an.' ) ); ?>
                    </p>
                    <div class="fw-article-lead-actions">
                        <a href="<?php echo esc_url( home_url( '/werkstatt/' ) ); ?>" class="fw-btn fw-btn-primary fw-btn-lg">
                            🔍 <?php echo esc_html( findewerkstatt_t( 'Werkstatt finden' ) ); ?>
                        </a>
                        <a href="<?php echo esc_url( function_exists( 'findewerkstatt_page_url' ) ? findewerkstatt_page_url( 'pakete' ) : home_url( '/pakete/' ) ); ?>" class="fw-btn fw-btn-outline fw-btn-lg">
                            <?php echo esc_html( findewerkstatt_t( 'Leistungen vergleichen' ) ); ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Paginierung für mehrseitige Beiträge -->
            <?php 
            wp_link_pages( array( 
                'before' => '<nav class="fw-pagination fw-article-pages" aria-label="' . esc_attr( findewerkstatt_t( 'Seiten dieses Beitrags' ) ) . '"><span class="fw-pagination-label">' . esc_html( findewerkstatt_t( 'Seiten:' ) ) . '</span> ', 
                'after'  => '</nav>' 
            ) ); 
            ?>

        </article>

        <!-- Sidebar -->
        <aside class="fw-article-sidebar" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Seitenleiste' ) ); ?>">

            <!-- Werkstattsuche Schnellbox -->
            <div class="fw-box fw-blog-widget fw-blog-widget-search">
                <div class="fw-blog-widget-tag"><?php echo esc_html( findewerkstatt_t( 'Werkstattsuche' ) ); ?></div>
                <h3 class="fw-blog-widget-title"><?php echo esc_html( findewerkstatt_t( 'Fachbetrieb finden' ) ); ?></h3>
                <p class="fw-blog-widget-desc"><?php echo esc_html( findewerkstatt_t( 'Finden Sie freie Werkstätten, Vertragspartner und Spezialisten.' ) ); ?></p>
                <form action="<?php echo esc_url( get_post_type_archive_link( 'mechanic' ) ); ?>" method="get">
                    <input type="hidden" name="post_type" value="mechanic">
                    <div class="fw-form-field" style="margin-bottom:10px;">
                        <input type="text" name="s" class="fw-input" placeholder="<?php echo esc_attr( findewerkstatt_t( 'Ort oder PLZ eingeben...' ) ); ?>">
                    </div>
                    <button type="submit" class="fw-btn fw-btn-primary fw-btn-block">
                        🔍 <?php echo esc_html( findewerkstatt_t( 'Werkstätten suchen' ) ); ?>
                    </button>
                </form>
            </div>

            <!-- Sticky Sidebar Werbeplatzierung -->
            <?php if ( function_exists( 'findewerkstatt_render_ad_placement' ) ) : ?>
                <div class="fw-article-sidebar-sticky-ad">
                    <?php findewerkstatt_render_ad_placement( 'sidebar_sticky' ); ?>
                </div>
            <?php endif; ?>

            <!-- Werkstattinhaber Box -->
            <div class="fw-box fw-blog-widget fw-blog-widget-cta">
                <span class="fw-blog-widget-icon" aria-hidden="true">🚗</span>
                <h3 class="fw-blog-widget-title"><?php echo esc_html( findewerkstatt_t( 'Ihre Werkstatt noch nicht gelistet?' ) ); ?></h3>
                <p class="fw-blog-widget-desc"><?php echo esc_html( findewerkstatt_t( 'Werden Sie Teil von Deutschlands modernstem Kfz-Verzeichnis und erhalten Sie qualifizierte Kundenanfragen.' ) ); ?></p>
                <a href="<?php echo esc_url( function_exists( 'findewerkstatt_page_url' ) ? findewerkstatt_page_url( 'werkstatt-anmelden' ) : home_url( '/werkstatt-anmelden/' ) ); ?>" class="fw-btn fw-btn-accent fw-btn-block">
                    + <?php echo esc_html( findewerkstatt_t( 'Jetzt kostenlos eintragen' ) ); ?>
                </a>
            </div>

        </aside>

    </div>

    <!-- Verwandte Beiträge -->
    <?php if ( ! empty( $related_posts ) ) : ?>
        <section class="fw-related-articles-section">
            <div class="fw-section-heading">
                <div class="fw-section-tag"><?php echo esc_html( findewerkstatt_t( 'Weiterlesen' ) ); ?></div>
                <h2><?php echo esc_html( findewerkstatt_t( 'Ähnliche Ratgeber & Artikel' ) ); ?></h2>
                <p><?php echo esc_html( findewerkstatt_t( 'Vertiefen Sie Ihr Wissen mit weiteren Anleitungen und Fachbeiträgen.' ) ); ?></p>
            </div>

            <div class="fw-blog-grid fw-related-grid">
                <?php foreach ( $related_posts as $related_post ) : 
                    $rel_id    = $related_post->ID;
                    $rel_thumb = findewerkstatt_post_thumbnail_url( $rel_id, 'medium_large' );
                    $rel_time  = findewerkstatt_reading_time( $related_post->post_content );
                    $rel_cats  = wp_get_post_categories( $rel_id, array( 'fields' => 'all' ) );
                    $rel_cat_name = ! empty( $rel_cats ) ? $rel_cats[0]->name : findewerkstatt_t( 'Ratgeber' );
                    $rel_excerpt = $related_post->post_excerpt ?: wp_trim_words( strip_tags( $related_post->post_content ), 18, '...' );
                ?>
                    <article class="fw-blog-card fw-box">
                        <a href="<?php echo esc_url( get_permalink( $rel_id ) ); ?>" class="fw-blog-card-media" aria-hidden="true" tabindex="-1">
                            <img src="<?php echo esc_url( $rel_thumb ); ?>" 
                                 alt="<?php echo esc_attr( $related_post->post_title ); ?>" 
                                 class="fw-blog-card-img" 
                                 loading="lazy" 
                                 decoding="async">
                            <span class="fw-blog-card-cat-badge"><?php echo esc_html( $rel_cat_name ); ?></span>
                        </a>
                        <div class="fw-blog-card-body">
                            <div class="fw-blog-card-meta">
                                <span class="fw-blog-card-date">
                                    <time datetime="<?php echo esc_attr( get_the_date( 'c', $rel_id ) ); ?>"><?php echo esc_html( get_the_date( '', $rel_id ) ); ?></time>
                                </span>
                                <span class="fw-blog-card-reading-time"><?php echo esc_html( $rel_time ); ?></span>
                            </div>
                            <h3 class="fw-blog-card-title">
                                <a href="<?php echo esc_url( get_permalink( $rel_id ) ); ?>"><?php echo esc_html( $related_post->post_title ); ?></a>
                            </h3>
                            <p class="fw-blog-card-excerpt">
                                <?php echo esc_html( $rel_excerpt ); ?>
                            </p>
                            <div class="fw-blog-card-footer">
                                <a href="<?php echo esc_url( get_permalink( $rel_id ) ); ?>" class="fw-blog-card-cta">
                                    <span><?php echo esc_html( findewerkstatt_t( 'Artikel lesen' ) ); ?></span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

</main>

<?php
endwhile;

get_footer();
