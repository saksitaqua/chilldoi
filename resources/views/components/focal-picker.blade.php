@props([
    'src',
    'nameX',
    'nameY',
    'nameZoom',
    'x' => 50,
    'y' => 50,
    'zoom' => 100,
    'aspect' => '16 / 9',
    'previewWidth' => 'w-40',
])

@php
    $x = max(0, min(100, (int) $x));
    $y = max(0, min(100, (int) $y));
    $zoom = max(100, min(300, (int) $zoom));
    $scale = $zoom > 100 ? 'transform: scale('.($zoom / 100).'); transform-origin: '.$x.'% '.$y.'%;' : '';
@endphp

<div {{ $attributes->merge(['class' => 'focal-picker']) }} data-focal data-aspect="{{ $aspect }}">
    <p class="text-xs text-gray-500 mb-1">คลิกหรือลากบนรูปเพื่อเลือกจุดโฟกัส — กรอบสว่างคือส่วนที่จะแสดงบนเว็บ</p>

    <div class="relative inline-block max-w-full cursor-crosshair select-none touch-none align-top overflow-hidden rounded-md border border-gray-200" data-focal-area>
        <img src="{{ $src }}" alt="" draggable="false" class="block max-w-full max-h-72">
        <span data-focal-box class="absolute pointer-events-none border-2 border-white hidden"
              style="box-shadow: 0 0 0 9999px rgba(0,0,0,.5);"></span>
        <span data-focal-dot class="absolute w-4 h-4 -ml-2 -mt-2 rounded-full border-2 border-white bg-emerald-500 ring-2 ring-emerald-800 shadow pointer-events-none"
              style="left: {{ $x }}%; top: {{ $y }}%;"></span>
    </div>

    <p data-focal-hint class="hidden mt-1 text-xs text-amber-600">รูปนี้มีอัตราส่วนตรงกับกรอบอยู่แล้ว จึงยังไม่เห็นการครอป — เลื่อนแถบ "ซูม" เพื่อโฟกัสไปที่จุดที่เลือก</p>

    <div class="mt-2 flex flex-wrap items-center gap-3 text-sm">
        <label class="flex items-center gap-2">
            ซูม
            <input type="range" min="100" max="300" step="5" value="{{ $zoom }}" data-focal-zoom-range class="w-40 accent-emerald-700">
            <span data-focal-zoom-label class="w-12 text-gray-600 tabular-nums">{{ $zoom }}%</span>
        </label>
        <button type="button" data-focal-reset class="px-3 py-1 text-xs rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">รีเซ็ต (กลับเป็นแบบปกติ)</button>
    </div>

    <div class="mt-3">
        <p class="text-xs text-gray-500 mb-1">ตัวอย่างที่แสดงบนเว็บ</p>
        <div class="{{ $previewWidth }} overflow-hidden rounded border border-gray-200 bg-gray-100" style="aspect-ratio: {{ $aspect }};">
            <img data-focal-preview src="{{ $src }}" alt="" class="w-full h-full object-cover"
                 style="object-position: {{ $x }}% {{ $y }}%; {{ $scale }}">
        </div>
    </div>

    <input type="hidden" name="{{ $nameX }}" value="{{ $x }}" data-focal-x>
    <input type="hidden" name="{{ $nameY }}" value="{{ $y }}" data-focal-y>
    <input type="hidden" name="{{ $nameZoom }}" value="{{ $zoom }}" data-focal-zoom>
</div>

@once
    <script>
        (function () {
            let active = null;

            function parseAspect(text) {
                const parts = String(text).split('/').map(parseFloat);
                return (parts[0] || 16) / (parts[1] || 9);
            }

            function value(root, selector) {
                return parseFloat(root.querySelector(selector).value);
            }

            // The visible window (as fractions of the image) for a cover-fitted frame with zoom,
            // positioned so the focal point keeps its relative spot inside the frame.
            function render(root) {
                const x = value(root, '[data-focal-x]');
                const y = value(root, '[data-focal-y]');
                const zoom = value(root, '[data-focal-zoom]');
                const area = root.querySelector('[data-focal-area]');
                const img = area.querySelector('img');
                const preview = root.querySelector('[data-focal-preview]');

                preview.style.objectPosition = x + '% ' + y + '%';
                preview.style.transformOrigin = x + '% ' + y + '%';
                preview.style.transform = zoom > 100 ? 'scale(' + (zoom / 100) + ')' : 'none';

                const dot = area.querySelector('[data-focal-dot]');
                dot.style.left = x + '%';
                dot.style.top = y + '%';

                root.querySelector('[data-focal-zoom-label]').textContent = zoom + '%';

                const iw = img.naturalWidth;
                const ih = img.naturalHeight;
                if (! iw || ! ih) return;

                const frameW = parseAspect(root.dataset.aspect);
                const frameH = 1;
                const cover = Math.max(frameW / iw, frameH / ih);
                const factor = zoom / 100;
                const wf = Math.min(1, (frameW / factor) / (cover * iw));
                const hf = Math.min(1, (frameH / factor) / (cover * ih));
                const full = wf >= 0.999 && hf >= 0.999;

                const box = area.querySelector('[data-focal-box]');
                box.classList.toggle('hidden', full);
                box.style.left = (x / 100 * (1 - wf) * 100) + '%';
                box.style.top = (y / 100 * (1 - hf) * 100) + '%';
                box.style.width = (wf * 100) + '%';
                box.style.height = (hf * 100) + '%';

                root.querySelector('[data-focal-hint]').classList.toggle('hidden', ! full);
            }

            function setPoint(area, event) {
                const rect = area.querySelector('img').getBoundingClientRect();
                if (! rect.width || ! rect.height) return;

                const root = area.closest('[data-focal]');
                root.querySelector('[data-focal-x]').value = Math.round(Math.min(100, Math.max(0, (event.clientX - rect.left) / rect.width * 100)));
                root.querySelector('[data-focal-y]').value = Math.round(Math.min(100, Math.max(0, (event.clientY - rect.top) / rect.height * 100)));
                render(root);
            }

            document.addEventListener('pointerdown', function (event) {
                const area = event.target.closest('[data-focal-area]');
                if (! area) return;
                active = area;
                event.preventDefault();
                setPoint(area, event);
            });

            document.addEventListener('pointermove', function (event) {
                if (active) setPoint(active, event);
            });

            ['pointerup', 'pointercancel'].forEach(function (name) {
                document.addEventListener(name, function () { active = null; });
            });

            document.addEventListener('input', function (event) {
                if (! event.target.matches('[data-focal-zoom-range]')) return;
                const root = event.target.closest('[data-focal]');
                root.querySelector('[data-focal-zoom]').value = event.target.value;
                render(root);
            });

            document.addEventListener('click', function (event) {
                const button = event.target.closest('[data-focal-reset]');
                if (! button) return;
                const root = button.closest('[data-focal]');
                root.querySelector('[data-focal-x]').value = 50;
                root.querySelector('[data-focal-y]').value = 50;
                root.querySelector('[data-focal-zoom]').value = 100;
                root.querySelector('[data-focal-zoom-range]').value = 100;
                render(root);
            });

            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-focal]').forEach(function (root) {
                    const img = root.querySelector('[data-focal-area] img');
                    img.addEventListener('load', function () { render(root); });
                    render(root);
                });
            });
        })();
    </script>
@endonce
