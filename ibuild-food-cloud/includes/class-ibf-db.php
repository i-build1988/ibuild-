<?php

if (!defined('ABSPATH')) {
    exit;
}

final class IBF_DB {
    public static function records_table() {
        global $wpdb;
        return $wpdb->prefix . 'ibf_records';
    }

    public static function audit_table() {
        global $wpdb;
        return $wpdb->prefix . 'ibf_audit_log';
    }

    public static function activate() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charset = $wpdb->get_charset_collate();

        dbDelta('CREATE TABLE ' . self::records_table() . " (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            record_type varchar(64) NOT NULL,
            external_id varchar(100) NOT NULL,
            site varchar(190) NOT NULL DEFAULT '',
            record_date date DEFAULT NULL,
            payload longtext NOT NULL,
            created_by bigint(20) unsigned NOT NULL DEFAULT 0,
            updated_by bigint(20) unsigned NOT NULL DEFAULT 0,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY type_external (record_type, external_id),
            KEY type_site (record_type, site),
            KEY type_date (record_type, record_date),
            KEY updated_at (updated_at)
        ) {$charset};");

        dbDelta('CREATE TABLE ' . self::audit_table() . " (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            actor_id bigint(20) unsigned NOT NULL DEFAULT 0,
            action varchar(30) NOT NULL,
            record_type varchar(64) NOT NULL,
            external_id varchar(100) NOT NULL DEFAULT '',
            site varchar(190) NOT NULL DEFAULT '',
            details text DEFAULT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY actor_date (actor_id, created_at),
            KEY entity (record_type, external_id),
            KEY site_date (site, created_at)
        ) {$charset};");

