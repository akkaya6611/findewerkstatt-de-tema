<?php
/**
 * Template Name: OBD-II Arıza Kodları ve Gösterge İkaz Lambaları
 * Description: Araç gösterge paneli ikaz lambaları ve 50+ OBD-II hata kodu için anında Türkçe teşhis, çözüm ve uzman usta bulma sihirbazı.
 * 
 * @package OtoTamir360
 */

get_header();

require_once get_template_directory() . '/inc/data/obd-codes-data.php';

$lights_data = ototamir_get_dashboard_lights_data();
$codes_data  = ototamir_get_obd_codes_data();

// Tüm şehirleri al
$all_cities = get_terms( array(
    'taxonomy'   => 'mechanic_city',
    'hide_empty' => false,
    'orderby'    => 'name',
    'order'      => 'ASC'
) );
?>

<style>
/* ===================================================
   OBD-II VE İKAZ LAMBASI SİHİRBAZI STİLLERİ
=================================================== */
.obd-page-wrapper {
    background: #f8fafc;
    min-height: 85vh;
    padding: 30px 0 80px;
}

/* BREADCRUMB */
.obd-breadcrumb {
    margin-bottom: 24px;
    font-size: 13.5px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.obd-breadcrumb a {
    color: #475569;
    text-decoration: none;
    font-weight: 600;
}
.obd-breadcrumb a:hover {
    color: #f97316;
}
.obd-breadcrumb span.current {
    color: #ea580c;
    font-weight: 700;
}

/* HERO BANNER */
.obd-hero-card {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 45%, #172554 100%);
    border-radius: 24px;
    padding: 44px 40px;
    color: white;
    position: relative;
    overflow: hidden;
    margin-bottom: 35px;
    box-shadow: 0 16px 36px rgba(15, 23, 42, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.08);
}
.obd-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(249, 115, 22, 0.2);
    border: 1px solid rgba(249, 115, 22, 0.4);
    color: #fdba74;
    padding: 6px 16px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 16px;
}
.obd-hero-title {
    font-size: 34px;
    font-weight: 900;
    line-height: 1.25;
    margin: 0 0 14px;
    letter-spacing: -0.5px;
}
.obd-hero-title span {
    background: linear-gradient(135deg, #fb923c 0%, #f97316 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.obd-hero-desc {
    font-size: 15.5px;
    color: #cbd5e1;
    max-width: 720px;
    line-height: 1.65;
    margin: 0 0 28px;
}

/* CANLI ARAMA KUTUSU */
.obd-search-box {
    display: flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.96);
    border-radius: 16px;
    padding: 6px 16px;
    max-width: 640px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    border: 2px solid transparent;
    transition: all 0.2s;
}
.obd-search-box:focus-within {
    border-color: #f97316;
    background: #ffffff;
}
.obd-search-box i {
    color: #f97316;
    font-size: 18px;
    margin-right: 12px;
}
.obd-search-input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 16px;
    padding: 12px 0;
    color: #0f172a;
    font-weight: 600;
}
.obd-search-input::placeholder {
    color: #94a3b8;
    font-weight: 400;
}
.obd-search-clear {
    background: #e2e8f0;
    border: none;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    color: #64748b;
    font-size: 12px;
    cursor: pointer;
    display: none;
    align-items: center;
    justify-content: center;
}

/* SEKME DÜĞMELERİ */
.obd-tabs-nav {
    display: flex;
    gap: 12px;
    margin-bottom: 28px;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 12px;
    flex-wrap: wrap;
}
.obd-tab-btn {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #475569;
    padding: 12px 24px;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}
.obd-tab-btn:hover {
    color: #ea580c;
    border-color: #ea580c;
}
.obd-tab-btn.active {
    background: #ea580c;
    color: white;
    border-color: #ea580c;
    box-shadow: 0 4px 12px rgba(234, 88, 12, 0.3);
}

