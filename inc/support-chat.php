<?php
/**
 * Ototamir360 - Canlı Destek & Kullanıcı Mesajlaşma Sistemi
 * 
 * Eklentisiz, sıfır harici bağımlılıkla çalışan; üyelerin profil panelinden
 * yöneticiye canlı mesaj göndermesini, yöneticinin wp-admin üzerinden yanıtlamasını,
 * ilan referanslı destek alabilmesini ve e-posta bildirimlerini sağlayan modül.
 * 
 * @package Ototamir360
 * @version 1.4.24
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'OTOTAMIR_SUPPORT_DB_VER', '1.0' );

// ============================================================
// 1. VERİTABANI TABLOSU KURULUMU
// ============================================================
function ototamir_support_install_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ototamir_support_messages';

    // Tablo henüz yoksa anında ve doğrudan oluştur
    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
        $charset_collate = $wpdb->get_charset_collate();
        if ( empty( $charset_collate ) ) {
            $charset_collate = "DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        }

        $sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `user_id` bigint(20) NOT NULL,
            `sender_type` varchar(10) NOT NULL DEFAULT 'user',
            `admin_id` bigint(20) NOT NULL DEFAULT 0,
            `message` longtext NOT NULL,
            `listing_id` bigint(20) NOT NULL DEFAULT 0,
            `is_read` tinyint(1) NOT NULL DEFAULT 0,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            KEY `listing_id` (`listing_id`),
            KEY `is_read` (`is_read`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB $charset_collate;";

        $wpdb->query( $sql );
        update_option( 'ototamir_support_db_version', OTOTAMIR_SUPPORT_DB_VER );
    }
}
add_action( 'init', 'ototamir_support_install_table' );
add_action( 'admin_init', 'ototamir_support_install_table' );


// ============================================================
// 2. YARDIMCI VERİTABANI & SAYAÇ FONKSİYONLARI
// ============================================================

/**
 * Yönetici için okunmamış kullanıcı mesajlarının sayısını döner.
 */
function ototamir_count_unread_support_for_admin() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ototamir_support_messages';
    
    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
        return 0;
    }

    $count = $wpdb->get_var(
        "SELECT COUNT(id) FROM $table_name WHERE sender_type = 'user' AND is_read = 0"
    );
    return intval( $count );
}

/**
 * Belirli bir kullanıcı için okunmamış yönetici yanıtı sayısını döner.
 */
function ototamir_count_unread_support_for_user( $user_id ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ototamir_support_messages';
    
    if ( ! $user_id || $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
        return 0;
    }

    $count = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(id) FROM $table_name WHERE user_id = %d AND sender_type = 'admin' AND is_read = 0",
        $user_id
    ) );
    return intval( $count );
}

/**
 * Bir kullanıcının tüm mesaj geçmişini (thread) çeker.
 */
function ototamir_get_support_thread( $user_id, $limit = 100 ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ototamir_support_messages';

    if ( ! $user_id ) {
        return array();
    }

    ototamir_support_install_table();

    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
        return array();
    }

    $results = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM `$table_name` WHERE `user_id` = %d ORDER BY `created_at` ASC LIMIT %d",
        $user_id,
        $limit
    ) );

    return $results ? $results : array();
}

/**
 * Yönetici paneli için tüm kullanıcı konuşmalarını gruplanmış ve son mesaja göre sıralanmış döner.
 */
function ototamir_get_support_conversations() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ototamir_support_messages';

    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
        return array();
    }

    $query = "
        SELECT 
            m.user_id,
            m.message AS last_message,
            m.created_at AS last_time,
            m.sender_type AS last_sender,
            m.listing_id,
            (SELECT COUNT(sub.id) FROM $table_name sub WHERE sub.user_id = m.user_id AND sub.sender_type = 'user' AND sub.is_read = 0) AS unread_count
        FROM $table_name m
        INNER JOIN (
            SELECT user_id, MAX(id) as max_id
            FROM $table_name
            GROUP BY user_id
        ) latest ON m.id = latest.max_id
        ORDER BY unread_count DESC, m.created_at DESC
    ";

    return $wpdb->get_results( $query );
}


// ============================================================
// 3. WP-ADMIN MENÜSÜ & BİLDİRİM ROZETİ
// ============================================================
function ototamir_register_support_admin_menu() {
    $unread = ototamir_count_unread_support_for_admin();
    $badge = '';
    if ( $unread > 0 ) {
        $badge = ' <span class="update-plugins count-' . $unread . '" style="background:#ea580c; color:#fff; border-radius:10px; padding:2px 7px; font-weight:700; font-size:11px; margin-left:5px;">' . $unread . '</span>';
    }

    add_menu_page(
        __( 'Destek Mesajları', 'ototamir-360' ),
        __( 'Destek Mesajları', 'ototamir-360' ) . $badge,
        'manage_options',
        'ototamir-support',
        'ototamir_render_support_admin_page',
        'dashicons-format-chat',
        26
    );
}
add_action( 'admin_menu', 'ototamir_register_support_admin_menu' );


