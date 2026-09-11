<?php

namespace Database\Seeders;

use App\Models\AccommodationType;
use App\Models\Booking;
use App\Models\Unit;
use App\Models\User;
use App\Support\Slug;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'sterpdd@gmail.com'],
            ['name' => 'Admin', 'password' => bcrypt('password')]
        );

        $house = AccommodationType::firstOrCreate(
            ['name' => 'บ้านพัก'],
            ['slug' => Slug::unique('บ้านพัก', AccommodationType::class), 'description' => 'บ้านพักพร้อมสิ่งอำนวยความสะดวก', 'max_guests' => 4, 'base_price' => 1200]
        );

        $tent = AccommodationType::firstOrCreate(
            ['name' => 'จุดกางเต็นท์'],
            ['slug' => Slug::unique('จุดกางเต็นท์', AccommodationType::class), 'description' => 'จุดกางเต็นท์ริมธรรมชาติ', 'max_guests' => 2, 'base_price' => 300]
        );

        $units = collect();

        foreach (range(1, 3) as $i) {
            $units->push(Unit::firstOrCreate(
                ['accommodation_type_id' => $house->id, 'name' => "บ้านพัก #$i"],
                ['lat' => 13.7563 + ($i * 0.001), 'lng' => 100.5018 + ($i * 0.001)]
            ));
        }

        foreach (range(1, 5) as $i) {
            $units->push(Unit::firstOrCreate(
                ['accommodation_type_id' => $tent->id, 'name' => "จุดกางเต็นท์ #$i"],
                ['lat' => 13.7580 + ($i * 0.001), 'lng' => 100.5030 + ($i * 0.001)]
            ));
        }

        Booking::firstOrCreate([
            'unit_id' => $units->first()->id,
            'guest_name' => 'สมชาย ใจดี',
            'check_in' => now()->startOfWeek()->addDay(),
            'check_out' => now()->startOfWeek()->addDays(3),
        ], [
            'guest_phone' => '0812345678',
            'guests' => 2,
            'status' => 'confirmed',
            'source' => 'admin',
            'created_by' => $admin->id,
        ]);
    }
}
