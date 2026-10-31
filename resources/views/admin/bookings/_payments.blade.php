@php
    use App\Models\Payment;

    $canManage = auth()->user()->isAdmin();
    $payments = $booking->payments()->with('receiver')->get();
    $receipts = $booking->receipts()->with('issuer')->get();
    $mismatch = abs($pay['balance']) >= 0.01;
    $baht = fn ($n) => number_format((float) $n, 2);

    if ($pay['paid'] <= 0) {
        $badge = ['ยังไม่มีการรับชำระ', 'bg-gray-100 text-gray-600'];
    } elseif ($pay['fully_paid']) {
        $badge = [$pay['balance'] < -0.009 ? 'รับครบ (รับเกินราคาระบบ)' : 'รับครบแล้ว', 'bg-emerald-100 text-emerald-800'];
    } else {
        $badge = ['รับบางส่วน', 'bg-yellow-100 text-yellow-800'];
    }
@endphp

<div id="payments" class="mt-6 bg-white shadow-sm rounded-lg p-6 space-y-5">
    <div class="flex items-center justify-between gap-3">
        <h3 class="font-semibold text-lg">การชำระเงินและใบเสร็จ</h3>
        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $badge[1] }}">{{ $badge[0] }}</span>
    </div>

    @if (session('payment_status'))
        <div class="rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">{{ session('payment_status') }}</div>
    @endif
    @if (session('payment_error'))
        <div class="rounded-md bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">{{ session('payment_error') }}</div>
    @endif
    @foreach (['payment', 'receipt', 'void'] as $bag)
        @if ($errors->$bag->any())
            <div class="rounded-md bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">{{ $errors->$bag->first() }}</div>
        @endif
    @endforeach

    <div class="grid grid-cols-3 gap-3 text-sm">
        <div class="rounded-md bg-gray-50 p-3">
            <p class="text-xs text-gray-500">ยอดตามราคาในระบบ</p>
            <p class="text-lg font-semibold">{{ $baht($pay['due']) }}</p>
        </div>
        <div class="rounded-md bg-gray-50 p-3">
            <p class="text-xs text-gray-500">รับชำระแล้ว</p>
            <p class="text-lg font-semibold text-emerald-700">{{ $baht($pay['paid']) }}</p>
        </div>
        <div class="rounded-md bg-gray-50 p-3">
            <p class="text-xs text-gray-500">{{ $pay['balance'] < -0.009 ? 'รับเกิน' : 'คงเหลือ' }}</p>
            <p class="text-lg font-semibold {{ $pay['balance'] > 0.009 ? 'text-red-600' : '' }}">{{ $baht(abs($pay['balance'])) }}</p>
        </div>
    </div>

    <details class="text-sm">
        <summary class="cursor-pointer text-emerald-700">ดูรายการที่ใช้คิดยอด</summary>
        <table class="mt-2 w-full text-sm">
            @foreach ($pay['billing']['lines'] as $line)
                <tr class="border-b border-gray-100">
                    <td class="py-1 pr-2">{{ $line['th'] }}</td>
                    <td class="py-1 px-2 text-right text-gray-500 whitespace-nowrap">{{ $line['qty'] }} × {{ $baht($line['unit_price']) }}</td>
                    <td class="py-1 pl-2 text-right whitespace-nowrap">{{ $baht($line['amount']) }}</td>
                </tr>
            @endforeach
        </table>
        @if ($booking->payment_slip_url)
            <p class="mt-2 text-xs text-gray-500">ลูกค้าแนบสลิปแล้วเมื่อ {{ $booking->payment_uploaded_at?->format('d/m/Y H:i') }} (ดูรูปสลิปด้านบนในฟอร์มแก้ไขการจอง)</p>
        @endif
    </details>

    <div>
        <h4 class="font-medium text-sm mb-2">รายการรับชำระ</h4>
        @if ($payments->isEmpty())
            <p class="text-sm text-gray-400">ยังไม่มีการบันทึกรับชำระ</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500">
                        <tr>
                            <th class="px-2 py-1 text-left">วันที่รับ</th>
                            <th class="px-2 py-1 text-right">ยอด</th>
                            <th class="px-2 py-1 text-left">ช่องทาง</th>
                            <th class="px-2 py-1 text-left">อ้างอิง</th>
                            <th class="px-2 py-1 text-left">ผู้บันทึก</th>
                            <th class="px-2 py-1"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($payments as $payment)
                            <tr @class(['text-gray-400 line-through' => $payment->isVoided()])>
                                <td class="px-2 py-1 whitespace-nowrap">{{ $payment->paid_at->format('d/m/Y H:i') }}</td>
                                <td class="px-2 py-1 text-right whitespace-nowrap">{{ $baht($payment->amount) }}</td>
                                <td class="px-2 py-1">{{ $payment->method_label }}</td>
                                <td class="px-2 py-1">{{ $payment->reference ?: '-' }}</td>
                                <td class="px-2 py-1">{{ $payment->receiver?->name ?? '-' }}</td>
                                <td class="px-2 py-1 text-right no-underline" style="text-decoration:none;">
                                    @if ($payment->isVoided())
                                        <span class="text-xs text-red-600" title="{{ $payment->void_reason }}">ยกเลิกแล้ว: {{ $payment->void_reason }}</span>
                                    @elseif ($canManage && $pay['can_record_payment'])
                                        <form method="POST" action="{{ route('admin.payments.void', $payment) }}" class="inline-flex gap-1"
                                              onsubmit="return confirm('ยกเลิกรายการรับชำระนี้? (เก็บประวัติไว้ ไม่ลบ)');">
                                            @csrf
                                            <input type="text" name="void_reason" required maxlength="255" placeholder="เหตุผล" class="w-28 border-gray-300 rounded-md text-xs py-1">
                                            <button type="submit" class="text-xs text-red-600 hover:underline">ยกเลิก</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($canManage && $pay['can_record_payment'])
        <form method="POST" action="{{ route('admin.payments.store', $booking) }}" class="border-t border-gray-100 pt-4 space-y-3">
            @csrf
            <h4 class="font-medium text-sm">บันทึกการรับชำระ</h4>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">ยอดที่รับ (บาท)</label>
                    <input type="number" name="amount" step="0.01" min="0.01" required
                           value="{{ old('amount', $pay['balance'] > 0 ? number_format($pay['balance'], 2, '.', '') : '') }}" class="w-full border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">วันและเวลาที่รับเงิน</label>
                    <input type="datetime-local" name="paid_at" required value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}" class="w-full border-gray-300 rounded-md">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">ช่องทาง</label>
                    <select name="method" class="w-full border-gray-300 rounded-md">
                        @foreach (Payment::METHODS as $key => $label)
                            <option value="{{ $key }}" @selected(old('method', 'transfer') === $key)>{{ $label['th'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">เลขอ้างอิง / ธนาคาร (ไม่บังคับ)</label>
                    <input type="text" name="reference" maxlength="255" value="{{ old('reference') }}" class="w-full border-gray-300 rounded-md">
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">หมายเหตุ (ไม่บังคับ)</label>
                <input type="text" name="note" maxlength="1000" value="{{ old('note') }}" class="w-full border-gray-300 rounded-md">
            </div>
            <button type="submit" class="bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-800">บันทึกการรับชำระ</button>
        </form>
    @endif

    <div class="border-t border-gray-100 pt-4">
        <h4 class="font-medium text-sm mb-2">ใบเสร็จรับเงิน</h4>

        @if ($receipts->isNotEmpty())
            <table class="min-w-full text-sm mb-3">
                <tbody class="divide-y divide-gray-100">
                    @foreach ($receipts as $receipt)
                        <tr>
                            <td class="py-1 pr-2 font-medium whitespace-nowrap">{{ $receipt->receipt_no }}</td>
                            <td class="py-1 px-2 text-gray-500 whitespace-nowrap">{{ $receipt->issued_at->format('d/m/Y H:i') }}</td>
                            <td class="py-1 px-2 text-right whitespace-nowrap">{{ $baht($receipt->total_amount) }}</td>
                            <td class="py-1 px-2">
                                @if ($receipt->isVoided())
                                    <span class="px-2 py-0.5 rounded text-xs bg-red-100 text-red-800" title="{{ $receipt->void_reason }}">ยกเลิกแล้ว</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-800">ใช้งาน</span>
                                @endif
                            </td>
                            <td class="py-1 pl-2 text-right"><a href="{{ route('admin.receipts.show', $receipt) }}" class="text-emerald-700 hover:underline">ดู / พิมพ์</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-sm text-gray-400 mb-3">ยังไม่ได้ออกใบเสร็จ</p>
        @endif

        @if ($canManage && $pay['can_issue'])
            <form method="POST" action="{{ route('admin.receipts.issue', $booking) }}" class="space-y-2"
                  onsubmit="return confirm('ออกใบเสร็จ ยอดรับชำระ {{ $baht($pay['paid']) }} บาท ?\nออกแล้วจะแก้ไขรายการรับชำระไม่ได้ (ต้องยกเลิกใบเสร็จก่อน)');">
                @csrf
                @if ($mismatch)
                    <div class="rounded-md bg-yellow-50 border border-yellow-200 text-yellow-800 px-3 py-2 text-sm">
                        ยอดที่รับ ({{ $baht($pay['paid']) }}) ไม่ตรงกับราคาในระบบ ({{ $baht($pay['due']) }}) —
                        ใบเสร็จออกได้เมื่อรับครบ หรือถ้าตกลงราคาต่างจากระบบ (เช่น ส่วนลด) ให้ระบุเหตุผลด้านล่าง
                    </div>
                    <input type="text" name="adjustment_note" maxlength="500" placeholder="เหตุผลที่ยอดต่างจากราคาระบบ เช่น ส่วนลดลูกค้าประจำ" value="{{ old('adjustment_note') }}" class="w-full border-gray-300 rounded-md text-sm">
                @endif
                <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-700">
                    ออกใบเสร็จรับเงิน{{ $mismatch ? ' (ยอดต่างจากราคาระบบ)' : '' }}
                </button>
            </form>
        @elseif ($pay['active_receipt'])
            <p class="text-xs text-gray-500">มีใบเสร็จที่ใช้งานอยู่ ถ้าต้องการแก้ไข ให้ยกเลิก (void) ใบเสร็จนั้นในหน้าใบเสร็จก่อน แล้วออกใบใหม่</p>
        @elseif ($booking->status === 'cancelled')
            <p class="text-xs text-gray-500">การจองนี้ถูกยกเลิก จึงออกใบเสร็จไม่ได้</p>
        @elseif ($pay['paid'] <= 0)
            <p class="text-xs text-gray-500">บันทึกการรับชำระก่อน จึงจะออกใบเสร็จได้</p>
        @endif

        @unless ($canManage)
            <p class="text-xs text-gray-400 mt-2">เฉพาะผู้ดูแลระบบ (admin) เท่านั้นที่บันทึกการรับชำระและออกใบเสร็จได้</p>
        @endunless
    </div>
</div>
