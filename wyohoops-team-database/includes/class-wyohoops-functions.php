<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WyoHoops_Functions {
    /**
     * @var wpdb
     */
    protected $db;

    /**
     * @var string
     */
    protected $teams_table;

    /**
     * @var string
     */
    protected $players_table;

    public function __construct() {
        global $wpdb;
        $this->db            = $wpdb;
        $this->teams_table   = $wpdb->prefix . 'wyohoops_teams';
        $this->players_table = $wpdb->prefix . 'wyohoops_players';
    }

    public function get_team( $id ) {
        $id = absint( $id );
        if ( ! $id ) {
            return null;
        }
        return $this->db->get_row( $this->db->prepare( "SELECT * FROM {$this->teams_table} WHERE id = %d", $id ), ARRAY_A );
    }

    public function get_team_by_slug( $slug ) {
        if ( ! $slug ) {
            return null;
        }
        return $this->db->get_row( $this->db->prepare( "SELECT * FROM {$this->teams_table} WHERE slug = %s", sanitize_title( $slug ) ), ARRAY_A );
    }

    public function get_teams( $args = array() ) {
        $defaults = array(
            'classification' => '',
            'gender'         => '',
            'search'         => '',
            'rank_min'       => null,
            'rank_max'       => null,
            'limit'          => 100,
            'order_by'       => 'rank',
            'order'          => 'ASC',
        );
        $args     = wp_parse_args( $args, $defaults );

        $where  = array();
        $params = array();

        if ( ! empty( $args['classification'] ) ) {
            $where[]  = 'classification = %s';
            $params[] = strtoupper( $args['classification'] );
        }

        if ( ! empty( $args['gender'] ) ) {
            $where[]  = 'gender = %s';
            $params[] = strtolower( $args['gender'] );
        }

        if ( $args['rank_min'] !== null ) {
            $where[]  = 'rank >= %d';
            $params[] = absint( $args['rank_min'] );
        }

        if ( $args['rank_max'] !== null ) {
            $where[]  = 'rank <= %d';
            $params[] = absint( $args['rank_max'] );
        }

        if ( ! empty( $args['search'] ) ) {
            $like     = '%' . $this->db->esc_like( $args['search'] ) . '%';
            $where[]  = '(school_name LIKE %s OR city LIKE %s)';
            $params[] = $like;
            $params[] = $like;
        }

        $where_sql = $where ? 'WHERE ' . implode( ' AND ', $where ) : '';

        $order_by = in_array( $args['order_by'], array( 'rank', 'school_name', 'team_rating', 'def_rating', 'classification' ), true ) ? $args['order_by'] : 'rank';
        $order    = ( 'DESC' === strtoupper( $args['order'] ) ) ? 'DESC' : 'ASC';

        $limit = absint( $args['limit'] );
        if ( ! $limit ) {
            $limit = 100;
        }

        $sql = "SELECT * FROM {$this->teams_table} {$where_sql} ORDER BY {$order_by} {$order} LIMIT %d";

        $params[] = $limit;

        return $this->db->get_results( $this->db->prepare( $sql, $params ), ARRAY_A );
    }

    public function save_team( $data ) {
        $defaults = array(
            'id'                 => 0,
            'school_name'        => '',
            'city'               => '',
            'state'              => 'WY',
            'classification'     => '',
            'gender'             => '',
            'slug'               => '',
            'logo_attachment_id' => null,
            'primary_color'      => '',
            'secondary_color'    => '',
            'rank'               => null,
            'team_rating'        => null,
            'def_rating'         => null,
        );

        $data = wp_parse_args( $data, $defaults );

        $data['school_name']    = sanitize_text_field( $data['school_name'] );
        $data['city']           = sanitize_text_field( $data['city'] );
        $data['state']          = strtoupper( sanitize_text_field( $data['state'] ) );
        $data['classification'] = strtoupper( sanitize_text_field( $data['classification'] ) );
        $data['gender']         = sanitize_text_field( strtolower( $data['gender'] ) );
        $data['slug']           = sanitize_title( $data['slug'] ?: $data['school_name'] . '-' . $data['gender'] );

        $data['logo_attachment_id'] = $data['logo_attachment_id'] ? absint( $data['logo_attachment_id'] ) : null;
        $data['primary_color']      = $this->sanitize_hex( $data['primary_color'] );
        $data['secondary_color']    = $this->sanitize_hex( $data['secondary_color'] );
        $data['rank']               = ( '' === $data['rank'] || null === $data['rank'] ) ? null : absint( $data['rank'] );
        $data['team_rating']        = $this->sanitize_rating( $data['team_rating'] );
        $data['def_rating']         = $this->sanitize_rating( $data['def_rating'] );

        $now = current_time( 'mysql' );

        $payload = array(
            'school_name'        => $data['school_name'],
            'city'               => $data['city'],
            'state'              => $data['state'],
            'classification'     => $data['classification'],
            'gender'             => $data['gender'],
            'slug'               => $data['slug'],
            'logo_attachment_id' => $data['logo_attachment_id'],
            'primary_color'      => $data['primary_color'],
            'secondary_color'    => $data['secondary_color'],
            'rank'               => $data['rank'],
            'team_rating'        => $data['team_rating'],
            'def_rating'         => $data['def_rating'],
            'updated_at'         => $now,
        );

        $formats        = array( '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%d', '%d', '%s' );
        $insert_formats = array_merge( $formats, array( '%s' ) );

        if ( empty( $data['id'] ) ) {
            $payload['created_at'] = $now;
            $result                = $this->db->insert( $this->teams_table, $payload, $insert_formats );
            return $result ? $this->db->insert_id : false;
        }

        return false !== $this->db->update( $this->teams_table, $payload, array( 'id' => absint( $data['id'] ) ), $formats, array( '%d' ) );
    }

    public function delete_team( $id ) {
        $id = absint( $id );
        if ( ! $id ) {
            return false;
        }

        $this->db->delete( $this->players_table, array( 'team_id' => $id ), array( '%d' ) );
        return false !== $this->db->delete( $this->teams_table, array( 'id' => $id ), array( '%d' ) );
    }

    public function get_roster( $team_id ) {
        $team_id = absint( $team_id );
        if ( ! $team_id ) {
            return array();
        }

        $sql = $this->db->prepare( "SELECT * FROM {$this->players_table} WHERE team_id = %d ORDER BY CAST(jersey_number AS UNSIGNED), last_name", $team_id );
        return $this->db->get_results( $sql, ARRAY_A );
    }

    public function save_player( $data ) {
        $defaults = array(
            'id'             => 0,
            'team_id'        => 0,
            'first_name'     => '',
            'last_name'      => '',
            'position'       => '',
            'grade'          => '',
            'height_ft'      => '',
            'height_in'      => '',
            'player_rating'  => '',
            'jersey_number'  => '',
        );

        $data = wp_parse_args( $data, $defaults );

        $payload = array(
            'team_id'       => absint( $data['team_id'] ),
            'first_name'    => sanitize_text_field( $data['first_name'] ),
            'last_name'     => sanitize_text_field( $data['last_name'] ),
            'position'      => sanitize_text_field( $data['position'] ),
            'grade'         => absint( $data['grade'] ),
            'height_ft'     => absint( $data['height_ft'] ),
            'height_in'     => absint( $data['height_in'] ),
            'player_rating' => $this->sanitize_rating( $data['player_rating'] ),
            'jersey_number' => sanitize_text_field( $data['jersey_number'] ),
            'updated_at'    => current_time( 'mysql' ),
        );

        $formats = array( '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%s' );

        if ( empty( $data['id'] ) ) {
            $payload['created_at'] = current_time( 'mysql' );
            $inserted              = $this->db->insert( $this->players_table, $payload, $formats );
            return $inserted ? $this->db->insert_id : false;
        }

        return false !== $this->db->update( $this->players_table, $payload, array( 'id' => absint( $data['id'] ) ), $formats, array( '%d' ) );
    }

    public function get_player( $id ) {
        $id = absint( $id );
        if ( ! $id ) {
            return null;
        }
        return $this->db->get_row( $this->db->prepare( "SELECT * FROM {$this->players_table} WHERE id = %d", $id ), ARRAY_A );
    }

    public function delete_player( $id ) {
        $id = absint( $id );
        if ( ! $id ) {
            return false;
        }
        return false !== $this->db->delete( $this->players_table, array( 'id' => $id ), array( '%d' ) );
    }

    protected function sanitize_hex( $hex ) {
        $hex = sanitize_text_field( $hex );
        if ( preg_match( '/^#([0-9a-fA-F]{3}){1,2}$/', $hex ) ) {
            return strtolower( $hex );
        }
        return '';
    }

    protected function sanitize_rating( $value ) {
        if ( '' === $value || null === $value ) {
            return null;
        }
        $value = absint( $value );
        return min( 100, $value );
    }
}

if ( ! function_exists( 'wyohoops_functions_instance' ) ) {
    function wyohoops_functions_instance() {
        static $instance = null;
        if ( null === $instance ) {
            $instance = new WyoHoops_Functions();
        }
        return $instance;
    }
}

if ( ! function_exists( 'wyohoops_get_team' ) ) {
    function wyohoops_get_team( $team_id ) {
        return wyohoops_functions_instance()->get_team( $team_id );
    }
}

if ( ! function_exists( 'wyohoops_get_teams' ) ) {
    function wyohoops_get_teams( $args = array() ) {
        return wyohoops_functions_instance()->get_teams( $args );
    }
}

if ( ! function_exists( 'wyohoops_get_roster' ) ) {
    function wyohoops_get_roster( $team_id ) {
        return wyohoops_functions_instance()->get_roster( $team_id );
    }
}
