<?php
/* Template Name: Profilim */

if ( ! is_user_logged_in() ) {
    wp_redirect( home_url('/giris') );
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Profil Güncelleme İşlemi (POST)
$update_success = '';
$update_error = '';

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['ototamir_update_profile_nonce'] ) ) {
    if ( wp_verify_nonce( $_POST['ototamir_update_profile_nonce'], 'ototamir_update_profile_action' ) ) {
        $display_name = sanitize_text_field( $_POST['display_name'] ?? '' );
        $user_email   = sanitize_email( $_POST['user_email'] ?? '' );
        $user_phone   = sanitize_text_field( $_POST['user_phone'] ?? '' );
        $pass1        = $_POST['pass1'] ?? '';
        $pass2        = $_POST['pass2'] ?? '';

        if ( empty( $display_name ) ) {
            $update_error = 'Ad Soyad alanı boş bırakılamaz.';
        } elseif ( ! is_email( $user_email ) ) {
            $update_error = 'Geçerli bir e-posta adresi giriniz.';
        } elseif ( email_exists( $user_email ) && email_exists( $user_email ) != $user_id ) {
            $update_error = 'Bu e-posta adresi başka bir hesap tarafından kullanılıyor.';
        } else {
            $userdata = array(
                'ID'           => $user_id,
                'display_name' => $display_name,
                'user_email'   => $user_email,
            );

            // Şifre Değişikliği
            if ( ! empty( $pass1 ) ) {
                if ( strlen( $pass1 ) < 6 ) {
                    $update_error = 'Şifre en az 6 karakter olmalıdır.';
                } elseif ( $pass1 !== $pass2 ) {
                    $update_error = 'Yeni şifreler birbiriyle uyuşmuyor.';
                } else {
                    $userdata['user_pass'] = $pass1;
                }
            }

            if ( empty( $update_error ) ) {
                wp_update_user( $userdata );
                update_user_meta( $user_id, '_user_phone', $user_phone );
                $update_success = 'Profil ve hesap bilgileriniz başarıyla güncellendi!';
                $current_user = wp_get_current_user();
            }
        }
    } else {
        $update_error = 'Güvenlik doğrulaması başarısız oldu. Lütfen sayfayı yenileyip tekrar deneyin.';
    }
}

// İlan Durum Değiştirme İşlemi (Yayından Kaldır / Tekrar Yayına Al)
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['ototamir_toggle_status_nonce'] ) ) {
    if ( wp_verify_nonce( $_POST['ototamir_toggle_status_nonce'], 'ototamir_toggle_status_action' ) ) {
        $mech_id       = intval( $_POST['mech_id'] ?? 0 );
        $status_action = sanitize_text_field( $_POST['status_action'] ?? '' );

        $target_mech = get_post( $mech_id );
        if ( $target_mech && $target_mech->post_type === 'mechanic' ) {
            $orig_author = (int) get_post_meta( $target_mech->ID, '_mechanic_original_author', true );
            $owner_meta  = (int) get_post_meta( $target_mech->ID, '_mechanic_owner_user_id', true );
            $is_owner    = ( (int) $target_mech->post_author === (int) $user_id || $orig_author === (int) $user_id || $owner_meta === (int) $user_id );
            $is_admin    = current_user_can( 'manage_options' );

            if ( $is_owner || $is_admin ) {
                if ( $status_action === 'unpublish' && $target_mech->post_status === 'publish' ) {
                    wp_update_post( array(
                        'ID'          => $mech_id,
                        'post_status' => 'draft',
                    ) );
                    $update_success = sprintf( '"%s" başlıklı ilanınız yayından kaldırıldı (taslak olarak saklanıyor).', esc_html( $target_mech->post_title ) );
                } elseif ( $status_action === 'publish' && $target_mech->post_status === 'draft' ) {
                    wp_update_post( array(
                        'ID'          => $mech_id,
                        'post_status' => 'publish',
                    ) );
                    $update_success = sprintf( '"%s" başlıklı ilanınız başarıyla tekrar yayına alındı!', esc_html( $target_mech->post_title ) );
                }
            } else {
                $update_error = 'Bu ilan üzerinde işlem yapma yetkiniz bulunmuyor.';
            }
        } else {
            $update_error = 'Geçersiz ilan seçimi.';
        }
    } else {
        $update_error = 'Güvenlik doğrulaması başarısız oldu. Lütfen tekrar deneyin.';
    }
}

// Garaj & Araç Bakım Defteri İşlemleri (POST)
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['ototamir_garage_nonce'] ) ) {
    if ( wp_verify_nonce( $_POST['ototamir_garage_nonce'], 'ototamir_garage_action' ) ) {
        $subaction = sanitize_text_field( $_POST['garage_subaction'] ?? '' );
        $garage = get_user_meta( $user_id, '_user_garage', true );
        if ( ! is_array( $garage ) ) {
            $garage = array();
        }

        if ( $subaction === 'add_vehicle' ) {
            $plate = sanitize_text_field( $_POST['v_plate'] ?? '' );
            $brand = sanitize_text_field( $_POST['v_brand'] ?? '' );
            $model = sanitize_text_field( $_POST['v_model'] ?? '' );
            $year  = intval( $_POST['v_year'] ?? 0 );
            $fuel  = sanitize_text_field( $_POST['v_fuel'] ?? '' );
            $km    = intval( $_POST['v_km'] ?? 0 );

            if ( ! empty( $plate ) && ! empty( $brand ) ) {
                $clean_plate = strtoupper( preg_replace( '/\s+/', ' ', trim( $plate ) ) );
                $vid = 'v_' . time() . '_' . wp_rand( 100, 999 );
                $garage[ $vid ] = array(
                    'id'         => $vid,
                    'plate'      => $clean_plate,
                    'brand'      => $brand,
                    'model'      => $model,
                    'year'       => $year,
                    'fuel'       => $fuel,
                    'current_km' => $km,
                    'created_at' => current_time( 'mysql' ),
                    'logs'       => array(),
                );
                update_user_meta( $user_id, '_user_garage', $garage );
                $update_success = 'Aracınız (' . $clean_plate . ') garajınıza başarıyla eklendi!';
            } else {
                $update_error = 'Lütfen en az Plaka ve Araç Markası alanlarını doldurun.';
            }
        } elseif ( $subaction === 'delete_vehicle' ) {
            $vid = sanitize_text_field( $_POST['v_id'] ?? '' );
            if ( isset( $garage[ $vid ] ) ) {
                $del_plate = $garage[ $vid ]['plate'] ?? '';
                unset( $garage[ $vid ] );
                update_user_meta( $user_id, '_user_garage', $garage );
                $update_success = 'Araç (' . $del_plate . ') garajınızdan kaldırıldı.';
            }
        } elseif ( $subaction === 'add_log' ) {
            $vid      = sanitize_text_field( $_POST['v_id'] ?? '' );
            $log_date = sanitize_text_field( $_POST['log_date'] ?? date('Y-m-d') );
            $log_km   = intval( $_POST['log_km'] ?? 0 );
            $log_type = sanitize_text_field( $_POST['log_type'] ?? 'Periyodik Bakım' );
            $mech_txt = sanitize_text_field( $_POST['log_mechanic'] ?? '' );
            $cost     = floatval( $_POST['log_cost'] ?? 0 );
            $next_km  = intval( $_POST['log_next_km'] ?? 0 );
            $notes    = sanitize_textarea_field( $_POST['log_notes'] ?? '' );

            if ( isset( $garage[ $vid ] ) ) {
                $lid = 'log_' . time() . '_' . wp_rand( 100, 999 );
                if ( ! isset( $garage[ $vid ]['logs'] ) || ! is_array( $garage[ $vid ]['logs'] ) ) {
                    $garage[ $vid ]['logs'] = array();
                }
                $garage[ $vid ]['logs'][ $lid ] = array(
                    'id'       => $lid,
                    'date'     => $log_date,
                    'km'       => $log_km,
                    'type'     => $log_type,
                    'mechanic' => $mech_txt,
                    'cost'     => $cost,
                    'next_km'  => $next_km,
                    'notes'    => $notes,
                );
                if ( $log_km > ( $garage[ $vid ]['current_km'] ?? 0 ) ) {
                    $garage[ $vid ]['current_km'] = $log_km;
                }
                update_user_meta( $user_id, '_user_garage', $garage );
                $update_success = 'Bakım ve servis kaydı başarıyla deftere işlendi!';
            }
        } elseif ( $subaction === 'delete_log' ) {
            $vid = sanitize_text_field( $_POST['v_id'] ?? '' );
            $lid = sanitize_text_field( $_POST['log_id'] ?? '' );
            if ( isset( $garage[ $vid ]['logs'][ $lid ] ) ) {
                unset( $garage[ $vid ]['logs'][ $lid ] );
                update_user_meta( $user_id, '_user_garage', $garage );
                $update_success = 'Bakım kaydı silindi.';
            }
        }
    } else {
        $update_error = 'Güvenlik doğrulaması başarısız oldu. Lütfen tekrar deneyin.';
    }
}

// 1. İstatistikleri ve İlanları Çek (Doğrudan Yazar veya Meta Sahip Çift Koruması)
global $wpdb;
$user_owned_ids = $wpdb->get_col( $wpdb->prepare(
    "SELECT DISTINCT ID FROM {$wpdb->posts} WHERE post_type = 'mechanic' AND (post_author = %d OR ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ('_mechanic_original_author', '_mechanic_owner_user_id') AND meta_value = %s))",
    $user_id,
    strval( $user_id )
) );

$total_mechs      = 0;
$published_count  = 0;
$total_views      = 0;
$total_calls      = 0;
$total_whatsapp   = 0;
$total_directions = 0;

if ( ! empty( $user_owned_ids ) ) {
    $id_placeholders = implode( ',', array_map( 'intval', $user_owned_ids ) );

    $status_counts = $wpdb->get_results(
        "SELECT post_status, COUNT(*) as cnt FROM {$wpdb->posts} WHERE ID IN ($id_placeholders) AND post_status IN ('publish','pending','draft') GROUP BY post_status"
    );

    if ( ! empty( $status_counts ) ) {
        foreach ( $status_counts as $sc ) {
            $total_mechs += (int) $sc->cnt;
            if ( $sc->post_status === 'publish' ) {
                $published_count = (int) $sc->cnt;
            }
        }
    }

    $total_views = (int) $wpdb->get_var(
        "SELECT SUM(CAST(pm.meta_value AS UNSIGNED)) FROM {$wpdb->postmeta} pm WHERE pm.post_id IN ($id_placeholders) AND pm.meta_key = '_mechanic_views_count'"
    );

    $total_calls = (int) $wpdb->get_var(
        "SELECT SUM(CAST(pm.meta_value AS UNSIGNED)) FROM {$wpdb->postmeta} pm WHERE pm.post_id IN ($id_placeholders) AND pm.meta_key = '_mechanic_call_count'"
    );

    $total_whatsapp = (int) $wpdb->get_var(
        "SELECT SUM(CAST(pm.meta_value AS UNSIGNED)) FROM {$wpdb->postmeta} pm WHERE pm.post_id IN ($id_placeholders) AND pm.meta_key = '_mechanic_whatsapp_count'"
    );

    $total_directions = (int) $wpdb->get_var(
        "SELECT SUM(CAST(pm.meta_value AS UNSIGNED)) FROM {$wpdb->postmeta} pm WHERE pm.post_id IN ($id_placeholders) AND pm.meta_key = '_mechanic_directions_count'"
    );
}

$total_leads_user = $total_calls + $total_whatsapp + $total_directions;

// 2. Kullanıcının İlanlarını Sayfalı Çek (Sayfa başına 10 ilan - Anında açılış)
$paged = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ), isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1 );
$user_mechs_query = new WP_Query( array(
    'post_type'              => 'mechanic',
    'post__in'               => ! empty( $user_owned_ids ) ? $user_owned_ids : array( 0 ),
    'post_status'            => array( 'publish', 'pending', 'draft' ),
    'posts_per_page'         => 10,
    'paged'                  => $paged,
    'update_post_term_cache' => true,
    'update_post_meta_cache' => true,
) );

// Analitik ve Görüntüleme Dökümü İçin Tüm İlanları Çek
$analytics_mechs = ! empty( $user_owned_ids ) ? get_posts( array(
    'post_type'      => 'mechanic',
    'post__in'       => $user_owned_ids,
    'post_status'    => array( 'publish', 'pending', 'draft' ),
    'posts_per_page' => 100,
    'orderby'        => 'meta_value_num',
    'meta_key'       => '_mechanic_views_count',
    'order'          => 'DESC',
) ) : array();
$avg_views_per_mech = $total_mechs > 0 ? round( $total_views / $total_mechs ) : 0;
$top_mech_post      = ! empty( $analytics_mechs ) ? $analytics_mechs[0] : null;
$top_mech_views     = $top_mech_post ? (int) get_post_meta( $top_mech_post->ID, '_mechanic_views_count', true ) : 0;

// Favorileri Çek
$favorites = get_user_meta( $user_id, '_user_favorites', true );
if ( ! is_array( $favorites ) ) {
    $favorites = array();
}
$fav_count = count( $favorites );

// Garajdaki Araçları Çek
$garage_vehicles = get_user_meta( $user_id, '_user_garage', true );
if ( ! is_array( $garage_vehicles ) ) {
    $garage_vehicles = array();
}
$garage_count = count( $garage_vehicles );

// Canlı Destek & Mesajlaşma Değişkenleri
$unread_support_count = function_exists( 'ototamir_count_unread_support_for_user' ) ? ototamir_count_unread_support_for_user( $user_id ) : 0;
$req_listing_id = isset( $_GET['listing_id'] ) ? intval( $_GET['listing_id'] ) : 0;
$req_listing_title = '';
if ( $req_listing_id > 0 ) {
    $listing_post = get_post( $req_listing_id );
    if ( $listing_post ) {
        $req_listing_title = $listing_post->post_title;
    }
}

// Onboarding Karşılama Anketi Değişkenleri
$user_survey_completed = get_user_meta( $user_id, '_ototamir_survey_completed', true );
$cookie_completed      = isset( $_COOKIE['ototamir_survey_completed_' . $user_id] ) || isset( $_COOKIE['ototamir_survey_completed'] );
if ( $cookie_completed && empty( $user_survey_completed ) ) {
    update_user_meta( $user_id, '_ototamir_survey_completed', 1 );
    $user_survey_completed = 1;
}

$user_survey_snooze    = get_user_meta( $user_id, '_ototamir_survey_snooze', true );
$cookie_dismissed      = isset( $_COOKIE['ototamir_survey_dismissed_' . $user_id] );
$should_show_survey    = ( empty( $user_survey_completed ) && ! $cookie_completed && ! $cookie_dismissed && ( empty( $user_survey_snooze ) || (int) $user_survey_snooze < time() ) );
$survey_nonce          = wp_create_nonce( 'ototamir_survey_nonce' );

get_header();
?>

<style>
/* ===================================================
   PROFİL SAYFASI ULTRA MODERN SAAS DASHBOARD STİLLERİ
=================================================== */
.profile-dashboard {
    background: #f8fafc;
    min-height: 85vh;
    padding: 28px 0 80px;
}
.profile-dashboard .container {
    max-width: 1340px;
    width: 100%;
    margin-left: auto;
    margin-right: auto;
    padding-left: 20px;
    padding-right: 20px;
    box-sizing: border-box;
}

/* 1. ÜST SAAS ADMIN TOPBAR KARTI */
.admin-topbar-card {
    background: linear-gradient(135deg, #0b1329 0%, #1e293b 60%, #0f172a 100%);
    border-radius: 20px;
    padding: 26px 32px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 24px;
    box-shadow: 0 16px 36px -8px rgba(15, 23, 42, 0.3), inset 0 1px 0 0 rgba(255, 255, 255, 0.1);
    margin-bottom: 24px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    position: relative;
    overflow: hidden;
}
.admin-topbar-card::before {
    content: "";
    position: absolute;
    top: -60px;
    right: 15%;
    width: 260px;
    height: 260px;
    background: radial-gradient(circle, rgba(249, 115, 22, 0.15) 0%, rgba(0, 0, 0, 0) 70%);
    pointer-events: none;
}
.admin-topbar-card::after {
    content: "";
    position: absolute;
    bottom: -60px;
    left: 20%;
    width: 220px;
    height: 220px;
    background: radial-gradient(circle, rgba(56, 189, 248, 0.12) 0%, rgba(0, 0, 0, 0) 70%);
    pointer-events: none;
}
.admin-topbar-user {
    display: flex;
    align-items: center;
    gap: 20px;
    position: relative;
    z-index: 1;
}
.admin-avatar {
    width: 72px;
    height: 72px;
    border-radius: 18px;
    background: linear-gradient(135deg, #f97316 0%, #ea580c 45%, #c026d3 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    font-weight: 900;
    color: white;
    box-shadow: 0 8px 20px rgba(249, 115, 22, 0.35), 0 0 0 2.5px rgba(255, 255, 255, 0.2);
    flex-shrink: 0;
    position: relative;
}
.admin-avatar .avatar-status-dot {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 14px;
    height: 14px;
    background: #22c55e;
    border: 2.5px solid #0f172a;
    border-radius: 50%;
    box-shadow: 0 0 10px rgba(34, 197, 94, 0.9);
}
.admin-user-details {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.admin-user-title-row {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.admin-user-title-row h1 {
    font-size: 24px;
    font-weight: 900;
    margin: 0;
    color: #ffffff;
    letter-spacing: -0.4px;
}
.admin-account-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    backdrop-filter: blur(8px);
}
.admin-account-badge.badge-verified {
    background: rgba(34, 197, 94, 0.15);
    color: #86efac;
    border: 1px solid rgba(34, 197, 94, 0.3);
}
.admin-account-badge.badge-user {
    background: rgba(255, 255, 255, 0.1);
    color: #cbd5e1;
    border: 1px solid rgba(255, 255, 255, 0.16);
}
.admin-id-pill {
    font-size: 11px;
    font-weight: 800;
    color: #94a3b8;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: 3px 8px;
    border-radius: 8px;
    letter-spacing: 0.5px;
}
.admin-user-subline {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13.5px;
    color: #cbd5e1;
    flex-wrap: wrap;
}
.admin-user-subline span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.subline-bullet {
    color: #64748b;
}
.admin-topbar-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
    position: relative;
    z-index: 1;
}
.btn-admin-cta {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: white;
    padding: 11px 22px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 14px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 16px rgba(234, 88, 12, 0.35);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.2s ease;
    white-space: nowrap;
}
.btn-admin-cta:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(234, 88, 12, 0.45);
    color: white;
}
.btn-admin-logout {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: rgba(239, 68, 68, 0.12);
    color: #fca5a5;
    border: 1px solid rgba(239, 68, 68, 0.25);
    padding: 11px 18px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 13.5px;
    text-decoration: none;
    transition: all 0.2s ease;
    backdrop-filter: blur(8px);
    white-space: nowrap;
}
.btn-admin-logout:hover {
    background: #ef4444;
    color: white;
    border-color: #ef4444;
    box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35);
    transform: translateY(-2px);
}

