<?php
/**
 * Template Name: Nöbetçi & Pazar Günü Açık Tamirciler
 * 
 * Hafta sonu, pazar günleri ve 7/24 acil durumlarda açık oto tamirciler ve yol yardım servisleri
 */
get_header();

// Filtre Değerleri
$selected_city = sanitize_text_field( $_GET['sehir'] ?? $_GET['city'] ?? '' );
$selected_type = sanitize_text_field( $_GET['tip'] ?? 'all' );
$paged = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ), isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1 );

// Meta Query Oluştur
if ( $selected_type === 'pazar' ) {
    $meta_query = array(
        array(
            'key'     => '_mechanic_sunday',
            'value'   => array( 'Evet', 'yes', '1' ),
            'compare' => 'IN',
        )
    );
} elseif ( $selected_type === 'yol_yardim' ) {
    $meta_query = array(
        array(
            'key'     => '_mechanic_road_assist',
            'value'   => array( 'Evet', 'yes', '1' ),
            'compare' => 'IN',
        )
    );
} else {
    // Tümü: Ya pazar günü açık, ya 7/24 yol yardımı var
    $meta_query = array(
        'relation' => 'OR',
        array(
            'key'     => '_mechanic_sunday',
            'value'   => array( 'Evet', 'yes', '1' ),
            'compare' => 'IN',
        ),
        array(
            'key'     => '_mechanic_road_assist',
            'value'   => array( 'Evet', 'yes', '1' ),
            'compare' => 'IN',
        )
    );
}

$args = array(
    'post_type'                  => 'mechanic',
    'post_status'                => 'publish',
    'posts_per_page'             => 12,
    'paged'                      => $paged,
    'meta_query'                 => $meta_query,
    'nobetci_prioritize_sunday'  => ( $selected_type === 'all' ),
    'update_post_term_cache'     => true,
    'update_post_meta_cache'     => true,
);

// Şehir Filtresi
if ( ! empty( $selected_city ) ) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'mechanic_city',
            'field'    => 'slug',
            'terms'    => $selected_city,
        ),
    );
}

$nobetci_order_hook = function( $clauses, $query ) {
    if ( $query->get( 'nobetci_prioritize_sunday' ) ) {
        global $wpdb;
        $clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS pm_sun ON ({$wpdb->posts}.ID = pm_sun.post_id AND pm_sun.meta_key = '_mechanic_sunday' AND pm_sun.meta_value IN ('Evet', 'yes', '1')) ";
        $clauses['orderby'] = " (CASE WHEN pm_sun.meta_value IS NOT NULL THEN 1 ELSE 0 END) DESC, {$wpdb->posts}.post_date DESC ";
    }
    return $clauses;
};

add_filter( 'posts_clauses', $nobetci_order_hook, 10, 2 );
$emergency_query = new WP_Query( $args );
remove_filter( 'posts_clauses', $nobetci_order_hook, 10 );

// Tüm Şehirleri Çek
$all_cities = get_terms( array(
    'taxonomy'   => 'mechanic_city',
    'hide_empty' => true,
    'orderby'    => 'name',
    'order'      => 'ASC',
) );

$clean_default_img = 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?auto=format&fit=crop&w=600&q=75';
?>

<!-- Schema.org SEO Structured Data -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebPage",
    "name": "Pazar Günü Açık Oto Tamirciler & 7/24 Nöbetçi Yol Yardım",
    "description": "Hafta sonu, pazar günleri ve 7/24 acil durumlarda açık oto tamircileri, oto kurtarıcıları ve nöbetçi servisleri anında bulun.",
    "publisher": {
        "@type": "Organization",
        "name": "OtoTamir360"
    }
}
</script>

