<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Slug
{
    /**
     * Build a unique slug for the given model, supporting non-Latin (e.g. Thai) names.
     */
    public static function unique(string $name, string $modelClass, ?int $ignoreId = null): string
    {
        $base = Str::slug($name, '-', null);

        if ($base === '') {
            $base = 'item';
        }

        $slug = $base;
        $suffix = 2;

        while ((new $modelClass)->newQuery()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
