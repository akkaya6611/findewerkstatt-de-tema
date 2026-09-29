<?php
/**
 * Template Name: Araç Marka & Model Tamir Rehberi
 * Description: Size En Yakın [Marka] [Model] Oto Tamircileri, Yetkili Özel Servisleri, 2026 Fiyat Rehberi & Kronik Arıza Çözümleri
 */
get_header();

$post_id    = get_the_ID();
$brand_name = get_post_meta( $post_id, '_ototamir_target_brand', true );
$model_name = get_post_meta( $post_id, '_ototamir_target_model', true );
$brand_slug = get_post_meta( $post_id, '_ototamir_brand_slug', true );
$model_slug = get_post_meta( $post_id, '_ototamir_model_slug', true );

// Fallback: Eğer meta boşsa başlıktan veya slug'dan çıkar
if ( empty( $brand_name ) ) {
    $title = get_the_title();
    if ( preg_match( '/Size En Yakın\s+([^\s]+)(?:\s+([^\s]+))?\s+Oto/iu', $title, $m ) ) {
        $brand_name = $m[1] ?? '';
        $model_name = $m[2] ?? '';
        $brand_slug = sanitize_title( $brand_name );
        if ( ! empty( $model_name ) ) {
            $model_slug = sanitize_title( $brand_name . '-' . $model_name );
        }
    }
}

$page_h1_title = ! empty( $model_name ) 
    ? "Size En Yakın {$brand_name} {$model_name} Oto Tamircileri & Servisleri" 
    : "Size En Yakın {$brand_name} Oto Tamircileri & Özel Servisleri";

$page_desc     = ! empty( $model_name )
    ? "{$brand_name} {$model_name} aracınız için garantili motor mekanik, periyodik bakım, fren balata, şanzıman ve bilgisayarlı arıza tespit hizmeti sunan en yakın onaylı oto tamir servisleri ve 2026 güncel bakım fiyat rehberi."
    : "{$brand_name} marka tüm araçlar için garantili bakım, orijinal/OEM yedek parça ve profesyonel oto tamirci rehberi. 2026 servis işçilik fiyatlarını karşılaştırın.";

// Filtre Değişkenleri
$selected_city     = isset( $_GET['mechanic_city'] ) ? sanitize_text_field( $_GET['mechanic_city'] ) : '';
$selected_district = isset( $_GET['mechanic_district'] ) ? sanitize_text_field( $_GET['mechanic_district'] ) : '';
$selected_service  = isset( $_GET['service_type'] ) ? sanitize_text_field( $_GET['service_type'] ) : '';
$paged             = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ), isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1 );

// SEO Zeka Motorundan Model Verilerini Çek
$specs       = class_exists( 'OtoTamir_Brand_Model_SEO_Data' ) ? OtoTamir_Brand_Model_SEO_Data::get_model_specs( $brand_name, $model_name ) : array();
$price_table = class_exists( 'OtoTamir_Brand_Model_SEO_Data' ) ? OtoTamir_Brand_Model_SEO_Data::get_price_table( $brand_name, $model_name ) : array();
$faqs        = class_exists( 'OtoTamir_Brand_Model_SEO_Data' ) ? OtoTamir_Brand_Model_SEO_Data::get_model_faqs( $brand_name, $model_name ) : array();
$top_cities  = class_exists( 'OtoTamir_Brand_Model_SEO_Data' ) ? OtoTamir_Brand_Model_SEO_Data::get_top_provinces() : array();
$silos       = class_exists( 'OtoTamir_Brand_Model_SEO_Data' ) ? OtoTamir_Brand_Model_SEO_Data::get_service_silos() : array();
?>

<!-- ===================================================
     1. HERO BAŞLIK & EKMEK KIRINTILARI BÖLÜMÜ
     =================================================== -->
