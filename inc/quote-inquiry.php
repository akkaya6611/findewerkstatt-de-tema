<?php
/**
 * FindeWerkstatt.de — Kostenvoranschlag & Terminanfrage (Direktanfrage an Werkstätten)
 *
 * Ermöglicht Autofahrern, direkt über das Werkstattprofil unverbindliche
 * Kostenvoranschläge oder Termine anzufragen:
 * - Detaillierte Fahrzeugangaben (Marke, Modell, Baujahr)
 * - Foto-Upload (z. B. Schadensbild, Fahrzeugschein) mit MIME-Prüfung
 * - Bevorzugter Kontaktkanal (Telefon, WhatsApp, E-Mail)
 * - Benachrichtigung per E-Mail an Werkstattinhaber & Bestätigung an Kunden
 * - Speicherung im WordPress-Backend (Post-Type fw_inquiry) zur Nachverfolgung
 * - DSGVO-Konformität & Spam-Schutz (Honeypot, Nonce)
 *
 * @package FindeWerkstatt
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 1. Post Type für Werkstattanfragen im Admin registrieren
 */
function findewerkstatt_register_inquiry_cpt() {
    register_post_type( 'fw_inquiry', array(
        'labels' => array(
            'name'               => findewerkstatt_t( 'Werkstattanfragen' ),
            'singular_name'      => findewerkstatt_t( 'Anfrage' ),
            'menu_name'          => findewerkstatt_t( 'Anfragen' ),
            'all_items'          => findewerkstatt_t( 'Alle Anfragen' ),
            'view_item'          => findewerkstatt_t( 'Anfrage ansehen' ),
            'search_items'       => findewerkstatt_t( 'Anfragen durchsuchen' ),
            'not_found'          => findewerkstatt_t( 'Keine Anfragen gefunden' ),
        ),
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => 'edit.php?post_type=mechanic',
        'capability_type'    => 'post',
        'capabilities'       => array(
            'create_posts' => 'do_not_allow',
        ),
        'map_meta_cap'       => true,
        'hierarchical'       => false,
        'supports'           => array( 'title' ),
        'has_archive'        => false,
    ) );
}
add_action( 'init', 'findewerkstatt_register_inquiry_cpt' );

/**
 * Admin-Spalten für fw_inquiry
 */
add_filter( 'manage_fw_inquiry_posts_columns', function( $columns ) {
    return array(
        'cb'           => '<input type="checkbox" />',
        'title'        => findewerkstatt_t( 'Kunde / Betreff' ),
        'fw_workshop'  => findewerkstatt_t( 'Werkstatt' ),
        'fw_contact'   => findewerkstatt_t( 'Kontakt' ),
        'fw_vehicle'   => findewerkstatt_t( 'Fahrzeug' ),
        'fw_service'   => findewerkstatt_t( 'Leistung' ),
        'fw_pref'      => findewerkstatt_t( 'Bevorzugter Kontakt' ),
        'date'         => findewerkstatt_t( 'Eingegangen am' ),
    );
} );

add_action( 'manage_fw_inquiry_posts_custom_column', function( $column, $post_id ) {
    switch ( $column ) {
        case 'fw_workshop':
            $ws_id = (int) get_post_meta( $post_id, '_fw_inquiry_workshop_id', true );
            if ( $ws_id && get_post( $ws_id ) ) {
                echo '<a href="' . esc_url( get_edit_post_link( $ws_id ) ) . '">' . esc_html( get_the_title( $ws_id ) ) . '</a>';
            } else {
                echo '—';
            }
            break;

        case 'fw_contact':
            $email = get_post_meta( $post_id, '_fw_inquiry_email', true );
            $phone = get_post_meta( $post_id, '_fw_inquiry_phone', true );
            if ( $email ) echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a><br>';
            if ( $phone ) echo '<a href="tel:' . esc_attr( $phone ) . '">' . esc_html( $phone ) . '</a>';
            break;

        case 'fw_vehicle':
            $brand = get_post_meta( $post_id, '_fw_inquiry_brand', true );
            $model = get_post_meta( $post_id, '_fw_inquiry_model', true );
            $year  = get_post_meta( $post_id, '_fw_inquiry_year', true );
            $parts = array_filter( array( $brand, $model, $year ? "($year)" : '' ) );
            echo esc_html( implode( ' ', $parts ) ?: '—' );
            break;

        case 'fw_service':
            echo esc_html( get_post_meta( $post_id, '_fw_inquiry_service', true ) ?: '—' );
            break;

        case 'fw_pref':
            $pref = get_post_meta( $post_id, '_fw_inquiry_contact_pref', true );
            $labels = array( 'phone' => '📞 Telefon', 'whatsapp' => '💬 WhatsApp', 'email' => '✉️ E-Mail' );
            echo esc_html( $labels[ $pref ] ?? $pref ?: '—' );
            break;
    }
}, 10, 2 );

