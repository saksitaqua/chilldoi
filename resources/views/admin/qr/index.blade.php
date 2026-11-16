<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">QR เข้าเว็บไซต์</h2>
    </x-slot>

    <style>
        .qr-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px; }
        .qr-item { display: flex; flex-direction: column; align-items: center; gap: 10px; }
        .qr-card { width: 100%; max-width: 320px; text-align: center; box-sizing: border-box; }
        .qr-card svg { display: block; width: 100%; height: auto; }
        .qr-title { font-weight: 700; line-height: 1.3; }
        .qr-sub { font-size: 14px; }
        .qr-url { font-size: 11px; word-break: break-all; opacity: .75; }

        .s1 { background: #fff; border: 1px solid #d1d5db; padding: 16px; }
        .s2 { background: #fff; border: 6px solid #047857; border-radius: 20px; padding: 22px 18px; color: #064e3b; }
        .s2 .qr-title { font-size: 20px; color: #047857; margin-bottom: 4px; }
        .s3 { background: #1b2421; color: #fff; border-radius: 20px; padding: 24px 20px; }
        .s3 .qr-box { background: #fff; border-radius: 14px; padding: 10px; margin: 12px 0; }
        .s3 .qr-title { font-size: 20px; }
        .s4 { background: #fef3c7; border: 3px dashed #b45309; border-radius: 12px; padding: 18px; color: #78350f; }
        .s4 .qr-box { background: #fff; padding: 8px; border-radius: 8px; margin: 10px 0; }
        .s4 .qr-title { font-size: 18px; }

        @media print {
            .no-print { display: none !important; }
            body.printing-one .qr-item:not(.print-me), body.printing-one header, body.printing-one nav { display: none !important; }
            body.printing-one .qr-card { max-width: 360px; margin: 0 auto; }
            body.printing-one .qr-item { display: block; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="no-print bg-white shadow-sm rounded-lg p-6 text-sm text-gray-600 space-y-2">
            <p>QR ทั้ง 4 แบบสแกนแล้วเข้าหน้าแรกของเว็บไซต์เดียวกัน: <span class="font-mono text-gray-900">{{ $url }}</span></p>
            <p>เลือกแบบที่ชอบแล้วกด "พิมพ์แบบนี้" หรือ "ดาวน์โหลด QR (SVG)" ไปใช้กับป้าย สติกเกอร์ หรือโปสเตอร์</p>
            @if (str_contains($url, 'localhost') || str_contains($url, '127.0.0.1'))
                <p class="text-amber-800 bg-amber-50 rounded-md px-3 py-2">ตอนนี้ลิงก์เป็นที่อยู่ในเครื่องทดสอบ บนเซิร์ฟเวอร์จริงให้ตั้ง <code>APP_URL</code> เป็นโดเมนจริงก่อนพิมพ์ ไม่เช่นนั้น QR จะพาไปผิดที่</p>
            @endif
        </div>

        <div class="qr-grid">
            <div class="qr-item">
                <div class="qr-card s1">{!! $qr !!}</div>
                <div class="text-sm font-medium text-gray-700">แบบ 1: QR เปล่า (เรียบ ใช้ได้ทุกที่)</div>
                <div class="no-print"><button type="button" class="px-3 py-1.5 bg-gray-800 text-white rounded-md text-xs" onclick="printOne(this)">พิมพ์แบบนี้</button></div>
            </div>

            <div class="qr-item">
                <div class="qr-card s2">
                    <div class="qr-title">{{ $seller['trade_name'] }}</div>
                    <div class="qr-sub" style="margin-bottom:8px;">สแกนเพื่อดูที่พักและจองออนไลน์</div>
                    {!! $qr !!}
                    <div class="qr-sub" style="margin-top:8px;">โทร {{ $seller['phone'] }}</div>
                </div>
                <div class="text-sm font-medium text-gray-700">แบบ 2: กรอบเขียว พร้อมชื่อร้านและเบอร์โทร</div>
                <div class="no-print"><button type="button" class="px-3 py-1.5 bg-gray-800 text-white rounded-md text-xs" onclick="printOne(this)">พิมพ์แบบนี้</button></div>
            </div>

            <div class="qr-item">
                <div class="qr-card s3">
                    <div class="qr-title">จองที่พักออนไลน์</div>
                    <div class="qr-sub">{{ $seller['trade_name'] }}</div>
                    <div class="qr-box">{!! $qr !!}</div>
                    <div class="qr-sub">สแกนด้วยกล้องมือถือ</div>
                    <div class="qr-url">{{ $url }}</div>
                </div>
                <div class="text-sm font-medium text-gray-700">แบบ 3: การ์ดสีเข้ม พร้อมลิงก์ตัวอักษร</div>
                <div class="no-print"><button type="button" class="px-3 py-1.5 bg-gray-800 text-white rounded-md text-xs" onclick="printOne(this)">พิมพ์แบบนี้</button></div>
            </div>

            <div class="qr-item">
                <div class="qr-card s4">
                    <div class="qr-title">🌿 Chill Doi Camping &amp; Farm</div>
                    <div class="qr-box">{!! $qr !!}</div>
                    <div class="qr-sub">สแกนดูที่พัก กิจกรรม และแผนที่</div>
                </div>
                <div class="text-sm font-medium text-gray-700">แบบ 4: ป้ายตั้งโต๊ะ ขอบประ สีอบอุ่น</div>
                <div class="no-print"><button type="button" class="px-3 py-1.5 bg-gray-800 text-white rounded-md text-xs" onclick="printOne(this)">พิมพ์แบบนี้</button></div>
            </div>
        </div>

        <div class="no-print">
            <a href="{{ route('admin.qr.download') }}" class="inline-block px-4 py-2 bg-emerald-700 text-white rounded-md text-sm hover:bg-emerald-800">ดาวน์โหลด QR (SVG) ไปทำป้ายเอง</a>
        </div>
    </div>

    <script>
        function printOne(btn) {
            var item = btn.closest('.qr-item');
            item.classList.add('print-me');
            document.body.classList.add('printing-one');
            window.print();
            setTimeout(function () {
                item.classList.remove('print-me');
                document.body.classList.remove('printing-one');
            }, 500);
        }
    </script>
</x-app-layout>
