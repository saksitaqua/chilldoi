@php $title = $status === 'done' ? 'แผนงาน — เสร็จแล้ว' : 'แผนงาน — รอดำเนินการ'; @endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $title }}</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex justify-between items-center mb-4">
            <div class="flex gap-2 text-sm">
                <a href="{{ route('admin.plans.index', ['status' => 'pending']) }}"
                   class="px-3 py-1.5 rounded-md {{ $status === 'pending' ? 'bg-emerald-700 text-white' : 'bg-white border border-gray-300 text-gray-600' }}">รอดำเนินการ</a>
                <a href="{{ route('admin.plans.index', ['status' => 'done']) }}"
                   class="px-3 py-1.5 rounded-md {{ $status === 'done' ? 'bg-emerald-700 text-white' : 'bg-white border border-gray-300 text-gray-600' }}">เสร็จแล้ว</a>
            </div>
            <a href="{{ route('admin.plans.create') }}" class="bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-800">+ เพิ่มแผนงาน</a>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">แผนงาน</th>
                        <th class="px-4 py-2 text-left">วันครบกำหนด</th>
                        <th class="px-4 py-2 text-right">งบประมาณ</th>
                        <th class="px-4 py-2 text-left">ผู้บันทึก</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($plans as $plan)
                        <tr>
                            <td class="px-4 py-2 font-medium">
                                {{ $plan->title }}
                                @if ($plan->notes)
                                    <p class="text-xs text-gray-400 mt-0.5 line-clamp-1">{{ $plan->notes }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                @if ($plan->due_date)
                                    <span @class([
                                        'font-medium' => true,
                                        'text-red-600' => $plan->isOverdue(),
                                        'text-orange-600' => $plan->isDueSoon() && ! $plan->isOverdue(),
                                    ])>{{ $plan->due_date->format('d/m/Y') }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right">{{ $plan->budget ? number_format($plan->budget, 2) : '-' }}</td>
                            <td class="px-4 py-2 text-gray-500">{{ $plan->creator->name }}</td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <a href="{{ route('admin.plans.edit', $plan) }}" class="text-emerald-700 hover:underline">แก้ไข</a>
                                <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" class="inline" onsubmit="return confirm('ยืนยันการลบ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">ลบ</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">ยังไม่มีแผนงาน</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $plans->links() }}</div>
    </div>
</x-app-layout>
