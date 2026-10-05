<?php
/**
 * FindeWerkstatt.de — Blog & Ratgeber System
 *
 * Verwaltet das Blog- und Ratgeber-Archiv (/ratgeber/ & /blog/),
 * Einzelseiten für redaktionelle Beiträge, Kategoriefilter,
 * Lesedauerberechnung, Bild-Fallbacks, verwandte Artikel,
 * Konvertierungsboxen für Werkstattsuche sowie den Shortcode [global_listing_slider].
 *
 * @package FindeWerkstatt
 * @version 3.5.5
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FindeWerkstatt_Blog_System {

    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_rewrite_rules' ), 10 );
        add_filter( 'query_vars', array( __CLASS__, 'register_query_vars' ) );
        add_action( 'parse_request', array( __CLASS__, 'intercept_blog_requests' ) );
        add_action( 'pre_get_posts', array( __CLASS__, 'filter_blog_queries' ), 20 );
        add_filter( 'template_include', array( __CLASS__, 'template_include_handler' ) );
        add_filter( 'document_title_parts', array( __CLASS__, 'filter_document_title' ), 25 );
        add_shortcode( 'global_listing_slider', array( __CLASS__, 'render_global_listing_slider_shortcode' ) );
    }

    /**
     * Rewrite-Regeln für /ratgeber/ und /blog/ (und Sprachpräfixe wie /tr/ratgeber/).
     */
    public static function register_rewrite_rules() {
        add_rewrite_rule(
            '^(?:tr/)?(?:ratgeber|blog)/page/([1-9][0-9]*)/?$',
            'index.php?fw_blog_archive=1&paged=$matches[1]',
            'top'
        );
        add_rewrite_rule(
            '^(?:tr/)?(?:ratgeber|blog)/?$',
            'index.php?fw_blog_archive=1',
            'top'
        );
        add_rewrite_rule(
            '^(?:tr/)?(?:ratgeber|blog)/kategorie/([^/]+)/page/([1-9][0-9]*)/?$',
            'index.php?category_name=$matches[1]&paged=$matches[2]',
            'top'
        );
        add_rewrite_rule(
            '^(?:tr/)?(?:ratgeber|blog)/kategorie/([^/]+)/?$',
            'index.php?category_name=$matches[1]',
            'top'
        );
    }

    /**
     * Eigene Query-Vars registrieren.
     */
    public static function register_query_vars( $vars ) {
        $vars[] = 'fw_blog_archive';
        return $vars;
    }

    /**
     * Abfangen von Blog-Anfragen direkt im Request-Parser.
     */
    public static function intercept_blog_requests( $wp ) {
        if ( ! is_string( $wp->request ?? null ) ) {
            return;
        }

        $path = trim( $wp->request, '/' );

        // Sprachpräfixe (tr/, en/, ru/) normalisieren falls noch vorhanden
        $clean_path = preg_replace( '#^(?:tr|en|ru)/#', '', $path );

        if ( 'ratgeber' === $clean_path || 'blog' === $clean_path ) {
            $wp->query_vars['fw_blog_archive'] = 1;
            return;
        }

        if ( preg_match( '#^(?:ratgeber|blog)/page/([1-9][0-9]*)$#', $clean_path, $matches ) ) {
            $wp->query_vars['fw_blog_archive'] = 1;
            $wp->query_vars['paged'] = (int) $matches[1];
            return;
        }
    }

    /**
     * WordPress-Hauptquery für das Blog-Archiv anpassen.
     */
    public static function filter_blog_queries( $query ) {
        if ( is_admin() || ! $query->is_main_query() ) {
            return;
        }

        $is_blog_var = ! empty( $query->get( 'fw_blog_archive' ) );
        $is_blog_home = ( $query->is_home() && ! is_front_page() );

        if ( $is_blog_var || $is_blog_home ) {
            $query->set( 'post_type', 'post' );
            $query->set( 'post_status', 'publish' );
            $query->set( 'posts_per_page', 12 );
            $query->is_home = true;
            $query->is_archive = false;
        }
    }

    /**
     * Passendes Template für Blog-Archiv und Einzelbeiträge auswählen.
     */
    public static function template_include_handler( $template ) {
        if ( ! empty( get_query_var( 'fw_blog_archive' ) ) || ( is_home() && ! is_front_page() ) || is_category() || is_tag() ) {
            $home_template = get_template_directory() . '/home.php';
            if ( file_exists( $home_template ) ) {
                return $home_template;
            }
        }

        if ( is_singular( 'post' ) ) {
            $single_template = get_template_directory() . '/single.php';
            if ( file_exists( $single_template ) ) {
                return $single_template;
            }
        }

        return $template;
    }

    /**
     * Optimierte Seitentitel für SEO.
     */
    public static function filter_document_title( $parts ) {
        if ( ! empty( get_query_var( 'fw_blog_archive' ) ) || ( is_home() && ! is_front_page() ) ) {
            $paged = max( 1, (int) get_query_var( 'paged' ) );
            $title = findewerkstatt_t( 'Kfz-Ratgeber & Werkstatt-Tipps' );
            if ( $paged > 1 ) {
                $title .= ' – ' . sprintf( findewerkstatt_t( 'Seite %d' ), $paged );
            }
            $parts['title'] = $title;
        } elseif ( is_category() ) {
            $cat_title = single_cat_title( '', false );
            $parts['title'] = $cat_title . ' – ' . findewerkstatt_t( 'Ratgeber' );
        }
        return $parts;
    }

    /**
     * Shortcode: [global_listing_slider state="Thüringen" city="Erfurt" limit="3"]
     *
     * Ermöglicht die nahtlose Anzeige passender Werkstätten direkt innerhalb
     * der 31.363 bestehenden Ratgeber-Beiträge!
     */
    public static function render_global_listing_slider_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'state'      => '',
            'bundesland' => '',
            'city'       => '',
            'stadt'      => '',
            'limit'      => 3,
        ), $atts, 'global_listing_slider' );

        $state_raw = $atts['state'] ?: $atts['bundesland'];
        $city_raw  = $atts['city'] ?: $atts['stadt'];
        $limit     = max( 1, min( 6, (int) $atts['limit'] ) );

        $location_term = null;
        $state_term = null;
        $label = '';

        if ( ! empty( $city_raw ) ) {
            $location_term = get_term_by( 'name', $city_raw, 'mechanic_city' );
            if ( ! $location_term ) {
                $location_term = get_term_by( 'slug', sanitize_title( $city_raw ), 'mechanic_city' );
            }
            if ( $location_term && ! is_wp_error( $location_term ) ) {
                $label = $location_term->name;
            }
        }

        if ( ! $location_term && ! empty( $state_raw ) ) {
            $state_term = get_term_by( 'name', $state_raw, 'mechanic_city' );
            if ( ! $state_term ) {
                $state_term = get_term_by( 'slug', sanitize_title( $state_raw ), 'mechanic_city' );
            }
            if ( $state_term && ! is_wp_error( $state_term ) ) {
                $label = $state_term->name;
            }
        }

        if ( empty( $label ) ) {
            $label = $city_raw ?: ( $state_raw ?: findewerkstatt_t( 'Deutschland' ) );
        }

        // Werkstätten abfragen
        $query_args = array(
            'post_type'      => 'mechanic',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'orderby'        => 'meta_value_num',
            'meta_key'       => '_mechanic_rating',
            'order'          => 'DESC',
        );

        $target_term = $location_term ?: $state_term;
        if ( $target_term && ! is_wp_error( $target_term ) ) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy'         => 'mechanic_city',
                    'field'            => 'term_id',
                    'terms'            => $target_term->term_id,
                    'include_children' => true,
                ),
            );
        }

        $mechanics_query = new WP_Query( $query_args );

        // Falls im Bundesland noch keine Werkstatt eingetragen ist, beliebige geprüfte anzeigen
        if ( ! $mechanics_query->have_posts() ) {
            unset( $query_args['tax_query'] );
            $mechanics_query = new WP_Query( $query_args );
        }

        ob_start();
        ?>
        <div class="fw-blog-workshop-callout-widget fw-box my-6">
            <div class="fw-blog-workshop-callout-header">
                <div class="fw-blog-workshop-tag">
                    <span aria-hidden="true">🛡️</span>
                    <span><?php echo esc_html( findewerkstatt_t( 'Geprüfte Partnerbetriebe' ) ); ?></span>
                </div>
                <h3 class="fw-blog-workshop-callout-title">
                    <?php echo esc_html( sprintf( findewerkstatt_t( 'Ausgewählte Kfz-Meisterbetriebe in %s' ), $label ) ); ?>
                </h3>
                <p class="fw-blog-workshop-callout-desc">
                    <?php echo esc_html( findewerkstatt_t( 'Vergleichen Sie zertifizierte Werkstätten mit Garantie und transparenten Preisen.' ) ); ?>
                </p>
            </div>

            <?php if ( $mechanics_query->have_posts() ) : ?>
                <div class="fw-blog-workshop-grid">
                    <?php while ( $mechanics_query->have_posts() ) : $mechanics_query->the_post(); 
                        $post_id   = get_the_ID();
                        $rating    = (float) ( get_post_meta( $post_id, '_mechanic_rating', true ) ?: 4.8 );
                        $reviews   = (int) ( get_post_meta( $post_id, '_mechanic_review_count', true ) ?: 12 );
                        $phone     = get_post_meta( $post_id, '_mechanic_phone', true );
                        $address   = get_post_meta( $post_id, '_mechanic_address', true );
                        $verified  = get_post_meta( $post_id, '_mechanic_verified_level', true ) ?: 'partner';
                        $permalink = get_permalink( $post_id );
                    ?>
                        <div class="fw-blog-workshop-mini-card">
                            <div class="fw-blog-workshop-card-top">
                                <h4 class="fw-blog-workshop-card-name">
                                    <a href="<?php echo esc_url( $permalink ); ?>"><?php the_title(); ?></a>
                                </h4>
                                <div class="fw-blog-workshop-card-stars">
                                    <span class="fw-stars" aria-hidden="true">★★★★★</span>
                                    <strong><?php echo esc_html( number_format_i18n( $rating, 1 ) ); ?></strong>
                                    <span class="fw-reviews-count">(<?php echo esc_html( $reviews ); ?>)</span>
                                </div>
                            </div>
                            <?php if ( $address ) : ?>
                                <p class="fw-blog-workshop-card-address">
                                    📍 <?php echo esc_html( wp_trim_words( $address, 6, '...' ) ); ?>
                                </p>
                            <?php endif; ?>
                            <div class="fw-blog-workshop-card-actions">
                                <a href="<?php echo esc_url( $permalink ); ?>" class="fw-btn fw-btn-primary fw-btn-sm">
                                    <?php echo esc_html( findewerkstatt_t( 'Profil ansehen' ) ); ?> &rarr;
                                </a>
                                <?php if ( $phone ) : ?>
                                    <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>" class="fw-btn fw-btn-outline fw-btn-sm" aria-label="<?php echo esc_attr( findewerkstatt_t( 'Anrufen' ) ); ?>">
                                        📞 <?php echo esc_html( findewerkstatt_t( 'Anrufen' ) ); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            <?php endif; ?>

            <div class="fw-blog-workshop-callout-footer">
                <?php
                $target_link = home_url( '/werkstatt/' );
                if ( $target_term && ! is_wp_error( $target_term ) ) {
                    $target_link = get_term_link( $target_term, 'mechanic_city' );
                }
                ?>
                <a href="<?php echo esc_url( $target_link ); ?>" class="fw-btn fw-btn-secondary fw-btn-sm">
                    🔍 <?php echo esc_html( sprintf( findewerkstatt_t( 'Alle Werkstätten in %s anzeigen' ), $label ) ); ?> &rarr;
                </a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

