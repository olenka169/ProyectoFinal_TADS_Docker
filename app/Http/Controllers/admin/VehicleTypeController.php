<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class VehicleTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $types = VehicleType::all();

            return DataTables::of($types)
                ->addColumn('created_at', function ($type) {
                    return $type->created_at->format('d/m/Y H:i');
                })
                ->addColumn('updated_at', function ($type) {
                    return $type->updated_at->format('d/m/Y H:i');
                })
                ->addColumn('actions', function ($type) {
                    return '
                        <button class="btn btn-sm btn-warning btn-editar" id="' . $type->id . '">
                            <i class="fas fa-pen"></i>
                        </button>

                        <button type="button"
                            class="btn btn-sm btn-danger btn-delete"
                            data-url="' . route('admin.vehicle-types.destroy', $type->id) . '">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    ';
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('admin.vehicle-types.index');
    }

    public function create()
    {
        return view('admin.vehicle-types.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|unique:vehicle_types,name',
                'description' => 'nullable'
            ], [
                'name.required' => 'El nombre del tipo es obligatorio.',
                'name.unique' => 'Este tipo ya existe.'
            ]);

            VehicleType::create($request->all());

            return response()->json(['message' => 'Tipo de vehículo registrado correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function edit(string $id)
    {
        $type = VehicleType::findOrFail($id);
        return view('admin.vehicle-types.edit', compact('type'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $type = VehicleType::findOrFail($id);

            $request->validate([
                'name' => 'required|unique:vehicle_types,name,' . $id,
                'description' => 'nullable'
            ], [
                'name.required' => 'El nombre del tipo es obligatorio.',
                'name.unique' => 'Este tipo ya existe.'
            ]);

            $type->update($request->all());

            return response()->json(['message' => 'Tipo de vehículo actualizado correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $type = VehicleType::findOrFail($id);

            if ($type->vehicles()->count() > 0) {
                return response()->json([
                    'message' => 'No se puede eliminar el tipo de vehículo porque tiene vehículos asociados.'
                ], 422);
            }

            $type->delete();

            return response()->json(['message' => 'Tipo de vehículo eliminado correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en la eliminación: ' . $th->getMessage()], 500);
        }
    }
}
