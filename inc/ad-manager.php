<?php
/**
 * Ad Management System: Google AdSense, Direct Company/Sponsor Ads, and Hybrid Monetezation.
 *
 * Supports Google AdSense Auto Ads, Responsive Ad Units, Direct Automotive Sponsors,
 * and In-Feed Directory Ads with an intuitive WordPress Admin interface.
 *
 * @package FindeWerkstatt
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Retrieve ad settings with sensible defaults.
 */
function findewerkstatt_get_ad_settings() {
    $defaults = array(
        'google_adsense_client' => '',
        'google_auto_ads'       => 'yes',
        'header_ad_code'        => '',
        'ads_test_mode'         => 'no',
        'ads_txt'               => '',
        'slots'                 => array(
            'top_banner'      => array(
                'mode'           => 'hybrid',
                'adsense_slot'   => '',
                'custom_code'    => '',
                'direct_title'   => 'Kfz-Versicherungen & Inspektionsangebote vergleichen',
                'direct_desc'    => 'Finden Sie günstige Tarife und Gutscheine in Ihrer Nähe.',
                'direct_link'    => '',
                'direct_cta'     => 'Angebote ansehen',
                'direct_image'   => '',
            ),
            'incontent_video' => array(
                'mode'           => 'hybrid',
                'adsense_slot'   => '',
                'custom_code'    => '',
                'direct_title'   => 'Jetzt Reifenwechsel & Hauptuntersuchung zum Sparpreis buchen',
                'direct_desc'    => 'Exklusive Rabatte bei teilnehmenden Partnerbetrieben in Sachsen-Anhalt und bundesweit.',
                'direct_link'    => '',
                'direct_cta'     => 'Video ansehen & Rabatt sichern',
                'direct_video'   => '',
            ),
            'sidebar_sticky'  => array(
                'mode'           => 'hybrid',
                'adsense_slot'   => '',
                'custom_code'    => '',
                'direct_title'   => 'Günstige Ersatzteile & Reifen',
                'direct_desc'    => 'Bis zu 30% sparen bei unseren Partnern für Kfz-Teile und Autozubehör.',
                'direct_link'    => '',
                'direct_cta'     => 'Angebote ansehen',
                'direct_image'   => '',
            ),
            'archive_feed'    => array(
                'mode'           => 'hybrid',
                'adsense_slot'   => '',
                'custom_code'    => '',
                'direct_title'   => 'Kfz-Betrieb eintragen & Neukunden gewinnen',
                'direct_desc'    => 'Präsentieren Sie Ihre Werkstatt regionalen Autofahrern in ganz Deutschland.',
                'direct_link'    => '',
                'direct_cta'     => 'Werkstatt anmelden',
                'direct_image'   => '',
            ),
        ),
    );

    $saved = get_option( 'findewerkstatt_ad_settings', array() );
    if ( ! is_array( $saved ) ) {
        $saved = array();
    }

    $settings = wp_parse_args( $saved, $defaults );
    if ( ! isset( $settings['slots'] ) || ! is_array( $settings['slots'] ) ) {
        $settings['slots'] = $defaults['slots'];
    } else {
        foreach ( $defaults['slots'] as $slot_key => $slot_defaults ) {
            $settings['slots'][ $slot_key ] = wp_parse_args(
                is_array( $settings['slots'][ $slot_key ] ?? null ) ? $settings['slots'][ $slot_key ] : array(),
                $slot_defaults
            );
        }
    }

    return $settings;
}

/**
 * Sanitize publisher client ID (format: ca-pub-XXXXXXXXXXXXXXXX).
 * Handles ca-pub-, pub-, raw digits, and full script tags.
 */
function findewerkstatt_sanitize_adsense_client( $client ) {
    $client = trim( (string) $client );
    if ( '' === $client ) {
        return '';
    }

    // 1. Matched ca-pub-XXXXXXXXXXXXXXXX (from script, raw string, etc.)
    if ( preg_match( '/ca-pub-(\d{10,20})/i', $client, $matches ) ) {
        return 'ca-pub-' . $matches[1];
    }

    // 2. Matched pub-XXXXXXXXXXXXXXXX
    if ( preg_match( '/pub-(\d{10,20})/i', $client, $matches ) ) {
        return 'ca-pub-' . $matches[1];
    }

    // 3. Matched pure digit string
    if ( preg_match( '/\b(\d{10,20})\b/', $client, $matches ) ) {
        return 'ca-pub-' . $matches[1];
    }

    return '';
}

/**
 * Sanitize AdSense slot ID.
 * Handles pure numbers, quotes, or extracted from <ins data-ad-slot="...">.
 */
function findewerkstatt_sanitize_adsense_slot( $slot ) {
    $slot = trim( (string) $slot );
    if ( '' === $slot ) {
        return '';
    }

    // 1. Extracted from data-ad-slot attribute
    if ( preg_match( '/data-ad-slot=["\']?(\d{6,20})["\']?/i', $slot, $matches ) ) {
        return $matches[1];
    }

    // 2. Pure digits (slot IDs are typically 8-12 digits)
    if ( preg_match( '/\b(\d{6,20})\b/', $slot, $matches ) ) {
        return $matches[1];
    }

    return '';
}

