<?php
/**
 * German directory copy from real location context and published profile counts.
 * All values are plain text; templates escape them when rendering.
 *
 * @package FindeWerkstatt
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Preserve standalone provenance checks while using the shared catalog on the site. */
function findewerkstatt_location_translate( $text ) {
    return function_exists( 'findewerkstatt_t' ) ? findewerkstatt_t( $text ) : $text;
}

function findewerkstatt_location_language() {
    return function_exists( 'findewerkstatt_language' ) ? findewerkstatt_language() : 'de';
}

/** A grammatical location phrase without inventing administrative relationships. */
function findewerkstatt_location_phrase( $context ) {
    $name = trim( (string) ( $context['name'] ?? '' ) );
    $parent = trim( (string) ( $context['parent_name'] ?? '' ) );
    $kind = (string) ( $context['kind'] ?? '' );
    $language = findewerkstatt_location_language();
    if ( 'de' !== $language ) {
        if ( '' === $name || ( 'Deutschland' === $name && ! $kind ) ) { return findewerkstatt_location_translate( 'in Deutschland' ); }
        if ( in_array( $kind, array( 'district', 'borough' ), true ) && '' !== $parent ) {
            return sprintf( findewerkstatt_location_translate( 'im Stadtteil %1$s in %2$s' ), $name, $parent );
        }
        if ( 'state' === $kind ) { return sprintf( findewerkstatt_location_translate( 'im Bundesland %s' ), $name ); }
        if ( in_array( $kind, array( 'county', 'region', 'district', 'borough' ), true ) ) {
            return sprintf( findewerkstatt_location_translate( 'für das Gebiet %s' ), $name );
        }
        return sprintf( findewerkstatt_location_translate( 'in %s' ), $name );
    }
    if ( '' === $name ) { return 'in Deutschland'; }

    if ( in_array( $kind, array( 'city', 'city_state' ), true ) ) { return 'in ' . $name; }
    if ( 'state' === $kind ) {
        if ( 'Bremen' === $name ) { return 'im Bundesland Bremen'; }
        return ( 'Saarland' === $name ? 'im ' : 'in ' ) . $name;
    }
    if ( in_array( $kind, array( 'district', 'borough' ), true ) ) {
        if ( '' === $parent ) { return 'für das Gebiet ' . $name; }
        return ( 'borough' === $kind ? 'im Bezirk ' : 'im Stadtteil ' ) . $name . ' in ' . $parent;
    }
    if ( 'county' === $kind ) {
        if ( preg_match( '/^(.+)er Kreis$/u', $name, $matches ) ) { return 'im ' . $matches[1] . 'en Kreis'; }
        if ( preg_match( '/^(?:Landkreis|Kreis)\s|kreis$|\sLand$/iu', $name ) ) { return 'im ' . $name; }
        if ( preg_match( '/^Städteregion\s/iu', $name ) ) { return 'in der ' . $name; }
    }
    if ( in_array( $kind, array( 'county', 'region' ), true ) && preg_match( '/^(?:Region|Städteregion)\s/iu', $name ) ) {
        return 'in der ' . $name;
    }
    if ( 'region' === $kind && preg_match( '/^Regionalverband\s/iu', $name ) ) { return 'im ' . $name; }
    return 'für das Gebiet ' . $name;
}

/** Plain names supplied by the caller, limited to a readable real-world selection. */
function findewerkstatt_location_copy_names( $names ) {
    $result = array();
    foreach ( (array) $names as $name ) {
        if ( ! is_string( $name ) ) { continue; }
        $name = trim( preg_replace( '/\s+/u', ' ', $name ) );
        if ( '' !== $name && ! in_array( $name, $result, true ) ) { $result[] = $name; }
        if ( count( $result ) >= 6 ) { break; }
    }
    if ( count( $result ) <= 1 ) { return implode( '', $result ); }
    $last = array_pop( $result );
    return implode( ', ', $result ) . findewerkstatt_location_translate( ' und ' ) . $last;
}

/**
 * Shared copy for headings, visible content, metadata and FAQ structured data.
 * The caller supplies actual hierarchy, child names and displayed profile data.
 */
