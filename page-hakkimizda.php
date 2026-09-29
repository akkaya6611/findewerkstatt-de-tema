<?php
/*
Template Name: Hakkımızda
*/
get_header(); ?>

<style>
/* ===================================================
   HAKKIMIZDA SAYFASI STİLLERİ
=================================================== */
.about-hero {
    background: linear-gradient(135deg, #1a0533 0%, #0d1b4b 35%, #0c2d6b 65%, #0f172a 100%);
    padding: 90px 20px 110px;
    text-align: center;
    color: white;
    position: relative;
    overflow: hidden;
}
.about-hero::before {
    content: '';
    position: absolute;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(139,92,246,0.22) 0%, transparent 70%);
    top: -120px;
    left: -80px;
    border-radius: 50%;
}
.about-hero::after {
    content: '';
    position: absolute;
    width: 450px;
    height: 450px;
    background: radial-gradient(circle, rgba(249,115,22,0.18) 0%, transparent 70%);
    bottom: -100px;
    right: -80px;
    border-radius: 50%;
}
.about-hero .container {
    position: relative;
    z-index: 2;
    max-width: 900px;
    margin: 0 auto;
}
.about-badge {
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
    margin-bottom: 24px;
    backdrop-filter: blur(4px);
}
.about-hero h1 {
    font-size: 48px;
    font-weight: 800;
    line-height: 1.2;
    margin: 0 0 20px;
    letter-spacing: -1px;
}
.about-hero h1 .hl {
    background: linear-gradient(90deg, #fb923c, #f472b6, #818cf8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.about-hero p {
    font-size: 18px;
    color: #cbd5e1;
    line-height: 1.7;
    margin: 0 auto;
    max-width: 680px;
}

/* ===================================================
   İSTATİSTİK BANT
=================================================== */
.about-stats-strip {
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    padding: 30px 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.03);
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
    max-width: 1100px;
    margin: 0 auto;
    text-align: center;
}
.stat-box {
    padding: 10px;
}
.stat-box .number {
    font-size: 36px;
    font-weight: 800;
    background: linear-gradient(135deg, #f97316, #6366f1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    line-height: 1.2;
    margin-bottom: 6px;
}
.stat-box .label {
    font-size: 14px;
    color: #64748b;
    font-weight: 600;
}

/* ===================================================
   HİKAYEMİZ / BİZ KİMİZ
=================================================== */
.about-story-section {
    padding: 90px 20px;
    background: #ffffff;
}
.story-wrapper {
    max-width: 1150px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1.1fr;
    gap: 60px;
    align-items: center;
}
.story-image-wrap {
    position: relative;
}
.story-image-wrap img {
    width: 100%;
    height: 480px;
    object-fit: cover;
    border-radius: 24px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.12);
}
.story-badge-float {
    position: absolute;
    bottom: -24px;
    right: 24px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 18px 24px;
    box-shadow: 0 12px 30px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 14px;
}
.story-badge-float .badge-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    background: linear-gradient(135deg, #10b981, #f97316);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}
.story-badge-float .badge-text h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
}
.story-badge-float .badge-text p {
    margin: 3px 0 0;
    font-size: 13px;
    color: #64748b;
}

