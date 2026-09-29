<?php
/*
Template Name: Hizmetlerimiz
*/
get_header(); ?>

<style>
/* ===================================================
   HİZMETLERİMİZ SAYFASI STİLLERİ
=================================================== */
.services-hero {
    background: linear-gradient(135deg, #1a0533 0%, #0d1b4b 35%, #0c2d6b 65%, #0f172a 100%);
    padding: 85px 20px 105px;
    text-align: center;
    color: white;
    position: relative;
    overflow: hidden;
}
.services-hero::before {
    content: '';
    position: absolute;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(139,92,246,0.25) 0%, transparent 70%);
    top: -120px;
    left: -80px;
    border-radius: 50%;
}
.services-hero::after {
    content: '';
    position: absolute;
    width: 450px;
    height: 450px;
    background: radial-gradient(circle, rgba(249,115,22,0.2) 0%, transparent 70%);
    bottom: -100px;
    right: -80px;
    border-radius: 50%;
}
.services-hero .container {
    position: relative;
    z-index: 2;
    max-width: 860px;
    margin: 0 auto;
}
.services-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, rgba(249,115,22,0.2), rgba(139,92,246,0.2));
    color: #fdba74;
    border: 1px solid rgba(249,115,22,0.3);
    padding: 8px 22px;
    border-radius: 50px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 22px;
    backdrop-filter: blur(4px);
}
.services-hero h1 {
    font-size: clamp(25px, 5vw, 46px);
    font-weight: 800;
    line-height: 1.25;
    margin: 0 0 18px;
    letter-spacing: -0.5px;
    word-wrap: break-word;
    overflow-wrap: break-word;
}
.services-hero h1 .hl {
    background: linear-gradient(90deg, #fb923c, #f472b6, #818cf8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.services-hero p {
    font-size: clamp(14px, 2.5vw, 17px);
    color: #cbd5e1;
    line-height: 1.7;
    margin: 0 auto 35px;
    max-width: 650px;
    box-sizing: border-box;
}

/* Hızlı Filtre Arama Kutusu */
.services-filter-box {
    max-width: 520px;
    width: 100%;
    margin: 0 auto;
    position: relative;
    box-sizing: border-box;
}
.services-filter-box input {
    width: 100%;
    box-sizing: border-box;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.22);
    border-radius: 50px;
    padding: 14px 20px 14px 48px;
    color: white;
    font-size: 15px;
    outline: none;
    backdrop-filter: blur(12px);
    transition: all 0.25s ease;
}
.services-filter-box input::placeholder {
    color: #94a3b8;
}
.services-filter-box input:focus {
    background: rgba(255, 255, 255, 0.16);
    border-color: #f97316;
    box-shadow: 0 0 20px rgba(249, 115, 22, 0.35);
}
.services-filter-box i {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 16px;
}

/* ===================================================
   HİZMET KARTLARI BÖLÜMÜ
=================================================== */
.services-section {
    padding: 80px 20px 90px;
    background: #f8fafc;
}
.services-container {
    max-width: 1200px;
    margin: 0 auto;
}
.services-section-head {
    text-align: center;
    margin-bottom: 50px;
}
.services-section-head h2 {
    font-size: 32px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 10px;
}
.services-section-head p {
    font-size: 16px;
    color: #64748b;
    margin: 0;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
}
.service-item-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 20px;
    padding: 32px 28px;
    display: flex;
    flex-direction: column;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    position: relative;
}
.service-item-card:hover {
    border-color: #f97316;
    transform: translateY(-6px);
    box-shadow: 0 16px 36px rgba(14,165,233,0.14);
}

