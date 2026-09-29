<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcode ve blog için DB'den kategori bazlı kod listesi döndürür.
 * Yapı: [ 'P' => ['label'=>'...','codes'=>[...]], ... ]
 */
function ak_get_obd_codes() {
    static $cache = null;
    if ( $cache !== null ) return $cache;

    $cats  = AK_DB::categories();
    $cache = [];
    foreach ( $cats as $cat => $info ) {
        $cache[ $cat ] = [
            'label' => $info['label'],
            'icon'  => $info['icon'],
            'color' => $info['color'],
            'codes' => AK_DB::get_all_by_category( $cat ),
        ];
    }
    return $cache;
}
