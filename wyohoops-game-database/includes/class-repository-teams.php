<?php
/**
 * Teams repository for database operations.
 *
 * @package WyoHoops_GameDB
 */

class WyoHoops_Repository_Teams {

    /**
     * Get all teams with optional filters.
     */
    public function get_teams($args = array()) {
        global $wpdb;
        $table = $wpdb->prefix . 'wyohoops_teams';
        
        $defaults = array(
            'classification' => '',
            'is_active' => 1,
            'search' => '',
            'orderby' => 'name',
            'order' => 'ASC',
            'limit' => -1,
            'offset' => 0
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = array();
        $where_values = array();
        
        if (!empty($args['is_active'])) {
            $where[] = 'is_active = %d';
            $where_values[] = $args['is_active'];
        }
        
        if (!empty($args['classification'])) {
            $where[] = 'classification = %s';
            $where_values[] = $args['classification'];
        }
        
        if (!empty($args['search'])) {
            $where[] = '(name LIKE %s OR abbreviation LIKE %s OR location_city LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $orderby = sanitize_sql_orderby($args['orderby'] . ' ' . $args['order']);
        $order_clause = $orderby ? "ORDER BY $orderby" : 'ORDER BY name ASC';
        
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
     * Get a single team by ID.
     */
    public function get_team($team_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'wyohoops_teams';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $team_id
        ));
    }

    /**
     * Insert or update a team.
     */
    public function save_team($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'wyohoops_teams';
        
        $team_data = array(
            'name' => sanitize_text_field($data['name']),
            'abbreviation' => sanitize_text_field($data['abbreviation']),
            'mascot' => !empty($data['mascot']) ? sanitize_text_field($data['mascot']) : null,
            'classification' => sanitize_text_field($data['classification']),
            'location_city' => !empty($data['location_city']) ? sanitize_text_field($data['location_city']) : null,
            'location_notes' => !empty($data['location_notes']) ? sanitize_textarea_field($data['location_notes']) : null,
            'primary_color' => !empty($data['primary_color']) ? sanitize_hex_color($data['primary_color']) : '#C8A100',
            'secondary_color' => !empty($data['secondary_color']) ? sanitize_hex_color($data['secondary_color']) : '#111111',
            'logo_attachment_id' => !empty($data['logo_attachment_id']) ? absint($data['logo_attachment_id']) : null,
            'school_photo_attachment_id' => !empty($data['school_photo_attachment_id']) ? absint($data['school_photo_attachment_id']) : null,
            'offensive_rating' => isset($data['offensive_rating']) ? floatval($data['offensive_rating']) : 0,
            'defensive_rating' => isset($data['defensive_rating']) ? floatval($data['defensive_rating']) : 0,
            'overall_rating' => isset($data['overall_rating']) ? floatval($data['overall_rating']) : 0,
            'is_active' => isset($data['is_active']) ? absint($data['is_active']) : 1,
        );
        
        $format = array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%f', '%f', '%f', '%d');
        
        if (!empty($data['id'])) {
            // Update existing team
            $wpdb->update(
                $table,
                $team_data,
                array('id' => absint($data['id'])),
                $format,
                array('%d')
            );
            return absint($data['id']);
        } else {
            // Insert new team
            $wpdb->insert($table, $team_data, $format);
            return $wpdb->insert_id;
        }
    }

    /**
     * Delete a team.
     */
    public function delete_team($team_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'wyohoops_teams';
        
        return $wpdb->delete($table, array('id' => absint($team_id)), array('%d'));
    }

    /**
     * Get team count.
     */
    public function get_team_count($args = array()) {
        global $wpdb;
        $table = $wpdb->prefix . 'wyohoops_teams';
        
        $where = array();
        $where_values = array();
        
        if (!empty($args['classification'])) {
            $where[] = 'classification = %s';
            $where_values[] = $args['classification'];
        }
        
        if (!empty($args['is_active'])) {
            $where[] = 'is_active = %d';
            $where_values[] = $args['is_active'];
        }
        
        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT COUNT(*) FROM $table $where_clause";
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }
        
        return (int) $wpdb->get_var($sql);
    }
}
