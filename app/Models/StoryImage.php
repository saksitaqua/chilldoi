<?php

namespace App\Models;

use App\Models\Concerns\HasFocalPoint;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class StoryImage extends Model
{
    use HasFactory, HasFocalPoint;

    protected $fillable = [
        'story_id',
        'path',
        'sort_order',
        'focal_x',
        'focal_y',
    ];

    public function getFocalStyleAttribute(): string
    {
        return $this->focalStyleFrom();
    }

    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
