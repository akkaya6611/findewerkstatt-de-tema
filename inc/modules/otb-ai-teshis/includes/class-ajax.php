<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class OTB_AI_Ajax {

    public function __construct() {
        add_action( 'wp_ajax_otb_ai_analyse',          [ $this, 'analyse' ] );
        add_action( 'wp_ajax_nopriv_otb_ai_analyse',   [ $this, 'analyse' ] );
        add_action( 'wp_ajax_otb_ai_firms',            [ $this, 'firms' ] );
        add_action( 'wp_ajax_nopriv_otb_ai_firms',     [ $this, 'firms' ] );
    }

    private function verify() {
        if ( ! isset( $_POST['_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ), 'otb_ai_nonce' ) )
            wp_send_json_error( [ 'message' => 'Güvenlik hatası.' ] );
    }

    private function field( $key, $ta = false ) {
        $val = wp_unslash( $_POST[ $key ] ?? '' );
        return $ta ? sanitize_textarea_field( $val ) : sanitize_text_field( $val );
    }

    public function analyse() {
        $this->verify();

        $require_login = OTB_AI_Settings::get( OTB_AI_Settings::REQ_LOGIN, '1' );
        $cost          = max( 1, (int) OTB_AI_Settings::get( OTB_AI_Settings::COST, 3 ) );
        $uid           = 0;
        $credits_before = null;

        if ( $require_login === '1' ) {
            if ( ! is_user_logged_in() )
                wp_send_json_error( [ 'message' => 'Giriş gerekli.', 'remaining_credits' => null ] );

            $uid = get_current_user_id();

            if ( ! current_user_can( 'manage_options' ) ) {
                $credits_before = OTB_AI_Credits::get( $uid );
                if ( ! OTB_AI_Credits::has_enough( $uid, $cost ) )
                    wp_send_json_error( [ 'message' => 'Yeterli kredin yok.', 'remaining_credits' => $credits_before ] );
                OTB_AI_Credits::deduct( $uid, $cost );
            }
        }

        $inputs = [
            'beschreibung' => $this->field( 'beschreibung', true ),
            'ort'          => $this->field( 'ort' ),
            'marke_modell' => $this->field( 'marke_modell' ),
            'kilometer'    => $this->field( 'kilometer' ),
            'situationen'  => $this->field( 'situationen', true ),
            'geraeusche'   => $this->field( 'geraeusche', true ),
            'warnleuchten' => $this->field( 'warnleuchten', true ),
            'dauer'        => $this->field( 'dauer' ),
            'share_consent'=> $this->field( 'share_consent' ),
        ];

        if ( empty( $inputs['beschreibung'] ) )
            wp_send_json_error( [ 'message' => 'Problem açıklaması zorunlu.' ] );

        $json = OTB_AI_Groq::analyse( $inputs );

        if ( isset( $json['error'] ) ) {
            if ( $uid && $credits_before !== null ) OTB_AI_Credits::refund( $uid, $cost );
            wp_send_json_error( [ 'message' => $json['error'] ] );
        }

        // Kalan kredi
        if ( $require_login === '1' ) {
            $json['remaining_credits'] = current_user_can( 'manage_options' )
                ? '∞'
                : OTB_AI_Credits::get( $uid );
        }

        // Blog
        $inputs['city'] = OTB_AI_Groq::city_from_ort( $inputs['ort'] );
        $blog = OTB_AI_Blog::maybe_create( $json, $inputs );
        if ( is_array( $blog ) && ! empty( $blog['url'] ) ) {
            $json['blog_post_url']    = $blog['url'];
            $json['blog_post_status'] = $blog['status'];
        }

        // Akıllı Köprü: Teşhise En Uygun Usta Branşını Tespit Et
        $full_analysis_text = mb_strtolower( ( $inputs['beschreibung'] ?? '' ) . ' ' . ( $json['kurzer_text'] ?? '' ) . ' ' . implode( ' ', (array) ( $json['wahrscheinliche_ursachen'] ?? [] ) ) . ' ' . implode( ' ', (array) ( $json['empfohlene_werkstatt_typen'] ?? [] ) ), 'UTF-8' );
        $json['matched_category'] = self::detect_service_category( $full_analysis_text );

        wp_send_json_success( $json );
    }

    public static function detect_service_category( $text ) {
        $categories = array(
            'sanziman'   => array( 'slug' => 'sanziman-ve-guc-aktarma', 'name' => 'Şanzıman ve Güç Aktarma', 'keys' => array( 'şanzıman', 'sanziman', 'vites', 'debriyaj', 'baskı balata', 'baski balata', 'dsg', 'edc', 'tork konvertör' ) ),
            'elektrik'   => array( 'slug' => 'oto-elektrik-ustalari', 'name' => 'Oto Elektrik Ustaları', 'keys' => array( 'elektrik', 'akü', 'aku', 'marş', 'mars', 'şarj', 'sarj', 'sigorta', 'far', 'aydınlatma', 'alternatör', 'dinamo' ) ),
            'fren'       => array( 'slug' => 'fren-ve-balata-ustasi', 'name' => 'Fren ve Balata Ustası', 'keys' => array( 'fren', 'balata', 'disk', 'abs', 'esp', 'el freni', 'fren hidroliği', 'kaliper', 'westinghouse' ) ),
            'lastik'     => array( 'slug' => 'oto-lastik-ve-jant-ustasi-lastik-oteli-rot-balans', 'name' => 'Oto Lastik ve Jant Ustası', 'keys' => array( 'lastik', 'balans', 'rot', 'jant', 'hava basıncı', 'stepne' ) ),
            'klima'      => array( 'slug' => 'oto-klima-ustalari', 'name' => 'Oto Klima Ustaları', 'keys' => array( 'klima', 'kalorifer', 'kompresör', 'gaz dolumu', 'polen', 'klima gazı' ) ),
            'beyin'      => array( 'slug' => 'oto-beyin-ve-beyin-tamiri-ustasi', 'name' => 'Oto Beyin & ECU Yazılım', 'keys' => array( 'beyin', 'ecu', 'yazılım', 'yazilim', 'çip', 'tuning', 'ariza kodu', 'obd' ) ),
            'enjeksiyon' => array( 'slug' => 'oto-enjeksiyon-ustalari', 'name' => 'Oto Enjeksiyon Ustaları', 'keys' => array( 'enjektör', 'enjektor', 'enjeksiyon', 'mazot pompası', 'yakıt pompası', 'dizel pompa' ) ),
            'egzoz'      => array( 'slug' => 'egzoz-ve-emisyon-sistemleri-ustasi', 'name' => 'Egzoz & Emisyon Sistemleri', 'keys' => array( 'egzoz', 'egsoz', 'partikül', 'dpf', 'katalizör', 'emisyon', 'susturucu' ) ),
            'lpg'        => array( 'slug' => 'lpg-montaj-ustalari', 'name' => 'LPG Montaj Ustaları', 'keys' => array( 'lpg', 'otogaz', 'regülatör', 'gaz kesici', 'atiker', 'brc', 'prins' ) ),
            'kaporta'    => array( 'slug' => 'kaporta-ustalari', 'name' => 'Kaporta Ustaları', 'keys' => array( 'kaporta', 'boya', 'göçük', 'gocuk', 'pdr', 'tampon', 'çamurluk', 'çizik' ) ),
            'cam'        => array( 'slug' => 'oto-cam-ustalari', 'name' => 'Oto Cam Ustaları', 'keys' => array( 'cam', 'ön cam', 'krikosu', 'cam filmi', 'otocam' ) ),
            'motor'      => array( 'slug' => 'motor-ustalari', 'name' => 'Motor Ustaları', 'keys' => array( 'motor', 'yağ', 'triger', 'silindir', 'subap', 'radyatör', 'hararet', 'termostat', 'piston' ) ),
        );

        foreach ( $categories as $cat ) {
            foreach ( $cat['keys'] as $k ) {
                if ( strpos( $text, $k ) !== false ) {
                    return array(
                        'slug' => $cat['slug'],
                        'name' => $cat['name'],
                        'url'  => home_url( '/hizmet/' . $cat['slug'] . '/' ),
                    );
                }
            }
        }

        return array(
            'slug' => 'mekanik-tamir-bakim',
            'name' => 'Mekanik Tamir & Bakım',
            'url'  => home_url( '/hizmet/mekanik-tamir-bakim/' ),
        );
    }

    public function firms() {
        $this->verify();
        $ort      = sanitize_text_field( wp_unslash( $_POST['ort'] ?? '' ) );
        $cat_slug = sanitize_text_field( wp_unslash( $_POST['category'] ?? '' ) );
        $city     = OTB_AI_Groq::city_from_ort( $ort );
        $res      = self::render_city_mechanics( $city, 6, $cat_slug );
        if ( empty( $res['html'] ) ) {
            wp_send_json_error( [ 'message' => 'Servis bulunamadı.' ] );
        }
        wp_send_json_success( [
            'html'          => $res['html'],
            'city'          => $city,
            'category_name' => $res['category_name'],
        ] );
    }

    public static function render_city_mechanics( $city, $limit = 6, $cat_slug = '' ) {
        $tax_query = array();
        $clean_city = trim( preg_replace( '/\s+/u', ' ', $city ) );
        $cat_name = '';

        if ( ! empty( $clean_city ) ) {
            $term = get_term_by( 'name', $clean_city, 'mechanic_city' );
            if ( ! $term ) {
                $term = get_term_by( 'slug', sanitize_title( $clean_city ), 'mechanic_city' );
            }
            if ( $term ) {
                $tax_query[] = array(
                    'taxonomy' => 'mechanic_city',
                    'field'    => 'term_id',
                    'terms'    => $term->term_id,
                );
            }
        }

        // Kategori filtresi varsa ekle
        if ( ! empty( $cat_slug ) ) {
            $c_term = get_term_by( 'slug', $cat_slug, 'service_type' );
            if ( $c_term ) {
                $cat_name = $c_term->name;
                $tax_query[] = array(
                    'taxonomy' => 'service_type',
                    'field'    => 'slug',
                    'terms'    => $cat_slug,
                );
            }
        }

        $query_args = array(
            'post_type'      => 'mechanic',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'orderby'        => 'rand',
        );
        if ( ! empty( $tax_query ) ) {
            $query_args['tax_query'] = $tax_query;
        }

        $mechanics = new WP_Query( $query_args );

        // Eğer o branşta usta o şehirde yoksa sadece şehirdeki genel ustaları göster
        if ( ! $mechanics->have_posts() && ! empty( $cat_slug ) ) {
            $cat_name = '';
            // service_type filtresini kaldır, sadece şehir filtresi kalsın
            $tax_query_city_only = array();
            if ( ! empty( $clean_city ) && isset( $term ) && $term ) {
                $tax_query_city_only[] = array(
                    'taxonomy' => 'mechanic_city',
                    'field'    => 'term_id',
                    'terms'    => $term->term_id,
                );
            }
            $query_args['tax_query'] = $tax_query_city_only;
            $mechanics = new WP_Query( $query_args );
        }

        // Şehirde de usta bulunamazsa genel onaylı ustaları göster
        if ( ! $mechanics->have_posts() ) {
            unset( $query_args['tax_query'] );
            $mechanics = new WP_Query( $query_args );
        }

        if ( ! $mechanics->have_posts() ) {
            return array(
                'html'          => '<p style="color:#94a3b8; font-size:14px; text-align:center; padding:15px;">Bu bölgede henüz kayıtlı usta bulunamadı. <a href="' . esc_url( home_url( '/ustalar' ) ) . '" style="color:#f97316; font-weight:600;">Tüm ustaları görmek için tıklayın &rarr;</a></p>',
                'category_name' => $cat_name,
            );
        }

        ob_start();
        ?>
        <div class="otb-mechanic-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:16px; margin-top:16px;">
            <?php while ( $mechanics->have_posts() ) : $mechanics->the_post(); 
                $pid = get_the_ID();
                $img = get_post_meta( $pid, '_custom_mechanic_image', true );
                if ( empty( $img ) && has_post_thumbnail( $pid ) ) {
                    $img = get_the_post_thumbnail_url( $pid, 'medium' );
                }
                if ( empty( $img ) ) {
                    $img = get_template_directory_uri() . '/assets/images/placeholder-mechanic.webp';
                }
                if ( function_exists( 'ototamir_optimize_image_url' ) ) {
                    $img = ototamir_optimize_image_url( $img, 320 );
                }
                $districts = wp_get_post_terms( $pid, 'mechanic_district' );
                $dist_name = ( $districts && ! is_wp_error( $districts ) ) ? $districts[0]->name : '';
                $cities    = wp_get_post_terms( $pid, 'mechanic_city' );
                $c_name    = ( $cities && ! is_wp_error( $cities ) ) ? $cities[0]->name : $city;
                $rating    = function_exists( 'get_mechanic_rating_data' ) ? get_mechanic_rating_data( $pid ) : [ 'avg' => '5.0', 'count' => 0 ];
                $phone     = get_post_meta( $pid, '_mechanic_phone', true ) ?: get_post_meta( $pid, '_custom_mechanic_phone', true );
            ?>
            <div class="otb-mech-item" style="background:#1e293b; border:1px solid #334155; border-radius:14px; overflow:hidden; display:flex; flex-direction:column; transition:transform 0.2s ease, border-color 0.2s ease;">
                <div style="position:relative; height:140px; overflow:hidden;">
                    <img src="<?php echo esc_url( $img ); ?>" alt="<?php the_title_attribute(); ?>" style="width:100%; height:100%; object-fit:cover;" loading="lazy" decoding="async">
                    <?php if ( ! empty( $c_name ) ) : ?>
                    <span style="position:absolute; top:8px; left:8px; background:rgba(15,23,42,0.85); backdrop-filter:blur(6px); color:#f8fafc; font-size:11px; font-weight:700; padding:3px 8px; border-radius:6px; border:1px solid rgba(255,255,255,0.1);">
                        📍 <?php echo esc_html( $c_name ); ?>
                    </span>
                    <?php endif; ?>
                    <?php if ( $rating['count'] > 0 ) : ?>
                    <span style="position:absolute; top:8px; right:8px; background:rgba(245,158,11,0.95); color:#0f172a; font-size:11px; font-weight:800; padding:2px 7px; border-radius:6px;">
                        ★ <?php echo esc_html( $rating['avg'] ); ?>
                    </span>
                    <?php endif; ?>
                </div>
                <div style="padding:14px; display:flex; flex-direction:column; flex:1; justify-content:space-between;">
                    <div>
                        <h4 style="margin:0 0 6px; font-size:15px; font-weight:700; color:#f8fafc; line-height:1.3;">
                            <a href="<?php the_permalink(); ?>" style="color:#f8fafc; text-decoration:none;" target="_blank"><?php the_title(); ?></a>
                        </h4>
                        <?php if ( $dist_name ) : ?>
                        <p style="margin:0 0 10px; font-size:12px; color:#94a3b8;">
                            <?php echo esc_html( $dist_name ); ?>, <?php echo esc_html( $c_name ); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <div style="display:flex; gap:8px; align-items:center; margin-top:10px;">
                        <a href="<?php the_permalink(); ?>" style="flex:1; background:linear-gradient(135deg, #f97316, #ea580c); color:#fff; text-align:center; padding:8px 12px; border-radius:8px; font-size:12px; font-weight:700; text-decoration:none;" target="_blank">
                            Profili Gör
                        </a>
                        <?php if ( $phone ) : ?>
                        <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>" style="background:#0f172a; border:1px solid #475569; color:#4ade80; padding:8px 12px; border-radius:8px; font-size:12px; font-weight:700; text-decoration:none;" title="Hemen Ara">
                            📞 Ara
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        <div style="text-align:center; margin-top:16px;">
            <a href="<?php echo esc_url( home_url( '/ustalar/?mechanic_city=' . sanitize_title( $clean_city ) ) ); ?>" style="color:#f97316; font-size:13px; font-weight:700; text-decoration:none;" target="_blank">
                <?php echo esc_html( $clean_city ?: 'Şehir' ); ?> Bölgesindeki Tüm Ustaları İncele &rarr;
            </a>
        </div>
        <?php
        return array(
            'html'          => ob_get_clean(),
            'category_name' => $cat_name,
        );
    }
}

// Geriye dönük uyumluluk shortcode'u
add_shortcode( 'pixelas_il_ilce', function( $atts ) {
    $city = $atts['city'] ?? '';
    $res = OTB_AI_Ajax::render_city_mechanics( $city );
    return is_array( $res ) ? $res['html'] : $res;
} );
