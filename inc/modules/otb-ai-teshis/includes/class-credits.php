<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class OTB_AI_Credits {

    const META_KEY = 'otb_ai_credits';

    public static function get( $uid ) {
        $c = get_user_meta( $uid, self::META_KEY, true );
        if ( $c === '' || $c === null ) {
            $default = max( 0, (int) OTB_AI_Settings::get( OTB_AI_Settings::DEF_CREDIT, 6 ) );
            update_user_meta( $uid, self::META_KEY, $default );
            return $default;
        }
        return (int) $c;
    }

    public static function deduct( $uid, $amount ) {
        $current = self::get( $uid );
        update_user_meta( $uid, self::META_KEY, max( 0, $current - $amount ) );
    }

    public static function refund( $uid, $amount ) {
        $current = (int) get_user_meta( $uid, self::META_KEY, true );
        update_user_meta( $uid, self::META_KEY, $current + $amount );
    }

    public static function has_enough( $uid, $amount ) {
        return self::get( $uid ) >= $amount;
    }
}
