<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">ปฏิทินการจองรายสัปดาห์</h2>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-4">
            <a href="{{ route('admin.calendar.index', ['week' => $weekStart->copy()->subWeek()->toDateString()]) }}"
               class="text-sm text-emerald-700 hover:underline">&larr; สัปดาห์ก่อนหน้า</a>
            <h3 class="font-semibold">{{ $weekStart->format('d M') }} - {{ $weekEnd->format('d M Y') }}</h3>
            <a href="{{ route('admin.calendar.index', ['week' => $weekStart->copy()->addWeek()->toDateString()]) }}"
               class="text-sm text-emerald-700 hover:underline">สัปดาห์ถัดไป &rarr;</a>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
            <table class="min-w-full border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-3 py-2 text-left border-b border-gray-200 sticky left-0 bg-gray-50">ที่พัก</th>
                        @foreach ($days as $day)
                            <th class="px-3 py-2 text-center border-b border-gray-200 min-w-[110px]">
                                {{ $day->translatedFormat('D') }}<br>
                                <span class="text-xs text-gray-400">{{ $day->format('d/m') }}</span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($units as $unit)
                        <tr class="border-b border-gray-100">
                            <td class="px-3 py-2 font-medium sticky left-0 bg-white">
                                {{ $unit->name }}
                                <div class="text-xs text-gray-400">{{ $unit->accommodationType->name }}</div>
                            </td>
                            @foreach ($days as $day)
                                @php
                                    $booking = ($bookings[$unit->id] ?? collect())->first(fn ($b) => $day->between($b->check_in, $b->check_out->copy()->subDay()));
                                @endphp
                                <td class="px-2 py-2 text-center">
                                    @if ($booking)
                                        <a href="{{ route('admin.bookings.edit', $booking) }}"
                                           @class([
                                               'block rounded px-2 py-1 text-xs font-medium',
                                               'bg-yellow-100 text-yellow-800' => $booking->status === 'pending',
                                               'bg-emerald-100 text-emerald-800' => $booking->status === 'confirmed',
                                           ])
                                           title="{{ $booking->guest_name }}">
                                            {{ Str::limit($booking->guest_name, 10) }}
                                        </a>
                                    @else
                                        <span class="block rounded px-2 py-1 text-xs text-gray-300 bg-gray-50">ว่าง</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex gap-4 text-xs text-gray-500">
            <span><span class="inline-block w-3 h-3 bg-gray-50 border border-gray-200 rounded align-middle"></span> ว่าง</span>
            <span><span class="inline-block w-3 h-3 bg-yellow-100 rounded align-middle"></span> รอยืนยัน</span>
            <span><span class="inline-block w-3 h-3 bg-emerald-100 rounded align-middle"></span> ยืนยันแล้ว</span>
        </div>
    </div>
</x-app-layout>
