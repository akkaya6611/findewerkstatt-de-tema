<?php
/**
 * FindeWerkstatt.de — Helper Functions
 * 
 * Beinhaltet Hilfsfunktionen für Sternebewertungen, Abzeichen,
 * Status "Jetzt geöffnet", Telefon- und Routenformatierung.
 * 
 * @package FindeWerkstatt
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Bewertungs-Sterne HTML ausgeben
 */
function findewerkstatt_render_stars( $rating = 4.9, $count = 18 ) {
    $rating = (float) $rating;
    $count  = (int) $count;
    $stars  = '';
    
    for ( $i = 1; $i <= 5; $i++ ) {
        if ( $rating >= $i ) {
            $stars .= '★';
        } elseif ( $rating >= ( $i - 0.5 ) ) {
            $stars .= '★';
        } else {
            $stars .= '☆';
        }
    }
    
    return sprintf(
        '<div class="fw-card-rating">
            <span class="fw-stars" title="%1$s von 5 Sternen">%2$s</span>
            <span class="fw-rating-val">%1$s</span>
            <span class="fw-review-count">(%3$s Bewertungen)</span>
        </div>',
        number_format( $rating, 1, ',', '.' ),
        $stars,
        $count
    );
}

/**
 * Gütesiegel-Badges für Werkstatt ausgeben
 */
function findewerkstatt_render_badges( $post_id ) {
    $is_verified = get_post_meta( $post_id, '_mechanic_is_verified', true );
    $is_master   = get_post_meta( $post_id, '_mechanic_is_master', true );
    $is_24h      = get_post_meta( $post_id, '_mechanic_emergency_24h', true );
    $output      = '';

    if ( $is_verified === 'yes' ) {
        $output .= '<span class="fw-badge fw-badge-verified">🛡️ Geprüfter Partner</span>';
    }
    if ( $is_master === 'yes' ) {
        $output .= '<span class="fw-badge fw-badge-meister">🏆 Meisterbetrieb</span>';
    }
    if ( $is_24h === 'yes' ) {
        $output .= '<span class="fw-badge fw-badge-urgent">🚨 24h Notdienst</span>';
    }

    return $output;
}

/**
 * Öffnungszeiten-Status prüfen (Europe/Berlin Zeitzone)
 */
function findewerkstatt_get_open_status() {
    $now = new DateTime( 'now', new DateTimeZone( 'Europe/Berlin' ) );
    $day = (int) $now->format( 'w' ); // 0 = Sonntag, 6 = Samstag
    $hour = (int) $now->format( 'G' ); // 0 - 23

    // Mo - Fr (8 - 18)
    if ( $day >= 1 && $day <= 5 ) {
        if ( $hour >= 8 && $hour < 18 ) {
            return '<span class="fw-card-status" style="background:#10b981;">● Jetzt geöffnet</span>';
        }
    }
    // Sa (9 - 13)
    elseif ( $day === 6 ) {
        if ( $hour >= 9 && $hour < 13 ) {
            return '<span class="fw-card-status" style="background:#10b981;">● Jetzt geöffnet</span>';
        }
    }

    return '<span class="fw-card-status" style="background:#64748b;">○ Derzeit geschlossen</span>';
}

/**
 * Google Maps Routen-URL generieren
 */
function findewerkstatt_get_maps_url( $title, $address, $city = '' ) {
    $query = urlencode( trim( "{$title} {$address} {$city} Deutschland" ) );
    return "https://www.google.com/maps/search/?api=1&query={$query}";
}

/**
 * WhatsApp Chat-Link generieren
 */
function findewerkstatt_get_whatsapp_url( $phone, $title ) {
    $clean = preg_replace( '/[^0-9]/', '', (string) $phone );
    if ( empty( $clean ) ) {
        return '';
    }
    $text = rawurlencode( "Hallo {$title}, ich habe Ihr Werkstatt-Profil auf FindeWerkstatt.de gesehen und möchte einen Termin anfragen." );
    return "https://wa.me/{$clean}?text={$text}";
}
