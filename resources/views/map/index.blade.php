<x-layouts.public :title="__('site.map.title')">
    <h1 class="text-2xl font-bold mb-6">{{ __('site.map.title') }}</h1>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" integrity="sha512-h9FcoyWjHcOcmEVkxOfTLnmZFWIH0iZhZT1H2TbOq55xssQGEJHEaIm+PgoUaZbRvQTNTluNOEfb1ZRy6D3BOw==" crossorigin="anonymous" referrerpolicy="no-referrer">

    <div id="map" class="w-full h-[500px] rounded-lg shadow-sm z-0"></div>
    <p class="text-xs text-gray-400 mt-2">{{ __('site.map.attribution_prefix') }} <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener" class="underline">OpenStreetMap</a> {{ __('site.map.attribution_suffix') }}</p>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js" integrity="sha512-puJW3E/qXDqYp9IfhAI54BJEaWIfloJ7JWs7OeD5i6ruC9JZL1gERT1wjtwXFlh7CjE7ZJ+/vcRZRkIYIb6p4g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        (function () {
            const units = @json($mapPoints);
            const noPinsText = @json(__('site.map.no_pins'));
            const mapEl = document.getElementById('map');

            if (units.length === 0) {
                mapEl.innerHTML = '<p class="p-4 text-gray-500">' + noPinsText + '</p>';
                return;
            }

            const map = L.map('map').setView([units[0].lat, units[0].lng], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            }).addTo(map);

            const bounds = [];

            units.forEach((unit) => {
                const marker = L.marker([unit.lat, unit.lng]).addTo(map);
                marker.bindPopup('<strong>' + unit.name + '</strong><br>' + unit.type);
                bounds.push([unit.lat, unit.lng]);
            });

            if (bounds.length > 1) {
                map.fitBounds(bounds, { padding: [30, 30] });
            }
        })();
    </script>
</x-layouts.public>
