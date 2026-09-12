@php $unit = $unit ?? null; @endphp

<div>
    <label class="block text-sm font-medium mb-1">ประเภทที่พัก</label>
    <select name="accommodation_type_id" required class="w-full border-gray-300 rounded-md">
        <option value="">-- เลือกประเภท --</option>
        @foreach ($types as $type)
            <option value="{{ $type->id }}" @selected(old('accommodation_type_id', $unit?->accommodation_type_id) == $type->id)>{{ $type->name }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('accommodation_type_id')" class="mt-1" />
</div>

<div>
    <label class="block text-sm font-medium mb-1">ชื่อ / เลขที่</label>
    <input type="text" name="name" value="{{ old('name', $unit?->name) }}" required class="w-full border-gray-300 rounded-md">
    <x-input-error :messages="$errors->get('name')" class="mt-1" />
</div>

<div>
    <label class="block text-sm font-medium mb-1">รหัส (ถ้ามี)</label>
    <input type="text" name="code" value="{{ old('code', $unit?->code) }}" class="w-full border-gray-300 rounded-md">
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">Latitude</label>
        <input type="text" name="lat" value="{{ old('lat', $unit?->lat) }}" class="w-full border-gray-300 rounded-md" placeholder="13.7563">
        <x-input-error :messages="$errors->get('lat')" class="mt-1" />
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Longitude</label>
        <input type="text" name="lng" value="{{ old('lng', $unit?->lng) }}" class="w-full border-gray-300 rounded-md" placeholder="100.5018">
        <x-input-error :messages="$errors->get('lng')" class="mt-1" />
    </div>
</div>

<div>
    <label class="block text-sm font-medium mb-1">หมายเหตุ</label>
    <textarea name="notes" rows="2" class="w-full border-gray-300 rounded-md">{{ old('notes', $unit?->notes) }}</textarea>
</div>

<div class="flex items-center gap-2">
    <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $unit?->is_active ?? true))>
    <label for="is_active" class="text-sm">เปิดใช้งาน</label>
</div>

@if ($unit && $unit->images->count())
    <div>
        <label class="block text-sm font-medium mb-2">รูปภาพปัจจุบัน <span class="text-gray-400 font-normal">(ติ๊ก "แสดง" เพื่อให้ขึ้นหน้าเว็บสาธารณะ)</span></label>
        <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
            @foreach ($unit->images as $image)
                <div class="relative border rounded-md overflow-hidden">
                    <img src="{{ $image->url }}" class="w-full h-24 object-cover">
                    <div class="p-1 text-xs bg-gray-50 flex justify-between items-center">
                        <label class="flex items-center gap-1">
                            <input type="checkbox" name="visible_images[]" value="{{ $image->id }}" @checked($image->is_visible)>
                            แสดง
                        </label>
                        <label class="flex items-center gap-1 text-red-600">
                            <input type="checkbox" name="remove_images[]" value="{{ $image->id }}">
                            ลบ
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div>
    <label class="block text-sm font-medium mb-1">เพิ่มรูปภาพ (เลือกได้หลายรูป)</label>
    <input type="file" name="images[]" multiple accept="image/*" class="w-full">
    <x-input-error :messages="$errors->get('images.*')" class="mt-1" />
</div>

<div>
    <label class="block text-sm font-medium mb-1">วิดีโอ</label>
    @if ($unit?->video)
        <div class="flex items-center gap-3 mb-2">
            <video src="{{ $unit->video_url }}" class="w-40 rounded-md border" controls></video>
            <label class="flex items-center gap-2 text-sm text-red-600">
                <input type="checkbox" name="remove_video" value="1"> ลบวิดีโอนี้
            </label>
        </div>
    @endif
    <input type="file" name="video" accept="video/*" class="w-full">
    <p class="text-xs text-gray-400 mt-1">รองรับ mp4, mov, webm, avi ขนาดไม่เกิน 50MB</p>
    <x-input-error :messages="$errors->get('video')" class="mt-1" />
</div>
