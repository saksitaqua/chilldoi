<x-layouts.public :title="__('site.map.title')">
    <h1 class="text-2xl font-bold mb-2">{{ __('site.map.title') }}</h1>
    <p class="text-gray-500 mb-6">{{ $shop['name'] }}</p>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="rounded-lg overflow-hidden shadow-sm border border-gray-200 bg-white">
                <iframe src="{{ $shop['embed_url'] }}" title="{{ $shop['name'] }}" class="w-full h-80 sm:h-[460px] block" style="border:0;" loading="lazy" referrerpolicy="no-referrer"></iframe>
            </div>
            <p class="text-xs text-gray-400 mt-2">
                <a href="{{ $shop['osm_url'] }}" target="_blank" rel="noopener" class="hover:underline">{{ __('site.map.view_larger') }}</a>
            </p>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-lg shadow-sm p-5 space-y-3">
                <a href="{{ $shop['directions_url'] }}" target="_blank" rel="noopener"
                   class="block text-center bg-emerald-700 text-white font-medium rounded-md py-3 hover:bg-emerald-800">
                    🧭 {{ __('site.map.navigate') }}
                </a>
                <a href="{{ $shop['place_url'] }}" target="_blank" rel="noopener"
                   class="block text-center border border-gray-300 text-gray-700 font-medium rounded-md py-3 hover:bg-gray-50">
                    📍 {{ __('site.map.open_google') }}
                </a>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-5 text-sm space-y-2">
                <h2 class="font-semibold text-base">{{ __('site.map.find_us') }}</h2>
                <p class="text-gray-700">{{ $shop['address'] }}</p>
                <p class="text-gray-500">{{ __('site.map.coordinates') }}:
                    <button type="button" data-copy="{{ $shop['coords'] }}" class="underline decoration-dotted hover:text-emerald-700" title="{{ __('site.map.copy') }}">{{ $shop['coords'] }}</button>
                </p>
                <p class="text-gray-500">Plus Code: <span class="font-medium text-gray-700">{{ $shop['plus_code'] }}</span></p>

                <div class="pt-2 border-t border-gray-100 space-y-1">
                    <a href="tel:0869227117" class="block text-emerald-700 hover:underline">📞 086 922 7117 <span class="text-gray-400">({{ __('site.map.contact_maung') }})</span></a>
                    <a href="tel:0910670574" class="block text-emerald-700 hover:underline">📞 091 067 0574 <span class="text-gray-400">({{ __('site.map.contact_nui') }})</span></a>
                    <a href="mailto:{{ $shop['email'] }}" class="block text-emerald-700 hover:underline break-all">✉️ {{ $shop['email'] }}</a>
                </div>
            </div>
        </div>
    </div>

    @if ($directions)
        <div class="bg-white rounded-lg shadow-sm p-5 mt-6 text-sm">
            <h2 class="font-semibold text-base mb-2">{{ __('site.map.how_to_get_here') }}</h2>
            <div class="space-y-2 text-gray-700">
                @foreach ($directions as $line)
                    <p>{{ $line }}</p>
                @endforeach
            </div>
        </div>
    @endif

    <script>
        document.querySelectorAll('[data-copy]').forEach(function (button) {
            button.addEventListener('click', function () {
                var text = button.dataset.copy;
                var original = button.textContent;
                var done = function () { button.textContent = @json(__('site.map.copied')); setTimeout(function () { button.textContent = original; }, 1500); };

                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(text).then(done, function () { window.prompt('', text); });
                } else {
                    window.prompt('', text);
                }
            });
        });
    </script>
</x-layouts.public>
