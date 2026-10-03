<?php
require_once 'C:/Users/Serkan/Local Sites/finde/app/public/wp-load.php';

// Read all unique cities from Excel via python output or direct list
$json_data = file_get_contents('c:/Users/Serkan/.gemini/antigravity/scratch/findewerkstatt-de-tema/data/workshops.json');
if (!$json_data) {
    echo "workshops.json not created yet.\n";
    exit;
}
$workshops = json_decode($json_data, true);
$cities = [];
foreach ($workshops as $w) {
    $c = $w['city'];
    if (!isset($cities[$c])) {
        $cities[$c] = 0;
    }
    $cities[$c]++;
}

echo "Total unique cities in data: " . count($cities) . "\n";
$missing = [];
$matched = [];

foreach ($cities as $cityName => $count) {
    // 1. Direct name match
    $term = get_term_by('name', $cityName, 'mechanic_city');
    // 2. Slug match
    if (!$term) {
        $slug = sanitize_title($cityName);
        $term = get_term_by('slug', $slug, 'mechanic_city');
    }
    if ($term) {
        $matched[$cityName] = $term->term_id;
    } else {
        $missing[] = $cityName;
    }
}

echo "Matched cities: " . count($matched) . "\n";
echo "Missing cities: " . count($missing) . "\n";
if (!empty($missing)) {
    echo "Missing list: " . implode(', ', $missing) . "\n";
}
