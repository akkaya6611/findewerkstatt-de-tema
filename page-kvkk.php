<?php
/*
Template Name: KVKK Aydınlatma Metni
*/
get_header(); ?>

<style>.legal-hero {
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
    line-height: 1.8;
    font-size: 15px;
}
.legal-content-wrap ul {
    margin: 10px 0 20px 20px;
}
.legal-alert-box {
    background: #fff7ed;
    border-left: 4px solid #f97316;
    padding: 16px 20px;
    border-radius: 6px;
    margin: 20px 0;
    color: #9a3412;
    line-height: 1.6;
    font-size: 14px;
}
.kvkk-rights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px;
    margin: 20px 0;
}
.kvkk-right-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 16px;
    font-size: 13.5px;
    color: #334155;
    line-height: 1.6;
}
.kvkk-right-item i {
    color: #10b981;
    margin-right: 6px;
}
@media (max-width: 768px) {
    .legal-content-wrap {
        padding: 30px 20px;
        margin-top: -15px;
    }
}
</style>

<section class="legal-hero">
    <div class="container">
        <nav style="font-size: 13px; color: rgba(255,255,255,0.7); display:flex; align-items:center; justify-content:center; gap:8px; margin-bottom:16px;">
            <a href="<?php echo esc_url( home_url('/') ); ?>" style="color: rgba(255,255,255,0.85); text-decoration: none;"><i class="fa-solid fa-house" style="font-size:11px;"></i> Ana Sayfa</a>
            <span>/</span>
            <span style="color: #fb923c; font-weight:700;">KVKK Aydınlatma Metni</span>
        </nav>

        <div class="legal-badge">
            <i class="fa-solid fa-shield-halved"></i> 6698 Sayılı Kanun Uyarınca
        </div>

        <h1 style="font-size: 38px; font-weight: 800; margin: 0 0 12px 0;">KVKK Aydınlatma ve Başvuru Metni</h1>
        <p style="font-size: 16px; color: #cbd5e1; max-width: 650px; margin: 0 auto;">
            Kişisel verilerinizin güvenliği, işlenme amaçları ve yasal haklarınız hakkında bilgilendirme
        </p>
    </div>
</section>

<div class="container">
    <article class="legal-content-wrap">
        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom: 1px solid #f1f5f9; padding-bottom: 18px; margin-bottom: 25px;">
            <span style="font-size: 13px; color: #94a3b8; font-weight: 500;">
                <i class="fa-regular fa-calendar-check" style="color: #f97316;"></i> Son Güncelleme: <strong>Eylül 2026</strong>
            </span>
            <span style="font-size: 13px; color: #ea580c; font-weight: 700; background: #fff7ed; border: 1px solid #ffedd5; padding: 4px 12px; border-radius: 20px;">
                <i class="fa-solid fa-gavel"></i> Yasal Bildirim
            </span>
        </div>

        <h2><i class="fa-solid fa-building-shield"></i> 1. Veri Sorumlusunun Kimliği</h2>
        <p>6698 sayılı Kişisel Verilerin Korunması Kanunu ("KVKK") uyarınca, veri sorumlusu sıfatıyla <strong>OtoTamirciBul</strong> ("Platform" veya "Şirket") olarak, kişisel verilerinizi aşağıda açıklanan amaç ve kapsam dahilinde işlemekteyiz.</p>
        <p><strong>İletişim Bilgilerimiz:</strong><br>
            Web Sitesi: <a href="<?php echo esc_url( home_url('/') ); ?>" style="color:#f97316;"><?php echo esc_html( home_url('/') ); ?></a><br>
            E-Posta: <a href="mailto:kvkk@ototamircibul.com.tr" style="color:#f97316;">kvkk@ototamircibul.com.tr</a> / <a href="mailto:info@ototamircibul.com.tr" style="color:#f97316;">info@ototamircibul.com.tr</a>
        </p>

        <h2><i class="fa-solid fa-list-check"></i> 2. İşlenen Kişisel Veriler ve Hukuki Sebepler</h2>
        <p>
            Kimlik verileri (Ad, Soyad), iletişim verileri (Telefon, E-posta, Adres, İl, İlçe), müşteri işlem verileri (İlan bilgileri, puan ve yorumlar) ve işlem güvenliği verileri (IP adresi, log kayıtları); KVKK'nın 5. maddesinde belirtilen <em>"bir sözleşmenin kurulması veya ifasıyla doğrudan doğruya ilgili olması"</em> ve <em>"veri sorumlusunun meşru menfaatleri"</em> hukuki sebeplerine dayalı olarak işlenmektedir.
        </p>

        <h2><i class="fa-solid fa-user-gear"></i> 3. KVKK 11. Madde Kapsamındaki Haklarınız</h2>
        <p>Veri sahibi olarak Veri Sorumlusu’na başvurarak aşağıdaki haklarınızı kullanabilirsiniz:</p>

        <div class="kvkk-rights-grid">
            <div class="kvkk-right-item"><i class="fa-solid fa-check"></i> Kişisel verilerinizin işlenip işlenmediğini öğrenme,</div>
            <div class="kvkk-right-item"><i class="fa-solid fa-check"></i> İşlenmişse buna ilişkin bilgi talep etme,</div>
            <div class="kvkk-right-item"><i class="fa-solid fa-check"></i> İşlenme amacını ve amacına uygun kullanılıp kullanılmadığını öğrenme,</div>
            <div class="kvkk-right-item"><i class="fa-solid fa-check"></i> Yurt içinde veya yurt dışında aktarıldığı 3. kişileri bilme,</div>
            <div class="kvkk-right-item"><i class="fa-solid fa-check"></i> Eksik veya yanlış işlenmişse düzeltilmesini isteme,</div>
            <div class="kvkk-right-item"><i class="fa-solid fa-check"></i> Kanun şartları çerçevesinde verilerin silinmesini/yok edilmesini talep etme.</div>
        </div>

        <h2><i class="fa-solid fa-paper-plane"></i> 4. Başvuru Yöntemi</h2>
        <p>
            Yukarıda belirtilen haklarınızı kullanmak için kimliğinizi tespit edici belgeler ile birlikte talebinizi açıkça belirten imzalı dilekçenizi <strong>kvkk@ototamircibul.com.tr</strong> e-posta adresine güvenli elektronik imzalı veya kayıtlı e-posta adresinizden iletebilirsiniz. Başvurularınız en geç <strong>30 (otuz) gün</strong> içinde ücretsiz olarak sonuçlandırılacaktır.
        </p>
    </article>
</div>

<?php get_footer(); ?>