/**
 * Sanitize custom HTML/JS ad code preserving AdSense scripts and ins tags.
 */
function findewerkstatt_sanitize_ad_code( $code ) {
    $code = trim( (string) $code );
    if ( '' === $code ) {
        return '';
    }

    if ( current_user_can( 'unfiltered_html' ) ) {
        return $code;
    }

    $allowed = wp_kses_allowed_html( 'post' );
    $allowed['script'] = array(
        'async'       => true,
        'src'         => true,
        'crossorigin' => true,
        'type'        => true,
    );
    $allowed['ins'] = array(
        'class'                      => true,
        'style'                      => true,
        'data-ad-client'             => true,
        'data-ad-slot'               => true,
        'data-ad-format'             => true,
        'data-full-width-responsive' => true,
        'data-ad-layout'             => true,
        'data-ad-layout-key'         => true,
    );
    return wp_kses( $code, $allowed );
}

/**
 * Output Google AdSense verification meta, client script & custom head code in <head>.
 */
function findewerkstatt_ad_head_scripts() {
    $settings    = findewerkstatt_get_ad_settings();
    $client      = findewerkstatt_sanitize_adsense_client( $settings['google_adsense_client'] ?? '' );
    $header_code = trim( (string) ( $settings['header_ad_code'] ?? '' ) );

    if ( ! $client && ! $header_code ) {
        return;
    }

    echo "\n<!-- Google AdSense - FindeWerkstatt.de -->\n";
    if ( $client ) {
        // Modern ownership verification tag required by Google AdSense
        echo '<meta name="google-adsense-account" content="' . esc_attr( $client ) . '">' . "\n";
        // Asynchronous client script (Auto Ads & responsive units)
        echo '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . esc_attr( $client ) . '" crossorigin="anonymous"></script>' . "\n";
    }

    if ( $header_code ) {
        echo $header_code . "\n";
    }
}
add_action( 'wp_head', 'findewerkstatt_ad_head_scripts', 3 );

/**
 * Render dynamic ad unit based on slot configuration.
 *
 * @param string $slot_key Slot identifier (top_banner, incontent_video, sidebar_sticky, archive_feed).
 * @param array  $custom_args Optional overrides.
 * @return string HTML content of ad unit.
 */
