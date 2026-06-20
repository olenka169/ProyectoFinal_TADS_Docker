<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\PersonnelType;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;


class PersonnelTypeController extends Controller
{
    public function index(Request $request)
    {
        $types = PersonnelType::all();

        if ($request->ajax()) {

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
                            data-url="' . route('admin.personnel-types.destroy', $type->id) . '">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    ';
                })

                ->rawColumns(['actions'])

                ->make(true);
        }

        return view('admin.personnel-types.index');
    }

    public function create()
    {
        return view('admin.personnel-types.create');
    }

    public function store(Request $request)
    {
        try {

            $request->validate([
                'name' => 'required|unique:personnel_types,name',
                'description' => 'nullable'
            ], [
                'name.required' => 'El nombre es obligatorio.',
                'name.unique' => 'Ya existe un tipo de personal con ese nombre.'
            ]);

            PersonnelType::create([
                'name' => $request->name,
                'description' => $request->description
            ]);

            return response()->json([
                'message' => 'Tipo de personal registrado correctamente'
            ], 200);
        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Error: ' . $th->getMessage()
            ], 500);
        }
    }

    public function edit(string $id)
    {
        $type = PersonnelType::findOrFail($id);

        return view('admin.personnel-types.edit', compact('type'));
    }

    public function update(Request $request, string $id)
    {
        try {

            $type = PersonnelType::findOrFail($id);

            $request->validate([
                'name' => 'required|unique:personnel_types,name,' . $id,
                'description' => 'nullable'
            ]);

            $protectedTypes = ['conductor', 'ayudante'];

            if (
                in_array(strtolower(trim($type->name)), $protectedTypes)
                &&
                strtolower(trim($request->name)) !== strtolower(trim($type->name))
            ) {

                return response()->json([
                    'message' => 'No se puede modificar el nombre de los tipos de personal predefinidos: Conductor y Ayudante.'
                ], 422);
            }

            $type->update([
                'name' => $request->name,
                'description' => $request->description
            ]);

            return response()->json([
                'message' => 'Tipo de personal actualizado correctamente'
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

            $type = PersonnelType::withCount('personnels')->findOrFail($id);

            if ($type->personnels_count > 0) {
                return response()->json([
                    'message' => 'No se puede eliminar el tipo de personal porque tiene personal registrado con este tipo.'
                ], 422);
            }

            $protectedTypes = ['conductor', 'ayudante'];

            if (in_array(strtolower(trim($type->name)), $protectedTypes)) {

                return response()->json([
                    'message' => 'No se pueden eliminar los tipos de personal predefinidos: Conductor y Ayudante.'
                ], 422);
            }

            $type->delete();

            return response()->json([
                'message' => 'Tipo de personal eliminado correctamente'
            ], 200);
        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Error en la eliminación: ' . $th->getMessage()
            ], 500);
        }
    }
}