        update_option('ibf_db_version', IBF_VERSION, false);
    }

    public static function decode($payload) {
        $data = json_decode((string) $payload, true);
        return is_array($data) ? $data : array();
    }

    /**
     * $site = null  : كل المواقع
     * $site = 'اسم' : موقع واحد
     * $site = array : عدة مواقع — يستخدمها المراجع المسنَد له أكثر من مدرسة
     */
    public static function rows($type, $site = null) {
        global $wpdb;
        $table = self::records_table();
        if ($site === null) {
            $sql = $wpdb->prepare("SELECT * FROM {$table} WHERE record_type=%s ORDER BY id DESC", $type);
        } elseif (is_array($site)) {
            $sites = array_values(array_unique(array_filter(array_map('strval', $site), static function ($value) {
                return $value !== '';
            })));
            if (!$sites) {
                // لا موقع مسموحًا: لا تُرجع إلا السجلات العامة
                $sql = $wpdb->prepare("SELECT * FROM {$table} WHERE record_type=%s AND site='' ORDER BY id DESC", $type);
            } else {
                $placeholders = implode(',', array_fill(0, count($sites), '%s'));
                $sql = $wpdb->prepare(
                    "SELECT * FROM {$table} WHERE record_type=%s AND (site IN ({$placeholders}) OR site='') ORDER BY id DESC",
                    array_merge(array($type), $sites)
                );
            }
        } else {
            $sql = $wpdb->prepare("SELECT * FROM {$table} WHERE record_type=%s AND (site=%s OR site='') ORDER BY id DESC", $type, $site);
        }
        $rows = $wpdb->get_results($sql, ARRAY_A);
        foreach ($rows as &$row) {
            $row['data'] = self::decode($row['payload']);
        }
        unset($row);
        return $rows;
    }

    public static function find($type, $external_id) {
        global $wpdb;
        $row = $wpdb->get_row(
            $wpdb->prepare(
                'SELECT * FROM ' . self::records_table() . ' WHERE record_type=%s AND external_id=%s LIMIT 1',
                $type,
                $external_id
            ),
            ARRAY_A
        );
        if ($row) {
            $row['data'] = self::decode($row['payload']);
        }
        return $row;
    }

    public static function save($type, $external_id, array $data, $site, $record_date, $user_id, $action = 'save') {
        global $wpdb;
        $now = current_time('mysql');
        $existing = self::find($type, $external_id);
        $values = array(
            'record_type' => $type,
            'external_id' => $external_id,
            'site' => $site,
            'record_date' => $record_date ?: null,
            'payload' => wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'updated_by' => $user_id,
            'updated_at' => $now,
        );

        if ($existing) {
            $wpdb->update(self::records_table(), $values, array('id' => (int) $existing['id']));
        } else {
            $values['created_by'] = $user_id;
            $values['created_at'] = $now;
            $wpdb->insert(self::records_table(), $values);
        }

        self::audit($user_id, $action, $type, $external_id, $site, array('record_date' => $record_date));
        return self::find($type, $external_id);
    }

    public static function delete($type, $external_id, $user_id) {
        global $wpdb;
        $existing = self::find($type, $external_id);
        if (!$existing) {
            return false;
        }
        $deleted = $wpdb->delete(self::records_table(), array('id' => (int) $existing['id']));
        if ($deleted) {
            self::audit($user_id, 'delete', $type, $external_id, $existing['site']);
        }
        return (bool) $deleted;
    }

    public static function audit($actor_id, $action, $type, $external_id = '', $site = '', array $details = array()) {
        global $wpdb;
        $wpdb->insert(
            self::audit_table(),
            array(
                'actor_id' => (int) $actor_id,
                'action' => sanitize_key($action),
                'record_type' => sanitize_key($type),
                'external_id' => sanitize_text_field($external_id),
                'site' => sanitize_text_field($site),
                'details' => $details ? wp_json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                'created_at' => current_time('mysql'),
            )
        );
    }

    /**
     * قراءة سجل المراجعة مع فلاتر المراجع: الموقع والتاريخ والمنفّذ ونوع العملية.
     * $sites = null يعني كل المواقع؛ المصفوفة تقصر النتيجة على مدارس المراجع.
     */
    public static function audit_rows(array $args = array()) {
        global $wpdb;
        $table = self::audit_table();
        $where = array('1=1');
        $params = array();

        if (isset($args['sites']) && is_array($args['sites'])) {
            $sites = array_values(array_unique(array_filter(array_map('strval', $args['sites']), static function ($value) {
                return $value !== '';
            })));
            if (!$sites) {
                $where[] = "site=''";
            } else {
                $placeholders = implode(',', array_fill(0, count($sites), '%s'));
                $where[] = "(site IN ({$placeholders}) OR site='')";
                $params = array_merge($params, $sites);
            }
        }
        if (!empty($args['site'])) {
            $where[] = 'site=%s';
            $params[] = (string) $args['site'];
        }
        if (!empty($args['from'])) {
            $where[] = 'created_at>=%s';
            $params[] = $args['from'] . ' 00:00:00';
        }
        if (!empty($args['to'])) {
            $where[] = 'created_at<=%s';
            $params[] = $args['to'] . ' 23:59:59';
        }
        if (!empty($args['actor_id'])) {
            $where[] = 'actor_id=%d';
            $params[] = (int) $args['actor_id'];
        }
        if (!empty($args['action'])) {
            $where[] = 'action=%s';
            $params[] = sanitize_key((string) $args['action']);
        }
        if (!empty($args['record_type'])) {
            $where[] = 'record_type=%s';
            $params[] = sanitize_key((string) $args['record_type']);
        }

        $limit = isset($args['limit']) ? max(1, min(2000, (int) $args['limit'])) : 500;
        $offset = isset($args['offset']) ? max(0, (int) $args['offset']) : 0;
        $clause = implode(' AND ', $where);

        $count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$clause}";
        $total = (int) $wpdb->get_var($params ? $wpdb->prepare($count_sql, $params) : $count_sql);

        $sql = "SELECT * FROM {$table} WHERE {$clause} ORDER BY id DESC LIMIT %d OFFSET %d";
        $rows = $wpdb->get_results($wpdb->prepare($sql, array_merge($params, array($limit, $offset))), ARRAY_A);

        foreach ($rows as &$row) {
            $row['details'] = $row['details'] ? self::decode($row['details']) : array();
        }
        unset($row);

        return array('rows' => $rows, 'total' => $total);
    }

    public static function export_all() {
        global $wpdb;
        $records = $wpdb->get_results('SELECT * FROM ' . self::records_table() . ' ORDER BY id ASC', ARRAY_A);
        foreach ($records as &$record) {
            $record['payload'] = self::decode($record['payload']);
        }
        unset($record);
        return $records;
    }
}
