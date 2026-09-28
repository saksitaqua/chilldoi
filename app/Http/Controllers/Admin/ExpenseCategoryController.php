<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ExpenseCategoryController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'income') === 'expense' ? 'expense' : 'income';

        $categories = ExpenseCategory::where('type', $type)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.expense-categories.index', compact('categories', 'type'));
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'income') === 'expense' ? 'expense' : 'income';

        return view('admin.expense-categories.create', compact('type'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['key'] = $this->uniqueKey($data['type'], $data['name']);

        ExpenseCategory::create($data);

        return redirect()->route('admin.expense-categories.index', ['type' => $data['type']])->with('status', 'เพิ่มประเภทเรียบร้อยแล้ว');
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        return view('admin.expense-categories.edit', ['category' => $expenseCategory]);
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $data = $this->validateData($request, $expenseCategory);

        $expenseCategory->update($data);

        return redirect()->route('admin.expense-categories.index', ['type' => $expenseCategory->type])->with('status', 'บันทึกการแก้ไขเรียบร้อยแล้ว');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        $type = $expenseCategory->type;

        $inUse = \App\Models\Expense::where('type', $type)->where('category_key', $expenseCategory->key)->exists();

        if ($inUse) {
            return redirect()->route('admin.expense-categories.index', ['type' => $type])
                ->with('status', 'ลบไม่ได้ เนื่องจากมีรายการที่ใช้ประเภทนี้อยู่ — ปิดใช้งานแทนได้');
        }

        $expenseCategory->delete();

        return redirect()->route('admin.expense-categories.index', ['type' => $type])->with('status', 'ลบประเภทเรียบร้อยแล้ว');
    }

    private function validateData(Request $request, ?ExpenseCategory $category = null): array
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['income', 'expense'])],
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function uniqueKey(string $type, string $name): string
    {
        $base = Str::slug($name, '-', null) ?: 'category';
        $key = $base;
        $suffix = 1;

        while (ExpenseCategory::where('type', $type)->where('key', $key)->exists()) {
            $key = $base.'-'.(++$suffix);
        }

        return $key;
    }
}
