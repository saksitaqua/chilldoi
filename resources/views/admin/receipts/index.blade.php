<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">ใบเสร็จรับเงิน</h2>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8">
        <form method="GET" class="mb-4 flex flex-wrap gap-2 text-sm">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="ค้นหาเลขที่ / ชื่อ / เบอร์โทร" class="w-full sm:w-72 border-gray-300 rounded-md">
            <select name="status" onchange="this.form.submit()" class="border-gray-300 rounded-md">
                <option value="">ทุกสถานะ</option>
                <option value="active" @selected(request('status') === 'active')>ใช้งาน</option>
                <option value="void" @selected(request('status') === 'void')>ยกเลิกแล้ว</option>
            </select>
            <button type="submit" class="bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-800">ค้นหา</button>
            @if (request()->hasAny(['q', 'status']))
                <a href="{{ route('admin.receipts.index') }}" class="text-emerald-700 hover:underline self-center">ล้างตัวกรอง</a>
            @endif
        </form>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">เลขที่</th>
                        <th class="px-4 py-2 text-left">วันที่ออก</th>
                        <th class="px-4 py-2 text-left">ลูกค้า</th>
                        <th class="px-4 py-2 text-left">ที่พัก</th>
                        <th class="px-4 py-2 text-right">ยอดที่รับ</th>
                        <th class="px-4 py-2 text-left">สถานะ</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($receipts as $receipt)
                        <tr @class(['text-gray-400' => $receipt->isVoided()])>
                            <td class="px-4 py-2 font-medium whitespace-nowrap">{{ $receipt->receipt_no }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">{{ $receipt->issued_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-2">{{ $receipt->customer_name }}<br><span class="text-xs text-gray-400">{{ $receipt->customer_phone }}</span></td>
                            <td class="px-4 py-2">{{ $receipt->booking?->unit?->name ?? '-' }}</td>
                            <td class="px-4 py-2 text-right whitespace-nowrap">{{ number_format((float) $receipt->total_amount, 2) }}</td>
                            <td class="px-4 py-2">
                                @if ($receipt->isVoided())
                                    <span class="px-2 py-0.5 rounded text-xs bg-red-100 text-red-800" title="{{ $receipt->void_reason }}">ยกเลิกแล้ว</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-xs bg-emerald-100 text-emerald-800">ใช้งาน</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('admin.receipts.show', $receipt) }}" class="text-emerald-700 hover:underline">ดู / พิมพ์</a>
                                <a href="{{ route('admin.bookings.edit', $receipt->booking_id) }}#payments" class="text-gray-500 hover:underline">การจอง</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-gray-400">ยังไม่มีใบเสร็จ ออกใบเสร็จได้จากหน้าแก้ไขการจอง หลังบันทึกการรับชำระแล้ว</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $receipts->links() }}</div>
    </div>
</x-app-layout>
