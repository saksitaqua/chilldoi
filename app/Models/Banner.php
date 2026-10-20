<?php

namespace App\Models;

use App\Models\Concerns\HasFocalPoint;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory, HasFocalPoint;

    protected $fillable = [
        'filename',
        'sort_order',
        'is_active',
        'focal_position',
        'focal_x',
        'focal_y',
        'focal_zoom',
    ];

    public function getFocalStyleAttribute(): string
    {
        return $this->focalStyleFrom();
    }

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getUrlAttribute(): string
    {
        return asset('images/banner/'.$this->filename);
    }
}
