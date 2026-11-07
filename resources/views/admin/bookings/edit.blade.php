<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">แก้ไขการจอง</h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
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
