<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = Activity::orderBy('name')->paginate(20);

        return view('admin.activities.index', compact('activities'));
    }

    public function create()
    {
        return view('admin.activities.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        Activity::create($data);

        return redirect()->route('admin.activities.index')->with('status', 'เพิ่มกิจกรรมเรียบร้อยแล้ว');
    }

    public function edit(Activity $activity)
    {
        return view('admin.activities.edit', compact('activity'));
    }

    public function update(Request $request, Activity $activity)
    {
        $data = $this->validateData($request);

        $activity->update($data);

        return redirect()->route('admin.activities.index')->with('status', 'บันทึกการแก้ไขเรียบร้อยแล้ว');
    }

    public function destroy(Activity $activity)
    {
        $activity->delete();

        return redirect()->route('admin.activities.index')->with('status', 'ลบกิจกรรมเรียบร้อยแล้ว');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_free' => 'required|boolean',
            'price' => 'required_if:is_free,0|nullable|numeric|min:0.01',
            'starts_on' => 'nullable|date',
            'ends_on' => 'nullable|date|after_or_equal:starts_on',
        ], [
            'price.required_if' => 'กรุณาระบุราคา เนื่องจากกิจกรรมนี้ไม่ฟรี',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['is_free'] = $request->boolean('is_free');
        $data['price'] = $data['is_free'] ? null : $data['price'];

        return $data;
    }
}
