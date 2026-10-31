<?php

namespace App\Http\Controllers;

use App\Models\Receipt;

class PublicReceiptController extends Controller
{
    /**
     * Reached only through a signed link that staff copy from the admin receipt page.
     */
    public function show(Receipt $receipt)
    {
        return response()
            ->view('receipts.print', ['receipt' => $receipt, 'mode' => 'public'])
            ->header('Cache-Control', 'no-store, private')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
