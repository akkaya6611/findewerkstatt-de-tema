<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class OTB_AI_Groq {

    const API_URL = 'https://api.groq.com/openai/v1/chat/completions';

    private static function system_prompt() {
        return 'Sen deneyimli bir Türk oto ustasısın. Kullanıcının araç arıza bilgilerini analiz et ve SADECE aşağıdaki JSON şemasında cevap ver, başka hiçbir şey yazma:
{"kurzer_text":"...","wahrscheinliche_ursachen":["..."],"grundlegende_kontrollen":["..."],"empfohlene_werkstatt_typen":["..."],"kosten_einschaetzung":"düşük|orta|yüksek","folgen_bei_ignorieren":"...","dringlichkeit":"düşük|orta|yüksek","tags":["..."],"hinweis":"..."}
Tüm değerler TÜRKÇE olsun. JSON dışında hiçbir şey yazma.';
    }

    public static function analyse( array $data ) {
        $api_key = OTB_AI_Settings::get( OTB_AI_Settings::API_KEY );
        $model   = trim( OTB_AI_Settings::get( OTB_AI_Settings::MODEL, 'llama-3.3-70b-versatile' ) ) ?: 'llama-3.3-70b-versatile';

        if ( empty( $api_key ) ) return [ 'error' => 'Groq API anahtarı eksik.' ];

        $user_msg = 'Problem: ' . $data['beschreibung']
            . ' | Araç: ' . $data['marke_modell']
            . ' | Km: ' . $data['kilometer']
            . ' | Durum: ' . $data['situationen']
            . ' | Sesler: ' . $data['geraeusche']
            . ' | Uyarı: ' . $data['warnleuchten']
            . ' | Süre: ' . $data['dauer']
            . ' | Şehir: ' . $data['ort'];

        $body = [
            'model'       => $model,
            'messages'    => [
                [ 'role' => 'system', 'content' => self::system_prompt() ],
                [ 'role' => 'user',   'content' => $user_msg ],
            ],
            'response_format' => [ 'type' => 'json_object' ],
            'temperature' => 0.4,
        ];

        $response = wp_remote_post( self::API_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode( $body ),
            'timeout' => 30,
        ] );

        if ( is_wp_error( $response ) ) return [ 'error' => 'Bağlantı hatası: ' . $response->get_error_message() ];

        $code = wp_remote_retrieve_response_code( $response );
        $raw  = wp_remote_retrieve_body( $response );

        if ( $code < 200 || $code >= 300 ) {
            $err = json_decode( $raw, true );
            return [ 'error' => $err['error']['message'] ?? "API hatası ($code)" ];
        }

        $decoded = json_decode( $raw, true );
        $text    = $decoded['choices'][0]['message']['content'] ?? null;
        if ( ! $text ) return [ 'error' => 'Groq boş yanıt döndürdü.' ];

        $json = json_decode( $text, true );
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
