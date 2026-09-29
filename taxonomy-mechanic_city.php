<?php
/**
 * Taxonomy Template: Mechanic City (Şehir Sayfası)
 * Her il için özel vitrin slider'ı, ilçe filtreleri ve SEO içerikleri
 */
get_header();

$current_city = get_queried_object();
$city_name = $current_city->name;
$city_slug = $current_city->slug;
$city_id   = $current_city->term_id;

// Bu ildeki toplam usta sayısı
$total_mechanics = $current_city->count;
if ( ! $total_mechanics ) {
    global $wpdb;
    $total_mechanics = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(tr.object_id) 
         FROM {$wpdb->term_relationships} tr 
         JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id 
         WHERE tt.term_id = %d", $city_id
    ) );
}

// Bu ildeki öne çıkan ustalar (Slider için)
$slider_query = new WP_Query( array(
    'post_type'      => 'mechanic',
    'posts_per_page' => 12,
    'post_status'    => 'publish',
    'tax_query'      => array(
        array(
            'taxonomy' => 'mechanic_city',
            'field'    => 'term_id',
            'terms'    => $city_id,
        ),
    ),
    'meta_key'       => '_mechanic_views_count',
    'orderby'        => 'meta_value_num',
    'order'          => 'DESC',
) );

if ( $slider_query->post_count < 3 ) {
    $slider_query = new WP_Query( array(
        'post_type'      => 'mechanic',
        'posts_per_page' => 12,
        'post_status'    => 'publish',
        'tax_query'      => array(
            array(
                'taxonomy' => 'mechanic_city',
                'field'    => 'term_id',
                'terms'    => $city_id,
            ),
        ),
        'orderby'        => 'date',
        'order'          => 'DESC',
    ) );
}

// Bu ildeki aktif ilçeleri bul
global $wpdb;
$active_districts = $wpdb->get_results( $wpdb->prepare("
    SELECT t.name, t.slug, COUNT(p.ID) as cnt
    FROM {$wpdb->posts} p
    JOIN {$wpdb->term_relationships} tr_city ON p.ID = tr_city.object_id
    JOIN {$wpdb->term_taxonomy} tt_city ON tr_city.term_taxonomy_id = tt_city.term_taxonomy_id AND tt_city.taxonomy = 'mechanic_city'
    JOIN {$wpdb->term_relationships} tr_dist ON p.ID = tr_dist.object_id
    JOIN {$wpdb->term_taxonomy} tt_dist ON tr_dist.term_taxonomy_id = tt_dist.term_taxonomy_id AND tt_dist.taxonomy = 'mechanic_district'
    JOIN {$wpdb->terms} t ON tt_dist.term_id = t.term_id
    WHERE tt_city.term_id = %d AND p.post_status = 'publish' AND p.post_type = 'mechanic'
    GROUP BY t.term_id
    ORDER BY cnt DESC
    LIMIT 24
", $city_id) );

$seo_profile = function_exists('ototamir_get_city_seo_profile') ? ototamir_get_city_seo_profile($city_name) : array();
$city_desc = $seo_profile['desc'] ?? '';
$hub_name = $seo_profile['hub_name'] ?? "$city_name Sanayi Siteleri";
$dist_highlight = $seo_profile['districts_highlight'] ?? "$city_name geneli";

$default_clean_img = 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?auto=format&fit=crop&w=800&q=80';
?>

<style>
.city-hero {
    background: linear-gradient(rgba(15, 23, 42, 0.92), rgba(15, 23, 42, 0.92)), url('https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1920&q=80') center/cover;
    padding: 55px 0 45px 0;
    color: white;
}
.city-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(249, 115, 22, 0.18);
    border: 1px solid rgba(249, 115, 22, 0.35);
    color: #fb923c;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 12px;
}
.district-chips-wrap {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 14px;
}
.district-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #ffffff;
    color: #334155;
    padding: 7px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    border: 1px solid #e2e8f0;
}
.district-chip:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(14, 165, 233, 0.2);
}
.district-chip span {
    background: #f1f5f9;
    color: #64748b;
    padding: 2px 7px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
}
.district-chip:hover span {
    background: rgba(255, 255, 255, 0.25);
    color: white;
}
.city-slider-wrap {
    padding: 45px 0;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}
