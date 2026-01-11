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
        add_action('delete_post', [$this, 'clear_gallery_cache']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('admin_menu', [$this, 'add_bulk_upload_page']);
        add_action('wp_ajax_cg29ga_bulk_upload', [$this, 'handle_bulk_upload']);
        add_action('wp_ajax_cg29ga_upload_file', [$this, 'handle_file_upload']);
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
        
        // Gallery colors management meta box
        add_meta_box(
            'cg29ga_gallery_colors',
            __('Gallery Colors', 'color-gallery-29ga'),
            [$this, 'render_gallery_colors_meta_box'],
            'cg29ga_gallery',
            'normal',
            'default'
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
        $max_rows = get_post_meta($post->ID, '_cg29ga_max_rows', true) ?: '0';
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
            <label for="cg29ga_max_rows"><?php _e('Maximum rows to display:', 'color-gallery-29ga'); ?></label>
            <input type="number" id="cg29ga_max_rows" name="cg29ga_max_rows" value="<?php echo esc_attr($max_rows); ?>" min="0" max="100" />
            <p class="description"><?php _e('If set, only this many rows will display initially. A "See More ↓" button will appear to show all colors. Set to 0 to show all colors (default).', 'color-gallery-29ga'); ?></p>
        </div>
        <div class="cg29ga-meta-field">
            <p><strong><?php _e('Shortcode:', 'color-gallery-29ga'); ?></strong></p>
            <code>[color_gallery_29ga_<?php echo esc_html(strtolower(str_replace(' ', '_', get_the_title($post->ID)))); ?>]</code>
            <p class="description"><?php _e('Use this shortcode to display this gallery on any page or post. The shortcode uses the gallery title converted to lowercase with spaces replaced by underscores.', 'color-gallery-29ga'); ?></p>
        </div>
        <?php
    }
    
    public function render_gallery_colors_meta_box($post) {
        // Get colors assigned to this gallery
        $args = [
            'post_type' => 'cg29ga_color',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_cg29ga_gallery_ids',
                    'value' => '"' . $post->ID . '"',
                    'compare' => 'LIKE'
                ]
            ],
            'orderby' => 'title',
            'order' => 'ASC'
        ];
        $gallery_colors = get_posts($args);
        
        // Get ALL colors for the add colors dropdown
        $all_colors = get_posts([
            'post_type' => 'cg29ga_color',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ]);
        
        wp_nonce_field('cg29ga_add_colors_to_gallery', 'cg29ga_add_colors_nonce');
        ?>
        <style>
            .cg29ga-colors-list { margin-bottom: 20px; }
            .cg29ga-color-item { padding: 10px; border-bottom: 1px solid #ddd; display: flex; align-items: center; gap: 15px; }
            .cg29ga-color-item:last-child { border-bottom: none; }
            .cg29ga-color-thumb { width: 50px; height: 50px; border: 1px solid #ddd; background-size: cover; background-position: center; flex-shrink: 0; }
            .cg29ga-color-name { flex-grow: 1; font-weight: 500; }
            .cg29ga-add-colors-section { border-top: 2px solid #ddd; padding-top: 20px; margin-top: 20px; }
            .cg29ga-add-colors-btn { margin-top: 10px; }
            #cg29ga-color-selector-container { display: none; margin-top: 15px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; max-height: 400px; overflow-y: auto; }
            #cg29ga-color-selector-container.active { display: block; }
            .cg29ga-selectable-color { padding: 8px; border-bottom: 1px solid #e0e0e0; display: flex; align-items: center; gap: 10px; }
            .cg29ga-selectable-color:hover { background: #fff; }
            .cg29ga-selectable-color input[type="checkbox"] { margin: 0; }
            .cg29ga-import-actions { margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd; }
        </style>
        <div class="cg29ga-colors-list">
            <h4><?php _e('Current Colors in this Gallery:', 'color-gallery-29ga'); ?></h4>
            <?php if (empty($gallery_colors)): ?>
                <p><?php _e('No colors in this gallery yet. Use "Add Colors" below to add colors.', 'color-gallery-29ga'); ?></p>
            <?php else: ?>
                <?php foreach ($gallery_colors as $color): 
                    $thumb_id = get_post_thumbnail_id($color->ID);
                    $color_value = get_post_meta($color->ID, '_cg29ga_color_value', true);
                    $thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'thumbnail') : '';
                    $bg_style = $thumb_url ? "background-image: url('" . esc_url($thumb_url) . "')" : "background-color: " . esc_attr($color_value);
                    ?>
                    <div class="cg29ga-color-item">
                        <div class="cg29ga-color-thumb" style="<?php echo $bg_style; ?>"></div>
                        <div class="cg29ga-color-name"><?php echo esc_html($color->post_title); ?></div>
                        <a href="<?php echo get_edit_post_link($color->ID); ?>" class="button button-small"><?php _e('Edit', 'color-gallery-29ga'); ?></a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="cg29ga-add-colors-section">
            <h4><?php _e('Add Colors to this Gallery:', 'color-gallery-29ga'); ?></h4>
            <p class="description"><?php _e('Select existing colors from your color database to add them to this gallery.', 'color-gallery-29ga'); ?></p>
            <button type="button" id="cg29ga-toggle-color-selector" class="button">
                <span class="dashicons dashicons-plus-alt"></span> <?php _e('Add Colors', 'color-gallery-29ga'); ?>
            </button>
            
            <div id="cg29ga-color-selector-container">
                <p><strong><?php _e('Select colors to add:', 'color-gallery-29ga'); ?></strong></p>
                <?php if (empty($all_colors)): ?>
                    <p><?php _e('No colors found. Create colors first.', 'color-gallery-29ga'); ?></p>
                <?php else: ?>
                    <?php 
                    $gallery_color_ids = array_map(function($c) { return $c->ID; }, $gallery_colors);
                    foreach ($all_colors as $color): 
                        $is_in_gallery = in_array($color->ID, $gallery_color_ids);
                        if ($is_in_gallery) continue; // Skip colors already in gallery
                        
                        $thumb_id = get_post_thumbnail_id($color->ID);
                        $color_value = get_post_meta($color->ID, '_cg29ga_color_value', true);
                        $thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'thumbnail') : '';
                        $bg_style = $thumb_url ? "background-image: url('" . esc_url($thumb_url) . "')" : "background-color: " . esc_attr($color_value);
                        ?>
                        <div class="cg29ga-selectable-color">
                            <input type="checkbox" name="cg29ga_add_color_ids[]" value="<?php echo esc_attr($color->ID); ?>" id="color-<?php echo esc_attr($color->ID); ?>" />
                            <div class="cg29ga-color-thumb" style="<?php echo $bg_style; ?>"></div>
                            <label for="color-<?php echo esc_attr($color->ID); ?>" style="margin: 0; cursor: pointer;"><?php echo esc_html($color->post_title); ?></label>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div class="cg29ga-import-actions">
                    <button type="button" id="cg29ga-select-all-colors" class="button button-small"><?php _e('Select All', 'color-gallery-29ga'); ?></button>
                    <button type="button" id="cg29ga-deselect-all-colors" class="button button-small"><?php _e('Deselect All', 'color-gallery-29ga'); ?></button>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Toggle color selector
            $('#cg29ga-toggle-color-selector').on('click', function() {
                $('#cg29ga-color-selector-container').toggleClass('active');
                var isActive = $('#cg29ga-color-selector-container').hasClass('active');
                $(this).find('.dashicons').toggleClass('dashicons-plus-alt', !isActive).toggleClass('dashicons-minus', isActive);
            });
            
            // Select all
            $('#cg29ga-select-all-colors').on('click', function() {
                $('#cg29ga-color-selector-container input[type="checkbox"]').prop('checked', true);
            });
            
            // Deselect all
            $('#cg29ga-deselect-all-colors').on('click', function() {
                $('#cg29ga-color-selector-container input[type="checkbox"]').prop('checked', false);
            });
        });
        </script>
        <?php
    }
    
    public function render_color_meta_box($post) {
        $color_value = get_post_meta($post->ID, '_cg29ga_color_value', true);
        $gallery_ids = get_post_meta($post->ID, '_cg29ga_gallery_ids', true);
        if (!is_array($gallery_ids)) {
            $gallery_ids = [];
        }
        
        // Backwards compatibility: check old single gallery meta
        $old_gallery_id = get_post_meta($post->ID, '_cg29ga_gallery_id', true);
        if ($old_gallery_id && empty($gallery_ids)) {
            $gallery_ids = [$old_gallery_id];
        }
        
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
            .cg29ga-galleries-list { max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f9f9f9; }
            .cg29ga-galleries-list label { display: block; margin-bottom: 8px; font-weight: normal; }
            .cg29ga-galleries-list input[type="checkbox"] { margin-right: 8px; }
        </style>
        <div class="cg29ga-meta-field">
            <label><?php _e('Assign to Galleries:', 'color-gallery-29ga'); ?></label>
            <p class="description" style="margin-bottom: 10px;"><?php _e('Select one or more galleries where this color should appear:', 'color-gallery-29ga'); ?></p>
            <div class="cg29ga-galleries-list">
                <?php if (empty($galleries)): ?>
                    <p><?php _e('No galleries found. Create a gallery first.', 'color-gallery-29ga'); ?></p>
                <?php else: ?>
                    <?php foreach ($galleries as $gallery): ?>
                        <label>
                            <input type="checkbox" 
                                   name="cg29ga_gallery_ids[]" 
                                   value="<?php echo esc_attr($gallery->ID); ?>" 
                                   <?php checked(in_array($gallery->ID, $gallery_ids)); ?> />
                            <?php echo esc_html($gallery->post_title); ?>
                        </label>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
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
        
        if (isset($_POST['cg29ga_max_rows'])) {
            update_post_meta($post_id, '_cg29ga_max_rows', intval($_POST['cg29ga_max_rows']));
        }
        
        // Handle adding colors to gallery
        if (isset($_POST['cg29ga_add_color_ids']) && is_array($_POST['cg29ga_add_color_ids'])) {
            foreach ($_POST['cg29ga_add_color_ids'] as $color_id) {
                $color_id = intval($color_id);
                if ($color_id) {
                    // Get existing gallery IDs for this color
                    $gallery_ids = get_post_meta($color_id, '_cg29ga_gallery_ids', true);
                    if (!is_array($gallery_ids)) {
                        $gallery_ids = [];
                    }
                    
                    // Add this gallery ID if not already present
                    if (!in_array($post_id, $gallery_ids)) {
                        $gallery_ids[] = $post_id;
                        update_post_meta($color_id, '_cg29ga_gallery_ids', $gallery_ids);
                    }
                }
            }
        }
        
        // Clear cache when gallery is saved
        delete_transient('cg29ga_gallery_shortcodes');
    }
    
    public function clear_gallery_cache($post_id) {
        $post_type = get_post_type($post_id);
        if ($post_type === 'cg29ga_gallery' || $post_type === 'cg29ga_color') {
            delete_transient('cg29ga_gallery_shortcodes');
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
        
        // Save multiple gallery IDs
        if (isset($_POST['cg29ga_gallery_ids']) && is_array($_POST['cg29ga_gallery_ids'])) {
            $gallery_ids = array_map('intval', $_POST['cg29ga_gallery_ids']);
            update_post_meta($post_id, '_cg29ga_gallery_ids', $gallery_ids);
            
            // Clear old single gallery meta for backwards compatibility
            delete_post_meta($post_id, '_cg29ga_gallery_id');
        } else {
            // No galleries selected - clear the meta
            update_post_meta($post_id, '_cg29ga_gallery_ids', []);
            delete_post_meta($post_id, '_cg29ga_gallery_id');
        }
        
        // Invalidate caches for all affected galleries
        delete_transient('cg29ga_gallery_shortcodes');
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
                    <tr>
                        <th scope="row">
                            <label><?php _e('Or Drag & Drop', 'color-gallery-29ga'); ?></label>
                        </th>
                        <td>
                            <div id="cg29ga_drop_zone" class="cg29ga-drop-zone">
                                <div class="drop-text">
                                    Drag & Drop Images Here<br>
                                    <span style="font-size: 14px; font-weight: normal;">or click "Choose Images" button above</span>
                                </div>
                            </div>
                            <p class="description"><?php _e('Drag multiple image files directly from your computer and drop them here', 'color-gallery-29ga'); ?></p>
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
        
        // Assign to gallery (using new multi-gallery system)
        update_post_meta($post_id, '_cg29ga_gallery_ids', [$gallery_id]);
        // Clear old single gallery meta
        delete_post_meta($post_id, '_cg29ga_gallery_id');
        
        wp_send_json_success([
            'message' => sprintf(__('Created color: %s', 'color-gallery-29ga'), $color_name),
            'post_id' => $post_id
        ]);
    }
    
    public function handle_file_upload() {
        check_ajax_referer('cg29ga_bulk_upload_nonce', 'nonce');
        
        if (!current_user_can('upload_files')) {
            wp_send_json_error(['message' => __('Permission denied.', 'color-gallery-29ga')]);
        }
        
        if (empty($_FILES['file'])) {
            wp_send_json_error(['message' => __('No file uploaded.', 'color-gallery-29ga')]);
        }
        
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        // Handle the file upload
        $file = $_FILES['file'];
        $upload_overrides = ['test_form' => false];
        $movefile = wp_handle_upload($file, $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            // Create attachment
            $attachment = [
                'post_mime_type' => $movefile['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', basename($movefile['file'])),
                'post_content' => '',
                'post_status' => 'inherit'
            ];
            
            $attach_id = wp_insert_attachment($attachment, $movefile['file']);
            
            if (!is_wp_error($attach_id)) {
                // Generate metadata
                $attach_data = wp_generate_attachment_metadata($attach_id, $movefile['file']);
                wp_update_attachment_metadata($attach_id, $attach_data);
                
                wp_send_json_success([
                    'id' => $attach_id,
                    'url' => wp_get_attachment_url($attach_id),
                    'filename' => basename($movefile['file']),
                    'title' => get_the_title($attach_id)
                ]);
            } else {
                wp_send_json_error(['message' => $attach_id->get_error_message()]);
            }
        } else {
            wp_send_json_error(['message' => $movefile['error']]);
        }
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
        $max_rows = get_post_meta($gallery->ID, '_cg29ga_max_rows', true) ?: '0';
        
        // Get colors for this gallery (supports multiple gallery assignments)
        $colors = get_posts([
            'post_type' => 'cg29ga_color',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'menu_order title',
            'order' => 'ASC'
        ]);
        
        // Filter colors that belong to this gallery (handles both old and new meta structure)
        $filtered_colors = [];
        foreach ($colors as $color) {
            $gallery_ids = get_post_meta($color->ID, '_cg29ga_gallery_ids', true);
            
            // Check new multi-gallery system
            if (is_array($gallery_ids) && in_array($gallery->ID, $gallery_ids)) {
                $filtered_colors[] = $color;
                continue;
            }
            
            // Backwards compatibility: check old single gallery meta
            $old_gallery_id = get_post_meta($color->ID, '_cg29ga_gallery_id', true);
            if ($old_gallery_id == $gallery->ID) {
                $filtered_colors[] = $color;
            }
        }
        
        $colors = $filtered_colors;
        
        if (empty($colors)) {
            return '<p>No colors in this gallery yet.</p>';
        }
        
        // Calculate if we need pagination
        $total_colors = count($colors);
        $colors_per_row = intval($columns);
        $max_rows_int = intval($max_rows);
        $show_see_more = false;
        $initially_visible = $total_colors;
        
        if ($max_rows_int > 0) {
            $initially_visible = $max_rows_int * $colors_per_row;
            if ($total_colors > $initially_visible) {
                $show_see_more = true;
            }
        }
        
        ob_start();
        ?>
        <div class="cg29ga-gallery" data-columns="<?php echo esc_attr($columns); ?>" data-max-visible="<?php echo esc_attr($initially_visible); ?>">
            <div class="cg29ga-grid" style="grid-template-columns: repeat(<?php echo esc_attr($columns); ?>, 1fr);">
                <?php 
                $color_index = 0;
                foreach ($colors as $color): 
                    $color_value = get_post_meta($color->ID, '_cg29ga_color_value', true);
                    $color_name = $color->post_title;
                    $featured_image = get_the_post_thumbnail_url($color->ID, 'large');
                    $is_hidden = $show_see_more && $color_index >= $initially_visible;
                ?>
                    <div class="cg29ga-tile<?php echo $is_hidden ? ' cg29ga-hidden' : ''; ?>" data-color-id="<?php echo esc_attr($color->ID); ?>">
                        <div class="cg29ga-chip" 
                             style="<?php echo $featured_image ? 'background-image: url(' . esc_url($featured_image) . ');' : 'background-color: ' . esc_attr($color_value) . ';'; ?>">
                        </div>
                        <div class="cg29ga-name"><?php echo esc_html($color_name); ?></div>
                    </div>
                <?php 
                    $color_index++;
                endforeach; 
                ?>
            </div>
            
            <?php if ($show_see_more): ?>
                <div class="cg29ga-see-more-container">
                    <button class="cg29ga-see-more-btn">
                        See More <span class="cg29ga-arrow">↓</span>
                    </button>
                </div>
            <?php endif; ?>
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