/**
 * Meta-Box im Admin für Anfragedetails
 */
add_action( 'add_meta_boxes', function() {
    add_meta_box(
        'fw_inquiry_details_box',
        findewerkstatt_t( 'Details der Anfrage' ),
        'findewerkstatt_render_inquiry_meta_box',
        'fw_inquiry',
        'normal',
        'high'
    );
} );

function findewerkstatt_render_inquiry_meta_box( $post ) {
    $meta = get_post_custom( $post->ID );
    $ws_id    = (int) ( $meta['_fw_inquiry_workshop_id'][0] ?? 0 );
    $name     = $meta['_fw_inquiry_name'][0] ?? '';
    $email    = $meta['_fw_inquiry_email'][0] ?? '';
    $phone    = $meta['_fw_inquiry_phone'][0] ?? '';
    $brand    = $meta['_fw_inquiry_brand'][0] ?? '';
    $model    = $meta['_fw_inquiry_model'][0] ?? '';
    $year     = $meta['_fw_inquiry_year'][0] ?? '';
    $service  = $meta['_fw_inquiry_service'][0] ?? '';
    $pref     = $meta['_fw_inquiry_contact_pref'][0] ?? '';
    $message  = $meta['_fw_inquiry_message'][0] ?? '';
    $file_url = $meta['_fw_inquiry_file_url'][0] ?? '';
    ?>
    <table class="form-table" style="max-width:700px;">
        <tr><th><?php echo esc_html( findewerkstatt_t( 'Werkstatt' ) ); ?></th><td><?php echo $ws_id ? '<a href="' . esc_url( get_edit_post_link( $ws_id ) ) . '">' . esc_html( get_the_title( $ws_id ) ) . '</a>' : '—'; ?></td></tr>
        <tr><th><?php echo esc_html( findewerkstatt_t( 'Name des Kunden' ) ); ?></th><td><strong><?php echo esc_html( $name ); ?></strong></td></tr>
        <tr><th><?php echo esc_html( findewerkstatt_t( 'E-Mail' ) ); ?></th><td><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></td></tr>
        <tr><th><?php echo esc_html( findewerkstatt_t( 'Telefon' ) ); ?></th><td><a href="tel:<?php echo esc_attr( $phone ); ?>"><?php echo esc_html( $phone ); ?></a></td></tr>
        <tr><th><?php echo esc_html( findewerkstatt_t( 'Fahrzeug' ) ); ?></th><td><?php echo esc_html( trim( "$brand $model $year" ) ?: '—' ); ?></td></tr>
        <tr><th><?php echo esc_html( findewerkstatt_t( 'Gewünschte Leistung' ) ); ?></th><td><?php echo esc_html( $service ?: '—' ); ?></td></tr>
        <tr><th><?php echo esc_html( findewerkstatt_t( 'Bevorzugter Kontakt' ) ); ?></th><td><?php echo esc_html( $pref ?: '—' ); ?></td></tr>
        <tr><th><?php echo esc_html( findewerkstatt_t( 'Nachricht / Fehlerbeschreibung' ) ); ?></th><td><div style="background:#f8fafc; padding:12px; border-radius:6px; white-space:pre-wrap;"><?php echo esc_html( $message ); ?></div></td></tr>
        <?php if ( $file_url ) : ?>
            <tr><th><?php echo esc_html( findewerkstatt_t( 'Angehängte Datei' ) ); ?></th><td><a href="<?php echo esc_url( $file_url ); ?>" target="_blank" class="button">📎 <?php echo esc_html( findewerkstatt_t( 'Datei ansehen / herunterladen' ) ); ?></a></td></tr>
        <?php endif; ?>
    </table>
    <?php
}

