@props([
    'src',
    'nameX',
    'nameY',
    'x' => 50,
    'y' => 50,
    'aspect' => '16 / 9',
    'previewWidth' => 'w-40',
])

@php
    $x = max(0, min(100, (int) $x));
    $y = max(0, min(100, (int) $y));
@endphp

<div {{ $attributes->merge(['class' => 'focal-picker']) }} data-focal>
    <div class="relative inline-block max-w-full cursor-crosshair select-none touch-none align-top" data-focal-area>
        <img src="{{ $src }}" alt="" draggable="false" class="block max-w-full max-h-56 rounded-md border border-gray-200">
        <span data-focal-dot class="absolute w-5 h-5 -ml-2.5 -mt-2.5 rounded-full border-2 border-white bg-emerald-600/70 ring-2 ring-emerald-700 shadow pointer-events-none"
              style="left: {{ $x }}%; top: {{ $y }}%;"></span>
    </div>

    <div class="mt-2 flex items-start gap-3">
        <div class="shrink-0">
            <p class="text-xs text-gray-500 mb-1">ตัวอย่างที่แสดงบนเว็บ</p>
            <div class="{{ $previewWidth }} overflow-hidden rounded border border-gray-200 bg-gray-100" style="aspect-ratio: {{ $aspect }};">
                <img data-focal-preview src="{{ $src }}" alt="" class="w-full h-full object-cover" style="object-position: {{ $x }}% {{ $y }}%;">
            </div>
        </div>
        <p class="text-xs text-gray-400 pt-5">คลิกหรือลากบนรูปเพื่อเลือกส่วนที่อยากให้เห็นเสมอ</p>
    </div>

    <input type="hidden" name="{{ $nameX }}" value="{{ $x }}" data-focal-x>
    <input type="hidden" name="{{ $nameY }}" value="{{ $y }}" data-focal-y>
</div>

@once
    <script>
        (function () {
            let active = null;

            function update(area, event) {
                const img = area.querySelector('img');
                const rect = img.getBoundingClientRect();
                if (! rect.width || ! rect.height) return;

                const x = Math.round(Math.min(100, Math.max(0, (event.clientX - rect.left) / rect.width * 100)));
                const y = Math.round(Math.min(100, Math.max(0, (event.clientY - rect.top) / rect.height * 100)));
                const root = area.closest('[data-focal]');

                area.querySelector('[data-focal-dot]').style.left = x + '%';
                area.querySelector('[data-focal-dot]').style.top = y + '%';
                root.querySelector('[data-focal-preview]').style.objectPosition = x + '% ' + y + '%';
                root.querySelector('[data-focal-x]').value = x;
                root.querySelector('[data-focal-y]').value = y;
            }

            document.addEventListener('pointerdown', function (event) {
                const area = event.target.closest('[data-focal-area]');
                if (! area) return;
                active = area;
                event.preventDefault();
                update(area, event);
            });

            document.addEventListener('pointermove', function (event) {
                if (active) update(active, event);
            });

            ['pointerup', 'pointercancel'].forEach(function (name) {
                document.addEventListener(name, function () { active = null; });
            });
        })();
    </script>
@endonce
