<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('coaches') || Schema::hasColumn('coaches', 'first_name')) {
            return;
        }

        Schema::table('coaches', function (Blueprint $table): void {
            $table->string('first_name', 120)->nullable()->after('name');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('coaches') || ! Schema::hasColumn('coaches', 'first_name')) {
            return;
        }

        Schema::table('coaches', function (Blueprint $table): void {
            $table->dropColumn('first_name');
        });
    }
};
