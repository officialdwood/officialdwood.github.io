<?php
/**
 * Plugin Name: Building Visualizer
 * Description: Interactive 3D building configurator with customizable dimensions, roof pitch, and colors. Use shortcode [building_visualizer].
 * Version: 1.0.0
 * Author: Protech Buildings
 * License: GPL-2.0+
 * Text Domain: building-visualizer
 */

if (!defined('ABSPATH')) exit;

define('BV_VERSION', '1.0.0');
define('BV_URL', plugin_dir_url(__FILE__));
define('BV_PATH', plugin_dir_path(__FILE__));

require_once BV_PATH . 'includes/class-bv-admin-settings.php';
require_once BV_PATH . 'includes/class-bv-shortcode.php';

class BuildingVisualizerPlugin {
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin']);
        add_action('init', function() {
            if (!session_id()) session_start();
        });
    }
    
    public function enqueue_frontend() {
        // Styles
        wp_register_style('bv-style', BV_URL . 'assets/css/style.css', [], BV_VERSION);
        wp_enqueue_style('bv-style');
        
        // Scripts
        wp_enqueue_script('bv-app', BV_URL . 'assets/js/app.js', ['jquery'], BV_VERSION, true);
        
        // Get saved colors from options
        $colors = get_option('bv_colors', [
            'roofing' => [
                ['name' => 'Charcoal', 'rgb' => '54,69,79'],
                ['name' => 'Barn Red', 'rgb' => '139,26,33'],
                ['name' => 'Evergreen', 'rgb' => '34,94,68']
            ],
            'siding' => [
                ['name' => 'White', 'rgb' => '245,245,245'],
                ['name' => 'Tan', 'rgb' => '210,180,140'],
                ['name' => 'Gray', 'rgb' => '128,128,128']
            ],
            'wainscott' => [
                ['name' => 'Brown', 'rgb' => '101,67,33'],
                ['name' => 'Black', 'rgb' => '30,30,30'],
                ['name' => 'Dark Gray', 'rgb' => '64,64,64']
            ]
        ]);
        
        wp_localize_script('bv-app', 'BVConfig', [
            'version' => BV_VERSION,
            'colors' => $colors
        ]);
    }
    
    public function enqueue_admin($hook) {
        if ($hook !== 'settings_page_building-visualizer') {
            return;
        }
        wp_enqueue_style('bv-admin-style', BV_URL . 'assets/css/admin-style.css', [], BV_VERSION);
    }
}

new BuildingVisualizerPlugin();
