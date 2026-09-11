<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Story extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'category',
        'description',
        'is_published',
        'starts_on',
        'ends_on',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'starts_on' => 'date',
        'ends_on' => 'date',
    ];

    public const CATEGORIES = [
        'recommend' => 'แนะนำ',
        'event' => 'อีเวนต์',
        'benefit' => 'ประโยชน์',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function images(): HasMany
    {
        return $this->hasMany(StoryImage::class)->orderBy('sort_order');
    }

    public function scopeVisibleNow($query)
    {
        $today = now()->toDateString();

        return $query->where('is_published', true)
            ->where(fn ($q) => $q->whereNull('starts_on')->orWhere('starts_on', '<=', $today))
            ->where(fn ($q) => $q->whereNull('ends_on')->orWhere('ends_on', '>=', $today));
    }

    public function isVisibleNow(): bool
    {
        if (! $this->is_published) {
            return false;
        }

        $today = now()->toDateString();

        if ($this->starts_on && $this->starts_on->toDateString() > $today) {
            return false;
        }

        if ($this->ends_on && $this->ends_on->toDateString() < $today) {
            return false;
        }

        return true;
    }
}
