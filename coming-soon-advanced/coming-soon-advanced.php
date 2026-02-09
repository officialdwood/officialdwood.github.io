<?php
/**
 * Plugin Name: Coming Soon Advanced
 * Description: Advanced coming soon page with golden metallic text, smoke effects, and customizable settings.
 * Version: 1.0.0
 * Author: DWood
 * License: GPL-2.0+
 * Text Domain: coming-soon-advanced
 */

if (!defined('ABSPATH')) exit;

define('CSA_VERSION', '1.0.0');
define('CSA_URL', plugin_dir_url(__FILE__));
define('CSA_PATH', plugin_dir_path(__FILE__));

// Include admin settings
require_once CSA_PATH . 'includes/admin-settings.php';

class ComingSoonAdvanced {
    
    public function __construct() {
        // Hook to check if coming soon is enabled
        add_action('template_redirect', [$this, 'maybe_show_coming_soon']);
        
        // Enqueue scripts and styles for the coming soon page
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
    }
    
    /**
     * Check if coming soon mode is enabled and show the page
     */
    public function maybe_show_coming_soon() {
        // Don't show for logged in admins
        if (current_user_can('manage_options')) {
            return;
        }
        
        // Check if coming soon is enabled
        $is_enabled = get_option('csa_enabled', '0');
        
        if ($is_enabled === '1') {
            $this->display_coming_soon_page();
            exit;
        }
    }
    
    /**
     * Display the coming soon page
     */
    private function display_coming_soon_page() {
        // Get settings
        $logo_url = get_option('csa_logo_url', '');
        $bg_image_url = get_option('csa_bg_image_url', '');
        $description = get_option('csa_description', 'Home Of The Most Advanced Basketball Player Database and Player Portal.');
        
        // Set default background to matte black
        $bg_style = 'background-color: #1a1a1a;';
        if (!empty($bg_image_url)) {
            $bg_style = 'background-image: url(' . esc_url($bg_image_url) . '); background-size: cover; background-position: center;';
        }
        
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?php bloginfo('name'); ?> - Coming Soon</title>
            <?php wp_head(); ?>
        </head>
        <body class="csa-coming-soon-page">
            <div class="csa-container" style="<?php echo esc_attr($bg_style); ?>">
                <!-- Smoke overlay -->
                <div class="csa-smoke-overlay"></div>
                
                <div class="csa-content">
                    <?php if (!empty($logo_url)): ?>
                        <div class="csa-logo">
                            <img src="<?php echo esc_url($logo_url); ?>" alt="<?php bloginfo('name'); ?>">
                        </div>
                    <?php endif; ?>
                    
                    <div class="csa-description">
                        <?php echo esc_html($description); ?>
                    </div>
                    
                    <div class="csa-coming-soon">
                        Coming Soon...
                    </div>
                </div>
            </div>
            <?php wp_footer(); ?>
        </body>
        </html>
        <?php
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Only enqueue if coming soon is enabled or if we're previewing
        $is_enabled = get_option('csa_enabled', '0');
        
        if ($is_enabled === '1' || (isset($_GET['csa_preview']) && current_user_can('manage_options'))) {
            wp_enqueue_style('csa-frontend-style', CSA_URL . 'assets/css/frontend.css', [], CSA_VERSION);
            wp_enqueue_script('csa-frontend-script', CSA_URL . 'assets/js/frontend.js', ['jquery'], CSA_VERSION, true);
        }
    }
}

// Initialize the plugin
new ComingSoonAdvanced();
