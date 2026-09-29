<?php
/*
Template Name: Çerez Politikası
*/
get_header(); ?>

<style>
.legal-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #431407 100%);
    padding: 70px 0 60px 0;
    color: white;
    text-align: center;
    position: relative;
}
.legal-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(249, 115, 22, 0.15);
    border: 1px solid rgba(249, 115, 22, 0.35);
    color: #fb923c;
    padding: 6px 18px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 16px;
}
.legal-content-wrap {
    max-width: 900px;
    margin: -30px auto 80px auto;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 50px 60px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.04);
    position: relative;
    z-index: 10;
}
.legal-content-wrap h2 {
    color: #0f172a;
    font-size: 22px;
    font-weight: 700;
    margin: 35px 0 14px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.legal-content-wrap h2 i {
    color: #f97316;
    font-size: 20px;
}
.legal-content-wrap p {
    color: #475569;
    line-height: 1.8;
    font-size: 15px;
    margin-bottom: 16px;
}
.legal-content-wrap ul {
    margin: 10px 0 20px 20px;
    color: #475569;
    line-height: 1.8;
    font-size: 15px;
}
.cookie-type-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 18px;
    margin: 25px 0;
}
.cookie-type-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 22px;
}
.cookie-type-card h3 {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 8px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.cookie-type-card p {
    font-size: 13.5px;
    color: #64748b;
    line-height: 1.6;
    margin: 0;
}
.browser-guide-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    font-size: 14.5px;
}
.browser-guide-table th, .browser-guide-table td {
    padding: 12px 16px;
    border: 1px solid #e2e8f0;
    text-align: left;
}
.browser-guide-table th {
    background: #f1f5f9;
    color: #1e293b;
    font-weight: 700;
}
@media (max-width: 768px) {
    .legal-content-wrap {
        padding: 30px 20px;
        margin-top: 0;
    }
}
</style>

<!-- ======================== HERO ======================== -->
<section class="legal-hero">
    <div class="container">
        <!-- Breadcrumbs -->
        <nav style="font-size: 13px; color: rgba(255,255,255,0.7); display:flex; align-items:center; justify-content:center; gap:8px; margin-bottom:16px;">
            <a href="<?php echo esc_url( home_url('/') ); ?>" style="color: rgba(255,255,255,0.85); text-decoration: none;"><i class="fa-solid fa-house" style="font-size:11px;"></i> Ana Sayfa</a>
            <span>/</span>
            <span style="color: #fb923c; font-weight:600;">Çerez Politikası</span>
        </nav>

        <div class="legal-badge">
            <i class="fa-solid fa-cookie-bite"></i> KVKK & Gizlilik Düzenlemeleri
        </div>

        <h1 style="font-size: 38px; font-weight: 800; margin: 0 0 12px 0;">Çerez (Cookie) Politikası</h1>
        <p style="font-size: 16px; color: #cbd5e1; max-width: 650px; margin: 0 auto;">
            OtoTamirciBul olarak ziyaretçilerimizin kişisel verilerinin güvenliğine ve gizliliğine azami önem veriyoruz.
        </p>
    </div>
</section>

