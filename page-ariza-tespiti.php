<?php
/**
 * Template Name: Yapay Zeka ile Arıza Tespiti
 * Description: Araç arızalarını yapay zeka ile anında teşhis eden ve ilgili uzman ustalara yönlendiren sayfa şablonu.
 */

get_header();
?>

<style>
/* ===================================================
   AI ARIZA TESPİTİ ÖZEL SAYFA TASARIMI
=================================================== */
.ai-diagnosis-page {
    background: #f8fafc;
    min-height: 80vh;
    padding: 30px 0 80px;
}

/* BREADCRUMB */
.ai-page-breadcrumb {
    margin-bottom: 24px;
    font-size: 13.5px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.ai-page-breadcrumb a {
    color: #475569;
    text-decoration: none;
    font-weight: 600;
}
.ai-page-breadcrumb a:hover {
    color: #ea580c;
}
.ai-page-breadcrumb span.current {
    color: #ea580c;
    font-weight: 700;
}

/* HERO BANNER */
.ai-hero-banner {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #311042 100%);
    border-radius: 24px;
    padding: 44px 40px;
    color: white;
    position: relative;
    overflow: hidden;
    margin-bottom: 40px;
    box-shadow: 0 16px 36px rgba(15, 23, 42, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.1);
}
.ai-hero-banner::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(168, 85, 247, 0.25) 0%, rgba(0, 0, 0, 0) 70%);
    pointer-events: none;
}
.ai-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(168, 85, 247, 0.2);
    border: 1px solid rgba(192, 132, 252, 0.4);
    color: #e9d5ff;
    padding: 6px 16px;
    border-radius: 30px;
    font-size: 13.5px;
    font-weight: 700;
    margin-bottom: 18px;
    backdrop-filter: blur(4px);
}
.ai-hero-title {
    font-size: 36px;
    font-weight: 900;
    line-height: 1.25;
    margin: 0 0 16px;
    letter-spacing: -0.5px;
}
.ai-hero-title span {
    background: linear-gradient(135deg, #f97316 0%, #c084fc 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.ai-hero-desc {
    font-size: 16px;
    color: #cbd5e1;
    max-width: 680px;
    line-height: 1.65;
    margin: 0 0 28px;
}

/* 3 ADIMDA NASIL ÇALIŞIR */
.ai-steps-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-top: 25px;
    padding-top: 25px;
    border-top: 1px solid rgba(255, 255, 255, 0.12);
}
.ai-step-card {
    display: flex;
    align-items: flex-start;
    gap: 14px;
}
.ai-step-num {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 15px;
    flex-shrink: 0;
}
.ai-step-info h4 {
    margin: 0 0 4px;
    font-size: 15px;
    font-weight: 700;
    color: white;
}
.ai-step-info p {
    margin: 0;
    font-size: 13px;
    color: #94a3b8;
    line-height: 1.45;
}

/* FORM WRAPPER */
.ai-tool-container {
    background: white;
    border-radius: 20px;
    padding: 36px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
    margin-bottom: 40px;
}

