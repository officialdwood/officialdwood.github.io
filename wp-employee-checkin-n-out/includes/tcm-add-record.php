<?php
/**
 * Manual Add Time Record handler + reusable saver + AJAX endpoint.
 */
if ( ! defined('ABSPATH') ) { exit; }

if ( ! function_exists('tcm_log') ) {
    function tcm_log( $msg, $ctx = [] ) {
        if ( defined('WP_DEBUG') && WP_DEBUG ) {
            error_log('[timeclock] ' . $msg . ( $ctx ? ' ' . wp_json_encode($ctx) : '' ));
        }
    }
}

if ( ! function_exists('tcm_mar_save_record') ) {
    function tcm_mar_save_record( $args ) {
        global $wpdb;
        $table = $wpdb->prefix . 'tcm_timesheets';

        $user_id  = isset($args['user_id']) ? absint($args['user_id']) : 0;
        $location = isset($args['location']) ? sanitize_text_field($args['location']) : '';
        $cin_raw  = isset($args['clock_in']) ? sanitize_text_field($args['clock_in']) : '';
        $cout_raw = isset($args['clock_out']) ? sanitize_text_field($args['clock_out']) : '';
        $note     = isset($args['note']) ? sanitize_text_field($args['note']) : '';
        $dur_h    = isset($args['duration_hours']) ? floatval($args['duration_hours']) : 0.0;

        if ( ! $user_id || ! get_user_by('id', $user_id) ) {
            return new WP_Error('tcm_bad_user', __('Invalid user.', 'tcm'));
        }

        $in_dt  = tcm_normalize_datetime_input($cin_raw);
        if ( ! $in_dt ) {
            return new WP_Error('tcm_bad_timein', TimeClock\Dates\input_format_hint());
        }

        $is_open_shift = ($cout_raw === '' || $cout_raw === null) && (! $dur_h || $dur_h <= 0);

        $out_dt = null;
        if ( ! $is_open_shift ) {
            $out_dt = tcm_normalize_datetime_input($cout_raw, $in_dt);
            if ( $dur_h && $dur_h > 0 ) {
                $out_dt = (clone $in_dt)->modify( '+' . (int)round($dur_h * HOUR_IN_SECONDS) . ' seconds' );
            } elseif ( ! $out_dt ) {
                return new WP_Error('tcm_need_time', __('Provide Time Out or Duration (hrs).', 'tcm'));
            }

            if ( $out_dt <= $in_dt ) {
                // If the admin enters a time-out that is before time-in, assume it spans to the next day.
                $out_dt = (clone $out_dt)->modify('+1 day');
            }
        }

        $clock_in  = TimeClock\Dates\to_storage($in_dt);
        $clock_out = $out_dt ? TimeClock\Dates\to_storage($out_dt) : null;
        $seconds   = ($out_dt) ? max(0, $out_dt->getTimestamp() - $in_dt->getTimestamp()) : null;
        $minutes   = ($seconds !== null) ? (int) round( $seconds / 60 ) : null;
        $hours     = ($minutes !== null) ? round( $minutes / 60, 4 ) : null;

        $week_start_dt = TimeClock\Dates\week_start($in_dt);
        $week_start    = TimeClock\Dates\to_storage_date($week_start_dt);

        if ( $location !== '' ) {
            $locations = get_user_meta($user_id, 'tcm_locations', true);
            if ( ! is_array($locations) ) { $locations = []; }
            if ( ! in_array($location, $locations, true) ) {
                $locations[] = $location;
                update_user_meta($user_id, 'tcm_locations', $locations);
            }
        }

        $active_open_id = null;
        if ( $is_open_shift ) {
            $active_open_id = $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM {$table} WHERE user_id = %d AND clock_out IS NULL ORDER BY id DESC LIMIT 1",
                $user_id
            ) );
        }

        $existing_id = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$table} WHERE user_id=%d AND clock_in=%s LIMIT 1", $user_id, $clock_in
        ) );

        $id = null;

        if ( $is_open_shift && $active_open_id ) {
            // Adjust an existing open punch (keep it open, just move the start back).
            $sql = $wpdb->prepare(
                "UPDATE {$table} SET clock_in = %s, clock_out = NULL, week_start_date = %s, week_start_date_sun = %s, total_hours = NULL, total_minutes = NULL WHERE id = %d",
                $clock_in, $week_start, $week_start, absint($active_open_id)
            );
            $ok = $wpdb->query( $sql );
            if ( $ok === false ) {
                tcm_log('DB update (open) failed', ['err' => $wpdb->last_error, 'sql' => $wpdb->last_query]);
                return new WP_Error('tcm_db_write', __('Failed to update open record.', 'tcm'));
            }
            $id = (int) $active_open_id;
        } elseif ( $existing_id ) {
            $ok = $wpdb->update(
                $table,
                [
                    'clock_out'           => $clock_out,
                    'week_start_date'     => $week_start,
                    'week_start_date_sun' => $week_start,
                    'total_hours'         => $hours,
                    'total_minutes'       => $minutes,
                ],
                ['id' => absint($existing_id)],
                ['%s','%s','%s','%f','%d'],
                ['%d']
            );
            if ( $ok === false ) {
                tcm_log('DB update failed', ['err' => $wpdb->last_error, 'sql' => $wpdb->last_query]);
                return new WP_Error('tcm_db_write', __('Failed to update record.', 'tcm'));
            }
            $id = (int) $existing_id;
        } else {
            if ( $is_open_shift ) {
                $sql = $wpdb->prepare(
                    "INSERT INTO {$table} (user_id, clock_in, clock_out, week_start_date, week_start_date_sun, total_hours, total_minutes) VALUES (%d, %s, NULL, %s, %s, NULL, NULL)",
                    $user_id, $clock_in, $week_start, $week_start
                );
                $ok = $wpdb->query( $sql );
                if ( $ok === false ) {
                    tcm_log('DB insert (open) failed', ['err' => $wpdb->last_error, 'sql' => $wpdb->last_query]);
                    return new WP_Error('tcm_db_write', __('Failed to save record.', 'tcm'));
                }
                $id = (int) $wpdb->insert_id;
            } else {
                $ok = $wpdb->insert(
                    $table,
                    [
                        'user_id'             => $user_id,
                        'clock_in'            => $clock_in,
                        'clock_out'           => $clock_out,
                        'week_start_date'     => $week_start,
                        'week_start_date_sun' => $week_start,
                        'total_hours'         => $hours,
                        'total_minutes'       => $minutes,
                    ],
                    ['%d','%s','%s','%s','%s','%f','%d']
                );
                if ( ! $ok ) {
                    tcm_log('DB insert failed', ['err' => $wpdb->last_error, 'sql' => $wpdb->last_query]);
                    return new WP_Error('tcm_db_write', __('Failed to save record.', 'tcm'));
                }
                $id = (int) $wpdb->insert_id;
            }
        }

        if ( $note !== '' ) {
            add_user_meta( $user_id, '_tcm_manual_note_' . time(), $note );
        }

        return [
            'id'              => $id,
            'user_id'         => $user_id,
            'clock_in'        => $clock_in,
            'clock_out'       => $clock_out,
            'week_start_date' => $week_start,
            'week_start_date_sun' => $week_start,
            'total_hours'     => $hours,
            'total_minutes'   => $minutes,
            'location'        => $location,
        ];
    }
}

