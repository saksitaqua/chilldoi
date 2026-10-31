<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    public const METHODS = [
        'transfer' => ['th' => 'โอนเงินผ่านธนาคาร', 'en' => 'Bank transfer'],
        'promptpay' => ['th' => 'พร้อมเพย์', 'en' => 'PromptPay'],
        'cash' => ['th' => 'เงินสด', 'en' => 'Cash'],
        'other' => ['th' => 'อื่น ๆ', 'en' => 'Other'],
    ];

    protected $fillable = [
        'booking_id',
        'amount',
        'paid_at',
        'method',
        'reference',
        'note',
        'received_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('voided_at');
    }

    public function isVoided(): bool
    {
        return $this->voided_at !== null;
    }

    public function getMethodLabelAttribute(): string
    {
        return self::METHODS[$this->method]['th'] ?? $this->method;
    }

    public function getMethodLabelEnAttribute(): string
    {
        return self::METHODS[$this->method]['en'] ?? $this->method;
    }
}
