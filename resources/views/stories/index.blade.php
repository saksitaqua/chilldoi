<x-layouts.public :title="'เรื่องราวจากลานกางเต็นท์'">
    <x-slot name="hero">
        @if ($banners->count())
            <div class="w-full h-56 sm:h-80 overflow-hidden relative" id="banner-carousel">
                <div class="flex h-full transition-transform duration-700 ease-in-out" id="banner-track">
                    @foreach ($banners as $banner)
                        <img src="{{ $banner->url }}" alt="banner" class="w-full h-full object-cover shrink-0"
                             style="object-position: center {{ $banner->focal_position }};">
                    @endforeach
                    {{-- clone of first slide for seamless loop --}}
                    <img src="{{ $banners->first()->url }}" alt="banner" class="w-full h-full object-cover shrink-0"
                         style="object-position: center {{ $banners->first()->focal_position }};">
                </div>
            </div>

            <script>
                (function () {
                    const track = document.getElementById('banner-track');
                    const slideCount = {{ $banners->count() }};
                    let index = 0;

                    function goToSlide(i, animate = true) {
                        track.style.transition = animate ? 'transform 700ms ease-in-out' : 'none';
                        track.style.transform = `translateX(-${i * 100}%)`;
                    }

                    setInterval(function () {
                        index++;
                        goToSlide(index);

                        if (index === slideCount) {
                            setTimeout(function () {
                                index = 0;
                                goToSlide(index, false);
                            }, 700);
                        }
                    }, 4000);
                })();
            </script>
        @else
            <div class="w-full h-56 sm:h-72 bg-center bg-no-repeat"
                 style="background-color: #1f3236; background-image: url('{{ asset('images/logo-bg.png') }}'); background-size: contain;">
            </div>
        @endif
    </x-slot>

    @if ($groups !== null)
        @if ($groups->isEmpty())
            <h1 class="text-2xl font-bold mb-6">เรื่องราวล่าสุด</h1>
            <p class="text-gray-500">ยังไม่มีเรื่องราวที่โพสต์</p>
        @else
            @foreach ($groups as $group)
                <div class="flex items-center justify-between mb-4 {{ $loop->first ? '' : 'mt-12' }}">
                    <h2 class="text-2xl font-bold">{{ $group['label'] }}</h2>
                    <a href="{{ route('home', ['category' => $group['key']]) }}" class="text-sm text-emerald-700 hover:underline">ดูทั้งหมด &rarr;</a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($group['stories'] as $story)
                        @include('stories._card', ['story' => $story])
                    @endforeach
                </div>
            @endforeach
        @endif
    @else
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('home') }}" class="text-sm text-emerald-700 hover:underline">&larr; กลับหน้ารวม</a>
            <h1 class="text-2xl font-bold">{{ \App\Models\Story::CATEGORIES[$filteredCategory] ?? $filteredCategory }}</h1>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($stories as $story)
                @include('stories._card', ['story' => $story])
            @empty
                <p class="text-gray-500">ยังไม่มีเรื่องราวในกลุ่มนี้</p>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $stories->links() }}
        </div>
    @endif
</x-layouts.public>
