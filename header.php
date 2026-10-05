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
    <link rel="manifest" href="<?php echo esc_url( get_template_directory_uri() . '/assets/manifest.json' ); ?>">
    <meta name="theme-color" content="#05295d">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="fw-header">
    <div class="fw-header-top">
        <div class="fw-container">
            <div>
                <span>🇩🇪 <?php echo esc_html( findewerkstatt_t( 'Deutschlands Kfz-Portal' ) ); ?></span> — <?php echo esc_html( findewerkstatt_t( 'Geprüfte Meisterbetriebe & 24h Pannenhilfe' ) ); ?>
            </div>
            <div>
                <a href="<?php echo esc_url( function_exists( 'findewerkstatt_page_url' ) ? findewerkstatt_page_url( 'werkstatt-anmelden' ) : home_url( '/werkstatt-anmelden/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Für Werkstattinhaber: Betrieb eintragen' ) ); ?></a>
            </div>
        </div>
    </div>

    <div class="fw-container">
        <div class="fw-header-main">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="fw-logo" title="FindeWerkstatt.de — <?php echo esc_attr( findewerkstatt_t( 'Finde deine Werkstatt in Sekunden.' ) ); ?>">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>" 
                     alt="FindeWerkstatt.de — <?php echo esc_attr( findewerkstatt_t( 'Finde deine Werkstatt in Sekunden.' ) ); ?>" 
                     class="fw-logo-img fw-logo-image" 
                     width="250" 
                     height="52">
            </a>

            <nav class="fw-nav">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Startseite' ) ); ?></a>
                <a href="<?php echo esc_url( home_url( '/werkstaetten/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Werkstätten' ) ); ?></a>
                <a href="<?php echo esc_url( home_url( '/service/tuev-hu-au/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'TÜV & HU' ) ); ?></a>
                <a href="<?php echo esc_url( home_url( '/service/abschleppdienst-pannenhilfe/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( '24h Pannenhilfe' ) ); ?></a>
                <a href="<?php echo esc_url( home_url( '/#bundeslaender' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Bundesländer' ) ); ?></a>
                <a href="<?php echo esc_url( function_exists( 'findewerkstatt_page_url' ) ? findewerkstatt_page_url( 'pakete' ) : home_url( '/pakete/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Pakete' ) ); ?></a>
                <a href="<?php echo esc_url( function_exists( 'findewerkstatt_page_url' ) ? findewerkstatt_page_url( 'kontakt' ) : home_url( '/kontakt/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Kontakt' ) ); ?></a>
            </nav>

            <div class="fw-header-cta">
                <!-- Sprachauswahl (Language Switcher: DE, TR, EN, RU) -->
                <?php if ( function_exists( 'findewerkstatt_languages' ) ) : 
                    $current_lang = findewerkstatt_language();
                    $languages = findewerkstatt_languages();
                    $flag_icons = array( 'de' => '🇩🇪', 'tr' => '🇹🇷', 'en' => '🇬🇧', 'ru' => '🇷🇺' );
                ?>
                <details class="fw-language-menu">
                    <summary class="fw-language-menu-toggle" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Sprache auswählen' ) ); ?>">
                        <span><?php echo $flag_icons[ $current_lang ] ?? '🌐'; ?></span>
                        <span class="fw-language-menu-code"><?php echo esc_html( strtoupper( $current_lang ) ); ?></span>
                        <svg class="fw-account-menu-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
                    </summary>
                    <div class="fw-language-dropdown">
                        <?php foreach ( $languages as $code => $data ) : ?>
                            <a href="<?php echo esc_url( findewerkstatt_language_url( $code ) ); ?>" <?php if ( $code === $current_lang ) echo 'aria-current="true"'; ?>>
                                <span><?php echo $flag_icons[ $code ] ?? '🌐'; ?> <?php echo esc_html( $data['label'] ); ?></span>
                                <span style="font-size:11px; font-weight:700; opacity:0.6; text-transform:uppercase;"><?php echo esc_html( $code ); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </details>
                <?php endif; ?>

                <!-- Benutzerkonto / Anmelden Menu -->
                <?php if ( function_exists( 'findewerkstatt_page_url' ) ) : ?>
                    <?php if ( is_user_logged_in() ) : 
                        $current_user = wp_get_current_user();
                        $initial = mb_substr( $current_user->display_name ?: $current_user->user_login, 0, 1 );
                    ?>
                        <details class="fw-account-menu">
                            <summary class="fw-account-menu-toggle" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Mein Konto' ) ); ?>" title="<?php echo esc_attr( findewerkstatt_t( 'Mein Konto' ) ); ?>">
                                <span class="fw-account-menu-avatar"><?php echo esc_html( strtoupper( $initial ) ); ?></span>
                                <span class="fw-account-menu-label"><?php echo esc_html( wp_trim_words( $current_user->display_name, 1, '' ) ); ?></span>
                                <svg class="fw-account-menu-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
                            </summary>
                            <div class="fw-account-dropdown">
                                <div class="fw-account-dropdown-title">
                                    <strong><?php echo esc_html( $current_user->display_name ); ?></strong><br>
                                    <span style="font-size:11px; color:#64748b;"><?php echo esc_html( $current_user->user_email ); ?></span>
                                </div>
                                <a href="<?php echo esc_url( findewerkstatt_page_url( 'mein-konto' ) ); ?>">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    <?php echo esc_html( findewerkstatt_t( 'Mein Konto' ) ); ?>
                                </a>
                                <a href="<?php echo esc_url( findewerkstatt_page_url( 'werkstatt-anmelden' ) ); ?>">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                                    <?php echo esc_html( findewerkstatt_t( 'Werkstatt eintragen' ) ); ?>
                                </a>
                                <div class="fw-account-dropdown-logout">
                                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                                        <?php if ( function_exists( 'findewerkstatt_language_field' ) ) findewerkstatt_language_field(); ?>
                                        <input type="hidden" name="action" value="fw_member_logout">
                                        <?php wp_nonce_field( 'fw_member_logout', 'fw_member_nonce' ); ?>
                                        <button type="submit">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                                            <?php echo esc_html( findewerkstatt_t( 'Abmelden' ) ); ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </details>
                    <?php else : ?>
                        <details class="fw-account-menu">
                            <summary class="fw-account-menu-toggle" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Kontozugang' ) ); ?>" title="<?php echo esc_attr( findewerkstatt_t( 'Anmelden' ) ); ?>">
                                <span class="fw-account-menu-avatar" aria-hidden="true">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </span>
                                <span class="fw-account-menu-label"><?php echo esc_html( findewerkstatt_t( 'Anmelden' ) ); ?></span>
                                <svg class="fw-account-menu-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
                            </summary>
                            <div class="fw-account-dropdown">
                                <a href="<?php echo esc_url( findewerkstatt_page_url( 'mein-konto' ) ); ?>">
                                    <?php echo esc_html( findewerkstatt_t( 'Anmelden' ) ); ?>
                                </a>
                                <a href="<?php echo esc_url( findewerkstatt_page_url( 'mein-konto' ) ); ?>" class="fw-account-dropdown-signup">
                                    <?php echo esc_html( findewerkstatt_t( 'Kostenlos registrieren' ) ); ?>
                                </a>
                            </div>
                        </details>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Werkstatt eintragen CTA -->
                <a href="<?php echo esc_url( function_exists( 'findewerkstatt_page_url' ) ? findewerkstatt_page_url( 'werkstatt-anmelden' ) : home_url( '/werkstatt-anmelden/' ) ); ?>" class="fw-header-add">
                    <span>+</span> <?php echo esc_html( findewerkstatt_t( 'Werkstatt eintragen' ) ); ?>
                </a>

                <button class="fw-menu-toggle" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Menü öffnen' ) ); ?>" aria-expanded="false">☰</button>
            </div>
        </div>
    </div>
</header>