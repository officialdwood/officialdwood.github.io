<?php
/*
Plugin Name: TimeClock Manager
 * Plugin URI: https://www.brightidea.media
 * Author URI: https://www.brightidea.media
Description: A timeclock plugin for users to clock in/out and admins to manage weekly reports.
Version: 1.1
Author: SolCoders
Author URI: https://solcoders.com
Text Domain: tcm

MIGRATION NOTE (2025-10): Week boundaries now use Sunday 00:00 America/Denver. Existing data keeps
week_start_date for reference; week_start_date_sun stores the canonical value. To roll back, remove
the new column and restore legacy Monday logic. Backfill runs on admin_init and is idempotent.
*/

if (!defined('ABSPATH'))
    exit;

define('TCM_PLUGIN_FILE', __FILE__);
define('TCM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TCM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TCM_PLUGIN_ASSETS', TCM_PLUGIN_URL . 'assets');
define('TCM_PLUGIN_INCLUDES', TCM_PLUGIN_DIR . 'includes');
define('TCM_PLUGIN_TEMPLATES', TCM_PLUGIN_DIR . 'templates');
define('TCM_PLUGIN_INC', TCM_PLUGIN_DIR . 'inc');


// Load core plugin
require_once TCM_PLUGIN_INC . '/utils-dates.php';
require_once TCM_PLUGIN_DIR . '/helper.php';
require_once TCM_PLUGIN_DIR . '/database.php';
require_once TCM_PLUGIN_INCLUDES . '/class-tcm-loader.php';


require_once TCM_PLUGIN_INCLUDES . '/admin/class-tcm-admin-reports.php';
TCM_Admin_Reports::maybe_export_csv();

// === Create time requests table on admin init ===
add_action('admin_init', 'tcm_create_time_requests_table');
function tcm_create_time_requests_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'tcm_time_requests';
    
    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            request_type varchar(50) NOT NULL,
            request_date date NOT NULL,
            request_time time NOT NULL,
            description text NOT NULL,
            status varchar(20) DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

// === TimeClock: Redirect users to /timeclock after logout ===
add_filter('logout_redirect', function($redirect_to, $requested_redirect_to, $user) {
    // Default target for the kiosk
    $target = home_url('/timeclock/');

    // If we have a user object and they are an admin, preserve normal behavior
    if ($user instanceof WP_User && user_can($user, 'manage_options')) {
        // Respect an explicit redirect if provided, otherwise leave core behavior
        return $requested_redirect_to ? $requested_redirect_to : $redirect_to;
    }

    // For employees/standard users, always send back to the kiosk
    return $target;
}, 20, 3);


// === Bright Idea Marketing Branding Enforcement ===
add_action('admin_init', function () {
    add_filter('all_plugins', function ($plugins) {
        if (!function_exists('plugin_basename')) { return $plugins; }
        $file = plugin_basename(__FILE__);
        if (isset($plugins[$file])) {
            $plugins[$file]['AuthorName'] = 'Bright Idea Marketing';
            $plugins[$file]['Author']     = '<a href="https://www.brightidea.media" target="_blank" rel="noopener">Bright Idea Marketing</a>';
            $plugins[$file]['AuthorURI']  = 'https://www.brightidea.media';
            $plugins[$file]['PluginURI']  = 'https://www.brightidea.media';
            $desc = isset($plugins[$file]['Description']) ? $plugins[$file]['Description'] : '';
            if (strpos($desc, 'Dylan Wood') === false) {
                $plugins[$file]['Description'] = trim($desc . ' — Created by Bright Idea Marketing • Creator: Dylan Wood');
            }
        }
        return $plugins;
    }, 9999);

    // Extra row meta: Creator credit (cannot be altered via header)
    add_filter('plugin_row_meta', function ($links, $file) {
        if ($file === plugin_basename(__FILE__)) {
            $links[] = '<strong>Creator:</strong> Dylan Wood';
            $links[] = '<a href="https://www.brightidea.media" target="_blank" rel="noopener">brightidea.media</a>';
        }
        return $links;
    }, 9999, 2);
});
// === End Branding Enforcement ===



/**
 * Force logout redirect to /timeclock to prep for next user PIN login.
 */
add_filter('logout_redirect', function($redirect_to, $requested_redirect_to, $user){
    $target = home_url('/timeclock');
    return $target;
}, 9999, 3);


