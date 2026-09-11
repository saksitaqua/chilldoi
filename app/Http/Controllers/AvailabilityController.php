<?php

namespace App\Http\Controllers;

use App\Models\AccommodationType;
use App\Models\Booking;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $types = AccommodationType::where('is_active', true)->orderBy('name')->get();
        $units = collect();
        $searched = false;

        if ($request->filled(['check_in', 'check_out'])) {
            $request->validate([
                'check_in' => 'required|date',
                'check_out' => 'required|date|after:check_in',
                'accommodation_type_id' => 'nullable|exists:accommodation_types,id',
            ]);

            $searched = true;

            $units = Unit::with(['accommodationType', 'visibleImages'])
                ->where('is_active', true)
                ->when($request->accommodation_type_id, fn ($q) => $q->where('accommodation_type_id', $request->accommodation_type_id))
                ->whereDoesntHave('bookings', function ($q) use ($request) {
                    $q->where('status', '!=', 'cancelled')
                        ->where('check_in', '<', $request->check_out)
                        ->where('check_out', '>', $request->check_in);
                })
                ->orderBy('name')
                ->get();
        }

        $weekStart = $request->filled('week')
            ? Carbon::parse($request->string('week'))->startOfWeek()
            : Carbon::now()->startOfWeek();

        $weekEnd = $weekStart->copy()->endOfWeek();

        $days = collect(range(0, 6))->map(fn ($i) => $weekStart->copy()->addDays($i));

        $calendarUnits = Unit::with('accommodationType')
            ->where('is_active', true)
            ->when($request->accommodation_type_id, fn ($q) => $q->where('accommodation_type_id', $request->accommodation_type_id))
            ->orderBy('name')
            ->get();

        $calendarBookings = Booking::whereIn('unit_id', $calendarUnits->pluck('id'))
            ->where('status', '!=', 'cancelled')
            ->where('check_in', '<=', $weekEnd)
            ->where('check_out', '>=', $weekStart)
            ->get()
            ->groupBy('unit_id');

        return view('availability.index', compact(
            'types', 'units', 'searched',
            'days', 'weekStart', 'weekEnd', 'calendarUnits', 'calendarBookings'
        ));
    }
}
