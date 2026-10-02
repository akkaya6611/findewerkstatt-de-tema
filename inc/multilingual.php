<?php
/** Server-rendered languages sharing the same directory and member records. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function findewerkstatt_languages() {
    return array( 'de' => array( 'label' => 'Deutsch', 'locale' => 'de_DE', 'tag' => 'de-DE' ), 'tr' => array( 'label' => 'Türkçe', 'locale' => 'tr_TR', 'tag' => 'tr-TR' ), 'en' => array( 'label' => 'English', 'locale' => 'en_US', 'tag' => 'en' ), 'ru' => array( 'label' => 'Русский', 'locale' => 'ru_RU', 'tag' => 'ru-RU' ) );
}

function findewerkstatt_language() {
    if ( isset( $GLOBALS['fw_test_language'] ) && defined( 'WP_CLI' ) && WP_CLI && isset( findewerkstatt_languages()[ $GLOBALS['fw_test_language'] ] ) ) { return $GLOBALS['fw_test_language']; }
    $action = isset( $_POST['action'] ) && is_string( $_POST['action'] ) ? wp_unslash( $_POST['action'] ) : '';
    if ( str_starts_with( $action, 'fw_' ) && is_string( $_POST['fw_language'] ?? null ) && isset( findewerkstatt_languages()[ $_POST['fw_language'] ] ) ) { return $_POST['fw_language']; }
    if ( is_admin() ) { return 'de'; }
    $path = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
    $base = trailingslashit( (string) wp_parse_url( get_option( 'home' ), PHP_URL_PATH ) );
    if ( '/' !== $base && str_starts_with( $path, $base ) ) { $path = substr( $path, strlen( $base ) ); }
    return preg_match( '#^(?:/)?(tr|en|ru)(?:/|$)#', $path, $matches ) ? $matches[1] : 'de';
}
function findewerkstatt_language_locale() { return findewerkstatt_languages()[ findewerkstatt_language() ]['locale']; }
function findewerkstatt_language_tag() { return findewerkstatt_languages()[ findewerkstatt_language() ]['tag']; }

function findewerkstatt_translation_catalog( $language = '' ) {
    static $catalogs = array();
    $language = $language ?: findewerkstatt_language();
    if ( 'de' === $language || ! isset( findewerkstatt_languages()[ $language ] ) ) { return array(); }
    if ( ! isset( $catalogs[ $language ] ) ) {
        $catalogs[ $language ] = array();
        foreach ( array( 'core', 'public', 'member', 'location', 'legal' ) as $area ) {
            $path = get_template_directory() . '/languages/' . $area . '-' . $language . '.php';
            if ( file_exists( $path ) ) { $values = require $path; if ( is_array( $values ) ) { $catalogs[ $language ] = array_replace( $catalogs[ $language ], $values ); } }
        }
    }
    return $catalogs[ $language ];
}
function findewerkstatt_t( $text ) {
    if ( ! is_string( $text ) ) { return ''; }
    return findewerkstatt_translation_catalog()[ $text ] ?? $text;
}
add_filter( 'gettext_findewerkstatt', static function ( $translated, $text ) { return findewerkstatt_t( $text ); }, 10, 2 );
add_filter( 'ngettext_findewerkstatt', static function ( $translated, $single, $plural, $number ) { return findewerkstatt_t( 1 === (int) $number ? $single : $plural ); }, 10, 4 );
add_filter( 'language_attributes', static function ( $attributes ) { return preg_replace( '/\blang="[^"]*"/', 'lang="' . esc_attr( str_replace( '_', '-', findewerkstatt_language_locale() ) ) . '"', $attributes ); } );

/** Idempotent URL transformation; external URLs and infrastructure stay untouched. */
function findewerkstatt_localize_url( $url, $language = '' ) {
    $language = $language ?: findewerkstatt_language();
    if ( ! is_string( $url ) || ! isset( findewerkstatt_languages()[ $language ] ) ) { return $url; }
    $home = untrailingslashit( get_option( 'home' ) );
    if ( $url !== $home && ! str_starts_with( $url, $home . '/' ) && ! str_starts_with( $url, $home . '?' ) && ! str_starts_with( $url, $home . '#' ) ) { return $url; }
    $suffix = substr( $url, strlen( $home ) );
    $suffix = preg_replace( '#^/(?:tr|en|ru)(?=/|\?|\#|$)#', '', $suffix );
    if ( preg_match( '#^/(?:wp-admin|wp-content|wp-includes|wp-json)(?:/|$)|^/(?:wp-login\.php|wp-cron\.php|wp-sitemap[^/]*\.xml|robots\.txt|ads\.txt|llms\.txt)(?:[?\#]|$)#', $suffix ) ) { return $home . $suffix; }
    if ( '' === $suffix || '?' === substr( $suffix, 0, 1 ) || '#' === substr( $suffix, 0, 1 ) ) { $suffix = '/' . $suffix; }
    return $home . ( 'de' === $language ? '' : '/' . $language ) . $suffix;
}
function findewerkstatt_language_url( $language ) {
    $home = untrailingslashit( get_option( 'home' ) );
    $base = untrailingslashit( (string) wp_parse_url( $home, PHP_URL_PATH ) );
    $uri = is_string( $_SERVER['REQUEST_URI'] ?? null ) ? $_SERVER['REQUEST_URI'] : '/';
    if ( '' !== $base && str_starts_with( $uri, $base . '/' ) ) { $uri = substr( $uri, strlen( $base ) ); }
    $parts = wp_parse_url( $home . '/' . ltrim( $uri, '/' ) );
    $query = array(); parse_str( $parts['query'] ?? '', $query );
    // A one-use notice belongs to the current response, not another language URL.
    unset( $query['fw_notice'], $query['fw_member_notice'], $query['fw_language'] );
    $url = $home . ( $parts['path'] ?? '/' );
    if ( '' !== $base ) { $url = $home . substr( $parts['path'] ?? '/', strlen( $base ) ); }
    if ( $query ) { $url = add_query_arg( $query, $url ); }
    return findewerkstatt_localize_url( $url, $language );
}
function findewerkstatt_language_field() { echo '<input type="hidden" name="fw_language" value="' . esc_attr( findewerkstatt_language() ) . '">'; }

