<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccommodationType;
use App\Models\Unit;
use App\Models\UnitImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::with('accommodationType')->orderBy('name')->paginate(20);

        return view('admin.units.index', compact('units'));
    }

    public function create()
    {
        $types = AccommodationType::orderBy('name')->get();

        return view('admin.units.create', compact('types'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $unit = Unit::create($data);

        $this->storeImages($unit, $request->file('images', []));

        return redirect()->route('admin.units.index')->with('status', 'เพิ่มที่พัก/จุดกางเต็นท์เรียบร้อยแล้ว');
    }

    public function edit(Unit $unit)
    {
        $types = AccommodationType::orderBy('name')->get();
        $unit->load('images');

        return view('admin.units.edit', compact('unit', 'types'));
    }

    public function update(Request $request, Unit $unit)
    {
        $data = $this->validateData($request);

        $unit->update($data);

        $removeIds = $request->input('remove_images', []);

        if (! empty($removeIds)) {
            UnitImage::whereIn('id', $removeIds)->where('unit_id', $unit->id)->get()->each(function (UnitImage $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            });
        }

        $visibleIds = $request->input('visible_images', []);
        $unit->images()->update(['is_visible' => false]);
        if (! empty($visibleIds)) {
            UnitImage::whereIn('id', $visibleIds)->where('unit_id', $unit->id)->update(['is_visible' => true]);
        }

        $this->storeImages($unit, $request->file('images', []));

        return redirect()->route('admin.units.index')->with('status', 'บันทึกการแก้ไขเรียบร้อยแล้ว');
    }

    public function destroy(Unit $unit)
    {
        foreach ($unit->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $unit->delete();

        return redirect()->route('admin.units.index')->with('status', 'ลบเรียบร้อยแล้ว');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'accommodation_type_id' => 'required|exists:accommodation_types,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'notes' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|max:5120',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'exists:unit_images,id',
            'visible_images' => 'nullable|array',
            'visible_images.*' => 'exists:unit_images,id',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        unset($data['images'], $data['remove_images'], $data['visible_images']);

        return $data;
    }

    private function storeImages(Unit $unit, array $files): void
    {
        $sortOrder = $unit->images()->max('sort_order') + 1;

        foreach ($files as $file) {
            $filename = "{$unit->id}_{$sortOrder}.{$file->getClientOriginalExtension()}";
            $path = $file->storeAs('units', $filename, 'public');

            UnitImage::create([
                'unit_id' => $unit->id,
                'path' => $path,
                'sort_order' => $sortOrder++,
                'is_visible' => true,
            ]);
        }
    }
}
