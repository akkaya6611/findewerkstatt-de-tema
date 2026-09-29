<?php
// FindeWerkstatt.de — Deutschland Geodatenbank, 16 Bundesländer, Top 100 Städte & Taxonomien
require_once get_template_directory() . '/inc/german-seeder-and-taxonomies.php';

// FindeWerkstatt.de — Deutsche Rechtssicherheit (DSGVO, Impressum § 5 DDG, Disclaimer)
require_once get_template_directory() . '/inc/german-legal-compliance.php';

// Marka & Model SEO Zeka Motoru
require_once get_template_directory() . '/inc/brand-model-seo-data.php';

// Gerçek İnsan Ziyaretçi Takip Motoru (Anti-Bot)
require_once get_template_directory() . '/inc/visitor-tracker.php';

// Konuma Göre Çekici & Acil Yol Yardım AJAX Motoru
require_once get_template_directory() . '/inc/ajax-tow-locator.php';

// FindeWerkstatt.de — Deutsche Werkstätten & Pannenhilfe Seeder
// require_once get_template_directory() . '/inc/german-workshops-migrator.php';

// Mükerrer (Duplicate) Usta & Servis Kayıtlarını Temizleme Modülü
require_once get_template_directory() . '/inc/mechanic-deduplicator.php';

// Güvenlik Başlıkları, HTTPS Zorlama & Giriş/Kayıt Önbellek Koruması
require_once get_template_directory() . '/inc/security-headers.php';

// 1. Tema Ayarları ve Destekler
function ototamir_theme_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    register_nav_menus( array(
        'primary' => __( 'Ana Menü', 'ototamir' ),
    ) );
}
add_action( 'after_setup_theme', 'ototamir_theme_setup' );


// --- Bereinigte Migrationen fuer Deutschland ---

// 2. CSS ve JS Yükleme (Performans ve Önbellek Uyumlu)
function ototamir_enqueue_scripts() {
    $theme_ver = wp_get_theme()->get( 'Version' ) ?: '1.0.7';
    wp_enqueue_style( 'ototamir-style', get_stylesheet_uri(), array(), $theme_ver );

    // turkey.js sadece usta-ekle formunun açık olduğu sayfada gerekli (13.9 KB tasarruf)
    if ( is_page( 'usta-ekle' ) ) {
        wp_enqueue_script( 'turkey-locations', get_template_directory_uri() . '/assets/js/turkey.js', array(), $theme_ver, true );
    }

    // Swiper sadece ana sayfa ve şehir listeleme sayfasında kullanılıyor
    if ( is_front_page() || is_tax( 'mechanic_city' ) ) {
        // CSS: media="print" trick ile non-render-blocking async yükleme
        wp_enqueue_style( 'swiper-bundle', get_template_directory_uri() . '/assets/css/swiper-bundle.min.css', array(), $theme_ver );
        // JS: defer ile footer'da yükleniyor (true = footer)
        wp_enqueue_script( 'swiper-bundle', get_template_directory_uri() . '/assets/js/swiper-bundle.min.js', array(), $theme_ver, true );
    }
}
add_action( 'wp_enqueue_scripts', 'ototamir_enqueue_scripts' );

// 2.1. CSS Optimizasyonu (Swiper render-blocking engeli kaldır)
// Not: Ana tema CSS'i (ototamir-style) en erken keşif için header.php'nin en başında preload edilmektedir.
function ototamir_preload_main_style( $html, $handle, $href, $media ) {
    // Swiper CSS'i async yükle (render-blocking engeli kaldır)
    if ( 'swiper-bundle' === $handle ) {
        $async  = '<link rel="preload" href="' . esc_url( $href ) . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n";
        $async .= '<noscript>' . $html . '</noscript>';
        return $async;
    }
    return $html;
}
add_filter( 'style_loader_tag', 'ototamir_preload_main_style', 10, 4 );

// 2.2. Gereksiz Render-Blocking Eklenti Kaynaklarını Temizle (Contact Form 7 vb.)
function ototamir_dequeue_unneeded_plugin_assets() {
    if ( ! is_page( 'iletisim' ) && ! is_page_template( 'page-iletisim.php' ) ) {
        wp_dequeue_style( 'contact-form-7' );
        wp_dequeue_script( 'contact-form-7' );
    }
}
add_action( 'wp_enqueue_scripts', 'ototamir_dequeue_unneeded_plugin_assets', 99 );

// 2.3. JavaScript Dosyalarını Defer ile Yükle (Kritik İstek Zincirini Kırma)
function ototamir_defer_scripts( $tag, $handle, $src ) {
    if ( is_admin() ) {
        return $tag;
    }
    $defer_scripts = array( 'turkey-locations', 'swiper-bundle' );
    if ( in_array( $handle, $defer_scripts, true ) ) {
        if ( strpos( $tag, 'defer' ) === false ) {
            $tag = str_replace( '<script ', '<script defer ', $tag );
        }
    }
    return $tag;
}
add_filter( 'script_loader_tag', 'ototamir_defer_scripts', 10, 3 );

// 2.4. WordPress Gereksiz <head> Şişkinliğini Temizle (mobil için kritik)
function ototamir_cleanup_head() {
    remove_action( 'wp_head', 'wp_generator' );
    remove_action( 'wp_head', 'rsd_link' );
    remove_action( 'wp_head', 'wlwmanifest_link' );
    remove_action( 'wp_head', 'wp_shortlink_wp_head' );
    remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
    remove_action( 'wp_head', 'rest_output_link_wp_head' );
    remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
}
add_action( 'init', 'ototamir_cleanup_head' );

// 3. Custom Post Type & Rewrite Rules (FindeWerkstatt.de)
// Die CPT-Registrierung und Taxonomien werden in inc/german-seeder-and-taxonomies.php verwaltet.

function findewerkstatt_rewrite_rules() {
    add_rewrite_rule( '^(werkstaetten|ustalar)/?$', 'index.php?post_type=mechanic', 'top' );
    add_rewrite_rule( '^(werkstaetten|ustalar)/page/([0-9]+)/?$', 'index.php?post_type=mechanic&paged=$matches[2]', 'top' );
    add_rewrite_rule( '^(24h-pannenhilfe|nobetci-oto-tamirciler)/?$', 'index.php?pagename=24h-pannenhilfe', 'top' );
    add_rewrite_rule( '^(24h-pannenhilfe|nobetci-oto-tamirciler)/page/([0-9]+)/?$', 'index.php?pagename=24h-pannenhilfe&paged=$matches[2]', 'top' );
    add_rewrite_rule( '^(fehlerdiagnose|ariza-tespiti)/?$', 'index.php?pagename=fehlerdiagnose', 'top' );
    add_rewrite_rule( '^(marke|marka)/([^/]+)/([^/]+)/?$', 'index.php?car_brand=$matches[3]', 'top' );
    add_rewrite_rule( '^(marke|marka)/([^/]+)/([^/]+)/page/([0-9]+)/?$', 'index.php?car_brand=$matches[3]&paged=$matches[4]', 'top' );

    // Single Werkstatt / Abschleppdienst / Ladestation Rewrite Rules
    add_rewrite_rule( '^(?:ladestation|sarj-istasyonu|elektrikli-sarj-istasyonu)/([^/]+)(?:/([0-9]+))?/?$', 'index.php?mechanic=$matches[1]&page=$matches[2]', 'top' );
    add_rewrite_rule( '^(?:ladestationen|elektrikli-sarj-istasyonlari)/?$', 'index.php?pagename=ladestationen', 'top' );
    add_rewrite_rule( '^(?:ladestationen|elektrikli-sarj-istasyonlari)/page/([0-9]+)/?$', 'index.php?pagename=ladestationen&paged=$matches[1]', 'top' );
    add_rewrite_rule( '^(?:abschleppdienst|pannenhilfe|yol-yardim|yol-yardim-firmasi)/([^/]+)(?:/([0-9]+))?/?$', 'index.php?mechanic=$matches[1]&page=$matches[2]', 'top' );
    add_rewrite_rule( '^(?:werkstatt|autowerkstatt|oto-tamirci|tamirci|mekanik-ustasi)/([^/]+)(?:/([0-9]+))?/?$', 'index.php?mechanic=$matches[1]&page=$matches[2]', 'top' );
    add_rewrite_rule( '^mechanic/([^/]+)(?:/([0-9]+))?/?$', 'index.php?mechanic=$matches[1]&page=$matches[2]', 'top' );
}
add_action( 'init', 'findewerkstatt_rewrite_rules' );

// Model 1: Dinamik Kalıcı Bağlantı Filtresi (Ladestation -> /ladestation/, Pannenhilfe -> /pannenhilfe/, Werkstatt -> /werkstatt/)
function ototamir_mechanic_custom_permalink( $post_link, $post ) {
    if ( is_object( $post ) && $post->post_type === 'mechanic' ) {
        // 1. Ladestation (E-Mobilität)
        $charging_net = get_post_meta( $post->ID, '_mechanic_charging_network', true );
        $is_charging = ! empty( $charging_net );
        
        $terms = wp_get_post_terms( $post->ID, 'service_type', array( 'fields' => 'slugs' ) );
        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            if ( in_array( 'e-mobilitaet-ladestationen', $terms, true ) || in_array( 'ladestation', $terms, true ) || in_array( 'elektrikli-sarj-istasyonu', $terms, true ) ) {
                $is_charging = true;
            }
        }
        
        if ( $is_charging ) {
            return home_url( user_trailingslashit( 'ladestation/' . $post->post_name ) );
        }

        $road_assist = get_post_meta( $post->ID, '_mechanic_road_assist', true );
        $is_road_assist = ( $road_assist === 'Evet' || $road_assist === '1' || $road_assist === 'yes' || $road_assist === 'Ja' || $road_assist === 'ja' );
        
        if ( ! $is_road_assist ) {
            if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
                if ( in_array( '24h-abschleppdienst-pannenhilfe', $terms, true ) || in_array( 'pannenhilfe', $terms, true ) || in_array( 'abschleppdienst', $terms, true ) || in_array( 'oto-cekici-yol-yardim', $terms, true ) ) {
                    $is_road_assist = true;
                }
            }
        }
        
        $prefix = $is_road_assist ? 'pannenhilfe' : 'werkstatt';
        return home_url( user_trailingslashit( $prefix . '/' . $post->post_name ) );
    }
    return $post_link;
}
add_filter( 'post_type_link', 'ototamir_mechanic_custom_permalink', 10, 2 );

// Model 1: Eski /mechanic/{slug} ve Alias Linkleri için Otomatik 301 Kalıcı Yönlendirme (SEO Koruma)
function ototamir_redirect_old_mechanic_slugs() {
    if ( is_admin() || wp_doing_ajax() ) {
        return;
    }
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    $path = trim( parse_url( $request_uri, PHP_URL_PATH ), '/' );

    // 1. Eski /mechanic/{slug} linklerini 301 kalıcı yönlendirme ile yeni dinamik linke aktar
    if ( preg_match( '#^mechanic/([^/]+)/?$#i', $path, $matches ) ) {
        $slug = sanitize_title( $matches[1] );
        $post = get_page_by_path( $slug, OBJECT, 'mechanic' );
        if ( ! $post ) {
            $p = get_posts( array(
                'name'        => $slug,
                'post_type'   => 'mechanic',
                'post_status' => 'publish',
                'numberposts' => 1,
            ) );
            if ( ! empty( $p ) ) {
                $post = $p[0];
            }
        }
        if ( $post ) {
            $target = get_permalink( $post->ID );
            if ( $target ) {
                wp_safe_redirect( $target, 301 );
                exit;
            }
        }
    }

    // 2. Kanonik URL doğrulaması
    if ( is_singular( 'mechanic' ) ) {
        $queried_id = get_queried_object_id();
        if ( $queried_id ) {
            $canonical = get_permalink( $queried_id );
            $current = home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) );
            if ( $canonical && rtrim( $canonical, '/' ) !== rtrim( $current, '/' ) ) {
                wp_safe_redirect( $canonical, 301 );
                exit;
            }
        }
    }
}
add_action( 'template_redirect', 'ototamir_redirect_old_mechanic_slugs', 1 );

// Sürüm güncellemesinde rewrite kurallarını otomatik bir defaya mahsus yenile
function ototamir_flush_rewrite_rules_on_version() {
    $flushed_ver = get_option( 'findewerkstatt_rewrite_version', '' );
    if ( $flushed_ver !== '1.0.0' ) {
        findewerkstatt_rewrite_rules();
        flush_rewrite_rules( false );
        update_option( 'findewerkstatt_rewrite_version', '1.0.0' );
    }
}
add_action( 'init', 'ototamir_flush_rewrite_rules_on_version', 99 );

// Not: CPT (mechanic) ve Taxonomien (mechanic_city, mechanic_district, service_type, car_brand)
// deutsche Slugs (stadt, stadtteil, service, marke) werden zentral in inc/german-seeder-and-taxonomies.php registriert.