.service-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 22px;
}
.service-icon-wrap {
    width: 64px;
    height: 64px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    color: white;
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    transition: transform 0.25s;
}
.service-item-card:hover .service-icon-wrap {
    transform: scale(1.08);
}
.service-icon-png {
    width: 38px;
    height: 38px;
    object-fit: contain;
    filter: brightness(0) invert(1);
    transition: transform 0.25s ease;
}
.service-item-card:hover .service-icon-png {
    transform: scale(1.1);
}
.service-count-badge {
    background: #f1f5f9;
    color: #475569;
    font-size: 12px;
    font-weight: 700;
    padding: 6px 14px;
    border-radius: 50px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.service-count-badge i {
    color: #f97316;
}

.service-item-card h3 {
    font-size: 21px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 12px;
    line-height: 1.3;
}
.service-item-card p {
    font-size: 14.5px;
    color: #64748b;
    line-height: 1.65;
    margin: 0 0 20px;
    flex-grow: 1;
}

.service-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 24px;
}
.service-tag {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #475569;
    font-size: 12px;
    font-weight: 600;
    padding: 5px 11px;
    border-radius: 8px;
    transition: all 0.2s;
}
.service-item-card:hover .service-tag {
    background: #f0f9ff;
    border-color: #bae6fd;
    color: #ea580c;
}

.service-card-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    color: #0f172a;
    font-size: 14px;
    font-weight: 700;
    padding: 12px 18px;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.25s;
    margin-top: auto;
}
.service-card-btn i {
    color: #f97316;
    transition: transform 0.2s;
}
.service-item-card:hover .service-card-btn {
    background: #f97316;
    border-color: #f97316;
    color: white;
}
.service-item-card:hover .service-card-btn i {
    color: white;
    transform: translateX(4px);
}

/* ===================================================
   ARIZA & BELİRTİ REHBERİ (AKILLI NAVİGATÖR)
=================================================== */
.troubleshoot-section {
    padding: 90px 20px;
    background: #ffffff;
    border-top: 1px solid #e2e8f0;
}
.troubleshoot-container {
    max-width: 1150px;
    margin: 0 auto;
}
.troubleshoot-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-top: 40px;
}
.troubleshoot-card {
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 18px;
    padding: 24px 22px;
    text-decoration: none;
    transition: all 0.25s;
    display: flex;
    flex-direction: column;
}
.troubleshoot-card:hover {
    background: #ffffff;
    border-color: #f97316;
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(249, 115, 22, 0.12);
}
.trouble-symptom {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}
.trouble-symptom-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #fee2e2;
    color: #ef4444;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
    flex-shrink: 0;
}
.trouble-symptom-title {
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.35;
}
.trouble-solution {
    margin-top: auto;
    padding-top: 12px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 13px;
    color: #f97316;
    font-weight: 700;
}

