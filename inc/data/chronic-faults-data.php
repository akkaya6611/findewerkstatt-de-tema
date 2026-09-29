<?php
/**
 * Kronik Arıza & "Bu Araba Alınır mı?" Veri Seti
 * 
 * @package OtoTamir360
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Türkiye'de En Çok Satan Popüler Araçlar İçin Kronik Arıza ve Değerlendirme Verilerini Döndürür
 * 
 * @return array
 */
function ototamir_get_chronic_faults_data() {
    return array(
        'fiat-egea-13-14-16' => array(
            'id'             => 'fiat-egea-13-14-16',
            'brand'          => 'Fiat',
            'brand_slug'     => 'fiat',
            'model'          => 'Egea (Sedan / HB / Cross)',
            'years'          => '2015 - 2026',
            'engines'        => '1.4 Fire Benzin / 1.3 Multijet / 1.6 Multijet Dizel / 1.5 Hybrid',
            'verdict'        => 'sartli', // alinir, sartli, riskli
            'verdict_text'   => 'Fiyat/Performans Kralı - Doğru Motorla Alınır',
            'overall_score'  => 8.4,
            'part_score'     => 'Çok Ucuz & Her Yerde Var',
            'maint_cost'     => 'Çok Düşük',
            'resale_speed'   => 'Peynir Ekmek Gibi (Çok Hızlı)',
            'chronic_issues' => array(
                array(
                    'title'       => '1.4 Fire Yağ Eksiltme Sorunu',
                    'symptoms'    => 'Her 1.000 - 2.000 km’de 0.5 - 1 litreye kadar motor yağı eksilmesi, yağ lambası uyarısı.',
                    'severity'    => 'Orta',
                    'cost_range'  => '₺500 - ₺2.500 (Düzenli Yağ Tamamlama / Subap Keçesi)',
                    'solution'    => 'Fabrika toleransı kabul edilir. Kaliteli tam sentetik 5W-40 yağ kullanımı ve 1.000 km’de bir yağ çubuğu kontrolü şarttır.'
                ),
                array(
                    'title'       => '1.3 Multijet Zincir Sesi ve Debriyaj Bilyası',
                    'symptoms'    => 'İlk çalıştırmada metalik şakırtı sesi, debriyaja basınca değişen uğultu.',
                    'severity'    => 'Yüksek',
                    'cost_range'  => '₺8.000 - ₺16.000',
                    'solution'    => 'Triger zincir kiti ve debriyaj baskı balata seti 120.000 km civarında mutlaka yenilenmelidir.'
                ),
                array(
                    'title'       => 'Direksiyon Açı Sensörü & City Modu Arızası',
                    'symptoms'    => 'Direksiyonda sertleşme, ekranda direksiyon simgesi kırmızı uyarı lambası.',
                    'severity'    => 'Orta',
                    'cost_range'  => '₺4.000 - ₺9.000',
                    'solution'    => 'Direksiyon kolon motoru veya tork sensörü revizyonu yapılır.'
                )
            ),
            'buyer_tips'     => array(
                '1.4 Fire motorda satıcıya yağ tüketim sıklığını ve bagajdaki yedek yağ kutusunu sorun.',
                '1.3 & 1.6 Multijet’te sabah ilk marşta zincir şakırtısını soğuk motorken dinleyin.',
                'Eski taksi veya filo çıkması olup olmadığını Tramer ve kilometre geçmişinden detaylı sorgulatın.'
            ),
            'summary'        => 'Türkiye’nin en çok satan aracı. Parçası bakkalda bile bulunur, ikinci elde anında satılır. 1.3 ve 1.6 Multijet dizelleri çok dayanıklıdır, 1.4 Fire alacakların ise yağ kontrolünü ihmal etmemesi gerekir.'
        ),

        'volkswagen-golf-7-16tdi-14tsi' => array(
            'id'             => 'volkswagen-golf-7-16tdi-14tsi',
            'brand'          => 'Volkswagen',
            'brand_slug'     => 'volkswagen',
            'model'          => 'Golf 7 / 7.5',
            'years'          => '2012 - 2020',
            'engines'        => '1.2 TSI / 1.4 TSI / 1.6 TDI / 1.5 TSI Evo',
            'verdict'        => 'sartli',
            'verdict_text'   => 'Konfor ve Prestij Yüksek - DSG Ekspertizi Şart',
            'overall_score'  => 8.6,
            'part_score'     => 'Bol ve Kaliteli (Orijinal & Muadil)',
            'maint_cost'     => 'Orta - Yüksek',
            'resale_speed'   => 'Çok Hızlı',
            'chronic_issues' => array(
                array(
                    'title'       => 'DSG (DQ200 Kuru Kavrama) Titreme ve Mekatronik Arızası',
                    'symptoms'    => 'Özellikle 1’den 2’ye geçerken silkeleme, yokuşta kalkışta titreme, göstergede İngiliz anahtarı veya vites yanıp sönmesi.',
                    'severity'    => 'Kritik',
                    'cost_range'  => '₺18.000 - ₺45.000',
                    'solution'    => 'Kavrama seti değişimi veya mekatronik gövde basınç tüpü güçlendirilmiş kit ile revize edilir.'
                ),
                array(
                    'title'       => '1.6 TDI Devirdaim (Su Pompası) Sızıntısı',
                    'symptoms'    => 'Antifriz genleşme kabında sıvı eksilmesi, motor ısınması, hafif tatlı koku.',
                    'severity'    => 'Yüksek',
                    'cost_range'  => '₺5.500 - ₺12.000',
                    'solution'    => 'Triger seti değişirken elektrikli selenoidli su pompası orijinal parça ile yenilenmelidir.'
                ),
                array(
                    'title'       => 'Sunroof Su Tahliye Tıkanıklığı',
                    'symptoms'    => 'A sütunundan veya tavan döşemesinden içeriye su sızması, ön paspas altında ıslaklık.',
                    'severity'    => 'Düşük',
                    'cost_range'  => '₺1.000 - ₺3.000',
                    'solution'    => 'Ön direklerdeki tahliye hortumları temizlenmeli veya modifiye edilmelidir.'
                )
            ),
            'buyer_tips'     => array(
                'Otomatik vites alırken aracı dik yokuşta dur-kalk yaparak test edin, 1-2 vites geçişlerinde titreme olup olmadığını kontrol edin.',
                'Yedek su bidonunu kontrol edin; pas, yağ kalıntısı veya eksilme var mı bakın.',
                'DPF (Dizel Partikül Filtresi) doluluk oranını OBD cihazı ile ölçtürün.'
            ),
            'summary'        => 'Sınıfının referans modeli. Yol tutuşu, malzeme kalitesi ve yalıtımı harikadır. Manuel vitesleri sorunsuzdur; DSG modellerde kavrama ve mekatronik sağlığı kontrol edilirse keyifle binilir.'
        ),

        'volkswagen-passat-b8' => array(
            'id'             => 'volkswagen-passat-b8',
            'brand'          => 'Volkswagen',
            'brand_slug'     => 'volkswagen',
            'model'          => 'Passat B8',
            'years'          => '2015 - 2022',
            'engines'        => '1.4 TSI / 1.5 TSI / 1.6 TDI / 2.0 TDI',
            'verdict'        => 'alinir',
            'verdict_text'   => 'D Segmentinin Lideri - Temizi Her Zaman Değerlidir',
            'overall_score'  => 8.9,
            'part_score'     => 'Çok Kolay Bulunur',
            'maint_cost'     => 'Orta',
            'resale_speed'   => 'Kapış Kapış',
            'chronic_issues' => array(
                array(
                    'title'       => 'Kuru Kavrama DSG Mekatronik & Basınç Tüpü',
                    'symptoms'    => 'Vites geçişlerinde vuruntu, geriye takarken gecikme, ekranda şanzıman hatası.',
                    'severity'    => 'Yüksek',
                    'cost_range'  => '₺20.000 - ₺45.000',
                    'solution'    => 'Güçlendirilmiş mekatronik tamir takımı ve orijinal kavrama montajı.'
                ),
                array(
                    'title'       => 'Ön Salıncak Arka Burçlarından Gelen Gıcırtı Sesi',
                    'symptoms'    => 'Kasislerden geçerken özellikle soğuk havalarda gelen yatak gıcırtısı sesi.',
                    'severity'    => 'Düşük',
                    'cost_range'  => '₺1.500 - ₺3.500',
                    'solution'    => 'Salıncak burçlarına özel lityumlu gres uygulanır veya dolu tip burçla değiştirilir.'
                )
            ),
            'buyer_tips'     => array(
                'Yetkili veya güvenilir özel servis bakım kayıtlarının eksiksiz olduğunu teyit edin.',
                '1.6 TDI motorlarda enjektör geri dönüş değerlerini bilgisayarda ölçtürün.',
                'Ağır kazalı, hava yastığı açmış şirket araçlarına karşı dikkatli olun.'
            ),
            'summary'        => 'Türkiye piyasasında altından farksızdır. Değerini korur, geniş iç hacim ve üst düzey sürüş konforu sunar. Bakımlısı sahibini asla üzmez.'
        ),

        'renault-megane-4' => array(
            'id'             => 'renault-megane-4',
            'brand'          => 'Renault',
            'brand_slug'     => 'renault',
            'model'          => 'Megane 4 (Sedan / HB)',
            'years'          => '2016 - 2026',
            'engines'        => '1.5 dCi / 1.6 16V / 1.2 TCe / 1.3 TCe',
            'verdict'        => 'alinir',
            'verdict_text'   => 'Ekonomik, Şık ve Parçası Bol',
            'overall_score'  => 8.5,
            'part_score'     => 'Çok Kolay & Hesaplı',
            'maint_cost'     => 'Düşük',
            'resale_speed'   => 'Çok Hızlı',
            'chronic_issues' => array(
                array(
                    'title'       => 'EDC Çift Kavrama Beyin Isınması & Vuruntu',
                    'symptoms'    => 'Yoğun trafikte "Vites Kutusu Hararet Yaptı" uyarısı, 1-2 vites geçişlerinde kararsızlık.',
                    'severity'    => 'Orta - Yüksek',
                    'cost_range'  => '₺15.000 - ₺35.000',
                    'solution'    => 'Kavrama adaptasyonu ve şanzıman beyni (TCM) yazılım güncellemesi veya revizyonu.'
                ),
                array(
                    'title'       => 'Arka Torsiyon ve Amortisör Takoz Sesi',
                    'symptoms'    => 'Bozuk yollarda bagaj altından gelen lokurtu veya gıcırtı sesleri.',
                    'severity'    => 'Düşük',
                    'cost_range'  => '₺2.000 - ₺4.500',
                    'solution'    => 'Amortisör üst takozları ve torsiyon burçları yenilenir.'
                ),
                array(
                    'title'       => 'R-Link Multimedya Ekranında Donma',
                    'symptoms'    => 'Büyük dikey ekranda Bluetooth kopması, geri görüş kamerasında gecikme.',
                    'severity'    => 'Düşük',
                    'cost_range'  => '₺1.000 - ₺3.000 (Yazılım)',
                    'solution'    => 'Son sürüm multimedya yazılımı yüklenir.'
                )
            ),
            'buyer_tips'     => array(
                '1.5 dCi motor dayanıklılıkta bir efsanedir; ancak enjektör sesine ve turbosuna baktırın.',
                '1.3 TCe (Mercedes ortak üretimi motor) son derece sorunsuzdur, tercih sebebidir.',
                '1.2 TCe motorlarda yağ yakma şikayetleri olduğundan 1.3 TCe tercih edilmelidir.'
            ),
            'summary'        => 'Türkiye yollarına en uygun C segment araçlardan biri. 1.5 dCi yakıt cimrisidir, 1.3 TCe ise performanslı ve dayanıklıdır. İkinci el sirkülasyonu son derece canlıdır.'
        ),

        'renault-clio-4-5' => array(
            'id'             => 'renault-clio-4-5',
            'brand'          => 'Renault',
            'brand_slug'     => 'renault',
            'model'          => 'Clio 4 & Clio 5',
            'years'          => '2012 - 2026',
            'engines'        => '1.2 16V / 0.9 TCe / 1.0 TCe / 1.5 dCi',
            'verdict'        => 'alinir',
            'verdict_text'   => 'Şehir İçi Kullanımın Vazgeçilmezi',
            'overall_score'  => 8.7,
            'part_score'     => 'Çok Ucuz',
            'maint_cost'     => 'Çok Düşük',
            'resale_speed'   => 'Peynir Ekmek',
            'chronic_issues' => array(
                array(
                    'title'       => 'Clio 4 Yan Aynalardan ve Cam Fitillerinden Rüzgar Sesi',
                    'symptoms'    => '90-100 km/s hızın üzerine çıkıldığında kapı içi ve kelebek camından gelen ıslık sesi.',
                    'severity'    => 'Düşük',
                    'cost_range'  => '₺500 - ₺1.500',
                    'solution'    => 'Ayna üçgen plastiği izolasyonu ve kapı fitil takviyesi yapılır.'
                ),
                array(
                    'title'       => '1.5 dCi Mazot Filtresi ve EGR Kurum Bağlama',
                    'symptoms'    => 'Düşük devirde tekleme, egzoz emisyon uyarısı.',
                    'severity'    => 'Orta',
                    'cost_range'  => '₺2.500 - ₺6.000',
                    'solution'    => 'EGR valfi temizliği ve kaliteli mazot filtresi kullanımı.'
                )
            ),
            'buyer_tips'     => array(
                '0.9 TCe ve 1.0 TCe motorlar LPG’ye uyumludur ancak subap ayarına dikkat edilmelidir.',
                'Ön takımda z-rot ve rot başı boşluklarını lifte kaldırarak kontrol ettirin.',
                'Clio 4’te multimedya ekranının çalışıp çalışmadığını deneyin.'
            ),
            'summary'        => 'Ekonomik parça, düşük yakıt tüketimi ve şık tasarım. Şehir içi ve kadın sürücüler için en ideal, satarken de 1 gün içinde elden çıkarılabilen mükemmel bir B segment araç.'
        ),

        'ford-focus-3' => array(
            'id'             => 'ford-focus-3',
            'brand'          => 'Ford',
            'brand_slug'     => 'ford',
            'model'          => 'Focus 3 & 3.5',
            'years'          => '2011 - 2018',
            'engines'        => '1.6 Ti-VCT / 1.6 TDCi / 1.5 TDCi / 1.0 EcoBoost',
            'verdict'        => 'sartli',
            'verdict_text'   => 'Yol Tutuş Efsanesi - Otomatikte Powershift Dikkat',
            'overall_score'  => 8.3,
            'part_score'     => 'Kolay Bulunur',
            'maint_cost'     => 'Orta',
            'resale_speed'   => 'Hızlı',
            'chronic_issues' => array(
                array(
                    'title'       => 'Powershift Kuru Çift Kavrama Arızası (Benzinli Modeller)',
                    'symptoms'    => 'Kalkışta titreme, "Şanzıman Aşırı Isındı" uyarısı, vitese geçmeme, TCM beyin arızası.',
                    'severity'    => 'Kritik',
                    'cost_range'  => '₺25.000 - ₺55.000',
                    'solution'    => 'TCM modülü değişimi ve çift çatal kavrama seti yenilenmesi gerekir.'
                ),
                array(
                    'title'       => '1.6 Ti-VCT Termostat Gövdesi ve Su Hortumu Kaçağı',
                    'symptoms'    => 'Antifriz kokusu, motor bloğunun yanında pembe antifriz izi, hararet yükselmesi.',
                    'severity'    => 'Yüksek',
                    'cost_range'  => '₺3.000 - ₺7.000',
                    'solution'    => 'Plastik termostat gövdesi alüminyum/orijinal parça ile yenilenir.'
                ),
                array(
                    'title'       => '1.6 TDCi Turbo Yağ Besleme Rekoru Filtresi Tıkanması',
                    'symptoms'    => 'Turbodan ıslık sesi gelmesi, turbo mil boşluğu, güç kaybı.',
                    'severity'    => 'Yüksek',
                    'cost_range'  => '₺10.000 - ₺22.000',
                    'solution'    => 'Yağ rekorundaki küçük mikron süzgeç sökülüp temizlenmeli veya iptal edilmelidir.'
                )
            ),
            'buyer_tips'     => array(
                'Benzinli otomatik (Powershift) alırken iki kez düşünün veya tork konvertörlü tam otomatik 1.5 TDCi dizeli tercih edin.',
                '1.5 TDCi otomatik şanzıman Powershift değil klasik tork konvertörlüdür ve son derece sorunsuzdur.',
                'Direksiyon kutusundan tıkırtı sesi gelip gelmediğini bozuk zeminde direksiyonu sağ-sol yaparak test edin.'
            ),
            'summary'        => 'Sınıfının en iyi viraj kabiliyetine sahip arabasıdır. Manuel vitesleri veya 1.5 TDCi tork konvertörlü otomatikleri çok uzun ömürlü ve keyiflidir.'
        ),

        'toyota-corolla' => array(
            'id'             => 'toyota-corolla',
            'brand'          => 'Toyota',
            'brand_slug'     => 'toyota',
            'model'          => 'Corolla',
            'years'          => '2013 - 2026',
            'engines'        => '1.33 Dual VVT-i / 1.6 Valvematic / 1.4 D-4D / 1.8 Hybrid / 1.5 Dynamic Force',
            'verdict'        => 'alinir',
            'verdict_text'   => 'Sorunsuzluk ve Dayanıklılık Abidesi',
            'overall_score'  => 9.2,
            'part_score'     => 'Çok Kolay',
            'maint_cost'     => 'Çok Düşük',
            'resale_speed'   => 'Çok Hızlı',
            'chronic_issues' => array(
                array(
                    'title'       => '1.33 Motorda Aşırı Yağ Yakma (2013-2015 Arası)',
                    'symptoms'    => 'Piston segman tasarımından dolayı yüksek kilometrede motor yağı eksilmesi.',
                    'severity'    => 'Yüksek',
                    'cost_range'  => '₺15.000 - ₺35.000 (Segman Revizyonu)',
                    'solution'    => '1.33 yerine 1.6 Valvematic veya 1.4 D-4D motor tercih edilmelidir.'
                ),
                array(
                    'title'       => 'Multimode (Yarı Otomatik) Debriyaj Aktüatörü (2007-2012 Modeller)',
                    'symptoms'    => 'Vitesin "N" konumuna düşmesi, yokuşta kaydırma, vitese geçmeme.',
                    'severity'    => 'Yüksek',
                    'cost_range'  => '₺10.000 - ₺20.000',
                    'solution'    => '2013 sonrası CVT (Multidrive S) şanzımanlara geçilmiş ve sorun tamamen bitmiştir.'
                ),
                array(
                    'title'       => 'Fren Kaliper Pimi Tıkırtısı',
                    'symptoms'    => 'Parke taşlı yollarda tekerlekten gelen teneke tıkırtısı sesi.',
                    'severity'    => 'Düşük',
                    'cost_range'  => '₺500 - ₺1.500',
                    'solution'    => 'Kaliper pimlerine özel susturucu lastik/pim takılır.'
                )
            ),
            'buyer_tips'     => array(
                '1.6 Valvematic atmosferik motor LPG ile 300.000+ km sorunsuz yürür, tam bir aile arabasıdır.',
                '1.8 Hybrid modellerde batarya sağlık testini yetkili serviste yaptırın.',
                'CVT şanzımanın yağının her 60.000 km’de bir değiştiğini kontrol edin.'
            ),
            'summary'        => 'Sanayi yüzü görmeden 500.000 km yapmak isteyenlerin tercihi. 1.6 Valvematic ve 1.8 Hybrid motorları mekanik olarak dünyanın en sağlam otomobilleri arasındadır.'
        ),

        'honda-civic-fc5' => array(
            'id'             => 'honda-civic-fc5',
            'brand'          => 'Honda',
            'brand_slug'     => 'honda',
            'model'          => 'Civic (FC5 Kasa)',
            'years'          => '2016 - 2021',
            'engines'        => '1.6 i-VTEC Eco (Fabrikasyon LPG) / 1.5 VTEC Turbo / 1.6 i-DTEC Dizel',
            'verdict'        => 'alinir',
            'verdict_text'   => 'Tasarım Harikası - Gençlerin ve Ailelerin Favorisi',
            'overall_score'  => 8.8,
            'part_score'     => 'Çok Kolay & Bol',
            'maint_cost'     => 'Düşük',
            'resale_speed'   => 'Kapış Kapış',
            'chronic_issues' => array(
                array(
                    'title'       => 'C Sütunu Kaporta Göçüğü / Dalgalanması (2016-2017 İlk Seriler)',
                    'symptoms'    => 'Arka camın yanındaki C sütunu sacında kendiliğinden oluşan hafif göçük veya dalgalanma.',
                    'severity'    => 'Düşük (Kozmetik)',
                    'cost_range'  => '₺1.000 - ₺3.000 (PDR Boyasız Düzeltme)',
                    'solution'    => 'Yetkili servisler iç kısımdan köpük desteği uygulamıştır; 2018 sonrası düzeltilmiştir.'
                ),
                array(
                    'title'       => 'Elektrikli Direksiyon Kutusu Boşluğu ve Tıkırtı',
                    'symptoms'    => 'Düşük hızda direksiyonu çevirirken veya kasiste gelen tıkırtı sesi.',
                    'severity'    => 'Orta',
                    'cost_range'  => '₺4.000 - ₺9.000',
                    'solution'    => 'Direksiyon kutusu boşluk ayar vidası sıkılır veya burç revizyonu yapılır.'
                ),
                array(
                    'title'       => 'Klima Kompresörü ve Kondenser Gaz Kaçağı',
                    'symptoms'    => 'Yaz aylarında klimanın sıcak üflemesi, klima gazının bitmesi.',
                    'severity'    => 'Orta',
                    'cost_range'  => '₺4.500 - ₺10.000',
                    'solution'    => 'Honda bu parçayı uzatılmış garanti kapsamında değiştirmiştir; delinen kondenser yenilenir.'
                )
            ),
            'buyer_tips'     => array(
                'C sütununda dalgalanma veya boyasız göçük düzeltme işlemi olup olmadığına ışık altında bakın.',
                'Klimanın soğuk üfleyip üflemediğini mutlaka test edin.',
                'CVT otomatik şanzımanda vites geçişi sarsıntısız olmalıdır, yağ değişim geçmişini sorun.'
            ),
            'summary'        => 'Fabrikasyon LPG uyumu sayesinde kuruş yakıt tüketimi, sportif tasarım ve bağımsız arka süspansiyon konforu. Türkiye pazarında altın bilezik gibi değer kazanır.'
        ),

        'peugeot-3008-2008' => array(
            'id'             => 'peugeot-3008-2008',
            'brand'          => 'Peugeot',
            'brand_slug'     => 'peugeot',
            'model'          => '3008 & 2008',
            'years'          => '2016 - 2026',
            'engines'        => '1.2 PureTech Benzin / 1.5 BlueHDi / 1.6 BlueHDi',
            'verdict'        => 'sartli',
            'verdict_text'   => 'SUV Yıldızı - 1.2 PureTech Trigerine Dikkat!',
            'overall_score'  => 8.4,
            'part_score'     => 'Kolay Bulunur',
            'maint_cost'     => 'Orta',
            'resale_speed'   => 'Çok Hızlı',
            'chronic_issues' => array(
                array(
                    'title'       => '1.2 PureTech Islak Triger Kayışı Ufalanması (Çok Önemli)',
                    'symptoms'    => 'Motor yağının içinde çalışan triger kayışının pullanarak erimesi, yağ süzgecini tıkaması, yağ basıncı uyarısı.',
                    'severity'    => 'Kritik',
                    'cost_range'  => '₺12.000 - ₺28.000 (Kayış + Karter ve Yağ Pompası Temizliği)',
                    'solution'    => 'Her 50.000 - 60.000 km veya 4 yılda bir özel kalınlaştırılmış kayışla yenilenmeli; yalnızca PSA onaylı yağ kullanılmalıdır.'
                ),
                array(
                    'title'       => '1.5 BlueHDi 7mm Eksantrik Zincir Kopması (2018 - 2022 Arası)',
                    'symptoms'    => 'Motor üst kapağından gelen metalik şıkırtı sesi; zincir koparsa motor subap eğebilir.',
                    'severity'    => 'Kritik',
                    'cost_range'  => '₺15.000 - ₺40.000',
                    'solution'    => 'Yetkili servisler 8mm güçlendirilmiş zincir kiti ile revize etmektedir; kontrol ettirilmelidir.'
                ),
                array(
                    'title'       => 'AdBlue Tankı ve Pompa Arızası (P20EE / UREA)',
                    'symptoms'    => 'Ekranda "UREA" ikaz lambası, "1100 km sonra motor çalışmayacak" uyarısı.',
                    'severity'    => 'Yüksek',
                    'cost_range'  => '₺8.000 - ₺22.000 (Depo Değişimi veya İptal)',
                    'solution'    => 'Depo içindeki kristalleşme önleyici katkı kullanılmalı veya arızalanan depo revize edilmelidir.'
                )
            ),
            'buyer_tips'     => array(
                '1.2 PureTech alacaksanız yağ kapağından fenerle triger kayışının yüzeyinde çatlak/ufalanma var mı baktırın.',
                '1.5 BlueHDi dizelde motor üstündeki eksantrik zincir sesini ustaya dinletin.',
                'EAT6 ve EAT8 şanzımanlar Japon Aisin tork konvertörlüdür; dünyanın en dayanıklı şanzımanlarındandır, şanzıman tarafı kusursuzdur.'
            ),
            'summary'        => 'Muhteşem i-Cockpit kabin tasarımı, yüksek SUV sürüş konforu ve kusursuz EAT8 otomatik şanzıman. Dizelde zincir, benzinlide ıslak kayış kontrolleri düzenli yapılırsa harika bir araçtır.'
        ),

        'opel-astra-j-k' => array(
            'id'             => 'opel-astra-j-k',
            'brand'          => 'Opel',
            'brand_slug'     => 'opel',
            'model'          => 'Astra J & Astra K',
            'years'          => '2010 - 2021',
            'engines'        => '1.6 16V / 1.4 Turbo Benzin / 1.3 CDTI / 1.6 CDTI Dizel',
            'verdict'        => 'sartli',
            'verdict_text'   => 'Ağırbaşlı ve Tok Sürüş - Motoruna Göre Alınır',
            'overall_score'  => 8.1,
            'part_score'     => 'Çok Bol ve Uygun',
            'maint_cost'     => 'Orta',
            'resale_speed'   => 'Hızlı',
            'chronic_issues' => array(
                array(
                    'title'       => '1.6 CDTI (Whisper Diesel) Triger Zincir Şakırtısı',
                    'symptoms'    => 'Motorun şanzıman tarafında arkada bulunan zincirden sabah ilk çalıştırmada 3-5 saniye gelen zincir sesi.',
                    'severity'    => 'Kritik',
                    'cost_range'  => '₺18.000 - ₺35.000 (Motor/Şanzıman İndirilir)',
                    'solution'    => 'Zincir arka tarafta olduğu için işçiliği yüksektir; ses başladığında gecikmeden kiti değiştirilmelidir.'
                ),
                array(
                    'title'       => '1.4 Turbo Termostat, Yağ Soğutucu ve Çelik Sibop İhtiyacı',
                    'symptoms'    => 'Antifrize motor yağı karışması, LPG takılan araçlarda subap erimesi ve kompresyon kaybı.',
                    'severity'    => 'Yüksek',
                    'cost_range'  => '₺8.000 - ₺18.000',
                    'solution'    => 'Yağ soğutucu contaları yenilenir; LPG’li ise çelik subap takımı yapılmış olması aranır.'
                )
            ),
            'buyer_tips'     => array(
                '1.6 CDTI dizelde sabah motor soğukken marşa basarak zincir sesini test edin.',
                '1.4 Turbo benzinli alırken su genleşme kabında yağlanma var mı kontrol edin.',
                'Astra J kasada yol tutuş çok iyidir ancak gövde ağırdır; 1.6 atmosferik motorda hantal hissettirebilir.'
            ),
            'summary'        => 'Alman tokluğu ve sağlam kaporta hissi. 1.6 CDTI zincir masrafı göze alınarak veya 1.4 Turbo çelik subaplı olarak tercih edildiğinde fiyatının hakkını fazlasıyla verir.'
        ),

        'hyundai-i20' => array(
            'id'             => 'hyundai-i20',
            'brand'          => 'Hyundai',
            'brand_slug'     => 'hyundai',
            'model'          => 'i20',
            'years'          => '2014 - 2026',
            'engines'        => '1.2 MPI / 1.4 MPI / 1.0 T-GDI / 1.4 CRDi Dizel',
            'verdict'        => 'alinir',
            'verdict_text'   => 'Masrafsız Şehirli - Sanayiye Uğramaz',
            'overall_score'  => 8.9,
            'part_score'     => 'Çok Kolay & Çok Ucuz',
            'maint_cost'     => 'Çok Düşük',
            'resale_speed'   => 'Çok Hızlı',
            'chronic_issues' => array(
                array(
                    'title'       => 'Direksiyon Kolonu Tıkırtısı (Plastik Yıldız Kaplin)',
                    'symptoms'    => 'Bozuk parke yollarda direksiyondan gelen tık-tık sesi.',
                    'severity'    => 'Düşük',
                    'cost_range'  => '₺1.000 - ₺2.500',
                    'solution'    => 'Direksiyon kutusu içindeki 100 liralık kauçuk yıldız kaplin parçası yenilenir.'
                ),
                array(
                    'title'       => 'Debriyaj Üst Merkezi ve Çatal Gıcırtısı',
                    'symptoms'    => 'Debriyaj pedalına basıp bırakırken içeriden gelen yay gıcırtısı sesi.',
                    'severity'    => 'Düşük',
                    'cost_range'  => '₺500 - ₺1.500',
                    'solution'    => 'Pedal mafsalı yağlanır veya üst merkez revize edilir.'
                )
            ),
            'buyer_tips'     => array(
                '1.4 MPI tam otomatik 4 ileri veya 6 ileri tork konvertörlü vitesler ömürlüktür, bozulmaz.',
                '1.2 MPI motor çok az yakar ancak yokuşlarda klima açıkken bayılabilir.',
                'LPG uyumu tamdır, sübap sorunu yaşatmaz.'
            ),
            'summary'        => 'Türkiye’de üretilen, parçası bakkalda bile bulunan, sanayi ustalarının en sevdiği ve en az sorun çıkaran B segment otomobillerden biridir.'
        ),

        'dacia-duster' => array(
            'id'             => 'dacia-duster',
            'brand'          => 'Dacia',
            'brand_slug'     => 'dacia',
            'model'          => 'Duster',
            'years'          => '2013 - 2026',
            'engines'        => '1.5 dCi / 1.6 16V / 1.3 TCe / 1.0 ECO-G (Fabrikasyon LPG)',
            'verdict'        => 'alinir',
            'verdict_text'   => 'Türkiye Yollarının Dağ Keçisi',
            'overall_score'  => 8.6,
            'part_score'     => 'Çok Kolay & Çok Ucuz',
            'maint_cost'     => 'Çok Düşük',
            'resale_speed'   => 'Peynir Ekmek',
            'chronic_issues' => array(
                array(
                    'title'       => 'Plastik İç Trim Sesleri ve İzolasyon',
                    'symptoms'    => 'Bozuk arazide kapı içlerinden ve konsoldan gelen tıkırtılar.',
                    'severity'    => 'Düşük',
                    'cost_range'  => '₺500 - ₺1.500',
                    'solution'    => 'Kendinden yapışkanlı kadife kumaş / trim yalıtımı yapılır.'
                ),
                array(
                    'title'       => 'Yakıt Şamandırası Hatalı Seviye Gösterme',
                    'symptoms'    => 'Göstergedeki yakıt seviyesinin depoyu doldurduktan sonra geç güncellenmesi.',
                    'severity'    => 'Düşük',
                    'cost_range'  => '₺1.000 - ₺2.500',
                    'solution'    => 'Gösterge yazılım sıfırlaması veya şamandıra temizliği yapılır.'
                )
            ),
            'buyer_tips'     => array(
                '4x4 modellerde diferansiyel ve şaft kollarında arazi yıpranması olup olmadığını kontrol ettirin.',
                '1.0 ECO-G fabrikasyon LPG’li modelleri yakıt tasarrufunda rakipsizdir.',
                '1.5 dCi dizellerde turbo ve enjektör testini ihmal etmeyin.'
            ),
            'summary'        => 'Köy yolları, bozuk asfalt, balık ve yayla meraklıları için Türkiye’nin en pratik ve ekonomik SUV aracı. Mekaniği Renault ile birebir aynıdır, masrafsızdır.'
        )
    );
}

/**
 * Mevcut Araç Markalarının Benzersiz Listesini Döndürür
 * 
 * @return array
 */
function ototamir_get_chronic_faults_brands() {
    $data = ototamir_get_chronic_faults_data();
    $brands = array();
    foreach ( $data as $car ) {
        $brands[$car['brand_slug']] = $car['brand'];
    }
    asort( $brands );
    return $brands;
}