.city-seo-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 30px;
    margin: 40px 0;
}
.city-feature-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 20px;
    transition: all 0.2s ease;
}
.city-feature-box:hover {
    background: #ffffff;
    box-shadow: 0 6px 18px rgba(0,0,0,0.05);
    border-color: #cbd5e1;
}
.city-feature-icon {
    width: 42px;
    height: 42px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    margin-bottom: 12px;
}
</style>

<!-- ======================== ŞEHİR HERO BÖLÜMÜ ======================== -->
<section class="city-hero">
    <div class="container">
        <!-- Breadcrumbs -->
        <nav style="font-size: 13px; color: rgba(255,255,255,0.7); display:flex; align-items:center; gap:8px; margin-bottom:14px;">
            <a href="<?php echo esc_url( home_url('/') ); ?>" style="color: rgba(255,255,255,0.85); text-decoration: none;"><i class="fa-solid fa-house" style="font-size:11px;"></i> Ana Sayfa</a>
            <span>/</span>
            <a href="<?php echo esc_url( home_url('/iller') ); ?>" style="color: rgba(255,255,255,0.85); text-decoration: none;">İller</a>
            <span>/</span>
            <span style="color: #fb923c; font-weight:700;"><?php echo esc_html($city_name); ?></span>
        </nav>

        <div class="city-badge">
            <i class="fa-solid fa-location-dot"></i> Türkiye / <?php echo esc_html($city_name); ?>
        </div>

        <h1 style="font-size: 32px; font-weight: 800; margin: 0 0 8px 0; color: #ffffff;">
            <?php echo esc_html($city_name); ?> Oto Tamircileri & Yetkili Özel Servisler
        </h1>
        <p style="font-size: 15px; color: rgba(255,255,255,0.85); max-width: 800px; line-height: 1.6; margin: 0;">
            <?php echo esc_html($city_name); ?> genelinde hizmet veren toplam <strong><?php echo number_format($total_mechanics); ?> adet</strong> onaylı oto tamir ustası, motor mekanik, oto elektrik, periyodik bakım ve 7/24 oto kurtarma servisi tek adreste.
        </p>

        <!-- İlçe Çipleri (Hero İçi) -->
        <?php if ( ! empty($active_districts) ) : ?>
        <div style="margin-top: 22px;">
            <div style="font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.7); margin-bottom: 6px;">
                <i class="fa-solid fa-map-pin" style="color: #fb923c;"></i> <?php echo esc_html($city_name); ?> İlçeleri:
            </div>
            <div class="district-chips-wrap">
                <a href="<?php echo esc_url( home_url('/ustalar/?mechanic_city=' . $city_slug) ); ?>" class="district-chip" style="background: linear-gradient(135deg, #f97316, #ea580c); color: white; border-color: #ea580c; box-shadow: 0 4px 10px rgba(249,115,22,0.3);">
                    Tüm <?php echo esc_html($city_name); ?> <span><?php echo number_format($total_mechanics); ?></span>
                </a>
                <?php foreach ( array_slice($active_districts, 0, 12) as $d ) : ?>
                <a href="<?php echo esc_url( home_url('/ustalar/?mechanic_city=' . $city_slug . '&mechanic_district=' . urlencode($d->name)) ); ?>" class="district-chip">
                    <?php echo esc_html($d->name); ?> <span><?php echo number_format($d->cnt); ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ======================== ŞEHİR USTA SLIDER BÖLÜMÜ ======================== -->
