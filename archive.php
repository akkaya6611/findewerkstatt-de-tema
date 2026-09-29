<?php
/**
 * FindeWerkstatt.de — Universal Archive Fallback
 * 
 * @package FindeWerkstatt
 * @version 2.0.0
 */

if ( is_post_type_archive( 'mechanic' ) ) {
    get_template_part( 'archive-mechanic' );
    return;
}

get_template_part( 'archive-mechanic' );
