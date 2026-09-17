<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Booking;
use App\Models\Expense;
use App\Models\Plan;
use App\Models\Story;

class DashboardController extends Controller
{
    public function index()
    {
        $pendingPayments = Booking::with('unit.accommodationType')
            ->where('status', 'pending')
            ->whereNull('payment_slip')
            ->orderBy('check_in')
            ->limit(8)
            ->get();

        $unpublishedStories = Story::where('is_published', false)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $expiringStories = Story::where('is_published', true)
            ->whereNotNull('ends_on')
            ->whereBetween('ends_on', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->orderBy('ends_on')
            ->limit(5)
            ->get();

        $expiringActivities = Activity::where('is_active', true)
            ->whereNotNull('ends_on')
            ->whereBetween('ends_on', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->orderBy('ends_on')
            ->limit(5)
            ->get();

        $today = now()->toDateString();
        $weekAhead = now()->addDays(7)->toDateString();

        $upcomingPlans = Plan::with('creator')
            ->where('status', 'pending')
            ->where(function ($query) use ($today, $weekAhead) {
                $query->where('remind_from', '<=', $today)
                    ->orWhere(function ($query) use ($today, $weekAhead) {
                        $query->whereNull('remind_from')
                            ->whereNotNull('due_date')
                            ->where('due_date', '<=', $weekAhead);
                    });
            })
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->limit(8)
            ->get();

        $recurringExpensesDue = Expense::where('type', 'expense')
            ->where('is_recurring', true)
            ->whereNotNull('recurrence_months')
            ->get()
            ->filter(fn (Expense $expense) => $expense->isRecurringDueSoon())
            ->sortBy(fn (Expense $expense) => $expense->nextDueDate())
            ->values();

        $charts = $this->buildChartData();

        return view('dashboard', compact(
            'pendingPayments',
            'unpublishedStories',
            'expiringStories',
            'expiringActivities',
            'upcomingPlans',
            'recurringExpensesDue',
            'charts'
        ));
    }

    private function buildChartData(): array
    {
        $months = collect(range(11, 0))->map(fn ($i) => now()->subMonths($i)->startOfMonth());
        $rangeStart = $months->first()->copy()->startOfMonth();
        $rangeEnd = now()->endOfMonth();

        $bookings = Booking::with(['unit.accommodationType', 'activities', 'services'])
            ->where('status', '!=', 'cancelled')
            ->whereBetween('check_in', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->get()
            ->map(function (Booking $booking) {
                $nights = $booking->check_in->diffInDays($booking->check_out);
                $roomAmount = $nights * (float) $booking->unit->accommodationType->base_price;
                $activitiesAmount = $booking->activities->sum(fn ($a) => $a->is_free ? 0 : (float) $a->price);
                $servicesAmount = $booking->services->sum(fn ($s) => (float) $s->price * (int) $s->pivot->quantity);

                $booking->computed_amount = $roomAmount + $activitiesAmount + $servicesAmount;
                $booking->month_key = $booking->check_in->format('Y-m');
                $booking->is_paid = ! is_null($booking->payment_slip);

                return $booking;
            });

        $expenseEntries = Expense::whereBetween('expense_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])->get();

        $monthLabels = [];
        $incomeSeries = [];
        $expenseSeries = [];
        $occupancySeries = [];
        $incomeByCategory = [];

        foreach ($months as $month) {
            $key = $month->format('Y-m');
            $monthLabels[] = $month->translatedFormat('M Y');

            $bookingIncome = $bookings->where('month_key', $key)->sum('computed_amount');
            $manualIncome = $expenseEntries->where('type', 'income')
                ->filter(fn (Expense $e) => $e->expense_date->format('Y-m') === $key)
                ->sum('total_amount');

            $incomeSeries[] = round($bookingIncome + $manualIncome, 2);

            $expenseSeries[] = round(
                $expenseEntries->where('type', 'expense')
                    ->filter(fn (Expense $e) => $e->expense_date->format('Y-m') === $key)
                    ->sum('total_amount'),
                2
            );

            $occupancySeries[] = $bookings->where('month_key', $key)->where('is_paid', true)->count();

            $incomeByCategory['booking'] = ($incomeByCategory['booking'] ?? 0) + $bookingIncome;
        }

        foreach ($expenseEntries->where('type', 'income') as $entry) {
            $category = $entry->income_category ?: 'other';
            $incomeByCategory[$category] = ($incomeByCategory[$category] ?? 0) + (float) $entry->total_amount;
        }

        $incomeByCategoryLabels = [];
        $incomeByCategoryValues = [];
        foreach ($incomeByCategory as $key => $value) {
            if ($value <= 0) {
                continue;
            }
            $incomeByCategoryLabels[] = Expense::INCOME_CATEGORIES[$key] ?? $key;
            $incomeByCategoryValues[] = round($value, 2);
        }

        $repeatCustomers = Booking::whereNotNull('guest_phone')
            ->where('guest_phone', '!=', '')
            ->where('status', '!=', 'cancelled')
            ->selectRaw('guest_phone, MAX(guest_name) as guest_name, COUNT(*) as visits, MAX(check_in) as last_check_in')
            ->groupBy('guest_phone')
            ->havingRaw('COUNT(*) > 1')
            ->orderByDesc('visits')
            ->limit(10)
            ->get();

        return [
            'monthLabels' => $monthLabels,
            'incomeSeries' => $incomeSeries,
            'expenseSeries' => $expenseSeries,
            'occupancySeries' => $occupancySeries,
            'incomeByCategoryLabels' => $incomeByCategoryLabels,
            'incomeByCategoryValues' => $incomeByCategoryValues,
            'repeatCustomers' => $repeatCustomers,
        ];
    }
}
