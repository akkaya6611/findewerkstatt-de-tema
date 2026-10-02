<?php
/**
 * Strukturierte Daten und Metadaten aus tatsächlich vorhandenen Profilangaben.
 *
 * @package FindeWerkstatt
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FindeWerkstatt_Schema_SEO {

    public static function init() {
        add_action( 'wp_head', array( __CLASS__, 'render_seo_meta_tags' ), 1 );
        add_action( 'wp_head', array( __CLASS__, 'render_geo_meta_tags' ), 2 );
        add_action( 'wp_head', array( __CLASS__, 'render_json_ld_schemas' ), 3 );
        add_filter( 'document_title_parts', array( __CLASS__, 'filter_city_document_title' ), 20 );
    }

    public static function filter_city_document_title( $parts ) {
        list( $term ) = self::get_taxonomy_context();
        if ( $term && 'mechanic_city' === $term->taxonomy ) {
            $context = findewerkstatt_location_context( $term );
            $copy = findewerkstatt_location_copy( $context );
            $parts['title'] = $copy['title'];
            $state_in_phrase = in_array( $context['kind'], array( 'district', 'borough' ), true ) && $context['parent_name'] === $context['state_name'];
            if ( $context['state_name'] && $context['state_name'] !== $context['name'] && ! $state_in_phrase ) {
                $parts['title'] .= ', ' . $context['state_name'];
            }
        }
        return $parts;
    }

    private static function get_taxonomy_context() {
        if ( is_404() || ! is_tax() ) {
            return array( null, null );
        }
        foreach ( array( 'service_type', 'car_brand', 'mechanic_city' ) as $taxonomy ) {
            $term = FindeWerkstatt_Programmatic_SEO::get_query_term( $taxonomy );
            if ( $term ) {
                $location = 'mechanic_city' === $taxonomy ? $term : ( get_query_var( 'fw_pseo_location' ) ? FindeWerkstatt_Programmatic_SEO::get_location_term() : null );
                return array( $term, $location );
            }
        }
        return array( null, null );
    }

    public static function render_seo_meta_tags() {
        if ( is_404() ) {
            return;
        }
        list( $term, $location ) = self::get_taxonomy_context();
        $description = '';
        $canonical = '';
        $paged = max( 1, (int) get_query_var( 'paged' ) );
        if ( $term ) {
            if ( $location && 'mechanic_city' !== $term->taxonomy ) {
                $canonical = FindeWerkstatt_Programmatic_SEO::get_combination_url( $term, $location, $paged );
                $description = sprintf( findewerkstatt_location_translate( '%s: Werkstätten mit Angaben zu Leistungen, Adresse und Kontakt. Fragen Sie Termine und Kosten direkt beim Betrieb an.' ), findewerkstatt_directory_term_title( $term, $location ) );
            } else {
                $canonical = get_term_link( $term );
                if ( $paged > 1 && ! is_wp_error( $canonical ) ) {
                    $canonical = trailingslashit( $canonical ) . user_trailingslashit( 'page/' . $paged );
                }
                if ( 'mechanic_city' === $term->taxonomy ) {
                    global $wp_query;
                    $copy = findewerkstatt_location_copy( findewerkstatt_location_context( $term ), max( 0, (int) $wp_query->found_posts ) );
                    $description = $copy['meta_description'];
                } else {
                    $description = sprintf( findewerkstatt_location_translate( '%s: Finden Sie passende Werkstätten in Deutschland und vergleichen Sie Leistungen und Kontaktdaten.' ), findewerkstatt_directory_term_title( $term ) );
                }
            }
        } elseif ( is_singular( 'mechanic' ) ) {
            $post_id = get_queried_object_id();
            $description = sprintf( findewerkstatt_location_translate( '%s: Adresse, Leistungen und Kontaktdaten der Werkstatt. Fragen Sie Verfügbarkeit und Kosten direkt beim Betrieb an.' ), get_the_title( $post_id ) );
        } elseif ( is_post_type_archive( 'mechanic' ) ) {
            $canonical = get_post_type_archive_link( 'mechanic' );
            if ( $paged > 1 ) {
                $canonical = trailingslashit( $canonical ) . user_trailingslashit( 'page/' . $paged );
            }
            $description = findewerkstatt_location_translate( 'Finden Sie Kfz-Werkstätten in Deutschland nach Standort, Leistung und Automarke. Vergleichen Sie Profilangaben und kontaktieren Sie den Betrieb direkt.' );
        } elseif ( is_front_page() ) {
            $description = findewerkstatt_location_translate( 'Finden Sie eine Kfz-Werkstatt in Deutschland. Suchen Sie nach Ort, Leistung oder Automarke und nehmen Sie direkt Kontakt mit dem Betrieb auf.' );
        }
        if ( $description ) {
            echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
        }
        if ( $canonical && ! is_wp_error( $canonical ) ) {
            echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";
        }
    }

    private static function valid_coordinates( $lat, $lng ) {
        return is_numeric( $lat ) && is_numeric( $lng ) && is_finite( (float) $lat ) && is_finite( (float) $lng )
            && (float) $lat >= -90 && (float) $lat <= 90 && (float) $lng >= -180 && (float) $lng <= 180;
    }

    public static function render_geo_meta_tags() {
        if ( is_404() ) {
            return;
        }
        $lat = '';
        $lng = '';
        $city = null;
        if ( is_singular( 'mechanic' ) ) {
            $post_id = get_queried_object_id();
            if ( 'yes' === get_post_meta( $post_id, '_findewerkstatt_demo', true ) ) {
                return;
            }
            $city = findewerkstatt_get_city_term( $post_id );
            $lat = get_post_meta( $post_id, '_mechanic_latitude', true );
            $lng = get_post_meta( $post_id, '_mechanic_longitude', true );
        } else {
            list( $term, $city ) = self::get_taxonomy_context();
            // Ortsmittelpunkte benötigen eine geprüfte Quelle und sind keine Betriebsadressen.
            $geo = $city ? findewerkstatt_location_geo( $city ) : null;
            if ( $geo ) {
                $lat = $geo['lat'];
                $lng = $geo['lng'];
            }
        }
        if ( ! $city && ! self::valid_coordinates( $lat, $lng ) ) {
            return;
        }
        echo '<meta name="geo.region" content="DE">' . "\n";
        if ( $city ) {
            echo '<meta name="geo.placename" content="' . esc_attr( findewerkstatt_location_display_name( $city ) . ', ' . findewerkstatt_location_translate( 'Deutschland' ) ) . '">' . "\n";
        }
        if ( self::valid_coordinates( $lat, $lng ) ) {
            echo '<meta name="geo.position" content="' . esc_attr( number_format( (float) $lat, 6, '.', '' ) . ';' . number_format( (float) $lng, 6, '.', '' ) ) . '">' . "\n";
        }
    }

    public static function render_json_ld_schemas() {
        if ( is_404() ) {
            return;
        }
        $schemas = array();
        $breadcrumbs = self::get_breadcrumb_schema();
        if ( count( $breadcrumbs ) > 1 ) {
            $schemas[] = array( '@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $breadcrumbs );
        }
        if ( is_singular( 'mechanic' ) && 'yes' !== get_post_meta( get_queried_object_id(), '_findewerkstatt_demo', true ) ) {
            $schemas[] = self::get_autorepair_schema( get_queried_object_id() );
        }
        list( $term, $location ) = self::get_taxonomy_context();
        if ( $term && 'mechanic_city' === $term->taxonomy ) {
            global $wp_query;
            $collection = self::get_city_collection_schema( $term, $wp_query );
            if ( $collection ) {
                $schemas[] = $collection;
            }
            $entities = array();
            foreach ( self::get_city_faq_entries( $term ) as $entry ) {
                $entities[] = array( '@type' => 'Question', 'name' => $entry['question'], 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $entry['answer'] ) );
            }
            $schemas[] = array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'inLanguage' => function_exists( 'findewerkstatt_language_tag' ) ? findewerkstatt_language_tag() : 'de-DE', 'mainEntity' => $entities );
        }
        if ( $schemas ) {
            echo '<script type="application/ld+json">' . wp_json_encode(
                count( $schemas ) === 1 ? $schemas[0] : $schemas,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
            ) . '</script>' . "\n";
        }
    }

    /** Bildet ausschließlich die veröffentlichten Profile der Hauptabfrage ab. */
    private static function get_city_collection_schema( $term, $query ) {
        $url = get_term_link( $term );
        if ( is_wp_error( $url ) || ! $query ) {
            return null;
        }
        $paged = max( 1, (int) $query->get( 'paged' ) );
        if ( $paged > 1 ) {
            $url = trailingslashit( $url ) . user_trailingslashit( 'page/' . $paged );
        }
        $context = findewerkstatt_location_context( $term );
        $copy = findewerkstatt_location_copy( $context, max( 0, (int) $query->found_posts ) );
        $place = self::get_location_place_schema( $term );
        $elements = array();
        $offset = ( $paged - 1 ) * max( 1, (int) $query->get( 'posts_per_page' ) );
        foreach ( (array) $query->posts as $post ) {
            if ( ! is_object( $post ) || 'mechanic' !== $post->post_type || 'publish' !== $post->post_status || 'yes' === get_post_meta( $post->ID, '_findewerkstatt_demo', true ) ) {
                continue;
            }
            $profile_url = get_permalink( $post->ID );
            $elements[] = array(
                '@type' => 'ListItem',
                'position' => $offset + count( $elements ) + 1,
                'item' => array( '@type' => 'AutoRepair', '@id' => $profile_url . '#autorepair', 'name' => html_entity_decode( get_the_title( $post->ID ), ENT_QUOTES, 'UTF-8' ), 'url' => $profile_url ),
            );
        }
        return array(
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            '@id' => $url . '#collection',
            'url' => $url,
            'name' => $copy['title'],
            'description' => $copy['intro'],
            'inLanguage' => function_exists( 'findewerkstatt_language_tag' ) ? findewerkstatt_language_tag() : 'de-DE',
            'about' => $place,
            'mainEntity' => array(
                '@type' => 'ItemList',
                '@id' => $url . '#workshops',
                // numberOfItems bezieht sich auch bei Pagination auf die vollständige Liste.
                'numberOfItems' => max( 0, (int) $query->found_posts ),
                'itemListElement' => $elements,
            ),
        );
    }

    /** Complete existing parent hierarchy; centre coordinates belong only to the selected place. */
    private static function get_location_place_schema( $term ) {
        $chain = array();
        $seen = array();
        $current = $term;
        while ( $current && ! is_wp_error( $current ) && ! isset( $seen[ $current->term_id ] ) ) {
            $seen[ $current->term_id ] = true;
            $context = findewerkstatt_location_context( $current );
            $chain[] = array( 'term' => $current, 'context' => $context );
            $current = $context['parent'];
        }
        $place = array( '@type' => 'Country', 'name' => findewerkstatt_location_translate( 'Deutschland' ) );
        foreach ( array_reverse( $chain ) as $entry ) {
            $kind = $entry['context']['kind'];
            $node = array(
                '@type' => in_array( $kind, array( 'city', 'city_state' ), true ) ? 'City' : ( 'district' === $kind ? 'Place' : 'AdministrativeArea' ),
                'name' => $entry['context']['name'],
                'containedInPlace' => $place,
            );
            $url = get_term_link( $entry['term'] );
            if ( ! is_wp_error( $url ) ) { $node['url'] = $url; }
            if ( (int) $entry['term']->term_id === (int) $term->term_id ) {
                $geo = findewerkstatt_location_geo( $term );
                if ( $geo ) { $node['geo'] = array( '@type' => 'GeoCoordinates', 'latitude' => $geo['lat'], 'longitude' => $geo['lng'] ); }
            }
            $place = $node;
        }
        return $place;
    }

    private static function get_breadcrumb_schema() {
        $entries = array( array( 'name' => findewerkstatt_location_translate( 'Startseite' ), 'item' => home_url( '/' ) ) );
        list( $term, $location ) = self::get_taxonomy_context();
        if ( is_singular( 'mechanic' ) || $term || is_post_type_archive( 'mechanic' ) ) {
            $entries[] = array( 'name' => findewerkstatt_location_translate( 'Werkstätten' ), 'item' => get_post_type_archive_link( 'mechanic' ) );
        }
        if ( is_singular( 'mechanic' ) ) {
            $city = findewerkstatt_get_city_term( get_queried_object_id() );
            if ( $city ) {
                $entries[] = array( 'name' => findewerkstatt_location_display_name( $city ), 'item' => get_term_link( $city ) );
            }
            $entries[] = array( 'name' => html_entity_decode( get_the_title( get_queried_object_id() ), ENT_QUOTES, 'UTF-8' ), 'item' => get_permalink( get_queried_object_id() ) );
        } elseif ( $term ) {
            if ( 'mechanic_city' === $term->taxonomy ) {
                foreach ( array_reverse( get_ancestors( $term->term_id, 'mechanic_city', 'taxonomy' ) ) as $ancestor_id ) {
                    $ancestor = get_term( $ancestor_id, 'mechanic_city' );
                    if ( $ancestor && ! is_wp_error( $ancestor ) ) {
                        $entries[] = array( 'name' => findewerkstatt_location_display_name( $ancestor ), 'item' => get_term_link( $ancestor ) );
                    }
                }
            }
            $entries[] = array( 'name' => 'mechanic_city' === $term->taxonomy ? findewerkstatt_location_display_name( $term ) : ( 'service_type' === $term->taxonomy ? findewerkstatt_location_translate( $term->name ) : $term->name ), 'item' => get_term_link( $term ) );
            if ( $location && 'mechanic_city' !== $term->taxonomy ) {
                $entries[] = array( 'name' => findewerkstatt_location_display_name( $location ), 'item' => FindeWerkstatt_Programmatic_SEO::get_combination_url( $term, $location ) );
            }
        }
        $items = array();
        foreach ( $entries as $entry ) {
            if ( ! is_wp_error( $entry['item'] ) && $entry['item'] ) {
                $items[] = array( '@type' => 'ListItem', 'position' => count( $items ) + 1, 'name' => $entry['name'], 'item' => $entry['item'] );
            }
        }
        return $items;
    }

    private static function get_autorepair_schema( $post_id ) {
        $url = get_permalink( $post_id );
        $schema = array( '@context' => 'https://schema.org', '@type' => 'AutoRepair', '@id' => $url . '#autorepair', 'name' => html_entity_decode( get_the_title( $post_id ), ENT_QUOTES, 'UTF-8' ), 'url' => $url );
        if ( has_post_thumbnail( $post_id ) ) {
            $schema['image'] = get_the_post_thumbnail_url( $post_id, 'large' );
        }
        $phone = findewerkstatt_normalize_phone( get_post_meta( $post_id, '_mechanic_phone', true ) );
        if ( $phone ) {
            $schema['telephone'] = $phone;
        }
        $address = get_post_meta( $post_id, '_mechanic_address', true );
        $plz = get_post_meta( $post_id, '_mechanic_plz', true );
        $city = findewerkstatt_get_city_term( $post_id );
        if ( $address || $plz || $city ) {
            $schema['address'] = array( '@type' => 'PostalAddress', 'addressCountry' => 'DE' );
            if ( $address ) { $schema['address']['streetAddress'] = $address; }
            if ( $plz ) { $schema['address']['postalCode'] = $plz; }
            if ( $city ) {
                $context = findewerkstatt_location_context( $city );
                if ( $context['state_name'] ) { $schema['address']['addressRegion'] = $context['state_name']; }
                if ( in_array( $context['kind'], array( 'city', 'city_state' ), true ) ) {
                    $schema['address']['addressLocality'] = $context['name'];
                } elseif ( in_array( $context['kind'], array( 'district', 'borough' ), true ) && $context['parent_name'] ) {
                    $schema['address']['addressLocality'] = $context['parent_name'];
                }
            }
        }
        $lat = get_post_meta( $post_id, '_mechanic_latitude', true );
        $lng = get_post_meta( $post_id, '_mechanic_longitude', true );
        if ( self::valid_coordinates( $lat, $lng ) ) {
            $schema['geo'] = array( '@type' => 'GeoCoordinates', 'latitude' => (float) $lat, 'longitude' => (float) $lng );
        }
        $hours = self::get_opening_hours_schema( $post_id );
        if ( $hours ) {
            $schema['openingHoursSpecification'] = $hours;
        }
        $services = wp_get_post_terms( $post_id, 'service_type' );
        if ( $services && ! is_wp_error( $services ) ) {
            $schema['knowsAbout'] = array_map( 'findewerkstatt_location_translate', wp_list_pluck( $services, 'name' ) );
        }
        $rating = findewerkstatt_get_rating( $post_id );
        if ( null !== $rating['rating'] && $rating['count'] > 0 ) {
            $schema['aggregateRating'] = array( '@type' => 'AggregateRating', 'ratingValue' => $rating['rating'], 'reviewCount' => $rating['count'], 'bestRating' => 5, 'worstRating' => 0 );
        }
        return $schema;
    }

    private static function get_opening_hours_schema( $post_id ) {
        $groups = array(
            '_mechanic_hours_weekday' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday' ),
            '_mechanic_hours_saturday' => array( 'Saturday' ),
            '_mechanic_hours_sunday' => array( 'Sunday' ),
        );
        $hours = array();
        foreach ( $groups as $key => $days ) {
            $ranges = findewerkstatt_parse_hours( get_post_meta( $post_id, $key, true ) );
            foreach ( (array) $ranges as $range ) {
                $hours[] = array(
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => $days,
                    'opens' => sprintf( '%02d:%02d', intdiv( $range[0], 60 ), $range[0] % 60 ),
                    'closes' => 1440 === $range[1] ? '23:59' : sprintf( '%02d:%02d', intdiv( $range[1], 60 ), $range[1] % 60 ),
                );
            }
        }
        return $hours;
    }

    /** Gemeinsame Quelle für die sichtbaren Fragen und das FAQ-Schema. */
    public static function get_city_faq_entries( $location ) {
        global $wp_query;
        $has_term = $location instanceof WP_Term;
        $context = $has_term
            ? findewerkstatt_location_context( $location )
            : array( 'kind' => 'city', 'name' => (string) $location );
        $copy = findewerkstatt_location_copy( $context, $has_term ? max( 0, (int) ( $wp_query->found_posts ?? 0 ) ) : 0 );
        if ( ! $has_term ) {
            // A legacy name alone cannot establish the published count for that place.
            $copy['faqs'][0]['answer'] = findewerkstatt_location_translate( 'Vergleichen Sie die Angaben in den veröffentlichten Werkstattprofilen. Prüfen Sie Leistungen, Adresse und Kontaktdaten und fragen Sie direkt beim Betrieb nach der Eignung für Ihr Fahrzeug.' );
        }
        return $copy['faqs'];
    }
}

FindeWerkstatt_Schema_SEO::init();

