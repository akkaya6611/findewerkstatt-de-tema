<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="ak-wrap" id="ak-wrap">

    <!-- Header -->
    <div class="ak-header">
        <div class="ak-header-left">
            <div class="ak-icon-ring">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div>
                <h2 class="ak-title">OBD Arıza Kodları</h2>
                <p class="ak-subtitle">Koda tıkla, AI detaylı analiz yapsın</p>
            </div>
        </div>
        <div class="ak-search-wrap">
            <svg class="ak-search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" id="ak-search" placeholder="Kod veya açıklama ara... (P0300)">
        </div>
    </div>

    <!-- Kategori Sekmeleri -->
    <div class="ak-tabs" id="ak-tabs">
        <?php $first = true; foreach ( $codes as $cat => $data ) : ?>
        <button class="ak-tab <?php echo $first ? 'ak-tab-active' : ''; ?>" data-cat="<?php echo esc_attr( $cat ); ?>">
            <span class="ak-tab-letter"><?php echo esc_html( $cat ); ?></span>
            <span class="ak-tab-label"><?php echo esc_html( explode( ' ', $data['label'] )[0] ); ?></span>
            <span class="ak-tab-count"><?php echo number_format( count( $data['codes'] ) ); ?></span>
        </button>
        <?php $first = false; endforeach; ?>
    </div>

    <!-- Kod Listeleri -->
    <?php foreach ( $codes as $cat => $data ) : 
        $cat_codes = $data['codes'];
        $initial_codes = array_slice( $cat_codes, 0, 30, true );
        $remaining = count( $cat_codes ) - 30;
    ?>
    <div class="ak-panel" id="ak-panel-<?php echo esc_attr( $cat ); ?>" <?php echo $cat !== array_key_first( $codes ) ? 'style="display:none"' : ''; ?>>
        <div class="ak-panel-header">
            <span class="ak-panel-title"><?php echo esc_html( $data['label'] ); ?></span>
            <span class="ak-panel-count" id="ak-count-<?php echo esc_attr( $cat ); ?>"><?php echo count( $data['codes'] ); ?> kod</span>
        </div>
        <div class="ak-grid" id="ak-grid-<?php echo esc_attr( $cat ); ?>">
            <?php foreach ( $initial_codes as $code => $name ) : ?>
            <button class="ak-code-btn" data-code="<?php echo esc_attr( $code ); ?>" data-name="<?php echo esc_attr( $name ); ?>">
                <span class="ak-code-badge"><?php echo esc_html( $code ); ?></span>
                <span class="ak-code-name"><?php echo esc_html( $name ); ?></span>
                <svg class="ak-code-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </button>
            <?php endforeach; ?>
        </div>
        <?php if ( $remaining > 0 ) : ?>
        <div style="text-align:center; margin-top:16px;">
            <button class="ak-load-more-btn" data-cat="<?php echo esc_attr( $cat ); ?>" data-offset="30">
                Tüm <?php echo count( $data['codes'] ); ?> Kodu Listele (+<?php echo $remaining; ?>)
            </button>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <!-- Modal -->
    <div id="ak-modal-overlay" class="ak-modal-overlay" style="display:none">
        <div class="ak-modal" id="ak-modal">
            <button class="ak-modal-close" id="ak-modal-close" aria-label="Kapat">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
            <div id="ak-modal-body">
                <div class="ak-modal-loading">
                    <div class="ak-spinner"></div>
                    <p>AI analiz ediyor...</p>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
/* ── Base ── */
.ak-wrap{max-width:1000px;margin:0 auto;padding:12px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}

