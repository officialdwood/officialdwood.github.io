<?php

if (!class_exists('TCM_Admin_Reports')) {
    class TCM_Admin_Reports
    {
        const STORES = array('Great Falls', 'Helena', 'Billings');

        public static function maybe_export_csv()
        {
            if (isset($_GET['page'], $_GET['tcm_export']) && $_GET['page'] === 'tcm-reports' && $_GET['tcm_export'] === '1') {
                self::export_csv();
                exit;
            }
        }

        public static function render()
        {
            $filters = self::normalize_filters(array(
                'user' => isset($_GET['tcm_user']) ? $_GET['tcm_user'] : 0,
                'week' => isset($_GET['tcm_week']) ? $_GET['tcm_week'] : '',
                'location' => isset($_GET['tcm_location']) ? $_GET['tcm_location'] : '',
            ));

            self::render_view($filters);
        }

        private static function render_view($filters)
        {
            $users = get_users(array(
                'fields'  => array('ID', 'display_name'),
                'orderby' => 'display_name',
                'order'   => 'ASC',
            ));

            $locations_map  = self::build_locations_map($users);
            $filtered_users = self::filter_users($users, $filters['user'], $filters['location'], $locations_map);

            if ($filters['user'] && empty($filtered_users)) {
                $maybe_user = get_user_by('id', $filters['user']);
                if ($maybe_user) {
                    $filtered_users[]                 = $maybe_user;
                    $locations_map[$maybe_user->ID] = self::fetch_locations_for_user($maybe_user->ID);
                }
            }

            $user_ids_filter = array();
            $has_user_filter = !empty($filters['user']);
            $has_location_filter = ($filters['location'] !== '');

            if ($has_user_filter || $has_location_filter) {
                $user_ids_filter = array_map('intval', wp_list_pluck($filtered_users, 'ID'));

                if ($has_location_filter && !$has_user_filter && empty($user_ids_filter)) {
                    $user_ids_filter = array(-1); // force no matches when location filter yields no employees
                }
            }

            $rows = self::fetch_week_rows($filters['week'], $user_ids_filter);

            $summary_rows   = self::build_summary_rows($filtered_users, $rows, $locations_map);
            $location_stats = self::aggregate_location_totals($rows, $locations_map);
            $location_totals = array();
            foreach (self::STORES as $store_name) {
                $stat = isset($location_stats[$store_name]) ? $location_stats[$store_name] : array();
                $hours = isset($stat['hours']) ? (float) $stat['hours'] : 0.0;
                $location_totals[$store_name] = $hours;
            }
            $week_info       = self::week_range_info($filters['week']);
            $weeks           = self::build_week_options();

            $selected_user_name = '';
            if ($filters['user']) {
                $user_obj = get_user_by('id', $filters['user']);
                if ($user_obj) {
                    $selected_user_name = $user_obj->display_name;
                }
            }

            $total_punches = 0;
            foreach ($summary_rows as $summary_row) {
                $total_punches += (int) $summary_row['punch_count'];
            }
            $total_employees = count($summary_rows);

            include TCM_PLUGIN_TEMPLATES . '/admin-report-table.php';
        }

        private static function build_summary_rows($users, $rows, $locations_map)
        {
            $summary = array();

            foreach ($users as $user) {
                $uid = (int) $user->ID;
                $summary[$uid] = array(
                    'user_id'              => $uid,
                    'display_name'         => $user->display_name,
                    'weekly_total'         => 0.0,
                    'weekly_total_display' => self::format_hours_decimal(0.0) . ' h',
                    'punch_count'          => 0,
                    'location_label'       => self::format_location_label(isset($locations_map[$uid]) ? $locations_map[$uid] : array()),
                );
            }

            foreach ($rows as $row) {
                $uid = (int) $row->user_id;
                if (!isset($summary[$uid])) {
                    $label_locations = isset($locations_map[$uid]) ? $locations_map[$uid] : self::fetch_locations_for_user($uid);
                    $summary[$uid] = array(
                        'user_id'              => $uid,
                        'display_name'         => $row->display_name,
                        'weekly_total'         => 0.0,
                        'weekly_total_display' => self::format_hours_decimal(0.0) . ' h',
                        'punch_count'          => 0,
                        'location_label'       => self::format_location_label($label_locations),
                    );
                }

                $hours = self::calculate_row_hours($row);
                $summary[$uid]['weekly_total'] += $hours;
                $summary[$uid]['punch_count']++;
            }

            foreach ($summary as $uid => $data) {
                $summary[$uid]['weekly_total'] = round($data['weekly_total'], 2);
                $summary[$uid]['weekly_total_display'] = self::format_hours_decimal($summary[$uid]['weekly_total']) . ' h';
            }

            uasort($summary, function ($a, $b) {
                return strcasecmp($a['display_name'], $b['display_name']);
            });

            return $summary;
        }

        private static function normalize_filters($raw)
        {
            $filters = array(
                'user'     => isset($raw['user']) ? absint($raw['user']) : 0,
                'week'     => isset($raw['week']) ? sanitize_text_field($raw['week']) : '',
                'location' => isset($raw['location']) ? sanitize_text_field($raw['location']) : '',
            );

            if (empty($filters['week'])) {
                $filters['week'] = tcm_get_week_start(0);
            }

            return $filters;
        }

        private static function export_csv()
        {
            $filters = self::normalize_filters(array(
                'user' => isset($_GET['tcm_user']) ? $_GET['tcm_user'] : 0,
                'week' => isset($_GET['tcm_week']) ? $_GET['tcm_week'] : '',
                'location' => isset($_GET['tcm_location']) ? $_GET['tcm_location'] : '',
            ));

            $users          = get_users(array('fields' => array('ID', 'display_name')));
            $locations_map  = self::build_locations_map($users);
            $filtered_users = self::filter_users($users, $filters['user'], $filters['location'], $locations_map);

            if ($filters['user'] && empty($filtered_users)) {
                $maybe_user = get_user_by('id', $filters['user']);
                if ($maybe_user) {
                    $filtered_users[]                 = $maybe_user;
                    $locations_map[$maybe_user->ID] = self::fetch_locations_for_user($maybe_user->ID);
                }
            }

            $user_ids = array_map('intval', wp_list_pluck($filtered_users, 'ID'));
            $rows     = self::fetch_week_rows($filters['week'], $user_ids);

            $weekly_totals = array();
            $daily_totals  = array();

            foreach ($rows as $row) {
                $uid      = (int) $row->user_id;
                $date_key = $uid . '|' . $row->session_date;
                $hours    = self::calculate_row_hours($row);

                $weekly_totals[$uid]  = (isset($weekly_totals[$uid]) ? $weekly_totals[$uid] : 0.0) + $hours;
                $daily_totals[$date_key] = (isset($daily_totals[$date_key]) ? $daily_totals[$date_key] : 0.0) + $hours;
            }

            while (ob_get_level()) {
                ob_end_clean();
            }

            $week_info = self::week_range_info($filters['week']);

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment;filename=' . sprintf(
                'timeclock-report_%s_to_%s.csv',
                $week_info['start']->format('m-d-Y'),
                $week_info['end']->format('m-d-Y')
            ));
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            $output = fopen('php://output', 'w');
            fputcsv($output, array('Report Period', $week_info['label']));
            fputcsv($output, array(
                'Employee',
                'Clock In (MM/DD/YYYY hh:mm AM/PM)',
                'Clock Out (MM/DD/YYYY hh:mm AM/PM)',
                'Session Hours',
                'Date (MM/DD/YYYY)',
                'Daily Total',
                'Weekly Total',
            ));

            foreach ($rows as $index => $row) {
                $uid      = (int) $row->user_id;
                $date_key = $uid . '|' . $row->session_date;
                $hours    = self::calculate_row_hours($row);
                $next     = isset($rows[$index + 1]) ? $rows[$index + 1] : null;
                $is_last  = !($next && (int) $next->user_id === $uid);

                fputcsv($output, array(
                    $row->display_name,
                    $row->clock_in ? TimeClock\Dates\fmt_storage($row->clock_in, TimeClock\Dates\CSV_FMT) : '',
                    $row->clock_out ? TimeClock\Dates\fmt_storage($row->clock_out, TimeClock\Dates\CSV_FMT) : '',
                    self::format_hours_decimal($hours) . ' h',
                    TimeClock\Dates\fmt_storage_date($row->session_date),
                    self::format_hours_decimal(isset($daily_totals[$date_key]) ? $daily_totals[$date_key] : 0.0) . ' h',
                    $is_last ? self::format_hours_decimal(isset($weekly_totals[$uid]) ? $weekly_totals[$uid] : 0.0) . ' h' : '',
                ));
            }

            fclose($output);
            exit;
        }

        public static function ajax_render_reports()
        {
            if (!current_user_can('tcm_access') && !current_user_can('manage_options')) {
                wp_send_json_error(array('message' => __('Insufficient permissions.', 'tcm')), 403);
            }

            check_ajax_referer('tcm_update_hours_nonce', 'nonce');

            $filters = self::normalize_filters(array(
                'user' => isset($_POST['tcm_user']) ? $_POST['tcm_user'] : 0,
                'week' => isset($_POST['tcm_week']) ? $_POST['tcm_week'] : '',
                'location' => isset($_POST['tcm_location']) ? $_POST['tcm_location'] : '',
            ));

            ob_start();
            self::render_view($filters);
            $html = ob_get_clean();

            wp_send_json_success(array('html' => $html));
        }

        public static function ajax_user_punches()
        {
            if (!current_user_can('tcm_access') && !current_user_can('manage_options')) {
                wp_send_json_error(array('message' => __('Insufficient permissions.', 'tcm')), 403);
            }

            check_ajax_referer('tcm_update_hours_nonce', 'nonce');

            $user_id  = isset($_POST['user_id']) ? absint($_POST['user_id']) : 0;
            $week     = isset($_POST['tcm_week']) ? sanitize_text_field(wp_unslash($_POST['tcm_week'])) : '';
            $location = isset($_POST['tcm_location']) ? sanitize_text_field(wp_unslash($_POST['tcm_location'])) : '';

            if (!$user_id) {
                wp_send_json_error(array('message' => __('Invalid user supplied.', 'tcm')), 400);
            }

            $filters = self::normalize_filters(array(
                'user' => $user_id,
                'week' => $week,
                'location' => $location,
            ));

            $week_options = self::build_week_options(12);

            $user_locations = self::fetch_locations_for_user($user_id);
            if ($filters['location'] && !self::user_matches_location($user_locations, $filters['location'])) {
                $week_info = self::week_range_info($filters['week']);
                $html      = self::render_user_punches_html($user_id, array(), array(), $week_info['label'], 0.0, $filters['week'], $week_options);

                wp_send_json_success(array(
                    'html' => $html,
                    'weekly_total_formatted' => self::format_hours_decimal(0.0) . ' h',
                    'weekly_total_decimal' => 0.0,
                    'punch_count' => 0,
                    'week_label' => $week_info['label'],
                    'week_value' => $filters['week'],
                ));
            }

            $user_week = self::fetch_user_week_rows($user_id, $filters['week']);
            $rows         = $user_week[0];
            $weekly_total = $user_week[1];
            $daily_totals = $user_week[2];

            $week_info = self::week_range_info($filters['week']);
            $html      = self::render_user_punches_html($user_id, $rows, $daily_totals, $week_info['label'], $weekly_total, $filters['week'], $week_options);

            wp_send_json_success(array(
                'html' => $html,
                'weekly_total_formatted' => self::format_hours_decimal($weekly_total) . ' h',
                'weekly_total_decimal' => round($weekly_total, 2),
                'punch_count' => count($rows),
                'week_label' => $week_info['label'],
                'week_value' => $filters['week'],
            ));
        }

        private static function fetch_week_rows($week, $user_ids = array())
        {
            global $wpdb;

            $table = $wpdb->prefix . 'tcm_timesheets';
            $where = '1=1';

            if ($week) {
                $week_anchor = TimeClock\Dates\parse_storage($week . ' 00:00:00');
                if ($week_anchor) {
                    list($week_start_dt, $week_end_dt) = TimeClock\Dates\week_bounds($week_anchor);
                    $range_start = TimeClock\Dates\to_storage($week_start_dt);
                    $range_end   = TimeClock\Dates\to_storage($week_end_dt);

                    $where .= $wpdb->prepare(
                        ' AND (t.week_start_date_sun = %s OR ( (t.clock_in IS NOT NULL AND t.clock_in BETWEEN %s AND %s) OR (t.clock_out IS NOT NULL AND t.clock_out BETWEEN %s AND %s) OR (t.clock_in IS NOT NULL AND t.clock_out IS NOT NULL AND t.clock_in <= %s AND t.clock_out >= %s) ))',
                        $week,
                        $range_start,
                        $range_end,
                        $range_start,
                        $range_end,
                        $range_start,
                        $range_end
                    );
                } else {
                    $where .= $wpdb->prepare(' AND t.week_start_date_sun = %s', $week);
                }
            }

            if (!empty($user_ids)) {
                $ids_sql = implode(',', array_map('intval', $user_ids));
                if ($ids_sql) {
                    $where .= " AND t.user_id IN ($ids_sql)";
                }
            }

            $sql = "
                SELECT u.display_name, u.ID AS user_id, t.*, DATE(t.clock_in) AS session_date
                FROM {$table} t
                INNER JOIN {$wpdb->users} u ON u.ID = t.user_id
                WHERE {$where}
                ORDER BY u.display_name ASC, t.clock_in ASC, t.id ASC
            ";

            return $wpdb->get_results($sql);
        }

        private static function fetch_user_week_rows($user_id, $week)
        {
            $rows         = self::fetch_week_rows($week, array($user_id));
            $weekly_total = 0.0;
            $daily_totals = array();

            foreach ($rows as $row) {
                $hours = self::calculate_row_hours($row);
                $weekly_total += $hours;

                $date_key = $row->session_date;
                $daily_totals[$date_key] = (isset($daily_totals[$date_key]) ? $daily_totals[$date_key] : 0.0) + $hours;
            }

            return array($rows, $weekly_total, $daily_totals);
        }

        private static function calculate_row_hours($row)
        {
            if (isset($row->_computed_hours)) {
                return $row->_computed_hours;
            }

            $clock_in  = !empty($row->clock_in) ? TimeClock\Dates\parse_storage($row->clock_in) : null;
            $clock_out = !empty($row->clock_out) ? TimeClock\Dates\parse_storage($row->clock_out) : null;

            if ($clock_in && $clock_out) {
                $seconds = max(0, $clock_out->getTimestamp() - $clock_in->getTimestamp());
                $hours = $seconds / 3600;
            } elseif (isset($row->total_minutes) && $row->total_minutes !== null) {
                $hours = max(0, (int) $row->total_minutes) / 60;
            } else {
                $hours = max(0, floatval($row->total_hours));
            }

            $row->_computed_hours = round($hours, 4);
            return $row->_computed_hours;
        }

        private static function build_locations_map($users)
        {
            $map = array();
            foreach ($users as $user) {
                $map[$user->ID] = self::fetch_locations_for_user($user->ID);
            }
            return $map;
        }

        private static function fetch_locations_for_user($user_id)
        {
            $locations = get_user_meta($user_id, 'tcm_locations', true);
            if (!is_array($locations)) {
                return array();
            }
            return array_values(array_filter(array_map('trim', $locations)));
        }

        private static function filter_users($users, $selected_user, $selected_location, $locations_map)
        {
            if (!$selected_user && !$selected_location) {
                return $users;
            }

            $filtered = array();
            foreach ($users as $user) {
                if ($selected_user && $user->ID !== $selected_user) {
                    continue;
                }

                $user_locations = isset($locations_map[$user->ID]) ? $locations_map[$user->ID] : array();
                if ($selected_location && !self::user_matches_location($user_locations, $selected_location)) {
                    continue;
                }

                $filtered[] = $user;
            }

            return $filtered;
        }

        private static function user_matches_location($locations, $selected_location)
        {
            if ($selected_location === '') {
                return true;
            }

            foreach ($locations as $location) {
                if (strcasecmp($location, $selected_location) === 0) {
                    return true;
                }
            }

            return false;
        }

        private static function aggregate_location_totals($rows, $locations_map)
        {
            $stats = array();
            foreach (self::STORES as $store) {
                $stats[$store] = array(
                    'hours'          => 0.0,
                    'punches'        => 0,
                    'employee_ids'   => array(),
                    'employee_count' => 0,
                );
            }

            foreach ($rows as $row) {
                $hours     = self::calculate_row_hours($row);
                $locations = isset($locations_map[$row->user_id]) ? $locations_map[$row->user_id] : self::fetch_locations_for_user((int) $row->user_id);
                $store_match = null;

                foreach (self::STORES as $store) {
                    if (in_array($store, $locations, true)) {
                        $store_match = $store;
                        break;
                    }
                }

                if (!$store_match) {
                    continue;
                }

                $stats[$store_match]['hours'] += $hours;
                $stats[$store_match]['punches'] += 1;
                $stats[$store_match]['employee_ids'][(int) $row->user_id] = true;
            }

            foreach ($stats as $store => $data) {
                $stats[$store]['hours'] = round($data['hours'], 4);
                $stats[$store]['employee_count'] = count($data['employee_ids']);
                unset($stats[$store]['employee_ids']);
            }

            return $stats;
        }

        private static function format_location_label($locations)
        {
            if (empty($locations)) {
                return '';
            }
            return implode(', ', $locations);
        }

        private static function week_range_info($week)
        {
            $anchor = TimeClock\Dates\parse_storage($week . ' 00:00:00');
            if (!$anchor) {
                $anchor = TimeClock\Dates\week_start(TimeClock\Dates\now());
            }

            $bounds = TimeClock\Dates\week_bounds($anchor);
            $start  = $bounds[0];
            $end    = $bounds[1];

            return array(
                'start'       => $start,
                'end'         => $end,
                'label'       => $start->format(TimeClock\Dates\DATE_FMT) . ' - ' . $end->format(TimeClock\Dates\DATE_FMT),
                'start_label' => $start->format(TimeClock\Dates\DATE_FMT),
            );
        }

        private static function render_user_punches_html($user_id, $rows, $daily_totals, $week_label, $weekly_total, $selected_week, $week_options = array())
        {
            $week_options = is_array($week_options) ? $week_options : array();
            $has_selected = false;
            foreach ($week_options as $option) {
                if (isset($option['value']) && $option['value'] === $selected_week) {
                    $has_selected = true;
                    break;
                }
            }

            if ($selected_week && !$has_selected) {
                $missing_info   = self::week_range_info($selected_week);
                $week_options[] = array(
                    'value' => $selected_week,
                    'label' => $missing_info['label'],
                );
            }

            usort($week_options, function ($a, $b) {
                return strcmp(isset($b['value']) ? $b['value'] : '', isset($a['value']) ? $a['value'] : '');
            });

            ob_start();
            ?>
            <div class="tcm-punch-toolbar">
                <div class="tcm-week-picker">
                    <label class="screen-reader-text" for="tcm-week-select-<?php echo esc_attr($user_id); ?>"><?php esc_html_e('Select week', 'tcm'); ?></label>
                    <select id="tcm-week-select-<?php echo esc_attr($user_id); ?>" class="tcm-week-select" data-user-id="<?php echo esc_attr($user_id); ?>">
                        <?php foreach ($week_options as $option) :
                            if (!isset($option['value'], $option['label'])) {
                                continue;
                            }
                            ?>
                            <option value="<?php echo esc_attr($option['value']); ?>" <?php selected($selected_week, $option['value']); ?>><?php echo esc_html($option['label']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="tcm-week-meta">
                    <span class="tcm-week-label"><?php echo esc_html($week_label); ?></span>
                    <span class="tcm-week-total"><?php echo esc_html__('Weekly Total', 'tcm'); ?>: <strong class="tcm-detail-weekly-total"><?php echo esc_html(self::format_hours_decimal($weekly_total) . ' h'); ?></strong></span>
                </div>
            </div>
            <?php if (empty($rows)) : ?>
                <div class="tcm-punch-empty"><?php echo esc_html__('No punches this week.', 'tcm'); ?></div>
            <?php else : ?>
                <table class="tcm-punch-table">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('Clock In', 'tcm'); ?></th>
                            <th><?php echo esc_html__('Clock Out', 'tcm'); ?></th>
                            <th><?php echo esc_html__('Session Hours', 'tcm'); ?></th>
                            <th><?php echo esc_html__('Daily Total', 'tcm'); ?></th>
                            <th class="tcm-punch-actions-col"><?php echo esc_html__('Actions', 'tcm'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $row) :
                        $hours_decimal = self::calculate_row_hours($row);
                        $hours_part    = (int) floor($hours_decimal);
                        $minutes_part  = (int) round(($hours_decimal - $hours_part) * 60);
                        if ($minutes_part >= 60) {
                            $hours_part   += (int) floor($minutes_part / 60);
                            $minutes_part %= 60;
                        }
                        $daily_key   = $row->session_date;
                        $daily_total = self::format_hours_decimal(isset($daily_totals[$daily_key]) ? $daily_totals[$daily_key] : 0.0) . ' h';
                        ?>
                        <tr class="tcm-punch-row" data-id="<?php echo esc_attr($row->id); ?>" data-user-id="<?php echo esc_attr($user_id); ?>">
                            <td class="tcm-punch-time">
                                <div class="tcm-time-display"><?php echo esc_html($row->clock_in ? TimeClock\Dates\fmt_storage($row->clock_in) : ''); ?></div>
                                <div class="tcm-inline-edit">
                                    <input type="text" class="tcm-edit-clock-in" data-id="<?php echo esc_attr($row->id); ?>" data-user-id="<?php echo esc_attr($user_id); ?>" value="<?php echo esc_attr($row->clock_in ? TimeClock\Dates\fmt_storage($row->clock_in) : ''); ?>" placeholder="MM/DD/YYYY hh:mm AM/PM" />
                                    <button type="button" class="button-link tcm-save-time" data-kind="in" data-id="<?php echo esc_attr($row->id); ?>" data-user-id="<?php echo esc_attr($user_id); ?>"><?php esc_html_e('Save', 'tcm'); ?></button>
                                </div>
                            </td>
                            <td class="tcm-punch-time">
                                <div class="tcm-time-display"><?php echo esc_html($row->clock_out ? TimeClock\Dates\fmt_storage($row->clock_out) : ''); ?></div>
                                <div class="tcm-inline-edit">
                                    <input type="text" class="tcm-edit-clock-out" data-id="<?php echo esc_attr($row->id); ?>" data-user-id="<?php echo esc_attr($user_id); ?>" value="<?php echo esc_attr($row->clock_out ? TimeClock\Dates\fmt_storage($row->clock_out) : ''); ?>" placeholder="MM/DD/YYYY hh:mm AM/PM" />
                                    <button type="button" class="button-link tcm-save-time" data-kind="out" data-id="<?php echo esc_attr($row->id); ?>" data-user-id="<?php echo esc_attr($user_id); ?>"><?php esc_html_e('Save', 'tcm'); ?></button>
                                </div>
                            </td>
                            <td class="tcm-punch-hours">
                                <div class="tcm-hours-edit">
                                    <input type="number" min="0" class="tcm-hours-part" data-id="<?php echo esc_attr($row->id); ?>" data-user-id="<?php echo esc_attr($user_id); ?>" value="<?php echo esc_attr($hours_part); ?>" placeholder="H" />
                                    <span>h</span>
                                    <input type="number" min="0" max="59" class="tcm-minutes-part" data-id="<?php echo esc_attr($row->id); ?>" data-user-id="<?php echo esc_attr($user_id); ?>" value="<?php echo esc_attr($minutes_part); ?>" placeholder="M" />
                                    <span>m</span>
                                    <button type="button" class="button button-small tcm-update-btn" data-id="<?php echo esc_attr($row->id); ?>" data-user-id="<?php echo esc_attr($user_id); ?>"><?php esc_html_e('Update', 'tcm'); ?></button>
                                </div>
                            </td>
                            <td class="tcm-punch-daily">
                                <strong class="tcm-daily-total"><?php echo esc_html($daily_total); ?></strong><br />
                                <small class="description"><?php echo esc_html(TimeClock\Dates\fmt_storage_date($row->session_date)); ?></small>
                            </td>
                            <td class="tcm-punch-actions">
                                <button type="button" class="button-link tcm-delete-btn" data-id="<?php echo esc_attr($row->id); ?>" data-user-id="<?php echo esc_attr($user_id); ?>"><?php esc_html_e('Delete', 'tcm'); ?></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif;
            return ob_get_clean();
        }

        private static function format_hours_decimal($hours)
        {
            return number_format($hours, 2, '.', '');
        }

        private static function build_week_options($count = 8)
        {
            $options = array();
            $anchor  = TimeClock\Dates\week_start(TimeClock\Dates\now());

            for ($i = 0; $i < $count; $i++) {
                $start = ($i === 0)
                    ? $anchor
                    : $anchor->modify('-' . $i . ' weeks');
                $bounds = TimeClock\Dates\week_bounds($start);
                $week_start = $bounds[0];
                $week_end   = $bounds[1];

                $options[] = array(
                    'value' => TimeClock\Dates\to_storage_date($week_start),
                    'label' => $week_start->format(TimeClock\Dates\DATE_FMT) . ' - ' . $week_end->format(TimeClock\Dates\DATE_FMT),
                );
            }

            return $options;
        }
    }

    new TCM_Admin_Reports();
    add_action('wp_ajax_tcm_render_reports', array('TCM_Admin_Reports', 'ajax_render_reports'));
    add_action('wp_ajax_tcm_get_user_punches', array('TCM_Admin_Reports', 'ajax_user_punches'));
}
