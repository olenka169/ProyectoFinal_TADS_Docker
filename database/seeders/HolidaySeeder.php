<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $holidays = [
            ['date' => '2027-01-01', 'description' => 'Año Nuevo', 'status' => 0],
            ['date' => '2027-01-02', 'description' => 'Día no laborable para el sector público', 'status' => 0],
            ['date' => '2027-03-25', 'description' => 'Semana Santa', 'status' => 0],
            ['date' => '2027-03-26', 'description' => 'Semana Santa', 'status' => 0],
            ['date' => '2027-05-01', 'description' => 'Día del Trabajo', 'status' => 0],
            ['date' => '2027-06-07', 'description' => 'Batalla de Arica y Día de la Bandera', 'status' => 0],
            ['date' => '2026-07-23', 'description' => 'Día de la Fuerza Aérea del Perú', 'status' => 1],
            ['date' => '2026-07-27', 'description' => 'Día no laborable para el sector público', 'status' => 1],
            ['date' => '2026-07-28', 'description' => 'Fiestas Patrias', 'status' => 1],
            ['date' => '2026-07-29', 'description' => 'Fiestas Patrias', 'status' => 1],
            ['date' => '2026-08-06', 'description' => 'Batalla de Junín', 'status' => 1],
            ['date' => '2026-08-30', 'description' => 'Santa Rosa de Lima', 'status' => 1],
            ['date' => '2026-10-08', 'description' => 'Combate de Angamos', 'status' => 1],
            ['date' => '2026-11-01', 'description' => 'Día de Todos los Santos', 'status' => 1],
            ['date' => '2026-11-05', 'description' => 'Día del Trabajador Municipal', 'status' => 1],
            ['date' => '2026-12-08', 'description' => 'Inmaculada Concepción', 'status' => 1],
            ['date' => '2026-12-09', 'description' => 'Batalla de Ayacucho', 'status' => 1],
            ['date' => '2026-12-25', 'description' => 'Navidad', 'status' => 1],
        ];

        foreach ($holidays as $holiday) {
            Holiday::updateOrCreate(
                ['date' => $holiday['date']],
                $holiday
            );
        }
    }
}