if ( ! function_exists('tcm_mar_handle_add_record') ) {
    function tcm_mar_handle_add_record() {
        if ( ! is_user_logged_in() ) { wp_die( esc_html__('Login required.', 'tcm'), 403 ); }
        if ( ! current_user_can('tcm_access') && ! current_user_can('manage_options') ) { wp_die( esc_html__('Insufficient permissions.', 'tcm'), 403 ); }
        if ( ! isset($_POST['_wpnonce']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash($_POST['_wpnonce']) ), 'tcm_add_record_nonce' ) ) {
            wp_die( esc_html__('Security check failed.', 'tcm'), 403 );
        }

        $row = tcm_mar_save_record([
            'user_id'        => isset($_POST['tcm_user_id']) ? absint( wp_unslash($_POST['tcm_user_id']) ) : 0,
            'location'       => isset($_POST['tcm_location']) ? sanitize_text_field( wp_unslash($_POST['tcm_location']) ) : '',
            'clock_in'       => isset($_POST['tcm_clock_in']) ? sanitize_text_field( wp_unslash($_POST['tcm_clock_in']) ) : '',
            'clock_out'      => isset($_POST['tcm_clock_out']) ? sanitize_text_field( wp_unslash($_POST['tcm_clock_out']) ) : '',
            'duration_hours' => isset($_POST['tcm_duration_hours']) ? floatval( wp_unslash($_POST['tcm_duration_hours']) ) : 0.0,
            'note'           => isset($_POST['tcm_note']) ? sanitize_text_field( wp_unslash($_POST['tcm_note']) ) : '',
        ]);

        $ok = ! is_wp_error($row);
        if ( is_wp_error($row) ) { tcm_log('add_record error', ['error' => $row->get_error_message()]); }

        $dest = wp_get_referer();
        if ( empty($dest) ) { $dest = admin_url('admin.php?page=timeclock-reports'); }
        $dest = add_query_arg(['tcm_added' => $ok ? '1' : '0'], $dest);
        wp_safe_redirect($dest);
        exit;
    }
    add_action('admin_post_tcm_add_record', 'tcm_mar_handle_add_record');
    add_action('admin_post_nopriv_tcm_add_record', 'tcm_mar_handle_add_record');
}

if ( ! function_exists('tcm_ajax_add_manual_record') ) {
    function tcm_ajax_add_manual_record() {
        check_ajax_referer('tcm_add_record_nonce', 'nonce');
        if ( ! current_user_can('tcm_access') && ! current_user_can('manage_options') ) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'tcm')], 403);
        }

        $row = tcm_mar_save_record([
            'user_id'        => isset($_POST['user_id']) ? absint( wp_unslash($_POST['user_id']) ) : 0,
            'location'       => isset($_POST['location']) ? sanitize_text_field( wp_unslash($_POST['location']) ) : '',
            'clock_in'       => isset($_POST['clock_in']) ? sanitize_text_field( wp_unslash($_POST['clock_in']) ) : '',
            'clock_out'      => isset($_POST['clock_out']) ? sanitize_text_field( wp_unslash($_POST['clock_out']) ) : '',
            'duration_hours' => isset($_POST['duration_hours']) ? floatval( wp_unslash($_POST['duration_hours']) ) : 0.0,
            'note'           => isset($_POST['note']) ? sanitize_text_field( wp_unslash($_POST['note']) ) : '',
        ]);

        if ( is_wp_error($row) ) {
            wp_send_json_error(['message' => $row->get_error_message()], 400);
        }
        wp_send_json_success(['record' => $row]);
    }
    add_action('wp_ajax_tcm_add_manual_record', 'tcm_ajax_add_manual_record');
}
