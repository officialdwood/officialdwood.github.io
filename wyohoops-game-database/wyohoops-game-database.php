<?php
/**
 * Plugin Name: WyoHoops Game Database
 * Plugin URI: https://github.com/officialdwood/officialdwood.github.io
 * Description: A state-of-the-art WordPress plugin for managing Wyoming high school basketball team and game database with team ratings, rankings, and comparison tools.
 * Version: 1.0.0
 * Author: Official D Wood
 * Author URI: https://officialdwood.github.io
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wyohoops-gamedb
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Plugin version.
 */
define('WYOHOOPS_VERSION', '1.0.0');

/**
 * Plugin paths.
 */
define('WYOHOOPS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WYOHOOPS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WYOHOOPS_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_wyohoops_gamedb() {
    require_once WYOHOOPS_PLUGIN_DIR . 'includes/class-activator.php';
    WyoHoops_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_wyohoops_gamedb() {
    require_once WYOHOOPS_PLUGIN_DIR . 'includes/class-deactivator.php';
    WyoHoops_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_wyohoops_gamedb');
register_deactivation_hook(__FILE__, 'deactivate_wyohoops_gamedb');

/**
 * The core plugin class.
 */
require WYOHOOPS_PLUGIN_DIR . 'includes/class-plugin.php';

/**
 * Begins execution of the plugin.
 */
function run_wyohoops_gamedb() {
    $plugin = new WyoHoops_Plugin();
    $plugin->run();
}
run_wyohoops_gamedb();
