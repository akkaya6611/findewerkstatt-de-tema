<?php
/**
 * Template Name: Kronik Arızalar & Araç Alım Rehberi
 * Description: Popüler araç modellerinin bilinen kronik arızaları, masraf boyutları, usta ekspertiz tavsiyeleri ve "Bu araba alınır mı?" karne sorgulama motoru.
 * 
 * @package OtoTamir360
 */

get_header();

require_once get_template_directory() . '/inc/data/chronic-faults-data.php';

$cars = ototamir_get_chronic_faults_data();
$brands = ototamir_get_chronic_faults_brands();
?>

<style>
/* ===================================================
   KRONİK ARIZA & ARAÇ ALIM REHBERİ STİLLERİ
=================================================== */
.chronic-page-wrapper {
    background: #f8fafc;
    min-height: 85vh;
    padding: 30px 0 80px;
}

/* BREADCRUMB */
.chronic-breadcrumb {
    margin-bottom: 24px;
    font-size: 13.5px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.chronic-breadcrumb a {
    color: #475569;
    text-decoration: none;
    font-weight: 600;
}
.chronic-breadcrumb a:hover {
    color: #ea580c;
}
.chronic-breadcrumb span.current {
    color: #c2410c;
    font-weight: 700;
}

/* HERO */
.chronic-hero {
    background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 60%, #312e81 100%);
    border-radius: 20px;
    padding: 40px 36px;
    color: #ffffff;
    margin-bottom: 35px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 16px 36px rgba(30, 27, 75, 0.25);
}
.chronic-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(249, 115, 22, 0.2);
    border: 1px solid rgba(249, 115, 22, 0.4);
    color: #fb923c;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 16px;
}
.chronic-hero h1 {
    font-size: 32px;
    font-weight: 800;
    color: #ffffff;
    margin: 0 0 12px 0;
    line-height: 1.3;
}
.chronic-hero p {
    font-size: 16px;
    color: #cbd5e1;
    margin: 0 0 24px 0;
    max-width: 800px;
    line-height: 1.6;
}

/* ARAMA VE MARKA FİLTRELERİ */
.chronic-search-bar {
    display: flex;
    gap: 12px;
    max-width: 600px;
    margin-bottom: 20px;
}
.search-input-wrap {
    position: relative;
    flex: 1;
}
.search-input-wrap i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 16px;
}
.chronic-search-input {
    width: 100%;
    padding: 13px 16px 13px 44px;
    border-radius: 12px;
    border: 1.5px solid rgba(255,255,255,0.2);
    background: rgba(255,255,255,0.1);
    color: #ffffff;
    font-size: 15px;
    box-sizing: border-box;
}
.chronic-search-input::placeholder {
    color: #94a3b8;
}
.chronic-search-input:focus {
    outline: none;
    background: rgba(255,255,255,0.15);
    border-color: #f97316;
}

