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
            'closures.*.start_date.required_with' => 'กรุณาระบุวันที่เริ่มหยุดให้บริการ',
            'closures.*.start_date.date' => 'รูปแบบวันที่เริ่มหยุดให้บริการไม่ถูกต้อง',
            'closures.*.end_date.date' => 'รูปแบบวันที่สิ้นสุดการหยุดให้บริการไม่ถูกต้อง',
            'closures.*.end_date.after_or_equal' => 'วันสุดท้ายของช่วงหยุดให้บริการต้องไม่ก่อนวันเริ่ม',
            'closures.*.reason.max' => 'เหตุผลยาวเกินไป (ไม่เกิน 255 ตัวอักษร)',
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
