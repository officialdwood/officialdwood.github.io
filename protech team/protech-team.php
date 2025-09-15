<?php
/**
 * Plugin Name: Protech Team
 * Description: Basic, modern, Elementor-friendly Team carousel for Protech Steel. Use shortcode [protech_team].
 * Version: 1.1.0
 * Author: Protech
 * License: GPLv2 or later
 * Text Domain: protech-team
 */

if ( ! defined('ABSPATH') ) exit;

define('PROTECH_TEAM_VER', '1.1.0');
define('PROTECH_TEAM_URL', plugin_dir_url(__FILE__));
define('PROTECH_TEAM_PATH', plugin_dir_path(__FILE__));

add_action('init', function(){
  $labels = array(
    'name' => __('Team Members', 'protech-team'),
    'singular_name' => __('Team Member', 'protech-team'),
    'add_new_item' => __('Add New Team Member', 'protech-team'),
    'edit_item' => __('Edit Team Member', 'protech-team'),
    'menu_name' => __('Protech Team', 'protech-team'),
  );
  $args = array(
    'label' => __('Team Member', 'protech-team'),
    'labels' => $labels,
    'public' => true,
    'show_in_menu' => true,
    'menu_icon' => 'dashicons-groups',
    'supports' => array('title', 'thumbnail', 'editor'),
    'has_archive' => false,
    'show_in_rest' => true,
  );
  register_post_type('protech_team', $args);
  add_post_type_support('protech_team', 'thumbnail');
});

/** Admin meta box: role, phone, email, photo (media uploader) */
add_action('add_meta_boxes', function(){
  add_meta_box('protech_team_details', __('Team Details','protech-team'), function($post){
    $role = get_post_meta($post->ID, '_pt_role', true);
    $phone = get_post_meta($post->ID, '_pt_phone', true);
    $email = get_post_meta($post->ID, '_pt_email', true);
    $photo = get_post_meta($post->ID, '_pt_photo', true); // URL
    wp_nonce_field('protech_team_save', 'protech_team_nonce');
    ?>
    <style>
      .pt-meta-grid{display:grid;grid-template-columns:140px 1fr;gap:12px;align-items:center}
      .pt-meta-grid .full{grid-column:1/-1;margin-top:8px}
      .pt-photo-wrap{display:flex;align-items:center;gap:12px}
      .pt-photo-wrap img{max-width:120px;height:auto;border:1px solid rgba(0,0,0,.1);border-radius:8px;background:transparent}
      .button.pt-remove{display: <?php echo $photo ? 'inline-block' : 'none'; ?>;}
    </style>
    <div class="pt-meta-grid">
      <label for="pt_role"><strong><?php _e('Role/Title','protech-team'); ?></strong></label>
      <input type="text" id="pt_role" name="pt_role" value="<?php echo esc_attr($role); ?>" />
      <label for="pt_phone"><strong><?php _e('Phone','protech-team'); ?></strong></label>
      <input type="text" id="pt_phone" name="pt_phone" value="<?php echo esc_attr($phone); ?>" placeholder="+1 (555) 123-4567" />
      <label for="pt_email"><strong><?php _e('Email','protech-team'); ?></strong></label>
      <input type="email" id="pt_email" name="pt_email" value="<?php echo esc_attr($email); ?>" placeholder="name@company.com" />
      <div class="full"><strong><?php _e('Profile Photo (PNG keeps transparency)','protech-team'); ?></strong></div>
      <div class="pt-photo-wrap full">
        <img id="pt_photo_preview" src="<?php echo esc_url($photo ?: PROTECH_TEAM_URL.'assets/img/avatar-placeholder.png'); ?>" alt="" />
        <input type="hidden" id="pt_photo" name="pt_photo" value="<?php echo esc_attr($photo); ?>" />
        <button type="button" class="button pt-upload"><?php _e('Choose/Upload','protech-team'); ?></button>
        <button type="button" class="button pt-remove"><?php _e('Remove','protech-team'); ?></button>
      </div>
      <p class="full"><?php _e('Note: You can also set a Featured Image. The Profile Photo field (PNG recommended) will take priority.','protech-team'); ?></p>
    </div>
    <?php
  }, 'protech_team', 'normal', 'high');
});


/** Settings page for background & mask **/
add_action('admin_menu', function(){
  add_submenu_page(
    'edit.php?post_type=protech_team',
    __('Protech Team Settings','protech-team'),
    __('Settings','protech-team'),
    'manage_options',
    'protech-team-settings',
    'protech_team_render_settings'
  );
});

add_action('admin_init', function(){
  register_setting('protech_team_settings', 'pt_bg_image', ['type'=>'string','sanitize_callback'=>'esc_url_raw','default'=>'']);
  register_setting('protech_team_settings', 'pt_bg_size', ['type'=>'string','sanitize_callback'=>'sanitize_text_field','default'=>'cover']);
  register_setting('protech_team_settings', 'pt_bg_position', ['type'=>'string','sanitize_callback'=>'sanitize_text_field','default'=>'center center']);
  register_setting('protech_team_settings', 'pt_bg_height', ['type'=>'string','sanitize_callback'=>'sanitize_text_field','default'=>'420px']);
  register_setting('protech_team_settings', 'pt_mask_color', ['type'=>'string','sanitize_callback'=>'sanitize_text_field','default'=>'#000000']);
  register_setting('protech_team_settings', 'pt_mask_opacity', ['type'=>'number','sanitize_callback'=>'floatval','default'=>0.35]);
});

