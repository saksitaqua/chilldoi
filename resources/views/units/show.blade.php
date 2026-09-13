<x-layouts.public :title="$unit->name">
    <a href="{{ route('availability.index') }}" class="text-sm text-emerald-700 hover:underline">{{ __('site.unit.back') }}</a>

    <div class="mt-4 mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-xs text-emerald-700 font-medium">{{ $unit->accommodationType->display_name }}</p>
            <h1 class="text-2xl font-bold">{{ $unit->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ __('site.unit.capacity_and_price', ['count' => $unit->accommodationType->max_guests, 'price' => number_format($unit->accommodationType->base_price)]) }}
            </p>
        </div>

        <form method="GET" action="{{ route('booking.create', $unit) }}" class="bg-white rounded-lg shadow-sm p-3 flex flex-wrap items-end gap-2">
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ __('site.unit.checkin') }}</label>
                <input type="date" name="check_in" required value="{{ request('check_in') }}" class="border-gray-300 rounded-md text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ __('site.unit.checkout') }}</label>
                <input type="date" name="check_out" required value="{{ request('check_out') }}" class="border-gray-300 rounded-md text-sm">
            </div>
            <button type="submit" class="bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-md hover:bg-emerald-800">{{ __('site.unit.book') }}</button>
        </form>
    </div>

    @if ($unit->video)
        <video src="{{ $unit->video_url }}" controls class="w-full rounded-lg mb-4 max-h-96"></video>
    @endif

    @if ($unit->visibleImages->count())
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-8">
            @foreach ($unit->visibleImages as $image)
                <img src="{{ $image->url }}" alt="{{ $unit->name }}" class="w-full h-40 object-cover rounded-lg">
            @endforeach
        </div>
    @elseif (! $unit->video)
        <div class="w-full h-40 bg-gray-100 flex items-center justify-center text-gray-400 text-sm rounded-lg mb-8">{{ __('site.home.no_image') }}</div>
    @endif

    @if ($unit->accommodationType->display_description || $unit->notes)
        <div class="bg-white rounded-lg shadow-sm p-4 mb-8">
            @if ($unit->accommodationType->display_description)
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $unit->accommodationType->display_description }}</p>
            @endif
            @if ($unit->notes)
                <p class="text-sm text-gray-500 whitespace-pre-line mt-2">{{ $unit->notes }}</p>
            @endif
        </div>
    @endif

    <h2 class="text-lg font-semibold mb-3">{{ __('site.unit.availability_heading') }}</h2>
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
                                <span class="block rounded px-2 py-1 text-xs font-medium bg-red-100 text-red-700">{{ __('site.availability.occupied') }}</span>
                            @else
                                <a href="{{ route('booking.create', ['unit' => $unit, 'check_in' => $day->toDateString(), 'check_out' => $day->copy()->addDay()->toDateString()]) }}"
                                   class="block rounded px-2 py-1 text-xs font-medium bg-emerald-50 text-emerald-700 hover:bg-emerald-100">{{ __('site.availability.vacant') }}</a>
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
               class="text-sm text-emerald-700 hover:underline">{{ __('site.unit.open_maps') }}</a>
        </div>
    @endif
</x-layouts.public>
