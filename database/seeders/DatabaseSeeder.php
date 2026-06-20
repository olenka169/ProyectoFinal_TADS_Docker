<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\VehicleColor;
use App\Models\VehicleType;
use App\Models\Brand;
use App\Models\BrandModel;
use App\Models\Vehicle;
use App\Models\PersonnelType;
use App\Models\Personnel;
use App\Models\Shift;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Province;
use App\Models\District;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Usuario Administrador
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Administrador TADS',
                'password' => Hash::make('password'),
            ]
        );

        $this->call(UbigeoSeeder::class);
        $this->call(HolidaySeeder::class);

        // 2. Colores de Vehículos
        $colors = [
            ['name' => 'Blanco', 'code' => '#FFFFFF', 'description' => 'Blanco estándar'],
            ['name' => 'Negro', 'code' => '#000000', 'description' => 'Negro obsidiana'],
            ['name' => 'Gris Plata', 'code' => '#C0C0C0', 'description' => 'Gris metalizado'],
            ['name' => 'Rojo', 'code' => '#FF0000', 'description' => 'Rojo brillante'],
            ['name' => 'Azul', 'code' => '#0000FF', 'description' => 'Azul municipal'],
        ];
        foreach ($colors as $color) {
            VehicleColor::create($color);
        }

        // 3. Tipos de Vehículo
        $types = [
            ['name' => 'Camión recolector', 'description' => 'Vehículo principal de recolección de basura'],
            ['name' => 'Compactador', 'description' => 'Vehículo con sistema de compactado de residuos'],
            ['name' => 'Volquete', 'description' => 'Vehículo de carga pesada para escombros'],
            ['name' => 'Camioneta', 'description' => 'Vehículo de supervisión y transporte de personal'],
            ['name' => 'Motofurgón', 'description' => 'Vehículo ligero para recolección en pasajes estrechos'],
            ['name' => 'Cisterna', 'description' => 'Vehículo para transporte de agua y riego de áreas verdes'],
        ];
        foreach ($types as $type) {
            VehicleType::create($type);
        }

        // 4. Marcas y Modelos
        $brands = [
            [
                'name' => 'Toyota',
                'description' => 'Marca japonesa reconocida por su durabilidad',
                'models' => [
                    ['name' => 'Hilux', 'code' => 'TOY-HIL-01', 'description' => 'Camioneta 4x4'],
                    ['name' => 'Dyna', 'code' => 'TOY-DYN-02', 'description' => 'Camión de carga ligera'],
                ]
            ],
            [
                'name' => 'Volvo',
                'description' => 'Fabricante sueco líder en vehículos pesados',
                'models' => [
                    ['name' => 'FH16', 'code' => 'VOL-FH-03', 'description' => 'Camión recolector pesado'],
                    ['name' => 'FMX', 'code' => 'VOL-FMX-04', 'description' => 'Vehículo para construcción'],
                ]
            ],
            [
                'name' => 'Mercedes-Benz',
                'description' => 'Calidad y potencia alemana',
                'models' => [
                    ['name' => 'Atego', 'code' => 'MB-ATE-05', 'description' => 'Camión recolector mediano'],
                    ['name' => 'Actros', 'code' => 'MB-ACT-06', 'description' => 'Vehículo de gran tonelaje'],
                ]
            ],
        ];

        foreach ($brands as $bData) {
            $brand = Brand::updateOrCreate(
                ['name' => $bData['name']],
                ['description' => $bData['description']]
            );

            foreach ($bData['models'] as $mData) {
                $brand->models()->updateOrCreate(
                    ['code' => $mData['code']],
                    [
                        'name' => $mData['name'],
                        'description' => $mData['description']
                    ]
                );
            }
        }

        // 5. Vehículos
        $allModels = BrandModel::all();
        $allTypes = VehicleType::all();
        $allColors = VehicleColor::all();

        $plates = ['V1C-789', 'A5T-456', 'M9B-123', 'X4D-001', 'P2R-555', 'B7Y-888', 'C9K-222', 'Z1X-999', 'F4G-333', 'H8J-111'];

        for ($i = 0; $i < 10; $i++) {
            Vehicle::updateOrCreate(
                ['plate' => $plates[$i]],
                [
                    'code' => 'VEH-' . strtoupper(\Illuminate\Support\Str::random(5)),
                    'brand_model_id' => $allModels->random()->id,
                    'vehicle_type_id' => $allTypes->random()->id,
                    'vehicle_color_id' => $allColors->random()->id,
                    'year' => rand(2015, 2024),
                    'engine_number' => 'ENG-' . rand(100000, 999999),
                    'chassis_number' => 'CHS-' . rand(100000, 999999),
                    'mileage' => rand(1000, 50000),
                    'status' => 'Activo',
                    'passenger_capacity' => rand(2, 4), // Mínimo 2 para que haya al menos 1 ayudante
                ]
            );
        }

        // 6. Tipo de personal
        $personnelTypes = [
            [
                'name' => 'Conductor',
                'description' => 'Personal autorizado para conducir vehículos'
            ],
            [
                'name' => 'Ayudante',
                'description' => 'Personal de apoyo en la recolección'
            ],
        ];

        foreach ($personnelTypes as $type) {
            PersonnelType::updateOrCreate(
                ['name' => $type['name']],
                ['description' => $type['description']]
            );
        }

        // 7. Personal y Contratos Iniciales
        $conductorType = PersonnelType::where('name', 'Conductor')->first();
        $ayudanteType = PersonnelType::where('name', 'Ayudante')->first();

        $personnelData = [
            [
                'dni' => '87654321',
                'type_id' => $ayudanteType->id,
                'names' => 'Pedro Fernando',
                'lastnames' => 'Montenegro Quispe',
                'birthdate' => '1995-09-16',
                'phone' => '987654321',
                'email' => 'pedro.montenegro@gmail.com',
                'address' => 'Av. Grau 456',
                'salary' => 1200.00,
                'contract_type' => 'Permanente'
            ],
            [
                'dni' => '12345678',
                'type_id' => $conductorType->id,
                'names' => 'Juan Alberto',
                'lastnames' => 'Ramos Soto',
                'birthdate' => '1985-05-20',
                'phone' => '955443322',
                'email' => 'juan.ramos@gmail.com',
                'address' => 'Calle Las Flores 123',
                'salary' => 2500.00,
                'contract_type' => 'Nombrado'
            ],
            [
                'dni' => '44556677',
                'type_id' => $conductorType->id,
                'names' => 'Carlos Mario',
                'lastnames' => 'Vargas Llosa',
                'birthdate' => '1990-12-10',
                'phone' => '999888777',
                'email' => 'carlos.vargas@gmail.com',
                'address' => 'Urb. Los Pinos B-12',
                'salary' => 2800.00,
                'contract_type' => 'Temporal'
            ],
            [
                'dni' => '11223344',
                'type_id' => $ayudanteType->id,
                'names' => 'Maria Elena',
                'lastnames' => 'Paz Soldan',
                'birthdate' => '1998-03-25',
                'phone' => '912345678',
                'email' => 'maria.paz@gmail.com',
                'address' => 'Jr. Junin 789',
                'salary' => 1100.00,
                'contract_type' => 'Temporal'
            ],
        ];

        foreach ($personnelData as $p) {
            $personnel = Personnel::updateOrCreate(
                ['dni' => $p['dni']],
                [
                    'personnel_type_id' => $p['type_id'],
                    'names' => $p['names'],
                    'lastnames' => $p['lastnames'],
                    'birthdate' => $p['birthdate'],
                    'phone' => $p['phone'],
                    'email' => $p['email'],
                    'status' => 'Activo',
                    'password' => Hash::make($p['dni']),
                    'address' => $p['address'],
                    'photo_path' => null,
                    'license_number' => $p['type_id'] == $conductorType->id ? 'C' . $p['dni'] : null,
                ]
            );

            $personnel->contracts()->updateOrCreate(
                ['personnel_id' => $personnel->id, 'is_active' => true],
                [
                    'type' => $p['contract_type'],
                    'start_date' => now()->subMonths(rand(1, 12))->format('Y-m-d'),
                    'end_date' => $p['contract_type'] == 'Temporal' ? now()->addMonths(6)->format('Y-m-d') : null,
                    'salary' => $p['salary'],
                    'probation_period' => '3 meses',
                    'is_active' => true
                ]
            );
        }

        // 8. Turnos
        $shifts = [
            [
                'name' => 'Madrugada',
                'description' => 'Turno madrugada',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
            ],
            [
                'name' => 'Mañana',
                'description' => 'Turno matutino',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
            ],
            [
                'name' => 'Tarde',
                'description' => 'Turno vespertino',
                'start_time' => '14:00:00',
                'end_time' => '18:00:00',
            ],
            [
                'name' => 'Noche',
                'description' => 'Turno nocturno',
                'start_time' => '18:00:00',
                'end_time' => '22:00:00',
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::updateOrCreate(
                ['name' => $shift['name']],
                [
                    'description' => $shift['description'],
                    'start_time' => $shift['start_time'],
                    'end_time' => $shift['end_time'],
                ]
            );
        }

        // 9. Asistencias de prueba
        $pedro = Personnel::where('dni', '87654321')->first();
        $juan = Personnel::where('dni', '12345678')->first();

        $shiftManana = Shift::where('name', 'Mañana')->first();

        Attendance::updateOrCreate(
            [
                'personnel_id' => $pedro->id,
                'date' => '2026-06-06',
                'time' => '06:00:00',
                'type' => 'Ingreso',
            ],
            [
                'shift_id' => $shiftManana->id,
                'status' => 'Presente',
                'notes' => 'Ingreso registrado.',
            ]
        );

        Attendance::updateOrCreate(
            [
                'personnel_id' => $pedro->id,
                'date' => '2026-06-06',
                'time' => '14:00:00',
                'type' => 'Salida',
            ],
            [
                'shift_id' => $shiftManana->id,
                'status' => 'Presente',
                'notes' => 'Salida registrada.',
            ]
        );

        Attendance::updateOrCreate(
            [
                'personnel_id' => $juan->id,
                'date' => '2026-06-06',
                'time' => '06:15:00',
                'type' => 'Ingreso',
            ],
            [
                'shift_id' => $shiftManana->id,
                'status' => 'Presente',
                'notes' => 'Ingreso registrado.',
            ]
        );

        // 10. Vacaciones de prueba
        \App\Models\Vacation::create([
            'personnel_id' => $juan->id,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-15',
            'requested_days' => 15,
            'status' => 'Aprobada',
            'notes' => 'Vacaciones de mitad de año aprobadas.',
        ]);

        \App\Models\Vacation::create([
            'personnel_id' => $pedro->id,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-10',
            'requested_days' => 10,
            'status' => 'Pendiente',
            'notes' => 'Solicitud pendiente.',
        ]);

        // 11. Creación de zonas de prueba
        $department = Department::where('name', 'Lambayeque')->first();
        $province = Province::where('name', 'Chiclayo')
            ->where('department_id', $department->id)
            ->first();
        $district = District::where('name', 'José Leonardo Ortiz')
            ->where('province_id', $province->id)
            ->first();

        if ($department && $province && $district) {
            $zones = [
                ['name' => 'Centro', 'description' => 'Sector centro de JLO', 'coordinates' => [['lat' => -6.756392, 'lng' => -79.833667], ['lat' => -6.756401, 'lng' => -79.838865], ['lat' => -6.762681, 'lng' => -79.841187], ['lat' => -6.763225, 'lng' => -79.834664]]],
                ['name' => 'Norte', 'description' => 'Sector norte de JLO', 'coordinates' => [['lat' => -6.750900, 'lng' => -79.835900], ['lat' => -6.750900, 'lng' => -79.829900], ['lat' => -6.755500, 'lng' => -79.829900], ['lat' => -6.755500, 'lng' => -79.835900]]],
                ['name' => 'Sur', 'description' => 'Sector sur de JLO', 'coordinates' => [['lat' => -6.765000, 'lng' => -79.835900], ['lat' => -6.765000, 'lng' => -79.829900], ['lat' => -6.770000, 'lng' => -79.829900], ['lat' => -6.770000, 'lng' => -79.835900]]],
                ['name' => 'Oeste', 'description' => 'Sector oeste de JLO', 'coordinates' => [['lat' => -6.756392, 'lng' => -79.842000], ['lat' => -6.756401, 'lng' => -79.847000], ['lat' => -6.762681, 'lng' => -79.847000], ['lat' => -6.763225, 'lng' => -79.842000]]],
            ];

            foreach ($zones as $zone) {
                Zone::updateOrCreate(
                    ['name' => $zone['name']],
                    [
                        'department_id' => $department->id,
                        'province_id' => $province->id,
                        'district_id' => $district->id,
                        'description' => $zone['description'],
                        'status' => true,
                        'coordinates' => $zone['coordinates'],
                    ]
                );
            }
        }

        // 12. Llamar a los nuevos seeders
        $this->call(PersonnelSeeder::class);
        $this->call(PersonnelGroupSeeder::class);
        $this->call(ScheduleSeeder::class);
    }
}