/* İKAZ LAMBASI KARTLARI GRİDİ */
.lights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 18px;
    margin-bottom: 40px;
}
.light-card {
    background: white;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    padding: 20px;
    cursor: pointer;
    transition: all 0.25s ease;
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
}
.light-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.06);
    border-color: #f97316;
}
.light-card.border-red {
    border-left: 4px solid #ef4444;
}
.light-card.border-amber {
    border-left: 4px solid #f59e0b;
}
.light-icon-wrap {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    margin-bottom: 14px;
}
.light-card.border-red .light-icon-wrap {
    background: #fef2f2;
    color: #dc2626;
}
.light-card.border-amber .light-icon-wrap {
    background: #fffbeb;
    color: #d97706;
}
.light-title {
    font-size: 15.5px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 8px;
    line-height: 1.35;
}
.light-badge {
    display: inline-block;
    font-size: 11.5px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 6px;
    margin-bottom: 10px;
    align-self: flex-start;
}
.light-badge.badge-red {
    background: #fee2e2;
    color: #991b1b;
}
.light-badge.badge-amber {
    background: #fef3c7;
    color: #92400e;
}
.light-summary {
    font-size: 13px;
    color: #64748b;
    line-height: 1.5;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* OBD KODLARI LİSTESİ */
.codes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}
.code-card {
    background: white;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    padding: 24px;
    transition: all 0.25s ease;
    display: flex;
    flex-direction: column;
}
.code-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.05);
    border-color: #f97316;
}
.code-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.code-badge {
    background: #0f172a;
    color: #38bdf8;
    font-family: monospace;
    font-size: 17px;
    font-weight: 800;
    padding: 4px 12px;
    border-radius: 8px;
    letter-spacing: 0.5px;
}
.code-cat {
    font-size: 12px;
    color: #64748b;
    font-weight: 600;
    background: #f1f5f9;
    padding: 4px 10px;
    border-radius: 20px;
}
.code-title {
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 10px;
    line-height: 1.4;
}
.code-desc {
    font-size: 13.5px;
    color: #475569;
    line-height: 1.6;
    margin: 0 0 16px;
    flex-grow: 1;
}
.code-causes-box {
    background: #f8fafc;
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 16px;
    border-left: 3px solid #f97316;
}
.code-causes-box strong {
    display: block;
    font-size: 12px;
    color: #0f172a;
    margin-bottom: 6px;
}
.code-causes-box ul {
    margin: 0;
    padding-left: 16px;
    font-size: 12px;
    color: #64748b;
    line-height: 1.5;
}
.code-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 14px;
    border-top: 1px solid #f1f5f9;
    gap: 10px;
}
.code-btn-usta {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: white;
    text-decoration: none;
    font-size: 12.5px;
    font-weight: 700;
    padding: 8px 14px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}
.code-btn-usta:hover {
    box-shadow: 0 4px 12px rgba(234, 88, 12, 0.35);
}

/* DETAY MODALI */
.obd-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(4px);
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.obd-modal-card {
    background: white;
    border-radius: 20px;
    max-width: 640px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    padding: 32px;
    position: relative;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    animation: modalPop 0.2s ease-out;
}
@keyframes modalPop {
    from { transform: scale(0.95); opacity: 0; }
    to   { transform: scale(1); opacity: 1; }
}
.obd-modal-close {
    position: absolute;
    top: 20px;
    right: 20px;
    background: #f1f5f9;
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    color: #475569;
    font-size: 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}
