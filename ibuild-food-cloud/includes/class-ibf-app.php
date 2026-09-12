<?php

if (!defined('ABSPATH')) {
    exit;
}

final class IBF_App {
    const PAGE_OPTION = 'ibf_app_page_id';

    public static function init() {
        add_action('template_redirect', array(__CLASS__, 'render_if_app'));
        add_shortcode('ibuild_food_cloud', array(__CLASS__, 'shortcode_notice'));
        add_filter('login_redirect', array(__CLASS__, 'login_redirect'), 10, 3);
    }

    private static function security_headers() {
        header_remove('X-Powered-By');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(self), microphone=(), geolocation=()');
        header("Content-Security-Policy: default-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'; object-src 'none'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; img-src 'self' data: blob: https:; connect-src 'self'; media-src 'self' blob:");
        if (is_ssl()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    /**
     * أدوار التغذية لا تملك edit_posts، وووردبريس يرمي من لا يملكها على profile.php
     * عند تسجيل الدخول بلا وجهة محددة. نوجّههم إلى شاشة النظام بدلًا من ذلك.
     * يعمل قبل فحص edit_posts في wp-login.php فيتخطّاه.
     */
    public static function login_redirect($redirect_to, $requested, $user) {
        if (is_wp_error($user) || !($user instanceof WP_User)) {
            return $redirect_to;
        }
        if (!user_can($user, 'ibf_access_food') || user_can($user, 'manage_options')) {
            return $redirect_to;
        }
        $requested = (string) $requested;
        $neutral = array('', admin_url(), admin_url('profile.php'), 'wp-admin/');
        if (!in_array($requested, $neutral, true)) {
            return $redirect_to;
        }
        return self::app_url();
    }

    public static function activate() {
        $page_id = (int) get_option(self::PAGE_OPTION);
        if ($page_id && get_post($page_id)) {
            return;
        }
        $existing = get_page_by_path('ibuild-food');
        if ($existing) {
            $page_id = $existing->ID;
        } else {
            $page_id = wp_insert_post(array(
                'post_title' => 'I-BUILD Food',
                'post_name' => 'ibuild-food',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_content' => '[ibuild_food_cloud]',
            ));
        }
        if (!is_wp_error($page_id)) {
            update_option(self::PAGE_OPTION, (int) $page_id, false);
        }
    }

    public static function app_url() {
        $page_id = (int) get_option(self::PAGE_OPTION);
        return $page_id ? get_permalink($page_id) : home_url('/ibuild-food/');
    }

    public static function shortcode_notice() {
        return '<p>يعمل تطبيق I-BUILD Food في وضع الصفحة الكاملة.</p>';
    }

    private static function login_page() {
        status_header(200);
        nocache_headers();
        $redirect = self::app_url();
        ?><!doctype html>
        <html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
        <title>تسجيل الدخول | I-BUILD Food</title>
        <style>body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f4f7f5;color:#17251f;font-family:Arial,sans-serif}.box{width:min(420px,calc(100% - 32px));background:#fff;border:1px solid #dce7e1;border-radius:22px;padding:32px;box-sizing:border-box;box-shadow:0 18px 55px rgba(17,54,40,.12)}h1{margin:0;color:#16835b;font-size:30px}p{color:#62736c}.login-username,.login-password{display:grid;gap:7px;margin:16px 0}label{font-weight:700}input[type=text],input[type=password]{padding:13px;border:1px solid #cddbd4;border-radius:10px;font-size:16px;width:100%;box-sizing:border-box}.login-submit input{width:100%;padding:13px;border:0;border-radius:10px;background:#16835b;color:#fff;font-size:16px;font-weight:800;cursor:pointer}.login-remember{font-size:14px}.brand{font-weight:900;color:#0b8fa3;letter-spacing:.06em}.note{font-size:13px;border-right:3px solid #0b8fa3;padding:8px 12px;background:#f0fafb;border-radius:8px}</style></head><body><main class="box"><div class="brand">I-BUILD</div><h1>قطاع التغذية</h1><p>ادخل بحسابك الخاص لفتح الشاشة والصلاحيات المخصصة لك.</p>
        <?php
        wp_login_form(array(
            'redirect' => $redirect,
            'label_username' => 'اسم المستخدم أو البريد الإلكتروني',
            'label_password' => 'كلمة المرور',
            'label_remember' => 'تذكرني',
            'label_log_in' => 'دخول النظام',
        ));
        ?>
        <div class="note">لا توجد كلمات مرور افتراضية ظاهرة. يحصل كل موظف على حساب مستقل من مدير النظام.</div></main></body></html><?php
    }

    private static function denied_page() {
        wp_die('هذا الحساب غير مصرح له بدخول نظام التغذية. تواصل مع مدير النظام.', 'غير مصرح', array('response' => 403));
    }

    private static function role_users($roles) {
        $result = array();
        foreach (get_users(array('role__in' => (array) $roles)) as $user) {
            $result[$user->user_login] = array(
                'name' => $user->display_name ?: $user->user_login,
                'site' => IBF_Auth::site($user->ID),
            );
        }
        return $result;
    }

    private static function boot() {
        $user = wp_get_current_user();
        return array(
            'version' => IBF_VERSION,
            'restUrl' => trailingslashit(rest_url(IBF_REST::NS)),
            'nonce' => wp_create_nonce('wp_rest'),
            'logoutUrl' => wp_logout_url(self::app_url()),
            'adminUrl' => admin_url('admin.php?page=ibuild-food-users'),
            'user' => IBF_Auth::context($user),
            'supervisors' => self::role_users(array('ibf_supervisor', 'ibf_storekeeper', 'ibf_purchasing', 'ibf_quality', 'ibf_hr', 'ibf_maintenance')),
            'chefs' => self::role_users(array('ibf_chef')),
        );
    }

    public static function render_if_app() {
        $page_id = (int) get_option(self::PAGE_OPTION);
        if (!$page_id || !is_page($page_id)) {
            return;
        }
        self::security_headers();
        if (!is_user_logged_in()) {
            self::login_page();
            exit;
        }
        if (!current_user_can('ibf_access_food')) {
            self::denied_page();
            exit;
        }
        if (!IBF_Auth::can_all_sites() && IBF_Auth::site() === '') {
            wp_die('هذا الحساب غير مربوط بموقع. اطلب من مدير النظام تحديد الموقع أولًا.', 'الموقع غير محدد', array('response' => 403));
        }

        $file = IBF_DIR . 'templates/app.php';
        if (!is_readable($file)) {
            wp_die('ملف واجهة النظام غير موجود.', 'I-BUILD Food', array('response' => 500));
        }
        ob_start();
        include $file;
        $html = ob_get_clean();
        $html = str_replace('%%IBF_XLSX_URL%%', esc_url(IBF_URL . 'assets/xlsx.full.min.js'), $html);
        $html = str_replace('%%IBF_QRCODE_URL%%', esc_url(IBF_URL . 'assets/qrcode.min.js'), $html);
        $html = str_replace('%%IBF_HTML5_QRCODE_URL%%', esc_url(IBF_URL . 'assets/html5-qrcode.min.js'), $html);
        $boot = wp_json_encode(self::boot(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        $injection = '<script>window.IBF_BOOT=' . $boot . ';</script>' . "\n<script>\n// ===== SUPERVISORS =====";
        $html = str_replace("<script>\n// ===== SUPERVISORS =====", $injection, $html);
        nocache_headers();
        header('Content-Type: text/html; charset=' . get_option('blog_charset'));
        echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        exit;
    }
}
