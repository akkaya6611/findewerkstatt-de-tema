<?php
/** Template Name: Über uns */
get_header();
?>
<main id="main-content" class="fw-container fw-page-content"><article class="fw-box fw-form-wrap fw-form-wrap-wide">
    <div class="fw-page-heading"><h1><?php echo esc_html( findewerkstatt_t( 'Über FindeWerkstatt.de' ) ); ?></h1></div>
    <div class="fw-prose">
        <p><?php echo esc_html( findewerkstatt_t( 'FindeWerkstatt.de ist ein Verzeichnis für Kfz-Werkstätten und Autoreparaturbetriebe in Deutschland. Wir führen veröffentlichte Werkstattprofile zusammen, damit Sie Leistungen, Standort und Kontaktdaten vergleichen können.' ) ); ?></p>
        <h2><?php echo esc_html( findewerkstatt_t( 'Passende Betriebe finden' ) ); ?></h2>
        <p><?php echo esc_html( findewerkstatt_t( 'Wählen Sie zuerst ein Bundesland und anschließend Ihre Stadt oder Ihren Ort. Sie können auch im gesamten Bundesland suchen und die Ergebnisse nach Leistung oder Fahrzeugmarke eingrenzen. Die Profile zeigen die Angaben des jeweiligen Betriebs. Termine, Preise und die Verfügbarkeit klären Sie direkt mit der Werkstatt.' ) ); ?></p>
        <h2><?php echo esc_html( findewerkstatt_t( 'Ein Verzeichnis, das wächst' ) ); ?></h2>
        <p><?php echo esc_html( findewerkstatt_t( 'Werkstattinhaber können ihren Betrieb kostenlos anmelden. Neue Einträge werden vor der Veröffentlichung geprüft. Wenn in einer Region noch kein Betrieb eingetragen ist, zeigen wir das offen an.' ) ); ?></p>
        <h2><?php echo esc_html( findewerkstatt_t( 'Wer hinter dem Portal steht' ) ); ?></h2>
        <p><?php echo esc_html( sprintf( findewerkstatt_t( 'FindeWerkstatt.de wird von %s betrieben.' ), findewerkstatt_get_site_details()['name'] ) ); ?> <?php echo esc_html( findewerkstatt_t( 'Die Angaben zum Betreiber finden Sie im Impressum.' ) ); ?> <a href="<?php echo esc_url( findewerkstatt_page_url( 'impressum' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Impressum' ) ); ?></a></p>
    </div>
    <div class="fw-empty-actions"><a class="fw-btn fw-btn-primary" href="<?php echo esc_url( get_post_type_archive_link( 'mechanic' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Werkstätten durchsuchen' ) ); ?></a> <a class="fw-btn fw-btn-outline" href="<?php echo esc_url( findewerkstatt_page_url( 'werkstatt-anmelden' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Werkstatt eintragen' ) ); ?></a> <a class="fw-btn fw-btn-outline" href="<?php echo esc_url( findewerkstatt_page_url( 'kontakt' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Kontakt aufnehmen' ) ); ?></a></div>
</article></main>
<?php get_footer(); ?>
