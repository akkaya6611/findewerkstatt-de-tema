<?php
/** The operator's supplied brand artwork, shared by the site and WordPress icons. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function findewerkstatt_brand_asset_url( $asset ) {
    $files = array(
        'logo' => 'logo.png',
        'icon' => 'icon.jpg',
        'mascot_welcome' => 'mascot-welcome.png',
        'mascot_workshop' => 'mascot-workshop.png',
    );
    if ( ! isset( $files[ $asset ] ) ) { return ''; }
    return add_query_arg( 'ver', wp_get_theme()->get( 'Version' ), get_template_directory_uri() . '/assets/images/brand/' . $files[ $asset ] );
}

function findewerkstatt_banner_url( $asset ) {
    $files = array(
        'autoelektrik'        => 'banner-autoelektrik.png',
        'turkiye-ustalar'     => 'banner-turkiye-ustalar.png',
        'ladestationen'       => 'banner-ladestationen.png',
        'elektro-hybrid'      => 'banner-elektro-hybrid.png',
        'werkstatt-diagnostic'=> 'banner-werkstatt-diagnostic.jpg',
    );
    if ( ! isset( $files[ $asset ] ) ) { return ''; }
    return add_query_arg( 'ver', wp_get_theme()->get( 'Version' ), get_template_directory_uri() . '/assets/images/banners/' . $files[ $asset ] );
}

// Core prints browser, touch and administration icons. An admin-selected icon takes priority.
add_filter( 'get_site_icon_url', function ( $url ) {
    return $url ?: findewerkstatt_brand_asset_url( 'icon' );
} );

/**
 * Service Term Slug -> Passendes Banner Bild
 */
function findewerkstatt_get_service_banner( $slug ) {
    switch ( $slug ) {
        case 'kfz-elektrik-elektronik':
            return array(
                'url'   => findewerkstatt_banner_url( 'autoelektrik' ),
                'alt'   => 'Autoelektrik & Fehlerdiagnose — Finde zuverlässige Kfz-Werkstätten',
                'title' => 'Autoelektrik & Fehlerdiagnose',
            );
        case 'e-auto-ladestationen':
            return array(
                'url'   => findewerkstatt_banner_url( 'elektro-hybrid' ),
                'alt'   => 'Elektrische & Hybridfahrzeuge Werkstätten finden',
                'title' => 'Elektrische & Hybridfahrzeuge',
            );
        default:
            return null;
    }
}

/**
 * Responsive Banner-Slider für die Startseite
 */
