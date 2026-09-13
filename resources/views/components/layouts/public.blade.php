<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) ? $title.' | '.config('app.name') : config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Chonburi&family=Sarabun:wght@400;500;600;700&family=Kanit:wght@400;500;600;700&display=swap" rel="stylesheet">
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

    <footer class="mt-16" style="background:#173626;">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex flex-wrap justify-between gap-10">
            <div class="max-w-sm">
                <img src="{{ asset('images/logo-bg.png') }}" alt="{{ config('app.name') }}" class="h-9 w-auto mb-3" style="filter:brightness(0) invert(1);opacity:.92;">
                <p class="text-sm text-white/70 leading-relaxed">พื้นที่เล็กๆ สำหรับคนรักธรรมชาติ กาแฟท้องถิ่น ผ้าทอปกาเกอะญอ แคมป์ปิ้ง เดินป่าศึกษาธรรมชาติ = เก็บความทรงจำ</p>
            </div>

            <div class="flex flex-wrap gap-12 text-sm">
                <div class="flex flex-col gap-2 text-white/85">
                    <span class="text-white/50 uppercase tracking-wider text-xs mb-1">สำรวจ</span>
                    <a href="{{ route('home') }}" class="text-white/85 hover:text-white">หน้าแรก</a>
                    <a href="{{ route('availability.index') }}" class="text-white/85 hover:text-white">เช็คห้องว่าง</a>
                    <a href="{{ route('map.index') }}" class="text-white/85 hover:text-white">แผนที่</a>
                </div>

                <div class="flex flex-col gap-2 text-white/85">
                    <span class="text-white/50 uppercase tracking-wider text-xs mb-1">ติดต่อเรา</span>
                    <a href="mailto:sales@chilldoicampingandfarm.com" class="text-white/85 hover:text-white break-all">✉️ sales@chilldoicampingandfarm.com</a>
                    <a href="tel:0869227117" class="text-white/85 hover:text-white">📞 086 922 7117 <span class="text-white/50">(คุณหม่อง)</span></a>
                    <a href="tel:0910670574" class="text-white/85 hover:text-white">📞 091 067 0574 <span class="text-white/50">(คุณหนุ่ย)</span></a>
                    <a href="https://www.facebook.com/profile.php?id=61576119053590" target="_blank" rel="noopener" class="text-white/85 hover:text-white">📘 Facebook</a>
                    <a href="https://www.youtube.com/@Chilldoicampingandfarm" target="_blank" rel="noopener" class="text-white/85 hover:text-white">▶️ YouTube</a>
                    <a href="https://www.tiktok.com/@chill.doi.camping" target="_blank" rel="noopener" class="text-white/85 hover:text-white">🎵 TikTok</a>
                </div>
            </div>
        </div>
        <div class="border-t border-white/10 text-center py-4 text-xs text-white/50">© {{ date('Y') }} {{ config('app.name') }}</div>
    </footer>
</body>
</html>
