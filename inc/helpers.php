<?php
/**
 * Validierte Angaben, Bewertungen und Öffnungszeiten für das Werkstattverzeichnis.
 *
 * @package FindeWerkstatt
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function findewerkstatt_normalize_rating( $rating ) {
    if ( ! is_scalar( $rating ) || trim( (string) $rating ) === '' ) {
        return null;
    }
    $rating = str_replace( ',', '.', trim( (string) $rating ) );
    if ( ! is_numeric( $rating ) || ! is_finite( (float) $rating ) ) {
        return null;
    }
    return max( 0.0, min( 5.0, (float) $rating ) );
}

function findewerkstatt_normalize_review_count( $count ) {
    if ( ! is_scalar( $count ) || ! is_numeric( $count ) || ! is_finite( (float) $count ) ) {
        return 0;
    }
    if ( (float) $count >= PHP_INT_MAX ) {
        return PHP_INT_MAX;
    }
    return (int) max( 0, (float) $count );
}

/** Fehlende Bewertungen bleiben unbekannt; es werden keine Standardwerte erfunden. */
function findewerkstatt_get_rating( $post_id ) {
    return array(
        'rating' => findewerkstatt_normalize_rating( get_post_meta( $post_id, '_mechanic_rating_avg', true ) ),
        'count'  => findewerkstatt_normalize_review_count( get_post_meta( $post_id, '_mechanic_rating_count', true ) ),
    );
}

/** Die tiefste zugewiesene Stadt statt eines ebenfalls zugewiesenen Bundeslands. */
function findewerkstatt_get_city_term( $post_id ) {
    $terms = get_the_terms( $post_id, 'mechanic_city' );
    if ( ! $terms || is_wp_error( $terms ) ) {
        return null;
    }
    $selected = null;
    $depth    = -1;
    foreach ( $terms as $term ) {
        $term_depth = count( get_ancestors( $term->term_id, 'mechanic_city', 'taxonomy' ) );
        if ( $term_depth > $depth || ( $term_depth === $depth && $selected && strcmp( $term->slug, $selected->slug ) < 0 ) ) {
            $selected = $term;
            $depth    = $term_depth;
        }
    }
    return $selected;
}

function findewerkstatt_render_stars( $rating = null, $count = 0 ) {
    $rating = findewerkstatt_normalize_rating( $rating );
    $count  = findewerkstatt_normalize_review_count( $count );
    if ( null === $rating ) {
        return '';
    }
    $stars = '';
    for ( $i = 1; $i <= 5; $i++ ) {
        $stars .= $rating >= ( $i - 0.5 ) ? '★' : '☆';
    }
    $value = number_format( $rating, 1, ',', '.' );
    $review_label = $count > 0 ? sprintf( findewerkstatt_t( 1 === $count ? '%s Bewertung' : '%s Bewertungen' ), number_format_i18n( $count ) ) : '';
    $reviews = '' !== $review_label ? '<span class="fw-review-count">(' . esc_html( $review_label ) . ')</span>' : '';
    $label = sprintf( findewerkstatt_t( '%s von 5 Sternen' ), $value ) . ( '' !== $review_label ? ', ' . $review_label : '' );
    return '<div class="fw-card-rating" role="img" aria-label="' . esc_attr( $label ) . '"><span class="fw-stars" aria-hidden="true">' . $stars . '</span><span class="fw-rating-val">' . esc_html( $value ) . '</span>' . $reviews . '</div>';
}

function findewerkstatt_render_badges( $post_id ) {
    $badges = array(
        '_mechanic_is_verified'   => array( 'fw-badge-verified', '🛡️ Geprüfter Partner' ),
        '_mechanic_is_master'     => array( 'fw-badge-meister', '🏆 Meisterbetrieb' ),
        '_mechanic_emergency_24h' => array( 'fw-badge-urgent', '🚨 24h Notdienst' ),
    );
    $output = '';
    foreach ( $badges as $key => $badge ) {
        if ( 'yes' === get_post_meta( $post_id, $key, true ) ) {
            $output .= '<span class="fw-badge ' . esc_attr( $badge[0] ) . '">' . esc_html( findewerkstatt_t( $badge[1] ) ) . '</span>';
        }
    }
    return $output;
}

