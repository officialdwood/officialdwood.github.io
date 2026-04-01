<?php
/**
 * Games repository for database operations.
 *
 * @package WyoHoops_GameDB
 */

class WyoHoops_Repository_Games {

    /**
     * Get all games with optional filters.
     */
    public function get_games($args = array()) {
        global $wpdb;
        $table = $wpdb->prefix . 'wyohoops_games';
        
        $defaults = array(
            'gender' => '',
            'level' => '',
            'team_id' => '',
            'season_label' => '',
            'week_label' => '',
            'conference_game' => '',
            'postseason_round' => '',
            'completed_only' => false,
            'upcoming_only' => false,
            'date_from' => '',
            'date_to' => '',
            'orderby' => 'game_date',
            'order' => 'DESC',
            'limit' => -1,
            'offset' => 0
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = array();
        $where_values = array();
        
        if (!empty($args['gender'])) {
            $where[] = 'gender = %s';
            $where_values[] = $args['gender'];
        }
        
        if (!empty($args['level'])) {
            $where[] = 'level = %s';
            $where_values[] = $args['level'];
        }
        
        if (!empty($args['team_id'])) {
            $where[] = '(home_team_id = %d OR away_team_id = %d)';
            $where_values[] = absint($args['team_id']);
            $where_values[] = absint($args['team_id']);
        }
        
        if (!empty($args['season_label'])) {
            $where[] = 'season_label = %s';
            $where_values[] = $args['season_label'];
        }
        
        if (!empty($args['week_label'])) {
            $where[] = 'week_label = %s';
            $where_values[] = $args['week_label'];
        }
        
        if ($args['conference_game'] !== '') {
            $where[] = 'conference_game = %d';
            $where_values[] = absint($args['conference_game']);
        }
        
        if (!empty($args['postseason_round'])) {
            $where[] = 'postseason_round = %s';
            $where_values[] = $args['postseason_round'];
        }
        
        if ($args['completed_only']) {
            $where[] = 'home_score IS NOT NULL AND away_score IS NOT NULL';
        }
        
        if ($args['upcoming_only']) {
            $where[] = '(home_score IS NULL OR away_score IS NULL)';
        }
        
        if (!empty($args['date_from'])) {
            $where[] = 'game_date >= %s';
            $where_values[] = $args['date_from'];
        }
        
        if (!empty($args['date_to'])) {
            $where[] = 'game_date <= %s';
            $where_values[] = $args['date_to'];
        }
        
        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        $order_clause = $orderby ? "ORDER BY $orderby" : 'ORDER BY game_date DESC';
        
        $limit_clause = '';
        if ($args['limit'] > 0) {
            $limit_clause = $wpdb->prepare('LIMIT %d OFFSET %d', $args['limit'], $args['offset']);
        }
        
        $sql = "SELECT * FROM $table $where_clause $order_clause $limit_clause";
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }
        
        return $wpdb->get_results($sql);
    }

    /**
     * Get a single game by ID.
     */
    public function get_game($game_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'wyohoops_games';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $game_id
        ));
    }

    /**
     * Insert or update a game.
     */
    public function save_game($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'wyohoops_games';
        
        $game_data = array(
            'game_date' => sanitize_text_field($data['game_date']),
            'game_time' => !empty($data['game_time']) ? sanitize_text_field($data['game_time']) : null,
            'week_label' => !empty($data['week_label']) ? sanitize_text_field($data['week_label']) : null,
            'season_label' => !empty($data['season_label']) ? sanitize_text_field($data['season_label']) : '2025-2026',
            'gender' => sanitize_text_field($data['gender']),
            'level' => !empty($data['level']) ? sanitize_text_field($data['level']) : 'Varsity',
            'home_team_id' => absint($data['home_team_id']),
            'away_team_id' => absint($data['away_team_id']),
            'home_score' => !empty($data['home_score']) ? absint($data['home_score']) : null,
            'away_score' => !empty($data['away_score']) ? absint($data['away_score']) : null,
            'location_text' => !empty($data['location_text']) ? sanitize_text_field($data['location_text']) : null,
            'conference_game' => isset($data['conference_game']) ? absint($data['conference_game']) : 0,
            'postseason_round' => !empty($data['postseason_round']) ? sanitize_text_field($data['postseason_round']) : null,
            'notes' => !empty($data['notes']) ? sanitize_textarea_field($data['notes']) : null,
        );
        
        $format = array('%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%d', '%s', '%s');
        
        if (!empty($data['id'])) {
            // Update existing game
            $wpdb->update(
                $table,
                $game_data,
                array('id' => absint($data['id'])),
                $format,
                array('%d')
            );
            
            // Clear stats cache when a game is updated
            $this->clear_stats_cache();
            
            return absint($data['id']);
        } else {
            // Insert new game
            $wpdb->insert($table, $game_data, $format);
            
            // Clear stats cache when a game is added
            $this->clear_stats_cache();
            
            return $wpdb->insert_id;
        }
    }

    /**
     * Delete a game.
     */
    public function delete_game($game_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'wyohoops_games';
        
        $result = $wpdb->delete($table, array('id' => absint($game_id)), array('%d'));
        
        // Clear stats cache when a game is deleted
        $this->clear_stats_cache();
        
        return $result;
    }

    /**
     * Get games for a specific team.
     */
    public function get_team_games($team_id, $limit = -1, $completed_only = false) {
        $args = array(
            'team_id' => $team_id,
            'limit' => $limit,
            'orderby' => 'game_date',
            'order' => 'DESC'
        );
        
        if ($completed_only) {
            $args['completed_only'] = true;
        }
        
        return $this->get_games($args);
    }

    /**
     * Clear stats cache.
     */
    private function clear_stats_cache() {
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wyohoops_stats_%' OR option_name LIKE '_transient_timeout_wyohoops_stats_%'");
    }
}
