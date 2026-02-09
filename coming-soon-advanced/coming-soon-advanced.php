<?php
/**
 * Plugin Name: Coming Soon Advanced
 * Description: Advanced coming soon page with golden metallic text, smoke effects, and customizable settings.
 * Version: 2.0.0
 * Author: DWood
 * License: GPL-2.0+
 * Text Domain: coming-soon-advanced
 */

if (!defined('ABSPATH')) exit;

define('CSA_VERSION', '2.0.0');
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
        $bg_color = get_option('csa_bg_color', '#1a1a1a');
        $description = get_option('csa_description', 'Home Of The Most Advanced Basketball Player Database and Player Portal.');
        $description_color = get_option('csa_description_color', '#ffd700');
        $description_size = get_option('csa_description_size', 40);
        $coming_soon_color = get_option('csa_coming_soon_color', '#ffd700');
        $coming_soon_size = get_option('csa_coming_soon_size', 48);
        $button_text = get_option('csa_button_text', 'Email Us');
        $button_email = get_option('csa_button_email', '');
        $button_bg_color = get_option('csa_button_bg_color', '#ffd700');
        $button_text_color = get_option('csa_button_text_color', '#1a1a1a');
        
        // Set background style
        $bg_style = 'background-color: ' . esc_attr($bg_color) . ';';
        if (!empty($bg_image_url)) {
            $bg_style = 'background-image: url(' . esc_url($bg_image_url) . '); background-size: cover; background-position: center; background-color: ' . esc_attr($bg_color) . ';';
        }
        
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?php bloginfo('name'); ?> - Coming Soon</title>
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@1,700&display=swap" rel="stylesheet">
            <style>
                :root {
                    --desc-color: <?php echo esc_attr($description_color); ?>;
                    --desc-size: <?php echo esc_attr($description_size); ?>px;
                    --cs-color: <?php echo esc_attr($coming_soon_color); ?>;
                    --cs-size: <?php echo esc_attr($coming_soon_size); ?>px;
                    --btn-bg: <?php echo esc_attr($button_bg_color); ?>;
                    --btn-text: <?php echo esc_attr($button_text_color); ?>;
                }
            </style>
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
                    
                    <?php if (!empty($button_email) && !empty($button_text)): ?>
                        <div class="csa-button-wrapper">
                            <a href="mailto:<?php echo esc_attr($button_email); ?>" class="csa-email-button">
                                <?php echo esc_html($button_text); ?>
                            </a>
                        </div>
                    <?php endif; ?>
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