function findewerkstatt_render_banner_slider() {
    $lang = function_exists( 'findewerkstatt_language' ) ? findewerkstatt_language() : 'de';
    
    // Sliders nach Sprache zusammenstellen
    if ( 'tr' === $lang ) {
        $slides = array(
            array(
                'url'   => findewerkstatt_banner_url( 'turkiye-ustalar' ),
                'alt'   => 'Aracınız için güvenilir ustaları bulun',
                'link'  => home_url( '/tr/werkstaetten/' ),
            ),
            array(
                'url'   => findewerkstatt_banner_url( 'autoelektrik' ),
                'alt'   => 'Oto Elektrik & Arıza Teşhis',
                'link'  => home_url( '/tr/service/kfz-elektrik-elektronik/' ),
            ),
            array(
                'url'   => findewerkstatt_banner_url( 'elektro-hybrid' ),
                'alt'   => 'Elektrikli & Hibrit Araç Servisleri',
                'link'  => home_url( '/tr/service/e-auto-ladestationen/' ),
            ),
            array(
                'url'   => findewerkstatt_banner_url( 'werkstatt-diagnostic' ),
                'alt'   => 'Profesyonel Oto Servis & Diyagnostik',
                'link'  => home_url( '/tr/werkstaetten/' ),
            ),
        );
    } else {
        $slides = array(
            array(
                'url'   => findewerkstatt_banner_url( 'autoelektrik' ),
                'alt'   => 'Autoelektrik & Fehlerdiagnose Werkstätten finden',
                'link'  => findewerkstatt_term_url( 'kfz-elektrik-elektronik', 'service_type' ),
            ),
            array(
                'url'   => findewerkstatt_banner_url( 'elektro-hybrid' ),
                'alt'   => 'Elektrische & Hybridfahrzeuge Werkstätten finden',
                'link'  => findewerkstatt_term_url( 'e-auto-ladestationen', 'service_type' ),
            ),
            array(
                'url'   => findewerkstatt_banner_url( 'ladestationen' ),
                'alt'   => 'Ladestationen für Elektrofahrzeuge finden',
                'link'  => findewerkstatt_term_url( 'e-auto-ladestationen', 'service_type' ),
            ),
            array(
                'url'   => findewerkstatt_banner_url( 'werkstatt-diagnostic' ),
                'alt'   => 'Geprüfte Kfz-Meisterwerkstätten & Diagnose',
                'link'  => get_post_type_archive_link( 'mechanic' ),
            ),
        );
    }

    $slider_id = 'fw-hero-slider-' . wp_rand( 100, 999 );
    ?>
    <div class="fw-banner-slider-wrap" id="<?php echo esc_attr( $slider_id ); ?>">
        <div class="fw-banner-slider-track">
            <?php foreach ( $slides as $i => $slide ) : ?>
                <div class="fw-banner-slide<?php echo 0 === $i ? ' is-active' : ''; ?>">
                    <a href="<?php echo esc_url( $slide['link'] ); ?>" class="fw-banner-slide-link">
                        <img src="<?php echo esc_url( $slide['url'] ); ?>" 
                             alt="<?php echo esc_attr( $slide['alt'] ); ?>" 
                             width="1024" 
                             height="384" 
                             loading="<?php echo 0 === $i ? 'eager' : 'lazy'; ?>" 
                             class="fw-banner-slide-img">
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="fw-slider-btn fw-slider-prev" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Vorheriger' ) ); ?>">‹</button>
        <button type="button" class="fw-slider-btn fw-slider-next" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Nächster' ) ); ?>">›</button>
        <div class="fw-slider-dots">
            <?php foreach ( $slides as $i => $slide ) : ?>
                <button type="button" class="fw-slider-dot<?php echo 0 === $i ? ' is-active' : ''; ?>" data-slide="<?php echo esc_attr( $i ); ?>" aria-label="Slide <?php echo esc_attr( $i + 1 ); ?>"></button>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
    (function(){
        const wrap = document.getElementById('<?php echo esc_js( $slider_id ); ?>');
        if(!wrap) return;
        const slides = wrap.querySelectorAll('.fw-banner-slide');
        const dots = wrap.querySelectorAll('.fw-slider-dot');
        const prev = wrap.querySelector('.fw-slider-prev');
        const next = wrap.querySelector('.fw-slider-next');
        let current = 0;
        let timer = null;

        function showSlide(index) {
            current = (index + slides.length) % slides.length;
            slides.forEach((s, idx) => s.classList.toggle('is-active', idx === current));
            dots.forEach((d, idx) => d.classList.toggle('is-active', idx === current));
        }

        function nextSlide() { showSlide(current + 1); }
        function prevSlide() { showSlide(current - 1); }

        function startTimer() {
            stopTimer();
            timer = setInterval(nextSlide, 5000);
        }
        function stopTimer() {
            if(timer) clearInterval(timer);
        }

        if(prev) prev.addEventListener('click', () => { prevSlide(); startTimer(); });
        if(next) next.addEventListener('click', () => { nextSlide(); startTimer(); });
        dots.forEach((d, idx) => {
            d.addEventListener('click', () => { showSlide(idx); startTimer(); });
        });

        wrap.addEventListener('mouseenter', stopTimer);
        wrap.addEventListener('mouseleave', startTimer);

        // Touch swipe support
        let startX = 0;
        wrap.addEventListener('touchstart', e => { startX = e.touches[0].clientX; stopTimer(); }, {passive:true});
        wrap.addEventListener('touchend', e => {
            const diff = e.changedTouches[0].clientX - startX;
            if(Math.abs(diff) > 40) {
                if(diff > 0) prevSlide(); else nextSlide();
            }
            startTimer();
        }, {passive:true});

        startTimer();
    })();
    </script>
    <?php
}
