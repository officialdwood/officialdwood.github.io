<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class TCM_Clock_Handler
 *
 * Handles clock in/out actions, session checks, and hours updates.
 */

if (!class_exists('TCM_Clock_Handler')) {
    class TCM_Clock_Handler
    {

        public function __construct()
        {
            add_action('wp_ajax_tcm_clock_action', [$this, 'handle_clock_action']);
            add_action('wp_ajax_tcm_check_session', [$this, 'check_active_session']);
            add_action('wp_ajax_tcm_update_hours', [$this, 'update_hours']);
            add_action('wp_ajax_tcm_delete_record', [$this, 'delete_record']);
            add_action('wp_ajax_tcm_get_server_time', [$this, 'get_server_time']);
            register_activation_hook(TCM_PLUGIN_FILE, [$this, 'create_table']);
        }

        public function handle_clock_action()
        {
            $user_id = get_current_user_id();
            $action = sanitize_text_field($_POST['clock_action']);
            global $wpdb;
            $table = $wpdb->prefix . 'tcm_timesheets';

            if ($action === 'clock_in') {
                // Check if user already has an active session (clock_in without clock_out)
                $active_session = $wpdb->get_row($wpdb->prepare("
                    SELECT id, clock_in FROM $table 
                    WHERE user_id = %d 
                    AND clock_out IS NULL 
                    ORDER BY id DESC LIMIT 1
                ", $user_id));

                if ($active_session) {
                    wp_send_json_error('You already have an active session. Please clock out first.');
                    return;
                }

                // Create new clock in entry
                $now_local = TimeClock\Dates\now();
                $clock_in = TimeClock\Dates\to_storage($now_local);
                $week_start = TimeClock\Dates\to_storage_date(TimeClock\Dates\week_start($now_local));

                $result = $wpdb->insert($table, [
                    'user_id' => $user_id,
                    'clock_in' => $clock_in,
                    'clock_out' => null,
                    'week_start_date' => $week_start,
                    'week_start_date_sun' => $week_start,
                    'created_at' => $clock_in,
                ]);

                if ($result) {
                    wp_send_json_success([
                        'message' => 'Clocked in at ' . TimeClock\Dates\fmt_storage($clock_in),
                        'clock_in' => $clock_in,
                        'session_id' => $wpdb->insert_id
                    ]);
                } else {
                    wp_send_json_error('Failed to clock in. Please try again.');
                }
                
            } elseif ($action === 'clock_out') {
                // Find the most recent active session (clock_in without clock_out)
                $active_session = $wpdb->get_row($wpdb->prepare("
                    SELECT id, clock_in FROM $table 
                    WHERE user_id = %d AND clock_out IS NULL 
                    ORDER BY id DESC LIMIT 1
                ", $user_id));

                if (!$active_session) {
                    wp_send_json_error('No active session found. Please clock in first.');
                    return;
                }

                $now_local = TimeClock\Dates\now();
                $clock_out = TimeClock\Dates\to_storage($now_local);
                $clock_in_dt = TimeClock\Dates\parse_storage($active_session->clock_in);

                $seconds_worked = 0;
                if ($clock_in_dt) {
                    $seconds_worked = max(0, $now_local->getTimestamp() - $clock_in_dt->getTimestamp());
                }

                $worked_minutes = (int) round($seconds_worked / 60);
                $worked_hours = round($worked_minutes / 60, 4);
                $week_start_dt = TimeClock\Dates\week_start($clock_in_dt ?: TimeClock\Dates\now());

                // Update the active session with clock out time and duration
                $result = $wpdb->update($table, [
                    'clock_out' => $clock_out,
                    'total_hours' => $worked_hours,
                    'total_minutes' => $worked_minutes,
                    'week_start_date' => TimeClock\Dates\to_storage_date($week_start_dt),
                    'week_start_date_sun' => TimeClock\Dates\to_storage_date($week_start_dt),
                ], ['id' => $active_session->id]);

                if ($result !== false) {
                    wp_send_json_success([
                        'message' => 'Clocked out at ' . TimeClock\Dates\fmt_storage($clock_out) .
                                   ' (Duration: ' . tcm_format_hours($worked_hours) . ')',
                        'clock_out' => $clock_out,
                        'duration' => round($worked_hours, 2),
                        'session_id' => $active_session->id
                    ]);
                } else {
                    wp_send_json_error('Failed to clock out. Please try again.');
                }
            }

            wp_send_json_error('Invalid action');
        }

        public 
function update_hours()
        {
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tcm_update_hours_nonce')) {
                wp_send_json_error('Security check failed');
                return;
            }

            if (!current_user_can('tcm_access') && !current_user_can('manage_options')) {
                wp_send_json_error('Not allowed');
                return;
            }

            $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
            if (!$id) {
                wp_send_json_error('Invalid request');
                return;
            }

            global $wpdb;
            $table = $wpdb->prefix . 'tcm_timesheets';

            $time_in_raw  = isset($_POST['time_in']) ? sanitize_text_field(wp_unslash($_POST['time_in'])) : '';
            $time_out_raw = isset($_POST['time_out']) ? sanitize_text_field(wp_unslash($_POST['time_out'])) : '';
            $total_hours_raw = (isset($_POST['total_hours']) && $_POST['total_hours'] !== '') ? floatval(wp_unslash($_POST['total_hours'])) : null;
            $total_minutes_raw = (isset($_POST['total_minutes']) && $_POST['total_minutes'] !== '') ? intval(wp_unslash($_POST['total_minutes'])) : null;
            $hours_part_raw = (isset($_POST['hours_part']) && $_POST['hours_part'] !== '') ? intval(wp_unslash($_POST['hours_part'])) : null;
            $minutes_part_raw = (isset($_POST['minutes_part']) && $_POST['minutes_part'] !== '') ? intval(wp_unslash($_POST['minutes_part'])) : null;

            $record = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id));
            if (!$record) {
                wp_send_json_error('Record not found');
                return;
            }

                $existing_in  = TimeClock\Dates\parse_storage($record->clock_in);
                $existing_out = TimeClock\Dates\parse_storage($record->clock_out);
                $in_dt  = $existing_in;
                $out_dt = $existing_out;
                $had_clock_out = ($existing_out !== null);
                $had_duration  = ($record->total_minutes !== null);

            $update_data = [];
            $update_formats = [];

            $assign = function ($field, $value, $format) use (&$update_data, &$update_formats) {
                $update_data[$field] = $value;
                $update_formats[$field] = $format;
            };

            if ($time_in_raw !== '') {
                $parsed_in = tcm_normalize_datetime_input($time_in_raw, $existing_in ?: TimeClock\Dates\now());
                if (!$parsed_in) {
                    wp_send_json_error(TimeClock\Dates\input_format_hint());
                    return;
                }
                $in_dt = $parsed_in;
                $assign('clock_in', TimeClock\Dates\to_storage($in_dt), '%s');
            }

            if ($time_out_raw !== '') {
                $parsed_out = tcm_normalize_datetime_input($time_out_raw, $existing_out ?: $in_dt ?: TimeClock\Dates\now());
                if (!$parsed_out) {
                    wp_send_json_error(TimeClock\Dates\input_format_hint());
                    return;
                }
                $out_dt = $parsed_out;
                $assign('clock_out', TimeClock\Dates\to_storage($out_dt), '%s');
            }

            $minutes_from_parts = null;
            if ($hours_part_raw !== null || $minutes_part_raw !== null) {
                $hours_part = max(0, (int) $hours_part_raw);
                $minutes_part = max(0, min(59, (int) $minutes_part_raw));
                $minutes_from_parts = ($hours_part * 60) + $minutes_part;
            }

            if ($total_minutes_raw !== null) {
                $minutes_from_parts = max(0, (int) $total_minutes_raw);
            } elseif ($total_hours_raw !== null && $minutes_from_parts === null) {
                $minutes_from_parts = max(0, (int) round($total_hours_raw * 60));
            }

                if ($minutes_from_parts !== null && $time_out_raw === '') {
                    $should_infer_clock_out = $had_clock_out || $had_duration || $minutes_from_parts > 0;
                    if ($should_infer_clock_out) {
                        if (!$in_dt) {
                            wp_send_json_error('Cannot update duration without a clock-in time.');
                            return;
                        }
                        $out_dt = $in_dt->modify('+' . $minutes_from_parts . ' minutes');
                        $assign('clock_out', TimeClock\Dates\to_storage($out_dt), '%s');
                    } else {
                        $out_dt = null;
                    }
            }

            if ($in_dt && $out_dt && $out_dt <= $in_dt) {
                $out_dt = $out_dt->modify('+1 day');
                $assign('clock_out', TimeClock\Dates\to_storage($out_dt), '%s');
            }

            $computed_minutes = null;
            if ($in_dt && $out_dt) {
                $seconds_diff = max(0, $out_dt->getTimestamp() - $in_dt->getTimestamp());
                $computed_minutes = (int) round($seconds_diff / 60);
            } elseif ($minutes_from_parts !== null) {
                $computed_minutes = $minutes_from_parts;
            }

            if ($computed_minutes === null && $total_hours_raw !== null) {
                $computed_minutes = max(0, (int) round($total_hours_raw * 60));
            }

            if ($computed_minutes !== null) {
                $computed_minutes = max(0, $computed_minutes);
                $assign('total_minutes', $computed_minutes, '%d');
                $assign('total_hours', round($computed_minutes / 60, 4), '%f');
            }

            if ($in_dt) {
                $week_start = TimeClock\Dates\to_storage_date(TimeClock\Dates\week_start($in_dt));
                $assign('week_start_date', $week_start, '%s');
                $assign('week_start_date_sun', $week_start, '%s');
            }

            if (empty($update_data)) {
                wp_send_json_error('Nothing to update');
                return;
            }

            $updated = $wpdb->update($table, $update_data, ['id' => $id], array_values($update_formats), ['%d']);
            if ($updated === false) {
                wp_send_json_error('DB Error');
                return;
            }

            $response_data = ['message' => 'Updated'];
            if (isset($update_data['clock_in'])) {
                $response_data['new_clock_in'] = TimeClock\Dates\fmt_storage($update_data['clock_in']);
            }
            if (isset($update_data['clock_out'])) {
                $response_data['new_clock_out'] = TimeClock\Dates\fmt_storage($update_data['clock_out']);
            }
            if (isset($update_data['total_minutes'])) {
                $response_data['total_minutes'] = $update_data['total_minutes'];
                $response_data['total_hours'] = round($update_data['total_minutes'] / 60, 2);
            }

            wp_send_json_success($response_data);
        }


        public function delete_record(){
            // Reuse update-hours nonce and capability
            check_ajax_referer('tcm_update_hours_nonce','nonce');
            if (!current_user_can('tcm_access') && !current_user_can('manage_options')){
                wp_send_json_error('Not allowed',403); return;
            }
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            if (!$id){ wp_send_json_error('Invalid id',400); return; }
            global $wpdb;
            $table = $wpdb->prefix . 'tcm_timesheets';
            $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(1) FROM {$table} WHERE id = %d", $id));
            if (!$exists){ wp_send_json_error('Record not found',404); return; }
            $deleted = $wpdb->delete($table, ['id'=>$id], ['%d']);
            if ($deleted===false){ wp_send_json_error('Delete failed',500); return; }
            wp_send_json_success(['deleted'=>$id]);
        }
        public function check_active_session()
        {
            $user_id = get_current_user_id();
            if (!$user_id) {
                wp_send_json_error('Not logged in');
            }

            global $wpdb;
            $table = $wpdb->prefix . 'tcm_timesheets';

            $row = $wpdb->get_row($wpdb->prepare("
        SELECT clock_in FROM $table 
        WHERE user_id = %d AND clock_out IS NULL 
        ORDER BY id DESC LIMIT 1", $user_id));

            if ($row) {
                wp_send_json_success(['clock_in' => TimeClock\Dates\fmt_storage($row->clock_in)]);
            } else {
                wp_send_json_success(['clock_in' => null]);
            }
        }

        public function get_server_time()
        {
            $user_id = get_current_user_id();
            if (!$user_id) {
                wp_send_json_error('Not logged in');
            }

            // Return current server time in WordPress timezone
            wp_send_json_success([
                'server_time' => TimeClock\Dates\fmt(TimeClock\Dates\now()),
                'timezone' => TimeClock\Dates\TZ,
            ]);
        }
    }
    new TCM_Clock_Handler();
}
