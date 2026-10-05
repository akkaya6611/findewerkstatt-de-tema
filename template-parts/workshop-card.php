<?php
/** Workshop card with stored business facts and modern UX. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$card_id = get_the_ID();
$card_phone = findewerkstatt_normalize_phone( get_post_meta( $card_id, '_mechanic_phone', true ) );
$card_whatsapp_url = findewerkstatt_get_whatsapp_url( get_post_meta( $card_id, '_mechanic_whatsapp', true ), get_the_title(), $card_phone );
$card_address = get_post_meta( $card_id, '_mechanic_address', true );
$card_plz = get_post_meta( $card_id, '_mechanic_plz', true );
$card_city = findewerkstatt_get_city_term( $card_id );
$card_context = $card_city ? findewerkstatt_location_context( $card_city ) : null;
$card_city_name = $card_context ? $card_context['name'] : '';
if ( $card_context && in_array( $card_context['kind'], array( 'district', 'borough' ), true ) && $card_context['parent_name'] ) {
    $card_city_name .= ', ' . $card_context['parent_name'];
}
$card_rating = findewerkstatt_get_rating( $card_id );
$card_services = wp_get_post_terms( $card_id, 'service_type' );
$card_languages = findewerkstatt_workshop_language_labels( $card_id );
$card_badges = findewerkstatt_render_badges( $card_id );
$card_display_address = findewerkstatt_clean_address( $card_address, get_the_title(), $card_plz, $card_city_name );
$card_is_featured = function_exists( 'findewerkstatt_is_featured' ) && findewerkstatt_is_featured( $card_id );

$user_lat = isset( $_GET['fw_lat'] ) && is_numeric( $_GET['fw_lat'] ) ? (float) $_GET['fw_lat'] : null;
$user_lng = isset( $_GET['fw_lng'] ) && is_numeric( $_GET['fw_lng'] ) ? (float) $_GET['fw_lng'] : null;
$card_lat = get_post_meta( $card_id, '_mechanic_latitude', true );
$card_lng = get_post_meta( $card_id, '_mechanic_longitude', true );
$card_distance = null;
if ( $user_lat !== null && $user_lng !== null && is_numeric( $card_lat ) && is_numeric( $card_lng ) && function_exists( 'findewerkstatt_calculate_distance' ) ) {
    $card_distance = findewerkstatt_calculate_distance( $user_lat, $user_lng, (float) $card_lat, (float) $card_lng );
}

$card_maps_url = '';
if ( is_numeric( $card_lat ) && is_numeric( $card_lng ) ) {
    $card_maps_url = 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode( $card_lat . ',' . $card_lng );
} elseif ( ! empty( $card_display_address ) ) {
    $card_maps_url = 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode( $card_display_address );
}

$card_status_html = findewerkstatt_get_open_status( $card_id, false );
$card_is_open = ( $card_status_html && false !== strpos( $card_status_html, 'fw-status-open' ) ) ? '1' : '0';
$card_rating_val = ! empty( $card_rating['rating'] ) ? (float) $card_rating['rating'] : 0;
?>
<article class="fw-workshop-card <?php echo $card_is_featured ? 'fw-card-featured' : ''; ?>"
         data-open="<?php echo esc_attr( $card_is_open ); ?>"
         data-rating="<?php echo esc_attr( $card_rating_val ); ?>"
         data-whatsapp="<?php echo $card_whatsapp_url ? '1' : '0'; ?>"
         data-featured="<?php echo $card_is_featured ? '1' : '0'; ?>"
         data-route="<?php echo $card_maps_url ? '1' : '0'; ?>">
    <div class="fw-card-top">
        <a href="<?php the_permalink(); ?>" class="fw-card-img-link" aria-label="<?php echo esc_attr( sprintf( findewerkstatt_t( '%s – Werkstattprofil ansehen' ), get_the_title() ) ); ?>">
            <?php 
            $card_photo = get_post_meta( $card_id, '_mechanic_google_photo_url', true );
            $card_is_real = get_post_meta( $card_id, '_mechanic_is_real_photo', true ) === 'yes';
            if ( has_post_thumbnail() ) : ?>
                <?php the_post_thumbnail( 'medium_large', array( 'class' => 'fw-card-img', 'loading' => 'lazy' ) ); ?>
            <?php elseif ( $card_is_real && $card_photo ) : ?>
                <img src="<?php echo esc_url( $card_photo ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" class="fw-card-img" width="640" height="320" loading="lazy" style="object-fit:cover;">
            <?php else : ?>
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/placeholder-mechanic.webp' ); ?>" alt="" class="fw-card-img" width="640" height="320" loading="lazy">
            <?php endif; ?>
            <div class="fw-card-img-overlay" aria-hidden="true"></div>
        </a>
        <div class="fw-card-badges">
            <?php if ( $card_is_featured ) : ?>
                <span class="fw-badge fw-badge-featured-gold">
                    <span aria-hidden="true">👑</span>
                    <span><?php echo esc_html( findewerkstatt_t( 'Geprüfter Meisterbetrieb' ) ); ?></span>
                </span>
            <?php else : ?>
                <?php echo findewerkstatt_render_verification_badge( $card_id ); ?>
            <?php endif; ?>
            <?php if ( 'yes' === get_post_meta( $card_id, '_mechanic_is_master', true ) && ! $card_is_featured ) : ?>
                <span class="fw-badge fw-badge-meister">
                    <?php echo findewerkstatt_icon( 'trophy', 11 ); ?>
                    <span><?php echo esc_html( findewerkstatt_t( 'Meisterbetrieb' ) ); ?></span>
                </span>
            <?php endif; ?>
        </div>
        <button type="button" class="fw-card-bookmark-btn" data-id="<?php echo esc_attr( $card_id ); ?>" title="<?php echo esc_attr( findewerkstatt_t( 'Merken' ) ); ?>" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Merken' ) ); ?>">
            <span class="fw-bookmark-icon" aria-hidden="true">🔖</span>
        </button>
        <?php if ( $card_status_html ) : ?>
            <div class="fw-card-status-wrap">
                <?php echo $card_status_html; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="fw-card-body">
        <h3 class="fw-card-title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h3>

        <div class="fw-card-meta-top">
            <?php if ( $card_city_name ) : ?>
                <div class="fw-card-city-tag">
                    <?php echo findewerkstatt_icon( 'pin', 11, 'fw-card-city-pin' ); ?>
                    <span><?php echo esc_html( $card_city_name ); ?></span>
                </div>
            <?php endif; ?>
            <?php if ( null !== $card_distance ) : ?>
                <div class="fw-card-city-tag fw-card-distance-tag" style="background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe; font-weight:700;">
                    <span>📍</span>
                    <span><?php echo esc_html( $card_distance ); ?> km</span>
                </div>
            <?php endif; ?>
            <?php if ( ! empty( $card_rating['rating'] ) ) : ?>
                <div class="fw-card-google-rating" title="<?php echo esc_attr( sprintf( findewerkstatt_t( 'Google-Bewertung: %s von 5 Sternen (%d Bewertungen)' ), $card_rating['rating'], $card_rating['count'] ) ); ?>">
                    <span style="font-weight:700; color:#ea4335; font-size:11px;">G</span>
                    <?php echo findewerkstatt_render_stars( $card_rating['rating'], $card_rating['count'] ); ?>
                </div>
            <?php else : ?>
                <div class="fw-card-trust-pill">
                    <span class="fw-trust-star" aria-hidden="true"><?php echo findewerkstatt_icon( 'star', 11, 'fw-trust-star-svg' ); ?></span>
                    <span class="fw-trust-label"><?php echo esc_html( findewerkstatt_t( 'Verzeichniseintrag' ) ); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="fw-card-address-block">
            <p class="fw-card-address" title="<?php echo esc_attr( $card_display_address ?: findewerkstatt_t( 'Standort nicht angegeben' ) ); ?>">
                <?php echo findewerkstatt_icon( 'pin', 13, 'fw-card-addr-pin' ); ?>
                <span><?php echo esc_html( $card_display_address ?: findewerkstatt_t( 'Standort nicht angegeben' ) ); ?></span>
            </p>
        </div>

        <?php if ( $card_services && ! is_wp_error( $card_services ) ) : ?>
            <div class="fw-card-services">
                <?php foreach ( array_slice( $card_services, 0, 3 ) as $card_service ) : ?>
                    <span class="fw-badge fw-badge-service">
                        <?php echo findewerkstatt_service_icon( $card_service, 12, 'fw-badge-service-icon' ); ?>
                        <span><?php echo esc_html( findewerkstatt_t( $card_service->name ) ); ?></span>
                    </span>
                <?php endforeach; ?>
                <?php if ( count( $card_services ) > 3 ) : ?>
                    <span class="fw-badge fw-badge-more">+<?php echo count( $card_services ) - 3; ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ( $card_languages ) : ?>
            <p class="fw-card-languages">
                <?php echo findewerkstatt_icon( 'chat', 12, 'fw-card-lang-icon' ); ?>
                <span><?php echo esc_html( implode( ', ', $card_languages ) ); ?></span>
            </p>
        <?php endif; ?>

        <div class="fw-card-actions">
            <?php if ( $card_phone || $card_whatsapp_url || $card_maps_url ) : ?>
                <div class="fw-card-quick-contact">
                    <?php if ( $card_phone ) : ?>
                        <a href="tel:<?php echo esc_attr( $card_phone ); ?>" class="fw-btn-card-action fw-btn-action-phone" title="<?php echo esc_attr( findewerkstatt_t( 'Jetzt anrufen' ) ); ?>">
                            <?php echo findewerkstatt_icon( 'phone', 13 ); ?>
                            <span><?php echo esc_html( findewerkstatt_t( 'Anrufen' ) ); ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if ( $card_whatsapp_url ) : ?>
                        <a href="<?php echo esc_url( $card_whatsapp_url ); ?>" class="fw-btn-card-action fw-btn-action-wa" aria-label="<?php echo esc_attr( sprintf( findewerkstatt_t( 'WhatsApp-Anfrage an %s' ), get_the_title() ) ); ?>" target="_blank" rel="noopener noreferrer" title="WhatsApp">
                            <?php echo findewerkstatt_icon( 'whatsapp', 14 ); ?>
                            <span>WhatsApp</span>
                        </a>
                    <?php endif; ?>
                    <?php if ( $card_maps_url ) : ?>
                        <a href="<?php echo esc_url( $card_maps_url ); ?>" class="fw-btn-card-action fw-btn-action-route" aria-label="<?php echo esc_attr( sprintf( findewerkstatt_t( 'Route zu %s planen' ), get_the_title() ) ); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr( findewerkstatt_t( 'Route planen' ) ); ?>">
                            <?php echo findewerkstatt_icon( 'pin', 13 ); ?>
                            <span><?php echo esc_html( findewerkstatt_t( 'Route' ) ); ?></span>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <a href="<?php the_permalink(); ?>" class="fw-btn-card-main">
                <span><?php echo esc_html( findewerkstatt_t( 'Profil ansehen' ) ); ?></span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </a>
        </div>
    </div>
</article>
