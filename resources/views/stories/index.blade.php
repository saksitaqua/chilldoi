<x-layouts.public :title="__('site.home.page_title')">
    @if ($groups !== null)
        <x-slot name="hero">
            <style>
                .cd-hero{ position:relative; min-height:480px; display:flex; flex-direction:column; overflow:hidden; color:#fdf9ee; }
                .cd-hero-media{ position:absolute; inset:0; }
                .cd-hero-media img{ width:100%; height:100%; object-fit:cover; display:block; }
                .cd-hero::after{ content:""; position:absolute; inset:0; background:linear-gradient(180deg, rgba(15,30,20,.12) 0%, rgba(15,30,20,.05) 28%, rgba(12,24,16,.58) 74%, rgba(9,18,12,.9) 100%); }
                .cd-hero-inner{ position:relative; z-index:2; max-width:72rem; margin:0 auto; padding:0 1rem; width:100%; }
                .cd-hero-copy{ margin-top:auto; padding:2.4rem 0 5.2rem; max-width:640px; }
                .cd-eyebrow{ font-family:'Kanit',sans-serif; text-transform:uppercase; letter-spacing:.14em; font-size:.72rem; font-weight:600; color:#e2914a; }
                .cd-hero-copy h1{ font-family:'Chonburi','Sarabun',sans-serif; font-weight:400; font-size:clamp(1.9rem,4.6vw,3.1rem); line-height:1.18; margin:.6rem 0 0; text-wrap:balance; }
                .cd-hero-copy p{ font-family:'Sarabun',sans-serif; font-size:1rem; line-height:1.75; margin-top:.9rem; color:#eee7d4; max-width:32rem; }
                .cd-hero-actions{ display:flex; flex-wrap:wrap; gap:.75rem; margin-top:1.6rem; }
                .cd-btn{ font-family:'Kanit',sans-serif; font-weight:600; font-size:.92rem; border-radius:10px; padding:.75rem 1.4rem; display:inline-block; }
                .cd-btn-primary{ background:#e2914a; color:#2a1607; }
                .cd-btn-primary:hover{ background:#c97430; }
                .cd-btn-ghost{ color:#fff; border:1.5px solid rgba(255,255,255,.55); }
                .cd-btn-ghost:hover{ border-color:#fff; }

                .cd-quick{ position:relative; z-index:3; max-width:60rem; margin:-3.6rem auto 0; padding:0 1rem; }
                .cd-quick-panel{
                    background:#fff; border:1px solid #dcdccb; box-shadow:0 18px 40px -18px rgba(23,54,38,.35);
                    border-radius:16px; padding:1.4rem 1.5rem 1.3rem;
                    clip-path: polygon(0 12px, 12px 0, calc(100% - 12px) 0, 100% 12px, 100% 100%, 0 100%);
                }
                .cd-quick-row{ display:grid; grid-template-columns:1fr 1fr 1.1fr auto; gap:1rem; align-items:end; }
                .cd-field label{ display:block; font-family:'Kanit',sans-serif; font-size:.72rem; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:#2f6b4f; margin-bottom:.35rem; }
                .cd-field input, .cd-field select{ width:100%; border:1.5px solid #dcdccb; border-radius:9px; padding:.55rem .7rem; font-family:'Sarabun',sans-serif; font-size:.92rem; background:#faf9f2; color:#1c261f; }
                .cd-quick .cd-btn-primary{ width:100%; padding:.68rem 1.1rem; text-align:center; border:none; cursor:pointer; }

                @media (max-width: 760px){
                    .cd-quick-row{ grid-template-columns:1fr 1fr; }
                    .cd-quick-row > .cd-field:nth-child(3){ grid-column:1/-1; }
                    .cd-quick-row > button{ grid-column:1/-1; }
                }
            </style>

            <div class="cd-hero">
                <div class="cd-hero-media">
                    @if ($banners->count())
                        <div class="w-full h-full overflow-hidden relative" id="banner-carousel">
                            <div class="flex h-full transition-transform duration-700 ease-in-out" id="banner-track">
                                @foreach ($banners as $banner)
                                    <img src="{{ $banner->url }}" alt="บรรยากาศ Chill Doi Camping & Farm" class="w-full h-full object-cover shrink-0"
                                         style="object-position: center {{ $banner->focal_position }};">
                                @endforeach
                                <img src="{{ $banners->first()->url }}" alt="บรรยากาศ Chill Doi Camping & Farm" class="w-full h-full object-cover shrink-0"
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
                                        setTimeout(function () { index = 0; goToSlide(index, false); }, 700);
                                    }
                                }, 4000);
                            })();
                        </script>
                    @else
                        <img src="{{ asset('images/logo-bg.png') }}" alt="{{ config('app.name') }}" style="object-fit:contain;background:#173626;">
                    @endif
                </div>

                <div class="cd-hero-inner cd-hero-copy">
                    <p class="cd-eyebrow">{{ __('site.home.eyebrow') }}</p>
                    <h1>{{ __('site.home.headline_1') }}<br>{{ __('site.home.headline_2') }}</h1>
                    <p>{{ __('site.home.subhead') }}</p>
                    <div class="cd-hero-actions">
                        <a href="{{ route('availability.index') }}" class="cd-btn cd-btn-primary">{{ __('site.home.cta_check') }}</a>
                        <a href="#stories" class="cd-btn cd-btn-ghost">{{ __('site.home.cta_stories') }}</a>
                    </div>
                </div>
            </div>

            <div class="cd-quick">
                <form method="GET" action="{{ route('availability.index') }}" class="cd-quick-panel">
                    <div class="cd-quick-row">
                        <div class="cd-field">
                            <label>{{ __('site.home.quick_checkin') }}</label>
                            <input type="date" name="check_in" required>
                        </div>
                        <div class="cd-field">
                            <label>{{ __('site.home.quick_checkout') }}</label>
                            <input type="date" name="check_out" required>
                        </div>
                        <div class="cd-field">
                            <label>{{ __('site.home.quick_type') }}</label>
                            <select name="accommodation_type_id">
                                <option value="">{{ __('site.home.quick_type_all') }}</option>
                                @foreach ($accommodationTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="cd-btn cd-btn-primary">{{ __('site.home.quick_search') }}</button>
                    </div>
                </form>
            </div>
        </x-slot>
    @else
        <x-slot name="hero">
            @if ($banners->count())
                <div class="w-full h-56 sm:h-72 overflow-hidden relative" id="banner-carousel">
                    <div class="flex h-full transition-transform duration-700 ease-in-out" id="banner-track">
                        @foreach ($banners as $banner)
                            <img src="{{ $banner->url }}" alt="banner" class="w-full h-full object-cover shrink-0"
                                 style="object-position: center {{ $banner->focal_position }};">
                        @endforeach
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
                                setTimeout(function () { index = 0; goToSlide(index, false); }, 700);
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
    @endif

    @if ($groups !== null)
        <style>
            .cd-section{ margin-top:4.5rem; }
            .cd-section:first-of-type{ margin-top:2.5rem; }
            .cd-section-head{ display:flex; align-items:flex-end; justify-content:space-between; gap:1rem; margin-bottom:1.7rem; }
            .cd-section-head h2{ font-family:'Chonburi','Sarabun',sans-serif; font-weight:400; font-size:1.6rem; margin:.3rem 0 0; }
            .cd-more{ font-family:'Kanit',sans-serif; font-weight:600; font-size:.86rem; color:#2f6b4f; white-space:nowrap; }
            .cd-more:hover{ color:#c97430; }

            .cd-stays{ display:grid; grid-template-columns:repeat(auto-fit, minmax(260px,1fr)); gap:1.4rem; }
            .cd-stay-card{ background:#fff; border:1px solid #e4e3d2; border-radius:16px; overflow:hidden; display:flex; flex-direction:column; }
            .cd-stay-media{ position:relative; height:190px; background:#f2f1e7; }
            .cd-stay-media img{ width:100%; height:100%; object-fit:cover; display:block; }
            .cd-stay-empty{ width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#9aa393; font-size:.85rem; font-family:'Sarabun',sans-serif; }
            .cd-stay-body{ padding:1.2rem 1.3rem 1.35rem; display:flex; flex-direction:column; gap:.5rem; flex:1; }
            .cd-stay-body h3{ font-family:'Chonburi','Sarabun',sans-serif; font-weight:400; font-size:1.2rem; }
            .cd-stay-meta{ font-family:'Kanit',sans-serif; font-size:.8rem; color:#5b6357; }
            .cd-stay-foot{ margin-top:auto; display:flex; align-items:baseline; justify-content:space-between; padding-top:.55rem; border-top:1px dashed #e4e3d2; }
            .cd-stay-price{ font-family:'Kanit',sans-serif; font-weight:700; font-size:1.05rem; color:#2f6b4f; }
            .cd-stay-price span{ font-weight:400; font-size:.72rem; opacity:.7; margin-left:.15rem; }
            .cd-stay-link{ font-family:'Kanit',sans-serif; font-weight:600; font-size:.82rem; color:#c97430; }

            .cd-trail{ display:grid; grid-template-columns:repeat(auto-fit, minmax(260px,1fr)); gap:1.3rem; }
            .cd-trail-card{ position:relative; border-radius:16px; overflow:hidden; min-height:260px; display:flex; align-items:flex-end; background:#e4e3d2; }
            .cd-trail-card img{ position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
            .cd-trail-card::after{ content:""; position:absolute; inset:0; background:linear-gradient(0deg, rgba(10,20,13,.86) 0%, rgba(10,20,13,.2) 55%, rgba(10,20,13,0) 78%); }
            .cd-trail-body{ position:relative; z-index:2; padding:1.2rem 1.3rem; color:#f6f1e2; }
            .cd-trail-badge{ display:inline-block; font-family:'Kanit',sans-serif; font-size:.68rem; font-weight:600; letter-spacing:.06em; text-transform:uppercase; color:#173626; background:#e2914a; padding:.28rem .6rem; border-radius:100px; margin-bottom:.6rem; }
            .cd-trail-card h3{ font-family:'Sarabun',sans-serif; font-weight:700; font-size:1.08rem; color:#fff; }

            .cd-strip{ background:#faf9f2; border:1px solid #e4e3d2; border-radius:20px; padding:2rem 1.8rem; display:grid; grid-template-columns:.9fr 1.6fr; gap:2rem; align-items:center; }
            .cd-strip h2{ font-family:'Chonburi','Sarabun',sans-serif; font-weight:400; font-size:1.4rem; margin-top:.4rem; }
            .cd-strip .lead{ margin-top:.6rem; opacity:.75; font-size:.9rem; line-height:1.6; }
            .cd-chips{ display:flex; flex-wrap:wrap; gap:.6rem; }
            .cd-chip{ display:flex; align-items:center; gap:.5rem; background:#fff; border:1px solid #e4e3d2; border-radius:100px; padding:.55rem 1rem; font-size:.85rem; font-family:'Sarabun',sans-serif; }
            .cd-chip .dot{ width:7px; height:7px; border-radius:50%; background:#2f6b4f; flex:none; }
            .cd-chip.free .dot{ background:#e2914a; }
            .cd-chip b{ font-family:'Kanit',sans-serif; font-weight:600; }
            .cd-chip .price{ font-family:'Kanit',sans-serif; font-size:.74rem; opacity:.6; }

            @media (max-width: 760px){
                .cd-strip{ grid-template-columns:1fr; }
            }
        </style>

        @if ($accommodationTypes->isNotEmpty())
            <div class="cd-section">
                <div class="cd-section-head">
                    <div>
                        <p class="cd-eyebrow" style="color:#2f6b4f;">{{ __('site.home.stays_eyebrow') }}</p>
                        <h2>{{ __('site.home.stays_heading') }}</h2>
                    </div>
                    <a class="cd-more" href="{{ route('availability.index') }}">{{ __('site.nav.availability') }} →</a>
                </div>
                <div class="cd-stays">
                    @foreach ($accommodationTypes as $type)
                        <div class="cd-stay-card">
                            <div class="cd-stay-media">
                                @if ($type->cover_image)
                                    <img src="{{ $type->cover_image }}" alt="{{ $type->display_name }}">
                                @else
                                    <div class="cd-stay-empty">{{ __('site.home.no_image') }}</div>
                                @endif
                            </div>
                            <div class="cd-stay-body">
                                <h3>{{ $type->display_name }}</h3>
                                <p class="cd-stay-meta">{{ __('site.home.stays_capacity', ['count' => $type->max_guests]) }}</p>
                                <div class="cd-stay-foot">
                                    <div class="cd-stay-price">฿{{ number_format($type->base_price) }} <span>{{ __('site.home.stays_per_night') }}</span></div>
                                    <a class="cd-stay-link" href="{{ route('availability.index') }}">{{ __('site.home.stays_view') }}</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div id="stories" class="cd-section">
            @if ($groups->isEmpty())
                <div class="cd-section-head"><h2>{{ __('site.home.recent_stories') }}</h2></div>
                <p style="color:#5b6357;">{{ __('site.home.stories_none') }}</p>
            @else
                @foreach ($groups as $group)
                    <div class="{{ $loop->first ? '' : 'cd-section' }}">
                        <div class="cd-section-head">
                            <div>
                                <p class="cd-eyebrow" style="color:#2f6b4f;">{{ __('site.home.stories_eyebrow') }}</p>
                                <h2>{{ $group['label'] }}</h2>
                            </div>
                            <a class="cd-more" href="{{ route('home', ['category' => $group['key']]) }}">{{ __('site.home.view_all') }}</a>
                        </div>
                        <div class="cd-trail">
                            @foreach ($group['stories'] as $story)
                                @php $img = $story->images->first(); @endphp
                                <a href="{{ route('stories.show', $story) }}" class="cd-trail-card">
                                    @if ($img)
                                        <img src="{{ $img->url }}" alt="{{ $story->display_title }}">
                                    @endif
                                    <div class="cd-trail-body">
                                        <span class="cd-trail-badge">{{ $group['label'] }}</span>
                                        <h3>{{ $story->display_title }}</h3>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        @if ($activities->isNotEmpty() || $services->isNotEmpty())
            <div class="cd-section">
                <div class="cd-strip">
                    <div>
                        <p class="cd-eyebrow" style="color:#2f6b4f;">{{ __('site.home.extras_eyebrow') }}</p>
                        <h2>{{ __('site.home.extras_heading') }}</h2>
                        <p class="lead">{{ __('site.home.extras_lead') }}</p>
                    </div>
                    <div class="cd-chips">
                        @foreach ($activities as $activity)
                            <div class="cd-chip {{ $activity->is_free ? 'free' : '' }}">
                                <span class="dot"></span><b>{{ $activity->display_name }}</b>
                                <span class="price">{{ $activity->price_label }}</span>
                            </div>
                        @endforeach
                        @foreach ($services as $service)
                            <div class="cd-chip">
                                <span class="dot"></span><b>{{ $service->display_name }}</b>
                                <span class="price">{{ $service->price_label }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('home') }}" class="text-sm text-emerald-700 hover:underline">{{ __('site.home.back_to_all') }}</a>
            <h1 class="text-2xl font-bold">{{ \App\Models\Story::categoryLabel($filteredCategory) }}</h1>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($stories as $story)
                @include('stories._card', ['story' => $story])
            @empty
                <p class="text-gray-500">{{ __('site.home.no_stories_in_group') }}</p>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $stories->links() }}
        </div>
    @endif
</x-layouts.public>
