<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Unit;
use App\Support\BookingNotifier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = Booking::with('unit.accommodationType')
            ->withSum(['payments as paid_total' => fn ($q) => $q->whereNull('voided_at')], 'amount')
            ->withCount(['receipts as active_receipts_count' => fn ($q) => $q->whereNull('voided_at')])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->boolean('attention'), fn ($q) => $q->needsAttention())
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
        $customers = Customer::orderByDesc('id')->limit(300)->get(['id', 'name', 'phone', 'email']);

        return view('admin.bookings.create', compact('units', 'activities', 'services', 'customers'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $this->assertAvailable($data['unit_id'], $data['check_in'], $data['check_out']);

        $this->assertServicesOpen($data['services'] ?? [], $data['check_in'], $data['check_out']);

        $data['source'] = 'admin';
        $data['created_by'] = $request->user()->id;
        $data['customer_id'] = Customer::findOrCreateFromBooking($data['guest_name'], $data['guest_phone'] ?? null, $data['guest_email'] ?? null)->id;
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
        $customers = Customer::orderByDesc('id')->limit(300)->get(['id', 'name', 'phone', 'email']);
        $booking->load('activities', 'services');
        $pay = app(\App\Services\ReceiptService::class)->summary($booking);

        return view('admin.bookings.edit', compact('booking', 'units', 'activities', 'services', 'customers', 'pay'));
    }

    public function update(Request $request, Booking $booking)
    {
        $data = $this->validateData($request);

        if ($data['status'] !== 'cancelled') {
            $unchanged = (int) $booking->unit_id === (int) $data['unit_id']
                && $booking->check_in->toDateString() === Carbon::parse($data['check_in'])->toDateString()
                && $booking->check_out->toDateString() === Carbon::parse($data['check_out'])->toDateString();

            $this->assertAvailable($data['unit_id'], $data['check_in'], $data['check_out'], $booking->id, ! $unchanged);

            $newServiceIds = array_diff($data['services'] ?? [], $booking->services()->pluck('services.id')->all());
            $this->assertServicesOpen($newServiceIds, $data['check_in'], $data['check_out']);
        }

        $data['customer_id'] = Customer::findOrCreateFromBooking($data['guest_name'], $data['guest_phone'] ?? null, $data['guest_email'] ?? null)->id;
        $activityIds = $data['activities'] ?? [];
        $serviceIds = $data['services'] ?? [];
        unset($data['activities'], $data['services']);

        $wasConfirmed = $booking->status === 'confirmed';

        $booking->update($data);
        $booking->activities()->sync($activityIds);
        $booking->services()->sync(array_fill_keys($serviceIds, ['quantity' => 1]));

        $message = 'บันทึกการแก้ไขเรียบร้อยแล้ว';

        if ($booking->status === 'confirmed' && ! $wasConfirmed) {
            $message .= ' · '.$this->confirmationMessage($booking, BookingNotifier::bookingConfirmed($booking));
        }

        return redirect()->route('admin.bookings.index')->with('status', $message);
    }

    public function resendConfirmation(Booking $booking)
    {
        if ($booking->status !== 'confirmed') {
            return back()->with('status', 'ส่งอีเมลยืนยันได้เฉพาะการจองที่มีสถานะ "ยืนยันแล้ว"');
        }

        return back()->with('status', $this->confirmationMessage($booking, BookingNotifier::bookingConfirmed($booking)));
    }

    private function confirmationMessage(Booking $booking, string $result): string
    {
        return match ($result) {
            'sent' => "ส่งอีเมลยืนยันการจองถึงลูกค้าที่ {$booking->guest_email} แล้ว",
            'no_email' => 'ลูกค้าไม่ได้ระบุอีเมล กรุณาโทรยืนยันกับลูกค้าเอง'.($booking->guest_phone ? " ({$booking->guest_phone})" : ''),
            default => 'ส่งอีเมลยืนยันไม่สำเร็จ กรุณาโทรแจ้งลูกค้า'.($booking->guest_phone ? " ({$booking->guest_phone})" : '').' หรือกด "ส่งอีเมลยืนยันอีกครั้ง" ในหน้าแก้ไขการจอง',
        };
    }

    public function destroy(Booking $booking)
    {
        if ($booking->payments()->exists() || $booking->receipts()->exists()) {
            return redirect()->route('admin.bookings.index')
                ->with('status', 'ลบไม่ได้ เพราะการจองนี้มีการบันทึกรับชำระหรือใบเสร็จแล้ว (ต้องเก็บไว้เป็นหลักฐาน) ถ้าไม่ใช้แล้วให้เปลี่ยนสถานะเป็น "ยกเลิก"');
        }

        $booking->delete();

        return redirect()->route('admin.bookings.index')->with('status', 'ลบการจองเรียบร้อยแล้ว');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'unit_id' => 'required|exists:units,id',
            'guest_name' => 'required|string|max:255',
            'guest_phone' => 'nullable|string|max:50',
            'guest_email' => 'nullable|email|max:255',
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

    private function assertAvailable(int $unitId, string $checkIn, string $checkOut, ?int $excludeId = null, bool $checkClosures = true): void
    {
        $unit = Unit::findOrFail($unitId);

        if ($checkClosures && $unit->isClosedBetween($checkIn, $checkOut)) {
            throw ValidationException::withMessages([
                'unit_id' => 'ที่พัก/จุดนี้หยุดให้บริการในช่วงวันที่เลือก',
            ]);
        }

        if ($unit->hasBookingConflict($checkIn, $checkOut, $excludeId)) {
            throw ValidationException::withMessages([
                'unit_id' => 'ที่พัก/จุดนี้ไม่ว่างในช่วงวันที่เลือก',
            ]);
        }
    }

    private function assertServicesOpen(array $serviceIds, string $checkIn, string $checkOut): void
    {
        if ($serviceIds === []) {
            return;
        }

        $closed = Service::whereIn('id', $serviceIds)->get()
            ->filter(fn (Service $service) => $service->isClosedBetween($checkIn, $checkOut));

        if ($closed->isNotEmpty()) {
            throw ValidationException::withMessages([
                'services' => 'บริการเสริมหยุดให้บริการในช่วงวันที่เข้าพัก: '.$closed->pluck('name')->join(', '),
            ]);
        }
    }
}
