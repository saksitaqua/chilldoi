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
            'title_en' => 'nullable|string|max:255',
            'category' => ['required', Rule::in(array_keys(Story::CATEGORIES))],
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'starts_on' => 'nullable|date',
            'ends_on' => 'nullable|date|after_or_equal:starts_on',
            'images' => 'nullable|array',
            'images.*' => 'image|max:5120',
            'video' => 'nullable|mimes:mp4,mov,webm,avi|max:51200',
        ]);

        $story = Story::create([
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'title_en' => $data['title_en'] ?? null,
            'category' => $data['category'],
            'description' => $data['description'] ?? null,
            'description_en' => $data['description_en'] ?? null,
            'is_published' => $request->boolean('is_published', true),
            'starts_on' => $data['starts_on'] ?? null,
            'ends_on' => $data['ends_on'] ?? null,
        ]);

        $this->storeImages($story, $request->file('images', []));

        if ($request->hasFile('video')) {
            $this->storeVideo($story, $request->file('video'));
        }

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
            'title_en' => 'nullable|string|max:255',
            'category' => ['required', Rule::in(array_keys(Story::CATEGORIES))],
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'starts_on' => 'nullable|date',
            'ends_on' => 'nullable|date|after_or_equal:starts_on',
            'images' => 'nullable|array',
            'images.*' => 'image|max:5120',
            'video' => 'nullable|mimes:mp4,mov,webm,avi|max:51200',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'exists:story_images,id',
            'remove_video' => 'nullable|boolean',
        ]);

        $updateData = [
            'title' => $data['title'],
            'title_en' => $data['title_en'] ?? null,
            'category' => $data['category'],
            'description' => $data['description'] ?? null,
            'description_en' => $data['description_en'] ?? null,
            'is_published' => $request->boolean('is_published', true),
            'starts_on' => $data['starts_on'] ?? null,
            'ends_on' => $data['ends_on'] ?? null,
        ];

        if ($request->boolean('remove_video') && $story->video) {
            Storage::disk('public')->delete($story->video);
            $updateData['video'] = null;
        }

        $story->update($updateData);

        if (! empty($data['remove_images'])) {
            StoryImage::whereIn('id', $data['remove_images'])->where('story_id', $story->id)->get()->each(function (StoryImage $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            });
        }

        $this->storeImages($story, $request->file('images', []));

        if ($request->hasFile('video')) {
            $this->storeVideo($story, $request->file('video'));
        }

        return redirect()->route('admin.stories.index')->with('status', 'บันทึกการแก้ไขเรียบร้อยแล้ว');
    }

    public function destroy(Story $story)
    {
        foreach ($story->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        if ($story->video) {
            Storage::disk('public')->delete($story->video);
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

    private function storeVideo(Story $story, $file): void
    {
        if ($story->video) {
            Storage::disk('public')->delete($story->video);
        }

        $filename = "{$story->id}.{$file->getClientOriginalExtension()}";
        $path = $file->storeAs('stories/videos', $filename, 'public');

        $story->update(['video' => $path]);
    }
}
