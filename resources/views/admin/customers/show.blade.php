<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">ประวัติลูกค้า: {{ $customer->name }}</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="{{ route('admin.customers.index') }}" class="text-emerald-700 hover:underline text-sm">← กลับรายชื่อลูกค้า</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-xs text-gray-500">เบอร์โทร</p>
                <p class="text-base font-medium">{{ $customer->phone ?: '-' }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-xs text-gray-500">อีเมล</p>
                <p class="text-base font-medium">{{ $customer->email ?: '-' }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-xs text-gray-500">จำนวนครั้งที่จอง</p>
                <p class="text-2xl font-bold text-emerald-700">{{ $bookings->count() }}</p>
            </div>
        </div>

        <h3 class="font-semibold mb-3">ประวัติการจอง</h3>
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">ที่พัก</th>
                        <th class="px-4 py-2 text-left">เช็คอิน</th>
                        <th class="px-4 py-2 text-left">เช็คเอาท์</th>
                        <th class="px-4 py-2 text-left">สถานะ</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($bookings as $booking)
                        <tr>
                            <td class="px-4 py-2 font-medium">{{ $booking->unit->name }}</td>
                            <td class="px-4 py-2">{{ $booking->check_in->format('d/m/Y') }}</td>
                            <td class="px-4 py-2">{{ $booking->check_out->format('d/m/Y') }}</td>
                            <td class="px-4 py-2">
                                <span @class([
                                    'px-2 py-0.5 rounded text-xs font-medium',
                                    'bg-yellow-100 text-yellow-800' => $booking->status === 'pending',
                                    'bg-emerald-100 text-emerald-800' => $booking->status === 'confirmed',
                                    'bg-gray-100 text-gray-500' => $booking->status === 'cancelled',
                                ])>{{ ['pending' => 'รอยืนยัน', 'confirmed' => 'ยืนยันแล้ว', 'cancelled' => 'ยกเลิก'][$booking->status] ?? $booking->status }}</span>
                            </td>
                            <td class="px-4 py-2 text-right">
                                <a href="{{ route('admin.bookings.edit', $booking) }}" class="text-emerald-700 hover:underline">ดู/แก้ไข</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">ยังไม่มีประวัติการจอง</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($customer->notes)
            <h3 class="font-semibold mt-6 mb-2">หมายเหตุ</h3>
            <div class="bg-white shadow-sm rounded-lg p-4 text-sm text-gray-600">{{ $customer->notes }}</div>
        @endif
    </div>
</x-app-layout>