/* 2. ADMIN 2-COLUMN LAYOUT */
.admin-layout-grid {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 28px;
    align-items: start;
}
.admin-sidebar {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 16px;
    box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);
    position: sticky;
    top: 90px;
    z-index: 20;
}
.sidebar-section-title {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.8px;
    color: #94a3b8;
    text-transform: uppercase;
    padding: 8px 12px 6px;
    margin-bottom: 4px;
}
.admin-sidebar-menu {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.admin-sidebar-menu .tab-btn {
    width: 100%;
    text-align: left;
    padding: 11px 14px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 700;
    color: #475569;
    background: transparent;
    border: none;
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    font-family: inherit;
    box-sizing: border-box;
}
.admin-sidebar-menu .tab-btn:hover {
    background: #f8fafc;
    color: #0f172a;
    transform: translateX(3px);
}
.admin-sidebar-menu .tab-btn.active {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.2);
}
.tab-btn-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #f1f5f9;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
    transition: all 0.2s;
}
.admin-sidebar-menu .tab-btn:hover .tab-btn-icon {
    background: #e2e8f0;
    color: #0f172a;
}
.admin-sidebar-menu .tab-btn.active .tab-btn-icon {
    background: rgba(255, 255, 255, 0.15);
    color: #fb923c;
}
.tab-btn-text {
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.tab-btn-badge {
    background: #f1f5f9;
    color: #475569;
    font-size: 11.5px;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 12px;
    transition: all 0.2s;
}
.admin-sidebar-menu .tab-btn.active .tab-btn-badge {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
}
.tab-btn-tag {
    font-size: 10.5px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 8px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.support-tab-badge {
    background: #ef4444;
    color: #ffffff;
    font-size: 11px;
    font-weight: 800;
    min-width: 20px;
    height: 20px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 6px;
    animation: pulseAlert 2s infinite;
}
.admin-sidebar-promo {
    margin-top: 18px;
    padding: 16px;
    background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
    border: 1px solid #fed7aa;
    border-radius: 14px;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}
.promo-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #ea580c;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}
.promo-content h4 {
    font-size: 13px;
    font-weight: 800;
    color: #9a3412;
    margin: 0 0 4px;
}
.promo-content p {
    font-size: 11.5px;
    color: #c2410c;
    margin: 0 0 10px;
    line-height: 1.4;
}
.btn-promo-action {
    background: #ea580c;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 6px 12px;
    font-size: 11.5px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
}
.btn-promo-action:hover {
    background: #c2410c;
}

/* 3. MAIN CANVAS & TOP KPIS */
.admin-main-canvas {
    min-width: 0;
}
.admin-kpi-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
.admin-kpi-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 18px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.02);
    cursor: pointer;
    transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}
.admin-kpi-card::after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: transparent;
    transition: all 0.22s ease;
}
.admin-kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px -4px rgba(15, 23, 42, 0.08);
    border-color: #cbd5e1;
}
.admin-kpi-card[data-open-tab="tab-firms"]:hover::after     { background: #ea580c; }
.admin-kpi-card[data-open-tab="tab-analytics"]:hover::after { background: #0284c7; }
.admin-kpi-card[data-open-tab="tab-garage"]:hover::after    { background: #16a34a; }
.admin-kpi-card[data-open-tab="tab-support"]:hover::after   { background: #a855f7; }

.kpi-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.kpi-icon.orange { background: #fff7ed; color: #ea580c; }
.kpi-icon.blue   { background: #f0f9ff; color: #0284c7; }
.kpi-icon.green  { background: #f0fdf4; color: #16a34a; }
.kpi-icon.purple { background: #faf5ff; color: #7c3aed; }

.kpi-data {
    flex: 1;
    min-width: 0;
}
.kpi-label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.kpi-value {
    display: block;
    font-size: 20px;
    font-weight: 900;
    color: #0f172a;
    line-height: 1.1;
    letter-spacing: -0.3px;
}
.kpi-arrow {
    color: #cbd5e1;
    font-size: 12px;
    transition: all 0.2s ease;
    margin-left: auto;
}
.admin-kpi-card:hover .kpi-arrow {
    color: #0f172a;
    transform: translateX(3px);
}

/* FIRMS TOOLBAR */
.firms-toolbar {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 14px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 18px;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.02);
}
.firms-toolbar-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.firms-toolbar-title {
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.firms-toolbar-title i {
    color: #ea580c;
}
.firms-count-badge {
    background: #f1f5f9;
    color: #475569;
    font-size: 12px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
}
.firms-toolbar-right {
    display: flex;
    align-items: center;
    gap: 12px;
}
.firms-search-box {
    position: relative;
    width: 240px;
}
.firms-search-box i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 13px;
    pointer-events: none;
}
.firms-search-box input {
    width: 100%;
    padding: 9px 12px 9px 34px;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 13px;
    color: #0f172a;
    transition: all 0.2s;
    outline: none;
    box-sizing: border-box;
}
.firms-search-box input:focus {
    background: #ffffff;
    border-color: #ea580c;
    box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.12);
}
.btn-add-firm-inline {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: #ffffff;
    padding: 9px 16px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    box-shadow: 0 3px 10px rgba(234, 88, 12, 0.25);
    transition: all 0.2s;
}
.btn-add-firm-inline:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(234, 88, 12, 0.35);
    color: #ffffff;
}

/* RESPONSIVE LAYOUT BREAKPOINTS */
@media (max-width: 1024px) {
    .admin-layout-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    .admin-sidebar {
        position: static;
        padding: 10px;
    }
    .sidebar-section-title {
        display: none;
    }
    .admin-sidebar-menu {
        flex-direction: row;
        overflow-x: auto;
        padding-bottom: 4px;
        scrollbar-width: none;
    }
    .admin-sidebar-menu::-webkit-scrollbar {
        display: none;
    }
    .admin-sidebar-menu .tab-btn {
        width: auto;
        flex-shrink: 0;
        padding: 10px 14px;
    }
    .admin-sidebar-promo {
        display: none;
    }
    .admin-kpi-row {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 768px) {
    .admin-topbar-card {
        flex-direction: column;
        align-items: flex-start;
        padding: 20px;
        gap: 18px;
    }
    .admin-topbar-actions {
        width: 100%;
        justify-content: stretch;
    }
    .btn-admin-cta, .btn-admin-logout {
        flex: 1;
        justify-content: center;
    }
    .firms-toolbar {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }
    .firms-toolbar-right {
        flex-direction: column;
        align-items: stretch;
    }
    .firms-search-box {
        width: 100%;
    }
    .btn-add-firm-inline {
        justify-content: center;
    }
    .firm-item-card {
        flex-direction: column;
        align-items: flex-start;
        padding: 18px;
        gap: 16px;
    }
    .firm-actions {
        width: 100%;
        flex-wrap: wrap;
    }
    .firm-actions .btn-view-firm,
    .firm-actions .btn-edit-firm,
    .firm-actions .btn-unpublish-firm,
    .firm-actions .btn-publish-firm {
        flex: 1;
        justify-content: center;
        text-align: center;
    }
}
@media (max-width: 540px) {
    .admin-kpi-row {
        grid-template-columns: 1fr;
    }
}

/* 4. TAB İÇERİKLERİ */
.tab-pane {
    display: none;
}
.tab-pane.active {
    display: block;
    animation: fadeIn 0.25s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* FİRMA KARTLARI */
.firm-cards-list {
    display: flex;
    flex-direction: column;
    gap: 18px;
}
.firm-item-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 22px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.025);
    transition: all 0.25s ease;
}
.firm-item-card:hover {
    border-color: #cbd5e1;
    transform: translateY(-3px);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.06);
}
.firm-info-left {
    display: flex;
    align-items: center;
    gap: 22px;
}
.firm-thumb {
    width: 86px;
    height: 86px;
    border-radius: 14px;
    object-fit: cover;
    background: #e2e8f0;
    border: 2px solid #f1f5f9;
    flex-shrink: 0;
    transition: transform 0.3s ease;
}
.firm-item-card:hover .firm-thumb {
    transform: scale(1.04);
}
.firm-details h3 {
    font-size: 19px;
    font-weight: 900;
    margin: 0 0 8px;
    color: #0f172a;
    letter-spacing: -0.3px;
}
.firm-details h3 a {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s;
}
.firm-details h3 a:hover {
    color: #ea580c;
}
.firm-meta-row {
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 13.5px;
    color: #64748b;
    flex-wrap: wrap;
}
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12.5px;
    font-weight: 700;
}
.status-pill.publish {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
}
.status-pill.pending {
    background: #fffbeb;
    color: #b45309;
    border: 1px solid #fde68a;
}
.status-pill.draft {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}

.firm-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}
.btn-view-firm {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #0f172a;
    padding: 10px 18px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 700;
    font-size: 13.5px;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    transition: all 0.2s;
}
.btn-view-firm:hover {
    background: #0f172a;
    color: white;
    border-color: #0f172a;
    transform: translateY(-1px);
}
.btn-edit-firm {
    background: #fff7ed;
    color: #c2410c;
    border: 1.5px solid #fed7aa;
    padding: 10px 18px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 700;
    font-size: 13.5px;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    transition: all 0.2s;
}
.btn-edit-firm:hover {
    background: #ea580c;
    color: white;
    border-color: #ea580c;
    box-shadow: 0 4px 14px rgba(234, 88, 12, 0.25);
    transform: translateY(-1px);
}
.btn-unpublish-firm {
    background: #fef2f2;
    color: #dc2626;
    border: 1.5px solid #fecaca;
    padding: 10px 16px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 13.5px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
}
.btn-unpublish-firm:hover {
    background: #dc2626;
    color: white;
    border-color: #dc2626;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
    transform: translateY(-1px);
}
.btn-publish-firm {
    background: #f0fdf4;
    color: #15803d;
    border: 1.5px solid #bbf7d0;
    padding: 10px 16px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 13.5px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
}
.btn-publish-firm:hover {
    background: #16a34a;
    color: white;
    border-color: #16a34a;
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);
    transform: translateY(-1px);
}

/* ANALİTİK & GÖRÜNTÜLEMELER */
.analytics-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}
.analytics-kpi-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 24px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.025);
    transition: all 0.25s ease;
}
.analytics-kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 26px rgba(0, 0, 0, 0.06);
    border-color: #cbd5e1;
}
.analytics-kpi-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3.5px;
}
.analytics-kpi-card:nth-child(1)::before { background: linear-gradient(90deg, #0284c7, #38bdf8); }
.analytics-kpi-card:nth-child(2)::before { background: linear-gradient(90deg, #16a34a, #4ade80); }
.analytics-kpi-card:nth-child(3)::before { background: linear-gradient(90deg, #eab308, #facc15); }
.analytics-kpi-card:nth-child(4)::before { background: linear-gradient(90deg, #ea580c, #f97316); }

.analytics-kpi-title {
    font-size: 13px;
    color: #64748b;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.analytics-kpi-val {
    font-size: 28px;
    font-weight: 900;
    color: #0f172a;
    line-height: 1.1;
    margin-bottom: 6px;
    letter-spacing: -0.5px;
}
.analytics-kpi-sub {
    font-size: 13px;
    color: #94a3b8;
    font-weight: 500;
}
.analytics-table-wrap {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.025);
    margin-bottom: 30px;
}
.analytics-table-header {
    padding: 22px 28px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}
.analytics-table-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 900;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 10px;
}
.analytics-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}
.analytics-table th {
    background: #f8fafc;
    padding: 16px 24px;
    font-size: 13px;
    font-weight: 800;
    color: #475569;
    border-bottom: 1px solid #e2e8f0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.analytics-table td {
    padding: 18px 24px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 14.5px;
    color: #334155;
    vertical-align: middle;
    transition: background 0.15s;
}
.analytics-table tr:hover td {
    background: #fbfcfe;
}
.analytics-table tr:last-child td {
    border-bottom: none;
}
.analytics-progress-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 150px;
}
.analytics-progress-bar {
    flex: 1;
    height: 9px;
    background: #f1f5f9;
    border-radius: 20px;
    overflow: hidden;
}
.analytics-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6 0%, #0284c7 100%);
    border-radius: 20px;
    box-shadow: 0 1px 4px rgba(2, 132, 199, 0.3);
}
.vip-boost-card {
    background: radial-gradient(100% 120% at 0% 0%, #1e1b4b 0%, #0f172a 60%, #020617 100%);
    border-radius: 22px;
    padding: 34px 40px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 28px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    box-shadow: 0 16px 36px rgba(15, 23, 42, 0.2);
    position: relative;
    overflow: hidden;
}
.vip-boost-info h4 {
    margin: 0 0 8px;
    font-size: 20px;
    font-weight: 900;
    color: #fb923c;
    display: flex;
    align-items: center;
    gap: 10px;
}
.vip-boost-info p {
    margin: 0;
    color: #cbd5e1;
    font-size: 14.5px;
    max-width: 600px;
    line-height: 1.6;
}
.vip-boost-btn {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: white;
    padding: 13px 26px;
    border-radius: 14px;
    font-weight: 700;
    font-size: 14.5px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 9px;
    white-space: nowrap;
    transition: all 0.25s ease;
    box-shadow: 0 6px 20px rgba(234, 88, 12, 0.4);
}
.vip-boost-btn:hover {
    box-shadow: 0 8px 26px rgba(234, 88, 12, 0.55);
    transform: translateY(-2px);
    color: white;
}

/* YAPAY ZEKA SEKMESİ */
.profile-ai-hero {
    background: radial-gradient(120% 120% at 0% 0%, #2e1065 0%, #1e1b4b 50%, #0f172a 100%);
    border-radius: 22px;
    padding: 32px 38px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 26px;
    margin-bottom: 28px;
    border: 1px solid rgba(168, 85, 247, 0.25);
    box-shadow: 0 16px 36px rgba(46, 16, 101, 0.2);
    position: relative;
    overflow: hidden;
}
.profile-ai-hero::after {
    content: "";
    position: absolute;
    top: -50px;
    right: -50px;
    width: 220px;
    height: 220px;
    background: radial-gradient(circle, rgba(216, 180, 254, 0.2) 0%, transparent 70%);
    pointer-events: none;
}
.profile-ai-hero-info h3 {
    margin: 0 0 8px;
    font-size: 22px;
    font-weight: 900;
    color: #f3e8ff;
    display: flex;
    align-items: center;
    gap: 10px;
}
.profile-ai-hero-info p {
    margin: 0;
    color: #cbd5e1;
    font-size: 14.5px;
    max-width: 620px;
    line-height: 1.55;
}
.btn-open-full-ai {
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
    padding: 12px 22px;
    border-radius: 14px;
    font-weight: 700;
    font-size: 14px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
    transition: all 0.25s;
    backdrop-filter: blur(8px);
}
.btn-open-full-ai:hover {
    background: white;
    color: #1e1b4b;
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(255, 255, 255, 0.25);
}
.profile-ai-tool-wrap {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 22px;
    padding: 34px;
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.03);
}

/* PROFİL SAYFALAMA */
.profile-pagination {
    margin-top: 34px;
    display: flex;
    justify-content: center;
}
.profile-pagination ul.page-numbers {
    display: flex;
    align-items: center;
    gap: 8px;
    list-style: none;
    padding: 0;
    margin: 0;
    flex-wrap: wrap;
    justify-content: center;
}
.profile-pagination .page-numbers {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 44px;
    height: 44px;
    padding: 0 16px;
    border-radius: 14px;
    background: white;
    border: 1.5px solid #e2e8f0;
    color: #334155;
    font-weight: 800;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.2s ease;
}
.profile-pagination a.page-numbers:hover {
    border-color: #ea580c;
    color: #ea580c;
    background: #fff7ed;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(234, 88, 12, 0.18);
}
.profile-pagination .page-numbers.current {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    border-color: #ea580c;
    color: white;
    box-shadow: 0 4px 16px rgba(234, 88, 12, 0.35);
}

/* BOŞ DURUM (EMPTY STATE) */
.empty-box {
    background: white;
    border: 2px dashed #cbd5e1;
    border-radius: 24px;
    padding: 64px 24px;
    text-align: center;
    max-width: 580px;
    margin: 20px auto;
}
.empty-icon {
    width: 76px;
    height: 76px;
    background: #fff7ed;
    color: #ea580c;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    margin: 0 auto 20px;
    box-shadow: 0 4px 16px rgba(234, 88, 12, 0.15);
}
.empty-box h3 {
    font-size: 21px;
    font-weight: 900;
    margin: 0 0 8px;
    color: #0f172a;
}
.empty-box p {
    color: #64748b;
    font-size: 15px;
    margin: 0 0 26px;
    line-height: 1.6;
}

/* DİJİTAL ARAÇ GARAJI & BAKIM DEFTERİ */
.garage-top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 26px;
    flex-wrap: wrap;
    gap: 16px;
}
.btn-toggle-garage-form {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: white;
    padding: 13px 24px;
    border-radius: 14px;
    font-weight: 700;
    font-size: 14.5px;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 16px rgba(234, 88, 12, 0.3);
    transition: all 0.25s;
    font-family: inherit;
}
.btn-toggle-garage-form:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(234, 88, 12, 0.45);
}
.garage-form-card {
    background: white;
    border: 1.5px solid #fed7aa;
    border-radius: 20px;
    padding: 28px;
    margin-bottom: 30px;
    box-shadow: 0 6px 20px rgba(249, 115, 22, 0.08);
}
.garage-vehicles-list {
    display: flex;
    flex-direction: column;
    gap: 26px;
}
.garage-vehicle-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 22px;
    padding: 26px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    transition: all 0.2s;
}
.garage-vehicle-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 18px;
    border-bottom: 1px solid #f1f5f9;
    flex-wrap: wrap;
    gap: 14px;
}
.plate-badge-wrap {
    display: flex;
    align-items: center;
    gap: 16px;
}
.plate-badge {
    display: inline-flex;
    align-items: center;
    border: 2.5px solid #0f172a;
    border-radius: 8px;
    overflow: hidden;
    background: white;
    font-weight: 900;
    font-family: monospace, sans-serif;
    box-shadow: 0 3px 8px rgba(0,0,0,0.12);
}
.plate-tr {
    background: #003399;
    color: white;
    font-size: 11px;
    font-weight: 900;
    padding: 6px 7px;
    display: flex;
    flex-direction: column;
    align-items: center;
    line-height: 1;
}
.plate-text {
    padding: 5px 14px;
    font-size: 19px;
    color: #0f172a;
    letter-spacing: 1.5px;
}
.vehicle-specs {
    font-size: 17px;
    font-weight: 900;
    color: #0f172a;
}
.vehicle-sub-specs {
    font-size: 13.5px;
    color: #64748b;
    font-weight: 600;
    margin-top: 2px;
}
.garage-logs-table-wrap {
    overflow-x: auto;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
}
.garage-logs-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
    text-align: left;
}
.garage-logs-table th {
    background: #f8fafc;
    color: #475569;
    font-weight: 800;
    padding: 14px 16px;
    border-bottom: 1px solid #e2e8f0;
    white-space: nowrap;
}
.garage-logs-table td {
    padding: 14px 16px;
    border-bottom: 1px solid #f1f5f9;
    color: #1e293b;
}

