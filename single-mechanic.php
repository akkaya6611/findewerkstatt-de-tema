<?php
/** Workshop profile with ad-friendly layout and below-the-fold contact conversion. */
get_header();
while ( have_posts() ) : the_post();
$id = get_the_ID();
$title = get_the_title();
$phone = findewerkstatt_normalize_phone( get_post_meta( $id, '_mechanic_phone', true ) );
$whatsapp_url = findewerkstatt_get_whatsapp_url( get_post_meta( $id, '_mechanic_whatsapp', true ), $title, $phone );
$email = get_post_meta( $id, '_mechanic_email_public', true ) === 'yes' ? get_post_meta( $id, '_mechanic_email', true ) : '';
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
$single_real_photo = get_post_meta( $id, '_mechanic_is_real_photo', true ) === 'yes' ? get_post_meta( $id, '_mechanic_google_photo_url', true ) : '';

// Cover and Avatar logic (Facebook Style)
$has_real_cover = false;
if ( $single_real_photo ) {
    $cover_photo_url = $single_real_photo;
    $has_real_cover  = true;
} elseif ( $profile_image_id ) {
    $cover_photo_url = wp_get_attachment_image_url( $profile_image_id, 'full' );
    $has_real_cover  = true;
} else {
    $cover_photo_url = get_template_directory_uri() . '/assets/images/banners/banner-werkstatt-diagnostic.jpg';
}

$avatar_url = '';
if ( $profile_image_id ) {
    $avatar_url = wp_get_attachment_image_url( $profile_image_id, 'medium' );
} elseif ( $single_real_photo ) {
    $avatar_url = $single_real_photo;
}

// Monogram Initials
$clean_title = trim( wp_strip_all_tags( $title ) );
$title_parts = preg_split( '/[\s\-_]+/', $clean_title );
if ( count( $title_parts ) >= 2 ) {
    $initials = mb_substr( $title_parts[0], 0, 1, 'UTF-8' ) . mb_substr( $title_parts[1], 0, 1, 'UTF-8' );
} elseif ( mb_strlen( $clean_title ) >= 2 ) {
    $initials = mb_substr( $clean_title, 0, 2, 'UTF-8' );
} else {
    $initials = 'FW';
}
$initials = mb_strtoupper( $initials, 'UTF-8' );

