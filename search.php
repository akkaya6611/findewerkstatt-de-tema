<?php get_header(); ?>

    <div class="page-header-small">
        <div class="container">
            <h1 style="margin: 0; font-size: 32px; color: white; margin-bottom: 8px; font-weight: 700;">Ustaları Bul & Filtrele</h1>
            <p style="margin: 0; font-size: 16px; color: rgba(255,255,255,0.8);">Kriterlerinize en uygun oto tamircilerini aşağıdan bulabilirsiniz.</p>
        </div>
    </div>

    <style>
    /* Search / Arama Sayfası Responsive Düzen */
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
        .page-header-small h1 {
            font-size: 22px !important;
            line-height: 1.35 !important;
        }
        .page-header-small p {
            font-size: 13.5px !important;
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
                <button type="button" class="mobile-filter-toggle" id="filterMobileToggleBtn" onclick="this.classList.toggle('active'); document.getElementById('searchFilterFormWrap').classList.toggle('active');">
                    <span><i class="fa-solid fa-sliders"></i> Arama Filtrelerini Göster / Filtrele</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>

                <div class="filter-form-wrapper" id="searchFilterFormWrap">
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
                            <select name="car_brand" id="search-brand" class="form-control" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px; background:white;">
                                <option value="">Tüm Markalar</option>
                                <?php 
                                $parent_brands = get_terms(array('taxonomy' => 'car_brand', 'parent' => 0, 'hide_empty' => false));
                                $selected_brand = isset($_GET['car_brand']) ? sanitize_text_field($_GET['car_brand']) : '';
                                foreach($parent_brands as $brand) { 
                                    $sel = ($selected_brand === $brand->slug) ? 'selected' : '';
                                    echo '<option value="'.esc_attr($brand->slug).'" '.$sel.'>'.esc_html($brand->name).'</option>'; 
                                }
                                ?>
                            </select>
                        </div>

                        <?php $selected_model = isset($_GET['car_model']) ? sanitize_text_field($_GET['car_model']) : ''; ?>
                        <div class="form-group" id="search-model-wrap" style="margin-bottom: 16px; <?php echo empty($selected_brand) ? 'display:none;' : ''; ?>">
                            <label style="display:block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #334155;">Araç Modeli <span style="font-weight:normal; color:#94a3b8;">(Opsiyonel)</span></label>
                            <select name="car_model" id="search-model" class="form-control" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px; background:white;">
                                <option value="">Tüm Modeller</option>
                                <?php
                                if ( ! empty($selected_brand) ) {
                                    $curr_b_term = get_term_by('slug', $selected_brand, 'car_brand');
                                    if ( $curr_b_term ) {
                                        $models = get_terms(array('taxonomy' => 'car_brand', 'parent' => $curr_b_term->term_id, 'hide_empty' => false));
                                        foreach($models as $m) {
                                            $sel = ($selected_model === $m->slug) ? 'selected' : '';
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
            
            // Marka & Model Filtresi
            if ( ! empty( $selected_model ) ) {
                $m_term_obj = get_term_by( 'slug', $selected_model, 'car_brand' );
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
            } elseif ( ! empty( $selected_brand ) ) {
                $tax_queries[] = array(
                    'taxonomy'         => 'car_brand',
                    'field'            => 'slug',
                    'terms'            => $selected_brand,
                    'include_children' => true,
                );
            }
            
            if ( isset( $_GET['service_type'] ) && !empty( $_GET['service_type'] ) ) {
                $tax_queries[] = array('taxonomy' => 'service_type', 'field' => 'slug', 'terms' => sanitize_text_field( $_GET['service_type'] ));
            }
            
            if ( count( $tax_queries ) > 0 ) {
                $tax_queries['relation'] = 'AND';
                $args['tax_query'] = $tax_queries;
            }
            
            $query = new WP_Query( $args );
            ?>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                <h2 style="margin:0; font-size: 20px; color: #1e293b;">Bulunan Sonuçlar <span style="color:#64748b; font-size:16px;">(<?php echo $query->found_posts; ?>)</span></h2>
            </div>

            <div class="listing-grid">
                <?php
                if ( $query->have_posts() ) :
                    while ( $query->have_posts() ) : $query->the_post();
                        $image_url = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'medium_large') : 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?auto=format&fit=crop&w=800&q=80';
                        $is_vip = get_post_meta( get_the_ID(), '_mechanic_is_featured', true ) === '1';
                        $vip_badge_text = get_post_meta( get_the_ID(), '_mechanic_badge_text', true ) ?: 'Öne Çıkan VIP';
                        $card_vip_class = $is_vip ? ' listing-card-vip' : '';
                ?>
                    <a href="<?php the_permalink(); ?>" class="listing-card<?php echo $card_vip_class; ?>">
                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>" class="listing-card-bg">
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
                    endwhile;
                else :
                    echo '<p style="color: #64748b;">Aradığınız kriterlere uygun usta bulunamadı.</p>';
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

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var brandSelect = document.getElementById('search-brand');
        var modelWrap = document.getElementById('search-model-wrap');
        var modelSelect = document.getElementById('search-model');

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