// ============================================================
// 4. WP-ADMIN DESTEK MESAJLARI ARAYÜZÜ (SPLIT CONVERSATION UI)
// ============================================================
function ototamir_render_support_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'Bu sayfaya erişim yetkiniz bulunmamaktadır.', 'ototamir-360' ) );
    }

    $conversations = ototamir_get_support_conversations();
    $active_user_id = isset( $_GET['user_id'] ) ? intval( $_GET['user_id'] ) : ( ! empty( $conversations ) ? intval( $conversations[0]->user_id ) : 0 );
    $unread_total = ototamir_count_unread_support_for_admin();
    ?>
    <div class="wrap ototamir-support-admin-wrap">
        <div class="ototamir-support-header">
            <div class="header-left">
                <h1><span class="dashicons dashicons-format-chat" style="font-size:32px; width:32px; height:32px; margin-right:8px; color:#ea580c;"></span> <?php esc_html_e( 'Canlı Destek & Kullanıcı Mesajları', 'ototamir-360' ); ?></h1>
                <p class="sub-lead"><?php esc_html_e( 'Üyelerden ve ilan sahiplerinden gelen canlı destek talepleri ve doğrudan iletişim merkezi.', 'ototamir-360' ); ?></p>
            </div>
            <div class="header-stats">
                <div class="stat-pill <?php echo $unread_total > 0 ? 'has-unread' : ''; ?>">
                    <span class="pill-label"><?php esc_html_e( 'Bekleyen / Okunmamış', 'ototamir-360' ); ?></span>
                    <strong class="pill-count" id="admin-total-unread-badge"><?php echo esc_html( $unread_total ); ?></strong>
                </div>
                <div class="stat-pill">
                    <span class="pill-label"><?php esc_html_e( 'Toplam Konuşma', 'ototamir-360' ); ?></span>
                    <strong class="pill-count"><?php echo count( $conversations ); ?></strong>
                </div>
                <button type="button" id="btn-admin-sound-toggle" class="btn-sound-toggle" title="<?php esc_attr_e( 'Bildirim Sesini Aç/Kapat', 'ototamir-360' ); ?>" style="margin-top:auto; margin-bottom:auto; height:42px; width:42px; font-size:18px; border-radius:12px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:white;">
                    <span class="dashicons dashicons-controls-volumeon" style="vertical-align:middle;"></span>
                </button>
            </div>
        </div>

        <div class="ototamir-chat-app-layout">
            <!-- SOL KOLON: KONUŞMA LİSTESİ -->
            <div class="ototamir-inbox-sidebar">
                <div class="inbox-search-bar">
                    <span class="dashicons dashicons-search"></span>
                    <input type="text" id="admin-search-users" placeholder="<?php esc_attr_e( 'Kullanıcı veya ilan ara...', 'ototamir-360' ); ?>" autocomplete="off">
                </div>

                <div class="inbox-conversation-list" id="admin-conversation-list">
                    <?php if ( empty( $conversations ) ) : ?>
                        <div class="inbox-empty-notice">
                            <span class="dashicons dashicons-testimonial"></span>
                            <p><?php esc_html_e( 'Henüz gelen bir destek mesajı bulunmuyor.', 'ototamir-360' ); ?></p>
                        </div>
                    <?php else : ?>
                        <?php foreach ( $conversations as $conv ) : 
                            $u = get_userdata( $conv->user_id );
                            if ( ! $u ) continue;
                            $u_name = $u->display_name ?: $u->user_login;
                            $u_initial = mb_strtoupper( mb_substr( $u_name, 0, 1, 'UTF-8' ), 'UTF-8' );
                            $is_active = ( $conv->user_id == $active_user_id );
                            $time_ago = human_time_diff( strtotime( $conv->last_time ), current_time( 'timestamp' ) ) . ' önce';
                            $listing_title = '';
                            if ( $conv->listing_id > 0 ) {
                                $listing_title = get_the_title( $conv->listing_id );
                            }
                        ?>
                            <div class="conv-card <?php echo $is_active ? 'active' : ''; ?> <?php echo $conv->unread_count > 0 ? 'unread' : ''; ?>" 
                                 data-user-id="<?php echo esc_attr( $conv->user_id ); ?>"
                                 data-user-name="<?php echo esc_attr( strtolower( $u_name . ' ' . $u->user_email . ' ' . $listing_title ) ); ?>">
                                <div class="conv-avatar">
                                    <?php echo esc_html( $u_initial ); ?>
                                    <?php if ( $conv->unread_count > 0 ) : ?>
                                        <span class="conv-unread-dot"></span>
                                    <?php endif; ?>
                                </div>
                                <div class="conv-meta">
                                    <div class="conv-top-row">
                                        <strong class="conv-user-name"><?php echo esc_html( $u_name ); ?></strong>
                                        <span class="conv-time"><?php echo esc_html( $time_ago ); ?></span>
                                    </div>
                                    <div class="conv-snippet">
                                        <?php if ( $conv->last_sender === 'admin' ) : ?>
                                            <span class="prefix-you"><?php esc_html_e( 'Siz: ', 'ototamir-360' ); ?></span>
                                        <?php endif; ?>
                                        <?php echo esc_html( wp_trim_words( $conv->last_message, 8, '...' ) ); ?>
                                    </div>
                                    <?php if ( ! empty( $listing_title ) ) : ?>
                                        <div class="conv-listing-tag">
                                            <span class="dashicons dashicons-admin-tools"></span> <?php echo esc_html( wp_trim_words( $listing_title, 4 ) ); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php if ( $conv->unread_count > 0 ) : ?>
                                    <span class="conv-unread-count"><?php echo intval( $conv->unread_count ); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- SAĞ KOLON: AKTİF MESAJLAŞMA PENCERESİ -->
            <div class="ototamir-chat-window" id="admin-chat-window">
                <?php if ( $active_user_id > 0 ) : 
                    $target_user = get_userdata( $active_user_id );
                    $user_mechs_count = count_user_posts( $active_user_id, 'mechanic' );
                ?>
                    <!-- SOHBET BAŞLIĞI -->
                    <div class="chat-header" id="chat-header">
                        <div class="chat-header-user">
                            <div class="chat-header-avatar">
                                <?php echo esc_html( mb_strtoupper( mb_substr( $target_user->display_name ?: $target_user->user_login, 0, 1, 'UTF-8' ), 'UTF-8' ) ); ?>
                            </div>
                            <div class="chat-header-info">
                                <h3><?php echo esc_html( $target_user->display_name ); ?></h3>
                                <div class="chat-header-sub">
                                    <span><span class="dashicons dashicons-email"></span> <?php echo esc_html( $target_user->user_email ); ?></span>
                                    <span>•</span>
                                    <span><span class="dashicons dashicons-admin-users"></span> <?php echo esc_html( $user_mechs_count ); ?> İlan Sahibi</span>
                                    <span>•</span>
                                    <a href="<?php echo esc_url( get_edit_user_link( $active_user_id ) ); ?>" target="_blank" class="user-profile-link"><?php esc_html_e( 'Kullanıcı Profilini Aç', 'ototamir-360' ); ?></a>
                                </div>
                            </div>
                        </div>
                        <div class="chat-header-actions">
                            <button type="button" class="btn-refresh-thread" id="btn-admin-refresh" title="<?php esc_attr_e( 'Sohbeti Yenile', 'ototamir-360' ); ?>">
                                <span class="dashicons dashicons-update"></span>
                            </button>
                            <button type="button" class="btn-delete-thread" id="btn-admin-delete-thread" title="<?php esc_attr_e( 'Bu Konuşmayı Temizle / Sil', 'ototamir-360' ); ?>" data-user-id="<?php echo esc_attr( $active_user_id ); ?>">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </div>

                    <!-- İLAN BİLGİ KARTI (İLANLA İLİŞKİLİYSE) -->
                    <div id="chat-listing-notice-box" style="display:none;"></div>

                    <!-- MESAJ AKIŞI GÖVDESİ -->
                    <div class="chat-messages-body" id="admin-chat-body">
                        <div class="chat-loading-placeholder">
                            <span class="dashicons dashicons-update spin"></span> <?php esc_html_e( 'Mesajlar yükleniyor...', 'ototamir-360' ); ?>
                        </div>
                    </div>

                    <!-- HIZLI HAZIR CEVAP BUTONLARI -->
                    <div class="chat-quick-replies">
                        <span class="quick-label"><span class="dashicons dashicons-lightbulb"></span> <?php esc_html_e( 'Hızlı Cevaplar:', 'ototamir-360' ); ?></span>
                        <button type="button" class="btn-canned" data-msg="<?php esc_attr_e( 'Merhaba, destek talebinizi aldık. Konuyu inceliyoruz, en kısa sürede size bilgi vereceğiz.', 'ototamir-360' ); ?>">👋 İncelemede</button>
                        <button type="button" class="btn-canned" data-msg="<?php esc_attr_e( 'Merhaba, ilanınız editörlerimiz tarafından kontrol edildi ve başarıyla onaylanarak yayına alındı. İyi çalışmalar dileriz.', 'ototamir-360' ); ?>">✅ İlan Onaylandı</button>
                        <button type="button" class="btn-canned" data-msg="<?php esc_attr_e( 'Merhaba, ilan bilgilerinizde eksik alanlar tespit edildi. Lütfen telefon veya konum alanlarınızı profilinizden güncelleyiniz.', 'ototamir-360' ); ?>">⚠️ Bilgi Eksik</button>
                        <button type="button" class="btn-canned" data-msg="<?php esc_attr_e( 'Talebiniz başarıyla sonuçlandırılmıştır. Ototamir360 ailesi olarak bol kazançlı günler dileriz.', 'ototamir-360' ); ?>">🎉 Çözümlendi</button>
                    </div>

                    <!-- MESAJ YAZMA VE GÖNDERME PANELİ -->
                    <div class="chat-composer">
                        <textarea id="admin-reply-input" placeholder="<?php esc_attr_e( 'Kullanıcıya yanıtınızı yazın... (Enter ile gönder, Shift+Enter yeni satır)', 'ototamir-360' ); ?>" rows="2"></textarea>
                        <div class="composer-footer">
                            <label class="email-notify-check">
                                <input type="checkbox" id="admin-send-email-notify" checked="checked">
                                <span><?php esc_html_e( 'Kullanıcıya e-posta bildirimi gönder', 'ototamir-360' ); ?></span>
                            </label>
                            <button type="button" id="btn-admin-send-reply" class="btn-admin-send" data-user-id="<?php echo esc_attr( $active_user_id ); ?>">
                                <span class="dashicons dashicons-arrow-right-alt"></span> <?php esc_html_e( 'Yanıtı Gönder', 'ototamir-360' ); ?>
                            </button>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="chat-none-selected">
                        <span class="dashicons dashicons-format-chat" style="font-size:64px; width:64px; height:64px; color:#cbd5e1;"></span>
                        <h3><?php esc_html_e( 'Mesajlaşma Seçilmedi', 'ototamir-360' ); ?></h3>
                        <p><?php esc_html_e( 'Mesaj geçmişini görüntülemek ve yanıtlamak için sol listeden bir kullanıcı seçin.', 'ototamir-360' ); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ADMIN PANEL CSS STİLLERİ -->
    <style>
    .ototamir-support-admin-wrap {
        margin: 20px 20px 0 0;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    }
    .ototamir-support-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #0f172a;
        color: white;
        padding: 24px 30px;
        border-radius: 16px;
        margin-bottom: 24px;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.25);
    }
    .ototamir-support-header h1 {
        color: white;
        margin: 0 0 6px 0;
        font-size: 24px;
        font-weight: 800;
        display: flex;
        align-items: center;
    }
    .ototamir-support-header .sub-lead {
        margin: 0;
        color: #94a3b8;
        font-size: 14px;
    }
    .header-stats {
        display: flex;
        gap: 16px;
    }
    .stat-pill {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 12px;
        padding: 10px 18px;
        text-align: right;
    }
    .stat-pill.has-unread {
        background: rgba(234, 88, 12, 0.15);
        border-color: #ea580c;
    }
    .stat-pill.has-unread .pill-count {
        color: #ea580c;
    }
    .pill-label {
        display: block;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #94a3b8;
        margin-bottom: 2px;
    }
    .pill-count {
        font-size: 22px;
        font-weight: 900;
        color: white;
        line-height: 1;
    }

    /* CHAT APP DÜZENİ */
    .ototamir-chat-app-layout {
        display: grid;
        grid-template-columns: 320px 1fr;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        height: calc(100vh - 200px);
        min-height: 520px;
        box-sizing: border-box;
    }

    /* SOL INBOX */
    .ototamir-inbox-sidebar {
        border-right: 1px solid #e2e8f0;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 0;
        overflow: hidden;
    }
    .inbox-search-bar {
        padding: 16px;
        border-bottom: 1px solid #e2e8f0;
        position: relative;
        background: white;
    }
    .inbox-search-bar .dashicons-search {
        position: absolute;
        left: 26px;
        top: 24px;
        color: #94a3b8;
        font-size: 18px;
    }
    .inbox-search-bar input {
        width: 100%;
        padding: 8px 12px 8px 34px !important;
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 8px !important;
        font-size: 13.5px !important;
        background: #f8fafc !important;
        transition: all 0.2s;
    }
    .inbox-search-bar input:focus {
        background: white !important;
        border-color: #ea580c !important;
        box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.2) !important;
    }
    .inbox-conversation-list {
        flex: 1 1 0%;
        min-height: 0;
        overflow-y: auto;
    }
    .inbox-empty-notice {
        padding: 40px 20px;
        text-align: center;
        color: #94a3b8;
    }
    .inbox-empty-notice .dashicons {
        font-size: 42px;
        width: 42px;
        height: 42px;
        margin-bottom: 10px;
        color: #cbd5e1;
    }
    .conv-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        transition: all 0.15s ease;
        position: relative;
        background: white;
    }
    .conv-card:hover {
        background: #f1f5f9;
    }
    .conv-card.active {
        background: #eff6ff;
        border-left: 4px solid #ea580c;
    }
    .conv-card.unread {
        background: #fff7ed;
    }
    .conv-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 17px;
        flex-shrink: 0;
        position: relative;
    }
    .conv-card.unread .conv-avatar {
        background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
    }
    .conv-unread-dot {
        position: absolute;
        top: 0;
        right: 0;
        width: 12px;
        height: 12px;
        background: #ea580c;
        border: 2px solid white;
        border-radius: 50%;
    }
    .conv-meta {
        flex: 1;
        min-width: 0;
    }
    .conv-top-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin-bottom: 4px;
    }
    .conv-user-name {
        font-size: 14px;
        color: #0f172a;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 150px;
    }
    .conv-time {
        font-size: 11px;
        color: #94a3b8;
    }
    .conv-snippet {
        font-size: 12.5px;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .conv-snippet .prefix-you {
        color: #0284c7;
        font-weight: 600;
    }
    .conv-listing-tag {
        font-size: 11px;
        color: #ea580c;
        background: #fff7ed;
        border: 1px solid #ffedd5;
        padding: 2px 6px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-top: 4px;
    }
    .conv-listing-tag .dashicons {
        font-size: 13px;
        width: 13px;
        height: 13px;
    }
    .conv-unread-count {
        background: #ea580c;
        color: white;
        font-size: 11px;
        font-weight: 800;
        padding: 2px 7px;
        border-radius: 10px;
        flex-shrink: 0;
    }

    /* SAĞ CHAT PANELİ */
    .ototamir-chat-window {
        display: flex;
        flex-direction: column;
        background: white;
        position: relative;
        height: 100%;
        max-height: 100%;
        min-height: 0;
        overflow: hidden;
    }
    .chat-header {
        flex-shrink: 0;
        padding: 14px 20px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #ffffff;
        z-index: 2;
    }
    .chat-header-user {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .chat-header-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #0f172a;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 17px;
    }
    .chat-header-info h3 {
        margin: 0 0 4px 0;
        font-size: 16px;
        font-weight: 800;
        color: #0f172a;
    }
    .chat-header-sub {
        font-size: 12.5px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .chat-header-sub .dashicons {
        font-size: 14px;
        width: 14px;
        height: 14px;
        vertical-align: middle;
    }
    .user-profile-link {
        color: #ea580c;
        text-decoration: none;
        font-weight: 600;
    }
    .user-profile-link:hover {
        text-decoration: underline;
    }
    .chat-header-actions {
        display: flex;
        gap: 8px;
    }
    .btn-refresh-thread, .btn-delete-thread {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #475569;
        transition: all 0.2s;
    }
    .btn-refresh-thread:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .btn-delete-thread:hover {
        background: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
    }

    /* İLAN BİLGİ KARTI BANNARI */
    .chat-listing-banner {
        flex-shrink: 0;
        background: #fff7ed;
        border-bottom: 1px solid #fed7aa;
        padding: 8px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 13px;
        color: #9a3412;
    }
    .chat-listing-banner a {
        color: #ea580c;
        font-weight: 700;
        text-decoration: none;
    }
    .chat-listing-banner a:hover {
        text-decoration: underline;
    }

    /* MESAJLAR ALANI */
    .chat-messages-body {
        flex: 1 1 0%;
        min-height: 0;
        overflow-y: auto;
        padding: 20px;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .chat-loading-placeholder {
        margin: auto;
        color: #94a3b8;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .spin {
        animation: chat-spin 1s linear infinite;
    }
    @keyframes chat-spin { 100% { transform: rotate(360deg); } }

    /* BALONCUKLAR */
    .chat-bubble-wrap {
        display: flex;
        flex-direction: column;
        max-width: 75%;
    }
    .chat-bubble-wrap.from-user {
        align-self: flex-start;
    }
    .chat-bubble-wrap.from-admin {
        align-self: flex-end;
    }
    .chat-bubble-sender {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .chat-bubble-wrap.from-admin .chat-bubble-sender {
        justify-content: flex-end;
        color: #ea580c;
    }
    .chat-bubble {
        padding: 14px 18px;
        border-radius: 16px;
        font-size: 14px;
        line-height: 1.55;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        word-break: break-word;
        white-space: pre-wrap;
    }
    .chat-bubble-wrap.from-user .chat-bubble {
        background: white;
        color: #0f172a;
        border: 1px solid #e2e8f0;
        border-top-left-radius: 4px;
    }
    .chat-bubble-wrap.from-admin .chat-bubble {
        background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
        color: white;
        border-top-right-radius: 4px;
    }
    .chat-bubble-time {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 4px;
    }
    .chat-bubble-wrap.from-admin .chat-bubble-time {
        text-align: right;
    }

    /* HIZLI CEVAPLAR */
    .chat-quick-replies {
        flex-shrink: 0;
        padding: 8px 20px;
        background: #f1f5f9;
        border-top: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 8px;
        overflow-x: auto;
        white-space: nowrap;
        z-index: 2;
    }
    .quick-label {
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 4px;
        flex-shrink: 0;
    }
    .btn-canned {
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
    }
    .btn-canned:hover {
        background: #ea580c;
        color: white;
        border-color: #ea580c;
    }

    /* COMPOSER */
    .chat-composer {
        flex-shrink: 0;
        padding: 12px 20px;
        border-top: 1px solid #e2e8f0;
        background: white;
        z-index: 2;
    }
    .chat-composer textarea {
        width: 100%;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 12px !important;
        padding: 10px 14px !important;
        font-size: 14px !important;
        resize: none;
        height: 64px;
        min-height: 56px;
        box-sizing: border-box;
        font-family: inherit;
        transition: all 0.2s;
    }
    .chat-composer textarea:focus {
        border-color: #ea580c !important;
        box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.15) !important;
        outline: none !important;
    }
    .composer-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 12px;
    }
    .email-notify-check {
        font-size: 13px;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
    }
    .btn-admin-send {
        background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
        color: white;
        border: none;
        border-radius: 10px;
        padding: 10px 22px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(234, 88, 12, 0.3);
        transition: all 0.2s;
    }
    .btn-admin-send:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(234, 88, 12, 0.4);
    }
    .btn-admin-send:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .chat-none-selected {
        margin: auto;
        text-align: center;
        padding: 40px;
        color: #64748b;
    }
    </style>

    <!-- ADMIN PANEL JAVASCRIPT LOGIC -->
    <script>
    jQuery(document).ready(function($) {
        var activeUserId = <?php echo intval( $active_user_id ); ?>;
        var ajaxUrl = "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>";
        var adminNonce = "<?php echo wp_create_nonce( 'ototamir_support_admin_nonce' ); ?>";
        var pollTimer = null;

        function loadAdminThread(userId, scrollBottom) {
            if (!userId) return;

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'ototamir_admin_get_thread',
                    user_id: userId,
                    security: adminNonce
                },
                success: function(res) {
                    if (res.success) {
                        renderMessages(res.data.messages);
                        
                        if (res.data.listing) {
                            $('#chat-listing-notice-box').html(
                                '<div class="chat-listing-banner">' +
                                    '<span><span class="dashicons dashicons-admin-tools"></span> Bu talep <strong>' + res.data.listing.title + '</strong> ilanıyla ilgilidir.</span>' +
                                    '<a href="' + res.data.listing.url + '" target="_blank">İlanı Canlı Gör <span class="dashicons dashicons-external"></span></a>' +
                                '</div>'
                            ).show();
                        } else {
                            $('#chat-listing-notice-box').hide();
                        }

                        var $card = $('.conv-card[data-user-id="' + userId + '"]');
                        $card.removeClass('unread').find('.conv-unread-count, .conv-unread-dot').remove();
                        
                        if (res.data.total_unread !== undefined) {
                            $('#admin-total-unread-badge').text(res.data.total_unread);
                        }

                        if (scrollBottom) {
                            var chatBody = document.getElementById('admin-chat-body');
                            if (chatBody) {
                                chatBody.scrollTop = chatBody.scrollHeight;
                            }
                        }
                    }
                }
            });
        }

        function renderMessages(messages) {
            var $body = $('#admin-chat-body');
            $body.empty();

            if (!messages || messages.length === 0) {
                $body.html('<div class="inbox-empty-notice"><p>Bu kullanıcı ile henüz bir mesajlaşma bulunmuyor.</p></div>');
                return;
            }

            messages.forEach(function(m) {
                var isUser = (m.sender_type === 'user');
                var wrapClass = isUser ? 'from-user' : 'from-admin';
                var senderLabel = isUser ? ('<span class="dashicons dashicons-admin-users"></span> ' + (m.user_name || 'Üye')) : '<span class="dashicons dashicons-shield"></span> Yönetici (Siz)';
                
                var bubbleHtml = '<div class="chat-bubble-wrap ' + wrapClass + '">' +
                    '<div class="chat-bubble-sender">' + senderLabel + '</div>' +
                    '<div class="chat-bubble">' + escapeHtml(m.message) + '</div>' +
                    '<div class="chat-bubble-time">' + m.formatted_time + '</div>' +
                '</div>';

                $body.append(bubbleHtml);
            });
        }

        function escapeHtml(text) {
            var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        function sendAdminReply() {
            var msg = $.trim($('#admin-reply-input').val());
            if (!msg || !activeUserId) return;

            var $btn = $('#btn-admin-send-reply');
            var sendEmail = $('#admin-send-email-notify').is(':checked') ? 1 : 0;
            $btn.prop('disabled', true).text('Gönderiliyor...');

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'ototamir_admin_reply_support',
                    user_id: activeUserId,
                    message: msg,
                    send_email: sendEmail,
                    security: adminNonce
                },
                success: function(res) {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-arrow-right-alt"></span> Yanıtı Gönder');
                    if (res.success) {
                        $('#admin-reply-input').val('');
                        loadAdminThread(activeUserId, true);
                    } else {
                        alert(res.data && res.data.message ? res.data.message : 'Mesaj gönderilemedi.');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-arrow-right-alt"></span> Yanıtı Gönder');
                    alert('Sunucuyla bağlantı kurulamadı.');
                }
            });
        }

        $('#btn-admin-send-reply').on('click', function(e) {
            e.preventDefault();
            sendAdminReply();
        });

        $('#admin-reply-input').on('keydown', function(e) {
            if ((e.keyCode === 13 && !e.shiftKey) || ((e.ctrlKey || e.metaKey) && e.keyCode === 13)) {
                e.preventDefault();
                sendAdminReply();
            }
        });

        $('.btn-canned').on('click', function() {
            var cannedText = $(this).attr('data-msg');
            var currentVal = $('#admin-reply-input').val();
            if (currentVal.length > 0) {
                $('#admin-reply-input').val(currentVal + ' ' + cannedText);
            } else {
                $('#admin-reply-input').val(cannedText);
            }
            $('#admin-reply-input').focus();
        });

        $(document).on('click', '.conv-card', function() {
            var userId = $(this).data('user-id');
            if (userId === activeUserId) return;

            activeUserId = userId;
            $('.conv-card').removeClass('active');
            $(this).addClass('active');

            var newUrl = window.location.pathname + '?page=ototamir-support&user_id=' + userId;
            window.history.pushState({ path: newUrl }, '', newUrl);

            loadAdminThread(activeUserId, true);
        });

        $('#admin-search-users').on('input', function() {
            var query = $(this).val().toLowerCase();
            $('.conv-card').each(function() {
                var searchData = $(this).data('user-name') || '';
                if (searchData.indexOf(query) !== -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        $('#btn-admin-refresh').on('click', function() {
            var $btn = $(this);
            $btn.find('.dashicons').addClass('spin');
            loadAdminThread(activeUserId, false);
            setTimeout(function() {
                $btn.find('.dashicons').removeClass('spin');
            }, 800);
        });

        $('#btn-admin-delete-thread').on('click', function() {
            if (!confirm('Bu kullanıcı ile olan tüm mesajlaşma geçmişini silmek istediğinize emin misiniz? Bu işlem geri alınamaz.')) {
                return;
            }

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'ototamir_admin_delete_thread',
                    user_id: activeUserId,
                    security: adminNonce
                },
                success: function(res) {
                    if (res.success) {
                        window.location.href = window.location.pathname + '?page=ototamir-support';
                    }
                }
            });
        });

        // Ses Butonu Durumu & Tıklama
        function updateAdminSoundBtn() {
            var isMuted = localStorage.getItem('ototamir_sound_muted') === '1';
            var $btn = $('#btn-admin-sound-toggle');
            if (isMuted) {
                $btn.addClass('muted').html('<span class="dashicons dashicons-controls-volumeoff" style="vertical-align:middle; color:#f87171;"></span>');
                $btn.attr('title', 'Bildirim Sesi Kapalı (Açmak için tıklayın)');
            } else {
                $btn.removeClass('muted').html('<span class="dashicons dashicons-controls-volumeon" style="vertical-align:middle; color:#4ade80;"></span>');
                $btn.attr('title', 'Bildirim Sesi Açık (Kapatmak için tıklayın)');
            }
        }
        updateAdminSoundBtn();

        $('#btn-admin-sound-toggle').on('click', function() {
            var isMuted = localStorage.getItem('ototamir_sound_muted') === '1';
            if (isMuted) {
                localStorage.setItem('ototamir_sound_muted', '0');
                updateAdminSoundBtn();
                if (window.OtotamirAudio) window.OtotamirAudio.playChime();
            } else {
                localStorage.setItem('ototamir_sound_muted', '1');
                updateAdminSoundBtn();
            }
        });

        // Canlı bildirim geldiğinde çalışacak global kancalar
        window.switchAdminConversation = function(userId) {
            userId = parseInt(userId, 10);
            if (!userId) return;
            activeUserId = userId;
            $('.conv-card').removeClass('active');
            $('.conv-card[data-user-id="' + userId + '"]').addClass('active');
            var newUrl = window.location.pathname + '?page=ototamir-support&user_id=' + userId;
            window.history.pushState({ path: newUrl }, '', newUrl);
            loadAdminThread(activeUserId, true);
        };

        window.onOtotamirAdminLiveMessage = function(data) {
            if (activeUserId === parseInt(data.user_id, 10)) {
                loadAdminThread(activeUserId, true);
            }
            var $card = $('.conv-card[data-user-id="' + data.user_id + '"]');
            if ($card.length) {
                $card.addClass('unread');
                $card.find('.conv-snippet').text(data.message_snippet);
                $card.find('.conv-time').text(data.time);
                if (!$card.find('.conv-unread-dot').length) {
                    $card.find('.conv-avatar').append('<span class="conv-unread-dot"></span>');
                }
                $('#admin-conversation-list').prepend($card);
            } else {
                var initial = (data.user_name || 'U').charAt(0).toUpperCase();
                var newCardHtml = 
                    '<div class="conv-card unread active" data-user-id="' + data.user_id + '" data-user-name="' + escapeHtml(data.user_name.toLowerCase()) + '">' +
                        '<div class="conv-avatar">' + initial + '<span class="conv-unread-dot"></span></div>' +
                        '<div class="conv-meta">' +
                            '<div class="conv-top-row">' +
                                '<strong class="conv-user-name">' + escapeHtml(data.user_name) + '</strong>' +
                                '<span class="conv-time">' + data.time + '</span>' +
                            '</div>' +
                            '<div class="conv-snippet">' + escapeHtml(data.message_snippet) + '</div>' +
                        '</div>' +
                    '</div>';
                $('#admin-conversation-list').prepend(newCardHtml);
            }
        };

        if (activeUserId > 0) {
            loadAdminThread(activeUserId, true);
        }

        pollTimer = setInterval(function() {
            if (activeUserId > 0) {
                loadAdminThread(activeUserId, false);
            }
        }, 12000);
    });
    </script>
    <?php
}