<?php if ( $slider_query->have_posts() ) : ?>
<section class="city-slider-wrap">
    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom: 22px;">
            <div>
                <div style="display:inline-flex; align-items:center; gap:6px; background:#fff7ed; color:#ea580c; border:1px solid #ffedd5; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700; margin-bottom:6px;">
                    <i class="fa-solid fa-fire"></i> <?php echo esc_html($city_name); ?> VİTRİNİ
                </div>
                <h2 style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0;">
                    <?php echo esc_html($city_name); ?> Öne Çıkan Ustalar
                </h2>
                <p style="color: #64748b; font-size: 14px; margin: 4px 0 0 0;">
                    Sürücülerin en çok incelediği ve tercih ettiği <?php echo esc_html($city_name); ?> oto tamir servisleri
                </p>
            </div>
            
            <div style="display:flex; align-items:center; gap:8px;">
                <button class="swiper-nav-btn" id="city-prev"><i class="fa-solid fa-chevron-left"></i></button>
                <button class="swiper-nav-btn" id="city-next"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </div>

        <div class="swiper swiper-city-mechs" style="overflow: hidden; padding-bottom: 8px;">
            <div class="swiper-wrapper">
                <?php
                while ( $slider_query->have_posts() ) : $slider_query->the_post();
                    $img = get_post_meta( get_the_ID(), '_custom_mechanic_image', true );
                    if ( empty( $img ) || str_contains($img, 'tum-ustalar-burada') ) {
                        $img = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'medium_large' ) : $default_clean_img;
                    }
                    $districts = wp_get_post_terms( get_the_ID(), 'mechanic_district' );
                    $dist = ( $districts && ! is_wp_error( $districts ) ) ? $districts[0]->name : '';
                    $rating = get_mechanic_rating_data( get_the_ID() );
                    $views = function_exists('ototamir_get_post_views') ? ototamir_get_post_views(get_the_ID()) : '850';
                ?>
                <div class="swiper-slide">
                    <a href="<?php the_permalink(); ?>" class="mech-card">
                        <img src="<?php echo esc_url($img); ?>" alt="<?php the_title_attribute(); ?>">
                        <div class="mech-card-overlay"></div>

                        <div class="mech-card-badge">
                            <i class="fa-solid fa-circle-check"></i> <?php echo esc_html($city_name); ?>
                        </div>

                        <div class="mech-card-body">
                            <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px; flex-wrap:wrap;">
                                <div class="mech-card-views">
                                    <i class="fa-solid fa-eye"></i> <?php echo esc_html($views); ?> Ziyaret
                                </div>
                                <?php if($rating['count'] > 0): ?>
                                    <div class="mech-card-rating">
                                        <i class="fa-solid fa-star"></i> <?php echo $rating['avg']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <h3><?php the_title(); ?> <i class="fa-solid fa-circle-check"></i></h3>
                            
                            <?php if ( $dist ) : ?>
                            <div class="mech-card-loc">
                                <i class="fa-solid fa-location-dot"></i> <?php echo esc_html($dist); ?>, <?php echo esc_html($city_name); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ======================== ŞEHİR SEO TANITIM & USTA BİLGİLERİ ======================== -->
<div class="container">
    <div class="city-seo-card">
        <div style="margin-bottom: 24px;">
            <span style="display: inline-flex; align-items: center; gap: 6px; background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; margin-bottom: 8px;">
                <i class="fa-solid fa-book-open"></i> ŞEHİR & SANAYİ REHBERİ
            </span>
            <h2 style="font-size: 24px; color: #1e293b; font-weight: 800; margin: 0 0 10px 0;">
                <?php echo esc_html($city_name); ?> Oto Tamir & Bakım Hizmetleri Hakkında
            </h2>
            <p style="color: #475569; font-size: 15px; line-height: 1.7; margin: 0;">
                <?php echo esc_html($city_desc); ?>
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 16px; margin-top: 20px;">
            <div class="city-feature-box">
                <div class="city-feature-icon" style="background:#fff7ed; color:#f97316;">
                    <i class="fa-solid fa-wrench"></i>
                </div>
                <h4 style="font-size: 15px; font-weight: 700; color: #1e293b; margin: 0 0 6px 0;">Mekanik & Motor</h4>
                <p style="font-size: 13px; color: #64748b; line-height: 1.5; margin: 0;">
                    Triger seti, baskı balata, şanzıman ve motor revizyonu gibi ağır mekanik onarımlar.
                </p>
            </div>

            <div class="city-feature-box">
                <div class="city-feature-icon" style="background:#fef3c7; color:#d97706;">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <h4 style="font-size: 15px; font-weight: 700; color: #1e293b; margin: 0 0 6px 0;">Elektrik & Teşhis</h4>
                <p style="font-size: 13px; color: #64748b; line-height: 1.5; margin: 0;">
                    OBD2 bilgisayarlı arıza tespiti, akü, marş motoru, aydınlatma ve klima gazı dolumu.
                </p>
            </div>

            <div class="city-feature-box">
                <div class="city-feature-icon" style="background:#f0fdf4; color:#16a34a;">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <h4 style="font-size: 15px; font-weight: 700; color: #1e293b; margin: 0 0 6px 0;">Çalışma Saatleri</h4>
                <p style="font-size: 13px; color: #64748b; line-height: 1.5; margin: 0;">
                    Hafta içi ve Cumartesi açık servisler, pazar günleri açık/nöbetçi oto tamir noktaları.
                </p>
            </div>

            <div class="city-feature-box">
                <div class="city-feature-icon" style="background:#fef2f2; color:#dc2626;">
                    <i class="fa-solid fa-truck-pickup"></i>
                </div>
                <h4 style="font-size: 15px; font-weight: 700; color: #1e293b; margin: 0 0 6px 0;">7/24 Yol Yardım</h4>
                <p style="font-size: 13px; color: #64748b; line-height: 1.5; margin: 0;">
                    Yolda kalan araçlar için akü takviye, lastik değişimi ve acil oto çekici desteği.
                </p>
            </div>
        </div>

        <div style="margin-top: 20px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 12px 18px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; font-size: 13px; color: #475569;">
            <div><strong style="color: #0f172a;"><i class="fa-solid fa-industry" style="color: #f97316;"></i> Sanayi Merkezleri:</strong> <?php echo esc_html($hub_name); ?></div>
            <div style="color:#cbd5e1;">|</div>
            <div><strong style="color: #0f172a;"><i class="fa-solid fa-map-location" style="color: #f97316;"></i> Hizmet Verilen Bölgeler:</strong> <?php echo esc_html($dist_highlight); ?></div>
        </div>
    </div>
