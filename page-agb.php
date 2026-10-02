<?php
/** Template Name: Nutzungsbedingungen */
$details = findewerkstatt_get_site_details();
get_header();
?>
<main id="main-content" class="fw-container fw-page-content">
<article class="fw-box fw-form-wrap-wide">
    <h1><?php echo esc_html( findewerkstatt_t( 'Nutzungsbedingungen' ) ); ?></h1>
    <?php get_template_part( 'template-parts/legal-navigation' ); ?>
    <div class="fw-prose">
        <h2><?php echo esc_html( findewerkstatt_t( '1. Angebot und Betreiber' ) ); ?></h2>
        <p><?php echo esc_html( findewerkstatt_t( 'FindeWerkstatt.de wird von' ) ); ?> <?php echo esc_html( $details['name'] ); ?> <?php echo esc_html( findewerkstatt_t( 'betrieben. Das Verzeichnis unterstützt die Suche nach Kfz-Werkstätten, Autoservices und Pannendiensten in Deutschland. Betriebsprofile und Standortseiten stellen Informationen und direkte Kontaktmöglichkeiten bereit. Die Betreiberangaben finden Sie im' ) ); ?> <a href="<?php echo esc_url( findewerkstatt_page_url( 'impressum' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Impressum' ) ); ?></a>.</p>

        <h2><?php echo esc_html( findewerkstatt_t( '2. Kostenlose Suche und Einträge' ) ); ?></h2>
        <p><?php echo esc_html( findewerkstatt_t( 'Die Werkstattsuche ist kostenlos und erfordert kein Benutzerkonto. Werkstattinhaber können ein kostenloses Konto erstellen und im kostenlosen Paket innerhalb der geltenden Eintragsgrenze Betriebe zur Prüfung einreichen. Die aktuellen Eintragsgrenzen finden Sie auf der' ) ); ?> <a href="<?php echo esc_url( findewerkstatt_page_url( 'pakete' ) ); ?>">Paketseite</a><?php echo esc_html( findewerkstatt_t( '. Ein Eintrag begründet keinen Anspruch auf eine bestimmte Platzierung, Sichtbarkeit oder Anzahl von Anfragen.' ) ); ?></p>

        <h2><?php echo esc_html( findewerkstatt_t( '3. Anmeldung, Prüfung und Aktualisierung' ) ); ?></h2>
        <p><?php echo esc_html( findewerkstatt_t( 'Eine Betriebsanmeldung darf nur durch den Inhaber oder eine dazu berechtigte Person erfolgen. Bitte machen Sie zutreffende Angaben und verwenden Sie nur Texte und Bilder, zu deren Nutzung Sie berechtigt sind. Die Anmeldung wird vor einer Veröffentlichung geprüft; durch das Absenden wird der Eintrag noch nicht freigeschaltet.' ) ); ?></p>
        <p><?php echo esc_html( findewerkstatt_t( 'Irreführende, rechtswidrige, unvollständige oder doppelte Anmeldungen können abgelehnt werden. Änderungen an Kontaktdaten, Standort oder Leistungen teilen Sie uns bitte mit. Die übernommenen Betriebsangaben stammen ursprünglich aus öffentlich zugänglichen Unternehmenseinträgen bei Google Maps und wurden über das bisherige Verzeichnis übernommen.' ) ); ?></p>
        <p><?php echo esc_html( findewerkstatt_t( 'Ein vorhandenes Profil wird Ihrem Konto erst nach Prüfung Ihrer Vertretungsberechtigung zugeordnet. Änderungen an zugeordneten Einträgen können Sie im Konto einreichen; sie werden vor der Übernahme geprüft. Auch noch nicht veröffentlichte Einträge zählen zur Paketgrenze. Schützen Sie Ihre Zugangsdaten und teilen Sie uns einen vermuteten unbefugten Zugriff mit.' ) ); ?></p>
        <p><?php echo esc_html( findewerkstatt_t( 'Sie können die öffentliche Anzeige eines zugeordneten, veröffentlichten Profils im Konto pausieren. Der Eintrag bleibt gespeichert, Ihrem Konto zugeordnet und zählt weiterhin zur Paketgrenze. Wenn Sie ihn erneut zur Prüfung einreichen, wird er erst nach Freigabe wieder veröffentlicht.' ) ); ?></p>

        <h2><?php echo esc_html( findewerkstatt_t( '4. Kontaktaufnahme und Werkstattaufträge' ) ); ?></h2>
        <p><?php echo esc_html( findewerkstatt_t( 'Termine, Angebote, Preise, Zahlungsbedingungen, Verfügbarkeit und Leistungsumfang klären Sie direkt mit dem jeweiligen Betrieb. Ein Anruf, ein externer Link oder das Öffnen von WhatsApp stellt über FindeWerkstatt.de keine verbindliche Buchung dar. Das Kontaktformular dieser Website richtet sich an den Verzeichnisbetreiber.' ) ); ?></p>
        <p><?php echo esc_html( findewerkstatt_t( 'Werkstatt- und Dienstleistungsverträge kommen zwischen Ihnen und dem Betrieb zustande. Das Verzeichnis ersetzt keine Diagnose am Fahrzeug. Öffnungszeiten können beispielsweise an Feiertagen abweichen. Die Erreichbarkeit einer Telefonnummer über WhatsApp wird nicht technisch bestätigt.' ) ); ?></p>

        <h2><?php echo esc_html( findewerkstatt_t( '5. Darstellung von Profilen' ) ); ?></h2>
        <p><?php echo esc_html( findewerkstatt_t( 'Die Aufnahme in das Verzeichnis ist keine Empfehlung oder Garantie für Qualifikation, Preise oder Ausführungsqualität. Bewertungen und besondere Kennzeichnungen werden nur bei entsprechend hinterlegten Angaben angezeigt. Als „Symbolbild“ bezeichnete Motive sind allgemeine Illustrationen und keine Fotos des Betriebs.' ) ); ?></p>

        <h2><?php echo esc_html( findewerkstatt_t( '6. Fehler, rechtswidrige Inhalte und Löschungswünsche' ) ); ?></h2>
        <p><?php echo esc_html( findewerkstatt_t( 'Korrekturen, Hinweise auf rechtswidrige Inhalte und Wünsche zur Entfernung eines Eintrags können Sie über das' ) ); ?> <a href="<?php echo esc_url( findewerkstatt_page_url( 'kontakt' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Kontaktformular' ) ); ?></a> <?php echo esc_html( findewerkstatt_t( 'oder an' ) ); ?> <a href="mailto:<?php echo esc_attr( $details['email'] ); ?>"><?php echo esc_html( $details['email'] ); ?></a> <?php echo esc_html( findewerkstatt_t( 'senden. Bitte nennen Sie den Betrieb, den Ort und nach Möglichkeit die Adresse der betroffenen Profilseite.' ) ); ?></p>
        <p><?php echo esc_html( findewerkstatt_t( 'Wir prüfen Ihr Anliegen. Bei Änderungen im Namen eines Betriebs können wir einen angemessenen Nachweis Ihrer Berechtigung anfordern, um missbräuchliche Änderungen zu verhindern. Datenschutzrechte können betroffene Personen unabhängig davon geltend machen.' ) ); ?></p>
        <p><?php echo esc_html( findewerkstatt_t( 'Betriebsinhaber und hierzu berechtigte Personen können auch die Entfernung ihres Eintrags und die Löschung der zugehörigen Betriebsdaten aus unserer aktiven Datenbank beantragen. Nach Prüfung Ihrer Berechtigung entfernen wir den Eintrag und löschen die Daten, soweit keine gesetzlichen Aufbewahrungspflichten entgegenstehen. Angaben zu Sicherungskopien und Ihren Datenschutzrechten finden Sie in unserer' ) ); ?> <a href="<?php echo esc_url( findewerkstatt_page_url( 'datenschutz' ) ); ?>#bestehende-firmendaten"><?php echo esc_html( findewerkstatt_t( 'Datenschutzerklärung' ) ); ?></a>.</p>

        <h2><?php echo esc_html( findewerkstatt_t( '7. Gesetzliche Rechte und Datenschutz' ) ); ?></h2>
        <p><?php echo esc_html( findewerkstatt_t( 'Gesetzliche Ansprüche und zwingende Haftungsregeln bleiben unberührt. Die Verarbeitung personenbezogener Daten und Ihre Rechte erläutern wir in der' ) ); ?> <a href="<?php echo esc_url( findewerkstatt_page_url( 'datenschutz' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Datenschutzerklärung' ) ); ?></a><?php echo esc_html( findewerkstatt_t( '. Diese Bedingungen ändern keine Vereinbarung, die Sie mit einem gelisteten Betrieb treffen.' ) ); ?></p>
        <p><?php echo esc_html( findewerkstatt_t( 'Wir nehmen nicht freiwillig an einem Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teil. Etwaige gesetzliche Verpflichtungen bleiben unberührt. Für Beschwerden erreichen Sie uns über die im Impressum genannten Kontaktmöglichkeiten.' ) ); ?></p>
        <h2><?php echo esc_html( findewerkstatt_t( '8. Pakete und unverbindliche Paketwechsel' ) ); ?></h2>
        <p><?php echo esc_html( findewerkstatt_t( 'Zur Verfügung stehen die Pakete Kostenlos, Plus und Professional. Bei der Kontoerstellung ist keine Paketauswahl erforderlich; jedes neue Konto erhält zunächst die kostenlose Eintragsgrenze. Das passende Paket wählen Sie beim Eintragen Ihres Betriebs. Für Plus und Professional sind die Preise noch nicht festgelegt. Eine Paketanfrage ist unverbindlich, wird manuell geprüft und bewirkt weder eine automatische kostenpflichtige Buchung noch eine Zahlung. Etwaige spätere Preise, Zahlungszeiträume und Vertragsbedingungen werden vor einer kostenpflichtigen Vereinbarung gesondert mitgeteilt.' ) ); ?></p>
        <p><?php echo esc_html( findewerkstatt_t( 'Bis zur Freigabe einer Paketanfrage bleibt das bisherige Paket aktiv. Eine Paketfreigabe ist keine Bestätigung der Qualifikation des Betriebs und führt nicht automatisch zu einer bevorzugten Platzierung. Hat ein Konto nach einem Paketwechsel mehr Einträge als das neue Paket erlaubt, bleiben die vorhandenen Einträge erhalten; weitere Einträge können erst innerhalb der geltenden Grenze hinzugefügt werden.' ) ); ?></p>
    </div>
</article>
</main>
<?php get_footer(); ?>
