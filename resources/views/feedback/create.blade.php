<x-layouts.public :title="__('site.feedback.title')">
    <div class="max-w-lg mx-auto bg-white rounded-lg shadow-sm p-8">
        @if (session('submitted') || $existing)
            <div class="text-center">
                <div class="text-5xl mb-4">💚</div>
                <h1 class="text-2xl font-bold mb-2">{{ __('site.feedback.thanks_title') }}</h1>
                <p class="text-gray-500">{{ __('site.feedback.thanks_body') }}</p>
                @if ($existing)
                    <p class="mt-4 text-amber-500 text-2xl tracking-widest" aria-label="{{ $existing->rating }}/5">{{ str_repeat('★', $existing->rating) }}<span class="text-gray-300">{{ str_repeat('★', 5 - $existing->rating) }}</span></p>
                @endif
                <a href="{{ route('home') }}" class="inline-block mt-6 text-emerald-700 hover:underline">{{ __('site.thankyou.back_home') }}</a>
            </div>
        @else
            <div class="text-center mb-6">
                <div class="text-5xl mb-3">🏕️</div>
                <h1 class="text-2xl font-bold mb-1">{{ __('site.feedback.title') }}</h1>
                <p class="text-gray-500 text-sm">{{ __('site.feedback.intro') }}</p>
            </div>

            <form method="POST" action="{{ route('feedback.store', [$receipt, $token]) }}" class="space-y-5"
                  x-data="{ rating: {{ (int) old('rating', 0) }}, hover: 0 }">
                @csrf

                <div class="text-center">
                    <p class="text-sm font-medium mb-2">{{ __('site.feedback.rating') }}</p>
                    <input type="hidden" name="rating" :value="rating">
                    <div class="inline-flex gap-1 text-4xl" @mouseleave="hover = 0">
                        @foreach (range(1, 5) as $star)
                            <button type="button" @click="rating = {{ $star }}" @mouseenter="hover = {{ $star }}"
                                    :class="(hover || rating) >= {{ $star }} ? 'text-amber-400' : 'text-gray-300'"
                                    class="leading-none px-0.5 focus:outline-none" aria-label="{{ $star }}">★</button>
                        @endforeach
                    </div>
                    @error('rating')
                        <p class="text-sm text-red-600 mt-2">{{ __('site.feedback.rating_required') }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1" for="comment">{{ __('site.feedback.comment') }}</label>
                    <textarea id="comment" name="comment" rows="5" maxlength="2000" class="w-full border-gray-300 rounded-md" placeholder="{{ __('site.feedback.comment_hint') }}">{{ old('comment') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1" for="name">{{ __('site.feedback.name') }}</label>
                    <input id="name" type="text" name="name" maxlength="100" value="{{ old('name') }}" class="w-full border-gray-300 rounded-md">
                </div>

                <button type="submit" class="w-full bg-emerald-700 text-white py-2.5 rounded-md font-medium hover:bg-emerald-800">{{ __('site.feedback.submit') }}</button>
            </form>
        @endif
    </div>
</x-layouts.public>
