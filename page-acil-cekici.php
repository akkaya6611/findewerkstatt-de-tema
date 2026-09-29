<?php
/**
 * Template Name: En Yakın Çekici & Acil Yol Yardım
 * Description: 7/24 acil durum oto kurtarıcı, çekici ve yol yardım çağırma sayfası. HTML5 Geolocation ile tek tıkla konum alma, canlı haritada iğneleme, rota/yol çizimi, en yakın çekiciyi otomatik hesaplama ve WhatsApp'tan anında harita koordinatı gönderme desteği.
 * 
 * @package OtoTamir360
 */

get_header();

require_once get_template_directory() . '/inc/seo-geo.php';

// Seçili şehir ve ilçe
$selected_city = sanitize_text_field( $_GET['cekici_sehir'] ?? $_GET['city'] ?? '' );
$selected_district = sanitize_text_field( $_GET['cekici_ilce'] ?? $_GET['district'] ?? '' );
$paged = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ), isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1 );

// Çekici ve Yol Yardım Verilerini Çek
$meta_query = array(
    'relation' => 'OR',
    array(
        'key'     => '_mechanic_road_assist',
        'value'   => array( 'Evet', 'yes', '1' ),
        'compare' => 'IN',
    )
);

// Tax Query: Çekici servis türü
$tax_query = array(
    'relation' => 'OR',
    array(
        'taxonomy' => 'service_type',
        'field'    => 'slug',
        'terms'    => array( 'oto-cekici-yol-yardim', 'oto-kurtarma', 'yol-yardim' ),
    )
);

if ( ! empty( $selected_city ) ) {
    $tax_and = array(
        'relation' => 'AND',
        array(
            'taxonomy' => 'service_type',
            'field'    => 'slug',
            'terms'    => array( 'oto-cekici-yol-yardim', 'oto-kurtarma', 'yol-yardim' ),
        ),
        array(
            'taxonomy' => 'mechanic_city',
            'field'    => 'slug',
            'terms'    => $selected_city,
        )
    );

    if ( ! empty( $selected_district ) ) {
        $d_term = get_term_by( 'slug', sanitize_title( $selected_district ), 'mechanic_district' );
        if ( ! $d_term ) {
            $d_term = get_term_by( 'name', $selected_district, 'mechanic_district' );
        }
        if ( $d_term ) {
            $tax_and[] = array(
                'taxonomy' => 'mechanic_district',
                'field'    => 'term_id',
                'terms'    => $d_term->term_id,
            );
        } else {
            $tax_and[] = array(
                'taxonomy' => 'mechanic_district',
                'field'    => 'name',
                'terms'    => $selected_district,
            );
        }
    }

    $tax_query = $tax_and;
}

$args = array(
    'post_type'      => 'mechanic',
    'post_status'    => 'publish',
    'posts_per_page' => 18, // 3 sütunlu ızgara için tam 6 satır
    'paged'          => $paged,
    'tax_query'      => $tax_query,
    'meta_query'     => $meta_query,
);

$tow_query = new WP_Query( $args );

// Eğer seçili ilçe ile usta bulunamadıysa şehre geri çekil
$district_fallback_notice = false;
if ( ! $tow_query->have_posts() && ! empty( $selected_district ) && ! empty( $selected_city ) ) {
    $args_city_fallback = array(
        'post_type'      => 'mechanic',
        'post_status'    => 'publish',
        'posts_per_page' => 18,
        'paged'          => $paged,
        'tax_query'      => array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'service_type',
                'field'    => 'slug',
                'terms'    => array( 'oto-cekici-yol-yardim', 'oto-kurtarma', 'yol-yardim' ),
            ),
            array(
                'taxonomy' => 'mechanic_city',
                'field'    => 'slug',
                'terms'    => $selected_city,
            )
        ),
        'meta_query'     => $meta_query,
    );
    $tow_query = new WP_Query( $args_city_fallback );
    if ( $tow_query->have_posts() ) {
        $district_fallback_notice = true;
    }
}

// Eğer tax_query ile çıkmazsa sadece meta_query ile genişletilmiş yedek çek
if ( ! $tow_query->have_posts() && empty( $selected_city ) ) {
    $args_fallback = array(
        'post_type'      => 'mechanic',
        'post_status'    => 'publish',
        'posts_per_page' => 18,
        'paged'          => $paged,
        'tax_query'      => array(
            array(
                'taxonomy' => 'service_type',
                'field'    => 'slug',
                'terms'    => array( 'oto-cekici-yol-yardim', 'oto-kurtarma' ),
            )
        )
    );
    $tow_query = new WP_Query( $args_fallback );
}

$all_cities = get_transient( 'ototamir_all_cities_terms' );
if ( false === $all_cities ) {
    $all_cities = get_terms( array(
        'taxonomy'   => 'mechanic_city',
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC'
    ) );
    if ( ! is_wp_error( $all_cities ) ) {
        set_transient( 'ototamir_all_cities_terms', $all_cities, 12 * HOUR_IN_SECONDS );
    }
}
?>

<!-- Leaflet.js Harita Kütüphanesi (Non-render blocking) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" crossorigin="anonymous" media="print" onload="this.media='all'" />
<noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" crossorigin="anonymous" /></noscript>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js" crossorigin="anonymous" defer></script>
<!-- Türkiye 81 İl ve İlçe Konum Veritabanı -->
<script src="<?php echo esc_url( get_template_directory_uri() . '/assets/js/turkey.js' ); ?>" defer></script>

<style>
/* ===================================================
   ACİL ÇEKİCİ & YOL YARDIM ÖZEL STİLLERİ
=================================================== */
.tow-page-wrapper {
    background: #f8fafc;
    min-height: 85vh;
    padding: 24px 0 80px;
}