<section class="page-header-small" style="background: linear-gradient(135deg, #090d16 0%, #111827 50%, #1e293b 100%); padding: 48px 0 42px; color: white; border-bottom: 3px solid #ea580c; position:relative; overflow:hidden;">
    <div style="position:absolute; right:-60px; top:-40px; width:340px; height:340px; background:radial-gradient(circle, rgba(234,88,12,0.18) 0%, rgba(0,0,0,0) 70%); border-radius:50%; pointer-events:none;"></div>
    
    <div class="container" style="position:relative; z-index:2;">
        <!-- SEO Breadcrumbs (Ekmek Kırıntısı) -->
        <nav class="archive-breadcrumbs" style="font-size: 13px; color: rgba(255,255,255,0.7); display:flex; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom: 16px;">
            <a href="<?php echo esc_url( home_url('/') ); ?>" style="color: rgba(255,255,255,0.85); text-decoration: none;"><i class="fa-solid fa-house" style="font-size:11px;"></i> Ana Sayfa</a>
            <span>/</span>
            <a href="<?php echo esc_url( home_url('/ustalar') ); ?>" style="color: rgba(255,255,255,0.85); text-decoration: none;">Ustalar & Servisler</a>
            <?php if ( ! empty( $brand_name ) ) : ?>
                <span>/</span>
                <span style="color: #fdba74;"><?php echo esc_html( $brand_name ); ?></span>
            <?php endif; ?>
            <?php if ( ! empty( $model_name ) ) : ?>
                <span>/</span>
                <span style="color: #ffffff; font-weight:700;"><?php echo esc_html( $model_name ); ?></span>
            <?php endif; ?>
        </nav>

        <!-- Güven Rozetleri (Trust Badges) -->
        <div style="display:flex; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:14px;">
            <span style="background:rgba(234,88,12,0.2); border:1px solid #ea580c; color:#fdba74; padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">
                <i class="fa-solid fa-certificate"></i> 2026 Onaylı Servis Rehberi
            </span>
            <span style="background:rgba(16,185,129,0.15); border:1px solid #10b981; color:#6ee7b7; padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:700;">
                <i class="fa-solid fa-shield-check"></i> Garantili İşçilik & Orijinal Parça
            </span>
            <span style="background:rgba(255,255,255,0.1); padding:4px 10px; border-radius:20px; font-size:11.5px; color:#e2e8f0;">
                <i class="fa-solid fa-star" style="color:#eab308;"></i> 4.8 / 5 (Doğrulanmış Usta Yorumları)
            </span>
        </div>

        <h1 class="archive-header-title" style="margin: 0 0 12px 0; font-size: 32px; font-weight: 900; letter-spacing: -0.5px; color: white; line-height: 1.25;">
            <?php echo esc_html( $page_h1_title ); ?>
        </h1>
        <p class="archive-header-desc" style="margin: 0; font-size: 16px; color: #cbd5e1; max-width: 900px; line-height: 1.65;">
            <?php echo esc_html( $page_desc ); ?>
        </p>

        <!-- Sibling Model Çipleri (İç Linkleme & UX) -->
        <?php
        if ( ! empty( $brand_name ) && class_exists( 'OtoTamir_Brands_Models_Migration' ) ) {
            $all_bm = OtoTamir_Brands_Models_Migration::get_brands_and_models();
            $brand_models = $all_bm[ $brand_name ] ?? array();
            if ( ! empty( $brand_models ) ) {
                echo '<div style="margin-top: 22px; display:flex; align-items:center; flex-wrap:wrap; gap:8px;">';
                echo '<span style="font-size:12px; font-weight:700; color:#fdba74; text-transform:uppercase; letter-spacing:0.5px;"><i class="fa-solid fa-car-side"></i> Diğer ' . esc_html($brand_name) . ' Modelleri:</span>';
                foreach ( $brand_models as $bm_item ) {
                    $item_is_current = ( strcasecmp( $bm_item, $model_name ) === 0 );
                    $model_page_slug = sanitize_title( "size en yakin {$brand_name} {$bm_item} oto tamircileri" );
                    $page_obj = get_page_by_path( $model_page_slug );
                    $target_url = $page_obj ? get_permalink( $page_obj->ID ) : home_url( "/marka/" . sanitize_title($brand_name) . "/" . sanitize_title($brand_name . '-' . $bm_item) . "/" );
                    $bg_style = $item_is_current ? 'background:#ea580c; color:white; font-weight:700; box-shadow:0 2px 8px rgba(234,88,12,0.4);' : 'background:rgba(255,255,255,0.08); color:#e2e8f0; border:1px solid rgba(255,255,255,0.15);';
                    echo '<a href="' . esc_url( $target_url ) . '" style="' . $bg_style . ' padding:5px 12px; border-radius:14px; font-size:12.5px; text-decoration:none; transition:all 0.2s;">' . esc_html( $bm_item ) . '</a>';
                }
                echo '</div>';
            }
        }
        ?>
    </div>
</section>

<!-- ===================================================
     2. HIZLI MODEL TEKNİK ÖZET KARTLARI (ÖN BİLGİLENDİRME)
     =================================================== -->
