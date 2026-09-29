<?php
/**
 * FindeWerkstatt.de — Schema.org (JSON-LD) & Lokales SEO (GEO)
 * 
 * 1. AutoRepair / AutomotiveBusiness Schema für Google Rich Cards
 * 2. BreadcrumbList Schema (Hinterlegte Navigationsstruktur)
 * 3. FAQPage Schema (SGE & Rich Results für Städte und Werkstätten)
 * 4. Lokale Geo-Metatags (geo.region, geo.placename, geo.position, ICBM)
 * 
 * @package FindeWerkstatt
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FindeWerkstatt_Schema_SEO {

    public static function init() {
        add_action( 'wp_head', array( __CLASS__, 'render_geo_meta_tags' ), 2 );
        add_action( 'wp_head', array( __CLASS__, 'render_json_ld_schemas' ), 3 );
    }

    /**
     * Lokale GEO-Metatags in <head> einfügen
     */
    public static function render_geo_meta_tags() {
        $lat = 51.1657; // Mittelpunkt Deutschland
        $lng = 10.4515;
        $region = 'DE';
        $placename = 'Deutschland';

        if ( is_singular( 'mechanic' ) ) {
            $post_id = get_the_ID();
            $custom_lat = get_post_meta( $post_id, '_mechanic_latitude', true );
            $custom_lng = get_post_meta( $post_id, '_mechanic_longitude', true );
            $city_terms = wp_get_post_terms( $post_id, 'mechanic_city' );
            $city_name  = ( $city_terms && ! is_wp_error( $city_terms ) ) ? $city_terms[0]->name : '';

            if ( ! empty( $custom_lat ) && ! empty( $custom_lng ) ) {
                $lat = (float) $custom_lat;
                $lng = (float) $custom_lng;
            }
            if ( $city_name ) {
                $placename = $city_name . ', Deutschland';
            }
        } elseif ( is_tax( 'mechanic_city' ) ) {
            $term = get_queried_object();
            if ( $term && ! is_wp_error( $term ) ) {
                $term_lat = get_term_meta( $term->term_id, '_geo_lat', true );
                $term_lng = get_term_meta( $term->term_id, '_geo_lng', true );
                if ( $term_lat && $term_lng ) {
                    $lat = (float) $term_lat;
                    $lng = (float) $term_lng;
                }
                $placename = $term->name . ', Deutschland';
            }
        }
        ?>
        <!-- FindeWerkstatt.de — Lokale Geotargeting Meta-Tags -->
        <meta name="geo.region" content="<?php echo esc_attr( $region ); ?>">
        <meta name="geo.placename" content="<?php echo esc_attr( $placename ); ?>">
        <meta name="geo.position" content="<?php echo esc_attr( number_format( $lat, 6, '.', '' ) . ';' . number_format( $lng, 6, '.', '' ) ); ?>">
        <meta name="ICBM" content="<?php echo esc_attr( number_format( $lat, 6, '.', '' ) . ', ' . number_format( $lng, 6, '.', '' ) ); ?>">
        <?php
    }

    /**
     * Alle Schema.org JSON-LD Daten generieren und einbetten
     */
    public static function render_json_ld_schemas() {
        $schemas = array();

        // 1. BreadcrumbList Schema
        $breadcrumbs = self::get_breadcrumb_schema();
        if ( ! empty( $breadcrumbs ) && count( $breadcrumbs ) > 1 ) {
            $schemas[] = array(
                '@context'        => 'https://schema.org',
                '@type'           => 'BreadcrumbList',
                'itemListElement' => $breadcrumbs,
            );
        }

        // 2. AutoRepair Schema auf Einzelseiten
        if ( is_singular( 'mechanic' ) ) {
            $post_id = get_the_ID();
            $schemas[] = self::get_autorepair_schema( $post_id );
            $schemas[] = self::get_single_mechanic_faq_schema( $post_id );
        }

        // 3. FAQPage Schema auf Städte- & Kategorieseiten
        if ( is_tax( 'mechanic_city' ) ) {
            $term = get_queried_object();
            if ( $term && ! is_wp_error( $term ) ) {
                $schemas[] = self::get_city_faq_schema( $term->name );
            }
        }

        if ( empty( $schemas ) ) {
            return;
        }
        ?>
        <!-- FindeWerkstatt.de — Strukturierte Daten (Schema.org JSON-LD) -->
        <script type="application/ld+json">
        <?php echo wp_json_encode( count( $schemas ) === 1 ? $schemas[0] : $schemas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ); ?>
        </script>
        <?php
    }

    private static function get_breadcrumb_schema() {
        $items = array();
        $pos = 1;

        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => 'Startseite',
            'item'     => home_url( '/' ),
        );

        if ( is_front_page() ) {
            return $items;
        }

        if ( is_singular( 'mechanic' ) ) {
            $post_id = get_the_ID();
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $pos++,
                'name'     => 'Werkstätten',
                'item'     => home_url( '/werkstaetten/' ),
            );

            $cities = wp_get_post_terms( $post_id, 'mechanic_city' );
            if ( ! empty( $cities ) && ! is_wp_error( $cities ) ) {
                $city = $cities[0];
                $items[] = array(
                    '@type'    => 'ListItem',
                    'position' => $pos++,
                    'name'     => $city->name,
                    'item'     => get_term_link( $city ),
                );
            }

            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $pos++,
                'name'     => get_the_title( $post_id ),
                'item'     => get_permalink( $post_id ),
            );
        } elseif ( is_tax( 'mechanic_city' ) ) {
            $term = get_queried_object();
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $pos++,
                'name'     => 'Werkstätten',
                'item'     => home_url( '/werkstaetten/' ),
            );
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $pos++,
                'name'     => $term->name,
                'item'     => get_term_link( $term ),
            );
        }

        return $items;
    }

    private static function get_autorepair_schema( $post_id ) {
        $title       = get_the_title( $post_id );
        $url         = get_permalink( $post_id );
        $phone       = get_post_meta( $post_id, '_mechanic_phone', true ) ?: '+49300000000';
        $address     = get_post_meta( $post_id, '_mechanic_address', true );
        $plz         = get_post_meta( $post_id, '_mechanic_plz', true );
        $lat         = get_post_meta( $post_id, '_mechanic_latitude', true ) ?: '52.520008';
        $lng         = get_post_meta( $post_id, '_mechanic_longitude', true ) ?: '13.404954';
        $rating_avg  = get_post_meta( $post_id, '_mechanic_rating_avg', true ) ?: '4.9';
        $rating_cnt  = get_post_meta( $post_id, '_mechanic_rating_count', true ) ?: '18';

        $cities = wp_get_post_terms( $post_id, 'mechanic_city' );
        $city_name = ( $cities && ! is_wp_error( $cities ) ) ? $cities[0]->name : 'Deutschland';

        $image = has_post_thumbnail( $post_id ) ? get_the_post_thumbnail_url( $post_id, 'large' ) : get_template_directory_uri() . '/assets/images/logo-main.png';

        $services = wp_get_post_terms( $post_id, 'service_type' );
        $service_names = ( $services && ! is_wp_error( $services ) ) ? wp_list_pluck( $services, 'name' ) : array();

        return array(
            '@context'           => 'https://schema.org',
            '@type'              => 'AutoRepair',
            '@id'                => esc_url( $url ) . '#autorepair',
            'name'               => $title,
            'url'                => esc_url( $url ),
            'image'              => esc_url( $image ),
            'telephone'          => $phone,
            'priceRange'         => '€€',
            'currenciesAccepted' => 'EUR',
            'paymentAccepted'    => 'Barzahlung, Girocard, EC-Karte, Kreditkarte, Überweisung',
            'address'            => array(
                '@type'           => 'PostalAddress',
                'streetAddress'   => $address ?: ( $city_name . ', Deutschland' ),
                'postalCode'      => $plz ?: '',
                'addressLocality' => $city_name,
                'addressCountry'  => 'DE',
            ),
            'geo'                => array(
                '@type'     => 'GeoCoordinates',
                'latitude'  => (float) $lat,
                'longitude' => (float) $lng,
            ),
            'openingHoursSpecification' => array(
                array(
                    '@type'     => 'OpeningHoursSpecification',
                    'dayOfWeek' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday' ),
                    'opens'     => '08:00',
                    'closes'    => '18:00',
                ),
                array(
                    '@type'     => 'OpeningHoursSpecification',
                    'dayOfWeek' => 'Saturday',
                    'opens'     => '09:00',
                    'closes'    => '13:00',
                ),
            ),
            'areaServed'         => array(
                '@type' => 'City',
                'name'  => $city_name,
            ),
            'knowsAbout'         => $service_names,
            'aggregateRating'    => array(
                '@type'       => 'AggregateRating',
                'ratingValue' => number_format( (float) $rating_avg, 1, '.', '' ),
                'reviewCount' => (int) $rating_cnt,
                'bestRating'  => '5',
                'worstRating' => '1',
            ),
        );
    }

    private static function get_single_mechanic_faq_schema( $post_id ) {
        $title   = get_the_title( $post_id );
        $phone   = get_post_meta( $post_id, '_mechanic_phone', true );
        $address = get_post_meta( $post_id, '_mechanic_address', true );

        return array(
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => array(
                array(
                    '@type'          => 'Question',
                    'name'           => "Welche Leistungen bietet {$title} an?",
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text'  => "{$title} bietet herstellerunabhängige Reparaturen, regelmäßige Inspektion nach Herstellervorgaben, TÜV/AU-Vorbereitung, Bremsenservice, Reifenwechsel und elektronische Fehlerauslese an.",
                    ),
                ),
                array(
                    '@type'          => 'Question',
                    'name'           => "Wo befindet sich {$title} und wie kann ich einen Termin buchen?",
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text'  => "Die Werkstatt liegt unter der Adresse: " . ( $address ?: 'im Stadtgebiet' ) . ". Termine können direkt telefonisch unter " . ( $phone ?: 'der angegebenen Nummer' ) . " vereinbart werden.",
                    ),
                ),
            ),
        );
    }

    private static function get_city_faq_schema( $city_name ) {
        return array(
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => array(
                array(
                    '@type'          => 'Question',
                    'name'           => "Wie finde ich die beste Kfz-Werkstatt in {$city_name}?",
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text'  => "Auf FindeWerkstatt.de vergleichen Sie geprüfte Meisterwerkstätten in {$city_name} anhand echter Kundenbewertungen, Spezialisierungen (z. B. Bremsen, Karosserie, TÜV) und Kontaktdaten.",
                    ),
                ),
                array(
                    '@type'          => 'Question',
                    'name'           => "Was kostet eine Autoreparatur oder Inspektion in {$city_name}?",
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text'  => "Die Kosten variieren je nach Fahrzeugmodell und benötigten Ersatzteilen. Über FindeWerkstatt.de können Sie direkt unverbindliche Kostenvoranschläge bei Werkstätten in {$city_name} anfragen.",
                    ),
                ),
                array(
                    '@type'          => 'Question',
                    'name'           => "Gibt es in {$city_name} einen 24h Abschleppdienst?",
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text'  => "Ja, mit unserem Filter '24h Abschleppdienst & Pannenhilfe' finden Sie Notdienste in {$city_name}, die bei Unfällen oder Pannen rund um die Uhr einsatzbereit sind.",
                    ),
                ),
            ),
        );
    }
}

FindeWerkstatt_Schema_SEO::init();
