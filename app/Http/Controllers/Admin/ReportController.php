<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function summary(Request $request)
    {
        [$monthFrom, $monthTo, $rangeStart, $rangeEnd] = $this->resolveRange($request);

        $data = $this->buildReportData($rangeStart, $rangeEnd);

        return view('admin.reports.summary', array_merge($data, [
            'monthFrom' => $monthFrom,
            'monthTo' => $monthTo,
        ]));
    }

    public function export(Request $request): StreamedResponse
    {
        [$monthFrom, $monthTo, $rangeStart, $rangeEnd] = $this->resolveRange($request);

        $data = $this->buildReportData($rangeStart, $rangeEnd);

        $filename = "rayngan-sarup_{$monthFrom}_ถึง_{$monthTo}.csv";

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');

            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['ส่วนรับเงิน - การจอง']);
            fputcsv($out, ['ผู้จอง', 'ที่พัก', 'เช็คอิน', 'คืน', 'สถานะ', 'ยอดประมาณการ']);
            foreach ($data['bookings'] as $booking) {
                fputcsv($out, [
                    $booking->guest_name,
                    $booking->unit->name,
                    $booking->check_in->format('d/m/Y'),
                    $booking->nights,
                    $booking->status === 'pending' ? 'รอยืนยัน' : 'ยืนยันแล้ว',
                    number_format($booking->computed_amount, 2, '.', ''),
                ]);
            }
            fputcsv($out, ['', '', '', '', 'รวมรับจากการจอง', number_format($data['bookingIncomeTotal'], 2, '.', '')]);
            fputcsv($out, []);

            fputcsv($out, ['ส่วนรับเงิน - รายรับเพิ่มเติม (บันทึกเอง)']);
            fputcsv($out, ['วันที่', 'รายการ', 'ผู้บันทึก', 'จำนวนเงิน']);
            foreach ($data['manualIncomes'] as $income) {
                fputcsv($out, [
                    $income->expense_date->format('d/m/Y'),
                    $income->item,
                    $income->creator->name,
                    number_format($income->total_amount, 2, '.', ''),
                ]);
            }
            fputcsv($out, ['', '', 'รวมรายรับเพิ่มเติม', number_format($data['manualIncomeTotal'], 2, '.', '')]);
            fputcsv($out, []);

            fputcsv($out, ['ส่วนจ่ายเงิน - รายจ่าย']);
            fputcsv($out, ['วันที่', 'รายการ', 'ผู้บันทึก', 'จำนวนเงิน']);
            foreach ($data['expenses'] as $expense) {
                fputcsv($out, [
                    $expense->expense_date->format('d/m/Y'),
                    $expense->item,
                    $expense->creator->name,
                    number_format($expense->total_amount, 2, '.', ''),
                ]);
            }
            fputcsv($out, ['', '', 'รวมรายจ่าย', number_format($data['expenseTotal'], 2, '.', '')]);
            fputcsv($out, []);

            fputcsv($out, ['สรุป']);
            fputcsv($out, ['รับเงินรวม', number_format($data['incomeTotal'], 2, '.', '')]);
            fputcsv($out, ['จ่ายเงินรวม', number_format($data['expenseTotal'], 2, '.', '')]);
            fputcsv($out, ['คงเหลือสุทธิ', number_format($data['incomeTotal'] - $data['expenseTotal'], 2, '.', '')]);

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function resolveRange(Request $request): array
    {
        $currentMonth = now()->format('Y-m');
        $monthFrom = $request->string('month_from')->value() ?: $currentMonth;
        $monthTo = $request->string('month_to')->value() ?: $monthFrom;

        if ($monthTo < $monthFrom) {
            [$monthFrom, $monthTo] = [$monthTo, $monthFrom];
        }

        $rangeStart = Carbon::createFromFormat('Y-m-d', "{$monthFrom}-01")->startOfMonth();
        $rangeEnd = Carbon::createFromFormat('Y-m-d', "{$monthTo}-01")->endOfMonth();

        return [$monthFrom, $monthTo, $rangeStart, $rangeEnd];
    }

    private function buildReportData(Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $bookings = Booking::with(['unit.accommodationType', 'activities', 'services'])
            ->where('status', '!=', 'cancelled')
            ->whereBetween('check_in', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
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
            ->whereBetween('expense_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->orderBy('expense_date')
            ->get();

        $manualIncomeTotal = $manualIncomes->sum('total_amount');
        $incomeTotal = $bookingIncomeTotal + $manualIncomeTotal;

        $expenses = Expense::with('creator')
            ->where('type', 'expense')
            ->whereBetween('expense_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->orderBy('expense_date')
            ->get();

        $expenseTotal = $expenses->sum('total_amount');

        return compact(
            'bookings', 'bookingIncomeTotal', 'manualIncomes', 'manualIncomeTotal',
            'incomeTotal', 'expenses', 'expenseTotal'
        );
    }
}
