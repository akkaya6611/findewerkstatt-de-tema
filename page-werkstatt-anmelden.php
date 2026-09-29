<?php
/**
 * Template Name: Werkstatt eintragen
 * 
 * FindeWerkstatt.de — Werkstatt-Anmeldung für Inhaber & Meisterbetriebe
 * 
 * @package FindeWerkstatt
 * @version 2.0.0
 */

get_header();

$success = false;
$error   = '';

if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['fw_register_nonce'] ) && wp_verify_nonce( $_POST['fw_register_nonce'], 'fw_register_workshop' ) ) {
    $company_name = sanitize_text_field( $_POST['company_name'] ?? '' );
    $contact_name = sanitize_text_field( $_POST['contact_name'] ?? '' );
    $email        = sanitize_email( $_POST['email'] ?? '' );
    $phone        = sanitize_text_field( $_POST['phone'] ?? '' );
    $street       = sanitize_text_field( $_POST['street'] ?? '' );
    $plz          = sanitize_text_field( $_POST['plz'] ?? '' );
    $city_name    = sanitize_text_field( $_POST['city'] ?? '' );
    $bundesland   = sanitize_text_field( $_POST['bundesland'] ?? '' );
    $description  = sanitize_textarea_field( $_POST['description'] ?? '' );
    $is_master    = isset( $_POST['is_master'] ) ? 'yes' : 'no';
    $is_24h       = isset( $_POST['is_24h'] ) ? 'yes' : 'no';

    $selected_services = isset( $_POST['services'] ) && is_array( $_POST['services'] ) ? array_map( 'sanitize_text_field', $_POST['services'] ) : array();
    $selected_brands   = isset( $_POST['brands'] ) && is_array( $_POST['brands'] ) ? array_map( 'sanitize_text_field', $_POST['brands'] ) : array();

    if ( empty( $company_name ) || empty( $email ) || empty( $phone ) || empty( $city_name ) ) {
        $error = 'Bitte füllen Sie alle erforderlichen Pflichtfelder (Firmenname, E-Mail, Telefon und Stadt) aus.';
    } else {
        // Neuen Beitrag als "pending" (zur Überprüfung) anlegen
        $post_data = array(
            'post_title'   => $company_name,
            'post_content' => $description,
            'post_status'  => 'pending',
            'post_type'    => 'mechanic',
        );

        $new_id = wp_insert_post( $post_data );

        if ( $new_id && ! is_wp_error( $new_id ) ) {
            update_post_meta( $new_id, '_mechanic_phone', $phone );
            update_post_meta( $new_id, '_mechanic_email', $email );
            update_post_meta( $new_id, '_mechanic_address', $street );
            update_post_meta( $new_id, '_mechanic_plz', $plz );
            update_post_meta( $new_id, '_mechanic_is_master', $is_master );
            update_post_meta( $new_id, '_mechanic_emergency_24h', $is_24h );
            update_post_meta( $new_id, '_mechanic_contact_person', $contact_name );

            // Stadt & Bundesland zuweisen
            $city_slug = sanitize_title( $city_name );
            $term = term_exists( $city_slug, 'mechanic_city' );
            if ( ! $term ) {
                $term = wp_insert_term( $city_name, 'mechanic_city', array( 'slug' => $city_slug ) );
            }
            if ( ! is_wp_error( $term ) ) {
                $term_id = is_array( $term ) ? $term['term_id'] : $term;
                wp_set_object_terms( $new_id, (int) $term_id, 'mechanic_city' );
            }

            if ( ! empty( $selected_services ) ) {
                wp_set_object_terms( $new_id, $selected_services, 'service_type' );
            }

            if ( ! empty( $selected_brands ) ) {
                wp_set_object_terms( $new_id, $selected_brands, 'car_brand' );
            }

            $success = true;
        } else {
            $error = 'Es ist ein Fehler bei der Speicherung aufgetreten. Bitte kontaktieren Sie uns direkt.';
        }
    }
}

$bundeslaender = FindeWerkstatt_German_Data::get_bundeslaender();
$categories    = FindeWerkstatt_German_Data::get_categories();
$brands        = FindeWerkstatt_German_Data::get_car_brands();
?>

