<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class TCM_Admin_Menu
 *
 * Handles the admin menu and reports page rendering.
 */
if (!class_exists('TCM_Admin_Menu')) {
    class TCM_Admin_Menu
    {

        public function __construct()
        {
            add_action('admin_menu', [$this, 'add_menu_items']);
        }

        public function add_menu_items()
        {
            // Get pending request count
            global $wpdb;
            $table = $wpdb->prefix . 'tcm_adjustment_requests';
            $pending_count = $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE status = 'pending'");
            
            $menu_title = 'TimeClock';
            if ($pending_count > 0) {
                $menu_title .= ' <span class="update-plugins"><span class="update-count">' . $pending_count . '</span></span>';
            }

            add_menu_page(
                'TimeClock Reports',
                $menu_title,
                'tcm_access',
                'tcm-reports',
                [$this, 'render_reports_page'],
                'dashicons-clock',
                25
            );

            // Add Time Adjustment Requests submenu
            add_submenu_page(
                'tcm-reports',
                'Time Adjustment Requests',
                'Adjustment Requests' . ($pending_count > 0 ? ' <span class="awaiting-mod">' . $pending_count . '</span>' : ''),
                'tcm_access',
                'tcm-adjustment-requests',
                [$this, 'render_adjustment_requests_page']
            );
        }

        public function render_reports_page()
        {
            TCM_Admin_Reports::render();
        }

        public function render_adjustment_requests_page()
        {
            if (!current_user_can('tcm_access')) {
                wp_die('You do not have sufficient permissions to access this page.');
            }

            global $wpdb;
            $table = $wpdb->prefix . 'tcm_adjustment_requests';

            // Handle status updates
            if (isset($_POST['tcm_update_request']) && isset($_POST['request_id'])) {
                check_admin_referer('tcm_update_request_' . $_POST['request_id']);
                
                $request_id = intval($_POST['request_id']);
                $new_status = sanitize_text_field($_POST['new_status']);
                $admin_notes = isset($_POST['admin_notes']) ? sanitize_textarea_field($_POST['admin_notes']) : '';
                
                $wpdb->update(
                    $table,
                    [
                        'status' => $new_status,
                        'admin_notes' => $admin_notes,
                        'reviewed_by' => get_current_user_id(),
                        'reviewed_at' => current_time('mysql')
                    ],
                    ['id' => $request_id],
                    ['%s', '%s', '%d', '%s'],
                    ['%d']
                );
                
                echo '<div class="notice notice-success"><p>Request updated successfully.</p></div>';
            }

            // Fetch all requests
            $requests = $wpdb->get_results("
                SELECT r.*, u.display_name 
                FROM {$table} r
                LEFT JOIN {$wpdb->users} u ON r.user_id = u.ID
                ORDER BY 
                    CASE WHEN r.status = 'pending' THEN 0 ELSE 1 END,
                    r.created_at DESC
            ");

            include TCM_PLUGIN_TEMPLATES . '/admin-adjustment-requests.php';
        }
    }
    new TCM_Admin_Menu();
}

