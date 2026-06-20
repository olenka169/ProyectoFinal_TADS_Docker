<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Shift;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $attendances = Attendance::with(['personnel', 'shift'])
                ->leftJoin('personnels', 'attendances.personnel_id', '=', 'personnels.id')
                ->leftJoin('shifts', 'attendances.shift_id', '=', 'shifts.id')
                ->select('attendances.*');

            $hasDateFilter = $request->filled('start_date') || $request->filled('end_date');
            $hasPersonnelSearch = $request->filled('personnel_search');

            if ($hasDateFilter) {

                if ($request->filled('start_date')) {
                    $attendances->whereDate('date', '>=', $request->start_date);
                }

                if ($request->filled('end_date')) {
                    $attendances->whereDate('date', '<=', $request->end_date);
                }

            } elseif (!$hasPersonnelSearch) {

                $attendances->whereDate('date', now()->format('Y-m-d'));
            }

            if ($request->filled('personnel_search')) {
                $search = $request->personnel_search;

                $attendances->whereHas('personnel', function ($query) use ($search) {
                    $query->where('dni', 'like', "%{$search}%")
                        ->orWhere('names', 'like', "%{$search}%")
                        ->orWhere('lastnames', 'like', "%{$search}%");
                });
            }

            return DataTables::of($attendances)

                ->addColumn('personnel_dni', function ($attendance) {
                    return $attendance->personnel->dni ?? 'N/A';
                })

                ->addColumn('personnel_name', function ($attendance) {
                    return $attendance->personnel
                        ? $attendance->personnel->names . ' ' . $attendance->personnel->lastnames
                        : 'Sin personal';
                })

                ->addColumn('date', function ($attendance) {
                    return $attendance->date->format('d/m/Y');
                })

                ->addColumn('time', function ($attendance) {
                    return \Carbon\Carbon::parse($attendance->time)->format('H:i');
                })

                ->addColumn('shift_name', function ($attendance) {
                    return $attendance->shift ? $attendance->shift->name : 'Sin turno';
                })

                ->addColumn('type_badge', function ($attendance) {
                    if ($attendance->type == 'Ingreso') {
                        return '<span class="badge badge-success badge-custom">Ingreso</span>';
                    }

                    return '<span class="badge badge-info badge-custom">Salida</span>';
                })

                ->addColumn('status_badge', function ($attendance) {
                    if ($attendance->status == 'Presente') {
                        return '<span class="badge badge-success badge-custom">Presente</span>';
                    }

                    return '<span class="badge badge-danger badge-custom">Ausente</span>';
                })

                ->addColumn('notes', function ($attendance) {
                    return $attendance->notes ?: '—';
                })

                ->addColumn('actions', function ($attendance) {
                    return '
                        <button class="btn btn-sm btn-warning btn-editar" id="' . $attendance->id . '">
                            <i class="fas fa-pen"></i>
                        </button>

                        <button type="button"
                            class="btn btn-sm btn-danger btn-delete"
                            data-url="' . route('admin.attendances.destroy', $attendance->id) . '">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    ';
                })

                ->rawColumns([
                    'type_badge',
                    'status_badge',
                    'actions'
                ])

                ->make(true);
        }

        return view('admin.attendances.index');
    }

    public function create()
    {
        $personnels = Personnel::whereHas('contracts', function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('lastnames')
            ->orderBy('names')
            ->get();

        $shifts = Shift::all();

        return view('admin.attendances.create', compact('personnels', 'shifts'));
    }

    public function store(Request $request)
    {
        try {

            $request->validate([
                'personnel_id' => 'required|exists:personnels,id',
                'date' => 'required|date',
                'time' => 'required',
                'status' => 'required|in:Presente,Ausente',
                'notes' => 'nullable',
            ], [
                'personnel_id.required' => 'Debe seleccionar al personal.',
                'personnel_id.exists' => 'El personal seleccionado no es válido.',
                'date.required' => 'La fecha es obligatoria.',
                'time.required' => 'La hora es obligatoria.',
                'status.required' => 'Debe seleccionar el estado de asistencia.',
            ]);

            // Validar que el personal tenga un contrato activo
            $hasActiveContract = \App\Models\Contract::where('personnel_id', $request->personnel_id)
                ->where('is_active', true)
                ->exists();

            if (!$hasActiveContract) {
                return response()->json([
                    'message' => 'No se puede registrar asistencia. El personal seleccionado no tiene un contrato activo.'
                ], 422);
            }

            $attendanceStatus = $this->getAttendanceStatus($request->personnel_id, $request->date);

            $type = $attendanceStatus['next_type'];

            $shift = $this->determineShiftByTime($request->time);

            Attendance::create([
                'personnel_id' => $request->personnel_id,
                'date' => $request->date,
                'time' => $request->time,
                'shift_id' => $shift ? $shift->id : null,
                'type' => $type,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'message' => 'Asistencia registrada correctamente'
            ], 200);

        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Error: ' . $th->getMessage()
            ], 500);
        }
    }

    public function edit(string $id)
    {
        $attendance = Attendance::findOrFail($id);

        $personnels = Personnel::whereHas('contracts', function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('lastnames')
            ->orderBy('names')
            ->get();

        $shifts = Shift::all();

        return view('admin.attendances.edit', compact('attendance', 'personnels', 'shifts'));
    }

    

    public function update(Request $request, string $id)
    {
        try {

            $attendance = Attendance::findOrFail($id);

            $request->validate([
                'personnel_id' => 'required|exists:personnels,id',
                'date' => 'required|date',
                'time' => 'required',
                'status' => 'required|in:Presente,Ausente',
                'notes' => 'nullable',
            ]);

            $shift = $this->determineShiftByTime($request->time);

            $attendance->update([
                'personnel_id' => $request->personnel_id,
                'date' => $request->date,
                'time' => $request->time,
                'shift_id' => $shift ? $shift->id : null,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'message' => 'Asistencia actualizada correctamente'
            ], 200);

        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Error: ' . $th->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {

            $attendance = Attendance::findOrFail($id);
            $attendance->delete();

            return response()->json([
                'message' => 'Asistencia eliminada correctamente'
            ], 200);

        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Error en la eliminación: ' . $th->getMessage()
            ], 500);
        }
    }

    private function getAttendanceStatus($personnelId, $date)
    {
        $records = Attendance::where('personnel_id', $personnelId)
            ->where('date', $date)
            ->orderBy('time')
            ->get();

        $lastAttendance = Attendance::where('personnel_id', $personnelId)
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->first();

        if (!$lastAttendance || $lastAttendance->type == 'Salida') {
            return [
                'records' => $records,
                'next_type' => 'Ingreso',
                'can_register' => true,
                'message' => 'Corresponde registrar el INGRESO del personal.'
            ];
        }

        return [
            'records' => $records,
            'next_type' => 'Salida',
            'can_register' => true,
            'message' => 'Existe un ingreso pendiente. Corresponde registrar la SALIDA del personal.'
        ];
    }

    public function personnelDayInfo(Request $request)
    {
        $request->validate([
            'personnel_id' => 'required|exists:personnels,id',
            'date' => 'required|date',
        ]);

        $personnel = Personnel::findOrFail($request->personnel_id);

        $attendanceStatus = $this->getAttendanceStatus(
            $request->personnel_id,
            $request->date
        );

        $records = $attendanceStatus['records'];
        $nextType = $attendanceStatus['next_type'];
        $canRegister = $attendanceStatus['can_register'];
        $message = $attendanceStatus['message'];

        return response()->json([
            'personnel' => [
                'dni' => $personnel->dni,
                'names' => $personnel->names,
                'lastnames' => $personnel->lastnames,
                'email' => $personnel->email,
                'phone' => $personnel->phone,
            ],
            'records' => $records->map(function ($record) {
                return [
                    'type' => $record->type,
                    'time' => \Carbon\Carbon::parse($record->time)->format('H:i'),
                    'status' => $record->status,
                ];
            }),
            'next_type' => $nextType,
            'can_register' => $canRegister,
            'message' => $message,
        ]);
    }

    private function determineShiftByTime($time)
    {
        $time = \Carbon\Carbon::parse($time)->format('H:i:s');

        $shifts = Shift::all();

        foreach ($shifts as $shift) {
            $start = \Carbon\Carbon::parse($shift->start_time)->format('H:i:s');
            $end = \Carbon\Carbon::parse($shift->end_time)->format('H:i:s');

            if ($start <= $end) {
                if ($time >= $start && $time < $end) {
                    return $shift;
                }
            } else {
                if ($time >= $start || $time < $end) {
                    return $shift;
                }
            }
        }

        return null;
    }
}