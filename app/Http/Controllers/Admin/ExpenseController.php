<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'expense') === 'income' ? 'income' : 'expense';

        $query = Expense::with('creator')->where('type', $type)->orderByDesc('expense_date');

        if ($request->filled('month')) {
            $query->whereRaw("DATE_FORMAT(expense_date, '%Y-%m') = ?", [$request->string('month')]);
        }

        $expenses = $query->paginate(20)->withQueryString();
        $total = (clone $query)->sum('total_amount');

        return view('admin.expenses.index', compact('expenses', 'total', 'type'));
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'expense') === 'income' ? 'income' : 'expense';
        $categoryOptions = ExpenseCategory::allOptions($type);
        $itemSuggestions = Expense::itemSuggestions($type);

        return view('admin.expenses.create', compact('type', 'categoryOptions', 'itemSuggestions'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['created_by'] = $request->user()->id;

        $expense = Expense::create($data);

        if ($request->hasFile('receipt_file')) {
            $this->storeReceipt($expense, $request->file('receipt_file'));
        }

        $message = $expense->type === 'income' ? 'บันทึกรายรับเรียบร้อยแล้ว' : 'บันทึกรายจ่ายเรียบร้อยแล้ว';

        return redirect()->route('admin.expenses.index', ['type' => $expense->type])->with('status', $message);
    }

    public function edit(Expense $expense)
    {
        $categoryOptions = ExpenseCategory::allOptions($expense->type);
        $itemSuggestions = Expense::itemSuggestions($expense->type);

        return view('admin.expenses.edit', compact('expense', 'categoryOptions', 'itemSuggestions'));
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $this->validateData($request);

        if ($request->boolean('remove_receipt') && $expense->receipt_file) {
            Storage::disk('public')->delete($expense->receipt_file);
            $data['receipt_file'] = null;
        }

        $expense->update($data);

        if ($request->hasFile('receipt_file')) {
            $this->storeReceipt($expense, $request->file('receipt_file'));
        }

        $message = $expense->type === 'income' ? 'บันทึกการแก้ไขเรียบร้อยแล้ว' : 'บันทึกการแก้ไขเรียบร้อยแล้ว';

        return redirect()->route('admin.expenses.index', ['type' => $expense->type])->with('status', $message);
    }

    public function destroy(Expense $expense)
    {
        if ($expense->receipt_file) {
            Storage::disk('public')->delete($expense->receipt_file);
        }

        $type = $expense->type;
        $expense->delete();

        return redirect()->route('admin.expenses.index', ['type' => $type])->with('status', 'ลบรายการเรียบร้อยแล้ว');
    }

    private function validateData(Request $request): array
    {
        $type = $request->input('type') === 'income' ? 'income' : 'expense';

        $data = $request->validate([
            'type' => 'required|in:income,expense',
            'category_key' => ['nullable', Rule::in(array_keys(ExpenseCategory::allOptions($type)))],
            'expense_date' => 'required|date',
            'item' => 'required|string|max:255',
            'is_recurring' => 'nullable|boolean',
            'recurrence_months' => ['nullable', Rule::in(array_keys(Expense::RECURRENCE_OPTIONS))],
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:8192',
        ]);

        $data['total_amount'] = $data['quantity'] * $data['unit_price'];
        $data['is_recurring'] = $request->boolean('is_recurring');
        $data['category_key'] = $data['category_key'] ?? null;
        $data['recurrence_months'] = $data['is_recurring'] ? ($data['recurrence_months'] ?? null) : null;

        unset($data['receipt_file']);

        return $data;
    }

    private function storeReceipt(Expense $expense, $file): void
    {
        if ($expense->receipt_file) {
            Storage::disk('public')->delete($expense->receipt_file);
        }

        $filename = "{$expense->id}.{$file->getClientOriginalExtension()}";
        $path = $file->storeAs('expenses', $filename, 'public');

        $expense->update(['receipt_file' => $path]);
    }
}
