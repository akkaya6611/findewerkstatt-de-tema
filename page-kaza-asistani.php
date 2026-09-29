<?php
/**
 * Template Name: Kaza Anı Asistanı & Tutanak Sihirbazı
 * Description: Trafik kazası anında adım adım yapılması gerekenler, polis çağırma zorunluluk testi, kaza tutanağı doldurma rehberi ve acil yardım asistanı.
 * 
 * @package OtoTamir360
 */

get_header();

require_once get_template_directory() . '/inc/data/accident-assistant-data.php';

$emergency_steps = ototamir_get_accident_emergency_steps();
$police_checklist = ototamir_get_police_checklist();
$fault_scenarios = ototamir_get_fault_scenarios();
?>

<style>
/* ===================================================
   KAZA ANI ASİSTANI & TUTANAK SİHİRBAZI STİLLERİ
=================================================== */
.accident-page-wrapper {
    background: #f8fafc;
    min-height: 85vh;
    padding: 30px 0 80px;
}

/* BREADCRUMB */
.accident-breadcrumb {
    margin-bottom: 24px;
    font-size: 13.5px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.accident-breadcrumb a {
    color: #475569;
    text-decoration: none;
    font-weight: 600;
}
.accident-breadcrumb a:hover {
    color: #dc2626;
}
.accident-breadcrumb span.current {
    color: #b91c1c;
    font-weight: 700;
}

/* HERO (ACİL DURUM KIRMIZI) */
.accident-hero {
    background: linear-gradient(135deg, #7f1d1d 0%, #450a0a 60%, #991b1b 100%);
    border-radius: 20px;
    padding: 36px 32px;
    color: #ffffff;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 16px 36px rgba(127, 29, 29, 0.3);
}
.accident-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(239, 68, 68, 0.25);
    border: 1px solid rgba(239, 68, 68, 0.4);
    color: #fca5a5;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 14px;
}
.accident-hero h1 {
    font-size: 32px;
    font-weight: 800;
    color: #ffffff;
    margin: 0 0 12px 0;
    line-height: 1.3;
}
.accident-hero p {
    font-size: 16px;
    color: #fecaca;
    margin: 0 0 24px 0;
    max-width: 780px;
    line-height: 1.6;
}

/* HIZLI ACİL ÇAĞRI BUTONLARI */
.quick-emergency-actions {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
}
.btn-emergency-112 {
    background: #ffffff;
    color: #dc2626 !important;
    font-weight: 800;
    padding: 12px 22px;
    border-radius: 12px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.15);
    transition: transform 0.2s;
}
.btn-emergency-112:hover {
    transform: translateY(-2px);
}
.btn-emergency-tow {
    background: #ef4444;
    color: #ffffff !important;
    font-weight: 700;
    padding: 12px 22px;
    border-radius: 12px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    border: 1.5px solid rgba(255,255,255,0.3);
    transition: background 0.2s;
}
.btn-emergency-tow:hover {
    background: #dc2626;
}

/* 5 ADIMLI ACİL MÜDAHALE */
.steps-section {
    margin-bottom: 40px;
}
.section-head-title {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.steps-timeline {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.step-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 22px 24px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 14px rgba(0,0,0,0.03);
    display: flex;
    gap: 20px;
    align-items: flex-start;
}
.step-num-badge {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #fee2e2;
    color: #dc2626;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 900;
    flex-shrink: 0;
}
.step-content h3 {
    margin: 0 0 6px 0;
    font-size: 17px;
    font-weight: 800;
    color: #1e293b;
}
.step-content p {
    margin: 0 0 10px 0;
    font-size: 14.5px;
    color: #475569;
    line-height: 1.5;
}
.step-action-badge {
    display: inline-block;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
}

/* POLİS ÇAĞIRMA SİHİRBAZI */
.police-quiz-box {
    background: #ffffff;
    border-radius: 20px;
    padding: 32px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 6px 24px rgba(0,0,0,0.04);
    margin-bottom: 40px;
}
.quiz-header {
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 18px;
    margin-bottom: 24px;
}
.quiz-header h3 {
    margin: 0 0 6px 0;
    font-size: 20px;
    font-weight: 800;
    color: #0f172a;
}
.quiz-header p {
    margin: 0;
    color: #64748b;
    font-size: 14px;
}

.checklist-items {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 24px;
}
.check-item-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 18px;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}
.check-title {
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
    flex: 1;
}
.check-toggle-btns {
    display: flex;
    gap: 8px;
}
.toggle-btn {
    padding: 6px 14px;
    border-radius: 8px;
    border: 1.5px solid #cbd5e1;
    background: #ffffff;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
}
.toggle-btn.btn-yes.active {
    background: #ef4444;
    border-color: #ef4444;
    color: #ffffff;
}
.toggle-btn.btn-no.active {
    background: #10b981;
    border-color: #10b981;
    color: #ffffff;
}

