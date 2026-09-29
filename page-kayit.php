<?php
/* Template Name: Kayıt Ol */

if ( is_user_logged_in() ) {
    wp_redirect( home_url('/profil') );
    exit;
}

get_header(); 

$redirect_to = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( $_REQUEST['redirect_to'] ) : '';
$auth_nonce = wp_create_nonce( 'ototamir_auth_nonce' );
?>

<style>
/* ===================================================
   MODERN AUTH (KAYIT OL) SPLIT LAYOUT STİLLERİ
=================================================== */
.auth-page-wrapper {
    min-height: calc(100vh - 80px);
    background: radial-gradient(circle at 10% 20%, #1e293b 0%, #0f172a 100%);
    padding: 60px 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.auth-page-wrapper::before {
    content: '';
    position: absolute;
    top: -10%;
    left: 15%;
    width: 450px;
    height: 450px;
    background: radial-gradient(circle, rgba(249, 115, 22, 0.15) 0%, rgba(249, 115, 22, 0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.auth-page-wrapper::after {
    content: '';
    position: absolute;
    bottom: -10%;
    right: 15%;
    width: 450px;
    height: 450px;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.12) 0%, rgba(16, 185, 129, 0) 70%);
    border-radius: 50%;
    pointer-events: none;
}

.auth-container {
    width: 100%;
    max-width: 1060px;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 28px;
    box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);
    display: grid;
    grid-template-columns: 1fr 1.15fr;
    overflow: hidden;
    position: relative;
    z-index: 2;
}

/* SOL PANEL */
.auth-brand-col {
    padding: 50px 44px;
    background: linear-gradient(145deg, rgba(30, 41, 59, 0.8) 0%, rgba(15, 23, 42, 0.95) 100%);
    border-right: 1px solid rgba(255, 255, 255, 0.08);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
}
.auth-brand-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(16, 185, 129, 0.15);
    border: 1px solid rgba(16, 185, 129, 0.35);
    color: #34d399;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    width: fit-content;
    margin-bottom: 24px;
}
.auth-brand-title {
    font-size: 30px;
    font-weight: 800;
    color: white;
    line-height: 1.3;
    margin: 0 0 16px;
    letter-spacing: -0.5px;
}
.auth-brand-title span {
    background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.auth-brand-subtitle {
    color: #94a3b8;
    font-size: 15px;
    line-height: 1.65;
    margin: 0 0 36px;
}

.auth-benefits-list {
    list-style: none;
    padding: 0;
    margin: 0 0 36px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.auth-benefit-item {
    display: flex;
    align-items: flex-start;
    gap: 14px;
}
.auth-benefit-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #10b981;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
    margin-top: 2px;
}
.auth-benefit-text strong {
    display: block;
    color: #f1f5f9;
    font-size: 14.5px;
    font-weight: 700;
    margin-bottom: 3px;
}
.auth-benefit-text span {
    color: #94a3b8;
    font-size: 13px;
    line-height: 1.45;
}

.auth-brand-footer {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-top: 24px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    font-size: 13px;
    color: #64748b;
}
.auth-brand-footer i { color: #10b981; }

/* SAĞ PANEL: Kayıt Formu & OTP */
.auth-form-col {
    padding: 50px 48px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
}

.auth-tabs-nav {
    display: flex;
    background: rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    padding: 4px;
    margin-bottom: 28px;
    gap: 4px;
}
.auth-tab-link {
    flex: 1;
    text-align: center;
    padding: 10px 16px;
    border-radius: 9px;
    font-size: 14px;
    font-weight: 700;
    color: #94a3b8;
    text-decoration: none;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.auth-tab-link.active {
    background: rgba(16, 185, 129, 0.18);
    color: #34d399;
    border: 1px solid rgba(16, 185, 129, 0.3);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
}
.auth-tab-link:hover:not(.active) {
    color: white;
    background: rgba(255, 255, 255, 0.04);
}

.auth-heading {
    margin-bottom: 24px;
}
.auth-heading h1 {
    font-size: 26px;
    font-weight: 800;
    color: white;
    margin: 0 0 6px;
    letter-spacing: -0.3px;
}
.auth-heading p {
    color: #94a3b8;
    font-size: 14.5px;
    margin: 0;
}

/* ROL SEÇİCİ KARTLAR */
.role-cards-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 22px;
}
.role-radio-card {
    position: relative;
    display: block;
    cursor: pointer;
}
.role-radio-card input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}
.role-card-inner {
    padding: 14px 16px;
    background: rgba(15, 23, 42, 0.6);
    border: 1.5px solid rgba(255, 255, 255, 0.1);
    border-radius: 14px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    transition: all 0.2s ease;
    height: 100%;
}
.role-card-icon {
    font-size: 20px;
    color: #94a3b8;
    transition: color 0.2s;
}
.role-card-title {
    color: #f1f5f9;
    font-size: 14px;
    font-weight: 700;
}
.role-card-desc {
    color: #64748b;
    font-size: 12px;
    line-height: 1.4;
}
.role-radio-card input[type="radio"]:checked + .role-card-inner {
    border-color: #ea580c;
    background: rgba(234, 88, 12, 0.12);
    box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.25);
}
.role-radio-card input[type="radio"]:checked + .role-card-inner .role-card-icon {
    color: #f97316;
}
.role-radio-card input[type="radio"]:checked + .role-card-inner .role-card-title {
    color: #f97316;
}

/* Form Alanları */
.auth-form-group {
    margin-bottom: 18px;
}
.auth-form-group label {
    display: block;
    color: #cbd5e1;
    font-size: 13.5px;
    font-weight: 600;
    margin-bottom: 8px;
}
.auth-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}
.auth-input-icon {
    position: absolute;
    left: 16px;
    color: #64748b;
    font-size: 16px;
    pointer-events: none;
    transition: color 0.2s;
}
.auth-input {
    width: 100%;
    background: rgba(15, 23, 42, 0.7);
    border: 1.5px solid rgba(255, 255, 255, 0.12);
    border-radius: 12px;
    padding: 13px 16px 13px 44px;
    color: white;
    font-size: 14.5px;
    outline: none;
    transition: all 0.25s ease;
    box-sizing: border-box;
}
.auth-input:focus {
    border-color: #ea580c;
    box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.2);
    background: rgba(15, 23, 42, 0.9);
}
.auth-input:focus + .auth-input-icon {
    color: #f97316;
}
.btn-toggle-pw {
    position: absolute;
    right: 14px;
    background: none;
    border: none;
    color: #64748b;
    cursor: pointer;
    font-size: 16px;
    padding: 4px;
    transition: color 0.2s;
}
.btn-toggle-pw:hover { color: #f1f5f9; }

/* Şifre Gücü Çubuğu */
.pw-strength-wrap {
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.pw-strength-bar {
    flex: 1;
    height: 4px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 4px;
    overflow: hidden;
}
.pw-strength-fill {
    height: 100%;
    width: 0%;
    border-radius: 4px;
    transition: all 0.3s ease;
}
.pw-strength-text {
    font-size: 11.5px;
    font-weight: 700;
    min-width: 45px;
    text-align: right;
}

/* Koşullar Checkbox */
.auth-terms-row {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin: 20px 0 24px;
    cursor: pointer;
    font-size: 13px;
    color: #94a3b8;
    line-height: 1.5;
}
.auth-terms-row input[type="checkbox"] {
    margin-top: 3px;
    accent-color: #ea580c;
    width: 16px;
    height: 16px;
    cursor: pointer;
}
.auth-terms-row a {
    color: #f97316;
    text-decoration: none;
    font-weight: 600;
}
.auth-terms-row a:hover { text-decoration: underline; }

/* Butonlar */
.btn-auth-submit {
    width: 100%;
    background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
    color: white;
    border: none;
    padding: 14px 24px;
    border-radius: 12px;
    font-size: 15.5px;
    font-weight: 800;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    box-shadow: 0 8px 24px -4px rgba(234, 88, 12, 0.4);
    transition: all 0.25s ease;
}
.btn-auth-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px -4px rgba(234, 88, 12, 0.55);
}
.btn-auth-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.auth-bottom-switch {
    margin-top: 24px;
    text-align: center;
    color: #94a3b8;
    font-size: 14px;
}
.auth-bottom-switch a {
    color: #f97316;
    font-weight: 700;
    text-decoration: none;
    margin-left: 4px;
}
.auth-bottom-switch a:hover {
    text-decoration: underline;
    color: #ea580c;
}

/* Bildirim Kutuları */
.auth-alert {
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13.5px;
    line-height: 1.5;
    animation: fadeIn 0.3s ease;
}
.auth-alert.error {
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #fca5a5;
}
.auth-alert.success {
    background: rgba(16, 185, 129, 0.15);
    border: 1px solid rgba(16, 185, 129, 0.3);
    color: #6ee7b7;
}

/* ===================================================
   ADIM 2: OTP (DOĞRULAMA KODU) ÖZEL STİLLERİ
=================================================== */
#register-step-2 {
    display: none;
    animation: fadeIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}
