<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'guest_name',
        'guest_phone',
        'guest_email',
        'check_in',
        'check_out',
        'guests',
        'status',
        'source',
        'notes',
        'created_by',
        'payment_slip',
        'payment_uploaded_at',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'payment_uploaded_at' => 'datetime',
    ];

    public function getPaymentSlipUrlAttribute(): ?string
    {
        return $this->payment_slip ? Storage::disk('public')->url($this->payment_slip) : null;
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class, 'booking_activity');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'booking_service')
            ->withPivot('quantity');
    }

    public function scopeOverlapping($query, string $checkIn, string $checkOut)
    {
        return $query->where('check_in', '<', $checkOut)
            ->where('check_out', '>', $checkIn);
    }
}
