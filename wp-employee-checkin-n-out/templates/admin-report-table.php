<?php
$summary_rows = isset($summary_rows) && is_array($summary_rows) ? $summary_rows : array();
$location_totals = isset($location_totals) && is_array($location_totals) ? $location_totals : array();
$location_stats = isset($location_stats) && is_array($location_stats) ? $location_stats : array();
$week_info = isset($week_info) && is_array($week_info) ? $week_info : array('label' => '', 'start_label' => '');
$total_punches = isset($total_punches) ? (int) $total_punches : 0;
$total_employees = isset($total_employees) ? (int) $total_employees : 0;
$selected_user_name = isset($selected_user_name) ? $selected_user_name : '';
$filters = isset($filters) && is_array($filters) ? $filters : array();
$users = isset($users) && is_array($users) ? $users : array();

$logo_url                  = plugins_url('assets/images/timeclock-logo-transparent.png', TCM_PLUGIN_FILE);
$selected_user             = isset($filters['user']) ? (int) $filters['user'] : 0;
$selected_week             = isset($filters['week']) ? $filters['week'] : '';
$selected_location         = isset($filters['location']) ? $filters['location'] : '';
$selected_week_range       = isset($week_info['label']) ? $week_info['label'] : '';
$selected_week_start_label = isset($week_info['start_label']) ? $week_info['start_label'] : '';
$location_totals           = array_merge(array('Great Falls' => 0.0, 'Helena' => 0.0, 'Billings' => 0.0), $location_totals);
$total_hours_all_locations = array_sum($location_totals);
$total_available_employees = count($users);
$export_args = array(
    'page'       => 'tcm-reports',
    'tcm_export' => '1',
);
if ($selected_user) {
    $export_args['tcm_user'] = $selected_user;
}
if ($selected_week) {
    $export_args['tcm_week'] = $selected_week;
}
if ($selected_location) {
    $export_args['tcm_location'] = $selected_location;
}
?>
<div class="wrap tcm-admin-reports" data-current-week="<?php echo esc_attr($selected_week); ?>">
    <div class="tcm-header" style="display:flex;align-items:center;gap:12px;margin-top:8px;flex-wrap:wrap;">
        <img src="<?php echo esc_url($logo_url); ?>" alt="TimeClock by Protechsteel" style="height:48px;max-width:100%;object-fit:contain;background:transparent;" />
        <div style="font-size:14px;color:#4b5563;">
            View grouped employee totals and drill into punches for the selected week. Filters and exports respect America/Denver (MT) week boundaries.
        </div>
    </div>

    <div class="postbox" style="margin-top:20px;">
        <div class="postbox-header">
            <h2 style="padding:0 20px;">Filter Reports</h2>
        </div>
        <div class="inside">
            <div class="tcm-filter-layout" style="display:grid;grid-template-columns:1.1fr 1fr;gap:24px;align-items:start;">
                <form method="get" class="tcm-filter-form" style="display:block;">
                    <input type="hidden" name="page" value="tcm-reports" />
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row"><label for="tcm_user">Employee</label></th>
                                <td>
                                    <select name="tcm_user" id="tcm_user" class="regular-text">
                                        <option value="">All Employees</option>
                                        <?php foreach ($users as $user) : ?>
                                            <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($selected_user, $user->ID); ?>><?php echo esc_html($user->display_name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="tcm_week">Week</label></th>
                                <td>
                                    <select name="tcm_week" id="tcm_week" class="regular-text">
                                        <?php foreach ($weeks as $option) : ?>
                                            <option value="<?php echo esc_attr($option['value']); ?>" <?php selected($selected_week, $option['value']); ?>><?php echo esc_html($option['label']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="tcm_location">Location</label></th>
                                <td>
                                    <select name="tcm_location" id="tcm_location" class="regular-text">
                                        <option value="">All Locations</option>
                                        <option value="Billings" <?php selected($selected_location, 'Billings'); ?>>Billings</option>
                                        <option value="Great Falls" <?php selected($selected_location, 'Great Falls'); ?>>Great Falls</option>
                                        <option value="Helena" <?php selected($selected_location, 'Helena'); ?>>Helena</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="submit" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:16px;">
                        <input type="submit" name="submit" id="submit" class="button button-primary" value="Apply Filters" />
                        <a href="<?php echo esc_url(add_query_arg($export_args, admin_url('admin.php'))); ?>" class="button button-secondary">
                            <span class="dashicons dashicons-download" style="vertical-align:middle;margin-right:5px;"></span>
                            Export CSV
                        </a>
                    </p>
                </form>
                <div class="tcm-filter-insights" style="display:flex;flex-direction:row;gap:18px;align-items:flex-start;justify-content:flex-start;flex-wrap:wrap;">
                    <?php
                    $donuts = array(
                        array('key' => 'Great Falls', 'label' => 'Great Falls', 'class' => 'gf'),
                        array('key' => 'Helena', 'label' => 'Helena', 'class' => 'hl'),
                        array('key' => 'Billings', 'label' => 'Billings', 'class' => 'bl'),
                    );
                    foreach ($donuts as $donut) :
                        $key = $donut['key'];
                        $stat = isset($location_stats[$key]) ? $location_stats[$key] : array();
                        $hours = isset($stat['hours']) ? (float) $stat['hours'] : (isset($location_totals[$key]) ? (float) $location_totals[$key] : 0.0);
                        $punches = isset($stat['punches']) ? (int) $stat['punches'] : 0;
                        $employees = isset($stat['employee_count']) ? (int) $stat['employee_count'] : 0;
                        $percentage = $total_hours_all_locations > 0 ? round(($hours / $total_hours_all_locations) * 100, 1) : 0;
                        ?>
                        <div class="tcm-donut <?php echo esc_attr($donut['class']); ?>" style="text-align:center;">
                            <div style="position:relative;width:120px;height:120px;margin:0 auto;">
                                <svg viewBox="0 0 36 36" style="position:absolute;inset:0;width:120px;height:120px;transform:rotate(-90deg);">
                                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#e5e7eb" stroke-width="3.4"></circle>
                                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#111827" stroke-width="3.4" stroke-linecap="round" stroke-dasharray="<?php echo esc_attr($percentage); ?>, 100"></circle>
                                </svg>
                                <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;font-weight:700;color:#111827;">
                                    <span><?php echo esc_html(number_format($hours, 2)); ?>h</span>
                                    <small style="color:#6b7280;font-weight:600;letter-spacing:.3px;"><?php echo esc_html($percentage); ?>%</small>
                                </div>
                            </div>
                            <div style="margin-top:6px;font-size:12px;color:#6b7280;font-weight:600;letter-spacing:.2px;">&nbsp;<?php echo esc_html($donut['label']); ?></div>
                            <div style="margin-top:4px;font-size:11px;color:#4b5563;font-weight:600;">Employees: <?php echo esc_html($employees); ?></div>
                            <div style="font-size:11px;color:#4b5563;font-weight:600;">Punches: <?php echo esc_html($punches); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            </div>
        </div>
    </div>

    <div class="tcm-summary-hero" style="margin-top:24px;background:#f0f0f1;padding:18px 20px;border-radius:6px;border-left:4px solid #2271b1;display:flex;flex-wrap:wrap;gap:18px;align-items:center;">
        <div style="display:flex;align-items:center;gap:8px;font-weight:600;color:#1f2937;">
            <span class="dashicons dashicons-chart-bar" style="color:#2271b1;"></span>
            <span>Week <?php echo esc_html($selected_week_range); ?></span>
        </div>
        <div style="color:#374151;">
            Employees in view: <strong><?php echo esc_html($total_employees); ?></strong> of <?php echo esc_html($total_available_employees); ?>
        </div>
        <div style="color:#374151;">
            Total punches (loaded on demand): <strong><?php echo esc_html($total_punches); ?></strong>
        </div>
        <?php if ($selected_user_name) : ?>
            <div style="color:#374151;">Focused employee: <strong><?php echo esc_html($selected_user_name); ?></strong></div>
        <?php endif; ?>
        <?php if ($selected_location) : ?>
            <div style="color:#374151;">Filtered location: <strong><?php echo esc_html($selected_location); ?></strong></div>
        <?php endif; ?>
        <div style="margin-left:auto;color:#6b7280;font-size:13px;">Pay period begins <?php echo esc_html($selected_week_start_label); ?> (Sun)</div>
    </div>

    <div class="postbox" style="margin-top:24px;">
        <div class="postbox-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <h2 style="padding:0 20px;margin:0;">Employee Summaries</h2>
            <div style="padding:10px 16px;display:flex;gap:8px;">
                <button id="tcm-open-add-record" class="button button-primary">Add Time Record</button>
            </div>
        </div>
        <div class="inside">
            <?php if (empty($summary_rows)) : ?>
                <div class="tcm-empty" style="text-align:center;padding:60px 20px;">
                    <div class="dashicons dashicons-clock" style="font-size:64px;color:#c3c4c7;margin-bottom:20px;"></div>
                    <h3 style="color:#1f2937;margin-bottom:12px;">No employees match the current filters.</h3>
                    <p style="color:#6b7280;max-width:420px;margin:0 auto 18px;">Adjust filters above or reset to view all employees.</p>
                    <a href="<?php echo esc_url(add_query_arg(array('page' => 'tcm-reports'), admin_url('admin.php'))); ?>" class="button button-secondary">
                        <span class="dashicons dashicons-update" style="vertical-align:middle;margin-right:5px;"></span>
                        Reset Filters
                    </a>
                </div>
            <?php else : ?>
                <div class="tcm-summary-rows" style="display:flex;flex-direction:column;gap:12px;">
                    <?php foreach ($summary_rows as $row) : ?>
                        <div class="tcm-summary-row" data-user-id="<?php echo esc_attr($row['user_id']); ?>" data-week="<?php echo esc_attr($selected_week); ?>" data-location="<?php echo esc_attr($selected_location); ?>" style="border:1px solid #d1d5db;border-radius:8px;overflow:hidden;background:#fff;">
                            <div class="tcm-summary-head" style="display:flex;align-items:center;gap:12px;padding:14px 16px;">
                                <button type="button" class="button button-secondary tcm-summary-toggle" aria-expanded="false" style="display:flex;align-items:center;gap:4px;">
                                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                                    <span>View Punches</span>
                                </button>
                                <div class="tcm-summary-name" style="font-weight:600;color:#111827;flex:1;display:flex;flex-direction:column;gap:2px;">
                                    <span><?php echo esc_html($row['display_name']); ?></span>
                                    <?php if (!empty($row['location_label'])) : ?>
                                        <small style="color:#6b7280;font-size:12px;">Locations: <?php echo esc_html($row['location_label']); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="tcm-summary-stat" style="min-width:120px;text-align:right;">
                                    <div style="font-size:13px;color:#6b7280;">Weekly Total</div>
                                    <div class="tcm-summary-weekly" data-weekly-decimal="<?php echo esc_attr($row['weekly_total']); ?>" style="font-weight:700;color:#047857;font-size:16px;">
                                        <?php echo esc_html($row['weekly_total_display']); ?>
                                    </div>
                                </div>
                                <div class="tcm-summary-stat" style="min-width:100px;text-align:right;">
                                    <div style="font-size:13px;color:#6b7280;">Punches</div>
                                    <div class="tcm-summary-punches" style="font-weight:700;color:#111827;">
                                        <?php echo esc_html($row['punch_count']); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="tcm-summary-details" hidden>
                                <div class="tcm-detail-loading" style="padding:24px;text-align:center;color:#6b7280;">
                                    Loading punches...
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="tcm-add-record-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:100000;">
    <div style="max-width:640px;margin:8vh auto;background:#fff;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,.2);overflow:hidden;">
    <div style="padding:14px 18px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;">
            <strong style="font-size:16px;">Add Time Record</strong>
            <button type="button" id="tcm-close-add-record" class="button">Close</button>
        </div>
        <div style="padding:16px 18px;">
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="tcm-add-form" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;align-items:end;">
                <?php wp_nonce_field('tcm_add_record_nonce'); ?>
                <input type="hidden" name="action" value="tcm_add_record" />
                <div>
                    <label><strong>Employee</strong></label><br />
                    <select name="tcm_user_id" required style="width:100%;">
                        <?php
                        $all_users = get_users(array(
                            'orderby' => 'display_name',
                            'order'   => 'ASC',
                            'fields'  => array('ID', 'display_name'),
                        ));
                        foreach ($all_users as $user_obj) {
                            echo '<option value="' . esc_attr($user_obj->ID) . '">' . esc_html($user_obj->display_name) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label><strong>Location</strong></label><br />
                    <select name="tcm_location" style="width:100%;">
                        <option value="">Select location (optional)</option>
                        <option>Great Falls</option>
                        <option>Helena</option>
                        <option>Billings</option>
                    </select>
                </div>
                <div>
                    <label><strong>Time In</strong></label><br />
                    <input type="text" name="tcm_clock_in" required style="width:100%;" placeholder="MM/DD/YYYY hh:mm AM/PM" />
                </div>
                <div>
                    <label><strong>Time Out</strong></label><br />
                    <input type="text" name="tcm_clock_out" style="width:100%;" placeholder="MM/DD/YYYY hh:mm AM/PM" />
                </div>
                <div>
                    <label><strong>Duration (hrs)</strong></label><br />
                    <input type="number" step="0.25" min="0" name="tcm_duration_hours" style="width:100%;" placeholder="Optional" />
                </div>
                <div style="grid-column:1/-1;">
                    <label><strong>Note</strong> <span style="color:#6b7280;font-weight:400;">(optional)</span></label><br />
                    <input type="text" name="tcm_note" style="width:100%;" placeholder="Reason or comment" />
                </div>
                <div style="grid-column:1/-1;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <button type="submit" class="button button-primary">Save Time Record</button>
                    <button type="button" id="tcm-cancel-add-record" class="button">Cancel</button>
                    <span style="color:#6b7280;font-size:12px;">Tip: if the clock out is before clock in we assume it continues into the next day.</span>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .tcm-admin-reports .tcm-summary-row[aria-busy="true"] .tcm-summary-head { opacity: 0.6; }
    .tcm-admin-reports .tcm-summary-toggle[aria-expanded="true"] .dashicons { transform: rotate(90deg); }
    .tcm-admin-reports .tcm-summary-details { border-top: 1px solid #e5e7eb; background:#f9fafb; padding:18px 18px 24px; }
    .tcm-admin-reports .tcm-summary-details .tcm-punch-header { display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px;align-items:center;margin-bottom:12px;font-weight:600;color:#111827; }
    .tcm-admin-reports .tcm-summary-details .tcm-punch-table { width:100%;border-collapse:collapse;background:#fff; }
    .tcm-admin-reports .tcm-summary-details .tcm-punch-table th,
    .tcm-admin-reports .tcm-summary-details .tcm-punch-table td { padding:10px 12px;border-bottom:1px solid #e5e7eb;vertical-align:middle;text-align:left; }
    .tcm-admin-reports .tcm-summary-details .tcm-punch-table th { background:#f3f4f6;font-weight:600;font-size:13px;color:#4b5563; }
    .tcm-admin-reports .tcm-summary-details .tcm-inline-edit { margin-top:6px;display:flex;gap:6px;flex-wrap:wrap; }
    .tcm-admin-reports .tcm-summary-details .tcm-inline-edit input { width:200px; }
    .tcm-admin-reports .tcm-summary-details .tcm-hours-edit { display:flex;align-items:center;gap:6px;flex-wrap:wrap; }
    .tcm-admin-reports .tcm-summary-details .tcm-hours-edit input { width:70px;text-align:center; }
    .tcm-admin-reports .tcm-summary-details .tcm-hours-edit span { font-size:12px;color:#6b7280; }
    .tcm-admin-reports .tcm-summary-details .tcm-punch-actions { text-align:center; }
    .tcm-admin-reports .tcm-summary-details .tcm-punch-empty { padding:24px;text-align:center;color:#6b7280;font-style:italic; }
    .tcm-admin-reports .tcm-summary-rows .tcm-summary-row.open .tcm-summary-details { display:block; }
    .tcm-admin-reports .tcm-summary-rows .tcm-summary-row.open .tcm-summary-details[hidden] { display:block; }
    .tcm-admin-reports .tcm-summary-rows .tcm-summary-row.open .tcm-summary-details .tcm-detail-loading { color:#6b7280; }
    .tcm-admin-reports .tcm-punch-toolbar { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:16px; }
    .tcm-admin-reports .tcm-week-picker select { min-width:200px; }
    .tcm-admin-reports .tcm-week-meta { display:flex; align-items:center; gap:16px; flex-wrap:wrap; font-weight:600; color:#1f2937; }
    .tcm-admin-reports .tcm-week-meta .tcm-week-label { color:#4b5563; font-weight:500; }
    .tcm-admin-reports .tcm-week-meta .tcm-detail-weekly-total { color:#047857; }
    body.toplevel_page_tcm-reports #wpfooter { display:none !important; }
    @media (max-width:960px) {
        .tcm-admin-reports .tcm-filter-layout { grid-template-columns:1fr; }
    }
</style>
