<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'customer_id',
        'guest_name',
        'guest_phone',
        'guest_email',
        'check_in',
        'check_out',
        'guests',
        'status',
        'source',
        'notes',
        'created_by',
        'payment_slip',
        'payment_uploaded_at',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'payment_uploaded_at' => 'datetime',
    ];

    public function getPaymentSlipUrlAttribute(): ?string
    {
        return $this->payment_slip ? Storage::disk('public')->url($this->payment_slip) : null;
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class, 'booking_activity');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'booking_service')
            ->withPivot('quantity');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('paid_at');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class)->orderByDesc('id');
    }

    public function nights(): int
    {
        return max(1, (int) round($this->check_in->diffInDays($this->check_out, true)));
    }

    /**
     * What the system says this booking costs (room x nights + chargeable activities + services),
     * as bilingual lines ready to print on a receipt.
     */
    public function billing(): array
    {
        $this->loadMissing('unit.accommodationType', 'activities', 'services');

        $type = $this->unit->accommodationType;
        $nights = $this->nights();
        $roomPrice = (float) $type->base_price;

        $lines = [[
            'th' => 'ค่าที่พัก '.$this->unit->name.' ('.$type->name.')',
            'en' => 'Accommodation: '.$this->unit->name.' ('.($type->name_en ?: $type->name).')',
            'qty' => $nights,
            'unit_price' => $roomPrice,
            'amount' => round($nights * $roomPrice, 2),
        ]];

        foreach ($this->activities as $activity) {
            $price = $activity->is_free ? 0.0 : (float) $activity->price;

            $lines[] = [
                'th' => 'กิจกรรม: '.$activity->name,
                'en' => 'Activity: '.($activity->name_en ?: $activity->name),
                'qty' => 1,
                'unit_price' => $price,
                'amount' => round($price, 2),
            ];
        }

        foreach ($this->services as $service) {
            $qty = max(1, (int) $service->pivot->quantity);
            $price = (float) $service->price;

            $lines[] = [
                'th' => 'บริการเสริม: '.$service->name,
                'en' => 'Service: '.($service->name_en ?: $service->name),
                'qty' => $qty,
                'unit_price' => $price,
                'amount' => round($qty * $price, 2),
            ];
        }

        return [
            'lines' => $lines,
            'total' => round(array_sum(array_column($lines, 'amount')), 2),
        ];
    }

    public function scopeOverlapping($query, string $checkIn, string $checkOut)
    {
        return $query->where('check_in', '<', $checkOut)
            ->where('check_out', '>', $checkIn);
    }
}
