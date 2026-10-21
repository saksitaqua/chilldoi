@props([
    'unit',
    'typeName' => null,
    'capacity' => null,
    'size' => 'w-10 h-10',
])

@php $image = $unit->visibleImages->first(); @endphp

<div class="relative inline-block shrink-0 align-middle" data-unit-thumb tabindex="0">
    @if ($image)
        <x-focal-image :src="$image->url" :focal="$image->focal_style" alt="{{ $unit->name }}" class="{{ $size }} rounded-md border border-gray-200" />
    @else
        <div class="{{ $size }} rounded-md border border-gray-200 bg-gray-100"></div>
    @endif

    <div data-unit-pop class="hidden fixed z-50 w-56 bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden pointer-events-none text-left">
        @if ($image)
            <x-focal-image :src="$image->url" :focal="$image->focal_style" alt="{{ $unit->name }}" class="w-full h-36" />
        @else
            <div class="w-full h-36 bg-gray-100"></div>
        @endif
        <div class="p-3">
            <p class="font-semibold text-gray-800">{{ $unit->name }}</p>
            @if ($typeName)
                <p class="text-xs text-emerald-700">{{ $typeName }}</p>
            @endif
            @if ($capacity)
                <p class="text-xs text-gray-500 mt-1">{{ $capacity }}</p>
            @endif
        </div>
    </div>
</div>

@once
    <script>
        (function () {
            let current = null;

            function hide() {
                if (! current) return;
                current.querySelector('[data-unit-pop]').classList.add('hidden');
                current = null;
            }

            function show(thumb) {
                if (current === thumb) return;
                hide();

                const pop = thumb.querySelector('[data-unit-pop]');
                pop.classList.remove('hidden');

                const rect = thumb.getBoundingClientRect();
                const width = pop.offsetWidth;
                const height = pop.offsetHeight;
                const margin = 8;

                let left = rect.right + margin;
                let top = rect.top + rect.height / 2 - height / 2;

                if (left + width > window.innerWidth - margin) {
                    left = Math.min(Math.max(margin, rect.left), window.innerWidth - width - margin);
                    top = rect.bottom + margin;
                    if (top + height > window.innerHeight - margin) top = rect.top - height - margin;
                }

                top = Math.min(Math.max(margin, top), Math.max(margin, window.innerHeight - height - margin));

                pop.style.left = left + 'px';
                pop.style.top = top + 'px';
                current = thumb;
            }

            document.addEventListener('mouseover', function (event) {
                const thumb = event.target.closest('[data-unit-thumb]');
                if (thumb) show(thumb); else hide();
            });

            document.addEventListener('focusin', function (event) {
                const thumb = event.target.closest('[data-unit-thumb]');
                if (thumb) show(thumb);
            });

            document.addEventListener('focusout', function (event) {
                if (current && ! current.contains(event.relatedTarget)) hide();
            });

            ['scroll', 'resize'].forEach(function (name) {
                window.addEventListener(name, hide, true);
            });
        })();
    </script>
@endonce
