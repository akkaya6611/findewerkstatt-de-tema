<?php
/**
 * OtoTamirciBul - Türkiye Araç Marka & Model Hiyerarşik Veritabanı Migrasyonu
 * 
 * Özellikler:
 * 1. 35+ Türkiye genelinde popüler otomotiv markasını `car_brand` taksonomisinde ana kategori (parent) olarak kaydeder.
 * 2. Her markanın en popüler 300+ modelini (Örn: Chevrolet -> Cruze, Fiat -> Egea, VW -> Golf) alt kategori (child) olarak bağlar.
 * 3. İdempotent: `ototamir_brands_models_v1436` seçeneğiyle kontrol edilir, tekrar çalıştırıldığında mükerrer kayıt üretmez.
 * 4. Mevcut ilanların marka ilişkilerini bozmaz, mevcut terimleri korur ve zenginleştirir.
 * 
 * @package OtoTamirciBul
 * @version 1.4.36
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OtoTamir_Brands_Models_Migration {

    const OPTION_NAME = 'ototamir_brands_models_v1436_done';

    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_rewrites' ), 20 );
        add_action( 'init', array( __CLASS__, 'maybe_run_migration' ), 25 );
        add_action( 'template_redirect', array( __CLASS__, 'handle_marka_redirects' ), 1 );
    }

    /**
     * Marka ve Model URL Rewrite Kuralları
     */
    public static function register_rewrites() {
        add_rewrite_rule( '^marka/([^/]+)/([^/]+)/page/([0-9]+)/?$', 'index.php?car_brand=$matches[2]&paged=$matches[3]', 'top' );
        add_rewrite_rule( '^marka/([^/]+)/([^/]+)/?$', 'index.php?car_brand=$matches[2]', 'top' );
        add_rewrite_rule( '^marka/(.+?)/?$', 'index.php?car_brand=$matches[1]', 'top' );

        if ( ! get_option( 'ototamir_marka_rewrites_v1438_flushed' ) ) {
            flush_rewrite_rules( false );
            update_option( 'ototamir_marka_rewrites_v1438_flushed', 1 );
        }
    }

    /**
     * Marka ve Modeller Sözlüğü
     */
    public static function get_brands_and_models() {
        return array(
            'Chevrolet' => array(
                'Cruze', 'Aveo', 'Captiva', 'Lacetti', 'Kalos', 'Spark', 'Trax', 'Epica', 'Camaro', 'Rezzo'
            ),
            'Volkswagen' => array(
                'Golf', 'Passat', 'Polo', 'Caddy', 'Tiguan', 'Transporter', 'Jetta', 'Crafter', 'Arteon', 'Touareg', 'T-Roc', 'Amarok', 'Taigo', 'T-Cross', 'Scirocco', 'Bora', 'Beetle', 'Caravelle'
            ),
            'Renault' => array(
                'Clio', 'Megane', 'Symbol', 'Fluence', 'Kangoo', 'Trafic', 'Master', 'Kadjar', 'Captur', 'Talisman', 'Austral', 'Twingo', 'Koleos', 'Scenic', 'Laguna', 'Modus', 'Express', 'Rafale'
            ),
            'Fiat' => array(
                'Egea', 'Doblo', 'Fiorino', 'Linea', 'Punto', 'Ducato', 'Palio', 'Albea', 'Bravo', 'Panda', '500', '500X', '500L', 'Scudo', 'Uno', 'Tempra', 'Tipo', 'Marea', 'Stilo', 'Freemont'
            ),
            'Ford' => array(
                'Focus', 'Fiesta', 'Transit', 'Tourneo Courier', 'Tourneo Connect', 'Tourneo Custom', 'Mondeo', 'Kuga', 'Puma', 'Ranger', 'B-Max', 'C-Max', 'S-Max', 'Ka', 'EcoSport', 'Taunus', 'Escort'
            ),
            'Toyota' => array(
                'Corolla', 'Yaris', 'Auris', 'C-HR', 'RAV4', 'Hilux', 'Avensis', 'Proace', 'Camry', 'Land Cruiser', 'Aygo', 'Prius', 'Verso', 'Corolla Cross'
            ),
            'Opel' => array(
                'Astra', 'Corsa', 'Insignia', 'Mokka', 'Crossland', 'Grandland', 'Vectra', 'Combo', 'Zafira', 'Meriva', 'Antara', 'Vivaro', 'Movano', 'Frontera', 'Omega', 'Tigra'
            ),
            'Peugeot' => array(
                '206', '207', '208', '301', '307', '308', '407', '408', '508', '2008', '3008', '5008', 'Partner', 'Rifter', 'Boxer', 'Expert', 'Bipper', '106', '306'
            ),
            'Hyundai' => array(
                'i10', 'i20', 'i30', 'Accent', 'Accent Blue', 'Accent Era', 'Elantra', 'Tucson', 'Santa Fe', 'Bayon', 'Kona', 'H-100', 'Staria', 'Getz', 'Matrix', 'ix35', 'Atos'
            ),
            'Honda' => array(
                'Civic', 'City', 'CR-V', 'HR-V', 'Jazz', 'Accord', 'Civic Type R', 'Prelude'
            ),
            'BMW' => array(
                '1 Serisi', '2 Serisi', '3 Serisi', '4 Serisi', '5 Serisi', '6 Serisi', '7 Serisi', 'X1', 'X2', 'X3', 'X4', 'X5', 'X6', 'X7', 'i4', 'iX', 'Z4'
            ),
            'Mercedes-Benz' => array(
                'A-Serisi', 'B-Serisi', 'C-Serisi', 'E-Serisi', 'S-Serisi', 'CLA', 'GLA', 'GLB', 'GLC', 'GLE', 'GLS', 'Vito', 'Sprinter', 'Citan', 'EQC', 'EQE', 'EQS', 'CLS', 'G-Serisi'
            ),
            'Audi' => array(
                'A1', 'A3', 'A4', 'A5', 'A6', 'A7', 'A8', 'Q2', 'Q3', 'Q4 e-tron', 'Q5', 'Q7', 'Q8', 'TT', 'e-tron'
            ),
            'Dacia' => array(
                'Duster', 'Sandero', 'Sandero Stepway', 'Logan', 'Lodgy', 'Dokker', 'Jogger', 'Spring', 'Solenza'
            ),
            'Skoda' => array(
                'Octavia', 'Superb', 'Fabia', 'Kodiaq', 'Karoq', 'Kamiq', 'Scala', 'Rapid', 'Roomster', 'Yeti', 'Felicia'
            ),
            'Seat' => array(
                'Leon', 'Ibiza', 'Arona', 'Ateca', 'Tarraco', 'Toledo', 'Altea', 'Cordoba'
            ),
            'Nissan' => array(
                'Qashqai', 'Micra', 'Juke', 'X-Trail', 'Navara', 'Note', 'Primera', 'Almera', 'Pulsar', 'Terrano', 'Patrol'
            ),
            'Kia' => array(
                'Rio', 'Ceed', 'Sportage', 'Stonic', 'Picanto', 'Cerato', 'Bongo', 'Sorento', 'XCeed', 'EV6', 'Niro', 'Carens', 'Carnival'
            ),
            'Citroen' => array(
                'C-Elysee', 'C3', 'C3 Aircross', 'C4', 'C4 Cactus', 'C4 X', 'C5', 'C5 Aircross', 'C5 X', 'Berlingo', 'Jumpy', 'Nemo', 'C1', 'C2', 'Saxo', 'Xsara'
            ),
            'Volvo' => array(
                'S60', 'S90', 'XC40', 'XC60', 'XC90', 'V40', 'V60', 'V90', 'C40', 'EX30', 'S40', 'V50'
            ),
            'Tofaş' => array(
                'Şahin', 'Doğan', 'Kartal', 'Murat 124', 'Murat 131', 'Serçe'
            ),
            'Togg' => array(
                'T10X', 'T10F'
            ),
            'Chery' => array(
                'Omoda 5', 'Tiggo 7 Pro', 'Tiggo 8 Pro', 'Tiggo 4 Pro'
            ),
            'MG' => array(
                'ZS', 'HS', 'MG4', 'Cyberster'
            ),
            'BYD' => array(
                'Atto 3', 'Seal', 'Dolphin', 'Han', 'Tang'
            ),
            'Cupra' => array(
                'Formentor', 'Leon', 'Ateca', 'Born'
            ),
            'Suzuki' => array(
                'Swift', 'Vitara', 'Jimny', 'S-Cross', 'Baleno', 'Alto', 'Grand Vitara'
            ),
            'Mitsubishi' => array(
                'L200', 'ASX', 'Eclipse Cross', 'Outlander', 'Colt', 'Pajero', 'Carisma', 'Lancer'
            ),
            'Mazda' => array(
                'Mazda 3', 'Mazda 6', 'CX-3', 'CX-5', 'CX-30', 'MX-5', '323', '626'
            ),
            'Subaru' => array(
                'XV', 'Forester', 'Impreza', 'Outback', 'Crosstrek'
            ),
            'Alfa Romeo' => array(
                'Giulietta', 'Giulia', 'Stelvio', 'Tonale', '147', '156', '159', 'Mito'
            ),
            'Jeep' => array(
                'Renegade', 'Compass', 'Cherokee', 'Grand Cherokee', 'Wrangler', 'Avenger'
            ),
            'Land Rover' => array(
                'Range Rover', 'Range Rover Sport', 'Range Rover Evoque', 'Range Rover Velar', 'Discovery', 'Discovery Sport', 'Defender'
            ),
            'Mini' => array(
                'Cooper', 'Countryman', 'Clubman', 'One'
            ),
            'DS Automobiles' => array(
                'DS 3', 'DS 4', 'DS 7', 'DS 9'
            ),
            'Porsche' => array(
                'Cayenne', 'Macan', 'Panamera', 'Taycan', '911'
            ),
        );
    }

    /**
     * Veritabanı Migrasyonunu Çalıştır
     */
    public static function maybe_run_migration() {
        if ( get_option( self::OPTION_NAME ) ) {
            return;
        }

        if ( ! taxonomy_exists( 'car_brand' ) ) {
            return;
        }

        self::run_migration();
    }

    public static function run_migration() {
        $brands_and_models = self::get_brands_and_models();
        $total_brands = 0;
        $total_models = 0;

        foreach ( $brands_and_models as $brand_name => $models ) {
            // 1. Markayı (Parent) Ekle veya Bul
            $brand_term = get_term_by( 'name', $brand_name, 'car_brand' );
            if ( ! $brand_term ) {
                $inserted = wp_insert_term( $brand_name, 'car_brand', array(
                    'parent' => 0,
                    'slug'   => sanitize_title( $brand_name ),
                ) );
                if ( ! is_wp_error( $inserted ) ) {
                    $brand_id = $inserted['term_id'];
                    $total_brands++;
                } else {
                    continue;
                }
            } else {
                $brand_id = $brand_term->term_id;
                // Parent'ı 0 olduğundan emin ol
                if ( (int) $brand_term->parent !== 0 ) {
                    wp_update_term( $brand_id, 'car_brand', array( 'parent' => 0 ) );
                }
            }

            // 2. Modelleri (Child) Ekle veya Güncelle
            foreach ( $models as $model_name ) {
                $model_slug = sanitize_title( $brand_name . '-' . $model_name );
                $model_term = get_term_by( 'slug', $model_slug, 'car_brand' );
                
                if ( ! $model_term ) {
                    // İsimle de ara
                    $existing_by_name = get_terms( array(
                        'taxonomy'   => 'car_brand',
                        'name'       => $model_name,
                        'parent'     => $brand_id,
                        'hide_empty' => false,
                    ) );

                    if ( ! empty( $existing_by_name ) && ! is_wp_error( $existing_by_name ) ) {
                        $model_term = $existing_by_name[0];
                    }
                }

                if ( ! $model_term ) {
                    $m_insert = wp_insert_term( $model_name, 'car_brand', array(
                        'parent' => $brand_id,
                        'slug'   => $model_slug,
                    ) );
                    if ( ! is_wp_error( $m_insert ) ) {
                        $total_models++;
                    }
                } else {
                    // Parent ilişkisini garantiye al
                    if ( (int) $model_term->parent !== (int) $brand_id ) {
                        wp_update_term( $model_term->term_id, 'car_brand', array(
                            'parent' => $brand_id,
                        ) );
                    }
                }
            }
        }

        update_option( self::OPTION_NAME, array(
            'time'         => time(),
            'total_brands' => $total_brands,
            'total_models' => $total_models,
            'version'      => '1.4.36',
        ) );

        clean_term_cache( '', 'car_brand' );
    }

    /**
     * Marka ve Model URL Akıllı Yönlendirme ve 404 Kurtarma (Self-Healing)
     */
    public static function handle_marka_redirects() {
        $uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
        $path = wp_parse_url( $uri, PHP_URL_PATH );
        if ( ! $path || strpos( $path, '/marka/' ) === false ) {
            return;
        }

        // /marka/ sonrasındaki parçaları ayrıştır
        $parts = array_values( array_filter( explode( '/', trim( $path, '/' ) ) ) );
        $marka_index = array_search( 'marka', $parts );
        if ( $marka_index === false || ! isset( $parts[ $marka_index + 1 ] ) ) {
            return;
        }

        $brand_raw = $parts[ $marka_index + 1 ];
        $model_raw = isset( $parts[ $marka_index + 2 ] ) ? $parts[ $marka_index + 2 ] : '';

        // Eğer sayfalama, feed veya embed ise normal WP akışına bırak
        if ( in_array( $model_raw, array( 'feed', 'embed', 'page' ) ) ) {
            return;
        }

        $target_term = null;

        if ( ! empty( $model_raw ) ) {
            // Model çözümleme (Örn: "chevrolet cruze", "cruze", "chevrolet-cruze")
            $target_term = self::resolve_brand_or_model( $brand_raw, $model_raw );
        } else {
            // Sadece Marka çözümleme (Örn: "chevrolet")
            $target_term = get_term_by( 'slug', sanitize_title( urldecode( $brand_raw ) ), 'car_brand' );
            if ( ! $target_term ) {
                $target_term = get_term_by( 'name', urldecode( $brand_raw ), 'car_brand' );
            }
        }

        if ( $target_term && ! is_wp_error( $target_term ) ) {
            // Eğer bu marka/model için oluşturulmuş gerçek bir WordPress sayfası varsa doğrudan o sayfaya yönlendir
            $brand_term_name = $target_term->name;
            $model_term_name = '';
            if ( ! empty( $target_term->parent ) ) {
                $p_term = get_term( $target_term->parent, 'car_brand' );
                if ( $p_term && ! is_wp_error( $p_term ) ) {
                    $brand_term_name = $p_term->name;
                    $model_term_name = $target_term->name;
                }
            }

            $expected_page_slug = ! empty( $model_term_name )
                ? sanitize_title( "size en yakin {$brand_term_name} {$model_term_name} oto tamircileri" )
                : sanitize_title( "size en yakin {$brand_term_name} oto tamircileri" );

            $linked_page = get_page_by_path( $expected_page_slug );
            if ( $linked_page && $linked_page->post_status === 'publish' ) {
                $page_url = get_permalink( $linked_page->ID );
                $page_path = wp_parse_url( $page_url, PHP_URL_PATH );
                if ( rtrim( $path, '/' ) !== rtrim( $page_path, '/' ) ) {
                    wp_safe_redirect( $page_url, 301 );
                    exit;
                }
            }

            $canonical_url = get_term_link( $target_term, 'car_brand' );
            if ( ! is_wp_error( $canonical_url ) ) {
                $canonical_path = wp_parse_url( $canonical_url, PHP_URL_PATH );
                
                // Eğer mevcut URI canonical link ile tam eşleşmiyorsa (örn: boşluk içeriyor, cruze yerine chevrolet-cruze değilse) -> 301 Redirect
                if ( rtrim( $path, '/' ) !== rtrim( $canonical_path, '/' ) ) {
                    wp_safe_redirect( $canonical_url, 301 );
                    exit;
                }

                // Eğer 404 durumundaysa (çünkü sunucuda rewrite kuralları henüz yenilenmemiş olabilir) doğrudan archive şablonuna bağla
                if ( is_404() ) {
                    flush_rewrite_rules( false );
                    global $wp_query;
                    $wp_query->is_404            = false;
                    $wp_query->is_archive        = true;
                    $wp_query->is_tax            = true;
                    $wp_query->queried_object    = $target_term;
                    $wp_query->queried_object_id = $target_term->term_id;
                    $wp_query->set( 'car_brand', $target_term->slug );
                    status_header( 200 );
                    include get_template_directory() . '/archive.php';
                    exit;
                }
            }
        }
    }

    /**
     * Kullanıcı Girişini (Boşluklu, kısa model adı vb.) Marka/Model Taksonomisine Eşle
     */
    public static function resolve_brand_or_model( $brand_input, $model_input ) {
        $model_input = urldecode( $model_input );
        $brand_input = urldecode( $brand_input );

        $brand_slug = sanitize_title( $brand_input );
        $model_slug = sanitize_title( $model_input );

        // 1. Doğrudan slug araması (Örn: chevrolet-cruze)
        $term = get_term_by( 'slug', $model_slug, 'car_brand' );
        if ( $term ) {
            return $term;
        }

        // 2. Marka + Model birleşik slug araması (Örn: 'cruze' girildiğinde 'chevrolet-cruze')
        $combined_slug = sanitize_title( $brand_slug . '-' . $model_slug );
        $term = get_term_by( 'slug', $combined_slug, 'car_brand' );
        if ( $term ) {
            return $term;
        }

        // 3. Marka altındaki child terimler arasında isim veya slug eşleşmesi
        $brand_term = get_term_by( 'slug', $brand_slug, 'car_brand' );
        if ( ! $brand_term ) {
            $brand_term = get_term_by( 'name', $brand_input, 'car_brand' );
        }
        if ( $brand_term ) {
            $child_terms = get_terms( array(
                'taxonomy'   => 'car_brand',
                'parent'     => $brand_term->term_id,
                'hide_empty' => false,
            ) );
            if ( ! empty( $child_terms ) && ! is_wp_error( $child_terms ) ) {
                foreach ( $child_terms as $ct ) {
                    if ( strcasecmp( $ct->name, $model_input ) === 0 || 
                         strcasecmp( $ct->slug, $model_slug ) === 0 || 
                         strcasecmp( $ct->slug, $combined_slug ) === 0 ) {
                        return $ct;
                    }
                }
            }
        }

        return null;
    }
}

OtoTamir_Brands_Models_Migration::init();
