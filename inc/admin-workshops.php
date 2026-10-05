<?php
/**
 * Admin Columns, Filters, and Profile Completion for Workshops (CPT: mechanic).
 *
 * @package FindeWerkstatt
 * @version 3.4.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Calculate Profile Completion Percentage (Section 31).
 *
 * Phone (+15%)
 * WhatsApp (+10%)
 * Full Address (+15%)
 * Photo (+15%)
 * Description (+15%)
 * Working Hours (+15%)
 * Category (+15%)
 * Total: 100%
 */
function findewerkstatt_calculate_profile_completion( $post_id ) {
    $score = 0;

    // 1. Phone (+15%)
    $phone = get_post_meta( $post_id, '_mechanic_phone', true );
    if ( ! empty( $phone ) ) {
        $score += 15;
    }

    // 2. WhatsApp (+10%)
    $wa = get_post_meta( $post_id, '_mechanic_whatsapp', true );
    if ( ! empty( $wa ) ) {
        $score += 10;
    }

    // 3. Address & PLZ (+15%)
    $address = get_post_meta( $post_id, '_mechanic_address', true );
    $plz     = get_post_meta( $post_id, '_mechanic_plz', true );
    if ( ! empty( $address ) && ! empty( $plz ) ) {
        $score += 15;
    } elseif ( ! empty( $address ) || ! empty( $plz ) ) {
        $score += 8;
    }

    // 4. Photo (+15%)
    $has_thumb = has_post_thumbnail( $post_id );
    $google_photo = get_post_meta( $post_id, '_mechanic_google_photo_url', true );
    if ( $has_thumb || ! empty( $google_photo ) ) {
        $score += 15;
    }

    // 5. Description (+15%)
    $content = get_post_field( 'post_content', $post_id );
    if ( ! empty( trim( wp_strip_all_tags( $content ) ) ) ) {
        $score += 15;
    }

    // 6. Working hours (+15%)
    $hours = get_post_meta( $post_id, '_mechanic_hours_weekday', true );
    if ( ! empty( $hours ) ) {
        $score += 15;
    }

    // 7. Category (+15%)
    $terms = wp_get_post_terms( $post_id, 'service_type', array( 'fields' => 'ids' ) );
    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
        $score += 15;
    }

    return min( 100, $score );
}

/**
 * Register Admin Columns for 'mechanic' Post Type (Section 30).
 */
add_filter( 'manage_mechanic_posts_columns', function( $columns ) {
    $new_cols = array();
    $new_cols['cb']              = $columns['cb'];
    $new_cols['title']           = __( 'Firma', 'findewerkstatt' );
    $new_cols['fw_city']         = __( 'Stadt', 'findewerkstatt' );
    $new_cols['fw_state']        = __( 'Bundesland', 'findewerkstatt' );
    $new_cols['fw_category']     = __( 'Kategorie', 'findewerkstatt' );
    $new_cols['fw_verification'] = __( 'Prüfstatus', 'findewerkstatt' );
    $new_cols['fw_package']      = __( 'Paket', 'findewerkstatt' );
    $new_cols['fw_phone']        = __( 'Telefon', 'findewerkstatt' );
    $new_cols['fw_whatsapp']     = __( 'WhatsApp', 'findewerkstatt' );
    $new_cols['fw_completion']   = __( 'Profil', 'findewerkstatt' );
    $new_cols['date']            = $columns['date'];
    return $new_cols;
} );

/**
 * Render Admin Column Values for 'mechanic'.
 */
