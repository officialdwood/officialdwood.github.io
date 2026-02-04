<?php
/**
 * Plugin Name: Steel Store
 * Plugin URI: https://www.brightidea.media
 * Description: Modern online store for steel products with cart functionality and email submission. Use shortcode [steel_store].
 * Version: 1.0.0
 * Author: Bright Idea Marketing
 * Author URI: https://www.brightidea.media
 * License: GPL-2.0+
 * Text Domain: steel-store
 */

if (!defined('ABSPATH')) exit;

define('STEEL_STORE_VERSION', '1.0.0');
define('STEEL_STORE_FILE', __FILE__);
define('STEEL_STORE_DIR', plugin_dir_path(__FILE__));
define('STEEL_STORE_URL', plugin_dir_url(__FILE__));
define('STEEL_STORE_ASSETS', STEEL_STORE_URL . 'assets');
define('STEEL_STORE_INCLUDES', STEEL_STORE_DIR . 'includes');
define('STEEL_STORE_TEMPLATES', STEEL_STORE_DIR . 'templates');

// Load includes
require_once STEEL_STORE_INCLUDES . '/class-steel-store-post-type.php';
require_once STEEL_STORE_INCLUDES . '/class-steel-store-shortcode.php';
require_once STEEL_STORE_INCLUDES . '/admin/class-steel-store-admin.php';
require_once STEEL_STORE_INCLUDES . '/admin/class-steel-store-settings.php';

class SteelStore {
    private static $instance = null;

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', [$this, 'init']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
        
        // AJAX handlers
        add_action('wp_ajax_steel_store_submit_cart', [$this, 'ajax_submit_cart']);
        add_action('wp_ajax_nopriv_steel_store_submit_cart', [$this, 'ajax_submit_cart']);
    }

    public function init() {
        // Initialize post type
        Steel_Store_Post_Type::instance();
        
        // Initialize shortcode
        Steel_Store_Shortcode::instance();
        
        // Initialize admin
        if (is_admin()) {
            Steel_Store_Admin::instance();
            Steel_Store_Settings::instance();
        }
    }

    public function enqueue_scripts() {
        // Montserrat font from Google Fonts
        wp_enqueue_style(
            'steel-store-fonts',
            'https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&display=swap',
            [],
            null
        );

        // Main stylesheet
        wp_enqueue_style(
            'steel-store-style',
            STEEL_STORE_ASSETS . '/css/style.css',
            [],
            STEEL_STORE_VERSION
        );

        // Main script
        wp_enqueue_script(
            'steel-store-script',
            STEEL_STORE_ASSETS . '/js/store.js',
            ['jquery'],
            STEEL_STORE_VERSION,
            true
        );

        // Localize script
        wp_localize_script('steel-store-script', 'steelStoreData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('steel_store_nonce'),
        ]);
    }

    public function admin_enqueue_scripts($hook) {
        // Only load on our admin pages
        if (strpos($hook, 'steel-store') === false && get_post_type() !== 'steel_product') {
            return;
        }

        wp_enqueue_media();
        
        wp_enqueue_style(
            'steel-store-admin-style',
            STEEL_STORE_ASSETS . '/css/admin-style.css',
            [],
            STEEL_STORE_VERSION
        );

        wp_enqueue_script(
            'steel-store-admin-script',
            STEEL_STORE_ASSETS . '/js/admin.js',
            ['jquery'],
            STEEL_STORE_VERSION,
            true
        );
    }

    public function ajax_submit_cart() {
        check_ajax_referer('steel_store_nonce', 'nonce');

        $cart_items = isset($_POST['cart_items']) ? json_decode(stripslashes($_POST['cart_items']), true) : [];
        $customer_name = isset($_POST['customer_name']) ? sanitize_text_field($_POST['customer_name']) : '';
        $customer_email = isset($_POST['customer_email']) ? sanitize_email($_POST['customer_email']) : '';
        $customer_notes = isset($_POST['customer_notes']) ? sanitize_textarea_field($_POST['customer_notes']) : '';

        if (empty($cart_items)) {
            wp_send_json_error(['message' => 'Cart is empty']);
        }

        // Get admin email from settings
        $admin_email = get_option('steel_store_admin_email', get_option('admin_email'));

        // Build email content
        $subject = 'New Steel Store Order - ' . $customer_name;
        $message = "New order received from Steel Store\n\n";
        $message .= "Customer Information:\n";
        $message .= "Name: {$customer_name}\n";
        $message .= "Email: {$customer_email}\n";
        $message .= "Notes: {$customer_notes}\n\n";
        $message .= "Order Items:\n";
        $message .= str_repeat('-', 50) . "\n";

        foreach ($cart_items as $item) {
            $message .= "Product: {$item['title']}\n";
            $message .= "Quantity: {$item['quantity']}\n";
            $message .= "Tags: " . implode(', ', $item['tags']) . "\n";
            $message .= str_repeat('-', 50) . "\n";
        }

        $message .= "\nSubmitted on: " . date('F j, Y g:i a') . "\n";

        $headers = ['Content-Type: text/plain; charset=UTF-8'];
        if (!empty($customer_email)) {
            $headers[] = "Reply-To: {$customer_email}";
        }

        $sent = wp_mail($admin_email, $subject, $message, $headers);

        if ($sent) {
            wp_send_json_success(['message' => 'Order submitted successfully!']);
        } else {
            wp_send_json_error(['message' => 'Failed to send order. Please try again.']);
        }
    }
}

// Initialize plugin
function steel_store() {
    return SteelStore::instance();
}
steel_store();

// Activation hook
register_activation_hook(__FILE__, function() {
    flush_rewrite_rules();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
});
