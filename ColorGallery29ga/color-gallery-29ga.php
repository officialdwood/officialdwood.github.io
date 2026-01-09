<?php
/**
 * Plugin Name: Color Gallery 29ga
 * Description: A beautiful color gallery plugin with hover effects and expandable views. Use shortcode [color_gallery_29ga_<gallery_name>].
 * Version: 1.0.0
 * Author: Color Gallery
 * License: GPL-2.0+
 * Text Domain: color-gallery-29ga
 */

if (!defined('ABSPATH')) exit;

define('CG29GA_VERSION', '1.0.0');
define('CG29GA_URL', plugin_dir_url(__FILE__));
define('CG29GA_PATH', plugin_dir_path(__FILE__));

class ColorGallery29ga {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', [$this, 'register_post_types']);
        add_action('init', [$this, 'load_textdomain']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_cg29ga_gallery', [$this, 'save_gallery_meta']);
        add_action('save_post_cg29ga_color', [$this, 'save_color_meta']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('admin_menu', [$this, 'add_bulk_upload_page']);
        add_action('wp_ajax_cg29ga_bulk_upload', [$this, 'handle_bulk_upload']);
        add_shortcode('color_gallery_29ga', [$this, 'render_shortcode']);
        add_action('init', [$this, 'register_dynamic_shortcodes'], 20);
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('color-gallery-29ga', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function register_post_types() {
        // Register Gallery post type
        register_post_type('cg29ga_gallery', [
            'labels' => [
                'name' => __('Color Galleries', 'color-gallery-29ga'),
                'singular_name' => __('Color Gallery', 'color-gallery-29ga'),
                'add_new_item' => __('Add New Gallery', 'color-gallery-29ga'),
                'edit_item' => __('Edit Gallery', 'color-gallery-29ga'),
                'menu_name' => __('Color Galleries', 'color-gallery-29ga'),
            ],
            'public' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-art',
            'supports' => ['title'],
            'has_archive' => false,
            'show_in_rest' => true,
        ]);
        
        // Register Color post type
        register_post_type('cg29ga_color', [
            'labels' => [
                'name' => __('Colors', 'color-gallery-29ga'),
                'singular_name' => __('Color', 'color-gallery-29ga'),
                'add_new_item' => __('Add New Color', 'color-gallery-29ga'),
                'edit_item' => __('Edit Color', 'color-gallery-29ga'),
                'menu_name' => __('Colors', 'color-gallery-29ga'),
            ],
            'public' => true,
            'show_in_menu' => 'edit.php?post_type=cg29ga_gallery',
            'supports' => ['title', 'thumbnail'],
            'has_archive' => false,
            'show_in_rest' => true,
        ]);
        
        add_post_type_support('cg29ga_color', 'thumbnail');
    }
    
    public function add_meta_boxes() {
        // Gallery meta box
        add_meta_box(
            'cg29ga_gallery_settings',
            __('Gallery Settings', 'color-gallery-29ga'),
            [$this, 'render_gallery_meta_box'],
            'cg29ga_gallery',
            'normal',
            'high'
        );
        
        // Color meta box
        add_meta_box(
            'cg29ga_color_details',
            __('Color Details', 'color-gallery-29ga'),
            [$this, 'render_color_meta_box'],
            'cg29ga_color',
            'normal',
            'high'
        );
    }
    
    public function render_gallery_meta_box($post) {
        $columns = get_post_meta($post->ID, '_cg29ga_columns', true) ?: '6';
        wp_nonce_field('cg29ga_gallery_save', 'cg29ga_gallery_nonce');
        ?>
        <style>
            .cg29ga-meta-field { margin-bottom: 15px; }
            .cg29ga-meta-field label { display: block; font-weight: bold; margin-bottom: 5px; }
        </style>
        <div class="cg29ga-meta-field">
            <label for="cg29ga_columns"><?php _e('Columns per row:', 'color-gallery-29ga'); ?></label>
            <input type="number" id="cg29ga_columns" name="cg29ga_columns" value="<?php echo esc_attr($columns); ?>" min="1" max="12" />
            <p class="description"><?php _e('Number of color tiles per row (default: 6)', 'color-gallery-29ga'); ?></p>
        </div>
        <div class="cg29ga-meta-field">
            <p><strong><?php _e('Shortcode:', 'color-gallery-29ga'); ?></strong></p>
            <code>[color_gallery_29ga_<?php echo esc_html(strtolower(str_replace(' ', '_', get_the_title($post->ID)))); ?>]</code>
            <p class="description"><?php _e('Use this shortcode to display this gallery on any page or post. The shortcode uses the gallery title converted to lowercase with spaces replaced by underscores.', 'color-gallery-29ga'); ?></p>
        </div>
        <?php
    }
    
    public function render_color_meta_box($post) {
        $color_value = get_post_meta($post->ID, '_cg29ga_color_value', true);
        $gallery_id = get_post_meta($post->ID, '_cg29ga_gallery_id', true);
        
        wp_nonce_field('cg29ga_color_save', 'cg29ga_color_nonce');
        
        // Get all galleries
        $galleries = get_posts([
            'post_type' => 'cg29ga_gallery',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ]);
        ?>
        <style>
            .cg29ga-meta-field { margin-bottom: 15px; }
            .cg29ga-meta-field label { display: block; font-weight: bold; margin-bottom: 5px; }
            .cg29ga-color-preview { width: 100px; height: 100px; border: 1px solid #ddd; margin-top: 10px; }
        </style>
        <div class="cg29ga-meta-field">
            <label for="cg29ga_gallery_id"><?php _e('Assign to Gallery:', 'color-gallery-29ga'); ?></label>
            <select id="cg29ga_gallery_id" name="cg29ga_gallery_id" style="width: 100%; max-width: 400px;">
                <option value=""><?php _e('-- Select Gallery --', 'color-gallery-29ga'); ?></option>
                <?php foreach ($galleries as $gallery): ?>
                    <option value="<?php echo esc_attr($gallery->ID); ?>" <?php selected($gallery_id, $gallery->ID); ?>>
                        <?php echo esc_html($gallery->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="cg29ga-meta-field">
            <label for="cg29ga_color_value"><?php _e('Color Value (Hex) - Optional:', 'color-gallery-29ga'); ?></label>
            <input type="text" id="cg29ga_color_value" name="cg29ga_color_value" value="<?php echo esc_attr($color_value); ?>" placeholder="#FF5733" class="cg29ga-color-input" />
            <div class="cg29ga-color-preview" style="background-color: <?php echo esc_attr($color_value); ?>;"></div>
            <p class="description"><?php _e('Enter hex color code (e.g., #FF5733) OR use the Featured Image below to upload a color image from your media library. Featured Image takes priority if both are set.', 'color-gallery-29ga'); ?></p>
        </div>
        <div class="cg29ga-meta-field">
            <p><strong><?php _e('Featured Image (Recommended):', 'color-gallery-29ga'); ?></strong></p>
            <p class="description"><?php _e('Use "Set featured image" button in the sidebar to upload or select a color image from your media library. This is the recommended method if you already have color images.', 'color-gallery-29ga'); ?></p>
        </div>
        <?php
    }
    
    public function save_gallery_meta($post_id) {
        if (!isset($_POST['cg29ga_gallery_nonce']) || !wp_verify_nonce($_POST['cg29ga_gallery_nonce'], 'cg29ga_gallery_save')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        
        if (isset($_POST['cg29ga_columns'])) {
            update_post_meta($post_id, '_cg29ga_columns', intval($_POST['cg29ga_columns']));
        }
    }
    
    public function save_color_meta($post_id) {
        if (!isset($_POST['cg29ga_color_nonce']) || !wp_verify_nonce($_POST['cg29ga_color_nonce'], 'cg29ga_color_save')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        
        if (isset($_POST['cg29ga_color_value'])) {
            update_post_meta($post_id, '_cg29ga_color_value', sanitize_text_field($_POST['cg29ga_color_value']));
        }
        if (isset($_POST['cg29ga_gallery_id'])) {
            update_post_meta($post_id, '_cg29ga_gallery_id', intval($_POST['cg29ga_gallery_id']));
        }
    }
    
    public function enqueue_frontend_assets() {
        wp_enqueue_style('cg29ga-style', CG29GA_URL . 'assets/css/style.css', [], CG29GA_VERSION);
        wp_enqueue_script('cg29ga-app', CG29GA_URL . 'assets/js/app.js', ['jquery'], CG29GA_VERSION, true);
    }
    
    public function enqueue_admin_assets($hook) {
        global $post_type;
        if (('post.php' === $hook || 'post-new.php' === $hook) && 
            ($post_type === 'cg29ga_gallery' || $post_type === 'cg29ga_color')) {
            wp_enqueue_media();
            wp_enqueue_script('cg29ga-admin', CG29GA_URL . 'assets/admin/admin.js', ['jquery'], CG29GA_VERSION, true);
        }
        
        // Bulk upload page
        if (strpos($hook, 'cg29ga-bulk-upload') !== false) {
            wp_enqueue_media();
            wp_enqueue_style('cg29ga-bulk-upload', CG29GA_URL . 'assets/admin/bulk-upload.css', [], CG29GA_VERSION);
            wp_enqueue_script('cg29ga-bulk-upload', CG29GA_URL . 'assets/admin/bulk-upload.js', ['jquery'], CG29GA_VERSION, true);
            wp_localize_script('cg29ga-bulk-upload', 'cg29gaBulk', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('cg29ga_bulk_upload_nonce')
            ]);
        }
    }
    
    public function add_bulk_upload_page() {
        add_submenu_page(
            'edit.php?post_type=cg29ga_gallery',
            __('Bulk Upload Colors', 'color-gallery-29ga'),
            __('Bulk Upload', 'color-gallery-29ga'),
            'edit_posts',
            'cg29ga-bulk-upload',
            [$this, 'render_bulk_upload_page']
        );
    }
    
    public function render_bulk_upload_page() {
        // Get all galleries
        $galleries = get_posts([
            'post_type' => 'cg29ga_gallery',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ]);
        ?>
        <div class="wrap cg29ga-bulk-upload-wrap">
            <h1><?php _e('Bulk Upload Colors', 'color-gallery-29ga'); ?></h1>
            <p class="description"><?php _e('Select multiple images from your media library and assign names to add them to a gallery quickly.', 'color-gallery-29ga'); ?></p>
            
            <div class="cg29ga-bulk-form">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="cg29ga_bulk_gallery"><?php _e('Select Gallery', 'color-gallery-29ga'); ?></label>
                        </th>
                        <td>
                            <select id="cg29ga_bulk_gallery" name="gallery_id" required>
                                <option value=""><?php _e('-- Choose a Gallery --', 'color-gallery-29ga'); ?></option>
                                <?php foreach ($galleries as $gallery): ?>
                                    <option value="<?php echo esc_attr($gallery->ID); ?>">
                                        <?php echo esc_html($gallery->post_title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e('All uploaded colors will be added to this gallery.', 'color-gallery-29ga'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label><?php _e('Select Images', 'color-gallery-29ga'); ?></label>
                        </th>
                        <td>
                            <button type="button" class="button button-primary" id="cg29ga_select_images">
                                <?php _e('Choose Images from Media Library', 'color-gallery-29ga'); ?>
                            </button>
                            <p class="description"><?php _e('Select multiple images at once using Ctrl/Cmd + Click', 'color-gallery-29ga'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <div id="cg29ga_selected_images" class="cg29ga-selected-images"></div>
                
                <p class="submit">
                    <button type="button" class="button button-primary button-large" id="cg29ga_bulk_save" disabled>
                        <?php _e('Create All Colors', 'color-gallery-29ga'); ?>
                    </button>
                    <span class="spinner"></span>
                </p>
                
                <div id="cg29ga_bulk_messages" class="cg29ga-messages"></div>
            </div>
        </div>
        <?php
    }
    
    public function handle_bulk_upload() {
        check_ajax_referer('cg29ga_bulk_upload_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied.', 'color-gallery-29ga')]);
        }
        
        $gallery_id = intval($_POST['gallery_id']);
        $image_id = intval($_POST['image_id']);
        $color_name = sanitize_text_field($_POST['color_name']);
        
        if (!$gallery_id || !$image_id || empty($color_name)) {
            wp_send_json_error(['message' => __('Missing required data.', 'color-gallery-29ga')]);
        }
        
        // Create color post
        $post_id = wp_insert_post([
            'post_title' => $color_name,
            'post_type' => 'cg29ga_color',
            'post_status' => 'publish'
        ]);
        
        if (is_wp_error($post_id)) {
            wp_send_json_error(['message' => $post_id->get_error_message()]);
        }
        
        // Set featured image
        set_post_thumbnail($post_id, $image_id);
        
        // Assign to gallery
        update_post_meta($post_id, '_cg29ga_gallery_id', $gallery_id);
        
        wp_send_json_success([
            'message' => sprintf(__('Created color: %s', 'color-gallery-29ga'), $color_name),
            'post_id' => $post_id
        ]);
    }
    
    public function register_dynamic_shortcodes() {
        // Only register on frontend to avoid unnecessary queries in admin
        if (is_admin()) {
            return;
        }
        
        // Use transient cache to avoid repeated queries
        $galleries = get_transient('cg29ga_gallery_shortcodes');
        
        if (false === $galleries) {
            $galleries = get_posts([
                'post_type' => 'cg29ga_gallery',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'fields' => 'ids',
                'no_found_rows' => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false
            ]);
            
            // Cache for 1 hour
            set_transient('cg29ga_gallery_shortcodes', $galleries, HOUR_IN_SECONDS);
        }
        
        foreach ($galleries as $gallery_id) {
            $title = get_the_title($gallery_id);
            $slug = strtolower(str_replace(' ', '_', $title));
            add_shortcode('color_gallery_29ga_' . $slug, [$this, 'render_shortcode']);
        }
    }
    
    public function render_shortcode($atts, $content = null, $tag = '') {
        // Extract gallery name from tag (e.g., color_gallery_29ga_standard_color -> standard_color)
        $gallery_slug = str_replace('color_gallery_29ga_', '', $tag);
        
        if (empty($gallery_slug)) {
            return '<p>No gallery specified.</p>';
        }
        
        // Find gallery by matching title (case-insensitive, spaces replaced with underscores)
        $all_galleries = get_posts([
            'post_type' => 'cg29ga_gallery',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ]);
        
        $gallery = null;
        foreach ($all_galleries as $g) {
            $title_slug = strtolower(str_replace(' ', '_', $g->post_title));
            if ($title_slug === $gallery_slug) {
                $gallery = $g;
                break;
            }
        }
        
        if (!$gallery) {
            return '<p>Gallery "' . esc_html($gallery_slug) . '" not found.</p>';
        }
        $columns = get_post_meta($gallery->ID, '_cg29ga_columns', true) ?: '6';
        
        // Get colors for this gallery
        $colors = get_posts([
            'post_type' => 'cg29ga_color',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_cg29ga_gallery_id',
                    'value' => $gallery->ID,
                    'compare' => '='
                ]
            ],
            'orderby' => 'menu_order title',
            'order' => 'ASC'
        ]);
        
        if (empty($colors)) {
            return '<p>No colors in this gallery yet.</p>';
        }
        
        ob_start();
        ?>
        <div class="cg29ga-gallery" data-columns="<?php echo esc_attr($columns); ?>">
            <div class="cg29ga-grid" style="grid-template-columns: repeat(<?php echo esc_attr($columns); ?>, 1fr);">
                <?php foreach ($colors as $color): 
                    $color_value = get_post_meta($color->ID, '_cg29ga_color_value', true);
                    $color_name = $color->post_title;
                    $featured_image = get_the_post_thumbnail_url($color->ID, 'large');
                ?>
                    <div class="cg29ga-tile" data-color-id="<?php echo esc_attr($color->ID); ?>">
                        <div class="cg29ga-chip" 
                             style="<?php echo $featured_image ? 'background-image: url(' . esc_url($featured_image) . ');' : 'background-color: ' . esc_attr($color_value) . ';'; ?>">
                        </div>
                        <div class="cg29ga-name"><?php echo esc_html($color_name); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Modal for expanded view -->
        <div id="cg29ga-modal" class="cg29ga-modal">
            <span class="cg29ga-close">&times;</span>
            <button class="cg29ga-nav-arrow prev" aria-label="Previous color">‹</button>
            <button class="cg29ga-nav-arrow next" aria-label="Next color">›</button>
            <div class="cg29ga-modal-content">
                <div class="cg29ga-modal-chip"></div>
                <div class="cg29ga-modal-name"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize the plugin
ColorGallery29ga::get_instance();

// Clear shortcode cache when galleries are updated
add_action('save_post_cg29ga_gallery', function($post_id) {
    delete_transient('cg29ga_gallery_shortcodes');
}, 10, 1);

add_action('delete_post', function($post_id) {
    if (get_post_type($post_id) === 'cg29ga_gallery') {
        delete_transient('cg29ga_gallery_shortcodes');
    }
}, 10, 1);
