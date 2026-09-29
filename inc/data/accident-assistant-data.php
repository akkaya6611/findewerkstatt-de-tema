<?php
/**
 * Kaza Anı Asistanı & Tutanak Sihirbazı Veri Seti
 * 
 * @package OtoTamir360
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Kaza Anı Acil Müdahale Adımlarını Döndürür
 * 
 * @return array
 */
function ototamir_get_accident_emergency_steps() {
    return array(
        array(
            'step'        => 1,
            'title'       => 'Can Güvenliği & Dörtlüleri Yakın',
            'desc'        => 'Kaza anında ilk yapılması gereken şey paniği durdurmak, motoru kapatmak ve hemen dörtlü ikaz flaşörlerini yakmaktır.',
            'icon'        => 'fa-triangle-exclamation',
            'alert_type'  => 'critical',
            'action_note' => 'Aracınız hareket edebiliyorsa ve trafik akışı tehlikedeyse fotoğraf çektikten sonra güvenli emniyet şeridine geçin.'
        ),
        array(
            'step'        => 2,
            'title'       => 'Reflektör Yerleşimi (Hayati Kural)',
            'desc'        => 'Arkadan gelecek araçların çarpmasını önlemek için bagajdaki üçgen reflektörü kurun.',
            'icon'        => 'fa-signs-post',
            'alert_type'  => 'warning',
            'action_note' => 'Şehir içi yollarda aracın 30 metre arkasına, şehirler arası yollarda ve otoyollarda en az 150 metre arkasına yerleştirin.'
        ),
        array(
            'step'        => 3,
            'title'       => 'Yaralanma Kontrolü & 112 Acil',
            'desc'        => 'Kendi aracınızdaki ve karşı taraftaki yolcuların sağlık durumunu kontrol edin.',
            'icon'        => 'fa-kit-medical',
            'alert_type'  => 'critical',
            'action_note' => 'En ufak yaralanma, baş dönmesi veya baygınlık varsa araçları KESİNLİKLE kıpırdatmayın ve hemen 112’yi arayın!'
        ),
        array(
            'step'        => 4,
            'title'       => 'Fotoğraf ve Video Kanıtı Alın',
            'desc'        => 'Araçları yerinden oynatmadan önce her iki aracın plakaları, çarpışma açısı ve fren izleri görünecek şekilde geniş açıdan fotoğraflayın.',
            'icon'        => 'fa-camera',
            'alert_type'  => 'info',
            'action_note' => 'En az 4 farklı açıdan (ön, arka, sağ, sol ve yol çizgileriyle birlikte) net fotoğraflar çekin.'
        ),
        array(
            'step'        => 5,
            'title'       => 'Tutanak Tutun veya Çekici Çağırın',
            'desc'        => 'Taraflarla karşılıklı ruhsat, ehliyet ve zorunlu trafik sigortası poliçelerinin fotoğraflarını çekip kaza tutanağını doldurun.',
            'icon'        => 'fa-file-signature',
            'alert_type'  => 'success',
            'action_note' => 'Araç yürümüyorsa hemen ototamircibul.com.tr 7/24 Acil Çekici butonundan en yakın çekiciyi çağırın.'
        )
    );
}

/**
 * Hangi Durumlarda Trafik Polisi / Jandarma Çağrılmalıdır?
 * 
 * @return array
 */
function ototamir_get_police_checklist() {
    return array(
        array(
            'condition'   => 'Ölüm veya yaralanma olması durumu',
            'police'      => true,
            'note'        => '112 Aranmalıdır. Yaralanmalı kazalarda taraflar kendi aralarında tutanak tutamaz.'
        ),
        array(
            'condition'   => 'Sürücülerden birinin ehliyetsiz olması veya ehliyet sınıfının yetersizliği',
            'police'      => true,
            'note'        => 'Ehliyetsiz araç kullanımı durumunda sigorta hasar ödemesi için polis zaptı şarttır.'
        ),
        array(
            'condition'   => 'Sürücülerden birinde alkol veya uyuşturucu madde şüphesi olması',
            'police'      => true,
            'note'        => 'Polis ekipleri alkolmetreli ölçüm ve yasal alkol raporu düzenlemek zorundadır.'
        ),
        array(
            'condition'   => 'Araçlardan birinin Zorunlu Trafik Sigortası (ZMMS) bulunmaması',
            'police'      => true,
            'note'        => 'Sigortasız araç kazaya karıştığında zabıt tutulmalı ve Güvence Hesabı devreye girmelidir.'
        ),
        array(
            'condition'   => 'Kamu malına veya 3. şahısların eşyalarına (bariyer, direk, dükkan) zarar gelmesi',
            'police'      => true,
            'note'        => 'Kamu kurumu hasar tespiti için resmi tutanak şarttır.'
        ),
        array(
            'condition'   => 'Sürücülerden birinin aracı resmi kuruma ait (ambulans, polis, resmi plaka) olması',
            'police'      => true,
            'note'        => 'Resmi araç kazalarında kolluk kuvveti çağrılır.'
        ),
        array(
            'condition'   => 'Sürücünün akli dengesinin yerinde olmaması veya yaşının küçük olması',
            'police'      => true,
            'note'        => 'Yasal ehliyet geçerliliği olmadığından resmi zabıt gerekir.'
        ),
        array(
            'condition'   => 'Yalnızca maddi hasarlı ve her iki tarafın evrakları tam & anlaşması durumu',
            'police'      => false,
            'note'        => 'Polis ÇAĞIRILMAZ! Sürücüler kendi aralarında Kaza Tespit Tutanağı düzenler. Polis gelirse ceza yazabilir.'
        )
    );
}

