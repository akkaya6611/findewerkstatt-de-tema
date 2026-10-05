<?php
/**
 * FindeWerkstatt.de — Dynamisches FAQ-System, Schema.org FAQPage & Kostenschätzer
 *
 * 1. Generiert kontextbezogene FAQs für Städte & Dienstleistungen
 * 2. Injiziert valides JSON-LD FAQPage-Schema für Google Rich Snippets
 * 3. Rendert barrierefreie HTML-Akkordeons für Besucher
 * 4. Stellt einen interaktiven Kostenschätzer (Preisspanne) bereit
 *
 * @package FindeWerkstatt
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FindeWerkstatt_FAQ_Manager {

    /**
     * Initialisierung
     */
    public static function init() {
        if ( function_exists( 'add_action' ) ) {
            add_action( 'wp_head', array( __CLASS__, 'output_faq_schema' ), 30 );
        }
    }

    /**
     * Holt FAQs für die aktuelle Ansicht (Service oder Stadt)
     */
    public static function get_current_faqs() {
        if ( is_admin() ) {
            return array();
        }

        // 1. Dienstleistungs-Seite (Taxonomie service_type)
        if ( is_tax( 'service_type' ) ) {
            $term = get_queried_object();
            if ( $term && isset( $term->slug ) ) {
                return self::get_service_faqs( $term->slug, $term->name );
            }
        }

        // 2. Stadt-Seite (Taxonomie mechanic_city)
        if ( is_tax( 'mechanic_city' ) ) {
            $term = get_queried_object();
            if ( $term && isset( $term->name ) ) {
                return self::get_city_faqs( $term->name );
            }
        }

        // 3. Hauptarchiv / Werkstätten
        if ( is_post_type_archive( 'mechanic' ) || is_front_page() ) {
            return self::get_general_faqs();
        }

        return array();
    }

    /**
     * FAQs für spezifische Kfz-Dienstleistungen
     */
    public static function get_service_faqs( $slug, $name ) {
        $faqs = array();

        switch ( $slug ) {
            case 'tuev-hu-au':
                $faqs[] = array(
                    'q' => findewerkstatt_t( 'Was kostet die Hauptuntersuchung (TÜV/AU) in Deutschland?' ),
                    'a' => findewerkstatt_t( 'Die regulären Gebühren für HU und AU liegen bei anerkannten Prüforganisationen (TÜV, DEKRA, GTÜ, KÜS) für Pkw in der Regel zwischen 140 € und 165 €, abhängig vom jeweiligen Bundesland.' ),
                );
                $faqs[] = array(
                    'q' => findewerkstatt_t( 'Wie lange dauert die Hauptuntersuchung in der Werkstatt?' ),
                    'a' => findewerkstatt_t( 'Die eigentliche technische Prüfung dauert ca. 20 bis 30 Minuten. Bei kombinierten Werkstatt-Vorab-Checks empfiehlt es sich, das Fahrzeug für einen halben Tag abzugeben.' ),
                );
                $faqs[] = array(
                    'q' => findewerkstatt_t( 'Was passiert, wenn mein Fahrzeug die Prüfung nicht besteht?' ),
                    'a' => findewerkstatt_t( 'Sie erhalten einen Prüfbericht mit den festgestellten Mängeln. Innerhalb eines Monats muss das Fahrzeug nach Behebung der Mängel zur Nachprüfung vorgeführt werden (Kosten meist ca. 15–30 €).' ),
                );
                break;

            case 'kfz-elektrik-elektronik':
                $faqs[] = array(
                    'q' => findewerkstatt_t( 'Was kostet eine professionelle Fehlerauslese (OBD-2 Diagnose)?' ),
                    'a' => findewerkstatt_t( 'Eine computergestützte Fehlerauslese inklusive Löschen von sporadischen Fehlern kostet in freien Meisterbetrieben meist zwischen 35 € und 75 €.' ),
                );
                $faqs[] = array(
                    'q' => findewerkstatt_t( 'Wann sollte eine Diagnosewerkstatt aufgesucht werden?' ),
                    'a' => findewerkstatt_t( 'Sobald die Motorkontrollleuchte (MKL), ABS/ESP-Leuchten oder Batterie-Symbole im Kombiinstrument dauerhaft aufleuchten oder das Fahrzeug in den Notlauf schaltet.' ),
                );
                $faqs[] = array(
                    'q' => findewerkstatt_t( 'Können alle Fahrzeugmarken ausgelesen werden?' ),
                    'a' => findewerkstatt_t( 'Ja, die gelisteten Fachbetriebe verfügen über universelle Diagnosetester (Bosch, Gutmann, Launch) sowie herstellerspezifische Schnittstellen für alle gängigen Marken.' ),
                );
                break;

            case 'abschleppdienst-pannenhilfe':
                $faqs[] = array(
                    'q' => findewerkstatt_t( 'Wie schnell ist ein Abschleppdienst vor Ort?' ),
                    'a' => findewerkstatt_t( 'In städtischen Gebieten und auf Autobahnzubringern beträgt die durchschnittliche Anfahrtszeit bei Notrufen meist 25 bis 45 Minuten.' ),
                );
                $faqs[] = array(
                    'q' => findewerkstatt_t( 'Wer übernimmt die Kosten für das Abschleppen?' ),
                    'a' => findewerkstatt_t( 'Häufig werden die Kosten über Schutzbriefe (z. B. Kfz-Versicherung, ADAC, AvD) oder bei unverschuldeten Unfällen durch die gegnerische Haftpflichtversicherung erstattet.' ),
                );
                $faqs[] = array(
                    'q' => findewerkstatt_t( 'Bieten die Betriebe einen 24h Notdienst an?' ),
                    'a' => findewerkstatt_t( 'Ja, viele der auf FindeWerkstatt.de eingetragenen Abschlepp- und Pannendienste sind rund um die Uhr (24h/7 Tage) telefonisch erreichbar.' ),
                );
                break;

            case 'e-auto-ladestationen':
                $faqs[] = array(
                    'q' => findewerkstatt_t( 'Welche Reparaturen dürfen nur zertifizierte Hochvolt-Werkstätten durchführen?' ),
                    'a' => findewerkstatt_t( 'Arbeiten an Hochvolt-Komponenten (Batterie, Inverter, Hochvoltkabel, Elektromotor) dürfen gesetzlich nur von Mechanikern mit Hochvolt-Zertifizierung (DGUV 209-093) ausgeführt werden.' ),
                );
                $faqs[] = array(
                    'q' => findewerkstatt_t( 'Ist die Wartung bei Elektroautos günstiger als bei Verbrennern?' ),
                    'a' => findewerkstatt_t( 'Ja, E-Autos benötigen weder Motorölwechsel noch Zündkerzen oder Auspuffreparaturen. Die Inspektionskosten liegen oft 30–40 % unter denen vergleichbarer Benziner oder Diesel.' ),
                );
                break;

            default:
                $faqs[] = array(
                    'q' => sprintf( findewerkstatt_t( 'Wie finde ich den besten Betrieb für „%s“?' ), $name ),
                    'a' => sprintf( findewerkstatt_t( 'Vergleichen Sie Kundenbewertungen, Lage und Fachgebiete auf FindeWerkstatt.de. Sie können Betriebe direkt per Telefon, WhatsApp oder Online-Anfrage kontaktieren.' ), $name ),
                );
                $faqs[] = array(
                    'q' => findewerkstatt_t( 'Sollte ich vorab einen Kostenvoranschlag anfordern?' ),
                    'a' => findewerkstatt_t( 'Ja, seriöse Kfz-Betriebe erstellen vor Beginn der Reparatur einen detaillierten Kostenvoranschlag für Material und Arbeitslohn.' ),
                );
                break;
        }

        return $faqs;
    }

    /**
     * FAQs für Städte
     */
    public static function get_city_faqs( $city_name ) {
        return array(
            array(
                'q' => sprintf( findewerkstatt_t( 'Wie viele geprüfte Werkstätten gibt es in %s?' ), $city_name ),
                'a' => sprintf( findewerkstatt_t( 'Auf FindeWerkstatt.de sind zahlreiche Kfz-Meisterbetriebe, freie Werkstätten und Spezialisten in %s gelistet, inklusive Standortangaben, Öffnungszeiten und Bewertungen.' ), $city_name ),
            ),
            array(
                'q' => sprintf( findewerkstatt_t( 'Wie schnell bekomme ich einen Werkstatttermin in %s?' ), $city_name ),
                'a' => sprintf( findewerkstatt_t( 'Für Routinearbeiten (Ölwechsel, Reifenwechsel, HU/AU) erhalten Sie bei Betrieben in %s meist innerhalb weniger Werktage einen freien Termin. Notdienste helfen sofort.' ), $city_name ),
            ),
            array(
                'q' => sprintf( findewerkstatt_t( 'Gibt es in %s auch markenunabhängige freie Werkstätten?' ), $city_name ),
                'a' => sprintf( findewerkstatt_t( 'Ja, freie Werkstätten in %s führen Inspektionen nach Herstellervorgaben durch, sodass Ihre Neuwagen-Herstellergarantie vollumfänglich erhalten bleibt.' ), $city_name ),
            ),
        );
    }

    /**
     * Allgemeine FAQs
     */
    public static function get_general_faqs() {
        return array(
            array(
                'q' => findewerkstatt_t( 'Ist die Nutzung von FindeWerkstatt.de kostenlos?' ),
                'a' => findewerkstatt_t( 'Ja, für Autofahrer ist die Suche nach Werkstätten, das Einholen von Angeboten und die Kontaktaufnahme vollständig kostenlos und unverbindlich.' ),
            ),
            array(
                'q' => findewerkstatt_t( 'Bleibt die Herstellergarantie bei Reparatur in einer freien Werkstatt erhalten?' ),
                'a' => findewerkstatt_t( 'Ja. Gemäß EU-GVO (Gruppenfreistellungsverordnung) bleibt die Herstellergarantie vollständig bestehen, wenn die Inspektion nach Herstellervorgaben mit Ersatzteilen in Erstausrüsterqualität durchgeführt wird.' ),
            ),
            array(
                'q' => findewerkstatt_t( 'Wie kann ich meine eigene Werkstatt eintragen?' ),
                'a' => findewerkstatt_t( 'Werkstattinhaber können ihren Betrieb über den Menüpunkt „Betrieb eintragen“ in wenigen Minuten kostenlos registrieren und regional sichtbar machen.' ),
            ),
        );
    }

    /**
     * Gibt das JSON-LD Schema im <head> aus
     */
    public static function output_faq_schema() {
        if ( is_tax( 'mechanic_city' ) ) {
            // schema-seo.php erzeugt bereits FAQPage-Schema für Ortsseiten
            return;
        }

        $faqs = self::get_current_faqs();
        if ( empty( $faqs ) ) {
            return;
        }

        $main_entity = array();
        foreach ( $faqs as $item ) {
            $main_entity[] = array(
                '@type'          => 'Question',
                'name'           => wp_strip_all_tags( $item['q'] ),
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text'  => wp_strip_all_tags( $item['a'] ),
                ),
            );
        }

        $schema = array(
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $main_entity,
        );

        echo "\n<!-- FindeWerkstatt.de FAQ Rich Snippet Schema -->\n";
        echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "</script>\n";
    }

    /**
     * Rendert die sichtbare FAQ-Akkordeon-Sektion
     */
    public static function render_faq_section() {
        $faqs = self::get_current_faqs();
        if ( empty( $faqs ) ) {
            return;
        }
        ?>
        <section class="fw-box fw-faq-section" style="margin-top: 36px;">
            <div class="fw-section-header" style="text-align: left; margin-bottom: 20px;">
                <div class="fw-section-tag"><?php echo esc_html( findewerkstatt_t( 'Wissenswertes' ) ); ?></div>
                <h2 style="font-size: 22px; margin-top: 6px;"><?php echo esc_html( findewerkstatt_t( 'Häufig gestellte Fragen (FAQ)' ) ); ?></h2>
            </div>
            <div class="fw-faq-accordion-list">
                <?php foreach ( $faqs as $idx => $faq ) : ?>
                    <details class="fw-faq-item" <?php echo 0 === $idx ? 'open' : ''; ?>>
                        <summary class="fw-faq-question">
                            <span><?php echo esc_html( $faq['q'] ); ?></span>
                            <span class="fw-faq-arrow" aria-hidden="true">+</span>
                        </summary>
                        <div class="fw-faq-answer">
                            <p><?php echo esc_html( $faq['a'] ); ?></p>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
    }

    /**
     * Rendert den interaktiven Kostenschätzer (Typische Werkstattpreise in Deutschland)
     */
    public static function render_cost_estimator() {
        $prices = array(
            array( 'icon' => '🛡️', 'name' => findewerkstatt_t( 'Hauptuntersuchung (HU/AU TÜV)' ), 'range' => '140 € – 165 €', 'time' => 'ca. 30 Min.' ),
            array( 'icon' => '🔧', 'name' => findewerkstatt_t( 'Inspektion & Wartung (klein/groß)' ), 'range' => '150 € – 380 €', 'time' => '1–3 Std.' ),
            array( 'icon' => '🛑', 'name' => findewerkstatt_t( 'Bremsbeläge & Scheiben (Achse)' ), 'range' => '180 € – 420 €', 'time' => '1–2 Std.' ),
            array( 'icon' => '🛢️', 'name' => findewerkstatt_t( 'Ölwechsel inkl. Filter & Markenöl' ), 'range' => '85 € – 180 €', 'time' => 'ca. 45 Min.' ),
            array( 'icon' => '💻', 'name' => findewerkstatt_t( 'Fehlerauslese & Diagnose (OBD-2)' ), 'range' => '35 € – 75 €', 'time' => 'ca. 20 Min.' ),
            array( 'icon' => '🔄', 'name' => findewerkstatt_t( 'Räderwechsel & Auswuchten (4 Räder)' ), 'range' => '35 € – 70 €', 'time' => 'ca. 30 Min.' ),
            array( 'icon' => '❄️', 'name' => findewerkstatt_t( 'Klimaanlagenservice & Desinfektion' ), 'range' => '80 € – 160 €', 'time' => 'ca. 1 Std.' ),
            array( 'icon' => '⚙️', 'name' => findewerkstatt_t( 'Zahnriemenwechsel mit Wasserpumpe' ), 'range' => '450 € – 950 €', 'time' => '3–5 Std.' ),
        );
        ?>
        <section class="fw-box fw-cost-estimator-section" style="margin-top: 36px; background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);">
            <div class="fw-section-header" style="text-align: left; margin-bottom: 20px;">
                <div class="fw-section-tag" style="background: rgba(251,96,6,0.1); color: #fb6006; border-color: rgba(251,96,6,0.2);">
                    <?php echo esc_html( findewerkstatt_t( 'Preis-Orientierung' ) ); ?>
                </div>
                <h2 style="font-size: 22px; margin-top: 6px;"><?php echo esc_html( findewerkstatt_t( 'Typische Werkstattkosten im Überblick' ) ); ?></h2>
                <p style="color: var(--fw-text-muted); font-size: 14px; margin-top: 4px;">
                    <?php echo esc_html( findewerkstatt_t( 'Durchschnittliche Richtwerte für Kfz-Reparaturen in Deutschland (Material & Arbeitslohn).' ) ); ?>
                </p>
            </div>

            <div class="fw-cost-table-grid">
                <?php foreach ( $prices as $item ) : ?>
                    <div class="fw-cost-card">
                        <div class="fw-cost-header">
                            <span class="fw-cost-icon" aria-hidden="true"><?php echo $item['icon']; ?></span>
                            <span class="fw-cost-title"><?php echo esc_html( $item['name'] ); ?></span>
                        </div>
                        <div class="fw-cost-details">
                            <span class="fw-cost-price"><?php echo esc_html( $item['range'] ); ?></span>
                            <span class="fw-cost-time"><?php echo esc_html( $item['time'] ); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="margin-top: 24px; text-align: center; padding-top: 18px; border-top: 1px dashed #e2e8f0;">
                <p style="font-size: 14px; color: #475569; margin-bottom: 12px;">
                    <?php echo esc_html( findewerkstatt_t( 'Möchten Sie den genauen Preis für Ihr Fahrzeugmodell wissen?' ) ); ?>
                </p>
                <a href="#kontakt-formular" class="fw-btn fw-btn-primary fw-open-quote-modal" style="display: inline-flex; align-items: center; gap: 8px;">
                    <span>⚡</span>
                    <span><?php echo esc_html( findewerkstatt_t( 'Kostenlosen Kostenvoranschlag anfragen' ) ); ?></span>
                </a>
            </div>
        </section>
        <?php
    }
}

// Initialisieren
FindeWerkstatt_FAQ_Manager::init();
