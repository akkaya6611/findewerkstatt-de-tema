<?php
/*
Template Name: İller Sayfası
*/
get_header();

// 81 İl Listesi ve Bölge Eşleştirmesi
$city_regions = array(
    'adana' => array('name' => 'Adana', 'code' => '01', 'region' => 'akdeniz'),
    'adiyaman' => array('name' => 'Adıyaman', 'code' => '02', 'region' => 'guneydogu'),
    'afyonkarahisar' => array('name' => 'Afyonkarahisar', 'code' => '03', 'region' => 'ege'),
    'agri' => array('name' => 'Ağrı', 'code' => '04', 'region' => 'dogu'),
    'amasya' => array('name' => 'Amasya', 'code' => '05', 'region' => 'karadeniz'),
    'ankara' => array('name' => 'Ankara', 'code' => '06', 'region' => 'icanadolu'),
    'antalya' => array('name' => 'Antalya', 'code' => '07', 'region' => 'akdeniz'),
    'artvin' => array('name' => 'Artvin', 'code' => '08', 'region' => 'karadeniz'),
    'aydin' => array('name' => 'Aydın', 'code' => '09', 'region' => 'ege'),
    'balikesir' => array('name' => 'Balıkesir', 'code' => '10', 'region' => 'marmara'),
    'bilecik' => array('name' => 'Bilecik', 'code' => '11', 'region' => 'marmara'),
    'bingol' => array('name' => 'Bingöl', 'code' => '12', 'region' => 'dogu'),
    'bitlis' => array('name' => 'Bitlis', 'code' => '13', 'region' => 'dogu'),
    'bolu' => array('name' => 'Bolu', 'code' => '14', 'region' => 'karadeniz'),
    'burdur' => array('name' => 'Burdur', 'code' => '15', 'region' => 'akdeniz'),
    'bursa' => array('name' => 'Bursa', 'code' => '16', 'region' => 'marmara'),
    'canakkale' => array('name' => 'Çanakkale', 'code' => '17', 'region' => 'marmara'),
    'cankiri' => array('name' => 'Çankırı', 'code' => '18', 'region' => 'icanadolu'),
    'corum' => array('name' => 'Çorum', 'code' => '19', 'region' => 'karadeniz'),
    'denizli' => array('name' => 'Denizli', 'code' => '20', 'region' => 'ege'),
    'diyarbakir' => array('name' => 'Diyarbakır', 'code' => '21', 'region' => 'guneydogu'),
    'edirne' => array('name' => 'Edirne', 'code' => '22', 'region' => 'marmara'),
    'elazig' => array('name' => 'Elazığ', 'code' => '23', 'region' => 'dogu'),
    'erzincan' => array('name' => 'Erzincan', 'code' => '24', 'region' => 'dogu'),
    'erzurum' => array('name' => 'Erzurum', 'code' => '25', 'region' => 'dogu'),
    'eskisehir' => array('name' => 'Eskişehir', 'code' => '26', 'region' => 'icanadolu'),
    'gaziantep' => array('name' => 'Gaziantep', 'code' => '27', 'region' => 'guneydogu'),
    'giresun' => array('name' => 'Giresun', 'code' => '28', 'region' => 'karadeniz'),
    'gumushane' => array('name' => 'Gümüşhane', 'code' => '29', 'region' => 'karadeniz'),
    'hakkari' => array('name' => 'Hakkari', 'code' => '30', 'region' => 'dogu'),
    'hatay' => array('name' => 'Hatay', 'code' => '31', 'region' => 'akdeniz'),
    'isparta' => array('name' => 'Isparta', 'code' => '32', 'region' => 'akdeniz'),
    'mersin' => array('name' => 'Mersin', 'code' => '33', 'region' => 'akdeniz'),
    'istanbul' => array('name' => 'İstanbul', 'code' => '34', 'region' => 'marmara'),
    'izmir' => array('name' => 'İzmir', 'code' => '35', 'region' => 'ege'),
    'kars' => array('name' => 'Kars', 'code' => '36', 'region' => 'dogu'),
    'kastamonu' => array('name' => 'Kastamonu', 'code' => '37', 'region' => 'karadeniz'),
    'kayseri' => array('name' => 'Kayseri', 'code' => '38', 'region' => 'icanadolu'),
    'kirklareli' => array('name' => 'Kırklareli', 'code' => '39', 'region' => 'marmara'),
    'kirsehir' => array('name' => 'Kırşehir', 'code' => '40', 'region' => 'icanadolu'),
    'kocaeli' => array('name' => 'Kocaeli', 'code' => '41', 'region' => 'marmara'),
    'konya' => array('name' => 'Konya', 'code' => '42', 'region' => 'icanadolu'),
    'kutahya' => array('name' => 'Kütahya', 'code' => '43', 'region' => 'ege'),
    'malatya' => array('name' => 'Malatya', 'code' => '44', 'region' => 'dogu'),
    'manisa' => array('name' => 'Manisa', 'code' => '45', 'region' => 'ege'),
    'kahramanmaras' => array('name' => 'Kahramanmaraş', 'code' => '46', 'region' => 'akdeniz'),
    'mardin' => array('name' => 'Mardin', 'code' => '47', 'region' => 'guneydogu'),
    'mugla' => array('name' => 'Muğla', 'code' => '48', 'region' => 'ege'),
    'mus' => array('name' => 'Muş', 'code' => '49', 'region' => 'dogu'),
    'nevsehir' => array('name' => 'Nevşehir', 'code' => '50', 'region' => 'icanadolu'),
    'nigde' => array('name' => 'Niğde', 'code' => '51', 'region' => 'icanadolu'),
    'ordu' => array('name' => 'Ordu', 'code' => '52', 'region' => 'karadeniz'),
    'rize' => array('name' => 'Rize', 'code' => '53', 'region' => 'karadeniz'),
    'sakarya' => array('name' => 'Sakarya', 'code' => '54', 'region' => 'marmara'),
    'samsun' => array('name' => 'Samsun', 'code' => '55', 'region' => 'karadeniz'),
    'siirt' => array('name' => 'Siirt', 'code' => '56', 'region' => 'guneydogu'),
    'sinop' => array('name' => 'Sinop', 'code' => '57', 'region' => 'karadeniz'),
    'sivas' => array('name' => 'Sivas', 'code' => '58', 'region' => 'icanadolu'),
    'tekirdag' => array('name' => 'Tekirdağ', 'code' => '59', 'region' => 'marmara'),
    'tokat' => array('name' => 'Tokat', 'code' => '60', 'region' => 'karadeniz'),
    'trabzon' => array('name' => 'Trabzon', 'code' => '61', 'region' => 'karadeniz'),
    'tunceli' => array('name' => 'Tunceli', 'code' => '62', 'region' => 'dogu'),
    'sanliurfa' => array('name' => 'Şanlıurfa', 'code' => '63', 'region' => 'guneydogu'),
    'usak' => array('name' => 'Uşak', 'code' => '64', 'region' => 'ege'),
    'van' => array('name' => 'Van', 'code' => '65', 'region' => 'dogu'),
    'yozgat' => array('name' => 'Yozgat', 'code' => '66', 'region' => 'icanadolu'),
    'zonguldak' => array('name' => 'Zonguldak', 'code' => '67', 'region' => 'karadeniz'),
    'aksaray' => array('name' => 'Aksaray', 'code' => '68', 'region' => 'icanadolu'),
    'bayburt' => array('name' => 'Bayburt', 'code' => '69', 'region' => 'karadeniz'),
    'karaman' => array('name' => 'Karaman', 'code' => '70', 'region' => 'icanadolu'),
    'kirikkale' => array('name' => 'Kırıkkale', 'code' => '71', 'region' => 'icanadolu'),
    'batman' => array('name' => 'Batman', 'code' => '72', 'region' => 'guneydogu'),
    'sirnak' => array('name' => 'Şırnak', 'code' => '73', 'region' => 'guneydogu'),
    'bartin' => array('name' => 'Bartın', 'code' => '74', 'region' => 'karadeniz'),
    'ardahan' => array('name' => 'Ardahan', 'code' => '75', 'region' => 'dogu'),
    'igdir' => array('name' => 'Iğdır', 'code' => '76', 'region' => 'dogu'),
    'yalova' => array('name' => 'Yalova', 'code' => '77', 'region' => 'marmara'),
    'karabuk' => array('name' => 'Karabük', 'code' => '78', 'region' => 'karadeniz'),
    'kilis' => array('name' => 'Kilis', 'code' => '79', 'region' => 'guneydogu'),
    'osmaniye' => array('name' => 'Osmaniye', 'code' => '80', 'region' => 'akdeniz'),
    'duzce' => array('name' => 'Düzce', 'code' => '81', 'region' => 'karadeniz'),
);

