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
        'ads_txt'               => '',
        'slots'                 => array(
            'top_banner'      => array(
                'mode'           => 'hybrid',
                'adsense_slot'   => '',
                'direct_title'   => 'Kfz-Versicherungen & Inspektionsangebote vergleichen',
                'direct_desc'    => 'Finden Sie günstige Tarife und Gutscheine in Ihrer Nähe.',
                'direct_link'    => '',
                'direct_cta'     => 'Angebote ansehen',
                'direct_image'   => '',
            ),
            'incontent_video' => array(
                'mode'           => 'hybrid',
                'adsense_slot'   => '',
                'direct_title'   => 'Jetzt Reifenwechsel & Hauptuntersuchung zum Sparpreis buchen',
                'direct_desc'    => 'Exklusive Rabatte bei teilnehmenden Partnerbetrieben in Sachsen-Anhalt und bundesweit.',
                'direct_link'    => '',
                'direct_cta'     => 'Video ansehen & Rabatt sichern',
                'direct_video'   => '',
            ),
            'sidebar_sticky'  => array(
                'mode'           => 'hybrid',
                'adsense_slot'   => '',
                'direct_title'   => 'Günstige Ersatzteile & Reifen',
                'direct_desc'    => 'Bis zu 30% sparen bei unseren Partnern für Kfz-Teile und Autozubehör.',
                'direct_link'    => '',
                'direct_cta'     => 'Angebote ansehen',
                'direct_image'   => '',
            ),
            'archive_feed'    => array(
                'mode'           => 'hybrid',
                'adsense_slot'   => '',
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
 */
function findewerkstatt_sanitize_adsense_client( $client ) {
    $client = trim( (string) $client );
    if ( '' === $client ) {
        return '';
    }
    if ( preg_match( '/^\d{10,20}$/', $client ) ) {
        return 'ca-pub-' . $client;
    }
    if ( preg_match( '/^ca-pub-\d{10,20}$/i', $client ) ) {
        return strtolower( $client );
    }
    return '';
}

/**
 * Output Google AdSense verification & auto ads script in <head>.
 */
function findewerkstatt_ad_head_scripts() {
    $settings = findewerkstatt_get_ad_settings();
    $client = findewerkstatt_sanitize_adsense_client( $settings['google_adsense_client'] ?? '' );
    if ( ! $client ) {
        return;
    }

    echo "\n<!-- Google AdSense - FindeWerkstatt.de -->\n";
    echo '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . esc_attr( $client ) . '" crossorigin="anonymous"></script>' . "\n";
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

    $mode = $slot['mode'] ?? 'hybrid';
    $client = findewerkstatt_sanitize_adsense_client( $settings['google_adsense_client'] ?? '' );
    $slot_id = trim( (string) ( $slot['adsense_slot'] ?? '' ) );
    $has_adsense = ( $client && preg_match( '/^\d{6,20}$/', $slot_id ) );
    $has_direct = ! empty( $slot['direct_link'] ) || ! empty( $slot['direct_image'] );

    // Decide which format to render
    $render_type = 'fallback';
    if ( 'adsense' === $mode && $has_adsense ) {
        $render_type = 'adsense';
    } elseif ( 'direct' === $mode && $has_direct ) {
        $render_type = 'direct';
    } elseif ( 'hybrid' === $mode ) {
        if ( $has_direct ) {
            $render_type = 'direct';
        } elseif ( $has_adsense ) {
            $render_type = 'adsense';
        } else {
            $render_type = 'fallback';
        }
    }

    ob_start();

    // 1. Google AdSense Unit
    if ( 'adsense' === $render_type ) : ?>
        <div class="fw-ad-zone fw-ad-zone-<?php echo esc_attr( $slot_key ); ?>" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Gesponserte Anzeige' ) ); ?>">
            <div class="fw-ad-header">
                <span class="fw-ad-label"><?php echo esc_html( findewerkstatt_t( 'Gesponserte Anzeige' ) ); ?></span>
            </div>
            <div class="fw-ad-slot fw-ad-slot-adsense">
                <ins class="adsbygoogle"
                     style="display:block"
                     data-ad-client="<?php echo esc_attr( $client ); ?>"
                     data-ad-slot="<?php echo esc_attr( $slot_id ); ?>"
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
                        <div class="fw-ad-tag">SPONSOR</div>
                        <h4><?php echo esc_html( findewerkstatt_t( 'Günstige Ersatzteile & Reifen' ) ); ?></h4>
                        <p><?php echo esc_html( findewerkstatt_t( 'Bis zu 30% sparen bei unseren Partnern für Kfz-Teile und Autozubehör.' ) ); ?></p>
                        <a href="<?php echo esc_url( $contact_url ); ?>" class="fw-btn fw-btn-primary fw-btn-sm fw-ad-btn"><?php echo esc_html( findewerkstatt_t( 'Angebote ansehen' ) ); ?> &rarr;</a>
                    </div>
                </div>
            <?php elseif ( 'archive_feed' === $slot_key ) : ?>
                <div class="fw-ad-slot fw-ad-slot-feed" style="grid-column: 1 / -1; margin: 10px 0 16px;">
                    <div class="fw-ad-slot-leaderboard">
                        <div class="fw-ad-placeholder">
                            <div class="fw-ad-tag">PARTNER</div>
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
                        <div class="fw-ad-tag">ANZEIGE</div>
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
    $clean['ads_txt']               = sanitize_textarea_field( $input['ads_txt'] ?? '' );

    $clean['slots'] = array();
    $allowed_modes  = array( 'hybrid', 'adsense', 'direct', 'off' );

    $raw_slots = is_array( $input['slots'] ?? null ) ? $input['slots'] : array();
    foreach ( array( 'top_banner', 'incontent_video', 'sidebar_sticky', 'archive_feed' ) as $key ) {
        $raw = is_array( $raw_slots[ $key ] ?? null ) ? $raw_slots[ $key ] : array();
        $mode = in_array( $raw['mode'] ?? 'hybrid', $allowed_modes, true ) ? $raw['mode'] : 'hybrid';
        $clean['slots'][ $key ] = array(
            'mode'           => $mode,
            'adsense_slot'   => preg_replace( '/[^0-9]/', '', (string) ( $raw['adsense_slot'] ?? '' ) ),
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
    ?>
    <div class="wrap" style="max-width: 1050px;">
        <h1 style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
            <span class="dashicons dashicons-megaphone" style="font-size:32px;width:32px;height:32px;color:#fb6006;"></span>
            Reklam & Gelir Yönetimi (Google AdSense & Firma Sponsorlukları)
        </h1>
        <p class="description" style="font-size:14px;margin-bottom:24px;">
            Bu panelden sitenizdeki <strong>Google AdSense</strong> kodlarını ve ilerleyen süreçte doğrudan firmalardan alacağınız <strong>özel reklam bannerlarını / videolarını</strong> yönetebilirsiniz.
        </p>

        <?php settings_errors(); ?>

        <form method="post" action="options.php">
            <?php settings_fields( 'findewerkstatt_ads_group' ); ?>

            <!-- KART 1: Google AdSense Genel Ayarları -->
            <div class="postbox" style="padding:22px;border-radius:12px;box-shadow:0 4px 14px rgba(0,0,0,0.06);margin-bottom:24px;">
                <h2 style="margin-top:0;font-size:18px;display:flex;align-items:center;gap:8px;">
                    <span class="dashicons dashicons-google" style="color:#0284c7;"></span>
                    1. Google AdSense Entegrasyonu
                </h2>
                <p>Google AdSense hesabınızdaki <code>ca-pub-XXXXXXXXXXXXXXXX</code> kodunuzu buraya girmeniz yeterlidir. Kod otomatik olarak site <code>&lt;head&gt;</code> alanına entegre edilir.</p>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="fw-adsense-client">Google AdSense Yayıncı Kimliği (Publisher ID)</label></th>
                        <td>
                            <input type="text" id="fw-adsense-client" name="findewerkstatt_ad_settings[google_adsense_client]" value="<?php echo esc_attr( $settings['google_adsense_client'] ); ?>" class="regular-text" placeholder="ca-pub-1234567890123456">
                            <p class="description">Örnek: <code>ca-pub-1234567890123456</code>. Boş bırakırsanız AdSense kodları sitede çalışmaz.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Otomatik Reklamlar (Auto Ads)</th>
                        <td>
                            <label>
                                <input type="checkbox" name="findewerkstatt_ad_settings[google_auto_ads]" value="yes" <?php checked( $settings['google_auto_ads'], 'yes' ); ?>>
                                Google Otomatik Reklamlarını (Auto Ads) etkinleştir.
                            </label>
                            <p class="description">Google'ın sayfa içerisinde en uygun yerlere otomatik olarak reklam yerleştirmesine izin verir.</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- KART 2: Reklam Alanları & Firma Sponsorlukları -->
            <div class="postbox" style="padding:22px;border-radius:12px;box-shadow:0 4px 14px rgba(0,0,0,0.06);margin-bottom:24px;">
                <h2 style="margin-top:0;font-size:18px;display:flex;align-items:center;gap:8px;">
                    <span class="dashicons dashicons-layout" style="color:#10b981;"></span>
                    2. Sayfa İçi Reklam Alanları (Profil & Dizin)
                </h2>
                <p>Her alan için çalışma modunu seçebilirsiniz: <strong>Hibrit</strong> (Firma reklamı varsa firma reklamını, yoksa Google AdSense'i, ikisi de yoksa reklam alma davetiyesini gösterir).</p>

                <?php
                $slot_labels = array(
                    'top_banner'      => array( 'title' => 'Üst Banner Reklamı (Profil Sayfası)', 'desc' => 'Atölye profilinde başlığın hemen altında, içeriklerden önce yer alır.' ),
                    'incontent_video' => array( 'title' => 'İçerik İçi Video / Sponsor Reklamı (İletişimden Hemen Önce)', 'desc' => 'Hizmetler ve markalar bittikten sonra, kullanıcının iletişim bilgilerine ulaşmadan hemen önce izleyeceği / tıklayacağı reklam.' ),
                    'sidebar_sticky'  => array( 'title' => 'Sağ Kenar Çubuğu Yapışkan Reklamı (Sticky Sidebar)', 'desc' => 'Masaüstünde sayfa boyunca kayarken ekranda sabit kalan kare/dikdörtgen reklam alanı.' ),
                    'archive_feed'    => array( 'title' => 'Dizin / Arama İçi Banner (Werkstätten Listesi)', 'desc' => 'Firma listeleme sayfasında kartların arasında yer alan sponsor/AdSense alanı.' ),
                );

                foreach ( $slot_labels as $s_key => $s_info ) :
                    $s_data = $settings['slots'][ $s_key ];
                ?>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px 20px;margin-bottom:20px;">
                        <h3 style="margin:0 0 4px;font-size:16px;color:#05295d;"><?php echo esc_html( $s_info['title'] ); ?></h3>
                        <p style="margin:0 0 12px;color:#64748b;font-size:13px;"><?php echo esc_html( $s_info['desc'] ); ?></p>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                            <div>
                                <label style="display:block;font-weight:600;margin-bottom:4px;">Çalışma Modu:</label>
                                <select name="findewerkstatt_ad_settings[slots][<?php echo esc_attr( $s_key ); ?>][mode]" style="width:100%;">
                                    <option value="hybrid" <?php selected( $s_data['mode'], 'hybrid' ); ?>>Hibrit (Önce Firma, sonra Google AdSense, yoksa Teklif Daveti)</option>
                                    <option value="adsense" <?php selected( $s_data['mode'], 'adsense' ); ?>>Sadece Google AdSense</option>
                                    <option value="direct" <?php selected( $s_data['mode'], 'direct' ); ?>>Sadece Doğrudan Firma Reklamı</option>
                                    <option value="off" <?php selected( $s_data['mode'], 'off' ); ?>>Kapalı (Bu alanı gizle)</option>
                                </select>
                            </div>
                            <div>
                                <label style="display:block;font-weight:600;margin-bottom:4px;">Google AdSense Slot ID:</label>
                                <input type="text" name="findewerkstatt_ad_settings[slots][<?php echo esc_attr( $s_key ); ?>][adsense_slot]" value="<?php echo esc_attr( $s_data['adsense_slot'] ); ?>" placeholder="Örn: 9876543210" style="width:100%;">
                            </div>
                        </div>

                        <!-- Doğrudan Firma Sponsorluk Bilgileri -->
                        <div style="margin-top:12px;padding-top:12px;border-top:1px dashed #cbd5e1;">
                            <span style="font-weight:700;color:#0f172a;font-size:13px;">💼 Doğrudan Firma Reklamı Bilgileri (İlerleyen süreçte reklam aldığınızda doldurunuz):</span>
                            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-top:8px;">
                                <div>
                                    <label style="display:block;font-size:12px;color:#475569;">Firma / Kampanya Başlığı:</label>
                                    <input type="text" name="findewerkstatt_ad_settings[slots][<?php echo esc_attr( $s_key ); ?>][direct_title]" value="<?php echo esc_attr( $s_data['direct_title'] ); ?>" style="width:100%;" placeholder="Firma Adı veya Kampanya">
                                </div>
                                <div>
                                    <label style="display:block;font-size:12px;color:#475569;">Hedef Link (Tıklanınca Açılacak Site):</label>
                                    <input type="url" name="findewerkstatt_ad_settings[slots][<?php echo esc_attr( $s_key ); ?>][direct_link]" value="<?php echo esc_attr( $s_data['direct_link'] ); ?>" style="width:100%;" placeholder="https://firma-sitesi.de">
                                </div>
                                <div>
                                    <label style="display:block;font-size:12px;color:#475569;">Banner Görsel / Video URL (Opsiyonel):</label>
                                    <input type="url" name="findewerkstatt_ad_settings[slots][<?php echo esc_attr( $s_key ); ?>][direct_image]" value="<?php echo esc_attr( $s_data['direct_image'] ); ?>" style="width:100%;" placeholder="https://site.de/banner.jpg">
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- KART 3: ads.txt Yönetimi -->
            <div class="postbox" style="padding:22px;border-radius:12px;box-shadow:0 4px 14px rgba(0,0,0,0.06);margin-bottom:24px;">
                <h2 style="margin-top:0;font-size:18px;display:flex;align-items:center;gap:8px;">
                    <span class="dashicons dashicons-media-text" style="color:#f59e0b;"></span>
                    3. ads.txt Doğrulama Dosyası
                </h2>
                <p>Google AdSense hesabınızın onaylanması için <code>/ads.txt</code> dosyası gereklidir. Yayıncı kimliğinizi yukarıya girdiğinizde sistem otomatik olarak doğru kaydı üretir, dilerseniz aşağıya kendi özel satırlarınızı da ekleyebilirsiniz.</p>
                <textarea name="findewerkstatt_ad_settings[ads_txt]" rows="4" class="large-text code" placeholder="google.com, pub-XXXXXXXXXXXXXXXX, DIRECT, f08c47fec0942fa0"><?php echo esc_textarea( $settings['ads_txt'] ); ?></textarea>
                <p class="description">Örnek: <code>google.com, pub-XXXXXXXXXXXXXXXX, DIRECT, f08c47fec0942fa0</code></p>
            </div>

            <?php submit_button( 'Reklam Ayarlarını Kaydet' ); ?>
        </form>
    </div>
    <?php
}