add_action( 'manage_mechanic_posts_custom_column', function( $column, $post_id ) {
    switch ( $column ) {
        case 'fw_city':
            $city_term = findewerkstatt_get_city_term( $post_id );
            echo $city_term ? esc_html( $city_term->name ) : '<span style="color:#94a3b8;">—</span>';
            break;

        case 'fw_state':
            $city_term = findewerkstatt_get_city_term( $post_id );
            if ( $city_term ) {
                $ctx = findewerkstatt_location_context( $city_term );
                echo ! empty( $ctx['state_name'] ) ? esc_html( $ctx['state_name'] ) : '<span style="color:#94a3b8;">—</span>';
            } else {
                echo '<span style="color:#94a3b8;">—</span>';
            }
            break;

        case 'fw_category':
            $terms = wp_get_post_terms( $post_id, 'service_type' );
            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                $names = wp_list_pluck( array_slice( $terms, 0, 2 ), 'name' );
                echo esc_html( implode( ', ', $names ) );
                if ( count( $terms ) > 2 ) {
                    echo ' <small style="color:#64748b;">(+' . ( count( $terms ) - 2 ) . ')</small>';
                }
            } else {
                echo '<span style="color:#94a3b8;">—</span>';
            }
            break;

        case 'fw_verification':
            $level = findewerkstatt_get_verification_level( $post_id );
            if ( 3 === $level['level'] ) {
                echo '<span style="display:inline-block; padding:3px 8px; border-radius:999px; background:#dcfce7; color:#15803d; font-size:11px; font-weight:700;">🛡️ ' . esc_html( $level['label'] ) . '</span>';
            } elseif ( 2 === $level['level'] ) {
                echo '<span style="display:inline-block; padding:3px 8px; border-radius:999px; background:#e0f2fe; color:#0369a1; font-size:11px; font-weight:700;">✓ ' . esc_html( $level['label'] ) . '</span>';
            } else {
                echo '<span style="display:inline-block; padding:3px 8px; border-radius:999px; background:#f1f5f9; color:#475569; font-size:11px; font-weight:600;">' . esc_html( $level['label'] ) . '</span>';
            }
            break;

        case 'fw_package':
            $owner_id = get_post_meta( $post_id, '_fw_owner_user_id', true );
            $plan = $owner_id ? findewerkstatt_member_plan( (int) $owner_id ) : ( get_post_meta( $post_id, '_fw_workshop_plan', true ) ?: 'free' );
            $badge_bg = 'professional' === $plan ? '#fef3c7; color:#b45309;' : ( 'plus' === $plan ? '#e0e7ff; color:#4338ca;' : '#f1f5f9; color:#475569;' );
            echo '<span style="display:inline-block; padding:2px 7px; border-radius:4px; font-size:11px; font-weight:700; background:' . $badge_bg . '">' . esc_html( ucfirst( $plan ) ) . '</span>';
            break;

        case 'fw_phone':
            $phone = get_post_meta( $post_id, '_mechanic_phone', true );
            if ( $phone ) {
                echo '<a href="tel:' . esc_attr( $phone ) . '" style="font-size:12px;">' . esc_html( $phone ) . '</a>';
            } else {
                echo '<span style="color:#94a3b8;">—</span>';
            }
            break;

        case 'fw_whatsapp':
            $wa = get_post_meta( $post_id, '_mechanic_whatsapp', true );
            if ( $wa ) {
                echo '<span style="color:#16a34a; font-weight:700; font-size:12px;">✓ Aktiv</span>';
            } else {
                echo '<span style="color:#94a3b8;">—</span>';
            }
            break;

        case 'fw_completion':
            $pct = findewerkstatt_calculate_profile_completion( $post_id );
            $color = $pct >= 80 ? '#16a34a' : ( $pct >= 50 ? '#f59e0b' : '#dc2626' );
            echo '<div style="display:flex; align-items:center; gap:6px;">';
            echo '<div style="width:40px; height:6px; background:#e2e8f0; border-radius:3px; overflow:hidden;">';
            echo '<div style="width:' . $pct . '%; height:100%; background:' . $color . ';"></div>';
            echo '</div>';
            echo '<span style="font-size:11px; font-weight:700; color:' . $color . ';">' . $pct . '%</span>';
            echo '</div>';
            break;
    }
}, 10, 2 );

/**
 * Add Quick Filter Dropdowns on 'mechanic' List Table (Section 30).
 */
