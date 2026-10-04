<?php
require_once 'C:/Users/Serkan/Local Sites/finde/app/public/wp-load.php';

$cities = json_decode(file_get_contents('c:/Users/Serkan/.gemini/antigravity/scratch/findewerkstatt-de-tema/data/unique_80_cities.json'), true);

$matched = [];
$missing = [];

foreach ($cities as $cityName) {
    // 1. Direct name lookup
    $term = get_term_by('name', $cityName, 'mechanic_city');
    // 2. Slug lookup
    if (!$term) {
        $slug = sanitize_title($cityName);
        $term = get_term_by('slug', $slug, 'mechanic_city');
    }
    // 3. German umlaut slug lookup (e.g. muenchen, koeln, duesseldorf, luebeck, nuernberg, fuerth, wuerzburg, goettingen, saarbruecken)
    if (!$term) {
        $clean = str_replace(
            ['ä', 'ö', 'ü', 'ß', 'Ä', 'Ö', 'Ü'],
            ['ae', 'oe', 'ue', 'ss', 'ae', 'oe', 'ue'],
            mb_strtolower($cityName, 'UTF-8')
        );
        $slug = sanitize_title($clean);
        $term = get_term_by('slug', $slug, 'mechanic_city');
    }

    if ($term) {
        $matched[$cityName] = [
            'term_id' => $term->term_id,
            'name' => $term->name,
            'slug' => $term->slug,
        ];
    } else {
        $missing[] = $cityName;
    }
}

echo "Matched: " . count($matched) . " / " . count($cities) . "\n";
if (!empty($missing)) {
    echo "Missing: " . implode(', ', $missing) . "\n";
}