// ============================================================
// 5. AJAX ENDPOINT'LERİ (YÖNETİCİ TARAFI)
// ============================================================

/**
 * Yönetici: Konuşma mesajlarını getir ve okundu işaretle
 */
function ototamir_ajax_admin_get_thread() {
    check_ajax_referer( 'ototamir_support_admin_nonce', 'security' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Yetkisiz işlem.' ) );
    }

    $user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
    if ( ! $user_id ) {
        wp_send_json_error( array( 'message' => 'Geçersiz kullanıcı.' ) );
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'ototamir_support_messages';

    $wpdb->query( $wpdb->prepare(
        "UPDATE $table_name SET is_read = 1 WHERE user_id = %d AND sender_type = 'user' AND is_read = 0",
        $user_id
    ) );

    $raw_messages = ototamir_get_support_thread( $user_id );
    $user = get_userdata( $user_id );
    $user_name = $user ? ($user->display_name ?: $user->user_login) : 'Kullanıcı';

    $formatted = array();
    $attached_listing = null;

    foreach ( $raw_messages as $m ) {
        if ( $m->listing_id > 0 && ! $attached_listing ) {
            $listing_post = get_post( $m->listing_id );
            if ( $listing_post ) {
                $attached_listing = array(
                    'id'    => $m->listing_id,
                    'title' => get_the_title( $m->listing_id ),
                    'url'   => get_permalink( $m->listing_id ),
                );
            }
        }

        $formatted[] = array(
            'id'             => $m->id,
            'sender_type'    => $m->sender_type,
            'user_name'      => $user_name,
            'message'        => stripslashes( $m->message ),
            'is_read'        => $m->is_read,
            'created_at'     => $m->created_at,
            'formatted_time' => date_i18n( 'j M H:i', strtotime( $m->created_at ) ),
        );
    }

    $total_unread = ototamir_count_unread_support_for_admin();

    wp_send_json_success( array(
        'messages'     => $formatted,
        'listing'      => $attached_listing,
        'total_unread' => $total_unread,
    ) );
}
add_action( 'wp_ajax_ototamir_admin_get_thread', 'ototamir_ajax_admin_get_thread' );

/**
 * Yönetici: Kullanıcıya yanıt gönder ve bildirim e-postası yolla
 */
function ototamir_ajax_admin_reply_support() {
    check_ajax_referer( 'ototamir_support_admin_nonce', 'security' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Yetkisiz işlem.' ) );
    }

    $user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
    $message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
    $send_email = ! empty( $_POST['send_email'] );

    if ( ! $user_id || empty( $message ) ) {
        wp_send_json_error( array( 'message' => 'Lütfen bir yanıt mesajı yazın.' ) );
    }

    $target_user = get_userdata( $user_id );
    if ( ! $target_user ) {
        wp_send_json_error( array( 'message' => 'Kullanıcı bulunamadı.' ) );
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'ototamir_support_messages';

    $inserted = $wpdb->insert(
        $table_name,
        array(
            'user_id'     => $user_id,
            'sender_type' => 'admin',
            'admin_id'    => get_current_user_id(),
            'message'     => $message,
            'is_read'     => 0,
            'created_at'  => current_time( 'mysql' ),
        ),
        array( '%d', '%s', '%d', '%s', '%d', '%s' )
    );

    if ( ! $inserted ) {
        wp_send_json_error( array( 'message' => 'Mesaj kaydedilirken bir hata oluştu.' ) );
    }

    if ( $send_email && is_email( $target_user->user_email ) ) {
        $site_name = get_bloginfo( 'name' );
        $profile_url = home_url( '/profil/?tab=destek' );
        $subject = sprintf( '[%s] Yönetici Destek Yanıtı', $site_name );

        $body  = "Merhaba " . esc_html( $target_user->display_name ) . ",\n\n";
        $body .= "Ototamir360 canlı destek üzerinden ilettiğiniz mesajınıza yöneticimiz yanıt verdi:\n\n";
        $body .= "--------------------------------------------------\n";
        $body .= $message . "\n";
        $body .= "--------------------------------------------------\n\n";
        $body .= "Mesajlaşma geçmişini görmek ve yanıt vermek için profil panelinizi ziyaret edebilirsiniz:\n";
        $body .= $profile_url . "\n\n";
        $body .= "İyi günler dileriz,\n" . $site_name . " Yönetim Ekibi";

        $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
        wp_mail( $target_user->user_email, $subject, $body, $headers );
    }

    wp_send_json_success( array( 'message' => 'Yanıtınız başarıyla iletildi.' ) );
}
add_action( 'wp_ajax_ototamir_admin_reply_support', 'ototamir_ajax_admin_reply_support' );

/**
 * Yönetici: Konuşmayı sil
 */
function ototamir_ajax_admin_delete_thread() {
    check_ajax_referer( 'ototamir_support_admin_nonce', 'security' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Yetkisiz işlem.' ) );
    }

    $user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
    if ( ! $user_id ) {
        wp_send_json_error( array( 'message' => 'Geçersiz kullanıcı.' ) );
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'ototamir_support_messages';
    $wpdb->delete( $table_name, array( 'user_id' => $user_id ), array( '%d' ) );

    wp_send_json_success( array( 'message' => 'Konuşma silindi.' ) );
}
add_action( 'wp_ajax_ototamir_admin_delete_thread', 'ototamir_ajax_admin_delete_thread' );


// ============================================================
// 6. AJAX ENDPOINT'LERİ (KULLANICI / PROFİL TARAFI)
// ============================================================

/**
 * Kullanıcı: Destek mesajı gönder
 */
function ototamir_ajax_user_send_support() {
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'Mesaj göndermek için lütfen önce giriş yapın.' ) );
    }

    // Güvenlik doğrulaması (LiteSpeed ve önbellek uyumlu esnek kontrol)
    check_ajax_referer( 'ototamir_support_user_nonce', 'security', false );

    $user_id = get_current_user_id();
    $message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
    $listing_id = isset( $_POST['listing_id'] ) ? intval( $_POST['listing_id'] ) : 0;

    if ( empty( $message ) || mb_strlen( trim( $message ) ) < 2 ) {
        wp_send_json_error( array( 'message' => 'Lütfen geçerli bir mesaj yazın.' ) );
    }

    $last_sent = get_user_meta( $user_id, '_last_support_msg_time', true );
    if ( $last_sent && ( time() - intval( $last_sent ) < 2 ) ) {
        wp_send_json_error( array( 'message' => 'Lütfen biraz bekleyip tekrar deneyin.' ) );
    }
    update_user_meta( $user_id, '_last_support_msg_time', time() );

    // Tablonun kesin olarak var olmasını sağla
    ototamir_support_install_table();

    global $wpdb;
    $table_name = $wpdb->prefix . 'ototamir_support_messages';

    $inserted = $wpdb->insert(
        $table_name,
        array(
            'user_id'     => $user_id,
            'sender_type' => 'user',
            'admin_id'    => 0,
            'message'     => $message,
            'listing_id'  => $listing_id,
            'is_read'     => 0,
            'created_at'  => current_time( 'mysql' ),
        ),
        array( '%d', '%s', '%d', '%s', '%d', '%d', '%s' )
    );

    if ( ! $inserted ) {
        wp_send_json_error( array( 'message' => 'Mesaj iletilemedi, lütfen tekrar deneyin.' ) );
    }

    $admin_email = get_option( 'admin_email' );
    $cur_user = wp_get_current_user();
    $admin_panel_url = admin_url( 'admin.php?page=ototamir-support&user_id=' . $user_id );
    $site_name = get_bloginfo( 'name' );

    $listing_info = '';
    if ( $listing_id > 0 ) {
        $listing_info = "\nİlgili İlan: " . get_the_title( $listing_id ) . " (" . get_permalink( $listing_id ) . ")\n";
    }

    $subject = sprintf( '[%s] Yeni Canlı Destek Mesajı: %s', $site_name, $cur_user->display_name );
    $body  = "Merhaba Site Yöneticisi,\n\n";
    $body .= "Bir üyemiz canlı destek üzerinden yeni bir mesaj gönderdi:\n\n";
    $body .= "Gönderen: " . $cur_user->display_name . " (" . $cur_user->user_email . ")\n";
    $body .= $listing_info;
    $body .= "Mesaj:\n--------------------------------------------------\n";
    $body .= $message . "\n";
    $body .= "--------------------------------------------------\n\n";
    $body .= "Mesaja yanıt vermek ve konuşmayı görmek için tıklayın:\n";
    $body .= $admin_panel_url . "\n";

    $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
    wp_mail( $admin_email, $subject, $body, $headers );

    wp_send_json_success( array(
        'message' => 'Mesajınız yöneticiye başarıyla iletildi.',
    ) );
}
add_action( 'wp_ajax_ototamir_user_send_support', 'ototamir_ajax_user_send_support' );
add_action( 'wp_ajax_nopriv_ototamir_user_send_support', 'ototamir_ajax_user_send_support' );

