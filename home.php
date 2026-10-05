<?php
/**
 * FindeWerkstatt.de — Blog & Ratgeber Archiv Template
 *
 * Zeigt redaktionelle Fachartikel, Reparaturanleitungen, TÜV-Tipps
 * und Fahrzeugmarken-Ratgeber übersichtlich und mobiloptimiert an.
 *
 * @package FindeWerkstatt
 * @version 3.5.5
 */

get_header();

global $wp_query;
$paged = max( 1, (int) get_query_var( 'paged' ) );
$is_cat = is_category();
$cat_name = $is_cat ? single_cat_title( '', false ) : '';
?>

<main id="main-content" class="fw-container fw-page-content fw-blog-archive-page">

    <!-- Brotkrümelnavigation -->
    <nav class="fw-breadcrumbs" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Brotkrümelnavigation' ) ); ?>">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Startseite' ) ); ?></a>
        <span aria-hidden="true">›</span>
        <?php if ( $is_cat ) : ?>
            <a href="<?php echo esc_url( home_url( '/ratgeber/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Ratgeber' ) ); ?></a>
            <span aria-hidden="true">›</span>
            <span><?php echo esc_html( $cat_name ); ?></span>
        <?php else : ?>
            <span><?php echo esc_html( findewerkstatt_t( 'Ratgeber' ) ); ?></span>
        <?php endif; ?>
    </nav>

    <!-- Hero Header -->
    <header class="fw-blog-hero">
        <div class="fw-blog-hero-badge">
            <span aria-hidden="true">📖</span>
            <span><?php echo esc_html( findewerkstatt_t( 'Kfz-Ratgeber & Magazin' ) ); ?></span>
        </div>
        <h1 class="fw-blog-hero-title">
            <?php if ( $is_cat ) : ?>
                <?php echo esc_html( sprintf( findewerkstatt_t( 'Ratgeber: %s' ), $cat_name ) ); ?>
            <?php else : ?>
                <?php echo esc_html( findewerkstatt_t( 'Kfz-Ratgeber, Tipps & Reparaturanleitungen' ) ); ?>
            <?php endif; ?>
        </h1>
        <p class="fw-blog-hero-desc">
            <?php if ( $is_cat && category_description() ) : ?>
                <?php echo esc_html( strip_tags( category_description() ) ); ?>
            <?php else : ?>
                <?php echo esc_html( findewerkstatt_t( 'Hilfreiche Anleitungen, Reparatur-Kostenratgeber, Inspektionsintervalle und Praxistipps rund um Ihr Fahrzeug.' ) ); ?>
            <?php endif; ?>
        </p>
    </header>

    <!-- Top Banner Werbeplatzierung -->
    <?php if ( function_exists( 'findewerkstatt_render_ad_placement' ) ) : ?>
        <div class="fw-blog-ad-top">
            <?php findewerkstatt_render_ad_placement( 'top_banner' ); ?>
        </div>
    <?php endif; ?>

    <!-- Hauptbereich mit 2-Spalten-Layout -->
    <div class="fw-blog-layout">

        <!-- Linke Spalte: Beitragsliste -->
        <div class="fw-blog-main">
            <?php if ( have_posts() ) : ?>
                <div class="fw-blog-grid">
                    <?php 
                    $post_counter = 0;
                    while ( have_posts() ) : the_post(); 
                        $post_counter++;
                        $post_id   = get_the_ID();
                        $thumb_url = findewerkstatt_post_thumbnail_url( $post_id, 'medium_large' );
                        $reading_time = findewerkstatt_reading_time( get_the_content() );
                        $cats      = get_the_category();
                        $primary_cat = ! empty( $cats ) ? $cats[0]->name : findewerkstatt_t( 'Kfz-Wissen' );
                        $excerpt   = get_the_excerpt() ?: wp_trim_words( strip_tags( get_the_content() ), 22, '...' );
                    ?>
                        <article class="fw-blog-card fw-box" id="post-<?php the_ID(); ?>">
                            <a href="<?php the_permalink(); ?>" class="fw-blog-card-media" aria-hidden="true" tabindex="-1">
                                <img src="<?php echo esc_url( $thumb_url ); ?>" 
                                     alt="<?php echo esc_attr( get_the_title() ); ?>" 
                                     class="fw-blog-card-img" 
                                     loading="lazy" 
                                     decoding="async">
                                <span class="fw-blog-card-cat-badge"><?php echo esc_html( $primary_cat ); ?></span>
                            </a>
                            <div class="fw-blog-card-body">
                                <div class="fw-blog-card-meta">
                                    <span class="fw-blog-card-date">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                        <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
                                    </span>
                                    <span class="fw-blog-card-reading-time">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        <?php echo esc_html( $reading_time ); ?>
                                    </span>
                                </div>
                                <h2 class="fw-blog-card-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>
                                <p class="fw-blog-card-excerpt">
                                    <?php echo esc_html( wp_trim_words( $excerpt, 20, '...' ) ); ?>
                                </p>
                                <div class="fw-blog-card-footer">
                                    <a href="<?php the_permalink(); ?>" class="fw-blog-card-cta">
                                        <span><?php echo esc_html( findewerkstatt_t( 'Artikel lesen' ) ); ?></span>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                    </a>
                                </div>
                            </div>
                        </article>

                        <?php 
                        // In-Feed-Werbung nach dem 4. Beitrag einfügen
                        if ( 4 === $post_counter && function_exists( 'findewerkstatt_render_ad_placement' ) ) : ?>
                            <div class="fw-blog-infeed-ad fw-box">
                                <?php findewerkstatt_render_ad_placement( 'archive_feed' ); ?>
                            </div>
                        <?php endif; ?>

                    <?php endwhile; ?>
                </div>

                <!-- Paginierung -->
                <div class="fw-pagination fw-blog-pagination">
                    <?php 
                    the_posts_pagination( array(
                        'mid_size'           => 2,
                        'prev_text'          => '← ' . findewerkstatt_t( 'Zurück' ),
                        'next_text'          => findewerkstatt_t( 'Weiter' ) . ' →',
                        'screen_reader_text' => findewerkstatt_t( 'Weitere Seiten' ),
                    ) ); 
                    ?>
                </div>

            <?php else : ?>
                <div class="fw-empty-state fw-box">
                    <div style="font-size:48px; margin-bottom:12px;">📚</div>
                    <h2><?php echo esc_html( findewerkstatt_t( 'Noch keine Beiträge vorhanden' ) ); ?></h2>
                    <p><?php echo esc_html( findewerkstatt_t( 'In dieser Rubrik sind derzeit keine Beiträge veröffentlicht.' ) ); ?></p>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="fw-btn fw-btn-primary">
                        <?php echo esc_html( findewerkstatt_t( 'Zur Startseite' ) ); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Rechte Spalte: Sidebar & Monetarisierung -->
        <aside class="fw-blog-sidebar" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Seitenleiste' ) ); ?>">

            <!-- Werkstattsuche Widget -->
            <div class="fw-box fw-blog-widget fw-blog-widget-search">
                <div class="fw-blog-widget-tag"><?php echo esc_html( findewerkstatt_t( 'Express-Suche' ) ); ?></div>
                <h3 class="fw-blog-widget-title"><?php echo esc_html( findewerkstatt_t( 'Werkstatt in Ihrer Nähe' ) ); ?></h3>
                <p class="fw-blog-widget-desc"><?php echo esc_html( findewerkstatt_t( 'Vergleichen Sie zertifizierte Meisterbetriebe in allen deutschen Regionen.' ) ); ?></p>
                <form action="<?php echo esc_url( get_post_type_archive_link( 'mechanic' ) ); ?>" method="get" class="fw-blog-quick-search-form">
                    <input type="hidden" name="post_type" value="mechanic">
                    <div class="fw-form-field" style="margin-bottom:10px;">
                        <input type="text" name="s" class="fw-input" placeholder="<?php echo esc_attr( findewerkstatt_t( 'PLZ, Stadt oder Werkstatt...' ) ); ?>">
                    </div>
                    <button type="submit" class="fw-btn fw-btn-primary fw-btn-block">
                        🔍 <?php echo esc_html( findewerkstatt_t( 'Werkstatt finden' ) ); ?>
                    </button>
                </form>
            </div>

            <!-- Sticky Sidebar Werbeplatzierung -->
            <?php if ( function_exists( 'findewerkstatt_render_ad_placement' ) ) : ?>
                <div class="fw-blog-sidebar-sticky-ad">
                    <?php findewerkstatt_render_ad_placement( 'sidebar_sticky' ); ?>
                </div>
            <?php endif; ?>

            <!-- Werkstatt eintragen CTA -->
            <div class="fw-box fw-blog-widget fw-blog-widget-cta">
                <span class="fw-blog-widget-icon" aria-hidden="true">💼</span>
                <h3 class="fw-blog-widget-title"><?php echo esc_html( findewerkstatt_t( 'Sind Sie Werkstattinhaber?' ) ); ?></h3>
                <p class="fw-blog-widget-desc"><?php echo esc_html( findewerkstatt_t( 'Tragen Sie Ihren Betrieb kostenlos ein und gewinnen Sie gezielt Autofahrer aus Ihrer Region.' ) ); ?></p>
                <a href="<?php echo esc_url( function_exists( 'findewerkstatt_page_url' ) ? findewerkstatt_page_url( 'werkstatt-anmelden' ) : home_url( '/werkstatt-anmelden/' ) ); ?>" class="fw-btn fw-btn-accent fw-btn-block">
                    + <?php echo esc_html( findewerkstatt_t( 'Werkstatt eintragen' ) ); ?>
                </a>
            </div>

        </aside>

    </div>

</main>

<?php 
get_footer();
