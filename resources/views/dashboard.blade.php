<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8" x-data="{ tab: 'notify' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex gap-2 mb-6 border-b border-gray-200">
                <button @click="tab = 'notify'"
                        :class="tab === 'notify' ? 'border-emerald-700 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2 text-sm font-medium border-b-2 -mb-px">การแจ้งเตือน</button>
                <button @click="tab = 'charts'; $nextTick(() => window.renderDashboardCharts && window.renderDashboardCharts())"
                        :class="tab === 'charts' ? 'border-emerald-700 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2 text-sm font-medium border-b-2 -mb-px">กราฟสรุปข้อมูล</button>
            </div>

            <div x-show="tab === 'notify'" class="space-y-6">

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

                <div class="bg-white shadow-sm rounded-lg">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-800">รายจ่ายที่ต้องซื้อซ้ำ — ใกล้ถึงกำหนด</h3>
                        <a href="{{ route('admin.expenses.index', ['type' => 'expense']) }}" class="text-sm text-emerald-700 hover:underline">ดูทั้งหมด →</a>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($recurringExpensesDue as $expense)
                            <a href="{{ route('admin.expenses.edit', $expense) }}" class="flex items-center justify-between px-5 py-3 text-sm hover:bg-gray-50">
                                <div>
                                    <p class="font-medium text-gray-800">{{ $expense->item }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">ซื้อล่าสุด {{ $expense->expense_date->format('d/m/Y') }} &middot; {{ \App\Models\Expense::RECURRENCE_OPTIONS[$expense->recurrence_months] }}</p>
                                </div>
                                <span @class([
                                    'text-xs px-2 py-1 rounded font-medium whitespace-nowrap',
                                    'bg-red-50 text-red-700' => $expense->isRecurringOverdue(),
                                    'bg-orange-50 text-orange-700' => ! $expense->isRecurringOverdue(),
                                ])>{{ $expense->isRecurringOverdue() ? 'เลยกำหนดซื้อ' : 'ถึงกำหนดซื้อ' }} {{ $expense->nextDueDate()->format('d/m/Y') }}</span>
                            </a>
                        @empty
                            <p class="px-5 py-6 text-sm text-gray-400 text-center">ไม่มีรายจ่ายที่ต้องซื้อซ้ำใกล้ถึงกำหนด</p>
                        @endforelse
                    </div>
                </div>

            </div>

            <div x-show="tab === 'charts'" class="space-y-6" x-cloak>

                <div class="bg-white shadow-sm rounded-lg p-5">
                    <h3 class="font-semibold text-gray-800 mb-4">รายรับ เทียบ รายจ่าย รายเดือน (ย้อนหลัง 12 เดือน)</h3>
                    <canvas id="financeChart" height="90"></canvas>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white shadow-sm rounded-lg p-5">
                        <h3 class="font-semibold text-gray-800 mb-4">รายรับแยกตามประเภท (ย้อนหลัง 12 เดือน)</h3>
                        @if (count($charts['incomeByCategoryLabels']))
                            <canvas id="incomeCategoryChart" height="220"></canvas>
                        @else
                            <p class="text-sm text-gray-400 text-center py-10">ยังไม่มีข้อมูลรายรับ</p>
                        @endif
                    </div>

                    <div class="bg-white shadow-sm rounded-lg p-5">
                        <h3 class="font-semibold text-gray-800 mb-4">ความหนาแน่นการเข้าพัก (คนที่จ่ายเงินแล้ว รายเดือน)</h3>
                        <canvas id="occupancyChart" height="220"></canvas>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-800">ลูกค้าที่จองซ้ำ (ดูจากเบอร์โทร)</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($charts['repeatCustomers'] as $customer)
                            <div class="flex items-center justify-between px-5 py-3 text-sm">
                                <div>
                                    <p class="font-medium text-gray-800">{{ $customer->guest_name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">โทร {{ $customer->guest_phone }} &middot; จองล่าสุด {{ \Carbon\Carbon::parse($customer->last_check_in)->format('d/m/Y') }}</p>
                                </div>
                                <span class="text-xs bg-emerald-50 text-emerald-700 px-2 py-1 rounded font-medium whitespace-nowrap">จองแล้ว {{ $customer->visits }} ครั้ง</span>
                            </div>
                        @empty
                            <p class="px-5 py-6 text-sm text-gray-400 text-center">ยังไม่มีลูกค้าที่จองซ้ำ</p>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>
    </div>

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.5.1/chart.umd.min.js" integrity="sha512-WoViKhKD4qI2WruSZqv9+kvM4WfFhUMQCLN4QlDTt5aU56fLQy2gYoxWIqlEnXqJy/+Ac5q/hk1oWfqnMDhwMA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script>
            (function () {
                const monthLabels = @json($charts['monthLabels']);
                const incomeSeries = @json($charts['incomeSeries']);
                const expenseSeries = @json($charts['expenseSeries']);
                const occupancySeries = @json($charts['occupancySeries']);
                const incomeCategoryLabels = @json($charts['incomeByCategoryLabels']);
                const incomeCategoryValues = @json($charts['incomeByCategoryValues']);

                let financeChart, categoryChart, occupancyChart;

                function renderCharts() {
                    const financeEl = document.getElementById('financeChart');
                    if (financeEl && !financeChart) {
                        financeChart = new Chart(financeEl, {
                            type: 'bar',
                            data: {
                                labels: monthLabels,
                                datasets: [
                                    { label: 'รายรับ', data: incomeSeries, backgroundColor: '#2f6b4f' },
                                    { label: 'รายจ่าย', data: expenseSeries, backgroundColor: '#e2914a' },
                                ],
                            },
                            options: { responsive: true, scales: { y: { beginAtZero: true } } },
                        });
                    }

                    const categoryEl = document.getElementById('incomeCategoryChart');
                    if (categoryEl && !categoryChart && incomeCategoryLabels.length) {
                        categoryChart = new Chart(categoryEl, {
                            type: 'doughnut',
                            data: {
                                labels: incomeCategoryLabels,
                                datasets: [{
                                    data: incomeCategoryValues,
                                    backgroundColor: ['#2f6b4f', '#e2914a', '#5b8ab0', '#a35b8a', '#9aa393', '#c97430'],
                                }],
                            },
                            options: { responsive: true },
                        });
                    }

                    const occupancyEl = document.getElementById('occupancyChart');
                    if (occupancyEl && !occupancyChart) {
                        occupancyChart = new Chart(occupancyEl, {
                            type: 'line',
                            data: {
                                labels: monthLabels,
                                datasets: [{
                                    label: 'จำนวนการจองที่ชำระแล้ว',
                                    data: occupancySeries,
                                    borderColor: '#2f6b4f',
                                    backgroundColor: 'rgba(47,107,79,.15)',
                                    fill: true,
                                    tension: 0.3,
                                }],
                            },
                            options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
                        });
                    }
                }

                window.renderDashboardCharts = renderCharts;
            })();
        </script>
    @endpush
</x-app-layout>
