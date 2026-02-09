<?php
/**
 * Teams Tab Partial
 *
 * @package WyoHoops_GameDB
 */

if (!defined('ABSPATH')) exit;
?>

<div class="wyohoops-teams-container">
    <!-- Filters and Search -->
    <div class="wyohoops-filters">
        <div class="wyohoops-filter-group">
            <input type="text" id="wyohoops-search" class="wyohoops-input" placeholder="<?php _e('Search teams...', 'wyohoops-gamedb'); ?>">
        </div>
        
        <div class="wyohoops-filter-group">
            <label for="wyohoops-gender-filter"><?php _e('Gender:', 'wyohoops-gamedb'); ?></label>
            <select id="wyohoops-gender-filter" class="wyohoops-select">
                <option value="B"><?php _e('Boys', 'wyohoops-gamedb'); ?></option>
                <option value="G"><?php _e('Girls', 'wyohoops-gamedb'); ?></option>
            </select>
        </div>
        
        <div class="wyohoops-filter-group">
            <label for="wyohoops-class-filter"><?php _e('Classification:', 'wyohoops-gamedb'); ?></label>
            <select id="wyohoops-class-filter" class="wyohoops-select">
                <option value=""><?php _e('All', 'wyohoops-gamedb'); ?></option>
                <option value="4A">4A</option>
                <option value="3A">3A</option>
                <option value="2A">2A</option>
                <option value="1A">1A</option>
            </select>
        </div>
        
        <div class="wyohoops-filter-group">
            <label for="wyohoops-sort"><?php _e('Sort by:', 'wyohoops-gamedb'); ?></label>
            <select id="wyohoops-sort" class="wyohoops-select">
                <option value="rank"><?php _e('Ranking', 'wyohoops-gamedb'); ?></option>
                <option value="wins"><?php _e('Wins', 'wyohoops-gamedb'); ?></option>
                <option value="losses"><?php _e('Losses', 'wyohoops-gamedb'); ?></option>
                <option value="win_pct"><?php _e('Win %', 'wyohoops-gamedb'); ?></option>
                <option value="offensive_efficiency"><?php _e('Offensive Efficiency', 'wyohoops-gamedb'); ?></option>
                <option value="defensive_efficiency"><?php _e('Defensive Efficiency', 'wyohoops-gamedb'); ?></option>
                <option value="points_for"><?php _e('Points For', 'wyohoops-gamedb'); ?></option>
                <option value="points_against"><?php _e('Points Against', 'wyohoops-gamedb'); ?></option>
                <option value="point_differential"><?php _e('Point Differential', 'wyohoops-gamedb'); ?></option>
            </select>
        </div>
    </div>
    
    <!-- Teams List -->
    <div id="wyohoops-teams-list" class="wyohoops-teams-list">
        <!-- Teams will be loaded here via JavaScript -->
    </div>
    
    <!-- Team Detail Modal -->
    <div id="wyohoops-team-modal" class="wyohoops-modal" style="display: none;">
        <div class="wyohoops-modal-content">
            <span class="wyohoops-modal-close">&times;</span>
            <div id="wyohoops-team-detail">
                <!-- Team details will be loaded here -->
            </div>
        </div>
    </div>
</div>
