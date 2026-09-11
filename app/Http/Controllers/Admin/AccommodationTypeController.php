<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccommodationType;
use App\Support\Slug;
use Illuminate\Http\Request;

class AccommodationTypeController extends Controller
{
    public function index()
    {
        $types = AccommodationType::withCount('units')->orderBy('name')->paginate(20);

        return view('admin.types.index', compact('types'));
    }

    public function create()
    {
        return view('admin.types.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = Slug::unique($data['name'], AccommodationType::class);

        AccommodationType::create($data);

        return redirect()->route('admin.types.index')->with('status', 'เพิ่มประเภทที่พักเรียบร้อยแล้ว');
    }

    public function edit(AccommodationType $type)
    {
        return view('admin.types.edit', compact('type'));
    }

    public function update(Request $request, AccommodationType $type)
    {
        $data = $this->validateData($request);
        $data['slug'] = Slug::unique($data['name'], AccommodationType::class, $type->id);

        $type->update($data);

        return redirect()->route('admin.types.index')->with('status', 'บันทึกการแก้ไขเรียบร้อยแล้ว');
    }

    public function destroy(AccommodationType $type)
    {
        $type->delete();

        return redirect()->route('admin.types.index')->with('status', 'ลบประเภทที่พักเรียบร้อยแล้ว');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_guests' => 'required|integer|min:1',
            'base_price' => 'required|numeric|min:0',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
