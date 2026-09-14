<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">เพิ่มแผนงาน</h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('admin.plans.store') }}" class="bg-white shadow-sm rounded-lg p-6 space-y-4">
            @csrf
            @include('admin.plans._form')

            <div class="pt-2">
                <button type="submit" class="bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-emerald-800">บันทึก</button>
                <a href="{{ route('admin.plans.index') }}" class="ml-2 text-sm text-gray-500">ยกเลิก</a>
            </div>
        </form>
    </div>
</x-app-layout>
