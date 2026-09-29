<?php
/* Template Name: Giriş Yap */

if ( is_user_logged_in() ) {
    wp_redirect( home_url('/profil') );
    exit;
}

get_header(); 

$redirect_to = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( $_REQUEST['redirect_to'] ) : '';
?>

<style>
/* ===================================================
   MODERN AUTH (GİRİŞ & KAYIT) SPLIT LAYOUT STİLLERİ
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

/* Arka plan ışık halkaları (Aura effects) */
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
    background: radial-gradient(circle, rgba(59, 130, 246, 0.12) 0%, rgba(59, 130, 246, 0) 70%);
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

/* SOL PANEL: Marka & Avantajlar */
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
    background: rgba(249, 115, 22, 0.15);
    border: 1px solid rgba(249, 115, 22, 0.35);
    color: #fb923c;
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
    background: linear-gradient(135deg, #fb923c 0%, #f97316 100%);
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
    color: #f97316;
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
.auth-brand-footer i {
    color: #10b981;
}

/* SAĞ PANEL: Giriş Formu */
.auth-form-col {
    padding: 50px 48px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.auth-tabs-nav {
    display: flex;
    background: rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    padding: 4px;
    margin-bottom: 32px;
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
    background: rgba(249, 115, 22, 0.18);
    color: #fb923c;
    border: 1px solid rgba(249, 115, 22, 0.3);
    box-shadow: 0 4px 12px rgba(249, 115, 22, 0.15);
}
.auth-tab-link:hover:not(.active) {
    color: white;
    background: rgba(255, 255, 255, 0.04);
}

.auth-heading {
    margin-bottom: 26px;
}
.auth-heading h1 {
    font-size: 26px;
    font-weight: 800;
    color: white;
    margin: 0 0 8px;
    letter-spacing: -0.3px;
}
.auth-heading p {
    color: #94a3b8;
    font-size: 14.5px;
    margin: 0;
}

/* Form Grupları & İkonlu Inputlar */
.auth-form-group {
    margin-bottom: 20px;
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
    padding: 14px 44px 14px 46px;
    background: rgba(15, 23, 42, 0.6);
    border: 1.5px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    color: white;
    font-size: 15px;
    font-family: inherit;
    transition: all 0.2s ease;
    outline: none;
}
.auth-input:focus {
    border-color: #f97316;
    background: rgba(15, 23, 42, 0.85);
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.18);
}
.auth-input:focus ~ .auth-input-icon {
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
    padding: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.2s;
}
.btn-toggle-pw:hover {
    color: #fb923c;
}

/* Beni Hatırla & Şifremi Unuttum */
.auth-options-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    font-size: 13.5px;
}
.auth-remember {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #cbd5e1;
    cursor: pointer;
    user-select: none;
}
.auth-remember input[type="checkbox"] {
    accent-color: #f97316;
    width: 16px;
    height: 16px;
    cursor: pointer;
}
.btn-forgot-pw {
    color: #fb923c;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.2s;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    font-family: inherit;
    font-size: 13.5px;
}
.btn-forgot-pw:hover {
    color: #ea580c;
    text-decoration: underline;
}

/* Giriş Butonu */
.btn-auth-submit {
    width: 100%;
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: white;
    border: none;
    padding: 15px 24px;
    border-radius: 12px;
    font-size: 15.5px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    box-shadow: 0 8px 20px -4px rgba(249, 115, 22, 0.45);
    transition: all 0.2s ease;
    font-family: inherit;
}
.btn-auth-submit:hover {
    background: linear-gradient(135deg, #fb923c 0%, #ea580c 100%);
    transform: translateY(-1px);
    box-shadow: 0 12px 24px -4px rgba(249, 115, 22, 0.55);
}
.btn-auth-submit:active {
    transform: translateY(0);
}

/* Mesaj Kutuları */
.auth-alert {
    padding: 14px 18px;
    border-radius: 12px;
    margin-bottom: 22px;
    font-size: 14px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    line-height: 1.5;
}
.auth-alert.error {
    background: rgba(239, 68, 68, 0.12);
    border: 1px solid rgba(239, 68, 68, 0.28);
    color: #fca5a5;
}
.auth-alert.error i {
    color: #ef4444;
    font-size: 16px;
    margin-top: 2px;
}
.auth-alert.success {
    background: rgba(16, 185, 129, 0.12);
    border: 1px solid rgba(16, 185, 129, 0.28);
    color: #6ee7b7;
}
.auth-alert.success i {
    color: #10b981;
    font-size: 16px;
    margin-top: 2px;
}

/* Şifre Sıfırlama Paneli */
#lostpw-panel {
    display: none;
    background: rgba(0, 0, 0, 0.2);
    border: 1px dashed rgba(251, 146, 60, 0.35);
    border-radius: 14px;
    padding: 22px;
    margin-bottom: 24px;
    animation: fadeIn 0.25s ease;
}
#lostpw-panel h3 {
    color: #f8fafc;
    font-size: 16px;
    font-weight: 700;
    margin: 0 0 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}
