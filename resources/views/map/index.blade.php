<x-layouts.public :title="__('site.map.title')">
    <h1 class="text-2xl font-bold mb-6">{{ __('site.map.title') }}</h1>

    @php $apiKey = config('services.google_maps.key'); @endphp

    @if (! $apiKey)
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-md p-4 text-sm">
            {{ __('site.map.no_key_warning') }}
        </div>
    @else
        <div id="map" class="w-full h-[500px] rounded-lg shadow-sm"></div>

        <script>
            const units = @json($mapPoints);
            const noPinsText = @json(__('site.map.no_pins'));

            function initMap() {
                if (units.length === 0) {
                    document.getElementById('map').innerHTML = '<p class="p-4 text-gray-500">' + noPinsText + '</p>';
                    return;
                }

                const center = { lat: units[0].lat, lng: units[0].lng };
                const map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 14,
                    center,
                });

                units.forEach((unit) => {
                    const marker = new google.maps.Marker({
                        position: { lat: unit.lat, lng: unit.lng },
                        map,
                        title: unit.name,
                    });

                    const info = new google.maps.InfoWindow({
                        content: `<strong>${unit.name}</strong><br>${unit.type}`,
                    });

                    marker.addListener('click', () => info.open(map, marker));
                });
            }
        </script>
        <script src="https://maps.googleapis.com/maps/api/js?key={{ $apiKey }}&callback=initMap" async defer></script>
    @endif
</x-layouts.public>
