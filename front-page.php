<?php
/**
 * FindeWerkstatt.de — Startseite (Homepage)
 * 
 * @package FindeWerkstatt
 * @version 2.0.0
 */

get_header();

// Daten für Suchfilter laden
$bundeslaender = FindeWerkstatt_German_Data::get_bundeslaender();
$categories    = FindeWerkstatt_German_Data::get_categories();
$brands        = FindeWerkstatt_German_Data::get_car_brands();
?>

<!-- 1. HERO SEARCH SECTION -->
<section class="fw-hero">
    <div class="fw-container">
        <div class="fw-hero-content">
            <div class="fw-hero-badge">
                <span>🛡️</span> Offizielles deutsches Kfz-Werkstattverzeichnis
            </div>
            <h1>Die beste <span>Kfz-Werkstatt</span> in Ihrer Nähe finden</h1>
            <p>Vergleichen Sie geprüfte Meisterbetriebe, TÜV-Stationen und 24h-Pannenhilfen in allen 16 Bundesländern.</p>

            <form action="<?php echo esc_url( home_url( '/werkstaetten/' ) ); ?>" method="get" class="fw-search-box">
                <!-- Bundesland / Stadt -->
                <div class="fw-search-field">
                    <span class="fw-field-icon">📍</span>
                    <label>Bundesland / Stadt</label>
                    <select name="fw_city">
                        <option value="">Ganz Deutschland</option>
                        <?php foreach ( $bundeslaender as $code => $land ) : ?>
                            <option value="<?php echo esc_attr( $land['slug'] ); ?>"><?php echo esc_html( $land['name'] ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Leistung / Fachbereich -->
                <div class="fw-search-field">
                    <span class="fw-field-icon">🔧</span>
                    <label>Leistung / Service</label>
                    <select name="fw_service">
                        <option value="">Alle Leistungen</option>
                        <?php foreach ( $categories as $slug => $cat ) : ?>
                            <option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $cat['name'] ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Automarke -->
                <div class="fw-search-field">
                    <span class="fw-field-icon">🚗</span>
                    <label>Fahrzeugmarke</label>
                    <select name="fw_brand">
                        <option value="">Alle Automarken</option>
                        <?php foreach ( $brands as $slug => $name ) : ?>
                            <option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="fw-btn fw-btn-accent fw-btn-lg">
                    🔍 Suchen
                </button>
            </form>

            <div class="fw-hero-popular">
                <span>Beliebte Städte:</span>
                <a href="<?php echo esc_url( home_url( '/stadt/berlin/' ) ); ?>">Berlin</a>
                <a href="<?php echo esc_url( home_url( '/stadt/hamburg/' ) ); ?>">Hamburg</a>
                <a href="<?php echo esc_url( home_url( '/stadt/muenchen/' ) ); ?>">München</a>
                <a href="<?php echo esc_url( home_url( '/stadt/koeln/' ) ); ?>">Köln</a>
                <a href="<?php echo esc_url( home_url( '/stadt/frankfurt-am-main/' ) ); ?>">Frankfurt</a>
                <a href="<?php echo esc_url( home_url( '/stadt/stuttgart/' ) ); ?>">Stuttgart</a>
            </div>
        </div>
    </div>
</section>

<!-- 2. KATEGORIEN & FACHBEREICHE -->
<section class="fw-section">
    <div class="fw-container">
        <div class="fw-section-header">
            <div class="fw-section-tag">Kompetente Hilfe</div>
            <h2>Kfz-Leistungen & Fachbereiche</h2>
            <p>Vom schnellen Reifenwechsel bis zur komplexen Getriebeinstandsetzung — wählen Sie die passende Kategorie für Ihr Anliegen.</p>
        </div>

        <div class="fw-categories-grid">
            <?php foreach ( $categories as $slug => $cat ) : ?>
                <a href="<?php echo esc_url( home_url( '/service/' . $slug . '/' ) ); ?>" class="fw-cat-card">
                    <div class="fw-cat-icon">⚙️</div>
                    <div class="fw-cat-info">
                        <h3><?php echo esc_html( $cat['name'] ); ?></h3>
                        <p><?php echo esc_html( wp_trim_words( $cat['desc'], 8 ) ); ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 3. GEPRÜFTE WERKSTÄTTEN (FEATURED) -->
<section class="fw-section" style="background:#f1f5f9;">
    <div class="fw-container">
        <div class="fw-section-header">
            <div class="fw-section-tag">Qualitätsgeprüft</div>
            <h2>Empfohlene Kfz-Meisterbetriebe</h2>
            <p>Von Kunden top-bewertete Werkstätten mit geprüften Zertifikaten und modernster Diagnoseausstattung.</p>
        </div>

        <div class="fw-workshops-grid">
            <?php
            $featured_query = new WP_Query( array(
                'post_type'      => 'mechanic',
                'posts_per_page' => 6,
                'post_status'    => 'publish',
            ) );

            if ( $featured_query->have_posts() ) :
                while ( $featured_query->have_posts() ) : $featured_query->the_post();
                    $post_id   = get_the_ID();
                    $phone     = get_post_meta( $post_id, '_mechanic_phone', true );
                    $address   = get_post_meta( $post_id, '_mechanic_address', true );
                    $plz       = get_post_meta( $post_id, '_mechanic_plz', true );
                    $rating    = get_post_meta( $post_id, '_mechanic_rating_avg', true ) ?: '4.9';
                    $count     = get_post_meta( $post_id, '_mechanic_rating_count', true ) ?: '18';
                    
                    $city_terms = wp_get_post_terms( $post_id, 'mechanic_city' );
                    $city_name  = ( $city_terms && ! is_wp_error( $city_terms ) ) ? $city_terms[0]->name : '';
                    $services   = wp_get_post_terms( $post_id, 'service_type' );
                    ?>
                    <div class="fw-workshop-card">
                        <div class="fw-card-top">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'medium', array( 'class' => 'fw-card-img' ) ); ?>
                            <?php else : ?>
                                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/placeholder-mechanic.webp' ); ?>" alt="<?php the_title_attribute(); ?>" class="fw-card-img">
                            <?php endif; ?>
                            <div class="fw-card-badges">
                                <?php echo findewerkstatt_render_badges( $post_id ); ?>
                            </div>
                            <?php echo findewerkstatt_get_open_status(); ?>
                        </div>

                        <div class="fw-card-body">
                            <?php echo findewerkstatt_render_stars( $rating, $count ); ?>

                            <h3>
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>

                            <div class="fw-card-address">
                                <span>📍</span>
                                <span><?php echo esc_html( trim( "{$address}, {$plz} {$city_name}", ', ' ) ); ?></span>
                            </div>

                            <?php if ( ! empty( $services ) && ! is_wp_error( $services ) ) : ?>
                                <div class="fw-card-services">
                                    <?php foreach ( array_slice( $services, 0, 3 ) as $st ) : ?>
                                        <span class="fw-badge fw-badge-service"><?php echo esc_html( $st->name ); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="fw-card-actions">
                                <?php if ( $phone ) : ?>
                                    <a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>" class="fw-btn fw-btn-phone fw-btn-sm">
                                        📞 Anrufen
                                    </a>
                                <?php endif; ?>
                                <a href="<?php the_permalink(); ?>" class="fw-btn fw-btn-outline fw-btn-sm">
                                    Details ansehen →
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </div>

        <div style="text-align:center; margin-top:36px;">
            <a href="<?php echo esc_url( home_url( '/werkstaetten/' ) ); ?>" class="fw-btn fw-btn-primary fw-btn-lg">
                Alle Kfz-Werkstätten in Deutschland ansehen →
            </a>
        </div>
    </div>
