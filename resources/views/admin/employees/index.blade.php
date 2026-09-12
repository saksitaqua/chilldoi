<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">พนักงาน</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if (session('generated_password'))
            <div class="mb-4 rounded-md bg-yellow-50 border border-yellow-300 text-yellow-900 px-4 py-3 text-sm">
                <p class="font-medium mb-1">ข้อมูลเข้าสู่ระบบของ {{ session('generated_for') }} (แสดงครั้งเดียว กรุณาคัดลอกเก็บไว้)</p>
                <p>Username: <span class="font-mono font-semibold">{{ session('generated_username') }}</span></p>
                <p>Password: <span class="font-mono font-semibold">{{ session('generated_password') }}</span></p>
            </div>
        @endif

        <div class="flex justify-end mb-4">
            <a href="{{ route('admin.employees.create') }}" class="bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-800">+ เพิ่มพนักงาน</a>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">ชื่อ</th>
                        <th class="px-4 py-2 text-left">เบอร์โทร</th>
                        <th class="px-4 py-2 text-left">Username</th>
                        <th class="px-4 py-2 text-left">สิทธิ์</th>
                        <th class="px-4 py-2 text-left">สถานะ</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($employees as $employee)
                        <tr>
                            <td class="px-4 py-2 font-medium">{{ $employee->name }}</td>
                            <td class="px-4 py-2 text-gray-500">{{ $employee->phone ?: '-' }}</td>
                            <td class="px-4 py-2 font-mono text-gray-500">{{ $employee->username }}</td>
                            <td class="px-4 py-2">{{ $employee->role_label }}</td>
                            <td class="px-4 py-2">
                                <span @class([
                                    'px-2 py-0.5 rounded text-xs font-medium',
                                    'bg-emerald-100 text-emerald-800' => $employee->status === 'active',
                                    'bg-gray-100 text-gray-600' => $employee->status === 'inactive',
                                ])>{{ $employee->status === 'active' ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <a href="{{ route('admin.employees.edit', $employee) }}" class="text-emerald-700 hover:underline">แก้ไข</a>
                                <form action="{{ route('admin.employees.destroy', $employee) }}" method="POST" class="inline" onsubmit="return confirm('ยืนยันการลบพนักงานนี้?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">ลบ</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">ยังไม่มีพนักงาน</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $employees->links() }}</div>
    </div>
</x-app-layout>
