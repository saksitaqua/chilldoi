<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Story groups are now admin-editable (story_categories), so the column can no longer be a fixed
     * enum of the three original keys. Existing values are kept as they are.
     */
    public function up(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->string('category', 255)->default('recommend')->change();
        });
    }

    public function down(): void
    {
        // Intentionally left as a string: narrowing back to the old enum would reject or truncate
        // rows that use groups created later.
    }
};
