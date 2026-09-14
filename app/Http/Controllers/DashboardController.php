<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Booking;
use App\Models\Plan;
use App\Models\Story;

class DashboardController extends Controller
{
    public function index()
    {
        $pendingPayments = Booking::with('unit.accommodationType')
            ->where('status', 'pending')
            ->whereNull('payment_slip')
            ->orderBy('check_in')
            ->limit(8)
            ->get();

        $unpublishedStories = Story::where('is_published', false)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $expiringStories = Story::where('is_published', true)
            ->whereNotNull('ends_on')
            ->whereBetween('ends_on', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->orderBy('ends_on')
            ->limit(5)
            ->get();

        $expiringActivities = Activity::where('is_active', true)
            ->whereNotNull('ends_on')
            ->whereBetween('ends_on', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->orderBy('ends_on')
            ->limit(5)
            ->get();

        $upcomingPlans = Plan::with('creator')
            ->where('status', 'pending')
            ->whereNotNull('due_date')
            ->where('due_date', '<=', now()->addDays(7)->toDateString())
            ->orderBy('due_date')
            ->limit(8)
            ->get();

        return view('dashboard', compact(
            'pendingPayments',
            'unpublishedStories',
            'expiringStories',
            'expiringActivities',
            'upcomingPlans'
        ));
    }
}
