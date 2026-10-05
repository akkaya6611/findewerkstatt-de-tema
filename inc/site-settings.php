<?php
/**
 * Site identity and German Legal (Impressum) settings.
 *
 * @package FindeWerkstatt
 * @version 3.4.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function findewerkstatt_get_site_details() {
    $defaults = array(
        'name'                => 'FindeWerkstatt.de',
        'company_name'        => 'Mis Technology 360',
        'operator'            => 'Serkan Akkaya',
        'legal_form'          => '',
        'street'              => 'Kocasinan',
        'house_number'        => '',
        'plz'                 => '',
        'city'                => 'Kayseri',
        'country'             => 'Türkei',
        'address'             => "Kocasinan\nKayseri\nTürkei",
        'address_complete'    => '',
        'email'               => 'admin@findewerkstatt.de',
        'phone'               => '',
        'representative'      => '',
        'commercial_register' => '',
        'register_number'     => '',
        'register'            => '',
        'vat_id'              => '',
        'vat'                 => '',
        'hosting'             => 'Hostinger International Ltd.',
    );
    $saved = get_option( 'findewerkstatt_site_details', array() );
    $details = wp_parse_args( is_array( $saved ) ? $saved : array(), $defaults );

    // Normalize backward compatibility
    if ( empty( $details['company_name'] ) && ! empty( $details['name'] ) ) {
        $details['company_name'] = $details['name'];
    }
    if ( empty( $details['vat_id'] ) && ! empty( $details['vat'] ) ) {
        $details['vat_id'] = $details['vat'];
    }
    if ( empty( $details['commercial_register'] ) && ! empty( $details['register'] ) ) {
        $details['commercial_register'] = $details['register'];
    }

    return $details;
}

function findewerkstatt_sanitize_site_details( $input ) {
    $input = is_array( $input ) ? $input : array();
    $clean = array();

    $text_keys = array(
        'name', 'company_name', 'operator', 'legal_form',
        'street', 'house_number', 'plz', 'city', 'country',
        'phone', 'representative', 'commercial_register', 'register_number',
        'register', 'vat_id', 'vat'
    );

    foreach ( $text_keys as $key ) {
        $clean[ $key ] = sanitize_text_field( is_scalar( $input[ $key ] ?? null ) ? $input[ $key ] : '' );
    }

    foreach ( array( 'address', 'hosting' ) as $key ) {
        $clean[ $key ] = sanitize_textarea_field( is_scalar( $input[ $key ] ?? null ) ? $input[ $key ] : '' );
    }

    $clean['email'] = sanitize_email( is_scalar( $input['email'] ?? null ) ? $input['email'] : '' );
    if ( ! is_email( $clean['email'] ) ) {
        add_settings_error( 'findewerkstatt_site_details', 'email', 'Bitte geben Sie eine gültige E-Mail-Adresse an.' );
        $clean['email'] = findewerkstatt_get_site_details()['email'];
    }

    $clean['address_complete'] = ! empty( $input['address_complete'] ) ? 'yes' : '';
    return $clean;
}

add_action( 'admin_init', function () {
    register_setting( 'findewerkstatt_site', 'findewerkstatt_site_details', array(
        'type' => 'array', 'sanitize_callback' => 'findewerkstatt_sanitize_site_details',
    ) );
} );

add_action( 'admin_menu', function () {
    add_theme_page( 'Website-Angaben', 'Website-Angaben', 'manage_options', 'findewerkstatt-site', 'findewerkstatt_site_settings_page' );
} );

function findewerkstatt_site_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) { return; }
    $details = findewerkstatt_get_site_details();
    ?>
    <div class="wrap">
        <h1>Website-Angaben für FindeWerkstatt.de (Impressum & Rechtliches)</h1>
        <p>Diese Angaben erscheinen im Impressum und in den Datenschutzhinweisen gemäß § 5 DDG (ehemals TMG) und DSGVO.</p>
        <?php settings_errors(); ?>
        <form method="post" action="options.php">
            <?php settings_fields( 'findewerkstatt_site' ); ?>
            <h2 class="title">1. Betreiber & Unternehmen</h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="fw-site-company_name">Firmenname</label></th>
                    <td><input class="regular-text" type="text" id="fw-site-company_name" name="findewerkstatt_site_details[company_name]" value="<?php echo esc_attr( $details['company_name'] ); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="fw-site-operator">Betreiber / Inhaber</label></th>
                    <td><input class="regular-text" type="text" id="fw-site-operator" name="findewerkstatt_site_details[operator]" value="<?php echo esc_attr( $details['operator'] ); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="fw-site-legal_form">Rechtsform</label></th>
                    <td><input class="regular-text" type="text" id="fw-site-legal_form" name="findewerkstatt_site_details[legal_form]" value="<?php echo esc_attr( $details['legal_form'] ); ?>" placeholder="z. B. GmbH, Einzelunternehmen, GbR"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="fw-site-representative">Vertretungsberechtigte Person</label></th>
                    <td><input class="regular-text" type="text" id="fw-site-representative" name="findewerkstatt_site_details[representative]" value="<?php echo esc_attr( $details['representative'] ); ?>"></td>
                </tr>
            </table>

            <h2 class="title">2. Ladungsfähige Anschrift</h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="fw-site-street">Straße & Hausnummer</label></th>
                    <td>
                        <input class="regular-text" style="width:250px;" type="text" id="fw-site-street" name="findewerkstatt_site_details[street]" value="<?php echo esc_attr( $details['street'] ); ?>" placeholder="Straße">
                        <input class="small-text" style="width:80px;" type="text" id="fw-site-house_number" name="findewerkstatt_site_details[house_number]" value="<?php echo esc_attr( $details['house_number'] ); ?>" placeholder="Nr.">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="fw-site-plz">PLZ & Ort</label></th>
                    <td>
                        <input class="small-text" style="width:80px;" type="text" id="fw-site-plz" name="findewerkstatt_site_details[plz]" value="<?php echo esc_attr( $details['plz'] ); ?>" placeholder="PLZ">
                        <input class="regular-text" style="width:250px;" type="text" id="fw-site-city" name="findewerkstatt_site_details[city]" value="<?php echo esc_attr( $details['city'] ); ?>" placeholder="Ort">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="fw-site-country">Land</label></th>
                    <td><input class="regular-text" type="text" id="fw-site-country" name="findewerkstatt_site_details[country]" value="<?php echo esc_attr( $details['country'] ); ?>" placeholder="Deutschland"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="fw-site-address">Vollständige Anschrift (Textblock)</label></th>
                    <td><textarea class="large-text" rows="3" id="fw-site-address" name="findewerkstatt_site_details[address]"><?php echo esc_textarea( $details['address'] ); ?></textarea></td>
                </tr>
            </table>

            <h2 class="title">3. Kontaktdaten</h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="fw-site-email">E-Mail-Adresse</label></th>
                    <td><input class="regular-text" type="email" id="fw-site-email" name="findewerkstatt_site_details[email]" value="<?php echo esc_attr( $details['email'] ); ?>" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="fw-site-phone">Telefonnummer</label></th>
                    <td><input class="regular-text" type="text" id="fw-site-phone" name="findewerkstatt_site_details[phone]" value="<?php echo esc_attr( $details['phone'] ); ?>"></td>
                </tr>
            </table>

            <h2 class="title">4. Register & Umsatzsteuer</h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="fw-site-commercial_register">Handelsregister / Amtsgericht</label></th>
                    <td><input class="regular-text" type="text" id="fw-site-commercial_register" name="findewerkstatt_site_details[commercial_register]" value="<?php echo esc_attr( $details['commercial_register'] ); ?>" placeholder="z. B. Amtsgericht München"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="fw-site-register_number">Registernummer</label></th>
                    <td><input class="regular-text" type="text" id="fw-site-register_number" name="findewerkstatt_site_details[register_number]" value="<?php echo esc_attr( $details['register_number'] ); ?>" placeholder="z. B. HRB 123456"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="fw-site-vat_id">USt-IdNr. (Umsatzsteuer-Identifikationsnummer)</label></th>
                    <td><input class="regular-text" type="text" id="fw-site-vat_id" name="findewerkstatt_site_details[vat_id]" value="<?php echo esc_attr( $details['vat_id'] ); ?>" placeholder="z. B. DE 123456789"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="fw-site-hosting">Hosting-Dienstleister</label></th>
                    <td><textarea class="large-text" rows="2" id="fw-site-hosting" name="findewerkstatt_site_details[hosting]"><?php echo esc_textarea( $details['hosting'] ); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row">Anschrift bestätigen</th>
                    <td><label><input type="checkbox" name="findewerkstatt_site_details[address_complete]" value="yes" <?php checked( $details['address_complete'], 'yes' ); ?>> Die Anschrift enthält die vollständigen Zustellangaben.</label></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

add_action( 'admin_notices', function () {
    if ( ! current_user_can( 'manage_options' ) ) { return; }
    $details = findewerkstatt_get_site_details();
    if ( $details['address_complete'] !== 'yes' || empty( $details['hosting'] ) ) {
        echo '<div class="notice notice-warning"><p>Bitte vervollständigen Sie die Anschrift und Hosting-Angaben für Impressum und Datenschutz unter <a href="' . esc_url( admin_url( 'themes.php?page=findewerkstatt-site' ) ) . '">Website-Angaben</a>.</p></div>';
    }
} );