<?php if ( ! empty( $specs ) ) : ?>
<section style="background:#ffffff; border-bottom:1px solid #e2e8f0; padding:22px 0;">
    <div class="container">
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap:16px;">
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:14px 18px; display:flex; align-items:center; gap:14px;">
                <div style="width:42px; height:42px; border-radius:10px; background:#fff7ed; color:#ea580c; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0;">
                    <i class="fa-solid fa-oil-can"></i>
                </div>
                <div>
                    <div style="font-size:11.5px; font-weight:700; color:#64748b; text-transform:uppercase;">Motor Yağı Viskozitesi</div>
                    <div style="font-size:13.5px; font-weight:800; color:#0f172a; margin-top:2px;"><?php echo esc_html( $specs['oil'] ); ?></div>
                </div>
            </div>

            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:14px 18px; display:flex; align-items:center; gap:14px;">
                <div style="width:42px; height:42px; border-radius:10px; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0;">
                    <i class="fa-solid fa-gauge-high"></i>
                </div>
                <div>
                    <div style="font-size:11.5px; font-weight:700; color:#64748b; text-transform:uppercase;">Bakım Aralığı</div>
                    <div style="font-size:13.5px; font-weight:800; color:#0f172a; margin-top:2px;"><?php echo esc_html( $specs['maintenance'] ); ?></div>
                </div>
            </div>

            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:14px 18px; display:flex; align-items:center; gap:14px;">
                <div style="width:42px; height:42px; border-radius:10px; background:#f0fdf4; color:#16a34a; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0;">
                    <i class="fa-solid fa-gears"></i>
                </div>
                <div>
                    <div style="font-size:11.5px; font-weight:700; color:#64748b; text-transform:uppercase;">Triger Mekanizması</div>
                    <div style="font-size:13.5px; font-weight:800; color:#0f172a; margin-top:2px;"><?php echo esc_html( $specs['timing'] ); ?></div>
                </div>
            </div>

            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:14px 18px; display:flex; align-items:center; gap:14px;">
                <div style="width:42px; height:42px; border-radius:10px; background:#faf5ff; color:#9333ea; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0;">
                    <i class="fa-solid fa-car-battery"></i>
                </div>
                <div>
                    <div style="font-size:11.5px; font-weight:700; color:#64748b; text-transform:uppercase;">Motor Yelpazesi</div>
                    <div style="font-size:13px; font-weight:700; color:#0f172a; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:180px;" title="<?php echo esc_attr($specs['engines']); ?>"><?php echo esc_html( $specs['engines'] ); ?></div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===================================================
     3. ANA LİSTELEME VE FİLTRE DÜZENİ
     =================================================== -->
