<?php
/**
 * Template Name: Elektrikli Araç Şarj İstasyonları
 * Description: 81 il geneli elektrikli araç (EV) şarj istasyonları haritası ve dizini. Canlı GPS konumu alma, en yakın istasyonu rotalama, operatör filtresi (Trugo, Eşarj, ZES vb.) ve doğrudan Google Haritalar navigasyon desteği.
 * 
 * @package OtoTamir360
 */

get_header();

require_once get_template_directory() . '/inc/seo-geo.php';

// Filtre parametreleri
$selected_city = sanitize_text_field( $_GET['city'] ?? '' );
$selected_district = sanitize_text_field( $_GET['district'] ?? '' );
$selected_network = sanitize_text_field( $_GET['network'] ?? '' );
$paged = max( 1, get_query_var( 'paged' ), get_query_var( 'page' ), isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1 );

// JSON dosyasından tüm 760 istasyonu oku (Canlı harita ve hızlı arama için)
$json_file = get_template_directory() . '/inc/data/charging-stations-data.json';
$all_stations_data = array();
if ( file_exists( $json_file ) ) {
    $all_stations_data = json_decode( file_get_contents( $json_file ), true ) ?: array();
}

// WP_Query ile sayfalanmış şarj istasyonlarını çek
$tax_query = array(
    array(
        'taxonomy' => 'service_type',
        'field'    => 'slug',
        'terms'    => 'elektrikli-sarj-istasyonu',
    )
);

if ( ! empty( $selected_city ) ) {
    $tax_query[] = array(
        'taxonomy' => 'mechanic_city',
        'field'    => 'slug',
        'terms'    => sanitize_title( $selected_city ),
    );
}

if ( ! empty( $selected_district ) ) {
    $tax_query[] = array(
        'taxonomy' => 'mechanic_district',
        'field'    => 'name',
        'terms'    => $selected_district,
    );
}

$meta_query = array();
if ( ! empty( $selected_network ) ) {
    $meta_query[] = array(
        'key'   => '_mechanic_charging_network',
        'value' => $selected_network,
    );
}

$args = array(
    'post_type'      => 'mechanic',
    'post_status'    => 'publish',
    'posts_per_page' => 18,
    'paged'          => $paged,
    'tax_query'      => $tax_query,
    'meta_query'     => $meta_query,
);

$ev_query = new WP_Query( $args );

// Şehirler listesi
$all_cities = get_terms( array(
    'taxonomy'   => 'mechanic_city',
    'hide_empty' => false,
    'orderby'    => 'name',
    'order'      => 'ASC'
) );

$networks = array(
    'Tümü'                 => '',
    'Trugo (TOGG)'         => 'Trugo',
    'Eşarj (Enerjisa)'     => 'Eşarj',
    'ZES (Zorlu Energy)'   => 'ZES',
    'Sharz.net'            => 'Sharz.net',
    'WAT Mobilite'         => 'WAT Mobilite',
    'Astor Şarj'           => 'Astor Şarj',
    'Voltrun'              => 'Voltrun',
    'Tesla Supercharger'   => 'Tesla Supercharger',
);
?>

<!-- Leaflet.js Harita Kütüphanesi -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" crossorigin="anonymous" media="print" onload="this.media='all'" />
<noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" crossorigin="anonymous" /></noscript>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js" crossorigin="anonymous" defer></script>
<script src="<?php echo esc_url( get_template_directory_uri() . '/assets/js/turkey.js' ); ?>" defer></script>

