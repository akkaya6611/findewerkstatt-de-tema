<?php
/**
 * FindeWerkstatt.de — Workshop Bulk Importer
 * Imports all parsed workshops into WordPress as 'mechanic' CPT.
 */

// Define CLI mode
if ( php_sapi_name() !== 'cli' ) {
    die( "CLI only.\n" );
}

define( 'WP_INSTALLING', false );
require_once 'C:/Users/Serkan/Local Sites/finde/app/public/wp-load.php';

echo "=== FindeWerkstatt.de Workshop Importer ===\n";

$json_path = 'c:/Users/Serkan/.gemini/antigravity/scratch/findewerkstatt-de-tema/data/workshops.json';
if ( ! file_exists( $json_path ) ) {
    die( "Error: workshops.json not found at {$json_path}\n" );
}

$workshops = json_decode( file_get_contents( $json_path ), true );
$total_count = count( $workshops );
echo "Loaded {$total_count} workshops from JSON.\n";

// Ensure primary service term exists: 'reifenservice-raederwechsel'
$primary_service = get_term_by( 'slug', 'reifenservice-raederwechsel', 'service_type' );
if ( ! $primary_service ) {
    $inserted = wp_insert_term( 'Reifenservice & Räderwechsel', 'service_type', [
        'slug' => 'reifenservice-raederwechsel',
        'description' => 'Saisonaler Radwechsel, Reifenmontage, Auswuchten, RDKS-Programmierung und fachgerechte Rädereinlagerung.',
    ] );
    if ( is_wp_error( $inserted ) ) {
        die( "Failed to create primary service term: " . $inserted->get_error_message() . "\n" );
    }
    $primary_service_id = (int) $inserted['term_id'];
} else {
    $primary_service_id = (int) $primary_service->term_id;
}
echo "Primary Service ID (reifenservice-raederwechsel): {$primary_service_id}\n";

// Map secondary service categories
$category_service_map = [
    'karosseriewerkstatt'  => 'karosserie-lackiererei',
    'fahrzeuglackiererei'  => 'karosserie-lackiererei',
    'autoglaswerkstatt'    => 'autoglas-scheibenreparatur',
    'autowerkstatt'        => 'kfz-werkstatt',
    'reifenwerkstatt'      => 'reifenservice-raederwechsel',
    'reifengeschäft'       => 'reifenservice-raederwechsel',
    'reifengeschft'       => 'reifenservice-raederwechsel',
];

// Pre-build City Map (City Name/Slug -> Term ID & Parent Bundesland ID)
$all_city_terms = get_terms( [
    'taxonomy'   => 'mechanic_city',
    'hide_empty' => false,
] );

$city_lookup = [];
foreach ( $all_city_terms as $ct ) {
    $city_lookup[ mb_strtolower( $ct->name, 'UTF-8' ) ] = $ct;
    $city_lookup[ $ct->slug ] = $ct;

    // Umlaut slug normalized
    $clean_slug = sanitize_title( str_replace(
        ['ä', 'ö', 'ü', 'ß', 'Ä', 'Ö', 'Ü'],
        ['ae', 'oe', 'ue', 'ss', 'ae', 'oe', 'ue'],
        mb_strtolower( $ct->name, 'UTF-8' )
    ) );
    $city_lookup[ $clean_slug ] = $ct;
}

// Pre-build Car Brands Map
$all_brand_terms = get_terms( [
    'taxonomy'   => 'car_brand',
    'hide_empty' => false,
] );
$brand_lookup = [];
foreach ( $all_brand_terms as $bt ) {
    $brand_lookup[ $bt->slug ] = (int) $bt->term_id;
}

// Pre-build Existing Posts lookup by Title + PLZ to avoid duplicate insertions
global $wpdb;
$existing_posts = $wpdb->get_results(
    "SELECT p.ID, p.post_title, pm.meta_value as plz
     FROM {$wpdb->posts} p
     LEFT JOIN {$wpdb->postmeta} pm ON (p.ID = pm.post_id AND pm.meta_key = '_mechanic_plz')
     WHERE p.post_type = 'mechanic' AND p.post_status = 'publish'"
);

$existing_lookup = [];
foreach ( $existing_posts as $ep ) {
    $key = mb_strtolower( trim( $ep->post_title ), 'UTF-8' ) . '|' . trim( (string) $ep->plz );
    $existing_lookup[ $key ] = (int) $ep->ID;
}
echo "Found " . count( $existing_lookup ) . " existing mechanic posts in DB.\n";

wp_defer_term_counting( true );
wp_defer_comment_counting( true );

$inserted_count = 0;
$updated_count = 0;
$skipped_count = 0;

$start_time = microtime( true );

