<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('measure_units', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        $names = ['ชิ้น', 'อัน', 'ชุด', 'ถุง', 'กล่อง', 'แพ็ค', 'ขวด', 'กิโลกรัม', 'กรัม', 'ลิตร', 'ครั้ง', 'เดือน', 'วัน', 'คน'];

        DB::table('measure_units')->insert(array_map(fn ($name, $i) => [
            'name' => $name,
            'sort_order' => $i + 1,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ], $names, array_keys($names)));
    }

    public function down(): void
    {
        Schema::dropIfExists('measure_units');
    }
};
