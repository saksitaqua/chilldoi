@php
    use App\Models\Payment;
    use App\Support\BahtText;
    use App\Support\ThaiDate;

    $s = $receipt->snapshot;
    $seller = $s['seller'];
    $stay = $s['stay'];
    $voided = $receipt->isVoided();
    $isAdminView = $mode === 'admin';
    $canManage = $isAdminView && auth()->user()?->isAdmin();
    $total = (float) $receipt->total_amount;
    $checkIn = \Carbon\Carbon::parse($stay['check_in']);
    $checkOut = \Carbon\Carbon::parse($stay['check_out']);
    $baht = fn ($n) => number_format((float) $n, 2);
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>ใบเสร็จรับเงิน {{ $receipt->receipt_no }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #e8ece9; color: #1b2421; font-family: 'Sarabun', 'Tahoma', sans-serif; font-size: 13px; line-height: 1.45; }
        .toolbar { max-width: 210mm; margin: 12px auto 0; padding: 0 4px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .toolbar .btn { border: 1px solid #c9d1cc; background: #fff; color: #1b2421; padding: 7px 14px; border-radius: 6px; font: inherit; font-size: 13px; cursor: pointer; text-decoration: none; }
        .toolbar .btn.primary { background: #047857; border-color: #047857; color: #fff; }
        .toolbar .btn.danger { color: #b91c1c; border-color: #f0b4b4; }
        .toolbar input[type=text] { border: 1px solid #c9d1cc; border-radius: 6px; padding: 7px 10px; font: inherit; font-size: 13px; }
        .flash { max-width: 210mm; margin: 10px auto 0; padding: 9px 14px; border-radius: 6px; font-size: 13px; }
        .flash.ok { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
        .flash.err { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
        .sheet { position: relative; width: 210mm; min-height: 297mm; margin: 12px auto 24px; background: #fff; padding: 14mm; box-shadow: 0 2px 14px rgba(0,0,0,.12); }
        .head { display: flex; justify-content: space-between; gap: 16px; border-bottom: 2px solid #1b2421; padding-bottom: 10px; }
        .brand h1 { margin: 0; font-size: 20px; }
        .brand p { margin: 1px 0; color: #374540; }
        .doc { text-align: right; }
        .doc h2 { margin: 0; font-size: 22px; letter-spacing: .5px; }
        .doc .sub { font-size: 14px; font-weight: 600; letter-spacing: 3px; }
        .doc table { margin-left: auto; margin-top: 6px; }
        .doc td { padding: 1px 0 1px 10px; text-align: right; }
        .doc td.k { color: #5b6963; }
        .note { margin-top: 6px; font-size: 11.5px; color: #5b6963; }
        .box { border: 1px solid #c9d1cc; border-radius: 4px; padding: 8px 10px; margin-top: 12px; display: flex; flex-wrap: wrap; gap: 4px 24px; }
        .box div { min-width: 46%; }
        .box .k { color: #5b6963; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 12px; }
        table.items th { background: #f1f4f2; border: 1px solid #c9d1cc; padding: 6px 8px; font-weight: 600; text-align: center; }
        table.items td { border: 1px solid #c9d1cc; padding: 6px 8px; vertical-align: top; }
        table.items td.num { text-align: right; white-space: nowrap; }
        table.items td.c { text-align: center; }
        .en { color: #5b6963; font-size: 11.5px; }
        table.totals { width: 58%; margin-left: auto; border-collapse: collapse; margin-top: 8px; }
        table.totals td { padding: 4px 8px; }
        table.totals td.num { text-align: right; white-space: nowrap; }
        table.totals tr.grand td { border-top: 2px solid #1b2421; border-bottom: 2px solid #1b2421; font-weight: 700; font-size: 15px; }
        .words { margin-top: 10px; border: 1px dashed #98a69f; border-radius: 4px; padding: 8px 10px; }
        .words b { font-weight: 600; }
        table.pay { width: 100%; border-collapse: collapse; margin-top: 12px; }
        table.pay th { background: #f1f4f2; border: 1px solid #c9d1cc; padding: 5px 8px; text-align: left; font-weight: 600; }
        table.pay td { border: 1px solid #c9d1cc; padding: 5px 8px; }
        table.pay td.num { text-align: right; }
        .sign { margin-top: 36px; display: flex; justify-content: flex-end; }
        .sign .col { width: 62mm; text-align: center; }
        .sign .line { border-bottom: 1px solid #1b2421; height: 34px; }
        .foot { margin-top: 18px; text-align: center; color: #5b6963; font-size: 12px; }
        .stamp { position: absolute; left: 50%; top: 46%; transform: translate(-50%, -50%) rotate(-24deg); font-size: 76px; font-weight: 700; color: rgba(185, 28, 28, .22); border: 8px solid rgba(185, 28, 28, .22); padding: 0 28px; border-radius: 12px; pointer-events: none; white-space: nowrap; }
        .voidbox { margin-top: 12px; border: 1px solid #fecaca; background: #fef2f2; color: #991b1b; border-radius: 4px; padding: 8px 10px; }
        @page { size: A4; margin: 12mm; }
        @media print {
            body { background: #fff; }
            .toolbar, .flash { display: none !important; }
            .sheet { margin: 0; width: auto; min-height: auto; padding: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        @if ($isAdminView)
            <a class="btn" href="{{ route('admin.bookings.edit', $receipt->booking_id) }}#payments">← กลับไปหน้าการจอง</a>
            <a class="btn" href="{{ route('admin.receipts.index') }}">รายการใบเสร็จ</a>
        @endif
        <button type="button" class="btn primary" onclick="window.print()">พิมพ์ / บันทึกเป็น PDF</button>

        @if ($isAdminView && ! $voided)
            <button type="button" class="btn" data-copy="{{ $receipt->customerUrl() }}">คัดลอกลิงก์สำหรับส่งให้ลูกค้า</button>
        @endif

        @if ($canManage && ! $voided)
            <form method="POST" action="{{ route('admin.receipts.void', $receipt) }}" style="display:flex; gap:6px; margin-left:auto;"
                  onsubmit="return confirm('ยืนยันยกเลิกใบเสร็จเลขที่ {{ $receipt->receipt_no }} ?\nระบบจะเก็บประวัติไว้ และเลขที่นี้จะไม่ถูกนำกลับมาใช้อีก');">
                @csrf
                <input type="text" name="void_reason" placeholder="เหตุผลที่ยกเลิก (จำเป็น)" required maxlength="255" value="{{ old('void_reason') }}">
                <button type="submit" class="btn danger">ยกเลิกใบเสร็จ (void)</button>
            </form>
        @endif
    </div>

    @if (session('status'))
        <div class="flash ok">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="flash err">{{ session('error') }}</div>
    @endif
    @if ($errors->void->any())
        <div class="flash err">{{ $errors->void->first() }}</div>
    @endif

    <div class="sheet">
        @if ($voided)
            <div class="stamp">ยกเลิก / VOID</div>
        @endif

        <div class="head">
            <div class="brand">
                <h1>{{ $seller['trade_name'] }}</h1>
                <p>{{ $seller['name_th'] }}@if (! empty($seller['name_en'])) / {{ $seller['name_en'] }}@endif</p>
                <p>{{ $seller['address_th'] }}</p>
                <p class="en">{{ $seller['address_en'] }}</p>
                <p>โทร / Tel: {{ $seller['phone'] }}@if (! empty($seller['email'])) &nbsp;·&nbsp; {{ $seller['email'] }}@endif</p>
                @if (! empty($seller['tax_id']))
                    <p>เลขประจำตัวผู้เสียภาษี / Tax ID: {{ $seller['tax_id'] }}</p>
                @endif
            </div>
            <div class="doc">
                <h2>ใบเสร็จรับเงิน</h2>
                <div class="sub">RECEIPT</div>
                <table>
                    <tr><td class="k">เลขที่ / No.</td><td><strong>{{ $receipt->receipt_no }}</strong></td></tr>
                    <tr><td class="k">วันที่ / Date</td><td>{{ ThaiDate::long($receipt->issued_at) }}<br><span class="en">{{ ThaiDate::longEnglish($receipt->issued_at) }}</span></td></tr>
                    <tr><td class="k">อ้างอิงการจอง / Booking ref.</td><td>#{{ $stay['booking_id'] }}</td></tr>
                </table>
            </div>
        </div>
        <div class="note">ผู้ขายไม่ได้จดทะเบียนภาษีมูลค่าเพิ่ม / The seller is not registered for VAT.</div>

        <div class="box">
            <div><span class="k">ได้รับเงินจาก / Received from:</span> <strong>{{ $receipt->customer_name }}</strong></div>
            <div><span class="k">โทร / Tel:</span> {{ $receipt->customer_phone ?: '-' }}</div>
            <div><span class="k">ที่พัก / Accommodation:</span> {{ $stay['unit'] }} ({{ $stay['type_th'] }} / {{ $stay['type_en'] }})</div>
            <div><span class="k">อีเมล / Email:</span> {{ $receipt->customer_email ?: '-' }}</div>
            <div><span class="k">เข้าพัก / Check-in:</span> {{ ThaiDate::short($checkIn) }} <span class="en">({{ $checkIn->format('d M Y') }})</span></div>
            <div><span class="k">ออก / Check-out:</span> {{ ThaiDate::short($checkOut) }} <span class="en">({{ $checkOut->format('d M Y') }})</span> · {{ $stay['nights'] }} คืน / night(s) · {{ $stay['guests'] }} คน / guest(s)</div>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th style="width:32px;">#<br><span class="en">No.</span></th>
                    <th>รายการ<br><span class="en">Description</span></th>
                    <th style="width:62px;">จำนวน<br><span class="en">Qty</span></th>
                    <th style="width:92px;">ราคา/หน่วย<br><span class="en">Unit price</span></th>
                    <th style="width:100px;">จำนวนเงิน (บาท)<br><span class="en">Amount (THB)</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($s['lines'] as $i => $line)
                    <tr>
                        <td class="c">{{ $i + 1 }}</td>
                        <td>{{ $line['th'] }}<br><span class="en">{{ $line['en'] }}</span></td>
                        <td class="c">{{ $line['qty'] }}</td>
                        <td class="num">{{ $line['unit_price'] > 0 ? $baht($line['unit_price']) : 'ฟรี / Free' }}</td>
                        <td class="num">{{ $baht($line['amount']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tr><td>รวมตามรายการ / Subtotal</td><td class="num">{{ $baht($s['subtotal']) }}</td></tr>
            @if (abs($s['adjustment']) >= 0.01)
                <tr>
                    <td>{{ $s['adjustment'] < 0 ? 'ส่วนลด / Discount' : 'ปรับปรุงเพิ่ม / Adjustment' }}
                        @if ($receipt->adjustment_note)<br><span class="en">{{ $receipt->adjustment_note }}</span>@endif
                    </td>
                    <td class="num">{{ $s['adjustment'] < 0 ? '-' : '+' }}{{ $baht(abs($s['adjustment'])) }}</td>
                </tr>
            @endif
            <tr class="grand"><td>รวมที่รับชำระ / Total received</td><td class="num">{{ $baht($total) }}</td></tr>
        </table>

        <div class="words">
            <div><b>จำนวนเงินเป็นตัวอักษร:</b> {{ BahtText::thai($total) }}</div>
            <div class="en"><b>In words:</b> {{ BahtText::english($total) }}</div>
        </div>

        <table class="pay">
            <thead>
                <tr>
                    <th>วันที่รับเงิน / Date</th>
                    <th>ช่องทาง / Method</th>
                    <th>อ้างอิง / Reference</th>
                    <th style="text-align:right;">จำนวนเงิน / Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($s['payments'] as $payment)
                    @php $paidAt = \Carbon\Carbon::parse($payment['paid_at']); $method = Payment::METHODS[$payment['method']] ?? ['th' => $payment['method'], 'en' => $payment['method']]; @endphp
                    <tr>
                        <td>{{ ThaiDate::short($paidAt) }} {{ $paidAt->format('H:i') }}</td>
                        <td>{{ $method['th'] }} / {{ $method['en'] }}</td>
                        <td>{{ $payment['reference'] ?: '-' }}</td>
                        <td class="num">{{ $baht($payment['amount']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($receipt->replaces)
            <div class="note">ใบเสร็จนี้ออกแทนใบเสร็จเลขที่ {{ $receipt->replaces->receipt_no }} ซึ่งถูกยกเลิก / This receipt replaces voided receipt no. {{ $receipt->replaces->receipt_no }}.</div>
        @endif

        @if ($voided)
            <div class="voidbox">
                <strong>ใบเสร็จนี้ถูกยกเลิก / This receipt has been VOIDED</strong><br>
                เมื่อ / On: {{ ThaiDate::short($receipt->voided_at) }} {{ $receipt->voided_at->format('H:i') }}
                · เหตุผล / Reason: {{ $receipt->void_reason }}
            </div>
        @endif

        <div class="sign">
            <div class="col">
                <div class="line"></div>
                <div>({{ $seller['name_th'] }})</div>
                <div>ผู้รับเงิน / Received by</div>
                <div class="en">วันที่ / Date: {{ ThaiDate::short($receipt->issued_at) }}</div>
            </div>
        </div>

        <div class="foot">ขอบคุณที่ใช้บริการ / Thank you for staying with us</div>
    </div>

    <script>
        document.querySelectorAll('[data-copy]').forEach(function (button) {
            button.addEventListener('click', function () {
                var url = button.dataset.copy;
                var done = function () { button.textContent = 'คัดลอกลิงก์แล้ว ✓'; };

                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(url).then(done, function () { window.prompt('คัดลอกลิงก์นี้ส่งให้ลูกค้า', url); });
                } else {
                    window.prompt('คัดลอกลิงก์นี้ส่งให้ลูกค้า', url);
                }
            });
        });
    </script>
</body>
</html>