// 5. Meta Box: İletişim Bilgileri
function ototamir_add_mechanic_meta_box() {
    add_meta_box(
        'mechanic_contact_details',
        __( 'İletişim ve Detaylar', 'ototamir' ),
        'ototamir_mechanic_meta_box_html',
        'mechanic',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'ototamir_add_mechanic_meta_box' );

function ototamir_mechanic_meta_box_html( $post ) {
    $phone = get_post_meta( $post->ID, '_mechanic_phone', true );
    $address = get_post_meta( $post->ID, '_mechanic_address', true );
    $sunday = get_post_meta( $post->ID, '_mechanic_sunday', true );
    $road_assist = get_post_meta( $post->ID, '_mechanic_road_assist', true );
    $is_featured = get_post_meta( $post->ID, '_mechanic_is_featured', true );
    $badge_text = get_post_meta( $post->ID, '_mechanic_badge_text', true );
    
    // İlan Sahibi (Mülkiyet) Yönetimi
    $current_author_id = (int) $post->post_author;
    $author_user       = $current_author_id > 0 ? get_userdata( $current_author_id ) : null;
    $is_admin_author   = $author_user && in_array( 'administrator', (array) $author_user->roles, true );
    $orig_author       = (int) get_post_meta( $post->ID, '_mechanic_original_author', true );

    // Eğer ilan yöneticiye geçmişse ve başlık/slug bir üyeyle uyuşuyorsa akıllı öneri bul
    $suggested_user = null;
    if ( $is_admin_author ) {
        if ( $orig_author > 0 && $orig_author !== $current_author_id ) {
            $suggested_user = get_userdata( $orig_author );
        }
        if ( ! $suggested_user ) {
            $slug_keyword = str_replace( array('usta','oto','tamir','servis','-'), '', sanitize_title( $post->post_title ) );
            if ( strlen( $slug_keyword ) >= 3 ) {
                $candidate_users = get_users( array(
                    'search'         => '*' . $slug_keyword . '*',
                    'search_columns' => array( 'user_login', 'user_nicename', 'display_name' ),
                    'number'         => 3,
                ) );
                foreach ( $candidate_users as $cu ) {
                    if ( ! in_array( 'administrator', (array) $cu->roles, true ) ) {
                        $suggested_user = $cu;
                        break;
                    }
                }
            }
        }
    }
    
    wp_nonce_field( 'ototamir_save_mechanic_meta', 'ototamir_mechanic_meta_nonce' );
    ?>
    <!-- 👤 İLAN SAHİBİ & FİRMA YETKİLİSİ (ÜYELİK BAĞLANTISI) -->
    <div style="background:#f8fafc; border:2px solid <?php echo $is_admin_author ? '#f87171' : '#cbd5e1'; ?>; border-radius:10px; padding:16px; margin-bottom:18px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; flex-wrap:wrap; gap:8px;">
            <div>
                <h4 style="margin:0 0 3px; font-size:14px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px;">
                    👤 İlan Sahibi & Firma Yetkilisi (Üye Hesabı Bağlantısı)
                </h4>
                <p style="margin:0; font-size:12px; color:#64748b;">
                    Bu ilanın paneline erişebilecek ve düzenleyebilecek kayıtlı kullanıcı hesabı. Siz yönetici olarak düzenleseniz dahi ilanın sahibi değişmez.
                </p>
            </div>
            <?php if ( $is_admin_author ) : ?>
                <span style="background:#fee2e2; color:#b91c1c; border:1px solid #f87171; font-size:11.5px; font-weight:800; padding:4px 10px; border-radius:12px;">
                    ⚠️ Yöneticiye (Admin) Atanmış
                </span>
            <?php else : ?>
                <span style="background:#dcfce7; color:#15803d; border:1px solid #86efac; font-size:11.5px; font-weight:800; padding:4px 10px; border-radius:12px;">
                    ✓ Üye Hesabına Bağlı
                </span>
            <?php endif; ?>
        </div>

        <?php if ( $suggested_user ) : ?>
            <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:10px 14px; margin-bottom:12px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                <div style="font-size:12.5px; color:#1e40af;">
                    💡 <strong>Öneri:</strong> Bu ilan kayıtlı üye <strong><?php echo esc_html( $suggested_user->display_name ); ?></strong> (@<?php echo esc_html( $suggested_user->user_login ); ?> &middot; <?php echo esc_html( $suggested_user->user_email ); ?>) ile eşleşiyor!
                </div>
                <button type="button" class="button button-small" onclick="document.getElementById('ototamir_mechanic_assigned_author').value='<?php echo esc_attr( $suggested_user->ID ); ?>';" style="background:#2563eb; color:#fff; border-color:#1d4ed8; font-weight:700;">
                    👉 Bu Kullanıcıyı Seç
                </button>
            </div>
        <?php endif; ?>

        <div style="display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
            <div style="flex:1; min-width:280px;">
                <label for="ototamir_mechanic_assigned_author" style="font-size:12px; font-weight:700; color:#334155; display:block; margin-bottom:5px;">
                    İlan Sahibi Olan Üyeyi Seçin / Değiştirin:
                </label>
                <select name="ototamir_mechanic_assigned_author" id="ototamir_mechanic_assigned_author" style="width:100%; max-width:440px; font-size:13px; font-weight:600; padding:6px 10px; border-radius:6px; border:1px solid #cbd5e1;">
                    <?php
                    $all_users = get_users( array( 'orderby' => 'display_name', 'order' => 'ASC', 'number' => 200 ) );
                    foreach ( $all_users as $u ) {
                        $role_label = in_array( 'administrator', (array) $u->roles, true ) ? ' [Yönetici]' : '';
                        $selected   = ( (int) $u->ID === $current_author_id ) ? 'selected="selected"' : '';
                        echo '<option value="' . esc_attr( $u->ID ) . '" ' . $selected . '>';
                        echo esc_html( $u->display_name . ' (@' . $u->user_login . ' - ' . $u->user_email . ')' . $role_label );
                        echo '</option>';
                    }
                    ?>
                </select>
            </div>

            <?php if ( $author_user ) : ?>
                <div style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:8px 14px; font-size:12px;">
                    <div style="font-weight:700; color:#0f172a;">
                        Şu Anki Sahip: <?php echo esc_html( $author_user->display_name ); ?> (@<?php echo esc_html( $author_user->user_login ); ?>)
                    </div>
                    <div style="color:#64748b; font-size:11.5px;">
                        ✉ <?php echo esc_html( $author_user->user_email ); ?>
                    </div>
                    <a href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . $current_author_id ) ); ?>" target="_blank" style="color:#0284c7; text-decoration:none; font-weight:600; font-size:11px; margin-top:3px; display:inline-block;">
                        Kullanıcı Profilini Gör ↗
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <p>
        <label for="mechanic_phone">Telefon Numarası:</label>
        <input type="text" id="mechanic_phone" name="mechanic_phone" value="<?php echo esc_attr( $phone ); ?>" style="width:100%;" />
    </p>
    <p>
        <label for="mechanic_address">Açık Adres:</label>
        <textarea id="mechanic_address" name="mechanic_address" rows="3" style="width:100%;"><?php echo esc_textarea( $address ); ?></textarea>
    </p>
    <div style="background:#f8fafc; border:1px solid #e2e8f0; padding:12px; border-radius:8px; margin-bottom:15px;">
        <p style="margin:0 0 8px 0; font-weight:600; color:#334155;">
            📍 Yerel SEO & Harita Konum Koordinatları (İsteğe bağlı):
            <span style="font-weight:400; font-size:12px; color:#64748b; display:block;">Boş bırakılırsa ustanın seçili il/ilçe merkez koordinatları otomatik atanır.</span>
        </p>
        <div style="display:flex; gap:12px;">
            <div style="flex:1;">
                <label for="mechanic_latitude" style="font-size:12px; color:#475569;">Enlem (Latitude):</label>
                <input type="text" id="mechanic_latitude" name="mechanic_latitude" value="<?php echo esc_attr( $lat ); ?>" placeholder="Örn: 41.0082" style="width:100%; font-family:monospace;" />
            </div>
            <div style="flex:1;">
                <label for="mechanic_longitude" style="font-size:12px; color:#475569;">Boylam (Longitude):</label>
                <input type="text" id="mechanic_longitude" name="mechanic_longitude" value="<?php echo esc_attr( $lng ); ?>" placeholder="Örn: 28.9784" style="width:100%; font-family:monospace;" />
            </div>
        </div>
    </div>
    <div style="background:#f0fdf4; border:1px solid #bbf7d0; padding:12px; border-radius:8px; margin-bottom:15px;">
        <p style="margin:0 0 8px 0;">
            <label>
                <input type="checkbox" name="mechanic_sunday" value="yes" <?php checked( $sunday, 'yes' ); ?> />
                <strong>Pazar Günü Açık mı?</strong> (Nöbetçi filtrelerinde listelenir)
            </label>
        </p>
        <p style="margin:0;">
            <label>
                <input type="checkbox" name="mechanic_road_assist" value="yes" <?php checked( $road_assist, 'yes' ); ?> />
                <strong>7/24 Yol Yardım / Çekici Var mı?</strong> (Acil yol yardım filtrelerinde listelenir)
            </label>
        </p>
    </div>
    <div style="background:#fffbeb; border:1px solid #fde68a; padding:12px; border-radius:8px; margin-bottom:15px;">
        <p style="margin:0 0 8px 0;">
            <label style="font-weight:bold; color:#b45309;">
                <input type="checkbox" name="mechanic_is_featured" value="1" <?php checked( $is_featured, '1' ); ?> />
                👑 Öne Çıkan / VIP Vitrin Usta (Arama ve listelemelerin en başında altın çerçeveyle gösterilir)
            </label>
        </p>
        <p style="margin:0;">
            <label for="mechanic_badge_text" style="font-size:12px; color:#78350f; font-weight:600;">Özel Rozet Metni (İsteğe bağlı):</label>
            <input type="text" id="mechanic_badge_text" name="mechanic_badge_text" value="<?php echo esc_attr( $badge_text ); ?>" placeholder="Örn: Öne Çıkan VIP Usta" style="width:100%; margin-top:4px;" />
    </div>
    
    <!-- 🎯 MÜŞTERİ TIKLAMA & LEAD DÖNÜŞÜM RADARI -->
    <?php
    $calls       = (int) get_post_meta( $post->ID, '_mechanic_call_count', true );
    $whatsapp    = (int) get_post_meta( $post->ID, '_mechanic_whatsapp_count', true );
    $directions  = (int) get_post_meta( $post->ID, '_mechanic_directions_count', true );
    $location_wp = (int) get_post_meta( $post->ID, '_mechanic_location_wp_count', true );
    $total_leads = $calls + $whatsapp + $directions + $location_wp;
    $lead_logs   = get_post_meta( $post->ID, '_mechanic_lead_logs', true );
    if ( ! is_array( $lead_logs ) ) {
        $lead_logs = array();
    }
    ?>
    <div style="background:#f8fafc; border:2px solid #cbd5e1; border-radius:10px; padding:16px; margin-top:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
            <div>
                <h4 style="margin:0 0 3px 0; font-size:14px; font-weight:800; color:#0f172a;">
                    🎯 Bu İlanda Yapılan Müşteri Tıklamaları & Ziyaretçi Hareketleri
                </h4>
                <p style="margin:0; font-size:12px; color:#64748b;">
                    Ziyaretçilerin bu ilanın sayfasına girip tıkladığı butonlar (Hemen Ara, WhatsApp, Açık Adres Yol Tarifi) anlık takip edilir.
                </p>
            </div>
            <span style="background:#0f172a; color:#fff; font-size:11px; font-weight:700; padding:4px 10px; border-radius:12px;">
                Toplam <?php echo esc_html( $total_leads ); ?> Müşteri Aksiyonu
            </span>
        </div>

        <!-- 4 SAYAÇ KARTI -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(130px, 1fr)); gap:10px; margin-bottom:14px;">
            <div style="background:#fff; border:1px solid #fed7aa; border-radius:8px; padding:10px; border-left:3px solid #ea580c;">
                <div style="font-size:11px; font-weight:700; color:#c2410c;">📞 Telefon Araması</div>
                <div style="font-size:20px; font-weight:900; color:#0f172a; margin-top:3px;"><?php echo esc_html( $calls ); ?></div>
            </div>
            <div style="background:#fff; border:1px solid #bbf7d0; border-radius:8px; padding:10px; border-left:3px solid #16a34a;">
                <div style="font-size:11px; font-weight:700; color:#15803d;">💬 WhatsApp</div>
                <div style="font-size:20px; font-weight:900; color:#0f172a; margin-top:3px;"><?php echo esc_html( $whatsapp ); ?></div>
            </div>
            <div style="background:#fff; border:1px solid #bae6fd; border-radius:8px; padding:10px; border-left:3px solid #0284c7;">
                <div style="font-size:11px; font-weight:700; color:#0369a1;">📍 Açık Adres / Yol</div>
                <div style="font-size:20px; font-weight:900; color:#0f172a; margin-top:3px;"><?php echo esc_html( $directions ); ?></div>
            </div>
            <div style="background:#fff; border:1px solid #fecaca; border-radius:8px; padding:10px; border-left:3px solid #dc2626;">
                <div style="font-size:11px; font-weight:700; color:#b91c1c;">🚨 Acil GPS Konum</div>
                <div style="font-size:20px; font-weight:900; color:#0f172a; margin-top:3px;"><?php echo esc_html( $location_wp ); ?></div>
            </div>
        </div>

        <!-- SON TIKLAMA HAREKETLERİ LİSTESİ -->
        <?php if ( ! empty( $lead_logs ) ) : ?>
            <div style="background:#fff; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden;">
                <table class="widefat fixed striped" style="border:none; font-size:12px; margin:0;">
                    <thead>
                        <tr style="background:#f1f5f9;">
                            <th style="width:130px; font-weight:700; padding:8px 10px;">Zaman</th>
                            <th style="font-weight:700; padding:8px 10px;">Nereye Tıkladı & Ne Yaptı?</th>
                            <th style="width:140px; font-weight:700; padding:8px 10px;">Cihaz</th>
                            <th style="width:110px; font-weight:700; padding:8px 10px;">Maskeli IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $lead_logs as $log ) : 
                            $time_diff = human_time_diff( $log['time'], time() ) . ' önce';
                        ?>
                            <tr>
                                <td style="padding:8px 10px;">
                                    <strong><?php echo esc_html( $time_diff ); ?></strong>
                                    <div style="color:#94a3b8; font-size:11px;"><?php echo date_i18n( 'd.m H:i:s', $log['time'] ); ?></div>
                                </td>
                                <td style="padding:8px 10px; font-weight:600; color:#0f172a;">
                                    <?php echo esc_html( $log['detail'] ?? ( $log['action_type'] ?? 'Müşteri Aksiyonu' ) ); ?>
                                </td>
                                <td style="padding:8px 10px; color:#475569;">
                                    <?php echo esc_html( $log['device'] ?? '-' ); ?>
                                </td>
                                <td style="padding:8px 10px;">
                                    <code style="font-size:11px; background:#f1f5f9; padding:2px 5px; border-radius:4px;"><?php echo esc_html( $log['ip'] ?? '-' ); ?></code>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <div style="background:#fff; border:1px dashed #cbd5e1; border-radius:8px; padding:15px; text-align:center; color:#94a3b8; font-size:12.5px;">
                Henüz bu ilana ait kaydedilmiş telefon, WhatsApp veya harita tıklaması bulunmuyor.
            </div>
        <?php endif; ?>
    </div>
    <?php
}

