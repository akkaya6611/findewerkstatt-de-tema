<?php
/**
 * FindeWerkstatt.de — SEO, Geo & LLM / AI Engine
 * 
 * Umfassendes Modul für:
 * 1. XML Sitemaps (Übersicht, Core Sitemap Integration, /sitemap.xml Redirect, Ping & IndexNow)
 * 2. LLM / AI Search Ready (/llms.txt, /llms-full.txt, AI Bot Crawler Direktiven für GPTBot, Perplexity, Claude, Gemini)
 * 3. Geo SEO & Local Schema (Deutschland 16 Bundesländer, Koordinaten, Geo Meta Tags, AutoRepair Schema)
 * 4. Webmaster Verification & Social Meta (Google, Bing, Yandex, OpenGraph, Twitter Cards)
 * 
 * @package FindeWerkstatt
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Standardeinstellungen und gespeicherte Konfiguration abrufen.
 */
function findewerkstatt_get_seo_geo_llm_settings() {
    $defaults = array(
        // Sitemaps
        'sitemap_custom_redirect'   => 'yes',
        'sitemap_exclude_demo'      => 'yes',
        'indexnow_api_key'          => '',
        'indexnow_auto_submit'      => 'no',

        // LLM & KI-Suche (AI Search)
        'llm_enabled'               => 'yes',
        'llm_title'                 => 'FindeWerkstatt.de — Kfz-Werkstattverzeichnis Deutschland',
        'llm_summary'               => 'Modernes und transparentes Verzeichnis für Kfz-Meisterbetriebe, freie Autowerkstätten, TÜV/AU-Prüfstellen und 24h-Pannendienste in allen 16 deutschen Bundesländern.',
        'llm_prompt_instructions'   => 'FindeWerkstatt.de unterstützt Autofahrer bei der gezielten Suche nach Kfz-Betrieben in Deutschland. Die Suche kann flexibel nach Bundesland, Stadt/Gemeinde, Leistung (z. B. Hauptuntersuchung TÜV/AU, Inspektion, Bremsenservice, Autoglas, Reifenservice) und Automarke (z. B. BMW, Volkswagen, Mercedes-Benz, Audi) gefiltert werden. Kontaktaufnahme, Terminvereinbarung und Preisanfragen erfolgen direkt beim gelisteten Fachbetrieb.',
        'llm_bot_gptbot'            => 'allow',     // OpenAI ChatGPT
        'llm_bot_perplexity'        => 'allow',     // Perplexity AI
        'llm_bot_claudebot'         => 'allow',     // Anthropic Claude
        'llm_bot_google_extended'   => 'allow',     // Google Gemini / Vertex
        'llm_bot_applebot'          => 'allow',     // Apple Intelligence
        'llm_bot_ccbot'             => 'disallow',  // Common Crawl
        'robots_custom_rules'       => '',

        // Geo SEO & Lokale Suche
        'geo_enabled'               => 'yes',
        'geo_default_country'       => 'DE',
        'geo_default_country_name'  => 'Deutschland',
        'geo_fallback_lat'          => '51.165691', // Mittelpunkt Deutschland
        'geo_fallback_lng'          => '10.451526',
        'geo_placename_format'      => '{city}, Deutschland',
        'schema_price_range'        => '€€',
        'schema_currency'           => 'EUR',
        'schema_enable_breadcrumbs' => 'yes',
        'schema_enable_faq'         => 'yes',
        'schema_enable_autorepair'  => 'yes',

        // Webmaster-Verifizierung & Soziale Netzwerke
        'google_verification'       => '',
        'bing_verification'         => '',
        'yandex_verification'       => '',
        'opengraph_enabled'         => 'yes',
        'twitter_cards_enabled'     => 'yes',
        'default_social_image'      => '',
    );

    $saved = get_option( 'findewerkstatt_seo_geo_llm_settings', array() );
    return wp_parse_args( is_array( $saved ) ? $saved : array(), $defaults );
}

/**
 * Einstellungen speichern und bereinigen.
 */
