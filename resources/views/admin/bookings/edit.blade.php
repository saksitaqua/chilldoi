<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">แก้ไขการจอง</h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif
        @if (session('customer_link'))
            <div class="mb-4 rounded-md bg-amber-50 border border-amber-200 px-4 py-3 text-sm">
                <p class="text-amber-900 mb-2">ลิงก์ใบเสร็จสำหรับส่งให้ลูกค้า</p>
                <div class="flex gap-2">
                    <input type="text" readonly value="{{ session('customer_link') }}" onfocus="this.select()" class="flex-1 border-gray-300 rounded-md text-xs">
                    <button type="button" class="px-3 py-1.5 bg-amber-700 text-white rounded-md text-xs" onclick="var i=this.previousElementSibling; i.select(); if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(i.value); } else { document.execCommand('copy'); } this.textContent='คัดลอกแล้ว ✓';">คัดลอก</button>
                </div>
            </div>
        @endif

        @if (auth()->user()->isAdmin() && $booking->status !== 'cancelled' && ! $pay['active_receipt'])
            @php
                $quickNeedsPayment = $pay['paid'] <= 0 || ! $pay['fully_paid'];
                $quickEmail = $booking->guest_email;
            @endphp
            <form method="POST" action="{{ route('admin.bookings.quick-confirm', $booking) }}" class="mb-4 bg-emerald-50 border border-emerald-300 rounded-lg p-5 space-y-3"
                  onsubmit="return confirm('ยืนยันการจอง' + (this.amount && this.amount.value ? ' และบันทึกรับชำระ ' + Number(this.amount.value).toLocaleString('th-TH', {minimumFractionDigits: 2}) + ' บาท' : '') + ' แล้วออกใบเสร็จรับเงิน' + {{ $quickEmail ? json_encode(' พร้อมส่งอีเมลถึง '.$quickEmail) : json_encode(' (ลูกค้าไม่มีอีเมล ต้องส่งลิงก์ใบเสร็จเอง)') }} + ' ?\n\nออกใบเสร็จแล้วจะแก้ไขไม่ได้ ต้องยกเลิก (void) และออกใหม่');">
                @csrf
                <div>
                    <h3 class="font-semibold text-emerald-900">ทางลัด: ยืนยัน + ออกใบเสร็จ + ส่งอีเมลลูกค้า</h3>
                    <p class="text-xs text-emerald-800 mt-1">ตรวจสลิปด้านล่างแล้วเงินเข้าบัญชีถูกต้อง กดปุ่มเดียว ระบบเปลี่ยนสถานะเป็น "ยืนยันแล้ว" บันทึกรับชำระ ออกใบเสร็จ และส่งอีเมลให้ลูกค้าพร้อมลิงก์ใบเสร็จ</p>
                </div>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    @if ($quickNeedsPayment)
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">ยอดที่รับ (บาท)</label>
                            <input type="number" name="amount" step="0.01" min="0.01" required
                                   value="{{ old('amount', $pay['balance'] > 0 ? number_format($pay['balance'], 2, '.', '') : '') }}" class="w-full border-gray-300 rounded-md">
                        </div>
                    @else
                        <div class="text-xs text-gray-600 self-end">รับชำระครบแล้ว {{ number_format($pay['paid'], 2) }} บาท จะออกใบเสร็จตามนี้</div>
                    @endif
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">ช่องทางรับเงิน</label>
                        <select name="method" class="w-full border-gray-300 rounded-md">
                            @foreach (\App\Models\Payment::METHODS as $key => $label)
                                <option value="{{ $key }}" @selected($key === 'transfer')>{{ $label['th'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">เหตุผลถ้ายอดที่รับไม่เท่ายอดตามระบบ เช่น ส่วนลด (ไม่ต้องกรอกถ้าเท่ากัน)</label>
                    <input type="text" name="adjustment_note" maxlength="500" value="{{ old('adjustment_note') }}" class="w-full border-gray-300 rounded-md text-sm">
                </div>
                @error('amount')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('adjustment_note')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                <button type="submit" class="bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-800">ยืนยัน + ออกใบเสร็จ + ส่งอีเมล</button>
                @unless ($quickEmail)
                    <span class="ml-2 text-xs text-amber-700">ลูกค้าไม่ได้ระบุอีเมล ระบบจะแสดงลิงก์ใบเสร็จให้คัดลอกส่งเอง</span>
                @endunless
            </form>
        @endif

        <form method="POST" action="{{ route('admin.bookings.update', $booking) }}" class="bg-white shadow-sm rounded-lg p-6 space-y-4">
            @csrf
            @method('PUT')
            @include('admin.bookings._form')

            <div class="pt-2">
                <button type="submit" class="bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-800">บันทึก</button>
                <a href="{{ route('admin.bookings.index') }}" class="ml-2 text-sm text-gray-500">ยกเลิก</a>
            </div>
        </form>

        @if ($booking->status === 'confirmed')
            <form method="POST" action="{{ route('admin.bookings.resend-confirmation', $booking) }}" class="mt-4 flex flex-wrap items-center gap-3 text-sm"
                  onsubmit="return confirm('ส่งอีเมลยืนยันการจองให้ลูกค้าอีกครั้ง?');">
                @csrf
                @if ($booking->guest_email)
                    <button type="submit" class="border border-emerald-700 text-emerald-700 px-3 py-1.5 rounded-md hover:bg-emerald-50">ส่งอีเมลยืนยันอีกครั้ง</button>
                    <span class="text-gray-500">ถึง {{ $booking->guest_email }}</span>
                @else
                    <span class="text-amber-700 bg-amber-50 rounded-md px-3 py-1.5">ลูกค้าไม่ได้ระบุอีเมล — กรุณาโทรยืนยันกับลูกค้าเอง</span>
                @endif
            </form>
        @endif

        @include('admin.bookings._payments')
    </div>
</x-app-layout>
