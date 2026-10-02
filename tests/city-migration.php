<?php
/** Isolierte Migrationstests; verändert keine WordPress-Datenbank. */
if ( PHP_SAPI !== 'cli' ) { http_response_code( 404 ); exit; }
define( 'ABSPATH', __DIR__ . '/' );
class WP_Error {
    public $code; public $message;
    public function __construct( $code, $message, $data = null ) { $this->code = $code; $this->message = $message; }
}
class WP_Term {
    public $term_id; public $slug; public $name; public $parent; public $taxonomy = 'mechanic_city'; public $description = '';
    public function __construct( $id, $slug, $name, $parent = 0 ) { $this->term_id = $id; $this->slug = $slug; $this->name = $name; $this->parent = $parent; }
}
class FindeWerkstatt_German_Data {
    public static function get_top_cities() {
        return array(
            'muenchen' => array( 'name' => 'München', 'land' => 'bayern' ),
            'koeln' => array( 'name' => 'Köln', 'land' => 'nordrhein-westfalen' ),
            'berlin' => array( 'name' => 'Berlin', 'land' => 'berlin' ),
            'ludwigshafen-am-rhein' => array( 'name' => 'Ludwigshafen am Rhein', 'land' => 'rheinland-pfalz' ),
        );
    }
}
class FindeWerkstatt_Germany_Geo_Hierarchy {
    public static function get_districts_by_state() {
        return array(
            'bayern' => array( 'fuerth' => 'Fürth (Stadt)', 'lk-fuerth' => 'Landkreis Fürth' ),
            'schleswig-holstein' => array( 'neumuenster' => 'Neumünster (Stadt)' ),
            'niedersachsen' => array( 'hildesheim-stadt' => 'Hildesheim (Stadt)' ),
        );
    }
}
$options = array();
$authorized = true;
$terms = array(
    1 => new WP_Term( 1, 'bayern', 'Bayern' ),
    2 => new WP_Term( 2, 'munchen', 'München', 1 ),
    3 => new WP_Term( 3, 'muenchen', 'München', 1 ),
    4 => new WP_Term( 4, 'viertel', 'Viertel', 2 ),
    5 => new WP_Term( 5, 'hessen', 'Hessen' ),
    6 => new WP_Term( 6, 'berlin', 'Berlin' ),
    7 => new WP_Term( 7, 'nordrhein-westfalen', 'Nordrhein-Westfalen' ),
    8 => new WP_Term( 8, 'koln', 'Köln', 7 ),
    9 => new WP_Term( 9, 'muenchen-anderer-ort', 'München', 5 ),
    10 => new WP_Term( 10, 'bremsen', 'Bremsenservice' ),
    11 => new WP_Term( 11, 'bmw', 'BMW' ),
    12 => new WP_Term( 12, 'furth', 'Fürth', 1 ),
    13 => new WP_Term( 13, 'fuerth', 'Fürth (Stadt)', 1 ),
    14 => new WP_Term( 14, 'neumunster', 'Neumünster', 15 ),
    15 => new WP_Term( 15, 'schleswig-holstein', 'Schleswig-Holstein' ),
    16 => new WP_Term( 16, 'neumuenster', 'Neumünster (Stadt)', 15 ),
    17 => new WP_Term( 17, 'hildesheim', 'Hildesheim', 18 ),
    18 => new WP_Term( 18, 'niedersachsen', 'Niedersachsen' ),
    19 => new WP_Term( 19, 'hildesheim-stadt', 'Hildesheim (Stadt)', 18 ),
    20 => new WP_Term( 20, 'brandenburg', 'Brandenburg' ),
    21 => new WP_Term( 21, 'brandenburg-an-der-havel', 'Brandenburg an der Havel', 20 ),
    22 => new WP_Term( 22, 'brandenburg-stadt', 'Brandenburg an der Havel', 20 ),
    23 => new WP_Term( 23, 'lk-fuerth', 'Landkreis Fürth', 1 ),
    24 => new WP_Term( 24, 'lk-fuerth-andere-region', 'Landkreis Fürth', 1 ),
    25 => new WP_Term( 25, 'muenchen-aehnlich', 'München mit anderem Zusatz', 1 ),
    26 => new WP_Term( 26, 'furth-viertel', 'Fürther Viertel', 12 ),
    28 => new WP_Term( 28, 'havel-viertel', 'Havelviertel', 21 ),
    29 => new WP_Term( 29, 'havel-viertel-dublette', 'Havelviertel', 22 ),
    30 => new WP_Term( 30, 'rheinland-pfalz', 'Rheinland-Pfalz' ),
    136 => new WP_Term( 136, 'ludwigshafen-am-rhein', 'Ludwigshafen am Rhein', 30 ),
    181 => new WP_Term( 181, 'ludwigshafen', 'Ludwigshafen', 30 ),
    182 => new WP_Term( 182, 'ludwigshafen-hessen', 'Ludwigshafen', 5 ),
    183 => new WP_Term( 183, 'lk-ludwigshafen', 'Ludwigshafen', 30 ),
    184 => new WP_Term( 184, 'oggersheim', 'Oggersheim', 181 ),
);
$terms[10]->taxonomy = 'service_type';
$terms[11]->taxonomy = 'car_brand';
$metadata = array( 2 => array( 'custom' => array( 'alt', serialize( array( 'nested' => 'Wert' ) ) ) ), 3 => array( 'custom' => array( 'neu' ) ) );
$metadata[136] = array( '_fw_city_ags' => array( '07314000' ), '_geo_lat' => array( '49.483235' ), '_geo_lng' => array( '8.447899' ) );
$metadata[181] = array( '_geo_lat' => array( '49.4811' ), '_geo_lng' => array( '8.4464' ), 'legacy' => array( 'bewahren' ) );
$relations = array( 100 => array( 2 ), 101 => array( 2, 3, 5 ), 102 => array( 8 ), 103 => array( 9 ), 200 => array( 12 ), 210 => array( 14 ), 300 => array( 19 ), 400 => array( 22 ), 500 => array( 23, 24 ) );
$relations[600] = array( 181 );
$relations[601] = array( 136, 181, 5 );
$relations[602] = array( 182 );
$relations[603] = array( 183 );
$events = array();
function add_action( $hook, $callback, $priority = 10 ) {}
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function current_user_can( $capability ) { return $GLOBALS['authorized']; }
function sanitize_title( $value ) { return strtolower( trim( (string) $value, '/' ) ); }
function get_option( $key, $default = null ) { return $GLOBALS['options'][ $key ] ?? $default; }
function update_option( $key, $value, $autoload = false ) { $GLOBALS['options'][ $key ] = $value; $GLOBALS['events'][] = 'save:' . $key; return true; }
function get_term( $id, $taxonomy ) { return $GLOBALS['terms'][ $id ] ?? null; }
function get_term_by( $field, $value, $taxonomy ) {
    foreach ( $GLOBALS['terms'] as $term ) { if ( $term->slug === $value && $term->taxonomy === $taxonomy ) { return $term; } }
    return false;
}
function get_terms( $args ) {
    return array_values( array_filter( $GLOBALS['terms'], function( $term ) use ( $args ) {
        return $term->taxonomy === $args['taxonomy'] && ( ! isset( $args['parent'] ) || $term->parent === (int) $args['parent'] ) && ( ! isset( $args['name'] ) || $term->name === $args['name'] );
    } ) );
}
function get_objects_in_term( $id, $taxonomy ) {
    $result = array();
    foreach ( $GLOBALS['relations'] as $post_id => $ids ) { if ( in_array( $id, $ids, true ) ) { $result[] = $post_id; } }
    return $result;
}
function wp_get_object_terms( $id, $taxonomy, $args ) { return $GLOBALS['relations'][ $id ] ?? array(); }
function wp_set_object_terms( $id, $ids, $taxonomy, $append ) { $GLOBALS['relations'][ $id ] = array_values( array_unique( array_merge( $append ? ( $GLOBALS['relations'][ $id ] ?? array() ) : array(), $ids ) ) ); return $ids; }
function wp_update_term( $id, $taxonomy, $args ) { foreach ( $args as $key => $value ) { $GLOBALS['terms'][ $id ]->$key = $value; } $GLOBALS['events'][] = 'update:' . $id; return array( 'term_id' => $id ); }
function get_term_meta( $id ) { return $GLOBALS['metadata'][ $id ] ?? array(); }
function maybe_unserialize( $value ) { if ( is_string( $value ) && preg_match( '/^[aObisdN]:/', $value ) ) { return unserialize( $value ); } return $value; }
function add_term_meta( $id, $key, $value ) { $GLOBALS['metadata'][ $id ][ $key ][] = is_array( $value ) ? serialize( $value ) : $value; return 1; }
function wp_delete_term( $id, $taxonomy ) {
    $GLOBALS['events'][] = 'delete:' . $id;
    unset( $GLOBALS['terms'][ $id ], $GLOBALS['metadata'][ $id ] );
    foreach ( $GLOBALS['relations'] as &$ids ) { $ids = array_values( array_diff( $ids, array( $id ) ) ); }
    unset( $ids );
    return true;
}
function wp_slash( $value ) { return $value; }
function wp_unslash( $value ) { return $value; }
require dirname( __DIR__ ) . '/inc/city-migration.php';
$checks = 0;
function check( $condition, $message ) { ++$GLOBALS['checks']; if ( ! $condition ) { throw new RuntimeException( $message ); } }

