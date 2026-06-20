<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        // Desactivación automática de contratos vencidos
        Contract::where('is_active', true)
            ->whereNotNull('end_date')
            ->where('end_date', '<', now()->format('Y-m-d'))
            ->update(['is_active' => false]);

        if ($request->ajax()) {
            $contracts = Contract::with('personnel')
                ->leftJoin('personnels', 'contracts.personnel_id', '=', 'personnels.id')
                ->select('contracts.*');

            return DataTables::of($contracts)
                ->addColumn('personnel_name', function ($contract) {
                    return $contract->personnel->names . ' ' . $contract->personnel->lastnames;
                })
                ->addColumn('personnel_dni', function ($contract) {
                    return $contract->personnel->dni;
                })
                ->editColumn('start_date', function ($contract) {
                    return $contract->start_date->format('d/m/Y');
                })
                ->editColumn('end_date', function ($contract) {
                    return $contract->end_date ? $contract->end_date->format('d/m/Y') : 'N/A';
                })
                ->addColumn('status', function ($contract) {
                    if ($contract->is_active) {
                        return '<span class="badge badge-success badge-custom">Activo</span>';
                    }

                    return '<span class="badge badge-danger badge-custom">Inactivo</span>';
                })
                ->addColumn('actions', function ($contract) {
                    return '
                        <button class="btn btn-sm btn-warning btn-editar" id="' . $contract->id . '">
                            <i class="fas fa-pen"></i>
                        </button>

                        <button type="button"
                            class="btn btn-sm btn-danger btn-delete"
                            data-url="' . route('admin.contracts.destroy', $contract->id) . '">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    ';
                })
                ->rawColumns(['status', 'actions'])
                ->make(true);
        }

        return view('admin.contracts.index');
    }

    public function create()
    {
        $personnels = Personnel::all();
        return view('admin.contracts.create', compact('personnels'));
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'personnel_id' => 'required|exists:personnels,id',
                'type' => 'required|in:Permanente,Nombrado,Temporal',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'salary' => 'required|numeric|min:0',
                'probation_period' => 'nullable|string',
                'is_active' => 'boolean'
            ]);

            $personnelId = $data['personnel_id'];
            $is_active = $request->boolean('is_active');

            // 1. Validar si ya existe un contrato activo para este personal
            $activeContract = Contract::where('personnel_id', $personnelId)
                ->where('is_active', true)
                ->first();

            if ($activeContract) {
                return response()->json([
                    'message' => 'Este personal ya cuenta con un contrato activo. Debe finalizarlo antes de registrar uno nuevo.'
                ], 422);
            }

            // 2. Validar periodo de carencia de 2 meses desde el último contrato inactivo
            $lastContract = Contract::where('personnel_id', $personnelId)
                ->orderBy('end_date', 'desc')
                ->first();

            if ($lastContract && $lastContract->end_date) {
                $minDate = $lastContract->end_date->addMonths(2);
                if (now()->lessThan($minDate)) {
                    return response()->json([
                        'message' => 'Debe pasar un mínimo de dos meses desde la finalización del contrato anterior (Fecha permitida: ' . $minDate->format('d/m/Y') . ').'
                    ], 422);
                }
            }

            $data['is_active'] = $is_active;
            Contract::create($data);

            return response()->json([
                'message' => 'Contrato registrado correctamente'
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error: ' . $th->getMessage()
            ], 500);
        }
    }

    public function edit(string $id)
    {
        $contract = Contract::findOrFail($id);
        $personnels = Personnel::all();
        return view('admin.contracts.edit', compact('contract', 'personnels'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $contract = Contract::findOrFail($id);

            $data = $request->validate([
                'personnel_id' => 'required|exists:personnels,id',
                'type' => 'required|in:Permanente,Nombrado,Temporal',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'salary' => 'required|numeric|min:0',
                'probation_period' => 'nullable|string',
                'is_active' => 'boolean'
            ]);

            $data['is_active'] = $request->boolean('is_active');

            // Desactivar otros contratos si este se marca como activo
            if ($data['is_active']) {
                Contract::where('personnel_id', $data['personnel_id'])
                    ->where('id', '!=', $id)
                    ->update(['is_active' => false]);
            }

            $contract->update($data);

            return response()->json([
                'message' => 'Contrato actualizado correctamente'
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
            $contract = Contract::findOrFail($id);

            // Verificación explícita del estado activo (soporta booleanos e integrales de la DB)
            if ($contract->is_active == 1 || $contract->is_active === true) {
                return response()->json([
                    'message' => 'No se puede eliminar un contrato que se encuentra actualmente activo.'
                ], 422);
            }

            $contract->delete();

            return response()->json([
                'message' => 'Contrato eliminado correctamente'
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error en la eliminación: ' . $th->getMessage()
            ], 500);
        }
    }
}