/* ===================================================
   CTA BÖLÜMÜ
=================================================== */
.services-cta-section {
    padding: 80px 20px;
    background: linear-gradient(135deg, #0d1b4b 0%, #1a0533 100%);
    color: white;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.services-cta-section::before {
    content: '';
    position: absolute;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(249, 115, 22, 0.2) 0%, transparent 70%);
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    border-radius: 50%;
}
.s-cta-box {
    position: relative;
    z-index: 2;
    max-width: 760px;
    margin: 0 auto;
}
.s-cta-box h2 {
    font-size: 38px;
    font-weight: 800;
    margin: 0 0 16px;
    letter-spacing: -0.5px;
}
.s-cta-box p {
    font-size: 17px;
    color: #cbd5e1;
    margin: 0 0 32px;
    line-height: 1.7;
}
.s-cta-buttons {
    display: flex;
    justify-content: center;
    gap: 16px;
    flex-wrap: wrap;
}
.s-cta-primary {
    background: linear-gradient(135deg, #f97316, #2563eb);
    color: white;
    padding: 15px 32px;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.25s;
    box-shadow: 0 6px 20px rgba(249, 115, 22, 0.4);
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.s-cta-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(249, 115, 22, 0.55);
}
.s-cta-secondary {
    background: rgba(255,255,255,0.1);
    color: white;
    border: 1px solid rgba(255,255,255,0.25);
    padding: 15px 32px;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 700;
    text-decoration: none;
    backdrop-filter: blur(8px);
    transition: all 0.25s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.s-cta-secondary:hover {
    background: rgba(255,255,255,0.2);
    transform: translateY(-2px);
}

/* ===================================================
   RESPONSIVE
=================================================== */
@media (max-width: 992px) {
    .services-hero { padding: 50px 16px 60px; }
    .services-hero h1 { font-size: 30px; }
    .services-grid { grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .troubleshoot-grid { grid-template-columns: repeat(2, 1fr); gap: 18px; }
    .services-section { padding: 50px 16px; }
    .troubleshoot-section { padding: 50px 16px; }
}
@media (max-width: 768px) {
    .services-hero { padding: 36px 16px 46px; }
    .services-hero h1 { font-size: 24px; line-height: 1.3; }
    .mobile-only-br { display: block !important; }
    .services-hero p { font-size: 13.5px; margin-bottom: 22px; }
    .services-badge { font-size: 11.5px; padding: 6px 14px; margin-bottom: 14px; }
    .services-filter-box { width: 100%; max-width: 100%; }
    .services-filter-box input { font-size: 13px; padding: 11px 16px 11px 40px; width: 100%; }
    .services-section { padding: 40px 16px; }
    .services-section-head { margin-bottom: 26px; }
    .services-section-head h2 { font-size: 20px; }
    .services-section-head p { font-size: 13px; }
    .services-grid { grid-template-columns: 1fr; gap: 16px; }
    .service-item-card { padding: 20px 16px; border-radius: 16px; }
    .troubleshoot-section { padding: 40px 16px; }
    .troubleshoot-grid { grid-template-columns: 1fr; gap: 14px; }
    .s-cta-box h2 { font-size: 20px; }
    .s-cta-box p { font-size: 13.5px; margin-bottom: 18px; }
    .s-cta-buttons { flex-direction: column; gap: 10px; }
    .s-cta-primary, .s-cta-secondary { width: 100%; justify-content: center; padding: 12px 18px; box-sizing: border-box; font-size: 14.5px; }
}
.mobile-only-br { display: none; }
</style>

<!-- HERO BÖLÜMÜ -->
<section class="services-hero">
    <div class="container">
        <div class="services-badge">
            <i class="fa-solid fa-wrench"></i>
            Tüm Uzmanlık Alanları & Servisler
        </div>
        <h1>Aracınız İçin Tüm <br><span class="hl">Tamir & Bakım</span> <br class="mobile-only-br">Hizmetleri</h1>
        <p>Motor revizyonundan oto beyin tamirine, periyodik bakımdan kaporta boyaya kadar aracınızın ihtiyaç duyduğu tüm uzmanlık alanlarında en tecrübeli ustalar burada.</p>

        <!-- Canlı Servis Filtresi -->
        <div class="services-filter-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="serviceSearchInput" placeholder="Hizmet veya uzmanlık alanı ara... (örn: Fren, Klima, Motor)">
        </div>
    </div>
</section>

<!-- HİZMET KARTLARI LİSTESİ -->
<section class="services-section">
    <div class="services-container">
        <div class="services-section-head">
            <h2>Hizmet Kategorileri</h2>
            <p>Aşağıdaki uzmanlık alanlarından ihtiyacınıza uygun olanı seçerek ilgili ustaları hemen inceleyebilirsiniz.</p>
        </div>

        <div class="services-grid" id="servicesGrid">
            <?php
            $services_list = array(
                array(
                    'slug'   => 'mekanik-ustasi',
                    'icon'   => 'fa-gears',
                    'title'  => 'Mekanik Ustaları & Yürür Aksam',
                    'desc'   => 'Aracınızın alt takım, diferansiyel, direksiyon kutusu, mekanik parça onarımları ve genel yürür aksam kontrolleri.',
                    'tags'   => array('Alt Takım', 'Direksiyon Kutusu', 'Mekanik Arıza', 'Bilyalar'),
                    'color'  => 'linear-gradient(135deg, #f97316, #6366f1)',
                ),
                array(
                    'slug'   => 'oto-elektrik-ustalari',
                    'icon'   => 'fa-bolt',
                    'title'  => 'Oto Elektrik & Elektronik',
                    'desc'   => 'Akü testi ve değişimi, marş motoru, şarj dinamosu, aydınlatma grubu, sigorta paneli ve elektrik tesisatı tamiri.',
                    'tags'   => array('Akü Değişimi', 'Marş Motoru', 'Şarj Dinamosu', 'Far & Aydınlatma'),
                    'color'  => 'linear-gradient(135deg, #f59e0b, #ef4444)',
                ),
                array(
                    'slug'   => 'kaporta-ustalari',
                    'icon'   => 'fa-car-burst',
                    'title'  => 'Kaporta, Boya & Göçük',
                    'desc'   => 'Boyasız göçük düzeltme (PDR), fırınlı oto boya, pasta cila, seramik kaplama, tampon tamiri ve şasi doğrultma.',
                    'tags'   => array('Boyasız Göçük', 'Fırın Boya', 'Pasta Cila', 'Tampon Onarımı'),
                    'color'  => 'linear-gradient(135deg, #ec4899, #8b5cf6)',
                ),
                array(
                    'slug'   => 'motor-ustalari',
                    'icon'   => 'fa-oil-can',
                    'title'  => 'Motor Yenileme & Revizyon',
                    'desc'   => 'Komple motor rektifiye, silindir kapağı taşlama, segman atma, triger kayışı/zinciri değişimi ve yağ yakma çözümleri.',
                    'tags'   => array('Komple Rektifiye', 'Triger Değişimi', 'Subap Ayarı', 'Yağ Yakma'),
                    'color'  => 'linear-gradient(135deg, #f97316, #eab308)',
                ),
                array(
                    'slug'   => 'oto-ekspertiz',
                    'icon'   => 'fa-clipboard-check',
                    'title'  => 'Oto Ekspertiz & Muayene',
                    'desc'   => 'Detaylı ekspertiz raporu, dyno motor güç testi, kaporta boya mikron ölçümü, süspansiyon ve yanal kayma testleri.',
                    'tags'   => array('Dyno Testi', 'Kaporta Ekspertiz', 'OBD Diagnostik', 'Full Rapor'),
                    'color'  => 'linear-gradient(135deg, #10b981, #f97316)',
                ),
                array(
                    'slug'   => 'oto-klima-ustalari',
                    'icon'   => 'fa-snowflake',
                    'title'  => 'Oto Klima & Isıtma',
                    'desc'   => 'Klima gazı dolumu (R134a / R1234yf), kompresör tamiri, radyatör temizliği, evaporatör değişimi ve polen filtresi bakımı.',
                    'tags'   => array('Klima Gazı', 'Kompresör Tamiri', 'Kaçak Tespiti', 'Kalorifer Peteği'),
                    'color'  => 'linear-gradient(135deg, #06b6d4, #3b82f6)',
                ),
                array(
                    'slug'   => 'oto-cam-ustalari',
                    'icon'   => 'fa-shield-halved',
                    'title'  => 'Oto Cam & Film Uygulama',
                    'desc'   => 'Ön, arka ve yan oto cam değişimi, taş çatlağı tamiri, kaskodan ücretsiz cam değişimi, garantili Amerikan cam filmi ve cam krikosu onarımı.',
                    'tags'   => array('Cam Değişimi', 'Çatlak Tamiri', 'Cam Filmi', 'Kasko Cam'),
                    'color'  => 'linear-gradient(135deg, #0284c7, #0369a1)',
                ),
                array(
                    'slug'   => 'oto-doseme-ustalari',
                    'icon'   => 'fa-couch',
                    'title'  => 'Oto Döşeme & İç Dizayn',
                    'desc'   => 'Deri ve kumaş oto koltuk döşeme, sarkan tavan tamiri, taban halısı değişimi, deri direksiyon kaplama ve sigara yanığı onarımı.',
                    'tags'   => array('Koltuk Döşeme', 'Tavan Kaplama', 'Deri Direksiyon', 'Sigara Yanığı'),
                    'color'  => 'linear-gradient(135deg, #6366f1, #8b5cf6)',
                ),
                array(
                    'slug'   => 'oto-beyin-ve-beyin-tamiri-ustasi-ecu-cip-tunning-yazilim',
                    'icon'   => 'fa-microchip',
                    'title'  => 'Oto Beyin (ECU) & Yazılım',
                    'desc'   => 'Motor beyin tamiri, immo iptali, gösterge paneli onarımı, chip tuning güç artırımı, EGR/DPF arıza çözümleri.',
                    'tags'   => array('ECU Programlama', 'Chip Tuning', 'Gösterge Paneli', 'DPF / EGR'),
                    'color'  => 'linear-gradient(135deg, #8b5cf6, #ec4899)',
                ),
                array(
                    'slug'   => 'periyodik-bakim-servisleri-yag-filtre-genel-kontrol',
                    'icon'   => 'fa-calendar-check',
                    'title'  => 'Periyodik Bakım & Sıvılar',
                    'desc'   => '10.000 / 15.000 km periyodik yağ ve filtre bakımı, buji değişimi, antifriz değişimi ve 40 nokta güvenlik kontrolü.',
                    'tags'   => array('Motor Yağı', 'Hava & Polen Filtresi', 'Buji Değişimi', 'Antifriz'),
                    'color'  => 'linear-gradient(135deg, #22c55e, #16a34a)',
                ),
                array(
                    'slug'   => 'fren-ve-balata-ustasi',
                    'icon'   => 'fa-hand-paper',
                    'title'  => 'Fren Sistemi & Balata',
                    'desc'   => 'Fren balatası ve disk değişimi, disk tornalama, ABS hidrolik ünitesi arızası, el freni teli ve fren hidrolik sıvısı yenileme.',
                    'tags'   => array('Ön/Arka Balata', 'Fren Diski', 'ABS Sistemi', 'Hidrolik Değişimi'),
                    'color'  => 'linear-gradient(135deg, #ef4444, #f97316)',
                ),
                array(
                    'slug'   => 'rot-balans-ve-on-takim-ustasi',
                    'icon'   => 'fa-rotate',
                    'title'  => 'Rot & Bilgisayarlı Balans',
                    'desc'   => '3D lazerli rot ayarı, dinamik tekerlek balans ayarı, direksiyon titremesi giderme, çekme sorunlarının çözümü.',
                    'tags'   => array('3D Lazer Rot', 'Lastik Balans', 'Direksiyon Titremesi', 'Kamber Açısı'),
                    'color'  => 'linear-gradient(135deg, #f97316, #22c55e)',
                ),
                array(
                    'slug'   => 'aks-ve-suspansiyon-tamircisi',
                    'icon'   => 'fa-arrows-up-down',
                    'title'  => 'Aks & Süspansiyon Sistemleri',
                    'desc'   => 'Amortisör değişimi, helezon yaylar, salıncak burçları, rotil, viraj demir lastiği ve aks kafası onarımları.',
                    'tags'   => array('Amortisör', 'Aks Kafası', 'Salıncak Burcu', 'Rotil & Z Rot'),
                    'color'  => 'linear-gradient(135deg, #f59e0b, #84cc16)',
                ),
                array(
                    'slug'   => 'oto-lastik-ve-jant-ustasi-lastik-oteli-rot-balans',
                    'icon'   => 'fa-circle-dot',
                    'title'  => 'Lastik & Jant Hizmetleri',
                    'desc'   => 'Yazlık/kışlık lastik montajı, lastik tamiri, eğri jant düzeltme, jant boyama ve azot dolum hizmetleri.',
                    'tags'   => array('Lastik Değişimi', 'Jant Düzeltme', 'Lastik Oteli', 'Azot Dolumu'),
                    'color'  => 'linear-gradient(135deg, #64748b, #334155)',
                ),
                array(
                    'slug'   => 'sanziman-ve-guc-aktarma',
                    'icon'   => 'fa-cogs',
                    'title'  => 'Şanzıman & Debriyaj',
                    'desc'   => 'Manuel ve otomatik (DSG, EDC, CVT) şanzıman tamiri, baskı balata seti değişimi, şanzıman yağı ve mekatronik onarımı.',
                    'tags'   => array('Otomatik Şanzıman', 'Baskı Balata', 'Mekatronik Tamiri', 'Şanzıman Yağı'),
                    'color'  => 'linear-gradient(135deg, #a855f7, #6366f1)',
                ),
                array(
                    'slug'   => 'oto-cekici-yol-yardim',
                    'icon'   => 'fa-truck-moving',
                    'title'  => 'Yol Yardım & Oto Kurtarma',
                    'desc'   => '7/24 oto çekici, kaza kurtarma, yol kenarı arıza yardımı, mobil lastik değişimi ve akü takviye hizmetleri.',
                    'tags'   => array('7/24 Çekici', 'Oto Kurtarma', 'Mobil Lastik', 'Akü Takviye'),
                    'color'  => 'linear-gradient(135deg, #0ea5e9, #f97316)',
                ),
            );

            foreach($services_list as $svc):
                $term = get_term_by('slug', $svc['slug'], 'service_type');
                $count = ($term && !is_wp_error($term)) ? $term->count : 0;
                $url = home_url('/ustalar/?service_type=' . $svc['slug']);
            ?>
            <div class="service-item-card" data-title="<?php echo esc_attr(strtolower($svc['title'] . ' ' . implode(' ', $svc['tags']))); ?>">
                <div class="service-card-top">
                    <div class="service-icon-wrap" style="background: <?php echo $svc['color']; ?>;">
                        <img src="<?php echo esc_url( ototamir_get_service_icon( $svc['slug'] ) ); ?>" alt="" aria-hidden="true" class="service-icon-png" width="38" height="38" loading="lazy" decoding="async">
                    </div>
                    <div class="service-count-badge">
                        <i class="fa-solid fa-wrench"></i>
                        <span><?php echo $count > 0 ? $count . ' Usta' : 'Onaylı Ustalar'; ?></span>
                    </div>
                </div>

                <h3><?php echo esc_html($svc['title']); ?></h3>
                <p><?php echo esc_html($svc['desc']); ?></p>

                <div class="service-tags">
                    <?php foreach($svc['tags'] as $tag): ?>
                        <span class="service-tag"><?php echo esc_html($tag); ?></span>
                    <?php endforeach; ?>
                </div>

                <a href="<?php echo esc_url($url); ?>" class="service-card-btn">
                    <span>Ustaları Görüntüle</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ARIZA & BELİRTİ REHBERİ -->
<section class="troubleshoot-section">
    <div class="troubleshoot-container">
        <div class="services-section-head">
            <h2>Arıza & Belirti Rehberi</h2>
            <p>Aracınızda bir sorun var ama hangi ustaya gideceğinizi bilmiyor musunuz? Yaşadığınız belirtiyi seçin, doğru uzmana anında ulaşın.</p>
        </div>

        <div class="troubleshoot-grid">
            <a href="<?php echo esc_url(home_url('/ustalar/?service_type=oto-elektrik-ustalari')); ?>" class="troubleshoot-card">
                <div class="trouble-symptom">
                    <div class="trouble-symptom-icon"><i class="fa-solid fa-car-battery"></i></div>
                    <div class="trouble-symptom-title">Araç marş basmıyor veya zor çalışıyor</div>
                </div>
                <div class="trouble-solution">
                    <span>Önerilen: Oto Elektrik & Akü</span>
                    <i class="fa-solid fa-chevron-right"></i>
                </div>
            </a>

            <a href="<?php echo esc_url(home_url('/ustalar/?service_type=fren-ve-balata-ustasi')); ?>" class="troubleshoot-card">
                <div class="trouble-symptom">
                    <div class="trouble-symptom-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div class="trouble-symptom-title">Frene basınca ses geliyor veya pedal titriyor</div>
                </div>
                <div class="trouble-solution">
                    <span>Önerilen: Fren & Balata Ustaları</span>
                    <i class="fa-solid fa-chevron-right"></i>
                </div>
            </a>

            <a href="<?php echo esc_url(home_url('/ustalar/?service_type=oto-klima-ustalari')); ?>" class="troubleshoot-card">
                <div class="trouble-symptom">
                    <div class="trouble-symptom-icon"><i class="fa-solid fa-fan"></i></div>
                    <div class="trouble-symptom-title">Klima soğutmuyor veya kötü koku üflüyor</div>
                </div>
                <div class="trouble-solution">
                    <span>Önerilen: Oto Klima Ustaları</span>
                    <i class="fa-solid fa-chevron-right"></i>
                </div>
            </a>

            <a href="<?php echo esc_url(home_url('/ustalar/?service_type=motor-ustalari')); ?>" class="troubleshoot-card">
                <div class="trouble-symptom">
                    <div class="trouble-symptom-icon"><i class="fa-solid fa-smog"></i></div>
                    <div class="trouble-symptom-title">Egzozdan mavi/beyaz duman atıyor & çekiş düştü</div>
                </div>
                <div class="trouble-solution">
                    <span>Önerilen: Motor Revizyon Ustaları</span>
                    <i class="fa-solid fa-chevron-right"></i>
                </div>
            </a>

            <a href="<?php echo esc_url(home_url('/ustalar/?service_type=sanziman-ve-guc-aktarma')); ?>" class="troubleshoot-card">
                <div class="trouble-symptom">
                    <div class="trouble-symptom-icon"><i class="fa-solid fa-sliders"></i></div>
                    <div class="trouble-symptom-title">Vites geçişlerinde vuruntu var & kalkışta titriyor</div>
                </div>
                <div class="trouble-solution">
                    <span>Önerilen: Şanzıman & Debriyaj</span>
                    <i class="fa-solid fa-chevron-right"></i>
                </div>
            </a>

            <a href="<?php echo esc_url(home_url('/ustalar/?service_type=rot-balans-ve-on-takim-ustasi')); ?>" class="troubleshoot-card">
                <div class="trouble-symptom">
                    <div class="trouble-symptom-icon"><i class="fa-solid fa-gauge"></i></div>
                    <div class="trouble-symptom-title">Yüksek hızda direksiyon titriyor & araç sağa çekiyor</div>
                </div>
                <div class="trouble-solution">
                    <span>Önerilen: Rot & Balans Ustaları</span>
                    <i class="fa-solid fa-chevron-right"></i>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- ÇAĞRI BÖLÜMÜ (CTA) -->
<section class="services-cta-section">
    <div class="s-cta-box">
        <h2>Aradığınız Hizmeti Bulamadınız mı?</h2>
        <p>Arama formunu kullanarak binlerce onaylı usta arasından şehrinize ve araç markanıza özel usta bulabilir veya dükkanınızı hemen ücretsiz ekleyebilirsiniz.</p>
        <div class="s-cta-buttons">
            <a href="<?php echo esc_url(home_url('/ustalar')); ?>" class="s-cta-primary">
                <i class="fa-solid fa-magnifying-glass"></i> Tüm Ustaları Gör
            </a>
            <a href="<?php echo esc_url(home_url('/usta-ekle')); ?>" class="s-cta-secondary">
                <i class="fa-solid fa-plus"></i> Ücretsiz Usta İlanı Ver
            </a>
        </div>
    </div>
</section>

<!-- Canlı Arama Filtresi JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('serviceSearchInput');
    const cards = document.querySelectorAll('.service-item-card');

    if(searchInput && cards.length > 0) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            cards.forEach(card => {
                const searchData = card.getAttribute('data-title') || '';
                if(query === '' || searchData.includes(query)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});
</script>

<?php get_footer(); ?>