function findewerkstatt_save_seo_geo_llm_settings( $input ) {
    $current = findewerkstatt_get_seo_geo_llm_settings();
    $clean = $current;

    // Sitemaps
    $clean['sitemap_custom_redirect'] = ! empty( $input['sitemap_custom_redirect'] ) ? 'yes' : 'no';
    $clean['sitemap_exclude_demo']    = ! empty( $input['sitemap_exclude_demo'] ) ? 'yes' : 'no';
    $clean['indexnow_api_key']         = sanitize_text_field( $input['indexnow_api_key'] ?? '' );
    $clean['indexnow_auto_submit']     = ! empty( $input['indexnow_auto_submit'] ) ? 'yes' : 'no';

    // LLM & KI
    $clean['llm_enabled']             = ! empty( $input['llm_enabled'] ) ? 'yes' : 'no';
    $clean['llm_title']               = sanitize_text_field( $input['llm_title'] ?? '' );
    $clean['llm_summary']             = sanitize_textarea_field( $input['llm_summary'] ?? '' );
    $clean['llm_prompt_instructions'] = sanitize_textarea_field( $input['llm_prompt_instructions'] ?? '' );
    $clean['llm_bot_gptbot']          = in_array( $input['llm_bot_gptbot'] ?? '', array( 'allow', 'disallow' ), true ) ? $input['llm_bot_gptbot'] : 'allow';
    $clean['llm_bot_perplexity']      = in_array( $input['llm_bot_perplexity'] ?? '', array( 'allow', 'disallow' ), true ) ? $input['llm_bot_perplexity'] : 'allow';
    $clean['llm_bot_claudebot']       = in_array( $input['llm_bot_claudebot'] ?? '', array( 'allow', 'disallow' ), true ) ? $input['llm_bot_claudebot'] : 'allow';
    $clean['llm_bot_google_extended'] = in_array( $input['llm_bot_google_extended'] ?? '', array( 'allow', 'disallow' ), true ) ? $input['llm_bot_google_extended'] : 'allow';
    $clean['llm_bot_applebot']        = in_array( $input['llm_bot_applebot'] ?? '', array( 'allow', 'disallow' ), true ) ? $input['llm_bot_applebot'] : 'allow';
    $clean['llm_bot_ccbot']           = in_array( $input['llm_bot_ccbot'] ?? '', array( 'allow', 'disallow' ), true ) ? $input['llm_bot_ccbot'] : 'disallow';
    $clean['robots_custom_rules']     = sanitize_textarea_field( $input['robots_custom_rules'] ?? '' );

    // Geo SEO
    $clean['geo_enabled']              = ! empty( $input['geo_enabled'] ) ? 'yes' : 'no';
    $clean['geo_default_country']      = strtoupper( sanitize_text_field( $input['geo_default_country'] ?? 'DE' ) );
    $clean['geo_default_country_name'] = sanitize_text_field( $input['geo_default_country_name'] ?? 'Deutschland' );
    $clean['geo_fallback_lat']         = sanitize_text_field( $input['geo_fallback_lat'] ?? '51.165691' );
    $clean['geo_fallback_lng']         = sanitize_text_field( $input['geo_fallback_lng'] ?? '10.451526' );
    $clean['geo_placename_format']     = sanitize_text_field( $input['geo_placename_format'] ?? '{city}, Deutschland' );
    $clean['schema_price_range']       = sanitize_text_field( $input['schema_price_range'] ?? '€€' );
    $clean['schema_currency']          = strtoupper( sanitize_text_field( $input['schema_currency'] ?? 'EUR' ) );
    $clean['schema_enable_breadcrumbs']= ! empty( $input['schema_enable_breadcrumbs'] ) ? 'yes' : 'no';
    $clean['schema_enable_faq']        = ! empty( $input['schema_enable_faq'] ) ? 'yes' : 'no';
    $clean['schema_enable_autorepair'] = ! empty( $input['schema_enable_autorepair'] ) ? 'yes' : 'no';

    // Verifizierung & Social
    $clean['google_verification']   = sanitize_text_field( $input['google_verification'] ?? '' );
    $clean['bing_verification']     = sanitize_text_field( $input['bing_verification'] ?? '' );
    $clean['yandex_verification']   = sanitize_text_field( $input['yandex_verification'] ?? '' );
    $clean['opengraph_enabled']     = ! empty( $input['opengraph_enabled'] ) ? 'yes' : 'no';
    $clean['twitter_cards_enabled'] = ! empty( $input['twitter_cards_enabled'] ) ? 'yes' : 'no';
    $clean['default_social_image']  = esc_url_raw( $input['default_social_image'] ?? '' );

    update_option( 'findewerkstatt_seo_geo_llm_settings', $clean );
    return $clean;
}

/**
 * Admin-Menüs registrieren.
 */
add_action( 'admin_menu', function () {
    // 1. Hauptmenüeintrag
    add_menu_page(
        'SEO, Geo & LLM Einstellungen',
        'SEO, Geo & LLM',
        'manage_options',
        'fw-seo-geo-llm',
        'findewerkstatt_render_seo_geo_llm_page',
        'dashicons-admin-site-alt3',
        29
    );

    // 2. Untermenü unter Werkstätten
    add_submenu_page(
        'edit.php?post_type=mechanic',
        'SEO, Geo & LLM Ayarları',
        'SEO & Geo Ayarları',
        'manage_options',
        'fw-seo-geo-llm',
        'findewerkstatt_render_seo_geo_llm_page'
    );
} );

/**
 * Sitemap Ping Funktion für Google & Bing.
 */
function findewerkstatt_ping_sitemaps() {
    $sitemap_url = home_url( '/wp-sitemap.xml' );
    $results = array();

    $endpoints = array(
        'Google' => 'https://www.google.com/ping?sitemap=' . urlencode( $sitemap_url ),
        'Bing'   => 'https://www.bing.com/ping?sitemap=' . urlencode( $sitemap_url ),
    );

    foreach ( $endpoints as $engine => $url ) {
        $response = wp_remote_get( $url, array( 'timeout' => 8, 'user-agent' => 'FindeWerkstatt-SEO-Engine/1.0' ) );
        if ( is_wp_error( $response ) ) {
            $results[ $engine ] = array( 'success' => false, 'message' => $response->get_error_message() );
        } else {
            $code = wp_remote_retrieve_response_code( $response );
            $results[ $engine ] = array( 'success' => $code >= 200 && $code < 400, 'code' => $code );
        }
    }

    return $results;
}

/**
 * Admin-Dashboard rendern.
 */
