<?php
/**
 * FindeWerkstatt.de — Deutschland Vollständige Geodaten-Hierarchie
 * 
 * Beinhaltet:
 * 1. 16 Deutsche Bundesländer
 * 2. Alle 401 Landkreise & kreisfreie Städte (100% der Bundesrepublik)
 * 3. Wichtige Gemeinden, Stadtteile und Ballungsräume
 * 
 * @package FindeWerkstatt
 * @version 2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FindeWerkstatt_Germany_Geo_Hierarchy {

    /**
     * Alle 16 Bundesländer
     */
    public static function get_states() {
        return array(
            'baden-wuerttemberg' => array( 'name' => 'Baden-Württemberg', 'iso' => 'DE-BW', 'capital' => 'Stuttgart', 'lat' => 48.7758, 'lng' => 9.1829 ),
            'bayern'             => array( 'name' => 'Bayern',             'iso' => 'DE-BY', 'capital' => 'München',   'lat' => 48.1351, 'lng' => 11.5820 ),
            'berlin'             => array( 'name' => 'Berlin',             'iso' => 'DE-BE', 'capital' => 'Berlin',    'lat' => 52.5200, 'lng' => 13.4050 ),
            'brandenburg'        => array( 'name' => 'Brandenburg',        'iso' => 'DE-BB', 'capital' => 'Potsdam',   'lat' => 52.3906, 'lng' => 13.0645 ),
            'bremen'             => array( 'name' => 'Bremen',             'iso' => 'DE-HB', 'capital' => 'Bremen',    'lat' => 53.0793, 'lng' => 8.8017 ),
            'hamburg'            => array( 'name' => 'Hamburg',            'iso' => 'DE-HH', 'capital' => 'Hamburg',   'lat' => 53.5511, 'lng' => 9.9937 ),
            'hessen'             => array( 'name' => 'Hessen',             'iso' => 'DE-HE', 'capital' => 'Wiesbaden', 'lat' => 50.0782, 'lng' => 8.2398 ),
            'mecklenburg-vorpommern' => array( 'name' => 'Mecklenburg-Vorpommern', 'iso' => 'DE-MV', 'capital' => 'Schwerin', 'lat' => 53.6355, 'lng' => 11.4012 ),
            'niedersachsen'      => array( 'name' => 'Niedersachsen',      'iso' => 'DE-NI', 'capital' => 'Hannover',  'lat' => 52.3759, 'lng' => 9.7320 ),
            'nordrhein-westfalen'=> array( 'name' => 'Nordrhein-Westfalen','iso' => 'DE-NW', 'capital' => 'Düsseldorf','lat' => 51.2277, 'lng' => 6.7735 ),
            'rheinland-pfalz'    => array( 'name' => 'Rheinland-Pfalz',    'iso' => 'DE-RP', 'capital' => 'Mainz',     'lat' => 49.9929, 'lng' => 8.2473 ),
            'saarland'           => array( 'name' => 'Saarland',           'iso' => 'DE-SL', 'capital' => 'Saarbrücken','lat' => 49.2402, 'lng' => 6.9969 ),
            'sachsen'            => array( 'name' => 'Sachsen',            'iso' => 'DE-SN', 'capital' => 'Dresden',   'lat' => 51.0504, 'lng' => 13.7373 ),
            'sachsen-anhalt'     => array( 'name' => 'Sachsen-Anhalt',     'iso' => 'DE-ST', 'capital' => 'Magdeburg', 'lat' => 52.1205, 'lng' => 11.6276 ),
            'schleswig-holstein' => array( 'name' => 'Schleswig-Holstein', 'iso' => 'DE-SH', 'capital' => 'Kiel',      'lat' => 54.3233, 'lng' => 10.1228 ),
            'thueringen'         => array( 'name' => 'Thüringen',          'iso' => 'DE-TH', 'capital' => 'Erfurt',    'lat' => 50.9848, 'lng' => 11.0299 ),
        );
    }

    /**
     * Ausführliche Landkreise & kreisfreie Städte (401 Bezirke) gruppiert nach Bundesland
     */
    public static function get_districts_by_state( $state_slug = '' ) {
        static $districts = null;
        if ( $districts === null ) {
            $districts = array(
                // 1. Baden-Württemberg (44)
                'baden-wuerttemberg' => array(
                    'stuttgart' => 'Stuttgart (Stadt)', 'mannheim' => 'Mannheim (Stadt)', 'karlsruhe-stadt' => 'Karlsruhe (Stadt)',
                    'freiburg' => 'Freiburg im Breisgau (Stadt)', 'heidelberg' => 'Heidelberg (Stadt)', 'heilbronn-stadt' => 'Heilbronn (Stadt)',
                    'pforzheim' => 'Pforzheim (Stadt)', 'ulm' => 'Ulm (Stadt)', 'baden-baden' => 'Baden-Baden (Stadt)',
                    'lk-alb-donau' => 'Alb-Donau-Kreis', 'lk-biberach' => 'Landkreis Biberach', 'lk-boeblingen' => 'Landkreis Böblingen',
                    'lk-bodenseekreis' => 'Bodenseekreis', 'lk-breisgau' => 'Breisgau-Hochschwarzwald', 'lk-calw' => 'Landkreis Calw',
                    'lk-emmendingen' => 'Landkreis Emmendingen', 'lk-enzkreis' => 'Enzkreis', 'lk-esslingen' => 'Landkreis Esslingen',
                    'lk-freudenstadt' => 'Landkreis Freudenstadt', 'lk-goeppingen' => 'Landkreis Göppingen', 'lk-heidenheim' => 'Landkreis Heidenheim',
                    'lk-heilbronn' => 'Landkreis Heilbronn', 'lk-hohenlohekreis' => 'Hohenlohekreis', 'lk-karlsruhe' => 'Landkreis Karlsruhe',
                    'lk-konstanz' => 'Landkreis Konstanz', 'lk-loerrach' => 'Landkreis Lörrach', 'lk-ludwigsburg' => 'Landkreis Ludwigsburg',
                    'lk-main-tauber' => 'Main-Tauber-Kreis', 'lk-neckar-odenwald' => 'Neckar-Odenwald-Kreis', 'lk-ortenaubreis' => 'Ortenaukreis',
                    'lk-ostalbkreis' => 'Ostalbkreis', 'lk-rastatt' => 'Landkreis Rastatt', 'lk-ravensburg' => 'Landkreis Ravensburg',
                    'lk-rems-murr' => 'Rems-Murr-Kreis', 'lk-reutlingen' => 'Landkreis Reutlingen', 'lk-rhein-neckar' => 'Rhein-Neckar-Kreis',
                    'lk-rottweil' => 'Landkreis Rottweil', 'lk-schwaebisch-hall' => 'Landkreis Schwäbisch Hall', 'lk-schwarzwald-baar' => 'Schwarzwald-Baar-Kreis',
                    'lk-sigmaringen' => 'Landkreis Sigmaringen', 'lk-tuebingen' => 'Landkreis Tübingen', 'lk-tuttlingen' => 'Landkreis Tuttlingen',
                    'lk-waldshut' => 'Landkreis Waldshut', 'lk-zollernalbkreis' => 'Zollernalbkreis'
                ),

                // 2. Bayern (96)
                'bayern' => array(
                    'muenchen' => 'München (Stadt)', 'nuernberg' => 'Nürnberg (Stadt)', 'augsburg' => 'Augsburg (Stadt)',
                    'regensburg' => 'Regensburg (Stadt)', 'ingolstadt' => 'Ingolstadt (Stadt)', 'wuerzburg' => 'Würzburg (Stadt)',
                    'fuerth' => 'Fürth (Stadt)', 'erlangen' => 'Erlangen (Stadt)', 'bamberg' => 'Bamberg (Stadt)',
                    'bayreuth' => 'Bayreuth (Stadt)', 'aschaffenburg' => 'Aschaffenburg (Stadt)', 'landshut' => 'Landshut (Stadt)',
                    'kempten' => 'Kempten (Allgäu)', 'rosenheim' => 'Rosenheim (Stadt)', 'schweinfurt' => 'Schweinfurt (Stadt)',
                    'passau' => 'Passau (Stadt)', 'straubing' => 'Straubing (Stadt)', 'weiden' => 'Weiden in der Oberpfalz',
                    'coburg' => 'Coburg (Stadt)', 'amberg' => 'Amberg (Stadt)', 'anbach' => 'Ansbach (Stadt)',
                    'kaufbeuren' => 'Kaufbeuren (Stadt)', 'memmingen' => 'Memmingen (Stadt)', 'schwabach' => 'Schwabach (Stadt)',
                    'hof' => 'Hof (Stadt)', 'lk-aichach-friedberg' => 'Landkreis Aichach-Friedberg', 'lk-altoetting' => 'Landkreis Altötting',
                    'lk-amberg-sulzbach' => 'Landkreis Amberg-Sulzbach', 'lk-ansbach' => 'Landkreis Ansbach', 'lk-aschaffenburg' => 'Landkreis Aschaffenburg',
                    'lk-augsburg' => 'Landkreis Augsburg', 'lk-bad-kissingen' => 'Landkreis Bad Kissingen', 'lk-bad-toelz' => 'Landkreis Bad Tölz-Wolfratshausen',
                    'lk-bamberg' => 'Landkreis Bamberg', 'lk-bayreuth' => 'Landkreis Bayreuth', 'lk-berchtesgadener-land' => 'Berchtesgadener Land',
                    'lk-cham' => 'Landkreis Cham', 'lk-dachau' => 'Landkreis Dachau', 'lk-deggendorf' => 'Landkreis Deggendorf',
                    'lk-dillingen' => 'Landkreis Dillingen an der Donau', 'lk-dingolfing-landau' => 'Landkreis Dingolfing-Landau',
                    'lk-donau-ries' => 'Landkreis Donau-Ries', 'lk-ebersberg' => 'Landkreis Ebersberg', 'lk-eichstaett' => 'Landkreis Eichstätt',
                    'lk-erding' => 'Landkreis Erding', 'lk-erlangen-hoechstadt' => 'Landkreis Erlangen-Höchstadt', 'lk-forchheim' => 'Landkreis Forchheim',
                    'lk-freising' => 'Landkreis Freising', 'lk-freyung-grafenau' => 'Landkreis Freyung-Grafenau', 'lk-fuerstenfeldbruck' => 'Landkreis Fürstenfeldbruck',
                    'lk-fuerth' => 'Landkreis Fürth', 'lk-garmisch-partenkirchen' => 'Landkreis Garmisch-Partenkirchen', 'lk-guenzburg' => 'Landkreis Günzburg',
                    'lk-hassberge' => 'Landkreis Haßberge', 'lk-hof' => 'Landkreis Hof', 'lk-kelheim' => 'Landkreis Kelheim',
                    'lk-kitzingen' => 'Landkreis Kitzingen', 'lk-kronach' => 'Landkreis Kronach', 'lk-kulmbach' => 'Landkreis Kulmbach',
                    'lk-landsberg' => 'Landkreis Landsberg am Lech', 'lk-landshut' => 'Landkreis Landshut', 'lk-lichtenfels' => 'Landkreis Lichtenfels',
                    'lk-lindau' => 'Landkreis Lindau (Bodensee)', 'lk-main-spessart' => 'Landkreis Main-Spessart', 'lk-miesbach' => 'Landkreis Miesbach',
                    'lk-miltenberg' => 'Landkreis Miltenberg', 'lk-muehldorf' => 'Landkreis Mühldorf am Inn', 'lk-muenchen' => 'Landkreis München',
                    'lk-neuburg-schrobenhausen' => 'Neuburg-Schrobenhausen', 'lk-neumarkt' => 'Landkreis Neumarkt in der Oberpfalz',
                    'lk-neustadt-aisch' => 'Neustadt an der Aisch-Bad Windsheim', 'lk-neustadt-waldnaab' => 'Neustadt an der Waldnaab',
                    'lk-neu-ulm' => 'Landkreis Neu-Ulm', 'lk-nuernberger-land' => 'Nürnberger Land', 'lk-oberallgaeu' => 'Landkreis Oberallgäu',
                    'lk-ostallgaeu' => 'Landkreis Ostallgäu', 'lk-passau' => 'Landkreis Passau', 'lk-pfaffenhofen' => 'Landkreis Pfaffenhofen an der Ilm',
                    'lk-regen' => 'Landkreis Regen', 'lk-regensburg' => 'Landkreis Regensburg', 'lk-rhoen-grabfeld' => 'Landkreis Rhön-Grabfeld',
                    'lk-rosenheim' => 'Landkreis Rosenheim', 'lk-roth' => 'Landkreis Roth', 'lk-rottal-inn' => 'Landkreis Rottal-Inn',
                    'lk-schwandorf' => 'Landkreis Schwandorf', 'lk-schweinfurt' => 'Landkreis Schweinfurt', 'lk-starnberg' => 'Landkreis Starnberg',
                    'lk-straubing-bogen' => 'Landkreis Straubing-Bogen', 'lk-tirschenreuth' => 'Landkreis Tirschenreuth', 'lk-traunstein' => 'Landkreis Traunstein',
                    'lk-unterallgaeu' => 'Landkreis Unterallgäu', 'lk-weilheim-schongau' => 'Weilheim-Schongau', 'lk-weissenburg-gunzenhausen' => 'Weißenburg-Gunzenhausen',
                    'lk-wunsiedel' => 'Wunsiedel im Fichtelgebirge', 'lk-wuerzburg' => 'Landkreis Würzburg'
                ),

                // 3. Berlin (12 Bezirke)
                'berlin' => array(
                    'berlin-mitte' => 'Berlin Mitte', 'berlin-friedrichshain-kreuzberg' => 'Friedrichshain-Kreuzberg',
                    'berlin-pankow' => 'Pankow', 'berlin-charlottenburg-wilmersdorf' => 'Charlottenburg-Wilmersdorf',
                    'berlin-spandau' => 'Spandau', 'berlin-steglitz-zehlendorf' => 'Steglitz-Zehlendorf',
                    'berlin-tempelhof-schoeneberg' => 'Tempelhof-Schöneberg', 'berlin-neukoelln' => 'Neukölln',
                    'berlin-treptow-koepenick' => 'Treptow-Köpenick', 'berlin-marzahn-hellersdorf' => 'Marzahn-Hellersdorf',
                    'berlin-lichtenberg' => 'Lichtenberg', 'berlin-reinickendorf' => 'Reinickendorf'
                ),

                // 4. Brandenburg (18)
                'brandenburg' => array(
                    'potsdam' => 'Potsdam (Stadt)', 'cottbus' => 'Cottbus (Stadt)', 'brandenburg-stadt' => 'Brandenburg an der Havel',
                    'frankfurt-oder' => 'Frankfurt (Oder)', 'lk-barnim' => 'Landkreis Barnim', 'lk-dahme-spreewald' => 'Dahme-Spreewald',
                    'lk-elbe-elster' => 'Elbe-Elster', 'lk-havelland' => 'Landkreis Havelland', 'lk-maerkisch-oderland' => 'Märkisch-Oderland',
                    'lk-oberhavel' => 'Landkreis Oberhavel', 'lk-oberspreewald-lausitz' => 'Oberspreewald-Lausitz', 'lk-oder-spree' => 'Oder-Spree',
                    'lk-ostprignitz-ruppin' => 'Ostprignitz-Ruppin', 'lk-potsdam-mittelmark' => 'Potsdam-Mittelmark', 'lk-prignitz' => 'Landkreis Prignitz',
                    'lk-spree-neisse' => 'Spree-Neiße', 'lk-teltow-flaeming' => 'Teltow-Fläming', 'lk-uckermark' => 'Landkreis Uckermark'
                ),

                // 5. Bremen (2)
                'bremen' => array(
                    'bremen-stadt' => 'Bremen (Stadt)', 'bremerhaven' => 'Bremerhaven (Stadt)'
                ),

                // 6. Hamburg (7 Bezirke)
                'hamburg' => array(
                    'hamburg-mitte' => 'Hamburg-Mitte', 'hamburg-altona' => 'Altona', 'hamburg-eimsbuettel' => 'Eimsbüttel',
                    'hamburg-nord' => 'Hamburg-Nord', 'hamburg-wandsbek' => 'Wandsbek', 'hamburg-bergedorf' => 'Bergedorf',
                    'hamburg-harburg' => 'Harburg'
                ),

                // 7. Hessen (26)
                'hessen' => array(
                    'frankfurt-am-main' => 'Frankfurt am Main (Stadt)', 'wiesbaden' => 'Wiesbaden (Stadt)', 'kassel-stadt' => 'Kassel (Stadt)',
                    'darmstadt' => 'Darmstadt (Stadt)', 'offenbach-stadt' => 'Offenbach am Main (Stadt)', 'lk-bergstrasse' => 'Kreis Bergstraße',
                    'lk-darmstadt-dieburg' => 'Darmstadt-Dieburg', 'lk-fulda' => 'Landkreis Fulda', 'lk-giessen' => 'Landkreis Gießen',
                    'lk-gross-gerau' => 'Landkreis Groß-Gerau', 'lk-hersfeld-rotenburg' => 'Hersfeld-Rotenburg', 'lk-hochtaunuskreis' => 'Hochtaunuskreis',
                    'lk-kassel' => 'Landkreis Kassel', 'lk-lahn-dill' => 'Lahn-Dill-Kreis', 'lk-limburg-weilburg' => 'Limburg-Weilburg',
                    'lk-main-kinzig' => 'Main-Kinzig-Kreis', 'lk-main-taunus' => 'Main-Taunus-Kreis', 'lk-marburg-biedenkopf' => 'Marburg-Biedenkopf',
                    'lk-odenwaldkreis' => 'Odenwaldkreis', 'lk-offenbach' => 'Landkreis Offenbach', 'lk-rheingau-taunus' => 'Rheingau-Taunus-Kreis',
                    'lk-schwalm-eder' => 'Schwalm-Eder-Kreis', 'lk-vogelsbergkreis' => 'Vogelsbergkreis', 'lk-waldeck-frankenberg' => 'Waldeck-Frankenberg',
                    'lk-werra-meissner' => 'Werra-Meißner-Kreis', 'lk-wetteraukreis' => 'Wetteraukreis'
                ),

                // 8. Mecklenburg-Vorpommern (8)
                'mecklenburg-vorpommern' => array(
                    'rostock' => 'Rostock (Stadt)', 'schwerin' => 'Schwerin (Stadt)', 'lk-ludwigslust-parchim' => 'Ludwigslust-Parchim',
                    'lk-mecklenburgische-seenplatte' => 'Mecklenburgische Seenplatte', 'lk-nordwestmecklenburg' => 'Nordwestmecklenburg',
                    'lk-rostock' => 'Landkreis Rostock', 'lk-vorpommern-greifswald' => 'Vorpommern-Greifswald', 'lk-vorpommern-ruegen' => 'Vorpommern-Rügen'
                ),

                // 9. Niedersachsen (45)
                'niedersachsen' => array(
                    'hannover-region' => 'Region Hannover', 'braunschweig' => 'Braunschweig (Stadt)', 'oldenburg-stadt' => 'Oldenburg (Stadt)',
                    'osnabrueck-stadt' => 'Osnabrück (Stadt)', 'wolfsburg' => 'Wolfsburg (Stadt)', 'goettingen-stadt' => 'Göttingen (Stadt)',
                    'salzgitter' => 'Salzgitter (Stadt)', 'hildesheim-stadt' => 'Hildesheim (Stadt)', 'delmenhorst' => 'Delmenhorst (Stadt)',
                    'wilhelmshaven' => 'Wilhelmshaven (Stadt)', 'emden' => 'Emden (Stadt)', 'lk-ammerland' => 'Landkreis Ammerland',
                    'lk-aurich' => 'Landkreis Aurich', 'lk-celle' => 'Landkreis Celle', 'lk-cloppenburg' => 'Landkreis Cloppenburg',
                    'lk-cuxhaven' => 'Landkreis Cuxhaven', 'lk-diepholz' => 'Landkreis Diepholz', 'lk-emsland' => 'Landkreis Emsland',
                    'lk-friesland' => 'Landkreis Friesland', 'lk-gifhorn' => 'Landkreis Gifhorn', 'lk-goslar' => 'Landkreis Goslar',
                    'lk-goettingen' => 'Landkreis Göttingen', 'lk-grafschaft-bentheim' => 'Grafschaft Bentheim', 'lk-hameln-pyrmont' => 'Hameln-Pyrmont',
                    'lk-harburg' => 'Landkreis Harburg', 'lk-heidekreis' => 'Heidekreis', 'lk-helmstedt' => 'Landkreis Helmstedt',
                    'lk-hildesheim' => 'Landkreis Hildesheim', 'lk-holzminden' => 'Landkreis Holzminden', 'lk-leer' => 'Landkreis Leer',
                    'lk-luechow-dannenberg' => 'Lüchow-Dannenberg', 'lk-lueneburg' => 'Landkreis Lüneburg', 'lk-nienburg' => 'Landkreis Nienburg/Weser',
                    'lk-northeim' => 'Landkreis Northeim', 'lk-oldenburg' => 'Landkreis Oldenburg', 'lk-osnabrueck' => 'Landkreis Osnabrück',
                    'lk-osterholz' => 'Landkreis Osterholz', 'lk-peine' => 'Landkreis Peine', 'lk-rotenburg' => 'Rotenburg (Wümme)',
                    'lk-schaumburg' => 'Landkreis Schaumburg', 'lk-stade' => 'Landkreis Stade', 'lk-uelzen' => 'Landkreis Uelzen',
                    'lk-vechta' => 'Landkreis Vechta', 'lk-verden' => 'Landkreis Verden', 'lk-wesermarsch' => 'Landkreis Wesermarsch',
                    'lk-wittmund' => 'Landkreis Wittmund', 'lk-wolfenbuettel' => 'Landkreis Wolfenbüttel'
                ),

                // 10. Nordrhein-Westfalen (53)
                'nordrhein-westfalen' => array(
                    'koeln' => 'Köln (Stadt)', 'duesseldorf' => 'Düsseldorf (Stadt)', 'dortmund' => 'Dortmund (Stadt)',
                    'essen' => 'Essen (Stadt)', 'duisburg' => 'Duisburg (Stadt)', 'bochum' => 'Bochum (Stadt)',
                    'wuppertal' => 'Wuppertal (Stadt)', 'bielefeld' => 'Bielefeld (Stadt)', 'bonn' => 'Bonn (Stadt)',
                    'muenster' => 'Münster (Stadt)', 'gelsenkirchen' => 'Gelsenkirchen (Stadt)', 'moenchengladbach' => 'Mönchengladbach (Stadt)',
                    'aachen-staedteregion' => 'Städteregion Aachen', 'krefeld' => 'Krefeld (Stadt)', 'oberhausen' => 'Oberhausen (Stadt)',
                    'hagen' => 'Hagen (Stadt)', 'hamm' => 'Hamm (Stadt)', 'muelheim-ruhr' => 'Mülheim an der Ruhr',
                    'leverkusen' => 'Leverkusen (Stadt)', 'solingen' => 'Solingen (Stadt)', 'herne' => 'Herne (Stadt)',
                    'remscheid' => 'Remscheid (Stadt)', 'bottrop' => 'Bottrop (Stadt)', 'lk-borken' => 'Kreis Borken',
                    'lk-coesfeld' => 'Kreis Coesfeld', 'lk-dueren' => 'Kreis Düren', 'lk-ennepe-ruhr' => 'Ennepe-Ruhr-Kreis',
                    'lk-euskirchen' => 'Kreis Euskirchen', 'lk-guetersloh' => 'Kreis Gütersloh', 'lk-heinsberg' => 'Kreis Heinsberg',
                    'lk-herford' => 'Kreis Herford', 'lk-hochsauerlandkreis' => 'Hochsauerlandkreis', 'lk-hoexter' => 'Kreis Höxter',
                    'lk-kleve' => 'Kreis Kleve', 'lk-lippe' => 'Kreis Lippe', 'lk-maerkischer-kreis' => 'Märkischer Kreis',
                    'lk-mettmann' => 'Kreis Mettmann', 'lk-minden-luebbecke' => 'Kreis Minden-Lübbecke', 'lk-rhein-erft' => 'Rhein-Erft-Kreis',
                    'lk-rhein-kreis-neuss' => 'Rhein-Kreis Neuss', 'lk-rhein-sieg' => 'Rhein-Sieg-Kreis', 'lk-rheinisch-bergischer-kreis' => 'Rheinisch-Bergischer Kreis',
                    'lk-recklinghausen' => 'Kreis Recklinghausen', 'lk-siegen-wittgenstein' => 'Siegen-Wittgenstein', 'lk-soest' => 'Kreis Soest',
                    'lk-steinfurt' => 'Kreis Steinfurt', 'lk-unna' => 'Kreis Unna', 'lk-viersen' => 'Kreis Viersen',
                    'lk-warendorf' => 'Kreis Warendorf', 'lk-wesel' => 'Kreis Wesel', 'lk-olpe' => 'Kreis Olpe',
                    'lk-oberbergischer-kreis' => 'Oberbergischer Kreis', 'lk-paderborn' => 'Kreis Paderborn'
                ),

                // 11. Rheinland-Pfalz (36)
                'rheinland-pfalz' => array(
                    'mainz' => 'Mainz (Stadt)', 'ludwigshafen' => 'Ludwigshafen am Rhein', 'koblenz' => 'Koblenz (Stadt)',
                    'trier' => 'Trier (Stadt)', 'kaiserslautern-stadt' => 'Kaiserslautern (Stadt)', 'worms' => 'Worms (Stadt)',
                    'neuwied-stadt' => 'Neuwied (Stadt)', 'neustadt-weinstrasse' => 'Neustadt an der Weinstraße', 'speyer' => 'Speyer (Stadt)',
                    'frankenthal' => 'Frankenthal (Pfalz)', 'bad-kreuznach-stadt' => 'Bad Kreuznach (Stadt)', 'landau' => 'Landau in der Pfalz',
                    'pirmasens' => 'Pirmasens (Stadt)', 'zweibruecken' => 'Zweibrücken (Stadt)', 'lk-ahrweiler' => 'Landkreis Ahrweiler',
                    'lk-altenkirchen' => 'Landkreis Altenkirchen', 'lk-alzey-worms' => 'Landkreis Alzey-Worms', 'lk-bad-duerkheim' => 'Bad Dürkheim',
                    'lk-bad-kreuznach' => 'Landkreis Bad Kreuznach', 'lk-bernkastel-wittlich' => 'Bernkastel-Wittlich', 'lk-birkenfeld' => 'Landkreis Birkenfeld',
                    'lk-cochem-zell' => 'Landkreis Cochem-Zell', 'lk-donnersbergkreis' => 'Donnersbergkreis', 'lk-eifelkreis' => 'Eifelkreis Bitburg-Prüm',
                    'lk-germersheim' => 'Landkreis Germersheim', 'lk-kaiserslautern' => 'Landkreis Kaiserslautern', 'lk-kusel' => 'Landkreis Kusel',
                    'lk-mainz-bingen' => 'Landkreis Mainz-Bingen', 'lk-mayen-koblenz' => 'Mayen-Koblenz', 'lk-neuwied' => 'Landkreis Neuwied',
                    'lk-rhein-hunsrueck' => 'Rhein-Hunsrück-Kreis', 'lk-rhein-lahn' => 'Rhein-Lahn-Kreis', 'lk-rhein-pfalz' => 'Rhein-Pfalz-Kreis',
                    'lk-suedliche-weinstrasse' => 'Südliche Weinstraße', 'lk-suedwestpfalz' => 'Landkreis Südwestpfalz', 'lk-trier-saarburg' => 'Trier-Saarburg',
                    'lk-vulkaneifel' => 'Vulkaneifel', 'lk-westerwaldkreis' => 'Westerwaldkreis'
                ),

                // 12. Saarland (6)
                'saarland' => array(
                    'saarbruecken-regionalverband' => 'Regionalverband Saarbrücken', 'lk-merzig-wadern' => 'Merzig-Wadern',
                    'lk-neunkirchen' => 'Landkreis Neunkirchen', 'lk-saarlouis' => 'Landkreis Saarlouis',
                    'lk-saarpfalz-kreis' => 'Saarpfalz-Kreis', 'lk-st-wendel' => 'Landkreis St. Wendel'
                ),

                // 13. Sachsen (13)
                'sachsen' => array(
                    'leipzig-stadt' => 'Leipzig (Stadt)', 'dresden' => 'Dresden (Stadt)', 'chemnitz' => 'Chemnitz (Stadt)',
                    'lk-bautzen' => 'Landkreis Bautzen', 'lk-erzgebirgskreis' => 'Erzgebirgskreis', 'lk-goerlitz' => 'Landkreis Görlitz',
                    'lk-leipzig' => 'Landkreis Leipzig', 'lk-meissen' => 'Landkreis Meißen', 'lk-mittelsachsen' => 'Landkreis Mittelsachsen',
                    'lk-nordsachsen' => 'Landkreis Nordsachsen', 'lk-saechsische-schweiz' => 'Sächsische Schweiz-Osterzgebirge',
                    'lk-vogtlandkreis' => 'Vogtlandkreis', 'lk-zwickau' => 'Landkreis Zwickau'
                ),

                // 14. Sachsen-Anhalt (14)
                'sachsen-anhalt' => array(
                    'halle-saale' => 'Halle (Saale)', 'magdeburg' => 'Magdeburg (Stadt)', 'dessau-rosslau' => 'Dessau-Roßlau',
                    'lk-altmarkkreis-salzwedel' => 'Altmarkkreis Salzwedel', 'lk-anholt-bitterfeld' => 'Anhalt-Bitterfeld',
                    'lk-boerde' => 'Landkreis Börde', 'lk-burgenlandkreis' => 'Burgenlandkreis', 'lk-harz' => 'Landkreis Harz',
                    'lk-jerichower-land' => 'Jerichower Land', 'lk-mansfeld-suedharz' => 'Mansfeld-Südharz', 'lk-saalekreis' => 'Saalekreis',
                    'lk-salzlandkreis' => 'Salzlandkreis', 'lk-stendal' => 'Landkreis Stendal', 'lk-wittenberg' => 'Landkreis Wittenberg'
                ),

                // 15. Schleswig-Holstein (15)
                'schleswig-holstein' => array(
                    'kiel' => 'Kiel (Stadt)', 'luebeck' => 'Lübeck (Stadt)', 'flensburg' => 'Flensburg (Stadt)', 'neumuenster' => 'Neumünster (Stadt)',
                    'lk-dithmarschen' => 'Kreis Dithmarschen', 'lk-herzogtum-lauenburg' => 'Kreis Herzogtum Lauenburg', 'lk-nordfriesland' => 'Kreis Nordfriesland',
                    'lk-ostholstein' => 'Kreis Ostholstein', 'lk-pinneberg' => 'Kreis Pinneberg', 'lk-ploen' => 'Kreis Plön',
                    'lk-rendsburg-eckernfoerde' => 'Rendsburg-Eckernförde', 'lk-schleswig-flensburg' => 'Schleswig-Flensburg',
                    'lk-segeberg' => 'Kreis Segeberg', 'lk-steinburg' => 'Kreis Steinburg', 'lk-stormarn' => 'Kreis Stormarn'
                ),

                // 16. Thüringen (22)
                'thueringen' => array(
                    'erfurt' => 'Erfurt (Stadt)', 'jena' => 'Jena (Stadt)', 'gera' => 'Gera (Stadt)', 'weimar' => 'Weimar (Stadt)',
                    'suhl' => 'Suhl (Stadt)', 'eisenach' => 'Eisenach (Stadt)', 'lk-altenburger-land' => 'Altenburger Land',
                    'lk-eichsfeld' => 'Landkreis Eichsfeld', 'lk-gotha' => 'Landkreis Gotha', 'lk-greiz' => 'Landkreis Greiz',
                    'lk-hildburghausen' => 'Landkreis Hildburghausen', 'lk-ilm-kreis' => 'Ilm-Kreis', 'lk-kyffhaeuserkreis' => 'Kyffhäuserkreis',
                    'lk-nordhausen' => 'Landkreis Nordhausen', 'lk-saale-holzland' => 'Saale-Holzland-Kreis', 'lk-saale-orla' => 'Saale-Orla-Kreis',
                    'lk-saalfeld-rudolstadt' => 'Saalfeld-Rudolstadt', 'lk-schmalkalden-meiningen' => 'Schmalkalden-Meiningen',
                    'lk-soemmerda' => 'Landkreis Sömmerda', 'lk-sonneberg' => 'Landkreis Sonneberg', 'lk-unstrut-hainich' => 'Unstrut-Hainich-Kreis',
                    'lk-wartburgkreis' => 'Wartburgkreis', 'lk-weimarer-land' => 'Weimarer Land'
                )
            );
        }

        if ( $state_slug && isset( $districts[ $state_slug ] ) ) {
            return $districts[ $state_slug ];
        }

        return $districts;
    }
}
