<?php
if (!defined('ABSPATH')) exit;

class BV_Shortcode {
    public function __construct() {
        add_shortcode('building_visualizer', [$this, 'render']);
    }
    
    public function render($atts = [], $content = null) {
        $atts = shortcode_atts([
            'width' => '20',
            'length' => '40',
            'wall_height' => '12',
            'roof_pitch' => '4'
        ], $atts);
        
        ob_start(); ?>
        <div class="bv-wrap" data-version="<?php echo esc_attr(BV_VERSION); ?>">
            <div class="bv-header">
                <h2>Building Visualizer</h2>
                <p>Customize your building and see it come to life!</p>
            </div>
            
            <div class="bv-container">
                <div class="bv-controls">
                    <div class="bv-section">
                        <h3>Dimensions</h3>
                        <div class="bv-control-group">
                            <label for="bv-width">Width (feet)</label>
                            <input type="number" id="bv-width" value="<?php echo esc_attr($atts['width']); ?>" min="10" max="100" step="1" />
                        </div>
                        <div class="bv-control-group">
                            <label for="bv-length">Length (feet)</label>
                            <input type="number" id="bv-length" value="<?php echo esc_attr($atts['length']); ?>" min="10" max="100" step="1" />
                        </div>
                        <div class="bv-control-group">
                            <label for="bv-wall-height">Wall Height (feet)</label>
                            <input type="number" id="bv-wall-height" value="<?php echo esc_attr($atts['wall_height']); ?>" min="8" max="20" step="1" />
                        </div>
                        <div class="bv-control-group">
                            <label for="bv-roof-pitch">Roof Pitch (rise per 12")</label>
                            <input type="number" id="bv-roof-pitch" value="<?php echo esc_attr($atts['roof_pitch']); ?>" min="1" max="12" step="0.5" />
                        </div>
                    </div>
                    
                    <div class="bv-section">
                        <h3>Colors</h3>
                        <div class="bv-control-group">
                            <label for="bv-roofing-color">Roofing Color</label>
                            <select id="bv-roofing-color">
                                <!-- Populated by JavaScript -->
                            </select>
                        </div>
                        <div class="bv-control-group">
                            <label for="bv-siding-color">Siding Color</label>
                            <select id="bv-siding-color">
                                <!-- Populated by JavaScript -->
                            </select>
                        </div>
                        <div class="bv-control-group">
                            <label>
                                <input type="checkbox" id="bv-wainscott-enabled" checked />
                                Enable Wainscott
                            </label>
                        </div>
                        <div class="bv-control-group" id="bv-wainscott-controls">
                            <label for="bv-wainscott-height">Wainscott Height (feet)</label>
                            <input type="number" id="bv-wainscott-height" value="3" min="0" max="8" step="0.5" />
                        </div>
                        <div class="bv-control-group" id="bv-wainscott-color-group">
                            <label for="bv-wainscott-color">Wainscott Color</label>
                            <select id="bv-wainscott-color">
                                <!-- Populated by JavaScript -->
                            </select>
                        </div>
                    </div>
                    
                    <div class="bv-section">
                        <h3>View Controls</h3>
                        <div class="bv-control-group">
                            <label for="bv-rotation">Rotation (degrees)</label>
                            <input type="range" id="bv-rotation" min="0" max="360" value="30" step="5" />
                            <span id="bv-rotation-value">30°</span>
                        </div>
                        <div class="bv-control-group">
                            <label for="bv-zoom">Zoom Level</label>
                            <input type="range" id="bv-zoom" min="0.5" max="3" value="1" step="0.1" />
                            <span id="bv-zoom-value">100%</span>
                        </div>
                        <div class="bv-control-group bv-button-group">
                            <button id="bv-zoom-in" class="bv-button">Zoom In</button>
                            <button id="bv-zoom-out" class="bv-button">Zoom Out</button>
                        </div>
                    </div>
                    
                    <div class="bv-section">
                        <h3>Actions</h3>
                        <button id="bv-download" class="bv-button primary">Download Image</button>
                    </div>
                </div>
                
                <div class="bv-preview">
                    <canvas id="bv-canvas" width="800" height="600"></canvas>
                    <div class="bv-info">
                        <div class="bv-info-item">
                            <strong>Building Size:</strong> <span id="bv-info-size">-</span>
                        </div>
                        <div class="bv-info-item">
                            <strong>Wall Height:</strong> <span id="bv-info-wall">-</span>
                        </div>
                        <div class="bv-info-item">
                            <strong>Roof Pitch:</strong> <span id="bv-info-pitch">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php return ob_get_clean();
    }
}

new BV_Shortcode();
