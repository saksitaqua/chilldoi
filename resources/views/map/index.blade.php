<x-layouts.public :title="'แผนที่ที่พัก'">
    <h1 class="text-2xl font-bold mb-6">แผนที่ที่พักทั้งหมด</h1>

    @php $apiKey = config('services.google_maps.key'); @endphp

    @if (! $apiKey)
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-md p-4 text-sm">
            ยังไม่ได้ตั้งค่า Google Maps API Key กรุณาเพิ่ม <code>GOOGLE_MAPS_API_KEY</code> ในไฟล์ .env
        </div>
    @else
        <div id="map" class="w-full h-[500px] rounded-lg shadow-sm"></div>

        <script>
            const units = @json($mapPoints);

            function initMap() {
                if (units.length === 0) {
                    document.getElementById('map').innerHTML = '<p class="p-4 text-gray-500">ยังไม่มีการปักหมุดตำแหน่งที่พัก</p>';
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