$authorized = false;
check( is_wp_error( findewerkstatt_merge_city_terms() ) && ! $events, 'Unberechtigte Migration verändert keine Daten.' );
$authorized = true;
$result = findewerkstatt_merge_city_terms();
check( ! is_wp_error( $result ) && 7 === $result['merged'] && 1 === $result['renamed'] && 2 === $result['names_updated'], 'Bekannte Dubletten, Stadtvarianten und exakte weitere Ortsdubletten werden vereinigt.' );
check( ! isset( $terms[2] ) && isset( $terms[3] ), 'Nur der doppelte Term wird entfernt.' );
check( array( 3 ) === $relations[100], 'Direkte Post-Zuordnung wandert zum kanonischen Term.' );
check( array( 3, 5 ) === $relations[101], 'Andere Ortszuordnungen bleiben erhalten und Canonical wird nicht doppelt angehängt.' );
check( 3 === $terms[4]->parent, 'Kinder werden zum kanonischen Term umgehängt.' );
check( 'koeln' === $terms[8]->slug && array( 8 ) === $relations[102], 'Umbenennen bewahrt Term-ID und Post-Zuordnung.' );
check( 0 === $terms[6]->parent, 'Stadtstaat bleibt Wurzel und wird nicht sein eigener Elternterm.' );
check( isset( $terms[9] ) && array( 9 ) === $relations[103], 'Namensgleicher Ort im anderen Bundesland bleibt unverändert.' );
check( array( 'neu', 'alt', serialize( array( 'nested' => 'Wert' ) ) ) === $metadata[3]['custom'], 'Abweichende und strukturierte Metadaten gehen nicht verloren.' );
check( isset( $terms[13] ) && 'Fürth' === $terms[13]->name && array( 13 ) === $relations[200], 'Fürth behält die belegte canonical-Slug-Identität und den normalen Stadtnamen.' );
check( isset( $terms[16] ) && 'Neumünster' === $terms[16]->name && array( 16 ) === $relations[210], 'Neumünster behält den korrigierten Datenslug und seine Beziehungen.' );
check( isset( $terms[17] ) && ! isset( $terms[19] ) && array( 17 ) === $relations[300], 'Ohne vorgegebenen Top-Slug bleibt der vorhandene Stadtbegriff ohne Zusatz.' );
check( isset( $terms[21] ) && ! isset( $terms[22] ) && array( 21 ) === $relations[400], 'Exakte Dubletten außerhalb der Stadtlisten behalten die ältere Identität.' );
check( isset( $terms[23], $terms[24], $terms[25] ) && array( 23, 24 ) === $relations[500], 'Landkreise und nur ähnliche Namen bleiben unangetastet.' );
check( 13 === $terms[26]->parent, 'Kinder eines Stadtvarianten-Terms werden korrekt umgehängt.' );
check( isset( $terms[28] ) && ! isset( $terms[29] ) && 21 === $terms[28]->parent, 'Durch Umhängen neu entstandene exakte Kind-Dubletten werden ebenfalls vereinigt.' );
check( isset( $terms[136] ) && ! isset( $terms[181] ) && 'Ludwigshafen am Rhein' === $terms[136]->name && 'ludwigshafen-am-rhein' === $terms[136]->slug && 30 === $terms[136]->parent, 'Ludwigshafen behält die bestehende offizielle Identität samt ID, Slug und Elterngebiet.' );
check( array( 136 ) === $relations[600] && array( 136, 5 ) === $relations[601], 'Werkstattzuordnungen der verkürzten Ludwigshafen-Variante werden ohne Duplikate übertragen.' );
check( 136 === $terms[184]->parent, 'Stadtteile der verkürzten Ludwigshafen-Variante werden zur offiziellen Stadt umgehängt.' );
check( isset( $terms[182], $terms[183] ) && array( 182 ) === $relations[602] && array( 183 ) === $relations[603], 'Gleicher Kurzname in einem anderen Bundesland und Landkreis bleiben unangetastet.' );
check( array( '07314000' ) === $metadata[136]['_fw_city_ags'] && '49.483235' === $metadata[136]['_geo_lat'][0] && array( 'bewahren' ) === $metadata[136]['legacy'], 'Offizielle Ludwigshafen-Metadaten bleiben führend und zusätzliche alte Angaben werden bewahrt.' );
check( 'ludwigshafen-am-rhein' === $options['findewerkstatt_city_aliases']['ludwigshafen'], 'Die frühere Ludwigshafen-Adresse bleibt als Alias erreichbar.' );
check( array( 136, 181, 5 ) === $options['findewerkstatt_city_merge_snapshots']['181:ludwigshafen-am-rhein']['object_terms'][601]
    && 181 === $options['findewerkstatt_city_merge_snapshots']['181:ludwigshafen-am-rhein']['children'][184]['term']['parent'], 'Ludwigshafen-Zuordnungen und Kinder werden vor der Zusammenführung vollständig gesichert.' );
