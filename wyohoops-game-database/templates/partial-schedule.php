<?php
/**
 * Schedule Tab Partial
 *
 * @package WyoHoops_GameDB
 */

if (!defined('ABSPATH')) exit;
?>

<div class="wyohoops-schedule-container">
    <!-- Filters and Search -->
    <div class="wyohoops-filters">
        <div class="wyohoops-filter-group">
            <input type="text" id="wyohoops-schedule-search" class="wyohoops-input" placeholder="<?php _e('Search by team...', 'wyohoops-gamedb'); ?>">
        </div>
        
        <div class="wyohoops-filter-group">
            <label for="wyohoops-schedule-gender"><?php _e('Gender:', 'wyohoops-gamedb'); ?></label>
            <select id="wyohoops-schedule-gender" class="wyohoops-select">
                <option value=""><?php _e('All', 'wyohoops-gamedb'); ?></option>
                <option value="B"><?php _e('Boys', 'wyohoops-gamedb'); ?></option>
                <option value="G"><?php _e('Girls', 'wyohoops-gamedb'); ?></option>
            </select>
        </div>
        
        <div class="wyohoops-filter-group">
            <label for="wyohoops-schedule-class"><?php _e('Classification:', 'wyohoops-gamedb'); ?></label>
            <select id="wyohoops-schedule-class" class="wyohoops-select">
                <option value=""><?php _e('All', 'wyohoops-gamedb'); ?></option>
                <option value="4A">4A</option>
                <option value="3A">3A</option>
                <option value="2A">2A</option>
                <option value="1A">1A</option>
            </select>
        </div>
        
        <div class="wyohoops-filter-group">
            <label for="wyohoops-schedule-status"><?php _e('Status:', 'wyohoops-gamedb'); ?></label>
            <select id="wyohoops-schedule-status" class="wyohoops-select">
                <option value="all"><?php _e('All Games', 'wyohoops-gamedb'); ?></option>
                <option value="completed"><?php _e('Completed', 'wyohoops-gamedb'); ?></option>
                <option value="upcoming"><?php _e('Upcoming', 'wyohoops-gamedb'); ?></option>
            </select>
        </div>
        
        <div class="wyohoops-filter-group">
            <label>
                <input type="checkbox" id="wyohoops-schedule-conference">
                <?php _e('Conference Only', 'wyohoops-gamedb'); ?>
            </label>
        </div>
        
        <div class="wyohoops-filter-group">
            <label>
                <input type="checkbox" id="wyohoops-schedule-postseason">
                <?php _e('Postseason Only', 'wyohoops-gamedb'); ?>
            </label>
        </div>
    </div>
    
    <!-- Schedule List -->
    <div id="wyohoops-schedule-list" class="wyohoops-schedule-list">
        <!-- Games will be loaded here via JavaScript -->
    </div>
</div>
