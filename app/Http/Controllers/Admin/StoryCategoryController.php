<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoryCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoryCategoryController extends Controller
{
    public function index()
    {
        $categories = StoryCategory::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.story-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.story-categories.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['key'] = $this->uniqueKey($data['name']);

        StoryCategory::create($data);

        return redirect()->route('admin.story-categories.index')->with('status', 'เพิ่มกลุ่มโพสต์เรียบร้อยแล้ว');
    }

    public function edit(StoryCategory $storyCategory)
    {
        return view('admin.story-categories.edit', ['category' => $storyCategory]);
    }

    public function update(Request $request, StoryCategory $storyCategory)
    {
        $data = $this->validateData($request);

        $storyCategory->update($data);

        return redirect()->route('admin.story-categories.index')->with('status', 'บันทึกการแก้ไขเรียบร้อยแล้ว');
    }

    public function destroy(StoryCategory $storyCategory)
    {
        $inUse = \App\Models\Story::where('category', $storyCategory->key)->exists();

        if ($inUse) {
            return redirect()->route('admin.story-categories.index')
                ->with('status', 'ลบไม่ได้ เนื่องจากมีโพสต์ที่ใช้กลุ่มนี้อยู่ — ปิดใช้งานแทนได้');
        }

        $storyCategory->delete();

        return redirect()->route('admin.story-categories.index')->with('status', 'ลบกลุ่มโพสต์เรียบร้อยแล้ว');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function uniqueKey(string $name): string
    {
        $base = Str::slug($name, '-', null) ?: 'category';
        $key = $base;
        $suffix = 1;

        while (StoryCategory::where('key', $key)->exists()) {
            $key = $base.'-'.(++$suffix);
        }

        return $key;
    }
}
