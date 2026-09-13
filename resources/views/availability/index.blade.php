<x-layouts.public :title="__('site.availability.title')">
    <h1 class="text-2xl font-bold mb-6">{{ __('site.availability.title') }}</h1>

    <img src="{{ asset('images/booking-steps.svg') }}" alt="{{ __('site.availability.title') }}" class="w-full rounded-lg shadow-sm mb-8">

    <form method="GET" action="{{ route('availability.index') }}" class="bg-white p-4 rounded-lg shadow-sm mb-8 grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('site.home.quick_checkin') }}</label>
            <input type="date" name="check_in" value="{{ request('check_in') }}" required class="w-full border-gray-300 rounded-md">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('site.home.quick_checkout') }}</label>
            <input type="date" name="check_out" value="{{ request('check_out') }}" required class="w-full border-gray-300 rounded-md">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('site.home.quick_type') }}</label>
            <select name="accommodation_type_id" class="w-full border-gray-300 rounded-md">
                <option value="">{{ __('site.home.quick_type_all') }}</option>
                @foreach ($types as $type)
                    <option value="{{ $type->id }}" @selected(request('accommodation_type_id') == $type->id)>{{ $type->display_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full bg-emerald-700 text-white rounded-md py-2 font-medium hover:bg-emerald-800">{{ __('site.availability.search') }}</button>
        </div>
    </form>

    @error('check_out')
        <div class="mb-4 text-sm text-red-600">{{ $message }}</div>
    @enderror

    @if ($searched)
        <h2 class="text-lg font-semibold mb-4">{{ __('site.availability.results_heading', ['count' => $units->count()]) }}</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-10">
            @forelse ($units as $unit)
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    @if ($unit->visibleImages->first())
                        <img src="{{ $unit->visibleImages->first()->url }}" alt="{{ $unit->name }}" class="w-full h-40 object-cover">
                    @else
                        <div class="w-full h-40 bg-gray-100 flex items-center justify-center text-gray-400 text-sm">{{ __('site.home.no_image') }}</div>
                    @endif
                    <div class="p-4">
                        <p class="text-xs text-emerald-700 font-medium">{{ $unit->accommodationType->display_name }}</p>
                        <h3 class="font-semibold text-lg">{{ $unit->name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ __('site.availability.capacity', ['count' => $unit->accommodationType->max_guests]) }}</p>
                        <p class="text-sm text-gray-700 mt-1">{{ __('site.availability.price_from', ['price' => number_format($unit->accommodationType->base_price)]) }}</p>
                        <div class="flex items-center justify-between mt-3">
                            <a href="{{ route('units.show', $unit) }}" class="text-sm text-emerald-700 hover:underline">{{ __('site.availability.view_details') }}</a>
                            <a href="{{ route('booking.create', ['unit' => $unit->id, 'check_in' => request('check_in'), 'check_out' => request('check_out')]) }}"
                               class="bg-emerald-700 text-white text-sm font-medium px-4 py-1.5 rounded-md hover:bg-emerald-800">{{ __('site.availability.book') }}</a>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500">{{ __('site.availability.no_results') }}</p>
            @endforelse
        </div>
    @endif

    <h2 class="text-lg font-semibold mb-4">{{ __('site.availability.weekly_calendar') }}</h2>

    <div class="flex justify-between items-center mb-4">
        <a href="{{ route('availability.index', array_merge(request()->except('week'), ['week' => $weekStart->copy()->subWeek()->toDateString()])) }}"
           class="text-sm text-emerald-700 hover:underline">{{ __('site.availability.prev_week') }}</a>
        <h3 class="font-semibold text-sm">{{ $weekStart->format('d M') }} - {{ $weekEnd->format('d M Y') }}</h3>
        <a href="{{ route('availability.index', array_merge(request()->except('week'), ['week' => $weekStart->copy()->addWeek()->toDateString()])) }}"
           class="text-sm text-emerald-700 hover:underline">{{ __('site.availability.next_week') }}</a>
    </div>

    <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
        <table class="min-w-full border-collapse text-sm">
            <thead>
                <tr class="bg-gray-50">
                    <th class="px-3 py-2 text-left border-b border-gray-200 sticky left-0 bg-gray-50">{{ __('site.availability.unit_column') }}</th>
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
                            <div class="text-xs text-gray-400">{{ $unit->accommodationType->display_name }}</div>
                        </td>
                        @foreach ($days as $day)
                            @php
                                $occupied = ($calendarBookings[$unit->id] ?? collect())->first(fn ($b) => $day->between($b->check_in, $b->check_out->copy()->subDay()));
                            @endphp
                            <td class="px-2 py-2 text-center">
                                @if ($occupied)
                                    <a href="{{ route('units.show', $unit) }}" class="block rounded px-2 py-1 text-xs font-medium bg-red-100 text-red-700 hover:bg-red-200">{{ __('site.availability.occupied') }}</a>
                                @else
                                    <a href="{{ route('booking.create', ['unit' => $unit, 'check_in' => $day->toDateString(), 'check_out' => $day->copy()->addDay()->toDateString()]) }}"
                                       class="block rounded px-2 py-1 text-xs font-medium bg-emerald-50 text-emerald-700 hover:bg-emerald-100">{{ __('site.availability.vacant') }}</a>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layouts.public>
