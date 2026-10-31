<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\User;
use App\Support\ThaiDate;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Payments and receipts for a booking. Nothing here deletes anything: mistakes are voided with a reason,
 * and a receipt number is never reused.
 */
class ReceiptService
{
    public function summary(Booking $booking): array
    {
        $billing = $booking->billing();
        $paid = round((float) $booking->payments()->active()->sum('amount'), 2);
        $due = $billing['total'];
        $activeReceipt = $booking->receipts()->active()->first();

        return [
            'billing' => $billing,
            'due' => $due,
            'paid' => $paid,
            'balance' => round($due - $paid, 2),
            'fully_paid' => $paid > 0 && $this->satang($paid) >= $this->satang($due),
            'active_receipt' => $activeReceipt,
            'can_record_payment' => $booking->status !== 'cancelled' && ! $activeReceipt,
            'can_issue' => $booking->status !== 'cancelled' && ! $activeReceipt && $paid > 0,
        ];
    }

    public function recordPayment(Booking $booking, User $user, array $data): Payment
    {
        if ($booking->status === 'cancelled') {
            throw new \DomainException('การจองนี้ถูกยกเลิกแล้ว บันทึกการรับชำระไม่ได้');
        }

        if ($booking->receipts()->active()->exists()) {
            throw new \DomainException('การจองนี้ออกใบเสร็จแล้ว ถ้าต้องการเพิ่มหรือแก้การรับชำระ ให้ยกเลิก (void) ใบเสร็จก่อน');
        }

        return $booking->payments()->create([
            'amount' => round((float) $data['amount'], 2),
            'paid_at' => $data['paid_at'],
            'method' => $data['method'],
            'reference' => $data['reference'] ?? null,
            'note' => $data['note'] ?? null,
            'received_by' => $user->id,
        ]);
    }

    public function voidPayment(Payment $payment, User $user, string $reason): void
    {
        if ($payment->isVoided()) {
            throw new \DomainException('รายการรับชำระนี้ถูกยกเลิกไปแล้ว');
        }

        if ($payment->booking->receipts()->active()->exists()) {
            throw new \DomainException('การจองนี้ออกใบเสร็จแล้ว ต้องยกเลิก (void) ใบเสร็จก่อน จึงจะยกเลิกรายการรับชำระได้');
        }

        $payment->forceFill([
            'voided_at' => now(),
            'void_reason' => $reason,
            'voided_by' => $user->id,
        ])->save();
    }

    public function issue(Booking $booking, User $user, ?string $adjustmentNote = null): Receipt
    {
        $adjustmentNote = trim((string) $adjustmentNote) ?: null;

        for ($attempt = 1; ; $attempt++) {
            try {
                return DB::transaction(fn () => $this->createReceipt($booking->id, $user, $adjustmentNote));
            } catch (UniqueConstraintViolationException $e) {
                // Two receipts raced for the same running number: take the next one.
                if ($attempt >= 5) {
                    throw $e;
                }
            }
        }
    }

    public function voidReceipt(Receipt $receipt, User $user, string $reason): void
    {
        if ($receipt->isVoided()) {
            throw new \DomainException('ใบเสร็จนี้ถูกยกเลิกไปแล้ว');
        }

        $receipt->forceFill([
            'voided_at' => now(),
            'void_reason' => $reason,
            'voided_by' => $user->id,
        ])->save();
    }

    private function createReceipt(int $bookingId, User $user, ?string $adjustmentNote): Receipt
    {
        $booking = Booking::with('unit.accommodationType', 'activities', 'services')->lockForUpdate()->findOrFail($bookingId);

        if ($booking->status === 'cancelled') {
            throw new \DomainException('การจองนี้ถูกยกเลิกแล้ว ออกใบเสร็จไม่ได้');
        }

        if ($booking->receipts()->active()->exists()) {
            throw new \DomainException('การจองนี้มีใบเสร็จที่ใช้งานอยู่แล้ว ถ้าต้องการออกใหม่ ให้ยกเลิก (void) ใบเดิมก่อน');
        }

        $payments = $booking->payments()->active()->get();

        if ($payments->isEmpty()) {
            throw new \DomainException('ยังไม่มีการบันทึกรับชำระ กรุณาบันทึกการรับชำระก่อนออกใบเสร็จ');
        }

        $billing = $booking->billing();
        $received = round((float) $payments->sum('amount'), 2);
        $adjustment = round($received - $billing['total'], 2);

        if ($this->satang($adjustment) !== 0 && $adjustmentNote === null) {
            $state = $adjustment < 0
                ? 'ยังรับไม่ครบ ขาดอีก '.number_format(abs($adjustment), 2).' บาท'
                : 'รับเกินราคาในระบบ '.number_format($adjustment, 2).' บาท';

            throw new \DomainException("{$state} — ออกใบเสร็จได้เมื่อรับครบ หรือถ้าตกลงราคาต่างจากระบบ (เช่น ส่วนลด) ให้ระบุเหตุผลในช่องที่ขึ้นมา");
        }

        $now = now();
        $year = $now->year;
        $sequence = ((int) Receipt::where('issue_year', $year)->lockForUpdate()->max('sequence')) + 1;
        $lastReceipt = $booking->receipts()->first();

        return Receipt::create([
            'receipt_no' => sprintf('%s%02d-%02d%04d', config('receipt.prefix', 'IN'), ThaiDate::year($now) % 100, $now->month, $sequence),
            'issue_year' => $year,
            'sequence' => $sequence,
            'booking_id' => $booking->id,
            'replaces_id' => $lastReceipt?->isVoided() ? $lastReceipt->id : null,
            'issued_at' => $now,
            'total_amount' => $received,
            'adjustment_note' => $this->satang($adjustment) !== 0 ? $adjustmentNote : null,
            'customer_name' => $booking->guest_name,
            'customer_phone' => $booking->guest_phone,
            'customer_email' => $booking->guest_email,
            'issued_by' => $user->id,
            'snapshot' => [
                'seller' => config('receipt.seller'),
                'stay' => [
                    'unit' => $booking->unit->name,
                    'type_th' => $booking->unit->accommodationType->name,
                    'type_en' => $booking->unit->accommodationType->name_en ?: $booking->unit->accommodationType->name,
                    'check_in' => $booking->check_in->toDateString(),
                    'check_out' => $booking->check_out->toDateString(),
                    'nights' => $booking->nights(),
                    'guests' => $booking->guests,
                    'booking_id' => $booking->id,
                ],
                'lines' => $billing['lines'],
                'subtotal' => $billing['total'],
                'adjustment' => $adjustment,
                'payments' => $payments->map(fn (Payment $payment) => [
                    'paid_at' => $payment->paid_at->toDateTimeString(),
                    'amount' => (float) $payment->amount,
                    'method' => $payment->method,
                    'reference' => $payment->reference,
                ])->all(),
                'issued_by_name' => $user->name,
            ],
        ]);
    }

    private function satang(float|int $amount): int
    {
        return (int) round($amount * 100);
    }
}
