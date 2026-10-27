<x-layouts.public :title="__('site.booking.title_prefix').$unit->name">
    <a href="{{ route('availability.index') }}" class="text-sm text-emerald-700 hover:underline">{{ __('site.unit.back') }}</a>

    <div class="mt-4 mb-6 bg-white rounded-lg shadow-sm p-4 flex gap-4 items-center">
        @if ($unit->visibleImages->first())
            <x-focal-image :src="$unit->visibleImages->first()->url" :focal="$unit->visibleImages->first()->focal_style" class="w-24 h-24 rounded-md shrink-0" />
        @endif
        <div>
            <p class="text-xs text-emerald-700 font-medium">{{ $unit->accommodationType->display_name }}</p>
            <h1 class="text-xl font-bold">{{ $unit->name }}</h1>
            <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($checkIn)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($checkOut)->format('d/m/Y') }}</p>
        </div>
    </div>

    @if (! $available)
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-md p-4 text-sm">
            {{ $closed ? __('site.booking.closed_unit') : __('site.booking.unavailable') }}
            <a href="{{ route('availability.index') }}" class="underline">{{ __('site.booking.back_search') }}</a>
        </div>
    @else
        <form method="POST" action="{{ route('booking.store', $unit) }}" class="bg-white rounded-lg shadow-sm p-6 space-y-4">
            @csrf
            <input type="hidden" name="check_in" value="{{ $checkIn }}">
            <input type="hidden" name="check_out" value="{{ $checkOut }}">

            @if ($errors->has('check_in') || $errors->has('check_out'))
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-md p-3 text-sm">
                    {{ $errors->first('check_in') ?: $errors->first('check_out') }}
                    <a href="{{ route('availability.index') }}" class="underline">{{ __('site.booking.back_search') }}</a>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('site.booking.guest_name') }}</label>
                    <input type="text" name="guest_name" value="{{ old('guest_name') }}" required class="w-full border-gray-300 rounded-md">
                    <x-input-error :messages="$errors->get('guest_name')" class="mt-1" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">{{ __('site.booking.guest_phone') }}</label>
                    <input type="text" name="guest_phone" value="{{ old('guest_phone') }}" required class="w-full border-gray-300 rounded-md">
                    <x-input-error :messages="$errors->get('guest_phone')" class="mt-1" />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">{{ __('site.booking.guest_email') }} <span class="text-red-500">*</span></label>
                <input type="email" name="guest_email" value="{{ old('guest_email') }}" required class="w-full border-gray-300 rounded-md" placeholder="you@example.com">
                <p class="text-xs text-gray-400 mt-1">{{ __('site.booking.guest_email_hint') }}</p>
                <x-input-error :messages="$errors->get('guest_email')" class="mt-1" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">{{ __('site.booking.guests') }}</label>
                <input type="number" name="guests" min="1" max="{{ $unit->accommodationType->max_guests }}" value="{{ old('guests', 1) }}" required class="w-full border-gray-300 rounded-md sm:w-40">
                <x-input-error :messages="$errors->get('guests')" class="mt-1" />
            </div>

            @if ($activities->count())
                <div>
                    <label class="block text-sm font-medium mb-2">{{ __('site.booking.activities_optional') }}</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach ($activities as $activity)
                            <label class="flex items-center gap-2 text-sm bg-gray-50 rounded-md px-3 py-2">
                                <input type="checkbox" name="activities[]" value="{{ $activity->id }}" @checked(in_array($activity->id, old('activities', [])))>
                                @if ($activity->image)
                                    <x-focal-image :src="$activity->image_url" :focal="$activity->image_focal_style" class="w-6 h-6 rounded shrink-0" />
                                @endif
                                {{ $activity->display_name }}
                                <span class="text-xs text-gray-400">({{ $activity->price_label }})</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($services->count())
                <div>
                    <label class="block text-sm font-medium mb-2">{{ __('site.booking.services_optional') }}</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach ($services as $service)
                            @php $serviceClosed = in_array($service->id, $closedServiceIds, true); @endphp
                            <label @class(['flex items-center gap-2 text-sm bg-gray-50 rounded-md px-3 py-2', 'opacity-50' => $serviceClosed])>
                                <input type="checkbox" name="services[]" value="{{ $service->id }}"
                                       @checked(! $serviceClosed && in_array($service->id, old('services', [])))
                                       @disabled($serviceClosed)>
                                @if ($service->image)
                                    <x-focal-image :src="$service->image_url" :focal="$service->image_focal_style" class="w-6 h-6 rounded shrink-0" />
                                @endif
                                {{ $service->display_name }}
                                @if ($serviceClosed)
                                    <span class="text-xs text-red-500">({{ __('site.booking.service_closed_note') }})</span>
                                @else
                                    <span class="text-xs text-gray-400">({{ $service->price_label }})</span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('services')" class="mt-1" />
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium mb-1">{{ __('site.booking.notes') }}</label>
                <textarea name="notes" rows="3" class="w-full border-gray-300 rounded-md">{{ old('notes') }}</textarea>
            </div>

            <button type="submit" class="w-full bg-emerald-700 text-white rounded-md py-3 font-medium hover:bg-emerald-800">{{ __('site.booking.confirm') }}</button>
            <p class="text-xs text-gray-400 text-center">{{ __('site.booking.confirm_note') }}</p>
        </form>
    @endif
</x-layouts.public>