/**
 * Kullanıcı: Mesaj geçmişini getir ve yöneticinin gönderdiği yanıtları okundu işaretle
 */
function ototamir_ajax_user_get_support() {
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'Lütfen giriş yapın.', 'logged_in' => false ) );
    }

    // Güvenlik doğrulaması (LiteSpeed ve önbellek uyumlu esnek kontrol)
    check_ajax_referer( 'ototamir_support_user_nonce', 'security', false );

    $user_id = get_current_user_id();

    // Tablonun kesin olarak var olmasını sağla
    ototamir_support_install_table();

    global $wpdb;
    $table_name = $wpdb->prefix . 'ototamir_support_messages';

    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
        $wpdb->query( $wpdb->prepare(
            "UPDATE `$table_name` SET `is_read` = 1 WHERE `user_id` = %d AND `sender_type` = 'admin' AND `is_read` = 0",
            $user_id
        ) );
    }

    $raw_messages = ototamir_get_support_thread( $user_id );
    $formatted = array();

    if ( ! empty( $raw_messages ) ) {
        foreach ( $raw_messages as $m ) {
            $formatted[] = array(
                'id'             => $m->id,
                'sender_type'    => $m->sender_type,
                'message'        => stripslashes( $m->message ),
                'created_at'     => $m->created_at,
                'formatted_time' => date_i18n( 'j M H:i', strtotime( $m->created_at ) ),
            );
        }
    }

    wp_send_json_success( array(
        'messages' => $formatted,
        'unread'   => 0,
    ) );
}
add_action( 'wp_ajax_ototamir_user_get_support', 'ototamir_ajax_user_get_support' );
add_action( 'wp_ajax_nopriv_ototamir_user_get_support', 'ototamir_ajax_user_get_support' );


