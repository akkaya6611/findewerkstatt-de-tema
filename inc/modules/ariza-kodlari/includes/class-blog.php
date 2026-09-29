<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class AK_Blog {

    const API_URL = 'https://api.groq.com/openai/v1/chat/completions';

    /**
     * Kategori için sistem promptu
     */
    private static function system_prompt() {
        return 'Sen Türkiye\'nin en deneyimli otomotiv uzmanı ve SEO odaklı içerik yazarısın. 15 yıllık araç tamiri ve OBD diagnostik tecrüben var. Görevin: Verilen OBD arıza kodu kategorisi için kapsamlı, SEO uyumlu, okuyucu dostu bir Türkçe makale yazmak.

MAKALE KURALLARI:
- Dil: Türkçe, sade ve anlaşılır
- Ton: Uzman ama samimi, teknik ama erişilebilir
- Uzunluk: En az 1500 kelime
- SEO: Başlık, alt başlıklar, anahtar kelimeler doğal geçmeli
- Yapı: Giriş → Kategori açıklaması → Alt kodlar → Teşhis yöntemleri → Çözüm önerileri → Sonuç
- HTML formatında yaz, <h2>, <h3>, <p>, <ul>, <li>, <strong>, <em> tagları kullan
- Hiçbir markdown kullanma, sadece HTML
- Makale sonunda bir "Sık Sorulan Sorular" bölümü ekle (en az 5 soru-cevap)

YANIT FORMATI (sadece JSON):
{
  "title": "SEO uyumlu makale başlığı",
  "meta_description": "155 karakteri geçmeyen meta açıklama",
  "focus_keyword": "ana anahtar kelime",
  "content": "tam HTML makale içeriği",
  "tags": ["etiket1", "etiket2", "etiket3", "etiket4", "etiket5"]
}

JSON dışında hiçbir şey yazma.';
    }

    /**
     * Kategori için kullanıcı promptu
     */
    private static function user_prompt( $category, $label, array $codes ) {
        $code_list = '';
        $i = 0;
        foreach ( $codes as $code => $name ) {
            $code_list .= "- $code: $name\n";
            if ( ++$i >= 30 ) { // İlk 30 kodu örnek olarak ver, token tasarrufu
                $code_list .= '... ve ' . ( count( $codes ) - 30 ) . " kod daha\n";
                break;
            }
        }

        $cat_descriptions = [
            'P' => 'Powertrain (Motor, Şanzıman, Yakıt Sistemi, Emisyon)',
            'B' => 'Body (Kaporta Elektroniği, Hava Yastıkları, Konfor Sistemleri)',
            'C' => 'Chassis (Şasi, ABS, ESP, Fren Sistemi, Süspansiyon)',
            'U' => 'Network (CAN Bus, Modüller Arası İletişim, ECU Ağı)',
        ];

        $desc = $cat_descriptions[ $category ] ?? $label;

        return "OBD Arıza Kodu Kategorisi: $category - $desc
Toplam kod sayısı: " . count( $codes ) . "

Bu kategorideki örnek kodlar:
$code_list

Bu kategori hakkında kapsamlı bir Türkçe makale yaz. Makale:
1. Bu kategorinin ne anlama geldiğini açıkla
2. En sık karşılaşılan kodları ve nedenlerini anlat
3. Araç sahibinin neler yapabileceğini açıkla
4. Servise ne zaman gidilmesi gerektiğini belirt
5. Teşhis sürecini adım adım anlat
6. Türkiye'deki araç sahiplerine özel tavsiyeler ver
7. Maliyet tahminleri hakkında genel bilgi ver

Hedef kitle: Araç sahibi, teknik bilgisi sınırlı Türk okuyucular.
Anahtar kelimeler: OBD $category kodu, araç arıza kodu, $category arıza, araç teşhis";
    }

    /**
     * Groq API çağrısı
     */
    private static function call_groq( $system, $user ) {
        $api_key = AK_Settings::get( AK_Settings::API_KEY );
        $model   = trim( AK_Settings::get( AK_Settings::MODEL, 'llama-3.3-70b-versatile' ) ) ?: 'llama-3.3-70b-versatile';

        if ( empty( $api_key ) ) return [ 'error' => 'Groq API anahtarı eksik.' ];

        $body = [
            'model'           => $model,
            'messages'        => [
                [ 'role' => 'system', 'content' => $system ],
                [ 'role' => 'user',   'content' => $user ],
            ],
            'response_format' => [ 'type' => 'json_object' ],
            'temperature'     => 0.6,
            'max_tokens'      => 8000,
        ];

        $response = wp_remote_post( self::API_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'body'    => wp_json_encode( $body ),
            'timeout' => 120,
        ] );

        if ( is_wp_error( $response ) ) return [ 'error' => $response->get_error_message() ];

        $http_code = wp_remote_retrieve_response_code( $response );
        $raw       = wp_remote_retrieve_body( $response );

        if ( $http_code < 200 || $http_code >= 300 ) {
            $err = json_decode( $raw, true );
            return [ 'error' => $err['error']['message'] ?? "API hatası ($http_code)" ];
        }

        $decoded = json_decode( $raw, true );
        $text    = $decoded['choices'][0]['message']['content'] ?? null;
        if ( ! $text ) return [ 'error' => 'Groq boş yanıt döndürdü.' ];

        $json = json_decode( $text, true );
        if ( json_last_error() !== JSON_ERROR_NONE ) return [ 'error' => 'JSON parse hatası: ' . json_last_error_msg() ];

        return $json;
    }

    /**
     * Kategori makalesi oluştur ve WordPress'e kaydet
     */
    public static function generate( $category ) {
        $all_codes = ak_get_obd_codes();

        if ( ! isset( $all_codes[ $category ] ) )
            return [ 'error' => 'Geçersiz kategori.' ];

        $label = $all_codes[ $category ]['label'];
        $codes = $all_codes[ $category ]['codes'];

        // Zaten var mı kontrol et
        $existing = get_posts( [
            'post_type'   => 'post',
            'post_status' => 'any',
            'meta_key'    => '_ak_obd_category',
            'meta_value'  => $category,
            'numberposts' => 1,
        ] );

        $result = self::call_groq( self::system_prompt(), self::user_prompt( $category, $label, $codes ) );

        if ( isset( $result['error'] ) ) return $result;

        $post_data = [
            'post_title'   => wp_strip_all_tags( $result['title'] ?? "OBD $category Arıza Kodları Rehberi" ),
            'post_content' => $result['content'] ?? '',
            'post_status'  => 'draft',
            'post_type'    => 'post',
            'post_author'  => get_current_user_id(),
            'tags_input'   => $result['tags'] ?? [],
        ];

        // Güncelle veya yeni oluştur
        if ( ! empty( $existing ) ) {
            $post_data['ID'] = $existing[0]->ID;
            $post_id = wp_update_post( $post_data );
            $action  = 'updated';
        } else {
            $post_id = wp_insert_post( $post_data );
            $action  = 'created';
        }

        if ( is_wp_error( $post_id ) ) return [ 'error' => $post_id->get_error_message() ];

        // Meta kaydet
        update_post_meta( $post_id, '_ak_obd_category', $category );
        update_post_meta( $post_id, '_ak_meta_description', $result['meta_description'] ?? '' );
        update_post_meta( $post_id, '_ak_focus_keyword', $result['focus_keyword'] ?? '' );

        return [
            'success'  => true,
            'post_id'  => $post_id,
            'action'   => $action,
            'title'    => $post_data['post_title'],
            'edit_url' => get_edit_post_link( $post_id, 'raw' ),
            'view_url' => get_permalink( $post_id ),
        ];
    }
}
