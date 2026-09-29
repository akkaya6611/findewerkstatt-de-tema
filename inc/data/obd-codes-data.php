<?php
/**
 * OBD-II Arıza Kodları ve Gösterge Paneli İkaz Lambaları Veritabanı
 * 
 * Türkiye araç parkında en sık görülen 50+ arıza kodu ve 14 temel gösterge ikaz lambası.
 * 
 * @package OtoTamir360
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 14 Temel Gösterge Paneli İkaz Lambası Verisi
 */
function ototamir_get_dashboard_lights_data() {
    return array(
        'check-engine' => array(
            'id'             => 'check-engine',
            'title'          => 'Motor Arıza Lambası (Check Engine / MIL)',
            'color'          => 'amber',
            'icon'           => 'fa-solid fa-engine-warning',
            'severity'       => 'warning',
            'severity_label' => 'Uyarı - En Kısa Sürede Servise Gidin (Yanıp Sönüyorsa Kritik!)',
            'summary'        => 'Motor yönetim sistemi veya egzoz emisyonunda bir anormallik tespit edildiğini gösterir.',
            'symptoms'       => array(
                'Çekişten düşme veya gaz yememe',
                'Yakıt tüketiminde belirgin artış',
                'Rölantide sarsıntı ve tekleme',
                'Egzozdan siyah veya yoğun duman çıkışı'
            ),
            'causes'         => array(
                'Oksijen (Lambda) sensörü arızası',
                'Ateşleme bobini veya buji aşınması',
                'Katalitik konvertör veya DPF tıkanıklığı',
                'Hava akışmetre (MAF/MAP) kirlenmesi',
                'Gevşek yakıt deposu kapağı veya yakıt basınç sorunu'
            ),
            'what_to_do'     => 'Eğer lamba sabit yanıyorsa aracınızı zorlamadan en yakın servise sürün. Eğer lamba yanıp sönüyorsa (flashing) katalitik konvertör hasarını önlemek için aracı kenara çekin ve motoru durdurun.',
            'service_slug'   => 'mekanik-ustasi',
            'service_name'   => 'Motor & Mekanik Ustaları',
            'alt_slug'       => 'oto-beyin-ve-beyin-tamiri-ustasi-ecu-cip-tunning-yazilim',
            'alt_name'       => 'Oto Beyin & Diyagnostik Ustaları'
        ),
        'oil-pressure' => array(
            'id'             => 'oil-pressure',
            'title'          => 'Motor Yağ Basıncı İkaz Lambası (Kırmızı Yağdanlık)',
            'color'          => 'red',
            'icon'           => 'fa-solid fa-oil-can',
            'severity'       => 'critical',
            'severity_label' => '🚨 Acil Durdur - Motor Kilitleme Tehlikesi!',
            'summary'        => 'Motor içinde yeterli yağ basıncı olmadığını bildirir. Motor saniyeler içinde yatak sarabilir ve kullanılamaz hale gelebilir.',
            'symptoms'       => array(
                'Motordan gelen metalik sürtünme ve şıkırtı sesi',
                'Motor sıcaklığının hızla yükselmesi',
                'Yanık yağ kokusu'
            ),
            'causes'         => array(
                'Kritik seviyede eksik motor yağı',
                'Yağ pompası arızası veya pompa süzgeci tıkanması',
                'Yağ müşürü (sensörü) bozulması',
                'Yanlış viskozitede kalitesiz yağ kullanımı'
            ),
            'what_to_do'     => 'ARACI DERHAL GÜVENLİ BİR YERE ÇEKİP MOTORU STOP EDİN! Asla sürmeye devam etmeyin. Yağ çubuğunu kontrol edin, yağ yoksa ekleyin. Yağ tam olmasına rağmen lamba yanıyorsa çekici çağırın.',
            'service_slug'   => 'mekanik-ustasi',
            'service_name'   => 'Motor & Mekanik Ustaları',
            'alt_slug'       => 'oto-cekici-yol-yardim',
            'alt_name'       => 'Acil Çekici / Yol Yardım'
        ),
        'battery-charging' => array(
            'id'             => 'battery-charging',
            'title'          => 'Akü & Şarj Sistemi İkaz Lambası',
            'color'          => 'red',
            'icon'           => 'fa-solid fa-car-battery',
            'severity'       => 'critical',
            'severity_label' => '🚨 Kritik - Araç Kısa Süre İçinde Stop Edebilir',
            'summary'        => 'Alternatörün (şarj dinamosu) aküyü şarj etmediğini veya şarj kayışının koptuğunu gösterir.',
            'symptoms'       => array(
                'Farların ve gösterge ışıklarının zayıflaması',
                'Radyo, klima ve ekranların kapanması',
                'Direksiyon sertleşmesi (elektrikli direksiyonlarda)',
                'Motorun aniden durması ve marş basmaması'
            ),
            'causes'         => array(
                'V kayışı (şarj kayışı) kopması veya gevşemesi',
                'Alternatör (şarj dinamosu) veya konjektör arızası',
                'Akü kutup başlarında oksitlenme veya gevşeklik',
                'Akü ömrünün tükenmesi (hücre bozulması)'
            ),
            'what_to_do'     => 'Gereksiz tüm elektrik tüketicilerini (klima, far, koltuk ısıtma, radyo) kapatın. Aracınız aküdeki son enerji bitene kadar çalışır, güvenli bir noktaya veya en yakın oto elektrikçiye ulaşın.',
            'service_slug'   => 'oto-elektrik-ustalari',
            'service_name'   => 'Oto Elektrik Ustaları',
            'alt_slug'       => 'oto-cekici-yol-yardim',
            'alt_name'       => 'Acil Çekici / Yol Yardım'
        ),
        'coolant-temp' => array(
            'id'             => 'coolant-temp',
            'title'          => 'Hararet & Soğutma Suyu Sıcaklık Lambası',
            'color'          => 'red',
            'icon'           => 'fa-solid fa-temperature-high',
            'severity'       => 'critical',
            'severity_label' => '🚨 Acil Durdur - Silindir Kapak Contası Yanabilir!',
            'summary'        => 'Motor soğutma suyu sıcaklığının tehlikeli derecede yükseldiğini bildirir. Aşırı ısı motor blok çatlağına ve conta yanmasına yol açar.',
            'symptoms'       => array(
                'Kaputtan beyaz buhar veya duman çıkması',
                'Hararet göstergesinin kırmızı bölgeye dayanması',
                'Tatlımsı antifriz kokusu gelmesi',
                'Kaloriferin sıcak hava üflememesi'
            ),
            'causes'         => array(
                'Radyatör hortumu patlaması veya soğutma sıvısı kaçağı',
                'Termostatın kapalı takılı kalması',
                'Radyatör fanının devreye girmemesi (fan müşürü veya sigortası)',
                'Devirdaim (su pompası) arızası',
                'Silindir kapak contasının yanması'
            ),
            'what_to_do'     => 'Aracı hemen kenara çekin ve motoru durdurun. ASLA MOTOR SICAKKEN RADYATÖR VEYA YEDEK SU KAPAĞINI AÇMAYIN! Ciddi yanıklara sebep olur. Motorun en az 30-40 dakika soğumasını bekleyin.',
            'service_slug'   => 'oto-radyator-tamir-ustalari',
            'service_name'   => 'Oto Radyatör & Soğutma Ustaları',
            'alt_slug'       => 'mekanik-ustasi',
            'alt_name'       => 'Motor & Mekanik Ustaları'
        ),
        'brake-system' => array(
            'id'             => 'brake-system',
            'title'          => 'Fren Sistemi & Hidrolik Seviye Lambası (!)',
            'color'          => 'red',
            'icon'           => 'fa-solid fa-circle-exclamation',
            'severity'       => 'critical',
            'severity_label' => '🚨 Kritik - Frenleme Performansı Kaybı Riski',
            'summary'        => 'El freni çekili unutulmuş olabilir veya fren hidrolik sıvısı seviyesi tehlikeli derecede düşmüştür.',
            'symptoms'       => array(
                'Fren pedalının tabana kadar yumuşakça inmesi (süngerimsi pedal)',
                'Fren mesafesinin belirgin uzaması',
                'Frene basıldığında metal sürtme sesi veya titreme'
            ),
            'causes'         => array(
                'El freninin tam indirilmemiş olması',
                'Fren balatalarının aşırı incelmesi nedeniyle hidrolik seviyesinin inmesi',
                'Fren hortumlarında, kaliperlerinde veya merkezinde hidrolik kaçağı',
                'Fren hidrolik seviye sensörü arızası'
            ),
            'what_to_do'     => 'Önce el frenini kontrol edin. İndirilmiş olmasına rağmen lamba yanıyorsa fren pedalına basın; pedal sertleşmiyor ve boşalıyorsa aracı sürmeyin, çekici çağırın.',
            'service_slug'   => 'fren-ve-balata-ustasi',
            'service_name'   => 'Fren & Balata Ustaları',
            'alt_slug'       => 'oto-cekici-yol-yardim',
            'alt_name'       => 'Acil Çekici / Yol Yardım'
        ),
        'abs-system' => array(
            'id'             => 'abs-system',
            'title'          => 'ABS Fren Sistemi İkaz Lambası',
            'color'          => 'amber',
            'icon'           => 'fa-solid fa-triangle-exclamation',
            'severity'       => 'warning',
            'severity_label' => 'Uyarı - Ani Frenlemede Tekerlekler Kilitlenebilir',
            'summary'        => 'Kilitlenme Karşıtı Fren Sistemi (ABS) devre dışı kalmıştır. Standart hidrolik frenler çalışmaya devam eder ancak acil frenlemede tekerlekler kilitlenip kızaklayabilir.',
            'symptoms'       => array(
                'Sert frende direksiyon kontrolünün kaybolması',
                'ABS devreye girdiğinde pedaldaki olağan titremenin olmaması'
            ),
            'causes'         => array(
                'Tekerlek hız sensörünün (ABS sensörü) kirlenmesi veya bozulması',
                'Sensör kablosunun kopması veya soket gevşemesi',
                'ABS beyni veya hidrolik valf bloğu arızası',
                'Porya rulmanı manyetik şeridinin hasar görmesi'
            ),
            'what_to_do'     => 'Normal hızlarda servise kadar gidebilirsiniz ancak takip mesafenizi iki katına çıkarın ve sert ani frenlerden kaçının. En kısa sürede diyagnostik cihazına bağlatın.',
            'service_slug'   => 'fren-ve-balata-ustasi',
            'service_name'   => 'Fren & Balata Ustaları',
            'alt_slug'       => 'oto-elektrik-ustalari',
            'alt_name'       => 'Oto Elektrik & Elektronik Ustaları'
        ),
        'esp-traction' => array(
            'id'             => 'esp-traction',
            'title'          => 'ESP / ESC / Çekiş Kontrol Lambası',
            'color'          => 'amber',
            'icon'           => 'fa-solid fa-road',
            'severity'       => 'warning',
            'severity_label' => 'Uyarı - Virajda ve Kaygan Zeminde Savrulma Riski',
            'summary'        => 'Elektronik Denge Programı (ESP/ESC) devre dışıdır. Yağışlı, karlı ve virajlı yollarda aracın arkadan veya önden kaymasını düzelten sistem çalışmaz.',
            'symptoms'       => array(
                'Kaygan zeminde patinaj çekme',
                'Virajlarda aracın savrulma eğiliminin artması'
            ),
            'causes'         => array(
                'Direksiyon açı sensörü kalibrasyon bozukluğu',
                'ABS tekerlek hız sensörü arızası',
                'ESP tuşuna basılarak manuel kapatılmış olması',
                'Rot-balans veya ön takım geometrisi bozukluğu'
            ),
            'what_to_do'     => 'ESP tuşuna basarak sistemin kapalı olup olmadığını kontrol edin. Kapanmıyorsa virajlara temkinli girerek rot-balans ve oto elektrik uzmanına başvurun.',
            'service_slug'   => 'rot-balans-ve-on-takim-ustasi',
            'service_name'   => 'Rot Balans & Ön Takım Ustaları',
            'alt_slug'       => 'oto-elektrik-ustalari',
            'alt_name'       => 'Oto Elektrik Ustaları'
        ),
        'tpms-tire' => array(
            'id'             => 'tpms-tire',
            'title'          => 'Lastik Basınç Uyarı Lambası (TPMS)',
            'color'          => 'amber',
            'icon'           => 'fa-solid fa-circle-dot',
            'severity'       => 'warning',
            'severity_label' => 'Uyarı - Bir veya Daha Fazla Lastikte Hava Kaybı',
            'summary'        => 'En az bir lastiğin hava basıncının tavsiye edilen değerin %25 altına düştüğünü bildirir.',
            'symptoms'       => array(
                'Aracın bir tarafa çekmesi',
                'Direksiyon sertleşmesi veya uğultu sesi',
                'Yakıt tüketiminde artış'
            ),
            'causes'         => array(
                'Lastiğe çivi veya vida batması (patlak)',
                'Mevsimsel hava sıcaklığı düşüşü (soğuk hava)',
                'Sibop iğnesi kaçağı veya jant kenarı sızıntısı',
                'Lastik içi TPMS basınç sensörü pili bitmesi veya arızası'
            ),
            'what_to_do'     => 'Hızınızı düşürün. En yakın benzinlikte tüm 4 lastiğin basıncını üretici fabrika etiketine (şoför kapı içinde yazar) göre tamamlayın ve göstergeden TPMS sıfırlaması yapın. Hava düşmeye devam ederse lastikçiye gidin.',
            'service_slug'   => 'oto-lastik-ve-jant-ustasi-lastik-oteli-rot-balans',
            'service_name'   => 'Oto Lastik & Jant Ustaları',
            'alt_slug'       => 'rot-balans-ve-on-takim-ustasi',
            'alt_name'       => 'Rot Balans Ustaları'
        ),
        'airbag-srs' => array(
            'id'             => 'airbag-srs',
            'title'          => 'Hava Yastığı & Emniyet Kemeri Lambası (Airbag / SRS)',
            'color'          => 'red',
            'icon'           => 'fa-solid fa-user-shield',
            'severity'       => 'critical',
            'severity_label' => '🚨 Kritik Güvenlik - Olası Kazada Hava Yastığı Açılmayabilir',
            'summary'        => 'Ek Güvenlik Sistemi (SRS) hava yastıklarında veya emniyet kemeri gergi fişeklerinde arıza olduğunu bildirir.',
            'symptoms'       => array(
                'Genellikle sürüşe doğrudan etkisi hissedilmez ancak kaza anında hayati tehlike oluşturur'
            ),
            'causes'         => array(
                'Koltuk altı airbag kablo soketlerinin gevşemesi (koltuk ileri-geri kaydırıldığında çok sık yaşanır)',
                'Direksiyon zembereği (spiral kablo) kopması',
                'Darbe sensörü veya emniyet kemeri toka sensörü arızası',
                'Airbag beyni (ECU) kaza kaydı veya arızası'
            ),
            'what_to_do'     => 'Koltuk altındaki sarı soketleri kontrol ettirin. Kaza anında yolcu ve sürücü can güvenliği için derhal oto elektrik ve hava yastığı uzmanına gösterin.',
            'service_slug'   => 'oto-elektrik-ustalari',
            'service_name'   => 'Oto Elektrik & Airbag Uzmanları',
            'alt_slug'       => 'oto-beyin-ve-beyin-tamiri-ustasi-ecu-cip-tunning-yazilim',
            'alt_name'       => 'Oto Beyin & Diyagnostik Ustaları'
        ),
        'dpf-diesel' => array(
            'id'             => 'dpf-diesel',
            'title'          => 'Dizel Partikül Filtresi (DPF) Lambası',
            'color'          => 'amber',
            'icon'           => 'fa-solid fa-smog',
            'severity'       => 'warning',
            'severity_label' => 'Uyarı - DPF Kurum Doluluk Seviyesi Yüksek',
            'summary'        => 'Dizel motorlu araçlarda egzozdaki partikül filtresinin kurumla dolduğunu ve rejenerasyon (kendi kendini temizleme) yapamadığını bildirir.',
            'symptoms'       => array(
                'Aracın çekişten düşmesi ve koruma moduna geçmesi',
                'Yakıt tüketiminde sıçrama',
                'Egzozdan koku veya duman gelmesi'
            ),
            'causes'         => array(
                'Sürekli kısa mesafe ve dur-kalk şehir içi sürüş',
                'Diferansiyel basınç sensörü arızası',
                'EGR valfi kirlenmesi veya enjektör işemesi',
                'Kalitesiz yakıt veya DPF uyumsuz motor yağı kullanımı'
            ),
            'what_to_do'     => 'Aracı çevre yoluna veya otoyola çıkarın; motor sıcakken 2000-2500 devir bandında sabit hızla 20-30 dakika sürün (pasif rejenerasyon). Lamba sönmezse DPF temizleme servisine gidin.',
            'service_slug'   => 'egzoz-ve-emisyon-sistemleri-ustasi',
            'service_name'   => 'Egzoz & DPF Emisyon Ustaları',
            'alt_slug'       => 'mekanik-ustasi',
            'alt_name'       => 'Dizel Motor & Enjeksiyon Ustaları'
        ),
        'adblue-scr' => array(
            'id'             => 'adblue-scr',
            'title'          => 'AdBlue / Egzoz Sıvısı İkaz Lambası',
            'color'          => 'amber',
            'icon'           => 'fa-solid fa-droplet',
            'severity'       => 'warning',
            'severity_label' => 'Uyarı - Kalan Mesafe Bitince Motor Yeniden Çalışmaz!',
            'summary'        => 'Euro 6 dizel araçlarda SCR katalizörü için gerekli AdBlue sıvısının azaldığını veya sistemde arıza (P20EE vb.) olduğunu gösterir.',
            'symptoms'       => array(
                'Göstergede kalan menzil uyarısı (örn: "800 km sonra motor çalışmayacak")',
                'Motor arıza lambası ile birlikte yanması'
            ),
            'causes'         => array(
                'AdBlue deposundaki sıvının tükenmesi',
                'Kristalleşmiş kalitesiz AdBlue veya enjektör tıkanıklığı',
                'NOx sensörü 1 veya 2 arızası',
                'AdBlue depo ısıtıcısı veya pompası arızası'
            ),
            'what_to_do'     => 'Kaliteli ISO 22241 standartlı AdBlue sıvısı ekleyin. Sıvı tam olmasına rağmen arıza lambası sönmüyorsa motoru stop etmeden önce yetkili/özel dizel servisine ulaşın.',
            'service_slug'   => 'egzoz-ve-emisyon-sistemleri-ustasi',
            'service_name'   => 'Egzoz & SCR Emisyon Ustaları',
            'alt_slug'       => 'mekanik-ustasi',
            'alt_name'       => 'Motor Mekanik Ustaları'
        ),
        'power-steering' => array(
            'id'             => 'power-steering',
            'title'          => 'Elektrikli / Hidrolik Direksiyon Lambası (EPS)',
            'color'          => 'amber',
            'icon'           => 'fa-solid fa-dharmachakra',
            'severity'       => 'warning',
            'severity_label' => 'Uyarı - Direksiyon Aşırı Ağırlaşabilir',
            'summary'        => 'Direksiyon destek sisteminde (EPS motoru veya hidrolik pompa) arıza olduğunu gösterir.',
            'symptoms'       => array(
                'Direksiyonun aşırı sertleşmesi ve manevra yaparken zorlanma',
                'Dönüşlerde takılma ve ses gelmesi'
            ),
            'causes'         => array(
                'Hidrolik direksiyon yağı kaçağı veya eksikliği',
                'Elektrikli direksiyon motoru (EPS) aşırı ısınması veya arızası',
                'Direksiyon tork sensörü kalibrasyon bozukluğu',
                'Akü voltaj düşüklüğü (yetersiz elektrik beslemesi)'
            ),
            'what_to_do'     => 'Park manevralarında çok güç harcamanız gerekecektir. Hidrolik seviyesini kontrol edin ve ön takım / direksiyon kutusu tamircisine gidin.',
            'service_slug'   => 'rot-balans-ve-on-takim-ustasi',
            'service_name'   => 'Direksiyon & Ön Takım Ustaları',
            'alt_slug'       => 'oto-elektrik-ustalari',
            'alt_name'       => 'Oto Elektrik Ustaları'
        ),
        'glow-plug' => array(
            'id'             => 'glow-plug',
            'title'          => 'Kızdırma Bujisi Lambası (Dizel Spiral / Yay)',
            'color'          => 'amber',
            'icon'           => 'fa-solid fa-bolt-lightning',
            'severity'       => 'warning',
            'severity_label' => 'Uyarı - Soğukta Zor Çalışma veya Motor Yönetim Hatası',
            'summary'        => 'Dizel araç marş öncesi yanma odasını ısıtan kızdırma bujilerinde veya sürüş esnasında yanıp sönüyorsa motor yönetiminde arıza olduğunu gösterir.',
            'symptoms'       => array(
                'Sabahları soğuk motorda geç veya zor çalışma',
                'İlk çalıştırmada beyaz duman ve sarsıntılı rölanti'
            ),
            'causes'         => array(
                'Bir veya birden fazla kızdırma bujisinin patlaması/yanması',
                'Kızdırma bujisi rölesi veya sigortası arızası',
                'Enjektör veya mazot basınç sensörü uyarısı'
            ),
            'what_to_do'     => 'Kızdırma bujisi direnç testi yaptırın ve patlak olanları orijinal parça ile değiştirtin.',
            'service_slug'   => 'oto-elektrik-ustalari',
            'service_name'   => 'Oto Elektrik Ustaları',
            'alt_slug'       => 'mekanik-ustasi',
            'alt_name'       => 'Dizel Motor Ustaları'
        ),
        'transmission' => array(
            'id'             => 'transmission',
            'title'          => 'Otomatik Şanzıman Arıza Lambası (Dişli İçinde Ünlem)',
            'color'          => 'amber',
            'icon'           => 'fa-solid fa-gear',
            'severity'       => 'critical',
            'severity_label' => '🚨 Kritik - Şanzıman Koruma (Limp-Home) Modu',
            'summary'        => 'Otomatik, DSG, CVT veya EDC şanzımanda mekanik, hidrolik veya elektronik bir arıza tespit edildiğini gösterir.',
            'symptoms'       => array(
                'Vites geçişlerinde sert vuruntu ve sarsıntı',
                'Vitesin belirli bir viteste (genelde 3. viteste) kilitlenmesi',
                'Geri vitese (R) geçmeme veya boşa düşme',
                'Kavrama kaçırması ve devir yükselmesine rağmen hızlanmama'
            ),
            'causes'         => array(
                'Şanzıman yağı eksikliği, yanması veya aşırı ısınması',
                'Mekatronik kartı veya solenoid valf arızası',
                'Çift kavrama (baskı-balata) aşınması',
                'Şanzıman hız sensörü veya tork konvertörü bozulması'
            ),
            'what_to_do'     => 'Aracı zorlamayın. Durup motoru kapatıp 5 dakika bekledikten sonra tekrar çalıştırın; düzelmiyorsa şanzımana daha fazla hasar vermemek için çekici ile otomatik şanzıman servisine çektirin.',
            'service_slug'   => 'sanziman-ve-guc-aktarma',
            'service_name'   => 'Otomatik Şanzıman Ustaları',
            'alt_slug'       => 'oto-cekici-yol-yardim',
            'alt_name'       => 'Acil Çekici / Yol Yardım'
        ),
    );
}