function protech_team_render_settings(){
  if ( ! current_user_can('manage_options') ) return;
  ?>
  <div class="wrap">
    <h1><?php _e('Protech Team — Display Settings','protech-team'); ?></h1>
    <form method="post" action="options.php">
      <?php settings_fields('protech_team_settings'); do_settings_sections('protech_team_settings'); ?>
      <table class="form-table" role="presentation">
        <tr>
          <th scope="row"><label for="pt_bg_image"><?php _e('Background Image','protech-team'); ?></label></th>
          <td>
            <input type="text" id="pt_bg_image" name="pt_bg_image" value="<?php echo esc_attr(get_option('pt_bg_image','')); ?>" class="regular-text" />
            <button type="button" class="button" id="pt_bg_upload"><?php _e('Choose/Upload','protech-team'); ?></button>
            <p class="description"><?php _e('Select a static image. It will remain fixed while the page scrolls.','protech-team'); ?></p>
          </td>
        </tr>
        <tr><th><label for="pt_bg_size"><?php _e('Background Size','protech-team'); ?></label></th>
          <td><input type="text" id="pt_bg_size" name="pt_bg_size" value="<?php echo esc_attr(get_option('pt_bg_size','cover')); ?>" class="regular-text" />
          <p class="description"><?php _e('Use CSS values like cover, contain, 1200px auto, or 150%','protech-team'); ?></p></td></tr>
        <tr><th><label for="pt_bg_position"><?php _e('Background Position','protech-team'); ?></label></th>
          <td><input type="text" id="pt_bg_position" name="pt_bg_position" value="<?php echo esc_attr(get_option('pt_bg_position','center center')); ?>" class="regular-text" /></td></tr>
        <tr><th><label for="pt_bg_height"><?php _e('Section Height','protech-team'); ?></label></th>
          <td><input type="text" id="pt_bg_height" name="pt_bg_height" value="<?php echo esc_attr(get_option('pt_bg_height','420px')); ?>" class="regular-text" />
          <p class="description"><?php _e('Any CSS length (e.g., 420px, 60vh).','protech-team'); ?></p></td></tr>
        <tr><th><label for="pt_mask_color"><?php _e('Mask Color','protech-team'); ?></label></th>
          <td><input type="text" id="pt_mask_color" name="pt_mask_color" value="<?php echo esc_attr(get_option('pt_mask_color','#000000')); ?>" class="regular-text" /></td></tr>
        <tr><th><label for="pt_mask_opacity"><?php _e('Mask Opacity','protech-team'); ?></label></th>
          <td><input type="number" step="0.01" min="0" max="1" id="pt_mask_opacity" name="pt_mask_opacity" value="<?php echo esc_attr(get_option('pt_mask_opacity',0.35)); ?>" />
          <p class="description"><?php _e('0 = transparent, 1 = solid.','protech-team'); ?></p></td></tr>
      </table>
      <?php submit_button(); ?>
    </form>
  </div>
  <?php
}

add_action('admin_enqueue_scripts', function($hook){
  if ($hook === 'protech_team_page_protech-team-settings'){
    wp_enqueue_media();
    wp_add_inline_script('jquery-core', "jQuery(function($){ var f; $('#pt_bg_upload').on('click', function(e){ e.preventDefault(); if(f){f.open(); return;} f=wp.media({title:'Select Background',button:{text:'Use this image'},library:{type:'image'},multiple:false}); f.on('select', function(){ var a=f.state().get('selection').first().toJSON(); $('#pt_bg_image').val(a.url); }); f.open(); }); });");
  }
});


add_action('admin_enqueue_scripts', function($hook){
  global $post_type;
  if ( ('post.php' === $hook || 'post-new.php' === $hook) && 'protech_team' === $post_type ){
    wp_enqueue_media();
    wp_enqueue_script('protech-team-admin', PROTECH_TEAM_URL.'assets/admin/media.js', array('jquery'), PROTECH_TEAM_VER, true);
  }
});

add_action('save_post_protech_team', function($post_id){
  if ( ! isset($_POST['protech_team_nonce']) || ! wp_verify_nonce($_POST['protech_team_nonce'], 'protech_team_save') ) return;
  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
  if ( ! current_user_can('edit_post', $post_id) ) return;
  update_post_meta($post_id, '_pt_role', sanitize_text_field($_POST['pt_role'] ?? ''));
  update_post_meta($post_id, '_pt_phone', sanitize_text_field($_POST['pt_phone'] ?? ''));
  update_post_meta($post_id, '_pt_email', sanitize_email($_POST['pt_email'] ?? ''));
  update_post_meta($post_id, '_pt_photo', esc_url_raw($_POST['pt_photo'] ?? ''));
});

