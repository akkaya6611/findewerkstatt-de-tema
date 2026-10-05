<?php
/**
 * FindeWerkstatt.de — Kundenbewertungen & Sterne-Bewertungssystem
 *
 * Verwaltet authentische Kundenbewertungen (1-5 Sterne) für Kfz-Werkstätten
 * unter Nutzung des nativen WordPress-Kommentarsystems (Moderation, DSGVO & Spam-Schutz).
 *
 * @package FindeWerkstatt
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Bewertungs-Formularverarbeitung.
 */
function findewerkstatt_handle_workshop_review_submission() {
    if ( 'POST' !== $_SERVER['REQUEST_METHOD'] || ! isset( $_POST['fw_action'] ) || 'fw_submit_review' !== $_POST['fw_action'] ) {
        return;
    }

    $workshop_id = isset( $_POST['workshop_id'] ) ? absint( $_POST['workshop_id'] ) : 0;
    if ( ! $workshop_id || 'mechanic' !== get_post_type( $workshop_id ) ) {
        return;
    }

    $redirect_url = get_permalink( $workshop_id );

    // 1. Nonce
    if ( ! isset( $_POST['fw_review_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['fw_review_nonce'] ), 'fw_review_action' ) ) {
        wp_safe_redirect( add_query_arg( 'review_status', 'invalid_nonce', $redirect_url ) . '#fw-reviews' );
        exit;
    }

    // 2. Honeypot
    if ( ! empty( $_POST['review_hp_field'] ) ) {
        wp_safe_redirect( add_query_arg( 'review_status', 'success_moderation', $redirect_url ) . '#fw-reviews' );
        exit;
    }

    // 3. DSGVO
    if ( empty( $_POST['review_dsgvo'] ) ) {
        wp_safe_redirect( add_query_arg( 'review_status', 'missing_dsgvo', $redirect_url ) . '#fw-reviews' );
        exit;
    }

    // 4. Rating (1 bis 5 Sterne)
    $rating = isset( $_POST['review_rating'] ) ? absint( $_POST['review_rating'] ) : 0;
    if ( $rating < 1 || $rating > 5 ) {
        wp_safe_redirect( add_query_arg( 'review_status', 'invalid_rating', $redirect_url ) . '#fw-reviews' );
        exit;
    }

    // 5. Daten bereinigen
    $author_name  = sanitize_text_field( wp_unslash( $_POST['review_author'] ?? '' ) );
    $author_email = sanitize_email( wp_unslash( $_POST['review_email'] ?? '' ) );
    $content      = sanitize_textarea_field( wp_unslash( $_POST['review_content'] ?? '' ) );

    if ( empty( $author_name ) || empty( $author_email ) || ! is_email( $author_email ) || empty( $content ) ) {
        wp_safe_redirect( add_query_arg( 'review_status', 'missing_fields', $redirect_url ) . '#fw-reviews' );
        exit;
    }

    // Standardmäßig zur Moderation vorlegen (DSGVO & Netiquette)
    $comment_approved = current_user_can( 'manage_options' ) ? 1 : 0;

    $comment_data = array(
        'comment_post_ID'      => $workshop_id,
        'comment_author'       => $author_name,
        'comment_author_email' => $author_email,
        'comment_content'      => $content,
        'comment_type'         => 'workshop_review',
        'comment_approved'     => $comment_approved,
        'user_id'              => get_current_user_id(),
    );

    $comment_id = wp_insert_comment( $comment_data );

    if ( $comment_id && ! is_wp_error( $comment_id ) ) {
        update_comment_meta( $comment_id, '_fw_rating', $rating );
        findewerkstatt_recalculate_workshop_rating( $workshop_id );

        $status = $comment_approved ? 'success_published' : 'success_moderation';
        wp_safe_redirect( add_query_arg( 'review_status', $status, $redirect_url ) . '#fw-reviews' );
        exit;
    }

    wp_safe_redirect( add_query_arg( 'review_status', 'error', $redirect_url ) . '#fw-reviews' );
    exit;
}
if ( function_exists( 'add_action' ) ) {
    add_action( 'template_redirect', 'findewerkstatt_handle_workshop_review_submission' );
}


/**
 * Berechnet Durchschnittsbewertung und Anzahl neu und speichert in Post-Meta.
 *
 * @param int $workshop_id
 */
function findewerkstatt_recalculate_workshop_rating( $workshop_id ) {
    $comments = get_comments( array(
        'post_id' => $workshop_id,
        'status'  => 'approve',
        'type'    => 'workshop_review',
    ) );

    if ( empty( $comments ) ) {
        // Falls keine Kommentare existieren, behalte ggf. vorhandene Google-/Import-Bewertungen bei
        return;
    }

    $total_rating = 0;
    $count = 0;

    foreach ( $comments as $c ) {
        $r = (int) get_comment_meta( $c->comment_ID, '_fw_rating', true );
        if ( $r >= 1 && $r <= 5 ) {
            $total_rating += $r;
            $count++;
        }
    }

    if ( $count > 0 ) {
        $avg = round( $total_rating / $count, 1 );
        update_post_meta( $workshop_id, '_fw_site_rating_avg', $avg );
        update_post_meta( $workshop_id, '_fw_site_rating_count', $count );
    } else {
        delete_post_meta( $workshop_id, '_fw_site_rating_avg' );
        delete_post_meta( $workshop_id, '_fw_site_rating_count' );
    }
}

/**
 * Bei Statusänderung (Genehmigung / Löschen) Bewertung neu berechnen.
 */
if ( function_exists( 'add_action' ) ) {
    add_action( 'transition_comment_status', function( $new_status, $old_status, $comment ) {
        if ( 'workshop_review' === $comment->comment_type ) {
            findewerkstatt_recalculate_workshop_rating( $comment->comment_post_ID );
        }
    }, 10, 3 );

    add_action( 'delete_comment', function( $comment_id ) {
        $comment = get_comment( $comment_id );
        if ( $comment && 'workshop_review' === $comment->comment_type ) {
            findewerkstatt_recalculate_workshop_rating( $comment->comment_post_ID );
        }
    } );
}


/**
 * Bewertungen und Bewertungsformular rendern.
 *
 * @param int $workshop_id
 */
function findewerkstatt_render_workshop_reviews( $workshop_id ) {
    $status = isset( $_GET['review_status'] ) ? sanitize_key( $_GET['review_status'] ) : '';
    
    // Genehmigte Bewertungen abrufen
    $reviews = get_comments( array(
        'post_id' => $workshop_id,
        'status'  => 'approve',
        'type'    => 'workshop_review',
        'parent'  => 0, // Nur Hauptbewertungen
        'order'   => 'DESC',
    ) );

    $site_rating_avg   = get_post_meta( $workshop_id, '_fw_site_rating_avg', true );
    $site_rating_count = (int) get_post_meta( $workshop_id, '_fw_site_rating_count', true );
    ?>
    <section id="fw-reviews" class="fw-box fw-reviews-section" aria-label="<?php echo esc_attr( findewerkstatt_t( 'FindeWerkstatt Bewertungen' ) ); ?>">
        <div class="fw-reviews-header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px; margin-bottom:24px;">
            <div>
                <h2 style="margin:0 0 6px; font-size:20px; color:#0f172a; display:flex; align-items:center; gap:8px;">
                    <span aria-hidden="true">⭐</span>
                    <span><?php echo esc_html( findewerkstatt_t( 'FindeWerkstatt Bewertungen' ) ); ?></span>
                </h2>
                <p class="fw-muted" style="margin:0; font-size:13.5px;">
                    <?php echo esc_html( sprintf( findewerkstatt_t( 'Verifizierte Bewertungen von Autofahrern auf FindeWerkstatt für %s.' ), get_the_title( $workshop_id ) ) ); ?>
                </p>
            </div>
            
            <?php if ( ! empty( $site_rating_avg ) && $site_rating_count > 0 ) : ?>
                <div class="fw-reviews-summary" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:10px 18px; text-align:right;">
                    <div style="font-size:22px; font-weight:800; color:#0f172a; line-height:1;">
                        <?php echo esc_html( number_format( (float) $site_rating_avg, 1, ',', '.' ) ); ?> <span style="font-size:14px; font-weight:500; color:#64748b;">/ 5</span>
                    </div>
                    <div style="margin-top:4px;">
                        <?php echo findewerkstatt_render_stars( $site_rating_avg, $site_rating_count ); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Status Alerts -->
        <?php if ( 'success_published' === $status ) : ?>
            <div class="fw-form-notice fw-form-notice-success" role="status" style="margin-bottom:20px; padding:14px 18px; background:#ecfdf5; border:1px solid #10b981; border-radius:12px; color:#065f46;">
                ✓ <?php echo esc_html( findewerkstatt_t( 'Vielen Dank für Ihre Bewertung! Sie wurde erfolgreich veröffentlicht.' ) ); ?>
            </div>
        <?php elseif ( 'success_moderation' === $status ) : ?>
            <div class="fw-form-notice fw-form-notice-success" role="status" style="margin-bottom:20px; padding:14px 18px; background:#ecfdf5; border:1px solid #10b981; border-radius:12px; color:#065f46;">
                ✓ <?php echo esc_html( findewerkstatt_t( 'Vielen Dank! Ihre Bewertung wurde zur redaktionellen Prüfung übermittelt und wird in Kürze freigeschaltet.' ) ); ?>
            </div>
        <?php elseif ( 'missing_fields' === $status ) : ?>
            <div class="fw-form-notice fw-form-notice-error" role="alert" style="margin-bottom:20px; padding:14px 18px; background:#fef2f2; border:1px solid #ef4444; border-radius:12px; color:#991b1b;">
                ⚠️ <?php echo esc_html( findewerkstatt_t( 'Bitte füllen Sie alle erforderlichen Felder (Name, E-Mail, Bewertungstext) aus.' ) ); ?>
            </div>
        <?php elseif ( 'invalid_rating' === $status ) : ?>
            <div class="fw-form-notice fw-form-notice-error" role="alert" style="margin-bottom:20px; padding:14px 18px; background:#fef2f2; border:1px solid #ef4444; border-radius:12px; color:#991b1b;">
                ⚠️ <?php echo esc_html( findewerkstatt_t( 'Bitte vergeben Sie zwischen 1 und 5 Sternen.' ) ); ?>
            </div>
        <?php elseif ( 'missing_dsgvo' === $status ) : ?>
            <div class="fw-form-notice fw-form-notice-error" role="alert" style="margin-bottom:20px; padding:14px 18px; background:#fef2f2; border:1px solid #ef4444; border-radius:12px; color:#991b1b;">
                ⚠️ <?php echo esc_html( findewerkstatt_t( 'Bitte stimmen Sie den Datenschutzbestimmungen zu.' ) ); ?>
            </div>
        <?php endif; ?>

        <!-- Liste der vorhandenen Bewertungen -->
        <?php if ( ! empty( $reviews ) ) : ?>
            <div class="fw-reviews-list" style="display:flex; flex-direction:column; gap:16px; margin-bottom:32px;">
                <?php foreach ( $reviews as $rev ) : 
                    $rev_rating = (int) get_comment_meta( $rev->comment_ID, '_fw_rating', true );
                    if ( $rev_rating < 1 ) $rev_rating = 5;
                    $replies = get_comments( array( 'parent' => $rev->comment_ID, 'status' => 'approve' ) );
                ?>
                    <article class="fw-review-card" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:18px 20px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                            <div>
                                <strong style="font-size:15px; color:#0f172a;"><?php echo esc_html( $rev->comment_author ); ?></strong>
                                <span style="font-size:12px; color:#64748b; margin-left:8px;"><?php echo esc_html( date_i18n( 'd. F Y', strtotime( $rev->comment_date ) ) ); ?></span>
                            </div>
                            <div>
                                <?php echo findewerkstatt_render_stars( $rev_rating, null ); ?>
                            </div>
                        </div>
                        <div style="font-size:14px; line-height:1.6; color:#334155;">
                            <?php echo nl2br( esc_html( $rev->comment_content ) ); ?>
                        </div>

                        <!-- Antwort der Werkstatt (falls vorhanden) -->
                        <?php if ( ! empty( $replies ) ) : ?>
                            <?php foreach ( $replies as $reply ) : ?>
                                <div class="fw-review-reply" style="margin-top:14px; padding:12px 16px; background:#ffffff; border-left:3px solid #fb6006; border-radius:8px;">
                                    <div style="font-weight:700; font-size:13px; color:#0f172a; margin-bottom:4px;">
                                        💬 <?php echo esc_html( findewerkstatt_t( 'Antwort des Betriebs' ) ); ?>
                                    </div>
                                    <div style="font-size:13.5px; color:#475569; line-height:1.5;">
                                        <?php echo nl2br( esc_html( $reply->comment_content ) ); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p class="fw-reviews-empty" style="color:#64748b; font-size:14px; margin-bottom:28px; background:#f8fafc; padding:16px 20px; border-radius:10px; border:1px dashed #cbd5e1;">
                <?php echo esc_html( findewerkstatt_t( 'Noch keine Bewertungen auf FindeWerkstatt.' ) ); ?>
            </p>
        <?php endif; ?>

        <!-- Bewertungsformular -->
        <div class="fw-review-form-wrap" style="background:#ffffff; border:1px solid #e2e8f0; border-radius:14px; padding:22px 24px;">
            <h3 style="margin:0 0 14px; font-size:16px; color:#0f172a;">
                ✍️ <?php echo esc_html( findewerkstatt_t( 'Jetzt Werkstatt bewerten' ) ); ?>
            </h3>
            
            <form method="post" action="<?php echo esc_url( get_permalink( $workshop_id ) ); ?>">
                <input type="hidden" name="fw_action" value="fw_submit_review">
                <input type="hidden" name="workshop_id" value="<?php echo esc_attr( $workshop_id ); ?>">
                <?php wp_nonce_field( 'fw_review_action', 'fw_review_nonce' ); ?>

                <!-- Honeypot -->
                <div style="display:none;" aria-hidden="true">
                    <label for="review_hp_field">Website</label>
                    <input type="text" id="review_hp_field" name="review_hp_field" tabindex="-1" autocomplete="off">
                </div>

                <!-- Stern-Auswahl -->
                <div style="margin-bottom:16px;">
                    <label style="display:block; font-size:13.5px; font-weight:600; color:#1e293b; margin-bottom:6px;">
                        <?php echo esc_html( findewerkstatt_t( 'Ihre Gesamtnote' ) ); ?> <span style="color:#ef4444;">*</span>
                    </label>
                    <div class="fw-star-rating-select" style="display:flex; gap:12px;">
                        <?php for ( $i = 5; $i >= 1; $i-- ) : ?>
                            <label style="display:inline-flex; align-items:center; gap:4px; font-size:14px; font-weight:700; cursor:pointer;">
                                <input type="radio" name="review_rating" value="<?php echo esc_attr( $i ); ?>" <?php checked( $i, 5 ); ?>>
                                <span><?php echo esc_html( $i ); ?> ★</span>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="fw-inquiry-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:16px; margin-bottom:16px;">
                    <div class="fw-form-field">
                        <label for="review-author"><?php echo esc_html( findewerkstatt_t( 'Ihr Name' ) ); ?> <span style="color:#ef4444;">*</span></label>
                        <input type="text" id="review-author" name="review_author" required placeholder="<?php echo esc_attr( findewerkstatt_t( 'Vorname oder Initialen' ) ); ?>" class="fw-input">
                    </div>
                    <div class="fw-form-field">
                        <label for="review-email"><?php echo esc_html( findewerkstatt_t( 'Ihre E-Mail-Adresse' ) ); ?> <span style="color:#ef4444;">*</span></label>
                        <input type="email" id="review-email" name="review_email" required placeholder="<?php echo esc_attr( findewerkstatt_t( 'Wird nicht veröffentlicht' ) ); ?>" class="fw-input">
                    </div>
                </div>

                <div class="fw-form-field" style="margin-bottom:16px;">
                    <label for="review-content"><?php echo esc_html( findewerkstatt_t( 'Ihre Bewertung & Erfahrung' ) ); ?> <span style="color:#ef4444;">*</span></label>
                    <textarea id="review-content" name="review_content" required rows="4" placeholder="<?php echo esc_attr( findewerkstatt_t( 'Wie zufrieden waren Sie mit dem Service, der Freundlichkeit, der Terminvergabe und den Kosten?' ) ); ?>" class="fw-input"></textarea>
                </div>

                <div class="fw-form-field fw-form-checkbox" style="margin-bottom:18px;">
                    <label style="display:flex; align-items:flex-start; gap:8px; font-size:12.5px; color:#64748b; cursor:pointer;">
                        <input type="checkbox" name="review_dsgvo" value="1" required style="margin-top:2px;">
                        <span>
                            <?php echo esc_html( findewerkstatt_t( 'Ich bestätige, dass dies ein echter Erfahrungsbericht ist und stimme der Speicherung meiner Daten gemäß' ) ); ?>
                            <a href="<?php echo esc_url( function_exists( 'findewerkstatt_page_url' ) ? findewerkstatt_page_url( 'datenschutz' ) : home_url( '/datenschutz/' ) ); ?>" target="_blank" rel="noopener" style="color:#fb6006;">
                                <?php echo esc_html( findewerkstatt_t( 'Datenschutzerklärung' ) ); ?>
                            </a> zu.
                        </span>
                    </label>
                </div>

                <button type="submit" class="fw-btn fw-btn-primary" style="font-weight:700;">
                    <span><?php echo esc_html( findewerkstatt_t( 'Bewertung absenden' ) ); ?></span>
                </button>
            </form>
        </div>
    </section>
    <?php
}
