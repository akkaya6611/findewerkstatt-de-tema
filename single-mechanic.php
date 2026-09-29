<?php
/**
 * FindeWerkstatt.de — Einzelansicht Kfz-Werkstatt (Single Template)
 * 
 * @package FindeWerkstatt
 * @version 2.0.0
 */

get_header();

while ( have_posts() ) : the_post();
    $post_id    = get_the_ID();
    $title      = get_the_title();
    $phone      = get_post_meta( $post_id, '_mechanic_phone', true );
    $whatsapp   = get_post_meta( $post_id, '_mechanic_whatsapp', true );
    $email      = get_post_meta( $post_id, '_mechanic_email', true );
    $website    = get_post_meta( $post_id, '_mechanic_website', true );
    $address    = get_post_meta( $post_id, '_mechanic_address', true );
    $plz        = get_post_meta( $post_id, '_mechanic_plz', true );
    $rating_avg = get_post_meta( $post_id, '_mechanic_rating_avg', true ) ?: '4.9';
    $rating_cnt = get_post_meta( $post_id, '_mechanic_rating_count', true ) ?: '18';
    
    $hours_week = get_post_meta( $post_id, '_mechanic_hours_weekday', true ) ?: '08:00 - 18:00 Uhr';
    $hours_sat  = get_post_meta( $post_id, '_mechanic_hours_saturday', true ) ?: '09:00 - 13:00 Uhr';
    $hours_sun  = get_post_meta( $post_id, '_mechanic_hours_sunday', true ) ?: 'Geschlossen';

    $city_terms = wp_get_post_terms( $post_id, 'mechanic_city' );
    $city       = ( $city_terms && ! is_wp_error( $city_terms ) ) ? $city_terms[0] : null;

    $services   = wp_get_post_terms( $post_id, 'service_type' );
    $brands     = wp_get_post_terms( $post_id, 'car_brand' );

    $maps_url     = findewerkstatt_get_maps_url( $title, $address, $city ? $city->name : '' );
    $whatsapp_url = findewerkstatt_get_whatsapp_url( $whatsapp ?: $phone, $title );
?>

