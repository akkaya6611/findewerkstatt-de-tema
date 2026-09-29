<?php
/**
 * Categories Grid Template Part
 * Uses custom automotive vector icons
 */
$grid_categories = array(
    array( 'slug' => 'mekanik-ustasi', 'title' => 'Mekanik Ustaları' ),
    array( 'slug' => 'oto-elektrik-ustalari', 'title' => 'Oto Elektrik Ustaları' ),
    array( 'slug' => 'kaporta-ustalari', 'title' => 'Kaporta & Boya' ),
    array( 'slug' => 'motor-ustalari', 'title' => 'Motor Ustaları' ),
    array( 'slug' => 'oto-cam-ustalari', 'title' => 'Oto Cam Ustaları' ),
    array( 'slug' => 'oto-doseme-ustalari', 'title' => 'Oto Döşeme Ustaları' ),
    array( 'slug' => 'oto-klima-ustalari', 'title' => 'Oto Klima Ustaları' ),
    array( 'slug' => 'oto-ekspertiz', 'title' => 'Oto Ekspertiz' ),
    array( 'slug' => 'lpg-montaj-ustalari', 'title' => 'LPG Montaj Ustaları' ),
    array( 'slug' => 'oto-enjeksiyon-ustalari', 'title' => 'Oto Enjeksiyon' ),
    array( 'slug' => 'egzoz-ve-emisyon-sistemleri-ustasi', 'title' => 'Egzoz & Emisyon' ),
    array( 'slug' => 'fren-ve-balata-ustasi', 'title' => 'Fren & Balata' ),
);
?>
<div class="categories-grid">
    <?php foreach ( $grid_categories as $item ) : 
        $term = get_term_by( 'slug', $item['slug'], 'service_type' );
        $count = ( $term && ! is_wp_error( $term ) ) ? $term->count : 0;
        $url = home_url( '/ustalar/?service_type=' . $item['slug'] );
        $icon_url = function_exists( 'ototamir_get_service_icon' ) ? ototamir_get_service_icon( $item['slug'] ) : '';
    ?>
    <a href="<?php echo esc_url( $url ); ?>" class="category-small-box">
        <i class="listeo-svg-icon-box-grid">
            <img src="<?php echo esc_url( $icon_url ); ?>" alt="" aria-hidden="true" width="44" height="44" loading="lazy" decoding="async" style="filter: brightness(0); object-fit: contain;">
        </i>
        <h4><?php echo esc_html( $item['title'] ); ?></h4>
        <?php if ( $count > 0 ) : ?>
            <span class="category-box-counter"><?php echo esc_html( $count ); ?></span>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>