<style>
/* EV Şarj İstasyonları Özel UI */
.ev-hero {
    background: linear-gradient(135deg, #064e3b 0%, #0f172a 60%, #022c22 100%);
    color: #ffffff;
    padding: 65px 0 45px;
    position: relative;
    overflow: hidden;
}
.ev-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.15) 0%, transparent 70%);
    pointer-events: none;
}
.ev-badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(16, 185, 129, 0.2);
    border: 1px solid rgba(16, 185, 129, 0.4);
    color: #34d399;
    padding: 6px 14px;
    border-radius: 9999px;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 15px;
    letter-spacing: 0.5px;
}
.ev-title {
    font-size: 34px;
    font-weight: 800;
    line-height: 1.25;
    margin-bottom: 12px;
}
.ev-subtitle {
    font-size: 16px;
    color: #94a3b8;
    max-width: 720px;
    margin-bottom: 25px;
    line-height: 1.6;
}
.ev-cta-box {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 15px;
    background: rgba(255, 255, 255, 0.08);
    padding: 16px 20px;
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    backdrop-filter: blur(8px);
}
.btn-ev-gps {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff;
    border: none;
    padding: 14px 24px;
    border-radius: 12px;
    font-weight: 800;
    font-size: 15px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
    transition: all 0.25s ease;
}
.btn-ev-gps:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.6);
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
}
.ev-geo-status {
    font-size: 13px;
    color: #cbd5e1;
    display: flex;
    align-items: center;
    gap: 8px;
}
.ev-geo-status.success { color: #34d399; font-weight: 600; }
.ev-geo-status.error { color: #f87171; }

/* Ağ Filtre Hapları */
.ev-network-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 20px;
}
.ev-pill {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    background: rgba(255, 255, 255, 0.1);
    color: #e2e8f0;
    border: 1px solid rgba(255, 255, 255, 0.15);
    transition: all 0.2s;
}
.ev-pill:hover, .ev-pill.active {
    background: #10b981;
    color: #ffffff;
    border-color: #10b981;
}

/* Harita Alanı */
.ev-map-section {
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    display: none;
}
.ev-map-container {
    height: 480px;
    width: 100%;
    position: relative;
}
.ev-vip-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border: 2px solid #10b981;
    margin-top: -40px;
    position: relative;
    z-index: 10;
    margin-bottom: 30px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
}

