<?php
/**
 * Plugin Name: MastrLab 4.0
 * Description: High‑tech in‑browser audio mastering experience with realtime controls, presets, and export. Use shortcode [mastrlab].
 * Version: 4.0.0
 * Author: MastrLab
 * License: GPL-2.0+
 * Text Domain: mastrlab
 */

if (!defined('ABSPATH')) exit;

define('MASTRLAB_VERSION', '4.0.0');
define('MASTRLAB_URL', plugin_dir_url(__FILE__));
define('MASTRLAB_PATH', plugin_dir_path(__FILE__));

require_once MASTRLAB_PATH . 'includes/class-mastrlab-shortcode.php';

class MastrLabPlugin {
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue']);
        add_action('init', function() {
            if (!session_id()) session_start();
        });
    }
    public function enqueue() {
        // Styles
        wp_register_style('mastrlab-style', MASTRLAB_URL . 'assets/css/style.css', [], MASTRLAB_VERSION);
        wp_enqueue_style('mastrlab-style');

        // Vendor libs via robust CDN
        wp_enqueue_script('wavesurfer', 'https://unpkg.com/wavesurfer.js@7.7.10/dist/wavesurfer.min.js', [], '7.7.10', true);
        wp_enqueue_script('wavesurfer-timeline', 'https://unpkg.com/wavesurfer.js@7.7.10/dist/plugins/timeline.min.js', ['wavesurfer'], '7.7.10', true);
        wp_enqueue_script('lamejs', 'https://cdn.jsdelivr.net/npm/lamejs@1.2.0/lame.min.js', [], '1.2.0', true);

        // App
        wp_enqueue_script('mastrlab-app', MASTRLAB_URL . 'assets/js/app.js', ['wavesurfer','wavesurfer-timeline','lamejs'], MASTRLAB_VERSION, true);
        wp_localize_script('mastrlab-app', 'MAStrlabConfig', [
            'assets' => ['logo' => MASTRLAB_URL . 'assets/img/logo.png'],
            'version' => MASTRLAB_VERSION
        ]);
    }
}

new MastrLabPlugin();
