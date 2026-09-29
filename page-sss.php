<?php
/*
Template Name: SSS (Sıkça Sorulan Sorular)
*/
get_header(); ?>

<style>
.faq-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #431407 100%);
    padding: 70px 0 60px 0;
    color: white;
    text-align: center;
}
.faq-badge {
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
.faq-container {
    max-width: 900px;
    margin: -30px auto 80px auto;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 40px 50px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.04);
    position: relative;
    z-index: 10;
}
.faq-tab-btn {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    padding: 10px 20px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
}
.faq-tab-btn.active, .faq-tab-btn:hover {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: white;
    border-color: #ea580c;
    box-shadow: 0 4px 12px rgba(249, 115, 22, 0.35);
}
.faq-accordion-group {
    display: flex;
    flex-direction: column;
    gap: 14px;
    margin-top: 25px;
}
.faq-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 18px 22px;
    cursor: pointer;
    transition: all 0.2s;
}
.faq-item summary {
    font-weight: 700;
    font-size: 16px;
    color: #1e293b;
    list-style: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.faq-item summary i.icon-arrow {
    color: #94a3b8;
    font-size: 13px;
    transition: transform 0.2s;
}
.faq-item[open] summary i.icon-arrow {
    transform: rotate(180deg);
}
.faq-item p {
    color: #64748b;
    font-size: 14.5px;
    line-height: 1.7;
    margin: 14px 0 0 0;
    padding-top: 14px;
    border-top: 1px dashed #e2e8f0;
}
@media (max-width: 768px) {
    .faq-container {
        padding: 25px 18px;
        margin-top: 0;
    }
}
</style>

<section class="faq-hero">
    <div class="container">
        <nav style="font-size: 13px; color: rgba(255,255,255,0.7); display:flex; align-items:center; justify-content:center; gap:8px; margin-bottom:16px;">
            <a href="<?php echo esc_url( home_url('/') ); ?>" style="color: rgba(255,255,255,0.85); text-decoration: none;"><i class="fa-solid fa-house" style="font-size:11px;"></i> Ana Sayfa</a>
            <span>/</span>
            <span style="color: #fb923c; font-weight:700;">Sıkça Sorulan Sorular</span>
        </nav>

        <div class="faq-badge">
            <i class="fa-solid fa-circle-question"></i> Yardım & Destek Merkezi
        </div>

        <h1 style="font-size: 38px; font-weight: 800; margin: 0 0 12px 0;">Sıkça Sorulan Sorular (SSS)</h1>
        <p style="font-size: 16px; color: #cbd5e1; max-width: 650px; margin: 0 auto;">
            Sürücüler ve oto tamircileri için en çok merak edilen soruların yanıtları
        </p>
    </div>
</section>

<div class="container">
    <div class="faq-container">
        
        <!-- Sekmeler -->
        <div style="display:flex; justify-content:center; gap:10px; flex-wrap:wrap; margin-bottom: 20px;">
            <button class="faq-tab-btn active" onclick="showFaq('all')">Tüm Sorular</button>
            <button class="faq-tab-btn" onclick="showFaq('drivers')">🚗 Sürücüler İçin</button>
            <button class="faq-tab-btn" onclick="showFaq('mechanics')">🔧 Ustalar & Servisler</button>
        </div>

        <div class="faq-accordion-group">
            <!-- Sürücüler -->
            <details class="faq-item" data-cat="drivers">
                <summary>
                    <span><i class="fa-solid fa-circle-check" style="color:#f97316; margin-right:8px;"></i> OtoTamirciBul'u kullanmak ücretli mi?</span>
                    <i class="fa-solid fa-chevron-down icon-arrow"></i>
                </summary>
                <p>Hayır, kesinlikle tamamen ücretsizdir. Sitemizdeki tüm usta profillerini inceleyebilir, telefon numaralarını alabilir ve doğrudan ustayla hiçbir komisyon ödemeden iletişime geçebilirsiniz.</p>
            </details>

            <details class="faq-item" data-cat="drivers">
                <summary>
                    <span><i class="fa-solid fa-location-crosshairs" style="color:#f97316; margin-right:8px;"></i> Bana en yakın ustayı nasıl bulabilirim?</span>
                    <i class="fa-solid fa-chevron-down icon-arrow"></i>
                </summary>
                <p>Ana sayfadaki arama kutusundan il ve ilçenizi seçebilir veya "İller" sayfamızdan şehrinize tıklayarak semtinizdeki ustaları puan ve yorumlarına göre listeleyebilirsiniz.</p>
            </details>

            <details class="faq-item" data-cat="drivers">
                <summary>
                    <span><i class="fa-solid fa-truck-pickup" style="color:#f97316; margin-right:8px;"></i> Pazar günü veya gece açık oto çekici ve usta var mı?</span>
                    <i class="fa-solid fa-chevron-down icon-arrow"></i>
                </summary>
                <p>Evet. Sitemizdeki arama filtrelerinde "Pazar Günü Açık" veya "7/24 Yol Yardım" filtrelerini seçerek acil durumlarda hizmet veren nöbetçi ustaları anında listeleyebilirsiniz.</p>
            </details>

            <details class="faq-item" data-cat="drivers">
                <summary>
                    <span><i class="fa-solid fa-star" style="color:#f97316; margin-right:8px;"></i> Usta değerlendirmeleri ve yorumları güvenilir mi?</span>
                    <i class="fa-solid fa-chevron-down icon-arrow"></i>
                </summary>
                <p>Evet, sistemimize yapılan tüm kullanıcı yorumları moderatörlerimiz tarafından incelenir. Sahte veya haksız rekabet amaçlı yorumlar engellenir, sadece gerçek kullanıcı deneyimleri yayınlanır.</p>
            </details>

            <!-- Ustalar -->
            <details class="faq-item" data-cat="mechanics">
                <summary>
                    <span><i class="fa-solid fa-plus-circle" style="color:#10b981; margin-right:8px;"></i> Tamirhanemi veya servisimi nasıl ekleyebilirim?</span>
                    <i class="fa-solid fa-chevron-down icon-arrow"></i>
                </summary>
                <p>Sitemizin üst menüsünde yer alan "Ücretsiz İlan Ver" butonuna tıklayarak işletme adı, telefon, adres ve hizmet dallarınızı içeren formu doldurarak birkaç dakika içinde ilanınızı oluşturabilirsiniz.</p>
            </details>

            <details class="faq-item" data-cat="mechanics">
                <summary>
                    <span><i class="fa-solid fa-clock-rotate-left" style="color:#10b981; margin-right:8px;"></i> Eklediğim ilan ne zaman yayına girer?</span>
                    <i class="fa-solid fa-chevron-down icon-arrow"></i>
                </summary>
                <p>İlanınız eklendikten sonra moderasyon ekibimiz bilgilerin doğruluğunu kontrol eder ve en geç 24 saat içinde onaylayarak yayına alır.</p>
            </details>

            <details class="faq-item" data-cat="mechanics">
                <summary>
                    <span><i class="fa-solid fa-pen-to-square" style="color:#10b981; margin-right:8px;"></i> İlanımdaki telefon veya adres bilgisini nasıl güncellerim?</span>
                    <i class="fa-solid fa-chevron-down icon-arrow"></i>
                </summary>
                <p>Üye panelinize giriş yaparak profilinizi düzenleyebilir veya İletişim sayfamızdan işletme adınızı belirterek güncelleme talebinde bulunabilirsiniz.</p>
            </details>

            <details class="faq-item" data-cat="mechanics">
                <summary>
                    <span><i class="fa-solid fa-tag" style="color:#10b981; margin-right:8px;"></i> İlan vermek ücretli mi? Geçici ücretsiz süreç nedir?</span>
                    <i class="fa-solid fa-chevron-down icon-arrow"></i>
                </summary>
                <p>Platformumuz lansman süresi boyunca tüm ustalarımıza geçici olarak <strong>tamamen ÜCRETSİZDİR</strong>. Şimdi kaydolarak ücretsiz ilan hakkı kazanabilirsiniz. Çok yakında Profesyonel ve Premium paketlerimiz aktif olacaktır. Lansman döneminde kayıt olan ustalarımız erken üyelik avantajlarından faydalanacaktır.</p>
            </details>

            <details class="faq-item" data-cat="mechanics">
                <summary>
                    <span><i class="fa-solid fa-layer-group" style="color:#10b981; margin-right:8px;"></i> Hangi üyelik paketleri bulunmaktadır ve ücretleri nelerdir?</span>
                    <i class="fa-solid fa-chevron-down icon-arrow"></i>
                </summary>
                <p>Platformumuzda 3 paket modeli yer almaktadır:<br>
                • <strong>🆓 Ücretsiz Paket (0 TL):</strong> 1 ilan (90 gün yayında kalır), 1 fotoğraf, 1 hizmet ekleme, WhatsApp & telefon butonu.<br>
                • <strong>⭐ Profesyonel Paket (299 TL / ay - Çok Yakında):</strong> 5 ilan, 10 fotoğraf, 10 hizmet, 1 adet vitrin ilanı, aramalarda öncelik ve profil doğrulama rozeti.<br>
                • <strong>👑 Premium Paket (599 TL / ay - Çok Yakında):</strong> 15 ilan, 30 fotoğraf, sınırsız hizmet, 3 vitrin ilanı, ana sayfada görünme, 'Önerilen Usta' altın rozeti ve 7/24 VIP destek.<br>
                Detaylı karşılaştırma tablosunu <a href="/paketler" style="color:#f97316; font-weight:700;">Paketler</a> sayfamızdan inceleyebilirsiniz.</p>
            </details>

            <details class="faq-item" data-cat="mechanics">
                <summary>
                    <span><i class="fa-solid fa-shield" style="color:#10b981; margin-right:8px;"></i> Ücretli paketler açıldığında ücretsiz ilanım silinir mi?</span>
                    <i class="fa-solid fa-chevron-down icon-arrow"></i>
                </summary>
                <p>Hayır, lansman süresince eklediğiniz temel usta ilanınız yayında kalmaya devam eder. Dilerseniz firmanızı aramalarda ve il slider'larında en üste taşımak için dilediğiniz zaman Profesyonel veya Premium pakete geçiş yapabilirsiniz.</p>
            </details>
        </div>

        <div style="margin-top: 35px; text-align:center; background:#f8fafc; border:1px dashed #cbd5e1; border-radius:12px; padding:22px;">
            <p style="color:#475569; margin:0 0 10px 0; font-size:15px;">Aradığınız sorunun cevabını bulamadınız mı?</p>
            <a href="<?php echo esc_url( home_url('/iletisim') ); ?>" style="display:inline-flex; align-items:center; gap:8px; background:var(--primary); color:white; padding:10px 22px; border-radius:25px; text-decoration:none; font-weight:600; font-size:14px;">
                <i class="fa-solid fa-headset"></i> Destek Ekibine Ulaşın
            </a>
        </div>

    </div>
</div>

<script>
function showFaq(category) {
    const items = document.querySelectorAll('.faq-item');
    const btns = document.querySelectorAll('.faq-tab-btn');
    
    btns.forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');

    items.forEach(item => {
        if (category === 'all' || item.getAttribute('data-cat') === category) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}
</script>

<?php get_footer(); ?>