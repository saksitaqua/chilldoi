<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['banners', 'story_images', 'unit_images'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->unsignedTinyInteger('focal_x')->default(50);
                $t->unsignedTinyInteger('focal_y')->default(50);
            });
        }

        foreach (['activities', 'services'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->unsignedTinyInteger('image_focal_x')->default(50);
                $t->unsignedTinyInteger('image_focal_y')->default(50);
            });
        }

        // Carry the old top/center/bottom choice over; the old column is left in place.
        DB::table('banners')->where('focal_position', 'top')->update(['focal_y' => 0]);
        DB::table('banners')->where('focal_position', 'bottom')->update(['focal_y' => 100]);
    }

    public function down(): void
    {
        foreach (['banners', 'story_images', 'unit_images'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn(['focal_x', 'focal_y']);
            });
        }

        foreach (['activities', 'services'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn(['image_focal_x', 'image_focal_y']);
            });
        }
    }
};
