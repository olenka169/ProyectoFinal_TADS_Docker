<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Province;
use App\Models\District;
use Illuminate\Database\Seeder;

class UbigeoSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Lambayeque' => [
                'Chiclayo' => [
                    'Chiclayo',
                    'Chongoyape',
                    'Eten',
                    'Eten Puerto',
                    'José Leonardo Ortiz',
                    'La Victoria',
                    'Lagunas',
                    'Monsefú',
                    'Nueva Arica',
                    'Oyotún',
                    'Picsi',
                    'Pimentel',
                    'Reque',
                    'Santa Rosa',
                    'Saña',
                    'Cayaltí',
                    'Pátapo',
                    'Pomalca',
                    'Pucalá',
                    'Tumán',
                ],
                'Ferreñafe' => [
                    'Ferreñafe',
                    'Cañaris',
                    'Incahuasi',
                    'Manuel Antonio Mesones Muro',
                    'Pítipo',
                    'Pueblo Nuevo',
                ],
                'Lambayeque' => [
                    'Lambayeque',
                    'Chóchope',
                    'Íllimo',
                    'Jayanca',
                    'Mochumí',
                    'Mórrope',
                    'Motupe',
                    'Olmos',
                    'Pacora',
                    'Salas',
                    'San José',
                    'Túcume',
                ],
            ],
        ];

        foreach ($data as $departmentName => $provinces) {
            $department = Department::updateOrCreate(
                ['name' => $departmentName],
                ['name' => $departmentName]
            );

            foreach ($provinces as $provinceName => $districts) {
                $province = Province::updateOrCreate(
                    [
                        'name' => $provinceName,
                        'department_id' => $department->id,
                    ],
                    [
                        'name' => $provinceName,
                        'department_id' => $department->id,
                    ]
                );

                foreach ($districts as $districtName) {
                    District::updateOrCreate(
                        [
                            'name' => $districtName,
                            'province_id' => $province->id,
                        ],
                        [
                            'name' => $districtName,
                            'province_id' => $province->id,
                        ]
                    );
                }
            }
        }
    }
}