@php
    $title = $category->type === 'income' ? 'แก้ไขประเภทรายรับ' : 'แก้ไขประเภทรายจ่าย';
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $title }}</h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg p-6">
            <form method="POST" action="{{ route('admin.expense-categories.update', $category) }}">
                @method('PUT')
                @include('admin.expense-categories._form')
            </form>
        </div>
    </div>
</x-app-layout>