</div>

<!-- ======================== TÜM İL USTALARI LİSTESİ ======================== -->
<div class="container" style="margin-bottom: 80px;">
    
    <!-- Yatay Filtreleme Barı -->
    <div class="city-filter-bar">
        <form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" class="city-filter-form">
            <input type="hidden" name="post_type" value="mechanic">
            <input type="hidden" name="mechanic_city" value="<?php echo esc_attr($city_slug); ?>">

            <div class="city-filter-group">
                <label><i class="fa-solid fa-map-pin" style="color:var(--primary);"></i> İlçe Seçin</label>
                <select name="mechanic_district" id="filter-ilce" class="city-filter-select">
                    <option value="">Tüm <?php echo esc_html($city_name); ?> İlçeleri</option>
                    <?php foreach ( $active_districts as $d ) : ?>
                        <option value="<?php echo esc_attr($d->name); ?>"><?php echo esc_html($d->name); ?> (<?php echo $d->cnt; ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="city-filter-group">
                <label><i class="fa-solid fa-wrench" style="color:var(--primary);"></i> Hizmet Türü</label>
                <select name="service_type" class="city-filter-select">
                    <option value="">Tüm Hizmetler</option>
                    <?php 
                    $services = get_terms(array('taxonomy' => 'service_type', 'hide_empty' => true));
                    foreach($services as $service) { 
                        echo '<option value="'.esc_attr($service->slug).'">'.esc_html($service->name).'</option>'; 
                    }
                    ?>
                </select>
            </div>

            <button type="submit" class="city-filter-btn">
                <i class="fa-solid fa-magnifying-glass"></i> İlanları Filtrele
            </button>
        </form>
    </div>

    <!-- Başlık -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h2 style="margin:0; font-size: 22px; font-weight: 700; color: #1e293b;">
            <?php echo esc_html($city_name); ?> Usta Listesi 
            <span style="color:#64748b; font-size:16px; font-weight: 500;">(<?php echo number_format($total_mechanics); ?> Usta)</span>
        </h2>
    </div>

    <!-- 3-4 Kolonlu Ferah Usta Izgarası -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px;">
        <?php
        $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
        $main_query = new WP_Query( array(
            'post_type'      => 'mechanic',
            'posts_per_page' => 12,
            'paged'          => $paged,
            'post_status'    => 'publish',
            'tax_query'      => array(
                array(
                    'taxonomy' => 'mechanic_city',
                    'field'    => 'term_id',
                    'terms'    => $city_id,
                ),
            ),
        ) );

        if ( $main_query->have_posts() ) :
            while ( $main_query->have_posts() ) : $main_query->the_post();
                $image_url = get_post_meta( get_the_ID(), '_custom_mechanic_image', true );
                if ( empty( $image_url ) || str_contains($image_url, 'tum-ustalar-burada') ) {
                    $image_url = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'medium_large' ) : $default_clean_img;
                }
                $districts = wp_get_post_terms( get_the_ID(), 'mechanic_district' );
                $dist_text = ( $districts && ! is_wp_error( $districts ) ) ? $districts[0]->name : '';
                $rating = get_mechanic_rating_data( get_the_ID() );
                $is_fav = function_exists('is_user_favorite') && is_user_favorite(get_the_ID());
                $heart_class = $is_fav ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
                $heart_color = $is_fav ? 'color: #ef4444;' : 'color: rgba(255,255,255,0.7);';
                $is_vip = get_post_meta( get_the_ID(), '_mechanic_is_featured', true ) === '1';
                $vip_badge_text = get_post_meta( get_the_ID(), '_mechanic_badge_text', true ) ?: 'Öne Çıkan VIP';
                $card_vip_class = $is_vip ? ' mech-card-vip' : '';
        ?>
            <a href="<?php the_permalink(); ?>" class="mech-card<?php echo $card_vip_class; ?>">
                <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>">
                <div class="mech-card-overlay"></div>
                
                <?php if ( $is_vip ) : ?>
                    <div class="mech-card-badge mech-card-badge-vip">
                        <i class="fa-solid fa-crown"></i> <?php echo esc_html( $vip_badge_text ); ?>
                    </div>
                <?php else : ?>
                    <div class="mech-card-badge">
                        <i class="fa-solid fa-star"></i> Onaylı Usta
                    </div>
                <?php endif; ?>

                <div class="mech-card-heart listing-heart" data-post-id="<?php echo get_the_ID(); ?>" style="<?php echo $heart_color; ?>">
                    <i class="<?php echo $heart_class; ?>"></i>
                </div>

                <div class="mech-card-body">
                    <?php if($rating['count'] > 0): ?>
                        <div class="mech-card-rating">
                            <i class="fa-solid fa-star"></i> <?php echo $rating['avg']; ?> (<?php echo $rating['count']; ?>)
                        </div>
                    <?php endif; ?>
                    
                    <h3>
                        <?php the_title(); ?>
                        <i class="fa-solid fa-circle-check"></i>
                    </h3>

                    <?php if ( $dist_text ) : ?>
                    <div class="mech-card-loc">
                        <i class="fa-solid fa-location-dot"></i> <?php echo esc_html( $dist_text ); ?>, <?php echo esc_html($city_name); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </a>
        <?php 
            endwhile;
        else :
            echo '<p style="color: #64748b;">Bu ilde henüz kayıtlı usta bulunmamaktadır.</p>';
        endif;
        wp_reset_postdata();
        ?>
    </div>
    
    <!-- Sayfalama -->
    <div class="pagination">
        <?php
        echo paginate_links( array(
            'total'     => $main_query->max_num_pages,
            'current'   => $paged,
            'prev_text' => '&laquo; Önceki',
            'next_text' => 'Sonraki &raquo;',
        ) );
        ?>
    </div>
