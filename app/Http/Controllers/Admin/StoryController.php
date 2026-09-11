<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Story;
use App\Models\StoryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StoryController extends Controller
{
    public function index()
    {
        $stories = Story::withCount('images')->orderByDesc('created_at')->paginate(20);

        return view('admin.stories.index', compact('stories'));
    }

    public function create()
    {
        return view('admin.stories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => ['required', Rule::in(array_keys(Story::CATEGORIES))],
            'description' => 'nullable|string',
            'starts_on' => 'nullable|date',
            'ends_on' => 'nullable|date|after_or_equal:starts_on',
            'images' => 'nullable|array',
            'images.*' => 'image|max:5120',
        ]);

        $story = Story::create([
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'category' => $data['category'],
            'description' => $data['description'] ?? null,
            'is_published' => $request->boolean('is_published', true),
            'starts_on' => $data['starts_on'] ?? null,
            'ends_on' => $data['ends_on'] ?? null,
        ]);

        $this->storeImages($story, $request->file('images', []));

        return redirect()->route('admin.stories.index')->with('status', 'โพสต์เรื่องราวเรียบร้อยแล้ว');
    }

    public function edit(Story $story)
    {
        $story->load('images');

        return view('admin.stories.edit', compact('story'));
    }

    public function update(Request $request, Story $story)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => ['required', Rule::in(array_keys(Story::CATEGORIES))],
            'description' => 'nullable|string',
            'starts_on' => 'nullable|date',
            'ends_on' => 'nullable|date|after_or_equal:starts_on',
            'images' => 'nullable|array',
            'images.*' => 'image|max:5120',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'exists:story_images,id',
        ]);

        $story->update([
            'title' => $data['title'],
            'category' => $data['category'],
            'description' => $data['description'] ?? null,
            'is_published' => $request->boolean('is_published', true),
            'starts_on' => $data['starts_on'] ?? null,
            'ends_on' => $data['ends_on'] ?? null,
        ]);

        if (! empty($data['remove_images'])) {
            StoryImage::whereIn('id', $data['remove_images'])->where('story_id', $story->id)->get()->each(function (StoryImage $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            });
        }

        $this->storeImages($story, $request->file('images', []));

        return redirect()->route('admin.stories.index')->with('status', 'บันทึกการแก้ไขเรียบร้อยแล้ว');
    }

    public function destroy(Story $story)
    {
        foreach ($story->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $story->delete();

        return redirect()->route('admin.stories.index')->with('status', 'ลบเรื่องราวเรียบร้อยแล้ว');
    }

    private function storeImages(Story $story, array $files): void
    {
        $sortOrder = $story->images()->max('sort_order') + 1;

        foreach ($files as $file) {
            $filename = "{$story->id}_{$sortOrder}.{$file->getClientOriginalExtension()}";
            $path = $file->storeAs('stories', $filename, 'public');

            StoryImage::create([
                'story_id' => $story->id,
                'path' => $path,
                'sort_order' => $sortOrder++,
            ]);
        }
    }
}
