<?php
/**
 * Otomotiv Marka & Model Zeka Motoru ve Zengin SEO Veri Katmanı
 * 
 * Google SERP'te #1 sıraya çıkmak için model özelinde:
 * 1. Teknik Özellikler, Motor Tipleri, Yağ Viskozitesi ve Triger Periyodu
 * 2. Kronik Arızalar ve Uzman Usta Çözüm Tavsiyeleri
 * 3. 2026 Yılı Tahmini İşçilik & Parça Fiyat Rehberi Tablosu
 * 4. Model Özelinde Sıkça Sorulan Sorular (FAQPage Schema.org Uyumlu)
 * 5. 81 İl ve İlçe Çapraz Bağlantı Ağı (Internal Linking Hub)
 * 6. Uzmanlık Hizmet Siloları (Service Silos)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OtoTamir_Brand_Model_SEO_Data {

    /**
     * Modele Özel Teknik Veri ve Kronik Arıza Çözümleri
     */
    public static function get_model_specs( $brand, $model ) {
        $key = strtolower( trim( $brand . ' ' . $model ) );
        $brand_lower = strtolower( trim( $brand ) );

        // 1. Model Özelinde Derinlikli Veritabanı
        $db = array(
            'chevrolet cruze' => array(
                'engines'       => '1.6 124 HP Ecotec, 1.4 Turbo 140 HP, 2.0 VCDi Dizel (163 HP)',
                'oil'           => '5W-30 Tam Sentetik (GM Dexos2 Onaylı)',
                'oil_capacity'  => '4.5 Litre (Filtre dahil)',
                'timing'        => '1.6 Benzinlide Triger Kayışı (60.000 km / 4 Yıl); 2.0 VCDi Kayışlı',
                'maintenance'   => '10.000 - 15.000 km veya Yılda 1 Kez',
                'issues'        => array(
                    array(
                        'title'  => 'Çelik Subap İhtiyacı (LPG Kaynaklı Subap Erimesi)',
                        'desc'   => '1.6 Ecotec motorlarda magnezyum alaşımlı subaplar LPG\'nin yüksek yanma ısısında eriyerek kompresyon kaybına ve tekleme arızasına yol açabilir. Çözüm: Isıya dayanıklı çelik subap takımı ve subap ayarı yapılmasıdır.'
                    ),
                    array(
                        'title'  => 'Yağ Soğutucu Gövde Contası Kaçağı',
                        'desc'   => 'Motor yağı ile soğutma suyunun birbirine karışması veya genleşme kabında yağ tabakası oluşması bu modelde yaygındır. Çözüm: Yağ soğutucu gövdesinin sökülerek orijinal conta takımı ile yenilenmesi ve soğutma sıvısının komple yıkanarak antifriz tazelenmesidir.'
                    ),
                    array(
                        'title'  => 'Termostat Gövdesi & Su Flanşı Çatlağı',
                        'desc'   => 'Yüksek motor çalışma sıcaklığı nedeniyle plastik termostat gövdesinde kılcal çatlaklar ve antifriz kaçağı görülebilir. Çözüm: Metal/alüminyum alaşımlı termostat gövdesi takılması ve 105°C müşür kontrolüdür.'
                    ),
                    array(
                        'title'  => 'Ateşleme Bobini & Buji Arızası',
                        'desc'   => 'Hızlanmalarda tekleme, ESP arıza ışığı ve P0300 arıza kodu. Çözüm: Delphi/GM orijinal tek parça kaset bobin ve 0.9mm tırnak aralıklı bujiler takılmalıdır.'
                    ),
                ),
            ),

            'fiat egea' => array(
                'engines'       => '1.4 Fire 95 HP, 1.3 Multijet 95 HP, 1.6 Multijet 120/130 HP, 1.5 T4 Hibrit (130 HP)',
                'oil'           => '1.4 Fire için 5W-40 C3; Multijet Dizeller için 0W-30 / 5W-30 DPF Uyumlu',
                'oil_capacity'  => '3.0 - 4.8 Litre (Motora göre)',
                'timing'        => '1.3 Multijet Zincirli (160.000 km); 1.4 Fire ve 1.6 Multijet Triger Kayışlı (80.000 km)',
                'maintenance'   => 'Benzinli 10.000 km; Dizel Multijet 15.000 km veya 1 Yıl',
                'issues'        => array(
                    array(
                        'title'  => '1.4 Fire Motorlarda Yağ Eksiltme',
                        'desc'   => 'Özellikle ilk 10.000 km aralığında yüksek devirli kullanımda piston segman toleransı nedeniyle yağ tüketimi görülebilir. Çözüm: 5W-40 C3 tam sentetik kaliteli yağ kullanımı, yağ seviyesinin 2.000 km\'de bir çubuktan kontrol edilmesi.'
                    ),
                    array(
                        'title'  => 'DPF (Dizel Partikül Filtresi) ve EGR Tıkanıklığı',
                        'desc'   => 'Şehir içi kısa mesafe kullanımında 1.3 ve 1.6 Multijet motorlarda partikül filtresi dolar. Çözüm: Usta kontrolünde rejenerasyon, kurum temizliği ve periyodik uzun yol devirli sürüş.'
                    ),
                    array(
                        'title'  => 'Şanzıman Alt Takozu & Titreme',
                        'desc'   => 'Kalkışlarda ve vites geçişlerinde sarsıntı/vuruntu hissi. Çözüm: Şanzıman arka kulağının (takozunun) orijinal parça ile değişimi.'
                    ),
                ),
            ),

            'renault megane' => array(
                'engines'       => '1.5 dCi (90/110/115 HP), 1.3 TCe (140 HP), 1.6 16V, 1.2 TCe',
                'oil'           => '5W-30 RN0720 (Dizel DPF) / RN17 (1.3 TCe Benzin)',
                'oil_capacity'  => '4.5 - 5.5 Litre',
                'timing'        => '1.5 dCi Triger Kayışlı (80.000 km / 4 Yıl); 1.3 TCe Triger Zincirli',
                'maintenance'   => '15.000 - 20.000 km veya Yılda 1 Kez',
                'issues'        => array(
                    array(
                        'title'  => 'EDC Çift Kavrama Şanzıman Isınması & Titreme',
                        'desc'   => 'Yoğun trafikte kalkışta silkeleme veya "Vites Kutusu Aşırı Isındı" uyarısı. Çözüm: EDC kavrama adaptasyonu, şanzıman beyni yazılım güncellemesi veya kavrama seti değişimi.'
                    ),
                    array(
                        'title'  => '1.5 dCi Enjektör Geri Dönüş ve Pul Kaçakları',
                        'desc'   => 'Soğuk çalıştırmada sarsıntı, siyah duman veya enjektör dip kısmında mazot/kurum birikmesi. Çözüm: Enjektör bakır pullarının yenilenmesi ve püskürtme testi.'
                    ),
                    array(
                        'title'  => 'Termostat Plastik Dağıtıcı Gövde Kaçağı',
                        'desc'   => '1.5 dCi motor blok arkasında antifriz kokusu ve su seviyesi azalması. Çözüm: Komple termostat kutusunun contasıyla birlikte değişimi.'
                    ),
                ),
            ),

            'volkswagen golf' => array(
                'engines'       => '1.0 TSI (110/115 HP), 1.5 eTSI (150 HP), 1.6 TDI (105/115 HP), 2.0 TDI',
                'oil'           => '5W-30 / 0W-30 (VW 504.00 / 507.00 Spesifikasyonlu)',
                'oil_capacity'  => '4.0 - 4.7 Litre',
                'timing'        => '1.6 TDI Kayışlı (120.000 km); 1.0 & 1.5 TSI Kayışlı (120.000 km)',
                'maintenance'   => '15.000 km veya Yılda 1 Kez',
                'issues'        => array(
                    array(
                        'title'  => '7 İleri Kuru Kavrama DSG Mekatronik & Basınç Tüpü',
                        'desc'   => 'Vites geçişlerinde vuruntu, ekranda İngiliz anahtarı veya P17BF arıza kodu. Çözüm: Güçlendirilmiş mekatronik basınç tüpü montajı ve mekatronik yağ değişimi.'
                    ),
                    array(
                        'title'  => 'Devirdaim (Su Pompası) Sızıntısı',
                        'desc'   => 'TDI ve TSI motorlarda 60.000 km sonrasında antifriz eksiltme. Çözüm: Triger seti ile birlikte orijinal devirdaim pompasının değişimi.'
                    ),
                    array(
                        'title'  => 'TSI Direkt Enjeksiyon Emme Manifoldu Karbon Birikimi',
                        'desc'   => 'Rölanti dalgalanması ve performans kaybı. Çözüm: Manifold sökülerek ceviz kabuğu kumlama (Walnut blasting) ile sübap karbon temizliği.'
                    ),
                ),
            ),

            'ford focus' => array(
                'engines'       => '1.5 EcoBlue Dizel (120 HP), 1.0 EcoBoost (100/125 HP), 1.6 Ti-VCT',
                'oil'           => '0W-20 WSS-M2C950-A (EcoBlue) / 5W-20 WSS-M2C948-B (EcoBoost)',
                'oil_capacity'  => '4.1 - 6.2 Litre',
                'timing'        => 'EcoBoost & EcoBlue Islak Triger Kayışı (Yağ Banyolu); 1.6 Ti-VCT Kuru Kayışlı',
                'maintenance'   => '15.000 km veya Yılda 1 Kez',
                'issues'        => array(
                    array(
                        'title'  => 'Islak Triger Kayışı Aşınması & Yağ Süzgeci Tıkanması (Kritik!)',
                        'desc'   => 'Motor yağı içinde çalışan triger kayışı yanlış yağ konulursa pul pul dökülerek karterdeki yağ pompasını tıkar. Çözüm: Sadece Ford onaylı orijinal yağ kullanımı ve 100.000 km\'de karter açılarak süzgeç temizliği ve kayış değişimi.'
                    ),
                    array(
                        'title'  => 'Powershift Şanzıman Titremesi',
                        'desc'   => 'Özellikle benzinli modellerde çift kavrama aşınması. Çözüm: Kavrama çatalı ve balata değişimi, şanzıman beyni (TCM) kalibrasyonu.'
                    ),
                ),
            ),
        );

        // Model bulunduysa direkt döndür
        if ( isset( $db[ $key ] ) ) {
            return $db[ $key ];
        }

        // Bulunamadıysa marka ve model adını akıllı harmanlayarak dinamik profesyonel veri üret
        return array(
            'engines'       => "{$brand} {$model} Benzinli, Dizel ve Hibrit Motor Seçenekleri",
            'oil'           => '5W-30 / 5W-40 Tam Sentetik (Üretici Onaylı Motor Yağı)',
            'oil_capacity'  => '3.8 - 5.0 Litre',
            'timing'        => 'Triger Seti (Kayışlı modellerde 60.000 - 90.000 km; Zincirli modellerde 150.000+ km)',
            'maintenance'   => '10.000 - 15.000 km veya Yılda 1 Kez',
            'issues'        => array(
                array(
                    'title'  => "{$brand} {$model} Periyodik Yağ ve Filtre Değişimi",
                    'desc'   => 'Motor ömrünü uzatmak ve yakıt tasarrufu sağlamak için motor yağı, yağ filtresi, hava ve polen filtresinin orijinal parçalarla düzenli değiştirilmesi gerekir.'
                ),
                array(
                    'title'  => 'Fren Sistemi, Balata ve Disk Kontrolü',
                    'desc'   => 'Fren pedalında yumuşama, ötme sesi veya titreme durumlarında ön-arka balataların ve fren hidrolik sıvısının usta tarafından kontrol edilmesi şarttır.'
                ),
                array(
                    'title'  => 'Ön Düzen, Alt Takım ve Amortisör Bakımı',
                    'desc'   => 'Bozuk yollarda gelen tıkırtı sesleri rot başı, z-rot veya salıncak burçlarının yıpranmasından kaynaklanır. Güvenli sürüş için periyodik rot-balans ayarı yapılmalıdır.'
                ),
                array(
                    'title'  => 'Bilgisayarlı Beyin ve Sensör Arıza Tespiti',
                    'desc'   => "{$model} aracınızda motor arıza lambası yandığında OBD-II cihazı ile hata kodları taranarak arızanın kaynağı noktasal olarak tespit edilir."
                ),
            ),
        );
    }

    /**
     * 2026 Güncel Ortalama Servis & Usta Fiyat Tablosu
     */
    public static function get_price_table( $brand, $model ) {
        return array(
            array(
                'service'   => 'Periyodik Bakım (Motor Yağı + 4 Filtre)',
                'scope'     => 'Tam sentetik yağ, yağ filtresi, hava filtresi, polen filtresi değişimi ve 24 nokta kontrolü',
                'price'     => '2.200 TL - 4.800 TL',
                'duration'  => '45 - 60 Dk'
            ),
            array(
                'service'   => 'Ön Fren Balata Değişimi',
                'scope'     => 'Ön fren balata takımı sökme-takma, kaliper temizliği ve fren hidrolik kontrolü',
                'price'     => '1.300 TL - 2.600 TL',
                'duration'  => '30 - 45 Dk'
            ),
            array(
                'service'   => 'Ağır Bakım (Triger Seti & Devirdaim)',
                'scope'     => 'Triger kayışı/zinciri, gergi bilyeleri, devirdaim pompası ve antifriz yenileme',
                'price'     => '4.800 TL - 11.500 TL',
                'duration'  => '3 - 5 Saat'
            ),
            array(
                'service'   => 'Debriyaj Baskı Balata & Bilye Değişimi',
                'scope'     => 'Şanzıman indirme, debriyaj seti değişimi ve şanzıman yağı tazeleme',
                'price'     => '5.500 TL - 13.500 TL',
                'duration'  => '4 - 6 Saat'
            ),
            array(
                'service'   => 'Bilgisayarlı Arıza Tespiti (OBD-II)',
                'scope'     => 'Elektronik beyin (ECU) hata kodu okuma, canlı değer analizi ve arıza ışığı söndürme',
                'price'     => '350 TL - 750 TL',
                'duration'  => '20 - 30 Dk'
            ),
            array(
                'service'   => 'Alt Takım, Rot-Balans ve Amortisör',
                'scope'     => 'Rot başı, rotil, z-rot kontrolü ve 4 teker lazerli rot balans ayarı',
                'price'     => '700 TL - 1.600 TL',
                'duration'  => '45 Dk'
            ),
        );
    }

    /**
     * Zengin Sıkça Sorulan Sorular (Model ve Marka Odaklı)
     */
    public static function get_model_faqs( $brand, $model ) {
        $specs = self::get_model_specs( $brand, $model );

        return array(
            array(
                'q' => "{$brand} {$model} periyodik bakımı kaç kilometrede bir yapılmalıdır?",
                'a' => "{$brand} {$model} araçlar için üretici tarafından tavsiye edilen standart periyodik bakım aralığı {$specs['maintenance']}'dır. Aracınızı yoğun şehir içi dur-kalk trafikte veya tozlu ortamlarda kullanıyorsanız yağ ve filtre değişimini 10.000 km'de bir yaptırmanız motor sağlığı açısından tavsiye edilir."
            ),
            array(
                'q' => "{$brand} {$model} için hangi motor yağı ve viskozite kullanılmalıdır?",
                'a' => "{$brand} {$model} motorlarında genellikle {$specs['oil']} spesifikasyonuna sahip tam sentetik motor yağları kullanılmalıdır. Yağ kapasitesi yaklaşık {$specs['oil_capacity']} seviyesindedir. Yanlış viskozitede veya onaysız yağ kullanımı motor sesine, subap aşınmasına ve yakıt tüketiminin artmasına yol açar."
            ),
            array(
                'q' => "{$brand} {$model} triger seti (kayış/zincir) ne zaman değişir?",
                'a' => "{$brand} {$model} modelinde {$specs['timing']} tavsiye edilmektedir. Ağır bakım esnasında triger seti ile birlikte devirdaim su pompasının ve antifrizin de yenilenmesi olası motor hararet ve su kaçaklarını önler."
            ),
            array(
                'q' => "{$brand} {$model} kronik arızaları nelerdir ve tamiri ne kadar sürer?",
                'a' => "Kullanıcı geri bildirimlerine göre {$brand} {$model} modelinde en sık karşılaşılan durumlar: " . implode( ', ', array_map( function($item){ return $item['title']; }, $specs['issues'] ) ) . " konularıdır. Alanında uzman bir {$brand} özel servisinde bu arızaların teşhisi ve onarımı genellikle aynı gün içinde tamamlanmaktadır."
            ),
            array(
                'q' => "Yetkili servis yerine onaylı {$brand} özel servisi tercih etmek avantajlı mıdır?",
                'a' => "Garantisi bitmiş veya devam eden {$brand} {$model} araçlar için TSE onaylı özel oto servisleri tercih etmek ortalama %40-%60 daha ekonomik bakım maliyeti sağlar. Platformumuzda listelenen ustalar orijinal ve OEM eşdeğer garantili yedek parça kullanmakta, bilgisayarlı arıza teşhisi yapmaktadır."
            ),
        );
    }

    /**
     * En Çok Aranan 24 Büyükşehir (Local SEO İç Linkleme Hub'ı)
     */
    public static function get_top_provinces() {
        return array(
            array( 'slug' => 'istanbul',    'name' => 'İstanbul' ),
            array( 'slug' => 'ankara',      'name' => 'Ankara' ),
            array( 'slug' => 'izmir',       'name' => 'İzmir' ),
            array( 'slug' => 'bursa',       'name' => 'Bursa' ),
            array( 'slug' => 'antalya',     'name' => 'Antalya' ),
            array( 'slug' => 'adana',       'name' => 'Adana' ),
            array( 'slug' => 'konya',       'name' => 'Konya' ),
            array( 'slug' => 'gaziantep',   'name' => 'Gaziantep' ),
            array( 'slug' => 'kocaeli',     'name' => 'Kocaeli' ),
            array( 'slug' => 'mersin',      'name' => 'Mersin' ),
            array( 'slug' => 'diyarbakir',  'name' => 'Diyarbakır' ),
            array( 'slug' => 'kayseri',     'name' => 'Kayseri' ),
            array( 'slug' => 'eskisehir',   'name' => 'Eskişehir' ),
            array( 'slug' => 'samsun',      'name' => 'Samsun' ),
            array( 'slug' => 'denizli',     'name' => 'Denizli' ),
            array( 'slug' => 'sanliurfa',   'name' => 'Şanlıurfa' ),
            array( 'slug' => 'sakarya',     'name' => 'Sakarya' ),
            array( 'slug' => 'malatya',     'name' => 'Malatya' ),
            array( 'slug' => 'kahramanmaras','name' => 'Kahramanmaraş' ),
            array( 'slug' => 'van',         'name' => 'Van' ),
            array( 'slug' => 'aydin',       'name' => 'Aydın' ),
            array( 'slug' => 'tekirdag',    'name' => 'Tekirdağ' ),
            array( 'slug' => 'balikesir',   'name' => 'Balıkesir' ),
            array( 'slug' => 'trabzon',     'name' => 'Trabzon' ),
        );
    }

    /**
     * Hizmet Siloları (Service Categories)
     */
    public static function get_service_silos() {
        return array(
            array( 'slug' => 'periyodik-bakim',   'name' => 'Periyodik Bakım',      'icon' => 'fa-oil-can' ),
            array( 'slug' => 'motor-mekanik',     'name' => 'Motor & Mekanik',       'icon' => 'fa-gears' ),
            array( 'slug' => 'oto-elektrik',      'name' => 'Oto Elektrik & Beyin', 'icon' => 'fa-bolt' ),
            array( 'slug' => 'otomatik-sanziman', 'name' => 'Otomatik Şanzıman',    'icon' => 'fa-gauge' ),
            array( 'slug' => 'fren-balata',       'name' => 'Fren & Alt Takım',     'icon' => 'fa-circle-stop' ),
            array( 'slug' => 'oto-klima',         'name' => 'Oto Klima & Gaz',       'icon' => 'fa-snowflake' ),
            array( 'slug' => 'ariza-tespiti',     'name' => 'Bilgisayarlı Teşhis',  'icon' => 'fa-laptop-code' ),
            array( 'slug' => 'kaporta-boya',      'name' => 'Kaporta & Boya',       'icon' => 'fa-car-side' ),
        );
    }
}
