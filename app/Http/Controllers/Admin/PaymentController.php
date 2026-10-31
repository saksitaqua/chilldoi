<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\ReceiptService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function store(Request $request, Booking $booking, ReceiptService $service)
    {
        $data = $request->validateWithBag('payment', [
            'amount' => 'required|numeric|min:0.01|max:10000000',
            'paid_at' => 'required|date',
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'reference' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:1000',
        ], [
            'amount.required' => 'กรุณากรอกยอดที่รับ',
            'amount.numeric' => 'ยอดที่รับต้องเป็นตัวเลข',
            'amount.min' => 'ยอดที่รับต้องมากกว่า 0',
            'paid_at.required' => 'กรุณาระบุวันที่รับเงิน',
            'paid_at.date' => 'รูปแบบวันที่รับเงินไม่ถูกต้อง',
        ]);

        try {
            $service->recordPayment($booking, $request->user(), $data);
        } catch (\DomainException $e) {
            return redirect()->to(route('admin.bookings.edit', $booking).'#payments')
                ->withInput()
                ->with('payment_error', $e->getMessage());
        }

        return redirect()->to(route('admin.bookings.edit', $booking).'#payments')
            ->with('payment_status', 'บันทึกการรับชำระเรียบร้อยแล้ว');
    }

    public function void(Request $request, Payment $payment, ReceiptService $service)
    {
        $data = $request->validateWithBag('void', [
            'void_reason' => 'required|string|max:255',
        ], [
            'void_reason.required' => 'กรุณาระบุเหตุผลที่ยกเลิก',
        ]);

        try {
            $service->voidPayment($payment, $request->user(), $data['void_reason']);
        } catch (\DomainException $e) {
            return redirect()->to(route('admin.bookings.edit', $payment->booking_id).'#payments')
                ->with('payment_error', $e->getMessage());
        }

        return redirect()->to(route('admin.bookings.edit', $payment->booking_id).'#payments')
            ->with('payment_status', 'ยกเลิกรายการรับชำระแล้ว (เก็บประวัติไว้ ไม่ได้ลบ)');
    }
}
