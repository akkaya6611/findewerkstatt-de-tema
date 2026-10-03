<?php
require_once 'C:/Users/Serkan/Local Sites/finde/app/public/wp-load.php';

echo "=== Verification Results ===\n";
echo "Total published mechanics: " . wp_count_posts('mechanic')->publish . "\n";

// Update taxonomy term counts if needed
wp_update_term_count_now(get_terms(['taxonomy' => 'service_type', 'fields' => 'ids', 'hide_empty' => false]), 'service_type');
wp_update_term_count_now(get_terms(['taxonomy' => 'mechanic_city', 'fields' => 'ids', 'hide_empty' => false]), 'mechanic_city');

$service = get_term_by('slug', 'reifenservice-raederwechsel', 'service_type');
echo "Service 'reifenservice-raederwechsel' count: " . ($service ? $service->count : 'N/A') . "\n";

$cities_to_check = ['berlin', 'muenchen', 'koeln', 'frankfurt-am-main', 'hamburg', 'stuttgart', 'dortmund', 'leipzig'];
foreach ($cities_to_check as $cslug) {
    $ct = get_term_by('slug', $cslug, 'mechanic_city');
    echo "City '{$cslug}' count: " . ($ct ? $ct->count : 'N/A') . "\n";
}

$sample = get_posts([
    'post_type' => 'mechanic',
    'posts_per_page' => 1,
    'meta_key' => '_mechanic_is_real_photo',
    'meta_value' => 'yes',
]);

if (!empty($sample)) {
    $p = $sample[0];
    echo "\nSample Workshop with Real Photo:\n";
    echo "Title:     " . $p->post_title . " (ID: {$p->ID})\n";
    echo "Link:      " . get_permalink($p->ID) . "\n";
    echo "Phone:     " . get_post_meta($p->ID, '_mechanic_phone', true) . "\n";
    echo "Street:    " . get_post_meta($p->ID, '_mechanic_address', true) . "\n";
    echo "PLZ:       " . get_post_meta($p->ID, '_mechanic_plz', true) . "\n";
    echo "Rating:    " . get_post_meta($p->ID, '_mechanic_rating_avg', true) . " (" . get_post_meta($p->ID, '_mechanic_rating_count', true) . " reviews)\n";
    echo "Photo:     " . substr(get_post_meta($p->ID, '_mechanic_google_photo_url', true), 0, 80) . "...\n";
    echo "Verified:  " . get_post_meta($p->ID, '_mechanic_is_verified', true) . "\n";
    echo "Meister:   " . get_post_meta($p->ID, '_mechanic_is_master', true) . "\n";
    echo "24h:       " . get_post_meta($p->ID, '_mechanic_emergency_24h', true) . "\n";

    $city_term = findewerkstatt_get_city_term($p->ID);
    echo "City Term: " . ($city_term ? $city_term->name : 'None') . "\n";
}
