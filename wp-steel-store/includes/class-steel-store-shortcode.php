<?php
if (!defined('ABSPATH')) exit;

class Steel_Store_Shortcode {
    private static $instance = null;

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_shortcode('steel_store', [$this, 'render_shortcode']);
    }

    public function render_shortcode($atts) {
        $atts = shortcode_atts([
            'default_tag' => 'Panels',
        ], $atts);

        ob_start();
        include STEEL_STORE_TEMPLATES . '/store-frontend.php';
        return ob_get_clean();
    }

    public static function get_products($tag = '', $search = '') {
        $args = [
            'post_type' => 'steel_product',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC',
        ];

        if (!empty($tag)) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'steel_product_tag',
                    'field' => 'name',
                    'terms' => $tag,
                ]
            ];
        }

        if (!empty($search)) {
            $args['s'] = $search;
        }

        $query = new WP_Query($args);
        $products = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $image_id = get_post_meta($post_id, '_steel_product_image_id', true);
                $image_url = $image_id ? wp_get_attachment_url($image_id) : STEEL_STORE_ASSETS . '/img/placeholder.png';
                
                $terms = wp_get_post_terms($post_id, 'steel_product_tag');
                $tags = [];
                foreach ($terms as $term) {
                    $tags[] = $term->name;
                }

                $products[] = [
                    'id' => $post_id,
                    'title' => get_the_title(),
                    'description' => get_the_content(),
                    'image' => $image_url,
                    'tags' => $tags,
                ];
            }
            wp_reset_postdata();
        }

        return $products;
    }

    public static function get_all_tags() {
        $terms = get_terms([
            'taxonomy' => 'steel_product_tag',
            'hide_empty' => false,
        ]);

        $tags = [];
        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $tags[] = $term->name;
            }
        }

        return $tags;
    }
}
