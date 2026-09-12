<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">การจอง</h2>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex justify-between items-center mb-4">
            <form method="GET" class="flex gap-2 text-sm">
                <select name="status" onchange="this.form.submit()" class="border-gray-300 rounded-md">
                    <option value="">ทุกสถานะ</option>
                    <option value="pending" @selected(request('status') == 'pending')>รอยืนยัน</option>
                    <option value="confirmed" @selected(request('status') == 'confirmed')>ยืนยันแล้ว</option>
                    <option value="cancelled" @selected(request('status') == 'cancelled')>ยกเลิก</option>
                </select>
            </form>
            <a href="{{ route('admin.bookings.create') }}" class="bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-800">+ เพิ่มการจอง</a>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">ผู้จอง</th>
                        <th class="px-4 py-2 text-left">ที่พัก</th>
                        <th class="px-4 py-2 text-left">เช็คอิน</th>
                        <th class="px-4 py-2 text-left">เช็คเอาท์</th>
                        <th class="px-4 py-2 text-left">สถานะ</th>
                        <th class="px-4 py-2 text-left">ที่มา</th>
                        <th class="px-4 py-2"></th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($bookings as $booking)
                        <tr>
                            <td class="px-4 py-2 font-medium">{{ $booking->guest_name }}</td>
                            <td class="px-4 py-2">{{ $booking->unit->name }} <span class="text-gray-400">({{ $booking->unit->accommodationType->name }})</span></td>
                            <td class="px-4 py-2">{{ $booking->check_in->format('d/m/Y') }}</td>
                            <td class="px-4 py-2">{{ $booking->check_out->format('d/m/Y') }}</td>
                            <td class="px-4 py-2">
                                <span @class([
                                    'px-2 py-0.5 rounded text-xs font-medium',
                                    'bg-yellow-100 text-yellow-800' => $booking->status === 'pending',
                                    'bg-emerald-100 text-emerald-800' => $booking->status === 'confirmed',
                                    'bg-red-100 text-red-800' => $booking->status === 'cancelled',
                                ])>
                                    @switch($booking->status)
                                        @case('pending') รอยืนยัน @break
                                        @case('confirmed') ยืนยันแล้ว @break
                                        @case('cancelled') ยกเลิก @break
                                    @endswitch
                                </span>
                            </td>
                            <td class="px-4 py-2 text-gray-500">{{ $booking->source === 'online' ? 'ออนไลน์' : 'แอดมิน' }}</td>
                            <td class="px-4 py-2">
                                @if ($booking->payment_slip)
                                    <span class="text-emerald-700 text-xs">💳 แนบสลิปแล้ว</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <a href="{{ route('admin.bookings.edit', $booking) }}" class="text-emerald-700 hover:underline">แก้ไข</a>
                                <form action="{{ route('admin.bookings.destroy', $booking) }}" method="POST" class="inline" onsubmit="return confirm('ยืนยันการลบ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">ลบ</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $bookings->links() }}</div>
    </div>
</x-app-layout>