// ============================================================
// 7. CANLI BİLDİRİM SESİ & SOL ALT PUSH TOAST (HER İKİ TARAF İÇİN)
// ============================================================

/**
 * Yönetici Bildirim Kontrolü AJAX Endpoint'i
 */
function ototamir_ajax_admin_check_notifications() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Yetkisiz erişim.' ) );
    }

    check_ajax_referer( 'ototamir_support_admin_nonce', 'security', false );

    global $wpdb;
    $table_name = $wpdb->prefix . 'ototamir_support_messages';
    $last_id = isset( $_POST['last_id'] ) ? intval( $_POST['last_id'] ) : 0;

    $total_unread = ototamir_count_unread_support_for_admin();

    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
        wp_send_json_success( array( 'has_new' => false, 'total_unread' => 0 ) );
    }

    $new_msg = null;
    if ( $last_id > 0 ) {
        $new_msg = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM `$table_name` WHERE `sender_type` = 'user' AND `id` > %d ORDER BY `id` DESC LIMIT 1",
            $last_id
        ) );
    }

    if ( $new_msg ) {
        $user = get_userdata( $new_msg->user_id );
        $user_name = $user ? ( $user->display_name ?: $user->user_login ) : 'Bir Üye';
        $snippet = wp_strip_all_tags( stripslashes( $new_msg->message ) );
        if ( mb_strlen( $snippet ) > 90 ) {
            $snippet = mb_substr( $snippet, 0, 87 ) . '...';
        }

        wp_send_json_success( array(
            'has_new'         => true,
            'id'              => intval( $new_msg->id ),
            'user_id'         => intval( $new_msg->user_id ),
            'user_name'       => $user_name,
            'message_snippet' => $snippet,
            'full_message'    => stripslashes( $new_msg->message ),
            'time'            => date_i18n( 'H:i', strtotime( $new_msg->created_at ) ),
            'admin_url'       => admin_url( 'admin.php?page=ototamir-support&user_id=' . $new_msg->user_id ),
            'total_unread'    => $total_unread,
        ) );
    } else {
        wp_send_json_success( array(
            'has_new'      => false,
            'total_unread' => $total_unread,
        ) );
    }
}
add_action( 'wp_ajax_ototamir_admin_check_notifications', 'ototamir_ajax_admin_check_notifications' );

