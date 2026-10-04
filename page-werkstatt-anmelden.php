<?php
/**
 * Template Name: Werkstatt eintragen
 * @package FindeWerkstatt
 */
$notice = findewerkstatt_get_form_notice( 'registration' );
$values = $notice['values'] ?? array();
$received = ! empty( $notice['received'] );
$can_register = is_user_logged_in() && ( findewerkstatt_is_workshop_member() || current_user_can( 'manage_options' ) );
$can_add = $can_register && ( current_user_can( 'manage_options' ) || findewerkstatt_member_can_add_workshop() );
$plans = findewerkstatt_membership_plans();
$active_plan = findewerkstatt_member_plan();
$requested_plan = isset( $_GET['plan'] ) && is_string( $_GET['plan'] ) ? wp_unslash( $_GET['plan'] ) : $active_plan;
$selected_plan = isset( $values['selected_plan'] ) && is_string( $values['selected_plan'] ) ? $values['selected_plan'] : $requested_plan;
if ( ! isset( $plans[ $selected_plan ] ) ) { $selected_plan = $active_plan; }
$owned_count = $can_register ? count( findewerkstatt_member_owned_workshops() ) : 0;
$larger_plans = array_filter( $plans, static function ( $plan ) use ( $owned_count ) { return (int) $plan['listing_limit'] > $owned_count; } );
if ( findewerkstatt_is_workshop_member() ) {
    $member = wp_get_current_user();
    $values['contact_name'] = $values['contact_name'] ?? $member->display_name;
    $values['email'] = $values['email'] ?? $member->user_email;
}
if ( ! defined( 'DONOTCACHEPAGE' ) ) { define( 'DONOTCACHEPAGE', true ); }
$bundeslaender = FindeWerkstatt_German_Data::get_bundeslaender();
$services = findewerkstatt_form_terms( 'service_type' );
$brands = findewerkstatt_form_terms( 'car_brand' );
get_header();
?>
<main id="main-content" class="fw-container fw-form-page">
    <div class="fw-form-wrap fw-form-wrap-wide">
        <div class="fw-registration-intro">
            <div class="fw-section-header">
                <div class="fw-section-tag"><?php echo esc_html( findewerkstatt_t( 'Für Werkstattinhaber' ) ); ?></div>
                <h1 id="fw-registration-heading"><?php echo esc_html( findewerkstatt_t( 'Ihre Werkstatt eintragen' ) ); ?></h1>
                <p><?php echo esc_html( findewerkstatt_t( 'Machen Sie Ihren Betrieb für Autofahrer in Ihrer Region sichtbar. Mit Ihrem Werkstattkonto reichen Sie Kontaktdaten und Leistungen ein. Wir prüfen jeden Eintrag vor der Veröffentlichung.' ) ); ?></p>
            </div>
            <div class="fw-mascot-art fw-registration-mascot" aria-hidden="true" style="flex: 0 0 160px; max-width: 170px;">
                <img src="<?php echo esc_url( findewerkstatt_brand_asset_url( 'mascot_workshop' ) ); ?>" alt="" width="180" height="180" style="max-height: 190px; width: auto; max-width: 170px; object-fit: contain; display: block;" decoding="async">
            </div>
        </div>
        <?php if ( $notice ) : ?>
            <div id="fw-registration-notice" class="fw-form-notice fw-form-notice-<?php echo esc_attr( $notice['type'] ); ?>" tabindex="-1" role="<?php echo $notice['type'] === 'error' ? 'alert' : 'status'; ?>">
                <p><?php echo esc_html( $notice['message'] ); ?></p>
                <?php if ( ! empty( $notice['errors'] ) ) : ?>
                    <ul><?php foreach ( $notice['errors'] as $error ) : ?><li><?php echo esc_html( $error ); ?></li><?php endforeach; ?></ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ( ! $received && ! $can_register ) : ?>
            <div class="fw-box">
                <h2><?php echo esc_html( findewerkstatt_t( 'Zuerst ein Werkstattkonto anlegen' ) ); ?></h2>
                <p><?php echo esc_html( findewerkstatt_t( 'Erstellen Sie ein kostenloses Konto oder melden Sie sich mit Ihrem Werkstattkonto an. Das Paket wählen Sie anschließend beim Eintragen Ihrer Werkstatt. Den Bearbeitungsstand können Sie in Ihrem Konto verfolgen.' ) ); ?></p>
                <p><a class="fw-btn fw-btn-primary" href="<?php echo esc_url( findewerkstatt_page_url( 'mein-konto' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Konto erstellen oder anmelden' ) ); ?></a> <a class="fw-btn fw-btn-outline" href="<?php echo esc_url( findewerkstatt_page_url( 'pakete' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Pakete ansehen' ) ); ?></a></p>
                <p><?php echo esc_html( findewerkstatt_t( 'Ist Ihre Werkstatt bereits im Verzeichnis? Nach der Anmeldung können Sie die Zuordnung des vorhandenen Eintrags zu Ihrem Konto anfragen. Wir prüfen Ihre Berechtigung.' ) ); ?></p>
            </div>
        <?php elseif ( ! $received && ! $can_add ) : ?>
            <div class="fw-box">
                <h2><?php echo esc_html( findewerkstatt_t( 'Ihre Betriebe im aktuellen Paket' ) ); ?></h2>
                <p><?php echo esc_html( findewerkstatt_t( 'Die Anzahl der Betriebe in Ihrem Paket ist erreicht. Bereits eingereichte und veröffentlichte Betriebe zählen zu Ihrem Kontingent. Wählen Sie für einen weiteren Betrieb ein größeres Paket. Sobald wir die Paketanfrage freigeschaltet haben, können Sie den neuen Eintrag hier einreichen.' ) ); ?></p>
                <?php if ( $larger_plans ) : ?>
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="fw-form"><?php findewerkstatt_language_field(); ?>
                        <input type="hidden" name="action" value="fw_member_plan_request">
                        <input type="hidden" name="fw_submission_token" value="<?php echo esc_attr( findewerkstatt_form_submission_token( 'member_plan_request' ) ); ?>">
                        <?php wp_nonce_field( 'fw_member_plan_request', 'fw_member_nonce' ); ?>
                        <?php get_template_part( 'template-parts/registration-packages', null, array( 'plans' => $plans, 'selected_plan' => isset( $larger_plans[ $selected_plan ] ) ? $selected_plan : array_key_first( $larger_plans ), 'active_plan' => $active_plan, 'owned_count' => $owned_count, 'request_only' => true ) ); ?>
                        <button type="submit" class="fw-btn fw-btn-primary fw-btn-block"><?php echo esc_html( findewerkstatt_t( 'Größeres Paket anfragen' ) ); ?></button>
                    </form>
                <?php else : ?>
                    <p><?php echo esc_html( findewerkstatt_t( 'Für weitere Standorte kontaktieren Sie uns bitte, damit wir Ihren Bedarf besprechen können.' ) ); ?></p>
                    <p><a class="fw-btn fw-btn-primary" href="<?php echo esc_url( findewerkstatt_page_url( 'kontakt' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Kontakt aufnehmen' ) ); ?></a></p>
                <?php endif; ?>
                <p><a href="<?php echo esc_url( findewerkstatt_page_url( 'mein-konto' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Bearbeitungsstand in meinem Konto ansehen' ) ); ?></a></p>
            </div>
        <?php elseif ( ! $received ) : ?>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="fw-box fw-form" aria-labelledby="fw-registration-heading" aria-describedby="fw-registration-required-help"><?php findewerkstatt_language_field(); ?>
                <input type="hidden" name="action" value="fw_register_workshop">
                <input type="hidden" name="fw_submission_token" value="<?php echo esc_attr( findewerkstatt_form_submission_token( 'registration' ) ); ?>">
                <?php wp_nonce_field( 'fw_register_workshop', 'fw_register_nonce' ); ?>
                <div class="fw-form-honeypot" style="display:none !important; visibility:hidden !important; position:absolute !important; left:-9999px !important; width:0 !important; height:0 !important; opacity:0 !important; pointer-events:none !important;" aria-hidden="true">
                    <label for="fw-register-company-website"><?php echo esc_html( findewerkstatt_t( 'Dieses Feld bitte leer lassen' ) ); ?></label>
                    <input id="fw-register-company-website" type="text" name="company_website" tabindex="-1" autocomplete="off">
                </div>
                <p id="fw-registration-required-help" class="fw-form-help"><?php echo esc_html( findewerkstatt_t( 'Felder mit * sind erforderlich. Ihr Eintrag wird vor der Veröffentlichung geprüft und Ihrem Konto zugeordnet.' ) ); ?></p>
                <?php if ( findewerkstatt_is_workshop_member() ) { get_template_part( 'template-parts/registration-packages', null, array( 'plans' => $plans, 'selected_plan' => $selected_plan, 'active_plan' => $active_plan, 'owned_count' => $owned_count ) ); } ?>
                <fieldset class="fw-form-section">
                    <legend><?php echo findewerkstatt_is_workshop_member() ? '2.' : '1.'; ?> <?php echo esc_html( findewerkstatt_t( 'Betrieb & Kontakt' ) ); ?></legend>
                    <div class="fw-form-grid">
                        <div class="fw-form-field">
                            <label for="fw-company-name"><?php echo esc_html( findewerkstatt_t( 'Name der Werkstatt *' ) ); ?></label>
                            <input id="fw-company-name" type="text" name="company_name" required maxlength="160" autocomplete="organization" value="<?php echo esc_attr( $values['company_name'] ?? '' ); ?>" placeholder="<?php echo esc_attr( findewerkstatt_t( 'Kfz-Meisterbetrieb Schmidt' ) ); ?>">
                        </div>
                        <div class="fw-form-field">
                            <label for="fw-contact-person"><?php echo esc_html( findewerkstatt_t( 'Ansprechpartner' ) ); ?></label>
                            <input id="fw-contact-person" type="text" name="contact_name" maxlength="160" autocomplete="name" value="<?php echo esc_attr( $values['contact_name'] ?? '' ); ?>" placeholder="<?php echo esc_attr( findewerkstatt_t( 'Vor- und Nachname' ) ); ?>">
                        </div>
                        <div class="fw-form-field">
                            <label for="fw-workshop-email"><?php echo esc_html( findewerkstatt_t( 'E-Mail-Adresse *' ) ); ?></label>
                            <input id="fw-workshop-email" type="email" name="email" required maxlength="254" autocomplete="email" value="<?php echo esc_attr( $values['email'] ?? '' ); ?>" placeholder="info@ihre-werkstatt.de" aria-describedby="fw-registration-email-help">
                            <p id="fw-registration-email-help" class="fw-form-help"><?php echo esc_html( findewerkstatt_t( 'Wir verwenden diese Adresse für Rückfragen zu Ihrer Anmeldung.' ) ); ?></p>
                        </div>
                        <div class="fw-form-field">
                            <label for="fw-workshop-phone"><?php echo esc_html( findewerkstatt_t( 'Telefonnummer *' ) ); ?></label>
                            <input id="fw-workshop-phone" type="tel" name="phone" required maxlength="60" autocomplete="tel" value="<?php echo esc_attr( $values['phone'] ?? '' ); ?>" placeholder="030 1234567">
                        </div>
                        <div class="fw-form-field">
                            <label for="fw-workshop-whatsapp"><?php echo esc_html( findewerkstatt_t( 'WhatsApp-Nummer (optional)' ) ); ?></label>
                            <input id="fw-workshop-whatsapp" type="tel" name="whatsapp" maxlength="60" value="<?php echo esc_attr( $values['whatsapp'] ?? '' ); ?>" placeholder="+49 170 1234567" aria-describedby="fw-whatsapp-help">
                            <p id="fw-whatsapp-help" class="fw-form-help"><?php echo esc_html( findewerkstatt_t( 'Nur ausfüllen, wenn Kunden Sie unter dieser Nummer über WhatsApp erreichen können.' ) ); ?></p>
                        </div>
                        <div class="fw-form-field">
                            <label for="fw-workshop-website"><?php echo esc_html( findewerkstatt_t( 'Website (optional)' ) ); ?></label>
                            <input id="fw-workshop-website" type="text" inputmode="url" name="website" maxlength="250" autocomplete="url" spellcheck="false" value="<?php echo esc_attr( $values['website'] ?? '' ); ?>" placeholder="https://www.ihre-werkstatt.de">
                        </div>
                    </div>
                    <label class="fw-form-check"><input type="checkbox" name="public_email" value="1" <?php checked( ! empty( $values['public_email'] ) ); ?>><span><?php echo esc_html( findewerkstatt_t( 'Diese E-Mail-Adresse darf auch im öffentlichen Werkstattprofil als Kontaktadresse erscheinen.' ) ); ?></span></label>
                </fieldset>
                <fieldset class="fw-form-section">
                    <legend><?php echo findewerkstatt_is_workshop_member() ? '3.' : '2.'; ?> <?php echo esc_html( findewerkstatt_t( 'Standort' ) ); ?></legend>
                    <div class="fw-form-field">
                        <label for="fw-workshop-street"><?php echo esc_html( findewerkstatt_t( 'Straße & Hausnummer' ) ); ?></label>
                        <input id="fw-workshop-street" type="text" name="street" maxlength="200" autocomplete="address-line1" value="<?php echo esc_attr( $values['street'] ?? '' ); ?>" placeholder="<?php echo esc_attr( findewerkstatt_t( 'Musterstraße 12' ) ); ?>">
                    </div>
                    <div class="fw-form-grid fw-form-grid-three">
                        <div class="fw-form-field">
                            <label for="fw-workshop-state"><?php echo esc_html( findewerkstatt_t( 'Bundesland *' ) ); ?></label>
                            <select id="fw-workshop-state" name="bundesland" required autocomplete="address-level1">
                                <option value=""><?php echo esc_html( findewerkstatt_t( 'Bitte auswählen' ) ); ?></option>
                                <?php foreach ( $bundeslaender as $land ) : ?>
                                    <option value="<?php echo esc_attr( $land['slug'] ); ?>" <?php selected( $values['bundesland'] ?? '', $land['slug'] ); ?>><?php echo esc_html( $land['name'] ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="fw-form-field">
                            <label for="fw-workshop-city">Stadt / Ort *</label>
                            <input id="fw-workshop-city" type="text" name="city" required maxlength="100" autocomplete="address-level2" value="<?php echo esc_attr( $values['city'] ?? '' ); ?>" placeholder="Berlin" aria-describedby="fw-registration-city-help">
                            <p id="fw-registration-city-help" class="fw-form-help"><?php echo esc_html( findewerkstatt_t( 'Geben Sie den vollständigen Ortsnamen passend zum gewählten Bundesland an.' ) ); ?></p>
                        </div>
                        <div class="fw-form-field">
                            <label for="fw-workshop-plz"><?php echo esc_html( findewerkstatt_t( 'Postleitzahl' ) ); ?></label>
                            <input id="fw-workshop-plz" type="text" name="plz" maxlength="5" pattern="[0-9]{5}" inputmode="numeric" autocomplete="postal-code" value="<?php echo esc_attr( $values['plz'] ?? '' ); ?>" placeholder="10115" aria-describedby="fw-registration-plz-help">
                            <p id="fw-registration-plz-help" class="fw-form-help"><?php echo esc_html( findewerkstatt_t( 'Optional; eine deutsche Postleitzahl besteht aus fünf Ziffern.' ) ); ?></p>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="fw-form-section">
                    <legend><?php echo findewerkstatt_is_workshop_member() ? '4.' : '3.'; ?> <?php echo esc_html( findewerkstatt_t( 'Leistungen & Fahrzeugmarken' ) ); ?></legend>
                    <p id="fw-registration-services-help" class="fw-form-help"><?php echo esc_html( findewerkstatt_t( 'Wählen Sie die Leistungen aus, die Ihr Betrieb tatsächlich anbietet.' ) ); ?></p>
                    <div class="fw-form-choices" role="group" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Angebotene Leistungen' ) ); ?>" aria-describedby="fw-registration-services-help">
                        <?php foreach ( $services as $slug => $term ) : ?>
                            <label class="fw-form-check"><input type="checkbox" name="services[]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( in_array( $slug, $values['services'] ?? array(), true ) ); ?>><span><?php echo esc_html( findewerkstatt_t( $term->name ) ); ?></span></label>
                        <?php endforeach; ?>
                    </div>
                    <h2 id="fw-registration-brands-heading" class="fw-form-subheading"><?php echo esc_html( findewerkstatt_t( 'Welche Marken betreuen Sie?' ) ); ?></h2>
                    <p id="fw-registration-brands-help" class="fw-form-help"><?php echo esc_html( findewerkstatt_t( 'Optional: Wählen Sie die Marken aus, für die Sie Wartung oder Reparaturen anbieten.' ) ); ?></p>
                    <div class="fw-form-choices" role="group" aria-labelledby="fw-registration-brands-heading" aria-describedby="fw-registration-brands-help">
                        <?php foreach ( $brands as $slug => $term ) : ?>
                            <label class="fw-form-check"><input type="checkbox" name="brands[]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( in_array( $slug, $values['brands'] ?? array(), true ) ); ?>><span><?php echo esc_html( $term->name ); ?></span></label>
                        <?php endforeach; ?>
                    </div>
                    <?php findewerkstatt_render_workshop_language_choices( $values['spoken_languages'] ?? array(), 'fw-registration-spoken' ); ?>
                    <label class="fw-form-check"><input type="checkbox" name="is_master" value="1" <?php checked( ! empty( $values['is_master'] ) ); ?>><span><?php echo esc_html( findewerkstatt_t( 'Unser Betrieb ist ein Kfz-Meisterbetrieb.' ) ); ?></span></label>
                    <label class="fw-form-check"><input type="checkbox" name="is_24h" value="1" <?php checked( ! empty( $values['is_24h'] ) ); ?>><span><?php echo esc_html( findewerkstatt_t( 'Wir bieten einen rund um die Uhr erreichbaren Notdienst an.' ) ); ?></span></label>
                    <div class="fw-form-field">
                        <label for="fw-workshop-description"><?php echo esc_html( findewerkstatt_t( 'Über Ihren Betrieb' ) ); ?></label>
                        <textarea id="fw-workshop-description" name="description" rows="5" maxlength="5000" placeholder="<?php echo esc_attr( findewerkstatt_t( 'Beschreiben Sie Ihren Betrieb, Ihre Erfahrung und Ihre Schwerpunkte.' ) ); ?>"><?php echo esc_textarea( $values['description'] ?? '' ); ?></textarea>
                    </div>
                </fieldset>
                <label class="fw-form-check" for="fw-register-privacy">
                    <input id="fw-register-privacy" type="checkbox" name="privacy" value="1" required <?php checked( ! empty( $values['privacy'] ) ); ?>>
                    <span><?php echo esc_html( findewerkstatt_t( 'Ich darf diesen Betrieb vertreten und bestätige, dass meine Angaben richtig sind. Ich habe die' ) ); ?> <a href="<?php echo esc_url( findewerkstatt_page_url( 'datenschutz' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( findewerkstatt_t( 'Datenschutzerklärung' ) ); ?><span class="screen-reader-text"> <?php echo esc_html( findewerkstatt_t( '(öffnet einen neuen Tab)' ) ); ?></span></a> <?php echo esc_html( findewerkstatt_t( 'gelesen. Die Betriebsdaten dürfen nach Prüfung im Verzeichnis veröffentlicht werden. *' ) ); ?></span>
                </label>
                <button type="submit" class="fw-btn fw-btn-primary fw-btn-lg fw-btn-block"><?php echo esc_html( findewerkstatt_t( 'Werkstatt zur Prüfung einreichen' ) ); ?></button>
            </form>
        <?php else : ?>
            <div class="fw-form-success-actions"><a class="fw-btn fw-btn-primary" href="<?php echo esc_url( findewerkstatt_page_url( 'mein-konto' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Mein Konto öffnen' ) ); ?></a> <a class="fw-btn fw-btn-outline" href="<?php echo esc_url( get_post_type_archive_link( 'mechanic' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Zum Werkstattverzeichnis' ) ); ?></a></div>
        <?php endif; ?>
    </div>
</main>
<?php get_footer(); ?>
