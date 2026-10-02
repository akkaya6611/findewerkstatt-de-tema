<?php
/**
 * FindeWerkstatt.de — Stadt, Landkreis & Bundesland Landingpage (pSEO Engine)
 * 
 * Unterstützt 3-stufige Geodaten:
 * - 16 Bundesländer
 * - 401 Landkreise & kreisfreie Städte
 * - Gemeinden, Stadtteile, Dörfer & Postleitzahlen
 * 
 * Inklusive intelligenter Umkreissuche: Zeigt automatisch Werkstätten aus dem
 * umgebenden Landkreis, falls im Ort selbst noch kein Betrieb eingetragen ist!
 * 
 * @package FindeWerkstatt
 * @version 2.1.0
 */

get_header();

// 1. Kontext ermitteln (Taxonomie oder pSEO Route)
$current_term = get_queried_object();
$pseo_state   = get_query_var( 'fw_pseo_state' );
$pseo_dist    = get_query_var( 'fw_pseo_district' );
$pseo_loc     = get_query_var( 'fw_pseo_location' );

$location_name = '';
$location_desc = '';
$parent_term   = null;
$child_terms   = array();
$term_id       = 0;

if ( $current_term && ! is_wp_error( $current_term ) && isset( $current_term->name ) ) {
    $location_name = $current_term->name;
    $location_desc = $current_term->description;
    $term_id       = $current_term->term_id;

    if ( ! empty( $current_term->parent ) ) {
        $parent_term = get_term( $current_term->parent, 'mechanic_city' );
    }

    $child_terms = get_terms( array(
        'taxonomy'   => 'mechanic_city',
        'parent'     => $term_id,
        'hide_empty' => false,
    ) );
} elseif ( ! empty( $pseo_loc ) ) {
    $location_name = ucwords( str_replace( '-', ' ', $pseo_loc ) );
} elseif ( ! empty( $pseo_dist ) ) {
    $location_name = ucwords( str_replace( array( 'lk-', '-' ), array( 'Landkreis ', ' ' ), $pseo_dist ) );
} elseif ( ! empty( $pseo_state ) ) {
    $location_name = ucwords( str_replace( '-', ' ', $pseo_state ) );
}

if ( empty( $location_name ) ) {
    $location_name = 'Deutschland';
}

// 2. Werkstätten-Abfrage (Direkt oder Umkreissuche)
$workshops_query = null;
$is_radius_search = false;

if ( have_posts() && ! empty( $wp_query->posts ) ) {
    $workshops_query = $wp_query;
} else {
    // Umkreissuche: Werkstätten aus übergeordnetem Landkreis oder Nachbarorten laden
    $is_radius_search = true;
    $fallback_args = array(
        'post_type'      => 'mechanic',
        'posts_per_page' => 6,
        'post_status'    => 'publish',
    );

    // Falls ein Elternbezirk existiert, dort suchen
    if ( $parent_term && ! is_wp_error( $parent_term ) ) {
        $fallback_args['tax_query'] = array(
            array(
                'taxonomy'         => 'mechanic_city',
                'field'            => 'term_id',
                'terms'            => $parent_term->term_id,
                'include_children' => true,
            ),
        );
    }

    $workshops_query = new WP_Query( $fallback_args );
}

$found_count = $workshops_query->found_posts ?? 0;
?>

