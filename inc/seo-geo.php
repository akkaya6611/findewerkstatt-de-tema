<?php
/**
 * OtoTamirciBul - Gelişmiş Yerel SEO (GEO) ve Schema.org Yapılandırılmış Veri Motoru
 * 
 * Özellikler:
 * 1. Türkiye 81 İl GEO Veritabanı (ISO 3166-2 Kodları, Resmi İl İsimleri ve GPS Koordinatları)
 * 2. Standart Yerel SEO Meta Etiketleri (geo.region, geo.placename, geo.position, ICBM)
 * 3. Google & AI (SGE) Uyumlu Schema.org (JSON-LD) Üretimi:
 *    - BreadcrumbList (Tüm sayfalar için hiyerarşik ekmek kırıntısı)
 *    - AutoRepair / AutomotiveBusiness (Gelişmiş GPS koordinatları, çalışma saatleri, ödeme türleri)
 *    - ItemList (Şehir ve kategori listeleme sayfaları için usta dizini)
 *    - FAQPage (Şehir ve ilçe sayfalarında zengin sonuç soru-cevap akordeonu)
 *    - Article / BlogPosting (Blog yazıları için yazar ve yayıncı şeması)
 * 
 * @package OtoTamirciBul
 * @version 1.4.18
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1. TÜRKİYE 81 İL GEO VE KOORDİNAT VERİTABANI
 * Her il için resmi plaka, ISO 3166-2 kod, il adı ve merkez koordinatları (enlem/boylam).
 */