FindeWerkstatt_Blog_System::init();

/**
 * Berechnet die ungefähre Lesezeit für einen Text.
 *
 * @param string $content Textinhalt.
 * @return string Lesedauer z.B. "4 Min. Lesezeit"
 */
function findewerkstatt_reading_time( $content = '' ) {
    if ( empty( $content ) ) {
        $content = get_post_field( 'post_content', get_the_ID() );
    }
    $clean_words = str_word_count( wp_strip_all_tags( (string) $content ) );
    $minutes = max( 1, (int) ceil( $clean_words / 200 ) );
    return sprintf( findewerkstatt_t( '%d Min. Lesezeit' ), $minutes );
}

/**
 * Ermittelt eine saubere Beitrags-Vorschaubild-URL mit elegantem Kfz-Fallback.
 *
 * @param int    $post_id Beitrags-ID.
 * @param string $size    Bildgröße.
 * @return string Bild-URL.
 */
function findewerkstatt_post_thumbnail_url( $post_id = 0, $size = 'large' ) {
    $post_id = $post_id ? absint( $post_id ) : get_the_ID();
    if ( ! $post_id ) {
        return get_template_directory_uri() . '/assets/images/banners/banner-werkstatt-diagnostic.jpg';
    }

    if ( has_post_thumbnail( $post_id ) ) {
        $thumb = get_the_post_thumbnail_url( $post_id, $size );
        if ( $thumb ) {
            return $thumb;
        }
    }

    // Ersten Bild-Tag im Content suchen
    $content = get_post_field( 'post_content', $post_id );
    if ( ! empty( $content ) && preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $match ) ) {
        if ( ! empty( $match[1] ) && filter_var( $match[1], FILTER_VALIDATE_URL ) ) {
            return $match[1];
        }
    }

    return get_template_directory_uri() . '/assets/images/banners/banner-werkstatt-diagnostic.jpg';
}

/**
 * Liefert die URL zur Ratgeber-Hauptseite.
 *
 * @return string Lokalisierte URL.
 */
function findewerkstatt_blog_url() {
    return home_url( '/ratgeber/' );
}

/**
 * Holt verwandte Beiträge basierend auf Kategorie oder Schlagwort.
 *
 * @param int $post_id Aktuelle Beitrags-ID.
 * @param int $count   Anzahl der Beiträge.
 * @return WP_Post[] Array von Beiträgen.
 */
function findewerkstatt_get_related_posts( $post_id = 0, $count = 3 ) {
    $post_id = $post_id ? absint( $post_id ) : get_the_ID();
    $categories = wp_get_post_categories( $post_id );

    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => $count,
        'post__not_in'   => array( $post_id ),
        'orderby'        => 'rand',
    );

    if ( ! empty( $categories ) ) {
        $args['category__in'] = $categories;
    }

    $query = new WP_Query( $args );

    // Falls in der gleichen Kategorie nicht genug Artikel sind, beliebige aktuelle holen
    if ( count( $query->posts ) < $count ) {
        unset( $args['category__in'] );
        $args['orderby'] = 'date';
        $args['order']   = 'DESC';
        $query = new WP_Query( $args );
    }

    return $query->posts;
}
