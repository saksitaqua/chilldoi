<x-layouts.public :title="$unit->name">
    <a href="{{ route('availability.index') }}" class="text-sm text-emerald-700 hover:underline">&larr; กลับหน้าตรวจสอบห้องว่าง</a>

    <div class="mt-4 mb-6">
        <p class="text-xs text-emerald-700 font-medium">{{ $unit->accommodationType->name }}</p>
        <h1 class="text-2xl font-bold">{{ $unit->name }}</h1>
        <p class="text-sm text-gray-500 mt-1">
            รองรับ {{ $unit->accommodationType->max_guests }} คน &middot;
            เริ่มต้น {{ number_format($unit->accommodationType->base_price) }} บาท/คืน
        </p>
    </div>

    @if ($unit->visibleImages->count())
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-8">
            @foreach ($unit->visibleImages as $image)
                <img src="{{ $image->url }}" alt="{{ $unit->name }}" class="w-full h-40 object-cover rounded-lg">
            @endforeach
        </div>
    @else
        <div class="w-full h-40 bg-gray-100 flex items-center justify-center text-gray-400 text-sm rounded-lg mb-8">ไม่มีรูปภาพ</div>
    @endif

    @if ($unit->accommodationType->description || $unit->notes)
        <div class="bg-white rounded-lg shadow-sm p-4 mb-8">
            @if ($unit->accommodationType->description)
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $unit->accommodationType->description }}</p>
            @endif
            @if ($unit->notes)
                <p class="text-sm text-gray-500 whitespace-pre-line mt-2">{{ $unit->notes }}</p>
            @endif
        </div>
    @endif

    <h2 class="text-lg font-semibold mb-3">ความว่าง 2 สัปดาห์ข้างหน้า</h2>
    <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
        <table class="min-w-full border-collapse text-sm">
            <tbody>
                <tr>
                    @foreach ($days as $day)
                        <td class="px-2 py-2 text-center border-b border-gray-100 min-w-[70px]">
                            <div class="text-xs text-gray-400 mb-1">{{ $day->translatedFormat('D') }} {{ $day->format('d/m') }}</div>
                            @php
                                $occupied = $bookings->first(fn ($b) => $day->between($b->check_in, $b->check_out->copy()->subDay()));
                            @endphp
                            @if ($occupied)
                                <span class="block rounded px-2 py-1 text-xs font-medium bg-red-100 text-red-700">ไม่ว่าง</span>
                            @else
                                <span class="block rounded px-2 py-1 text-xs font-medium bg-emerald-50 text-emerald-700">ว่าง</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>

    @if ($unit->lat && $unit->lng)
        <div class="mt-6">
            <a href="https://www.google.com/maps?q={{ $unit->lat }},{{ $unit->lng }}" target="_blank" rel="noopener"
               class="text-sm text-emerald-700 hover:underline">เปิดตำแหน่งใน Google Maps &rarr;</a>
        </div>
    @endif
</x-layouts.public>