add_action( 'restrict_manage_posts', function( $post_type ) {
    if ( 'mechanic' !== $post_type ) { return; }

    // 1. Filter by Bundesland
    $selected_state = isset( $_GET['fw_filter_state'] ) ? sanitize_text_field( $_GET['fw_filter_state'] ) : '';
    $states = FindeWerkstatt_German_Data::get_bundeslaender();
    echo '<select name="fw_filter_state">';
    echo '<option value="">' . esc_html__( 'Alle Bundesländer', 'findewerkstatt' ) . '</option>';
    foreach ( $states as $st ) {
        echo '<option value="' . esc_attr( $st['slug'] ) . '"' . selected( $selected_state, $st['slug'], false ) . '>' . esc_html( $st['name'] ) . '</option>';
    }
    echo '</select>';

    // 2. Filter by Verification Level
    $selected_verif = isset( $_GET['fw_filter_verif'] ) ? sanitize_text_field( $_GET['fw_filter_verif'] ) : '';
    echo '<select name="fw_filter_verif">';
    echo '<option value="">' . esc_html__( 'Alle Prüfstufen', 'findewerkstatt' ) . '</option>';
    echo '<option value="3"' . selected( $selected_verif, '3', false ) . '>🛡️ Geprüfter Partner</option>';
    echo '<option value="2"' . selected( $selected_verif, '2', false ) . '>✓ Daten geprüft</option>';
    echo '<option value="1"' . selected( $selected_verif, '1', false ) . '>Verzeichniseintrag</option>';
    echo '</select>';

    // 3. Filter by Package
    $selected_plan = isset( $_GET['fw_filter_plan'] ) ? sanitize_text_field( $_GET['fw_filter_plan'] ) : '';
    echo '<select name="fw_filter_plan">';
    echo '<option value="">' . esc_html__( 'Alle Pakete', 'findewerkstatt' ) . '</option>';
    echo '<option value="free"' . selected( $selected_plan, 'free', false ) . '>Free</option>';
    echo '<option value="plus"' . selected( $selected_plan, 'plus', false ) . '>Plus</option>';
    echo '<option value="professional"' . selected( $selected_plan, 'professional', false ) . '>Professional</option>';
    echo '</select>';
} );

/**
 * Handle Admin Filters Query
 */
add_action( 'pre_get_posts', function( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() || 'mechanic' !== $query->get( 'post_type' ) ) {
        return;
    }

    // 1. State Filter
    if ( ! empty( $_GET['fw_filter_state'] ) ) {
        $state_slug = sanitize_text_field( $_GET['fw_filter_state'] );
        $state_term = get_term_by( 'slug', $state_slug, 'mechanic_city' );
        if ( $state_term ) {
            $child_ids = get_term_children( $state_term->term_id, 'mechanic_city' );
            $all_ids = array_merge( array( $state_term->term_id ), is_array( $child_ids ) ? $child_ids : array() );
            $tax_query = $query->get( 'tax_query' ) ?: array();
            $tax_query[] = array(
                'taxonomy' => 'mechanic_city',
                'field'    => 'term_id',
                'terms'    => $all_ids,
                'operator' => 'IN',
            );
            $query->set( 'tax_query', $tax_query );
        }
    }

    // 2. Verification Level Filter
    if ( ! empty( $_GET['fw_filter_verif'] ) ) {
        $verif = (int) $_GET['fw_filter_verif'];
        $meta_query = $query->get( 'meta_query' ) ?: array();
        if ( 3 === $verif ) {
            $meta_query[] = array(
                'key'     => '_fw_owner_user_id',
                'value'   => 0,
                'compare' => '>',
                'type'    => 'NUMERIC',
            );
            $meta_query[] = array(
                'key'     => '_mechanic_is_verified',
                'value'   => 'yes',
                'compare' => '=',
            );
        } elseif ( 2 === $verif ) {
            $meta_query[] = array(
                'key'     => '_mechanic_phone',
                'value'   => '',
                'compare' => '!=',
            );
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key'     => '_fw_owner_user_id',
                    'compare' => 'NOT EXISTS',
                ),
                array(
                    'key'     => '_fw_owner_user_id',
                    'value'   => 0,
                    'compare' => '<=',
                    'type'    => 'NUMERIC',
                ),
            );
        } elseif ( 1 === $verif ) {
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key'     => '_mechanic_phone',
                    'compare' => 'NOT EXISTS',
                ),
                array(
                    'key'     => '_mechanic_phone',
                    'value'   => '',
                    'compare' => '=',
                ),
            );
        }
        $query->set( 'meta_query', $meta_query );
    }

    // 3. Package Filter
    if ( ! empty( $_GET['fw_filter_plan'] ) ) {
        $plan = sanitize_text_field( $_GET['fw_filter_plan'] );
        // Find users with this plan
        $users = get_users( array(
            'meta_key'   => '_fw_member_plan',
            'meta_value' => $plan,
            'fields'     => 'ID',
        ) );
        $meta_query = $query->get( 'meta_query' ) ?: array();
        if ( ! empty( $users ) ) {
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key'     => '_fw_owner_user_id',
                    'value'   => $users,
                    'compare' => 'IN',
                ),
                array(
                    'key'     => '_fw_workshop_plan',
                    'value'   => $plan,
                    'compare' => '=',
                ),
            );
        } else {
            $meta_query[] = array(
                'key'     => '_fw_workshop_plan',
                'value'   => $plan,
                'compare' => '=',
            );
        }
        $query->set( 'meta_query', $meta_query );
    }
} );