/* BREADCRUMB */
.tow-breadcrumb {
    margin-bottom: 20px;
    font-size: 13px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.tow-breadcrumb a {
    color: #475569;
    text-decoration: none;
    font-weight: 600;
}
.tow-breadcrumb span.current {
    color: #dc2626;
    font-weight: 700;
}

/* ACİL DURUM HERO BANNER */
.tow-hero-card {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 40%, #450a0a 100%);
    border-radius: 24px;
    padding: 40px;
    color: white;
    position: relative;
    overflow: hidden;
    margin-bottom: 30px;
    box-shadow: 0 16px 36px rgba(220, 38, 38, 0.15);
    border: 1.5px solid rgba(239, 68, 68, 0.25);
}
.tow-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(239, 68, 68, 0.2);
    border: 1px solid rgba(239, 68, 68, 0.5);
    color: #fca5a5;
    padding: 6px 16px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 16px;
    animation: pulseBadge 2s infinite ease-in-out;
}
@keyframes pulseBadge {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.85; transform: scale(1.02); }
}
.tow-hero-title {
    font-size: 34px;
    font-weight: 900;
    line-height: 1.25;
    margin: 0 0 12px;
    color: white;
}
.tow-hero-title span {
    color: #f87171;
}
.tow-hero-desc {
    font-size: 15.5px;
    line-height: 1.6;
    color: #cbd5e1;
    margin: 0 0 28px;
    max-width: 720px;
}

/* GPS AKSİYON KUTUSU */
.tow-geo-action-box {
    background: rgba(255, 255, 255, 0.08);
    border: 1.5px solid rgba(239, 68, 68, 0.4);
    border-radius: 18px;
    padding: 20px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    backdrop-filter: blur(10px);
}
.tow-geo-info h4 {
    margin: 0 0 4px;
    font-size: 16px;
    font-weight: 800;
    color: white;
    display: flex;
    align-items: center;
    gap: 8px;
}
.tow-geo-info p {
    margin: 0;
    font-size: 13.5px;
    color: #cbd5e1;
}
.tow-geo-btn {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    border: none;
    padding: 13px 24px;
    border-radius: 12px;
    font-size: 14.5px;
    font-weight: 800;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 16px rgba(220, 38, 38, 0.4);
    transition: all 0.2s;
    white-space: nowrap;
}
.tow-geo-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(220, 38, 38, 0.5);
}
.tow-geo-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}
.tow-geo-status {
    width: 100%;
    font-size: 13px;
    font-weight: 700;
    margin-top: 4px;
    display: none;
}
.tow-geo-status.success { color: #4ade80; display: block; }
.tow-geo-status.error   { color: #f87171; display: block; }

/* CANLI HARİTA & EN YAKIN ÇEKİCİ ALANI */
.tow-map-section {
    display: none;
    margin-bottom: 35px;
    animation: fadeInMap 0.4s ease-out forwards;
}
@keyframes fadeInMap {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* EN YAKIN ÇEKİCİ VIP KARTI */
.nearest-tow-vip-card {
    background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
    border: 2px solid #ef4444;
    border-radius: 20px;
    padding: 24px 28px;
    margin-bottom: 20px;
    box-shadow: 0 10px 30px rgba(239, 68, 68, 0.15);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
    position: relative;
    overflow: hidden;
}
.nearest-tow-vip-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 6px;
    height: 100%;
    background: #ef4444;
}
.vip-badge-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #dc2626;
    color: #ffffff;
    font-size: 12px;
    font-weight: 800;
    padding: 4px 12px;
    border-radius: 20px;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.nearest-title {
    font-size: 22px;
    font-weight: 900;
    color: #0f172a;
    margin: 0 0 6px 0;
}
.nearest-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    font-size: 14px;
    color: #475569;
    flex-wrap: wrap;
}
.nearest-meta strong {
    color: #dc2626;
    font-size: 15px;
}
.nearest-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.btn-vip-call {
    background: #0f172a;
    color: #ffffff !important;
    padding: 12px 20px;
    border-radius: 12px;
    font-weight: 800;
    font-size: 14px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}
.btn-vip-call:hover {
    background: #1e293b;
    transform: translateY(-2px);
}
.btn-vip-wp {
    background: #16a34a;
    color: #ffffff !important;
    padding: 12px 20px;
    border-radius: 12px;
    font-weight: 800;
    font-size: 14px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}
.btn-vip-wp:hover {
    background: #15803d;
    transform: translateY(-2px);
}
.btn-vip-route {
    background: #2563eb;
    color: #ffffff !important;
    padding: 12px 20px;
    border-radius: 12px;
    font-weight: 800;
    font-size: 14px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}
.btn-vip-route:hover {
    background: #1d4ed8;
    transform: translateY(-2px);
}

/* HARİTA KUTUSU */
.live-map-box {
    background: #ffffff;
    border-radius: 20px;
    border: 1.5px solid #e2e8f0;
    padding: 16px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.06);
}
#towLiveMap {
    width: 100%;
    height: 380px;
    border-radius: 14px;
    z-index: 1;
}

/* FİLTRE VE ŞEHİR ÇUBUĞU */
.tow-filter-bar {
    background: white;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    padding: 18px 24px;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
}
.tow-filter-title {
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 8px;
}
.tow-city-form {
    display: flex;
    gap: 10px;
    align-items: center;
}
.tow-city-select {
    padding: 10px 14px;
    border-radius: 10px;
    border: 1.5px solid #cbd5e1;
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
    background: white;
    min-width: 200px;
}
.tow-city-submit {
    background: #0f172a;
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 13.5px;
    cursor: pointer;
    transition: background 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.tow-city-submit:hover {
    background: #1e293b;
}
.tow-city-reset {
    background: #f1f5f9;
    color: #475569;
    padding: 10px 16px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 13.5px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1.5px solid #cbd5e1;
    transition: all 0.2s;
}
.tow-city-reset:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.tow-card.is-in-district {
    border-color: #86efac;
    box-shadow: 0 4px 16px rgba(34, 197, 94, 0.12);
}
.tow-card.is-in-district:hover {
    border-color: #22c55e;
    box-shadow: 0 14px 28px rgba(34, 197, 94, 0.2);
}

/* ÇEKİCİ KARTLARI IZGARASI (TAM EŞİT YÜKSEKLİK) */
.tow-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 22px;
    align-items: stretch;
    margin-bottom: 40px;
}
.tow-card {
    background: white;
    border-radius: 18px;
    border: 1.5px solid #e2e8f0;
    padding: 22px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
    box-sizing: border-box;
    position: relative;
    transition: all 0.25s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
}
.tow-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 28px rgba(0, 0, 0, 0.07);
    border-color: #ef4444;
}

