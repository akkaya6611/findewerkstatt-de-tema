<?php
/** Shared clickable location labels for workshop cards and profiles. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$location_links = findewerkstatt_workshop_location_links( $args['context'] ?? null );
if ( ! $location_links ) { return; }
?>
<div class="fw-workshop-location" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Standort des Betriebs' ) ); ?>">
    <?php foreach ( $location_links as $location_link ) : ?>
        <?php if ( $location_link['url'] ) : ?>
            <a class="fw-workshop-location-item" href="<?php echo esc_url( $location_link['url'] ); ?>">
                <span class="fw-workshop-location-label"><?php echo esc_html( findewerkstatt_t( $location_link['label'] ) ); ?></span>
                <span class="fw-workshop-location-name"><?php echo esc_html( $location_link['name'] ); ?></span>
            </a>
        <?php else : ?>
            <span class="fw-workshop-location-item">
                <span class="fw-workshop-location-label"><?php echo esc_html( findewerkstatt_t( $location_link['label'] ) ); ?></span>
                <span class="fw-workshop-location-name"><?php echo esc_html( $location_link['name'] ); ?></span>
            </span>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
