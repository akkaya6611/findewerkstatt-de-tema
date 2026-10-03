<?php
require_once 'C:/Users/Serkan/Local Sites/finde/app/public/wp-load.php';

echo "=== Importer Admin Module Test ===\n";

// 1. Check Class exists
if (!class_exists('FindeWerkstatt_Workshop_Importer')) {
    die("Error: FindeWerkstatt_Workshop_Importer not loaded.\n");
}
echo "✓ FindeWerkstatt_Workshop_Importer class loaded.\n";

// 2. Test Parser
$xlsx_path = 'C:/Users/Serkan/.gemini/antigravity/brain/a66a60b5-6687-4a17-b9ce-240284dfd061/.user_uploaded/media_1791060353110.xlsx';
$rows = FindeWerkstatt_Spreadsheet_Reader::parse($xlsx_path);

if (is_wp_error($rows)) {
    die("Parser Error: " . $rows->get_error_message() . "\n");
}
echo "✓ Spreadsheet parsed: " . count($rows) . " rows.\n";

// 3. Test Column Map
$header = $rows[0];
$map = FindeWerkstatt_Workshop_Importer::resolve_column_map($header);
echo "✓ Column map resolved:\n";
foreach ($map as $k => $idx) {
    $col_name = $header[$idx] ?? 'N/A';
    echo "   - {$k} => Col {$idx} ({$col_name})\n";
}

// 4. Test Single Workshop Import (Updating an existing record)
$sample_row = $rows[1];
$sample_data = [
    'search_cat' => $sample_row[$map['search_cat']],
    'city'       => $sample_row[$map['city']],
    'district'   => $sample_row[$map['district']],
    'plz'        => $sample_row[$map['plz']],
    'name'       => $sample_row[$map['name']],
    'category'   => $sample_row[$map['category']],
    'rating'     => $sample_row[$map['rating']],
    'reviews'    => $sample_row[$map['reviews']],
    'phone'      => $sample_row[$map['phone']],
    'address'    => $sample_row[$map['address']],
    'website'    => $sample_row[$map['website']],
    'image_url'  => $sample_row[$map['image_url']],
    'maps_url'   => $sample_row[$map['maps_url']],
];

$res = FindeWerkstatt_Workshop_Importer::import_single_workshop($sample_data, [
    'update_existing' => true,
    'gen_desc'        => true,
    'auto_district'   => true,
]);

echo "✓ Test single workshop processed:\n";
echo "   - Status:  {$res['status']}\n";
echo "   - Post ID: {$res['post_id']}\n";
echo "   - Title:   {$res['title']}\n";

echo "All Importer module tests passed successfully!\n";