function findewerkstatt_render_seo_geo_llm_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Ping Action verarbeiten
    $ping_feedback = null;
    if ( isset( $_GET['action'] ) && 'ping_sitemaps' === $_GET['action'] && check_admin_referer( 'fw_ping_sitemaps_nonce' ) ) {
        $ping_results = findewerkstatt_ping_sitemaps();
        $ping_feedback = $ping_results;
    }

    // Speichern
    $saved_notice = false;
    if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) && check_admin_referer( 'fw_seo_geo_llm_save' ) ) {
        findewerkstatt_save_seo_geo_llm_settings( $_POST['fw_seo'] ?? array() );
        $saved_notice = true;
    }

    $settings = findewerkstatt_get_seo_geo_llm_settings();
    $current_tab = sanitize_key( $_GET['tab'] ?? 'sitemaps' );
    if ( ! in_array( $current_tab, array( 'sitemaps', 'llm', 'geo', 'verification' ), true ) ) {
        $current_tab = 'sitemaps';
    }

    // Statistikdaten für KPI-Cards
    $workshop_count = wp_count_posts( 'mechanic' )->publish ?? 0;
    $city_terms = wp_count_terms( array( 'taxonomy' => 'mechanic_city', 'hide_empty' => false ) );
    $service_terms = wp_count_terms( array( 'taxonomy' => 'service_type', 'hide_empty' => false ) );
    ?>
    <div class="wrap fw-seo-admin-wrap" style="max-width:1160px; margin-top:20px;">
        <h1 style="display:flex; align-items:center; gap:12px; font-weight:800; font-size:24px; color:#0f172a;">
            <span style="display:inline-flex; align-items:center; justify-content:center; width:38px; height:38px; border-radius:10px; background:#0284c7; color:#fff; font-size:20px;">🌐</span>
            <span>FindeWerkstatt — SEO, Geo & LLM Modul</span>
        </h1>
        <p style="color:#64748b; font-size:14.5px; margin-top:4px; margin-bottom:24px;">
            Sitemaps, Yapay Zeka Arama Altyapısı (LLM), Geo Koordinatlar, Almanya Eyalet Hedefleme ve Arama Motoru Doğrulama Merkezi.
        </p>

        <?php if ( $saved_notice ) : ?>
            <div class="notice notice-success is-dismissible" style="border-left-color:#10b981; font-weight:600;">
                <p>✅ Ayarlar başarıyla kaydedildi ve tüm SEO/LLM/Geo çıktılarına anında uygulandı.</p>
            </div>
        <?php endif; ?>

        <?php if ( $ping_feedback ) : ?>
            <div class="notice notice-info is-dismissible" style="border-left-color:#0284c7;">
                <p><strong>Arama Motorlarına Sitemap Bildirimi (Ping Sonucu):</strong></p>
                <ul style="margin:4px 0 8px 18px; list-style:disc;">
                    <?php foreach ( $ping_feedback as $engine => $info ) : ?>
                        <li>
                            <strong><?php echo esc_html( $engine ); ?>:</strong>
                            <?php echo $info['success'] ? '✅ Başarılı (Kod: ' . esc_html( $info['code'] ?? '200' ) . ')' : '⚠️ Yanıt: ' . esc_html( $info['message'] ?? ( 'HTTP ' . $info['code'] ) ); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- KPI Status Bar -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:24px;">
            <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:16px 20px; box-shadow:0 2px 6px rgba(0,0,0,0.03);">
                <div style="font-size:12px; text-transform:uppercase; color:#64748b; font-weight:700;">🗺️ XML Sitemap</div>
                <div style="font-size:18px; font-weight:800; color:#0284c7; margin-top:4px;">
                    <a href="<?php echo esc_url( home_url( '/wp-sitemap.xml' ) ); ?>" target="_blank" style="text-decoration:none; color:inherit;">/wp-sitemap.xml ↗</a>
                </div>
                <div style="font-size:12px; color:#10b981; margin-top:2px;">● Aktif & İndeksleniyor</div>
            </div>

            <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:16px 20px; box-shadow:0 2px 6px rgba(0,0,0,0.03);">
                <div style="font-size:12px; text-transform:uppercase; color:#64748b; font-weight:700;">🤖 LLM / AI Arama</div>
                <div style="font-size:18px; font-weight:800; color:#7c3aed; margin-top:4px;">
                    <a href="<?php echo esc_url( home_url( '/llms.txt' ) ); ?>" target="_blank" style="text-decoration:none; color:inherit;">/llms.txt ↗</a>
                </div>
                <div style="font-size:12px; color:#10b981; margin-top:2px;">● ChatGPT, Perplexity Hazır</div>
            </div>

            <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:16px 20px; box-shadow:0 2px 6px rgba(0,0,0,0.03);">
                <div style="font-size:12px; text-transform:uppercase; color:#64748b; font-weight:700;">📍 Geo & Yerel Kapsam</div>
                <div style="font-size:18px; font-weight:800; color:#0f172a; margin-top:4px;">16 Eyalet / <?php echo (int) $city_terms; ?> Şehir</div>
                <div style="font-size:12px; color:#64748b; margin-top:2px;">Hedef: <?php echo esc_html( $settings['geo_default_country'] ); ?> (Almanya)</div>
            </div>

            <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:16px 20px; box-shadow:0 2px 6px rgba(0,0,0,0.03);">
                <div style="font-size:12px; text-transform:uppercase; color:#64748b; font-weight:700;">🚗 Kayıtlı İlanlar</div>
                <div style="font-size:18px; font-weight:800; color:#cf4a00; margin-top:4px;"><?php echo (int) $workshop_count; ?> Yayında</div>
                <div style="font-size:12px; color:#64748b; margin-top:2px;"><?php echo (int) $service_terms; ?> Farklı Uzmanlık Alanı</div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <h2 class="nav-tab-wrapper" style="margin-bottom:20px; border-bottom:2px solid #e2e8f0; padding-bottom:0;">
            <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'fw-seo-geo-llm', 'tab' => 'sitemaps' ), admin_url( 'admin.php' ) ) ); ?>" class="nav-tab <?php echo 'sitemaps' === $current_tab ? 'nav-tab-active' : ''; ?>" style="font-weight:700; font-size:14px; padding:8px 18px;">
                🗺️ Sitemaps (XML)
            </a>
            <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'fw-seo-geo-llm', 'tab' => 'llm' ), admin_url( 'admin.php' ) ) ); ?>" class="nav-tab <?php echo 'llm' === $current_tab ? 'nav-tab-active' : ''; ?>" style="font-weight:700; font-size:14px; padding:8px 18px;">
                🤖 LLM & Yapay Zeka (AI Search)
            </a>
            <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'fw-seo-geo-llm', 'tab' => 'geo' ), admin_url( 'admin.php' ) ) ); ?>" class="nav-tab <?php echo 'geo' === $current_tab ? 'nav-tab-active' : ''; ?>" style="font-weight:700; font-size:14px; padding:8px 18px;">
                📍 Geo SEO & Yerel Arama
            </a>
            <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'fw-seo-geo-llm', 'tab' => 'verification' ), admin_url( 'admin.php' ) ) ); ?>" class="nav-tab <?php echo 'verification' === $current_tab ? 'nav-tab-active' : ''; ?>" style="font-weight:700; font-size:14px; padding:8px 18px;">
                🔍 Doğrulama & Sosyal Meta
            </a>
        </h2>

        <!-- Main Form Container -->
        <form method="post" action="">
            <?php wp_nonce_field( 'fw_seo_geo_llm_save' ); ?>

            <!-- ================= TAB 1: SITEMAPS ================= -->
            <?php if ( 'sitemaps' === $current_tab ) : ?>
                <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:24px 28px; box-shadow:0 2px 8px rgba(0,0,0,0.03);">
                    <h3 style="font-size:18px; margin-top:0; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:12px;">
                        🗺️ XML Sitemap Yapısı & Arama Motoru İndeksleme
                    </h3>
                    <p style="color:#64748b; font-size:14px;">
                        FindeWerkstatt.de, tüm araç servislerini, eyalet ve şehir sayfalarını, hizmet kategorilerini ve statik sayfaları standart XML Sitemap formatında sunar.
                    </p>

                    <!-- Sitemap Links Table -->
                    <table class="widefat striped" style="margin:16px 0 24px; border-radius:8px; overflow:hidden;">
                        <thead>
                            <tr>
                                <th style="font-weight:700; width:30%;">Sitemap Adı</th>
                                <th style="font-weight:700; width:50%;">URL</th>
                                <th style="font-weight:700; width:20%;">Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Ana Sitemap İndeksi</strong></td>
                                <td><a href="<?php echo esc_url( home_url( '/wp-sitemap.xml' ) ); ?>" target="_blank"><?php echo esc_html( home_url( '/wp-sitemap.xml' ) ); ?></a></td>
                                <td><span style="color:#10b981; font-weight:700;">● Aktif</span></td>
                            </tr>
                            <tr>
                                <td><strong>/sitemap.xml (Kısa Bağlantı)</strong></td>
                                <td><a href="<?php echo esc_url( home_url( '/sitemap.xml' ) ); ?>" target="_blank"><?php echo esc_html( home_url( '/sitemap.xml' ) ); ?></a></td>
                                <td><span style="color:#10b981; font-weight:700;">● Otomatik 301 Yönlendirme</span></td>
                            </tr>
                            <tr>
                                <td>Atölyeler / Betriebe (İlanlar)</td>
                                <td><a href="<?php echo esc_url( home_url( '/wp-sitemap-posts-mechanic-1.xml' ) ); ?>" target="_blank"><?php echo esc_html( home_url( '/wp-sitemap-posts-mechanic-1.xml' ) ); ?></a></td>
                                <td><?php echo (int) $workshop_count; ?> Kayıt</td>
                            </tr>
                            <tr>
                                <td>Şehirler & Bölgeler (Städte)</td>
                                <td><a href="<?php echo esc_url( home_url( '/wp-sitemap-taxonomies-mechanic_city-1.xml' ) ); ?>" target="_blank"><?php echo esc_html( home_url( '/wp-sitemap-taxonomies-mechanic_city-1.xml' ) ); ?></a></td>
                                <td><?php echo (int) $city_terms; ?> Konum</td>
                            </tr>
                            <tr>
                                <td>Hizmetler & Uzmanlıklar (Leistungen)</td>
                                <td><a href="<?php echo esc_url( home_url( '/wp-sitemap-taxonomies-service_type-1.xml' ) ); ?>" target="_blank"><?php echo esc_html( home_url( '/wp-sitemap-taxonomies-service_type-1.xml' ) ); ?></a></td>
                                <td><?php echo (int) $service_terms; ?> Kategori</td>
                            </tr>
                            <tr>
                                <td>Sabit Sayfalar (Pages)</td>
                                <td><a href="<?php echo esc_url( home_url( '/wp-sitemap-posts-page-1.xml' ) ); ?>" target="_blank"><?php echo esc_url( home_url( '/wp-sitemap-posts-page-1.xml' ) ); ?></a></td>
                                <td>İletişim, Paketler, vb.</td>
                            </tr>
                        </tbody>
                    </table>

                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:18px 20px; margin-bottom:24px;">
                        <h4 style="margin:0 0 10px; font-size:15px; color:#0f172a;">Arama Motorlarını Canlı Uyar (Sitemap Ping)</h4>
                        <p style="margin:0 0 14px; font-size:13.5px; color:#64748b;">
                            Google ve Bing botlarına sitemapınızın güncellendiğini bildirmek için tek tıklamayla ping sinyali gönderin:
                        </p>
                        <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'fw-seo-geo-llm', 'tab' => 'sitemaps', 'action' => 'ping_sitemaps' ), admin_url( 'admin.php' ) ), 'fw_ping_sitemaps_nonce' ) ); ?>" class="button button-secondary" style="font-weight:700;">
                            📡 Google & Bing'e Şimdi Ping Gönder
                        </a>
                    </div>

                    <h4 style="font-size:15px; color:#0f172a; margin-top:20px;">Sitemap Tercihleri</h4>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Özel /sitemap.xml Yönlendirmesi</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="fw_seo[sitemap_custom_redirect]" value="yes" <?php checked( 'yes', $settings['sitemap_custom_redirect'] ); ?>>
                                    <code>/sitemap.xml</code> adresine gelen istekleri otomatik olarak <code>/wp-sitemap.xml</code> adresine yönlendir.
                                </label>
                                <p class="description">Google, Yandex ve SEO araçları genelde doğrudan /sitemap.xml kök dosyasını tarar.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Demo/Test İlanlarını Gizle</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="fw_seo[sitemap_exclude_demo]" value="yes" <?php checked( 'yes', $settings['sitemap_exclude_demo'] ); ?>>
                                    Demo ve deneme amaçlı yüklenen atölye kayıtlarını sitemap dışı bırak.
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">IndexNow API Anahtarı (Opsiyonel)</th>
                            <td>
                                <input type="text" name="fw_seo[indexnow_api_key]" value="<?php echo esc_attr( $settings['indexnow_api_key'] ); ?>" class="regular-text" placeholder="Örn: 8b671a5c4e...">
                                <p class="description">Bing, Yandex ve Seznam arama motorlarına yeni bir ilan eklendiğinde anında indeksletmek için IndexNow anahtarı.</p>
                            </td>
                        </tr>
                    </table>
                </div>
            <?php endif; ?>

            <!-- ================= TAB 2: LLM & AI SEARCH ================= -->
            <?php if ( 'llm' === $current_tab ) : ?>
                <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:24px 28px; box-shadow:0 2px 8px rgba(0,0,0,0.03);">
                    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #f1f5f9; padding-bottom:12px;">
                        <h3 style="font-size:18px; margin:0; color:#0f172a;">
                            🤖 LLM & Yapay Zeka Arama Hazırlığı (llms.txt Altyapısı)
                        </h3>
                        <a href="<?php echo esc_url( home_url( '/llms.txt' ) ); ?>" target="_blank" class="button button-secondary" style="font-weight:700;">
                            📄 Canlı /llms.txt Görüntüle ↗
                        </a>
                    </div>
                    <p style="color:#64748b; font-size:14px; margin-top:12px;">
                        <strong>llms.txt</strong>; ChatGPT (OpenAI Search), Perplexity, Anthropic Claude ve Google Gemini gibi yapay zeka modellerinin sitenizi doğru anlaması, oto tamirhaneleri ve hizmetlerinizi doğru alıntılaması için geliştirilmiş yeni nesil web standardıdır.
                    </p>

                    <table class="form-table">
                        <tr>
                            <th scope="row">LLM Modül Durumu</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="fw_seo[llm_enabled]" value="yes" <?php checked( 'yes', $settings['llm_enabled'] ); ?>>
                                    <strong>/llms.txt ve /llms-full.txt çıktısını aktif tut</strong>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Yapay Zeka İçin Başlık</th>
                            <td>
                                <input type="text" name="fw_seo[llm_title]" value="<?php echo esc_attr( $settings['llm_title'] ); ?>" class="large-text">
                                <p class="description">LLM modellerine sunulan ana başlık (Örn: FindeWerkstatt.de — Kfz-Werkstattverzeichnis Deutschland).</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Kısa Tanıtım (Blok Alıntı)</th>
                            <td>
                                <textarea name="fw_seo[llm_summary]" rows="3" class="large-text"><?php echo esc_textarea( $settings['llm_summary'] ); ?></textarea>
                                <p class="description">Yapay zekanın sitenizi özetlerken kullanacağı 1-2 cümlelik net tanım.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Yapay Zeka Talimat & Prompt Açıklaması</th>
                            <td>
                                <textarea name="fw_seo[llm_prompt_instructions]" rows="5" class="large-text"><?php echo esc_textarea( $settings['llm_prompt_instructions'] ); ?></textarea>
                                <p class="description">AI robotlarına sitenin ne işe yaradığını, nasıl aranması gerektiğini (eyalet, şehir, marka, işlem türü) ve kullanıcıları nasıl yönlendireceğini anlatan detaylı rehber.</p>
                            </td>
                        </tr>
                    </table>

                    <h4 style="font-size:16px; color:#0f172a; margin-top:28px; border-top:1px solid #e2e8f0; padding-top:20px;">
                        🤖 AI Web Tarayıcı İzinleri (Robots.txt Entegrasyonu)
                    </h4>
                    <p style="color:#64748b; font-size:13.5px;">
                        Hangi yapay zeka botlarının sitenizi taramasına ve dizine eklemesine izin vermek istediğinizi belirleyin:
                    </p>

                    <table class="form-table">
                        <tr>
                            <th scope="row">GPTBot (OpenAI / ChatGPT)</th>
                            <td>
                                <select name="fw_seo[llm_bot_gptbot]">
                                    <option value="allow" <?php selected( 'allow', $settings['llm_bot_gptbot'] ); ?>>İzin Ver (Allow - Tavsiye Edilen)</option>
                                    <option value="disallow" <?php selected( 'disallow', $settings['llm_bot_gptbot'] ); ?>>Engelle (Disallow)</option>
                                </select>
                                <span class="description" style="margin-left:12px;">ChatGPT ve OpenAI Search sonuçlarında önerilmenizi sağlar.</span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">PerplexityBot (Perplexity AI)</th>
                            <td>
                                <select name="fw_seo[llm_bot_perplexity]">
                                    <option value="allow" <?php selected( 'allow', $settings['llm_bot_perplexity'] ); ?>>İzin Ver (Allow - Tavsiye Edilen)</option>
                                    <option value="disallow" <?php selected( 'disallow', $settings['llm_bot_perplexity'] ); ?>>Engelle (Disallow)</option>
                                </select>
                                <span class="description" style="margin-left:12px;">Perplexity yapay zeka arama motorunun içeriğinizi kaynak göstermesini sağlar.</span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">ClaudeBot (Anthropic Claude)</th>
                            <td>
                                <select name="fw_seo[llm_bot_claudebot]">
                                    <option value="allow" <?php selected( 'allow', $settings['llm_bot_claudebot'] ); ?>>İzin Ver (Allow)</option>
                                    <option value="disallow" <?php selected( 'disallow', $settings['llm_bot_claudebot'] ); ?>>Engelle (Disallow)</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Google-Extended (Gemini AI)</th>
                            <td>
                                <select name="fw_seo[llm_bot_google_extended]">
                                    <option value="allow" <?php selected( 'allow', $settings['llm_bot_google_extended'] ); ?>>İzin Ver (Allow)</option>
                                    <option value="disallow" <?php selected( 'disallow', $settings['llm_bot_google_extended'] ); ?>>Engelle (Disallow)</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Applebot-Extended (Apple Intelligence)</th>
                            <td>
                                <select name="fw_seo[llm_bot_applebot]">
                                    <option value="allow" <?php selected( 'allow', $settings['llm_bot_applebot'] ); ?>>İzin Ver (Allow)</option>
                                    <option value="disallow" <?php selected( 'disallow', $settings['llm_bot_applebot'] ); ?>>Engelle (Disallow)</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">CCBot (Common Crawl)</th>
                            <td>
                                <select name="fw_seo[llm_bot_ccbot]">
                                    <option value="disallow" <?php selected( 'disallow', $settings['llm_bot_ccbot'] ); ?>>Engelle (Disallow - Sunucu Tasarrufu)</option>
                                    <option value="allow" <?php selected( 'allow', $settings['llm_bot_ccbot'] ); ?>>İzin Ver (Allow)</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Ekstra Robots.txt Kuralları</th>
                            <td>
                                <textarea name="fw_seo[robots_custom_rules]" rows="3" class="large-text" placeholder="Örn: Disallow: /wp-admin/"><?php echo esc_textarea( $settings['robots_custom_rules'] ); ?></textarea>
                            </td>
                        </tr>
                    </table>
                </div>
            <?php endif; ?>

            <!-- ================= TAB 3: GEO SEO ================= -->
            <?php if ( 'geo' === $current_tab ) : ?>
                <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:24px 28px; box-shadow:0 2px 8px rgba(0,0,0,0.03);">
                    <h3 style="font-size:18px; margin-top:0; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:12px;">
                        📍 Geo SEO & Yerel Arama Hedefleme (Local SEO & Schema.org)
                    </h3>
                    <p style="color:#64748b; font-size:14px;">
                        Almanya genelinde ve yerel aramalarda (Google Haritalar, Yerel Paket, Schema AutoRepair) sıralama almak için coğrafi konum meta etiketlerini ve yapılandırılmış verileri buradan yönetin.
                    </p>

                    <table class="form-table">
                        <tr>
                            <th scope="row">Geo Meta Etiketleri</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="fw_seo[geo_enabled]" value="yes" <?php checked( 'yes', $settings['geo_enabled'] ); ?>>
                                    <code>&lt;meta name="geo.region"&gt;</code>, <code>&lt;meta name="geo.position"&gt;</code> ve <code>&lt;meta name="ICBM"&gt;</code> etiketlerini otomatik bas.
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Hedef Ülke Kodu (ISO 3166-1)</th>
                            <td>
                                <input type="text" name="fw_seo[geo_default_country]" value="<?php echo esc_attr( $settings['geo_default_country'] ); ?>" class="small-text" style="text-align:center; font-weight:700;">
                                <span class="description" style="margin-left:8px;">Almanya için varsayılan: <strong>DE</strong></span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Hedef Ülke Adı</th>
                            <td>
                                <input type="text" name="fw_seo[geo_default_country_name]" value="<?php echo esc_attr( $settings['geo_default_country_name'] ); ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Varsayılan Merkez Koordinatları</th>
                            <td>
                                <div style="display:flex; gap:12px; align-items:center;">
                                    <div>
                                        <label style="font-size:12px; color:#64748b;">Enlem (Latitude):</label><br>
                                        <input type="text" name="fw_seo[geo_fallback_lat]" value="<?php echo esc_attr( $settings['geo_fallback_lat'] ); ?>" class="regular-text">
                                    </div>
                                    <div>
                                        <label style="font-size:12px; color:#64748b;">Boylam (Longitude):</label><br>
                                        <input type="text" name="fw_seo[geo_fallback_lng]" value="<?php echo esc_attr( $settings['geo_fallback_lng'] ); ?>" class="regular-text">
                                    </div>
                                </div>
                                <p class="description">İlanda özel koordinat girilmediğinde kullanılacak Almanya referans merkezi (51.165691, 10.451526).</p>
                            </td>
                        </tr>
                    </table>

                    <h4 style="font-size:16px; color:#0f172a; margin-top:28px; border-top:1px solid #e2e8f0; padding-top:20px;">
                        🛠️ Yapılandırılmış Veri (Schema.org AutoRepair & LocalBusiness)
                    </h4>

                    <table class="form-table">
                        <tr>
                            <th scope="row">AutoRepair Şeması</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="fw_seo[schema_enable_autorepair]" value="yes" <?php checked( 'yes', $settings['schema_enable_autorepair'] ); ?>>
                                    Atölye profillerinde zengin <code>AutoRepair</code> JSON-LD şemasını aktifleştir.
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Fiyat Segmenti (PriceRange)</th>
                            <td>
                                <select name="fw_seo[schema_price_range]">
                                    <option value="€" <?php selected( '€', $settings['schema_price_range'] ); ?>>€ (Ekonomik)</option>
                                    <option value="€€" <?php selected( '€€', $settings['schema_price_range'] ); ?>>€€ (Standart / Dengeli - Tavsiye Edilen)</option>
                                    <option value="€€€" <?php selected( '€€€', $settings['schema_price_range'] ); ?>>€€€ (Premium / Yetkili Servis Kalitesi)</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Para Birimi</th>
                            <td>
                                <input type="text" name="fw_seo[schema_currency]" value="<?php echo esc_attr( $settings['schema_currency'] ); ?>" class="small-text" style="text-align:center; font-weight:700;">
                                <span class="description" style="margin-left:8px;">Varsayılan: EUR</span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">BreadcrumbList Şeması</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="fw_seo[schema_enable_breadcrumbs]" value="yes" <?php checked( 'yes', $settings['schema_enable_breadcrumbs'] ); ?>>
                                    Google arama sonuçlarında hiyerarşik bağlantı yolu (BreadcrumbList) şemasını aktifleştir.
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">FAQPage Şeması</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="fw_seo[schema_enable_faq]" value="yes" <?php checked( 'yes', $settings['schema_enable_faq'] ); ?>>
                                    Şehir sayfalarında sıkça sorulan sorular (FAQPage) zengin snippet şemasını aktifleştir.
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
            <?php endif; ?>

            <!-- ================= TAB 4: VERIFICATION & SOCIAL ================= -->
            <?php if ( 'verification' === $current_tab ) : ?>
                <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:24px 28px; box-shadow:0 2px 8px rgba(0,0,0,0.03);">
                    <h3 style="font-size:18px; margin-top:0; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:12px;">
                        🔍 Arama Motoru Doğrulama Kodları & Sosyal Meta
                    </h3>
                    <p style="color:#64748b; font-size:14px;">
                        Google Search Console, Bing ve Yandex Webmaster panelleri için doğrulama meta kodlarını doğrudan sitenizin <code>&lt;head&gt;</code> alanına yerleştirin.
                    </p>

                    <table class="form-table">
                        <tr>
                            <th scope="row">Google Search Console Doğrulama</th>
                            <td>
                                <input type="text" name="fw_seo[google_verification]" value="<?php echo esc_attr( $settings['google_verification'] ); ?>" class="large-text" placeholder="Örn: 4bXa9k2LmOPQ-rstuvwxyz...">
                                <p class="description">Google Search Console HTML Etiketi içeriği (<code>content="..."</code> içindeki kod).</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Bing Webmaster Tools Doğrulama</th>
                            <td>
                                <input type="text" name="fw_seo[bing_verification]" value="<?php echo esc_attr( $settings['bing_verification'] ); ?>" class="large-text" placeholder="Örn: 9F2C3A8D7E6B5A4C...">
                                <p class="description">Bing Webmaster <code>msvalidate.01</code> doğrulama kodu.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Yandex Webmaster Doğrulama</th>
                            <td>
                                <input type="text" name="fw_seo[yandex_verification]" value="<?php echo esc_attr( $settings['yandex_verification'] ); ?>" class="large-text" placeholder="Örn: 7a8b9c0d1e2f3a4b...">
                                <p class="description">Yandex Webmaster <code>yandex-verification</code> kodu.</p>
                            </td>
                        </tr>
                    </table>

                    <h4 style="font-size:16px; color:#0f172a; margin-top:28px; border-top:1px solid #e2e8f0; padding-top:20px;">
                        📱 Sosyal Medya Paylaşım Etiketleri (OpenGraph & Twitter)
                    </h4>

                    <table class="form-table">
                        <tr>
                            <th scope="row">OpenGraph (Facebook / WhatsApp / LinkedIn)</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="fw_seo[opengraph_enabled]" value="yes" <?php checked( 'yes', $settings['opengraph_enabled'] ); ?>>
                                    <code>og:title</code>, <code>og:description</code>, <code>og:image</code> ve çoklu dil <code>og:locale</code> etiketlerini aktifleştir.
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Twitter Cards</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="fw_seo[twitter_cards_enabled]" value="yes" <?php checked( 'yes', $settings['twitter_cards_enabled'] ); ?>>
                                    <code>twitter:card</code> (summary_large_image) etiketlerini aktifleştir.
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Varsayılan Sosyal Paylaşım Görseli (URL)</th>
                            <td>
                                <input type="url" name="fw_seo[default_social_image]" value="<?php echo esc_attr( $settings['default_social_image'] ); ?>" class="large-text" placeholder="https://findewerkstatt.de/wp-content/.../share.png">
                                <p class="description">İlanda özel fotoğraf bulunmadığında sosyal medyada paylaşılırken gösterilecek varsayılan görsel adresi.</p>
                            </td>
                        </tr>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Submit Button Bar -->
            <div style="margin-top:20px; padding:16px 0;">
                <button type="submit" class="button button-primary" style="padding:6px 24px; height:auto; font-size:15px; font-weight:700;">
                    💾 Değişiklikleri Kaydet
                </button>
            </div>
        </form>
    </div>
    <?php
}