foreach ( $workshops as $index => $w ) {
    $name = trim( $w['name'] );
    $city_name = trim( $w['city'] );
    $district_name = trim( $w['district'] ?? '' );
    $plz = trim( $w['plz'] ?? '' );
    $street = trim( $w['street'] ?? '' );
    $full_addr = trim( $w['full_address'] ?? '' );
    $phone = trim( $w['phone'] ?? '' );
    $website = trim( $w['website'] ?? '' );
    $rating = $w['rating'];
    $review_count = (int) ( $w['review_count'] ?? 0 );
    $lat = $w['lat'];
    $lng = $w['lng'];
    $maps_url = trim( $w['maps_url'] ?? '' );
    $image_url = trim( $w['image_url'] ?? '' );
    $is_real_photo = ! empty( $w['is_real_photo'] );
    $is_verified = ! empty( $w['is_verified'] );
    $is_master = ! empty( $w['is_master'] );
    $emergency_24h = ! empty( $w['emergency_24h'] );
    $brands = $w['brands'] ?? [];

    // Find city term
    $city_term = null;
    $city_key = mb_strtolower( $city_name, 'UTF-8' );
    if ( isset( $city_lookup[ $city_key ] ) ) {
        $city_term = $city_lookup[ $city_key ];
    } else {
        $clean_slug = sanitize_title( str_replace(
            ['ä', 'ö', 'ü', 'ß', 'Ä', 'Ö', 'Ü'],
            ['ae', 'oe', 'ue', 'ss', 'ae', 'oe', 'ue'],
            $city_key
        ) );
        if ( isset( $city_lookup[ $clean_slug ] ) ) {
            $city_term = $city_lookup[ $clean_slug ];
        }
    }

    if ( ! $city_term ) {
        echo "[Warn] City not found in mechanic_city: {$city_name}. Skipping workshop: {$name}\n";
        $skipped_count++;
        continue;
    }

    // Construct natural, high quality German description
    $district_phrase = $district_name ? " im Stadtteil {$district_name}" : "";
    $content = "<p><strong>" . esc_html( $name ) . "</strong> ist Ihr kompetenter Fachbetrieb für professionellen Reifenservice und Kfz-Dienstleistungen in <strong>" . esc_html( $city_name ) . "</strong>{$district_phrase}.</p>\n";
    $content .= "<p>Ob saisonaler Reifenwechsel (Sommer- und Winterräder), präzises Auswuchten, fachgerechte Montage oder Einlagerung – bei " . esc_html( $name ) . " profitieren Autofahrer von modernster Werkstatttechnik, schneller Terminvergabe und zuverlässigem Service.</p>\n";
    $content .= "<h3>Leistungsüberblick & Werkstattservice</h3>\n<ul>\n";
    $content .= "  <li>Fachgerechter Rad- und Reifenwechsel für Pkw und Transporter</li>\n";
    $content .= "  <li>Reifenmontage, Demontage und dynamisches Auswuchten</li>\n";
    $content .= "  <li>Prüfung und Programmierung von Reifendruckkontrollsystemen (RDKS)</li>\n";
    $content .= "  <li>Prüfung der Profiltiefe und des Reifenalters nach Herstellervorgaben</li>\n";
    if ( $is_master ) {
        $content .= "  <li>Zertifizierter Kfz-Meisterbetrieb mit höchstem Qualitätsanspruch</li>\n";
    }
    if ( $emergency_24h ) {
        $content .= "  <li>24h Notdienst & Soforthilfe bei Reifenpannen</li>\n";
    }
    $content .= "</ul>\n";
    $content .= "<p>Vereinbaren Sie Ihren nächsten Servicetermin direkt telefonisch oder besuchen Sie den Betrieb in " . esc_html( $street ) . ", " . esc_html( $plz . ' ' . $city_name ) . ".</p>";

    // Check duplicate
    $dup_key = mb_strtolower( $name, 'UTF-8' ) . '|' . $plz;
    $post_id = $existing_lookup[ $dup_key ] ?? null;

    if ( ! $post_id ) {
        // Insert new post
        $post_data = [
            'post_title'   => $name,
            'post_content' => $content,
            'post_status'  => 'publish',
            'post_type'    => 'mechanic',
            'post_author'  => 1,
        ];
        $post_id = wp_insert_post( $post_data );

        if ( is_wp_error( $post_id ) ) {
            echo "[Error] Failed to insert {$name}: " . $post_id->get_error_message() . "\n";
            $skipped_count++;
            continue;
        }
        $existing_lookup[ $dup_key ] = $post_id;
        $inserted_count++;
    } else {
        // Update content if existing
        wp_update_post( [
            'ID'           => $post_id,
            'post_content' => $content,
        ] );
        $updated_count++;
    }

    // Set mechanic_city terms (both city and its parent Bundesland)
    $city_terms_to_set = [ (int) $city_term->term_id ];
    if ( $city_term->parent ) {
        $city_terms_to_set[] = (int) $city_term->parent;
    }
    wp_set_object_terms( $post_id, $city_terms_to_set, 'mechanic_city', false );

    // Set service_type terms
    $service_terms_to_set = [ $primary_service_id ];
    $cat_slug = sanitize_title( $w['category'] ?? '' );
    if ( isset( $category_service_map[ $cat_slug ] ) ) {
        $sec_slug = $category_service_map[ $cat_slug ];
        $sec_term = get_term_by( 'slug', $sec_slug, 'service_type' );
        if ( $sec_term ) {
            $service_terms_to_set[] = (int) $sec_term->term_id;
        }
    }
    wp_set_object_terms( $post_id, array_unique( $service_terms_to_set ), 'service_type', false );

    // Set mechanic_district terms if district provided
    if ( $district_name ) {
        $district_slug = sanitize_title( $district_name );
        $district_term = get_term_by( 'slug', $district_slug, 'mechanic_district' );
        if ( ! $district_term ) {
            $inserted_dist = wp_insert_term( $district_name, 'mechanic_district', [
                'slug' => $district_slug,
            ] );
            if ( ! is_wp_error( $inserted_dist ) ) {
                $district_term_id = (int) $inserted_dist['term_id'];
            } else {
                $district_term_id = null;
            }
        } else {
            $district_term_id = (int) $district_term->term_id;
        }
        if ( $district_term_id ) {
            wp_set_object_terms( $post_id, [ $district_term_id ], 'mechanic_district', false );
        }
    }

    // Set car_brand terms if matched
    if ( ! empty( $brands ) ) {
        $brand_term_ids = [];
        foreach ( $brands as $b_slug ) {
            if ( isset( $brand_lookup[ $b_slug ] ) ) {
                $brand_term_ids[] = $brand_lookup[ $b_slug ];
            }
        }
        if ( ! empty( $brand_term_ids ) ) {
            wp_set_object_terms( $post_id, $brand_term_ids, 'car_brand', false );
        }
    }

    // Meta Fields
    update_post_meta( $post_id, '_mechanic_phone', $phone );
    update_post_meta( $post_id, '_mechanic_address', $street );
    update_post_meta( $post_id, '_mechanic_plz', $plz );
    update_post_meta( $post_id, '_mechanic_full_address', $full_addr );
    update_post_meta( $post_id, '_mechanic_website', $website );
    update_post_meta( $post_id, '_mechanic_rating_avg', $rating !== null ? (string) $rating : '' );
    update_post_meta( $post_id, '_mechanic_rating_count', $review_count );
    if ( $lat !== null ) update_post_meta( $post_id, '_mechanic_latitude', (string) $lat );
    if ( $lng !== null ) update_post_meta( $post_id, '_mechanic_longitude', (string) $lng );
    update_post_meta( $post_id, '_mechanic_google_maps_url', $maps_url );
    update_post_meta( $post_id, '_mechanic_google_photo_url', $image_url );
    update_post_meta( $post_id, '_mechanic_is_real_photo', $is_real_photo ? 'yes' : 'no' );
    update_post_meta( $post_id, '_mechanic_is_verified', $is_verified ? 'yes' : 'no' );
    update_post_meta( $post_id, '_mechanic_is_master', $is_master ? 'yes' : 'no' );
    update_post_meta( $post_id, '_mechanic_emergency_24h', $emergency_24h ? 'yes' : 'no' );
    update_post_meta( $post_id, '_mechanic_hours_weekday', '08:00 - 18:00 Uhr' );
    update_post_meta( $post_id, '_mechanic_hours_saturday', '09:00 - 13:00 Uhr' );
    update_post_meta( $post_id, '_mechanic_hours_sunday', 'Geschlossen' );
    update_post_meta( $post_id, '_mechanic_languages', [ 'de' ] );

    // WhatsApp if mobile
    if ( preg_match( '/\+49\s*(15|16|17)/', $phone ) ) {
        update_post_meta( $post_id, '_mechanic_whatsapp', $phone );
    }

    if ( ( $index + 1 ) % 100 === 0 || ( $index + 1 ) === $total_count ) {
        echo "Processed " . ( $index + 1 ) . " / {$total_count} workshops...\n";
    }
}

wp_defer_term_counting( false );
wp_defer_comment_counting( false );

$elapsed = round( microtime( true ) - $start_time, 2 );

echo "\n=== Import Complete ===\n";
echo "Total processed: {$total_count}\n";
echo "Newly inserted:  {$inserted_count}\n";
echo "Updated:         {$updated_count}\n";
echo "Skipped:         {$skipped_count}\n";
echo "Elapsed time:    {$elapsed} seconds\n";
echo "Total published mechanics in DB: " . wp_count_posts( 'mechanic' )->publish . "\n";
