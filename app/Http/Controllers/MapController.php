<?php

namespace App\Http\Controllers;

use App\Models\Unit;

class MapController extends Controller
{
    public function index()
    {
        $units = Unit::with('accommodationType')
            ->where('is_active', true)
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->get();

        $mapPoints = $units->map(fn (Unit $unit) => [
            'name' => $unit->name,
            'type' => $unit->accommodationType->display_name,
            'lat' => (float) $unit->lat,
            'lng' => (float) $unit->lng,
        ])->values();

        return view('map.index', compact('units', 'mapPoints'));
    }
}
