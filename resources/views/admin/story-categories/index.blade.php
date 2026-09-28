<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">กลุ่มโพสต์</h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex justify-end items-center mb-4">
            <a href="{{ route('admin.story-categories.create') }}" class="bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-800">+ เพิ่มกลุ่มโพสต์</a>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">ชื่อ (ไทย)</th>
                        <th class="px-4 py-2 text-left">ชื่อ (อังกฤษ)</th>
                        <th class="px-4 py-2 text-right">ลำดับ</th>
                        <th class="px-4 py-2 text-left">สถานะ</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($categories as $category)
                        <tr>
                            <td class="px-4 py-2 font-medium">{{ $category->name }}</td>
                            <td class="px-4 py-2 text-gray-500">{{ $category->name_en ?: '-' }}</td>
                            <td class="px-4 py-2 text-right">{{ $category->sort_order }}</td>
                            <td class="px-4 py-2">
                                @if ($category->is_active)
                                    <span class="text-xs bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded">เปิดใช้งาน</span>
                                @else
                                    <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded">ปิดใช้งาน</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <a href="{{ route('admin.story-categories.edit', $category) }}" class="text-emerald-700 hover:underline">แก้ไข</a>
                                <form action="{{ route('admin.story-categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('ยืนยันการลบ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">ลบ</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">ยังไม่มีกลุ่มโพสต์</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
