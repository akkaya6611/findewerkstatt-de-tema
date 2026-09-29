<?php
/*
Template Name: Werkstatt eintragen
*/

$success_msg = '';
$error_msg = '';

$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : (isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0);
$edit_post = null;
$is_edit = false;

if ( $edit_id > 0 && is_user_logged_in() ) {
    $ep = get_post( $edit_id );
    if ( $ep && $ep->post_type === 'mechanic' && ( $ep->post_author == get_current_user_id() || current_user_can('edit_others_posts') ) ) {
        $edit_post = $ep;
        $is_edit = true;
    }
}

if ( $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_mechanic']) && is_user_logged_in() ) {
    $title = isset($_POST['firma_adi']) ? sanitize_text_field($_POST['firma_adi']) : '';
    $desc = isset($_POST['hakkinda']) ? sanitize_textarea_field($_POST['hakkinda']) : '';
    $phone = isset($_POST['telefon']) ? sanitize_text_field($_POST['telefon']) : '';
    $address = isset($_POST['adres']) ? sanitize_textarea_field($_POST['adres']) : '';
    $sunday = isset($_POST['pazar']) ? 'yes' : 'no';
    $road = isset($_POST['yol_yardim']) ? 'yes' : 'no';
    $package = isset($_POST['selected_package']) ? sanitize_text_field($_POST['selected_package']) : 'free';

    if( empty($title) || empty($phone) ) {
        $error_msg = "Bitte geben Sie den Werkstattnamen und eine Telefonnummer an.";
    } elseif( ! $is_edit && !empty($_POST['city_id']) && function_exists('ototamir_check_duplicate_mechanic') && ototamir_check_duplicate_mechanic($title, intval($_POST['city_id'])) ) {
        $error_msg = "Dieser Werkstattname ist in dieser Stadt bereits im System registriert!";
    } else {
        if ( $is_edit ) {
            $update_args = array(
                'ID'           => $edit_id,
                'post_title'   => $title,
                'post_content' => $desc,
            );
            // Admin harici kullanıcılar düzenleme yaptığında güvenlik ve reklam denetimi için ilan tekrar onaya (pending) alınır
            if ( ! current_user_can( 'administrator' ) ) {
                $update_args['post_status'] = 'pending';
            }

            wp_update_post( $update_args );
            $post_id = $edit_id;

            if ( ! current_user_can( 'administrator' ) ) {
                $success_msg = "Firma bilgileriniz başarıyla güncellendi ve <strong>yönetici onayına gönderildi</strong>! Editör incelemesinin ardından ilanınız güncel haliyle tekrar yayına alınacaktır. <a href='" . esc_url( home_url('/profil') ) . "' style='color:#15803d; text-decoration:underline; margin-left:8px; font-weight:700;'>Profilime Dön →</a>";
            } else {
                $success_msg = "Firma bilgileri yönetici olarak güncellendi! <a href='" . esc_url( home_url('/profil') ) . "' style='color:#15803d; text-decoration:underline; margin-left:8px; font-weight:700;'>Profilime Dön →</a>";
            }
        } else {
            $post_id = wp_insert_post(array(
                'post_title'   => $title,
                'post_content' => $desc,
                'post_type'    => 'mechanic',
                'post_status'  => 'pending',
                'post_author'  => get_current_user_id()
            ));
            $success_msg = "İlanınız başarıyla gönderildi! Yönetici onayından sonra yayınlanacaktır.";
        }

        if( $post_id && !is_wp_error($post_id) ) {
            update_post_meta( $post_id, '_mechanic_phone', $phone );
            update_post_meta( $post_id, '_mechanic_address', $address );
            update_post_meta( $post_id, '_mechanic_sunday', $sunday );
            update_post_meta( $post_id, '_mechanic_road_assist', $road );
            if ( ! $is_edit ) {
                update_post_meta( $post_id, '_mechanic_package', $package );
                update_post_meta( $post_id, '_mechanic_original_author', get_current_user_id() );
                update_post_meta( $post_id, '_mechanic_owner_user_id', get_current_user_id() );
            }

            if(isset($_POST['service_type']) && is_array($_POST['service_type'])) {
                $service_ints = array_map('intval', $_POST['service_type']);
                wp_set_object_terms( $post_id, $service_ints, 'service_type' );
            } else {
                wp_set_object_terms( $post_id, array(), 'service_type' );
            }
            if(isset($_POST['car_brand']) && is_array($_POST['car_brand'])) {
                $brand_ints = array_map('intval', $_POST['car_brand']);
                wp_set_object_terms( $post_id, $brand_ints, 'car_brand' );
            } else {
                wp_set_object_terms( $post_id, array(), 'car_brand' );
            }
            if(isset($_POST['city_id']) && !empty($_POST['city_id'])) {
                wp_set_object_terms( $post_id, array(intval($_POST['city_id'])), 'mechanic_city' );
            }
            if(isset($_POST['district_id']) && !empty($_POST['district_id'])) {
                $d = sanitize_text_field($_POST['district_id']);
                if(is_numeric($d)) {
                    wp_set_object_terms( $post_id, array(intval($d)), 'mechanic_district' );
                } else {
                    wp_set_object_terms( $post_id, $d, 'mechanic_district' );
                }
            }

            if ( ! empty( $_FILES['kapak_fotografi']['name'] ) ) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php');
                $attach_id = media_handle_upload('kapak_fotografi', $post_id);
                if( !is_wp_error($attach_id) ) {
                    set_post_thumbnail( $post_id, $attach_id );
                }
            }

            // Yeni eklenen veya onaya giden güncellemeler için yöneticiye anlık e-posta bildirimi gönder
            if ( function_exists( 'ototamir_notify_admin_new_mechanic' ) ) {
                ototamir_notify_admin_new_mechanic( $post_id, $is_edit );
            }

            if ( $is_edit ) {
                $edit_post = get_post( $edit_id );
            }
        } else {
            $error_msg = "İşlem sırasında bir hata oluştu.";
        }
    }
}

