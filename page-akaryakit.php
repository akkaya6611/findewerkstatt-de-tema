<?php
/**
 * Template Name: Canlı Akaryakıt Fiyatları & Rota Yakıt Hesaplama
 * Description: 81 il için güncel benzin, motorin ve LPG fiyatları ile şehirler arası ve şehir içi canlı rota yakıt masrafı hesaplama sihirbazı.
 * 
 * @package OtoTamir360
 */

get_header();

require_once get_template_directory() . '/inc/data/fuel-prices-data.php';

$fuel_prices = ototamir_get_fuel_prices();
$routes_matrix = ototamir_get_popular_routes_matrix();

// Seçili şehir (varsayılan: istanbul)
$selected_city_slug = isset( $_GET['sehir'] ) && isset( $fuel_prices[ sanitize_key( $_GET['sehir'] ) ] ) ? sanitize_key( $_GET['sehir'] ) : 'istanbul';
$active_city = $fuel_prices[$selected_city_slug];
?>

<style>
/* ===================================================
   CANLI AKARYAKIT & YAKIT HESAPLAMA SİHİRBAZI STİLLERİ
=================================================== */
.fuel-page-wrapper {
    background: #f8fafc;
    min-height: 85vh;
    padding: 30px 0 80px;
}

/* BREADCRUMB */
.fuel-breadcrumb {
    margin-bottom: 24px;
    font-size: 13.5px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.fuel-breadcrumb a {
    color: #475569;
    text-decoration: none;
    font-weight: 600;
}
.fuel-breadcrumb a:hover {
    color: #10b981;
}
.fuel-breadcrumb span.current {
    color: #059669;
    font-weight: 700;
}

/* HERO SECTION */
.fuel-hero {
    background: linear-gradient(135deg, #064e3b 0%, #0f172a 60%, #065f46 100%);
    border-radius: 20px;
    padding: 40px 36px;
    color: #ffffff;
    margin-bottom: 35px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 16px 36px rgba(6, 78, 59, 0.25);
}
.fuel-hero::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 380px;
    height: 380px;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.2) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}
.fuel-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(16, 185, 129, 0.2);
    border: 1px solid rgba(16, 185, 129, 0.4);
    color: #34d399;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 16px;
}
.fuel-hero h1 {
    font-size: 32px;
    font-weight: 800;
    color: #ffffff;
    margin: 0 0 12px 0;
    line-height: 1.3;
}
.fuel-hero p {
    font-size: 16px;
    color: #cbd5e1;
    margin: 0 0 20px 0;
    max-width: 760px;
    line-height: 1.6;
}