// === AJAX: Weekly Total for current user (Mon-Sun) ===
add_action('wp_ajax_tcm_get_weekly_total', 'tcm_ajax_get_weekly_total');
function tcm_ajax_get_weekly_total(){
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
    }
    $user_id = get_current_user_id();
    global $wpdb;
    $table = $wpdb->prefix . 'tcm_timesheets';

    $now = TimeClock\Dates\now();
    [$start_dt, $end_dt] = TimeClock\Dates\week_bounds($now);
    $week_start = TimeClock\Dates\to_storage_date($start_dt);
    $week_end   = TimeClock\Dates\to_storage_date($end_dt);

    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT id, clock_in, clock_out, total_hours, total_minutes
         FROM {$table}
         WHERE user_id = %d
           AND week_start_date_sun = %s",
        $user_id, $week_start
    ));

    $total = 0.0;
    $daily = [];
    $cursor = $start_dt;
    for ($i = 0; $i < 7; $i++) {
        $key = TimeClock\Dates\to_storage_date($cursor);
        $daily[$key] = [
            'dt'      => $cursor,
            'decimal' => 0.0,
        ];
        $cursor = $cursor->modify('+1 day');
    }

    if ($rows) {
        foreach ($rows as $r) {
            $clock_in_dt  = !empty($r->clock_in) ? TimeClock\Dates\parse_storage($r->clock_in) : null;
            $clock_out_dt = !empty($r->clock_out) ? TimeClock\Dates\parse_storage($r->clock_out) : null;

            if ($clock_in_dt && $clock_out_dt) {
                $seconds = max(0, $clock_out_dt->getTimestamp() - $clock_in_dt->getTimestamp());
                $h = $seconds / 3600;
            } elseif (isset($r->total_minutes) && $r->total_minutes !== null) {
                $h = max(0, (int) $r->total_minutes) / 60;
            } else {
                $h = max(0, floatval($r->total_hours));
            }

            $total += $h;

            $date_key = $clock_in_dt ? TimeClock\Dates\to_storage_date($clock_in_dt) : $week_start;
            if (!isset($daily[$date_key])) {
                $daily[$date_key] = [
                    'dt'      => $clock_in_dt ?: $start_dt,
                    'decimal' => 0.0,
                ];
            }
            $daily[$date_key]['decimal'] += $h;
        }
    }
    $total = round($total, 4);
    $total_formatted = number_format($total, 2) . ' hours';

    $daily_payload = [];
    foreach ($daily as $date_key => $info) {
        $label_dt = $info['dt'] instanceof DateTimeInterface ? $info['dt'] : TimeClock\Dates\parse_storage($date_key . ' 00:00:00');
        $label = $label_dt ? TimeClock\Dates\fmt($label_dt, TimeClock\Dates\DATE_FMT) : $date_key;
        $dec = isset($info['decimal']) ? round((float) $info['decimal'], 2) : 0.0;
        $daily_payload[] = [
            'date'      => $date_key,
            'label'     => $label,
            'decimal'   => $dec,
            'formatted' => number_format($dec, 2) . ' h',
        ];
    }

    wp_send_json_success([
        'total_decimal'   => round($total, 2),
        'total_formatted' => $total_formatted,
        'week_start'      => TimeClock\Dates\fmt($start_dt, TimeClock\Dates\DATE_FMT),
        'week_end'        => TimeClock\Dates\fmt($end_dt, TimeClock\Dates\DATE_FMT),
        'daily_totals'    => $daily_payload,
    ]);
}

// === AJAX: Submit Time Change Request ===
add_action('wp_ajax_tcm_submit_time_request', 'tcm_ajax_submit_time_request');
function tcm_ajax_submit_time_request(){
    // Verify nonce for CSRF protection
    check_ajax_referer('tcm_time_request_nonce', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
    }
    
    $user_id = get_current_user_id();
    $user = wp_get_current_user();
    
    // Sanitize inputs
    $request_type = sanitize_text_field($_POST['request_type']);
    $request_date = sanitize_text_field($_POST['request_date']);
    $request_time = sanitize_text_field($_POST['request_time']);
    $description = sanitize_textarea_field($_POST['description']);
    
    // Validate inputs
    if (empty($request_type) || empty($request_date) || empty($request_time) || empty($description)) {
        wp_send_json_error('All fields are required');
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'tcm_time_requests';
    
    // Insert the request
    $result = $wpdb->insert(
        $table,
        array(
            'user_id' => $user_id,
            'request_type' => $request_type,
            'request_date' => $request_date,
            'request_time' => $request_time,
            'description' => $description,
            'status' => 'pending',
            'created_at' => current_time('mysql')
        ),
        array('%d', '%s', '%s', '%s', '%s', '%s', '%s')
    );
    
    if ($result) {
        // Send notification email to configured address
        $settings = get_option('tcm_settings');
        $notification_email = isset($settings['time_request_email']) && !empty($settings['time_request_email']) 
            ? $settings['time_request_email'] 
            : 'info@protechsteel.com';
        
        $subject = 'New Time Change Request - ' . $user->display_name;
        $message = "A new time change request has been submitted:\n\n";
        $message .= "User: " . $user->display_name . " (" . $user->user_email . ")\n";
        $message .= "Type: " . $request_type . "\n";
        $message .= "Date: " . $request_date . "\n";
        $message .= "Time: " . $request_time . "\n";
        $message .= "Description: " . $description . "\n";
        
        wp_mail($notification_email, $subject, $message);
        
        wp_send_json_success('Request submitted successfully');
    } else {
        wp_send_json_error('Failed to submit request');
    }
}

// Add-record feature (guarded include)
if ( file_exists( TCM_PLUGIN_DIR . '/includes/tcm-add-record.php' ) ) {
    require_once TCM_PLUGIN_DIR . '/includes/tcm-add-record.php';
}
