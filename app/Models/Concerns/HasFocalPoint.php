<?php

namespace App\Models\Concerns;

trait HasFocalPoint
{
    protected function focalStyleFrom(string $xColumn = 'focal_x', string $yColumn = 'focal_y'): string
    {
        $x = max(0, min(100, (int) ($this->{$xColumn} ?? 50)));
        $y = max(0, min(100, (int) ($this->{$yColumn} ?? 50)));

        return "object-position: {$x}% {$y}%;";
    }
}
