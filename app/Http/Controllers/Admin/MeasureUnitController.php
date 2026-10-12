<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MeasureUnit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MeasureUnitController extends Controller
{
    public function index()
    {
        $units = MeasureUnit::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.measure-units.index', compact('units'));
    }

    public function create()
    {
        return view('admin.measure-units.create');
    }

    public function store(Request $request)
    {
        MeasureUnit::create($this->validateData($request));

        return redirect()->route('admin.measure-units.index')->with('status', 'เพิ่มหน่วยนับเรียบร้อยแล้ว');
    }

    public function edit(MeasureUnit $measureUnit)
    {
        return view('admin.measure-units.edit', ['unit' => $measureUnit]);
    }

    public function update(Request $request, MeasureUnit $measureUnit)
    {
        $measureUnit->update($this->validateData($request, $measureUnit));

        return redirect()->route('admin.measure-units.index')->with('status', 'บันทึกการแก้ไขเรียบร้อยแล้ว');
    }

    public function destroy(MeasureUnit $measureUnit)
    {
        $measureUnit->delete();

        return redirect()->route('admin.measure-units.index')->with('status', 'ลบหน่วยนับเรียบร้อยแล้ว (รายการเก่าที่เคยใช้หน่วยนี้ยังแสดงตามเดิม)');
    }

    private function validateData(Request $request, ?MeasureUnit $unit = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('measure_units', 'name')->ignore($unit?->id)],
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
