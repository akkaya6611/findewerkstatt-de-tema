<?php get_header(); ?>

    <?php if ( have_posts() ) : the_post(); 
        if ( function_exists('ototamir_track_post_views') ) {
            ototamir_track_post_views( get_the_ID() );
        }
        $phone = get_post_meta( get_the_ID(), '_mechanic_phone', true );
        $address = get_post_meta( get_the_ID(), '_mechanic_address', true );
        $sunday = get_post_meta( get_the_ID(), '_mechanic_sunday', true );
        $road_assist = get_post_meta( get_the_ID(), '_mechanic_road_assist', true );
        $charging_net = get_post_meta( get_the_ID(), '_mechanic_charging_network', true );
        $is_charging = ! empty( $charging_net );
        
        $image_url = get_post_meta( get_the_ID(), '_custom_mechanic_image', true );
        $is_google_avatar = ( ! empty( $image_url ) && strpos( $image_url, 'googleusercontent.com' ) !== false );
        $hero_bg_img = ( ! empty( $image_url ) && ! $is_google_avatar ) 
            ? $image_url 
            : ( has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'medium_large' ) : 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?auto=format&fit=crop&w=1200&q=75' );
        $hero_bg_var = 'style="--hero-bg-img: url(' . esc_url( $hero_bg_img ) . ');"';

        $cities = wp_get_post_terms( get_the_ID(), 'mechanic_city' );
        $districts = wp_get_post_terms( get_the_ID(), 'mechanic_district' );
        $city = ( $cities && ! is_wp_error( $cities ) ) ? $cities[0]->name : '';
        $city_slug = ( $cities && ! is_wp_error( $cities ) ) ? $cities[0]->slug : '';
        $district = ( $districts && ! is_wp_error( $districts ) ) ? $districts[0]->name : '';
        $rating = get_mechanic_rating_data( get_the_ID() );

        // Ham post_content içindeki mükerrer ve ham başlıkları temizle
        $raw_content = get_the_content();
        $clean_about = '';
        $disclaimer_text = '';

        if ( ! empty( $raw_content ) ) {
            // İlan içindeki iletişim mailini hemen admin@ototamircibul.com.tr olarak normalize et
            $raw_content = str_ireplace( 's.akkaya0166@gmail.com', 'admin@ototamircibul.com.tr', $raw_content );

            if ( stripos( $raw_content, 'İletişim Bilgileri:' ) !== false || stripos( $raw_content, 'Neden Biz?' ) !== false || stripos( $raw_content, 'Bu sayfadaki bilgiler' ) !== false ) {
                $parts = preg_split('/<h5>\s*Not:\s*<\/h5>|(?=Bu sayfadaki bilgiler)/iu', $raw_content, 2);
                $main_part = $parts[0];
                if ( isset( $parts[1] ) ) {
                    $disclaimer_text = trim( strip_tags( $parts[1], '<a><br><b><strong>' ) );
                }

                // Kenar çubuğuyla çakışan ham İletişim Bilgileri bölümünü metinden temizle
                $main_part = preg_replace('/<h3>\s*İletişim Bilgileri:.*?<\/h3>.*?(?=<h5>|<b>İletişim|$)/isu', '', $main_part);
                $main_part = preg_replace('/^<p>\s*<b>\s*Hakkında:\s*<\/b>/iu', '<p>', $main_part);

                $clean_about = apply_filters( 'the_content', $main_part );
            } else {
                $clean_about = apply_filters( 'the_content', $raw_content );
            }
            $clean_about = str_ireplace( 's.akkaya0166@gmail.com', 'admin@ototamircibul.com.tr', $clean_about );
        }

        // Taksonomiler (Markalar ve Hizmetler)
        $service_terms = wp_get_post_terms( get_the_ID(), 'service_type' );
        $brand_terms = wp_get_post_terms( get_the_ID(), 'car_brand' );
        $service_names = ( $service_terms && ! is_wp_error( $service_terms ) ) ? wp_list_pluck( $service_terms, 'name' ) : array();
        $brand_names = ( $brand_terms && ! is_wp_error( $brand_terms ) ) ? wp_list_pluck( $brand_terms, 'name' ) : array();
        $knows_about = array_merge( $service_names, $brand_names );
        $maps_search_query = urlencode( get_the_title() . ' ' . $address . ' ' . $district . ' ' . $city );

        if ( ! $is_charging && ! empty( $service_terms ) && ! is_wp_error( $service_terms ) ) {
            $s_slugs = wp_list_pluck( $service_terms, 'slug' );
            if ( in_array( 'elektrikli-sarj-istasyonu', $s_slugs, true ) || in_array( 'sarj-istasyonu', $s_slugs, true ) ) {
                $is_charging = true;
            }
        }
    ?>
    
    <!-- Schema.org JSON-LD (AutoRepair, BreadcrumbList, FAQPage) otomatik olarak inc/seo-geo.php üzerinden wp_head içine basılmaktadır. -->
    
    <div class="single-hero has-bg-img" <?php echo $hero_bg_var; ?>>
        <div class="container" style="margin-bottom: 20px;">
            <!-- SEO Breadcrumbs -->
            <nav class="single-breadcrumbs" style="font-size: 13px; color: rgba(255,255,255,0.7); display:flex; align-items:center; flex-wrap:wrap; gap:6px;">
                <a href="<?php echo esc_url( home_url('/') ); ?>" style="color: rgba(255,255,255,0.85); text-decoration: none;"><i class="fa-solid fa-house" style="font-size:11px;"></i> Ana Sayfa</a>
                <span>/</span>
                <?php if ( $is_charging ) : ?>
                    <a href="<?php echo esc_url( home_url('/elektrikli-sarj-istasyonlari/') ); ?>" style="color: rgba(255,255,255,0.85); text-decoration: none;"><i class="fa-solid fa-charging-station" style="font-size:12px; margin-right:4px;"></i> Şarj İstasyonları</a>
                    <?php if ( $city ) : ?>
                        <span>/</span>
                        <a href="<?php echo esc_url( home_url('/elektrikli-sarj-istasyonlari/?city=' . $city_slug) ); ?>" style="color: rgba(255,255,255,0.85); text-decoration: none;"><?php echo esc_html($city); ?></a>
                    <?php endif; ?>
                    <?php if ( $district ) : ?>
                        <span>/</span>
                        <span style="color: rgba(255,255,255,0.85);"><?php echo esc_html($district); ?></span>
                    <?php endif; ?>
                <?php elseif ( $road_assist === 'Evet' || $road_assist === '1' ) : ?>
                    <a href="<?php echo esc_url( home_url('/acil-cekici/') ); ?>" style="color: rgba(255,255,255,0.85); text-decoration: none;">Acil Çekici & Yol Yardım</a>
                    <?php if ( $city ) : ?>
                        <span>/</span>
                        <a href="<?php echo esc_url( home_url('/acil-cekici/?city=' . $city_slug) ); ?>" style="color: rgba(255,255,255,0.85); text-decoration: none;"><?php echo esc_html($city); ?></a>
                    <?php endif; ?>
                    <?php if ( $district ) : ?>
                        <span>/</span>
                        <a href="<?php echo esc_url( home_url('/acil-cekici/?city=' . $city_slug . '&district=' . urlencode($district)) ); ?>" style="color: rgba(255,255,255,0.85); text-decoration: none;"><?php echo esc_html($district); ?></a>
                    <?php endif; ?>
                <?php else : ?>
                    <a href="<?php echo esc_url( home_url('/ustalar') ); ?>" style="color: rgba(255,255,255,0.85); text-decoration: none;">Ustalar</a>
                    <?php if ( $city ) : ?>
                        <span>/</span>
                        <a href="<?php echo esc_url( home_url('/ustalar/?mechanic_city=' . $city_slug) ); ?>" style="color: rgba(255,255,255,0.85); text-decoration: none;"><?php echo esc_html($city); ?></a>
                    <?php endif; ?>
                    <?php if ( $district ) : ?>
                        <span>/</span>
                        <a href="<?php echo esc_url( home_url('/ustalar/?mechanic_city=' . $city_slug . '&mechanic_district=' . urlencode($district)) ); ?>" style="color: rgba(255,255,255,0.85); text-decoration: none;"><?php echo esc_html($district); ?></a>
                    <?php endif; ?>
                <?php endif; ?>
                <span>/</span>
                <span style="color: #cbd5e1;"><?php the_title(); ?></span>
            </nav>
        </div>

        <div class="container mechanic-profile-wrap">
            <div class="mechanic-profile-img">
                <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>" width="120" height="120" fetchpriority="high" loading="eager" decoding="async" onerror="this.onerror=null;this.src='<?php echo esc_url( get_template_directory_uri() . '/assets/images/placeholder-mechanic.webp' ); ?>';">
            </div>

            <div>
                <?php
                $is_featured = get_post_meta( get_the_ID(), '_mechanic_is_featured', true );
                $badge_text = get_post_meta( get_the_ID(), '_mechanic_badge_text', true ) ?: 'Öne Çıkan VIP Usta';
                ?>
                <h1 style="display:flex; align-items:center; flex-wrap:wrap; gap:8px;">
                    <?php the_title(); ?> 
                    <i class="fa-solid fa-circle-check" title="Onaylı Usta" style="color: #10b981; font-size: 24px;"></i>
                    <?php if ( $is_featured === '1' ) : ?>
                        <span class="badge-featured-vip" style="display:inline-flex; align-items:center; gap:6px; background:linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color:white; font-size:13px; font-weight:700; padding:4px 12px; border-radius:20px; vertical-align:middle; box-shadow:0 4px 12px rgba(245,158,11,0.35);">
                            <i class="fa-solid fa-crown"></i> <?php echo esc_html( $badge_text ); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ( $charging_net ) : ?>
                        <span class="badge-charging-net" style="display:inline-flex; align-items:center; gap:6px; background:linear-gradient(135deg, #059669 0%, #10b981 100%); color:white; font-size:13px; font-weight:700; padding:4px 12px; border-radius:20px; vertical-align:middle; box-shadow:0 4px 12px rgba(16,185,129,0.35);">
                            <i class="fa-solid fa-bolt"></i> <?php echo esc_html( $charging_net ); ?> Şarj Ağı
                        </span>
                    <?php endif; ?>
                </h1>
                <div class="single-meta">
                    <?php if($rating['count'] > 0): ?>
                        <span><i class="fa-solid fa-star" style="color:#fbbf24;"></i> <?php echo $rating['avg']; ?> (<?php echo $rating['count']; ?> Değerlendirme)</span>
                    <?php else: ?>
                        <span><i class="fa-regular fa-star"></i> Henüz değerlendirilmedi</span>
                    <?php endif; ?>
                    <?php if ( $address ) : ?>
                        <span><i class="fa-solid fa-location-dot"></i> <?php echo wp_trim_words( esc_html( $address ), 8 ); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* Single Mechanic Sayfa İçi Geliştirmeleri */
    .single-box {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: var(--radius);
        padding: 32px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }
    .single-box-title {
        margin: 0 0 22px 0;
        font-size: 20px;
        font-weight: 700;
        color: #0f172a;
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .single-box-title i {
        color: #f97316;
        font-size: 19px;
    }
    .single-about-body {
        line-height: 1.8;
        color: #475569;
        font-size: 15px;
    }
    .single-about-body h3 {
        font-size: 17px;
        font-weight: 700;
        color: #0f172a;
        margin: 24px 0 10px 0;
    }
    .single-about-body h4 {
        font-size: 15px;
        font-weight: 600;
        color: #1e293b;
        margin: 18px 0 8px 0;
    }
    .single-about-body p {
        margin: 0 0 14px 0;
    }

    /* Hizmetler ve Markalar 2 Kolon Grid */
    .services-brand-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }
    .sub-service-col {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
    }
    .sub-service-title {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 14px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .sub-service-title i {
        color: #f97316;
    }
    .service-chip-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .service-chip-list li {
        background: #ffffff;
        color: #334155;
        padding: 7px 14px;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 600;
        border: 1px solid #cbd5e1;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .service-chip-list li:hover {
        border-color: #f97316;
        color: #ea580c;
        transform: translateY(-2px);
    }
    .service-chip-list li i {
        color: #f97316;
        font-size: 12px;
    }

    /* Kaynak / Sorumluluk Reddi Bildirimi */
    .mechanic-source-notice {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-left: 4px solid #f97316;
        border-radius: 8px;
        padding: 14px 18px;
        font-size: 13px;
        color: #9a3412;
        line-height: 1.6;
        margin-top: 15px;
    }
    .mechanic-source-notice i {
        margin-right: 6px;
        color: #ea580c;
    }

    /* Sticky Sidebar */
    .single-sidebar {
        position: sticky;
        top: 90px;
    }
    .btn-maps-route {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: #ffffff;
        color: #1e293b;
        border: 1.5px solid #cbd5e1;
        padding: 12px;
        text-align: center;
        border-radius: 8px;
        font-weight: 700;
        font-size: 14px;
        text-decoration: none;
        margin-bottom: 15px;
        transition: all 0.2s;
    }
    .btn-maps-route:hover {
        background: #f1f5f9;
        border-color: #f97316;
        color: #ea580c;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }

    /* Benzer Ustalar Widget */
    .similar-mechs-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .similar-mech-item {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        padding: 10px;
        border-radius: 8px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: all 0.2s;
    }
    .similar-mech-item:hover {
        border-color: #f97316;
        background: #ffffff;
        transform: translateX(3px);
    }
    .similar-mech-img {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        object-fit: cover;
        flex-shrink: 0;
    }
    .similar-mech-info h5 {
        margin: 0 0 4px 0;
        font-size: 13.5px;
        font-weight: 700;
        color: #0f172a;
    }
    .similar-mech-info span {
        font-size: 12px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    @media (max-width: 768px) {
        .single-hero {
            padding: 24px 16px 28px !important;
            background-color: #0f172a !important;
            background-image: linear-gradient(180deg, #0f172a 0%, #1e293b 100%) !important;
            color: #ffffff !important;
            text-align: center;
        }
        .single-breadcrumbs {
            justify-content: center !important;
            margin-bottom: 14px !important;
            font-size: 11.5px !important;
            gap: 4px !important;
            flex-wrap: wrap !important;
        }
        .single-breadcrumbs span:last-child,
        .single-breadcrumbs span:nth-last-child(2) {
            display: none;
        }
        .mechanic-profile-wrap {
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 12px !important;
            padding-bottom: 0 !important;
            text-align: center !important;
        }
        .mechanic-profile-img {
            width: 76px !important;
            height: 76px !important;
            border-width: 3px !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.3) !important;
            margin: 0 auto !important;
        }
        .single-hero h1 {
            font-size: 20px !important;
            line-height: 1.35 !important;
            justify-content: center !important;
            margin: 0 0 8px 0 !important;
            flex-wrap: wrap;
            color: #ffffff !important;
        }
        .single-hero h1 i {
            font-size: 18px !important;
            margin-left: 6px !important;
        }
        .single-meta {
            font-size: 12.5px !important;
            justify-content: center !important;
            gap: 8px 14px !important;
            flex-wrap: wrap !important;
        }
        .services-brand-grid {
            grid-template-columns: 1fr !important;
            gap: 16px !important;
        }
        .single-box {
            padding: 16px 14px !important;
            border-radius: 12px !important;
        }
        .single-box-title {
            font-size: 16px !important;
            margin-bottom: 14px !important;
        }
        main {
            padding: 16px 0 50px 0 !important;
        }
        /* ── MOBİL TAŞMA ENGELLEYİCİ (v1.4.70) ── */
        main,
        .single-container,
        .single-content,
        .single-sidebar,
        .single-box,
        .single-about-body,
        .services-brand-grid,
        .sub-service-col,
        .service-chip-list,
        .sidebar-widget,
        .container {
            max-width: 100% !important;
            width: 100% !important;
            box-sizing: border-box !important;
            overflow-wrap: break-word !important;
            word-break: break-word !important;
        }
        .single-content,
        .single-container,
        main {
            overflow-x: hidden !important;
        }
        /* Yorum formu, iletişim kutusu ve padding'ler */
        .sidebar-widget {
            padding: 16px 14px !important;
        }
        .contact-action {
            font-size: 14px !important;
            padding: 12px !important;
        }
        /* Tablo, harita iframe, pre blokları taşmasın */
        table { max-width: 100% !important; overflow-x: auto !important; display: block; }
        iframe { max-width: 100% !important; }
        pre, code { max-width: 100% !important; overflow-x: auto !important; white-space: pre-wrap !important; }
        img { max-width: 100% !important; height: auto !important; }
        /* Yorum formu input'ları taşmasın */
        .review-form input,
        .review-form textarea,
        .review-form select {
            max-width: 100% !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
    }
    </style>

    <main style="background: var(--bg-color); padding: 40px 0 80px 0;">
        <div class="container single-container" style="margin-top: 0;">
            
            <div class="single-content">
                
                <!-- 1. HAKKINDA -->
                <div class="single-box">
                    <h2 class="single-box-title"><i class="fa-solid fa-circle-info"></i> Firma Tanıtımı & Hakkında</h2>
                    <div class="single-about-body">
                        <?php 
                        if ( empty( $clean_about ) ) {
                            echo "<p>Bu usta için henüz detaylı bir açıklama girilmemiştir.</p>";
                        } else {
                            echo $clean_about; 
                        }
                        ?>
                    </div>

                    <?php if ( ! empty( $disclaimer_text ) ) : 
                        $disclaimer_text = str_ireplace( 's.akkaya0166@gmail.com', 'admin@ototamircibul.com.tr', $disclaimer_text );
                        if ( strpos( $disclaimer_text, 'mailto:' ) === false && strpos( $disclaimer_text, 'admin@ototamircibul.com.tr' ) !== false ) {
                            $disclaimer_text = str_replace(
                                'admin@ototamircibul.com.tr',
                                '<a href="mailto:admin@ototamircibul.com.tr" style="color:#ea580c; font-weight:700; text-decoration:underline;">admin@ototamircibul.com.tr</a>',
                                $disclaimer_text
                            );
                        }
                    ?>
                    <div class="mechanic-source-notice">
                        <i class="fa-solid fa-circle-info"></i> <?php echo $disclaimer_text; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- 2. HİZMETLER VE UZMANLIK ALANLARI (BİRLEŞİK & MODERN) -->
                <div class="single-box">
                    <h2 class="single-box-title"><i class="fa-solid fa-wrench"></i> Hizmetler ve Uzmanlık Alanları</h2>
                    
                    <div class="services-brand-grid">
                        <!-- Verilen Hizmetler -->
                        <div class="sub-service-col">
                            <h3 class="sub-service-title"><i class="fa-solid fa-gears"></i> Verilen Hizmet Türleri</h3>
                            <ul class="service-chip-list">
                                <?php 
                                $services = get_the_terms( get_the_ID(), 'service_type' );
                                if ( $services && ! is_wp_error( $services ) ) {
                                    foreach ( $services as $service ) {
                                        echo '<li><i class="fa-solid fa-check"></i> ' . esc_html( $service->name ) . '</li>';
                                    }
                                } else {
                                    echo '<li><i class="fa-solid fa-check"></i> Genel Oto Tamir</li>';
                                }
                                ?>
                            </ul>
                        </div>

                        <!-- Uzmanlık (Araç Markaları) -->
                        <div class="sub-service-col">
                            <h3 class="sub-service-title"><i class="fa-solid fa-car-side"></i> Hizmet Verilen Markalar</h3>
                            <ul class="service-chip-list">
                                <?php 
                                $brands = get_the_terms( get_the_ID(), 'car_brand' );
                                if ( $brands && ! is_wp_error( $brands ) ) {
                                    foreach ( $brands as $brand ) {
                                        echo '<li><i class="fa-solid fa-car"></i> ' . esc_html( $brand->name ) . '</li>';
                                    }
                                } else {
                                    echo '<li><i class="fa-solid fa-car"></i> Tüm Araç Markaları</li>';
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- AI ARIZA TESPİTİ BANNER -->
                <div class="ai-diagnosis-banner">
                    <div class="ai-banner-left">
                        <div class="ai-banner-icon">
                            <i class="fa-solid fa-robot"></i>
                        </div>
                        <div class="ai-banner-text">
                            <strong>Ustaya gitmeden önce arızanı öğren!</strong>
                            <span>Yapay zeka ile aracının arızasını saniyeler içinde tespit et. Ustanıza hazırlıklı gidin, daha az ödeyin.</span>
                        </div>
                    </div>
                    <a href="<?php echo esc_url( home_url('/ariza-tespiti/') ); ?>" class="ai-banner-btn">
                        <i class="fa-solid fa-microchip"></i> Ücretsiz Arıza Tespiti
                    </a>
                </div>
                <style>
                .ai-diagnosis-banner {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 20px;
                    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%);
                    border-radius: 16px;
                    padding: 22px 28px;
                    border: 1px solid rgba(99,179,237,0.2);
                    box-shadow: 0 8px 32px rgba(15,23,42,0.25), inset 0 1px 0 rgba(255,255,255,0.05);
                    position: relative;
                    overflow: hidden;
                }
                .ai-diagnosis-banner::before {
                    content: '';
                    position: absolute;
                    top: -40px; right: -40px;
                    width: 180px; height: 180px;
                    background: radial-gradient(circle, rgba(249,115,22,0.15) 0%, transparent 70%);
                    pointer-events: none;
                }
                .ai-diagnosis-banner::after {
                    content: '';
                    position: absolute;
                    bottom: -30px; left: 40px;
                    width: 120px; height: 120px;
                    background: radial-gradient(circle, rgba(59,130,246,0.1) 0%, transparent 70%);
                    pointer-events: none;
                }
                .ai-banner-left {
                    display: flex;
                    align-items: center;
                    gap: 18px;
                    flex: 1;
                    min-width: 0;
                }
                .ai-banner-icon {
                    width: 52px;
                    height: 52px;
                    border-radius: 14px;
                    background: linear-gradient(135deg, #f97316, #ea580c);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex-shrink: 0;
                    box-shadow: 0 4px 16px rgba(249,115,22,0.4);
                }
                .ai-banner-icon i {
                    font-size: 24px;
                    color: #ffffff;
                }
                .ai-banner-text {
                    display: flex;
                    flex-direction: column;
                    gap: 4px;
                }
                .ai-banner-text strong {
                    color: #ffffff;
                    font-size: 16px;
                    font-weight: 700;
                    line-height: 1.3;
                }
                .ai-banner-text span {
                    color: rgba(203,213,225,0.9);
                    font-size: 13.5px;
                    line-height: 1.5;
                }
                .ai-banner-btn {
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
                    color: #ffffff !important;
                    font-size: 14px;
                    font-weight: 700;
                    padding: 12px 22px;
                    border-radius: 10px;
                    text-decoration: none !important;
                    white-space: nowrap;
                    flex-shrink: 0;
                    box-shadow: 0 4px 16px rgba(249,115,22,0.45);
                    transition: all 0.2s ease;
                }
                .ai-banner-btn:hover {
                    background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
                    transform: translateY(-2px);
                    box-shadow: 0 6px 20px rgba(249,115,22,0.55);
                }
                @media (max-width: 768px) {
                    .ai-diagnosis-banner {
                        flex-direction: column !important;
                        align-items: flex-start !important;
                        padding: 18px 16px !important;
                        gap: 16px !important;
                    }
                    .ai-banner-btn {
                        width: 100% !important;
                        justify-content: center !important;
                    }
                    .ai-banner-text strong { font-size: 15px !important; }
                    .ai-banner-text span { font-size: 13px !important; }
                }
                </style>

                <?php 
                // Google AdSense Usta Detayı İçerik Altı Reklamı
                if ( function_exists( 'ototamir_render_ad' ) ) {
                    echo ototamir_render_ad( 'single_content' );
                }
                ?>

                <!-- 3. MÜŞTERİ YORUMLARI & DEĞERLENDİRME FORMU -->
                <div class="single-box" id="yorumlar">
                    <h2 class="single-box-title"><i class="fa-solid fa-comments"></i> Müşteri Yorumları & Değerlendirmeler</h2>
                    <div style="display: flex; flex-direction: column; gap: 30px;">
                        <div>
                            <?php
                            $comments = get_comments(array('post_id' => get_the_ID(), 'status' => 'approve'));
                            if($comments) {
                                echo '<ul style="list-style:none; padding:0; margin:0;">';
                                foreach($comments as $comment) {
                                    $c_rating = get_comment_meta($comment->comment_ID, 'mechanic_rating', true);
                                    $is_verified = get_comment_meta($comment->comment_ID, '_is_verified_customer', true);
                                    $proof_id = get_comment_meta($comment->comment_ID, '_comment_proof_id', true);
                                    echo '<li style="border-bottom: 1px solid var(--border-color); padding: 15px 0;">';
                                    echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap:wrap; gap:6px;">';
                                    echo '<div style="display:inline-flex; align-items:center; flex-wrap:wrap; gap:6px;">';
                                    echo '<strong style="color:var(--text-main); font-size:15px;">' . esc_html($comment->comment_author) . '</strong>';
                                    if ( $is_verified === '1' ) {
                                        echo '<span style="background:#ecfdf5; color:#059669; border:1px solid #a7f3d0; padding:2px 8px; border-radius:12px; font-size:11.5px; font-weight:700; display:inline-flex; align-items:center; gap:4px;" title="Bu değerlendirme tamir fişi veya servis faturası ile onaylanmıştır."><i class="fa-solid fa-circle-check"></i> Doğrulanmış Müşteri</span>';
                                    }
                                    echo '</div>';
                                    if($c_rating) {
                                        echo '<span style="color:#fbbf24; font-size:13px;">';
                                        for($i=1; $i<=5; $i++) { echo $i <= $c_rating ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>'; }
                                        echo '</span>';
                                    }
                                    echo '</div>';
                                    echo '<p style="color:var(--text-muted); font-size:14px; margin:0; line-height:1.6;">' . esc_html($comment->comment_content) . '</p>';
                                    if ( $proof_id ) {
                                        $proof_url = wp_get_attachment_url( $proof_id );
                                        if ( $proof_url ) {
                                            echo '<div style="margin-top:8px;"><a href="' . esc_url($proof_url) . '" target="_blank" rel="noopener" style="display:inline-flex; align-items:center; gap:6px; font-size:11.5px; font-weight:600; color:#0284c7; background:#f0f9ff; border:1px solid #bae6fd; padding:3px 10px; border-radius:6px; text-decoration:none;"><i class="fa-solid fa-receipt"></i> İş Emri / Servis Fişi Ekini Gör</a></div>';
                                        }
                                    }
                                    echo '<span style="font-size:12px; color:#aaa; display:block; margin-top:8px;">' . date('d.m.Y', strtotime($comment->comment_date)) . '</span>';
                                    echo '</li>';
                                }
                                echo '</ul>';
                            } else {
                                echo '<p style="color:var(--text-muted); font-size:14px; padding: 10px 0;">Bu usta için henüz yorum yapılmamış. İlk değerlendiren siz olun!</p>';
                            }
                            ?>
                        </div>

                        <div style="background: #f8fafc; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0;">
                            <h4 style="margin:0 0 15px 0; font-size:16px; font-weight:700; color:#0f172a;">Değerlendirme Yazın</h4>
                            <form action="<?php echo site_url('/wp-comments-post.php'); ?>" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="comment_post_ID" value="<?php echo get_the_ID(); ?>">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                                    <div class="form-group" style="margin:0;">
                                        <label style="display:block; margin-bottom:6px; font-weight:600; font-size:13px; color:#334155;">Puanınız</label>
                                        <select name="mechanic_rating" class="form-control" required style="padding: 10px 12px; width:100%; border:1px solid #cbd5e1; border-radius:8px; background:white; font-size:14px;">
                                            <option value="5">⭐⭐⭐⭐⭐ 5 - Mükemmel</option>
                                            <option value="4">⭐⭐⭐⭐ 4 - Çok İyi</option>
                                            <option value="3">⭐⭐⭐ 3 - İdare Eder</option>
                                            <option value="2">⭐⭐ 2 - Kötü</option>
                                            <option value="1">⭐ 1 - Çok Kötü</option>
                                        </select>
                                    </div>
                                    <div class="form-group" style="margin:0;">
                                        <label style="display:block; margin-bottom:6px; font-weight:600; font-size:13px; color:#334155;">Adınız Soyadınız</label>
                                        <input type="text" name="author" class="form-control" required placeholder="Adınız Soyadınız" style="padding: 10px 12px; width:100%; border:1px solid #cbd5e1; border-radius:8px; background:white; font-size:14px;">
                                    </div>
                                </div>
                                <div class="form-group" style="margin-bottom: 15px;">
                                    <label style="display:block; margin-bottom:6px; font-weight:600; font-size:13px; color:#334155;">Yorumunuz & Tecrübeniz</label>
                                    <textarea name="comment" rows="3" class="form-control" required placeholder="Ustanın işçiliği, fiyatı ve ilgisi nasıldı?..." style="padding: 10px 12px; width:100%; border:1px solid #cbd5e1; border-radius:8px; resize:vertical; background:white; font-size:14px; font-family:inherit;"></textarea>
                                </div>
                                <div class="form-group" style="margin-bottom: 18px;">
                                    <label style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; font-weight:600; font-size:13px; color:#334155;">
                                        <span><i class="fa-solid fa-file-invoice-dollar" style="color:#10b981;"></i> Fatura / İş Emri / Fiş Yükle <small style="color:#64748b; font-weight:normal;">(İsteğe bağlı)</small></span>
                                        <span style="background:#ecfdf5; color:#059669; font-size:11px; padding:2px 8px; border-radius:10px; font-weight:700;"><i class="fa-solid fa-shield-check"></i> Doğrulanmış Rozet</span>
                                    </label>
                                    <input type="file" name="comment_proof" accept="image/*,application/pdf" style="padding: 8px 12px; width:100%; border:1px dashed #cbd5e1; border-radius:8px; background:white; font-size:13px; color:#475569;">
                                    <small style="display:block; font-size:11.5px; color:#64748b; margin-top:4px;">Tamir fişi veya faturası yüklediğinizde yorumunuzda <strong>"✅ Doğrulanmış Müşteri"</strong> güven rozeti çıkar.</small>
                                </div>
                                <button type="submit" class="btn-primary" style="width:100%; border:none; padding:13px; cursor:pointer; font-size:15px; background:linear-gradient(135deg, #f97316 0%, #ea580c 100%); color:white; border-radius:8px; font-weight:700; box-shadow:0 4px 12px rgba(249,115,22,0.35);">
                                    <i class="fa-solid fa-paper-plane"></i> Değerlendirmeyi Gönder
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
            </div>

            <!-- STICKY SAĞ PANEL / SIDEBAR -->
            <div class="single-sidebar">
                
                <!-- İLETİŞİM & YOL TARİFİ (DEUTSCH) -->
                <div class="sidebar-widget">
                    <h3 style="margin:0 0 16px 0; font-size:17px; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:10px;">
                        <i class="fa-solid fa-address-card" style="color:#f97316;"></i> Kontakt & Anfahrt
                    </h3>
                    
                    <?php if ( $phone && $phone !== '+49' ) : ?>
                        <a href="tel:<?php echo esc_attr( $phone ); ?>" class="contact-action btn-call" data-track-action="call" data-post-id="<?php echo get_the_ID(); ?>" style="display:flex; align-items:center; justify-content:center; gap:8px; background:linear-gradient(135deg, #f97316 0%, #ea580c 100%); color:white; padding:13px; text-align:center; border-radius:8px; font-weight:700; margin-bottom:10px; text-decoration:none; box-shadow: 0 4px 12px rgba(249,115,22,0.35);">
                            <i class="fa-solid fa-phone"></i> Jetzt anrufen
                        </a>
                        
                        <?php 
                        $wp_number = preg_replace('/[^0-9]/', '', $phone);
                        if ( substr($wp_number, 0, 1) === '0' ) {
                            $wp_number = '49' . substr($wp_number, 1);
                        } elseif ( substr($wp_number, 0, 2) !== '49' && strlen($wp_number) <= 11 ) {
                            $wp_number = '49' . $wp_number;
                        }

                        $service_terms = wp_get_post_terms( get_the_ID(), 'service_type', array('fields' => 'names') );
                        $primary_service = (!empty($service_terms) && !is_wp_error($service_terms)) ? $service_terms[0] : 'Kfz-Reparatur & Service';

                        $service_slugs = wp_get_post_terms( get_the_ID(), 'service_type', array('fields' => 'slugs') );
                        $is_tow_or_emergency = ( in_array('abschleppdienst-pannenhilfe', (array)$service_slugs) || in_array('oto-cekici-yol-yardim', (array)$service_slugs) || in_array($road_assist, array('Evet','yes','1','Ja')) );

                        $smart_wp_text = urlencode( "Hallo " . get_the_title() . ", ich habe Ihr Profil auf Findewerkstatt.de gefunden und möchte mich bezüglich " . $primary_service . " erkundigen." );
                        ?>
                        <a href="https://wa.me/<?php echo $wp_number; ?>?text=<?php echo $smart_wp_text; ?>" target="_blank" class="contact-action btn-whatsapp" data-track-action="whatsapp" data-post-id="<?php echo get_the_ID(); ?>" style="display:flex; align-items:center; justify-content:center; gap:8px; background:#10b981; color:white; padding:13px; text-align:center; border-radius:8px; font-weight:700; margin-bottom:10px; text-decoration:none; transition:all 0.2s;">
                            <i class="fa-brands fa-whatsapp" style="font-size:18px;"></i> Per WhatsApp anfragen
                        </a>

                        <?php if ( $is_tow_or_emergency ) : ?>
                            <button type="button" id="btn-share-location-wp" class="contact-action btn-location-wp" data-track-action="location_wp" data-post-id="<?php echo get_the_ID(); ?>" style="display:flex; align-items:center; justify-content:center; gap:8px; background:linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color:white; padding:13px; text-align:center; border-radius:8px; font-weight:700; margin-bottom:12px; border:none; cursor:pointer; width:100%; box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35); font-family:inherit; transition:all 0.2s;">
                                <i class="fa-solid fa-location-crosshairs" style="font-size:16px;"></i> 📍 Standort senden (Pannenhilfe)
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <div style="background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; font-size:13px; padding:10px; border-radius:6px; margin-bottom:12px; text-align:center;">
                            <i class="fa-solid fa-phone-slash"></i> Keine Telefonnummer hinterlegt.
                        </div>
                    <?php endif; ?>

                    <!-- GOOGLE MAPS ROUTENPLANER -->
                    <?php 
                    $maps_query = $address ? ($address . ', ' . $city . ', Deutschland') : (get_the_title() . ', ' . $city . ', Deutschland');
                    ?>
                    <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo urlencode($maps_query); ?>" target="_blank" class="btn-maps-route" data-track-action="directions" data-post-id="<?php echo get_the_ID(); ?>">
                        <i class="fa-solid fa-diamond-turn-right" style="color:#f97316;"></i> Route auf Google Maps planen
                    </a>

                    <ul class="contact-info-list" style="list-style:none; padding:0; margin:0;">
                        <?php if ( $phone && $phone !== '+49' ) : ?>
                        <li style="display:flex; gap:12px; margin-bottom:14px; padding-bottom:14px; border-bottom:1px solid #f1f5f9;">
                            <i class="fa-solid fa-phone-volume" style="color:#f97316; font-size:18px; margin-top:2px;"></i>
                            <div>
                                <strong style="font-size:12.5px; color:#64748b; display:block;">Telefonnummer</strong>
                                <a href="tel:<?php echo esc_attr( $phone ); ?>" data-track-action="call" data-post-id="<?php echo get_the_ID(); ?>" style="color:#0f172a; font-weight:700; font-size:15px; text-decoration:none;"><?php echo esc_html( $phone ); ?></a>
                            </div>
                        </li>
                        <?php endif; ?>
                        
                        <?php if ( $address ) : ?>
                        <li style="display:flex; gap:12px; cursor:pointer; padding:8px; border-radius:8px; transition:background 0.2s;" onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='transparent';" onclick="window.open('https://www.google.com/maps/dir/?api=1&destination=<?php echo urlencode($maps_query); ?>', '_blank');" data-track-action="directions" data-post-id="<?php echo get_the_ID(); ?>" title="Auf Google Maps öffnen & Route berechnen">
                            <i class="fa-solid fa-map-location-dot" style="color:#f97316; font-size:18px; margin-top:2px;"></i>
                            <div>
                                <strong style="font-size:12.5px; color:#64748b; display:flex; align-items:center; gap:6px;">
                                    Adresse <span style="font-size:11px; color:#0284c7; font-weight:600;"><i class="fa-solid fa-arrow-up-right-from-square"></i> Karte öffnen</span>
                                </strong>
                                <span style="color:#334155; font-size:14px; line-height:1.5; display:block;"><?php echo nl2br( esc_html( $address ) ); ?></span>
                            </div>
                        </li>
                        <?php endif; ?>
                    </ul>

                    <!-- İLAN HAKKINDA CANLI DESTEK / YÖNETİCİYE MESAJ GÖNDER -->
                    <?php 
                    $support_link = is_user_logged_in() 
                        ? add_query_arg( array( 'tab' => 'destek', 'listing_id' => get_the_ID() ), home_url( '/profil/' ) )
                        : add_query_arg( 'redirect_to', urlencode( add_query_arg( array( 'tab' => 'destek', 'listing_id' => get_the_ID() ), home_url( '/profil/' ) ) ), home_url( '/giris-yap/' ) );
                    ?>
                    <div style="margin-top:16px; padding-top:16px; border-top:1px dashed #e2e8f0; text-align:center;">
                        <a href="<?php echo esc_url( $support_link ); ?>" style="display:flex; align-items:center; justify-content:center; gap:8px; background:#f8fafc; color:#334155; border:1px solid #cbd5e1; padding:11px 14px; border-radius:10px; font-weight:700; font-size:13px; text-decoration:none; transition:all 0.2s;" onmouseover="this.style.background='#0f172a';this.style.color='white';this.style.borderColor='#0f172a';" onmouseout="this.style.background='#f8fafc';this.style.color='#334155';this.style.borderColor='#cbd5e1';">
                            <i class="fa-solid fa-headset" style="color:#ea580c; font-size:15px;"></i>
                            <span>İlanla İlgili Yöneticiye Mesaj Gönder</span>
                        </a>
                        <small style="display:block; font-size:11.5px; color:#94a3b8; margin-top:6px;">İlan sahibiyseniz veya düzenleme/destek istiyorsanız canlı mesaj atabilirsiniz.</small>
                    </div>
                </div>
                
                <!-- ÇALIŞMA SAATLERİ & KOŞULLARI -->
                <div class="sidebar-widget">
                    <h3 style="margin:0 0 15px 0; font-size:16px; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:10px;">
                        <i class="fa-solid fa-clock" style="color:#f97316;"></i> Çalışma Koşulları
                    </h3>
                    
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; margin-bottom:10px;">
                        <span style="font-weight:600; font-size:13.5px; color:#334155;">Pazar Günü Açık</span>
                        <?php if ( $sunday == 'yes' ) : ?>
                            <span style="color:#10b981; font-weight:700; font-size:13px; display:flex; align-items:center; gap:4px;"><i class="fa-solid fa-circle-check"></i> Evet (Açık)</span>
                        <?php else: ?>
                            <span style="color:#94a3b8; font-weight:600; font-size:13px; display:flex; align-items:center; gap:4px;"><i class="fa-solid fa-circle-xmark"></i> Kapalı</span>
                        <?php endif; ?>
                    </div>
                    
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; margin-bottom:16px;">
                        <span style="font-weight:600; font-size:13.5px; color:#334155;">7/24 Acil Yol Yardım</span>
                        <?php if ( $road_assist == 'yes' ) : ?>
                            <span style="color:#10b981; font-weight:700; font-size:13px; display:flex; align-items:center; gap:4px;"><i class="fa-solid fa-circle-check"></i> Var (7/24)</span>
                        <?php else: ?>
                            <span style="color:#94a3b8; font-weight:600; font-size:13px; display:flex; align-items:center; gap:4px;"><i class="fa-solid fa-circle-xmark"></i> Yok</span>
                        <?php endif; ?>
                    </div>

                    <!-- GÜVEN ROZETLERİ -->
                    <div style="padding-top:12px; border-top:1px dashed #e2e8f0; display:flex; flex-direction:column; gap:8px; font-size:12.5px; color:#64748b;">
                        <div style="display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-shield-check" style="color:#10b981;"></i> Onaylı Oto Tamirci Profili</div>
                        <div style="display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-handshake-simple" style="color:#f97316;"></i> Aracı Yok, 0 Komisyon</div>
                    </div>
                </div>

                <!-- ŞEHİRDEKİ DİĞER USTALAR -->
                <?php if ( $city_slug ) : 
                    $similar_mechs = new WP_Query(array(
                        'post_type'      => 'mechanic',
                        'posts_per_page' => 3,
                        'post__not_in'   => array( get_the_ID() ),
                        'tax_query'      => array(
                            array(
                                'taxonomy' => 'mechanic_city',
                                'field'    => 'slug',
                                'terms'    => $city_slug
                            )
                        )
                    ));
                    if ( $similar_mechs->have_posts() ) :
                ?>
                <div class="sidebar-widget">
                    <h3 style="margin:0 0 14px 0; font-size:15px; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:10px;">
                        <i class="fa-solid fa-location-arrow" style="color:#f97316;"></i> <?php echo esc_html($city); ?> Bölgesindeki Diğer Ustalar
                    </h3>
                    <div class="similar-mechs-list">
                        <?php while ( $similar_mechs->have_posts() ) : $similar_mechs->the_post(); 
                            $sim_img = get_post_meta( get_the_ID(), '_custom_mechanic_image', true );
                            if ( empty( $sim_img ) ) {
                                $sim_img = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ) : 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?auto=format&fit=crop&w=150&q=80';
                            }
                            $sim_rating = get_mechanic_rating_data( get_the_ID() );
                        ?>
                        <a href="<?php the_permalink(); ?>" class="similar-mech-item">
                            <img src="<?php echo esc_url($sim_img); ?>" alt="<?php the_title_attribute(); ?>" class="similar-mech-img" onerror="this.onerror=null;this.src='<?php echo esc_url( get_template_directory_uri() . '/assets/images/placeholder-mechanic.webp' ); ?>';">
                            <div class="similar-mech-info">
                                <h5><?php the_title(); ?></h5>
                                <span><i class="fa-solid fa-star" style="color:#fbbf24;"></i> <?php echo $sim_rating['avg'] ?: '5.0'; ?> Puan</span>
                            </div>
                        </a>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                </div>
                <?php endif; endif; ?>

                <?php 
                // Google AdSense Usta Detayı Yan Panel (Sidebar) Reklamı
                if ( function_exists( 'ototamir_render_ad' ) ) {
                    echo ototamir_render_ad( 'single_sidebar' );
                }
                ?>

            </div>
        </div>
    </main>

    <?php endif; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var locBtn = document.getElementById('btn-share-location-wp');
        if (locBtn) {
            locBtn.addEventListener('click', function() {
                var btn = this;
                var origHtml = btn.innerHTML;
                if (!navigator.geolocation) {
                    alert('Tarayıcınız konum paylaşımını desteklemiyor.');
                    return;
                }
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Konum Alınıyor...';
                btn.disabled = true;

                navigator.geolocation.getCurrentPosition(
                    function(pos) {
                        var lat = pos.coords.latitude;
                        var lng = pos.coords.longitude;
                        var mapsUrl = 'https://maps.google.com/?q=' + lat + ',' + lng;
                        var msg = encodeURIComponent('🚨 ACİL YOL YARDIM / ÇEKİCİ TALEBİ\n\nMerhaba <?php echo esc_js( get_the_title() ); ?>, aracım arızalandı ve yolda kaldım. Güncel canlı konumum:\n' + mapsUrl + '\n\nEn kısa sürede yardımcı olabilir misiniz?');
                        var wpUrl = 'https://wa.me/<?php echo esc_js( $wp_number ); ?>?text=' + msg;
                        btn.innerHTML = origHtml;
                        btn.disabled = false;
                        window.open(wpUrl, '_blank');
                    },
                    function(err) {
                        btn.innerHTML = origHtml;
                        btn.disabled = false;
                        alert('Konumunuz alınamadı. Lütfen cihazınızda konum servislerinin ve tarayıcı izninin açık olduğundan emin olun.');
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            });
        }
    });
    </script>

<?php get_footer(); ?>
