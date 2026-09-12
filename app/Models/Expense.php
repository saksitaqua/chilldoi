<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'expense_date',
        'item',
        'quantity',
        'unit_price',
        'total_amount',
        'receipt_file',
        'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getReceiptUrlAttribute(): ?string
    {
        return $this->receipt_file ? Storage::disk('public')->url($this->receipt_file) : null;
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'income' ? 'รายรับ' : 'รายจ่าย';
    }
}
