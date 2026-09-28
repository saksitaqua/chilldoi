<?php

namespace App\Models;

use App\Models\Concerns\HasEnglishFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use HasFactory, HasEnglishFields;

    protected $fillable = [
        'type',
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

    public static function options(string $type): array
    {
        return self::where('type', $type)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->pluck('display_name', 'key')
            ->all();
    }

    /**
     * All categories of a type regardless of active status, for admin forms/validation.
     */
    public static function allOptions(string $type): array
    {
        return self::where('type', $type)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->pluck('display_name', 'key')
            ->all();
    }

    public static function labelFor(string $type, ?string $key): ?string
    {
        if (! $key) {
            return null;
        }

        $category = self::where('type', $type)->where('key', $key)->first();

        return $category ? $category->display_name : $key;
    }
}
