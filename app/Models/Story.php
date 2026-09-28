<?php

namespace App\Models;

use App\Models\Concerns\HasEnglishFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Story extends Model
{
    use HasFactory, HasEnglishFields;

    protected $fillable = [
        'user_id',
        'title',
        'title_en',
        'category',
        'description',
        'description_en',
        'video',
        'is_published',
        'starts_on',
        'ends_on',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'starts_on' => 'date',
        'ends_on' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::categoryLabel($this->category);
    }

    public static function categoryLabel(?string $key): string
    {
        return StoryCategory::labelFor($key) ?? (string) $key;
    }

    /**
     * @return array<string, string> category key => localized label, ordered for display
     */
    public static function categoriesForLocale(): array
    {
        return StoryCategory::options();
    }

    public function getDisplayTitleAttribute(): ?string
    {
        return $this->translate('title');
    }

    public function getDisplayDescriptionAttribute(): ?string
    {
        return $this->translate('description');
    }

    public function images(): HasMany
    {
        return $this->hasMany(StoryImage::class)->orderBy('sort_order');
    }

    public function getVideoUrlAttribute(): ?string
    {
        return $this->video ? Storage::disk('public')->url($this->video) : null;
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
