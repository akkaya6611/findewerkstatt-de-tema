<?php
require_once 'C:/Users/Serkan/Local Sites/finde/app/public/wp-load.php';

echo "WP Loaded successfully!\n";
echo "Post count: " . wp_count_posts('mechanic')->publish . "\n";

$services = get_terms([
    'taxonomy' => 'service_type',
    'hide_empty' => false,
]);

echo "Service types:\n";
foreach ($services as $s) {
    echo " - [{$s->term_id}] {$s->slug} => {$s->name}\n";
}

$brands = get_terms([
    'taxonomy' => 'car_brand',
    'hide_empty' => false,
]);

echo "Brands count: " . count($brands) . "\n";

// Test city lookup for Berlin, München, Köln, Frankfurt am Main
$test_cities = ['Berlin', 'München', 'Köln', 'Frankfurt am Main', 'Hamburg'];
foreach ($test_cities as $c) {
    $term = get_term_by('name', $c, 'mechanic_city');
    if ($term) {
        $parent = $term->parent ? get_term($term->parent, 'mechanic_city') : null;
        $parent_name = $parent ? $parent->name : 'None';
        echo "City found: {$term->name} (slug: {$term->slug}, ID: {$term->term_id}, Parent: {$parent_name})\n";
    } else {
        echo "City NOT found: {$c}\n";
    }
}
