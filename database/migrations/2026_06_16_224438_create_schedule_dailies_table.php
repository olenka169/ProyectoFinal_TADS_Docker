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
        Schema::create('schedule_dailies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            
            // Datos del servicio para ese día (pueden ser reprogramados)
            $table->foreignId('shift_id')->constrained();
            $table->foreignId('vehicle_id')->constrained();
            $table->foreignId('driver_id')->constrained('personnels');
            
            // Estado del día: pendiente, completado, reprogramado, cancelado
            $table->string('status')->default('pendiente');
            $table->text('notes')->nullable(); // Para justificar cambios o incidencias
            
            $table->timestamps();

            $table->unique(['schedule_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_dailies');
    }
};
