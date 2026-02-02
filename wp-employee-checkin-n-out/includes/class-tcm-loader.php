<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class TCM_Loader
 *
 * Initializes the plugin, registers hooks, and loads necessary classes.
 */

if (!class_exists('TCM_Loader')) {
    class TCM_Loader
    {

        public function __construct()
        {
            add_action('init', [$this, 'init']);
            add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
            add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
            add_action('admin_menu', [$this, 'register_admin_menu']);
        }

        public function init()
        {
            require_once TCM_PLUGIN_INCLUDES . '/class-tcm-shortcodes.php';
            require_once TCM_PLUGIN_INCLUDES . '/class-tcm-clock-handler.php';
            require_once TCM_PLUGIN_INCLUDES . '/class-tcm-user-meta.php';
            
            // Load admin classes early so they can register their hooks
            // if (is_admin()) {
                require_once TCM_PLUGIN_INCLUDES . '/admin/class-tcm-admin-settings.php';
            // }
        }

        public function enqueue_assets()
        {
            wp_enqueue_style('tcm-style', TCM_PLUGIN_ASSETS . '/css/style.css');
            wp_enqueue_script('tcm-script', TCM_PLUGIN_ASSETS . '/js/script.js', ['jquery'], null, true);
            wp_localize_script('tcm-script', 'tcm_ajax_object', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'time_request_nonce' => wp_create_nonce('tcm_time_request_nonce')
            ]);

            wp_enqueue_script('tcm-admin-script', TCM_PLUGIN_ASSETS . '/js/admin/script.js', ['jquery'], null, true);
            wp_localize_script('tcm-admin-script', 'tcm_ajax_object', [
                'add_record_nonce' => wp_create_nonce('tcm_add_record_nonce'),
                'reports_nonce' => wp_create_nonce('tcm_update_hours_nonce'),
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('tcm_update_hours_nonce')
            ]);
        }

        public function admin_assets()
        {
            wp_enqueue_style('tcm-admin-style', TCM_PLUGIN_ASSETS . '/css/admin/style.css');
            wp_enqueue_script('tcm-admin-script', TCM_PLUGIN_ASSETS . '/js/admin/script.js', ['jquery'], null, true);
            wp_localize_script('tcm-admin-script', 'tcm_ajax_object', [
                'add_record_nonce' => wp_create_nonce('tcm_add_record_nonce'),
                'reports_nonce' => wp_create_nonce('tcm_update_hours_nonce'),
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('tcm_update_hours_nonce')
            ]);
        }

        public function register_admin_menu()
        {
            require_once TCM_PLUGIN_INCLUDES . '/admin/class-tcm-admin-menu.php';
        }
    }

    new TCM_Loader();
}
