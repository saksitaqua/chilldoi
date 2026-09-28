<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">ลูกค้า</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8">
        <form method="GET" class="mb-4 flex gap-2 text-sm">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="ค้นหาชื่อ / เบอร์โทร / อีเมล"
                   class="w-full sm:w-80 border-gray-300 rounded-md">
            <button type="submit" class="bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-800">ค้นหา</button>
            @if (request('q'))
                <a href="{{ route('admin.customers.index') }}" class="text-emerald-700 hover:underline self-center">ล้างตัวกรอง</a>
            @endif
        </form>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">ชื่อ</th>
                        <th class="px-4 py-2 text-left">เบอร์โทร</th>
                        <th class="px-4 py-2 text-left">อีเมล</th>
                        <th class="px-4 py-2 text-right">จำนวนครั้งที่จอง</th>
                        <th class="px-4 py-2 text-left">จองล่าสุด</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($customers as $customer)
                        <tr>
                            <td class="px-4 py-2 font-medium">{{ $customer->name }}</td>
                            <td class="px-4 py-2 text-gray-500">{{ $customer->phone ?: '-' }}</td>
                            <td class="px-4 py-2 text-gray-500">{{ $customer->email ?: '-' }}</td>
                            <td class="px-4 py-2 text-right">{{ $customer->bookings_count }}</td>
                            <td class="px-4 py-2 text-gray-500">{{ $customer->bookings_max_check_in ? \Illuminate\Support\Carbon::parse($customer->bookings_max_check_in)->format('d/m/Y') : '-' }}</td>
                            <td class="px-4 py-2 text-right">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="text-emerald-700 hover:underline">ดูประวัติ</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">ยังไม่มีข้อมูลลูกค้า</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $customers->links() }}</div>
    </div>
</x-app-layout>
