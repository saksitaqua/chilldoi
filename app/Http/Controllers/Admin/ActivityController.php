<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        $activity = Activity::create($data);

        if ($request->hasFile('image')) {
            $this->storeImage($activity, $request->file('image'));
        }

        if ($request->hasFile('video')) {
            $this->storeVideo($activity, $request->file('video'));
        }

        return redirect()->route('admin.activities.index')->with('status', 'เพิ่มกิจกรรมเรียบร้อยแล้ว');
    }

    public function edit(Activity $activity)
    {
        return view('admin.activities.edit', compact('activity'));
    }

    public function update(Request $request, Activity $activity)
    {
        $data = $this->validateData($request);

        if ($request->boolean('remove_image') && $activity->image) {
            Storage::disk('public')->delete($activity->image);
            $data['image'] = null;
        }

        if ($request->boolean('remove_video') && $activity->video) {
            Storage::disk('public')->delete($activity->video);
            $data['video'] = null;
        }

        $activity->update($data);

        if ($request->hasFile('image')) {
            $this->storeImage($activity, $request->file('image'));
        }

        if ($request->hasFile('video')) {
            $this->storeVideo($activity, $request->file('video'));
        }

        return redirect()->route('admin.activities.index')->with('status', 'บันทึกการแก้ไขเรียบร้อยแล้ว');
    }

    public function destroy(Activity $activity)
    {
        if ($activity->image) {
            Storage::disk('public')->delete($activity->image);
        }

        if ($activity->video) {
            Storage::disk('public')->delete($activity->video);
        }

        $activity->delete();

        return redirect()->route('admin.activities.index')->with('status', 'ลบกิจกรรมเรียบร้อยแล้ว');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
            'video' => 'nullable|mimes:mp4,mov,webm,avi|max:51200',
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

        unset($data['image'], $data['video']);

        return $data;
    }

    private function storeImage(Activity $activity, $file): void
    {
        if ($activity->image) {
            Storage::disk('public')->delete($activity->image);
        }

        $filename = "{$activity->id}.{$file->getClientOriginalExtension()}";
        $path = $file->storeAs('activities', $filename, 'public');

        $activity->update(['image' => $path]);
    }

    private function storeVideo(Activity $activity, $file): void
    {
        if ($activity->video) {
            Storage::disk('public')->delete($activity->video);
        }

        $filename = "{$activity->id}.{$file->getClientOriginalExtension()}";
        $path = $file->storeAs('activities/videos', $filename, 'public');

        $activity->update(['video' => $path]);
    }
}
