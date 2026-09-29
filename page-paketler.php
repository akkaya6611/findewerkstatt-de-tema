<?php
/*
Template Name: Paketler ve Fiyatlandırma
*/
get_header(); ?>

<style>
.pricing-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #431407 100%);
    padding: 75px 0 65px 0;
    color: white;
    text-align: center;
}
.pricing-launch-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #ef4444, #f97316);
    color: white;
    padding: 7px 20px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 18px;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.35);
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.03); }
}
.pricing-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
    gap: 24px;
    max-width: 1100px;
    margin: -40px auto 60px auto;
    position: relative;
    z-index: 10;
}
.pricing-card {
    background: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 18px;
    padding: 35px 28px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
}
.pricing-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}
.pricing-card.featured {
    border-color: #f97316;
    box-shadow: 0 15px 35px rgba(249, 115, 22, 0.2);
}
.pricing-card.premium {
    border-color: #f59e0b;
    background: linear-gradient(180deg, #fffbeb 0%, #ffffff 25%);
}
.card-badge-top {
    position: absolute;
    top: -14px;
    left: 50%;
    transform: translateX(-50%);
    padding: 5px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
    letter-spacing: 0.5px;
}
.card-badge-top.active {
    background: #10b981;
    color: white;
}
.card-badge-top.soon {
    background: linear-gradient(135deg, #f97316, #ea580c);
    color: white;
}
.card-badge-top.king {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
}
.price-tag {
    font-size: 40px;
    font-weight: 800;
    color: #0f172a;
    margin: 16px 0 6px 0;
}
.price-tag span {
    font-size: 15px;
    font-weight: 500;
    color: #64748b;
}
.pricing-features-list {
    list-style: none;
    padding: 0;
    margin: 24px 0;
    display: flex;
    flex-direction: column;
    gap: 12px;
    flex: 1;
}
.pricing-features-list li {
    font-size: 14px;
    color: #334155;
    display: flex;
    align-items: center;
    gap: 10px;
}
.pricing-features-list li i.check {
    color: #10b981;
    font-size: 15px;
}
.pricing-features-list li i.cross {
    color: #ef4444;
    font-size: 15px;
}
.pricing-btn {
    display: block;
    text-align: center;
    padding: 13px 20px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 15px;
    text-decoration: none;
    transition: all 0.2s ease;
}
.pricing-btn.btn-active {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(249, 115, 22, 0.35);
}
.pricing-btn.btn-active:hover {
    background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
    box-shadow: 0 6px 18px rgba(249, 115, 22, 0.45);
}
.pricing-btn.btn-soon {
    background: #f1f5f9;
    color: #64748b;
    border: 1px solid #cbd5e1;
    cursor: default;
}
.comparison-table-wrap {
    max-width: 1100px;
    margin: 0 auto 80px auto;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow-x: auto;
    box-shadow: 0 10px 30px rgba(0,0,0,0.04);
}
.comp-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
    min-width: 650px;
}
.comp-table th, .comp-table td {
    padding: 14px 20px;
    border-bottom: 1px solid #f1f5f9;
    text-align: center;
}
.comp-table th:first-child, .comp-table td:first-child {
    text-align: left;
    font-weight: 600;
    color: #1e293b;
    width: 40%;
}
.comp-table th {
    background: #f8fafc;
    color: #1e293b;
    font-weight: 700;
    font-size: 15px;
}
.comp-table tr:hover td {
    background: #f8fafc;
}
</style>

<!-- HERO -->
<section class="pricing-hero">
    <div class="container">
        <div class="pricing-launch-badge">
            <i class="fa-solid fa-fire"></i> LANSMANA ÖZEL: GEÇİCİ SÜREYLE %100 ÜCRETSİZ!
        </div>
        <h1 style="font-size: 38px; font-weight: 800; margin: 0 0 12px 0;">Usta & Servis Üyelik Paketleri</h1>
        <p style="font-size: 16px; color: #cbd5e1; max-width: 700px; margin: 0 auto;">
            OtoTamirciBul şu anda lansman süresince tüm ustalarımıza tamamen ücretsizdir. Çok yakında devreye girecek avantajlı paketlerimizle firmanızı zirveye taşıyın!
        </p>
    </div>
</section>

<div class="container">
    
    <!-- 3 PAKET KARTLARI -->
    <div class="pricing-grid">
        
        <!-- 1. ÜCRETSİZ PAKET (AKTİF) -->
        <div class="pricing-card">
            <div class="card-badge-top active">
                <i class="fa-solid fa-circle-check"></i> ŞU AN AKTİF & ÜCRETSİZ
            </div>
            <div style="font-size: 28px; margin-bottom: 6px;">🆓</div>
            <h3 style="font-size: 22px; font-weight: 800; color: #1e293b; margin: 0;">Ücretsiz Paket</h3>
            <p style="font-size: 13px; color: #64748b; margin: 4px 0 0 0;">Lansman süresince geçerli temel üyelik</p>
            
            <div class="price-tag">0 TL <span>/ 90 gün</span></div>

            <ul class="pricing-features-list">
                <li><i class="fa-solid fa-check check"></i> <strong>1 Adet</strong> İlan Yayınlama</li>
                <li><i class="fa-solid fa-check check"></i> <strong>1 Adet</strong> Firma Fotoğrafı</li>
                <li><i class="fa-solid fa-check check"></i> <strong>1 Adet</strong> Hizmet Kategorisi</li>
                <li><i class="fa-solid fa-clock" style="color:#f97316;"></i> <strong>90 Gün</strong> Yayında Kalma Süresi</li>
                <li><i class="fa-solid fa-check check"></i> WhatsApp & Telefon Butonu</li>
                <li><i class="fa-solid fa-xmark cross"></i> Google'da İndekslenme</li>
                <li><i class="fa-solid fa-xmark cross"></i> Google Maps Yol Tarifi</li>
                <li><i class="fa-solid fa-xmark cross"></i> Arama Sonuçlarında Öne Çıkma</li>
                <li><i class="fa-solid fa-xmark cross"></i> Vitrin ve Slider İlanı</li>
                <li><i class="fa-solid fa-xmark cross"></i> Önerilen Usta Rozeti</li>
            </ul>

            <a href="<?php echo esc_url( home_url('/usta-ekle') ); ?>" class="pricing-btn btn-active">
                <i class="fa-solid fa-plus"></i> Şimdi Ücretsiz İlan Ver
            </a>
        </div>

        <!-- 2. PROFESYONEL PAKET (YAKINDA) -->
        <div class="pricing-card featured">
            <div class="card-badge-top soon">
                <i class="fa-solid fa-clock"></i> ÇOK YAKINDA AKTİF
            </div>
            <div style="font-size: 28px; margin-bottom: 6px;">⭐</div>
            <h3 style="font-size: 22px; font-weight: 800; color: #ea580c; margin: 0;">Profesyonel Paket</h3>
            <p style="font-size: 13px; color: #64748b; margin: 4px 0 0 0;">İşlerini büyütmek isteyen servisler için</p>
            
            <div class="price-tag">299 TL <span>/ ay</span></div>

            <ul class="pricing-features-list">
                <li><i class="fa-solid fa-check check"></i> <strong>5 Adet</strong> İlan Yayınlama</li>
                <li><i class="fa-solid fa-check check"></i> <strong>10 Adet</strong> Firma Fotoğrafı</li>
                <li><i class="fa-solid fa-check check"></i> <strong>10 Adet</strong> Hizmet Kategorisi</li>
                <li><i class="fa-solid fa-check check"></i> WhatsApp & Telefon Butonu</li>
                <li><i class="fa-solid fa-check check"></i> Google'da İndekslenme</li>
                <li><i class="fa-solid fa-check check"></i> Google Maps Yol Tarifi</li>
                <li><i class="fa-solid fa-check check"></i> Arama Sonuçlarında Öne Çıkma</li>
                <li><i class="fa-solid fa-check check"></i> Şehir / İlçe Sayfalarında Öncelik</li>
                <li><i class="fa-solid fa-check check"></i> <strong>1 Adet</strong> Vitrin İlanı</li>
                <li><i class="fa-solid fa-xmark cross"></i> “Önerilen Usta” Rozeti</li>
            </ul>

            <button class="pricing-btn btn-soon" disabled>
                <i class="fa-solid fa-lock"></i> Yakında Satışta
            </button>
        </div>

        <!-- 3. PREMIUM PAKET (YAKINDA) -->
        <div class="pricing-card premium">
            <div class="card-badge-top king">
                <i class="fa-solid fa-crown"></i> LİDER PAKET (YAKINDA)
            </div>
            <div style="font-size: 28px; margin-bottom: 6px;">👑</div>
            <h3 style="font-size: 22px; font-weight: 800; color: #d97706; margin: 0;">Premium Paket</h3>
            <p style="font-size: 13px; color: #64748b; margin: 4px 0 0 0;">Bölgesinde lider olmak isteyen ustalar için</p>
            
            <div class="price-tag">599 TL <span>/ ay</span></div>

            <ul class="pricing-features-list">
                <li><i class="fa-solid fa-check check"></i> <strong>15 Adet</strong> İlan Yayınlama</li>
                <li><i class="fa-solid fa-check check"></i> <strong>30 Adet</strong> Firma Fotoğrafı</li>
                <li><i class="fa-solid fa-check check"></i> <strong>Sınırsız</strong> Hizmet Kategorisi</li>
                <li><i class="fa-solid fa-check check"></i> WhatsApp & Telefon Butonu</li>
                <li><i class="fa-solid fa-check check"></i> Google'da Hızlı İndekslenme</li>
                <li><i class="fa-solid fa-check check"></i> Google Maps Yol Tarifi</li>
                <li><i class="fa-solid fa-bolt" style="color:#f59e0b;"></i> En Üst Sırada Listelenme (Roket)</li>
                <li><i class="fa-solid fa-crown" style="color:#f59e0b;"></i> “Önerilen Usta” Rozeti</li>
                <li><i class="fa-solid fa-check check"></i> <strong>3 Adet</strong> Vitrin İlanı</li>
                <li><i class="fa-solid fa-check check"></i> Ana Sayfa Slider Gösterimi</li>
            </ul>

            <button class="pricing-btn btn-soon" disabled>
                <i class="fa-solid fa-lock"></i> Yakında Satışta
            </button>
        </div>

    </div>

    <!-- DETAYLI KARŞILAŞTIRMA TABLOSU -->
    <div style="text-align: center; margin-bottom: 25px;">
        <span style="background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; padding: 4px 14px; border-radius: 20px; font-size: 13px; font-weight: 700;">
            <i class="fa-solid fa-table-columns"></i> AYRINTILI BİLGİ
        </span>
        <h2 style="font-size: 28px; color: #1e293b; margin: 10px 0 4px 0;">Paket Karşılaştırma Tablosu</h2>
        <p style="color: #64748b; font-size: 15px; margin: 0;">Tüm paketlerin sunduğu imkan ve kotaları tek ekranda inceleyin</p>
    </div>

    <div class="comparison-table-wrap">
        <table class="comp-table">
            <thead>
                <tr>
                    <th>Özellik</th>
                    <th>🆓 Ücretsiz</th>
                    <th>⭐ Profesyonel</th>
                    <th>👑 Premium</th>
                </tr>
            </thead>
            <tbody>
                <tr style="background:#f8fafc; font-weight:700;">
                    <td>Fiyat</td>
                    <td style="color:#10b981; font-weight:800;">0 TL</td>
                    <td style="color:#ea580c; font-weight:800;">299 TL / ay</td>
                    <td style="color:#d97706; font-weight:800;">599 TL / ay</td>
                </tr>
                <tr>
                    <td>Firma Profili</td>
                    <td><i class="fa-solid fa-check" style="color:#10b981;"></i></td>
                    <td><i class="fa-solid fa-check" style="color:#10b981;"></i></td>
                    <td><i class="fa-solid fa-check" style="color:#10b981;"></i></td>
                </tr>
                <tr>
                    <td>İlan Hakkı</td>
                    <td>1</td>
                    <td><strong>5</strong></td>
                    <td><strong>15</strong></td>
                </tr>
                <tr>
                    <td>Yayında Kalma Süresi</td>
                    <td><strong style="color:#f97316;">90 Gün</strong></td>
                    <td>Kesintisiz (Aylık)</td>
                    <td><strong style="color:#10b981;">Kesintisiz (Aylık)</strong></td>
                </tr>
                <tr>
                    <td>Firma Fotoğrafları</td>
                    <td>1</td>
                    <td>10</td>
                    <td>30</td>
                </tr>
                <tr>
                    <td>Hizmet Ekleme</td>
                    <td>1</td>
                    <td>10</td>
                    <td><strong style="color:#10b981;">Sınırsız</strong></td>
                </tr>
                <tr>
                    <td>WhatsApp Butonu</td>
                    <td><i class="fa-solid fa-check" style="color:#10b981;"></i></td>
                    <td><i class="fa-solid fa-check" style="color:#10b981;"></i></td>
                    <td><i class="fa-solid fa-check" style="color:#10b981;"></i></td>
                </tr>
                <tr>
                    <td>Telefonla Doğrudan Arama</td>
                    <td><i class="fa-solid fa-check" style="color:#10b981;"></i></td>
                    <td><i class="fa-solid fa-check" style="color:#10b981;"></i></td>
                    <td><i class="fa-solid fa-check" style="color:#10b981;"></i></td>
                </tr>
                <tr>
                    <td>Google'da İndekslenme</td>
                    <td><i class="fa-solid fa-xmark" style="color:#ef4444; font-size:16px;"></i></td>
                    <td><i class="fa-solid fa-check" style="color:#10b981; font-size:16px;"></i></td>
                    <td><i class="fa-solid fa-check" style="color:#10b981; font-size:16px;"></i></td>
                </tr>
                <tr>
                    <td>Google Maps Bağlantısı</td>
                    <td><i class="fa-solid fa-xmark" style="color:#ef4444; font-size:16px;"></i></td>
                    <td><i class="fa-solid fa-check" style="color:#10b981; font-size:16px;"></i></td>
                    <td><i class="fa-solid fa-check" style="color:#10b981; font-size:16px;"></i></td>
                </tr>
                <tr>
                    <td>Müşteri Değerlendirmeleri</td>
                    <td><i class="fa-solid fa-check" style="color:#10b981; font-size:16px;"></i></td>
                    <td><i class="fa-solid fa-check" style="color:#10b981; font-size:16px;"></i></td>
                    <td><i class="fa-solid fa-check" style="color:#10b981; font-size:16px;"></i></td>
                </tr>
                <tr>
                    <td>Arama Sonuçlarında Öne Çıkma</td>
                    <td><i class="fa-solid fa-xmark" style="color:#ef4444; font-size:16px;"></i></td>
                    <td><i class="fa-solid fa-check" style="color:#10b981; font-size:16px;"></i></td>
                    <td>🚀 <strong>Öncelikli</strong></td>
                </tr>
                <tr>
                    <td>Şehir / İlçe Sayfalarında Öncelik</td>
                    <td><i class="fa-solid fa-xmark" style="color:#ef4444; font-size:16px;"></i></td>
                    <td><i class="fa-solid fa-check" style="color:#10b981; font-size:16px;"></i></td>
                    <td>🚀 <strong>En Üst Sıra</strong></td>
                </tr>
                <tr>
                    <td>Vitrin İlanı</td>
                    <td><i class="fa-solid fa-xmark" style="color:#ef4444; font-size:16px;"></i></td>
                    <td>1 Adet</td>
                    <td><strong>3 Adet</strong></td>
                </tr>
                <tr>
                    <td>Ana Sayfada Görünme</td>
                    <td><i class="fa-solid fa-xmark" style="color:#ef4444; font-size:16px;"></i></td>
                    <td><i class="fa-solid fa-xmark" style="color:#ef4444; font-size:16px;"></i></td>
                    <td><i class="fa-solid fa-check" style="color:#10b981; font-size:16px;"></i></td>
                </tr>
                <tr>
                    <td>“Önerilen Usta” Rozeti</td>
                    <td><i class="fa-solid fa-xmark" style="color:#ef4444; font-size:16px;"></i></td>
                    <td><i class="fa-solid fa-xmark" style="color:#ef4444; font-size:16px;"></i></td>
                    <td>👑 <strong>Altın Rozet</strong></td>
                </tr>
                <tr>
                    <td>Portföy / Önce-Sonra Fotoğrafları</td>
                    <td><i class="fa-solid fa-xmark" style="color:#ef4444; font-size:16px;"></i></td>
                    <td>10</td>
                    <td>30</td>
                </tr>
                <tr>
                    <td>Kampanya / İndirim Yayınlama</td>
                    <td><i class="fa-solid fa-xmark" style="color:#ef4444; font-size:16px;"></i></td>
                    <td><i class="fa-solid fa-check" style="color:#10b981; font-size:16px;"></i></td>
                    <td><i class="fa-solid fa-check" style="color:#10b981; font-size:16px;"></i></td>
                </tr>
                <tr>
                    <td>Müşteri Talebi Alma</td>
                    <td><i class="fa-solid fa-xmark" style="color:#ef4444; font-size:16px;"></i></td>
                    <td><i class="fa-solid fa-check" style="color:#10b981; font-size:16px;"></i></td>
                    <td>🚀 <strong>Anında Bildirim</strong></td>
                </tr>
                <tr>
                    <td>İstatistikler</td>
                    <td><i class="fa-solid fa-xmark" style="color:#ef4444; font-size:16px;"></i></td>
                    <td>Temel</td>
                    <td><strong>Gelişmiş</strong></td>
                </tr>
                <tr>
                    <td>Profil Doğrulama Rozeti</td>
                    <td><i class="fa-solid fa-xmark" style="color:#ef4444; font-size:16px;"></i></td>
                    <td><i class="fa-solid fa-check" style="color:#10b981; font-size:16px;"></i></td>
                    <td>👑 <strong>VIP Rozet</strong></td>
                </tr>
                <tr>
                    <td>Öncelikli Destek</td>
                    <td><i class="fa-solid fa-xmark" style="color:#ef4444; font-size:16px;"></i></td>
                    <td>Standart</td>
                    <td><strong style="color:#10b981;">7/24 VIP</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<?php get_footer(); ?>