<?php
/* Swiper.js için CSS + JS kaydet */
add_action('wp_head', function() {
    if (is_front_page() || is_home()) {
        echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">';
    }
});
add_action('wp_footer', function() {
    if (is_front_page() || is_home()) {
        echo '<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>';
    }
});
