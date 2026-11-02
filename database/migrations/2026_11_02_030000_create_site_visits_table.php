<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_visits')) {
            return;
        }

        // One row per visitor per day. The visitor is a one-way hash (IP + browser + date + app key),
        // so the raw IP address is never stored and cannot be recovered.
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->date('visit_date');
            $table->char('visitor_hash', 64);
            $table->unsignedInteger('views')->default(1);
            $table->timestamps();

            $table->unique(['visit_date', 'visitor_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visits');
    }
};
