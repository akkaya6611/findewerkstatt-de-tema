<?php
/*
Template Name: Güvenilir Yorumlar
Description: Metodoloji sayfası – güvenilir yorumların oluşturulma ve denetleme süreci.
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Prevent direct access.
}

get_header();
?>
<main id="primary" class="site-main">
    <section class="methodology">
        <h1>Güvenilir Yorumlar Metodolojisi</h1>
        <p>Bu sayfa, ototamircibul.com.tr sitesinde yer alan kullanıcı yorumlarının güvenilirliğini sağlamak amacıyla kullanılan metodolojiyi açıklamaktadır.</p>
        <h2>1. Veri Toplama</h2>
        <p>Kullanıcıların hizmet deneyimleri, site üzerinden otomatik olarak toplanır. Yorumların tarih, hizmet türü ve mekanik bilgileri veritabanına kaydedilir.</p>
        <h2>2. Doğrulama</h2>
        <ul>
            <li>IP adresi ve oturum kontrolü ile sahte hesaplar engellenir.</li>
            <li>Yorumlar, ilgili mekanik kaydıyla eşleştirilerek servis sağlayıcı tarafından onaylanır.</li>
            <li>İçerik otomatik olarak belirli anahtar kelimeler (ör. "kötü", "spam") için taranır.</li>
        </ul>
        <h2>3. Moderasyon</h2>
        <p>Manuel moderatörler, şüpheli yorumları inceler ve gerekirse yayından kaldırır. Moderatör eylemleri loglanır.</p>
        <h2>4. Şeffaflık</h2>
        <p>Her yorumun altında "Doğrulandı", "Beklemede" veya "Reddedildi" etiketleri gösterilir. Kullanıcılar, yorumlarını güncelleyebilir.</p>
        <h2>5. Performans ve Önbellek</h2>
        <p>Yorum listeleri ve istatistikleri, WordPress transients ve dosya‑tabanlı object‑cache (object‑cache.php) ile önbelleğe alınır.</p>
        <h2>6. Güncelleme ve Denetim</h2>
        <p>Metodoloji yılda bir kez gözden geçirilir ve gerekirse yeni güvenlik önlemleri eklenir.</p>
    </section>
</main>
<?php
get_footer();
?>
