@php $type = $type ?? null; @endphp

<div>
    <label class="block text-sm font-medium mb-1">ชื่อประเภท</label>
    <input type="text" name="name" value="{{ old('name', $type?->name) }}" required class="w-full border-gray-300 rounded-md">
    <x-input-error :messages="$errors->get('name')" class="mt-1" />
</div>

<div>
    <label class="block text-sm font-medium mb-1">คำอธิบาย</label>
    <x-emoji-picker target="description" />
    <textarea name="description" id="description" rows="3" class="w-full border-gray-300 rounded-md">{{ old('description', $type?->description) }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-1" />
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">จำนวนคนสูงสุด</label>
        <input type="number" name="max_guests" min="1" value="{{ old('max_guests', $type?->max_guests ?? 1) }}" required class="w-full border-gray-300 rounded-md">
        <x-input-error :messages="$errors->get('max_guests')" class="mt-1" />
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">ราคาเริ่มต้น (บาท)</label>
        <input type="number" step="0.01" min="0" name="base_price" value="{{ old('base_price', $type?->base_price ?? 0) }}" required class="w-full border-gray-300 rounded-md">
        <x-input-error :messages="$errors->get('base_price')" class="mt-1" />
    </div>
</div>

<div class="flex items-center gap-2">
    <input type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $type?->is_active ?? true))>
    <label for="is_active" class="text-sm">เปิดใช้งาน</label>
</div>
