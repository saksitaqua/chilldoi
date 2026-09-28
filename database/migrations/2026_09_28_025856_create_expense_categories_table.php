<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['income', 'expense']);
            $table->string('key');
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['type', 'key']);
        });

        $now = now();

        DB::table('expense_categories')->insert([
            ['type' => 'income', 'key' => 'booking', 'name' => 'จองที่พัก', 'name_en' => 'Booking', 'sort_order' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'income', 'key' => 'coffee', 'name' => 'ร้านกาแฟ', 'name_en' => 'Coffee Shop', 'sort_order' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'income', 'key' => 'equipment', 'name' => 'เช่าอุปกรณ์', 'name_en' => 'Equipment Rental', 'sort_order' => 3, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'income', 'key' => 'agriculture', 'name' => 'ขายพืชการเกษตร', 'name_en' => 'Farm Produce', 'sort_order' => 4, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'income', 'key' => 'other', 'name' => 'อื่นๆ', 'name_en' => 'Other', 'sort_order' => 5, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            ['type' => 'expense', 'key' => 'utilities_water', 'name' => 'ค่าน้ำ', 'name_en' => 'Water Bill', 'sort_order' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'expense', 'key' => 'utilities_electric', 'name' => 'ค่าไฟ', 'name_en' => 'Electricity Bill', 'sort_order' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'expense', 'key' => 'staff_wage', 'name' => 'ค่าจ้างพนักงาน', 'name_en' => 'Staff Wages', 'sort_order' => 3, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'expense', 'key' => 'supplies', 'name' => 'ซื้อวัตถุดิบ/ของใช้', 'name_en' => 'Supplies', 'sort_order' => 4, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'expense', 'key' => 'maintenance', 'name' => 'ซ่อมบำรุง', 'name_en' => 'Maintenance', 'sort_order' => 5, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'expense', 'key' => 'marketing', 'name' => 'การตลาด/โฆษณา', 'name_en' => 'Marketing', 'sort_order' => 6, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'expense', 'key' => 'other', 'name' => 'อื่นๆ', 'name_en' => 'Other', 'sort_order' => 7, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        if (Schema::hasColumn('expenses', 'income_category')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->string('category_key')->nullable()->after('income_category');
            });

            DB::table('expenses')->whereNotNull('income_category')->orderBy('id')->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('expenses')->where('id', $row->id)->update(['category_key' => $row->income_category]);
                }
            });

            Schema::table('expenses', function (Blueprint $table) {
                $table->dropColumn('income_category');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('expenses', 'category_key')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->string('income_category')->nullable()->after('type');
            });

            DB::table('expenses')->whereNotNull('category_key')->orderBy('id')->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('expenses')->where('id', $row->id)->update(['income_category' => $row->category_key]);
                }
            });

            Schema::table('expenses', function (Blueprint $table) {
                $table->dropColumn('category_key');
            });
        }

        Schema::dropIfExists('expense_categories');
    }
};
