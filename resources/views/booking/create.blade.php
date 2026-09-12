<x-layouts.public :title="'จองที่พัก - '.$unit->name">
    <a href="{{ route('availability.index') }}" class="text-sm text-emerald-700 hover:underline">&larr; กลับหน้าตรวจสอบห้องว่าง</a>

    <div class="mt-4 mb-6 bg-white rounded-lg shadow-sm p-4 flex gap-4 items-center">
        @if ($unit->visibleImages->first())
            <img src="{{ $unit->visibleImages->first()->url }}" class="w-24 h-24 object-cover rounded-md">
        @endif
        <div>
            <p class="text-xs text-emerald-700 font-medium">{{ $unit->accommodationType->name }}</p>
            <h1 class="text-xl font-bold">{{ $unit->name }}</h1>
            <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($checkIn)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($checkOut)->format('d/m/Y') }}</p>
        </div>
    </div>

    @if (! $available)
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-md p-4 text-sm">
            ขออภัย ที่พัก/จุดนี้เพิ่งถูกจองในช่วงวันที่เลือกไปแล้ว
            <a href="{{ route('availability.index') }}" class="underline">กลับไปค้นหาที่พักอื่น</a>
        </div>
    @else
        <form method="POST" action="{{ route('booking.store', $unit) }}" class="bg-white rounded-lg shadow-sm p-6 space-y-4">
            @csrf
            <input type="hidden" name="check_in" value="{{ $checkIn }}">
            <input type="hidden" name="check_out" value="{{ $checkOut }}">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">ชื่อผู้จอง</label>
                    <input type="text" name="guest_name" value="{{ old('guest_name') }}" required class="w-full border-gray-300 rounded-md">
                    <x-input-error :messages="$errors->get('guest_name')" class="mt-1" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">เบอร์โทร</label>
                    <input type="text" name="guest_phone" value="{{ old('guest_phone') }}" required class="w-full border-gray-300 rounded-md">
                    <x-input-error :messages="$errors->get('guest_phone')" class="mt-1" />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">อีเมล <span class="text-red-500">*</span></label>
                <input type="email" name="guest_email" value="{{ old('guest_email') }}" required class="w-full border-gray-300 rounded-md" placeholder="you@example.com">
                <p class="text-xs text-gray-400 mt-1">ใช้สำหรับส่งข้อมูลยืนยันการจองและติดต่อกลับ</p>
                <x-input-error :messages="$errors->get('guest_email')" class="mt-1" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">จำนวนคน</label>
                <input type="number" name="guests" min="1" max="{{ $unit->accommodationType->max_guests }}" value="{{ old('guests', 1) }}" required class="w-full border-gray-300 rounded-md sm:w-40">
                <x-input-error :messages="$errors->get('guests')" class="mt-1" />
            </div>

            @if ($activities->count())
                <div>
                    <label class="block text-sm font-medium mb-2">กิจกรรมเสริม (เลือกได้)</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach ($activities as $activity)
                            <label class="flex items-center gap-2 text-sm bg-gray-50 rounded-md px-3 py-2">
                                <input type="checkbox" name="activities[]" value="{{ $activity->id }}" @checked(in_array($activity->id, old('activities', [])))>
                                @if ($activity->image)
                                    <img src="{{ $activity->image_url }}" class="w-6 h-6 object-cover rounded">
                                @endif
                                {{ $activity->name }}
                                <span class="text-xs text-gray-400">({{ $activity->price_label }})</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($services->count())
                <div>
                    <label class="block text-sm font-medium mb-2">บริการเสริม (เลือกได้)</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach ($services as $service)
                            <label class="flex items-center gap-2 text-sm bg-gray-50 rounded-md px-3 py-2">
                                <input type="checkbox" name="services[]" value="{{ $service->id }}" @checked(in_array($service->id, old('services', [])))>
                                @if ($service->image)
                                    <img src="{{ $service->image_url }}" class="w-6 h-6 object-cover rounded">
                                @endif
                                {{ $service->name }}
                                <span class="text-xs text-gray-400">({{ $service->price_label }})</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium mb-1">หมายเหตุเพิ่มเติม (ถ้ามี)</label>
                <textarea name="notes" rows="3" class="w-full border-gray-300 rounded-md">{{ old('notes') }}</textarea>
            </div>

            <button type="submit" class="w-full bg-emerald-700 text-white rounded-md py-3 font-medium hover:bg-emerald-800">ยืนยันการจอง</button>
            <p class="text-xs text-gray-400 text-center">การจองนี้จะรอเจ้าหน้าที่ยืนยันอีกครั้ง เราจะติดต่อกลับตามเบอร์โทรที่ให้ไว้</p>
        </form>
    @endif
</x-layouts.public>
