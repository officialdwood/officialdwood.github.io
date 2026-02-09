<?php
/**
 * The core plugin class.
 *
 * @package WyoHoops_GameDB
 */

class WyoHoops_Plugin {

    /**
     * The loader that's responsible for maintaining and registering all hooks.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     */
    protected $version;

    /**
     * Initialize the plugin.
     */
    public function __construct() {
        $this->plugin_name = 'wyohoops-gamedb';
        $this->version = WYOHOOPS_VERSION;
        
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        require_once WYOHOOPS_PLUGIN_DIR . 'includes/class-repository-teams.php';
        require_once WYOHOOPS_PLUGIN_DIR . 'includes/class-repository-games.php';
        require_once WYOHOOPS_PLUGIN_DIR . 'includes/class-stats-service.php';
        require_once WYOHOOPS_PLUGIN_DIR . 'includes/class-admin.php';
        require_once WYOHOOPS_PLUGIN_DIR . 'includes/class-public.php';
        require_once WYOHOOPS_PLUGIN_DIR . 'includes/class-rest-api.php';
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     */
    private function define_admin_hooks() {
        $admin = new WyoHoops_Admin($this->get_plugin_name(), $this->get_version());
        
        add_action('admin_menu', array($admin, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($admin, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($admin, 'enqueue_scripts'));
        
        // AJAX handlers for admin
        add_action('wp_ajax_wyohoops_save_team', array($admin, 'ajax_save_team'));
        add_action('wp_ajax_wyohoops_delete_team', array($admin, 'ajax_delete_team'));
        add_action('wp_ajax_wyohoops_save_game', array($admin, 'ajax_save_game'));
        add_action('wp_ajax_wyohoops_delete_game', array($admin, 'ajax_delete_game'));
        add_action('wp_ajax_wyohoops_import_default_teams', array($admin, 'ajax_import_default_teams'));
        add_action('wp_ajax_wyohoops_recalculate_stats', array($admin, 'ajax_recalculate_stats'));
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     */
    private function define_public_hooks() {
        $public = new WyoHoops_Public($this->get_plugin_name(), $this->get_version());
        
        add_action('wp_enqueue_scripts', array($public, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($public, 'enqueue_scripts'));
        add_shortcode('wyohoops_gamedb', array($public, 'render_shortcode'));
        
        // REST API
        $rest_api = new WyoHoops_REST_API();
        add_action('rest_api_init', array($rest_api, 'register_routes'));
    }

    /**
     * Run the plugin.
     */
    public function run() {
        // Plugin is now running with all hooks registered
    }

    /**
     * Get the plugin name.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Get the version number.
     */
    public function get_version() {
        return $this->version;
    }
}