/* AYARLAR FORMU */
.settings-box {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 22px;
    padding: 38px 44px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.025);
    max-width: 800px;
}
.settings-box h2 {
    font-size: 22px;
    font-weight: 900;
    margin: 0 0 24px;
    color: #0f172a;
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 16px;
}
.form-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}
.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 20px;
}
.form-group label {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
}
.form-group input {
    padding: 13px 18px;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    font-size: 15px;
    font-family: inherit;
    transition: all 0.2s;
    background: #f8fafc;
}
.form-group input:focus {
    outline: none;
    border-color: #ea580c;
    background: white;
    box-shadow: 0 0 0 4px rgba(234, 88, 12, 0.12);
}
.btn-save-settings {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: white;
    border: none;
    padding: 14px 34px;
    border-radius: 14px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(234, 88, 12, 0.35);
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 9px;
}
.btn-save-settings:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(234, 88, 12, 0.45);
}

/* BİLDİRİM KUTULARI */
.alert-box {
    padding: 16px 22px;
    border-radius: 16px;
    margin-bottom: 26px;
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 14.5px;
    font-weight: 700;
}
.alert-box.success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.alert-box.error   { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

/* CANLI DESTEK & MESAJLAŞMA SEKME VE CHAT STİLLERİ */
.support-tab-badge {
    background: #ea580c;
    color: white;
    font-size: 11px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 10px;
    margin-left: 6px;
    box-shadow: 0 2px 6px rgba(234, 88, 12, 0.4);
}
.support-chat-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 4px 25px rgba(0, 0, 0, 0.03);
    display: flex;
    flex-direction: column;
    min-height: 600px;
}
.support-chat-header {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    color: white;
    padding: 22px 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    flex-wrap: wrap;
    gap: 16px;
}
.support-agent-info {
    display: flex;
    align-items: center;
    gap: 16px;
}
.support-agent-avatar {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    box-shadow: 0 4px 14px rgba(234, 88, 12, 0.35);
    position: relative;
}
.support-online-dot {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 13px;
    height: 13px;
    background: #22c55e;
    border: 2px solid #0f172a;
    border-radius: 50%;
}
.support-agent-text h3 {
    margin: 0 0 4px 0;
    font-size: 17px;
    font-weight: 800;
    color: white;
    letter-spacing: -0.3px;
}
.support-agent-status {
    font-size: 13px;
    color: #94a3b8;
    display: flex;
    align-items: center;
    gap: 6px;
}
.support-agent-status i {
    color: #22c55e;
    font-size: 9px;
}
.support-badge-guarantee {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 12px;
    padding: 8px 14px;
    font-size: 12.5px;
    color: #cbd5e1;
    display: flex;
    align-items: center;
    gap: 8px;
}
.support-listing-ref-banner {
    background: #fff7ed;
    border-bottom: 1px solid #fed7aa;
    padding: 12px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 13.5px;
    color: #9a3412;
    flex-wrap: wrap;
    gap: 8px;
}
.support-listing-ref-banner a {
    color: #ea580c;
    font-weight: 700;
    text-decoration: none;
}
.support-listing-ref-banner a:hover {
    text-decoration: underline;
}
.support-messages-stream {
    flex: 1;
    overflow-y: auto;
    padding: 28px;
    background: #f8fafc;
    display: flex;
    flex-direction: column;
    gap: 16px;
    max-height: 480px;
    min-height: 300px;
}
.user-chat-bubble-wrap {
    display: flex;
    flex-direction: column;
    max-width: 78%;
}
.user-chat-bubble-wrap.from-user {
    align-self: flex-end;
}
.user-chat-bubble-wrap.from-admin {
    align-self: flex-start;
}
.user-chat-sender-tag {
    font-size: 11px;
    font-weight: 700;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.user-chat-bubble-wrap.from-user .user-chat-sender-tag {
    justify-content: flex-end;
    color: #ea580c;
}
.user-chat-bubble-wrap.from-admin .user-chat-sender-tag {
    color: #0f172a;
}
.user-chat-bubble {
    padding: 14px 18px;
    border-radius: 18px;
    font-size: 14.5px;
    line-height: 1.55;
    word-break: break-word;
    white-space: pre-wrap;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
}
.user-chat-bubble-wrap.from-user .user-chat-bubble {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: white;
    border-top-right-radius: 4px;
}
.user-chat-bubble-wrap.from-admin .user-chat-bubble {
    background: white;
    color: #1e293b;
    border: 1px solid #e2e8f0;
    border-top-left-radius: 4px;
}
.user-chat-time {
    font-size: 11px;
    color: #94a3b8;
    margin-top: 5px;
}
.user-chat-bubble-wrap.from-user .user-chat-time {
    text-align: right;
}
.support-quick-chips {
    padding: 12px 28px;
    background: #f1f5f9;
    border-top: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 8px;
    overflow-x: auto;
    white-space: nowrap;
}
.support-quick-label {
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 5px;
    flex-shrink: 0;
}
.btn-quick-chip {
    background: white;
    border: 1px solid #cbd5e1;
    border-radius: 20px;
    padding: 5px 12px;
    font-size: 12px;
    font-weight: 600;
    color: #334155;
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;
    font-family: inherit;
}
.btn-quick-chip:hover {
    background: #ea580c;
    color: white;
    border-color: #ea580c;
    transform: translateY(-1px);
}
.support-input-bar {
    padding: 20px 28px;
    background: white;
    border-top: 1px solid #e2e8f0;
}
.support-input-row {
    display: flex;
    gap: 12px;
    align-items: flex-end;
}
.support-input-row textarea {
    flex: 1;
    border: 1.5px solid #cbd5e1 !important;
    border-radius: 14px !important;
    padding: 12px 18px !important;
    font-size: 14px !important;
    line-height: 1.5 !important;
    resize: none !important;
    height: 52px;
    box-sizing: border-box;
    font-family: inherit;
    transition: all 0.2s;
}
.support-input-row textarea:focus {
    border-color: #ea580c !important;
    box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.15) !important;
    outline: none !important;
}
.btn-support-send {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: white;
    border: none;
    border-radius: 14px;
    padding: 0 24px;
    height: 52px;
    font-weight: 700;
    font-size: 14.5px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 14px rgba(234, 88, 12, 0.35);
    transition: all 0.2s;
    font-family: inherit;
    flex-shrink: 0;
}
.btn-support-send:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(234, 88, 12, 0.45);
}
.btn-support-send:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}
.support-input-notice {
    font-size: 12px;
    color: #64748b;
    margin-top: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* MOBİL DUYARLILIK */
@media (max-width: 1100px) {
    .profile-stats-grid { grid-template-columns: repeat(3, 1fr); }
    .analytics-kpi-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 900px) {
    .profile-hero-card { flex-direction: column; align-items: flex-start; padding: 28px; gap: 22px; }
    .profile-hero-stats { width: 100%; justify-content: space-around; }
    .profile-actions { width: 100%; justify-content: flex-start; }
    .tab-btn { flex: unset; padding: 11px 16px; font-size: 13.5px; }
    .profile-stats-grid { grid-template-columns: 1fr 1fr; }
    .firm-item-card { flex-direction: column; align-items: flex-start; }
    .firm-actions { width: 100%; justify-content: flex-end; }
    .form-grid-2 { grid-template-columns: 1fr; }
    .settings-box { padding: 26px; }
    .vip-boost-card { flex-direction: column; align-items: flex-start; }
    .profile-ai-hero { flex-direction: column; align-items: flex-start; }
}
@media (max-width: 600px) {
    .profile-stats-grid { grid-template-columns: 1fr; }
    .analytics-kpi-grid { grid-template-columns: 1fr; }
    .profile-user-wrap { flex-direction: column; align-items: flex-start; gap: 16px; }
    .profile-tabs { overflow-x: auto; padding: 4px; }
    .tab-btn { padding: 10px 16px; font-size: 13.5px; }
}
</style>

<div class="profile-dashboard">
    <div class="container">

        <!-- BİLDİRİMLER -->
        <?php if ( ! empty( $update_success ) ) : ?>
            <div class="alert-box success">
                <i class="fa-solid fa-circle-check"></i>
                <span><?php echo esc_html( $update_success ); ?></span>
            </div>
        <?php endif; ?>

        <?php if ( ! empty( $update_error ) ) : ?>
            <div class="alert-box error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo esc_html( $update_error ); ?></span>
            </div>
        <?php endif; ?>

        <!-- 1. ÜST SAAS ADMIN TOPBAR KARTI -->
        <div class="admin-topbar-card">
            <div class="admin-topbar-user">
                <div class="admin-avatar">
                    <?php 
                    $first_letter = mb_substr( $current_user->display_name ?: $current_user->user_login, 0, 1, 'UTF-8' );
                    echo esc_html( mb_strtoupper( $first_letter, 'UTF-8' ) );
                    ?>
                    <span class="avatar-status-dot" title="Hesap Aktif (Çevrimiçi)"></span>
                </div>
                <div class="admin-user-details">
                    <div class="admin-user-title-row">
                        <h1><?php echo esc_html( $current_user->display_name ?: $current_user->user_login ); ?></h1>
                        <span class="admin-account-badge <?php echo $total_mechs > 0 ? 'badge-verified' : 'badge-user'; ?>">
                            <i class="fa-solid <?php echo $total_mechs > 0 ? 'fa-circle-check' : 'fa-user'; ?>"></i>
                            <?php echo $total_mechs > 0 ? 'Onaylı Servis & Usta Hesabı' : 'Kullanıcı Hesabı'; ?>
                        </span>
                        <span class="admin-id-pill">ID: #<?php echo esc_html( $current_user->ID ); ?></span>
                    </div>
                    <div class="admin-user-subline">
                        <span><i class="fa-regular fa-envelope"></i> <?php echo esc_html( $current_user->user_email ); ?></span>
                        <span class="subline-bullet">•</span>
                        <span><i class="fa-regular fa-calendar-check"></i> Katılım: <?php echo date_i18n( 'F Y', strtotime( $current_user->user_registered ) ); ?></span>
                        <span class="subline-bullet">•</span>
                        <span><i class="fa-solid fa-bolt" style="color:#eab308;"></i> Panel Modu: <strong>SaaS Dashboard</strong></span>
                    </div>
                </div>
            </div>

            <div class="admin-topbar-actions">
                <a href="<?php echo esc_url( home_url('/usta-ekle') ); ?>" class="btn-admin-cta" title="Yeni Firma / Usta Ekle">
                    <i class="fa-solid fa-circle-plus"></i>
                    <span>Yeni Firma Ekle</span>
                </a>
                <a href="<?php echo wp_logout_url( home_url() ); ?>" class="btn-admin-logout" title="Güvenli Çıkış Yap">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span>Çıkış Yap</span>
                </a>
            </div>
        </div>
        <script>
        (function() {
            var uid = '<?php echo esc_js( $user_id ); ?>';
            var isDone = false;
            try {
                if (localStorage.getItem('ototamir_survey_completed_' + uid) === '1' || localStorage.getItem('ototamir_survey_completed') === '1') {
                    isDone = true;
                }
            } catch(e) {}
            if (document.cookie.indexOf('ototamir_survey_completed_' + uid + '=1') !== -1 || document.cookie.indexOf('ototamir_survey_completed=1') !== -1) {
                isDone = true;
            }
            if (isDone) {
                try {
                    document.cookie = 'ototamir_survey_completed_' + uid + '=1; path=/; max-age=31536000; SameSite=Lax';
                    document.cookie = 'ototamir_survey_completed=1; path=/; max-age=31536000; SameSite=Lax';
                } catch(e) {}
                var s = document.createElement('style');
                s.innerHTML = '#survey-welcome-banner, #sidebar-survey-btn, #ototamirSurveyModalOverlay { display: none !important; }';
                document.head.appendChild(s);
            }
        })();
        </script>

        <?php if ( empty( $user_survey_completed ) ) : ?>
        
        <div id="survey-welcome-banner" style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border: 1.5px solid #fed7aa; border-radius: 16px; padding: 16px 22px; margin-bottom: 24px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px; box-shadow: 0 4px 14px rgba(234,88,12,0.08);">
            <div style="display:flex; align-items:center; gap:14px;">
                <div style="width:44px; height:44px; border-radius:12px; background:#ea580c; color:white; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; box-shadow: 0 4px 10px rgba(234,88,12,0.3);">
                    <i class="fa-solid fa-gift"></i>
                </div>
                <div>
                    <h4 style="margin:0 0 3px 0; font-size:15px; color:#9a3412; font-weight:800;">Aramıza Hoş Geldiniz! Aracınızı veya Hizmetinizi Tanımlayın</h4>
                    <p style="margin:0; font-size:13px; color:#c2410c;">4 adımlı kısa anketimizi tamamlayarak aracınızı otomatik olarak <strong>Dijital Araç Garajı</strong>'nıza ekleyin.</p>
                </div>
            </div>
            <button type="button" onclick="openOnboardingSurveyModal();" style="background:#ea580c; color:white; border:none; padding:10px 20px; border-radius:10px; font-weight:700; font-size:13.5px; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow: 0 2px 8px rgba(234,88,12,0.3); transition:all 0.2s;">
                <i class="fa-solid fa-play"></i> Ankete Başla (1 Dk)
            </button>
        </div>
        <?php endif; ?>

        <!-- 2. ADMIN 2-SÜTUNLU DASHBOARD DÜZENİ -->
        <div class="admin-layout-grid">
            <!-- SOL SABİT / YAPIŞKAN NAVİGASYON KENAR ÇUBUĞU -->
            <aside class="admin-sidebar">
                <div class="admin-sidebar-menu">
                    <div class="sidebar-section-title">YÖNETİM MENÜSÜ</div>
                    <button class="tab-btn active" data-tab="tab-firms">
                        <span class="tab-btn-icon"><i class="fa-solid fa-rectangle-list"></i></span>
                        <span class="tab-btn-text">İlanlarım</span>
                        <span class="tab-btn-badge"><?php echo number_format_i18n( $total_mechs ); ?></span>
                    </button>
                    <button class="tab-btn" data-tab="tab-analytics">
                        <span class="tab-btn-icon"><i class="fa-solid fa-chart-line"></i></span>
                        <span class="tab-btn-text">Görüntülemelerim</span>
                        <span class="tab-btn-badge" style="background:#f0f9ff; color:#0284c7;"><?php echo number_format_i18n( $total_views ); ?></span>
                    </button>
                    <button class="tab-btn" data-tab="tab-ai">
                        <span class="tab-btn-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></span>
                        <span class="tab-btn-text">AI Arıza Tespiti</span>
                        <span class="tab-btn-tag" style="background:#fdf4ff; color:#a855f7;">Yeni</span>
                    </button>
                    <button class="tab-btn" data-tab="tab-favorites">
                        <span class="tab-btn-icon"><i class="fa-solid fa-heart"></i></span>
                        <span class="tab-btn-text">Beğendiğim Ustalar</span>
                        <span class="tab-btn-badge"><?php echo intval( $fav_count ); ?></span>
                    </button>
                    <button class="tab-btn" data-tab="tab-garage">
                        <span class="tab-btn-icon"><i class="fa-solid fa-car"></i></span>
                        <span class="tab-btn-text">Araç Garajım</span>
                        <span class="tab-btn-badge"><?php echo intval( $garage_count ); ?></span>
                    </button>
                    <button class="tab-btn" data-tab="tab-support">
                        <span class="tab-btn-icon"><i class="fa-solid fa-headset"></i></span>
                        <span class="tab-btn-text">Destek & Mesajlar</span>
                        <span class="support-tab-badge" id="user-support-unread-badge" style="<?php echo $unread_support_count > 0 ? '' : 'display:none;'; ?>"><?php echo intval( $unread_support_count ); ?></span>
                    </button>

                    <div class="sidebar-section-title" style="margin-top:20px;">HESAP VE GÜVENLİK</div>
                    <button class="tab-btn" data-tab="tab-settings">
                        <span class="tab-btn-icon"><i class="fa-solid fa-user-gear"></i></span>
                        <span class="tab-btn-text">Hesap Ayarları</span>
                    </button>
                    <?php if ( empty( $user_survey_completed ) ) : ?>
                    <button type="button" class="tab-btn" id="sidebar-survey-btn" onclick="openOnboardingSurveyModal();" style="border: 1px dashed #ea580c; background: #fff7ed; color: #ea580c; margin-top: 6px;">
                        <span class="tab-btn-icon"><i class="fa-solid fa-clipboard-question"></i></span>
                        <span class="tab-btn-text">Üye Karşılama Anketi</span>
                        <span class="tab-btn-tag" style="background:#ea580c; color:white;">1 Dk</span>
                    </button>
                    <?php endif; ?>

                    <?php if ( current_user_can( 'manage_options' ) ) : ?>
                    <div class="sidebar-section-title" style="margin-top:22px; color:#ea580c;"><i class="fa-solid fa-shield-halved"></i> SİTE YÖNETİMİ</div>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=ototamir-indexnow' ) ); ?>" class="tab-btn" style="text-decoration:none; color:inherit;">
                        <span class="tab-btn-icon" style="color:#16a34a;"><i class="fa-solid fa-bolt"></i></span>
                        <span class="tab-btn-text">🚀 Hızlı İndeksleme</span>
                        <span class="tab-btn-tag" style="background:#dcfce7; color:#15803d;">SEO</span>
                    </a>
                    <a href="<?php echo esc_url( admin_url() ); ?>" class="tab-btn" style="text-decoration:none; color:inherit;">
                        <span class="tab-btn-icon" style="color:#64748b;"><i class="fa-solid fa-gauge-high"></i></span>
                        <span class="tab-btn-text">WP Kontrol Paneli</span>
                    </a>
                    <?php endif; ?>
                </div>

                <!-- SIDEBAR PROMO / VIP CARD -->
                <div class="admin-sidebar-promo">
                    <div class="promo-icon"><i class="fa-solid fa-gem"></i></div>
                    <div class="promo-content">
                        <h4>İşletmenizi Öne Çıkarın</h4>
                        <p>Arama sonuçlarında en üst sırada yer alın, müşterilere doğrudan ulaşın.</p>
                        <button type="button" class="btn-promo-action" onclick="document.querySelector('.tab-btn[data-tab=\'tab-support\']')?.click();">Yöneticiye Yaz</button>
                    </div>
                </div>
            </aside>

            <!-- SAĞ ANA İÇERİK TUVALİ (CANVAS) -->
            <main class="admin-main-canvas">
                <!-- 4 ADET ÜST KPI METRİK KARTI -->
                <div class="admin-kpi-row">
                    <div class="admin-kpi-card stat-clickable" data-open-tab="tab-firms" title="İlanlarıma Git">
                        <div class="kpi-icon orange"><i class="fa-solid fa-rectangle-list"></i></div>
                        <div class="kpi-data">
                            <span class="kpi-label">Toplam İlanlarım</span>
                            <span class="kpi-value"><?php echo number_format_i18n( $total_mechs ); ?></span>
                        </div>
                        <i class="fa-solid fa-arrow-right kpi-arrow"></i>
                    </div>

                    <div class="admin-kpi-card stat-clickable" data-open-tab="tab-analytics" title="Görüntüleme Detaylarına Git">
                        <div class="kpi-icon blue"><i class="fa-solid fa-chart-line"></i></div>
                        <div class="kpi-data">
                            <span class="kpi-label">Toplam Görüntülenme</span>
                            <span class="kpi-value"><?php echo number_format_i18n( $total_views ); ?></span>
                        </div>
                        <i class="fa-solid fa-arrow-right kpi-arrow"></i>
                    </div>

                    <div class="admin-kpi-card stat-clickable" data-open-tab="tab-garage" title="Araç Garajıma Git">
                        <div class="kpi-icon green"><i class="fa-solid fa-car"></i></div>
                        <div class="kpi-data">
                            <span class="kpi-label">Kayıtlı Araçlarım</span>
                            <span class="kpi-value"><?php echo intval( $garage_count ); ?></span>
                        </div>
                        <i class="fa-solid fa-arrow-right kpi-arrow"></i>
                    </div>

                    <div class="admin-kpi-card stat-clickable" data-open-tab="tab-support" title="Canlı Destek Paneline Git">
                        <div class="kpi-icon purple"><i class="fa-solid fa-headset"></i></div>
                        <div class="kpi-data">
                            <span class="kpi-label">Yönetici Desteği</span>
                            <span class="kpi-value" style="<?php echo $unread_support_count > 0 ? 'color:#ea580c;' : ''; ?>">
                                <?php echo $unread_support_count > 0 ? ($unread_support_count . ' Yeni') : 'Canlı Aktif'; ?>
                            </span>
                        </div>
                        <i class="fa-solid fa-arrow-right kpi-arrow"></i>
                    </div>
                </div>

                <!-- 4. TAB İÇERİK 1: FİRMALARIM -->
                <div class="tab-pane active" id="tab-firms">
                    <!-- FİRMALAR TOOLBAR (Canlı Arama & Hızlı Firma Ekle) -->
                    <div class="firms-toolbar">
                        <div class="firms-toolbar-left">
                            <h2 class="firms-toolbar-title"><i class="fa-solid fa-shop"></i> Kayıtlı İşletmelerim</h2>
                            <span class="firms-count-badge"><?php echo number_format_i18n( $total_mechs ); ?> Firma</span>
                        </div>
                        <div class="firms-toolbar-right">
                            <div class="firms-search-box">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="filter-firms-search" placeholder="İlanlarımda hızlı ara..." autocomplete="off">
                            </div>
                            <a href="<?php echo esc_url( home_url('/usta-ekle') ); ?>" class="btn-add-firm-inline">
                                <i class="fa-solid fa-plus"></i> Yeni Firma Ekle
                            </a>
                        </div>
                    </div>
            <?php if ( $user_mechs_query->have_posts() ) : ?>
                <div class="firm-cards-list">
                    <?php while ( $user_mechs_query->have_posts() ) : $user_mechs_query->the_post(); 
                        $image_url = get_post_meta( get_the_ID(), '_custom_mechanic_image', true );
                        if ( empty( $image_url ) ) {
                            $image_url = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ) : 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?auto=format&fit=crop&w=150&q=70';
                        }
                        if ( function_exists( 'ototamir_optimize_image_url' ) ) {
                            $image_url = ototamir_optimize_image_url( $image_url, 150 );
                        }
                        $status = get_post_status();
                        $views  = function_exists('ototamir_get_post_views') ? ototamir_get_post_views( get_the_ID() ) : '0';
                        $cities = wp_get_post_terms( get_the_ID(), 'mechanic_city' );
                        $districts = wp_get_post_terms( get_the_ID(), 'mechanic_district' );
                        $loc = ($cities && !is_wp_error($cities)) ? $cities[0]->name : '';
                        if ($districts && !is_wp_error($districts)) {
                            $loc = $districts[0]->name . ', ' . $loc;
                        }
                    ?>
                        <div class="firm-item-card">
                            <div class="firm-info-left">
                                <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php the_title_attribute(); ?>" class="firm-thumb" width="80" height="80" loading="lazy">
                                <div class="firm-details">
                                    <h3>
                                        <?php if ( $status === 'publish' ) : ?>
                                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                        <?php else : ?>
                                            <span><?php the_title(); ?></span>
                                        <?php endif; ?>
                                    </h3>
                                    <div class="firm-meta-row">
                                        <?php if ( ! empty( $loc ) ) : ?>
                                            <span><i class="fa-solid fa-location-dot"></i> <?php echo esc_html( $loc ); ?></span>
                                        <?php endif; ?>
                                        <span><i class="fa-regular fa-eye"></i> <?php echo esc_html( $views ); ?> Görüntülenme</span>
                                        <span><i class="fa-regular fa-clock"></i> <?php echo get_the_date('j F Y'); ?></span>
                                        
                                        <?php if ( $status === 'publish' ) : ?>
                                            <span class="status-pill publish"><i class="fa-solid fa-circle-check"></i> Yayında</span>
                                        <?php elseif ( $status === 'pending' ) : ?>
                                            <span class="status-pill pending"><i class="fa-solid fa-hourglass-half"></i> Onay Bekliyor</span>
                                        <?php else : ?>
                                            <span class="status-pill draft"><i class="fa-solid fa-eye-slash"></i> Yayında Değil</span>
                                        <?php endif; ?>

                                        <?php
                                        $is_vip = get_post_meta( get_the_ID(), '_mechanic_is_featured', true ) === '1';
                                        if ( $is_vip ) :
                                        ?>
                                            <span style="background:linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color:#92400e; border:1px solid #fcd34d; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:800; display:inline-flex; align-items:center; gap:4px;">
                                                <i class="fa-solid fa-crown" style="color:#d97706;"></i> VIP Vitrin Aktif
                                            </span>
                                        <?php elseif ( $status === 'publish' ) : ?>
                                            <?php
                                            $mail_subject = rawurlencode( 'VIP Vitrin Başvurusu: ' . get_the_title() . ' (İlan ID: #' . get_the_ID() . ')' );
                                            $mail_body    = rawurlencode( "Merhaba Ototamir360 Yönetimi,\n\n" . get_the_title() . " (İlan ID: #" . get_the_ID() . ") adlı işletmemi Öne Çıkan / VIP Vitrin paketine yükselterek listelerde en üst sıralarda yer almak istiyorum.\n\nYetkili Adı Soyadı:\nİletişim Telefonu:\n\nPaket detayları ve ödeme bilgileri için tarafıma dönüş yapılmasını rica ederim." );
                                            $mail_url     = 'mailto:admin@ototamircibul.com.tr?subject=' . $mail_subject . '&body=' . $mail_body;
                                            ?>
                                            <a href="<?php echo esc_url( $mail_url ); ?>" style="background:#fff7ed; color:#ea580c; border:1px dashed #fdba74; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:4px;" title="İlanınızı listenin en başına taşıyarak kat kat daha fazla müşteri kazanın! Mail ile VIP vitrin talebi iletin.">
                                                <i class="fa-solid fa-envelope" style="color:#ea580c;"></i> Vitrine Taşı (VIP Talep Et)
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="firm-actions">
                                <?php if ( $status === 'publish' ) : ?>
                                    <a href="<?php the_permalink(); ?>" class="btn-view-firm" target="_blank">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i> İlanı Gör
                                    </a>
                                <?php else : ?>
                                    <span style="font-size:12.5px; color:#b45309; font-weight:600; padding:8px 12px; background:#fffbeb; border:1px solid #fef3c7; border-radius:10px;">
                                        <i class="fa-solid fa-hourglass-half"></i> <?php echo ( $status === 'draft' ) ? 'Taslak' : 'İnceleniyor'; ?>
                                    </span>
                                <?php endif; ?>

                                <a href="<?php echo esc_url( add_query_arg( 'edit_id', get_the_ID(), home_url('/usta-ekle') ) ); ?>" class="btn-edit-firm" title="Firma Bilgilerini Güncelle">
                                    <i class="fa-solid fa-pen-to-square"></i> İlanı Düzenle
                                </a>

                                <?php if ( $status === 'publish' ) : ?>
                                    <form method="post" action="#firmalar" style="display:inline; margin:0;" onsubmit="return confirm('Bu ilanı yayından kaldırmak istediğinize emin misiniz? İlanınız arama sonuçlarında ve haritada görünmeyecektir.');">
                                        <?php wp_nonce_field( 'ototamir_toggle_status_action', 'ototamir_toggle_status_nonce' ); ?>
                                        <input type="hidden" name="mech_id" value="<?php echo get_the_ID(); ?>">
                                        <input type="hidden" name="status_action" value="unpublish">
                                        <button type="submit" class="btn-unpublish-firm" title="İlanı Yayından Kaldır">
                                            <i class="fa-solid fa-eye-slash"></i> Yayından Kaldır
                                        </button>
                                    </form>
                                <?php elseif ( $status === 'draft' ) : ?>
                                    <form method="post" action="#firmalar" style="display:inline; margin:0;">
                                        <?php wp_nonce_field( 'ototamir_toggle_status_action', 'ototamir_toggle_status_nonce' ); ?>
                                        <input type="hidden" name="mech_id" value="<?php echo get_the_ID(); ?>">
                                        <input type="hidden" name="status_action" value="publish">
                                        <button type="submit" class="btn-publish-firm" title="İlanı Tekrar Yayına Al">
                                            <i class="fa-solid fa-globe"></i> Tekrar Yayına Al
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>

                <?php if ( $user_mechs_query->max_num_pages > 1 ) : ?>
                    <div class="profile-pagination">
                        <?php
                        echo paginate_links( array(
                            'base'         => add_query_arg( 'paged', '%#%' ),
                            'format'       => '',
                            'prev_text'    => '<i class="fa-solid fa-chevron-left"></i> Önceki',
                            'next_text'    => 'Sonraki <i class="fa-solid fa-chevron-right"></i>',
                            'total'        => $user_mechs_query->max_num_pages,
                            'current'      => $paged,
                            'type'         => 'list',
                            'add_fragment' => '#firmalar',
                        ) );
                        ?>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="empty-box">
                    <div class="empty-icon">
                        <i class="fa-solid fa-building"></i>
                    </div>
                    <h3>Henüz Kayıtlı Bir Firmanız Yok</h3>
                    <p>Oto servis, tamirci veya ekspertiz işletmenizi Türkiye'nin en büyük usta rehberine ekleyerek binlerce yeni araç sahibine anında ulaşın.</p>
                    <a href="<?php echo esc_url( home_url('/usta-ekle') ); ?>" class="btn-primary">
                        <i class="fa-solid fa-plus"></i> Hemen Ücretsiz Firma Ekle
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- 4. TAB İÇERİK 2: GÖRÜNTÜLEMELERİM & ANALİTİK -->
        <div class="tab-pane" id="tab-analytics">
            <div class="analytics-kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div class="analytics-kpi-card">
                    <div class="analytics-kpi-title">
                        <span>Toplam Görüntülenme</span>
                        <i class="fa-solid fa-eye" style="color:#0284c7;"></i>
                    </div>
                    <div class="analytics-kpi-val"><?php echo number_format_i18n( $total_views ); ?></div>
                    <div class="analytics-kpi-sub">Tüm ilanlarınıza gelen toplam ziyaret</div>
                </div>

                <div class="analytics-kpi-card" style="border-left: 3px solid #ea580c;">
                    <div class="analytics-kpi-title">
                        <span style="color:#9a3412;">Telefon Aramaları</span>
                        <i class="fa-solid fa-phone" style="color:#ea580c;"></i>
                    </div>
                    <div class="analytics-kpi-val" style="color:#ea580c;"><?php echo number_format_i18n( $total_calls ); ?></div>
                    <div class="analytics-kpi-sub">Numaranızı tıklayıp arayanlar</div>
                </div>

                <div class="analytics-kpi-card" style="border-left: 3px solid #10b981;">
                    <div class="analytics-kpi-title">
                        <span style="color:#166534;">WhatsApp Mesajları</span>
                        <i class="fa-brands fa-whatsapp" style="color:#10b981;"></i>
                    </div>
                    <div class="analytics-kpi-val" style="color:#10b981;"><?php echo number_format_i18n( $total_whatsapp ); ?></div>
                    <div class="analytics-kpi-sub">WhatsApp'tan fiyat ve bilgi isteyenler</div>
                </div>

                <div class="analytics-kpi-card" style="border-left: 3px solid #0284c7;">
                    <div class="analytics-kpi-title">
                        <span style="color:#075985;">Yol Tarifi & Konum</span>
                        <i class="fa-solid fa-diamond-turn-right" style="color:#0284c7;"></i>
                    </div>
                    <div class="analytics-kpi-val" style="color:#0284c7;"><?php echo number_format_i18n( $total_directions ); ?></div>
                    <div class="analytics-kpi-sub">Adresinizi haritada açıp rota çizenler</div>
                </div>
            </div>

            <!-- İlan Bazında Görüntüleme Döküm Tablosu -->
            <div class="analytics-table-wrap">
                <div class="analytics-table-header">
                    <h3><i class="fa-solid fa-chart-column" style="color:#0284c7;"></i> İlan Bazında Görüntülenme & Müşteri Dönüşümleri</h3>
                    <span style="font-size:13px; color:#64748b; font-weight:600;">
                        Toplam <?php echo count( $analytics_mechs ); ?> İlan İnceleniyor
                    </span>
                </div>

                <?php if ( ! empty( $analytics_mechs ) ) : ?>
                    <div style="overflow-x:auto;">
                        <table class="analytics-table">
                            <thead>
                                <tr>
                                    <th>İlan / İşletme</th>
                                    <th>Konum</th>
                                    <th>Durum</th>
                                    <th>Görüntülenme</th>
                                    <th>Müşteri Aksiyonları</th>
                                    <th>Trafik Payı</th>
                                    <th style="text-align:right;">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $analytics_mechs as $m ) : 
                                    $m_id     = $m->ID;
                                    $m_views  = (int) get_post_meta( $m_id, '_mechanic_views_count', true );
                                    $m_calls  = (int) get_post_meta( $m_id, '_mechanic_call_count', true );
                                    $m_wa     = (int) get_post_meta( $m_id, '_mechanic_whatsapp_count', true );
                                    $m_dir    = (int) get_post_meta( $m_id, '_mechanic_directions_count', true );
                                    $m_status = get_post_status( $m_id );
                                    $m_cities = wp_get_post_terms( $m_id, 'mechanic_city' );
                                    $m_dists  = wp_get_post_terms( $m_id, 'mechanic_district' );
                                    $m_loc    = ( ! empty($m_cities) && ! is_wp_error($m_cities) ) ? $m_cities[0]->name : '';
                                    if ( ! empty($m_dists) && ! is_wp_error($m_dists) ) {
                                        $m_loc = $m_dists[0]->name . ', ' . $m_loc;
                                    }
                                    $m_pct = ( $total_views > 0 ) ? round( ( $m_views / $total_views ) * 100 ) : 0;
                                    $is_vip = get_post_meta( $m_id, '_mechanic_is_featured', true ) === '1';
                                ?>
                                    <tr>
                                        <td>
                                            <strong style="color:#0f172a;">
                                                <?php if ( $m_status === 'publish' ) : ?>
                                                    <a href="<?php echo esc_url( get_permalink($m_id) ); ?>" target="_blank" style="color:inherit; text-decoration:none;">
                                                        <?php echo esc_html( get_the_title($m_id) ); ?>
                                                    </a>
                                                <?php else : ?>
                                                    <?php echo esc_html( get_the_title($m_id) ); ?>
                                                <?php endif; ?>
                                            </strong>
                                            <?php if ( $is_vip ) : ?>
                                                <span style="background:#fef3c7; color:#92400e; font-size:11px; font-weight:800; padding:2px 8px; border-radius:10px; margin-left:6px;">VIP</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="color:#64748b; font-size:13.5px;">
                                            <?php echo ! empty($m_loc) ? esc_html($m_loc) : '-'; ?>
                                        </td>
                                        <td>
                                            <?php if ( $m_status === 'publish' ) : ?>
                                                <span class="status-pill publish" style="font-size:12px; padding:3px 10px;"><i class="fa-solid fa-circle-check"></i> Yayında</span>
                                            <?php elseif ( $m_status === 'pending' ) : ?>
                                                <span class="status-pill pending" style="font-size:12px; padding:3px 10px;"><i class="fa-solid fa-hourglass-half"></i> Onayda</span>
                                            <?php else : ?>
                                                <span class="status-pill draft" style="font-size:12px; padding:3px 10px;"><i class="fa-solid fa-eye-slash"></i> Pasif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span style="font-weight:800; color:#0284c7; font-size:15px;">
                                                <?php echo number_format_i18n( $m_views ); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="display:inline-flex; align-items:center; gap:5px; flex-wrap:wrap; font-size:11.5px;">
                                                <?php if ( $m_calls > 0 ) : ?>
                                                    <span style="background:#ffedd5; color:#c2410c; padding:2px 6px; border-radius:4px; font-weight:700;" title="Telefon Araması">📞 <?php echo $m_calls; ?></span>
                                                <?php endif; ?>
                                                <?php if ( $m_wa > 0 ) : ?>
                                                    <span style="background:#dcfce7; color:#15803d; padding:2px 6px; border-radius:4px; font-weight:700;" title="WhatsApp Mesajı">💬 <?php echo $m_wa; ?></span>
                                                <?php endif; ?>
                                                <?php if ( $m_dir > 0 ) : ?>
                                                    <span style="background:#e0f2fe; color:#0369a1; padding:2px 6px; border-radius:4px; font-weight:700;" title="Yol Tarifi / Konum">📍 <?php echo $m_dir; ?></span>
                                                <?php endif; ?>
                                                <?php if ( $m_calls == 0 && $m_wa == 0 && $m_dir == 0 ) : ?>
                                                    <span style="color:#94a3b8; font-size:12px;">-</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="analytics-progress-wrap">
                                                <div class="analytics-progress-bar">
                                                    <div class="analytics-progress-fill" style="width: <?php echo esc_attr( min(100, max(4, $m_pct)) ); ?>%;"></div>
                                                </div>
                                                <span style="font-size:12.5px; font-weight:700; color:#64748b; min-width:34px;">%<?php echo $m_pct; ?></span>
                                            </div>
                                        </td>
                                        <td style="text-align:right; white-space:nowrap;">
                                            <?php if ( $m_status === 'publish' ) : ?>
                                                <a href="<?php echo esc_url( get_permalink($m_id) ); ?>" class="btn-view-firm" target="_blank" style="padding:6px 12px; font-size:12.5px;">
                                                    <i class="fa-solid fa-eye"></i> İncele
                                                </a>
                                            <?php endif; ?>
                                            <?php if ( ! $is_vip && $m_status === 'publish' ) : 
                                                $mail_sub = rawurlencode( 'VIP Vitrin Başvurusu: ' . get_the_title($m_id) . ' (#' . $m_id . ')' );
                                                $mail_body = rawurlencode( "Merhaba,\n\n" . get_the_title($m_id) . " (ID: #" . $m_id . ") ilanımı vitrine taşımak ve görüntülenmemi artırmak istiyorum.\n\nİletişim Numaram:\nYetkili Adı Soyadı:\n" );
                                            ?>
                                                <a href="mailto:admin@ototamircibul.com.tr?subject=<?php echo $mail_sub; ?>&body=<?php echo $mail_body; ?>" class="btn-primary" style="padding:6px 12px; font-size:12.5px; background:linear-gradient(135deg, #f97316 0%, #ea580c 100%);" title="VIP Vitrin paketi ile görüntülenmenizi 5 kat artırın!">
                                                    <i class="fa-solid fa-bolt"></i> Öne Çıkar
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <div style="padding:40px 20px; text-align:center; color:#64748b;">
                        <i class="fa-solid fa-chart-pie" style="font-size:36px; color:#cbd5e1; margin-bottom:12px; display:block;"></i>
                        Henüz yayınlanmış bir firmanız veya ilanınız bulunmadığı için görüntülenme verisi oluşmadı.
                    </div>
                <?php endif; ?>
            </div>

            <!-- VIP Vitrin Tanıtım Banner'ı -->
            <div class="vip-boost-card">
                <div class="vip-boost-info">
                    <h4><i class="fa-solid fa-crown" style="color:#f59e0b;"></i> İlanlarınızı 5 Kat Daha Fazla Müşteriye Ulaştırın!</h4>
                    <p>Ototamir360 VIP Vitrin ve Şehir Ana Sayfası Öne Çıkarma paketleri ile işletmenizi arama sonuçlarında daima zirvede tutun, müşteri aramalarını kaçırmayın.</p>
                </div>
                <a href="mailto:admin@ototamircibul.com.tr?subject=VIP%20Vitrin%20ve%20Paket%20Bilgisi%20Talebi&body=Merhaba%20Ototamir360%20Yonetimi,%0A%0AIsletmem%20icin%20VIP%20Vitrin%20paketleri%20ve%20ucretlendirme%20hakkinda%20bilgi%20almak%20istiyorum.%0A%0ATelefon%20Numaram:%20%0AIsletme%20Adim:%20" class="vip-boost-btn">
                    <i class="fa-solid fa-envelope"></i> admin@ototamircibul.com.tr İle İletişime Geç
                </a>
            </div>
        </div>

        <!-- 4. TAB İÇERİK 3: YAPAY ZEKA İLE ARIZA TESPİTİ -->
        <div class="tab-pane" id="tab-ai">
            <div class="profile-ai-hero">
                <div class="profile-ai-hero-info">
                    <h3><i class="fa-solid fa-wand-magic-sparkles" style="color:#c084fc;"></i> Yapay Zekâ ile Araç Arıza Tespiti</h3>
                    <p>Aracınızdan gelen tuhaf sesleri, tekleme şikayetlerini veya yanan ikaz lambasını anlatın; yapay zeka muhtemel sebepleri anında analiz etsin ve sizi ilgili uzman tamirciye yönlendirsin.</p>
                </div>
                <div>
                    <a href="<?php echo esc_url( home_url('/ariza-tespiti/') ); ?>" class="btn-open-full-ai" target="_blank">
                        <i class="fa-solid fa-up-right-from-square"></i> Tam Sayfada Aç
                    </a>
                </div>
            </div>

            <div class="profile-ai-tool-wrap">
                <?php echo do_shortcode( '[otb_ai_teshis]' ); ?>
            </div>
        </div>

        <!-- 4. TAB İÇERİK: ARAÇ GARAJIM & BAKIM DEFTERİ -->
        <div class="tab-pane" id="tab-garage">
            <div class="garage-top-bar">
                <div>
                    <h2 style="font-size:22px; font-weight:800; color:#0f172a; margin:0 0 4px;">
                        <i class="fa-solid fa-car-side" style="color:#f97316;"></i> Dijital Garajım & Servis Kayıtlarım
                    </h2>
                    <p style="font-size:14px; color:#64748b; margin:0;">
                        Araçlarınızın periyodik bakımlarını, parça değişimlerini ve sonraki bakım kilometrelerini dijital defterde takip edin.
                    </p>
                </div>
                <button type="button" class="btn-toggle-garage-form" onclick="toggleElement('garage-add-vehicle-card');">
                    <i class="fa-solid fa-plus"></i> Yeni Araç Ekle
                </button>
            </div>

            <!-- Yeni Araç Ekleme Formu -->
            <div id="garage-add-vehicle-card" class="garage-form-card" style="display:none;">
                <h4><i class="fa-solid fa-car"></i> Yeni Araç Kaydı Oluştur</h4>
                <form method="post" action="#garaj">
                    <?php wp_nonce_field( 'ototamir_garage_action', 'ototamir_garage_nonce' ); ?>
                    <input type="hidden" name="garage_subaction" value="add_vehicle">

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>Plaka *</label>
                            <input type="text" name="v_plate" placeholder="Örn: 34 ABC 123" required style="text-transform:uppercase; font-weight:700;">
                        </div>
                        <div class="form-group">
                            <label>Marka *</label>
                            <input type="text" name="v_brand" placeholder="Örn: Volkswagen, Renault, Ford" required>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>Model</label>
                            <input type="text" name="v_model" placeholder="Örn: Golf 7 1.6 TDI, Megane 4">
                        </div>
                        <div class="form-group">
                            <label>Model Yılı</label>
                            <input type="number" name="v_year" placeholder="Örn: 2018" min="1950" max="2027">
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>Yakıt Türü</label>
                            <select name="v_fuel" style="padding:12px 16px; border:1.5px solid #cbd5e1; border-radius:10px; font-size:15px; background:#f8fafc;">
                                <option value="Dizel">Dizel</option>
                                <option value="Benzin">Benzin</option>
                                <option value="Benzin & LPG">Benzin & LPG</option>
                                <option value="Hibrit">Hibrit</option>
                                <option value="Elektrik">Elektrik</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Güncel Kilometre (km)</label>
                            <input type="number" name="v_km" placeholder="Örn: 145000">
                        </div>
                    </div>

                    <div style="display:flex; gap:12px; margin-top:10px;">
                        <button type="submit" class="btn-save-settings">
                            <i class="fa-solid fa-floppy-disk"></i> Aracı Garaja Kaydet
                        </button>
                        <button type="button" onclick="toggleElement('garage-add-vehicle-card');" style="background:#f1f5f9; color:#475569; border:none; padding:12px 20px; border-radius:12px; font-weight:700; cursor:pointer;">
                            İptal
                        </button>
                    </div>
                </form>
            </div>

            <!-- Araç Listesi -->
            <?php if ( ! empty( $garage_vehicles ) ) : ?>
                <div class="garage-vehicles-list">
                    <?php foreach ( $garage_vehicles as $vid => $v ) : 
                        $logs = isset($v['logs']) && is_array($v['logs']) ? $v['logs'] : array();
                        usort($logs, function($a, $b) {
                            return strtotime($b['date'] ?? '') - strtotime($a['date'] ?? '');
                        });
                        $total_spent = 0;
                        foreach ($logs as $l) {
                            $total_spent += floatval($l['cost'] ?? 0);
                        }
                    ?>
                        <div class="garage-vehicle-card">
                            <div class="garage-vehicle-header">
                                <div class="plate-badge-wrap">
                                    <div class="plate-badge">
                                        <div class="plate-tr"><span>TR</span></div>
                                        <div class="plate-text"><?php echo esc_html( $v['plate'] ?? 'PLAKA' ); ?></div>
                                    </div>
                                    <div>
                                        <div class="vehicle-specs">
                                            <?php echo esc_html( trim( ($v['brand'] ?? '') . ' ' . ($v['model'] ?? '') ) ); ?>
                                            <?php if ( ! empty($v['year']) ) : ?><span style="color:#64748b; font-weight:600;">(<?php echo (int) $v['year']; ?>)</span><?php endif; ?>
                                        </div>
                                        <div class="vehicle-sub-specs">
                                            <span><i class="fa-solid fa-gas-pump"></i> <?php echo esc_html( $v['fuel'] ?? 'Belirtilmemiş' ); ?></span>
                                            <span> • </span>
                                            <span><i class="fa-solid fa-gauge"></i> <?php echo number_format( (int)($v['current_km'] ?? 0), 0, ',', '.' ); ?> km</span>
                                            <span> • </span>
                                            <span style="color:#15803d; font-weight:700;"><i class="fa-solid fa-wallet"></i> Toplam Harcama: <?php echo number_format($total_spent, 0, ',', '.'); ?> ₺</span>
                                        </div>
                                    </div>
                                </div>

                                <div style="display:flex; gap:10px; align-items:center;">
                                    <button type="button" class="btn-view-firm" onclick="toggleElement('log-form-<?php echo esc_attr($vid); ?>');" style="color:#c2410c; background:#fff7ed; border:1px solid #fed7aa; cursor:pointer;">
                                        <i class="fa-solid fa-plus"></i> Bakım Kaydı Ekle
                                    </button>
                                    <form method="post" action="#garaj" style="margin:0; display:inline;" onsubmit="return confirm('Bu aracı ve tüm bakım kayıtlarını silmek istediğinize emin misiniz?');">
                                        <?php wp_nonce_field( 'ototamir_garage_action', 'ototamir_garage_nonce' ); ?>
                                        <input type="hidden" name="garage_subaction" value="delete_vehicle">
                                        <input type="hidden" name="v_id" value="<?php echo esc_attr( $vid ); ?>">
                                        <button type="submit" class="btn-logout" style="padding:9px 14px; font-size:13px; cursor:pointer;" title="Aracı Sil">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Bakım Ekleme Formu (Gizli/Açılabilir) -->
                            <div id="log-form-<?php echo esc_attr($vid); ?>" class="garage-form-card" style="display:none; background:#f8fafc; border-color:#e2e8f0;">
                                <h4><i class="fa-solid fa-wrench"></i> <?php echo esc_html($v['plate']); ?> İçin Bakım / Servis Kaydı Ekle</h4>
                                <form method="post" action="#garaj">
                                    <?php wp_nonce_field( 'ototamir_garage_action', 'ototamir_garage_nonce' ); ?>
                                    <input type="hidden" name="garage_subaction" value="add_log">
                                    <input type="hidden" name="v_id" value="<?php echo esc_attr( $vid ); ?>">

                                    <div class="form-grid-2">
                                        <div class="form-group">
                                            <label>Bakım Tarihi *</label>
                                            <input type="date" name="log_date" value="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>İşlem Anındaki Kilometre *</label>
                                            <input type="number" name="log_km" value="<?php echo (int)($v['current_km'] ?? 0); ?>" required>
                                        </div>
                                    </div>

                                    <div class="form-grid-2">
                                        <div class="form-group">
                                            <label>İşlem Türü *</label>
                                            <select name="log_type" style="padding:12px 16px; border:1.5px solid #cbd5e1; border-radius:10px; font-size:15px; background:white;">
                                                <option value="Periyodik Bakım (Yağ & Filtre)">Periyodik Bakım (Yağ & Filtre)</option>
                                                <option value="Fren & Balata Değişimi">Fren & Balata Değişimi</option>
                                                <option value="Ağır Bakım (Triger & Devirdaim)">Ağır Bakım (Triger & Devirdaim)</option>
                                                <option value="Akü Değişimi">Akü Değişimi</option>
                                                <option value="Lastik & Balans">Lastik & Balans</option>
                                                <option value="Şanzıman / Debriyaj / Baskı">Şanzıman / Debriyaj / Baskı</option>
                                                <option value="Ön Takım & Amortisör">Ön Takım & Amortisör</option>
                                                <option value="Oto Elektrik & Beyin">Oto Elektrik & Beyin</option>
                                                <option value="Klima Gazı & Kompresör">Klima Gazı & Kompresör</option>
                                                <option value="Kaporta & Boya Onarım">Kaporta & Boya Onarım</option>
                                                <option value="Muayene Hazırlık & Genel Kontrol">Muayene Hazırlık & Genel Kontrol</option>
                                                <option value="Diğer Tamir / Onarım">Diğer Tamir / Onarım</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Hizmet Alınan Usta / Servis</label>
                                            <input type="text" name="log_mechanic" placeholder="Örn: Özkan Oto Mekanik">
                                        </div>
                                    </div>

                                    <div class="form-grid-2">
                                        <div class="form-group">
                                            <label>Toplam Masraf Tutarı (₺)</label>
                                            <input type="number" step="0.01" name="log_cost" placeholder="Örn: 3500">
                                        </div>
                                        <div class="form-group">
                                            <label>Sonraki Bakım Hedef KM</label>
                                            <input type="number" name="log_next_km" placeholder="Örn: <?php echo (int)($v['current_km'] ?? 0) + 10000; ?>">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Yapılan İşlem Detayı ve Parça Notları</label>
                                        <textarea name="log_notes" rows="2" placeholder="Örn: Motul 5W-30 motor yağı, Bosch filtre seti kullanıldı. Ön fren balataları yenilendi." style="padding:12px 16px; border:1.5px solid #cbd5e1; border-radius:10px; font-size:14px; background:white; font-family:inherit;"></textarea>
                                    </div>

                                    <div style="display:flex; gap:12px; margin-top:10px;">
                                        <button type="submit" class="btn-save-settings">
                                            <i class="fa-solid fa-check"></i> Kaydı Deftere Ekle
                                        </button>
                                        <button type="button" onclick="toggleElement('log-form-<?php echo esc_attr($vid); ?>');" style="background:#e2e8f0; color:#475569; border:none; padding:12px 20px; border-radius:12px; font-weight:700; cursor:pointer;">
                                            İptal
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Bakım Defteri Tablosu -->
                            <div class="garage-logs-wrap">
                                <?php if ( ! empty($logs) ) : ?>
                                    <div class="garage-logs-table-wrap">
                                        <table class="garage-logs-table">
                                            <thead>
                                                <tr>
                                                    <th>Tarih</th>
                                                    <th>Kilometre</th>
                                                    <th>İşlem Türü</th>
                                                    <th>Servis / Usta</th>
                                                    <th>Tutar</th>
                                                    <th>Sonraki Bakım</th>
                                                    <th>İşlem Notları</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ( $logs as $log ) : ?>
                                                    <tr>
                                                        <td style="white-space:nowrap; font-weight:600;">
                                                            <i class="fa-regular fa-calendar" style="color:#94a3b8; margin-right:4px;"></i>
                                                            <?php echo esc_html( date('d.m.Y', strtotime($log['date'] ?? 'now')) ); ?>
                                                        </td>
                                                        <td style="white-space:nowrap; font-weight:700; color:#0f172a;">
                                                            <?php echo number_format((int)($log['km'] ?? 0), 0, ',', '.'); ?> km
                                                        </td>
                                                        <td>
                                                            <span class="log-badge-type"><?php echo esc_html( $log['type'] ?? 'Bakım' ); ?></span>
                                                        </td>
                                                        <td>
                                                            <?php echo esc_html( $log['mechanic'] ?: '-' ); ?>
                                                        </td>
                                                        <td style="white-space:nowrap; font-weight:700; color:#15803d;">
                                                            <?php echo ! empty($log['cost']) ? number_format(floatval($log['cost']), 0, ',', '.') . ' ₺' : '-'; ?>
                                                        </td>
                                                        <td style="white-space:nowrap; color:#c2410c; font-weight:600;">
                                                            <?php echo ! empty($log['next_km']) ? number_format((int)$log['next_km'], 0, ',', '.') . ' km' : '-'; ?>
                                                        </td>
                                                        <td style="font-size:12.5px; color:#64748b; max-width:260px;">
                                                            <?php echo esc_html( $log['notes'] ?: '-' ); ?>
                                                        </td>
                                                        <td>
                                                            <form method="post" action="#garaj" style="margin:0; display:inline;" onsubmit="return confirm('Bu bakım kaydını silmek istediğinize emin misiniz?');">
                                                                <?php wp_nonce_field( 'ototamir_garage_action', 'ototamir_garage_nonce' ); ?>
                                                                <input type="hidden" name="garage_subaction" value="delete_log">
                                                                <input type="hidden" name="v_id" value="<?php echo esc_attr( $vid ); ?>">
                                                                <input type="hidden" name="log_id" value="<?php echo esc_attr( $log['id'] ?? '' ); ?>">
                                                                <button type="submit" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:14px; padding:4px 8px;" title="Kaydı Sil">
                                                                    <i class="fa-solid fa-xmark"></i>
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else : ?>
                                    <div style="background:#f8fafc; border:1px dashed #cbd5e1; border-radius:12px; padding:20px; text-align:center; color:#64748b; font-size:13.5px;">
                                        <i class="fa-solid fa-wrench" style="font-size:20px; color:#cbd5e1; display:block; margin-bottom:6px;"></i>
                                        Henüz bu araç için kayıtlı bakım geçmişi bulunmuyor. "Bakım Kaydı Ekle" butonuna basarak ilk servis kaydınızı ekleyin!
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="empty-box">
                    <div class="empty-icon">
                        <i class="fa-solid fa-car"></i>
                    </div>
                    <h3>Garajınızda Henüz Kayıtlı Bir Araç Yok</h3>
                    <p>Aracınızı dijital garajınıza ekleyerek periyodik bakımlarını, parça değişim tarihlerini ve sonraki bakım kilometrelerini tek bir ekrandan zahmetsizce takip edin.</p>
                    <button type="button" class="btn-primary" onclick="toggleElement('garage-add-vehicle-card');">
                        <i class="fa-solid fa-plus"></i> Hemen İlk Aracınızı Ekleyin
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- 4. TAB İÇERİK 2: FAVORİLERİM -->
        <div class="tab-pane" id="tab-favorites">
            <?php 
            if ( ! empty( $favorites ) ) :
                $fav_paged = max( 1, isset( $_GET['fav_paged'] ) ? intval( $_GET['fav_paged'] ) : 1 );
                $fav_query = new WP_Query( array(
                    'post_type'              => 'mechanic',
                    'post__in'               => $favorites,
                    'post_status'            => 'publish',
                    'posts_per_page'         => 12,
                    'paged'                  => $fav_paged,
                    'update_post_term_cache' => true,
                    'update_post_meta_cache' => true,
                ) );

                if ( $fav_query->have_posts() ) :
            ?>
                <div class="listing-grid">
                    <?php while ( $fav_query->have_posts() ) : $fav_query->the_post(); 
                        $image_url = get_post_meta( get_the_ID(), '_custom_mechanic_image', true );
                        if ( empty( $image_url ) ) {
                            $image_url = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'medium' ) : 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?auto=format&fit=crop&w=450&q=75';
                        }
                        if ( function_exists( 'ototamir_optimize_image_url' ) ) {
                            $image_url = ototamir_optimize_image_url( $image_url, 450 );
                        }
                    ?>
                        <a href="<?php the_permalink(); ?>" class="listing-card">
                            <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php the_title_attribute(); ?>" class="listing-card-bg" width="370" height="250" loading="lazy" decoding="async">
                            <div class="listing-card-overlay"></div>
                            
                            <div class="listing-card-content">
                                <?php $rating = get_mechanic_rating_data( get_the_ID() ); if( $rating['count'] > 0 ) : ?>
                                    <div class="listing-rating-pill"><?php echo esc_html( $rating['avg'] ); ?> <i class="fa-solid fa-star" style="font-size:11px; margin-left:2px;"></i></div>
                                <?php endif; ?>
                                
                                <h3 class="listing-title">
                                    <?php the_title(); ?> <i class="fa-solid fa-circle-check"></i>
                                </h3>
                            </div>
                            
                            <div class="listing-heart" style="color: #ef4444;" title="Favorilerde">
                                <i class="fa-solid fa-heart"></i>
                            </div>
                        </a>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>

                <?php if ( $fav_query->max_num_pages > 1 ) : ?>
                    <div class="profile-pagination">
                        <?php
                        echo paginate_links( array(
                            'base'         => add_query_arg( 'fav_paged', '%#%' ),
                            'format'       => '',
                            'prev_text'    => '<i class="fa-solid fa-chevron-left"></i> Önceki',
                            'next_text'    => 'Sonraki <i class="fa-solid fa-chevron-right"></i>',
                            'total'        => $fav_query->max_num_pages,
                            'current'      => $fav_paged,
                            'type'         => 'list',
                            'add_fragment' => '#favoriler',
                        ) );
                        ?>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="empty-box">
                    <div class="empty-icon">
                        <i class="fa-solid fa-heart-crack"></i>
                    </div>
                    <h3>Favori Listeniz Boş</h3>
                    <p>Beğendiğiniz veya aracınızı emanet etmek istediğiniz ustaların kartlarındaki kalp simgesine basarak bu listeye ekleyebilirsiniz.</p>
                    <a href="<?php echo esc_url( home_url('/ustalar') ); ?>" class="btn-primary">
                        <i class="fa-solid fa-magnifying-glass"></i> Ustaları Keşfet
                    </a>
                </div>
            <?php endif; ?>
            <?php else : ?>
                <div class="empty-box">
                    <div class="empty-icon">
                        <i class="fa-solid fa-heart-crack"></i>
                    </div>
                    <h3>Henüz Favori Ustanız Yok</h3>
                    <p>Beğendiğiniz veya aracınızı emanet etmek istediğiniz ustaların kartlarındaki kalp simgesine basarak bu listeye ekleyebilirsiniz.</p>
                    <a href="<?php echo esc_url( home_url('/ustalar') ); ?>" class="btn-primary">
                        <i class="fa-solid fa-magnifying-glass"></i> Ustaları Keşfet
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- 4. TAB İÇERİK 3: HESAP & ŞİFRE AYARLARI -->
        <div class="tab-pane" id="tab-settings">
            <div class="settings-box">
                <h2><i class="fa-solid fa-user-pen" style="color:#ea580c; margin-right:8px;"></i> Profil & Hesap Bilgileri</h2>

                <form method="post" action="#ayarlar">
                    <?php wp_nonce_field( 'ototamir_update_profile_action', 'ototamir_update_profile_nonce' ); ?>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="display_name">Ad Soyad / Firma Temsilcisi *</label>
                            <input type="text" id="display_name" name="display_name" value="<?php echo esc_attr( $current_user->display_name ); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="user_email">E-Posta Adresi *</label>
                            <input type="email" id="user_email" name="user_email" value="<?php echo esc_attr( $current_user->user_email ); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="user_phone">Telefon Numarası</label>
                        <input type="tel" id="user_phone" name="user_phone" value="<?php echo esc_attr( get_user_meta( $user_id, '_user_phone', true ) ); ?>" placeholder="05XX XXX XX XX">
                        <span class="form-note">İlanlarınız ve hesap bildirimleri için iletişim numaranız.</span>
                    </div>

                    <h2 style="margin-top: 36px;"><i class="fa-solid fa-lock" style="color:#ea580c; margin-right:8px;"></i> Şifre Değiştir</h2>
                    <p style="font-size:14px; color:#64748b; margin-top:-14px; margin-bottom:20px;">Şifrenizi değiştirmek istemiyorsanız bu alanları boş bırakabilirsiniz.</p>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="pass1">Yeni Şifre</label>
                            <input type="password" id="pass1" name="pass1" placeholder="En az 6 karakter" autocomplete="new-password">
                        </div>
                        <div class="form-group">
                            <label for="pass2">Yeni Şifre (Tekrar)</label>
                            <input type="password" id="pass2" name="pass2" placeholder="Şifrenizi tekrar girin" autocomplete="new-password">
                        </div>
                    </div>

                    <div style="margin-top: 24px;">
                        <button type="submit" class="btn-save-settings">
                            <i class="fa-solid fa-floppy-disk"></i> Değişiklikleri Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 4. TAB İÇERİK: CANLI DESTEK & YÖNETİCİ MESAJLAŞMA SİSTEMİ -->
        <div class="tab-pane" id="tab-support">
            <div class="support-chat-card">
                
                <!-- ÜST DESTEK BAŞLIĞI -->
                <div class="support-chat-header">
                    <div class="support-agent-info">
                        <div class="support-agent-avatar">
                            <i class="fa-solid fa-headset"></i>
                            <span class="support-online-dot" title="Yönetim Çevrimiçi"></span>
                        </div>
                        <div class="support-agent-text">
                            <h3>Ototamir360 Yönetim & Canlı Destek</h3>
                            <div class="support-agent-status">
                                <i class="fa-solid fa-circle"></i>
                                <span>Canlı Destek Hattı Aktif • Doğrudan Yöneticiye İletilir</span>
                            </div>
                        </div>
                    </div>
                    <div class="support-badge-guarantee" style="display:flex; align-items:center; gap:10px;">
                        <i class="fa-solid fa-shield-check" style="color:#22c55e;"></i>
                        <span>Güvenli & Hızlı Yanıt</span>
                        <button type="button" id="btn-user-sound-toggle" class="btn-sound-toggle" title="<?php esc_attr_e( 'Bildirim Sesini Aç/Kapat', 'ototamir-360' ); ?>">
                            <i class="fa-solid fa-volume-high"></i>
                        </button>
                    </div>
                </div>

                <!-- İLGİLİ İLAN BAĞLANTI BANNERI (VARSA) -->
                <?php if ( ! empty( $req_listing_title ) ) : ?>
                    <div class="support-listing-ref-banner">
                        <span><i class="fa-solid fa-wrench"></i> Bu mesajlaşma <strong>"<?php echo esc_html( $req_listing_title ); ?>"</strong> ilanınız ile ilişkilendirildi.</span>
                        <a href="<?php echo esc_url( get_permalink( $req_listing_id ) ); ?>" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> İlanı İncele</a>
                    </div>
                <?php endif; ?>

                <!-- MESAJ AKIŞI ALANI (SERVER-SIDE RENDERED: SIFIR BEKLEME, ANINDA GÖRÜNÜR) -->
                <div class="support-messages-stream" id="user-support-stream">
                    <?php 
                    $existing_messages = function_exists('ototamir_get_support_thread') ? ototamir_get_support_thread( $user_id ) : array();
                    $initial_max_admin_msg_id = 0;
                    if ( ! empty( $existing_messages ) ) {
                        foreach ( $existing_messages as $em_chk ) {
                            if ( $em_chk->sender_type === 'admin' && intval( $em_chk->id ) > $initial_max_admin_msg_id ) {
                                $initial_max_admin_msg_id = intval( $em_chk->id );
                            }
                        }
                    }
                    if ( empty( $existing_messages ) ) : ?>
                        <div style="margin:auto; text-align:center; padding:36px 20px; color:#64748b;">
                            <div style="width:64px; height:64px; border-radius:50%; background:#fff7ed; color:#ea580c; display:flex; align-items:center; justify-content:center; font-size:26px; margin:0 auto 16px;">
                                <i class="fa-solid fa-comments"></i>
                            </div>
                            <h4 style="margin:0 0 6px; font-size:17px; font-weight:800; color:#0f172a;">Canlı Destek Başlatın</h4>
                            <p style="margin:0 0 16px; font-size:13.5px; max-width:400px; margin-inline:auto; line-height:1.5;">
                                Merhaba <strong><?php echo esc_html( $current_user->display_name ?: $current_user->user_login ); ?></strong>! İlan onayları, hesap işlemleri veya sorularınız hakkında yöneticimize buradan doğrudan yazabilirsiniz.
                            </p>
                            <span style="display:inline-flex; align-items:center; gap:6px; font-size:12.5px; color:#059669; font-weight:700; background:#ecfdf5; padding:6px 14px; border-radius:20px;">
                                <i class="fa-solid fa-circle-check"></i> Mesajlarınız doğrudan site yöneticisine iletilir.
                            </span>
                        </div>
                    <?php else : ?>
                        <?php foreach ( $existing_messages as $em ) : 
                            $is_user = ($em->sender_type === 'user');
                            $wrap_class = $is_user ? 'from-user' : 'from-admin';
                            $time_fmt = date_i18n( 'j M H:i', strtotime( $em->created_at ) );
                        ?>
                            <div class="user-chat-bubble-wrap <?php echo $wrap_class; ?>">
                                <div class="user-chat-sender-tag">
                                    <?php if ( $is_user ) : ?>
                                        <span>Siz (<?php echo esc_html( $current_user->display_name ?: $current_user->user_login ); ?>)</span> <i class="fa-solid fa-user-check"></i>
                                    <?php else : ?>
                                        <i class="fa-solid fa-shield-halved" style="color:#ea580c;"></i> <span>Ototamir360 Yönetimi</span>
                                    <?php endif; ?>
                                </div>
                                <div class="user-chat-bubble"><?php echo nl2br( esc_html( stripslashes( $em->message ) ) ); ?></div>
                                <div class="user-chat-time"><?php echo esc_html( $time_fmt ); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- HIZLI SORU VE YARDIM ŞABLONLARI -->
                <div class="support-quick-chips">
                    <span class="support-quick-label"><i class="fa-regular fa-lightbulb"></i> Hızlı Sorular:</span>
                    <button type="button" class="btn-quick-chip" data-quick="Merhaba, eklediğim firma ilanımın onay durumu hakkında bilgi alabilir miyim?">📌 İlanımın Onay Durumu</button>
                    <button type="button" class="btn-quick-chip" data-quick="Merhaba, usta/firma profilimi ve iletişim bilgilerimi güncellemek istiyorum.">📝 Bilgi Güncelleme</button>
                    <button type="button" class="btn-quick-chip" data-quick="Merhaba, ilanımı bölgemde ve aramalarda nasıl öne çıkarabilirim?">⭐ İlanı Öne Çıkarma</button>
                    <button type="button" class="btn-quick-chip" data-quick="Merhaba, sitemizle ilgili bir öneri ve işbirliği konusu paylaşmak istiyorum.">💡 Öneri & İşbirliği</button>
                </div>

                <!-- MESAJ YAZMA PANELİ -->
                <div class="support-input-bar">
                    <div class="support-input-row">
                        <textarea id="user-support-input" placeholder="Yöneticiye iletmek istediğiniz mesajınızı buraya yazın... (Enter ile gönder)" rows="1"></textarea>
                        <button type="button" id="btn-user-support-send" class="btn-support-send" data-listing-id="<?php echo esc_attr( $req_listing_id ); ?>">
                            <i class="fa-solid fa-paper-plane"></i>
                            <span>Gönder</span>
                        </button>
                    </div>
                    <div class="support-input-notice">
                        <i class="fa-solid fa-circle-info" style="color:#3b82f6;"></i>
                        <span>Yöneticimiz yanıt verdiğinde <strong><?php echo esc_html( $current_user->user_email ); ?></strong> e-posta adresinize bilgilendirme gönderilir.</span>
                    </div>
                </div>

            </div>
        </div>

            </main><!-- /.admin-main-canvas -->
        </div><!-- /.admin-layout-grid -->

    </div>
