<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Flow การทำงาน</h2>
    </x-slot>

    @php
        // [title, who, detail, tone]
        $steps = [
            ['1', 'ลูกค้า', 'เลือกที่พักและวันที่ในหน้าเว็บ (หน้าแรก / เช็กห้องว่าง) แล้วกรอกชื่อ เบอร์โทร จำนวนคน และเลือกกิจกรรม/บริการเสริมที่ต้องการ', 'customer'],
            ['2', 'ระบบ', 'บันทึกการจองเป็นสถานะ "รอยืนยัน" และกันห้องวันนั้นไว้ทันที (คนอื่นจองซ้ำไม่ได้) มีตัวเลขสีแดงขึ้นที่เมนู "การจอง" ให้ทีมงานเห็น', 'system'],
            ['3', 'ทีมงาน', 'ติดต่อลูกค้าทางโทรศัพท์ ตรวจวันที่/จำนวนคน แล้วเปลี่ยนสถานะเป็น "ยืนยันแล้ว" ที่หน้าแก้ไขการจอง', 'staff'],
            ['4', 'ลูกค้า', 'โอนเงินตามยอด (สแกน QR พร้อมเพย์หรือโอนบัญชีในหน้าขอบคุณ) แล้วอัปโหลดสลิป ลูกค้าเปิดหน้าเดิมกลับมาอัปโหลดทีหลังได้', 'customer'],
            ['5', 'แอดมิน', 'ดูสลิปในหน้าแก้ไขการจอง ตรวจกับยอดเงินเข้าบัญชี แล้วกด "บันทึกการรับชำระ" (ทำได้เฉพาะแอดมิน) ยอดคงเหลือจะลดลงตามที่บันทึก', 'admin'],
            ['6', 'แอดมิน', 'กด "ออกใบเสร็จรับเงิน" ในหน้าแก้ไขการจอง (ต้องมีการบันทึกรับชำระก่อน) ใบเสร็จมีเลขที่รันต่อเนื่อง กด "ดู / พิมพ์" เพื่อพิมพ์ให้ลูกค้า', 'admin'],
            ['7', 'ทีมงาน', 'ลูกค้าเข้าพัก ดูสถานะห้องและการจองได้จากเมนู "ปฏิทิน" ส่วนรายรับ/รายจ่ายที่เกิดขึ้นจริงบันทึกใน "บันทึกทั่วไป"', 'staff'],
        ];
        $tones = [
            'customer' => 'bg-amber-50 border-amber-300 text-amber-800',
            'system' => 'bg-sky-50 border-sky-300 text-sky-800',
            'staff' => 'bg-emerald-50 border-emerald-300 text-emerald-800',
            'admin' => 'bg-violet-50 border-violet-300 text-violet-800',
        ];
    @endphp

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white shadow-sm rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-1">ขั้นตอนตั้งแต่ลูกค้าจองจนออกใบเสร็จ</h3>
            <p class="text-sm text-gray-500 mb-4">สีของป้ายบอกว่าใครเป็นคนทำในขั้นตอนนั้น</p>

            <div class="flex flex-wrap gap-2 text-xs mb-6">
                <span class="px-2 py-1 rounded-full border {{ $tones['customer'] }}">ลูกค้า</span>
                <span class="px-2 py-1 rounded-full border {{ $tones['system'] }}">ระบบอัตโนมัติ</span>
                <span class="px-2 py-1 rounded-full border {{ $tones['staff'] }}">ทีมงาน (ทุกคนที่ล็อกอิน)</span>
                <span class="px-2 py-1 rounded-full border {{ $tones['admin'] }}">เฉพาะแอดมิน</span>
            </div>

            <ol class="space-y-0">
                @foreach ($steps as [$no, $who, $detail, $tone])
                    <li class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <span class="flex items-center justify-center w-9 h-9 rounded-full border-2 font-semibold {{ $tones[$tone] }}">{{ $no }}</span>
                            @unless ($loop->last)
                                <span class="flex-1 w-px bg-gray-300 my-1"></span>
                            @endunless
                        </div>
                        <div class="pb-6 flex-1">
                            <span class="inline-block text-xs px-2 py-0.5 rounded-full border {{ $tones[$tone] }}">{{ $who }}</span>
                            <p class="mt-1 text-sm text-gray-700">{{ $detail }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>

        <div class="grid sm:grid-cols-2 gap-6">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="font-semibold text-gray-900 mb-3">สถานะของการจอง</h3>
                <ul class="space-y-3 text-sm text-gray-700">
                    <li><span class="inline-block px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-800 text-xs mr-2">รอยืนยัน</span>ลูกค้าจองเข้ามาแล้ว รอทีมงานติดต่อ</li>
                    <li><span class="inline-block px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs mr-2">ยืนยันแล้ว</span>ทีมงานคุยกับลูกค้าแล้ว ห้องถูกกันไว้</li>
                    <li><span class="inline-block px-2 py-0.5 rounded-full bg-gray-200 text-gray-700 text-xs mr-2">ยกเลิก</span>ไม่เข้าพักแล้ว ห้องกลับมาว่าง และไม่นับเป็น "ต้องดำเนินการ"</li>
                </ul>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="font-semibold text-gray-900 mb-3">ตัวเลขสีแดง "ต้องดำเนินการ"</h3>
                <p class="text-sm text-gray-700 mb-2">นับการจองที่ยังไม่ยกเลิก และเข้าข้อใดข้อหนึ่ง</p>
                <ul class="list-disc pl-5 text-sm text-gray-700 space-y-1">
                    <li>ยังเป็น "รอยืนยัน"</li>
                    <li>ลูกค้าอัปโหลดสลิปแล้ว แต่ยังไม่มีการบันทึกการชำระเงิน</li>
                </ul>
                <p class="text-xs text-gray-500 mt-3">กดตัวเลขเพื่อดูเฉพาะรายการเหล่านี้ เมื่อทำครบข้อ 3 และ 5 ตัวเลขจะลดลงเอง</p>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-3">กรณีพิเศษ</h3>
            <dl class="space-y-3 text-sm text-gray-700">
                <div><dt class="font-medium text-gray-900">ลูกค้าโทรจอง / walk-in</dt><dd>เข้า การจอง → สร้างการจองเอง ใส่ข้อมูลลูกค้าแล้วเริ่มที่ขั้นตอนที่ 3</dd></div>
                <div><dt class="font-medium text-gray-900">บันทึกยอดผิด</dt><dd>แอดมินกด "ยกเลิก" ที่รายการรับชำระนั้น แล้วบันทึกใหม่ ระบบเก็บประวัติไว้ ไม่ลบ แต่ถ้าออกใบเสร็จไปแล้ว ต้องยกเลิกใบเสร็จก่อนจึงจะแก้รายการรับชำระได้</dd></div>
                <div><dt class="font-medium text-gray-900">ใบเสร็จผิด</dt><dd>แอดมินยกเลิก (void) ใบเสร็จในเมนู "ใบเสร็จรับเงิน" แล้วออกใบใหม่ เลขที่เดิมจะยังอยู่ในรายการสถานะ "ยกเลิกแล้ว"</dd></div>
                <div><dt class="font-medium text-gray-900">ปิดห้อง/ปิดบริการบางวัน</dt><dd>วันที่ปิดจะขึ้น "ปิด" ในปฏิทินและหน้าเช็กห้องว่าง ลูกค้าจองวันนั้นไม่ได้</dd></div>
            </dl>
        </div>

        <p class="text-sm text-gray-500">รายละเอียดการใช้แต่ละเมนู ดูที่ <a href="{{ route('admin.guide.manual') }}" class="text-emerald-700 hover:underline">คู่มือการใช้งาน →</a></p>
    </div>
</x-app-layout>
