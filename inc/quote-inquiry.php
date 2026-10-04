<?php
/**
 * FindeWerkstatt.de — Kostenvoranschlag & Terminanfrage (Direktanfrage an Werkstätten)
 *
 * Ermöglicht Autofahrern, direkt über das Werkstattprofil unverbindliche
 * Kostenvoranschläge oder Termine anzufragen.
 *
 * @package FindeWerkstatt
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Verarbeitet das Absenden einer Terminanfrage / eines Kostenvoranschlags.
 */
function findewerkstatt_handle_quote_inquiry() {
    if ( 'POST' !== $_SERVER['REQUEST_METHOD'] || ! isset( $_POST['fw_action'] ) || 'fw_quote_inquiry' !== $_POST['fw_action'] ) {
        return;
    }

    $workshop_id = isset( $_POST['workshop_id'] ) ? absint( $_POST['workshop_id'] ) : 0;
    if ( ! $workshop_id || 'mechanic' !== get_post_type( $workshop_id ) ) {
        return;
    }

    $redirect_url = get_permalink( $workshop_id );

    // 1. Nonce-Prüfung
    if ( ! isset( $_POST['fw_quote_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['fw_quote_nonce'] ), 'fw_quote_inquiry_action' ) ) {
        wp_safe_redirect( add_query_arg( 'inquiry_status', 'invalid_nonce', $redirect_url ) . '#fw-inquiry-box' );
        exit;
    }

    // 2. Spam-Honeypot-Prüfung
    if ( ! empty( $_POST['inquiry_website_hp'] ) ) {
        // Bot abgefangen — lautlos weiterleiten
        wp_safe_redirect( add_query_arg( 'inquiry_status', 'success', $redirect_url ) . '#fw-inquiry-box' );
        exit;
    }

    // 3. DSGVO-Einwilligung
    if ( empty( $_POST['dsgvo_consent'] ) ) {
        wp_safe_redirect( add_query_arg( 'inquiry_status', 'missing_dsgvo', $redirect_url ) . '#fw-inquiry-box' );
        exit;
    }

    // 4. Pflichtfelder auslesen & bereinigen
    $name          = sanitize_text_field( wp_unslash( $_POST['customer_name'] ?? '' ) );
    $email         = sanitize_email( wp_unslash( $_POST['customer_email'] ?? '' ) );
    $phone         = sanitize_text_field( wp_unslash( $_POST['customer_phone'] ?? '' ) );
    $vehicle       = sanitize_text_field( wp_unslash( $_POST['vehicle_info'] ?? '' ) );
    $service       = sanitize_text_field( wp_unslash( $_POST['service_type'] ?? '' ) );
    $date_pref     = sanitize_text_field( wp_unslash( $_POST['preferred_date'] ?? '' ) );
    $message       = sanitize_textarea_field( wp_unslash( $_POST['customer_message'] ?? '' ) );

    if ( empty( $name ) || empty( $email ) || ! is_email( $email ) || empty( $message ) ) {
        wp_safe_redirect( add_query_arg( 'inquiry_status', 'missing_fields', $redirect_url ) . '#fw-inquiry-box' );
        exit;
    }

    $workshop_title = get_the_title( $workshop_id );
    $workshop_email = get_post_meta( $workshop_id, '_mechanic_email', true );
    $admin_email    = get_option( 'admin_email' );

    // Empfänger festlegen (Priorität: Werkstatt-E-Mail, sonst Admin)
    $recipient = ( ! empty( $workshop_email ) && is_email( $workshop_email ) ) ? $workshop_email : $admin_email;

    // Betreff
    $subject = sprintf( '[FindeWerkstatt.de] Neue Anfrage: %s für %s', ( $service ?: 'Werkstattanfrage' ), $workshop_title );

    // E-Mail-Nachricht an Werkstatt/Admin
    $body  = "Hallo,\n\n";
    $body .= "über das Profil auf FindeWerkstatt.de ist eine neue Anfrage eingegangen:\n\n";
    $body .= "--------------------------------------------------\n";
    $body .= "Werkstatt: " . $workshop_title . "\n";
    $body .= "Kunde:     " . $name . "\n";
    $body .= "E-Mail:    " . $email . "\n";
    if ( $phone ) {
        $body .= "Telefon:   " . $phone . "\n";
    }
    if ( $vehicle ) {
        $body .= "Fahrzeug:  " . $vehicle . "\n";
    }
    if ( $service ) {
        $body .= "Leistung:  " . $service . "\n";
    }
    if ( $date_pref ) {
        $body .= "Wunschtermin: " . $date_pref . "\n";
    }
    $body .= "--------------------------------------------------\n\n";
    $body .= "Nachricht des Kunden:\n" . $message . "\n\n";
    $body .= "--\nDiese Nachricht wurde über das Kontaktformular von FindeWerkstatt.de gesendet.";

    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'Reply-To: ' . $name . ' <' . $email . '>',
    );

    // E-Mail versenden
    wp_mail( $recipient, $subject, $body, $headers );

    // Bestätigungskopie an Kunden
    $customer_subject = sprintf( 'Ihre Anfrage an %s über FindeWerkstatt.de', $workshop_title );
    $customer_body  = "Guten Tag " . $name . ",\n\n";
    $customer_body .= "vielen Dank für Ihre Anfrage an " . $workshop_title . " über FindeWerkstatt.de.\n";
    $customer_body .= "Ihre Anfrage wurde erfolgreich an den Betrieb übermittelt.\n\n";
    $customer_body .= "Zusammenfassung Ihrer Angaben:\n";
    $customer_body .= "--------------------------------------------------\n";
    $customer_body .= "Werkstatt: " . $workshop_title . "\n";
    if ( $vehicle ) {
        $customer_body .= "Fahrzeug: " . $vehicle . "\n";
    }
    if ( $service ) {
        $customer_body .= "Gewünschte Leistung: " . $service . "\n";
    }
    if ( $date_pref ) {
        $customer_body .= "Wunschtermin: " . $date_pref . "\n";
    }
    $customer_body .= "\nIhre Nachricht:\n" . $message . "\n\n";
    $customer_body .= "Der Betrieb wird sich zeitnah mit Ihnen in Verbindung setzen.\n\n";
    $customer_body .= "Mit freundlichen Grüßen,\nIhr Team von FindeWerkstatt.de\nhttps://findewerkstatt.de";

    wp_mail( $email, $customer_subject, $customer_body, array( 'Content-Type: text/plain; charset=UTF-8' ) );

    // Weiterleitung mit Erfolgsmeldung
    wp_safe_redirect( add_query_arg( 'inquiry_status', 'success', $redirect_url ) . '#fw-inquiry-box' );
    exit;
}

if ( function_exists( 'add_action' ) ) {
    add_action( 'template_redirect', 'findewerkstatt_handle_quote_inquiry' );
}



/**
 * Formular für Kostenvoranschlag & Terminanfrage auf der Profilseite rendern.
 *
 * @param int $workshop_id
 */
function findewerkstatt_render_quote_inquiry_form( $workshop_id ) {
    $status = isset( $_GET['inquiry_status'] ) ? sanitize_key( $_GET['inquiry_status'] ) : '';
    $services = get_terms( array( 'taxonomy' => 'service_type', 'hide_empty' => false ) );
    ?>
    <section id="fw-inquiry-box" class="fw-box fw-inquiry-box" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Kostenvoranschlag & Terminanfrage' ) ); ?>">
        <div class="fw-inquiry-header">
            <div class="fw-inquiry-badge">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <span><?php echo esc_html( findewerkstatt_t( 'Direktanfrage' ) ); ?></span>
            </div>
            <h2><?php echo esc_html( findewerkstatt_t( 'Kostenvoranschlag & Terminanfrage' ) ); ?></h2>
            <p class="fw-muted">
                <?php echo esc_html( sprintf( findewerkstatt_t( 'Senden Sie Ihre unverbindliche Anfrage direkt an %s. Sie erhalten zeitnah ein Angebot oder einen Terminvorschlag.' ), get_the_title( $workshop_id ) ) ); ?>
            </p>
        </div>

        <?php if ( 'success' === $status ) : ?>
            <div class="fw-form-notice fw-form-notice-success" role="status" style="margin-bottom:24px; padding:16px 20px; background:#ecfdf5; border:1px solid #10b981; border-radius:12px; color:#065f46;">
                <strong>✓ <?php echo esc_html( findewerkstatt_t( 'Vielen Dank! Ihre Anfrage wurde erfolgreich übermittelt.' ) ); ?></strong>
                <p style="margin:4px 0 0; font-size:13.5px;"><?php echo esc_html( findewerkstatt_t( 'Eine Bestätigung wurde an Ihre E-Mail-Adresse gesendet. Die Werkstatt wird sich in Kürze bei Ihnen melden.' ) ); ?></p>
            </div>
        <?php elseif ( 'missing_fields' === $status ) : ?>
            <div class="fw-form-notice fw-form-notice-error" role="alert" style="margin-bottom:20px; padding:14px 18px; background:#fef2f2; border:1px solid #ef4444; border-radius:12px; color:#991b1b;">
                ⚠️ <?php echo esc_html( findewerkstatt_t( 'Bitte füllen Sie alle erforderlichen Felder (Name, E-Mail, Nachricht) aus.' ) ); ?>
            </div>
        <?php elseif ( 'missing_dsgvo' === $status ) : ?>
            <div class="fw-form-notice fw-form-notice-error" role="alert" style="margin-bottom:20px; padding:14px 18px; background:#fef2f2; border:1px solid #ef4444; border-radius:12px; color:#991b1b;">
                ⚠️ <?php echo esc_html( findewerkstatt_t( 'Bitte stimmen Sie den Datenschutzbestimmungen zu, um die Anfrage abzusenden.' ) ); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?php echo esc_url( get_permalink( $workshop_id ) ); ?>" class="fw-inquiry-form">
            <input type="hidden" name="fw_action" value="fw_quote_inquiry">
            <input type="hidden" name="workshop_id" value="<?php echo esc_attr( $workshop_id ); ?>">
            <?php wp_nonce_field( 'fw_quote_inquiry_action', 'fw_quote_nonce' ); ?>

            <!-- Honeypot -->
            <div style="display:none;" aria-hidden="true">
                <label for="inquiry_website_hp">Website URL (Leave blank)</label>
                <input type="text" id="inquiry_website_hp" name="inquiry_website_hp" tabindex="-1" autocomplete="off">
            </div>

            <div class="fw-inquiry-grid">
                <div class="fw-form-field">
                    <label for="inquiry-name"><?php echo esc_html( findewerkstatt_t( 'Ihr Name' ) ); ?> <span class="fw-required" style="color:#ef4444;">*</span></label>
                    <input type="text" id="inquiry-name" name="customer_name" required placeholder="<?php echo esc_attr( findewerkstatt_t( 'Vor- und Nachname' ) ); ?>" class="fw-input">
                </div>

                <div class="fw-form-field">
                    <label for="inquiry-email"><?php echo esc_html( findewerkstatt_t( 'Ihre E-Mail-Adresse' ) ); ?> <span class="fw-required" style="color:#ef4444;">*</span></label>
                    <input type="email" id="inquiry-email" name="customer_email" required placeholder="<?php echo esc_attr( findewerkstatt_t( 'beispiel@domain.de' ) ); ?>" class="fw-input">
                </div>

                <div class="fw-form-field">
                    <label for="inquiry-phone"><?php echo esc_html( findewerkstatt_t( 'Telefonnummer' ) ); ?> <span class="fw-muted" style="font-weight:normal; font-size:12px;">(optional für Rückfragen)</span></label>
                    <input type="tel" id="inquiry-phone" name="customer_phone" placeholder="<?php echo esc_attr( findewerkstatt_t( '+49 151 12345678' ) ); ?>" class="fw-input">
                </div>

                <div class="fw-form-field">
                    <label for="inquiry-vehicle"><?php echo esc_html( findewerkstatt_t( 'Fahrzeugmodell & Baujahr' ) ); ?></label>
                    <input type="text" id="inquiry-vehicle" name="vehicle_info" placeholder="<?php echo esc_attr( findewerkstatt_t( 'z. B. VW Golf 7 2.0 TDI (2018)' ) ); ?>" class="fw-input">
                </div>

                <div class="fw-form-field">
                    <label for="inquiry-service"><?php echo esc_html( findewerkstatt_t( 'Gewünschte Leistung' ) ); ?></label>
                    <select id="inquiry-service" name="service_type" class="fw-input">
                        <option value=""><?php echo esc_html( findewerkstatt_t( 'Bitte wählen oder allgemein' ) ); ?></option>
                        <option value="Inspektion & Wartung"><?php echo esc_html( findewerkstatt_t( 'Inspektion & Wartung' ) ); ?></option>
                        <option value="Hauptuntersuchung (TÜV / AU)"><?php echo esc_html( findewerkstatt_t( 'Hauptuntersuchung (TÜV / AU)' ) ); ?></option>
                        <option value="Bremsenservice"><?php echo esc_html( findewerkstatt_t( 'Bremsenservice' ) ); ?></option>
                        <option value="Reifenservice / Radwechsel"><?php echo esc_html( findewerkstatt_t( 'Reifenservice / Radwechsel' ) ); ?></option>
                        <option value="Ölwechsel"><?php echo esc_html( findewerkstatt_t( 'Ölwechsel' ) ); ?></option>
                        <option value="Klimaservice"><?php echo esc_html( findewerkstatt_t( 'Klimaservice' ) ); ?></option>
                        <option value="Karosserie- & Lackarbeiten"><?php echo esc_html( findewerkstatt_t( 'Karosserie- & Lackarbeiten' ) ); ?></option>
                        <option value="Fehlerdiagnose / Elektronik"><?php echo esc_html( findewerkstatt_t( 'Fehlerdiagnose / Elektronik' ) ); ?></option>
                        <option value="Sonstige Reparatur"><?php echo esc_html( findewerkstatt_t( 'Sonstige Reparatur' ) ); ?></option>
                    </select>
                </div>

                <div class="fw-form-field">
                    <label for="inquiry-date"><?php echo esc_html( findewerkstatt_t( 'Wunschtermin' ) ); ?> <span class="fw-muted" style="font-weight:normal; font-size:12px;">(optional)</span></label>
                    <input type="date" id="inquiry-date" name="preferred_date" class="fw-input" min="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>">
                </div>
            </div>

            <div class="fw-form-field" style="margin-top:14px;">
                <label for="inquiry-message"><?php echo esc_html( findewerkstatt_t( 'Ihr Anliegen / Problembeschreibung' ) ); ?> <span class="fw-required" style="color:#ef4444;">*</span></label>
                <textarea id="inquiry-message" name="customer_message" required rows="4" placeholder="<?php echo esc_attr( findewerkstatt_t( 'Beschreiben Sie möglichst genau, welche Reparatur oder Wartung benötigt wird...' ) ); ?>" class="fw-input"></textarea>
            </div>

            <div class="fw-form-field fw-form-checkbox" style="margin:16px 0 20px;">
                <label style="display:flex; align-items:flex-start; gap:10px; font-size:13px; color:#475569; cursor:pointer;">
                    <input type="checkbox" name="dsgvo_consent" value="1" required style="margin-top:3px;">
                    <span>
                        <?php echo esc_html( findewerkstatt_t( 'Ich stimme zu, dass meine Angaben zur Bearbeitung meiner Anfrage an den ausgewählten Betrieb weitergeleitet werden. Weitere Hinweise finden Sie in der' ) ); ?>
                        <a href="<?php echo esc_url( function_exists( 'findewerkstatt_page_url' ) ? findewerkstatt_page_url( 'datenschutz' ) : home_url( '/datenschutz/' ) ); ?>" target="_blank" rel="noopener" style="color:#fb6006; text-decoration:underline;">
                            <?php echo esc_html( findewerkstatt_t( 'Datenschutzerklärung' ) ); ?>
                        </a>.
                    </span>
                </label>
            </div>

            <button type="submit" class="fw-btn fw-btn-primary fw-btn-lg" style="width:100%; justify-content:center; font-weight:700;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                <span><?php echo esc_html( findewerkstatt_t( 'Kostenlose Anfrage absenden' ) ); ?></span>
            </button>
        </form>
    </section>
    <?php
}
