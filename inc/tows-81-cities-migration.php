<?php
/**
 * OtoTamirciBul - Eklenen Tüm Otomatik Çekici / Yol Yardım İlanlarını Temizleyici
 * 
 * Bu dosya, daha önce sisteme eklenmiş olan tüm otomatik/yapay/temsili
 * yol yardım ve oto çekici ilanlarını veritabanından, meta tablolarından ve
 * taksonomi bağlantılarından kalıntısız ve güvenli şekilde tamamen siler.
 * 
 * Kesinlikle hiçbir yeni yapay ilan üretmez veya eklemez.
 * 
 * @package OtoTamir360
 * @version 1.4.54
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OtoTamir_Purge_All_Generated_Tows {

    const PURGE_OPTION = 'ototamir_all_generated_tows_purged_v1454';

    public static function init() {
        add_action( 'init', array( __CLASS__, 'run_purge' ), 5 );
        add_action( 'wp_ajax_ototamir_force_purge_tows', array( __CLASS__, 'ajax_purge' ) );
    }

    public static function ajax_purge() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Yetkisiz erişim.' );
        }
        delete_option( self::PURGE_OPTION );
        $deleted = self::run_purge();
        wp_send_json_success( array( 'purged' => $deleted ) );
    }

    public static function run_purge() {
        if ( get_option( self::PURGE_OPTION ) ) {
            return 0;
        }

        global $wpdb;

        // 1. Eklenen tüm yapay/otomatik çekici ilanlarının ID'lerini tespit et
        // GÜVENLİK: post_date >= '2026-09-08 10:00:00' şartı ile kullanıcının önceki hiçbir orijinal ilanı asla silinmez!
        $post_ids = $wpdb->get_col(
            "SELECT DISTINCT ID FROM {$wpdb->posts} 
             WHERE post_type = 'mechanic' 
             AND post_date >= '2026-09-08 10:00:00'
             AND (
                 ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_mechanic_verified_tow_city')
                 OR post_content LIKE '%çevre ilçelerde, otoyollarda 7/24%'
                 OR post_content LIKE '%kesintisiz oto çekici, oto kurtarıcı, akü takviye%'
                 OR post_title LIKE '%7/24 Acil Oto Kurtarma%'
                 OR post_title LIKE '%7/24 Acil Çekici%'
                 OR post_title LIKE '%7/24 Oto Çekici%'
                 OR post_title LIKE '%Kayseri Erciyes 7/24%'
                 OR post_title LIKE '%Adana Çukurova 7/24%'
                 OR post_title LIKE '%Adana Kalay Oto Kurtarma%'
                 OR post_title LIKE '%Ayyıldız Oto Kurtarma%'
                 OR post_title LIKE '%Dumanlıdağ Oto Kurtarma%'
                 OR post_title LIKE '%Ağrı Oto Kurtarma Yol Yardım%'
                 OR post_title LIKE '%Amasya Oto Kurtarma%'
                 OR post_title LIKE '%Şimşek Oto Kurtarma%'
                 OR post_title LIKE '%Antalya Yıldırım Oto Kurtarma%'
                 OR post_title LIKE '%Şan Oto Kurtarma%'
                 OR post_title LIKE '%09 Oto Kurtarma & Yol Yardım%'
                 OR post_title LIKE '%Balıkesir Merkez Oto Kurtarma%'
                 OR post_title LIKE '%Bilecik Vinç Oto Kurtarma%'
                 OR post_title LIKE '%Bingöl Bintaş Oto Kurtarma%'
                 OR post_title LIKE '%Öz Amatlar Oto Kurtarma%'
                 OR post_title LIKE '%Bolu Oto Kurtarma%'
                 OR post_title LIKE '%Burdur Reç Oto Kurtarma%'
                 OR post_title LIKE '%Bursa Kütük Oto Kurtarma%'
                 OR post_title LIKE '%Çanakkale Oto Kurtarıcı%'
                 OR post_title LIKE '%Teke Kardeşler Çankırı Oto Kurtarma%'
                 OR post_title LIKE '%Çorum Oto Kurtarma & Yol Yardım%'
                 OR post_title LIKE '%Denizli Gençler Oto Kurtarma%'
                 OR post_title LIKE '%EKA Yol Yardım Oto Kurtarma%'
                 OR post_title LIKE '%Özcanlar Oto Kurtarıcı%'
                 OR post_title LIKE '%Acemoğlu Oto Çekici Kurtarma%'
                 OR post_title LIKE '%Erzincan Efe Oto Kurtarma%'
                 OR post_title LIKE '%Pak Oto Kurtarma%'
                 OR post_title LIKE '%Panorama Eskişehir Oto Kurtarma%'
                 OR post_title LIKE '%Gaziantep Sinan Yol Yardım%'
                 OR post_title LIKE '%Seymen Oto Kurtarma%'
                 OR post_title LIKE '%Gümüş Vip Tur Oto Kurtarma%'
                 OR post_title LIKE '%Koç Oto Kurtarma%'
                 OR post_title LIKE '%Ataç Oto Kurtarma%'
                 OR post_title LIKE '%Çevik Oto Kurtarma%'
                 OR post_title LIKE '%Mersin Şimşek Oto Kurtarma%'
                 OR post_title LIKE '%Batur Oto Kurtarıcı & Çekici%'
                 OR post_title LIKE '%Arda Oto Kurtarma Yol Yardım%'
                 OR post_title LIKE '%Doğu Oto Kurtarma%'
                 OR post_title LIKE '%Gözlemeci Oto Kurtarma%'
                 OR post_title LIKE '%Emre Oto Kurtarma%'
                 OR post_title LIKE '%Burgaz Oto Kurtarıcı%'
                 OR post_title LIKE '%Ahi Oto Kurtarma%'
                 OR post_title LIKE '%Genç Oto Kurtarma & Çekici%'
                 OR post_title LIKE '%Konya Emin Oto Kurtarma%'
                 OR post_title LIKE '%Yılmaz Oto Kurtarıcı%'
                 OR post_title LIKE '%Malatya Sarıkaya Oto Kurtarma%'
                 OR post_title LIKE '%Dumrul Oto Yol Yardım%'
                 OR post_title LIKE '%Asaf Oto Kurtarma%'
                 OR post_title LIKE '%Mardin Artuklu Oto Kurtarma%'
                 OR post_title LIKE '%Cengiz Oto Kurtarma%'
                 OR post_title LIKE '%Güven Oto Kurtarma%'
                 OR post_title LIKE '%Nevşehir ASM Oto Çekici%'
                 OR post_title LIKE '%İnce Yol Yardım Oto Kurtarma%'
                 OR post_title LIKE '%Karadağ Oto Kurtarma & Yol Yardım%'
                 OR post_title LIKE '%Eroğlu Rize Oto Kurtarma%'
                 OR post_title LIKE '%Sakarya Nazım Oto Kurtarma%'
                 OR post_title LIKE '%Pusula Oto Kurtarma%'
                 OR post_title LIKE '%Elçiçek Oto Kurtarma%'
                 OR post_title LIKE '%Atalay Oto Kurtarma%'
                 OR post_title LIKE '%Sivas İz Oto Kurtarma%'
                 OR post_title LIKE '%Özyılmaz Oto Çekici%'
                 OR post_title LIKE '%Tokat HD Oto Kurtarma%'
                 OR post_title LIKE '%Trabzon Yıldırım Oto Kurtarma%'
                 OR post_title LIKE '%Tunceli Can Oto Kurtarma%'
                 OR post_title LIKE '%Urfa Tofan Oto Kurtarma%'
                 OR post_title LIKE '%Uşak Gencer Oto Kurtarma%'
                 OR post_title LIKE '%Aksüt Oto Kurtarma%'
                 OR post_title LIKE '%Enes Oto Kurtarma%'
                 OR post_title LIKE '%Öztürk Kardeşler Oto Kurtarma%'
                 OR post_title LIKE '%Aksaray Ömür Oto Kurtarma%'
                 OR post_title LIKE '%Bayburt Kaya Oto Kurtarma%'
                 OR post_title LIKE '%Karaman Oto Kurtarma%'
                 OR post_title LIKE '%Kırıkkale Eko Oto Kurtarma%'
                 OR post_title LIKE '%Batman Oto Çekici%'
                 OR post_title LIKE '%Dicle Oto Kurtarma%'
                 OR post_title LIKE '%İskender Oto Kurtarma%'
                 OR post_title LIKE '%Ardahan Oto Kurtarma Servisi%'
                 OR post_title LIKE '%Üç Kardeşler Oto Tamir & Yol Yardım%'
                 OR post_title LIKE '%Yalova Oto Kurtarma%'
                 OR post_title LIKE '%Karabük Yeşilyurt Oto Kurtarma%'
                 OR post_title LIKE '%Kilis Oto Kurtarıcı Yol Yardım%'
                 OR post_title LIKE '%Osmaniye Oto Kurtarma%'
                 OR post_title LIKE '%Düzce Kahraman Oto Kurtarma%'
             )"
        );

        $deleted_count = 0;

        if ( ! empty( $post_ids ) ) {
            $ids_in = implode( ',', array_map( 'intval', $post_ids ) );

            // 1. Postları sil
            $wpdb->query( "DELETE FROM {$wpdb->posts} WHERE ID IN ({$ids_in})" );

            // 2. Postmeta verilerini sil
            $wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE post_id IN ({$ids_in})" );

            // 3. Taksonomi bağlantılarını sil
            $wpdb->query( "DELETE FROM {$wpdb->term_relationships} WHERE object_id IN ({$ids_in})" );

            $deleted_count = count( $post_ids );
        }

        // Eski migrasyon anahtarlarını sil
        delete_option( 'ototamir_81_cities_tows_v1' );
        delete_option( 'ototamir_clean_dummy_tows_v1452' );
        delete_option( 'ototamir_verified_81_tows_v2' );

        // Temizliğin tamamlandığını kaydet
        update_option( self::PURGE_OPTION, 'done_purged_' . $deleted_count );

        return $deleted_count;
    }
}

OtoTamir_Purge_All_Generated_Tows::init();
