<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Expense;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function summary(Request $request)
    {
        $month = $request->string('month')->value() ?: now()->format('Y-m');

        $bookings = Booking::with(['unit.accommodationType', 'activities', 'services'])
            ->where('status', '!=', 'cancelled')
            ->whereRaw("DATE_FORMAT(check_in, '%Y-%m') = ?", [$month])
            ->orderBy('check_in')
            ->get()
            ->map(function (Booking $booking) {
                $nights = $booking->check_in->diffInDays($booking->check_out);
                $roomAmount = $nights * (float) $booking->unit->accommodationType->base_price;

                $activitiesAmount = $booking->activities->sum(fn ($a) => $a->is_free ? 0 : (float) $a->price);
                $servicesAmount = $booking->services->sum(fn ($s) => (float) $s->price * (int) $s->pivot->quantity);

                $booking->computed_amount = $roomAmount + $activitiesAmount + $servicesAmount;
                $booking->nights = $nights;

                return $booking;
            });

        $bookingIncomeTotal = $bookings->sum('computed_amount');

        $manualIncomes = Expense::with('creator')
            ->where('type', 'income')
            ->whereRaw("DATE_FORMAT(expense_date, '%Y-%m') = ?", [$month])
            ->orderBy('expense_date')
            ->get();

        $manualIncomeTotal = $manualIncomes->sum('total_amount');
        $incomeTotal = $bookingIncomeTotal + $manualIncomeTotal;

        $expenses = Expense::with('creator')
            ->where('type', 'expense')
            ->whereRaw("DATE_FORMAT(expense_date, '%Y-%m') = ?", [$month])
            ->orderBy('expense_date')
            ->get();

        $expenseTotal = $expenses->sum('total_amount');

        return view('admin.reports.summary', compact(
            'bookings', 'bookingIncomeTotal', 'manualIncomes', 'manualIncomeTotal',
            'incomeTotal', 'expenses', 'expenseTotal', 'month'
        ));
    }
}
