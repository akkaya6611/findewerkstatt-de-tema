<?php
/**
 * OTOTAMİR-360 Mükerrer Usta Temizleyici (Deduplicator)
 * 
 * Veritabanında mükerrer (duplicate) olarak eklenmiş aynı usta ve servis kayıtlarını
 * güvenli bir şekilde tespit eder; orijinal kaydı (en küçük ID) tutar,
 * kopyaları ve bunlara ait wp_postmeta ile taxonomy ilişkilerini temizler.
 *
 * @package OtoTamir_360
 * @version 1.4.67
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OtoTamir_Mechanic_Deduplicator {

    /**
     * Başlatıcı
     */
    public static function init() {
        // Otomatik tek seferlik temizlik (Tema aktifleştiğinde veya ilk sayfa yenilendiğinde)
        add_action( 'init', [ __CLASS__, 'maybe_run_auto_cleanup' ], 5 );

        // Manuel tetikleme (Admin paneli üstünden veya gizli URL anahtarı ile)
        add_action( 'admin_post_ototamir_clean_duplicates', [ __CLASS__, 'handle_manual_cleanup' ] );
        add_action( 'init', [ __CLASS__, 'handle_secret_url_trigger' ] );

        // Admin Bildirimi
        add_action( 'admin_notices', [ __CLASS__, 'render_admin_notice' ] );
    }

    /**
     * Otomatik Temizleme Kontrolü
     */
    public static function maybe_run_auto_cleanup() {
        $done_version = get_option( 'ototamir_deduplication_run_v1468', false );
        if ( ! $done_version ) {
            self::run_deduplication();
            update_option( 'ototamir_deduplication_run_v1468', 1 );
        }
    }

    /**
     * Gizli URL ile Tetikleme (Gerekirse tarayıcıdan admin olmadan da tetiklenebilir)
     * URL: https://ototamircibul.com.tr/?ototamir_cleanup_duplicates=ototamir360_clean_now
     */
    public static function handle_secret_url_trigger() {
        if ( isset( $_GET['ototamir_cleanup_duplicates'] ) && $_GET['ototamir_cleanup_duplicates'] === 'ototamir360_clean_now' ) {
            $deleted_count = self::run_deduplication();
            wp_die(
                '<div style="max-width:600px;margin:40px auto;font-family:sans-serif;background:#fff;padding:30px;border-radius:12px;box-shadow:0 4px 15px rgba(0,0,0,0.1);">' .
                '<h2 style="color:#10b981;margin-top:0;">✅ Mükerrer Usta Temizliği Başarıyla Tamamlandı!</h2>' .
                '<p style="font-size:16px;line-height:1.6;color:#334155;">Veritabanınız taranarak toplam <strong style="color:#f97316;font-size:18px;">' . (int) $deleted_count . '</strong> adet mükerrer (kopya) usta kaydı güvenle temizlendi. Her dükkanın yalnızca 1 adet orijinal profili yayında bırakıldı.</p>' .
                '<p style="margin-top:25px;"><a href="' . esc_url( home_url( '/ustalar' ) ) . '" style="display:inline-block;background:#f97316;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:700;">← Ustalar Sayfasına Git</a></p>' .
                '</div>',
                'Mükerrer Temizliği Tamamlandı'
            );
        }
    }

    /**
     * Manuel Tetikleme (Admin Post)
     */
    public static function handle_manual_cleanup() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Yetkisiz işlem.' );
        }
        check_admin_referer( 'ototamir_clean_duplicates_action' );

        $count = self::run_deduplication();
        set_transient( 'ototamir_dedup_success_count', $count, 60 );
        wp_safe_redirect( wp_get_referer() ?: admin_url() );
        exit;
    }

    /**
     * Temizleme Bildirimi Göster
     */
    public static function render_admin_notice() {
        $count = get_transient( 'ototamir_dedup_success_count' );
        if ( $count !== false ) {
            delete_transient( 'ototamir_dedup_success_count' );
            ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>✅ OtoTamir-360 Temizlik Raporu:</strong> Toplam <strong><?php echo (int) $count; ?></strong> adet mükerrer (kopya) usta kaydı veritabanından güvenle temizlendi. Her dükkanın sadece 1 orijinal profili yayında bırakıldı.</p>
            </div>
            <?php
        }
    }

    /**
     * Mükerrer Kayıtları Bul ve Temizle (Ana Algoritma)
     *
     * @return int Silinen mükerrer post sayısı
     */
    public static function run_deduplication() {
        global $wpdb;

        // 1. Tüm mechanic postlarını ve ayırt edici alanlarını hafızaya al
        $query = "
            SELECT p.ID, p.post_title, p.post_name,
                   pm_phone.meta_value AS phone,
                   pm_addr.meta_value  AS address,
                   pm_lat.meta_value   AS lat,
                   pm_lng.meta_value   AS lng
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm_phone ON (pm_phone.post_id = p.ID AND pm_phone.meta_key = '_mechanic_phone')
            LEFT JOIN {$wpdb->postmeta} pm_addr  ON (pm_addr.post_id = p.ID AND pm_addr.meta_key = '_mechanic_address')
            LEFT JOIN {$wpdb->postmeta} pm_lat   ON (pm_lat.post_id = p.ID AND pm_lat.meta_key = '_mechanic_latitude')
            LEFT JOIN {$wpdb->postmeta} pm_lng   ON (pm_lng.post_id = p.ID AND pm_lng.meta_key = '_mechanic_longitude')
            WHERE p.post_type = 'mechanic'
            ORDER BY p.ID ASC
        ";

        $posts = $wpdb->get_results( $query );
        if ( empty( $posts ) ) {
            return 0;
        }

        $groups = array();

        foreach ( $posts as $p ) {
            $raw_title = trim( $p->post_title );
            if ( empty( $raw_title ) ) {
                continue;
            }

            // Normalizasyon: Başlık (küçük harf, çift boşluksuz)
            $norm_title = mb_strtolower( preg_replace( '/\s+/', ' ', $raw_title ), 'UTF-8' );

            // Telefon (sadece rakamlar, son 10 hane)
            $digits_only = preg_replace( '/\D+/', '', (string) $p->phone );
            $phone_key = ( strlen( $digits_only ) >= 10 ) ? substr( $digits_only, -10 ) : '';

            // GPS Koordinatı (4 basamak yuvarlama: ~11 metre hassasiyet)
            $lat_f = (float) ( $p->lat ?? 0 );
            $lng_f = (float) ( $p->lng ?? 0 );
            $coord_key = ( $lat_f > 0 && $lng_f > 0 ) ? number_format( $lat_f, 4, '.', '' ) . '_' . number_format( $lng_f, 4, '.', '' ) : '';

            // Adres (harf ve rakamlar)
            $clean_addr = mb_strtolower( preg_replace( '/[^a-z0-9ğüşıöç]/iu', '', (string) $p->address ), 'UTF-8' );
            $addr_key   = substr( $clean_addr, 0, 30 );

            // Parmak İzi (Fingerprint) Belirleme:
            if ( ! empty( $phone_key ) ) {
                $fingerprint = 'tel:' . $norm_title . '___' . $phone_key;
            } elseif ( ! empty( $coord_key ) ) {
                $fingerprint = 'geo:' . $norm_title . '___' . $coord_key;
            } elseif ( ! empty( $addr_key ) ) {
                $fingerprint = 'addr:' . $norm_title . '___' . $addr_key;
            } else {
                $base_slug = preg_replace( '/-\d+$/', '', $p->post_name );
                $fingerprint = 'slug:' . $norm_title . '___' . $base_slug;
            }

            $groups[$fingerprint][] = (int) $p->ID;
        }

        $delete_ids = array();
        foreach ( $groups as $fp => $ids ) {
            if ( count( $ids ) > 1 ) {
                // En küçük ID'yi (ilk eklenen orijinal kaydı) tut
                $keep_id = array_shift( $ids );
                // Diğer tüm kopyaları silinecekler listesine ekle
                foreach ( $ids as $dup_id ) {
                    $delete_ids[] = $dup_id;
                }
            }
        }

        if ( empty( $delete_ids ) ) {
            return 0;
        }

        // 2. Güvenli Toplu Silme (500'lük parçalar halinde)
        $total_deleted = count( $delete_ids );
        $chunks = array_chunk( $delete_ids, 500 );

        foreach ( $chunks as $chunk ) {
            $placeholders = implode( ',', array_fill( 0, count( $chunk ), '%d' ) );

            // postmeta sil
            $wpdb->query( $wpdb->prepare(
                "DELETE FROM {$wpdb->postmeta} WHERE post_id IN ($placeholders)",
                $chunk
            ) );

            // term_relationships sil
            $wpdb->query( $wpdb->prepare(
                "DELETE FROM {$wpdb->term_relationships} WHERE object_id IN ($placeholders)",
                $chunk
            ) );

            // posts sil
            $wpdb->query( $wpdb->prepare(
                "DELETE FROM {$wpdb->posts} WHERE ID IN ($placeholders)",
                $chunk
            ) );
        }

        // 3. Taksonomi Sayaçlarını Senkronize Et
        $taxonomies = array( 'mechanic_city', 'mechanic_district', 'service_type', 'car_brand' );
        foreach ( $taxonomies as $tax ) {
            $term_ids = get_terms( array(
                'taxonomy'   => $tax,
                'hide_empty' => false,
                'fields'     => 'ids',
            ) );
            if ( ! empty( $term_ids ) && ! is_wp_error( $term_ids ) ) {
                wp_update_term_count_now( $term_ids, $tax );
            }
        }

        // 4. Kalıcı Önbellekleri Temizle
        wp_cache_flush();

        return $total_deleted;
    }
}

OtoTamir_Mechanic_Deduplicator::init();