function ototamir_save_mechanic_meta( $post_id ) {
    if ( ! isset( $_POST['ototamir_mechanic_meta_nonce'] ) || ! wp_verify_nonce( $_POST['ototamir_mechanic_meta_nonce'], 'ototamir_save_mechanic_meta' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    
    if ( isset( $_POST['mechanic_phone'] ) ) {
        update_post_meta( $post_id, '_mechanic_phone', sanitize_text_field( $_POST['mechanic_phone'] ) );
    }
    if ( isset( $_POST['mechanic_address'] ) ) {
        update_post_meta( $post_id, '_mechanic_address', sanitize_textarea_field( $_POST['mechanic_address'] ) );
    }
    if ( isset( $_POST['mechanic_latitude'] ) ) {
        update_post_meta( $post_id, '_mechanic_latitude', sanitize_text_field( $_POST['mechanic_latitude'] ) );
    }
    if ( isset( $_POST['mechanic_longitude'] ) ) {
        update_post_meta( $post_id, '_mechanic_longitude', sanitize_text_field( $_POST['mechanic_longitude'] ) );
    }
    
    $sunday = isset( $_POST['mechanic_sunday'] ) ? 'yes' : 'no';
    update_post_meta( $post_id, '_mechanic_sunday', $sunday );
    
    $road = isset( $_POST['mechanic_road_assist'] ) ? 'yes' : 'no';
    update_post_meta( $post_id, '_mechanic_road_assist', $road );

    $old_featured = get_post_meta( $post_id, '_mechanic_is_featured', true );
    $featured = isset( $_POST['mechanic_is_featured'] ) ? '1' : '0';
    update_post_meta( $post_id, '_mechanic_is_featured', $featured );

    if ( $featured === '1' && $old_featured !== '1' && function_exists( 'ototamir_notify_user_vip_activated' ) ) {
        ototamir_notify_user_vip_activated( $post_id );
    }

    if ( isset( $_POST['mechanic_badge_text'] ) ) {
        update_post_meta( $post_id, '_mechanic_badge_text', sanitize_text_field( $_POST['mechanic_badge_text'] ) );
    }

    // İlan Sahibi / Bağlı Üye Ataması (Mülkiyet Koruma)
    if ( isset( $_POST['ototamir_mechanic_assigned_author'] ) ) {
        $assigned_author = (int) $_POST['ototamir_mechanic_assigned_author'];
        if ( $assigned_author > 0 ) {
            $current_post_author = (int) get_post_field( 'post_author', $post_id );
            if ( $current_post_author !== $assigned_author ) {
                global $wpdb;
                $wpdb->update( $wpdb->posts, array( 'post_author' => $assigned_author ), array( 'ID' => $post_id ) );
                clean_post_cache( $post_id );
            }
            update_post_meta( $post_id, '_mechanic_original_author', $assigned_author );
            update_post_meta( $post_id, '_mechanic_owner_user_id', $assigned_author );
        }
    }
}
add_action( 'save_post', 'ototamir_save_mechanic_meta' );

/**
 * Yönetici İlanı Düzenlediğinde Asıl Üyenin / Sahibin Otomatik Olarak Değişmesini Kesinlikle Engelle
 */
add_filter( 'wp_insert_post_data', function( $data, $postarr ) {
    if ( ( $data['post_type'] ?? '' ) === 'mechanic' && ! empty( $postarr['ID'] ) ) {
        $post_id       = (int) $postarr['ID'];
        $original_post = get_post( $post_id );

        if ( $original_post && $original_post->post_type === 'mechanic' ) {
            $original_author = (int) $original_post->post_author;

            // 1. Admin metabox dropdown'ından açıkça bir üye seçilmişse
            if ( isset( $_POST['ototamir_mechanic_assigned_author'] ) ) {
                $assigned = (int) $_POST['ototamir_mechanic_assigned_author'];
                if ( $assigned > 0 ) {
                    $data['post_author'] = $assigned;
                    return $data;
                }
            }

            // 2. Standart WordPress post_author_override alanı geldiyse
            if ( ! empty( $postarr['post_author_override'] ) ) {
                $data['post_author'] = (int) $postarr['post_author_override'];
                return $data;
            }

            // 3. Eğer mevcut ilanın bir yazarı varsa, WP core'un otomatik olarak mevcut yöneticiyi (admin) yazar yapmasını ENGELLE!
            if ( $original_author > 0 ) {
                $data['post_author'] = $original_author;
            }
        }
    }
    return $data;
}, 10, 2 );

/**
 * Admin veya sistem tarafından düzenlenip yazarı yöneticiye geçen ilanları,
 * eğer başlık ve kullanıcı adı tam eşleşiyorsa (örneğin 'Hamza Usta' ve 'hamzausta')
 * veya meta eşleşiyorsa otomatik olarak asıl sahibine geri bağla.
 */
function ototamir_auto_link_orphaned_mechanics() {
    if ( ! is_admin() || get_transient( 'ototamir_orphaned_mechanics_checked' ) ) {
        return;
    }
    set_transient( 'ototamir_orphaned_mechanics_checked', 1, 3600 );

    global $wpdb;
    $admin_mechanics = $wpdb->get_results(
        "SELECT ID, post_title, post_name FROM {$wpdb->posts} WHERE post_type = 'mechanic' AND post_author = 1"
    );

    if ( empty( $admin_mechanics ) ) {
        return;
    }

    $all_users = get_users( array( 'role__not_in' => array( 'administrator' ) ) );
    if ( empty( $all_users ) ) {
        return;
    }

    foreach ( $admin_mechanics as $mech ) {
        $mech_slug = sanitize_title( $mech->post_title );
        foreach ( $all_users as $user ) {
            $user_login_slug = sanitize_title( $user->user_login );
            $user_name_slug  = sanitize_title( $user->display_name );
            
            $match = false;
            if ( ! empty( $user_login_slug ) && strpos( str_replace('-', '', $mech_slug), str_replace('-', '', $user_login_slug) ) !== false ) {
                $match = true;
            } elseif ( ! empty( $user_name_slug ) && strpos( str_replace('-', '', $mech_slug), str_replace('-', '', $user_name_slug) ) !== false ) {
                $match = true;
            }

            if ( $match ) {
                $wpdb->update( $wpdb->posts, array( 'post_author' => $user->ID ), array( 'ID' => $mech->ID ) );
                clean_post_cache( $mech->ID );
                update_post_meta( $mech->ID, '_mechanic_original_author', $user->ID );
                update_post_meta( $mech->ID, '_mechanic_owner_user_id', $user->ID );
                break;
            }
        }
    }
}
add_action( 'admin_init', 'ototamir_auto_link_orphaned_mechanics' );

// Not: Deutschland 16 Bundesländer, Top 100 Städte & deutsche Kfz-Kategorien
// werden sauber über inc/german-seeder-and-taxonomies.php verwaltet.

// 8. Temel Sayfaları (Usta Ekle, Nöbetçi Tamirciler) Otomatik Oluştur
function auto_create_essential_pages() {
    $pages = array(
        'usta-ekle' => array(
            'title'    => 'Usta Ekle',
            'template' => 'page-usta-ekle.php'
        ),
        'nobetci-oto-tamirciler' => array(
            'title'    => 'Nöbetçi & Pazar Günü Açık Oto Tamirciler',
            'template' => 'page-nobetci-oto-tamirciler.php'
        ),
        'ariza-tespiti' => array(
            'title'    => 'Yapay Zeka ile Arıza Tespiti',
            'template' => 'page-ariza-tespiti.php'
        ),
        'elektrikli-sarj-istasyonlari' => array(
            'title'    => 'Türkiye Elektrikli Araç Şarj İstasyonları Haritası ve Rehberi',
            'template' => 'page-sarj-istasyonlari.php'
        )
    );

    foreach ( $pages as $slug => $p_data ) {
        $page = get_page_by_path( $slug );
        if ( ! $page ) {
            $pid = wp_insert_post( array(
                'post_title'     => $p_data['title'],
                'post_name'      => $slug,
                'post_content'   => '',
                'post_status'    => 'publish',
                'post_author'    => 1,
                'post_type'      => 'page',
            ) );
            if ( $pid && ! is_wp_error( $pid ) && ! empty( $p_data['template'] ) ) {
                update_post_meta( $pid, '_wp_page_template', $p_data['template'] );
            }
        }
    }
}
add_action('init', 'auto_create_essential_pages');

// Nöbetçi Tamirciler, AI Arıza Tespiti ve Şarj İstasyonları Sayfa Şablonu Garantisi (404'e düşmeyi engeller, şablonu doğrudan yükler)
add_filter( 'template_include', function( $template ) {
    $uri = trim( parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ), '/' );
    if ( $uri === 'nobetci-oto-tamirciler' || is_page('nobetci-oto-tamirciler') || preg_match( '#(^|/)nobetci-oto-tamirciler$#', $uri ) ) {
        $nobetci_template = locate_template( array( 'page-nobetci-oto-tamirciler.php', 'page-nobetci-tamirci.php' ) );
        if ( $nobetci_template ) {
            global $wp_query;
            $wp_query->is_page   = true;
            $wp_query->is_404    = false;
            $wp_query->is_single = false;
            $wp_query->is_archive = false;
            status_header( 200 );
            return $nobetci_template;
        }
    }

    if ( $uri === 'ariza-tespiti' || is_page('ariza-tespiti') || preg_match( '#(^|/)ariza-tespiti$#', $uri ) ) {
        $ariza_template = locate_template( array( 'page-ariza-tespiti.php' ) );
        if ( $ariza_template ) {
            global $wp_query;
            $wp_query->is_page    = true;
            $wp_query->is_404     = false;
            $wp_query->is_single  = false;
            $wp_query->is_archive = false;
            status_header( 200 );
            return $ariza_template;
        }
    }

    if ( $uri === 'elektrikli-sarj-istasyonlari' || $uri === 'sarj-istasyonlari' || is_page('elektrikli-sarj-istasyonlari') || preg_match( '#(^|/)elektrikli-sarj-istasyonlari$#', $uri ) ) {
        $sarj_template = locate_template( array( 'page-sarj-istasyonlari.php' ) );
        if ( $sarj_template ) {
            global $wp_query;
            $wp_query->is_page    = true;
            $wp_query->is_404     = false;
            $wp_query->is_single  = false;
            $wp_query->is_archive = false;
            status_header( 200 );
            return $sarj_template;
        }
    }
    return $template;
}, 99 );

// 8.1. Oto Çekici ve Yol Yardım Ustalarını Meta ile Senkronize Et
function ototamir_sync_road_assist_meta_v136() {
    if ( get_option('ototamir_road_assist_synced_v136') ) return;
    
    $term = get_term_by('slug', 'oto-cekici-yol-yardim', 'service_type');
    if ($term) {
        $cekici_posts = get_posts(array(
            'post_type'      => 'mechanic',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'tax_query'      => array(
                array(
                    'taxonomy' => 'service_type',
                    'field'    => 'term_id',
                    'terms'    => $term->term_id,
                )
            )
        ));
        if ($cekici_posts) {
            foreach ($cekici_posts as $pid) {
                update_post_meta($pid, '_mechanic_road_assist', 'yes');
            }
        }
    }
    update_option('ototamir_road_assist_synced_v136', true);
}
add_action('init', 'ototamir_sync_road_assist_meta_v136');

// 9. WP Admin "Tamirciler" listesinde Onayla/Reddet butonları
add_filter( 'post_row_actions', 'ototamir_mechanic_row_actions', 10, 2 );
function ototamir_mechanic_row_actions( $actions, $post ) {
    if ( $post->post_type == 'mechanic' && $post->post_status == 'pending' ) {
        $approve_url = wp_nonce_url( admin_url( 'admin.php?action=approve_mechanic&post_id=' . $post->ID ), 'approve_mechanic_' . $post->ID );
        $reject_url = wp_nonce_url( admin_url( 'admin.php?action=reject_mechanic&post_id=' . $post->ID ), 'reject_mechanic_' . $post->ID );
        
        $new_actions = array(
            'btn_onayla' => sprintf( '<a href="%s" style="color:#10b981; font-weight:bold;">Onayla (Yayınla)</a>', esc_url( $approve_url ) ),
            'btn_reddet' => sprintf( '<a href="%s" style="color:#ef4444; font-weight:bold;">Reddet (Çöpe At)</a>', esc_url( $reject_url ) )
        );
        $actions = array_merge( $new_actions, $actions );
    }
    return $actions;
}

add_action( 'admin_action_approve_mechanic', 'ototamir_handle_approve_mechanic' );
function ototamir_handle_approve_mechanic() {
    if ( !isset($_GET['post_id']) || !isset($_GET['_wpnonce']) ) return;
    $post_id = intval($_GET['post_id']);
    if ( !wp_verify_nonce( $_GET['_wpnonce'], 'approve_mechanic_' . $post_id ) ) wp_die('Güvenlik doğrulaması başarısız.');
    
    if ( current_user_can('edit_post', $post_id) ) {
        wp_update_post( array(
            'ID' => $post_id,
            'post_status' => 'publish'
        ) );
    }
    wp_redirect( wp_get_referer() );
    exit;
}

add_action( 'admin_action_reject_mechanic', 'ototamir_handle_reject_mechanic' );
function ototamir_handle_reject_mechanic() {
    if ( !isset($_GET['post_id']) || !isset($_GET['_wpnonce']) ) return;
    $post_id = intval($_GET['post_id']);
    if ( !wp_verify_nonce( $_GET['_wpnonce'], 'reject_mechanic_' . $post_id ) ) wp_die('Güvenlik doğrulaması başarısız.');
    
    if ( current_user_can('delete_post', $post_id) ) {
        wp_trash_post( $post_id );
    }
    wp_redirect( wp_get_referer() );
    exit;
}

// 10. YORUM VE PUANLAMA SİSTEMİ & DOĞRULANMIŞ MÜŞTERİ (FATURA / İŞ EMRİ)
add_action('comment_post', function($comment_id) {
    if(isset($_POST['mechanic_rating']) && !empty($_POST['mechanic_rating'])) {
        $rating = intval($_POST['mechanic_rating']);
        if($rating >= 1 && $rating <= 5) {
            add_comment_meta($comment_id, 'mechanic_rating', $rating);
        }
    }

    // Fatura / İş Emri / Fiş Yükleme (Doğrulanmış Müşteri Rozeti)
    if ( ! empty( $_FILES['comment_proof']['name'] ) ) {
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );

        $attach_id = media_handle_upload( 'comment_proof', 0 );
        if ( ! is_wp_error( $attach_id ) ) {
            add_comment_meta( $comment_id, '_comment_proof_id', $attach_id );
            add_comment_meta( $comment_id, '_is_verified_customer', '1' );
        }
    }
});

// WP Admin "Tamirciler" listesinde VIP Vitrin, Müşteri Eylemleri ve Ekleyen / Firma Sahibi sütunları
add_filter( 'manage_mechanic_posts_columns', function( $columns ) {
    $new_columns = array();
    foreach ( $columns as $key => $title ) {
        $new_columns[$key] = $title;
        if ( $key === 'title' ) {
            $new_columns['mechanic_owner'] = 'Ekleyen / Firma Sahibi';
            $new_columns['mechanic_leads'] = 'Müşteri Eylemleri';
        }
    }
    $new_columns['mechanic_vip'] = 'VIP Vitrin';
    return $new_columns;
} );

// Müşteri Eylemleri sütununu sıralanabilir yap
add_filter( 'manage_edit-mechanic_sortable_columns', function( $sortable_columns ) {
    $sortable_columns['mechanic_leads'] = 'mechanic_leads';
    return $sortable_columns;
} );

add_action( 'pre_get_posts', function( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() ) {
        return;
    }
    if ( $query->get( 'post_type' ) === 'mechanic' && $query->get( 'orderby' ) === 'mechanic_leads' ) {
        $query->set( 'meta_key', '_mechanic_leads_total' );
        $query->set( 'orderby', 'meta_value_num' );
    }
} );

add_action( 'manage_mechanic_posts_custom_column', function( $column, $post_id ) {
    if ( $column === 'mechanic_owner' ) {
        $author_id = (int) get_post_field( 'post_author', $post_id );
        if ( $author_id > 0 && ( $user = get_userdata( $author_id ) ) ) {
            $is_admin = in_array( 'administrator', (array) $user->roles, true );
            $edit_user_url = admin_url( 'user-edit.php?user_id=' . $author_id );
            $phone = get_user_meta( $author_id, 'billing_phone', true );
            if ( empty( $phone ) ) {
                $phone = get_user_meta( $author_id, 'phone', true );
            }
            if ( empty( $phone ) ) {
                $phone = get_post_meta( $post_id, '_mechanic_phone', true );
            }

            echo '<div style="display:flex; flex-direction:column; gap:2px; font-size:12px;">';
            echo '<div style="font-weight:600;"><a href="' . esc_url( $edit_user_url ) . '" title="Kullanıcı Profilini Gör">' . esc_html( $user->display_name ) . '</a>';
            if ( $is_admin ) {
                echo ' <span style="background:#e0e7ff; color:#3730a3; padding:1px 5px; border-radius:4px; font-size:10px; font-weight:700;">Yönetici</span>';
            } else {
                echo ' <span style="background:#dcfce7; color:#15803d; padding:1px 5px; border-radius:4px; font-size:10px; font-weight:700;">Usta/Üye</span>';
            }
            echo '</div>';
            echo '<div style="color:#64748b;"><a href="mailto:' . esc_attr( $user->user_email ) . '" style="color:#64748b; text-decoration:none;">✉ ' . esc_html( $user->user_email ) . '</a></div>';
            if ( ! empty( $phone ) ) {
                echo '<div style="color:#0284c7; font-weight:500;">📞 ' . esc_html( $phone ) . '</div>';
            }
            echo '</div>';
        } else {
            echo '<span style="color:#94a3b8; font-style:italic;">Misafir / Sistem</span>';
        }
    }

    if ( $column === 'mechanic_leads' ) {
        $calls       = (int) get_post_meta( $post_id, '_mechanic_call_count', true );
        $whatsapp    = (int) get_post_meta( $post_id, '_mechanic_whatsapp_count', true );
        $directions  = (int) get_post_meta( $post_id, '_mechanic_directions_count', true );
        $location_wp = (int) get_post_meta( $post_id, '_mechanic_location_wp_count', true );
        $total_leads = $calls + $whatsapp + $directions + $location_wp;

        if ( $total_leads > 0 ) {
            echo '<div style="display:flex; flex-direction:column; gap:4px; font-size:11.5px;">';
            echo '<div style="display:inline-flex; align-items:center; gap:5px; flex-wrap:wrap;">';
            if ( $calls > 0 ) {
                echo '<span style="background:#ffedd5; color:#c2410c; padding:2px 6px; border-radius:4px; font-weight:700;" title="' . esc_attr($calls) . ' Telefon Araması">📞 ' . esc_html($calls) . '</span>';
            }
            if ( $whatsapp > 0 ) {
                echo '<span style="background:#dcfce7; color:#15803d; padding:2px 6px; border-radius:4px; font-weight:700;" title="' . esc_attr($whatsapp) . ' WhatsApp Mesajı">💬 ' . esc_html($whatsapp) . '</span>';
            }
            if ( $directions > 0 ) {
                echo '<span style="background:#e0f2fe; color:#0369a1; padding:2px 6px; border-radius:4px; font-weight:700;" title="' . esc_attr($directions) . ' Harita / Yol Tarifi">📍 ' . esc_html($directions) . '</span>';
            }
            if ( $location_wp > 0 ) {
                echo '<span style="background:#fee2e2; color:#b91c1c; padding:2px 6px; border-radius:4px; font-weight:700;" title="' . esc_attr($location_wp) . ' Acil Konum Paylaşımı">🚨 ' . esc_html($location_wp) . '</span>';
            }
            echo '</div>';
            echo '<small style="color:#64748b; font-size:10.5px;">Toplam <strong>' . esc_html($total_leads) . '</strong> dönüşüm</small>';
            echo '</div>';
        } else {
            echo '<span style="color:#cbd5e1; font-size:12px;">-</span>';
        }
    }

    if ( $column === 'mechanic_vip' ) {
        $is_vip = get_post_meta( $post_id, '_mechanic_is_featured', true ) === '1';
        $toggle_url = wp_nonce_url( admin_url( 'admin.php?action=toggle_vip_mechanic&post_id=' . $post_id ), 'toggle_vip_' . $post_id );
        if ( $is_vip ) {
            echo '<span style="color:#d97706; font-weight:bold;">👑 VIP</span> <a href="' . esc_url($toggle_url) . '" style="font-size:11px; color:#dc2626; margin-left:6px;">[Kaldır]</a>';
        } else {
            echo '<span style="color:#94a3b8;">-</span> <a href="' . esc_url($toggle_url) . '" style="font-size:11px; color:#16a34a; margin-left:6px;">[⭐ VIP Yap]</a>';
        }
    }
}, 10, 2 );

/**
 * Yeni veya güncellenen firma ilanı için yöneticiye HTML e-posta bildirimi gönderir
 */
