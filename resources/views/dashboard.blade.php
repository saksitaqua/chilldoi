<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm rounded-lg">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">การเตรียมงาน — ลูกค้าที่จองแต่ยังไม่โอนเงิน</h3>
                    <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full font-medium">{{ $pendingPayments->count() }} รายการ</span>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($pendingPayments as $booking)
                        <a href="{{ route('admin.bookings.edit', $booking) }}" class="flex items-center justify-between px-5 py-3 text-sm hover:bg-gray-50">
                            <div>
                                <p class="font-medium text-gray-800">{{ $booking->guest_name }} <span class="text-gray-400 font-normal">· {{ $booking->unit->name }} ({{ $booking->unit->accommodationType->name }})</span></p>
                                <p class="text-xs text-gray-500 mt-0.5">เช็คอิน {{ $booking->check_in->format('d/m/Y') }} &middot; โทร {{ $booking->guest_phone ?: '-' }}</p>
                            </div>
                            <span class="text-xs bg-yellow-50 text-yellow-700 px-2 py-1 rounded font-medium whitespace-nowrap">รอโอนเงิน</span>
                        </a>
                    @empty
                        <p class="px-5 py-6 text-sm text-gray-400 text-center">ไม่มีรายการที่ต้องติดตามการชำระเงิน</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">แผนงาน/โพสต์/กิจกรรม ที่รอโพสต์ หรือใกล้หมดอายุ</h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach ($unpublishedStories as $story)
                        <a href="{{ route('admin.stories.edit', $story) }}" class="flex items-center justify-between px-5 py-3 text-sm hover:bg-gray-50">
                            <p class="font-medium text-gray-800">{{ $story->title }}</p>
                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded font-medium whitespace-nowrap">รอเผยแพร่</span>
                        </a>
                    @endforeach

                    @foreach ($expiringStories as $story)
                        <a href="{{ route('admin.stories.edit', $story) }}" class="flex items-center justify-between px-5 py-3 text-sm hover:bg-gray-50">
                            <p class="font-medium text-gray-800">{{ $story->title }} <span class="text-gray-400 font-normal">(โพสต์)</span></p>
                            <span class="text-xs bg-red-50 text-red-700 px-2 py-1 rounded font-medium whitespace-nowrap">หมดอายุ {{ $story->ends_on->format('d/m/Y') }}</span>
                        </a>
                    @endforeach

                    @foreach ($expiringActivities as $activity)
                        <a href="{{ route('admin.activities.edit', $activity) }}" class="flex items-center justify-between px-5 py-3 text-sm hover:bg-gray-50">
                            <p class="font-medium text-gray-800">{{ $activity->name }} <span class="text-gray-400 font-normal">(กิจกรรม)</span></p>
                            <span class="text-xs bg-red-50 text-red-700 px-2 py-1 rounded font-medium whitespace-nowrap">หมดอายุ {{ $activity->ends_on->format('d/m/Y') }}</span>
                        </a>
                    @endforeach

                    @if ($unpublishedStories->isEmpty() && $expiringStories->isEmpty() && $expiringActivities->isEmpty())
                        <p class="px-5 py-6 text-sm text-gray-400 text-center">ไม่มีโพสต์หรือกิจกรรมที่ต้องติดตาม</p>
                    @endif
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">แผนงานที่ต้องติดตาม</h3>
                    <a href="{{ route('admin.plans.index') }}" class="text-sm text-emerald-700 hover:underline">ดูทั้งหมด →</a>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse ($upcomingPlans as $plan)
                        <a href="{{ route('admin.plans.edit', $plan) }}" class="flex items-center justify-between px-5 py-3 text-sm hover:bg-gray-50">
                            <div>
                                <p class="font-medium text-gray-800">{{ $plan->title }}</p>
                                @if ($plan->budget)
                                    <p class="text-xs text-gray-500 mt-0.5">งบประมาณ {{ number_format($plan->budget, 2) }} บาท</p>
                                @endif
                            </div>
                            @if ($plan->due_date)
                                <span @class([
                                    'text-xs px-2 py-1 rounded font-medium whitespace-nowrap',
                                    'bg-red-50 text-red-700' => $plan->isOverdue(),
                                    'bg-orange-50 text-orange-700' => ! $plan->isOverdue(),
                                ])>{{ $plan->isOverdue() ? 'เลยกำหนด' : 'กำหนดเสร็จ' }} {{ $plan->due_date->format('d/m/Y') }}</span>
                            @else
                                <span class="text-xs bg-orange-50 text-orange-700 px-2 py-1 rounded font-medium whitespace-nowrap">แจ้งเตือน</span>
                            @endif
                        </a>
                    @empty
                        <p class="px-5 py-6 text-sm text-gray-400 text-center">ไม่มีแผนงานที่ต้องติดตาม</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