function findewerkstatt_render_ad( $slot_key, $custom_args = array() ) {
    $settings = findewerkstatt_get_ad_settings();
    $slot = $settings['slots'][ $slot_key ] ?? null;
    if ( ! $slot || ( $slot['mode'] ?? 'hybrid' ) === 'off' ) {
        return '';
    }

    $mode        = $slot['mode'] ?? 'hybrid';
    $client      = findewerkstatt_sanitize_adsense_client( $settings['google_adsense_client'] ?? '' );
    $slot_id     = findewerkstatt_sanitize_adsense_slot( $slot['adsense_slot'] ?? '' );
    $custom_code = trim( (string) ( $slot['custom_code'] ?? '' ) );
    $has_custom  = ! empty( $custom_code );
    $has_adsense = ( $client && ( ! empty( $slot_id ) || 'yes' === ( $settings['google_auto_ads'] ?? 'no' ) ) );
    $has_direct  = ! empty( $slot['direct_link'] ) || ! empty( $slot['direct_image'] );
    $is_test     = ( 'yes' === ( $settings['ads_test_mode'] ?? 'no' ) );

    // Decide which format to render
    $render_type = 'fallback';
    if ( 'custom' === $mode && $has_custom ) {
        $render_type = 'custom';
    } elseif ( 'adsense' === $mode ) {
        if ( $has_custom ) {
            $render_type = 'custom';
        } elseif ( $has_adsense ) {
            $render_type = 'adsense';
        } else {
            $render_type = 'fallback';
        }
    } elseif ( 'direct' === $mode && $has_direct ) {
        $render_type = 'direct';
    } elseif ( 'hybrid' === $mode ) {
        if ( $has_custom ) {
            $render_type = 'custom';
        } elseif ( $has_direct ) {
            $render_type = 'direct';
        } elseif ( $has_adsense ) {
            $render_type = 'adsense';
        } else {
            $render_type = 'fallback';
        }
    }

    ob_start();

    // 0. Custom Raw Ad Code (HTML / JS / AdSense unit code)
    if ( 'custom' === $render_type ) : ?>
        <div class="fw-ad-zone fw-ad-zone-<?php echo esc_attr( $slot_key ); ?>" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Gesponserte Anzeige' ) ); ?>">
            <div class="fw-ad-header">
                <span class="fw-ad-label"><?php echo esc_html( findewerkstatt_t( 'Gesponserte Anzeige' ) ); ?></span>
                <?php if ( $is_test ) : ?><span style="background:#fef08a;color:#854d0e;padding:1px 6px;border-radius:4px;font-size:10px;font-weight:700;margin-left:6px;">TEST MODU: ÖZEL KOD</span><?php endif; ?>
            </div>
            <div class="fw-ad-slot fw-ad-slot-custom">
                <?php echo $custom_code; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        </div>
    <?php
    // 1. Google AdSense Unit
    elseif ( 'adsense' === $render_type ) : ?>
        <div class="fw-ad-zone fw-ad-zone-<?php echo esc_attr( $slot_key ); ?>" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Gesponserte Anzeige' ) ); ?>">
            <div class="fw-ad-header">
                <span class="fw-ad-label"><?php echo esc_html( findewerkstatt_t( 'Gesponserte Anzeige' ) ); ?></span>
                <?php if ( $is_test ) : ?><span style="background:#dbeafe;color:#1e40af;padding:1px 6px;border-radius:4px;font-size:10px;font-weight:700;margin-left:6px;">TEST MODU: ADSENSE (<?php echo esc_html( $slot_id ? 'Slot: ' . $slot_id : 'Oto-Format' ); ?>)</span><?php endif; ?>
            </div>
            <div class="fw-ad-slot fw-ad-slot-adsense <?php echo 'sidebar_sticky' === $slot_key ? 'fw-ad-slot-adsense-sidebar' : ''; ?>">
                <ins class="adsbygoogle"
                     style="display:block"
                     data-ad-client="<?php echo esc_attr( $client ); ?>"
                     <?php if ( $slot_id ) : ?>data-ad-slot="<?php echo esc_attr( $slot_id ); ?>"<?php endif; ?>
                     data-ad-format="auto"
                     data-full-width-responsive="true"></ins>
                <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
            </div>
        </div>
    <?php
    // 2. Direct Automotive Firm Sponsor
    elseif ( 'direct' === $render_type ) :
        $title = $slot['direct_title'] ?: findewerkstatt_t( 'Partnerangebote & Kfz-Spezialisten' );
        $desc = $slot['direct_desc'] ?: '';
        $link = $slot['direct_link'] ?: findewerkstatt_page_url( 'kontakt' );
        $cta = $slot['direct_cta'] ?: findewerkstatt_t( 'Angebote ansehen' );
        $image = $slot['direct_image'] ?? '';
        $video = $slot['direct_video'] ?? '';
        ?>
        <div class="fw-ad-zone fw-ad-zone-<?php echo esc_attr( $slot_key ); ?>" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Gesponserte Anzeige' ) ); ?>">
            <div class="fw-ad-header">
                <span class="fw-ad-label"><?php echo esc_html( findewerkstatt_t( 'Gesponserte Anzeige' ) ); ?></span>
            </div>
            <?php if ( 'incontent_video' === $slot_key ) : ?>
                <div class="fw-ad-slot fw-ad-slot-video">
                    <div class="fw-ad-video-card">
                        <div class="fw-ad-video-preview">
                            <?php if ( $image ) : ?>
                                <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="fw-ad-video-bg" loading="lazy">
                            <?php endif; ?>
                            <a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="sponsored nofollow noopener" class="fw-ad-video-play" aria-label="<?php echo esc_attr( $title ); ?>">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                            </a>
                            <span class="fw-ad-video-time">0:30</span>
                        </div>
                        <div class="fw-ad-video-content">
                            <h4><a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="sponsored nofollow noopener" style="color:inherit;text-decoration:none;"><?php echo esc_html( $title ); ?></a></h4>
                            <?php if ( $desc ) : ?><p><?php echo esc_html( $desc ); ?></p><?php endif; ?>
                            <a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="sponsored nofollow noopener" class="fw-btn fw-btn-primary fw-btn-sm fw-ad-video-btn"><?php echo esc_html( $cta ); ?> ↗</a>
                        </div>
                    </div>
                </div>
            <?php elseif ( 'sidebar_sticky' === $slot_key ) : ?>
                <div class="fw-ad-slot fw-ad-slot-rectangle">
                    <div class="fw-ad-placeholder fw-ad-placeholder-sidebar">
                        <div class="fw-ad-tag"><?php echo esc_html( findewerkstatt_t( 'Sponsor' ) ); ?></div>
                        <?php if ( $image ) : ?>
                            <a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="sponsored nofollow noopener" style="display:block;margin-bottom:12px;">
                                <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" style="max-width:100%;border-radius:10px;" loading="lazy">
                            </a>
                        <?php endif; ?>
                        <h4><?php echo esc_html( $title ); ?></h4>
                        <?php if ( $desc ) : ?><p><?php echo esc_html( $desc ); ?></p><?php endif; ?>
                        <a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="sponsored nofollow noopener" class="fw-btn fw-btn-primary fw-btn-sm fw-ad-btn"><?php echo esc_html( $cta ); ?> &rarr;</a>
                    </div>
                </div>
            <?php else : ?>
                <div class="fw-ad-slot fw-ad-slot-leaderboard">
                    <div class="fw-ad-placeholder">
                        <div class="fw-ad-tag"><?php echo esc_html( findewerkstatt_t( 'Sponsor' ) ); ?></div>
                        <div class="fw-ad-info">
                            <strong><?php echo esc_html( $title ); ?></strong>
                            <?php if ( $desc ) : ?><p><?php echo esc_html( $desc ); ?></p><?php endif; ?>
                        </div>
                        <a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="sponsored nofollow noopener" class="fw-btn fw-btn-sm fw-btn-primary fw-ad-action-btn"><?php echo esc_html( $cta ); ?> &rarr;</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php
    // 3. Fallback / "Hier werben" Lead Generator (Monetization Placeholder)
    else :
        $contact_url = add_query_arg( 'anfrage', 'werbung', findewerkstatt_page_url( 'kontakt' ) );
        ?>
        <div class="fw-ad-zone fw-ad-zone-<?php echo esc_attr( $slot_key ); ?>" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Gesponserte Anzeige' ) ); ?>">
            <div class="fw-ad-header">
                <span class="fw-ad-label"><?php echo esc_html( findewerkstatt_t( 'Gesponserte Anzeige' ) ); ?></span>
            </div>
            <?php if ( 'incontent_video' === $slot_key ) : ?>
                <div class="fw-ad-slot fw-ad-slot-video">
                    <div class="fw-ad-video-card">
                        <div class="fw-ad-video-preview">
                            <div class="fw-ad-video-play" aria-hidden="true">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                            </div>
                            <span class="fw-ad-video-time">0:30</span>
                        </div>
                        <div class="fw-ad-video-content">
                            <h4><?php echo esc_html( findewerkstatt_t( 'Jetzt Reifenwechsel & Hauptuntersuchung zum Sparpreis buchen' ) ); ?></h4>
                            <p><?php echo esc_html( findewerkstatt_t( 'Exklusive Rabatte bei teilnehmenden Partnerbetrieben in Sachsen-Anhalt und bundesweit.' ) ); ?></p>
                            <a href="<?php echo esc_url( $contact_url ); ?>" class="fw-btn fw-btn-primary fw-btn-sm fw-ad-video-btn"><?php echo esc_html( findewerkstatt_t( 'Video ansehen & Rabatt sichern' ) ); ?> ↗</a>
                        </div>
                    </div>
                </div>
            <?php elseif ( 'sidebar_sticky' === $slot_key ) : ?>
                <div class="fw-ad-slot fw-ad-slot-rectangle">
                    <div class="fw-ad-placeholder fw-ad-placeholder-sidebar">
                        <div class="fw-ad-tag"><?php echo esc_html( findewerkstatt_t( 'SPONSOR' ) ); ?></div>
                        <h4><?php echo esc_html( findewerkstatt_t( 'Günstige Ersatzteile & Reifen' ) ); ?></h4>
                        <p><?php echo esc_html( findewerkstatt_t( 'Bis zu 30% sparen bei unseren Partnern für Kfz-Teile und Autozubehör.' ) ); ?></p>
                        <a href="<?php echo esc_url( $contact_url ); ?>" class="fw-btn fw-btn-primary fw-btn-sm fw-ad-btn"><?php echo esc_html( findewerkstatt_t( 'Angebote ansehen' ) ); ?> &rarr;</a>
                    </div>
                </div>
            <?php elseif ( 'archive_feed' === $slot_key ) : ?>
                <div class="fw-ad-slot fw-ad-slot-feed" style="grid-column: 1 / -1; margin: 10px 0 16px;">
                    <div class="fw-ad-slot-leaderboard">
                        <div class="fw-ad-placeholder">
                            <div class="fw-ad-tag"><?php echo esc_html( findewerkstatt_t( 'PARTNER' ) ); ?></div>
                            <div class="fw-ad-info">
                                <strong><?php echo esc_html( findewerkstatt_t( 'Kfz-Betrieb eintragen & Neukunden gewinnen' ) ); ?></strong>
                                <p><?php echo esc_html( findewerkstatt_t( 'Präsentieren Sie Ihre Werkstatt regionalen Autofahrern in ganz Deutschland.' ) ); ?></p>
                            </div>
                            <a href="<?php echo esc_url( findewerkstatt_page_url( 'werkstatt-anmelden' ) ); ?>" class="fw-btn fw-btn-sm fw-btn-primary fw-ad-action-btn"><?php echo esc_html( findewerkstatt_t( 'Werkstatt anmelden' ) ); ?> &rarr;</a>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="fw-ad-slot fw-ad-slot-leaderboard">
                    <div class="fw-ad-placeholder">
                        <div class="fw-ad-tag"><?php echo esc_html( findewerkstatt_t( 'Anzeige' ) ); ?></div>
                        <div class="fw-ad-info">
                            <strong><?php echo esc_html( findewerkstatt_t( 'Kfz-Versicherungen & Inspektionsangebote vergleichen' ) ); ?></strong>
                            <p><?php echo esc_html( findewerkstatt_t( 'Finden Sie günstige Tarife und Gutscheine in Ihrer Nähe.' ) ); ?></p>
                        </div>
                        <a href="<?php echo esc_url( $contact_url ); ?>" class="fw-btn fw-btn-sm fw-btn-primary fw-ad-action-btn"><?php echo esc_html( findewerkstatt_t( 'Angebote ansehen' ) ); ?> &rarr;</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php
    endif;

    return ob_get_clean();
}

