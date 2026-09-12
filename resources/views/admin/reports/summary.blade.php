<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">รายงานสรุป</h2>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto sm:px-6 lg:px-8">
        <form method="GET" class="mb-6">
            <label class="text-sm font-medium mr-2">เดือน</label>
            <input type="month" name="month" value="{{ $month }}" onchange="this.form.submit()" class="border-gray-300 rounded-md text-sm">
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-xs text-gray-500">รับเงิน (ประมาณการจากการจอง)</p>
                <p class="text-2xl font-bold text-emerald-700">{{ number_format($incomeTotal, 2) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-xs text-gray-500">จ่ายเงิน (รายจ่าย)</p>
                <p class="text-2xl font-bold text-red-600">{{ number_format($expenseTotal, 2) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-4">
                <p class="text-xs text-gray-500">คงเหลือสุทธิ</p>
                <p class="text-2xl font-bold {{ ($incomeTotal - $expenseTotal) >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                    {{ number_format($incomeTotal - $expenseTotal, 2) }}
                </p>
            </div>
        </div>

        <h3 class="font-semibold mb-3">ส่วนรับเงิน — การจอง</h3>
        <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-8">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">ผู้จอง</th>
                        <th class="px-4 py-2 text-left">ที่พัก</th>
                        <th class="px-4 py-2 text-left">เช็คอิน</th>
                        <th class="px-4 py-2 text-right">คืน</th>
                        <th class="px-4 py-2 text-left">สถานะ</th>
                        <th class="px-4 py-2 text-right">ยอดประมาณการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($bookings as $booking)
                        <tr>
                            <td class="px-4 py-2 font-medium">{{ $booking->guest_name }}</td>
                            <td class="px-4 py-2 text-gray-500">{{ $booking->unit->name }}</td>
                            <td class="px-4 py-2">{{ $booking->check_in->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-right">{{ $booking->nights }}</td>
                            <td class="px-4 py-2">
                                <span @class([
                                    'px-2 py-0.5 rounded text-xs font-medium',
                                    'bg-yellow-100 text-yellow-800' => $booking->status === 'pending',
                                    'bg-emerald-100 text-emerald-800' => $booking->status === 'confirmed',
                                ])>{{ $booking->status === 'pending' ? 'รอยืนยัน' : 'ยืนยันแล้ว' }}</span>
                            </td>
                            <td class="px-4 py-2 text-right font-medium">{{ number_format($booking->computed_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">ไม่มีการจองในเดือนนี้</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <h3 class="font-semibold mb-3">ส่วนรับเงิน — รายรับเพิ่มเติม (บันทึกเอง)</h3>
        <div class="bg-white shadow-sm rounded-lg overflow-hidden mb-8">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">วันที่</th>
                        <th class="px-4 py-2 text-left">รายการ</th>
                        <th class="px-4 py-2 text-left">ผู้บันทึก</th>
                        <th class="px-4 py-2 text-right">จำนวนเงิน</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($manualIncomes as $income)
                        <tr>
                            <td class="px-4 py-2">{{ $income->expense_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 font-medium">{{ $income->item }}</td>
                            <td class="px-4 py-2 text-gray-500">{{ $income->creator->name }}</td>
                            <td class="px-4 py-2 text-right font-medium">{{ number_format($income->total_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">ไม่มีรายรับเพิ่มเติมในเดือนนี้</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <h3 class="font-semibold mb-3">ส่วนจ่ายเงิน — รายจ่าย</h3>
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">วันที่</th>
                        <th class="px-4 py-2 text-left">รายการ</th>
                        <th class="px-4 py-2 text-left">ผู้บันทึก</th>
                        <th class="px-4 py-2 text-right">จำนวนเงิน</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($expenses as $expense)
                        <tr>
                            <td class="px-4 py-2">{{ $expense->expense_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 font-medium">{{ $expense->item }}</td>
                            <td class="px-4 py-2 text-gray-500">{{ $expense->creator->name }}</td>
                            <td class="px-4 py-2 text-right font-medium">{{ number_format($expense->total_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">ไม่มีรายจ่ายในเดือนนี้</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <p class="text-xs text-gray-400 mt-4">
            * ยอดรับเงินจากการจองเป็นการประมาณการจากราคาที่พัก x จำนวนคืน บวกกิจกรรม/บริการเสริมที่เลือก ไม่ใช่ยอดที่ยืนยันการชำระจริงทั้งหมด (รวม {{ number_format($bookingIncomeTotal, 2) }} บาท + รายรับเพิ่มเติม {{ number_format($manualIncomeTotal, 2) }} บาท)
        </p>
    </div>
</x-app-layout>
