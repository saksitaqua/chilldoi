<?php

namespace App\Http\Middleware;

use App\Models\SiteVisit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

/**
 * Counts visitors to the public pages without keeping IP addresses: only a daily one-way hash is stored.
 */
class TrackVisit
{
    private const ROUTES = ['home', 'stories.*', 'availability.*', 'units.*', 'booking.create', 'booking.thankyou', 'map.*'];

    private const BOTS = '/bot|crawl|spider|slurp|facebookexternalhit|preview|monitor|uptime|curl|wget|python|httpclient|headless|lighthouse/i';

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        try {
            if (! $this->shouldCount($request, $response)) {
                return;
            }

            $today = now()->toDateString();
            $hash = hash_hmac('sha256', $request->ip().'|'.$request->userAgent().'|'.$today, (string) config('app.key'));

            $updated = SiteVisit::where('visit_date', $today)->where('visitor_hash', $hash)
                ->update(['views' => DB::raw('views + 1'), 'updated_at' => now()]);

            if (! $updated) {
                try {
                    SiteVisit::create(['visit_date' => $today, 'visitor_hash' => $hash]);
                } catch (\Illuminate\Database\UniqueConstraintViolationException) {
                    SiteVisit::where('visit_date', $today)->where('visitor_hash', $hash)
                        ->update(['views' => DB::raw('views + 1')]);
                }
            }
        } catch (\Throwable $e) {
            // Counting visitors must never break the site.
            report($e);
        }
    }

    private function shouldCount(Request $request, Response $response): bool
    {
        return $request->isMethod('GET')
            && $response->getStatusCode() === 200
            && ! $request->ajax()
            && ! $request->user()
            && $request->routeIs(...self::ROUTES)
            && ! preg_match(self::BOTS, (string) $request->userAgent())
            && $request->userAgent() !== null
            && Cache::remember('site-visits-table', 300, fn () => Schema::hasTable('site_visits'));
    }
}
