<?php
/**
 * Shortcode Template
 *
 * @package WyoHoops_GameDB
 */

if (!defined('ABSPATH')) exit;

// Get logo
$logo_id = get_option('wyohoops_logo_attachment_id', 0);
$logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'large') : '';
?>

<div class="wyohoops-gamedb" data-default-tab="<?php echo esc_attr($atts['default_tab']); ?>">
    <!-- Logo Header -->
    <?php if ($logo_url): ?>
    <div class="wyohoops-logo-header">
        <img src="<?php echo esc_url($logo_url); ?>" alt="WyoHoops" class="wyohoops-logo">
    </div>
    <?php endif; ?>
    
    <!-- Navigation Tabs -->
    <div class="wyohoops-tabs">
        <button class="wyohoops-tab-button active" data-tab="teams">
            <?php _e('Teams', 'wyohoops-gamedb'); ?>
        </button>
        <button class="wyohoops-tab-button" data-tab="rankings">
            <?php _e('Rankings', 'wyohoops-gamedb'); ?>
        </button>
        <button class="wyohoops-tab-button" data-tab="players">
            <?php _e('Player Profile', 'wyohoops-gamedb'); ?>
        </button>
        <button class="wyohoops-tab-button" data-tab="stats">
            <?php _e('Stats', 'wyohoops-gamedb'); ?>
        </button>
    </div>
    
    <!-- Tab Content Container -->
    <div class="wyohoops-tab-content">
        <!-- Teams Tab -->
        <div class="wyohoops-tab-panel active" id="wyohoops-teams-tab">
            <?php include WYOHOOPS_PLUGIN_DIR . 'templates/partial-teams.php'; ?>
        </div>
        
        <!-- Rankings Tab -->
        <div class="wyohoops-tab-panel" id="wyohoops-rankings-tab">
            <?php include WYOHOOPS_PLUGIN_DIR . 'templates/partial-rankings.php'; ?>
        </div>
        
        <!-- Player Profile Tab -->
        <div class="wyohoops-tab-panel" id="wyohoops-players-tab">
            <?php include WYOHOOPS_PLUGIN_DIR . 'templates/partial-players.php'; ?>
        </div>
        
        <!-- Stats Tab -->
        <div class="wyohoops-tab-panel" id="wyohoops-stats-tab">
            <?php include WYOHOOPS_PLUGIN_DIR . 'templates/partial-stats.php'; ?>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div class="wyohoops-loading" style="display: none;">
        <div class="wyohoops-spinner"></div>
    </div>
</div>
