<?php

namespace App\Support;

use App\Mail\NewBookingReceived;
use App\Mail\PaymentSlipUploaded;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BookingNotifier
{
    /**
     * Addresses from config('mail.notify_to') (comma separated), optionally plus every staff user.
     */
    public static function recipients(bool $includeStaff = false): array
    {
        $addresses = array_map('trim', explode(',', (string) config('mail.notify_to')));

        if ($includeStaff) {
            $addresses = array_merge($addresses, User::pluck('email')->all());
        }

        return collect($addresses)
            ->filter(fn ($address) => $address && filter_var($address, FILTER_VALIDATE_EMAIL))
            ->unique(fn ($address) => strtolower($address))
            ->values()
            ->all();
    }

    public static function newBooking(Booking $booking): void
    {
        $booking->loadMissing('unit.accommodationType', 'activities', 'services');

        static::send(self::recipients(), new NewBookingReceived($booking), 'new booking');
    }

    public static function slipUploaded(Booking $booking): void
    {
        $booking->loadMissing('unit.accommodationType');

        static::send(self::recipients(includeStaff: true), new PaymentSlipUploaded($booking), 'payment slip');
    }

    /**
     * A failed notification must never break the customer's booking, so it is only logged.
     */
    private static function send(array $recipients, $mailable, string $label): void
    {
        foreach ($recipients as $address) {
            try {
                Mail::to($address)->send(clone $mailable);
            } catch (\Throwable $e) {
                Log::warning("Failed to send {$label} email to {$address}: ".$e->getMessage());
            }
        }
    }
}
