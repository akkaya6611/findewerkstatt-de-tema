<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="otb-wrap">

    <div class="otb-card">

        <!-- Header -->
        <div class="otb-header">
            <div class="otb-header-left">
                <div class="otb-icon-ring">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                </div>
                <div>
                    <h2 class="otb-title">AI Araç Teşhisi</h2>
                    <p class="otb-subtitle">Aracının sorununu anlat, yapay zekâ analiz etsin</p>
                </div>
            </div>
            <div class="otb-badges">
                <span class="otb-badge-pill">⚡ <?php echo (int) $cost; ?> kredi / teşhis</span>
                <?php if ( $uid ) : ?>
                <span class="otb-badge-pill otb-badge-green" id="otb-credits-chip">
                    🔋 <span id="otb-credits-val"><?php echo esc_html( $display ); ?></span> kredi
                </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Form -->
        <form id="otb-ai-form" autocomplete="off">
            <input type="hidden" id="otb-nonce" value="<?php echo esc_attr( $nonce ); ?>">

            <!-- Zorunlu alan: Problem -->
            <div class="otb-section">
                <div class="otb-section-label">
                    <span class="otb-num">1</span>
                    <span>Problemi Anlat</span>
                </div>
                <div class="otb-field-full">
                    <textarea id="otb-beschreibung" rows="3" required
                        placeholder="Fren yaparken araç sağa çekiyor ve hafif gıcırtı sesi geliyor..."></textarea>
                </div>
            </div>

            <!-- Araç Bilgileri -->
            <div class="otb-section">
                <div class="otb-section-label">
                    <span class="otb-num">2</span>
                    <span>Araç Bilgileri</span>
                </div>
                <div class="otb-grid-3">
                    <div class="otb-field">
                        <label>Marka / Model / Yıl</label>
                        <input type="text" id="otb-marke" placeholder="VW Golf 7, 2016">
                    </div>
                    <div class="otb-field">
                        <label>Kilometre</label>
                        <input type="text" id="otb-km" placeholder="145.000 km">
                    </div>
                    <div class="otb-field">
                        <label>Şehir <span class="otb-req">*</span></label>
                        <input type="text" id="otb-ort" required placeholder="İstanbul">
                    </div>
                </div>
            </div>

            <!-- Detaylar -->
            <div class="otb-section">
                <div class="otb-section-label">
                    <span class="otb-num">3</span>
                    <span>Detaylar <span class="otb-optional">(isteğe bağlı)</span></span>
                </div>
                <div class="otb-grid-2">
                    <div class="otb-field">
                        <label>Hangi durumlarda oluyor?</label>
                        <textarea id="otb-situ" rows="2" placeholder="Sadece soğuk motorda, 80 km/s üzeri..."></textarea>
                    </div>
                    <div class="otb-field">
                        <label>Ne zamandır var?</label>
                        <input type="text" id="otb-dauer" placeholder="2 gündür, 1 aydır">
                    </div>
                    <div class="otb-field">
                        <label>Sesler</label>
                        <textarea id="otb-ses" rows="2" placeholder="Tıkırtı, ıslık, gıcırtı..."></textarea>
                    </div>
                    <div class="otb-field">
                        <label>Uyarı lambaları</label>
                        <textarea id="otb-warn" rows="2" placeholder="Motor, ABS, yağ..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="otb-form-footer">
                <label class="otb-consent">
                    <input type="checkbox" id="otb-consent">
                    <span>Teşhis sonucunun anonim blog içeriği olarak paylaşılmasına izin veriyorum</span>
                </label>
                <div class="otb-footer-right">
                    <div id="otb-progress" style="display:none">
                        <div class="otb-bar-track"><div class="otb-bar-fill"></div></div>
                        <span id="otb-loading-text">Analiz ediliyor...</span>
                    </div>
                    <button type="submit" id="otb-submit">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        Teşhisi Başlat
                    </button>
                </div>
            </div>

        </form>
    </div><!-- /.otb-card -->

    <!-- Sonuç -->
    <div id="otb-result" style="display:none"></div>

    <!-- Servisler -->
    <div id="otb-workshops"></div>

</div><!-- /.otb-wrap -->

<style>
/* ── Base ── */
.otb-wrap{max-width:900px;margin:0 auto;padding:12px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}