function ototamir_get_provinces_geo_data() {
    static $provinces = null;
    if ( $provinces !== null ) {
        return $provinces;
    }

    $provinces = array(
        '01' => array( 'plate' => '01', 'region' => 'TR-01', 'name' => 'Adana',          'slug' => 'adana',          'lat' => 37.0000, 'lng' => 35.3213 ),
        '02' => array( 'plate' => '02', 'region' => 'TR-02', 'name' => 'Adıyaman',        'slug' => 'adiyaman',       'lat' => 37.7648, 'lng' => 38.2786 ),
        '03' => array( 'plate' => '03', 'region' => 'TR-03', 'name' => 'Afyonkarahisar',  'slug' => 'afyonkarahisar', 'lat' => 38.7507, 'lng' => 30.5567 ),
        '04' => array( 'plate' => '04', 'region' => 'TR-04', 'name' => 'Ağrı',           'slug' => 'agri',           'lat' => 39.7191, 'lng' => 43.0503 ),
        '05' => array( 'plate' => '05', 'region' => 'TR-05', 'name' => 'Amasya',         'slug' => 'amasya',         'lat' => 40.6534, 'lng' => 35.8331 ),
        '06' => array( 'plate' => '06', 'region' => 'TR-06', 'name' => 'Ankara',         'slug' => 'ankara',         'lat' => 39.9334, 'lng' => 32.8597 ),
        '07' => array( 'plate' => '07', 'region' => 'TR-07', 'name' => 'Antalya',        'slug' => 'antalya',        'lat' => 36.8969, 'lng' => 30.7133 ),
        '08' => array( 'plate' => '08', 'region' => 'TR-08', 'name' => 'Artvin',         'slug' => 'artvin',         'lat' => 41.1828, 'lng' => 41.8183 ),
        '09' => array( 'plate' => '09', 'region' => 'TR-09', 'name' => 'Aydın',          'slug' => 'aydin',          'lat' => 37.8444, 'lng' => 27.8458 ),
        '10' => array( 'plate' => '10', 'region' => 'TR-10', 'name' => 'Balıkesir',      'slug' => 'balikesir',      'lat' => 39.6484, 'lng' => 27.8826 ),
        '11' => array( 'plate' => '11', 'region' => 'TR-11', 'name' => 'Bilecik',        'slug' => 'bilecik',        'lat' => 40.1451, 'lng' => 29.9799 ),
        '12' => array( 'plate' => '12', 'region' => 'TR-12', 'name' => 'Bingöl',         'slug' => 'bingol',         'lat' => 38.8854, 'lng' => 40.4983 ),
        '13' => array( 'plate' => '13', 'region' => 'TR-13', 'name' => 'Bitlis',         'slug' => 'bitlis',         'lat' => 38.4006, 'lng' => 42.1095 ),
        '14' => array( 'plate' => '14', 'region' => 'TR-14', 'name' => 'Bolu',           'slug' => 'bolu',           'lat' => 40.7350, 'lng' => 31.6061 ),
        '15' => array( 'plate' => '15', 'region' => 'TR-15', 'name' => 'Burdur',         'slug' => 'burdur',         'lat' => 37.7203, 'lng' => 30.2908 ),
        '16' => array( 'plate' => '16', 'region' => 'TR-16', 'name' => 'Bursa',          'slug' => 'bursa',          'lat' => 40.1885, 'lng' => 29.0610 ),
        '17' => array( 'plate' => '17', 'region' => 'TR-17', 'name' => 'Çanakkale',      'slug' => 'canakkale',      'lat' => 40.1553, 'lng' => 26.4142 ),
        '18' => array( 'plate' => '18', 'region' => 'TR-18', 'name' => 'Çankırı',        'slug' => 'cankiri',        'lat' => 40.6013, 'lng' => 33.6134 ),
        '19' => array( 'plate' => '19', 'region' => 'TR-19', 'name' => 'Çorum',          'slug' => 'corum',          'lat' => 40.5506, 'lng' => 34.9556 ),
        '20' => array( 'plate' => '20', 'region' => 'TR-20', 'name' => 'Denizli',        'slug' => 'denizli',        'lat' => 37.7765, 'lng' => 29.0864 ),
        '21' => array( 'plate' => '21', 'region' => 'TR-21', 'name' => 'Diyarbakır',     'slug' => 'diyarbakir',     'lat' => 37.9144, 'lng' => 40.2306 ),
        '22' => array( 'plate' => '22', 'region' => 'TR-22', 'name' => 'Edirne',         'slug' => 'edirne',         'lat' => 41.6771, 'lng' => 26.5557 ),
        '23' => array( 'plate' => '23', 'region' => 'TR-23', 'name' => 'Elazığ',         'slug' => 'elazig',         'lat' => 38.6810, 'lng' => 39.2264 ),
        '24' => array( 'plate' => '24', 'region' => 'TR-24', 'name' => 'Erzincan',       'slug' => 'erzincan',       'lat' => 39.7500, 'lng' => 39.5000 ),
        '25' => array( 'plate' => '25', 'region' => 'TR-25', 'name' => 'Erzurum',        'slug' => 'erzurum',        'lat' => 39.9043, 'lng' => 41.2678 ),
        '26' => array( 'plate' => '26', 'region' => 'TR-26', 'name' => 'Eskişehir',      'slug' => 'eskisehir',      'lat' => 39.7767, 'lng' => 30.5206 ),
        '27' => array( 'plate' => '27', 'region' => 'TR-27', 'name' => 'Gaziantep',      'slug' => 'gaziantep',      'lat' => 37.0662, 'lng' => 37.3833 ),
        '28' => array( 'plate' => '28', 'region' => 'TR-28', 'name' => 'Giresun',        'slug' => 'giresun',        'lat' => 40.9128, 'lng' => 38.3895 ),
        '29' => array( 'plate' => '29', 'region' => 'TR-29', 'name' => 'Gümüşhane',      'slug' => 'gumushane',      'lat' => 40.4600, 'lng' => 39.4718 ),
        '30' => array( 'plate' => '30', 'region' => 'TR-30', 'name' => 'Hakkâri',        'slug' => 'hakkari',        'lat' => 37.5833, 'lng' => 43.7333 ),
        '31' => array( 'plate' => '31', 'region' => 'TR-31', 'name' => 'Hatay',          'slug' => 'hatay',          'lat' => 36.2023, 'lng' => 36.1606 ),
        '32' => array( 'plate' => '32', 'region' => 'TR-32', 'name' => 'Isparta',        'slug' => 'isparta',        'lat' => 37.7648, 'lng' => 30.5566 ),
        '33' => array( 'plate' => '33', 'region' => 'TR-33', 'name' => 'Mersin',         'slug' => 'mersin',         'lat' => 36.8121, 'lng' => 34.6415 ),
        '34' => array( 'plate' => '34', 'region' => 'TR-34', 'name' => 'İstanbul',       'slug' => 'istanbul',       'lat' => 41.0082, 'lng' => 28.9784 ),
        '35' => array( 'plate' => '35', 'region' => 'TR-35', 'name' => 'İzmir',          'slug' => 'izmir',          'lat' => 38.4237, 'lng' => 27.1428 ),
        '36' => array( 'plate' => '36', 'region' => 'TR-36', 'name' => 'Kars',           'slug' => 'kars',           'lat' => 40.6013, 'lng' => 43.0975 ),
        '37' => array( 'plate' => '37', 'region' => 'TR-37', 'name' => 'Kastamonu',      'slug' => 'kastamonu',      'lat' => 41.3887, 'lng' => 33.7827 ),
        '38' => array( 'plate' => '38', 'region' => 'TR-38', 'name' => 'Kayseri',        'slug' => 'kayseri',        'lat' => 38.7312, 'lng' => 35.4787 ),
        '39' => array( 'plate' => '39', 'region' => 'TR-39', 'name' => 'Kırklareli',     'slug' => 'kirklareli',     'lat' => 41.7333, 'lng' => 27.2167 ),
        '40' => array( 'plate' => '40', 'region' => 'TR-40', 'name' => 'Kırşehir',       'slug' => 'kirsehir',       'lat' => 39.1425, 'lng' => 34.1709 ),
        '41' => array( 'plate' => '41', 'region' => 'TR-41', 'name' => 'Kocaeli',        'slug' => 'kocaeli',        'lat' => 40.8533, 'lng' => 29.8815 ),
        '42' => array( 'plate' => '42', 'region' => 'TR-42', 'name' => 'Konya',          'slug' => 'konya',          'lat' => 37.8667, 'lng' => 32.4833 ),
        '43' => array( 'plate' => '43', 'region' => 'TR-43', 'name' => 'Kütahya',        'slug' => 'kutahya',        'lat' => 39.4167, 'lng' => 29.9833 ),
        '44' => array( 'plate' => '44', 'region' => 'TR-44', 'name' => 'Malatya',        'slug' => 'malatya',        'lat' => 38.3552, 'lng' => 38.3095 ),
        '45' => array( 'plate' => '45', 'region' => 'TR-45', 'name' => 'Manisa',         'slug' => 'manisa',         'lat' => 38.6191, 'lng' => 27.4289 ),
        '46' => array( 'plate' => '46', 'region' => 'TR-46', 'name' => 'Kahramanmaraş',  'slug' => 'kahramanmaras',  'lat' => 37.5858, 'lng' => 36.9371 ),
        '47' => array( 'plate' => '47', 'region' => 'TR-47', 'name' => 'Mardin',         'slug' => 'mardin',         'lat' => 37.3212, 'lng' => 40.7245 ),
        '48' => array( 'plate' => '48', 'region' => 'TR-48', 'name' => 'Muğla',          'slug' => 'mugla',          'lat' => 37.2153, 'lng' => 28.3636 ),
        '49' => array( 'plate' => '49', 'region' => 'TR-49', 'name' => 'Muş',            'slug' => 'mus',            'lat' => 38.7432, 'lng' => 41.5064 ),
        '50' => array( 'plate' => '50', 'region' => 'TR-50', 'name' => 'Nevşehir',       'slug' => 'nevsehir',       'lat' => 38.6244, 'lng' => 34.7144 ),
        '51' => array( 'plate' => '51', 'region' => 'TR-51', 'name' => 'Niğde',          'slug' => 'nigde',          'lat' => 37.9667, 'lng' => 34.6833 ),
        '52' => array( 'plate' => '52', 'region' => 'TR-52', 'name' => 'Ordu',           'slug' => 'ordu',           'lat' => 40.9839, 'lng' => 37.8764 ),
        '53' => array( 'plate' => '53', 'region' => 'TR-53', 'name' => 'Rize',           'slug' => 'rize',           'lat' => 41.0201, 'lng' => 40.5234 ),
        '54' => array( 'plate' => '54', 'region' => 'TR-54', 'name' => 'Sakarya',        'slug' => 'sakarya',        'lat' => 40.7569, 'lng' => 30.3783 ),
        '55' => array( 'plate' => '55', 'region' => 'TR-55', 'name' => 'Samsun',         'slug' => 'samsun',         'lat' => 41.2928, 'lng' => 36.3313 ),
        '56' => array( 'plate' => '56', 'region' => 'TR-56', 'name' => 'Siirt',          'slug' => 'siirt',          'lat' => 37.9333, 'lng' => 41.9500 ),
        '57' => array( 'plate' => '57', 'region' => 'TR-57', 'name' => 'Sinop',          'slug' => 'sinop',          'lat' => 42.0231, 'lng' => 35.1531 ),
        '58' => array( 'plate' => '58', 'region' => 'TR-58', 'name' => 'Sivas',          'slug' => 'sivas',          'lat' => 39.7477, 'lng' => 37.0179 ),
        '59' => array( 'plate' => '59', 'region' => 'TR-59', 'name' => 'Tekirdağ',       'slug' => 'tekirdag',       'lat' => 40.9833, 'lng' => 27.5167 ),
        '60' => array( 'plate' => '60', 'region' => 'TR-60', 'name' => 'Tokat',          'slug' => 'tokat',          'lat' => 40.3167, 'lng' => 36.5500 ),
        '61' => array( 'plate' => '61', 'region' => 'TR-61', 'name' => 'Trabzon',        'slug' => 'trabzon',        'lat' => 41.0027, 'lng' => 39.7168 ),
        '62' => array( 'plate' => '62', 'region' => 'TR-62', 'name' => 'Tunceli',        'slug' => 'tunceli',        'lat' => 39.1079, 'lng' => 39.5401 ),
        '63' => array( 'plate' => '63', 'region' => 'TR-63', 'name' => 'Şanlıurfa',      'slug' => 'sanliurfa',      'lat' => 37.1674, 'lng' => 38.7955 ),
        '64' => array( 'plate' => '64', 'region' => 'TR-64', 'name' => 'Uşak',           'slug' => 'usak',           'lat' => 38.6823, 'lng' => 29.4082 ),
        '65' => array( 'plate' => '65', 'region' => 'TR-65', 'name' => 'Van',            'slug' => 'van',            'lat' => 38.4891, 'lng' => 43.4089 ),
        '66' => array( 'plate' => '66', 'region' => 'TR-66', 'name' => 'Yozgat',         'slug' => 'yozgat',         'lat' => 39.8181, 'lng' => 34.8147 ),
        '67' => array( 'plate' => '67', 'region' => 'TR-67', 'name' => 'Zonguldak',      'slug' => 'zonguldak',      'lat' => 41.4564, 'lng' => 31.7987 ),
        '68' => array( 'plate' => '68', 'region' => 'TR-68', 'name' => 'Aksaray',        'slug' => 'aksaray',        'lat' => 38.3687, 'lng' => 34.0370 ),
        '69' => array( 'plate' => '69', 'region' => 'TR-69', 'name' => 'Bayburt',        'slug' => 'bayburt',        'lat' => 40.2552, 'lng' => 40.2249 ),
        '70' => array( 'plate' => '70', 'region' => 'TR-70', 'name' => 'Karaman',        'slug' => 'karaman',        'lat' => 37.1759, 'lng' => 33.2287 ),
        '71' => array( 'plate' => '71', 'region' => 'TR-71', 'name' => 'Kırıkkale',      'slug' => 'kirikkale',      'lat' => 39.8468, 'lng' => 33.5153 ),
        '72' => array( 'plate' => '72', 'region' => 'TR-72', 'name' => 'Batman',         'slug' => 'batman',         'lat' => 37.8812, 'lng' => 41.1293 ),
        '73' => array( 'plate' => '73', 'region' => 'TR-73', 'name' => 'Şırnak',         'slug' => 'sirnak',         'lat' => 37.5164, 'lng' => 42.4918 ),
        '74' => array( 'plate' => '74', 'region' => 'TR-74', 'name' => 'Bartın',         'slug' => 'bartin',         'lat' => 41.6344, 'lng' => 32.3375 ),
        '75' => array( 'plate' => '75', 'region' => 'TR-75', 'name' => 'Ardahan',        'slug' => 'ardahan',        'lat' => 41.1105, 'lng' => 42.7022 ),
        '76' => array( 'plate' => '76', 'region' => 'TR-76', 'name' => 'Iğdır',          'slug' => 'igdir',          'lat' => 39.9196, 'lng' => 44.0454 ),
        '77' => array( 'plate' => '77', 'region' => 'TR-77', 'name' => 'Yalova',         'slug' => 'yalova',         'lat' => 40.6550, 'lng' => 29.2769 ),
        '78' => array( 'plate' => '78', 'region' => 'TR-78', 'name' => 'Karabük',        'slug' => 'karabuk',        'lat' => 41.2061, 'lng' => 32.6204 ),
        '79' => array( 'plate' => '79', 'region' => 'TR-79', 'name' => 'Kilis',          'slug' => 'kilis',          'lat' => 36.7184, 'lng' => 37.1212 ),
        '80' => array( 'plate' => '80', 'region' => 'TR-80', 'name' => 'Osmaniye',       'slug' => 'osmaniye',       'lat' => 37.0742, 'lng' => 36.2472 ),
        '81' => array( 'plate' => '81', 'region' => 'TR-81', 'name' => 'Düzce',          'slug' => 'duzce',          'lat' => 40.8438, 'lng' => 31.1565 ),
    );

    return $provinces;
}

