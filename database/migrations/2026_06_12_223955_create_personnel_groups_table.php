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
        Schema::create('personnel_groups', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->foreignId('zone_id')
                ->constrained()
                ->cascadeOnUpdate();

            $table->foreignId('shift_id')
                ->constrained()
                ->cascadeOnUpdate();

            $table->foreignId('vehicle_id')
                ->constrained()
                ->cascadeOnUpdate();

            $table->foreignId('driver_id')
                ->constrained('personnels')
                ->cascadeOnUpdate();

            $table->boolean('status')
                ->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnel_groups');
    }
};