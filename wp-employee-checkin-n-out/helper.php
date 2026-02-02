<?php

function tcm_grant_access_capability()
{
    // Ensure Administrator has access too
    $admin = get_role('administrator');
    if ($admin && !$admin->has_cap('tcm_access')) {
        $admin->add_cap('tcm_access');
    }

    // Also ensure timeclock_admin has access
    $clock_admin = get_role('timeclock_admin');

    if ($clock_admin && !$clock_admin->has_cap('tcm_access')) {
        $clock_admin->add_cap('tcm_access');
    }
}
add_action('init', 'tcm_grant_access_capability');

register_activation_hook(__FILE__, function () {
    add_role('timeclock_admin', 'Timeclock Admin', [
        'read' => true,
        'tcm_access' => true,
    ]);
});

/**
 * Get current time in WordPress timezone
 * 
 * @param string $format Date format
 * @return string Formatted date/time in plugin timezone
 */
function tcm_current_time($format = 'Y-m-d H:i:s') {
    $now = TimeClock\Dates\now();
    return $now->format($format);
}

/**
 * Get current date in WordPress timezone
 * 
 * @param string $format Date format
 * @return string Formatted date in plugin timezone
 */
function tcm_current_date($format = TimeClock\Dates\DATE_FMT) {
    $now = TimeClock\Dates\now();
    return $now->format($format);
}

/**
 * Format a datetime string in WordPress timezone
 * 
 * @param string $datetime_string Database datetime string
 * @param string $format Output format
 * @return string Formatted date/time in plugin timezone
 */
function tcm_format_datetime($datetime_string, $format = TimeClock\Dates\OUT_FMT) {
    return TimeClock\Dates\fmt_storage($datetime_string, $format);
}

/**
 * Format a date string in WordPress timezone
 * 
 * @param string $date_string Database date string
 * @param string $format Output format
 * @return string Formatted date in plugin timezone
 */
function tcm_format_date($date_string, $format = TimeClock\Dates\DATE_FMT) {
    return TimeClock\Dates\fmt_storage_date($date_string, $format);
}

/**
 * Get the start of a week (Sunday) in plugin timezone
 * 
 * @param int $weeks_ago Number of weeks ago (0 = current week)
 * @return string Week start date in Y-m-d format
 */
function tcm_get_week_start($weeks_ago = 0) {
    $anchor = TimeClock\Dates\now();
    if ($weeks_ago > 0) {
        $anchor = $anchor->modify('-' . absint($weeks_ago) . ' week');
    }
    $start = TimeClock\Dates\week_start($anchor);
    return TimeClock\Dates\to_storage_date($start);
}

/**
 * Format decimal hours into hour:minute format
 * 
 * @param float $decimal_hours Decimal hours (e.g., 8.5)
 * @return string Formatted time (e.g., "8h 30m")
 */
function tcm_format_hours($decimal_hours) {
    if (empty($decimal_hours) || $decimal_hours == 0) {
        return '0h 0m';
    }
    
    $hours = floor($decimal_hours);
    $minutes = round(($decimal_hours - $hours) * 60);
    
    // Handle edge case where rounding gives us 60 minutes
    if ($minutes >= 60) {
        $hours += floor($minutes / 60);
        $minutes = $minutes % 60;
    }
    
    return $hours . 'h ' . $minutes . 'm';
}

/**
 * Get day of week name for a given date (for validation purposes)
 * 
 * @param string $date Date in Y-m-d format
 * @return string Day name (Monday, Tuesday, etc.)
 */
function tcm_get_day_name($date) {
    $dt = TimeClock\Dates\parse_storage($date . ' 00:00:00');
    if (!$dt) {
        return '';
    }
    return $dt->format('l');
}

/**
 * Test function to verify week start calculations
 * This function can be called to validate that all week starts are Mondays
 * 
 * @return array Test results
 */
function tcm_test_week_calculations() {
    $results = [];
    
    // Test current week and past 10 weeks
    for ($i = 0; $i <= 10; $i++) {
        $week_start = tcm_get_week_start($i);
        $day_name = tcm_get_day_name($week_start);
        $results[] = [
            'weeks_ago' => $i,
            'date' => $week_start,
            'day_name' => $day_name,
            'is_sunday' => $day_name === 'Sunday'
        ];
    }
    
    return $results;
}

function tcm_normalize_datetime_input($value, $fallback = null) {
    if ($value === '' || $value === null) {
        return $fallback instanceof DateTimeImmutable ? $fallback : null;
    }

    $parsed = TimeClock\Dates\parse_input($value);
    if ($parsed instanceof DateTimeImmutable) {
        return $parsed;
    }

    if ($fallback instanceof DateTimeImmutable) {
        $time_only = trim($value);
        if (preg_match('/^(\d{1,2}):(\d{2})(?:\s*(AM|PM))?$/i', $time_only, $matches)) {
            $hour = (int) $matches[1];
            $minute = (int) $matches[2];
            $meridiem = isset($matches[3]) ? strtoupper($matches[3]) : '';
            if ($meridiem === 'AM' || $meridiem === 'PM') {
                $hour = $hour % 12;
                if ($meridiem === 'PM') {
                    $hour += 12;
                }
            }
            return $fallback->setTime($hour, $minute, 0);
        }
    }

    $formats = ['Y-m-d\TH:i', 'Y-m-d\TH:i:s', DATE_ATOM, 'Y-m-d H:i:s'];
    foreach ($formats as $fmt) {
        $dt = DateTimeImmutable::createFromFormat($fmt, $value, TimeClock\Dates\tz());
        if ($dt instanceof DateTimeImmutable) {
            return $dt;
        }
    }

    $ts = strtotime($value);
    if ($ts) {
        return (new DateTimeImmutable('@' . $ts))->setTimezone(TimeClock\Dates\tz());
    }

    return $fallback instanceof DateTimeImmutable ? $fallback : null;
}