.tow-card-header {
    display: flex;
    gap: 14px;
    align-items: flex-start;
    margin-bottom: 12px;
}
.tow-avatar {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    background: #fee2e2;
    color: #dc2626;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
    border: 1.5px solid #fecaca;
    overflow: hidden;
}
.tow-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.tow-name-wrap {
    flex: 1;
    min-width: 0;
}
.tow-name-wrap h3 {
    margin: 0 0 6px;
    font-size: 15.5px;
    font-weight: 800;
    line-height: 1.35;
    min-height: 42px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    word-break: break-word;
}
.tow-name-wrap h3 a {
    color: #0f172a;
    text-decoration: none;
    transition: color 0.2s;
}
.tow-name-wrap h3 a:hover {
    color: #dc2626;
}
.tow-loc {
    font-size: 12.5px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* MESAFE ROZETİ */
.card-distance-badge {
    display: none;
    font-size: 12px;
    font-weight: 800;
    color: #059669;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    padding: 3px 10px;
    border-radius: 20px;
    margin-bottom: 10px;
    width: fit-content;
}

.tow-features {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 16px;
}
.tow-pill {
    font-size: 11.5px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.tow-pill.red {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}
.tow-pill.green {
    background: #f0fdf4;
    color: #16a34a;
    border: 1px solid #bbf7d0;
}

/* BUTONLAR */
.tow-actions {
    margin-top: auto;
    padding-top: 14px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    border-top: 1px solid #f1f5f9;
}
.tow-btn-call {
    background: #0f172a;
    color: white !important;
    text-decoration: none;
    font-size: 13px;
    font-weight: 800;
    padding: 11px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s;
}
.tow-btn-call:hover {
    background: #1e293b;
}
.tow-btn-wp {
    background: #16a34a;
    color: white !important;
    text-decoration: none;
    font-size: 13px;
    font-weight: 800;
    padding: 11px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: all 0.2s;
}
.tow-btn-wp:hover {
    background: #15803d;
}

/* SAYFALAMA */
.tow-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin: 20px 0 45px;
    flex-wrap: wrap;
}
.tow-pagination .page-numbers {
    padding: 8px 14px;
    border-radius: 10px;
    background: white;
    border: 1.5px solid #cbd5e1;
    color: #1e293b;
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 38px;
    height: 38px;
    box-sizing: border-box;
}
.tow-pagination .page-numbers:hover {
    background: #fef2f2;
    border-color: #f87171;
    color: #dc2626;
}
.tow-pagination .page-numbers.current {
    background: #dc2626;
    border-color: #dc2626;
    color: white;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35);
}
.tow-pagination .page-numbers.dots {
    border: none;
    background: transparent;
    color: #94a3b8;
    padding: 8px 4px;
    min-width: auto;
}

/* GÜVENLİK KARTI */
.tow-safety-card {
    background: white;
    border-radius: 20px;
    border: 1.5px solid #fed7d7;
    padding: 30px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.03);
}
.tow-safety-card h3 {
    margin: 0 0 16px;
    color: #991b1b;
    font-size: 20px;
    font-weight: 900;
    display: flex;
    align-items: center;
    gap: 10px;
}
.tow-safety-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}
.tow-safety-item {
    background: #fff5f5;
    border-radius: 14px;
    padding: 18px;
    border: 1px solid #fed7d7;
}
.tow-safety-item h4 {
    margin: 0 0 6px;
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
}
.tow-safety-item p {
    margin: 0;
    font-size: 13px;
    color: #475569;
    line-height: 1.55;
}

@media (max-width: 1024px) {
    .tow-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
    .tow-hero-card { padding: 26px 18px; }
    .tow-hero-title { font-size: 23px; }
    .tow-hero-desc { font-size: 14px; }
    .tow-geo-action-box { flex-direction: column; align-items: stretch; padding: 18px 16px; }
    .tow-geo-btn { width: 100%; justify-content: center; }
    .tow-filter-bar { flex-direction: column; align-items: stretch; }
    .tow-city-form { flex-direction: column; }
    .tow-city-select { width: 100%; min-width: 0; }
    .tow-city-submit { width: 100%; }
    .tow-grid { grid-template-columns: 1fr; }
    .tow-safety-grid { grid-template-columns: 1fr; }
    .nearest-actions { width: 100%; flex-direction: column; }
    .btn-vip-call, .btn-vip-wp, .btn-vip-route { width: 100%; justify-content: center; }
}
</style>