/**
 * Filter ads.txt output if configured in settings.
 */
add_filter( 'findewerkstatt_ads_txt_content', function ( $default_content ) {
    $settings = findewerkstatt_get_ad_settings();
    $custom = trim( (string) ( $settings['ads_txt'] ?? '' ) );
    if ( $custom ) {
        return $custom . "\n";
    }
    $client = findewerkstatt_sanitize_adsense_client( $settings['google_adsense_client'] ?? '' );
    if ( $client ) {
        $pub_id = str_replace( 'ca-', '', $client );
        return "google.com, " . $pub_id . ", DIRECT, f08c47fec0942fa0\n";
    }
    return $default_content;
} );

/**
 * Register settings and create WP-Admin ad management page.
 */
function findewerkstatt_ad_admin_init() {
    register_setting( 'findewerkstatt_ads_group', 'findewerkstatt_ad_settings', array(
        'type'              => 'array',
        'sanitize_callback' => 'findewerkstatt_sanitize_ad_settings',
    ) );
}
add_action( 'admin_init', 'findewerkstatt_ad_admin_init' );

function findewerkstatt_sanitize_ad_settings( $input ) {
    if ( ! is_array( $input ) ) {
        return array();
    }

    $clean = array();
    $clean['google_adsense_client'] = findewerkstatt_sanitize_adsense_client( $input['google_adsense_client'] ?? '' );
    $clean['google_auto_ads']       = ! empty( $input['google_auto_ads'] ) && 'yes' === $input['google_auto_ads'] ? 'yes' : 'no';
    $clean['header_ad_code']        = findewerkstatt_sanitize_ad_code( $input['header_ad_code'] ?? '' );
    $clean['ads_test_mode']         = ! empty( $input['ads_test_mode'] ) && 'yes' === $input['ads_test_mode'] ? 'yes' : 'no';
    $clean['ads_txt']               = sanitize_textarea_field( $input['ads_txt'] ?? '' );

    $clean['slots'] = array();
    $allowed_modes  = array( 'hybrid', 'adsense', 'custom', 'direct', 'off' );

    $raw_slots = is_array( $input['slots'] ?? null ) ? $input['slots'] : array();
    foreach ( array( 'top_banner', 'incontent_video', 'sidebar_sticky', 'archive_feed' ) as $key ) {
        $raw = is_array( $raw_slots[ $key ] ?? null ) ? $raw_slots[ $key ] : array();
        $mode = in_array( $raw['mode'] ?? 'hybrid', $allowed_modes, true ) ? $raw['mode'] : 'hybrid';
        $clean['slots'][ $key ] = array(
            'mode'           => $mode,
            'adsense_slot'   => findewerkstatt_sanitize_adsense_slot( $raw['adsense_slot'] ?? '' ),
            'custom_code'    => findewerkstatt_sanitize_ad_code( $raw['custom_code'] ?? '' ),
            'direct_title'   => sanitize_text_field( $raw['direct_title'] ?? '' ),
            'direct_desc'    => sanitize_text_field( $raw['direct_desc'] ?? '' ),
            'direct_link'    => esc_url_raw( $raw['direct_link'] ?? '' ),
            'direct_cta'     => sanitize_text_field( $raw['direct_cta'] ?? '' ),
            'direct_image'   => esc_url_raw( $raw['direct_image'] ?? '' ),
            'direct_video'   => esc_url_raw( $raw['direct_video'] ?? '' ),
        );
    }

    return $clean;
}

