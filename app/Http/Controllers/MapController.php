<?php

namespace App\Http\Controllers;

class MapController extends Controller
{
    public function index()
    {
        $lat = (float) config('location.lat');
        $lng = (float) config('location.lng');
        $delta = 0.005;

        $seller = config('receipt.seller');
        $isEnglish = app()->getLocale() === 'en';

        $directions = trim((string) config($isEnglish ? 'location.directions_en' : 'location.directions_th'))
            ?: trim((string) config('location.directions_th'));

        return view('map.index', [
            'shop' => [
                'name' => config('location.name'),
                'lat' => $lat,
                'lng' => $lng,
                'plus_code' => config('location.plus_code'),
                'coords' => number_format($lat, 6, '.', '').', '.number_format($lng, 6, '.', ''),
                'address' => $isEnglish ? $seller['address_en'] : $seller['address_th'],
                'email' => $seller['email'],
                'embed_url' => 'https://www.openstreetmap.org/export/embed.html?bbox='
                    .implode(',', [$lng - $delta, $lat - $delta, $lng + $delta, $lat + $delta])
                    .'&layer=mapnik&marker='.$lat.','.$lng,
                'osm_url' => "https://www.openstreetmap.org/?mlat={$lat}&mlon={$lng}#map=16/{$lat}/{$lng}",
                'directions_url' => "https://www.google.com/maps/dir/?api=1&destination={$lat},{$lng}",
                'place_url' => config('location.place_url'),
            ],
            'directions' => $directions !== '' ? preg_split('/\R+/', $directions) : [],
        ]);
    }
}