/* ── Card ── */
.otb-card{background:#0f172a;border-radius:24px;padding:32px;box-shadow:0 32px 64px rgba(0,0,0,.25);animation:otbIn .45s cubic-bezier(.22,1,.36,1)}
@keyframes otbIn{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}

/* ── Header ── */
.otb-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:28px}
.otb-header-left{display:flex;align-items:center;gap:14px}
.otb-icon-ring{width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,#e63946,#ff6b6b);display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0;box-shadow:0 8px 20px rgba(230,57,70,.4)}
.otb-title{margin:0;font-size:20px;font-weight:700;color:#f8fafc;letter-spacing:-.3px}
.otb-subtitle{margin:3px 0 0;font-size:13px;color:#64748b}
.otb-badges{display:flex;flex-wrap:wrap;gap:8px}
.otb-badge-pill{padding:5px 13px;border-radius:999px;font-size:12px;font-weight:600;background:#1e293b;color:#94a3b8;border:1px solid #334155}
.otb-badge-green{background:#052e16;color:#4ade80;border-color:#166534}

/* ── Sections ── */
.otb-section{margin-bottom:22px}
.otb-section-label{display:flex;align-items:center;gap:10px;margin-bottom:12px;font-size:13px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px}
.otb-num{width:22px;height:22px;border-radius:50%;background:#e63946;color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.otb-optional{font-weight:400;text-transform:none;letter-spacing:0;color:#475569;font-size:12px}

/* ── Grids ── */
.otb-grid-3{display:grid;grid-template-columns:1fr;gap:12px}
.otb-grid-2{display:grid;grid-template-columns:1fr;gap:12px}
@media(min-width:600px){.otb-grid-3{grid-template-columns:1fr 1fr}.otb-grid-2{grid-template-columns:1fr 1fr}}
@media(min-width:800px){.otb-grid-3{grid-template-columns:1fr 1fr 1fr}}

/* ── Fields ── */
.otb-field{display:flex;flex-direction:column;gap:6px}
.otb-field label{font-size:12px;font-weight:600;color:#64748b;letter-spacing:.3px}
.otb-req{color:#e63946}
.otb-field input,.otb-field textarea,.otb-field-full textarea{
    background:#1e293b;border:1.5px solid #334155;border-radius:10px;
    padding:10px 13px;font-size:14px;color:#f1f5f9;outline:none;
    transition:border-color .2s,box-shadow .2s;resize:vertical;
    width:100%;box-sizing:border-box;font-family:inherit
}
.otb-field input::placeholder,.otb-field textarea::placeholder,.otb-field-full textarea::placeholder{color:#475569}
.otb-field input:focus,.otb-field textarea:focus,.otb-field-full textarea:focus{
    border-color:#e63946;box-shadow:0 0 0 3px rgba(230,57,70,.15);background:#1a2540
}
.otb-field-full textarea{min-height:90px}

/* ── Footer ── */
.otb-form-footer{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;padding-top:20px;border-top:1px solid #1e293b;margin-top:4px}
.otb-consent{display:flex;align-items:flex-start;gap:9px;font-size:12px;color:#475569;cursor:pointer;flex:1;min-width:200px}
.otb-consent input{margin-top:2px;accent-color:#e63946;flex-shrink:0}
.otb-footer-right{display:flex;align-items:center;gap:14px;flex-shrink:0}

/* ── Progress ── */
#otb-progress{display:flex;align-items:center;gap:10px}
.otb-bar-track{width:100px;height:4px;border-radius:999px;background:#1e293b;overflow:hidden}
.otb-bar-fill{width:100%;height:100%;border-radius:inherit;background:linear-gradient(90deg,#e63946,#ff6b6b);animation:otbSlide 1.2s ease-in-out infinite;transform-origin:0 50%}
@keyframes otbSlide{0%{transform:translateX(-100%) scaleX(.3)}50%{transform:translateX(0%) scaleX(.7)}100%{transform:translateX(100%) scaleX(.3)}}
#otb-loading-text{font-size:12px;color:#64748b;white-space:nowrap}

/* ── Submit Button ── */
#otb-submit{display:flex;align-items:center;gap:8px;padding:11px 24px;border:none;border-radius:999px;cursor:pointer;font-weight:700;font-size:14px;background:linear-gradient(135deg,#e63946,#ff6b6b);color:#fff;transition:transform .2s,box-shadow .2s;letter-spacing:.2px;white-space:nowrap}
#otb-submit:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(230,57,70,.45)}
#otb-submit:disabled{opacity:.5;cursor:not-allowed;transform:none;box-shadow:none}

/* ── Result Card ── */
#otb-result{margin-top:20px}
.otb-result-card{background:#0f172a;border-radius:20px;border-left:4px solid #4f46e5;padding:24px;box-shadow:0 20px 40px rgba(0,0,0,.2)}
.otb-result-card h3{margin:0 0 12px;font-size:18px;color:#f8fafc}
.otb-result-card h4{margin:14px 0 6px;font-size:13px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px}
.otb-result-card ul{margin:0 0 10px 18px;padding:0;font-size:14px;color:#cbd5e1}
.otb-result-card ul li{margin-bottom:4px}
.otb-result-card p{font-size:14px;color:#cbd5e1;margin:6px 0}
.otb-rbadge{display:inline-block;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;margin-left:6px}
.otb-rbadge-low{background:#052e16;color:#4ade80}
.otb-rbadge-mid{background:#422006;color:#fbbf24}
.otb-rbadge-high{background:#450a0a;color:#f87171}

/* ── Workshops ── */
#otb-workshops{margin-top:20px}
.otb-ws-card{background:#0f172a;border-radius:20px;border:1px solid #1e293b;padding:22px}
.otb-ws-card h3{margin:0 0 14px;font-size:18px;color:#f8fafc}

/* ── Locked ── */
.otb-locked{text-align:center;padding:48px 20px;color:#94a3b8}
.otb-locked-icon{font-size:44px;display:block;margin-bottom:14px}
.otb-locked h3{font-size:20px;color:#f8fafc;margin:0 0 8px}
.otb-locked p{font-size:14px;margin-bottom:22px}
.otb-auth-btns{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
.otb-btn{padding:10px 22px;border-radius:999px;text-decoration:none;font-weight:600;font-size:14px;transition:.2s}
.otb-btn-primary{background:linear-gradient(135deg,#e63946,#ff6b6b);color:#fff}
.otb-btn-secondary{background:#1e293b;color:#f1f5f9;border:1px solid #334155}
</style>

<script>
(function(){
    var form    = document.getElementById('otb-ai-form');
    var result  = document.getElementById('otb-result');
    var wsEl    = document.getElementById('otb-workshops');
    var prog    = document.getElementById('otb-progress');
    var submit  = document.getElementById('otb-submit');
    var credVal = document.getElementById('otb-credits-val');
    var ajax    = <?php echo wp_json_encode( esc_url( $ajax ) ); ?>;

    function badge(val) {
        var map = {'düşük':'low','orta':'mid','yüksek':'high','low':'low','mittel':'mid','hoch':'high'};
        var cls = map[(val||'').toLowerCase()] || 'mid';
        return '<span class="otb-rbadge otb-rbadge-'+cls+'">'+val+'</span>';
    }
    function ul(arr) {
        return arr?.length ? '<ul>'+arr.map(function(x){return '<li>'+x+'</li>';}).join('')+'</ul>' : '';
    }
    function setLoading(on) {
        prog.style.display = on ? 'flex' : 'none';
        submit.disabled    = on;
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        var beschreibung = document.getElementById('otb-beschreibung').value.trim();
        var ort          = document.getElementById('otb-ort').value.trim();
        if (!beschreibung || !ort) { alert('Problem açıklaması ve şehir zorunlu.'); return; }

        setLoading(true);
        result.style.display = 'none'; result.innerHTML = ''; wsEl.innerHTML = '';

        var fd = new FormData();
        fd.append('action','otb_ai_analyse');
        fd.append('_nonce', document.getElementById('otb-nonce').value);
        fd.append('beschreibung', beschreibung);
        fd.append('ort', ort);
        fd.append('marke_modell', document.getElementById('otb-marke').value);
        fd.append('kilometer',    document.getElementById('otb-km').value);
        fd.append('situationen',  document.getElementById('otb-situ').value);
        fd.append('geraeusche',   document.getElementById('otb-ses').value);
        fd.append('warnleuchten', document.getElementById('otb-warn').value);
        fd.append('dauer',        document.getElementById('otb-dauer').value);
        fd.append('share_consent', document.getElementById('otb-consent').checked ? '1' : '0');

        try {
            var res  = await fetch(ajax, {method:'POST', body:fd});
            var json = await res.json();
            setLoading(false);

            if (!json.success) {
                result.innerHTML = '<div class="otb-result-card" style="border-left-color:#e63946"><p>❌ <strong>Hata:</strong> '+(json.data?.message||'Bilinmeyen hata')+'</p></div>';
                result.style.display = 'block';
                if (credVal && json.data?.remaining_credits != null) credVal.textContent = json.data.remaining_credits;
                result.scrollIntoView({behavior:'smooth',block:'start'});
                return;
            }

            var d = json.data;
            var catHtml = '';
            if (d.matched_category) {
                catHtml = '<div style="background:rgba(249,115,22,0.12); border:1.5px solid rgba(249,115,22,0.35); border-radius:14px; padding:16px 20px; margin:20px 0; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px;">'
                    + '<div style="display:flex; align-items:center; gap:12px;">'
                    + '<div style="width:42px; height:42px; border-radius:50%; background:linear-gradient(135deg, #f97316, #ea580c); color:white; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0;">'
                    + '<i class="fa-solid fa-screwdriver-wrench"></i>'
                    + '</div>'
                    + '<div>'
                    + '<div style="font-size:11.5px; color:#fdba74; font-weight:800; text-transform:uppercase; letter-spacing:0.5px;">Önerilen Uzmanlık Branşı</div>'
                    + '<div style="font-size:16px; color:#ffffff; font-weight:800;">'+d.matched_category.name+'</div>'
                    + '</div>'
                    + '</div>'
                    + '<a href="'+d.matched_category.url+'" target="_blank" style="background:#f97316; color:white; padding:10px 18px; border-radius:10px; font-weight:700; font-size:13px; text-decoration:none; display:inline-flex; align-items:center; gap:6px; box-shadow:0 4px 12px rgba(249,115,22,0.3);">'
                    + d.matched_category.name+' Ustalarını İncele <i class="fa-solid fa-arrow-right"></i>'
                    + '</a>'
                    + '</div>';
            }

            result.innerHTML = '<div class="otb-result-card">'
                + '<h3>✅ Ön Teşhis Sonucu</h3>'
                + catHtml
                + '<p>'+(d.kurzer_text||'')+'</p>'
                + (d.wahrscheinliche_ursachen?.length ? '<h4>Olası Nedenler</h4>'+ul(d.wahrscheinliche_ursachen) : '')
                + (d.grundlegende_kontrollen?.length  ? '<h4>Kontrol Noktaları</h4>'+ul(d.grundlegende_kontrollen) : '')
                + (d.empfohlene_werkstatt_typen?.length ? '<h4>Uygun Servis Türü</h4>'+ul(d.empfohlene_werkstatt_typen) : '')
                + (d.kosten_einschaetzung ? '<p><strong style="color:#94a3b8">Maliyet Tahmini:</strong>'+badge(d.kosten_einschaetzung)+'</p>' : '')
                + (d.dringlichkeit ? '<p><strong style="color:#94a3b8">Aciliyet:</strong>'+badge(d.dringlichkeit)+'</p>' : '')
                + (d.folgen_bei_ignorieren ? '<p><strong style="color:#94a3b8">Yok sayarsan:</strong> '+d.folgen_bei_ignorieren+'</p>' : '')
                + (d.hinweis ? '<p style="color:#64748b"><em>💡 '+d.hinweis+'</em></p>' : '')
                + (d.blog_post_url ? '<p>📝 <a href="'+d.blog_post_url+'" target="_blank" style="color:#e63946">Blog yazısını gör</a></p>' : '')
                + '</div>';
            result.style.display = 'block';

            if (credVal && d.remaining_credits != null) credVal.textContent = d.remaining_credits;
            result.scrollIntoView({behavior:'smooth',block:'start'});

            var fd2 = new FormData();
            fd2.append('action','otb_ai_firms');
            fd2.append('_nonce', document.getElementById('otb-nonce').value);
            fd2.append('ort', ort);
            if (d.matched_category && d.matched_category.slug) {
                fd2.append('category', d.matched_category.slug);
            }
            var res2 = await fetch(ajax, {method:'POST', body:fd2});
            var wsj  = await res2.json();
            if (wsj.success && wsj.data?.html) {
                var headerText = wsj.data.category_name 
                    ? '🧰 ' + wsj.data.city + ' Bölgesindeki ' + wsj.data.category_name + ' Servisleri'
                    : '🧰 ' + wsj.data.city + ' Yakınındaki Servisler';
                wsEl.innerHTML = '<div class="otb-ws-card"><h3>' + headerText + '</h3>' + wsj.data.html + '</div>';
            }

        } catch(err) {
            setLoading(false);
            result.innerHTML = '<div class="otb-result-card" style="border-left-color:#e63946"><p>❌ İstek başarısız oldu.</p></div>';
            result.style.display = 'block';
        }
    });
})();
</script>
