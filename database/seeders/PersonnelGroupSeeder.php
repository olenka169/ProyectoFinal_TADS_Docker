<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Personnel;
use App\Models\PersonnelType;
use App\Models\Shift;
use App\Models\Zone;
use App\Models\Vehicle;
use App\Models\PersonnelGroup;
use App\Models\PersonnelGroupWorkday;
use App\Models\PersonnelGroupDetail;

class PersonnelGroupSeeder extends Seeder
{
    public function run(): void
    {
        $conductorType = PersonnelType::where('name', 'Conductor')->first();
        $ayudanteType = PersonnelType::where('name', 'Ayudante')->first();
        
        $zones = Zone::all();
        $shifts = Shift::all();
        $vehicles = Vehicle::all();
        
        $conductores = Personnel::where('personnel_type_id', $conductorType->id)->get();
        $ayudantes = Personnel::where('personnel_type_id', $ayudanteType->id)->get();

        $groupData = [
            [
                'name' => 'Grupo B2',
                'zone' => 'Norte',
                'shift' => 'Mañana',
                'driver_index' => 1, // Roberto Sánchez
                'helpers' => [0, 1, 2], // Jorge, Raul, Fernando
                'days' => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
            ],
            [
                'name' => 'Grupo C3',
                'zone' => 'Sur',
                'shift' => 'Tarde',
                'driver_index' => 2, // Luis Alberto Perez
                'helpers' => [3, 4, 5], // Andres, Sebastian, Kevin
                'days' => ['Lunes', 'Miércoles', 'Viernes']
            ],
            [
                'name' => 'Grupo D4',
                'zone' => 'Oeste',
                'shift' => 'Noche',
                'driver_index' => 3, // Miguel Angel Rodriguez
                'helpers' => [6, 7, 0], // Diego, Mateo, Jorge
                'days' => ['Martes', 'Jueves', 'Sábado']
            ],
            [
                'name' => 'Grupo E5',
                'zone' => 'Centro',
                'shift' => 'Noche',
                'driver_index' => 0, // Juan Alberto Ramos
                'helpers' => [1, 3, 5], // Raul, Andres, Kevin
                'days' => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']
            ],
        ];

        foreach ($groupData as $index => $data) {
            $zone = Zone::where('name', $data['name'] == 'Grupo B2 - Sector Norte' ? 'Norte' : ($data['name'] == 'Grupo C3 - Sector Sur' ? 'Sur' : ($data['name'] == 'Grupo D4 - Sector Oeste' ? 'Oeste' : 'Centro')))->first() ?? $zones->random();
            $shift = Shift::where('name', $data['shift'])->first() ?? $shifts->random();
            $vehicle = $vehicles->get($index + 1) ?? $vehicles->random();
            $driver = $conductores->get($data['driver_index']) ?? $conductores->random();

            $group = PersonnelGroup::updateOrCreate(
                ['name' => $data['name']],
                [
                    'zone_id' => $zone->id,
                    'shift_id' => $shift->id,
                    'vehicle_id' => $vehicle->id,
                    'driver_id' => $driver->id,
                    'status' => true
                ]
            );

            // Dias de trabajo
            foreach ($data['days'] as $day) {
                PersonnelGroupWorkday::updateOrCreate([
                    'personnel_group_id' => $group->id,
                    'day' => $day
                ]);
            }

            // Integrantes (Ayudantes)
            foreach ($data['helpers'] as $hIndex) {
                $helper = $ayudantes->get($hIndex);
                if ($helper) {
                    PersonnelGroupDetail::updateOrCreate([
                        'personnel_group_id' => $group->id,
                        'personnel_id' => $helper->id
                    ]);
                }
            }
        }
    }
}
