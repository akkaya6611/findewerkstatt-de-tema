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
        '_mechanic_is_verified'   => array( 'fw-badge-verified', 'shield-check', 'Geprüfter Partner' ),
        '_mechanic_is_master'     => array( 'fw-badge-meister', 'trophy', 'Meisterbetrieb' ),
        '_mechanic_emergency_24h' => array( 'fw-badge-urgent', 'notdienst', '24h Notdienst' ),
    );
    $output = '';
    foreach ( $badges as $key => $badge ) {
        if ( 'yes' === get_post_meta( $post_id, $key, true ) ) {
            $icon_svg = function_exists( 'findewerkstatt_icon' ) ? findewerkstatt_icon( $badge[1], 12 ) : '';
            $output .= '<span class="fw-badge ' . esc_attr( $badge[0] ) . '">' . $icon_svg . '<span>' . esc_html( findewerkstatt_t( $badge[2] ) ) . '</span></span>';
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

function findewerkstatt_get_open_status( $post_id = null, $wrap = true ) {
    $post_id = null === $post_id ? get_the_ID() : $post_id;
    $state = findewerkstatt_get_open_state( $post_id );
    $labels = array(
        'open'      => 'Jetzt geöffnet',
        'closed'    => 'Derzeit geschlossen',
        'emergency' => '24h Notdienst verfügbar',
        'unknown'   => 'Öffnungszeiten nicht angegeben',
    );
    if ( ! $wrap ) {
        $dot_class = in_array( $state, array( 'open', 'emergency' ), true ) ? 'is-open' : 'is-closed';
        return '<span class="fw-card-status ' . esc_attr( $dot_class ) . '"><span class="fw-status-dot"></span><span>' . esc_html( findewerkstatt_t( $labels[ $state ] ) ) . '</span></span>';
    }
    $color = in_array( $state, array( 'open', 'emergency' ), true ) ? '#047857' : '#475569';
    return '<span class="fw-card-status fw-status-' . esc_attr( $state ) . '" style="background:' . $color . ';">' . esc_html( findewerkstatt_t( $labels[ $state ] ) ) . '</span>';
}

/** Format clean workshop address, removing duplicated company name prefixes. */
function findewerkstatt_clean_address( $address, $title = '', $plz = '', $city = '' ) {
    if ( ! is_string( $address ) ) {
        return '';
    }
    $clean = trim( $address );
    if ( $title && is_string( $title ) ) {
        $words = preg_split( '/[\s\-–—,._\/]+/u', trim( $title ), -1, PREG_SPLIT_NO_EMPTY );
        if ( ! empty( $words ) ) {
            $escaped = array_map( static function ( $w ) { return preg_quote( $w, '/' ); }, $words );
            $pattern = '/^\s*' . implode( '[\s\-–—,._\/]+', $escaped ) . '[\s\-–—,._\/]+/iu';
            $clean = (string) preg_replace( $pattern, '', $clean );
        }
    }
    $clean = ltrim( $clean, " ,\t\n\r\0\x0B-" );
    $location = trim( $plz . ' ' . $city );
    if ( $location && str_contains( $clean, $location ) ) {
        return $clean;
    }
    $parts = array_filter( array( $clean, $location ) );
    return implode( ', ', $parts );
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

/**
 * Liefert gestochen scharfe, barrierefreie SVG-Icons statt fehleranfälliger Emojis.
 */
function findewerkstatt_icon( $name, $size = 14, $class = '' ) {
    $class_attr = $class ? ' class="' . esc_attr( $class ) . '"' : '';
    $size = (int) $size;
    if ( $size <= 0 ) {
        $size = 14;
    }

    switch ( $name ) {
        case 'pin':
        case 'location':
        case 'map-pin':
            return '<svg' . $class_attr . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/></svg>';

        case 'star':
            return '<svg' . $class_attr . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>';

        case 'shield-check':
        case 'verified':
            return '<svg' . $class_attr . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><polyline points="9 12 11 14 15 10"></polyline></svg>';

        case 'award':
        case 'meister':
        case 'trophy':
            return '<svg' . $class_attr . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>';

        case 'urgent':
        case 'emergency':
        case 'notdienst':
        case 'siren':
        case 'clock-24':
            return '<svg' . $class_attr . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';

        case 'phone':
        case 'call':
            return '<svg' . $class_attr . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>';

        case 'whatsapp':
            return '<svg' . $class_attr . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.364 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/></svg>';

        case 'chat':
        case 'message':
        case 'language':
            return '<svg' . $class_attr . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>';

        case 'wrench':
        case 'freie-werkstatt':
            return '<svg' . $class_attr . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>';

        case 'tuev-hu-au':
            return '<svg' . $class_attr . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><polyline points="9 12 11 14 15 10"></polyline></svg>';

        case 'truck':
        case 'abschleppdienst-pannenhilfe':
            return '<svg' . $class_attr . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>';

        case 'sparkles':
        case 'autoglas-scheibenreparatur':
            return '<svg' . $class_attr . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v18M3 12h18M5.6 5.6l12.8 12.8M18.4 5.6L5.6 18.4"></path></svg>';

        case 'color-swatch':
        case 'karosserie-lackiererei':
            return '<svg' . $class_attr . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.93 0 1.65-.75 1.65-1.69 0-.44-.18-.84-.44-1.13-.29-.29-.44-.65-.44-1.13 0-.92.75-1.67 1.67-1.67h2c3.05 0 5.56-2.51 5.56-5.56C22 6.5 17.5 2 12 2z"></path><circle cx="7.5" cy="10.5" r="1.5" fill="currentColor"></circle><circle cx="12" cy="7.5" r="1.5" fill="currentColor"></circle><circle cx="16.5" cy="10.5" r="1.5" fill="currentColor"></circle></svg>';

        case 'circle-stack':
        case 'wheel':
        case 'reifenservice-raederwechsel':
            return '<svg' . $class_attr . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="3.5"></circle><line x1="12" y1="2" x2="12" y2="8.5"></line><line x1="12" y1="15.5" x2="12" y2="22"></line><line x1="2" y1="12" x2="8.5" y2="12"></line><line x1="15.5" y1="12" x2="22" y2="12"></line></svg>';

        case 'cpu-chip':
        case 'kfz-elektrik-elektronik':
            return '<svg' . $class_attr . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>';

        case 'sun':
        case 'klimaservice-standheizung':
            return '<svg' . $class_attr . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>';

        case 'cog-6-tooth':
        case 'bremsenservice-fahrwerk':
            return '<svg' . $class_attr . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path></svg>';

        case 'fire':
        case 'motor-getriebeinstandsetzung':
            return '<svg' . $class_attr . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.07-2.14-.22-4.05 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.15.43-2.29 1-3a2.5 2.5 0 0 0 2.5 2.5z"></path></svg>';

        case 'e-auto-ladestationen':
            return '<svg' . $class_attr . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 18H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h3.19M15 6h2a2 2 0 0 1 2 2v8"></path><path d="M23 13v-2"></path><polyline points="11 6 7 12 13 12 9 18"></polyline></svg>';

        case 'car':
            return '<svg' . $class_attr . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 16H9m10 0h3v-3.15a1 1 0 0 0-.84-.99L16 11l-2.7-3.6a1 1 0 0 0-.8-.4H7.5a1 1 0 0 0-.8.4L4 11l-5.16.86a1 1 0 0 0-.84.99V16h3m14 0a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0zm-10 0a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"></path></svg>';

        case 'building':
            return '<svg' . $class_attr . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="9" y1="22" x2="9" y2="12"></line><line x1="15" y1="22" x2="15" y2="12"></line><line x1="9" y1="12" x2="15" y2="12"></line><line x1="9" y1="6" x2="9" y2="6.01"></line><line x1="15" y1="6" x2="15" y2="6.01"></line></svg>';

        case 'search':
            return '<svg' . $class_attr . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>';

        default:
            return '<svg' . $class_attr . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>';
    }
}

function findewerkstatt_service_icon( $slug_or_term, $size = 12, $class = 'fw-badge-service-icon' ) {
    $slug = is_object( $slug_or_term ) && isset( $slug_or_term->slug ) ? $slug_or_term->slug : (string) $slug_or_term;
    return findewerkstatt_icon( $slug, $size, $class );
}

/**
 * Ergänzt inaktive Vor-/Zurück-Schaltflächen auf der ersten und letzten Seite,
 * damit die Blätterfunktion optisch und ergonomisch immer vollständig bleibt.
 */
function findewerkstatt_filter_paginate_links_output( $r, $args = array() ) {
    if ( ! is_string( $r ) || '' === trim( $r ) ) {
        return $r;
    }

    $total     = isset( $args['total'] ) ? (int) $args['total'] : 0;
    $current   = isset( $args['current'] ) ? (int) $args['current'] : 1;
    $prev_next = ! isset( $args['prev_next'] ) || ! empty( $args['prev_next'] );

    if ( ! $prev_next || $total <= 1 ) {
        return $r;
    }

    $prev_text = ! empty( $args['prev_text'] ) ? $args['prev_text'] : ( function_exists( 'findewerkstatt_t' ) ? findewerkstatt_t( '← Zurück' ) : '← Zurück' );
    $next_text = ! empty( $args['next_text'] ) ? $args['next_text'] : ( function_exists( 'findewerkstatt_t' ) ? findewerkstatt_t( 'Weiter →' ) : 'Weiter →' );

    // Inaktiven Zurück-Button auf Seite 1 voranstellen, falls noch kein Zurück-Link vorhanden ist
    if ( $current <= 1 && ! preg_match( '/\bprev\b/', $r ) ) {
        $disabled_prev = '<span class="page-numbers prev disabled" aria-disabled="true">' . $prev_text . '</span>';
        $r = $disabled_prev . "\n" . $r;
    }

    // Inaktiven Weiter-Button auf der letzten Seite anhängen, falls noch kein Weiter-Link vorhanden ist
    if ( $current >= $total && ! preg_match( '/\bnext\b/', $r ) ) {
        $disabled_next = '<span class="page-numbers next disabled" aria-disabled="true">' . $next_text . '</span>';
        $r = $r . "\n" . $disabled_next;
    }

    return $r;
}
add_filter( 'paginate_links_output', 'findewerkstatt_filter_paginate_links_output', 10, 2 );

/**
 * Einheitliche Paginierung für Archiv-, Taxonomie- und Suchseiten.
 */
function findewerkstatt_pagination( $args = array(), $query = null ) {
    global $wp_query;
    $target_query = ( null !== $query && is_object( $query ) && isset( $query->max_num_pages ) ) ? $query : $wp_query;

    if ( ! is_object( $target_query ) || ! isset( $target_query->max_num_pages ) || (int) $target_query->max_num_pages <= 1 ) {
        return;
    }

    $screen_reader_text = function_exists( 'findewerkstatt_t' ) ? findewerkstatt_t( 'Weitere Ergebnisse' ) : 'Weitere Ergebnisse';
    if ( function_exists( 'findewerkstatt_location_translate' ) && function_exists( 'is_tax' ) && is_tax( 'mechanic_city' ) ) {
        $screen_reader_text = findewerkstatt_location_translate( 'Weitere Werkstätten' );
    }

    $prev_text = function_exists( 'findewerkstatt_t' ) ? findewerkstatt_t( '← Zurück' ) : '← Zurück';
    $next_text = function_exists( 'findewerkstatt_t' ) ? findewerkstatt_t( 'Weiter →' ) : 'Weiter →';

    $defaults = array(
        'mid_size'           => 2,
        'prev_text'          => $prev_text,
        'next_text'          => $next_text,
        'screen_reader_text' => $screen_reader_text,
    );

    if ( function_exists( 'wp_parse_args' ) ) {
        $args = wp_parse_args( $args, $defaults );
    } else {
        $args = array_merge( $defaults, (array) $args );
    }

    $current = 1;
    if ( function_exists( 'get_query_var' ) ) {
        $current = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
    }
    $args['total']   = (int) $target_query->max_num_pages;
    $args['current'] = $current;

    $swapped = false;
    if ( $target_query !== $wp_query ) {
        $original_wp_query = $wp_query;
        $wp_query = $target_query;
        $swapped = true;
    }

    echo '<div class="fw-pagination">';
    if ( function_exists( 'the_posts_pagination' ) ) {
        the_posts_pagination( $args );
    }
    echo '</div>';

    if ( $swapped ) {
        $wp_query = $original_wp_query;
    }
}

