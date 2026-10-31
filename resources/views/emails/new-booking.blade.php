<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; color: #1b2421; line-height: 1.6;">
    <h2>มีการจองใหม่เข้ามา</h2>
    <p>ลูกค้าส่งคำขอจองผ่านเว็บไซต์ สถานะ <strong>รอยืนยัน</strong> กรุณาตรวจสอบและติดต่อกลับ</p>

    <table style="border-collapse: collapse; margin: 16px 0;">
        <tr><td style="padding: 4px 12px 4px 0; color: #666;">ชื่อผู้จอง</td><td><strong>{{ $booking->guest_name }}</strong></td></tr>
        <tr><td style="padding: 4px 12px 4px 0; color: #666;">เบอร์โทร</td><td>{{ $booking->guest_phone }}</td></tr>
        <tr><td style="padding: 4px 12px 4px 0; color: #666;">อีเมล</td><td>{{ $booking->guest_email ?: '-' }}</td></tr>
        <tr><td style="padding: 4px 12px 4px 0; color: #666;">ที่พัก</td><td>{{ $booking->unit->name }} ({{ $booking->unit->accommodationType->name }})</td></tr>
        <tr><td style="padding: 4px 12px 4px 0; color: #666;">เช็คอิน</td><td>{{ $booking->check_in->format('d/m/Y') }}</td></tr>
        <tr><td style="padding: 4px 12px 4px 0; color: #666;">เช็คเอาท์</td><td>{{ $booking->check_out->format('d/m/Y') }} ({{ $booking->check_in->diffInDays($booking->check_out) }} คืน)</td></tr>
        <tr><td style="padding: 4px 12px 4px 0; color: #666;">จำนวนคน</td><td>{{ $booking->guests }}</td></tr>
        @if ($booking->activities->count())
            <tr><td style="padding: 4px 12px 4px 0; color: #666;">กิจกรรม</td><td>{{ $booking->activities->pluck('name')->join(', ') }}</td></tr>
        @endif
        @if ($booking->services->count())
            <tr><td style="padding: 4px 12px 4px 0; color: #666;">บริการเสริม</td><td>{{ $booking->services->pluck('name')->join(', ') }}</td></tr>
        @endif
        @if ($booking->notes)
            <tr><td style="padding: 4px 12px 4px 0; color: #666; vertical-align: top;">หมายเหตุ</td><td>{{ $booking->notes }}</td></tr>
        @endif
        <tr><td style="padding: 4px 12px 4px 0; color: #666;">เวลาที่จอง</td><td>{{ $booking->created_at->format('d/m/Y H:i') }}</td></tr>
    </table>

    <p>
        <a href="{{ route('admin.bookings.edit', $booking) }}" style="background: #047857; color: #fff; padding: 10px 18px; border-radius: 6px; text-decoration: none;">
            เปิดดูและยืนยันการจอง
        </a>
    </p>
    <p style="color: #888; font-size: 12px;">ตอบกลับอีเมลนี้เพื่อติดต่อลูกค้าได้โดยตรง (ถ้าลูกค้ากรอกอีเมลไว้)</p>
</body>
</html>
