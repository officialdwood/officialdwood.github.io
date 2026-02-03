<?php
if (!defined('ABSPATH')) exit;

class BV_Admin_Settings {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }
    
    public function add_menu() {
        add_options_page(
            'Building Visualizer Settings',
            'Building Visualizer',
            'manage_options',
            'building-visualizer',
            [$this, 'render_settings_page']
        );
    }
    
    public function register_settings() {
        register_setting('bv_settings', 'bv_colors', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_colors']
        ]);
    }
    
    public function sanitize_colors($colors) {
        if (!is_array($colors)) {
            return [];
        }
        
        $sanitized = [];
        foreach (['roofing', 'siding', 'wainscott'] as $type) {
            if (isset($colors[$type]) && is_array($colors[$type])) {
                $sanitized[$type] = [];
                foreach ($colors[$type] as $color) {
                    if (isset($color['name']) && isset($color['rgb'])) {
                        $sanitized[$type][] = [
                            'name' => sanitize_text_field($color['name']),
                            'rgb' => sanitize_text_field($color['rgb'])
                        ];
                    }
                }
            }
        }
        
        return $sanitized;
    }
    
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Save settings
        if (isset($_POST['bv_save_colors']) && check_admin_referer('bv_colors_nonce')) {
            $colors = [
                'roofing' => [],
                'siding' => [],
                'wainscott' => []
            ];
            
            foreach (['roofing', 'siding', 'wainscott'] as $type) {
                if (isset($_POST[$type . '_names']) && isset($_POST[$type . '_rgbs'])) {
                    $names = $_POST[$type . '_names'];
                    $rgbs = $_POST[$type . '_rgbs'];
                    
                    for ($i = 0; $i < count($names); $i++) {
                        if (!empty($names[$i]) && !empty($rgbs[$i])) {
                            $colors[$type][] = [
                                'name' => sanitize_text_field($names[$i]),
                                'rgb' => sanitize_text_field($rgbs[$i])
                            ];
                        }
                    }
                }
            }
            
            update_option('bv_colors', $colors);
            echo '<div class="notice notice-success"><p>Colors saved successfully!</p></div>';
        }
        
        $colors = get_option('bv_colors', [
            'roofing' => [
                ['name' => 'Charcoal', 'rgb' => '54,69,79'],
                ['name' => 'Barn Red', 'rgb' => '139,26,33'],
                ['name' => 'Evergreen', 'rgb' => '34,94,68']
            ],
            'siding' => [
                ['name' => 'White', 'rgb' => '245,245,245'],
                ['name' => 'Tan', 'rgb' => '210,180,140'],
                ['name' => 'Gray', 'rgb' => '128,128,128']
            ],
            'wainscott' => [
                ['name' => 'Brown', 'rgb' => '101,67,33'],
                ['name' => 'Black', 'rgb' => '30,30,30'],
                ['name' => 'Dark Gray', 'rgb' => '64,64,64']
            ]
        ]);
        ?>
        <div class="wrap">
            <h1>Building Visualizer - Color Settings</h1>
            <p>Configure the color options that will be available in the building visualizer. Use RGB format (e.g., "255,0,0" for red).</p>
            
            <form method="post" action="">
                <?php wp_nonce_field('bv_colors_nonce'); ?>
                
                <h2>Roofing Colors</h2>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Color Name</th>
                            <th>RGB Values (format: R,G,B)</th>
                            <th>Preview</th>
                        </tr>
                    </thead>
                    <tbody id="roofing-colors">
                        <?php foreach ($colors['roofing'] as $i => $color): ?>
                        <tr>
                            <td><input type="text" name="roofing_names[]" value="<?php echo esc_attr($color['name']); ?>" class="regular-text" /></td>
                            <td><input type="text" name="roofing_rgbs[]" value="<?php echo esc_attr($color['rgb']); ?>" class="regular-text bv-rgb-input" /></td>
                            <td><div class="bv-color-preview" style="background-color: rgb(<?php echo esc_attr($color['rgb']); ?>);"></div></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="button" class="button" onclick="bvAddColorRow('roofing')">Add Roofing Color</button>
                
                <h2>Siding Colors</h2>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Color Name</th>
                            <th>RGB Values (format: R,G,B)</th>
                            <th>Preview</th>
                        </tr>
                    </thead>
                    <tbody id="siding-colors">
                        <?php foreach ($colors['siding'] as $i => $color): ?>
                        <tr>
                            <td><input type="text" name="siding_names[]" value="<?php echo esc_attr($color['name']); ?>" class="regular-text" /></td>
                            <td><input type="text" name="siding_rgbs[]" value="<?php echo esc_attr($color['rgb']); ?>" class="regular-text bv-rgb-input" /></td>
                            <td><div class="bv-color-preview" style="background-color: rgb(<?php echo esc_attr($color['rgb']); ?>);"></div></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="button" class="button" onclick="bvAddColorRow('siding')">Add Siding Color</button>
                
                <h2>Wainscott Colors</h2>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Color Name</th>
                            <th>RGB Values (format: R,G,B)</th>
                            <th>Preview</th>
                        </tr>
                    </thead>
                    <tbody id="wainscott-colors">
                        <?php foreach ($colors['wainscott'] as $i => $color): ?>
                        <tr>
                            <td><input type="text" name="wainscott_names[]" value="<?php echo esc_attr($color['name']); ?>" class="regular-text" /></td>
                            <td><input type="text" name="wainscott_rgbs[]" value="<?php echo esc_attr($color['rgb']); ?>" class="regular-text bv-rgb-input" /></td>
                            <td><div class="bv-color-preview" style="background-color: rgb(<?php echo esc_attr($color['rgb']); ?>);"></div></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="button" class="button" onclick="bvAddColorRow('wainscott')">Add Wainscott Color</button>
                
                <p class="submit">
                    <input type="submit" name="bv_save_colors" class="button button-primary" value="Save Colors" />
                </p>
            </form>
        </div>
        
        <script>
        function bvAddColorRow(type) {
            var tbody = document.getElementById(type + '-colors');
            var row = document.createElement('tr');
            row.innerHTML = '<td><input type="text" name="' + type + '_names[]" class="regular-text" /></td>' +
                           '<td><input type="text" name="' + type + '_rgbs[]" class="regular-text bv-rgb-input" /></td>' +
                           '<td><div class="bv-color-preview"></div></td>';
            tbody.appendChild(row);
        }
        
        // Update color preview on RGB input change
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.bv-rgb-input').forEach(function(input) {
                input.addEventListener('input', function() {
                    var preview = this.closest('tr').querySelector('.bv-color-preview');
                    preview.style.backgroundColor = 'rgb(' + this.value + ')';
                });
            });
        });
        </script>
        
        <style>
        .bv-color-preview {
            width: 50px;
            height: 30px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        </style>
        <?php
    }
}

new BV_Admin_Settings();
