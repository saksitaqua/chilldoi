@php $service = $service ?? null; @endphp

<div>
    <label class="block text-sm font-medium mb-1">ชื่อบริการ</label>
    <input type="text" name="name" value="{{ old('name', $service?->name) }}" required placeholder="เช่น อาหาร 1 มื้อ" class="w-full border-gray-300 rounded-md">
    <x-input-error :messages="$errors->get('name')" class="mt-1" />
</div>

<div>
    <label class="block text-sm font-medium mb-1">คำอธิบาย (ถ้ามี)</label>
    <x-emoji-picker target="description" />
    <textarea name="description" id="description" rows="2" class="w-full border-gray-300 rounded-md">{{ old('description', $service?->description) }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-1" />
</div>

<div>
    <label class="block text-sm font-medium mb-1">รูปภาพ</label>
    @if ($service?->image)
        <div class="flex items-center gap-3 mb-2">
            <img src="{{ $service->image_url }}" class="w-24 h-24 object-cover rounded-md border">
            <label class="flex items-center gap-2 text-sm text-red-600">
                <input type="checkbox" name="remove_image" value="1"> ลบรูปนี้
            </label>
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
