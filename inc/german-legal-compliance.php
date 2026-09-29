<?php
/**
 * FindeWerkstatt.de - Deutsche Rechtssicherheit (DSGVO, Impressum § 5 DDG, Haftungsausschluss)
 * 
 * Schützt den Betreiber vor Abmahnungen durch:
 * 1. Automatische Anlage von Impressum-, Datenschutz-, AGB- und Kontaktseiten
 * 2. Gesetzlich konformen Haftungsausschluss für Fremddaten (Google Maps / Google Unternehmensprofile)
 * 3. Schutz der Privatsphäre & Cookiefreie Basiskonfiguration
 * 
 * @package FindeWerkstatt
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FindeWerkstatt_Legal_Compliance {

    const OPTION_PAGES_CREATED = 'findewerkstatt_legal_pages_created_v1';

    public static function init() {
        add_action( 'admin_init', array( __CLASS__, 'create_legal_pages_if_missing' ) );
        add_filter( 'the_content', array( __CLASS__, 'filter_listing_disclaimer' ), 10 );
    }

    /**
     * Automatische Erstellung der rechtlichen Pflichtseiten
     */
    public static function create_legal_pages_if_missing() {
        if ( get_option( self::OPTION_PAGES_CREATED ) ) {
            return;
        }

        $pages = array(
            'impressum' => array(
                'title'   => 'Impressum',
                'content' => self::get_impressum_content(),
            ),
            'datenschutz' => array(
                'title'   => 'Datenschutzerklärung',
                'content' => self::get_datenschutz_content(),
            ),
            'agb' => array(
                'title'   => 'Allgemeine Geschäftsbedingungen (AGB)',
                'content' => self::get_agb_content(),
            ),
            'kontakt' => array(
                'title'   => 'Kontakt & Support',
                'content' => self::get_kontakt_content(),
            ),
        );

        foreach ( $pages as $slug => $data ) {
            $existing = get_page_by_path( $slug );
            if ( ! $existing ) {
                wp_insert_post( array(
                    'post_title'     => $data['title'],
                    'post_name'      => $slug,
                    'post_content'   => $data['content'],
                    'post_status'    => 'publish',
                    'post_type'      => 'page',
                    'comment_status' => 'closed',
                    'ping_status'    => 'closed',
                ) );
            }
        }

        update_option( self::OPTION_PAGES_CREATED, 1 );
    }

    /**
     * Standard Disclaimer für Kfz-Werkstatt-Profile
     */
    public static function filter_listing_disclaimer( $content ) {
        if ( is_singular( 'mechanic' ) ) {
            $disclaimer_html = '
            <div class="mechanic-source-notice" style="background:#fffbeb; border:1px solid #fef3c7; border-left:4px solid #f59e0b; border-radius:10px; padding:16px 20px; margin-top:24px; font-size:13px; color:#92400e; line-height:1.6;">
                <div style="font-weight:700; margin-bottom:4px; display:flex; align-items:center; gap:6px;">
                    <i class="fa-solid fa-circle-info" style="color:#d97706;"></i> Hinweis für Werkstattinhaber & Verbraucher
                </div>
                <div>
                    Die auf dieser Seite dargestellten Angaben zu Anschrift, Telefonnummer und Leistungen wurden aus öffentlich zugänglichen Quellen (wie Google Maps / Google Unternehmensprofil) oder über direkte Nutzereinträge erhoben. 
                    Sollten Sie als Inhaber eine Korrektur, Ergänzung oder Löschung Ihres Eintrags wünschen, senden Sie uns bitte eine kurze Nachricht an: 
                    <a href="mailto:admin@findewerkstatt.de" style="color:#b45309; font-weight:800; text-decoration:underline;">admin@findewerkstatt.de</a>. Wir bearbeiten jedes Anliegen unverzüglich und kostenfrei.
                </div>
            </div>';

            // Wenn noch nicht enthalten, unten anhängen
            if ( strpos( $content, 'admin@findewerkstatt.de' ) === false ) {
                $content .= $disclaimer_html;
            }
        }
        return $content;
    }

    private static function get_impressum_content() {
        return '<h2>Angaben gemäß § 5 DDG (Digitale-Dienste-Gesetz)</h2>
<p><strong>FindeWerkstatt.de</strong><br>
Ein Projekt von Serkan AKKAYA<br>
E-Mail: <a href="mailto:admin@findewerkstatt.de">admin@findewerkstatt.de</a><br>
Website: <a href="https://findewerkstatt.de">https://findewerkstatt.de</a></p>

<h3>Verantwortlich für den Inhalt nach § 18 Abs. 2 MStV</h3>
<p>Serkan AKKAYA<br>
E-Mail: admin@findewerkstatt.de</p>

<h3>Streitschlichtung</h3>
<p>Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS) bereit: <a href="https://ec.europa.eu/consumers/odr" target="_blank" rel="noopener">https://ec.europa.eu/consumers/odr</a>.<br>
Unsere E-Mail-Adresse finden Sie oben im Impressum. Wir sind nicht bereit oder verpflichtet, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.</p>