/**
 * Sigorta Bilgi ve Gözetim Merkezi (SBM) Kusur Oranı Senaryoları
 * 
 * @return array
 */
function ototamir_get_fault_scenarios() {
    return array(
        array(
            'scenario'    => 'Arkadan Çarpma',
            'fault_a'     => '%100 Kusurlu',
            'fault_b'     => '%0 Kusursuz',
            'desc'        => 'Öndeki araç ne sebeple fren yaparsa yapsın, arkadaki araç takip mesafesini korumak zorundadır (KTK Madde 56/1-c). Arkadan çarpan taraf %100 suçludur.',
            'badge'       => 'Net Kural'
        ),
        array(
            'scenario'    => 'Kırmızı Işıkta Geçerek Çarpışma',
            'fault_a'     => '%100 Kusurlu (Kırmızıda Geçen)',
            'fault_b'     => '%0 Kusursuz (Yeşilde Geçen)',
            'desc'        => 'Işıklı işaret cihazlarına uymamak asli kusurdur (Madde 47/1-b). Şahit veya kamera kaydıyla teyit edilir.',
            'badge'       => 'Asli Kusur'
        ),
        array(
            'scenario'    => 'Geri Giderken Başka Araca Çarpma',
            'fault_a'     => '%100 Kusurlu (Geri Giden)',
            'fault_b'     => '%0 Kusursuz',
            'desc'        => 'Geri manevra yapan araç yolu ve arkasını kontrol etmekle yükümlüdür. Geri vitesle çarpan taraf tam kusurludur.',
            'badge'       => 'Net Kural'
        ),
        array(
            'scenario'    => 'Kontrolsüz Kavşakta Sağdaki Araca Yol Vermeme',
            'fault_a'     => '%100 Kusurlu (Soldan Gelen)',
            'fault_b'     => '%0 Kusursuz (Sağdan Gelen)',
            'desc'        => 'Eşdeğer ve ışıksız kontrolsüz kavşaklarda kavşak içi sağ kuralı geçerlidir; sağdan gelen araca ilk geçiş hakkı verilmelidir.',
            'badge'       => 'Kavşak Kuralı'
        ),
        array(
            'scenario'    => 'Park Yerinden / Sokaktan Çıkarken Ana Yoldaki Araca Çarpma',
            'fault_a'     => '%100 Kusurlu (Yola Çıkan)',
            'fault_b'     => '%0 Kusursuz (Ana Yoldaki)',
            'desc'        => 'Parktan çıkan veya tali yoldan ana yola bağlanan sürücü, ana yol trafiğini beklemek zorundadır.',
            'badge'       => 'Geçiş Üstünlüğü'
        ),
        array(
            'scenario'    => 'Dönel Kavşak (Ada) İçi ve Dışı Çarpışma',
            'fault_a'     => '%100 Kusurlu (Adaya Giren)',
            'fault_b'     => '%0 Kusursuz (Ada İçindeki)',
            'desc'        => 'Dönel kavşak içindeki araç her zaman geçiş önceliğine sahiptir; adaya girmek isteyen yol vermek zorundadır.',
            'badge'       => 'Dönel Kavşak'
        ),
        array(
            'scenario'    => 'Şerit Değiştirirken Yanındaki Araca Sürtme',
            'fault_a'     => '%100 Kusurlu (Şerit Değiştiren)',
            'fault_b'     => '%0 Kusursuz (Kendi Şeridindeki)',
            'desc'        => 'Sinyal verseniz dahi şerit değiştiren araç kendi şeridinde düz giden aracın geçişini beklemelidir.',
            'badge'       => 'Şerit İhlali'
        )
    );
}
