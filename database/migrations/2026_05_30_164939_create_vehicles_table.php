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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_model_id')->constrained('brand_models')->onDelete('cascade');
            $table->foreignId('vehicle_type_id')->constrained('vehicle_types')->onDelete('restrict');
            $table->foreignId('vehicle_color_id')->constrained('vehicle_colors')->onDelete('restrict');
            $table->string('plate')->unique();
            $table->integer('year');
            $table->string('engine_number')->nullable();
            $table->string('chassis_number')->nullable();
            $table->integer('mileage')->default(0);
            $table->string('status')->default('Activo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
