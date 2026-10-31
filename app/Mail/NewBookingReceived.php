<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewBookingReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking)
    {
        //
    }

    public function envelope(): Envelope
    {
        $booking = $this->booking;

        return new Envelope(
            replyTo: $booking->guest_email ? [new Address($booking->guest_email, $booking->guest_name)] : [],
            subject: "มีการจองใหม่ - {$booking->guest_name} ({$booking->unit->name}) {$booking->check_in->format('d/m/Y')}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-booking',
        );
    }
}
