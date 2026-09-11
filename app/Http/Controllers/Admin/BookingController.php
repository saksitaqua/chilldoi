<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = Booking::with('unit.accommodationType')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('check_in')
            ->paginate(20)
            ->withQueryString();

        return view('admin.bookings.index', compact('bookings'));
    }

    public function create()
    {
        $units = Unit::with('accommodationType')->where('is_active', true)->orderBy('name')->get();
        $activities = Activity::where('is_active', true)->orderBy('name')->get();
        $services = Service::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.bookings.create', compact('units', 'activities', 'services'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $this->assertAvailable($data['unit_id'], $data['check_in'], $data['check_out']);

        $data['source'] = 'admin';
        $data['created_by'] = $request->user()->id;
        $activityIds = $data['activities'] ?? [];
        $serviceIds = $data['services'] ?? [];
        unset($data['activities'], $data['services']);

        $booking = Booking::create($data);
        $booking->activities()->sync($activityIds);
        $booking->services()->sync(array_fill_keys($serviceIds, ['quantity' => 1]));

        return redirect()->route('admin.bookings.index')->with('status', 'บันทึกการจองเรียบร้อยแล้ว');
    }

    public function edit(Booking $booking)
    {
        $units = Unit::with('accommodationType')->where('is_active', true)->orderBy('name')->get();
        $activities = Activity::where('is_active', true)->orderBy('name')->get();
        $services = Service::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $booking->load('activities', 'services');

        return view('admin.bookings.edit', compact('booking', 'units', 'activities', 'services'));
    }

    public function update(Request $request, Booking $booking)
    {
        $data = $this->validateData($request);

        if ($data['status'] !== 'cancelled') {
            $this->assertAvailable($data['unit_id'], $data['check_in'], $data['check_out'], $booking->id);
        }

        $activityIds = $data['activities'] ?? [];
        $serviceIds = $data['services'] ?? [];
        unset($data['activities'], $data['services']);

        $booking->update($data);
        $booking->activities()->sync($activityIds);
        $booking->services()->sync(array_fill_keys($serviceIds, ['quantity' => 1]));

        return redirect()->route('admin.bookings.index')->with('status', 'บันทึกการแก้ไขเรียบร้อยแล้ว');
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();

        return redirect()->route('admin.bookings.index')->with('status', 'ลบการจองเรียบร้อยแล้ว');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'unit_id' => 'required|exists:units,id',
            'guest_name' => 'required|string|max:255',
            'guest_phone' => 'nullable|string|max:50',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1',
            'status' => ['required', Rule::in(['pending', 'confirmed', 'cancelled'])],
            'notes' => 'nullable|string',
            'activities' => 'nullable|array',
            'activities.*' => 'exists:activities,id',
            'services' => 'nullable|array',
            'services.*' => 'exists:services,id',
        ]);
    }

    private function assertAvailable(int $unitId, string $checkIn, string $checkOut, ?int $excludeId = null): void
    {
        $unit = Unit::findOrFail($unitId);

        if (! $unit->isAvailable($checkIn, $checkOut, $excludeId)) {
            throw ValidationException::withMessages([
                'unit_id' => 'ที่พัก/จุดนี้ไม่ว่างในช่วงวันที่เลือก',
            ]);
        }
    }
}
