<?php
/**
 * Template Name: Kontakt
 * 
 * FindeWerkstatt.de — Kontakt & Support
 * 
 * @package FindeWerkstatt
 * @version 2.0.0
 */

get_header();

$sent = false;
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['fw_kontakt_nonce'] ) && wp_verify_nonce( $_POST['fw_kontakt_nonce'], 'fw_send_kontakt' ) ) {
    $sent = true;
}
?>

<div class="fw-container" style="padding-top:40px; padding-bottom:70px;">
    <div style="max-width:760px; margin:0 auto;">
        <div style="text-align:center; margin-bottom:36px;">
            <h1 style="font-size:32px; font-weight:900; color:var(--fw-primary); margin-bottom:12px;">
                Kontakt & Support
            </h1>
            <p style="font-size:16px; color:var(--fw-text-muted);">
                Haben Sie Fragen zur Nutzung, zu Ihrem Werkstatt-Eintrag oder Feedback? Wir helfen Ihnen gerne weiter.
            </p>
        </div>

        <?php if ( $sent ) : ?>
            <div style="background:#ecfdf5; border:1px solid #a7f3d0; border-radius:var(--fw-radius); padding:24px; text-align:center;">
                <div style="font-size:36px; margin-bottom:10px;">✉️</div>
                <h3 style="color:#065f46; font-size:18px; margin-bottom:8px;">Nachricht erfolgreich gesendet!</h3>
                <p style="color:#047857; font-size:14.5px;">
                    Vielen Dank für Ihre Kontaktaufnahme. Unser Team wird sich schnellstmöglich bei Ihnen melden.
                </p>
            </div>
        <?php else : ?>
            <form method="post" class="fw-box">
                <?php wp_nonce_field( 'fw_send_kontakt', 'fw_kontakt_nonce' ); ?>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; margin-bottom:6px;">Ihr Name *</label>
                        <input type="text" name="name" required placeholder="Vor- und Nachname" style="width:100%; padding:10px; border:1px solid var(--fw-border); border-radius:8px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; margin-bottom:6px;">Ihre E-Mail-Adresse *</label>
                        <input type="email" name="email" required placeholder="name@beispiel.de" style="width:100%; padding:10px; border:1px solid var(--fw-border); border-radius:8px;">
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:13px; font-weight:700; margin-bottom:6px;">Betreff</label>
                    <input type="text" name="subject" placeholder="Worum geht es?" style="width:100%; padding:10px; border:1px solid var(--fw-border); border-radius:8px;">
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block; font-size:13px; font-weight:700; margin-bottom:6px;">Ihre Nachricht *</label>
                    <textarea name="message" rows="5" required placeholder="Wie können wir Ihnen helfen?" style="width:100%; padding:10px; border:1px solid var(--fw-border); border-radius:8px; font-family:inherit;"></textarea>
                </div>

                <button type="submit" class="fw-btn fw-btn-primary fw-btn-lg fw-btn-block">
                    Nachricht absenden
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php
get_footer();
