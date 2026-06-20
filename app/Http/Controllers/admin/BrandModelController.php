<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\BrandModel;
use App\Models\Brand;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BrandModelController extends Controller
{
    public function index(Request $request)
    {
        // Cargamos la relación 'brand' para mostrar el nombre de la marca en la tabla
        $models = BrandModel::with('brand')->get();

        if ($request->ajax()) {
            return DataTables::of($models)
                ->addColumn('brand_name', function ($model) {
                    return $model->brand ? $model->brand->name : 'N/A';
                })
                ->addColumn('created_at', function ($model) {
                    return $model->created_at->format('d/m/Y H:i');
                })
                ->addColumn('updated_at', function ($model) {
                    return $model->updated_at->format('d/m/Y H:i');
                })
                ->addColumn('actions', function ($model) {
                    return '
                        <button class="btn btn-sm btn-warning btn-editar" id="' . $model->id . '">
                            <i class="fas fa-pen"></i>
                        </button>

                        <button type="button"
                            class="btn btn-sm btn-danger btn-delete"
                            data-url="' . route('admin.models.destroy', $model->id) . '">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    ';
                })

                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('admin.models.index');
    }

    public function create()
    {
        $brands = Brand::all();
        return view('admin.models.create', compact('brands'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'brand_id' => 'required|exists:brands,id',
                'name' => 'required',
                'code' => 'required|unique:brand_models,code',
                'description' => 'nullable'
            ], [
                'brand_id.required' => 'Debe seleccionar una marca.',
                'brand_id.exists' => 'La marca seleccionada no es válida.',
                'name.required' => 'El nombre del modelo es obligatorio.',
                'code.required' => 'El código del modelo es obligatorio.',
                'code.unique' => 'Ya existe un modelo con ese código.'
            ]);

            BrandModel::create($request->all());

            return response()->json(['message' => 'Modelo registrado correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function edit(string $id)
    {
        $model = BrandModel::findOrFail($id);
        $brands = Brand::all();
        return view('admin.models.edit', compact('model', 'brands'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $model = BrandModel::findOrFail($id);

            $request->validate([
                'brand_id' => 'required|exists:brands,id',
                'name' => 'required',
                'code' => 'required|unique:brand_models,code,' . $id,
                'description' => 'nullable'
            ], [
                'brand_id.required' => 'Debe seleccionar una marca.',
                'name.required' => 'El nombre del modelo es obligatorio.',
                'code.required' => 'El código del modelo es obligatorio.',
                'code.unique' => 'Ya existe un modelo con ese código.'
            ]);

            $model->update($request->all());

            return response()->json(['message' => 'Modelo actualizado correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $model = BrandModel::findOrFail($id);
            $model->delete();

            return response()->json(['message' => 'Modelo eliminado correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en la eliminación: ' . $th->getMessage()], 500);
        }
    }
}