<div class="container archive-container" style="display:flex; gap:32px; margin-top:35px; margin-bottom:60px; align-items:flex-start;">
    <!-- SOL KENAR ÇUBUĞU FİLTRE -->
    <aside class="archive-sidebar" style="width:310px; flex-shrink:0;">
        <div class="archive-sidebar-card" style="background:white; padding:22px; border-radius:14px; border:1px solid #e2e8f0; box-shadow:0 4px 16px rgba(0,0,0,0.04); position:sticky; top:85px;">
            <h3 style="margin:0 0 16px 0; font-size:16px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-filter" style="color:#ea580c;"></i> Servis ve Konum Filtresi
            </h3>

            <form action="<?php echo esc_url( get_permalink() ); ?>" method="get">
                <div class="form-group" style="margin-bottom: 14px;">
                    <label style="display:block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #334155;">İl Seçin</label>
                    <select name="mechanic_city" id="filter-il" class="form-control" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px; background:white;">
                        <option value="">Tüm İller</option>
                        <?php 
                        $cities = get_terms(array('taxonomy' => 'mechanic_city', 'hide_empty' => false));
                        foreach($cities as $city) { 
                            $sel = ($selected_city === $city->slug || $selected_city === $city->name) ? 'selected' : '';
                            echo '<option value="'.esc_attr($city->slug).'" data-name="'.esc_attr($city->name).'" '.$sel.'>'.esc_html($city->name).'</option>'; 
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label style="display:block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #334155;">İlçe</label>
                    <select name="mechanic_district" id="filter-ilce" data-selected="<?php echo esc_attr($selected_district); ?>" class="form-control" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px; background:white;">
                        <option value="">Tüm İlçeler</option>
                        <?php 
                        if( !empty($selected_district) ) {
                            echo '<option value="'.esc_attr($selected_district).'" selected>'.esc_html($selected_district).'</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 18px;">
                    <label style="display:block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #334155;">Uzmanlık / Hizmet</label>
                    <select name="service_type" class="form-control" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px; background:white;">
                        <option value="">Tüm Hizmetler</option>
                        <?php 
                        $services = get_terms(array('taxonomy' => 'service_type', 'hide_empty' => false));
                        foreach($services as $srv) { 
                            $sel = ($selected_service === $srv->slug) ? 'selected' : '';
                            echo '<option value="'.esc_attr($srv->slug).'" '.$sel.'>'.esc_html($srv->name).'</option>'; 
                        }
                        ?>
                    </select>
                </div>

                <button type="submit" style="width: 100%; padding: 12px; background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%); color: white; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(234,88,12,0.3); font-size:14px;">
                    <i class="fa-solid fa-magnifying-glass"></i> Filtrele & Listele
                </button>
            </form>

            <!-- Hızlı Arıza Tespiti CTA -->
            <div style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); border:1px solid #fed7aa; border-radius:12px; padding:16px; margin-top:22px;">
                <div style="font-size:13.5px; font-weight:800; color:#9a3412; margin-bottom:4px; display:flex; align-items:center; gap:6px;">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Arıza Lambası mı Yandı?
                </div>
                <p style="margin:0 0 10px 0; font-size:12px; color:#c2410c; line-height:1.45;">
                    Yapay zeka arıza tespit modülümüz ile sorununuzu saniyeler içinde ücretsiz teşhis edin.
                </p>
                <a href="<?php echo esc_url( home_url('/ariza-tespiti/') ); ?>" style="display:inline-flex; align-items:center; gap:6px; font-size:12.5px; font-weight:700; color:white; background:#ea580c; padding:7px 14px; border-radius:8px; text-decoration:none; box-shadow:0 2px 6px rgba(234,88,12,0.3);">
                    Arıza Tespiti Yap <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- SAĞ ANA LİSTELEME KOLONU -->
    <main class="archive-main-col" style="flex:1; min-width:0;">
        <?php
        // Usta Sorgusu Hazırla
        $args = array(
            'post_type'      => 'mechanic',
            'posts_per_page' => 12,
            'post_status'    => 'publish',
            'paged'          => $paged,
        );

        $tax_queries = array();

        if ( ! empty( $selected_city ) ) {
            $tax_queries[] = array(
                'taxonomy' => 'mechanic_city',
                'field'    => 'slug',
                'terms'    => $selected_city,
            );
        }

        if ( ! empty( $selected_district ) ) {
            $d_term = get_term_by( 'name', $selected_district, 'mechanic_district' ) ?: get_term_by( 'slug', $selected_district, 'mechanic_district' );
            if ( $d_term ) {
                $tax_queries[] = array( 'taxonomy' => 'mechanic_district', 'field' => 'term_id', 'terms' => $d_term->term_id );
            } else {
                $tax_queries[] = array( 'taxonomy' => 'mechanic_district', 'field' => 'name', 'terms' => $selected_district );
            }
        }

        if ( ! empty( $selected_service ) ) {
            $tax_queries[] = array( 'taxonomy' => 'service_type', 'field' => 'slug', 'terms' => $selected_service );
        }

        // Marka & Model taksonomi eşleşmesi
        $brand_term_ids = array();
        if ( ! empty( $model_slug ) ) {
            $m_term = get_term_by( 'slug', $model_slug, 'car_brand' );
            if ( $m_term ) {
                $brand_term_ids[] = $m_term->term_id;
                if ( ! empty( $m_term->parent ) ) {
                    $brand_term_ids[] = (int) $m_term->parent;
                }
            }
        }
        if ( empty( $brand_term_ids ) && ! empty( $brand_slug ) ) {
            $b_term = get_term_by( 'slug', $brand_slug, 'car_brand' );
            if ( $b_term ) {
                $brand_term_ids[] = $b_term->term_id;
            }
        }

        if ( ! empty( $brand_term_ids ) ) {
            $tax_queries[] = array(
                'taxonomy'         => 'car_brand',
                'field'            => 'term_id',
                'terms'            => $brand_term_ids,
                'operator'         => 'IN',
                'include_children' => true,
            );
        }

        if ( count( $tax_queries ) > 0 ) {
            $tax_queries['relation'] = 'AND';
            $args['tax_query'] = $tax_queries;
        }

        $query = new WP_Query( $args );

        // Eğer doğrudan sonuç yoksa genel ustaları fallback olarak getir (Sayfa asla boş dönmez)
        $is_fallback = false;
        if ( ! $query->have_posts() ) {
            $fallback_args = array(
                'post_type'      => 'mechanic',
                'posts_per_page' => 8,
                'post_status'    => 'publish',
            );
            if ( ! empty( $selected_city ) ) {
                $fallback_args['tax_query'] = array(
                    array( 'taxonomy' => 'mechanic_city', 'field' => 'slug', 'terms' => $selected_city )
                );
            }
            $query = new WP_Query( $fallback_args );
            $is_fallback = true;
        }
        ?>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
            <div>
                <h2 style="margin:0; font-size:20px; font-weight:800; color:#0f172a;">
                    <?php if ( ! $is_fallback ) : ?>
                        Onaylı <?php echo esc_html( $model_name ? "{$brand_name} {$model_name}" : $brand_name ); ?> Servisleri <span style="color:#64748b; font-size:15px; font-weight:600;">(<?php echo $query->found_posts; ?> Usta)</span>
                    <?php else : ?>
                        Bölgenizdeki Önerilen En İyi Oto Servisleri
                    <?php endif; ?>
                </h2>
            </div>
            <div style="font-size:13px; color:#64748b;">
                <i class="fa-solid fa-clock-rotate-left"></i> 2026 Güncel Usta Listesi
            </div>
        </div>

        <?php if ( $is_fallback ) : ?>
            <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:14px 18px; margin-bottom:22px; font-size:13.5px; color:#1e40af; display:flex; align-items:center; gap:10px;">
                <i class="fa-solid fa-circle-info" style="font-size:18px; flex-shrink:0;"></i>
                <span>Aradığınız filtrede henüz doğrudan usta kaydı bulunamadı; bölgenizdeki en yüksek puanlı genel oto mekanik ve bakım ustaları aşağıda listelenmiştir.</span>
            </div>
        <?php endif; ?>

        <div class="listing-grid">
            <?php
            if ( $query->have_posts() ) :
                while ( $query->have_posts() ) : $query->the_post();
                    $image_url = get_post_meta( get_the_ID(), '_custom_mechanic_image', true );
                    if ( empty( $image_url ) ) {
                        $image_url = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'medium' ) : 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?auto=format&fit=crop&w=450&q=75';
                    }
                    if ( function_exists( 'ototamir_optimize_image_url' ) ) {
                        $image_url = ototamir_optimize_image_url( $image_url, 450 );
                    }
                    $is_vip = get_post_meta( get_the_ID(), '_mechanic_is_featured', true ) === '1';
                    $vip_badge_text = get_post_meta( get_the_ID(), '_mechanic_badge_text', true ) ?: 'Öne Çıkan VIP';
                    $card_vip_class = $is_vip ? ' listing-card-vip' : '';
            ?>
                <a href="<?php the_permalink(); ?>" class="listing-card<?php echo $card_vip_class; ?>">
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>" class="listing-card-bg" width="370" height="250" loading="lazy" decoding="async">
                    <div class="listing-card-overlay"></div>
                    
                    <?php if ( $is_vip ) : ?>
                        <div class="listing-badge-top listing-badge-vip">
                            <i class="fa-solid fa-crown"></i> <?php echo esc_html( $vip_badge_text ); ?>
                        </div>
                    <?php else : ?>
                        <div class="listing-badge-top">
                            <i class="fa-solid fa-shield-check"></i> Onaylı Servis
                        </div>
                    <?php endif; ?>
                    
                    <div class="listing-card-content">
                        <?php $rating = get_mechanic_rating_data(get_the_ID()); if($rating['count'] > 0): ?>
                            <div class="listing-rating-pill"><?php echo $rating['avg']; ?></div>
                        <?php else: ?>
                            <div class="listing-rating-pill" style="background:#64748b; color: white;">Yeni</div>
                        <?php endif; ?>
                        
                        <h3 class="listing-title">
                            <?php the_title(); ?> <i class="fa-solid fa-circle-check"></i>
                        </h3>
                        
                        <div class="listing-address">
                            <?php 
                            $address = get_post_meta( get_the_ID(), '_mechanic_address', true );
                            if(empty($address)) {
                                $cities = wp_get_post_terms(get_the_ID(), 'mechanic_city');
                                $districts = wp_get_post_terms(get_the_ID(), 'mechanic_district');
                                $c_name = ($cities && !is_wp_error($cities)) ? $cities[0]->name : '';
                                $d_name = ($districts && !is_wp_error($districts)) ? $districts[0]->name : '';
                                $address = trim("$d_name, $c_name", ", ");
                            }
                            echo esc_html( $address ); 
                            ?>
                        </div>
                    </div>
                </a>
            <?php 
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </div>

        <!-- ===================================================
             4. 2026 GÜNCEL İŞÇİLİK VE PARÇA FİYAT REHBERİ TABLOSU
             =================================================== -->
        <section style="background:white; border:1px solid #e2e8f0; border-radius:16px; padding:28px; margin-top:40px; box-shadow:0 4px 14px rgba(0,0,0,0.03);">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:18px;">
                <div>
                    <h2 style="margin:0; font-size:22px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-table-list" style="color:#ea580c;"></i> 2026 <?php echo esc_html( $model_name ? "{$brand_name} {$model_name}" : $brand_name ); ?> Bakım ve Tamir Fiyat Rehberi
                    </h2>
                    <p style="margin:4px 0 0 0; font-size:13.5px; color:#64748b;">
                        Türkiye genelinde özel servislerde uygulanan ortalama parça ve usta işçilik maliyet tablosu.
                    </p>
                </div>
                <span style="background:#f1f5f9; padding:5px 12px; border-radius:20px; font-size:12px; font-weight:700; color:#475569;">
                    Son Güncelleme: 2026
                </span>
            </div>

            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; text-align:left; font-size:14px;">
                    <thead>
                        <tr style="background:#f8fafc; border-bottom:2px solid #cbd5e1;">
                            <th style="padding:12px 16px; font-weight:700; color:#1e293b;">Servis / İşlem Türü</th>
                            <th style="padding:12px 16px; font-weight:700; color:#1e293b;">Kapsam & Değişen Parçalar</th>
                            <th style="padding:12px 16px; font-weight:700; color:#1e293b;">Tahmini Süre</th>
                            <th style="padding:12px 16px; font-weight:700; color:#ea580c; text-align:right;">Ortalama Fiyat Aralığı</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $price_table as $pt_item ) : ?>
                            <tr style="border-bottom:1px solid #f1f5f9; transition:background 0.15s;">
                                <td style="padding:14px 16px; font-weight:700; color:#0f172a;">
                                    <?php echo esc_html( $pt_item['service'] ); ?>
                                </td>
                                <td style="padding:14px 16px; color:#475569; font-size:13px;">
                                    <?php echo esc_html( $pt_item['scope'] ); ?>
                                </td>
                                <td style="padding:14px 16px; color:#64748b; font-size:13px;">
                                    <i class="fa-regular fa-clock"></i> <?php echo esc_html( $pt_item['duration'] ); ?>
                                </td>
                                <td style="padding:14px 16px; font-weight:800; color:#ea580c; text-align:right; font-size:14.5px;">
                                    <?php echo esc_html( $pt_item['price'] ); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p style="margin:14px 0 0 0; font-size:12px; color:#94a3b8; font-style:italic;">
                * Belirtilen fiyatlar piyasa ortalaması olup orijinal veya OEM eşdeğer yedek parça tercihi, motor hacmi ve döviz kuruna göre ustalar arasında farklılık gösterebilir. Hizmet öncesinde ustanızdan yazılı teklif alınız.
            </p>
        </section>

        <!-- ===================================================
             5. KRONİK ARIZALAR VE USTA ÇÖZÜM TAVSİYELERİ
             =================================================== -->
        <?php if ( ! empty( $specs['issues'] ) ) : ?>
        <section style="background:white; border:1px solid #e2e8f0; border-radius:16px; padding:28px; margin-top:35px; box-shadow:0 4px 14px rgba(0,0,0,0.03);">
            <h2 style="margin:0 0 8px 0; font-size:22px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-triangle-exclamation" style="color:#ea580c;"></i> <?php echo esc_html( $model_name ? "{$brand_name} {$model_name}" : $brand_name ); ?> Kronik Arızaları & Çözüm Yolları
            </h2>
            <p style="margin:0 0 20px 0; font-size:14px; color:#64748b;">
                Sektördeki tecrübeli ustaların ve kullanıcıların paylaştığı en yaygın arıza belirtileri ve kalıcı tamir yöntemleri:
            </p>

            <div style="display:grid; grid-template-columns: 1fr; gap:16px;">
                <?php foreach ( $specs['issues'] as $idx => $issue ) : ?>
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-left:4px solid #ea580c; border-radius:10px; padding:18px;">
                        <h3 style="margin:0 0 6px 0; font-size:16px; font-weight:800; color:#0f172a;">
                            <?php echo ($idx + 1) . '. ' . esc_html( $issue['title'] ); ?>
                        </h3>
                        <p style="margin:0; font-size:13.5px; color:#475569; line-height:1.6;">
                            <?php echo esc_html( $issue['desc'] ); ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- ===================================================
             6. USTA SEÇERKEN DİKKAT EDİLECEK 5 ALTIN KURAL (E-E-A-T)
             =================================================== -->
        <section style="background:linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color:white; border-radius:16px; padding:30px; margin-top:35px;">
            <h2 style="margin:0 0 8px 0; font-size:21px; font-weight:800; color:white; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-award" style="color:#fdba74;"></i> <?php echo esc_html($brand_name); ?> Ustası Seçerken 5 Altın Kural
            </h2>
            <p style="margin:0 0 20px 0; font-size:13.5px; color:#cbd5e1;">
                Aracınızı servise bırakmadan önce bu kriterleri mutlaka sorgulayın:
            </p>

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:16px;">
                <div style="background:rgba(255,255,255,0.06); padding:16px; border-radius:10px; border:1px solid rgba(255,255,255,0.1);">
                    <strong style="color:#fdba74; font-size:14px; display:block; margin-bottom:4px;">1. Parça Orijinalliği:</strong>
                    <span style="font-size:12.5px; color:#e2e8f0; line-height:1.5;">Kullanılan yağın üretici onaylı olduğundan (Dexos, RN, VW spesifikasyonu) ve filtrelerin OEM standartlarında olduğundan emin olun.</span>
                </div>
                <div style="background:rgba(255,255,255,0.06); padding:16px; border-radius:10px; border:1px solid rgba(255,255,255,0.1);">
                    <strong style="color:#fdba74; font-size:14px; display:block; margin-bottom:4px;">2. Bilgisayarlı Teşhis:</strong>
                    <span style="font-size:12.5px; color:#e2e8f0; line-height:1.5;">Ezbere parça değiştirmek yerine lisanslı OBD arıza tespit cihazıyla canlı sensör değerlerini okuyan ustaları tercih edin.</span>
                </div>
                <div style="background:rgba(255,255,255,0.06); padding:16px; border-radius:10px; border:1px solid rgba(255,255,255,0.1);">
                    <strong style="color:#fdba74; font-size:14px; display:block; margin-bottom:4px;">3. Yazılı İş Emri & Garanti:</strong>
                    <span style="font-size:12.5px; color:#e2e8f0; line-height:1.5;">Yapılan işçilik ve takılan parçalar için en az 6 ay veya 10.000 km servis garantisi talep edin.</span>
                </div>
                <div style="background:rgba(255,255,255,0.06); padding:16px; border-radius:10px; border:1px solid rgba(255,255,255,0.1);">
                    <strong style="color:#fdba74; font-size:14px; display:block; margin-bottom:4px;">4. Eski Parçaları Görme:</strong>
                    <span style="font-size:12.5px; color:#e2e8f0; line-height:1.5;">Aracınızdan sökülen eski buji, balata, triger seti ve filtreleri teslim alarak değişimin yapıldığını teyit edin.</span>
                </div>
            </div>
        </section>

        <!-- ===================================================
             7. ZENGİN SIKÇA SORULAN SORULAR (FAQ ACCORDION)
             =================================================== -->
        <section style="background:white; border:1px solid #e2e8f0; border-radius:16px; padding:28px; margin-top:35px; box-shadow:0 4px 14px rgba(0,0,0,0.03);">
            <h2 style="margin:0 0 6px 0; font-size:22px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-circle-question" style="color:#ea580c;"></i> <?php echo esc_html( $model_name ? "{$brand_name} {$model_name}" : $brand_name ); ?> Hakkında Sıkça Sorulan Sorular
            </h2>
            <p style="margin:0 0 20px 0; font-size:13.5px; color:#64748b;">
                Araç sahiplerinin bakım ve servis süreçleriyle ilgili en çok merak ettiği konular:
            </p>

            <div class="faq-list" style="display:flex; flex-direction:column; gap:12px;">
                <?php foreach ( $faqs as $f_idx => $faq ) : ?>
                    <details style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:16px; cursor:pointer;" <?php echo ($f_idx === 0) ? 'open' : ''; ?>>
                        <summary style="font-weight:800; font-size:15px; color:#0f172a; outline:none; display:flex; justify-content:space-between; align-items:center;">
                            <span><?php echo esc_html( $faq['q'] ); ?></span>
                            <i class="fa-solid fa-chevron-down" style="font-size:12px; color:#ea580c;"></i>
                        </summary>
                        <p style="margin:12px 0 0 0; font-size:14px; color:#475569; line-height:1.65; border-top:1px solid #e2e8f0; padding-top:10px;">
                            <?php echo esc_html( $faq['a'] ); ?>
                        </p>
                    </details>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ===================================================
             8. TÜRKİYE 24 BÜYÜKŞEHİR SEO İÇ LİNKLEME AĞI (LOCAL SEO WEB)
             =================================================== -->
        <section style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:16px; padding:26px; margin-top:35px;">
            <h3 style="margin:0 0 6px 0; font-size:18px; font-weight:800; color:#0f172a; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-map-location-dot" style="color:#ea580c;"></i> Şehirlere Göre <?php echo esc_html( $model_name ? "{$brand_name} {$model_name}" : $brand_name ); ?> Ustaları
            </h3>
            <p style="margin:0 0 16px 0; font-size:13px; color:#64748b;">
                Bulunduğunuz şehirdeki en yakın onaylı servis ve mekanik ustalarını tek tıkla listeleyin:
            </p>

            <div style="display:flex; flex-wrap:wrap; gap:8px;">
                <?php foreach ( $top_cities as $c_item ) : 
                    $city_link = add_query_arg( 'mechanic_city', $c_item['slug'], get_permalink() );
                    $is_active_city = ( $selected_city === $c_item['slug'] );
                    $c_style = $is_active_city 
                        ? 'background:#ea580c; color:white; font-weight:700;' 
                        : 'background:white; color:#334155; border:1px solid #cbd5e1;';
                ?>
                    <a href="<?php echo esc_url( $city_link ); ?>" style="<?php echo $c_style; ?> padding:6px 12px; border-radius:8px; font-size:12.5px; text-decoration:none; transition:all 0.15s; display:inline-flex; align-items:center; gap:5px;">
                        <i class="fa-solid fa-location-dot" style="font-size:11px; opacity:0.7;"></i>
                        <?php echo esc_html( $c_item['name'] . ' ' . ($model_name ?: $brand_name) . ' Ustaları' ); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ===================================================
             9. HİZMET SİLOLARI (SERVICE SILOS)
             =================================================== -->
        <section style="margin-top:30px;">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                <i class="fa-solid fa-screwdriver-wrench" style="color:#ea580c;"></i>
                <strong style="font-size:14px; color:#0f172a;">Öne Çıkan Servis Hizmetleri:</strong>
            </div>
            <div style="display:flex; flex-wrap:wrap; gap:8px;">
                <?php foreach ( $silos as $silo ) : 
                    $silo_link = add_query_arg( 'service_type', $silo['slug'], get_permalink() );
                ?>
                    <a href="<?php echo esc_url( $silo_link ); ?>" style="background:white; border:1px solid #e2e8f0; color:#475569; padding:6px 12px; border-radius:6px; font-size:12px; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                        <i class="fa-solid <?php echo esc_attr( $silo['icon'] ); ?>" style="color:#ea580c; font-size:11px;"></i>
                        <?php echo esc_html( $silo['name'] ); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

    </main>