/* GÜNCEL ŞEHİR FİYATLARI KARTLARI */
.fuel-cards-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 40px;
}
.fuel-price-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 24px;
    border: 2px solid transparent;
    box-shadow: 0 4px 16px rgba(0,0,0,0.04);
    display: flex;
    flex-direction: column;
    position: relative;
    transition: transform 0.2s, box-shadow 0.2s;
}
.fuel-price-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}
.card-benzin {
    border-color: #d1fae5;
    background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%);
}
.card-motorin {
    border-color: #e0e7ff;
    background: linear-gradient(180deg, #ffffff 0%, #eef2ff 100%);
}
.card-lpg {
    border-color: #ffedd5;
    background: linear-gradient(180deg, #ffffff 0%, #fff7ed 100%);
}

.fuel-card-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
}
.fuel-name-tag {
    font-size: 15px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
}
.fuel-icon-wrap {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}
.icon-benzin { background: #10b981; color: #ffffff; }
.icon-motorin { background: #6366f1; color: #ffffff; }
.icon-lpg { background: #f97316; color: #ffffff; }

.fuel-price-val {
    font-size: 34px;
    font-weight: 900;
    color: #0f172a;
    line-height: 1.1;
    margin-bottom: 6px;
}
.fuel-price-val small {
    font-size: 16px;
    font-weight: 600;
    color: #64748b;
}
.fuel-update-text {
    font-size: 12.5px;
    color: #64748b;
}

/* ŞEHİR SEÇİCİ */
.city-selector-bar {
    background: #ffffff;
    border-radius: 14px;
    padding: 16px 20px;
    margin-bottom: 30px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.03);
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 14px;
}
.city-selector-bar label {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
}
.city-dropdown-select {
    padding: 10px 16px;
    border-radius: 10px;
    border: 1.5px solid #cbd5e1;
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
    background: #ffffff;
    cursor: pointer;
    min-width: 220px;
}

/* HESAPLAYICI ANA BLOK */
.calculator-box {
    background: #ffffff;
    border-radius: 20px;
    padding: 36px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.05);
    border: 1px solid #e2e8f0;
    margin-bottom: 40px;
}
.calc-header {
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 20px;
    margin-bottom: 28px;
}
.calc-header h2 {
    font-size: 24px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 8px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.calc-header p {
    margin: 0;
    color: #64748b;
    font-size: 14.5px;
}

.calc-grid {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 36px;
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 8px;
}
.input-control {
    width: 100%;
    padding: 12px 14px;
    border-radius: 10px;
    border: 1.5px solid #cbd5e1;
    font-size: 15px;
    color: #1e293b;
    background: #f8fafc;
    transition: border-color 0.2s;
    box-sizing: border-box;
}
.input-control:focus {
    outline: none;
    border-color: #10b981;
    background: #ffffff;
}

/* PRESET SEGMENT BUTONLARI */
.preset-pills {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-top: 8px;
}
.preset-btn {
    background: #f1f5f9;
    border: 1.5px solid #e2e8f0;
    padding: 9px 12px;
    border-radius: 10px;
    font-size: 12.5px;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    text-align: left;
    transition: all 0.2s;
}
.preset-btn:hover, .preset-btn.active {
    background: #ecfdf5;
    border-color: #10b981;
    color: #065f46;
}

/* SONUÇ KARTI */
.calc-result-panel {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    border-radius: 16px;
    padding: 30px 26px;
    color: #ffffff;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.calc-result-title {
    font-size: 15px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 16px;
}
.total-cost-hero {
    margin-bottom: 24px;
}
.total-cost-label {
    font-size: 13px;
    color: #34d399;
    font-weight: 700;
    margin-bottom: 4px;
}
.total-cost-number {
    font-size: 42px;
    font-weight: 900;
    color: #ffffff;
    line-height: 1.1;
}
.total-cost-number span {
    font-size: 20px;
    color: #34d399;
}

.calc-breakdown {
    border-top: 1px solid rgba(255,255,255,0.1);
    padding-top: 18px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 22px;
}
.breakdown-row {
    display: flex;
    justify-content: space-between;
    font-size: 14px;
    color: #cbd5e1;
}
.breakdown-row strong {
    color: #ffffff;
}

.round-trip-box {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    padding: 12px 16px;
    border-radius: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13.5px;
}
.round-trip-box strong {
    color: #34d399;
    font-size: 16px;
}

/* TASARRUF & CTA BANNER */
.fuel-saving-cta {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border: 1.5px solid #bfdbfe;
    border-radius: 16px;
    padding: 26px 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 40px;
}
.cta-text h3 {
    margin: 0 0 6px 0;
    font-size: 18px;
    color: #1e3a8a;
    font-weight: 800;
}
.cta-text p {
    margin: 0;
    color: #3b82f6;
    font-size: 14px;
}
.btn-fuel-mechanic {
    background: #2563eb;
    color: #ffffff !important;
    padding: 12px 22px;
    border-radius: 10px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background 0.2s;
}
.btn-fuel-mechanic:hover {
    background: #1d4ed8;
}

/* RESPONSIVE */
@media (max-width: 900px) {
    .fuel-cards-grid {
        grid-template-columns: 1fr;
    }
    .calc-grid {
        grid-template-columns: 1fr;
    }
    .preset-pills {
        grid-template-columns: 1fr;
    }
    .fuel-hero {
        padding: 28px 20px;
    }
    .fuel-hero h1 {
        font-size: 24px;
    }
}
</style>

<div class="fuel-page-wrapper">
    <div class="container">

        <!-- BREADCRUMB -->
        <nav class="fuel-breadcrumb" aria-label="Breadcrumb">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><i class="fa-solid fa-house"></i> Ana Sayfa</a>
            <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
            <span>Sürücü Araçları</span>
            <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
            <span class="current">Canlı Akaryakıt Fiyatları & Rota Yakıt Hesaplama</span>
        </nav>

        <!-- HERO SECTION -->
        <div class="fuel-hero">
            <div class="fuel-hero-badge">
                <i class="fa-solid fa-bolt" style="color:#fbbf24;"></i>
                <span>Canlı OPET Pompa Fiyatları (Günde 2 Kez Otomatik Güncellenir)</span>
            </div>
            <h1>81 İl Güncel Akaryakıt Fiyatları ve Rota Yakıt Masrafı Hesaplama</h1>
            <p>
                İlinize ait güncel benzin, motorin (dizel) ve LPG (otogaz) pompa litre fiyatlarını inceleyin; gideceğiniz rotaya göre harcayacağınız toplam yakıt miktarını ve yol masrafınızı kuruşu kuruşuna hesaplayın.
            </p>
        </div>

        <!-- ŞEHİR SEÇİCİ -->
        <div class="city-selector-bar">
            <label for="citySelect">
                <i class="fa-solid fa-location-dot" style="color:#059669; font-size:16px;"></i>
                Şehir Seçin (Fiyatlar Otomatik Güncellenir):
            </label>
            <select id="citySelect" class="city-dropdown-select" onchange="changeFuelCity(this.value)">
                <?php foreach ( $fuel_prices as $slug => $c ) : ?>
                    <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $selected_city_slug, $slug ); ?>>
                        <?php echo esc_html( $c['name'] ); ?> (Benzin: ₺<?php echo esc_html( $c['benzin'] ); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- CANLI 3 BÜYÜK FİYAT KARTI -->
        <div class="fuel-cards-grid">
            <!-- BENZİN KARTI -->
            <div class="fuel-price-card card-benzin">
                <div class="fuel-card-top">
                    <div class="fuel-name-tag">
                        <div class="fuel-icon-wrap icon-benzin"><i class="fa-solid fa-gas-pump"></i></div>
                        <span>Kurşunsuz Benzin 95</span>
                    </div>
                    <span style="font-size:12px; font-weight:700; color:#059669; background:#d1fae5; padding:3px 8px; border-radius:6px;">Litre</span>
                </div>
                <div class="fuel-price-val">
                    <span id="displayBenzin"><?php echo esc_html( $active_city['benzin'] ); ?></span> <small>TL / Lt</small>
                </div>
                <div class="fuel-update-text">
                    <i class="fa-regular fa-clock"></i> Son Güncelleme: <strong><?php echo esc_html( $active_city['last_update'] ); ?></strong> (<?php echo esc_html( $active_city['name'] ); ?>)
                </div>
            </div>

            <!-- MOTORİN KARTI -->
            <div class="fuel-price-card card-motorin">
                <div class="fuel-card-top">
                    <div class="fuel-name-tag">
                        <div class="fuel-icon-wrap icon-motorin"><i class="fa-solid fa-oil-can"></i></div>
                        <span>Motorin (Dizel)</span>
                    </div>
                    <span style="font-size:12px; font-weight:700; color:#4f46e5; background:#e0e7ff; padding:3px 8px; border-radius:6px;">Litre</span>
                </div>
                <div class="fuel-price-val">
                    <span id="displayMotorin"><?php echo esc_html( $active_city['motorin'] ); ?></span> <small>TL / Lt</small>
                </div>
                <div class="fuel-update-text">
                    <i class="fa-regular fa-clock"></i> Son Güncelleme: <strong><?php echo esc_html( $active_city['last_update'] ); ?></strong> (<?php echo esc_html( $active_city['name'] ); ?>)
                </div>
            </div>

            <!-- LPG KARTI -->
            <div class="fuel-price-card card-lpg">
                <div class="fuel-card-top">
                    <div class="fuel-name-tag">
                        <div class="fuel-icon-wrap icon-lpg"><i class="fa-solid fa-fire-flame-simple"></i></div>
                        <span>Otogaz (LPG)</span>
                    </div>
                    <span style="font-size:12px; font-weight:700; color:#ea580c; background:#ffedd5; padding:3px 8px; border-radius:6px;">Litre</span>
                </div>
                <div class="fuel-price-val">
                    <span id="displayLpg"><?php echo esc_html( $active_city['lpg'] ); ?></span> <small>TL / Lt</small>
                </div>
                <div class="fuel-update-text">
                    <i class="fa-regular fa-clock"></i> Son Güncelleme: <strong><?php echo esc_html( $active_city['last_update'] ); ?></strong> (<?php echo esc_html( $active_city['name'] ); ?>)
                </div>
            </div>
        </div>

        <!-- İNTERAKTİF ROTA VE YAKIT HESAPLAYICI -->
        <div class="calculator-box" id="yakit-hesaplayici">
            <div class="calc-header">
                <h2><i class="fa-solid fa-calculator" style="color:#10b981;"></i> Rota Yakıt Masrafı Hesaplama Motoru</h2>
                <p>Nereden nereye gideceğinizi seçin veya doğrudan mesafeyi yazın; aracınızın yakıt tipine ve tüketimine göre toplam masrafı anında görün.</p>
            </div>

            <div class="calc-grid">
                <!-- FORM GİRİŞLERİ -->
                <div class="calc-inputs">
                    
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                        <div class="form-group">
                            <label><i class="fa-solid fa-play" style="color:#10b981; font-size:11px;"></i> Kalkış Şehri</label>
                            <select id="routeFrom" class="input-control" onchange="updateRouteDistance()">
                                <option value="istanbul">İstanbul</option>
                                <option value="ankara" selected>Ankara</option>
                                <option value="izmir">İzmir</option>
                                <option value="bursa">Bursa</option>
                                <option value="antalya">Antalya</option>
                                <option value="adana">Adana</option>
                                <option value="trabzon">Trabzon</option>
                                <option value="diyarbakir">Diyarbakır</option>
                                <option value="eskisehir">Eskişehir</option>
                                <option value="kocaeli">Kocaeli</option>
                                <option value="sakarya">Sakarya</option>
                                <option value="samsun">Samsun</option>
                                <option value="gaziantep">Gaziantep</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fa-solid fa-flag-checkered" style="color:#ef4444; font-size:11px;"></i> Varış Şehri</label>
                            <select id="routeTo" class="input-control" onchange="updateRouteDistance()">
                                <option value="istanbul" selected>İstanbul</option>
                                <option value="ankara">Ankara</option>
                                <option value="izmir">İzmir</option>
                                <option value="bursa">Bursa</option>
                                <option value="antalya">Antalya</option>
                                <option value="adana">Adana</option>
                                <option value="trabzon">Trabzon</option>
                                <option value="diyarbakir">Diyarbakır</option>
                                <option value="eskisehir">Eskişehir</option>
                                <option value="kocaeli">Kocaeli</option>
                                <option value="sakarya">Sakarya</option>
                                <option value="samsun">Samsun</option>
                                <option value="gaziantep">Gaziantep</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="routeKm"><i class="fa-solid fa-route" style="color:#6366f1;"></i> Yol Mesafesi (KM)</label>
                        <input type="number" id="routeKm" class="input-control" value="450" min="1" step="1" oninput="calculateFuelCost()">
                    </div>

                    <div class="form-group">
                        <label><i class="fa-solid fa-gas-pump" style="color:#f97316;"></i> Yakıt Türü</label>
                        <select id="fuelTypeSelect" class="input-control" onchange="calculateFuelCost()">
                            <option value="benzin">Kurşunsuz Benzin 95 (₺<?php echo esc_html( $active_city['benzin'] ); ?> / Lt)</option>
                            <option value="motorin" selected>Motorin / Dizel (₺<?php echo esc_html( $active_city['motorin'] ); ?> / Lt)</option>
                            <option value="lpg">Otogaz / LPG (₺<?php echo esc_html( $active_city['lpg'] ); ?> / Lt)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="fuelConsumption"><i class="fa-solid fa-gauge-high" style="color:#0ea5e9;"></i> Ortalama Yakıt Tüketimi (Litre / 100 KM)</label>
                        <input type="number" id="fuelConsumption" class="input-control" value="6.0" min="2" max="25" step="0.1" oninput="calculateFuelCost()">
                        
                        <div class="preset-pills">
                            <button type="button" class="preset-btn" onclick="setPresetConsumption(5.0)">🚗 Ekonomik Dizel/Hibrit (5.0L)</button>
                            <button type="button" class="preset-btn active" onclick="setPresetConsumption(6.2)">🚙 Standart C-Segment (6.2L)</button>
                            <button type="button" class="preset-btn" onclick="setPresetConsumption(7.8)">🏎️ Benzinli / SUV (7.8L)</button>
                            <button type="button" class="preset-btn" onclick="setPresetConsumption(8.8)">🚐 Ticari / Otomatik (8.8L)</button>
                        </div>
                    </div>

                </div>

                <!-- CANLI HESAPLAMA PANELİ -->
                <div class="calc-result-panel">
                    <div>
                        <div class="calc-result-title">
                            <i class="fa-solid fa-receipt"></i> Tahmini Yakıt Gideri
                        </div>
                        <div class="total-cost-hero">
                            <div class="total-cost-label">TEK YÖN TOPLAM MALİYET</div>
                            <div class="total-cost-number">
                                <span id="resTotalCost">2.398</span> <span>TL</span>
                            </div>
                        </div>

                        <div class="calc-breakdown">
                            <div class="breakdown-row">
                                <span>Toplam Mesafe:</span>
                                <strong id="resDistance">450 km</strong>
                            </div>
                            <div class="breakdown-row">
                                <span>Tüketilecek Yakıt:</span>
                                <strong id="resLiters">27.0 Litre</strong>
                            </div>
                            <div class="breakdown-row">
                                <span>Birim Pompa Fiyatı:</span>
                                <strong id="resUnitPrice">₺<?php echo esc_html( $active_city['motorin'] ); ?> / Lt</strong>
                            </div>
                            <div class="breakdown-row">
                                <span>KM Başına Yakıt Masrafı:</span>
                                <strong id="resKmCost">5.33 TL / km</strong>
                            </div>
                        </div>
                    </div>

                    <div class="round-trip-box">
                        <span><i class="fa-solid fa-arrows-left-right"></i> <strong>Gidiş - Dönüş:</strong></span>
                        <strong id="resRoundTrip">4.795 TL</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- TASARRUF & USTA BULMA BANNERI -->
        <div class="fuel-saving-cta">
            <div class="cta-text">
                <h3><i class="fa-solid fa-wrench"></i> Aracınız Beklediğinizden Fazla mı Yakıyor?</h3>
                <p>Tıkanmış enjektörler, kirli hava filtresi, aşınmış bujiler veya bozuk oksijen sensörü yakıt tüketiminizi %30 artırabilir. İlinizdeki uzman oto servislerinden destek alın.</p>
            </div>
            <a href="<?php echo esc_url( home_url( '/ustalar/?sehir=' . $selected_city_slug ) ); ?>" class="btn-fuel-mechanic">
                <span><?php echo esc_html( $active_city['name'] ); ?> Bakım Ustalarını Gör</span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

    </div>
</div>

<script>
// Şehir bazlı fiyat veri tablosu
const fuelPricesData = <?php echo json_encode( $fuel_prices ); ?>;
const routesMatrix = <?php echo json_encode( $routes_matrix ); ?>;

let currentCity = '<?php echo esc_js( $selected_city_slug ); ?>';

function changeFuelCity(citySlug) {
    if (fuelPricesData[citySlug]) {
        currentCity = citySlug;
        const data = fuelPricesData[citySlug];
        document.getElementById('displayBenzin').innerText = data.benzin;
        document.getElementById('displayMotorin').innerText = data.motorin;
        document.getElementById('displayLpg').innerText = data.lpg;
        
        // Dropdown seçeneklerindeki birim fiyatları güncelle
        const select = document.getElementById('fuelTypeSelect');
        select.options[0].text = `Kurşunsuz Benzin 95 (₺${data.benzin} / Lt)`;
        select.options[1].text = `Motorin / Dizel (₺${data.motorin} / Lt)`;
        select.options[2].text = `Otogaz / LPG (₺${data.lpg} / Lt)`;

        calculateFuelCost();
    }
}

function updateRouteDistance() {
    const from = document.getElementById('routeFrom').value;
    const to = document.getElementById('routeTo').value;
    const key = `${from}-${to}`;

    if (routesMatrix[key]) {
        document.getElementById('routeKm').value = routesMatrix[key];
    } else if (from === to) {
        document.getElementById('routeKm').value = 40; // Şehir içi varsayılan
    }
    calculateFuelCost();
}

function setPresetConsumption(val) {
    document.getElementById('fuelConsumption').value = val;
    const buttons = document.querySelectorAll('.preset-btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    calculateFuelCost();
}

function calculateFuelCost() {
    const km = parseFloat(document.getElementById('routeKm').value) || 0;
    const consumption = parseFloat(document.getElementById('fuelConsumption').value) || 0;
    const fuelType = document.getElementById('fuelTypeSelect').value;

    const cityData = fuelPricesData[currentCity] || fuelPricesData['istanbul'];
    const unitPrice = parseFloat(cityData[fuelType]) || 40.0;

    const totalLiters = (km * consumption) / 100;
    const totalCost = totalLiters * unitPrice;
    const kmCost = km > 0 ? (totalCost / km) : 0;
    const roundTrip = totalCost * 2;

    document.getElementById('resDistance').innerText = `${km.toLocaleString('tr-TR')} km`;
    document.getElementById('resLiters').innerText = `${totalLiters.toFixed(1)} Litre`;
    document.getElementById('resUnitPrice').innerText = `₺${unitPrice.toFixed(2)} / Lt`;
    document.getElementById('resTotalCost').innerText = Math.round(totalCost).toLocaleString('tr-TR');
    document.getElementById('resKmCost').innerText = `${kmCost.toFixed(2)} TL / km`;
    document.getElementById('resRoundTrip').innerText = `${Math.round(roundTrip).toLocaleString('tr-TR')} TL`;
}

// Sayfa yüklendiğinde hesapla
document.addEventListener('DOMContentLoaded', function() {
    calculateFuelCost();
});
</script>

<!-- SEO Structured Data Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "Canlı Akaryakıt Fiyatları ve Rota Yakıt Tüketim Hesaplama",
  "operatingSystem": "All",
  "applicationCategory": "UtilitiesApplication",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "TRY"
  },
  "description": "81 il için güncel benzin, motorin ve LPG pompa fiyatları ile şehirler arası yol yakıt masrafı hesaplama aracı."
}
</script>

<?php get_footer(); ?>