function ototamir_notify_admin_new_mechanic( $post_id, $is_edit = false ) {
    $post = get_post( $post_id );
    if ( ! $post || $post->post_type !== 'mechanic' ) return false;

    if ( current_user_can( 'administrator' ) && $is_edit ) {
        return false;
    }

    $admin_email = get_option( 'admin_email' );
    if ( empty( $admin_email ) ) return false;

    $author_id = (int) $post->post_author;
    $author = get_userdata( $author_id );
    $author_name = $author ? ( ! empty($author->first_name) ? trim($author->first_name . ' ' . $author->last_name) : $author->display_name ) : 'Bilinmeyen Kullanıcı';
    $author_login = $author ? $author->user_login : '-';
    $author_email = $author ? $author->user_email : '-';
    $author_phone = get_user_meta( $author_id, 'billing_phone', true );
    if ( empty( $author_phone ) ) {
        $author_phone = get_user_meta( $author_id, 'phone', true );
    }
    if ( empty( $author_phone ) ) {
        $author_phone = get_post_meta( $post_id, '_mechanic_phone', true );
    }

    $firm_phone = get_post_meta( $post_id, '_mechanic_phone', true );

    $cities = wp_get_object_terms( $post_id, 'mechanic_city', array('fields' => 'names') );
    $city_str = ( ! empty($cities) && ! is_wp_error($cities) ) ? implode( ', ', $cities ) : '-';

    $districts = wp_get_object_terms( $post_id, 'mechanic_district', array('fields' => 'names') );
    $district_str = ( ! empty($districts) && ! is_wp_error($districts) ) ? implode( ', ', $districts ) : '-';

    $services = wp_get_object_terms( $post_id, 'service_type', array('fields' => 'names') );
    $service_str = ( ! empty($services) && ! is_wp_error($services) ) ? implode( ', ', $services ) : '-';

    $action_title = $is_edit ? 'Firma Güncellendi (Onay Bekliyor)' : 'Yeni Firma Eklendi (Onay Bekliyor)';
    $site_name = get_bloginfo( 'name' );
    $subject = sprintf( '[%s] %s: %s', $site_name, $action_title, $post->post_title );

    $admin_edit_url = admin_url( 'post.php?post=' . $post_id . '&action=edit' );
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $site_name . ' <' . ( $admin_email ) . '>',
        'Reply-To: ' . ( $author_email !== '-' ? $author_email : $admin_email )
    );

    $date_now = current_time( 'd.m.Y H:i' );

    $message = '<!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . esc_html( $subject ) . '</title>
    </head>
    <body style="margin:0; padding:24px 0; background-color:#f1f5f9; font-family:Arial, Helvetica, sans-serif; -webkit-font-smoothing:antialiased; color:#1e293b;">
        <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#f1f5f9">
            <tr>
                <td align="center" style="padding:10px 15px;">
                    <table width="600" border="0" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background:#ffffff; border-radius:10px; overflow:hidden; border:1px solid #e2e8f0;">
                        <!-- HEADER -->
                        <tr>
                            <td style="background-color:#0f172a; padding:22px 30px; border-bottom:3px solid #ea580c; text-align:left;">
                                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td>
                                            <span style="font-size:20px; font-weight:bold; color:#ffffff; letter-spacing:-0.5px;">🛠️ ' . esc_html( $site_name ) . '</span>
                                        </td>
                                        <td align="right">
                                            <span style="background-color:#ea580c; color:#ffffff; font-size:11px; font-weight:bold; padding:4px 10px; border-radius:12px; text-transform:uppercase;">YÖNETİCİ BİLDİRİMİ</span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <!-- BODY -->
                        <tr>
                            <td style="padding:32px 30px;">
                                <div style="display:inline-block; background-color:#ffedd5; color:#c2410c; font-size:12px; font-weight:bold; padding:4px 10px; border-radius:4px; margin-bottom:12px;">
                                    ' . esc_html( $action_title ) . '
                                </div>
                                <h1 style="margin:0 0 16px 0; font-size:20px; color:#0f172a; font-weight:bold;">
                                    ' . esc_html( $post->post_title ) . '
                                </h1>
                                <p style="margin:0 0 20px 0; font-size:14px; line-height:1.6; color:#475569;">
                                    Sitenize yeni bir firma kaydı yapıldı veya mevcut bir ilan güncellenerek onayınıza sunuldu. Detaylar aşağıda yer almaktadır:
                                </p>

                                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; margin-bottom:24px;">
                                    <tr>
                                        <td style="padding:16px 20px;">
                                            <table width="100%" border="0" cellpadding="6" cellspacing="0" style="font-size:13px;">
                                                <tr>
                                                    <td width="130" style="color:#64748b; font-weight:bold;">Firma Adı:</td>
                                                    <td style="color:#0f172a; font-weight:bold;">' . esc_html( $post->post_title ) . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="color:#64748b; font-weight:bold;">Ekleyen Kullanıcı:</td>
                                                    <td style="color:#0f172a;">' . esc_html( $author_name ) . ' (' . esc_html( $author_login ) . ')</td>
                                                </tr>
                                                <tr>
                                                    <td style="color:#64748b; font-weight:bold;">Ekleyen E-posta:</td>
                                                    <td><a href="mailto:' . esc_attr( $author_email ) . '" style="color:#0284c7; text-decoration:none;">' . esc_html( $author_email ) . '</a></td>
                                                </tr>
                                                <tr>
                                                    <td style="color:#64748b; font-weight:bold;">İletişim Telefonu:</td>
                                                    <td style="color:#0f172a; font-weight:bold;">' . esc_html( $author_phone ?: ( $firm_phone ?: '-' ) ) . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="color:#64748b; font-weight:bold;">Şehir / İlçe:</td>
                                                    <td style="color:#0f172a;">' . esc_html( $city_str . ' / ' . $district_str ) . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="color:#64748b; font-weight:bold;">Hizmet Alanları:</td>
                                                    <td style="color:#0f172a;">' . esc_html( $service_str ) . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="color:#64748b; font-weight:bold;">İşlem Zamanı:</td>
                                                    <td style="color:#64748b;">' . esc_html( $date_now ) . '</td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                <!-- BUTTON -->
                                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin:20px 0 10px 0;">
                                    <tr>
                                        <td align="center">
                                            <a href="' . esc_url( $admin_edit_url ) . '" target="_blank" style="background-color:#ea580c; color:#ffffff; font-size:14px; font-weight:bold; text-decoration:none; padding:12px 28px; border-radius:6px; display:inline-block;">
                                                İlanı İncele ve Onayla →
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <!-- FOOTER -->
                        <tr>
                            <td style="background-color:#f8fafc; border-top:1px solid #e2e8f0; padding:16px 30px; text-align:center; font-size:12px; color:#94a3b8;">
                                Bu e-posta <strong>' . esc_html( $site_name ) . '</strong> platformundaki yeni usta bildirim sistemi tarafından gönderilmiştir.
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';

    return wp_mail( $admin_email, $subject, $message, $headers );
}

add_action( 'admin_action_toggle_vip_mechanic', function() {
    if ( ! isset( $_GET['post_id'] ) || ! isset( $_GET['_wpnonce'] ) ) return;
    $post_id = intval( $_GET['post_id'] );
    if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'toggle_vip_' . $post_id ) ) wp_die('Yetkisiz işlem.');
    if ( ! current_user_can( 'edit_post', $post_id ) ) wp_die('İzniniz yok.');

    $current = get_post_meta( $post_id, '_mechanic_is_featured', true );
    if ( $current === '1' ) {
        delete_post_meta( $post_id, '_mechanic_is_featured' );
    } else {
        update_post_meta( $post_id, '_mechanic_is_featured', '1' );
        if ( function_exists( 'ototamir_notify_user_vip_activated' ) ) {
            ototamir_notify_user_vip_activated( $post_id );
        }
    }
    wp_redirect( wp_get_referer() );
    exit;
} );

/**
 * Firma ilanı yönetici tarafından onaylanıp yayınlandığında ustaya tebrik e-postası gönderir
 */
function ototamir_notify_user_mechanic_approved( $post_id ) {
    $post = get_post( $post_id );
    if ( ! $post || $post->post_type !== 'mechanic' ) return false;

    $author_id = (int) $post->post_author;
    if ( $author_id <= 0 ) return false;

    $user = get_userdata( $author_id );
    if ( ! $user || empty( $user->user_email ) ) return false;

    $site_name = get_bloginfo( 'name' );
    $admin_email = get_option( 'admin_email' );
    $subject = sprintf( 'Tebrikler! "%s" İlanınız Onaylandı ve Yayında! - %s', $post->post_title, $site_name );

    $post_url = get_permalink( $post_id );
    $profile_url = home_url( '/profil' );

    $greeting_name = '';
    if ( ! empty( $user->first_name ) ) {
        $greeting_name = trim( $user->first_name . ' ' . $user->last_name );
    } elseif ( ! empty( $user->display_name ) && $user->display_name !== $user->user_login ) {
        $greeting_name = $user->display_name;
    } else {
        $greeting_name = $post->post_title . ' Yetkilisi';
    }

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $site_name . ' <' . ( $admin_email ) . '>',
        'Reply-To: ' . $admin_email
    );

    $message = '<!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . esc_html( $subject ) . '</title>
    </head>
    <body style="margin:0; padding:24px 0; background-color:#f1f5f9; font-family:Arial, Helvetica, sans-serif; -webkit-font-smoothing:antialiased; color:#1e293b;">
        <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#f1f5f9">
            <tr>
                <td align="center" style="padding:10px 15px;">
                    <table width="600" border="0" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background:#ffffff; border-radius:10px; overflow:hidden; border:1px solid #e2e8f0;">
                        <!-- HEADER -->
                        <tr>
                            <td style="background-color:#0f172a; padding:22px 30px; border-bottom:3px solid #16a34a; text-align:left;">
                                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td>
                                            <span style="font-size:20px; font-weight:bold; color:#ffffff; letter-spacing:-0.5px;">🛠️ ' . esc_html( $site_name ) . '</span>
                                        </td>
                                        <td align="right">
                                            <span style="background-color:#16a34a; color:#ffffff; font-size:11px; font-weight:bold; padding:4px 10px; border-radius:12px; text-transform:uppercase;">ONAYLANDI</span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <!-- BODY -->
                        <tr>
                            <td style="padding:32px 30px;">
                                <div style="display:inline-block; background-color:#dcfce7; color:#15803d; font-size:12px; font-weight:bold; padding:4px 10px; border-radius:4px; margin-bottom:12px;">
                                    ✓ İLANINIZ YAYINDA
                                </div>
                                <h1 style="margin:0 0 16px 0; font-size:22px; color:#0f172a; font-weight:bold;">
                                    Tebrikler, İlanınız Başarıyla Yayınlandı!
                                </h1>
                                <p style="margin:0 0 14px 0; font-size:15px; line-height:1.6; color:#334155;">
                                    Merhaba <strong>' . esc_html( $greeting_name ) . '</strong>,
                                </p>
                                <p style="margin:0 0 20px 0; font-size:14px; line-height:1.6; color:#475569;">
                                    <strong>"' . esc_html( $post->post_title ) . '"</strong> başlıklı firma profiliniz incelenerek onaylandı ve yayına alındı. Artık bölgenizdeki araç sahipleri doğrudan sizinle iletişime geçebilir.
                                </p>

                                <!-- HIGHLIGHT CARD -->
                                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; margin-bottom:24px;">
                                    <tr>
                                        <td style="padding:18px 20px; text-align:center;">
                                            <div style="font-size:12px; color:#15803d; font-weight:bold; text-transform:uppercase; margin-bottom:4px;">YAYINLANAN İLANINIZ</div>
                                            <div style="font-size:17px; font-weight:bold; color:#0f172a; margin-bottom:14px;">' . esc_html( $post->post_title ) . '</div>
                                            <a href="' . esc_url( $post_url ) . '" target="_blank" style="background-color:#16a34a; color:#ffffff; font-size:14px; font-weight:bold; text-decoration:none; padding:11px 26px; border-radius:6px; display:inline-block;">
                                                İlanınızı İnceleyin →
                                            </a>
                                        </td>
                                    </tr>
                                </table>

                                <!-- TIPS BOX -->
                                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color:#f8fafc; border-left:4px solid #0284c7; border-radius:4px; margin-bottom:20px;">
                                    <tr>
                                        <td style="padding:14px 16px; font-size:13px; line-height:1.6; color:#475569;">
                                            <strong style="color:#0f172a;">💡 Usta İpucu:</strong> Daha fazla müşteri çekmek için kapak fotoğrafınızı güncelleyebilir, sunduğunuz hizmetleri zenginleştirebilir ve müşterilerinizden profilinize yorum bırakmalarını isteyebilirsiniz.<br>
                                            <a href="' . esc_url( $profile_url ) . '" target="_blank" style="color:#0284c7; font-weight:bold; text-decoration:underline; display:inline-block; margin-top:6px;">Usta Panelinize Gitmek İçin Tıklayın →</a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <!-- FOOTER -->
                        <tr>
                            <td style="background-color:#f8fafc; border-top:1px solid #e2e8f0; padding:16px 30px; text-align:center; font-size:12px; color:#94a3b8;">
                                Bu e-posta <strong>' . esc_html( $site_name ) . '</strong> platformundaki üyeliğiniz kapsamında gönderilmiştir.
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';

    return wp_mail( $user->user_email, $subject, $message, $headers );
}

/**
 * Firma ilanı VIP yapıldığında ustaya özel VIP bildirim e-postası gönderir
 */
function ototamir_notify_user_vip_activated( $post_id ) {
    $post = get_post( $post_id );
    if ( ! $post || $post->post_type !== 'mechanic' ) return false;

    $author_id = (int) $post->post_author;
    if ( $author_id <= 0 ) return false;

    $user = get_userdata( $author_id );
    if ( ! $user || empty( $user->user_email ) ) return false;

    $site_name = get_bloginfo( 'name' );
    $admin_email = get_option( 'admin_email' );
    $subject = sprintf( 'Tebrikler! "%s" İlanınız VIP Özellik Kazandı! - %s', $post->post_title, $site_name );

    $post_url = get_permalink( $post_id );

    $greeting_name = '';
    if ( ! empty( $user->first_name ) ) {
        $greeting_name = trim( $user->first_name . ' ' . $user->last_name );
    } elseif ( ! empty( $user->display_name ) && $user->display_name !== $user->user_login ) {
        $greeting_name = $user->display_name;
    } else {
        $greeting_name = $post->post_title . ' Yetkilisi';
    }

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $site_name . ' <' . ( $admin_email ) . '>',
        'Reply-To: ' . $admin_email
    );

    $message = '<!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . esc_html( $subject ) . '</title>
    </head>
    <body style="margin:0; padding:24px 0; background-color:#f1f5f9; font-family:Arial, Helvetica, sans-serif; -webkit-font-smoothing:antialiased; color:#1e293b;">
        <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#f1f5f9">
            <tr>
                <td align="center" style="padding:10px 15px;">
                    <table width="600" border="0" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background:#ffffff; border-radius:10px; overflow:hidden; border:1px solid #e2e8f0;">
                        <!-- HEADER -->
                        <tr>
                            <td style="background-color:#0f172a; padding:22px 30px; border-bottom:3px solid #f59e0b; text-align:left;">
                                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td>
                                            <span style="font-size:20px; font-weight:bold; color:#ffffff; letter-spacing:-0.5px;">🛠️ ' . esc_html( $site_name ) . '</span>
                                        </td>
                                        <td align="right">
                                            <span style="background-color:#f59e0b; color:#0f172a; font-size:11px; font-weight:bold; padding:4px 10px; border-radius:12px; text-transform:uppercase;">VIP VİTRİN AYRICALIĞI</span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <!-- BODY -->
                        <tr>
                            <td style="padding:32px 30px;">
                                <div style="display:inline-block; background-color:#fef3c7; color:#b45309; font-size:12px; font-weight:bold; padding:4px 10px; border-radius:4px; margin-bottom:12px;">
                                    ★ VIP STATÜSÜ AKTİF EDİLDİ
                                </div>
                                <h1 style="margin:0 0 16px 0; font-size:22px; color:#0f172a; font-weight:bold;">
                                    Tebrikler! İlanınız VIP Özellik Kazandı!
                                </h1>
                                <p style="margin:0 0 14px 0; font-size:15px; line-height:1.6; color:#334155;">
                                    Merhaba <strong>' . esc_html( $greeting_name ) . '</strong>,
                                </p>
                                <p style="margin:0 0 20px 0; font-size:14px; line-height:1.6; color:#475569;">
                                    Müjdeli bir haberimiz var! <strong>"' . esc_html( $post->post_title ) . '"</strong> isimli işletme ilanınız yönetici tarafından <strong>VIP Vitrin Statüsüne</strong> yükseltildi. Artık bölgenizdeki aramalarda en önde yer alacaksınız!
                                </p>

                                <!-- VIP FEATURES CARD -->
                                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color:#fffdf5; border:1px solid #fde68a; border-left:4px solid #f59e0b; border-radius:6px; margin-bottom:24px;">
                                    <tr>
                                        <td style="padding:18px 20px;">
                                            <div style="font-size:13px; font-weight:bold; color:#92400e; margin-bottom:10px; text-transform:uppercase;">
                                                VIP Üyeliğinizin Kazandırdığı Ayrıcalıklar:
                                            </div>
                                            <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size:13px; line-height:1.6; color:#475569;">
                                                <tr>
                                                    <td width="20" valign="top" style="color:#f59e0b; font-weight:bold;">✓</td>
                                                    <td><strong style="color:#0f172a;">Aramalarda Zirve:</strong> Şehrinizde ve kategorinizde yapılan tüm aramalarda en üst sırada listelenirsiniz.</td>
                                                </tr>
                                                <tr>
                                                    <td width="20" valign="top" style="color:#f59e0b; font-weight:bold;">✓</td>
                                                    <td><strong style="color:#0f172a;">Özel Altın Rozet:</strong> İlanınız özel VIP etiketi ve dikkat çekici vitrin tasarımıyla hemen fark edilir.</td>
                                                </tr>
                                                <tr>
                                                    <td width="20" valign="top" style="color:#f59e0b; font-weight:bold;">✓</td>
                                                    <td><strong style="color:#0f172a;">Maksimum Müşteri:</strong> Bölgenizdeki araç sahipleri doğrudan dükkanınızı görerek arama ve yol tarifi butonlarına tıklar.</td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                <!-- BUTTON -->
                                <table width="100%" border="0" cellpadding="0" cellspacing="0" style="margin:20px 0 10px 0;">
                                    <tr>
                                        <td align="center">
                                            <a href="' . esc_url( $post_url ) . '" target="_blank" style="background-color:#ea580c; color:#ffffff; font-size:14px; font-weight:bold; text-decoration:none; padding:13px 30px; border-radius:6px; display:inline-block;">
                                                VIP İlanınızı Görüntüleyin →
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <!-- FOOTER -->
                        <tr>
                            <td style="background-color:#f8fafc; border-top:1px solid #e2e8f0; padding:16px 30px; text-align:center; font-size:12px; color:#94a3b8;">
                                <strong>' . esc_html( $site_name ) . '</strong> VIP Vitrin Hizmeti • Bol ve bereketli işler dileriz!
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';

    return wp_mail( $user->user_email, $subject, $message, $headers );
}

