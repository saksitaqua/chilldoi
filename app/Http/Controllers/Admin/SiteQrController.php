<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\QrCode;

/**
 * Printable QR codes that open the public website (4 styles to choose from).
 */
class SiteQrController extends Controller
{
    public function index()
    {
        $url = route('home');

        return view('admin.qr.index', [
            'url' => $url,
            'qr' => QrCode::svg($url, 2),
            'seller' => config('receipt.seller'),
        ]);
    }

    public function download()
    {
        return response(QrCode::svg(route('home'), 4), 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="chilldoi-website-qr.svg"',
        ]);
    }
}