check( array_search( 'save:findewerkstatt_city_merge_snapshots', $events, true ) < array_search( 'delete:181', $events, true ), 'Dubletten-Sicherung wird vor dem Löschen der Ludwigshafen-Variante geschrieben.' );
check( 'Fürth (Stadt)' === $options['findewerkstatt_city_merge_snapshots']['13:fuerth:canonical']['source']['name'], 'Auch der ursprüngliche Zusatz des erhaltenen canonical-Terms bleibt gesichert.' );
check( 'Hildesheim (Stadt)' === $options['findewerkstatt_city_merge_snapshots']['19:hildesheim']['source']['name'], 'Gelöschte Stadtvariantennamen bleiben in der Term-Sicherung.' );
$snapshot = $options['findewerkstatt_city_merge_snapshots']['2:muenchen'];
check( 'munchen' === $snapshot['source']['slug'] && 2 === $snapshot['children'][4]['term']['parent'], 'Originalterm und alte Kinderkette sind gesichert.' );
check( array( 2, 3, 5 ) === $snapshot['object_terms'][101], 'Originalzuordnungen bleiben in der Sicherung.' );
check( array_search( 'save:findewerkstatt_city_merge_snapshots', $events, true ) < array_search( 'delete:2', $events, true ), 'Sicherung wird vor Löschung gespeichert.' );
check( 'muenchen' === $options['findewerkstatt_city_aliases']['munchen'] && 'koeln' === $options['findewerkstatt_city_aliases']['koln'], 'Frühere Slugs bleiben als Aliase erhalten.' );
$result = findewerkstatt_merge_city_terms();
check( ! is_wp_error( $result ) && 0 === $result['merged'] && 0 === $result['renamed'], 'Zweiter Aufruf ist idempotent.' );
check( $snapshot === $options['findewerkstatt_city_merge_snapshots']['2:muenchen'], 'Zweiter Aufruf überschreibt Original-Sicherung nicht.' );

