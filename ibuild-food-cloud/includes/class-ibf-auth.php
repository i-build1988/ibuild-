<?php

if (!defined('ABSPATH')) {
    exit;
}

final class IBF_Auth {
    const USER_SITE = 'ibf_site';
    const USER_SITES = 'ibf_sites';
    const USER_PERMISSIONS = 'ibf_permissions';

    public static function all_caps() {
        return array(
            'ibf_access_food',
            'ibf_view_all_sites',
            'ibf_submit_meals',
            'ibf_approve_meals',
            'ibf_manage_inventory',
            'ibf_manage_purchases',
            'ibf_approve_purchases',
            'ibf_manage_cashbox',
            'ibf_manage_recipes',
            'ibf_manage_quality',
            'ibf_manage_hr',
            'ibf_manage_equipment',
            'ibf_transfer_stock',
            'ibf_review_sites',
            'ibf_view_costs',
            'ibf_view_audit_log',
            'ibf_manage_users',
            'ibf_export_data',
            'ibf_delete_records',
        );
    }

    public static function role_definitions() {
        $access = array('read' => true, 'ibf_access_food' => true);
        $all = array_fill_keys(self::all_caps(), true);
        $all['read'] = true;

        return array(
            'ibf_manager' => array('مدير نظام التغذية', $all),
            'ibf_executive' => array('موظف الإدارة العليا', array_merge($access, array('ibf_view_all_sites' => true, 'ibf_transfer_stock' => true, 'ibf_export_data' => true))),
            'ibf_supervisor' => array('مشرف موقع التغذية', array_merge($access, array('ibf_submit_meals' => true, 'ibf_manage_quality' => true, 'ibf_manage_hr' => true, 'ibf_manage_equipment' => true))),
            'ibf_chef' => array('شيف قطاع التغذية', array_merge($access, array('ibf_approve_meals' => true, 'ibf_manage_recipes' => true))),
            'ibf_storekeeper' => array('أمين مخزن التغذية', array_merge($access, array('ibf_manage_inventory' => true, 'ibf_approve_meals' => true))),
            'ibf_purchasing' => array('مسؤول مشتريات التغذية', array_merge($access, array('ibf_manage_purchases' => true, 'ibf_approve_purchases' => true))),
            'ibf_cashier' => array('مسؤول خزنة التغذية', array_merge($access, array('ibf_manage_cashbox' => true))),
            'ibf_quality' => array('مسؤول جودة التغذية', array_merge($access, array('ibf_manage_quality' => true))),
            'ibf_hr' => array('مسؤول الموارد البشرية', array_merge($access, array('ibf_manage_hr' => true))),
            'ibf_maintenance' => array('مسؤول المعدات والصيانة', array_merge($access, array('ibf_manage_equipment' => true))),
            'ibf_reviewer' => array('مراجع المدارس', array_merge($access, array(
                'ibf_review_sites' => true,
                'ibf_view_costs' => true,
                'ibf_view_audit_log' => true,
                'ibf_manage_users' => true,
                'ibf_export_data' => true,
            ))),
            'ibf_viewer' => array('مشاهدة تقارير التغذية', $access),
        );
    }

    public static function activate() {
        foreach (self::role_definitions() as $key => $definition) {
            remove_role($key);
            add_role($key, $definition[0], $definition[1]);
        }
        $admin = get_role('administrator');
        if ($admin) {
            foreach (self::all_caps() as $cap) {
                $admin->add_cap($cap);
            }
        }
    }

    public static function primary_role($user = null) {
        $user = $user ?: wp_get_current_user();
        $roles = (array) $user->roles;
        foreach (array_keys(self::role_definitions()) as $role) {
            if (in_array($role, $roles, true)) {
                return $role;
            }
        }
        return in_array('administrator', $roles, true) ? 'administrator' : '';
    }

    public static function ui_role($user = null) {
        $role = self::primary_role($user);
        if (in_array($role, array('administrator', 'ibf_manager', 'ibf_executive'), true)) {
            return 'manager';
        }
        if ($role === 'ibf_chef') {
            return 'chef';
        }
        if ($role === 'ibf_reviewer') {
            return 'reviewer';
        }
        return 'supervisor';
    }

