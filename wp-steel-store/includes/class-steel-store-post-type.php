<?php
if (!defined('ABSPATH')) exit;

class Steel_Store_Post_Type {
    private static $instance = null;

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', [$this, 'register_post_type']);
        add_action('init', [$this, 'register_taxonomy']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_steel_product', [$this, 'save_meta_boxes'], 10, 2);
    }

    public function register_post_type() {
        $labels = [
            'name' => 'Products',
            'singular_name' => 'Product',
            'menu_name' => 'Steel Store',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Product',
            'edit_item' => 'Edit Product',
            'new_item' => 'New Product',
            'view_item' => 'View Product',
            'search_items' => 'Search Products',
            'not_found' => 'No products found',
            'not_found_in_trash' => 'No products found in trash',
        ];

        $args = [
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-store',
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => ['title', 'editor', 'thumbnail'],
            'has_archive' => false,
            'rewrite' => false,
            'query_var' => false,
        ];

        register_post_type('steel_product', $args);
    }

    public function register_taxonomy() {
        $labels = [
            'name' => 'Product Tags',
            'singular_name' => 'Product Tag',
            'search_items' => 'Search Tags',
            'all_items' => 'All Tags',
            'edit_item' => 'Edit Tag',
            'update_item' => 'Update Tag',
            'add_new_item' => 'Add New Tag',
            'new_item_name' => 'New Tag Name',
            'menu_name' => 'Tags',
        ];

        $args = [
            'labels' => $labels,
            'hierarchical' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => false,
        ];

        register_taxonomy('steel_product_tag', ['steel_product'], $args);
    }

    /**
     * Create default product tags
     * Called on plugin activation
     */
    public static function create_default_tags() {
        $default_tags = ['Panels', 'Trim', 'Accessories', 'Fasteners', 'Building Materials', 'Tools'];
        foreach ($default_tags as $tag) {
            if (!term_exists($tag, 'steel_product_tag')) {
                wp_insert_term($tag, 'steel_product_tag');
            }
        }
    }

    public function add_meta_boxes() {
        add_meta_box(
            'steel_product_image',
            'Product Image',
            [$this, 'render_image_meta_box'],
            'steel_product',
            'side',
            'default'
        );
    }

    public function render_image_meta_box($post) {
        wp_nonce_field('steel_product_meta', 'steel_product_meta_nonce');
        
        $image_id = get_post_meta($post->ID, '_steel_product_image_id', true);
        $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
        ?>
        <div class="steel-product-image-wrapper">
            <input type="hidden" id="steel_product_image_id" name="steel_product_image_id" value="<?php echo esc_attr($image_id); ?>">
            <div class="steel-product-image-preview" style="margin-bottom: 10px;">
                <?php if ($image_url): ?>
                    <img src="<?php echo esc_url($image_url); ?>" style="max-width: 100%; height: auto;">
                <?php else: ?>
                    <p style="color: #666;">No image selected</p>
                <?php endif; ?>
            </div>
            <button type="button" class="button steel-upload-image-button">
                <?php echo $image_url ? 'Change Image' : 'Upload Image'; ?>
            </button>
            <?php if ($image_url): ?>
                <button type="button" class="button steel-remove-image-button" style="margin-left: 5px;">Remove</button>
            <?php endif; ?>
        </div>
        <?php
    }

    public function save_meta_boxes($post_id, $post) {
        // Security checks
        if (!isset($_POST['steel_product_meta_nonce']) || !wp_verify_nonce($_POST['steel_product_meta_nonce'], 'steel_product_meta')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save image ID
        if (isset($_POST['steel_product_image_id'])) {
            $image_id = absint($_POST['steel_product_image_id']);
            update_post_meta($post_id, '_steel_product_image_id', $image_id);
        } else {
            delete_post_meta($post_id, '_steel_product_image_id');
        }
    }
}
