<?php
/**
 * Stats service for calculating team statistics and rankings.
 *
 * @package WyoHoops_GameDB
 */

class WyoHoops_Stats_Service {

    private $teams_repo;
    private $games_repo;

    public function __construct() {
        $this->teams_repo = new WyoHoops_Repository_Teams();
        $this->games_repo = new WyoHoops_Repository_Games();
    }

    /**
     * Get team stats with caching support.
     */
    public function get_team_stats($team_id, $gender = 'B', $level = 'Varsity') {
        $cache_enabled = get_option('wyohoops_enable_caching', 1);
        $cache_key = "wyohoops_stats_{$team_id}_{$gender}_{$level}";
        
        if ($cache_enabled) {
            $cached = get_transient($cache_key);
            if ($cached !== false) {
                return $cached;
            }
        }
        
        $stats = $this->calculate_team_stats($team_id, $gender, $level);
        
        if ($cache_enabled) {
            set_transient($cache_key, $stats, HOUR_IN_SECONDS);
        }
        
        return $stats;
    }

    /**
     * Calculate team statistics.
     */
    private function calculate_team_stats($team_id, $gender = 'B', $level = 'Varsity') {
        $games = $this->games_repo->get_games(array(
            'team_id' => $team_id,
            'gender' => $gender,
            'level' => $level,
            'completed_only' => true
        ));
        
        $stats = array(
            'team_id' => $team_id,
            'wins' => 0,
            'losses' => 0,
            'games_played' => 0,
            'win_pct' => 0,
            'points_for' => 0,
            'points_against' => 0,
            'avg_points_for' => 0,
            'avg_points_against' => 0,
            'point_differential' => 0,
            'offensive_efficiency' => 0,
            'defensive_efficiency' => 0
        );
        
        foreach ($games as $game) {
            if ($game->home_score === null || $game->away_score === null) {
                continue;
            }
            
            $is_home = ($game->home_team_id == $team_id);
            $team_score = $is_home ? $game->home_score : $game->away_score;
            $opponent_score = $is_home ? $game->away_score : $game->home_score;
            
            $stats['games_played']++;
            $stats['points_for'] += $team_score;
            $stats['points_against'] += $opponent_score;
            
            if ($team_score > $opponent_score) {
                $stats['wins']++;
            } else {
                $stats['losses']++;
            }
        }
        
        // Calculate averages
        if ($stats['games_played'] > 0) {
            $stats['avg_points_for'] = round($stats['points_for'] / $stats['games_played'], 2);
            $stats['avg_points_against'] = round($stats['points_against'] / $stats['games_played'], 2);
            $stats['win_pct'] = round($stats['wins'] / $stats['games_played'], 3);
        }
        
        $stats['point_differential'] = $stats['points_for'] - $stats['points_against'];
        
        // Calculate efficiency scores
        $stats['offensive_efficiency'] = $this->calculate_offensive_efficiency($stats['avg_points_for']);
        $stats['defensive_efficiency'] = $this->calculate_defensive_efficiency($stats['avg_points_against']);
        
        return $stats;
    }

    /**
     * Calculate offensive efficiency (0-100).
     * Default: 80 points per game = 98 efficiency
     */
    private function calculate_offensive_efficiency($avg_points) {
        $baseline_points = get_option('wyohoops_off_eff_baseline_points', 80);
        $baseline_score = get_option('wyohoops_off_eff_baseline_score', 98);
        
        if ($avg_points <= 0) {
            return 0;
        }
        
        $efficiency = round(($avg_points / $baseline_points) * $baseline_score);
        return max(0, min(100, $efficiency));
    }

    /**
     * Calculate defensive efficiency (0-100).
     * Default: Holding opponent to 40 points = 96 efficiency
     */
    private function calculate_defensive_efficiency($avg_points_against) {
        $baseline_points = get_option('wyohoops_def_eff_baseline_points', 40);
        $baseline_score = get_option('wyohoops_def_eff_baseline_score', 96);
        
        if ($avg_points_against <= 0) {
            return 100;
        }
        
        $efficiency = round(($baseline_points / $avg_points_against) * $baseline_score);
        return max(0, min(100, $efficiency));
    }

    /**
     * Get rankings for teams.
     */
    public function get_rankings($gender = 'B', $classification = '', $level = 'Varsity') {
        $args = array(
            'is_active' => 1
        );
        
        if (!empty($classification)) {
            $args['classification'] = $classification;
        }
        
        $teams = $this->teams_repo->get_teams($args);
        $rankings = array();
        
        foreach ($teams as $team) {
            $stats = $this->get_team_stats($team->id, $gender, $level);
            $rankings[] = array_merge((array)$team, $stats);
        }
        
        // Sort by ranking criteria
        usort($rankings, function($a, $b) {
            // 1. Win percentage
            if ($a['win_pct'] != $b['win_pct']) {
                return $b['win_pct'] <=> $a['win_pct'];
            }
            // 2. Offensive efficiency
            if ($a['offensive_efficiency'] != $b['offensive_efficiency']) {
                return $b['offensive_efficiency'] <=> $a['offensive_efficiency'];
            }
            // 3. Defensive efficiency
            if ($a['defensive_efficiency'] != $b['defensive_efficiency']) {
                return $b['defensive_efficiency'] <=> $a['defensive_efficiency'];
            }
            // 4. Point differential
            return $b['point_differential'] <=> $a['point_differential'];
        });
        
        // Add rank numbers
        foreach ($rankings as $index => &$team) {
            $team['rank'] = $index + 1;
        }
        
        return $rankings;
    }

    /**
     * Get head to head results between two teams.
     */
    public function get_head_to_head($team_a_id, $team_b_id, $gender = 'B', $level = 'Varsity') {
        global $wpdb;
        $table = $wpdb->prefix . 'wyohoops_games';
        
        $games = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table 
            WHERE gender = %s 
            AND level = %s
            AND home_score IS NOT NULL 
            AND away_score IS NOT NULL
            AND ((home_team_id = %d AND away_team_id = %d) 
                OR (home_team_id = %d AND away_team_id = %d))
            ORDER BY game_date DESC",
            $gender, $level, $team_a_id, $team_b_id, $team_b_id, $team_a_id
        ));
        
        return $games;
    }

    /**
     * Clear all stats cache.
     */
    public function clear_all_cache() {
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wyohoops_stats_%' OR option_name LIKE '_transient_timeout_wyohoops_stats_%'");
    }
}
