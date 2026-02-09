<?php
/**
 * Admin Settings for Coming Soon Advanced Plugin
 */

if (!defined('ABSPATH')) exit;

// Add admin menu
add_action('admin_menu', 'csa_add_admin_menu');
function csa_add_admin_menu() {
    add_menu_page(
        __('Coming Soon', 'coming-soon-advanced'),
        __('Coming Soon', 'coming-soon-advanced'),
        'manage_options',
        'coming-soon-advanced',
        'csa_render_settings_page',
        'dashicons-visibility',
        30
    );
    
    add_submenu_page(
        'coming-soon-advanced',
        __('Settings', 'coming-soon-advanced'),
        __('Settings', 'coming-soon-advanced'),
        'manage_options',
        'coming-soon-advanced',
        'csa_render_settings_page'
    );
}

// Register settings
add_action('admin_init', 'csa_register_settings');
function csa_register_settings() {
    register_setting('csa_settings', 'csa_enabled', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '0'
    ]);
    
    register_setting('csa_settings', 'csa_logo_url', [
        'type' => 'string',
        'sanitize_callback' => 'esc_url_raw',
        'default' => ''
    ]);
    
    register_setting('csa_settings', 'csa_bg_image_url', [
        'type' => 'string',
        'sanitize_callback' => 'esc_url_raw',
        'default' => ''
    ]);
    
    register_setting('csa_settings', 'csa_description', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_textarea_field',
        'default' => 'Home Of The Most Advanced Basketball Player Database and Player Portal.'
    ]);
}

