<!DOCTYPE html>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['page_views'] = ($_SESSION['page_views'] ?? 0) + 1;
?>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google-site-verification" content="_BNSH-EIkJeIPDmudyH_LDIbXNv1mifvtn8Ld5GpTHI" />
    
    <!-- DNS-Prefetch ve Preconnect (Arama motoru ve kritik kaynaklar için) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="//www.google.com">
    <link rel="dns-prefetch" href="//api.indexnow.org">

    <!-- Preload Ana Tema CSS (Render-blocking engelleme) -->
    <link rel="preload" as="style" href="<?php echo esc_url( get_stylesheet_uri() ); ?>?ver=1.5.1">

    <!-- Google Fonts: display=swap → Metin anında çizilir, FOIT/LCP gecikmesini sıfırlar -->
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"></noscript>

    <!-- FontAwesome SOLID subset (solid+brands only = ~70KB yerine 300KB) - render blocking yok -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/solid.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/brands.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/fontawesome.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/solid.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/brands.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/fontawesome.min.css">
    </noscript>

    <!-- Favicon & Mobil Simge Tanımları -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo esc_url( get_template_directory_uri() . '/assets/images/favicon-32x32.png' ); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo esc_url( get_template_directory_uri() . '/assets/images/apple-touch-icon.png' ); ?>">

    <?php wp_head(); ?>
    <style>
    .site-logo { display: flex; align-items: center; }
    .site-logo a { display: block; line-height: 0; }
    .site-logo img {
        height: 48px;
        width: auto;
        max-width: 260px;
        aspect-ratio: 248 / 48;
        object-fit: contain;
        display: block;
        image-rendering: -webkit-optimize-contrast;
    }
    /* ========== MOBİL LCP VE FCP HIZLANDIRICI ========== */
    @media (max-width: 768px) {
        body, h1, h2, h3, h4, p, span, a, strong, button, input, select {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
        }
        .header-actions  { display: none !important; }
        .header-nav      { display: none !important; }
        .header-nav.active { display: flex !important; }
        .mobile-menu-toggle { display: flex !important; }
        .header-inner    { height: 64px !important; }
        .site-logo img   { height: 36px !important; max-width: 200px !important; aspect-ratio: 515 / 100 !important; }
    }
    .nav-item-mobile-only {
        display: none !important;
    }
    @media (max-width: 768px) {
        .header-nav.active .nav-item-mobile-only {
            display: flex !important;
            align-items: center;
            gap: 8px;
        }
    }
    </style>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header" role="banner">
    <div class="container header-inner">

        <!-- LOGO (FindeWerkstatt.de) -->
        <div class="site-logo">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="FindeWerkstatt.de Startseite" style="text-decoration:none; display:flex; align-items:center;">
                <?php 
                $custom_logo_id = get_theme_mod( 'custom_logo' );
                if ( $custom_logo_id ) {
                    $logo_img = wp_get_attachment_image_src( $custom_logo_id, 'full' );
                    if ( $logo_img ) {
                        echo '<img src="' . esc_url( $logo_img[0] ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" width="' . esc_attr( $logo_img[1] ) . '" height="' . esc_attr( $logo_img[2] ) . '" fetchpriority="high" decoding="sync">';
                    }
                } else {
                ?>
                <span style="font-size:24px; font-weight:900; color:#0f172a; display:inline-flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-wrench" style="color:#f97316;"></i> Finde<span style="color:#ea580c;">Werkstatt</span><span style="font-size:15px; color:#64748b; font-weight:600;">.de</span>
                </span>
                <?php } ?>
            </a>
        </div>

        <!-- MASAÜSTÜ & MOBİL MENÜ -->
        <nav class="header-nav" role="navigation" aria-label="Hauptnavigation">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Startseite</a>
            <a href="<?php echo esc_url( home_url( '/werkstaetten' ) ); ?>">Werkstätten</a>
            <a href="<?php echo esc_url( home_url( '/24h-pannenhilfe' ) ); ?>" style="color:#dc2626; font-weight:700;"><i class="fa-solid fa-truck-pickup"></i> 24h Notdienst</a>
            <a href="<?php echo esc_url( home_url( '/staedte' ) ); ?>">Städte</a>
            <a href="<?php echo esc_url( home_url( '/ladestationen' ) ); ?>">E-Ladestationen</a>
            <a href="<?php echo esc_url( home_url( '/kontakt' ) ); ?>">Kontakt</a>

            <!-- Mobil Menü İçi Butonlar (sadece mobil açık menüde görünür) -->
            <div class="mobile-nav-actions">
                <div class="mobile-nav-quick-tools" style="display:flex; flex-direction:column; gap:8px; margin-bottom:12px; width:100%;">
                    <a href="<?php echo esc_url( home_url( '/fehlerdiagnose' ) ); ?>" style="background:#fff7ed; color:#ea580c; border:1px solid #fed7aa; padding:10px 14px; border-radius:10px; font-weight:700; text-decoration:none; display:flex; align-items:center; gap:8px; font-size:13.5px;">
                        <i class="fa-solid fa-triangle-exclamation"></i> KI-Fehlerdiagnose (OBD-II)
                    </a>
                    <a href="<?php echo esc_url( home_url( '/24h-pannenhilfe' ) ); ?>" style="background:#fef2f2; color:#dc2626; border:1px solid #fecaca; padding:10px 14px; border-radius:10px; font-weight:700; text-decoration:none; display:flex; align-items:center; gap:8px; font-size:13.5px;">
                        <i class="fa-solid fa-truck-pickup"></i> 24h Abschleppdienst &amp; Notruf
                    </a>
                </div>
                <?php if ( is_user_logged_in() ) : ?>
                    <a href="<?php echo esc_url( home_url( '/mein-konto' ) ); ?>" class="mobile-nav-user">
                        <i class="fa-solid fa-user"></i> Mein Konto
                    </a>
                    <a href="<?php echo esc_url( home_url( '/werkstatt-eintragen' ) ); ?>" class="btn-primary mobile-nav-cta"><i class="fa-solid fa-plus"></i> Werkstatt eintragen</a>
                <?php else : ?>
                    <div class="mobile-nav-auth">
                        <a href="<?php echo esc_url( home_url( '/anmelden' ) ); ?>" class="mobile-nav-login">Anmelden</a>
                        <a href="<?php echo esc_url( home_url( '/registrieren' ) ); ?>" class="mobile-nav-register">Registrieren</a>
                    </div>
                    <a href="<?php echo esc_url( home_url( '/werkstatt-eintragen' ) ); ?>" class="btn-primary mobile-nav-cta"><i class="fa-solid fa-plus"></i> Werkstatt eintragen</a>
                <?php endif; ?>
            </div>
        </nav>

        <!-- MASAÜSTÜ BUTONLAR -->
        <div class="header-actions">
            <?php if ( is_user_logged_in() ) : ?>
                <a href="<?php echo esc_url( home_url( '/mein-konto' ) ); ?>" style="color:#c2410c; font-weight:600; text-decoration:none; font-size:15px; white-space:nowrap;">
                    <i class="fa-solid fa-user"></i> Mein Konto
                </a>
                <a href="<?php echo esc_url( home_url( '/werkstatt-eintragen' ) ); ?>" class="btn-primary"><i class="fa-solid fa-plus"></i> Werkstatt eintragen</a>
            <?php else : ?>
                <a href="<?php echo esc_url( home_url( '/anmelden' ) ); ?>" style="font-weight:600; text-decoration:none; font-size:15px; color:var(--text-main); white-space:nowrap;">Anmelden</a>
                <a href="<?php echo esc_url( home_url( '/registrieren' ) ); ?>" style="color:#c2410c; font-weight:600; text-decoration:none; font-size:15px; white-space:nowrap;">Registrieren</a>
                <a href="<?php echo esc_url( home_url( '/werkstatt-eintragen' ) ); ?>" class="btn-primary" style="white-space:nowrap;"><i class="fa-solid fa-plus"></i> Werkstatt eintragen</a>
            <?php endif; ?>
        </div>

        <!-- MOBİL HAMBURGER (Sağ köşe) -->
        <button class="mobile-menu-toggle" aria-label="Menü öffnen">
            <i class="fa-solid fa-bars"></i>
        </button>

    </div>
</header>

<?php 
// Google AdSense Header Altı Banner (Leaderboard) - Ana sayfada kahraman arama kutusunu ekran dışına itmemesi için alt sayfalarda gösterilir
if ( function_exists( 'ototamir_render_ad' ) && ! is_front_page() && ! is_home() ) {
    echo ototamir_render_ad( 'header' );
}
?>

<main id="primary" class="site-main" role="main">