</div>

<script>
// Toggle Görünürlük Yardımcısı (Garaj Formları vb.)
function toggleElement(id) {
    const el = document.getElementById(id);
    if (!el) return;
    if (el.style.display === 'none' || el.style.display === '') {
        el.style.display = 'block';
    } else {
        el.style.display = 'none';
    }
}

// Sekmeler Arası Geçiş (Tabs) ve Akıllı URL Yönlendirme (Query & Hash)
document.addEventListener('DOMContentLoaded', function() {
    // ============================================================
    // 1. CANLI DESTEK & MESAJLAŞMA SABİTLERİ (EN BAŞTA TANIMLANMALI)
    // ============================================================
    window.ototamirProfileChatActive = true;
    const supportAjaxUrl = "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>";
    const supportUserNonce = "<?php echo wp_create_nonce( 'ototamir_support_user_nonce' ); ?>";
    const currentUserName = "<?php echo esc_js( $current_user->display_name ?: $current_user->user_login ); ?>";
    let userLastSeenAdminMsgId = <?php echo intval( $initial_max_admin_msg_id ); ?>;
    let isFirstSupportPoll = true;
    let isSendingSupport = false;

    // Ses Butonu Durumu & Tıklama
    function updateUserSoundBtn() {
        const isMuted = localStorage.getItem('ototamir_sound_muted') === '1';
        const btn = document.getElementById('btn-user-sound-toggle');
        if (!btn) return;
        if (isMuted) {
            btn.classList.add('muted');
            btn.innerHTML = '<i class="fa-solid fa-volume-xmark"></i>';
            btn.title = 'Bildirim Sesi Kapalı (Açmak için tıklayın)';
        } else {
            btn.classList.remove('muted');
            btn.innerHTML = '<i class="fa-solid fa-volume-high"></i>';
            btn.title = 'Bildirim Sesi Açık (Kapatmak için tıklayın)';
        }
    }
    updateUserSoundBtn();

    const userSoundBtn = document.getElementById('btn-user-sound-toggle');
    if (userSoundBtn) {
        userSoundBtn.addEventListener('click', function() {
            const isMuted = localStorage.getItem('ototamir_sound_muted') === '1';
            if (isMuted) {
                localStorage.setItem('ototamir_sound_muted', '0');
                updateUserSoundBtn();
                if (window.OtotamirAudio) window.OtotamirAudio.playChime();
            } else {
                localStorage.setItem('ototamir_sound_muted', '1');
                updateUserSoundBtn();
            }
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function renderUserStream(messages, scrollBottom) {
        const stream = document.getElementById('user-support-stream');
        if (!stream) return;

        if (!messages || messages.length === 0) {
            stream.innerHTML = `
                <div style="margin:auto; text-align:center; padding:36px 20px; color:#64748b;">
                    <div style="width:64px; height:64px; border-radius:50%; background:#fff7ed; color:#ea580c; display:flex; align-items:center; justify-content:center; font-size:26px; margin:0 auto 16px;">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                    <h4 style="margin:0 0 6px; font-size:17px; font-weight:800; color:#0f172a;">Canlı Destek Başlatın</h4>
                    <p style="margin:0 0 16px; font-size:13.5px; max-width:400px; margin-inline:auto; line-height:1.5;">
                        Merhaba <strong>${currentUserName}</strong>! İlan onayları, hesap işlemleri veya sorularınız hakkında yöneticimize buradan doğrudan yazabilirsiniz.
                    </p>
                    <span style="display:inline-flex; align-items:center; gap:6px; font-size:12.5px; color:#059669; font-weight:700; background:#ecfdf5; padding:6px 14px; border-radius:20px;">
                        <i class="fa-solid fa-circle-check"></i> Mesajlarınız doğrudan site yöneticisine iletilir.
                    </span>
                </div>
            `;
            return;
        }

        let html = '';
        messages.forEach(m => {
            const isUser = (m.sender_type === 'user');
            const wrapClass = isUser ? 'from-user' : 'from-admin';
            const senderTag = isUser 
                ? `<span>Siz (${currentUserName})</span> <i class="fa-solid fa-user-check"></i>`
                : `<i class="fa-solid fa-shield-halved" style="color:#ea580c;"></i> <span>Ototamir360 Yönetimi</span>`;

            html += `
                <div class="user-chat-bubble-wrap ${wrapClass}">
                    <div class="user-chat-sender-tag">${senderTag}</div>
                    <div class="user-chat-bubble">${escapeHtml(m.message)}</div>
                    <div class="user-chat-time">${m.formatted_time}</div>
                </div>
            `;
        });

        stream.innerHTML = html;

        if (scrollBottom) {
            stream.scrollTop = stream.scrollHeight;
        }
    }

    function loadUserSupportMessages(scrollBottom) {
        const stream = document.getElementById('user-support-stream');
        if (!stream) return;

        const formData = new FormData();
        formData.append('action', 'ototamir_user_get_support');
        formData.append('security', supportUserNonce);

        fetch(supportAjaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(res => res.text())
        .then(text => {
            let json = null;
            try {
                json = JSON.parse(text);
            } catch (e) {
                // Non-JSON response
            }

            if (json && json.success && json.data && Array.isArray(json.data.messages)) {
                renderUserStream(json.data.messages, scrollBottom);

                // Yeni Yönetici Mesajı Kontrolü (Ses + Sol Alt Push Bildirimi)
                let newAdminMsg = null;
                json.data.messages.forEach(m => {
                    if (m.sender_type === 'admin') {
                        const mid = parseInt(m.id, 10);
                        if (mid > userLastSeenAdminMsgId) {
                            newAdminMsg = m;
                            userLastSeenAdminMsgId = mid;
                        }
                    }
                });

                if (newAdminMsg && !isFirstSupportPoll) {
                    if (window.OtotamirToast) {
                        window.OtotamirToast.show({
                            iconHtml: '<i class="fa-solid fa-headset" style="font-size:20px;"></i>',
                            title: 'Ototamir360 Yönetimi',
                            message: newAdminMsg.message,
                            timeText: newAdminMsg.formatted_time || 'Şimdi',
                            actionText: 'Destek Sekmesini Gör',
                            onClick: function() {
                                if (typeof switchTab === 'function') {
                                    switchTab('tab-support');
                                    const s = document.getElementById('user-support-stream');
                                    if (s) s.scrollTop = s.scrollHeight;
                                }
                            }
                        });
                    }
                }
                isFirstSupportPoll = false;
            }
        })
        .catch(err => {
            console.error('Destek mesajları yüklenirken hata:', err);
        });
    }

    function sendUserSupport() {
        if (isSendingSupport) return;

        const input = document.getElementById('user-support-input');
        const btn = document.getElementById('btn-user-support-send');
        if (!input || !btn) return;

        const msg = input.value.trim();
        if (msg.length < 2) {
            input.focus();
            return;
        }

        const listingId = btn.getAttribute('data-listing-id') || '0';

        isSendingSupport = true;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>İletiliyor...</span>';

        const formData = new FormData();
        formData.append('action', 'ototamir_user_send_support');
        formData.append('message', msg);
        formData.append('listing_id', listingId);
        formData.append('security', supportUserNonce);

        fetch(supportAjaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(res => res.text())
        .then(text => {
            isSendingSupport = false;
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> <span>Gönder</span>';

            let json = null;
            try {
                json = JSON.parse(text);
            } catch (e) {
                // Non-JSON response
            }

            if (json && json.success) {
                input.value = '';
                loadUserSupportMessages(true);
            } else if (json && json.data && json.data.message) {
                alert(json.data.message);
            } else {
                input.value = '';
                loadUserSupportMessages(true);
            }
        })
        .catch(err => {
            isSendingSupport = false;
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> <span>Gönder</span>';
            alert('Sunucuyla bağlantı kurulurken hata oluştu.');
        });
    }

    // ============================================================
    // 2. TAB YÖNETİMİ & NAVİGASYON
    // ============================================================
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');

    const tabAliases = {
        'ilanlarim': 'tab-firms',
        'ilanlar': 'tab-firms',
        'firmalarim': 'tab-firms',
        'firmalar': 'tab-firms',
        'firms': 'tab-firms',
        'goruntulemeler': 'tab-analytics',
        'goruntulenmeler': 'tab-analytics',
        'analitik': 'tab-analytics',
        'views': 'tab-analytics',
        'analytics': 'tab-analytics',
        'ariza-tespiti': 'tab-ai',
        'ai-teshis': 'tab-ai',
        'ai': 'tab-ai',
        'teshis': 'tab-ai',
        'favoriler': 'tab-favorites',
        'favorilerim': 'tab-favorites',
        'begendiklerim': 'tab-favorites',
        'favorites': 'tab-favorites',
        'garaj': 'tab-garage',
        'garajim': 'tab-garage',
        'garage': 'tab-garage',
        'destek': 'tab-support',
        'support': 'tab-support',
        'mesajlar': 'tab-support',
        'yardim': 'tab-support',
        'canli-destek': 'tab-support',
        'ayarlar': 'tab-settings',
        'profil': 'tab-settings',
        'settings': 'tab-settings'
    };

    function switchTab(targetId, updateHistory) {
        if (typeof updateHistory === 'undefined') updateHistory = true;
        if (!targetId) return;

        if (tabAliases[targetId]) {
            targetId = tabAliases[targetId];
        }

        const targetPane = document.getElementById(targetId);
        if (!targetPane) return;

        tabBtns.forEach(btn => {
            if (btn.getAttribute('data-tab') === targetId) {
                btn.classList.add('active');
                if (btn.scrollIntoView) {
                    btn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }
            } else {
                btn.classList.remove('active');
            }
        });

        tabPanes.forEach(pane => {
            if (pane.id === targetId) {
                pane.classList.add('active');
            } else {
                pane.classList.remove('active');
            }
        });

        if (targetId === 'tab-support') {
            const stream = document.getElementById('user-support-stream');
            if (stream) stream.scrollTop = stream.scrollHeight;
            const badge = document.getElementById('user-support-unread-badge');
            if (badge) badge.style.display = 'none';
            loadUserSupportMessages(false);
        }

        if (updateHistory && history.pushState) {
            const cleanSlug = targetId.replace('tab-', '');
            const slugMap = {
                'firms': 'ilanlarim',
                'analytics': 'goruntulemeler',
                'ai': 'ariza-tespiti',
                'favorites': 'favoriler',
                'garage': 'garaj',
                'support': 'destek',
                'settings': 'ayarlar'
            };
            const friendlySlug = slugMap[cleanSlug] || cleanSlug;
            history.pushState(null, null, '#' + friendlySlug);
        }
    }

    // Tab butonlarına tıklama
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-tab');
            switchTab(targetId, true);
        });
    });

    // KPI Kartları ve Hızlı Geçiş Elemanlarına Tıklama
    const quickTabTriggers = document.querySelectorAll('[data-open-tab]');
    quickTabTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-open-tab');
            if (targetId) {
                switchTab(targetId, true);
                const mainCanvas = document.querySelector('.admin-main-canvas');
                if (mainCanvas && window.innerWidth <= 1024) {
                    mainCanvas.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    });

    // İlanlarım İçinde Canlı / Anında Arama Filtresi
    const firmSearchInput = document.getElementById('filter-firms-search');
    if (firmSearchInput) {
        firmSearchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.firm-cards-list .firm-item-card');
            let matchCount = 0;
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                if (!query || text.includes(query)) {
                    card.style.display = 'flex';
                    matchCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            let noResultBox = document.getElementById('firms-filter-no-results');
            const cardsList = document.querySelector('.firm-cards-list');
            if (cardsList) {
                if (matchCount === 0 && query) {
                    if (!noResultBox) {
                        noResultBox = document.createElement('div');
                        noResultBox.id = 'firms-filter-no-results';
                        noResultBox.className = 'empty-box';
                        noResultBox.style.padding = '34px 20px';
                        noResultBox.innerHTML = `
                            <div class="empty-icon" style="width:50px; height:50px; font-size:20px; margin-bottom:12px;"><i class="fa-solid fa-magnifying-glass"></i></div>
                            <h4 style="margin:0 0 6px; font-size:16px; font-weight:800; color:#0f172a;">Sonuç Bulunamadı</h4>
                            <p style="margin:0; font-size:13px; color:#64748b;">"<strong>${escapeHtml(query)}</strong>" aramasına uyan bir ilanınız bulunamadı.</p>
                        `;
                        cardsList.appendChild(noResultBox);
                    }
                } else if (noResultBox) {
                    noResultBox.remove();
                }
            }
        });
    }

    // 1. URL Query Kontrolü (?tab=ilanlarim veya ?listing_id=123)
    const urlParams = new URLSearchParams(window.location.search);
    const queryTab = urlParams.get('tab');
    const queryListing = urlParams.get('listing_id');

    // 2. Hash Kontrolü (#goruntulemeler)
    const currentHash = window.location.hash.replace('#', '').toLowerCase();

    if (queryTab && tabAliases[queryTab.toLowerCase()]) {
        switchTab(tabAliases[queryTab.toLowerCase()], false);
    } else if (queryListing) {
        switchTab('tab-support', false);
    } else if (currentHash && tabAliases[currentHash]) {
        switchTab(tabAliases[currentHash], false);
    } else {
        const stream = document.getElementById('user-support-stream');
        if (stream) stream.scrollTop = stream.scrollHeight;
    }

    // Tarayıcı İleri/Geri Butonları İçin Popstate Desteği
    window.addEventListener('popstate', function() {
        const hash = window.location.hash.replace('#', '').toLowerCase();
        if (hash && tabAliases[hash]) {
            switchTab(tabAliases[hash], false);
        } else {
            switchTab('tab-firms', false);
        }
    });

    // Gönderme Butonu & Input Dinleyicileri
    const sendBtn = document.getElementById('btn-user-support-send');
    if (sendBtn) {
        sendBtn.addEventListener('click', function(e) {
            e.preventDefault();
            sendUserSupport();
        });
    }

    const chatInput = document.getElementById('user-support-input');
    if (chatInput) {
        chatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendUserSupport();
            }
        });
    }

    // Hızlı Çip Butonları
    const quickChips = document.querySelectorAll('.btn-quick-chip');
    quickChips.forEach(chip => {
        chip.addEventListener('click', function() {
            const txt = this.getAttribute('data-quick');
            if (chatInput && txt) {
                chatInput.value = txt;
                chatInput.focus();
            }
        });
    });

    // Otomatik Canlı Yoklama (Profil sayfası açıkken 10 saniyede bir yeni yönetici yanıtı var mı kontrol et)
    setInterval(function() {
        const supportPane = document.getElementById('tab-support');
        const isSupportActive = supportPane && supportPane.classList.contains('active');
        loadUserSupportMessages(isSupportActive);
    }, 10000);
});
</script>

