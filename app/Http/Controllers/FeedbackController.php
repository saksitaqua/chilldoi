<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Receipt;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Reached from the QR code on the receipt. The token is derived from the receipt id and the app key,
     * so the link cannot be guessed and nothing extra has to be stored.
     */
    public function create(Receipt $receipt, string $token)
    {
        $this->authorizeToken($receipt, $token);

        return view('feedback.create', [
            'receipt' => $receipt,
            'token' => $token,
            'existing' => Feedback::where('booking_id', $receipt->booking_id)->first(),
        ]);
    }

    public function store(Request $request, Receipt $receipt, string $token)
    {
        $this->authorizeToken($receipt, $token);

        if (Feedback::where('booking_id', $receipt->booking_id)->exists()) {
            return redirect()->route('feedback.create', [$receipt, $token]);
        }

        $data = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'name' => 'nullable|string|max:100',
            'comment' => 'nullable|string|max:2000',
        ]);

        Feedback::create([
            'booking_id' => $receipt->booking_id,
            'receipt_id' => $receipt->id,
            'rating' => $data['rating'],
            'name' => $data['name'] ?? null,
            'comment' => $data['comment'] ?? null,
        ]);

        return redirect()->route('feedback.create', [$receipt, $token])->with('submitted', true);
    }

    private function authorizeToken(Receipt $receipt, string $token): void
    {
        abort_unless(hash_equals($receipt->feedbackToken(), $token), 404);
    }
}
