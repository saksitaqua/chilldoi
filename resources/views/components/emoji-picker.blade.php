@props(['target'])

@php
    $icons = [
        '📶' => 'WiFi', '🅿️' => 'ที่จอดรถ', '🍳' => 'อาหารเช้า', '🍽️' => 'อาหาร', '☕' => 'กาแฟ',
        '🔥' => 'แคมป์ไฟ', '🏊' => 'สระว่ายน้ำ', '🐾' => 'สัตว์เลี้ยง', '🚿' => 'ห้องอาบน้ำ', '🚻' => 'ห้องน้ำ',
        '🛏️' => 'เตียง', '❄️' => 'แอร์', '🌳' => 'ธรรมชาติ', '🏕️' => 'เต็นท์', '🏞️' => 'วิว',
        '🌅' => 'พระอาทิตย์ขึ้น', '🌄' => 'พระอาทิตย์ตก', '🚗' => 'ที่จอดรถ/รถยนต์', '🚲' => 'จักรยาน', '🎣' => 'ตกปลา',
        '⭐' => 'ดาว/แนะนำ', '✅' => 'ถูกต้อง/พร้อมใช้', '📍' => 'ตำแหน่ง', '📞' => 'โทรศัพท์', '💧' => 'น้ำ',
        '🌙' => 'กลางคืน', '☀️' => 'แดด', '🌧️' => 'ฝน', '🔌' => 'ปลั๊กไฟ', '🎪' => 'เต็นท์ใหญ่/งานอีเวนต์',
    ];
@endphp

<div class="mb-2">
    <p class="text-xs text-gray-400 mb-1">แทรกไอคอน (คลิกเพื่อเพิ่มในตำแหน่งเคอร์เซอร์)</p>
    <div class="flex flex-wrap gap-1">
        @foreach ($icons as $emoji => $label)
            <button type="button" title="{{ $label }}" data-emoji-target="{{ $target }}" data-emoji="{{ $emoji }}"
                    class="js-emoji-btn w-8 h-8 flex items-center justify-center text-lg rounded border border-gray-200 hover:bg-gray-50">
                {{ $emoji }}
            </button>
        @endforeach
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.js-emoji-btn');
                if (!btn) return;
                e.preventDefault();

                const el = document.getElementById(btn.dataset.emojiTarget);
                if (!el) return;

                const emoji = btn.dataset.emoji;
                const start = el.selectionStart ?? el.value.length;
                const end = el.selectionEnd ?? el.value.length;

                el.value = el.value.slice(0, start) + emoji + el.value.slice(end);
                el.focus();
                el.selectionStart = el.selectionEnd = start + emoji.length;
            });
        </script>
    @endpush
@endonce
