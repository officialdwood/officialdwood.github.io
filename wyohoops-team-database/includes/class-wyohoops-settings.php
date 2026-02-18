<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WyoHoops_Settings {
    /** @var WyoHoops_Functions */
    protected $functions;

    public function __construct( WyoHoops_Functions $functions ) {
        $this->functions = $functions;
    }

    public function hooks() {
        add_action( 'admin_menu', array( $this, 'register_page' ) );
        add_action( 'admin_post_wyohoops_save_settings', array( $this, 'save_settings' ) );
    }

    public function register_page() {
        add_submenu_page(
            'wyohoops_teams',
            __( 'Settings', 'wyohoops-team-database' ),
            __( 'Settings', 'wyohoops-team-database' ),
            'manage_options',
            'wyohoops_settings',
            array( $this, 'render_settings' )
        );
    }

    public function render_settings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $options = wp_parse_args( get_option( 'wyohoops_display_options', array() ), array(
            'frame_color'      => '#d4af37',
            'background_color' => '#000000',
            'text_color'       => '#ffffff',
        ) );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'WyoHoops Settings', 'wyohoops-team-database' ); ?></h1>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="wyohoops-card" style="max-width:600px;">
                <?php wp_nonce_field( 'wyohoops_save_settings' ); ?>
                <input type="hidden" name="action" value="wyohoops_save_settings" />
                <table class="form-table">
                    <tr>
                        <th><label for="frame_color"><?php esc_html_e( 'Frame Color', 'wyohoops-team-database' ); ?></label></th>
                        <td><input type="color" name="frame_color" id="frame_color" value="<?php echo esc_attr( $options['frame_color'] ); ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="background_color"><?php esc_html_e( 'Background Color', 'wyohoops-team-database' ); ?></label></th>
                        <td><input type="color" name="background_color" id="background_color" value="<?php echo esc_attr( $options['background_color'] ); ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="text_color"><?php esc_html_e( 'Text Color', 'wyohoops-team-database' ); ?></label></th>
                        <td><input type="color" name="text_color" id="text_color" value="<?php echo esc_attr( $options['text_color'] ); ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="wyohoops_remove_on_uninstall"><?php esc_html_e( 'Remove data on uninstall', 'wyohoops-team-database' ); ?></label></th>
                        <td>
                            <label>
                                <input type="checkbox" name="wyohoops_remove_on_uninstall" id="wyohoops_remove_on_uninstall" value="1" <?php checked( get_option( 'wyohoops_remove_on_uninstall', false ), true ); ?> />
                                <?php esc_html_e( 'Delete custom tables and options when the plugin is deleted.', 'wyohoops-team-database' ); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                <?php submit_button( __( 'Save Settings', 'wyohoops-team-database' ) ); ?>
            </form>
        </div>
        <?php
    }

    public function save_settings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Not allowed', 'wyohoops-team-database' ) );
        }
        check_admin_referer( 'wyohoops_save_settings' );

        $options = array(
            'frame_color'      => sanitize_hex_color( $_POST['frame_color'] ?? '#d4af37' ),
            'background_color' => sanitize_hex_color( $_POST['background_color'] ?? '#000000' ),
            'text_color'       => sanitize_hex_color( $_POST['text_color'] ?? '#ffffff' ),
        );

        update_option( 'wyohoops_display_options', $options );
        update_option( 'wyohoops_remove_on_uninstall', isset( $_POST['wyohoops_remove_on_uninstall'] ) );

        wp_safe_redirect( admin_url( 'admin.php?page=wyohoops_settings&updated=true' ) );
        exit;
    }
}