check( 'stadt/bayern/muenchen' === findewerkstatt_city_alias_path( 'stadt/bayern/munchen' ), 'Alter Stadtslug löst zum korrekten Bundesland auf.' );
check( 'stadt/bayern/muenchen/page/2' === findewerkstatt_city_alias_path( 'stadt/bayern/munchen/page/2/' ), 'Pagination bleibt erhalten.' );
check( 'stadt/bayern/muenchen/feed/rss2' === findewerkstatt_city_alias_path( 'stadt/bayern/munchen/feed/rss2/' ), 'Feedsuffix bleibt erhalten.' );
check( 'stadt/bayern/muenchen/viertel' === findewerkstatt_city_alias_path( 'stadt/bayern/munchen/viertel/' ), 'Alias in Elternsegment behält echte Kindkette.' );
check( 'stadt/bayern/fuerth/furth-viertel' === findewerkstatt_city_alias_path( 'stadt/bayern/furth/furth-viertel/' ), 'Kinder bleiben unter einem canonical-Stadtvarianten-Alias erreichbar.' );
check( '' === findewerkstatt_city_alias_path( 'stadt/hessen/munchen' ), 'Falsches Bundesland erhält keinen Redirect.' );
check( '' === findewerkstatt_city_alias_path( 'stadt/bayern/munchen/erfunden' ), 'Unbekanntes Kind erhält keinen Redirect.' );
check( '' === findewerkstatt_city_alias_path( 'stadt/bayern/munchen/page/0' ), 'Ungültige Seitennummer erhält keinen Redirect.' );
check( 'service/bremsen/in/muenchen/page/2' === findewerkstatt_city_alias_path( 'service/bremsen/in/munchen/page/2' ), 'Leistungsalias bewahrt Pagination.' );
check( 'marke/bmw/in/muenchen' === findewerkstatt_city_alias_path( 'marke/bmw/in/munchen' ), 'Markenalias wird aufgelöst.' );
check( '' === findewerkstatt_city_alias_path( 'service/erfunden/in/munchen' ), 'Unbekannte Leistung wird nicht durch einen Alias akzeptiert.' );
check( 'stadt/rheinland-pfalz/ludwigshafen-am-rhein' === findewerkstatt_city_alias_path( 'stadt/rheinland-pfalz/ludwigshafen/' ), 'Ludwigshafen-Alias führt zur offiziellen Stadt im richtigen Bundesland.' );
check( 'stadt/rheinland-pfalz/ludwigshafen-am-rhein/oggersheim' === findewerkstatt_city_alias_path( 'stadt/rheinland-pfalz/ludwigshafen/oggersheim/' ), 'Frühere Ludwigshafen-Stadtteilpfade behalten ihre Ortskette.' );
check( '' === findewerkstatt_city_alias_path( 'stadt/hessen/ludwigshafen/' ), 'Ludwigshafen-Alias überschreibt keine Standortkette im falschen Bundesland.' );
$_GET = array( 'fw_city' => 'munchen', 'fw_pseo_location' => 'koln', 'keep' => 'value' );
$wp = (object) array( 'request' => 'werkstaetten', 'query_vars' => array( 'fw_pseo_location' => 'koln' ) );
findewerkstatt_normalize_city_alias_request( $wp );
check( 'muenchen' === $_GET['fw_city'] && 'koeln' === $_GET['fw_pseo_location'], 'Gefilterte Suchparameter werden früh normalisiert.' );
check( 'koeln' === $wp->query_vars['fw_pseo_location'] && 'value' === $_GET['keep'], 'Parsed Query wird aktualisiert, andere Argumente bleiben erhalten.' );
$options['findewerkstatt_city_aliases']['loop-a'] = 'loop-b';
$options['findewerkstatt_city_aliases']['loop-b'] = 'loop-a';
check( 'loop-a' === findewerkstatt_normalize_city_slug( 'loop-a' ), 'Aliaszyklen werden ohne Endlosschleife verworfen.' );
echo 'Stadtmigration und Aliase: ' . $checks . " Prüfungen erfolgreich.\n";

