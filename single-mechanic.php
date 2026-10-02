<?php
/** Workshop profile without invented reviews or business facts. */
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
?>
<main id="main-content" class="fw-container fw-page-content">
    <nav class="fw-breadcrumbs" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Brotkrümelnavigation' ) ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Startseite' ) ); ?></a><span aria-hidden="true">›</span><a href="<?php echo esc_url( get_post_type_archive_link( 'mechanic' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Werkstätten' ) ); ?></a><?php if ( $city ) : ?><span aria-hidden="true">›</span><a href="<?php echo esc_url( get_term_link( $city ) ); ?>"><?php echo esc_html( $city_name ); ?></a><?php endif; ?><span aria-hidden="true">›</span><span aria-current="page"><?php echo esc_html( $title ); ?></span></nav>
    <div class="fw-single-hero">
        <div class="fw-profile-badges"><?php echo findewerkstatt_render_badges( $id ); echo findewerkstatt_get_open_status( $id ); ?></div>
        <div class="fw-single-title-row"><div><h1><?php echo esc_html( $title ); ?></h1><?php echo findewerkstatt_render_stars( $rating['rating'], $rating['count'] ); ?><p class="fw-card-address"><span aria-hidden="true">📍</span><?php echo esc_html( trim( $address . ', ' . $plz . ' ' . $address_city, ', ' ) ?: findewerkstatt_t( 'Standort nicht angegeben' ) ); ?></p>
            <?php get_template_part( 'template-parts/workshop-location', null, array( 'context' => $city_context ) ); ?>
        </div><div class="fw-profile-actions">
            <?php if ( $phone ) : ?><a class="fw-btn fw-btn-phone" href="tel:<?php echo esc_attr( $phone ); ?>"><?php echo esc_html( findewerkstatt_t( 'Jetzt anrufen' ) ); ?></a><?php endif; ?>
            <?php if ( $whatsapp_url ) : ?><a class="fw-btn fw-btn-whatsapp" href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( findewerkstatt_t( 'Per WhatsApp anfragen' ) ); ?></a><?php endif; ?>
            <?php if ( $maps_url ) : ?><a class="fw-btn fw-btn-outline" href="<?php echo esc_url( $maps_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( findewerkstatt_t( 'Route planen' ) ); ?></a><?php endif; ?>
        </div></div>
    </div>
    <div class="fw-single-layout"><div>
        <figure class="fw-profile-image<?php echo $profile_image ? '' : ' fw-profile-image-symbol'; ?>">
            <?php if ( $profile_image ) : ?>
                <?php echo $profile_image; ?>
            <?php else : ?>
                <img class="fw-profile-image-img" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/placeholder-mechanic.webp' ); ?>" alt="<?php echo esc_attr( findewerkstatt_t( 'Symbolbild: Arbeiten an einem Motor' ) ); ?>" width="450" height="300" loading="eager" decoding="async">
                <figcaption><?php echo esc_html( findewerkstatt_t( 'Symbolbild' ) ); ?></figcaption>
            <?php endif; ?>
        </figure>
        <section class="fw-box"><h2><?php echo esc_html( findewerkstatt_t( 'Über den Betrieb' ) ); ?></h2><div class="fw-prose"><?php if ( trim( get_the_content() ) ) { the_content(); } else { echo '<p>' . esc_html( findewerkstatt_t( 'Der Betrieb hat noch keine Beschreibung hinterlegt.' ) ) . '</p>'; } ?></div></section>
        <?php if ( $services && ! is_wp_error( $services ) ) : ?><section class="fw-box"><h2><?php echo esc_html( findewerkstatt_t( 'Leistungen' ) ); ?></h2><div class="fw-services-list"><?php foreach ( $services as $service ) : ?><a class="fw-service-item" href="<?php echo esc_url( get_term_link( $service ) ); ?>"><span class="fw-service-check" aria-hidden="true">✓</span><?php echo esc_html( findewerkstatt_t( $service->name ) ); ?></a><?php endforeach; ?></div></section><?php endif; ?>
        <?php if ( $brands && ! is_wp_error( $brands ) ) : ?><section class="fw-box"><h2><?php echo esc_html( findewerkstatt_t( 'Fahrzeugmarken' ) ); ?></h2><div class="fw-card-services"><?php foreach ( $brands as $brand ) : ?><a class="fw-badge fw-badge-service" href="<?php echo esc_url( get_term_link( $brand ) ); ?>"><?php echo esc_html( $brand->name ); ?></a><?php endforeach; ?></div></section><?php endif; ?>
        <?php if ( $spoken_languages ) : ?><section class="fw-box"><h2><?php echo esc_html( findewerkstatt_t( 'Gesprochene Sprachen' ) ); ?></h2><p class="fw-muted"><?php echo esc_html( findewerkstatt_t( 'Vom Betrieb angegebene Sprachen. Bitte bestätigen Sie die Verfügbarkeit direkt beim Betrieb.' ) ); ?></p><div class="fw-card-services"><?php foreach ( $spoken_languages as $language_label ) : ?><span class="fw-badge fw-badge-service"><?php echo esc_html( $language_label ); ?></span><?php endforeach; ?></div></section><?php endif; ?>
        <section class="fw-box"><h2><?php echo esc_html( findewerkstatt_t( 'Häufige Fragen' ) ); ?></h2>
            <div class="fw-faq-item"><button type="button" class="fw-faq-question"><span><?php echo esc_html( findewerkstatt_t( 'Wie vereinbare ich einen Termin?' ) ); ?></span><span class="fw-faq-icon" aria-hidden="true">+</span></button><div class="fw-faq-answer"><p><?php echo esc_html( findewerkstatt_t( 'Nutzen Sie die im Profil angegebenen Kontaktmöglichkeiten. Termine und die Verfügbarkeit besprechen Sie direkt mit dem Betrieb.' ) ); ?></p></div></div>
            <div class="fw-faq-item"><button type="button" class="fw-faq-question"><span><?php echo esc_html( findewerkstatt_t( 'Wie erhalte ich einen Kostenvoranschlag?' ) ); ?></span><span class="fw-faq-icon" aria-hidden="true">+</span></button><div class="fw-faq-answer"><p><?php echo esc_html( findewerkstatt_t( 'Nennen Sie dem Betrieb Ihre Fahrzeugmarke, das Modell und Ihr Anliegen. Fragen Sie vor der Beauftragung nach dem Leistungsumfang und den voraussichtlichen Kosten.' ) ); ?></p></div></div>
            <div class="fw-faq-item"><button type="button" class="fw-faq-question"><span><?php echo esc_html( findewerkstatt_t( 'Welche Zahlungsmöglichkeiten gibt es?' ) ); ?></span><span class="fw-faq-icon" aria-hidden="true">+</span></button><div class="fw-faq-answer"><p><?php echo esc_html( findewerkstatt_t( 'Erkundigen Sie sich direkt beim Betrieb nach den akzeptierten Zahlungsmöglichkeiten.' ) ); ?></p></div></div>
        </section>
        <?php if ( $rating['rating'] !== null && $rating['count'] > 0 ) : ?><section class="fw-box"><h2><?php echo esc_html( findewerkstatt_t( 'Bewertung des Betriebs' ) ); ?></h2><?php echo findewerkstatt_render_stars( $rating['rating'], $rating['count'] ); ?><p><?php echo esc_html( findewerkstatt_t( 'Bewertungen dienen der Orientierung. Leistungen und Kosten besprechen Sie bitte direkt mit dem Betrieb.' ) ); ?></p></section><?php endif; ?>
    </div><aside class="fw-sidebar" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Kontakt und Öffnungszeiten' ) ); ?>"><div class="fw-contact-card"><h2><?php echo esc_html( findewerkstatt_t( 'Kontakt und Anfahrt' ) ); ?></h2>
        <p><strong><?php echo esc_html( findewerkstatt_t( 'Adresse' ) ); ?></strong><br><?php echo esc_html( trim( $address . ', ' . $plz . ' ' . $address_city, ', ' ) ?: findewerkstatt_t( 'Nicht angegeben' ) ); ?></p>
        <?php if ( $phone ) : ?><p><strong><?php echo esc_html( findewerkstatt_t( 'Telefon' ) ); ?></strong><br><a href="tel:<?php echo esc_attr( $phone ); ?>"><?php echo esc_html( $phone ); ?></a></p><?php endif; ?>
        <?php if ( $email && is_email( $email ) ) : ?><p><strong><?php echo esc_html( findewerkstatt_t( 'E-Mail' ) ); ?></strong><br><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></p><?php endif; ?>
        <?php if ( $website ) : ?><p><strong><?php echo esc_html( findewerkstatt_t( 'Website' ) ); ?></strong><br><a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="nofollow noopener noreferrer"><?php echo esc_html( wp_parse_url( $website, PHP_URL_HOST ) ); ?> ↗</a></p><?php endif; ?>
        <h3><?php echo esc_html( findewerkstatt_t( 'Öffnungszeiten' ) ); ?></h3><table class="fw-hours-table"><tbody><?php foreach ( array( 'weekday' => 'Montag–Freitag', 'saturday' => 'Samstag', 'sunday' => 'Sonntag' ) as $key => $label ) : ?><tr><th scope="row"><?php echo esc_html( findewerkstatt_t( $label ) ); ?></th><td><?php echo esc_html( get_post_meta( $id, '_mechanic_hours_' . $key, true ) ?: findewerkstatt_t( 'Nicht angegeben' ) ); ?></td></tr><?php endforeach; ?></tbody></table><p class="fw-muted"><?php echo esc_html( findewerkstatt_t( 'An Feiertagen können die Öffnungszeiten abweichen. Bitte fragen Sie bei Bedarf direkt nach.' ) ); ?></p>
        <?php if ( $maps_url ) : ?><a class="fw-btn fw-btn-primary fw-btn-block" href="<?php echo esc_url( $maps_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( findewerkstatt_t( 'In Google Maps öffnen' ) ); ?></a><?php endif; ?>
        <p class="fw-muted"><?php echo esc_html( findewerkstatt_t( 'Bitte bestätigen Sie Leistungen, Kontaktdaten und Öffnungszeiten direkt beim Betrieb.' ) ); ?> <a href="<?php echo esc_url( findewerkstatt_page_url( 'kontakt' ) ); ?>"><?php echo esc_html( findewerkstatt_t( 'Fehler melden' ) ); ?></a></p>
        <?php if ( ! get_post_meta( $id, '_fw_owner_user_id', true ) ) : ?><p class="fw-muted"><?php echo esc_html( findewerkstatt_t( 'Gehört Ihnen dieser Betrieb?' ) ); ?> <a href="<?php echo esc_url( add_query_arg( 'uebernehmen', $id, findewerkstatt_page_url( 'mein-konto' ) ) . '#eintrag-uebernehmen' ); ?>"><?php echo esc_html( findewerkstatt_t( 'Eintrag übernehmen' ) ); ?></a></p><?php endif; ?>
    </div></aside></div>
</main>
<?php endwhile; get_footer(); ?>
