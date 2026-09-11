<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Story</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex justify-end mb-4">
            <a href="{{ route('admin.stories.create') }}" class="bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-800">+ โพสต์ใหม่</a>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">หัวข้อ</th>
                        <th class="px-4 py-2 text-left">กลุ่ม</th>
                        <th class="px-4 py-2 text-left">จำนวนรูป</th>
                        <th class="px-4 py-2 text-left">สถานะ</th>
                        <th class="px-4 py-2 text-left">ช่วงเวลาแสดง</th>
                        <th class="px-4 py-2 text-left">วันที่โพสต์</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($stories as $story)
                        <tr>
                            <td class="px-4 py-2 font-medium">{{ $story->title }}</td>
                            <td class="px-4 py-2 text-xs">
                                <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $story->category_label }}</span>
                            </td>
                            <td class="px-4 py-2">{{ $story->images_count }}</td>
                            <td class="px-4 py-2">
                                @if (! $story->is_published)
                                    <span class="text-gray-400">ปิดใช้งาน</span>
                                @elseif ($story->isVisibleNow())
                                    <span class="text-emerald-700">กำลังแสดง</span>
                                @elseif ($story->starts_on && $story->starts_on->isFuture())
                                    <span class="text-yellow-600">รอถึงวันแสดง</span>
                                @else
                                    <span class="text-red-500">หมดเวลาแสดง</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-gray-500 text-xs">
                                {{ $story->starts_on?->format('d/m/Y') ?? 'ทันที' }}
                                &ndash;
                                {{ $story->ends_on?->format('d/m/Y') ?? 'ตลอดไป' }}
                            </td>
                            <td class="px-4 py-2 text-gray-500">{{ $story->created_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <a href="{{ route('admin.stories.edit', $story) }}" class="text-emerald-700 hover:underline">แก้ไข</a>
                                <form action="{{ route('admin.stories.destroy', $story) }}" method="POST" class="inline" onsubmit="return confirm('ยืนยันการลบ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">ลบ</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $stories->links() }}</div>
    </div>
</x-app-layout>