</div>

<?php
$model_breadcrumb_items = array(
    array(
        '@type'    => 'ListItem',
        'position' => 1,
        'name'     => 'Ana Sayfa',
        'item'     => home_url( '/' ),
    ),
    array(
        '@type'    => 'ListItem',
        'position' => 2,
        'name'     => 'Ustalar & Servisler',
        'item'     => home_url( '/ustalar' ),
    ),
);

$mb_pos = 3;
if ( ! empty( $brand_name ) ) {
    $model_breadcrumb_items[] = array(
        '@type'    => 'ListItem',
        'position' => $mb_pos++,
        'name'     => $brand_name,
        'item'     => home_url( '/marka/' . $brand_slug . '/' ),
    );
}

$model_breadcrumb_items[] = array(
    '@type'    => 'ListItem',
    'position' => $mb_pos++,
    'name'     => $page_h1_title,
    'item'     => get_permalink(),
);

$model_faq_entities = array();
foreach ( $faqs as $f ) {
    $model_faq_entities[] = array(
        '@type'          => 'Question',
        'name'           => $f['q'],
        'acceptedAnswer' => array(
            '@type' => 'Answer',
            'text'  => $f['a'],
        ),
    );
}

$model_schema_graph = array(
    '@context' => 'https://schema.org',
    '@graph'   => array(
        array(
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $model_breadcrumb_items,
        ),
        array(
            '@type'           => 'Service',
            'name'            => $page_h1_title,
            'description'     => $page_desc,
            'provider'        => array(
                '@type' => 'Organization',
                'name'  => 'OtoTamirciBul',
                'url'   => home_url( '/' ),
            ),
            'areaServed'      => array(
                '@type' => 'Country',
                'name'  => 'Türkiye',
            ),
        ),
        array(
            '@type'      => 'FAQPage',
            'mainEntity' => $model_faq_entities,
        ),
    ),
);
?>
<!-- ===================================================
     10. GELİŞMİŞ SCHEMA.ORG JSON-LD YAPISAL VERİLERİ (GOOGLE RICH SNIPPETS)
     =================================================== -->
<script type="application/ld+json">
<?php echo wp_json_encode( $model_schema_graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ); ?>
</script>

<?php get_footer(); ?>