/** null = unbekannt, array() = geschlossen, ansonsten Intervalle in Minuten. */
function findewerkstatt_parse_hours( $hours ) {
    if ( ! is_scalar( $hours ) ) {
        return null;
    }
    $hours = trim( (string) $hours );
    if ( '' === $hours ) {
        return null;
    }
    if ( preg_match( '/^(?:geschlossen|ruhetag|closed|-)\s*[.!]?$/iu', $hours ) ) {
        return array();
    }
    if ( preg_match( '/^(?:24\s*(?:h|stunden)(?:\s+(?:geöffnet|notdienst))?|rund\s+um\s+die\s+uhr)\s*[.!]?$/iu', $hours ) ) {
        return array( array( 0, 1440 ) );
    }
    $pattern = '/(?<![\d:.])(\d{1,2})(?:[:.](\d{2}))?\s*(?:Uhr\s*)?(?:-|–|—|bis)\s*(\d{1,2})(?:[:.](\d{2}))?\s*(?:Uhr)?(?![\d:.])/iu';
    if ( ! preg_match_all( $pattern, $hours, $matches, PREG_SET_ORDER ) ) {
        return null;
    }
    $remaining = preg_replace( $pattern, '', $hours );
    if ( ! preg_match( '/^[\s,;\/&+]*(?:(?:und|u\.)[\s,;\/&+]*)?$/iu', $remaining ) ) {
        return null;
    }
    $ranges = array();
    foreach ( $matches as $match ) {
        $start_hour   = (int) $match[1];
        $start_minute = isset( $match[2] ) && '' !== $match[2] ? (int) $match[2] : 0;
        $end_hour     = (int) $match[3];
        $end_minute   = isset( $match[4] ) && '' !== $match[4] ? (int) $match[4] : 0;
        if ( $start_hour > 23 || $end_hour > 24 || $start_minute > 59 || $end_minute > 59 || ( 24 === $end_hour && 0 !== $end_minute ) ) {
            return null;
        }
        $start = $start_hour * 60 + $start_minute;
        $end   = $end_hour * 60 + $end_minute;
        if ( $start === $end ) {
            return null;
        }
        $ranges[] = array( $start, $end );
    }
    return $ranges;
}

/** Getrennt vom HTML, damit Pausen, Tageswechsel und Zeitzonen geprüft werden können. */
function findewerkstatt_get_open_state( $post_id, $now = null ) {
    if ( ! $post_id ) {
        return 'unknown';
    }
    if ( 'yes' === get_post_meta( $post_id, '_mechanic_emergency_24h', true ) ) {
        return 'emergency';
    }
    $timezone = new DateTimeZone( 'Europe/Berlin' );
    $now = $now instanceof DateTimeInterface ? ( new DateTimeImmutable( $now->format( 'c' ) ) )->setTimezone( $timezone ) : new DateTimeImmutable( 'now', $timezone );
    $day     = (int) $now->format( 'w' );
    $minutes = (int) $now->format( 'G' ) * 60 + (int) $now->format( 'i' );
    $keys    = array( '_mechanic_hours_sunday', '_mechanic_hours_weekday', '_mechanic_hours_weekday', '_mechanic_hours_weekday', '_mechanic_hours_weekday', '_mechanic_hours_weekday', '_mechanic_hours_saturday' );
    $today   = findewerkstatt_parse_hours( get_post_meta( $post_id, $keys[ $day ], true ) );
    $before  = findewerkstatt_parse_hours( get_post_meta( $post_id, $keys[ ( $day + 6 ) % 7 ], true ) );
    foreach ( (array) $before as $range ) {
        if ( $range[0] > $range[1] && $minutes < $range[1] ) {
            return 'open';
        }
    }
    if ( null === $today ) {
        return 'unknown';
    }
    foreach ( $today as $range ) {
        if ( $range[0] < $range[1] && $minutes >= $range[0] && $minutes < $range[1] ) {
            return 'open';
        }
        if ( $range[0] > $range[1] && $minutes >= $range[0] ) {
            return 'open';
        }
    }
    return 'closed';
}

function findewerkstatt_get_open_status( $post_id = null ) {
    $post_id = null === $post_id ? get_the_ID() : $post_id;
    $state = findewerkstatt_get_open_state( $post_id );
    $labels = array(
        'open'      => '● Jetzt geöffnet',
        'closed'    => '○ Derzeit geschlossen',
        'emergency' => '● 24h Notdienst verfügbar',
        'unknown'   => 'Öffnungszeiten nicht angegeben',
    );
    $color = in_array( $state, array( 'open', 'emergency' ), true ) ? '#047857' : '#475569';
    return '<span class="fw-card-status fw-status-' . esc_attr( $state ) . '" style="background:' . $color . ';">' . esc_html( findewerkstatt_t( $labels[ $state ] ) ) . '</span>';
}

