<?php
/** Template Name: Impressum */
$details = findewerkstatt_get_site_details();
get_header();
?>
<main id="main-content" class="fw-container fw-page-content">
<article class="fw-box fw-form-wrap-wide">
    <h1><?php echo esc_html( findewerkstatt_t( 'Impressum' ) ); ?></h1>
    <?php get_template_part( 'template-parts/legal-navigation' ); ?>
    <div class="fw-prose">
        <h2><?php echo esc_html( findewerkstatt_t( 'Betreiber und Kontakt' ) ); ?></h2>
        <p><strong><?php echo esc_html( $details['name'] ); ?></strong><br><?php echo nl2br( esc_html( $details['address'] ) ); ?></p>
        <p>E-Mail: <a href="mailto:<?php echo esc_attr( $details['email'] ); ?>"><?php echo esc_html( $details['email'] ); ?></a><?php if ( $details['phone'] ) : ?><br><?php echo esc_html( findewerkstatt_t( 'Telefon:' ) ); ?> <?php echo esc_html( $details['phone'] ); ?><?php endif; ?><br><a href="<?php echo esc_url( findewerkstatt_page_url( 'kontakt' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Kontaktformular für Anfragen und Hinweise' ) ); ?></a></p>
        <?php if ( $details['register'] ) : ?><h2><?php echo esc_html( findewerkstatt_t( 'Registerangaben' ) ); ?></h2><p><?php echo esc_html( $details['register'] ); ?></p><?php endif; ?>
        <?php if ( $details['vat'] ) : ?><h2><?php echo esc_html( findewerkstatt_t( 'Steuerliche Identifikationsnummer' ) ); ?></h2><p><?php echo esc_html( $details['vat'] ); ?></p><?php endif; ?>

        <h2><?php echo esc_html( findewerkstatt_t( 'FindeWerkstatt.de als Werkstattverzeichnis' ) ); ?></h2>
        <p><?php echo esc_html( findewerkstatt_t( 'FindeWerkstatt.de listet Kfz-Werkstätten, Autoservices und Pannendienste in Deutschland. Die Suche nach Bundesland, Stadt und Ortsteil hilft Ihnen, einen Betrieb in Ihrer Nähe zu finden und direkt zu kontaktieren.' ) ); ?></p>
        <p><?php echo esc_html( findewerkstatt_t( 'Reparaturen, Prüfungen, Abschleppleistungen und Termine vereinbaren Sie mit dem jeweiligen Betrieb. Der Betreiber dieses Verzeichnisses erbringt diese Leistungen nicht selbst und ist nicht Vertragspartner eines Werkstattauftrags.' ) ); ?></p>

        <h2><?php echo esc_html( findewerkstatt_t( 'Betriebsangaben, Bilder und externe Links' ) ); ?></h2>
        <p><?php echo esc_html( findewerkstatt_t( 'Die übernommenen Betriebsangaben stammen ursprünglich aus öffentlich zugänglichen Unternehmenseinträgen bei Google Maps und wurden über das bisherige Verzeichnis übernommen. Weitere Angaben stammen aus eigenen Anmeldungen der Betriebe. Bitte bestätigen Sie aktuelle Kontaktdaten, Öffnungszeiten, Leistungen und Preise vor einer Beauftragung beim Betrieb. Als „Symbolbild“ gekennzeichnete Motive zeigen nicht die jeweilige Werkstatt.' ) ); ?></p>
        <p><strong><?php echo esc_html( findewerkstatt_t( 'Löschung auf Anfrage:' ) ); ?></strong> <?php echo esc_html( findewerkstatt_t( 'Betriebsinhaber und hierzu berechtigte Personen können die Entfernung ihres Eintrags und die Löschung der zugehörigen Betriebsdaten aus unserer aktiven Datenbank beantragen. Senden Sie uns dafür den Betriebsnamen, den Ort und nach Möglichkeit die Profiladresse über das Kontaktformular oder per E-Mail. Nach Prüfung Ihrer Berechtigung entfernen wir den Eintrag und löschen die Daten, soweit keine gesetzlichen Aufbewahrungspflichten entgegenstehen. Einzelheiten erläutert unsere' ) ); ?> <a href="<?php echo esc_url( findewerkstatt_page_url( 'datenschutz' ) ); ?>#bestehende-firmendaten"><?php echo esc_html( findewerkstatt_t( 'Datenschutzerklärung' ) ); ?></a>.</p>
        <p><?php echo esc_html( findewerkstatt_t( 'Verlinkte Betriebswebsites, Karten- und Kommunikationsdienste werden von ihren jeweiligen Anbietern betrieben. Wenn Sie einen fehlerhaften Eintrag oder einen möglicherweise rechtswidrigen Inhalt bemerken, nennen Sie uns bitte die betroffene Seite und den Grund über das Kontaktformular oder per E-Mail. Wir prüfen den Hinweis und veranlassen erforderliche Korrekturen.' ) ); ?></p>

        <h2><?php echo esc_html( findewerkstatt_t( 'Verbraucherstreitbeilegung' ) ); ?></h2>
        <p><?php echo esc_html( findewerkstatt_t( 'Wir nehmen nicht freiwillig an einem Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teil. Etwaige gesetzliche Verpflichtungen bleiben unberührt. Für Beschwerden erreichen Sie uns über die oben genannten Kontaktmöglichkeiten.' ) ); ?></p>
        <p><?php echo esc_html( findewerkstatt_t( 'Informationen zur Nutzung des Verzeichnisses finden Sie in unseren' ) ); ?> <a href="<?php echo esc_url( findewerkstatt_page_url( 'agb' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Nutzungsbedingungen' ) ); ?></a><?php echo esc_html( findewerkstatt_t( ', Informationen zur Datenverarbeitung in unserer' ) ); ?> <a href="<?php echo esc_url( findewerkstatt_page_url( 'datenschutz' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Datenschutzerklärung' ) ); ?></a>.</p>
    </div>
</article>
</main>
<?php get_footer(); ?>
