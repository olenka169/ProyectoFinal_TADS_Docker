<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Vacation;
use App\Models\Personnel;
use App\Models\Contract;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class VacationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $vacations = Vacation::with('personnel')
                ->leftJoin('personnels', 'vacations.personnel_id', '=', 'personnels.id')
                ->select('vacations.*')
                ->orderBy('vacations.start_date', 'desc');

            return DataTables::of($vacations)
                ->addColumn('personnel_name', function ($vacation) {
                    return $vacation->personnel->names . ' ' . $vacation->personnel->lastnames;
                })
                ->addColumn('personnel_dni', function ($vacation) {
                    return $vacation->personnel->dni;
                })
                ->editColumn('start_date', function ($vacation) {
                    return $vacation->start_date->format('d/m/Y');
                })
                ->editColumn('end_date', function ($vacation) {
                    return $vacation->end_date->format('d/m/Y');
                })
                ->addColumn('status_badge', function ($vacation) {
                    $badges = [
                        'Pendiente' => 'warning',
                        'Aprobada' => 'success',
                        'Rechazada' => 'danger'
                    ];
                    $color = $badges[$vacation->status] ?? 'secondary';
                    return '<span class="badge badge-' . $color . ' badge-custom">' . $vacation->status . '</span>';
                })
                ->addColumn('actions', function ($vacation) {
                    $btns = '<div class="btn-group">';
                    $btns .= '<button type="button" class="btn btn-sm btn-info btn-ver" data-id="' . $vacation->id . '"><i class="fas fa-eye"></i></button>';
                    
                    if ($vacation->status == 'Pendiente') {
                        $btns .= '<button type="button" class="btn btn-sm btn-warning btn-editar" data-id="' . $vacation->id . '"><i class="fas fa-pen"></i></button>';
                        $btns .= '<button type="button" class="btn btn-sm btn-success btn-approve" data-id="' . $vacation->id . '"><i class="fas fa-check"></i></button>';
                        $btns .= '<button type="button" class="btn btn-sm btn-danger btn-reject" data-id="' . $vacation->id . '"><i class="fas fa-times"></i></button>';
                    }
                    
                    $btns .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-url="' . route('admin.vacations.destroy', $vacation->id) . '"><i class="fas fa-trash-alt"></i></button>';
                    $btns .= '</div>';
                    return $btns;
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        }

        return view('admin.vacations.index');
    }

    public function create()
    {
        // Solo personal con contrato activo y tipo Permanente o Nombrado
        $personnels = Personnel::whereHas('contracts', function ($query) {
            $query->where('is_active', true)
                  ->whereIn('type', ['Permanente', 'Nombrado']);
        })->get();

        return view('admin.vacations.create', compact('personnels'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'personnel_id' => 'required|exists:personnels,id',
                'start_date' => 'required|date',
                'requested_days' => 'required|integer|min:1',
                'notes' => 'nullable|string'
            ]);

            $personnelId = $request->personnel_id;
            $startDate = Carbon::parse($request->start_date);
            $requestedDays = $request->requested_days;
            
            // Calcular fecha fin automáticamente (excluyendo fines de semana si fuera el caso, pero el PDF no lo especifica, así que sumamos días corridos - 1)
            // Generalmente vacaciones son días calendario.
            $endDate = $startDate->copy()->addDays($requestedDays - 1);

            // 1. Validar tipo de contrato
            $activeContract = Contract::where('personnel_id', $personnelId)
                ->where('is_active', true)
                ->first();

            if (!$activeContract || !in_array($activeContract->type, ['Permanente', 'Nombrado'])) {
                return response()->json([
                    'message' => 'Solo el personal con contrato Permanente o Nombrado puede solicitar vacaciones.'
                ], 422);
            }

            // 2. Validar días disponibles (30 al año)
            $year = $startDate->year;
            $usedDays = Vacation::getUsedDays($personnelId, $year);
            if (($usedDays + $requestedDays) > 30) {
                $available = 30 - $usedDays;
                return response()->json([
                    'message' => "El personal solo tiene {$available} días disponibles para el año {$year}."
                ], 422);
            }

            // 3. Validar solapamiento de fechas (Pendientes o Aprobadas)
            $overlap = Vacation::where('personnel_id', $personnelId)
                ->whereIn('status', ['Pendiente', 'Aprobada'])
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('start_date', '<=', $startDate)
                                ->where('end_date', '>=', $endDate);
                        });
                })
                ->exists();

            if ($overlap) {
                return response()->json([
                    'message' => 'Las fechas seleccionadas coinciden con otra solicitud de vacaciones pendiente o aprobada.'
                ], 422);
            }

            Vacation::create([
                'personnel_id' => $personnelId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'requested_days' => $requestedDays,
                'status' => 'Pendiente',
                'notes' => $request->notes
            ]);

            return response()->json(['message' => 'Solicitud de vacaciones registrada correctamente'], 200);

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $vacation = Vacation::with('personnel')->findOrFail($id);
        $availableDays = Vacation::getAvailableDays($vacation->personnel_id, $vacation->start_date->year);
        
        return view('admin.vacations.show', compact('vacation', 'availableDays'));
    }

    public function edit($id)
    {
        $vacation = Vacation::findOrFail($id);
        if ($vacation->status != 'Pendiente') {
            return redirect()->route('admin.vacations.index')->with('error', 'Solo se pueden editar solicitudes pendientes.');
        }

        $personnels = Personnel::whereHas('contracts', function ($query) {
            $query->where('is_active', true)
                  ->whereIn('type', ['Permanente', 'Nombrado']);
        })->get();

        return view('admin.vacations.edit', compact('vacation', 'personnels'));
    }

    public function update(Request $request, $id)
    {
        try {
            $vacation = Vacation::findOrFail($id);
            if ($vacation->status != 'Pendiente') {
                return response()->json(['message' => 'Solo se pueden editar solicitudes pendientes.'], 422);
            }

            $request->validate([
                'personnel_id' => 'required|exists:personnels,id',
                'start_date' => 'required|date',
                'requested_days' => 'required|integer|min:1',
                'notes' => 'nullable|string'
            ]);

            $personnelId = $request->personnel_id;
            $startDate = Carbon::parse($request->start_date);
            $requestedDays = $request->requested_days;
            $endDate = $startDate->copy()->addDays($requestedDays - 1);

            // Validaciones similares al store (excluyendo la solicitud actual)
            $year = $startDate->year;
            $usedDays = Vacation::where('personnel_id', $personnelId)
                ->where('status', 'Aprobada')
                ->whereYear('start_date', $year)
                ->where('id', '!=', $id)
                ->sum('requested_days');

            if (($usedDays + $requestedDays) > 30) {
                $available = 30 - $usedDays;
                return response()->json(['message' => "Días insuficientes. Disponibles: {$available}."], 422);
            }

            $overlap = Vacation::where('personnel_id', $personnelId)
                ->whereIn('status', ['Pendiente', 'Aprobada'])
                ->where('id', '!=', $id)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('start_date', '<=', $startDate)
                                ->where('end_date', '>=', $endDate);
                        });
                })
                ->exists();

            if ($overlap) {
                return response()->json(['message' => 'Cruce de fechas con otra solicitud.'], 422);
            }

            $vacation->update([
                'personnel_id' => $personnelId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'requested_days' => $requestedDays,
                'notes' => $request->notes
            ]);

            return response()->json(['message' => 'Solicitud actualizada correctamente'], 200);

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function approve(Request $request, $id)
    {
        try {
            $vacation = Vacation::findOrFail($id);
            if ($vacation->status != 'Pendiente') {
                return response()->json(['message' => 'La solicitud ya no está pendiente.'], 422);
            }

            // Re-validar días disponibles al momento de aprobar
            $year = $vacation->start_date->year;
            $usedDays = Vacation::getUsedDays($vacation->personnel_id, $year);
            if (($usedDays + $vacation->requested_days) > 30) {
                return response()->json(['message' => 'No se puede aprobar: excede los 30 días anuales.'], 422);
            }

            $vacation->update(['status' => 'Aprobada']);
            return response()->json(['message' => 'Solicitud aprobada correctamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        try {
            $vacation = Vacation::findOrFail($id);
            if ($vacation->status != 'Pendiente') {
                return response()->json(['message' => 'La solicitud ya no está pendiente.'], 422);
            }

            $vacation->update(['status' => 'Rechazada']);
            return response()->json(['message' => 'Solicitud rechazada correctamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $vacation = Vacation::findOrFail($id);
            $vacation->delete();
            return response()->json(['message' => 'Solicitud eliminada correctamente.'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function getPersonnelVacationInfo(Request $request)
    {
        $request->validate([
            'personnel_id' => 'required|exists:personnels,id',
            'year' => 'required|integer'
        ]);

        $used = Vacation::getUsedDays($request->personnel_id, $request->year);
        $available = 30 - $used;

        return response()->json([
            'used_days' => $used,
            'available_days' => $available
        ]);
    }
}