<?php if ( empty( $user_survey_completed ) ) : ?>
<!-- ===================================================
     YENİ ÜYE KARŞILAMA VE PROFİL ANKETİ MODALI (ONBOARDING WIZARD)
     =================================================== -->
<style>
.survey-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    box-sizing: border-box;
}
.survey-modal-card {
    background: #ffffff;
    border-radius: 24px;
    width: 100%;
    max-width: 580px;
    box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35);
    border: 1px solid rgba(226, 232, 240, 0.8);
    overflow: hidden;
    position: relative;
    animation: surveyModalIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes surveyModalIn {
    from { opacity: 0; transform: scale(0.96) translateY(12px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.survey-modal-header {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    color: white;
    padding: 24px 28px;
    position: relative;
}
.survey-modal-header h3 {
    margin: 0 0 6px 0;
    font-size: 20px;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 10px;
}
.survey-modal-header p {
    margin: 0;
    font-size: 13.5px;
    color: #94a3b8;
    line-height: 1.4;
}
.survey-modal-close {
    position: absolute;
    top: 20px;
    right: 20px;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: #cbd5e1;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    transition: all 0.2s;
}
.survey-modal-close:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}
.survey-progress-wrap {
    height: 5px;
    background: #e2e8f0;
    width: 100%;
}
.survey-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #f97316 0%, #ea580c 100%);
    width: 25%;
    transition: width 0.3s ease;
}
.survey-modal-body {
    padding: 28px;
    max-height: 70vh;
    overflow-y: auto;
}
.survey-step-pane {
    display: none;
}
.survey-step-pane.active {
    display: block;
}
.survey-role-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 18px;
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    margin-bottom: 12px;
    cursor: pointer;
    transition: all 0.2s;
}
.survey-role-card:hover {
    border-color: #fdba74;
    background: #fffaf5;
}
.survey-role-card.selected {
    border-color: #ea580c;
    background: #fff7ed;
    box-shadow: 0 4px 12px rgba(234, 88, 12, 0.12);
}
.survey-role-card input[type="radio"] {
    accent-color: #ea580c;
    width: 18px;
    height: 18px;
    cursor: pointer;
}
.survey-role-card .role-icon {
    font-size: 24px;
}
.survey-role-card .role-text strong {
    display: block;
    font-size: 14.5px;
    color: #1e293b;
    margin-bottom: 2px;
}
.survey-role-card .role-text span {
    font-size: 12.5px;
    color: #64748b;
}
.survey-modal-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 28px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}
.survey-btn-secondary {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #cbd5e1;
    padding: 10px 18px;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.survey-btn-secondary:hover {
    background: #e2e8f0;
    color: #1e293b;
}
.survey-btn-primary {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: white;
    border: none;
    padding: 10px 22px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 3px 10px rgba(234, 88, 12, 0.3);
    transition: all 0.2s;
}
.survey-btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 5px 14px rgba(234, 88, 12, 0.4);
}
.survey-btn-snooze {
    background: transparent;
    border: none;
    color: #94a3b8;
    font-size: 12.5px;
    cursor: pointer;
    text-decoration: underline;
    padding: 6px 10px;
}
.survey-btn-snooze:hover {
    color: #64748b;
}
</style>