<style>
.emergency-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #1e293b 100%);
    padding: 60px 0 50px;
    color: white;
    position: relative;
    overflow: hidden;
}
.emergency-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba(239, 68, 68, 0.15) 0%, transparent 70%);
    border-radius: 50%;
}
.emergency-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(239, 68, 68, 0.2);
    color: #fca5a5;
    border: 1px solid rgba(239, 68, 68, 0.4);
    padding: 6px 16px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 16px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.emergency-badge i {
    animation: pulse 1.5s infinite;
}
@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.2); opacity: 0.7; }
}
.emergency-title {
    font-size: 34px;
    font-weight: 800;
    margin: 0 0 12px;
    letter-spacing: -0.5px;
}
.emergency-desc {
    color: #cbd5e1;
    font-size: 16px;
    max-width: 680px;
    margin: 0 0 30px;
    line-height: 1.6;
}
.emergency-filter-bar {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 16px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}
.emergency-filter-bar select, .emergency-filter-bar .filter-tab-btn {
    padding: 11px 18px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    font-family: inherit;
    transition: all 0.2s;
}
.emergency-filter-bar select {
    background: white;
    color: #0f172a;
    border: 1px solid #cbd5e1;
    cursor: pointer;
    min-width: 180px;
}
.filter-tabs {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.filter-tab-btn {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.2);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.filter-tab-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}
.filter-tab-btn.active {
    background: #ef4444;
    border-color: #ef4444;
    color: white;
    box-shadow: 0 4px 14px rgba(239, 68, 68, 0.4);
}

/* KART LİSTESİ */
.emergency-section {
    background: #f8fafc;
    padding: 50px 0 80px;
    min-height: 60vh;
}
.emergency-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 40px;
}
.em-card {
    background: white;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    transition: all 0.25s ease;
}
.em-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
    border-color: #cbd5e1;
}
.em-card-img-wrap {
    position: relative;
    height: 180px;
    overflow: hidden;
    background: #0f172a;
}
.em-card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}
.em-card:hover .em-card-img {
    transform: scale(1.05);
}
.em-card-badges {
    position: absolute;
    top: 12px;
    left: 12px;
    right: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
}
.em-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
.em-badge.sunday {
    background: #10b981;
    color: white;
}
.em-badge.road {
    background: #ef4444;
    color: white;
}
.em-card-body {
    padding: 22px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}
.em-card-title {
    font-size: 17px;
    font-weight: 800;
    margin: 0 0 8px;
    color: #0f172a;
    line-height: 1.35;
}
.em-card-title a {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s;
}
.em-card-title a:hover {
    color: #ef4444;
}
.em-card-loc {
    font-size: 13.5px;
    color: #64748b;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.em-card-features {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 18px;
    font-size: 12.5px;
    color: #334155;
}
.em-feat-item {
    display: flex;
    align-items: center;
    gap: 8px;
}
.em-feat-item i {
    width: 18px;
    text-align: center;
}
.em-card-actions {
    margin-top: auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    padding-top: 14px;
    border-top: 1px solid #f1f5f9;
}
.btn-em-call {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: white;
    padding: 10px;
    border-radius: 8px;
    text-align: center;
    font-weight: 700;
    font-size: 13px;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: all 0.2s;
}
.btn-em-call:hover {
    box-shadow: 0 4px 12px rgba(234, 88, 12, 0.3);
    color: white;
}
.btn-em-wp {
    background: #10b981;
    color: white;
    padding: 10px;
    border-radius: 8px;
    text-align: center;
    font-weight: 700;
    font-size: 13px;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: all 0.2s;
}
.btn-em-wp:hover {
    background: #059669;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    color: white;
}

/* SEO Bilgi Alanı */
.emergency-seo-content {
    background: white;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    padding: 36px 40px;
    margin-top: 40px;
    color: #475569;
    line-height: 1.8;
}
.emergency-seo-content h2 {
    font-size: 22px;
    color: #0f172a;
    font-weight: 800;
    margin: 0 0 14px;
}
.emergency-seo-content h3 {
    font-size: 18px;
    color: #1e293b;
    font-weight: 700;
    margin: 24px 0 10px;
}

@media (max-width: 992px) {
    .emergency-grid { grid-template-columns: repeat(2, 1fr); }
    .emergency-title { font-size: 28px; }
}
@media (max-width: 640px) {
    .emergency-grid { grid-template-columns: 1fr; }
    .emergency-filter-bar { flex-direction: column; align-items: stretch; }
    .emergency-filter-bar select { width: 100%; }
    .filter-tabs { width: 100%; justify-content: space-between; }
    .filter-tab-btn { flex: 1; text-align: center; justify-content: center; font-size: 12.5px; padding: 10px; }
}

