<?php
/**
 * FindeWerkstatt.de — Header Template
 * 
 * @package FindeWerkstatt
 * @version 2.0.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo esc_url( get_template_directory_uri() . '/assets/images/favicon-32x32.png' ); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo esc_url( get_template_directory_uri() . '/assets/images/apple-touch-icon.png' ); ?>">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="fw-header">
    <div class="fw-header-top">
        <div class="fw-container">
            <div>
                <span>🇩🇪 Deutschlands Kfz-Portal</span> — Geprüfte Meisterbetriebe & 24h Pannenhilfe
            </div>
            <div>
                <a href="<?php echo esc_url( home_url( '/werkstatt-anmelden/' ) ); ?>">Für Werkstattinhaber: Betrieb eintragen</a>
            </div>
        </div>
    </div>

    <div class="fw-container">
        <div class="fw-header-main">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="fw-logo" title="FindeWerkstatt.de — Finde Deine Werkstatt in Sekunden">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>" 
                     alt="FindeWerkstatt.de — Finde Deine Werkstatt in Sekunden" 
                     class="fw-logo-img" 
                     width="220" 
                     height="45">
            </a>

            <nav class="fw-nav">
                <a href="<?php echo esc_url( home_url( '/werkstaetten/' ) ); ?>">Alle Werkstätten</a>
                <a href="<?php echo esc_url( home_url( '/service/tuev-hu-au/' ) ); ?>">TÜV & HU</a>
                <a href="<?php echo esc_url( home_url( '/service/abschleppdienst-pannenhilfe/' ) ); ?>">24h Pannenhilfe</a>
                <a href="<?php echo esc_url( home_url( '/#bundeslaender' ) ); ?>">Bundesländer</a>
            </nav>

            <div class="fw-header-cta">
                <a href="<?php echo esc_url( home_url( '/werkstatt-anmelden/' ) ); ?>" class="fw-btn fw-btn-primary fw-btn-sm">
                    + Werkstatt eintragen
                </a>
                <button class="fw-menu-toggle" aria-label="Menü öffnen">☰</button>
            </div>
        </div>
    </div>
</header>