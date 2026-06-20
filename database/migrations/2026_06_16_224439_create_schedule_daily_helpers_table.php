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
        Schema::create('schedule_daily_helpers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_daily_id')->constrained()->cascadeOnDelete();
            $table->foreignId('personnel_id')->constrained('personnels');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_daily_helpers');
    }
};
