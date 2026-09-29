<?php
/**
 * FindeWerkstatt.de — Deutschland Geodaten & Kfz-Kategorien
 * 
 * 1. 16 Deutsche Bundesländer (ISO 3166-2:DE)
 * 2. Top 100 Deutsche Großstädte mit GPS-Koordinaten
 * 3. 11 Fachkategorien für den deutschen Kfz-Markt
 * 4. Automarken für Werkstatt-Spezialisierungen
 * 
 * @package FindeWerkstatt
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FindeWerkstatt_German_Data {

    /**
     * 16 Deutsche Bundesländer
     */
    public static function get_bundeslaender() {
        return array(
            'DE-BW' => array( 'name' => 'Baden-Württemberg',       'slug' => 'baden-wuerttemberg',     'capital' => 'Stuttgart',  'lat' => 48.7758, 'lng' => 9.1829 ),
            'DE-BY' => array( 'name' => 'Bayern',                  'slug' => 'bayern',                'capital' => 'München',    'lat' => 48.1351, 'lng' => 11.5820 ),
            'DE-BE' => array( 'name' => 'Berlin',                  'slug' => 'berlin',                'capital' => 'Berlin',     'lat' => 52.5200, 'lng' => 13.4050 ),
            'DE-BB' => array( 'name' => 'Brandenburg',             'slug' => 'brandenburg',           'capital' => 'Potsdam',    'lat' => 52.3906, 'lng' => 13.0645 ),
            'DE-HB' => array( 'name' => 'Bremen',                  'slug' => 'bremen',                'capital' => 'Bremen',     'lat' => 53.0793, 'lng' => 8.8017 ),
            'DE-HH' => array( 'name' => 'Hamburg',                 'slug' => 'hamburg',               'capital' => 'Hamburg',    'lat' => 53.5511, 'lng' => 9.9937 ),
            'DE-HE' => array( 'name' => 'Hessen',                  'slug' => 'hessen',                'capital' => 'Wiesbaden',  'lat' => 50.0782, 'lng' => 8.2398 ),
            'DE-MV' => array( 'name' => 'Mecklenburg-Vorpommern',  'slug' => 'mecklenburg-vorpommern', 'capital' => 'Schwerin',   'lat' => 53.6355, 'lng' => 11.4012 ),
            'DE-NI' => array( 'name' => 'Niedersachsen',           'slug' => 'niedersachsen',         'capital' => 'Hannover',   'lat' => 52.3759, 'lng' => 9.7320 ),
            'DE-NW' => array( 'name' => 'Nordrhein-Westfalen',     'slug' => 'nordrhein-westfalen',   'capital' => 'Düsseldorf', 'lat' => 51.2277, 'lng' => 6.7735 ),
            'DE-RP' => array( 'name' => 'Rheinland-Pfalz',         'slug' => 'rheinland-pfalz',       'capital' => 'Mainz',      'lat' => 49.9929, 'lng' => 8.2473 ),
            'DE-SL' => array( 'name' => 'Saarland',                'slug' => 'saarland',              'capital' => 'Saarbrücken','lat' => 49.2402, 'lng' => 6.9969 ),
            'DE-SN' => array( 'name' => 'Sachsen',                 'slug' => 'sachsen',               'capital' => 'Dresden',    'lat' => 51.0504, 'lng' => 13.7373 ),
            'DE-ST' => array( 'name' => 'Sachsen-Anhalt',          'slug' => 'sachsen-anhalt',        'capital' => 'Magdeburg',  'lat' => 52.1205, 'lng' => 11.6276 ),
            'DE-SH' => array( 'name' => 'Schleswig-Holstein',      'slug' => 'schleswig-holstein',    'capital' => 'Kiel',       'lat' => 54.3233, 'lng' => 10.1228 ),
            'DE-TH' => array( 'name' => 'Thüringen',               'slug' => 'thueringen',            'capital' => 'Erfurt',     'lat' => 50.9848, 'lng' => 11.0299 ),
        );
    }

    /**
     * Top 100 Deutsche Städte mit Bundesland-Zuordnung und GPS-Koordinaten
     */
    public static function get_top_cities() {
        return array(
            'berlin'           => array( 'name' => 'Berlin',               'land' => 'berlin',              'lat' => 52.5200, 'lng' => 13.4050, 'pop' => 3850000 ),
            'hamburg'          => array( 'name' => 'Hamburg',              'land' => 'hamburg',             'lat' => 53.5511, 'lng' => 9.9937,  'pop' => 1900000 ),
            'muenchen'         => array( 'name' => 'München',              'land' => 'bayern',              'lat' => 48.1351, 'lng' => 11.5820, 'pop' => 1500000 ),
            'koeln'            => array( 'name' => 'Köln',                 'land' => 'nordrhein-westfalen', 'lat' => 50.9375, 'lng' => 6.9603,  'pop' => 1080000 ),
            'frankfurt-am-main'=> array( 'name' => 'Frankfurt am Main',    'land' => 'hessen',              'lat' => 50.1109, 'lng' => 8.6821,  'pop' => 760000 ),
            'stuttgart'        => array( 'name' => 'Stuttgart',            'land' => 'baden-wuerttemberg',  'lat' => 48.7758, 'lng' => 9.1829,  'pop' => 630000 ),
            'duesseldorf'      => array( 'name' => 'Düsseldorf',           'land' => 'nordrhein-westfalen', 'lat' => 51.2277, 'lng' => 6.7735,  'pop' => 620000 ),
            'leipzig'          => array( 'name' => 'Leipzig',              'land' => 'sachsen',             'lat' => 51.3397, 'lng' => 12.3731, 'pop' => 600000 ),
            'dortmund'         => array( 'name' => 'Dortmund',             'land' => 'nordrhein-westfalen', 'lat' => 51.5136, 'lng' => 7.4653,  'pop' => 590000 ),
            'essen'            => array( 'name' => 'Essen',                'land' => 'nordrhein-westfalen', 'lat' => 51.4556, 'lng' => 7.0116,  'pop' => 580000 ),
            'bremen'           => array( 'name' => 'Bremen',               'land' => 'bremen',              'lat' => 53.0793, 'lng' => 8.8017,  'pop' => 570000 ),
            'dresden'          => array( 'name' => 'Dresden',              'land' => 'sachsen',             'lat' => 51.0504, 'lng' => 13.7373, 'pop' => 560000 ),
            'hannover'         => array( 'name' => 'Hannover',             'land' => 'niedersachsen',       'lat' => 52.3759, 'lng' => 9.7320,  'pop' => 540000 ),
            'nuernberg'        => array( 'name' => 'Nürnberg',             'land' => 'bayern',              'lat' => 49.4521, 'lng' => 11.0767, 'pop' => 520000 ),
            'duisburg'         => array( 'name' => 'Duisburg',             'land' => 'nordrhein-westfalen', 'lat' => 51.4344, 'lng' => 6.7623,  'pop' => 500000 ),
            'bochum'           => array( 'name' => 'Bochum',               'land' => 'nordrhein-westfalen', 'lat' => 51.4818, 'lng' => 7.2162,  'pop' => 365000 ),
            'wuppertal'        => array( 'name' => 'Wuppertal',            'land' => 'nordrhein-westfalen', 'lat' => 51.2562, 'lng' => 7.1508,  'pop' => 355000 ),
            'bielefeld'        => array( 'name' => 'Bielefeld',            'land' => 'nordrhein-westfalen', 'lat' => 52.0302, 'lng' => 8.5325,  'pop' => 334000 ),
            'bonn'             => array( 'name' => 'Bonn',                 'land' => 'nordrhein-westfalen', 'lat' => 50.7374, 'lng' => 7.0982,  'pop' => 330000 ),
            'muenster'         => array( 'name' => 'Münster',              'land' => 'nordrhein-westfalen', 'lat' => 51.9607, 'lng' => 7.6261,  'pop' => 315000 ),
            'karlsruhe'        => array( 'name' => 'Karlsruhe',            'land' => 'baden-wuerttemberg',  'lat' => 49.0069, 'lng' => 8.4037,  'pop' => 310000 ),
            'mannheim'         => array( 'name' => 'Mannheim',             'land' => 'baden-wuerttemberg',  'lat' => 49.4875, 'lng' => 8.4660,  'pop' => 310000 ),
            'augsburg'         => array( 'name' => 'Augsburg',             'land' => 'bayern',              'lat' => 48.3705, 'lng' => 10.8978, 'pop' => 300000 ),
            'wiesbaden'        => array( 'name' => 'Wiesbaden',            'land' => 'hessen',              'lat' => 50.0782, 'lng' => 8.2398,  'pop' => 280000 ),
            'gelsenkirchen'    => array( 'name' => 'Gelsenkirchen',        'land' => 'nordrhein-westfalen', 'lat' => 51.5177, 'lng' => 7.0857,  'pop' => 260000 ),
            'moenchengladbach' => array( 'name' => 'Mönchengladbach',     'land' => 'nordrhein-westfalen', 'lat' => 51.1805, 'lng' => 6.4428,  'pop' => 260000 ),
            'braunschweig'     => array( 'name' => 'Braunschweig',         'land' => 'niedersachsen',       'lat' => 52.2689, 'lng' => 10.5268, 'pop' => 250000 ),
            'chemnitz'         => array( 'name' => 'Chemnitz',             'land' => 'sachsen',             'lat' => 50.8278, 'lng' => 12.9214, 'pop' => 245000 ),
            'kiel'             => array( 'name' => 'Kiel',                 'land' => 'schleswig-holstein',    'lat' => 54.3233, 'lng' => 10.1228, 'pop' => 245000 ),
            'aachen'           => array( 'name' => 'Aachen',               'land' => 'nordrhein-westfalen', 'lat' => 50.7753, 'lng' => 6.0839,  'pop' => 250000 ),
            'halle-saale'      => array( 'name' => 'Halle (Saale)',        'land' => 'sachsen-anhalt',        'lat' => 51.4828, 'lng' => 11.9700, 'pop' => 240000 ),
            'magdeburg'        => array( 'name' => 'Magdeburg',            'land' => 'sachsen-anhalt',        'lat' => 52.1205, 'lng' => 11.6276, 'pop' => 240000 ),
            'freiburg'         => array( 'name' => 'Freiburg im Breisgau', 'land' => 'baden-wuerttemberg',  'lat' => 47.9990, 'lng' => 7.8421,  'pop' => 230000 ),
            'krefeld'          => array( 'name' => 'Krefeld',              'land' => 'nordrhein-westfalen', 'lat' => 51.3388, 'lng' => 6.5853,  'pop' => 230000 ),
            'luebeck'          => array( 'name' => 'Lübeck',               'land' => 'schleswig-holstein',    'lat' => 53.8655, 'lng' => 10.6866, 'pop' => 220000 ),
            'oberhausen'       => array( 'name' => 'Oberhausen',           'land' => 'nordrhein-westfalen', 'lat' => 51.4782, 'lng' => 6.8587,  'pop' => 210000 ),
            'erfurt'           => array( 'name' => 'Erfurt',               'land' => 'thueringen',            'lat' => 50.9848, 'lng' => 11.0299, 'pop' => 215000 ),
            'mainz'            => array( 'name' => 'Mainz',                'land' => 'rheinland-pfalz',       'lat' => 49.9929, 'lng' => 8.2473,  'pop' => 218000 ),
            'rostock'          => array( 'name' => 'Rostock',              'land' => 'mecklenburg-vorpommern', 'lat' => 54.0924, 'lng' => 12.0991, 'pop' => 210000 ),
            'kassel'           => array( 'name' => 'Kassel',               'land' => 'hessen',              'lat' => 51.3127, 'lng' => 9.4797,  'pop' => 200000 ),
            'hagen'            => array( 'name' => 'Hagen',                'land' => 'nordrhein-westfalen', 'lat' => 51.3671, 'lng' => 7.4633,  'pop' => 190000 ),
            'potsdam'          => array( 'name' => 'Potsdam',              'land' => 'brandenburg',           'lat' => 52.3906, 'lng' => 13.0645, 'pop' => 185000 ),
            'saarbruecken'     => array( 'name' => 'Saarbrücken',          'land' => 'saarland',              'lat' => 49.2402, 'lng' => 6.9969,  'pop' => 180000 ),
            'hamm'             => array( 'name' => 'Hamm',                 'land' => 'nordrhein-westfalen', 'lat' => 51.6812, 'lng' => 7.8188,  'pop' => 180000 ),
            'ludwigshafen'     => array( 'name' => 'Ludwigshafen',         'land' => 'rheinland-pfalz',       'lat' => 49.4811, 'lng' => 8.4464,  'pop' => 170000 ),
            'muelheim'         => array( 'name' => 'Mülheim an der Ruhr',  'land' => 'nordrhein-westfalen', 'lat' => 51.4272, 'lng' => 6.8829,  'pop' => 170000 ),
            'oldenburg'        => array( 'name' => 'Oldenburg',            'land' => 'niedersachsen',       'lat' => 53.1435, 'lng' => 8.2146,  'pop' => 170000 ),
            'osnabrueck'       => array( 'name' => 'Osnabrück',            'land' => 'niedersachsen',       'lat' => 52.2799, 'lng' => 8.0472,  'pop' => 165000 ),
            'leverkusen'       => array( 'name' => 'Leverkusen',           'land' => 'nordrhein-westfalen', 'lat' => 51.0459, 'lng' => 7.0192,  'pop' => 165000 ),
            'heidelberg'       => array( 'name' => 'Heidelberg',           'land' => 'baden-wuerttemberg',  'lat' => 49.3988, 'lng' => 8.6724,  'pop' => 160000 ),
            'darmstadt'        => array( 'name' => 'Darmstadt',            'land' => 'hessen',              'lat' => 49.8728, 'lng' => 8.6512,  'pop' => 160000 ),
            'solingen'         => array( 'name' => 'Solingen',             'land' => 'nordrhein-westfalen', 'lat' => 51.1719, 'lng' => 7.0845,  'pop' => 160000 ),
            'regensburg'       => array( 'name' => 'Regensburg',           'land' => 'bayern',              'lat' => 49.0134, 'lng' => 12.1016, 'pop' => 155000 ),
            'ingolstadt'       => array( 'name' => 'Ingolstadt',           'land' => 'bayern',              'lat' => 48.7665, 'lng' => 11.4258, 'pop' => 140000 ),
            'wuerzburg'        => array( 'name' => 'Würzburg',             'land' => 'bayern',              'lat' => 49.7913, 'lng' => 9.9534,  'pop' => 130000 ),
            'wolfsburg'        => array( 'name' => 'Wolfsburg',            'land' => 'niedersachsen',       'lat' => 52.4227, 'lng' => 10.7865, 'pop' => 125000 ),
            'ulm'              => array( 'name' => 'Ulm',                  'land' => 'baden-wuerttemberg',  'lat' => 48.4011, 'lng' => 9.9876,  'pop' => 125000 ),
            'heilbronn'        => array( 'name' => 'Heilbronn',            'land' => 'baden-wuerttemberg',  'lat' => 49.1427, 'lng' => 9.2109,  'pop' => 125000 ),
            'pforzheim'        => array( 'name' => 'Pforzheim',            'land' => 'baden-wuerttemberg',  'lat' => 48.8932, 'lng' => 8.6949,  'pop' => 125000 ),
            'goettingen'       => array( 'name' => 'Göttingen',            'land' => 'niedersachsen',       'lat' => 51.5413, 'lng' => 9.9158,  'pop' => 120000 ),
            'bottrop'          => array( 'name' => 'Bottrop',              'land' => 'nordrhein-westfalen', 'lat' => 51.5216, 'lng' => 6.9248,  'pop' => 118000 ),
            'reutlingen'       => array( 'name' => 'Reutlingen',           'land' => 'baden-wuerttemberg',  'lat' => 48.4914, 'lng' => 9.2043,  'pop' => 115000 ),
            'koblenz'          => array( 'name' => 'Koblenz',              'land' => 'rheinland-pfalz',       'lat' => 50.3569, 'lng' => 7.5890,  'pop' => 114000 ),
            'bremerhaven'      => array( 'name' => 'Bremerhaven',          'land' => 'bremen',              'lat' => 53.5414, 'lng' => 8.5809,  'pop' => 113000 ),
            'trier'            => array( 'name' => 'Trier',                'land' => 'rheinland-pfalz',       'lat' => 49.7596, 'lng' => 6.6442,  'pop' => 110000 ),
            'jena'             => array( 'name' => 'Jena',                 'land' => 'thueringen',            'lat' => 50.9271, 'lng' => 11.5892, 'pop' => 110000 ),
            'erlangen'         => array( 'name' => 'Erlangen',             'land' => 'bayern',              'lat' => 49.5897, 'lng' => 11.0039, 'pop' => 115000 ),
            'cottbus'          => array( 'name' => 'Cottbus',              'land' => 'brandenburg',           'lat' => 51.7563, 'lng' => 14.3329, 'pop' => 100000 ),
        );
    }

    /**
     * 11 Offizielle Deutsche Fachbereiche & Dienstleistungen
     */
    public static function get_categories() {
        return array(
            'freie-werkstatt' => array(
                'name'  => 'Freie Kfz-Werkstatt',
                'desc'  => 'Herstellerunabhängige Meisterbetriebe für Inspektion nach Herstellervorgaben, Wartung und Reparaturen aller Fabrikate.',
                'icon'  => 'wrench',
            ),
            'tuev-hu-au' => array(
                'name'  => 'TÜV / HU & AU',
                'desc'  => 'Hauptuntersuchung und Abgasuntersuchung durch anerkannte Prüforganisationen (TÜV, DEKRA, GTÜ, KÜS) inkl. Vorab-Check.',
                'icon'  => 'shield-check',
            ),
            'abschleppdienst-pannenhilfe' => array(
                'name'  => '24h Abschleppdienst & Pannenhilfe',
                'desc'  => 'Rund um die Uhr Soforthilfe bei Pannen, Unfällen und Fahrzeugtransporten in ganz Deutschland.',
                'icon'  => 'truck',
            ),
            'autoglas-scheibenreparatur' => array(
                'name'  => 'Autoglas & Scheiben',
                'desc'  => 'Steinschlagreparatur, Austausch von Front-, Seiten- und Heckscheiben sowie Kalibrierung von Fahrerassistenzsystemen.',
                'icon'  => 'sparkles',
            ),
            'karosserie-lackiererei' => array(
                'name'  => 'Karosserie & Lackiererei',
                'desc'  => 'Unfallinstandsetzung, Smart Repair, Dellenentfernung ohne Lackieren (Hagelschaden) und Neulackierungen.',
                'icon'  => 'color-swatch',
            ),
            'reifenservice-raederwechsel' => array(
                'name'  => 'Reifenservice & Räderwechsel',
                'desc'  => 'Saisonaler Radwechsel, Reifenmontage, Auswuchten, RDKS-Programmierung und fachgerechte Rädereinlagerung.',
                'icon'  => 'circle-stack',
            ),
            'kfz-elektrik-elektronik' => array(
                'name'  => 'Kfz-Elektrik & Diagnose',
                'desc'  => 'Computergestützte OBD2-Fehlerauslese, Steuergerätereparatur, Batterie- und Lichtmaschinenservice.',
                'icon'  => 'cpu-chip',
            ),
            'klimaservice-standheizung' => array(
                'name'  => 'Klimaservice & Standheizung',
                'desc'  => 'Klimaanlagenwartung, Kältemittelnachfüllung (R134a & R1234yf), Ozon-Desinfektion und Standheizungswartung.',
                'icon'  => 'sun',
            ),
            'bremsenservice-fahrwerk' => array(
                'name'  => 'Bremsenservice & Fahrwerk',
                'desc'  => 'Austausch von Bremsbelägen und Bremsscheiben, Bremsflüssigkeitswechsel, Stoßdämpfer und 3D-Achsvermessung.',
                'icon'  => 'cog-6-tooth',
            ),
            'motor-getriebeinstandsetzung' => array(
                'name'  => 'Motor- & Getriebeinstandsetzung',
                'desc'  => 'Zahnriemenwechsel, Kupplungstausch, Zylinderkopfreparatur, Automatikgetriebespülung und Motorüberholung.',
                'icon'  => 'fire',
            ),
            'e-auto-ladestationen' => array(
                'name'  => 'E-Mobilität & Hybrid-Service',
                'desc'  => 'Zertifizierte Hochvolt-Werkstätten für Elektroautos, Hybridfahrzeuge, Akku-Checks und Ladestationen.',
                'icon'  => 'bolt',
            ),
        );
    }

    /**
     * Top Automarken in Deutschland
     */
    public static function get_car_brands() {
        return array(
            'volkswagen'    => 'Volkswagen',
            'bmw'           => 'BMW',
            'mercedes-benz' => 'Mercedes-Benz',
            'audi'          => 'Audi',
            'opel'          => 'Opel',
            'ford'          => 'Ford',
            'porsche'       => 'Porsche',
            'skoda'         => 'Skoda',
            'seat'          => 'Seat',
            'renault'       => 'Renault',
            'peugeot'       => 'Peugeot',
            'toyota'        => 'Toyota',
            'hyundai'       => 'Hyundai',
            'kia'           => 'Kia',
            'fiat'          => 'Fiat',
            'volvo'         => 'Volvo',
            'mazda'         => 'Mazda',
            'nissan'        => 'Nissan',
            'tesla'         => 'Tesla',
            'dacia'         => 'Dacia',
            'mini'          => 'Mini',
            'honda'         => 'Honda',
            'citroen'       => 'Citroën',
            'suzuki'        => 'Suzuki',
            'alfa-romeo'    => 'Alfa Romeo',
            'jeep'          => 'Jeep',
            'land-rover'    => 'Land Rover',
        );
    }
}
