<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WyoHoops_Admin {
    /** @var WyoHoops_Functions */
    protected $functions;

    public function __construct( WyoHoops_Functions $functions ) {
        $this->functions = $functions;
    }

    public function hooks() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        add_action( 'admin_post_wyohoops_save_team', array( $this, 'handle_save_team' ) );
        add_action( 'admin_post_wyohoops_delete_team', array( $this, 'handle_delete_team' ) );
        add_action( 'admin_post_wyohoops_save_player', array( $this, 'handle_save_player' ) );
        add_action( 'admin_post_wyohoops_delete_player', array( $this, 'handle_delete_player' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );
    }

    public function enqueue_admin( $hook ) {
        if ( strpos( $hook, 'wyohoops' ) === false ) {
            return;
        }
        wp_enqueue_style( 'wyohoops-admin', WYOHOOPS_PLUGIN_URL . 'assets/css/wyohoops-front.css', array(), WYOHOOPS_VERSION );
        wp_enqueue_media();
        wp_enqueue_script( 'wyohoops-admin', WYOHOOPS_PLUGIN_URL . 'assets/js/wyohoops-admin.js', array( 'jquery' ), WYOHOOPS_VERSION, true );
    }

    public function register_menu() {
        add_menu_page(
            __( 'WyoHoops', 'wyohoops-team-database' ),
            __( 'WyoHoops', 'wyohoops-team-database' ),
            'manage_options',
            'wyohoops_teams',
            array( $this, 'render_teams_page' ),
            'dashicons-groups',
            26
        );

        add_submenu_page(
            'wyohoops_teams',
            __( 'Teams', 'wyohoops-team-database' ),
            __( 'Teams', 'wyohoops-team-database' ),
            'manage_options',
            'wyohoops_teams',
            array( $this, 'render_teams_page' )
        );
    }

    public function render_teams_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $action  = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
        $team_id = isset( $_GET['team_id'] ) ? absint( $_GET['team_id'] ) : 0;

        echo '<div class="wrap"><h1>' . esc_html__( 'WyoHoops Teams', 'wyohoops-team-database' ) . '</h1>';

        if ( 'edit' === $action && $team_id ) {
            $this->render_edit_team( $team_id );
        } else {
            $this->render_list();
        }

        echo '</div>';
    }

    protected function render_list() {
        $classification = isset( $_GET['classification'] ) ? sanitize_text_field( wp_unslash( $_GET['classification'] ) ) : '';
        $gender         = isset( $_GET['gender'] ) ? sanitize_text_field( wp_unslash( $_GET['gender'] ) ) : '';
        $search         = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

        $teams = $this->functions->get_teams(
            array(
                'classification' => $classification,
                'gender'         => $gender,
                'search'         => $search,
                'limit'          => 500,
                'order_by'       => 'rank',
            )
        );

        ?>
        <form method="get">
            <input type="hidden" name="page" value="wyohoops_teams" />
            <div class="wyohoops-filter-bar">
                <select name="classification">
                    <option value=""><?php esc_html_e( 'All Classes', 'wyohoops-team-database' ); ?></option>
                    <?php foreach ( array( '4A', '3A', '2A', '1A' ) as $class ) : ?>
                        <option value="<?php echo esc_attr( $class ); ?>" <?php selected( $classification, $class ); ?>><?php echo esc_html( $class ); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="gender">
                    <option value=""><?php esc_html_e( 'All Genders', 'wyohoops-team-database' ); ?></option>
                    <option value="boys" <?php selected( $gender, 'boys' ); ?>><?php esc_html_e( 'Boys', 'wyohoops-team-database' ); ?></option>
                    <option value="girls" <?php selected( $gender, 'girls' ); ?>><?php esc_html_e( 'Girls', 'wyohoops-team-database' ); ?></option>
                </select>
                <input type="search" name="s" placeholder="<?php esc_attr_e( 'Search school or city', 'wyohoops-team-database' ); ?>" value="<?php echo esc_attr( $search ); ?>" />
                <button class="button"><?php esc_html_e( 'Filter', 'wyohoops-team-database' ); ?></button>
                <a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=wyohoops_teams&action=edit' ) ); ?>"><?php esc_html_e( 'Add Team', 'wyohoops-team-database' ); ?></a>
            </div>
        </form>
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'School', 'wyohoops-team-database' ); ?></th>
                    <th><?php esc_html_e( 'City', 'wyohoops-team-database' ); ?></th>
                    <th><?php esc_html_e( 'Class', 'wyohoops-team-database' ); ?></th>
                    <th><?php esc_html_e( 'Gender', 'wyohoops-team-database' ); ?></th>
                    <th><?php esc_html_e( 'Rank', 'wyohoops-team-database' ); ?></th>
                    <th><?php esc_html_e( 'Rating', 'wyohoops-team-database' ); ?></th>
                    <th><?php esc_html_e( 'Def Rating', 'wyohoops-team-database' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'wyohoops-team-database' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $teams ) ) : ?>
                    <tr><td colspan="8"><?php esc_html_e( 'No teams found.', 'wyohoops-team-database' ); ?></td></tr>
                <?php else : ?>
                    <?php foreach ( $teams as $team ) : ?>
                        <tr>
                            <td><?php echo esc_html( $team['school_name'] ); ?></td>
                            <td><?php echo esc_html( $team['city'] ); ?></td>
                            <td><?php echo esc_html( $team['classification'] ); ?></td>
                            <td><?php echo esc_html( ucfirst( $team['gender'] ) ); ?></td>
                            <td><?php echo esc_html( $team['rank'] ); ?></td>
                            <td><?php echo esc_html( $team['team_rating'] ); ?></td>
                            <td><?php echo esc_html( $team['def_rating'] ); ?></td>
                            <td>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wyohoops_teams&action=edit&team_id=' . absint( $team['id'] ) ) ); ?>"><?php esc_html_e( 'Edit', 'wyohoops-team-database' ); ?></a>
                                |
                                <form style="display:inline" class="wyohoops-delete-form" data-confirm="<?php echo esc_attr( __( 'Delete this team and its roster?', 'wyohoops-team-database' ) ); ?>" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                                    <?php wp_nonce_field( 'wyohoops_delete_team' ); ?>
                                    <input type="hidden" name="action" value="wyohoops_delete_team" />
                                    <input type="hidden" name="team_id" value="<?php echo esc_attr( $team['id'] ); ?>" />
                                    <button type="submit" class="link-delete"><?php esc_html_e( 'Delete', 'wyohoops-team-database' ); ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
    }

    protected function render_edit_team( $team_id ) {
        $team = $team_id ? $this->functions->get_team( $team_id ) : null;
        $roster = $team ? $this->functions->get_roster( $team['id'] ) : array();
        ?>
        <h2><?php echo $team ? esc_html__( 'Edit Team', 'wyohoops-team-database' ) : esc_html__( 'Add Team', 'wyohoops-team-database' ); ?></h2>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="wyohoops-card" style="max-width:900px;">
            <?php wp_nonce_field( 'wyohoops_save_team' ); ?>
            <input type="hidden" name="action" value="wyohoops_save_team" />
            <input type="hidden" name="team_id" value="<?php echo esc_attr( $team ? $team['id'] : 0 ); ?>" />
            <table class="form-table">
                <tr>
                    <th><label for="school_name"><?php esc_html_e( 'School Name', 'wyohoops-team-database' ); ?></label></th>
                    <td><input type="text" name="school_name" id="school_name" class="regular-text" required value="<?php echo esc_attr( $team['school_name'] ?? '' ); ?>" /></td>
                </tr>
                <tr>
                    <th><label for="city"><?php esc_html_e( 'City', 'wyohoops-team-database' ); ?></label></th>
                    <td><input type="text" name="city" id="city" class="regular-text" required value="<?php echo esc_attr( $team['city'] ?? '' ); ?>" /></td>
                </tr>
                <tr>
                    <th><label for="state"><?php esc_html_e( 'State', 'wyohoops-team-database' ); ?></label></th>
                    <td><input type="text" name="state" id="state" value="<?php echo esc_attr( $team['state'] ?? 'WY' ); ?>" maxlength="2" /></td>
                </tr>
                <tr>
                    <th><label for="classification"><?php esc_html_e( 'Classification', 'wyohoops-team-database' ); ?></label></th>
                    <td>
                        <select name="classification" id="classification" required>
                            <?php foreach ( array( '4A', '3A', '2A', '1A' ) as $class ) : ?>
                                <option value="<?php echo esc_attr( $class ); ?>" <?php selected( $team['classification'] ?? '', $class ); ?>><?php echo esc_html( $class ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="gender"><?php esc_html_e( 'Gender', 'wyohoops-team-database' ); ?></label></th>
                    <td>
                        <select name="gender" id="gender" required>
                            <option value="boys" <?php selected( $team['gender'] ?? '', 'boys' ); ?>><?php esc_html_e( 'Boys', 'wyohoops-team-database' ); ?></option>
                            <option value="girls" <?php selected( $team['gender'] ?? '', 'girls' ); ?>><?php esc_html_e( 'Girls', 'wyohoops-team-database' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="logo_attachment_id"><?php esc_html_e( 'Logo', 'wyohoops-team-database' ); ?></label></th>
                    <td>
                        <input type="hidden" name="logo_attachment_id" id="logo_attachment_id" value="<?php echo esc_attr( $team['logo_attachment_id'] ?? '' ); ?>" />
                        <button type="button" class="button wyohoops-media-upload" data-target="logo_attachment_id"><?php esc_html_e( 'Choose Logo', 'wyohoops-team-database' ); ?></button>
                        <div class="wyohoops-logo-preview" style="margin-top:10px;">
                            <?php if ( ! empty( $team['logo_attachment_id'] ) ) : ?>
                                <?php echo wp_get_attachment_image( $team['logo_attachment_id'], array( 64, 64 ) ); ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><label for="primary_color"><?php esc_html_e( 'Primary Color', 'wyohoops-team-database' ); ?></label></th>
                    <td><input type="color" name="primary_color" id="primary_color" value="<?php echo esc_attr( $team['primary_color'] ?? '#d4af37' ); ?>" /></td>
                </tr>
                <tr>
                    <th><label for="secondary_color"><?php esc_html_e( 'Secondary Color', 'wyohoops-team-database' ); ?></label></th>
                    <td><input type="color" name="secondary_color" id="secondary_color" value="<?php echo esc_attr( $team['secondary_color'] ?? '#000000' ); ?>" /></td>
                </tr>
                <tr>
                    <th><label for="rank"><?php esc_html_e( 'Rank', 'wyohoops-team-database' ); ?></label></th>
                    <td><input type="number" name="rank" id="rank" min="1" max="999" value="<?php echo esc_attr( $team['rank'] ?? '' ); ?>" /></td>
                </tr>
                <tr>
                    <th><label for="team_rating"><?php esc_html_e( 'Team Rating', 'wyohoops-team-database' ); ?></label></th>
                    <td><input type="number" name="team_rating" id="team_rating" min="0" max="100" value="<?php echo esc_attr( $team['team_rating'] ?? '' ); ?>" /></td>
                </tr>
                <tr>
                    <th><label for="def_rating"><?php esc_html_e( 'Def Rating', 'wyohoops-team-database' ); ?></label></th>
                    <td><input type="number" name="def_rating" id="def_rating" min="0" max="100" value="<?php echo esc_attr( $team['def_rating'] ?? '' ); ?>" /></td>
                </tr>
            </table>
            <?php submit_button( $team ? __( 'Update Team', 'wyohoops-team-database' ) : __( 'Create Team', 'wyohoops-team-database' ) ); ?>
        </form>

        <?php if ( $team ) : ?>
            <h2><?php esc_html_e( 'Roster', 'wyohoops-team-database' ); ?></h2>
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( '#', 'wyohoops-team-database' ); ?></th>
                        <th><?php esc_html_e( 'Name', 'wyohoops-team-database' ); ?></th>
                        <th><?php esc_html_e( 'Position', 'wyohoops-team-database' ); ?></th>
                        <th><?php esc_html_e( 'Grade', 'wyohoops-team-database' ); ?></th>
                        <th><?php esc_html_e( 'Height', 'wyohoops-team-database' ); ?></th>
                        <th><?php esc_html_e( 'Rating', 'wyohoops-team-database' ); ?></th>
                        <th><?php esc_html_e( 'Actions', 'wyohoops-team-database' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $roster ) ) : ?>
                        <tr><td colspan="7"><?php esc_html_e( 'No players yet.', 'wyohoops-team-database' ); ?></td></tr>
                    <?php else : ?>
                    <?php foreach ( $roster as $player ) : ?>
                        <?php
                        $height_ft      = isset( $player['height_ft'] ) ? absint( $player['height_ft'] ) : 0;
                        $height_in      = isset( $player['height_in'] ) ? absint( $player['height_in'] ) : 0;
                        if ( $height_ft > 0 ) {
                            $height_display = $height_in > 0 ? sprintf( "%d' %d\"", $height_ft, $height_in ) : sprintf( "%d'", $height_ft );
                        } elseif ( $height_in > 0 ) {
                            $height_display = sprintf( '%d"', $height_in );
                        } else {
                            $height_display = '—';
                        }
                        ?>
                        <tr>
                            <td><?php echo esc_html( $player['jersey_number'] ); ?></td>
                            <td><?php echo esc_html( trim( $player['first_name'] . ' ' . $player['last_name'] ) ); ?></td>
                            <td><?php echo esc_html( $player['position'] ); ?></td>
                            <td><?php echo esc_html( $player['grade'] ); ?></td>
                            <td><?php echo esc_html( $height_display ); ?></td>
                            <td><?php echo esc_html( $player['player_rating'] ); ?></td>
                            <td>
                                    <a href="#" class="wyohoops-toggle-player" data-target="player-<?php echo esc_attr( $player['id'] ); ?>"><?php esc_html_e( 'Edit', 'wyohoops-team-database' ); ?></a> |
                                    <form style="display:inline" class="wyohoops-delete-form" data-confirm="<?php echo esc_attr( __( 'Delete this player?', 'wyohoops-team-database' ) ); ?>" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                                        <?php wp_nonce_field( 'wyohoops_delete_player' ); ?>
                                        <input type="hidden" name="action" value="wyohoops_delete_player" />
                                        <input type="hidden" name="player_id" value="<?php echo esc_attr( $player['id'] ); ?>" />
                                        <input type="hidden" name="team_id" value="<?php echo esc_attr( $team['id'] ); ?>" />
                                        <button class="link-delete" type="submit"><?php esc_html_e( 'Delete', 'wyohoops-team-database' ); ?></button>
                                    </form>
                                </td>
                            </tr>
                            <tr id="player-<?php echo esc_attr( $player['id'] ); ?>" style="display:none;">
                                <td colspan="7">
                                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="wyohoops-inline-form">
                                        <?php wp_nonce_field( 'wyohoops_save_player' ); ?>
                                        <input type="hidden" name="action" value="wyohoops_save_player" />
                                        <input type="hidden" name="player_id" value="<?php echo esc_attr( $player['id'] ); ?>" />
                                        <input type="hidden" name="team_id" value="<?php echo esc_attr( $team['id'] ); ?>" />
                                        <label><?php esc_html_e( '#', 'wyohoops-team-database' ); ?> <input type="text" name="jersey_number" value="<?php echo esc_attr( $player['jersey_number'] ); ?>" /></label>
                                        <label><?php esc_html_e( 'First', 'wyohoops-team-database' ); ?> <input type="text" name="first_name" value="<?php echo esc_attr( $player['first_name'] ); ?>" /></label>
                                        <label><?php esc_html_e( 'Last', 'wyohoops-team-database' ); ?> <input type="text" name="last_name" value="<?php echo esc_attr( $player['last_name'] ); ?>" /></label>
                                        <label><?php esc_html_e( 'Pos', 'wyohoops-team-database' ); ?> <input type="text" name="position" value="<?php echo esc_attr( $player['position'] ); ?>" /></label>
                                        <label><?php esc_html_e( 'Grade', 'wyohoops-team-database' ); ?> <input type="number" name="grade" min="9" max="12" value="<?php echo esc_attr( $player['grade'] ); ?>" /></label>
                                        <label><?php esc_html_e( 'Height ft', 'wyohoops-team-database' ); ?> <input type="number" name="height_ft" min="4" max="8" value="<?php echo esc_attr( $player['height_ft'] ); ?>" /></label>
                                        <label><?php esc_html_e( 'in', 'wyohoops-team-database' ); ?> <input type="number" name="height_in" min="0" max="11" value="<?php echo esc_attr( $player['height_in'] ); ?>" /></label>
                                        <label><?php esc_html_e( 'Rating', 'wyohoops-team-database' ); ?> <input type="number" name="player_rating" min="0" max="100" value="<?php echo esc_attr( $player['player_rating'] ); ?>" /></label>
                                        <button class="button button-primary" type="submit"><?php esc_html_e( 'Save Player', 'wyohoops-team-database' ); ?></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <h3><?php esc_html_e( 'Add Player', 'wyohoops-team-database' ); ?></h3>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="wyohoops-inline-form">
                <?php wp_nonce_field( 'wyohoops_save_player' ); ?>
                <input type="hidden" name="action" value="wyohoops_save_player" />
                <input type="hidden" name="team_id" value="<?php echo esc_attr( $team['id'] ); ?>" />
                <label><?php esc_html_e( '#', 'wyohoops-team-database' ); ?> <input type="text" name="jersey_number" /></label>
                <label><?php esc_html_e( 'First', 'wyohoops-team-database' ); ?> <input type="text" name="first_name" required /></label>
                <label><?php esc_html_e( 'Last', 'wyohoops-team-database' ); ?> <input type="text" name="last_name" required /></label>
                <label><?php esc_html_e( 'Pos', 'wyohoops-team-database' ); ?> <input type="text" name="position" /></label>
                <label><?php esc_html_e( 'Grade', 'wyohoops-team-database' ); ?> <input type="number" name="grade" min="9" max="12" /></label>
                <label><?php esc_html_e( 'Height ft', 'wyohoops-team-database' ); ?> <input type="number" name="height_ft" min="4" max="8" /></label>
                <label><?php esc_html_e( 'in', 'wyohoops-team-database' ); ?> <input type="number" name="height_in" min="0" max="11" /></label>
                <label><?php esc_html_e( 'Rating', 'wyohoops-team-database' ); ?> <input type="number" name="player_rating" min="0" max="100" /></label>
                <button class="button button-primary" type="submit"><?php esc_html_e( 'Add Player', 'wyohoops-team-database' ); ?></button>
            </form>
        <?php endif; ?>
        <?php
    }

    public function handle_save_team() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Not allowed', 'wyohoops-team-database' ) );
        }
        check_admin_referer( 'wyohoops_save_team' );

        $team_id = isset( $_POST['team_id'] ) ? absint( $_POST['team_id'] ) : 0;

        $data = array(
            'id'                 => $team_id,
            'school_name'        => sanitize_text_field( wp_unslash( $_POST['school_name'] ?? '' ) ),
            'city'               => sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) ),
            'state'              => sanitize_text_field( wp_unslash( $_POST['state'] ?? 'WY' ) ),
            'classification'     => sanitize_text_field( wp_unslash( $_POST['classification'] ?? '' ) ),
            'gender'             => sanitize_text_field( wp_unslash( $_POST['gender'] ?? '' ) ),
            'logo_attachment_id' => absint( $_POST['logo_attachment_id'] ?? 0 ),
            'primary_color'      => sanitize_hex_color( wp_unslash( $_POST['primary_color'] ?? '' ) ),
            'secondary_color'    => sanitize_hex_color( wp_unslash( $_POST['secondary_color'] ?? '' ) ),
            'rank'               => isset( $_POST['rank'] ) ? absint( $_POST['rank'] ) : null,
            'team_rating'        => isset( $_POST['team_rating'] ) ? absint( $_POST['team_rating'] ) : null,
            'def_rating'         => isset( $_POST['def_rating'] ) ? absint( $_POST['def_rating'] ) : null,
        );

        $saved = $this->functions->save_team( $data );

        $redirect = add_query_arg(
            array(
                'page'    => 'wyohoops_teams',
                'action'  => 'edit',
                'team_id' => $team_id ?: $saved,
                'updated' => 'true',
            ),
            admin_url( 'admin.php' )
        );
        wp_safe_redirect( $redirect );
        exit;
    }

    public function handle_delete_team() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Not allowed', 'wyohoops-team-database' ) );
        }
        check_admin_referer( 'wyohoops_delete_team' );

        $team_id = isset( $_POST['team_id'] ) ? absint( $_POST['team_id'] ) : 0;
        if ( $team_id ) {
            $this->functions->delete_team( $team_id );
        }

        wp_safe_redirect( admin_url( 'admin.php?page=wyohoops_teams' ) );
        exit;
    }

    public function handle_save_player() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Not allowed', 'wyohoops-team-database' ) );
        }
        check_admin_referer( 'wyohoops_save_player' );

        $player_id = isset( $_POST['player_id'] ) ? absint( $_POST['player_id'] ) : 0;
        $team_id   = isset( $_POST['team_id'] ) ? absint( $_POST['team_id'] ) : 0;

        $data = array(
            'id'            => $player_id,
            'team_id'       => $team_id,
            'first_name'    => sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) ),
            'last_name'     => sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) ),
            'position'      => sanitize_text_field( wp_unslash( $_POST['position'] ?? '' ) ),
            'grade'         => isset( $_POST['grade'] ) ? absint( $_POST['grade'] ) : null,
            'height_ft'     => isset( $_POST['height_ft'] ) ? absint( $_POST['height_ft'] ) : null,
            'height_in'     => isset( $_POST['height_in'] ) ? absint( $_POST['height_in'] ) : null,
            'player_rating' => isset( $_POST['player_rating'] ) ? absint( $_POST['player_rating'] ) : null,
            'jersey_number' => sanitize_text_field( wp_unslash( $_POST['jersey_number'] ?? '' ) ),
        );

        $this->functions->save_player( $data );

        wp_safe_redirect( admin_url( 'admin.php?page=wyohoops_teams&action=edit&team_id=' . $team_id ) );
        exit;
    }

    public function handle_delete_player() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Not allowed', 'wyohoops-team-database' ) );
        }
        check_admin_referer( 'wyohoops_delete_player' );

        $player_id = isset( $_POST['player_id'] ) ? absint( $_POST['player_id'] ) : 0;
        $team_id   = isset( $_POST['team_id'] ) ? absint( $_POST['team_id'] ) : 0;

        if ( $player_id ) {
            $this->functions->delete_player( $player_id );
        }

        wp_safe_redirect( admin_url( 'admin.php?page=wyohoops_teams&action=edit&team_id=' . $team_id ) );
        exit;
    }
}
