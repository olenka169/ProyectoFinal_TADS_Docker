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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            
            // Relación con el grupo de origen (plantilla)
            $table->foreignId('personnel_group_id')->constrained()->cascadeOnDelete();
            
            // Datos operativos (pueden diferir del grupo original)
            $table->foreignId('zone_id')->constrained();
            $table->foreignId('shift_id')->constrained();
            $table->foreignId('vehicle_id')->constrained();
            $table->foreignId('driver_id')->constrained('personnels');
            
            // Rango de fechas
            $table->date('start_date');
            $table->date('end_date');
            
            // Estado de la programación
            // scheduled: programada, in_progress: en curso, completed: finalizada, cancelled: cancelada
            $table->string('status')->default('scheduled');
            
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });

        // Tabla para los ayudantes de la programación específica
        Schema::create('schedule_helpers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('personnel_id')->constrained('personnels');
            $table->timestamps();
        });

        // Tabla para los días de la semana específicos de la programación
        Schema::create('schedule_workdays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->string('day'); // Lunes, Martes, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_workdays');
        Schema::dropIfExists('schedule_helpers');
        Schema::dropIfExists('schedules');
    }
};
