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
                <h1 class="text-2xl font-bold mb-2">{{ __('site.feedback.title') }}</h1>
                <p class="text-gray-500 text-sm">{{ __('site.feedback.intro') }}</p>
            </div>

            <form method="POST" action="{{ route('feedback.store', [$receipt, $token]) }}" class="space-y-6"
                  x-data="{ rating: {{ (int) old('rating', 0) }}, hover: 0 }">
                @csrf

                <div>
                    <p class="font-medium">1. {{ __('site.feedback.q1') }}</p>
                    <p class="text-xs text-gray-500 mb-2">{{ __('site.feedback.q1_hint') }}</p>
                    <input type="hidden" name="rating" :value="rating">
                    <div class="flex justify-center gap-1 text-4xl" @mouseleave="hover = 0">
                        @foreach (range(1, 5) as $star)
                            <button type="button" @click="rating = {{ $star }}" @mouseenter="hover = {{ $star }}"
                                    :class="(hover || rating) >= {{ $star }} ? 'text-amber-400' : 'text-gray-300'"
                                    class="leading-none px-0.5 focus:outline-none" aria-label="{{ $star }}">★</button>
                        @endforeach
                    </div>
                    @error('rating')
                        <p class="text-sm text-red-600 mt-2 text-center">{{ __('site.feedback.rating_required') }}</p>
                    @enderror
                    <textarea name="comment" rows="2" maxlength="2000" class="mt-3 w-full border-gray-300 rounded-md text-sm" placeholder="{{ __('site.feedback.q1_reason') }} ({{ __('site.feedback.optional') }})">{{ old('comment') }}</textarea>
                </div>

                <div>
                    <label class="block font-medium mb-1" for="best">2. {{ __('site.feedback.q2') }}</label>
                    <textarea id="best" name="best" rows="2" maxlength="2000" class="w-full border-gray-300 rounded-md text-sm" placeholder="({{ __('site.feedback.optional') }})">{{ old('best') }}</textarea>
                </div>

                <div>
                    <p class="font-medium mb-2">3. {{ __('site.feedback.q3') }}</p>
                    <div class="flex flex-wrap gap-2 mb-2">
                        @foreach (\App\Models\Feedback::TAGS as $key => $label)
                            <label class="cursor-pointer">
                                <input type="checkbox" name="facility_tags[]" value="{{ $key }}" class="peer sr-only" @checked(in_array($key, old('facility_tags', [])))>
                                <span class="inline-block px-3 py-1 rounded-full border border-gray-300 text-sm text-gray-600 peer-checked:bg-emerald-700 peer-checked:border-emerald-700 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-emerald-500">{{ __('site.feedback.tags.'.$key) }}</span>
                            </label>
                        @endforeach
                    </div>
                    <textarea name="facility" rows="2" maxlength="2000" class="w-full border-gray-300 rounded-md text-sm" placeholder="{{ __('site.feedback.q3_more') }} ({{ __('site.feedback.optional') }})">{{ old('facility') }}</textarea>
                </div>

                <div>
                    <label class="block font-medium mb-1" for="service">4. {{ __('site.feedback.q4') }}</label>
                    <textarea id="service" name="service" rows="2" maxlength="2000" class="w-full border-gray-300 rounded-md text-sm" placeholder="({{ __('site.feedback.optional') }})">{{ old('service') }}</textarea>
                </div>

                <div>
                    <label class="block font-medium mb-1" for="future">5. {{ __('site.feedback.q5') }}</label>
                    <p class="text-xs text-gray-500 mb-1">{{ __('site.feedback.q5_hint') }}</p>
                    <textarea id="future" name="future" rows="2" maxlength="2000" class="w-full border-gray-300 rounded-md text-sm" placeholder="({{ __('site.feedback.optional') }})">{{ old('future') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1" for="name">{{ __('site.feedback.name') }}</label>
                    <input id="name" type="text" name="name" maxlength="100" value="{{ old('name') }}" class="w-full border-gray-300 rounded-md">
                </div>

                <button type="submit" class="w-full bg-emerald-700 text-white py-2.5 rounded-md font-medium hover:bg-emerald-800">{{ __('site.feedback.submit') }}</button>
                <p class="text-center text-xs text-gray-500">{{ __('site.feedback.footer') }}</p>
            </form>
        @endif
    </div>
</x-layouts.public>