<div class="fw-container" style="padding-top:24px;">
    <!-- Breadcrumbs -->
    <nav class="fw-breadcrumbs" aria-label="Brotkrümelnavigation">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Startseite</a>
        <span>›</span>
        <a href="<?php echo esc_url( home_url( '/werkstaetten/' ) ); ?>">Werkstätten</a>
        <?php if ( $city ) : ?>
            <span>›</span>
            <a href="<?php echo esc_url( get_term_link( $city ) ); ?>"><?php echo esc_html( $city->name ); ?></a>
        <?php endif; ?>
        <span>›</span>
        <span><?php echo esc_html( $title ); ?></span>
    </nav>

    <!-- Hero Header Box -->
    <div class="fw-single-hero">
        <div style="margin-bottom:12px;">
            <?php echo findewerkstatt_render_badges( $post_id ); ?>
            <?php echo findewerkstatt_get_open_status(); ?>
        </div>

        <div class="fw-single-title-row">
            <div>
                <h1><?php echo esc_html( $title ); ?></h1>
                <div style="margin-top:8px;">
                    <?php echo findewerkstatt_render_stars( $rating_avg, $rating_cnt ); ?>
                </div>
                <div class="fw-card-address" style="margin-top:10px; font-size:15px;">
                    <span>📍</span>
                    <span><?php echo esc_html( trim( "{$address}, {$plz} " . ( $city ? $city->name : '' ), ', ' ) ); ?></span>
                </div>
            </div>

            <!-- Quick Action Buttons -->
            <div style="display:flex; flex-direction:column; gap:10px; min-width:220px;">
                <?php if ( $phone ) : ?>
                    <a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>" class="fw-btn fw-btn-phone fw-btn-lg">
                        📞 Jetzt anrufen
                    </a>
                <?php endif; ?>

                <?php if ( $whatsapp_url ) : ?>
                    <a href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener" class="fw-btn fw-btn-whatsapp">
                        💬 WhatsApp schreiben
                    </a>
                <?php endif; ?>

                <a href="<?php echo esc_url( $maps_url ); ?>" target="_blank" rel="noopener" class="fw-btn fw-btn-outline">
                    🗺️ Route planen
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content + Sidebar Layout -->
    <div class="fw-single-layout">
        <!-- Main Column -->
        <div>
            <!-- Über uns -->
            <div class="fw-box">
                <h2>Über den Betrieb & Meisterprofil</h2>
                <div style="font-size:15.5px; line-height:1.7; color:#334155;">
                    <?php the_content(); ?>
                </div>
            </div>

            <!-- Leistungen & Spezialisierungen -->
            <?php if ( ! empty( $services ) && ! is_wp_error( $services ) ) : ?>
                <div class="fw-box">
                    <h2>Angebotene Leistungen & Services</h2>
                    <div class="fw-services-list">
                        <?php foreach ( $services as $s ) : ?>
                            <div class="fw-service-item">
                                <span class="fw-service-check">✓</span>
                                <span><?php echo esc_html( $s->name ); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Unterstützte Marken -->
            <?php if ( ! empty( $brands ) && ! is_wp_error( $brands ) ) : ?>
                <div class="fw-box">
                    <h2>Reparierte & gewartete Automarken</h2>
                    <div style="display:flex; flex-wrap:wrap; gap:10px;">
                        <?php foreach ( $brands as $b ) : ?>
                            <span class="fw-badge fw-badge-service" style="padding:8px 14px; font-size:13.5px;">
                                🚗 <?php echo esc_html( $b->name ); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- FAQ Accordion -->
            <div class="fw-box">
                <h2>Häufige Fragen zu dieser Werkstatt</h2>
                
                <div class="fw-faq-item">
                    <button type="button" class="fw-faq-question">
                        <span>Werden Reparaturen nach Herstellervorgaben durchgeführt?</span>
                        <span class="fw-faq-icon">+</span>
                    </button>
                    <div class="fw-faq-answer" style="display:none;">
                        Ja, als zertifizierter Fachbetrieb werden alle Wartungen, Inspektionen und Reparaturen streng nach den Richtlinien des jeweiligen Herstellers ausgeführt. Der volle Garantieanspruch Ihres Fahrzeugs bleibt dabei lückenlos erhalten.
                    </div>
                </div>

                <div class="fw-faq-item">
                    <button type="button" class="fw-faq-question">
                        <span>Wie erhalte ich einen Kostenvoranschlag?</span>
                        <span class="fw-faq-icon">+</span>
                    </button>
                    <div class="fw-faq-answer" style="display:none;">
                        Rufen Sie direkt unter der angegebenen Telefonnummer an oder schreiben Sie per WhatsApp mit Angabe Ihrer Fahrzeugdaten (Fahrzeugschein zu 2.1 und 2.2) und einer kurzen Beschreibung des Schadens.
                    </div>
                </div>

                <div class="fw-faq-item">
                    <button type="button" class="fw-faq-question">
                        <span>Welche Zahlungsmethoden werden akzeptiert?</span>
                        <span class="fw-faq-icon">+</span>
                    </button>
                    <div class="fw-faq-answer" style="display:none;">
                        In der Regel werden Barzahlung, Girocard (EC-Karte), Kreditkarten (Visa, Mastercard) sowie bei Firmenkunden Überweisung nach Rechnungsstellung akzeptiert.
                    </div>
                </div>
            </div>

            <!-- Bewertungen -->
            <div class="fw-box">
                <h2>Kundenbewertungen (<?php echo esc_html( $rating_cnt ); ?>)</h2>
                <div style="display:flex; align-items:center; gap:20px; padding:20px; background:#f8fafc; border-radius:12px; margin-bottom:20px;">
                    <div style="font-size:42px; font-weight:900; color:var(--fw-primary); line-height:1;">
                        <?php echo esc_html( number_format( (float) $rating_avg, 1, ',', '.' ) ); ?>
                    </div>
                    <div>
                        <div style="font-size:18px; color:#f59e0b;">★★★★★</div>
                        <div style="font-size:13px; color:var(--fw-text-muted);">Basierend auf <?php echo esc_html( $rating_cnt ); ?> verifizierten Erfahrungen</div>
                    </div>
                </div>

                <!-- Beispiel Bewertung -->
                <div style="border-bottom:1px solid #e2e8f0; padding-bottom:16px; margin-bottom:16px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <strong>Michael S.</strong>
                        <span style="font-size:12.5px; color:#94a3b8;">Vor 2 Wochen</span>
                    </div>
                    <div style="color:#f59e0b; font-size:13px; margin-bottom:6px;">★★★★★ (5/5)</div>
                    <p style="font-size:14px; color:#475569;">
                        Sehr kompetente Beratung, schneller Bremsenwechsel und transparente Rechnung. Der Wagen war pünktlich fertig. Absolut empfehlenswert!
                    </p>
                </div>
            </div>
        </div>

        <!-- Sticky Sidebar -->
        <div>
            <div class="fw-sidebar">
                <!-- Kontaktdaten Box -->
                <div class="fw-contact-card">
                    <h3 style="font-size:18px; margin-bottom:14px; color:var(--fw-primary);">Kontakt & Anfahrt</h3>
                    
                    <div style="margin-bottom:14px; font-size:14px;">
                        <strong>Adresse:</strong><br>
                        <?php echo esc_html( $address ); ?><br>
                        <?php echo esc_html( "{$plz} " . ( $city ? $city->name : '' ) ); ?>
                    </div>

                    <?php if ( $phone ) : ?>
                        <div style="margin-bottom:14px; font-size:14px;">
                            <strong>Telefon:</strong><br>
                            <a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>" style="color:var(--fw-info); font-weight:700;">
                                <?php echo esc_html( $phone ); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if ( $email ) : ?>
                        <div style="margin-bottom:14px; font-size:14px;">
                            <strong>E-Mail:</strong><br>
                            <a href="mailto:<?php echo esc_attr( $email ); ?>" style="color:var(--fw-info);">
                                <?php echo esc_html( $email ); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if ( $website ) : ?>
                        <div style="margin-bottom:14px; font-size:14px;">
                            <strong>Webseite:</strong><br>
                            <a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="nofollow noopener" style="color:var(--fw-info);">
                                <?php echo esc_html( preg_replace( '#^https?://#', '', rtrim( $website, '/' ) ) ); ?> ↗
                            </a>
                        </div>
                    <?php endif; ?>

                    <hr style="border:0; border-top:1px solid var(--fw-border); margin:16px 0;">

                    <!-- Öffnungszeiten -->
                    <h4 style="font-size:15px; margin-bottom:10px;">⏰ Öffnungszeiten</h4>
                    <table class="fw-hours-table">
                        <tr>
                            <td>Mo – Fr</td>
                            <td><?php echo esc_html( $hours_week ); ?></td>
                        </tr>
                        <tr>
                            <td>Samstag</td>
                            <td><?php echo esc_html( $hours_sat ); ?></td>
                        </tr>
                        <tr>
                            <td>Sonntag</td>
                            <td><?php echo esc_html( $hours_sun ); ?></td>
                        </tr>
                    </table>
                </div>

                <!-- Google Maps Button -->
                <div class="fw-contact-card" style="text-align:center;">
                    <div style="font-size:32px; margin-bottom:10px;">🗺️</div>
                    <h4 style="margin-bottom:6px;">Anfahrt navigieren</h4>
                    <p style="font-size:13px; color:var(--fw-text-muted); margin-bottom:14px;">
                        Starten Sie die Navigation direkt in Google Maps auf Ihrem Smartphone.
                    </p>
                    <a href="<?php echo esc_url( $maps_url ); ?>" target="_blank" rel="noopener" class="fw-btn fw-btn-primary fw-btn-block">
                        Route in Google Maps öffnen
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
endwhile;

get_footer();
