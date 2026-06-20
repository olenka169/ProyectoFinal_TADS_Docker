<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use App\Models\PersonnelType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class PersonnelController extends Controller
{
    public function index(Request $request)
    {
        $personnels = Personnel::with('type')->get();

        if ($request->ajax()) {

            return DataTables::of($personnels)

                ->addColumn('photo', function ($personnel) {

                    if ($personnel->photo_path && Storage::disk('public')->exists($personnel->photo_path)) {
                        return '<img src="' . asset('storage/' . $personnel->photo_path) . '"
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

                ->addColumn('type_name', function ($personnel) {
                    return $personnel->type ? $personnel->type->name : 'Sin tipo';
                })

                ->addColumn('status_badge', function ($personnel) {
                    if ($personnel->status == 'Activo') {
                        return '<span class="badge badge-success badge-custom" >Activo</span>';
                    }

                    return '<span class="badge badge-danger badge-custom">Inactivo</span>';
                })

                ->addColumn('created_at', function ($personnel) {
                    return $personnel->created_at->format('d/m/Y H:i');
                })

                ->addColumn('updated_at', function ($personnel) {
                    return $personnel->updated_at->format('d/m/Y H:i');
                })

                ->addColumn('actions', function ($personnel) {
                    return '
                        <button class="btn btn-sm btn-warning btn-editar" id="' . $personnel->id . '">
                            <i class="fas fa-pen"></i>
                        </button>

                        <button class="btn btn-sm btn-info btn-ver" id="' . $personnel->id . '">
                            <i class="fas fa-eye"></i>
                        </button>

                        <button type="button"
                            class="btn btn-sm btn-danger btn-delete"
                            data-url="' . route('admin.personnels.destroy', $personnel->id) . '">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    ';
                })

                ->rawColumns([
                    'photo',
                    'status_badge',
                    'actions'
                ])

                ->make(true);
        }

        return view('admin.personnels.index');
    }

    public function create()
    {
        $types = PersonnelType::all();

        return view('admin.personnels.create', compact('types'));
    }

    public function store(Request $request)
    {
        try {

            $conductorType = PersonnelType::whereRaw('LOWER(name) = ?', ['conductor'])->first();

            $rules = [
                'dni' => 'required|digits:8|unique:personnels,dni',
                'personnel_type_id' => 'required|exists:personnel_types,id',
                'names' => 'required',
                'lastnames' => 'required',
                'birthdate' => [
                    'required',
                    'date',
                    'before_or_equal:' . Carbon::now()->subYears(18)->format('Y-m-d'),
                ],
                'phone' => 'nullable',
                'email' => 'required|email|unique:personnels,email',
                'status' => 'required',
                'password' => 'required|min:6',
                'address' => 'required',
                'photo_path' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'license_number' => 'nullable|regex:/^[A-Z][0-9]{8}$/',
            ];

            $request->validate($rules, [
                'dni.required' => 'El DNI es obligatorio.',
                'dni.digits' => 'El DNI debe tener 8 dígitos.',
                'dni.unique' => 'Ya existe personal registrado con ese DNI.',

                'personnel_type_id.required' => 'Debe seleccionar un tipo de personal.',
                'personnel_type_id.exists' => 'El tipo de personal seleccionado no es válido.',

                'names.required' => 'Los nombres son obligatorios.',
                'lastnames.required' => 'Los apellidos son obligatorios.',

                'birthdate.required' => 'La fecha de nacimiento es obligatoria.',
                'birthdate.date' => 'Debe ingresar una fecha de nacimiento válida.',
                'birthdate.before_or_equal' => 'El personal debe tener como mínimo 18 años.',

                'email.required' => 'El email es obligatorio.',
                'email.email' => 'Debe ingresar un email válido.',
                'email.unique' => 'Ya existe personal registrado con ese email.',

                'status.required' => 'El estado es obligatorio.',

                'password.required' => 'La contraseña es obligatoria.',
                'password.min' => 'La contraseña debe tener mínimo 6 caracteres.',

                'address.required' => 'La dirección es obligatoria.',

                'photo_path.image' => 'La foto debe ser una imagen.',
                'photo_path.mimes' => 'La foto debe estar en formato JPG, JPEG o PNG.',
                'photo_path.max' => 'La foto no debe superar los 2MB.',

                'license_number.regex' => 'El número de licencia debe tener 1 letra seguida de 8 números. Ejemplo: M12345678.',
            ]);

            $photoPath = null;

            if ($request->hasFile('photo_path')) {
                $photoPath = $request->file('photo_path')
                    ->store('personnels/photos', 'public');
            }

            Personnel::create([
                'dni' => $request->dni,
                'personnel_type_id' => $request->personnel_type_id,
                'names' => $request->names,
                'lastnames' => $request->lastnames,
                'birthdate' => $request->birthdate,
                'phone' => $request->phone,
                'email' => $request->email,
                'status' => $request->status,
                'password' => Hash::make($request->password),
                'address' => $request->address,
                'photo_path' => $photoPath,
                'license_number' => $request->license_number,
            ]);

            return response()->json([
                'message' => 'Personal registrado correctamente'
            ], 200);

        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Error: ' . $th->getMessage()
            ], 500);
        }
    }

    public function show(string $id)
    {
        $personnel = Personnel::with('type')->findOrFail($id);

        return view('admin.personnels.show', compact('personnel'));
    }

    public function edit(string $id)
    {
        $personnel = Personnel::findOrFail($id);
        $types = PersonnelType::all();

        return view('admin.personnels.edit', compact('personnel', 'types'));
    }

    public function update(Request $request, string $id)
    {
        try {

            $personnel = Personnel::findOrFail($id);
            $conductorType = PersonnelType::whereRaw('LOWER(name) = ?', ['conductor'])->first();

            $rules = [
                'dni' => 'required|digits:8|unique:personnels,dni,' . $id,
                'personnel_type_id' => 'required|exists:personnel_types,id',
                'names' => 'required',
                'lastnames' => 'required',
                'birthdate' => [
                    'required',
                    'date',
                    'before_or_equal:' . Carbon::now()->subYears(18)->format('Y-m-d'),
                ],
                'phone' => 'nullable',
                'email' => 'required|email|unique:personnels,email,' . $id,
                'status' => 'required',
                'password' => 'nullable|min:6',
                'address' => 'required',
                'photo_path' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'license_number' => 'nullable|regex:/^[A-Z][0-9]{8}$/',
            ];

            $request->validate($rules, [
                'dni.required' => 'El DNI es obligatorio.',
                'dni.digits' => 'El DNI debe tener 8 dígitos.',
                'dni.unique' => 'Ya existe personal registrado con ese DNI.',

                'personnel_type_id.required' => 'Debe seleccionar un tipo de personal.',
                'personnel_type_id.exists' => 'El tipo de personal seleccionado no es válido.',

                'names.required' => 'Los nombres son obligatorios.',
                'lastnames.required' => 'Los apellidos son obligatorios.',

                'birthdate.required' => 'La fecha de nacimiento es obligatoria.',
                'birthdate.date' => 'Debe ingresar una fecha de nacimiento válida.',
                'birthdate.before_or_equal' => 'El personal debe tener como mínimo 18 años.',

                'email.required' => 'El email es obligatorio.',
                'email.email' => 'Debe ingresar un email válido.',
                'email.unique' => 'Ya existe personal registrado con ese email.',

                'status.required' => 'El estado es obligatorio.',

                'password.min' => 'La contraseña debe tener mínimo 6 caracteres.',

                'address.required' => 'La dirección es obligatoria.',

                'photo_path.image' => 'La foto debe ser una imagen.',
                'photo_path.mimes' => 'La foto debe estar en formato JPG, JPEG o PNG.',
                'photo_path.max' => 'La foto no debe superar los 2MB.',

                'license_number.regex' => 'El número de licencia debe tener 1 letra seguida de 8 números. Ejemplo: M12345678.',
            ]);

            $data = [
                'dni' => $request->dni,
                'personnel_type_id' => $request->personnel_type_id,
                'names' => $request->names,
                'lastnames' => $request->lastnames,
                'birthdate' => $request->birthdate,
                'phone' => $request->phone,
                'email' => $request->email,
                'status' => $request->status,
                'address' => $request->address,
                'license_number' => $request->license_number,
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            if ($request->hasFile('photo_path')) {

                if ($personnel->photo_path && Storage::disk('public')->exists($personnel->photo_path)) {
                    Storage::disk('public')->delete($personnel->photo_path);
                }

                $data['photo_path'] = $request->file('photo_path')
                    ->store('personnels/photos', 'public');
            }

            $personnel->update($data);

            return response()->json([
                'message' => 'Personal actualizado correctamente'
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

            $personnel = Personnel::withCount('contracts')->findOrFail($id);

            if ($personnel->contracts_count > 0) {
                return response()->json([
                    'message' => 'No se puede eliminar al personal porque tiene contratos registrados.'
                ], 422);
            }

            if ($personnel->photo_path && Storage::disk('public')->exists($personnel->photo_path)) {
                Storage::disk('public')->delete($personnel->photo_path);
            }

            $personnel->delete();

            return response()->json([
                'message' => 'Personal eliminado correctamente'
            ], 200);

        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Error en la eliminación: ' . $th->getMessage()
            ], 500);
        }
    }
}