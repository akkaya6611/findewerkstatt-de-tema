<?php
/*
Template Name: Gizlilik Politikası
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
.legal-content-wrap ul {
    margin: 10px 0 20px 20px;
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
            <span style="color: #fb923c; font-weight:600;">Gizlilik Politikası</span>
        </nav>

        <div class="legal-badge">
            <i class="fa-solid fa-user-shield"></i> Veri Güvenliği & Gizlilik
        </div>

        <h1 style="font-size: 38px; font-weight: 800; margin: 0 0 12px 0;">Gizlilik Politikası</h1>
        <p style="font-size: 16px; color: #cbd5e1; max-width: 650px; margin: 0 auto;">
            Kişisel verilerinizin toplanması, korunması ve işlenmesine ilişkin ilkelerimiz
        </p>
    </div>
</section>

<div class="container">
    <article class="legal-content-wrap">
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom: 1px solid #f1f5f9; padding-bottom: 18px; margin-bottom: 25px;">
            <span style="font-size: 13px; color: #94a3b8; font-weight: 500;">
                <i class="fa-regular fa-calendar-check" style="color: #f97316;"></i> Son Güncelleme: <strong>Eylül 2026</strong>
            </span>
            <span style="font-size: 13px; color: #10b981; font-weight: 600; background: #ecfdf5; padding: 4px 12px; border-radius: 20px;">
                <i class="fa-solid fa-lock"></i> SSL & Güvenli Altyapı
            </span>
        </div>

        <p>
            <strong>OtoTamirciBul</strong> (“ototamircibul.com.tr”), kullanıcılarımızın gizlilik haklarına saygı duymakta ve sitemiz üzerinden sağlanan kişisel verilerin gizliliğini korumayı en üst düzey ilke olarak kabul etmektedir.
        </p>

        <h2><i class="fa-solid fa-database"></i> 1. Toplanan Bilgiler ve Toplanma Yöntemi</h2>
        <p>Platformumuz üzerinden aşağıdaki yöntemlerle veri toplanmaktadır:</p>
        <ul>
            <li><strong>Doğrudan Tarafınızca Sağlanan Bilgiler:</strong> Usta ekleme formu, kullanıcı kayıt formu ve iletişim formunu doldururken girdiğiniz ad-soyad, işletme unvanı, telefon numarası, e-posta adresi, il/ilçe ve işletme adresi.</li>
            <li><strong>Otomatik Olarak Toplanan Bilgiler:</strong> Web sitemizi ziyaretiniz sırasında cihaz türünüz, IP adresiniz, tarayıcı bilgileriniz, ziyaret ettiğiniz sayfalar ve görüntüleme istatistikleri.</li>
        </ul>

        <h2><i class="fa-solid fa-bullseye"></i> 2. Kişisel Verilerin Kullanım Amaçları</h2>
        <p>Toplanan kişisel verileriniz yalnızca aşağıdaki amaçlar doğrultusunda kullanılır:</p>
        <ul>
            <li>Oto tamirci ve servis arayan sürücüler ile ustaların iletişim kurmasını sağlamak,</li>
            <li>Usta profillerinin doğruluğunu teyit etmek ve sahte ilanları engellemek,</li>
            <li>Kullanıcı geri bildirimlerine yanıt vermek ve teknik destek sağlamak,</li>
            <li>Sitemizin arama ve filtreleme performansını geliştirmek,</li>
            <li>Yasal mercilerce talep edilmesi durumunda adli ve idari yükümlülükleri yerine getirmek.</li>
        </ul>

        <h2><i class="fa-solid fa-share-nodes"></i> 3. Bilgilerin Üçüncü Taraflarla Paylaşımı</h2>
        <p>
            OtoTamirciBul, kişisel verilerinizi <strong>asla üçüncü şahıslara veya kurumlara satmaz, kiralamaz ya da ticari amaçla devretmez</strong>. Usta tarafından ilan formunda paylaşılan işletme adı, telefon ve adres bilgileri yalnızca platform üzerinde sürücülerin ustaya ulaşabilmesi amacıyla kamuya açık profil sayfasında yayınlanır.
        </p>

        <h2><i class="fa-solid fa-rectangle-ad"></i> 4. Google AdSense, Reklam Çerezleri ve Üçüncü Taraflar</h2>
        <p>
            Platformumuzda gelir elde etmek ve hizmetlerimizi ücretsiz sürdürebilmek amacıyla <strong>Google AdSense</strong> gibi üçüncü taraf reklam ağları kullanılmaktadır. Bu kapsamda:
        </p>
        <ul>
            <li>Google dahil olmak üzere üçüncü taraf tedarikçiler, kullanıcıların web sitemize veya internetteki diğer sitelere daha önce gerçekleştirdikleri ziyaretlere dayalı olarak reklam sunmak için çerezler (DoubleClick DART vb.) kullanır.</li>
            <li>Google'ın reklam çerezlerini kullanması, kullanıcılara sitemize ve internet üzerindeki diğer sitelere yaptıkları ziyaretlere göre ilgi alanlarına özel reklamlar sunmasını mümkün kılar.</li>
            <li>Kullanıcılarımız, diledikleri zaman <a href="https://myadcenter.google.com/" target="_blank" rel="noopener nofollow" style="color:#f97316; font-weight:600;">Google Reklam Merkezi (My Ad Center)</a> sayfasını ziyaret ederek kişiselleştirilmiş reklamları devre dışı bırakabilir veya <a href="https://www.aboutads.info/choices/" target="_blank" rel="noopener nofollow" style="color:#f97316; font-weight:600;">aboutads.info</a> adresinden üçüncü taraf çerez tercihlerini yönetebilirler.</li>
        </ul>

        <h2><i class="fa-solid fa-shield-virus"></i> 5. Veri Güvenliği Önlemleri</h2>
        <p>
            Kişisel verileriniz, uluslararası güvenlik standartlarına sahip sunucularda ve 256-bit SSL şifreleme protokolleri altında korunmaktadır. Yetkisiz erişim, veri kaybı veya kötüye kullanıma karşı düzenli güvenlik denetimleri ve güncellemeler uygulanmaktadır.
        </p>

        <h2><i class="fa-solid fa-envelope-open-text"></i> 6. İletişim ve Veri Güncelleme</h2>
        <p>
            Gizlilik Politikamız hakkında sorularınız varsa veya sistemimizde kayıtlı bilgilerinizi güncellemek/silmek isterseniz <a href="<?php echo esc_url( home_url('/iletisim') ); ?>" style="color:#f97316; font-weight:600; text-decoration:none;">İletişim</a> sayfamızdan veya <strong>destek@ototamircibul.com.tr</strong> adresinden bize ulaşabilirsiniz.
        </p>
    </article>
</div>

<?php get_footer(); ?>