.obd-modal-close:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.obd-modal-section {
    margin-bottom: 20px;
}
.obd-modal-section h4 {
    font-size: 14px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.obd-modal-section p,
.obd-modal-section ul {
    font-size: 14px;
    color: #475569;
    line-height: 1.65;
    margin: 0;
}
.obd-modal-section ul {
    padding-left: 20px;
}
.obd-modal-section li {
    margin-bottom: 4px;
}
.obd-city-selector-wrap {
    background: #fff7ed;
    border: 1px solid #ffedd5;
    border-radius: 14px;
    padding: 18px;
    margin-top: 24px;
}
.obd-city-selector-wrap h4 {
    color: #9a3412;
    margin: 0 0 10px;
    font-size: 14px;
    font-weight: 700;
}
.obd-city-flex {
    display: flex;
    gap: 10px;
}
.obd-city-select {
    flex: 1;
    border: 1.5px solid #cbd5e1;
    border-radius: 8px;
    padding: 10px 12px;
    font-size: 14px;
    background: white;
    color: #0f172a;
}
.obd-city-go-btn {
    background: #ea580c;
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 13.5px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}
.obd-city-go-btn:hover {
    background: #c2410c;
}

/* SSS VE BİLGİ BÖLÜMÜ */
.obd-faq-section {
    background: white;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    padding: 40px;
    margin-top: 50px;
}
.obd-faq-section h2 {
    font-size: 24px;
    font-weight: 900;
    color: #0f172a;
    margin: 0 0 24px;
}
.obd-faq-item {
    border-bottom: 1px solid #f1f5f9;
    padding: 18px 0;
}
.obd-faq-item:last-child {
    border-bottom: none;
}
.obd-faq-item h3 {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 8px;
}
.obd-faq-item p {
    font-size: 14.5px;
    color: #64748b;
    line-height: 1.6;
    margin: 0;
}

@media (max-width: 768px) {
    .obd-hero-card { padding: 28px 20px; }
    .obd-hero-title { font-size: 24px; }
    .obd-hero-desc { font-size: 14px; }
    .lights-grid { grid-template-columns: 1fr; }
    .codes-grid { grid-template-columns: 1fr; }
    .obd-modal-card { padding: 24px 18px; }
    .obd-city-flex { flex-direction: column; }
    .obd-faq-section { padding: 24px 18px; }
}
</style>

<div class="obd-page-wrapper">
    <div class="container">

        <!-- BREADCRUMB -->
        <nav class="obd-breadcrumb" aria-label="Ekmek Kırıntıları">
            <a href="<?php echo esc_url( home_url('/') ); ?>"><i class="fa-solid fa-house" style="font-size:12px;"></i> Ana Sayfa</a>
            <span>/</span>
            <span class="current">Arıza Kodları & Gösterge Lambaları</span>
        </nav>

        <!-- HERO KARTI -->
        <div class="obd-hero-card">
            <div class="obd-hero-badge">
                <i class="fa-solid fa-microchip"></i>
                <span>OTOTAMİR-360 Akıllı Teşhis Rehberi</span>
            </div>
            <h1 class="obd-hero-title">
                Arıza Kodunu veya Lambayı Seçin, <br>
                <span>Nedenini & En Yakın Uzman Ustayla Öğrenin</span>
            </h1>
            <p class="obd-hero-desc">
                Aracınızın gösterge panelinde yanan ikaz lambası ne anlama geliyor? Ya da OBD-II cihazında çıkan P0420, P0300, P0171 gibi arıza kodları hangi parçanın bozulduğunu gösterir? Aşağıdan arayın, saniyeler içinde teşhis edin.
            </p>

            <!-- CANLI ARAMA -->
            <div class="obd-search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="obdSearchInput" class="obd-search-input" placeholder="Hata kodu veya lamba adı yazın (Örn: P0420, Motor lambası, Hararet, P0300)..." autocomplete="off">
                <button type="button" id="obdSearchClear" class="obd-search-clear" title="Temizle"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </div>

        <!-- SEKME SEÇİCİ -->
        <div class="obd-tabs-nav">
            <button type="button" class="obd-tab-btn active" data-tab="lights">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>Gösterge Paneli İkaz Lambaları (14 Temel Lamba)</span>
            </button>
            <button type="button" class="obd-tab-btn" data-tab="codes">
                <i class="fa-solid fa-code"></i>
                <span>OBD-II Hata Kodları (50+ Popüler Kod)</span>
            </button>
        </div>

        <!-- BÖLÜM 1: GÖSTERGE LAMBALARI -->
        <div id="tabLightsContent" class="obd-tab-content">
            <div class="lights-grid" id="lightsGrid">
                <?php foreach ( $lights_data as $key => $l ) : ?>
                    <div class="light-card border-<?php echo esc_attr( $l['color'] ); ?>" 
                         data-id="<?php echo esc_attr( $key ); ?>"
                         data-title="<?php echo esc_attr( $l['title'] ); ?>"
                         data-severity="<?php echo esc_attr( $l['severity_label'] ); ?>"
                         data-service-slug="<?php echo esc_attr( $l['service_slug'] ); ?>"
                         data-service-name="<?php echo esc_attr( $l['service_name'] ); ?>"
                         data-search="<?php echo esc_attr( mb_strtolower( $l['title'] . ' ' . $l['summary'] . ' ' . implode(' ', $l['causes']) ) ); ?>">
                        
                        <div class="light-icon-wrap">
                            <i class="<?php echo esc_attr( $l['icon'] ); ?>"></i>
                        </div>
                        <span class="light-badge badge-<?php echo esc_attr( $l['color'] ); ?>">
                            <?php echo esc_html( $l['color'] === 'red' ? '🚨 KRİTİK / ACİL' : '⚠️ SERVİS UYARISI' ); ?>
                        </span>
                        <h3 class="light-title"><?php echo esc_html( $l['title'] ); ?></h3>
                        <p class="light-summary"><?php echo esc_html( $l['summary'] ); ?></p>
                        
                        <div style="margin-top:auto; padding-top:14px; font-size:12.5px; font-weight:700; color:#ea580c; display:flex; align-items:center; gap:6px;">
                            <span>Detaylı Teşhis & Ustalar</span>
                            <i class="fa-solid fa-arrow-right" style="font-size:11px;"></i>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- BÖLÜM 2: OBD KODLARI -->
        <div id="tabCodesContent" class="obd-tab-content" style="display:none;">
            <div class="codes-grid" id="codesGrid">
                <?php foreach ( $codes_data as $code => $c ) : ?>
                    <div class="code-card" 
                         data-code="<?php echo esc_attr( $code ); ?>"
                         data-search="<?php echo esc_attr( mb_strtolower( $code . ' ' . $c['title_tr'] . ' ' . $c['category'] . ' ' . $c['desc'] . ' ' . implode(' ', $c['causes']) ) ); ?>">
                        <div class="code-head">
                            <span class="code-badge"><?php echo esc_html( $code ); ?></span>
                            <span class="code-cat"><?php echo esc_html( $c['category'] ); ?></span>
                        </div>
                        <h3 class="code-title"><?php echo esc_html( $c['title_tr'] ); ?></h3>
                        <p class="code-desc"><?php echo esc_html( $c['desc'] ); ?></p>
                        
                        <div class="code-causes-box">
                            <strong>Olası Nedenler:</strong>
                            <ul>
                                <?php foreach ( array_slice( $c['causes'], 0, 3 ) as $cause ) : ?>
                                    <li><?php echo esc_html( $cause ); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="code-actions">
                            <span style="font-size:11.5px; color:#64748b;">
                                <?php echo esc_html( $c['severity'] === 'critical' ? '🚨 Acil Onarım' : '⚠️ Servis İncelemesi' ); ?>
                            </span>
                            <a href="<?php echo esc_url( home_url( '/ustalar/?service_type=' . $c['service'] ) ); ?>" class="code-btn-usta">
                                <i class="fa-solid fa-wrench"></i>
                                <span><?php echo esc_html( $c['service_name'] ); ?></span>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- SSS (SEO & SCHEMA ENTEGRASYONU) -->
        <div class="obd-faq-section">
            <h2>Sıkça Sorulan Sorular: Gösterge Lambaları ve OBD-II Kodları</h2>
            
            <div class="obd-faq-item">
                <h3>Motor arıza lambası sarı yanıp sönüyorsa ne yapılmalı?</h3>
                <p>Motor arıza lambasının (Check Engine) sabit yanması yerine yanıp sönmesi (flashing), silindirlerde ciddi bir ateşleme teklemesi (misfire) olduğunu ve yanmamış çiğ yakıtın katalitik konvertöre ulaşarak onu eritme tehlikesi taşıdığını gösterir. Bu durumda araç derhal durdurulmalı, motor zorlanmamalı ve çekici çağrılmalıdır.</p>
            </div>

            <div class="obd-faq-item">
                <h3>Kırmızı renkli gösterge lambaları ile sarı renkli lambalar arasındaki fark nedir?</h3>
                <p>Gösterge panelindeki kırmızı lambalar (Yağ basıncı, hararet, fren hidroliği, akü şarjı) acil ve hayati tehlike belirtir; sürüşe devam edilmesi motorun kilitlenmesine veya can güvenliği kaybına yol açar. Sarı/turuncu lambalar ise aracın koruma modunda veya kontrollü hızla en yakın yetkili/özel servise götürülebileceğini bildiren erken uyarı sistemleridir.</p>
            </div>

            <div class="obd-faq-item">
                <h3>OBD-II arıza kodu sildirmek arızayı çözer mi?</h3>
                <p>Hayır. Diyagnostik cihazıyla arıza kodunu silmek yalnızca göstergedeki ışığı geçici olarak söndürür. Arızaya sebep olan mekanik veya elektriksel problem (örneğin tıkalı katalizör, arızalı oksijen sensörü veya buji) giderilmediği takdirde birkaç kilometre sonra arıza kodu yeniden belirecektir.</p>
            </div>
        </div>

    </div>
</div>

<!-- DETAY MODAL PENCERESİ -->
<div id="obdModal" class="obd-modal-overlay">
    <div class="obd-modal-card">
        <button type="button" class="obd-modal-close" id="obdModalClose"><i class="fa-solid fa-xmark"></i></button>
        
        <div id="modalContent"></div>

        <!-- İLİNİZDEKİ UZMAN USTA YÖNLENDİRMESİ -->
        <div class="obd-city-selector-wrap">
            <h4><i class="fa-solid fa-location-dot"></i> İlinizdeki İlgili Uzman Servisleri Bulun:</h4>
            <div class="obd-city-flex">
                <select id="modalCitySelect" class="obd-city-select">
                    <option value="">Şehir Seçin (Tüm Türkiye)</option>
                    <?php if ( ! empty( $all_cities ) && ! is_wp_error( $all_cities ) ) : ?>
                        <?php foreach ( $all_cities as $c_term ) : ?>
                            <option value="<?php echo esc_attr( $c_term->slug ); ?>"><?php echo esc_html( $c_term->name ); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <a href="#" id="modalGoUstaBtn" class="obd-city-go-btn" target="_blank">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>Ustaları Gör</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- JAVASCRIPT ETKİLEŞİM MOTORU -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const lightsData = <?php echo wp_json_encode( $lights_data ); ?>;
    const tabBtns = document.querySelectorAll('.obd-tab-btn');
    const tabLights = document.getElementById('tabLightsContent');
    const tabCodes  = document.getElementById('tabCodesContent');
    const searchInput = document.getElementById('obdSearchInput');
    const searchClear = document.getElementById('obdSearchClear');
    
    // Sekme Değiştirme
    tabBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const tab = this.getAttribute('data-tab');
            if (tab === 'lights') {
                tabLights.style.display = 'block';
                tabCodes.style.display = 'none';
            } else {
                tabLights.style.display = 'none';
                tabCodes.style.display = 'block';
            }
        });
    });

    // Canlı Arama
    searchInput.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        if (query.length > 0) {
            searchClear.style.display = 'flex';
        } else {
            searchClear.style.display = 'none';
        }

        // İkaz lambalarını filtrele
        const lightCards = document.querySelectorAll('#lightsGrid .light-card');
        lightCards.forEach(function(card) {
            const str = card.getAttribute('data-search') || '';
            if (str.indexOf(query) !== -1) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });

        // OBD kodlarını filtrele
        const codeCards = document.querySelectorAll('#codesGrid .code-card');
        codeCards.forEach(function(card) {
            const str = card.getAttribute('data-search') || '';
            if (str.indexOf(query) !== -1) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });

        // Eğer arama sorgusu "P0" veya kod içeriyorsa otomatik OBD sekmesine geç
        if (/^p\d/i.test(query) || /^b\d/i.test(query) || /^c\d/i.test(query) || /^u\d/i.test(query)) {
            tabBtns[1].click();
        }
    });

    searchClear.addEventListener('click', function() {
        searchInput.value = '';
        searchInput.dispatchEvent(new Event('input'));
        searchInput.focus();
    });

    // Modal Açma / Kapatma
    const modal = document.getElementById('obdModal');
    const modalClose = document.getElementById('obdModalClose');
    const modalContent = document.getElementById('modalContent');
    const modalGoBtn = document.getElementById('modalGoUstaBtn');
    const modalCitySelect = document.getElementById('modalCitySelect');
    let currentServiceSlug = 'mekanik-ustasi';

    function updateGoBtnLink() {
        const city = modalCitySelect.value;
        let url = '<?php echo esc_url( home_url("/ustalar/") ); ?>?service_type=' + encodeURIComponent(currentServiceSlug);
        if (city) {
            url += '&mechanic_city=' + encodeURIComponent(city);
        }
        modalGoBtn.setAttribute('href', url);
    }
    modalCitySelect.addEventListener('change', updateGoBtnLink);

    document.querySelectorAll('#lightsGrid .light-card').forEach(function(card) {
        card.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const data = lightsData[id];
            if (!data) return;

            currentServiceSlug = data.service_slug;
            updateGoBtnLink();

            let symptomsHtml = '';
            if (data.symptoms && data.symptoms.length) {
                symptomsHtml = '<div class="obd-modal-section"><h4><i class="fa-solid fa-triangle-exclamation" style="color:#f59e0b;"></i> Sık Görülen Belirtiler:</h4><ul>' + 
                    data.symptoms.map(s => '<li>' + s + '</li>').join('') + '</ul></div>';
            }

            let causesHtml = '';
            if (data.causes && data.causes.length) {
                causesHtml = '<div class="obd-modal-section"><h4><i class="fa-solid fa-screwdriver-wrench" style="color:#f97316;"></i> Olası Arıza Nedenleri:</h4><ul>' + 
                    data.causes.map(c => '<li>' + c + '</li>').join('') + '</ul></div>';
            }

            let html = `
                <div style="display:flex; align-items:center; gap:14px; margin-bottom:18px;">
                    <div style="width:50px; height:50px; border-radius:14px; background:${data.color === 'red' ? '#fee2e2' : '#fef3c7'}; color:${data.color === 'red' ? '#dc2626' : '#d97706'}; display:flex; align-items:center; justify-content:center; font-size:24px;">
                        <i class="${data.icon}"></i>
                    </div>
                    <div>
                        <span style="font-size:12px; font-weight:800; color:${data.color === 'red' ? '#dc2626' : '#d97706'}; background:${data.color === 'red' ? '#fef2f2' : '#fffbeb'}; padding:2px 8px; border-radius:6px;">
                            ${data.severity_label}
                        </span>
                        <h2 style="font-size:20px; font-weight:900; color:#0f172a; margin:4px 0 0;">${data.title}</h2>
                    </div>
                </div>

                <div class="obd-modal-section">
                    <h4><i class="fa-solid fa-circle-info" style="color:#3b82f6;"></i> Arıza Tanımı:</h4>
                    <p>${data.summary}</p>
                </div>

                ${symptomsHtml}
                ${causesHtml}

                <div class="obd-modal-section" style="background:#f1f5f9; padding:14px; border-radius:10px; border-left:4px solid #0f172a;">
                    <h4><i class="fa-solid fa-shield-halved" style="color:#0f172a;"></i> Sürücünün Ne Yapması Gerekir?</h4>
                    <p style="margin:0; font-weight:600; color:#1e293b;">${data.what_to_do}</p>
                </div>
            `;

            modalContent.innerHTML = html;
            modal.style.display = 'flex';
        });
    });

    modalClose.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script>

<?php get_footer(); ?>
