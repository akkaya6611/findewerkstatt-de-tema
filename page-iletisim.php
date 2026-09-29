<?php
/*
Template Name: İletişim
*/
get_header(); ?>

<style>
/* ===================================================
   İLETİŞİM SAYFASI STİLLERİ
=================================================== */
.contact-hero {
    background: linear-gradient(135deg, #1a0533 0%, #0d1b4b 35%, #0c2d6b 65%, #0f172a 100%);
    padding: 85px 20px 105px;
    text-align: center;
    color: white;
    position: relative;
    overflow: hidden;
}
.contact-hero::before {
    content: '';
    position: absolute;
    width: 500px; height: 500px;
    background: radial-gradient(circle, rgba(249, 115, 22, 0.2) 0%, transparent 70%);
    top: -100px; left: -80px;
    border-radius: 50%;
}
.contact-hero::after {
    content: '';
    position: absolute;
    width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(249,115,22,0.15) 0%, transparent 70%);
    bottom: -80px; right: -60px;
    border-radius: 50%;
}
.contact-hero .hero-inner {
    position: relative;
    z-index: 2;
    max-width: 720px;
    margin: 0 auto;
}
.contact-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.2), rgba(99,102,241,0.2));
    color: #7dd3fc;
    border: 1px solid rgba(249, 115, 22, 0.3);
    padding: 8px 22px;
    border-radius: 50px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 22px;
    backdrop-filter: blur(4px);
}
.contact-hero h1 {
    font-size: 46px;
    font-weight: 800;
    line-height: 1.2;
    margin: 0 0 18px;
    letter-spacing: -1px;
}
.contact-hero h1 .hl {
    background: linear-gradient(90deg, #fb923c, #818cf8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.contact-hero p {
    font-size: 17px;
    color: #cbd5e1;
    line-height: 1.7;
    margin: 0;
}

/* ===================================================
   İLETİŞİM BİLGİLERİ KARTLARI
=================================================== */
.contact-info-section {
    background: #f8fafc;
    padding: 60px 20px 0;
}
.contact-info-grid {
    max-width: 780px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
}
.contact-info-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 20px;
    padding: 32px 24px;
    text-align: center;
    transition: all 0.3s ease;
    box-shadow: 0 4px 16px rgba(0,0,0,0.03);
}
.contact-info-card:hover {
    border-color: #f97316;
    transform: translateY(-5px);
    box-shadow: 0 14px 32px rgba(249, 115, 22, 0.12);
}
.contact-info-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    margin: 0 auto 18px;
}
.contact-info-card h4 {
    font-size: 15px;
    font-weight: 700;
    color: #64748b;
    margin: 0 0 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.contact-info-card p,
.contact-info-card a {
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
    text-decoration: none;
    line-height: 1.5;
    display: block;
}
.contact-info-card a:hover { color: #f97316; }

/* ===================================================
   FORM + HARİTA BÖLÜMÜ
=================================================== */
.contact-main-section {
    background: #f8fafc;
    padding: 60px 20px 90px;
}
.contact-main-container {
    max-width: 1100px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    gap: 36px;
    align-items: start;
}

/* FORM KART */
.contact-form-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 24px;
    padding: 44px;
    box-shadow: 0 6px 24px rgba(0,0,0,0.04);
}
.contact-form-card h2 {
    font-size: 26px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 6px;
}
.contact-form-card > p {
    font-size: 15px;
    color: #64748b;
    margin: 0 0 30px;
}

/* CF7 Form Override */
.contact-form-card .wpcf7 { margin: 0; }
.contact-form-card .wpcf7 label {
    display: block;
    font-size: 14px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 6px;
}
.contact-form-card .wpcf7 input[type="text"],
.contact-form-card .wpcf7 input[type="email"],
.contact-form-card .wpcf7 input[type="tel"],
.contact-form-card .wpcf7 textarea,
.contact-form-card .wpcf7 select {
    width: 100%;
    padding: 13px 16px;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    font-size: 15px;
    font-family: inherit;
    color: #0f172a;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    box-sizing: border-box;
}
.contact-form-card .wpcf7 input:focus,
.contact-form-card .wpcf7 textarea:focus {
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
    background: #ffffff;
}
.contact-form-card .wpcf7 textarea { min-height: 130px; resize: vertical; }
.contact-form-card .wpcf7 input[type="submit"] {
    background: linear-gradient(135deg, #f97316, #2563eb);
    color: white;
    border: none;
    padding: 15px 36px;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    width: 100%;
    margin-top: 6px;
    transition: all 0.25s;
    box-shadow: 0 4px 14px rgba(249, 115, 22, 0.4);
}
.contact-form-card .wpcf7 input[type="submit"]:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(249, 115, 22, 0.6);
}
.contact-form-card .wpcf7-response-output {
    margin-top: 16px;
    padding: 14px 18px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    border: none !important;
}
.contact-form-card .wpcf7-mail-sent-ok {
    background: #f0fdf4;
    color: #15803d;
}
.contact-form-card .wpcf7-mail-sent-ng,
.contact-form-card .wpcf7-validation-errors {
    background: #fff1f2;
    color: #be123c;
}
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.form-group { margin-bottom: 18px; }
.form-group:last-child { margin-bottom: 0; }

/* SAĞ KOLON */
.contact-right-col { display: flex; flex-direction: column; gap: 24px; }

/* HARİTA */
.contact-map-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 6px 24px rgba(0,0,0,0.04);
}
.contact-map-card iframe {
    width: 100%;
    height: 280px;
    display: block;
    border: none;
}
.contact-map-label {
    padding: 18px 24px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
    border-top: 1px solid #f1f5f9;
}
.contact-map-label i { color: #f97316; }

/* ÇALIŞMA SAATLERİ */
.contact-hours-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 24px;
    padding: 28px;
    box-shadow: 0 6px 24px rgba(0,0,0,0.04);
}
.contact-hours-card h3 {
    font-size: 17px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 18px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.contact-hours-card h3 i { color: #f97316; }
.hours-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 9px 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 14.5px;
}
.hours-row:last-child { border-bottom: none; }
.hours-day { color: #475569; font-weight: 600; }
.hours-time { color: #0f172a; font-weight: 700; }
.hours-closed { color: #ef4444; font-weight: 700; }

/* SSS BÖLÜMÜ */
.contact-faq-section {
    background: #ffffff;
    padding: 70px 20px 80px;
    border-top: 1px solid #e2e8f0;
}
.contact-faq-container {
    max-width: 860px;
    margin: 0 auto;
}
.contact-faq-container .section-head {
    text-align: center;
    display: block;
    margin-bottom: 44px;
}
.contact-faq-container .section-head h2 { font-size: 32px; }
.faq-item {
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    margin-bottom: 14px;
    overflow: hidden;
    transition: border-color 0.2s;
}
.faq-item.open { border-color: #f97316; }
.faq-question {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    cursor: pointer;
    background: #ffffff;
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    user-select: none;
}
.faq-question:hover { color: #f97316; }
.faq-question i {
    color: #f97316;
    font-size: 14px;
    transition: transform 0.25s;
    flex-shrink: 0;
    margin-left: 16px;
}
.faq-item.open .faq-question i { transform: rotate(180deg); }
.faq-answer {
    display: none;
    padding: 0 24px 22px;
    font-size: 15px;
    color: #475569;
    line-height: 1.7;
    border-top: 1px solid #f1f5f9;
}
.faq-item.open .faq-answer { display: block; }

/* ===================================================
   RESPONSIVE
=================================================== */
@media (max-width: 992px) {
    .contact-info-grid { grid-template-columns: 1fr; }
    .contact-main-container { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
    .contact-hero h1 { font-size: 30px; }
    .contact-info-grid { grid-template-columns: 1fr; }
    .contact-form-card { padding: 24px; }
    .form-row { grid-template-columns: 1fr; }
}
@media (max-width: 400px) {
    .contact-info-grid { grid-template-columns: 1fr; }
}
</style>

<!-- HERO -->
<section class="contact-hero">
    <div class="hero-inner">
        <div class="contact-badge">
            <i class="fa-solid fa-envelope-open-text"></i>
            Çevrim İçi Destek & İletişim
        </div>
        <h1>Bizimle <span class="hl">İletişime Geçin</span></h1>
        <p>Sorularınız, önerileriniz veya iş ortaklığı teklifleriniz için buradayız.<br>En kısa sürede size geri döneceğiz.</p>
    </div>
</section>

<!-- İLETİŞİM BİLGİLERİ -->
<section class="contact-info-section">
    <div class="contact-info-grid">
        <div class="contact-info-card">
            <div class="contact-info-icon" style="background: linear-gradient(135deg, #8b5cf6, #ec4899);">
                <i class="fa-solid fa-envelope"></i>
            </div>
            <h4>E-Posta</h4>
            <a href="mailto:info@ototamircibul.com.tr">info@ototamircibul.com.tr</a>
            <a href="mailto:destek@ototamircibul.com.tr" style="font-weight:500; color:#64748b; font-size:14px; margin-top:4px;">destek@ototamircibul.com.tr</a>
        </div>
        <div class="contact-info-card">
            <div class="contact-info-icon" style="background: linear-gradient(135deg, #f59e0b, #ef4444);">
                <i class="fa-solid fa-location-dot"></i>
            </div>
            <h4>Adres</h4>
            <p>Kocasinan, KAYSERİ</p>
            <p style="font-weight:500; color:#64748b; font-size:14px; margin-top:4px;">Türkiye</p>
        </div>
    </div>
</section>

<!-- FORM + HARİTA -->
<section class="contact-main-section">
    <div class="contact-main-container">

        <!-- FORM -->
        <div class="contact-form-card">
            <h2>Mesaj Gönderin</h2>
            <p>Aşağıdaki formu doldurmanız yeterli — 24 saat içinde yanıt veririz.</p>

            <?php
            // CF7 Form ID:13 kullan
            if (function_exists('wpcf7_contact_form')) {
                $cf7 = wpcf7_contact_form(13);
                if ($cf7) {
                    echo $cf7->form_html();
                } else {
                    // CF7 yoksa kendi HTML formumuz
                    echo '<p style="color:#ef4444;">Form yüklenemedi.</p>';
                }
            } else {
            ?>
            <!-- Kendi HTML Formumuz (CF7 yoksa fallback) -->
            <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST">
                <input type="hidden" name="action" value="ototamir_contact">
                <?php wp_nonce_field('ototamir_contact_nonce', 'contact_nonce'); ?>
                <div class="form-row">
                    <div class="form-group">
                        <label>Adınız Soyadınız *</label>
                        <input type="text" name="contact_name" placeholder="Adınız Soyadınız" required>
                    </div>
                    <div class="form-group">
                        <label>Telefon</label>
                        <input type="tel" name="contact_phone" placeholder="05XX XXX XX XX">
                    </div>
                </div>
                <div class="form-group">
                    <label>E-Posta Adresiniz *</label>
                    <input type="email" name="contact_email" placeholder="ornek@email.com" required>
                </div>
                <div class="form-group">
                    <label>Konu</label>
                    <input type="text" name="contact_subject" placeholder="Mesaj konunuz">
                </div>
                <div class="form-group">
                    <label>Mesajınız *</label>
                    <textarea name="contact_message" placeholder="Mesajınızı buraya yazın..." required></textarea>
                </div>
                <div class="form-group">
                    <input type="submit" value="📩 Mesajı Gönder">
                </div>
            </form>
            <?php } ?>
        </div>

        <!-- SAĞ KOLON: Harita + Çalışma Saatleri -->
        <div class="contact-right-col">

            <!-- HARİTA -->
            <div class="contact-map-card">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d199144.92131974868!2d35.3168817!3d38.7437877!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x152b11ddc3b28b77%3A0x6b44fb745f66c986!2sKocasinan%2C%20Kayseri!5e0!3m2!1str!2str!4v1700000000000!5m2!1str!2str"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="OtoTamirciBul Kocasinan Kayseri">
                </iframe>
                <div class="contact-map-label">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Kocasinan, KAYSERİ</span>
                </div>
            </div>

            <!-- ÇALIŞMA SAATLERİ -->
            <div class="contact-hours-card">
                <h3><i class="fa-regular fa-clock"></i> Çalışma Saatleri</h3>
                <div class="hours-row">
                    <span class="hours-day">Pazartesi – Cuma</span>
                    <span class="hours-time">09:00 – 18:00</span>
                </div>
                <div class="hours-row">
                    <span class="hours-day">Cumartesi</span>
                    <span class="hours-time">10:00 – 15:00</span>
                </div>
                <div class="hours-row">
                    <span class="hours-day">Pazar</span>
                    <span class="hours-closed">Kapalı</span>
                </div>
                <div class="hours-row">
                    <span class="hours-day">E-Posta Desteği</span>
                    <span class="hours-time">24 Saat İçinde</span>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- SSS BÖLÜMÜ -->
<section class="contact-faq-section">
    <div class="contact-faq-container">
        <div class="section-head">
            <h2>Sık Sorulan Sorular</h2>
            <p>En çok merak edilen konuları yanıtladık</p>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <span>Siteye usta kaydı yapmak ücretli mi?</span>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="faq-answer">Hayır, OtoTamirciBul'a usta kaydı tamamen ücretsizdir. Dükkanınızı veya servisinizi eklemek, fotoğraf yüklemek ve müşterilerden değerlendirme almak için herhangi bir ücret ödemezsiniz.</div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <span>İlanım ne kadar sürede yayına girer?</span>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="faq-answer">İlanınız admin onayından sonra yayına girer. Bu süreç genellikle iş günleri 24 saat içinde tamamlanmaktadır. Onay durumunuzu profil sayfanızdan takip edebilirsiniz.</div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <span>Birden fazla şehirde ilan verebilir miyim?</span>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="faq-answer">Evet, birden fazla şehir ve hizmet türü seçerek ilanınızı daha geniş bir alana yayabilirsiniz. Her şehir için ayrı ayrı kayıt almanıza gerek yoktur.</div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <span>Müşteriler benimle nasıl iletişim kurar?</span>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="faq-answer">Müşteriler ilan sayfanızda belirttiğiniz iletişim kanalları veya e-posta adresiniz üzerinden doğrudan sizinle iletişime geçebilir. Tüm bilgiler ilana girdiğiniz verilerle gösterilir.</div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <span>Yorum veya değerlendirmelere itiraz edebilir miyim?</span>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="faq-answer">Evet, size bırakılan uygunsuz ya da haksız bir yorum için iletişim formumuzdan veya e-posta adresimizden bize ulaşabilirsiniz. İçerik politikamızı ihlal eden yorumları inceleyip gerekli aksiyonu alırız.</div>
        </div>

    </div>
</section>

<!-- SSS Toggle Script -->
<script>
document.querySelectorAll('.faq-question').forEach(function(q) {
    q.addEventListener('click', function() {
        var item = this.closest('.faq-item');
        var isOpen = item.classList.contains('open');
        // Hepsini kapat
        document.querySelectorAll('.faq-item').forEach(function(i) { i.classList.remove('open'); });
        // Kapalıysa aç
        if (!isOpen) { item.classList.add('open'); }
    });
});
</script>

<?php get_footer(); ?>
