<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">เพิ่มกลุ่มโพสต์</h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-lg p-6">
            <form method="POST" action="{{ route('admin.story-categories.store') }}">
                @include('admin.story-categories._form')
            </form>
        </div>
    </div>
</x-app-layout>
