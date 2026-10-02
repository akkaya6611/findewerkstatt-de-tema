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
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="fw-footer-logo" title="FindeWerkstatt.de — Finde Deine Werkstatt in Sekunden">
                    <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo-white.png' ); ?>" 
                         alt="FindeWerkstatt.de" 
                         class="fw-footer-logo-img" 
                         width="210" 
                         height="43">
                </a>
                <p style="font-size:14px; line-height:1.6; margin-bottom:16px;">
                    Deutschlands modernes Verzeichnis für Kfz-Meisterbetriebe, freie Werkstätten, TÜV-Prüfstellen und 24h-Pannenhilfen. Unabhängig, transparent und geprüft.
                </p>
                <div style="font-size:13px; color:#94a3b8;">
                    🛡️ Geprüfte Partnerbetriebe in allen 16 Bundesländern
                </div>
            </div>

            <div class="fw-footer-col">
                <h4>Top Städte</h4>
                <ul>
                    <li><a href="<?php echo esc_url( home_url( '/stadt/berlin/' ) ); ?>">Werkstätten in Berlin</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/stadt/hamburg/' ) ); ?>">Werkstätten in Hamburg</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/stadt/muenchen/' ) ); ?>">Werkstätten in München</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/stadt/koeln/' ) ); ?>">Werkstätten in Köln</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/stadt/frankfurt-am-main/' ) ); ?>">Werkstätten in Frankfurt</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/stadt/stuttgart/' ) ); ?>">Werkstätten in Stuttgart</a></li>
                </ul>
            </div>

            <div class="fw-footer-col">
                <h4>Kfz-Leistungen</h4>
                <ul>
                    <li><a href="<?php echo esc_url( home_url( '/service/freie-werkstatt/' ) ); ?>">Freie Werkstatt</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/service/tuev-hu-au/' ) ); ?>">TÜV / HU & AU</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/service/abschleppdienst-pannenhilfe/' ) ); ?>">24h Abschleppdienst</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/service/autoglas-scheibenreparatur/' ) ); ?>">Autoglas & Scheiben</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/service/bremsenservice-fahrwerk/' ) ); ?>">Bremsenservice</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/service/reifenservice-raederwechsel/' ) ); ?>">Reifenservice</a></li>
                </ul>
            </div>

            <div class="fw-footer-col">
                <h4>Informationen</h4>
                <ul>
                    <li><a href="<?php echo esc_url( home_url( '/werkstatt-anmelden/' ) ); ?>">Werkstatt eintragen</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>">Kontakt & Support</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/impressum/' ) ); ?>">Impressum (§ 5 DDG)</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/datenschutz/' ) ); ?>">Datenschutz (DSGVO)</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/agb/' ) ); ?>">AGB & Richtlinien</a></li>
                </ul>
            </div>
        </div>

        <div class="fw-footer-bottom">
            <div>
                &copy; <?php echo date( 'Y' ); ?> FindeWerkstatt.de — Alle Rechte vorbehalten.
            </div>
            <div class="fw-footer-bottom-links">
                <a href="<?php echo esc_url( home_url( '/impressum/' ) ); ?>">Impressum</a>
                <a href="<?php echo esc_url( home_url( '/datenschutz/' ) ); ?>">Datenschutz</a>
                <a href="<?php echo esc_url( home_url( '/agb/' ) ); ?>">AGB</a>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>