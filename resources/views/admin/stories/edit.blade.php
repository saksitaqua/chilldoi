<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">แก้ไขเรื่องราว</h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('admin.stories.update', $story) }}" enctype="multipart/form-data" class="bg-white shadow-sm rounded-lg p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium mb-1">หัวข้อ</label>
                <input type="text" name="title" value="{{ old('title', $story->title) }}" required class="w-full border-gray-300 rounded-md">
                <x-input-error :messages="$errors->get('title')" class="mt-1" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">กลุ่มประเภท</label>
                <select name="category" required class="w-full border-gray-300 rounded-md">
                    @foreach (\App\Models\Story::CATEGORIES as $value => $label)
                        <option value="{{ $value }}" @selected(old('category', $story->category) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('category')" class="mt-1" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">คำอธิบาย</label>
                <textarea name="description" rows="5" class="w-full border-gray-300 rounded-md">{{ old('description', $story->description) }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-1" />
            </div>

            @if ($story->images->count())
                <div>
                    <label class="block text-sm font-medium mb-2">รูปภาพปัจจุบัน</label>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach ($story->images as $image)
                            <div class="relative">
                                <img src="{{ $image->url }}" class="w-full h-24 object-cover rounded-md">
                                <label class="absolute top-1 right-1 bg-white/90 rounded px-1 text-xs flex items-center gap-1">
                                    <input type="checkbox" name="remove_images[]" value="{{ $image->id }}"> ลบ
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium mb-1">เพิ่มรูปภาพใหม่</label>
                <input type="file" name="images[]" multiple accept="image/*" class="w-full">
                <x-input-error :messages="$errors->get('images.*')" class="mt-1" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">วันเริ่มแสดง (ถ้าไม่ระบุ แสดงทันที)</label>
                    <input type="date" name="starts_on" value="{{ old('starts_on', $story->starts_on?->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-md">
                    <x-input-error :messages="$errors->get('starts_on')" class="mt-1" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">วันสิ้นสุดการแสดง (ถ้าไม่ระบุ แสดงตลอดไป)</label>
                    <input type="date" name="ends_on" value="{{ old('ends_on', $story->ends_on?->format('Y-m-d')) }}" class="w-full border-gray-300 rounded-md">
                    <x-input-error :messages="$errors->get('ends_on')" class="mt-1" />
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_published" id="is_published" value="1" @checked(old('is_published', $story->is_published))>
                <label for="is_published" class="text-sm">เผยแพร่ (เปิดใช้งานโพสต์นี้)</label>
            </div>

            <div class="pt-2">
                <button type="submit" class="bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-800">บันทึก</button>
                <a href="{{ route('admin.stories.index') }}" class="ml-2 text-sm text-gray-500">ยกเลิก</a>
            </div>
        </form>
    </div>
</x-app-layout>
