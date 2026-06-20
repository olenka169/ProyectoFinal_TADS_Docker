<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Personnel;
use App\Models\PersonnelType;
use Illuminate\Support\Facades\Hash;

class PersonnelSeeder extends Seeder
{
    public function run(): void
    {
        $conductorType = PersonnelType::where('name', 'Conductor')->first();
        $ayudanteType = PersonnelType::where('name', 'Ayudante')->first();

        $extraPersonnel = [
            // Conductores
            ['dni' => '22334455', 'names' => 'Ricardo', 'lastnames' => 'Gomez Tapia', 'type' => $conductorType->id, 'contract' => 'Nombrado', 'salary' => 2600],
            ['dni' => '33445566', 'names' => 'Roberto', 'lastnames' => 'Sánchez Diaz', 'type' => $conductorType->id, 'contract' => 'Permanente', 'salary' => 2700],
            ['dni' => '44556688', 'names' => 'Luis Alberto', 'lastnames' => 'Perez Prado', 'type' => $conductorType->id, 'contract' => 'Nombrado', 'salary' => 2550],
            ['dni' => '55667788', 'names' => 'Miguel Angel', 'lastnames' => 'Rodriguez Franco', 'type' => $conductorType->id, 'contract' => 'Permanente', 'salary' => 2650],
            
            // Ayudantes
            ['dni' => '66778899', 'names' => 'Jorge', 'lastnames' => 'Castro Ruiz', 'type' => $ayudanteType->id, 'contract' => 'Permanente', 'salary' => 1300],
            ['dni' => '77889900', 'names' => 'Raul', 'lastnames' => 'Mendoza Lima', 'type' => $ayudanteType->id, 'contract' => 'Nombrado', 'salary' => 1350],
            ['dni' => '88990011', 'names' => 'Fernando', 'lastnames' => 'Soto Mayor', 'type' => $ayudanteType->id, 'contract' => 'Permanente', 'salary' => 1250],
            ['dni' => '99001122', 'names' => 'Andres', 'lastnames' => 'Cueva Bravo', 'type' => $ayudanteType->id, 'contract' => 'Nombrado', 'salary' => 1400],
            ['dni' => '10112233', 'names' => 'Sebastian', 'lastnames' => 'Vela Ortiz', 'type' => $ayudanteType->id, 'contract' => 'Permanente', 'salary' => 1200],
            ['dni' => '20223344', 'names' => 'Kevin', 'lastnames' => 'Rojas Peña', 'type' => $ayudanteType->id, 'contract' => 'Temporal', 'salary' => 1150],
            ['dni' => '30334455', 'names' => 'Diego', 'lastnames' => 'Torres Luna', 'type' => $ayudanteType->id, 'contract' => 'Temporal', 'salary' => 1100],
            ['dni' => '40445566', 'names' => 'Mateo', 'lastnames' => 'Silva Cruz', 'type' => $ayudanteType->id, 'contract' => 'Temporal', 'salary' => 1100],
        ];

        foreach ($extraPersonnel as $p) {
            $personnel = Personnel::updateOrCreate(
                ['dni' => $p['dni']],
                [
                    'personnel_type_id' => $p['type'],
                    'names' => $p['names'],
                    'lastnames' => $p['lastnames'],
                    'birthdate' => now()->subYears(rand(20, 50))->format('Y-m-d'),
                    'phone' => '9' . rand(10000000, 99999999),
                    'email' => strtolower($p['names'] . '.' . explode(' ', $p['lastnames'])[0] . '@gmail.com'),
                    'status' => 'Activo',
                    'password' => Hash::make($p['dni']),
                    'address' => 'Dirección de prueba ' . $p['dni'],
                    'license_number' => $p['type'] == $conductorType->id ? 'C' . $p['dni'] : null,
                ]
            );

            $personnel->contracts()->updateOrCreate(
                ['personnel_id' => $personnel->id, 'is_active' => true],
                [
                    'type' => $p['contract'],
                    'start_date' => now()->subYears(rand(1, 5))->format('Y-m-d'),
                    'end_date' => $p['contract'] == 'Temporal' ? now()->addMonths(6)->format('Y-m-d') : null,
                    'salary' => $p['salary'],
                    'probation_period' => '3 meses',
                    'is_active' => true
                ]
            );
        }
    }
}
