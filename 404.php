<?php
/**
 * FindeWerkstatt.de — 404 Fehlerseite (Not Found)
 * 
 * @package FindeWerkstatt
 * @version 3.4.0
 */

get_header();
?>

<main id="main-content" class="fw-container" style="padding:60px 20px; text-align:center;">
    <div style="max-width:640px; margin:0 auto; background:#ffffff; border:1px solid var(--fw-border); border-radius:var(--fw-radius-lg); padding:clamp(24px, 6vw, 48px) clamp(20px, 4vw, 36px); box-shadow:var(--fw-shadow);">
        <div aria-hidden="true" style="font-size:72px; font-weight:900; color:var(--fw-accent); line-height:1; margin-bottom:12px;">
            404
        </div>
        <h1 style="font-size:26px; font-weight:800; color:var(--fw-primary); margin-bottom:12px;">
            <?php echo esc_html( findewerkstatt_t( 'Diese Seite wurde leider nicht gefunden.' ) ); ?>
        </h1>
        <p style="color:var(--fw-text-muted); font-size:15px; margin-bottom:28px; line-height:1.6;">
            <?php echo esc_html( findewerkstatt_t( 'Möglicherweise wurde der Eintrag verschoben oder existiert nicht mehr.' ) ); ?>
        </p>

        <!-- Search Box -->
        <form action="<?php echo esc_url( get_post_type_archive_link( 'mechanic' ) ); ?>" method="get" style="margin-bottom:28px;">
            <div style="display:flex; gap:8px; max-width:480px; margin:0 auto;">
                <input type="text" name="s" placeholder="<?php echo esc_attr( findewerkstatt_t( 'Werkstatt oder Stadt suchen...' ) ); ?>" style="flex:1; padding:12px 16px; border:1px solid var(--fw-border); border-radius:var(--fw-radius); font-size:15px;" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Werkstatt oder Stadt suchen' ) ); ?>">
                <button type="submit" class="fw-btn fw-btn-primary">
                    <?php echo esc_html( findewerkstatt_t( 'Werkstatt suchen' ) ); ?>
                </button>
            </div>
        </form>

        <div style="display:flex; justify-content:center; gap:12px; flex-wrap:wrap;">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="fw-btn fw-btn-outline">
                <?php echo esc_html( findewerkstatt_t( 'Zur Startseite' ) ); ?>
            </a>
            <a href="<?php echo esc_url( get_post_type_archive_link( 'mechanic' ) ); ?>" class="fw-btn fw-btn-primary">
                <?php echo esc_html( findewerkstatt_t( 'Werkstatt suchen' ) ); ?>
            </a>
        </div>
    </div>
</main>

<?php get_footer();