/**
 * Türkçe Karakterleri ve İ-I harflerini güvenle URL slug haline getirir
 */
if ( ! function_exists( 'ototamir_clean_turkish_slug' ) ) {
    function ototamir_clean_turkish_slug( $str ) {
        if ( empty( $str ) ) return '';
        $tr = array(
            'ı' => 'i', 'I' => 'i', 'İ' => 'i', 'i̇' => 'i',
            'ğ' => 'g', 'Ğ' => 'g',
            'ü' => 'u', 'Ü' => 'u',
            'ş' => 's', 'Ş' => 's',
            'ö' => 'o', 'Ö' => 'o',
            'ç' => 'c', 'Ç' => 'c',
        );
        $clean = strtr( (string) $str, $tr );
        $clean = preg_replace( '/[^a-zA-Z0-9\-_]/', '-', strtolower( $clean ) );
        return trim( preg_replace( '/-+/', '-', $clean ), '-' );
    }
}

/**
 * Şehir ismi veya slug'ından GEO verisini bulur
 */
function ototamir_get_city_geo( $city_name_or_slug ) {
    if ( empty( $city_name_or_slug ) ) {
        return null;
    }

    $provinces = ototamir_get_provinces_geo_data();
    
    // Temizle ve slug haline getir
    $find = ototamir_clean_turkish_slug( $city_name_or_slug );

    foreach ( $provinces as $p ) {
        if ( $p['slug'] === $find || ototamir_clean_turkish_slug( $p['name'] ) === $find ) {
            return $p;
        }
        // Plaka ile arama
        if ( $p['plate'] === str_pad( trim( $city_name_or_slug ), 2, '0', STR_PAD_LEFT ) ) {
            return $p;
        }
    }

    // İsim benzerliği kontrolü (örn. Afyon -> Afyonkarahisar, Maraş -> Kahramanmaraş, Urfa -> Şanlıurfa)
    $lower = ototamir_clean_turkish_slug( $city_name_or_slug );
    
    foreach ( $provinces as $p ) {
        $p_clean = ototamir_clean_turkish_slug( $p['name'] );
        if ( ! empty( $lower ) && ! empty( $p_clean ) && ( stripos( $p_clean, $lower ) !== false || stripos( $lower, $p_clean ) !== false ) ) {
            return $p;
        }
    }

    return null;
}

