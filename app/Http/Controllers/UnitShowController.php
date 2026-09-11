<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Carbon\Carbon;

class UnitShowController extends Controller
{
    public function show(Unit $unit)
    {
        abort_unless($unit->is_active, 404);

        $unit->load(['accommodationType', 'visibleImages']);

        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = $weekStart->copy()->addWeeks(1)->endOfWeek();

        $days = collect(range(0, 13))->map(fn ($i) => $weekStart->copy()->addDays($i));

        $bookings = $unit->bookings()
            ->where('status', '!=', 'cancelled')
            ->where('check_in', '<=', $weekEnd)
            ->where('check_out', '>=', $weekStart)
            ->get();

        return view('units.show', compact('unit', 'days', 'bookings'));
    }
}
