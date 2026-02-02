<?php

function tcm_create_table()
{
    global $wpdb;
    $table = $wpdb->prefix . 'tcm_timesheets';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            clock_in DATETIME,
            clock_out DATETIME,
            week_start_date DATE,
            week_start_date_sun DATE,
            total_hours DECIMAL(7,4),
            total_minutes INT UNSIGNED,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    tcm_maybe_add_week_start_column($table);
    tcm_maybe_alter_total_hours_precision($table);
    tcm_maybe_add_total_minutes_column($table);
    tcm_backfill_week_starts($table);
    tcm_backfill_total_minutes($table);
}

function tcm_maybe_add_week_start_column($table)
{
    global $wpdb;
    $column = $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM {$table} LIKE %s", 'week_start_date_sun'));
    if (!$column) {
        $wpdb->query("ALTER TABLE {$table} ADD COLUMN week_start_date_sun DATE NULL AFTER week_start_date");
    }
}

function tcm_maybe_alter_total_hours_precision($table)
{
    global $wpdb;
    $column = $wpdb->get_row($wpdb->prepare("SHOW COLUMNS FROM {$table} LIKE %s", 'total_hours'));
    if ($column && isset($column->Type) && stripos($column->Type, 'decimal(7,4)') === false) {
        $wpdb->query("ALTER TABLE {$table} MODIFY total_hours DECIMAL(7,4) NULL");
    }
}

function tcm_maybe_add_total_minutes_column($table)
{
    global $wpdb;
    $column = $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM {$table} LIKE %s", 'total_minutes'));
    if (!$column) {
        $wpdb->query("ALTER TABLE {$table} ADD COLUMN total_minutes INT UNSIGNED NULL AFTER total_hours");
    }
}

function tcm_backfill_week_starts($table)
{
    global $wpdb;
    $batch = 250;
    do {
        $rows = $wpdb->get_results(
            "SELECT id, clock_in, week_start_date, week_start_date_sun
             FROM {$table}
             WHERE (week_start_date_sun IS NULL OR week_start_date_sun = '0000-00-00')
               AND clock_in IS NOT NULL
             LIMIT {$batch}"
        );

        if (empty($rows)) {
            break;
        }

        foreach ($rows as $row) {
            $clock_in_dt = TimeClock\Dates\parse_storage($row->clock_in);
            if (!$clock_in_dt) {
                continue;
            }
            $start = TimeClock\Dates\week_start($clock_in_dt);
            $start_date = TimeClock\Dates\to_storage_date($start);
            $wpdb->update(
                $table,
                [
                    'week_start_date_sun' => $start_date,
                    'week_start_date'     => $start_date,
                ],
                ['id' => (int) $row->id],
                ['%s', '%s'],
                ['%d']
            );
        }
    } while (count($rows) === $batch);
}

function tcm_backfill_total_minutes($table)
{
    global $wpdb;
    $batch = 250;

    do {
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, clock_in, clock_out, total_hours, total_minutes FROM {$table}
                 WHERE total_minutes IS NULL
                 LIMIT %d",
                $batch
            )
        );

        if (empty($rows)) {
            break;
        }

        foreach ($rows as $row) {
            $clock_in_dt  = TimeClock\Dates\parse_storage($row->clock_in);
            $clock_out_dt = TimeClock\Dates\parse_storage($row->clock_out);

            if ($clock_in_dt && $clock_out_dt) {
                $seconds = max(0, $clock_out_dt->getTimestamp() - $clock_in_dt->getTimestamp());
                $minutes = (int) round($seconds / 60);
            } else {
                $minutes = (int) round(floatval($row->total_hours) * 60);
            }

            $hours = round($minutes / 60, 4);

            $wpdb->update(
                $table,
                [
                    'total_minutes' => $minutes,
                    'total_hours'   => $hours,
                ],
                ['id' => (int) $row->id],
                ['%d', '%f'],
                ['%d']
            );
        }
    } while (count($rows) === $batch);
}

tcm_create_table();
