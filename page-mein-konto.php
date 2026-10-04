<?php
/**
 * Template Name: Mein Konto
 * @package FindeWerkstatt
 */
$plans        = findewerkstatt_membership_plans();
$notice       = findewerkstatt_member_notice();
$values       = isset( $notice['values'] ) && is_array( $notice['values'] ) ? $notice['values'] : array();
$notice_kind  = $notice['kind'] ?? '';
$account_url  = findewerkstatt_page_url( 'mein-konto' );
$claim_intent = isset( $_GET['uebernehmen'] ) && is_scalar( $_GET['uebernehmen'] ) ? absint( $_GET['uebernehmen'] ) : 0;
$claim_post = $claim_intent ? get_post( $claim_intent ) : null;
if ( ! $claim_post || 'mechanic' !== $claim_post->post_type || 'publish' !== $claim_post->post_status || get_post_meta( $claim_intent, '_fw_owner_user_id', true ) ) { $claim_intent = 0; }
$request_plan = isset( $_GET['plan'] ) && is_string( $_GET['plan'] ) ? sanitize_key( wp_unslash( $_GET['plan'] ) ) : 'free';
$selected_plan = isset( $values['selected_plan'] ) && is_string( $values['selected_plan'] ) ? $values['selected_plan'] : $request_plan;
if ( ! isset( $plans[ $selected_plan ] ) ) {
    $selected_plan = 'free';
}
get_header();
?>
<main id="main-content" class="fw-container fw-page-content fw-member<?php echo is_user_logged_in() ? ' fw-account-dashboard' : ''; ?>">
    <?php if ( ! is_user_logged_in() ) : ?>
    <div class="fw-member-intro">
        <span class="fw-section-tag"><?php echo esc_html( findewerkstatt_t( 'Für Werkstattinhaber' ) ); ?></span>
        <h1><?php echo is_user_logged_in() ? findewerkstatt_t( 'Mein Werkstattkonto' ) : findewerkstatt_t( 'Ihr Werkstattkonto' ); ?></h1>
        <p><?php echo is_user_logged_in() ? findewerkstatt_t( 'Verwalten Sie Ihre Kontodaten, behalten Sie Ihre Betriebseinträge im Blick und fragen Sie bei Bedarf ein größeres Paket an.' ) : findewerkstatt_t( 'Melden Sie sich an oder erstellen Sie ein kostenloses Konto, um Ihre Betriebseinträge zu verwalten.' ); ?></p>
    </div>
    <?php endif; ?>
    <?php if ( $notice ) : ?>
        <div id="fw-member-notice" class="fw-form-notice fw-form-notice-<?php echo esc_attr( $notice['type'] ); ?>" tabindex="-1" role="<?php echo 'error' === $notice['type'] ? 'alert' : 'status'; ?>">
            <p><?php echo esc_html( $notice['message'] ); ?></p>
            <?php if ( ! empty( $notice['errors'] ) ) : ?>
                <ul><?php foreach ( $notice['errors'] as $error ) : ?><li><?php echo esc_html( $error ); ?></li><?php endforeach; ?></ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <?php if ( ! is_user_logged_in() ) : ?>
        <div class="fw-member-auth-grid">
            <section id="anmelden" class="fw-box fw-member-auth-panel" aria-labelledby="fw-member-login-heading">
                <h2 id="fw-member-login-heading"><?php echo esc_html( findewerkstatt_t( 'Bereits registriert?' ) ); ?></h2>
                <p class="fw-member-panel-intro"><?php echo esc_html( findewerkstatt_t( 'Melden Sie sich mit Ihrer E-Mail-Adresse und Ihrem Passwort an.' ) ); ?></p>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="fw-form" aria-labelledby="fw-member-login-heading"><?php findewerkstatt_language_field(); ?>
                    <input type="hidden" name="action" value="fw_member_login">
                    <?php if ( $claim_intent ) : ?><input type="hidden" name="return_workshop" value="<?php echo (int) $claim_intent; ?>"><?php endif; ?>
                    <?php wp_nonce_field( 'fw_member_login', 'fw_member_nonce' ); ?>
                    <div class="fw-form-field">
                        <label for="fw-member-login-email"><?php echo esc_html( findewerkstatt_t( 'E-Mail-Adresse *' ) ); ?></label>
                        <input id="fw-member-login-email" type="email" name="email" required maxlength="254" autocomplete="username" value="<?php echo esc_attr( 'login' === $notice_kind ? ( $values['email'] ?? '' ) : '' ); ?>">
                    </div>
                    <div class="fw-form-field">
                        <label for="fw-member-login-password"><?php echo esc_html( findewerkstatt_t( 'Passwort *' ) ); ?></label>
                        <input id="fw-member-login-password" type="password" name="password" required autocomplete="current-password">
                    </div>
                    <label class="fw-form-check"><input type="checkbox" name="remember" value="1" <?php checked( 'login' === $notice_kind && ! empty( $values['remember'] ) ); ?>><span><?php echo esc_html( findewerkstatt_t( 'Angemeldet bleiben' ) ); ?></span></label>
                    <button type="submit" class="fw-btn fw-btn-primary fw-btn-block"><?php echo esc_html( findewerkstatt_t( 'Anmelden' ) ); ?></button>
                    <p class="fw-member-form-link"><a href="<?php echo esc_url( wp_lostpassword_url( $account_url ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Passwort vergessen?' ) ); ?></a></p>
                    <div class="fw-member-resend-wrapper" style="margin-top: 16px; padding-top: 14px; border-top: 1px dashed #cbd5e1;">
                        <details <?php if ( in_array( $notice_kind, array( 'resend_verification', 'signup_pending' ), true ) ) echo 'open'; ?>>
                            <summary style="font-size: 13px; color: var(--fw-primary, #0284c7); cursor: pointer; font-weight: 500;">
                                <?php echo esc_html( findewerkstatt_t( 'Bestätigungs-E-Mail nicht erhalten? Erneut senden' ) ); ?>
                            </summary>
                            <div style="margin-top: 10px;">
                                <div class="fw-form-field" style="margin-bottom: 8px;">
                                    <label for="fw-resend-email" style="font-size: 12px;"><?php echo esc_html( findewerkstatt_t( 'Ihre E-Mail-Adresse' ) ); ?></label>
                                    <input id="fw-resend-email" type="email" name="resend_email_display" value="<?php echo esc_attr( $values['email'] ?? '' ); ?>" placeholder="name@beispiel.de" style="padding: 7px 10px; font-size: 13px;" oninput="document.getElementById('fw-resend-hidden-email').value=this.value;">
                                </div>
                            </div>
                        </details>
                    </div>
                </form>
                <form id="fw-resend-verification-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top: -6px;"><?php findewerkstatt_language_field(); ?>
                    <input type="hidden" name="action" value="fw_member_resend_verification">
                    <?php wp_nonce_field( 'fw_member_resend_verification', 'fw_member_nonce' ); ?>
                    <input type="hidden" id="fw-resend-hidden-email" name="email" value="<?php echo esc_attr( $values['email'] ?? '' ); ?>">
                    <button type="submit" class="fw-btn fw-btn-outline fw-btn-sm fw-btn-block"><?php echo esc_html( findewerkstatt_t( 'Bestätigungslink erneut anfordern' ) ); ?></button>
                </form>
            </section>
            <section id="registrieren" class="fw-box fw-member-auth-panel" aria-labelledby="fw-member-signup-heading">
                <h2 id="fw-member-signup-heading"><?php echo esc_html( findewerkstatt_t( 'Kostenloses Konto erstellen' ) ); ?></h2>
                <p class="fw-member-panel-intro"><?php echo esc_html( findewerkstatt_t( 'Für Inhaber und berechtigte Ansprechpartner von Werkstätten. Das Paket wählen Sie erst beim Eintragen Ihres Betriebs. Felder mit * sind erforderlich.' ) ); ?></p>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="fw-form" aria-labelledby="fw-member-signup-heading"><?php findewerkstatt_language_field(); ?>
                    <input type="hidden" name="action" value="fw_member_signup">
                    <?php if ( $claim_intent ) : ?><input type="hidden" name="return_workshop" value="<?php echo (int) $claim_intent; ?>"><?php endif; ?>
                    <input type="hidden" name="fw_submission_token" value="<?php echo esc_attr( findewerkstatt_form_submission_token( 'member_signup' ) ); ?>">
                    <?php wp_nonce_field( 'fw_member_signup', 'fw_member_nonce' ); ?>
                    <div class="fw-form-honeypot" style="display:none !important; visibility:hidden !important; position:absolute !important; left:-9999px !important; width:0 !important; height:0 !important; opacity:0 !important; pointer-events:none !important;" aria-hidden="true">
                        <label for="fw-member-company-website"><?php echo esc_html( findewerkstatt_t( 'Dieses Feld bitte leer lassen' ) ); ?></label>
                        <input id="fw-member-company-website" type="text" name="company_website" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="fw-form-field">
                        <label for="fw-member-signup-name"><?php echo esc_html( findewerkstatt_t( 'Vor- und Nachname *' ) ); ?></label>
                        <input id="fw-member-signup-name" type="text" name="name" required maxlength="160" autocomplete="name" value="<?php echo esc_attr( 'signup' === $notice_kind ? ( $values['name'] ?? '' ) : '' ); ?>">
                    </div>
                    <div class="fw-form-field">
                        <label for="fw-member-signup-email"><?php echo esc_html( findewerkstatt_t( 'E-Mail-Adresse *' ) ); ?></label>
                        <input id="fw-member-signup-email" type="email" name="email" required maxlength="254" autocomplete="email" value="<?php echo esc_attr( 'signup' === $notice_kind ? ( $values['email'] ?? '' ) : '' ); ?>">
                    </div>
                    <div class="fw-form-grid">
                        <div class="fw-form-field">
                            <label for="fw-member-signup-password"><?php echo esc_html( findewerkstatt_t( 'Passwort *' ) ); ?></label>
                            <input id="fw-member-signup-password" type="password" name="password" required minlength="12" maxlength="128" autocomplete="new-password" aria-describedby="fw-member-password-help">
                        </div>
                        <div class="fw-form-field">
                            <label for="fw-member-signup-password-confirm"><?php echo esc_html( findewerkstatt_t( 'Passwort wiederholen *' ) ); ?></label>
                            <input id="fw-member-signup-password-confirm" type="password" name="password_confirm" required minlength="12" maxlength="128" autocomplete="new-password" aria-describedby="fw-member-password-help">
                        </div>
                    </div>
                    <p id="fw-member-password-help" class="fw-form-help"><?php echo esc_html( findewerkstatt_t( 'Verwenden Sie 12 bis 128 Zeichen und ein Passwort, das Sie auf keiner anderen Website nutzen.' ) ); ?></p>
                    <label class="fw-form-check" for="fw-member-privacy">
                        <input id="fw-member-privacy" type="checkbox" name="privacy" value="1" required>
                        <span><?php echo esc_html( findewerkstatt_t( 'Ich habe die' ) ); ?> <a href="<?php echo esc_url( findewerkstatt_page_url( 'datenschutz' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( findewerkstatt_t( 'Datenschutzerklärung' ) ); ?><span class="screen-reader-text"> <?php echo esc_html( findewerkstatt_t( '(öffnet einen neuen Tab)' ) ); ?></span></a> gelesen. *</span>
                    </label>
                    <label class="fw-form-check" for="fw-member-terms">
                        <input id="fw-member-terms" type="checkbox" name="terms" value="1" required>
                        <span><?php echo esc_html( findewerkstatt_t( 'Ich akzeptiere die' ) ); ?> <a href="<?php echo esc_url( findewerkstatt_page_url( 'agb' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( findewerkstatt_t( 'Nutzungsbedingungen' ) ); ?><span class="screen-reader-text"> <?php echo esc_html( findewerkstatt_t( '(öffnet einen neuen Tab)' ) ); ?></span></a>. *</span>
                    </label>
                    <button type="submit" class="fw-btn fw-btn-primary fw-btn-block"><?php echo esc_html( findewerkstatt_t( 'Kostenloses Konto erstellen' ) ); ?></button>
                </form>
            </section>
        </div>
    <?php else :
        get_template_part( 'template-parts/member-dashboard', null, array( 'values' => $values, 'notice_kind' => $notice_kind, 'selected_plan' => $selected_plan, 'notice' => $notice ) );
    endif; ?>
</main>
<?php get_footer(); ?>
