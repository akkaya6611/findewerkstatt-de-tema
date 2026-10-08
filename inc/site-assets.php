<?php
/** Serve public text files at the website root, including subdirectory installations. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Resolve public document links against the current WordPress page permalinks. */
function findewerkstatt_site_asset_contents( $file ) {
    if ( ! in_array( $file, array( 'llms.txt', 'llms-full.txt', 'ads.txt' ), true ) ) { return false; }
    if ( 'llms-full.txt' === $file && function_exists( 'findewerkstatt_generate_dynamic_llms_content' ) ) {
        return findewerkstatt_generate_dynamic_llms_content( true );
    }
    $source_file = 'llms-full.txt' === $file ? 'llms.txt' : $file;
    $contents = file_get_contents( get_template_directory() . '/' . $source_file );
    if ( false === $contents ) { return false; }
    $links = array( 'https://findewerkstatt.de/werkstaetten/' => get_post_type_archive_link( 'mechanic' ) );
    foreach ( array( 'werkstatt-anmelden', 'ueber-uns', 'kontakt', 'impressum', 'datenschutz', 'agb', 'pakete', 'mein-konto' ) as $slug ) {
        $links[ 'https://findewerkstatt.de/' . $slug . '/' ] = findewerkstatt_page_url( $slug );
    }
    $contents = strtr( $contents, $links );
    $contents = str_replace( 'https://findewerkstatt.de', untrailingslashit( home_url() ), $contents );
    if ( 'ads.txt' === $file && function_exists( 'apply_filters' ) ) {
        return apply_filters( 'findewerkstatt_ads_txt_content', $contents );
    }
    return $contents;
}

add_action( 'template_redirect', function () {
    if ( ! in_array( $_SERVER['REQUEST_METHOD'] ?? 'GET', array( 'GET', 'HEAD' ), true ) ) { return; }
    $path = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
    $base = trailingslashit( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ) );
    foreach ( array( 'llms.txt', 'llms-full.txt', 'ads.txt' ) as $file ) {
        if ( $path !== $base . $file ) { continue; }
        $contents = findewerkstatt_site_asset_contents( $file );
        if ( $contents === false ) { return; }
        status_header( 200 );
        header( 'Content-Type: text/plain; charset=UTF-8' );
        header( 'X-Content-Type-Options: nosniff' );
        if ( ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) !== 'HEAD' ) {
            echo $contents;
        }
        exit;
    }
}, 0 );