.otp-header-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: rgba(234, 88, 12, 0.15);
    color: #f97316;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    margin-bottom: 18px;
    border: 1px solid rgba(234, 88, 12, 0.3);
    box-shadow: 0 0 0 8px rgba(234, 88, 12, 0.08);
}
.otp-inputs-grid {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    margin: 24px 0 20px;
}
.otp-digit {
    flex: 1;
    max-width: 54px;
    height: 60px;
    background: rgba(15, 23, 42, 0.85);
    border: 2px solid rgba(255, 255, 255, 0.15);
    border-radius: 14px;
    color: #ffffff;
    font-size: 26px;
    font-weight: 800;
    text-align: center;
    font-family: monospace;
    outline: none;
    transition: all 0.2s ease;
    box-sizing: border-box;
}
.otp-digit:focus {
    border-color: #ea580c;
    background: rgba(15, 23, 42, 1);
    box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.3);
    transform: translateY(-2px);
}
.otp-digit.filled {
    border-color: #34d399;
    color: #34d399;
}
.otp-meta-bar {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 22px;
    font-size: 13px;
}
.otp-meta-timer {
    color: #94a3b8;
    display: flex;
    align-items: center;
    gap: 6px;
}
.otp-meta-timer strong {
    color: #34d399;
    font-family: monospace;
    font-size: 14px;
}
.btn-resend-link {
    background: none;
    border: none;
    color: #f97316;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
    padding: 0;
}
.btn-resend-link:hover:not(:disabled) {
    color: #ea580c;
    text-decoration: underline;
}
.btn-resend-link:disabled {
    color: #64748b;
    cursor: not-allowed;
    text-decoration: none;
}
.btn-auth-back {
    background: transparent;
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #cbd5e1;
    padding: 11px 20px;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    width: 100%;
    margin-top: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s;
}
.btn-auth-back:hover {
    background: rgba(255, 255, 255, 0.06);
    color: white;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}