/**
 * 2. GEÇERLİ SAYFANIN YEREL SEO (GEO) BİLGİLERİNİ TESPİT ET
 */
function ototamir_detect_page_geo() {
    // Varsayılan: Türkiye Genel Merkezi (Ankara merkez koordinatları)
    $geo = array(
        'region'    => 'TR',
        'placename' => 'Türkiye',
        'lat'       => 39.9334,
        'lng'       => 32.8597,
        'city'      => '',
        'district'  => '',
        'is_exact'  => false,
    );

    // A) Tekil Usta Sayfası (single-mechanic)
    if ( is_singular( 'mechanic' ) ) {
        $post_id = get_the_ID();
        
        $cities = wp_get_post_terms( $post_id, 'mechanic_city' );
        $districts = wp_get_post_terms( $post_id, 'mechanic_district' );
        $city = ( $cities && ! is_wp_error( $cities ) ) ? $cities[0]->name : '';
        $district = ( $districts && ! is_wp_error( $districts ) ) ? $districts[0]->name : '';

        $geo['city'] = $city;
        $geo['district'] = $district;

        $lat = get_post_meta( $post_id, '_mechanic_latitude', true );
        $lng = get_post_meta( $post_id, '_mechanic_longitude', true );

        if ( ! empty( $lat ) && ! empty( $lng ) && is_numeric( $lat ) && is_numeric( $lng ) ) {
            $geo['lat'] = (float) $lat;
            $geo['lng'] = (float) $lng;
            $geo['is_exact'] = true;
        } elseif ( $city ) {
            $city_geo = ototamir_get_city_geo( $city );
            if ( $city_geo ) {
                $geo['lat'] = $city_geo['lat'];
                $geo['lng'] = $city_geo['lng'];
                $geo['region'] = $city_geo['region'];
            }
        }

        if ( $city ) {
            $city_geo = ototamir_get_city_geo( $city );
            if ( $city_geo ) {
                $geo['region'] = $city_geo['region'];
            }
            if ( $district ) {
                $geo['placename'] = "{$district}, {$city}, Türkiye";
            } else {
                $geo['placename'] = "{$city}, Türkiye";
            }
        }

        return $geo;
    }

    // B) Şehir Taksonomi Sayfası veya Filtreli Arama
    $city_name = '';
    $district_name = '';

    if ( is_tax( 'mechanic_city' ) ) {
        $term = get_queried_object();
        if ( $term && ! is_wp_error( $term ) ) {
            $city_name = $term->name;
        }
    } elseif ( is_tax( 'mechanic_district' ) ) {
        $term = get_queried_object();
        if ( $term && ! is_wp_error( $term ) ) {
            $district_name = $term->name;
        }
    }

    if ( isset( $_GET['mechanic_city'] ) && ! empty( $_GET['mechanic_city'] ) ) {
        $term = get_term_by( 'slug', sanitize_text_field( $_GET['mechanic_city'] ), 'mechanic_city' );
        if ( $term ) {
            $city_name = $term->name;
        }
    }

    if ( isset( $_GET['mechanic_district'] ) && ! empty( $_GET['mechanic_district'] ) ) {
        $district_name = sanitize_text_field( $_GET['mechanic_district'] );
    }

    if ( $city_name ) {
        $city_geo = ototamir_get_city_geo( $city_name );
        if ( $city_geo ) {
            $geo['region'] = $city_geo['region'];
            $geo['lat'] = $city_geo['lat'];
            $geo['lng'] = $city_geo['lng'];
            $geo['city'] = $city_geo['name'];
        }
        if ( $district_name ) {
            $geo['district'] = $district_name;
            $geo['placename'] = "{$district_name}, {$city_name}, Türkiye";
        } else {
            $geo['placename'] = "{$city_name}, Türkiye";
        }
        return $geo;
    }

    return $geo;
}

