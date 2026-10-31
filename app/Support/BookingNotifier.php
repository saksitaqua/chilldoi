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
        $addresses = explode(',', (string) config('mail.notify_to'));

        if ($includeStaff) {
            $addresses = array_merge($addresses, User::pluck('email')->all());
        }

        return self::clean($addresses);
    }

    /**
     * Hidden-copy addresses from config('mail.notify_bcc'), minus anyone already in $visible.
     */
    public static function bccRecipients(array $visible = []): array
    {
        $taken = array_map('strtolower', $visible);

        return array_values(array_filter(
            self::clean(explode(',', (string) config('mail.notify_bcc'))),
            fn ($address) => ! in_array(strtolower($address), $taken, true)
        ));
    }

    public static function newBooking(Booking $booking): void
    {
        $booking->loadMissing('unit.accommodationType', 'activities', 'services');

        $to = self::recipients();

        static::send($to, self::bccRecipients($to), new NewBookingReceived($booking), 'new booking');
    }

    public static function slipUploaded(Booking $booking): void
    {
        $booking->loadMissing('unit.accommodationType');

        $to = self::recipients(includeStaff: true);

        static::send($to, self::bccRecipients($to), new PaymentSlipUploaded($booking), 'payment slip');
    }

    private static function clean(array $addresses): array
    {
        return collect($addresses)
            ->map(fn ($address) => trim((string) $address))
            ->filter(fn ($address) => $address !== '' && filter_var($address, FILTER_VALIDATE_EMAIL))
            ->unique(fn ($address) => strtolower($address))
            ->values()
            ->all();
    }

    /**
     * One message to everyone, with the hidden copies attached. If the mail server rejects it (for
     * example because one address is invalid) fall back to one message per address so a single bad
     * address cannot stop the others. A failed notification must never break the customer's booking,
     * so errors are only logged.
     */
    private static function send(array $to, array $bcc, $mailable, string $label): void
    {
        if ($to === [] && $bcc !== []) {
            [$to, $bcc] = [$bcc, []];
        }

        if ($to === []) {
            return;
        }

        try {
            $pending = Mail::to($to);

            if ($bcc !== []) {
                $pending->bcc($bcc);
            }

            $pending->send(clone $mailable);

            return;
        } catch (\Throwable $e) {
            $message = $e->getMessage();

            // The server itself is unreachable or refused our login: retrying per address would only
            // make the customer wait again, so stop here.
            if (preg_match('/could not be established|timed out|timeout|authenticat|credentials/i', $message)) {
                Log::warning("Failed to send {$label} email (mail server problem): {$message}");

                return;
            }

            Log::warning("Failed to send {$label} email to [".implode(', ', $to).'], falling back to one by one: '.$message);
        }

        foreach (array_merge($to, $bcc) as $address) {
            try {
                Mail::to($address)->send(clone $mailable);
            } catch (\Throwable $e) {
                Log::warning("Failed to send {$label} email to {$address}: ".$e->getMessage());
            }
        }
    }
}