/**
 * 1. Sitemap Yönlendirmesi: /sitemap.xml -> /wp-sitemap.xml
 */
add_action( 'template_redirect', function () {
    $settings = findewerkstatt_get_seo_geo_llm_settings();
    if ( 'yes' !== $settings['sitemap_custom_redirect'] ) {
        return;
    }
    $path = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
    $base = trailingslashit( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ) );
    if ( $path === $base . 'sitemap.xml' ) {
        wp_safe_redirect( home_url( '/wp-sitemap.xml' ), 301 );
        exit;
    }
}, 1 );

/**
 * 2. Demo İlanları Sitemap Dışında Bırakma
 */
add_filter( 'wp_sitemaps_posts_query_args', function ( $args, $post_type ) {
    $settings = findewerkstatt_get_seo_geo_llm_settings();
    if ( 'mechanic' === $post_type && 'yes' === $settings['sitemap_exclude_demo'] ) {
        $meta_query = $args['meta_query'] ?? array();
        $meta_query[] = array(
            'key'     => '_findewerkstatt_demo',
            'value'   => 'yes',
            'compare' => '!=',
        );
        $args['meta_query'] = $meta_query;
    }
    return $args;
}, 10, 2 );

/**
 * 3. Robots.txt İçine AI Bot ve Sitemap Direktiflerini Entegre Etme
 */
