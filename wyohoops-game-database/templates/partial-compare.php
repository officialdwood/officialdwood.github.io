<?php
/**
 * Compare Tab Partial
 *
 * @package WyoHoops_GameDB
 */

if (!defined('ABSPATH')) exit;
?>

<div class="wyohoops-compare-container">
    <!-- Team Selection -->
    <div class="wyohoops-compare-selection">
        <div class="wyohoops-compare-team-select">
            <h3><?php _e('Team A', 'wyohoops-gamedb'); ?></h3>
            <select id="wyohoops-compare-team-a" class="wyohoops-select">
                <option value=""><?php _e('Select Team...', 'wyohoops-gamedb'); ?></option>
            </select>
        </div>
        
        <div class="wyohoops-compare-vs">
            <span>VS</span>
        </div>
        
        <div class="wyohoops-compare-team-select">
            <h3><?php _e('Team B', 'wyohoops-gamedb'); ?></h3>
            <select id="wyohoops-compare-team-b" class="wyohoops-select">
                <option value=""><?php _e('Select Team...', 'wyohoops-gamedb'); ?></option>
            </select>
        </div>
    </div>
    
    <div class="wyohoops-compare-filters">
        <div class="wyohoops-filter-group">
            <label for="wyohoops-compare-gender"><?php _e('Gender:', 'wyohoops-gamedb'); ?></label>
            <select id="wyohoops-compare-gender" class="wyohoops-select">
                <option value="B"><?php _e('Boys', 'wyohoops-gamedb'); ?></option>
                <option value="G"><?php _e('Girls', 'wyohoops-gamedb'); ?></option>
            </select>
        </div>
        
        <div class="wyohoops-filter-group">
            <button id="wyohoops-compare-btn" class="wyohoops-button"><?php _e('Compare Teams', 'wyohoops-gamedb'); ?></button>
        </div>
    </div>
    
    <!-- Comparison Results -->
    <div id="wyohoops-compare-results" class="wyohoops-compare-results" style="display: none;">
        <!-- Comparison data will be loaded here -->
    </div>
</div>
