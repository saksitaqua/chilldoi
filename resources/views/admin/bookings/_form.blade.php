@php
    $booking = $booking ?? null;
    $selectedActivities = old('activities', $booking?->activities->pluck('id')->all() ?? []);
    $selectedServices = old('services', $booking?->services->pluck('id')->all() ?? []);
@endphp

<div>
    <label class="block text-sm font-medium mb-1">ที่พัก / จุดกางเต็นท์</label>
    <select name="unit_id" required class="w-full border-gray-300 rounded-md">
        <option value="">-- เลือกที่พัก --</option>
        @foreach ($units as $unit)
            <option value="{{ $unit->id }}" @selected(old('unit_id', $booking?->unit_id) == $unit->id)>{{ $unit->name }} ({{ $unit->accommodationType->name }})</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('unit_id')" class="mt-1" />
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">ชื่อผู้จอง</label>
        <input type="text" name="guest_name" value="{{ old('guest_name', $booking?->guest_name) }}" required class="w-full border-gray-300 rounded-md">
        <x-input-error :messages="$errors->get('guest_name')" class="mt-1" />
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">เบอร์โทร</label>
        <input type="text" name="guest_phone" value="{{ old('guest_phone', $booking?->guest_phone) }}" class="w-full border-gray-300 rounded-md">
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">วันเช็คอิน</label>
        <input type="date" name="check_in" value="{{ old('check_in', $booking?->check_in?->format('Y-m-d')) }}" required class="w-full border-gray-300 rounded-md">
        <x-input-error :messages="$errors->get('check_in')" class="mt-1" />
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">วันเช็คเอาท์</label>
        <input type="date" name="check_out" value="{{ old('check_out', $booking?->check_out?->format('Y-m-d')) }}" required class="w-full border-gray-300 rounded-md">
        <x-input-error :messages="$errors->get('check_out')" class="mt-1" />
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">จำนวนคน</label>
        <input type="number" name="guests" min="1" value="{{ old('guests', $booking?->guests ?? 1) }}" required class="w-full border-gray-300 rounded-md">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">สถานะ</label>
        <select name="status" required class="w-full border-gray-300 rounded-md">
            <option value="pending" @selected(old('status', $booking?->status ?? 'pending') == 'pending')>รอยืนยัน</option>
            <option value="confirmed" @selected(old('status', $booking?->status) == 'confirmed')>ยืนยันแล้ว</option>
            <option value="cancelled" @selected(old('status', $booking?->status) == 'cancelled')>ยกเลิก</option>
        </select>
    </div>
</div>

@if ($activities->count())
    <div>
        <label class="block text-sm font-medium mb-2">กิจกรรมเสริม</label>
        <div class="grid grid-cols-2 gap-2">
            @foreach ($activities as $activity)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="activities[]" value="{{ $activity->id }}" @checked(in_array($activity->id, $selectedActivities))>
                    @if ($activity->image)
                        <img src="{{ $activity->image_url }}" class="w-6 h-6 object-cover rounded">
                    @endif
                    {{ $activity->name }}
                    <span class="text-xs text-gray-400">({{ $activity->price_label }})</span>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('activities')" class="mt-1" />
    </div>
@endif

@if ($services->count())
    <div>
        <label class="block text-sm font-medium mb-2">บริการเสริม</label>
        <div class="grid grid-cols-2 gap-2">
            @foreach ($services as $service)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="services[]" value="{{ $service->id }}" @checked(in_array($service->id, $selectedServices))>
                    @if ($service->image)
                        <img src="{{ $service->image_url }}" class="w-6 h-6 object-cover rounded">
                    @endif
                    {{ $service->name }}
                    <span class="text-xs text-gray-400">({{ $service->price_label }})</span>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('services')" class="mt-1" />
    </div>
@endif

<div>
    <label class="block text-sm font-medium mb-1">หมายเหตุ</label>
    <textarea name="notes" rows="2" class="w-full border-gray-300 rounded-md">{{ old('notes', $booking?->notes) }}</textarea>
</div>
