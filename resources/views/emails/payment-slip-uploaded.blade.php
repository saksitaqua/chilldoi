<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; color: #1b2421; line-height: 1.6;">
    <h2>มีสลิปการชำระเงินใหม่</h2>
    <p>ลูกค้า <strong>{{ $booking->guest_name }}</strong> ({{ $booking->guest_phone }}) ได้แนบสลิปการโอนเงินสำหรับการจองนี้แล้ว</p>

    <table style="border-collapse: collapse; margin: 16px 0;">
        <tr><td style="padding: 4px 12px 4px 0; color: #666;">ที่พัก</td><td>{{ $booking->unit->name }} ({{ $booking->unit->accommodationType->name }})</td></tr>
        <tr><td style="padding: 4px 12px 4px 0; color: #666;">เช็คอิน</td><td>{{ $booking->check_in->format('d/m/Y') }}</td></tr>
        <tr><td style="padding: 4px 12px 4px 0; color: #666;">เช็คเอาท์</td><td>{{ $booking->check_out->format('d/m/Y') }}</td></tr>
        <tr><td style="padding: 4px 12px 4px 0; color: #666;">จำนวนคน</td><td>{{ $booking->guests }}</td></tr>
    </table>

    @if ($booking->payment_slip_url)
        <p><img src="{{ $booking->payment_slip_url }}" alt="สลิปการโอนเงิน" style="max-width: 320px; border-radius: 8px; border: 1px solid #ddd;"></p>
    @endif

    <p>
        <a href="{{ route('admin.bookings.edit', $booking) }}" style="background: #047857; color: #fff; padding: 10px 18px; border-radius: 6px; text-decoration: none;">
            ตรวจสอบและยืนยันการจอง
        </a>
    </p>
</body>
</html>
