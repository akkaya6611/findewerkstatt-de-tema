    <style>
    /* ===================================================
       MODERN FOOTER STİLLERİ
    =================================================== */
    .site-footer {
        background: linear-gradient(180deg, #0f172a 0%, #090d16 100%);
        color: #94a3b8;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        padding: 70px 0 0 0;
        margin-top: 80px;
        font-size: 14.5px;
    }
    .footer-grid {
        display: grid;
        grid-template-columns: 1.4fr 1fr 1.2fr 1.2fr;
        gap: 40px;
        padding-bottom: 50px;
    }
    .footer-logo-wrap {
        display: inline-flex;
        align-items: center;
        background: #ffffff;
        padding: 8px 18px;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
        transition: transform 0.2s, box-shadow 0.2s;
        text-decoration: none;
    }
    .footer-logo-wrap:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(0, 0, 0, 0.35);
    }
    .footer-logo-wrap img {
        height: 44px;
        width: auto;
        display: block;
        object-fit: contain;
    }
    .footer-about-text {
        line-height: 1.7;
        margin-bottom: 20px;
        color: #94a3b8;
    }
    .footer-badge-trust {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(249, 115, 22, 0.12);
        border: 1px solid rgba(249, 115, 22, 0.35);
        color: #fb923c;
        padding: 7px 16px;
        border-radius: 50px;
        font-size: 12.5px;
        font-weight: 700;
    }
    .footer-badge-trust i {
        color: #f97316;
    }

    .footer-col h4 {
        color: #ffffff;
        font-size: 16px;
        font-weight: 700;
        margin: 0 0 20px 0;
        position: relative;
        padding-bottom: 10px;
        letter-spacing: 0.3px;
    }
    .footer-col h4::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 32px;
        height: 2px;
        background: linear-gradient(90deg, #f97316, #ea580c);
        border-radius: 2px;
    }

    .footer-links {
        display: flex;
        flex-direction: column;
        gap: 11px;
    }
    .footer-links a {
        color: #94a3b8;
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .footer-links a i {
        font-size: 10px;
        color: #f97316;
        transition: transform 0.2s;
    }
    .footer-links a:hover {
        color: #ffffff;
        transform: translateX(4px);
    }
    .footer-links a:hover i {
        transform: translateX(2px);
    }

    /* İletişim Kolonu */
    .footer-contact-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .footer-contact-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        line-height: 1.5;
    }
    .footer-contact-item i {
        color: #f97316;
        font-size: 15px;
        margin-top: 3px;
        flex-shrink: 0;
    }
    .footer-contact-item a {
        color: inherit;
        text-decoration: none;
        transition: color 0.2s;
    }
    .footer-contact-item a:hover {
        color: #fb923c;
    }
    .footer-cta-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: linear-gradient(135deg, #f97316, #ea580c);
        color: #ffffff !important;
        font-weight: 700;
        font-size: 13.5px;
        padding: 11px 20px;
        border-radius: 10px;
        text-decoration: none;
        margin-top: 16px;
        transition: all 0.25s;
        box-shadow: 0 4px 14px rgba(249, 115, 22, 0.4);
    }
    .footer-cta-btn:hover {
        transform: translateY(-2px);
        background: linear-gradient(135deg, #ea580c, #c2410c);
        box-shadow: 0 8px 20px rgba(249, 115, 22, 0.55);
    }

    /* Alt Telif & Sosyal Bar */
    .footer-bottom-bar {
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        padding: 22px 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        font-size: 13.5px;
    }
    .footer-social-wrap {
        display: flex;
        gap: 10px;
    }
    .footer-social-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.2s;
        font-size: 14px;
    }
    .footer-social-btn:hover {
        background: #f97316;
        border-color: #f97316;
        color: #ffffff;
        transform: translateY(-2px);
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .footer-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 36px;
        }
    }
    @media (max-width: 600px) {
        .site-footer {
            padding-top: 50px;
            margin-top: 50px;
        }
        .footer-grid {
            grid-template-columns: 1fr;
            gap: 32px;
        }
        .footer-bottom-bar {
            flex-direction: column;
            text-align: center;
            gap: 12px;
        }
        .footer-social-wrap {
            justify-content: center;
        }
    }

    /* MİS Teknoloji 360 Alt Banner */
    .footer-credit-banner {
        background: #060911;
        border-top: 1px solid rgba(255, 255, 255, 0.07);
        padding: 14px 0;
        font-size: 13.5px;
        color: #94a3b8;
    }
    .footer-credit-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    .footer-credit-banner a {
        color: #f97316;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s;
    }
    .footer-credit-banner a:hover {
        color: #fb923c;
        text-decoration: underline;
    }
    .credit-brand-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(249, 115, 22, 0.1);
        border: 1px solid rgba(249, 115, 22, 0.3);
        padding: 4px 12px;
        border-radius: 6px;
        color: #f97316 !important;
        text-decoration: none !important;
        transition: all 0.2s;
    }
    .credit-brand-link:hover {
        background: #f97316;
        color: #ffffff !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
    }
    @media (max-width: 600px) {
        .footer-credit-inner {
            flex-direction: column;
            text-align: center;
            gap: 8px;
        }
    }
    </style>

    </main><!-- #primary .site-main -->

    <footer class="site-footer" role="contentinfo">
        <div class="container">
            <div class="footer-grid">

                <!-- 1. SÜTUN: LOGO & ÜBER UNS -->
                <div class="footer-col footer-col-about">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="footer-logo-wrap" aria-label="FindeWerkstatt.de Startseite">
                        <span style="font-size:22px; font-weight:900; color:#0f172a; display:inline-flex; align-items:center; gap:8px;">
                            <i class="fa-solid fa-wrench" style="color:#f97316;"></i> Finde<span style="color:#ea580c;">Werkstatt</span><span style="font-size:14px; color:#64748b; font-weight:600;">.de</span>
                        </span>
                    </a>
                    <p class="footer-about-text">Deutschlands modernes Portal für Kfz-Meisterwerkstätten, 24h Abschleppdienste, TÜV-Vorbereitung und E-Auto Ladestationen in allen 16 Bundesländern. Schnell, transparent und datenschutzkonform.</p>
                    <div class="footer-badge-trust">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Geprüfte Kfz-Meisterbetriebe</span>
                    </div>
                </div>

                <!-- 2. SÜTUN: SÜRÜCÜ HİZMETLERİ & NOTDIENST -->
                <div class="footer-col">
                    <h4>Fahrerservice &amp; Notdienst</h4>
                    <div class="footer-links">
                        <a href="<?php echo esc_url( home_url( '/24h-pannenhilfe' ) ); ?>" style="color:#f87171; font-weight:700;"><i class="fa-solid fa-truck-pickup" style="color:#ef4444;"></i> 24h Abschleppdienst</a>
                        <a href="<?php echo esc_url( home_url( '/fehlerdiagnose' ) ); ?>" style="color:#fb923c;"><i class="fa-solid fa-triangle-exclamation" style="color:#f97316;"></i> KI-Fehlerdiagnose (OBD-II)</a>
                        <a href="<?php echo esc_url( home_url( '/ladestationen' ) ); ?>" style="color:#34d399;"><i class="fa-solid fa-charging-station" style="color:#10b981;"></i> E-Ladestationen Finder</a>
                        <a href="<?php echo esc_url( home_url( '/werkstaetten' ) ); ?>"><i class="fa-solid fa-chevron-right"></i> Alle Werkstätten durchsuchen</a>
                        <a href="<?php echo esc_url( home_url( '/staedte' ) ); ?>"><i class="fa-solid fa-chevron-right"></i> 16 Bundesländer &amp; Städte</a>
                    </div>
                </div>

                <!-- 3. SÜTUN: BELIEBTE LEISTUNGEN -->
                <div class="footer-col">
                    <h4>Beliebte Leistungen</h4>
                    <div class="footer-links">
                        <a href="<?php echo esc_url( home_url( '/service/freie-werkstatt' ) ); ?>"><i class="fa-solid fa-chevron-right"></i> Freie Kfz-Werkstatt</a>
                        <a href="<?php echo esc_url( home_url( '/service/tuev-hu-au' ) ); ?>"><i class="fa-solid fa-chevron-right"></i> HU / AU &amp; TÜV-Check</a>
                        <a href="<?php echo esc_url( home_url( '/service/autoglas-scheibenreparatur' ) ); ?>"><i class="fa-solid fa-chevron-right"></i> Autoglas &amp; Steinschlag</a>
                        <a href="<?php echo esc_url( home_url( '/service/karosserie-lackiererei' ) ); ?>"><i class="fa-solid fa-chevron-right"></i> Karosserie &amp; Lack</a>
                        <a href="<?php echo esc_url( home_url( '/service/reifenservice-raederwechsel' ) ); ?>"><i class="fa-solid fa-chevron-right"></i> Reifenservice &amp; Räder</a>
                        <a href="<?php echo esc_url( home_url( '/service/bremsenservice-fahrwerk' ) ); ?>"><i class="fa-solid fa-chevron-right"></i> Bremsen &amp; Fahrwerk</a>
                        <a href="<?php echo esc_url( home_url( '/service/kfz-elektrik-elektronik' ) ); ?>"><i class="fa-solid fa-chevron-right"></i> Kfz-Elektrik &amp; Diagnose</a>
                    </div>
                </div>

                <!-- 4. SÜTUN: RECHTLICHES (DEUTSCHLAND COMPLIANCE) -->
                <div class="footer-col">
                    <h4>Rechtliches &amp; Kontakt</h4>
                    <div class="footer-links">
                        <a href="<?php echo esc_url( home_url( '/impressum' ) ); ?>" style="font-weight:700;"><i class="fa-solid fa-scale-balanced" style="color:#f97316;"></i> Impressum (§ 5 DDG)</a>
                        <a href="<?php echo esc_url( home_url( '/datenschutz' ) ); ?>" style="font-weight:700;"><i class="fa-solid fa-lock" style="color:#10b981;"></i> Datenschutzerklärung (DSGVO)</a>
                        <a href="<?php echo esc_url( home_url( '/agb' ) ); ?>"><i class="fa-solid fa-chevron-right"></i> Allgemeine Geschäftsbedingungen</a>
                        <a href="<?php echo esc_url( home_url( '/kontakt' ) ); ?>"><i class="fa-solid fa-chevron-right"></i> Kontakt &amp; Support</a>
                    </div>
                    <a href="<?php echo esc_url( home_url( '/werkstatt-eintragen' ) ); ?>" class="footer-cta-btn" style="margin-top: 18px;">
                        <i class="fa-solid fa-plus"></i>
                        <span>Werkstatt eintragen</span>
                    </a>
                </div>

            </div>

            <!-- ALT TELİF BAR -->
            <div class="footer-bottom-bar">
                <div>
                    &copy; <?php echo date('Y'); ?> <strong>FindeWerkstatt.de</strong> • Alle Rechte vorbehalten.
                </div>
            </div>
        </div>

        <!-- MİS TEKNOLOJİ 360 BANNER BAR -->
        <div class="footer-credit-banner">
            <div class="container footer-credit-inner">
                <div class="credit-left">
                    <i class="fa-solid fa-copyright" style="color: #f97316; margin-right: 4px;"></i>
                    <span>Tasarım ve Altyapı <a href="https://misteknoloji360.com.tr/" target="_blank" rel="noopener">MİS Teknoloji 360</a> tarafından sağlanmaktadır.</span>
                </div>
                <div class="credit-right">
                    <span>Entwicklung: <a href="https://misteknoloji360.com.tr/" target="_blank" rel="noopener" class="credit-brand-link"><strong>MİS Teknoloji 360</strong> <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 11px;"></i></a></span>
                </div>
            </div>
        </div>
    </footer>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Mobile Menu Toggle
        const menuBtn = document.querySelector('.mobile-menu-toggle');
        const nav = document.querySelector('.header-nav');
        if(menuBtn && nav) {
            menuBtn.addEventListener('click', function() {
                const isActive = nav.classList.toggle('active');
                menuBtn.setAttribute('aria-expanded', isActive ? 'true' : 'false');
                if(isActive) {
                    menuBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
                    menuBtn.setAttribute('aria-label', 'Menüyü Kapat');
                } else {
                    menuBtn.innerHTML = '<i class="fa-solid fa-bars"></i>';
                    menuBtn.setAttribute('aria-label', 'Menüyü Aç');
                }
            });
        }
        // Favori Ekleme (AJAX)
        const heartIcons = document.querySelectorAll('.listing-heart');
        heartIcons.forEach(icon => {
            icon.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const postId = this.getAttribute('data-post-id');
                if(!postId) return;
                
                const iconElement = this.querySelector('i');
                const isLoggedIn = <?php echo is_user_logged_in() ? 'true' : 'false'; ?>;
                
                if(!isLoggedIn) {
                    alert('Favorilere eklemek için giriş yapmalısınız.');
                    window.location.href = '<?php echo esc_url(home_url('/giris/')); ?>';
                    return;
                }
                
                const formData = new FormData();
                formData.append('action', 'ototamir_toggle_favorite');
                formData.append('post_id', postId);
                formData.append('nonce', '<?php echo esc_js( wp_create_nonce( 'ototamir_favorite_nonce' ) ); ?>');
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        if(data.data.status === 'added') {
                            iconElement.classList.remove('fa-regular');
                            iconElement.classList.add('fa-solid');
                            this.style.color = '#ef4444';
                        } else {
                            iconElement.classList.remove('fa-solid');
                            iconElement.classList.add('fa-regular');
                            this.style.color = 'white';
                        }
                    } else {
                        alert(data.data);
                    }
                });
            });
        });
    });
    </script>
    <?php wp_footer(); ?>
</body>
</html>