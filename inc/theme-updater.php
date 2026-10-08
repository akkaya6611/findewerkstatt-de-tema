<?php
/**
 * FindeWerkstatt.de — Automatischer GitHub Theme-Updater
 *
 * Ermöglicht automatische Update-Benachrichtigungen und 1-Klick-Aktualisierungen
 * direkt aus dem offiziellen GitHub-Repository (akkaya6611/findewerkstatt-de-tema).
 *
 * @package FindeWerkstatt
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FindeWerkstatt_Theme_Updater {

    const GITHUB_REPO     = 'akkaya6611/findewerkstatt-de-tema';
    const GITHUB_BRANCH   = 'main';
    const THEME_SLUG      = 'findewerkstatt-de-tema';
    const TRANSIENT_KEY   = 'fw_theme_github_update_info';
    const CACHE_LIFETIME  = 1800; // 30 Minuten Standard-Cache statt 6 Stunden

    /**
     * Ermittelt den tatsächlichen Theme-Slug
     */
    public static function get_theme_slug() {
        return function_exists( 'get_template' ) ? get_template() : self::THEME_SLUG;
    }

    /**
     * Initialisierung aller Hooks
     */
    public static function init() {
        if ( ! function_exists( 'add_action' ) || ( function_exists( 'is_admin' ) && ! is_admin() ) ) {
            return;
        }

        // 1. Plugin Update Checker (PUC) initialisieren, falls vorhanden
        self::init_puc();

        // 2. WordPress Theme-Update-Transiente manipulieren
        add_filter( 'pre_set_site_transient_update_themes', array( __CLASS__, 'check_for_theme_update' ) );

        // 3. Extrahierter Ordnername beim Entpacken korrigieren (GitHub hängt Branch-Name an)
        add_filter( 'upgrader_source_selection', array( __CLASS__, 'fix_github_unzip_directory' ), 10, 4 );

        // 4. Admin-Benachrichtigung anzeigen, wenn Update verfügbar ist
        add_action( 'admin_notices', array( __CLASS__, 'render_update_admin_notice' ) );

        // 5. Manuelle Update-Prüfung per URL (?fw_check_update=1)
        add_action( 'admin_init', array( __CLASS__, 'handle_manual_check' ) );

        // 6. Nach erfolgreichem Update Cache leeren
        add_action( 'upgrader_process_complete', array( __CLASS__, 'clear_cache_after_update' ), 10, 2 );
    }

    /**
     * Plugin Update Checker v5 initialisieren
     */
    public static function init_puc() {
        $puc_file = get_template_directory() . '/inc/plugin-update-checker/plugin-update-checker.php';
        if ( file_exists( $puc_file ) ) {
            require_once $puc_file;
            if ( class_exists( 'YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
                $checker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
                    'https://github.com/' . self::GITHUB_REPO . '/',
                    get_template_directory() . '/style.css',
                    self::get_theme_slug()
                );
                if ( method_exists( $checker, 'setBranch' ) ) {
                    $checker->setBranch( self::GITHUB_BRANCH );
                }
            }
        }
    }

    /**
     * Ermittelt die aktuelle installierte Theme-Version
     */
    public static function get_installed_version() {
        $theme = wp_get_theme( self::get_theme_slug() );
        return $theme->exists() ? $theme->get( 'Version' ) : '0.0.0';
    }

    /**
     * Holt die neueste Version und Paket-URL von GitHub (mit Caching)
     */
    public static function get_remote_theme_info( $force_refresh = false ) {
        if ( ! $force_refresh ) {
            $cached = get_transient( self::TRANSIENT_KEY );
            if ( false !== $cached && is_array( $cached ) ) {
                return $cached;
            }
        }

        $raw_style_url = sprintf(
            'https://raw.githubusercontent.com/%s/%s/style.css?t=%d',
            self::GITHUB_REPO,
            self::GITHUB_BRANCH,
            time()
        );

        $response = wp_remote_get( $raw_style_url, array(
            'timeout'    => 10,
            'user-agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url( '/' ),
            'headers'    => array(
                'Accept' => 'text/plain',
            ),
        ) );

        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return false;
        }

        $css_content = wp_remote_retrieve_body( $response );
        if ( empty( $css_content ) ) {
            return false;
        }

        // Version aus style.css Header extrahieren
        if ( ! preg_match( '/^[ \t\/*#@]*Version:\s*([0-9\.]+)/mi', $css_content, $matches ) ) {
            return false;
        }

        $remote_version = trim( $matches[1] );
        $package_url    = sprintf(
            'https://github.com/%s/archive/refs/heads/%s.zip',
            self::GITHUB_REPO,
            self::GITHUB_BRANCH
        );

        $info = array(
            'version'     => $remote_version,
            'package'     => $package_url,
            'url'         => 'https://github.com/' . self::GITHUB_REPO,
            'checked_at'  => time(),
        );

        set_transient( self::TRANSIENT_KEY, $info, self::CACHE_LIFETIME );
        return $info;
    }

    /**
     * Injiziert das GitHub-Update in den WordPress-Update-Manager
     */
    public static function check_for_theme_update( $transient ) {
        if ( empty( $transient ) || ! is_object( $transient ) ) {
            return $transient;
        }

        $remote_info = self::get_remote_theme_info();
        if ( ! $remote_info || empty( $remote_info['version'] ) ) {
            return $transient;
        }

        $current_version = self::get_installed_version();
        $slug            = self::get_theme_slug();

        // Wenn GitHub eine neuere Versionsnummer hat als lokal
        if ( version_compare( $current_version, $remote_info['version'], '<' ) ) {
            $transient->response[ $slug ] = array(
                'theme'       => $slug,
                'new_version' => $remote_info['version'],
                'url'         => $remote_info['url'],
                'package'     => $remote_info['package'],
                'requires'    => '6.0',
                'requires_php'=> '8.0',
            );
        } else {
            // Aktuelle Version ist auf dem neuesten Stand
            unset( $transient->response[ $slug ] );
        }

        return $transient;
    }

    /**
     * Korrigiert den Ordnernamen beim Entpacken des ZIP-Archivs
     * GitHub entpackt 'findewerkstatt-de-tema-main/', WordPress benötigt aber den aktuellen Theme-Ordner
     */
    public static function fix_github_unzip_directory( $source, $remote_source, $upgrader, $hook_extra = array() ) {
        global $wp_filesystem;

        $slug = self::get_theme_slug();
        if ( ! isset( $hook_extra['theme'] ) || $slug !== $hook_extra['theme'] ) {
            return $source;
        }

        $proper_destination = trailingslashit( $remote_source ) . $slug . '/';

        // Wenn der entpackte Quellordner nicht dem Theme-Slug entspricht, umbenennen
        if ( trailingslashit( $source ) !== $proper_destination ) {
            if ( $wp_filesystem->move( $source, $proper_destination ) ) {
                return $proper_destination;
            }
        }

        return $source;
    }

    /**
     * Zeigt eine auffällige, elegante Admin-Leiste an, wenn ein Update bereitsteht
     */
    public static function render_update_admin_notice() {
        if ( ! current_user_can( 'update_themes' ) ) {
            return;
        }

        $remote_info = self::get_remote_theme_info();
        if ( ! $remote_info || empty( $remote_info['version'] ) ) {
            return;
        }

        $current_version = self::get_installed_version();
        if ( ! version_compare( $current_version, $remote_info['version'], '<' ) ) {
            return;
        }

        $slug       = self::get_theme_slug();
        $update_url = wp_nonce_url(
            admin_url( 'update.php?action=upgrade-theme&theme=' . urlencode( $slug ) ),
            'upgrade-theme_' . $slug
        );
        ?>
        <div class="notice notice-warning is-dismissible" style="border-left-color: #fb6006; padding: 14px 18px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); margin-top: 15px;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div>
                    <strong style="font-size: 15px; color: #05295d; display: flex; align-items: center; gap: 6px;">
                        <span>🚀</span> FindeWerkstatt.de Tema Güncellemesi Mevcut!
                    </strong>
                    <p style="margin: 4px 0 0 0; color: #475569; font-size: 13.5px;">
                        GitHub üzerinde yeni bir sürüm yayınlandı: 
                        <span style="background: #eff6ff; color: #1e40af; padding: 2px 6px; border-radius: 4px; font-weight: 600;">v<?php echo esc_html( $remote_info['version'] ); ?></span> 
                        (Mevcut: <code>v<?php echo esc_html( $current_version ); ?></code>).
                    </p>
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <a href="<?php echo esc_url( $update_url ); ?>" class="button button-primary" style="background: #fb6006; border-color: #ea580c; font-weight: 600; padding: 4px 16px; height: auto;">
                        ⚡ Şimdi Otomatik Güncelle
                    </a>
                    <a href="<?php echo esc_url( $remote_info['url'] ); ?>" target="_blank" class="button" style="color: #64748b;">
                        GitHub Değişiklikleri
                    </a>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Manuelle Prüfung per URL-Parameter: wp-admin/?fw_check_update=1 oder wp-admin/themes.php?fw_check_update=1
     */
    public static function handle_manual_check() {
        if ( isset( $_GET['fw_check_update'] ) && current_user_can( 'update_themes' ) ) {
            delete_transient( self::TRANSIENT_KEY );
            delete_site_transient( 'update_themes' );
            self::get_remote_theme_info( true );
            wp_safe_redirect( admin_url( 'themes.php' ) );
            exit;
        }
    }

    /**
     * Cache nach abgeschlossenem Theme-Update leeren
     */
    public static function clear_cache_after_update( $upgrader, $options ) {
        if ( isset( $options['type'], $options['action'] ) && 'theme' === $options['type'] && 'update' === $options['action'] ) {
            delete_transient( self::TRANSIENT_KEY );
            delete_site_transient( 'update_themes' );
        }
    }
}

// Initialisieren
if ( function_exists( 'add_action' ) ) {
    FindeWerkstatt_Theme_Updater::init();
}