function findewerkstatt_location_copy( $context, $count = 0 ) {
    if ( 'de' !== findewerkstatt_location_language() ) { return findewerkstatt_location_copy_translated( $context, $count ); }
    $name = trim( (string) ( $context['name'] ?? '' ) ) ?: 'Deutschland';
    $state = trim( (string) ( $context['state_name'] ?? '' ) );
    $parent = trim( (string) ( $context['parent_name'] ?? '' ) );
    $kind = (string) ( $context['kind'] ?? '' );
    $count = max( 0, (int) $count );
    $phrase = findewerkstatt_location_phrase( $context );
    $title = 'Kfz-Werkstätten ' . $phrase;
    $children = findewerkstatt_location_copy_names( $context['child_names'] ?? array() );
    $services = $count > 0 ? findewerkstatt_location_copy_names( $context['service_names'] ?? array() ) : '';
    $brands = $count > 0 ? findewerkstatt_location_copy_names( $context['brand_names'] ?? array() ) : '';

    if ( 0 === $count ) {
        $availability = 'Aktuell ist für dieses Gebiet noch kein Werkstattprofil veröffentlicht.';
        $intro = 'Auf FindeWerkstatt.de finden Sie das Verzeichnis der Kfz-Werkstätten und Autoreparaturbetriebe ' . $phrase . '. ' . $availability . ' Sobald Profile veröffentlicht sind, können Sie hier die Angaben zu Leistungen, Adresse und Kontakt vergleichen.';
        $meta = $title . ': Noch kein Profil veröffentlicht. Das Verzeichnis wird erweitert. Suchen Sie nach Ort und Leistung oder tragen Sie Ihren Betrieb ein.';
    } else {
        $availability = 1 === $count
            ? 'Derzeit ist ein Werkstattprofil für dieses Gebiet veröffentlicht.'
            : 'Derzeit sind ' . number_format( $count, 0, ',', '.' ) . ' Werkstattprofile für dieses Gebiet veröffentlicht.';
        $intro = 'FindeWerkstatt.de listet ' . ( 1 === $count ? 'ein veröffentlichtes Werkstattprofil' : number_format( $count, 0, ',', '.' ) . ' veröffentlichte Werkstattprofile' ) . ' ' . $phrase . '. Vergleichen Sie die hinterlegten Leistungen, Adressen und Kontaktdaten. Das Verzeichnis hilft Ihnen bei der Suche; Reparatur, Kosten und Termin klären Sie direkt mit der Werkstatt.';
        $meta = $title . ': ' . ( 1 === $count ? '1 veröffentlichtes Werkstattprofil' : number_format( $count, 0, ',', '.' ) . ' veröffentlichte Werkstattprofile' ) . '. Leistungen und Kontaktdaten vergleichen, Termine direkt beim Betrieb anfragen.';
    }

    switch ( $kind ) {
        case 'state':
            $scope_heading = 'Vom Bundesland zum passenden Ort';
            $scope = 'Diese Seite bündelt das Werkstattverzeichnis für das Bundesland ' . $name . '. Wählen Sie zuerst das Bundesland und anschließend Ihre Stadt oder Ihren Ort. Ohne Ortsauswahl durchsuchen Sie das gesamte Bundesland; mit Ortsauswahl grenzen Sie die angezeigten Betriebe ein.';
            $child_label = 'Zu den verlinkten Standortseiten gehören ';
            break;
        case 'city_state':
            $scope_heading = 'Werkstattsuche in der Stadt und ihren Bezirken';
            $scope = $name . ' ist eine Stadt und zugleich ein Bundesland. Dieses Verzeichnis umfasst die veröffentlichten Profile für die Stadt und ihre zugeordneten Bezirke. Wählen Sie einen Bezirk, wenn Sie Ihre Suche auf einen bestimmten Teil der Stadt eingrenzen möchten.';
            $child_label = 'Zu den verlinkten Bezirken gehören ';
            break;
        case 'city':
            $scope_heading = 'Das örtliche Werkstattverzeichnis';
            $scope = 'Diese Standortseite führt veröffentlichte Werkstattprofile für ' . $name . ' zusammen.';
            if ( '' !== $state ) { $scope .= ' Der Ort ist dem Bundesland ' . $state . ' zugeordnet.'; }
            if ( '' !== $parent && $parent !== $state ) { $scope .= ' Im Verzeichnis finden Sie das übergeordnete Gebiet unter ' . $parent . '.'; }
            $scope .= ' Prüfen Sie die konkrete Betriebsadresse im Profil. Der ausgewählte Ort allein sagt noch nichts über die Entfernung von Ihrem Startpunkt aus.';
            $child_label = 'Untergeordnete Standortseiten finden Sie für ';
            break;
        case 'district':
        case 'borough':
            $scope_heading = 'Den Standort innerhalb der Stadt eingrenzen';
            $scope = 'Die Auswahl bezieht sich auf ' . ( 'borough' === $kind ? 'den Bezirk ' : 'den Stadtteil ' ) . $name . ( '' !== $parent ? ' in ' . $parent : '' ) . '.';
            if ( '' !== $state && $state !== $parent ) { $scope .= ' Das zugeordnete Bundesland ist ' . $state . '.'; }
            $scope .= ' Hier erscheinen die diesem Gebiet zugeordneten veröffentlichten Werkstattprofile.';
            if ( '' !== $parent ) { $scope .= ' Über das Verzeichnis für ' . $parent . ' können Sie Ihre Suche auf die übergeordnete Stadt ausweiten.'; }
            $child_label = 'Weitere untergeordnete Standortseiten betreffen ';
            break;
        case 'county':
        case 'region':
        default:
            $scope_heading = 'Werkstätten nach Gebiet und Ort suchen';
            $scope = 'Diese Seite bündelt veröffentlichte Werkstattprofile für das Gebiet ' . $name . '.';
            if ( '' !== $state ) { $scope .= ' Das Verzeichnis ordnet dieses Gebiet dem Bundesland ' . $state . ' zu.'; }
            $scope .= ' Nutzen Sie die verlinkten Orte oder die Ortsauswahl, um Ihre Suche einzugrenzen. Entscheidend für die Anfahrt bleibt die konkrete Adresse des Betriebs im Werkstattprofil.';
            $child_label = 'Zu den verlinkten Standortseiten gehören ';
            break;
    }
    if ( '' !== $children ) { $scope .= ' ' . $child_label . $children . '.'; }

    $service_text = 'Beschreiben Sie die gewünschte Reparatur oder Wartung und nennen Sie Fahrzeugmarke, Modell, Baujahr und Motorisierung. Nutzen Sie Leistungs- und Markenfilter. Klären Sie die Eignung für Ihr Fahrzeug anhand des Profils und direkt mit dem Betrieb.';
    if ( '' !== $services ) { $service_text .= ' In den angezeigten Profilen werden folgende Leistungen genannt: ' . $services . '.'; }
    if ( '' !== $brands ) { $service_text .= ' Genannte Fahrzeugmarken sind ' . $brands . '.'; }

    return array(
        'title' => $title,
        'intro' => $intro,
        'meta_description' => $meta,
        'sections' => array(
            array( 'heading' => $scope_heading, 'text' => $scope ),
            array( 'heading' => 'Leistungen und Fahrzeug vorab klären', 'text' => $service_text ),
            array( 'heading' => 'Reparaturumfang und Kosten besprechen', 'text' => 'Beschreiben Sie die gewünschten Arbeiten oder Symptome. Fragen Sie nach Diagnose, Ersatzteilen und Arbeitskosten. Vergleichen Sie den Reparaturumfang im Kostenvoranschlag. Klären Sie, wie der Betrieb Sie informiert, wenn während der Arbeiten weitere Reparaturen nötig werden.' ),
            array( 'heading' => 'Direkt mit dem Betrieb Kontakt aufnehmen', 'text' => 'Nutzen Sie die Kontaktdaten im Werkstattprofil und fragen Sie nach Termin, Zeitbedarf und Fahrzeugabgabe. Bestätigen Sie Öffnungszeiten und Verfügbarkeit direkt beim Betrieb. FindeWerkstatt.de stellt das Verzeichnis bereit; die Werkstatt führt die Arbeiten durch.' ),
        ),
        'faqs' => array(
            array( 'question' => 'Welche Werkstätten finde ich ' . $phrase . '?', 'answer' => $availability . ' ' . ( 0 === $count ? 'Künftige Einträge werden diesem Standort oder seinen untergeordneten Orten zugeordnet.' : 'Die Liste zeigt die diesem Standort zugeordneten Betriebe und gegebenenfalls Profile aus untergeordneten Orten.' ) . ' Für weitere Standorte nutzen Sie die Ortsauswahl.' ),
            array( 'question' => 'Wie grenze ich die Werkstattsuche ein?', 'answer' => 'Wählen Sie Bundesland und Ort sowie bei Bedarf Leistung oder Fahrzeugmarke. Die Filter zeigen passende veröffentlichte Profile. Prüfen Sie deren Angaben und fragen Sie den Betrieb nach der Eignung für Ihr Fahrzeug.' ),
            array( 'question' => 'Wie erhalte ich einen Kostenvoranschlag?', 'answer' => 'Kontaktieren Sie den Betrieb mit Fahrzeugdaten und einer Beschreibung der gewünschten Arbeiten oder Symptome. Fragen Sie nach dem vorgesehenen Reparaturumfang, Ersatzteilen und Arbeitskosten. Preise und verbindliche Angebote erhalten Sie von der Werkstatt.' ),
            array( 'question' => 'Wie vereinbare ich einen Werkstatttermin?', 'answer' => 'Nutzen Sie die Kontaktdaten im Werkstattprofil und vereinbaren Sie den Termin direkt mit dem Betrieb. Klären Sie Verfügbarkeit, Fahrzeugabgabe und den voraussichtlichen Zeitbedarf.' ),
            array( 'question' => 'Führt FindeWerkstatt.de selbst Autoreparaturen durch?', 'answer' => 'FindeWerkstatt.de ist ein Werkstattverzeichnis. Die aufgeführten Betriebe geben ihre Leistungen und Kontaktdaten an. Reparaturen, Angebote und Termine werden direkt mit dem jeweiligen Betrieb vereinbart.' ),
        ),
        'cta_heading' => 'Ihre Werkstatt ' . $phrase . ' sichtbar machen',
        'cta_text' => 'Tragen Sie Ihren Betrieb mit der richtigen Standortzuordnung, Adresse, Leistungen und Kontaktdaten ein. Nach der Veröffentlichung erscheint das Profil im passenden örtlichen Verzeichnis und in den zugehörigen übergeordneten Gebieten.',
    );
}

