<?php
/**
 * FindeWerkstatt.de — Hervorgehobene Werkstätten (Top-Empfehlungen & Monetarisierung)
 *
 * Markiert Werkstätten mit Plus- / Professional-Paket oder manueller Hervorhebung
 * mit dem Gütesiegel "TOP EMPFEHLUNG" und priorisiert sie in den Suchergebnissen.
 *
 * @package FindeWerkstatt
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Prüfen, ob ein Eintrag hervorgehoben ist (Featured / Gesponsert).
 *
 * @param int $post_id Werkstatt-ID.
 * @return bool
 */
function findewerkstatt_is_featured( $post_id = 0 ) {
    $post_id = $post_id ? absint( $post_id ) : get_the_ID();
    if ( ! $post_id ) {
        return false;
    }

    $is_explicit = get_post_meta( $post_id, '_mechanic_is_featured', true );
    if ( 'yes' === $is_explicit || '1' === $is_explicit || true === $is_explicit ) {
        return true;
    }

    $plan = get_post_meta( $post_id, '_mechanic_plan', true );
    if ( in_array( $plan, array( 'plus', 'professional' ), true ) ) {
        return true;
    }

    return false;
}

/**
 * Featured-Badge HTML ausgeben.
 *
 * @param int $post_id
 * @return string HTML
 */
function findewerkstatt_render_featured_badge( $post_id = 0 ) {
    if ( ! findewerkstatt_is_featured( $post_id ) ) {
        return '';
    }

    return '<span class="fw-badge fw-badge-featured">' .
           '<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" stroke="none" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>' .
           '<span>' . esc_html( findewerkstatt_t( 'TOP EMPFEHLUNG' ) ) . '</span>' .
           '</span>';
}

/**
 * Hauptabfrage modifizieren: Hervorgehobene Werkstätten nach oben sortieren.
 * Greift in Archiv- und Taxonomie-Seiten (Stadt, Marke, Service).
 */
function findewerkstatt_prioritize_featured_listings( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }

    // Nur bei Mechanic-Archiven oder entsprechenden Taxonomien
    $is_mechanic_query = (
        $query->is_post_type_archive( 'mechanic' ) ||
        $query->is_tax( array( 'city', 'service_type', 'car_brand' ) )
    );

    if ( ! $is_mechanic_query ) {
        return;
    }

    // Wenn keine spezielle Sortierung wie Distanz gewählt ist, Featured priorisieren
    if ( ! isset( $_GET['orderby'] ) || empty( $_GET['orderby'] ) ) {
        $meta_query = $query->get( 'meta_query' );
        if ( ! is_array( $meta_query ) ) {
            $meta_query = array();
        }

        $meta_query['relation'] = 'AND';
        $meta_query['featured_clause'] = array(
            'key'     => '_mechanic_is_featured',
            'compare' => 'EXISTS',
        );

        $query->set( 'meta_query', $meta_query );
        $query->set( 'orderby', array(
            'featured_clause' => 'DESC',
            'date'            => 'DESC',
        ) );
    }
}

if ( function_exists( 'add_action' ) ) {
    add_action( 'pre_get_posts', 'findewerkstatt_prioritize_featured_listings', 15 );
}