#lostpw-panel p {
    font-size: 13px;
    color: #94a3b8;
    margin: 0 0 16px;
    line-height: 1.5;
}

/* Alt Link */
.auth-bottom-switch {
    margin-top: 30px;
    text-align: center;
    font-size: 14px;
    color: #94a3b8;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    padding-top: 24px;
}
.auth-bottom-switch a {
    color: #fb923c;
    font-weight: 700;
    text-decoration: none;
    margin-left: 4px;
}
.auth-bottom-switch a:hover {
    text-decoration: underline;
    color: #f97316;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* RESPONSIVE */
@media (max-width: 860px) {
    .auth-container {
        grid-template-columns: 1fr;
        max-width: 520px;
    }
    .auth-brand-col {
        display: none; /* Mobilde doğrudan form odaklı */
    }
    .auth-form-col {
        padding: 40px 28px;
    }
}
</style>

<div class="auth-page-wrapper">
    <div class="auth-container">
        
        <!-- 1. SOL PANEL: Marka & Platform Avantajları -->
        <div class="auth-brand-col">
            <div>
                <div class="auth-brand-badge">
                    <i class="fa-solid fa-shield-halved"></i> Güvenilir Oto Tamir Platformu
                </div>
                <h2 class="auth-brand-title">
                    Aracınız İçin Doğru Usta, <span>Tek Tıkla</span> Yanınızda
                </h2>
                <p class="auth-brand-subtitle">
                    Binlerce doğrulanmış oto tamirci ve servisi inceleyin, gerçek müşteri yorumlarını okuyun ve aracınızı güvenle emanet edin.
                </p>

                <ul class="auth-benefits-list">
                    <li class="auth-benefit-item">
                        <div class="auth-benefit-icon">
                            <i class="fa-solid fa-wrench"></i>
                        </div>
                        <div class="auth-benefit-text">
                            <strong>81 İlde 5.000+ Onaylı Usta</strong>
                            <span>Motor, mekanik, elektrik, kaporta ve tüm branşlarda uzmanlar.</span>
                        </div>
                    </li>
                    <li class="auth-benefit-item">
                        <div class="auth-benefit-icon">
                            <i class="fa-solid fa-car"></i>
                        </div>
                        <div class="auth-benefit-text">
                            <strong>Dijital Araç Garajı & Bakım Defteri</strong>
                            <span>Aracınızı ekleyin; periyodik bakım, parça ve maliyet geçmişinizi saklayın.</span>
                        </div>
                    </li>
                    <li class="auth-benefit-item">
                        <div class="auth-benefit-icon">
                            <i class="fa-solid fa-microchip"></i>
                        </div>
                        <div class="auth-benefit-text">
                            <strong>Yapay Zeka Destekli Arıza Teşhisi</strong>
                            <span>Arıza belirtilerini girin, sistem ilgili uzman branşı anında bulsun.</span>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="auth-brand-footer">
                <i class="fa-solid fa-circle-check"></i>
                <span>%100 Ücretsiz Üyelik & Güvenli SSL Koruması</span>
            </div>
        </div>

        <!-- 2. SAĞ PANEL: Giriş Formu -->
        <div class="auth-form-col">
            <!-- Navigasyon Sekmeleri -->
            <div class="auth-tabs-nav">
                <a href="<?php echo esc_url( home_url('/giris/') ); ?>" class="auth-tab-link active">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i> Giriş Yap
                </a>
                <a href="<?php echo esc_url( home_url('/kayit/') ); ?>" class="auth-tab-link">
                    <i class="fa-solid fa-user-plus"></i> Kayıt Ol
                </a>
            </div>

            <div class="auth-heading">
                <h1>Hesabınıza Giriş Yapın</h1>
                <p>Ototamir360 hesabınızla oturum açarak profilinize erişin.</p>
            </div>

            <?php 
            global $login_error, $lostpw_error, $lostpw_success;
            if ( ! empty( $login_error ) ) {
                echo '<div class="auth-alert error"><i class="fa-solid fa-circle-exclamation"></i><div><strong>Giriş Başarısız:</strong> ' . wp_kses_post( $login_error ) . '</div></div>';
            }
            if ( ! empty( $lostpw_error ) ) {
                echo '<div class="auth-alert error"><i class="fa-solid fa-circle-exclamation"></i><div>' . esc_html( $lostpw_error ) . '</div></div>';
            }
            if ( ! empty( $lostpw_success ) ) {
                echo '<div class="auth-alert success"><i class="fa-solid fa-circle-check"></i><div>' . esc_html( $lostpw_success ) . '</div></div>';
            }
            ?>

            <!-- Şifremi Unuttum Paneli (Açılır-Kapanır) -->
            <div id="lostpw-panel">
                <h3><i class="fa-solid fa-key" style="color:#f97316;"></i> Şifremi Unuttum</h3>
                <p>Hesabınıza ait kullanıcı adı veya e-posta adresinizi yazın. Şifre yenileme linkini adresinize ulaştıralım.</p>
                <form method="post" action="<?php echo esc_url( home_url('/giris/') ); ?>">
                    <?php wp_nonce_field( 'ototamir_lostpw_action', 'ototamir_lostpw_nonce' ); ?>
                    <div class="auth-form-group">
                        <div class="auth-input-wrapper">
                            <input type="text" name="user_login" class="auth-input" placeholder="Kullanıcı adı veya e-posta" required style="padding-left: 42px;">
                            <i class="fa-solid fa-envelope auth-input-icon"></i>
                        </div>
                    </div>
                    <div style="display:flex; gap:10px; align-items:center;">
                        <button type="submit" name="ototamir_lostpw_submit" class="btn-auth-submit" style="padding:12px 20px; font-size:14px;">
                            <i class="fa-solid fa-paper-plane"></i> Sıfırlama Bağlantısı Gönder
                        </button>
                        <button type="button" class="btn-forgot-pw" onclick="toggleLostPwPanel();" style="font-size:13px; color:#94a3b8;">
                            Vazgeç
                        </button>
                    </div>
                </form>
            </div>

            <!-- Standart Giriş Formu -->
            <form method="post" action="<?php echo esc_url( home_url('/giris/') ); ?>" id="login-form">
                <?php wp_nonce_field( 'ototamir_login_action', 'ototamir_login_nonce' ); ?>
                <?php if ( ! empty( $redirect_to ) ) : ?>
                    <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>">
                <?php endif; ?>

                <div class="auth-form-group">
                    <label for="log">Kullanıcı Adı veya E-posta Adresi</label>
                    <div class="auth-input-wrapper">
                        <input type="text" name="log" id="log" class="auth-input" placeholder="ornek@domain.com veya kullaniciadi" required autocomplete="username" value="<?php echo isset($_POST['log']) ? esc_attr($_POST['log']) : ''; ?>">
                        <i class="fa-solid fa-user auth-input-icon"></i>
                    </div>
                </div>

                <div class="auth-form-group">
                    <label for="pwd">Şifreniz</label>
                    <div class="auth-input-wrapper">
                        <input type="password" name="pwd" id="pwd" class="auth-input" placeholder="••••••••" required autocomplete="current-password">
                        <i class="fa-solid fa-lock auth-input-icon"></i>
                        <button type="button" class="btn-toggle-pw" onclick="togglePasswordVisibility('pwd', this);" title="Şifreyi Göster / Gizle">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="auth-options-row">
                    <label class="auth-remember">
                        <input type="checkbox" name="rememberme" id="rememberme" value="forever" checked>
                        <span>Beni Hatırla</span>
                    </label>
                    <button type="button" class="btn-forgot-pw" onclick="toggleLostPwPanel();">
                        Şifremi Unuttum?
                    </button>
                </div>

                <button type="submit" name="ototamir_login_submit" class="btn-auth-submit">
                    Giriş Yap <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>

            <div class="auth-bottom-switch">
                Hesabınız henüz yok mu? <a href="<?php echo esc_url( home_url('/kayit') ); ?>">Ücretsiz Kayıt Olun</a>
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

// Şifremi Unuttum Panelini Aç / Kapat
function toggleLostPwPanel() {
    const panel = document.getElementById('lostpw-panel');
    const loginForm = document.getElementById('login-form');
    if (!panel) return;
    if (panel.style.display === 'block') {
        panel.style.display = 'none';
        if (loginForm) loginForm.style.opacity = '1';
    } else {
        panel.style.display = 'block';
        const pwInput = panel.querySelector('input[name="user_login"]');
        if (pwInput) pwInput.focus();
    }
}
</script>

<?php get_footer(); ?>
