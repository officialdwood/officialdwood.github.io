<?php
/**
 * Fired during plugin activation.
 *
 * @package WyoHoops_GameDB
 */

class WyoHoops_Activator {

    /**
     * Activate the plugin.
     * Creates database tables and sets default options.
     */
    public static function activate() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table names
        $teams_table = $wpdb->prefix . 'wyohoops_teams';
        $games_table = $wpdb->prefix . 'wyohoops_games';
        
        // Teams table SQL
        $sql_teams = "CREATE TABLE $teams_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            abbreviation varchar(10) NOT NULL,
            classification varchar(2) NOT NULL,
            location_city varchar(120) DEFAULT NULL,
            location_notes varchar(255) DEFAULT NULL,
            primary_color varchar(20) DEFAULT '#C8A100',
            secondary_color varchar(20) DEFAULT '#111111',
            logo_attachment_id bigint(20) DEFAULT NULL,
            school_photo_attachment_id bigint(20) DEFAULT NULL,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY classification (classification),
            KEY is_active (is_active)
        ) $charset_collate;";
        
        // Games table SQL
        $sql_games = "CREATE TABLE $games_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            game_date date NOT NULL,
            game_time time DEFAULT NULL,
            week_label varchar(60) DEFAULT NULL,
            season_label varchar(20) DEFAULT '2025-2026',
            gender char(1) NOT NULL DEFAULT 'B',
            level varchar(20) DEFAULT 'Varsity',
            home_team_id bigint(20) NOT NULL,
            away_team_id bigint(20) NOT NULL,
            home_score int(11) DEFAULT NULL,
            away_score int(11) DEFAULT NULL,
            location_text varchar(255) DEFAULT NULL,
            conference_game tinyint(1) DEFAULT 0,
            postseason_round varchar(50) DEFAULT NULL,
            notes text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY game_date (game_date),
            KEY gender (gender),
            KEY level (level),
            KEY home_team_id (home_team_id),
            KEY away_team_id (away_team_id),
            KEY season_label (season_label)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_teams);
        dbDelta($sql_games);
        
        // Set default options
        add_option('wyohoops_off_eff_baseline_points', 80);
        add_option('wyohoops_off_eff_baseline_score', 98);
        add_option('wyohoops_def_eff_baseline_points', 40);
        add_option('wyohoops_def_eff_baseline_score', 96);
        add_option('wyohoops_default_gender', 'B');
        add_option('wyohoops_count_levels', 'Varsity');
        add_option('wyohoops_enable_caching', 1);
        add_option('wyohoops_ui_view_mode', 'table');
        add_option('wyohoops_show_meters', 1);
        add_option('wyohoops_db_version', '1.0.0');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
