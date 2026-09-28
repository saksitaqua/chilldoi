@csrf

@if (!isset($category))
    <input type="hidden" name="type" value="{{ $type }}">
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อ (ไทย)</label>
        <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" required
               class="w-full border-gray-300 rounded-md">
        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อ (อังกฤษ) <span class="text-gray-400">(ไม่บังคับ)</span></label>
        <input type="text" name="name_en" value="{{ old('name_en', $category->name_en ?? '') }}"
               class="w-full border-gray-300 rounded-md">
        @error('name_en') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">ลำดับการแสดงผล</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" min="0"
               class="w-full border-gray-300 rounded-md">
        @error('sort_order') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>
    <div class="flex items-center mt-6">
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true)) class="rounded border-gray-300">
            เปิดใช้งาน
        </label>
    </div>
</div>

<div class="mt-6 flex gap-3">
    <button type="submit" class="bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-800">บันทึก</button>
    <a href="{{ route('admin.expense-categories.index', ['type' => $category->type ?? $type]) }}" class="text-gray-500 hover:underline self-center text-sm">ยกเลิก</a>
</div>