</section>

<!-- 4. 16 BUNDESLÄNDER DIRECTORY -->
<section class="fw-section" id="bundeslaender">
    <div class="fw-container">
        <div class="fw-section-header">
            <div class="fw-section-tag">Bundesweit</div>
            <h2>Werkstätten nach Bundesland</h2>
            <p>Finden Sie zertifizierte Autowerkstätten und Notdienste direkt in Ihrem Bundesland.</p>
        </div>

        <div class="fw-bundeslaender-grid">
            <?php foreach ( $bundeslaender as $code => $land ) : ?>
                <a href="<?php echo esc_url( home_url( '/stadt/' . $land['slug'] . '/' ) ); ?>" class="fw-land-card">
                    <span class="fw-land-name"><?php echo esc_html( $land['name'] ); ?></span>
                    <span class="fw-land-badge"><?php echo esc_html( $land['capital'] ); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 5. VORTEILE (WARUM FINDEWERKSTATT.DE) -->
<section class="fw-section" style="background:#ffffff; border-top:1px solid var(--fw-border); border-bottom:1px solid var(--fw-border);">
    <div class="fw-container">
        <div class="fw-section-header">
            <div class="fw-section-tag">Ihre Vorteile</div>
            <h2>Warum Autofahrer FindeWerkstatt.de vertrauen</h2>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:24px;">
            <div class="fw-box" style="margin:0; text-align:center;">
                <div style="font-size:36px; margin-bottom:14px;">🛡️</div>
                <h3 style="font-size:18px; margin-bottom:10px;">100% Geprüfte Betriebe</h3>
                <p style="font-size:14.5px; color:var(--fw-text-muted);">
                    Wir prüfen Handwerkskammer-Zugehörigkeiten und Zertifikate, damit Sie Ihr Auto nur in vertrauenswürdige Hände geben.
                </p>
            </div>

            <div class="fw-box" style="margin:0; text-align:center;">
                <div style="font-size:36px; margin-bottom:14px;">⏱️</div>
                <h3 style="font-size:18px; margin-bottom:10px;">Direkter Kontakt ohne Vermittlungsgebühr</h3>
                <p style="font-size:14.5px; color:var(--fw-text-muted);">
                    Rufen Sie die Werkstatt direkt an oder schreiben Sie per WhatsApp. Keine teuren Vermittlungsaufschläge oder versteckten Kosten.
                </p>
            </div>

            <div class="fw-box" style="margin:0; text-align:center;">
                <div style="font-size:36px; margin-bottom:14px;">🚨</div>
                <h3 style="font-size:18px; margin-bottom:10px;">24h Notdienst & Pannenhilfe</h3>
                <p style="font-size:14.5px; color:var(--fw-text-muted);">
                    Panne auf der Autobahn oder nachts? Finden Sie sofort verfügbare Abschleppdienste in ganz Deutschland.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- 6. WERKSTATTINHABER CTA -->
<section class="fw-section" style="background:var(--fw-primary); color:#ffffff; text-align:center;">
    <div class="fw-container">
        <div style="max-width:700px; margin:0 auto;">
            <h2 style="font-size:32px; font-weight:900; margin-bottom:16px;">Sind Sie Kfz-Werkstattinhaber?</h2>
            <p style="font-size:17px; color:#cbd5e1; margin-bottom:30px; line-height:1.5;">
                Präsentieren Sie Ihren Betrieb Autofahrern in Ihrer Region. Erhalten Sie qualifizierte Neukundenanfragen und stärken Sie Ihren lokalen Ruf.
            </p>
            <a href="<?php echo esc_url( home_url( '/werkstatt-anmelden/' ) ); ?>" class="fw-btn fw-btn-accent fw-btn-lg">
                Betrieb jetzt kostenlos eintragen →
            </a>
        </div>
    </div>
</section>

<?php
get_footer();
