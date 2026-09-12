<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'video',
        'is_free',
        'price',
        'starts_on',
        'ends_on',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_free' => 'boolean',
        'price' => 'decimal:2',
        'starts_on' => 'date',
        'ends_on' => 'date',
    ];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }

    public function getVideoUrlAttribute(): ?string
    {
        return $this->video ? Storage::disk('public')->url($this->video) : null;
    }

    public function getPriceLabelAttribute(): string
    {
        if ($this->is_free) {
            return 'ฟรี';
        }

        return number_format((float) $this->price, 0).' บาท';
    }

    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'booking_activity');
    }

    public function scopeAvailableNow($query)
    {
        $today = now()->toDateString();

        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_on')->orWhere('starts_on', '<=', $today))
            ->where(fn ($q) => $q->whereNull('ends_on')->orWhere('ends_on', '>=', $today));
    }

    public function isAvailableNow(): bool
    {
        if (! $this->is_active) {
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
