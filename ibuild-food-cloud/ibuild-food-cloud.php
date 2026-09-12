<?php
/**
 * Plugin Name: I-BUILD Food Cloud
 * Description: نظام مركزي لقطاع التغذية والكاترنج مع مستخدمين وصلاحيات وقاعدة بيانات مشتركة.
 * Version: 2.15.0
 * Author: I-BUILD Egypt
 * Text Domain: ibuild-food-cloud
 */

if (!defined('ABSPATH')) {
    exit;
}

define('IBF_VERSION', '2.15.0');
define('IBF_FILE', __FILE__);
define('IBF_DIR', plugin_dir_path(__FILE__));
define('IBF_URL', plugin_dir_url(__FILE__));

require_once IBF_DIR . 'includes/class-ibf-db.php';
require_once IBF_DIR . 'includes/class-ibf-auth.php';
require_once IBF_DIR . 'includes/class-ibf-rest.php';
require_once IBF_DIR . 'includes/class-ibf-app.php';
require_once IBF_DIR . 'includes/class-ibf-admin.php';

register_activation_hook(__FILE__, static function () {
    IBF_DB::activate();
    IBF_Auth::activate();
    IBF_App::activate();
});

add_action('plugins_loaded', static function () {
    if (get_option('ibf_db_version') !== IBF_VERSION) {
        IBF_DB::activate();
        IBF_Auth::activate();
    }

    IBF_REST::init();
    IBF_App::init();
    IBF_Admin::init();
});

// Do not expose WordPress user names or display names to anonymous visitors.
add_filter('rest_endpoints', static function ($endpoints) {
    if (is_user_logged_in()) {
        return $endpoints;
    }
    unset($endpoints['/wp/v2/users']);
    unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    return $endpoints;
});