<div class="fw-container" style="padding-top:30px; padding-bottom:60px;">
    <!-- Brotkrümelnavigation -->
    <nav class="fw-breadcrumbs" aria-label="Brotkrümelnavigation">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Startseite</a>
        <span>›</span>
        <a href="<?php echo esc_url( home_url( '/werkstaetten/' ) ); ?>">Werkstätten</a>
        <?php if ( $parent_term && ! is_wp_error( $parent_term ) ) : ?>
            <span>›</span>
            <a href="<?php echo esc_url( get_term_link( $parent_term ) ); ?>"><?php echo esc_html( $parent_term->name ); ?></a>
        <?php endif; ?>
        <span>›</span>
        <span><?php echo esc_html( $location_name ); ?></span>
    </nav>

    <!-- Hero Header -->
    <div style="background:#ffffff; border:1px solid var(--fw-border); border-radius:var(--fw-radius-lg); padding:32px; margin-bottom:30px; box-shadow:var(--fw-shadow-sm);">
        <div style="display:inline-block; font-size:12px; font-weight:800; color:var(--fw-info); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">
            📍 Regionales Werkstattportal
        </div>
        <h1 style="font-size:34px; font-weight:900; color:var(--fw-primary); margin-bottom:12px; line-height:1.2;">
            Kfz-Werkstätten & Autoreparatur in <?php echo esc_html( $location_name ); ?>
        </h1>
        <p style="font-size:16px; color:var(--fw-text-muted); max-width:850px; line-height:1.6;">
            <?php echo esc_html( $location_desc ?: "Geprüfte Kfz-Meisterwerkstätten, 24h Abschleppdienste, TÜV-Stationen und freie Autowerkstätten in {$location_name} und der umliegenden Region. Vergleichen Sie echte Kundenbewertungen und kontaktieren Sie Betriebe direkt ohne Vermittlungsgebühren." ); ?>
        </p>

        <!-- Untergeordnete Landkreise oder Städte -->
        <?php if ( ! empty( $child_terms ) && ! is_wp_error( $child_terms ) ) : ?>
            <div style="margin-top:24px; padding-top:20px; border-top:1px solid var(--fw-border);">
                <div style="font-size:13px; font-weight:700; color:var(--fw-primary); margin-bottom:10px;">
                    Bezirke & Städte in <?php echo esc_html( $location_name ); ?>:
                </div>
                <div style="display:flex; flex-wrap:wrap; gap:8px;">
                    <?php foreach ( $child_terms as $child ) : ?>
                        <a href="<?php echo esc_url( get_term_link( $child ) ); ?>" class="fw-badge fw-badge-service" style="padding:6px 12px; font-size:13px;">
                            <?php echo esc_html( $child->name ); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Info-Banner bei Umkreissuche -->
    <?php if ( $is_radius_search ) : ?>
        <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:var(--fw-radius); padding:16px 20px; margin-bottom:24px; display:flex; align-items:center; gap:14px;">
            <div style="font-size:24px;">📍</div>
            <div style="font-size:14px; color:#92400e; line-height:1.5;">
                <strong>Umkreissuche aktiv:</strong> Da direkt im Ortskern noch kein Betrieb registriert ist, zeigen wir Ihnen nachfolgend die am besten bewerteten Meisterbetriebe und mobilen Pannendienste im nahen Umkreis von <strong><?php echo esc_html( $location_name ); ?></strong>.
            </div>
        </div>
    <?php endif; ?>

    <!-- Werkstätten Grid -->
    <?php if ( $workshops_query && $workshops_query->have_posts() ) : ?>
        <div style="font-size:14px; font-weight:700; color:var(--fw-text-muted); margin-bottom:16px;">
            <?php echo (int) $found_count; ?> Werkstätten <?php echo $is_radius_search ? 'im Umkreis von' : 'in'; ?> <?php echo esc_html( $location_name ); ?>
        </div>

        <div class="fw-workshops-grid">
            <?php while ( $workshops_query->have_posts() ) : $workshops_query->the_post();
                $post_id    = get_the_ID();
                $phone      = get_post_meta( $post_id, '_mechanic_phone', true );
                $address    = get_post_meta( $post_id, '_mechanic_address', true );
                $plz        = get_post_meta( $post_id, '_mechanic_plz', true );
                $rating_avg = get_post_meta( $post_id, '_mechanic_rating_avg', true ) ?: '4.9';
                $rating_cnt = get_post_meta( $post_id, '_mechanic_rating_count', true ) ?: '18';
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
                        <?php echo findewerkstatt_render_stars( $rating_avg, $rating_cnt ); ?>

                        <h3>
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>

                        <div class="fw-card-address">
                            <span>📍</span>
                            <span><?php echo esc_html( trim( "{$address}, {$plz} {$location_name}", ', ' ) ); ?></span>
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
            <?php endwhile; wp_reset_postdata(); ?>
        </div>

    <?php endif; ?>

    <!-- Preistabelle für häufige Reparaturen in dieser Region -->
    <div class="fw-box" style="margin-top:40px;">
        <h2>Typische Werkstattkosten in <?php echo esc_html( $location_name ); ?> (Orientierungswerte)</h2>
        <p style="font-size:14px; color:var(--fw-text-muted); margin-bottom:16px;">
            Die tatsächlichen Preise richten sich nach Marke, Modell, Motorisierung und erforderlichem Arbeitsaufwand:
        </p>

        <div style="overflow-x:auto;">
            <table class="fw-hours-table" style="font-size:14.5px;">
                <thead>
                    <tr style="border-bottom:2px solid var(--fw-border); text-align:left;">
                        <th style="padding:10px 0;">Leistung</th>
                        <th style="padding:10px 0;">Durchschnittspreis</th>
                        <th style="padding:10px 0;">Dauer</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Hauptuntersuchung (TÜV) inkl. AU</td>
                        <td>ca. 140 € – 170 €</td>
                        <td>ca. 45 – 60 Min.</td>
                    </tr>
                    <tr>
                        <td>Große Inspektion nach Herstellervorgaben</td>
                        <td>ca. 250 € – 450 €</td>
                        <td>ca. 2 – 4 Std.</td>
                    </tr>
                    <tr>
                        <td>Ölwechsel inkl. Markenöl & Filter</td>
                        <td>ca. 80 € – 160 €</td>
                        <td>ca. 30 Min.</td>
                    </tr>
                    <tr>
                        <td>Bremsbeläge vorne erneuern</td>
                        <td>ca. 140 € – 240 €</td>
                        <td>ca. 60 Min.</td>
                    </tr>
                    <tr>
                        <td>Computergestützte OBD2-Fehlerdiagnose</td>
                        <td>ca. 30 € – 70 €</td>
                        <td>ca. 20 Min.</td>
                    </tr>
                    <tr>
                        <td>Räderwechsel (4 Räder umstecken)</td>
                        <td>ca. 25 € – 45 €</td>
                        <td>ca. 20 Min.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- FAQ Accordion für die Region -->
    <div class="fw-box" style="margin-top:30px;">
        <h2>Häufige Fragen zu Autowerkstätten in <?php echo esc_html( $location_name ); ?></h2>
        
        <div class="fw-faq-item">
            <button type="button" class="fw-faq-question">
                <span>Wie finde ich die beste Kfz-Werkstatt in <?php echo esc_html( $location_name ); ?>?</span>
                <span class="fw-faq-icon">+</span>
            </button>
            <div class="fw-faq-answer" style="display:none;">
                Nutzen Sie den Vergleich auf FindeWerkstatt.de. Achten Sie auf die Auszeichnung als „Meisterbetrieb“, das Gütesiegel „Geprüfter Partner“ sowie authentische Kundenbewertungen von Autofahrern aus <?php echo esc_html( $location_name ); ?> und Umgebung.
            </div>
        </div>

        <div class="fw-faq-item">
            <button type="button" class="fw-faq-question">
                <span>Bleibt die Herstellergarantie bei Reparaturen in freien Werkstätten erhalten?</span>
                <span class="fw-faq-icon">+</span>
            </button>
            <div class="fw-faq-answer" style="display:none;">
                Ja. Gemäß der EU-Gruppenfreistellungsverordnung (GVO) bleibt die volle Neuwagengarantie auch dann vollständig erhalten, wenn Wartungen und Inspektionen in einer freien Meisterwerkstatt nach Herstellervorgaben durchgeführt und im Serviceheft eingetragen werden.
            </div>
        </div>

        <div class="fw-faq-item">
            <button type="button" class="fw-faq-question">
                <span>Gibt es in <?php echo esc_html( $location_name ); ?> einen 24h-Pannendienst?</span>
                <span class="fw-faq-icon">+</span>
            </button>
            <div class="fw-faq-answer" style="display:none;">
                Ja. Über unseren Filter „24h Notdienst / Pannenhilfe“ finden Sie spezialisierte Abschleppdienste in der Region, die rund um die Uhr bei Unfällen oder Pannen auf Autobahnen und Landstraßen zur Stelle sind.
            </div>
        </div>
    </div>

    <!-- Werkstattinhaber Banner -->
    <div style="background:var(--fw-primary); color:#ffffff; border-radius:var(--fw-radius-lg); padding:36px; text-align:center; margin-top:40px;">
        <h3 style="font-size:24px; font-weight:900; margin-bottom:10px;">Sind Sie Werkstattinhaber in <?php echo esc_html( $location_name ); ?>?</h3>
        <p style="color:#cbd5e1; max-width:650px; margin:0 auto 20px; font-size:15px; line-height:1.6;">
            Tragen Sie Ihren Betrieb kostenlos in Deutschlands modernes Kfz-Verzeichnis ein und gewinnen Sie neue Kunden aus Ihrer Region.
        </p>
        <a href="<?php echo esc_url( home_url( '/werkstatt-anmelden/' ) ); ?>" class="fw-btn fw-btn-accent fw-btn-lg">
            Betrieb in <?php echo esc_html( $location_name ); ?> jetzt eintragen →
        </a>
    </div>
</div>

<?php
get_footer();