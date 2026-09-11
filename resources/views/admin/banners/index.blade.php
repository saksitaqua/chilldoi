<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">แบนเนอร์หน้าหลัก</h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        <p class="text-sm text-gray-500 mb-4">
            ระบบดึงรูปภาพจากโฟลเดอร์ <code class="bg-gray-100 px-1 rounded">public/images/banner</code> โดยอัตโนมัติ
            ติ๊ก "ใช้งาน" เพื่อให้ภาพนั้นแสดงในสไลด์แบนเนอร์หน้าหลัก กำหนดลำดับการแสดง และปรับ "ตำแหน่งโฟกัสภาพ"
            สำหรับภาพแนวตั้งที่ถูกครอปไม่สวยได้ (พรีวิวด้านล่างคืออัตราส่วนเดียวกับหน้าเว็บจริง)
        </p>

        <form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data" class="bg-white rounded-lg shadow-sm p-6 mb-6">
            @csrf
            <label class="block text-sm font-medium mb-2">อัปโหลดรูปภาพแบนเนอร์ใหม่ (เลือกได้หลายรูป)</label>
            <div class="flex flex-wrap items-center gap-3">
                <input type="file" name="images[]" multiple accept="image/*" required class="flex-1 min-w-[200px]">
                <button type="submit" class="bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-800">อัปโหลด</button>
            </div>
            <x-input-error :messages="$errors->get('images.*')" class="mt-2" />
        </form>

        @if ($banners->isEmpty())
            <div class="bg-white rounded-lg shadow-sm p-6 text-sm text-gray-500">
                ไม่พบไฟล์รูปภาพในโฟลเดอร์ public/images/banner
            </div>
        @else
            <form method="POST" action="{{ route('admin.banners.update') }}" class="bg-white rounded-lg shadow-sm p-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-4 mb-6">
                    @foreach ($banners as $banner)
                        <div class="border rounded-lg overflow-hidden">
                            <img src="{{ $banner->url }}"
                                 class="w-full h-40 object-cover banner-preview"
                                 style="object-position: center {{ $banner->focal_position }};">
                            <div class="p-3 flex flex-wrap items-center justify-between gap-3">
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="hidden" name="banners[{{ $loop->index }}][id]" value="{{ $banner->id }}">
                                    <input type="checkbox" name="active[]" value="{{ $banner->id }}" @checked($banner->is_active)>
                                    ใช้งาน
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    ลำดับ
                                    <input type="number" name="banners[{{ $loop->index }}][sort_order]" value="{{ $banner->sort_order }}" min="0" class="w-16 border-gray-300 rounded-md">
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    ตำแหน่งโฟกัสภาพ
                                    <select name="banners[{{ $loop->index }}][focal_position]" class="focal-select border-gray-300 rounded-md text-sm">
                                        <option value="top" @selected($banner->focal_position === 'top')>บน</option>
                                        <option value="center" @selected($banner->focal_position === 'center')>กลาง</option>
                                        <option value="bottom" @selected($banner->focal_position === 'bottom')>ล่าง</option>
                                    </select>
                                </label>
                            </div>
                            <div class="px-3 pb-2 flex items-center justify-between">
                                <span class="text-xs text-gray-400">{{ $banner->filename }}</span>
                                <button type="button" class="text-xs text-red-600 hover:underline delete-banner-btn" data-id="{{ $banner->id }}">ลบรูปนี้</button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-800">บันทึก</button>
            </form>

            <form method="POST" id="delete-banner-form" class="hidden">
                @csrf
                @method('DELETE')
            </form>

            <script>
                document.querySelectorAll('.focal-select').forEach(function (select) {
                    select.addEventListener('change', function () {
                        const img = this.closest('.border').querySelector('.banner-preview');
                        img.style.objectPosition = 'center ' + this.value;
                    });
                });

                document.querySelectorAll('.delete-banner-btn').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        if (! confirm('ยืนยันการลบรูปนี้?')) return;
                        const form = document.getElementById('delete-banner-form');
                        form.action = '{{ url('admin/banners') }}/' + this.dataset.id;
                        form.submit();
                    });
                });
            </script>
        @endif
    </div>
</x-app-layout>
