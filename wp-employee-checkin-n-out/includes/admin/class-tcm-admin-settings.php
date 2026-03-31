<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class TCM_Admin_Settings
 *
 * Handles the admin settings page for UI customization.
 */

class TCM_Admin_Settings
{
    private $options;

    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_plugin_page'], 10);
        add_action('admin_init', [$this, 'page_init']);
        add_action('wp_head', [$this, 'output_custom_css']);
    }

    public function add_plugin_page()
    {
        add_submenu_page(
            'tcm-reports',             // Parent slug
            'TCM Settings',            // Page title
            'Settings',                // Menu title
            'tcm_access',              // Capability (must match parent menu)
            'tcm-settings',            // Menu slug
            [$this, 'create_admin_page'] // Callback function
        );
    }

    public function create_admin_page()
    {
        $this->options = get_option('tcm_settings');
?>
        <div class="wrap">
            <h1>TimeClock Manager Settings</h1>
            <p>Customize the appearance of the clock in/out form on the frontend.</p>

            <form method="post" action="options.php">
                <?php
                settings_fields('tcm_settings_group');
                do_settings_sections('tcm-settings-admin');
                submit_button();
                ?>
            </form>

            <div class="tcm-preview-section" style="margin-top: 30px; padding: 20px; background: #f9f9f9; border: 1px solid #e1e1e1; border-radius: 8px;">
                <h2>Live Preview</h2>
                <p>This is how your clock form will look with the current settings:</p>

                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">

                <div class="tcm-clock-form" style="max-width: 400px; margin: 20px 0; background: white; padding: 30px; border: 1px solid #e1e1e1; border-radius: 8px; text-align: center;">
                    <p>Hello, <strong>Preview User</strong></p>
                    <button class="tcm-button tcm-preview-button" style="margin: 8px;">Clock In</button>
                    <button class="tcm-button tcm-preview-button" style="margin: 8px;">Clock Out</button>
                    <p style="color: #00a32a; padding: 10px;">✅ You are currently clocked in.</p>
                    <div class="tcm-preview-timer" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 2px solid #2271b1; border-radius: 8px; padding: 15px; margin: 20px 0; font-family: 'Montserrat', sans-serif; font-weight: 700;">
                        <div style="font-size: 24px; margin-bottom: 8px; letter-spacing: 1px; font-family: 'Montserrat', sans-serif; font-weight: 700;">⏱️ 01:23:45</div>
                        <div style="font-size: 14px; font-weight: 600; font-family: 'Montserrat', sans-serif;">Total: 1.40 hours</div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .tcm-preview-section {
                background: #f9f9f9;
                border: 1px solid #e1e1e1;
                border-radius: 8px;
                padding: 20px;
                margin-top: 30px;
            }

            .tcm-preview-button {
                background-color: <?php echo esc_attr($this->get_option('button_bg_color', '#2271b1')); ?> !important;
                color: <?php echo esc_attr($this->get_option('button_text_color', '#ffffff')); ?> !important;
                padding: <?php echo esc_attr($this->get_option('button_padding', '12px 24px')); ?> !important;
                border: none;
                border-radius: 6px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                min-width: 120px;
                display: inline-block;
                text-decoration: none;
            }

            .tcm-preview-timer {
                color: <?php echo esc_attr($this->get_option('timer_text_color', '#2271b1')); ?> !important;
            }

            .tcm-preview-timer div:first-child {
                color: <?php echo esc_attr($this->get_option('timer_text_color', '#2271b1')); ?> !important;
            }
        </style>

        <script>
            jQuery(document).ready(function($) {
                // Update preview when settings change
                function updatePreview() {
                    var buttonBg = $('#button_bg_color').val();
                    var buttonText = $('#button_text_color').val();
                    var buttonPadding = $('#button_padding').val();
                    var timerColor = $('#timer_text_color').val();

                    $('.tcm-preview-button').css({
                        'background-color': buttonBg + ' !important',
                        'color': buttonText + ' !important',
                        'padding': buttonPadding + ' !important'
                    });

                    $('.tcm-preview-timer, .tcm-preview-timer div:first-child').css({
                        'color': timerColor + ' !important'
                    });
                }

                $('#button_bg_color, #button_text_color, #button_padding, #timer_text_color').on('input change', updatePreview);
            });
        </script>
<?php
    }

    public function page_init()
    {
        register_setting(
            'tcm_settings_group', // Option group
            'tcm_settings', // Option name
            [$this, 'sanitize'] // Sanitize
        );

        add_settings_section(
            'tcm_button_settings', // ID
            'Button Settings', // Title
            [$this, 'button_info'], // Callback
            'tcm-settings-admin' // Page
        );

        add_settings_field(
            'button_bg_color', // ID
            'Button Background Color', // Title
            [$this, 'button_bg_color_callback'], // Callback
            'tcm-settings-admin', // Page
            'tcm_button_settings' // Section
        );

        add_settings_field(
            'button_text_color', // ID
            'Button Text Color', // Title
            [$this, 'button_text_color_callback'], // Callback
            'tcm-settings-admin', // Page
            'tcm_button_settings' // Section
        );

        add_settings_field(
            'button_padding', // ID
            'Button Padding', // Title
            [$this, 'button_padding_callback'], // Callback
            'tcm-settings-admin', // Page
            'tcm_button_settings' // Section
        );

        add_settings_section(
            'tcm_timer_settings', // ID
            'Timer Settings', // Title
            [$this, 'timer_info'], // Callback
            'tcm-settings-admin' // Page
        );

        add_settings_field(
            'timer_text_color', // ID
            'Timer Text Color', // Title
            [$this, 'timer_text_color_callback'], // Callback
            'tcm-settings-admin', // Page
            'tcm_timer_settings' // Section
        );

        add_settings_section(
            'tcm_notification_settings', // ID
            'Notification Settings', // Title
            [$this, 'notification_info'], // Callback
            'tcm-settings-admin' // Page
        );

        add_settings_field(
            'time_request_email', // ID
            'Time Request Notification Email', // Title
            [$this, 'time_request_email_callback'], // Callback
            'tcm-settings-admin', // Page
            'tcm_notification_settings' // Section
        );
    }

    public function sanitize($input)
    {
        $new_input = array();

        if (isset($input['button_bg_color'])) {
            $new_input['button_bg_color'] = sanitize_hex_color($input['button_bg_color']);
        }

        if (isset($input['button_text_color'])) {
            $new_input['button_text_color'] = sanitize_hex_color($input['button_text_color']);
        }

        if (isset($input['button_padding'])) {
            // Allow CSS padding values like "12px 24px", "10px", etc.
            $new_input['button_padding'] = sanitize_text_field($input['button_padding']);
        }

        if (isset($input['timer_text_color'])) {
            $new_input['timer_text_color'] = sanitize_hex_color($input['timer_text_color']);
        }

        if (isset($input['time_request_email'])) {
            $new_input['time_request_email'] = sanitize_email($input['time_request_email']);
        }

        return $new_input;
    }

    public function button_info()
    {
        print 'Customize the appearance of the Clock In and Clock Out buttons:';
    }

    public function timer_info()
    {
        print 'Customize the appearance of the timer display:';
    }

    public function notification_info()
    {
        print 'Configure email notifications for time change requests:';
    }

    public function button_bg_color_callback()
    {
        printf(
            '<input type="color" id="button_bg_color" name="tcm_settings[button_bg_color]" value="%s" />
                <p class="description">Choose the background color for the clock buttons. Default: #2271b1</p>',
            isset($this->options['button_bg_color']) ? esc_attr($this->options['button_bg_color']) : '#2271b1'
        );
    }

    public function button_text_color_callback()
    {
        printf(
            '<input type="color" id="button_text_color" name="tcm_settings[button_text_color]" value="%s" />
                <p class="description">Choose the text color for the clock buttons. Default: #ffffff</p>',
            isset($this->options['button_text_color']) ? esc_attr($this->options['button_text_color']) : '#ffffff'
        );
    }

    public function button_padding_callback()
    {
        printf(
            '<input type="text" id="button_padding" name="tcm_settings[button_padding]" value="%s" placeholder="12px 24px" />
                <p class="description">Button padding in CSS format (e.g., "12px 24px", "15px", "10px 20px 10px 20px"). Default: 12px 24px</p>',
            isset($this->options['button_padding']) ? esc_attr($this->options['button_padding']) : '12px 24px'
        );
    }

    public function timer_text_color_callback()
    {
        printf(
            '<input type="color" id="timer_text_color" name="tcm_settings[timer_text_color]" value="%s" />
                <p class="description">Choose the text color for the timer display. Default: #2271b1</p>',
            isset($this->options['timer_text_color']) ? esc_attr($this->options['timer_text_color']) : '#2271b1'
        );
    }

    public function time_request_email_callback()
    {
        printf(
            '<input type="email" id="time_request_email" name="tcm_settings[time_request_email]" value="%s" class="regular-text" />
                <p class="description">Email address where time change requests will be sent. Default: info@protechsteel.com</p>',
            isset($this->options['time_request_email']) ? esc_attr($this->options['time_request_email']) : 'info@protechsteel.com'
        );
    }

    private function get_option($key, $default = '')
    {
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }
        return $default;
    }

    public function output_custom_css()
    {
        $options = get_option('tcm_settings');

        if (empty($options)) {
            return;
        }

        $button_bg = isset($options['button_bg_color']) ? $options['button_bg_color'] : '#2271b1';
        $button_text = isset($options['button_text_color']) ? $options['button_text_color'] : '#ffffff';
        $button_padding = isset($options['button_padding']) ? $options['button_padding'] : '12px 24px';
        $timer_color = isset($options['timer_text_color']) ? $options['timer_text_color'] : '#2271b1';

        echo '<style type="text/css" id="tcm-custom-styles">';
        echo '.tcm-button {';
        echo 'background-color: ' . esc_attr($button_bg) . ' !important;';
        echo 'color: ' . esc_attr($button_text) . ' !important;';
        echo 'padding: ' . esc_attr($button_padding) . ' !important;';
        echo '}';

        echo '.tcm-button:hover {';
        echo 'background-color: ' . esc_attr($this->darken_color($button_bg, 0.1)) . ' !important;';
        echo '}';

        echo '.tcm-update-btn {';
        echo 'background-color: ' . esc_attr($button_bg) . ' !important;';
        echo 'color: ' . esc_attr($button_text) . ' !important;';
        echo 'padding: ' . esc_attr($button_padding) . ' !important;';
        echo 'border: none !important;';
        echo '}';

        echo '.tcm-update-btn:hover {';
        echo 'background-color: ' . esc_attr($this->darken_color($button_bg, 0.1)) . ' !important;';
        echo '}';

        echo '#tcm-timer .timer-main {';
        echo 'color: ' . esc_attr($timer_color) . ' !important;';
        echo '}';

        echo '#tcm-timer {';
        echo 'border-color: ' . esc_attr($timer_color) . ' !important;';
        echo '}';
        echo '</style>';
    }

    private function darken_color($hex, $percent)
    {
        // Remove # if present
        $hex = str_replace('#', '', $hex);

        // Convert hex to RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Darken by percentage
        $r = max(0, min(255, $r * (1 - $percent)));
        $g = max(0, min(255, $g * (1 - $percent)));
        $b = max(0, min(255, $b * (1 - $percent)));

        // Convert back to hex
        return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) .
            str_pad(dechex($g), 2, '0', STR_PAD_LEFT) .
            str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    }
}

new TCM_Admin_Settings();
?>