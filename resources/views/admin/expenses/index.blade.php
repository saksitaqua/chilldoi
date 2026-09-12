@php
    $title = $type === 'income' ? 'บันทึกรายรับ' : 'บันทึกรายจ่าย';
    $addLabel = $type === 'income' ? '+ เพิ่มรายรับ' : '+ เพิ่มรายจ่าย';
@endphp
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
            <form method="GET" class="flex gap-2 text-sm">
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="month" name="month" value="{{ request('month') }}" onchange="this.form.submit()" class="border-gray-300 rounded-md">
                @if (request('month'))
                    <a href="{{ route('admin.expenses.index', ['type' => $type]) }}" class="text-emerald-700 hover:underline self-center">ล้างตัวกรอง</a>
                @endif
            </form>
            <a href="{{ route('admin.expenses.create', ['type' => $type]) }}" class="bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-800">{{ $addLabel }}</a>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">วันที่</th>
                        <th class="px-4 py-2 text-left">รายการ</th>
                        <th class="px-4 py-2 text-right">จำนวน</th>
                        <th class="px-4 py-2 text-right">ราคาต่อหน่วย</th>
                        <th class="px-4 py-2 text-right">รวม</th>
                        <th class="px-4 py-2 text-left">ผู้บันทึก</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($expenses as $expense)
                        <tr>
                            <td class="px-4 py-2">{{ $expense->expense_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 font-medium">
                                {{ $expense->item }}
                                @if ($expense->receipt_file)
                                    <a href="{{ $expense->receipt_url }}" target="_blank" rel="noopener" class="text-emerald-700 text-xs ml-1">📎</a>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right">{{ rtrim(rtrim(number_format($expense->quantity, 2), '0'), '.') }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format($expense->unit_price, 2) }}</td>
                            <td class="px-4 py-2 text-right font-medium">{{ number_format($expense->total_amount, 2) }}</td>
                            <td class="px-4 py-2 text-gray-500">{{ $expense->creator->name }}</td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <a href="{{ route('admin.expenses.edit', $expense) }}" class="text-emerald-700 hover:underline">แก้ไข</a>
                                <form action="{{ route('admin.expenses.destroy', $expense) }}" method="POST" class="inline" onsubmit="return confirm('ยืนยันการลบ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">ลบ</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-gray-400">{{ $type === 'income' ? 'ยังไม่มีรายการรายรับ' : 'ยังไม่มีรายการรายจ่าย' }}</td></tr>
                    @endforelse
                </tbody>
                @if ($expenses->count())
                    <tfoot>
                        <tr class="bg-gray-50 font-semibold">
                            <td colspan="4" class="px-4 py-2 text-right">รวมทั้งหมด</td>
                            <td class="px-4 py-2 text-right">{{ number_format($total, 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <div class="mt-4">{{ $expenses->links() }}</div>
    </div>
</x-app-layout>
