<?php
/**
 * FindeWerkstatt.de — 404 Fehlerseite (Not Found)
 * 
 * @package FindeWerkstatt
 * @version 2.0.0
 */

get_header();
?>

<div class="fw-container" style="padding:80px 20px; text-align:center;">
    <div style="max-width:600px; margin:0 auto; background:#ffffff; border:1px solid var(--fw-border); border-radius:var(--fw-radius-lg); padding:50px 30px; box-shadow:var(--fw-shadow);">
        <div style="font-size:72px; font-weight:900; color:var(--fw-primary); line-height:1; margin-bottom:16px;">
            404
        </div>
        <h1 style="font-size:24px; font-weight:800; color:var(--fw-primary); margin-bottom:12px;">
            Seite nicht gefunden
        </h1>
        <p style="color:var(--fw-text-muted); font-size:15px; margin-bottom:30px; line-height:1.6;">
            Die von Ihnen gesuchte Seite oder Werkstatt existiert leider nicht mehr oder die Adresse wurde geändert.
        </p>

        <div style="display:flex; justify-content:center; gap:12px; flex-wrap:wrap;">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="fw-btn fw-btn-primary">
                Zur Startseite
            </a>
            <a href="<?php echo esc_url( home_url( '/werkstaetten/' ) ); ?>" class="fw-btn fw-btn-outline">
                Werkstätten durchsuchen
            </a>
        </div>
    </div>
</div>

<?php
get_footer();
