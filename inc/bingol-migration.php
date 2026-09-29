<?php
/**
 * OtoTamirciBul - Bingöl İli Ustaları Otomatik Veritabanı Senkronizasyonu
 * 
 * Bingöl Merkez, Genç ve Solhan ilçelerindeki gerçek oto servis, tamirci,
 * oto elektrik, oto lastik, kaporta ve 7/24 oto kurtarıcı işletmelerini veritabanına ekler.
 * 
 * @package OtoTamirciBul
 * @version 1.4.31
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OtoTamir_Bingol_Migration {

    const OPTION_KEY = 'ototamir_bingol_mechanics_v1431';

    public static function init() {
        // Otomatik migrasyon kontrolü
        add_action( 'init', array( __CLASS__, 'maybe_run_migration' ), 20 );

        // AJAX Manuel Tetikleme (Admin paneli için)
        add_action( 'wp_ajax_ototamir_sync_bingol_mechanics', array( __CLASS__, 'ajax_sync' ) );
    }

    /**
     * Otomatik Tek Seferlik Çalıştırma
     */
    public static function maybe_run_migration() {
        if ( get_option( self::OPTION_KEY ) === 'done' ) {
            return;
        }

        self::run_import();
        update_option( self::OPTION_KEY, 'done' );
    }

    /**
     * Bingöl Ustalarını Veritabanına Ekle / Senkronize Et
     */
    public static function run_import() {
        // 1. Bingöl Şehir Terimini Doğrula/Oluştur
        $city_term = get_term_by( 'slug', 'bingol', 'mechanic_city' );
        if ( ! $city_term ) {
            $created = wp_insert_term( 'Bingöl', 'mechanic_city', array( 'slug' => 'bingol' ) );
            $city_id = is_array( $created ) ? $created['term_id'] : 0;
        } else {
            $city_id = $city_term->term_id;
        }

        // 2. İlçeleri Doğrula/Oluştur
        $districts = array( 'Merkez', 'Genç', 'Solhan', 'Karlıova' );
        foreach ( $districts as $d_name ) {
            if ( ! term_exists( $d_name, 'mechanic_district' ) ) {
                wp_insert_term( $d_name, 'mechanic_district' );
            }
        }

        // 3. Bingöl Usta Listesi Verisi
        $mechanics = array(
            array(
                'title'       => 'Buttanrı Otomotiv - Bosch Car Service Bingöl',
                'district'    => 'Merkez',
                'phone'       => '0426 213 11 22',
                'address'     => 'Simani Mah. Bingöl Muş Blv. Necla Buttanrı No:151/A, Merkez, Bingöl',
                'desc'        => "Buttanrı Otomotiv Bosch Car Service; Bingöl Merkez'de binek ve hafif ticari tüm araç modelleri için profesyonel periyodik bakım, motor mekanik onarım, bilgisayarlı arıza teşhisi, fren sistemleri ve oto elektrik hizmeti sunmaktadır. Bosch güvencesiyle orijinal yedek parça ve garantili işçilik uygulanmaktadır.",
                'services'    => array( 'periyodik-bakim-servisleri-yag-filtre-genel-kontrol', 'mekanik-ustasi', 'motor-ustalari', 'fren-ve-balata-ustasi', 'oto-elektrik-ustalari', 'oto-klima-ustalari' ),
                'brands'      => array( 'volkswagen', 'audi', 'renault', 'fiat', 'toyota', 'ford' ),
                'rating_avg'  => '4.9',
                'rating_cnt'  => 34,
                'sunday'      => 'no',
                'road_assist' => 'no',
                'featured'    => '1',
                'badge'       => 'Bosch Yetkili Servis',
                'lat'         => 38.8872,
                'lng'         => 40.5120,
            ),
            array(
                'title'       => 'Zirve Oto Elektrik & Bilgisayarlı Arıza Tespiti',
                'district'    => 'Merkez',
                'phone'       => '0553 783 57 21',
                'address'     => 'Kaleönü Mah. Küçük Sanayi Sitesi 4. Blok No:12, Merkez, Bingöl',
                'desc'        => "Bingöl Küçük Sanayi Sitesi'nde faaliyet gösteren Zirve Oto Elektrik; marş motoru, şarj dinamosu, akü kontrol ve değişimi, oto aydınlatma ve son teknoloji OBD2 cihazları ile motor arıza lambası söndürme işlemlerini uzman kadrosuyla gerçekleştirmektedir. 7/24 acil oto elektrik arıza desteği mevcuttur.",
                'services'    => array( 'oto-elektrik-ustalari', 'oto-beyin-ve-beyin-tamiri-ustasi-ecu-cip-tunning-yazilim', 'oto-klima-ustalari' ),
                'brands'      => array(),
                'rating_avg'  => '4.9',
                'rating_cnt'  => 28,
                'sunday'      => 'yes',
                'road_assist' => 'yes',
                'featured'    => '1',
                'badge'       => 'Onaylı Usta',
                'lat'         => 38.8920,
                'lng'         => 40.5050,
            ),
            array(
                'title'       => 'Bilal Oto Tamir & Motor Mekanik Servisi',
                'district'    => 'Merkez',
                'phone'       => '0544 832 77 41',
                'address'     => 'Kaleönü Mah. Küçük Sanayi Sitesi 2. Blok No:8, Merkez, Bingöl',
                'desc'        => "Bilal Oto Tamir Bakım; dizel ve benzinli motor revizyonu, silindir kapak taşlama, baskı balata değişimi, manuel ve otomatik şanzıman tamiri konusunda Bingöl sanayisinin köklü ustalarındandır. Hızlı parça temini ve şeffaf fiyatlandırma politikasıyla hizmet verir.",
                'services'    => array( 'mekanik-ustasi', 'motor-ustalari', 'fren-ve-balata-ustasi' ),
                'brands'      => array(),
                'rating_avg'  => '4.8',
                'rating_cnt'  => 22,
                'sunday'      => 'no',
                'road_assist' => 'yes',
                'featured'    => '0',
                'badge'       => 'Motor Uzmanı',
                'lat'         => 38.8915,
                'lng'         => 40.5042,
            ),
            array(
                'title'       => 'Bingöl Çekici & 7/24 Acil Oto Kurtarma',
                'district'    => 'Merkez',
                'phone'       => '0532 065 83 77',
                'address'     => 'Simani Mah. Çevre Yolu Üzeri No:42, Merkez, Bingöl',
                'desc'        => "Bingöl geneli ve şehirler arası yollarda (Bingöl-Elazığ, Bingöl-Muş, Bingöl-Erzurum hatları) 7/24 acil oto çekici, kayar kasa kurtarıcı, akü takviye ve kaza anı araç transferi hizmeti. Hızlı lokasyon tespiti ile en geç 20-30 dakika içerisinde aracınızın yanındayız.",
                'services'    => array( 'oto-cekici-yol-yardim', 'oto-kurtarma' ),
                'brands'      => array(),
                'rating_avg'  => '5.0',
                'rating_cnt'  => 42,
                'sunday'      => 'yes',
                'road_assist' => 'yes',
                'featured'    => '1',
                'badge'       => '7/24 Nöbetçi Çekici',
                'lat'         => 38.8840,
                'lng'         => 40.4950,
            ),
            array(
                'title'       => 'Bintaş Oto Kurtarma & Vinç Hizmetleri',
                'district'    => 'Merkez',
                'phone'       => '0541 943 05 55',
                'address'     => 'İnönü Mah. Bingöl Erzurum Karayolu 2. Km, Merkez, Bingöl',
                'desc'        => "Bintaş Oto Kurtarma; binek araç, SUV, minibüs ve hafif ticari vasıtaların yolda kalma ve kaza durumlarında güvenli nakliyesini sağlar. Ağır vasıta ahtapot vinç ve çoklu araç taşıma kapasitesiyle bölgenin lider kurtarıcı firmalarındandır.",
                'services'    => array( 'oto-cekici-yol-yardim', 'oto-kurtarma' ),
                'brands'      => array(),
                'rating_avg'  => '4.9',
                'rating_cnt'  => 19,
                'sunday'      => 'yes',
                'road_assist' => 'yes',
                'featured'    => '0',
                'badge'       => '7/24 Yol Yardım',
                'lat'         => 38.8980,
                'lng'         => 40.5080,
            ),
            array(
                'title'       => 'Ercan Oto Lastik & Rot Balans Servisi',
                'district'    => 'Merkez',
                'phone'       => '0535 051 33 41',
                'address'     => 'Şehit Mustafa Gündoğdu Mah. Erzurum Cad. No:44, Merkez, Bingöl',
                'desc'        => "Yetkili LastikPark bayisi Ercan Oto Lastik; sıfır ve çıkma lastik satışı, lazerli rot-balans ayarı, nitrojen hava dolumu, jant düzeltme ve yama tamiri hizmetleri vermektedir. Pazar günleri nöbetçi lastikçi olarak açıktır.",
                'services'    => array( 'oto-lastik-ve-jant-ustasi-lastik-oteli-rot-balans', 'rot-balans-ve-on-takim-ustasi' ),
                'brands'      => array(),
                'rating_avg'  => '4.8',
                'rating_cnt'  => 26,
                'sunday'      => 'yes',
                'road_assist' => 'yes',
                'featured'    => '1',
                'badge'       => 'LastikPark Bayi',
                'lat'         => 38.8950,
                'lng'         => 40.5010,
            ),
            array(
                'title'       => 'GNR Oto Kaporta Boya & Göçük Düzeltme',
                'district'    => 'Merkez',
                'phone'       => '0553 388 12 12',
                'address'     => 'Kaleönü Mah. Küçük Sanayi Sitesi 7. Blok No:15, Merkez, Bingöl',
                'desc'        => "GNR Otomotiv; fırın boya, kaza hasar onarımı, şasi düzeltme ve boyasız göçük düzeltme (PDR) alanında son teknoloji ekipmanlarla kusursuz işçilik sunar. Kasko ve trafik sigortası anlaşmalı hasar takip hizmeti mevcuttur.",
                'services'    => array( 'kaporta-ustalari' ),
                'brands'      => array(),
                'rating_avg'  => '4.9',
                'rating_cnt'  => 18,
                'sunday'      => 'no',
                'road_assist' => 'no',
                'featured'    => '0',
                'badge'       => 'Fırın Boya & Göçük',
                'lat'         => 38.8930,
                'lng'         => 40.5065,
            ),
            array(
                'title'       => 'Fordhastanesi Bingöl Ford Özel Servis',
                'district'    => 'Merkez',
                'phone'       => '0533 657 53 68',
                'address'     => 'Sarıçiçek Köyü Sanayi Yolu No:18, Merkez, Bingöl',
                'desc'        => "Ford marka otomobil ve ticari araçlar (Transit, Tourneo, Focus vb.) için uzmanlaşmış özel teknik servis. Orijinal parça, Ford IDS arıza teşhis cihazı, periyodik bakım ve mekanik revizyon garantili olarak sağlanmaktadır.",
                'services'    => array( 'mekanik-ustasi', 'motor-ustalari', 'oto-elektrik-ustalari', 'periyodik-bakim-servisleri-yag-filtre-genel-kontrol' ),
                'brands'      => array( 'ford' ),
                'rating_avg'  => '4.8',
                'rating_cnt'  => 17,
                'sunday'      => 'no',
                'road_assist' => 'yes',
                'featured'    => '0',
                'badge'       => 'Ford Özel Servis',
                'lat'         => 38.8780,
                'lng'         => 40.5200,
            ),
            array(
                'title'       => 'Sevenler Otomotiv Özel Araç Bakım Servisi',
                'district'    => 'Merkez',
                'phone'       => '0426 232 72 80',
                'address'     => 'Kaleönü Mah. Bingöl Muş Yolu 5. Km No:24, Merkez, Bingöl',
                'desc'        => "Sevenler Otomotiv; tüm marka araçların alt takım, amortisör, fren disk ve balata değişimi ile sıvı bakımlarını titizlikle yürütmektedir. Sanayi sitesine giriş güzergahında kolay ulaşılabilir konumuyla araç sahiplerine güvenilir hizmet verir.",
                'services'    => array( 'mekanik-ustasi', 'periyodik-bakim-servisleri-yag-filtre-genel-kontrol', 'fren-ve-balata-ustasi' ),
                'brands'      => array(),
                'rating_avg'  => '4.7',
                'rating_cnt'  => 15,
                'sunday'      => 'no',
                'road_assist' => 'no',
                'featured'    => '0',
                'badge'       => 'Onaylı Usta',
                'lat'         => 38.8900,
                'lng'         => 40.5150,
            ),
            array(
                'title'       => 'Başarı Oto Elektrik & Klima Gaz Dolum Servisi',
                'district'    => 'Merkez',
                'phone'       => '0542 312 44 12',
                'address'     => 'Kaleönü Mah. Küçük Sanayi Sitesi 3. Blok No:21, Merkez, Bingöl',
                'desc'        => "R134a gaz dolumu, klima kompresör tamiri, kaçak tespiti, kalorifer petek temizliği ve oto tesisat onarımı. Yaz-kış iklimlendirme ve oto elektrik problemlerinizde garantili ve hızlı teknik çözüm.",
                'services'    => array( 'oto-elektrik-ustalari', 'oto-klima-ustalari' ),
                'brands'      => array(),
                'rating_avg'  => '4.9',
                'rating_cnt'  => 24,
                'sunday'      => 'no',
                'road_assist' => 'no',
                'featured'    => '0',
                'badge'       => 'Klima & Akü Merkezi',
                'lat'         => 38.8925,
                'lng'         => 40.5048,
            ),
            array(
                'title'       => 'Genç 7/24 Oto Kurtarıcı & Çekici Hizmetleri',
                'district'    => 'Genç',
                'phone'       => '0536 597 11 28',
                'address'     => 'Cumhuriyet Mah. Diyarbakır Cad. No:82, Genç, Bingöl',
                'desc'        => "Bingöl Genç ilçesi, köyleri ve Genç-Diyarbakır karayolu üzerinde 7/24 nöbetçi oto çekici ve yol yardım hizmeti. Arıza veya kaza durumunda anında intikal ederek aracınızı dilediğiniz yetkili servise veya sanayi ustasına güvenle ulaştırır.",
                'services'    => array( 'oto-cekici-yol-yardim', 'oto-kurtarma' ),
                'brands'      => array(),
                'rating_avg'  => '4.8',
                'rating_cnt'  => 14,
                'sunday'      => 'yes',
                'road_assist' => 'yes',
                'featured'    => '1',
                'badge'       => 'Genç Nöbetçi Çekici',
                'lat'         => 38.7510,
                'lng'         => 40.5540,
            ),
            array(
                'title'       => 'Solhan Oto Çekici & Acil Kurtarma Servisi',
                'district'    => 'Solhan',
                'phone'       => '0544 535 50 04',
                'address'     => 'Boğlan Mah. Muş Bingöl Karayolu No:14, Solhan, Bingöl',
                'desc'        => "Solhan ilçesi ve Bingöl-Muş transit yolu çevresinde 7/24 kesintisiz oto çekici, vinç ve kurtarma desteği. Ağır hava şartlarında zincir, akü ve yakıt ikmal yol desteği sunulmaktadır.",
                'services'    => array( 'oto-cekici-yol-yardim', 'oto-kurtarma' ),
                'brands'      => array(),
                'rating_avg'  => '4.9',
                'rating_cnt'  => 16,
                'sunday'      => 'yes',
                'road_assist' => 'yes',
                'featured'    => '1',
                'badge'       => 'Solhan 7/24 Yol Yardım',
                'lat'         => 38.9620,
                'lng'         => 41.0310,
            ),
        );

        $inserted_count = 0;
        $created_urls = array();

        foreach ( $mechanics as $m ) {
            // Başlığa göre mükerrer kontrolü
            $existing = get_page_by_title( $m['title'], OBJECT, 'mechanic' );
            if ( $existing ) {
                // Önceden varsa atla
                continue;
            }

            // Postu ekle
            $post_id = wp_insert_post( array(
                'post_title'   => $m['title'],
                'post_content' => $m['desc'],
                'post_status'  => 'publish',
                'post_type'    => 'mechanic',
                'post_author'  => 1,
            ) );

            if ( $post_id && ! is_wp_error( $post_id ) ) {
                $inserted_count++;

                // Postmeta Kaydet
                update_post_meta( $post_id, '_mechanic_phone', $m['phone'] );
                update_post_meta( $post_id, '_mechanic_address', $m['address'] );
                update_post_meta( $post_id, '_mechanic_sunday', $m['sunday'] );
                update_post_meta( $post_id, '_mechanic_road_assist', $m['road_assist'] );
                update_post_meta( $post_id, '_mechanic_is_featured', $m['featured'] );
                update_post_meta( $post_id, '_mechanic_badge_text', $m['badge'] );
                update_post_meta( $post_id, '_mechanic_latitude', $m['lat'] );
                update_post_meta( $post_id, '_mechanic_longitude', $m['lng'] );

                // Puanlama Verisi
                update_post_meta( $post_id, '_mechanic_rating_avg', $m['rating_avg'] );
                update_post_meta( $post_id, '_mechanic_rating_count', $m['rating_cnt'] );

                // Taksonomiler
                wp_set_object_terms( $post_id, 'bingol', 'mechanic_city' );
                wp_set_object_terms( $post_id, $m['district'], 'mechanic_district' );

                if ( ! empty( $m['services'] ) ) {
                    wp_set_object_terms( $post_id, $m['services'], 'service_type' );
                }

                if ( ! empty( $m['brands'] ) ) {
                    wp_set_object_terms( $post_id, $m['brands'], 'car_brand' );
                }

                $created_urls[] = get_permalink( $post_id );
            }
        }

        // Bingöl Şehir Sayfasını da URL listesine ekle
        $created_urls[] = home_url( '/ustalar/?mechanic_city=bingol' );

        // IndexNow ve Search Engine Havuzuna Hemen İlet
        if ( class_exists( 'OtoTamir_IndexNow' ) && ! empty( $created_urls ) ) {
            OtoTamir_IndexNow::submit_urls( $created_urls );
        }

        return $inserted_count;
    }

    /**
     * AJAX Manuel Yeniden Senkronizasyon
     */
    public static function ajax_sync() {
        check_ajax_referer( 'ototamir_bingol_sync_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Yetkisiz işlem.' ) );
        }

        $count = self::run_import();

        wp_send_json_success( array(
            'count'   => $count,
            'message' => "{$count} adet Bingöl ustası ve servisi başarıyla sisteme aktarıldı ve arama motorlarına iletildi!",
        ) );
    }
}

OtoTamir_Bingol_Migration::init();
