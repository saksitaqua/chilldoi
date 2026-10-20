@php $activity = $activity ?? null; @endphp

<div>
    <label class="block text-sm font-medium mb-1">ชื่อกิจกรรม</label>
    <input type="text" name="name" value="{{ old('name', $activity?->name) }}" required class="w-full border-gray-300 rounded-md">
    <x-input-error :messages="$errors->get('name')" class="mt-1" />
</div>

<div>
    <label class="block text-sm font-medium mb-1 text-gray-500">ชื่อกิจกรรม (English)</label>
    <input type="text" name="name_en" value="{{ old('name_en', $activity?->name_en) }}" class="w-full border-gray-300 rounded-md">
    <x-input-error :messages="$errors->get('name_en')" class="mt-1" />
</div>

<div>
    <label class="block text-sm font-medium mb-1">คำอธิบาย</label>
    <x-emoji-picker target="description" />
    <textarea name="description" id="description" rows="3" class="w-full border-gray-300 rounded-md">{{ old('description', $activity?->description) }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-1" />
</div>

<div>
    <label class="block text-sm font-medium mb-1 text-gray-500">คำอธิบาย (English)</label>
    <textarea name="description_en" rows="3" class="w-full border-gray-300 rounded-md">{{ old('description_en', $activity?->description_en) }}</textarea>
    <x-input-error :messages="$errors->get('description_en')" class="mt-1" />
</div>

<div>
    <label class="block text-sm font-medium mb-1">รูปภาพ</label>
    @if ($activity?->image)
        <div class="mb-2">
            <x-focal-picker :src="$activity->image_url"
                            :x="$activity->image_focal_x" :y="$activity->image_focal_y" :zoom="$activity->image_focal_zoom"
                            name-x="image_focal_x" name-y="image_focal_y" name-zoom="image_focal_zoom"
                            aspect="1 / 1" preview-width="w-24" />
            <label class="mt-2 inline-flex items-center gap-2 text-sm text-red-600">
                <input type="checkbox" name="remove_image" value="1"> ลบรูปนี้
            </label>
            <p class="text-xs text-gray-400">ถ้าอัปโหลดรูปใหม่ จุดโฟกัสจะรีเซ็ตเป็นกึ่งกลาง แล้วตั้งใหม่ได้ตอนแก้ไขครั้งถัดไป</p>
        </div>
    @endif
    <input type="file" name="image" accept="image/*" class="w-full">
    <x-input-error :messages="$errors->get('image')" class="mt-1" />
</div>

<div>
    <label class="block text-sm font-medium mb-1">วิดีโอ</label>
    @if ($activity?->video)
        <div class="flex items-center gap-3 mb-2">
            <video src="{{ $activity->video_url }}" class="w-40 rounded-md border" controls></video>
            <label class="flex items-center gap-2 text-sm text-red-600">
                <input type="checkbox" name="remove_video" value="1"> ลบวิดีโอนี้
            </label>
        </div>
    @endif
    <input type="file" name="video" accept="video/*" class="w-full">
    <p class="text-xs text-gray-400 mt-1">รองรับ mp4, mov, webm, avi ขนาดไม่เกิน 50MB</p>
    <x-input-error :messages="$errors->get('video')" class="mt-1" />
</div>

@php $isFree = old('is_free', $activity?->is_free ?? true); @endphp

<div>
    <div class="flex items-center gap-2 mb-2">
        <input type="hidden" name="is_free" value="0">
        <input type="checkbox" name="is_free" id="is_free" value="1" @checked($isFree)
               onchange="document.getElementById('price-field').classList.toggle('hidden', this.checked); document.getElementById('price-input').required = ! this.checked;">
        <label for="is_free" class="text-sm">กิจกรรมนี้ฟรี ไม่มีค่าใช้จ่าย</label>
    </div>

    <div id="price-field" class="{{ $isFree ? 'hidden' : '' }}">
        <label class="block text-sm font-medium mb-1">ราคา (บาท) <span class="text-red-500">*</span></label>
        <input type="number" id="price-input" step="0.01" min="0.01" name="price" value="{{ old('price', $activity?->price) }}" @required(! $isFree) class="w-full border-gray-300 rounded-md">
        <x-input-error :messages="$errors->get('price')" class="mt-1" />
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">วันเริ่มเปิดให้เลือก (ถ้าไม่ระบุ เปิดทันที)</label>
        <input type="date" name="starts_on" value="{{ old('starts_on', $activity?->starts_on?->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-md">
        <x-input-error :messages="$errors->get('starts_on')" class="mt-1" />
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">วันสิ้นสุด (ถ้าไม่ระบุ เปิดตลอดไป)</label>
        <input type="date" name="ends_on" value="{{ old('ends_on', $activity?->ends_on?->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-md">
        <x-input-error :messages="$errors->get('ends_on')" class="mt-1" />
    </div>
</div>

<div class="flex items-center gap-2">
    <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $activity?->is_active ?? true))>
    <label for="is_active" class="text-sm">เปิดใช้งาน (active)</label>
</div>
