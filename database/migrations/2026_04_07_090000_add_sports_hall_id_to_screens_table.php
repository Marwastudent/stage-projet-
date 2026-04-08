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
        if (!Schema::hasTable('screens')) {
            return;
        }

        if (!Schema::hasColumn('screens', 'sports_hall_id')) {
            Schema::table('screens', function (Blueprint $table): void {
                $table->unsignedBigInteger('sports_hall_id')->nullable()->after('name');
                $table->index('sports_hall_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('screens') || !Schema::hasColumn('screens', 'sports_hall_id')) {
            return;
        }

        Schema::table('screens', function (Blueprint $table): void {
            $table->dropIndex(['sports_hall_id']);
            $table->dropColumn('sports_hall_id');
        });
    }
};

