<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\ScheduleDaily;
use App\Models\PersonnelGroup;
use App\Models\Personnel;
use App\Models\Shift;
use App\Models\Vehicle;
use App\Models\Zone;
use App\Models\Holiday;
use App\Models\Vacation;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $schedules = Schedule::with(['personnelGroup', 'zone', 'shift', 'vehicle', 'driver', 'helpers'])
                ->select('schedules.*');

            return DataTables::of($schedules)
                ->addColumn('group_name', fn($s) => $s->personnelGroup?->name)
                ->addColumn('zone_name', fn($s) => $s->zone?->name)
                ->addColumn('shift_name', fn($s) => $s->shift?->name)
                ->addColumn('vehicle_plate', fn($s) => $s->vehicle?->plate)
                ->addColumn('driver_name', fn($s) => $s->driver?->names . ' ' . $s->driver?->lastnames)
                ->addColumn('helpers_names', function($s) {
                    return $s->helpers->map(fn($h) => $h->names . ' ' . $h->lastnames)->implode('<br>');
                })
                ->addColumn('date_range', fn($s) => $s->start_date->format('d/m/Y') . ' - ' . $s->end_date->format('d/m/Y'))
                ->addColumn('status_badge', function($s) {
                    $badges = [
                        'scheduled' => '<span class="badge badge-primary">Programada</span>',
                        'in_progress' => '<span class="badge badge-info">En Curso</span>',
                        'completed' => '<span class="badge badge-success">Finalizada</span>',
                        'cancelled' => '<span class="badge badge-danger">Cancelada</span>',
                    ];
                    return $badges[$s->status] ?? $s->status;
                })
                ->addColumn('actions', function($s) {
                    return '
                        <div class="btn-group">
                            <button class="btn btn-sm btn-info btn-daily" data-id="'.$s->id.'" title="Ver Detalle Diario"><i class="fas fa-calendar-day"></i></button>
                            <button class="btn btn-sm btn-warning btn-edit" data-id="'.$s->id.'" title="Modificar"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-danger btn-delete" data-id="'.$s->id.'" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </div>
                    ';
                })
                ->rawColumns(['status_badge', 'actions', 'helpers_names'])
                ->make(true);
        }

        $groups = PersonnelGroup::where('status', true)->get();
        $zones = Zone::all();
        $shifts = Shift::all();
        $vehicles = Vehicle::all();
        $personnels = Personnel::all();

        return view('admin.schedules.index', compact('groups', 'zones', 'shifts', 'vehicles', 'personnels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'personnel_group_id' => 'required|exists:personnel_groups,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'zone_id' => 'required',
            'shift_id' => 'required',
            'vehicle_id' => 'required',
            'driver_id' => 'required',
        ]);

        $availability = $this->checkAvailability($request);
        if (!$availability['valid']) {
            return response()->json([
                'success' => false,
                'message' => 'Existen conflictos bloqueantes en la programación:',
                'errors' => $availability['errors']
            ], 422);
        }

        return DB::transaction(function() use ($request) {
            $schedule = Schedule::create($request->all());

            if ($request->has('helper_ids')) {
                $schedule->helpers()->attach($request->helper_ids);
            }

            if ($request->has('workdays')) {
                foreach ($request->workdays as $day) {
                    $schedule->workdays()->create(['day' => $day]);
                }
            }

            $this->generateDailyRecords($schedule);

            return response()->json(['success' => true]);
        });
    }

    public function previewMass(Request $request)
    {
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $shiftId = $request->shift_id;

        $holidays = Holiday::where('status', true)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $groups = PersonnelGroup::where('status', true)
            ->when($shiftId, fn($q) => $q->where('shift_id', $shiftId))
            ->with(['zone', 'shift', 'vehicle', 'driver', 'helpers.personnel', 'workdays'])
            ->get();

        $preview = [];
        $personnels = Personnel::all(); // Para el selector de cambios

        foreach ($groups as $group) {
            $tempRequest = new Request([
                'personnel_group_id' => $group->id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'zone_id' => $group->zone_id,
                'shift_id' => $group->shift_id,
                'vehicle_id' => $group->vehicle_id,
                'driver_id' => $group->driver_id,
                'helper_ids' => $group->helpers->pluck('personnel_id')->toArray(),
            ]);

            $availability = $this->checkAvailability($tempRequest);
            
            $preview[] = [
                'group' => $group,
                'availability' => $availability
            ];
        }

        return response()->json([
            'holidays' => $holidays,
            'preview' => $preview,
            'personnels' => $personnels
        ]);
    }

    public function storeMass(Request $request)
    {
        $data = $request->groups;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        return DB::transaction(function() use ($data, $startDate, $endDate) {
            foreach ($data as $item) {
                $schedule = Schedule::create([
                    'personnel_group_id' => $item['group_id'],
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'zone_id' => $item['zone_id'],
                    'shift_id' => $item['shift_id'],
                    'vehicle_id' => $item['vehicle_id'],
                    'driver_id' => $item['driver_id'],
                    'status' => 'scheduled'
                ]);

                if (isset($item['helper_ids'])) {
                    $schedule->helpers()->attach($item['helper_ids']);
                }

                $this->generateDailyRecords($schedule);
            }

            return response()->json(['success' => true]);
        });
    }

    private function generateDailyRecords(Schedule $schedule)
    {
        $startDate = $schedule->start_date;
        $endDate = $schedule->end_date;
        $currentDate = $startDate->copy();

        $holidays = Holiday::where('status', true)
            ->whereBetween('date', [$startDate, $endDate])
            ->pluck('date')
            ->map(fn($d) => $d->format('Y-m-d'))
            ->toArray();

        while ($currentDate->lte($endDate)) {
            if (!in_array($currentDate->format('Y-m-d'), $holidays)) {
                $daily = $schedule->dailies()->create([
                    'date' => $currentDate->format('Y-m-d'),
                    'shift_id' => $schedule->shift_id,
                    'vehicle_id' => $schedule->vehicle_id,
                    'driver_id' => $schedule->driver_id,
                    'status' => 'pendiente'
                ]);

                $daily->helpers()->attach($schedule->helpers->pluck('id'));
            }
            $currentDate->addDay();
        }
    }

    public function show($id)
    {
        $schedule = Schedule::with(['helpers', 'workdays', 'dailies.shift', 'dailies.vehicle', 'dailies.driver', 'dailies.helpers'])->findOrFail($id);
        return response()->json($schedule);
    }

    public function edit($id)
    {
        $schedule = Schedule::with(['helpers', 'workdays', 'shift', 'vehicle', 'driver'])->findOrFail($id);
        
        $eligibleDrivers = $this->getEligiblePersonnelByRole('Conductor');
        $eligibleHelpers = $this->getEligiblePersonnelByRole('Ayudante');
        
        return response()->json([
            'schedule' => $schedule,
            'current_shift_name' => $schedule->shift?->name,
            'current_vehicle_plate' => $schedule->vehicle?->plate,
            'eligibleDrivers' => $eligibleDrivers,
            'eligibleHelpers' => $eligibleHelpers,
            'current_driver' => $schedule->driver,
            'current_helpers' => $schedule->helpers
        ]);
    }

    private function getEligiblePersonnelByRole($roleName)
    {
        return Personnel::whereHas('type', function($q) use ($roleName) {
            $q->where('name', $roleName);
        })
        ->whereHas('contracts', function($q) {
            $q->whereIn('type', ['Permanente', 'Nombrado'])->where('is_active', true);
        })
        ->whereHas('attendances', function($q) {
            $q->whereDate('date', now()->toDateString());
        })
        ->whereDoesntHave('driverGroups') // No es conductor titular en ningún grupo
        ->whereDoesntHave('helperGroups') // No es ayudante en ningún grupo
        ->get();
    }

    public function update(Request $request, $id)
    {
        $schedule = Schedule::findOrFail($id);
        
        $request->validate([
            'shift_id' => 'required',
            'vehicle_id' => 'required',
            'driver_id' => 'required',
            'reason' => 'required'
        ]);

        return DB::transaction(function() use ($request, $schedule) {
            $schedule->update([
                'shift_id' => $request->shift_id,
                'vehicle_id' => $request->vehicle_id,
                'driver_id' => $request->driver_id,
                'status' => 'reprogramado',
                'notes' => $request->reason
            ]);

            if ($request->has('helper_ids')) {
                $schedule->helpers()->sync($request->helper_ids);
            }

            // Al actualizar a nivel general, marcamos todos los días 'pendiente' futuros con la nueva info
            $schedule->dailies()->where('status', 'pendiente')->where('date', '>=', now()->toDateString())->update([
                'shift_id' => $request->shift_id,
                'vehicle_id' => $request->vehicle_id,
                'driver_id' => $request->driver_id,
                'status' => 'reprogramado'
            ]);

            return response()->json(['success' => true]);
        });
    }

    public function updateDailyStatus(Request $request, $id)
    {
        $daily = ScheduleDaily::findOrFail($id);
        $daily->update(['status' => $request->status]);
        
        return response()->json(['success' => true]);
    }

    public function updateDailyRecord(Request $request, $id)
    {
        $daily = ScheduleDaily::findOrFail($id);
        $daily->update($request->only(['shift_id', 'vehicle_id', 'driver_id', 'notes']));
        
        if ($request->has('helper_ids')) {
            $daily->helpers()->sync($request->helper_ids);
        }

        return response()->json(['success' => true]);
    }

    private function checkAvailability(Request $request, $excludeId = null)
    {
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $groupId = $request->personnel_group_id;
        $driverId = $request->driver_id;
        $helperIds = $request->helper_ids ?? [];
        $vehicleId = $request->vehicle_id;

        $errors = [];
        $warnings = [];

        $holidays = Holiday::where('status', true)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
        
        foreach ($holidays as $holiday) {
            $warnings[] = "El periodo incluye un feriado: " . $holiday->description . " (" . $holiday->date->format('d/m/Y') . ").";
        }

        $allPersonnelIds = array_filter(array_unique(array_merge([$driverId], $helperIds)));
        foreach ($allPersonnelIds as $pId) {
            $personnel = Personnel::find($pId);
            if (!$personnel) continue;

            $vacations = Vacation::where('personnel_id', $pId)
                ->where('status', 'Aprobada')
                ->where(function($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function($sub) use ($startDate, $endDate) {
                          $sub->where('start_date', '<=', $startDate)
                              ->where('end_date', '>=', $endDate);
                      });
                })->first();

            if ($vacations) {
                $errors[] = "El personal {$personnel->names} {$personnel->lastnames} tiene vacaciones aprobadas.";
            }
        }

        $existingSchedules = Schedule::whereIn('status', ['scheduled', 'in_progress'])
            ->when($excludeId, function($q) use ($excludeId) {
                $q->where('id', '!=', $excludeId);
            })
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function($sub) use ($startDate, $endDate) {
                      $sub->where('start_date', '<=', $startDate)
                          ->where('end_date', '>=', $endDate);
                      });
            })->get();

        foreach ($existingSchedules as $es) {
            if ($es->personnel_group_id == $groupId && $es->shift_id == $request->shift_id) {
                $errors[] = "El Grupo de Personal ya tiene una programación activa en el turno seleccionado durante este periodo.";
            }
            if ($es->zone_id == $request->zone_id && $es->shift_id == $request->shift_id) {
                $errors[] = "La Zona y el Turno ya se encuentran ocupados por otra programación activa.";
            }
            if ($es->driver_id == $driverId) {
                $errors[] = "El conductor ya está asignado.";
            }
            if ($es->vehicle_id == $vehicleId) {
                $errors[] = "El vehículo ya está asignado.";
            }
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    public function validateAvailability(Request $request)
    {
        $result = $this->checkAvailability($request, $request->id);
        return response()->json($result);
    }

    public function destroy($id)
    {
        try {
            $schedule = Schedule::findOrFail($id);
            $schedule->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