/**
 * Kullanıcı Bildirim Kontrolü AJAX Endpoint'i
 */
function ototamir_ajax_user_check_notifications() {
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => 'Giriş yapılmamış.' ) );
    }

    check_ajax_referer( 'ototamir_support_user_nonce', 'security', false );

    $user_id = get_current_user_id();
    $last_id = isset( $_POST['last_id'] ) ? intval( $_POST['last_id'] ) : 0;

    global $wpdb;
    $table_name = $wpdb->prefix . 'ototamir_support_messages';

    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
        wp_send_json_success( array( 'has_new' => false ) );
    }

    $new_msg = null;
    if ( $last_id > 0 ) {
        $new_msg = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM `$table_name` WHERE `user_id` = %d AND `sender_type` = 'admin' AND `id` > %d AND `is_read` = 0 ORDER BY `id` DESC LIMIT 1",
            $user_id,
            $last_id
        ) );
    }

    if ( $new_msg ) {
        $snippet = wp_strip_all_tags( stripslashes( $new_msg->message ) );
        if ( mb_strlen( $snippet ) > 90 ) {
            $snippet = mb_substr( $snippet, 0, 87 ) . '...';
        }

        wp_send_json_success( array(
            'has_new'         => true,
            'id'              => intval( $new_msg->id ),
            'message_snippet' => $snippet,
            'time'            => date_i18n( 'H:i', strtotime( $new_msg->created_at ) ),
            'profile_url'     => home_url( '/profil/?tab=destek' ),
        ) );
    } else {
        wp_send_json_success( array( 'has_new' => false ) );
    }
}
add_action( 'wp_ajax_ototamir_user_check_notifications', 'ototamir_ajax_user_check_notifications' );
add_action( 'wp_ajax_nopriv_ototamir_user_check_notifications', 'ototamir_ajax_user_check_notifications' );

/**
 * WP-Admin Paneli için Global Bildirim Sesi ve Sol Alt Push Toast
 */
