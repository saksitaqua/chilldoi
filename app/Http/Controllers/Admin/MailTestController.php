<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\BookingNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailTestController extends Controller
{
    public function index()
    {
        return view('admin.mail-test.index', [
            'settings' => $this->settings(),
            'warnings' => $this->warnings(),
            'result' => session('mail_result'),
        ]);
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'to' => 'required|email|max:255',
        ]);

        try {
            Mail::raw(
                "นี่คืออีเมลทดสอบจากระบบ Chill Doi Camping & Farm\n\nถ้าคุณได้รับอีเมลนี้ แสดงว่าการตั้งค่าอีเมลของเว็บไซต์ใช้งานได้\nส่งเมื่อ ".now()->format('d/m/Y H:i:s'),
                fn ($message) => $message->to($data['to'])->subject('ทดสอบการส่งอีเมล - Chill Doi')
            );

            $result = ['ok' => true, 'message' => 'ส่งอีเมลทดสอบไปที่ '.$data['to'].' แล้ว (ระบบเมลรับคำสั่งส่งเรียบร้อย) กรุณาตรวจกล่องจดหมายและโฟลเดอร์สแปม'];
        } catch (\Throwable $e) {
            $result = ['ok' => false, 'message' => 'ส่งไม่สำเร็จ: '.$e->getMessage()];
        }

        return redirect()->route('admin.mail-test.index')->with('mail_result', $result)->withInput();
    }

    private function settings(): array
    {
        $mailer = config('mail.default');
        $config = config("mail.mailers.{$mailer}", []);

        return [
            'ตัวส่งอีเมล (MAIL_MAILER)' => $mailer,
            'เซิร์ฟเวอร์ (MAIL_HOST)' => $config['host'] ?? '-',
            'พอร์ต (MAIL_PORT)' => $config['port'] ?? '-',
            'การเข้ารหัส (MAIL_SCHEME)' => $config['scheme'] ?? '(ค่าเริ่มต้น)',
            'ตรวจใบรับรองเซิร์ฟเวอร์ (MAIL_VERIFY_PEER)' => ($config['verify_peer'] ?? true) ? 'เปิด (ปลอดภัย)' : 'ปิด',
            'ชื่อผู้ใช้ (MAIL_USERNAME)' => ! empty($config['username']) ? $config['username'] : '(ไม่ได้ตั้งค่า)',
            'รหัสผ่าน (MAIL_PASSWORD)' => ! empty($config['password']) ? 'ตั้งค่าแล้ว (ไม่แสดง)' : '(ไม่ได้ตั้งค่า)',
            'ส่งจากอีเมล (MAIL_FROM_ADDRESS)' => config('mail.from.address'),
            'ผู้รับแจ้งเตือนการจอง (BOOKING_NOTIFY_EMAIL)' => implode(', ', BookingNotifier::recipients()) ?: '(ไม่มี)',
            'สำเนาลับ BCC (BOOKING_NOTIFY_BCC)' => implode(', ', BookingNotifier::bccRecipients()) ?: '(ไม่มี)',
            'ที่อยู่เว็บไซต์ (APP_URL)' => config('app.url'),
        ];
    }

    private function warnings(): array
    {
        $warnings = [];
        $mailer = config('mail.default');

        if (in_array($mailer, ['log', 'array'], true)) {
            $warnings[] = 'MAIL_MAILER ยังเป็น "'.$mailer.'" ระบบจึงไม่ส่งอีเมลจริง (แค่บันทึกลงไฟล์) ต้องเปลี่ยนเป็น smtp ในไฟล์ .env';
        }

        if ($mailer === 'smtp' && in_array(config('mail.mailers.smtp.host'), ['127.0.0.1', 'localhost'], true) && (int) config('mail.mailers.smtp.port') === 2525) {
            $warnings[] = 'MAIL_HOST/MAIL_PORT ยังเป็นค่าเริ่มต้น (127.0.0.1:2525) ซึ่งไม่ใช่เซิร์ฟเวอร์เมลจริง';
        }

        if ($mailer === 'smtp' && ! (config('mail.mailers.smtp.verify_peer') ?? true)) {
            $warnings[] = 'MAIL_VERIFY_PEER ถูกปิดอยู่ ระบบจะไม่ตรวจว่าเซิร์ฟเวอร์เมลเป็นเครื่องจริงหรือไม่ ใช้ได้เมื่อเมลเซิร์ฟเวอร์อยู่เครื่องเดียวกับเว็บ (เช่น MAIL_HOST=localhost) เท่านั้น ถ้าแก้ชื่อโฮสต์ให้ตรงกับใบรับรองได้ ควรเปิดกลับ';
        }

        if (str_contains((string) config('mail.from.address'), 'example.com')) {
            $warnings[] = 'MAIL_FROM_ADDRESS ยังเป็นค่าตัวอย่าง (example.com) ควรใช้อีเมลของโดเมนร้าน เช่น sales@chilldoicampingandfarm.com มิฉะนั้นอีเมลมักถูกปฏิเสธหรือเข้าสแปม';
        }

        if (str_contains((string) config('app.url'), 'localhost')) {
            $warnings[] = 'APP_URL ยังเป็น localhost ลิงก์ในอีเมลจะเปิดไม่ได้ ควรตั้งเป็น https://โดเมนจริงของร้าน';
        }

        return $warnings;
    }
}
