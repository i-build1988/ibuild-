<?php

if (!defined('ABSPATH')) {
    exit;
}

final class IBF_REST {
    const NS = 'ibuild-food/v1';

    private static $types = array(
        'meal_logs', 'inventory', 'invoices', 'menus', 'cashbox_accounts',
        'cashbox_transactions', 'suppliers', 'purchase_requests', 'returns_waste',
        'sites', 'warehouses', 'ration_standards', 'app_permissions',
        'quality_checks', 'employees', 'attendance', 'equipment', 'maintenance_logs',
        'stock_transfers',
    );

    private static $snapshot_types = array(
        'suppliers', 'purchase_requests', 'returns_waste', 'sites', 'warehouses', 'ration_standards',
    );

    public static function init() {
        add_action('rest_api_init', array(__CLASS__, 'routes'));
    }

    public static function routes() {
        register_rest_route(self::NS, '/query', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'query'),
            'permission_callback' => array(__CLASS__, 'can_access'),
        ));
        register_rest_route(self::NS, '/snapshot', array(
            array('methods' => 'GET', 'callback' => array(__CLASS__, 'snapshot_get'), 'permission_callback' => array(__CLASS__, 'can_access')),
            array('methods' => 'POST', 'callback' => array(__CLASS__, 'snapshot_save'), 'permission_callback' => array(__CLASS__, 'can_access')),
        ));
        register_rest_route(self::NS, '/export', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'export'),
            'permission_callback' => static function () {
                return current_user_can('ibf_export_data') || current_user_can('manage_options');
            },
        ));
        register_rest_route(self::NS, '/users', array(
            array(
                'methods' => 'GET',
                'callback' => array(__CLASS__, 'users_get'),
                'permission_callback' => array(__CLASS__, 'can_manage_users'),
            ),
            array(
                'methods' => 'POST',
                'callback' => array(__CLASS__, 'users_create'),
                'permission_callback' => array(__CLASS__, 'can_manage_users'),
            ),
            array(
                'methods' => 'DELETE',
                'callback' => array(__CLASS__, 'users_delete'),
                'permission_callback' => array(__CLASS__, 'can_manage_users'),
            ),
        ));
        register_rest_route(self::NS, '/evidence', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'evidence_upload'),
            'permission_callback' => array(__CLASS__, 'can_upload_evidence'),
        ));
        register_rest_route(self::NS, '/warehouse-issue', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'warehouse_issue'),
            'permission_callback' => array(__CLASS__, 'can_access'),
        ));
        register_rest_route(self::NS, '/audit-log', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'audit_log'),
            'permission_callback' => array(__CLASS__, 'can_view_audit_log'),
        ));
        register_rest_route(self::NS, '/chef-decision', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'chef_decision'),
            'permission_callback' => array(__CLASS__, 'can_access'),
        ));
    }

    /**
     * سجل المراجعة: من فعل ماذا ومتى.
     * المراجع يقرأ مدارسه فقط؛ المدير والإدارة العليا يقرأون كل المواقع.
     */
    public static function audit_log(WP_REST_Request $request) {
        $sites = IBF_Auth::can_all_sites() ? null : IBF_Auth::sites();
        $requested_site = sanitize_text_field((string) $request->get_param('site'));
        if ($requested_site !== '' && !IBF_Auth::can_read_site($requested_site)) {
            return new WP_Error('ibf_audit_site_forbidden', 'هذا الموقع خارج نطاق مراجعتك.', array('status' => 403));
        }

        $from = sanitize_text_field((string) $request->get_param('from'));
        $to = sanitize_text_field((string) $request->get_param('to'));
        foreach (array($from, $to) as $date) {
            if ($date !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                return new WP_Error('ibf_audit_date_invalid', 'صيغة التاريخ غير صالحة.', array('status' => 400));
            }
        }
        if ($from !== '' && $to !== '' && $from > $to) {
            return new WP_Error('ibf_audit_range_invalid', 'تاريخ البداية بعد تاريخ النهاية.', array('status' => 400));
        }

        $result = IBF_DB::audit_rows(array(
            'sites' => $sites,
            'site' => $requested_site,
            'from' => $from,
            'to' => $to,
            'actor_id' => (int) $request->get_param('actor'),
            'action' => (string) $request->get_param('action'),
            'record_type' => (string) $request->get_param('type'),
            'limit' => (int) $request->get_param('limit') ?: 500,
            'offset' => (int) $request->get_param('offset'),
        ));

        $names = array();
        $entries = array();
        foreach ($result['rows'] as $row) {
            $actor_id = (int) $row['actor_id'];
            if ($actor_id && !array_key_exists($actor_id, $names)) {
                $actor = get_user_by('id', $actor_id);
                $names[$actor_id] = $actor ? ($actor->display_name ?: $actor->user_login) : 'حساب محذوف #' . $actor_id;
            }
            $entries[] = array(
                'id' => (int) $row['id'],
                'actorId' => $actor_id,
                'actorName' => $actor_id ? $names[$actor_id] : 'النظام',
                'action' => $row['action'],
                'actionLabel' => self::audit_action_label($row['action']),
                'recordType' => $row['record_type'],
                'recordLabel' => self::audit_type_label($row['record_type']),
                'externalId' => $row['external_id'],
                'site' => $row['site'],
                'details' => $row['details'],
                'createdAt' => $row['created_at'],
            );
        }

        return rest_ensure_response(array(
            'data' => $entries,
            'total' => $result['total'],
            'scope' => $sites === null ? 'all' : $sites,
            'error' => null,
        ));
    }

    private static function audit_action_label($action) {
        $labels = array(
            'save' => 'حفظ',
            'insert' => 'إضافة',
            'update' => 'تعديل',
            'upsert' => 'حفظ أو تعديل',
            'delete' => 'حذف',
            'chef_decision' => 'قرار الشيف',
            'warehouse_issue' => 'اعتماد صرف المخزن',
            'create_user' => 'إنشاء مستخدم',
            'delete_user' => 'حذف مستخدم',
            'disable_user' => 'تعطيل مستخدم',
            'permissions' => 'تغيير صلاحيات',
        );
        return isset($labels[$action]) ? $labels[$action] : $action;
    }

    private static function audit_type_label($type) {
        $labels = array(
            'meal_logs' => 'صرف الوجبات',
            'inventory' => 'المخزن',
            'invoices' => 'الفواتير',
            'menus' => 'المنيو والريسيبي',
            'cashbox_accounts' => 'حسابات الخزنة',
            'cashbox_transactions' => 'حركات الخزنة',
            'suppliers' => 'الموردون',
            'purchase_requests' => 'طلبات الشراء',
            'returns_waste' => 'المرتجعات والهدر',
            'sites' => 'المواقع',
            'warehouses' => 'المخازن',
            'ration_standards' => 'المقننات',
            'app_permissions' => 'الصلاحيات',
            'quality_checks' => 'فحوصات الجودة',
            'employees' => 'العاملون',
            'attendance' => 'الحضور والانصراف',
            'equipment' => 'المعدات',
            'maintenance_logs' => 'سجل الصيانة',
            'stock_transfers' => 'تحويلات المخزون',
            'user' => 'حساب مستخدم',
        );
        return isset($labels[$type]) ? $labels[$type] : $type;
    }

    public static function chef_decision(WP_REST_Request $request) {
        global $wpdb;
        $permissions = IBF_Auth::permissions();
        if (empty($permissions['can_approve_meals']) && !IBF_Auth::can_all_sites()) {
            return new WP_Error('ibf_chef_forbidden', 'ليس لديك صلاحية اعتماد طلبات الصرف.', array('status' => 403));
        }
        $body = (array) $request->get_json_params();
        $id = sanitize_text_field((string) ($body['id'] ?? ''));
        $decision = sanitize_key((string) ($body['decision'] ?? ''));
        $notes = sanitize_textarea_field((string) ($body['notes'] ?? ''));
        if ($id === '' || !in_array($decision, array('approved', 'rejected'), true)) {
            return new WP_Error('ibf_chef_invalid', 'قرار الشيف غير صالح.', array('status' => 400));
        }
        if ($decision === 'rejected' && $notes === '') {
            return new WP_Error('ibf_chef_notes_required', 'اكتب سبب الرفض أولاً.', array('status' => 400));
        }
        $table = IBF_DB::records_table();
        $wpdb->query('START TRANSACTION');
        try {
            $row = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table} WHERE record_type='meal_logs' AND external_id=%s FOR UPDATE",
                $id
            ), ARRAY_A);
            if (!$row) {
                throw new Exception('طلب الصرف غير موجود.');
            }
            $log = IBF_DB::decode($row['payload']);
            $site = sanitize_text_field((string) ($log['site'] ?? ''));
            if (!IBF_Auth::can_all_sites() && $site !== IBF_Auth::site()) {
                throw new Exception('طلب الصرف تابع لموقع آخر.');
            }
            if (!empty($log['chef_status'])) {
                throw new Exception('تم اتخاذ قرار على هذا الطلب من قبل.');
            }
            $user = wp_get_current_user();
            $log['chef_status'] = $decision;
            $log['chef_notes'] = $notes;
            $log['chef_name'] = $user->display_name ?: $user->user_login;
            $log['chef_time'] = current_time('mysql');
            $log['warehouse_approved'] = false;
            if (!IBF_DB::save('meal_logs', $id, $log, $site, (string) ($log['log_date'] ?? ''), get_current_user_id(), 'chef_decision')) {
                throw new Exception('تعذر حفظ قرار الشيف.');
            }
            $wpdb->query('COMMIT');
            return rest_ensure_response(array('data' => $log, 'error' => null));
        } catch (Throwable $error) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('ibf_chef_decision_failed', $error->getMessage(), array('status' => 409));
        }
    }

    public static function warehouse_issue(WP_REST_Request $request) {
        global $wpdb;
        $permissions = IBF_Auth::permissions();
        if (empty($permissions['can_edit_inventory']) && !IBF_Auth::can_all_sites()) {
            return new WP_Error('ibf_warehouse_forbidden', 'ليس لديك صلاحية اعتماد صرف المخزن.', array('status' => 403));
        }
        $body = $request->get_json_params();
        $log_id = sanitize_text_field((string) ($body['id'] ?? ''));
        if ($log_id === '') {
            return new WP_Error('ibf_warehouse_missing', 'رقم طلب الصرف غير صالح.', array('status' => 400));
        }
        $table = IBF_DB::records_table();
        $wpdb->query('START TRANSACTION');
        try {
            $log_row = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table} WHERE record_type='meal_logs' AND external_id=%s FOR UPDATE",
                $log_id
            ), ARRAY_A);
            if (!$log_row) {
                throw new Exception('طلب الصرف غير موجود.');
            }
            $log = IBF_DB::decode($log_row['payload']);
            $site = sanitize_text_field((string) ($log['site'] ?? ''));
            if (!IBF_Auth::can_all_sites() && $site !== IBF_Auth::site()) {
                throw new Exception('طلب الصرف تابع لموقع آخر.');
            }
            if (($log['chef_status'] ?? '') !== 'approved') {
                throw new Exception('يجب اعتماد الشيف أولاً.');
            }
            if (!empty($log['inventory_posted']) || ($log['warehouse_status'] ?? '') === 'approved') {
                throw new Exception('تم خصم طلب الصرف من قبل.');
            }
            if (($log['warehouse_status'] ?? '') === 'rejected') {
                throw new Exception('طلب الصرف مرفوض من المخزن.');
            }
            $date = sanitize_text_field((string) ($log['log_date'] ?? current_time('Y-m-d')));
            $required = array();
            foreach ((array) ($log['items'] ?? array()) as $item) {
                $name = sanitize_text_field((string) ($item['name'] ?? ''));
                $qty = (float) ($item['qty'] ?? 0);
                if ($name === '' || $qty <= 0) {
                    throw new Exception('طلب الصرف يحتوي على صنف أو كمية غير صالحة.');
                }
                if (!isset($required[$name])) {
                    $required[$name] = array('qty' => 0, 'unit' => sanitize_text_field((string) ($item['unit'] ?? 'قطعة')));
                }
                $required[$name]['qty'] += $qty;
            }
            if (!$required) {
                throw new Exception('طلب الصرف لا يحتوي على أصناف.');
            }
            $updates = array();
            foreach ($required as $name => $need) {
                $inventory_row = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$table} WHERE record_type='inventory' AND site=%s AND record_date=%s AND JSON_UNQUOTE(JSON_EXTRACT(payload, '$.item_name'))=%s LIMIT 1 FOR UPDATE",
                    $site, $date, $name
                ), ARRAY_A);
                $inventory = $inventory_row ? IBF_DB::decode($inventory_row['payload']) : array();
                if (!$inventory_row) {
                    $prior = $wpdb->get_row($wpdb->prepare(
                        "SELECT * FROM {$table} WHERE record_type='inventory' AND site=%s AND record_date<%s AND JSON_UNQUOTE(JSON_EXTRACT(payload, '$.item_name'))=%s ORDER BY record_date DESC,id DESC LIMIT 1 FOR UPDATE",
                        $site, $date, $name
                    ), ARRAY_A);
                    $prior_data = $prior ? IBF_DB::decode($prior['payload']) : array();
                    $opening = (float) ($prior_data['closing_stock'] ?? 0);
                    $inventory = array(
                        'id' => 'INV-' . wp_generate_uuid4(), 'site' => $site, 'item_name' => $name,
                        'unit' => $need['unit'], 'price' => (float) ($prior_data['price'] ?? 0),
                        'opening_stock' => $opening, 'additions' => 0, 'dispensed' => 0,
                        'transfer_in' => 0, 'transfer_out' => 0, 'stock_date' => $date,
                    );
                }
                $available = (float) ($inventory['opening_stock'] ?? 0) + (float) ($inventory['additions'] ?? 0)
                    + (float) ($inventory['transfer_in'] ?? 0) - (float) ($inventory['dispensed'] ?? 0)
                    - (float) ($inventory['transfer_out'] ?? 0);
                if ($need['qty'] > $available + 0.000001) {
                    throw new Exception(sprintf('الرصيد غير كافٍ للصنف %s: المتاح %s والمطلوب %s.', $name, $available, $need['qty']));
                }
                $inventory['dispensed'] = (float) ($inventory['dispensed'] ?? 0) + $need['qty'];
                $inventory['closing_stock'] = $available - $need['qty'];
                $inventory['last_updated'] = current_time('mysql');
                $updates[] = $inventory;
            }
            foreach ($updates as $inventory) {
                $saved = IBF_DB::save('inventory', (string) $inventory['id'], $inventory, $site, $date, get_current_user_id(), 'warehouse_issue');
                if (!$saved) {
                    throw new Exception('تعذر تحديث رصيد المخزن.');
                }
            }
            $user = wp_get_current_user();
            $log['inventory_posted'] = true;
            $log['warehouse_approved'] = true;
            $log['warehouse_status'] = 'approved';
            $log['warehouse_name'] = $user->display_name ?: $user->user_login;
            $log['warehouse_time'] = current_time('mysql');
            if (!IBF_DB::save('meal_logs', $log_id, $log, $site, $date, get_current_user_id(), 'warehouse_issue')) {
                throw new Exception('تعذر حفظ اعتماد أمين المخزن.');
            }
            $wpdb->query('COMMIT');
            return rest_ensure_response(array('data' => $log, 'error' => null));
        } catch (Throwable $error) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('ibf_warehouse_issue_failed', $error->getMessage(), array('status' => 409));
        }
    }

    public static function can_access() {
        if (!is_user_logged_in() || !current_user_can('ibf_access_food')) {
            return false;
        }
        return IBF_Auth::can_all_sites() || IBF_Auth::sites() !== array();
    }

    public static function can_view_audit_log() {
        if (!self::can_access()) {
            return false;
        }
        $permissions = IBF_Auth::permissions();
        return !empty($permissions['can_view_audit_log']);
    }

    public static function can_manage_users() {
        return is_user_logged_in() && (current_user_can('ibf_manage_users') || current_user_can('manage_options'));
    }

    public static function can_upload_evidence() {
        if (!self::can_access()) {
            return false;
        }
        $permissions = IBF_Auth::permissions();
        return !empty($permissions['can_manage_quality']);
    }

    private static function user_record($user) {
        $role = IBF_Auth::primary_role($user);
        $definitions = IBF_Auth::role_definitions();
        $permissions = IBF_Auth::permissions($user);
        return array(
            'id' => (int) $user->ID,
            'username' => $user->user_login,
            'name' => $user->display_name ?: $user->user_login,
            'email' => $user->user_email,
            'role' => $role,
            'roleLabel' => isset($definitions[$role]) ? $definitions[$role][0] : $role,
            'site' => IBF_Auth::site($user->ID),
            'sites' => IBF_Auth::sites($user->ID),
            'registeredAt' => $user->user_registered,
            'permissions' => array(
                'can_submit_meals' => !empty($permissions['can_submit_meals']),
                'can_edit_inventory' => !empty($permissions['can_edit_inventory']),
                'can_add_invoice' => !empty($permissions['can_add_invoice']),
                'can_manage_cashbox' => !empty($permissions['can_manage_cashbox']),
                'can_manage_quality' => !empty($permissions['can_manage_quality']),
                'can_manage_hr' => !empty($permissions['can_manage_hr']),
                'can_manage_equipment' => !empty($permissions['can_manage_equipment']),
                'can_review_sites' => !empty($permissions['can_review_sites']),
                'can_view_costs' => !empty($permissions['can_view_costs']),
                'can_view_audit_log' => !empty($permissions['can_view_audit_log']),
                'can_manage_users' => !empty($permissions['can_manage_users']),
            ),
        );
    }

    public static function users_get() {
        $definitions = IBF_Auth::role_definitions();
        $users = get_users(array(
            'role__in' => array_keys($definitions),
            'orderby' => 'display_name',
            'order' => 'ASC',
        ));
        return rest_ensure_response(array(
            'data' => array_map(array(__CLASS__, 'user_record'), $users),
            'roles' => array_map(static function ($definition) {
                return $definition[0];
            }, $definitions),
            'error' => null,
        ));
    }

    public static function users_create(WP_REST_Request $request) {
        $body = $request->get_json_params();
        $body = is_array($body) ? $body : array();
        $login = sanitize_user((string) ($body['username'] ?? ''), true);
        $name = sanitize_text_field((string) ($body['display_name'] ?? ''));
        $raw_email = trim((string) ($body['email'] ?? ''));
        $email = sanitize_email($raw_email);
        $password = (string) ($body['password'] ?? '');
        $role = sanitize_key((string) ($body['role'] ?? ''));
        $site = sanitize_text_field((string) ($body['site'] ?? ''));
        $extra_sites = array();
        foreach ((array) ($body['sites'] ?? array()) as $extra) {
            $extra = sanitize_text_field((string) $extra);
            if ($extra !== '' && !in_array($extra, $extra_sites, true)) {
                $extra_sites[] = $extra;
            }
        }
        if ($role === 'ibf_reviewer' && $site === '' && $extra_sites) {
            $site = $extra_sites[0];
        }
        $definitions = IBF_Auth::role_definitions();

        if ($login === '' || $name === '') {
            return new WP_Error('ibf_user_required', 'اسم المستخدم واسم الموظف مطلوبان.', array('status' => 400));
        }
        if (strlen($password) < 10) {
            return new WP_Error('ibf_password_short', 'كلمة المرور يجب ألا تقل عن 10 أحرف.', array('status' => 400));
        }
        if (!isset($definitions[$role])) {
            return new WP_Error('ibf_role_invalid', 'الدور الوظيفي غير صالح.', array('status' => 400));
        }
        if (!in_array($role, array('ibf_manager', 'ibf_executive'), true) && $site === '') {
            return new WP_Error('ibf_site_required', 'حدد موقع الموظف قبل إنشاء الحساب.', array('status' => 400));
        }
        if ($role === 'ibf_reviewer' && !$extra_sites) {
            return new WP_Error('ibf_reviewer_sites_required', 'حدد المدارس التي يراجعها هذا الحساب.', array('status' => 400));
        }
        if ($raw_email !== '' && ($email === '' || !is_email($raw_email))) {
            return new WP_Error('ibf_email_invalid', 'البريد الإلكتروني غير صالح.', array('status' => 400));
        }
        if (username_exists($login)) {
            return new WP_Error('ibf_username_exists', 'اسم المستخدم مستخدم من قبل.', array('status' => 409));
        }
        if ($email !== '' && email_exists($email)) {
            return new WP_Error('ibf_email_exists', 'البريد الإلكتروني مستخدم من قبل.', array('status' => 409));
        }

        $user_id = wp_insert_user(array(
            'user_login' => $login,
            'user_pass' => $password,
            'user_email' => $email,
            'display_name' => $name,
            'role' => $role,
        ));
        if (is_wp_error($user_id)) {
            return new WP_Error('ibf_user_create_failed', $user_id->get_error_message(), array('status' => 400));
        }

        update_user_meta($user_id, IBF_Auth::USER_SITE, $site);
        update_user_meta($user_id, IBF_Auth::USER_SITES, $extra_sites);
        update_user_meta($user_id, IBF_Auth::USER_PERMISSIONS, array(
            'can_submit_meals' => !empty($body['can_submit_meals']),
            'can_edit_inventory' => !empty($body['can_edit_inventory']) || $role === 'ibf_storekeeper',
            'can_add_invoice' => !empty($body['can_add_invoice']) || $role === 'ibf_purchasing',
            'can_manage_quality' => !empty($body['can_manage_quality']) || in_array($role, array('ibf_supervisor', 'ibf_quality'), true),
            'can_manage_hr' => !empty($body['can_manage_hr']) || in_array($role, array('ibf_supervisor', 'ibf_hr'), true),
            'can_manage_equipment' => !empty($body['can_manage_equipment']) || in_array($role, array('ibf_supervisor', 'ibf_maintenance'), true),
            'can_review_sites' => $role === 'ibf_reviewer',
            'can_view_costs' => !empty($body['can_view_costs']) || $role === 'ibf_reviewer',
            'can_view_audit_log' => !empty($body['can_view_audit_log']) || $role === 'ibf_reviewer',
        ));
        IBF_DB::audit(get_current_user_id(), 'create_user', 'user', (string) $user_id, $site, array(
            'role' => $role,
            'sites' => $extra_sites,
            'source' => 'manager_dashboard',
        ));

        return new WP_REST_Response(array(
            'data' => self::user_record(get_user_by('id', $user_id)),
            'message' => 'تم إنشاء المستخدم بنجاح.',
            'error' => null,
        ), 201);
    }

    /**
     * حذف أو تعطيل مستخدم من داخل النظام.
     * mode=disable  : يسحب الدور والموقع والصلاحيات ويبقي الحساب وسجله (قابل للتراجع)
     * mode=delete   : يحذف حساب ووردبريس نهائيًا
     */
    public static function users_delete(WP_REST_Request $request) {
        $body = $request->get_json_params();
        $body = is_array($body) ? $body : array();
        $user_id = absint($body['id'] ?? 0);
        $mode = sanitize_key((string) ($body['mode'] ?? 'disable'));
        $actor_id = get_current_user_id();

        if (!$user_id) {
            return new WP_Error('ibf_user_missing', 'حدد المستخدم المراد حذفه.', array('status' => 400));
        }
        if ($user_id === $actor_id) {
            return new WP_Error('ibf_user_self', 'لا يمكنك حذف حسابك أنت.', array('status' => 403));
        }
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return new WP_Error('ibf_user_not_found', 'المستخدم غير موجود.', array('status' => 404));
        }
        // منع تصعيد الصلاحيات: مدير التغذية لا يملك حذف مديري الموقع
        if (user_can($user, 'manage_options') && !current_user_can('manage_options')) {
            return new WP_Error('ibf_user_protected', 'لا يمكن حذف حساب بصلاحيات إدارة الموقع.', array('status' => 403));
        }
        $definitions = IBF_Auth::role_definitions();
        $role = IBF_Auth::primary_role($user);
        if ($role !== '' && !isset($definitions[$role]) && !current_user_can('manage_options')) {
            return new WP_Error('ibf_user_outside', 'هذا الحساب خارج نظام التغذية.', array('status' => 403));
        }

        $snapshot = self::user_record($user);

        if ($mode === 'delete') {
            require_once ABSPATH . 'wp-admin/includes/user.php';
            $reassign = $actor_id !== $user_id ? $actor_id : null;
            $deleted = wp_delete_user($user_id, $reassign);
            if (!$deleted) {
                return new WP_Error('ibf_user_delete_failed', 'تعذر حذف الحساب.', array('status' => 500));
            }
            IBF_DB::audit($actor_id, 'delete_user', 'user', (string) $user_id, $snapshot['site'], array(
                'username' => $snapshot['username'], 'role' => $snapshot['role'], 'mode' => 'delete',
            ));
            return rest_ensure_response(array(
                'data' => $snapshot, 'mode' => 'delete',
                'message' => 'تم حذف الحساب نهائيًا.', 'error' => null,
            ));
        }

        // تعطيل: نزع كل أدوار النظام والصلاحيات والموقع
        foreach (array_keys($definitions) as $key) {
            $user->remove_role($key);
        }
        delete_user_meta($user_id, IBF_Auth::USER_SITE);
        delete_user_meta($user_id, IBF_Auth::USER_SITES);
        delete_user_meta($user_id, IBF_Auth::USER_PERMISSIONS);
        IBF_DB::audit($actor_id, 'disable_user', 'user', (string) $user_id, $snapshot['site'], array(
            'username' => $snapshot['username'], 'role' => $snapshot['role'], 'mode' => 'disable',
        ));
        return rest_ensure_response(array(
            'data' => $snapshot, 'mode' => 'disable',
            'message' => 'تم تعطيل وصول الحساب مع الاحتفاظ بسجله.', 'error' => null,
        ));
    }

    public static function evidence_upload() {
        if (empty($_FILES['file']) || !is_array($_FILES['file'])) {
            return new WP_Error('ibf_evidence_missing', 'اختر صورة قبل الرفع.', array('status' => 400));
        }
        $file = $_FILES['file'];
        if (!empty($file['error'])) {
            return new WP_Error('ibf_evidence_upload_error', 'تعذر استلام الصورة.', array('status' => 400));
        }
        if ((int) ($file['size'] ?? 0) > 8 * MB_IN_BYTES) {
            return new WP_Error('ibf_evidence_too_large', 'حجم الصورة يجب ألا يزيد عن 8 ميجابايت.', array('status' => 413));
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $checked = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);
        $allowed = array('image/jpeg', 'image/png', 'image/webp');
        if (empty($checked['type']) || !in_array($checked['type'], $allowed, true)) {
            return new WP_Error('ibf_evidence_type', 'الصور المسموحة: JPG أو PNG أو WebP.', array('status' => 415));
        }

        // media_handle_upload لا يفحص صلاحية الرفع بنفسه؛ الفحص مسؤولية المُستدعي.
        // أدوار التغذية لا تملك upload_files، فنمنحها الإذن لهذه العملية فقط ثم نسحبه.
        $grant_upload = static function ($caps, $cap) {
            if ($cap === 'upload_files') {
                $caps['upload_files'] = true;
            }
            return $caps;
        };
        add_filter('user_has_cap', $grant_upload, 10, 2);

        $_FILES['file']['name'] = sanitize_file_name($file['name']);
        $attachment_id = media_handle_upload('file', 0, array(
            'post_title' => 'I-BUILD Food Evidence ' . current_time('Y-m-d H:i'),
            'post_content' => '',
            'post_status' => 'inherit',
        ), array('test_form' => false));
        remove_filter('user_has_cap', $grant_upload, 10);

        if (is_wp_error($attachment_id)) {
            return new WP_Error('ibf_evidence_save', $attachment_id->get_error_message(), array('status' => 400));
        }

        update_post_meta($attachment_id, '_ibf_evidence_site', IBF_Auth::site());
        update_post_meta($attachment_id, '_ibf_evidence_owner', get_current_user_id());
        return new WP_REST_Response(array(
            'data' => array(
                'id' => (int) $attachment_id,
                'url' => wp_get_attachment_url($attachment_id),
                'name' => get_the_title($attachment_id),
                'mime' => get_post_mime_type($attachment_id),
            ),
            'error' => null,
        ), 201);
    }

    private static function valid_type($type) {
        return in_array($type, self::$types, true);
    }

    private static function user_site() {
        return IBF_Auth::site();
    }

    private static function date_from(array $data) {
        foreach (array('record_date', 'log_date', 'stock_date', 'invoice_date', 'transaction_date', 'service_date', 'requestDate', 'check_date', 'work_date', 'date') as $key) {
            if (!empty($data[$key]) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $data[$key])) {
                return $data[$key];
            }
        }
        return null;
    }

    private static function sanitize_payload($value) {
        if (is_array($value)) {
            $clean = array();
            foreach ($value as $key => $item) {
                $clean_key = is_int($key) ? $key : preg_replace('/[^A-Za-z0-9_\-]/', '', (string) $key);
                $clean[$clean_key] = self::sanitize_payload($item);
            }
            return $clean;
        }
        if (is_bool($value) || is_int($value) || is_float($value) || $value === null) {
            return $value;
        }
        return sanitize_textarea_field((string) $value);
    }

    private static function row_site(array $data) {
        if (!IBF_Auth::can_all_sites()) {
            return self::user_site();
        }
        return sanitize_text_field((string) ($data['site'] ?? ''));
    }

    private static function can_write($type, $action = 'save') {
        $permissions = IBF_Auth::permissions();
        if ($action === 'delete') {
            return current_user_can('ibf_delete_records') || current_user_can('manage_options');
        }
        if ($type === 'app_permissions') {
            return current_user_can('ibf_manage_users') || current_user_can('manage_options');
        }
        // تعريف المواقع والمخازن يظل للمدير: المراجع يملك إدارة المستخدمين لكنه لا ينشئ مدارس
        if (in_array($type, array('sites', 'warehouses'), true)) {
            return (current_user_can('ibf_manage_users') && IBF_Auth::can_all_sites()) || current_user_can('manage_options');
        }
        if ($type === 'stock_transfers') {
            return $action === 'insert' && (!empty($permissions['can_transfer_stock']) || IBF_Auth::can_all_sites());
        }
        if ($type === 'quality_checks') {
            return !empty($permissions['can_manage_quality']);
        }
        if (in_array($type, array('employees', 'attendance'), true)) {
            return !empty($permissions['can_manage_hr']);
        }
        if (in_array($type, array('equipment', 'maintenance_logs'), true)) {
            return !empty($permissions['can_manage_equipment']);
        }
        if ($type === 'returns_waste') {
            return !empty($permissions['can_submit_meals']) || !empty($permissions['can_transfer_stock']) || IBF_Auth::can_all_sites();
        }
        if ($type === 'inventory') {
            return !empty($permissions['can_edit_inventory']) || !empty($permissions['can_transfer_stock']);
        }
        if (in_array($type, array('invoices', 'suppliers'), true)) {
            if ($action === 'insert' || $action === 'upsert') {
                return !empty($permissions['can_add_invoice']);
            }
            return !empty($permissions['can_add_invoice']) || !empty($permissions['can_approve_meals']) || !empty($permissions['can_edit_inventory']);
        }
        if ($type === 'purchase_requests') {
            if ($action === 'insert') {
                return !empty($permissions['can_add_invoice']);
            }
            return !empty($permissions['can_add_invoice']) || !empty($permissions['can_approve_meals']) || !empty($permissions['can_edit_inventory']) || !empty($permissions['can_approve_purchases']);
        }
        if ($type === 'cashbox_accounts') {
            return !empty($permissions['can_manage_cashbox']);
        }
        if ($type === 'cashbox_transactions') {
            if ($action === 'insert') {
                return !empty($permissions['can_manage_cashbox']) || !empty($permissions['can_add_invoice']);
            }
            return IBF_Auth::can_all_sites();
        }
        if ($type === 'ration_standards') {
            return current_user_can('ibf_manage_recipes') || current_user_can('manage_options');
        }
        if ($type === 'meal_logs') {
            if ($action === 'insert') {
                return !empty($permissions['can_submit_meals']);
            }
            return !empty($permissions['can_approve_meals']) || !empty($permissions['can_edit_inventory']) || !empty($permissions['can_submit_meals']);
        }
        if ($type === 'menus') {
            return !empty($permissions['can_submit_meals']) || !empty($permissions['can_manage_recipes']);
        }
        return false;
    }

    private static function can_read($type) {
        $permissions = IBF_Auth::permissions();
        // شاشة متابعة HR تقرأ الجودة والصيانة للعرض فقط؛ الكتابة تظل محكومة بـ can_write
        $hr_overview = !empty($permissions['can_manage_hr']);
        if ($type === 'quality_checks') {
            return !empty($permissions['can_manage_quality']) || $hr_overview;
        }
        if (in_array($type, array('employees', 'attendance'), true)) {
            return $hr_overview;
        }
        if (in_array($type, array('equipment', 'maintenance_logs'), true)) {
            return !empty($permissions['can_manage_equipment']) || $hr_overview;
        }
        return true;
    }

    private static function next_cashbox_reference($site, $date) {
        $site_token = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', remove_accents((string) $site)));
        if ($site_token === '') {
            $site_token = strtoupper(substr(md5((string) $site), 0, 4));
        }
        $site_token = substr($site_token, 0, 6);
        $prefix = 'CB-' . $site_token . '-' . str_replace('-', '', $date) . '-';
        $sequence = 0;
        foreach (IBF_DB::rows('cashbox_transactions', $site) as $row) {
            $reference = (string) ($row['data']['reference'] ?? '');
            if (strpos($reference, $prefix) !== 0) {
                continue;
            }
            if (preg_match('/-(\d{4})$/', $reference, $match)) {
                $sequence = max($sequence, (int) $match[1]);
            }
        }
        return $prefix . str_pad((string) ($sequence + 1), 4, '0', STR_PAD_LEFT);
    }

    private static function site_definition($name) {
        foreach (IBF_DB::rows('sites', null) as $row) {
            if ((string) ($row['data']['name'] ?? '') === (string) $name) {
                return $row['data'];
            }
        }
        return null;
    }

    private static function restricted_stock_transfer(array $payload) {
        $from = self::site_definition((string) ($payload['fromSite'] ?? ''));
        $to = self::site_definition((string) ($payload['toSite'] ?? ''));
        $items = is_array($payload['items'] ?? null) ? $payload['items'] : array();
        $item = is_array($items[0] ?? null) ? $items[0] : array();
        $qty = (float) ($item['qty'] ?? 0);
        if (!$from || !$to || $qty <= 0 || empty($item['name'])) {
            return array();
        }
        if (!empty($from['isSupplyOnly'])) {
            return array();
        }
        $supply_only = !empty($to['isSupplyOnly']);
        $purchase_price = (float) ($item['purchasePrice'] ?? $item['price'] ?? 0);
        $sale_price = (float) ($item['salePrice'] ?? 0);
        if ($supply_only && (empty($from['isMain']) || $purchase_price <= 0 || $sale_price <= 0)) {
            return array();
        }
        $user = wp_get_current_user();
        $payload['supplyOnly'] = $supply_only;
        $payload['createdBy'] = $user->display_name ?: $user->user_login;
        $payload['createdAt'] = current_time('mysql');
        $item['purchasePrice'] = $purchase_price;
        $item['salePrice'] = $supply_only ? $sale_price : 0;
        $item['markupPercent'] = $supply_only && $purchase_price > 0 ? (($sale_price / $purchase_price) - 1) * 100 : 0;
        $item['price'] = $purchase_price;
        $item['cost'] = $qty * $purchase_price;
        $item['revenue'] = $supply_only ? $qty * $sale_price : 0;
        $item['profit'] = $item['revenue'] - $item['cost'];
        $payload['items'] = array($item);
        return $payload;
    }

    private static function restricted_insert($type, array $payload) {
        if ($type === 'stock_transfers') {
            return self::restricted_stock_transfer($payload);
        }
        if ($type === 'purchase_requests') {
            return self::restricted_purchase_snapshot($payload);
        }
        if ($type === 'meal_logs') {
            $user = wp_get_current_user();
            $payload['site'] = self::row_site($payload);
            $site = self::site_definition($payload['site']);
            if ($site && !empty($site['isSupplyOnly'])) {
                return array();
            }
            $payload['supervisor_name'] = $user->display_name ?: $user->user_login;
            $payload['chef_status'] = '';
            $payload['chef_name'] = '';
            $payload['chef_time'] = '';
            $payload['chef_notes'] = '';
            $payload['inventory_posted'] = false;
            $payload['warehouse_approved'] = false;
            $payload['warehouse_status'] = '';
            $payload['warehouse_name'] = '';
            $payload['warehouse_time'] = '';
            $payload['warehouse_notes'] = '';
            return $payload;
        }
        if ($type !== 'cashbox_transactions') {
            return $payload;
        }
        $permissions = IBF_Auth::permissions();
        $transaction_type = sanitize_key((string) ($payload['type'] ?? ''));
        $cash_in_types = array('deposit', 'receivable');
        $cash_out_types = array('expense', 'supplier_payment');
        if (!in_array($transaction_type, array_merge($cash_in_types, $cash_out_types), true)) {
            return array();
        }
        if (empty($permissions['can_manage_cashbox'])) {
            if (empty($permissions['can_add_invoice']) || $transaction_type !== 'supplier_payment' || empty($payload['source_reference'])) {
                return array();
            }
            $payload['category'] = 'فاتورة مشتريات';
        }
        $amount = (float) ($payload['amount'] ?? 0);
        if ($amount <= 0 || empty($payload['category'])) {
            return array();
        }
        if ($transaction_type === 'supplier_payment' && empty($payload['supplier'])) {
            return array();
        }
        $site = IBF_Auth::can_all_sites()
            ? sanitize_text_field((string) ($payload['site'] ?? ''))
            : self::user_site();
        if ($site === '') {
            return array();
        }
        $date = sanitize_text_field((string) ($payload['transaction_date'] ?? ''));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = current_time('Y-m-d');
        }
        $user = wp_get_current_user();
        $actor = $user->display_name ?: $user->user_login;
        $now = current_time('mysql');
        $payload['site'] = $site;
        $payload['transaction_date'] = $date;
        $payload['reference'] = self::next_cashbox_reference($site, $date);
        $payload['requested_by'] = $actor;
        $payload['requested_at'] = $now;
        $payload['supervisor_name'] = $actor;
        if (in_array($transaction_type, $cash_in_types, true)) {
            $payload['approval_status'] = 'approved';
            $payload['approved_by'] = $actor;
            $payload['approved_at'] = $now;
            unset($payload['rejected_by'], $payload['rejected_at']);
        } else {
            $payload['approval_status'] = 'pending';
            $payload['approved_by'] = '';
            $payload['approved_at'] = '';
            $payload['rejected_by'] = '';
            $payload['rejected_at'] = '';
        }
        return $payload;
    }

    private static function cashbox_available_balance($site) {
        $opening = 0;
        foreach (IBF_DB::rows('cashbox_accounts', $site) as $row) {
            $opening = (float) ($row['data']['opening_balance'] ?? 0);
        }
        $balance = $opening;
        foreach (IBF_DB::rows('cashbox_transactions', $site) as $row) {
            $data = $row['data'];
            $amount = (float) ($data['amount'] ?? 0);
            $type = (string) ($data['type'] ?? '');
            $status = (string) ($data['approval_status'] ?? 'approved');
            if (in_array($type, array('deposit', 'receivable'), true) && $status !== 'rejected') {
                $balance += $amount;
            } elseif (in_array($type, array('expense', 'supplier_payment'), true) && $status === 'approved') {
                $balance -= $amount;
            }
        }
        return $balance;
    }

    private static function restricted_update($type, array $payload) {
        if ($type === 'cashbox_transactions') {
            if (!IBF_Auth::can_all_sites()) {
                return array();
            }
            $decision = sanitize_key((string) ($payload['approval_status'] ?? ''));
            if (!in_array($decision, array('approved', 'rejected'), true)) {
                return array();
            }
            $user = wp_get_current_user();
            $actor = $user->display_name ?: $user->user_login;
            $now = current_time('mysql');
            return array(
                'approval_status' => $decision,
                'approval_notes' => sanitize_textarea_field((string) ($payload['approval_notes'] ?? '')),
                'approved_by' => $decision === 'approved' ? $actor : '',
                'approved_at' => $decision === 'approved' ? $now : '',
                'rejected_by' => $decision === 'rejected' ? $actor : '',
                'rejected_at' => $decision === 'rejected' ? $now : '',
            );
        }
        if (IBF_Auth::can_all_sites()) {
            return $payload;
        }
        $permissions = IBF_Auth::permissions();
        if ($type === 'meal_logs') {
            $allowed = array();
            if (!empty($permissions['can_approve_meals'])) {
                $allowed = array_merge($allowed, array('chef_status', 'chef_name', 'chef_time', 'chef_notes'));
            }
            if (!empty($permissions['can_edit_inventory'])) {
                $allowed = array_merge($allowed, array('inventory_posted', 'warehouse_approved', 'warehouse_status', 'warehouse_name', 'warehouse_time', 'warehouse_notes'));
            }
            return array_intersect_key($payload, array_flip($allowed));
        }
        return $payload;
    }

    private static function restricted_purchase_snapshot(array $item) {
        if (IBF_Auth::can_all_sites()) {
            return $item;
        }
        $permissions = IBF_Auth::permissions();
        $external = sanitize_text_field((string) ($item['id'] ?? ''));
        $existing = $external ? IBF_DB::find('purchase_requests', $external) : null;
        if (!$existing) {
            if (empty($permissions['can_add_invoice'])) {
                return array();
            }
            $site_definition = self::site_definition(self::user_site());
            if ($site_definition && !empty($site_definition['isSupplyOnly'])) {
                return array();
            }
            $user = wp_get_current_user();
            $item['site'] = self::user_site();
            $item['supervisorName'] = $user->display_name ?: $user->user_login;
            $item['supervisorApproval'] = 'approved';
            $item['supervisorTime'] = current_time('mysql');
            $item['chefApproval'] = 'pending';
            $item['warehouseApproval'] = 'pending';
            $item['purchasingApproval'] = 'pending';
            $item['managerApproval'] = 'pending';
            $item['status'] = 'بانتظار الاعتماد';
            return $item;
        }
        if ($existing['site'] !== '' && $existing['site'] !== self::user_site()) {
            return array();
        }
        $allowed = array();
        if (!empty($permissions['can_approve_meals'])) {
            $allowed = array_merge($allowed, array('chefApproval', 'chefName', 'chefTime', 'rejectionReason', 'status'));
        }
        if (!empty($permissions['can_edit_inventory'])) {
            $allowed = array_merge($allowed, array('warehouseApproval', 'warehouseName', 'warehouseTime', 'rejectionReason', 'status'));
        }
        if (!empty($permissions['can_approve_purchases'])) {
            $allowed = array_merge($allowed, array('purchasingApproval', 'purchasingName', 'purchasingTime', 'purchasingSupplier', 'purchasingNotes', 'items', 'estimatedTotal', 'rejectionReason', 'status'));
        }
        return array_merge($existing['data'], array_intersect_key($item, array_flip($allowed)));
    }

    private static function normalize_record($type, array $data) {
        $data = self::sanitize_payload($data);
        $external = sanitize_text_field((string) ($data['id'] ?? ''));
        if ($external === '' || $external === '0') {
            $external = wp_generate_uuid4();
        }
        $data['id'] = $external;
        $site = self::row_site($data);
        $existing = IBF_DB::find($type, $external);
        if ($existing && !IBF_Auth::can_all_sites() && $existing['site'] !== '' && $existing['site'] !== $site) {
            $external = wp_generate_uuid4();
            $data['id'] = $external;
        }
        if ($site !== '') {
            $data['site'] = $site;
        }
        return array($external, $site, self::date_from($data), $data);
    }

    private static function visible_rows($type) {
        if (IBF_Auth::can_all_sites()) {
            return IBF_DB::rows($type, null);
        }
        $sites = IBF_Auth::sites();
        // حساب مربوط بأكثر من مدرسة (المراجع) يقرأ كل مدارسه، والباقي موقع واحد
        return IBF_DB::rows($type, count($sites) > 1 ? $sites : self::user_site());
    }

    private static function matches(array $data, array $filters) {
        foreach ($filters as $filter) {
            $field = sanitize_key((string) ($filter['field'] ?? ''));
            $value = $filter['value'] ?? null;
            if ($field === '' || (string) ($data[$field] ?? '') !== (string) $value) {
                return false;
            }
        }
        return true;
    }

    private static function permissions_query($action, array $payload, array $filters, $single = false) {
        if ($action === 'upsert') {
            if (!self::can_write('app_permissions')) {
                return new WP_Error('ibf_forbidden', 'غير مصرح بتعديل الصلاحيات.', array('status' => 403));
            }
            $rows = isset($payload[0]) ? $payload : array($payload);
            foreach ($rows as $row) {
                $login = sanitize_user((string) ($row['supervisor_code'] ?? ''));
                $user = get_user_by('login', $login);
                if (!$user) {
                    continue;
                }
                // الدمج بدل الاستبدال: صلاحيات المراجعة والتكلفة لا تُمسح بحفظ شبكة المشرفين
                $stored = get_user_meta($user->ID, IBF_Auth::USER_PERMISSIONS, true);
                $stored = is_array($stored) ? $stored : array();
                update_user_meta($user->ID, IBF_Auth::USER_PERMISSIONS, array_merge($stored, array(
                    'can_submit_meals' => !empty($row['can_submit_meals']),
                    'can_edit_inventory' => !empty($row['can_edit_inventory']),
                    'can_add_invoice' => !empty($row['can_add_invoice']),
                    'can_manage_quality' => !empty($row['can_manage_quality']),
                    'can_manage_hr' => !empty($row['can_manage_hr']),
                    'can_manage_equipment' => !empty($row['can_manage_equipment']),
                )));
                IBF_DB::audit(get_current_user_id(), 'permissions', 'app_permissions', $login, IBF_Auth::site($user->ID));
            }
        }

        $users = get_users(array('role__in' => array('ibf_supervisor', 'ibf_storekeeper', 'ibf_purchasing', 'ibf_quality', 'ibf_hr', 'ibf_maintenance')));
        $result = array();
        foreach ($users as $user) {
            $permissions = IBF_Auth::permissions($user);
            $row = array(
                'supervisor_code' => $user->user_login,
                'supervisor_name' => $user->display_name,
                'site' => IBF_Auth::site($user->ID),
                'can_submit_meals' => $permissions['can_submit_meals'],
                'can_edit_inventory' => $permissions['can_edit_inventory'],
                'can_add_invoice' => $permissions['can_add_invoice'],
                'can_manage_quality' => $permissions['can_manage_quality'],
                'can_manage_hr' => $permissions['can_manage_hr'],
                'can_manage_equipment' => $permissions['can_manage_equipment'],
            );
            if (self::matches($row, $filters)) {
                $result[] = $row;
            }
        }
        return array('data' => $single ? ($result[0] ?? null) : $result, 'error' => null);
    }

    public static function query(WP_REST_Request $request) {
        $body = $request->get_json_params();
        $type = sanitize_key((string) ($body['table'] ?? ''));
        $action = sanitize_key((string) ($body['action'] ?? 'select'));
        $filters = is_array($body['filters'] ?? null) ? $body['filters'] : array();
        $payload = $body['payload'] ?? array();
        $single = !empty($body['single']);

        if (!self::valid_type($type) || !in_array($action, array('select', 'insert', 'update', 'delete', 'upsert'), true)) {
            return new WP_Error('ibf_bad_query', 'طلب بيانات غير صالح.', array('status' => 400));
        }
        if ($type === 'app_permissions') {
            if ($action === 'select' && !self::can_manage_users()) {
                return new WP_Error('ibf_forbidden', 'غير مصرح بعرض صلاحيات المستخدمين.', array('status' => 403));
            }
            $permission_result = self::permissions_query($action, (array) $payload, $filters, $single);
            return is_wp_error($permission_result) ? $permission_result : rest_ensure_response($permission_result);
        }

        if ($action === 'select' && !self::can_read($type)) {
            return new WP_Error('ibf_forbidden', 'ليس لديك صلاحية عرض هذه البيانات.', array('status' => 403));
        }
        if ($action !== 'select' && !self::can_write($type, $action)) {
            return new WP_Error('ibf_forbidden', 'ليس لديك صلاحية تنفيذ هذه العملية.', array('status' => 403));
        }

        if ($action === 'insert') {
            $items = isset($payload[0]) ? $payload : array($payload);
            $saved = array();
            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $item = self::restricted_insert($type, $item);
                if (!$item) {
                    return new WP_Error('ibf_invalid_cashbox_transaction', 'بيانات حركة الخزنة غير صالحة أو غير مصرح بها.', array('status' => 403));
                }
                list($external, $site, $date, $data) = self::normalize_record($type, $item);
                $row = IBF_DB::save($type, $external, $data, $site, $date, get_current_user_id(), 'insert');
                $saved[] = $row['data'];
            }
            return rest_ensure_response(array('data' => $saved, 'error' => null));
        }

        $rows = self::visible_rows($type);
        $matching = array_values(array_filter($rows, static function ($row) use ($filters) {
            return self::matches($row['data'], $filters);
        }));

        if ($action === 'select') {
            $order = is_array($body['orderBy'] ?? null) ? $body['orderBy'] : null;
            if ($order && !empty($order['field'])) {
                $field = sanitize_key($order['field']);
                $ascending = !empty($order['ascending']);
                usort($matching, static function ($a, $b) use ($field, $ascending) {
                    $cmp = strnatcasecmp((string) ($a['data'][$field] ?? ''), (string) ($b['data'][$field] ?? ''));
                    return $ascending ? $cmp : -$cmp;
                });
            }
            $data = array_map(static function ($row) { return $row['data']; }, $matching);
            return rest_ensure_response(array('data' => $single ? ($data[0] ?? null) : $data, 'error' => null));
        }

        if ($action === 'update') {
            $update_payload = self::restricted_update($type, is_array($payload) ? $payload : array());
            if (!$update_payload && !IBF_Auth::can_all_sites()) {
                return new WP_Error('ibf_forbidden_fields', 'هذا الدور غير مصرح له بتعديل هذه الحقول.', array('status' => 403));
            }
            $saved = array();
            foreach ($matching as $row) {
                if ($type === 'cashbox_transactions') {
                    $current_status = (string) ($row['data']['approval_status'] ?? 'approved');
                    if ($current_status !== 'pending') {
                        return new WP_Error('ibf_cashbox_already_decided', 'تم اتخاذ قرار على حركة الخزنة من قبل.', array('status' => 409));
                    }
                    if (($update_payload['approval_status'] ?? '') === 'approved') {
                        $available = self::cashbox_available_balance((string) ($row['data']['site'] ?? ''));
                        if ((float) ($row['data']['amount'] ?? 0) > $available) {
                            return new WP_Error('ibf_cashbox_insufficient', 'رصيد الخزنة غير كافٍ لاعتماد هذا الصرف.', array('status' => 409));
                        }
                    }
                }
                $data = array_merge($row['data'], $update_payload);
                list($external, $site, $date, $data) = self::normalize_record($type, $data);
                $updated = IBF_DB::save($type, $external, $data, $site, $date, get_current_user_id(), 'update');
                $saved[] = $updated['data'];
            }
            return rest_ensure_response(array('data' => $saved, 'error' => null));
        }

        if ($action === 'delete') {
            $count = 0;
            foreach ($matching as $row) {
                $count += IBF_DB::delete($type, $row['external_id'], get_current_user_id()) ? 1 : 0;
            }
            return rest_ensure_response(array('data' => array('count' => $count), 'error' => null));
        }

        $items = isset($payload[0]) ? $payload : array($payload);
        $conflict = array_filter(array_map('sanitize_key', explode(',', (string) (($body['options']['onConflict'] ?? 'id')))));
        $saved = array();
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $existing = null;
            foreach (self::visible_rows($type) as $candidate) {
                $ok = true;
                foreach ($conflict as $field) {
                    if ((string) ($candidate['data'][$field] ?? '') !== (string) ($item[$field] ?? '')) {
                        $ok = false;
                        break;
                    }
                }
                if ($ok) {
                    $existing = $candidate;
                    break;
                }
            }
            if ($type === 'purchase_requests') {
                $data = self::restricted_purchase_snapshot($item);
            } elseif ($existing) {
                $changes = self::restricted_update($type, $item);
                if (!$changes && !IBF_Auth::can_all_sites()) {
                    return new WP_Error('ibf_forbidden_fields', 'هذا الدور غير مصرح له بتعديل هذه الحقول.', array('status' => 403));
                }
                $data = array_merge($existing['data'], $changes);
            } else {
                if (!self::can_write($type, 'insert')) {
                    return new WP_Error('ibf_forbidden_insert', 'هذا الدور غير مصرح له بإنشاء سجل جديد.', array('status' => 403));
                }
                $data = self::restricted_insert($type, $item);
            }
            if (!$data) {
                return new WP_Error('ibf_invalid_upsert', 'بيانات العملية غير صالحة أو غير مصرح بها.', array('status' => 403));
            }
            if ($existing) {
                $data['id'] = $existing['external_id'];
            }
            list($external, $site, $date, $data) = self::normalize_record($type, $data);
            $row = IBF_DB::save($type, $external, $data, $site, $date, get_current_user_id(), 'upsert');
            $saved[] = $row['data'];
        }
        return rest_ensure_response(array('data' => $saved, 'error' => null));
    }

    public static function snapshot_get() {
        $result = array();
        foreach (self::$snapshot_types as $type) {
            $rows = self::visible_rows($type);
            $result[$type] = array_map(static function ($row) { return $row['data']; }, $rows);
        }
        return rest_ensure_response(array('data' => $result, 'error' => null));
    }

    public static function snapshot_save(WP_REST_Request $request) {
        $body = $request->get_json_params();
        $data = is_array($body['data'] ?? null) ? $body['data'] : array();
        foreach (self::$snapshot_types as $type) {
            if (empty($data[$type]) || !is_array($data[$type]) || !self::can_write($type)) {
                continue;
            }
            foreach ($data[$type] as $item) {
                if (!is_array($item)) {
                    continue;
                }
                if ($type === 'returns_waste') {
                    $permissions = IBF_Auth::permissions();
                    $is_transfer = ($item['type'] ?? '') === 'transfer';
                    if ($is_transfer && empty($permissions['can_transfer_stock']) && !IBF_Auth::can_all_sites()) {
                        continue;
                    }
                    if (!$is_transfer && empty($permissions['can_submit_meals']) && !IBF_Auth::can_all_sites()) {
                        continue;
                    }
                    if (!IBF_Auth::can_all_sites()) {
                        $item['site'] = self::user_site();
                    }
                }
                if ($type === 'purchase_requests') {
                    $item = self::restricted_purchase_snapshot($item);
                    if (!$item) {
                        continue;
                    }
                }
                list($external, $site, $date, $record) = self::normalize_record($type, $item);
                IBF_DB::save($type, $external, $record, $site, $date, get_current_user_id(), 'snapshot');
            }
        }
        return rest_ensure_response(array('data' => true, 'error' => null));
    }

    public static function export() {
        return rest_ensure_response(array(
            'app' => 'I-BUILD Food Cloud',
            'version' => IBF_VERSION,
            'exportedAt' => current_time('mysql'),
            'records' => IBF_DB::export_all(),
        ));
    }
}
