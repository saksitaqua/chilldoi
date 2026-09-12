<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) ? $title.' | '.config('app.name') : config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
    <nav class="bg-white border-b border-gray-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between h-16 items-center">
            <a href="{{ route('home') }}" class="flex items-center h-14 py-1">
                <img src="{{ asset('images/logo-bg.png') }}" alt="{{ config('app.name') }}" class="h-full w-auto">
            </a>
            <div class="flex gap-6 text-sm font-medium">
                <a href="{{ route('home') }}" class="hover:text-emerald-700">หน้าแรก</a>
                <a href="{{ route('availability.index') }}" class="hover:text-emerald-700">เช็คห้องว่าง</a>
                <a href="{{ route('map.index') }}" class="hover:text-emerald-700">แผนที่</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="hover:text-emerald-700">แอดมิน</a>
                @else
                    <a href="{{ route('login') }}" class="hover:text-emerald-700">เข้าสู่ระบบ</a>
                @endauth
            </div>
        </div>
    </nav>

    {{ $hero ?? '' }}

    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('status'))
            <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
