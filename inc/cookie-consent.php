<?php
/**
 * FindeWerkstatt.de — DSGVO / GDPR Konformer Cookie-Consent-Banner
 * 
 * Leichtgewichtiger, DSGVO- und TTDSG-konformer Cookie-Banner ohne externe Abhängigkeiten.
 * Speichert Auswahl in localStorage ('fw_cookie_consent': 'all' | 'essential').
 * 
 * @package FindeWerkstatt
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Cookie-Banner im Footer ausgeben.
 */
add_action( 'wp_footer', function () {
    $settings = function_exists( 'findewerkstatt_get_seo_geo_llm_settings' ) ? findewerkstatt_get_seo_geo_llm_settings() : array();
    if ( ( $settings['cookie_banner_enabled'] ?? 'yes' ) !== 'yes' ) {
        return;
    }

    $ga4_id = esc_attr( trim( $settings['analytics_ga4_id'] ?? '' ) );
    $pixel_id = esc_attr( trim( $settings['analytics_meta_pixel_id'] ?? '' ) );
    $privacy_url = function_exists( 'findewerkstatt_page_url' ) ? findewerkstatt_page_url( 'datenschutz' ) : home_url( '/datenschutz/' );
    $imprint_url = function_exists( 'findewerkstatt_page_url' ) ? findewerkstatt_page_url( 'impressum' ) : home_url( '/impressum/' );
    ?>
    <div id="fw-cookie-banner" class="fw-cookie-banner" style="display:none;" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Cookie-Einstellungen' ) ); ?>">
        <div class="fw-cookie-inner">
            <div class="fw-cookie-content">
                <div class="fw-cookie-title">
                    <span class="fw-cookie-icon" aria-hidden="true">🍪</span>
                    <strong><?php echo esc_html( findewerkstatt_t( 'Privatsphäre & Cookie-Einstellungen' ) ); ?></strong>
                </div>
                <p class="fw-cookie-text">
                    <?php echo esc_html( findewerkstatt_t( 'Wir verwenden Cookies und Technologien, um Ihnen die bestmögliche Erfahrung bei der Werkstattsuche zu bieten, Inhalte zu personalisieren und die Nutzung unseres Verzeichnisses anonymisiert auszuwerten. Sie können selbst entscheiden, welche Cookies Sie zulassen möchten.' ) ); ?>
                    <a href="<?php echo esc_url( $privacy_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( findewerkstatt_t( 'Datenschutzerklärung' ) ); ?></a> | 
                    <a href="<?php echo esc_url( $imprint_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( findewerkstatt_t( 'Impressum' ) ); ?></a>
                </p>
            </div>
            <div class="fw-cookie-actions">
                <button type="button" id="fw-cookie-accept-all" class="fw-btn fw-btn-cookie-primary">
                    <?php echo esc_html( findewerkstatt_t( 'Alle akzeptieren' ) ); ?>
                </button>
                <button type="button" id="fw-cookie-essential-only" class="fw-btn fw-btn-cookie-secondary">
                    <?php echo esc_html( findewerkstatt_t( 'Nur essenzielle' ) ); ?>
                </button>
            </div>
        </div>
    </div>

    <style>
    .fw-cookie-banner {
        position: fixed;
        bottom: 20px;
        left: 20px;
        right: 20px;
        max-width: 820px;
        margin: 0 auto;
        background: #0f172a;
        color: #f8fafc;
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 16px;
        padding: 20px 24px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45);
        z-index: 999999;
        font-family: inherit;
        animation: fwCookieSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes fwCookieSlideUp {
        from { transform: translateY(40px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    .fw-cookie-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        flex-wrap: wrap;
    }
    .fw-cookie-content {
        flex: 1;
        min-width: 280px;
    }
    .fw-cookie-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 15.5px;
        color: #ffffff;
        margin-bottom: 6px;
    }
    .fw-cookie-icon {
        font-size: 18px;
    }
    .fw-cookie-text {
        font-size: 12.5px;
        line-height: 1.55;
        color: #94a3b8;
        margin: 0;
    }
    .fw-cookie-text a {
        color: #fb6006;
        text-decoration: underline;
        font-weight: 600;
    }
    .fw-cookie-text a:hover {
        color: #ffffff;
    }
    .fw-cookie-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }
    .fw-btn-cookie-primary {
        background: #fb6006;
        color: #ffffff !important;
        font-size: 13.5px;
        font-weight: 700;
        padding: 10px 20px;
        border-radius: 10px;
        border: 0;
        cursor: pointer;
        transition: background 0.15s ease, transform 0.15s ease;
        white-space: nowrap;
    }
    .fw-btn-cookie-primary:hover {
        background: #e05300;
        transform: translateY(-1px);
    }
    .fw-btn-cookie-secondary {
        background: transparent;
        color: #cbd5e1 !important;
        font-size: 13px;
        font-weight: 600;
        padding: 9px 16px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.25);
        cursor: pointer;
        transition: background 0.15s ease, border-color 0.15s ease;
        white-space: nowrap;
    }
    .fw-btn-cookie-secondary:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: #ffffff;
        color: #ffffff !important;
    }
    @media (max-width: 640px) {
        .fw-cookie-banner {
            bottom: 12px;
            left: 12px;
            right: 12px;
            padding: 16px 18px;
        }
        .fw-cookie-actions {
            width: 100%;
            flex-direction: column;
            gap: 8px;
        }
        .fw-btn-cookie-primary, .fw-btn-cookie-secondary {
            width: 100%;
            text-align: center;
            justify-content: center;
        }
    }
    </style>

    <script>
    (function() {
        const STORAGE_KEY = 'fw_cookie_consent';
        const banner = document.getElementById('fw-cookie-banner');
        const acceptAllBtn = document.getElementById('fw-cookie-accept-all');
        const essentialBtn = document.getElementById('fw-cookie-essential-only');
        const ga4Id = <?php echo wp_json_encode( $ga4_id ); ?>;
        const pixelId = <?php echo wp_json_encode( $pixel_id ); ?>;

        function loadAnalytics() {
            if (ga4Id && !window.fwGaLoaded) {
                window.fwGaLoaded = true;
                const gaScript = document.createElement('script');
                gaScript.async = true;
                gaScript.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(ga4Id);
                document.head.appendChild(gaScript);
                window.dataLayer = window.dataLayer || [];
                function gtag(){ dataLayer.push(arguments); }
                gtag('js', new Date());
                gtag('config', ga4Id, { anonymize_ip: true });
            }
            if (pixelId && !window.fwPixelLoaded) {
                window.fwPixelLoaded = true;
                !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
                n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
                document,'script','https://connect.facebook.net/en_US/fbevents.js');
                fbq('init', pixelId);
                fbq('track', 'PageView');
            }
        }

        const savedConsent = localStorage.getItem(STORAGE_KEY);
        if (!savedConsent) {
            banner.style.display = 'block';
        } else if (savedConsent === 'all') {
            loadAnalytics();
        }

        if (acceptAllBtn) {
            acceptAllBtn.addEventListener('click', function() {
                localStorage.setItem(STORAGE_KEY, 'all');
                banner.style.display = 'none';
                loadAnalytics();
            });
        }

        if (essentialBtn) {
            essentialBtn.addEventListener('click', function() {
                localStorage.setItem(STORAGE_KEY, 'essential');
                banner.style.display = 'none';
            });
        }
    })();
    </script>
    <?php
} );
