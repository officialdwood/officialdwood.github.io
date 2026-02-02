<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class TCM_User_Meta
 *
 * Handles user meta fields for TimeClock Manager.
 */

if (!class_exists('TCM_User_Meta')) {
    class TCM_User_Meta
    {

        public function __construct()
        {
            add_action('show_user_profile', [$this, 'render_extra_fields']);
            add_action('edit_user_profile', [$this, 'render_extra_fields']);

            add_action('personal_options_update', [$this, 'save_extra_fields']);
            add_action('edit_user_profile_update', [$this, 'save_extra_fields']);
        }

        public function render_extra_fields($user)
        {
            $pin = get_user_meta($user->ID, 'tcm_pin', true);
            $locations = get_user_meta($user->ID, 'tcm_locations', true) ?: [];
            $available_locations = ['Billings', 'Great Falls', 'Helena'];
            ?>
            <h3>TimeClock Manager Settings</h3>
            <table class="form-table">
                <tr>
                    <th><label for="tcm_pin">4-Digit PIN</label></th>
                    <td>
                        <input type="text" name="tcm_pin" id="tcm_pin" value="<?php echo esc_attr($pin); ?>" maxlength="4"
                            class="regular-text" />
                        <p class="description">Must be unique and exactly 4 digits.</p>
                    </td>
                </tr>
                <tr>
                    <th><label>Assigned Locations</label></th>
                    <td>
                        <?php foreach ($available_locations as $loc): ?>
                            <label>
                                <input type="checkbox" name="tcm_locations[]" value="<?php echo esc_attr($loc); ?>" <?php checked(in_array($loc, $locations)); ?> />
                                <?php echo esc_html($loc); ?>
                            </label><br>
                        <?php endforeach; ?>
                    </td>
                </tr>
            </table>
            <?php
        }

        public function save_extra_fields($user_id)
        {
            if (!current_user_can('edit_user', $user_id))
                return;

            $pin = sanitize_text_field($_POST['tcm_pin']);
            $locations = isset($_POST['tcm_locations']) ? array_map('sanitize_text_field', $_POST['tcm_locations']) : [];

            if (!preg_match('/^\d{4}$/', $pin))
                return;

            // Ensure unique PIN
            $users = get_users(['exclude' => [$user_id]]);
            foreach ($users as $user) {
                if (get_user_meta($user->ID, 'tcm_pin', true) === $pin)
                    return;
            }

            update_user_meta($user_id, 'tcm_pin', $pin);
            update_user_meta($user_id, 'tcm_locations', $locations);
        }
    }

    new TCM_User_Meta();
}

