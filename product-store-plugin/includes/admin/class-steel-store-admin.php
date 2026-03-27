<?php
if (!defined('ABSPATH')) exit;

class Steel_Store_Admin {
    private static $instance = null;

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu'], 20);
    }

    public function add_admin_menu() {
        // Add Settings submenu under the Steel Store post type menu
        add_submenu_page(
            'edit.php?post_type=steel_product',
            'Store Settings',
            'Settings',
            'manage_options',
            'steel-store-settings',
            [Steel_Store_Settings::instance(), 'render_settings_page']
        );
    }
}
