<?php
if (!defined('ABSPATH')) exit;

$all_tags = Steel_Store_Shortcode::get_all_tags();
$default_tag = isset($atts['default_tag']) ? $atts['default_tag'] : 'Panels';
?>

<div class="steel-store-container">
    <!-- Category Tabs -->
    <div class="steel-store-tabs">
        <?php foreach ($all_tags as $tag): ?>
            <button class="steel-store-tab <?php echo ($tag === $default_tag) ? 'active' : ''; ?>" data-tag="<?php echo esc_attr($tag); ?>">
                <?php echo esc_html($tag); ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Search Bar -->
    <div class="steel-store-search">
        <input type="text" id="steel-store-search-input" placeholder="Search products...">
        <button id="steel-store-search-btn">Search</button>
    </div>

    <!-- Products Grid -->
    <div class="steel-store-products" id="steel-store-products">
        <div class="steel-store-loading">Loading products...</div>
    </div>

    <!-- Cart Button (Fixed) -->
    <button class="steel-store-cart-btn" id="steel-store-cart-btn">
        <span class="cart-icon">🛒</span>
        <span class="cart-count">0</span>
    </button>

    <!-- Cart Modal -->
    <div class="steel-store-cart-modal" id="steel-store-cart-modal">
        <div class="cart-modal-content">
            <div class="cart-modal-header">
                <h2>Your Cart</h2>
                <button class="cart-modal-close" id="cart-modal-close">&times;</button>
            </div>
            <div class="cart-modal-body" id="cart-modal-body">
                <div class="cart-empty-message">Your cart is empty</div>
            </div>
            <div class="cart-modal-footer">
                <button class="btn-secondary" id="cart-print-btn">Print Cart</button>
                <button class="btn-primary" id="cart-submit-btn">Submit Order</button>
            </div>
        </div>
    </div>

    <!-- Order Form Modal -->
    <div class="steel-store-order-modal" id="steel-store-order-modal">
        <div class="order-modal-content">
            <div class="order-modal-header">
                <h2>Submit Your Order</h2>
                <button class="order-modal-close" id="order-modal-close">&times;</button>
            </div>
            <form id="steel-store-order-form">
                <div class="form-group">
                    <label for="customer-name">Name *</label>
                    <input type="text" id="customer-name" name="customer_name" required>
                </div>
                <div class="form-group">
                    <label for="customer-email">Email *</label>
                    <input type="email" id="customer-email" name="customer_email" required>
                </div>
                <div class="form-group">
                    <label for="customer-notes">Notes / Special Instructions</label>
                    <textarea id="customer-notes" name="customer_notes" rows="4"></textarea>
                </div>
                <div class="order-modal-footer">
                    <button type="button" class="btn-secondary" id="order-cancel-btn">Cancel</button>
                    <button type="submit" class="btn-primary">Submit Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden data for JavaScript -->
<script type="text/javascript">
    var steelStoreProducts = <?php echo json_encode(Steel_Store_Shortcode::get_products()); ?>;
</script>