// Render settings page
function csa_render_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Handle form submission
    if (isset($_POST['csa_settings_nonce']) && wp_verify_nonce($_POST['csa_settings_nonce'], 'csa_settings_save')) {
        update_option('csa_enabled', isset($_POST['csa_enabled']) ? '1' : '0');
        update_option('csa_logo_url', sanitize_text_field($_POST['csa_logo_url']));
        update_option('csa_bg_image_url', sanitize_text_field($_POST['csa_bg_image_url']));
        update_option('csa_description', sanitize_textarea_field($_POST['csa_description']));
        
        echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'coming-soon-advanced') . '</p></div>';
    }
    
    $enabled = get_option('csa_enabled', '0');
    $logo_url = get_option('csa_logo_url', '');
    $bg_image_url = get_option('csa_bg_image_url', '');
    $description = get_option('csa_description', 'Home Of The Most Advanced Basketball Player Database and Player Portal.');
    
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <form method="post" action="">
            <?php wp_nonce_field('csa_settings_save', 'csa_settings_nonce'); ?>
            
            <table class="form-table" role="presentation">
                <!-- Enable/Disable Toggle -->
                <tr>
                    <th scope="row">
                        <label for="csa_enabled"><?php _e('Enable Coming Soon Page', 'coming-soon-advanced'); ?></label>
                    </th>
                    <td>
                        <label class="csa-switch">
                            <input type="checkbox" id="csa_enabled" name="csa_enabled" value="1" <?php checked($enabled, '1'); ?>>
                            <span class="csa-slider"></span>
                        </label>
                        <p class="description">
                            <?php _e('When enabled, visitors will see the coming soon page. Administrators can still access the site normally.', 'coming-soon-advanced'); ?>
                        </p>
                    </td>
                </tr>
                
                <!-- Logo Upload -->
                <tr>
                    <th scope="row">
                        <label for="csa_logo_url"><?php _e('Logo', 'coming-soon-advanced'); ?></label>
                    </th>
                    <td>
                        <div class="csa-media-upload-wrapper">
                            <input type="text" id="csa_logo_url" name="csa_logo_url" value="<?php echo esc_attr($logo_url); ?>" class="regular-text" readonly>
                            <button type="button" class="button csa-upload-button" data-target="csa_logo_url" data-preview="csa_logo_preview">
                                <?php _e('Choose Logo', 'coming-soon-advanced'); ?>
                            </button>
                            <button type="button" class="button csa-remove-button" data-target="csa_logo_url" data-preview="csa_logo_preview">
                                <?php _e('Remove', 'coming-soon-advanced'); ?>
                            </button>
                        </div>
                        <?php if (!empty($logo_url)): ?>
                            <div class="csa-image-preview" id="csa_logo_preview">
                                <img src="<?php echo esc_url($logo_url); ?>" alt="Logo Preview" style="max-width: 200px; margin-top: 10px;">
                            </div>
                        <?php else: ?>
                            <div class="csa-image-preview" id="csa_logo_preview" style="display: none;">
                                <img src="" alt="Logo Preview" style="max-width: 200px; margin-top: 10px;">
                            </div>
                        <?php endif; ?>
                        <p class="description">
                            <?php _e('Upload a logo to display centered above the description text.', 'coming-soon-advanced'); ?>
                        </p>
                    </td>
                </tr>
                
                <!-- Background Image Upload -->
                <tr>
                    <th scope="row">
                        <label for="csa_bg_image_url"><?php _e('Background Image', 'coming-soon-advanced'); ?></label>
                    </th>
                    <td>
                        <div class="csa-media-upload-wrapper">
                            <input type="text" id="csa_bg_image_url" name="csa_bg_image_url" value="<?php echo esc_attr($bg_image_url); ?>" class="regular-text" readonly>
                            <button type="button" class="button csa-upload-button" data-target="csa_bg_image_url" data-preview="csa_bg_preview">
                                <?php _e('Choose Background', 'coming-soon-advanced'); ?>
                            </button>
                            <button type="button" class="button csa-remove-button" data-target="csa_bg_image_url" data-preview="csa_bg_preview">
                                <?php _e('Remove', 'coming-soon-advanced'); ?>
                            </button>
                        </div>
                        <?php if (!empty($bg_image_url)): ?>
                            <div class="csa-image-preview" id="csa_bg_preview">
                                <img src="<?php echo esc_url($bg_image_url); ?>" alt="Background Preview" style="max-width: 300px; margin-top: 10px;">
                            </div>
                        <?php else: ?>
                            <div class="csa-image-preview" id="csa_bg_preview" style="display: none;">
                                <img src="" alt="Background Preview" style="max-width: 300px; margin-top: 10px;">
                            </div>
                        <?php endif; ?>
                        <p class="description">
                            <?php _e('Upload a custom background image. If not set, a matte black background will be used.', 'coming-soon-advanced'); ?>
                        </p>
                    </td>
                </tr>
                
                <!-- Description Text -->
                <tr>
                    <th scope="row">
                        <label for="csa_description"><?php _e('Description Text', 'coming-soon-advanced'); ?></label>
                    </th>
                    <td>
                        <textarea id="csa_description" name="csa_description" rows="4" class="large-text"><?php echo esc_textarea($description); ?></textarea>
                        <p class="description">
                            <?php _e('Enter the main description text to display on the coming soon page.', 'coming-soon-advanced'); ?>
                        </p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
        
        <?php if ($enabled === '1'): ?>
        <div class="csa-preview-notice">
            <p>
                <strong><?php _e('Coming Soon page is currently active!', 'coming-soon-advanced'); ?></strong>
                <a href="<?php echo esc_url(home_url('/?csa_preview=1')); ?>" target="_blank" class="button button-secondary">
                    <?php _e('Preview Coming Soon Page', 'coming-soon-advanced'); ?>
                </a>
            </p>
        </div>
        <?php endif; ?>
    </div>
    
    <style>
        .csa-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .csa-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .csa-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .csa-slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .csa-slider {
            background-color: #2196F3;
        }
        input:checked + .csa-slider:before {
            transform: translateX(26px);
        }
        .csa-media-upload-wrapper {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px;
        }
        .csa-image-preview img {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
        .csa-preview-notice {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            padding: 15px;
            margin-top: 20px;
        }
        .csa-preview-notice p {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }
    </style>
    <?php
}

// Enqueue admin scripts
add_action('admin_enqueue_scripts', 'csa_enqueue_admin_scripts');
function csa_enqueue_admin_scripts($hook) {
    if ($hook !== 'toplevel_page_coming-soon-advanced') {
        return;
    }
    
    wp_enqueue_media();
    wp_enqueue_script('csa-admin-script', CSA_URL . 'assets/js/admin.js', ['jquery'], CSA_VERSION, true);
}