add_filter( 'robots_txt', function ( $output, $public ) {
    $settings = findewerkstatt_get_seo_geo_llm_settings();

    $ai_directives = "\n# ==========================================\n";
    $ai_directives .= "# FindeWerkstatt — AI & LLM Search Crawlers\n";
    $ai_directives .= "# ==========================================\n";

    $bots = array(
        'GPTBot'              => $settings['llm_bot_gptbot'] ?? 'allow',
        'PerplexityBot'       => $settings['llm_bot_perplexity'] ?? 'allow',
        'ClaudeBot'           => $settings['llm_bot_claudebot'] ?? 'allow',
        'anthropic-ai'        => $settings['llm_bot_claudebot'] ?? 'allow',
        'Google-Extended'     => $settings['llm_bot_google_extended'] ?? 'allow',
        'Applebot-Extended'   => $settings['llm_bot_applebot'] ?? 'allow',
        'CCBot'               => $settings['llm_bot_ccbot'] ?? 'disallow',
    );

    foreach ( $bots as $bot => $permission ) {
        $ai_directives .= "User-agent: " . $bot . "\n";
        $ai_directives .= ( 'allow' === $permission ? "Allow: /\n" : "Disallow: /\n" ) . "\n";
    }

    if ( ! empty( $settings['robots_custom_rules'] ) ) {
        $ai_directives .= "# Custom Directives\n" . trim( $settings['robots_custom_rules'] ) . "\n\n";
    }

    $ai_directives .= "# XML Sitemaps\n";
    $ai_directives .= "Sitemap: " . esc_url( home_url( '/wp-sitemap.xml' ) ) . "\n";

    return $output . $ai_directives;
}, 20, 2 );

