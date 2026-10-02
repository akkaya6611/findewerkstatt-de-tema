<?php
/** Site identity and information supplied by the operator. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function findewerkstatt_get_site_details() {
    $defaults = array(
        'name' => 'Mis Technology 360',
        'address' => "Kocasinan\nKayseri\nTürkei",
        'address_complete' => '',
        'email' => 'admin@findewerkstatt.de',
        'phone' => '',
        'register' => '',
        'vat' => '',
        'hosting' => '',
    );
    $saved = get_option( 'findewerkstatt_site_details', array() );
    return wp_parse_args( is_array( $saved ) ? $saved : array(), $defaults );
}

function findewerkstatt_sanitize_site_details( $input ) {
    $input = is_array( $input ) ? $input : array();
    $clean = array();
    foreach ( array( 'name', 'phone', 'register', 'vat' ) as $key ) {
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
    $labels = array(
        'name' => 'Vollständiger Name des Betreibers / Unternehmens',
        'address' => 'Ladungsfähige Anschrift (Straße, Hausnummer, Postleitzahl, Ort, Land)',
        'email' => 'E-Mail-Adresse für Kontakt und Datenschutz',
        'phone' => 'Telefonnummer (optional)',
        'register' => 'Register und Registernummer (falls vorhanden)',
        'vat' => 'Umsatzsteuer- / Wirtschafts-Identifikationsnummer (falls vorhanden)',
        'hosting' => 'Hosting-Anbieter und Ort der Datenverarbeitung',
    );
    ?>
    <div class="wrap">
        <h1>Website-Angaben für FindeWerkstatt.de</h1>
        <p>Diese Angaben erscheinen im Impressum und in den Datenschutzhinweisen. Kontaktbenachrichtigungen werden an die hier hinterlegte E-Mail-Adresse gesendet.</p>
        <?php settings_errors(); ?>
        <form method="post" action="options.php">
            <?php settings_fields( 'findewerkstatt_site' ); ?>
            <table class="form-table" role="presentation">
                <?php foreach ( $labels as $key => $label ) : ?>
                    <tr><th scope="row"><label for="fw-site-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th><td>
                        <?php if ( in_array( $key, array( 'address', 'hosting' ), true ) ) : ?>
                            <textarea class="large-text" rows="4" id="fw-site-<?php echo esc_attr( $key ); ?>" name="findewerkstatt_site_details[<?php echo esc_attr( $key ); ?>]"><?php echo esc_textarea( $details[ $key ] ); ?></textarea>
                        <?php else : ?>
                            <input class="regular-text" type="<?php echo $key === 'email' ? 'email' : 'text'; ?>" id="fw-site-<?php echo esc_attr( $key ); ?>" name="findewerkstatt_site_details[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $details[ $key ] ); ?>">
                        <?php endif; ?>
                    </td></tr>
                <?php endforeach; ?>
                <tr><th scope="row">Anschrift bestätigen</th><td><label><input type="checkbox" name="findewerkstatt_site_details[address_complete]" value="yes" <?php checked( $details['address_complete'], 'yes' ); ?>> Die Anschrift enthält die vollständigen Zustellangaben.</label></td></tr>
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
