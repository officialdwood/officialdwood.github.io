<?php
/**
 * Admin Teams Page Template
 *
 * @package WyoHoops_GameDB
 */

if (!defined('ABSPATH')) exit;
?>

<div class="wrap wyohoops-admin">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php settings_errors('wyohoops_messages'); ?>
    
    <div class="wyohoops-admin-content">
        <!-- Team Form -->
        <div class="wyohoops-form-section">
            <h2><?php echo $edit_team ? __('Edit Team', 'wyohoops-gamedb') : __('Add New Team', 'wyohoops-gamedb'); ?></h2>
            
            <form method="post" action="">
                <?php wp_nonce_field('wyohoops_save_team', 'wyohoops_save_team_nonce'); ?>
                
                <?php if ($edit_team): ?>
                    <input type="hidden" name="id" value="<?php echo esc_attr($edit_team->id); ?>">
                <?php endif; ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="name"><?php _e('School Name', 'wyohoops-gamedb'); ?> *</label></th>
                        <td><input type="text" name="name" id="name" class="regular-text" value="<?php echo $edit_team ? esc_attr($edit_team->name) : ''; ?>" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="abbreviation"><?php _e('Abbreviation', 'wyohoops-gamedb'); ?> *</label></th>
                        <td><input type="text" name="abbreviation" id="abbreviation" class="small-text" value="<?php echo $edit_team ? esc_attr($edit_team->abbreviation) : ''; ?>" required maxlength="10"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="classification"><?php _e('Classification', 'wyohoops-gamedb'); ?> *</label></th>
                        <td>
                            <select name="classification" id="classification" required>
                                <option value=""><?php _e('Select...', 'wyohoops-gamedb'); ?></option>
                                <option value="4A" <?php selected($edit_team ? $edit_team->classification : '', '4A'); ?>>4A</option>
                                <option value="3A" <?php selected($edit_team ? $edit_team->classification : '', '3A'); ?>>3A</option>
                                <option value="2A" <?php selected($edit_team ? $edit_team->classification : '', '2A'); ?>>2A</option>
                                <option value="1A" <?php selected($edit_team ? $edit_team->classification : '', '1A'); ?>>1A</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="location_city"><?php _e('City', 'wyohoops-gamedb'); ?></label></th>
                        <td><input type="text" name="location_city" id="location_city" class="regular-text" value="<?php echo $edit_team ? esc_attr($edit_team->location_city) : ''; ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="location_notes"><?php _e('Location Notes', 'wyohoops-gamedb'); ?></label></th>
                        <td><textarea name="location_notes" id="location_notes" class="large-text" rows="2"><?php echo $edit_team ? esc_textarea($edit_team->location_notes) : ''; ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="primary_color"><?php _e('Primary Color', 'wyohoops-gamedb'); ?></label></th>
                        <td><input type="text" name="primary_color" id="primary_color" class="wyohoops-color-picker" value="<?php echo $edit_team ? esc_attr($edit_team->primary_color) : '#C8A100'; ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="secondary_color"><?php _e('Secondary Color', 'wyohoops-gamedb'); ?></label></th>
                        <td><input type="text" name="secondary_color" id="secondary_color" class="wyohoops-color-picker" value="<?php echo $edit_team ? esc_attr($edit_team->secondary_color) : '#111111'; ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="logo_attachment_id"><?php _e('Team Logo', 'wyohoops-gamedb'); ?></label></th>
                        <td>
                            <input type="hidden" name="logo_attachment_id" id="logo_attachment_id" value="<?php echo $edit_team ? esc_attr($edit_team->logo_attachment_id) : ''; ?>">
                            <button type="button" class="button wyohoops-upload-button" data-target="logo_attachment_id"><?php _e('Upload Logo', 'wyohoops-gamedb'); ?></button>
                            <div id="logo_preview" class="wyohoops-image-preview">
                                <?php if ($edit_team && $edit_team->logo_attachment_id): ?>
                                    <?php echo wp_get_attachment_image($edit_team->logo_attachment_id, 'thumbnail'); ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="school_photo_attachment_id"><?php _e('School Photo', 'wyohoops-gamedb'); ?></label></th>
                        <td>
                            <input type="hidden" name="school_photo_attachment_id" id="school_photo_attachment_id" value="<?php echo $edit_team ? esc_attr($edit_team->school_photo_attachment_id) : ''; ?>">
                            <button type="button" class="button wyohoops-upload-button" data-target="school_photo_attachment_id"><?php _e('Upload Photo', 'wyohoops-gamedb'); ?></button>
                            <div id="school_photo_preview" class="wyohoops-image-preview">
                                <?php if ($edit_team && $edit_team->school_photo_attachment_id): ?>
                                    <?php echo wp_get_attachment_image($edit_team->school_photo_attachment_id, 'medium'); ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="is_active"><?php _e('Active', 'wyohoops-gamedb'); ?></label></th>
                        <td>
                            <input type="checkbox" name="is_active" id="is_active" value="1" <?php checked($edit_team ? $edit_team->is_active : 1, 1); ?>>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button($edit_team ? __('Update Team', 'wyohoops-gamedb') : __('Add Team', 'wyohoops-gamedb')); ?>
                
                <?php if ($edit_team): ?>
                    <a href="<?php echo admin_url('admin.php?page=wyohoops-gamedb'); ?>" class="button"><?php _e('Cancel', 'wyohoops-gamedb'); ?></a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Teams List -->
        <div class="wyohoops-list-section">
            <h2><?php _e('Teams', 'wyohoops-gamedb'); ?></h2>
            
            <div class="wyohoops-filters">
                <form method="get" action="">
                    <input type="hidden" name="page" value="wyohoops-gamedb">
                    
                    <input type="text" name="s" placeholder="<?php _e('Search teams...', 'wyohoops-gamedb'); ?>" value="<?php echo esc_attr($search); ?>">
                    
                    <select name="classification">
                        <option value=""><?php _e('All Classifications', 'wyohoops-gamedb'); ?></option>
                        <option value="4A" <?php selected($classification, '4A'); ?>>4A</option>
                        <option value="3A" <?php selected($classification, '3A'); ?>>3A</option>
                        <option value="2A" <?php selected($classification, '2A'); ?>>2A</option>
                        <option value="1A" <?php selected($classification, '1A'); ?>>1A</option>
                    </select>
                    
                    <button type="submit" class="button"><?php _e('Filter', 'wyohoops-gamedb'); ?></button>
                </form>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Name', 'wyohoops-gamedb'); ?></th>
                        <th><?php _e('Abbr', 'wyohoops-gamedb'); ?></th>
                        <th><?php _e('Class', 'wyohoops-gamedb'); ?></th>
                        <th><?php _e('City', 'wyohoops-gamedb'); ?></th>
                        <th><?php _e('Colors', 'wyohoops-gamedb'); ?></th>
                        <th><?php _e('Active', 'wyohoops-gamedb'); ?></th>
                        <th><?php _e('Actions', 'wyohoops-gamedb'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($teams)): ?>
                        <tr>
                            <td colspan="7"><?php _e('No teams found.', 'wyohoops-gamedb'); ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($teams as $team): ?>
                            <tr>
                                <td><strong><?php echo esc_html($team->name); ?></strong></td>
                                <td><?php echo esc_html($team->abbreviation); ?></td>
                                <td><?php echo esc_html($team->classification); ?></td>
                                <td><?php echo esc_html($team->location_city); ?></td>
                                <td>
                                    <span class="wyohoops-color-swatch" style="background-color: <?php echo esc_attr($team->primary_color); ?>"></span>
                                    <span class="wyohoops-color-swatch" style="background-color: <?php echo esc_attr($team->secondary_color); ?>"></span>
                                </td>
                                <td><?php echo $team->is_active ? '✓' : '—'; ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=wyohoops-gamedb&action=edit&team_id=' . $team->id); ?>" class="button button-small"><?php _e('Edit', 'wyohoops-gamedb'); ?></a>
                                    <button type="button" class="button button-small wyohoops-delete-team" data-team-id="<?php echo $team->id; ?>"><?php _e('Delete', 'wyohoops-gamedb'); ?></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
