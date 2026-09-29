<?php
/**
 * İl İl Akaryakıt Fiyatları ve Rota Mesafe Veri Seti
 * Canlı OPET API Senkronizasyonu & Otomatik Günlük Güncelleme Motoru
 * 
 * @package OtoTamir360
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Canli OPET API Uzerinden Akaryakit Fiyatlarini Senkronize Eder
 * 
 * @param bool $force Zorunlu guncelleme
 * @return array|false
 */
function ototamir_sync_live_fuel_prices( $force = false ) {
    $lock = function_exists( 'get_transient' ) ? get_transient( 'ototamir_fuel_sync_lock' ) : false;
    if ( $lock && ! $force ) {
        return false;
    }

    // OPET Resmi Istanbul (34) Canli Pompa Fiyatlari API Servisi
    $url = 'https://api.opet.com.tr/api/fuelprices/prices?ProvinceCode=34&IncludeAllProducts=true';
    $response = function_exists( 'wp_remote_get' ) ? wp_remote_get( $url, array(
        'headers'   => array(
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        ),
        'timeout'   => 8,
        'sslverify' => false
    ) ) : false;

    if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( ! empty( $data[0]['prices'] ) && is_array( $data[0]['prices'] ) ) {
            $benzin = 0.0;
            $motorin = 0.0;

            foreach ( $data[0]['prices'] as $p ) {
                $pname = $p['productName'] ?? '';
                $pamount = floatval( $p['amount'] ?? 0 );

                if ( $pamount > 0 ) {
                    if ( stripos( $pname, 'Benzin 95' ) !== false || stripos( $pname, 'Kurşunsuz' ) !== false || stripos( $pname, 'Kursunsuz' ) !== false ) {
                        if ( $benzin == 0.0 ) {
                            $benzin = $pamount;
                        }
                    } elseif ( stripos( $pname, 'Motorin' ) !== false || stripos( $pname, 'UltraForce' ) !== false || stripos( $pname, 'EcoForce' ) !== false ) {
                        if ( $motorin == 0.0 ) {
                            $motorin = $pamount;
                        }
                    }
                }
            }

            if ( $benzin > 40 && $motorin > 40 ) {
                $lpg = 34.80; // Turkiye otogaz (LPG) pompa ortalamasi

                $saved_data = array(
                    'benzin'        => $benzin,
                    'motorin'       => $motorin,
                    'lpg'           => $lpg,
                    'last_update'   => function_exists( 'current_time' ) ? current_time( 'mysql' ) : date( 'Y-m-d H:i:s' ),
                    'source'        => 'OPET Resmi Canlı Pompa Fiyatları'
                );

                if ( function_exists( 'update_option' ) ) {
                    update_option( 'ototamir_fuel_base_prices', $saved_data );
                }
                if ( function_exists( 'set_transient' ) ) {
                    set_transient( 'ototamir_fuel_sync_lock', 1, 6 * HOUR_IN_SECONDS );
                }
                return $saved_data;
            }
        }
    }

    if ( function_exists( 'set_transient' ) ) {
        set_transient( 'ototamir_fuel_sync_lock', 1, 1 * HOUR_IN_SECONDS );
    }
    return false;
}

/**
 * 81 İl İçin Güncel Akaryakıt (Benzin, Motorin, LPG) Fiyat Verilerini Döndürür
 * 
 * @return array
 */
