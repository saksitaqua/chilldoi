<?php

namespace App\Models;

use App\Models\Concerns\HasFocalPoint;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class UnitImage extends Model
{
    use HasFactory, HasFocalPoint;

    protected $fillable = [
        'unit_id',
        'path',
        'sort_order',
        'is_visible',
        'focal_x',
        'focal_y',
        'focal_zoom',
    ];

    public function getFocalStyleAttribute(): string
    {
        return $this->focalStyleFrom();
    }

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
