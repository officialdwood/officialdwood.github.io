<?php
/**
 * Public-facing functionality of the plugin.
 *
 * @package WyoHoops_GameDB
 */

class WyoHoops_Public {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     */
    public function enqueue_styles() {
        global $post;
        
        // Only enqueue if shortcode is present
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'wyohoops_gamedb')) {
            wp_enqueue_style($this->plugin_name, WYOHOOPS_PLUGIN_URL . 'assets/css/public.css', array(), $this->version, 'all');
        }
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     */
    public function enqueue_scripts() {
        global $post;
        
        // Only enqueue if shortcode is present
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'wyohoops_gamedb')) {
            wp_enqueue_script($this->plugin_name, WYOHOOPS_PLUGIN_URL . 'assets/js/public.js', array('jquery'), $this->version, false);
            
            wp_localize_script($this->plugin_name, 'wyohoopsPublic', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'rest_url' => rest_url('wyohoops/v1/'),
                'nonce' => wp_create_nonce('wp_rest'),
                'default_gender' => get_option('wyohoops_default_gender', 'B')
            ));
        }
    }

    /**
     * Render the shortcode.
     */
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'default_tab' => 'teams',
            'classification' => '',
            'gender' => ''
        ), $atts, 'wyohoops_gamedb');
        
        ob_start();
        include WYOHOOPS_PLUGIN_DIR . 'templates/shortcode-gamedb.php';
        return ob_get_clean();
    }
}