/* RESPONSIVE */
@media (max-width: 860px) {
    .auth-container {
        grid-template-columns: 1fr;
        max-width: 520px;
    }
    .auth-brand-col {
        display: none;
    }
    .auth-form-col {
        padding: 38px 24px;
    }
    .role-cards-grid {
        grid-template-columns: 1fr;
    }
    .otp-digit {
        height: 52px;
        font-size: 22px;
    }
}
</style>

<div class="auth-page-wrapper">
    <div class="auth-container">
        
        <!-- 1. SOL PANEL: Bilgi & Avantajlar -->
        <div class="auth-brand-col">
            <div>
                <div class="auth-brand-badge">
                    <i class="fa-solid fa-user-plus"></i> Ücretsiz Katılım
                </div>
                <h2 class="auth-brand-title">
                    Ototamir360 Ailesine <span>Hemen Katılın</span>
                </h2>
                <p class="auth-brand-subtitle">
                    Aracınız için güvenilir tamircilere ulaşmak veya oto servis işletmenizi binlerce araç sahibine tanıtmak için ücretsiz üye olun.
                </p>

                <ul class="auth-benefits-list">
                    <li class="auth-benefit-item">
                        <div class="auth-benefit-icon">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div class="auth-benefit-text">
                            <strong>E-posta ile Güvenli Kayıt</strong>
                            <span>Tek kullanımlık güvenlik kodu ile hesabınız anında ve güvenle doğrulanır.</span>
                        </div>
                    </li>
                    <li class="auth-benefit-item">
                        <div class="auth-benefit-icon">
                            <i class="fa-solid fa-car-side"></i>
                        </div>
                        <div class="auth-benefit-text">
                            <strong>Araç Sahipleri İçin</strong>
                            <span>81 ilde onaylı ustalar, acil çekiciler ve dijital arıza tespit aracı her zaman cebinizde.</span>
                        </div>
                    </li>
                    <li class="auth-benefit-item">
                        <div class="auth-benefit-icon">
                            <i class="fa-solid fa-screwdriver-wrench"></i>
                        </div>
                        <div class="auth-benefit-text">
                            <strong>Ustalar & İşletmeler İçin</strong>
                            <span>Profilinizi oluşturun, müşteri değerlendirmeleriyle güveninizi kanıtlayın ve vitrine çıkın.</span>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="auth-brand-footer">
                <i class="fa-solid fa-circle-check"></i>
                <span>Bilgileriniz 256-Bit SSL ile korunur, 3. şahıslarla paylaşılmaz.</span>
            </div>
        </div>

        <!-- 2. SAĞ PANEL: Kayıt & Doğrulama -->
        <div class="auth-form-col">
            <!-- Navigasyon Sekmeleri -->
            <div class="auth-tabs-nav" id="auth-tabs-nav">
                <a href="<?php echo esc_url( home_url('/giris/') ); ?>" class="auth-tab-link">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i> Giriş Yap
                </a>
                <a href="<?php echo esc_url( home_url('/kayit/') ); ?>" class="auth-tab-link active">
                    <i class="fa-solid fa-user-plus"></i> Kayıt Ol
                </a>
            </div>

            <!-- Genel Uyarı Bildirim Kutusu -->
            <div id="auth-alert-box" style="display: none;"></div>

            <!-- =========================================================
                 ADIM 1: HESAP BİLGİLERİ FORMU
            ========================================================= -->
            <div id="register-step-1">
                <div class="auth-heading">
                    <h1>Yeni Hesap Oluşturun</h1>
                    <p>Aşağıdaki bilgileri doldurarak hemen ücretsiz üyeliğinizi başlatın.</p>
                </div>

                <form id="step-1-form" novalidate>
                    <input type="hidden" name="action" value="ototamir_send_register_code">
                    <input type="hidden" name="nonce" value="<?php echo esc_attr( $auth_nonce ); ?>">
                    <?php if ( ! empty( $redirect_to ) ) : ?>
                        <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>">
                    <?php endif; ?>

                    <!-- Görsel Rol Seçici (Müşteri / Usta) -->
                    <div class="role-cards-grid">
                        <label class="role-radio-card">
                            <input type="radio" name="user_role" value="customer" checked>
                            <div class="role-card-inner">
                                <i class="fa-solid fa-car role-card-icon"></i>
                                <div class="role-card-title">Araç Sahibiyim</div>
                                <div class="role-card-desc">Usta bulmak ve araç garajımı takip etmek istiyorum.</div>
                            </div>
                        </label>

                        <label class="role-radio-card">
                            <input type="radio" name="user_role" value="mechanic">
                            <div class="role-card-inner">
                                <i class="fa-solid fa-wrench role-card-icon"></i>
                                <div class="role-card-title">Oto Tamir / Servisim</div>
                                <div class="role-card-desc">İşletmemi eklemek ve vitrine çıkmak istiyorum.</div>
                            </div>
                        </label>
                    </div>

                    <div class="auth-form-group">
                        <label for="user_login">Kullanıcı Adı</label>
                        <div class="auth-input-wrapper">
                            <input type="text" name="user_login" id="user_login" class="auth-input" placeholder="ornek: ahmet_oto" required autocomplete="username">
                            <i class="fa-solid fa-user auth-input-icon"></i>
                        </div>
                    </div>

                    <div class="auth-form-group">
                        <label for="user_email">E-posta Adresi</label>
                        <div class="auth-input-wrapper">
                            <input type="email" name="user_email" id="user_email" class="auth-input" placeholder="adiniz@domain.com" required autocomplete="email">
                            <i class="fa-solid fa-envelope auth-input-icon"></i>
                        </div>
                    </div>

                    <div class="auth-form-group">
                        <label for="user_pass">Şifreniz</label>
                        <div class="auth-input-wrapper">
                            <input type="password" name="user_pass" id="user_pass" class="auth-input" placeholder="En az 6 karakter" required autocomplete="new-password" oninput="checkPasswordStrength(this.value);">
                            <i class="fa-solid fa-lock auth-input-icon"></i>
                            <button type="button" class="btn-toggle-pw" onclick="togglePasswordVisibility('user_pass', this);" title="Şifreyi Göster / Gizle">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <!-- Şifre Güç Göstergesi -->
                        <div class="pw-strength-wrap">
                            <div class="pw-strength-bar">
                                <div class="pw-strength-fill" id="pw-bar-fill"></div>
                            </div>
                            <span class="pw-strength-text" id="pw-bar-text"></span>
                        </div>
                    </div>

                    <label class="auth-terms-row">
                        <input type="checkbox" name="terms" id="terms_check" required checked>
                        <span>
                            <a href="<?php echo esc_url( home_url('/') ); ?>" target="_blank">Kullanım Koşulları</a> ve 
                            <a href="<?php echo esc_url( home_url('/') ); ?>" target="_blank">Gizlilik Politikasını</a> 
                            okudum, kabul ediyorum.
                        </span>
                    </label>

                    <button type="submit" id="btn-submit-step-1" class="btn-auth-submit">
                        <i class="fa-solid fa-paper-plane"></i> Devam Et & Kod Gönder
                    </button>
                </form>

                <div class="auth-bottom-switch">
                    Zaten hesabınız var mı? <a href="<?php echo esc_url( home_url('/giris') ); ?>">Giriş Yapın</a>
                </div>
            </div>

            <!-- =========================================================
                 ADIM 2: 6 HANELİ E-POSTA DOĞRULAMA (OTP)
            ========================================================= -->
            <div id="register-step-2">
                <div style="text-align: center; margin-bottom: 22px;">
                    <div class="otp-header-icon">
                        <i class="fa-solid fa-envelope-open-text"></i>
                    </div>
                    <h1 style="color: white; font-size: 24px; font-weight: 800; margin: 0 0 8px;">
                        Onay Kodunu Girin
                    </h1>
                    <p style="color: #94a3b8; font-size: 14px; margin: 0; line-height: 1.5;">
                        Güvenliğiniz için <strong id="otp-target-email" style="color: #f97316;"></strong> adresine 6 haneli bir onay kodu gönderdik.
                    </p>
                </div>

                <!-- 6 Haneli Kutu Ağı -->
                <div class="otp-inputs-grid" id="otp-inputs-container">
                    <input type="text" maxlength="1" class="otp-digit" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" data-index="0" autofocus>
                    <input type="text" maxlength="1" class="otp-digit" inputmode="numeric" pattern="[0-9]*" data-index="1">
                    <input type="text" maxlength="1" class="otp-digit" inputmode="numeric" pattern="[0-9]*" data-index="2">
                    <input type="text" maxlength="1" class="otp-digit" inputmode="numeric" pattern="[0-9]*" data-index="3">
                    <input type="text" maxlength="1" class="otp-digit" inputmode="numeric" pattern="[0-9]*" data-index="4">
                    <input type="text" maxlength="1" class="otp-digit" inputmode="numeric" pattern="[0-9]*" data-index="5">
                </div>

                <!-- Zamanlayıcı & Tekrar Gönder Barı -->
                <div class="otp-meta-bar">
                    <div class="otp-meta-timer">
                        <i class="fa-regular fa-clock"></i>
                        <span>Kalan Süre:</span>
                        <strong id="otp-timer-display">03:00</strong>
                    </div>
                    <button type="button" id="btn-resend-otp" class="btn-resend-link" disabled>
                        <i class="fa-solid fa-rotate-right"></i> Tekrar Gönder (<span id="resend-timer-seconds">60</span>s)
                    </button>
                </div>

                <button type="button" id="btn-verify-otp" class="btn-auth-submit">
                    <i class="fa-solid fa-circle-check"></i> Kodu Doğrula ve Hesabı Aç
                </button>

                <button type="button" id="btn-back-step-1" class="btn-auth-back">
                    <i class="fa-solid fa-arrow-left"></i> E-posta adresini değiştir / Bilgileri düzenle
                </button>
            </div>

        </div>

    </div>