/**
 * İlan durumu 'publish' olduğunda (onaylandığında) bildirim gönderen hook
 */
add_action( 'transition_post_status', function( $new_status, $old_status, $post ) {
    if ( ! $post || $post->post_type !== 'mechanic' ) {
        return;
    }

    // Durum yayında (publish) olduysa ve önceki durum yayında değilse (onaylanma anı)
    if ( $new_status === 'publish' && $old_status !== 'publish' ) {
        // İlanın daha önce onay bildirimi alıp almadığını kontrol et (mükerrer gönderim önleme)
        $already_notified = get_post_meta( $post->ID, '_mechanic_approval_notified', true );
        if ( ! $already_notified ) {
            if ( function_exists( 'ototamir_notify_user_mechanic_approved' ) ) {
                ototamir_notify_user_mechanic_approved( $post->ID );
            }
            update_post_meta( $post->ID, '_mechanic_approval_notified', current_time( 'mysql' ) );
        }
    }
}, 10, 3 );

// 11. Öne Çıkan / VIP Vitrin Ustaları Ön Yüz Listelemelerinde En Üstte Sırala
add_filter( 'posts_clauses', function( $clauses, $query ) {
    global $wpdb;
    if ( ! is_admin() && $query->get('post_type') === 'mechanic' && $query->get('suppress_filters') !== true ) {
        // Tekil sayfa veya profil panel sorgusu değilse uygula
        if ( ! $query->is_single() && ! $query->get('author') ) {
            $clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS pm_featured ON ({$wpdb->posts}.ID = pm_featured.post_id AND pm_featured.meta_key = '_mechanic_is_featured') ";
            $clauses['orderby'] = " (CASE WHEN pm_featured.meta_value = '1' THEN 1 ELSE 0 END) DESC, " . $clauses['orderby'];
        }
    }
    return $clauses;
}, 10, 2 );

function get_mechanic_rating_data($post_id) {
    $comments = get_comments(array('post_id' => $post_id, 'status' => 'approve'));
    $total_rating = 0;
    $count = 0;
    
    foreach($comments as $comment) {
        $rating = get_comment_meta($comment->comment_ID, 'mechanic_rating', true);
        if($rating) {
            $total_rating += intval($rating);
            $count++;
        }
    }
    
    $avg = $count > 0 ? round($total_rating / $count, 1) : 0;
    return array('avg' => $avg, 'count' => $count);
}


// --- ZİYARET SAYACI (POST VIEWS) ---
function ototamir_track_post_views($post_id = null) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }
    if ( ! $post_id || get_post_type($post_id) !== 'mechanic' ) return;
    
    $count = (int) get_post_meta($post_id, '_mechanic_views_count', true);
    $count++;
    update_post_meta($post_id, '_mechanic_views_count', $count);
}

function ototamir_get_post_views($post_id = null) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }
    $count = (int) get_post_meta($post_id, '_mechanic_views_count', true);
    return number_format($count, 0, ',', '.');
}

/**
 * Aynı isim ve aynı ilde mükerrer ilan kontrolü
 */
function ototamir_check_duplicate_mechanic($title, $city_id, $exclude_post_id = 0) {
    global $wpdb;
    $title_clean = mb_strtolower(trim($title), 'UTF-8');
    
    $sql = "
        SELECT p.ID 
        FROM {$wpdb->posts} p
        JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
        JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'mechanic_city'
        WHERE p.post_type = 'mechanic'
          AND LOWER(TRIM(p.post_title)) = %s
          AND tt.term_id = %d
    ";
    if ($exclude_post_id > 0) {
        $sql .= " AND p.ID != " . intval($exclude_post_id);
    }
    $sql .= " LIMIT 1";

    return $wpdb->get_var($wpdb->prepare($sql, $title_clean, intval($city_id)));
}

// --- ÜYELİK SİSTEMİ VE GÜVENLİK ---

// 1. Ziyaretçileri WP-Admin'den Engelle
add_action( 'admin_init', 'ototamir_block_wp_admin' );
function ototamir_block_wp_admin() {
    if ( is_admin() && ! current_user_can( 'administrator' ) && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
        wp_safe_redirect( home_url( '/profil' ) );
        exit;
    }
}

// 2. Sayfaları Otomatik Oluştur (Giriş, Kayıt, Profil, Arıza Sihirbazı, Çekici)
function auto_create_membership_pages() {
    $pages = array(
        'giris' => array( 'title' => 'Giriş Yap', 'template' => '' ),
        'kayit' => array( 'title' => 'Kayıt Ol', 'template' => '' ),
        'profil' => array( 'title' => 'Profilim', 'template' => '' ),
        'ariza-kodlari-ve-gosterge-lambalari' => array( 'title' => 'Arıza Kodları & Gösterge İkaz Lambaları', 'template' => 'page-ariza-kodlari.php' ),
        'oto-cekici-yol-yardim' => array( 'title' => 'En Yakın Oto Çekici & Acil Yol Yardım', 'template' => 'page-acil-cekici.php' ),
        'akaryakit-fiyatlari-ve-rota-hesaplama' => array( 'title' => 'Canlı Akaryakıt Fiyatları & Rota Yakıt Hesaplama', 'template' => 'page-akaryakit.php' ),
        'kronik-arizalar-ve-arac-rehberi' => array( 'title' => 'Kronik Arızalar & Araç Alım Rehberi', 'template' => 'page-kronik-arizalar.php' ),
        'kaza-ani-asistani-ve-tutanak' => array( 'title' => 'Kaza Anı Asistanı & Kaza Tespit Tutanağı Rehberi', 'template' => 'page-kaza-asistani.php' ),
    );
    foreach($pages as $slug => $data) {
        $page = get_page_by_path( $slug );
        if ( ! $page ) {
            $page_id = wp_insert_post( array(
                'post_title'     => $data['title'],
                'post_name'      => $slug,
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'post_author'    => 1,
            ) );
            if ( $page_id && ! empty( $data['template'] ) ) {
                update_post_meta( $page_id, '_wp_page_template', $data['template'] );
            }
        } elseif ( ! empty( $data['template'] ) ) {
            $current_tpl = get_post_meta( $page->ID, '_wp_page_template', true );
            if ( empty( $current_tpl ) || $current_tpl === 'default' ) {
                update_post_meta( $page->ID, '_wp_page_template', $data['template'] );
            }
        }
    }
}
add_action('init', 'auto_create_membership_pages');

// 3. Çıkış Yaptıktan Sonra Anasayfaya Yönlendir
add_action('wp_logout','ototamir_redirect_after_logout');
function ototamir_redirect_after_logout(){
    wp_safe_redirect( home_url() );
    exit;
}