/**
 * 50+ Popüler ve En Çok Aranan OBD-II Arıza Kodları
 */
function ototamir_get_obd_codes_data() {
    return array(
        'P0420' => array(
            'code'        => 'P0420',
            'title_tr'    => 'Katalitik Konvertör Sistemi Verimliliği Eşik Altında (Bank 1)',
            'category'    => 'Egzoz & Emisyon',
            'severity'    => 'warning',
            'desc'        => 'Katalizörün egzoz gazlarındaki zararlı emisyonları temizleme kapasitesinin fabrika sınırlarının altına düştüğünü belirtir. Türkiye’de en sık karşılaşılan arıza kodudur.',
            'symptoms'    => array( 'Motor arıza lambası yanması', 'Hafif çekiş kaybı', 'Egzozdan çiğ yakıt/çürük yumurta kokusu', 'TÜVTÜRK egzoz muayenesinden geçememe' ),
            'causes'      => array( 'Katalitik konvertörün tıkanması veya peteklerinin erimesi', 'Arka (Bank 1 Sensör 2) Oksijen sensörü bozulması', 'Egzoz manifoldunda kaçak', 'Zengin karışım nedeniyle katalizöre çiğ yakıt gitmesi' ),
            'solution'    => 'Egzoz kaçağı kontrol edilmeli, oksijen sensör voltajları canlı veride incelenmeli ve gerekirse katalizör temizlenmeli veya yenilenmelidir.',
            'service'     => 'egzoz-ve-emisyon-sistemleri-ustasi',
            'service_name'=> 'Egzoz & Emisyon Servisleri'
        ),
        'P0300' => array(
            'code'        => 'P0300',
            'title_tr'    => 'Rastgele / Çoklu Silindirde Ateşleme Teklemesi (Misfire)',
            'category'    => 'Ateşleme & Yakıt',
            'severity'    => 'critical',
            'desc'        => 'Motorun birden fazla silindirinde yanmanın gerçekleşmediğini bildirir. Yanmayan yakıt egzoza giderek katalizörü eritebilir.',
            'symptoms'    => array( 'Rölantide aşırı sarsıntı ve titreme', 'Gaz yememe ve silkeleme', 'Motor arıza lambasının yanıp sönmesi', 'Yüksek yakıt sarfiyatı' ),
            'causes'      => array( 'Aşınmış veya arızalı bujiler', 'Ateşleme bobinlerinin yanması', 'Düşük yakıt basıncı veya tıkalı yakıt filtresi', 'Emme manifoldundan hava sızıntısı (vakum kaçağı)' ),
            'solution'    => 'Bujiler ve ateşleme bobinleri sökülüp test edilmeli, vakum kaçakları duman testiyle kontrol edilmelidir.',
            'service'     => 'mekanik-ustasi',
            'service_name'=> 'Motor & Mekanik Ustaları'
        ),
        'P0301' => array(
            'code'        => 'P0301',
            'title_tr'    => '1. Silindirde Ateşleme Teklemesi Algılandı',
            'category'    => 'Ateşleme & Motor',
            'severity'    => 'critical',
            'desc'        => 'Motor kontrol ünitesi (ECU), 1 numaralı silindirde yanma olmadığını tespit etmiştir.',
            'symptoms'    => array( 'Motorun 3 silindir gibi çalışması (tekleme)', 'Egzoz sesinde pıt-pıt vuruntu', 'Hızlanırken gaz pedalında titreme' ),
            'causes'      => array( '1. Silindir bujisi veya buji kablosu arızası', '1. Silindir ateşleme bobini bozulması', '1. Silindir yakıt enjektörü tıkanıklığı', 'Silindirde düşük kompresyon basıncı' ),
            'solution'    => '1. silindirin buji ve bobini 2. silindirle çaprazlanıp arızanın yer değiştirip değiştirmediği test edilmelidir.',
            'service'     => 'mekanik-ustasi',
            'service_name'=> 'Motor Mekanik Ustaları'
        ),
        'P0302' => array(
            'code'        => 'P0302',
            'title_tr'    => '2. Silindirde Ateşleme Teklemesi Algılandı',
            'category'    => 'Ateşleme & Motor',
            'severity'    => 'critical',
            'desc'        => '2 numaralı silindirde ateşleme veya yanma gerçekleşmemektedir.',
            'symptoms'    => array( 'Sarsıntılı çalışma', 'Çekiş düşüklüğü', 'Egzozdan koku' ),
            'causes'      => array( '2. silindir bujisi veya ateşleme bobini arızası', 'Enjektör arızası', 'Sübap ayarsızlığı veya kompresyon kaybı' ),
            'solution'    => '2. silindirin ateşleme parçaları kontrol edilmeli, enjektör püskürtmesi test edilmelidir.',
            'service'     => 'mekanik-ustasi',
            'service_name'=> 'Motor Mekanik Ustaları'
        ),
        'P0303' => array(
            'code'        => 'P0303',
            'title_tr'    => '3. Silindirde Ateşleme Teklemesi Algılandı',
            'category'    => 'Ateşleme & Motor',
            'severity'    => 'critical',
            'desc'        => '3 numaralı silindirde ateşleme kaybı tespit edilmiştir.',
            'symptoms'    => array( 'Motor sarsıntısı', 'Arıza lambası yanıp sönmesi' ),
            'causes'      => array( '3. buji veya bobin hasarı', '3. silindir enjektörü', 'Vakum kaçağı' ),
            'solution'    => 'Buji, bobin ve enjektör testi yapılmalıdır.',
            'service'     => 'mekanik-ustasi',
            'service_name'=> 'Motor Mekanik Ustaları'
        ),
        'P0304' => array(
            'code'        => 'P0304',
            'title_tr'    => '4. Silindirde Ateşleme Teklemesi Algılandı',
            'category'    => 'Ateşleme & Motor',
            'severity'    => 'critical',
            'desc'        => '4 numaralı silindirde yanma teklemesi meydana gelmektedir.',
            'symptoms'    => array( 'Rölantide titreme', 'Gaz yememe' ),
            'causes'      => array( '4. silindir buji veya bobini', 'Yakıt enjektörü', 'Kompresyon kaçağı' ),
            'solution'    => 'Ateşleme elemanları kontrol edilip yenilenmelidir.',
            'service'     => 'mekanik-ustasi',
            'service_name'=> 'Motor Mekanik Ustaları'
        ),
        'P0171' => array(
            'code'        => 'P0171',
            'title_tr'    => 'Sistem Çok Fakir Karışım (Bank 1)',
            'category'    => 'Yakıt & Hava',
            'severity'    => 'warning',
            'desc'        => 'Motorda yanma odasına giren hava miktarına kıyasla yakıt miktarının yetersiz olduğunu (fazla hava veya az yakıt) bildirir.',
            'symptoms'    => array( 'Hızlanırken tereddüt ve gaz yememe', 'Rölantide dalgalanma', 'Soğuk motorda zor çalışma' ),
            'causes'      => array( 'Vakum hortumlarında yırtık veya kaçak', 'Kirlenmiş veya arızalı Hava Akışmetre (MAF sensörü)', 'Tıkalı yakıt enjektörleri veya düşük yakıt pompası basıncı', 'LPG ayarsızlığı (LPG’li araçlarda çok sık)' ),
            'solution'    => 'Duman testiyle emme hattı kaçakları aranmalı, MAF sensörü özel spreyle temizlenmeli, LPG ayarı AFR cihazıyla kontrol edilmelidir.',
            'service'     => 'lpg-montaj-ustalari',
            'service_name'=> 'LPG & Yakıt Sistemi Ustaları'
        ),
        'P0172' => array(
            'code'        => 'P0172',
            'title_tr'    => 'Sistem Çok Zengin Karışım (Bank 1)',
            'category'    => 'Yakıt & Hava',
            'severity'    => 'warning',
            'desc'        => 'Motorda yanma odasında gereğinden fazla yakıt veya yetersiz hava olduğunu bildirir.',
            'symptoms'    => array( 'Aşırı yakıt tüketimi', 'Egzozdan siyah duman ve benzin kokusu', 'Bujilerin kararması' ),
            'causes'      => array( 'Damlatma yapan (işeyen) yakıt enjektörleri', 'Tıkalı hava filtresi', 'Aşırı yakıt basıncı (yakıt regülatörü arızası)', 'Arızalı Oksijen (O2) sensörü' ),
            'solution'    => 'Hava filtresi kontrol edilmeli, enjektör geri dönüş testi yapılmalı ve oksijen sensörü kontrol edilmelidir.',
            'service'     => 'mekanik-ustasi',
            'service_name'=> 'Motor & Mekanik Ustaları'
        ),
        'P0101' => array(
            'code'        => 'P0101',
            'title_tr'    => 'Hava Akışmetre (MAF) Devresi Aralık / Performans Sorunu',
            'category'    => 'Hava Emiş Sistemi',
            'severity'    => 'warning',
            'desc'        => 'Kütle Hava Akış (MAF) sensörünün ECU’ya gönderdiği hava miktarı sinyalinin beklenen mantıksal değer aralığının dışında olduğunu belirtir.',
            'symptoms'    => array( 'Vites geçişlerinde silkeleme', 'Siyah duman atma', 'Rölantide stop etme' ),
            'causes'      => array( 'MAF sensör telinin yağ ve tozla kirlenmesi', 'Hava filtresi borusunda çatlak/hava girişi', 'Sensör soketinde oksitlenme' ),
            'solution'    => 'Sensör soketi kontrol edilmeli, MAF temizleme spreyi ile yıkanmalı, hava filtresi yenilenmelidir.',
            'service'     => 'oto-elektrik-ustalari',
            'service_name'=> 'Oto Elektrik & Sensör Ustaları'
        ),
        'P0113' => array(
            'code'        => 'P0113',
            'title_tr'    => 'Emme Havası Sıcaklık (IAT) Sensörü Devresi Yüksek Giriş',
            'category'    => 'Hava Emiş Sistemi',
            'severity'    => 'info',
            'desc'        => 'ECU, motora giren havanın sıcaklığını aşırı soğuk (-40°C) olarak okur (genelde açık devre kopukluk).',
            'symptoms'    => array( 'Soğuk havada geç marş alma', 'Zengin karışım ve yakıt artışı' ),
            'causes'      => array( 'IAT sensör soketinin çıkmış olması', 'Kopuk sensör kablosu', 'Arızalı sıcaklık sensörü' ),
            'solution'    => 'Sensör kablo tesisatı ve soket tırnakları kontrol edilmeli, sensör direnci ölçülmelidir.',
            'service'     => 'oto-elektrik-ustalari',
            'service_name'=> 'Oto Elektrik Ustaları'
        ),
        'P0128' => array(
            'code'        => 'P0128',
            'title_tr'    => 'Soğutma Suyu Termostatı Çalışma Sıcaklığı Altında (Termostat Açık Kaldı)',
            'category'    => 'Soğutma Sistemi',
            'severity'    => 'warning',
            'desc'        => 'Motorun çalıştıktan sonra optimum çalışma sıcaklığına (85-90°C) belirlenen sürede ulaşamadığını bildirir.',
            'symptoms'    => array( 'Hararet ibresinin yükselmemesi (özellikle yolda giderken düşmesi)', 'Kaloriferin ılık veya soğuk üflemesi', 'Motorun sürekli zengin karışımda çalışıp çok yakması' ),
            'causes'      => array( 'Termostatın mekanik olarak açık takılı kalması', 'Motor soğutma suyu sıcaklık (ECT) sensörü arızası' ),
            'solution'    => 'Termostat sökülüp yenilenmeli ve soğutma sıvısı antifrizi yenilenmelidir.',
            'service'     => 'oto-radyator-tamir-ustalari',
            'service_name'=> 'Oto Radyatör & Termostat Ustaları'
        ),
        'P0130' => array(
            'code'        => 'P0130',
            'title_tr'    => 'Oksijen Sensörü Devre Arızası (Bank 1 Sensör 1)',
            'category'    => 'Egzoz & Emisyon',
            'severity'    => 'warning',
            'desc'        => 'Katalizör öncesi ana oksijen (Lambda) sensörünün devresinde voltaj hatası olduğunu gösterir.',
            'symptoms'    => array( 'Yakıt tüketiminde %15-25 artış', 'Çekiş düşüklüğü', 'Egzoz muayene hatası' ),
            'causes'      => array( 'Sensör ısıtıcı devresinin yanması', 'Sensör kablosunun egzoz borusuna değip erimesi', 'Ömrü bitmiş sensör' ),
            'solution'    => 'Kablo tesisatı incelenmeli, sensör canlı voltaj dalgalanması osiloskop veya cihazla izlenip gerekiyorsa değiştirilmelidir.',
            'service'     => 'oto-elektrik-ustalari',
            'service_name'=> 'Oto Elektrik Ustaları'
        ),
        'P0299' => array(
            'code'        => 'P0299',
            'title_tr'    => 'Turboşarj / Süperşarj Düşük Basınç (Underboost)',
            'category'    => 'Turbo & Motor',
            'severity'    => 'warning',
            'desc'        => 'Turboşarjın ürettiği basıncın ECU’nun talep ettiği hedef basıncın belirgin şekilde altında kaldığını bildirir.',
            'symptoms'    => array( 'Aracın rampalarda bayılması ve hızlanmaması', 'Turbodan ıslık veya hava kaçırma sesi gelmesi', 'Araç koruma moduna geçmesi (Limp Mode)' ),
            'causes'      => array( 'Intercooler veya turbo hortumlarında patlak/yarık', 'Turbo wastegate valfi veya elektrovanası (N75) arızası', 'Turbo pervanesinde boşluk veya mekanik aşınma', 'Tıkalı DPF/Katalizörün egzoz gazını sıkıştırması' ),
            'solution'    => 'Turbo basınç hortumları duman ve basınç testiyle kontrol edilmeli, N75 elektrovanası ve turbo mili incelenmelidir.',
            'service'     => 'mekanik-ustasi',
            'service_name'=> 'Turbo & Motor Mekanik Ustaları'
        ),
        'P0234' => array(
            'code'        => 'P0234',
            'title_tr'    => 'Turboşarj Aşırı Basınç Durumu (Overboost)',
            'category'    => 'Turbo & Motor',
            'severity'    => 'critical',
            'desc'        => 'Turbonun motora zarar verecek seviyede aşırı yüksek basınç ürettiğini bildirir.',
            'symptoms'    => array( 'Gaza basınca aniden çekişin kesilmesi', 'Aracın silkelenmesi' ),
            'causes'      => array( 'Wastegate valfinin kapalı takılı kalması', 'VNT değişken geometrili turbo kanatçıklarının kurumdan sıkışması', 'Basınç kontrol hortumunun çıkması' ),
            'solution'    => 'Turbo geometrisi kurum temizliği yapılmalı, aktüatör mekanizması kontrol edilmelidir.',
            'service'     => 'mekanik-ustasi',
            'service_name'=> 'Turbo & Mekanik Ustaları'
        ),
        'P0401' => array(
            'code'        => 'P0401',
            'title_tr'    => 'Egzoz Gazı Devridaimi (EGR) Sistemi Yetersiz Akış Algılandı',
            'category'    => 'EGR & Emisyon',
            'severity'    => 'warning',
            'desc'        => 'EGR valfinin motora yeterli miktarda egzoz gazı geri gönderemediğini gösterir.',
            'symptoms'    => array( 'Hafif vuruntulu çalışma', 'Yakıt artışı', 'Emisyon muayene başarısızlığı' ),
            'causes'      => array( 'EGR valfinin ve kanallarının yoğun kurumla tıkanması', 'EGR vakum kontrol selenoidi arızası', 'Diferansiyel basınç sensörü kirliliği' ),
            'solution'    => 'EGR valfi sökülüp ultrasonik veya kimyasal temizliği yapılmalı, kanallar açılmalıdır.',
            'service'     => 'egzoz-ve-emisyon-sistemleri-ustasi',
            'service_name'=> 'EGR & Emisyon Ustaları'
        ),
        'P0500' => array(
            'code'        => 'P0500',
            'title_tr'    => 'Araç Hız Sensörü (VSS) Arızası',
            'category'    => 'Sensör & Şanzıman',
            'severity'    => 'warning',
            'desc'        => 'Hız göstergesi ibresinin çalışmamasına veya ECU’nun hız bilgisini alamamasına yol açar.',
            'symptoms'    => array( 'Hız göstergesinin sıfırda kalması veya dalgalanması', 'Otomatik vites geçişlerinin bozulması', 'Hız sabitleyicinin (Cruise Control) çalışmaması' ),
            'causes'      => array( 'Şanzıman üzerindeki hız sensörü bozulması', 'ABS beyninden hız verisi iletim hattında kopukluk', 'Sensör kablosunda şase' ),
            'solution'    => 'Hız sensörü ve ABS tekerlek sensörü canlı verisi kontrol edilip parça yenilenmelidir.',
            'service'     => 'oto-elektrik-ustalari',
            'service_name'=> 'Oto Elektrik & Sensör Ustaları'
        ),
        'P0700' => array(
            'code'        => 'P0700',
            'title_tr'    => 'Şanzıman Kontrol Sistemi Arızası (TCM Uyarısı)',
            'category'    => 'Şanzıman',
            'severity'    => 'critical',
            'desc'        => 'Şanzıman beyninin (TCM) bir arıza tespit ettiğini ve motor beynine (ECU) arıza lambasını yakması için sinyal gönderdiğini belirtir.',
            'symptoms'    => array( 'Şanzımanın koruma moduna geçmesi', 'Vites geçmeme', 'Vuruntu' ),
            'causes'      => array( 'Şanzıman içinde solenoid valf, yağ basıncı veya debriyaj arızası', 'Şanzıman beyni haberleşme hatası' ),
            'solution'    => 'TCM (Şanzıman beyni) alt arıza kodları (P07xx serisi) okunmalı ve uzman şanzıman servisine gidilmelidir.',
            'service'     => 'sanziman-ve-guc-aktarma',
            'service_name'=> 'Otomatik Şanzıman Ustaları'
        ),
        'P20EE' => array(
            'code'        => 'P20EE',
            'title_tr'    => 'SCR NOx Katalizörü Verimliliği Eşik Altında (Bank 1)',
            'category'    => 'AdBlue & Emisyon',
            'severity'    => 'warning',
            'desc'        => 'Euro 6 dizel araçlarda AdBlue/SCR sisteminin egzozdaki azot oksit gazlarını yeterince arındıramadığını bildirir.',
            'symptoms'    => array( 'Motor arıza lambası ve AdBlue uyarısı', 'Belirli km sonra motorun çalışmayacağı bildirimi' ),
            'causes'      => array( 'Kalitesiz veya kristalleşmiş AdBlue sıvısı', 'AdBlue enjektörünün beyaz kristal tortuyla tıkanması', 'NOx sensörü (giriş veya çıkış) arızası', 'SCR katalizör petek bozulması' ),
            'solution'    => 'AdBlue enjektörü sökülüp temizlenmeli, NOx sensör değerleri test edilmeli, orijinal AdBlue konulmalıdır.',
            'service'     => 'egzoz-ve-emisyon-sistemleri-ustasi',
            'service_name'=> 'AdBlue & Egzoz Emisyon Ustaları'
        ),
        'P2463' => array(
            'code'        => 'P2463',
            'title_tr'    => 'Dizel Partikül Filtresi (DPF) Kurum Birikimi Kısıtlaması',
            'category'    => 'DPF & Dizel',
            'severity'    => 'warning',
            'desc'        => 'DPF içerisindeki kül ve kurum miktarının güvenli temizleme eşiğini aştığını bildirir.',
            'symptoms'    => array( 'Çekiş düşüklüğü', 'Yüksek yakıt', 'DPF lambası' ),
            'causes'      => array( 'Sürekli kısa mesafe şehir içi kullanım', 'Diferansiyel basınç sensörü bozulması', 'Arızalı termostat nedeniyle motorun 90 dereceye ulaşamaması' ),
            'solution'    => 'Cihazla zorunlu rejenerasyon denenmeli, sonuç vermezse DPF sökülüp makinede yıkanmalıdır.',
            'service'     => 'egzoz-ve-emisyon-sistemleri-ustasi',
            'service_name'=> 'DPF & Egzoz Servisleri'
        ),
        'P0011' => array(
            'code'        => 'P0011',
            'title_tr'    => 'Eksantrik Mili Pozisyonu - Aşırı Erken / Sistem Performansı (Bank 1)',
            'category'    => 'Motor & Triger',
            'severity'    => 'critical',
            'desc'        => 'Değişken sübap zamanlama (VVT / Vanos) sisteminde eksantrik milinin hedef zamanlamadan daha ileri olduğunu gösterir.',
            'symptoms'    => array( 'Motordan şıkırtı veya zincir şakırtısı sesi gelmesi', 'Rölanti bozukluğu', 'Düşük devirde tekleme' ),
            'causes'      => array( 'Motor yağının eskimesi veya seviyesinin çok düşük olması', 'VVT selenoid valfinin yağ tortusuyla tıkanması', 'Triger zincirinde uzama veya senteden kaçma' ),
            'solution'    => 'Acilen motor yağı ve filtresi değiştirilmeli, VVT selenoidi temizlenmeli, triger zincir/kayış sentesi kontrol edilmelidir.',
            'service'     => 'mekanik-ustasi',
            'service_name'=> 'Motor & Triger Mekanik Ustaları'
        ),
        'P0016' => array(
            'code'        => 'P0016',
            'title_tr'    => 'Krank Mili - Eksantrik Mili Pozisyon Korelasyon Hatası (Bank 1 Sensör A)',
            'category'    => 'Triger & Motor',
            'severity'    => 'critical',
            'desc'        => 'Krank mili ile eksantrik milinin dönüş senkronizasyonunun bozulduğunu belirtir. Triger atlamış olabilir!',
            'symptoms'    => array( 'Motorun çok zor çalışması veya marş basıp hiç çalışmaması', 'Motordan mekanik vuruntu sesi', 'Aşırı çekiş kaybı' ),
            'causes'      => array( 'Triger kayışının diş atlaması veya zincirin esnemesi (sente kaçıklığı)', 'Eksantrik veya krank sensörü arızası', 'Eksantrik dişlisi (Vanos kasnağı) arızası' ),
            'solution'    => 'ARACI ÇALIŞTIRMAYIN. Sübapların pistona vurup eğilmesini önlemek için derhal triger sentesi uzman mekanik ustası tarafından kilitlenerek kontrol edilmelidir.',
            'service'     => 'mekanik-ustasi',
            'service_name'=> 'Motor & Triger Ustaları'
        ),
        'P0087' => array(
            'code'        => 'P0087',
            'title_tr'    => 'Yakıt Dağıtım Borusu (Rail) Basıncı Çok Düşük',
            'category'    => 'Yakıt & Enjeksiyon',
            'severity'    => 'critical',
            'desc'        => 'Common Rail veya doğrudan enjeksiyon sisteminde yüksek basınç hattındaki yakıt basıncının yetersiz olduğunu gösterir.',
            'symptoms'    => array( 'Ani gaza basıldığında motorun stop etmesi', 'Zor marş alma', 'Yüksek hızlarda çekiş kesilmesi' ),
            'causes'      => array( 'Tıkalı mazot / benzin filtresi', 'Yüksek basınç pompası (mazot pompası) aşınması', 'Geri dönüşe fazla kaçıran enjektör', 'Depo içi yakıt besleme pompası arızası' ),
            'solution'    => 'Yakıt filtresi yenilenmeli, pompa basıncı ve enjektör geri dönüşleri ölçülmelidir.',
            'service'     => 'oto-enjeksiyon-ustalari',
            'service_name'=> 'Dizel Enjeksiyon & Pompa Ustaları'
        ),
        'P0562' => array(
            'code'        => 'P0562',
            'title_tr'    => 'Sistem Voltajı Düşük',
            'category'    => 'Elektrik & Şarj',
            'severity'    => 'warning',
            'desc'        => 'Aracın elektrik besleme voltajının 10.5 Voltun altına düştüğünü bildirir.',
            'symptoms'    => array( 'Gösterge ekranlarının kapanması', 'Farların kısılması', 'ABS ve Airbag lambalarının peş peşe yanması' ),
            'causes'      => array( 'Biten veya ömrü tükenmiş akü', 'Şarj dinamosunun (alternatör) yeterli voltaj üretmemesi', 'Gevşek veya kopuk V kayışı' ),
            'solution'    => 'Akü ve şarj dinamosu şarj ölçümü yapılmalıdır (rölantide 13.8V - 14.4V arası olmalıdır).',
            'service'     => 'oto-elektrik-ustalari',
            'service_name'=> 'Oto Elektrik & Akü Ustaları'
        ),
        'U0100' => array(
            'code'        => 'U0100',
            'title_tr'    => 'Motor Kontrol Modülü (ECM/PCM) İle İletişim Kaybı',
            'category'    => 'CAN-Bus & Elektronik',
            'severity'    => 'critical',
            'desc'        => 'Aracın diğer beyinleri (ABS, Gösterge, Şanzıman) CAN-Bus veri yolu üzerinden motor beyninden yanıt alamamaktadır.',
            'symptoms'    => array( 'Marş basmasına rağmen motorun çalışmaması', 'Gösterge panelinde tüm ışıkların yanması veya çizgiler çıkması', 'Vitese geçmeme' ),
            'causes'      => array( 'Motor beyni ana besleme sigortası veya rölesinin atması', 'Akü kutup başı veya şase kablosunda temassızlık', 'CAN-Bus haberleşme kablosunda kopukluk veya kısa devre', 'Motor beynine su girmesi veya kart hasarı' ),
            'solution'    => 'Sigorta kutusu ana röleleri, şase bağlantıları ve CAN hattı direnç testi yapılmalıdır.',
            'service'     => 'oto-beyin-ve-beyin-tamiri-ustasi-ecu-cip-tunning-yazilim',
            'service_name'=> 'Oto Beyin & Elektronik Ustaları'
        ),
        'C0035' => array(
            'code'        => 'C0035',
            'title_tr'    => 'Sol Ön Tekerlek Hız Sensörü Devresi (ABS Hatası)',
            'category'    => 'Fren & ABS',
            'severity'    => 'warning',
            'desc'        => 'ABS beyni sol ön tekerlek hız sensöründen sinyal alamamaktadır.',
            'symptoms'    => array( 'ABS ve ESP lambalarının yanması', 'Hız sabitleyicinin devre dışı kalması' ),
            'causes'      => array( 'Sol ön ABS sensör kablosunun kopması', 'Porya rulmanı manyetik halkasının kirlenmesi/kırılması', 'Sensör bozulması' ),
            'solution'    => 'Sol ön tekerlek sökülüp sensör ve kablo hattı kontrol edilmeli, gerekirse sensör yenilenmelidir.',
            'service'     => 'fren-ve-balata-ustasi',
            'service_name'=> 'Fren & Balata Ustaları'
        ),
        'B0001' => array(
            'code'        => 'B0001',
            'title_tr'    => 'Sürücü Hava Yastığı Kademesi 1 Dağıtım Kontrolü',
            'category'    => 'Airbag & Güvenlik',
            'severity'    => 'critical',
            'desc'        => 'Sürücü direksiyon hava yastığı ateşleme devresinde direnç hatası veya açık devre tespit edilmiştir.',
            'symptoms'    => array( 'Kırmızı Airbag lambasının sürekli yanması' ),
            'causes'      => array( 'Direksiyon zembereğinin (spiral şerit kablo) kopması', 'Airbag soket gevşekliği' ),
            'solution'    => 'Direksiyon zembereği kontrol edilip onarılmalı veya yenilenmelidir.',
            'service'     => 'oto-elektrik-ustalari',
            'service_name'=> 'Airbag & Oto Elektrik Ustaları'
        )
    );
}
