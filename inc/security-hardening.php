<?php
/**
 * FindeWerkstatt.de — Sicherheitsoptimierung & Corporate Login-Branding
 *
 * 1. Abschaltung von XML-RPC (Pingbacks, Brute-Force-Vektor)
 * 2. Bereinigung von Versionsnummern & Information Disclosure
 * 3. Schutz vor Benutzer-Enumeration (Author Scans & REST API /users)
 * 4. Sicherheits-HTTP-Header (X-Frame-Options, X-Content-Type-Options, etc.)
 * 5. Neutrale Anmeldefehler (verhindert Benutzername-Erkennung)
 * 6. Hochwertiges, responsives Corporate Branding für die WordPress-Anmeldeseite (wp-login.php)
 *
 * @package FindeWerkstatt
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1. XML-RPC vollständig deaktivieren & Pingback-Header entfernen
 */
if ( function_exists( 'add_filter' ) ) {
    add_filter( 'xmlrpc_enabled', '__return_false' );
    add_filter( 'wp_headers', function( $headers ) {
        unset( $headers['X-Pingback'] );
        return $headers;
    } );
}

/**
 * 2. HTTP-Sicherheits-Header setzen
 */
function findewerkstatt_security_headers() {
    if ( headers_sent() ) {
        return;
    }
    header( 'X-Content-Type-Options: nosniff' );
    header( 'X-Frame-Options: SAMEORIGIN' );
    header( 'X-XSS-Protection: 1; mode=block' );
    header( 'Referrer-Policy: strict-origin-when-cross-origin' );
}
if ( function_exists( 'add_action' ) ) {
    add_action( 'send_headers', 'findewerkstatt_security_headers' );
}

/**
 * 3. Benutzer-Enumeration unterbinden
 * - /?author=1 Scans abfangen
 * - REST API /wp/v2/users für unauthentifizierte Anfragen sperren
 */
function findewerkstatt_block_author_enumeration() {
    if ( is_admin() ) {
        return;
    }
    if ( isset( $_REQUEST['author'] ) || ( function_exists( 'is_author' ) && is_author() ) ) {
        wp_safe_redirect( home_url( '/' ), 301 );
        exit;
    }
}
if ( function_exists( 'add_action' ) ) {
    add_action( 'template_redirect', 'findewerkstatt_block_author_enumeration' );
}

if ( function_exists( 'add_filter' ) ) {
    add_filter( 'rest_endpoints', function( $endpoints ) {
        if ( ! current_user_can( 'list_users' ) ) {
            if ( isset( $endpoints['/wp/v2/users'] ) ) {
                unset( $endpoints['/wp/v2/users'] );
            }
            if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
                unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
            }
        }
        return $endpoints;
    } );
}

/**
 * 4. Versions- und Offenlegungs-Bereinigung
 */
if ( function_exists( 'add_filter' ) ) {
    add_filter( 'the_generator', '__return_empty_string' );
}

function findewerkstatt_remove_ver_css_js( $src ) {
    if ( is_string( $src ) && strpos( $src, 'ver=' . get_bloginfo( 'version' ) ) !== false ) {
        $src = remove_query_arg( 'ver', $src );
    }
    return $src;
}
if ( function_exists( 'add_filter' ) ) {
    add_filter( 'style_loader_src', 'findewerkstatt_remove_ver_css_js', 9999 );
    add_filter( 'script_loader_src', 'findewerkstatt_remove_ver_css_js', 9999 );
}

/**
 * 5. Neutrale Anmeldefehler (verhindert User-Enumeration via Login)
 */
if ( function_exists( 'add_filter' ) ) {
    add_filter( 'login_errors', function() {
        return 'Ungültige Anmeldedaten. Bitte überprüfen Sie Ihre Eingaben.';
    } );
}

/**
 * 6. Corporate Branding für wp-login.php
 */
// Logo-Verlinkung auf Startseite
if ( function_exists( 'add_filter' ) ) {
    add_filter( 'login_headerurl', function() {
        return home_url( '/' );
    } );

    // Logo-Titel
    add_filter( 'login_headertext', function() {
        return get_bloginfo( 'name' ) . ' — Kfz-Werkstattverzeichnis Deutschland';
    } );
}

