<?php
/**
 * FindeWerkstatt.de — Domain-, Staging- & Legacy-URL-Schutz (Domain Guard)
 *
 * 1. Verhindert Indexierung von Staging-/Entwicklungsumgebungen (z. B. serkan.ototamircibul.com.tr)
 * 2. Setzt kanonische URLs (Canonical) immer auf die Hauptdomain https://findewerkstatt.de
 * 3. Leitet alte Portfolio- und Dummy-URLs (/about-us/, /services/, /portfolio/) per 301 um
 *
 * @package FindeWerkstatt
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FindeWerkstatt_Domain_Guard {

    const PRODUCTION_DOMAIN = 'findewerkstatt.de';
    const PRODUCTION_URL    = 'https://findewerkstatt.de';

    /**
     * Initialisiert alle Hooks.
     */
    public static function init() {
        // 1. Staging-Erkennung & Noindex
        if ( self::is_staging_environment() ) {
            add_action( 'wp_head', array( __CLASS__, 'output_noindex_meta' ), 1 );
            add_filter( 'pre_option_blog_public', '__return_zero' );
            add_filter( 'robots_txt', array( __CLASS__, 'filter_robots_for_staging' ), 999 );
        }

        // 2. Canonical URL Schutz (Verweist immer auf findewerkstatt.de)
        add_filter( 'get_canonical_url', array( __CLASS__, 'enforce_production_canonical' ), 99 );
        add_filter( 'wp_get_canonical_url', array( __CLASS__, 'enforce_production_canonical' ), 99 );

        // 3. 301-Weiterleitung alter Portfolio- und Demo-Pfade
        add_action( 'template_redirect', array( __CLASS__, 'handle_legacy_redirects' ), 1 );
    }

    /**
     * Prüft, ob die aktuelle Anfrage auf einer Staging-/Test-Domain läuft.
     */
    public static function is_staging_environment() {
        $host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) ) : '';
        if ( empty( $host ) ) {
            return false;
        }

        // Spezifisch serkan.ototamircibul.com.tr und andere Subdomains
        if ( str_contains( $host, 'ototamircibul.com.tr' ) ) {
            return true;
        }

        // Entwicklungs- und Staging-Muster
        if ( str_contains( $host, 'staging' ) || str_contains( $host, 'dev.' ) || str_contains( $host, 'test.' ) ) {
            return true;
        }

        return false;
    }

    /**
     * Gibt im <head> strict noindex, nofollow für Staging aus.
     */
    public static function output_noindex_meta() {
        echo "<!-- FindeWerkstatt.de Staging Guard: Search Engine Indexing Disabled -->\n";
        echo '<meta name="robots" content="noindex, nofollow, noarchive, nosnippet">' . "\n";
        echo '<meta name="googlebot" content="noindex, nofollow, noarchive, nosnippet">' . "\n";
    }

    /**
     * Sperrt die gesamte Staging-Site in der robots.txt.
     */
    public static function filter_robots_for_staging( $output ) {
        return "# FindeWerkstatt Staging Guard\nUser-agent: *\nDisallow: /\n";
    }

    /**
     * Zwingt Canonical-URLs auf die kanonische Produktivdomain findewerkstatt.de.
     */
    public static function enforce_production_canonical( $canonical_url ) {
        if ( empty( $canonical_url ) || ! is_string( $canonical_url ) ) {
            return $canonical_url;
        }

        $host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) ) : '';
        
        // Wenn auf serkan.ototamircibul.com.tr aufgerufen, ersetze durch findewerkstatt.de
        if ( ! empty( $host ) && str_contains( $host, 'ototamircibul.com.tr' ) ) {
            $parsed = wp_parse_url( $canonical_url );
            $path   = $parsed['path'] ?? '/';
            $query  = isset( $parsed['query'] ) ? '?' . $parsed['query'] : '';
            return self::PRODUCTION_URL . $path . $query;
        }

        return $canonical_url;
    }

    /**
     * Fängt alte Portfolio-URLs ab und leitet sie per 301 auf relevante FindeWerkstatt-Seiten weiter.
     */
    public static function handle_legacy_redirects() {
        if ( is_admin() || wp_doing_ajax() ) {
            return;
        }

        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
        $path        = strtolower( trim( (string) wp_parse_url( $request_uri, PHP_URL_PATH ), '/' ) );

        if ( empty( $path ) ) {
            return;
        }

        // 1. /about-us -> /ueber-uns/
        if ( 'about-us' === $path || 'about' === $path ) {
            $target = function_exists( 'findewerkstatt_page_url' ) ? findewerkstatt_page_url( 'ueber-uns' ) : home_url( '/ueber-uns/' );
            wp_safe_redirect( $target, 301 );
            exit;
        }

        // 2. /services -> /werkstaetten/
        if ( 'services' === $path ) {
            $target = get_post_type_archive_link( 'mechanic' ) ?: home_url( '/werkstaetten/' );
            wp_safe_redirect( $target, 301 );
            exit;
        }

        // 3. /portfolio oder /portfolio/* -> Startseite
        if ( 'portfolio' === $path || str_starts_with( $path, 'portfolio/' ) ) {
            wp_safe_redirect( home_url( '/' ), 301 );
            exit;
        }
    }
}

// Initialisieren
FindeWerkstatt_Domain_Guard::init();