.police-result-card {
    padding: 20px 24px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.res-green {
    background: #ecfdf5;
    border: 1.5px solid #a7f3d0;
    color: #065f46;
}
.res-red {
    background: #fef2f2;
    border: 1.5px solid #fecaca;
    color: #991b1b;
}

/* SBM KUSUR SENARYOLARI TABLOSU */
.fault-scenarios-box {
    background: #ffffff;
    border-radius: 20px;
    padding: 32px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 6px 24px rgba(0,0,0,0.04);
    margin-bottom: 40px;
}
.scenarios-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}
.scenario-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 18px 20px;
}
.scen-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
.scen-head h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
}
.scen-badge {
    background: #e2e8f0;
    color: #334155;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 6px;
}
.scen-rates {
    display: flex;
    gap: 10px;
    margin-bottom: 8px;
}
.rate-pill-red {
    background: #fee2e2;
    color: #dc2626;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12.5px;
    font-weight: 700;
}
.rate-pill-green {
    background: #d1fae5;
    color: #059669;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 12.5px;
    font-weight: 700;
}
.scen-desc {
    font-size: 13px;
    color: #475569;
    line-height: 1.5;
    margin: 0;
}

/* DOKÜMAN VE FORM İNDİRME */
.download-docs-banner {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border-radius: 16px;
    padding: 28px 32px;
    color: #ffffff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}
.docs-content h3 {
    margin: 0 0 6px 0;
    font-size: 18px;
    font-weight: 800;
}
.docs-content p {
    margin: 0;
    color: #94a3b8;
    font-size: 14px;
}
.docs-buttons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.btn-doc-pdf {
    background: #f97316;
    color: #ffffff !important;
    padding: 11px 18px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 13.5px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-doc-app {
    background: #334155;
    color: #ffffff !important;
    padding: 11px 18px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 13.5px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .scenarios-grid {
        grid-template-columns: 1fr;
    }
    .accident-hero h1 {
        font-size: 24px;
    }
    .check-item-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}
</style>

<div class="accident-page-wrapper">
    <div class="container">

        <!-- BREADCRUMB -->
        <nav class="accident-breadcrumb" aria-label="Breadcrumb">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><i class="fa-solid fa-house"></i> Ana Sayfa</a>
            <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
            <span>Sürücü Araçları</span>
            <i class="fa-solid fa-chevron-right" style="font-size: 10px;"></i>
            <span class="current">Kaza Anı Asistanı & Kaza Tespit Tutanağı Rehberi</span>
        </nav>

        <!-- HERO SECTION -->
        <div class="accident-hero">
            <div class="accident-hero-badge">
                <i class="fa-solid fa-bell"></i>
                <span>7/24 Acil Trafik Kazası Destek Kılavuzu</span>
            </div>
            <h1>Kaza Anı Asistanı & Kaza Tespit Tutanağı (KTT) Rehberi</h1>
            <p>
                Kaza yaptığınızda sakin olun. Adım adım güvenlik önlemlerinizi alın, polis çağırma durumunu kontrol edin, haklılığınızı kanıtlayacak kaza fotoğraflarını çekin ve tutanağı eksiksiz doldurun.
            </p>

            <div class="quick-emergency-actions">
                <a href="tel:112" class="btn-emergency-112">
                    <i class="fa-solid fa-phone-volume"></i>
                    <span>112 Acil Çağrı (Polis/Ambulans)</span>
                </a>
                <a href="<?php echo esc_url( home_url( '/oto-cekici-yol-yardim' ) ); ?>" class="btn-emergency-tow">
                    <i class="fa-solid fa-truck-pickup"></i>
                    <span>7/24 En Yakın Çekiciyi Çağır</span>
                </a>
            </div>
        </div>

        <!-- 5 ADIMDA ACİL MÜDAHALE -->
        <div class="steps-section">
            <div class="section-head-title">
                <i class="fa-solid fa-list-check" style="color:#dc2626;"></i>
                Kaza Anında Sırasıyla Yapılması Gereken 5 Hayati Adım
            </div>

            <div class="steps-timeline">
                <?php foreach ( $emergency_steps as $st ) : ?>
                    <div class="step-card">
                        <div class="step-num-badge">
                            <?php echo esc_html( $st['step'] ); ?>
                        </div>
                        <div class="step-content">
                            <h3><?php echo esc_html( $st['title'] ); ?></h3>
                            <p><?php echo esc_html( $st['desc'] ); ?></p>
                            <span class="step-action-badge">
                                <i class="fa-solid fa-triangle-exclamation" style="color:#ea580c;"></i> <?php echo esc_html( $st['action_note'] ); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- İNTERAKTİF POLİS ÇAĞIRMA KONTROL TESTİ -->
        <div class="police-quiz-box">
            <div class="quiz-header">
                <h3><i class="fa-solid fa-shield-halved" style="color:#2563eb;"></i> Trafik Polisi / Jandarma Çağırmak Zorunda mıyım?</h3>
                <p>Aşağıdaki soruları durumunuza göre işaretleyin. Sistem resmi mevzuata göre polis çağırmanız gerekip gerekmediğini anında söylesin.</p>
            </div>

            <div class="checklist-items">
                <div class="check-item-row">
                    <span class="check-title">1. Kazada herhangi bir yaralanma veya can kaybı var mı?</span>
                    <div class="check-toggle-btns">
                        <button type="button" class="toggle-btn btn-yes" onclick="setQuizAnswer(0, true)">Evet</button>
                        <button type="button" class="toggle-btn btn-no active" onclick="setQuizAnswer(0, false)">Hayır</button>
                    </div>
                </div>

                <div class="check-item-row">
                    <span class="check-title">2. Sürücülerden birinde ehliyet yok veya ehliyet sınıfı yetersiz mi?</span>
                    <div class="check-toggle-btns">
                        <button type="button" class="toggle-btn btn-yes" onclick="setQuizAnswer(1, true)">Evet</button>
                        <button type="button" class="toggle-btn btn-no active" onclick="setQuizAnswer(1, false)">Hayır</button>
                    </div>
                </div>

                <div class="check-item-row">
                    <span class="check-title">3. Karşı tarafta veya sizde alkol / uyuşturucu şüphesi var mı?</span>
                    <div class="check-toggle-btns">
                        <button type="button" class="toggle-btn btn-yes" onclick="setQuizAnswer(2, true)">Evet</button>
                        <button type="button" class="toggle-btn btn-no active" onclick="setQuizAnswer(2, false)">Hayır</button>
                    </div>
                </div>

                <div class="check-item-row">
                    <span class="check-title">4. Araçlardan birinin Zorunlu Trafik Sigortası eksik mi?</span>
                    <div class="check-toggle-btns">
                        <button type="button" class="toggle-btn btn-yes" onclick="setQuizAnswer(3, true)">Evet</button>
                        <button type="button" class="toggle-btn btn-no active" onclick="setQuizAnswer(3, false)">Hayır</button>
                    </div>
                </div>

                <div class="check-item-row">
                    <span class="check-title">5. Kamu malına (bariyer, aydınlatma direği, trafik levhası) zarar verildi mi?</span>
                    <div class="check-toggle-btns">
                        <button type="button" class="toggle-btn btn-yes" onclick="setQuizAnswer(4, true)">Evet</button>
                        <button type="button" class="toggle-btn btn-no active" onclick="setQuizAnswer(4, false)">Hayır</button>
                    </div>
                </div>
            </div>

            <div id="quizResultBox" class="police-result-card res-green">
                <i class="fa-solid fa-circle-check" style="font-size:28px;"></i>
                <div>
                    <h4 style="margin:0 0 4px 0; font-size:16px; font-weight:800;" id="quizResTitle">Polis Çağırmanıza Gerek Yok!</h4>
                    <p style="margin:0; font-size:13.5px;" id="quizResDesc">Tüm şartlar uygundur. Kendi aranızda fotoğrafları çekip Anlaşmalı Kaza Tespit Tutanağı düzenleyerek yolu trafiğe açabilirsiniz.</p>
                </div>
            </div>
        </div>

        <!-- SBM KUSUR SENARYOLARI -->
        <div class="fault-scenarios-box">
            <div class="quiz-header">
                <h3><i class="fa-solid fa-scale-balanced" style="color:#ea580c;"></i> En Yaygın Kaza Senaryoları & SBM Kusur Oranları</h3>
                <p>Sigorta Bilgi ve Gözetim Merkezi mevzuatına göre resmi eksperlerin uyguladığı temel kusur dağılımı:</p>
            </div>

            <div class="scenarios-grid">
                <?php foreach ( $fault_scenarios as $fs ) : ?>
                    <div class="scenario-card">
                        <div class="scen-head">
                            <h4><?php echo esc_html( $fs['scenario'] ); ?></h4>
                            <span class="scen-badge"><?php echo esc_html( $fs['badge'] ); ?></span>
                        </div>
                        <div class="scen-rates">
                            <span class="rate-pill-red"><?php echo esc_html( $fs['fault_a'] ); ?></span>
                            <span class="rate-pill-green"><?php echo esc_html( $fs['fault_b'] ); ?></span>
                        </div>
                        <p class="scen-desc"><?php echo esc_html( $fs['desc'] ); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- DOKÜMAN VE FORM İNDİRME BANNERI -->
        <div class="download-docs-banner">
            <div class="docs-content">
                <h3><i class="fa-solid fa-file-pdf" style="color:#f97316;"></i> Kaza Tespit Tutanağı (KTT) Elinizde Yok mu?</h3>
                <p>Boş tutanak şablonunu anında indirin veya akıllı telefonunuzdan e-Devlet Mobil Kaza Tutanağı ile kağıtsız işlem yapın.</p>
            </div>
            <div class="docs-buttons">
                <a href="https://www.sbm.org.tr/upload/KazaTespitTutanagi.pdf" target="_blank" rel="noopener" class="btn-doc-pdf">
                    <i class="fa-solid fa-download"></i> Boş Tutanak PDF İndir
                </a>
                <a href="https://mkt.sbm.org.tr/" target="_blank" rel="noopener" class="btn-doc-app">
                    <i class="fa-solid fa-mobile-screen"></i> Mobil Kaza Tutanağı (Online)
                </a>
            </div>
        </div>

    </div>
</div>

<script>
const quizAnswers = [false, false, false, false, false];

function setQuizAnswer(index, isYes) {
    quizAnswers[index] = isYes;
    
    // Buton stillerini güncelle
    const rows = document.querySelectorAll('.check-item-row');
    const buttons = rows[index].querySelectorAll('.toggle-btn');
    if (isYes) {
        buttons[0].classList.add('active');
        buttons[1].classList.remove('active');
    } else {
        buttons[0].classList.remove('active');
        buttons[1].classList.add('active');
    }

    // Sonucu hesapla
    evaluateQuiz();
}

function evaluateQuiz() {
    const hasAnyYes = quizAnswers.some(ans => ans === true);
    const resBox = document.getElementById('quizResultBox');
    const resTitle = document.getElementById('quizResTitle');
    const resDesc = document.getElementById('quizResDesc');

    if (hasAnyYes) {
        resBox.className = 'police-result-card res-red';
        resBox.querySelector('i').className = 'fa-solid fa-triangle-exclamation';
        resTitle.innerText = 'DİKKAT: Trafik Polisi veya Jandarma Çağırmak Zorundasınız!';
        resDesc.innerText = 'Yaralanma, ehliyetsizlik, alkol şüphesi, sigortasızlık veya kamu malı zararı durumlarında sürücüler kendi aralarında tutanak tutamaz. Lütfen araçları oynatmadan hemen 112 Acil Çağrı Merkezini arayın.';
    } else {
        resBox.className = 'police-result-card res-green';
        resBox.querySelector('i').className = 'fa-solid fa-circle-check';
        resTitle.innerText = 'Polis Çağırmanıza Gerek Yok!';
        resDesc.innerText = 'Tüm şartlar uygundur. Kendi aranızda fotoğrafları çekip Anlaşmalı Kaza Tespit Tutanağı düzenleyerek yolu trafiğe açabilirsiniz.';
    }
}
</script>

<?php get_footer(); ?>