// Styling der Login-Seite
function findewerkstatt_custom_login_styles() {
    $logo_url = function_exists( 'findewerkstatt_brand_asset_url' ) ? findewerkstatt_brand_asset_url( 'logo' ) : '';
    ?>
    <style type="text/css">
        body.login {
            background: linear-gradient(135deg, #05295d 0%, #0a192f 100%) !important;
            color: #f8fafc !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif !important;
            min-height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            position: relative !important;
            padding: 20px 0 !important;
        }

        /* Logo & Brand Heading */
        #login {
            width: 100% !important;
            max-width: 430px !important;
            padding: 24px !important;
            position: relative !important;
            z-index: 1 !important;
            margin: auto !important;
        }

        #login h1 {
            margin-bottom: 22px !important;
            text-align: center !important;
        }

        #login h1 a {
            <?php if ( $logo_url ) : ?>
                background-image: url('<?php echo esc_url( $logo_url ); ?>') !important;
                background-size: contain !important;
                background-repeat: no-repeat !important;
                background-position: center !important;
                width: 280px !important;
                height: 72px !important;
            <?php endif; ?>
            margin: 0 auto !important;
            filter: drop-shadow(0 4px 14px rgba(0, 0, 0, 0.35)) !important;
        }

        /* Form Card */
        .login form {
            background: #ffffff !important;
            border-radius: 20px !important;
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            box-shadow: 0 24px 48px -12px rgba(0, 0, 0, 0.45) !important;
            padding: 34px 30px !important;
        }

        .login form .input {
            background: #f8fafc !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 10px !important;
            padding: 10px 14px !important;
            font-size: 15px !important;
            color: #0f172a !important;
            transition: all 0.2s ease !important;
            box-shadow: none !important;
        }

        .login form .input:focus {
            border-color: #fb6006 !important;
            background: #ffffff !important;
            box-shadow: 0 0 0 3.5px rgba(251, 96, 6, 0.22) !important;
            outline: none !important;
        }

        .login label {
            font-size: 13.5px !important;
            font-weight: 600 !important;
            color: #1e293b !important;
            margin-bottom: 6px !important;
        }

        /* Primary Submit Button */
        .wp-core-ui .button-primary {
            background: #fb6006 !important;
            border: none !important;
            color: #ffffff !important;
            font-size: 15px !important;
            font-weight: 700 !important;
            border-radius: 10px !important;
            height: 46px !important;
            line-height: 46px !important;
            padding: 0 24px !important;
            width: 100% !important;
            margin-top: 18px !important;
            cursor: pointer !important;
            box-shadow: 0 4px 16px rgba(251, 96, 6, 0.35) !important;
            transition: transform 0.15s ease, background 0.15s ease !important;
            text-shadow: none !important;
        }

        .wp-core-ui .button-primary:hover,
        .wp-core-ui .button-primary:focus {
            background: #e05300 !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 20px rgba(251, 96, 6, 0.45) !important;
        }

        /* Checkbox & Forget Me Not */
        .forgetmenot {
            margin-top: 8px !important;
        }
        .forgetmenot label {
            font-weight: 500 !important;
            color: #64748b !important;
            font-size: 13px !important;
        }

        /* Alerts & Notices */
        .login #login_error,
        .login .message,
        .login .success {
            border-radius: 12px !important;
            border-left: 4px solid #fb6006 !important;
            background: #ffffff !important;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2) !important;
            color: #0f172a !important;
            font-size: 13.5px !important;
            padding: 14px 18px !important;
        }

        /* Bottom Links */
        .login #nav,
        .login #backtoblog {
            text-align: center !important;
            padding: 6px 0 !important;
            margin: 12px 0 0 !important;
        }

        .login #nav a,
        .login #backtoblog a {
            color: #94a3b8 !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            transition: color 0.15s ease !important;
            text-decoration: none !important;
        }

        .login #nav a:hover,
        .login #backtoblog a:hover {
            color: #ffffff !important;
            text-decoration: underline !important;
        }

        /* Footer */
        .fw-login-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
        }
        .fw-login-footer a {
            color: #94a3b8;
            text-decoration: none;
            margin: 0 6px;
        }
        .fw-login-footer a:hover {
            color: #ffffff;
            text-decoration: underline;
        }

        /* Language Switcher */
        .language-switcher {
            margin-top: 16px !important;
            text-align: center !important;
        }
        .language-switcher select {
            background: rgba(255, 255, 255, 0.1) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            border-radius: 8px !important;
            padding: 4px 8px !important;
        }
    </style>
    <?php
}
if ( function_exists( 'add_action' ) ) {
    add_action( 'login_head', 'findewerkstatt_custom_login_styles' );
}

/**
 * Zusätzliche rechtliche Fußzeilen-Links auf der Login-Seite
 */
function findewerkstatt_login_footer_links() {
    $imprint_url = function_exists( 'findewerkstatt_page_url' ) ? findewerkstatt_page_url( 'impressum' ) : home_url( '/impressum/' );
    $privacy_url = function_exists( 'findewerkstatt_page_url' ) ? findewerkstatt_page_url( 'datenschutz' ) : home_url( '/datenschutz/' );
    ?>
    <div class="fw-login-footer">
        <a href="<?php echo esc_url( $privacy_url ); ?>" target="_blank">Datenschutzerklärung</a> • 
        <a href="<?php echo esc_url( $imprint_url ); ?>" target="_blank">Impressum</a>
        <div style="margin-top:8px; opacity:0.8;">&copy; <?php echo esc_html( date( 'Y' ) ); ?> FindeWerkstatt.de — Alle Rechte vorbehalten.</div>
    </div>
    <?php
}
if ( function_exists( 'add_action' ) ) {
    add_action( 'login_footer', 'findewerkstatt_login_footer_links' );
}