/**
 * Alias for theme templates calling findewerkstatt_render_ad_placement.
 */
if ( ! function_exists( 'findewerkstatt_render_ad_placement' ) ) {
    function findewerkstatt_render_ad_placement( $slot_key, $custom_args = array() ) {
        echo findewerkstatt_render_ad( $slot_key, $custom_args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}

/**
 * Add WP-Admin menu entry for Ad Management.
 */
function findewerkstatt_ad_admin_menu() {
    add_menu_page(
        'Reklam Yönetimi',
        'Reklamlar (Ads)',
        'manage_options',
        'findewerkstatt-ads',
        'findewerkstatt_ad_admin_page',
        'dashicons-megaphone',
        59
    );
}
add_action( 'admin_menu', 'findewerkstatt_ad_admin_menu' );

/**
 * Render Ad Management Screen in WP Admin.
 */
function findewerkstatt_ad_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $settings = findewerkstatt_get_ad_settings();
    $client   = findewerkstatt_sanitize_adsense_client( $settings['google_adsense_client'] ?? '' );
    ?>
    <div class="wrap" style="max-width: 1080px;">
        <h1 style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
            <span class="dashicons dashicons-megaphone" style="font-size:32px;width:32px;height:32px;color:#fb6006;"></span>
            Reklam & Gelir Yönetimi (Google AdSense & Firma Sponsorlukları)
        </h1>
        <p class="description" style="font-size:14px;margin-bottom:20px;">
            Sitenizdeki <strong>Google AdSense</strong> kodlarını, otomatik reklamları ve <strong>özel reklam bannerlarını / HTML kodlarını</strong> bu ekrandan yönetebilirsiniz.
        </p>

        <?php settings_errors(); ?>

        <!-- CANLI DURUM BİLGİLENDİRME ROZETİ -->
        <?php if ( $client ) : ?>
            <div style="background:#ecfdf5;border:1px solid #10b981;border-radius:10px;padding:14px 18px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <span style="font-size:24px;">✅</span>
                    <div>
                        <strong style="color:#065f46;font-size:14.5px;">Google AdSense Entegrasyonu Aktif: <code><?php echo esc_html( $client ); ?></code></strong>
                        <p style="margin:2px 0 0;color:#047857;font-size:13px;">
                            Site sahiplik doğrulama meta etiketi ve AdSense kütüphanesi sitenizin <code>&lt;head&gt;</code> bölümüne başarıyla entegre edilmiştir.
                        </p>
                    </div>
                </div>
                <a href="<?php echo esc_url( home_url( '/ads.txt' ) ); ?>" target="_blank" class="button" style="color:#065f46;border-color:#10b981;">
                    📄 Canlı ads.txt Dosyasını Aç ↗
                </a>
            </div>
        <?php else : ?>
            <div style="background:#fffbeb;border:1px solid #f59e0b;border-radius:10px;padding:14px 18px;margin-bottom:24px;display:flex;align-items:center;gap:10px;">
                <span style="font-size:24px;">⚠️</span>
                <div>
                    <strong style="color:#92400e;font-size:14.5px;">AdSense Henüz Tanımlanmadı</strong>
                    <p style="margin:2px 0 0;color:#b45309;font-size:13px;">
                        Google AdSense yayıncı kimliğinizi (<code>pub-XXXXXXXXXXXXX</code> veya <code>ca-pub-XXXXXXXXXXXXX</code>) veya Google'dan kopyaladığınız script kodunu aşağıdaki alana girip kaydedin.
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <form method="post" action="options.php">
            <?php settings_fields( 'findewerkstatt_ads_group' ); ?>

            <!-- KART 1: Google AdSense Genel Ayarları -->
            <div class="postbox" style="padding:22px;border-radius:12px;box-shadow:0 4px 14px rgba(0,0,0,0.06);margin-bottom:24px;">
                <h2 style="margin-top:0;font-size:18px;display:flex;align-items:center;gap:8px;">
                    <span class="dashicons dashicons-google" style="color:#0284c7;"></span>
                    1. Google AdSense Hesap Entegrasyonu
                </h2>
                <p style="color:#475569;margin-bottom:16px;">
                    Google AdSense hesabınızdaki yayıncı numaranızı veya Google'ın size verdiği <code>&lt;script&gt;</code> kodunu buraya yapıştırmanız yeterlidir. Sistem kimliği otomatik olarak algılar ve sitenizin <code>&lt;head&gt;</code> alanına yazar.
                </p>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="fw-adsense-client">Google AdSense Yayıncı Kimliği (Publisher ID)</label></th>
                        <td>
                            <input type="text" id="fw-adsense-client" name="findewerkstatt_ad_settings[google_adsense_client]" value="<?php echo esc_attr( $settings['google_adsense_client'] ); ?>" class="large-text" placeholder="pub-7207931778635058 veya ca-pub-7207931778635058 veya <script...></script>">
                            <p class="description">
                                Örnek: <code>pub-7207931778635058</code> veya <code>ca-pub-7207931778635058</code>. Tam script kodunu yapıştırsanız bile sistem kimliğinizi otomatik tespit eder.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Otomatik Reklamlar (Auto Ads)</th>
                        <td>
                            <label>
                                <input type="checkbox" name="findewerkstatt_ad_settings[google_auto_ads]" value="yes" <?php checked( $settings['google_auto_ads'], 'yes' ); ?>>
                                <strong>Google Otomatik Reklamlarını (Auto Ads) etkinleştir.</strong>
                            </label>
                            <p class="description">Google'ın sayfa içerisinde en uygun yerlere otomatik olarak reklam yerleştirmesine izin verir (Önerilir).</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="fw-header-ad-code">Özel Header / Doğrulama Kodu (Opsiyonel)</label></th>
                        <td>
                            <textarea id="fw-header-ad-code" name="findewerkstatt_ad_settings[header_ad_code]" rows="3" class="large-text code" placeholder="&lt;meta name=&quot;google-adsense-account&quot; content=&quot;ca-pub-...&quot;&gt; veya ek AdSense scriptleri"><?php echo esc_textarea( $settings['header_ad_code'] ?? '' ); ?></textarea>
                            <p class="description">Google AdSense veya diğer reklam ağlarından <code>&lt;head&gt;</code> içine eklemeniz istenen ekstra kodlar varsa buraya ekleyebilirsiniz.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Geliştirici / Test Modu</th>
                        <td>
                            <label>
                                <input type="checkbox" name="findewerkstatt_ad_settings[ads_test_mode]" value="yes" <?php checked( $settings['ads_test_mode'] ?? 'no', 'yes' ); ?>>
                                Reklam alanlarını sayfada renkli etiketlerle vurgula (Test Modu).
                            </label>
                            <p class="description">Google AdSense sitenizi henüz onaylamadıysa ve reklam alanlarının sayfada nereye oturduğunu görmek istiyorsanız bu seçeneği açabilirsiniz.</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- KART 2: Reklam Alanları & Firma Sponsorlukları -->
            <div class="postbox" style="padding:22px;border-radius:12px;box-shadow:0 4px 14px rgba(0,0,0,0.06);margin-bottom:24px;">
                <h2 style="margin-top:0;font-size:18px;display:flex;align-items:center;gap:8px;">
                    <span class="dashicons dashicons-layout" style="color:#10b981;"></span>
                    2. Sayfa İçi Reklam Alanları (Profil, Blog & Dizin)
                </h2>
                <p style="color:#475569;margin-bottom:20px;">
                    Her alan için çalışma modunu belirleyebilir, ister sadece Google AdSense'ten aldığınız <strong>Slot ID</strong>'yi girebilir, ister doğrudan Google'dan kopyaladığınız <strong>tam reklam kodunu (HTML/Script)</strong> yapıştırabilirsiniz.
                </p>

                <?php
                $slot_labels = array(
                    'top_banner'      => array( 'title' => 'Üst Banner Reklamı (Profil & Blog Sayfası)', 'desc' => 'Atölye profilinde ve blog yazılarında başlığın hemen altında, içeriklerden önce yer alır.' ),
                    'incontent_video' => array( 'title' => 'İçerik İçi Reklam / Video Alanı', 'desc' => 'Hizmetler ve markalar bittikten sonra, kullanıcının iletişim bilgilerine ulaşmadan hemen önce izleyeceği / tıklayacağı reklam alanı.' ),
                    'sidebar_sticky'  => array( 'title' => 'Sağ Kenar Çubuğu Sabit Reklamı (Sticky Sidebar)', 'desc' => 'Masaüstünde sayfa boyunca kayarken ekranda sabit kalan kare/dikdörtgen reklam alanı.' ),
                    'archive_feed'    => array( 'title' => 'Dizin / Arama İçi Banner (Werkstätten & Blog Listesi)', 'desc' => 'Firma listeleme sayfasında kartların arasında yer alan sponsor/AdSense alanı.' ),
                );

                foreach ( $slot_labels as $s_key => $s_info ) :
                    $s_data = $settings['slots'][ $s_key ];
                ?>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:18px 22px;margin-bottom:22px;">
                        <h3 style="margin:0 0 4px;font-size:16px;color:#05295d;"><?php echo esc_html( $s_info['title'] ); ?></h3>
                        <p style="margin:0 0 14px;color:#64748b;font-size:13px;"><?php echo esc_html( $s_info['desc'] ); ?></p>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
                            <div>
                                <label style="display:block;font-weight:600;margin-bottom:5px;font-size:13px;">Çalışma Modu:</label>
                                <select name="findewerkstatt_ad_settings[slots][<?php echo esc_attr( $s_key ); ?>][mode]" style="width:100%;height:38px;">
                                    <option value="hybrid" <?php selected( $s_data['mode'], 'hybrid' ); ?>>Hibrit (Önce Özel Kod / Firma, sonra AdSense, yoksa Teklif Daveti)</option>
                                    <option value="adsense" <?php selected( $s_data['mode'], 'adsense' ); ?>>Sadece Google AdSense</option>
                                    <option value="custom" <?php selected( $s_data['mode'], 'custom' ); ?>>Özel / Manuel Reklam Kodu (HTML/JS)</option>
                                    <option value="direct" <?php selected( $s_data['mode'], 'direct' ); ?>>Sadece Doğrudan Firma Reklamı</option>
                                    <option value="off" <?php selected( $s_data['mode'], 'off' ); ?>>Kapalı (Bu alanı gizle)</option>
                                </select>
                            </div>
                            <div>
                                <label style="display:block;font-weight:600;margin-bottom:5px;font-size:13px;">Google AdSense Slot ID:</label>
                                <input type="text" name="findewerkstatt_ad_settings[slots][<?php echo esc_attr( $s_key ); ?>][adsense_slot]" value="<?php echo esc_attr( $s_data['adsense_slot'] ); ?>" placeholder="Örn: 9876543210 veya <ins data-ad-slot=...>" style="width:100%;height:38px;">
                            </div>
                        </div>

                        <!-- Özel / Manuel Kod Alanı (Kullanıcı AdSense'ten tüm kodu kopyaladıysa) -->
                        <div style="margin-bottom:14px;">
                            <label style="display:block;font-weight:600;margin-bottom:5px;font-size:13px;color:#334155;">
                                📝 Özel / Manuel Reklam Kodu (HTML / AdSense Reklam Birimi Kodu):
                            </label>
                            <textarea name="findewerkstatt_ad_settings[slots][<?php echo esc_attr( $s_key ); ?>][custom_code]" rows="3" class="large-text code" style="width:100%;" placeholder="<ins class=&quot;adsbygoogle&quot; style=&quot;display:block&quot; data-ad-client=&quot;ca-pub-...&quot; data-ad-slot=&quot;...&quot; data-ad-format=&quot;auto&quot;></ins><script>(adsbygoogle=window.adsbygoogle||[]).push({});</script>"><?php echo esc_textarea( $s_data['custom_code'] ?? '' ); ?></textarea>
                            <p class="description" style="font-size:12px;margin-top:3px;">
                                Google AdSense'ten oluşturduğunuz reklam biriminin tam kodunu doğrudan buraya yapıştırabilirsiniz. Kod girilirse öncelikle bu kod çalıştırılır.
                            </p>
                        </div>

                        <!-- Doğrudan Firma Sponsorluk Bilgileri -->
                        <details style="margin-top:10px;padding-top:10px;border-top:1px dashed #cbd5e1;">
                            <summary style="font-weight:700;color:#0f172a;font-size:13px;cursor:pointer;">
                                💼 Doğrudan Firma Reklamı Bilgileri (İlerleyen süreçte reklam aldığınızda açınız)
                            </summary>
                            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-top:10px;">
                                <div>
                                    <label style="display:block;font-size:12px;color:#475569;">Firma / Kampanya Başlığı:</label>
                                    <input type="text" name="findewerkstatt_ad_settings[slots][<?php echo esc_attr( $s_key ); ?>][direct_title]" value="<?php echo esc_attr( $s_data['direct_title'] ); ?>" style="width:100%;" placeholder="Firma Adı veya Kampanya">
                                </div>
                                <div>
                                    <label style="display:block;font-size:12px;color:#475569;">Hedef Link (Açılacak Site):</label>
                                    <input type="url" name="findewerkstatt_ad_settings[slots][<?php echo esc_attr( $s_key ); ?>][direct_link]" value="<?php echo esc_attr( $s_data['direct_link'] ); ?>" style="width:100%;" placeholder="https://firma-sitesi.de">
                                </div>
                                <div>
                                    <label style="display:block;font-size:12px;color:#475569;">Banner Görsel / Video URL:</label>
                                    <input type="url" name="findewerkstatt_ad_settings[slots][<?php echo esc_attr( $s_key ); ?>][direct_image]" value="<?php echo esc_attr( $s_data['direct_image'] ); ?>" style="width:100%;" placeholder="https://site.de/banner.jpg">
                                </div>
                            </div>
                        </details>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- KART 3: ads.txt Yönetimi -->
            <div class="postbox" style="padding:22px;border-radius:12px;box-shadow:0 4px 14px rgba(0,0,0,0.06);margin-bottom:24px;">
                <h2 style="margin-top:0;font-size:18px;display:flex;align-items:center;gap:8px;">
                    <span class="dashicons dashicons-media-text" style="color:#f59e0b;"></span>
                    3. ads.txt Doğrulama Dosyası
                </h2>
                <p style="color:#475569;">
                    Google AdSense hesabınızın onaylanması ve reklam gelirlerinizin güvenceye alınması için <code>/ads.txt</code> dosyası gereklidir.
                    <?php if ( $client ) : 
                        $pub_num = str_replace( 'ca-', '', $client );
                    ?>
                        <br>Otomatik üretilen geçerli kayıt: <code>google.com, <?php echo esc_html( $pub_num ); ?>, DIRECT, f08c47fec0942fa0</code>
                    <?php endif; ?>
                </p>
                <textarea name="findewerkstatt_ad_settings[ads_txt]" rows="3" class="large-text code" placeholder="google.com, pub-XXXXXXXXXXXXXXXX, DIRECT, f08c47fec0942fa0"><?php echo esc_textarea( $settings['ads_txt'] ); ?></textarea>
                <p class="description">İlave reklam ağlarınız (Criteo, Taboola, Ezoic vb.) varsa onların satırlarını da buraya ekleyebilirsiniz.</p>
            </div>

            <!-- KART 4: Sık Karşılaşılan Sorunlar & Çözümleri -->
            <div class="postbox" style="padding:20px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:12px;margin-bottom:24px;">
                <h3 style="margin-top:0;font-size:15px;color:#0369a1;display:flex;align-items:center;gap:6px;">
                    <span>ℹ️</span> Google AdSense Reklamları Neden Görünmüyor Olabilir? (Kontrol Listesi)
                </h3>
                <ul style="margin:8px 0 0 20px;color:#0c4a6e;font-size:13.5px;line-height:1.7;">
                    <li><strong>1. Site İnceleme / Onay Süreci:</strong> Yeni eklenen siteler Google AdSense tarafından incelenir (genellikle birkaç saat ile birkaç gün sürer). Siteniz Google panelinde <em>"Hazır" (Ready)</em> durumuna geçene kadar reklam alanları boş/şeffaf döner.</li>
                    <li><strong>2. Reklam Engelleyici (AdBlock):</strong> Bilgisayarınızda veya telefonunuzda AdBlock, uBlock Origin veya Brave tarayıcı kalkanı açıksa reklamlar engellenir. Kontrol ederken eklentiyi kapatın veya gizli sekmede açın.</li>
                    <li><strong>3. Otomatik Reklamlar:</strong> Yukarıdaki "Google Otomatik Reklamlarını etkinleştir" kutusunu işaretlediğinizde, Google botları sayfanızı tarayıp en yüksek tıklama alacak yerlere reklamları kendisi otomatik yerleştirir.</li>
                </ul>
            </div>

            <?php submit_button( 'Reklam Ayarlarını Kaydet', 'primary button-hero', 'submit', true, array( 'style' => 'background:#fb6006;border-color:#ea580c;font-weight:700;' ) ); ?>
        </form>
    </div>
    <?php
}
