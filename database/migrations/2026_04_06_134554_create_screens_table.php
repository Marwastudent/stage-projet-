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
        Schema::create('screens', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('emplacement')->default('entree')->index();
            $table->foreignId('sports_hall_id')->constrained('sports_halls')->cascadeOnDelete();
            $table->string('device_key')->unique();
            $table->string('status')->default('offline')->index();
            $table->timestamps();

            $table->index(['name', 'sports_hall_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screens');
    }
};
