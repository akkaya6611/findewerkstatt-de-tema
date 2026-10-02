<?php
/** Two-step location selection for workshop searches. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Load the hierarchy once; the city field never includes unrelated states or counties. */
function findewerkstatt_location_picker_data() {
    static $data = null;
    if ( null !== $data ) { return $data; }
    $data = array( 'states' => array(), 'cities' => array(), 'terms' => array() );
    $terms = get_terms( array( 'taxonomy' => 'mechanic_city', 'hide_empty' => false, 'orderby' => 'name' ) );
    if ( is_wp_error( $terms ) ) { return $data; }
    foreach ( $terms as $term ) { $data['terms'][ $term->term_id ] = $term; }
    $known_states = array_column( FindeWerkstatt_German_Data::get_bundeslaender(), null, 'slug' );
    foreach ( $terms as $term ) {
        if ( ! $term->parent && isset( $known_states[ $term->slug ] ) ) {
            $data['states'][ $term->slug ] = $term->name;
            $data['cities'][ $term->slug ] = array();
        }
    }
    foreach ( $terms as $term ) {
        $state = findewerkstatt_city_directory_state( $term, $data['terms'] );
        if ( ! isset( $data['states'][ $state ] ) || findewerkstatt_city_directory_is_region( $term ) ) { continue; }
        if ( ! $term->parent && ! in_array( $state, array( 'berlin', 'hamburg' ), true ) ) { continue; }
        $label = preg_replace( '/\s*\(Stadt\)$/u', '', $term->name );
        $parent = $data['terms'][ $term->parent ] ?? null;
        if ( $parent && $parent->parent && ! findewerkstatt_city_directory_is_region( $parent ) ) {
            $label .= ' (' . preg_replace( '/\s*\(Stadt\)$/u', '', $parent->name ) . ')';
        }
        $data['cities'][ $state ][] = array( 'value' => $term->slug, 'label' => $label );
    }
    foreach ( $data['cities'] as &$cities ) {
        usort( $cities, function ( $a, $b ) { return strnatcasecmp( remove_accents( $a['label'] ), remove_accents( $b['label'] ) ); } );
    }
    unset( $cities );
    return $data;
}

/** Accessible server-rendered fields, enhanced in main.js after a state is selected. */
function findewerkstatt_render_location_picker( $prefix, $selection = array(), $home = false ) {
    $prefix = sanitize_html_class( $prefix );
    $data = findewerkstatt_location_picker_data();
    $state = $selection['state'] ?? '';
    $city = $selection['city'] ?? '';
    $choices = $data['cities'];
    // Existing region links stay precise, while new searches offer only towns and districts.
    if ( $city && isset( $data['states'][ $state ] ) ) {
        $known = array_column( $choices[ $state ], 'value' );
        if ( ! in_array( $city, $known, true ) ) {
            $term = get_term_by( 'slug', $city, 'mechanic_city' );
            if ( $term && ! is_wp_error( $term ) && findewerkstatt_city_directory_state( $term, $data['terms'] ) === $state ) {
                array_unshift( $choices[ $state ], array( 'value' => $city, 'label' => sprintf( findewerkstatt_location_translate( 'Region: %s' ), findewerkstatt_location_label( $term ) ) ) );
            }
        }
    }
    $field_class = $home ? 'fw-search-field' : 'fw-form-field';
    $enabled = isset( $data['states'][ $state ] );
    ?>
    <div class="<?php echo esc_attr( $field_class ); ?>">
        <?php if ( $home ) : ?><span class="fw-field-icon" aria-hidden="true">📍</span><?php endif; ?>
        <label for="<?php echo esc_attr( $prefix ); ?>-state"><?php echo esc_html( findewerkstatt_location_translate( 'Bundesland' ) ); ?></label>
        <select id="<?php echo esc_attr( $prefix ); ?>-state" name="fw_state" data-fw-location-state="<?php echo esc_attr( $prefix ); ?>">
            <option value=""><?php echo esc_html( findewerkstatt_location_translate( 'Ganz Deutschland' ) ); ?></option>
            <?php foreach ( $data['states'] as $slug => $name ) : ?><option value="<?php echo esc_attr( $slug ); ?>"<?php selected( $state, $slug ); ?>><?php echo esc_html( $name ); ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="<?php echo esc_attr( $field_class ); ?>">
        <?php if ( $home ) : ?><span class="fw-field-icon" aria-hidden="true">🏙️</span><?php endif; ?>
        <label for="<?php echo esc_attr( $prefix ); ?>-city"><?php echo esc_html( findewerkstatt_location_translate( 'Stadt oder Ort' ) ); ?></label>
        <select id="<?php echo esc_attr( $prefix ); ?>-city" name="fw_city"<?php disabled( ! $enabled ); ?>>
            <option value=""><?php echo esc_html( findewerkstatt_location_translate( $enabled ? 'Alle Städte und Orte' : 'Erst Bundesland wählen' ) ); ?></option>
            <?php foreach ( $enabled ? $choices[ $state ] : array() as $choice ) : ?><option value="<?php echo esc_attr( $choice['value'] ); ?>"<?php selected( $city, $choice['value'] ); ?>><?php echo esc_html( $choice['label'] ); ?></option><?php endforeach; ?>
        </select>
    </div>
    <span id="<?php echo esc_attr( $prefix ); ?>-location-status" class="screen-reader-text" aria-live="polite"></span>
    <script id="<?php echo esc_attr( $prefix ); ?>-locations" type="application/json"><?php echo wp_json_encode( $choices, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?></script>
    <noscript><p class="fw-location-hint"><?php echo esc_html( findewerkstatt_location_translate( 'Wählen Sie ein Bundesland und starten Sie die Suche, um die Städteauswahl zu laden.' ) ); ?></p></noscript>
    <?php
}