// Düzenleme Değerlerini Hazırla
$val_title = $is_edit ? $edit_post->post_title : '';
$val_desc = $is_edit ? $edit_post->post_content : '';
$val_phone = $is_edit ? get_post_meta($edit_id, '_mechanic_phone', true) : '';
$val_address = $is_edit ? get_post_meta($edit_id, '_mechanic_address', true) : '';
$val_sunday = $is_edit ? (get_post_meta($edit_id, '_mechanic_sunday', true) === 'yes') : false;
$val_road = $is_edit ? (get_post_meta($edit_id, '_mechanic_road_assist', true) === 'yes') : false;

$val_services = array();
$val_brands = array();
$val_city_id = 0;
$val_district_name = '';

if ( $is_edit ) {
    $st = wp_get_object_terms( $edit_id, 'service_type', array('fields' => 'ids') );
    if ( ! is_wp_error($st) && ! empty($st) ) $val_services = $st;

    $cb = wp_get_object_terms( $edit_id, 'car_brand', array('fields' => 'ids') );
    if ( ! is_wp_error($cb) && ! empty($cb) ) $val_brands = $cb;

    $ct = wp_get_object_terms( $edit_id, 'mechanic_city', array('fields' => 'ids') );
    if ( ! is_wp_error($ct) && ! empty($ct) ) $val_city_id = $ct[0];

    $dt = wp_get_object_terms( $edit_id, 'mechanic_district', array('fields' => 'names') );
    if ( ! is_wp_error($dt) && ! empty($dt) ) $val_district_name = $dt[0];
}

get_header(); ?>