/**
 * 2. Formularverarbeitung (Absenden eines Kostenvoranschlags)
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

    // 2. Spam-Honeypot
    if ( ! empty( $_POST['inquiry_website_hp'] ) ) {
        wp_safe_redirect( add_query_arg( 'inquiry_status', 'success', $redirect_url ) . '#fw-inquiry-box' );
        exit;
    }

    // 3. DSGVO-Einwilligung
    if ( empty( $_POST['dsgvo_consent'] ) ) {
        wp_safe_redirect( add_query_arg( 'inquiry_status', 'missing_dsgvo', $redirect_url ) . '#fw-inquiry-box' );
        exit;
    }

    // 4. Daten auslesen & bereinigen
    $name         = sanitize_text_field( wp_unslash( $_POST['customer_name'] ?? '' ) );
    $email        = sanitize_email( wp_unslash( $_POST['customer_email'] ?? '' ) );
    $phone        = sanitize_text_field( wp_unslash( $_POST['customer_phone'] ?? '' ) );
    $car_brand    = sanitize_text_field( wp_unslash( $_POST['car_brand'] ?? '' ) );
    $car_model    = sanitize_text_field( wp_unslash( $_POST['car_model'] ?? '' ) );
    $car_year     = sanitize_text_field( wp_unslash( $_POST['car_year'] ?? '' ) );
    $service      = sanitize_text_field( wp_unslash( $_POST['service_type'] ?? '' ) );
    $contact_pref = sanitize_key( wp_unslash( $_POST['contact_pref'] ?? 'email' ) );
    $message      = sanitize_textarea_field( wp_unslash( $_POST['customer_message'] ?? '' ) );

    if ( empty( $name ) || empty( $email ) || ! is_email( $email ) || empty( $message ) ) {
        wp_safe_redirect( add_query_arg( 'inquiry_status', 'missing_fields', $redirect_url ) . '#fw-inquiry-box' );
        exit;
    }

    // 5. Sicherer Foto-/Datei-Upload
    $file_url = '';
    if ( ! empty( $_FILES['quote_photo']['name'] ) ) {
        $allowed_mimes = array(
            'jpg|jpeg|jpe' => 'image/jpeg',
            'png'          => 'image/png',
            'webp'         => 'image/webp',
            'pdf'          => 'application/pdf',
        );

        $file_info = wp_check_filetype( $_FILES['quote_photo']['name'], $allowed_mimes );
        $max_size  = 5 * 1024 * 1024; // 5 MB

        if ( ! empty( $file_info['ext'] ) && $_FILES['quote_photo']['size'] <= $max_size ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            $upload_overrides = array( 'test_form' => false, 'mimes' => $allowed_mimes );
            $uploaded_file    = wp_handle_upload( $_FILES['quote_photo'], $upload_overrides );

            if ( $uploaded_file && ! isset( $uploaded_file['error'] ) ) {
                $file_url = $uploaded_file['url'];
            }
        }
    }

    // 6. Im Backend speichern als fw_inquiry Post
    $vehicle_str = trim( "$car_brand $car_model " . ( $car_year ? "($car_year)" : '' ) );
    $inquiry_post_id = wp_insert_post( array(
        'post_type'   => 'fw_inquiry',
        'post_status' => 'publish',
        'post_title'  => sprintf( '%s — %s (%s)', $name, ( $service ?: 'Allgemeine Anfrage' ), ( $vehicle_str ?: 'Fahrzeug' ) ),
    ) );

    if ( $inquiry_post_id && ! is_wp_error( $inquiry_post_id ) ) {
        update_post_meta( $inquiry_post_id, '_fw_inquiry_workshop_id', $workshop_id );
        update_post_meta( $inquiry_post_id, '_fw_inquiry_name', $name );
        update_post_meta( $inquiry_post_id, '_fw_inquiry_email', $email );
        update_post_meta( $inquiry_post_id, '_fw_inquiry_phone', $phone );
        update_post_meta( $inquiry_post_id, '_fw_inquiry_brand', $car_brand );
        update_post_meta( $inquiry_post_id, '_fw_inquiry_model', $car_model );
        update_post_meta( $inquiry_post_id, '_fw_inquiry_year', $car_year );
        update_post_meta( $inquiry_post_id, '_fw_inquiry_service', $service );
        update_post_meta( $inquiry_post_id, '_fw_inquiry_contact_pref', $contact_pref );
        update_post_meta( $inquiry_post_id, '_fw_inquiry_message', $message );
        if ( $file_url ) {
            update_post_meta( $inquiry_post_id, '_fw_inquiry_file_url', $file_url );
        }

        // Statistik inkrementieren (offer_request)
        if ( function_exists( 'findewerkstatt_record_stat_event' ) ) {
            findewerkstatt_record_stat_event( $workshop_id, 'offer_request' );
        }
    }

    // 7. E-Mail an Werkstattinhaber / Admin
    $workshop_title = get_the_title( $workshop_id );
    $workshop_email = get_post_meta( $workshop_id, '_mechanic_email', true );
    $admin_email    = get_option( 'admin_email' );
    $recipient      = ( ! empty( $workshop_email ) && is_email( $workshop_email ) ) ? $workshop_email : $admin_email;

    $subject = sprintf( '[FindeWerkstatt.de] Neue Angebotsanfrage für %s: %s', $workshop_title, ( $service ?: 'Reparatur' ) );

    $body  = "Guten Tag,\n\n";
    $body .= "über Ihr Werkstattprofil auf FindeWerkstatt.de ist eine neue Angebotsanfrage eingegangen:\n\n";
    $body .= "--------------------------------------------------\n";
    $body .= "Werkstatt:            " . $workshop_title . "\n";
    $body .= "Kunde:                " . $name . "\n";
    $body .= "E-Mail:               " . $email . "\n";
    if ( $phone )        $body .= "Telefon:              " . $phone . "\n";
    if ( $vehicle_str )  $body .= "Fahrzeug:             " . $vehicle_str . "\n";
    if ( $service )      $body .= "Gewünschte Leistung:  " . $service . "\n";
    $pref_labels = array( 'phone' => 'Telefon', 'whatsapp' => 'WhatsApp', 'email' => 'E-Mail' );
    $body .= "Bevorzugter Kontakt:  " . ( $pref_labels[ $contact_pref ] ?? 'E-Mail' ) . "\n";
    if ( $file_url )     $body .= "Angehängte Datei:     " . $file_url . "\n";
    $body .= "--------------------------------------------------\n\n";
    $body .= "Problem- / Fehlerbeschreibung des Kunden:\n";
    $body .= $message . "\n\n";
    $body .= "--\nBitte antworten Sie dem Kunden zeitnah über den gewünschten Kontaktweg.\nFindeWerkstatt.de";

    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'Reply-To: ' . $name . ' <' . $email . '>',
    );

    wp_mail( $recipient, $subject, $body, $headers );

    // 8. Bestätigungs-E-Mail an Kunden
    $customer_subject = sprintf( 'Ihre Anfrage an %s über FindeWerkstatt.de erhalten', $workshop_title );
    $customer_body  = "Guten Tag " . $name . ",\n\n";
    $customer_body .= "vielen Dank für Ihre Anfrage an " . $workshop_title . " über FindeWerkstatt.de.\n";
    $customer_body .= "Ihre Anfrage wurde erfolgreich übermittelt. Der Betrieb wird sich in Kürze bei Ihnen melden.\n\n";
    $customer_body .= "Zusammenfassung Ihrer Angaben:\n";
    $customer_body .= "--------------------------------------------------\n";
    $customer_body .= "Werkstatt: " . $workshop_title . "\n";
    if ( $vehicle_str ) $customer_body .= "Fahrzeug:  " . $vehicle_str . "\n";
    if ( $service )     $customer_body .= "Leistung:  " . $service . "\n";
    $customer_body .= "--------------------------------------------------\n\n";
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
 * 3. Frontend-Formular rendern
 */
