<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\ReceiptService;
use App\Support\BookingNotifier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * One click for the usual case: the customer's slip is good, so confirm the booking, record the payment,
 * issue the receipt and email the customer. Admin only, like recording payments and issuing receipts.
 */
class QuickConfirmController extends Controller
{
    public function __invoke(Request $request, Booking $booking, ReceiptService $service)
    {
        $edit = route('admin.bookings.edit', $booking);

        if ($booking->status === 'cancelled') {
            return redirect($edit)->with('error', 'การจองนี้ถูกยกเลิกแล้ว ใช้ปุ่มลัดไม่ได้');
        }

        if ($booking->receipts()->active()->exists()) {
            return redirect($edit)->with('error', 'การจองนี้ออกใบเสร็จแล้ว ถ้าต้องการส่งใบเสร็จให้ลูกค้า ใช้ปุ่ม "ส่งใบเสร็จทางอีเมล" ในหน้าใบเสร็จ');
        }

        $summary = $service->summary($booking);
        $needsPayment = $summary['paid'] <= 0 || ! $summary['fully_paid'];

        $data = $request->validate([
            'amount' => [$needsPayment ? 'required' : 'nullable', 'numeric', 'min:0.01', 'max:10000000'],
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'adjustment_note' => 'nullable|string|max:500',
        ], [
            'amount.required' => 'กรุณากรอกยอดที่รับ',
            'amount.numeric' => 'ยอดที่รับต้องเป็นตัวเลข',
            'amount.min' => 'ยอดที่รับต้องมากกว่า 0',
        ]);

        // Decide up front, before anything is saved, whether the receipt will need a reason for a price difference.
        $received = round($summary['paid'] + (float) ($data['amount'] ?? 0), 2);
        $difference = round($received - $summary['due'], 2);
        $note = trim((string) ($data['adjustment_note'] ?? ''));

        if (abs($difference) >= 0.01 && $note === '') {
            return redirect($edit)->withInput()->withErrors([
                'adjustment_note' => 'ยอดที่รับรวม '.number_format($received, 2).' บาท ไม่เท่ายอดตามระบบ '.number_format($summary['due'], 2)
                    .' บาท (ต่าง '.number_format(abs($difference), 2).') กรุณาระบุเหตุผล เช่น ส่วนลด หรือมัดจำ แล้วกดอีกครั้ง',
            ]);
        }

        if ($booking->status !== 'confirmed') {
            $booking->update(['status' => 'confirmed']);
        }

        try {
            if (! empty($data['amount'])) {
                $service->recordPayment($booking, $request->user(), [
                    'amount' => $data['amount'],
                    'paid_at' => now()->toDateTimeString(),
                    'method' => $data['method'],
                    'note' => 'บันทึกจากปุ่มลัด ยืนยัน+ออกใบเสร็จ',
                ]);
            }

            $receipt = $service->issue($booking->refresh(), $request->user(), $note ?: null);
        } catch (\DomainException $e) {
            return redirect($edit)->with('error', $e->getMessage());
        }

        $result = BookingNotifier::bookingConfirmed($booking, $receipt);
        $message = 'ยืนยันการจอง บันทึกรับชำระ และออกใบเสร็จเลขที่ '.$receipt->receipt_no.' เรียบร้อยแล้ว · ';

        $redirect = redirect($edit);

        return match ($result) {
            'sent' => $redirect->with('status', $message."ส่งอีเมลถึงลูกค้าที่ {$booking->guest_email} แล้ว"),
            'no_email' => $redirect->with('status', $message.'ลูกค้าไม่ได้ระบุอีเมล กรุณาส่งลิงก์ใบเสร็จด้านล่างให้ลูกค้าเอง'.($booking->guest_phone ? " (โทร {$booking->guest_phone})" : ''))
                ->with('customer_link', $receipt->customerUrl()),
            default => $redirect->with('status', $message.'แต่ส่งอีเมลไม่สำเร็จ กรุณาส่งลิงก์ใบเสร็จด้านล่างให้ลูกค้าเอง หรือกดส่งอีเมลอีกครั้งในหน้าใบเสร็จ')
                ->with('customer_link', $receipt->customerUrl()),
        };
    }
}