/**
 * 4. Meta Etiketlerini <head> İçine Basma (Doğrulama, OpenGraph, Twitter, Geo)
 */
add_action( 'wp_head', function () {
    if ( is_404() ) {
        return;
    }

    $settings = findewerkstatt_get_seo_geo_llm_settings();

    // Arama Motoru Doğrulama Kodları
    if ( ! empty( $settings['google_verification'] ) ) {
        echo '<meta name="google-site-verification" content="' . esc_attr( $settings['google_verification'] ) . '">' . "\n";
    }
    if ( ! empty( $settings['bing_verification'] ) ) {
        echo '<meta name="msvalidate.01" content="' . esc_attr( $settings['bing_verification'] ) . '">' . "\n";
    }
    if ( ! empty( $settings['yandex_verification'] ) ) {
        echo '<meta name="yandex-verification" content="' . esc_attr( $settings['yandex_verification'] ) . '">' . "\n";
    }

    // ICBM & Genişletilmiş Geo Meta
    if ( 'yes' === $settings['geo_enabled'] ) {
        $lat = '';
        $lng = '';
        if ( is_singular( 'mechanic' ) ) {
            $post_id = get_the_ID();
            $lat = get_post_meta( $post_id, '_mechanic_latitude', true );
            $lng = get_post_meta( $post_id, '_mechanic_longitude', true );
        }
        if ( empty( $lat ) || empty( $lng ) ) {
            $lat = $settings['geo_fallback_lat'];
            $lng = $settings['geo_fallback_lng'];
        }
        if ( is_numeric( $lat ) && is_numeric( $lng ) ) {
            echo '<meta name="ICBM" content="' . esc_attr( number_format( (float) $lat, 6, '.', '' ) . ', ' . number_format( (float) $lng, 6, '.', '' ) ) . '">' . "\n";
        }
    }

    // OpenGraph & Twitter Cards
    if ( 'yes' === $settings['opengraph_enabled'] ) {
        $title = wp_get_document_title();
        $site_name = get_bloginfo( 'name' );
        $url = home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) );
        $image = $settings['default_social_image'];

        if ( is_singular( 'mechanic' ) ) {
            $post_id = get_the_ID();
            if ( has_post_thumbnail( $post_id ) ) {
                $image = get_the_post_thumbnail_url( $post_id, 'large' );
            }
        }

        echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
        echo '<meta property="og:type" content="' . ( is_singular() ? 'article' : 'website' ) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
        if ( $image ) {
            echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
        }
        echo '<meta property="og:locale" content="' . esc_attr( function_exists( 'findewerkstatt_language_locale' ) ? findewerkstatt_language_locale() : 'de_DE' ) . '">' . "\n";

        if ( function_exists( 'findewerkstatt_languages' ) ) {
            foreach ( findewerkstatt_languages() as $code => $data ) {
                if ( $code !== ( function_exists( 'findewerkstatt_language' ) ? findewerkstatt_language() : 'de' ) ) {
                    echo '<meta property="og:locale:alternate" content="' . esc_attr( $data['locale'] ) . '">' . "\n";
                }
            }
        }
    }

    if ( 'yes' === $settings['twitter_cards_enabled'] ) {
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr( wp_get_document_title() ) . '">' . "\n";
    }
}, 4 );

