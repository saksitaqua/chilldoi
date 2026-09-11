<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Story;
use Illuminate\Http\Request;

class StoryFeedController extends Controller
{
    public function index(Request $request)
    {
        $banners = Banner::where('is_active', true)->orderBy('sort_order')->get();

        if ($request->filled('category')) {
            $stories = Story::with('images')
                ->visibleNow()
                ->where('category', $request->string('category'))
                ->orderByDesc('created_at')
                ->paginate(9)
                ->withQueryString();

            return view('stories.index', [
                'banners' => $banners,
                'filteredCategory' => $request->string('category')->value(),
                'stories' => $stories,
                'groups' => null,
            ]);
        }

        $groups = collect(Story::CATEGORIES)->map(function ($label, $key) {
            return [
                'label' => $label,
                'key' => $key,
                'stories' => Story::with('images')
                    ->visibleNow()
                    ->where('category', $key)
                    ->orderByDesc('created_at')
                    ->limit(6)
                    ->get(),
            ];
        })->filter(fn ($group) => $group['stories']->isNotEmpty());

        return view('stories.index', [
            'banners' => $banners,
            'groups' => $groups,
            'stories' => null,
            'filteredCategory' => null,
        ]);
    }

    public function show(Story $story)
    {
        abort_unless($story->isVisibleNow(), 404);

        $story->load('images');

        return view('stories.show', compact('story'));
    }
}