<!-- ======================== İÇERİK ======================== -->
<div class="container">
    <article class="legal-content-wrap">
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom: 1px solid #f1f5f9; padding-bottom: 18px; margin-bottom: 25px;">
            <span style="font-size: 13px; color: #94a3b8; font-weight: 500;">
                <i class="fa-regular fa-calendar-check" style="color: #f97316;"></i> Son Güncelleme: <strong>Eylül 2026</strong>
            </span>
            <span style="font-size: 13px; color: #10b981; font-weight: 600; background: #ecfdf5; padding: 4px 12px; border-radius: 20px;">
                <i class="fa-solid fa-shield-halved"></i> 6698 Sayılı KVKK Uyumlu
            </span>
        </div>

        <p>
            Bu Çerez Politikası; <strong>OtoTamirciBul</strong> (“Şirket”, “Biz”, “Platform”) tarafından işletilen web sitesini (<strong>ototamircibul.com.tr</strong>) ziyaret eden tüm kullanıcılarımızın, sitemizi kullanırken cihazlarına yerleştirilen çerezler hakkında bilgilendirilmesi amacıyla hazırlanmıştır.
        </p>

        <h2><i class="fa-solid fa-circle-question"></i> 1. Çerez (Cookie) Nedir?</h2>
        <p>
            Çerezler (cookies), bir internet sitesini ziyaret ettiğinizde bilgisayarınız, tabletiniz veya akıllı telefonunuz gibi internet erişimi olan cihazlarınıza kaydedilen küçük metin dosyalarıdır. Çerezler, web sitemizin düzgün ve güvenli şekilde çalışmasını, tercihlerinizi hatırlamasını ve size daha iyi, hızlı ve kişiselleştirilmiş bir deneyim sunmasını sağlar.
        </p>

        <h2><i class="fa-solid fa-layer-group"></i> 2. Web Sitemizde Kullanılan Çerez Türleri</h2>
        <p>
            Web sitemizde kullanım amacına ve süresine göre farklı kategorilerde çerezler kullanılmaktadır:
        </p>

        <div class="cookie-type-grid">
            <div class="cookie-type-card">
                <h3><i class="fa-solid fa-lock" style="color:#f97316;"></i> Zorunlu Çerezler</h3>
                <p>
                    Web sitesinin temel fonksiyonlarının çalışması için zorunludur. Oturum açma, form güvenliği ve sayfa geçişlerinin sağlanması bu çerezlerle gerçekleştirilir. Kapatılamazlar.
                </p>
            </div>

            <div class="cookie-type-card">
                <h3><i class="fa-solid fa-sliders" style="color:#8b5cf6;"></i> İşlevsel Çerezler</h3>
                <p>
                    Kullanıcı tercihlerini (örneğin seçilen şehir, ilçe filtreleri ve favorilere eklenen ustalar) hatırlayarak bir sonraki ziyaretinizde deneyiminizi kolaylaştırır.
                </p>
            </div>

            <div class="cookie-type-card">
                <h3><i class="fa-solid fa-chart-line" style="color:#10b981;"></i> Performans & Analiz</h3>
                <p>
                    Sayfa ziyaret sıklığı, en çok aranan hizmetler ve hata sayfaları gibi anonim istatistiki verileri toplayarak site performansını geliştirmemize yardımcı olur.
                </p>
            </div>

            <div class="cookie-type-card">
                <h3><i class="fa-solid fa-bullhorn" style="color:#f59e0b;"></i> Hedefleme & Reklam (Google AdSense)</h3>
                <p>
                    Google AdSense ve iş ortakları tarafından kullanılan üçüncü taraf çerezlerdir (DoubleClick DART vb.). Sitemizi veya diğer siteleri daha önce ziyaretlerinize dayalı olarak ilgi alanlarınıza uygun reklamlar sunar.
                </p>
            </div>
        </div>

        <div style="background:#fffbeb; border:1px solid #fef3c7; border-left:4px solid #f59e0b; border-radius:8px; padding:16px 20px; margin:24px 0;">
            <strong style="color:#92400e; font-size:14px; display:block; margin-bottom:6px;"><i class="fa-solid fa-circle-info"></i> Google AdSense Reklam Kişiselleştirme Tercihleri</strong>
            <p style="margin:0; font-size:13.5px; color:#78350f; line-height:1.6;">
                Google'ın reklam çerezlerini kullanarak kişiselleştirilmiş reklam göstermesini istemiyorsanız, <a href="https://myadcenter.google.com/" target="_blank" rel="noopener nofollow" style="color:#d97706; font-weight:700; text-decoration:underline;">Google Reklam Merkezi</a> sayfasından kişiselleştirmeyi dilediğiniz zaman kapatabilirsiniz.
            </p>
        </div>

        <h2><i class="fa-solid fa-hourglass-half"></i> 3. Çerezlerin Saklanma Süreleri</h2>
        <ul>
            <li><strong>Oturum Çerezleri (Session Cookies):</strong> Tarayıcınızı kapattığınız anda otomatik olarak silinen geçici çerezlerdir.</li>
            <li><strong>Kalıcı Çerezler (Persistent Cookies):</strong> Belirli bir son kullanım tarihine kadar veya siz tarayıcınızdan silene kadar cihazınızda saklanan çerezlerdir.</li>
        </ul>

        <h2><i class="fa-solid fa-gear"></i> 4. Çerezleri Nasıl Kontrol Edebilir veya Silebilirsiniz?</h2>
        <p>
            İnternet tarayıcınızın ayarlarını değiştirerek çerez tercihlerinizi dilediğiniz zaman yönetebilir, mevcut çerezleri silebilir veya yeni çerez kaydedilmesini engelleyebilirsiniz. Çerezleri devre dışı bırakmanız durumunda web sitemizin bazı fonksiyonlarının (örneğin favorilere ekleme veya oturum açma) tam verimle çalışmayabileceğini hatırlatmak isteriz.
        </p>

        <table class="browser-guide-table">
            <thead>
                <tr>
                    <th>Tarayıcı</th>
                    <th>Çerez Ayarlarına Ulaşım Yolu</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Google Chrome</strong></td>
                    <td>Ayarlar > Gizlilik ve Güvenlik > Çerezler ve Diğer Site Verileri</td>
                </tr>
                <tr>
                    <td><strong>Mozilla Firefox</strong></td>
                    <td>Seçenekler > Gizlilik ve Güvenlik > Çerezler ve Site Verileri</td>
                </tr>
                <tr>
                    <td><strong>Apple Safari</strong></td>
                    <td>Tercihler > Gizlilik > Tüm Çerezleri Engelle / Çerezleri Yönet</td>
                </tr>
                <tr>
                    <td><strong>Microsoft Edge</strong></td>
                    <td>Ayarlar > Site İzinleri > Çerezler ve Site Verileri</td>
                </tr>
            </tbody>
        </table>

        <h2><i class="fa-solid fa-scale-balanced"></i> 5. KVKK Kapsamındaki Haklarınız</h2>
        <p>
            6698 sayılı Kişisel Verilerin Korunması Kanunu’nun 11. maddesi kapsamında, kişisel verilerinizin işlenip işlenmediğini öğrenme, işlenmişse buna ilişkin bilgi talep etme, işlenme amacına uygun kullanılıp kullanılmadığını öğrenme ve silinmesini isteme hakkına sahipsiniz.
        </p>

        <h2><i class="fa-solid fa-envelope"></i> 6. Bizimle İletişime Geçin</h2>
        <p>
            Çerez Politikamız veya kişisel verilerinizin işlenmesiyle ilgili tüm sorularınız için <a href="<?php echo esc_url( home_url('/iletisim') ); ?>" style="color:#f97316; font-weight:600; text-decoration:none;">İletişim Sayfamız</a> üzerinden veya <strong>destek@ototamircibul.com.tr</strong> e-posta adresinden bizimle irtibata geçebilirsiniz.
        </p>
    </article>
</div>

<?php get_footer(); ?>