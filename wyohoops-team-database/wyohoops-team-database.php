<?php
/**
 * Plugin Name: WyoHoops Team Database
 * Description: Centralized Wyoming HS basketball teams and rosters database with admin tools, shortcode, and REST API.
 * Version: 1.0.0
 * Author: WyoHoops
 * Text Domain: wyohoops-team-database
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WYOHOOPS_VERSION', '1.0.0' );
define( 'WYOHOOPS_DB_VERSION', '1.0.0' );
define( 'WYOHOOPS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WYOHOOPS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once WYOHOOPS_PLUGIN_DIR . 'includes/class-wyohoops-functions.php';
require_once WYOHOOPS_PLUGIN_DIR . 'includes/class-wyohoops-activator.php';
require_once WYOHOOPS_PLUGIN_DIR . 'includes/class-wyohoops-admin.php';
require_once WYOHOOPS_PLUGIN_DIR . 'includes/class-wyohoops-settings.php';
require_once WYOHOOPS_PLUGIN_DIR . 'includes/class-wyohoops-rest.php';
require_once WYOHOOPS_PLUGIN_DIR . 'includes/class-wyohoops-shortcodes.php';

register_activation_hook( __FILE__, array( 'WyoHoops_Activator', 'activate' ) );
register_uninstall_hook( __FILE__, array( 'WyoHoops_Activator', 'uninstall' ) );

final class WyoHoops_Team_Database {
    /**
     * @var WyoHoops_Functions
     */
    private $functions;

    public function __construct() {
        add_action( 'plugins_loaded', array( $this, 'init' ) );
    }

    public function init() {
        $this->functions = new WyoHoops_Functions();

        ( new WyoHoops_Admin( $this->functions ) )->hooks();
        ( new WyoHoops_Settings( $this->functions ) )->hooks();
        ( new WyoHoops_REST( $this->functions ) )->hooks();
        ( new WyoHoops_Shortcodes( $this->functions ) )->hooks();

        add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
    }

    public function register_assets() {
        wp_register_style( 'wyohoops-front', WYOHOOPS_PLUGIN_URL . 'assets/css/wyohoops-front.css', array(), WYOHOOPS_VERSION );
        wp_register_script( 'wyohoops-front', WYOHOOPS_PLUGIN_URL . 'assets/js/wyohoops-front.js', array( 'jquery' ), WYOHOOPS_VERSION, true );
    }
}

new WyoHoops_Team_Database();

