<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WyoHoops_Activator {
    public static function activate() {
        self::create_tables();
        self::seed_teams();
        self::ensure_defaults();
    }

    public static function uninstall() {
        $remove = get_option( 'wyohoops_remove_on_uninstall', false );
        if ( $remove ) {
            global $wpdb;
            $teams_table   = $wpdb->prefix . 'wyohoops_teams';
            $players_table = $wpdb->prefix . 'wyohoops_players';
            $wpdb->query( "DROP TABLE IF EXISTS {$players_table}" );
            $wpdb->query( "DROP TABLE IF EXISTS {$teams_table}" );
            delete_option( 'wyohoops_db_version' );
            delete_option( 'wyohoops_display_options' );
            delete_option( 'wyohoops_remove_on_uninstall' );
        }
    }

    protected static function create_tables() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();
        $teams_table     = $wpdb->prefix . 'wyohoops_teams';
        $players_table   = $wpdb->prefix . 'wyohoops_players';

        $teams_sql = "CREATE TABLE {$teams_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            school_name varchar(190) NOT NULL,
            city varchar(120) NOT NULL,
            state varchar(2) NOT NULL DEFAULT 'WY',
            classification varchar(3) NOT NULL,
            gender varchar(10) NOT NULL,
            slug varchar(200) NOT NULL,
            logo_attachment_id bigint(20) unsigned NULL,
            primary_color varchar(7) NULL,
            secondary_color varchar(7) NULL,
            rank int(11) NULL,
            team_rating int(11) NULL,
            def_rating int(11) NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY slug (slug),
            KEY classification (classification),
            KEY gender (gender),
            KEY rank (rank)
        ) {$charset_collate};";

        $players_sql = "CREATE TABLE {$players_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            team_id bigint(20) unsigned NOT NULL,
            first_name varchar(120) NOT NULL,
            last_name varchar(120) NOT NULL,
            position varchar(50) NULL,
            grade int(11) NULL,
            height_ft int(11) NULL,
            height_in int(11) NULL,
            player_rating int(11) NULL,
            jersey_number varchar(10) NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY team_id (team_id)
        ) {$charset_collate};";

        dbDelta( $teams_sql );
        dbDelta( $players_sql );

        update_option( 'wyohoops_db_version', WYOHOOPS_DB_VERSION );
    }

    protected static function seed_teams() {
        global $wpdb;

        $teams_table = $wpdb->prefix . 'wyohoops_teams';
        $schools     = self::get_schools();
        $now         = current_time( 'mysql' );

        foreach ( $schools as $school ) {
            foreach ( array( 'boys', 'girls' ) as $gender ) {
                $slug = sanitize_title( $school['school_name'] . '-' . $gender );
                $exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$teams_table} WHERE slug = %s", $slug ) );
                if ( $exists ) {
                    continue;
                }

                $payload = array(
                    'school_name'        => $school['school_name'],
                    'city'               => $school['city'],
                    'state'              => 'WY',
                    'classification'     => $school['classification'],
                    'gender'             => $gender,
                    'slug'               => $slug,
                    'logo_attachment_id' => null,
                    'primary_color'      => '',
                    'secondary_color'    => '',
                    'rank'               => null,
                    'team_rating'        => null,
                    'def_rating'         => null,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                );

                $wpdb->insert(
                    $teams_table,
                    $payload,
                    array( '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%d', '%d', '%s', '%s' )
                );
            }
        }
    }

    protected static function ensure_defaults() {
        $defaults = array(
            'frame_color'      => '#d4af37',
            'background_color' => '#000000',
            'text_color'       => '#ffffff',
        );

        if ( ! get_option( 'wyohoops_display_options' ) ) {
            add_option( 'wyohoops_display_options', $defaults, '', 'no' );
        }
    }

    protected static function get_schools() {
        return array(
            array( 'school_name' => 'Campbell County High School', 'city' => 'Gillette', 'classification' => '4A' ),
            array( 'school_name' => 'Central High School', 'city' => 'Cheyenne', 'classification' => '4A' ),
            array( 'school_name' => 'East High School', 'city' => 'Cheyenne', 'classification' => '4A' ),
            array( 'school_name' => 'Evanston High School', 'city' => 'Evanston', 'classification' => '4A' ),
            array( 'school_name' => 'Green River High School', 'city' => 'Green River', 'classification' => '4A' ),
            array( 'school_name' => 'Jackson Hole High School', 'city' => 'Jackson', 'classification' => '4A' ),
            array( 'school_name' => 'Kelly Walsh High School', 'city' => 'Casper', 'classification' => '4A' ),
            array( 'school_name' => 'Laramie High School', 'city' => 'Laramie', 'classification' => '4A' ),
            array( 'school_name' => 'Natrona County High School', 'city' => 'Casper', 'classification' => '4A' ),
            array( 'school_name' => 'Riverton High School', 'city' => 'Riverton', 'classification' => '4A' ),
            array( 'school_name' => 'Rock Springs High School', 'city' => 'Rock Springs', 'classification' => '4A' ),
            array( 'school_name' => 'Sheridan High School', 'city' => 'Sheridan', 'classification' => '4A' ),
            array( 'school_name' => 'South High School', 'city' => 'Cheyenne', 'classification' => '4A' ),
            array( 'school_name' => 'Star Valley High School', 'city' => 'Afton', 'classification' => '4A' ),
            array( 'school_name' => 'Thunder Basin High School', 'city' => 'Gillette', 'classification' => '4A' ),
            array( 'school_name' => 'Buffalo High School', 'city' => 'Buffalo', 'classification' => '3A' ),
            array( 'school_name' => 'Burns High School', 'city' => 'Burns', 'classification' => '3A' ),
            array( 'school_name' => 'Cody High School', 'city' => 'Cody', 'classification' => '3A' ),
            array( 'school_name' => 'Douglas High School', 'city' => 'Douglas', 'classification' => '3A' ),
            array( 'school_name' => 'Glenrock High School', 'city' => 'Glenrock', 'classification' => '3A' ),
            array( 'school_name' => 'Lander Valley High School', 'city' => 'Lander', 'classification' => '3A' ),
            array( 'school_name' => 'Lovell High School', 'city' => 'Lovell', 'classification' => '3A' ),
            array( 'school_name' => 'Lyman High School', 'city' => 'Lyman', 'classification' => '3A' ),
            array( 'school_name' => 'Mountain View High School', 'city' => 'Mountain View', 'classification' => '3A' ),
            array( 'school_name' => 'Newcastle High School', 'city' => 'Newcastle', 'classification' => '3A' ),
            array( 'school_name' => 'Pinedale High School', 'city' => 'Pinedale', 'classification' => '3A' ),
            array( 'school_name' => 'Powell High School', 'city' => 'Powell', 'classification' => '3A' ),
            array( 'school_name' => 'Rawlins High School', 'city' => 'Rawlins', 'classification' => '3A' ),
            array( 'school_name' => 'Torrington High School', 'city' => 'Torrington', 'classification' => '3A' ),
            array( 'school_name' => 'Wheatland High School', 'city' => 'Wheatland', 'classification' => '3A' ),
            array( 'school_name' => 'Worland High School', 'city' => 'Worland', 'classification' => '3A' ),
            array( 'school_name' => 'Big Horn High School', 'city' => 'Big Horn', 'classification' => '2A' ),
            array( 'school_name' => 'Big Piney High School', 'city' => 'Big Piney', 'classification' => '2A' ),
            array( 'school_name' => 'Greybull High School', 'city' => 'Greybull', 'classification' => '2A' ),
            array( 'school_name' => 'Hot Springs County High School', 'city' => 'Thermopolis', 'classification' => '2A' ),
            array( 'school_name' => 'Kemmerer High School', 'city' => 'Diamondville', 'classification' => '2A' ),
            array( 'school_name' => 'Moorcroft High School', 'city' => 'Moorcroft', 'classification' => '2A' ),
            array( 'school_name' => 'Pine Bluffs High School', 'city' => 'Pine Bluffs', 'classification' => '2A' ),
            array( 'school_name' => 'Rocky Mountain High School', 'city' => 'Cowley', 'classification' => '2A' ),
            array( 'school_name' => 'Shoshoni High School', 'city' => 'Shoshoni', 'classification' => '2A' ),
            array( 'school_name' => 'Sundance High School', 'city' => 'Sundance', 'classification' => '2A' ),
            array( 'school_name' => 'Tongue River High School', 'city' => 'Dayton', 'classification' => '2A' ),
            array( 'school_name' => 'Wind River High School', 'city' => 'Pavillion', 'classification' => '2A' ),
            array( 'school_name' => 'Wright High School', 'city' => 'Wright', 'classification' => '2A' ),
            array( 'school_name' => 'Wyoming Indian High School', 'city' => 'Ethete', 'classification' => '2A' ),
            array( 'school_name' => 'Arvada-Clearmont High School', 'city' => 'Clearmont', 'classification' => '1A' ),
            array( 'school_name' => 'Burlington High School', 'city' => 'Burlington', 'classification' => '1A' ),
            array( 'school_name' => 'Casper Christian School', 'city' => 'Casper', 'classification' => '1A' ),
            array( 'school_name' => 'Cokeville High School', 'city' => 'Cokeville', 'classification' => '1A' ),
            array( 'school_name' => 'Dubois High School', 'city' => 'Dubois', 'classification' => '1A' ),
            array( 'school_name' => 'Encampment K-12 School', 'city' => 'Encampment', 'classification' => '1A' ),
            array( 'school_name' => 'Farson-Eden High School', 'city' => 'Farson', 'classification' => '1A' ),
            array( 'school_name' => 'Ft. Washakie High School', 'city' => 'Ft. Washakie', 'classification' => '1A' ),
            array( 'school_name' => 'Guernsey-Sunrise High School', 'city' => 'Guernsey', 'classification' => '1A' ),
            array( 'school_name' => 'Hanna-Elk Mountain (HEM) High School', 'city' => 'Hanna', 'classification' => '1A' ),
            array( 'school_name' => 'Hulett High School', 'city' => 'Hulett', 'classification' => '1A' ),
            array( 'school_name' => 'Kaycee High School', 'city' => 'Kaycee', 'classification' => '1A' ),
            array( 'school_name' => 'Lingle-Fort Laramie High School', 'city' => 'Lingle', 'classification' => '1A' ),
            array( 'school_name' => 'Little Snake River Valley High School', 'city' => 'Baggs', 'classification' => '1A' ),
            array( 'school_name' => 'Meeteetse High School', 'city' => 'Meeteetse', 'classification' => '1A' ),
            array( 'school_name' => 'Midwest High School', 'city' => 'Midwest', 'classification' => '1A' ),
            array( 'school_name' => 'Niobrara County (Lusk) High School', 'city' => 'Lusk', 'classification' => '1A' ),
            array( 'school_name' => 'Riverside High School', 'city' => 'Basin', 'classification' => '1A' ),
            array( 'school_name' => 'Rock River High School', 'city' => 'Rock River', 'classification' => '1A' ),
            array( 'school_name' => 'Saratoga High School', 'city' => 'Saratoga', 'classification' => '1A' ),
            array( 'school_name' => 'Southeast High School', 'city' => 'Yoder', 'classification' => '1A' ),
            array( 'school_name' => 'St. Stephens Indian School', 'city' => 'St. Stephens', 'classification' => '1A' ),
            array( 'school_name' => 'Ten Sleep High School', 'city' => 'Ten Sleep', 'classification' => '1A' ),
            array( 'school_name' => 'Upton High School', 'city' => 'Upton', 'classification' => '1A' ),
        );
    }
}
