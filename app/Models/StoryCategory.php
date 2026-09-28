<?php

namespace App\Models;

use App\Models\Concerns\HasEnglishFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoryCategory extends Model
{
    use HasFactory, HasEnglishFields;

    protected $fillable = [
        'key',
        'name',
        'name_en',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getDisplayNameAttribute(): ?string
    {
        return $this->translate('name');
    }

    public static function options(): array
    {
        return self::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->pluck('display_name', 'key')
            ->all();
    }

    /**
     * All categories regardless of active status, for admin forms/validation.
     */
    public static function allOptions(): array
    {
        return self::orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->pluck('display_name', 'key')
            ->all();
    }

    public static function labelFor(?string $key): ?string
    {
        if (! $key) {
            return null;
        }

        $category = self::where('key', $key)->first();

        return $category ? $category->display_name : $key;
    }
}
