<?php
if (!defined('ABSPATH')) exit;

class MastrLabShortcode {
    public function __construct() {
        add_shortcode('mastrlab', [$this, 'render']);
    }
    public function render($atts = [], $content = null) {
        ob_start(); ?>
        <div class="mastrlab-wrap" data-version="<?php echo esc_attr(MASTRLAB_VERSION); ?>">
            <div class="ml-topbar">
                <img class="ml-logo" alt="MastrLab" src="<?php echo esc_url(MASTRLAB_URL . 'assets/img/logo.png'); ?>" />
                <div class="ml-title">MastrLab <span>4.0</span></div>
                <div class="ml-presets">
                    <label>Mode</label>
                    <select id="ml-mode">
                        <option value="vocals">Vocals</option>
                        <option value="song" selected>Song</option>
                        <option value="sample">Sample</option>
                        <option value="track">Track</option>
                    </select>
                    <select id="ml-preset"></select>
                    <label class="ml-ai">
                        <input type="checkbox" id="ml-ai-toggle" checked /> AI Auto‑Master
                    </label>
                </div>
            </div>

            <div class="ml-upload">
                <input type="file" id="ml-file" accept=".wav,.mp3,audio/*" />
                <button class="ml-btn" id="ml-load">Load</button>
            </div>

            <div id="ml-loader" class="ml-loader hidden">
                <div class="ml-loader-bar"><span id="ml-loader-progress" style="width:0%"></span></div>
                <div class="ml-loader-text"><span id="ml-loader-pct">0%</span> Uploading & Preparing…</div>
            </div>

            <div class="ml-transport">
                <button class="ml-btn" id="ml-play">Play</button>
                <button class="ml-btn" id="ml-stop">Stop</button>
                <div class="ml-time"><span id="ml-current">0:00</span> / <span id="ml-duration">0:00</span></div>
            </div>

            <div class="ml-wave">
                <div id="ml-waveform"></div>
                <div id="ml-timeline"></div>
            </div>

            <div class="ml-controls">
                <div class="ml-module">
                    <div class="ml-mod-title">Loudness</div>
                    <div class="ml-knob" data-param="gain" data-min="-24" data-max="24" data-step="0.1" data-default="0"></div>
                </div>
                <div class="ml-module">
                    <div class="ml-mod-title">EQ Low</div>
                    <div class="ml-knob" data-param="eqLow" data-min="-24" data-max="24" data-step="0.1" data-default="0"></div>
                </div>
                <div class="ml-module">
                    <div class="ml-mod-title">EQ Mid</div>
                    <div class="ml-knob" data-param="eqMid" data-min="-24" data-max="24" data-step="0.1" data-default="0"></div>
                </div>
                <div class="ml-module">
                    <div class="ml-mod-title">EQ High</div>
                    <div class="ml-knob" data-param="eqHigh" data-min="-24" data-max="24" data-step="0.1" data-default="0"></div>
                </div>
                <div class="ml-module">
                    <div class="ml-mod-title">Comp</div>
                    <div class="ml-knob" data-param="comp" data-min="0" data-max="1" data-step="0.01" data-default="0.35"></div>
                </div>
                <div class="ml-module">
                    <div class="ml-mod-title">Limiter</div>
                    <div class="ml-knob" data-param="limit" data-min="-12" data-max="0" data-step="0.1" data-default="-1"></div>
                </div>
                <div class="ml-module">
                    <div class="ml-mod-title">De‑esser</div>
                    <div class="ml-knob" data-param="deesser" data-min="0" data-max="1" data-step="0.01" data-default="0.25"></div>
                </div>
            </div>

            <div class="ml-export">
                <label>Download as</label>
                <select id="ml-format">
                    <option value="wav" selected>WAV</option>
                    <option value="mp3">MP3</option>
                </select>
                <button class="ml-btn primary" id="ml-render">Render & Download</button>
            </div>
        </div>
        <?php return ob_get_clean();
    }
}

new MastrLabShortcode();
