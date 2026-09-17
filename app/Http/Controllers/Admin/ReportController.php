<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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

        $spreadsheet = new Spreadsheet();

        $this->writeSummarySheet($spreadsheet->getActiveSheet(), $data, $monthFrom, $monthTo);
        $this->writeRawDataSheet($spreadsheet->createSheet(), $data);

        $filename = "rayngan-sarup_{$monthFrom}_ถึง_{$monthTo}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function writeSummarySheet($sheet, array $data, string $monthFrom, string $monthTo): void
    {
        $sheet->setTitle('สรุป');

        $row = 1;
        $sheet->setCellValue("A{$row}", "รายงานสรุป ({$monthFrom} ถึง {$monthTo})");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
        $row += 2;

        $row = $this->writeSectionTable(
            $sheet, $row, 'ส่วนรับเงิน — การจอง',
            ['ผู้จอง', 'ที่พัก', 'เช็คอิน', 'คืน', 'สถานะ', 'ยอดประมาณการ'],
            $data['bookings']->map(fn ($b) => [
                $b->guest_name,
                $b->unit->name,
                $b->check_in->format('d/m/Y'),
                $b->nights,
                $b->status === 'pending' ? 'รอยืนยัน' : 'ยืนยันแล้ว',
                (float) $b->computed_amount,
            ])->all(),
            'รวมรับจากการจอง', (float) $data['bookingIncomeTotal'], 5
        );

        $row = $this->writeSectionTable(
            $sheet, $row, 'ส่วนรับเงิน — รายรับเพิ่มเติม (บันทึกเอง)',
            ['วันที่', 'รายการ', 'ประเภทรายรับ', 'ผู้บันทึก', 'จำนวนเงิน'],
            $data['manualIncomes']->map(fn ($i) => [
                $i->expense_date->format('d/m/Y'),
                $i->item,
                $i->income_category_label ?? '-',
                $i->creator->name,
                (float) $i->total_amount,
            ])->all(),
            'รวมรายรับเพิ่มเติม', (float) $data['manualIncomeTotal'], 3
        );

        $row = $this->writeSectionTable(
            $sheet, $row, 'ส่วนจ่ายเงิน — รายจ่าย',
            ['วันที่', 'รายการ', 'ผู้บันทึก', 'จำนวนเงิน'],
            $data['expenses']->map(fn ($e) => [
                $e->expense_date->format('d/m/Y'),
                $e->item,
                $e->creator->name,
                (float) $e->total_amount,
            ])->all(),
            'รวมรายจ่าย', (float) $data['expenseTotal'], 2
        );

        $row++;
        $sheet->setCellValue("A{$row}", 'สรุป');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;
        $sheet->setCellValue("A{$row}", 'รับเงินรวม');
        $sheet->setCellValue("B{$row}", (float) $data['incomeTotal']);
        $row++;
        $sheet->setCellValue("A{$row}", 'จ่ายเงินรวม');
        $sheet->setCellValue("B{$row}", (float) $data['expenseTotal']);
        $row++;
        $sheet->setCellValue("A{$row}", 'คงเหลือสุทธิ');
        $sheet->setCellValue("B{$row}", (float) ($data['incomeTotal'] - $data['expenseTotal']));
        $sheet->getStyle("B" . ($row - 2) . ":B{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $sheet->getStyle("A" . ($row - 2) . ":A{$row}")->getFont()->setBold(true);

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function writeSectionTable($sheet, int $row, string $title, array $headers, array $rows, string $totalLabel, float $total, int $totalLabelCol): int
    {
        $sheet->setCellValue("A{$row}", $title);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue("{$col}{$row}", $header);
            $col++;
        }
        $sheet->getStyle("A{$row}:" . chr(ord('A') + count($headers) - 1) . "{$row}")
            ->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:" . chr(ord('A') + count($headers) - 1) . "{$row}")
            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F3F4F6');
        $row++;

        $amountCol = chr(ord('A') + count($headers) - 1);
        $firstDataRow = $row;

        foreach ($rows as $line) {
            $col = 'A';
            foreach ($line as $value) {
                $sheet->setCellValue("{$col}{$row}", $value);
                $col++;
            }
            $row++;
        }

        if ($rows === []) {
            $sheet->setCellValue("A{$row}", 'ไม่มีรายการ');
            $row++;
        } else {
            $sheet->getStyle("{$amountCol}{$firstDataRow}:{$amountCol}" . ($row - 1))
                ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        }

        $totalLabelColLetter = chr(ord('A') + $totalLabelCol - 1);
        $sheet->setCellValue("{$totalLabelColLetter}{$row}", $totalLabel);
        $sheet->setCellValue("{$amountCol}{$row}", $total);
        $sheet->getStyle("{$amountCol}{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $sheet->getStyle("{$totalLabelColLetter}{$row}:{$amountCol}{$row}")->getFont()->setBold(true);
        $row += 2;

        return $row;
    }

    private function writeRawDataSheet($sheet, array $data): void
    {
        $sheet->setTitle('ข้อมูลดิบ (Pivot)');

        $headers = ['วันที่', 'ประเภท', 'รายการ', 'จำนวน', 'ราคาต่อหน่วย', 'จำนวนเงิน', 'ผู้บันทึก/ผู้จอง'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F3F4F6');

        $row = 2;

        foreach ($data['bookings'] as $booking) {
            $unitPrice = $booking->nights > 0 ? (float) $booking->unit->accommodationType->base_price : 0;
            $sheet->fromArray([
                $booking->check_in->format('d/m/Y'),
                'รายรับ - การจอง',
                $booking->unit->name . ' (' . $booking->guest_name . ')',
                $booking->nights,
                $unitPrice,
                (float) $booking->computed_amount,
                $booking->guest_name,
            ], null, "A{$row}");
            $row++;
        }

        foreach ($data['manualIncomes'] as $income) {
            $sheet->fromArray([
                $income->expense_date->format('d/m/Y'),
                'รายรับ - บันทึกเอง',
                $income->item,
                (float) $income->quantity,
                (float) $income->unit_price,
                (float) $income->total_amount,
                $income->creator->name,
            ], null, "A{$row}");
            $row++;
        }

        foreach ($data['expenses'] as $expense) {
            $sheet->fromArray([
                $expense->expense_date->format('d/m/Y'),
                'รายจ่าย',
                $expense->item,
                (float) $expense->quantity,
                (float) $expense->unit_price,
                (float) $expense->total_amount,
                $expense->creator->name,
            ], null, "A{$row}");
            $row++;
        }

        $lastRow = $row - 1;

        if ($lastRow >= 2) {
            $sheet->getStyle("E2:F{$lastRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        }

        $sheet->setAutoFilter('A1:G' . max($lastRow, 1));
        $sheet->freezePane('A2');

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
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
