<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">ประเภทที่พัก</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex justify-end mb-4">
            <a href="{{ route('admin.types.create') }}" class="bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-800">+ เพิ่มประเภทที่พัก</a>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">ชื่อ</th>
                        <th class="px-4 py-2 text-left">จำนวนหน่วย</th>
                        <th class="px-4 py-2 text-left">คนสูงสุด</th>
                        <th class="px-4 py-2 text-left">ราคาเริ่มต้น</th>
                        <th class="px-4 py-2 text-left">สถานะ</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($types as $type)
                        <tr>
                            <td class="px-4 py-2 font-medium">{{ $type->name }}</td>
                            <td class="px-4 py-2">{{ $type->units_count }}</td>
                            <td class="px-4 py-2">{{ $type->max_guests }}</td>
                            <td class="px-4 py-2">{{ number_format($type->base_price) }}</td>
                            <td class="px-4 py-2">
                                @if ($type->is_active)
                                    <span class="text-emerald-700">ใช้งาน</span>
                                @else
                                    <span class="text-gray-400">ปิดใช้งาน</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <a href="{{ route('admin.types.edit', $type) }}" class="text-emerald-700 hover:underline">แก้ไข</a>
                                <form action="{{ route('admin.types.destroy', $type) }}" method="POST" class="inline" onsubmit="return confirm('ยืนยันการลบ?')">
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

        <div class="mt-4">{{ $types->links() }}</div>
    </div>
</x-app-layout>
