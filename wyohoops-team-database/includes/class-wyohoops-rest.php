<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WyoHoops_REST {
    /** @var WyoHoops_Functions */
    protected $functions;

    public function __construct( WyoHoops_Functions $functions ) {
        $this->functions = $functions;
    }

    public function hooks() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function register_routes() {
        register_rest_route(
            'wyohoops/v1',
            '/teams',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_teams' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'classification' => array( 'sanitize_callback' => 'sanitize_text_field' ),
                    'gender'         => array( 'sanitize_callback' => 'sanitize_text_field' ),
                    'rank_min'       => array( 'sanitize_callback' => 'absint' ),
                    'rank_max'       => array( 'sanitize_callback' => 'absint' ),
                    'limit'          => array( 'sanitize_callback' => 'absint' ),
                ),
            )
        );

        register_rest_route(
            'wyohoops/v1',
            '/teams/(?P<id>\\d+)',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_team' ),
                'permission_callback' => '__return_true',
            )
        );

        register_rest_route(
            'wyohoops/v1',
            '/teams/(?P<id>\\d+)/players',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_players' ),
                'permission_callback' => '__return_true',
            )
        );
    }

    public function get_teams( WP_REST_Request $request ) {
        $args = array(
            'classification' => $request->get_param( 'classification' ),
            'gender'         => $request->get_param( 'gender' ),
            'rank_min'       => $request->get_param( 'rank_min' ),
            'rank_max'       => $request->get_param( 'rank_max' ),
            'limit'          => $request->get_param( 'limit' ) ?: 100,
        );

        $teams = $this->functions->get_teams( $args );
        return rest_ensure_response( $this->prepare_teams( $teams ) );
    }

    public function get_team( WP_REST_Request $request ) {
        $team = $this->functions->get_team( $request['id'] );
        if ( ! $team ) {
            return new WP_Error( 'not_found', __( 'Team not found', 'wyohoops-team-database' ), array( 'status' => 404 ) );
        }

        return rest_ensure_response( $this->prepare_team( $team ) );
    }

    public function get_players( WP_REST_Request $request ) {
        $team = $this->functions->get_team( $request['id'] );
        if ( ! $team ) {
            return new WP_Error( 'not_found', __( 'Team not found', 'wyohoops-team-database' ), array( 'status' => 404 ) );
        }

        $players = $this->functions->get_roster( $team['id'] );
        return rest_ensure_response( $this->prepare_players( $players ) );
    }

    protected function prepare_teams( $teams ) {
        return array_map( array( $this, 'prepare_team' ), $teams );
    }

    protected function prepare_team( $team ) {
        return array(
            'id'             => (int) $team['id'],
            'school_name'    => $team['school_name'],
            'city'           => $team['city'],
            'state'          => $team['state'],
            'classification' => $team['classification'],
            'gender'         => $team['gender'],
            'slug'           => $team['slug'],
            'logo'           => ! empty( $team['logo_attachment_id'] ) ? wp_get_attachment_url( $team['logo_attachment_id'] ) : '',
            'primary_color'  => $team['primary_color'],
            'secondary_color' => $team['secondary_color'],
            'rank'           => $team['rank'],
            'team_rating'    => $team['team_rating'],
            'def_rating'     => $team['def_rating'],
        );
    }

    protected function prepare_players( $players ) {
        return array_map(
            function ( $player ) {
                return array(
                    'id'            => (int) $player['id'],
                    'team_id'       => (int) $player['team_id'],
                    'first_name'    => $player['first_name'],
                    'last_name'     => $player['last_name'],
                    'position'      => $player['position'],
                    'grade'         => $player['grade'],
                    'height_ft'     => $player['height_ft'],
                    'height_in'     => $player['height_in'],
                    'player_rating' => $player['player_rating'],
                    'jersey_number' => $player['jersey_number'],
                );
            },
            $players
        );
    }
}
