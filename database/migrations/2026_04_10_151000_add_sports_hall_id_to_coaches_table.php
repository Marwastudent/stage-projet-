<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('coaches') || Schema::hasColumn('coaches', 'sports_hall_id')) {
            return;
        }

        Schema::table('coaches', function (Blueprint $table): void {
            $table->foreignId('sports_hall_id')->nullable()->after('specialty')->constrained('sports_halls')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('coaches') || ! Schema::hasColumn('coaches', 'sports_hall_id')) {
            return;
        }

        Schema::table('coaches', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('sports_hall_id');
        });
    }
};
