<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $table = 'feedbacks';

    protected $fillable = [
        'booking_id',
        'receipt_id',
        'rating',
        'name',
        'comment',
        'read_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'read_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(Receipt::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}