// 4. Giriş İşlemi (Login Handler)
add_action('init', 'ototamir_handle_login');
function ototamir_handle_login() {
    if ( isset($_POST['ototamir_login_submit']) && isset($_POST['log']) && isset($_POST['pwd']) ) {
        if ( ! wp_verify_nonce( $_POST['ototamir_login_nonce'], 'ototamir_login_action' ) ) return;
        
        $creds = array(
            'user_login'    => sanitize_text_field($_POST['log']),
            'user_password' => $_POST['pwd'],
            'remember'      => isset($_POST['rememberme'])
        );
        
        $user = wp_signon( $creds, is_ssl() );
        
        if ( is_wp_error($user) ) {
            global $login_error;
            $login_error = $user->get_error_message();
        } else {
            $redirect = ! empty( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : home_url('/profil');
            wp_safe_redirect( $redirect );
            exit;
        }
    }
}

// 4.1. Şifre Sıfırlama Talebi (Lost Password Handler)
add_action('init', 'ototamir_handle_lost_password');
function ototamir_handle_lost_password() {
    if ( isset($_POST['ototamir_lostpw_submit']) && ! empty($_POST['user_login']) ) {
        if ( ! wp_verify_nonce( $_POST['ototamir_lostpw_nonce'], 'ototamir_lostpw_action' ) ) return;
        
        global $lostpw_error, $lostpw_success;
        $user_data = trim( sanitize_text_field( $_POST['user_login'] ) );
        
        if ( empty( $user_data ) ) {
            $lostpw_error = 'Lütfen kullanıcı adınızı veya e-posta adresinizi girin.';
            return;
        }
        
        $user = is_email( $user_data ) ? get_user_by( 'email', $user_data ) : get_user_by( 'login', $user_data );
        
        if ( ! $user ) {
            $lostpw_error = 'Bu kullanıcı adı veya e-posta adresiyle eşleşen bir hesap bulunamadı.';
            return;
        }
        
        require_once( ABSPATH . 'wp-includes/pluggable.php' );
        $res = retrieve_password( $user->user_login );
        if ( is_wp_error( $res ) ) {
            $lostpw_error = $res->get_error_message();
        } else {
            $lostpw_success = 'Şifre sıfırlama talimatları e-posta adresinize gönderildi. Lütfen gelen kutunuzu (ve spam klasörünü) kontrol edin.';
        }
    }
}

// 5. Kayıt İşlemi (Register Handler)
add_action('init', 'ototamir_handle_register');
function ototamir_handle_register() {
    if ( isset($_POST['ototamir_register_submit']) ) {
        if ( ! wp_verify_nonce( $_POST['ototamir_register_nonce'], 'ototamir_register_action' ) ) return;
        
        $username = sanitize_user($_POST['user_login']);
        $email    = sanitize_email($_POST['user_email']);
        $password = $_POST['user_pass'];
        $role     = (isset($_POST['user_role']) && $_POST['user_role'] === 'mechanic') ? 'contributor' : 'subscriber';

        global $register_error, $register_success;

        if ( empty($username) || empty($email) || empty($password) ) {
            $register_error = "Lütfen tüm zorunlu alanları doldurun.";
            return;
        }
        
        if ( username_exists( $username ) ) {
            $register_error = "Bu kullanıcı adı zaten başka bir üye tarafından kullanılıyor.";
            return;
        }
        
        if ( email_exists( $email ) ) {
            $register_error = "Bu e-posta adresi ile zaten kayıtlı bir hesap var.";
            return;
        }
        
        if ( strlen($password) < 6 ) {
            $register_error = "Şifreniz güvenliğiniz için en az 6 karakter olmalıdır.";
            return;
        }
        
        $user_id = wp_create_user( $username, $password, $email );
        if ( ! is_wp_error( $user_id ) ) {
            $user = new WP_User( $user_id );
            $user->set_role( $role );
            
            // Auto login after register
            wp_set_current_user( $user_id );
            wp_set_auth_cookie( $user_id );
            
            $redirect = ! empty( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : home_url('/profil');
            wp_safe_redirect( $redirect );
            exit;
        } else {
            $register_error = $user_id->get_error_message();
        }
    }
}

// 6. Favorilere Ekleme (AJAX)
add_action('wp_ajax_ototamir_toggle_favorite', 'ototamir_toggle_favorite');
function ototamir_toggle_favorite() {
    check_ajax_referer( 'ototamir_favorite_nonce', 'nonce', false );

    if ( ! is_user_logged_in() ) {
        wp_send_json_error('Giriş yapmalısınız.');
    }
    
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $user_id = get_current_user_id();
    
    $favorites = get_user_meta($user_id, '_user_favorites', true);
    if ( ! is_array($favorites) ) $favorites = array();
    
    $status = '';
    if ( in_array($post_id, $favorites) ) {
        $favorites = array_diff($favorites, array($post_id));
        $status = 'removed';
    } else {
        $favorites[] = $post_id;
        $status = 'added';
    }
    
    update_user_meta($user_id, '_user_favorites', $favorites);
    wp_send_json_success(array('status' => $status));
}

// Kullanıcının favorisi mi kontrol etme
function is_user_favorite($post_id) {
    if ( ! is_user_logged_in() ) return false;
    $favorites = get_user_meta(get_current_user_id(), '_user_favorites', true);
    if ( ! is_array($favorites) ) return false;
    return in_array($post_id, $favorites);
}

// 7. Otomatik Sayfa Tanımları (Hakkımızda vb.)
function ototamir_ensure_default_pages() {
    $pages = array(
        'hakkimizda' => array(
            'title'    => 'Hakkımızda',
            'template' => 'page-hakkimizda.php',
        ),
        'hizmetler' => array(
            'title'    => 'Hizmetlerimiz',
            'template' => 'page-hizmetler.php',
        ),
        'blog' => array(
            'title'    => 'Blog',
            'template' => 'page-blog.php',
        ),
        'iletisim' => array(
            'title'    => 'İletişim',
            'template' => 'page-iletisim.php',
        ),
        'iller' => array(
            'title'    => '81 İl Rehberi',
            'template' => 'page-iller.php',
        ),
        'cerez-politikasi' => array(
            'title'    => 'Çerez Politikası',
            'template' => 'page-cerez-politikasi.php',
        ),
        'kullanim-kosullari' => array(
            'title'    => 'Kullanım Koşulları',
            'template' => 'page-kullanim-kosullari.php',
        ),
        'gizlilik-politikasi' => array(
            'title'    => 'Gizlilik Politikası',
            'template' => 'page-gizlilik-politikasi.php',
        ),
        'kvkk-aydinlatma-metni' => array(
            'title'    => 'KVKK Aydınlatma Metni',
            'template' => 'page-kvkk.php',
        ),
        'ilan-verme-kurallari' => array(
            'title'    => 'İlan Verme Kuralları',
            'template' => 'page-ilan-kurallari.php',
        ),
        'sss' => array(
            'title'    => 'Sıkça Sorulan Sorular (SSS)',
            'template' => 'page-sss.php',
        ),
        'paketler' => array(
            'title'    => 'Paketler ve Fiyatlandırma',
            'template' => 'page-paketler.php',
        ),
    );

    foreach ($pages as $slug => $data) {
        $page_check = get_page_by_path($slug);
        if ( ! $page_check ) {
            $page_id = wp_insert_post(array(
                'post_title'   => $data['title'],
                'post_name'    => $slug,
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => '',
            ));
            if ( $page_id && ! is_wp_error($page_id) && ! empty($data['template']) ) {
                update_post_meta($page_id, '_wp_page_template', $data['template']);
            }
        }
    }
}
add_action('init', 'ototamir_ensure_default_pages');

/**
 * 81 İl İçin Zengin Yerel SEO Tanıtımı ve Usta Bilgileri
 */
function ototamir_get_city_seo_profile( $city_name ) {
    $city_lower = mb_strtolower( trim($city_name), 'UTF-8' );

    $special_cities = array(
        'adana' => array(
            'desc' => "Adana, Çukurova bölgesinin kalbinde yer alan, D400 ve TEM otoyolu bağlantılarıyla Akdeniz ve Güneydoğu'nun en yoğun araç trafiğine sahip ticaret merkezidir. Şehirde Seyhan, Yüreğir ve Çukurova ilçeleri başta olmak üzere Adana Metal Sanayi Sitesi ve Orta Ölçekli Sanayi Sitesi'nde motor mekanik, oto klima gazı dolumu, LPG dönüşüm ve periyodik bakım alanında deneyimli ustalar hizmet vermektedir. Sıcak iklim koşulları nedeniyle Adana'da özellikle oto klima sistemleri ve motor soğutma suyu/radyatör bakımı en çok talep gören servis hizmetlerindendir.",
            'districts_highlight' => "Seyhan, Çukurova, Yüreğir, Ceyhan ve Kozan",
            'hub_name' => "Adana Metal Sanayi Sitesi & Çukurova Oto Servis Merkezleri"
        ),
        'bursa' => array(
            'desc' => "Bursa, Türkiye'nin otomotiv sanayisinin ana başkenti olarak tanınmakta ve dünya standartlarında araç üretim altyapısıyla oto tamir-bakım sektöründe de benzersiz bir usta tecrübesine ev sahipliği yapmaktadır. Nilüfer Küçük Sanayi Sitesi, Beşevler Sanayi ve Yıldırım Oto Sanayi bölgelerinde motor revizyonu, otomatik şanzıman tamiri, oto kaporta-boya ve bilgisayarlı arıza tespiti konularında uzmanlaşmış yüzlerce özel servis bulunmaktadır.",
            'districts_highlight' => "Nilüfer, Osmangazi, Yıldırım, İnegöl ve Gemlik",
            'hub_name' => "Nilüfer Küçük Sanayi, Beşevler & Bursa Oto Sanayi Siteleri"
        ),
        'ankara' => array(
            'desc' => "Türkiye'nin başkenti Ankara, geniş çevre yolları ve yüz binlerce kayıtlı araç filosuyla oto servis sektörünün en organize olduğu şehirlerdendir. Şaşmaz Oto Sanayi Sitesi, İvedik Organize Sanayi ve OSTİM bölgeleri; binek, hafif ticari ve lüks segment araçların motor mekanik, oto elektrik, egzoz emisyon ve chip tuning işlemlerinde Türkiye geneline referans olan ustalara sahiptir. Ankara'nın sert kış koşulları sebebiyle antifriz, kış lastiği ve akü kontrolleri yoğun ilgi görmektedir.",
            'districts_highlight' => "Etimesgut, Yenimahalle, Keçiören, Çankaya ve Mamak",
            'hub_name' => "Şaşmaz Sanayi Sitesi, OSTİM & İvedik Organize Sanayi"
        ),
        'istanbul' => array(
            'desc' => "İstanbul, 5 milyondan fazla motorlu taşıt trafiğiyle Türkiye'nin en büyük otomotiv servis pazarıdır. Maslak Atatürk Oto Sanayi, Bostancı Oto Sanayi, Ümraniye Kadosan ve İkitelli Bağcılar-Güngören Sanayi Siteleri; her marka ve model araç için yetkili servis kalitesinde hizmet sunan uzman ustaları barındırır. Yoğun dur-kalk trafiğinden ötürü debriyaj balatası, fren diskleri ve şanzıman bakımları İstanbul sürücülerinin en sık başvurduğu işlemler arasındadır.",
            'districts_highlight' => "Kadıköy, Ümraniye, Bağcılar, Pendik ve Esenyurt",
            'hub_name' => "Maslak, Bostancı, İkitelli & Kadosan Sanayi Siteleri"
        ),
        'izmir' => array(
            'desc' => "Ege Bölgesi'nin incisi İzmir, sahil şeridi ve yoğun otoyol ağıyla yüksek araç hareketliliğine sahiptir. Bornova 1., 2. ve 3. Sanayi Siteleri ile Çiğli Atatürk Organize Sanayi ve Karabağlar Oto Sanayi, motor rektifiye, oto klima, oto elektrik ve rot-balans işlemlerinde Ege'nin en çok tercih edilen usta merkezleridir.",
            'districts_highlight' => "Bornova, Karabağlar, Konak, Çiğli ve Buca",
            'hub_name' => "Bornova Sanayi Siteleri, Çiğli & Karabağlar Sanayi"
        ),
        'antalya' => array(
            'desc' => "Antalya, hem yerleşik nüfusunun araç sayısı hem de turizm sezonunda milyonlarca ziyaretçinin yarattığı araç yoğunluğuyla oto tamir sektöründe dinamik bir şehirdir. Akdeniz Sanayi Sitesi ve Yeşil Sanayi Sitesi bünyesinde; sıcak havaya bağlı klima arızaları, motor hararet sorunları, periyodik yağ değişimi ve 7/24 oto çekici yol yardım hizmetleri kesintisiz verilmektedir.",
            'districts_highlight' => "Muratpaşa, Kepez, Alanya, Manavgat ve Konyaaltı",
            'hub_name' => "Akdeniz Sanayi Sitesi & Kepez Yeşil Sanayi"
        ),
        'konya' => array(
            'desc' => "Konya, yüz ölçümü bakımından Türkiye'nin en geniş ili olup tarım, ticaret ve sanayi taşımacılığında kritik bir kavşaktır. Motorlu Sanayi Sitesi, Marangozlar Sanayi ve Zafer Sanayi Siteleri; ağır vasıta, hafif ticari ve binek araçların motor rektifiyesi, dişli ve diferansiyel onarımlarında zengin bir usta kadrosuna sahiptir.",
            'districts_highlight' => "Selçuklu, Karatay, Meram, Ereğli ve Akşehir",
            'hub_name' => "Motorlu Sanayi Sitesi & Selçuklu Sanayi Merkezleri"
        ),
        'trabzon' => array(
            'desc' => "Karadeniz'in en stratejik liman ve ticaret kenti Trabzon; sahil yolu, zorlu dağ geçitleri ve virajlı coğrafi koşulları nedeniyle araçların yürüyen aksam ve fren sistemlerinin titizlikle bakılması gereken bir şehirdir. Değirmendere Sanayi Sitesi başta olmak üzere Ortahisar ve Akçaabat ilçelerindeki ustalar; 4x4 arazi aracı bakımları, fren balata değişimi ve 7/24 yol yardım desteğinde uzmanlaşmıştır.",
            'districts_highlight' => "Ortahisar, Akçaabat, Yomra, Of ve Araklı",
            'hub_name' => "Değirmendere Sanayi Sitesi & Trabzon Oto Sanayi"
        ),
        'yozgat' => array(
            'desc' => "İç Anadolu'nun önemli geçiş güzergahlarından olan Yozgat, D200 karayolu ve çevre bağlantılarıyla yoğun transit trafiğe ev sahipliği yapar. Yozgat Merkez Sanayi Sitesi ile Sorgun ve Boğazlıyan ilçelerindeki oto tamircileri; motor revizyonu, fren sistemleri, kışlık antifriz ve akü kontrolleri ile yolda kalan sürücülere acil çekici ve yol yardım hizmetlerini güvenle sunmaktadır.",
            'districts_highlight' => "Merkez, Sorgun, Akdağmadeni, Yerköy ve Boğazlıyan",
            'hub_name' => "Yozgat Merkez Küçük Sanayi Sitesi & Sorgun Sanayi"
        ),
    );

    if ( isset($special_cities[$city_lower]) ) {
        return $special_cities[$city_lower];
    }

    return array(
        'desc' => "{$city_name}, bölgesel ulaşım ağları ve artan motorlu araç filosuyla oto servis, periyodik bakım ve acil tamir hizmetlerinde kilit bir konuma sahiptir. Şehir genelindeki sanayi siteleri ve özel servis noktalarında; binek, SUV ve hafif ticari araçlar için motor mekanik, oto elektrik, fren-ön düzen, kaporta-boya ve 7/24 oto kurtarıcı yol yardım hizmetleri deneyimli ustalar tarafından verilmektedir. Bölgenin mevsimsel koşullarına uygun kışlık ve yazlık periyodik araç bakımları hem sürüş güvenliğinizi artırır hem de yakıt tasarrufu sağlar.",
        'districts_highlight' => "{$city_name} Merkez ve tüm çevre ilçeler",
        'hub_name' => "{$city_name} Sanayi Sitesi & Yetkili Özel Servisleri"
    );
}

/* ==========================================================================
   8. GELİŞMİŞ SEO MOTORU (META TAGS, SCHEMA.ORG, OPEN GRAPH & BAŞLIKLAR)
   ========================================================================== */

// 8.1. Akıllı ve Dinamik Title (Başlık) Filtresi
function ototamir_custom_seo_title( $title ) {
    $site_name = 'OtoTamirciBul';

    if ( is_front_page() ) {
        return "OtoTamirciBul - Türkiye'nin #1 Numaralı Oto Tamirci Rehberi";
    }

    if ( is_singular( 'mechanic' ) ) {
        $post_id = get_the_ID();
        $title_text = get_the_title( $post_id );
        $cities = wp_get_post_terms( $post_id, 'mechanic_city' );
        $districts = wp_get_post_terms( $post_id, 'mechanic_district' );
        $city = ( $cities && ! is_wp_error( $cities ) ) ? $cities[0]->name : '';
        $district = ( $districts && ! is_wp_error( $districts ) ) ? $districts[0]->name : '';
        $loc = trim( "$district $city" );

        if ( $loc ) {
            return "{$title_text} - {$loc} Oto Tamirci & Servis | {$site_name}";
        }
        return "{$title_text} - Onaylı Oto Tamir Servisi | {$site_name}";
    }

    if ( is_post_type_archive( 'mechanic' ) || is_tax( array( 'mechanic_city', 'mechanic_district', 'service_type', 'car_brand' ) ) ) {
        $city_name = '';
        $dist_name = '';
        $service_name = '';
        $brand_name = '';
        $model_name = '';

        if ( is_tax() ) {
            $q = get_queried_object();
            if ( $q && ! is_wp_error( $q ) ) {
                if ( $q->taxonomy === 'mechanic_city' ) $city_name = $q->name;
                if ( $q->taxonomy === 'mechanic_district' ) $dist_name = $q->name;
                if ( $q->taxonomy === 'service_type' ) $service_name = $q->name;
                if ( $q->taxonomy === 'car_brand' ) {
                    if ( ! empty( $q->parent ) ) {
                        $model_name = $q->name;
                        $parent_b = get_term( $q->parent, 'car_brand' );
                        if ( $parent_b && ! is_wp_error( $parent_b ) ) {
                            $brand_name = $parent_b->name;
                        }
                    } else {
                        $brand_name = $q->name;
                    }
                }
            }
        }

        if ( isset( $_GET['mechanic_city'] ) && ! empty( $_GET['mechanic_city'] ) ) {
            $c_term = get_term_by( 'slug', sanitize_text_field( $_GET['mechanic_city'] ), 'mechanic_city' );
            if ( $c_term ) $city_name = $c_term->name;
        }

        if ( isset( $_GET['mechanic_district'] ) && ! empty( $_GET['mechanic_district'] ) ) {
            $dist_name = sanitize_text_field( $_GET['mechanic_district'] );
        }

        if ( isset( $_GET['service_type'] ) && ! empty( $_GET['service_type'] ) ) {
            $s_term = get_term_by( 'slug', sanitize_text_field( $_GET['service_type'] ), 'service_type' );
            if ( $s_term ) $service_name = $s_term->name;
        }

        if ( isset( $_GET['car_model'] ) && ! empty( $_GET['car_model'] ) ) {
            $m_term = get_term_by( 'slug', sanitize_text_field( $_GET['car_model'] ), 'car_brand' );
            if ( $m_term ) {
                $model_name = $m_term->name;
                if ( empty( $brand_name ) && ! empty( $m_term->parent ) ) {
                    $parent_b = get_term( $m_term->parent, 'car_brand' );
                    if ( $parent_b && ! is_wp_error( $parent_b ) ) $brand_name = $parent_b->name;
                }
            }
        }

        if ( isset( $_GET['car_brand'] ) && ! empty( $_GET['car_brand'] ) ) {
            $b_term = get_term_by( 'slug', sanitize_text_field( $_GET['car_brand'] ), 'car_brand' );
            if ( $b_term ) {
                if ( ! empty( $b_term->parent ) ) {
                    $model_name = $b_term->name;
                    $parent_b = get_term( $b_term->parent, 'car_brand' );
                    if ( $parent_b && ! is_wp_error( $parent_b ) ) $brand_name = $parent_b->name;
                } else {
                    $brand_name = $b_term->name;
                }
            }
        }

        $loc_prefix = '';
        if ( $city_name && $dist_name ) {
            $loc_prefix = "{$city_name} {$dist_name} ";
        } elseif ( $city_name ) {
            $loc_prefix = "{$city_name} ";
        }

        if ( $brand_name && $model_name ) {
            return "Size En Yakın {$loc_prefix}{$brand_name} {$model_name} Oto Tamircileri | {$site_name}";
        } elseif ( $brand_name ) {
            return "Size En Yakın {$loc_prefix}{$brand_name} Oto Tamircileri & Özel Servisleri | {$site_name}";
        } elseif ( $city_name && $dist_name && $service_name ) {
            return "{$city_name} {$dist_name} {$service_name} Ustaları & Fiyatları (2026) | {$site_name}";
        } elseif ( $city_name && $dist_name ) {
            return "{$city_name} {$dist_name} En İyi Oto Tamircileri & Tamir Servisleri | {$site_name}";
        } elseif ( $city_name ) {
            return "{$city_name} Oto Tamircileri, En Yakın Servis ve Ustalar | {$site_name}";
        } elseif ( $service_name ) {
            return "{$service_name} Ustaları & Güvenilir Özel Servisler | {$site_name}";
        }
        return "Türkiye Geneli Oto Tamircileri & Yetkili Özel Servisler | {$site_name}";
    }

    if ( is_singular( 'post' ) ) {
        return get_the_title() . " | {$site_name} Blog";
    }

    if ( is_page() ) {
        $p_type = get_post_meta( get_the_ID(), '_ototamir_page_type', true );
        if ( $p_type === 'brand_model_landing' || $p_type === 'brand_landing' ) {
            $b = get_post_meta( get_the_ID(), '_ototamir_target_brand', true );
            $m = get_post_meta( get_the_ID(), '_ototamir_target_model', true );
            if ( $m ) {
                return "Size En Yakın {$b} {$m} Oto Tamircileri & Servisleri (2026) | {$site_name}";
            } else {
                return "Size En Yakın {$b} Oto Tamircileri & Yetkili Özel Servisleri (2026) | {$site_name}";
            }
        }
        return get_the_title() . " | {$site_name}";
    }

    return $title;
}
add_filter( 'pre_get_document_title', 'ototamir_custom_seo_title', 20 );


// 8.2. Dinamik Meta Description, Canonical ve OpenGraph Etiketleri
function ototamir_seo_meta_tags() {
    // Popüler SEO eklentileri (Yoast, Rank Math, All in One SEO, SEOPress) kuruluysa mükerrer basma
    if ( defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'AIOSEO_VERSION' ) || function_exists( 'seopress_init' ) ) {
        return;
    }

    $site_name = 'OtoTamirciBul';
    $theme_logo = get_template_directory_uri() . '/assets/images/logo-main.png';
    $default_img = $theme_logo;
    $desc = "Türkiye'nin #1 Numaralı Oto Tamirci Rehberi. 81 ilde onaylı oto mekanik, elektrik, periyodik bakım ve yol yardım ustaları tek tıkla yanınızda.";
    $title = wp_get_document_title();
    $canonical = home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) );
    $image = $default_img;
    $og_type = 'website';

    if ( is_front_page() ) {
        $canonical = home_url( '/' );
    } elseif ( is_singular( 'mechanic' ) ) {
        $post_id = get_the_ID();
        $og_type = 'profile';
        $canonical = get_permalink( $post_id );
        
        $phone = get_post_meta( $post_id, '_mechanic_phone', true );
        $addr = get_post_meta( $post_id, '_mechanic_address', true );
        $custom_img = get_post_meta( $post_id, '_custom_mechanic_image', true );
        if ( $custom_img ) $image = $custom_img;

        $cities = wp_get_post_terms( $post_id, 'mechanic_city' );
        $districts = wp_get_post_terms( $post_id, 'mechanic_district' );
        $city = ( $cities && ! is_wp_error( $cities ) ) ? $cities[0]->name : '';
        $dist = ( $districts && ! is_wp_error( $districts ) ) ? $districts[0]->name : '';
        $loc = trim( "$dist, $city", ", " );

        $t_name = get_the_title( $post_id );
        $desc = "{$t_name}, {$loc} bölgesinde oto tamir, periyodik bakım ve servis hizmeti sunmaktadır. Telefon: {$phone}. Müşteri yorumları ve yol tarifi için tıklayın.";
    } elseif ( is_singular( 'post' ) ) {
        $post_id = get_the_ID();
        $og_type = 'article';
        $canonical = get_permalink( $post_id );
        if ( has_post_thumbnail( $post_id ) ) {
            $image = get_the_post_thumbnail_url( $post_id, 'large' );
        }
        $post_desc = get_the_excerpt( $post_id );
        if ( ! empty( $post_desc ) ) {
            $desc = wp_trim_words( strip_tags( $post_desc ), 25 );
        }
    } elseif ( is_tax() ) {
        $term = get_queried_object();
        if ( $term && ! is_wp_error( $term ) ) {
            $canonical = get_term_link( $term );
            if ( $term->taxonomy === 'car_brand' ) {
                if ( ! empty( $term->parent ) ) {
                    $parent_b = get_term( $term->parent, 'car_brand' );
                    $p_name = ($parent_b && ! is_wp_error($parent_b)) ? $parent_b->name : '';
                    $title = "Size En Yakın {$p_name} {$term->name} Oto Tamircileri | {$site_name}";
                    $desc = "Size en yakın {$p_name} {$term->name} oto tamircileri, özel servisler, periyodik bakım ve arıza tespit ustaları. Fiyat teklifi alın, yorumları okuyun.";
                } else {
                    $title = "Size En Yakın {$term->name} Oto Tamircileri & Özel Servisleri | {$site_name}";
                    $desc = "{$term->name} araçlarınıza özel onaylı oto tamir servisleri, motor, mekanik, elektrik ve bakım ustaları rehberi.";
                }
            } else {
                $desc = $term->name . " alanında hizmet veren en iyi oto tamircileri, özel servisler ve yol yardım ustaları. İletişim, adres ve yorumlar.";
            }
        }
    } elseif ( is_post_type_archive( 'mechanic' ) || is_page( 'ustalar' ) ) {
        $city = isset( $_GET['mechanic_city'] ) ? sanitize_text_field( $_GET['mechanic_city'] ) : '';
        $dist = isset( $_GET['mechanic_district'] ) ? sanitize_text_field( $_GET['mechanic_district'] ) : '';
        if ( $city && $dist ) {
            $desc = ucfirst($city) . " " . ucfirst($dist) . " ilçesindeki en iyi oto tamircileri, telefon numaraları, adresleri ve müşteri değerlendirmeleri.";
            $canonical = home_url( '/ustalar/?mechanic_city=' . esc_attr($city) . '&mechanic_district=' . urlencode($dist) );
        } elseif ( $city ) {
            $desc = ucfirst($city) . " ilindeki oto tamircileri, 7/24 yol yardım ve özel oto servisleri listesi.";
            $canonical = home_url( '/ustalar/?mechanic_city=' . esc_attr($city) );
        } else {
            $canonical = home_url( '/ustalar/' );
        }
    } elseif ( is_page() ) {
        $p_type = get_post_meta( get_the_ID(), '_ototamir_page_type', true );
        if ( $p_type === 'brand_model_landing' || $p_type === 'brand_landing' ) {
            $b = get_post_meta( get_the_ID(), '_ototamir_target_brand', true );
            $m = get_post_meta( get_the_ID(), '_ototamir_target_model', true );
            $canonical = get_permalink();
            if ( $m ) {
                $desc = "{$b} {$m} için en yakın onaylı oto tamircileri, garantili özel servisler, 2026 periyodik bakım fiyatları, motor yağı ve kronik arıza çözümleri. Hemen inceleyin.";
            } else {
                $desc = "{$b} marka araçlar için en yakın profesyonel oto tamircileri, periyodik bakım ve arıza tespit rehberi. 2026 güncel servis işçilik fiyatlarını karşılaştırın.";
            }
        }
    } elseif ( is_singular( 'emergency_tow' ) ) {
        $post_id = get_the_ID();
        $og_type = 'business.business';
        $canonical = get_permalink( $post_id );
        $phone = get_post_meta( $post_id, '_tow_phone', true );
        $cities = wp_get_post_terms( $post_id, 'tow_city' );
        $city = ( $cities && ! is_wp_error( $cities ) ) ? $cities[0]->name : '';
        $t_name = get_the_title( $post_id );
        $title = "{$t_name} - {$city} 7/24 Acil Oto Çekici ve Yol Yardım | {$site_name}";
        $desc = "{$t_name}, {$city} ve çevresinde 7/24 acil oto çekici, oto kurtarıcı ve yol yardım hizmeti sunmaktadır. Telefon: {$phone}. Hemen ulaşın.";
    } elseif ( is_page( 'acil-cekici' ) ) {
        $title = "7/24 Acil Çekici ve Oto Kurtarıcı Bul | Size En Yakın Çekiciler | {$site_name}";
        $desc = "Türkiye genelinde 81 ilde 7/24 acil oto çekici ve yol yardım hizmeti. Konumunuza en yakın güvenilir çekiciyi anında harita üzerinden bulun ve arayın.";
        $canonical = get_permalink();
    }

    $robots_meta = 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
    if ( is_page( 'profil' ) || is_page( 'giris-yap' ) || is_page( 'kayit-ol' ) || is_search() || is_404() ) {
        $robots_meta = 'noindex, nofollow';
    }
    ?>
    <!-- Standart SEO Meta Etiketleri -->
    <meta name="description" content="<?php echo esc_attr( $desc ); ?>">
    <meta name="robots" content="<?php echo esc_attr( $robots_meta ); ?>">

    <!-- Open Graph (Facebook, WhatsApp vb.) -->
    <meta property="og:locale" content="tr_TR">
    <meta property="og:type" content="<?php echo esc_attr( $og_type ); ?>">
    <meta property="og:title" content="<?php echo esc_attr( $title ); ?>">
    <meta property="og:description" content="<?php echo esc_attr( $desc ); ?>">
    <meta property="og:url" content="<?php echo esc_url( $canonical ); ?>">
    <meta property="og:site_name" content="<?php echo esc_attr( $site_name ); ?>">
    <meta property="og:image" content="<?php echo esc_url( $image ); ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo esc_attr( $title ); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr( $desc ); ?>">
    <meta name="twitter:image" content="<?php echo esc_url( $image ); ?>">

    <?php if ( is_front_page() ) : ?>
    <!-- WebSite & Organization Schema.org (JSON-LD) -->
    <script type="application/ld+json">
    [
        {
            "@context": "https://schema.org",
            "@type": "WebSite",
            "name": "OtoTamirciBul",
            "url": "<?php echo esc_url( home_url( '/' ) ); ?>",
            "potentialAction": {
                "@type": "SearchAction",
                "target": "<?php echo esc_url( home_url( '/ustalar/?s=' ) ); ?>{search_term_string}",
                "query-input": "required name=search_term_string"
            }
        },
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": "OtoTamirciBul",
            "url": "<?php echo esc_url( home_url( '/' ) ); ?>",
            "logo": "<?php echo esc_url( $theme_logo ); ?>",
            "description": "Türkiye'nin #1 Numaralı Oto Tamirci ve Servis Rehberi",
            "sameAs": [
                "https://misteknoloji360.com.tr"
            ]
        }
    ]
    </script>
    <?php endif; ?>
    <?php
}
add_action( 'wp_head', 'ototamir_seo_meta_tags', 1 );

