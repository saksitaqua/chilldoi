<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\Receipt;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Sent to the customer when staff confirm the booking.
 */
class BookingConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking, public ?Receipt $receipt = null, public bool $isCopy = false)
    {
        //
    }

    public function envelope(): Envelope
    {
        $booking = $this->booking;
        $shopEmail = config('receipt.seller.email');

        return new Envelope(
            replyTo: $shopEmail ? [new Address($shopEmail, config('receipt.seller.trade_name'))] : [],
            subject: ($this->isCopy ? '[สำเนา] ' : '').($this->receipt
                ? ($booking->status === 'confirmed' ? 'ยืนยันการจองและ' : '')."ใบเสร็จรับเงิน {$this->receipt->receipt_no} - Chill Doi Camping & Farm"
                : "ยืนยันการจอง Chill Doi Camping & Farm - {$booking->unit->name} {$booking->check_in->format('d/m/Y')}"),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-confirmed',
            with: [
                'billing' => $this->booking->billing(),
                'seller' => config('receipt.seller'),
            ],
        );
    }

    public function attachments(): array
    {
        $path = $this->booking->payment_slip;

        if ($path && Storage::disk('public')->exists($path)) {
            return [Attachment::fromStorageDisk('public', $path)->as('payment-slip.'.pathinfo($path, PATHINFO_EXTENSION))];
        }

        return [];
    }
}
