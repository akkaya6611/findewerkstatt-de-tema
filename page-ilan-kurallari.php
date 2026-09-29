<?php
/*
Template Name: İlan Verme Kuralları
*/
get_header(); ?>

<style>
.legal-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #431407 100%);
    padding: 70px 0 60px 0;
    color: white;
    text-align: center;
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
.legal-content-wrap p, .legal-content-wrap li {
    color: #475569;
    line-height: 1.8;
    font-size: 15px;
}
.rule-card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 18px;
    margin: 25px 0;
}
.rule-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 22px;
}
.rule-card h3 {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 8px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.rule-card p {
    font-size: 13.5px;
    color: #64748b;
    line-height: 1.6;
    margin: 0;
}
@media (max-width: 768px) {
    .legal-content-wrap {
        padding: 30px 20px;
        margin-top: 0;
    }
}
</style>

<section class="legal-hero">
    <div class="container">
        <nav style="font-size: 13px; color: rgba(255,255,255,0.7); display:flex; align-items:center; justify-content:center; gap:8px; margin-bottom:16px;">
            <a href="<?php echo esc_url( home_url('/') ); ?>" style="color: rgba(255,255,255,0.85); text-decoration: none;"><i class="fa-solid fa-house" style="font-size:11px;"></i> Ana Sayfa</a>
            <span>/</span>
            <span style="color: #fb923c; font-weight:600;">İlan Kuralları</span>
        </nav>

        <div class="legal-badge">
            <i class="fa-solid fa-list-check"></i> Usta Rehberi Standartları
        </div>

        <h1 style="font-size: 38px; font-weight: 800; margin: 0 0 12px 0;">İlan Verme Kuralları</h1>
        <p style="font-size: 16px; color: #cbd5e1; max-width: 650px; margin: 0 auto;">
            Sitemizde usta ve servis ilanı yayınlarken uyulması gereken temel kurallar ve moderasyon ilkeleri
        </p>
    </div>
</section>

<div class="container">
    <article class="legal-content-wrap">
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom: 1px solid #f1f5f9; padding-bottom: 18px; margin-bottom: 25px;">
            <span style="font-size: 13px; color: #94a3b8; font-weight: 500;">
                <i class="fa-regular fa-calendar-check" style="color: #f97316;"></i> Son Güncelleme: <strong>Eylül 2026</strong>
            </span>
            <a href="<?php echo esc_url( home_url('/usta-ekle') ); ?>" style="font-size: 13px; color: white; background: #f97316; padding: 6px 16px; border-radius: 20px; text-decoration: none; font-weight: 600;">
                <i class="fa-solid fa-plus"></i> Hemen İlan Ver
            </a>
        </div>

        <p>
            OtoTamirciBul, hem sürücüler için güvenilir bir arama platformu sağlamak hem de dürüst işletmelerin haklarını korumak amacıyla ilanlarda belirli standartlar uygulamaktadır.
        </p>

        <div class="rule-card-grid">
            <div class="rule-card">
                <h3><i class="fa-solid fa-ban" style="color:#ef4444;"></i> Mükerrer İlan Yasağı</h3>
                <p>Aynı ilde ve aynı isimde birden fazla ilan eklenemez. Sistemimiz aynı isim ve şehre ait ikinci bir ilanı otomatik olarak engellemektedir.</p>
            </div>

            <div class="rule-card">
                <h3><i class="fa-solid fa-store" style="color:#f97316;"></i> Gerçek İşletme Adı</h3>
                <p>Firma adı alanına yalnızca resmi tabela veya işletme unvanı yazılmalıdır. Alakasız anahtar kelime spamı içeren ilanlar onaylanmaz.</p>
            </div>

            <div class="rule-card">
                <h3><i class="fa-solid fa-phone-volume" style="color:#10b981;"></i> Doğrulanmış Telefon</h3>
                <p>Müşterilerin doğrudan ulaşabileceği gerçek, aktif bir sabit veya cep telefonu girilmelidir. Sahte numaralı ilanlar silinir.</p>
            </div>

            <div class="rule-card">
                <h3><i class="fa-solid fa-image" style="color:#f59e0b;"></i> Özgün Fotoğraflar</h3>
                <p>Yüklenen kapak fotoğrafları atölyenin, dükkanın veya servis alanının gerçek görüntüsü olmalıdır. Telifli stok görseller tercih edilmemelidir.</p>
            </div>
        </div>

        <h2><i class="fa-solid fa-boxes-packing"></i> Üyelik Paketleri ve İlan Kotaları (Lansman Dönemi)</h2>
        <p>
            OtoTamirciBul usta ve servis kayıtları tanıtım lansmanı boyunca <strong>geçici bir süre için tamamen ÜCRETSİZDİR</strong>. Platformumuzda 3 seviyeli paket modeli planlanmış olup şu an Ücretsiz Paket aktiftir:
        </p>
        <ul>
            <li><strong>🆓 Ücretsiz Paket (0 TL - Şu an Aktif):</strong> 1 firma için 1 adet ilan yayınlama hakkı (90 gün yayında kalma süresi), 1 adet fotoğraf yükleme ve 1 temel hizmet kategorisi seçimi sağlar.</li>
            <li><strong>⭐ Profesyonel Paket (299 TL / ay - Çok Yakında):</strong> 5 adet ilan yayınlama hakkı, 10 adet fotoğraf, 10 hizmet ekleme, 1 adet vitrin ilanı, aramalarda öncelik ve profil doğrulama rozeti.</li>
            <li><strong>👑 Premium Paket (599 TL / ay - Çok Yakında):</strong> 15 adet ilan yayınlama hakkı, 30 fotoğraf + 30 portföy görseli, sınırsız hizmet ekleme, 3 adet vitrin ilanı, ana sayfada görünme, "Önerilen Usta" altın rozeti ve 7/24 öncelikli destek.</li>
        </ul>
        <p>
            Tüm paket detaylarını <a href="<?php echo esc_url(home_url('/paketler')); ?>" style="color:#f97316; font-weight:700;">Paketler ve Fiyatlandırma</a> sayfamızdan inceleyebilirsiniz.
        </p>

        <h2><i class="fa-solid fa-circle-check"></i> Onay ve Moderasyon Süreci</h2>
        <p>
            Sitemiz üzerinden eklenen tüm yeni usta ilanları moderasyon ekibimiz tarafından incelenir. İnceleme esnasında telefon numarasının doğruluğu, adresin geçerliliği ve hizmet kategorisi kontrol edilir. Kurallara uygun ilanlar en geç <strong>24 saat içinde</strong> onaylanarak yayına alınır.
        </p>

        <h2><i class="fa-solid fa-triangle-exclamation"></i> İlanın Askıya Alınması veya Silinmesi</h2>
        <p>
            Kullanıcılardan yoğun şikayet alan, yanıltıcı bilgi verdiği tespit edilen veya kapanan işletmelerin ilanları önceden bildirim yapılmaksızın sistemden kaldırılabilir.
        </p>
    </article>
</div>

<?php get_footer(); ?>