    public static function site($user_id = 0) {
        $user_id = $user_id ?: get_current_user_id();
        return sanitize_text_field((string) get_user_meta($user_id, self::USER_SITE, true));
    }

    /**
     * المواقع التي يُصرَّح لهذا الحساب بالاطلاع عليها.
     * المراجع يُسنِد له المدير أكثر من مدرسة، فتُخزَّن كقائمة في ibf_sites.
     * الحسابات الأخرى تبقى على موقع واحد في ibf_site.
     */
    public static function sites($user_id = 0) {
        $user_id = $user_id ?: get_current_user_id();
        $stored = get_user_meta($user_id, self::USER_SITES, true);
        $list = array();
        foreach ((array) $stored as $site) {
            $site = sanitize_text_field((string) $site);
            if ($site !== '' && !in_array($site, $list, true)) {
                $list[] = $site;
            }
        }
        $primary = self::site($user_id);
        if ($primary !== '' && !in_array($primary, $list, true)) {
            array_unshift($list, $primary);
        }
        return $list;
    }

    public static function can_all_sites() {
        return current_user_can('ibf_view_all_sites') || current_user_can('manage_options');
    }

    /**
     * هل يقرأ هذا الحساب بيانات الموقع المطلوب؟
     * السجل بلا موقع (بيانات عامة) متاح للجميع كما كان.
     */
    public static function can_read_site($site) {
        $site = sanitize_text_field((string) $site);
        if ($site === '' || self::can_all_sites()) {
            return true;
        }
        return in_array($site, self::sites(), true);
    }

    public static function permissions($user = null) {
        $user = $user ?: wp_get_current_user();
        $stored = get_user_meta($user->ID, self::USER_PERMISSIONS, true);
        $stored = is_array($stored) ? $stored : array();
        $primary_role = self::primary_role($user);
        $manager = in_array($primary_role, array('administrator', 'ibf_manager'), true) || user_can($user, 'manage_options');
        $permission = static function ($key, $cap) use ($stored, $user) {
            return array_key_exists($key, $stored) ? !empty($stored[$key]) : user_can($user, $cap);
        };

        return array(
            'can_submit_meals' => $manager || $permission('can_submit_meals', 'ibf_submit_meals'),
            'can_edit_inventory' => $manager || $permission('can_edit_inventory', 'ibf_manage_inventory'),
            'can_add_invoice' => $manager || $permission('can_add_invoice', 'ibf_manage_purchases'),
            'can_approve_purchases' => $manager || user_can($user, 'ibf_approve_purchases'),
            'can_manage_cashbox' => $manager || user_can($user, 'ibf_manage_cashbox'),
            'can_approve_meals' => $manager || user_can($user, 'ibf_approve_meals'),
            'can_manage_recipes' => $manager || user_can($user, 'ibf_manage_recipes'),
            'can_manage_quality' => $manager || $permission('can_manage_quality', 'ibf_manage_quality'),
            'can_manage_hr' => $manager || $permission('can_manage_hr', 'ibf_manage_hr'),
            'can_manage_equipment' => $manager || $permission('can_manage_equipment', 'ibf_manage_equipment'),
            'can_transfer_stock' => $manager || $permission('can_transfer_stock', 'ibf_transfer_stock'),
            'can_manage_users' => $manager || user_can($user, 'ibf_manage_users'),
            'can_view_all_sites' => $manager || user_can($user, 'ibf_view_all_sites'),
            'can_review_sites' => $manager || $permission('can_review_sites', 'ibf_review_sites'),
            'can_view_costs' => $manager || $permission('can_view_costs', 'ibf_view_costs'),
            'can_view_audit_log' => $manager || $permission('can_view_audit_log', 'ibf_view_audit_log'),
        );
    }

    public static function context($user = null) {
        $user = $user ?: wp_get_current_user();
        return array(
            'id' => (int) $user->ID,
            'username' => $user->user_login,
            'name' => $user->display_name ?: $user->user_login,
            'role' => self::ui_role($user),
            'roleKey' => self::primary_role($user),
            'site' => self::site($user->ID),
            'sites' => self::sites($user->ID),
            'permissions' => self::permissions($user),
        );
    }
}
