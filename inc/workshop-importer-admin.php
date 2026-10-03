<?php
/**
 * FindeWerkstatt.de — Admin Excel & CSV Workshop Importer
 * 
 * Allows site administrators to upload .xlsx and .csv files formatted with
 * German workshop listings and automatically import them into WordPress as
 * 'mechanic' custom post type with all taxonomies, coordinates, ratings,
 * badges, photos, and localized content.
 * 
 * @package FindeWerkstatt
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FindeWerkstatt_Spreadsheet_Reader {

    /**
     * Parse an uploaded file (.xlsx or .csv) into an array of rows.
     *
     * @param string $file_path Absolute path to the file.
     * @param string $original_name Original file name for extension checking.
     * @return array|WP_Error Array of rows or WP_Error on failure.
     */
    public static function parse( $file_path, $original_name = '' ) {
        $ext = strtolower( pathinfo( $original_name ?: $file_path, PATHINFO_EXTENSION ) );

        if ( 'xlsx' === $ext ) {
            return self::parse_xlsx( $file_path );
        } elseif ( in_array( $ext, array( 'csv', 'txt' ), true ) ) {
            return self::parse_csv( $file_path );
        } else {
            return new WP_Error( 'invalid_format', __( 'Ungültiges Dateiformat. Bitte laden Sie eine .xlsx oder .csv Datei hoch.', 'findewerkstatt' ) );
        }
    }

    /**
     * Pure-PHP XLSX parser using built-in ZipArchive and SimpleXML.
     */
    public static function parse_xlsx( $file_path ) {
        if ( ! class_exists( 'ZipArchive' ) ) {
            return new WP_Error( 'zip_missing', __( 'PHP ZipArchive-Erweiterung ist auf diesem Server nicht verfügbar.', 'findewerkstatt' ) );
        }

        $zip = new ZipArchive();
        if ( true !== $zip->open( $file_path ) ) {
            return new WP_Error( 'zip_open_failed', __( 'Die Excel-Datei konnte nicht geöffnet werden (beschädigt oder ungültig).', 'findewerkstatt' ) );
        }

        // 1. Shared Strings (xl/sharedStrings.xml)
        $shared_strings = array();
        $shared_xml = $zip->getFromName( 'xl/sharedStrings.xml' );
        if ( false !== $shared_xml ) {
            $s_xml = @simplexml_load_string( $shared_xml );
            if ( $s_xml && isset( $s_xml->si ) ) {
                foreach ( $s_xml->si as $si ) {
                    if ( isset( $si->r ) ) {
                        $text = '';
                        foreach ( $si->r as $r ) {
                            $text .= (string) $r->t;
                        }
                        $shared_strings[] = $text;
                    } else {
                        $shared_strings[] = (string) $si->t;
                    }
                }
            }
        }

        // 2. Sheet 1 (xl/worksheets/sheet1.xml)
        $sheet_xml = $zip->getFromName( 'xl/worksheets/sheet1.xml' );
        if ( false === $sheet_xml ) {
            $zip->close();
            return new WP_Error( 'sheet_missing', __( 'Das Arbeitsblatt sheet1.xml wurde in der Excel-Datei nicht gefunden.', 'findewerkstatt' ) );
        }

        $sheet = @simplexml_load_string( $sheet_xml );
        $zip->close();

        if ( ! $sheet || ! isset( $sheet->sheetData->row ) ) {
            return array();
        }

        $rows = array();
        foreach ( $sheet->sheetData->row as $row ) {
            $row_data = array();
            $curr_col = 0;

            foreach ( $row->c as $c ) {
                $ref = (string) $c['r']; // e.g. A1, B2, M10
                preg_match( '/^([A-Z]+)(\d+)$/', $ref, $matches );
                $col_letters = $matches[1] ?? 'A';

                // Convert column letters (A, B, ..., AA) to 0-based index
                $target_idx = 0;
                $len = strlen( $col_letters );
                for ( $i = 0; $i < $len; $i++ ) {
                    $target_idx = $target_idx * 26 + ( ord( $col_letters[ $i ] ) - ord( 'A' ) + 1 );
                }
                $target_idx -= 1;

                while ( $curr_col < $target_idx ) {
                    $row_data[ $curr_col ] = '';
                    $curr_col++;
                }

                $type = (string) $c['t'];
                $val = '';

                if ( 's' === $type ) {
                    $idx = (int) $c->v;
                    $val = $shared_strings[ $idx ] ?? '';
                } elseif ( 'inlineStr' === $type && isset( $c->is->t ) ) {
                    $val = (string) $c->is->t;
                } elseif ( isset( $c->v ) ) {
                    $val = (string) $c->v;
                }

                $row_data[ $target_idx ] = trim( $val );
                $curr_col = $target_idx + 1;
            }

            $rows[] = $row_data;
        }

        return $rows;
    }

    /**
     * CSV parser with automatic delimiter detection and UTF-8 handling.
     */
    public static function parse_csv( $file_path ) {
        $content = file_get_contents( $file_path );
        if ( false === $content ) {
            return new WP_Error( 'read_failed', __( 'Die CSV-Datei konnte nicht gelesen werden.', 'findewerkstatt' ) );
        }

        // Remove UTF-8 BOM
        if ( str_starts_with( $content, "\xEF\xBB\xBF" ) ) {
            $content = substr( $content, 3 );
        }

        // Detect delimiter: semicolon or comma or tab
        $first_line = strtok( $content, "\r\n" );
        $delimiters = array( ';', ',', "\t" );
        $best_delim = ',';
        $max_count = 0;
        foreach ( $delimiters as $d ) {
            $cnt = substr_count( $first_line, $d );
            if ( $cnt > $max_count ) {
                $max_count = $cnt;
                $best_delim = $d;
            }
        }

        $stream = fopen( 'php://temp', 'r+' );
        fwrite( $stream, $content );
        rewind( $stream );

        $rows = array();
        while ( ( $data = fgetcsv( $stream, 0, $best_delim ) ) !== false ) {
            $rows[] = array_map( 'trim', $data );
        }
        fclose( $stream );

        return $rows;
    }
}

class FindeWerkstatt_Workshop_Importer {