<h3>Haftung für Inhalte</h3>
<p>Als Diensteanbieter sind wir gemäß § 7 Abs.1 DDG für eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich. Nach §§ 8 bis 10 DDG sind wir als Diensteanbieter jedoch nicht verpflichtet, übermittelte oder gespeicherte fremde Informationen zu überwachen oder nach Umständen zu forschen, die auf eine rechtswidrige Tätigkeit hinweisen.</p>';
    }

    private static function get_datenschutz_content() {
        return '<h2>1. Datenschutz auf einen Blick</h2>
<p>Die folgenden Hinweise geben einen einfachen Überblick darüber, was mit Ihren personenbezogenen Daten passiert, wenn Sie unsere Website <strong>FindeWerkstatt.de</strong> besuchen. Personenbezogene Daten sind alle Daten, mit denen Sie persönlich identifiziert werden können.</p>

<h3>Datenerfassung auf unserer Website</h3>
<p><strong>Wer ist verantwortlich für die Datenerfassung auf dieser Website?</strong><br>
Die Datenverarbeitung auf dieser Website erfolgt durch den Websitebetreiber. Dessen Kontaktdaten können Sie dem Impressum dieser Website entnehmen.</p>

<p><strong>Wie erfassen wir Ihre Daten?</strong><br>
Ihre Daten werden zum einen dadurch erhoben, dass Sie uns diese mitteilen (z. B. bei der Eintragung einer Werkstatt oder per E-Mail). Andere Daten werden automatisch oder nach Ihrer Einwilligung beim Besuch der Website durch unsere IT-Systeme erfasst. Das sind vor allem technische Daten (z. B. Internetbrowser, Betriebssystem oder Uhrzeit des Seitenaufrufs).</p>

<h3>Cookiefreie Analytik & Schutz der Privatsphäre</h3>
<p>Wir verwenden eine moderne, serverbasierte und cookiefreie Zählmethode zur Reichweitenmessung. Ihre IP-Adresse wird sofort nach der Erhebung irreversibel maskiert (z. B. 192.168.xxx.xxx). Es werden keine Profile gebildet und keine Daten an Werbenetzwerke oder Dritte weitergegeben.</p>';
    }

    private static function get_agb_content() {
        return '<h2>Allgemeine Nutzungsbedingungen für FindeWerkstatt.de</h2>
<p>Stand: 2026</p>
<p><strong>1. Geltungsbereich:</strong> FindeWerkstatt.de stellt eine Online-Plattform zur Verfügung, auf der Kfz-Werkstätten, 24h Abschleppdienste und Kfz-Fachbetriebe in Deutschland gesucht und gefunden werden können.</p>
<p><strong>2. Kostenlose Basiseinträge:</strong> Der Basiseintrag für Werkstattbetreiber und die Nutzung der Suchfunktion durch Verbraucher ist vollständig kostenlos.</p>
<p><strong>3. Richtigkeit der Angaben:</strong> Die Betreiber von FindeWerkstatt.de bemühen sich um ständige Aktualität und Richtigkeit aller hinterlegten Kontaktdaten, übernehmen jedoch keine Gewähr für die ständige Verfügbarkeit der aufgeführten Werkstätten.</p>';
    }

    private static function get_kontakt_content() {
        return '<h2>Kontaktieren Sie FindeWerkstatt.de</h2>
<p>Haben Sie Fragen, Anregungen oder möchten Sie Ihre Werkstatt eintragen bzw. Ihren bestehenden Eintrag aktualisieren?</p>
<p>Schreiben Sie uns direkt eine E-Mail an: <a href="mailto:admin@findewerkstatt.de"><strong>admin@findewerkstatt.de</strong></a></p>
<p>Wir antworten in der Regel innerhalb von 24 Stunden.</p>';
    }
}

FindeWerkstatt_Legal_Compliance::init();