<div class="fw-container" style="padding-top:40px; padding-bottom:70px;">
    <div style="max-width:800px; margin:0 auto;">
        
        <div style="text-align:center; margin-bottom:36px;">
            <div style="font-size:13px; font-weight:800; color:var(--fw-info); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">
                Für Kfz-Werkstätten & Meisterbetriebe
            </div>
            <h1 style="font-size:34px; font-weight:900; color:var(--fw-primary); margin-bottom:12px;">
                Werkstatt jetzt kostenlos eintragen
            </h1>
            <p style="font-size:16px; color:var(--fw-text-muted); line-height:1.6;">
                Präsentieren Sie Ihren Betrieb Autofahrern in Ihrer Stadt. Keine versteckten Kosten, keine Provisionen.
            </p>
        </div>

        <?php if ( $success ) : ?>
            <div style="background:#ecfdf5; border:1px solid #a7f3d0; border-radius:var(--fw-radius); padding:24px; text-align:center; margin-bottom:30px;">
                <div style="font-size:42px; margin-bottom:10px;">✅</div>
                <h3 style="color:#065f46; font-size:20px; margin-bottom:8px;">Vielen Dank für Ihre Anmeldung!</h3>
                <p style="color:#047857; font-size:15px; line-height:1.6;">
                    Ihr Betrieb wurde erfolgreich übermittelt. Nach einer kurzen Prüfung schalten wir Ihr Profil online frei.
                </p>
            </div>
        <?php else : ?>

            <?php if ( $error ) : ?>
                <div style="background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; border-radius:var(--fw-radius); padding:16px; margin-bottom:24px; font-weight:600;">
                    ⚠️ <?php echo esc_html( $error ); ?>
                </div>
            <?php endif; ?>

            <form method="post" class="fw-box">
                <?php wp_nonce_field( 'fw_register_workshop', 'fw_register_nonce' ); ?>

                <h3 style="font-size:18px; margin-bottom:16px; color:var(--fw-primary);">1. Betriebsdaten</h3>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; margin-bottom:6px;">Firmenname / Werkstattname *</label>
                        <input type="text" name="company_name" required placeholder="z. B. Kfz-Meisterbetrieb Schmidt" style="width:100%; padding:10px; border:1px solid var(--fw-border); border-radius:8px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; margin-bottom:6px;">Ansprechpartner (Vor- und Nachname)</label>
                        <input type="text" name="contact_name" placeholder="z. B. Thomas Schmidt" style="width:100%; padding:10px; border:1px solid var(--fw-border); border-radius:8px;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; margin-bottom:6px;">E-Mail-Adresse *</label>
                        <input type="email" name="email" required placeholder="info@ihre-werkstatt.de" style="width:100%; padding:10px; border:1px solid var(--fw-border); border-radius:8px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; margin-bottom:6px;">Telefonnummer *</label>
                        <input type="tel" name="phone" required placeholder="030 1234567" style="width:100%; padding:10px; border:1px solid var(--fw-border); border-radius:8px;">
                    </div>
                </div>

                <hr style="border:0; border-top:1px solid var(--fw-border); margin:24px 0;">

                <h3 style="font-size:18px; margin-bottom:16px; color:var(--fw-primary);">2. Standort</h3>

                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:13px; font-weight:700; margin-bottom:6px;">Straße & Hausnummer</label>
                    <input type="text" name="street" placeholder="Musterstraße 12" style="width:100%; padding:10px; border:1px solid var(--fw-border); border-radius:8px;">
                </div>

                <div style="display:grid; grid-template-columns:1fr 1.5fr 1.5fr; gap:16px; margin-bottom:16px;">
                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; margin-bottom:6px;">PLZ</label>
                        <input type="text" name="plz" placeholder="10115" style="width:100%; padding:10px; border:1px solid var(--fw-border); border-radius:8px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; margin-bottom:6px;">Stadt / Ort *</label>
                        <input type="text" name="city" required placeholder="Berlin" style="width:100%; padding:10px; border:1px solid var(--fw-border); border-radius:8px;">
                    </div>
                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; margin-bottom:6px;">Bundesland</label>
                        <select name="bundesland" style="width:100%; padding:10px; border:1px solid var(--fw-border); border-radius:8px;">
                            <?php foreach ( $bundeslaender as $code => $land ) : ?>
                                <option value="<?php echo esc_attr( $land['slug'] ); ?>"><?php echo esc_html( $land['name'] ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <hr style="border:0; border-top:1px solid var(--fw-border); margin:24px 0;">

                <h3 style="font-size:18px; margin-bottom:16px; color:var(--fw-primary);">3. Fachbereiche & Leistungen</h3>
                
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:10px; margin-bottom:24px;">
                    <?php foreach ( $categories as $slug => $cat ) : ?>
                        <label style="display:flex; align-items:center; gap:8px; font-size:13.5px; cursor:pointer;">
                            <input type="checkbox" name="services[]" value="<?php echo esc_attr( $slug ); ?>">
                            <span><?php echo esc_html( $cat['name'] ); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:flex; align-items:center; gap:8px; font-weight:700; cursor:pointer; margin-bottom:8px;">
                        <input type="checkbox" name="is_master" value="1">
                        <span>🏆 Wir sind ein anerkannter Kfz-Meisterbetrieb (Handwerkskammer)</span>
                    </label>
                    <label style="display:flex; align-items:center; gap:8px; font-weight:700; cursor:pointer;">
                        <input type="checkbox" name="is_24h" value="1">
                        <span>🚨 Wir bieten 24h-Pannenhilfe / Notdienst an</span>
                    </label>
                </div>

                <div style="margin-bottom:24px;">
                    <label style="display:block; font-size:13px; font-weight:700; margin-bottom:6px;">Betriebsbeschreibung (Über uns)</label>
                    <textarea name="description" rows="5" placeholder="Beschreiben Sie Ihre Werkstatt, Ihre Erfahrung und besondere Ausstattungen..." style="width:100%; padding:10px; border:1px solid var(--fw-border); border-radius:8px; font-family:inherit;"></textarea>
                </div>

                <button type="submit" class="fw-btn fw-btn-primary fw-btn-lg fw-btn-block">
                    Betrieb jetzt verbindlich & kostenlos eintragen
                </button>
            </form>

        <?php endif; ?>
    </div>
</div>

<?php
get_footer();