    const OPTION_IMPORT_PREFIX = 'fw_import_job_';

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'register_admin_menu' ) );
        add_action( 'wp_ajax_fw_import_upload', array( __CLASS__, 'ajax_upload_file' ) );
        add_action( 'wp_ajax_fw_import_process_batch', array( __CLASS__, 'ajax_process_batch' ) );
        add_action( 'wp_ajax_fw_import_cancel', array( __CLASS__, 'ajax_cancel_import' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
    }

    public static function register_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=mechanic',
            __( 'Excel / CSV Import', 'findewerkstatt' ),
            __( 'Excel / CSV Import', 'findewerkstatt' ),
            'manage_options',
            'findewerkstatt-import',
            array( __CLASS__, 'render_admin_page' )
        );
    }

    public static function enqueue_admin_assets( $hook ) {
        if ( 'mechanic_page_findewerkstatt-import' !== $hook ) {
            return;
        }

        wp_enqueue_style( 'findewerkstatt-importer-admin', false );
        wp_add_inline_style( 'findewerkstatt-importer-admin', '
            .fw-import-wrap { max-width: 1000px; margin: 20px 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; }
            .fw-import-card { background: #fff; border: 1px solid #cbd5e1; border-radius: 12px; padding: 28px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); margin-bottom: 24px; }
            .fw-import-header h1 { margin: 0 0 8px 0; font-size: 26px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 10px; }
            .fw-import-badge-pill { background: #fee2e2; color: #dc2626; font-size: 13px; font-weight: 600; padding: 3px 10px; border-radius: 999px; }
            .fw-import-card p.lead { font-size: 15px; color: #475569; margin: 0 0 20px 0; line-height: 1.5; }
            
            .fw-upload-dropzone { border: 2px dashed #94a3b8; border-radius: 10px; padding: 40px 20px; text-align: center; background: #f8fafc; transition: all .2s ease; cursor: pointer; position: relative; }
            .fw-upload-dropzone:hover, .fw-upload-dropzone.dragover { border-color: #fb6006; background: #fff7ed; }
            .fw-upload-icon { font-size: 48px; margin-bottom: 12px; color: #fb6006; display: block; }
            .fw-upload-title { font-size: 17px; font-weight: 600; color: #1e293b; margin-bottom: 6px; }
            .fw-upload-sub { font-size: 13px; color: #64748b; }
            .fw-upload-dropzone input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }

            .fw-selected-file { display: none; margin-top: 15px; padding: 12px 16px; background: #e0f2fe; border: 1px solid #7dd3fc; border-radius: 8px; font-size: 14px; color: #0369a1; font-weight: 600; align-items: center; justify-content: space-between; }
            .fw-import-options { margin: 24px 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0; }
            .fw-import-options label { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 500; color: #334155; cursor: pointer; }

            .fw-btn-import { background: #fb6006; color: #fff; border: none; padding: 12px 28px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: background .15s; }
            .fw-btn-import:hover { background: #ea580c; color: #fff; }
            .fw-btn-import:disabled { opacity: 0.5; cursor: not-allowed; }

            .fw-progress-wrap { display: none; margin-top: 24px; padding-top: 24px; border-top: 1px solid #e2e8f0; }
            .fw-progress-bar-bg { height: 14px; background: #e2e8f0; border-radius: 999px; overflow: hidden; margin-bottom: 12px; }
            .fw-progress-bar-fill { height: 100%; width: 0%; background: linear-gradient(90deg, #fb6006, #f97316); border-radius: 999px; transition: width .2s ease; }
            .fw-progress-stats { display: flex; flex-wrap: wrap; gap: 16px; font-size: 14px; font-weight: 600; color: #1e293b; margin-bottom: 16px; }
            .fw-stat-badge { padding: 4px 12px; border-radius: 6px; font-size: 13px; }
            .fw-stat-inserted { background: #dcfce7; color: #15803d; }
            .fw-stat-updated { background: #dbeafe; color: #1d4ed8; }
            .fw-stat-skipped { background: #fef3c7; color: #b45309; }
            .fw-stat-errors { background: #fee2e2; color: #b91c1c; }

            .fw-import-log { max-height: 200px; overflow-y: auto; background: #0f172a; color: #f1f5f9; padding: 12px 16px; border-radius: 8px; font-family: Consolas, monospace; font-size: 12px; line-height: 1.6; }
            .fw-log-line { border-bottom: 1px solid #1e293b; padding: 2px 0; }
            .fw-log-success { color: #4ade80; }
            .fw-log-info { color: #38bdf8; }
            .fw-log-warn { color: #facc15; }
            .fw-log-err { color: #f87171; }

            .fw-template-table { width: 100%; border-collapse: collapse; margin-top: 14px; font-size: 13px; }
            .fw-template-table th, .fw-template-table td { border: 1px solid #cbd5e1; padding: 8px 12px; text-align: left; }
            .fw-template-table th { background: #f1f5f9; font-weight: 600; color: #334155; }
            .fw-template-table code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 12px; }
        ' );
    }

    public static function render_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Sie haben keine Berechtigung für diesen Bereich.', 'findewerkstatt' ) );
        }

        $total_mechanics = wp_count_posts( 'mechanic' )->publish;
        ?>
        <div class="wrap fw-import-wrap">
            <div class="fw-import-header">
                <h1>
                    <span>📦 <?php esc_html_e( 'Werkstatt-Datenimport (Excel / CSV)', 'findewerkstatt' ); ?></span>
                    <span class="fw-import-badge-pill"><?php echo esc_html( sprintf( __( '%d Werkstätten in der Datenbank', 'findewerkstatt' ), $total_mechanics ) ); ?></span>
                </h1>
            </div>

            <div class="fw-import-card">
                <p class="lead">
                    <?php esc_html_e( 'Laden Sie Ihre Excel- (.xlsx) oder CSV-Datei mit Werkstatt-Einträgen hoch. Das System erkennt automatisch Städte, Bundesländer, Kategorien, Adressen, Google Maps-Koordinaten, Bewertungen und Werkstatt-Fotos.', 'findewerkstatt' ); ?>
                </p>

                <div class="fw-upload-dropzone" id="fw-dropzone">
                    <span class="fw-upload-icon">📁</span>
                    <div class="fw-upload-title"><?php esc_html_e( 'Excel- (.xlsx) oder CSV-Datei hier ablegen', 'findewerkstatt' ); ?></div>
                    <div class="fw-upload-sub"><?php esc_html_e( 'oder klicken, um eine Datei von Ihrem Computer auszuwählen (Max. 32 MB)', 'findewerkstatt' ); ?></div>
                    <input type="file" id="fw-file-input" accept=".xlsx, .csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, text/csv">
                </div>

                <div class="fw-selected-file" id="fw-selected-file">
                    <span id="fw-selected-filename">📄 datei.xlsx</span>
                    <button type="button" class="button-link" id="fw-remove-file" style="color:#ef4444;"><?php esc_html_e( 'Entfernen', 'findewerkstatt' ); ?></button>
                </div>

                <div class="fw-import-options">
                    <label>
                        <input type="checkbox" id="fw-opt-update" checked>
                        <span><?php esc_html_e( 'Bestehende Werkstätten aktualisieren (Duplikate zusammenführen)', 'findewerkstatt' ); ?></span>
                    </label>
                    <label>
                        <input type="checkbox" id="fw-opt-desc" checked>
                        <span><?php esc_html_e( 'SEO-optimierte deutsche Beschreibung generieren', 'findewerkstatt' ); ?></span>
                    </label>
                    <label>
                        <input type="checkbox" id="fw-opt-district" checked>
                        <span><?php esc_html_e( 'Stadtteile / Bezirke automatisch anlegen', 'findewerkstatt' ); ?></span>
                    </label>
                </div>

                <button type="button" class="fw-btn-import" id="fw-start-btn" disabled>
                    <span>🚀 <?php esc_html_e( 'Import starten', 'findewerkstatt' ); ?></span>
                </button>

                <!-- Live Progress UI -->
                <div class="fw-progress-wrap" id="fw-progress-wrap">
                    <div class="fw-progress-stats">
                        <span id="fw-status-text"><?php esc_html_e( 'Wird vorbereitet...', 'findewerkstatt' ); ?></span>
                        <span class="fw-stat-badge fw-stat-inserted" id="fw-badge-inserted">Neu: 0</span>
                        <span class="fw-stat-badge fw-stat-updated" id="fw-badge-updated">Aktualisiert: 0</span>
                        <span class="fw-stat-badge fw-stat-skipped" id="fw-badge-skipped">Übersprungen: 0</span>
                        <span class="fw-stat-badge fw-stat-errors" id="fw-badge-errors">Fehler: 0</span>
                    </div>

                    <div class="fw-progress-bar-bg">
                        <div class="fw-progress-bar-fill" id="fw-progress-bar-fill"></div>
                    </div>

                    <div class="fw-import-log" id="fw-import-log"></div>

                    <div style="margin-top:16px; display:flex; gap:12px;">
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=mechanic' ) ); ?>" class="button button-primary" id="fw-btn-view-all" style="display:none;">
                            <?php esc_html_e( 'Alle Werkstätten ansehen →', 'findewerkstatt' ); ?>
                        </a>
                        <button type="button" class="button" id="fw-btn-reset" style="display:none;" onclick="location.reload();">
                            <?php esc_html_e( 'Weiteren Import durchführen', 'findewerkstatt' ); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Format Reference Card -->
            <div class="fw-import-card">
                <h2><?php esc_html_e( 'Unterstützte Spaltenstruktur (Excel / CSV)', 'findewerkstatt' ); ?></h2>
                <p><?php esc_html_e( 'Ihre Datei sollte die folgenden 13 Spalten in dieser Reihenfolge oder mit den entsprechenden Spaltenüberschriften enthalten:', 'findewerkstatt' ); ?></p>
                <div style="overflow-x:auto;">
                    <table class="fw-template-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Spaltenname</th>
                                <th>Erklärung & Beispiel</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>1</td><td><code>Arama Kategorisi</code></td><td>Dienstleistungskategorie (z.B. <em>Reifenservice & Räderwechsel</em>)</td></tr>
                            <tr><td>2</td><td><code>Şehir / Eyalet</code></td><td>Deutsche Großstadt oder Bundesland (z.B. <em>Berlin, München, Köln</em>)</td></tr>
                            <tr><td>3</td><td><code>İlçe / Bölge</code></td><td>Stadtteil / Bezirk (z.B. <em>Spandau, Altona, Mitte</em>)</td></tr>
                            <tr><td>4</td><td><code>Posta Kodu (PLZ)</code></td><td>5-stellige deutsche Postleitzahl (z.B. <em>10115, 01067</em>)</td></tr>
                            <tr><td>5</td><td><code>İşletme İsmi</code></td><td>Name des Betriebs / Kfz-Werkstatt</td></tr>
                            <tr><td>6</td><td><code>Kategori</code></td><td>Gewerbekategorie (z.B. <em>Autowerkstatt, Reifenservice</em>)</td></tr>
                            <tr><td>7</td><td><code>Puan</code></td><td>Google-Bewertung (z.B. <em>4,8</em>)</td></tr>
                            <tr><td>8</td><td><code>Değerlendirme Sayısı</code></td><td>Anzahl Bewertungen (z.B. <em>150 Berichte</em>)</td></tr>
                            <tr><td>9</td><td><code>Telefon</code></td><td>Telefonnummer (z.B. <em>+49 30 1234567</em>)</td></tr>
                            <tr><td>10</td><td><code>Adres</code></td><td>Straße und Hausnummer (z.B. <em>Hauptstraße 12</em>)</td></tr>
                            <tr><td>11</td><td><code>Web Sitesi</code></td><td>Offizielle Website-URL (z.B. <em>https://werkstatt.de</em>)</td></tr>
                            <tr><td>12</td><td><code>Görsel URL</code></td><td>Google Maps Foto-URL</td></tr>
                            <tr><td>13</td><td><code>Google Maps URL</code></td><td>Google Maps Link mit GPS-Koordinaten (<code>!3d...!4d...</code>)</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
        (function() {
            var fileInput = document.getElementById('fw-file-input');
            var dropzone = document.getElementById('fw-dropzone');
            var selectedFileDiv = document.getElementById('fw-selected-file');
            var filenameSpan = document.getElementById('fw-selected-filename');
            var removeBtn = document.getElementById('fw-remove-file');
            var startBtn = document.getElementById('fw-start-btn');
            
            var progressWrap = document.getElementById('fw-progress-wrap');
            var progressBar = document.getElementById('fw-progress-bar-fill');
            var statusText = document.getElementById('fw-status-text');
            var badgeInserted = document.getElementById('fw-badge-inserted');
            var badgeUpdated = document.getElementById('fw-badge-updated');
            var badgeSkipped = document.getElementById('fw-badge-skipped');
            var badgeErrors = document.getElementById('fw-badge-errors');
            var logBox = document.getElementById('fw-import-log');
            var btnViewAll = document.getElementById('fw-btn-view-all');
            var btnReset = document.getElementById('fw-btn-reset');

            var currentFile = null;
            var isRunning = false;
            var totalCount = 0;
            var processedCount = 0;
            var totalInserted = 0;
            var totalUpdated = 0;
            var totalSkipped = 0;
            var totalErrors = 0;
            var batchSize = 35; // optimal for PHP execution time
            var importToken = '';

            function log(msg, type) {
                var el = document.createElement('div');
                el.className = 'fw-log-line fw-log-' + (type || 'info');
                el.textContent = '[' + new Date().toLocaleTimeString() + '] ' + msg;
                logBox.appendChild(el);
                logBox.scrollTop = logBox.scrollHeight;
            }

            fileInput.addEventListener('change', function(e) {
                if (fileInput.files && fileInput.files[0]) {
                    setFile(fileInput.files[0]);
                }
            });

            ['dragenter', 'dragover'].forEach(function(evt) {
                dropzone.addEventListener(evt, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('dragover');
                });
            });

            ['dragleave', 'drop'].forEach(function(evt) {
                dropzone.addEventListener(evt, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('dragover');
                });
            });

            dropzone.addEventListener('drop', function(e) {
                if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
                    setFile(e.dataTransfer.files[0]);
                }
            });

            removeBtn.addEventListener('click', function() {
                currentFile = null;
                fileInput.value = '';
                selectedFileDiv.style.display = 'none';
                dropzone.style.display = 'block';
                startBtn.disabled = true;
            });

            function setFile(file) {
                var ext = file.name.split('.').pop().toLowerCase();
                if (ext !== 'xlsx' && ext !== 'csv') {
                    alert('Lütfen geçerli bir .xlsx veya .csv dosyası seçin.');
                    return;
                }
                currentFile = file;
                filenameSpan.textContent = '📄 ' + file.name + ' (' + Math.round(file.size / 1024) + ' KB)';
                dropzone.style.display = 'none';
                selectedFileDiv.style.display = 'flex';
                startBtn.disabled = false;
            }

            startBtn.addEventListener('click', function() {
                if (!currentFile || isRunning) return;
                isRunning = true;
                startBtn.disabled = true;
                progressWrap.style.display = 'block';
                log('Datei wird hochgeladen und analysiert...', 'info');

                var formData = new FormData();
                formData.append('action', 'fw_import_upload');
                formData.append('import_file', currentFile);
                formData.append('nonce', '<?php echo esc_js( wp_create_nonce( 'fw_import_action' ) ); ?>');

                fetch(ajaxurl, {
                    method: 'POST',
                    body: formData
                })
                .then(function(res) { return res.json(); })
                .then(function(response) {
                    if (!response.success) {
                        log('Fehler beim Hochladen: ' + (response.data || 'Unbekannter Fehler'), 'err');
                        statusText.textContent = 'Fehler beim Analysieren der Datei.';
                        isRunning = false;
                        return;
                    }

                    importToken = response.data.token;
                    totalCount = response.data.total_rows;
                    log('Datei erfolgreich analysiert! ' + totalCount + ' Werkstatt-Einträge gefunden.', 'success');
                    statusText.textContent = 'Wird importiert: 0 / ' + totalCount + ' (%0)';

                    processNextBatch(0);
                })
                .catch(function(err) {
                    log('Netzwerkfehler: ' + err.message, 'err');
                    isRunning = false;
                });
            });

            function processNextBatch(offset) {
                if (offset >= totalCount) {
                    finishImport();
                    return;
                }

                var updateExisting = document.getElementById('fw-opt-update').checked ? 1 : 0;
                var genDesc = document.getElementById('fw-opt-desc').checked ? 1 : 0;
                var autoDistrict = document.getElementById('fw-opt-district').checked ? 1 : 0;

                var formData = new FormData();
                formData.append('action', 'fw_import_process_batch');
                formData.append('token', importToken);
                formData.append('offset', offset);
                formData.append('limit', batchSize);
                formData.append('update_existing', updateExisting);
                formData.append('gen_desc', genDesc);
                formData.append('auto_district', autoDistrict);
                formData.append('nonce', '<?php echo esc_js( wp_create_nonce( 'fw_import_action' ) ); ?>');

                fetch(ajaxurl, {
                    method: 'POST',
                    body: formData
                })
                .then(function(res) { return res.json(); })
                .then(function(response) {
                    if (!response.success) {
                        log('Batch-Fehler bei Zeile ' + offset + ': ' + (response.data || 'Fehler'), 'err');
                        totalErrors += batchSize;
                        badgeErrors.textContent = 'Fehler: ' + totalErrors;
                        processNextBatch(offset + batchSize);
                        return;
                    }

                    var data = response.data;
                    totalInserted += (data.inserted || 0);
                    totalUpdated += (data.updated || 0);
                    totalSkipped += (data.skipped || 0);
                    totalErrors += (data.errors || 0);

                    badgeInserted.textContent = 'Neu: ' + totalInserted;
                    badgeUpdated.textContent = 'Aktualisiert: ' + totalUpdated;
                    badgeSkipped.textContent = 'Übersprungen: ' + totalSkipped;
                    badgeErrors.textContent = 'Fehler: ' + totalErrors;

                    processedCount = Math.min(offset + (data.processed || batchSize), totalCount);
                    var pct = Math.round((processedCount / totalCount) * 100);
                    progressBar.style.width = pct + '%';
                    statusText.textContent = 'Wird importiert: ' + processedCount + ' / ' + totalCount + ' (%' + pct + ')';

                    if (data.sample_titles && data.sample_titles.length) {
                        data.sample_titles.forEach(function(title) {
                            log('Verarbeitet: ' + title, 'info');
                        });
                    }

                    if (data.done || processedCount >= totalCount) {
                        finishImport();
                    } else {
                        processNextBatch(offset + batchSize);
                    }
                })
                .catch(function(err) {
                    log('Netzwerkfehler während des Batches: ' + err.message, 'err');
                    processNextBatch(offset + batchSize);
                });
            }

            function finishImport() {
                isRunning = false;
                progressBar.style.width = '100%';
                statusText.textContent = '✅ Import erfolgreich abgeschlossen!';
                log('=== IMPORT ABGESCHLOSSEN ===', 'success');
                log('Insgesamt: ' + totalCount + ' | Neu angelegt: ' + totalInserted + ' | Aktualisiert: ' + totalUpdated + ' | Übersprungen: ' + totalSkipped, 'success');
                btnViewAll.style.display = 'inline-block';
                btnReset.style.display = 'inline-block';
            }
        })();
        </script>
        <?php
    }

    /**
     * AJAX Step 1: Upload file, parse rows, and store in a temporary JSON file.
     */
    public static function ajax_upload_file() {
        check_ajax_referer( 'fw_import_action', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Keine Berechtigung.', 'findewerkstatt' ) );
        }

        if ( empty( $_FILES['import_file']['tmp_name'] ) ) {
            wp_send_json_error( __( 'Keine Datei ausgewählt.', 'findewerkstatt' ) );
        }

        $file = $_FILES['import_file'];
        $orig_name = sanitize_file_name( $file['name'] );

        $parsed_rows = FindeWerkstatt_Spreadsheet_Reader::parse( $file['tmp_name'], $orig_name );
        if ( is_wp_error( $parsed_rows ) ) {
            wp_send_json_error( $parsed_rows->get_error_message() );
        }

        if ( empty( $parsed_rows ) ) {
            wp_send_json_error( __( 'Die Datei enthält keine Daten.', 'findewerkstatt' ) );
        }

        // Header detection & column index resolution
        $header = array_shift( $parsed_rows );
        $col_map = self::resolve_column_map( $header );

        // Normalize rows into structured associative records
        $workshops = array();
        foreach ( $parsed_rows as $row ) {
            if ( ! is_array( $row ) || ! array_filter( $row ) ) {
                continue;
            }

            $name = trim( (string) ( $row[ $col_map['name'] ] ?? '' ) );
            $addr = trim( (string) ( $row[ $col_map['address'] ] ?? '' ) );

            if ( in_array( $name, array( '', 'N/A' ), true ) || in_array( $addr, array( '', 'N/A' ), true ) || strlen( $name ) < 2 ) {
                continue;
            }

            $workshops[] = array(
                'search_cat' => trim( (string) ( $row[ $col_map['search_cat'] ] ?? '' ) ),
                'city'       => trim( (string) ( $row[ $col_map['city'] ] ?? '' ) ),
                'district'   => trim( (string) ( $row[ $col_map['district'] ] ?? '' ) ),
                'plz'        => trim( (string) ( $row[ $col_map['plz'] ] ?? '' ) ),
                'name'       => $name,
                'category'   => trim( (string) ( $row[ $col_map['category'] ] ?? '' ) ),
                'rating'     => trim( (string) ( $row[ $col_map['rating'] ] ?? '' ) ),
                'reviews'    => trim( (string) ( $row[ $col_map['reviews'] ] ?? '' ) ),
                'phone'      => trim( (string) ( $row[ $col_map['phone'] ] ?? '' ) ),
                'address'    => $addr,
                'website'    => trim( (string) ( $row[ $col_map['website'] ] ?? '' ) ),
                'image_url'  => trim( (string) ( $row[ $col_map['image_url'] ] ?? '' ) ),
                'maps_url'   => trim( (string) ( $row[ $col_map['maps_url'] ] ?? '' ) ),
            );
        }

        if ( empty( $workshops ) ) {
            wp_send_json_error( __( 'Keine gültigen Werkstatt-Zeilen in der Datei gefunden.', 'findewerkstatt' ) );
        }

        // Store into temporary server cache directory
        $upload_dir = wp_upload_dir();
        $cache_dir = trailingslashit( $upload_dir['basedir'] ) . 'fw-imports';
        if ( ! file_exists( $cache_dir ) ) {
            wp_mkdir_p( $cache_dir );
            file_put_contents( $cache_dir . '/index.php', '<?php // Silence' );
        }

        $token = 'fw_' . wp_generate_password( 16, false );
        $cache_file = $cache_dir . '/' . $token . '.json';
        file_put_contents( $cache_file, wp_json_encode( $workshops ) );

        wp_send_json_success( array(
            'token'      => $token,
            'total_rows' => count( $workshops ),
            'filename'   => $orig_name,
        ) );
    }

    /**
     * AJAX Step 2: Process a chunk/batch of rows from the cached JSON file.
     */
    public static function ajax_process_batch() {
        check_ajax_referer( 'fw_import_action', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Keine Berechtigung.', 'findewerkstatt' ) );
        }

        $token = sanitize_key( $_POST['token'] ?? '' );
        $offset = absint( $_POST['offset'] ?? 0 );
        $limit = absint( $_POST['limit'] ?? 35 );
        $update_existing = ! empty( $_POST['update_existing'] );
        $gen_desc = ! empty( $_POST['gen_desc'] );
        $auto_district = ! empty( $_POST['auto_district'] );

        $upload_dir = wp_upload_dir();
        $cache_file = trailingslashit( $upload_dir['basedir'] ) . 'fw-imports/' . $token . '.json';

        if ( ! file_exists( $cache_file ) ) {
            wp_send_json_error( __( 'Import-Sitzung abgelaufen oder nicht gefunden.', 'findewerkstatt' ) );
        }

        $workshops = json_decode( file_get_contents( $cache_file ), true );
        if ( ! is_array( $workshops ) ) {
            wp_send_json_error( __( 'Ungültige Zwischendaten.', 'findewerkstatt' ) );
        }

        $total_rows = count( $workshops );
        $slice = array_slice( $workshops, $offset, $limit );

        // Optimization: defer counts during batch
        wp_defer_term_counting( true );
        wp_defer_comment_counting( true );

        $stats = array(
            'processed'     => 0,
            'inserted'      => 0,
            'updated'       => 0,
            'skipped'       => 0,
            'errors'        => 0,
            'sample_titles' => array(),
            'done'          => false,
        );

        $options = array(
            'update_existing' => $update_existing,
            'gen_desc'        => $gen_desc,
            'auto_district'   => $auto_district,
        );

        foreach ( $slice as $item ) {
            $stats['processed']++;
            $result = self::import_single_workshop( $item, $options );

            if ( 'inserted' === $result['status'] ) {
                $stats['inserted']++;
                if ( count( $stats['sample_titles'] ) < 5 ) {
                    $stats['sample_titles'][] = '✓ ' . $result['title'];
                }
            } elseif ( 'updated' === $result['status'] ) {
                $stats['updated']++;
                if ( count( $stats['sample_titles'] ) < 5 ) {
                    $stats['sample_titles'][] = '↺ ' . $result['title'];
                }
            } elseif ( 'skipped' === $result['status'] ) {
                $stats['skipped']++;
            } else {
                $stats['errors']++;
            }
        }

        wp_defer_term_counting( false );
        wp_defer_comment_counting( false );

        if ( ( $offset + $limit ) >= $total_rows ) {
            $stats['done'] = true;
            // Clean up temporary cache file
            @unlink( $cache_file );

            // Recalculate taxonomy counts once at completion
            wp_update_term_count_now( get_terms( array( 'taxonomy' => 'service_type', 'fields' => 'ids', 'hide_empty' => false ) ), 'service_type' );
            wp_update_term_count_now( get_terms( array( 'taxonomy' => 'mechanic_city', 'fields' => 'ids', 'hide_empty' => false ) ), 'mechanic_city' );
        }

        wp_send_json_success( $stats );
    }

    /**
     * Map column indexes dynamically.
     */
    public static function resolve_column_map( $header ) {
        // Default indices based on the standard 13-column format
        $map = array(
            'search_cat' => 0,
            'city'       => 1,
            'district'   => 2,
            'plz'        => 3,
            'name'       => 4,
            'category'   => 5,
            'rating'     => 6,
            'reviews'    => 7,
            'phone'      => 8,
            'address'    => 9,
            'website'    => 10,
            'image_url'  => 11,
            'maps_url'   => 12,
        );

        if ( ! is_array( $header ) ) {
            return $map;
        }

        foreach ( $header as $idx => $h ) {
            $clean_h = mb_strtolower( trim( (string) $h ), 'UTF-8' );

            if ( str_contains( $clean_h, 'arama' ) || str_contains( $clean_h, 'suchkategorie' ) ) {
                $map['search_cat'] = $idx;
            } elseif ( str_contains( $clean_h, 'şehir' ) || str_contains( $clean_h, 'sehir' ) || str_contains( $clean_h, 'stadt' ) || str_contains( $clean_h, 'eyalet' ) ) {
                $map['city'] = $idx;
            } elseif ( str_contains( $clean_h, 'ilçe' ) || str_contains( $clean_h, 'ilce' ) || str_contains( $clean_h, 'bezirk' ) || str_contains( $clean_h, 'stadtteil' ) ) {
                $map['district'] = $idx;
            } elseif ( str_contains( $clean_h, 'plz' ) || str_contains( $clean_h, 'posta' ) || str_contains( $clean_h, 'postleitzahl' ) ) {
                $map['plz'] = $idx;
            } elseif ( str_contains( $clean_h, 'işletme' ) || str_contains( $clean_h, 'isletme' ) || str_contains( $clean_h, 'name' ) || str_contains( $clean_h, 'werkstatt' ) ) {
                $map['name'] = $idx;
            } elseif ( str_contains( $clean_h, 'kategori' ) || str_contains( $clean_h, 'branche' ) ) {
                $map['category'] = $idx;
            } elseif ( str_contains( $clean_h, 'puan' ) || str_contains( $clean_h, 'bewertung' ) || str_contains( $clean_h, 'rating' ) ) {
                $map['rating'] = $idx;
            } elseif ( str_contains( $clean_h, 'değerlendirme' ) || str_contains( $clean_h, 'degerlendirme' ) || str_contains( $clean_h, 'berichte' ) || str_contains( $clean_h, 'rezension' ) ) {
                $map['reviews'] = $idx;
            } elseif ( str_contains( $clean_h, 'telefon' ) || str_contains( $clean_h, 'phone' ) ) {
                $map['phone'] = $idx;
            } elseif ( str_contains( $clean_h, 'adres' ) || str_contains( $clean_h, 'adresse' ) || str_contains( $clean_h, 'straße' ) ) {
                $map['address'] = $idx;
            } elseif ( str_contains( $clean_h, 'web' ) || str_contains( $clean_h, 'site' ) || str_contains( $clean_h, 'url' ) && ! str_contains( $clean_h, 'maps' ) && ! str_contains( $clean_h, 'görsel' ) ) {
                $map['website'] = $idx;
            } elseif ( str_contains( $clean_h, 'görsel' ) || str_contains( $clean_h, 'gorsel' ) || str_contains( $clean_h, 'foto' ) || str_contains( $clean_h, 'bild' ) || str_contains( $clean_h, 'image' ) ) {
                $map['image_url'] = $idx;
            } elseif ( str_contains( $clean_h, 'maps' ) || str_contains( $clean_h, 'harita' ) ) {
                $map['maps_url'] = $idx;
            }
        }

        return $map;
    }

    /**
     * Import or update a single workshop record.
     */
    public static function import_single_workshop( $data, $options = array() ) {
        $name = trim( $data['name'] ?? '' );
        if ( empty( $name ) || 'N/A' === $name ) {
            return array( 'status' => 'skipped', 'title' => $name );
        }

        $city_name     = trim( $data['city'] ?? '' );
        $district_name = trim( $data['district'] ?? '' );
        $plz_raw       = trim( $data['plz'] ?? '' );
        $addr_raw      = trim( $data['address'] ?? '' );
        $phone_raw     = trim( $data['phone'] ?? '' );
        $website_raw   = trim( $data['website'] ?? '' );
        $rating_raw    = trim( $data['rating'] ?? '' );
        $reviews_raw   = trim( $data['reviews'] ?? '' );
        $image_raw     = trim( $data['image_url'] ?? '' );
        $maps_raw      = trim( $data['maps_url'] ?? '' );
        $category      = trim( $data['category'] ?? '' );

        // 1. PLZ formatting (ensure 5-digit string with leading zeros)
        $plz = $plz_raw;
        if ( ctype_digit( $plz ) ) {
            $plz = str_pad( $plz, 5, '0', STR_PAD_LEFT );
        }

        // 2. Street address extraction (without trailing PLZ / City duplication)
        $street = $addr_raw;
        $match_plz_split = preg_split( '/,\s*\d{5}\b/u', $addr_raw );
        if ( ! empty( $match_plz_split[0] ) ) {
            $street = trim( $match_plz_split[0] );
        }

        // 3. District cleaning
        $district = ( $district_name && 'N/A' !== $district_name && strcasecmp( $district_name, $city_name ) !== 0 ) ? $district_name : '';

        // 4. Rating & Reviews
        $rating = null;
        if ( $rating_raw && 'N/A' !== $rating_raw ) {
            $r_num = (float) str_replace( ',', '.', $rating_raw );
            if ( $r_num > 0 ) {
                $rating = round( $r_num, 1 );
            }
        }
        $review_count = 0;
        if ( $reviews_raw && 'N/A' !== $reviews_raw ) {
            $clean_rev = preg_replace( '/[^0-9]/', '', explode( ' ', str_replace( '.', '', $reviews_raw ) )[0] );
            if ( ctype_digit( $clean_rev ) ) {
                $review_count = (int) $clean_rev;
            }
        }

        // 5. GPS Coordinates from Maps URL
        $lat = null;
        $lng = null;
        if ( preg_match( '/!3d([0-9.]+)!4d([0-9.]+)/', $maps_raw, $m ) ) {
            $lat = (float) $m[1];
            $lng = (float) $m[2];
        } elseif ( preg_match( '/@([0-9.]+),([0-9.]+)/', $maps_raw, $m2 ) ) {
            $lat = (float) $m2[1];
            $lng = (float) $m2[2];
        }

        // 6. Badges & flags
        $is_verified = ( null !== $rating && $rating >= 4.5 && $review_count >= 10 );
        $is_master = ( false !== stripos( $name, 'meister' ) || false !== stripos( $category, 'meister' ) );
        $emergency_24h = ( false !== stripos( $name, '24h' ) || false !== stripos( $name, 'notdienst' ) || false !== stripos( $category, 'notdienst' ) );

        $is_real_photo = false;
        if ( $image_raw && 'N/A' !== $image_raw ) {
            if ( ! str_contains( $image_raw, 'w36-h36' ) && ! str_contains( $image_raw, 'ALV-U' ) && ! str_contains( $image_raw, 'ACg8oc' ) ) {
                $is_real_photo = true;
            }
        }

        // 7. Car Brand Recognition
        $known_brands = array(
            'volkswagen'    => array( 'volkswagen', 'vw' ),
            'bmw'           => array( 'bmw' ),
            'mercedes-benz' => array( 'mercedes', 'mercedes-benz', 'daimler' ),
            'audi'          => array( 'audi' ),
            'opel'          => array( 'opel' ),
            'ford'          => array( 'ford' ),
            'porsche'       => array( 'porsche' ),
            'skoda'         => array( 'skoda', 'škoda' ),
            'seat'          => array( 'seat', 'cupra' ),
            'renault'       => array( 'renault', 'dacia' ),
            'peugeot'       => array( 'peugeot' ),
            'toyota'        => array( 'toyota', 'lexus' ),
            'hyundai'       => array( 'hyundai' ),
            'kia'           => array( 'kia' ),
            'fiat'          => array( 'fiat', 'abarth', 'alfa romeo' ),
            'volvo'         => array( 'volvo' ),
            'mazda'         => array( 'mazda' ),
            'nissan'        => array( 'nissan' ),
        );
        $matched_brands = array();
        $name_cat_lower = strtolower( $name . ' ' . $category );
        foreach ( $known_brands as $brand_slug => $aliases ) {
            foreach ( $aliases as $alias ) {
                if ( preg_match( '/\b' . preg_quote( $alias, '/' ) . '\b/i', $name_cat_lower ) ) {
                    $matched_brands[] = $brand_slug;
                    break;
                }
            }
        }

        // 8. Find City Term in mechanic_city
        $city_term = self::find_city_term( $city_name );

        // 9. Generate German description if enabled
        $content = '';
        if ( ! empty( $options['gen_desc'] ) ) {
            $district_phrase = $district ? " im Stadtteil {$district}" : "";
            $content  = "<p><strong>" . esc_html( $name ) . "</strong> ist Ihr kompetenter Fachbetrieb für professionellen Reifenservice und Kfz-Dienstleistungen in <strong>" . esc_html( $city_name ) . "</strong>{$district_phrase}.</p>\n";
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
        }

        // 10. Check if post already exists (by Name + PLZ)
        $existing_id = null;
        if ( ! empty( $options['update_existing'] ) ) {
            $existing_id = self::find_existing_mechanic( $name, $plz );
        }

        if ( $existing_id ) {
            // Update
            if ( $content ) {
                wp_update_post( array(
                    'ID'           => $existing_id,
                    'post_content' => $content,
                ) );
            }
            $post_id = $existing_id;
            $status = 'updated';
        } else {
            // Insert
            $post_data = array(
                'post_title'   => $name,
                'post_content' => $content,
                'post_status'  => 'publish',
                'post_type'    => 'mechanic',
                'post_author'  => get_current_user_id() ?: 1,
            );
            $post_id = wp_insert_post( $post_data );
            if ( is_wp_error( $post_id ) ) {
                return array( 'status' => 'error', 'title' => $name, 'message' => $post_id->get_error_message() );
            }
            $status = 'inserted';
        }

        // 11. Assign Taxonomies
        // mechanic_city
        if ( $city_term ) {
            $city_terms = array( (int) $city_term->term_id );
            if ( $city_term->parent ) {
                $city_terms[] = (int) $city_term->parent;
            }
            wp_set_object_terms( $post_id, $city_terms, 'mechanic_city', false );
        }

        // service_type
        $primary_service = get_term_by( 'slug', 'reifenservice-raederwechsel', 'service_type' );
        $service_terms = array();
        if ( $primary_service ) {
            $service_terms[] = (int) $primary_service->term_id;
        }
        $cat_slug = sanitize_title( $category );
        $cat_map = array(
            'karosseriewerkstatt' => 'karosserie-lackiererei',
            'fahrzeuglackiererei' => 'karosserie-lackiererei',
            'autoglaswerkstatt'   => 'autoglas-scheibenreparatur',
            'autowerkstatt'       => 'kfz-werkstatt',
        );
        if ( isset( $cat_map[ $cat_slug ] ) ) {
            $sec = get_term_by( 'slug', $cat_map[ $cat_slug ], 'service_type' );
            if ( $sec ) {
                $service_terms[] = (int) $sec->term_id;
            }
        }
        if ( ! empty( $service_terms ) ) {
            wp_set_object_terms( $post_id, array_unique( $service_terms ), 'service_type', false );
        }

        // mechanic_district
        if ( $district && ! empty( $options['auto_district'] ) ) {
            $dist_slug = sanitize_title( $district );
            $dist_term = get_term_by( 'slug', $dist_slug, 'mechanic_district' );
            if ( ! $dist_term ) {
                $ins_dist = wp_insert_term( $district, 'mechanic_district', array( 'slug' => $dist_slug ) );
                if ( ! is_wp_error( $ins_dist ) ) {
                    wp_set_object_terms( $post_id, array( (int) $ins_dist['term_id'] ), 'mechanic_district', false );
                }
            } else {
                wp_set_object_terms( $post_id, array( (int) $dist_term->term_id ), 'mechanic_district', false );
            }
        }

        // car_brand
        if ( ! empty( $matched_brands ) ) {
            $brand_term_ids = array();
            foreach ( $matched_brands as $b_slug ) {
                $b_term = get_term_by( 'slug', $b_slug, 'car_brand' );
                if ( $b_term ) {
                    $brand_term_ids[] = (int) $b_term->term_id;
                }
            }
            if ( ! empty( $brand_term_ids ) ) {
                wp_set_object_terms( $post_id, $brand_term_ids, 'car_brand', false );
            }
        }

        // 12. Meta Fields
        update_post_meta( $post_id, '_mechanic_phone', 'N/A' !== $phone_raw ? $phone_raw : '' );
        update_post_meta( $post_id, '_mechanic_address', $street );
        update_post_meta( $post_id, '_mechanic_plz', $plz );
        update_post_meta( $post_id, '_mechanic_full_address', $addr_raw );
        update_post_meta( $post_id, '_mechanic_website', 'N/A' !== $website_raw ? $website_raw : '' );
        update_post_meta( $post_id, '_mechanic_rating_avg', null !== $rating ? (string) $rating : '' );
        update_post_meta( $post_id, '_mechanic_rating_count', $review_count );
        if ( null !== $lat ) update_post_meta( $post_id, '_mechanic_latitude', (string) $lat );
        if ( null !== $lng ) update_post_meta( $post_id, '_mechanic_longitude', (string) $lng );
        update_post_meta( $post_id, '_mechanic_google_maps_url', 'N/A' !== $maps_raw ? $maps_raw : '' );
        update_post_meta( $post_id, '_mechanic_google_photo_url', 'N/A' !== $image_raw ? $image_raw : '' );
        update_post_meta( $post_id, '_mechanic_is_real_photo', $is_real_photo ? 'yes' : 'no' );
        update_post_meta( $post_id, '_mechanic_is_verified', $is_verified ? 'yes' : 'no' );
        update_post_meta( $post_id, '_mechanic_is_master', $is_master ? 'yes' : 'no' );
        update_post_meta( $post_id, '_mechanic_emergency_24h', $emergency_24h ? 'yes' : 'no' );
        update_post_meta( $post_id, '_mechanic_hours_weekday', '08:00 - 18:00 Uhr' );
        update_post_meta( $post_id, '_mechanic_hours_saturday', '09:00 - 13:00 Uhr' );
        update_post_meta( $post_id, '_mechanic_hours_sunday', 'Geschlossen' );
        update_post_meta( $post_id, '_mechanic_languages', array( 'de' ) );

        if ( preg_match( '/\+49\s*(15|16|17)/', $phone_raw ) ) {
            update_post_meta( $post_id, '_mechanic_whatsapp', $phone_raw );
        }

        return array(
            'status'  => $status,
            'post_id' => $post_id,
            'title'   => $name,
        );
    }

    /**
     * Helper: Resolve city name or slug to term.
     */
    public static function find_city_term( $city_name ) {
        if ( empty( $city_name ) ) {
            return null;
        }

        // 1. Direct name lookup
        $term = get_term_by( 'name', $city_name, 'mechanic_city' );
        if ( $term ) {
            return $term;
        }

        // 2. Slug lookup
        $slug = sanitize_title( $city_name );
        $term = get_term_by( 'slug', $slug, 'mechanic_city' );
        if ( $term ) {
            return $term;
        }

        // 3. German umlaut normalized lookup
        $clean = str_replace(
            array( 'ä', 'ö', 'ü', 'ß', 'Ä', 'Ö', 'Ü' ),
            array( 'ae', 'oe', 'ue', 'ss', 'ae', 'oe', 'ue' ),
            mb_strtolower( $city_name, 'UTF-8' )
        );
        $term = get_term_by( 'slug', sanitize_title( $clean ), 'mechanic_city' );
        return $term ?: null;
    }

    /**
     * Helper: Check existing mechanic post by Name and PLZ.
     */
    public static function find_existing_mechanic( $name, $plz = '' ) {
        global $wpdb;

        if ( $plz ) {
            $post_id = $wpdb->get_var( $wpdb->prepare(
                "SELECT p.ID FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm ON (p.ID = pm.post_id AND pm.meta_key = '_mechanic_plz')
                 WHERE p.post_type = 'mechanic' AND p.post_status = 'publish'
                   AND p.post_title = %s AND pm.meta_value = %s
                 LIMIT 1",
                $name,
                $plz
            ) );
            if ( $post_id ) {
                return (int) $post_id;
            }
        }

        // Fallback: title only
        $post = get_page_by_title( $name, OBJECT, 'mechanic' );
        return $post ? (int) $post->ID : null;
    }
}

FindeWerkstatt_Workshop_Importer::init();
