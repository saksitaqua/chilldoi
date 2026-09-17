<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('income_category')->nullable()->after('type');
            $table->boolean('is_recurring')->default(false)->after('item');
            $table->unsignedTinyInteger('recurrence_months')->nullable()->after('is_recurring');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['income_category', 'is_recurring', 'recurrence_months']);
        });
    }
};
