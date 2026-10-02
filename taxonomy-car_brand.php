<?php
/**
 * FindeWerkstatt.de — Automarken Landingpage (Taxonomie car_brand)
 * 
 * @package FindeWerkstatt
 * @version 2.0.0
 */

get_header();

$current_term = get_queried_object();
$brand_name   = $current_term && isset( $current_term->name ) ? $current_term->name : 'Kfz-Marke';
$brand_desc   = $current_term && isset( $current_term->description ) ? $current_term->description : '';

$pseo_loc     = get_query_var( 'fw_pseo_location' );
$loc_name     = ! empty( $pseo_loc ) ? ucwords( str_replace( '-', ' ', $pseo_loc ) ) : 'Deutschland';
?>

<div class="fw-container" style="padding-top:30px; padding-bottom:60px;">
    <!-- Breadcrumbs -->
    <nav class="fw-breadcrumbs" aria-label="Brotkrümelnavigation">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Startseite</a>
        <span>›</span>
        <a href="<?php echo esc_url( home_url( '/werkstaetten/' ) ); ?>">Werkstätten</a>
        <span>›</span>
        <?php if ( ! empty( $pseo_loc ) && $current_term ) : ?>
            <a href="<?php echo esc_url( get_term_link( $current_term ) ); ?>"><?php echo esc_html( $brand_name ); ?></a>
            <span>›</span>
            <span>in <?php echo esc_html( $loc_name ); ?></span>
        <?php else : ?>
            <span><?php echo esc_html( $brand_name ); ?></span>
        <?php endif; ?>
    </nav>

    <!-- Hero Header -->
    <div style="background:#ffffff; border:1px solid var(--fw-border); border-radius:var(--fw-radius-lg); padding:32px; margin-bottom:30px; box-shadow:var(--fw-shadow-sm);">
        <div style="display:inline-block; font-size:12px; font-weight:800; color:var(--fw-info); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">
            🚗 Markenspezialist & Standort
        </div>
        <h1 style="font-size:34px; font-weight:900; color:var(--fw-primary); margin-bottom:12px; line-height:1.2;">
            <?php echo esc_html( $brand_name ); ?> Werkstätten in <?php echo esc_html( $loc_name ); ?>
        </h1>
        <p style="font-size:16px; color:var(--fw-text-muted); max-width:850px; line-height:1.6;">
            <?php echo esc_html( $brand_desc ?: "Finden Sie qualifizierte Kfz-Meisterbetriebe mit Spezialisierung auf {$brand_name} in {$loc_name}. Fachgerechte Inspektion nach Herstellervorgaben, Originalteile und Erhalt der vollen Werksgarantie." ); ?>
        </p>
    </div>

    <!-- Werkstätten Grid -->
    <?php if ( have_posts() ) : ?>
        <div style="font-size:14px; font-weight:700; color:var(--fw-text-muted); margin-bottom:16px;">
            <?php echo (int) $wp_query->found_posts; ?> Werkstätten für <?php echo esc_html( $brand_name ); ?> gefunden
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
                'prev_text' => '← Vorherige',
                'next_text' => 'Nächste →',
            ) );
            ?>
        </div>

    <?php else : ?>
        <div style="background:#ffffff; border:1px solid var(--fw-border); border-radius:var(--fw-radius-lg); padding:48px; text-align:center;">
            <div style="font-size:48px; margin-bottom:12px;">🚗</div>
            <h2 style="font-size:22px; margin-bottom:8px;">Noch keine spezialisierten Betriebe für <?php echo esc_html( $brand_name ); ?> gelistet</h2>
            <p style="color:var(--fw-text-muted); margin-bottom:20px;">
                Sind Sie Spezialist für diese Marke? Tragen Sie Ihren Betrieb jetzt kostenlos ein!
            </p>
            <a href="<?php echo esc_url( home_url( '/werkstatt-anmelden/' ) ); ?>" class="fw-btn fw-btn-primary">
                + Werkstatt jetzt eintragen
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
get_footer();
