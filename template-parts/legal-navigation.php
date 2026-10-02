<?php
/** Shared navigation for the German legal information pages. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$legal_pages = array(
    'impressum' => array( 'Impressum', 'page-impressum.php' ),
    'datenschutz' => array( 'Datenschutz', 'page-datenschutz.php' ),
    'agb' => array( 'Nutzungsbedingungen', 'page-agb.php' ),
);
?>
<p class="fw-muted fw-legal-date"><?php echo esc_html( findewerkstatt_t( 'Stand: 1. Oktober 2026' ) ); ?></p>
<nav class="fw-legal-nav" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Rechtliche Informationen' ) ); ?>">
    <?php foreach ( $legal_pages as $slug => $page ) : ?>
        <a href="<?php echo esc_url( findewerkstatt_page_url( $slug ) ); ?>"<?php if ( is_page_template( $page[1] ) || is_page( $slug ) ) : ?> aria-current="page"<?php endif; ?>><?php echo esc_html( findewerkstatt_t( $page[0] ) ); ?></a>
    <?php endforeach; ?>
</nav>
