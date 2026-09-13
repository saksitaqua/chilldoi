<?php

namespace App\Models;

use App\Models\Concerns\HasEnglishFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Service extends Model
{
    use HasFactory, HasEnglishFields;

    protected $fillable = [
        'name',
        'name_en',
        'description',
        'description_en',
        'image',
        'price',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'booking_service')
            ->withPivot('quantity');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::disk('public')->url($this->image) : null;
    }

    public function getPriceLabelAttribute(): string
    {
        $amount = number_format((float) $this->price, 0);

        return app()->getLocale() === 'en' ? "{$amount} THB" : "{$amount} บาท";
    }

    public function getDisplayNameAttribute(): ?string
    {
        return $this->translate('name');
    }

    public function getDisplayDescriptionAttribute(): ?string
    {
        return $this->translate('description');
    }
}