// 8.3. Core Web Vitals Mobil LCP Hızlandırıcı (Kritik Görsel Preload)
function ototamir_lcp_preload_head() {
    // 1. Tekil İlan (Mechanic): LCP Görselini En Baştan Preload Et
    if ( is_singular( 'mechanic' ) ) {
        $m_id = get_the_ID();
        $img = get_post_meta( $m_id, '_custom_mechanic_image', true );
        if ( empty( $img ) ) {
            $img = has_post_thumbnail( $m_id ) ? (get_the_post_thumbnail_url( $m_id, 'medium_large' ) ?: get_the_post_thumbnail_url( $m_id, 'full' )) : 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?auto=format&fit=crop&w=1200&q=75';
        }
        if ( ! empty( $img ) ) {
            echo '<link rel="preload" as="image" href="' . esc_url( $img ) . '" fetchpriority="high">' . "\n";
        }
    }
    // 2. Tekil Blog Yazısı: Öne Çıkan Görseli Preload Et
    elseif ( is_singular( 'post' ) ) {
        $p_id = get_the_ID();
        $img = function_exists( 'ototamir_get_smart_post_image' ) 
            ? ototamir_get_smart_post_image( $p_id, 1200, 630 ) 
            : (get_post_meta( $p_id, '_custom_blog_image', true ) ?: (get_the_post_thumbnail_url( $p_id, 'full' ) ?: 'https://images.unsplash.com/photo-1487754180451-c456f719a1fc?auto=format&fit=crop&w=1200&q=80'));
        if ( ! empty( $img ) ) {
            echo '<link rel="preload" as="image" href="' . esc_url( $img ) . '" fetchpriority="high">' . "\n";
        }
    }
    // 3. Ana Sayfa: Mobil Logo Preload (LCP Hızlandırma)
    elseif ( is_front_page() ) {
        $logo_mob = get_template_directory_uri() . '/assets/images/logo-mobile.webp?v=2';
        echo '<link rel="preload" as="image" href="' . esc_url( $logo_mob ) . '" type="image/webp" media="(max-width: 768px)" fetchpriority="high">' . "\n";
    }
}
add_action( 'wp_head', 'ototamir_lcp_preload_head', 0 );

// 8.4. Core Web Vitals Görsel Optimizasyonu (LCP Görsellerinde Lazy-Load Asla Kullanılmaz)
function ototamir_optimize_image_attributes( $attr ) {
    static $first_image_handled = false;
    
    // Tekil sayfalarda sayfa üstündeki kritik görsel (LCP) asla lazy-load olmamalı
    if ( is_singular() && ! $first_image_handled ) {
        $attr['loading']       = 'eager';
        $attr['fetchpriority'] = 'high';
        $attr['decoding']      = 'async';
        $first_image_handled   = true;
        return $attr;
    }

    if ( ! isset( $attr['loading'] ) ) {
        $attr['loading'] = 'lazy';
    }
    if ( ! isset( $attr['decoding'] ) ) {
        $attr['decoding'] = 'async';
    }
    return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'ototamir_optimize_image_attributes' );

// 8.5. GEO İçin Otomatik İçindekiler Tablosu (Table of Contents - ToC) Oluşturucu
function ototamir_generate_toc_and_content( $content ) {
    if ( ! is_singular( 'post' ) ) {
        return array( 'toc' => '', 'content' => $content );
    }

    $pattern = '/<h([2-3])([^>]*)>(.*?)<\/h\1>/i';
    if ( ! preg_match_all( $pattern, $content, $matches, PREG_SET_ORDER ) ) {
        return array( 'toc' => '', 'content' => $content );
    }

    $toc_items = array();
    $index = 1;

    foreach ( $matches as $m ) {
        $level = $m[1];
        $attrs = $m[2];
        $title = strip_tags( $m[3] );
        $anchor = 'bolum-' . $index;

        $toc_items[] = array(
            'level'  => $level,
            'title'  => $title,
            'anchor' => $anchor,
        );

        $new_heading = sprintf( '<h%s%s id="%s">%s</h%s>', $level, $attrs, esc_attr( $anchor ), $m[3], $level );
        $content = str_replace( $m[0], $new_heading, $content );
        $index++;
    }

    if ( empty( $toc_items ) ) {
        return array( 'toc' => '', 'content' => $content );
    }

    ob_start();
    ?>
    <nav class="post-toc-box" aria-label="İçindekiler Tablosu">
        <div class="toc-header" onclick="document.getElementById('postTocList').classList.toggle('collapsed');">
            <span class="toc-title"><i class="fa-solid fa-list-ol" style="color:#f97316; margin-right:6px;"></i> İçindekiler Tablosu</span>
            <span class="toc-toggle"><i class="fa-solid fa-chevron-down"></i></span>
        </div>
        <ol class="toc-list" id="postTocList">
            <?php foreach ( $toc_items as $item ) : ?>
                <li class="toc-item-h<?php echo esc_attr( $item['level'] ); ?>">
                    <a href="#<?php echo esc_attr( $item['anchor'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>
    <?php
    $toc_html = ob_get_clean();

    return array( 'toc' => $toc_html, 'content' => $content );
}



// 8.6. Core Web Vitals İçin Akıllı Görsel Boyutlandırma ve CDN Optimizasyonu
function ototamir_optimize_image_url( $url, $width = 380 ) {
    if ( empty( $url ) ) {
        return $url;
    }

    // Tarayıcı WebP destekliyor mu?
    $accepts_webp = isset( $_SERVER['HTTP_ACCEPT'] ) && strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) !== false;

    // 1. Unsplash Görselleri: kart genişliği, q=65, WebP formatı
    if ( strpos( $url, 'images.unsplash.com' ) !== false ) {
        $url = preg_replace( '/([?&])w=\d+/', '$1w=' . $width, $url );
        if ( strpos( $url, 'w=' ) === false ) {
            $url .= ( strpos( $url, '?' ) === false ? '?' : '&' ) . 'w=' . $width;
        }
        $url = preg_replace( '/([?&])q=\d+/', '$1q=65', $url );
        if ( strpos( $url, 'q=' ) === false ) {
            $url .= '&q=65';
        }
        // WebP format
        $url = preg_replace( '/([?&])fm=[^&]+/', '$1fm=webp', $url );
        if ( strpos( $url, 'fm=' ) === false ) {
            $url .= '&fm=webp';
        }
        return $url;
    }

    // 2. Placeholder: WebP destekleniyorsa .webp, değilse optimize JPG
    if ( strpos( $url, 'tum-ustalar-burada' ) !== false ) {
        if ( $accepts_webp ) {
            return get_template_directory_uri() . '/assets/images/placeholder-mechanic.webp';
        }
        return get_template_directory_uri() . '/assets/images/tum-ustalar-burada-optimized.jpg';
    }

    // 3. Logo için WebP
    if ( strpos( $url, 'logoototamirci' ) !== false ) {
        return get_template_directory_uri() . '/assets/images/logo-main.webp';
    }

    return $url;
}

// 8.3. SEO Dostu Robots.txt Yapılandırması (Crawl Budget Optimize Edildi)
function ototamir_custom_robots_txt( $output, $public ) {
    if ( $public ) {
        $output  = "User-agent: *\n";
        $output .= "Allow: /\n";
        $output .= "Disallow: /wp-admin/\n";
        $output .= "Allow: /wp-admin/admin-ajax.php\n";
        $output .= "Disallow: /profil/\n";
        $output .= "Disallow: /giris/\n";
        $output .= "Disallow: /kayit/\n";
        $output .= "Disallow: /wp-login.php\n";
        $output .= "Disallow: /*?*s=*\n";
        $output .= "Disallow: /*?s=*\n";
        $output .= "Disallow: /*?tab=*\n";
        $output .= "Disallow: /*?action=*\n";
        $output .= "Disallow: /*?edit_id=*\n";
        $output .= "Disallow: /*?replytocom=*\n";
        $output .= "Disallow: /wp-json/\n\n";
        $output .= "User-agent: Googlebot-Image\n";
        $output .= "Allow: /\n\n";
        $output .= "Sitemap: " . esc_url( home_url( '/sitemap.xml' ) ) . "\n\n";
        $output .= "# LLMs.txt Standart Yapay Zeka & Ajan Rehberi (https://llmstxt.org)\n";
        $output .= "Allow: /llms.txt\n";
        $output .= "Allow: /llms-full.txt\n";
    }
    return $output;
}
add_filter( 'robots_txt', 'ototamir_custom_robots_txt', 10, 2 );

// 9. GitHub Otomatik Tema Güncelleme Entegrasyonu (PUC)
if ( file_exists( get_template_directory() . '/inc/plugin-update-checker/plugin-update-checker.php' ) ) {
    require_once get_template_directory() . '/inc/plugin-update-checker/plugin-update-checker.php';
    
    $findewerkstatt_update_checker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        'https://github.com/akkaya6611/findewerkstatt-de-tema/',
        get_template_directory() . '/style.css',
        'findewerkstatt-de-tema'
    );

    // Hauptbranch
    $findewerkstatt_update_checker->setBranch( 'main' );

    // Optional: Private Repo Token falls definiert
    if ( defined( 'FINDEWERKSTATT_GITHUB_TOKEN' ) && FINDEWERKSTATT_GITHUB_TOKEN ) {
        $findewerkstatt_update_checker->setAuthentication( FINDEWERKSTATT_GITHUB_TOKEN );
    }
}

// 10. Otomatik Browser Caching (.htaccess 1 Yıllık Statik Önbellek Kuralı)
function ototamir_add_browser_caching_htaccess() {
    if ( ! is_admin() ) {
        return;
    }

    $htaccess_file = get_home_path() . '.htaccess';
    if ( ! file_exists( $htaccess_file ) || ! is_writable( $htaccess_file ) ) {
        return;
    }

    $content = @file_get_contents( $htaccess_file );
    if ( $content && strpos( $content, 'OtoTamirCache' ) !== false ) {
        return;
    }

    $rules = array(
        '<IfModule mod_expires.c>',
        '    ExpiresActive On',
        '    ExpiresByType image/jpg "access plus 1 year"',
        '    ExpiresByType image/jpeg "access plus 1 year"',
        '    ExpiresByType image/gif "access plus 1 year"',
        '    ExpiresByType image/png "access plus 1 year"',
        '    ExpiresByType image/webp "access plus 1 year"',
        '    ExpiresByType image/svg+xml "access plus 1 year"',
        '    ExpiresByType text/css "access plus 1 year"',
        '    ExpiresByType application/javascript "access plus 1 year"',
        '    ExpiresByType text/javascript "access plus 1 year"',
        '    ExpiresByType font/woff2 "access plus 1 year"',
        '    ExpiresByType font/woff "access plus 1 year"',
        '</IfModule>',
        '<IfModule mod_headers.c>',
        '    <FilesMatch "\.(ico|jpe?g|png|gif|webp|css|js|woff2?)$">',
        '        Header set Cache-Control "public, max-age=31536000, immutable"',
        '    </FilesMatch>',
        '</IfModule>',
    );

    require_once ABSPATH . 'wp-admin/includes/file.php';
    insert_with_markers( $htaccess_file, 'OtoTamirCache', $rules );
}
add_action( 'admin_init', 'ototamir_add_browser_caching_htaccess' );

