<?php
/** Workshop card with stored business facts. */
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
?>
<article class="fw-workshop-card">
    <a class="fw-card-top" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr( sprintf( findewerkstatt_t( '%s – Werkstattprofil ansehen' ), get_the_title() ) ); ?>">
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
        <div class="fw-card-badges"><?php echo findewerkstatt_render_badges( $card_id ); ?></div>
        <?php echo findewerkstatt_get_open_status( $card_id ); ?>
    </a>
    <div class="fw-card-body">
        <?php echo findewerkstatt_render_stars( $card_rating['rating'], $card_rating['count'] ); ?>
        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <p class="fw-card-address"><span aria-hidden="true">📍</span><span><?php echo esc_html( trim( $card_address . ', ' . $card_plz . ' ' . $card_city_name, ', ' ) ?: findewerkstatt_t( 'Standort nicht angegeben' ) ); ?></span></p>
        <?php get_template_part( 'template-parts/workshop-location', null, array( 'context' => $card_context ) ); ?>
        <?php if ( $card_services && ! is_wp_error( $card_services ) ) : ?>
            <div class="fw-card-services">
                <?php foreach ( array_slice( $card_services, 0, 3 ) as $card_service ) : ?>
                    <span class="fw-badge fw-badge-service"><?php echo esc_html( findewerkstatt_t( $card_service->name ) ); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ( $card_languages ) : ?><p class="fw-card-languages"><strong><?php echo esc_html( findewerkstatt_t( 'Gesprochene Sprachen' ) ); ?>:</strong> <?php echo esc_html( implode( ', ', $card_languages ) ); ?></p><?php endif; ?>
        <div class="fw-card-actions<?php echo $card_phone && $card_whatsapp_url ? ' fw-card-actions-three' : ''; ?>">
            <?php if ( $card_phone ) : ?><a href="tel:<?php echo esc_attr( $card_phone ); ?>" class="fw-btn fw-btn-phone fw-btn-sm fw-btn-card-action"><?php echo findewerkstatt_icon('phone', 16); ?> <?php echo esc_html( findewerkstatt_t( 'Anrufen' ) ); ?></a><?php endif; ?>
            <?php if ( $card_whatsapp_url ) : ?><a href="<?php echo esc_url( $card_whatsapp_url ); ?>" class="fw-btn fw-btn-whatsapp fw-btn-sm" aria-label="<?php echo esc_attr( sprintf( findewerkstatt_t( 'WhatsApp-Anfrage an %s' ), get_the_title() ) ); ?>" target="_blank" rel="noopener noreferrer"><?php echo findewerkstatt_icon('whatsapp', 16); ?> WhatsApp</a><?php endif; ?>
            <a href="<?php the_permalink(); ?>" class="fw-btn fw-btn-outline fw-btn-sm"><?php echo esc_html( findewerkstatt_t( 'Profil ansehen →' ) ); ?></a>
        </div>
    </div>
</article>
