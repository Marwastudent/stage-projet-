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
        if (!Schema::hasTable('screens') || Schema::hasColumn('screens', 'emplacement')) {
            return;
        }

        Schema::table('screens', function (Blueprint $table): void {
            $table->string('emplacement')->default('entree')->after('name');
            $table->index('emplacement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('screens') || !Schema::hasColumn('screens', 'emplacement')) {
            return;
        }

        Schema::table('screens', function (Blueprint $table): void {
            $table->dropIndex(['emplacement']);
            $table->dropColumn('emplacement');
        });
    }
};

