<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $holidays = Holiday::query();

            if ($request->filled('start_date')) {
                $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
                $holidays->whereDate('date', '>=', $startDate);
            }

            if ($request->filled('end_date')) {
                $endDate = Carbon::parse($request->end_date)->format('Y-m-d');
                $holidays->whereDate('date', '<=', $endDate);
            }

            if ($request->filled('status')) {
                $holidays->where('status', $request->status);
            }

            $holidays->orderBy('date', 'asc');

            return DataTables::of($holidays)

                ->editColumn('date', function ($holiday) {
                    return $holiday->date->format('d/m/Y');
                })

                ->addColumn('day', function ($holiday) {
                    return ucfirst($holiday->date->locale('es')->translatedFormat('l'));
                })

                ->addColumn('status_badge', function ($holiday) {
                    if ($holiday->status) {
                        return '<span class="badge badge-success badge-custom">Activo</span>';
                    }

                    return '<span class="badge badge-danger badge-custom">Inactivo</span>';
                })

                ->addColumn('created_at', function ($holiday) {
                    return $holiday->created_at ? $holiday->created_at->format('d/m/Y H:i') : '-';
                })

                ->addColumn('actions', function ($holiday) {
                    return '
                        <button class="btn btn-sm btn-warning btn-editar" id="' . $holiday->id . '">
                            <i class="fas fa-pen"></i>
                        </button>

                        <button type="button"
                            class="btn btn-sm btn-danger btn-delete"
                            data-url="' . route('admin.holidays.destroy', $holiday->id) . '">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    ';
                })

                ->rawColumns([
                    'status_badge',
                    'actions'
                ])

                ->make(true);
        }

        return view('admin.holidays.index');
    }

    public function stats(Request $request)
    {
        $holidays = Holiday::query();

        if ($request->filled('start_date')) {
            $holidays->whereDate('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $holidays->whereDate('date', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            $holidays->where('status', $request->status);
        }

        return response()->json([
            'total' => (clone $holidays)->count(),
            'active' => (clone $holidays)->where('status', true)->count(),
            'upcoming' => (clone $holidays)
                ->where('status', true)
                ->whereDate('date', '>=', now()->format('Y-m-d'))
                ->count(),
        ]);
    }

    public function create()
    {
        return view('admin.holidays.create');
    }

    public function store(Request $request)
    {
        try {

            $request->validate([
                'date' => 'required|date|unique:holidays,date',
                'description' => 'required|string|max:150',
                'status' => 'required|boolean',
            ], [
                'date.required' => 'La fecha del feriado es obligatoria.',
                'date.unique' => 'Ya existe un feriado registrado en esta fecha.',
                'description.required' => 'La descripción es obligatoria.',
                'status.required' => 'El estado es obligatorio.',
            ]);

            Holiday::create([
                'date' => $request->date,
                'description' => $request->description,
                'status' => $request->status,
            ]);

            return response()->json([
                'message' => 'Feriado registrado correctamente'
            ], 200);

        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Error: ' . $th->getMessage()
            ], 500);
        }
    }

    public function edit(string $id)
    {
        $holiday = Holiday::findOrFail($id);

        return view('admin.holidays.edit', compact('holiday'));
    }

    public function update(Request $request, string $id)
    {
        try {

            $holiday = Holiday::findOrFail($id);

            $request->validate([
                'date' => 'required|date|unique:holidays,date,' . $holiday->id,
                'description' => 'required|string|max:150',
                'status' => 'required|boolean',
            ], [
                'date.required' => 'La fecha del feriado es obligatoria.',
                'date.unique' => 'Ya existe un feriado registrado en esta fecha.',
                'description.required' => 'La descripción es obligatoria.',
                'status.required' => 'El estado es obligatorio.',
            ]);

            $holiday->update([
                'date' => $request->date,
                'description' => $request->description,
                'status' => $request->status,
            ]);

            return response()->json([
                'message' => 'Feriado actualizado correctamente'
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

            $holiday = Holiday::findOrFail($id);
            $holiday->delete();

            return response()->json([
                'message' => 'Feriado eliminado correctamente'
            ], 200);

        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Error en la eliminación: ' . $th->getMessage()
            ], 500);
        }
    }
}