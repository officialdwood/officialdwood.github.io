<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WyoHoops_Shortcodes {
    /** @var WyoHoops_Functions */
    protected $functions;

    public function __construct( WyoHoops_Functions $functions ) {
        $this->functions = $functions;
    }

    public function hooks() {
        add_shortcode( 'wyohoops_team', array( $this, 'render_team_shortcode' ) );
    }

    public function render_team_shortcode( $atts ) {
        $atts = shortcode_atts(
            array(
                'gender' => 'boys',
            ),
            $atts,
            'wyohoops_team'
        );

        $default_gender = in_array( strtolower( $atts['gender'] ), array( 'boys', 'girls' ), true ) ? strtolower( $atts['gender'] ) : 'boys';

        wp_enqueue_style( 'wyohoops-front' );
        wp_enqueue_script( 'wyohoops-front' );

        $options = wp_parse_args( get_option( 'wyohoops_display_options', array() ), array(
            'frame_color'      => '#d4af37',
            'background_color' => '#000000',
            'text_color'       => '#ffffff',
        ) );

        $boys  = $this->functions->get_teams( array( 'gender' => 'boys', 'rank_max' => 5, 'limit' => 5 ) );
        $girls = $this->functions->get_teams( array( 'gender' => 'girls', 'rank_max' => 5, 'limit' => 5 ) );

        ob_start();
        ?>
        <div class="wyohoops-wrapper" style="--wyohoops-frame: <?php echo esc_attr( $options['frame_color'] ); ?>; --wyohoops-bg: <?php echo esc_attr( $options['background_color'] ); ?>; --wyohoops-text: <?php echo esc_attr( $options['text_color'] ); ?>;">
            <div class="wyohoops-toggle">
                <button class="wyohoops-tab <?php echo 'boys' === $default_gender ? 'is-active' : ''; ?>" data-target="boys"><?php esc_html_e( 'Boys', 'wyohoops-team-database' ); ?></button>
                <button class="wyohoops-tab <?php echo 'girls' === $default_gender ? 'is-active' : ''; ?>" data-target="girls"><?php esc_html_e( 'Girls', 'wyohoops-team-database' ); ?></button>
            </div>
            <div class="wyohoops-team-grid" data-gender="boys" style="display: <?php echo 'girls' === $default_gender ? 'none' : 'grid'; ?>;">
                <?php echo $this->render_team_cards( $boys ); ?>
            </div>
            <div class="wyohoops-team-grid" data-gender="girls" style="display: <?php echo 'girls' === $default_gender ? 'grid' : 'none'; ?>;">
                <?php echo $this->render_team_cards( $girls ); ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function render_team_cards( $teams ) {
        if ( empty( $teams ) ) {
            return '<p>' . esc_html__( 'No teams available.', 'wyohoops-team-database' ) . '</p>';
        }

        ob_start();
        foreach ( $teams as $team ) {
            $logo = ! empty( $team['logo_attachment_id'] ) ? wp_get_attachment_image_url( $team['logo_attachment_id'], 'medium' ) : '';
            $rank_display = isset( $team['rank'] ) && '' !== $team['rank'] ? $team['rank'] : '—';
            $rating_display = isset( $team['team_rating'] ) && '' !== $team['team_rating'] ? $team['team_rating'] : '—';
            $def_display = isset( $team['def_rating'] ) && '' !== $team['def_rating'] ? $team['def_rating'] : '—';
            ?>
            <div class="wyohoops-card">
                <div class="wyohoops-card-inner">
                    <div class="wyohoops-logo">
                        <?php if ( $logo ) : ?>
                            <img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $team['school_name'] ); ?>" />
                        <?php else : ?>
                            <div class="wyohoops-placeholder">🏀</div>
                        <?php endif; ?>
                    </div>
                    <div class="wyohoops-meta">
                        <h3><?php echo esc_html( $team['school_name'] ); ?></h3>
                        <p class="wyohoops-subtext"><?php echo esc_html( $team['city'] . ', ' . $team['state'] . ' – Class ' . $team['classification'] ); ?></p>
                        <p class="wyohoops-rank"><?php printf( esc_html__( 'Rank #%s', 'wyohoops-team-database' ), esc_html( $rank_display ) ); ?></p>
                        <div class="wyohoops-ratings">
                            <span><?php printf( esc_html__( 'Rating %s', 'wyohoops-team-database' ), esc_html( $rating_display ) ); ?></span>
                            <span><?php printf( esc_html__( 'Def %s', 'wyohoops-team-database' ), esc_html( $def_display ) ); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        return ob_get_clean();
    }
}
