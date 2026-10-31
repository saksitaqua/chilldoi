<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Receipt;
use App\Services\ReceiptService;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function index(Request $request)
    {
        $receipts = Receipt::with('booking.unit')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->string('q')->value();
                $query->where(function ($sub) use ($q) {
                    $sub->where('receipt_no', 'like', "%{$q}%")
                        ->orWhere('customer_name', 'like', "%{$q}%")
                        ->orWhere('customer_phone', 'like', "%{$q}%");
                });
            })
            ->when($request->input('status') === 'active', fn ($query) => $query->whereNull('voided_at'))
            ->when($request->input('status') === 'void', fn ($query) => $query->whereNotNull('voided_at'))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.receipts.index', compact('receipts'));
    }

    public function show(Receipt $receipt)
    {
        return view('receipts.print', ['receipt' => $receipt, 'mode' => 'admin']);
    }

    public function issue(Request $request, Booking $booking, ReceiptService $service)
    {
        $data = $request->validateWithBag('receipt', [
            'adjustment_note' => 'nullable|string|max:500',
        ]);

        try {
            $receipt = $service->issue($booking, $request->user(), $data['adjustment_note'] ?? null);
        } catch (\DomainException $e) {
            return redirect()->to(route('admin.bookings.edit', $booking).'#payments')
                ->withInput()
                ->with('payment_error', $e->getMessage());
        }

        return redirect()->route('admin.receipts.show', $receipt)
            ->with('status', "ออกใบเสร็จเลขที่ {$receipt->receipt_no} เรียบร้อยแล้ว");
    }

    public function void(Request $request, Receipt $receipt, ReceiptService $service)
    {
        $data = $request->validateWithBag('void', [
            'void_reason' => 'required|string|max:255',
        ], [
            'void_reason.required' => 'กรุณาระบุเหตุผลที่ยกเลิกใบเสร็จ',
        ]);

        try {
            $service->voidReceipt($receipt, $request->user(), $data['void_reason']);
        } catch (\DomainException $e) {
            return redirect()->route('admin.receipts.show', $receipt)->with('error', $e->getMessage());
        }

        return redirect()->route('admin.receipts.show', $receipt)
            ->with('status', "ยกเลิกใบเสร็จเลขที่ {$receipt->receipt_no} แล้ว (เก็บประวัติไว้ เลขที่นี้จะไม่ถูกนำกลับมาใช้) ออกใบใหม่ได้จากหน้าแก้ไขการจอง");
    }
}