<div class="tow-page-wrapper">
    <div class="container">

        <!-- BREADCRUMB -->
        <nav class="tow-breadcrumb" aria-label="Ekmek Kırıntıları">
            <a href="<?php echo esc_url( home_url('/') ); ?>"><i class="fa-solid fa-house" style="font-size:12px;"></i> Ana Sayfa</a>
            <span>/</span>
            <span class="current">En Yakın Oto Çekici & Acil Yol Yardım</span>
        </nav>

        <!-- ACİL DURUM HERO KARTI -->
        <div class="tow-hero-card">
            <div class="tow-hero-badge">
                <i class="fa-solid fa-truck-pickup"></i>
                <span>🚨 7/24 Kesintisiz Oto Kurtarma & Çekici Hizmeti</span>
            </div>
            <h1 class="tow-hero-title">
                Yolda mı Kaldınız? <br>
                <span>Tek Tıkla En Yakın Çekiciyi Çağırın</span>
            </h1>
            <p class="tow-hero-desc">
                Akü bitmesi, lastik patlaması, mekanik arıza veya kaza anında yanınızdayız. GPS butonuna basın; sistem canlı konumunuzu alsın, harita üzerinde size en yakın açık yol yardım firmasını iğneleyip rotanızı çıkarsın.
            </p>

            <!-- GPS AKSİYON KUTUSU -->
            <div class="tow-geo-action-box">
                <div class="tow-geo-info">
                    <h4><i class="fa-solid fa-location-crosshairs" style="color:#ef4444;"></i> Canlı Konumunuzu Alın:</h4>
                    <p id="geoCoordText">Bulunduğunuz yeri tam bilmiyorsanız butona tıklayarak GPS konumunuzu alın.</p>
                    <div id="geoStatus" class="tow-geo-status"></div>
                </div>
                <button type="button" id="btnGetLocation" class="tow-geo-btn">
                    <i class="fa-solid fa-location-arrow"></i>
                    <span>Konumumu Al & En Yakın Çekiciyi Bul</span>
                </button>
            </div>
        </div>

        <!-- CANLI HARİTA & EN YAKIN ÇEKİCİ ALANI (GPS ALINDIĞINDA AÇILIR) -->
        <div id="towMapSection" class="tow-map-section">
            
            <!-- EN YAKIN ÇEKİCİ VIP KARTI -->
            <div id="nearestTowCard" class="nearest-tow-vip-card">
                <div>
                    <span class="vip-badge-tag"><i class="fa-solid fa-bullseye"></i> SİZE EN YAKIN ÇEKİCİ</span>
                    <h2 class="nearest-title" id="nearestName">Firma Hesaplanıyor...</h2>
                    <div class="nearest-meta">
                        <span id="nearestDist">📍 Mesafe: <strong>-- km</strong></span>
                        <span id="nearestEta">⏱️ Tahmini Varış: <strong>~10-15 dk</strong></span>
                        <span id="nearestLoc">📌 Konum: <strong>--</strong></span>
                    </div>
                </div>
                <div class="nearest-actions">
                    <a href="#" id="nearestCallBtn" class="btn-vip-call">
                        <i class="fa-solid fa-phone-volume"></i>
                        <span>Hemen Ara</span>
                    </a>
                    <a href="#" id="nearestWpBtn" target="_blank" rel="noopener" class="btn-vip-wp">
                        <i class="fa-brands fa-whatsapp"></i>
                        <span>Konumumu WhatsApp'la</span>
                    </a>
                    <a href="#" id="nearestRouteBtn" target="_blank" rel="noopener" class="btn-vip-route">
                        <i class="fa-solid fa-diamond-turn-right"></i>
                        <span>Haritada Rotayı Aç</span>
                    </a>
                </div>
            </div>

            <!-- LEAFLET CANLI HARİTA -->
            <div class="live-map-box">
                <div id="towLiveMap"></div>
            </div>

        </div>

        <!-- ŞEHİR VE İLÇE FİLTRE ÇUBUĞU -->
        <div class="tow-filter-bar">
            <div class="tow-filter-title">
                <i class="fa-solid fa-location-dot" style="color:#ef4444;"></i>
                <span id="filterBarTitle"><?php 
                    if ( ! empty( $selected_district ) && ! empty( $selected_city ) ) {
                        echo esc_html( ucfirst( $selected_district ) . ', ' . ucfirst( $selected_city ) . ' Bölgesindeki Çekiciler' );
                    } elseif ( ! empty( $selected_city ) ) {
                        echo esc_html( ucfirst( $selected_city ) . ' Bölgesindeki Çekiciler' );
                    } else {
                        echo 'Tüm Türkiye Çekici ve Yol Yardım Servisleri';
                    }
                ?></span>
            </div>
            <form method="get" action="<?php echo esc_url( get_permalink() ); ?>" class="tow-city-form" id="towFilterForm">
                <select name="cekici_sehir" id="towSelectCity" class="tow-city-select">
                    <option value="">Tüm Şehirler (81 İl)</option>
                    <?php if ( ! empty( $all_cities ) && ! is_wp_error( $all_cities ) ) : ?>
                        <?php foreach ( $all_cities as $ct ) : ?>
                            <option value="<?php echo esc_attr( $ct->slug ); ?>" data-name="<?php echo esc_attr( $ct->name ); ?>" <?php selected( $selected_city, $ct->slug ); ?>>
                                <?php echo esc_html( $ct->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <select name="cekici_ilce" id="towSelectDistrict" class="tow-city-select" data-selected="<?php echo esc_attr( $selected_district ); ?>" style="min-width:170px;">
                    <option value="">Tüm İlçeler</option>
                </select>
                <button type="submit" class="tow-city-submit"><i class="fa-solid fa-filter"></i> Filtrele</button>
                <?php if ( ! empty( $selected_city ) || ! empty( $selected_district ) ) : ?>
                    <a href="<?php echo esc_url( get_permalink() ); ?>" class="tow-city-reset" title="Filtreyi Temizle"><i class="fa-solid fa-rotate-left"></i> Sıfırla</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if ( ! empty( $district_fallback_notice ) ) : ?>
            <div style="background:#fffbeb; border:1.5px solid #fef3c7; border-radius:14px; padding:14px 20px; margin-bottom:24px; color:#b45309; font-size:14px; display:flex; align-items:center; gap:10px;">
                <i class="fa-solid fa-circle-info" style="font-size:18px; color:#f59e0b;"></i>
                <span><strong><?php echo esc_html( ucfirst( $selected_district ) ); ?></strong> ilçesinde doğrudan çekici kaydı bulunamadı. <strong><?php echo esc_html( ucfirst( $selected_city ) ); ?></strong> genelindeki en yakın nöbetçi çekiciler listeleniyor.</span>
            </div>
        <?php endif; ?>

        <!-- ÇEKİCİ VE YOL YARDIM LİSTESİ -->
        <?php if ( $tow_query->have_posts() ) : 
            $all_tows_data = array();
        ?>
            <div class="tow-grid" id="towCardsGrid">
                <?php while ( $tow_query->have_posts() ) : $tow_query->the_post(); 
                    $m_id = get_the_ID();
                    $phone = get_post_meta( $m_id, '_mechanic_phone', true );
                    $clean_phone = preg_replace( '/[^0-9]/', '', $phone );
                    if ( substr( $clean_phone, 0, 1 ) === '0' ) {
                        $wp_phone = '90' . substr( $clean_phone, 1 );
                    } elseif ( substr( $clean_phone, 0, 2 ) === '90' ) {
                        $wp_phone = $clean_phone;
                    } else {
                        $wp_phone = '90' . $clean_phone;
                    }

                    $cities_terms = wp_get_post_terms( $m_id, 'mechanic_city' );
                    $district_terms = wp_get_post_terms( $m_id, 'mechanic_district' );
                    $loc_text = '';
                    $city_name = '';
                    $is_in_district = false;
                    if ( $district_terms && ! is_wp_error( $district_terms ) ) {
                        $loc_text .= $district_terms[0]->name . ', ';
                        if ( ! empty( $selected_district ) ) {
                            if ( mb_strtolower( $district_terms[0]->name, 'UTF-8' ) === mb_strtolower( $selected_district, 'UTF-8' ) || $district_terms[0]->slug === sanitize_title( $selected_district ) ) {
                                $is_in_district = true;
                            }
                        }
                    }
                    if ( $cities_terms && ! is_wp_error( $cities_terms ) ) {
                        $loc_text .= $cities_terms[0]->name;
                        $city_name = $cities_terms[0]->name;
                    }

                    // Koordinatları al
                    $lat = get_post_meta( $m_id, '_mechanic_latitude', true );
                    $lng = get_post_meta( $m_id, '_mechanic_longitude', true );

                    if ( empty( $lat ) || empty( $lng ) || ! is_numeric( $lat ) ) {
                        if ( ! empty( $city_name ) && function_exists( 'ototamir_get_city_geo' ) ) {
                            $c_geo = ototamir_get_city_geo( $city_name );
                            if ( $c_geo ) {
                                $lat = $c_geo['lat'];
                                $lng = $c_geo['lng'];
                            }
                        }
                    }

                    $road_assist = get_post_meta( $m_id, '_mechanic_road_assist', true );
                    $sunday = get_post_meta( $m_id, '_mechanic_sunday', true );
                    $avatar_img = get_the_post_thumbnail_url( $m_id, 'thumbnail' ) ?: get_post_meta( $m_id, '_custom_mechanic_image', true );

                    // JS için veriyi biriktir
                    if ( ! empty( $lat ) && ! empty( $lng ) ) {
                        $all_tows_data[] = array(
                            'id'          => $m_id,
                            'title'       => get_the_title(),
                            'lat'         => floatval( $lat ),
                            'lng'         => floatval( $lng ),
                            'phone'       => $clean_phone,
                            'wp_phone'    => $wp_phone,
                            'in_district' => $is_in_district,
                            'loc'         => $loc_text ?: 'Türkiye Geneli',
                            'url'         => get_permalink()
                        );
                    }
                ?>
                    <div class="tow-card <?php echo $is_in_district ? 'is-in-district' : ''; ?>" 
                         data-id="<?php echo esc_attr( $m_id ); ?>"
                         data-lat="<?php echo esc_attr( $lat ); ?>"
                         data-lng="<?php echo esc_attr( $lng ); ?>"
                         data-name="<?php the_title_attribute(); ?>"
                         data-phone="<?php echo esc_attr( $clean_phone ); ?>"
                         data-wp="<?php echo esc_attr( $wp_phone ); ?>"
                         data-loc="<?php echo esc_attr( $loc_text ); ?>">

                        <div>
                            <!-- MESAFE ROZETİ (GPS İLE GÖRÜNÜR) -->
                            <div class="card-distance-badge" id="badge-dist-<?php echo esc_attr( $m_id ); ?>">
                                <i class="fa-solid fa-location-dot"></i> <span class="dist-val">-- km</span>
                            </div>

                            <div class="tow-card-header">
                                <div class="tow-avatar">
                                    <?php if ( $avatar_img ) : ?>
                                        <img src="<?php echo esc_url( $avatar_img ); ?>" alt="<?php the_title_attribute(); ?>">
                                    <?php else : ?>
                                        <i class="fa-solid fa-truck-pickup"></i>
                                    <?php endif; ?>
                                </div>

                                <div class="tow-name-wrap">
                                    <h3 title="<?php the_title_attribute(); ?>"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                    <div class="tow-loc">
                                        <i class="fa-solid fa-location-dot" style="color:#ef4444;"></i>
                                        <span><?php echo esc_html( $loc_text ?: 'Türkiye Geneli' ); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="tow-features">
                                <span class="tow-pill red"><i class="fa-solid fa-clock"></i> 7/24 Acil Yol Yardım</span>
                                <?php if ( $is_in_district ) : ?>
                                    <span class="tow-pill in-district" style="background:#dcfce7; color:#15803d; border-color:#86efac; font-weight:700;"><i class="fa-solid fa-map-pin"></i> Bulunduğunuz İlçede</span>
                                <?php endif; ?>
                                <?php if ( in_array( $sunday, array('Evet','yes','1') ) ) : ?>
                                    <span class="tow-pill green"><i class="fa-solid fa-calendar-check"></i> Pazar Açık</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="tow-actions">
                            <?php if ( ! empty( $phone ) ) : ?>
                                <a href="tel:<?php echo esc_attr( $clean_phone ); ?>" class="tow-btn-call">
                                    <i class="fa-solid fa-phone-volume"></i>
                                    <span>Hemen Ara</span>
                                </a>
                                
                                <a href="https://api.whatsapp.com/send?phone=<?php echo esc_attr( $wp_phone ); ?>&text=<?php echo urlencode('Merhaba, OtoTamirciBul üzerinden ulaşıyorum. Aracım yolda kaldı, acil çekici ihtiyacım var. Fiyat ve varış süresi alabilir miyim?'); ?>" 
                                   target="_blank" 
                                   rel="noopener" 
                                   class="tow-btn-wp js-tow-wp" 
                                   data-phone="<?php echo esc_attr( $wp_phone ); ?>" 
                                   data-mechanic="<?php the_title_attribute(); ?>">
                                    <i class="fa-brands fa-whatsapp" style="font-size:16px;"></i>
                                    <span>Konum At</span>
                                </a>
                            <?php else : ?>
                                <a href="<?php the_permalink(); ?>" class="tow-btn-call" style="grid-column: span 2;">
                                    <span>Profili & İletişimi Gör</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>

            <!-- SAYFALAMA -->
            <div class="tow-pagination">
                <?php
                echo paginate_links( array(
                    'total'     => $tow_query->max_num_pages,
                    'current'   => $paged,
                    'prev_text' => '<i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Önceki',
                    'next_text' => 'Sonraki <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>',
                ) );
                ?>
            </div>

        <?php else : ?>
            <div style="background:white; border-radius:16px; padding:40px; text-align:center; border:1px solid #e2e8f0; margin-bottom:40px;">
                <i class="fa-solid fa-triangle-exclamation" style="font-size:36px; color:#f59e0b; margin-bottom:12px;"></i>
                <h3 style="margin:0 0 8px; color:#0f172a;">Bu Şehirde Henüz Kayıtlı Çekici Bulunmuyor</h3>
                <p style="color:#64748b; margin:0 0 16px;">Diğer illerdeki 7/24 yol yardım ekiplerini görüntülemek için şehir filtresini sıfırlayabilirsiniz.</p>
                <a href="<?php echo esc_url( get_permalink() ); ?>" class="tow-city-submit" style="display:inline-block; text-decoration:none;">Tüm Türkiye Çekicilerini Gör</a>
            </div>
        <?php endif; ?>

        <!-- YOLDA GÜVENLİK REHBERİ -->
        <div class="tow-safety-card">
            <h3><i class="fa-solid fa-shield-halved"></i> Yolda Kaldığınızda Hayat Kurtaran 3 Güvenlik Kuralı</h3>
            <div class="tow-safety-grid">
                <div class="tow-safety-item">
                    <h4>1. Dörtlüleri Açın & Emniyet Şeridine Geçin</h4>
                    <p>Mümkünse aracı sağ emniyet şeridine veya cebe yanaştırın. Dörtlü ikaz lambalarını derhal yakarak diğer araçların sizi fark etmesini sağlayın.</p>
                </div>
                <div class="tow-safety-item">
                    <h4>2. Reflektörü Uygun Mesafeye Koyun</h4>
                    <p>Şehir içinde aracın 30 metre, şehirlerarası yollarda ve otoyollarda en az 100-150 metre gerisine reflektör yerleştirin. Viraj ve tepe üstlerine dikkat edin.</p>
                </div>
                <div class="tow-safety-item">
                    <h4>3. Araç İçinde Beklemeyin (Bariyer Arkası)</h4>
                    <p>Özellikle otoyollarda arkadan çarpma riskine karşı araç içinde oturmayın. Yolcularla birlikte çelik bariyerlerin arkasına geçerek çekiciyi bekleyin.</p>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- JAVASCRIPT: GEOLOCATION, TERS JEOKODLAMA (İL/İLÇE), AJAX FİRMA GETİRME, CANLI HARİTA VE ROTA -->
<script>
let towsDataset = <?php echo ! empty( $all_tows_data ) ? json_encode( $all_tows_data ) : '[]'; ?>;
const towAjaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';

let leafletMap = null;
let userMarker = null;
let routeLine = null;
let towMarkers = [];

// Haversine formülü ile iki koordinat arası kilometre mesafesi
function getDistanceKm(lat1, lon1, lat2, lon2) {
    const R = 6371; // Dünya yarıçapı km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

// İlçe Select Kutusunu Doldurma Yardımcısı
function populateDistricts(cityName, selectedDistrict) {
    const districtSelect = document.getElementById('towSelectDistrict');
    if (!districtSelect) return;

    districtSelect.innerHTML = '<option value="">Tüm İlçeler</option>';
    if (!cityName || typeof turkeyLocations === 'undefined') return;

    let list = turkeyLocations[cityName];
    if (!list) {
        for (let k in turkeyLocations) {
            if (k.toLowerCase() === cityName.toLowerCase()) {
                list = turkeyLocations[k];
                break;
            }
        }
    }

    if (list && list.length > 0) {
        list.forEach(function(d) {
            const opt = document.createElement('option');
            opt.value = d;
            opt.textContent = d;
            if (selectedDistrict && selectedDistrict.toLowerCase() === d.toLowerCase()) {
                opt.selected = true;
            }
            districtSelect.appendChild(opt);
        });
    }
}

// GPS Koordinatından İl ve İlçe Tespiti (Ters Jeokodlama)
async function reverseGeocode(lat, lng) {
    let city = '';
    let district = '';

    // 1. Öncelik: OpenStreetMap Nominatim
    try {
        const controller = new AbortController();
        const tId = setTimeout(() => controller.abort(), 3500);
        const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=14&addressdetails=1`, {
            signal: controller.signal,
            headers: { 'Accept-Language': 'tr,en' }
        });
        clearTimeout(tId);
        if (res.ok) {
            const data = await res.json();
            const addr = data.address || {};
            city = addr.province || addr.state || addr.city || '';
            district = addr.town || addr.county || addr.suburb || addr.city_district || addr.district || '';
        }
    } catch(e) {
        console.warn('Nominatim reverse geocode:', e);
    }

    // 2. Yedek: BigDataCloud (Ücretsiz, limitsiz)
    if (!city || !district) {
        try {
            const controller = new AbortController();
            const tId = setTimeout(() => controller.abort(), 3000);
            const res = await fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lng}&localityLanguage=tr`, {
                signal: controller.signal
            });
            clearTimeout(tId);
            if (res.ok) {
                const data = await res.json();
                if (!city) city = data.principalSubdivision || data.city || '';
                if (!district) district = data.locality || data.district || '';
            }
        } catch(e) {
            console.warn('BigDataCloud reverse geocode:', e);
        }
    }

    // İsim temizleme (örn: 'İstanbul İli' -> 'İstanbul', 'Kadıköy Belediyesi' -> 'Kadıköy')
    if (city) {
        city = city.replace(/\s+(İli|Province|State|Valiliği)$/ui, '').trim();
    }
    if (district) {
        district = district.replace(/\s+(İlçesi|Belediyesi|District)$/ui, '').trim();
    }

    return { city, district };
}

