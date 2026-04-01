<?php
/**
 * Player Repository - Data access layer for players.
 *
 * @package WyoHoops_GameDB
 */

class WyoHoops_Repository_Players {

    private $table_name;
    private $wpdb;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'wyohoops_players';
    }

    /**
     * Get all players with optional filters.
     *
     * @param array $args Filter arguments.
     * @return array
     */
    public function get_players($args = array()) {
        $defaults = array(
            'team_id' => null,
            'has_profile' => null,
            'is_active' => 1,
            'orderby' => 'overall_rating',
            'order' => 'DESC',
            'limit' => 100,
            'offset' => 0
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = array('1=1');
        
        if ($args['team_id']) {
            $where[] = $this->wpdb->prepare('team_id = %d', $args['team_id']);
        }
        
        if ($args['has_profile'] !== null) {
            $where[] = $this->wpdb->prepare('has_profile = %d', $args['has_profile']);
        }
        
        if ($args['is_active'] !== null) {
            $where[] = $this->wpdb->prepare('is_active = %d', $args['is_active']);
        }
        
        $where_clause = implode(' AND ', $where);
        
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        
        $sql = "SELECT * FROM {$this->table_name} 
                WHERE $where_clause 
                ORDER BY $orderby 
                LIMIT %d OFFSET %d";
        
        return $this->wpdb->get_results(
            $this->wpdb->prepare($sql, $args['limit'], $args['offset']),
            ARRAY_A
        );
    }

    /**
     * Get player by ID.
     *
     * @param int $id Player ID.
     * @return array|null
     */
    public function get_player($id) {
        $sql = "SELECT * FROM {$this->table_name} WHERE id = %d";
        return $this->wpdb->get_row($this->wpdb->prepare($sql, $id), ARRAY_A);
    }

    /**
     * Get players with profiles (for player profile tab).
     *
     * @param array $args Filter arguments.
     * @return array
     */
    public function get_player_profiles($args = array()) {
        $defaults = array(
            'orderby' => 'overall_rating',
            'order' => 'DESC',
            'limit' => 100
        );
        
        $args = wp_parse_args($args, $defaults);
        $args['has_profile'] = 1;
        
        return $this->get_players($args);
    }

    /**
     * Get roster for a team.
     *
     * @param int $team_id Team ID.
     * @return array
     */
    public function get_team_roster($team_id) {
        return $this->get_players(array(
            'team_id' => $team_id,
            'orderby' => 'jersey_number',
            'order' => 'ASC'
        ));
    }

    /**
     * Save player (insert or update).
     *
     * @param array $data Player data.
     * @return int|false Player ID on success, false on failure.
     */
    public function save_player($data) {
        $defaults = array(
            'team_id' => 0,
            'first_name' => '',
            'last_name' => '',
            'jersey_number' => null,
            'position' => null,
            'year' => null,
            'height' => null,
            'weight' => null,
            'photo_attachment_id' => null,
            'has_profile' => 0,
            'offensive_rating' => 0,
            'defensive_rating' => 0,
            'overall_rating' => 0,
            'efficiency_rating' => 0,
            'points_per_game' => 0,
            'rebounds_per_game' => 0,
            'assists_per_game' => 0,
            'steals_per_game' => 0,
            'blocks_per_game' => 0,
            'field_goal_pct' => 0,
            'three_point_pct' => 0,
            'free_throw_pct' => 0,
            'games_played' => 0,
            'bio' => null,
            'is_active' => 1
        );
        
        $data = wp_parse_args($data, $defaults);
        
        $format = array(
            '%d', // team_id
            '%s', // first_name
            '%s', // last_name
            '%s', // jersey_number
            '%s', // position
            '%s', // year
            '%s', // height
            '%s', // weight
            '%d', // photo_attachment_id
            '%d', // has_profile
            '%f', // offensive_rating
            '%f', // defensive_rating
            '%f', // overall_rating
            '%f', // efficiency_rating
            '%f', // points_per_game
            '%f', // rebounds_per_game
            '%f', // assists_per_game
            '%f', // steals_per_game
            '%f', // blocks_per_game
            '%f', // field_goal_pct
            '%f', // three_point_pct
            '%f', // free_throw_pct
            '%d', // games_played
            '%s', // bio
            '%d'  // is_active
        );
        
        if (isset($data['id']) && $data['id']) {
            $id = $data['id'];
            unset($data['id']);
            
            $result = $this->wpdb->update(
                $this->table_name,
                $data,
                array('id' => $id),
                $format,
                array('%d')
            );
            
            return $result !== false ? $id : false;
        } else {
            unset($data['id']);
            
            $result = $this->wpdb->insert(
                $this->table_name,
                $data,
                $format
            );
            
            return $result ? $this->wpdb->insert_id : false;
        }
    }

    /**
     * Delete player.
     *
     * @param int $id Player ID.
     * @return bool
     */
    public function delete_player($id) {
        return (bool) $this->wpdb->delete(
            $this->table_name,
            array('id' => $id),
            array('%d')
        );
    }

    /**
     * Get top players by rating type.
     *
     * @param string $rating_type Rating type (overall, offensive, defensive, efficiency).
     * @param int $limit Number of players to return.
     * @return array
     */
    public function get_top_players($rating_type = 'overall', $limit = 10) {
        $valid_types = array('overall', 'offensive', 'defensive', 'efficiency');
        
        if (!in_array($rating_type, $valid_types)) {
            $rating_type = 'overall';
        }
        
        $column = $rating_type . '_rating';
        
        return $this->get_player_profiles(array(
            'orderby' => $column,
            'order' => 'DESC',
            'limit' => $limit
        ));
    }

    /**
     * Get player count.
     *
     * @param array $args Filter arguments.
     * @return int
     */
    public function get_player_count($args = array()) {
        $defaults = array(
            'team_id' => null,
            'has_profile' => null,
            'is_active' => 1
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = array('1=1');
        
        if ($args['team_id']) {
            $where[] = $this->wpdb->prepare('team_id = %d', $args['team_id']);
        }
        
        if ($args['has_profile'] !== null) {
            $where[] = $this->wpdb->prepare('has_profile = %d', $args['has_profile']);
        }
        
        if ($args['is_active'] !== null) {
            $where[] = $this->wpdb->prepare('is_active = %d', $args['is_active']);
        }
        
        $where_clause = implode(' AND ', $where);
        
        $sql = "SELECT COUNT(*) FROM {$this->table_name} WHERE $where_clause";
        
        return (int) $this->wpdb->get_var($sql);
    }
}
