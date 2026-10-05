<?php
/**
 * FindeWerkstatt.de — Client-seitiger Sofort-Filter (Live Instant Filter)
 *
 * Ermöglicht sekundenschnelles Filtern der angezeigten Werkstatt-Karten
 * ohne Neuladen der Seite:
 * - ⚡ Alle anzeigen
 * - 🟢 Jetzt geöffnet
 * - ⭐ Top bewertet (4.0+)
 * - 💬 Mit WhatsApp
 * - 👑 Meisterbetriebe (Featured)
 *
 * @package FindeWerkstatt
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function findewerkstatt_render_live_filter_bar() {
    $filter_id = 'fw-live-filter-' . wp_rand( 100, 999 );
    ?>
    <div class="fw-live-filter-bar" id="<?php echo esc_attr( $filter_id ); ?>">
        <div class="fw-filter-bar-inner">
            <span class="fw-filter-bar-label">
                <span aria-hidden="true">⚡</span>
                <span><?php echo esc_html( findewerkstatt_t( 'Schnellfilter:' ) ); ?></span>
            </span>
            <div class="fw-filter-chips-list">
                <button type="button" class="fw-filter-chip is-active" data-filter="all">
                    <span><?php echo esc_html( findewerkstatt_t( 'Alle anzeigen' ) ); ?></span>
                </button>
                <button type="button" class="fw-filter-chip" data-filter="open">
                    <span aria-hidden="true">🟢</span>
                    <span><?php echo esc_html( findewerkstatt_t( 'Jetzt geöffnet' ) ); ?></span>
                </button>
                <button type="button" class="fw-filter-chip" data-filter="top-rated">
                    <span aria-hidden="true">⭐</span>
                    <span><?php echo esc_html( findewerkstatt_t( 'Top bewertet (4.0+)' ) ); ?></span>
                </button>
                <button type="button" class="fw-filter-chip" data-filter="whatsapp">
                    <span aria-hidden="true">💬</span>
                    <span><?php echo esc_html( findewerkstatt_t( 'Mit WhatsApp' ) ); ?></span>
                </button>
                <button type="button" class="fw-filter-chip" data-filter="featured">
                    <span aria-hidden="true">👑</span>
                    <span><?php echo esc_html( findewerkstatt_t( 'Meisterbetriebe' ) ); ?></span>
                </button>
            </div>
            <div class="fw-filter-counter" style="display:none;"></div>
        </div>
    </div>
    <script>
    (function(){
        const bar = document.getElementById('<?php echo esc_js( $filter_id ); ?>');
        if(!bar) return;
        const chips = bar.querySelectorAll('.fw-filter-chip');
        const counter = bar.querySelector('.fw-filter-counter');
        const container = bar.closest('main') || document;
        const grid = container.querySelector('.fw-workshops-grid');
        if(!grid) return;
        const cards = grid.querySelectorAll('.fw-workshop-card');
        const total = cards.length;

        chips.forEach(chip => {
            chip.addEventListener('click', function(){
                chips.forEach(c => c.classList.remove('is-active'));
                this.classList.add('is-active');
                const filter = this.getAttribute('data-filter');
                let visibleCount = 0;

                cards.forEach(card => {
                    let show = true;
                    if(filter === 'open') {
                        show = card.getAttribute('data-open') === '1';
                    } else if(filter === 'top-rated') {
                        const r = parseFloat(card.getAttribute('data-rating') || '0');
                        show = r >= 4.0;
                    } else if(filter === 'whatsapp') {
                        show = card.getAttribute('data-whatsapp') === '1';
                    } else if(filter === 'featured') {
                        show = card.getAttribute('data-featured') === '1';
                    }

                    if(show) {
                        card.style.display = '';
                        card.style.opacity = '1';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                        card.style.opacity = '0';
                    }
                });

                if(filter !== 'all') {
                    counter.style.display = 'inline-block';
                    counter.textContent = visibleCount + ' / ' + total;
                } else {
                    counter.style.display = 'none';
                }
            });
        });
    })();
    </script>
    <?php
}