/** Full sentence templates let each language place real names and counts naturally. */
function findewerkstatt_location_copy_translated( $context, $count = 0 ) {
    $t = 'findewerkstatt_location_translate';
    $name = trim( (string) ( $context['name'] ?? '' ) ) ?: $t( 'Deutschland' );
    $state = trim( (string) ( $context['state_name'] ?? '' ) );
    $parent = trim( (string) ( $context['parent_name'] ?? '' ) );
    $kind = (string) ( $context['kind'] ?? '' );
    $count = max( 0, (int) $count );
    $number = number_format( $count, 0, 'en' === findewerkstatt_location_language() ? '.' : ',', 'ru' === findewerkstatt_location_language() ? ' ' : ( 'en' === findewerkstatt_location_language() ? ',' : '.' ) );
    $phrase = findewerkstatt_location_phrase( $context );
    $title = sprintf( $t( 'Kfz-Werkstätten %s' ), $phrase );
    $children = findewerkstatt_location_copy_names( $context['child_names'] ?? array() );
    $services = $count ? findewerkstatt_location_copy_names( array_map( $t, $context['service_names'] ?? array() ) ) : '';
    $brands = $count ? findewerkstatt_location_copy_names( $context['brand_names'] ?? array() ) : '';
    $availability = $count
        ? sprintf( $t( 'Veröffentlichte Werkstattprofile für dieses Gebiet: %s.' ), $number )
        : $t( 'Aktuell ist für dieses Gebiet noch kein Werkstattprofil veröffentlicht.' );
    $intro = $count
        ? sprintf( $t( 'FindeWerkstatt.de listet Werkstattprofile %1$s. Veröffentlichte Profile: %2$s. Vergleichen Sie die hinterlegten Leistungen, Adressen und Kontaktdaten. Reparaturen, Kosten und Termine klären Sie direkt mit dem Betrieb.' ), $phrase, $number )
        : sprintf( $t( 'Hier entsteht das Verzeichnis der Kfz-Werkstätten %s. Aktuell ist noch kein Profil für dieses Gebiet veröffentlicht. Sobald Einträge vorliegen, können Sie Leistungen, Adressen und Kontaktdaten vergleichen.' ), $phrase );
    $meta = $count
        ? sprintf( $t( '%1$s: %2$s veröffentlichte Profile. Leistungen und Kontaktdaten vergleichen, Termine direkt beim Betrieb anfragen.' ), $title, $number )
        : sprintf( $t( '%s: Noch kein Profil veröffentlicht. Suchen Sie nach Ort und Leistung oder tragen Sie Ihren Betrieb ein.' ), $title );

    switch ( $kind ) {
        case 'state':
            $scope_heading = $t( 'Vom Bundesland zum passenden Ort' );
            $scope = sprintf( $t( 'Diese Seite bündelt das Werkstattverzeichnis für das Bundesland %s. Wählen Sie anschließend eine Stadt oder einen Ort. Ohne Ortsauswahl durchsuchen Sie das gesamte Bundesland; mit Ortsauswahl grenzen Sie die angezeigten Betriebe ein.' ), $name );
            break;
        case 'city_state':
            $scope_heading = $t( 'Werkstattsuche in der Stadt und ihren Bezirken' );
            $scope = sprintf( $t( '%s ist eine Stadt und zugleich ein Bundesland. Dieses Verzeichnis umfasst die veröffentlichten Profile für die Stadt und ihre zugeordneten Bezirke. Mit der Bezirksauswahl können Sie Ihre Suche auf einen Teil der Stadt eingrenzen.' ), $name );
            break;
        case 'city':
            $scope_heading = $t( 'Das örtliche Werkstattverzeichnis' );
            $scope = sprintf( $t( 'Diese Standortseite führt veröffentlichte Werkstattprofile für %s zusammen. Prüfen Sie die konkrete Betriebsadresse im Profil. Der ausgewählte Ort allein sagt noch nichts über die Entfernung von Ihrem Startpunkt aus.' ), $name );
            break;
        case 'district':
        case 'borough':
            $scope_heading = $t( 'Den Standort innerhalb der Stadt eingrenzen' );
            $scope = sprintf( $t( 'Die Auswahl bezieht sich auf das Gebiet %s. Hier erscheinen die diesem Gebiet zugeordneten veröffentlichten Werkstattprofile. Prüfen Sie die genaue Adresse im Profil, bevor Sie Ihre Anfahrt planen.' ), $name );
            break;
        default:
            $scope_heading = $t( 'Werkstätten nach Gebiet und Ort suchen' );
            $scope = sprintf( $t( 'Diese Seite bündelt veröffentlichte Werkstattprofile für das Gebiet %s. Nutzen Sie die verlinkten Orte oder die Ortsauswahl, um Ihre Suche einzugrenzen. Entscheidend für die Anfahrt bleibt die konkrete Adresse des Betriebs.' ), $name );
            break;
    }
    if ( '' !== $state && $state !== $name ) { $scope .= ' ' . sprintf( $t( 'Das zugeordnete Bundesland ist %s.' ), $state ); }
    if ( '' !== $parent && $parent !== $state ) { $scope .= ' ' . sprintf( $t( 'Über das Verzeichnis für %s können Sie Ihre Suche auf das übergeordnete Gebiet ausweiten.' ), $parent ); }
    if ( '' !== $children ) { $scope .= ' ' . sprintf( $t( 'Zu den verlinkten Standortseiten gehören %s.' ), $children ); }
    $service_text = $t( 'Beschreiben Sie die gewünschte Reparatur oder Wartung und nennen Sie Fahrzeugmarke, Modell, Baujahr und Motorisierung. Nutzen Sie Leistungs- und Markenfilter. Klären Sie die Eignung für Ihr Fahrzeug anhand des Profils und direkt mit dem Betrieb.' );
    if ( '' !== $services ) { $service_text .= ' ' . sprintf( $t( 'In den angezeigten Profilen werden folgende Leistungen genannt: %s.' ), $services ); }
    if ( '' !== $brands ) { $service_text .= ' ' . sprintf( $t( 'Genannte Fahrzeugmarken sind %s.' ), $brands ); }
    $list_scope = $count ? $t( 'Die Liste zeigt Betriebe dieses Standorts und gegebenenfalls Profile aus untergeordneten Orten.' ) : $t( 'Künftige Einträge werden diesem Standort oder seinen untergeordneten Orten zugeordnet.' );
    return array(
        'title' => $title, 'intro' => $intro, 'meta_description' => $meta,
        'sections' => array(
            array( 'heading' => $scope_heading, 'text' => $scope ),
            array( 'heading' => $t( 'Leistungen und Fahrzeug vorab klären' ), 'text' => $service_text ),
            array( 'heading' => $t( 'Reparaturumfang und Kosten besprechen' ), 'text' => $t( 'Beschreiben Sie die gewünschten Arbeiten oder Symptome. Fragen Sie nach Diagnose, Ersatzteilen und Arbeitskosten. Vergleichen Sie den Reparaturumfang im Kostenvoranschlag. Klären Sie, wie der Betrieb Sie informiert, wenn während der Arbeiten weitere Reparaturen nötig werden.' ) ),
            array( 'heading' => $t( 'Direkt mit dem Betrieb Kontakt aufnehmen' ), 'text' => $t( 'Nutzen Sie die Kontaktdaten im Werkstattprofil und fragen Sie nach Termin, Zeitbedarf und Fahrzeugabgabe. Bestätigen Sie Öffnungszeiten und Verfügbarkeit direkt beim Betrieb. FindeWerkstatt.de stellt das Verzeichnis bereit; die Werkstatt führt die Arbeiten durch.' ) ),
        ),
        'faqs' => array(
            array( 'question' => sprintf( $t( 'Welche Werkstätten finde ich %s?' ), $phrase ), 'answer' => $availability . ' ' . $list_scope . ' ' . $t( 'Für weitere Standorte nutzen Sie die Ortsauswahl.' ) ),
            array( 'question' => $t( 'Wie grenze ich die Werkstattsuche ein?' ), 'answer' => $t( 'Wählen Sie Bundesland und Ort sowie bei Bedarf Leistung oder Fahrzeugmarke. Die Filter zeigen passende veröffentlichte Profile. Prüfen Sie deren Angaben und fragen Sie den Betrieb nach der Eignung für Ihr Fahrzeug.' ) ),
            array( 'question' => $t( 'Wie erhalte ich einen Kostenvoranschlag?' ), 'answer' => $t( 'Kontaktieren Sie den Betrieb mit Fahrzeugdaten und einer Beschreibung der gewünschten Arbeiten oder Symptome. Fragen Sie nach dem vorgesehenen Reparaturumfang, Ersatzteilen und Arbeitskosten. Preise und verbindliche Angebote erhalten Sie von der Werkstatt.' ) ),
            array( 'question' => $t( 'Wie vereinbare ich einen Werkstatttermin?' ), 'answer' => $t( 'Nutzen Sie die Kontaktdaten im Werkstattprofil und vereinbaren Sie den Termin direkt mit dem Betrieb. Klären Sie Verfügbarkeit, Fahrzeugabgabe und den voraussichtlichen Zeitbedarf.' ) ),
            array( 'question' => $t( 'Führt FindeWerkstatt.de selbst Autoreparaturen durch?' ), 'answer' => $t( 'FindeWerkstatt.de ist ein Werkstattverzeichnis. Die aufgeführten Betriebe geben ihre Leistungen und Kontaktdaten an. Reparaturen, Angebote und Termine werden direkt mit dem jeweiligen Betrieb vereinbart.' ) ),
        ),
        'cta_heading' => sprintf( $t( 'Ihre Werkstatt %s sichtbar machen' ), $phrase ),
        'cta_text' => $t( 'Tragen Sie Ihren Betrieb mit der richtigen Standortzuordnung, Adresse, Leistungen und Kontaktdaten ein. Nach der Veröffentlichung erscheint das Profil im passenden örtlichen Verzeichnis und in den zugehörigen übergeordneten Gebieten.' ),
    );
}

/** Shared service/brand heading and metadata without translating business names. */
function findewerkstatt_directory_term_title( $term, $location = null ) {
    $name = 'service_type' === $term->taxonomy ? findewerkstatt_location_translate( $term->name ) : $term->name;
    if ( $location ) {
        $phrase = findewerkstatt_location_phrase( findewerkstatt_location_context( $location ) );
        return sprintf( findewerkstatt_location_translate( 'car_brand' === $term->taxonomy ? '%1$s-Werkstätten %2$s' : '%1$s %2$s' ), $name, $phrase );
    }
    return 'car_brand' === $term->taxonomy ? sprintf( findewerkstatt_location_translate( '%s-Werkstätten' ), $name ) : $name;
}
