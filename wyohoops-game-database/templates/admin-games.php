<?php
/**
 * Admin Games Page Template
 *
 * @package WyoHoops_GameDB
 */

if (!defined('ABSPATH')) exit;
?>

<div class="wrap wyohoops-admin">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php settings_errors('wyohoops_messages'); ?>
    
    <div class="wyohoops-admin-content">
        <!-- Game Form -->
        <div class="wyohoops-form-section">
            <h2><?php echo $edit_game ? __('Edit Game', 'wyohoops-gamedb') : __('Add New Game', 'wyohoops-gamedb'); ?></h2>
            
            <form method="post" action="">
                <?php wp_nonce_field('wyohoops_save_game', 'wyohoops_save_game_nonce'); ?>
                
                <?php if ($edit_game): ?>
                    <input type="hidden" name="id" value="<?php echo esc_attr($edit_game->id); ?>">
                <?php endif; ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="game_date"><?php _e('Game Date', 'wyohoops-gamedb'); ?> *</label></th>
                        <td><input type="date" name="game_date" id="game_date" value="<?php echo $edit_game ? esc_attr($edit_game->game_date) : ''; ?>" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="game_time"><?php _e('Game Time', 'wyohoops-gamedb'); ?></label></th>
                        <td><input type="time" name="game_time" id="game_time" value="<?php echo $edit_game ? esc_attr($edit_game->game_time) : ''; ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="week_label"><?php _e('Week Label', 'wyohoops-gamedb'); ?></label></th>
                        <td><input type="text" name="week_label" id="week_label" class="regular-text" value="<?php echo $edit_game ? esc_attr($edit_game->week_label) : ''; ?>" placeholder="Week 7"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="season_label"><?php _e('Season', 'wyohoops-gamedb'); ?></label></th>
                        <td><input type="text" name="season_label" id="season_label" class="regular-text" value="<?php echo $edit_game ? esc_attr($edit_game->season_label) : '2025-2026'; ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="gender"><?php _e('Gender', 'wyohoops-gamedb'); ?> *</label></th>
                        <td>
                            <select name="gender" id="gender" required>
                                <option value="B" <?php selected($edit_game ? $edit_game->gender : 'B', 'B'); ?>><?php _e('Boys', 'wyohoops-gamedb'); ?></option>
                                <option value="G" <?php selected($edit_game ? $edit_game->gender : '', 'G'); ?>><?php _e('Girls', 'wyohoops-gamedb'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="level"><?php _e('Level', 'wyohoops-gamedb'); ?></label></th>
                        <td>
                            <select name="level" id="level">
                                <option value="Varsity" <?php selected($edit_game ? $edit_game->level : 'Varsity', 'Varsity'); ?>><?php _e('Varsity', 'wyohoops-gamedb'); ?></option>
                                <option value="JV" <?php selected($edit_game ? $edit_game->level : '', 'JV'); ?>><?php _e('JV', 'wyohoops-gamedb'); ?></option>
                                <option value="Freshman" <?php selected($edit_game ? $edit_game->level : '', 'Freshman'); ?>><?php _e('Freshman', 'wyohoops-gamedb'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="home_team_id"><?php _e('Home Team', 'wyohoops-gamedb'); ?> *</label></th>
                        <td>
                            <select name="home_team_id" id="home_team_id" required>
                                <option value=""><?php _e('Select...', 'wyohoops-gamedb'); ?></option>
                                <?php foreach ($teams as $team): ?>
                                    <option value="<?php echo esc_attr($team->id); ?>" <?php selected($edit_game ? $edit_game->home_team_id : '', $team->id); ?>>
                                        <?php echo esc_html($team->name); ?> (<?php echo esc_html($team->classification); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="away_team_id"><?php _e('Away Team', 'wyohoops-gamedb'); ?> *</label></th>
                        <td>
                            <select name="away_team_id" id="away_team_id" required>
                                <option value=""><?php _e('Select...', 'wyohoops-gamedb'); ?></option>
                                <?php foreach ($teams as $team): ?>
                                    <option value="<?php echo esc_attr($team->id); ?>" <?php selected($edit_game ? $edit_game->away_team_id : '', $team->id); ?>>
                                        <?php echo esc_html($team->name); ?> (<?php echo esc_html($team->classification); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="home_score"><?php _e('Home Score', 'wyohoops-gamedb'); ?></label></th>
                        <td><input type="number" name="home_score" id="home_score" class="small-text" value="<?php echo $edit_game && $edit_game->home_score !== null ? esc_attr($edit_game->home_score) : ''; ?>" min="0"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="away_score"><?php _e('Away Score', 'wyohoops-gamedb'); ?></label></th>
                        <td><input type="number" name="away_score" id="away_score" class="small-text" value="<?php echo $edit_game && $edit_game->away_score !== null ? esc_attr($edit_game->away_score) : ''; ?>" min="0"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="location_text"><?php _e('Location', 'wyohoops-gamedb'); ?></label></th>
                        <td><input type="text" name="location_text" id="location_text" class="regular-text" value="<?php echo $edit_game ? esc_attr($edit_game->location_text) : ''; ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="conference_game"><?php _e('Conference Game', 'wyohoops-gamedb'); ?></label></th>
                        <td>
                            <input type="checkbox" name="conference_game" id="conference_game" value="1" <?php checked($edit_game ? $edit_game->conference_game : 0, 1); ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="postseason_round"><?php _e('Postseason Round', 'wyohoops-gamedb'); ?></label></th>
                        <td>
                            <select name="postseason_round" id="postseason_round">
                                <option value=""><?php _e('None', 'wyohoops-gamedb'); ?></option>
                                <option value="Regionals" <?php selected($edit_game ? $edit_game->postseason_round : '', 'Regionals'); ?>><?php _e('Regionals', 'wyohoops-gamedb'); ?></option>
                                <option value="State Quarterfinals" <?php selected($edit_game ? $edit_game->postseason_round : '', 'State Quarterfinals'); ?>><?php _e('State Quarterfinals', 'wyohoops-gamedb'); ?></option>
                                <option value="State Semifinals" <?php selected($edit_game ? $edit_game->postseason_round : '', 'State Semifinals'); ?>><?php _e('State Semifinals', 'wyohoops-gamedb'); ?></option>
                                <option value="State Championship" <?php selected($edit_game ? $edit_game->postseason_round : '', 'State Championship'); ?>><?php _e('State Championship', 'wyohoops-gamedb'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="notes"><?php _e('Notes', 'wyohoops-gamedb'); ?></label></th>
                        <td><textarea name="notes" id="notes" class="large-text" rows="3"><?php echo $edit_game ? esc_textarea($edit_game->notes) : ''; ?></textarea></td>
                    </tr>
                </table>
                
                <?php submit_button($edit_game ? __('Update Game', 'wyohoops-gamedb') : __('Add Game', 'wyohoops-gamedb')); ?>
                
                <?php if ($edit_game): ?>
                    <a href="<?php echo admin_url('admin.php?page=wyohoops-games'); ?>" class="button"><?php _e('Cancel', 'wyohoops-gamedb'); ?></a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Games List -->
        <div class="wyohoops-list-section">
            <h2><?php _e('Games', 'wyohoops-gamedb'); ?></h2>
            
            <div class="wyohoops-filters">
                <form method="get" action="">
                    <input type="hidden" name="page" value="wyohoops-games">
                    
                    <select name="gender">
                        <option value=""><?php _e('All Genders', 'wyohoops-gamedb'); ?></option>
                        <option value="B" <?php selected(isset($_GET['gender']) ? $_GET['gender'] : '', 'B'); ?>><?php _e('Boys', 'wyohoops-gamedb'); ?></option>
                        <option value="G" <?php selected(isset($_GET['gender']) ? $_GET['gender'] : '', 'G'); ?>><?php _e('Girls', 'wyohoops-gamedb'); ?></option>
                    </select>
                    
                    <select name="level">
                        <option value=""><?php _e('All Levels', 'wyohoops-gamedb'); ?></option>
                        <option value="Varsity" <?php selected(isset($_GET['level']) ? $_GET['level'] : '', 'Varsity'); ?>><?php _e('Varsity', 'wyohoops-gamedb'); ?></option>
                        <option value="JV" <?php selected(isset($_GET['level']) ? $_GET['level'] : '', 'JV'); ?>><?php _e('JV', 'wyohoops-gamedb'); ?></option>
                        <option value="Freshman" <?php selected(isset($_GET['level']) ? $_GET['level'] : '', 'Freshman'); ?>><?php _e('Freshman', 'wyohoops-gamedb'); ?></option>
                    </select>
                    
                    <button type="submit" class="button"><?php _e('Filter', 'wyohoops-gamedb'); ?></button>
                </form>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Date', 'wyohoops-gamedb'); ?></th>
                        <th><?php _e('Matchup', 'wyohoops-gamedb'); ?></th>
                        <th><?php _e('Score', 'wyohoops-gamedb'); ?></th>
                        <th><?php _e('Gender', 'wyohoops-gamedb'); ?></th>
                        <th><?php _e('Level', 'wyohoops-gamedb'); ?></th>
                        <th><?php _e('Actions', 'wyohoops-gamedb'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($games)): ?>
                        <tr>
                            <td colspan="6"><?php _e('No games found.', 'wyohoops-gamedb'); ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($games as $game): ?>
                            <?php
                            $home_team = null;
                            $away_team = null;
                            foreach ($teams as $t) {
                                if ($t->id == $game->home_team_id) $home_team = $t;
                                if ($t->id == $game->away_team_id) $away_team = $t;
                            }
                            ?>
                            <tr>
                                <td><?php echo esc_html(date('M j, Y', strtotime($game->game_date))); ?></td>
                                <td>
                                    <?php echo $home_team ? esc_html($home_team->name) : 'Unknown'; ?> vs. 
                                    <?php echo $away_team ? esc_html($away_team->name) : 'Unknown'; ?>
                                </td>
                                <td>
                                    <?php if ($game->home_score !== null && $game->away_score !== null): ?>
                                        <?php echo esc_html($game->home_score); ?> - <?php echo esc_html($game->away_score); ?>
                                    <?php else: ?>
                                        <em><?php _e('Scheduled', 'wyohoops-gamedb'); ?></em>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $game->gender === 'B' ? 'Boys' : 'Girls'; ?></td>
                                <td><?php echo esc_html($game->level); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=wyohoops-games&action=edit&game_id=' . $game->id); ?>" class="button button-small"><?php _e('Edit', 'wyohoops-gamedb'); ?></a>
                                    <button type="button" class="button button-small wyohoops-delete-game" data-game-id="<?php echo $game->id; ?>"><?php _e('Delete', 'wyohoops-gamedb'); ?></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
