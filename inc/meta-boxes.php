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
        $website     = get_post_meta( $post->ID, '_mechanic_website', true );
        $address     = get_post_meta( $post->ID, '_mechanic_address', true );
        $plz         = get_post_meta( $post->ID, '_mechanic_plz', true );
        $lat         = get_post_meta( $post->ID, '_mechanic_latitude', true );
        $lng         = get_post_meta( $post->ID, '_mechanic_longitude', true );
        $hours_week  = get_post_meta( $post->ID, '_mechanic_hours_weekday', true ) ?: '08:00 - 18:00 Uhr';
        $hours_sat   = get_post_meta( $post->ID, '_mechanic_hours_saturday', true ) ?: '09:00 - 13:00 Uhr';
        $hours_sun   = get_post_meta( $post->ID, '_mechanic_hours_sunday', true ) ?: 'Geschlossen';
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
                <label for="fw_whatsapp">WhatsApp-Nummer (ohne + oder Leerzeichen, z. B. 491701234567):</label>
                <input type="text" id="fw_whatsapp" name="_mechanic_whatsapp" value="<?php echo esc_attr( $whatsapp ); ?>">
            </div>
            <div class="fw-admin-field">
                <label for="fw_email">E-Mail-Adresse:</label>
                <input type="email" id="fw_email" name="_mechanic_email" value="<?php echo esc_attr( $email ); ?>">
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
                <label for="fw_hours_sun">Sonntag / Feiertage:</label>
                <input type="text" id="fw_hours_sun" name="_mechanic_hours_sunday" value="<?php echo esc_attr( $hours_sun ); ?>">
            </div>
        </div>
        <?php
    }

    public static function render_badges_box( $post ) {
        $is_verified = get_post_meta( $post->ID, '_mechanic_is_verified', true );
        $is_master   = get_post_meta( $post->ID, '_mechanic_is_master', true );
        $is_24h      = get_post_meta( $post->ID, '_mechanic_emergency_24h', true );
        $rating_avg  = get_post_meta( $post->ID, '_mechanic_rating_avg', true ) ?: '4.9';
        $rating_cnt  = get_post_meta( $post->ID, '_mechanic_rating_count', true ) ?: '18';
        ?>
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
            <label for="fw_rating_avg" style="font-size:12px; font-weight:600; display:block;">Durchschnittsbewertung (z. B. 4.9):</label>
            <input type="text" id="fw_rating_avg" name="_mechanic_rating_avg" value="<?php echo esc_attr( $rating_avg ); ?>" style="width:100%; padding:4px 8px;">
        </div>
        <div>
            <label for="fw_rating_cnt" style="font-size:12px; font-weight:600; display:block;">Anzahl Bewertungen (z. B. 24):</label>
            <input type="number" id="fw_rating_cnt" name="_mechanic_rating_count" value="<?php echo esc_attr( $rating_cnt ); ?>" style="width:100%; padding:4px 8px;">
        </div>
        <?php
    }

    public static function save_meta_boxes( $post_id ) {
        if ( ! isset( $_POST['fw_mechanic_nonce'] ) || ! wp_verify_nonce( $_POST['fw_mechanic_nonce'], 'fw_save_mechanic_meta' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Text Fields
        $fields = array(
            '_mechanic_phone'          => 'sanitize_text_field',
            '_mechanic_whatsapp'       => 'sanitize_text_field',
            '_mechanic_email'          => 'sanitize_email',
            '_mechanic_website'        => 'esc_url_raw',
            '_mechanic_address'        => 'sanitize_text_field',
            '_mechanic_plz'            => 'sanitize_text_field',
            '_mechanic_latitude'       => 'sanitize_text_field',
            '_mechanic_longitude'      => 'sanitize_text_field',
            '_mechanic_hours_weekday'  => 'sanitize_text_field',
            '_mechanic_hours_saturday' => 'sanitize_text_field',
            '_mechanic_hours_sunday'   => 'sanitize_text_field',
            '_mechanic_rating_avg'     => 'sanitize_text_field',
            '_mechanic_rating_count'   => 'absint',
        );

        foreach ( $fields as $field => $sanitizer ) {
            if ( isset( $_POST[ $field ] ) ) {
                update_post_meta( $post_id, $field, call_user_func( $sanitizer, $_POST[ $field ] ) );
            }
        }

        // Checkboxes
        $checkboxes = array( '_mechanic_is_verified', '_mechanic_is_master', '_mechanic_emergency_24h' );
        foreach ( $checkboxes as $cb ) {
            $val = isset( $_POST[ $cb ] ) && $_POST[ $cb ] === 'yes' ? 'yes' : 'no';
            update_post_meta( $post_id, $cb, $val );
        }
    }
}

FindeWerkstatt_Meta_Boxes::init();
