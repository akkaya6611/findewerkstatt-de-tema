<?php
/**
 * OtoTamirciBul - Yeni Üye Karşılama Anketi (Onboarding Survey) & Yönetim Analitiği
 * 
 * Özellikler:
 * 1. Yeni üyeler için 4 adımlı, modern SaaS karşılama anketi AJAX işleyicisi
 * 2. Araç sahibi üyeler için seçilen marka/modeli doğrudan 'Dijital Araç Garajı'na otomatik ekleme
 * 3. Marka seçildiğinde alt modelleri döndüren AJAX uç noktası (wp_ajax_ototamir_get_models_by_brand)
 * 4. WP-Admin > Kullanıcılar > 📊 Üye Anketleri yönetim ve analiz gösterge paneli
 * 
 * @package OtoTamirciBul
 * @version 1.4.36
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OtoTamir_Survey_Analytics {

    const OPTION_SUMMARY = 'ototamir_survey_aggregated_stats';

    public static function init() {
        // AJAX İşleyicileri
        add_action( 'wp_ajax_ototamir_submit_onboarding_survey', array( __CLASS__, 'ajax_submit_survey' ) );
        add_action( 'wp_ajax_ototamir_dismiss_onboarding_survey', array( __CLASS__, 'ajax_dismiss_survey' ) );
        
        // Marka bazlı model getirme (hem oturumlu hem oturumsuz)
        add_action( 'wp_ajax_ototamir_get_models_by_brand', array( __CLASS__, 'ajax_get_models_by_brand' ) );
        add_action( 'wp_ajax_nopriv_ototamir_get_models_by_brand', array( __CLASS__, 'ajax_get_models_by_brand' ) );

        // Admin Menüsü
        add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
    }

    /**
     * Admin Menü
     */
    public static function add_admin_menu() {
        add_submenu_page(
            'users.php',
            __( 'Üye Anketleri & Kullanıcı Analitiği', 'ototamir' ),
            __( '📊 Üye Anketleri', 'ototamir' ),
            'manage_options',
            'ototamir-surveys',
            array( __CLASS__, 'render_admin_page' )
        );

        add_submenu_page(
            'edit.php?post_type=mechanic',
            __( 'Üye Anketleri & Kullanıcı Analitiği', 'ototamir' ),
            __( '📊 Üye Anketleri', 'ototamir' ),
            'manage_options',
            'ototamir-surveys',
            array( __CLASS__, 'render_admin_page' )
        );

        add_submenu_page(
            'ototamir-indexnow',
            __( 'Üye Anketleri & Analiz', 'ototamir' ),
            __( '📊 Üye Anketleri', 'ototamir' ),
            'manage_options',
            'ototamir-surveys',
            array( __CLASS__, 'render_admin_page' )
        );
    }

    /**
     * AJAX: Markaya Göre Modelleri Listele
     */
    public static function ajax_get_models_by_brand() {
        $brand_raw = isset( $_REQUEST['brand'] ) ? sanitize_text_field( $_REQUEST['brand'] ) : '';
        if ( empty( $brand_raw ) ) {
            wp_send_json_success( array( 'models' => array() ) );
        }

        $brand_term = null;
        if ( is_numeric( $brand_raw ) ) {
            $brand_term = get_term( (int) $brand_raw, 'car_brand' );
        } else {
            $brand_term = get_term_by( 'slug', $brand_raw, 'car_brand' );
            if ( ! $brand_term ) {
                $brand_term = get_term_by( 'name', $brand_raw, 'car_brand' );
            }
        }

        if ( ! $brand_term || is_wp_error( $brand_term ) ) {
            wp_send_json_success( array( 'models' => array() ) );
        }

        $models = get_terms( array(
            'taxonomy'   => 'car_brand',
            'parent'     => $brand_term->term_id,
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ) );

        $formatted = array();
        if ( ! empty( $models ) && ! is_wp_error( $models ) ) {
            foreach ( $models as $m ) {
                $formatted[] = array(
                    'id'   => $m->term_id,
                    'name' => $m->name,
                    'slug' => $m->slug,
                );
            }
        }

        wp_send_json_success( array(
            'brand'  => $brand_term->name,
            'models' => $formatted,
        ) );
    }

    /**
     * AJAX: Anket Yanıtlarını Kaydet
     */
    public static function ajax_submit_survey() {
        check_ajax_referer( 'ototamir_survey_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'Lütfen önce giriş yapın.' ) );
        }

        $user_id = get_current_user_id();

        $role         = sanitize_text_field( $_POST['role'] ?? 'car_owner' );
        $brand_name   = sanitize_text_field( $_POST['brand'] ?? '' );
        $model_name   = sanitize_text_field( $_POST['model'] ?? '' );
        $city         = sanitize_text_field( $_POST['city'] ?? '' );
        $district     = sanitize_text_field( $_POST['district'] ?? '' );
        $source       = sanitize_text_field( $_POST['source'] ?? '' );
        $primary_need = sanitize_text_field( $_POST['primary_need'] ?? '' );
        $notes        = sanitize_textarea_field( $_POST['notes'] ?? '' );

        $survey_data = array(
            'role'         => $role,
            'brand'        => $brand_name,
            'model'        => $model_name,
            'city'         => $city,
            'district'     => $district,
            'source'       => $source,
            'primary_need' => $primary_need,
            'notes'        => $notes,
            'submitted_at' => time(),
        );

        // Kullanıcı Metalarını Güncelle
        update_user_meta( $user_id, '_ototamir_survey_completed', 1 );
        update_user_meta( $user_id, '_ototamir_survey_completed_at', time() );
        update_user_meta( $user_id, '_ototamir_survey_data', $survey_data );
        delete_user_meta( $user_id, '_ototamir_survey_snooze' );

        // Tarayıcı çerezi olarak da yaz (Sayfa önbelleği veya oturumlar arası kalıcı güvence)
        if ( ! headers_sent() ) {
            $cookie_path = defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/';
            $cookie_domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';
            @setcookie( 'ototamir_survey_completed_' . $user_id, '1', time() + ( 365 * DAY_IN_SECONDS ), $cookie_path, $cookie_domain );
            @setcookie( 'ototamir_survey_completed', '1', time() + ( 365 * DAY_IN_SECONDS ), $cookie_path, $cookie_domain );
        }

        // Şehir belirtildiyse profil şehrini güncelle
        if ( ! empty( $city ) ) {
            $existing_city = get_user_meta( $user_id, '_user_city', true );
            if ( empty( $existing_city ) ) {
                update_user_meta( $user_id, '_user_city', $city );
            }
        }

        // AKILLI ENTEGRASYON: Araç sahibi ise doğrudan Garaj'a aracı ekle!
        $auto_garage_added = false;
        if ( in_array( $role, array( 'car_owner', 'visitor' ) ) && ! empty( $brand_name ) ) {
            $garage = get_user_meta( $user_id, '_user_garage', true );
            if ( ! is_array( $garage ) ) {
                $garage = array();
            }

            // Garajda aynı marka/model var mı kontrol et
            $already_exists = false;
            foreach ( $garage as $v ) {
                if ( isset( $v['brand'] ) && strcasecmp( $v['brand'], $brand_name ) === 0 && ( empty( $model_name ) || ( isset( $v['model'] ) && strcasecmp( $v['model'], $model_name ) === 0 ) ) ) {
                    $already_exists = true;
                    break;
                }
            }

            if ( ! $already_exists ) {
                $vid = 'v_' . time() . '_' . wp_rand( 100, 999 );
                $garage[ $vid ] = array(
                    'id'         => $vid,
                    'plate'      => '',
                    'brand'      => $brand_name,
                    'model'      => $model_name,
                    'year'       => (int) date( 'Y' ),
                    'fuel'       => '',
                    'km'         => 0,
                    'current_km' => 0,
                    'logs'       => array(),
                    'notes'      => 'Üye anketinden otomatik eklendi.',
                    'created_at' => current_time( 'mysql' ),
                );
                update_user_meta( $user_id, '_user_garage', $garage );
                $auto_garage_added = true;
            }
        }

        // Toplu İstatistik Özetini Güncelle
        self::record_in_aggregated_stats( $user_id, $survey_data );

        wp_send_json_success( array(
            'message'           => 'Anket yanıtınız başarıyla kaydedildi. Teşekkür ederiz!',
            'auto_garage_added' => $auto_garage_added,
        ) );
    }

    /**
     * AJAX: Anketi Daha Sonraya Ertele
     */
    public static function ajax_dismiss_survey() {
        check_ajax_referer( 'ototamir_survey_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error();
        }

        $user_id = get_current_user_id();
        // 7 gün boyunca tekrar gösterme
        update_user_meta( $user_id, '_ototamir_survey_snooze', time() + ( 7 * DAY_IN_SECONDS ) );

        if ( ! headers_sent() ) {
            $cookie_path = defined( 'COOKIEPATH' ) && COOKIEPATH ? COOKIEPATH : '/';
            $cookie_domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';
            @setcookie( 'ototamir_survey_dismissed_' . $user_id, '1', time() + ( 7 * DAY_IN_SECONDS ), $cookie_path, $cookie_domain );
        }

        wp_send_json_success();
    }

    /**
     * Global İstatistikleri Güncelle
     */
    private static function record_in_aggregated_stats( $user_id, $data ) {
        $stats = get_option( self::OPTION_SUMMARY, array(
            'total_submissions' => 0,
            'roles'             => array(),
            'brands'            => array(),
            'cities'            => array(),
            'sources'           => array(),
            'needs'             => array(),
            'recent'            => array(),
        ) );

        $stats['total_submissions'] = (int) ( $stats['total_submissions'] ?? 0 ) + 1;

        $role = $data['role'] ?: 'unspecified';
        $stats['roles'][ $role ] = ( $stats['roles'][ $role ] ?? 0 ) + 1;

        if ( ! empty( $data['brand'] ) ) {
            $b = $data['brand'];
            $stats['brands'][ $b ] = ( $stats['brands'][ $b ] ?? 0 ) + 1;
        }

        if ( ! empty( $data['city'] ) ) {
            $c = $data['city'];
            $stats['cities'][ $c ] = ( $stats['cities'][ $c ] ?? 0 ) + 1;
        }

        if ( ! empty( $data['source'] ) ) {
            $s = $data['source'];
            $stats['sources'][ $s ] = ( $stats['sources'][ $s ] ?? 0 ) + 1;
        }

        if ( ! empty( $data['primary_need'] ) ) {
            $n = $data['primary_need'];
            $stats['needs'][ $n ] = ( $stats['needs'][ $n ] ?? 0 ) + 1;
        }

        $user_info = get_userdata( $user_id );
        $recent_entry = array(
            'user_id'   => $user_id,
            'user_name' => $user_info ? $user_info->display_name : 'Kullanıcı #' . $user_id,
            'email'     => $user_info ? $user_info->user_email : '',
            'role'      => $data['role'],
            'brand'     => $data['brand'],
            'model'     => $data['model'],
            'city'      => $data['city'],
            'source'    => $data['source'],
            'need'      => $data['primary_need'],
            'time'      => time(),
        );

        if ( ! isset( $stats['recent'] ) || ! is_array( $stats['recent'] ) ) {
            $stats['recent'] = array();
        }
        array_unshift( $stats['recent'], $recent_entry );
        $stats['recent'] = array_slice( $stats['recent'], 0, 50 ); // Son 50 anket

        update_option( self::OPTION_SUMMARY, $stats );
    }

    /**
     * WP-Admin Anket Yönetim ve İstatistik Sayfası
     */
    public static function render_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Yetkisiz erişim.' );
        }

        $stats = get_option( self::OPTION_SUMMARY, array(
            'total_submissions' => 0,
            'roles'             => array(),
            'brands'            => array(),
            'cities'            => array(),
            'sources'           => array(),
            'needs'             => array(),
            'recent'            => array(),
        ) );

        $total = $stats['total_submissions'] ?? 0;
        $roles = $stats['roles'] ?? array();
        $brands = $stats['brands'] ?? array();
        arsort( $brands );
        $cities = $stats['cities'] ?? array();
        arsort( $cities );
        $sources = $stats['sources'] ?? array();
        $recent = $stats['recent'] ?? array();

        $role_labels = array(
            'car_owner'         => '🚗 Araç Sahibi',
            'mechanic'          => '🔧 Sanayi Esnafı / Usta',
            'towing'            => '🛞 Çekici & Kurtarıcı',
            'visitor'           => '🔍 Fiyat / Bilgi Araştıran',
        );

        $source_labels = array(
            'google'       => 'Google Arama',
            'social_media' => 'Sosyal Medya',
            'sanayi'       => 'Sanayi Sitesi Tavsiyesi',
            'friend'       => 'Arkadaş Tavsiyesi',
            'other'        => 'Diğer',
        );
        ?>
        <div class="wrap" style="max-width: 1180px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
            
            <!-- HEADER BANNER -->
            <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white; padding: 26px 32px; border-radius: 16px; margin: 20px 0 26px; box-shadow: 0 10px 30px rgba(15,23,42,0.15);">
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
                    <div>
                        <div style="display:inline-flex; align-items:center; gap:8px; background:rgba(249,115,22,0.2); color:#fed7aa; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700; margin-bottom:8px;">
                            <span style="width:8px; height:8px; border-radius:50%; background:#22c55e;"></span> Üye İstihbarat & İçgörü Motoru
                        </div>
                        <h1 style="margin:0 0 6px; font-size:26px; font-weight:900; color:white; letter-spacing:-0.4px;">
                            📊 Yeni Üye Karşılama Anketleri & İstatistikler
                        </h1>
                        <p style="margin:0; font-size:14px; color:#94a3b8; max-width:680px;">
                            Sitenize kayıt olan üyelerin araç sahipliği durumu, kullandıkları otomobil markaları, hizmet verdikleri alanlar ve platformdan beklentileri.
                        </p>
                    </div>
                    <div style="text-align:right;">
                        <span style="font-size:32px; font-weight:900; color:#f97316; display:block; line-height:1;"><?php echo number_format_i18n( $total ); ?></span>
                        <span style="font-size:12px; color:#cbd5e1; font-weight:600;">Toplam Anket Yanıtı</span>
                    </div>
                </div>
            </div>

            <!-- 4'LÜ KPI KARTLARI -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(230px, 1fr)); gap:18px; margin-bottom:26px;">
                <div style="background:white; border:1px solid #e2e8f0; border-radius:14px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,0.02);">
                    <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">🚗 Araç Sahibi Oranı</div>
                    <div style="font-size:24px; font-weight:900; color:#0f172a; margin:6px 0;">
                        <?php 
                        $car_owner_count = $roles['car_owner'] ?? 0;
                        echo $total > 0 ? '%' . round( ($car_owner_count / $total) * 100 ) : '%0';
                        ?>
                    </div>
                    <div style="font-size:12.5px; color:#10b981; font-weight:600;"><?php echo intval( $car_owner_count ); ?> Üye</div>
                </div>

                <div style="background:white; border:1px solid #e2e8f0; border-radius:14px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,0.02);">
                    <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">🔧 Sanayi Esnafı / Usta</div>
                    <div style="font-size:24px; font-weight:900; color:#0f172a; margin:6px 0;">
                        <?php 
                        $mech_count = ( $roles['mechanic'] ?? 0 ) + ( $roles['towing'] ?? 0 );
                        echo $total > 0 ? '%' . round( ($mech_count / $total) * 100 ) : '%0';
                        ?>
                    </div>
                    <div style="font-size:12.5px; color:#0284c7; font-weight:600;"><?php echo intval( $mech_count ); ?> İşletme</div>
                </div>

                <div style="background:white; border:1px solid #e2e8f0; border-radius:14px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,0.02);">
                    <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">⭐ En Çok Aranan Marka</div>
                    <div style="font-size:20px; font-weight:900; color:#f97316; margin:6px 0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        <?php 
                        $top_brand = ! empty( $brands ) ? array_key_first( $brands ) : 'Henüz Yok';
                        echo esc_html( $top_brand );
                        ?>
                    </div>
                    <div style="font-size:12.5px; color:#64748b;">En popüler araç</div>
                </div>

                <div style="background:white; border:1px solid #e2e8f0; border-radius:14px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,0.02);">
                    <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;">📍 Lider Şehir</div>
                    <div style="font-size:20px; font-weight:900; color:#8b5cf6; margin:6px 0;">
                        <?php 
                        $top_city = ! empty( $cities ) ? array_key_first( $cities ) : 'Henüz Yok';
                        echo esc_html( $top_city );
                        ?>
                    </div>
                    <div style="font-size:12.5px; color:#64748b;">En yoğun üye ili</div>
                </div>
            </div>

            <!-- İKİ KOLONLU DAĞILIM TABLOSU -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:28px;">
                <!-- Marka Dağılımı -->
                <div style="background:white; border:1px solid #e2e8f0; border-radius:14px; padding:22px;">
                    <h3 style="margin:0 0 16px; font-size:16px; font-weight:800; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:10px;">
                        🚘 En Çok Kayıt Edilen Araç Markaları
                    </h3>
                    <?php if ( ! empty( $brands ) ) : ?>
                        <div style="display:flex; flex-direction:column; gap:10px;">
                            <?php 
                            $b_count = 0;
                            foreach ( $brands as $b_name => $b_total ) : 
                                if ( ++$b_count > 8 ) break;
                                $pct = $total > 0 ? round( ( $b_total / $total ) * 100 ) : 0;
                            ?>
                                <div>
                                    <div style="display:flex; justify-content:space-between; font-size:13.5px; font-weight:700; color:#1e293b; margin-bottom:4px;">
                                        <span><?php echo esc_html( $b_name ); ?></span>
                                        <span><?php echo intval( $b_total ); ?> <small style="color:#64748b; font-weight:400;">(%<?php echo $pct; ?>)</small></span>
                                    </div>
                                    <div style="height:6px; background:#f1f5f9; border-radius:3px; overflow:hidden;">
                                        <div style="width:<?php echo $pct; ?>%; height:100%; background:linear-gradient(90deg, #f97316, #ea580c); border-radius:3px;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p style="color:#94a3b8; font-size:14px;">Henüz marka verisi toplanmadı.</p>
                    <?php endif; ?>
                </div>

                <!-- Şehir & Katılım Kaynağı -->
                <div style="background:white; border:1px solid #e2e8f0; border-radius:14px; padding:22px;">
                    <h3 style="margin:0 0 16px; font-size:16px; font-weight:800; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:10px;">
                        🎯 Üyeler Nereden Geliyor?
                    </h3>
                    <?php if ( ! empty( $sources ) ) : ?>
                        <div style="display:flex; flex-direction:column; gap:10px;">
                            <?php foreach ( $sources as $s_key => $s_total ) : 
                                $s_label = $source_labels[ $s_key ] ?? ucfirst( $s_key );
                                $pct = $total > 0 ? round( ( $s_total / $total ) * 100 ) : 0;
                            ?>
                                <div>
                                    <div style="display:flex; justify-content:space-between; font-size:13.5px; font-weight:700; color:#1e293b; margin-bottom:4px;">
                                        <span><?php echo esc_html( $s_label ); ?></span>
                                        <span><?php echo intval( $s_total ); ?> <small style="color:#64748b; font-weight:400;">(%<?php echo $pct; ?>)</small></span>
                                    </div>
                                    <div style="height:6px; background:#f1f5f9; border-radius:3px; overflow:hidden;">
                                        <div style="width:<?php echo $pct; ?>%; height:100%; background:linear-gradient(90deg, #0284c7, #38bdf8); border-radius:3px;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p style="color:#94a3b8; font-size:14px;">Henüz kaynak verisi toplanmadı.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- SON DOLDURULAN ANKETLER TABLOSU -->
            <div style="background:white; border:1px solid #e2e8f0; border-radius:14px; padding:22px; box-shadow:0 2px 8px rgba(0,0,0,0.02);">
                <h3 style="margin:0 0 16px; font-size:17px; font-weight:800; color:#0f172a;">
                    📋 Son Doldurulan Anketler
                </h3>
                <?php if ( ! empty( $recent ) ) : ?>
                    <table class="wp-list-table widefat fixed striped" style="border:none;">
                        <thead>
                            <tr>
                                <th style="font-weight:700;">Üye</th>
                                <th style="font-weight:700;">Rol</th>
                                <th style="font-weight:700;">Araç (Marka / Model)</th>
                                <th style="font-weight:700;">Şehir</th>
                                <th style="font-weight:700;">Kaynak</th>
                                <th style="font-weight:700;">Tarih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $recent as $entry ) : ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html( $entry['user_name'] ); ?></strong><br>
                                        <small style="color:#64748b;"><?php echo esc_html( $entry['email'] ); ?></small>
                                    </td>
                                    <td>
                                        <span style="background:#f1f5f9; padding:3px 8px; border-radius:6px; font-size:12px; font-weight:700;">
                                            <?php echo esc_html( $role_labels[ $entry['role'] ] ?? $entry['role'] ); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ( ! empty( $entry['brand'] ) ) : ?>
                                            <strong><?php echo esc_html( $entry['brand'] ); ?></strong>
                                            <?php if ( ! empty( $entry['model'] ) ) : ?>
                                                - <span style="color:#f97316; font-weight:700;"><?php echo esc_html( $entry['model'] ); ?></span>
                                            <?php endif; ?>
                                        <?php else : ?>
                                            <span style="color:#94a3b8;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html( $entry['city'] ?: '—' ); ?></td>
                                    <td><?php echo esc_html( $source_labels[ $entry['source'] ] ?? ( $entry['source'] ?: '—' ) ); ?></td>
                                    <td><small><?php echo date_i18n( 'j F Y H:i', $entry['time'] ); ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p style="color:#64748b; font-size:14px; margin:0;">Henüz doldurulmuş bir üye anketi bulunmuyor.</p>
                <?php endif; ?>
            </div>

        </div>
        <?php
    }
}

OtoTamir_Survey_Analytics::init();