/* Sayfalama (Pagination) */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin: 40px 0 20px;
    width: 100%;
}
.pagination ul,
ul.page-numbers {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    list-style: none;
    padding: 0;
    margin: 0;
}
.pagination ul li,
ul.page-numbers li {
    list-style: none;
    margin: 0;
    padding: 0;
    display: inline-flex;
}
.pagination .page-numbers,
ul.page-numbers .page-numbers {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 42px;
    height: 42px;
    padding: 0 16px;
    border-radius: 10px;
    border: 1.5px solid #e2e8f0;
    background: #ffffff;
    color: #334155;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}
.pagination a.page-numbers:hover,
ul.page-numbers a.page-numbers:hover {
    border-color: #ef4444;
    color: #ef4444;
    background: #fef2f2;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
}
.pagination .page-numbers.current,
ul.page-numbers .page-numbers.current {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #ffffff !important;
    border-color: #ef4444;
    box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35);
}
.pagination .page-numbers.dots,
ul.page-numbers .page-numbers.dots {
    border: none;
    background: transparent;
    color: #94a3b8;
    box-shadow: none;
    cursor: default;
}
</style>

<!-- 1. HERO BÖLÜMÜ & FİLTRELER -->
<section class="emergency-hero">
    <div class="container">
        <div class="emergency-badge">
            <i class="fa-solid fa-bell"></i> 7/24 Nöbetçi & Hafta Sonu Servisleri
        </div>
        <h1 class="emergency-title">Pazar Günü Açık Oto Tamirciler & Acil Yol Yardım</h1>
        <p class="emergency-desc">
            Hafta sonu yolda kaldığınızda veya pazar günü acil oto tamirci, lastikçi veya çekici ihtiyacınız olduğunda yakınınızdaki açık nöbetçi servisleri anında bulun ve iletişime geçin.
        </p>

        <!-- Filtreleme Çubuğu -->
        <div class="emergency-filter-bar">
            <!-- Şehir Seçimi -->
            <select onchange="window.location.href=this.value;">
                <option value="<?php echo esc_url( remove_query_arg( array( 'sehir', 'city', 'paged' ) ) ); ?>">
                    🏙️ Tüm Şehirler (81 İl)
                </option>
                <?php if ( ! empty( $all_cities ) && ! is_wp_error( $all_cities ) ) : ?>
                    <?php foreach ( $all_cities as $city ) : ?>
                        <option value="<?php echo esc_url( add_query_arg( array( 'sehir' => $city->slug, 'paged' => false ) ) ); ?>" <?php selected( $selected_city, $city->slug ); ?>>
                            <?php echo esc_html( $city->name ); ?> (<?php echo (int) $city->count; ?> Usta)
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>

            <!-- Hizmet Türü Filtresi -->
            <div class="filter-tabs">
                <a href="<?php echo esc_url( add_query_arg( array( 'tip' => 'all', 'paged' => false ) ) ); ?>" class="filter-tab-btn <?php echo ( $selected_type === 'all' ) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-list-check"></i> Tümü
                </a>
                <a href="<?php echo esc_url( add_query_arg( array( 'tip' => 'pazar', 'paged' => false ) ) ); ?>" class="filter-tab-btn <?php echo ( $selected_type === 'pazar' ) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-calendar-day"></i> Pazar Günü Açık
                </a>
                <a href="<?php echo esc_url( add_query_arg( array( 'tip' => 'yol_yardim', 'paged' => false ) ) ); ?>" class="filter-tab-btn <?php echo ( $selected_type === 'yol_yardim' ) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-truck-pickup"></i> 7/24 Yol Yardım & Çekici
                </a>
            </div>
        </div>
    </div>
</section>