/**
 * 3. YEREL SEO (GEO) META ETİKETLERİNİ <head> İÇİNE ENJEKTE ET
 */
function ototamir_render_geo_meta_tags() {
    $geo = ototamir_detect_page_geo();
    ?>
    <!-- ================================================================= -->
    <!-- OtoTamirciBul Yerel SEO (GEO) & Harita Konumlandırma Etiketleri   -->
    <!-- ================================================================= -->
    <meta name="geo.region" content="<?php echo esc_attr( $geo['region'] ); ?>">
    <meta name="geo.placename" content="<?php echo esc_attr( $geo['placename'] ); ?>">
    <meta name="geo.position" content="<?php echo esc_attr( number_format( $geo['lat'], 6, '.', '' ) . ';' . number_format( $geo['lng'], 6, '.', '' ) ); ?>">
    <meta name="ICBM" content="<?php echo esc_attr( number_format( $geo['lat'], 6, '.', '' ) . ', ' . number_format( $geo['lng'], 6, '.', '' ) ); ?>">
    <?php
}
add_action( 'wp_head', 'ototamir_render_geo_meta_tags', 2 );

/**
 * 4. BREADCRUMBLIST SCHEMA (JSON-LD) ÜRETİCİSİ
 * Google arama sonuçlarında site hiyerarşisini gösterir.
 */
function ototamir_get_breadcrumb_schema_items() {
    $items = array();
    $position = 1;

    // 1. Ana Sayfa
    $items[] = array(
        '@type'    => 'ListItem',
        'position' => $position++,
        'name'     => 'Ana Sayfa',
        'item'     => home_url( '/' ),
    );

    // Front page ise sadece ana sayfa
    if ( is_front_page() ) {
        return $items;
    }

    // A) Tekil Usta
    if ( is_singular( 'mechanic' ) ) {
        $post_id = get_the_ID();
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => 'Ustalar',
            'item'     => home_url( '/ustalar' ),
        );

        $cities = wp_get_post_terms( $post_id, 'mechanic_city' );
        $districts = wp_get_post_terms( $post_id, 'mechanic_district' );
        $city = ( $cities && ! is_wp_error( $cities ) ) ? $cities[0] : null;
        $district = ( $districts && ! is_wp_error( $districts ) ) ? $districts[0] : null;

        if ( $city ) {
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => $city->name,
                'item'     => home_url( '/ustalar/?mechanic_city=' . $city->slug ),
            );
        }

        if ( $city && $district ) {
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => $district->name,
                'item'     => home_url( '/ustalar/?mechanic_city=' . $city->slug . '&mechanic_district=' . urlencode( $district->name ) ),
            );
        }

        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => get_the_title( $post_id ),
            'item'     => get_permalink( $post_id ),
        );

        return $items;
    }

    // B) Şehir / Kategori / İlçe Arşivi
    if ( is_post_type_archive( 'mechanic' ) || is_tax( array( 'mechanic_city', 'mechanic_district', 'service_type', 'car_brand' ) ) ) {
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => 'Ustalar',
            'item'     => home_url( '/ustalar' ),
        );

        $city_name = '';
        $city_slug = '';
        $dist_name = '';
        $service_name = '';
        $brand_name = '';
        $brand_slug = '';
        $model_name = '';
        $model_slug = '';

        if ( is_tax() ) {
            $q = get_queried_object();
            if ( $q && ! is_wp_error( $q ) ) {
                if ( $q->taxonomy === 'mechanic_city' ) {
                    $city_name = $q->name;
                    $city_slug = $q->slug;
                } elseif ( $q->taxonomy === 'mechanic_district' ) {
                    $dist_name = $q->name;
                } elseif ( $q->taxonomy === 'service_type' ) {
                    $service_name = $q->name;
                } elseif ( $q->taxonomy === 'car_brand' ) {
                    if ( ! empty( $q->parent ) ) {
                        $model_name = $q->name;
                        $model_slug = $q->slug;
                        $parent_b = get_term( $q->parent, 'car_brand' );
                        if ( $parent_b && ! is_wp_error( $parent_b ) ) {
                            $brand_name = $parent_b->name;
                            $brand_slug = $parent_b->slug;
                        }
                    } else {
                        $brand_name = $q->name;
                        $brand_slug = $q->slug;
                    }
                }
            }
        }

        if ( isset( $_GET['mechanic_city'] ) && ! empty( $_GET['mechanic_city'] ) ) {
            $t = get_term_by( 'slug', sanitize_text_field( $_GET['mechanic_city'] ), 'mechanic_city' );
            if ( $t ) {
                $city_name = $t->name;
                $city_slug = $t->slug;
            }
        }

        if ( isset( $_GET['mechanic_district'] ) && ! empty( $_GET['mechanic_district'] ) ) {
            $dist_name = sanitize_text_field( $_GET['mechanic_district'] );
        }

        if ( isset( $_GET['service_type'] ) && ! empty( $_GET['service_type'] ) ) {
            $s = get_term_by( 'slug', sanitize_text_field( $_GET['service_type'] ), 'service_type' );
            if ( $s ) {
                $service_name = $s->name;
            }
        }

        if ( isset( $_GET['car_model'] ) && ! empty( $_GET['car_model'] ) ) {
            $m = get_term_by( 'slug', sanitize_text_field( $_GET['car_model'] ), 'car_brand' );
            if ( $m ) {
                $model_name = $m->name;
                $model_slug = $m->slug;
                if ( empty( $brand_name ) && ! empty( $m->parent ) ) {
                    $pb = get_term( $m->parent, 'car_brand' );
                    if ( $pb && ! is_wp_error( $pb ) ) {
                        $brand_name = $pb->name;
                        $brand_slug = $pb->slug;
                    }
                }
            }
        }

        if ( isset( $_GET['car_brand'] ) && ! empty( $_GET['car_brand'] ) ) {
            $b = get_term_by( 'slug', sanitize_text_field( $_GET['car_brand'] ), 'car_brand' );
            if ( $b ) {
                if ( ! empty( $b->parent ) ) {
                    $model_name = $b->name;
                    $model_slug = $b->slug;
                    $pb = get_term( $b->parent, 'car_brand' );
                    if ( $pb && ! is_wp_error( $pb ) ) {
                        $brand_name = $pb->name;
                        $brand_slug = $pb->slug;
                    }
                } else {
                    $brand_name = $b->name;
                    $brand_slug = $b->slug;
                }
            }
        }

        if ( $city_name ) {
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => $city_name,
                'item'     => home_url( '/ustalar/?mechanic_city=' . $city_slug ),
            );
        }

        if ( $city_name && $dist_name ) {
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => $dist_name,
                'item'     => home_url( '/ustalar/?mechanic_city=' . $city_slug . '&mechanic_district=' . urlencode( $dist_name ) ),
            );
        } elseif ( $dist_name ) {
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => $dist_name,
                'item'     => home_url( '/ustalar/?mechanic_district=' . urlencode( $dist_name ) ),
            );
        }

        if ( $brand_name ) {
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => $brand_name,
                'item'     => home_url( '/marka/' . $brand_slug ),
            );
        }

        if ( $model_name ) {
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => $model_name,
                'item'     => home_url( '/marka/' . $brand_slug . '/' . $model_slug ),
            );
        }

        if ( $service_name ) {
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => $service_name,
                'item'     => home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) ),
            );
        }

        return $items;
    }

    // C) Tekil Blog Yazısı
    if ( is_singular( 'post' ) ) {
        $post_id = get_the_ID();
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => 'Blog',
            'item'     => home_url( '/blog' ),
        );

        $cats = get_the_category( $post_id );
        if ( ! empty( $cats ) ) {
            $cat = $cats[0];
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => $cat->name,
                'item'     => get_category_link( $cat->term_id ),
            );
        }

        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => get_the_title( $post_id ),
            'item'     => get_permalink( $post_id ),
        );

        return $items;
    }

    // D) Sayfa (Page)
    if ( is_page() ) {
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => get_the_title(),
            'item'     => get_permalink(),
        );
        return $items;
    }

    return $items;
}

