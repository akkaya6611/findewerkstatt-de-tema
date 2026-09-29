<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AK_Groq {

    const API_URL = 'https://api.groq.com/openai/v1/chat/completions';

    private static function system_prompt() {
        return 'Sen deneyimli bir Türk oto ustasısın. Verilen OBD arıza kodunu analiz et ve SADECE aşağıdaki JSON formatında cevap ver, başka hiçbir şey yazma:
{"kod":"...","isim":"...","aciklama":"...","olasi_nedenler":["..."],"belirtiler":["..."],"cozum_onerileri":["..."],"aciliyet":"düşük|orta|yüksek","tahmini_maliyet":"...","teknik_detay":"...","sik_gorulen_araclar":["..."]}
Tüm değerler TÜRKÇE olsun. sik_gorulen_araclar alanına bu arıza kodunun en sık görüldüğü 3-5 araç marka/model/yıl bilgisini yaz (örn: "Volkswagen Golf 2010-2015"). JSON dışında hiçbir şey yazma.';
    }

    public static function analyse( $code, $name ) {
        $api_key = AK_Settings::get( AK_Settings::API_KEY );
        $model   = trim( AK_Settings::get( AK_Settings::MODEL, 'llama-3.3-70b-versatile' ) ) ?: 'llama-3.3-70b-versatile';

        if ( empty( $api_key ) ) return [ 'error' => 'Groq API anahtarı eksik.' ];

        $user_msg = "OBD Kodu: $code\nKod Adı: $name\nBu arıza kodunu detaylıca analiz et.";

        $body = [
            'model'           => $model,
            'messages'        => [
                [ 'role' => 'system', 'content' => self::system_prompt() ],
                [ 'role' => 'user',   'content' => $user_msg ],
            ],
            'response_format' => [ 'type' => 'json_object' ],
            'temperature'     => 0.3,
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

        $code_http = wp_remote_retrieve_response_code( $response );
        $raw       = wp_remote_retrieve_body( $response );

        if ( $code_http < 200 || $code_http >= 300 ) {
            $err = json_decode( $raw, true );
            return [ 'error' => $err['error']['message'] ?? "API hatası ($code_http)" ];
        }

        $decoded = json_decode( $raw, true );
        $text    = $decoded['choices'][0]['message']['content'] ?? null;
        if ( ! $text ) return [ 'error' => 'Groq boş yanıt döndürdü.' ];

        $json = json_decode( $text, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) return [ 'error' => 'JSON çözümlenemedi.' ];

        return $json;
    }
}