/** Front-end assets */
function protech_team_enqueue(){
  wp_enqueue_style('protech-team-style', PROTECH_TEAM_URL . 'assets/css/style.css', array(), PROTECH_TEAM_VER);
  wp_enqueue_script('protech-team-app', PROTECH_TEAM_URL . 'assets/js/app.js', array(), PROTECH_TEAM_VER, true);
}
add_action('wp_enqueue_scripts', 'protech_team_enqueue');
add_action('elementor/frontend/after_enqueue_styles', 'protech_team_enqueue');
add_action('elementor/frontend/after_enqueue_scripts', 'protech_team_enqueue');

/** Shortcode */
add_shortcode('protech_team', function($atts){
  $atts = shortcode_atts(array(
    'count' => -1,
    'order' => 'ASC',
    'orderby' => 'menu_order title',
    'img_size' => '120',
    'bg_image' => '',
    'bg_size' => '',
    'bg_position' => '',
    'bg_height' => '',
    'mask_color' => '',
    'mask_opacity' => ''
  ), $atts, 'protech_team');

  // Fallback to saved options
  $bg_image = $atts['bg_image'] !== '' ? esc_url_raw($atts['bg_image']) : get_option('pt_bg_image','');
  $bg_size = $atts['bg_size'] !== '' ? sanitize_text_field($atts['bg_size']) : get_option('pt_bg_size','cover');
  $bg_position = $atts['bg_position'] !== '' ? sanitize_text_field($atts['bg_position']) : get_option('pt_bg_position','center center');
  $bg_height = $atts['bg_height'] !== '' ? sanitize_text_field($atts['bg_height']) : get_option('pt_bg_height','420px');
  $mask_color = $atts['mask_color'] !== '' ? sanitize_text_field($atts['mask_color']) : get_option('pt_mask_color','#000000');
  $mask_opacity = $atts['mask_opacity'] !== '' ? floatval($atts['mask_opacity']) : floatval(get_option('pt_mask_opacity',0.35));
  if ($mask_opacity < 0) $mask_opacity = 0; if ($mask_opacity > 1) $mask_opacity = 1;

  $img_px = intval($atts['img_size']); if ($img_px < 60) $img_px = 60; if ($img_px > 480) $img_px = 480;

  $q = new WP_Query(array(
    'post_type' => 'protech_team',
    'posts_per_page' => intval($atts['count']),
    'orderby' => $atts['orderby'],
    'order' => $atts['order'],
    'no_found_rows' => true
  ));

  // Inline style with variables for CSS
  $style = sprintf('style="%s%s%s%s--pt-mask:%s;--pt-mask-alpha:%s;"',
    $bg_image ? 'background-image:url('.esc_url($bg_image).');' : '',
    'background-attachment:fixed;',
    'background-size:'.esc_attr($bg_size).';',
    'background-position:'.esc_attr($bg_position).';min-height:'.esc_attr($bg_height).';',
    esc_attr($mask_color),
    esc_attr($mask_opacity)
  );

  ob_start(); ?>
  <div class="pt-team-wrap has-bg" <?php echo $style; ?> role="region" aria-label="Protech Team Carousel">
    <div class="pt-mask-layer" aria-hidden="true"></div>
    <div class="pt-team-track" id="pt-team-track">
      <?php if ($q->have_posts()): while($q->have_posts()): $q->the_post();
        $id = get_the_ID();
        $name = get_the_title();
        $role = get_post_meta($id, '_pt_role', true);
        $phone = get_post_meta($id, '_pt_phone', true);
        $email = get_post_meta($id, '_pt_email', true);
        $photo = get_post_meta($id, '_pt_photo', true);
        $img = $photo ?: ( get_the_post_thumbnail_url($id, 'large') ?: PROTECH_TEAM_URL.'assets/img/avatar-placeholder.png' );
      ?>
        <article class="pt-card" style="--pt-photo-size: <?php echo esc_attr($img_px); ?>px;">
          <div class="pt-photo">
            <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($name); ?>" loading="lazy" decoding="async">
          </div>
          <h3 class="pt-name"><?php echo esc_html($name); ?></h3>
          <?php if ($role): ?><div class="pt-role"><?php echo esc_html($role); ?></div><?php endif; ?>
          <div class="pt-contact">
            <?php if ($phone): ?><a class="pt-phone" href="tel:<?php echo esc_attr(preg_replace('/[^0-9\+]/','',$phone)); ?>"><?php echo esc_html($phone); ?></a><?php endif; ?>
            <?php if ($email): ?><a class="pt-email" href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a><?php endif; ?>
          </div>
        </article>
      <?php endwhile; wp_reset_postdata(); else: ?>
        <div class="pt-empty">Add your first Team Member in <strong>Protech Team</strong> (left admin menu) to populate this carousel.</div>
      <?php endif; ?>
    </div>
    <button class="pt-nav prev" aria-label="Previous">&#10094;</button>
    <button class="pt-nav next" aria-label="Next">&#10095;</button>
    <div class="pt-dots" id="pt-dots" aria-hidden="true"></div>
  </div>
  <?php return ob_get_clean();
});
