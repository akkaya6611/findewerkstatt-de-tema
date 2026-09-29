<?php
/**
 * FindeWerkstatt.de — Werkstatt-Verzeichnis Archiv (Archive Template)
 * 
 * @package FindeWerkstatt
 * @version 2.0.0
 */

get_header();

$bundeslaender = FindeWerkstatt_German_Data::get_bundeslaender();
$categories    = FindeWerkstatt_German_Data::get_categories();
$brands        = FindeWerkstatt_German_Data::get_car_brands();

$current_city    = isset( $_GET['fw_city'] ) ? sanitize_text_field( $_GET['fw_city'] ) : '';
$current_service = isset( $_GET['fw_service'] ) ? sanitize_text_field( $_GET['fw_service'] ) : '';
$current_brand   = isset( $_GET['fw_brand'] ) ? sanitize_text_field( $_GET['fw_brand'] ) : '';
?>

<div class="fw-container" style="padding-top:30px; padding-bottom:60px;">
    <!-- Breadcrumbs -->
    <nav class="fw-breadcrumbs" aria-label="Brotkrümelnavigation">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Startseite</a>
        <span>›</span>
        <span>Werkstätten</span>
    </nav>

    <!-- Archiv Titel -->
    <div style="margin-bottom:24px;">
        <h1 style="font-size:32px; font-weight:900; color:var(--fw-primary); margin-bottom:8px;">
            Kfz-Werkstätten & Meisterbetriebe in Deutschland
        </h1>
        <p style="color:var(--fw-text-muted); font-size:16px;">
            Finden und vergleichen Sie geprüfte Autowerkstätten, 24h-Pannenhilfen und TÜV-Stationen.
        </p>
    </div>

    <!-- Filterleiste -->
    <div style="background:#ffffff; border:1px solid var(--fw-border); border-radius:var(--fw-radius-lg); padding:16px; margin-bottom:30px; box-shadow:var(--fw-shadow-sm);">
        <form action="<?php echo esc_url( home_url( '/werkstaetten/' ) ); ?>" method="get" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)) auto; gap:12px; align-items:center;">
            <!-- Stadt / Bundesland -->
            <div>
                <select name="fw_city" style="width:100%; padding:10px 14px; border:1px solid var(--fw-border); border-radius:8px; font-weight:600;">
                    <option value="">Alle Bundesländer & Städte</option>
                    <?php foreach ( $bundeslaender as $code => $land ) : ?>
                        <option value="<?php echo esc_attr( $land['slug'] ); ?>" <?php selected( $current_city, $land['slug'] ); ?>><?php echo esc_html( $land['name'] ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Leistung -->
            <div>
                <select name="fw_service" style="width:100%; padding:10px 14px; border:1px solid var(--fw-border); border-radius:8px; font-weight:600;">
                    <option value="">Alle Leistungen</option>
                    <?php foreach ( $categories as $slug => $cat ) : ?>
                        <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $current_service, $slug ); ?>><?php echo esc_html( $cat['name'] ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Marke -->
            <div>
                <select name="fw_brand" style="width:100%; padding:10px 14px; border:1px solid var(--fw-border); border-radius:8px; font-weight:600;">
                    <option value="">Alle Marken</option>
                    <?php foreach ( $brands as $slug => $name ) : ?>
                        <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $current_brand, $slug ); ?>><?php echo esc_html( $name ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter anwenden -->
            <button type="submit" class="fw-btn fw-btn-primary">
                Filter anwenden
            </button>
        </form>
    </div>

    <!-- Werkstätten Grid -->
    <?php if ( have_posts() ) : ?>
        <div style="font-size:14px; font-weight:700; color:var(--fw-text-muted); margin-bottom:16px;">
            Insgesamt <?php echo (int) $wp_query->found_posts; ?> Werkstätten gefunden
        </div>

        <div class="fw-workshops-grid">
            <?php while ( have_posts() ) : the_post();
                $post_id    = get_the_ID();
                $phone      = get_post_meta( $post_id, '_mechanic_phone', true );
                $address    = get_post_meta( $post_id, '_mechanic_address', true );
                $plz        = get_post_meta( $post_id, '_mechanic_plz', true );
                $rating_avg = get_post_meta( $post_id, '_mechanic_rating_avg', true ) ?: '4.9';
                $rating_cnt = get_post_meta( $post_id, '_mechanic_rating_count', true ) ?: '18';
                
                $city_terms = wp_get_post_terms( $post_id, 'mechanic_city' );
                $city_name  = ( $city_terms && ! is_wp_error( $city_terms ) ) ? $city_terms[0]->name : '';
                $services   = wp_get_post_terms( $post_id, 'service_type' );
                ?>
                <div class="fw-workshop-card">
                    <div class="fw-card-top">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <?php the_post_thumbnail( 'medium', array( 'class' => 'fw-card-img' ) ); ?>
                        <?php else : ?>
                            <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/placeholder-mechanic.webp' ); ?>" alt="<?php the_title_attribute(); ?>" class="fw-card-img">
                        <?php endif; ?>
                        <div class="fw-card-badges">
                            <?php echo findewerkstatt_render_badges( $post_id ); ?>
                        </div>
                        <?php echo findewerkstatt_get_open_status(); ?>
                    </div>

                    <div class="fw-card-body">
                        <?php echo findewerkstatt_render_stars( $rating_avg, $rating_cnt ); ?>

                        <h3>
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>

                        <div class="fw-card-address">
                            <span>📍</span>
                            <span><?php echo esc_html( trim( "{$address}, {$plz} {$city_name}", ', ' ) ); ?></span>
                        </div>

                        <?php if ( ! empty( $services ) && ! is_wp_error( $services ) ) : ?>
                            <div class="fw-card-services">
                                <?php foreach ( array_slice( $services, 0, 3 ) as $st ) : ?>
                                    <span class="fw-badge fw-badge-service"><?php echo esc_html( $st->name ); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="fw-card-actions">
                            <?php if ( $phone ) : ?>
                                <a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>" class="fw-btn fw-btn-phone fw-btn-sm">
                                    📞 Anrufen
                                </a>
                            <?php endif; ?>
                            <a href="<?php the_permalink(); ?>" class="fw-btn fw-btn-outline fw-btn-sm">
                                Details ansehen →
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <div style="margin-top:40px; text-align:center;">
            <?php
            the_posts_pagination( array(
                'mid_size'  => 2,
                'prev_text' => __( '← Vorherige', 'findewerkstatt' ),
                'next_text' => __( 'Nächste →', 'findewerkstatt' ),
            ) );
            ?>
        </div>

    <?php else : ?>
        <div style="background:#ffffff; border:1px solid var(--fw-border); border-radius:var(--fw-radius-lg); padding:48px; text-align:center;">
            <div style="font-size:48px; margin-bottom:12px;">🔍</div>
            <h2 style="font-size:22px; margin-bottom:8px;">Keine Werkstätten gefunden</h2>
            <p style="color:var(--fw-text-muted); margin-bottom:20px;">
                Für Ihre aktuellen Filterkriterien wurden keine Werkstätten gefunden. Bitte lockern Sie Ihre Suche.
            </p>
            <a href="<?php echo esc_url( home_url( '/werkstaetten/' ) ); ?>" class="fw-btn fw-btn-primary">
                Alle Filter zurücksetzen
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
get_footer();
