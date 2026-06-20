<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $brands = Brand::all();

        if ($request->ajax()) {

            return DataTables::of($brands)

                ->addColumn('logo', function ($brand) {

                    if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
                        return '<img src="' . asset('storage/' . $brand->logo) . '"
                                class="img-thumbnail"
                                width="50"
                                height="50"
                                style="object-fit:contain;">';
                    }

                    return '<div class="bg-light d-flex align-items-center justify-content-center border rounded" 
                                 style="width:50px; height:50px;">
                                <i class="fas fa-image text-muted"></i>
                            </div>';
                })

                ->addColumn('created_at', function ($brand) {
                    return $brand->created_at->format('d/m/Y H:i');
                })

                ->addColumn('updated_at', function ($brand) {
                    return $brand->updated_at->format('d/m/Y H:i');
                })

                ->addColumn('actions', function ($brand) {
                    return '
                        <button class="btn btn-sm btn-warning btn-editar"
                            id="' . $brand->id . '">
                            <i class="fas fa-pen"></i>
                        </button>

                        <button type="button"
                            class="btn btn-sm btn-danger btn-delete"
                            data-url="' . route('admin.brands.destroy', $brand->id) . '">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    ';
                })

                ->rawColumns([
                    'logo',
                    'actions'
                ])

                ->make(true);
        }

        return view('admin.brands.index');
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(Request $request)
    {
        try {

            $request->validate([
                'name' => 'required|unique:brands,name',
                'description' => 'nullable',
                'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
            ]);

            $logo = null;

            if ($request->hasFile('logo')) {

                $logo = $request->file('logo')
                    ->store('brands', 'public');
            }

            Brand::create([
                'name' => $request->name,
                'description' => $request->description,
                'logo' => $logo
            ]);

            return response()->json([
                'message' => 'Marca registrada correctamente'
            ], 200);

        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Error: ' . $th->getMessage()
            ], 500);
        }
    }

    public function edit(string $id)
    {
        $brand = Brand::findOrFail($id);

        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, string $id)
    {
        try {

            $brand = Brand::findOrFail($id);

            $request->validate([
                'name' => 'required|unique:brands,name,' . $id,
                'description' => 'nullable',
                'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
            ]);

            if ($request->hasFile('logo')) {
                // Eliminar logo anterior si existe
                if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
                    Storage::disk('public')->delete($brand->logo);
                }

                // Guardar nuevo logo
                $path = $request->file('logo')->store('brands', 'public');
                $brand->logo = $path;
            }

            $brand->name = $request->name;
            $brand->description = $request->description;

            $brand->save();

            return response()->json([
                'message' => 'Marca actualizada correctamente'
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
            $brand = Brand::findOrFail($id);

            // Verificar si la marca tiene modelos asociados
            if ($brand->models()->count() > 0) {
                return response()->json([
                    'message' => 'No se puede eliminar la marca porque tiene modelos registrados asociados a ella.'
                ], 422); // Error de validación / conflicto
            }

            if ($brand->logo &&
                Storage::disk('public')->exists($brand->logo)) {

                Storage::disk('public')->delete($brand->logo);
            }

            $brand->delete();

            return response()->json([
                'message' => 'Marca eliminada correctamente'
            ], 200);

        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Error en la eliminación: '
                    . $th->getMessage()
            ], 500);
        }
    }
}