<style>
.launch-notice-banner {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #ea580c 100%);
    border-radius: 16px;
    padding: 30px;
    color: white;
    text-align: center;
    margin-bottom: 35px;
    border: 1px solid rgba(249, 115, 22, 0.3);
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}
.launch-notice-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, #ef4444, #f97316);
    color: white;
    padding: 5px 16px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 800;
    margin-bottom: 12px;
    letter-spacing: 0.5px;
}
.launch-notice-banner h2 {
    font-size: 24px;
    font-weight: 800;
    margin: 0 0 8px 0;
    color: #ffffff;
}
.launch-notice-banner p {
    font-size: 14.5px;
    color: #cbd5e1;
    max-width: 750px;
    margin: 0 auto;
    line-height: 1.6;
}

/* Paket Seçici Izgarası */
.package-select-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px;
    margin-bottom: 25px;
}
.package-select-card {
    background: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    padding: 22px 18px;
    text-align: center;
    position: relative;
    transition: all 0.2s ease;
}
.package-select-card.selected {
    border-color: #10b981;
    background: #f0fdf4;
    box-shadow: 0 6px 18px rgba(16, 185, 129, 0.15);
}
.package-select-card.disabled {
    opacity: 0.85;
    background: #f8fafc;
    border-style: dashed;
}
.pkg-top-badge {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    padding: 3px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}
.pkg-top-badge.active {
    background: #10b981;
    color: white;
}
.pkg-top-badge.soon {
    background: #f97316;
    color: white;
}
.pkg-top-badge.king {
    background: #f59e0b;
    color: white;
}
.pkg-icon {
    font-size: 28px;
    margin: 4px 0 6px 0;
}
.package-select-card h4 {
    font-size: 17px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 4px 0;
}
.pkg-price {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 8px;
}
.pkg-price span {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
}
.package-select-card p {
    font-size: 12px;
    color: #64748b;
    line-height: 1.5;
    margin: 0 0 12px 0;
}
.pkg-select-indicator {
    font-size: 12px;
    font-weight: 700;
    color: #10b981;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.pkg-select-indicator.text-muted {
    color: #94a3b8;
}
</style>