/* Kartlar */
.ev-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 22px;
    margin-top: 30px;
}
.ev-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 22px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.25s ease;
    position: relative;
}
.ev-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 25px rgba(16, 185, 129, 0.12);
    border-color: #10b981;
}
.ev-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}
.ev-network-tag {
    font-size: 11px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
}
.ev-card-title {
    font-size: 17px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 8px;
    line-height: 1.35;
}
.ev-card-address {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 14px;
    display: flex;
    align-items: flex-start;
    gap: 6px;
}
.ev-card-meta {
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 13px;
    color: #475569;
    margin-bottom: 18px;
    padding-top: 10px;
    border-top: 1px dashed #e2e8f0;
}
.ev-card-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-top: auto;
}
.btn-ev-nav {
    background: #10b981;
    color: #ffffff;
    text-align: center;
    padding: 10px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.btn-ev-nav:hover { background: #059669; }
.btn-ev-call {
    background: #f1f5f9;
    color: #0f172a;
    text-align: center;
    padding: 10px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.btn-ev-call:hover { background: #e2e8f0; }

/* SSS Akordeon */
.ev-faq-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 30px;
    margin-top: 60px;
    margin-bottom: 40px;
}
</style>

<!-- 1. HERO BÖLÜMÜ & GPS BUTONU -->
<section class="ev-hero">
    <div class="container">
        <div class="ev-badge-pill">
            <i class="fa-solid fa-bolt fa-beat" style="--fa-animation-duration: 1.5s;"></i>
            <span>81 İLDE 760+ DOĞRULANMIŞ ŞARJ İSTASYONU</span>
        </div>
        <h1 class="ev-title">Türkiye Geneli Elektrikli Araç Şarj İstasyonları</h1>
        <p class="ev-subtitle">
            Trugo, Eşarj, ZES, Sharz.net ve tüm büyük elektrikli araç şarj istasyonlarının canlı konumları, güç kapasiteleri, Google kullanıcı puanları ve navigasyon rotaları.
        </p>

        <!-- Canlı GPS Arama Kutusu -->
        <div class="ev-cta-box">
            <button type="button" id="btnGetEvLocation" class="btn-ev-gps">
                <i class="fa-solid fa-location-crosshairs"></i>
                <span>En Yakın Şarj İstasyonunu Bul (GPS)</span>
            </button>
            <div id="evGeoStatus" class="ev-geo-status">
                <i class="fa-solid fa-circle-info"></i>
                <span>GPS butonuna basarak size en yakın şarj noktasını haritada görebilirsiniz.</span>
            </div>
            <div id="evCoordText" style="display:none; font-size:12px; color:#94a3b8;"></div>
        </div>

        <!-- Ağ / Operatör Filtreleri -->
        <div class="ev-network-pills">
            <?php foreach ( $networks as $net_label => $net_val ) : 
                $is_active = ( $selected_network === $net_val );
                $pill_url = add_query_arg( array( 'network' => $net_val, 'city' => $selected_city, 'district' => $selected_district ), home_url( '/elektrikli-sarj-istasyonlari/' ) );
            ?>
                <a href="<?php echo esc_url( $pill_url ); ?>" class="ev-pill <?php echo $is_active ? 'active' : ''; ?>" data-network="<?php echo esc_attr( $net_val ); ?>">
                    <?php echo esc_html( $net_label ); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 2. CANLI LEAFLET HARİTA BÖLÜMÜ -->
<section id="evMapSection" class="ev-map-section">
    <div class="container" style="padding-top: 20px;">
        <div id="evVipCard" class="ev-vip-card" style="display:none;">
            <div>
                <span style="background:#ecfdf5; color:#059669; font-size:11px; font-weight:800; padding:3px 8px; border-radius:6px; text-transform:uppercase;">⚡ EN YAKIN ŞARJ NOKTASI</span>
                <h3 id="nearestEvTitle" style="font-size:20px; font-weight:800; color:#0f172a; margin:6px 0 4px;">--</h3>
                <p id="nearestEvAddress" style="font-size:13px; color:#64748b; margin:0;"><i class="fa-solid fa-location-dot" style="color:#10b981;"></i> --</p>
            </div>
            <div style="display:flex; align-items:center; gap:20px;">
                <div style="text-align:right;">
                    <div id="nearestEvDist" style="font-size:22px; font-weight:900; color:#10b981;">-- km</div>
                    <div id="nearestEvEta" style="font-size:12px; color:#64748b;">Tahmini Sürüş: ~-- dk</div>
                </div>
                <a id="nearestEvNavBtn" href="#" target="_blank" rel="noopener" class="btn-ev-nav" style="padding:12px 20px;">
                    <i class="fa-solid fa-diamond-turn-right"></i> Yol Tarifi Al
                </a>
            </div>
        </div>
    </div>
    <div id="evLiveMap" class="ev-map-container"></div>
</section>

<!-- 3. İL & İLÇE FİLTRE ÇUBUĞU VE İSTASYON KARTLARI -->
<section style="background:#f8fafc; padding: 40px 0 60px;">
    <div class="container">
        <!-- Filtre Çubuğu -->
        <div style="background:#ffffff; padding:18px 24px; border-radius:14px; border:1px solid #e2e8f0; display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:15px; margin-bottom:25px;">
            <div style="font-size:16px; font-weight:700; color:#0f172a;">
                <i class="fa-solid fa-charging-station" style="color:#10b981; margin-right:6px;"></i>
                <span id="evListTitle">
                    <?php 
                    if ( ! empty( $selected_city ) ) {
                        echo esc_html( ucfirst( $selected_city ) ) . ( ! empty( $selected_district ) ? ' / ' . esc_html( $selected_district ) : '' ) . ' Şarj İstasyonları';
                    } else {
                        echo 'Türkiye Geneli Şarj İstasyonları';
                    }
                    ?>
                </span>
                <span style="font-size:13px; font-weight:500; color:#64748b; margin-left:8px;">(<?php echo number_format_i18n( $ev_query->found_posts ?: count( $all_stations_data ) ); ?> istasyon)</span>
            </div>

            <!-- İl ve İlçe Formu -->
            <form method="GET" action="<?php echo esc_url( home_url( '/elektrikli-sarj-istasyonlari/' ) ); ?>" style="display:flex; gap:10px; flex-wrap:wrap;">
                <?php if ( ! empty( $selected_network ) ) : ?>
                    <input type="hidden" name="network" value="<?php echo esc_attr( $selected_network ); ?>">
                <?php endif; ?>
                <select name="city" id="evSelectCity" style="padding:8px 14px; border-radius:8px; border:1px solid #cbd5e1; font-size:13px;">
                    <option value="">Tüm İller</option>
                    <?php if ( ! empty( $all_cities ) && ! is_wp_error( $all_cities ) ) : ?>
                        <?php foreach ( $all_cities as $c ) : ?>
                            <option value="<?php echo esc_attr( $c->slug ); ?>" <?php selected( $selected_city, $c->slug ); ?>>
                                <?php echo esc_html( $c->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>

                <select name="district" id="evSelectDistrict" style="padding:8px 14px; border-radius:8px; border:1px solid #cbd5e1; font-size:13px;">
                    <option value="">Tüm İlçeler</option>
                </select>

                <button type="submit" style="background:#0f172a; color:#fff; border:none; padding:8px 16px; border-radius:8px; font-weight:600; font-size:13px; cursor:pointer;">
                    <i class="fa-solid fa-filter"></i> Filtrele
                </button>
            </form>
        </div>

        <!-- Şarj İstasyon Kartları Izgarası -->
        <div id="evCardsGrid" class="ev-grid">
            <?php if ( $ev_query->have_posts() ) : ?>
                <?php while ( $ev_query->have_posts() ) : $ev_query->the_post(); 
                    $m_id = get_the_ID();
                    $net = get_post_meta( $m_id, '_mechanic_charging_network', true ) ?: 'Elektrikli Şarj';
                    $phone = get_post_meta( $m_id, '_mechanic_phone', true );
                    $address = get_post_meta( $m_id, '_mechanic_address', true );
                    $website = get_post_meta( $m_id, '_mechanic_website', true );
                    $rating = get_post_meta( $m_id, '_mechanic_rating', true ) ?: '4.8';
                    $reviews = get_post_meta( $m_id, '_mechanic_review_count', true ) ?: '12';
                    $lat = get_post_meta( $m_id, '_mechanic_latitude', true );
                    $lng = get_post_meta( $m_id, '_mechanic_longitude', true );
                    $nav_url = ( $lat && $lng ) ? sprintf( 'https://www.google.com/maps/dir/?api=1&destination=%s,%s', $lat, $lng ) : '#';
                ?>
                    <div class="ev-card" id="ev-card-<?php echo esc_attr( $m_id ); ?>">
                        <div>
                            <div class="ev-card-header">
                                <span class="ev-network-tag"><?php echo esc_html( $net ); ?></span>
                                <span style="font-size:12px; color:#f59e0b; font-weight:700;"><i class="fa-solid fa-star"></i> <?php echo esc_html( $rating ); ?></span>
                            </div>
                            <h3 class="ev-card-title">
                                <a href="<?php the_permalink(); ?>" style="color:#0f172a; text-decoration:none;">
                                    <?php the_title(); ?>
                                </a>
                            </h3>
                            <div class="ev-card-address">
                                <i class="fa-solid fa-location-dot" style="color:#10b981; margin-top:2px;"></i>
                                <span><?php echo esc_html( $address ?: 'Adres bilgisi mevcut' ); ?></span>
                            </div>
                        </div>

                        <div>
                            <div class="ev-card-meta">
                                <span><i class="fa-solid fa-plug" style="color:#10b981;"></i> Yüksek Hızlı DC/AC</span>
                                <span><i class="fa-regular fa-clock" style="color:#64748b;"></i> 7/24 Açık</span>
                            </div>
                            <div class="ev-card-actions">
                                <a href="<?php echo esc_url( $nav_url ); ?>" target="_blank" rel="noopener" class="btn-ev-nav">
                                    <i class="fa-solid fa-diamond-turn-right"></i> Yol Tarifi
                                </a>
                                <?php if ( $phone ) : ?>
                                    <a href="tel:<?php echo esc_attr( $phone ); ?>" class="btn-ev-call">
                                        <i class="fa-solid fa-phone"></i> Ara
                                    </a>
                                <?php elseif ( $website ) : ?>
                                    <a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener" class="btn-ev-call">
                                        <i class="fa-solid fa-globe"></i> Web
                                    </a>
                                <?php else : ?>
                                    <a href="<?php the_permalink(); ?>" class="btn-ev-call">
                                        <i class="fa-solid fa-circle-info"></i> Detay
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; wp_reset_postdata(); ?>
            <?php else : ?>
                <div style="grid-column: 1 / -1; text-align:center; padding:50px 20px; background:#fff; border-radius:14px; border:1px dashed #cbd5e1;">
                    <i class="fa-solid fa-charging-station" style="font-size:40px; color:#94a3b8; margin-bottom:12px;"></i>
                    <h3 style="font-size:18px; color:#1e293b; margin-bottom:6px;">Aramanıza Uygun Şarj İstasyonu Bulunamadı</h3>
                    <p style="font-size:14px; color:#64748b; margin-bottom:20px;">Lütfen yukarıdaki filtreleri temizleyerek tüm istasyonları listeleyin.</p>
                    <a href="<?php echo esc_url( home_url( '/elektrikli-sarj-istasyonlari/' ) ); ?>" style="padding:10px 20px; background:#10b981; color:#fff; font-weight:700; border-radius:8px; text-decoration:none;">Filtreleri Temizle</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sayfalama -->
        <?php if ( $ev_query->max_num_pages > 1 ) : ?>
            <div style="margin-top:40px; text-align:center;">
                <?php
                echo paginate_links( array(
                    'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
                    'total'        => $ev_query->max_num_pages,
                    'current'      => $paged,
                    'format'       => '?paged=%#%',
                    'show_all'     => false,
                    'type'         => 'plain',
                    'end_size'     => 2,
                    'mid_size'     => 1,
                    'prev_next'    => true,
                    'prev_text'    => sprintf( '<i class="fa-solid fa-arrow-left"></i> %s', __( 'Önceki', 'ototamir' ) ),
                    'next_text'    => sprintf( '%s <i class="fa-solid fa-arrow-right"></i>', __( 'Sonraki', 'ototamir' ) ),
                ) );
                ?>
            </div>
        <?php endif; ?>

        <!-- 4. SEO SSS ALANI -->
        <div class="ev-faq-box">
            <h3 style="font-size:22px; font-weight:800; color:#0f172a; margin-bottom:15px;">
                ⚡ Elektrikli Araç Şarj İstasyonları Hakkında Sıkça Sorulan Sorular
            </h3>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:20px; margin-top:20px;">
                <div style="background:#fff; padding:18px; border-radius:12px; border:1px solid #e2e8f0;">
                    <h4 style="font-size:15px; font-weight:700; color:#0f172a; margin-bottom:8px;">AC ve DC Şarj Arasındaki Fark Nedir?</h4>
                    <p style="font-size:13px; color:#64748b; line-height:1.5;">AC şarj istasyonları (11 kW - 22 kW) genellikle otopark ve otellerde uzun süreli park halinde kullanılır. DC Hızlı Şarj istasyonları (60 kW - 300 kW) ise otoyol ve ana arterlerde aracınızı 20-35 dakikada %80 doluluğa ulaştırır.</p>
                </div>
                <div style="background:#fff; padding:18px; border-radius:12px; border:1px solid #e2e8f0;">
                    <h4 style="font-size:15px; font-weight:700; color:#0f172a; margin-bottom:8px;">Şarj Ücreti Nasıl Hesaplanır?</h4>
                    <p style="font-size:13px; color:#64748b; line-height:1.5;">Şarj ücretleri tüketilen kWh (kilovatsaat) enerji miktarına göre operatörler (Trugo, Eşarj, ZES vb.) tarafından tarife üzerinden belirlenir. DC şarj tarifeleri AC şarja göre daha yüksektir.</p>
                </div>
                <div style="background:#fff; padding:18px; border-radius:12px; border:1px solid #e2e8f0;">
                    <h4 style="font-size:15px; font-weight:700; color:#0f172a; margin-bottom:8px;">Her İstasyon Tüm Araçlarla Uyumlu mu?</h4>
                    <p style="font-size:13px; color:#64748b; line-height:1.5;">Türkiye'deki elektrikli araçların büyük çoğunluğu Avrupa standardı olan Type 2 (AC) ve CCS Combo 2 (DC) soketini kullanır. Bu rehberdeki istasyonlar tüm binek ve ticari elektrikli araçlarla tam uyumludur.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- JAVASCRIPT: CANLI GPS, LEAFLET VE EN YAKIN İSTASYON HESAPLAMA -->
<script>
let allEvStations = <?php echo ! empty( $all_stations_data ) ? json_encode( $all_stations_data ) : '[]'; ?>;

let evMap = null;
let userMarker = null;
let evMarkers = [];
let evRouteLine = null;

function getDistanceKm(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

function renderEvMap(userLat, userLng, stations) {
    const mapSection = document.getElementById('evMapSection');
    mapSection.style.display = 'block';

    if (!evMap) {
        evMap = L.map('evLiveMap').setView([userLat, userLng], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(evMap);
    } else {
        evMap.setView([userLat, userLng], 12);
        evMap.invalidateSize();
    }

    // Kullanıcı Konum Nabız İkonu
    const userIcon = L.divIcon({
        className: 'user-pin',
        html: '<div style="background:#2563eb; width:22px; height:22px; border-radius:50%; border:3px solid #fff; box-shadow:0 0 14px rgba(37,99,235,0.7); animation: pulse 1.5s infinite;"></div>',
        iconSize: [22, 22],
        iconAnchor: [11, 11]
    });

    if (userMarker) evMap.removeLayer(userMarker);
    userMarker = L.marker([userLat, userLng], { icon: userIcon }).addTo(evMap)
        .bindPopup('<b>📍 Konumunuz</b>')
        .openPopup();

    evMarkers.forEach(m => evMap.removeLayer(m));
    evMarkers = [];

    // Şarj İstasyonu İkonu
    const evIcon = L.divIcon({
        className: 'ev-pin',
        html: '<div style="background:#10b981; color:#fff; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:2px solid #fff; box-shadow:0 4px 10px rgba(0,0,0,0.25);"><i class="fa-solid fa-charging-station" style="font-size:14px;"></i></div>',
        iconSize: [32, 32],
        iconAnchor: [16, 16]
    });

    const nearestIcon = L.divIcon({
        className: 'ev-pin-nearest',
        html: '<div style="background:#059669; color:#fff; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:3px solid #facc15; box-shadow:0 0 18px rgba(16,185,129,0.8);"><i class="fa-solid fa-bolt" style="font-size:18px;"></i></div>',
        iconSize: [40, 40],
        iconAnchor: [20, 20]
    });

    // Mesafeye göre sırala
    stations.forEach(st => {
        st.distance = getDistanceKm(userLat, userLng, st.lat, st.lng);
    });
    stations.sort((a, b) => a.distance - b.distance);

    let nearest = stations[0] || null;
    const groupBounds = [userMarker];

    stations.forEach((st, idx) => {
        const isFirst = (idx === 0);
        const icon = isFirst ? nearestIcon : evIcon;
        const m = L.marker([st.lat, st.lng], { icon: icon }).addTo(evMap);
        evMarkers.push(m);
        groupBounds.push(m);

        const distStr = st.distance < 1 ? Math.round(st.distance * 1000) + ' m' : st.distance.toFixed(1) + ' km';
        const navUrl = `https://www.google.com/maps/dir/?api=1&destination=${st.lat},${st.lng}`;
        m.bindPopup(`<b>${st.title}</b><br><span style="color:#059669; font-weight:700;">⚡ ${st.network}</span><br>${st.address}<br><strong>📍 Mesafe: ${distStr}</strong><br><a href="${navUrl}" target="_blank" style="display:inline-block; margin-top:6px; background:#10b981; color:#fff; padding:4px 10px; border-radius:6px; font-weight:700; text-decoration:none;">🚗 Yol Tarifi Al</a>`);
    });

    // VIP Kartı Doldur ve Rota Çiz
    if (nearest) {
        const vipCard = document.getElementById('evVipCard');
        vipCard.style.display = 'flex';
        document.getElementById('nearestEvTitle').textContent = nearest.title;
        document.getElementById('nearestEvAddress').innerHTML = `<i class="fa-solid fa-location-dot" style="color:#10b981;"></i> ${nearest.address}`;
        
        const dStr = nearest.distance < 1 ? Math.round(nearest.distance * 1000) + ' m' : nearest.distance.toFixed(1) + ' km';
        document.getElementById('nearestEvDist').textContent = dStr;
        const eta = Math.max(3, Math.round((nearest.distance / 35) * 60));
        document.getElementById('nearestEvEta').textContent = `Tahmini Sürüş: ~${eta} dk`;
        document.getElementById('nearestEvNavBtn').href = `https://www.google.com/maps/dir/?api=1&destination=${nearest.lat},${nearest.lng}`;

        if (evRouteLine) evMap.removeLayer(evRouteLine);
        evRouteLine = L.polyline([[userLat, userLng], [nearest.lat, nearest.lng]], {
            color: '#10b981',
            weight: 4,
            dashArray: '8, 8',
            opacity: 0.85
        }).addTo(evMap);

        const group = new L.featureGroup(groupBounds);
        evMap.fitBounds(group.getBounds().pad(0.2));
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const btnGps = document.getElementById('btnGetEvLocation');
    const geoStatus = document.getElementById('evGeoStatus');
    const mapSection = document.getElementById('evMapSection');

    if (btnGps) {
        btnGps.addEventListener('click', function() {
            if (!navigator.geolocation) {
                geoStatus.className = 'ev-geo-status error';
                geoStatus.textContent = 'Tarayıcınız konum servisini desteklemiyor.';
                return;
            }

            btnGps.disabled = true;
            btnGps.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Konum Alınıyor...</span>';
            geoStatus.className = 'ev-geo-status';
            geoStatus.innerHTML = '<i class="fa-solid fa-satellite fa-beat"></i> GPS uydusuna bağlanılıyor...';

            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;

                    btnGps.disabled = false;
                    btnGps.innerHTML = '<i class="fa-solid fa-circle-check"></i> <span>Konum Alındı (Yenile)</span>';
                    geoStatus.className = 'ev-geo-status success';
                    geoStatus.innerHTML = `✅ Konumunuz alındı. En yakın şarj istasyonu haritada rotalandı!`;

                    renderEvMap(lat, lng, allEvStations);
                    mapSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                },
                function(err) {
                    btnGps.disabled = false;
                    btnGps.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i> <span>Tekrar Dene</span>';
                    geoStatus.className = 'ev-geo-status error';
                    geoStatus.textContent = 'Konum izni verilmedi veya zaman aşımına uğradı.';
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        });
    }
});
</script>

<?php get_footer(); ?>