// WordPress must parse against its physical home, while templates link to the language home.
add_filter( 'do_parse_request', static function ( $parse ) { $GLOBALS['fw_parsing_request'] = (bool) $parse; return $parse; }, 999 );
add_action( 'parse_request', static function ( $wp ) {
    $GLOBALS['fw_parsing_request'] = false;
    if ( 'de' !== findewerkstatt_language() ) { $wp->request = preg_replace( '#^(?:tr|en|ru)(?:/|$)#', '', $wp->request ); }
}, -100 );
function findewerkstatt_language_rewrite_rules( $rules ) {
    if ( ! is_array( $rules ) ) { return $rules; }
    $translated = array( '^(?:tr|en|ru)/?$' => 'index.php' );
    foreach ( $rules as $pattern => $query ) {
        if ( str_contains( $pattern, '(?:tr|en|ru)' ) || preg_match( '#^(?:\^)?(?:wp-sitemap|wp-json|robots|favicon)#', $pattern ) ) { continue; }
        $translated[ '^(?:tr|en|ru)/' . ltrim( $pattern, '^' ) ] = $query;
    }
    return $translated + $rules;
}
add_filter( 'option_rewrite_rules', 'findewerkstatt_language_rewrite_rules' );
add_filter( 'rewrite_rules_array', 'findewerkstatt_language_rewrite_rules' );
add_filter( 'home_url', static function ( $url ) { return ! empty( $GLOBALS['fw_parsing_request'] ) ? $url : findewerkstatt_localize_url( $url ); }, 20 );
foreach ( array( 'post_link', 'page_link', 'post_type_link', 'post_type_archive_link', 'term_link', 'get_canonical_url', 'get_pagenum_link' ) as $hook ) { add_filter( $hook, 'findewerkstatt_localize_url', 20, 1 ); }
add_filter( 'wp_redirect', static function ( $url ) { return findewerkstatt_localize_url( $url ); } );
add_filter( 'redirect_canonical', static function ( $url ) { return $url ? findewerkstatt_localize_url( $url ) : $url; }, 99 );
add_filter( 'document_title_parts', static function ( $parts ) {
    if ( ! is_singular( 'mechanic' ) && ! is_tax( 'mechanic_city' ) ) { foreach ( $parts as $key => $value ) { $parts[ $key ] = findewerkstatt_t( $value ); } }
    return $parts;
}, 99 );