function ototamir_support_admin_global_notifications() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'ototamir_support_messages';
    $max_user_msg_id = 0;
    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
        $max_user_msg_id = intval( $wpdb->get_var( "SELECT MAX(id) FROM `$table_name` WHERE `sender_type` = 'user'" ) );
    }

    $admin_nonce = wp_create_nonce( 'ototamir_support_admin_nonce' );
    $ajax_url = admin_url( 'admin-ajax.php' );
    ?>
    <!-- OTOTAMIR360 ADMIN CANLI DESTEK BILDIRIM SESI VE PUSH TOAST -->
    <style>
    .ototamir-toast-container {
        position: fixed;
        bottom: 24px;
        left: 24px;
        z-index: 9999999;
        display: flex;
        flex-direction: column;
        gap: 12px;
        pointer-events: none;
        max-width: 380px;
        width: calc(100vw - 48px);
    }
    .ototamir-push-toast {
        pointer-events: auto;
        background: #0f172a;
        color: #f8fafc;
        border-radius: 14px;
        border-left: 4px solid #ea580c;
        box-shadow: 0 20px 35px -5px rgba(15, 23, 42, 0.45), 0 8px 16px -6px rgba(15, 23, 42, 0.3);
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 16px;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        animation: ototamirSlideInLeft 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        transition: transform 0.2s, box-shadow 0.2s, opacity 0.3s;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    .ototamir-push-toast:hover {
        transform: translateY(-2px);
        box-shadow: 0 24px 40px -5px rgba(15, 23, 42, 0.55);
    }
    .ototamir-push-toast.toast-hiding {
        animation: ototamirSlideOutLeft 0.3s cubic-bezier(0.7, 0, 0.84, 0) forwards;
    }
    @keyframes ototamirSlideInLeft {
        0% { transform: translateX(-115%); opacity: 0; }
        100% { transform: translateX(0); opacity: 1; }
    }
    @keyframes ototamirSlideOutLeft {
        0% { transform: translateX(0); opacity: 1; }
        100% { transform: translateX(-115%); opacity: 0; }
    }
    .ototamir-toast-icon-wrap {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: rgba(234, 88, 12, 0.18);
        color: #ea580c;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
        position: relative;
    }
    .ototamir-toast-pulse {
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 10px;
        border: 2px solid #ea580c;
        animation: ototamirToastPulse 2s infinite;
    }
    @keyframes ototamirToastPulse {
        0% { transform: scale(1); opacity: 0.8; }
        70% { transform: scale(1.3); opacity: 0; }
        100% { transform: scale(1.3); opacity: 0; }
    }
    .ototamir-toast-content {
        flex: 1;
        min-width: 0;
    }
    .ototamir-toast-title {
        font-size: 13.5px;
        font-weight: 700;
        color: #ffffff;
        margin: 0 0 3px 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .ototamir-toast-time {
        font-size: 11px;
        color: #94a3b8;
        font-weight: 400;
        margin-left: 8px;
    }
    .ototamir-toast-msg {
        font-size: 12.5px;
        color: #cbd5e1;
        line-height: 1.45;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        word-break: break-word;
    }
    .ototamir-toast-action {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11.5px;
        font-weight: 700;
        color: #fb923c;
        margin-top: 6px;
        text-decoration: none;
    }
    .ototamir-toast-close {
        background: none;
        border: none;
        color: #94a3b8;
        font-size: 20px;
        line-height: 1;
        padding: 0 4px;
        cursor: pointer;
        transition: color 0.15s;
    }
    .ototamir-toast-close:hover {
        color: #ffffff;
    }
    </style>

    <script>
    (function($) {
        if (!$) return;

        // Ses Motoru (Web Audio API Synthesizer)
        window.OtotamirAudio = {
            playChime: function() {
                try {
                    if (localStorage.getItem('ototamir_sound_muted') === '1') return;
                    var AudioCtx = window.AudioContext || window.webkitAudioContext;
                    if (!AudioCtx) return;
                    var ctx = window._ototamirAudioCtx || new AudioCtx();
                    if (ctx.state === 'suspended') {
                        ctx.resume().catch(function() {});
                    }
                    var now = ctx.currentTime;

                    var osc1 = ctx.createOscillator();
                    var gain1 = ctx.createGain();
                    osc1.type = 'sine';
                    osc1.frequency.setValueAtTime(587.33, now); // D5
                    gain1.gain.setValueAtTime(0.16, now);
                    gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.32);
                    osc1.connect(gain1);
                    gain1.connect(ctx.destination);
                    osc1.start(now);
                    osc1.stop(now + 0.32);

                    var osc2 = ctx.createOscillator();
                    var gain2 = ctx.createGain();
                    osc2.type = 'sine';
                    osc2.frequency.setValueAtTime(880, now + 0.11); // A5
                    gain2.gain.setValueAtTime(0.20, now + 0.11);
                    gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.50);
                    osc2.connect(gain2);
                    gain2.connect(ctx.destination);
                    osc2.start(now + 0.11);
                    osc2.stop(now + 0.50);
                } catch(e) {}
            }
        };

        // Push Toast Gösterici
        window.OtotamirToast = {
            show: function(opts) {
                window.OtotamirAudio.playChime();

                var container = document.getElementById('ototamir-toast-container');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'ototamir-toast-container';
                    container.className = 'ototamir-toast-container';
                    document.body.appendChild(container);
                }

                var toast = document.createElement('div');
                toast.className = 'ototamir-push-toast';
                toast.innerHTML = 
                    '<div class="ototamir-toast-icon-wrap">' +
                        (opts.iconHtml || '<span class="dashicons dashicons-format-chat"></span>') +
                        '<span class="ototamir-toast-pulse"></span>' +
                    '</div>' +
                    '<div class="ototamir-toast-content">' +
                        '<div class="ototamir-toast-title">' +
                            '<span>' + (opts.title || 'Yeni Destek Mesajı') + '</span>' +
                            '<span class="ototamir-toast-time">' + (opts.timeText || 'Şimdi') + '</span>' +
                        '</div>' +
                        '<p class="ototamir-toast-msg">' + (opts.message || '') + '</p>' +
                        (opts.actionText ? '<span class="ototamir-toast-action">' + opts.actionText + ' &rarr;</span>' : '') +
                    '</div>' +
                    '<button type="button" class="ototamir-toast-close" title="Kapat">&times;</button>';

                var closeTimeout;
                function dismiss() {
                    toast.classList.add('toast-hiding');
                    setTimeout(function() {
                        if (toast.parentNode) toast.parentNode.removeChild(toast);
                    }, 300);
                }

                toast.querySelector('.ototamir-toast-close').addEventListener('click', function(e) {
                    e.stopPropagation();
                    clearTimeout(closeTimeout);
                    dismiss();
                });

                toast.addEventListener('click', function(e) {
                    if (typeof opts.onClick === 'function') {
                        opts.onClick(e);
                    }
                    dismiss();
                });

                toast.addEventListener('mouseenter', function() {
                    clearTimeout(closeTimeout);
                });

                toast.addEventListener('mouseleave', function() {
                    closeTimeout = setTimeout(dismiss, 4000);
                });

                closeTimeout = setTimeout(dismiss, 8000);
                container.appendChild(toast);
            }
        };

        // Kullanıcı tıkladığında AudioContext kilitlerini aç
        document.addEventListener('click', function unlockAudio() {
            try {
                var AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (AudioCtx && !window._ototamirAudioCtx) {
                    window._ototamirAudioCtx = new AudioCtx();
                    if (window._ototamirAudioCtx.state === 'suspended') {
                        window._ototamirAudioCtx.resume();
                    }
                }
            } catch(e) {}
            document.removeEventListener('click', unlockAudio);
        }, { once: true, passive: true });

        // Yönetici Polling Döngüsü
        var adminLastUserMsgId = <?php echo intval( $max_user_msg_id ); ?>;
        var adminAjaxUrl = "<?php echo esc_url( $ajax_url ); ?>";
        var adminNonce = "<?php echo esc_js( $admin_nonce ); ?>";

        function checkAdminNotifications() {
            $.ajax({
                url: adminAjaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'ototamir_admin_check_notifications',
                    last_id: adminLastUserMsgId,
                    security: adminNonce
                },
                success: function(res) {
                    if (res.success && res.data) {
                        if (res.data.has_new && res.data.id > adminLastUserMsgId) {
                            adminLastUserMsgId = res.data.id;

                            // Sol alt push bildirimi göster ve ses çal
                            window.OtotamirToast.show({
                                iconHtml: '<span class="dashicons dashicons-admin-users" style="font-size:22px; width:22px; height:22px;"></span>',
                                title: res.data.user_name,
                                message: res.data.message_snippet,
                                timeText: res.data.time,
                                actionText: 'Yanıtla / Konuşmayı Gör',
                                onClick: function() {
                                    if (typeof window.switchAdminConversation === 'function') {
                                        window.switchAdminConversation(res.data.user_id);
                                    } else {
                                        window.location.href = res.data.admin_url;
                                    }
                                }
                            });

                            // Eğer yönetici zaten 'ototamir-support' sayfasındaysa canlı arayüzü güncelle
                            if (typeof window.onOtotamirAdminLiveMessage === 'function') {
                                window.onOtotamirAdminLiveMessage(res.data);
                            }
                        }

                        // Rozetleri güncelle
                        if (res.data.total_unread !== undefined) {
                            $('#admin-total-unread-badge').text(res.data.total_unread);
                            var $menuItem = $('#toplevel_page_ototamir-support a .wp-menu-name');
                            var $badge = $('#toplevel_page_ototamir-support .update-plugins');
                            if (res.data.total_unread > 0) {
                                if ($badge.length) {
                                    $badge.text(res.data.total_unread);
                                } else if ($menuItem.length) {
                                    $menuItem.append(' <span class="update-plugins count-' + res.data.total_unread + '" style="background:#ea580c; color:#fff; border-radius:10px; padding:2px 7px; font-weight:700; font-size:11px; margin-left:5px;">' + res.data.total_unread + '</span>');
                                }
                            } else {
                                $badge.remove();
                            }
                        }
                    }
                }
            });
        }

        setInterval(checkAdminNotifications, 12000);
    })(jQuery);
    </script>
    <?php
}
add_action( 'admin_footer', 'ototamir_support_admin_global_notifications' );