<div class="container" style="max-width: 950px; padding: 50px 20px 80px 20px;">
    
    <?php if ( $is_edit ) : ?>
        <!-- DÜZENLEME MODU BANNER'I -->
        <div class="launch-notice-banner" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #c2410c 100%);">
            <div class="launch-notice-badge" style="background: #ea580c;">
                <i class="fa-solid fa-pen-to-square"></i> DÜZENLEME MODU
            </div>
            <h2><?php echo esc_html( $val_title ); ?> — Firma Bilgilerini Güncelle</h2>
            <p>
                Firma bilgilerinizi, iletişim numaralarınızı ve sunduğunuz hizmetleri güncelleyebilirsiniz.<br>
                <span style="display:inline-block; margin-top:6px; font-size:13.5px; opacity:0.9; color:#fed7aa;"><i class="fa-solid fa-shield-halved"></i> Güvenlik ve kalite standartlarımız gereği yapılan her düzenleme yönetici onayından geçtikten sonra yayına alınır.</span>
            </p>
        </div>
    <?php else : ?>
        <!-- LANSMAN BİLGİLENDİRME BANNER'I -->
        <div class="launch-notice-banner">
            <div class="launch-notice-badge">
                <i class="fa-solid fa-fire"></i> LANSMANA ÖZEL FIRSAT
            </div>
            <h2>Geçici Süreyle %100 Ücretsiz Usta Kaydı!</h2>
            <p>
                OtoTamirciBul usta ve servis kayıtları tanıtım lansmanı boyunca <strong>tamamen ÜCRETSİZDİR</strong>. Çok yakında Profesyonel ve Premium paketlerimiz aktif edilecektir. Şimdi ücretsiz kaydolarak erken katılım avantajlarını yakalayın!
            </p>
        </div>

        <!-- 3 PAKET SEÇİM ÖZETİ -->
        <div class="package-select-grid">
            <div class="package-select-card selected">
                <div class="pkg-top-badge active"><i class="fa-solid fa-check"></i> ŞU AN AKTİF</div>
                <div class="pkg-icon">🆓</div>
                <h4>Ücretsiz Paket</h4>
                <div class="pkg-price">0 TL <span>/ 90 gün</span></div>
                <p>1 İlan (90 Gün Yayında), 1 Fotoğraf, 1 Hizmet, WhatsApp & Telefon</p>
                <div class="pkg-select-indicator"><i class="fa-solid fa-circle-check"></i> Bu Paket Seçili</div>
            </div>

            <div class="package-select-card disabled">
                <div class="pkg-top-badge soon"><i class="fa-solid fa-clock"></i> ÇOK YAKINDA</div>
                <div class="pkg-icon">⭐</div>
                <h4>Profesyonel Paket</h4>
                <div class="pkg-price">299 TL <span>/ ay</span></div>
                <p>5 İlan, 1 Vitrin İlanı, 10 Fotoğraf, Aramalarda Öncelik, Profil Doğrulama</p>
                <div class="pkg-select-indicator text-muted"><i class="fa-solid fa-clock"></i> Yakında Aktif</div>
            </div>

            <div class="package-select-card disabled">
                <div class="pkg-top-badge king"><i class="fa-solid fa-crown"></i> ÇOK YAKINDA</div>
                <div class="pkg-icon">👑</div>
                <h4>Premium Paket</h4>
                <div class="pkg-price">599 TL <span>/ ay</span></div>
                <p>15 İlan, 3 Vitrin İlanı, Ana Sayfada Görünme, Altın Rozet, Sınırsız Hizmet</p>
                <div class="pkg-select-indicator text-muted"><i class="fa-solid fa-clock"></i> Yakında Aktif</div>
            </div>
        </div>

        <div style="text-align:center; margin-bottom: 35px;">
            <a href="<?php echo esc_url( home_url('/paketler') ); ?>" target="_blank" style="color:var(--primary); font-weight:700; text-decoration:none; font-size:14px; display:inline-flex; align-items:center; gap:6px;">
                <i class="fa-solid fa-table-columns"></i> 20 Özellikli Tam Paket Karşılaştırmasını Gör →
            </a>
        </div>
    <?php endif; ?>

    <?php if ( ! is_user_logged_in() ) : ?>
        
        <div style="background: white; border: 1px solid #e2e8f0; padding: 50px; border-radius: 20px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.05);">
            <i class="fa-solid fa-lock" style="font-size: 48px; color: #94a3b8; margin-bottom: 20px;"></i>
            <h2 style="font-size: 24px; color: #1e293b; margin-bottom: 15px;">İlan Verebilmek İçin Giriş Yapmalısınız</h2>
            <p style="color: #64748b; margin-bottom: 30px;">İlanlarınızı yönetebilmek ve güvenliğinizi sağlamak adına üyelik sistemiyle çalışıyoruz. Lütfen giriş yapın veya yeni bir hesap oluşturun.</p>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <a href="<?php echo esc_url(home_url('/giris')); ?>" class="btn-primary" style="padding: 15px 30px; text-decoration:none;">Giriş Yap</a>
                <a href="<?php echo esc_url(home_url('/kayit')); ?>" style="padding: 15px 30px; background: transparent; border: 1px solid #f97316; color: #f97316; border-radius: 8px; font-weight: 600; text-decoration:none;">Kayıt Ol</a>
            </div>
        </div>

    <?php else : ?>

        <?php if($success_msg): ?>
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 16px 20px; border-radius: 12px; margin-bottom: 30px; text-align: center; font-size:15px; font-weight:600;">
                <i class="fa-solid fa-circle-check"></i> <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>
        <?php if($error_msg): ?>
            <div style="background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; padding: 16px 20px; border-radius: 12px; margin-bottom: 30px; text-align: center; font-size:15px; font-weight:600;">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" style="background: white; padding: 40px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
            
            <?php if ( $is_edit ) : ?>
                <input type="hidden" name="edit_id" value="<?php echo intval($edit_id); ?>">
            <?php else : ?>
                <input type="hidden" name="selected_package" value="free">
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px 18px; margin-bottom: 25px; display:flex; align-items:center; gap:10px; font-size:13.5px; color:#166534;">
                    <i class="fa-solid fa-circle-check" style="font-size:18px;"></i>
                    <div>Ihr Eintrag wird im Rahmen des <strong>kostenlosen Basis-Pakets (0 € / dauerhaft gelistet)</strong> veröffentlicht.</div>
                </div>
            <?php endif; ?>

            <h3 style="margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0; color:#1e293b;">
                <?php echo $is_edit ? 'Werkstattdaten bearbeiten' : 'Werkstatt-Informationen'; ?>
            </h3>
            
            <div style="margin-bottom: 20px;">
                <label style="display:block; margin-bottom:8px; font-weight:600; color:#1e293b;">Name der Werkstatt / Firmenname <span style="color:#ef4444;">*</span></label>
                <input type="text" name="firma_adi" value="<?php echo esc_attr( $val_title ); ?>" required style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:8px;" placeholder="z. B. Mustermann Kfz-Meisterbetrieb">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:block; margin-bottom:8px; font-weight:600; color:#1e293b;">Titelbild / Werkstattfoto</label>
                <?php if ( $is_edit && has_post_thumbnail($edit_id) ) : ?>
                    <div style="display:flex; align-items:center; gap:14px; margin-bottom:10px; background:#f8fafc; padding:10px; border-radius:8px; border:1px solid #e2e8f0;">
                        <img src="<?php echo esc_url( get_the_post_thumbnail_url($edit_id, 'thumbnail') ); ?>" style="width:64px; height:64px; object-fit:cover; border-radius:6px;">
                        <span style="font-size:13px; color:#64748b;">Aktuelles Werkstattfoto. Leer lassen, wenn keine Änderung gewünscht ist.</span>
                    </div>
                <?php endif; ?>
                <input type="file" name="kapak_fotografi" accept="image/*" style="width:100%; padding:12px; border:1px dashed #e2e8f0; border-radius:8px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:block; margin-bottom:8px; font-weight:600; color:#1e293b;">Über Ihren Betrieb (Optional)</label>
                <textarea name="hakkinda" rows="4" style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:8px;" placeholder="Beschreiben Sie Ihre Werkstatt, Ihre Erfahrung und Ihre Philosophie..."><?php echo esc_textarea( $val_desc ); ?></textarea>
            </div>

            <h3 style="margin-top: 40px; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0; color:#1e293b;">Kontaktdaten &amp; Standort</h3>

            <div style="margin-bottom: 20px;">
                <label style="display:block; margin-bottom:8px; font-weight:600; color:#1e293b;">Telefonnummer <span style="color:#ef4444;">*</span></label>
                <input type="text" name="telefon" value="<?php echo esc_attr( $val_phone ); ?>" required style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:8px;" placeholder="z. B. 030 12345678">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:block; margin-bottom:8px; font-weight:600; color:#1e293b;">Bundesland / Stadt</label>
                <select name="city_id" id="il_secici" style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:8px;">
                    <option value="">Bundesland oder Stadt wählen</option>
                    <?php
                    $cities = get_terms( array('taxonomy' => 'mechanic_city', 'hide_empty' => false) );
                    foreach($cities as $city) {
                        $sel = selected( $city->term_id, $val_city_id, false );
                        echo '<option value="'.esc_attr($city->term_id).'" data-name="'.esc_attr($city->name).'" '.$sel.'>'.esc_html($city->name).'</option>';
                    }
                    ?>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:block; margin-bottom:8px; font-weight:600; color:#1e293b;">Vollständige Anschrift (Straße, Hausnr., PLZ)</label>
                <textarea name="adres" rows="2" style="width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:8px;" placeholder="z. B. Berliner Str. 45, 10115 Berlin"><?php echo esc_textarea( $val_address ); ?></textarea>
            </div>

            <h3 style="margin-top: 40px; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0; color:#1e293b;">Leistungen &amp; Fachbereiche</h3>

            <div style="margin-bottom: 20px;">
                <label style="display:block; margin-bottom:15px; font-weight:600; color:#1e293b;">Angebotene Kfz-Leistungen</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; background: #f8fafc; padding: 20px; border-radius: 8px;">
                    <?php
                    $services = get_terms( array('taxonomy' => 'service_type', 'hide_empty' => false) );
                    foreach($services as $s) {
                        $chk = in_array( $s->term_id, $val_services ) ? 'checked' : '';
                        echo '<label style="display:flex; align-items:center; gap:8px; cursor:pointer;"><input type="checkbox" name="service_type[]" value="'.esc_attr($s->term_id).'" '.$chk.'> '.esc_html($s->name).'</label>';
                    }
                    ?>
                </div>
            </div>

            <div style="margin-bottom: 25px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; flex-wrap:wrap; gap:6px;">
                    <label style="font-weight:600; color:#1e293b; margin:0;">Uzman Olduğunuz Araç Markaları ve Modelleri</label>
                    <span style="font-size:12px; color:#64748b;">(Markayı doğrudan seçebilir veya modellerini belirtebilirsiniz)</span>
                </div>
                <div style="margin-bottom: 10px;">
                    <input type="text" id="brand-search-filter" placeholder="Marka ara (Örn: Renault, Fiat, Ford, BMW, Chevrolet)..." style="width:100%; padding:9px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:13.5px;" onkeyup="filterBrandList(this.value)">
                </div>
                <div id="brands-selection-container" style="max-height: 280px; overflow-y: auto; border: 1px solid #e2e8f0; padding: 12px; border-radius: 8px; background:#fff;">
                    <?php
                    $parent_brands = get_terms( array('taxonomy' => 'car_brand', 'parent' => 0, 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC') );
                    if ( empty($parent_brands) ) {
                        echo '<p style="color:#94a3b8; font-size:13px; margin:0;">Henüz marka eklenmemiş.</p>';
                    } else {
                        foreach ( $parent_brands as $pb ) {
                            $brand_chk = in_array( $pb->term_id, $val_brands ) ? 'checked' : '';
                            $models = get_terms( array('taxonomy' => 'car_brand', 'parent' => $pb->term_id, 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC') );
                            $has_models = ! empty($models) && ! is_wp_error($models);
                            $selected_model_count = 0;
                            if ( $has_models ) {
                                foreach ( $models as $m ) {
                                    if ( in_array( $m->term_id, $val_brands ) ) $selected_model_count++;
                                }
                            }
                            ?>
                            <div class="brand-item-row" data-brand-name="<?php echo esc_attr( mb_strtolower($pb->name, 'UTF-8') ); ?>" style="border-bottom: 1px solid #f1f5f9; padding: 8px 4px;">
                                <div style="display:flex; align-items:center; justify-content:space-between;">
                                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:600; font-size:14px; color:#1e293b; margin:0;">
                                        <input type="checkbox" name="car_brand[]" value="<?php echo esc_attr($pb->term_id); ?>" <?php echo $brand_chk; ?>>
                                        <span><?php echo esc_html($pb->name); ?></span>
                                    </label>
                                    <?php if ( $has_models ) : ?>
                                        <button type="button" onclick="var mEl = document.getElementById('models-<?php echo esc_attr($pb->term_id); ?>'); mEl.style.display = (mEl.style.display === 'none' ? 'grid' : 'none');" style="background:#f8fafc; border:1px solid #e2e8f0; font-size:11.5px; color:#64748b; padding:3px 10px; border-radius:12px; cursor:pointer; font-weight:500;">
                                            <i class="fa-solid fa-car-side" style="color:#f97316;"></i> <?php echo count($models); ?> Model <?php echo $selected_model_count > 0 ? "({$selected_model_count} seçili)" : ''; ?> <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <?php if ( $has_models ) : ?>
                                    <div id="models-<?php echo esc_attr($pb->term_id); ?>" style="display: <?php echo $selected_model_count > 0 ? 'grid' : 'none'; ?>; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 6px; margin-top: 8px; padding: 10px 12px; background: #f8fafc; border-radius: 6px; border: 1px dashed #e2e8f0;">
                                        <?php foreach ( $models as $m ) : 
                                            $m_chk = in_array( $m->term_id, $val_brands ) ? 'checked' : '';
                                        ?>
                                            <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size:12px; color:#475569; margin:0;">
                                                <input type="checkbox" name="car_brand[]" value="<?php echo esc_attr($m->term_id); ?>" <?php echo $m_chk; ?>>
                                                <span><?php echo esc_html($m->name); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>

            <div style="display: flex; gap: 20px; margin-bottom: 40px; margin-top: 20px; flex-wrap: wrap;">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="pazar" value="yes" <?php checked( $val_sunday, true ); ?>> Samstags / Notdienst geöffnet
                </label>
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="yol_yardim" value="yes" <?php checked( $val_road, true ); ?>> 24h Abschleppdienst &amp; Pannenhilfe
                </label>
            </div>

            <button type="submit" name="submit_mechanic" class="btn-primary" style="width:100%; padding:15px; font-size:18px; font-weight:800;">
                <i class="fa-solid <?php echo $is_edit ? 'fa-floppy-disk' : 'fa-paper-plane'; ?>"></i>
                <?php echo $is_edit ? 'Änderungen speichern' : 'Werkstatt jetzt kostenlos eintragen'; ?>
            </button>
            <?php if ( ! $is_edit ) : ?>
                <p style="text-align:center; font-size:13px; color:#64748b; margin-top:10px;">
                    Der Basiseintrag für Ihre Werkstatt ist dauerhaft 100% kostenlos und unverbindlich.
                </p>
            <?php endif; ?>

        </form>
    
    <?php endif; ?>

</div>

<script src="<?php echo get_template_directory_uri(); ?>/assets/js/turkey.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ilSecici = document.getElementById('il_secici');
    const ilceSecici = document.getElementById('ilce_secici');
    if(!ilSecici || !ilceSecici || typeof turkeyLocations === 'undefined') return;

    function populateDistricts(selectedDistrict) {
        const selectedOpt = ilSecici.options[ilSecici.selectedIndex];
        if (!selectedOpt) return;
        const selectedCityName = selectedOpt.getAttribute('data-name');
        ilceSecici.innerHTML = '<option value="">İlçe Seçin</option>';
        
        if (selectedCityName && turkeyLocations[selectedCityName]) {
            const districts = turkeyLocations[selectedCityName];
            districts.forEach(function(districtName) {
                const opt = document.createElement('option');
                opt.value = districtName;
                opt.textContent = districtName;
                if (selectedDistrict && selectedDistrict.toLowerCase() === districtName.toLowerCase()) {
                    opt.selected = true;
                }
                ilceSecici.appendChild(opt);
            });
        } else {
            ilceSecici.innerHTML = '<option value="">Önce İl Seçin</option>';
        }
    }

    ilSecici.addEventListener('change', function() {
        populateDistricts('');
    });

    // Sayfa açıldığında önceden seçili ilçe varsa yükle
    if (ilSecici.value) {
        const preDistrict = "<?php echo esc_js( $val_district_name ); ?>";
        populateDistricts(preDistrict);
    }
});

function filterBrandList(term) {
    term = (term || '').toLowerCase().trim();
    var rows = document.querySelectorAll('.brand-item-row');
    rows.forEach(function(row) {
        var name = (row.getAttribute('data-brand-name') || '').toLowerCase();
        if (!term || name.indexOf(term) > -1) {
            row.style.display = 'block';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>

<?php get_footer(); ?>