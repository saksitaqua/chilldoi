<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['banners', 'story_images', 'unit_images'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->unsignedSmallInteger('focal_zoom')->default(100);
            });
        }

        foreach (['activities', 'services'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->unsignedSmallInteger('image_focal_zoom')->default(100);
            });
        }
    }

    public function down(): void
    {
        foreach (['banners', 'story_images', 'unit_images'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('focal_zoom');
            });
        }

        foreach (['activities', 'services'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('image_focal_zoom');
            });
        }
    }
};
