<?php
/**
 * Template Name: Kontakt
 * @package FindeWerkstatt
 */
$notice = findewerkstatt_get_form_notice( 'contact' );
$values = $notice['values'] ?? array();
$received = ! empty( $notice['received'] );
$site_details = findewerkstatt_get_site_details();
$support_email = sanitize_email( $site_details['email'] );
get_header();
?>
<main id="main-content" class="fw-container fw-form-page">
    <div class="fw-form-wrap">
        <div class="fw-section-header">
            <div class="fw-section-tag"><?php echo esc_html( findewerkstatt_t( 'Wir helfen Ihnen weiter' ) ); ?></div>
            <h1 id="fw-contact-heading">Kontakt &amp; Support</h1>
            <p><?php echo esc_html( findewerkstatt_t( 'Sie haben eine Frage zum Verzeichnis, möchten einen Eintrag korrigieren oder uns Feedback geben? Schreiben Sie uns. Für einen Werkstatttermin kontaktieren Sie bitte den jeweiligen Betrieb direkt.' ) ); ?></p>
            <?php if ( is_email( $support_email ) ) : ?><p><?php echo esc_html( findewerkstatt_t( 'Sie erreichen uns auch per E-Mail:' ) ); ?> <a href="<?php echo esc_url( 'mailto:' . $support_email ); ?>"><?php echo esc_html( $support_email ); ?></a>.</p><?php endif; ?>
        </div>
        <?php if ( $notice ) : ?>
            <div id="fw-contact-notice" class="fw-form-notice fw-form-notice-<?php echo esc_attr( $notice['type'] ); ?>" tabindex="-1" role="<?php echo $notice['type'] === 'error' ? 'alert' : 'status'; ?>">
                <p><?php echo esc_html( $notice['message'] ); ?></p>
                <?php if ( ! empty( $notice['errors'] ) ) : ?>
                    <ul><?php foreach ( $notice['errors'] as $error ) : ?><li><?php echo esc_html( $error ); ?></li><?php endforeach; ?></ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ( ! $received ) : ?>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="fw-box fw-form" aria-labelledby="fw-contact-heading" aria-describedby="fw-contact-required-help"><?php findewerkstatt_language_field(); ?>
                <input type="hidden" name="action" value="fw_contact">
                <input type="hidden" name="fw_submission_token" value="<?php echo esc_attr( findewerkstatt_form_submission_token( 'contact' ) ); ?>">
                <?php wp_nonce_field( 'fw_send_kontakt', 'fw_kontakt_nonce' ); ?>
                <div class="fw-form-honeypot" aria-hidden="true">
                    <label for="fw-contact-company-website"><?php echo esc_html( findewerkstatt_t( 'Dieses Feld bitte leer lassen' ) ); ?></label>
                    <input id="fw-contact-company-website" type="text" name="company_website" tabindex="-1" autocomplete="off">
                </div>
                <p id="fw-contact-required-help" class="fw-form-help"><?php echo esc_html( findewerkstatt_t( 'Felder mit * sind erforderlich.' ) ); ?></p>
                <div class="fw-form-grid">
                    <div class="fw-form-field">
                        <label for="fw-contact-name"><?php echo esc_html( findewerkstatt_t( 'Ihr Name *' ) ); ?></label>
                        <input id="fw-contact-name" type="text" name="name" required maxlength="160" autocomplete="name" value="<?php echo esc_attr( $values['name'] ?? '' ); ?>" placeholder="<?php echo esc_attr( findewerkstatt_t( 'Vor- und Nachname' ) ); ?>">
                    </div>
                    <div class="fw-form-field">
                        <label for="fw-contact-email">Ihre E-Mail-Adresse *</label>
                        <input id="fw-contact-email" type="email" name="email" required maxlength="254" autocomplete="email" value="<?php echo esc_attr( $values['email'] ?? '' ); ?>" placeholder="name@beispiel.de">
                    </div>
                </div>
                <div class="fw-form-field">
                    <label for="fw-contact-subject"><?php echo esc_html( findewerkstatt_t( 'Betreff' ) ); ?></label>
                    <input id="fw-contact-subject" type="text" name="subject" maxlength="160" value="<?php echo esc_attr( $values['subject'] ?? '' ); ?>" placeholder="<?php echo esc_attr( findewerkstatt_t( 'Worum geht es?' ) ); ?>">
                </div>
                <div class="fw-form-field">
                    <label for="fw-contact-message"><?php echo esc_html( findewerkstatt_t( 'Ihre Nachricht *' ) ); ?></label>
                    <textarea id="fw-contact-message" name="message" rows="6" required maxlength="5000" placeholder="<?php echo esc_attr( findewerkstatt_t( 'Wie können wir Ihnen helfen? Bei Fragen zu einem Eintrag nennen Sie bitte den Betrieb und den Ort.' ) ); ?>"><?php echo esc_textarea( $values['message'] ?? '' ); ?></textarea>
                </div>
                <label class="fw-form-check" for="fw-contact-privacy">
                    <input id="fw-contact-privacy" type="checkbox" name="privacy" value="1" required <?php checked( ! empty( $values['privacy'] ) ); ?>>
                    <span><?php echo esc_html( findewerkstatt_t( 'Ich habe die' ) ); ?> <a href="<?php echo esc_url( findewerkstatt_page_url( 'datenschutz' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( findewerkstatt_t( 'Datenschutzerklärung' ) ); ?><span class="screen-reader-text"> <?php echo esc_html( findewerkstatt_t( '(öffnet einen neuen Tab)' ) ); ?></span></a> <?php echo esc_html( findewerkstatt_t( 'gelesen. Meine Angaben werden zur Bearbeitung dieser Anfrage verwendet. *' ) ); ?></span>
                </label>
                <button type="submit" class="fw-btn fw-btn-primary fw-btn-lg fw-btn-block"><?php echo esc_html( findewerkstatt_t( 'Nachricht senden' ) ); ?></button>
            </form>
        <?php else : ?>
            <div class="fw-form-success-actions"><a class="fw-btn fw-btn-outline" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Zur Startseite' ) ); ?></a></div>
        <?php endif; ?>
    </div>
</main>
<?php get_footer(); ?>
