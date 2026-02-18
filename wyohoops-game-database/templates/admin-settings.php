<?php
/**
 * Admin Settings Page Template
 *
 * @package WyoHoops_GameDB
 */

if (!defined('ABSPATH')) exit;
?>

<div class="wrap wyohoops-admin">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php settings_errors('wyohoops_messages'); ?>
    
    <form method="post" action="">
        <?php wp_nonce_field('wyohoops_save_settings', 'wyohoops_save_settings_nonce'); ?>
        
        <h2><?php _e('Branding', 'wyohoops-gamedb'); ?></h2>
        <p class="description"><?php _e('Customize the appearance of your WyoHoops database.', 'wyohoops-gamedb'); ?></p>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('WyoHoops Logo', 'wyohoops-gamedb'); ?></th>
                <td>
                    <?php 
                    $logo_id = get_option('wyohoops_logo_attachment_id', 0);
                    $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'medium') : '';
                    ?>
                    <input type="hidden" name="logo_attachment_id" id="logo_attachment_id" value="<?php echo esc_attr($logo_id); ?>">
                    <button type="button" class="button" id="upload_logo_button">
                        <?php echo $logo_url ? 'Change Logo' : 'Upload Logo'; ?>
                    </button>
                    <button type="button" class="button" id="remove_logo_button" style="<?php echo $logo_url ? '' : 'display:none;'; ?>">Remove Logo</button>
                    <div id="logo_preview" style="margin-top: 10px;">
                        <?php if ($logo_url): ?>
                            <img src="<?php echo esc_url($logo_url); ?>" style="max-width: 300px; height: auto;">
                        <?php endif; ?>
                    </div>
                    <p class="description"><?php _e('This logo will appear at the top of the front-end database display.', 'wyohoops-gamedb'); ?></p>
                </td>
            </tr>
        </table>
        
        <h2><?php _e('Efficiency Score Baselines', 'wyohoops-gamedb'); ?></h2>
        <p class="description"><?php _e('Configure how offensive and defensive efficiency scores are calculated.', 'wyohoops-gamedb'); ?></p>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Offensive Efficiency Baseline', 'wyohoops-gamedb'); ?></th>
                <td>
                    <label for="off_eff_baseline_points">
                        <input type="number" name="off_eff_baseline_points" id="off_eff_baseline_points" class="small-text" value="<?php echo esc_attr(get_option('wyohoops_off_eff_baseline_points', 80)); ?>" min="1">
                        <?php _e('points per game =', 'wyohoops-gamedb'); ?>
                    </label>
                    <label for="off_eff_baseline_score">
                        <input type="number" name="off_eff_baseline_score" id="off_eff_baseline_score" class="small-text" value="<?php echo esc_attr(get_option('wyohoops_off_eff_baseline_score', 98)); ?>" min="1" max="100">
                        <?php _e('efficiency score', 'wyohoops-gamedb'); ?>
                    </label>
                    <p class="description"><?php _e('Example: 80 points per game = 98 efficiency score', 'wyohoops-gamedb'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Defensive Efficiency Baseline', 'wyohoops-gamedb'); ?></th>
                <td>
                    <label for="def_eff_baseline_points">
                        <?php _e('Holding opponent to', 'wyohoops-gamedb'); ?>
                        <input type="number" name="def_eff_baseline_points" id="def_eff_baseline_points" class="small-text" value="<?php echo esc_attr(get_option('wyohoops_def_eff_baseline_points', 40)); ?>" min="1">
                        <?php _e('points =', 'wyohoops-gamedb'); ?>
                    </label>
                    <label for="def_eff_baseline_score">
                        <input type="number" name="def_eff_baseline_score" id="def_eff_baseline_score" class="small-text" value="<?php echo esc_attr(get_option('wyohoops_def_eff_baseline_score', 96)); ?>" min="1" max="100">
                        <?php _e('efficiency score', 'wyohoops-gamedb'); ?>
                    </label>
                    <p class="description"><?php _e('Example: Holding opponent to 40 points = 96 efficiency score', 'wyohoops-gamedb'); ?></p>
                </td>
            </tr>
        </table>
        
        <h2><?php _e('Default Display Settings', 'wyohoops-gamedb'); ?></h2>
        
        <table class="form-table">
            <tr>
                <th scope="row"><label for="default_gender"><?php _e('Default Gender View', 'wyohoops-gamedb'); ?></label></th>
                <td>
                    <select name="default_gender" id="default_gender">
                        <option value="B" <?php selected(get_option('wyohoops_default_gender', 'B'), 'B'); ?>><?php _e('Boys', 'wyohoops-gamedb'); ?></option>
                        <option value="G" <?php selected(get_option('wyohoops_default_gender', 'B'), 'G'); ?>><?php _e('Girls', 'wyohoops-gamedb'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="count_levels"><?php _e('Count Levels', 'wyohoops-gamedb'); ?></label></th>
                <td>
                    <select name="count_levels" id="count_levels">
                        <option value="Varsity" <?php selected(get_option('wyohoops_count_levels', 'Varsity'), 'Varsity'); ?>><?php _e('Varsity Only', 'wyohoops-gamedb'); ?></option>
                        <option value="All" <?php selected(get_option('wyohoops_count_levels', 'Varsity'), 'All'); ?>><?php _e('All Levels', 'wyohoops-gamedb'); ?></option>
                    </select>
                    <p class="description"><?php _e('Which game levels to include in statistics calculations', 'wyohoops-gamedb'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="ui_view_mode"><?php _e('UI View Mode', 'wyohoops-gamedb'); ?></label></th>
                <td>
                    <select name="ui_view_mode" id="ui_view_mode">
                        <option value="table" <?php selected(get_option('wyohoops_ui_view_mode', 'table'), 'table'); ?>><?php _e('Table View', 'wyohoops-gamedb'); ?></option>
                        <option value="cards" <?php selected(get_option('wyohoops_ui_view_mode', 'table'), 'cards'); ?>><?php _e('Card View', 'wyohoops-gamedb'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="show_meters"><?php _e('Show Efficiency Meters', 'wyohoops-gamedb'); ?></label></th>
                <td>
                    <input type="checkbox" name="show_meters" id="show_meters" value="1" <?php checked(get_option('wyohoops_show_meters', 1), 1); ?>>
                    <p class="description"><?php _e('Display visual meter bars for efficiency scores', 'wyohoops-gamedb'); ?></p>
                </td>
            </tr>
        </table>
        
        <h2><?php _e('Performance', 'wyohoops-gamedb'); ?></h2>
        
        <table class="form-table">
            <tr>
                <th scope="row"><label for="enable_caching"><?php _e('Enable Caching', 'wyohoops-gamedb'); ?></label></th>
                <td>
                    <input type="checkbox" name="enable_caching" id="enable_caching" value="1" <?php checked(get_option('wyohoops_enable_caching', 1), 1); ?>>
                    <p class="description"><?php _e('Cache team statistics for better performance. Clear cache from Import/Tools page after making changes.', 'wyohoops-gamedb'); ?></p>
                </td>
            </tr>
        </table>
        
        <?php submit_button(__('Save Settings', 'wyohoops-gamedb')); ?>
    </form>
</div>