function ototamir_get_fuel_prices() {
    $custom_prices = function_exists( 'get_option' ) ? get_option( 'ototamir_fuel_base_prices', array() ) : array();
    
    // Eger veri yoksa veya sync transient suresi dolmussa canli olarak guncelle
    if ( empty( $custom_prices['benzin'] ) || ( function_exists( 'get_transient' ) && ! get_transient( 'ototamir_fuel_sync_lock' ) ) ) {
        $synced = ototamir_sync_live_fuel_prices();
        if ( is_array( $synced ) ) {
            $custom_prices = $synced;
        }
    }

    // 15 Eylul 2026 Guncel Pompa Taban Fiyatlari (Istanbul Bazli)
    $base_benzin  = ! empty( $custom_prices['benzin'] ) ? floatval( $custom_prices['benzin'] ) : 80.16;
    $base_motorin = ! empty( $custom_prices['motorin'] ) ? floatval( $custom_prices['motorin'] ) : 95.53;
    $base_lpg     = ! empty( $custom_prices['lpg'] ) ? floatval( $custom_prices['lpg'] ) : 34.80;

    $last_update_date = ! empty( $custom_prices['last_update'] ) ? date( 'd.m.Y', strtotime( $custom_prices['last_update'] ) ) : date( 'd.m.Y' );

    // 81 İl Listesi ve lojistik/nakliye katsayıları
    $cities = array(
        'adana'          => array( 'name' => 'Adana', 'benzin_diff' => 1.35, 'motorin_diff' => 1.50, 'lpg_diff' => 0.50 ),
        'adiyaman'       => array( 'name' => 'Adıyaman', 'benzin_diff' => 1.85, 'motorin_diff' => 2.05, 'lpg_diff' => 0.75 ),
        'afyonkarahisar' => array( 'name' => 'Afyonkarahisar', 'benzin_diff' => 1.15, 'motorin_diff' => 1.25, 'lpg_diff' => 0.40 ),
        'agri'           => array( 'name' => 'Ağrı', 'benzin_diff' => 2.45, 'motorin_diff' => 2.65, 'lpg_diff' => 1.10 ),
        'aksaray'        => array( 'name' => 'Aksaray', 'benzin_diff' => 1.25, 'motorin_diff' => 1.35, 'lpg_diff' => 0.45 ),
        'amasya'         => array( 'name' => 'Amasya', 'benzin_diff' => 1.45, 'motorin_diff' => 1.60, 'lpg_diff' => 0.55 ),
        'ankara'         => array( 'name' => 'Ankara', 'benzin_diff' => 1.10, 'motorin_diff' => 1.24, 'lpg_diff' => 0.49 ),
        'antalya'        => array( 'name' => 'Antalya', 'benzin_diff' => 1.60, 'motorin_diff' => 1.75, 'lpg_diff' => 0.60 ),
        'ardahan'        => array( 'name' => 'Ardahan', 'benzin_diff' => 2.50, 'motorin_diff' => 2.70, 'lpg_diff' => 1.15 ),
        'artvin'         => array( 'name' => 'Artvin', 'benzin_diff' => 2.10, 'motorin_diff' => 2.25, 'lpg_diff' => 0.90 ),
        'aydin'          => array( 'name' => 'Aydın', 'benzin_diff' => 0.35, 'motorin_diff' => 0.30, 'lpg_diff' => 0.10 ),
        'balikesir'      => array( 'name' => 'Balıkesir', 'benzin_diff' => 0.30, 'motorin_diff' => 0.25, 'lpg_diff' => 0.05 ),
        'bartin'         => array( 'name' => 'Bartın', 'benzin_diff' => 0.50, 'motorin_diff' => 0.45, 'lpg_diff' => 0.20 ),
        'batman'         => array( 'name' => 'Batman', 'benzin_diff' => 0.90, 'motorin_diff' => 0.85, 'lpg_diff' => 0.40 ),
        'bayburt'        => array( 'name' => 'Bayburt', 'benzin_diff' => 1.05, 'motorin_diff' => 1.00, 'lpg_diff' => 0.50 ),
        'bilecik'        => array( 'name' => 'Bilecik', 'benzin_diff' => 0.25, 'motorin_diff' => 0.20, 'lpg_diff' => 0.05 ),
        'bingol'         => array( 'name' => 'Bingöl', 'benzin_diff' => 1.10, 'motorin_diff' => 1.05, 'lpg_diff' => 0.50 ),
        'bitlis'         => array( 'name' => 'Bitlis', 'benzin_diff' => 1.15, 'motorin_diff' => 1.10, 'lpg_diff' => 0.55 ),
        'bolu'           => array( 'name' => 'Bolu', 'benzin_diff' => 0.90, 'motorin_diff' => 0.95, 'lpg_diff' => 0.30 ),
        'burdur'         => array( 'name' => 'Burdur', 'benzin_diff' => 1.45, 'motorin_diff' => 1.60, 'lpg_diff' => 0.55 ),
        'bursa'          => array( 'name' => 'Bursa', 'benzin_diff' => 1.13, 'motorin_diff' => 1.10, 'lpg_diff' => -0.21 ),
        'canakkale'      => array( 'name' => 'Çanakkale', 'benzin_diff' => 1.25, 'motorin_diff' => 1.35, 'lpg_diff' => 0.45 ),
        'cankiri'        => array( 'name' => 'Çankırı', 'benzin_diff' => 1.35, 'motorin_diff' => 1.45, 'lpg_diff' => 0.50 ),
        'corum'          => array( 'name' => 'Çorum', 'benzin_diff' => 1.50, 'motorin_diff' => 1.65, 'lpg_diff' => 0.55 ),
        'denizli'        => array( 'name' => 'Denizli', 'benzin_diff' => 1.35, 'motorin_diff' => 1.45, 'lpg_diff' => 0.45 ),
        'diyarbakir'     => array( 'name' => 'Diyarbakır', 'benzin_diff' => 2.10, 'motorin_diff' => 2.35, 'lpg_diff' => 0.95 ),
        'duzce'          => array( 'name' => 'Düzce', 'benzin_diff' => 0.85, 'motorin_diff' => 0.90, 'lpg_diff' => 0.25 ),
        'edirne'         => array( 'name' => 'Edirne', 'benzin_diff' => 0.90, 'motorin_diff' => 0.95, 'lpg_diff' => 0.30 ),
        'elazig'         => array( 'name' => 'Elazığ', 'benzin_diff' => 1.95, 'motorin_diff' => 2.15, 'lpg_diff' => 0.85 ),
        'erzincan'       => array( 'name' => 'Erzincan', 'benzin_diff' => 2.15, 'motorin_diff' => 2.35, 'lpg_diff' => 0.95 ),
        'erzurum'        => array( 'name' => 'Erzurum', 'benzin_diff' => 2.30, 'motorin_diff' => 2.50, 'lpg_diff' => 1.10 ),
        'eskisehir'      => array( 'name' => 'Eskişehir', 'benzin_diff' => 1.00, 'motorin_diff' => 1.10, 'lpg_diff' => 0.30 ),
        'gaziantep'      => array( 'name' => 'Gaziantep', 'benzin_diff' => 1.65, 'motorin_diff' => 1.80, 'lpg_diff' => 0.65 ),
        'giresun'        => array( 'name' => 'Giresun', 'benzin_diff' => 1.70, 'motorin_diff' => 1.85, 'lpg_diff' => 0.65 ),
        'gumushane'      => array( 'name' => 'Gümüşhane', 'benzin_diff' => 2.15, 'motorin_diff' => 2.35, 'lpg_diff' => 0.95 ),
        'hakkari'        => array( 'name' => 'Hakkari', 'benzin_diff' => 2.60, 'motorin_diff' => 2.85, 'lpg_diff' => 1.25 ),
        'hatay'          => array( 'name' => 'Hatay', 'benzin_diff' => 1.55, 'motorin_diff' => 1.70, 'lpg_diff' => 0.60 ),
        'igdir'          => array( 'name' => 'Iğdır', 'benzin_diff' => 2.55, 'motorin_diff' => 2.75, 'lpg_diff' => 1.20 ),
        'isparta'        => array( 'name' => 'Isparta', 'benzin_diff' => 1.45, 'motorin_diff' => 1.60, 'lpg_diff' => 0.55 ),
        'istanbul'       => array( 'name' => 'İstanbul', 'benzin_diff' => 0.00, 'motorin_diff' => 0.00, 'lpg_diff' => 0.00 ),
        'izmir'          => array( 'name' => 'İzmir', 'benzin_diff' => 1.38, 'motorin_diff' => 1.51, 'lpg_diff' => 0.39 ),
        'kahramanmaras'  => array( 'name' => 'Kahramanmaraş', 'benzin_diff' => 1.70, 'motorin_diff' => 1.85, 'lpg_diff' => 0.70 ),
        'karabuk'        => array( 'name' => 'Karabük', 'benzin_diff' => 1.35, 'motorin_diff' => 1.45, 'lpg_diff' => 0.50 ),
        'karaman'        => array( 'name' => 'Karaman', 'benzin_diff' => 1.55, 'motorin_diff' => 1.70, 'lpg_diff' => 0.60 ),
        'kars'           => array( 'name' => 'Kars', 'benzin_diff' => 2.45, 'motorin_diff' => 2.65, 'lpg_diff' => 1.15 ),
        'kastamonu'      => array( 'name' => 'Kastamonu', 'benzin_diff' => 1.50, 'motorin_diff' => 1.65, 'lpg_diff' => 0.60 ),
        'kayseri'        => array( 'name' => 'Kayseri', 'benzin_diff' => 1.30, 'motorin_diff' => 1.45, 'lpg_diff' => 0.45 ),
        'kilis'          => array( 'name' => 'Kilis', 'benzin_diff' => 1.75, 'motorin_diff' => 1.90, 'lpg_diff' => 0.70 ),
        'kirikkale'      => array( 'name' => 'Kırıkkale', 'benzin_diff' => 1.25, 'motorin_diff' => 1.35, 'lpg_diff' => 0.40 ),
        'kirklareli'     => array( 'name' => 'Kırklareli', 'benzin_diff' => 0.85, 'motorin_diff' => 0.90, 'lpg_diff' => 0.30 ),
        'kirsehir'       => array( 'name' => 'Kırşehir', 'benzin_diff' => 1.35, 'motorin_diff' => 1.45, 'lpg_diff' => 0.45 ),
        'kocaeli'        => array( 'name' => 'Kocaeli', 'benzin_diff' => 0.15, 'motorin_diff' => 0.15, 'lpg_diff' => 0.10 ),
        'konya'          => array( 'name' => 'Konya', 'benzin_diff' => 1.40, 'motorin_diff' => 1.55, 'lpg_diff' => 0.50 ),
        'kutahya'        => array( 'name' => 'Kütahya', 'benzin_diff' => 1.25, 'motorin_diff' => 1.35, 'lpg_diff' => 0.40 ),
        'malatya'        => array( 'name' => 'Malatya', 'benzin_diff' => 1.90, 'motorin_diff' => 2.10, 'lpg_diff' => 0.80 ),
        'manisa'         => array( 'name' => 'Manisa', 'benzin_diff' => 1.25, 'motorin_diff' => 1.35, 'lpg_diff' => 0.40 ),
        'mardin'         => array( 'name' => 'Mardin', 'benzin_diff' => 2.25, 'motorin_diff' => 2.45, 'lpg_diff' => 1.05 ),
        'mersin'         => array( 'name' => 'Mersin', 'benzin_diff' => 1.30, 'motorin_diff' => 1.45, 'lpg_diff' => 0.45 ),
        'mugla'          => array( 'name' => 'Muğla', 'benzin_diff' => 1.45, 'motorin_diff' => 1.60, 'lpg_diff' => 0.55 ),
        'mus'            => array( 'name' => 'Muş', 'benzin_diff' => 2.40, 'motorin_diff' => 2.60, 'lpg_diff' => 1.15 ),
        'nevsehir'       => array( 'name' => 'Nevşehir', 'benzin_diff' => 1.40, 'motorin_diff' => 1.55, 'lpg_diff' => 0.50 ),
        'nigde'          => array( 'name' => 'Niğde', 'benzin_diff' => 1.45, 'motorin_diff' => 1.60, 'lpg_diff' => 0.55 ),
        'ordu'           => array( 'name' => 'Ordu', 'benzin_diff' => 1.65, 'motorin_diff' => 1.80, 'lpg_diff' => 0.60 ),
        'osmaniye'       => array( 'name' => 'Osmaniye', 'benzin_diff' => 1.40, 'motorin_diff' => 1.55, 'lpg_diff' => 0.50 ),
        'rize'           => array( 'name' => 'Rize', 'benzin_diff' => 1.85, 'motorin_diff' => 2.00, 'lpg_diff' => 0.75 ),
        'sakarya'        => array( 'name' => 'Sakarya', 'benzin_diff' => 0.25, 'motorin_diff' => 0.20, 'lpg_diff' => 0.10 ),
        'samsun'         => array( 'name' => 'Samsun', 'benzin_diff' => 1.50, 'motorin_diff' => 1.65, 'lpg_diff' => 0.55 ),
        'sanliurfa'      => array( 'name' => 'Şanlıurfa', 'benzin_diff' => 1.95, 'motorin_diff' => 2.15, 'lpg_diff' => 0.85 ),
        'siirt'          => array( 'name' => 'Siirt', 'benzin_diff' => 2.35, 'motorin_diff' => 2.55, 'lpg_diff' => 1.10 ),
        'sinop'          => array( 'name' => 'Sinop', 'benzin_diff' => 1.70, 'motorin_diff' => 1.85, 'lpg_diff' => 0.65 ),
        'sivas'          => array( 'name' => 'Sivas', 'benzin_diff' => 1.70, 'motorin_diff' => 1.85, 'lpg_diff' => 0.65 ),
        'sirnak'         => array( 'name' => 'Şırnak', 'benzin_diff' => 2.45, 'motorin_diff' => 2.70, 'lpg_diff' => 1.20 ),
        'tekirdag'       => array( 'name' => 'Tekirdağ', 'benzin_diff' => 0.20, 'motorin_diff' => 0.20, 'lpg_diff' => 0.10 ),
        'tokat'          => array( 'name' => 'Tokat', 'benzin_diff' => 1.55, 'motorin_diff' => 1.70, 'lpg_diff' => 0.60 ),
        'trabzon'        => array( 'name' => 'Trabzon', 'benzin_diff' => 1.80, 'motorin_diff' => 1.95, 'lpg_diff' => 0.70 ),
        'tunceli'        => array( 'name' => 'Tunceli', 'benzin_diff' => 2.30, 'motorin_diff' => 2.50, 'lpg_diff' => 1.05 ),
        'usak'           => array( 'name' => 'Uşak', 'benzin_diff' => 1.25, 'motorin_diff' => 1.35, 'lpg_diff' => 0.40 ),
        'van'            => array( 'name' => 'Van', 'benzin_diff' => 2.50, 'motorin_diff' => 2.70, 'lpg_diff' => 1.20 ),
        'yalova'         => array( 'name' => 'Yalova', 'benzin_diff' => 0.20, 'motorin_diff' => 0.20, 'lpg_diff' => 0.10 ),
        'yozgat'         => array( 'name' => 'Yozgat', 'benzin_diff' => 1.40, 'motorin_diff' => 1.55, 'lpg_diff' => 0.50 ),
        'zonguldak'      => array( 'name' => 'Zonguldak', 'benzin_diff' => 1.25, 'motorin_diff' => 1.35, 'lpg_diff' => 0.45 ),
    );

    $results = array();
    foreach ( $cities as $slug => $c ) {
        $results[$slug] = array(
            'slug'        => $slug,
            'name'        => $c['name'],
            'benzin'      => number_format( $base_benzin + $c['benzin_diff'], 2, '.', '' ),
            'motorin'     => number_format( $base_motorin + $c['motorin_diff'], 2, '.', '' ),
            'lpg'         => number_format( $base_lpg + $c['lpg_diff'], 2, '.', '' ),
            'last_update' => $last_update_date,
        );
    }

    return $results;
}

