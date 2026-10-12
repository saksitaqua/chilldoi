<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $names = [
        'โหล', 'ม้วน', 'ตลับ', 'ตัว', 'กระป๋อง', 'แท่ง', 'ก้อน', 'รีม',
        'เล่ม', 'ซอง', 'เครื่อง', 'ห้อง', 'หลัง', 'ผืน', 'ลัง',
    ];

    public function up(): void
    {
        $now = now();
        $order = (int) DB::table('measure_units')->max('sort_order');

        foreach ($this->names as $name) {
            if (DB::table('measure_units')->where('name', $name)->exists()) {
                continue;
            }

            DB::table('measure_units')->insert([
                'name' => $name,
                'sort_order' => ++$order,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('measure_units')->whereIn('name', $this->names)->delete();
    }
};
