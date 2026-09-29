<?php
/**
 * FindeWerkstatt.de - Deutschland Geodatenbank & Taxonomie-Seeder
 * 
 * Beinhaltet:
 * 1. 16 Deutsche Bundesländer & Top 100 deutsche Großstädte
 * 2. 11 Fachkategorien für den deutschen Kfz-Markt (TÜV, Freie Werkstatt, 24h Abschleppdienst etc.)
 * 3. Deutsche & internationale Automarken
 * 4. Automatische Initialisierung bei Theme-Aktivierung
 * 
 * @package FindeWerkstatt
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FindeWerkstatt_German_Seeder {

    const OPTION_SEEDED = 'findewerkstatt_de_seeded_v1';

    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_german_cpt_and_taxonomies' ), 5 );
        add_action( 'after_switch_theme', array( __CLASS__, 'auto_seed_data' ) );
        add_action( 'admin_init', array( __CLASS__, 'check_and_seed_if_needed' ) );
        add_action( 'admin_menu', array( __CLASS__, 'add_admin_tools_page' ) );
    }

    /**
     * CPT & Taxonomien für den deutschen Markt registrieren
     */
    public static function register_german_cpt_and_taxonomies() {
        // CPT: Werkstatt (mechanic)
        $labels = array(
            'name'                  => _x( 'Kfz-Werkstätten', 'Post type general name', 'findewerkstatt' ),
            'singular_name'         => _x( 'Werkstatt', 'Post type singular name', 'findewerkstatt' ),
            'menu_name'             => _x( 'Werkstätten', 'Admin Menu text', 'findewerkstatt' ),
            'name_admin_bar'        => _x( 'Werkstatt', 'Add New on Toolbar', 'findewerkstatt' ),
            'add_new'               => __( 'Neue Werkstatt eintragen', 'findewerkstatt' ),
            'add_new_item'          => __( 'Neue Werkstatt anlegen', 'findewerkstatt' ),
            'new_item'              => __( 'Neue Werkstatt', 'findewerkstatt' ),
            'edit_item'             => __( 'Werkstatt bearbeiten', 'findewerkstatt' ),
            'view_item'             => __( 'Werkstatt ansehen', 'findewerkstatt' ),
            'all_items'             => __( 'Alle Werkstätten', 'findewerkstatt' ),
            'search_items'          => __( 'Werkstatt suchen', 'findewerkstatt' ),
            'not_found'             => __( 'Keine Werkstätten gefunden.', 'findewerkstatt' ),
            'not_found_in_trash'    => __( 'Keine Werkstätten im Papierkorb.', 'findewerkstatt' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'has_archive'        => 'werkstaetten',
            'rewrite'            => array( 'slug' => 'werkstatt', 'with_front' => false ),
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-wrench',
            'supports'           => array( 'title', 'editor', 'thumbnail', 'comments', 'author' ),
            'taxonomies'         => array( 'service_type', 'car_brand', 'mechanic_city', 'mechanic_district' ),
            'show_in_rest'       => true,
        );
        register_post_type( 'mechanic', $args );

        // Taxonomie: Bundesland & Stadt (mechanic_city)
        register_taxonomy( 'mechanic_city', 'mechanic', array(
            'labels'            => array(
                'name'          => __( 'Bundesländer & Städte', 'findewerkstatt' ),
                'singular_name' => __( 'Stadt / Bundesland', 'findewerkstatt' ),
                'search_items'  => __( 'Städte suchen', 'findewerkstatt' ),
                'all_items'     => __( 'Alle Städte & Bundesländer', 'findewerkstatt' ),
                'edit_item'     => __( 'Stadt bearbeiten', 'findewerkstatt' ),
                'add_new_item'  => __( 'Neue Stadt / Bundesland hinzufügen', 'findewerkstatt' ),
            ),
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'stadt', 'with_front' => false ),
            'show_in_rest'      => true,
        ) );

        // Taxonomie: Stadtteile & Landkreise (mechanic_district)
        register_taxonomy( 'mechanic_district', 'mechanic', array(
            'labels'            => array(
                'name'          => __( 'Stadtteile & Landkreise', 'findewerkstatt' ),
                'singular_name' => __( 'Stadtteil / Landkreis', 'findewerkstatt' ),
                'search_items'  => __( 'Stadtteil suchen', 'findewerkstatt' ),
                'all_items'     => __( 'Alle Stadtteile', 'findewerkstatt' ),
            ),
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'stadtteil', 'with_front' => false ),
            'show_in_rest'      => true,
        ) );

        // Taxonomie: Dienstleistungen & Fachbereiche (service_type)
        register_taxonomy( 'service_type', 'mechanic', array(
            'labels'            => array(
                'name'          => __( 'Dienstleistungen & Kategorien', 'findewerkstatt' ),
                'singular_name' => __( 'Kategorie / Leistung', 'findewerkstatt' ),
                'search_items'  => __( 'Leistung suchen', 'findewerkstatt' ),
                'all_items'     => __( 'Alle Leistungen', 'findewerkstatt' ),
            ),
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'service', 'with_front' => false ),
            'show_in_rest'      => true,
        ) );

        // Taxonomie: Automarken (car_brand)
        register_taxonomy( 'car_brand', 'mechanic', array(
            'labels'            => array(
                'name'          => __( 'Automarken', 'findewerkstatt' ),
                'singular_name' => __( 'Marke', 'findewerkstatt' ),
                'search_items'  => __( 'Marke suchen', 'findewerkstatt' ),
                'all_items'     => __( 'Alle Marken', 'findewerkstatt' ),
            ),
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'marke', 'with_front' => false ),
            'show_in_rest'      => true,
        ) );
    }

    /**
     * Prüfen und bei Bedarf automatisch initialisieren
     */
    public static function check_and_seed_if_needed() {
        if ( ! get_option( self::OPTION_SEEDED ) ) {
            self::auto_seed_data();
        }
    }

    /**
     * Deutsche Geodaten, Kategorien und Marken anlegen
     */
    public static function auto_seed_data() {
        // 1. Kategorien (service_type)
        $services = array(
            'freie-werkstatt' => array(
                'name' => 'Freie Kfz-Werkstatt',
                'desc' => 'Unabhängige Meisterwerkstätten für herstellerübergreifende Kfz-Reparaturen, regelmäßige Inspektion nach Herstellervorgaben und Werterhalt.',
            ),
            'abschleppdienst-pannenhilfe' => array(
                'name' => 'Abschleppdienst & 24h Pannenhilfe',
                'desc' => '24-Stunden-Notdienst bei Pannen, Unfällen und Fahrzeugtransporten. Schnelle Hilfe vor Ort auf Autobahnen, Bundesstraßen und im Stadtverkehr.',
            ),
            'tuev-hu-au' => array(
                'name' => 'HU/AU & TÜV-Vorbereitung',
                'desc' => 'Hauptuntersuchung (HU) und Abgasuntersuchung (AU) durch anerkannte Prüforganisationen (TÜV, DEKRA, GTÜ, KÜS) inkl. Vorab-Check zur Mängelbeseitigung.',
            ),
            'autoglas-scheibenreparatur' => array(
                'name' => 'Autoglas & Scheibenreparatur',
                'desc' => 'Steinschlagreparatur, Windschutzscheibenaustausch und exakte Kalibrierung integrierter Fahrassistenzkameras (ADAS) aller Hersteller.',
            ),
            'karosserie-lackiererei' => array(
                'name' => 'Karosserie & Lackiererei',
                'desc' => 'Fachmännische Unfallinstandsetzung, Smart Repair, Dellenbeseitigung ohne Lackieren (Hagelschaden) und hochwertige Fahrzeuglackierungen.',
            ),
            'reifenservice-raederwechsel' => array(
                'name' => 'Reifenservice & Räderwechsel',
                'desc' => 'Saisonaler Radwechsel (Sommer/Winter), Auswuchten, Reifendruck-Kontrollsysteme (RDKS), Neureifenmontage und fachgerechte Rädereinlagerung.',
            ),
            'kfz-elektrik-elektronik' => array(
                'name' => 'Kfz-Elektrik & Diagnose',
                'desc' => 'Computergestützte OBD-II-Fehlerauslese, Steuergeräte-Reparatur, Lichtmaschinen- und Starterprüfung sowie Instandsetzung komplexer Fahrzeugelektronik.',
            ),
            'klimaservice-standheizung' => array(
                'name' => 'Klimaservice & Standheizung',
                'desc' => 'Klimaanlagenwartung, Kältemittelnachfüllung (R134a & R1234yf), Ozon-Desinfektion gegen Gerüche und Einbau/Wartung von Standheizungen.',
            ),
            'bremsenservice-fahrwerk' => array(
                'name' => 'Bremsenservice & Fahrwerk',
                'desc' => 'Austausch von Bremsscheiben und Bremsbelägen, Bremsflüssigkeitswechsel, Stoßdämpferprüfung und präzise elektronische 3D-Achsvermessung.',
            ),
            'motor-getriebeinstandsetzung' => array(
                'name' => 'Motor- & Getriebeinstandsetzung',
                'desc' => 'Zahnriemenwechsel, Kupplungserneuerung, Zylinderkopfdichtung, Automatikgetriebespülung nach Tim-Eckart-Methode und Motorüberholungen.',
            ),
            'e-auto-ladestationen' => array(
                'name' => 'E-Mobilität & Ladestationen',
                'desc' => 'Zertifizierte Hochvolt-Fachbetriebe für Elektro- und Hybridfahrzeuge, Batteriegesundheits-Checks und öffentlich zugängliche Schnellladestationen.',
            ),
        );

        foreach ( $services as $slug => $data ) {
            if ( ! term_exists( $slug, 'service_type' ) ) {
                wp_insert_term( $data['name'], 'service_type', array(
                    'slug'        => $slug,
                    'description' => $data['desc'],
                ) );
            }
        }

        // 2. Automarken (car_brand)
        $brands = array(
            'Volkswagen'    => 'volkswagen',
            'BMW'           => 'bmw',
            'Mercedes-Benz' => 'mercedes-benz',
            'Audi'          => 'audi',
            'Opel'          => 'opel',
            'Ford'          => 'ford',
            'Porsche'       => 'porsche',
            'Skoda'         => 'skoda',
            'Seat'          => 'seat',
            'Renault'       => 'renault',
            'Peugeot'       => 'peugeot',
            'Toyota'        => 'toyota',
            'Hyundai'       => 'hyundai',
            'Kia'           => 'kia',
            'Fiat'          => 'fiat',
            'Volvo'         => 'volvo',
            'Mazda'         => 'mazda',
            'Nissan'        => 'nissan',
            'Tesla'         => 'tesla',
            'Dacia'         => 'dacia',
            'Mini'          => 'mini',
            'Honda'         => 'honda',
            'Citroën'       => 'citroen',
            'Suzuki'        => 'suzuki',
            'Alfa Romeo'    => 'alfa-romeo',
            'Jeep'          => 'jeep',
            'Land Rover'    => 'land-rover',
        );

        foreach ( $brands as $name => $slug ) {
            if ( ! term_exists( $slug, 'car_brand' ) ) {
                wp_insert_term( $name, 'car_brand', array( 'slug' => $slug ) );
            }
        }

        // 3. 16 Bundesländer und deutsche Großstädte (mechanic_city)
        $bundeslaender = array(
            'Baden-Württemberg' => array(
                'slug'   => 'baden-wuerttemberg',
                'cities' => array( 'Stuttgart', 'Karlsruhe', 'Mannheim', 'Freiburg im Breisgau', 'Heidelberg', 'Ulm', 'Heilbronn', 'Pforzheim', 'Reutlingen', 'Esslingen am Neckar', 'Tübingen', 'Konstanz' ),
            ),
            'Bayern' => array(
                'slug'   => 'bayern',
                'cities' => array( 'München', 'Nürnberg', 'Augsburg', 'Regensburg', 'Ingolstadt', 'Würzburg', 'Fürth', 'Erlangen', 'Bamberg', 'Bayreuth', 'Aschaffenburg', 'Kempten' ),
            ),
            'Berlin' => array(
                'slug'   => 'berlin',
                'cities' => array( 'Berlin' ),
            ),
            'Brandenburg' => array(
                'slug'   => 'brandenburg',
                'cities' => array( 'Potsdam', 'Cottbus', 'Brandenburg an der Havel', 'Frankfurt (Oder)', 'Oranienburg' ),
            ),
            'Bremen' => array(
                'slug'   => 'bremen',
                'cities' => array( 'Bremen', 'Bremerhaven' ),
            ),
            'Hamburg' => array(
                'slug'   => 'hamburg',
                'cities' => array( 'Hamburg' ),
            ),
            'Hessen' => array(
                'slug'   => 'hessen',
                'cities' => array( 'Frankfurt am Main', 'Wiesbaden', 'Kassel', 'Darmstadt', 'Offenbach am Main', 'Hanau', 'Gießen', 'Marburg', 'Fulda' ),
            ),
            'Mecklenburg-Vorpommern' => array(
                'slug'   => 'mecklenburg-vorpommern',
                'cities' => array( 'Rostock', 'Schwerin', 'Neubrandenburg', 'Stralsund', 'Greifswald' ),
            ),
            'Niedersachsen' => array(
                'slug'   => 'niedersachsen',
                'cities' => array( 'Hannover', 'Braunschweig', 'Oldenburg', 'Osnabrück', 'Wolfsburg', 'Göttingen', 'Salzgitter', 'Hildesheim', 'Delmenhorst', 'Wilhelmshaven', 'Lüneburg' ),
            ),
            'Nordrhein-Westfalen' => array(
                'slug'   => 'nordrhein-westfalen',
                'cities' => array( 'Köln', 'Düsseldorf', 'Dortmund', 'Essen', 'Duisburg', 'Bochum', 'Wuppertal', 'Bielefeld', 'Bonn', 'Münster', 'Gelsenkirchen', 'Mönchengladbach', 'Aachen', 'Krefeld', 'Oberhausen', 'Hagen', 'Hamm', 'Mülheim an der Ruhr', 'Leverkusen', 'Solingen', 'Herne', 'Neuss', 'Paderborn', 'Recklinghausen', 'Bottrop', 'Remscheid', 'Bergisch Gladbach', 'Moers', 'Siegen' ),
            ),
            'Rheinland-Pfalz' => array(
                'slug'   => 'rheinland-pfalz',
                'cities' => array( 'Mainz', 'Ludwigshafen am Rhein', 'Koblenz', 'Trier', 'Kaiserslautern', 'Worms', 'Neuwied' ),
            ),
            'Saarland' => array(
                'slug'   => 'saarland',
                'cities' => array( 'Saarbrücken', 'Neunkirchen', 'Homburg', 'Völklingen' ),
            ),
            'Sachsen' => array(
                'slug'   => 'sachsen',
                'cities' => array( 'Leipzig', 'Dresden', 'Chemnitz', 'Zwickau', 'Plauen', 'Görlitz' ),
            ),
            'Sachsen-Anhalt' => array(
                'slug'   => 'sachsen-anhalt',
                'cities' => array( 'Halle (Saale)', 'Magdeburg', 'Dessau-Roßlau', 'Lutherstadt Wittenberg' ),
            ),
            'Schleswig-Holstein' => array(
                'slug'   => 'schleswig-holstein',
                'cities' => array( 'Kiel', 'Lübeck', 'Flensburg', 'Neumünster', 'Norderstedt' ),
            ),
            'Thüringen' => array(
                'slug'   => 'thueringen',
                'cities' => array( 'Erfurt', 'Jena', 'Gera', 'Weimar', 'Eisenach', 'Nordhausen' ),
            ),
        );

        foreach ( $bundeslaender as $land_name => $land_data ) {
            $land_term = term_exists( $land_data['slug'], 'mechanic_city' );
            $parent_id = 0;

            if ( ! $land_term ) {
                $inserted = wp_insert_term( $land_name, 'mechanic_city', array(
                    'slug'        => $land_data['slug'],
                    'description' => "Kfz-Werkstätten, 24h Abschleppdienste und Kfz-Meisterbetriebe im Bundesland {$land_name}.",
                ) );
                if ( ! is_wp_error( $inserted ) && isset( $inserted['term_id'] ) ) {
                    $parent_id = (int) $inserted['term_id'];
                }
            } else {
                $parent_id = is_array( $land_term ) ? (int) $land_term['term_id'] : (int) $land_term;
            }

            // Städte unter dem jeweiligen Bundesland anlegen
            foreach ( $land_data['cities'] as $city_name ) {
                $city_slug = sanitize_title( $city_name );
                if ( ! term_exists( $city_slug, 'mechanic_city' ) ) {
                    wp_insert_term( $city_name, 'mechanic_city', array(
                        'slug'        => $city_slug,
                        'parent'      => $parent_id,
                        'description' => "Geprüfte Kfz-Werkstätten, Pannenhilfe und Autoreparatur in {$city_name} ({$land_name}).",
                    ) );
                }
            }
        }

        update_option( self::OPTION_SEEDED, 1 );
    }

    /**
     * WP-Admin Menü für Geodaten-Tools
     */
    public static function add_admin_tools_page() {
        add_submenu_page(
            'edit.php?post_type=mechanic',
            __( 'Deutschland Geodaten & Setup', 'findewerkstatt' ),
            __( '🇩🇪 Deutschland Setup', 'findewerkstatt' ),
            'manage_options',
            'findewerkstatt-setup',
            array( __CLASS__, 'render_admin_page' )
        );
    }

    public static function render_admin_page() {
        if ( isset( $_POST['reseed_data'] ) && check_admin_referer( 'findewerkstatt_reseed' ) ) {
            self::auto_seed_data();
            echo '<div class="notice notice-success is-dismissible"><p><strong>Erfolg!</strong> 16 Bundesländer, Großstädte und Kfz-Fachkategorien wurden erfolgreich initialisiert.</p></div>';
        }

        $cities_count   = wp_count_terms( array( 'taxonomy' => 'mechanic_city', 'hide_empty' => false ) );
        $services_count = wp_count_terms( array( 'taxonomy' => 'service_type', 'hide_empty' => false ) );
        $brands_count   = wp_count_terms( array( 'taxonomy' => 'car_brand', 'hide_empty' => false ) );
        ?>
        <div class="wrap">
            <h1 style="display:flex; align-items:center; gap:10px;">
                <span>🇩🇪</span> FindeWerkstatt.de — Deutschland Setup & Geodatenbank
            </h1>
            <p>Herzlich willkommen bei FindeWerkstatt.de! Dieses Modul verwaltet die 16 Bundesländer, Top 100 Städte und die 11 spezialisierten deutschen Kfz-Fachbereiche.</p>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:18px; margin:24px 0;">
                <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:20px; border-left:4px solid #0284c7;">
                    <div style="font-size:12px; font-weight:700; color:#0369a1; text-transform:uppercase;">Städte & Bundesländer</div>
                    <div style="font-size:32px; font-weight:900; color:#0f172a; margin:8px 0;"><?php echo esc_html( $cities_count ); ?></div>
                    <div style="font-size:12.5px; color:#64748b;">16 Bundesländer + deutsche Großstädte</div>
                </div>

                <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:20px; border-left:4px solid #16a34a;">
                    <div style="font-size:12px; font-weight:700; color:#15803d; text-transform:uppercase;">Kfz-Fachkategorien</div>
                    <div style="font-size:32px; font-weight:900; color:#0f172a; margin:8px 0;"><?php echo esc_html( $services_count ); ?></div>
                    <div style="font-size:12.5px; color:#64748b;">TÜV/HU, Freie Werkstatt, 24h Abschleppdienst etc.</div>
                </div>

                <div style="background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:20px; border-left:4px solid #ea580c;">
                    <div style="font-size:12px; font-weight:700; color:#c2410c; text-transform:uppercase;">Automarken</div>
                    <div style="font-size:32px; font-weight:900; color:#0f172a; margin:8px 0;"><?php echo esc_html( $brands_count ); ?></div>
                    <div style="font-size:12.5px; color:#64748b;">VW, BMW, Mercedes, Audi, Opel etc.</div>
                </div>
            </div>

            <div style="background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:24px; max-width:800px;">
                <h3 style="margin-top:0;">🔄 Daten neu synchronisieren</h3>
                <p>Falls Kategorien oder Bundesländer gelöscht wurden, können Sie diese hier mit einem Klick wiederherstellen:</p>
                <form method="post">
                    <?php wp_nonce_field( 'findewerkstatt_reseed' ); ?>
                    <button type="submit" name="reseed_data" value="1" class="button button-primary button-large" style="background:#0f172a; border-color:#0f172a; font-weight:700;">
                        🇩🇪 Deutschland-Daten jetzt synchronisieren
                    </button>
                </form>
            </div>
        </div>
        <?php
    }
}

FindeWerkstatt_German_Seeder::init();
