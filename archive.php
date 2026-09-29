<?php get_header(); ?>

    <div class="page-header-small">
        <div class="container">
            <?php
            $queried_object = get_queried_object();
            $city_name = '';
            $city_slug = '';
            $dist_name = '';
            $service_name = '';
            $brand_name = '';
            $brand_slug = '';
            $model_name = '';
            $model_slug = '';
            
            if ( is_tax() && $queried_object ) {
                $t_name = $queried_object->name;
                $tax = $queried_object->taxonomy;
                if ( $tax === 'mechanic_city' ) { $city_name = $t_name; $city_slug = $queried_object->slug; }
                if ( $tax === 'mechanic_district' ) $dist_name = $t_name;
                if ( $tax === 'service_type' ) $service_name = $t_name;
                if ( $tax === 'car_brand' ) {
                    if ( ! empty( $queried_object->parent ) ) {
                        $model_name = $t_name;
                        $model_slug = $queried_object->slug;
                        $parent_b = get_term( $queried_object->parent, 'car_brand' );
                        if ( $parent_b && ! is_wp_error( $parent_b ) ) {
                            $brand_name = $parent_b->name;
                            $brand_slug = $parent_b->slug;
                        }
                    } else {
                        $brand_name = $t_name;
                        $brand_slug = $queried_object->slug;
                    }
                }
            }
            
            if ( isset($_GET['mechanic_city']) && !empty($_GET['mechanic_city']) ) {
                $c_term = get_term_by('slug', sanitize_text_field($_GET['mechanic_city']), 'mechanic_city');
                if ($c_term) { $city_name = $c_term->name; $city_slug = $c_term->slug; }
            }
            // Eski ?city= parametresini de destekle (smart-404-redirect ve dış linkler için)
            if ( empty($city_slug) && isset($_GET['city']) && !empty($_GET['city']) ) {
                $c_term = get_term_by('slug', sanitize_text_field($_GET['city']), 'mechanic_city');
                if ($c_term) { $city_name = $c_term->name; $city_slug = $c_term->slug; }
            }
            if ( isset($_GET['mechanic_district']) && !empty($_GET['mechanic_district']) ) {
                $dist_name = sanitize_text_field($_GET['mechanic_district']);
            }
            if ( isset($_GET['service_type']) && !empty($_GET['service_type']) ) {
                $s_term = get_term_by('slug', sanitize_text_field($_GET['service_type']), 'service_type');
                if ($s_term) $service_name = $s_term->name;
            }
            if ( isset($_GET['car_model']) && !empty($_GET['car_model']) ) {
                $m_term = get_term_by('slug', sanitize_text_field($_GET['car_model']), 'car_brand');
                if ($m_term) {
                    $model_name = $m_term->name;
                    $model_slug = $m_term->slug;
                    if ( empty($brand_name) && !empty($m_term->parent) ) {
                        $parent_b = get_term($m_term->parent, 'car_brand');
                        if ($parent_b && !is_wp_error($parent_b)) {
                            $brand_name = $parent_b->name;
                            $brand_slug = $parent_b->slug;
                        }
                    }
                }
            }
            if ( isset($_GET['car_brand']) && !empty($_GET['car_brand']) ) {
                $b_term = get_term_by('slug', sanitize_text_field($_GET['car_brand']), 'car_brand');
                if ($b_term) {
                    if ( !empty($b_term->parent) ) {
                        $model_name = $b_term->name;
                        $model_slug = $b_term->slug;
                        $parent_b = get_term($b_term->parent, 'car_brand');
                        if ($parent_b && !is_wp_error($parent_b)) {
                            $brand_name = $parent_b->name;
                            $brand_slug = $parent_b->slug;
                        }
                    } else {
                        $brand_name = $b_term->name;
                        $brand_slug = $b_term->slug;
                    }
                }
            }

            // Build dynamic SEO Title & Subtitle
            $loc_prefix = '';
            if ( $city_name && $dist_name ) {
                $loc_prefix = "{$city_name} {$dist_name} ";
            } elseif ( $city_name ) {
                $loc_prefix = "{$city_name} ";
            }

            if ( $brand_name && $model_name ) {
                if ( $service_name ) {
                    $title = "Size En Yakın {$loc_prefix}{$brand_name} {$model_name} {$service_name} Ustaları & Servisleri";
                    $desc  = "{$loc_prefix}{$brand_name} {$model_name} aracınız için garantili {$service_name} hizmeti veren en yakın uzman oto tamircileri ve özel servisler.";
                } else {
                    $title = "Size En Yakın {$loc_prefix}{$brand_name} {$model_name} Oto Tamircileri";
                    $desc  = "{$brand_name} {$model_name} aracınız için {$loc_prefix}bölgesinde en yakın onaylı oto tamir servisleri, mekanik ustalar ve periyodik bakım fiyatları.";
                }
            } elseif ( $brand_name ) {
                if ( $service_name ) {
                    $title = "Size En Yakın {$loc_prefix}{$brand_name} {$service_name} Servisleri & Ustaları";
                    $desc  = "{$brand_name} marka araçlara özel {$service_name} konusunda deneyimli en iyi oto servisleri ve usta yorumları.";
                } else {
                    $title = "Size En Yakın {$loc_prefix}{$brand_name} Oto Tamircileri & Özel Servisleri";
                    $desc  = "{$brand_name} aracınız için {$loc_prefix}en yakın profesyonel oto tamircileri, arıza tespit ve bakım rehberi.";
                }
            } elseif ( $city_name && $dist_name && $service_name ) {
                $title = "{$city_name} {$dist_name} {$service_name} Ustaları & Servisleri";
                $desc = "{$city_name} {$dist_name} bölgesindeki uzman {$service_name} ustaları, özel servisleri, müşteri yorumları ve iletişim bilgileri.";
            } elseif ( $city_name && $dist_name ) {
                $title = "{$city_name} {$dist_name} En Yakın Oto Tamircileri & Tamir Servisleri";
                $desc = "{$city_name} {$dist_name} bölgesinde hizmet veren onaylı oto tamircileri, motor, elektrik ve bakım ustaları.";
            } elseif ( $city_name ) {
                $title = "{$city_name} En Yakın Oto Tamircileri & Özel Servisler (2026)";
                $desc = "{$city_name} genelindeki güvenilir oto tamircileri, 7/24 yol yardım ve periyodik bakım servisleri.";
            } elseif ( $service_name ) {
                $title = "{$service_name} Ustaları & Güvenilir Servisler";
                $desc = "Türkiye genelinde en uzman {$service_name} ustalarını, servis fiyatlarını ve müşteri değerlendirmelerini inceleyin.";
            } else {
                $title = "Türkiye Geneli Oto Tamircileri & Yetkili Özel Servisler";
                $desc = "81 ilde onaylı oto mekanik, elektrik, periyodik bakım ve yol yardım ustaları.";
            }
            ?>

            <!-- SEO Breadcrumbs -->
            <nav class="archive-breadcrumbs" style="font-size: 13px; color: rgba(255,255,255,0.7); display:flex; align-items:center; flex-wrap:wrap; gap:6px; margin-bottom: 12px;">
                <a href="<?php echo esc_url( home_url('/') ); ?>" style="color: rgba(255,255,255,0.85); text-decoration: none;"><i class="fa-solid fa-house" style="font-size:11px;"></i> Ana Sayfa</a>
                <span>/</span>
                <a href="<?php echo esc_url( home_url('/ustalar') ); ?>" style="color: rgba(255,255,255,0.85); text-decoration: none;">Ustalar</a>
                <?php if ( $city_name ) : ?>
                    <span>/</span>
                    <a href="<?php echo esc_url( home_url('/ustalar/?mechanic_city=' . esc_attr($city_slug)) ); ?>" style="color: rgba(255,255,255,0.85); text-decoration: none;"><?php echo esc_html($city_name); ?></a>
                <?php endif; ?>
                <?php if ( $dist_name ) : ?>
                    <span>/</span>
                    <span style="color: #cbd5e1;"><?php echo esc_html($dist_name); ?></span>
                <?php endif; ?>
                <?php if ( $brand_name ) : ?>
                    <span>/</span>
                    <?php if ( $model_name ) : ?>
                        <a href="<?php echo esc_url( home_url('/marka/' . esc_attr($brand_slug)) ); ?>" style="color: rgba(255,255,255,0.85); text-decoration: none;"><?php echo esc_html($brand_name); ?></a>
                        <span>/</span>
                        <span style="color: #cbd5e1;"><?php echo esc_html($model_name); ?></span>
                    <?php else : ?>
                        <span style="color: #cbd5e1;"><?php echo esc_html($brand_name); ?></span>
                    <?php endif; ?>
                <?php endif; ?>
            </nav>

            <h1 class="archive-header-title" style="margin: 0; font-size: 28px; color: white; margin-bottom: 8px; font-weight: 700;"><?php echo esc_html($title); ?></h1>
            <p class="archive-header-desc" style="margin: 0; font-size: 15px; color: rgba(255,255,255,0.85); line-height: 1.5;"><?php echo esc_html($desc); ?></p>
        </div>
    </div>

    <style>
    /* Archive / Ustalar Sayfası Responsive Düzen */
    .archive-container {
        display: flex;
        gap: 36px;
        margin-top: 40px;
        margin-bottom: 80px;
        align-items: flex-start;
    }
    .archive-sidebar {
        width: 310px;
        flex-shrink: 0;
    }
    .archive-sidebar-card {
        background: white;
        padding: 24px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        position: sticky;
        top: 90px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }
    .archive-main-col {
        flex: 1;
        min-width: 0;
        width: 100%;
    }
    .mobile-filter-toggle {
        display: none;
    }
    @media (min-width: 992px) {
        .filter-form-wrapper {
            display: block !important;
        }
    }
    @media (max-width: 991px) {
        .page-header-small {
            padding: 26px 16px 24px 16px !important;
            text-align: center !important;
        }
        .page-breadcrumbs {
            justify-content: center !important;
            font-size: 11.5px !important;
            margin-bottom: 12px !important;
        }
        .archive-header-title {
            font-size: 20px !important;
            line-height: 1.35 !important;
        }
        .archive-header-desc {
            font-size: 13px !important;
        }
        .archive-container {
            flex-direction: column !important;
            gap: 20px !important;
            margin-top: 20px !important;
            margin-bottom: 40px !important;
            padding-left: 16px !important;
            padding-right: 16px !important;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            overflow: hidden !important;
        }
        .archive-sidebar {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            flex-shrink: 1 !important;
        }
        .archive-sidebar-card {
            position: static !important;
            padding: 16px !important;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }
        .mobile-filter-toggle {
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            box-sizing: border-box !important;
            background: #f8fafc;
            border: 1.5px solid #cbd5e1;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            cursor: pointer;
        }
        .mobile-filter-toggle i {
            color: #f97316;
            transition: transform 0.2s;
        }
        .mobile-filter-toggle.active i.fa-chevron-down {
            transform: rotate(180deg);
        }
        .filter-form-wrapper {
            display: none;
            margin-top: 16px;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .filter-form-wrapper.active {
            display: block;
        }
        .archive-main-col {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            box-sizing: border-box !important;
        }
        .listing-grid {
            grid-template-columns: 1fr !important;
            gap: 16px !important;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }
        .listing-card {
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
        }
    }
    </style>

    <div class="container archive-container">
        
        <aside class="archive-sidebar">
            <div class="archive-sidebar-card">
                <button type="button" class="mobile-filter-toggle" id="filterMobileToggleBtn" onclick="this.classList.toggle('active'); document.getElementById('archiveFilterFormWrap').classList.toggle('active');">
                    <span><i class="fa-solid fa-sliders"></i> Arama Filtrelerini Göster / Filtrele</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>

                <div class="filter-form-wrapper" id="archiveFilterFormWrap">
                    <h3 style="margin: 0 0 18px 0; font-size: 17px; font-weight:700; color: #0f172a; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px;">
                        <i class="fa-solid fa-sliders" style="color:#f97316;"></i> Arama Filtreleri
                    </h3>
                    
                    <form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
                        <input type="hidden" name="post_type" value="mechanic" />
                        
                        <div class="form-group" style="margin-bottom: 16px;">
                            <label style="display:block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #334155;">Kelime ile Ara</label>
                            <input type="text" name="s" placeholder="Örn: Akkaya Otomotiv" value="<?php echo get_search_query(); ?>" style="width:100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px;">
                        </div>

                        <div class="form-group" style="margin-bottom: 16px;">
                            <label style="display:block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #334155;">İl</label>
                            <select name="mechanic_city" id="filter-il" class="form-control" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px; background:white;">
                                <option value="">Tüm İller</option>
                                <?php 
                                $cities = get_terms(array('taxonomy' => 'mechanic_city', 'hide_empty' => false));
                                $selected_city = isset($_GET['mechanic_city']) ? sanitize_text_field($_GET['mechanic_city']) : '';
                                foreach($cities as $city) { 
                                    $sel = ($selected_city === $city->slug || $selected_city === $city->name) ? 'selected' : '';
                                    echo '<option value="'.esc_attr($city->slug).'" data-name="'.esc_attr($city->name).'" '.$sel.'>'.esc_html($city->name).'</option>'; 
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 16px;">
                            <label style="display:block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #334155;">İlçe</label>
                            <select name="mechanic_district" id="filter-ilce" data-selected="<?php echo esc_attr(isset($_GET['mechanic_district']) ? $_GET['mechanic_district'] : ''); ?>" class="form-control" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px; background:white;">
                                <option value="">Tüm İlçeler</option>
                                <?php 
                                if(isset($_GET['mechanic_district']) && !empty($_GET['mechanic_district'])) {
                                    echo '<option value="'.esc_attr($_GET['mechanic_district']).'" selected>'.esc_html($_GET['mechanic_district']).'</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 16px;">
                            <label style="display:block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #334155;">Araç Markası</label>
                            <select name="car_brand" id="filter-brand" class="form-control" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px; background:white;">
                                <option value="">Tüm Markalar</option>
                                <?php 
                                $parent_brands = get_terms(array('taxonomy' => 'car_brand', 'parent' => 0, 'hide_empty' => false));
                                foreach($parent_brands as $b) { 
                                    $sel = ($brand_slug === $b->slug) ? 'selected' : '';
                                    echo '<option value="'.esc_attr($b->slug).'" '.$sel.'>'.esc_html($b->name).'</option>'; 
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group" id="filter-model-wrap" style="margin-bottom: 16px; <?php echo empty($brand_slug) ? 'display:none;' : ''; ?>">
                            <label style="display:block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #334155;">Araç Modeli <span style="font-weight:normal; color:#94a3b8;">(Opsiyonel)</span></label>
                            <select name="car_model" id="filter-model" class="form-control" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px; background:white;">
                                <option value="">Tüm Modeller</option>
                                <?php
                                if ( ! empty($brand_slug) ) {
                                    $curr_b_term = get_term_by('slug', $brand_slug, 'car_brand');
                                    if ( $curr_b_term ) {
                                        $models = get_terms(array('taxonomy' => 'car_brand', 'parent' => $curr_b_term->term_id, 'hide_empty' => false));
                                        foreach($models as $m) {
                                            $sel = ($model_slug === $m->slug) ? 'selected' : '';
                                            echo '<option value="'.esc_attr($m->slug).'" '.$sel.'>'.esc_html($m->name).'</option>';
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="display:block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #334155;">Hizmet Türü</label>
                            <select name="service_type" class="form-control" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px; background:white;">
                                <option value="">Tüm Hizmetler</option>
                                <?php 
                                $services = get_terms(array('taxonomy' => 'service_type', 'hide_empty' => false));
                                $selected_service = isset($_GET['service_type']) ? sanitize_text_field($_GET['service_type']) : '';
                                foreach($services as $srv) { 
                                    $sel = ($selected_service === $srv->slug) ? 'selected' : '';
                                    echo '<option value="'.esc_attr($srv->slug).'" '.$sel.'>'.esc_html($srv->name).'</option>'; 
                                }
                                ?>
                            </select>
                        </div>
                        
                        <button type="submit" style="width: 100%; padding: 13px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: white; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(249,115,22,0.35); font-size:14.5px;">
                            <i class="fa-solid fa-filter"></i> Filtrele
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="archive-main-col">
            <?php
            $args = array(
                'post_type'      => 'mechanic',
                'posts_per_page' => 12,
                'post_status'    => 'publish',
                'paged'          => get_query_var('paged') ? get_query_var('paged') : 1,
            );
            
            if ( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {
                $args['s'] = sanitize_text_field( $_GET['s'] );
            }
            
            $tax_queries = array();
            
            if ( isset( $_GET['mechanic_city'] ) && !empty( $_GET['mechanic_city'] ) ) {
                $tax_queries[] = array('taxonomy' => 'mechanic_city', 'field' => 'slug', 'terms' => sanitize_text_field( $_GET['mechanic_city'] ));
            }
            // Eski ?city= parametresini de destekle
            if ( empty($tax_queries) || !in_array('mechanic_city', array_column($tax_queries, 'taxonomy')) ) {
                if ( isset( $_GET['city'] ) && !empty( $_GET['city'] ) ) {
                    $tax_queries[] = array('taxonomy' => 'mechanic_city', 'field' => 'slug', 'terms' => sanitize_text_field( $_GET['city'] ));
                }
            }
            
            if ( isset( $_GET['mechanic_district'] ) && !empty( $_GET['mechanic_district'] ) ) {
                $dist_val = sanitize_text_field( $_GET['mechanic_district'] );
                $d_term = get_term_by( 'name', $dist_val, 'mechanic_district' );
                if ( ! $d_term ) {
                    $d_term = get_term_by( 'slug', $dist_val, 'mechanic_district' );
                }
                if ( $d_term ) {
                    $tax_queries[] = array('taxonomy' => 'mechanic_district', 'field' => 'term_id', 'terms' => $d_term->term_id);
                } else {
                    $tax_queries[] = array('taxonomy' => 'mechanic_district', 'field' => 'name', 'terms' => $dist_val);
                }
            }
            
            // Marka & Model Sorgusu
            if ( ! empty( $model_slug ) ) {
                $m_term_obj = get_term_by( 'slug', $model_slug, 'car_brand' );
                if ( $m_term_obj ) {
                    $b_ids = array( $m_term_obj->term_id );
                    if ( ! empty( $m_term_obj->parent ) ) {
                        $b_ids[] = (int) $m_term_obj->parent;
                    }
                    $tax_queries[] = array(
                        'taxonomy' => 'car_brand',
                        'field'    => 'term_id',
                        'terms'    => $b_ids,
                        'operator' => 'IN',
                    );
                }
            } elseif ( ! empty( $brand_slug ) ) {
                $tax_queries[] = array(
                    'taxonomy'         => 'car_brand',
                    'field'            => 'slug',
                    'terms'            => $brand_slug,
                    'include_children' => true,
                );
            }
            
            if ( isset( $_GET['service_type'] ) && !empty( $_GET['service_type'] ) ) {
                $tax_queries[] = array('taxonomy' => 'service_type', 'field' => 'slug', 'terms' => sanitize_text_field( $_GET['service_type'] ));
            }
            
            if ( is_tax() && $queried_object ) {
                $already_has_tax = false;
                foreach($tax_queries as $tq) {
                    if(isset($tq['taxonomy']) && $tq['taxonomy'] == $queried_object->taxonomy) {
                        $already_has_tax = true; break;
                    }
                }
                if(!$already_has_tax) {
                    if ( $queried_object->taxonomy === 'car_brand' && !empty($queried_object->parent) ) {
                        // Model sayfası ise hem modeli hem ana markayı dahil et (Örn: Cruze için Chevrolet ustaları da gelsin)
                        $tax_queries[] = array(
                            'taxonomy' => 'car_brand',
                            'field'    => 'term_id',
                            'terms'    => array( $queried_object->term_id, $queried_object->parent ),
                            'operator' => 'IN',
                        );
                    } else {
                        $tax_queries[] = array(
                            'taxonomy'         => $queried_object->taxonomy,
                            'field'            => 'term_id',
                            'terms'            => $queried_object->term_id,
                            'include_children' => true,
                        );
                    }
                }
            }
            
            if ( count( $tax_queries ) > 0 ) {
                $tax_queries['relation'] = 'AND';
                $args['tax_query'] = $tax_queries;
            }
            
            $query = new WP_Query( $args );
            ?>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <?php if ( $query->found_posts > 0 ) : ?>
                    <h2 style="margin:0; font-size: 20px; color: #1e293b;">Bulunan Sonuçlar <span style="color:#64748b; font-size:16px;">(<?php echo $query->found_posts; ?>)</span></h2>
                <?php else : ?>
                    <h2 style="margin:0; font-size: 20px; color: #1e293b;"><?php echo esc_html( $dist_name ? "{$city_name} {$dist_name} Ustaları" : ( $city_name ? "{$city_name} Ustaları" : "Oto Tamircileri" ) ); ?> <span style="color:#ea580c; font-size:15px; font-weight:700;">(Önerilen En Yakın Servisler)</span></h2>
                <?php endif; ?>
            </div>

            <div class="listing-grid">
                <?php
                if ( $query->have_posts() ) :
                    $card_counter = 0;
                    while ( $query->have_posts() ) : $query->the_post();
                        $card_counter++;
                        $image_url = get_post_meta( get_the_ID(), '_custom_mechanic_image', true );
                        if ( empty( $image_url ) ) {
                            $image_url = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'medium' ) : 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?auto=format&fit=crop&w=450&q=75';
                        }
                        if ( function_exists( 'ototamir_optimize_image_url' ) ) {
                            $image_url = ototamir_optimize_image_url( $image_url, 450 );
                        }
                        $is_vip = get_post_meta( get_the_ID(), '_mechanic_is_featured', true ) === '1';
                        $vip_badge_text = get_post_meta( get_the_ID(), '_mechanic_badge_text', true ) ?: 'Öne Çıkan VIP';
                        $card_vip_class = $is_vip ? ' listing-card-vip' : '';
                ?>
                    <a href="<?php the_permalink(); ?>" class="listing-card<?php echo $card_vip_class; ?>">
                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>" class="listing-card-bg" width="370" height="250" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='<?php echo esc_url( get_template_directory_uri() . '/assets/images/placeholder-mechanic.webp' ); ?>';">
                        <div class="listing-card-overlay"></div>
                        
                        <?php if ( $is_vip ) : ?>
                            <div class="listing-badge-top listing-badge-vip">
                                <i class="fa-solid fa-crown"></i> <?php echo esc_html( $vip_badge_text ); ?>
                            </div>
                        <?php else : ?>
                            <div class="listing-badge-top">
                                <i class="fa-solid fa-star"></i> Tavsiye Ediyoruz!
                            </div>
                        <?php endif; ?>
                        
                        <div class="listing-card-content">
                            <?php $rating = get_mechanic_rating_data(get_the_ID()); if($rating['count'] > 0): ?>
                                <div class="listing-rating-pill"><?php echo $rating['avg']; ?></div>
                            <?php else: ?>
                                <div class="listing-rating-pill" style="background:#64748b; color: white;">Yeni</div>
                            <?php endif; ?>
                            
                            <h3 class="listing-title">
                                <?php the_title(); ?> <i class="fa-solid fa-circle-check"></i>
                            </h3>
                            
                            <div class="listing-address">
                                <?php 
                                $address = get_post_meta( get_the_ID(), '_mechanic_address', true );
                                if(empty($address)) {
                                    $cities = wp_get_post_terms(get_the_ID(), 'mechanic_city');
                                    $districts = wp_get_post_terms(get_the_ID(), 'mechanic_district');
                                    $city_name = ($cities && !is_wp_error($cities)) ? $cities[0]->name : '';
                                    $district_name = ($districts && !is_wp_error($districts)) ? $districts[0]->name : '';
                                    $address = trim("$district_name, $city_name", ", ");
                                }
                                echo esc_html( $address ); 
                                ?>
                            </div>
                        </div>
                        
                        <?php 
                        $is_fav = function_exists('is_user_favorite') && is_user_favorite(get_the_ID());
                        $heart_class = $is_fav ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
                        $heart_color = $is_fav ? 'color:#ef4444;' : 'color:white;';
                        ?>
                        <div class="listing-heart" data-post-id="<?php echo get_the_ID(); ?>" style="<?php echo $heart_color; ?> z-index: 10;">
                            <i class="<?php echo $heart_class; ?>"></i>
                        </div>
                    </a>

                    <?php 
                    // Google AdSense In-Feed Doğal Reklam Yerleşimi (4. ve 10. karttan sonra)
                    if ( ( $card_counter === 4 || $card_counter === 10 ) && function_exists( 'ototamir_render_ad' ) ) {
                        $infeed_ad_html = ototamir_render_ad( 'infeed' );
                        if ( ! empty( $infeed_ad_html ) ) {
                            echo '<div class="listing-ad-wrapper" style="grid-column: 1 / -1;">' . $infeed_ad_html . '</div>';
                        }
                    }
                    ?>
                <?php 
                    endwhile;
                else :
                    if ( ! empty( $city_slug ) ) :
                        // Akıllı İlçe Fallback: Bu ildeki diğer onaylı ve yol yardım ustalarını listele
                        $fallback_args = array(
                            'post_type'      => 'mechanic',
                            'posts_per_page' => 8,
                            'post_status'    => 'publish',
                            'tax_query'      => array(
                                array(
                                    'taxonomy' => 'mechanic_city',
                                    'field'    => 'slug',
                                    'terms'    => $city_slug,
                                ),
                            ),
                        );
                        $fallback_query = new WP_Query( $fallback_args );
                    ?>
                        <div class="empty-district-recommendation" style="grid-column: 1 / -1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; margin-bottom: 25px;">
                            <div style="display:flex; align-items:flex-start; gap:16px; flex-wrap:wrap;">
                                <div style="width:48px; height:48px; border-radius:12px; background:rgba(249,115,22,0.1); color:#ea580c; display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0;">
                                    <i class="fa-solid fa-map-location-dot"></i>
                                </div>
                                <div style="flex:1; min-width:280px;">
                                    <h3 style="margin:0 0 6px; font-size:18px; font-weight:800; color:#0f172a;">
                                        <?php echo esc_html( $dist_name ? "{$city_name} {$dist_name} Bölgesi" : $city_name ); ?> İçin En Yakın Ustalar
                                    </h3>
                                    <p style="margin:0 0 14px; font-size:14px; color:#64748b; line-height:1.6;">
                                        <?php if ( $dist_name ) : ?>
                                            <strong><?php echo esc_html( $dist_name ); ?></strong> ilçesinde doğrudan kayıtlı atölye aranıyor. Aşağıda <strong><?php echo esc_html( $city_name ); ?></strong> genelinde bu ilçeye ve çevre yollara en hızlı ulaşan onaylı oto servisleri, nöbetçi tamircileri ve 7/24 oto çekicileri listelenmiştir.
                                        <?php else : ?>
                                            Bu filtre kriterine uygun doğrudan usta bulunamadı. İl genelindeki popüler servisleri aşağıda inceleyebilirsiniz.
                                        <?php endif; ?>
                                    </p>
                                    <a href="<?php echo esc_url( home_url('/usta-ekle/') ); ?>" style="display:inline-flex; align-items:center; gap:8px; background:#ea580c; color:white; padding:10px 18px; border-radius:10px; font-weight:700; font-size:13.5px; text-decoration:none; box-shadow:0 4px 12px rgba(234,88,12,0.25);">
                                        <i class="fa-solid fa-plus-circle"></i> Bu İlçede Usta Mısınız? Firmanızı Ücretsiz Ekleyin
                                    </a>
                                </div>
                            </div>
                        </div>

                        <?php
                        if ( $fallback_query->have_posts() ) :
                            while ( $fallback_query->have_posts() ) : $fallback_query->the_post();
                                $image_url = get_post_meta( get_the_ID(), '_custom_mechanic_image', true );
                                if ( empty( $image_url ) ) {
                                    $image_url = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'medium' ) : 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?auto=format&fit=crop&w=450&q=75';
                                }
                                $phone = get_post_meta( get_the_ID(), '_mechanic_phone', true );
                                $address = get_post_meta( get_the_ID(), '_mechanic_address', true );
                                $is_featured = get_post_meta( get_the_ID(), '_mechanic_featured', true );
                                $badge = get_post_meta( get_the_ID(), '_mechanic_badge', true );
                                $is_road = get_post_meta( get_the_ID(), '_mechanic_road_assist', true );
                                $rating_avg = get_post_meta( get_the_ID(), '_mechanic_rating_avg', true );
                                $rating_cnt = get_post_meta( get_the_ID(), '_mechanic_rating_count', true );
                                if ( empty( $rating_avg ) ) $rating_avg = '5.0';
                                if ( empty( $rating_cnt ) ) $rating_cnt = '1';
                        ?>
                                <a href="<?php the_permalink(); ?>" class="listing-card" style="text-decoration:none; color:inherit; display:flex; flex-direction:column; background:white; border-radius:16px; overflow:hidden; border:1px solid #f1f5f9; box-shadow:0 4px 20px rgba(0,0,0,0.03); position:relative; transition:all 0.3s ease;">
                                    <div class="listing-img-box" style="position:relative; height:200px; width:100%;">
                                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>" style="width:100%; height:100%; object-fit:cover;" loading="lazy">
                                        <div style="position:absolute; top:12px; left:12px; display:flex; flex-direction:column; gap:6px;">
                                            <?php if($badge): ?>
                                                <span style="background:#ea580c; color:white; font-size:11px; font-weight:700; padding:4px 10px; border-radius:20px; box-shadow:0 2px 8px rgba(0,0,0,0.15); display:inline-block;"><?php echo esc_html($badge); ?></span>
                                            <?php elseif($is_featured): ?>
                                                <span style="background:#ea580c; color:white; font-size:11px; font-weight:700; padding:4px 10px; border-radius:20px; box-shadow:0 2px 8px rgba(0,0,0,0.15); display:inline-block;">Öne Çıkan</span>
                                            <?php endif; ?>
                                            <?php if($is_road === 'yes'): ?>
                                                <span style="background:#dc2626; color:white; font-size:11px; font-weight:700; padding:4px 10px; border-radius:20px; box-shadow:0 2px 8px rgba(0,0,0,0.15); display:inline-block;">7/24 Yol Yardım</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="listing-content" style="padding:20px; display:flex; flex-direction:column; flex:1;">
                                        <h3 style="margin:0 0 8px; font-size:16px; font-weight:800; color:#1e293b;"><?php the_title(); ?></h3>
                                        <p style="margin:0 0 12px; font-size:13px; color:#64748b; display:flex; align-items:center; gap:6px;">
                                            <i class="fa-solid fa-location-dot" style="color:#ea580c;"></i>
                                            <span><?php echo esc_html( wp_trim_words( $address, 6, '...' ) ); ?></span>
                                        </p>
                                        <div style="margin-top:auto; display:flex; justify-content:space-between; align-items:center; border-top:1px solid #f1f5f9; padding-top:12px;">
                                            <span style="font-size:13px; font-weight:700; color:#ea580c;">
                                                <i class="fa-solid fa-star" style="color:#f59e0b;"></i> <?php echo esc_html($rating_avg); ?> (<?php echo esc_html($rating_cnt); ?>)
                                            </span>
                                            <span style="font-size:13px; font-weight:700; color:#0f172a; display:inline-flex; align-items:center; gap:4px;">
                                                İncele <i class="fa-solid fa-arrow-right" style="font-size:11px;"></i>
                                            </span>
                                        </div>
                                    </div>
                                </a>
                        <?php
                            endwhile;
                            wp_reset_postdata();
                        else :
                            echo '<p style="color: #64748b; grid-column: 1 / -1;">Bu bölgede henüz usta kaydı bulunamadı.</p>';
                        endif;
                    else :
                        echo '<p style="color: #64748b; grid-column: 1 / -1;">Aradığınız kriterlere uygun usta bulunamadı.</p>';
                    endif;
                endif;
                wp_reset_postdata();
                ?>
            </div>
            
            <div class="pagination" style="margin-top: 40px;">
                <?php
                echo paginate_links( array(
                    'total' => $query->max_num_pages,
                    'prev_text' => '&laquo; Önceki',
                    'next_text' => 'Sonraki &raquo;',
                ) );
                ?>
            </div>
        </main>
    </div>

    <!-- ==================== YEREL SEO SSS & BİLGİ BÖLÜMÜ ==================== -->
    <?php
    $faq_loc = $city_name ? ($dist_name ? "$city_name $dist_name" : $city_name) : "Türkiye";
    $faqs = array(
        array(
            'q' => "{$faq_loc} bölgesinde en iyi oto tamircisi nasıl bulunur?",
            'a' => "OtoTamirciBul üzerinden {$faq_loc} bölgesindeki tüm kayıtlı tamircilerin müşteri puanlarını, gerçek yorumlarını, uzmanlık alanlarını (motor, fren, elektrik vb.) ve çalışma saatlerini inceleyerek en uygun ustayı seçebilirsiniz."
        ),
        array(
            'q' => "{$faq_loc} oto tamir servislerinde fiyatlar nasıl belirlenir?",
            'a' => "Oto tamir ve bakım fiyatları; aracın markası, arızanın kapsamı, işçilik süresi ve kullanılacak yedek parçanın (orijinal veya eşdeğer) türüne göre değişiklik gösterir. İlan sayfalarındaki telefon veya WhatsApp butonuyla doğrudan ustadan ücretsiz fiyat teklifi alabilirsiniz."
        ),
        array(
            'q' => "Pazar günü veya 7/24 açık oto tamirci var mı?",
            'a' => "Evet. Sitemizdeki filtreleme alanından veya ilan detaylarındaki 'Çalışma Koşulları' bölümünden Pazar günü açık olan ve 7/24 yol yardım desteği sağlayan ustaları kolayca bulabilirsiniz."
        ),
        array(
            'q' => "Oto tamir işleminden önce fiyat teklifi alabilir miyim?",
            'a' => "Kesinlikle. Listelenen tamircileri doğrudan arayarak veya WhatsApp üzerinden arıza belirtilerini anlatıp tahmini parça ve işçilik fiyatı alabilirsiniz."
        )
    );
    ?>

    <section class="archive-seo-faq" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 60px 0;">
        <div class="container" style="max-width: 900px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 35px;">
                <span style="display: inline-block; background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; padding: 4px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; margin-bottom: 8px;">
                    <i class="fa-solid fa-circle-question"></i> Sıkça Sorulan Sorular
                </span>
                <h2 style="font-size: 26px; color: #1e293b; margin: 0 0 8px 0;"><?php echo esc_html($faq_loc); ?> Oto Tamir Hizmetleri Hakkında</h2>
                <p style="color: #64748b; font-size: 15px; margin: 0;">Sürücülerimizin en çok merak ettiği sorular ve yanıtları</p>
            </div>

            <div class="faq-accordion" style="display: flex; flex-direction: column; gap: 14px;">
                <?php foreach ( $faqs as $faq ) : ?>
                <details style="background: white; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 22px; cursor: pointer; transition: all 0.2s;">
                    <summary style="font-weight: 600; font-size: 16px; color: #1e293b; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                        <span><i class="fa-solid fa-wrench" style="color:#f97316; margin-right:8px; font-size:14px;"></i> <?php echo esc_html($faq['q']); ?></span>
                        <i class="fa-solid fa-chevron-down" style="color:#94a3b8; font-size:13px;"></i>
                    </summary>
                    <p style="color: #64748b; font-size: 14px; line-height: 1.7; margin: 12px 0 0 0; padding-top: 12px; border-top: 1px dashed #f1f5f9;">
                        <?php echo esc_html($faq['a']); ?>
                    </p>
                </details>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php
    $schema_faqs = array();
    foreach ( $faqs as $faq ) {
        $schema_faqs[] = array(
            '@type'          => 'Question',
            'name'           => $faq['q'],
            'acceptedAnswer' => array(
                '@type' => 'Answer',
                'text'  => $faq['a'],
            ),
        );
    }

    $breadcrumb_items = array(
        array(
            '@type'    => 'ListItem',
            'position' => 1,
            'name'     => 'Ana Sayfa',
            'item'     => home_url( '/' ),
        ),
        array(
            '@type'    => 'ListItem',
            'position' => 2,
            'name'     => 'Ustalar',
            'item'     => home_url( '/ustalar' ),
        ),
    );

    $b_pos = 3;
    if ( $city_name ) {
        $breadcrumb_items[] = array(
            '@type'    => 'ListItem',
            'position' => $b_pos++,
            'name'     => $city_name,
            'item'     => home_url( '/ustalar/?mechanic_city=' . $city_slug ),
        );
        if ( $dist_name ) {
            $breadcrumb_items[] = array(
                '@type'    => 'ListItem',
                'position' => $b_pos++,
                'name'     => $dist_name,
                'item'     => home_url( '/ustalar/?mechanic_city=' . $city_slug . '&mechanic_district=' . urlencode( $dist_name ) ),
            );
        }
    }
    if ( $brand_name ) {
        $breadcrumb_items[] = array(
            '@type'    => 'ListItem',
            'position' => $b_pos++,
            'name'     => $brand_name,
            'item'     => home_url( '/marka/' . $brand_slug ),
        );
        if ( $model_name ) {
            $breadcrumb_items[] = array(
                '@type'    => 'ListItem',
                'position' => $b_pos++,
                'name'     => $model_name,
                'item'     => home_url( '/marka/' . $brand_slug . '/' . $model_slug ),
            );
        }
    }

    $archive_schemas = array(
        array(
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $schema_faqs,
        ),
        array(
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $breadcrumb_items,
        ),
    );
    ?>
    <!-- Google FAQPage & BreadcrumbList Schema.org (JSON-LD) -->
    <script type="application/ld+json">
    <?php echo wp_json_encode( $archive_schemas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ); ?>
    </script>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var brandSelect = document.getElementById('filter-brand');
        var modelWrap = document.getElementById('filter-model-wrap');
        var modelSelect = document.getElementById('filter-model');

        if (brandSelect && modelSelect && modelWrap) {
            brandSelect.addEventListener('change', function() {
                var brandVal = this.value;
                if (!brandVal) {
                    modelWrap.style.display = 'none';
                    modelSelect.innerHTML = '<option value="">Tüm Modeller</option>';
                    return;
                }
                modelWrap.style.display = 'block';
                modelSelect.innerHTML = '<option value="">Modeller yükleniyor...</option>';

                var ajaxUrl = '<?php echo esc_url( admin_url('admin-ajax.php') ); ?>?action=ototamir_get_models_by_brand&brand=' + encodeURIComponent(brandVal);
                fetch(ajaxUrl)
                    .then(function(res) { return res.json(); })
                    .then(function(json) {
                        modelSelect.innerHTML = '<option value="">Tüm Modeller</option>';
                        if (json.success && json.data && json.data.models) {
                            json.data.models.forEach(function(m) {
                                var opt = document.createElement('option');
                                opt.value = m.slug;
                                opt.textContent = m.name;
                                modelSelect.appendChild(opt);
                            });
                        }
                    })
                    .catch(function() {
                        modelSelect.innerHTML = '<option value="">Tüm Modeller</option>';
                    });
            });
        }
    });
    </script>

<?php get_footer(); ?>
