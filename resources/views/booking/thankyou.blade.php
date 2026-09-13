<x-layouts.public :title="__('site.thankyou.title')">
    <div class="max-w-lg mx-auto bg-white rounded-lg shadow-sm p-8 text-center">
        @if (session('status'))
            <div class="mb-4 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm text-left">
                {{ session('status') }}
            </div>
        @endif

        <div class="text-5xl mb-4">🏕️</div>
        <h1 class="text-2xl font-bold mb-2">{{ __('site.thankyou.title') }}</h1>
        <p class="text-gray-500 mb-6">{{ __('site.thankyou.will_contact', ['phone' => $booking->guest_phone]) }}</p>

        <div class="text-left bg-gray-50 rounded-md p-4 text-sm space-y-2">
            <div class="flex justify-between"><span class="text-gray-500">{{ __('site.thankyou.unit') }}</span><span class="font-medium">{{ $booking->unit->name }} ({{ $booking->unit->accommodationType->display_name }})</span></div>
            <div class="flex justify-between"><span class="text-gray-500">{{ __('site.thankyou.checkin') }}</span><span class="font-medium">{{ $booking->check_in->format('d/m/Y') }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">{{ __('site.thankyou.checkout') }}</span><span class="font-medium">{{ $booking->check_out->format('d/m/Y') }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">{{ __('site.thankyou.guests') }}</span><span class="font-medium">{{ $booking->guests }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">{{ __('site.thankyou.email') }}</span><span class="font-medium">{{ $booking->guest_email }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">{{ __('site.thankyou.status') }}</span><span class="font-medium text-yellow-600">{{ __('site.thankyou.status_pending') }}</span></div>

            @if ($booking->activities->count())
                <div class="pt-2 border-t border-gray-200">
                    <span class="text-gray-500">{{ __('site.thankyou.activities') }}</span>
                    <span class="font-medium">{{ $booking->activities->map->display_name->join(', ') }}</span>
                </div>
            @endif

            @if ($booking->services->count())
                <div>
                    <span class="text-gray-500">{{ __('site.thankyou.services') }}</span>
                    <span class="font-medium">{{ $booking->services->map->display_name->join(', ') }}</span>
                </div>
            @endif
        </div>

        <div class="text-left mt-6 pt-6 border-t border-gray-200">
            <h2 class="font-semibold mb-3">{{ __('site.thankyou.payment_heading') }}</h2>

            @if (config('services.bank.account_number'))
                <div class="bg-emerald-50 border border-emerald-100 rounded-md p-4 text-sm mb-4">
                    <p>{{ __('site.thankyou.bank') }}: <strong>{{ config('services.bank.name') }}</strong></p>
                    <p>{{ __('site.thankyou.account_number') }}: <strong>{{ config('services.bank.account_number') }}</strong></p>
                    <p>{{ __('site.thankyou.account_name') }}: <strong>{{ config('services.bank.account_name') }}</strong></p>
                    @if (config('services.bank.promptpay'))
                        <p>{{ __('site.thankyou.promptpay') }}: <strong>{{ config('services.bank.promptpay') }}</strong></p>
                    @endif
                </div>
            @else
                <p class="text-sm text-gray-400 mb-4">{{ __('site.thankyou.no_bank_info') }}</p>
            @endif

            @if ($booking->payment_slip)
                <div class="mb-3">
                    <p class="text-sm text-emerald-700 mb-2">{{ __('site.thankyou.slip_uploaded_at', ['date' => $booking->payment_uploaded_at->format('d/m/Y H:i')]) }}</p>
                    <img src="{{ $booking->payment_slip_url }}" class="w-40 rounded-md border">
                </div>
                <p class="text-sm text-gray-500 mb-2">{{ __('site.thankyou.slip_replace_note') }}</p>
            @endif

            @error('slip')
                <div class="mb-3 text-sm text-red-600">{{ $message }}</div>
            @enderror

            <form method="POST" action="{{ route('booking.slip', $booking) }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-2">
                @csrf
                <input type="file" name="slip" accept="image/*" required class="flex-1 text-sm border border-gray-300 rounded-md">
                <button type="submit" class="bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-md hover:bg-emerald-800 whitespace-nowrap">
                    {{ $booking->payment_slip ? __('site.thankyou.upload_new') : __('site.thankyou.upload_slip') }}
                </button>
            </form>

            <p class="text-xs text-gray-400 mt-3">
                {{ __('site.thankyou.slip_later_note', ['url' => url()->current()]) }}
            </p>
        </div>

        <a href="{{ route('home') }}" class="inline-block mt-6 text-emerald-700 hover:underline text-sm">{{ __('site.thankyou.back_home') }}</a>
    </div>
</x-layouts.public>
