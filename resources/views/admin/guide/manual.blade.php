<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">คู่มือการใช้งาน</h2>
    </x-slot>

    @php
        // [anchor, title, [ [menu, description], ... ], note]
        $sections = [
            ['start', 'เริ่มต้นใช้งาน', [
                ['เข้าสู่ระบบ', 'กด "เข้าสู่ระบบ" ที่หน้าเว็บ แล้วใช้อีเมลและรหัสผ่านที่แอดมินสร้างให้ เมื่อเข้าแล้วจะเห็นปุ่ม "แอดมิน" ที่มุมหน้าเว็บ'],
                ['Dashboard', 'ภาพรวมของระบบ และจำนวนผู้เข้าชมเว็บ (นับคนไม่ซ้ำต่อวัน ไม่เก็บ IP จริง ไม่นับทีมงานและบอท)'],
                ['ตัวเลขสีแดง', 'ข้างปุ่ม "แอดมิน" และเมนู "การจอง" คือจำนวนการจองที่ต้องดำเนินการ (รอยืนยัน หรือมีสลิปแต่ยังไม่บันทึกรับชำระ) กดเพื่อดูเฉพาะรายการนั้น'],
                ['เปลี่ยนรหัสผ่าน / ออกจากระบบ', 'กดชื่อตัวเองมุมขวาบน → Profile / Log Out'],
            ], null],
            ['calendar', 'ปฏิทิน', [
                ['ดูห้องว่าง', 'ตารางแสดงทุกห้อง/จุดตามวัน ใช้ดูว่าวันไหนมีคนเข้าพัก วันไหนว่าง หรือปิด'],
            ], null],
            ['bookings', 'การจอง', [
                ['การจอง', 'รายการจองทั้งหมด ค้นหา/กรองตามสถานะได้ กดเข้าไปแก้ไขรายละเอียด เปลี่ยนสถานะ ดูสลิปที่ลูกค้าแนบ และสร้างการจองเองได้เมื่อลูกค้าโทรจอง'],
                ['สถานะ', 'รอยืนยัน → ยืนยันแล้ว หรือ ยกเลิก (ห้องที่ยกเลิกจะกลับมาว่างทันที)'],
                ['รับชำระ / ใบเสร็จ', 'อยู่ท้ายหน้าแก้ไขการจอง: "บันทึกการรับชำระ" แล้วจึง "ออกใบเสร็จรับเงิน" ทำได้เฉพาะแอดมิน'],
                ['ลูกค้า', 'รายชื่อลูกค้าและประวัติการจอง ดูได้อย่างเดียว'],
                ['ใบเสร็จรับเงิน', 'ค้นหาใบเสร็จตามเลขที่/ชื่อ/เบอร์ กด "ดู / พิมพ์" ใบที่ออกผิดให้แอดมินยกเลิก (void) แล้วออกใหม่'],
            ], 'ผิดพลาดแก้ได้โดยการยกเลิกรายการ ระบบเก็บประวัติไว้เสมอ ไม่มีการลบทิ้ง'],
            ['rooms', 'ข้อมูลที่พัก/บริการ', [
                ['ประเภทห้อง', 'กำหนดชื่อประเภท จำนวนคนสูงสุด และราคาต่อคืน ห้องทุกห้องในประเภทเดียวกันใช้ราคาเดียวกัน'],
                ['บ้านพัก / จุดกางเต็นท์', 'ห้องหรือจุดแต่ละรายการ ใส่ชื่อ รูป วิดีโอ และเปิด/ปิดใช้งาน ทุกห้องที่เปิดใช้งานจะขึ้นเป็นการ์ดในหน้าแรก'],
                ['บริการเสริม', 'ของเสริมที่ลูกค้าเลือกตอนจอง กำหนดชื่อและราคา'],
            ], 'ถ้าห้องไม่ขึ้นหน้าเว็บ ให้เช็กว่าติ๊ก "เปิดใช้งาน" ทั้งที่ห้องและที่ประเภทห้อง'],
            ['posts', 'ข้อมูลโพสต์', [
                ['โพสต์ / แนะนำ', 'เขียนเรื่องราว/โปรโมชัน/ข้อมูลแนะนำ พร้อมรูป แสดงในหน้าแรกตามกลุ่ม'],
                ['กิจกรรม', 'กิจกรรมที่ลูกค้าเลือกเพิ่มตอนจอง'],
                ['กลุ่มโพสต์', 'หมวดสำหรับจัดกลุ่มโพสต์ ชื่อกลุ่มคือหัวข้อที่ขึ้นหน้าแรก'],
            ], null],
            ['general', 'บันทึกทั่วไป', [
                ['บันทึกรายรับ / รายจ่าย', 'จดรายการเงินเข้า-ออกประจำวัน เลือกประเภทและจำนวน'],
                ['ประเภทรายรับ/รายจ่าย', 'หมวดสำหรับจัดกลุ่มรายการ ใช้ในรายงานสรุป'],
                ['หน่วยนับ', 'หน่วยนับที่ใช้ตอนบันทึกรายการ'],
                ['แผนงาน', 'จดแผนงานหรืองานที่ต้องทำ'],
            ], null],
            ['other', 'แบนเนอร์ รายงาน และพนักงาน', [
                ['แบนเนอร์', 'รูปสไลด์ใหญ่หน้าแรก อัปโหลดและจัดการรูปได้'],
                ['รายงานสรุป', 'สรุปรายรับ รายจ่าย และการจอง ตามช่วงเวลา ส่งออกเป็นไฟล์ได้'],
                ['พนักงาน', 'เห็นเฉพาะแอดมิน: เพิ่ม/แก้ไขผู้ใช้ และรีเซ็ตรหัสผ่านให้พนักงาน'],
            ], null],
        ];
    @endphp

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white shadow-sm rounded-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-3">สารบัญ</h3>
            <div class="flex flex-wrap gap-2 text-sm">
                @foreach ($sections as [$id, $title])
                    <a href="#{{ $id }}" class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 hover:bg-emerald-100">{{ $title }}</a>
                @endforeach
                <a href="{{ route('admin.guide.flow') }}" class="px-3 py-1 rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200">ดู Flow การทำงาน →</a>
            </div>
        </div>

        @foreach ($sections as [$id, $title, $items, $note])
            <section id="{{ $id }}" class="bg-white shadow-sm rounded-lg p-6 scroll-mt-4">
                <h3 class="font-semibold text-lg text-gray-900 mb-4">{{ $title }}</h3>
                <dl class="divide-y divide-gray-100">
                    @foreach ($items as [$menu, $desc])
                        <div class="py-3 sm:flex sm:gap-4">
                            <dt class="sm:w-56 shrink-0 font-medium text-gray-900 text-sm">{{ $menu }}</dt>
                            <dd class="text-sm text-gray-600 mt-1 sm:mt-0">{{ $desc }}</dd>
                        </div>
                    @endforeach
                </dl>
                @if ($note)
                    <p class="mt-3 text-xs bg-amber-50 text-amber-800 rounded-md px-3 py-2">💡 {{ $note }}</p>
                @endif
            </section>
        @endforeach

        <div class="bg-white shadow-sm rounded-lg p-6">
            <h3 class="font-semibold text-lg text-gray-900 mb-3">สิทธิ์การใช้งาน</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="py-2">งาน</th>
                        <th class="py-2 text-center w-24">พนักงาน</th>
                        <th class="py-2 text-center w-24">แอดมิน</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-700">
                    <tr><td class="py-2">ดู/แก้ไขการจอง ข้อมูลที่พัก โพสต์ บันทึกรายรับรายจ่าย</td><td class="text-center">✓</td><td class="text-center">✓</td></tr>
                    <tr><td class="py-2">บันทึกรับชำระ / ยกเลิกรายการรับชำระ</td><td class="text-center text-gray-300">—</td><td class="text-center">✓</td></tr>
                    <tr><td class="py-2">ออก / ยกเลิกใบเสร็จรับเงิน</td><td class="text-center text-gray-300">—</td><td class="text-center">✓</td></tr>
                    <tr><td class="py-2">จัดการพนักงาน และรีเซ็ตรหัสผ่าน</td><td class="text-center text-gray-300">—</td><td class="text-center">✓</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
