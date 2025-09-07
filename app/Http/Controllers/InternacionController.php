<?php

namespace App\Http\Controllers;

use App\Models\Internacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InternacionController extends Controller
{
    public function index()
    {
        return Internacion::with('paciente')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha_ingreso'  => 'required|date',
            'fecha_alta'     => 'nullable|date|after_or_equal:fecha_ingreso',
            'motivo'         => 'required|string|max:100',
            'diagnostico'    => 'required|string|max:255',
            'observaciones'  => 'nullable|string|max:255',
            'paciente_id'    => 'required|exists:pacientes,id',
        ]);

        $internacion = Internacion::create($data);
        Log::info('Internación registrada', ['id' => $internacion->id]);

        return response()->json($internacion, 201);
    }

    public function show($id)
    {
        return Internacion::with('paciente')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $internacion = Internacion::findOrFail($id);

        $data = $request->validate([
            'fecha_ingreso'  => 'required|date',
            'fecha_alta'     => 'nullable|date|after_or_equal:fecha_ingreso',
            'motivo'         => 'required|string|max:100',
            'diagnostico'    => 'required|string|max:255',
            'observaciones'  => 'nullable|string|max:255',
            'paciente_id'    => 'required|exists:pacientes,id',
        ]);

        $internacion->update($data);
        Log::info('Internación actualizada', ['id' => $internacion->id]);

        return response()->json($internacion, 200);
    }

    public function destroy($id)
    {
        $internacion = Internacion::findOrFail($id);
        $internacion->delete();

        Log::warning('Internación eliminada', ['id' => $id]);
        return response()->noContent();
    }
}
