<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $shifts = Shift::all();

        if ($request->ajax()) {

            return DataTables::of($shifts)

                ->addColumn('start_time', function ($shift) {
                    return '<span class="badge badge-success badge-custom">'
                        . date('H:i', strtotime($shift->start_time))
                        . '</span>';
                })

                ->addColumn('end_time', function ($shift) {
                    return '<span class="badge badge-danger badge-custom">'
                        . date('H:i', strtotime($shift->end_time))
                        . '</span>';
                })

                ->addColumn('created_at', function ($shift) {
                    return $shift->created_at
                        ? $shift->created_at->format('d/m/Y H:i')
                        : '';
                })

                ->addColumn('updated_at', function ($shift) {
                    return $shift->updated_at
                        ? $shift->updated_at->format('d/m/Y H:i')
                        : '';
                })

                ->addColumn('actions', function ($shift) {
                    return '
                        <button class="btn btn-sm btn-warning btn-editar" id="' . $shift->id . '">
                            <i class="fas fa-pen"></i>
                        </button>

                        <button type="button"
                            class="btn btn-sm btn-danger btn-delete"
                            data-url="' . route('admin.shifts.destroy', $shift->id) . '">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    ';
                })

                ->rawColumns([
                    'start_time',
                    'end_time',
                    'actions'
                ])

                ->make(true);
        }

        return view('admin.shifts.index');
    }

    public function create()
    {
        return view('admin.shifts.create');
    }

    public function store(Request $request)
    {
        try {

            $request->validate([
                'name' => 'required|unique:shifts,name',
                'start_time' => 'required',
                'end_time' => 'required',
                'description' => 'nullable'
            ]);

            if ($request->start_time == $request->end_time) {
                return response()->json([
                    'message' => 'La hora de inicio y fin no pueden ser iguales.'
                ], 422);
            }

            Shift::create([
                'name' => $request->name,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'description' => $request->description
            ]);

            return response()->json([
                'message' => 'Turno registrado correctamente'
            ], 200);

        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Error: '.$th->getMessage()
            ], 500);
        }
    }

    public function edit(string $id)
    {
        $shift = Shift::findOrFail($id);

        return view('admin.shifts.edit', compact('shift'));
    }

    public function update(Request $request, string $id)
    {
        try {

            $shift = Shift::findOrFail($id);

            $request->validate([
                'name' => 'required|unique:shifts,name,'.$id,
                'start_time' => 'required',
                'end_time' => 'required',
                'description' => 'nullable'
            ]);

            if ($request->start_time == $request->end_time) {
                return response()->json([
                    'message' => 'La hora de inicio y fin no pueden ser iguales.'
                ], 422);
            }

            $shift->update([
                'name' => $request->name,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'description' => $request->description
            ]);

            return response()->json([
                'message' => 'Turno actualizado correctamente'
            ], 200);

        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Error: '.$th->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {

            $shift = Shift::findOrFail($id);

            $shift->delete();

            return response()->json([
                'message' => 'Turno eliminado correctamente'
            ], 200);

        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Error en la eliminación: '.$th->getMessage()
            ], 500);
        }
    }
}