.story-content .sub-title {
    font-size: 14px;
    font-weight: 700;
    color: #f97316;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 12px;
    display: inline-block;
}
.story-content h2 {
    font-size: 36px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.25;
    margin: 0 0 20px;
}
.story-content p {
    font-size: 16px;
    color: #475569;
    line-height: 1.8;
    margin-bottom: 20px;
}
.story-checklist {
    list-style: none;
    padding: 0;
    margin: 28px 0 0;
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.story-checklist li {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    font-size: 15px;
    color: #1e293b;
    font-weight: 600;
}
.story-checklist li i {
    color: #10b981;
    font-size: 18px;
    margin-top: 3px;
    flex-shrink: 0;
}

/* ===================================================
   DEĞERLERİMİZ
=================================================== */
.about-values-section {
    padding: 90px 20px;
    background: #f8fafc;
}
.values-container {
    max-width: 1150px;
    margin: 0 auto;
}
.section-center-head {
    text-align: center;
    max-width: 650px;
    margin: 0 auto 60px;
}
.section-center-head .sub-title {
    font-size: 14px;
    font-weight: 700;
    color: #f97316;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 10px;
    display: inline-block;
}
.section-center-head h2 {
    font-size: 36px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 14px;
}
.section-center-head p {
    font-size: 16px;
    color: #64748b;
    line-height: 1.6;
    margin: 0;
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
}
.value-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 20px;
    padding: 36px 30px;
    transition: all 0.3s ease;
}
.value-card:hover {
    border-color: #f97316;
    transform: translateY(-6px);
    box-shadow: 0 16px 32px rgba(249, 115, 22, 0.12);
}
.value-icon {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    color: white;
    margin-bottom: 22px;
}
.value-card h3 {
    font-size: 20px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 12px;
}
.value-card p {
    font-size: 15px;
    color: #64748b;
    line-height: 1.7;
    margin: 0;
}

/* ===================================================
   NASIL ÇALIŞIR?
=================================================== */
.about-how-section {
    padding: 90px 20px;
    background: #ffffff;
}
.how-container {
    max-width: 1150px;
    margin: 0 auto;
}
.how-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 32px;
    position: relative;
}
.how-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 20px;
    padding: 36px 28px;
    position: relative;
    text-align: center;
}
.how-step {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #f97316, #2563eb);
    color: white;
    font-size: 18px;
    font-weight: 800;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    box-shadow: 0 6px 16px rgba(249, 115, 22, 0.35);
}
.how-card h4 {
    font-size: 19px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 12px;
}
.how-card p {
    font-size: 15px;
    color: #64748b;
    line-height: 1.6;
    margin: 0;
}