// ============================================================
// 11. Akıllı Otomatik Blog Görseli Yöneticisi (Oto Tamir & Arıza)
// Görseli olmayan tüm yazılara başlık/konu analiziyle veya
// 10+ yüksek kaliteli oto tamir havuzundan dönüşümlü görsel atar.
// ============================================================
function ototamir_get_smart_post_image( $post_id = null, $width = 800, $height = 500 ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }
    if ( ! $post_id ) {
        return 'https://images.unsplash.com/photo-1487754180451-c456f719a1fc?auto=format&fit=crop&w=' . $width . '&h=' . $height . '&q=80';
    }

    // 1. Öne çıkan görsel (featured image) varsa öncelikli kullan (yerel demo .local veya localhost değilse)
    if ( has_post_thumbnail( $post_id ) ) {
        $thumb_url = get_the_post_thumbnail_url( $post_id, 'full' );
        if ( ! empty( $thumb_url ) && strpos( $thumb_url, '.local' ) === false && strpos( $thumb_url, 'localhost' ) === false ) {
            return $thumb_url;
        }
    }

    // 2. Özel kayıtlı görsel meta var mı?
    // Eğer kırık 404 unsplash görseli veya yerel demo URL (.local/localhost) içeriyorsa yok say ve yenile
    $custom_img = get_post_meta( $post_id, '_custom_blog_image', true );
    if ( ! empty( $custom_img ) ) {
        if ( strpos( $custom_img, 'photo-1486006920555-c77dce18193b' ) === false 
             && strpos( $custom_img, '.local' ) === false 
             && strpos( $custom_img, 'localhost' ) === false ) {
            return $custom_img;
        }
    }

    // 3. Başlık ve içerikten anahtar kelime analizi
    $post = get_post( $post_id );
    $title = $post ? mb_strtolower( $post->post_title, 'UTF-8' ) : '';
    $content = $post ? mb_strtolower( wp_strip_all_tags( $post->post_content ), 'UTF-8' ) : '';
    $text = $title . ' ' . mb_substr( $content, 0, 500, 'UTF-8' );

    // Konuya göre optimize Unsplash görsel koleksiyonu
    $topic_images = array(
        'motor' => array(
            'keywords' => array( 'motor', 'yağ', 'yag', 'triger', 'segman', 'silindir', 'subap', 'rektifiye', 'piston', 'enjektör', 'yakıt', 'duman' ),
            'images'   => array(
                'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e', // Motor mekanik & aletler
                'https://images.unsplash.com/photo-1487754180451-c456f719a1fc', // Motor revizyon lifte araç
                'https://images.unsplash.com/photo-1517524008697-84bbe3c3fd98', // Motor bloğu detay
            ),
        ),
        'fren' => array(
            'keywords' => array( 'fren', 'balata', 'disk', 'abs', 'hidrolik', 'el freni', 'kaliper', 'titreme', 'cıyaklama' ),
            'images'   => array(
                'https://images.unsplash.com/photo-1625047509248-ec889cbff17f', // Fren disk & kaliper
                'https://images.unsplash.com/photo-1617814076367-b759c7d7e738', // Fren sistemi tamiri
            ),
        ),
        'elektrik' => array(
            'keywords' => array( 'akü', 'aku', 'elektrik', 'beyin', 'ecu', 'marş', 'mars', 'şarj', 'sarj', 'sigorta', 'sensör', 'yazılım', 'arıza lambası' ),
            'images'   => array(
                'https://images.unsplash.com/photo-1563720223185-11003d516935', // ECU & diagnostik bilgisayar
                'https://images.unsplash.com/photo-1580273916550-e323be2ae537', // Elektrik ve batarya ölçüm
            ),
        ),
        'lastik' => array(
            'keywords' => array( 'lastik', 'jant', 'balans', 'rot', 'hava basıncı', 'stepne', 'kış lastiği', 'patlak', 'yarık' ),
            'images'   => array(
                'https://images.unsplash.com/photo-1578844251758-2f71da64c96f', // Lastik değişimi & servis
                'https://images.unsplash.com/photo-1580274455191-1c62238fa333', // Jant balans servisi
            ),
        ),
        'sanziman' => array(
            'keywords' => array( 'şanzıman', 'sanziman', 'debriyaj', 'baskı', 'baski', 'vites', 'dsg', 'edc', 'cvt', 'mekatronik' ),
            'images'   => array(
                'https://images.unsplash.com/photo-1503376780353-7e6692767b70', // Şanzıman dişli ve aktarma
                'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e', // Mekanik transmisyon
            ),
        ),
        'klima' => array(
            'keywords' => array( 'klima', 'gaz', 'kalorifer', 'radyatör', 'radyator', 'antifriz', 'kompresör', 'soğutma', 'sogutma', 'ısıtma' ),
            'images'   => array(
                'https://images.unsplash.com/photo-1517524008697-84bbe3c3fd98', // Klima bakım ve radyatör
            ),
        ),
        'kaporta' => array(
            'keywords' => array( 'kaporta', 'boya', 'göçük', 'gocuk', 'çizik', 'cizik', 'pasta cila', 'tampon', 'fırın boya', 'kaza' ),
            'images'   => array(
                'https://images.unsplash.com/photo-1607860108855-64acf2078ed9', // Pasta cila & oto boya
                'https://images.unsplash.com/photo-1507136566006-cfc505b114fc', // Kaporta düzeltme
            ),
        ),
        'yolyardim' => array(
            'keywords' => array( 'çekici', 'cekici', 'kurtarma', 'kurtarıcı', 'yol yardım', 'yolda kaldı', 'araba çekme' ),
            'images'   => array(
                'https://images.unsplash.com/photo-1549399542-7e3f8b79c341', // Yolda yardım & oto çekici
            ),
        ),
    );

    $matched_image = '';
    foreach ( $topic_images as $topic => $data ) {
        foreach ( $data['keywords'] as $kw ) {
            if ( strpos( $text, $kw ) !== false ) {
                $idx = absint( $post_id ) % count( $data['images'] );
                $matched_image = $data['images'][$idx];
                break 2;
            }
        }
    }

    // 4. Eşleşme yoksa genel oto tamir havuzundan yazı ID'sine göre dönüşümlü seç
    if ( empty( $matched_image ) ) {
        $general_pool = array(
            'https://images.unsplash.com/photo-1487754180451-c456f719a1fc', // Usta motor tamirinde
            'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e', // Atölyede tamir ekipmanları
            'https://images.unsplash.com/photo-1625047509248-ec889cbff17f', // Servis liftinde araç
            'https://images.unsplash.com/photo-1563720223185-11003d516935', // Diagnostik arıza tespit
            'https://images.unsplash.com/photo-1517524008697-84bbe3c3fd98', // Kapsamlı periyodik kontrol
            'https://images.unsplash.com/photo-1503376780353-7e6692767b70', // Atölye araç tamiri
            'https://images.unsplash.com/photo-1578844251758-2f71da64c96f', // Tekerlek & alt takım
            'https://images.unsplash.com/photo-1580273916550-e323be2ae537', // Kaput altı kontrol
            'https://images.unsplash.com/photo-1607860108855-64acf2078ed9', // Detaylı tamir servisi
            'https://images.unsplash.com/photo-1507136566006-cfc505b114fc', // Usta çalışma anı
        );
        $idx = absint( $post_id ) % count( $general_pool );
        $matched_image = $general_pool[$idx];
    }

    // Parametreleri ekle
    $final_url = $matched_image . '?auto=format&fit=crop&w=' . $width . '&h=' . $height . '&q=80';

    // CDN/WebP optimizasyon fonksiyonu mevcutsa geçir
    if ( function_exists( 'ototamir_optimize_image_url' ) ) {
        $final_url = ototamir_optimize_image_url( $final_url, $width );
    }

    // Kırık veya geçersiz meta varsa veritabanını otomatik iyileştir
    if ( empty( $custom_img ) || strpos( $custom_img, 'photo-1486006920555-c77dce18193b' ) !== false || strpos( $custom_img, '.local' ) !== false || strpos( $custom_img, 'localhost' ) !== false ) {
        update_post_meta( $post_id, '_custom_blog_image', esc_url_raw( $final_url ) );
    }

    return $final_url;
}

// 11.1. Yeni Yazı Kaydedildiğinde Görsel Yoksa Otomatik Ata
function ototamir_auto_assign_post_image( $post_id, $post ) {
    // Sadece standart blog yazıları (post) için çalışsın
    if ( ! $post || $post->post_type !== 'post' || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
        return;
    }

    // Zaten öne çıkan görseli veya geçerli özel görseli varsa dokunma
    $existing_custom = get_post_meta( $post_id, '_custom_blog_image', true );
    if ( ( has_post_thumbnail( $post_id ) && strpos( get_the_post_thumbnail_url( $post_id ), '.local' ) === false && strpos( get_the_post_thumbnail_url( $post_id ), 'localhost' ) === false ) 
         || ( ! empty( $existing_custom ) && strpos( $existing_custom, 'photo-1486006920555-c77dce18193b' ) === false && strpos( $existing_custom, '.local' ) === false && strpos( $existing_custom, 'localhost' ) === false ) ) {
        return;
    }

    // Akıllı görseli belirle ve kalıcı meta olarak kaydet
    $smart_img = ototamir_get_smart_post_image( $post_id, 1200, 675 );
    if ( ! empty( $smart_img ) ) {
        update_post_meta( $post_id, '_custom_blog_image', esc_url_raw( $smart_img ) );
    }
}
add_action( 'save_post_post', 'ototamir_auto_assign_post_image', 20, 2 );

// ============================================================
// 12. Dahili Yapay Zeka Arıza Teşhisi ve OBD Arıza Kodları Modülleri
// ============================================================
$otb_ai_file = get_template_directory() . '/inc/modules/otb-ai-teshis/otb-ai-teshis.php';
if ( file_exists( $otb_ai_file ) ) {
    require_once $otb_ai_file;
}

$ariza_kodlari_file = get_template_directory() . '/inc/modules/ariza-kodlari/ariza-kodlari.php';
if ( file_exists( $ariza_kodlari_file ) ) {
    require_once $ariza_kodlari_file;
}

// ============================================================
// 13. Google AdSense ve Reklam Yönetim Modülü
// ============================================================
$adsense_file = get_template_directory() . '/inc/adsense.php';
if ( file_exists( $adsense_file ) ) {
    require_once $adsense_file;
}

// ============================================================
// 14. E-posta Onay Kodu (OTP) ile Üyelik Doğrulama Modülü
// ============================================================
$email_verif_file = get_template_directory() . '/inc/email-verification.php';
if ( file_exists( $email_verif_file ) ) {
    require_once $email_verif_file;
}

// ============================================================
// 15. Yerel SEO (GEO) & Schema.org Zengin Sonuç Motoru
// ============================================================
$seo_geo_file = get_template_directory() . '/inc/seo-geo.php';
if ( file_exists( $seo_geo_file ) ) {
    require_once $seo_geo_file;
}

// ============================================================
// 16. Gelişmiş XML Site Haritası (Sitemap Engine) Modülü
// ============================================================
$sitemap_file = get_template_directory() . '/inc/sitemap.php';
if ( file_exists( $sitemap_file ) ) {
    require_once $sitemap_file;
}

// ============================================================
// 17. Canlı Destek & Kullanıcı Mesajlaşma Sistemi
// ============================================================
$support_chat_file = get_template_directory() . '/inc/support-chat.php';
if ( file_exists( $support_chat_file ) ) {
    require_once $support_chat_file;
}

// ============================================================
// 18. Hızlı Arama Motoru İndeksleme (IndexNow & Google Ping Engine)
// ============================================================
$indexnow_file = get_template_directory() . '/inc/indexnow.php';
if ( file_exists( $indexnow_file ) ) {
    require_once $indexnow_file;
}

// ============================================================
// 19. LLMs.txt Standart Entegrasyonu (Yapay Zeka & Ajan Rehberi)
// ============================================================
$llms_file = get_template_directory() . '/inc/llms.php';
if ( file_exists( $llms_file ) ) {
    require_once $llms_file;
}

// ============================================================
// 20. Entegre Trend Makaleler SEO Motoru (Dahili Eklenti)
// ============================================================
if ( ! class_exists( 'Makaleler_Plugin' ) ) {
    $makaleler_file = get_template_directory() . '/inc/plugins/makaleler/makaleler.php';
    if ( file_exists( $makaleler_file ) ) {
        require_once $makaleler_file;
    }
}


// Migrations bereinigt für FindeWerkstatt.de Deutschland

// ============================================================
// 22. Yeni Üye Karşılama Anketi & Yönetici Analitiği
// ============================================================
$survey_analytics_file = get_template_directory() . '/inc/survey-analytics.php';
if ( file_exists( $survey_analytics_file ) ) {
    require_once $survey_analytics_file;
}

// ============================================================
// 23. Araç Marka & Model Sayfaları Otomatik Veritabanı Oluşturucu
// ============================================================
$brand_pages_gen_file = get_template_directory() . '/inc/brand-model-pages-generator.php';
if ( file_exists( $brand_pages_gen_file ) ) {
    require_once $brand_pages_gen_file;
}

// ============================================================
// 24. Araç Marka & Model Zeka Motoru ve Zengin SEO Veri Katmanı
// ============================================================
$brand_seo_data_file = get_template_directory() . '/inc/brand-model-seo-data.php';
if ( file_exists( $brand_seo_data_file ) ) {
    require_once $brand_seo_data_file;
}





// ============================================================
// 15. Canlı Domain (ototamircibul.com.tr) & Demo Modu Yönetimi
// ============================================================
function ototamir_is_demo_mode() {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    return ( strpos( $host, '.local' ) !== false || strpos( $host, 'localhost' ) !== false || strpos( $host, '127.0.0.1' ) !== false );
}

function ototamir_demo_mode_admin_notice() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    if ( ! ototamir_is_demo_mode() ) return;

    $screen = get_current_screen();
    if ( $screen && ( $screen->id === 'toplevel_page_ototamir-ads' || $screen->id === 'dashboard' ) ) {
        ?>
        <div class="notice notice-info is-dismissible" style="border-left-color: #ea580c; border-radius: 6px;">
            <p>
                <strong><span class="dashicons dashicons-admin-site-alt3" style="color:#ea580c; vertical-align:middle;"></span> OtoTamirciBul Canlı Yapılandırma:</strong>
                Şu an yerel demo ortamındasınız (<code><?php echo esc_html( $_SERVER['HTTP_HOST'] ?? 'demo' ); ?></code>). Tüm SEO meta etiketleri, AdSense modülleri ve marka kimliği <strong>ototamircibul.com.tr</strong> alan adı için kusursuz şekilde hazırlandı. Sitenizi canlı sunucuya aktardığınızda tüm entegrasyonlar anında canlıda çalışacaktır.
            </p>
        </div>
        <?php
    }
}
add_action( 'admin_notices', 'ototamir_demo_mode_admin_notice' );

// ============================================================
// 17. Özel Vektör Kategori & Servis İkonları Motoru
// ============================================================
function ototamir_get_service_icon( $slug ) {
    $map = array(
        'mekanik-ustasi'                                               => '001-mechanic.png',
        'mekanik-ustalari'                                             => '001-mechanic.png',
        'mekanik-tamir-bakim'                                          => '001-mechanic.png',
        'motor-ustalari'                                               => '028-engine.png',
        'periyodik-bakim-servisleri-yag-filtre-genel-kontrol'          => '019-engine-oil.png',
        'periyodik-bakim'                                              => '019-engine-oil.png',
        'oto-elektrik-ustalari'                                        => '005-car-service.png',
        'kaporta-ustalari'                                             => '020-car-painting.png',
        'oto-cam-ustalari'                                             => '031-windshield.png',
        'oto-klima-ustalari'                                           => '017-ac.png',
        'fren-ve-balata-ustasi'                                        => '016-brake.png',
        'oto-lastik-ve-jant-ustasi-lastik-oteli-rot-balans'            => '029-tire.png',
        'rot-balans-ve-on-takim-ustasi'                                => '030-tire-1.png',
        'aks-ve-suspansiyon-tamircisi'                                 => '022-damper.png',
        'oto-ekspertiz'                                                => '023-check-up.png',
        'oto-cekici-yol-yardim'                                        => '033-breakdown.png',
        'yol-yardim'                                                   => '033-breakdown.png',
        'oto-kurtarma'                                                 => '033-breakdown.png',
        'sanziman-ve-guc-aktarma'                                      => '014-part.png',
        'oto-yedek-parcaci'                                            => '015-spare-parts.png',
        'oto-beyin-ve-beyin-tamiri-ustasi-ecu-cip-tunning-yazilim'     => '009-repair-shop.png',
        'oto-doseme-ustalari'                                          => '002-car.png',
        'lpg-montaj-ustalari'                                          => '004-maintenance-1.png',
        'oto-enjeksiyon-ustalari'                                      => '007-repair.png',
        'oto-radyator-tamir-ustalari'                                  => '018-repair-1.png',
        'oto-kuaför'                                                   => '032-body-repair-1.png',
        'oto-kuafor'                                                   => '032-body-repair-1.png',
        'oto-pratik-hizli-servis'                                      => '006-car-maintenance.png',
        'yakit-ve-motor-ozel-sistemleri'                               => '024-maintenance-2.png',
        'egzoz-ve-emisyon-sistemleri-ustasi'                           => '026-automotive.png',
        'egzoz-ustalari'                                               => '026-automotive.png',
        'size-en-yakin-kaporta-ustalari'                               => '020-car-painting.png',
        'size-en-yakin-lpg-montaj-ustalari'                            => '004-maintenance-1.png',
        'size-en-yakin-oto-ekspertizler'                               => '023-check-up.png',
        'size-en-yakin-oto-elektrik-ustalari'                          => '005-car-service.png',
        'size-en-yakin-oto-enjeksiyon-ustalari'                        => '007-repair.png',
        'size-en-yakin-oto-klima-ustalari'                             => '017-ac.png',
    );

    $file = isset( $map[ $slug ] ) ? $map[ $slug ] : '013-car-repair.png';
    return get_template_directory_uri() . '/assets/images/icons/' . $file;
}

// ===================================================
// CANLI AKARYAKIT FIYATLARI OTOMATIK SENKRONIZASYON (OPET API)
// ===================================================
require_once get_template_directory() . '/inc/data/fuel-prices-data.php';

add_action( 'ototamir_daily_fuel_sync_event', 'ototamir_handle_daily_fuel_sync' );
function ototamir_handle_daily_fuel_sync() {
    ototamir_sync_live_fuel_prices( true );
}

if ( ! wp_next_scheduled( 'ototamir_daily_fuel_sync_event' ) ) {
    wp_schedule_event( time(), 'twicedaily', 'ototamir_daily_fuel_sync_event' );
}
