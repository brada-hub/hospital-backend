<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EspecialidadController extends Controller
{
    public function index()
    {
        return Especialidad::with('hospital')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:100|unique:especialidads,nombre',
            'descripcion' => 'nullable|string|max:255',
            // CAMBIADO: La validación ahora es para un booleano (acepta 1, 0, true, false).
            'estado'      => 'required|boolean',
            'hospital_id' => 'required|exists:hospitals,id',
        ]);

        $especialidad = Especialidad::create($data);
        Log::info('Especialidad registrada', ['id' => $especialidad->id]);

        return response()->json($especialidad, 201);
    }

    public function show($id)
    {
        return Especialidad::with('hospital')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $especialidad = Especialidad::findOrFail($id);

        $data = $request->validate([
            'nombre'      => 'required|string|max:100|unique:especialidads,nombre,' . $especialidad->id,
            'descripcion' => 'nullable|string|max:255',
            // CAMBIADO: La validación ahora es para un booleano.
            'estado'      => 'required|boolean',
            'hospital_id' => 'required|exists:hospitals,id',
        ]);

        $especialidad->update($data);
        Log::info('Especialidad actualizada', ['id' => $especialidad->id]);

        return response()->json($especialidad, 200);
    }

    public function destroy($id)
    {
        $especialidad = Especialidad::findOrFail($id);

        // CAMBIADO: En lugar de borrar, ahora alterna el estado (activo/inactivo).
        $especialidad->update(['estado' => !$especialidad->estado]);

        Log::warning('Estado de la especialidad actualizado', ['id' => $id]);

        return response()->json([
            'message' => 'Estado de la especialidad actualizado',
            'especialidad' => $especialidad,
        ], 200);
    }
}
