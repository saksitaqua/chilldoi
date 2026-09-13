<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('sort_order')->orderBy('name')->paginate(20);

        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $service = Service::create($data);

        if ($request->hasFile('image')) {
            $this->storeImage($service, $request->file('image'));
        }

        return redirect()->route('admin.services.index')->with('status', 'เพิ่มรายการบริการเรียบร้อยแล้ว');
    }

    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $data = $this->validateData($request);

        if ($request->boolean('remove_image') && $service->image) {
            Storage::disk('public')->delete($service->image);
            $data['image'] = null;
        }

        $service->update($data);

        if ($request->hasFile('image')) {
            $this->storeImage($service, $request->file('image'));
        }

        return redirect()->route('admin.services.index')->with('status', 'บันทึกการแก้ไขเรียบร้อยแล้ว');
    }

    public function destroy(Service $service)
    {
        if ($service->image) {
            Storage::disk('public')->delete($service->image);
        }

        $service->delete();

        return redirect()->route('admin.services.index')->with('status', 'ลบรายการบริการเรียบร้อยแล้ว');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
            'price' => 'required|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        unset($data['image']);

        return $data;
    }

    private function storeImage(Service $service, $file): void
    {
        if ($service->image) {
            Storage::disk('public')->delete($service->image);
        }

        $filename = "{$service->id}.{$file->getClientOriginalExtension()}";
        $path = $file->storeAs('services', $filename, 'public');

        $service->update(['image' => $path]);
    }
}
