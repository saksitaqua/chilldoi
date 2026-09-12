<x-layouts.public :title="'จองสำเร็จ'">
    <div class="max-w-lg mx-auto bg-white rounded-lg shadow-sm p-8 text-center">
        <div class="text-5xl mb-4">🏕️</div>
        <h1 class="text-2xl font-bold mb-2">ได้รับคำขอจองแล้ว</h1>
        <p class="text-gray-500 mb-6">เจ้าหน้าที่จะติดต่อกลับเพื่อยืนยันการจองที่เบอร์ {{ $booking->guest_phone }}</p>

        <div class="text-left bg-gray-50 rounded-md p-4 text-sm space-y-2">
            <div class="flex justify-between"><span class="text-gray-500">ที่พัก</span><span class="font-medium">{{ $booking->unit->name }} ({{ $booking->unit->accommodationType->name }})</span></div>
            <div class="flex justify-between"><span class="text-gray-500">วันเช็คอิน</span><span class="font-medium">{{ $booking->check_in->format('d/m/Y') }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">วันเช็คเอาท์</span><span class="font-medium">{{ $booking->check_out->format('d/m/Y') }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">จำนวนคน</span><span class="font-medium">{{ $booking->guests }}</span></div>
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

        <a href="{{ route('home') }}" class="inline-block mt-6 text-emerald-700 hover:underline text-sm">&larr; กลับหน้าแรก</a>
    </div>
</x-layouts.public>