/**
 * Site Genelinde Giriş Yapmış Kullanıcılar için Bildirim Sesi ve Sol Alt Push Toast
 */
function ototamir_support_user_global_notifications() {
    if ( ! is_user_logged_in() ) {
        return;
    }

    $user_id = get_current_user_id();
    global $wpdb;
    $table_name = $wpdb->prefix . 'ototamir_support_messages';
    $max_admin_msg_id = 0;
    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
        $max_admin_msg_id = intval( $wpdb->get_var( $wpdb->prepare(
            "SELECT MAX(id) FROM `$table_name` WHERE `user_id` = %d AND `sender_type` = 'admin'",
            $user_id
        ) ) );
    }

    $user_nonce = wp_create_nonce( 'ototamir_support_user_nonce' );
    $ajax_url = admin_url( 'admin-ajax.php' );
    ?>
    <script>
    (function() {
        // Ses Motoru (Web Audio API Synthesizer)
        window.OtotamirAudio = window.OtotamirAudio || {
            playChime: function() {
                try {
                    if (localStorage.getItem('ototamir_sound_muted') === '1') return;
                    var AudioCtx = window.AudioContext || window.webkitAudioContext;
                    if (!AudioCtx) return;
                    var ctx = window._ototamirAudioCtx || new AudioCtx();
                    if (ctx.state === 'suspended') {
                        ctx.resume().catch(function() {});
                    }
                    var now = ctx.currentTime;

                    var osc1 = ctx.createOscillator();
                    var gain1 = ctx.createGain();
                    osc1.type = 'sine';
                    osc1.frequency.setValueAtTime(587.33, now); // D5
                    gain1.gain.setValueAtTime(0.16, now);
                    gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.32);
                    osc1.connect(gain1);
                    gain1.connect(ctx.destination);
                    osc1.start(now);
                    osc1.stop(now + 0.32);

                    var osc2 = ctx.createOscillator();
                    var gain2 = ctx.createGain();
                    osc2.type = 'sine';
                    osc2.frequency.setValueAtTime(880, now + 0.11); // A5
                    gain2.gain.setValueAtTime(0.20, now + 0.11);
                    gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.50);
                    osc2.connect(gain2);
                    gain2.connect(ctx.destination);
                    osc2.start(now + 0.11);
                    osc2.stop(now + 0.50);
                } catch(e) {}
            }
        };

        // Push Toast Gösterici
        window.OtotamirToast = window.OtotamirToast || {
            show: function(opts) {
                window.OtotamirAudio.playChime();

                var container = document.getElementById('ototamir-toast-container');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'ototamir-toast-container';
                    container.className = 'ototamir-toast-container';
                    document.body.appendChild(container);
                }

                var toast = document.createElement('div');
                toast.className = 'ototamir-push-toast';
                toast.innerHTML = 
                    '<div class="ototamir-toast-icon-wrap">' +
                        (opts.iconHtml || '<i class="fa-solid fa-headset"></i>') +
                        '<span class="ototamir-toast-pulse"></span>' +
                    '</div>' +
                    '<div class="ototamir-toast-content">' +
                        '<div class="ototamir-toast-title">' +
                            '<span>' + (opts.title || 'Canlı Destek Yanıtı') + '</span>' +
                            '<span class="ototamir-toast-time">' + (opts.timeText || 'Şimdi') + '</span>' +
                        '</div>' +
                        '<p class="ototamir-toast-msg">' + (opts.message || '') + '</p>' +
                        (opts.actionText ? '<span class="ototamir-toast-action">' + opts.actionText + ' &rarr;</span>' : '') +
                    '</div>' +
                    '<button type="button" class="ototamir-toast-close" title="Kapat">&times;</button>';

                var closeTimeout;
                function dismiss() {
                    toast.classList.add('toast-hiding');
                    setTimeout(function() {
                        if (toast.parentNode) toast.parentNode.removeChild(toast);
                    }, 300);
                }

                toast.querySelector('.ototamir-toast-close').addEventListener('click', function(e) {
                    e.stopPropagation();
                    clearTimeout(closeTimeout);
                    dismiss();
                });

                toast.addEventListener('click', function(e) {
                    if (typeof opts.onClick === 'function') {
                        opts.onClick(e);
                    }
                    dismiss();
                });

                toast.addEventListener('mouseenter', function() {
                    clearTimeout(closeTimeout);
                });

                toast.addEventListener('mouseleave', function() {
                    closeTimeout = setTimeout(dismiss, 4000);
                });

                closeTimeout = setTimeout(dismiss, 8000);
                container.appendChild(toast);
            }
        };

        // Kullanıcı tıkladığında AudioContext kilitlerini aç
        document.addEventListener('click', function unlockAudio() {
            try {
                var AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (AudioCtx && !window._ototamirAudioCtx) {
                    window._ototamirAudioCtx = new AudioCtx();
                    if (window._ototamirAudioCtx.state === 'suspended') {
                        window._ototamirAudioCtx.resume();
                    }
                }
            } catch(e) {}
            document.removeEventListener('click', unlockAudio);
        }, { once: true, passive: true });

        // Eğer kullanıcı şu an profil sayfasında değilse arka plan bildirim kontrolünü başlat
        if (!window.ototamirProfileChatActive) {
            var userLastAdminMsgId = <?php echo intval( $max_admin_msg_id ); ?>;
            var userAjaxUrl = "<?php echo esc_url( $ajax_url ); ?>";
            var userNonce = "<?php echo esc_js( $user_nonce ); ?>";

            function checkUserNotifications() {
                if (window.ototamirProfileChatActive) return;

                var formData = new FormData();
                formData.append('action', 'ototamir_user_check_notifications');
                formData.append('last_id', userLastAdminMsgId);
                formData.append('security', userNonce);

                fetch(userAjaxUrl, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res && res.success && res.data && res.data.has_new && res.data.id > userLastAdminMsgId) {
                        userLastAdminMsgId = res.data.id;

                        window.OtotamirToast.show({
                            iconHtml: '<i class="fa-solid fa-headset" style="font-size:20px;"></i>',
                            title: 'Ototamir360 Yönetimi',
                            message: res.data.message_snippet,
                            timeText: res.data.time,
                            actionText: 'Yanıtı Gör / Canlı Destek',
                            onClick: function() {
                                window.location.href = res.data.profile_url;
                            }
                        });
                    }
                })
                .catch(function() {});
            }

            setInterval(checkUserNotifications, 18000);
        }
    })();
    </script>
    <?php
}
add_action( 'wp_footer', 'ototamir_support_user_global_notifications' );