/* BİLGİLENDİRME & SSS KARTLARI */
.ai-info-section {
    margin-top: 50px;
}
.ai-section-heading {
    text-align: center;
    max-width: 620px;
    margin: 0 auto 36px;
}
.ai-section-heading h2 {
    font-size: 26px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 10px;
}
.ai-section-heading p {
    font-size: 15px;
    color: #64748b;
    margin: 0;
}
.ai-features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 40px;
}
.ai-feature-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 28px;
    transition: all 0.2s ease;
}
.ai-feature-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.05);
    border-color: #cbd5e1;
}
.ai-feature-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    margin-bottom: 18px;
}
.ai-feature-icon.orange { background: #fff7ed; color: #ea580c; }
.ai-feature-icon.purple { background: #f5f3ff; color: #7c3aed; }
.ai-feature-icon.green  { background: #f0fdf4; color: #16a34a; }
.ai-feature-card h3 {
    font-size: 18px;
    font-weight: 800;
    margin: 0 0 8px;
    color: #0f172a;
}
.ai-feature-card p {
    font-size: 14px;
    color: #64748b;
    line-height: 1.6;
    margin: 0;
}

@media (max-width: 900px) {
    .ai-steps-grid { grid-template-columns: 1fr; gap: 16px; }
    .ai-features-grid { grid-template-columns: 1fr; }
    .ai-hero-banner { padding: 30px 24px; }
    .ai-hero-title { font-size: 28px; }
    .ai-tool-container { padding: 20px 16px; }
}
</style>

<div class="ai-diagnosis-page">
    <div class="container">

        <!-- BREADCRUMB -->
        <nav class="ai-page-breadcrumb" aria-label="Breadcrumb">
            <a href="<?php echo esc_url( home_url('/') ); ?>"><i class="fa-solid fa-house"></i> Ana Sayfa</a>
            <span><i class="fa-solid fa-chevron-right" style="font-size:11px;"></i></span>
            <?php if ( is_user_logged_in() ) : ?>
                <a href="<?php echo esc_url( home_url('/profil/#ariza-tespiti') ); ?>">Profilim</a>
                <span><i class="fa-solid fa-chevron-right" style="font-size:11px;"></i></span>
            <?php endif; ?>
            <span class="current">Yapay Zeka ile Arıza Tespiti</span>
        </nav>

        <!-- HERO BANNER -->
        <div class="ai-hero-banner">
            <div class="ai-hero-badge">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <span>OTOTAMİR-360 Akıllı Asistan</span>
            </div>
            <h1 class="ai-hero-title">
                Aracınızın Sorununu Anlatın, <br>
                <span>Yapay Zekâ Anında Teşhis Etsin</span>
            </h1>
            <p class="ai-hero-desc">
                Motordan gelen garip bir ses, gösterge panelindeki arıza lambası veya fren titremesi mi var? Aracınızın marka, model ve belirtilerini paylaşın; saniyeler içinde olası nedenleri ve en doğru usta uzmanlığını öğrenin.
            </p>

            <div class="ai-steps-grid">
                <div class="ai-step-card">
                    <div class="ai-step-num">1</div>
                    <div class="ai-step-info">
                        <h4>Araç ve Belirti Girin</h4>
                        <p>Model yılı, yakıt türü ve duyduğunuz ses veya gördüğünüz lambayı yazın.</p>
                    </div>
                </div>
                <div class="ai-step-card">
                    <div class="ai-step-num">2</div>
                    <div class="ai-step-info">
                        <h4>AI Derin Analizi</h4>
                        <p>Otomotiv veritabanı ile eşleştirilerek muhtemel sorunlar listelenir.</p>
                    </div>
                </div>
                <div class="ai-step-card">
                    <div class="ai-step-num">3</div>
                    <div class="ai-step-info">
                        <h4>Doğru Ustayla İletişim</h4>
                        <p>İlgili branştaki en yakın doğrulanmış ustaları inceleyin ve arayın.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- FORM SHORTCODE WRAPPER -->
        <div class="ai-tool-container">
            <?php echo do_shortcode( '[otb_ai_teshis]' ); ?>
        </div>

        <?php 
        // Yapay Zeka Teşhis Sponsorlu Reklam Alanı
        if ( function_exists( 'ototamir_render_ad' ) ) {
            echo ototamir_render_ad( 'single_content' );
        }
        ?>

        <!-- BİLGİLENDİRME & AVANTAJLAR -->
        <div class="ai-info-section">
            <div class="ai-section-heading">
                <h2>Neden Yapay Zeka Destekli Arıza Tespiti?</h2>
                <p>Gereksiz parça değişimlerinin ve zaman kaybının önüne geçin, sanayiye gitmeden önce bilinçli olun.</p>
            </div>

            <div class="ai-features-grid">
                <div class="ai-feature-card">
                    <div class="ai-feature-icon orange">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                    <h3>Gereksiz Masraftan Kaçının</h3>
                    <p>Olası arıza ihtimallerini önceden bilerek, doğru olmayan işlemler veya fahiş fiyatlı parça değişimlerine karşı kendinizi koruyun.</p>
                </div>

                <div class="ai-feature-card">
                    <div class="ai-feature-icon purple">
                        <i class="fa-solid fa-user-gear"></i>
                    </div>
                    <h3>Doğru Usta Branşını Bulun</h3>
                    <p>Arızanız motor mekanik mi, oto elektrik mi yoksa şanzıman kaynaklı mı? AI sistemi sizi tam ihtiyacınız olan branştaki ustalara yönlendirir.</p>
                </div>

                <div class="ai-feature-card">
                    <div class="ai-feature-icon green">
                        <i class="fa-solid fa-shield-heart"></i>
                    </div>
                    <h3>Güvenli ve Hızlı Teşhis</h3>
                    <p>Aracınızı riske atmadan aciliyet seviyesini öğrenin; yolda kalma tehlikesine karşı önceden önlem alın.</p>
                </div>
            </div>
        </div>

    </div>
</div>

<?php get_footer(); ?>
