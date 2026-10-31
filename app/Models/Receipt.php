<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_no',
        'issue_year',
        'sequence',
        'booking_id',
        'replaces_id',
        'issued_at',
        'total_amount',
        'adjustment_note',
        'customer_name',
        'customer_phone',
        'customer_email',
        'snapshot',
        'issued_by',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'voided_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'snapshot' => 'array',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function replaces(): BelongsTo
    {
        return $this->belongsTo(self::class, 'replaces_id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('voided_at');
    }

    public function isVoided(): bool
    {
        return $this->voided_at !== null;
    }

    /**
     * A link that works without logging in, protected by Laravel's URL signature, so staff can send it to the customer.
     */
    public function customerUrl(): string
    {
        return URL::signedRoute('receipts.public', ['receipt' => $this->id]);
    }
}