/* ── Header ── */
.ak-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;background:#0f172a;border-radius:20px;padding:22px 26px;margin-bottom:16px}
.ak-header-left{display:flex;align-items:center;gap:14px}
.ak-icon-ring{width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#f59e0b,#ef4444);display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0;box-shadow:0 8px 20px rgba(245,158,11,.35)}
.ak-title{margin:0;font-size:18px;font-weight:700;color:#f8fafc;letter-spacing:-.3px}
.ak-subtitle{margin:3px 0 0;font-size:12px;color:#64748b}

/* ── Search ── */
.ak-search-wrap{position:relative;flex-shrink:0}
.ak-search-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#475569;pointer-events:none}
.ak-search-wrap input{background:#1e293b;border:1.5px solid #334155;border-radius:999px;padding:9px 14px 9px 36px;font-size:13px;color:#f1f5f9;outline:none;width:220px;transition:border-color .2s,box-shadow .2s}
.ak-search-wrap input::placeholder{color:#475569}
.ak-search-wrap input:focus{border-color:#f59e0b;box-shadow:0 0 0 3px rgba(245,158,11,.15)}

/* ── Tabs ── */
.ak-tabs{display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap}
.ak-tab{display:flex;align-items:center;gap:8px;padding:9px 16px;border-radius:12px;border:1.5px solid #1e293b;background:#0f172a;color:#64748b;cursor:pointer;font-size:13px;font-weight:600;transition:all .2s}
.ak-tab:hover{border-color:#334155;color:#94a3b8}
.ak-tab-active{background:#1e293b;border-color:#f59e0b;color:#f8fafc}
.ak-tab-letter{width:24px;height:24px;border-radius:7px;background:#1e293b;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#f59e0b}
.ak-tab-active .ak-tab-letter{background:#f59e0b;color:#0f172a}
.ak-tab-count{background:#1e293b;color:#475569;font-size:11px;padding:2px 7px;border-radius:999px}
.ak-tab-active .ak-tab-count{background:#f59e0b22;color:#f59e0b}

/* ── Panel ── */
.ak-panel-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
.ak-panel-title{font-size:13px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px}
.ak-panel-count{font-size:12px;color:#475569}

/* ── Grid ── */
.ak-grid{display:grid;grid-template-columns:1fr;gap:6px}
@media(min-width:600px){.ak-grid{grid-template-columns:1fr 1fr}}
@media(min-width:900px){.ak-grid{grid-template-columns:1fr 1fr 1fr}}

/* ── Code Button ── */
.ak-code-btn{display:flex;align-items:center;gap:10px;padding:11px 14px;background:#0f172a;border:1.5px solid #1e293b;border-radius:12px;cursor:pointer;text-align:left;transition:all .18s;width:100%}
.ak-code-btn:hover{border-color:#f59e0b33;background:#1e293b;transform:translateY(-1px)}
.ak-code-btn.ak-cached{border-color:#166534;background:#052e16}
.ak-code-badge{font-size:11px;font-weight:800;color:#f59e0b;background:#f59e0b15;padding:3px 8px;border-radius:6px;white-space:nowrap;flex-shrink:0;letter-spacing:.5px}
.ak-code-btn.ak-cached .ak-code-badge{color:#4ade80;background:#4ade8015}
.ak-code-name{font-size:12px;color:#94a3b8;flex:1;line-height:1.4}
.ak-code-arrow{color:#334155;flex-shrink:0;transition:transform .2s}
.ak-code-btn:hover .ak-code-arrow{color:#f59e0b;transform:translateX(2px)}

/* Load More Button */
.ak-load-more-btn{background:#1e293b; border:1px solid #334155; color:#f8fafc; font-size:13px; font-weight:700; padding:10px 24px; border-radius:999px; cursor:pointer; transition:all 0.2s;}
.ak-load-more-btn:hover{background:#f59e0b; color:#0f172a; border-color:#f59e0b;}

/* ── No results ── */
.ak-no-results{text-align:center;padding:40px 20px;color:#475569;font-size:14px;grid-column:1/-1}

/* ── Modal Overlay ── */
.ak-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.7);backdrop-filter:blur(4px);z-index:99999;display:flex;align-items:center;justify-content:center;padding:16px;animation:akFadeIn .2s ease}
@keyframes akFadeIn{from{opacity:0}to{opacity:1}}

/* ── Modal ── */
.ak-modal{background:#0f172a;border-radius:24px;border:1px solid #1e293b;padding:28px;max-width:640px;width:100%;max-height:85vh;overflow-y:auto;position:relative;animation:akSlideUp .25s cubic-bezier(.22,1,.36,1)}
@keyframes akSlideUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
.ak-modal-close{position:absolute;top:16px;right:16px;background:#1e293b;border:none;border-radius:8px;width:32px;height:32px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#64748b;transition:all .2s}
.ak-modal-close:hover{background:#334155;color:#f1f5f9}

/* ── Modal Loading ── */
.ak-modal-loading{text-align:center;padding:40px 20px;color:#64748b}
.ak-spinner{width:36px;height:36px;border:3px solid #1e293b;border-top-color:#f59e0b;border-radius:50%;animation:akSpin .8s linear infinite;margin:0 auto 14px}
@keyframes akSpin{to{transform:rotate(360deg)}}

/* ── Modal Content ── */
.ak-modal-code{display:inline-flex;align-items:center;gap:8px;margin-bottom:16px}
.ak-modal-code-badge{font-size:13px;font-weight:800;color:#f59e0b;background:#f59e0b15;padding:4px 12px;border-radius:8px;letter-spacing:.5px}
.ak-modal-code-name{font-size:13px;color:#64748b}
.ak-modal-title{font-size:20px;font-weight:700;color:#f8fafc;margin:0 0 10px;letter-spacing:-.3px}
.ak-modal-desc{font-size:14px;color:#94a3b8;line-height:1.7;margin-bottom:18px}
.ak-modal-section{margin-bottom:16px}
.ak-modal-section-title{font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.8px;margin-bottom:8px}
.ak-modal-list{margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:5px}
.ak-modal-list li{font-size:13px;color:#cbd5e1;padding:7px 12px;background:#1e293b;border-radius:8px;border-left:3px solid #334155;line-height:1.5}
.ak-modal-list li::before{content:'→ ';color:#f59e0b;font-weight:700}
.ak-modal-badges{display:flex;gap:8px;flex-wrap:wrap;margin-top:16px;padding-top:16px;border-top:1px solid #1e293b}
.ak-badge{padding:5px 13px;border-radius:999px;font-size:12px;font-weight:600}
.ak-badge-low{background:#052e16;color:#4ade80}
.ak-badge-mid{background:#422006;color:#fbbf24}
.ak-badge-high{background:#450a0a;color:#f87171}
.ak-badge-cost{background:#1e293b;color:#94a3b8}
.ak-modal-tech{font-size:12px;color:#475569;line-height:1.6;margin-top:14px;padding:12px;background:#1e293b;border-radius:10px;border-left:3px solid #334155}
.ak-modal-error{text-align:center;padding:30px;color:#f87171;font-size:14px}
</style>

<script>
(function(){
    var ajax       = <?php echo wp_json_encode( esc_url( $ajax ) ); ?>;
    var nonce      = <?php echo wp_json_encode( $nonce ); ?>;
    var allData    = <?php echo wp_json_encode( $codes ); ?>;
    var overlay    = document.getElementById('ak-modal-overlay');
    var modal      = document.getElementById('ak-modal');
    var body       = document.getElementById('ak-modal-body');
    var cache      = {};
    var activeCat  = '<?php echo esc_js( array_key_first( $codes ) ); ?>';

    function buildButton(code, name) {
        var btn = document.createElement('button');
        btn.className = 'ak-code-btn' + (cache[code] ? ' ak-cached' : '');
        btn.dataset.code = code;
        btn.dataset.name = name;
        btn.innerHTML = '<span class="ak-code-badge">' + code + '</span>'
            + '<span class="ak-code-name">' + name + '</span>'
            + '<svg class="ak-code-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>';
        return btn;
    }

    // Sekme değiştirme
    document.getElementById('ak-tabs').addEventListener('click', function(e) {
        var btn = e.target.closest('.ak-tab');
        if (!btn) return;
        activeCat = btn.dataset.cat;
        document.querySelectorAll('.ak-tab').forEach(function(t){ t.classList.remove('ak-tab-active'); });
        btn.classList.add('ak-tab-active');
        document.querySelectorAll('.ak-panel').forEach(function(p){ p.style.display = 'none'; });
        var activePanel = document.getElementById('ak-panel-' + activeCat);
        if (activePanel) activePanel.style.display = '';
        document.getElementById('ak-search').value = '';
        resetSearch();
    });

    // Daha fazla yükle butonu
    document.querySelectorAll('.ak-load-more-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var cat = this.dataset.cat;
            var grid = document.getElementById('ak-grid-' + cat);
            if (!grid || !allData[cat]) return;
            var codesObj = allData[cat].codes;
            grid.innerHTML = '';
            for (var c in codesObj) {
                grid.appendChild(buildButton(c, codesObj[c]));
            }
            this.parentElement.remove();
        });
    });

    // Arama
    document.getElementById('ak-search').addEventListener('input', function() {
        var q = this.value.trim().toLowerCase();
        if (!q) { resetSearch(); return; }

        document.querySelectorAll('.ak-tab').forEach(function(t){ t.classList.remove('ak-tab-active'); });
        document.querySelectorAll('.ak-panel').forEach(function(p){ p.style.display = ''; });

        var totalFound = 0;
        for (var cat in allData) {
            var grid = document.getElementById('ak-grid-' + cat);
            var panel = document.getElementById('ak-panel-' + cat);
            var loadBtnWrap = panel ? panel.querySelector('.ak-load-more-btn')?.parentElement : null;
            if (loadBtnWrap) loadBtnWrap.style.display = 'none';

            if (!grid) continue;
            grid.innerHTML = '';
            var count = 0;
            var codesObj = allData[cat].codes;

            for (var c in codesObj) {
                var codeLower = c.toLowerCase();
                var nameLower = (codesObj[c] || '').toLowerCase();
                if (codeLower.indexOf(q) !== -1 || nameLower.indexOf(q) !== -1) {
                    if (count < 60) {
                        grid.appendChild(buildButton(c, codesObj[c]));
                    }
                    count++;
                }
            }

            totalFound += count;
            var countEl = panel.querySelector('.ak-panel-count');
            if (countEl) countEl.textContent = count + ' eşleşme';

            var existing = panel.querySelector('.ak-no-results');
            if (count === 0) {
                if (!existing) {
                    var el = document.createElement('div');
                    el.className = 'ak-no-results';
                    el.textContent = 'Bu kategoride eşleşme yok.';
                    grid.appendChild(el);
                }
            } else if (existing) {
                existing.remove();
            }
        }
    });

    function resetSearch() {
        for (var cat in allData) {
            var panel = document.getElementById('ak-panel-' + cat);
            if (!panel) continue;
            var countEl = panel.querySelector('.ak-panel-count');
            var total = Object.keys(allData[cat].codes).length;
            if (countEl) countEl.textContent = total + ' kod';
            var loadBtnWrap = panel.querySelector('.ak-load-more-btn')?.parentElement;
            if (loadBtnWrap) loadBtnWrap.style.display = '';
        }
        document.querySelectorAll('.ak-panel').forEach(function(p){ 
            p.style.display = (p.id === 'ak-panel-' + activeCat) ? '' : 'none'; 
        });
        var activeTabBtn = document.querySelector('.ak-tab[data-cat="' + activeCat + '"]');
        if (activeTabBtn) activeTabBtn.classList.add('ak-tab-active');
    }

    // Kod tıklama
    document.getElementById('ak-wrap').addEventListener('click', function(e) {
        var btn = e.target.closest('.ak-code-btn');
        if (!btn) return;
        var code = btn.dataset.code;
        var name = btn.dataset.name;
        openModal(code, name);
    });

    // Modal kapat
    document.getElementById('ak-modal-close').addEventListener('click', closeModal);
    overlay.addEventListener('click', function(e){ if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeModal(); });

    function openModal(code, name) {
        body.innerHTML = '<div class="ak-modal-loading"><div class="ak-spinner"></div><p>AI analiz ediyor...</p></div>';
        overlay.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        if (cache[code]) { renderResult(cache[code], code, name); return; }

        var fd = new FormData();
        fd.append('action', 'ak_analyse');
        fd.append('_nonce', nonce);
        fd.append('code', code);
        fd.append('name', name);

        fetch(ajax, { method: 'POST', body: fd })
            .then(function(r){ return r.json(); })
            .then(function(json) {
                if (!json.success) {
                    body.innerHTML = '<div class="ak-modal-error">❌ ' + (json.data?.message || 'Hata oluştu.') + '</div>';
                    return;
                }
                cache[code] = json.data;
                var btn = document.querySelector('[data-code="' + code + '"]');
                if (btn) btn.classList.add('ak-cached');
                renderResult(json.data, code, name);
            })
            .catch(function() {
                body.innerHTML = '<div class="ak-modal-error">❌ İstek başarısız oldu.</div>';
            });
    }

    function renderResult(d, code, name) {
        var aciliyet = (d.aciliyet || '').toLowerCase();
        var aciliyetCls = aciliyet === 'düşük' ? 'low' : aciliyet === 'yüksek' ? 'high' : 'mid';
        var aciliyetLabel = aciliyet === 'düşük' ? 'Düşük Aciliyet' : aciliyet === 'yüksek' ? 'Yüksek Aciliyet' : 'Orta Aciliyet';

        function ul(arr) {
            if (!arr || !arr.length) return '';
            return '<ul class="ak-modal-list">' + arr.map(function(x){ return '<li>' + x + '</li>'; }).join('') + '</ul>';
        }

        body.innerHTML =
            '<div class="ak-modal-code">'
            + '<span class="ak-modal-code-badge">' + code + '</span>'
            + '<span class="ak-modal-code-name">' + name + '</span>'
            + '</div>'
            + '<h3 class="ak-modal-title">' + (d.isim || name) + '</h3>'
            + '<p class="ak-modal-desc">' + (d.aciklama || '') + '</p>'
            + (d.olasi_nedenler?.length ? '<div class="ak-modal-section"><div class="ak-modal-section-title">Olası Nedenler</div>' + ul(d.olasi_nedenler) + '</div>' : '')
            + (d.belirtiler?.length ? '<div class="ak-modal-section"><div class="ak-modal-section-title">Belirtiler</div>' + ul(d.belirtiler) + '</div>' : '')
            + (d.cozum_onerileri?.length ? '<div class="ak-modal-section"><div class="ak-modal-section-title">Çözüm Önerileri</div>' + ul(d.cozum_onerileri) + '</div>' : '')
            + (d.sik_gorulen_araclar?.length ? '<div class="ak-modal-section"><div class="ak-modal-section-title">Sık Görülen Araçlar</div>' + ul(d.sik_gorulen_araclar) + '</div>' : '')
            + '<div class="ak-modal-badges">'
            + '<span class="ak-badge ak-badge-' + aciliyetCls + '">' + aciliyetLabel + '</span>'
            + (d.tahmini_maliyet ? '<span class="ak-badge ak-badge-cost">💰 ' + d.tahmini_maliyet + '</span>' : '')
            + '</div>'
            + (d.teknik_detay ? '<div class="ak-modal-tech">🔧 ' + d.teknik_detay + '</div>' : '');
    }

    function closeModal() {
        overlay.style.display = 'none';
        document.body.style.overflow = '';
    }
})();
</script>