$primary_category_name = ( $services && ! is_wp_error( $services ) && ! empty( $services[0] ) ) ? findewerkstatt_t( $services[0]->name ) : findewerkstatt_t( 'Kfz-Werkstatt & Autoservice' );
$verification_info = findewerkstatt_get_verification_level( $id );
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

    <!-- Facebook-Style Profile Header Card -->
    <div class="fw-fb-profile-card fw-single-hero">
        <!-- Cover Banner -->
        <div class="fw-fb-cover">
            <img class="fw-fb-cover-img" src="<?php echo esc_url( $cover_photo_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="eager" decoding="async">
            <div class="fw-fb-cover-overlay"></div>
            <?php if ( $has_real_cover ) : ?>
                <span class="fw-fb-cover-badge">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                    <span><?php echo esc_html( findewerkstatt_t( 'Werkstatt-Foto' ) ); ?></span>
                </span>
            <?php endif; ?>
        </div>

        <!-- Profile Bar (Avatar + Info + Action Buttons) -->
        <div class="fw-fb-bar">
            <div class="fw-fb-bar-main">
                <div class="fw-fb-avatar-and-info">
                    <!-- Overlapping Avatar -->
                    <div class="fw-fb-avatar-wrap">
                        <div class="fw-fb-avatar">
                            <?php if ( $avatar_url ) : ?>
                                <img src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="fw-fb-avatar-img" loading="eager">
                            <?php else : ?>
                                <div class="fw-fb-avatar-initials">
                                    <span><?php echo esc_html( $initials ); ?></span>
                                    <span class="fw-fb-avatar-icon" aria-hidden="true">🔧</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if ( 'verified' === $verification_info['level'] ) : ?>
                            <span class="fw-fb-avatar-badge is-partner" title="<?php echo esc_attr( findewerkstatt_t( 'Geprüfter Partner' ) ); ?>" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Geprüfter Partner' ) ); ?>">✓</span>
                        <?php elseif ( 'checked' === $verification_info['level'] ) : ?>
                            <span class="fw-fb-avatar-badge is-checked" title="<?php echo esc_attr( findewerkstatt_t( 'Daten geprüft' ) ); ?>" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Daten geprüft' ) ); ?>">✓</span>
                        <?php endif; ?>
                    </div>

                    <!-- Profile Info -->
                    <div class="fw-fb-info">
                        <div class="fw-fb-title-row">
                            <h1 class="fw-fb-title"><?php echo esc_html( $title ); ?></h1>
                            <div class="fw-fb-badges">
                                <?php echo findewerkstatt_render_badges( $id ); ?>
                                <?php echo findewerkstatt_get_open_status( $id, false ); ?>
                            </div>
                        </div>

                        <p class="fw-fb-tagline">
                            <span class="fw-fb-category"><?php echo esc_html( $primary_category_name ); ?></span>
                            <?php if ( $city_name ) : ?>
                                <span class="fw-fb-bullet">&bull;</span>
                                <span class="fw-fb-location"><?php echo esc_html( $city_name ); ?></span>
                            <?php endif; ?>
                        </p>

                        <div class="fw-fb-meta-pills">
                            <?php if ( ! empty( $rating['rating'] ) ) : ?>
                                <div class="fw-fb-pill fw-fb-pill-google" title="<?php echo esc_attr( sprintf( findewerkstatt_t( 'Google-Bewertung: %s von 5 Sternen' ), $rating['rating'] ) ); ?>">
                                    <span class="fw-google-badge">Google</span>
                                    <?php echo findewerkstatt_render_stars( $rating['rating'], $rating['count'] ); ?>
                                </div>
                            <?php endif; ?>

                            <div class="fw-fb-pill fw-fb-pill-address">
                                <?php echo findewerkstatt_icon( 'pin', 14 ); ?>
                                <span><?php echo esc_html( $clean_full_address ?: findewerkstatt_t( 'Standort nicht angegeben' ) ); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Facebook Profile Actions -->
                <div class="fw-fb-actions">
                    <a class="fw-btn fw-btn-accent fw-fb-action-btn" href="#fw-inquiry-box" data-event="offer_request">
                        <span>⚡</span>
                        <span><?php echo esc_html( findewerkstatt_t( 'Angebot anfragen' ) ); ?></span>
                    </a>
                    <a class="fw-btn fw-btn-outline fw-fb-action-btn" href="#fw-kontakt-termin" data-event="scroll_to_contact">
                        <?php echo findewerkstatt_icon( 'phone', 15 ); ?>
                        <span><?php echo esc_html( findewerkstatt_t( 'Kontakt & Zeiten' ) ); ?> &darr;</span>
                    </a>
                    <a class="fw-btn fw-btn-subtle fw-fb-action-btn" href="#fw-profile-similar" data-event="scroll_to_similar" title="<?php echo esc_attr( findewerkstatt_t( 'Vergleichen' ) ); ?>">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <span><?php echo esc_html( findewerkstatt_t( 'Vergleichen' ) ); ?> &darr;</span>
                    </a>
                    <button type="button" class="fw-btn fw-btn-outline fw-fb-action-btn fw-card-bookmark-btn" data-id="<?php echo esc_attr( $id ); ?>" title="<?php echo esc_attr( findewerkstatt_t( 'Merken' ) ); ?>">
                        <span class="fw-bookmark-icon" aria-hidden="true">🔖</span>
                        <span><?php echo esc_html( findewerkstatt_t( 'Merken' ) ); ?></span>
                    </button>
                </div>
            </div>

            <!-- Facebook Navigation Tabs -->
            <nav class="fw-fb-tabs" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Profil-Navigation' ) ); ?>">
                <a href="#fw-fb-about" class="fw-fb-tab is-active"><?php echo esc_html( findewerkstatt_t( 'Übersicht' ) ); ?></a>
                <a href="#fw-fb-services" class="fw-fb-tab"><?php echo esc_html( findewerkstatt_t( 'Leistungen' ) ); ?></a>
                <?php if ( $brands && ! is_wp_error( $brands ) ) : ?>
                    <a href="#fw-fb-brands" class="fw-fb-tab"><?php echo esc_html( findewerkstatt_t( 'Automarken' ) ); ?></a>
                <?php endif; ?>
                <a href="#fw-inquiry-box" class="fw-fb-tab fw-fb-tab-highlight">⚡ <?php echo esc_html( findewerkstatt_t( 'Angebot anfragen' ) ); ?></a>
                <a href="#fw-fb-reviews" class="fw-fb-tab"><?php echo esc_html( findewerkstatt_t( 'Bewertungen' ) ); ?></a>
                <a href="#fw-kontakt-termin" class="fw-fb-tab"><?php echo esc_html( findewerkstatt_t( 'Kontakt & Anfahrt' ) ); ?></a>
            </nav>
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
            <section id="fw-fb-about" class="fw-box fw-profile-about">
                <h2>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <span><?php echo esc_html( findewerkstatt_t( 'Über den Betrieb' ) ); ?></span>
                </h2>
                <div class="fw-prose"><?php if ( trim( get_the_content() ) ) { the_content(); } else { echo '<p>' . esc_html( findewerkstatt_t( 'Zu diesem Unternehmen liegt derzeit noch keine ausführliche Beschreibung vor.' ) ) . '</p>'; } ?></div>
            </section>

            <!-- Services Section -->
            <?php if ( $services && ! is_wp_error( $services ) ) : ?>
                <section id="fw-fb-services" class="fw-box fw-profile-services">
                    <div class="fw-section-header-row" style="display:flex; justify-content:space-between; align-items:baseline; flex-wrap:wrap; gap:8px; margin-bottom:12px;">
                        <h2>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span><?php echo esc_html( findewerkstatt_t( 'Angebotene Leistungen' ) ); ?></span>
                        </h2>
                        <?php if ( $city ) : ?>
                            <a class="fw-section-sublink" href="<?php echo esc_url( get_term_link( $city ) ); ?>" style="font-size:13px; font-weight:600; color:var(--fw-accent);">
                                <?php echo esc_html( sprintf( findewerkstatt_t( 'Alle Werkstätten in %s anzeigen' ), $city_name ) ); ?> &rarr;
                            </a>
                        <?php endif; ?>
                    </div>
                    <p class="fw-muted" style="margin-top:-6px; margin-bottom:14px; font-size:13px;">
                        <?php echo esc_html( findewerkstatt_t( 'Klicken Sie auf eine Leistung, um spezialisierte Werkstätten in dieser Region zu vergleichen.' ) ); ?>
                    </p>
                    <div class="fw-services-list">
                        <?php foreach ( $services as $service ) :
                            $service_url = ( $city && class_exists( 'findewerkstatt_PSEO' ) )
                                ? findewerkstatt_PSEO::get_combination_url( $service, $city )
                                : get_term_link( $service );
                        ?>
                            <a class="fw-service-item" href="<?php echo esc_url( $service_url ); ?>" title="<?php echo esc_attr( sprintf( findewerkstatt_t( '%s in %s suchen' ), $service->name, $city_name ?: 'Deutschland' ) ); ?>">
                                <span class="fw-service-check" aria-hidden="true">✓</span>
                                <span><?php echo esc_html( findewerkstatt_t( $service->name ) ); ?></span>
                                <?php if ( $city_name ) : ?>
                                    <span style="font-size:11px; opacity:0.65; margin-left:auto; padding-left:6px;"><?php echo esc_html( $city_name ); ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Vehicle Brands Section -->
            <?php if ( $brands && ! is_wp_error( $brands ) ) : ?>
                <section id="fw-fb-brands" class="fw-box fw-profile-brands">
                    <div class="fw-section-header-row" style="display:flex; justify-content:space-between; align-items:baseline; flex-wrap:wrap; gap:8px; margin-bottom:12px;">
                        <h2>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"></path><circle cx="7" cy="17" r="2"></circle><circle cx="17" cy="17" r="2"></circle></svg>
                            <span><?php echo esc_html( findewerkstatt_t( 'Fahrzeugmarken' ) ); ?></span>
                        </h2>
                    </div>
                    <p class="fw-muted" style="margin-top:-6px; margin-bottom:14px; font-size:13px;">
                        <?php echo esc_html( findewerkstatt_t( 'Betreute Automarken dieses Betriebs. Klicken Sie auf eine Marke für weitere Werkstätten.' ) ); ?>
                    </p>
                    <div class="fw-card-services">
                        <?php foreach ( $brands as $brand ) :
                            $brand_url = ( $city && class_exists( 'findewerkstatt_PSEO' ) )
                                ? findewerkstatt_PSEO::get_combination_url( $brand, $city )
                                : get_term_link( $brand );
                        ?>
                            <a class="fw-badge fw-badge-service fw-brand-chip" href="<?php echo esc_url( $brand_url ); ?>" title="<?php echo esc_attr( sprintf( findewerkstatt_t( '%s Werkstatt in %s finden' ), $brand->name, $city_name ?: 'Deutschland' ) ); ?>">
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

            <!-- Reviews Anchor -->
            <div id="fw-fb-reviews">
                <!-- Google Reviews Section -->
                <?php if ( ! empty( $rating['rating'] ) ) : ?>
                    <section class="fw-box fw-profile-google-reviews">
                        <div class="fw-google-reviews-header">
                            <span class="fw-google-badge-large">Google</span>
                            <h2><?php echo esc_html( findewerkstatt_t( 'Google Bewertung' ) ); ?></h2>
                        </div>
                        <div class="fw-google-reviews-body">
                            <div class="fw-google-rating-score"><?php echo esc_html( number_format_i18n( (float) $rating['rating'], 1 ) ); ?> <span class="fw-google-rating-max">/ 5</span></div>
                            <div class="fw-google-rating-stars"><?php echo findewerkstatt_render_stars( $rating['rating'] ); ?></div>
                            <div class="fw-google-rating-count"><?php echo esc_html( sprintf( findewerkstatt_t( '%s Bewertungen' ), number_format_i18n( (int) $rating['count'] ) ) ); ?></div>
                        </div>
                        <p class="fw-muted fw-google-disclaimer"><?php echo esc_html( findewerkstatt_t( 'Öffentliche Bewertungen von Google Nutzern.' ) ); ?></p>
                    </section>
                <?php endif; ?>

                <!-- Reviews & Customer Feedback Section -->
                <?php if ( function_exists( 'findewerkstatt_render_workshop_reviews' ) ) : ?>
                    <?php findewerkstatt_render_workshop_reviews( $id ); ?>
                <?php endif; ?>
            </div>

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
                        <a class="fw-btn fw-btn-phone fw-btn-contact-main" href="tel:<?php echo esc_attr( $phone ); ?>" data-event="phone_click">
                            <?php echo findewerkstatt_icon( 'phone', 20 ); ?>
                            <span><?php echo esc_html( findewerkstatt_t( 'Jetzt anrufen' ) ); ?> (<?php echo esc_html( $phone ); ?>)</span>
                        </a>
                    <?php endif; ?>
                    <?php if ( $whatsapp_url ) : ?>
                        <a class="fw-btn fw-btn-whatsapp fw-btn-contact-main" href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer" data-event="whatsapp_click">
                            <?php echo findewerkstatt_icon( 'whatsapp', 20 ); ?>
                            <span><?php echo esc_html( findewerkstatt_t( 'Per WhatsApp anfragen' ) ); ?></span>
                        </a>
                    <?php endif; ?>
                    <?php if ( $maps_url ) : ?>
                        <a class="fw-btn fw-btn-outline fw-btn-contact-main" href="<?php echo esc_url( $maps_url ); ?>" target="_blank" rel="noopener noreferrer" data-event="route_click">
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
                                            <td class="<?php echo $val ? 'fw-hours-set' : 'fw-hours-unset'; ?>"><?php echo esc_html( findewerkstatt_format_display_hours( $val ) ); ?></td>
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

            <!-- Quick Quote & Appointment Inquiry Box -->
            <?php if ( function_exists( 'findewerkstatt_render_quote_inquiry_form' ) ) : ?>
                <?php findewerkstatt_render_quote_inquiry_form( $id ); ?>
            <?php endif; ?>

            <!-- Similar and Nearby Workshops -->
            <?php
            $similar_args = array(
                'post_type'      => 'mechanic',
                'posts_per_page' => 3,
                'post__not_in'   => array( $id ),
                'orderby'        => 'rand',
            );
            if ( $city ) {
                $similar_args['tax_query'] = array(
                    array(
                        'taxonomy' => 'mechanic_city',
                        'field'    => 'term_id',
                        'terms'    => $city->term_id,
                    ),
                );
            }
            $similar_query = new WP_Query( $similar_args );

            // Fallback to state if fewer than 2 workshops found in current city
            if ( $similar_query->post_count < 2 && $city_context && ! empty( $city_context['state'] ) ) {
                $state_args = array(
                    'post_type'      => 'mechanic',
                    'posts_per_page' => 3,
                    'post__not_in'   => array( $id ),
                    'orderby'        => 'rand',
                    'tax_query'      => array(
                        array(
                            'taxonomy' => 'mechanic_city',
                            'field'    => 'term_id',
                            'terms'    => $city_context['state']->term_id,
                        ),
                    ),
                );
                $similar_query = new WP_Query( $state_args );
            }

            if ( $similar_query->have_posts() ) : ?>
                <section id="fw-profile-similar" class="fw-box fw-profile-similar">
                    <div class="fw-section-header-row" style="display:flex; justify-content:space-between; align-items:baseline; flex-wrap:wrap; gap:8px; margin-bottom:16px;">
                        <h2>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            <span><?php echo esc_html( sprintf( findewerkstatt_t( 'Ähnliche Werkstätten in %s' ), $city_name ?: findewerkstatt_t( 'der Region' ) ) ); ?></span>
                        </h2>
                        <?php if ( $city ) : ?>
                            <a class="fw-section-sublink" href="<?php echo esc_url( get_term_link( $city ) ); ?>" style="font-size:13px; font-weight:600; color:var(--fw-accent);">
                                <?php echo esc_html( sprintf( findewerkstatt_t( 'Alle Werkstätten in %s vergleichen' ), $city_name ) ); ?> &rarr;
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="fw-similar-cards-grid">
                        <?php while ( $similar_query->have_posts() ) : $similar_query->the_post();
                            get_template_part( 'template-parts/workshop-card' );
                        endwhile; wp_reset_postdata(); ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Popular Services Discovery Hub in City (Boosts Site Navigation & Pageviews) -->
            <?php if ( $city ) :
                $popular_service_slugs = array( 'inspektion', 'hauptuntersuchung-tuev', 'bremsenservice', 'reifenwechsel', 'oelwechsel', 'klimaservice', 'unfallinstandsetzung', 'autoglas' );
            ?>
                <section class="fw-box fw-profile-discovery">
                    <h2>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <span><?php echo esc_html( sprintf( findewerkstatt_t( 'Beliebte Kfz-Services in %s' ), $city_name ) ); ?></span>
                    </h2>
                    <p class="fw-muted" style="margin-top:-6px; margin-bottom:14px; font-size:13px;">
                        <?php echo esc_html( findewerkstatt_t( 'Finden und vergleichen Sie geprüfte Werkstätten nach Fachbereich in Ihrer Nähe:' ) ); ?>
                    </p>
                    <div class="fw-discovery-chips" style="display:flex; flex-wrap:wrap; gap:8px;">
                        <?php foreach ( $popular_service_slugs as $ps_slug ) :
                            $sterm = get_term_by( 'slug', $ps_slug, 'service_type' );
                            if ( ! $sterm || is_wp_error( $sterm ) ) continue;
                            $ps_url = class_exists( 'findewerkstatt_PSEO' ) ? findewerkstatt_PSEO::get_combination_url( $sterm, $city ) : get_term_link( $sterm );
                        ?>
                            <a class="fw-badge fw-badge-service fw-discovery-chip" href="<?php echo esc_url( $ps_url ); ?>" style="padding:7px 12px; font-size:13px; text-decoration:none;">
                                <span style="color:var(--fw-accent); font-weight:700;">&bull;</span>
                                <span><?php echo esc_html( findewerkstatt_t( $sterm->name ) ); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Recently Viewed Workshops (Loaded dynamically from localStorage) -->
            <section id="fw-recently-viewed-box" class="fw-box fw-profile-recent" style="display:none;" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Zuletzt angesehen' ) ); ?>">
                <div class="fw-section-header-row" style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:14px;">
                    <h2>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
                        <span><?php echo esc_html( findewerkstatt_t( 'Zuletzt angesehen' ) ); ?></span>
                    </h2>
                </div>
                <div id="fw-recently-viewed-list" class="fw-recent-list" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:12px;"></div>
            </section>

            <!-- Internal Linking Section -->
            <section class="fw-box fw-internal-links-box">
                <h2><?php echo esc_html( findewerkstatt_t( 'Weitere Angebote & Standorte' ) ); ?></h2>
                <div class="fw-internal-links-grid">
                    <?php if ( $city ) : ?>
                        <div class="fw-internal-link-col">
                            <h4><?php echo esc_html( sprintf( findewerkstatt_t( 'Weitere Werkstätten in %s' ), $city_name ) ); ?></h4>
                            <ul>
                                <li><a href="<?php echo esc_url( get_term_link( $city ) ); ?>"><?php echo esc_html( sprintf( findewerkstatt_t( 'Alle Werkstätten in %s anzeigen' ), $city_name ) ); ?> &rarr;</a></li>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <?php if ( $city_context && ! empty( $city_context['state'] ) ) : ?>
                        <div class="fw-internal-link-col">
                            <h4><?php echo esc_html( sprintf( findewerkstatt_t( 'Kfz-Werkstätten in %s' ), $city_context['state_name'] ) ); ?></h4>
                            <ul>
                                <li><a href="<?php echo esc_url( get_term_link( $city_context['state'] ) ); ?>"><?php echo esc_html( sprintf( findewerkstatt_t( 'Werkstätten in %s durchsuchen' ), $city_context['state_name'] ) ); ?> &rarr;</a></li>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <?php if ( $services && ! is_wp_error( $services ) ) : ?>
                        <div class="fw-internal-link-col">
                            <h4><?php echo esc_html( sprintf( findewerkstatt_t( 'Beliebte Leistungen in %s' ), $city_name ?: findewerkstatt_t( 'Ihrer Region' ) ) ); ?></h4>
                            <ul>
                                <?php $count_svc = 0; foreach ( $services as $s ) : if ( ++$count_svc > 4 ) break; ?>
                                    <li><a href="<?php echo esc_url( get_term_link( $s ) ); ?>"><?php echo esc_html( findewerkstatt_t( $s->name ) ); ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <!-- Sticky Sidebar with Facebook-Style Steckbrief & Ad Unit -->
        <aside class="fw-sidebar" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Seitenleiste' ) ); ?>">
            <!-- Facebook-Style Steckbrief (Business Info Card) -->
            <div class="fw-fb-steckbrief fw-box">
                <div class="fw-fb-steckbrief-header">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <h3><?php echo esc_html( findewerkstatt_t( 'Steckbrief' ) ); ?></h3>
                </div>
                <div class="fw-fb-steckbrief-list">
                    <div class="fw-fb-steckbrief-item">
                        <span class="fw-fb-steckbrief-icon" aria-hidden="true">🏢</span>
                        <div class="fw-fb-steckbrief-detail">
                            <span class="fw-fb-steckbrief-label"><?php echo esc_html( findewerkstatt_t( 'Branche' ) ); ?></span>
                            <span class="fw-fb-steckbrief-val"><?php echo esc_html( $primary_category_name ); ?></span>
                        </div>
                    </div>
                    <div class="fw-fb-steckbrief-item">
                        <span class="fw-fb-steckbrief-icon" aria-hidden="true">📍</span>
                        <div class="fw-fb-steckbrief-detail">
                            <span class="fw-fb-steckbrief-label"><?php echo esc_html( findewerkstatt_t( 'Adresse' ) ); ?></span>
                            <span class="fw-fb-steckbrief-val"><?php echo esc_html( $clean_full_address ?: findewerkstatt_t( 'Nicht angegeben' ) ); ?></span>
                        </div>
                    </div>
                    <div class="fw-fb-steckbrief-item">
                        <span class="fw-fb-steckbrief-icon" aria-hidden="true">🕒</span>
                        <div class="fw-fb-steckbrief-detail">
                            <span class="fw-fb-steckbrief-label"><?php echo esc_html( findewerkstatt_t( 'Status' ) ); ?></span>
                            <span class="fw-fb-steckbrief-val"><?php echo findewerkstatt_get_open_status( $id, false ); ?></span>
                        </div>
                    </div>
                    <?php if ( ! empty( $spoken_languages ) ) : ?>
                        <div class="fw-fb-steckbrief-item">
                            <span class="fw-fb-steckbrief-icon" aria-hidden="true">🗣️</span>
                            <div class="fw-fb-steckbrief-detail">
                                <span class="fw-fb-steckbrief-label"><?php echo esc_html( findewerkstatt_t( 'Gesprochene Sprachen' ) ); ?></span>
                                <span class="fw-fb-steckbrief-val"><?php echo esc_html( implode( ', ', $spoken_languages ) ); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="fw-fb-steckbrief-item">
                        <span class="fw-fb-steckbrief-icon" aria-hidden="true">🛡️</span>
                        <div class="fw-fb-steckbrief-detail">
                            <span class="fw-fb-steckbrief-label"><?php echo esc_html( findewerkstatt_t( 'Eintragsstatus' ) ); ?></span>
                            <span class="fw-fb-steckbrief-val"><?php echo esc_html( $verification_info['label'] ); ?></span>
                        </div>
                    </div>
                </div>
                <div class="fw-fb-steckbrief-footer">
                    <a href="#fw-kontakt-termin" class="fw-btn fw-btn-outline fw-btn-block fw-btn-sm">
                        <span><?php echo esc_html( findewerkstatt_t( 'Alle Kontaktdaten anzeigen' ) ); ?> &darr;</span>
                    </a>
                </div>
            </div>

            <!-- Sticky Sidebar Ad Unit (Google AdSense / Direct Sponsor) -->
            <?php if ( function_exists( 'findewerkstatt_render_ad' ) ) { echo findewerkstatt_render_ad( 'sidebar_sticky' ); } ?>

            <!-- Quick Jump to Contact & Inquiry card in sidebar -->
            <div class="fw-sidebar-contact-card fw-box">
                <h3><?php echo esc_html( findewerkstatt_t( 'Kontakt & Termine' ) ); ?></h3>
                <p class="fw-sidebar-contact-desc"><?php echo esc_html( findewerkstatt_t( 'Direkt anrufen, per WhatsApp anfragen oder unverbindlichen Kostenvoranschlag einholen.' ) ); ?></p>
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <a href="#fw-inquiry-box" class="fw-btn fw-btn-primary fw-btn-block">
                        <span><?php echo esc_html( findewerkstatt_t( 'Kostenvoranschlag anfragen' ) ); ?> &darr;</span>
                    </a>
                    <a href="#fw-kontakt-termin" class="fw-btn fw-btn-outline fw-btn-block">
                        <span><?php echo esc_html( findewerkstatt_t( 'Zu den Kontaktdaten' ) ); ?> &darr;</span>
                    </a>
                    <a href="#fw-profile-similar" class="fw-btn fw-btn-subtle fw-btn-block" style="text-align:center;">
                        <span><?php echo esc_html( findewerkstatt_t( 'Ähnliche Betriebe vergleichen' ) ); ?> &darr;</span>
                    </a>
                </div>
            </div>

            <?php if ( ! get_post_meta( $id, '_fw_owner_user_id', true ) ) : ?>
                <div class="fw-claim-banner">
                    <div class="fw-claim-banner-icon" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    </div>
                    <div class="fw-claim-banner-text">
                        <h4><?php echo esc_html( findewerkstatt_t( 'Ist das Ihr Unternehmen?' ) ); ?></h4>
                        <p><?php echo esc_html( findewerkstatt_t( 'Übernehmen Sie diesen Eintrag und bearbeiten Sie Öffnungszeiten, Leistungen und Kontaktdaten.' ) ); ?></p>
                        <a class="fw-btn fw-btn-outline fw-btn-sm" href="<?php echo esc_url( add_query_arg( 'uebernehmen', $id, findewerkstatt_page_url( 'mein-konto' ) ) . '#eintrag-uebernehmen' ); ?>" data-event="claim_business">
                            <span><?php echo esc_html( findewerkstatt_t( 'Eintrag beanspruchen' ) ); ?> &rarr;</span>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </aside>
    </div>

    <!-- Mobile Fixed Bottom CTA Bar (Optimized for Scroll & Dwell Time) -->
    <nav class="fw-mobile-sticky-bar" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Schnellkontakt' ) ); ?>">
        <div class="fw-mobile-sticky-inner">
            <a href="#fw-inquiry-box" class="fw-sticky-btn fw-sticky-quote" data-event="offer_request">
                <span class="fw-sticky-icon" aria-hidden="true">⚡</span>
                <span><?php echo esc_html( findewerkstatt_t( 'Angebot anfragen' ) ); ?></span>
            </a>
            <a href="#fw-kontakt-termin" class="fw-sticky-btn fw-sticky-phone" data-event="scroll_to_contact">
                <?php echo findewerkstatt_icon( 'phone', 16 ); ?>
                <span><?php echo esc_html( findewerkstatt_t( 'Kontakt & Zeiten' ) ); ?> &darr;</span>
            </a>
            <a href="#fw-profile-similar" class="fw-sticky-btn fw-sticky-compare" data-event="scroll_to_similar" style="background:#f1f5f9; color:var(--fw-text);">
                <span aria-hidden="true">🔍</span>
                <span><?php echo esc_html( findewerkstatt_t( 'Vergleichen' ) ); ?></span>
            </a>
        </div>
    </nav>
</main>
<?php endwhile; get_footer(); ?>
