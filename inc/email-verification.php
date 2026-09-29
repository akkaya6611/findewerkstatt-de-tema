<?php
/**
 * OTOTAMİR-360 E-posta Onay Kodu (OTP) ile Üyelik Doğrulama Modülü
 *
 * @package OtoTamir_360
 * @version 1.4.12
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class OtoTamir_Email_Verification {

    const OTP_EXPIRY_MINUTES = 15;
    const RESEND_COOLDOWN_SECONDS = 60;
    const MAX_ATTEMPTS = 5;

    /**
     * Modülü Başlat
     */
    public static function init() {
        // AJAX: Kod Gönder
        add_action( 'wp_ajax_nopriv_ototamir_send_register_code', [ __CLASS__, 'ajax_send_code' ] );
        add_action( 'wp_ajax_ototamir_send_register_code', [ __CLASS__, 'ajax_send_code' ] );

        // AJAX: Kod Doğrula & Kayıt Tamamla
        add_action( 'wp_ajax_nopriv_ototamir_verify_register_code', [ __CLASS__, 'ajax_verify_code' ] );
        add_action( 'wp_ajax_ototamir_verify_register_code', [ __CLASS__, 'ajax_verify_code' ] );

        // AJAX: Kodu Tekrar Gönder
        add_action( 'wp_ajax_nopriv_ototamir_resend_register_code', [ __CLASS__, 'ajax_resend_code' ] );
        add_action( 'wp_ajax_ototamir_resend_register_code', [ __CLASS__, 'ajax_resend_code' ] );

        // Frontend script değişkenlerini enjekte et
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_auth_vars' ], 20 );
    }

    /**
     * Frontend AJAX Değişkenleri
     */
    public static function enqueue_auth_vars() {
        if ( is_page_template( 'page-kayit.php' ) || is_page( 'kayit' ) ) {
            wp_localize_script( 'jquery', 'ototamir_auth_vars', [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'ototamir_auth_nonce' ),
                'cooldown' => self::RESEND_COOLDOWN_SECONDS,
            ] );
        }
    }

    /**
     * Transient Anahtarı Üret
     */
    private static function get_transient_key( $email ) {
        return 'ototamir_reg_otp_' . md5( strtolower( trim( $email ) ) );
    }

    /**
     * AJAX 1: Kayıt Doğrulama Kodu Gönder
     */
    public static function ajax_send_code() {
        check_ajax_referer( 'ototamir_auth_nonce', 'nonce' );

        $user_login = isset( $_POST['user_login'] ) ? sanitize_user( wp_unslash( $_POST['user_login'] ), true ) : '';
        $user_email = isset( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '';
        $user_pass  = isset( $_POST['user_pass'] ) ? $_POST['user_pass'] : '';
        $user_role  = isset( $_POST['user_role'] ) && $_POST['user_role'] === 'mechanic' ? 'contributor' : 'subscriber';

        // 1. Temel doğrulamalar
        if ( empty( $user_login ) || empty( $user_email ) || empty( $user_pass ) ) {
            wp_send_json_error( [ 'message' => 'Lütfen tüm zorunlu alanları doldurun.' ] );
        }

        if ( ! is_email( $user_email ) ) {
            wp_send_json_error( [ 'message' => 'Geçerli bir e-posta adresi giriniz.' ] );
        }

        if ( strlen( $user_pass ) < 6 ) {
            wp_send_json_error( [ 'message' => 'Şifreniz güvenliğiniz için en az 6 karakter olmalıdır.' ] );
        }

        if ( username_exists( $user_login ) ) {
            wp_send_json_error( [ 'message' => 'Bu kullanıcı adı zaten başka bir üye tarafından kullanılıyor.' ] );
        }

        if ( email_exists( $user_email ) ) {
            wp_send_json_error( [ 'message' => 'Bu e-posta adresi ile zaten kayıtlı bir hesap var.' ] );
        }

        // 2. Rate-limit (Bekleme süresi) kontrolü
        $transient_key = self::get_transient_key( $user_email );
        $existing = get_transient( $transient_key );

        if ( $existing && is_array( $existing ) && isset( $existing['created_at'] ) ) {
            $elapsed = time() - $existing['created_at'];
            if ( $elapsed < self::RESEND_COOLDOWN_SECONDS ) {
                $wait = self::RESEND_COOLDOWN_SECONDS - $elapsed;
                wp_send_json_error( [
                    'message' => "Lütfen yeni bir kod istemeden önce {$wait} saniye bekleyin.",
                    'cooldown' => $wait
                ] );
            }
        }

        // 3. 6 Haneli Rastgele Doğrulama Kodu Üret
        $code = (string) wp_rand( 100000, 999999 );

        $payload = [
            'code'       => $code,
            'user_login' => $user_login,
            'user_email' => $user_email,
            'user_pass'  => $user_pass,
            'user_role'  => $user_role,
            'attempts'   => 0,
            'created_at' => time(),
        ];

        // 15 dakika boyunca sakla
        set_transient( $transient_key, $payload, self::OTP_EXPIRY_MINUTES * MINUTE_IN_SECONDS );

        // 4. E-postayı Gönder
        $sent = self::send_otp_email( $user_email, $user_login, $code );

        if ( $sent ) {
            wp_send_json_success( [
                'message'  => 'Doğrulama kodunuz e-posta adresinize başarıyla gönderildi.',
                'email'    => $user_email,
                'cooldown' => self::RESEND_COOLDOWN_SECONDS,
            ] );
        } else {
            wp_send_json_error( [
                'message' => 'Doğrulama e-postası gönderilemedi. Lütfen e-posta adresinizi kontrol edip tekrar deneyin.'
            ] );
        }
    }

    /**
     * AJAX 2: Kodu Tekrar Gönder
     */
    public static function ajax_resend_code() {
        check_ajax_referer( 'ototamir_auth_nonce', 'nonce' );

        $user_email = isset( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '';
        if ( empty( $user_email ) || ! is_email( $user_email ) ) {
            wp_send_json_error( [ 'message' => 'Geçersiz e-posta adresi.' ] );
        }

        $transient_key = self::get_transient_key( $user_email );
        $payload = get_transient( $transient_key );

        if ( ! $payload || ! is_array( $payload ) ) {
            wp_send_json_error( [ 'message' => 'Kayıt oturumunuzun süresi dolmuş. Lütfen formu yeniden doldurun.' ] );
        }

        // Bekleme süresi kontrolü
        $elapsed = time() - ( $payload['created_at'] ?? 0 );
        if ( $elapsed < self::RESEND_COOLDOWN_SECONDS ) {
            $wait = self::RESEND_COOLDOWN_SECONDS - $elapsed;
            wp_send_json_error( [
                'message' => "Yeni bir kod istemeden önce {$wait} saniye beklemelisiniz.",
                'cooldown' => $wait
            ] );
        }

        // Yeni kod üret
        $code = (string) wp_rand( 100000, 999999 );
        $payload['code'] = $code;
        $payload['created_at'] = time();
        $payload['attempts'] = 0;

        set_transient( $transient_key, $payload, self::OTP_EXPIRY_MINUTES * MINUTE_IN_SECONDS );

        $sent = self::send_otp_email( $user_email, $payload['user_login'], $code );

        if ( $sent ) {
            wp_send_json_success( [
                'message'  => 'Yeni doğrulama kodunuz e-posta adresinize gönderildi.',
                'cooldown' => self::RESEND_COOLDOWN_SECONDS,
            ] );
        } else {
            wp_send_json_error( [ 'message' => 'E-posta gönderilirken bir sorun oluştu.' ] );
        }
    }

    /**
     * AJAX 3: Kodu Doğrula & Kullanıcıyı Kaydet
     */
    public static function ajax_verify_code() {
        check_ajax_referer( 'ototamir_auth_nonce', 'nonce' );

        $user_email = isset( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '';
        $otp_code   = isset( $_POST['otp_code'] ) ? preg_replace( '/\D/', '', $_POST['otp_code'] ) : '';
        $redirect   = isset( $_POST['redirect_to'] ) && ! empty( $_POST['redirect_to'] ) ? esc_url_raw( $_POST['redirect_to'] ) : home_url( '/profil' );

        if ( empty( $user_email ) || empty( $otp_code ) ) {
            wp_send_json_error( [ 'message' => 'Lütfen 6 haneli doğrulama kodunu eksiksiz girin.' ] );
        }

        $transient_key = self::get_transient_key( $user_email );
        $payload = get_transient( $transient_key );

        if ( ! $payload || ! is_array( $payload ) ) {
            wp_send_json_error( [
                'message' => 'Doğrulama kodunun süresi dolmuş veya geçersiz. Lütfen tekrar kod isteyin.',
                'expired' => true
            ] );
        }

        // Çok fazla hatalı deneme koruması (Brute-force)
        if ( ( $payload['attempts'] ?? 0 ) >= self::MAX_ATTEMPTS ) {
            delete_transient( $transient_key );
            wp_send_json_error( [
                'message' => 'Çok fazla hatalı deneme yaptınız. Güvenliğiniz için bu kod iptal edildi. Lütfen baştan kayıt olun.',
                'locked'  => true
            ] );
        }

        // Kod Eşleşme Kontrolü
        if ( (string) $payload['code'] !== (string) $otp_code ) {
            $payload['attempts'] = ( $payload['attempts'] ?? 0 ) + 1;
            $remaining = self::MAX_ATTEMPTS - $payload['attempts'];
            set_transient( $transient_key, $payload, self::OTP_EXPIRY_MINUTES * MINUTE_IN_SECONDS );

            wp_send_json_error( [
                'message' => "Girdiğiniz 6 haneli kod hatalı! Kalan deneme hakkınız: {$remaining}",
                'remaining' => $remaining
            ] );
        }

        // KOD DOĞRU! Kullanıcıyı Oluştur
        if ( username_exists( $payload['user_login'] ) ) {
            delete_transient( $transient_key );
            wp_send_json_error( [ 'message' => 'Bu kullanıcı adı zaten alınmış. Lütfen farklı bir kullanıcı adı seçin.' ] );
        }

        if ( email_exists( $payload['user_email'] ) ) {
            delete_transient( $transient_key );
            wp_send_json_error( [ 'message' => 'Bu e-posta adresiyle zaten bir hesap açılmış.' ] );
        }

        $user_id = wp_create_user( $payload['user_login'], $payload['user_pass'], $payload['user_email'] );

        if ( is_wp_error( $user_id ) ) {
            wp_send_json_error( [ 'message' => $user_id->get_error_message() ] );
        }

        $user = new WP_User( $user_id );
        $user->set_role( $payload['user_role'] );

        // E-posta doğrulandı meta damgası
        update_user_meta( $user_id, 'email_verified', 1 );
        update_user_meta( $user_id, 'email_verified_at', current_time( 'mysql' ) );

        // Usta rolü ise firma ekleme yönlendirmesi veya profil
        if ( $payload['user_role'] === 'contributor' ) {
            update_user_meta( $user_id, '_is_mechanic_account', 1 );
        }

        // Oturumu otomatik aç
        wp_clear_auth_cookie();
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id, true );

        // Geçici kodu temizle
        delete_transient( $transient_key );

        wp_send_json_success( [
            'message'      => 'Tebrikler! E-posta adresiniz doğrulandı ve hesabınız açıldı. Yönlendiriliyorsunuz...',
            'redirect_url' => $redirect,
        ] );
    }

    /**
     * Şık Kurumsal HTML E-posta Gönderimi
     */
    public static function send_otp_email( $email, $username, $code ) {
        $site_name = get_bloginfo( 'name' );
        $site_url  = home_url( '/' );
        $logo_url  = get_template_directory_uri() . '/assets/images/logo-main.png';

        $subject = "[{$site_name}] Üyelik Doğrulama Kodunuz: {$code}";

        $html = '<!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Üyelik Doğrulama Kodu</title>
        </head>
        <body style="margin: 0; padding: 0; background-color: #0f172a; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; color: #334155;">
            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #0f172a; padding: 40px 15px;">
                <tr>
                    <td align="center">
                        <!-- Ana Kart -->
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 540px; background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
                            <!-- Üst Başlık & Logo -->
                            <tr>
                                <td align="center" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 32px 24px; border-bottom: 3px solid #ea580c;">
                                    <a href="' . esc_url( $site_url ) . '" target="_blank" style="text-decoration: none;">
                                        <img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( $site_name ) . '" width="220" style="display: block; max-width: 220px; height: auto; border: 0;">
                                    </a>
                                </td>
                            </tr>
                            <!-- İçerik Alanı -->
                            <tr>
                                <td style="padding: 36px 32px;">
                                    <div style="display: inline-block; background-color: #ffedd5; color: #ea580c; font-size: 12px; font-weight: 800; padding: 4px 12px; border-radius: 20px; margin-bottom: 16px; letter-spacing: 0.5px;">
                                        🔐 GÜVENLİK DOĞRULAMASI
                                    </div>
                                    <h1 style="color: #0f172a; font-size: 22px; font-weight: 800; margin: 0 0 14px; line-height: 1.3;">
                                        Hesabınızı Doğrulayın
                                    </h1>
                                    <p style="color: #475569; font-size: 15px; line-height: 1.6; margin: 0 0 24px;">
                                        Merhaba <strong>' . esc_html( $username ) . '</strong>,<br>
                                        <strong>' . esc_html( $site_name ) . '</strong> platformuna üye olmak için talep ettiğiniz tek kullanımlık güvenlik onay kodunuz aşağıdadır:
                                    </p>

                                    <!-- OTP Kod Kutusu -->
                                    <div style="background-color: #f8fafc; border: 2px dashed #ea580c; border-radius: 14px; padding: 22px 16px; text-align: center; margin: 0 0 24px;">
                                        <div style="font-size: 12px; font-weight: 700; color: #64748b; letter-spacing: 1px; margin-bottom: 8px;">
                                            6 HANELİ GİRİŞ KODUNUZ
                                        </div>
                                        <span style="font-size: 38px; font-weight: 800; letter-spacing: 10px; color: #ea580c; font-family: monospace; display: inline-block; padding-left: 10px;">
                                            ' . esc_html( $code ) . '
                                        </span>
                                    </div>

                                    <div style="background-color: #f1f5f9; border-radius: 10px; padding: 14px 16px; margin-bottom: 24px;">
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td width="24" valign="top" style="font-size: 16px;">⏱️</td>
                                                <td style="color: #64748b; font-size: 13px; line-height: 1.5; padding-left: 8px;">
                                                    Bu kod <strong>15 dakika</strong> boyunca geçerlidir. Süre dolduktan sonra yeni bir kod istemeniz gerekecektir.
                                                </td>
                                            </tr>
                                        </table>
                                    </div>

                                    <p style="color: #94a3b8; font-size: 13px; line-height: 1.5; margin: 0;">
                                        ⚠️ Bu işlemi siz başlatmadıysanız lütfen bu e-postayı dikkate almayınız. Hesabınız kod girilmediği sürece oluşturulmaz.
                                    </p>
                                </td>
                            </tr>
                            <!-- Alt Bilgi -->
                            <tr>
                                <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 32px; text-align: center;">
                                    <p style="color: #94a3b8; font-size: 12px; margin: 0; line-height: 1.5;">
                                        © ' . date( 'Y' ) . ' ' . esc_html( $site_name ) . ' — Türkiye\'nin Modern Oto Tamirci Rehberi<br>
                                        <a href="' . esc_url( $site_url ) . '" target="_blank" style="color: #ea580c; text-decoration: none; font-weight: 600;">' . esc_html( parse_url( $site_url, PHP_URL_HOST ) ) . '</a>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>';

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $site_name . ' <bilgi@' . parse_url( $site_url, PHP_URL_HOST ) . '>',
        ];

        return wp_mail( $email, $subject, $html, $headers );
    }
}

// Modülü başlat
OtoTamir_Email_Verification::init();
