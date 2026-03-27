// Steel Store - Frontend JavaScript
(function($) {
    'use strict';

    // Cart storage
    let cart = JSON.parse(localStorage.getItem('steel_store_cart')) || [];
    let currentProducts = steelStoreProducts || [];
    let currentTag = $('.steel-store-tab.active').data('tag') || 'Panels';
    let currentSearch = '';

    // Initialize
    $(document).ready(function() {
        initializeTabs();
        initializeSearch();
        initializeCart();
        renderProducts();
        updateCartCount();
    });

    // Tab functionality
    function initializeTabs() {
        $('.steel-store-tab').on('click', function() {
            $('.steel-store-tab').removeClass('active');
            $(this).addClass('active');
            currentTag = $(this).data('tag');
            currentSearch = '';
            $('#steel-store-search-input').val('');
            renderProducts();
        });
    }

    // Search functionality
    function initializeSearch() {
        $('#steel-store-search-btn').on('click', function() {
            currentSearch = $('#steel-store-search-input').val().trim();
            renderProducts();
        });

        $('#steel-store-search-input').on('keypress', function(e) {
            if (e.which === 13) {
                currentSearch = $(this).val().trim();
                renderProducts();
            }
        });
    }

    // Render products
    function renderProducts() {
        const $container = $('#steel-store-products');
        let filteredProducts = currentProducts;

        // Filter by tag
        if (currentTag) {
            filteredProducts = filteredProducts.filter(product => 
                product.tags.includes(currentTag)
            );
        }

        // Filter by search
        if (currentSearch) {
            const searchLower = currentSearch.toLowerCase();
            filteredProducts = filteredProducts.filter(product => 
                product.title.toLowerCase().includes(searchLower) ||
                product.tags.some(tag => tag.toLowerCase().includes(searchLower))
            );
        }

        if (filteredProducts.length === 0) {
            $container.html('<div class="steel-store-loading">No products found</div>');
            return;
        }

        let html = '';
        filteredProducts.forEach(product => {
            // Clean product title - remove numbers and symbols at the start
            let cleanTitle = product.title.replace(/^[\d\s\-#.]+/, '').trim();
            
            html += `
                <div class="steel-product-card" data-product-id="${product.id}">
                    <div class="steel-product-image-wrapper">
                        <img src="${product.image}" alt="${escapeHtml(cleanTitle)}" class="steel-product-image">
                    </div>
                    <div class="steel-product-info">
                        <h3 class="steel-product-title">${escapeHtml(cleanTitle)}</h3>
                        <div class="steel-product-actions">
                            <button class="btn-add-to-cart" data-product-id="${product.id}">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        $container.html(html);

        // Bind add to cart
        $('.btn-add-to-cart').on('click', function(e) {
            e.stopPropagation();
            const productId = parseInt($(this).data('product-id'));
            addToCart(productId);
        });
    }

    // Cart functionality
    function initializeCart() {
        // Open cart modal
        $('#steel-store-cart-btn').on('click', function() {
            renderCart();
            $('#steel-store-cart-modal').addClass('active');
        });

        // Close cart modal
        $('#cart-modal-close').on('click', function() {
            $('#steel-store-cart-modal').removeClass('active');
        });

        // Click outside to close
        $('#steel-store-cart-modal').on('click', function(e) {
            if ($(e.target).is('#steel-store-cart-modal')) {
                $(this).removeClass('active');
            }
        });

        // Print cart
        $('#cart-print-btn').on('click', function() {
            window.print();
        });

        // Submit cart
        $('#cart-submit-btn').on('click', function() {
            if (cart.length === 0) {
                alert('Your cart is empty');
                return;
            }
            $('#steel-store-cart-modal').removeClass('active');
            $('#steel-store-order-modal').addClass('active');
        });

        // Order form
        initializeOrderForm();
    }

    // Initialize order form
    function initializeOrderForm() {
        // Close order modal
        $('#order-modal-close, #order-cancel-btn').on('click', function() {
            $('#steel-store-order-modal').removeClass('active');
        });

        // Click outside to close
        $('#steel-store-order-modal').on('click', function(e) {
            if ($(e.target).is('#steel-store-order-modal')) {
                $(this).removeClass('active');
            }
        });

        // Submit order form
        $('#steel-store-order-form').on('submit', function(e) {
            e.preventDefault();

            const formData = {
                action: 'steel_store_submit_cart',
                nonce: steelStoreData.nonce,
                customer_name: $('#customer-name').val(),
                customer_email: $('#customer-email').val(),
                customer_notes: $('#customer-notes').val(),
                cart_items: JSON.stringify(cart)
            };

            $.ajax({
                url: steelStoreData.ajaxUrl,
                type: 'POST',
                data: formData,
                beforeSend: function() {
                    $('#steel-store-order-form button[type="submit"]').prop('disabled', true).text('Submitting...');
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        cart = [];
                        saveCart();
                        updateCartCount();
                        $('#steel-store-order-modal').removeClass('active');
                        $('#steel-store-order-form')[0].reset();
                    } else {
                        alert(response.data.message || 'Failed to submit order');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                },
                complete: function() {
                    $('#steel-store-order-form button[type="submit"]').prop('disabled', false).text('Submit Order');
                }
            });
        });
    }

    // Add to cart
    function addToCart(productId) {
        const product = currentProducts.find(p => p.id === productId);
        if (!product) return;

        const existingItem = cart.find(item => item.id === productId);
        if (existingItem) {
            existingItem.quantity++;
        } else {
            const cartItem = {
                id: product.id,
                title: product.title,
                image: product.image,
                tags: product.tags,
                quantity: 1
            };
            
            // Add default length for panel items
            if (product.tags.includes('Panels')) {
                cartItem.length = "8'0\""; // Default 8 feet
            }
            
            cart.push(cartItem);
        }

        saveCart();
        updateCartCount();
        
        // Visual feedback
        const $btn = $(`.btn-add-to-cart[data-product-id="${productId}"]`);
        const originalText = $btn.text();
        $btn.text('Added!').prop('disabled', true);
        setTimeout(() => {
            $btn.text(originalText).prop('disabled', false);
        }, 1000);
    }

    // Remove from cart
    function removeFromCart(productId) {
        cart = cart.filter(item => item.id !== productId);
        saveCart();
        updateCartCount();
        renderCart();
    }

    // Update quantity
    function updateQuantity(productId, quantity) {
        const item = cart.find(item => item.id === productId);
        if (item) {
            item.quantity = Math.max(1, parseInt(quantity) || 1);
            saveCart();
        }
    }

    // Update length (for panels)
    function updateLength(productId, length) {
        const item = cart.find(item => item.id === productId);
        if (item) {
            item.length = length;
            saveCart();
        }
    }

    // Generate length options (3'0" to 45'0" in quarter inch increments)
    function generateLengthOptions() {
        const options = [];
        for (let feet = 3; feet <= 45; feet++) {
            for (let quarterInches = 0; quarterInches < 48; quarterInches++) {
                const inches = quarterInches / 4;
                const wholeFeet = feet + Math.floor(inches / 12);
                const remainingInches = inches % 12;
                
                if (wholeFeet > 45) break;
                
                let inchStr = '';
                if (remainingInches === 0) {
                    inchStr = '0';
                } else if (remainingInches === Math.floor(remainingInches)) {
                    inchStr = remainingInches.toString();
                } else {
                    inchStr = remainingInches.toFixed(2).replace(/\.?0+$/, '');
                }
                
                const lengthStr = `${wholeFeet}'${inchStr}"`;
                options.push(lengthStr);
                
                if (wholeFeet === 45 && remainingInches === 0) break;
            }
        }
        return options;
    }

    // Render cart
    function renderCart() {
        const $body = $('#cart-modal-body');

        if (cart.length === 0) {
            $body.html('<div class="cart-empty-message">Your cart is empty</div>');
            return;
        }

        const lengthOptions = generateLengthOptions();
        
        let html = '';
        cart.forEach(item => {
            const isPanel = item.tags.includes('Panels');
            const currentLength = item.length || "8'0\"";
            
            html += `
                <div class="cart-item" data-product-id="${item.id}">
                    <img src="${item.image}" alt="${escapeHtml(item.title)}" class="cart-item-image">
                    <div class="cart-item-info">
                        <h4 class="cart-item-title">${escapeHtml(item.title)}</h4>
                        <div class="cart-item-quantity">
                            <label>Quantity:</label>
                            <input type="number" class="cart-quantity-input" data-product-id="${item.id}" value="${item.quantity}" min="1">
                        </div>
                        ${isPanel ? `
                        <div class="cart-item-length">
                            <label>Length:</label>
                            <select class="cart-length-select" data-product-id="${item.id}">
                                ${lengthOptions.map(opt => 
                                    `<option value="${opt}" ${opt === currentLength ? 'selected' : ''}>${opt}</option>`
                                ).join('')}
                            </select>
                        </div>
                        ` : ''}
                    </div>
                    <div class="cart-item-actions">
                        <button class="btn-remove-item" data-product-id="${item.id}">Remove</button>
                    </div>
                </div>
            `;
        });

        $body.html(html);

        // Bind events
        $('.btn-remove-item').on('click', function() {
            const productId = parseInt($(this).data('product-id'));
            removeFromCart(productId);
        });

        $('.cart-quantity-input').on('change', function() {
            const productId = parseInt($(this).data('product-id'));
            const quantity = $(this).val();
            updateQuantity(productId, quantity);
        });

        $('.cart-length-select').on('change', function() {
            const productId = parseInt($(this).data('product-id'));
            const length = $(this).val();
            updateLength(productId, length);
        });
    }

    // Update cart count
    function updateCartCount() {
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        $('.cart-count').text(totalItems);
    }

    // Save cart to localStorage
    function saveCart() {
        localStorage.setItem('steel_store_cart', JSON.stringify(cart));
    }

    // Utility: Escape HTML
    // Defense-in-depth XSS prevention. While data comes from server (trusted),
    // this provides an additional security layer in case of database compromise
    // or other server-side vulnerabilities. Ensures safe rendering in all contexts.
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

})(jQuery);
