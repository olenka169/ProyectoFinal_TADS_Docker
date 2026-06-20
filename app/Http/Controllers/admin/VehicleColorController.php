<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleColor;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class VehicleColorController extends Controller
{
    public function index(Request $request)
    {
        $colors = VehicleColor::all();

        if ($request->ajax()) {
            return DataTables::of($colors)
                ->addColumn('color_preview', function ($color) {
                    return '<div style="width:35px;height:25px;border-radius:4px;border:1px solid #ccc;background:' . $color->code . ';"></div>';
                })
                ->addColumn('created_at', function ($color) {
                    return $color->created_at->format('d/m/Y H:i');
                })
                ->addColumn('updated_at', function ($color) {
                    return $color->updated_at->format('d/m/Y H:i');
                })
                ->addColumn('actions', function ($color) {
                    return '
                        <button class="btn btn-sm btn-warning btn-editar" id="' . $color->id . '">
                            <i class="fas fa-pen"></i>
                        </button>

                        <button type="button"
                            class="btn btn-sm btn-danger btn-delete"
                            data-url="' . route('admin.colors.destroy', $color->id) . '">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    ';
                })
                ->rawColumns(['color_preview', 'actions'])
                ->make(true);
        }

        return view('admin.colors.index');
    }

    public function create()
    {
        return view('admin.colors.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|unique:vehicle_colors,name',
                'code' => 'required',
                'description' => 'nullable'
            ], [
                'name.required' => 'El nombre del color es obligatorio.',
                'name.unique' => 'Ya existe un color con ese nombre.',
                'code.required' => 'El código del color es obligatorio.'
            ]);

            VehicleColor::create([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description
            ]);

            return response()->json(['message' => 'Color registrado correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function edit(string $id)
    {
        $color = VehicleColor::findOrFail($id);
        return view('admin.colors.edit', compact('color'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $color = VehicleColor::findOrFail($id);

            $request->validate([
                'name' => 'required|unique:vehicle_colors,name,' . $id,
                'code' => 'required',
                'description' => 'nullable'
            ], [
                'name.required' => 'El nombre del color es obligatorio.',
                'name.unique' => 'Ya existe un color con ese nombre.',
                'code.required' => 'El código del color es obligatorio.'
            ]);

            $color->update([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description
            ]);

            return response()->json(['message' => 'Color actualizado correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $color = VehicleColor::findOrFail($id);
            $color->delete();

            return response()->json(['message' => 'Color eliminado correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en la eliminación: ' . $th->getMessage()], 500);
        }
    }
}