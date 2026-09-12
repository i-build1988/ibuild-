<?php

if (!defined('ABSPATH')) {
    exit;
}

final class IBF_Admin {
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'menu'));
        add_action('admin_post_ibf_create_user', array(__CLASS__, 'create_user'));
        add_action('admin_post_ibf_update_user', array(__CLASS__, 'update_user'));
    }

    public static function menu() {
        add_menu_page('I-BUILD Food', 'I-BUILD Food', 'ibf_manage_users', 'ibuild-food-users', array(__CLASS__, 'page'), 'dashicons-food', 27);
        add_submenu_page('ibuild-food-users', 'المستخدمون والصلاحيات', 'المستخدمون والصلاحيات', 'ibf_manage_users', 'ibuild-food-users', array(__CLASS__, 'page'));
        add_submenu_page('ibuild-food-users', 'فتح النظام', 'فتح النظام', 'ibf_access_food', 'ibuild-food-open', array(__CLASS__, 'open_page'));
    }

    private static function ensure_manage() {
        if (!current_user_can('ibf_manage_users') && !current_user_can('manage_options')) {
            wp_die('غير مصرح.');
        }
    }

    public static function open_page() {
        wp_safe_redirect(IBF_App::app_url());
        exit;
    }

    private static function roles() {
        $roles = array();
        foreach (IBF_Auth::role_definitions() as $key => $definition) {
            $roles[$key] = $definition[0];
        }
        return $roles;
    }

    public static function create_user() {
        self::ensure_manage();
        check_admin_referer('ibf_create_user');
        $login = sanitize_user(wp_unslash($_POST['username'] ?? ''), true);
        $name = sanitize_text_field(wp_unslash($_POST['display_name'] ?? ''));
        $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
        $password = (string) wp_unslash($_POST['password'] ?? '');
        $role = sanitize_key(wp_unslash($_POST['role'] ?? ''));
        $site = sanitize_text_field(wp_unslash($_POST['site'] ?? ''));
        if (!$login || strlen($password) < 10 || !isset(self::roles()[$role])) {
            wp_safe_redirect(add_query_arg('ibf_error', 'invalid', admin_url('admin.php?page=ibuild-food-users')));
            exit;
        }
        $user_id = wp_insert_user(array(
            'user_login' => $login,
            'user_pass' => $password,
            'user_email' => $email,
            'display_name' => $name ?: $login,
            'role' => $role,
        ));
        if (is_wp_error($user_id)) {
            wp_safe_redirect(add_query_arg('ibf_error', rawurlencode($user_id->get_error_message()), admin_url('admin.php?page=ibuild-food-users')));
            exit;
        }
        update_user_meta($user_id, IBF_Auth::USER_SITE, $site);
        update_user_meta($user_id, IBF_Auth::USER_PERMISSIONS, array(
            'can_submit_meals' => !empty($_POST['can_submit_meals']),
            'can_edit_inventory' => !empty($_POST['can_edit_inventory']) || $role === 'ibf_storekeeper',
            'can_add_invoice' => !empty($_POST['can_add_invoice']) || $role === 'ibf_purchasing',
            'can_manage_quality' => !empty($_POST['can_manage_quality']) || in_array($role, array('ibf_supervisor', 'ibf_quality'), true),
            'can_manage_hr' => !empty($_POST['can_manage_hr']) || in_array($role, array('ibf_supervisor', 'ibf_hr'), true),
            'can_manage_equipment' => !empty($_POST['can_manage_equipment']) || in_array($role, array('ibf_supervisor', 'ibf_maintenance'), true),
            'can_transfer_stock' => !empty($_POST['can_transfer_stock']),
        ));
        IBF_DB::audit(get_current_user_id(), 'create_user', 'user', (string) $user_id, $site, array('role' => $role));
        wp_safe_redirect(add_query_arg('ibf_created', '1', admin_url('admin.php?page=ibuild-food-users')));
        exit;
    }

    public static function update_user() {
        self::ensure_manage();
        $user_id = absint($_POST['user_id'] ?? 0);
        check_admin_referer('ibf_update_user_' . $user_id);
        $user = get_user_by('id', $user_id);
        $role = sanitize_key(wp_unslash($_POST['role'] ?? ''));
        $site = sanitize_text_field(wp_unslash($_POST['site'] ?? ''));
        if (!$user || !isset(self::roles()[$role])) {
            wp_safe_redirect(admin_url('admin.php?page=ibuild-food-users'));
            exit;
        }
        if (user_can($user, 'manage_options') && !current_user_can('manage_options')) {
            wp_die('لا يمكن لمدير نظام التغذية تعديل حساب مدير ووردبريس.', 'غير مصرح', array('response' => 403));
        }
        $current_role = IBF_Auth::primary_role($user);
        if ($current_role === '' && !current_user_can('manage_options')) {
            wp_die('هذا الحساب خارج نظام التغذية.', 'غير مصرح', array('response' => 403));
        }
        $user->set_role($role);
        update_user_meta($user_id, IBF_Auth::USER_SITE, $site);
        update_user_meta($user_id, IBF_Auth::USER_PERMISSIONS, array(
            'can_submit_meals' => !empty($_POST['can_submit_meals']),
            'can_edit_inventory' => !empty($_POST['can_edit_inventory']) || $role === 'ibf_storekeeper',
            'can_add_invoice' => !empty($_POST['can_add_invoice']) || $role === 'ibf_purchasing',
            'can_manage_quality' => !empty($_POST['can_manage_quality']),
            'can_manage_hr' => !empty($_POST['can_manage_hr']),
            'can_manage_equipment' => !empty($_POST['can_manage_equipment']),
            'can_transfer_stock' => !empty($_POST['can_transfer_stock']),
        ));
        IBF_DB::audit(get_current_user_id(), 'update_user', 'user', (string) $user_id, $site, array('role' => $role));
        wp_safe_redirect(add_query_arg('ibf_updated', '1', admin_url('admin.php?page=ibuild-food-users')));
        exit;
    }

    private static function role_options($selected = '') {
        foreach (self::roles() as $key => $label) {
            echo '<option value="' . esc_attr($key) . '" ' . selected($selected, $key, false) . '>' . esc_html($label) . '</option>';
        }
    }

    public static function page() {
        self::ensure_manage();
        $users = get_users(array('role__in' => array_keys(self::roles()), 'orderby' => 'display_name'));
        ?>
        <div class="wrap" dir="rtl"><h1>I-BUILD Food — المستخدمون والصلاحيات</h1>
        <?php if (!empty($_GET['ibf_created'])) : ?><div class="notice notice-success"><p>تم إنشاء المستخدم بنجاح.</p></div><?php endif; ?>
        <?php if (!empty($_GET['ibf_updated'])) : ?><div class="notice notice-success"><p>تم تحديث الصلاحيات.</p></div><?php endif; ?>
        <?php if (!empty($_GET['ibf_error'])) : ?><div class="notice notice-error"><p>تعذر الحفظ. راجع اسم المستخدم وكلمة المرور.</p></div><?php endif; ?>
        <p><a class="button button-primary" href="<?php echo esc_url(IBF_App::app_url()); ?>">فتح نظام التغذية</a></p>
        <div style="max-width:1050px;background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:20px;margin:18px 0">
        <h2>إضافة مستخدم</h2><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="ibf_create_user"><?php wp_nonce_field('ibf_create_user'); ?>
        <table class="form-table"><tr><th>اسم المستخدم</th><td><input required name="username" class="regular-text" autocomplete="off"></td></tr>
        <tr><th>الاسم</th><td><input required name="display_name" class="regular-text"></td></tr>
        <tr><th>البريد</th><td><input type="email" name="email" class="regular-text"></td></tr>
        <tr><th>كلمة المرور</th><td><input required minlength="10" type="password" name="password" class="regular-text" autocomplete="new-password"><p class="description">10 أحرف على الأقل، ولا تُرسلها في رسائل غير آمنة.</p></td></tr>
        <tr><th>الدور</th><td><select name="role"><?php self::role_options('ibf_supervisor'); ?></select></td></tr>
        <tr><th>الموقع</th><td><input name="site" class="regular-text" placeholder="مثال: الأقصر"></td></tr>
        <tr><th>صلاحيات إضافية</th><td><label><input type="checkbox" name="can_submit_meals" checked> تسجيل الوجبات</label> &nbsp; <label><input type="checkbox" name="can_edit_inventory"> تعديل المخزن</label> &nbsp; <label><input type="checkbox" name="can_add_invoice"> الفواتير وطلبات الشراء</label> &nbsp; <label><input type="checkbox" name="can_manage_quality"> الجودة والصور</label> &nbsp; <label><input type="checkbox" name="can_manage_hr"> HR والحضور</label> &nbsp; <label><input type="checkbox" name="can_manage_equipment"> المعدات والصيانة</label> &nbsp; <label><input type="checkbox" name="can_transfer_stock"> تحويل المخزون</label></td></tr></table>
        <?php submit_button('إنشاء المستخدم'); ?></form></div>
        <h2>المستخدمون الحاليون</h2>
        <div style="display:grid;gap:12px;max-width:1050px">
        <?php foreach ($users as $user) : $perms = IBF_Auth::permissions($user); ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:15px;display:grid;grid-template-columns:1.2fr 1fr 1fr 2fr auto;gap:12px;align-items:center">
        <input type="hidden" name="action" value="ibf_update_user"><input type="hidden" name="user_id" value="<?php echo esc_attr($user->ID); ?>"><?php wp_nonce_field('ibf_update_user_' . $user->ID); ?>
        <div><strong><?php echo esc_html($user->display_name); ?></strong><br><code><?php echo esc_html($user->user_login); ?></code></div>
        <select name="role"><?php self::role_options(IBF_Auth::primary_role($user)); ?></select>
        <input name="site" value="<?php echo esc_attr(IBF_Auth::site($user->ID)); ?>" placeholder="الموقع">
        <div><label><input type="checkbox" name="can_submit_meals" <?php checked($perms['can_submit_meals']); ?>> وجبات</label> &nbsp; <label><input type="checkbox" name="can_edit_inventory" <?php checked($perms['can_edit_inventory']); ?>> مخزن</label> &nbsp; <label><input type="checkbox" name="can_add_invoice" <?php checked($perms['can_add_invoice']); ?>> مشتريات</label> &nbsp; <label><input type="checkbox" name="can_manage_quality" <?php checked($perms['can_manage_quality']); ?>> جودة</label> &nbsp; <label><input type="checkbox" name="can_manage_hr" <?php checked($perms['can_manage_hr']); ?>> HR</label> &nbsp; <label><input type="checkbox" name="can_manage_equipment" <?php checked($perms['can_manage_equipment']); ?>> معدات وصيانة</label> &nbsp; <label><input type="checkbox" name="can_transfer_stock" <?php checked($perms['can_transfer_stock']); ?>> تحويل مخزون</label></div>
        <button class="button">حفظ</button></form>
        <?php endforeach; ?>
        </div></div>
        <?php
    }
}
