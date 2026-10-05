<?php
/** Standalone regression tests for the Blog & Ratgeber System */
if ( PHP_SAPI !== 'cli' ) { http_response_code( 404 ); exit; }
define( 'ABSPATH', __DIR__ );

$meta = array();
$hooks = array();
$shortcodes = array();

function add_action( ...$args ) { global $hooks; $hooks[] = $args; }
function add_filter( ...$args ) { global $hooks; $hooks[] = $args; }
function add_rewrite_rule( ...$args ) { global $hooks; $hooks[] = array( 'rewrite_rule', $args ); }
function add_shortcode( $tag, $callback ) { global $shortcodes; $shortcodes[ $tag ] = $callback; }
function shortcode_atts( $pairs, $atts, $shortcode = '' ) {
    $atts = (array) $atts;
    $out = array();
    foreach ( $pairs as $name => $default ) {
        if ( array_key_exists( $name, $atts ) ) {
            $out[ $name ] = $atts[ $name ];
        } else {
            $out[ $name ] = $default;
        }
    }
    return $out;
}
function absint( $val ) { return abs( (int) $val ); }
function get_the_ID() { return 101; }
function has_post_thumbnail( $id ) { return 101 === (int) $id; }
function get_the_post_thumbnail_url( $id, $size = 'large' ) { return 101 === (int) $id ? 'https://findewerkstatt.de/sample-thumb.jpg' : ''; }
function get_post_field( $field, $id ) {
    if ( 102 === (int) $id ) {
        return '<p>Text with <img src="https://findewerkstatt.de/content-img.png" alt="Auto"> more text.</p>';
    }
    return 'Dies ist ein einfacher Text für den Kfz-Ratgeber. ' . str_repeat( 'Wort ', 400 );
}
function wp_strip_all_tags( $text ) { return strip_tags( (string) $text ); }
function esc_html( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $text ) { return esc_html( $text ); }
function esc_url( $text ) { return $text; }
function number_format_i18n( $val, $dec = 0 ) { return number_format( (float) $val, $dec, ',', '.' ); }
function get_template_directory_uri() { return 'https://findewerkstatt.de/wp-content/themes/findewerkstatt-de-tema'; }
function home_url( $path = '/' ) { return 'https://findewerkstatt.de' . $path; }
function is_admin() { return false; }
function get_query_var( $var, $default = '' ) { return 'fw_blog_archive' === $var ? '1' : $default; }
function is_home() { return false; }
function is_front_page() { return false; }
function is_category() { return false; }
function sanitize_title( $text ) { return strtolower( trim( preg_replace( '/[^a-zA-Z0-9_-]+/', '-', $text ), '-' ) ); }
function findewerkstatt_t( $text ) { return $text; }

require dirname( __DIR__ ) . '/inc/blog-system.php';

$checks = 0;
function expect_true( $condition, $label ) {
    global $checks;
    if ( ! $condition ) {
        fwrite( STDERR, "FAILED: {$label}\n" );
        exit( 1 );
    }
    $checks++;
}

// Check 1: Reading time calculation
$content_400_words = str_repeat( 'Auto Reparatur Motor Bremse ', 100 ); // 400 words => 2 Min.
$rt = findewerkstatt_reading_time( $content_400_words );
expect_true( false !== strpos( $rt, '2 Min. Lesezeit' ), 'Reading time for 400 words should be 2 Min.' );

// Check 2: Minimum reading time is 1 Min.
$rt_short = findewerkstatt_reading_time( 'Kurzer Text' );
expect_true( false !== strpos( $rt_short, '1 Min. Lesezeit' ), 'Short reading time should be minimum 1 Min.' );

// Check 3: Post thumbnail with featured image
$thumb_101 = findewerkstatt_post_thumbnail_url( 101 );
expect_true( 'https://findewerkstatt.de/sample-thumb.jpg' === $thumb_101, 'Featured image URL should be used when available' );

// Check 4: Post thumbnail extracted from content
$thumb_102 = findewerkstatt_post_thumbnail_url( 102 );
expect_true( 'https://findewerkstatt.de/content-img.png' === $thumb_102, 'Image inside post content should be extracted if no thumbnail' );

// Check 5: Post thumbnail fallback
$thumb_fallback = findewerkstatt_post_thumbnail_url( 999 );
expect_true( false !== strpos( $thumb_fallback, 'banner-werkstatt-diagnostic.jpg' ), 'Fallback banner should be returned if no image found' );

// Check 6: Query vars registered
$vars = FindeWerkstatt_Blog_System::register_query_vars( array( 's' ) );
expect_true( in_array( 'fw_blog_archive', $vars, true ), 'fw_blog_archive should be registered in query_vars' );

// Check 7: Shortcode registered
expect_true( isset( $shortcodes['global_listing_slider'] ), 'global_listing_slider shortcode must be registered' );

// Check 8: Document title for blog archive
$title_parts = FindeWerkstatt_Blog_System::filter_document_title( array( 'title' => 'Test' ) );
expect_true( ! empty( $title_parts['title'] ), 'Document title should not be empty' );

echo "Blog-System: {$checks} Prüfungen erfolgreich.\n";