</div>

<!-- ======================== YEREL SEO SSS BÖLÜMÜ ======================== -->
<section class="archive-seo-faq" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 60px 0;">
    <div class="container" style="max-width: 900px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 35px;">
            <span style="display: inline-block; background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; padding: 4px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; margin-bottom: 8px;">
                <i class="fa-solid fa-circle-question"></i> Sıkça Sorulan Sorular
            </span>
            <h2 style="font-size: 26px; color: #1e293b; margin: 0 0 8px 0;"><?php echo esc_html($city_name); ?> Oto Tamir Hizmetleri Rehberi</h2>
            <p style="color: #64748b; font-size: 15px; margin: 0;"><?php echo esc_html($city_name); ?> bölgesinde tamirci arayan sürücüler için önemli bilgiler</p>
        </div>

        <div class="faq-accordion" style="display: flex; flex-direction: column; gap: 14px;">
            <details style="background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 22px; cursor: pointer;">
                <summary style="font-weight: 600; font-size: 16px; color: #1e293b; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fa-solid fa-wrench" style="color:#f97316; margin-right:8px;"></i> <?php echo esc_html($city_name); ?> bölgesinde en iyi oto tamircisi nasıl seçilir?</span>
                    <i class="fa-solid fa-chevron-down" style="color:#94a3b8; font-size:13px;"></i>
                </summary>
                <p style="color: #64748b; font-size: 14px; line-height: 1.7; margin: 12px 0 0 0; padding-top: 12px; border-top: 1px dashed #f1f5f9;">
                    OtoTamirciBul üzerinden <?php echo esc_html($city_name); ?> genelindeki kayıtlı oto tamir servislerinin müşteri yorumlarını, puanlarını, uzmanlık alanlarını (motor mekanik, oto elektrik, kaporta, klima) ve sertifikalarını inceleyerek güvenilir servislere tek tıkla doğrudan ulaşabilirsiniz.
                </p>
            </details>

            <details style="background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 22px; cursor: pointer;">
                <summary style="font-weight: 600; font-size: 16px; color: #1e293b; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fa-solid fa-tag" style="color:#f97316; margin-right:8px;"></i> <?php echo esc_html($city_name); ?> oto tamir ve periyodik bakım fiyatları 2026 yılında ne kadar?</span>
                    <i class="fa-solid fa-chevron-down" style="color:#94a3b8; font-size:13px;"></i>
                </summary>
                <p style="color: #64748b; font-size: 14px; line-height: 1.7; margin: 12px 0 0 0; padding-top: 12px; border-top: 1px dashed #f1f5f9;">
                    <?php echo esc_html($city_name); ?> genelinde oto tamir, yağ ve filtre bakımı, fren balata değişimi ve arıza tespit fiyatları aracın marka/modeline ve yapılacak işleme göre değişir. Ustalarımızın profillerindeki iletişim butonundan ücretsiz ön fiyat teklifi alabilirsiniz.
                </p>
            </details>

            <details style="background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 22px; cursor: pointer;">
                <summary style="font-weight: 600; font-size: 16px; color: #1e293b; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fa-solid fa-phone" style="color:#f97316; margin-right:8px;"></i> <?php echo esc_html($city_name); ?> 7/24 nöbetçi oto tamirci ve yol yardım hizmeti var mı?</span>
                    <i class="fa-solid fa-chevron-down" style="color:#94a3b8; font-size:13px;"></i>
                </summary>
                <p style="color: #64748b; font-size: 14px; line-height: 1.7; margin: 12px 0 0 0; padding-top: 12px; border-top: 1px dashed #f1f5f9;">
                    Evet, OtoTamirciBul üzerindeki '7/24 Yol Yardım' ve 'Pazar Açık / Nöbetçi' filtrelerini kullanarak <?php echo esc_html($city_name); ?> ve çevresinde gece ve tatil günlerinde acil destek veren çekici ve mobil yol yardım ustalarına 7/24 ulaşabilirsiniz.
                </p>
            </details>

            <details style="background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 22px; cursor: pointer;">
                <summary style="font-weight: 600; font-size: 16px; color: #1e293b; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fa-solid fa-microchip" style="color:#f97316; margin-right:8px;"></i> <?php echo esc_html($city_name); ?> oto sanayi sitelerinde bilgisayarlı arıza tespiti (OBD) yapılıyor mu?</span>
                    <i class="fa-solid fa-chevron-down" style="color:#94a3b8; font-size:13px;"></i>
                </summary>
                <p style="color: #64748b; font-size: 14px; line-height: 1.7; margin: 12px 0 0 0; padding-top: 12px; border-top: 1px dashed #f1f5f9;">
                    Evet, kayıtlı <?php echo esc_html($city_name); ?> ustalarımız son teknoloji OBD2 arıza tespit cihazları ile motor arıza lambası, ABS, ESP, airbag ve şanzıman elektriksel arızalarını dakikalar içinde tespit edip garantili tamir sunmaktadır.
                </p>
            </details>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const citySwiper = new Swiper('.swiper-city-mechs', {
        slidesPerView: 1.15,
        spaceBetween: 20,
        grabCursor: true,
        autoplay: {
            delay: 3500,
            disableOnInteraction: false,
        },
        navigation: {
            nextEl: '#city-next',
            prevEl: '#city-prev',
        },
        breakpoints: {
            640:  { slidesPerView: 2.2 },
            900:  { slidesPerView: 3.1 },
            1200: { slidesPerView: 4 },
        },
    });

    const prevBtn = document.getElementById('city-prev');
    const nextBtn = document.getElementById('city-next');
    if (prevBtn) prevBtn.addEventListener('click', () => citySwiper.slidePrev());
    if (nextBtn) nextBtn.addEventListener('click', () => citySwiper.slideNext());
});
</script>

<?php get_footer(); ?>