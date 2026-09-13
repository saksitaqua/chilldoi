<?php

namespace App\Models;

use App\Models\Concerns\HasEnglishFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccommodationType extends Model
{
    use HasFactory, HasEnglishFields;

    protected $fillable = [
        'name',
        'name_en',
        'slug',
        'description',
        'description_en',
        'max_guests',
        'base_price',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'base_price' => 'decimal:2',
    ];

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
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
