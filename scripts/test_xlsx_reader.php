<?php
/**
 * Test standalone PHP XLSX parser
 */
class Simple_XLSX_Reader {
    public static function parse($file_path) {
        $zip = new ZipArchive();
        if ($zip->open($file_path) !== true) {
            return new WP_Error('zip_error', 'Konnte Excel-Datei nicht öffnen (ungültiges ZIP-Format).');
        }

        // 1. Shared Strings (falls vorhanden)
        $shared_strings = [];
        $shared_xml_content = $zip->getFromName('xl/sharedStrings.xml');
        if ($shared_xml_content !== false) {
            $xml = simplexml_load_string($shared_xml_content);
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $si) {
                    // Falls verschachtelte <r><t> Tags vorhanden sind
                    if (isset($si->r)) {
                        $str = '';
                        foreach ($si->r as $r) {
                            $str .= (string)$r->t;
                        }
                        $shared_strings[] = $str;
                    } else {
                        $shared_strings[] = (string)$si->t;
                    }
                }
            }
        }

        // 2. Sheet 1
        $sheet_xml_content = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheet_xml_content === false) {
            $zip->close();
            return new WP_Error('sheet_error', 'Arbeitsblatt sheet1.xml in der Excel-Datei nicht gefunden.');
        }

        $sheet = simplexml_load_string($sheet_xml_content);
        $zip->close();

        if (!$sheet || !isset($sheet->sheetData->row)) {
            return [];
        }

        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $row_data = [];
            $current_col_idx = 0;

            foreach ($row->c as $c) {
                $ref = (string)$c['r']; // z.B. A1, B1, M1
                // Spaltenbuchstabe zu 0-basiertem Index umwandeln
                preg_match('/^([A-Z]+)(\d+)$/', $ref, $matches);
                $col_letters = $matches[1] ?? 'A';
                
                $target_idx = 0;
                $len = strlen($col_letters);
                for ($i = 0; $i < $len; $i++) {
                    $target_idx = $target_idx * 26 + (ord($col_letters[$i]) - ord('A') + 1);
                }
                $target_idx -= 1; // 0-indexed

                // Lücken auffüllen
                while ($current_col_idx < $target_idx) {
                    $row_data[$current_col_idx] = '';
                    $current_col_idx++;
                }

                $type = (string)$c['t'];
                $val = '';

                if ($type === 's') {
                    // Shared String
                    $si_idx = (int)$c->v;
                    $val = $shared_strings[$si_idx] ?? '';
                } elseif ($type === 'inlineStr' && isset($c->is->t)) {
                    $val = (string)$c->is->t;
                } elseif (isset($c->v)) {
                    $val = (string)$c->v;
                }

                $row_data[$target_idx] = trim($val);
                $current_col_idx = $target_idx + 1;
            }

            $rows[] = $row_data;
        }

        return $rows;
    }
}

// Test parsing
$xlsx = 'C:/Users/Serkan/.gemini/antigravity/brain/a66a60b5-6687-4a17-b9ce-240284dfd061/.user_uploaded/media_1791060353110.xlsx';
$start = microtime(true);
$data = Simple_XLSX_Reader::parse($xlsx);
$time = round(microtime(true) - $start, 3);

echo "Parsed rows: " . count($data) . " in {$time}s\n";
echo "Header: " . implode(' | ', $data[0] ?? []) . "\n";
echo "Row 1:  " . implode(' | ', $data[1] ?? []) . "\n";
