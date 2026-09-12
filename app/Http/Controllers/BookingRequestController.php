<?php

namespace App\Http\Controllers;

use App\Mail\PaymentSlipUploaded;
use App\Models\Activity;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class BookingRequestController extends Controller
{
    public function create(Request $request, Unit $unit)
    {
        abort_unless($unit->is_active, 404);

        $request->validate([
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
        ]);

        $checkIn = $request->string('check_in')->value();
        $checkOut = $request->string('check_out')->value();

        $unit->load('accommodationType');
        $activities = Activity::availableNow()->orderBy('name')->get();
        $services = Service::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();

        $available = $unit->isAvailable($checkIn, $checkOut);

        return view('booking.create', compact('unit', 'checkIn', 'checkOut', 'activities', 'services', 'available'));
    }

    public function store(Request $request, Unit $unit)
    {
        abort_unless($unit->is_active, 404);

        $data = $request->validate([
            'guest_name' => 'required|string|max:255',
            'guest_phone' => 'required|string|max:50',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'activities' => 'nullable|array',
            'activities.*' => 'exists:activities,id',
            'services' => 'nullable|array',
            'services.*' => 'exists:services,id',
        ]);

        if (! $unit->isAvailable($data['check_in'], $data['check_out'])) {
            return back()->withInput()->withErrors([
                'check_in' => 'ขออภัย ที่พัก/จุดนี้เพิ่งถูกจองในช่วงวันที่เลือกไปแล้ว กรุณาเลือกวันที่อื่น',
            ]);
        }

        $booking = Booking::create([
            'unit_id' => $unit->id,
            'guest_name' => $data['guest_name'],
            'guest_phone' => $data['guest_phone'],
            'check_in' => $data['check_in'],
            'check_out' => $data['check_out'],
            'guests' => $data['guests'],
            'status' => 'pending',
            'source' => 'online',
            'notes' => $data['notes'] ?? null,
        ]);

        $booking->activities()->sync($data['activities'] ?? []);

        $serviceIds = $data['services'] ?? [];
        $booking->services()->sync(array_fill_keys($serviceIds, ['quantity' => 1]));

        return redirect()->route('booking.thankyou', $booking);
    }

    public function thankyou(Booking $booking)
    {
        $booking->load('unit.accommodationType', 'activities', 'services');

        return view('booking.thankyou', compact('booking'));
    }

    public function uploadSlip(Request $request, Booking $booking)
    {
        $request->validate([
            'slip' => 'required|image|max:8192',
        ], [
            'slip.required' => 'กรุณาเลือกไฟล์รูปสลิปก่อนกดอัปโหลด',
            'slip.image' => 'ไฟล์ที่แนบต้องเป็นรูปภาพเท่านั้น (jpg, png)',
            'slip.max' => 'ไฟล์รูปใหญ่เกินไป กรุณาเลือกไฟล์ที่เล็กกว่า 8MB',
        ]);

        if ($booking->payment_slip) {
            Storage::disk('public')->delete($booking->payment_slip);
        }

        $filename = "{$booking->id}_".time().'.'.$request->file('slip')->getClientOriginalExtension();
        $path = $request->file('slip')->storeAs('payment-slips', $filename, 'public');

        $booking->update([
            'payment_slip' => $path,
            'payment_uploaded_at' => now(),
        ]);

        $this->notifyAdmins($booking);

        return redirect()->route('booking.thankyou', $booking)
            ->with('status', 'อัปโหลดสลิปเรียบร้อยแล้ว เจ้าหน้าที่จะตรวจสอบและยืนยันการจองโดยเร็วที่สุด');
    }

    private function notifyAdmins(Booking $booking): void
    {
        $booking->load('unit.accommodationType');

        try {
            foreach (User::pluck('email') as $email) {
                Mail::to($email)->send(new PaymentSlipUploaded($booking));
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send payment slip notification email: '.$e->getMessage());
        }
    }
}