// Veritabanındaki gerçek term sayılarını al
global $wpdb;
$counts = $wpdb->get_results("
    SELECT t.slug, COUNT(tr.object_id) as cnt
    FROM {$wpdb->terms} t
    JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id AND tt.taxonomy = 'mechanic_city'
    LEFT JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
    GROUP BY t.slug
", OBJECT_K);
?>

<style>
.iller-hero {
    background: linear-gradient(135deg, #0f172a, #1e293b);
    padding: 60px 0 50px 0;
    color: white;
    text-align: center;
}
.iller-search-input {
    width: 100%;
    max-width: 500px;
    padding: 14px 20px;
    font-size: 15px;
    border-radius: 30px;
    border: 1px solid #cbd5e1;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    outline: none;
    transition: all 0.2s;
}
.iller-search-input:focus {
    border-color: #f97316;
    box-shadow: 0 4px 20px rgba(249, 115, 22, 0.25);
}
.region-tabs {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 8px;
    margin: 25px 0 35px 0;
}
.region-btn {
    background: white;
    color: #475569;
    border: 1px solid #e2e8f0;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.region-btn.active, .region-btn:hover {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: white;
    border-color: #ea580c;
    box-shadow: 0 4px 12px rgba(249, 115, 22, 0.35);
}
.city-card-item {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}
.city-card-item:hover {
    border-color: #f97316;
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(249, 115, 22, 0.18);
}
.city-card-plate {
    background: #0f172a;
    color: white;
    font-weight: 800;
    font-size: 13px;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    margin-right: 12px;
}
.city-card-name {
    font-weight: 700;
    font-size: 15px;
    color: #1e293b;
    flex-grow: 1;
}
.city-card-count {
    background: #f1f5f9;
    color: #64748b;
    font-size: 12px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 12px;
}
.city-card-item:hover .city-card-count {
    background: #ffedd5;
    color: #ea580c;
}
</style>

<section class="iller-hero">
    <div class="container">
        <span style="display:inline-block; background:rgba(249,115,22,0.18); color:#fb923c; border:1px solid rgba(249,115,22,0.35); padding:5px 14px; border-radius:20px; font-size:12px; font-weight:700; margin-bottom:12px;">
            <i class="fa-solid fa-map-location-dot"></i> 81 İL REHBERİ
        </span>
        <h1 style="font-size: 36px; font-weight: 800; margin: 0 0 10px 0;">Türkiye Geneli Oto Tamircileri</h1>
        <p style="font-size: 16px; color: #94a3b8; max-width: 650px; margin: 0 auto 25px auto;">
            Aracınız için en yakın ve en güvenilir ustaları bulmak istediğiniz ili seçin
        </p>
        <div>
            <input type="text" id="citySearch" class="iller-search-input" placeholder="🔍 Şehir adı veya plaka no yazın... (Örn: Adana, 34, İzmir)">
        </div>
    </div>
</section>

<div class="container" style="padding: 40px 0 80px 0;">
    <!-- Bölge Filtreleme Sekmeleri -->
    <div class="region-tabs">
        <button class="region-btn active" data-region="all">Tümü (81 İl)</button>
        <button class="region-btn" data-region="marmara">Marmara</button>
        <button class="region-btn" data-region="ege">Ege</button>
        <button class="region-btn" data-region="akdeniz">Akdeniz</button>
        <button class="region-btn" data-region="icanadolu">İç Anadolu</button>
        <button class="region-btn" data-region="karadeniz">Karadeniz</button>
        <button class="region-btn" data-region="guneydogu">Güneydoğu Anadolu</button>
        <button class="region-btn" data-region="dogu">Doğu Anadolu</button>
    </div>

    <!-- 81 İl Grid -->
    <div id="cityGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px;">
        <?php foreach ( $city_regions as $slug => $data ) : 
            $c_count = isset($counts[$slug]) ? $counts[$slug]->cnt : 0;
        ?>
        <a href="<?php echo esc_url( home_url('/il/' . $slug . '/') ); ?>" class="city-card-item" data-region="<?php echo esc_attr($data['region']); ?>" data-name="<?php echo esc_attr(mb_strtolower($data['name'], 'UTF-8')); ?>" data-code="<?php echo esc_attr($data['code']); ?>">
            <div style="display:flex; align-items:center;">
                <span class="city-card-plate"><?php echo esc_html($data['code']); ?></span>
                <span class="city-card-name"><?php echo esc_html($data['name']); ?></span>
            </div>
            <span class="city-card-count"><?php echo number_format($c_count); ?> Usta</span>
        </a>
        <?php endforeach; ?>
    </div>

    <div id="noCityMatch" style="display:none; text-align:center; padding: 50px 0; color:#64748b;">
        <i class="fa-solid fa-magnifying-glass" style="font-size:32px; color:#cbd5e1; margin-bottom:10px; display:block;"></i>
        Aradığınız kriterlere uygun şehir bulunamadı.
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('citySearch');
    const cityItems = document.querySelectorAll('.city-card-item');
    const regionBtns = document.querySelectorAll('.region-btn');
    const noMatch = document.getElementById('noCityMatch');
    let currentRegion = 'all';

    function filterCities() {
        const query = searchInput.value.trim().toLowerCase();
        let visibleCount = 0;

        cityItems.forEach(item => {
            const name = item.getAttribute('data-name');
            const code = item.getAttribute('data-code');
            const region = item.getAttribute('data-region');

            const matchSearch = !query || name.includes(query) || code.includes(query);
            const matchRegion = currentRegion === 'all' || region === currentRegion;

            if (matchSearch && matchRegion) {
                item.style.display = 'flex';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        noMatch.style.display = visibleCount === 0 ? 'block' : 'none';
    }

    searchInput.addEventListener('input', filterCities);

    regionBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            regionBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentRegion = this.getAttribute('data-region');
            filterCities();
        });
    });
});
</script>

<?php get_footer(); ?>