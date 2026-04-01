<?php
/**
 * REST API endpoints for the plugin.
 *
 * @package WyoHoops_GameDB
 */

class WyoHoops_REST_API {

    private $teams_repo;
    private $games_repo;
    private $players_repo;
    private $stats_service;

    public function __construct() {
        $this->teams_repo = new WyoHoops_Repository_Teams();
        $this->games_repo = new WyoHoops_Repository_Games();
        $this->players_repo = new WyoHoops_Repository_Players();
        $this->stats_service = new WyoHoops_Stats_Service();
    }

    /**
     * Register REST API routes.
     */
    public function register_routes() {
        register_rest_route('wyohoops/v1', '/teams', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_teams'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('wyohoops/v1', '/teams/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_team'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('wyohoops/v1', '/rankings', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_rankings'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('wyohoops/v1', '/games', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_games'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('wyohoops/v1', '/games/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_game'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('wyohoops/v1', '/compare', array(
            'methods' => 'GET',
            'callback' => array($this, 'compare_teams'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('wyohoops/v1', '/players', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_players'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('wyohoops/v1', '/players/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_player'),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route('wyohoops/v1', '/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_team_stats'),
            'permission_callback' => '__return_true'
        ));
    }

    /**
     * Get teams endpoint.
     */
    public function get_teams($request) {
        $params = $request->get_params();
        
        $args = array(
            'classification' => isset($params['classification']) ? sanitize_text_field($params['classification']) : '',
            'search' => isset($params['search']) ? sanitize_text_field($params['search']) : '',
            'is_active' => isset($params['is_active']) ? absint($params['is_active']) : 1,
            'orderby' => isset($params['orderby']) ? sanitize_text_field($params['orderby']) : 'name',
            'order' => isset($params['order']) ? sanitize_text_field($params['order']) : 'ASC'
        );
        
        $teams = $this->teams_repo->get_teams($args);
        
        return rest_ensure_response($teams);
    }

    /**
     * Get single team endpoint.
     */
    public function get_team($request) {
        $team_id = $request->get_param('id');
        $team = $this->teams_repo->get_team($team_id);
        
        if (!$team) {
            return new WP_Error('not_found', 'Team not found', array('status' => 404));
        }
        
        $gender = $request->get_param('gender') ?: 'B';
        $level = $request->get_param('level') ?: 'Varsity';
        
        $stats = $this->stats_service->get_team_stats($team_id, $gender, $level);
        $recent_games = $this->games_repo->get_team_games($team_id, 5, true);
        
        return rest_ensure_response(array(
            'team' => $team,
            'stats' => $stats,
            'recent_games' => $recent_games
        ));
    }

    /**
     * Get rankings endpoint.
     */
    public function get_rankings($request) {
        $params = $request->get_params();
        
        $gender = isset($params['gender']) ? sanitize_text_field($params['gender']) : 'B';
        $classification = isset($params['classification']) ? sanitize_text_field($params['classification']) : '';
        $level = isset($params['level']) ? sanitize_text_field($params['level']) : 'Varsity';
        $orderby = isset($params['orderby']) ? sanitize_text_field($params['orderby']) : 'rank';
        
        $rankings = $this->stats_service->get_rankings($gender, $classification, $level);
        
        // Apply custom sorting if needed
        if ($orderby !== 'rank') {
            usort($rankings, function($a, $b) use ($orderby) {
                $order = 'DESC'; // Default descending
                
                switch ($orderby) {
                    case 'wins':
                        return $b['wins'] <=> $a['wins'];
                    case 'losses':
                        return $a['losses'] <=> $b['losses'];
                    case 'win_pct':
                        return $b['win_pct'] <=> $a['win_pct'];
                    case 'offensive_efficiency':
                        return $b['offensive_efficiency'] <=> $a['offensive_efficiency'];
                    case 'defensive_efficiency':
                        return $b['defensive_efficiency'] <=> $a['defensive_efficiency'];
                    case 'points_for':
                        return $b['points_for'] <=> $a['points_for'];
                    case 'points_against':
                        return $a['points_against'] <=> $b['points_against'];
                    case 'point_differential':
                        return $b['point_differential'] <=> $a['point_differential'];
                    default:
                        return 0;
                }
            });
        }
        
        return rest_ensure_response($rankings);
    }

    /**
     * Get games endpoint.
     */
    public function get_games($request) {
        $params = $request->get_params();
        
        $args = array(
            'gender' => isset($params['gender']) ? sanitize_text_field($params['gender']) : '',
            'level' => isset($params['level']) ? sanitize_text_field($params['level']) : '',
            'team_id' => isset($params['team_id']) ? absint($params['team_id']) : '',
            'completed_only' => isset($params['completed_only']) ? (bool)$params['completed_only'] : false,
            'upcoming_only' => isset($params['upcoming_only']) ? (bool)$params['upcoming_only'] : false,
            'conference_game' => isset($params['conference_game']) ? absint($params['conference_game']) : '',
            'limit' => isset($params['limit']) ? absint($params['limit']) : 50
        );
        
        $games = $this->games_repo->get_games($args);
        
        // Enrich games with team names
        foreach ($games as &$game) {
            $home_team = $this->teams_repo->get_team($game->home_team_id);
            $away_team = $this->teams_repo->get_team($game->away_team_id);
            
            $game->home_team_name = $home_team ? $home_team->name : '';
            $game->away_team_name = $away_team ? $away_team->name : '';
            $game->home_team_abbr = $home_team ? $home_team->abbreviation : '';
            $game->away_team_abbr = $away_team ? $away_team->abbreviation : '';
        }
        
        return rest_ensure_response($games);
    }

    /**
     * Get single game endpoint.
     */
    public function get_game($request) {
        $game_id = $request->get_param('id');
        $game = $this->games_repo->get_game($game_id);
        
        if (!$game) {
            return new WP_Error('not_found', 'Game not found', array('status' => 404));
        }
        
        // Enrich with team data
        $home_team = $this->teams_repo->get_team($game->home_team_id);
        $away_team = $this->teams_repo->get_team($game->away_team_id);
        
        $game->home_team = $home_team;
        $game->away_team = $away_team;
        
        return rest_ensure_response($game);
    }

    /**
     * Compare teams endpoint.
     */
    public function compare_teams($request) {
        $team_a_id = $request->get_param('team_a');
        $team_b_id = $request->get_param('team_b');
        $gender = $request->get_param('gender') ?: 'B';
        $level = $request->get_param('level') ?: 'Varsity';
        
        if (!$team_a_id || !$team_b_id) {
            return new WP_Error('invalid_params', 'Both team_a and team_b are required', array('status' => 400));
        }
        
        $team_a = $this->teams_repo->get_team($team_a_id);
        $team_b = $this->teams_repo->get_team($team_b_id);
        
        if (!$team_a || !$team_b) {
            return new WP_Error('not_found', 'One or both teams not found', array('status' => 404));
        }
        
        $stats_a = $this->stats_service->get_team_stats($team_a_id, $gender, $level);
        $stats_b = $this->stats_service->get_team_stats($team_b_id, $gender, $level);
        
        $head_to_head = $this->stats_service->get_head_to_head($team_a_id, $team_b_id, $gender, $level);
        
        // Determine edges
        $comparison = array(
            'team_a' => array_merge((array)$team_a, $stats_a),
            'team_b' => array_merge((array)$team_b, $stats_b),
            'head_to_head' => $head_to_head,
            'edges' => array(
                'win_pct' => $stats_a['win_pct'] > $stats_b['win_pct'] ? 'A' : ($stats_a['win_pct'] < $stats_b['win_pct'] ? 'B' : 'tie'),
                'offensive' => $stats_a['offensive_efficiency'] > $stats_b['offensive_efficiency'] ? 'A' : ($stats_a['offensive_efficiency'] < $stats_b['offensive_efficiency'] ? 'B' : 'tie'),
                'defensive' => $stats_a['defensive_efficiency'] > $stats_b['defensive_efficiency'] ? 'A' : ($stats_a['defensive_efficiency'] < $stats_b['defensive_efficiency'] ? 'B' : 'tie'),
            )
        );
        
        return rest_ensure_response($comparison);
    }

    /**
     * Get players endpoint.
     */
    public function get_players($request) {
        $params = $request->get_params();
        
        $args = array(
            'team_id' => isset($params['team_id']) ? absint($params['team_id']) : null,
            'has_profile' => isset($params['has_profile']) ? absint($params['has_profile']) : 1,
            'is_active' => isset($params['is_active']) ? absint($params['is_active']) : 1,
            'orderby' => isset($params['orderby']) ? sanitize_text_field($params['orderby']) : 'overall_rating',
            'order' => isset($params['order']) ? sanitize_text_field($params['order']) : 'DESC',
            'limit' => isset($params['limit']) ? absint($params['limit']) : 100
        );
        
        $players = $this->players_repo->get_players($args);
        
        // Enrich with team information
        foreach ($players as &$player) {
            if ($player['team_id']) {
                $team = $this->teams_repo->get_team($player['team_id']);
                if ($team) {
                    $player['team'] = $team;
                }
            }
        }
        
        return rest_ensure_response($players);
    }

    /**
     * Get single player endpoint.
     */
    public function get_player($request) {
        $player_id = $request['id'];
        
        $player = $this->players_repo->get_player($player_id);
        
        if (!$player) {
            return new WP_Error('not_found', 'Player not found', array('status' => 404));
        }
        
        // Enrich with team information
        if ($player['team_id']) {
            $team = $this->teams_repo->get_team($player['team_id']);
            if ($team) {
                $player['team'] = $team;
            }
        }
        
        return rest_ensure_response($player);
    }

    /**
     * Get team stats endpoint.
     */
    public function get_team_stats($request) {
        $params = $request->get_params();
        
        $classification = isset($params['classification']) ? sanitize_text_field($params['classification']) : '';
        $gender = isset($params['gender']) ? sanitize_text_field($params['gender']) : 'B';
        $level = isset($params['level']) ? sanitize_text_field($params['level']) : 'Varsity';
        
        // Get teams
        $team_args = array(
            'is_active' => 1,
            'orderby' => 'name',
            'order' => 'ASC'
        );
        
        if ($classification) {
            $team_args['classification'] = $classification;
        }
        
        $teams = $this->teams_repo->get_teams($team_args);
        
        // Calculate stats for each team
        $stats = array();
        foreach ($teams as $team) {
            $team_stats = $this->stats_service->get_team_stats($team['id'], $gender, $level);
            
            if ($team_stats['games_played'] > 0) {
                $stats[] = array(
                    'team' => $team,
                    'stats' => $team_stats
                );
            }
        }
        
        return rest_ensure_response($stats);
    }
}
