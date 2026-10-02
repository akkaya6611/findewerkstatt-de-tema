<?php
/**
 * FindeWerkstatt.de — Footer Template
 * 
 * @package FindeWerkstatt
 * @version 2.0.0
 */
?>
<footer class="fw-footer">
    <div class="fw-container">
        <div class="fw-footer-grid">
            <div class="fw-footer-col">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="fw-footer-logo" title="FindeWerkstatt.de — <?php echo esc_attr( findewerkstatt_t( 'Finde deine Werkstatt in Sekunden.' ) ); ?>">
                    <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo-white.png' ); ?>" 
                         alt="FindeWerkstatt.de" 
                         class="fw-footer-logo-img" 
                         width="210" 
                         height="43">
                </a>
                <p style="font-size:14px; line-height:1.6; margin-bottom:16px;">
                    <?php echo esc_html( findewerkstatt_t( 'Deutschlands modernes Verzeichnis für Kfz-Meisterbetriebe, freie Werkstätten, TÜV-Prüfstellen und 24h-Pannenhilfen. Unabhängig, transparent und geprüft.' ) ); ?>
                </p>
                <div style="font-size:13px; color:#94a3b8;">
                    🛡️ <?php echo esc_html( findewerkstatt_t( 'Geprüfte Partnerbetriebe in allen 16 Bundesländern' ) ); ?>
                </div>
            </div>

            <div class="fw-footer-col">
                <h4><?php echo esc_html( findewerkstatt_t( 'Top Städte' ) ); ?></h4>
                <ul>
                    <li><a href="<?php echo esc_url( home_url( '/stadt/berlin/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Werkstätten in Berlin' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/stadt/hamburg/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Werkstätten in Hamburg' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/stadt/muenchen/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Werkstätten in München' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/stadt/koeln/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Werkstätten in Köln' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/stadt/frankfurt-am-main/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Werkstätten in Frankfurt' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/stadt/stuttgart/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Werkstätten in Stuttgart' ) ); ?></a></li>
                </ul>
            </div>

            <div class="fw-footer-col">
                <h4><?php echo esc_html( findewerkstatt_t( 'Kfz-Leistungen' ) ); ?></h4>
                <ul>
                    <li><a href="<?php echo esc_url( home_url( '/service/freie-werkstatt/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Freie Werkstatt' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/service/tuev-hu-au/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'TÜV / HU & AU' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/service/abschleppdienst-pannenhilfe/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( '24h Abschleppdienst' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/service/autoglas-scheibenreparatur/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Autoglas & Scheiben' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/service/bremsenservice-fahrwerk/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Bremsenservice' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( home_url( '/service/reifenservice-raederwechsel/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Reifenservice' ) ); ?></a></li>
                </ul>
            </div>

            <div class="fw-footer-col">
                <h4><?php echo esc_html( findewerkstatt_t( 'Informationen' ) ); ?></h4>
                <ul>
                    <li><a href="<?php echo esc_url( function_exists( 'findewerkstatt_page_url' ) ? findewerkstatt_page_url( 'werkstatt-anmelden' ) : home_url( '/werkstatt-anmelden/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Werkstatt eintragen' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( function_exists( 'findewerkstatt_page_url' ) ? findewerkstatt_page_url( 'pakete' ) : home_url( '/pakete/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Pakete' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( function_exists( 'findewerkstatt_page_url' ) ? findewerkstatt_page_url( 'kontakt' ) : home_url( '/kontakt/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Kontakt' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( function_exists( 'findewerkstatt_page_url' ) ? findewerkstatt_page_url( 'impressum' ) : home_url( '/impressum/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Impressum' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( function_exists( 'findewerkstatt_page_url' ) ? findewerkstatt_page_url( 'datenschutz' ) : home_url( '/datenschutz/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Datenschutz' ) ); ?></a></li>
                    <li><a href="<?php echo esc_url( function_exists( 'findewerkstatt_page_url' ) ? findewerkstatt_page_url( 'agb' ) : home_url( '/agb/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'AGB' ) ); ?></a></li>
                </ul>
            </div>
        </div>

        <div class="fw-footer-bottom">
            <div>
                &copy; <?php echo date( 'Y' ); ?> FindeWerkstatt.de — <?php echo esc_html( findewerkstatt_t( 'Alle Rechte vorbehalten.' ) ); ?>
            </div>
            <div class="fw-footer-bottom-links">
                <?php if ( function_exists( 'findewerkstatt_languages' ) ) : ?>
                    <span style="display:inline-flex; gap:8px; margin-right:16px;">
                        <?php foreach ( findewerkstatt_languages() as $code => $data ) : ?>
                            <a href="<?php echo esc_url( findewerkstatt_language_url( $code ) ); ?>" style="<?php echo $code === findewerkstatt_language() ? 'color:#fb6006; font-weight:700;' : ''; ?>">
                                <?php echo esc_html( $data['label'] ); ?>
                            </a>
                        <?php endforeach; ?>
                    </span>
                <?php endif; ?>
                <a href="<?php echo esc_url( function_exists( 'findewerkstatt_page_url' ) ? findewerkstatt_page_url( 'impressum' ) : home_url( '/impressum/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Impressum' ) ); ?></a>
                <a href="<?php echo esc_url( function_exists( 'findewerkstatt_page_url' ) ? findewerkstatt_page_url( 'datenschutz' ) : home_url( '/datenschutz/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Datenschutz' ) ); ?></a>
                <a href="<?php echo esc_url( function_exists( 'findewerkstatt_page_url' ) ? findewerkstatt_page_url( 'agb' ) : home_url( '/agb/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'AGB' ) ); ?></a>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>