/**
 * 5. AUTOREPAIR SCHEMA (JSON-LD) ÜRETİCİSİ
 * Google Yerel İşletme (LocalBusiness / AutoRepair) zengin kartı
 */
function ototamir_get_single_autorepair_schema( $post_id ) {
    $title       = get_the_title( $post_id );
    $url         = get_permalink( $post_id );
    $phone       = get_post_meta( $post_id, '_mechanic_phone', true );
    $address     = get_post_meta( $post_id, '_mechanic_address', true );
    $sunday      = get_post_meta( $post_id, '_mechanic_sunday', true );
    $road_assist = get_post_meta( $post_id, '_mechanic_road_assist', true );
    
    $image_url = get_post_meta( $post_id, '_custom_mechanic_image', true );
    if ( empty( $image_url ) ) {
        $image_url = has_post_thumbnail( $post_id ) ? get_the_post_thumbnail_url( $post_id, 'full' ) : get_template_directory_uri() . '/assets/images/logo-main.png';
    }

    $cities = wp_get_post_terms( $post_id, 'mechanic_city' );
    $districts = wp_get_post_terms( $post_id, 'mechanic_district' );
    $city = ( $cities && ! is_wp_error( $cities ) ) ? $cities[0]->name : '';
    $district = ( $districts && ! is_wp_error( $districts ) ) ? $districts[0]->name : '';

    $geo = ototamir_detect_page_geo();

    // Taksonomiler
    $service_terms = wp_get_post_terms( $post_id, 'service_type' );
    $brand_terms   = wp_get_post_terms( $post_id, 'car_brand' );
    $service_names = ( $service_terms && ! is_wp_error( $service_terms ) ) ? wp_list_pluck( $service_terms, 'name' ) : array();
    $brand_names   = ( $brand_terms && ! is_wp_error( $brand_terms ) ) ? wp_list_pluck( $brand_terms, 'name' ) : array();
    $knows_about   = array_merge( $service_names, $brand_names );

    $rating = function_exists( 'get_mechanic_rating_data' ) ? get_mechanic_rating_data( $post_id ) : array( 'avg' => '5.0', 'count' => 0 );
    $maps_query = urlencode( "{$title} {$address} {$district} {$city}" );

    // Çalışma Saatleri (Opening Hours)
    $opening_hours = array();
    
    // Hafta içi ve Cumartesi standart çalışma
    $opening_hours[] = array(
        '@type'     => 'OpeningHoursSpecification',
        'dayOfWeek' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ),
        'opens'     => '08:30',
        'closes'    => '19:00',
    );

    // Pazar Günü Açık mı?
    if ( $sunday === 'yes' ) {
        $opening_hours[] = array(
            '@type'     => 'OpeningHoursSpecification',
            'dayOfWeek' => 'Sunday',
            'opens'     => '09:00',
            'closes'    => '18:00',
        );
    }

    $schema = array(
        '@context'           => 'https://schema.org',
        '@type'              => 'AutoRepair',
        '@id'                => esc_url( $url ) . '#autorepair',
        'name'               => $title,
        'url'                => esc_url( $url ),
        'image'              => esc_url( $image_url ),
        'telephone'          => $phone ?: '+49300000000',
        'priceRange'         => '€€',
        'currenciesAccepted' => 'EUR',
        'paymentAccepted'    => 'Barzahlung, Girocard, EC-Karte, Kreditkarte, Überweisung',
        'hasMap'             => 'https://www.google.com/maps/search/?api=1&query=' . $maps_query,
        'address'            => array(
            '@type'           => 'PostalAddress',
            'streetAddress'   => $address ?: ( $city . ', Deutschland' ),
            'addressLocality' => $city,
            'addressRegion'   => $city,
            'addressCountry'  => 'DE',
        ),
        'geo'                => array(
            '@type'     => 'GeoCoordinates',
            'latitude'  => (float) number_format( $geo['lat'], 6, '.', '' ),
            'longitude' => (float) number_format( $geo['lng'], 6, '.', '' ),
        ),
        'openingHoursSpecification' => $opening_hours,
    );

    if ( $city ) {
        $schema['areaServed'] = array(
            '@type' => 'City',
            'name'  => $city,
        );
    }

    if ( ! empty( $knows_about ) ) {
        $schema['knowsAbout'] = array_values( array_unique( $knows_about ) );
    }

    if ( ! empty( $rating['count'] ) && $rating['count'] > 0 ) {
        $schema['aggregateRating'] = array(
            '@type'       => 'AggregateRating',
            'ratingValue' => number_format( (float) $rating['avg'], 1, '.', '' ),
            'reviewCount' => (int) $rating['count'],
            'bestRating'  => '5',
            'worstRating' => '1',
        );
    }

    return $schema;
}

