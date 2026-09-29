<?php
/*
Template Name: Kullanım Koşulları
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
    font-weight: 700;
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
    font-size: 15px;
    line-height: 1.8;
}
.legal-content-wrap ul {
    padding-left: 20px;
    margin-bottom: 20px;
}
.legal-content-wrap li {
    margin-bottom: 8px;
}
.legal-box-highlight {
    background: #f8fafc;
    border-left: 4px solid #f97316;
    padding: 18px 24px;
    border-radius: 0 8px 8px 0;
    margin: 25px 0;
    font-size: 14.5px;
    color: #334155;
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
            <span style="color: #fb923c; font-weight:700;">Kullanım Koşulları</span>
        </nav>

        <div class="legal-badge">
            <i class="fa-solid fa-scale-balanced"></i> Hukuki & Yasal Sözleşme
        </div>

        <h1 style="font-size: 38px; font-weight: 800; margin: 0 0 12px 0;">Kullanım Koşulları ve Üyelik Şartları</h1>
        <p style="font-size: 16px; color: #cbd5e1; max-width: 650px; margin: 0 auto;">
            OtoTamirciBul platformunu kullanırken uymanız gereken kurallar ve karşılıklı haklarımız
        </p>
    </div>
</section>

<div class="container">
    <div class="legal-content-wrap">
        
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 20px; margin-bottom: 30px; flex-wrap: wrap; gap: 10px;">
            <div style="font-size: 14px; color: #64748b;">
                <i class="fa-regular fa-calendar-check" style="color: #f97316;"></i> Son Güncelleme: <strong>Eylül 2026</strong>
            </div>
            <span style="font-size: 13px; color: #ea580c; font-weight: 700; background: #fff7ed; border: 1px solid #ffedd5; padding: 4px 12px; border-radius: 20px;">
                Sürüm 2.0 (Resmi Yayın)
            </span>
        </div>

        <p>
            Lütfen <strong>ototamircibul.com.tr</strong> web sitesini kullanmadan önce bu Kullanım Koşulları’nı dikkatlice okuyunuz. Siteyi ziyaret eden, kullanan, üye olan veya ilan veren tüm kullanıcılar bu şartları peşinen kabul etmiş sayılır.
        </p>

        <h2><i class="fa-solid fa-landmark"></i> 1. Taraflar ve Hizmetin Tanımı</h2>
        <p>
            <strong>OtoTamirciBul</strong> (“Platform”), Türkiye genelindeki oto tamircileri, oto elektrik, oto servis ve yol yardım hizmeti veren ustalar ile araç sahiplerini bir araya getiren bağımsız bir dijital rehber ve ilan platformudur. Platform, 5651 sayılı Kanun kapsamında <strong>"Yer Sağlayıcı"</strong> ve 6563 sayılı Kanun kapsamında <strong>"Aracı Hizmet Sağlayıcı"</strong> sıfatına sahiptir.
        </p>

        <div class="legal-alert-box">
            <i class="fa-solid fa-shield-halved" style="margin-right: 6px;"></i>
            <strong>Hukuki Sorumluluk Sınırı:</strong> OtoTamirciBul, listelenen ustaların gerçekleştirdiği işçilik, montaj, yedek parça satışı veya fiyatlandırmanın doğrudan tarafı, garantörü ya da kefili değildir. Araç sahibi ile usta arasındaki tüm sözlü/yazılı anlaşmalar tamamen tarafların kendi sorumluluğundadır.
        </div>

        <h2><i class="fa-solid fa-user-check"></i> 2. Kullanıcı ve Üye Yükümlülükleri</h2>
        <ul>
            <li>Kullanıcılar, siteyi yalnızca yasal amaçlarla ve Türkiye Cumhuriyeti kanunlarına uygun şekilde kullanacağını kabul eder.</li>
            <li>İlan veren ustalar; paylaştıkları işletme adı, adres, telefon ve yetki belgelerinin doğruluğunu ve güncelliğini taahhüt eder.</li>
            <li>Başkasına ait marka, logo, fotoğraf veya tescilli isimlerin izinsiz kullanılması kesinlikle yasaktır ve tespiti halinde ilgili ilan derhal silinir.</li>
            <li>Site altyapısına zarar verebilecek bot, spam, crawler veya izinsiz veri çekme (scraping) faaliyetleri yasaktır.</li>
        </ul>

        <h2><i class="fa-solid fa-comment-dots"></i> 3. Yorum ve Değerlendirme Kuralları</h2>
        <p>
            Sitede yer alan usta profillerine yapılan yorumlar genel ahlaka, kamu düzenine ve kişilik haklarına saygılı olmalıdır. Hakaret, küfür, asılsız iftira veya haksız rekabet teşkil eden yorumlar onaylanmaz ve moderatörler tarafından derhal silinir.
        </p>

        <h2><i class="fa-solid fa-copyright"></i> 4. Fikri Mülkiyet Hakları</h2>
        <p>
            OtoTamirciBul web sitesinde yer alan tasarım, yazılım kodları, logo, veritabanı yapısı ve özgün metin içeriklerinin tüm hakları saklıdır. Yazılı izin alınmaksızın kopyalanamaz veya başka mecralarda ticari amaçla kullanılamaz.
        </p>

        <h2><i class="fa-solid fa-tags"></i> 5. Ücretlendirme, Paketler ve Lansman Dönemi Şartları</h2>
        <p>
            OtoTamirciBul, lansman ve tanıtım süresince usta ve servis üyeliklerini <strong>geçici bir süreyle tamamen ücretsiz</strong> olarak sunma hakkına sahiptir. İlerleyen dönemlerde devreye alınması planlanan ücretli üyelik modelleri (Ücretsiz Paket, Profesyonel Paket - 299 TL/ay, Premium Paket - 599 TL/ay), vitrin reklamları ve ek hizmetler için geçerli koşullar kullanıcılara önceden duyurulacaktır. Ücretli paketlerin devreye girmesi, lansman döneminde eklenmiş temel ücretsiz ilanların yayından kaldırılması sonucunu doğurmaz.
        </p>

        <h2><i class="fa-solid fa-scale-balanced"></i> 6. Uyuşmazlıkların Çözümü</h2>
        <p>
            İşbu Kullanım Koşulları'nın uygulanmasından ve yorumlanmasından doğabilecek her türlü ihtilafta Türk Hukuku uygulanacak olup, <strong>Kayseri Mahkemeleri ve İcra Daireleri</strong> yetkilidir.
        </p>
    </article>
</div>

<?php get_footer(); ?>