<x-layouts.public :title="'จองสำเร็จ'">
    <div class="max-w-lg mx-auto bg-white rounded-lg shadow-sm p-8 text-center">
        @if (session('status'))
            <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm text-left">
                {{ session('status') }}
            </div>
        @endif

        <div class="text-5xl mb-4">🏕️</div>
        <h1 class="text-2xl font-bold mb-2">ได้รับคำขอจองแล้ว</h1>
        <p class="text-gray-500 mb-6">เจ้าหน้าที่จะติดต่อกลับเพื่อยืนยันการจองที่เบอร์ {{ $booking->guest_phone }}</p>

        <div class="text-left bg-gray-50 rounded-md p-4 text-sm space-y-2">
            <div class="flex justify-between"><span class="text-gray-500">ที่พัก</span><span class="font-medium">{{ $booking->unit->name }} ({{ $booking->unit->accommodationType->name }})</span></div>
            <div class="flex justify-between"><span class="text-gray-500">วันเช็คอิน</span><span class="font-medium">{{ $booking->check_in->format('d/m/Y') }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">วันเช็คเอาท์</span><span class="font-medium">{{ $booking->check_out->format('d/m/Y') }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">จำนวนคน</span><span class="font-medium">{{ $booking->guests }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">อีเมล</span><span class="font-medium">{{ $booking->guest_email }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">สถานะ</span><span class="font-medium text-yellow-600">รอยืนยัน</span></div>

            @if ($booking->activities->count())
                <div class="pt-2 border-t border-gray-200">
                    <span class="text-gray-500">กิจกรรมเสริม:</span>
                    <span class="font-medium">{{ $booking->activities->pluck('name')->join(', ') }}</span>
                </div>
            @endif

            @if ($booking->services->count())
                <div>
                    <span class="text-gray-500">บริการเสริม:</span>
                    <span class="font-medium">{{ $booking->services->pluck('name')->join(', ') }}</span>
                </div>
            @endif
        </div>

        <div class="text-left mt-6 pt-6 border-t border-gray-200">
            <h2 class="font-semibold mb-3">แจ้งชำระเงิน</h2>

            @if (config('services.bank.account_number'))
                <div class="bg-emerald-50 border border-emerald-100 rounded-md p-4 text-sm mb-4">
                    <p>ธนาคาร: <strong>{{ config('services.bank.name') }}</strong></p>
                    <p>เลขบัญชี: <strong>{{ config('services.bank.account_number') }}</strong></p>
                    <p>ชื่อบัญชี: <strong>{{ config('services.bank.account_name') }}</strong></p>
                    @if (config('services.bank.promptpay'))
                        <p>พร้อมเพย์: <strong>{{ config('services.bank.promptpay') }}</strong></p>
                    @endif
                </div>
            @else
                <p class="text-sm text-gray-400 mb-4">ทางเจ้าหน้าที่จะแจ้งช่องทางชำระเงินให้ทางโทรศัพท์</p>
            @endif

            @if ($booking->payment_slip)
                <div class="mb-3">
                    <p class="text-sm text-emerald-700 mb-2">แนบสลิปแล้วเมื่อ {{ $booking->payment_uploaded_at->format('d/m/Y H:i') }}</p>
                    <img src="{{ $booking->payment_slip_url }}" class="w-40 rounded-md border">
                </div>
                <p class="text-sm text-gray-500 mb-2">ถ้าแนบผิด อัปโหลดรูปใหม่แทนที่ได้ที่นี่:</p>
            @endif

            @error('slip')
                <div class="mb-3 text-sm text-red-600">{{ $message }}</div>
            @enderror

            <form method="POST" action="{{ route('booking.slip', $booking) }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-2">
                @csrf
                <input type="file" name="slip" accept="image/*" required class="flex-1 text-sm border border-gray-300 rounded-md">
                <button type="submit" class="bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-md hover:bg-emerald-800 whitespace-nowrap">
                    {{ $booking->payment_slip ? 'อัปโหลดใหม่' : 'อัปโหลดสลิป' }}
                </button>
            </form>

            <p class="text-xs text-gray-400 mt-3">
                ยังไม่พร้อมตอนนี้ก็ได้ — บันทึกหน้านี้ไว้ (หรือจดลิงก์: <span class="break-all">{{ url()->current() }}</span>) แล้วกลับมาอัปโหลดทีหลังได้
            </p>
        </div>

        <a href="{{ route('home') }}" class="inline-block mt-6 text-emerald-700 hover:underline text-sm">&larr; กลับหน้าแรก</a>
    </div>
</x-layouts.public>
