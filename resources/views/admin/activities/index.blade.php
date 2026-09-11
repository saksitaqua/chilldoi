<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">ประเภทกิจกรรม</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex justify-end mb-4">
            <a href="{{ route('admin.activities.create') }}" class="bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-800">+ เพิ่มกิจกรรม</a>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">ชื่อกิจกรรม</th>
                        <th class="px-4 py-2 text-left">ราคา</th>
                        <th class="px-4 py-2 text-left">ช่วงเวลาเปิดให้เลือก</th>
                        <th class="px-4 py-2 text-left">สถานะ</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($activities as $activity)
                        <tr>
                            <td class="px-4 py-2 font-medium">{{ $activity->name }}</td>
                            <td class="px-4 py-2">
                                @if ($activity->is_free)
                                    <span class="text-emerald-700">ฟรี</span>
                                @else
                                    <span>{{ number_format($activity->price) }} บาท</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-xs text-gray-500">
                                {{ $activity->starts_on?->format('d/m/Y') ?? 'ทันที' }}
                                &ndash;
                                {{ $activity->ends_on?->format('d/m/Y') ?? 'ตลอดไป' }}
                            </td>
                            <td class="px-4 py-2">
                                @if (! $activity->is_active)
                                    <span class="text-gray-400">ปิดใช้งาน</span>
                                @elseif ($activity->isAvailableNow())
                                    <span class="text-emerald-700">เปิดใช้งาน</span>
                                @elseif ($activity->starts_on && $activity->starts_on->isFuture())
                                    <span class="text-yellow-600">รอถึงวันเปิด</span>
                                @else
                                    <span class="text-red-500">หมดเวลา</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <a href="{{ route('admin.activities.edit', $activity) }}" class="text-emerald-700 hover:underline">แก้ไข</a>
                                <form action="{{ route('admin.activities.destroy', $activity) }}" method="POST" class="inline" onsubmit="return confirm('ยืนยันการลบ?')">
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

        <div class="mt-4">{{ $activities->links() }}</div>
    </div>
</x-app-layout>
