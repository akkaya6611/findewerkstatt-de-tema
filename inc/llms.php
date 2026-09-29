<?php
/**
 * OtoTamirciBul - LLMs.txt Standart Entegrasyonu
 * 
 * Yapay zeka modelleri (ChatGPT, Claude, Gemini, Perplexity vb.) ve otonom AI ajanlari icin
 * site mimarisini ve araclarini yapilandirilmis Markdown formatinda sunan standart llms.txt motoru.
 * Standart: https://llmstxt.org
 * 
 * @package OtoTamir360
 * @version 1.5.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OtoTamir_LLMs_Txt {

    /**
     * Modülü Başlat
     */
    public static function init() {
        // Rewrite kuralları & Query Değişkenleri
        add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ), 6 );
        add_filter( 'query_vars', array( __CLASS__, 'add_query_vars' ) );

        // /llms.txt ve /llms-full.txt isteklerini yakalama
        add_action( 'template_redirect', array( __CLASS__, 'handle_request' ), 1 );

        // Tema güncellendiğinde veya etkinleştirildiğinde statik dosyayı güncelle
        add_action( 'after_switch_theme', array( __CLASS__, 'sync_static_file' ) );
        add_action( 'init', array( __CLASS__, 'maybe_sync_static_file' ), 99 );
    }

    /**
     * Rewrite Kuralı
     */
    public static function add_rewrite_rules() {
        add_rewrite_rule( '^llms\.txt$', 'index.php?ototamir_llms=1', 'top' );
        add_rewrite_rule( '^llms-full\.txt$', 'index.php?ototamir_llms=full', 'top' );
    }

    /**
     * Query Vars
     */
    public static function add_query_vars( $vars ) {
        $vars[] = 'ototamir_llms';
        return $vars;
    }

    /**
     * Tek seferlik rewrite flush ve dosya senkronizasyonu kontrolü
     */
    public static function maybe_sync_static_file() {
        if ( get_option( 'ototamir_llms_txt_v1478' ) !== 'yes' ) {
            flush_rewrite_rules( false );
            self::sync_static_file();
            update_option( 'ototamir_llms_txt_v1478', 'yes' );
        }
    }

    /**
     * llms.txt İsteklerini Yakala ve Markdown Olarak Bas
     */
    public static function handle_request() {
        $llms_var = get_query_var( 'ototamir_llms' );

        if ( empty( $llms_var ) ) {
            $req_uri = $_SERVER['REQUEST_URI'] ?? '';
            $path = trim( (string) parse_url( $req_uri, PHP_URL_PATH ), '/' );
            if ( $path === 'llms.txt' ) {
                $llms_var = '1';
            } elseif ( $path === 'llms-full.txt' ) {
                $llms_var = 'full';
            }
        }

        if ( empty( $llms_var ) ) {
            return;
        }

        // Önbellek bypass engeli
        if ( ! defined( 'LSCACHE_NO_CACHE' ) ) {
            define( 'LSCACHE_NO_CACHE', true );
        }

        // llmstxt.org standart HTTP basliklari
        header( 'Content-Type: text/markdown; charset=UTF-8' );
        header( 'X-Robots-Tag: all', true );
        header( 'Cache-Control: public, max-age=86400, stale-while-revalidate=604800', true );

        echo self::generate_content();
        exit;
    }

    /**
     * Statik llms.txt Dosyasını WordPress Kök Dizinine ve Tema Dizinine Senkronize Et
     */
    public static function sync_static_file() {
        $content = self::generate_content();

        // 1. Tema dizinine kaydet (Git & Yedek için)
        $theme_file = get_template_directory() . '/llms.txt';
        @file_put_contents( $theme_file, $content );

        // 2. Web kök dizinine kaydet (Doğrudan sunucu üzerinden en hızlı servis için)
        if ( defined( 'ABSPATH' ) ) {
            $root_file = ABSPATH . 'llms.txt';
            if ( is_writable( ABSPATH ) || ( file_exists( $root_file ) && is_writable( $root_file ) ) ) {
                @file_put_contents( $root_file, $content );
            }
        }
    }

    /**
     * llmstxt.org Standartlarına Uygun Markdown İçeriğini Üret
     */
    public static function generate_content() {
        $base = rtrim( home_url(), '/' );

        $out  = "# OtoTamirciBul (OTOTAMİR-360)\n\n";
        $out .= "> OtoTamirciBul, Türkiye'nin 81 ilinde hizmet veren oto tamircileri, özel servisleri, nöbetçi tamircileri ve oto çekicileri tek bir platformda toplayan; yapay zeka destekli araç arıza teşhis ve güvenilir sürücü rehberi platformudur.\n\n";

        $out .= "## Genel Bakış ve Platform Mimarisi\n";
        $out .= "OtoTamirciBul ({$base}), sürücülerin araç arızası anında veya periyodik bakım ihtiyaçlarında uzman usta ve servise en hızlı şekilde ulaşmasını sağlar. Platform; 81 il sanayi rehberi, OBD2 hata kodları veritabanı, yapay zeka destekli arıza tespit motoru, 7/24 nöbetçi servisler, canlı akaryakıt fiyatları, elektrikli araç şarj istasyonları haritası ve doğrulanmış yorum metodolojisi ile donatılmıştır.\n\n";

        $out .= "## Yapay Zeka ve Sürücü Destek Araçları\n";
        $out .= "- [Yapay Zeka ile Arıza Tespiti]({$base}/ariza-tespiti/): Araçta meydana gelen ses, duman, çekiş düşüklüğü veya sarsıntı gibi belirtileri analiz ederek muhtemel arıza kaynaklarını, aciliyet seviyesini ve tahmini onarım masrafını hesaplayan yapay zeka aracı.\n";
        $out .= "- [OBD2 Arıza Kodları Rehberi]({$base}/ariza-kodlari/): Araç arıza tespit cihazlarında (DTC) çıkan P, C, B ve U serisi motor/şasi kodlarının Türkçe anlamları, olası nedenleri ve çözüm adımları.\n";
        $out .= "- [7/24 Nöbetçi Oto Tamirciler]({$base}/nobetci-oto-tamirciler/): Mesai saatleri dışında, pazar günleri ve resmi tatillerde hizmet veren nöbetçi oto tamir ve servis listesi.\n";
        $out .= "- [Acil Çekici ve Yol Yardım]({$base}/acil-cekici/): 81 il genelinde en yakın oto kurtarıcı, ahtapot çekici, akü takviye ve yerinde lastik tamiri sağlayan güvenilir çekici ağı.\n";
        $out .= "- [Kaza ve Tutanak Asistanı]({$base}/kaza-asistani/): Maddi hasarlı trafik kazalarında hak kaybını önleyen adım adım tutanak doldurma rehberi ve online kaza asistanı.\n";
        $out .= "- [Kronik Araç Arızaları]({$base}/kronik-arizalar/): Marka ve modellere göre bilinen kronik motor, şanzıman, elektronik ve mekanik arıza kataloğu.\n";
        $out .= "- [Elektrikli Araç Şarj İstasyonları]({$base}/sarj-istasyonlari/): Türkiye genelindeki Trugo, ZES, Eşarj ve diğer ağlara ait 760+ şarj istasyonu, soket tipleri (AC/DC) ve anlık konumları.\n";
        $out .= "- [Güncel Akaryakıt Fiyatları]({$base}/akaryakit/): İl bazında anlık benzin, motorin (dizel) ve LPG otogaz pompa fiyatları.\n\n";

        $out .= "## 81 İl Sanayi ve Usta Rehberi\n";
        $out .= "- [Tüm İller ve Sanayi Siteleri]({$base}/iller/): İstanbul, Ankara, İzmir, Bursa, Antalya, Adana, Konya, Gaziantep, Kocaeli, Kayseri ve diğer tüm illerdeki oto sanayi siteleri ve tamirci profilleri.\n\n";

        $out .= "## Hizmet ve Servis Kategorileri\n";
        $out .= "- [Tüm Hizmetler]({$base}/hizmetler/): Platformda listelenen uzmanlık alanları:\n";
        $out .= "  - Motor Mekanik ve Revizyon Ustaları\n";
        $out .= "  - Oto Elektrik ve Elektronik Servisleri\n";
        $out .= "  - Periyodik Bakım ve Yağ Değişimi\n";
        $out .= "  - Kaporta, Boya ve Göçük Düzeltme\n";
        $out .= "  - Fren, Balata ve Disk Bakımı\n";
        $out .= "  - Oto Lastik, Jant ve Rot Balans\n";
        $out .= "  - Oto Klima Gazı ve İklimlendirme\n";
        $out .= "  - Şanzıman, Debriyaj ve Güç Aktarma\n";
        $out .= "  - Oto Ekspertiz ve Computest Merkezleri\n";
        $out .= "  - LPG Montaj, Bakım ve Ayar Servisleri\n";
        $out .= "  - Oto Beyin (ECU) ve Çip Tuning Yazılımı\n";
        $out .= "  - Oto Cam Tamiri ve Değişimi\n\n";

        $out .= "## Marka ve Model Servis Rehberi\n";
        $out .= "- [Marka Rehberi]({$base}/model-rehberi/): Fiat, Renault, Volkswagen, Ford, Opel, Toyota, Peugeot, Hyundai, Honda, BMW, Mercedes-Benz, Audi ve diğer tüm popüler markalara özel yetkili ve özel servisler.\n\n";

        $out .= "## Güvenilirlik, Şeffaflık ve Yorum Metodolojisi\n";
        $out .= "- [Güvenilir Yorumlar Metodolojisi]({$base}/guvenilir-yorumlar/): Sitedeki usta puanları ve kullanıcı yorumları; sahte incelemeleri önlemek amacıyla IP, oturum ve servis teyit filtrelerinden geçirilir. Moderasyon ve şeffaflık ilkelerimiz bu sayfada açıklanmıştır.\n\n";

        $out .= "## Esnaf ve Usta Kaydı\n";
        $out .= "- [Ücretsiz Usta / Servis Ekle]({$base}/usta-ekle/): Oto sanayi esnafları, bağımsız ustalar ve servis sahipleri profillerini ücretsiz olarak oluşturabilir ve müşteri talepleri alabilir.\n\n";

        $out .= "## Kurumsal ve Yasal Bilgiler\n";
        $out .= "- [Hakkımızda]({$base}/hakkimizda/): OtoTamirciBul misyonu ve kurumsal yapısı.\n";
        $out .= "- [İletişim & Destek]({$base}/iletisim/): Sürücüler ve işletmeler için doğrudan destek ve iletişim kanalları.\n";
        $out .= "- [Sıkça Sorulan Sorular (SSS)]({$base}/sss/): Platform kullanımı, randevu ve profil yönetimi ile ilgili sorular.\n";
        $out .= "- [Gizlilik Politikası]({$base}/gizlilik-politikasi/)\n";
        $out .= "- [Kullanım Koşulları]({$base}/kullanim-kosullari/)\n";
        $out .= "- [KVKK Aydınlatma Metni]({$base}/kvkk/)\n";
        $out .= "- [Çerez Politikası]({$base}/cerez-politikasi/)\n\n";

        $out .= "## İndeksleme ve XML Site Haritaları\n";
        $out .= "- [Ana XML Site Haritası]({$base}/sitemap.xml): Arama motorları ve botlar için dizin haritası.\n";
        $out .= "- [Usta Profilleri Haritası]({$base}/sitemap-mechanics.xml): Tüm kayıtlı tamirci ve servis profilleri.\n";
        $out .= "- [Şehir ve İlçe Haritası]({$base}/sitemap-cities.xml): 81 il ve ilçe rehber sayfaları.\n";
        $out .= "- [Hizmet Kategorileri Haritası]({$base}/sitemap-services.xml): Servis ve uzmanlık alanları.\n";

        return $out;
    }
}

OtoTamir_LLMs_Txt::init();
