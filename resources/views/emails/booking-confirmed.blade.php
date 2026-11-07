<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; color: #1b2421; line-height: 1.6;">
    <h2 style="margin-bottom: 4px;">{{ $booking->status === 'confirmed' ? 'ยืนยันการจองเรียบร้อยแล้ว' : 'ใบเสร็จรับเงินการจองของคุณ' }}</h2>
    <p style="margin-top: 0; color: #666;">Your booking is confirmed</p>

    <p>เรียนคุณ <strong>{{ $booking->guest_name }}</strong></p>
    <p>
        ขอบคุณที่เลือกพักกับ <strong>{{ $seller['trade_name'] }}</strong>
        {{ $booking->status === 'confirmed' ? 'ทางเราขอยืนยันการจองของคุณตามรายละเอียดด้านล่างนี้' : 'รายละเอียดการจองของคุณมีดังนี้' }}
    </p>

    <table style="border-collapse: collapse; margin: 16px 0;">
        <tr><td style="padding: 4px 16px 4px 0; color: #666;">หมายเลขการจอง</td><td><strong>#{{ $booking->id }}</strong></td></tr>
        <tr><td style="padding: 4px 16px 4px 0; color: #666;">ที่พัก</td><td>{{ $booking->unit->name }} ({{ $booking->unit->accommodationType->name }})</td></tr>
        <tr><td style="padding: 4px 16px 4px 0; color: #666;">เช็คอิน</td><td>{{ $booking->check_in->format('d/m/Y') }}</td></tr>
        <tr><td style="padding: 4px 16px 4px 0; color: #666;">เช็คเอาท์</td><td>{{ $booking->check_out->format('d/m/Y') }} ({{ $booking->check_in->diffInDays($booking->check_out) }} คืน)</td></tr>
        <tr><td style="padding: 4px 16px 4px 0; color: #666;">จำนวนคน</td><td>{{ $booking->guests }}</td></tr>
        @if ($booking->activities->count())
            <tr><td style="padding: 4px 16px 4px 0; color: #666;">กิจกรรม</td><td>{{ $booking->activities->pluck('name')->join(', ') }}</td></tr>
        @endif
        @if ($booking->services->count())
            <tr><td style="padding: 4px 16px 4px 0; color: #666;">บริการเสริม</td><td>{{ $booking->services->pluck('name')->join(', ') }}</td></tr>
        @endif
        <tr><td style="padding: 4px 16px 4px 0; color: #666;">ยอดรวมตามรายการ</td><td><strong>{{ number_format($billing['total'], 2) }} บาท</strong></td></tr>
    </table>

    @if ($booking->payment_slip)
        <p>ทางเราได้รับสลิปการชำระเงินของคุณแล้ว (แนบมาในอีเมลนี้เพื่อเป็นหลักฐาน)</p>
    @endif

    @if ($receipt)
        <p>
            ใบเสร็จรับเงินเลขที่ <strong>{{ $receipt->receipt_no }}</strong>
            ยอดรับชำระ <strong>{{ number_format((float) $receipt->total_amount, 2) }} บาท</strong>
        </p>
        <p>
            <a href="{{ $receipt->customerUrl() }}" style="background: #1b2421; color: #fff; padding: 10px 18px; border-radius: 6px; text-decoration: none;">
                เปิดดู / พิมพ์ใบเสร็จรับเงิน
            </a>
        </p>
    @endif

    <p>
        หากข้อมูลไม่ถูกต้อง หรือต้องการเปลี่ยนแปลงการจอง กรุณาติดต่อเราได้ที่
        โทร {{ $seller['phone'] }}
        @if (! empty($seller['email'])) หรืออีเมล {{ $seller['email'] }} @endif
    </p>

    <p>
        <a href="{{ route('map.index') }}" style="background: #047857; color: #fff; padding: 10px 18px; border-radius: 6px; text-decoration: none;">
            ดูแผนที่และวิธีเดินทาง
        </a>
    </p>

    <p style="margin-top: 28px;">ขอบคุณที่ไว้วางใจ เราหวังว่าจะได้ต้อนรับคุณเร็ว ๆ นี้ 🌿</p>
    <p style="color: #666; font-size: 13px;">
        Thank you for booking with us. If anything above is incorrect, please contact us at {{ $seller['phone'] }}.
    </p>
</body>
</html>
