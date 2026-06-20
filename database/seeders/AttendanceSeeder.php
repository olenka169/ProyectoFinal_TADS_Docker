<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Personnel;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener personal con contrato Permanente o Nombrado
        $personnel = Personnel::whereHas('contracts', function($q) {
            $q->whereIn('type', ['Permanente', 'Nombrado'])->where('is_active', true);
        })->get();

        foreach ($personnel as $p) {
            // Registrar asistencia (Ingreso) para el día de hoy
            Attendance::updateOrCreate(
                [
                    'personnel_id' => $p->id,
                    'date' => now()->toDateString(),
                    'type' => 'Ingreso',
                ],
                [
                    'time' => '08:00:00',
                    'status' => 'Presente'
                ]
            );
        }
    }
}
