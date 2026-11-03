<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $table = 'feedbacks';

    /** Quick-pick topics for the "what should we improve" question. */
    public const TAGS = [
        'tent' => 'ลานกางเต็นท์',
        'toilet' => 'ห้องน้ำ',
        'clean' => 'ความสะอาด',
        'light' => 'แสงสว่าง',
        'seating' => 'พื้นที่นั่งพัก',
        'other' => 'อื่น ๆ',
    ];

    protected $fillable = [
        'booking_id',
        'receipt_id',
        'rating',
        'name',
        'comment',
        'best',
        'facility_tags',
        'facility',
        'service',
        'future',
        'read_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'facility_tags' => 'array',
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