/**
 * Popüler Şehirler Arası Referans Karayolu Kilometre Matrisi
 * 
 * @return array
 */
function ototamir_get_popular_routes_matrix() {
    return array(
        'istanbul-ankara'     => 450,
        'ankara-istanbul'     => 450,
        'istanbul-izmir'      => 480,
        'izmir-istanbul'      => 480,
        'istanbul-bursa'      => 155,
        'bursa-istanbul'      => 155,
        'istanbul-antalya'    => 690,
        'antalya-istanbul'    => 690,
        'ankara-izmir'        => 590,
        'izmir-ankara'        => 590,
        'ankara-antalya'      => 480,
        'antalya-ankara'      => 480,
        'izmir-bursa'         => 345,
        'bursa-izmir'         => 345,
        'istanbul-adana'      => 930,
        'adana-istanbul'      => 930,
        'ankara-adana'        => 490,
        'adana-ankara'        => 490,
        'istanbul-trabzon'    => 1060,
        'trabzon-istanbul'    => 1060,
        'ankara-trabzon'      => 740,
        'trabzon-ankara'      => 740,
        'istanbul-diyarbakir' => 1430,
        'diyarbakir-istanbul' => 1430,
        'ankara-konya'        => 260,
        'konya-ankara'        => 260,
        'istanbul-eskisehir'  => 300,
        'eskisehir-istanbul'  => 300,
        'ankara-eskisehir'    => 235,
        'eskisehir-ankara'    => 235,
        'istanbul-kocaeli'    => 100,
        'kocaeli-istanbul'    => 100,
        'istanbul-sakarya'    => 150,
        'sakarya-istanbul'    => 150,
        'izmir-antalya'       => 460,
        'antalya-izmir'       => 460,
        'bursa-antalya'       => 545,
        'antalya-bursa'       => 545,
        'adana-gaziantep'     => 220,
        'gaziantep-adana'     => 220,
        'istanbul-samsun'     => 730,
        'samsun-istanbul'     => 730,
        'ankara-samsun'       => 410,
        'samsun-ankara'       => 410,
    );
}
