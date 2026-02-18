<?php
/**
 * Admin functionality of the plugin.
 *
 * @package WyoHoops_GameDB
 */

class WyoHoops_Admin {

    private $plugin_name;
    private $version;
    private $teams_repo;
    private $games_repo;
    private $stats_service;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->teams_repo = new WyoHoops_Repository_Teams();
        $this->games_repo = new WyoHoops_Repository_Games();
        $this->stats_service = new WyoHoops_Stats_Service();
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles() {
        if ($this->is_wyohoops_admin_page()) {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_style($this->plugin_name, WYOHOOPS_PLUGIN_URL . 'assets/css/admin.css', array(), $this->version, 'all');
        }
    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts() {
        if ($this->is_wyohoops_admin_page()) {
            wp_enqueue_media();
            wp_enqueue_script('wp-color-picker');
            wp_enqueue_script($this->plugin_name, WYOHOOPS_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'wp-color-picker'), $this->version, false);
            
            wp_localize_script($this->plugin_name, 'wyohoopsAdmin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wyohoops_admin_nonce')
            ));
        }
    }

    /**
     * Check if current page is a WyoHoops admin page.
     */
    private function is_wyohoops_admin_page() {
        $screen = get_current_screen();
        return $screen && strpos($screen->id, 'wyohoops') !== false;
    }

    /**
     * Add admin menu.
     */
    public function add_admin_menu() {
        add_menu_page(
            __('WyoHoops DB', 'wyohoops-gamedb'),
            __('WyoHoops DB', 'wyohoops-gamedb'),
            'manage_options',
            'wyohoops-gamedb',
            array($this, 'render_teams_page'),
            'dashicons-awards',
            30
        );
        
        add_submenu_page(
            'wyohoops-gamedb',
            __('Teams', 'wyohoops-gamedb'),
            __('Teams', 'wyohoops-gamedb'),
            'manage_options',
            'wyohoops-gamedb',
            array($this, 'render_teams_page')
        );
        
        add_submenu_page(
            'wyohoops-gamedb',
            __('Games', 'wyohoops-gamedb'),
            __('Games', 'wyohoops-gamedb'),
            'manage_options',
            'wyohoops-games',
            array($this, 'render_games_page')
        );
        
        add_submenu_page(
            'wyohoops-gamedb',
            __('Settings', 'wyohoops-gamedb'),
            __('Settings', 'wyohoops-gamedb'),
            'manage_options',
            'wyohoops-settings',
            array($this, 'render_settings_page')
        );
        
        add_submenu_page(
            'wyohoops-gamedb',
            __('Import/Tools', 'wyohoops-gamedb'),
            __('Import/Tools', 'wyohoops-gamedb'),
            'manage_options',
            'wyohoops-tools',
            array($this, 'render_tools_page')
        );
    }

    /**
     * Render teams admin page.
     */
    public function render_teams_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        // Handle form submission
        if (isset($_POST['wyohoops_save_team_nonce']) && wp_verify_nonce($_POST['wyohoops_save_team_nonce'], 'wyohoops_save_team')) {
            $this->handle_save_team();
        }
        
        // Get teams
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $classification = isset($_GET['classification']) ? sanitize_text_field($_GET['classification']) : '';
        
        $args = array('search' => $search);
        if ($classification) {
            $args['classification'] = $classification;
        }
        
        $teams = $this->teams_repo->get_teams($args);
        
        // Get single team for editing
        $edit_team = null;
        if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['team_id'])) {
            $edit_team = $this->teams_repo->get_team(absint($_GET['team_id']));
        }
        
        include WYOHOOPS_PLUGIN_DIR . 'templates/admin-teams.php';
    }

    /**
     * Render games admin page.
     */
    public function render_games_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        // Handle form submission
        if (isset($_POST['wyohoops_save_game_nonce']) && wp_verify_nonce($_POST['wyohoops_save_game_nonce'], 'wyohoops_save_game')) {
            $this->handle_save_game();
        }
        
        // Get games
        $args = array('limit' => 50);
        
        if (isset($_GET['gender'])) {
            $args['gender'] = sanitize_text_field($_GET['gender']);
        }
        if (isset($_GET['level'])) {
            $args['level'] = sanitize_text_field($_GET['level']);
        }
        if (isset($_GET['team_id'])) {
            $args['team_id'] = absint($_GET['team_id']);
        }
        
        $games = $this->games_repo->get_games($args);
        $teams = $this->teams_repo->get_teams(array('orderby' => 'name', 'order' => 'ASC'));
        
        // Get single game for editing
        $edit_game = null;
        if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['game_id'])) {
            $edit_game = $this->games_repo->get_game(absint($_GET['game_id']));
        }
        
        include WYOHOOPS_PLUGIN_DIR . 'templates/admin-games.php';
    }

    /**
     * Render settings admin page.
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        // Handle form submission
        if (isset($_POST['wyohoops_save_settings_nonce']) && wp_verify_nonce($_POST['wyohoops_save_settings_nonce'], 'wyohoops_save_settings')) {
            $this->handle_save_settings();
        }
        
        include WYOHOOPS_PLUGIN_DIR . 'templates/admin-settings.php';
    }

    /**
     * Render tools admin page.
     */
    public function render_tools_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        include WYOHOOPS_PLUGIN_DIR . 'templates/admin-tools.php';
    }

    /**
     * Handle save team form submission.
     */
    private function handle_save_team() {
        $team_id = $this->teams_repo->save_team($_POST);
        
        if ($team_id) {
            add_settings_error('wyohoops_messages', 'wyohoops_message', __('Team saved successfully.', 'wyohoops-gamedb'), 'success');
        } else {
            add_settings_error('wyohoops_messages', 'wyohoops_message', __('Error saving team.', 'wyohoops-gamedb'), 'error');
        }
    }

    /**
     * Handle save game form submission.
     */
    private function handle_save_game() {
        $game_id = $this->games_repo->save_game($_POST);
        
        if ($game_id) {
            add_settings_error('wyohoops_messages', 'wyohoops_message', __('Game saved successfully.', 'wyohoops-gamedb'), 'success');
        } else {
            add_settings_error('wyohoops_messages', 'wyohoops_message', __('Error saving game.', 'wyohoops-gamedb'), 'error');
        }
    }

    /**
     * Handle save settings form submission.
     */
    private function handle_save_settings() {
        update_option('wyohoops_off_eff_baseline_points', absint($_POST['off_eff_baseline_points']));
        update_option('wyohoops_off_eff_baseline_score', absint($_POST['off_eff_baseline_score']));
        update_option('wyohoops_def_eff_baseline_points', absint($_POST['def_eff_baseline_points']));
        update_option('wyohoops_def_eff_baseline_score', absint($_POST['def_eff_baseline_score']));
        update_option('wyohoops_default_gender', sanitize_text_field($_POST['default_gender']));
        update_option('wyohoops_count_levels', sanitize_text_field($_POST['count_levels']));
        update_option('wyohoops_enable_caching', isset($_POST['enable_caching']) ? 1 : 0);
        update_option('wyohoops_ui_view_mode', sanitize_text_field($_POST['ui_view_mode']));
        update_option('wyohoops_show_meters', isset($_POST['show_meters']) ? 1 : 0);
        
        add_settings_error('wyohoops_messages', 'wyohoops_message', __('Settings saved successfully.', 'wyohoops-gamedb'), 'success');
    }

    /**
     * AJAX: Save team.
     */
    public function ajax_save_team() {
        check_ajax_referer('wyohoops_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $team_id = $this->teams_repo->save_team($_POST);
        
        if ($team_id) {
            wp_send_json_success(array('team_id' => $team_id));
        } else {
            wp_send_json_error('Error saving team');
        }
    }

    /**
     * AJAX: Delete team.
     */
    public function ajax_delete_team() {
        check_ajax_referer('wyohoops_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $team_id = absint($_POST['team_id']);
        $result = $this->teams_repo->delete_team($team_id);
        
        if ($result) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Error deleting team');
        }
    }

    /**
     * AJAX: Save game.
     */
    public function ajax_save_game() {
        check_ajax_referer('wyohoops_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $game_id = $this->games_repo->save_game($_POST);
        
        if ($game_id) {
            wp_send_json_success(array('game_id' => $game_id));
        } else {
            wp_send_json_error('Error saving game');
        }
    }

    /**
     * AJAX: Delete game.
     */
    public function ajax_delete_game() {
        check_ajax_referer('wyohoops_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $game_id = absint($_POST['game_id']);
        $result = $this->games_repo->delete_game($game_id);
        
        if ($result) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Error deleting game');
        }
    }

    /**
     * AJAX: Import default Wyoming teams.
     */
    public function ajax_import_default_teams() {
        check_ajax_referer('wyohoops_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $teams_data = $this->get_default_teams_data();
        $imported = 0;
        
        foreach ($teams_data as $team) {
            $this->teams_repo->save_team($team);
            $imported++;
        }
        
        wp_send_json_success(array('imported' => $imported));
    }

    /**
     * AJAX: Recalculate stats.
     */
    public function ajax_recalculate_stats() {
        check_ajax_referer('wyohoops_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $this->stats_service->clear_all_cache();
        
        wp_send_json_success();
    }

    /**
     * Get default Wyoming teams data.
     */
    private function get_default_teams_data() {
        return array(
            // 4A Teams
            array('name' => 'Campbell County', 'abbreviation' => 'CC', 'classification' => '4A', 'location_city' => 'Gillette'),
            array('name' => 'Cheyenne Central', 'abbreviation' => 'CCHS', 'classification' => '4A', 'location_city' => 'Cheyenne'),
            array('name' => 'Cheyenne East', 'abbreviation' => 'CE', 'classification' => '4A', 'location_city' => 'Cheyenne'),
            array('name' => 'Cheyenne South', 'abbreviation' => 'CS', 'classification' => '4A', 'location_city' => 'Cheyenne'),
            array('name' => 'Evanston', 'abbreviation' => 'EHS', 'classification' => '4A', 'location_city' => 'Evanston'),
            array('name' => 'Green River', 'abbreviation' => 'GR', 'classification' => '4A', 'location_city' => 'Green River'),
            array('name' => 'Jackson Hole', 'abbreviation' => 'JH', 'classification' => '4A', 'location_city' => 'Jackson'),
            array('name' => 'Kelly Walsh', 'abbreviation' => 'KW', 'classification' => '4A', 'location_city' => 'Casper'),
            array('name' => 'Laramie', 'abbreviation' => 'LHS', 'classification' => '4A', 'location_city' => 'Laramie'),
            array('name' => 'Natrona County', 'abbreviation' => 'NC', 'classification' => '4A', 'location_city' => 'Casper'),
            array('name' => 'Riverton', 'abbreviation' => 'RHS', 'classification' => '4A', 'location_city' => 'Riverton'),
            array('name' => 'Rock Springs', 'abbreviation' => 'RS', 'classification' => '4A', 'location_city' => 'Rock Springs'),
            array('name' => 'Sheridan', 'abbreviation' => 'SHS', 'classification' => '4A', 'location_city' => 'Sheridan'),
            array('name' => 'Star Valley', 'abbreviation' => 'SV', 'classification' => '4A', 'location_city' => 'Afton'),
            array('name' => 'Thunder Basin', 'abbreviation' => 'TB', 'classification' => '4A', 'location_city' => 'Gillette'),
            
            // 3A Teams
            array('name' => 'Buffalo', 'abbreviation' => 'BHS', 'classification' => '3A', 'location_city' => 'Buffalo'),
            array('name' => 'Burns', 'abbreviation' => 'BUR', 'classification' => '3A', 'location_city' => 'Burns'),
            array('name' => 'Cody', 'abbreviation' => 'CHS', 'classification' => '3A', 'location_city' => 'Cody'),
            array('name' => 'Douglas', 'abbreviation' => 'DHS', 'classification' => '3A', 'location_city' => 'Douglas'),
            array('name' => 'Glenrock', 'abbreviation' => 'GHS', 'classification' => '3A', 'location_city' => 'Glenrock'),
            array('name' => 'Lander Valley', 'abbreviation' => 'LV', 'classification' => '3A', 'location_city' => 'Lander'),
            array('name' => 'Lovell', 'abbreviation' => 'LOV', 'classification' => '3A', 'location_city' => 'Lovell'),
            array('name' => 'Lyman', 'abbreviation' => 'LYM', 'classification' => '3A', 'location_city' => 'Lyman'),
            array('name' => 'Mountain View', 'abbreviation' => 'MV', 'classification' => '3A', 'location_city' => 'Mountain View'),
            array('name' => 'Newcastle', 'abbreviation' => 'NEW', 'classification' => '3A', 'location_city' => 'Newcastle'),
            array('name' => 'Pinedale', 'abbreviation' => 'PIN', 'classification' => '3A', 'location_city' => 'Pinedale'),
            array('name' => 'Powell', 'abbreviation' => 'POW', 'classification' => '3A', 'location_city' => 'Powell'),
            array('name' => 'Rawlins', 'abbreviation' => 'RAW', 'classification' => '3A', 'location_city' => 'Rawlins'),
            array('name' => 'Torrington', 'abbreviation' => 'TOR', 'classification' => '3A', 'location_city' => 'Torrington'),
            array('name' => 'Wheatland', 'abbreviation' => 'WHE', 'classification' => '3A', 'location_city' => 'Wheatland'),
            array('name' => 'Worland', 'abbreviation' => 'WOR', 'classification' => '3A', 'location_city' => 'Worland'),
            
            // 2A Teams
            array('name' => 'Big Horn', 'abbreviation' => 'BH', 'classification' => '2A', 'location_city' => 'Big Horn'),
            array('name' => 'Big Piney', 'abbreviation' => 'BP', 'classification' => '2A', 'location_city' => 'Big Piney'),
            array('name' => 'Greybull', 'abbreviation' => 'GB', 'classification' => '2A', 'location_city' => 'Greybull'),
            array('name' => 'Kemmerer', 'abbreviation' => 'KEM', 'classification' => '2A', 'location_city' => 'Kemmerer'),
            array('name' => 'Moorcroft', 'abbreviation' => 'MOO', 'classification' => '2A', 'location_city' => 'Moorcroft'),
            array('name' => 'Pine Bluffs', 'abbreviation' => 'PB', 'classification' => '2A', 'location_city' => 'Pine Bluffs'),
            array('name' => 'Rocky Mountain', 'abbreviation' => 'RM', 'classification' => '2A', 'location_city' => 'Cowley'),
            array('name' => 'Shoshoni', 'abbreviation' => 'SHO', 'classification' => '2A', 'location_city' => 'Shoshoni'),
            array('name' => 'Sundance', 'abbreviation' => 'SUN', 'classification' => '2A', 'location_city' => 'Sundance'),
            array('name' => 'Thermopolis', 'abbreviation' => 'THE', 'classification' => '2A', 'location_city' => 'Thermopolis'),
            array('name' => 'Tongue River', 'abbreviation' => 'TR', 'classification' => '2A', 'location_city' => 'Dayton'),
            array('name' => 'Wind River', 'abbreviation' => 'WR', 'classification' => '2A', 'location_city' => 'Pavillion'),
            array('name' => 'Wright', 'abbreviation' => 'WRI', 'classification' => '2A', 'location_city' => 'Wright'),
            array('name' => 'Wyoming Indian', 'abbreviation' => 'WI', 'classification' => '2A', 'location_city' => 'Ethete'),
            
            // 1A Teams
            array('name' => 'Arvada-Clearmont', 'abbreviation' => 'AC', 'classification' => '1A', 'location_city' => 'Arvada'),
            array('name' => 'Burlington', 'abbreviation' => 'BUR', 'classification' => '1A', 'location_city' => 'Burlington'),
            array('name' => 'Casper Christian', 'abbreviation' => 'CCS', 'classification' => '1A', 'location_city' => 'Casper'),
            array('name' => 'Cokeville', 'abbreviation' => 'COK', 'classification' => '1A', 'location_city' => 'Cokeville'),
            array('name' => 'Dubois', 'abbreviation' => 'DUB', 'classification' => '1A', 'location_city' => 'Dubois'),
            array('name' => 'Encampment', 'abbreviation' => 'ENC', 'classification' => '1A', 'location_city' => 'Encampment'),
            array('name' => 'Farson-Eden', 'abbreviation' => 'FE', 'classification' => '1A', 'location_city' => 'Farson'),
            array('name' => 'Fort Washakie', 'abbreviation' => 'FW', 'classification' => '1A', 'location_city' => 'Fort Washakie'),
            array('name' => 'Guernsey-Sunrise', 'abbreviation' => 'GS', 'classification' => '1A', 'location_city' => 'Guernsey'),
            array('name' => 'H.E.M.', 'abbreviation' => 'HEM', 'classification' => '1A', 'location_city' => 'Hanna'),
            array('name' => 'Hulett', 'abbreviation' => 'HUL', 'classification' => '1A', 'location_city' => 'Hulett'),
            array('name' => 'Kaycee', 'abbreviation' => 'KAY', 'classification' => '1A', 'location_city' => 'Kaycee'),
            array('name' => 'Lingle-Fort Laramie', 'abbreviation' => 'LFL', 'classification' => '1A', 'location_city' => 'Lingle'),
            array('name' => 'Little Snake River', 'abbreviation' => 'LSR', 'classification' => '1A', 'location_city' => 'Baggs'),
            array('name' => 'Meeteetse', 'abbreviation' => 'MEE', 'classification' => '1A', 'location_city' => 'Meeteetse'),
            array('name' => 'Midwest', 'abbreviation' => 'MID', 'classification' => '1A', 'location_city' => 'Midwest'),
            array('name' => 'Niobrara County', 'abbreviation' => 'NC', 'classification' => '1A', 'location_city' => 'Lusk'),
            array('name' => 'Riverside', 'abbreviation' => 'RIV', 'classification' => '1A', 'location_city' => 'Basin'),
            array('name' => 'Rock River', 'abbreviation' => 'RR', 'classification' => '1A', 'location_city' => 'Rock River'),
            array('name' => 'Saratoga', 'abbreviation' => 'SAR', 'classification' => '1A', 'location_city' => 'Saratoga'),
            array('name' => 'Southeast', 'abbreviation' => 'SE', 'classification' => '1A', 'location_city' => 'Yoder'),
            array('name' => 'St. Stephens', 'abbreviation' => 'SS', 'classification' => '1A', 'location_city' => 'St. Stephens'),
            array('name' => 'Ten Sleep', 'abbreviation' => 'TS', 'classification' => '1A', 'location_city' => 'Ten Sleep'),
            array('name' => 'Upton', 'abbreviation' => 'UPT', 'classification' => '1A', 'location_city' => 'Upton'),
        );
    }

    /**
     * AJAX: Import sample game records.
     */
    public function ajax_import_sample_games() {
        check_ajax_referer('wyohoops_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $games_data = $this->get_sample_games_data();
        $imported = 0;
        $errors = array();
        
        foreach ($games_data as $game) {
            $game_id = $this->games_repo->save_game($game);
            if ($game_id) {
                $imported++;
            } else {
                $errors[] = "Failed to import game: {$game['home_team_name']} vs {$game['away_team_name']}";
            }
        }
        
        if ($imported > 0) {
            wp_send_json_success(array(
                'imported' => $imported,
                'errors' => $errors
            ));
        } else {
            wp_send_json_error('No games were imported. ' . implode(', ', $errors));
        }
    }

    /**
     * Get sample game records data.
     * This represents typical Wyoming high school basketball game records.
     */
    private function get_sample_games_data() {
        // First, get team IDs by name
        $teams = $this->teams_repo->get_teams(array('is_active' => 1));
        $team_map = array();
        foreach ($teams as $team) {
            $team_map[$team->name] = $team->id;
        }
        
        // Helper function to get team ID
        $get_team_id = function($name) use ($team_map) {
            return isset($team_map[$name]) ? $team_map[$name] : null;
        };
        
        // Sample game records for 2025-2026 season
        // Format: game_date, home_team, away_team, home_score, away_score, gender, level
        $games = array();
        
        // 4A Boys Games - December 2025
        if ($sheridan = $get_team_id('Sheridan')) {
            if ($thunder_basin = $get_team_id('Thunder Basin')) {
                $games[] = array(
                    'game_date' => '2025-12-06',
                    'game_time' => '19:00:00',
                    'season_label' => '2025-2026',
                    'gender' => 'B',
                    'level' => 'Varsity',
                    'home_team_id' => $sheridan,
                    'away_team_id' => $thunder_basin,
                    'home_score' => 68,
                    'away_score' => 62,
                    'location_text' => 'Sheridan High School',
                    'week_label' => 'Week 1',
                    'conference_game' => 0,
                );
            }
        }
        
        if ($campbell = $get_team_id('Campbell County')) {
            if ($natrona = $get_team_id('Natrona County')) {
                $games[] = array(
                    'game_date' => '2025-12-07',
                    'game_time' => '18:30:00',
                    'season_label' => '2025-2026',
                    'gender' => 'B',
                    'level' => 'Varsity',
                    'home_team_id' => $campbell,
                    'away_team_id' => $natrona,
                    'home_score' => 71,
                    'away_score' => 65,
                    'location_text' => 'Campbell County High School',
                    'week_label' => 'Week 1',
                    'conference_game' => 1,
                );
            }
        }
        
        if ($kelly_walsh = $get_team_id('Kelly Walsh')) {
            if ($cheyenne_east = $get_team_id('Cheyenne East')) {
                $games[] = array(
                    'game_date' => '2025-12-10',
                    'game_time' => '19:00:00',
                    'season_label' => '2025-2026',
                    'gender' => 'B',
                    'level' => 'Varsity',
                    'home_team_id' => $kelly_walsh,
                    'away_team_id' => $cheyenne_east,
                    'home_score' => 58,
                    'away_score' => 61,
                    'location_text' => 'Kelly Walsh High School',
                    'week_label' => 'Week 2',
                    'conference_game' => 1,
                );
            }
        }
        
        if ($laramie = $get_team_id('Laramie')) {
            if ($rock_springs = $get_team_id('Rock Springs')) {
                $games[] = array(
                    'game_date' => '2025-12-13',
                    'game_time' => '19:00:00',
                    'season_label' => '2025-2026',
                    'gender' => 'B',
                    'level' => 'Varsity',
                    'home_team_id' => $laramie,
                    'away_team_id' => $rock_springs,
                    'home_score' => 72,
                    'away_score' => 68,
                    'location_text' => 'Laramie High School',
                    'week_label' => 'Week 2',
                    'conference_game' => 1,
                );
            }
        }
        
        // 3A Boys Games
        if ($cody = $get_team_id('Cody')) {
            if ($powell = $get_team_id('Powell')) {
                $games[] = array(
                    'game_date' => '2025-12-08',
                    'game_time' => '18:00:00',
                    'season_label' => '2025-2026',
                    'gender' => 'B',
                    'level' => 'Varsity',
                    'home_team_id' => $cody,
                    'away_team_id' => $powell,
                    'home_score' => 65,
                    'away_score' => 59,
                    'location_text' => 'Cody High School',
                    'week_label' => 'Week 1',
                    'conference_game' => 1,
                );
            }
        }
        
        if ($buffalo = $get_team_id('Buffalo')) {
            if ($worland = $get_team_id('Worland')) {
                $games[] = array(
                    'game_date' => '2025-12-12',
                    'game_time' => '19:00:00',
                    'season_label' => '2025-2026',
                    'gender' => 'B',
                    'level' => 'Varsity',
                    'home_team_id' => $buffalo,
                    'away_team_id' => $worland,
                    'home_score' => 54,
                    'away_score' => 58,
                    'location_text' => 'Buffalo High School',
                    'week_label' => 'Week 2',
                    'conference_game' => 1,
                );
            }
        }
        
        // Girls Games
        if ($sheridan) {
            if ($campbell) {
                $games[] = array(
                    'game_date' => '2025-12-07',
                    'game_time' => '17:00:00',
                    'season_label' => '2025-2026',
                    'gender' => 'G',
                    'level' => 'Varsity',
                    'home_team_id' => $sheridan,
                    'away_team_id' => $campbell,
                    'home_score' => 52,
                    'away_score' => 48,
                    'location_text' => 'Sheridan High School',
                    'week_label' => 'Week 1',
                    'conference_game' => 1,
                );
            }
        }
        
        if ($cheyenne_east) {
            if ($laramie) {
                $games[] = array(
                    'game_date' => '2025-12-09',
                    'game_time' => '17:30:00',
                    'season_label' => '2025-2026',
                    'gender' => 'G',
                    'level' => 'Varsity',
                    'home_team_id' => $cheyenne_east,
                    'away_team_id' => $laramie,
                    'home_score' => 61,
                    'away_score' => 55,
                    'location_text' => 'Cheyenne East High School',
                    'week_label' => 'Week 1',
                    'conference_game' => 1,
                );
            }
        }
        
        // Upcoming games (no scores)
        if ($natrona) {
            if ($kelly_walsh) {
                $games[] = array(
                    'game_date' => '2026-01-15',
                    'game_time' => '19:00:00',
                    'season_label' => '2025-2026',
                    'gender' => 'B',
                    'level' => 'Varsity',
                    'home_team_id' => $natrona,
                    'away_team_id' => $kelly_walsh,
                    'home_score' => null,
                    'away_score' => null,
                    'location_text' => 'Natrona County High School',
                    'week_label' => 'Week 6',
                    'conference_game' => 1,
                );
            }
        }
        
        if ($cody) {
            if ($buffalo) {
                $games[] = array(
                    'game_date' => '2026-01-17',
                    'game_time' => '18:00:00',
                    'season_label' => '2025-2026',
                    'gender' => 'B',
                    'level' => 'Varsity',
                    'home_team_id' => $cody,
                    'away_team_id' => $buffalo,
                    'home_score' => null,
                    'away_score' => null,
                    'location_text' => 'Cody High School',
                    'week_label' => 'Week 6',
                    'conference_game' => 1,
                );
            }
        }
        
        return $games;
    }
}
