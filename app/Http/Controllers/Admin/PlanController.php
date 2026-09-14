<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        $status = in_array($status, ['pending', 'done'], true) ? $status : 'pending';

        $plans = Plan::with('creator')
            ->where('status', $status)
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->paginate(20)
            ->withQueryString();

        return view('admin.plans.index', compact('plans', 'status'));
    }

    public function create()
    {
        return view('admin.plans.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['created_by'] = $request->user()->id;

        Plan::create($data);

        return redirect()->route('admin.plans.index')->with('status', 'เพิ่มแผนงานเรียบร้อยแล้ว');
    }

    public function edit(Plan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $this->validateData($request);

        $plan->update($data);

        return redirect()->route('admin.plans.index')->with('status', 'บันทึกการแก้ไขเรียบร้อยแล้ว');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return redirect()->route('admin.plans.index')->with('status', 'ลบแผนงานเรียบร้อยแล้ว');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'remind_from' => 'nullable|date',
            'due_date' => 'nullable|date',
            'budget' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'status' => ['required', Rule::in(['pending', 'done'])],
        ]);
    }
}
