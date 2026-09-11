<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $weekStart = $request->filled('week')
            ? Carbon::parse($request->string('week'))->startOfWeek()
            : Carbon::now()->startOfWeek();

        $weekEnd = $weekStart->copy()->endOfWeek();

        $days = collect(range(0, 6))->map(fn ($i) => $weekStart->copy()->addDays($i));

        $units = Unit::with('accommodationType')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $bookings = Booking::whereIn('unit_id', $units->pluck('id'))
            ->where('status', '!=', 'cancelled')
            ->where('check_in', '<=', $weekEnd)
            ->where('check_out', '>=', $weekStart)
            ->get()
            ->groupBy('unit_id');

        return view('admin.calendar.index', compact('units', 'bookings', 'days', 'weekStart', 'weekEnd'));
    }
}
