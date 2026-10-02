<?php
/**
 * FindeWerkstatt.de — Admin Meta Boxes für Kfz-Werkstätten
 * 
 * Beinhaltet:
 * - Kontaktdaten (Telefon, WhatsApp, E-Mail, Website)
 * - Standort & Geokoordinaten (Straße, PLZ, Lat, Lng)
 * - Gütesiegel & Zertifikate (Meisterbetrieb, Verifizierter Partner, 24h Notdienst)
 * - Öffnungszeiten & Bewertungen
 * 
 * @package FindeWerkstatt
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FindeWerkstatt_Meta_Boxes {

    public static function init() {
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
        add_action( 'save_post_mechanic', array( __CLASS__, 'save_meta_boxes' ) );
    }

    public static function add_meta_boxes() {
        add_meta_box(
            'fw_mechanic_details',
            '🇩🇪 Werkstatt-Stammdaten & Kontakt',
            array( __CLASS__, 'render_details_box' ),
            'mechanic',
            'normal',
            'high'
        );

        add_meta_box(
            'fw_mechanic_badges',
            '⭐ Gütesiegel & Zertifizierungen',
            array( __CLASS__, 'render_badges_box' ),
            'mechanic',
            'side',
            'high'
        );
    }

    public static function render_details_box( $post ) {
        wp_nonce_field( 'fw_save_mechanic_meta', 'fw_mechanic_nonce' );

        $phone       = get_post_meta( $post->ID, '_mechanic_phone', true );
        $whatsapp    = get_post_meta( $post->ID, '_mechanic_whatsapp', true );
        $email       = get_post_meta( $post->ID, '_mechanic_email', true );
        $email_public = get_post_meta( $post->ID, '_mechanic_email_public', true );
        $website     = get_post_meta( $post->ID, '_mechanic_website', true );
        $address     = get_post_meta( $post->ID, '_mechanic_address', true );
        $plz         = get_post_meta( $post->ID, '_mechanic_plz', true );
        $lat         = get_post_meta( $post->ID, '_mechanic_latitude', true );
        $lng         = get_post_meta( $post->ID, '_mechanic_longitude', true );
        $hours_week  = get_post_meta( $post->ID, '_mechanic_hours_weekday', true );
        $hours_sat   = get_post_meta( $post->ID, '_mechanic_hours_saturday', true );
        $hours_sun   = get_post_meta( $post->ID, '_mechanic_hours_sunday', true );
        ?>
        <style>
            .fw-admin-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
            .fw-admin-field label { display: block; font-weight: 600; margin-bottom: 5px; font-size: 13px; }
            .fw-admin-field input { width: 100%; padding: 8px 10px; border-radius: 4px; border: 1px solid #ccd0d4; }
            .fw-admin-section-title { font-weight: 700; margin: 15px 0 10px; padding-bottom: 5px; border-bottom: 1px solid #e2e8f0; color: #1e293b; }
        </style>

        <div class="fw-admin-section-title">📞 Kontakt & Kommunikation</div>
        <div class="fw-admin-grid">
            <div class="fw-admin-field">
                <label for="fw_phone">Telefonnummer (z. B. +49 30 1234567):</label>
                <input type="text" id="fw_phone" name="_mechanic_phone" value="<?php echo esc_attr( $phone ); ?>">
            </div>
            <div class="fw-admin-field">
                <label for="fw_whatsapp">WhatsApp-Nummer (z. B. +49 170 1234567):</label>
                <input type="text" id="fw_whatsapp" name="_mechanic_whatsapp" value="<?php echo esc_attr( $whatsapp ); ?>">
            </div>
            <div class="fw-admin-field">
                <label for="fw_email">E-Mail-Adresse:</label>
                <input type="email" id="fw_email" name="_mechanic_email" value="<?php echo esc_attr( $email ); ?>">
                <input type="hidden" name="fw_email_public_box_present" value="1">
                <label for="fw_email_public" style="display:flex; align-items:flex-start; gap:8px; margin-top:8px; font-weight:400;">
                    <input type="checkbox" id="fw_email_public" name="_mechanic_email_public" value="yes" <?php checked( $email_public, 'yes' ); ?> style="width:auto; margin-top:2px;">
                    <span>Diese E-Mail-Adresse öffentlich anzeigen (nur mit Zustimmung des Betriebs).</span>
                </label>
            </div>
            <div class="fw-admin-field">
                <label for="fw_website">Offizielle Website (inkl. https://):</label>
                <input type="url" id="fw_website" name="_mechanic_website" value="<?php echo esc_attr( $website ); ?>">
            </div>
        </div>

        <div class="fw-admin-section-title">📍 Standort & Geodaten</div>
        <div class="fw-admin-grid">
            <div class="fw-admin-field">
                <label for="fw_address">Straße & Hausnummer:</label>
                <input type="text" id="fw_address" name="_mechanic_address" value="<?php echo esc_attr( $address ); ?>">
            </div>
            <div class="fw-admin-field">
                <label for="fw_plz">Postleitzahl (PLZ):</label>
                <input type="text" id="fw_plz" name="_mechanic_plz" value="<?php echo esc_attr( $plz ); ?>">
            </div>
            <div class="fw-admin-field">
                <label for="fw_lat">Breitengrad (Latitude, z. B. 52.520008):</label>
                <input type="text" id="fw_lat" name="_mechanic_latitude" value="<?php echo esc_attr( $lat ); ?>">
            </div>
            <div class="fw-admin-field">
                <label for="fw_lng">Längengrad (Longitude, z. B. 13.404954):</label>
                <input type="text" id="fw_lng" name="_mechanic_longitude" value="<?php echo esc_attr( $lng ); ?>">
            </div>
        </div>

        <div class="fw-admin-section-title">⏰ Öffnungszeiten</div>
        <p>Leer lassen, wenn unbekannt. Beispiele: 08:00–18:00, 08:00–12:00 / 13:00–18:00, 22:00–02:00, 24h oder Geschlossen.</p>
        <div class="fw-admin-grid">
            <div class="fw-admin-field">
                <label for="fw_hours_week">Montag – Freitag:</label>
                <input type="text" id="fw_hours_week" name="_mechanic_hours_weekday" value="<?php echo esc_attr( $hours_week ); ?>">
            </div>
            <div class="fw-admin-field">
                <label for="fw_hours_sat">Samstag:</label>
                <input type="text" id="fw_hours_sat" name="_mechanic_hours_saturday" value="<?php echo esc_attr( $hours_sat ); ?>">
            </div>
            <div class="fw-admin-field">
                <label for="fw_hours_sun">Sonntag:</label>
                <input type="text" id="fw_hours_sun" name="_mechanic_hours_sunday" value="<?php echo esc_attr( $hours_sun ); ?>">
            </div>
        </div>
        <?php
    }

    public static function render_badges_box( $post ) {
        $is_verified = get_post_meta( $post->ID, '_mechanic_is_verified', true );
        $is_master   = get_post_meta( $post->ID, '_mechanic_is_master', true );
        $is_24h      = get_post_meta( $post->ID, '_mechanic_emergency_24h', true );
        $rating      = findewerkstatt_get_rating( $post->ID );
        $rating_avg  = null === $rating['rating'] ? '' : $rating['rating'];
        $rating_cnt  = $rating['count'];
        ?>
        <input type="hidden" name="fw_badges_box_present" value="1">
        <div style="margin-bottom:12px;">
            <label style="display:flex; align-items:center; gap:8px; font-weight:600; cursor:pointer;">
                <input type="checkbox" name="_mechanic_is_verified" value="yes" <?php checked( $is_verified, 'yes' ); ?>>
                <span>🛡️ Geprüfter Partnerbetrieb</span>
            </label>
        </div>
        <div style="margin-bottom:12px;">
            <label style="display:flex; align-items:center; gap:8px; font-weight:600; cursor:pointer;">
                <input type="checkbox" name="_mechanic_is_master" value="yes" <?php checked( $is_master, 'yes' ); ?>>
                <span>🏆 Kfz-Meisterbetrieb (Handwerkskammer)</span>
            </label>
        </div>
        <div style="margin-bottom:16px;">
            <label style="display:flex; align-items:center; gap:8px; font-weight:600; cursor:pointer;">
                <input type="checkbox" name="_mechanic_emergency_24h" value="yes" <?php checked( $is_24h, 'yes' ); ?>>
                <span>🚨 24h Notdienst / Pannenhilfe</span>
            </label>
        </div>

        <hr style="border:0; border-top:1px solid #e2e8f0; margin:14px 0;">

        <div style="margin-bottom:10px;">
            <label for="fw_rating_avg" style="font-size:12px; font-weight:600; display:block;">Nachgewiesene Durchschnittsbewertung (0 bis 5; leer = unbekannt):</label>
            <input type="text" id="fw_rating_avg" name="_mechanic_rating_avg" value="<?php echo esc_attr( $rating_avg ); ?>" style="width:100%; padding:4px 8px;">
        </div>
        <div>
            <label for="fw_rating_cnt" style="font-size:12px; font-weight:600; display:block;">Anzahl Bewertungen (z. B. 24):</label>
            <input type="number" min="0" step="1" id="fw_rating_cnt" name="_mechanic_rating_count" value="<?php echo esc_attr( $rating_cnt ); ?>" style="width:100%; padding:4px 8px;">
        </div>
        <?php
    }

    public static function save_meta_boxes( $post_id ) {
        if ( ! isset( $_POST['fw_mechanic_nonce'] ) || ! is_string( $_POST['fw_mechanic_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['fw_mechanic_nonce'] ), 'fw_save_mechanic_meta' ) ) {
            return;
        }

        if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || 'mechanic' !== get_post_type( $post_id ) ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Text Fields
        $fields = array(
            '_mechanic_phone'          => 'findewerkstatt_normalize_phone',
            '_mechanic_whatsapp'       => 'findewerkstatt_normalize_phone',
            '_mechanic_email'          => 'sanitize_email',
            '_mechanic_website'        => 'findewerkstatt_get_website_url',
            '_mechanic_address'        => 'sanitize_text_field',
            '_mechanic_plz'            => 'sanitize_text_field',
            '_mechanic_latitude'       => 'sanitize_text_field',
            '_mechanic_longitude'      => 'sanitize_text_field',
            '_mechanic_hours_weekday'  => 'sanitize_text_field',
            '_mechanic_hours_saturday' => 'sanitize_text_field',
            '_mechanic_hours_sunday'   => 'sanitize_text_field',
            '_mechanic_rating_avg'     => 'findewerkstatt_normalize_rating',
            '_mechanic_rating_count'   => 'findewerkstatt_normalize_review_count',
        );

        foreach ( $fields as $field => $sanitizer ) {
            if ( isset( $_POST[ $field ] ) && is_scalar( $_POST[ $field ] ) ) {
                $raw   = wp_unslash( $_POST[ $field ] );
                $value = call_user_func( $sanitizer, $raw );
                if ( in_array( $field, array( '_mechanic_latitude', '_mechanic_longitude' ), true ) ) {
                    $coordinate = str_replace( ',', '.', trim( $raw ) );
                    $limit      = '_mechanic_latitude' === $field ? 90 : 180;
                    $value      = is_numeric( $coordinate ) && is_finite( (float) $coordinate ) && abs( (float) $coordinate ) <= $limit ? (float) $coordinate : '';
                } elseif ( '_mechanic_plz' === $field && '' !== $value && ! preg_match( '/^[0-9]{5}$/', $value ) ) {
                    $value = '';
                }
                if ( null === $value || '' === $value ) {
                    delete_post_meta( $post_id, $field );
                } else {
                    update_post_meta( $post_id, $field, is_string( $value ) ? wp_slash( $value ) : $value );
                }
            }
        }

        // Zustimmung bleibt bei Teilanfragen ohne diese Formulargruppe erhalten.
        if ( isset( $_POST['fw_email_public_box_present'] ) && '1' === $_POST['fw_email_public_box_present'] ) {
            $email_public = isset( $_POST['_mechanic_email_public'] ) && 'yes' === $_POST['_mechanic_email_public'] ? 'yes' : 'no';
            update_post_meta( $post_id, '_mechanic_email_public', $email_public );
        }

        // Checkboxes
        if ( isset( $_POST['fw_badges_box_present'] ) && '1' === $_POST['fw_badges_box_present'] ) {
            $checkboxes = array( '_mechanic_is_verified', '_mechanic_is_master', '_mechanic_emergency_24h' );
            foreach ( $checkboxes as $cb ) {
                $val = isset( $_POST[ $cb ] ) && $_POST[ $cb ] === 'yes' ? 'yes' : 'no';
                update_post_meta( $post_id, $cb, $val );
            }
        }
    }
}

FindeWerkstatt_Meta_Boxes::init();