<!-- 2. İLANLAR LİSTESİ -->
<section class="emergency-section">
    <div class="container">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <div style="font-size: 15px; color: #64748b; font-weight: 600;">
                Toplam <strong style="color:#0f172a;"><?php echo (int) $emergency_query->found_posts; ?></strong> açık servis listeleniyor
                <?php if ( ! empty( $selected_city ) ) : ?>
                    (Filtre: <em><?php echo esc_html( strtoupper( $selected_city ) ); ?></em>)
                <?php endif; ?>
            </div>
        </div>

        <?php if ( $emergency_query->have_posts() ) : ?>
            <div class="emergency-grid">
                <?php while ( $emergency_query->have_posts() ) : $emergency_query->the_post(); 
                    $pid = get_the_ID();
                    $img = get_post_meta( $pid, '_custom_mechanic_image', true );
                    if ( empty( $img ) ) {
                        $img = has_post_thumbnail() ? get_the_post_thumbnail_url( $pid, 'medium_large' ) : $clean_default_img;
                    }
                    if ( function_exists( 'ototamir_optimize_image_url' ) ) {
                        $img = ototamir_optimize_image_url( $img, 450 );
                    }

                    $phone = get_post_meta( $pid, '_mechanic_phone', true );
                    $address = get_post_meta( $pid, '_mechanic_address', true );
                    $sunday = get_post_meta( $pid, '_mechanic_sunday', true );
                    $road_assist = get_post_meta( $pid, '_mechanic_road_assist', true );
                    
                    $is_sunday = in_array( $sunday, array( 'Evet', 'yes', '1' ) );
                    $is_road   = in_array( $road_assist, array( 'Evet', 'yes', '1' ) );

                    $cities = wp_get_post_terms( $pid, 'mechanic_city' );
                    $districts = wp_get_post_terms( $pid, 'mechanic_district' );
                    $city_name = ($cities && !is_wp_error($cities)) ? $cities[0]->name : '';
                    $dist_name = ($districts && !is_wp_error($districts)) ? $districts[0]->name : '';
                    $location = trim( $dist_name . ', ' . $city_name, ', ' );

                    $wp_number = preg_replace('/[^0-9]/', '', $phone);
                    if(strlen($wp_number) == 11 && substr($wp_number, 0, 1) == '0') {
                        $wp_number = '90' . substr($wp_number, 1);
                    } elseif(strlen($wp_number) == 10) {
                        $wp_number = '90' . $wp_number;
                    }
                    $wp_text = urlencode( "Merhaba " . get_the_title() . ", Ototamir360 üzerinden acil durum/nöbetçi servis için ulaşıyorum. Müsait misiniz?" );
                ?>
                    <div class="em-card">
                        <div class="em-card-img-wrap">
                            <img src="<?php echo esc_url( $img ); ?>" alt="<?php the_title_attribute(); ?>" class="em-card-img" loading="lazy">
                            <div class="em-card-badges">
                                <?php if ( $is_sunday ) : ?>
                                    <span class="em-badge sunday"><i class="fa-solid fa-calendar-check"></i> Pazar Açık</span>
                                <?php endif; ?>
                                <?php if ( $is_road ) : ?>
                                    <span class="em-badge road"><i class="fa-solid fa-truck-pickup"></i> 7/24 Yol Yardım</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="em-card-body">
                            <h3 class="em-card-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>

                            <?php if ( ! empty( $location ) ) : ?>
                                <div class="em-card-loc">
                                    <i class="fa-solid fa-location-dot" style="color:#ef4444;"></i>
                                    <span><?php echo esc_html( $location ); ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="em-card-features">
                                <?php if ( $is_sunday ) : ?>
                                    <div class="em-feat-item">
                                        <i class="fa-solid fa-calendar-check" style="color: #10b981;"></i>
                                        <span>Pazar Günü: <strong style="color:#10b981;">Açık / Nöbetçi</strong></span>
                                    </div>
                                    <div class="em-feat-item">
                                        <i class="fa-solid fa-clock" style="color: <?php echo $is_road ? '#ef4444' : '#64748b'; ?>;"></i>
                                        <span>Acil Yol Yardım: <strong><?php echo $is_road ? '7/24 Açık' : 'Mesai Saatlerinde'; ?></strong></span>
                                    </div>
                                <?php else : ?>
                                    <div class="em-feat-item">
                                        <i class="fa-solid fa-truck-pickup" style="color: #ea580c;"></i>
                                        <span>Pazar Durumu: <strong style="color:#ea580c;">7/24 Acil & Yol Yardım</strong></span>
                                    </div>
                                    <div class="em-feat-item">
                                        <i class="fa-solid fa-clock" style="color: #ef4444;"></i>
                                        <span>Acil Destek: <strong style="color:#ef4444;">7/24 Kesintisiz Açık</strong></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="em-card-actions">
                                <?php if ( ! empty( $phone ) && $phone !== '+90' ) : ?>
                                    <a href="tel:<?php echo esc_attr( $phone ); ?>" class="btn-em-call">
                                        <i class="fa-solid fa-phone"></i> Hemen Ara
                                    </a>
                                    <a href="https://wa.me/<?php echo $wp_number; ?>?text=<?php echo $wp_text; ?>" target="_blank" class="btn-em-wp">
                                        <i class="fa-brands fa-whatsapp"></i> WhatsApp
                                    </a>
                                <?php else : ?>
                                    <a href="<?php the_permalink(); ?>" class="btn-em-call" style="grid-column: span 2;">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Detayları Gör
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>

            <!-- Sayfalama -->
            <?php if ( $emergency_query->max_num_pages > 1 ) : ?>
                <div class="pagination" style="margin: 40px 0;">
                    <?php
                    echo paginate_links( array(
                        'base'      => add_query_arg( 'paged', '%#%' ),
                        'format'    => '',
                        'prev_text' => '<i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Önceki',
                        'next_text' => 'Sonraki <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>',
                        'total'     => $emergency_query->max_num_pages,
                        'current'   => $paged,
                        'type'      => 'plain',
                    ) );
                    ?>
                </div>
            <?php endif; ?>

        <?php else : ?>
            <div class="empty-box" style="margin: 40px auto;">
                <div class="empty-icon" style="background:#fef2f2; color:#ef4444;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3>Arama Kriterine Uygun Açık Servis Bulunamadı</h3>
                <p>Seçili şehir veya filtrede şu anda kayıtlı pazar günü açık veya 7/24 yol yardım servisi bulunamadı. Lütfen filtreyi değiştirip tüm şehirleri görüntüleyin.</p>
                <a href="<?php echo esc_url( remove_query_arg( array('sehir', 'tip', 'paged') ) ); ?>" class="btn-primary">
                    <i class="fa-solid fa-rotate-left"></i> Tüm Şehirleri Göster
                </a>
            </div>
        <?php endif; ?>

        <!-- 3. SEO BİLGİLENDİRME METNİ -->
        <div class="emergency-seo-content">
            <h2>Pazar Günü ve Hafta Sonu Açık Oto Tamircisi Nasıl Bulunur?</h2>
            <p>
                Hafta sonu yola çıktığınızda, uzun yolda veya şehir içinde aracınızın aniden arıza yapması ya da lastiğinizin patlaması sürücülerin en sık karşılaştığı acil durumlardan biridir. Sanayi sitelerinin büyük çoğunluğu pazar günleri kapalı olsa da Türkiye genelinde nöbetçi olarak hizmet veren oto tamircileri, 7/24 yol yardım araçları ve acil oto kurtarıcılar bulunmaktadır.
            </p>
            <h3>Acil Durumlarda ve Yolda Kaldığınızda Dikkat Etmeniz Gerekenler</h3>
            <ul>
                <li><strong>Güvenliği Sağlayın:</strong> Aracınızı güvenli bir emniyet şeridine veya cebe çekin, dörtlü flaşörlerinizi yakın ve reflektörünüzü yerleştirin.</li>
                <li><strong>Doğru Servisi Arayın:</strong> Arızanın kaynağına göre (akü bitmesi, lastik patlaması, motor harareti, yakıt tükenmesi) ilgili uzman servis veya çekiciyle iletişime geçin.</li>
                <li><strong>Konum Paylaşın:</strong> Sayfamızdaki WhatsApp bağlantılarını kullanarak durduğunuz noktanın canlı konumunu servis ustasına ileterek size çok daha hızlı ulaşmalarını sağlayabilirsiniz.</li>
            </ul>
        </div>

    </div>
</section>

<?php get_footer(); ?>
