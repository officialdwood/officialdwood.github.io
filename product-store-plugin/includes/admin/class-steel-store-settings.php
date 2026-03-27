<?php
if (!defined('ABSPATH')) exit;

class Steel_Store_Settings {
    private static $instance = null;

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_settings() {
        register_setting('steel_store_settings', 'steel_store_admin_email', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_email',
            'default' => get_option('admin_email'),
        ]);

        add_settings_section(
            'steel_store_general_section',
            'General Settings',
            [$this, 'general_section_callback'],
            'steel-store-settings'
        );

        add_settings_field(
            'steel_store_admin_email',
            'Order Notification Email',
            [$this, 'admin_email_field_callback'],
            'steel-store-settings',
            'steel_store_general_section'
        );
    }

    public function general_section_callback() {
        echo '<p>Configure your Steel Store settings below.</p>';
    }

    public function admin_email_field_callback() {
        $email = get_option('steel_store_admin_email', get_option('admin_email'));
        ?>
        <input type="email" name="steel_store_admin_email" value="<?php echo esc_attr($email); ?>" class="regular-text" required>
        <p class="description">Email address where order notifications will be sent.</p>
        <?php
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Handle messages
        if (isset($_GET['settings-updated'])) {
            add_settings_error('steel_store_messages', 'steel_store_message', 'Settings Saved', 'updated');
        }

        settings_errors('steel_store_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('steel_store_settings');
                do_settings_sections('steel-store-settings');
                submit_button('Save Settings');
                ?>
            </form>

            <div class="steel-store-info-box" style="margin-top: 30px; padding: 20px; background: #fff; border: 1px solid #ccd0d4; border-radius: 4px;">
                <h2>How to Use</h2>
                <p>Add the following shortcode to any page or Elementor widget to display your store:</p>
                <code style="padding: 10px; background: #f0f0f1; display: inline-block; border-radius: 3px;">[steel_store]</code>
                
                <h3 style="margin-top: 20px;">Managing Products</h3>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li>Go to <strong>Steel Store > All Products</strong> to manage your products</li>
                    <li>Click <strong>Add New</strong> to create a new product</li>
                    <li>Upload a .webp image (or any image format) for each product</li>
                    <li>Assign tags to organize your products (Panels, Trim, etc.)</li>
                    <li>Products are searchable by customers on the frontend</li>
                </ul>

                <h3 style="margin-top: 20px;">Order Processing</h3>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li>Customers can add products to their cart</li>
                    <li>They can view, print, or submit their cart</li>
                    <li>Submitted carts are emailed to the address configured above</li>
                    <li>No pricing is displayed (ready for future pricing implementation)</li>
                </ul>
            </div>
        </div>
        <?php
    }
}
