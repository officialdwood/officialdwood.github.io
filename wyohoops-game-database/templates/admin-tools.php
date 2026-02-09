<?php
/**
 * Admin Import/Tools Page Template
 *
 * @package WyoHoops_GameDB
 */

if (!defined('ABSPATH')) exit;
?>

<div class="wrap wyohoops-admin">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php settings_errors('wyohoops_messages'); ?>
    
    <div class="wyohoops-tools-section">
        <h2><?php _e('Import Default Data', 'wyohoops-gamedb'); ?></h2>
        <p class="description"><?php _e('Import default Wyoming high school teams and sample game data.', 'wyohoops-gamedb'); ?></p>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Import Wyoming Teams', 'wyohoops-gamedb'); ?></th>
                <td>
                    <button type="button" id="wyohoops-import-teams" class="button button-primary">
                        <?php _e('Import Default Teams', 'wyohoops-gamedb'); ?>
                    </button>
                    <p class="description">
                        <?php _e('Imports all Wyoming high school teams (4A, 3A, 2A, 1A) with default settings.', 'wyohoops-gamedb'); ?>
                    </p>
                    <div id="import-teams-result" class="wyohoops-result-message"></div>
                </td>
            </tr>
        </table>
        
        <h2><?php _e('Maintenance Tools', 'wyohoops-gamedb'); ?></h2>
        <p class="description"><?php _e('Tools for maintaining and optimizing the plugin.', 'wyohoops-gamedb'); ?></p>
        
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Recalculate Statistics', 'wyohoops-gamedb'); ?></th>
                <td>
                    <button type="button" id="wyohoops-recalculate-stats" class="button">
                        <?php _e('Recalculate All Stats', 'wyohoops-gamedb'); ?>
                    </button>
                    <p class="description">
                        <?php _e('Clears the statistics cache and forces recalculation on next view.', 'wyohoops-gamedb'); ?>
                    </p>
                    <div id="recalculate-result" class="wyohoops-result-message"></div>
                </td>
            </tr>
        </table>
        
        <h2><?php _e('Database Information', 'wyohoops-gamedb'); ?></h2>
        
        <?php
        global $wpdb;
        $teams_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wyohoops_teams");
        $games_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wyohoops_games");
        $completed_games = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wyohoops_games WHERE home_score IS NOT NULL AND away_score IS NOT NULL");
        ?>
        
        <table class="widefat striped">
            <tbody>
                <tr>
                    <td><strong><?php _e('Total Teams', 'wyohoops-gamedb'); ?></strong></td>
                    <td><?php echo esc_html($teams_count); ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('Total Games', 'wyohoops-gamedb'); ?></strong></td>
                    <td><?php echo esc_html($games_count); ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('Completed Games', 'wyohoops-gamedb'); ?></strong></td>
                    <td><?php echo esc_html($completed_games); ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('Upcoming Games', 'wyohoops-gamedb'); ?></strong></td>
                    <td><?php echo esc_html($games_count - $completed_games); ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('Plugin Version', 'wyohoops-gamedb'); ?></strong></td>
                    <td><?php echo esc_html(WYOHOOPS_VERSION); ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('Database Version', 'wyohoops-gamedb'); ?></strong></td>
                    <td><?php echo esc_html(get_option('wyohoops_db_version', '1.0.0')); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Import teams
    $('#wyohoops-import-teams').on('click', function() {
        var $btn = $(this);
        var $result = $('#import-teams-result');
        
        $btn.prop('disabled', true).text('<?php _e('Importing...', 'wyohoops-gamedb'); ?>');
        $result.html('');
        
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'wyohoops_import_default_teams',
                nonce: wyohoopsAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $result.html('<p class="success"><?php _e('Successfully imported', 'wyohoops-gamedb'); ?> ' + response.data.imported + ' <?php _e('teams!', 'wyohoops-gamedb'); ?></p>');
                    setTimeout(function() { location.reload(); }, 2000);
                } else {
                    $result.html('<p class="error">' + response.data + '</p>');
                }
            },
            error: function() {
                $result.html('<p class="error"><?php _e('An error occurred.', 'wyohoops-gamedb'); ?></p>');
            },
            complete: function() {
                $btn.prop('disabled', false).text('<?php _e('Import Default Teams', 'wyohoops-gamedb'); ?>');
            }
        });
    });
    
    // Recalculate stats
    $('#wyohoops-recalculate-stats').on('click', function() {
        var $btn = $(this);
        var $result = $('#recalculate-result');
        
        $btn.prop('disabled', true).text('<?php _e('Recalculating...', 'wyohoops-gamedb'); ?>');
        $result.html('');
        
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'wyohoops_recalculate_stats',
                nonce: wyohoopsAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $result.html('<p class="success"><?php _e('Statistics cache cleared successfully!', 'wyohoops-gamedb'); ?></p>');
                } else {
                    $result.html('<p class="error">' + response.data + '</p>');
                }
            },
            error: function() {
                $result.html('<p class="error"><?php _e('An error occurred.', 'wyohoops-gamedb'); ?></p>');
            },
            complete: function() {
                $btn.prop('disabled', false).text('<?php _e('Recalculate All Stats', 'wyohoops-gamedb'); ?>');
            }
        });
    });
});
</script>
