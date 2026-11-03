<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $feedbacks = Feedback::with(['booking.unit', 'receipt'])
            ->when($request->filled('rating'), fn ($q) => $q->where('rating', (int) $request->rating))
            ->when($request->boolean('unread'), fn ($q) => $q->unread())
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.feedbacks.index', [
            'feedbacks' => $feedbacks,
            'average' => Feedback::avg('rating'),
            'total' => Feedback::count(),
            'unread' => Feedback::unread()->count(),
            'tagCounts' => $this->tagCounts(),
        ]);
    }

    /** How often each quick-pick topic was chosen, most mentioned first. */
    private function tagCounts(): array
    {
        $counts = [];
        foreach (Feedback::whereNotNull('facility_tags')->pluck('facility_tags') as $tags) {
            foreach ((array) $tags as $tag) {
                if (isset(Feedback::TAGS[$tag])) {
                    $counts[$tag] = ($counts[$tag] ?? 0) + 1;
                }
            }
        }
        arsort($counts);

        return $counts;
    }

    public function read(Feedback $feedback)
    {
        $feedback->update(['read_at' => $feedback->read_at ?? now()]);

        return back();
    }
}
