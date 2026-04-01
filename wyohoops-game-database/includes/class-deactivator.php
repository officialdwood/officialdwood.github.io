<?php
/**
 * Fired during plugin deactivation.
 *
 * @package WyoHoops_GameDB
 */

class WyoHoops_Deactivator {

    /**
     * Deactivate the plugin.
     * Clears any transients and flushes rewrite rules.
     */
    public static function deactivate() {
        // Clear any cached transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wyohoops_%' OR option_name LIKE '_transient_timeout_wyohoops_%'");
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
