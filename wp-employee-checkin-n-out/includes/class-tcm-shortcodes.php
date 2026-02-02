<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class TCM_Shortcodes
 *
 * Handles the clock form and pin dashboard shortcodes.
 */

if (!class_exists('TCM_Shortcodes')) {
    class TCM_Shortcodes
    {

        public function __construct()
        {
            add_shortcode('tcm_clock', [$this, 'render_clock_form']);
            add_shortcode('tcm_pin_dashboard', [$this, 'render_pin_dashboard']);

            // Override WordPress login form
            add_action('login_enqueue_scripts', [$this, 'login_enqueue_scripts']);
            add_action('login_form', [$this, 'add_pin_field_to_login']);
            add_action('login_init', [$this, 'handle_pin_login_early']);
            add_filter('authenticate', [$this, 'pin_authenticate'], 1, 3);
            add_action('login_head', [$this, 'login_head_styles']);

            add_action('eael/login-register/after-login-form', [$this ,'elementor_login_enqueue_scripts']);
            add_action('eael/login-register/after-login-form-open', [ $this,'elementor_add_pin_field_to_login']);
            add_action('eael/login-register/before-login-form', [$this, 'elementor_login_styles']);

        }

        public function render_clock_form()
        {
            if (!is_user_logged_in()) {
                return '<p style="text-align:center;margin:0 0 16px;">Log in to clock in and clock out.</p>';
            }

            ob_start();
            include TCM_PLUGIN_TEMPLATES . '/clock-form.php';
            return ob_get_clean();
        }

        public function render_pin_dashboard()
        {
            // Check if user is logged in
            if (!is_user_logged_in()) {
                return '<p style="text-align: center; padding: 20px;">Please log in to view your dashboard.</p>';
            }

            ob_start();
            ?>
            <div id="tcm-dashboard">
                <?php
                // Show the reports directly
                require_once TCM_PLUGIN_INCLUDES . '/admin/class-tcm-admin-reports.php';
                $report = new TCM_Admin_Reports();
                $report->render();
                ?>
            </div>
            <?php
            return ob_get_clean();
        }

        public function login_head_styles()
        {
        ?>
            <style>
                /* Hide default fields in PIN mode, show PIN field in standard mode */
                body.tcm-pin-mode #loginform p:not(.tcm-pin-field):not(.submit):not(.tcm-toggle),
                body.tcm-pin-mode #loginform .user-pass-wrap,
                body.tcm-pin-mode #nav,
                body.tcm-pin-mode #backtoblog,
                body.tcm-pin-mode .forgetmenot,
                body.tcm-pin-mode .login-password,
                body.tcm-pin-mode .login-username {
                    display: none !important;
                }

                /* Hide PIN field in standard mode */
                body:not(.tcm-pin-mode) .tcm-pin-field {
                    display: none !important;
                }

                /* Hide toggle in standard mode when no PIN users exist */
                body:not(.tcm-has-pin-users) .tcm-toggle {
                    display: none !important;
                }

                /* Style the PIN field to match WordPress UI */
                .tcm-pin-field {
                    margin-bottom: 24px;
                }

                .tcm-pin-field label {
                    display: block;
                    font-size: 14px;
                    font-weight: 600;
                    margin-bottom: 8px;
                    color: #1d2327;
                    line-height: 1.3;
                }

                .tcm-pin-field input[type="text"] {
                    width: 100%;
                    padding: 12px 16px;
                    font-size: 14px;
                    border: 1px solid #8c8f94;
                    border-radius: 4px;
                    background: #fff;
                    color: #1d2327;
                    box-shadow: inset 0 1px 2px rgba(0, 0, 0, .07);
                    transition: border-color 0.1s ease-in-out, box-shadow 0.1s ease-in-out;
                }

                .tcm-pin-field input[type="text"]:focus {
                    border-color: #2271b1;
                    box-shadow: 0 0 0 1px #2271b1;
                    outline: 2px solid transparent;
                }

                /* Login button styling to match WordPress */
                body.tcm-pin-mode .submit {
                    text-align: center;
                    margin-top: 24px;
                }

                body.tcm-pin-mode .submit input {
                    width: 100%;
                    height: 48px;
                    font-size: 16px;
                    font-weight: 600;
                    background: #2271b1;
                    border: 1px solid #2271b1;
                    border-radius: 4px;
                    color: #fff;
                    cursor: pointer;
                    transition: all 0.15s ease-in-out;
                    text-transform: none;
                    box-shadow: 0 1px 0 #135e96;
                }

                body.tcm-pin-mode .submit input:hover {
                    background: #135e96;
                    border-color: #135e96;
                    box-shadow: 0 1px 0 #0a4b78;
                }

                body.tcm-pin-mode .submit input:active {
                    background: #043959;
                    border-color: #043959;
                    box-shadow: inset 0 2px 0 #032f4a;
                    transform: translateY(1px);
                }

                /* Login form container adjustments */
                body.tcm-pin-mode #loginform {
                    margin-top: 20px;
                    padding: 32px 32px 24px;
                    background: #fff;
                    border: 1px solid #dcdcde;
                    border-radius: 6px;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
                }

                /* Logo spacing adjustment */
                body.tcm-pin-mode #login h1 {
                    margin-bottom: 24px;
                }

                body.tcm-pin-mode #login h1 a {
                    box-shadow: none;
                }

                /* PIN field help text */
                .tcm-pin-help {
                    font-size: 13px;
                    color: #646970;
                    margin-top: 8px;
                    margin-bottom: 0;
                    text-align: center;
                }

                /* Auto-submit indicator */
                .tcm-auto-submit {
                    display: none;
                    font-size: 12px;
                    color: #00a32a;
                    margin-top: 8px;
                    text-align: center;
                    font-weight: 500;
                }

                .tcm-auto-submit.show {
                    display: block;
                }
                /* Toggle login mode styling */
                .tcm-toggle {
                    text-align: center;
                    margin-top: 20px;
                    padding-top: 16px;
                    border-top: 1px solid #dcdcde;
                }

                .tcm-toggle a {
                    color: #646970;
                    text-decoration: none;
                    font-size: 13px;
                    font-weight: 400;
                    transition: color 0.15s ease-in-out;
                }

                .tcm-toggle a:hover {
                    color: #2271b1;
                }

                .tcm-toggle a:focus {
                    color: #2271b1;
                    box-shadow: 0 0 0 2px #2271b1;
                    outline: 2px solid transparent;
                    border-radius: 2px;
                }

                /* Standard login mode adjustments */
                body:not(.tcm-pin-mode) #loginform {
                    padding: 26px 24px;
                }

                body:not(.tcm-pin-mode) #login h1 {
                    margin-bottom: 16px;
                }

                /* Mobile responsiveness */
                @media screen and (max-width: 480px) {
                    .tcm-pin-field input[type="text"] {
                        font-size: 20px;
                        letter-spacing: 8px;
                        padding: 10px 12px;
                    }

                    body.tcm-pin-mode #loginform {
                        padding: 24px 20px;
                    }
                }
            </style>
        <?php
        }

        public function login_enqueue_scripts()
        {
        ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {

                    const body = document.body;
                    const loginForm = document.getElementById('loginform');
                    const pinField = document.getElementById('tcm_pin');
                    const autoSubmitIndicator = document.querySelector('.tcm-auto-submit');
                    const userLoginField = document.getElementById('user_login');

                    // Select all inputs with the "required" attribute and remove it
                    document.querySelectorAll('[required]').forEach(function(element) {
                        element.removeAttribute('required');
                    });

                    // Check if we should show PIN mode (if PIN field exists and has users with PINs)
                    if (pinField && pinField.dataset.hasUsers === '1') {
                        // Mark that we have PIN users for CSS
                        body.classList.add('tcm-has-pin-users');

                        // Start in PIN mode by default
                        body.classList.add('tcm-pin-mode');

                        // Focus PIN field after a small delay to ensure proper loading
                        setTimeout(() => {
                            if (body.classList.contains('tcm-pin-mode')) {
                                pinField.focus();
                            }
                        }, 100);

                        // PIN input restrictions
                        pinField.addEventListener('input', function(e) {
                            // Only allow digits
                            this.value = this.value.replace(/[^0-9]/g, '');
                            // Limit to 4 digits
                            if (this.value.length > 4) {
                                this.value = this.value.slice(0, 4);
                            }

                            // Hide auto-submit indicator when typing
                            if (autoSubmitIndicator) {
                                autoSubmitIndicator.classList.remove('show');
                            }
                        });

                        // Auto-submit when 4 digits entered
                        // pinField.addEventListener('input', function(e) {
                        //     if (this.value.length === 4) {
                        //         // Show auto-submit indicator
                        //         if (autoSubmitIndicator) {
                        //             autoSubmitIndicator.classList.add('show');
                        //         }

                        //         // Disable the field temporarily

                        //         setTimeout(() => {
                        //             loginForm.submit();
                        //         }, 500); // Small delay for better UX
                        //     }
                        // });

                        // Add enter key support
                        // pinField.addEventListener('keypress', function(e) {
                        //     if (e.key === 'Enter' && this.value.length === 4) {
                        //         e.preventDefault();
                        //         loginForm.submit();
                        //     }
                        // });
                    }

                    // Toggle between PIN and standard login
                    window.toggleLoginMode = function() {
                        if (body.classList.contains('tcm-pin-mode')) {
                            // Switch to standard login
                            body.classList.remove('tcm-pin-mode');

                            // Clear PIN field and re-enable it
                            if (pinField) {
                                pinField.value = '';
                            }

                            // Hide auto-submit indicator
                            if (autoSubmitIndicator) {
                                autoSubmitIndicator.classList.remove('show');
                            }

                            // Focus username field
                            setTimeout(() => {
                                if (userLoginField) {
                                    userLoginField.focus();
                                }
                            }, 100);

                            // Update toggle text
                            const toggleLink = document.querySelector('.tcm-toggle a');
                            if (toggleLink) {
                                toggleLink.textContent = 'Use PIN login instead';
                            }
                        } else {
                            // Switch to PIN login
                            body.classList.add('tcm-pin-mode');

                            // Clear standard login fields
                            if (userLoginField) {
                                userLoginField.value = '';
                            }
                            const passwordField = document.getElementById('user_pass');
                            if (passwordField) {
                                passwordField.value = '';
                            }

                            // Focus PIN field
                            setTimeout(() => {
                                if (pinField) {
                                    pinField.focus();
                                }
                            }, 100);

                            // Update toggle text
                            const toggleLink = document.querySelector('.tcm-toggle a');
                            if (toggleLink) {
                                toggleLink.textContent = 'Forgot your PIN? Use username & password instead';
                            }
                        }
                        return false;
                    };

                    document.getElementById('user_pass').removeAttribute('disabled');
                    document.getElementById('user_login').removeAttribute('disabled');
                });
            </script>
            <?php
        }

        public function add_pin_field_to_login()
        {
            global $wpdb;

            // Check if any users have PINs set
            $has_pin_users = $wpdb->get_var("
                SELECT COUNT(*) 
                FROM {$wpdb->usermeta} 
                WHERE meta_key = 'tcm_pin' 
                AND meta_value != ''
            ");

            if ($has_pin_users > 0) {
            ?>
                <p class="tcm-pin-field">
                    <label for="tcm_pin">
                        <strong>Employee PIN</strong>
                    </label>
                    <input type="text"
                        name="tcm_pin"
                        id="tcm_pin"
                        maxlength="4"
                        pattern="[0-9]{4}"
                        autocomplete="off"
                        data-has-users="1"
                        placeholder="Enter 4-digit PIN" />
                    <span class="tcm-pin-help">Enter your 4-digit employee PIN to access the system</span>
                <div class="tcm-auto-submit">✓ Auto-submitting...</div>
                </p>

                <p class="tcm-toggle">
                    <a href="#" onclick="return toggleLoginMode();">
                        Forgot your PIN? Use username &amp; password instead
                    </a>
                </p>
<?php
            }
        }

        public function pin_authenticate($user, $username, $password)
        {
            // If the PIN is provided, handle the authentication using PIN
            if (isset($_POST['tcm_pin']) && !empty($_POST['tcm_pin'])) {
                $pin = sanitize_text_field($_POST['tcm_pin']);

                // Validate PIN format (must be 4 digits)
                if (!preg_match('/^\d{4}$/', $pin)) {
                    return new WP_Error('invalid_pin_format', 'PIN must be exactly 4 digits.');
                }

                global $wpdb;

                // Query the user by their PIN
                $user_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT u.ID, u.user_login
             FROM {$wpdb->users} u
             INNER JOIN {$wpdb->usermeta} m ON u.ID = m.user_id
             WHERE m.meta_key = 'tcm_pin' AND m.meta_value = %s",
                    $pin
                ));

                // If a user is found by PIN
                if ($user_data) {
                    // Create a WP_User object for the user and return it to bypass the username/password validation
                    $user = new WP_User($user_data->ID);
                    return $user;
                } else {
                    return new WP_Error('invalid_pin', 'Invalid PIN. Please try again.');
                }
            }

            // Continue with the standard WordPress authentication for non-PIN logins
            return $user;
        }


        public function handle_pin_login_early()
        {
            // If we are already in the middle of a normal login (i.e., no PIN field), return early
            if (!isset($_POST['log']) && !isset($_POST['tcm_pin'])) {
                return;
            }

            // If PIN is provided, attempt to authenticate with it
            if (isset($_POST['tcm_pin']) && !empty($_POST['tcm_pin'])) {
                $pin = sanitize_text_field($_POST['tcm_pin']);

                // Validate that the PIN is 4 digits long
                if (preg_match('/^\d{4}$/', $pin)) {
                    global $wpdb;

                    // Query the user based on the PIN stored in the usermeta table
                    $user_data = $wpdb->get_row($wpdb->prepare(
                        "SELECT u.user_login FROM {$wpdb->users} u
                 INNER JOIN {$wpdb->usermeta} m ON u.ID = m.user_id
                 WHERE m.meta_key = 'tcm_pin' AND m.meta_value = %s",
                        $pin
                    ));

                    // If a user is found
                    if ($user_data) {
                        // Set the username and a dummy password for WordPress to bypass password validation
                        $_POST['log'] = $user_data->user_login; // The username
                        $_POST['pwd'] = 'tcm_pin_auth_bypass'; // The dummy password

                        // Also set the standard field names required by WordPress login form
                        $_POST['user_login'] = $user_data->user_login;
                        $_POST['user_pass'] = 'tcm_pin_auth_bypass';
                    }
                }
            }
        }

        function elementor_login_enqueue_scripts( $widget ){
            ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {

                    const body = document.body;
                    const loginForm = document.getElementById('eael-login-form');
                    const pinField = document.getElementById('tcm_pin');
                    const autoSubmitIndicator = document.querySelector('.tcm-auto-submit');
                    const userLoginField = document.getElementById('eael-user-login');

                    // Select all inputs with the "required" attribute and remove it
                    document.querySelectorAll('[required]').forEach(function(element) {
                        element.removeAttribute('required');
                    });
                 
                    // Check if we should show PIN mode (if PIN field exists and has users with PINs)
                    if (pinField && pinField.dataset.hasUsers === '1') {
                        // Mark that we have PIN users for CSS
                        body.classList.add('tcm-has-pin-users');

                        // Start in PIN mode by default
                        body.classList.add('tcm-pin-mode');

                        // Focus PIN field after a small delay to ensure proper loading
                        setTimeout(() => {
                            if (body.classList.contains('tcm-pin-mode')) {
                                pinField.focus();
                            }
                        }, 100);

                        // PIN input restrictions
                        pinField.addEventListener('input', function(e) {
                            // Only allow digits
                            this.value = this.value.replace(/[^0-9]/g, '');
                            // Limit to 4 digits
                            if (this.value.length > 4) {
                                this.value = this.value.slice(0, 4);
                            }

                            // Hide auto-submit indicator when typing
                            if (autoSubmitIndicator) {
                                autoSubmitIndicator.classList.remove('show');
                            }
                        });

                       
                    }

                    // Toggle between PIN and standard login
                    window.toggleLoginMode = function() {
                        if (body.classList.contains('tcm-pin-mode')) {
                            // Switch to standard login
                            body.classList.remove('tcm-pin-mode');

                            // Clear PIN field and re-enable it
                            if (pinField) {
                                pinField.value = '';
                            }

                            // Hide auto-submit indicator
                            if (autoSubmitIndicator) {
                                autoSubmitIndicator.classList.remove('show');
                            }

                            // Focus username field
                            setTimeout(() => {
                                if (userLoginField) {
                                    userLoginField.focus();
                                }
                            }, 100);

                            // Update toggle text
                            const toggleLink = document.querySelector('.tcm-toggle a');
                            if (toggleLink) {
                                toggleLink.textContent = 'Use PIN login instead';
                            }
                        } else {
                            // Switch to PIN login
                            body.classList.add('tcm-pin-mode');

                            // Clear standard login fields
                            if (userLoginField) {
                                userLoginField.value = '';
                            }
                            const passwordField = document.getElementById('eael-user-password');
                            if (passwordField) {
                                passwordField.value = '';
                            }

                            // Focus PIN field
                            setTimeout(() => {
                                if (pinField) {
                                    pinField.focus();
                                }
                            }, 100);

                            // Update toggle text
                            const toggleLink = document.querySelector('.tcm-toggle a');
                            if (toggleLink) {
                                toggleLink.textContent = 'Forgot your PIN? Use username & password instead';
                            }
                        }
                        return false;
                    };

                    document.getElementById('eael-user-password').removeAttribute('disabled');
                    document.getElementById('eael-user-login').removeAttribute('disabled');
                });
            </script>
            <?php
        }

          public function elementor_add_pin_field_to_login()
        {
            global $wpdb;

            // Check if any users have PINs set
            $has_pin_users = $wpdb->get_var("
                SELECT COUNT(*) 
                FROM {$wpdb->usermeta} 
                WHERE meta_key = 'tcm_pin' 
                AND meta_value != ''
            ");
            
            if ($has_pin_users > 0) {
            ?>
                <p class="tcm-pin-field">
                    <label for="tcm_pin">
                       Employee PIN
                    </label>
                    <input type="text"
                        name="tcm_pin"
                        id="tcm_pin"
                        maxlength="4"
                        pattern="[0-9]{4}"
                        autocomplete="off"
                        data-has-users="1"
                        placeholder="Enter 4-digit PIN" />
                    <span class="tcm-pin-help">Enter your 4-digit employee PIN to access the system</span>
                <div class="tcm-auto-submit">✓ Auto-submitting...</div>
                </p>

                <p class="tcm-toggle">
                    <a href="#" onclick="return toggleLoginMode();">
                        Forgot your PIN? Use username &amp; password instead
                    </a>
                </p>
<?php
            }
        }

        function elementor_login_styles(){
            ?>
            <style>
                /* Hide default fields in PIN mode, show PIN field in standard mode */
                body.tcm-pin-mode #eael-login-form p:not(.tcm-pin-field):not(.submit):not(.tcm-toggle),
                body.tcm-pin-mode #eael-login-form .user-pass-wrap,
                body.tcm-pin-mode .forget-pass,
                body.tcm-pin-mode #backtoblog,
                body.tcm-pin-mode .forget-menot,
                body.tcm-pin-mode .eael-user-password,
                body.tcm-pin-mode .eael-user-login {
                    display: none !important;
                }

                /* Hide PIN field in standard mode */
                body:not(.tcm-pin-mode) .tcm-pin-field {
                    display: none !important;
                }

                /* Hide toggle in standard mode when no PIN users exist */
                body:not(.tcm-has-pin-users) .tcm-toggle {
                    display: none !important;
                }

                /* Style the PIN field to match WordPress UI */
                .tcm-pin-field {
                    margin-bottom: 24px;
                }

                .tcm-pin-field label {
                    display: block;
                    font-size: 17px;
                    font-weight: 400;
                    margin-bottom: 8px;
                    color:rgb(153, 153, 153);
                    line-height: 29.75px;
                }

                .tcm-pin-field input[type="text"] {
                    width: 100%;
                    padding: 12px 16px;
                    font-size: 1rem;
                    border: 1px solid #cfcfe8;
                    border-radius: 3px;
                    background-color: #ffffff;
                    color: #495057;
                    box-shadow: inset 0 1px 2px rgba(0, 0, 0, .07);
                    transition: border-color 0.1s ease-in-out, box-shadow 0.1s ease-in-out;
                }

                .tcm-pin-field input[type="text"]:focus {
                    outline: none;
                }

                /* Login button styling to match WordPress */
                body.tcm-pin-mode .submit {
                    text-align: center;
                    margin-top: 24px;
                }

               
                /* Login form container adjustments */
                body.tcm-pin-mode #loginform {
                    margin-top: 20px;
                    padding: 32px 32px 24px;
                    background: #fff;
                    border: 1px solid #dcdcde;
                    border-radius: 6px;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
                }

                /* Logo spacing adjustment */
                body.tcm-pin-mode #eael-login-form h1 {
                    margin-bottom: 24px;
                }

                body.tcm-pin-mode #eael-login-form h1 a {
                    box-shadow: none;
                }

                /* PIN field help text */
                .tcm-pin-help {
                    font-size: 13px;
                    color: #646970;
                    margin-top: 8px;
                    margin-bottom: 0;
                    text-align: center;
                }

                /* Auto-submit indicator */
                .tcm-auto-submit {
                    display: none;
                    font-size: 12px;
                    color: #00a32a;
                    margin-top: 8px;
                    text-align: center;
                    font-weight: 500;
                }

                .tcm-auto-submit.show {
                    display: block;
                }
                /* Toggle login mode styling */
                .tcm-toggle {
                    text-align: center;
                    margin-top: 20px;
                    padding-top: 16px;
                    border-top: 1px solid #dcdcde;
                }

                .tcm-toggle a {
                    color: #646970;
                    text-decoration: none;
                    font-size: 13px;
                    font-weight: 400;
                    transition: color 0.15s ease-in-out;
                }

                .tcm-toggle a:hover {
                    color: #2271b1;
                }

                .tcm-toggle a:focus {
                    color: #2271b1;
                    box-shadow: 0 0 0 2px #2271b1;
                    outline: 2px solid transparent;
                    border-radius: 2px;
                }

                /* Standard login mode adjustments */
                body:not(.tcm-pin-mode) #eael-login-form {
                    
                }

                body:not(.tcm-pin-mode) #eael-login-form h1 {
                  
                }

                /* Mobile responsiveness */
                @media screen and (max-width: 480px) {
                    .tcm-pin-field input[type="text"] {
                        font-size: 20px;
                        letter-spacing: 8px;
                        padding: 10px 12px;
                    }

                    body.tcm-pin-mode #eael-login-form {
                        padding: 24px 20px;
                    }
                }
            </style>
        <?php
        }
    }
    new TCM_Shortcodes();
}
