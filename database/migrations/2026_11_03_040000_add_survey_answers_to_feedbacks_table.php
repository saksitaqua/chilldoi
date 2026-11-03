<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Additive only: `comment` stays as the reason for the rating, the new columns hold the other survey answers.
        Schema::table('feedbacks', function (Blueprint $table) {
            if (! Schema::hasColumn('feedbacks', 'best')) {
                $table->text('best')->nullable()->after('comment');
            }
            if (! Schema::hasColumn('feedbacks', 'facility_tags')) {
                $table->json('facility_tags')->nullable()->after('best');
            }
            if (! Schema::hasColumn('feedbacks', 'facility')) {
                $table->text('facility')->nullable()->after('facility_tags');
            }
            if (! Schema::hasColumn('feedbacks', 'service')) {
                $table->text('service')->nullable()->after('facility');
            }
            if (! Schema::hasColumn('feedbacks', 'future')) {
                $table->text('future')->nullable()->after('service');
            }
        });
    }

    public function down(): void
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            $table->dropColumn(['best', 'facility_tags', 'facility', 'service', 'future']);
        });
    }
};
