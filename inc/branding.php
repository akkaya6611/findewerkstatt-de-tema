<?php
/** The operator's supplied brand artwork, shared by the site and WordPress icons. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function findewerkstatt_brand_asset_url( $asset ) {
    $files = array(
        'logo' => 'logo.png',
        'icon' => 'icon.jpg',
        'mascot_welcome' => 'mascot-welcome.png',
        'mascot_workshop' => 'mascot-workshop.png',
    );
    if ( ! isset( $files[ $asset ] ) ) { return ''; }
    return add_query_arg( 'ver', wp_get_theme()->get( 'Version' ), get_template_directory_uri() . '/assets/images/brand/' . $files[ $asset ] );
}

// Core prints browser, touch and administration icons. An admin-selected icon takes priority.
add_filter( 'get_site_icon_url', function ( $url ) {
    return $url ?: findewerkstatt_brand_asset_url( 'icon' );
} );
