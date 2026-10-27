@php $service = $service ?? null; @endphp

<div>
    <label class="block text-sm font-medium mb-1">ชื่อบริการ</label>
    <input type="text" name="name" value="{{ old('name', $service?->name) }}" required placeholder="เช่น อาหาร 1 มื้อ" class="w-full border-gray-300 rounded-md">
    <x-input-error :messages="$errors->get('name')" class="mt-1" />
</div>

<div>
    <label class="block text-sm font-medium mb-1 text-gray-500">ชื่อบริการ (English)</label>
    <input type="text" name="name_en" value="{{ old('name_en', $service?->name_en) }}" placeholder="e.g. 1 Meal" class="w-full border-gray-300 rounded-md">
    <x-input-error :messages="$errors->get('name_en')" class="mt-1" />
</div>

<div>
    <label class="block text-sm font-medium mb-1">คำอธิบาย (ถ้ามี)</label>
    <x-emoji-picker target="description" />
    <textarea name="description" id="description" rows="2" class="w-full border-gray-300 rounded-md">{{ old('description', $service?->description) }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-1" />
</div>

<div>
    <label class="block text-sm font-medium mb-1 text-gray-500">คำอธิบาย (English)</label>
    <textarea name="description_en" rows="2" class="w-full border-gray-300 rounded-md">{{ old('description_en', $service?->description_en) }}</textarea>
    <x-input-error :messages="$errors->get('description_en')" class="mt-1" />
</div>

<div>
    <label class="block text-sm font-medium mb-1">รูปภาพ</label>
    @if ($service?->image)
        <div class="mb-2">
            <x-focal-picker :src="$service->image_url"
                            :x="$service->image_focal_x" :y="$service->image_focal_y" :zoom="$service->image_focal_zoom"
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

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">ราคา (บาท)</label>
        <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $service?->price) }}" required class="w-full border-gray-300 rounded-md">
        <x-input-error :messages="$errors->get('price')" class="mt-1" />
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">ลำดับการแสดง</label>
        <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $service?->sort_order ?? 0) }}" class="w-full border-gray-300 rounded-md">
    </div>
</div>

<div class="flex items-center gap-2">
    <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $service?->is_active ?? true))>
    <label for="is_active" class="text-sm">เปิดใช้งาน</label>
</div>

<x-closure-editor :closures="$service?->closures ?? collect()" hint="ถ้าวันเข้าพักของลูกค้าตรงกับช่วงที่เลือก จะเลือกบริการนี้ไม่ได้" />