<div class="survey-modal-overlay" id="ototamirSurveyModalOverlay" onclick="if(event.target===this) dismissOnboardingSurveyModal();">
    <div class="survey-modal-card">
        <div class="survey-modal-header">
            <button type="button" class="survey-modal-close" onclick="dismissOnboardingSurveyModal();" title="Daha Sonra Hatırlat">&times;</button>
            <h3><i class="fa-solid fa-wand-magic-sparkles" style="color:#f97316;"></i> Aramıza Hoş Geldiniz!</h3>
            <p>Deneyiminizi özelleştirmek için 4 kısa soruluk mini anketimizi tamamlayın.</p>
        </div>
        
        <div class="survey-progress-wrap">
            <div class="survey-progress-bar" id="surveyProgressBar"></div>
        </div>

        <form id="ototamirSurveyForm" onsubmit="submitOnboardingSurvey(event);">
            <input type="hidden" name="action" value="ototamir_submit_onboarding_survey">
            <input type="hidden" name="nonce" value="<?php echo esc_attr( $survey_nonce ); ?>">

            <div class="survey-modal-body">
                
                <!-- ADIM 1: KULLANICI ROLÜ -->
                <div class="survey-step-pane active" id="surveyStep1">
                    <div style="margin-bottom: 16px;">
                        <span style="font-size:12px; font-weight:700; color:#ea580c; text-transform:uppercase; letter-spacing:0.5px;">Adım 1 / 4</span>
                        <h4 style="margin:4px 0 6px 0; font-size:17px; color:#0f172a; font-weight:800;">OtoTamirciBul'u hangi amaçla kullanıyorsunuz?</h4>
                        <p style="margin:0; font-size:13px; color:#64748b;">Size en doğru hizmetleri önerebilmemiz için rolünüzü seçin.</p>
                    </div>

                    <label class="survey-role-card selected" onclick="selectSurveyRole('car_owner', this)">
                        <input type="radio" name="role" value="car_owner" checked>
                        <span class="role-icon">🚗</span>
                        <div class="role-text">
                            <strong>Araç Sahibiyim</strong>
                            <span>Aracıma güvenilir usta, bakım ve yedek parça arıyorum.</span>
                        </div>
                    </label>

                    <label class="survey-role-card" onclick="selectSurveyRole('mechanic', this)">
                        <input type="radio" name="role" value="mechanic">
                        <span class="role-icon">🔧</span>
                        <div class="role-text">
                            <strong>Oto Tamircisi / Ustayım</strong>
                            <span>Hizmetlerimi tanıtmak ve yeni müşteriler kazanmak istiyorum.</span>
                        </div>
                    </label>

                    <label class="survey-role-card" onclick="selectSurveyRole('business', this)">
                        <input type="radio" name="role" value="business">
                        <span class="role-icon">🏢</span>
                        <div class="role-text">
                            <strong>Yedek Parça / Sektörel Firma</strong>
                            <span>Yedek parça, ekspertiz, sigorta veya filo hizmeti sunuyorum.</span>
                        </div>
                    </label>

                    <label class="survey-role-card" onclick="selectSurveyRole('visitor', this)">
                        <input type="radio" name="role" value="visitor">
                        <span class="role-icon">🔍</span>
                        <div class="role-text">
                            <strong>Genel Ziyaretçiyim</strong>
                            <span>Piyasa fiyatlarını incelemek veya bilgi edinmek için buradayım.</span>
                        </div>
                    </label>
                </div>

                <!-- ADIM 2: MARKA & MODEL / HİZMET İHTİYACI -->
                <div class="survey-step-pane" id="surveyStep2">
                    <div style="margin-bottom: 16px;">
                        <span style="font-size:12px; font-weight:700; color:#ea580c; text-transform:uppercase; letter-spacing:0.5px;">Adım 2 / 4</span>
                        <h4 style="margin:4px 0 6px 0; font-size:17px; color:#0f172a; font-weight:800;" id="surveyStep2Title">Kullandığınız Araç Nedir?</h4>
                        <p style="margin:0; font-size:13px; color:#64748b;" id="surveyStep2Desc">Aracınızı seçin, profilinizdeki Dijital Garaj'a otomatik ekleyelim.</p>
                    </div>

                    <!-- Araç Sahipleri İçin Marka & Model -->
                    <div id="surveyVehicleFields">
                        <div style="margin-bottom: 16px;">
                            <label style="display:block; margin-bottom:6px; font-weight:600; font-size:13px; color:#334155;">Araç Markanız</label>
                            <select name="brand" id="surveyBrandSelect" class="form-control" style="width:100%; padding:11px 14px; border:1.5px solid #cbd5e1; border-radius:10px; font-size:14px; background:white;">
                                <option value="">Marka Seçin...</option>
                                <?php
                                $survey_brands = get_terms(array('taxonomy' => 'car_brand', 'parent' => 0, 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC'));
                                foreach($survey_brands as $sb) {
                                    echo '<option value="'.esc_attr($sb->name).'" data-slug="'.esc_attr($sb->slug).'">'.esc_html($sb->name).'</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div style="margin-bottom: 16px;" id="surveyModelFieldWrap">
                            <label style="display:block; margin-bottom:6px; font-weight:600; font-size:13px; color:#334155;">Araç Modeliniz</label>
                            <select name="model" id="surveyModelSelect" class="form-control" style="width:100%; padding:11px 14px; border:1.5px solid #cbd5e1; border-radius:10px; font-size:14px; background:white;">
                                <option value="">Önce Marka Seçin</option>
                            </select>
                        </div>

                        <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:12px 16px; display:flex; align-items:flex-start; gap:10px; font-size:13px; color:#15803d;">
                            <i class="fa-solid fa-circle-check" style="font-size:16px; margin-top:2px;"></i>
                            <div><strong>Otomatik Garaj Tanımlaması:</strong> Bu araç profilinizdeki <strong>Araç Garajım</strong> defterinize doğrudan eklenecek ve bakım kayıtlarınızı tek tıkla tutabileceksiniz.</div>
                        </div>
                    </div>

                    <!-- Ustalar ve İşletmeler İçin Öncelikli Alan -->
                    <div id="surveyServiceFields" style="display: none;">
                        <div style="margin-bottom: 16px;">
                            <label style="display:block; margin-bottom:6px; font-weight:600; font-size:13px; color:#334155;">Öncelikli Hizmet Alanınız / İhtiyacınız</label>
                            <select name="primary_need" id="surveyPrimaryNeed" class="form-control" style="width:100%; padding:11px 14px; border:1.5px solid #cbd5e1; border-radius:10px; font-size:14px; background:white;">
                                <option value="Periyodik Bakım & Yağ Değişimi">Periyodik Bakım & Yağ Değişimi</option>
                                <option value="Motor & Mekanik Tamir">Motor & Mekanik Tamir</option>
                                <option value="Oto Elektrik & Beyin (Elektronik)">Oto Elektrik & Beyin (Elektronik)</option>
                                <option value="Fren & Alt Takım">Fren & Alt Takım</option>
                                <option value="Kaporta & Boya">Kaporta & Boya</option>
                                <option value="Oto Ekspertiz">Oto Ekspertiz</option>
                                <option value="Yedek Parça & Aksesuar">Yedek Parça & Aksesuar</option>
                                <option value="Yeni Müşteri Edinimi">Yeni Müşteri Edinimi / Tanıtım</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- ADIM 3: ŞEHİR & İLÇE -->
                <div class="survey-step-pane" id="surveyStep3">
                    <div style="margin-bottom: 16px;">
                        <span style="font-size:12px; font-weight:700; color:#ea580c; text-transform:uppercase; letter-spacing:0.5px;">Adım 3 / 4</span>
                        <h4 style="margin:4px 0 6px 0; font-size:17px; color:#0f172a; font-weight:800;">Hangi Şehirde Yaşıyorsunuz?</h4>
                        <p style="margin:0; font-size:13px; color:#64748b;">Size en yakın servisleri ve yerel fırsatları önerebilmemiz için konumunuzu belirtin.</p>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display:block; margin-bottom:6px; font-weight:600; font-size:13px; color:#334155;">İl</label>
                        <select name="city" id="surveyCitySelect" class="form-control" style="width:100%; padding:11px 14px; border:1.5px solid #cbd5e1; border-radius:10px; font-size:14px; background:white;">
                            <option value="">İl Seçiniz...</option>
                            <?php 
                            $cities = get_terms(array('taxonomy' => 'mechanic_city', 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC'));
                            $user_saved_city = get_user_meta($user_id, '_user_city', true);
                            foreach($cities as $c) {
                                $c_sel = ($user_saved_city === $c->name) ? 'selected' : '';
                                echo '<option value="'.esc_attr($c->name).'" '.$c_sel.'>'.esc_html($c->name).'</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display:block; margin-bottom:6px; font-weight:600; font-size:13px; color:#334155;">İlçe (Opsiyonel)</label>
                        <input type="text" name="district" placeholder="Örn: Kadıköy, Çankaya, Nilüfer..." style="width:100%; padding:11px 14px; border:1.5px solid #cbd5e1; border-radius:10px; font-size:14px;">
                    </div>
                </div>

                <!-- ADIM 4: KAYNAK VE GERİ BİLDİRİM -->
                <div class="survey-step-pane" id="surveyStep4">
                    <div style="margin-bottom: 16px;">
                        <span style="font-size:12px; font-weight:700; color:#ea580c; text-transform:uppercase; letter-spacing:0.5px;">Adım 4 / 4</span>
                        <h4 style="margin:4px 0 6px 0; font-size:17px; color:#0f172a; font-weight:800;">Son Birkaç Soru</h4>
                        <p style="margin:0; font-size:13px; color:#64748b;">Geri bildiriminiz platformumuzu geliştirmemize ışık tutacaktır.</p>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display:block; margin-bottom:6px; font-weight:600; font-size:13px; color:#334155;">OtoTamirciBul'u nereden duydunuz?</label>
                        <select name="source" class="form-control" style="width:100%; padding:11px 14px; border:1.5px solid #cbd5e1; border-radius:10px; font-size:14px; background:white;">
                            <option value="Google Arama">Google Arama / İnternet</option>
                            <option value="Instagram / Sosyal Medya">Instagram / Sosyal Medya</option>
                            <option value="Arkadaş / Usta Tavsiyesi">Arkadaş / Usta Tavsiyesi</option>
                            <option value="Sanayi Sitesi / Tabela">Sanayi Sitesi / Tabela</option>
                            <option value="Diğer">Diğer</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display:block; margin-bottom:6px; font-weight:600; font-size:13px; color:#334155;">Eklemek istediğiniz öneri veya talep var mı? (Opsiyonel)</label>
                        <textarea name="notes" rows="3" placeholder="Görüş ve önerileriniz bizim için çok kıymetli..." style="width:100%; padding:10px 14px; border:1.5px solid #cbd5e1; border-radius:10px; font-size:13.5px;"></textarea>
                    </div>

                    <div id="surveyAlertWrap" style="display:none; margin-top:12px; padding:12px; border-radius:8px; font-size:13.5px; font-weight:600;"></div>
                </div>

            </div>

            <div class="survey-modal-footer">
                <div>
                    <button type="button" class="survey-btn-secondary" id="surveyBtnBack" onclick="goToSurveyStep(currentSurveyStep - 1);" style="display:none;">
                        <i class="fa-solid fa-arrow-left"></i> Geri
                    </button>
                    <button type="button" class="survey-btn-snooze" id="surveyBtnSnooze" onclick="dismissOnboardingSurveyModal();">
                        Şimdilik Geç / Daha Sonra
                    </button>
                </div>
                <div>
                    <button type="button" class="survey-btn-primary" id="surveyBtnNext" onclick="goToSurveyStep(currentSurveyStep + 1);">
                        İleri <i class="fa-solid fa-arrow-right"></i>
                    </button>
                    <button type="submit" class="survey-btn-primary" id="surveyBtnSubmit" style="display:none;">
                        <i class="fa-solid fa-check"></i> Tamamla & Kaydet
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
var currentSurveyStep = 1;
var selectedRole = 'car_owner';

function openOnboardingSurveyModal() {
    var uid = '<?php echo esc_js( $user_id ); ?>';
    try {
        if (localStorage.getItem('ototamir_survey_completed_' + uid) === '1' || localStorage.getItem('ototamir_survey_completed') === '1') {
            return;
        }
    } catch(e) {}
    if (document.cookie.indexOf('ototamir_survey_completed_' + uid + '=1') !== -1 || document.cookie.indexOf('ototamir_survey_completed=1') !== -1) {
        return;
    }
    var modal = document.getElementById('ototamirSurveyModalOverlay');
    if (modal) {
        modal.style.display = 'flex';
        goToSurveyStep(1);
    }
}

function dismissOnboardingSurveyModal() {
    var uid = '<?php echo esc_js( $user_id ); ?>';
    var modal = document.getElementById('ototamirSurveyModalOverlay');
    if (modal) {
        modal.style.display = 'none';
    }
    try {
        localStorage.setItem('ototamir_survey_dismissed_' + uid, (Date.now() + (7 * 86400000)).toString());
    } catch(e) {}
    document.cookie = 'ototamir_survey_dismissed_' + uid + '=1; path=/; max-age=604800; SameSite=Lax';

    // Sunucuya erteleme (snooze) bilgisini gönder
    var formData = new FormData();
    formData.append('action', 'ototamir_dismiss_onboarding_survey');
    formData.append('nonce', '<?php echo esc_js( $survey_nonce ); ?>');
    fetch('<?php echo esc_url( admin_url('admin-ajax.php') ); ?>', {
        method: 'POST',
        body: formData
    });
}

function selectSurveyRole(role, element) {
    selectedRole = role;
    var cards = document.querySelectorAll('.survey-role-card');
    cards.forEach(function(c) { c.classList.remove('selected'); });
    if (element) {
        element.classList.add('selected');
        var radio = element.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
    }

    var vehicleFields = document.getElementById('surveyVehicleFields');
    var serviceFields = document.getElementById('surveyServiceFields');
    var s2Title = document.getElementById('surveyStep2Title');
    var s2Desc = document.getElementById('surveyStep2Desc');

    if (role === 'car_owner' || role === 'visitor') {
        if (vehicleFields) vehicleFields.style.display = 'block';
        if (serviceFields) serviceFields.style.display = 'none';
        if (s2Title) s2Title.textContent = 'Kullandığınız Araç Nedir?';
        if (s2Desc) s2Desc.textContent = 'Aracınızı seçin, profilinizdeki Dijital Garaj\'a otomatik ekleyelim.';
    } else {
        if (vehicleFields) vehicleFields.style.display = 'none';
        if (serviceFields) serviceFields.style.display = 'block';
        if (s2Title) s2Title.textContent = 'Öncelikli Hizmet Alanınız';
        if (s2Desc) s2Desc.textContent = 'Platformda en çok hangi alanda hizmet sunuyor veya talep ediyorsunuz?';
    }
}

function goToSurveyStep(step) {
    if (step < 1) step = 1;
    if (step > 4) step = 4;
    currentSurveyStep = step;

    for (var i = 1; i <= 4; i++) {
        var pane = document.getElementById('surveyStep' + i);
        if (pane) {
            if (i === step) pane.classList.add('active');
            else pane.classList.remove('active');
        }
    }

    var pBar = document.getElementById('surveyProgressBar');
    if (pBar) {
        pBar.style.width = (step * 25) + '%';
    }

    var btnBack = document.getElementById('surveyBtnBack');
    var btnNext = document.getElementById('surveyBtnNext');
    var btnSubmit = document.getElementById('surveyBtnSubmit');

    if (btnBack) btnBack.style.display = (step > 1) ? 'inline-flex' : 'none';
    if (btnNext) btnNext.style.display = (step < 4) ? 'inline-flex' : 'none';
    if (btnSubmit) btnSubmit.style.display = (step === 4) ? 'inline-flex' : 'none';
}

// Marka değiştiğinde modelleri AJAX ile yükle
document.addEventListener("DOMContentLoaded", function() {
    var brandSelect = document.getElementById('surveyBrandSelect');
    var modelSelect = document.getElementById('surveyModelSelect');

    if (brandSelect && modelSelect) {
        brandSelect.addEventListener('change', function() {
            var selectedOpt = this.options[this.selectedIndex];
            var brandSlug = selectedOpt ? selectedOpt.getAttribute('data-slug') : '';
            var brandVal = this.value;

            if (!brandVal) {
                modelSelect.innerHTML = '<option value="">Önce Marka Seçin</option>';
                return;
            }

            modelSelect.innerHTML = '<option value="">Modeller yükleniyor...</option>';
            var ajaxUrl = '<?php echo esc_url( admin_url('admin-ajax.php') ); ?>?action=ototamir_get_models_by_brand&brand=' + encodeURIComponent(brandSlug || brandVal);
            
            fetch(ajaxUrl)
                .then(function(res) { return res.json(); })
                .then(function(json) {
                    modelSelect.innerHTML = '<option value="">Model Seçiniz...</option>';
                    if (json.success && json.data && json.data.models && json.data.models.length > 0) {
                        json.data.models.forEach(function(m) {
                            var opt = document.createElement('option');
                            opt.value = m.name;
                            opt.textContent = m.name;
                            modelSelect.appendChild(opt);
                        });
                    } else {
                        modelSelect.innerHTML = '<option value="">Model Listelenemedi (Tüm Modeller)</option>';
                    }
                })
                .catch(function() {
                    modelSelect.innerHTML = '<option value="">Model Seçiniz...</option>';
                });
        });
    }

    // Yeni kullanıcıysa modalı 800ms sonra otomatik aç
    <?php if ( ! empty( $should_show_survey ) ) : ?>
    setTimeout(function() {
        var uid = '<?php echo esc_js( $user_id ); ?>';
        try {
            if (localStorage.getItem('ototamir_survey_completed_' + uid) === '1' || localStorage.getItem('ototamir_survey_completed') === '1') {
                return;
            }
            var snoozedUntil = localStorage.getItem('ototamir_survey_dismissed_' + uid);
            if (snoozedUntil && parseInt(snoozedUntil) > Date.now()) {
                return;
            }
        } catch(e) {}
        if (document.cookie.indexOf('ototamir_survey_completed_' + uid + '=1') !== -1 || document.cookie.indexOf('ototamir_survey_completed=1') !== -1 || document.cookie.indexOf('ototamir_survey_dismissed_' + uid) !== -1) {
            return;
        }
        openOnboardingSurveyModal();
    }, 800);
    <?php endif; ?>
});

function submitOnboardingSurvey(e) {
    e.preventDefault();
    var form = document.getElementById('ototamirSurveyForm');
    var btnSubmit = document.getElementById('surveyBtnSubmit');
    var alertWrap = document.getElementById('surveyAlertWrap');

    if (!form || !btnSubmit) return;

    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Kaydediliyor...';

    var formData = new FormData(form);

    fetch('<?php echo esc_url( admin_url('admin-ajax.php') ); ?>', {
        method: 'POST',
        body: formData
    })
    .then(function(res) { return res.json(); })
    .then(function(json) {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fa-solid fa-check"></i> Tamamla & Kaydet';

        if (json.success) {
            var uid = '<?php echo esc_js( $user_id ); ?>';
            try {
                localStorage.setItem('ototamir_survey_completed_' + uid, '1');
                localStorage.setItem('ototamir_survey_completed', '1');
            } catch(e) {}
            document.cookie = 'ototamir_survey_completed_' + uid + '=1; path=/; max-age=31536000; SameSite=Lax';
            document.cookie = 'ototamir_survey_completed=1; path=/; max-age=31536000; SameSite=Lax';

            var sidebarBtn = document.getElementById('sidebar-survey-btn');
            if (sidebarBtn) sidebarBtn.style.display = 'none';

            var banner = document.getElementById('survey-welcome-banner');
            if (banner) banner.style.display = 'none';

            if (alertWrap) {
                alertWrap.style.display = 'block';
                alertWrap.style.background = '#f0fdf4';
                alertWrap.style.border = '1px solid #bbf7d0';
                alertWrap.style.color = '#15803d';
                alertWrap.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + (json.data && json.data.message ? json.data.message : 'Anket başarıyla kaydedildi!');
            }

            setTimeout(function() {
                var modal = document.getElementById('ototamirSurveyModalOverlay');
                if (modal) modal.style.display = 'none';
                // Eğer garaja araç eklendiyse garaj sekmesine yönlendir, sonra sayfayı yenile
                if (json.data && json.data.auto_garage_added) {
                    window.location.hash = 'garaj';
                }
                window.location.reload();
            }, 900);
        } else {
            if (alertWrap) {
                alertWrap.style.display = 'block';
                alertWrap.style.background = '#fef2f2';
                alertWrap.style.border = '1px solid #fecaca';
                alertWrap.style.color = '#991b1b';
                alertWrap.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + (json.data && json.data.message ? json.data.message : 'Bir hata oluştu.');
            }
        }
    })
    .catch(function() {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fa-solid fa-check"></i> Tamamla & Kaydet';
        if (alertWrap) {
            alertWrap.style.display = 'block';
            alertWrap.style.background = '#fef2f2';
            alertWrap.style.border = '1px solid #fecaca';
            alertWrap.style.color = '#991b1b';
            alertWrap.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> Bağlantı hatası oluştu, lütfen tekrar deneyin.';
        }
    });
}
</script>
<?php endif; ?>

<?php get_footer(); ?>
