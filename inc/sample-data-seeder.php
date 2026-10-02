<?php
/**
 * Freiwillige, eindeutig markierte Demo-Entwürfe für die Einrichtung.
 *
 * @package FindeWerkstatt
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FindeWerkstatt_Sample_Seeder {
    // Legacy-Option bleibt für die sichere Migration vorhandener Beispieldaten verfügbar.
    const OPTION_SAMPLE_SEEDED = 'findewerkstatt_sample_workshops_v1';
    const OPTION_DEMO_SEEDED   = 'findewerkstatt_demo_drafts_v2';

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'register_admin_menu' ) );
        add_action( 'admin_post_fw_create_demo_drafts', array( __CLASS__, 'handle_create_drafts' ) );
    }

    public static function register_admin_menu() {
        add_submenu_page( 'edit.php?post_type=mechanic', 'Demo-Entwürfe', 'Demo-Entwürfe', 'manage_options', 'fw-demo-drafts', array( __CLASS__, 'render_admin_page' ) );
    }

    public static function render_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Du hast keine Berechtigung für diese Aktion.', 'findewerkstatt' ) );
        }
        ?>
        <div class="wrap">
            <h1>Demo-Entwürfe für das Werkstattverzeichnis</h1>
            <p>Optional können sechs fiktive Werkstätten als Entwürfe angelegt werden. Sie sind deutlich als Demo markiert und bleiben für Besucher unsichtbar.</p>
            <p>Die Entwürfe enthalten keine echten Kontaktdaten, Bewertungen oder Prüfsiegel. Ersetze die Beispielangaben vollständig, bevor du eine Werkstatt veröffentlichst.</p>
            <?php if ( isset( $_GET['fw_demo_created'] ) && is_scalar( $_GET['fw_demo_created'] ) ) : ?>
                <div class="notice notice-success"><p><?php echo esc_html( sprintf( '%d Demo-Entwürfe wurden angelegt.', absint( $_GET['fw_demo_created'] ) ) ); ?></p></div>
            <?php endif; ?>
            <?php if ( ! get_option( self::OPTION_DEMO_SEEDED ) ) : ?>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <input type="hidden" name="action" value="fw_create_demo_drafts">
                    <?php wp_nonce_field( 'fw_create_demo_drafts' ); ?>
                    <?php submit_button( 'Sechs Demo-Entwürfe anlegen' ); ?>
                </form>
            <?php else : ?>
                <p>Die Demo-Entwürfe wurden bereits angelegt. Du findest sie unter <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=mechanic&post_status=draft' ) ); ?>">Werkstätten → Entwürfe</a>.</p>
            <?php endif; ?>
        </div>
        <?php
    }

    public static function handle_create_drafts() {
        if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'edit_posts' ) ) {
            wp_die( esc_html__( 'Du hast keine Berechtigung für diese Aktion.', 'findewerkstatt' ), '', array( 'response' => 403 ) );
        }
        check_admin_referer( 'fw_create_demo_drafts' );
        $created = self::create_demo_drafts();
        wp_safe_redirect( add_query_arg( array( 'post_type' => 'mechanic', 'page' => 'fw-demo-drafts', 'fw_demo_created' => $created ), admin_url( 'edit.php' ) ) );
        exit;
    }

    private static function create_demo_drafts() {
        if ( ! current_user_can( 'manage_options' ) || get_option( self::OPTION_DEMO_SEEDED ) ) {
            return 0;
        }
        // add_option ist atomar: parallele Formularaufrufe erzeugen keine Dubletten.
        $lock = self::OPTION_DEMO_SEEDED . '_lock';
        if ( ! add_option( $lock, time(), '', false ) ) {
            if ( (int) get_option( $lock ) > time() - 300 ) {
                return 0;
            }
            delete_option( $lock );
            if ( ! add_option( $lock, time(), '', false ) ) {
                return 0;
            }
        }
        $created = 0;
        $complete = true;
        try {
            FindeWerkstatt_CPT_Taxonomies::check_and_seed();
            $cities = array( 'berlin' => 'Berlin', 'hamburg' => 'Hamburg', 'muenchen' => 'München', 'koeln' => 'Köln', 'frankfurt-am-main' => 'Frankfurt am Main', 'stuttgart' => 'Stuttgart' );
            foreach ( $cities as $slug => $name ) {
                $existing = get_posts( array(
                    'post_type' => 'mechanic', 'post_status' => array( 'draft', 'pending', 'publish', 'private', 'trash' ),
                    'posts_per_page' => 1, 'fields' => 'ids',
                    'meta_query' => array(
                        array( 'key' => '_findewerkstatt_demo', 'value' => 'yes' ),
                        array( 'key' => '_findewerkstatt_demo_city', 'value' => $slug ),
                    ),
                ) );
                if ( $existing ) {
                    continue;
                }
                $post_id = wp_insert_post( array(
                    'post_title' => '[DEMO] Beispielwerkstatt ' . $name,
                    'post_content' => 'Fiktiver Demo-Entwurf zur Einrichtung des Verzeichnisses. Bitte alle Angaben durch überprüfte Informationen einer echten Werkstatt ersetzen. Dieser Eintrag ist kein realer Betrieb.',
                    'post_type' => 'mechanic', 'post_status' => 'draft',
                    'post_author' => get_current_user_id(),
                    'meta_input' => array( '_findewerkstatt_demo' => 'yes', '_findewerkstatt_demo_city' => $slug ),
                ), true );
                if ( ! $post_id || is_wp_error( $post_id ) ) {
                    $complete = false;
                    continue;
                }
                $city = get_term_by( 'slug', $slug, 'mechanic_city' );
                if ( $city && ! is_wp_error( $city ) ) {
                    wp_set_object_terms( $post_id, array( (int) $city->term_id ), 'mechanic_city' );
                }
                $service = get_term_by( 'slug', 'freie-werkstatt', 'service_type' );
                if ( $service && ! is_wp_error( $service ) ) {
                    wp_set_object_terms( $post_id, array( (int) $service->term_id ), 'service_type' );
                }
                $created++;
            }
            if ( $complete ) {
                update_option( self::OPTION_DEMO_SEEDED, 1, false );
            }
        } finally {
            delete_option( $lock );
        }
        return $created;
    }
}
FindeWerkstatt_Sample_Seeder::init();