function findewerkstatt_render_quote_inquiry_form( $workshop_id ) {
    $status   = isset( $_GET['inquiry_status'] ) ? sanitize_key( $_GET['inquiry_status'] ) : '';
    $services = get_terms( array( 'taxonomy' => 'service_type', 'hide_empty' => false ) );
    $brands   = FindeWerkstatt_German_Data::get_car_brands();
    ?>
    <section id="fw-inquiry-box" class="fw-box fw-inquiry-box" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Kostenvoranschlag & Terminanfrage' ) ); ?>">
        <div class="fw-inquiry-header">
            <div class="fw-inquiry-badge">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <span><?php echo esc_html( findewerkstatt_t( 'Direktanfrage' ) ); ?></span>
            </div>
            <h2><?php echo esc_html( findewerkstatt_t( 'Angebot & Kostenvoranschlag anfragen' ) ); ?></h2>
            <p class="fw-muted">
                <?php echo esc_html( sprintf( findewerkstatt_t( 'Senden Sie Ihre unverbindliche Reparatur- oder Terminanfrage direkt an %s. Sie erhalten zeitnah ein Angebot.' ), get_the_title( $workshop_id ) ) ); ?>
            </p>
        </div>

        <?php if ( 'success' === $status ) : ?>
            <div class="fw-form-notice fw-form-notice-success" role="status" style="margin-bottom:24px; padding:16px 20px; background:#ecfdf5; border:1px solid #10b981; border-radius:12px; color:#065f46;">
                <strong>✓ <?php echo esc_html( findewerkstatt_t( 'Vielen Dank! Ihre Anfrage wurde erfolgreich übermittelt.' ) ); ?></strong>
                <p style="margin:4px 0 0; font-size:13.5px;"><?php echo esc_html( findewerkstatt_t( 'Eine Bestätigung wurde an Ihre E-Mail-Adresse gesendet. Die Werkstatt wird sich in Kürze bei Ihnen melden.' ) ); ?></p>
            </div>
        <?php elseif ( 'missing_fields' === $status ) : ?>
            <div class="fw-form-notice fw-form-notice-error" role="alert" style="margin-bottom:20px; padding:14px 18px; background:#fef2f2; border:1px solid #ef4444; border-radius:12px; color:#991b1b;">
                ⚠️ <?php echo esc_html( findewerkstatt_t( 'Bitte füllen Sie alle erforderlichen Pflichtfelder (Name, E-Mail, Problembeschreibung) aus.' ) ); ?>
            </div>
        <?php elseif ( 'missing_dsgvo' === $status ) : ?>
            <div class="fw-form-notice fw-form-notice-error" role="alert" style="margin-bottom:20px; padding:14px 18px; background:#fef2f2; border:1px solid #ef4444; border-radius:12px; color:#991b1b;">
                ⚠️ <?php echo esc_html( findewerkstatt_t( 'Bitte stimmen Sie den Datenschutzbestimmungen zu, um die Anfrage abzusenden.' ) ); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?php echo esc_url( get_permalink( $workshop_id ) ); ?>" class="fw-inquiry-form" enctype="multipart/form-data">
            <input type="hidden" name="fw_action" value="fw_quote_inquiry">
            <input type="hidden" name="workshop_id" value="<?php echo esc_attr( $workshop_id ); ?>">
            <?php wp_nonce_field( 'fw_quote_inquiry_action', 'fw_quote_nonce' ); ?>

            <!-- Honeypot -->
            <div style="display:none;" aria-hidden="true">
                <label for="inquiry_website_hp">Website URL (Leave blank)</label>
                <input type="text" id="inquiry_website_hp" name="inquiry_website_hp" tabindex="-1" autocomplete="off">
            </div>

            <!-- Kontaktdaten -->
            <div class="fw-inquiry-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:16px;">
                <div class="fw-form-field">
                    <label for="inquiry-name"><?php echo esc_html( findewerkstatt_t( 'Ihr Name' ) ); ?> <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="inquiry-name" name="customer_name" required placeholder="<?php echo esc_attr( findewerkstatt_t( 'Vor- und Nachname' ) ); ?>" class="fw-input">
                </div>

                <div class="fw-form-field">
                    <label for="inquiry-email"><?php echo esc_html( findewerkstatt_t( 'Ihre E-Mail-Adresse' ) ); ?> <span style="color:#ef4444;">*</span></label>
                    <input type="email" id="inquiry-email" name="customer_email" required placeholder="<?php echo esc_attr( findewerkstatt_t( 'beispiel@domain.de' ) ); ?>" class="fw-input">
                </div>

                <div class="fw-form-field">
                    <label for="inquiry-phone"><?php echo esc_html( findewerkstatt_t( 'Telefonnummer' ) ); ?></label>
                    <input type="tel" id="inquiry-phone" name="customer_phone" placeholder="<?php echo esc_attr( findewerkstatt_t( '+49 151 12345678' ) ); ?>" class="fw-input">
                </div>
            </div>

            <!-- Fahrzeugdaten -->
            <div class="fw-inquiry-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px; margin-bottom:16px;">
                <div class="fw-form-field">
                    <label for="inquiry-brand"><?php echo esc_html( findewerkstatt_t( 'Fahrzeugmarke' ) ); ?></label>
                    <select id="inquiry-brand" name="car_brand" class="fw-input">
                        <option value=""><?php echo esc_html( findewerkstatt_t( 'Marke wählen...' ) ); ?></option>
                        <?php foreach ( $brands as $brand ) : ?>
                            <option value="<?php echo esc_attr( $brand ); ?>"><?php echo esc_html( $brand ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="fw-form-field">
                    <label for="inquiry-model"><?php echo esc_html( findewerkstatt_t( 'Modell' ) ); ?></label>
                    <input type="text" id="inquiry-model" name="car_model" placeholder="<?php echo esc_attr( findewerkstatt_t( 'z. B. Golf VII, 3er, A4' ) ); ?>" class="fw-input">
                </div>

                <div class="fw-form-field">
                    <label for="inquiry-year"><?php echo esc_html( findewerkstatt_t( 'Baujahr' ) ); ?></label>
                    <input type="number" id="inquiry-year" name="car_year" min="1970" max="<?php echo esc_attr( date( 'Y' ) + 1 ); ?>" placeholder="<?php echo esc_attr( findewerkstatt_t( 'z. B. 2018' ) ); ?>" class="fw-input">
                </div>
            </div>

            <!-- Leistung & Nachricht -->
            <div class="fw-inquiry-grid" style="display:grid; grid-template-columns:1fr; gap:16px; margin-bottom:16px;">
                <div class="fw-form-field">
                    <label for="inquiry-service"><?php echo esc_html( findewerkstatt_t( 'Gewünschte Leistung / Problemkategorie' ) ); ?></label>
                    <select id="inquiry-service" name="service_type" class="fw-input">
                        <option value=""><?php echo esc_html( findewerkstatt_t( 'Bitte auswählen...' ) ); ?></option>
                        <?php if ( $services && ! is_wp_error( $services ) ) : ?>
                            <?php foreach ( $services as $s ) : ?>
                                <option value="<?php echo esc_attr( $s->name ); ?>"><?php echo esc_html( findewerkstatt_t( $s->name ) ); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <option value="<?php echo esc_attr( findewerkstatt_t( 'Sonstiges / Nicht aufgeführt' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Sonstiges / Nicht aufgeführt' ) ); ?></option>
                    </select>
                </div>

                <div class="fw-form-field">
                    <label for="inquiry-message"><?php echo esc_html( findewerkstatt_t( 'Problembeschreibung / Anliegen' ) ); ?> <span style="color:#ef4444;">*</span></label>
                    <textarea id="inquiry-message" name="customer_message" required rows="4" placeholder="<?php echo esc_attr( findewerkstatt_t( 'Beschreiben Sie möglichst genau das Problem, Geräusche, Fehlermeldungen oder Ihre Wunschleistung...' ) ); ?>" class="fw-input"></textarea>
                </div>
            </div>

            <!-- Foto-Upload & Bevorzugter Kontaktkanal -->
            <div class="fw-inquiry-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:16px; margin-bottom:20px;">
                <div class="fw-form-field">
                    <label for="inquiry-photo">
                        <span>📷 <?php echo esc_html( findewerkstatt_t( 'Foto oder Dokument anhängen' ) ); ?></span>
                        <span class="fw-muted" style="font-weight:normal; font-size:12px;">(optional, max. 5 MB)</span>
                    </label>
                    <input type="file" id="inquiry-photo" name="quote_photo" accept="image/jpeg,image/png,image/webp,application/pdf" class="fw-input-file" style="font-size:13px;">
                </div>

                <div class="fw-form-field">
                    <label style="display:block; margin-bottom:8px;"><?php echo esc_html( findewerkstatt_t( 'Bevorzugter Kontaktweg' ) ); ?></label>
                    <div style="display:flex; gap:16px; align-items:center; flex-wrap:wrap; font-size:13.5px;">
                        <label style="display:inline-flex; align-items:center; gap:6px; cursor:pointer;">
                            <input type="radio" name="contact_pref" value="email" checked>
                            <span>✉️ <?php echo esc_html( findewerkstatt_t( 'E-Mail' ) ); ?></span>
                        </label>
                        <label style="display:inline-flex; align-items:center; gap:6px; cursor:pointer;">
                            <input type="radio" name="contact_pref" value="phone">
                            <span>📞 <?php echo esc_html( findewerkstatt_t( 'Telefon' ) ); ?></span>
                        </label>
                        <label style="display:inline-flex; align-items:center; gap:6px; cursor:pointer;">
                            <input type="radio" name="contact_pref" value="whatsapp">
                            <span>💬 WhatsApp</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- DSGVO & Absenden -->
            <div class="fw-form-field fw-form-field-checkbox" style="margin-bottom:20px;">
                <label style="display:flex; align-items:flex-start; gap:10px; font-size:13px; color:#475569; cursor:pointer;">
                    <input type="checkbox" name="dsgvo_consent" value="1" required style="margin-top:3px;">
                    <span>
                        <?php echo esc_html( findewerkstatt_t( 'Ich willige ein, dass meine Angaben zur Bearbeitung meiner Anfrage an den ausgewählten Betrieb übermittelt und verarbeitet werden. Weitere Hinweise finden Sie in der' ) ); ?>
                        <a href="<?php echo esc_url( findewerkstatt_page_url( 'datenschutz' ) ); ?>" target="_blank" rel="noopener" style="text-decoration:underline;"><?php echo esc_html( findewerkstatt_t( 'Datenschutzerklärung' ) ); ?></a>.
                    </span>
                </label>
            </div>

            <button type="submit" class="fw-btn fw-btn-primary fw-btn-lg" style="display:inline-flex; align-items:center; gap:8px;">
                <span>⚡</span>
                <span><?php echo esc_html( findewerkstatt_t( 'Kostenlose Anfrage absenden' ) ); ?></span>
            </button>
        </form>
    </section>
    <?php
}
