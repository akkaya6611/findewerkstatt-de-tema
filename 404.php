<?php
/**
 * FindeWerkstatt.de — 404 Fehlerseite (Not Found)
 * 
 * @package FindeWerkstatt
 * @version 2.0.0
 */

get_header();
?>

<main id="main-content" class="fw-container" style="padding:80px 20px; text-align:center;">
    <div style="max-width:600px; margin:0 auto; background:#ffffff; border:1px solid var(--fw-border); border-radius:var(--fw-radius-lg); padding:clamp(24px, 6vw, 50px) clamp(20px, 4vw, 30px); box-shadow:var(--fw-shadow);">
        <div aria-hidden="true" style="font-size:72px; font-weight:900; color:var(--fw-primary); line-height:1; margin-bottom:16px;">
            404
        </div>
        <h1 style="font-size:24px; font-weight:800; color:var(--fw-primary); margin-bottom:12px;">
            <?php echo esc_html( findewerkstatt_t( 'Seite nicht gefunden' ) ); ?>
        </h1>
        <p style="color:var(--fw-text-muted); font-size:15px; margin-bottom:30px; line-height:1.6;">
            <?php echo esc_html( findewerkstatt_t( 'Die gesuchte Seite oder Werkstatt konnten wir unter dieser Adresse nicht finden. Prüfen Sie die Adresse oder suchen Sie im Werkstattverzeichnis weiter.' ) ); ?>
        </p>

        <div style="display:flex; justify-content:center; gap:12px; flex-wrap:wrap;">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="fw-btn fw-btn-primary">
                <?php echo esc_html( findewerkstatt_t( 'Zur Startseite' ) ); ?>
            </a>
            <a href="<?php echo esc_url( get_post_type_archive_link( 'mechanic' ) ); ?>" class="fw-btn fw-btn-outline">
                <?php echo esc_html( findewerkstatt_t( 'Werkstätten durchsuchen' ) ); ?>
            </a>
        </div>
    </div>
</main>

<?php get_footer();
