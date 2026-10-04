<?php
/** Workshop profile with ad-friendly layout and below-the-fold contact conversion. */
get_header();
while ( have_posts() ) : the_post();
$id = get_the_ID();
$title = get_the_title();
$phone = findewerkstatt_normalize_phone( get_post_meta( $id, '_mechanic_phone', true ) );
$whatsapp_url = findewerkstatt_get_whatsapp_url( get_post_meta( $id, '_mechanic_whatsapp', true ), $title, $phone );
$email = get_post_meta( $id, '_mechanic_email_public', true ) === 'yes' ? get_post_meta( $id, '_mechanic_email', true ) : '';
$website = findewerkstatt_get_website_url( get_post_meta( $id, '_mechanic_website', true ) );
$address = get_post_meta( $id, '_mechanic_address', true );
$plz = get_post_meta( $id, '_mechanic_plz', true );
$city = findewerkstatt_get_city_term( $id );
$city_context = $city ? findewerkstatt_location_context( $city ) : null;
$city_name = $city_context ? $city_context['name'] : '';
$address_city = $city_name;
if ( $city_context && in_array( $city_context['kind'], array( 'district', 'borough' ), true ) && $city_context['parent_name'] ) {
    $address_city = $city_name . ', ' . $city_context['parent_name'];
}
$rating = findewerkstatt_get_rating( $id );
$services = wp_get_post_terms( $id, 'service_type' );
$brands = wp_get_post_terms( $id, 'car_brand' );
$spoken_languages = findewerkstatt_workshop_language_labels( $id );
$maps_url = ( $address || $city ) ? findewerkstatt_get_maps_url( $title, trim( $address . ' ' . $plz ), $address_city ) : '';
$profile_image_id = get_post_thumbnail_id( $id );
$profile_image_attributes = array( 'class' => 'fw-profile-image-img', 'loading' => 'eager', 'decoding' => 'async' );
if ( $profile_image_id && ! trim( (string) get_post_meta( $profile_image_id, '_wp_attachment_image_alt', true ) ) ) {
    $profile_image_attributes['alt'] = sprintf( findewerkstatt_t( 'Bild zum Profil von %s' ), wp_strip_all_tags( html_entity_decode( $title, ENT_QUOTES, 'UTF-8' ) ) );
}
$profile_image = $profile_image_id ? wp_get_attachment_image( $profile_image_id, 'large', false, $profile_image_attributes ) : '';
$clean_full_address = findewerkstatt_clean_address( $address, $title, $plz, $address_city );
?>
<main id="main-content" class="fw-container fw-page-content">
    <nav class="fw-breadcrumbs" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Brotkrümelnavigation' ) ); ?>">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Startseite' ) ); ?></a>
        <span aria-hidden="true">›</span>
        <a href="<?php echo esc_url( get_post_type_archive_link( 'mechanic' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Werkstätten' ) ); ?></a>
        <?php if ( $city ) : ?>
            <span aria-hidden="true">›</span>
            <a href="<?php echo esc_url( get_term_link( $city ) ); ?>"><?php echo esc_html( $city_name ); ?></a>
        <?php endif; ?>
        <span aria-hidden="true">›</span>
        <span aria-current="page"><?php echo esc_html( $title ); ?></span>
    </nav>

    <!-- Workshop Hero Header (Clean, professional, without top contact exit) -->
    <div class="fw-single-hero">
        <div class="fw-profile-badges">
            <?php echo findewerkstatt_render_badges( $id ); ?>
            <?php echo findewerkstatt_get_open_status( $id, false ); ?>
        </div>
        <div class="fw-single-title-row">
            <div class="fw-single-title-content">
                <h1><?php echo esc_html( $title ); ?></h1>
                <div class="fw-single-meta-row">
                    <?php echo findewerkstatt_render_stars( $rating['rating'], $rating['count'] ); ?>
                    <p class="fw-card-address">
                        <?php echo findewerkstatt_icon( 'pin', 16 ); ?>
                        <span><?php echo esc_html( $clean_full_address ?: findewerkstatt_t( 'Standort nicht angegeben' ) ); ?></span>
                    </p>
                </div>
                <?php get_template_part( 'template-parts/workshop-location', null, array( 'context' => $city_context ) ); ?>
            </div>
            <div class="fw-profile-hero-highlights">
                <a class="fw-btn fw-btn-primary fw-btn-scroll-cta" href="#fw-kontakt-termin">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    <span><?php echo esc_html( findewerkstatt_t( 'Kontakt & Termine' ) ); ?> &darr;</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Ad Zone 1: Premium Top Responsive Banner (Google AdSense / Direct Sponsor) -->
    <?php if ( function_exists( 'findewerkstatt_render_ad' ) ) { echo findewerkstatt_render_ad( 'top_banner' ); } ?>

    <div class="fw-single-layout">
        <div class="fw-single-main">
            <!-- Profile Image -->
            <?php
            $single_real_photo = get_post_meta( $id, '_mechanic_is_real_photo', true ) === 'yes' ? get_post_meta( $id, '_mechanic_google_photo_url', true ) : '';
            ?>
            <figure class="fw-profile-image<?php echo ( $profile_image || $single_real_photo ) ? '' : ' fw-profile-image-symbol'; ?>">
                <?php if ( $profile_image ) : ?>
                    <?php echo $profile_image; ?>
                <?php elseif ( $single_real_photo ) : ?>
                    <img class="fw-profile-image-img" src="<?php echo esc_url( $single_real_photo ); ?>" alt="<?php echo esc_attr( sprintf( findewerkstatt_t( 'Foto von %s' ), wp_strip_all_tags( $title ) ) ); ?>" width="800" height="420" loading="eager" decoding="async" style="object-fit:cover;">
                <?php else : ?>
                    <img class="fw-profile-image-img" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/placeholder-mechanic.webp' ); ?>" alt="<?php echo esc_attr( findewerkstatt_t( 'Symbolbild: Arbeiten an einem Motor' ) ); ?>" width="800" height="420" loading="eager" decoding="async">
                    <figcaption><?php echo esc_html( findewerkstatt_t( 'Symbolbild' ) ); ?></figcaption>
                <?php endif; ?>
            </figure>

            <!-- About Section -->
            <section class="fw-box fw-profile-about">
                <h2>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <span><?php echo esc_html( findewerkstatt_t( 'Über den Betrieb' ) ); ?></span>
                </h2>
                <div class="fw-prose"><?php if ( trim( get_the_content() ) ) { the_content(); } else { echo '<p>' . esc_html( findewerkstatt_t( 'Der Betrieb hat noch keine Beschreibung hinterlegt.' ) ) . '</p>'; } ?></div>
            </section>

            <!-- Services Section -->
            <?php if ( $services && ! is_wp_error( $services ) ) : ?>
                <section class="fw-box fw-profile-services">
                    <h2>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span><?php echo esc_html( findewerkstatt_t( 'Leistungen' ) ); ?></span>
                    </h2>
                    <div class="fw-services-list">
                        <?php foreach ( $services as $service ) : ?>
                            <a class="fw-service-item" href="<?php echo esc_url( get_term_link( $service ) ); ?>">
                                <span class="fw-service-check" aria-hidden="true">✓</span>
                                <span><?php echo esc_html( findewerkstatt_t( $service->name ) ); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Vehicle Brands Section -->
            <?php if ( $brands && ! is_wp_error( $brands ) ) : ?>
                <section class="fw-box fw-profile-brands">
                    <h2>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"></path><circle cx="7" cy="17" r="2"></circle><circle cx="17" cy="17" r="2"></circle></svg>
                        <span><?php echo esc_html( findewerkstatt_t( 'Fahrzeugmarken' ) ); ?></span>
                    </h2>
                    <div class="fw-card-services">
                        <?php foreach ( $brands as $brand ) : ?>
                            <a class="fw-badge fw-badge-service fw-brand-chip" href="<?php echo esc_url( get_term_link( $brand ) ); ?>">
                                <?php echo findewerkstatt_icon( 'car', 12 ); ?>
                                <span><?php echo esc_html( $brand->name ); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Spoken Languages -->
            <?php if ( $spoken_languages ) : ?>
                <section class="fw-box fw-profile-languages">
                    <h2>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                        <span><?php echo esc_html( findewerkstatt_t( 'Gesprochene Sprachen' ) ); ?></span>
                    </h2>
                    <p class="fw-muted"><?php echo esc_html( findewerkstatt_t( 'Vom Betrieb angegebene Sprachen. Bitte bestätigen Sie die Verfügbarkeit direkt beim Betrieb.' ) ); ?></p>
                    <div class="fw-card-services">
                        <?php foreach ( $spoken_languages as $language_label ) : ?>
                            <span class="fw-badge fw-badge-service fw-language-chip"><?php echo findewerkstatt_icon( 'chat', 12 ); ?> <span><?php echo esc_html( $language_label ); ?></span></span>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Ad Zone 2: In-Content Video / Sponsor Unit (Google AdSense / Direct Sponsor) -->
            <?php if ( function_exists( 'findewerkstatt_render_ad' ) ) { echo findewerkstatt_render_ad( 'incontent_video' ); } ?>

            <!-- FAQ Section -->
            <section class="fw-box fw-profile-faq">
                <h2>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    <span><?php echo esc_html( findewerkstatt_t( 'Häufige Fragen' ) ); ?></span>
                </h2>
                <div class="fw-faq-item"><button type="button" class="fw-faq-question"><span><?php echo esc_html( findewerkstatt_t( 'Wie vereinbare ich einen Termin?' ) ); ?></span><span class="fw-faq-icon" aria-hidden="true">+</span></button><div class="fw-faq-answer"><p><?php echo esc_html( findewerkstatt_t( 'Nutzen Sie die im Profil angegebenen Kontaktmöglichkeiten. Termine und die Verfügbarkeit besprechen Sie direkt mit dem Betrieb.' ) ); ?></p></div></div>
                <div class="fw-faq-item"><button type="button" class="fw-faq-question"><span><?php echo esc_html( findewerkstatt_t( 'Wie erhalte ich einen Kostenvoranschlag?' ) ); ?></span><span class="fw-faq-icon" aria-hidden="true">+</span></button><div class="fw-faq-answer"><p><?php echo esc_html( findewerkstatt_t( 'Nennen Sie dem Betrieb Ihre Fahrzeugmarke, das Modell und Ihr Anliegen. Fragen Sie vor der Beauftragung nach dem Leistungsumfang und den voraussichtlichen Kosten.' ) ); ?></p></div></div>
                <div class="fw-faq-item"><button type="button" class="fw-faq-question"><span><?php echo esc_html( findewerkstatt_t( 'Welche Zahlungsmöglichkeiten gibt es?' ) ); ?></span><span class="fw-faq-icon" aria-hidden="true">+</span></button><div class="fw-faq-answer"><p><?php echo esc_html( findewerkstatt_t( 'Erkundigen Sie sich direkt beim Betrieb nach den akzeptierten Zahlungsmöglichkeiten.' ) ); ?></p></div></div>
            </section>

            <!-- Reviews Section -->
            <?php if ( $rating['rating'] !== null && $rating['count'] > 0 ) : ?>
                <section class="fw-box">
                    <h2><?php echo esc_html( findewerkstatt_t( 'Bewertung des Betriebs' ) ); ?></h2>
                    <?php echo findewerkstatt_render_stars( $rating['rating'], $rating['count'] ); ?>
                    <p><?php echo esc_html( findewerkstatt_t( 'Bewertungen dienen der Orientierung. Leistungen und Kosten besprechen Sie bitte direkt mit dem Betrieb.' ) ); ?></p>
                </section>
            <?php endif; ?>

            <!-- PRIMARY CONTACT & BOOKING HUB (Moved below content and ads) -->
            <section id="fw-kontakt-termin" class="fw-box fw-profile-contact-hub" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Kontakt und Anfahrt' ) ); ?>">
                <div class="fw-contact-hub-header">
                    <div class="fw-contact-hub-title-block">
                        <h2>
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                            <span><?php echo esc_html( findewerkstatt_t( 'Kontakt und Anfahrt' ) ); ?></span>
                        </h2>
                        <p class="fw-muted"><?php echo esc_html( findewerkstatt_t( 'Nutzen Sie die angegebenen Kontaktdaten für Anfragen, Termine und Preisinformationen.' ) ); ?></p>
                    </div>
                    <?php $hub_status = findewerkstatt_get_open_status( $id, false ); ?>
                    <?php if ( $hub_status ) : ?>
                        <div class="fw-contact-hub-status"><?php echo $hub_status; ?></div>
                    <?php endif; ?>
                </div>

                <!-- High-Impact Direct Action Buttons -->
                <div class="fw-contact-hub-actions">
                    <?php if ( $phone ) : ?>
                        <a class="fw-btn fw-btn-phone fw-btn-contact-main" href="tel:<?php echo esc_attr( $phone ); ?>">
                            <?php echo findewerkstatt_icon( 'phone', 20 ); ?>
                            <span><?php echo esc_html( findewerkstatt_t( 'Jetzt anrufen' ) ); ?> (<?php echo esc_html( $phone ); ?>)</span>
                        </a>
                    <?php endif; ?>
                    <?php if ( $whatsapp_url ) : ?>
                        <a class="fw-btn fw-btn-whatsapp fw-btn-contact-main" href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer">
                            <?php echo findewerkstatt_icon( 'whatsapp', 20 ); ?>
                            <span><?php echo esc_html( findewerkstatt_t( 'Per WhatsApp anfragen' ) ); ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if ( $maps_url ) : ?>
                        <a class="fw-btn fw-btn-outline fw-btn-contact-main" href="<?php echo esc_url( $maps_url ); ?>" target="_blank" rel="noopener noreferrer">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="3 11 22 2 13 21 11 13 3 11"></polygon></svg>
                            <span><?php echo esc_html( findewerkstatt_t( 'Route planen' ) ); ?></span>
                        </a>
                    <?php endif; ?>
                </div>

                <div class="fw-contact-hub-grid">
                    <div class="fw-contact-hub-details">
                        <div class="fw-contact-info-list">
                            <div class="fw-contact-info-row">
                                <div class="fw-contact-info-icon" aria-hidden="true">
                                    <?php echo findewerkstatt_icon( 'pin', 18 ); ?>
                                </div>
                                <div class="fw-contact-info-content">
                                    <span class="fw-contact-label"><?php echo esc_html( findewerkstatt_t( 'Adresse' ) ); ?></span>
                                    <span class="fw-contact-value"><?php echo esc_html( $clean_full_address ?: findewerkstatt_t( 'Nicht angegeben' ) ); ?></span>
                                </div>
                            </div>
                            <?php if ( $phone ) : ?>
                                <div class="fw-contact-info-row">
                                    <div class="fw-contact-info-icon" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                    </div>
                                    <div class="fw-contact-info-content">
                                        <span class="fw-contact-label"><?php echo esc_html( findewerkstatt_t( 'Telefon' ) ); ?></span>
                                        <a class="fw-contact-value fw-contact-link" href="tel:<?php echo esc_attr( $phone ); ?>"><?php echo esc_html( $phone ); ?></a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ( $email && is_email( $email ) ) : ?>
                                <div class="fw-contact-info-row">
                                    <div class="fw-contact-info-icon" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                    </div>
                                    <div class="fw-contact-info-content">
                                        <span class="fw-contact-label"><?php echo esc_html( findewerkstatt_t( 'E-Mail' ) ); ?></span>
                                        <a class="fw-contact-value fw-contact-link" href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ( $website ) : ?>
                                <div class="fw-contact-info-row">
                                    <div class="fw-contact-info-icon" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                                    </div>
                                    <div class="fw-contact-info-content">
                                        <span class="fw-contact-label"><?php echo esc_html( findewerkstatt_t( 'Website' ) ); ?></span>
                                        <a class="fw-contact-value fw-contact-link" href="<?php echo esc_url( $website ); ?>" target="_blank" rel="nofollow noopener noreferrer"><?php echo esc_html( wp_parse_url( $website, PHP_URL_HOST ) ); ?> ↗</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="fw-contact-hub-hours">
                        <div class="fw-hours-section">
                            <h3>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                <span><?php echo esc_html( findewerkstatt_t( 'Öffnungszeiten' ) ); ?></span>
                            </h3>
                            <table class="fw-hours-table">
                                <tbody>
                                    <?php foreach ( array( 'weekday' => 'Montag–Freitag', 'saturday' => 'Samstag', 'sunday' => 'Sonntag' ) as $key => $label ) :
                                        $val = get_post_meta( $id, '_mechanic_hours_' . $key, true ); ?>
                                        <tr>
                                            <th scope="row"><?php echo esc_html( findewerkstatt_t( $label ) ); ?></th>
                                            <td class="<?php echo $val ? 'fw-hours-set' : 'fw-hours-unset'; ?>"><?php echo esc_html( $val ?: findewerkstatt_t( 'Nicht angegeben' ) ); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <p class="fw-muted fw-hours-note"><?php echo esc_html( findewerkstatt_t( 'An Feiertagen können die Öffnungszeiten abweichen. Bitte fragen Sie bei Bedarf direkt nach.' ) ); ?></p>
                        </div>
                    </div>
                </div>

                <div class="fw-contact-footer">
                    <p class="fw-muted"><?php echo esc_html( findewerkstatt_t( 'Bitte bestätigen Sie Leistungen, Kontaktdaten und Öffnungszeiten direkt beim Betrieb.' ) ); ?> <a class="fw-report-link" href="<?php echo esc_url( findewerkstatt_page_url( 'kontakt' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Fehler melden' ) ); ?></a></p>
                </div>
            </section>
        </div>

        <!-- Sticky Sidebar with High CTR Ad Unit & Quick Navigation -->
        <aside class="fw-sidebar" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Seitenleiste' ) ); ?>">
            <!-- Sticky Sidebar Ad Unit (Google AdSense / Direct Sponsor) -->
            <?php if ( function_exists( 'findewerkstatt_render_ad' ) ) { echo findewerkstatt_render_ad( 'sidebar_sticky' ); } ?>

            <!-- Quick Jump to Contact card in sidebar -->
            <div class="fw-sidebar-contact-card fw-box">
                <h3><?php echo esc_html( findewerkstatt_t( 'Kontakt & Termine' ) ); ?></h3>
                <p class="fw-sidebar-contact-desc"><?php echo esc_html( findewerkstatt_t( 'Alle Kontaktdaten, Rufnummern und Öffnungszeiten finden Sie am Ende des Profils.' ) ); ?></p>
                <a href="#fw-kontakt-termin" class="fw-btn fw-btn-primary fw-btn-block">
                    <span><?php echo esc_html( findewerkstatt_t( 'Zu den Kontaktdaten' ) ); ?> &darr;</span>
                </a>
            </div>

            <?php if ( ! get_post_meta( $id, '_fw_owner_user_id', true ) ) : ?>
                <div class="fw-claim-banner">
                    <div class="fw-claim-banner-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    </div>
                    <div class="fw-claim-banner-text">
                        <h4><?php echo esc_html( findewerkstatt_t( 'Gehört Ihnen dieser Betrieb?' ) ); ?></h4>
                        <p><?php echo esc_html( findewerkstatt_t( 'Übernehmen Sie jetzt die Verwaltung, um Öffnungszeiten, Bilder und Kontaktdaten anzupassen.' ) ); ?></p>
                        <a class="fw-btn fw-btn-outline fw-btn-sm" href="<?php echo esc_url( add_query_arg( 'uebernehmen', $id, findewerkstatt_page_url( 'mein-konto' ) ) . '#eintrag-uebernehmen' ); ?>">
                            <span><?php echo esc_html( findewerkstatt_t( 'Eintrag übernehmen' ) ); ?> &rarr;</span>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</main>
<?php endwhile; get_footer(); ?>