/** Deutsche Rufnummern für tel:- und WhatsApp-Links normalisieren. */
function findewerkstatt_normalize_phone( $phone ) {
    if ( ! is_scalar( $phone ) ) {
        return '';
    }
    $phone = trim( (string) $phone );
    if ( '' === $phone || ! preg_match( '/^\+?[0-9\s().\/-]+$/u', $phone ) ) {
        return '';
    }
    $digits = preg_replace( '/[^0-9]/', '', $phone );
    if ( 0 === strpos( $digits, '00' ) ) {
        $digits = substr( $digits, 2 );
    } elseif ( '+' !== substr( $phone, 0, 1 ) ) {
        if ( 0 === strpos( $digits, '0' ) ) {
            $digits = '49' . substr( $digits, 1 );
        } elseif ( 0 !== strpos( $digits, '49' ) ) {
            $digits = '49' . $digits;
        }
    }
    if ( 0 === strpos( $digits, '490' ) ) {
        $digits = '49' . substr( $digits, 3 );
    }
    return preg_match( '/^[1-9][0-9]{6,14}$/', $digits ) ? '+' . $digits : '';
}

/** Public website links accept complete HTTP URLs or domain names, never credentials. */
function findewerkstatt_get_website_url( $value ) {
    if ( ! is_string( $value ) || preg_match( '/[\x00-\x1F\x7F]|%(?:0[0-9a-f]|1[0-9a-f]|7f)/i', $value ) ) { return ''; }
    $value = trim( $value );
    if ( '' === $value || preg_match( '/\s/u', $value ) || strpos( $value, '\\' ) !== false ) { return ''; }
    if ( str_starts_with( $value, '//' ) ) { $value = 'https:' . $value; }
    elseif ( ! preg_match( '#^https?://#i', $value ) ) {
        // A domain followed by a numeric port is a host, rather than a URL scheme.
        if ( preg_match( '/^[a-z][a-z0-9+.-]*:/i', $value ) && ! preg_match( '~^[^:/?#]+:\d+(?:[/?#]|$)~', $value ) ) { return ''; }
        $value = 'https://' . $value;
    }
    $parts = wp_parse_url( $value );
    if ( ! is_array( $parts ) || ! in_array( strtolower( $parts['scheme'] ?? '' ), array( 'http', 'https' ), true )
        || empty( $parts['host'] ) || isset( $parts['user'] ) || isset( $parts['pass'] )
        || ( isset( $parts['port'] ) && ( $parts['port'] < 1 || $parts['port'] > 65535 ) ) ) { return ''; }
    $host = $parts['host'];
    $is_ip = filter_var( $host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 );
    if ( str_starts_with( $host, '[' ) && str_ends_with( $host, ']' ) ) {
        $is_ip = filter_var( substr( $host, 1, -1 ), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 );
    }
    if ( ! $is_ip ) {
        $labels = explode( '.', rtrim( $host, '.' ) );
        if ( count( $labels ) < 2 || strlen( $host ) > 253 || ! preg_match( '/\p{L}/u', end( $labels ) ) ) { return ''; }
        foreach ( $labels as $label ) {
            if ( ! preg_match( '/^[\p{L}\p{N}](?:[\p{L}\p{N}-]{0,61}[\p{L}\p{N}])?$/u', $label ) ) { return ''; }
        }
    }
    $value = preg_replace_callback( '#^https?://#i', function ( $match ) { return strtolower( $match[0] ); }, $value );
    return esc_url_raw( $value, array( 'http', 'https' ) );
}

function findewerkstatt_get_maps_url( $title, $address, $city = '' ) {
    $query = rawurlencode( trim( "{$title} {$address} {$city} Deutschland" ) );
    return "https://www.google.com/maps/search/?api=1&query={$query}";
}

/** Prefer a dedicated WhatsApp number, otherwise use the stored business phone. */
function findewerkstatt_get_whatsapp_url( $phone, $title, $fallback_phone = '' ) {
    $normalized = findewerkstatt_normalize_phone( $phone );
    if ( '' === $normalized ) {
        $normalized = findewerkstatt_normalize_phone( $fallback_phone );
    }
    if ( '' === $normalized ) {
        return '';
    }
    $clean = substr( $normalized, 1 );
    $title = strip_tags( html_entity_decode( (string) $title, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
    $text = rawurlencode( sprintf( findewerkstatt_t( 'Guten Tag, ich habe Ihren Betrieb „%s“ auf FindeWerkstatt.de gefunden und möchte eine Anfrage stellen.' ), $title ) );
    return "https://wa.me/{$clean}?text={$text}";
}
