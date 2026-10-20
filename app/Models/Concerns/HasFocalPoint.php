<?php

namespace App\Models\Concerns;

trait HasFocalPoint
{
    /**
     * CSS that crops the image like a zoomable window: the chosen point stays at the
     * same relative spot in the frame, and zoom enlarges around it. The frame must
     * clip its overflow (overflow: hidden) when zoom is above 100.
     */
    protected function focalStyleFrom(string $xColumn = 'focal_x', string $yColumn = 'focal_y', string $zoomColumn = 'focal_zoom'): string
    {
        $x = max(0, min(100, (int) ($this->{$xColumn} ?? 50)));
        $y = max(0, min(100, (int) ($this->{$yColumn} ?? 50)));
        $zoom = max(100, min(300, (int) ($this->{$zoomColumn} ?? 100)));

        $style = "object-position: {$x}% {$y}%;";

        if ($zoom > 100) {
            $scale = round($zoom / 100, 2);
            $style .= " transform: scale({$scale}); transform-origin: {$x}% {$y}%;";
        }

        return $style;
    }
}