/**
 * 6. ŞEHİR VE İLÇE SAYFALARI İÇİN DİNAMİK FAQPAGE (SIKÇA SORULAN SORULAR) SCHEMA
 * Google arama sonuçlarında doğrudan soru-cevap akordeonu açar (SGE & Rich Results).
 */
function ototamir_get_city_faq_schema( $city_name, $district_name = '' ) {
    $loc = $district_name ? "{$city_name} {$district_name}" : $city_name;

    $questions = array(
        array(
            'q' => "{$loc} bölgesinde en iyi oto tamircisi nasıl seçilir?",
            'a' => "OtoTamirciBul rehberinde yer alan {$loc} oto tamir servislerinin müşteri yorumlarını, puanlarını, uzmanlık alanlarını (motor mekanik, oto elektrik, kaporta, klima) ve sertifikalarını inceleyerek güvenilir servislere tek tıkla doğrudan ulaşabilirsiniz.",
        ),
        array(
            'q' => "{$loc} oto tamir ve periyodik bakım fiyatları 2026 yılında ne kadar?",
            'a' => "{$loc} genelinde oto tamir, yağ ve filtre bakımı, fren balata değişimi ve arıza tespit fiyatları aracın marka/modeline ve yapılacak işleme göre değişir. Ustalarımızın profillerinden telefonla ücretsiz ön fiyat teklifi alabilirsiniz.",
        ),
        array(
            'q' => "{$loc} 7/24 nöbetçi oto tamirci ve yol yardım hizmeti var mı?",
            'a' => "Evet, OtoTamirciBul üzerindeki '7/24 Yol Yardım' ve 'Pazar Açık / Nöbetçi' filtrelerini kullanarak {$loc} ve çevresinde gece ve tatil günlerinde acil destek veren çekici ve mobil yol yardım ustalarına 7/24 ulaşabilirsiniz.",
        ),
        array(
            'q' => "{$loc} oto sanayi sitelerinde bilgisayarlı arıza tespiti (OBD) yapılıyor mu?",
            'a' => "Evet, kayıtlı {$loc} ustalarımız son teknoloji OBD2 arıza tespit cihazları ile motor arıza lambası, ABS, ESP, airbag ve şanzıman elektriksel arızalarını dakikalar içinde tespit edip raporlamaktadır.",
        ),
    );

    $mainEntity = array();
    foreach ( $questions as $faq ) {
        $mainEntity[] = array(
            '@type'          => 'Question',
            'name'           => $faq['q'],
            'acceptedAnswer' => array(
                '@type' => 'Answer',
                'text'  => $faq['a'],
            ),
        );
    }

    return array(
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $mainEntity,
    );
}

/**
 * 7. LİSTELEME VE ARŞİV SAYFALARI İÇİN ITEMLIST SCHEMA
 * Şehir veya arama sayfalarında listelenen ustaları Google indeksine toplu tanıtır.
 */
function ototamir_get_archive_itemlist_schema() {
    global $wp_query;
    if ( ! $wp_query || empty( $wp_query->posts ) ) {
        return null;
    }

    $items = array();
    $position = 1;

    foreach ( $wp_query->posts as $p ) {
        if ( $p->post_type !== 'mechanic' ) continue;
        
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => get_the_title( $p->ID ),
            'url'      => get_permalink( $p->ID ),
        );

        if ( $position > 20 ) break; // En fazla ilk 20 ustayı şemaya ekle (hafiflik için)
    }

    if ( empty( $items ) ) {
        return null;
    }

    return array(
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'itemListElement' => $items,
    );
}

