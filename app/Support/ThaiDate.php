<?php

namespace App\Support;

use Carbon\CarbonInterface;

class ThaiDate
{
    private const MONTHS_TH = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
        'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];

    private const MONTHS_EN = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'];

    public static function isBuddhist(): bool
    {
        return strtolower((string) config('receipt.year_era', 'be')) !== 'ce';
    }

    /** Year as used on Thai documents (Buddhist Era unless configured otherwise). */
    public static function year(CarbonInterface $date): int
    {
        return $date->year + (self::isBuddhist() ? 543 : 0);
    }

    /** 31 ตุลาคม 2569 */
    public static function long(CarbonInterface $date): string
    {
        return $date->day.' '.self::MONTHS_TH[$date->month - 1].' '.self::year($date);
    }

    /** 31 October 2026 (always Christian Era) */
    public static function longEnglish(CarbonInterface $date): string
    {
        return $date->day.' '.self::MONTHS_EN[$date->month - 1].' '.$date->year;
    }

    /** 31/10/2569 */
    public static function short(CarbonInterface $date): string
    {
        return $date->format('d/m/').self::year($date);
    }
}
