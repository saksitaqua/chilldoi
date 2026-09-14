<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'remind_from',
        'due_date',
        'budget',
        'notes',
        'status',
        'created_by',
    ];

    protected $casts = [
        'remind_from' => 'date',
        'due_date' => 'date',
        'budget' => 'decimal:2',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_date && $this->due_date->isPast();
    }

    public function isDueSoon(int $days = 7): bool
    {
        if ($this->status !== 'pending' || ! $this->due_date) {
            return false;
        }

        return $this->due_date->between(now()->startOfDay(), now()->addDays($days)->endOfDay());
    }

    public function shouldNotify(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        if ($this->remind_from) {
            return ! $this->remind_from->isFuture();
        }

        return $this->isDueSoon() || $this->isOverdue();
    }
}