add_action( 'wp_head', static function () {
    if ( is_404() || is_search() || is_feed() || is_page( 'mein-konto' ) || is_page_template( 'page-mein-konto.php' ) || is_preview() ) { return; }
    foreach ( findewerkstatt_languages() as $language => $data ) { echo '<link rel="alternate" hreflang="' . esc_attr( $data['tag'] ) . '" href="' . esc_url( findewerkstatt_language_url( $language ) ) . '">' . "\n"; }
    echo '<link rel="alternate" hreflang="x-default" href="' . esc_url( findewerkstatt_language_url( 'de' ) ) . '">' . "\n";
}, 5 );

add_action( 'wp_enqueue_scripts', static function () {
    $keys = array( 'Alle Städte und Orte', 'Erst Bundesland wählen', 'Die Städteauswahl für %s ist verfügbar.', 'Wählen Sie zuerst ein Bundesland.', 'Menü schließen', 'Menü öffnen' );
    $dictionary = array(); foreach ( $keys as $key ) { $dictionary[ $key ] = findewerkstatt_t( $key ); }
    wp_add_inline_script( 'findewerkstatt-main', 'window.fwI18n=' . wp_json_encode( $dictionary, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . ';', 'before' );
}, 30 );

add_filter( 'wp_date', static function ( $date ) {
    $names = array( 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag' );
    return strtr( $date, array_combine( $names, array_map( 'findewerkstatt_t', $names ) ) );
} );

/** Reuse core sitemap queries, including existing private/empty-directory exclusions. */
add_action( 'wp_sitemaps_init', static function ( $server ) {
    $server->registry->add_provider( 'languages', new class( $server->registry ) extends WP_Sitemaps_Provider {
        protected $name = 'languages';
        protected $object_type = 'languages';
        private $registry;
        public function __construct( $registry ) { $this->registry = $registry; }
        public function get_object_subtypes() {
            $subtypes = array();
            foreach ( array( 'posts', 'taxonomies' ) as $name ) {
                $provider = $this->registry->get_provider( $name ); if ( ! $provider ) { continue; }
                foreach ( $provider->get_object_subtypes() as $type => $data ) {
                    if ( ! in_array( $type, array( 'page', 'post', 'mechanic', 'mechanic_city', 'service_type', 'car_brand' ), true ) ) { continue; }
                    foreach ( array( 'tr', 'en', 'ru' ) as $language ) { $subtypes[ $language . '-' . $name . '-' . $type ] = $data; }
                }
            }
            return $subtypes;
        }
        private function source( $subtype ) {
            if ( ! is_string( $subtype ) || ! isset( $this->get_object_subtypes()[ $subtype ] ) ) { return array( null, '', '' ); }
            $parts = explode( '-', $subtype, 3 ); return array( $this->registry->get_provider( $parts[1] ), $parts[2], $parts[0] );
        }
        public function get_url_list( $page_num, $object_subtype = '' ) {
            list( $provider, $type, $language ) = $this->source( $object_subtype ); if ( ! $provider ) { return array(); }
            $entries = $provider->get_url_list( $page_num, $type );
            foreach ( $entries as &$entry ) { $entry['loc'] = findewerkstatt_localize_url( $entry['loc'], $language ); } unset( $entry );
            return $entries;
        }
        public function get_max_num_pages( $object_subtype = '' ) {
            list( $provider, $type ) = $this->source( $object_subtype ); return $provider ? $provider->get_max_num_pages( $type ) : 0;
        }
    } );
} );
