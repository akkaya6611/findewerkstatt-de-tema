<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class OTB_AI_Gemini {

    private static function prompt( $data ) {
        return 'Sen deneyimli bir Türk oto ustasısın. Aşağıdaki araç arıza bilgilerini analiz et ve SADECE bu JSON şemasında cevap ver, başka hiçbir şey yazma:
{"kurzer_text":"...","wahrscheinliche_ursachen":["..."],"grundlegende_kontrollen":["..."],"empfohlene_werkstatt_typen":["..."],"kosten_einschaetzung":"düşük|orta|yüksek","folgen_bei_ignorieren":"...","dringlichkeit":"düşük|orta|yüksek","tags":["..."],"hinweis":"..."}
Tüm değerler TÜRKÇE olsun.

Problem: ' . $data['beschreibung'] . '
Araç: ' . $data['marke_modell'] . '
Kilometre: ' . $data['kilometer'] . '
Durum: ' . $data['situationen'] . '
Sesler: ' . $data['geraeusche'] . '
Uyarı lambaları: ' . $data['warnleuchten'] . '
Süre: ' . $data['dauer'] . '
Şehir: ' . $data['ort'];
    }

    public static function analyse( array $data ) {
        $api_key = OTB_AI_Settings::get( OTB_AI_Settings::API_KEY );
        $model   = trim( OTB_AI_Settings::get( OTB_AI_Settings::MODEL, 'gemini-2.0-flash' ) ) ?: 'gemini-2.0-flash';

        if ( empty( $api_key ) ) return [ 'error' => 'Gemini API anahtarı eksik.' ];

        $url  = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";
        $body = [
            'contents'         => [ [ 'parts' => [ [ 'text' => self::prompt( $data ) ] ] ] ],
            'generationConfig' => [ 'responseMimeType' => 'application/json' ],
        ];

        $response = wp_remote_post( $url, [
            'headers' => [ 'Content-Type' => 'application/json' ],
            'body'    => wp_json_encode( $body ),
            'timeout' => 30,
        ] );

        if ( is_wp_error( $response ) ) return [ 'error' => 'Bağlantı hatası: ' . $response->get_error_message() ];

        $code = wp_remote_retrieve_response_code( $response );
        $raw  = wp_remote_retrieve_body( $response );

        if ( $code < 200 || $code >= 300 ) {
            $err = json_decode( $raw, true );
            $msg = $err['error']['message'] ?? "API hatası ($code)";
            return [ 'error' => $msg ];
        }

        $decoded = json_decode( $raw, true );
        $text    = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if ( ! $text ) return [ 'error' => 'Gemini boş yanıt döndürdü.' ];

        // Markdown kod bloğu varsa temizle
        $text = preg_replace( '/^```json\s*/i', '', trim( $text ) );
        $text = preg_replace( '/```\s*$/i', '', $text );

        $json = json_decode( trim( $text ), true );
        if ( json_last_error() !== JSON_ERROR_NONE ) return [ 'error' => 'JSON çözümlenemedi.' ];

        return $json;
    }

    public static function city_from_ort( $ort ) {
        $ort = preg_replace( '/^\s*[A-Za-z]{1,3}\s*-\s*/u', '', trim( $ort ) );
        $ort = trim( preg_replace( '/\s+/u', ' ', preg_replace( '/\b\d{5}\b/u', '', $ort ) ) );
        if ( $ort ) return $ort;
        $parts = preg_split( '/\s+/u', trim( $ort ) );
        return trim( (string) end( $parts ) );
    }
}
