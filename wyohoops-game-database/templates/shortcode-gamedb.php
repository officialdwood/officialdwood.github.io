<?php
/**
 * Shortcode Template
 *
 * @package WyoHoops_GameDB
 */

if (!defined('ABSPATH')) exit;
?>

<div class="wyohoops-gamedb" data-default-tab="<?php echo esc_attr($atts['default_tab']); ?>">
    <!-- Navigation Tabs -->
    <div class="wyohoops-tabs">
        <button class="wyohoops-tab-button active" data-tab="teams">
            <?php _e('Teams', 'wyohoops-gamedb'); ?>
        </button>
        <button class="wyohoops-tab-button" data-tab="schedule">
            <?php _e('Schedule', 'wyohoops-gamedb'); ?>
        </button>
        <button class="wyohoops-tab-button" data-tab="compare">
            <?php _e('Compare', 'wyohoops-gamedb'); ?>
        </button>
    </div>
    
    <!-- Tab Content Container -->
    <div class="wyohoops-tab-content">
        <!-- Teams Tab -->
        <div class="wyohoops-tab-panel active" id="wyohoops-teams-tab">
            <?php include WYOHOOPS_PLUGIN_DIR . 'templates/partial-teams.php'; ?>
        </div>
        
        <!-- Schedule Tab -->
        <div class="wyohoops-tab-panel" id="wyohoops-schedule-tab">
            <?php include WYOHOOPS_PLUGIN_DIR . 'templates/partial-schedule.php'; ?>
        </div>
        
        <!-- Compare Tab -->
        <div class="wyohoops-tab-panel" id="wyohoops-compare-tab">
            <?php include WYOHOOPS_PLUGIN_DIR . 'templates/partial-compare.php'; ?>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div class="wyohoops-loading" style="display: none;">
        <div class="wyohoops-spinner"></div>
    </div>
</div>
