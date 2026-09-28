<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'email', 'notes'];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public static function findOrCreateFromBooking(string $name, ?string $phone, ?string $email): self
    {
        $normalizedPhone = self::normalizePhone($phone);

        $customer = null;

        if ($normalizedPhone) {
            $customer = self::whereRaw(
                "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, '-', ''), ' ', ''), '+', ''), '(', ''), ')', '') = ?",
                [$normalizedPhone]
            )->first();
        }

        if (! $customer && $email) {
            $customer = self::where('email', $email)->first();
        }

        if ($customer) {
            $updates = [];
            if (! $customer->phone && $phone) {
                $updates['phone'] = $phone;
            }
            if (! $customer->email && $email) {
                $updates['email'] = $email;
            }
            if ($updates) {
                $customer->update($updates);
            }

            return $customer;
        }

        return self::create([
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
        ]);
    }

    private static function normalizePhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', $phone);

        return $digits ?: null;
    }
}
