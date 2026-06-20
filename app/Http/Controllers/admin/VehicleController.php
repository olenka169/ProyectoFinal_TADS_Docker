<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use App\Models\BrandModel;
use App\Models\VehicleType;
use App\Models\VehicleColor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $vehicles = Vehicle::with(['model.brand', 'type', 'color', 'images' => function($query) {
                $query->orderBy('is_profile', 'desc');
            }])->get();

            return DataTables::of($vehicles)
                ->addColumn('image', function ($vehicle) {
                    $profileImage = $vehicle->images->where('is_profile', true)->first() ?: $vehicle->images->first();

                    if ($profileImage && Storage::disk('public')->exists($profileImage->path)) {
                        return '<img src="' . asset('storage/' . $profileImage->path) . '"
                                class="img-thumbnail"
                                width="50"
                                height="50"
                                style="object-fit:cover;border-radius:4px;">';
                    }

                    return '<div class="bg-light d-flex align-items-center justify-content-center border rounded"
                                 style="width:50px; height:50px;">
                                <i class="fas fa-image text-muted"></i>
                            </div>';
                })
                ->addColumn('full_model', function ($vehicle) {
                    return ($vehicle->model && $vehicle->model->brand) 
                        ? $vehicle->model->brand->name . ' ' . $vehicle->model->name 
                        : 'N/A';
                })
                ->addColumn('type_name', function ($vehicle) {
                    return $vehicle->type ? $vehicle->type->name : 'N/A';
                })
                ->addColumn('color_info', function ($vehicle) {
                    if (!$vehicle->color) return 'N/A';
                    return '<div class="d-flex align-items-center">
                                <div style="width:20px;height:20px;border-radius:50%;background:' . $vehicle->color->code . ';margin-right:8px;border:1px solid #ccc;"></div>' 
                                . $vehicle->color->name . 
                           '</div>';
                })
                ->addColumn('status_badge', function ($vehicle) {
                    switch ($vehicle->status) {
                        case 'Activo':
                            return '<span class="badge badge-success badge-custom">Activo</span>';
                        case 'Inactivo':
                            return '<span class="badge badge-danger badge-custom">Inactivo</span>';
                        case 'Mantenimiento':
                            return '<span class="badge badge-warning badge-custom">Mantenimiento</span>';
                        default:
                            return '<span class="badge badge-secondary badge-custom">'
                                . $vehicle->status .
                                '</span>';
                    }
                })
                ->addColumn('actions', function ($vehicle) {
                    return '
                        <button class="btn btn-sm btn-warning btn-editar" id="' . $vehicle->id . '">
                            <i class="fas fa-pen"></i>
                        </button>

                        <button type="button"
                            class="btn btn-sm btn-danger btn-delete"
                            data-url="' . route('admin.vehicles.destroy', $vehicle->id) . '">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    ';
                })
                ->rawColumns(['image', 'color_info', 'status_badge', 'actions'])
                ->make(true);
        }

        return view('admin.vehicles.index');
    }

    public function create()
    {
        $models = BrandModel::with('brand')->get();
        $types = VehicleType::all();
        $colors = VehicleColor::all();
        
        return view('admin.vehicles.create', compact('models', 'types', 'colors'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'plate' => 'required|unique:vehicles,plate',
                'brand_model_id' => 'required|exists:brand_models,id',
                'vehicle_type_id' => 'required|exists:vehicle_types,id',
                'vehicle_color_id' => 'required|exists:vehicle_colors,id',
                'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'mileage' => 'required|integer|min:0',
                'status' => 'required',
                'code' => 'nullable|string|max:50',
                'name' => 'nullable|string|max:255',
                'load_capacity' => 'nullable|numeric|min:0',
                'fuel_capacity' => 'nullable|numeric|min:0',
                'compaction_capacity' => 'nullable|numeric|min:0',
                'passenger_capacity' => 'nullable|integer|min:0',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ], [
                'plate.required' => 'La placa es obligatoria.',
                'plate.unique' => 'Esta placa ya está registrada.',
                'brand_model_id.required' => 'Debe seleccionar un modelo.',
                'vehicle_type_id.required' => 'Debe seleccionar un tipo.',
                'vehicle_color_id.required' => 'Debe seleccionar un color.',
                'year.required' => 'El año es obligatorio.'
            ]);

            $vehicle = Vehicle::create($request->all());

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $path = $file->store('vehicles', 'public');
                    $vehicle->images()->create([
                        'path' => $path,
                        'is_profile' => ($index === 0) // La primera imagen será el perfil por defecto
                    ]);
                }
            }

            return response()->json(['message' => 'Vehículo registrado correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function edit(string $id)
    {
        $vehicle = Vehicle::with('images')->findOrFail($id);
        $models = BrandModel::with('brand')->get();
        $types = VehicleType::all();
        $colors = VehicleColor::all();

        return view('admin.vehicles.edit', compact('vehicle', 'models', 'types', 'colors'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $vehicle = Vehicle::findOrFail($id);

            $request->validate([
                'plate' => 'required|unique:vehicles,plate,' . $id,
                'brand_model_id' => 'required|exists:brand_models,id',
                'vehicle_type_id' => 'required|exists:vehicle_types,id',
                'vehicle_color_id' => 'required|exists:vehicle_colors,id',
                'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'mileage' => 'required|integer|min:0',
                'status' => 'required',
                'code' => 'nullable|string|max:50',
                'name' => 'nullable|string|max:255',
                'load_capacity' => 'nullable|numeric|min:0',
                'fuel_capacity' => 'nullable|numeric|min:0',
                'compaction_capacity' => 'nullable|numeric|min:0',
                'passenger_capacity' => 'nullable|integer|min:0',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ], [
                'plate.required' => 'La placa es obligatoria.',
                'plate.unique' => 'Esta placa ya está registrada.'
            ]);

            $vehicle->update($request->all());

            // Manejar cambio de imagen de perfil
            if ($request->has('profile_image_id')) {
                $vehicle->images()->update(['is_profile' => false]);
                $vehicle->images()->where('id', $request->profile_image_id)->update(['is_profile' => true]);
            }

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $path = $file->store('vehicles', 'public');
                    
                    // Si el vehículo no tenía imágenes previas, la primera nueva será el perfil
                    $isProfile = $vehicle->images()->count() === 0;

                    $vehicle->images()->create([
                        'path' => $path,
                        'is_profile' => $isProfile
                    ]);
                }
            }

            return response()->json(['message' => 'Vehículo actualizado correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function deleteImage(string $id)
    {
        try {
            $image = VehicleImage::findOrFail($id);
            $vehicleId = $image->vehicle_id;
            $wasProfile = $image->is_profile;

            // Eliminar archivo físico
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }

            $image->delete();

            // Si era la imagen de perfil, asignar otra como perfil
            if ($wasProfile) {
                $nextImage = VehicleImage::where('vehicle_id', $vehicleId)->first();
                if ($nextImage) {
                    $nextImage->update(['is_profile' => true]);
                }
            }

            return response()->json(['message' => 'Imagen eliminada correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error: ' . $th->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $vehicle = Vehicle::with('images')->findOrFail($id);
            
            // Eliminar archivos físicos
            foreach ($vehicle->images as $image) {
                if (Storage::disk('public')->exists($image->path)) {
                    Storage::disk('public')->delete($image->path);
                }
            }

            $vehicle->delete();

            return response()->json(['message' => 'Vehículo eliminado correctamente'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error en la eliminación: ' . $th->getMessage()], 500);
        }
    }
}
