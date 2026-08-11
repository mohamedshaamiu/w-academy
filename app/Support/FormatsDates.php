<?php

namespace App\Support;

use Carbon\CarbonInterface;

class FormatsDates
{
    /**
     * Format a date as d/m/Y, identical in both locales (Western Arabic
     * numerals always). Numbers are never localised.
     */
    public static function date(?CarbonInterface $date): string
    {
        return $date?->format('d/m/Y') ?? '';
    }

    public static function dateTime(?CarbonInterface $date): string
    {
        if (! $date) {
            return '';
        }

        return self::date($date).' '.$date->format('H:i');
    }

    public static function time(?CarbonInterface $date): string
    {
        return $date?->format('H:i') ?? '';
    }

    /**
     * Translated month name for the active locale (1-12).
     */
    public static function monthName(int $month): string
    {
        return __('common.month.'.$month);
    }

    /**
     * Translated weekday name for the active locale (0=Sunday..6=Saturday,
     * matching PHP's `w` / ISO weekday ints stored in squads.training_days).
     */
    public static function weekdayName(int $weekday): string
    {
        return __('common.weekday.'.$weekday);
    }

    /**
     * Long, human date such as "11 August 2026" with a translated month name.
     */
    public static function longDate(?CarbonInterface $date): string
    {
        if (! $date) {
            return '';
        }

        return sprintf('%d %s %d', $date->day, self::monthName($date->month), $date->year);
    }
}