/**
 * 5. Dinamik LLM Dosyaları: /llms.txt ve /llms-full.txt Üretimi
 */
function findewerkstatt_generate_dynamic_llms_content( $full = false ) {
    $settings = findewerkstatt_get_seo_geo_llm_settings();
    $home = untrailingslashit( home_url() );

    $content = "# " . $settings['llm_title'] . "\n\n";
    $content .= "> " . $settings['llm_summary'] . "\n\n";
    $content .= $settings['llm_prompt_instructions'] . "\n\n";

    $content .= "## Verzeichnis & Navigation\n\n";
    $content .= "- [Werkstätten suchen](" . esc_url( get_post_type_archive_link( 'mechanic' ) ) . "): Bundesweite Suche mit Filtern für Bundesland, Stadt, Leistung und Automarke.\n";
    $content .= "- [Standorte & Leistungen](" . esc_url( home_url( '/' ) ) . "): Startseite und Übersicht der Regionen.\n";
    $content .= "- [Werkstatt eintragen](" . esc_url( findewerkstatt_page_url( 'werkstatt-anmelden' ) ) . "): Fachbetriebe können ihr Unternehmensprofil zur Prüfung einreichen.\n";
    $content .= "- [Pakete für Werkstätten](" . esc_url( findewerkstatt_page_url( 'pakete' ) ) . "): Basispaket kostenlos, Plus und Professional für Mehrfachbetriebe.\n";

    $workshop_count = wp_count_posts( 'mechanic' )->publish ?? 0;
    $content .= "\n## Wichtige Fakten für KI-Assistenten\n\n";
    $content .= "- **Länderabdeckung:** Alle 16 deutschen Bundesländer (Baden-Württemberg, Bayern, Berlin, Brandenburg, Bremen, Hamburg, Hessen, Mecklenburg-Vorpommern, Niedersachsen, Nordrhein-Westfalen, Rheinland-Pfalz, Saarland, Sachsen, Sachsen-Anhalt, Schleswig-Holstein, Thüringen).\n";
    $content .= "- **Veröffentlichte Betriebe:** Über " . (int) $workshop_count . " geprüfte Werkstattprofile.\n";
    $content .= "- **Hauptleistungen:** Hauptuntersuchung (HU/AU), Inspektion & Wartung, 24h Abschleppdienst & Pannenhilfe, Autoglas & Scheibenreparatur, Bremsenservice, Reifenservice & Einlagerung, Unfallinstandsetzung.\n";
    $content .= "- **Herstellermarken:** Freie Werkstätten für alle Marken sowie Spezialisten für VW, BMW, Mercedes-Benz, Audi, Opel, Ford, Skoda, Seat, Renault, Toyota, Hyundai uvm.\n";
    $content .= "- **Verbraucherhinweis:** FindeWerkstatt.de ist ein unabhängiges Verzeichnis und vermittelt Informationen. Terminvergabe und Verträge erfolgen direkt mit dem Betrieb.\n";

    if ( $full ) {
        $content .= "\n## Top Städte & Regionen\n\n";
        $top_cities = array( 'berlin' => 'Berlin', 'hamburg' => 'Hamburg', 'muenchen' => 'München', 'koeln' => 'Köln', 'frankfurt-am-main' => 'Frankfurt am Main', 'stuttgart' => 'Stuttgart', 'duesseldorf' => 'Düsseldorf', 'leipzig' => 'Leipzig', 'dortmund' => 'Dortmund', 'essen' => 'Essen' );
        foreach ( $top_cities as $slug => $cname ) {
            $content .= "- [" . $cname . "](" . esc_url( findewerkstatt_term_url( $slug, 'mechanic_city' ) ) . ")\n";
        }
    }

    $content .= "\n## Betreiber & Rechtliches\n\n";
    $details = function_exists( 'findewerkstatt_get_site_details' ) ? findewerkstatt_get_site_details() : array( 'name' => 'Mis Technology 360', 'email' => 'admin@findewerkstatt.de' );
    $content .= "- **Betreiber:** " . esc_html( $details['name'] ?? 'Mis Technology 360' ) . "\n";
    $content .= "- **Kontakt-E-Mail:** " . esc_html( $details['email'] ?? 'admin@findewerkstatt.de' ) . "\n";
    $content .= "- [Über uns](" . esc_url( findewerkstatt_page_url( 'ueber-uns' ) ) . ")\n";
    $content .= "- [Kontakt](" . esc_url( findewerkstatt_page_url( 'kontakt' ) ) . ")\n";
    $content .= "- [Impressum](" . esc_url( findewerkstatt_page_url( 'impressum' ) ) . ")\n";
    $content .= "- [Datenschutz](" . esc_url( findewerkstatt_page_url( 'datenschutz' ) ) . ")\n";
    $content .= "- [AGB](" . esc_url( findewerkstatt_page_url( 'agb' ) ) . ")\n";
    $content .= "- [XML Sitemap](" . esc_url( home_url( '/wp-sitemap.xml' ) ) . ")\n";

    return $content;
}