.brand-filter-pills {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.brand-pill {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.15);
    color: #e2e8f0;
    padding: 7px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.brand-pill:hover, .brand-pill.active {
    background: #f97316;
    border-color: #f97316;
    color: #ffffff;
}

/* ARAÇ KARTLARI LİSTESİ */
.cars-grid {
    display: flex;
    flex-direction: column;
    gap: 28px;
}
.car-card {
    background: #ffffff;
    border-radius: 20px;
    border: 1.5px solid #e2e8f0;
    box-shadow: 0 4px 18px rgba(0,0,0,0.04);
    overflow: hidden;
    transition: box-shadow 0.2s;
}
.car-card:hover {
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}

.car-header {
    padding: 24px 28px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 16px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
}
.car-title-block h3 {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 6px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.car-meta-years {
    font-size: 13.5px;
    font-weight: 600;
    color: #64748b;
    margin-right: 12px;
}
.car-engines {
    display: inline-block;
    background: #f1f5f9;
    padding: 3px 10px;
    border-radius: 6px;
    font-size: 12.5px;
    color: #334155;
    font-weight: 600;
}

.verdict-tag {
    padding: 8px 16px;
    border-radius: 30px;
    font-size: 13.5px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.verdict-alinir {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
}
.verdict-sartli {
    background: #fffbeb;
    color: #d97706;
    border: 1px solid #fde68a;
}
.verdict-riskli {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

/* KARNE VE SKORLAR */
.car-score-bar {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    padding: 16px 28px;
    background: #f8fafc;
    border-bottom: 1px solid #f1f5f9;
}
.score-item {
    font-size: 13px;
    color: #64748b;
}
.score-item strong {
    display: block;
    font-size: 14.5px;
    color: #1e293b;
    margin-top: 2px;
}

/* DETAY KISMI */
.car-body {
    padding: 26px 28px;
}
.section-title {
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0 0 16px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* KRONİK ARIZALAR LİSTESİ */
.issues-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 14px;
    margin-bottom: 24px;
}
.issue-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px 20px;
    border-left: 4px solid #f97316;
}
.issue-card.sev-Kritik { border-left-color: #ef4444; }
.issue-card.sev-Yüksek { border-left-color: #f97316; }
.issue-card.sev-Orta   { border-left-color: #eab308; }
.issue-card.sev-Düşük  { border-left-color: #3b82f6; }

.issue-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    flex-wrap: wrap;
    gap: 8px;
}
.issue-title {
    font-size: 15.5px;
    font-weight: 700;
    color: #0f172a;
}
.issue-severity {
    font-size: 11.5px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 6px;
    background: #f1f5f9;
    color: #475569;
}
.issue-symptoms {
    font-size: 13.5px;
    color: #475569;
    margin-bottom: 8px;
    line-height: 1.5;
}
.issue-cost {
    font-size: 13px;
    font-weight: 700;
    color: #c2410c;
    background: #fff7ed;
    padding: 4px 10px;
    border-radius: 6px;
    display: inline-block;
    margin-bottom: 6px;
}
.issue-sol {
    font-size: 13px;
    color: #1e293b;
    background: #f8fafc;
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

/* EKSPERTİZ İPUÇLARI */
.buyer-tips-box {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 12px;
    padding: 18px 22px;
    margin-bottom: 20px;
}
.buyer-tips-box h4 {
    margin: 0 0 10px 0;
    font-size: 14.5px;
    font-weight: 800;
    color: #1e40af;
    display: flex;
    align-items: center;
    gap: 8px;
}
.tips-list {
    margin: 0;
    padding-left: 20px;
    color: #1e3a8a;
    font-size: 13.5px;
    line-height: 1.6;
}

.car-footer-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
    padding-top: 16px;
    border-top: 1px solid #f1f5f9;
}
.car-summary-quote {
    font-size: 13.5px;
    font-style: italic;
    color: #64748b;
    max-width: 680px;
}
.btn-brand-mechanics {
    background: #0f172a;
    color: #ffffff !important;
    padding: 10px 18px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 13.5px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background 0.2s;
}
.btn-brand-mechanics:hover {
    background: #f97316;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .car-score-bar {
        grid-template-columns: repeat(2, 1fr);
    }
    .chronic-hero h1 {
        font-size: 24px;
    }
    .car-header {
        flex-direction: column;
    }
}
</style>

<div class="chronic-page-wrapper">
    <div class="container">

        <!-- BREADCRUMB -->
        <nav class="chronic-breadcrumb" aria-label="Breadcrumb">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><i class="fa-solid fa-house"></i> Ana Sayfa</a>
            <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
            <span>Sürücü Araçları</span>
            <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
            <span class="current">Kronik Arızalar & "Bu Araba Alınır mı?" Rehberi</span>
        </nav>

        <!-- HERO SECTION -->
        <div class="chronic-hero">
            <div class="chronic-hero-badge">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>40+ Popüler Araç İçin Usta Ekspertiz Karnesi</span>
            </div>
            <h1>Araç Kronik Arızaları ve "Bu Araba Alınır mı?" Değerlendirme Motoru</h1>
            <p>
                İkinci el araba almadan önce bilinmesi gereken meşhur kronik sorunlar, motor ve şanzıman riskleri, ortalama tamir masrafları ve sanayi ustalarının ekspertiz tavsiyeleri burada!
            </p>

            <!-- ARAMA BAR VE MARKA FİLTRELERİ -->
            <div class="chronic-search-bar">
                <div class="search-input-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="chronicSearch" class="chronic-search-input" placeholder="Model veya motor ara (Örn: Golf 7, Egea 1.4, PureTech, EDC)..." onkeyup="filterCars()">
                </div>
            </div>

            <div class="brand-filter-pills">
                <button type="button" class="brand-pill active" onclick="filterByBrand('all')">Tüm Markalar</button>
                <?php foreach ( $brands as $b_slug => $b_name ) : ?>
                    <button type="button" class="brand-pill" onclick="filterByBrand('<?php echo esc_attr( $b_slug ); ?>')"><?php echo esc_html( $b_name ); ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ARAÇ KARTLARI LİSTESİ -->
        <div class="cars-grid" id="carsList">
            <?php foreach ( $cars as $car ) : ?>
                <div class="car-card" data-brand="<?php echo esc_attr( $car['brand_slug'] ); ?>" data-search="<?php echo esc_attr( strtolower( $car['brand'] . ' ' . $car['model'] . ' ' . $car['engines'] . ' ' . $car['summary'] ) ); ?>">
                    
                    <!-- KART BAŞLIĞI -->
                    <div class="car-header">
                        <div class="car-title-block">
                            <h3>
                                <i class="fa-solid fa-car-side" style="color:#4f46e5;"></i>
                                <?php echo esc_html( $car['brand'] . ' ' . $car['model'] ); ?>
                            </h3>
                            <div>
                                <span class="car-meta-years"><i class="fa-regular fa-calendar"></i> <?php echo esc_html( $car['years'] ); ?></span>
                                <span class="car-engines"><i class="fa-solid fa-microchip"></i> <?php echo esc_html( $car['engines'] ); ?></span>
                            </div>
                        </div>

                        <div class="verdict-tag verdict-<?php echo esc_attr( $car['verdict'] ); ?>">
                            <?php if ( $car['verdict'] === 'alinir' ) : ?>
                                <i class="fa-solid fa-circle-check"></i>
                            <?php elseif ( $car['verdict'] === 'sartli' ) : ?>
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            <?php else : ?>
                                <i class="fa-solid fa-circle-xmark"></i>
                            <?php endif; ?>
                            <span><?php echo esc_html( $car['verdict_text'] ); ?></span>
                        </div>
                    </div>

                    <!-- KARNE SKORLARI -->
                    <div class="car-score-bar">
                        <div class="score-item">
                            Genel Skor
                            <strong style="color:#059669; font-size:16px;">★ <?php echo esc_html( $car['overall_score'] ); ?> / 10</strong>
                        </div>
                        <div class="score-item">
                            Yedek Parça
                            <strong><?php echo esc_html( $car['part_score'] ); ?></strong>
                        </div>
                        <div class="score-item">
                            Periyodik Bakım
                            <strong><?php echo esc_html( $car['maint_cost'] ); ?></strong>
                        </div>
                        <div class="score-item">
                            2. El Piyasası
                            <strong><?php echo esc_html( $car['resale_speed'] ); ?></strong>
                        </div>
                    </div>

                    <!-- GÖVDE: KRONİK ARIZALAR & İPUÇLARI -->
                    <div class="car-body">
                        
                        <div class="section-title">
                            <i class="fa-solid fa-wrench" style="color:#ea580c;"></i>
                            Bilinen Kronik Arızalar & Çözüm Maliyetleri
                        </div>

                        <div class="issues-grid">
                            <?php foreach ( $car['chronic_issues'] as $issue ) : ?>
                                <div class="issue-card sev-<?php echo esc_attr( $issue['severity'] ); ?>">
                                    <div class="issue-head">
                                        <span class="issue-title"><?php echo esc_html( $issue['title'] ); ?></span>
                                        <span class="issue-severity">Önem Derecesi: <?php echo esc_html( $issue['severity'] ); ?></span>
                                    </div>
                                    <div class="issue-symptoms">
                                        <strong>Belirtiler:</strong> <?php echo esc_html( $issue['symptoms'] ); ?>
                                    </div>
                                    <div class="issue-cost">
                                        <i class="fa-solid fa-tag"></i> Tahmini Tamir Masrafı: <?php echo esc_html( $issue['cost_range'] ); ?>
                                    </div>
                                    <div class="issue-sol">
                                        <strong>Usta Çözümü:</strong> <?php echo esc_html( $issue['solution'] ); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- EKSPERTİZ İPUÇLARI -->
                        <div class="buyer-tips-box">
                            <h4><i class="fa-solid fa-magnifying-glass-chart"></i> Satın Alırken Ekspertizde Mutlaka Baktırılması Gerekenler</h4>
                            <ul class="tips-list">
                                <?php foreach ( $car['buyer_tips'] as $tip ) : ?>
                                    <li><?php echo esc_html( $tip ); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <!-- ALT BİLGİ & USTA BULMA -->
                        <div class="car-footer-actions">
                            <div class="car-summary-quote">
                                "<?php echo esc_html( $car['summary'] ); ?>"
                            </div>
                            <a href="<?php echo esc_url( home_url( '/ustalar/?brand=' . $car['brand_slug'] ) ); ?>" class="btn-brand-mechanics">
                                <span><?php echo esc_html( $car['brand'] ); ?> Uzman Ustalarını Gör</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</div>

<script>
let activeBrand = 'all';

function filterByBrand(brand) {
    activeBrand = brand;
    const buttons = document.querySelectorAll('.brand-pill');
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    filterCars();
}

function filterCars() {
    const query = document.getElementById('chronicSearch').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.car-card');

    cards.forEach(card => {
        const brand = card.getAttribute('data-brand');
        const searchText = card.getAttribute('data-search');

        const matchesBrand = (activeBrand === 'all' || brand === activeBrand);
        const matchesQuery = (query === '' || searchText.includes(query));

        if (matchesBrand && matchesQuery) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

<?php get_footer(); ?>
