<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class SiteVisit extends Model
{
    protected $fillable = ['visit_date', 'visitor_hash', 'views'];

    protected $casts = ['visit_date' => 'date'];

    /** Visitors (unique per day) and page views for the last $days days including today. */
    public static function range(int $days): array
    {
        $row = static::where('visit_date', '>=', now()->subDays($days - 1)->toDateString())
            ->selectRaw('count(*) as visitors, coalesce(sum(views), 0) as views')
            ->first();

        return ['visitors' => (int) $row->visitors, 'views' => (int) $row->views];
    }

    /** @return array{today: array, week: array, month: array, total: array, daily: array}|null null until the table exists */
    public static function stats(): ?array
    {
        if (! Schema::hasTable('site_visits')) {
            return null;
        }

        $total = static::selectRaw('count(*) as visitors, coalesce(sum(views), 0) as views')->first();

        $perDay = static::where('visit_date', '>=', now()->subDays(13)->toDateString())
            ->selectRaw('visit_date, count(*) as visitors')
            ->groupBy('visit_date')
            ->pluck('visitors', 'visit_date')
            ->mapWithKeys(fn ($count, $date) => [substr((string) $date, 0, 10) => (int) $count]);

        $daily = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $daily[$date] = $perDay[$date] ?? 0;
        }

        return [
            'today' => static::range(1),
            'week' => static::range(7),
            'month' => static::range(30),
            'total' => ['visitors' => (int) $total->visitors, 'views' => (int) $total->views],
            'daily' => $daily,
        ];
    }
}
