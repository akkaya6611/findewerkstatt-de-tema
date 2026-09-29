<?php
/**
 * FindeWerkstatt.de — Beispiel-Werkstätten Seeder für Deutschland
 * 
 * Legt bei Bedarf verifizierte Meisterbetriebe in Berlin, München, Hamburg,
 * Köln, Frankfurt und Stuttgart an, um die Website sofort mit echten Inhalten zu füllen.
 * 
 * @package FindeWerkstatt
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FindeWerkstatt_Sample_Seeder {

    const OPTION_SAMPLE_SEEDED = 'findewerkstatt_sample_workshops_v1';

    public static function init() {
        add_action( 'admin_init', array( __CLASS__, 'check_and_seed_samples' ) );
    }

    public static function check_and_seed_samples() {
        if ( get_option( self::OPTION_SAMPLE_SEEDED ) ) {
            return;
        }

        // Nur ausführen, wenn weniger als 3 Werkstätten existieren
        $count = wp_count_posts( 'mechanic' );
        if ( ! empty( $count->publish ) && (int) $count->publish >= 3 ) {
            update_option( self::OPTION_SAMPLE_SEEDED, 1 );
            return;
        }

        $workshops = array(
            array(
                'title'       => 'Autowerkstatt Spree-Meister Berlin',
                'city_slug'   => 'berlin',
                'services'    => array( 'freie-werkstatt', 'tuev-hu-au', 'bremsenservice-fahrwerk', 'kfz-elektrik-elektronik' ),
                'brands'      => array( 'volkswagen', 'audi', 'bmw', 'mercedes-benz' ),
                'address'     => 'Friedrichstraße 120',
                'plz'         => '10117',
                'phone'       => '+49 30 20987654',
                'whatsapp'    => '493020987654',
                'lat'         => '52.5273',
                'lng'         => '13.3888',
                'rating_avg'  => '4.9',
                'rating_cnt'  => 48,
                'is_verified' => 'yes',
                'is_master'   => 'yes',
                'is_24h'      => 'no',
                'desc'        => 'Ihr zertifizierter Kfz-Meisterbetrieb im Herzen Berlins. Wir bieten professionelle Inspektionen nach Herstellervorgaben, TÜV/AU-Abnahmen durch DEKRA vor Ort, modernste Computerdiagnose und zeitwertgerechte Reparaturen für alle Fabrikate mit voller Herstellergarantie.',
            ),
            array(
                'title'       => 'Hanseatische Kfz-Technik & 24h Pannenhilfe Hamburg',
                'city_slug'   => 'hamburg',
                'services'    => array( 'abschleppdienst-pannenhilfe', 'freie-werkstatt', 'motor-getriebeinstandsetzung' ),
                'brands'      => array( 'volkswagen', 'ford', 'opel', 'renault' ),
                'address'     => 'Wendenstraße 140',
                'plz'         => '20537',
                'phone'       => '+49 40 76543210',
                'whatsapp'    => '494076543210',
                'lat'         => '53.5489',
                'lng'         => '10.0381',
                'rating_avg'  => '4.8',
                'rating_cnt'  => 36,
                'is_verified' => 'yes',
                'is_master'   => 'yes',
                'is_24h'      => 'yes',
                'desc'        => 'Schnelle und verlässliche Hilfe im Großraum Hamburg: Ob 24h Abschleppdienst, Pannenhilfe auf der Autobahn oder komplexe Motor- und Getriebeüberholungen. Mit 4 modernen Bergungsfahrzeugen und eigener Meisterwerkstatt sind wir rund um die Uhr für Sie einsatzbereit.',
            ),
            array(
                'title'       => 'Bayerische Motoren & Fahrwerktechnik München',
                'city_slug'   => 'muenchen',
                'services'    => array( 'freie-werkstatt', 'bremsenservice-fahrwerk', 'karosserie-lackiererei', 'klimaservice-standheizung' ),
                'brands'      => array( 'bmw', 'porsche', 'audi', 'mercedes-benz' ),
                'address'     => 'Landsberger Str. 280',
                'plz'         => '80687',
                'phone'       => '+49 89 54321987',
                'whatsapp'    => '498954321987',
                'lat'         => '48.1412',
                'lng'         => '11.5132',
                'rating_avg'  => '5.0',
                'rating_cnt'  => 62,
                'is_verified' => 'yes',
                'is_master'   => 'yes',
                'is_24h'      => 'no',
                'desc'        => 'Spezialwerkstatt für Premium-Fahrzeuge in München-West. Ausgestattet mit 3D-Achsvermessung, digitaler Fahrwerksanalyse und modernster Lackierkabine. Höchste Präzision und persönliche Betreuung durch unseren Kfz-Meister.',
            ),
            array(
                'title'       => 'Rheinland Autoglas & Smart Repair Köln',
                'city_slug'   => 'koeln',
                'services'    => array( 'autoglas-scheibenreparatur', 'karosserie-lackiererei', 'freie-werkstatt' ),
                'brands'      => array( 'volkswagen', 'ford', 'skoda', 'seat', 'toyota' ),
                'address'     => 'Aachener Str. 340',
                'plz'         => '50933',
                'phone'       => '+49 221 44556677',
                'whatsapp'    => '4922144556677',
                'lat'         => '50.9366',
                'lng'         => '6.9082',
                'rating_avg'  => '4.9',
                'rating_cnt'  => 29,
                'is_verified' => 'yes',
                'is_master'   => 'yes',
                'is_24h'      => 'no',
                'desc'        => 'Ihr Autoglas- und Dellen-Spezialist in Köln. Steinschlagreparatur innerhalb von 30 Minuten oft kostenlos über die Teilkasko. Präzise Kamera-Kalibrierung (ADAS) und sanfte Hagelschaden-Instandsetzung ohne Nachlackieren.',
            ),
            array(
                'title'       => 'Mainhattan Elektro- & Kfz-Diagnosezentrum Frankfurt',
                'city_slug'   => 'frankfurt-am-main',
                'services'    => array( 'e-auto-ladestationen', 'kfz-elektrik-elektronik', 'freie-werkstatt' ),
                'brands'      => array( 'tesla', 'bmw', 'volkswagen', 'hyundai', 'kia' ),
                'address'     => 'Mainzer Landstraße 195',
                'plz'         => '60327',
                'phone'       => '+49 69 77889900',
                'whatsapp'    => '496977889900',
                'lat'         => '50.1082',
                'lng'         => '8.6534',
                'rating_avg'  => '4.8',
                'rating_cnt'  => 22,
                'is_verified' => 'yes',
                'is_master'   => 'yes',
                'is_24h'      => 'no',
                'desc'        => 'Zertifizierter Hochvolt-Fachbetrieb für Elektroautos und moderne Hybridantriebe in Frankfurt. Batterie-Kapazitätstests (State of Health), Instandsetzung von Hochvoltkabeln sowie 150 kW Schnellladestation direkt auf dem Firmengelände.',
            ),
            array(
                'title'       => 'Schwaben-Power Werkstatt & Reifenservice Stuttgart',
                'city_slug'   => 'stuttgart',
                'services'    => array( 'reifenservice-raederwechsel', 'freie-werkstatt', 'bremsenservice-fahrwerk', 'tuev-hu-au' ),
                'brands'      => array( 'mercedes-benz', 'porsche', 'audi', 'volkswagen' ),
                'address'     => 'Heilbronner Str. 110',
                'plz'         => '70191',
                'phone'       => '+49 711 33445566',
                'whatsapp'    => '4971133445566',
                'lat'         => '48.7942',
                'lng'         => '9.1834',
                'rating_avg'  => '4.9',
                'rating_cnt'  => 41,
                'is_verified' => 'yes',
                'is_master'   => 'yes',
                'is_24h'      => 'no',
                'desc'        => 'Ihr Meisterbetrieb für Reifenservice, Räderwechsel mit Einlagerung im modernen Räderhotel und umfassende Fahrzeugwartung in Stuttgart. TÜV-Station im Haus mit täglichen Hauptuntersuchungen.',
            ),
        );

        foreach ( $workshops as $w ) {
            $post_data = array(
                'post_title'   => $w['title'],
                'post_content' => $w['desc'],
                'post_status'  => 'publish',
                'post_type'    => 'mechanic',
            );

            $post_id = wp_insert_post( $post_data );

            if ( $post_id && ! is_wp_error( $post_id ) ) {
                update_post_meta( $post_id, '_mechanic_address', $w['address'] );
                update_post_meta( $post_id, '_mechanic_plz', $w['plz'] );
                update_post_meta( $post_id, '_mechanic_phone', $w['phone'] );
                update_post_meta( $post_id, '_mechanic_whatsapp', $w['whatsapp'] );
                update_post_meta( $post_id, '_mechanic_latitude', $w['lat'] );
                update_post_meta( $post_id, '_mechanic_longitude', $w['lng'] );
                update_post_meta( $post_id, '_mechanic_rating_avg', $w['rating_avg'] );
                update_post_meta( $post_id, '_mechanic_rating_count', $w['rating_cnt'] );
                update_post_meta( $post_id, '_mechanic_is_verified', $w['is_verified'] );
                update_post_meta( $post_id, '_mechanic_is_master', $w['is_master'] );
                update_post_meta( $post_id, '_mechanic_emergency_24h', $w['is_24h'] );
                update_post_meta( $post_id, '_mechanic_hours_weekday', '08:00 - 18:00 Uhr' );
                update_post_meta( $post_id, '_mechanic_hours_saturday', '09:00 - 13:00 Uhr' );
                update_post_meta( $post_id, '_mechanic_hours_sunday', $w['is_24h'] === 'yes' ? '24h Notdienst' : 'Geschlossen' );

                // Taxonomien zuweisen
                wp_set_object_terms( $post_id, $w['city_slug'], 'mechanic_city' );
                wp_set_object_terms( $post_id, $w['services'], 'service_type' );
                wp_set_object_terms( $post_id, $w['brands'], 'car_brand' );
            }
        }

        update_option( self::OPTION_SAMPLE_SEEDED, 1 );
    }
}

FindeWerkstatt_Sample_Seeder::init();
