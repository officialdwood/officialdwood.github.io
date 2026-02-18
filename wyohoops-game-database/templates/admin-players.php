<?php
/**
 * Admin Players Page Template
 *
 * @package WyoHoops_GameDB
 */

if (!defined('ABSPATH')) exit;
?>

<div class="wrap wyohoops-admin">
    <h1><?php echo $edit_player ? 'Edit Player' : 'Players'; ?></h1>
    
    <?php if (isset($_GET['updated'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Player saved successfully.', 'wyohoops-gamedb'); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['deleted'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Player deleted successfully.', 'wyohoops-gamedb'); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if ($edit_player || isset($_GET['action']) && $_GET['action'] === 'new'): ?>
        
        <!-- Player Edit/Add Form -->
        <form method="post" action="" class="wyohoops-form">
            <?php wp_nonce_field('wyohoops_save_player', 'wyohoops_save_player_nonce'); ?>
            
            <?php if ($edit_player): ?>
                <input type="hidden" name="player_id" value="<?php echo esc_attr($edit_player['id']); ?>">
            <?php endif; ?>
            
            <div class="wyohoops-form-grid">
                <div class="wyohoops-form-section">
                    <h2>Basic Information</h2>
                    
                    <div class="form-field">
                        <label for="team_id">Team *</label>
                        <select name="team_id" id="team_id" required>
                            <option value="">Select Team</option>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?php echo esc_attr($team['id']); ?>" <?php selected($edit_player ? $edit_player['team_id'] : '', $team['id']); ?>>
                                    <?php echo esc_html($team['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-field">
                        <label for="first_name">First Name *</label>
                        <input type="text" name="first_name" id="first_name" 
                               value="<?php echo esc_attr($edit_player ? $edit_player['first_name'] : ''); ?>" required>
                    </div>
                    
                    <div class="form-field">
                        <label for="last_name">Last Name *</label>
                        <input type="text" name="last_name" id="last_name" 
                               value="<?php echo esc_attr($edit_player ? $edit_player['last_name'] : ''); ?>" required>
                    </div>
                    
                    <div class="form-field-row">
                        <div class="form-field">
                            <label for="jersey_number">Jersey #</label>
                            <input type="text" name="jersey_number" id="jersey_number" 
                                   value="<?php echo esc_attr($edit_player ? $edit_player['jersey_number'] : ''); ?>">
                        </div>
                        
                        <div class="form-field">
                            <label for="position">Position</label>
                            <select name="position" id="position">
                                <option value="">Select Position</option>
                                <option value="PG" <?php selected($edit_player ? $edit_player['position'] : '', 'PG'); ?>>Point Guard</option>
                                <option value="SG" <?php selected($edit_player ? $edit_player['position'] : '', 'SG'); ?>>Shooting Guard</option>
                                <option value="SF" <?php selected($edit_player ? $edit_player['position'] : '', 'SF'); ?>>Small Forward</option>
                                <option value="PF" <?php selected($edit_player ? $edit_player['position'] : '', 'PF'); ?>>Power Forward</option>
                                <option value="C" <?php selected($edit_player ? $edit_player['position'] : '', 'C'); ?>>Center</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-field-row">
                        <div class="form-field">
                            <label for="year">Year</label>
                            <select name="year" id="year">
                                <option value="">Select Year</option>
                                <option value="Freshman" <?php selected($edit_player ? $edit_player['year'] : '', 'Freshman'); ?>>Freshman</option>
                                <option value="Sophomore" <?php selected($edit_player ? $edit_player['year'] : '', 'Sophomore'); ?>>Sophomore</option>
                                <option value="Junior" <?php selected($edit_player ? $edit_player['year'] : '', 'Junior'); ?>>Junior</option>
                                <option value="Senior" <?php selected($edit_player ? $edit_player['year'] : '', 'Senior'); ?>>Senior</option>
                            </select>
                        </div>
                        
                        <div class="form-field">
                            <label for="height">Height (e.g., 6'2")</label>
                            <input type="text" name="height" id="height" 
                                   value="<?php echo esc_attr($edit_player ? $edit_player['height'] : ''); ?>">
                        </div>
                        
                        <div class="form-field">
                            <label for="weight">Weight (lbs)</label>
                            <input type="text" name="weight" id="weight" 
                                   value="<?php echo esc_attr($edit_player ? $edit_player['weight'] : ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-field">
                        <label for="photo_attachment_id">Player Photo</label>
                        <input type="hidden" name="photo_attachment_id" id="photo_attachment_id" 
                               value="<?php echo esc_attr($edit_player ? $edit_player['photo_attachment_id'] : ''); ?>">
                        <button type="button" class="button" id="upload_photo_button">Select Photo</button>
                        <div id="photo_preview">
                            <?php if ($edit_player && $edit_player['photo_attachment_id']): ?>
                                <?php echo wp_get_attachment_image($edit_player['photo_attachment_id'], 'thumbnail'); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-field">
                        <label>
                            <input type="checkbox" name="has_profile" value="1" 
                                   <?php checked($edit_player ? $edit_player['has_profile'] : 0, 1); ?>>
                            Player has a public profile (show in Player Profile tab)
                        </label>
                    </div>
                </div>
                
                <div class="wyohoops-form-section">
                    <h2>Ratings (0-100)</h2>
                    
                    <div class="form-field">
                        <label for="overall_rating">Overall Rating</label>
                        <input type="number" name="overall_rating" id="overall_rating" min="0" max="100" step="0.1"
                               value="<?php echo esc_attr($edit_player ? $edit_player['overall_rating'] : '0'); ?>">
                    </div>
                    
                    <div class="form-field">
                        <label for="offensive_rating">Offensive Rating</label>
                        <input type="number" name="offensive_rating" id="offensive_rating" min="0" max="100" step="0.1"
                               value="<?php echo esc_attr($edit_player ? $edit_player['offensive_rating'] : '0'); ?>">
                    </div>
                    
                    <div class="form-field">
                        <label for="defensive_rating">Defensive Rating</label>
                        <input type="number" name="defensive_rating" id="defensive_rating" min="0" max="100" step="0.1"
                               value="<?php echo esc_attr($edit_player ? $edit_player['defensive_rating'] : '0'); ?>">
                    </div>
                    
                    <div class="form-field">
                        <label for="efficiency_rating">Efficiency Rating</label>
                        <input type="number" name="efficiency_rating" id="efficiency_rating" min="0" max="100" step="0.1"
                               value="<?php echo esc_attr($edit_player ? $edit_player['efficiency_rating'] : '0'); ?>">
                    </div>
                </div>
                
                <div class="wyohoops-form-section">
                    <h2>Statistics</h2>
                    
                    <div class="form-field">
                        <label for="games_played">Games Played</label>
                        <input type="number" name="games_played" id="games_played" min="0"
                               value="<?php echo esc_attr($edit_player ? $edit_player['games_played'] : '0'); ?>">
                    </div>
                    
                    <div class="form-field-row">
                        <div class="form-field">
                            <label for="points_per_game">Points Per Game</label>
                            <input type="number" name="points_per_game" id="points_per_game" min="0" step="0.1"
                                   value="<?php echo esc_attr($edit_player ? $edit_player['points_per_game'] : '0'); ?>">
                        </div>
                        
                        <div class="form-field">
                            <label for="rebounds_per_game">Rebounds Per Game</label>
                            <input type="number" name="rebounds_per_game" id="rebounds_per_game" min="0" step="0.1"
                                   value="<?php echo esc_attr($edit_player ? $edit_player['rebounds_per_game'] : '0'); ?>">
                        </div>
                        
                        <div class="form-field">
                            <label for="assists_per_game">Assists Per Game</label>
                            <input type="number" name="assists_per_game" id="assists_per_game" min="0" step="0.1"
                                   value="<?php echo esc_attr($edit_player ? $edit_player['assists_per_game'] : '0'); ?>">
                        </div>
                    </div>
                    
                    <div class="form-field-row">
                        <div class="form-field">
                            <label for="steals_per_game">Steals Per Game</label>
                            <input type="number" name="steals_per_game" id="steals_per_game" min="0" step="0.1"
                                   value="<?php echo esc_attr($edit_player ? $edit_player['steals_per_game'] : '0'); ?>">
                        </div>
                        
                        <div class="form-field">
                            <label for="blocks_per_game">Blocks Per Game</label>
                            <input type="number" name="blocks_per_game" id="blocks_per_game" min="0" step="0.1"
                                   value="<?php echo esc_attr($edit_player ? $edit_player['blocks_per_game'] : '0'); ?>">
                        </div>
                    </div>
                    
                    <h3>Shooting Percentages</h3>
                    
                    <div class="form-field-row">
                        <div class="form-field">
                            <label for="field_goal_pct">Field Goal %</label>
                            <input type="number" name="field_goal_pct" id="field_goal_pct" min="0" max="100" step="0.1"
                                   value="<?php echo esc_attr($edit_player ? $edit_player['field_goal_pct'] : '0'); ?>">
                        </div>
                        
                        <div class="form-field">
                            <label for="three_point_pct">3-Point %</label>
                            <input type="number" name="three_point_pct" id="three_point_pct" min="0" max="100" step="0.1"
                                   value="<?php echo esc_attr($edit_player ? $edit_player['three_point_pct'] : '0'); ?>">
                        </div>
                        
                        <div class="form-field">
                            <label for="free_throw_pct">Free Throw %</label>
                            <input type="number" name="free_throw_pct" id="free_throw_pct" min="0" max="100" step="0.1"
                                   value="<?php echo esc_attr($edit_player ? $edit_player['free_throw_pct'] : '0'); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="wyohoops-form-section wyohoops-form-full-width">
                    <h2>Biography</h2>
                    
                    <div class="form-field">
                        <label for="bio">Player Bio (optional)</label>
                        <textarea name="bio" id="bio" rows="5"><?php echo esc_textarea($edit_player ? $edit_player['bio'] : ''); ?></textarea>
                    </div>
                    
                    <div class="form-field">
                        <label>
                            <input type="checkbox" name="is_active" value="1" 
                                   <?php checked($edit_player ? $edit_player['is_active'] : 1, 1); ?>>
                            Active Player
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="wyohoops-form-actions">
                <button type="submit" class="button button-primary">
                    <?php echo $edit_player ? 'Update Player' : 'Add Player'; ?>
                </button>
                <a href="<?php echo admin_url('admin.php?page=wyohoops-players'); ?>" class="button">Cancel</a>
            </div>
        </form>
        
    <?php else: ?>
        
        <!-- Players List -->
        <div class="wyohoops-list-header">
            <a href="<?php echo admin_url('admin.php?page=wyohoops-players&action=new'); ?>" class="button button-primary">
                Add New Player
            </a>
            
            <div class="wyohoops-filters">
                <select id="filter_team" onchange="window.location.href='<?php echo admin_url('admin.php?page=wyohoops-players'); ?>&team_id=' + this.value">
                    <option value="">All Teams</option>
                    <?php foreach ($teams as $team): ?>
                        <option value="<?php echo esc_attr($team['id']); ?>" 
                                <?php selected(isset($_GET['team_id']) ? $_GET['team_id'] : '', $team['id']); ?>>
                            <?php echo esc_html($team['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select id="filter_profile" onchange="window.location.href='<?php echo admin_url('admin.php?page=wyohoops-players'); ?>&has_profile=' + this.value">
                    <option value="">All Players</option>
                    <option value="1" <?php selected(isset($_GET['has_profile']) ? $_GET['has_profile'] : '', '1'); ?>>With Profile</option>
                    <option value="0" <?php selected(isset($_GET['has_profile']) ? $_GET['has_profile'] : '', '0'); ?>>Without Profile</option>
                </select>
            </div>
        </div>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Player</th>
                    <th>Team</th>
                    <th>Jersey</th>
                    <th>Position</th>
                    <th>Year</th>
                    <th>Overall Rating</th>
                    <th>PPG</th>
                    <th>Profile</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($players)): ?>
                    <?php foreach ($players as $player): ?>
                        <?php
                        $team = null;
                        foreach ($teams as $t) {
                            if ($t['id'] == $player['team_id']) {
                                $team = $t;
                                break;
                            }
                        }
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($player['first_name'] . ' ' . $player['last_name']); ?></strong>
                            </td>
                            <td><?php echo $team ? esc_html($team['name']) : 'N/A'; ?></td>
                            <td><?php echo esc_html($player['jersey_number']); ?></td>
                            <td><?php echo esc_html($player['position']); ?></td>
                            <td><?php echo esc_html($player['year']); ?></td>
                            <td><?php echo esc_html(number_format($player['overall_rating'], 1)); ?></td>
                            <td><?php echo esc_html(number_format($player['points_per_game'], 1)); ?></td>
                            <td><?php echo $player['has_profile'] ? '✓ Yes' : '—'; ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=wyohoops-players&action=edit&player_id=' . $player['id']); ?>">Edit</a> |
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=wyohoops-players&action=delete&player_id=' . $player['id']), 'wyohoops_delete_player_' . $player['id']); ?>" 
                                   onclick="return confirm('Are you sure you want to delete this player?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">No players found. <a href="<?php echo admin_url('admin.php?page=wyohoops-players&action=new'); ?>">Add your first player</a></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
    <?php endif; ?>
</div>
