<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Closure extends Model
{
    use HasFactory;

    protected $fillable = ['start_date', 'end_date', 'reason'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public static function validationMessages(): array
    {
        return [
            'closure_days.max' => 'เลือกวันหยุดได้ไม่เกิน 800 วันต่อครั้ง',
            'closure_days.*.date_format' => 'รูปแบบวันที่หยุดให้บริการไม่ถูกต้อง',
            'closure_reason.max' => 'เหตุผลยาวเกินไป (ไม่เกิน 255 ตัวอักษร)',
        ];
    }

    public function closable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Closures that touch any night of a stay (check-in day up to the day before check-out).
     */
    public function scopeOverlappingStay($query, string $checkIn, string $checkOut)
    {
        return $query->whereDate('start_date', '<', $checkOut)
            ->whereDate('end_date', '>=', $checkIn);
    }
}
