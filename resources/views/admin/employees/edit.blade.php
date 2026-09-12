<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">แก้ไขพนักงาน</h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('admin.employees.update', $employee) }}" class="bg-white shadow-sm rounded-lg p-6 space-y-4">
            @csrf
            @method('PUT')
            @include('admin.employees._form')

            <div class="pt-2">
                <button type="submit" class="bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-800">บันทึก</button>
                <a href="{{ route('admin.employees.index') }}" class="ml-2 text-sm text-gray-500">ยกเลิก</a>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.employees.reset-password', $employee) }}" class="mt-4" onsubmit="return confirm('ยืนยันการรีเซ็ตรหัสผ่าน? รหัสผ่านเดิมจะใช้ไม่ได้ทันที')">
            @csrf
            <button type="submit" class="text-sm text-red-600 hover:underline">รีเซ็ตรหัสผ่าน (สุ่มรหัสใหม่)</button>
        </form>
    </div>
</x-app-layout>