// Harita, Pinler, Rota ve VIP Kart Render Fonksiyonu
function renderMapAndMarkers(userLat, userLng, tows, locDisplay) {
    const mapSection = document.getElementById('towMapSection');
    mapSection.style.display = 'block';

    if (!leafletMap) {
        leafletMap = L.map('towLiveMap').setView([userLat, userLng], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap Katkıda Bulunanlar'
        }).addTo(leafletMap);
    } else {
        leafletMap.setView([userLat, userLng], 12);
        leafletMap.invalidateSize();
    }

    // Kullanıcı ikonu (Mavi nabız gibi daireli)
    const userIcon = L.divIcon({
        className: 'user-map-pin',
        html: '<div style="background:#2563eb; width:22px; height:22px; border-radius:50%; border:3px solid #ffffff; box-shadow:0 0 14px rgba(37,99,235,0.7); animation: pulse 1.5s infinite;"></div>',
        iconSize: [22, 22],
        iconAnchor: [11, 11]
    });

    if (userMarker) {
        leafletMap.removeLayer(userMarker);
    }
    userMarker = L.marker([userLat, userLng], { icon: userIcon })
        .addTo(leafletMap)
        .bindPopup(`<b>📍 Siz Buradasınız</b><br>${locDisplay || 'Yolda Kalan Araç'}`)
        .openPopup();

    // Eski çekici pinlerini temizle
    towMarkers.forEach(m => leafletMap.removeLayer(m));
    towMarkers = [];

    // Çekici ikonları
    const towIcon = L.divIcon({
        className: 'tow-map-pin',
        html: '<div style="background:#dc2626; color:#fff; width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:2px solid #fff; box-shadow:0 4px 10px rgba(0,0,0,0.3);"><i class="fa-solid fa-truck-pickup" style="font-size:14px;"></i></div>',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    });

    const nearestIcon = L.divIcon({
        className: 'tow-map-pin-nearest',
        html: '<div style="background:#dc2626; color:#fff; width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:3px solid #facc15; box-shadow:0 0 16px rgba(220,38,38,0.8);"><i class="fa-solid fa-truck-pickup" style="font-size:16px;"></i></div>',
        iconSize: [36, 36],
        iconAnchor: [18, 18]
    });

    // Mesafeleri kesin olarak kullanıcı GPS'ine göre yeniden hesapla ve artan sırada sırala
    tows.forEach(function(tow) {
        if (tow.lat && tow.lng && userLat && userLng) {
            const dist = getDistanceKm(userLat, userLng, tow.lat, tow.lng);
            tow.distance = dist;
            tow.distance_formatted = dist < 1 ? Math.round(dist * 1000) + ' m' : dist.toFixed(1) + ' km';
        }
    });

    tows.sort(function(a, b) {
        const da = (typeof a.distance === 'number') ? a.distance : 99999;
        const db = (typeof b.distance === 'number') ? b.distance : 99999;
        return da - db;
    });

    let nearestTow = null;
    const groupBounds = [userMarker];

    tows.forEach(function(tow) {
        if (tow.lat && tow.lng) {
            const isFirst = (!nearestTow);
            if (isFirst) nearestTow = tow;

            const iconToUse = isFirst ? nearestIcon : towIcon;
            const m = L.marker([tow.lat, tow.lng], { icon: iconToUse }).addTo(leafletMap);
            towMarkers.push(m);
            groupBounds.push(m);

            const distText = tow.distance_formatted ? ` (${tow.distance_formatted})` : (tow.distance ? ` (${tow.distance.toFixed(1)} km)` : '');
            const inDistBadge = tow.in_district ? '<span style="color:#16a34a; font-weight:700; font-size:11px;">[Bulunduğunuz İlçede]</span><br>' : '';
            m.bindPopup(`<b>${tow.title}</b><br>${inDistBadge}${tow.loc}${distText}<br><a href="tel:${tow.phone}" style="display:inline-block; margin-top:4px; color:#dc2626; font-weight:700;">📞 Hemen Ara</a>`);
        }
    });

    // En Yakın Çekici VIP Kartını Doldur & Rota Çiz
    if (nearestTow && nearestTow.lat && nearestTow.lng) {
        document.getElementById('nearestName').textContent = nearestTow.title;
        const distStr = nearestTow.distance_formatted || (nearestTow.distance ? `${nearestTow.distance.toFixed(1)} km` : '-- km');
        document.getElementById('nearestDist').innerHTML = `📍 Mesafe: <strong>${distStr}</strong>`;
        
        const distVal = (typeof nearestTow.distance === 'number') ? nearestTow.distance : parseFloat(distStr);
        const etaMinutes = Math.max(8, Math.round(((distVal || 5) / 40) * 60));
        document.getElementById('nearestEta').innerHTML = `⏱️ Tahmini Varış: <strong>~${etaMinutes} dk</strong>`;
        document.getElementById('nearestLoc').innerHTML = `📌 Konum: <strong>${nearestTow.loc}</strong>`;

        document.getElementById('nearestCallBtn').href = `tel:${nearestTow.phone}`;

        const userLatFixed = userLat.toFixed(6);
        const userLngFixed = userLng.toFixed(6);
        const mapLink = `https://maps.google.com/?q=${userLatFixed},${userLngFixed}`;
        const vipWpMsg = `Merhaba ${nearestTow.title}, OtoTamirciBul üzerinden ulaşıyorum. Aracım yolda kaldı, acil çekici ihtiyacım var. Bulunduğum Canlı Konum: ${mapLink} Lütfen çekici fiyatı ve tahmini varış süresi verebilir misiniz?`;
        document.getElementById('nearestWpBtn').href = `https://api.whatsapp.com/send?phone=${nearestTow.wp_phone}&text=${encodeURIComponent(vipWpMsg)}`;

        // Google Haritalar Canlı Navigasyon Linki
        const routeUrl = `https://www.google.com/maps/dir/?api=1&origin=${userLatFixed},${userLngFixed}&destination=${nearestTow.lat},${nearestTow.lng}&travelmode=driving`;
        document.getElementById('nearestRouteBtn').href = routeUrl;

        // Harita üzerinde canlı rota polyline çiz
        if (routeLine) {
            leafletMap.removeLayer(routeLine);
        }
        routeLine = L.polyline([
            [userLat, userLng],
            [nearestTow.lat, nearestTow.lng]
        ], {
            color: '#dc2626',
            weight: 4,
            dashArray: '8, 8',
            opacity: 0.85
        }).addTo(leafletMap);

        // Haritayı hem kullanıcıyı hem çekicileri kapsayacak şekilde odakla
        const featureGroup = new L.featureGroup(groupBounds);
        leafletMap.fitBounds(featureGroup.getBounds().pad(0.25));
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const btnGetLocation = document.getElementById('btnGetLocation');
    const geoStatus      = document.getElementById('geoStatus');
    const geoCoordText   = document.getElementById('geoCoordText');
    const mapSection     = document.getElementById('towMapSection');
    const citySelect     = document.getElementById('towSelectCity');
    const districtSelect = document.getElementById('towSelectDistrict');

    // 1. Şehir Seçildiğinde İlçeleri Otomatik Doldur
    if (citySelect) {
        citySelect.addEventListener('change', function() {
            const selOpt = this.options[this.selectedIndex];
            const cName = selOpt ? (selOpt.getAttribute('data-name') || selOpt.text.trim()) : '';
            populateDistricts(cName, '');
        });

        // Sayfa yüklendiğinde seçili şehir varsa ilçeleri doldur
        const curOpt = citySelect.options[citySelect.selectedIndex];
        if (curOpt && citySelect.value) {
            const cName = curOpt.getAttribute('data-name') || curOpt.text.trim();
            const preSel = districtSelect ? districtSelect.getAttribute('data-selected') : '';
            populateDistricts(cName, preSel);
        }
    }

    // 2. GPS Butonuna Tıklanması
    btnGetLocation.addEventListener('click', function() {
        if (!navigator.geolocation) {
            geoStatus.className = 'tow-geo-status error';
            geoStatus.textContent = 'Tarayıcınız konum servisini desteklemiyor. Lütfen yukarıdan şehir ve ilçe seçin.';
            return;
        }

        btnGetLocation.disabled = true;
        btnGetLocation.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Konum Alınıyor...</span>';
        geoStatus.className = 'tow-geo-status';
        geoStatus.innerHTML = '<i class="fa-solid fa-satellite fa-beat"></i> GPS uydusuna bağlanılıyor...';

        navigator.geolocation.getCurrentPosition(
            async function(position) {
                const userLat = position.coords.latitude;
                const userLng = position.coords.longitude;
                const userLatFixed = userLat.toFixed(6);
                const userLngFixed = userLng.toFixed(6);
                const mapLink = `https://maps.google.com/?q=${userLatFixed},${userLngFixed}`;

                btnGetLocation.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>İl & İlçe Taranıyor...</span>';
                geoStatus.className = 'tow-geo-status';
                geoStatus.innerHTML = '<i class="fa-solid fa-location-crosshairs fa-beat"></i> Konum koordinatları alındı, bulunduğunuz il ve ilçedeki en yakın çekiciler sorgulanıyor...';

                // Ters jeokodlama ile il ve ilçe tespiti
                let detCity = '';
                let detDistrict = '';
                try {
                    const detected = await reverseGeocode(userLat, userLng);
                    detCity = detected.city || '';
                    detDistrict = detected.district || '';
                } catch(e) {
                    console.warn('Reverse geocode error:', e);
                }

                // AJAX ile bu il ve ilçedeki firmaları mesafeye göre getir
                try {
                    const formData = new FormData();
                    formData.append('action', 'ototamir_get_nearby_tows');
                    formData.append('nonce', '<?php echo esc_js( wp_create_nonce( 'ototamir_tow_locator_nonce' ) ); ?>');
                    formData.append('lat', userLat);
                    formData.append('lng', userLng);
                    formData.append('city', detCity);
                    formData.append('district', detDistrict);

                    const response = await fetch(towAjaxUrl, {
                        method: 'POST',
                        body: formData
                    });
                    const resJson = await response.json();

                    if (resJson.success && resJson.data) {
                        const tows = resJson.data.tows || [];
                        const finalCity = resJson.data.city || detCity;
                        const finalDistrict = resJson.data.district || detDistrict;
                        const locDisplay = (finalDistrict ? finalDistrict + ', ' : '') + (finalCity || 'Bölgeniz');

                        btnGetLocation.disabled = false;
                        btnGetLocation.innerHTML = '<i class="fa-solid fa-circle-check"></i> <span>Konum Alındı (Yenile)</span>';
                        geoStatus.className = 'tow-geo-status success';
                        geoStatus.innerHTML = `✅ Konumunuz Tespit Edildi: <strong>${locDisplay}</strong> (${resJson.data.count} çekici listelendi ve haritada rotalandı).`;
                        geoCoordText.textContent = `📍 Koordinatlarınız: ${userLatFixed}, ${userLngFixed} (${locDisplay})`;

                        // Filtre Çubuğunu Güncelle
                        const filterTitle = document.getElementById('filterBarTitle');
                        if (filterTitle) {
                            filterTitle.innerHTML = `<i class="fa-solid fa-location-dot" style="color:#ef4444;"></i> <span>${locDisplay} Bölgesindeki En Yakın Çekiciler</span>`;
                        }

                        // Select kutularını tespit edilen konuma ayarla
                        if (citySelect && resJson.data.city_slug) {
                            for (let i = 0; i < citySelect.options.length; i++) {
                                const opt = citySelect.options[i];
                                if (opt.value === resJson.data.city_slug || opt.text.trim().toLowerCase() === finalCity.toLowerCase()) {
                                    citySelect.selectedIndex = i;
                                    break;
                                }
                            }
                            populateDistricts(finalCity, finalDistrict);
                        }

                        // Çekici Kartları Izgarasını Güncelle
                        const cardsGrid = document.getElementById('towCardsGrid');
                        if (cardsGrid && resJson.data.html) {
                            cardsGrid.innerHTML = resJson.data.html;
                        }

                        // Sayfalamayı gizle (Canlı konum sonuçları)
                        const pagEl = document.querySelector('.tow-pagination');
                        if (pagEl) pagEl.style.display = 'none';

                        // Harita & Pinler & Rota Çizimi
                        renderMapAndMarkers(userLat, userLng, tows, locDisplay);

                        // Haritaya yumuşak kaydır
                        mapSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        return;
                    }
                } catch(ajaxErr) {
                    console.warn('AJAX çekici getirme hatası:', ajaxErr);
                }

                // Fallback: AJAX başarısız olsa bile mevcut sayfadaki çekicilerle haritayı çiz
                btnGetLocation.disabled = false;
                btnGetLocation.innerHTML = '<i class="fa-solid fa-circle-check"></i> <span>Konum Alındı</span>';
                geoStatus.className = 'tow-geo-status success';
                geoStatus.innerHTML = `✅ Konumunuz alındı (${userLatFixed}, ${userLngFixed}). En yakın çekici haritada işaretlendi!`;
                geoCoordText.textContent = `📍 Koordinatlarınız: ${userLatFixed}, ${userLngFixed}`;

                towsDataset.forEach(function(tow) {
                    if (tow.lat && tow.lng) {
                        const dist = getDistanceKm(userLat, userLng, tow.lat, tow.lng);
                        tow.distance = dist;
                        tow.distance_formatted = dist.toFixed(1) + ' km';

                        const badge = document.getElementById(`badge-dist-${tow.id}`);
                        if (badge) {
                            badge.style.display = 'inline-flex';
                            badge.querySelector('.dist-val').textContent = `${tow.distance_formatted} mesafede`;
                        }
                    }
                });

                towsDataset.sort((a, b) => (a.distance || 9999) - (b.distance || 9999));
                renderMapAndMarkers(userLat, userLng, towsDataset, 'Konumunuz');
                mapSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            },
            function(error) {
                btnGetLocation.disabled = false;
                btnGetLocation.innerHTML = '<i class="fa-solid fa-location-arrow"></i> <span>Tekrar Dene</span>';
                geoStatus.className = 'tow-geo-status error';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        geoStatus.textContent = 'Konum izni verilmedi. Lütfen tarayıcı ayarlarından konuma izin verin veya yukarıdan şehir/ilçe seçin.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        geoStatus.textContent = 'Konum bilgisi alınamadı. Lütfen cihaz GPS’inizi açın.';
                        break;
                    case error.TIMEOUT:
                        geoStatus.textContent = 'Konum alma zaman aşımına uğradı. Lütfen tekrar deneyin.';
                        break;
                    default:
                        geoStatus.textContent = 'Bilinmeyen bir konum hatası oluştu.';
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    });
});
</script>

<?php get_footer(); ?>