</div>

<script>
// Şifre Göster / Gizle
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Canlı Şifre Gücü Analizi
function checkPasswordStrength(password) {
    const bar = document.getElementById('pw-bar-fill');
    const text = document.getElementById('pw-bar-text');
    if (!bar || !text) return;

    if (!password || password.length === 0) {
        bar.style.width = '0%';
        bar.style.background = 'transparent';
        text.innerText = '';
        return;
    }

    let score = 0;
    if (password.length >= 6) score += 1;
    if (password.length >= 9) score += 1;
    if (/[A-Z]/.test(password)) score += 1;
    if (/[0-9]/.test(password)) score += 1;
    if (/[^A-Za-z0-9]/.test(password)) score += 1;

    if (score <= 2) {
        bar.style.width = '30%';
        bar.style.background = '#ef4444';
        text.innerText = 'Zayıf';
        text.style.color = '#f87171';
    } else if (score <= 4) {
        bar.style.width = '65%';
        bar.style.background = '#f59e0b';
        text.innerText = 'Orta';
        text.style.color = '#fbbf24';
    } else {
        bar.style.width = '100%';
        bar.style.background = '#10b981';
        text.innerText = 'Güçlü';
        text.style.color = '#34d399';
    }
}

/* =========================================================
   OTP & KAYIT AJAX AKIŞI
========================================================= */
(function() {
    var ajaxUrl = (typeof ototamir_auth_vars !== 'undefined') ? ototamir_auth_vars.ajax_url : '<?php echo esc_url( admin_url('admin-ajax.php') ); ?>';
    var nonce = (typeof ototamir_auth_vars !== 'undefined') ? ototamir_auth_vars.nonce : '<?php echo esc_js( $auth_nonce ); ?>';
    var redirectTo = '<?php echo esc_js( $redirect_to ); ?>';

    var currentEmail = '';
    var otpTimerInterval = null;
    var resendTimerInterval = null;
    var totalSecondsRemaining = 180; // 3 dakika
    var resendCooldown = 60;

    var alertBox = document.getElementById('auth-alert-box');
    var step1 = document.getElementById('register-step-1');
    var step2 = document.getElementById('register-step-2');
    var step1Form = document.getElementById('step-1-form');
    var submitBtn1 = document.getElementById('btn-submit-step-1');
    var verifyBtn = document.getElementById('btn-verify-otp');
    var resendBtn = document.getElementById('btn-resend-otp');
    var backBtn = document.getElementById('btn-back-step-1');
    var targetEmailDisplay = document.getElementById('otp-target-email');
    var otpDigits = document.querySelectorAll('.otp-digit');

    function showAlert(type, message) {
        alertBox.className = 'auth-alert ' + type;
        var icon = type === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check';
        alertBox.innerHTML = '<i class="fa-solid ' + icon + '"></i><div>' + message + '</div>';
        alertBox.style.display = 'flex';
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function hideAlert() {
        alertBox.style.display = 'none';
        alertBox.innerHTML = '';
    }

    // 1. ADIM FORM GÖNDERİMİ (KOD İSTEĞİ)
    step1Form.addEventListener('submit', function(e) {
        e.preventDefault();
        hideAlert();

        var username = document.getElementById('user_login').value.trim();
        var email = document.getElementById('user_email').value.trim();
        var pass = document.getElementById('user_pass').value;
        var role = document.querySelector('input[name="user_role"]:checked') ? document.querySelector('input[name="user_role"]:checked').value : 'customer';
        var terms = document.getElementById('terms_check').checked;

        if (!username || !email || !pass) {
            showAlert('error', 'Lütfen tüm alanları eksiksiz doldurun.');
            return;
        }

        if (pass.length < 6) {
            showAlert('error', 'Şifreniz en az 6 karakter olmalıdır.');
            return;
        }

        if (!terms) {
            showAlert('error', 'Devam etmek için kullanım koşullarını onaylamalısınız.');
            return;
        }

        submitBtn1.disabled = true;
        submitBtn1.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Kod Gönderiliyor...';

        var formData = new FormData();
        formData.append('action', 'ototamir_send_register_code');
        formData.append('nonce', nonce);
        formData.append('user_login', username);
        formData.append('user_email', email);
        formData.append('user_pass', pass);
        formData.append('user_role', role);

        fetch(ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            submitBtn1.disabled = false;
            submitBtn1.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Devam Et & Kod Gönder';

            if (data.success) {
                currentEmail = email;
                targetEmailDisplay.textContent = email;
                step1.style.display = 'none';
                step2.style.display = 'block';
                showAlert('success', data.data.message || 'Onay kodunuz e-posta adresinize gönderildi.');

                startOtpTimers(data.data.cooldown || 60);
                clearOtpDigits();
                otpDigits[0].focus();
            } else {
                showAlert('error', data.data && data.data.message ? data.data.message : 'Bir hata oluştu.');
            }
        })
        .catch(function() {
            submitBtn1.disabled = false;
            submitBtn1.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Devam Et & Kod Gönder';
            showAlert('error', 'Bağlantı hatası oluştu. Lütfen tekrar deneyin.');
        });
    });

    // ZAMANLAYICILARI BAŞLAT
    function startOtpTimers(cooldownSeconds) {
        clearInterval(otpTimerInterval);
        clearInterval(resendTimerInterval);

        totalSecondsRemaining = 180; // 3 dakika
        updateTimerDisplay();

        otpTimerInterval = setInterval(function() {
            totalSecondsRemaining--;
            updateTimerDisplay();
            if (totalSecondsRemaining <= 0) {
                clearInterval(otpTimerInterval);
                showAlert('error', 'Doğrulama kodunun süresi doldu. Lütfen tekrar kod gönderin.');
            }
        }, 1000);

        // Tekrar Gönder Sayacı
        var resendSeconds = cooldownSeconds || 60;
        resendBtn.disabled = true;
        document.getElementById('resend-timer-seconds').textContent = resendSeconds;

        resendTimerInterval = setInterval(function() {
            resendSeconds--;
            document.getElementById('resend-timer-seconds').textContent = resendSeconds;
            if (resendSeconds <= 0) {
                clearInterval(resendTimerInterval);
                resendBtn.disabled = false;
                resendBtn.innerHTML = '<i class="fa-solid fa-rotate-right"></i> Kodu Tekrar Gönder';
            }
        }, 1000);
    }

    function updateTimerDisplay() {
        var m = Math.floor(totalSecondsRemaining / 60);
        var s = totalSecondsRemaining % 60;
        document.getElementById('otp-timer-display').textContent = (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
    }

    // OTP KUTUCUKLARI ETKİLEŞİMİ (Auto-advance & Backspace & Paste)
    otpDigits.forEach(function(input, index) {
        input.addEventListener('input', function(e) {
            var val = input.value.replace(/\D/g, '');
            input.value = val ? val[0] : '';
            if (input.value) {
                input.classList.add('filled');
                if (index < otpDigits.length - 1) {
                    otpDigits[index + 1].focus();
                } else {
                    // Son hane doldu, tüm haneler tamsa otomatik doğrula
                    if (getOtpValue().length === 6) {
                        triggerVerifyOtp();
                    }
                }
            } else {
                input.classList.remove('filled');
            }
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !input.value && index > 0) {
                otpDigits[index - 1].focus();
            } else if (e.key === 'ArrowLeft' && index > 0) {
                otpDigits[index - 1].focus();
            } else if (e.key === 'ArrowRight' && index < otpDigits.length - 1) {
                otpDigits[index + 1].focus();
            }
        });

        input.addEventListener('paste', function(e) {
            e.preventDefault();
            var text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            if (!text) return;

            for (var i = 0; i < otpDigits.length; i++) {
                if (text[i]) {
                    otpDigits[i].value = text[i];
                    otpDigits[i].classList.add('filled');
                }
            }
            var nextFocus = Math.min(text.length, otpDigits.length - 1);
            otpDigits[nextFocus].focus();

            if (text.length >= 6) {
                triggerVerifyOtp();
            }
        });
    });

    function getOtpValue() {
        var str = '';
        otpDigits.forEach(function(el) { str += el.value; });
        return str;
    }

    function clearOtpDigits() {
        otpDigits.forEach(function(el) {
            el.value = '';
            el.classList.remove('filled');
        });
    }

    // KOD DOĞRULAMA BUTONU
    verifyBtn.addEventListener('click', function() {
        triggerVerifyOtp();
    });

    function triggerVerifyOtp() {
        var code = getOtpValue();
        if (code.length !== 6) {
            showAlert('error', 'Lütfen 6 haneli kodu eksiksiz giriniz.');
            return;
        }

        hideAlert();
        verifyBtn.disabled = true;
        verifyBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Doğrulanıyor...';

        var formData = new FormData();
        formData.append('action', 'ototamir_verify_register_code');
        formData.append('nonce', nonce);
        formData.append('user_email', currentEmail);
        formData.append('otp_code', code);
        if (redirectTo) formData.append('redirect_to', redirectTo);

        fetch(ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                showAlert('success', data.data.message);
                verifyBtn.innerHTML = '<i class="fa-solid fa-check"></i> Onaylandı! Giriş Yapılıyor...';
                setTimeout(function() {
                    window.location.href = data.data.redirect_url || '<?php echo esc_url( home_url('/profil') ); ?>';
                }, 1000);
            } else {
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Kodu Doğrula ve Hesabı Aç';
                showAlert('error', data.data && data.data.message ? data.data.message : 'Doğrulama başarısız.');
                if (data.data && (data.data.expired || data.data.locked)) {
                    // Başa dön
                    setTimeout(function() {
                        step2.style.display = 'none';
                        step1.style.display = 'block';
                    }, 2000);
                }
            }
        })
        .catch(function() {
            verifyBtn.disabled = false;
            verifyBtn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Kodu Doğrula ve Hesabı Aç';
            showAlert('error', 'Bir bağlantı hatası oluştu. Lütfen tekrar deneyin.');
        });
    }

    // KODU TEKRAR GÖNDER
    resendBtn.addEventListener('click', function() {
        if (resendBtn.disabled) return;

        hideAlert();
        resendBtn.disabled = true;
        resendBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Gönderiliyor...';

        var formData = new FormData();
        formData.append('action', 'ototamir_resend_register_code');
        formData.append('nonce', nonce);
        formData.append('user_email', currentEmail);

        fetch(ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                showAlert('success', data.data.message || 'Yeni kodunuz gönderildi.');
                startOtpTimers(data.data.cooldown || 60);
                clearOtpDigits();
                otpDigits[0].focus();
            } else {
                resendBtn.disabled = false;
                resendBtn.innerHTML = '<i class="fa-solid fa-rotate-right"></i> Kodu Tekrar Gönder';
                showAlert('error', data.data && data.data.message ? data.data.message : 'Kod gönderilemedi.');
            }
        })
        .catch(function() {
            resendBtn.disabled = false;
            resendBtn.innerHTML = '<i class="fa-solid fa-rotate-right"></i> Kodu Tekrar Gönder';
            showAlert('error', 'Bağlantı hatası.');
        });
    });

    // 1. ADIMA GERİ DÖN
    backBtn.addEventListener('click', function() {
        clearInterval(otpTimerInterval);
        clearInterval(resendTimerInterval);
        hideAlert();
        step2.style.display = 'none';
        step1.style.display = 'block';
    });
})();
</script>

<?php get_footer(); ?>