/* ===================================================
   CTA BÖLÜMÜ
=================================================== */
.about-cta-section {
    padding: 80px 20px;
    background: linear-gradient(135deg, #0d1b4b 0%, #1a0533 100%);
    color: white;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.about-cta-section::before {
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
.cta-box {
    position: relative;
    z-index: 2;
    max-width: 750px;
    margin: 0 auto;
}
.cta-box h2 {
    font-size: 38px;
    font-weight: 800;
    margin: 0 0 18px;
    letter-spacing: -0.5px;
}
.cta-box p {
    font-size: 17px;
    color: #cbd5e1;
    margin: 0 0 32px;
    line-height: 1.7;
}
.cta-buttons {
    display: flex;
    justify-content: center;
    gap: 16px;
    flex-wrap: wrap;
}
.cta-btn-primary {
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
.cta-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(249, 115, 22, 0.55);
}
.cta-btn-secondary {
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
.cta-btn-secondary:hover {
    background: rgba(255,255,255,0.2);
    transform: translateY(-2px);
}

/* ===================================================
   RESPONSIVE
=================================================== */
@media (max-width: 992px) {
    .story-wrapper { grid-template-columns: 1fr; gap: 40px; }
    .story-image-wrap img { height: 360px; }
    .values-grid { grid-template-columns: repeat(2, 1fr); }
    .how-grid { grid-template-columns: 1fr; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 20px; }
}
@media (max-width: 600px) {
    .about-hero h1 { font-size: 32px; }
    .story-content h2 { font-size: 28px; }
    .values-grid { grid-template-columns: 1fr; }
    .stats-grid { grid-template-columns: 1fr; }
    .cta-box h2 { font-size: 28px; }
    .story-badge-float { position: static; margin-top: 16px; }
}
</style>

<!-- HERO BÖLÜMÜ -->
<section class="about-hero">
    <div class="container">
        <div class="about-badge">
            <i class="fa-solid fa-handshake-angle"></i>
            Güvenilir Hizmet, Mutlu Sürücüler
        </div>
        <h1>Türkiye'nin En Kapsamlı <br><span class="hl">Oto Tamirci ve Usta</span> Platformu</h1>
        <p>Aracınız yolda kaldığında ya da bakıma ihtiyaç duyduğunda doğru ustayı aramak artık zor değil. 81 ilde tecrübeli, onaylı ve gerçek müşteri yorumlarıyla puanlanmış ustalar tek bir çatı altında.</p>
    </div>
</section>

<!-- İSTATİSTİK BANTI -->
<section class="about-stats-strip">
    <div class="stats-grid">
        <div class="stat-box">
            <div class="number">81 İl</div>
            <div class="label">Tüm Türkiye Kapsamı</div>
        </div>
        <div class="stat-box">
            <div class="number">2.500+</div>
            <div class="label">Onaylı ve Belgeli Usta</div>
        </div>
        <div class="stat-box">
            <div class="number">10.000+</div>
            <div class="label">Memnun Araç Sahibi</div>
        </div>
        <div class="stat-box">
            <div class="number">4.9 / 5</div>
            <div class="label">Müşteri Memnuniyeti Puanı</div>
        </div>
    </div>
</section>

<!-- HİKAYEMİZ & MİSYONUMUZ -->
<section class="about-story-section">
    <div class="story-wrapper">
        <div class="story-image-wrap">
            <img src="https://images.unsplash.com/photo-1613214149922-f1809c99b414?auto=format&fit=crop&w=900&q=80" alt="Oto Tamir Atölyesi">
            <div class="story-badge-float">
                <div class="badge-icon">
                    <i class="fa-solid fa-certificate"></i>
                </div>
                <div class="badge-text">
                    <h4>%100 Onaylı Profiller</h4>
                    <p>Her usta titizlikle incelenir</p>
                </div>
            </div>
        </div>

        <div class="story-content">
            <span class="sub-title">BİZ KİMİZ?</span>
            <h2>Aracınızı Güvenle Emanet Edebileceğiniz Ustaları Bir Araya Getiriyoruz</h2>
            <p><strong>OtoTamirciBul</strong>, otomotiv tamir sektöründe karşılaşılan şeffaflık eksikliğini gidermek ve araç sahiplerinin doğru ustaya en hızlı şekilde ulaşmasını sağlamak amacıyla yola çıktı.</p>
            <p>Oto mekanikten elektrik sistemlerine, kaporta boyadan motor revizyonuna ve ECU yazılımına kadar aracınızın ihtiyaç duyabileceği her uzmanlık alanında Türkiye'nin dört bir yanındaki sanayi sitelerini parmaklarınızın ucuna getiriyoruz.</p>

            <ul class="story-checklist">
                <li><i class="fa-solid fa-circle-check"></i> <span>Yalnızca admin onayından geçmiş gerçek işletmeler ve ustalar</span></li>
                <li><i class="fa-solid fa-circle-check"></i> <span>Müşteriler tarafından serbestçe yazılan şeffaf yorum ve değerlendirmeler</span></li>
                <li><i class="fa-solid fa-circle-check"></i> <span>İl, ilçe, araç markası ve arıza türüne göre anında nokta atışı filtreleme</span></li>
                <li><i class="fa-solid fa-circle-check"></i> <span>Hiçbir komisyon veya aracı ücreti olmadan doğrudan usta ile iletişim</span></li>
            </ul>
        </div>
    </div>
</section>

<!-- DEĞERLERİMİZ -->
<section class="about-values-section">
    <div class="values-container">
        <div class="section-center-head">
            <span class="sub-title">TEMEL İLKELERİMİZ</span>
            <h2>Bizi Farklı Kılan Değerlerimiz</h2>
            <p>Hem araç sahiplerini hem de işini hakkıyla yapan sanayi ustalarını aynı çatı altında dürüst bir vizyonla buluşturuyoruz.</p>
        </div>

        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon" style="background: linear-gradient(135deg, #f97316, #6366f1);">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h3>Şeffaflık & Güven</h3>
                <p>Manipüle edilemeyen müşteri yorumları, gerçek usta fotoğrafları ve net adres bilgileriyle belirsizliği ortadan kaldırıyoruz.</p>
            </div>

            <div class="value-card">
                <div class="value-icon" style="background: linear-gradient(135deg, #f59e0b, #ef4444);">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <h3>Hız ve Kolay Ulaşım</h3>
                <p>Acil bir arıza anında konumunuza ve araç markanıza en yakın ustaları harita ve telefon destekli olarak saniyeler içinde sunuyoruz.</p>
            </div>

            <div class="value-card">
                <div class="value-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <i class="fa-solid fa-wrench"></i>
                </div>
                <h3>Uzmanlık Odaklılık</h3>
                <p>Her usta her araca bakmaz. Biz markanıza ve arıza türünüze özel uzmanlaşmış dükkanları öne çıkarıyoruz.</p>
            </div>

            <div class="value-card">
                <div class="value-icon" style="background: linear-gradient(135deg, #8b5cf6, #ec4899);">
                    <i class="fa-solid fa-users"></i>
                </div>
                <h3>Sürücü & Usta Dayanışması</h3>
                <p>İşini severek yapan sanayi esnafına dijital dünyada hak ettiği görünürlüğü kazandırıyor, sürücüleri ise mağduriyetten koruyoruz.</p>
            </div>

            <div class="value-card">
                <div class="value-icon" style="background: linear-gradient(135deg, #06b6d4, #3b82f6);">
                    <i class="fa-solid fa-heart"></i>
                </div>
                <h3>Sıfır Komisyon</h3>
                <p>Sürücüler ve ustalar arasında aracılık ücreti veya gizli komisyon almıyoruz. Doğrudan telefon ve konumla iletişim sağlıyorsunuz.</p>
            </div>

            <div class="value-card">
                <div class="value-icon" style="background: linear-gradient(135deg, #f97316, #eab308);">
                    <i class="fa-solid fa-headset"></i>
                </div>
                <h3>Kesintisiz Destek</h3>
                <p>Platformumuz üzerinden hem üyelik hem de ilan süreçlerinizde her zaman yanınızdayız.</p>
            </div>
        </div>
    </div>
</section>

<!-- NASIL ÇALIŞIR? -->
<section class="about-how-section">
    <div class="how-container">
        <div class="section-center-head">
            <span class="sub-title">SÜREÇ</span>
            <h2>Nasıl Çalışır?</h2>
            <p>OtoTamirciBul ile doğru ustaya ulaşmak sadece 3 basit adımdan ibarettir.</p>
        </div>

        <div class="how-grid">
            <div class="how-card">
                <div class="how-step">1</div>
                <h4>İhtiyacınızı Belirleyin</h4>
                <p>Şehrinizi, aracınızın markasını ve ihtiyacınız olan bakım/onarım kategorisini seçerek arama yapın.</p>
            </div>

            <div class="how-card">
                <div class="how-step">2</div>
                <h4>Ustaları İnceleyin</h4>
                <p>Bölgenizdeki ustaların profilini, müşteri değerlendirmelerini, fotoğraflarını ve puanlarını kıyaslayın.</p>
            </div>

            <div class="how-card">
                <div class="how-step">3</div>
                <h4>Doğrudan İletişime Geçin</h4>
                <p>Tek dokunuşla ustayı telefonla arayın ya da navigasyon yardımıyla dükkanına kolayca ulaşın.</p>
            </div>
        </div>
    </div>
</section>

<!-- ÇAĞRI BÖLÜMÜ (CTA) -->
<section class="about-cta-section">
    <div class="cta-box">
        <h2>Aracınız İçin Doğru Ustayı Bulmaya Hazır mısınız?</h2>
        <p>Binlerce sürücünün güvendiği platformda hemen arama yapın veya işletmenizi ücretsiz ekleyerek daha fazla müşteriye ulaşın.</p>
        <div class="cta-buttons">
            <a href="<?php echo esc_url(home_url('/ustalar')); ?>" class="cta-btn-primary">
                <i class="fa-solid fa-magnifying-glass"></i> Usta Ara
            </a>
            <a href="<?php echo esc_url(home_url('/usta-ekle')); ?>" class="cta-btn-secondary">
                <i class="fa-solid fa-plus"></i> Ücretsiz İlan Ver
            </a>
        </div>
    </div>
</section>

<?php get_footer(); ?>
