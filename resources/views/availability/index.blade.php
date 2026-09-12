<x-layouts.public :title="'ตรวจสอบห้องว่าง'">
    <h1 class="text-2xl font-bold mb-6">ตรวจสอบห้องว่าง</h1>

    <form method="GET" action="{{ route('availability.index') }}" class="bg-white p-4 rounded-lg shadow-sm mb-8 grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">วันเช็คอิน</label>
            <input type="date" name="check_in" value="{{ request('check_in') }}" required class="w-full border-gray-300 rounded-md">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">วันเช็คเอาท์</label>
            <input type="date" name="check_out" value="{{ request('check_out') }}" required class="w-full border-gray-300 rounded-md">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">ประเภทที่พัก</label>
            <select name="accommodation_type_id" class="w-full border-gray-300 rounded-md">
                <option value="">ทั้งหมด</option>
                @foreach ($types as $type)
                    <option value="{{ $type->id }}" @selected(request('accommodation_type_id') == $type->id)>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full bg-emerald-700 text-white rounded-md py-2 font-medium hover:bg-emerald-800">ค้นหา</button>
        </div>
    </form>

    @error('check_out')
        <div class="mb-4 text-sm text-red-600">{{ $message }}</div>
    @enderror

    @if ($searched)
        <h2 class="text-lg font-semibold mb-4">ผลการค้นหา ({{ $units->count() }} รายการว่าง)</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-10">
            @forelse ($units as $unit)
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    @if ($unit->visibleImages->first())
                        <img src="{{ $unit->visibleImages->first()->url }}" alt="{{ $unit->name }}" class="w-full h-40 object-cover">
                    @else
                        <div class="w-full h-40 bg-gray-100 flex items-center justify-center text-gray-400 text-sm">ไม่มีรูปภาพ</div>
                    @endif
                    <div class="p-4">
                        <p class="text-xs text-emerald-700 font-medium">{{ $unit->accommodationType->name }}</p>
                        <h3 class="font-semibold text-lg">{{ $unit->name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">รองรับ {{ $unit->accommodationType->max_guests }} คน</p>
                        <p class="text-sm text-gray-700 mt-1">เริ่มต้น {{ number_format($unit->accommodationType->base_price) }} บาท/คืน</p>
                        <div class="flex items-center justify-between mt-3">
                            <a href="{{ route('units.show', $unit) }}" class="text-sm text-emerald-700 hover:underline">ดูรายละเอียด &rarr;</a>
                            <a href="{{ route('booking.create', ['unit' => $unit->id, 'check_in' => request('check_in'), 'check_out' => request('check_out')]) }}"
                               class="bg-emerald-700 text-white text-sm font-medium px-4 py-1.5 rounded-md hover:bg-emerald-800">จอง</a>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500">ไม่พบที่พักว่างในช่วงวันที่เลือก</p>
            @endforelse
        </div>
    @endif

    <h2 class="text-lg font-semibold mb-4">ปฏิทินความว่างรายสัปดาห์</h2>

    <div class="flex justify-between items-center mb-4">
        <a href="{{ route('availability.index', array_merge(request()->except('week'), ['week' => $weekStart->copy()->subWeek()->toDateString()])) }}"
           class="text-sm text-emerald-700 hover:underline">&larr; สัปดาห์ก่อนหน้า</a>
        <h3 class="font-semibold text-sm">{{ $weekStart->format('d M') }} - {{ $weekEnd->format('d M Y') }}</h3>
        <a href="{{ route('availability.index', array_merge(request()->except('week'), ['week' => $weekStart->copy()->addWeek()->toDateString()])) }}"
           class="text-sm text-emerald-700 hover:underline">สัปดาห์ถัดไป &rarr;</a>
    </div>

    <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
        <table class="min-w-full border-collapse text-sm">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-3 py-2 text-left border-b border-gray-200 sticky left-0 bg-gray-50">ที่พัก</th>
                    @foreach ($days as $day)
                        <th class="px-3 py-2 text-center border-b border-gray-200 min-w-[90px]">
                            {{ $day->translatedFormat('D') }}<br>
                            <span class="text-xs text-gray-400">{{ $day->format('d/m') }}</span>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($calendarUnits as $unit)
                    <tr class="border-b border-gray-100">
                        <td class="px-3 py-2 font-medium sticky left-0 bg-white">
                            <a href="{{ route('units.show', $unit) }}" class="text-emerald-700 hover:underline">{{ $unit->name }}</a>
                            <div class="text-xs text-gray-400">{{ $unit->accommodationType->name }}</div>
                        </td>
                        @foreach ($days as $day)
                            @php
                                $occupied = ($calendarBookings[$unit->id] ?? collect())->first(fn ($b) => $day->between($b->check_in, $b->check_out->copy()->subDay()));
                            @endphp
                            <td class="px-2 py-2 text-center">
                                @if ($occupied)
                                    <a href="{{ route('units.show', $unit) }}" class="block rounded px-2 py-1 text-xs font-medium bg-red-100 text-red-700 hover:bg-red-200">ไม่ว่าง</a>
                                @else
                                    <a href="{{ route('booking.create', ['unit' => $unit, 'check_in' => $day->toDateString(), 'check_out' => $day->copy()->addDay()->toDateString()]) }}"
                                       class="block rounded px-2 py-1 text-xs font-medium bg-emerald-50 text-emerald-700 hover:bg-emerald-100">ว่าง</a>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layouts.public>
