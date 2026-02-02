<?php
/**
 * TimeClock date helpers (America/Denver UI timezone).
 */

namespace TimeClock\Dates;

const TZ        = 'America/Denver';
const IN_FMT    = 'm/d/Y h:i A';   // UI inputs (modal, edits)
const DATE_FMT  = 'm/d/Y';         // MM/DD/YYYY (no time)
const OUT_FMT   = 'm/d/Y h:i A';   // UI outputs (tables, CSV)
const CSV_FMT   = 'm/d/Y h:i A';

/**
 * Lazily instantiate the primary timezone.
 */
function tz(): \DateTimeZone {
    static $tz;
    if (null === $tz) {
        $tz = new \DateTimeZone(TZ);
    }
    return $tz;
}

/**
 * Parse a UI datetime string (MM/DD/YYYY hh:mm AM/PM) into an immutable object in the UI timezone.
 */
function parse_input(?string $s): ?\DateTimeImmutable {
    if (!$s) {
        return null;
    }
    $dt = \DateTimeImmutable::createFromFormat(IN_FMT, trim($s), tz());
    if ($dt instanceof \DateTimeImmutable) {
        return $dt;
    }
    return null;
}

/**
 * Convert a runtime datetime to the UI timezone, then format.
 */
function fmt(\DateTimeInterface $dt, string $fmt = OUT_FMT): string {
    $local = (new \DateTimeImmutable('@' . $dt->getTimestamp()))->setTimezone(tz());
    return $local->format($fmt);
}

/**
 * Format a stored datetime string, treating storage as UI timezone.
 */
function fmt_storage(?string $s, string $fmt = OUT_FMT): string {
    if (!$s) {
        return '';
    }
    $parsed = parse_storage($s);
    if (!$parsed) {
        return '';
    }
    return $parsed->format($fmt);
}

/**
 * Convert a DateTimeInterface to the storage format (Y-m-d H:i:s) in the UI timezone.
 */
function to_storage(\DateTimeInterface $dt): string {
    $local = (new \DateTimeImmutable('@' . $dt->getTimestamp()))->setTimezone(tz());
    return $local->format('Y-m-d H:i:s');
}

/**
 * Parse a storage datetime string (assumed America/Denver) into immutable object.
 */
function parse_storage(?string $s): ?\DateTimeImmutable {
    if (!$s) {
        return null;
    }
    $dt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $s, tz());
    if ($dt instanceof \DateTimeImmutable) {
        return $dt;
    }
    $ts = strtotime($s);
    return $ts ? (new \DateTimeImmutable('@' . $ts))->setTimezone(tz()) : null;
}

/**
 * Format a storage date string (Y-m-d) into UI format.
 */
function fmt_storage_date(?string $s, string $fmt = DATE_FMT): string {
    if (!$s) {
        return '';
    }
    $dt = \DateTimeImmutable::createFromFormat('Y-m-d', $s, tz());
    if ($dt instanceof \DateTimeImmutable) {
        return $dt->format($fmt);
    }
    $ts = strtotime($s);
    return $ts ? fmt((new \DateTimeImmutable('@' . $ts))->setTimezone(tz()), $fmt) : '';
}

/** Returns [Sunday 00:00:00, Saturday 23:59:59] in America/Denver for given anchor */
function week_bounds(\DateTimeInterface $anchor): array {
    $local = (new \DateTimeImmutable('@' . $anchor->getTimestamp()))->setTimezone(tz())->setTime(0, 0, 0);
    $dow = (int) $local->format('w'); // 0=Sun
    $start = $local->modify('-' . $dow . ' days');
    $end   = $start->modify('+6 days')->setTime(23, 59, 59);
    return [$start, $end];
}

/** Sunday at 00:00:00 for the anchor’s week (Denver) */
function week_start(\DateTimeInterface $anchor): \DateTimeImmutable {
    return week_bounds($anchor)[0];
}

/** Short helper: now() in UI timezone. */
function now(): \DateTimeImmutable {
    return new \DateTimeImmutable('now', tz());
}

/**
 * Convert a DateTimeInterface into a storage date (Y-m-d) in the UI timezone.
 */
function to_storage_date(\DateTimeInterface $dt): string {
    $local = (new \DateTimeImmutable('@' . $dt->getTimestamp()))->setTimezone(tz());
    return $local->format('Y-m-d');
}

/**
 * Format week labels such as "MM/DD/YYYY – MM/DD/YYYY".
 */
function week_label(\DateTimeInterface $anchor): string {
    [$start, $end] = week_bounds($anchor);
    return $start->format(DATE_FMT) . ' - ' . $end->format(DATE_FMT);
}

/**
 * Ensure consistent friendly validation message.
 */
function input_format_hint(): string {
    return 'Use MM/DD/YYYY hh:mm AM/PM';
}
