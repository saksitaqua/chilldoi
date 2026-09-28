<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Expense extends Model
{
    use HasFactory;

    public const RECURRENCE_OPTIONS = [
        3 => 'ทุก 3 เดือน',
        6 => 'ทุก 6 เดือน',
        12 => 'ทุก 1 ปี',
    ];

    protected $fillable = [
        'type',
        'category_key',
        'expense_date',
        'item',
        'is_recurring',
        'recurrence_months',
        'quantity',
        'unit_price',
        'total_amount',
        'receipt_file',
        'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'is_recurring' => 'boolean',
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

    public function getIncomeCategoryLabelAttribute(): ?string
    {
        return ExpenseCategory::labelFor($this->type, $this->category_key);
    }

    public function nextDueDate(): ?\Carbon\Carbon
    {
        if (! $this->is_recurring || ! $this->recurrence_months || ! $this->expense_date) {
            return null;
        }

        return $this->expense_date->copy()->addMonths($this->recurrence_months);
    }

    public function isRecurringDueSoon(int $days = 14): bool
    {
        $next = $this->nextDueDate();

        if (! $next) {
            return false;
        }

        return $next->lessThanOrEqualTo(now()->addDays($days)->endOfDay());
    }

    public function isRecurringOverdue(): bool
    {
        $next = $this->nextDueDate();

        return $next && $next->isPast();
    }

    /**
     * Distinct item names previously used for this type, for an autocomplete list.
     */
    public static function itemSuggestions(string $type): array
    {
        return self::where('type', $type)
            ->whereNotNull('item')
            ->where('item', '!=', '')
            ->orderByDesc('expense_date')
            ->limit(300)
            ->pluck('item')
            ->unique()
            ->values()
            ->take(50)
            ->all();
    }
}
