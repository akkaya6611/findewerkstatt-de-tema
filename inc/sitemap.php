<?php
/**
 * OtoTamirciBul - Gelişmiş XML Site Haritası (Sitemap Engine) Modülü
 * 
 * Özellikler:
 * 1. Evrensel /sitemap.xml Dizin Haritası (Sitemap Index)
 * 2. Bölümlendirilmiş Alt Haritalar:
 *    - sitemap-mechanics.xml (Usta Profilleri + Google Image Görselleri)
 *    - sitemap-cities.xml (81 İl ve İlçe Sayfaları)
 *    - sitemap-services.xml (12 Hizmet Kategorisi ve Araç Markaları)
 *    - sitemap-pages.xml (Statik Sayfalar & Özel Landing Pageler)
 *    - sitemap-posts.xml (Blog Rehber Yazıları)
 * 3. Google Image Sitemap Standartları (<image:image>)
 * 4. Tarayıcı Dostu XSLT Şablon Desteği (Human-Readable UI)
 * 5. robots.txt Otomatik Entegrasyonu
 * 
 * @package OtoTamirciBul
 * @version 1.4.18
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OtoTamir_Sitemap {

    /**
     * Modülü Başlat
     */
    public static function init() {
        // Popüler SEO eklentileri (Yoast, Rank Math vb.) varsa çakışmayı önle
        if ( defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'AIOSEO_VERSION' ) ) {
            return;
        }

        // Rewrite kuralları & Query Değişkenleri
        add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ), 5 );
        add_filter( 'query_vars', array( __CLASS__, 'add_query_vars' ) );

        // Sitemap İsteği Yakalama (Template Redirect & URL fallback)
        add_action( 'template_redirect', array( __CLASS__, 'handle_sitemap_request' ), 1 );

        // Otomatik Rewrite Flush (Güncelleme sonrası ilk seferde)
        add_action( 'init', array( __CLASS__, 'maybe_flush_rewrites' ), 99 );

        // Usta kaydedildiğinde veya silindiğinde sitemap önbelleğini temizle
        add_action( 'save_post_mechanic', array( __CLASS__, 'clear_sitemap_cache' ) );
        add_action( 'deleted_post', array( __CLASS__, 'clear_sitemap_cache' ) );
    }

    public static function clear_sitemap_cache() {
        delete_transient( 'ototamir_sitemap_mechanics_xml' );
    }

    /**
     * Rewrite Kuralları Ekle
     */
    public static function add_rewrite_rules() {
        add_rewrite_rule( '^sitemap\.xml$', 'index.php?ototamir_sitemap=index', 'top' );
        add_rewrite_rule( '^sitemap-([a-z0-9_-]+)\.xml$', 'index.php?ototamir_sitemap=$matches[1]', 'top' );
    }

    /**
     * Query Değişkeni Tanımla
     */
    public static function add_query_vars( $vars ) {
        $vars[] = 'ototamir_sitemap';
        return $vars;
    }

    /**
     * Tek seferlik rewrite flush
     */
    public static function maybe_flush_rewrites() {
        if ( get_option( 'ototamir_sitemap_flush_v1416' ) !== 'yes' ) {
            flush_rewrite_rules( false );
            update_option( 'ototamir_sitemap_flush_v1416', 'yes' );
        }
    }

    /**
     * Sitemap İsteğini İşle ve XML Olarak Bas
     */
    public static function handle_sitemap_request() {
        $type = get_query_var( 'ototamir_sitemap' );

        // Rewrite henüz tetiklenmediyse URL kontrolü ile fallback yap
        if ( empty( $type ) ) {
            $req_uri = $_SERVER['REQUEST_URI'] ?? '';
            $path = trim( parse_url( $req_uri, PHP_URL_PATH ), '/' );
            if ( $path === 'sitemap.xml' ) {
                $type = 'index';
            } elseif ( preg_match( '/^sitemap-([a-z0-9_-]+)\.xml$/', $path, $m ) ) {
                $type = $m[1];
            }
        }

        if ( empty( $type ) ) {
            return;
        }

        // LiteSpeed Cache'e sitemap'i önbelleğe almamasını ve eski önbelleği temizlemesini bildir
        if ( ! defined( 'LSCACHE_NO_CACHE' ) ) {
            define( 'LSCACHE_NO_CACHE', true );
        }

        // HTTP Başlıkları (Google Search Console'un doğrudan ve hatasız okuması için sade ve standart XML)
        header( 'Content-Type: application/xml; charset=UTF-8' );
        header( 'X-Robots-Tag: all', true );
        header( 'Cache-Control: no-cache, no-store, must-revalidate', true );
        header( 'Pragma: no-cache', true );
        header( 'X-LiteSpeed-Cache-Control: no-cache', true );
        header( 'X-LiteSpeed-Purge: /sitemap.xml,/sitemap-mechanics.xml,/sitemap-cities.xml,/sitemap-services.xml,/sitemap-brands.xml,/sitemap-pages.xml,/sitemap-posts.xml', false );

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

        switch ( $type ) {
            case 'index':
                self::render_sitemap_index();
                break;
            case 'mechanics':
                self::render_mechanics_sitemap();
                break;
            case 'cities':
                self::render_cities_sitemap();
                break;
            case 'services':
                self::render_services_sitemap();
                break;
            case 'brands':
                self::render_brands_sitemap();
                break;
            case 'pages':
                self::render_pages_sitemap();
                break;
            case 'posts':
                self::render_posts_sitemap();
                break;
            default:
                status_header( 404 );
                echo '<error>Sitemap not found</error>';
                break;
        }

        exit;
    }

    /**
     * 1. ANA DİZİN HARİTASI (SITEMAP INDEX)
     */
    private static function render_sitemap_index() {
        global $wpdb;

        // En son usta güncellenme tarihi
        $mech_lastmod = $wpdb->get_var( "SELECT post_modified_gmt FROM {$wpdb->posts} WHERE post_type = 'mechanic' AND post_status = 'publish' ORDER BY post_modified_gmt DESC LIMIT 1" );
        $post_lastmod = $wpdb->get_var( "SELECT post_modified_gmt FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish' ORDER BY post_modified_gmt DESC LIMIT 1" );
        $page_lastmod = $wpdb->get_var( "SELECT post_modified_gmt FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status = 'publish' ORDER BY post_modified_gmt DESC LIMIT 1" );

        $now_w3c = gmdate( 'Y-m-d\TH:i:s+00:00' );
        $mech_date = $mech_lastmod ? gmdate( 'Y-m-d\TH:i:s+00:00', strtotime( $mech_lastmod ) ) : $now_w3c;
        $post_date = $post_lastmod ? gmdate( 'Y-m-d\TH:i:s+00:00', strtotime( $post_lastmod ) ) : $now_w3c;
        $page_date = $page_lastmod ? gmdate( 'Y-m-d\TH:i:s+00:00', strtotime( $page_lastmod ) ) : $now_w3c;

        $sitemaps = array(
            array(
                'loc'     => home_url( '/sitemap-mechanics.xml' ),
                'lastmod' => $mech_date,
            ),
            array(
                'loc'     => home_url( '/sitemap-cities.xml' ),
                'lastmod' => $mech_date,
            ),
            array(
                'loc'     => home_url( '/sitemap-services.xml' ),
                'lastmod' => $mech_date,
            ),
            array(
                'loc'     => home_url( '/sitemap-brands.xml' ),
                'lastmod' => $mech_date,
            ),
            array(
                'loc'     => home_url( '/sitemap-pages.xml' ),
                'lastmod' => $page_date,
            ),
            array(
                'loc'     => home_url( '/sitemap-posts.xml' ),
                'lastmod' => $post_date,
            ),
        );

        echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ( $sitemaps as $s ) {
            echo "  <sitemap>\n";
            echo '    <loc>' . esc_url( $s['loc'] ) . "</loc>\n";
            echo '    <lastmod>' . esc_html( $s['lastmod'] ) . "</lastmod>\n";
            echo "  </sitemap>\n";
        }
        echo '</sitemapindex>';
    }

    /**
     * 2. USTA PROFİLLERİ HARİTASI (GOOGLE IMAGE DESTEKLİ)
     */
    private static function render_mechanics_sitemap() {
        // 1. Transient Önbellek Kontrolü (6 Saat) - Zaman aşımını (timeout) sıfıra indirir
        $cached_xml = get_transient( 'ototamir_sitemap_mechanics_xml' );
        if ( false !== $cached_xml && ! empty( $cached_xml ) ) {
            echo $cached_xml;
            return;
        }

        global $wpdb;

        $posts = $wpdb->get_results( "
            SELECT ID, post_title, post_modified_gmt 
            FROM {$wpdb->posts} 
            WHERE post_type = 'mechanic' AND post_status = 'publish' 
            ORDER BY post_modified_gmt DESC 
            LIMIT 5000
        " );

        ob_start();
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        foreach ( $posts as $p ) {
            $loc = get_permalink( $p->ID );
            $lastmod = gmdate( 'Y-m-d\TH:i:s+00:00', strtotime( $p->post_modified_gmt ) );
            
            // Görsel tespiti
            $image_url = get_post_meta( $p->ID, '_custom_mechanic_image', true );
            if ( empty( $image_url ) && has_post_thumbnail( $p->ID ) ) {
                $image_url = get_the_post_thumbnail_url( $p->ID, 'large' );
            }

            echo "  <url>\n";
            echo '    <loc>' . esc_url( $loc ) . "</loc>\n";
            echo '    <lastmod>' . esc_html( $lastmod ) . "</lastmod>\n";
            echo "    <changefreq>daily</changefreq>\n";
            echo "    <priority>0.9</priority>\n";

            if ( ! empty( $image_url ) ) {
                // Protocol-relative URL'leri düzelt (//lh3.googleusercontent.com → https://lh3.googleusercontent.com)
                if ( strpos( $image_url, '//' ) === 0 ) {
                    $image_url = 'https:' . $image_url;
                }
                // http:// → https:// yükselt
                if ( strpos( $image_url, 'http://' ) === 0 ) {
                    $image_url = 'https://' . substr( $image_url, 7 );
                }
                echo "    <image:image>\n";
                echo '      <image:loc>' . esc_url( $image_url ) . "</image:loc>\n";
                echo '      <image:title>' . esc_html( $p->post_title ) . "</image:title>\n";
                echo "    </image:image>\n";
            }

            echo "  </url>\n";
        }

        echo '</urlset>';
        $generated_xml = ob_get_clean();

        // 6 saat boyunca önbellekte sakla
        set_transient( 'ototamir_sitemap_mechanics_xml', $generated_xml, 6 * HOUR_IN_SECONDS );
        echo $generated_xml;
    }

    /**
     * 3. 81 İL VE AKTİF İLÇELER HARİTASI
     */
    private static function render_cities_sitemap() {
        global $wpdb;

        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Tüm İller
        $cities = get_terms( array(
            'taxonomy'   => 'mechanic_city',
            'hide_empty' => false,
        ) );

        $now = gmdate( 'Y-m-d\TH:i:s+00:00' );
        $rendered_urls = array();

        // 1. 81 İl Sayfası
        if ( ! is_wp_error( $cities ) && ! empty( $cities ) ) {
            foreach ( $cities as $c ) {
                $url = home_url( '/ustalar/?mechanic_city=' . $c->slug );
                $rendered_urls[ $url ] = true;
                echo "  <url>\n";
                echo '    <loc>' . esc_url( $url ) . "</loc>\n";
                echo '    <lastmod>' . esc_html( $now ) . "</lastmod>\n";
                echo "    <changefreq>weekly</changefreq>\n";
                echo "    <priority>0.9</priority>\n";
                echo "  </url>\n";
            }
        }

        // 2. 81 İl ve ~970 Resmi İlçenin Tamamı
        if ( class_exists( 'OtoTamir_All_Cities_Migration' ) ) {
            $all_districts = OtoTamir_All_Cities_Migration::get_all_districts();
            foreach ( $all_districts as $c_name => $d_list ) {
                $c_slug = sanitize_title( $c_name );
                foreach ( $d_list as $d_name ) {
                    $url = home_url( '/ustalar/?mechanic_city=' . $c_slug . '&mechanic_district=' . urlencode( $d_name ) );
                    if ( ! isset( $rendered_urls[ $url ] ) ) {
                        $rendered_urls[ $url ] = true;
                        echo "  <url>\n";
                        echo '    <loc>' . esc_url( $url ) . "</loc>\n";
                        echo '    <lastmod>' . esc_html( $now ) . "</lastmod>\n";
                        echo "    <changefreq>weekly</changefreq>\n";
                        echo "    <priority>0.8</priority>\n";
                        echo "  </url>\n";
                    }
                }
            }
        } else {
            // Fallback: Aktif İlçeler (En az 1 ustası olan ilçeler)
            $districts = $wpdb->get_results( "
                SELECT DISTINCT t.name as dist_name, t_city.slug as city_slug
                FROM {$wpdb->terms} t
                JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id AND tt.taxonomy = 'mechanic_district'
                JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
                JOIN {$wpdb->posts} p ON tr.object_id = p.ID AND p.post_type = 'mechanic' AND p.post_status = 'publish'
                LEFT JOIN {$wpdb->term_relationships} tr_city ON p.ID = tr_city.object_id
                LEFT JOIN {$wpdb->term_taxonomy} tt_city ON tr_city.term_taxonomy_id = tt_city.term_taxonomy_id AND tt_city.taxonomy = 'mechanic_city'
                LEFT JOIN {$wpdb->terms} t_city ON tt_city.term_id = t_city.term_id
                WHERE t_city.slug IS NOT NULL
                LIMIT 1000
            " );

            if ( ! empty( $districts ) ) {
                foreach ( $districts as $d ) {
                    $url = home_url( '/ustalar/?mechanic_city=' . $d->city_slug . '&mechanic_district=' . urlencode( $d->dist_name ) );
                    echo "  <url>\n";
                    echo '    <loc>' . esc_url( $url ) . "</loc>\n";
                    echo '    <lastmod>' . esc_html( $now ) . "</lastmod>\n";
                    echo "    <changefreq>weekly</changefreq>\n";
                    echo "    <priority>0.8</priority>\n";
                    echo "  </url>\n";
                }
            }
        }

        echo '</urlset>';
    }

    /**
     * 4. HİZMET KATEGORİLERİ VE ARAÇ MARKALARI HARİTASI
     */
    private static function render_services_sitemap() {
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $now = gmdate( 'Y-m-d\TH:i:s+00:00' );

        // 12 Temel Hizmet
        $services = get_terms( array(
            'taxonomy'   => 'service_type',
            'hide_empty' => false,
        ) );

        if ( ! is_wp_error( $services ) && ! empty( $services ) ) {
            foreach ( $services as $s ) {
                $url = home_url( '/ustalar/?service_type=' . $s->slug );
                echo "  <url>\n";
                echo '    <loc>' . esc_url( $url ) . "</loc>\n";
                echo '    <lastmod>' . esc_html( $now ) . "</lastmod>\n";
                echo "    <changefreq>weekly</changefreq>\n";
                echo "    <priority>0.8</priority>\n";
                echo "  </url>\n";
            }
        }

        // Araç Markaları
        $brands = get_terms( array(
            'taxonomy'   => 'car_brand',
            'hide_empty' => true,
        ) );

        if ( ! is_wp_error( $brands ) && ! empty( $brands ) ) {
            foreach ( $brands as $b ) {
                $url = home_url( '/ustalar/?car_brand=' . $b->slug );
                echo "  <url>\n";
                echo '    <loc>' . esc_url( $url ) . "</loc>\n";
                echo '    <lastmod>' . esc_html( $now ) . "</lastmod>\n";
                echo "    <changefreq>weekly</changefreq>\n";
                echo "    <priority>0.8</priority>\n";
                echo "  </url>\n";
            }
        }

        echo '</urlset>';
    }

    /**
     * 4.1. ARAÇ MARKA VE MODEL LANDING SAYFALARI HARİTASI
     */
    private static function render_brands_sitemap() {
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $now = gmdate( 'Y-m-d\TH:i:s+00:00' );

        // Tüm Ana Markalar (Parent = 0)
        $parent_brands = get_terms( array(
            'taxonomy'   => 'car_brand',
            'parent'     => 0,
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ) );

        if ( ! is_wp_error( $parent_brands ) && ! empty( $parent_brands ) ) {
            foreach ( $parent_brands as $pb ) {
                $url = home_url( '/marka/' . $pb->slug );
                echo "  <url>\n";
                echo '    <loc>' . esc_url( $url ) . "</loc>\n";
                echo '    <lastmod>' . esc_html( $now ) . "</lastmod>\n";
                echo "    <changefreq>weekly</changefreq>\n";
                echo "    <priority>0.85</priority>\n";
                echo "  </url>\n";

                // Alt Modeller
                $models = get_terms( array(
                    'taxonomy'   => 'car_brand',
                    'parent'     => $pb->term_id,
                    'hide_empty' => false,
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                ) );

                if ( ! is_wp_error( $models ) && ! empty( $models ) ) {
                    foreach ( $models as $m ) {
                        $m_url = home_url( '/marka/' . $pb->slug . '/' . $m->slug );
                        echo "  <url>\n";
                        echo '    <loc>' . esc_url( $m_url ) . "</loc>\n";
                        echo '    <lastmod>' . esc_html( $now ) . "</lastmod>\n";
                        echo "    <changefreq>weekly</changefreq>\n";
                        echo "    <priority>0.80</priority>\n";
                        echo "  </url>\n";
                    }
                }
            }
        }

        echo '</urlset>';
    }

    /**
     * 5. STATİK SAYFALAR VE ÖZEL LANDING SAYFALARI HARİTASI
     */
    private static function render_pages_sitemap() {
        global $wpdb;

        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $now = gmdate( 'Y-m-d\TH:i:s+00:00' );

        // 1. Ana Sayfa (En Yüksek Öncelik)
        echo "  <url>\n";
        echo '    <loc>' . esc_url( home_url( '/' ) ) . "</loc>\n";
        echo '    <lastmod>' . esc_html( $now ) . "</lastmod>\n";
        echo "    <changefreq>daily</changefreq>\n";
        echo "    <priority>1.0</priority>\n";
        echo "  </url>\n";

        // 2. Ustalar Arama Sayfası
        echo "  <url>\n";
        echo '    <loc>' . esc_url( home_url( '/ustalar' ) ) . "</loc>\n";
        echo '    <lastmod>' . esc_html( $now ) . "</lastmod>\n";
        echo "    <changefreq>daily</changefreq>\n";
        echo "    <priority>0.9</priority>\n";
        echo "  </url>\n";

        // 3. Özel Modül ve Hizmet Sayfaları
        $special_pages = array(
            '/ariza-tespiti'         => array( 'p' => '0.9', 'f' => 'weekly' ),
            '/nobetci-oto-tamirciler'=> array( 'p' => '0.9', 'f' => 'daily' ),
            '/hizmetler'             => array( 'p' => '0.8', 'f' => 'weekly' ),
            '/iller'                 => array( 'p' => '0.8', 'f' => 'weekly' ),
            '/blog'                  => array( 'p' => '0.8', 'f' => 'daily' ),
            '/hakkimizda'            => array( 'p' => '0.6', 'f' => 'monthly' ),
            '/iletisim'              => array( 'p' => '0.7', 'f' => 'monthly' ),
            '/sss'                   => array( 'p' => '0.7', 'f' => 'monthly' ),
            '/usta-ekle'             => array( 'p' => '0.8', 'f' => 'monthly' ),
        );

        foreach ( $special_pages as $slug => $meta ) {
            echo "  <url>\n";
            echo '    <loc>' . esc_url( home_url( $slug ) ) . "</loc>\n";
            echo '    <lastmod>' . esc_html( $now ) . "</lastmod>\n";
            echo '    <changefreq>' . esc_html( $meta['f'] ) . "</changefreq>\n";
            echo '    <priority>' . esc_html( $meta['p'] ) . "</priority>\n";
            echo "  </url>\n";
        }

        // 4. Veritabanındaki Diğer Yayınlanmış Sayfalar
        $pages = $wpdb->get_results( "
            SELECT ID, post_modified_gmt 
            FROM {$wpdb->posts} 
            WHERE post_type = 'page' AND post_status = 'publish' 
            ORDER BY post_modified_gmt DESC
        " );

        if ( ! empty( $pages ) ) {
            foreach ( $pages as $page ) {
                $permalink = get_permalink( $page->ID );
                $lastmod = gmdate( 'Y-m-d\TH:i:s+00:00', strtotime( $page->post_modified_gmt ) );
                
                // Zaten eklenen sayfaları atla
                $path = parse_url( $permalink, PHP_URL_PATH );
                if ( in_array( rtrim($path, '/'), array_keys( $special_pages ) ) || rtrim($path, '/') === '' ) {
                    continue;
                }

                echo "  <url>\n";
                echo '    <loc>' . esc_url( $permalink ) . "</loc>\n";
                echo '    <lastmod>' . esc_html( $lastmod ) . "</lastmod>\n";
                echo "    <changefreq>monthly</changefreq>\n";
                echo "    <priority>0.6</priority>\n";
                echo "  </url>\n";
            }
        }

        echo '</urlset>';
    }

    /**
     * 6. BLOG REHBER YAZILARI HARİTASI
     */
    private static function render_posts_sitemap() {
        global $wpdb;

        $posts = $wpdb->get_results( "
            SELECT ID, post_title, post_modified_gmt 
            FROM {$wpdb->posts} 
            WHERE post_type = 'post' AND post_status = 'publish' 
            ORDER BY post_modified_gmt DESC 
            LIMIT 2000
        " );

        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        foreach ( $posts as $p ) {
            $loc = get_permalink( $p->ID );
            $lastmod = gmdate( 'Y-m-d\TH:i:s+00:00', strtotime( $p->post_modified_gmt ) );
            $thumb = has_post_thumbnail( $p->ID ) ? get_the_post_thumbnail_url( $p->ID, 'large' ) : '';

            echo "  <url>\n";
            echo '    <loc>' . esc_url( $loc ) . "</loc>\n";
            echo '    <lastmod>' . esc_html( $lastmod ) . "</lastmod>\n";
            echo "    <changefreq>monthly</changefreq>\n";
            echo "    <priority>0.7</priority>\n";

            if ( ! empty( $thumb ) ) {
                echo "    <image:image>\n";
                echo '      <image:loc>' . esc_url( $thumb ) . "</image:loc>\n";
                echo '      <image:title>' . esc_html( $p->post_title ) . "</image:title>\n";
                echo "    </image:image>\n";
            }

            echo "  </url>\n";
        }

        echo '</urlset>';
    }
}

// Modülü Başlat
OtoTamir_Sitemap::init();