/**
 * 8. BLOG YAZILARI İÇİN ARTICLE / BLOGPOSTING SCHEMA
 */
function ototamir_get_single_article_schema( $post_id ) {
    $author_id = get_post_field( 'post_author', $post_id );
    $author_name = get_the_author_meta( 'display_name', $author_id ) ?: 'OtoTamirciBul Editör Ekibi';
    $image = has_post_thumbnail( $post_id ) ? get_the_post_thumbnail_url( $post_id, 'large' ) : get_template_directory_uri() . '/assets/images/logo-main.png';

    return array(
        '@context'         => 'https://schema.org',
        '@type'            => 'BlogPosting',
        'mainEntityOfPage' => array(
            '@type' => 'WebPage',
            '@id'   => get_permalink( $post_id ),
        ),
        'headline'         => get_the_title( $post_id ),
        'image'            => array( esc_url( $image ) ),
        'datePublished'    => get_the_date( 'c', $post_id ),
        'dateModified'     => get_the_modified_date( 'c', $post_id ),
        'author'           => array(
            '@type' => 'Person',
            'name'  => $author_name,
        ),
        'publisher'        => array(
            '@type' => 'Organization',
            'name'  => 'OtoTamirciBul',
            'logo'  => array(
                '@type' => 'ImageObject',
                'url'   => get_template_directory_uri() . '/assets/images/logo-main.png',
            ),
        ),
        'description'      => wp_trim_words( strip_tags( get_the_excerpt( $post_id ) ), 30 ),
    );
}

/**
 * 9. TÜM SCHEMA.ORG JSON-LD ZENGİN VERİLERİNİ <head> İÇİNE ENJEKTE ET
 */
function ototamir_render_all_json_ld_schemas() {
    $schemas = array();

    // Popüler SEO eklentileri (Yoast, Rank Math vb.) varsa breadcrumb/article basımını onlara bırakabiliriz
    $has_seo_plugin = defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'AIOSEO_VERSION' );

    // 1. Breadcrumbs Schema (Her sayfada bas)
    if ( ! $has_seo_plugin ) {
        $bc_items = ototamir_get_breadcrumb_schema_items();
        if ( count( $bc_items ) > 1 ) {
            $schemas[] = array(
                '@context'        => 'https://schema.org',
                '@type'           => 'BreadcrumbList',
                'itemListElement' => $bc_items,
            );
        }
    }

    // 2. Tekil Usta Sayfası (AutoRepair + FAQ)
    if ( is_singular( 'mechanic' ) ) {
        $post_id = get_the_ID();
        $schemas[] = ototamir_get_single_autorepair_schema( $post_id );

        // Usta Sıkça Sorulan Sorular Şeması
        $title = get_the_title( $post_id );
        $phone = get_post_meta( $post_id, '_mechanic_phone', true );
        $address = get_post_meta( $post_id, '_mechanic_address', true );
        $brand_terms = wp_get_post_terms( $post_id, 'car_brand' );
        $brand_names = ( $brand_terms && ! is_wp_error( $brand_terms ) ) ? wp_list_pluck( $brand_terms, 'name' ) : array();

        $schemas[] = array(
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => array(
                array(
                    '@type'          => 'Question',
                    'name'           => "{$title} hangi araç markalarına bakar?",
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text'  => ! empty( $brand_names ) ? "{$title}, başta " . implode( ', ', array_slice( $brand_names, 0, 5 ) ) . " olmak üzere ilgili araçların bakım, onarım ve arıza tespit işlemlerini gerçekleştirmektedir." : "{$title} tüm binek ve hafif ticari araçların periyodik bakım ve mekanik tamir işlemlerini yapmaktadır.",
                    ),
                ),
                array(
                    '@type'          => 'Question',
                    'name'           => "{$title} nerede, nasıl gidilir?",
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text'  => "İşletme adresi: " . ( $address ?: 'Belirtilen sanayi sitesi / ilçe' ) . ". Sayfamızdaki Google Haritalar Yol Tarifi butonu üzerinden tek tıkla navigasyon rotası oluşturabilirsiniz.",
                    ),
                ),
                array(
                    '@type'          => 'Question',
                    'name'           => "İletişim ve servis randevusu için telefon numarası nedir?",
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text'  => "{$title} işletmesine doğrudan " . ( $phone ?: 'sitemizdeki iletişim butonundan' ) . " numarasını arayarak ulaşabilir ve randevu oluşturabilirsiniz.",
                    ),
                ),
            ),
        );
    }

    // 3. Şehir / Kategori Arşivi (FAQPage + ItemList)
    if ( is_post_type_archive( 'mechanic' ) || is_tax( array( 'mechanic_city', 'mechanic_district', 'service_type', 'car_brand' ) ) ) {
        $geo = ototamir_detect_page_geo();
        if ( ! empty( $geo['city'] ) ) {
            $schemas[] = ototamir_get_city_faq_schema( $geo['city'], $geo['district'] );
        }

        $item_list = ototamir_get_archive_itemlist_schema();
        if ( $item_list ) {
            $schemas[] = $item_list;
        }
    }

    // 4. Tekil Blog Yazısı
    if ( is_singular( 'post' ) && ! $has_seo_plugin ) {
        $schemas[] = ototamir_get_single_article_schema( get_the_ID() );
    }

    if ( empty( $schemas ) ) {
        return;
    }

    ?>
    <!-- ================================================================= -->
    <!-- Google & Yapay Zeka (GEO) Schema.org Zengin Sonuç Paketleri (JSON-LD) -->
    <!-- ================================================================= -->
    <script type="application/ld+json">
    <?php echo wp_json_encode( count( $schemas ) === 1 ? $schemas[0] : $schemas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ); ?>
    </script>
    <?php
}
add_action( 'wp_head', 'ototamir_render_all_json_ld_schemas', 3 );
