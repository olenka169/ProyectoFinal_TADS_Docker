<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use App\Models\Department;
use App\Models\Province;
use App\Models\District;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ZoneController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $zones = Zone::with(['department', 'province', 'district'])
                ->select('zones.*');

            return DataTables::eloquent($zones)
                ->addColumn('department', function ($zone) {
                    return $zone->department->name ?? '-';
                })
                ->addColumn('province', function ($zone) {
                    return $zone->province->name ?? '-';
                })
                ->addColumn('district', function ($zone) {
                    return $zone->district->name ?? '-';
                })
                ->addColumn('description', function ($zone) {
                    return $zone->description ?: '—';
                })
                ->addColumn('status_label', function ($zone) {
                    return $zone->status
                        ? '<span class="badge badge-success badge-custom">Activo</span>'
                        : '<span class="badge badge-danger badge-custom">Inactivo</span>';
                })
                ->addColumn('coordinates_status', function ($zone) {
                    $count = is_array($zone->coordinates) ? count($zone->coordinates) : 0;

                    if ($count > 0) {
                        return '<span class="badge badge-info badge-custom">' . $count . ' puntos</span>';
                    }

                    return '<span class="badge badge-warning badge-custom">Pendiente</span>';
                })
                ->addColumn('created_at_formatted', function ($zone) {
                    return $zone->created_at ? $zone->created_at->format('d/m/Y H:i') : '-';
                })
                ->addColumn('actions', function ($zone) {
                    return '
                        <button class="btn btn-sm btn-warning btn-editar" id="' . $zone->id . '">
                            <i class="fas fa-pen"></i>
                        </button>

                        <button class="btn btn-sm btn-info btn-ver-mapa" id="' . $zone->id . '">
                            <i class="fas fa-map-marked-alt"></i>
                        </button>

                        <button type="button"
                            class="btn btn-sm btn-danger btn-delete"
                            data-url="' . route('admin.zones.destroy', $zone->id) . '">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    ';
                })
                ->rawColumns(['status_label', 'coordinates_status', 'actions'])
                ->make(true);
        }

        return view('admin.zones.index');
    }

    public function create()
    {
        $departments = Department::orderBy('name', 'asc')->pluck('name', 'id');

        $provinces = collect();
        $districts = collect();

        $defaultDepartment = null;
        $defaultProvince = null;
        $defaultDistrict = null;

        return view('admin.zones.create', compact(
            'departments',
            'provinces',
            'districts',
            'defaultDepartment',
            'defaultProvince',
            'defaultDistrict'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:zones,name',
            'department_id' => 'required|exists:departments,id',
            'province_id' => 'required|exists:provinces,id',
            'district_id' => 'required|exists:districts,id',
            'description' => 'nullable|string',
            'average_waste' => 'nullable|numeric|min:0',
            'status' => 'required|boolean',
            'coordinates' => 'required|json',
        ], [
            'name.required' => 'El nombre de la zona es obligatorio.',
            'name.unique' => 'La zona ya ha sido registrada.',
            'department_id.required' => 'Seleccione un departamento.',
            'province_id.required' => 'Seleccione una provincia.',
            'district_id.required' => 'Seleccione un distrito.',
            'coordinates.required' => 'Debe dibujar el perímetro de la zona.',
            'coordinates.json' => 'Las coordenadas del perímetro no tienen un formato válido.',
        ]);

        $data = $request->all();
        $data['coordinates'] = json_decode($request->coordinates, true);

        Zone::create($data);

        return response()->json([
            'message' => 'Zona registrada correctamente.'
        ]);
    }

    public function show(string $id)
    {
        $zone = Zone::with(['department', 'province', 'district'])->findOrFail($id);

        return view('admin.zones.show', compact('zone'));
    }

    public function edit(string $id)
    {
        $zone = Zone::findOrFail($id);

        $departments = Department::orderBy('name', 'asc')->pluck('name', 'id');

        $provinces = Province::where('department_id', $zone->department_id)
            ->orderBy('name', 'asc')
            ->pluck('name', 'id');

        $districts = District::where('province_id', $zone->province_id)
            ->orderBy('name', 'asc')
            ->pluck('name', 'id');

        return view('admin.zones.edit', compact(
            'zone',
            'departments',
            'provinces',
            'districts'
        ));
    }

    public function update(Request $request, string $id)
    {
        $zone = Zone::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100|unique:zones,name,' . $zone->id,
            'department_id' => 'required|exists:departments,id',
            'province_id' => 'required|exists:provinces,id',
            'district_id' => 'required|exists:districts,id',
            'description' => 'nullable|string',
            'average_waste' => 'nullable|numeric|min:0',
            'status' => 'required|boolean',
            'coordinates' => 'required|json',
        ], [
            'name.required' => 'El nombre de la zona es obligatorio.',
            'name.unique' => 'La zona ya ha sido registrada.',
            'coordinates.required' => 'Debe dibujar el perímetro de la zona.',
            'coordinates.json' => 'Las coordenadas del perímetro no tienen un formato válido.',
        ]);

        $data = $request->all();
        $data['coordinates'] = json_decode($request->coordinates, true);

        $zone->update($data);

        return response()->json([
            'message' => 'Zona actualizada correctamente.'
        ]);
    }

    public function destroy(string $id)
    {
        $zone = Zone::findOrFail($id);
        $zone->delete();

        return response()->json([
            'message' => 'Zona eliminada correctamente.'
        ]);
    }

    public function polygons($id = null)
    {
        $zones = Zone::select('id', 'name', 'coordinates')
            ->whereNotNull('coordinates')
            ->when($id, function ($query) use ($id) {
                $query->where('id', '!=', $id);
            })
            ->get();

        return response()->json($zones);
    }

    public function generalMap()
    {
        $departments = Department::orderBy('name')->get(['id', 'name']);

        return view('admin.zones.general-map', compact('departments'));
    }

    public function allPolygons()
    {
        $zones = Zone::with(['department', 'province', 'district'])
            ->whereNotNull('coordinates')
            ->get()
            ->map(function ($zone) {
                return [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'department_id' => $zone->department_id,
                    'province_id' => $zone->province_id,
                    'district_id' => $zone->district_id,
                    'department' => $zone->department->name ?? '-',
                    'province' => $zone->province->name ?? '-',
                    'district' => $zone->district->name ?? '-',
                    'description' => $zone->description ?: 'Sin descripción',
                    'average_waste' => $zone->average_waste,
                    'status' => $zone->status,
                    'coordinates' => $zone->coordinates,
                ];
            });

        return response()->json($zones);
    }

    public function getProvinces($departmentId)
    {
        $provinces = Province::where('department_id', $departmentId)
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        return response()->json($provinces);
    }

    public function getDistricts($provinceId)
    {
        $districts = District::where('province_id', $provinceId)
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        return response()->json($districts);
    }
}