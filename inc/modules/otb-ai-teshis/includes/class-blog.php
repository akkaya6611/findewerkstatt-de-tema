<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class OTB_AI_Blog {

    public static function maybe_create( array $json, array $inputs ) {
        if ( OTB_AI_Settings::get( OTB_AI_Settings::BLOG_ON, '0' ) !== '1' ) return false;
        if ( ( $inputs['share_consent'] ?? '' ) !== '1' ) return false;

        $hash     = md5( wp_json_encode( $json ) . ( $inputs['city'] ?? '' ) . ( $inputs['marke_modell'] ?? '' ) );
        $existing = get_posts( [ 'post_type' => 'post', 'meta_key' => '_otb_ai_hash', 'meta_value' => $hash, 'fields' => 'ids', 'posts_per_page' => 1 ] );
        if ( ! empty( $existing ) ) return [ 'post_id' => $existing[0], 'status' => 'existing', 'url' => get_permalink( $existing[0] ) ];

        $anon    = fn( $t ) => preg_replace( [ '/\b(\+?\d[\d\s\-\(\)]{7,}\d)\b/u', '/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/iu' ], [ '[telefon]', '[email]' ], (string) $t );
        $title   = ucfirst( $json['tags'][0] ?? 'Araç' ) . ' Problemi – ' . ( $inputs['city'] ?? '' ) . ' (' . ( $inputs['marke_modell'] ?? '' ) . ')';
        $content = '<p><em>Otomatik anonim ön teşhis örneği.</em></p>';
        $content .= '<h2>Problem</h2><p>' . esc_html( $anon( $inputs['beschreibung'] ?? '' ) ) . '</p>';
        if ( ! empty( $json['kurzer_text'] ) )              $content .= '<h2>Özet</h2><p>' . esc_html( $json['kurzer_text'] ) . '</p>';
        if ( ! empty( $json['wahrscheinliche_ursachen'] ) ) {
            $content .= '<h2>Olası Nedenler</h2><ul>';
            foreach ( $json['wahrscheinliche_ursachen'] as $u ) $content .= '<li>' . esc_html( $u ) . '</li>';
            $content .= '</ul>';
        }
        if ( ! empty( $json['kosten_einschaetzung'] ) ) $content .= '<p><strong>Maliyet:</strong> ' . esc_html( $json['kosten_einschaetzung'] ) . '</p>';
        if ( ! empty( $json['dringlichkeit'] ) )        $content .= '<p><strong>Aciliyet:</strong> ' . esc_html( $json['dringlichkeit'] ) . '</p>';

        $status = in_array( OTB_AI_Settings::get( OTB_AI_Settings::BLOG_STATUS, 'draft' ), [ 'draft', 'publish' ], true )
            ? OTB_AI_Settings::get( OTB_AI_Settings::BLOG_STATUS, 'draft' ) : 'draft';

        $arr = [ 'post_type' => 'post', 'post_status' => $status, 'post_title' => wp_strip_all_tags( $title ), 'post_content' => $content ];
        $cat = (int) OTB_AI_Settings::get( OTB_AI_Settings::BLOG_CAT, 0 );
        if ( $cat ) $arr['post_category'] = [ $cat ];

        $pid = wp_insert_post( $arr, true );
        if ( is_wp_error( $pid ) ) return false;

        update_post_meta( $pid, '_otb_ai_hash', $hash );
        if ( ! empty( $json['tags'] ) ) wp_set_post_tags( $pid, array_map( 'sanitize_text_field', $json['tags'] ) );

        return [ 'post_id' => (int) $pid, 'status' => $status, 'url' => get_permalink( $pid ) ];
    }
}
