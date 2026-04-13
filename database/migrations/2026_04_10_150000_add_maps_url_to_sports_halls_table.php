<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sports_halls') || Schema::hasColumn('sports_halls', 'maps_url')) {
            return;
        }

        Schema::table('sports_halls', function (Blueprint $table): void {
            $table->string('maps_url', 500)->nullable()->after('localisation');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('sports_halls') || ! Schema::hasColumn('sports_halls', 'maps_url')) {
            return;
        }

        Schema::table('sports_halls', function (Blueprint $table): void {
            $table->dropColumn('maps_url');
        });
    }
};
