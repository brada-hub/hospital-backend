<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule; // <-- Asegúrate de importar esto

class EspecialidadController extends Controller
{
    public function index(Request $request)
    {
        // MEJORADO: Devuelve solo las especialidades del hospital del usuario.
        $hospitalId = $request->user()->hospital_id;
        return Especialidad::where('hospital_id', $hospitalId)->orderBy('nombre')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            // REGLAS MEJORADAS
            'nombre'      => [
                'required',
                'string',
                'max:100',
                // El nombre debe ser único, pero solo dentro del mismo hospital.
                Rule::unique('especialidads')->where('hospital_id', $request->hospital_id),
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s-]+$/'
            ],
            'descripcion' => 'nullable|string|max:255',
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
            // REGLAS MEJORADAS
            'nombre'      => [
                'required',
                'string',
                'max:100',
                // Al actualizar, ignora el registro actual en la validación unique.
                Rule::unique('especialidads')->where('hospital_id', $especialidad->hospital_id)->ignore($especialidad->id),
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s-]+$/'
            ],
            'descripcion' => 'nullable|string|max:255',
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

        // AÑADIDO: Verificamos si tiene salas activas antes de desactivar.
        if ($especialidad->salas()->where('estado', 1)->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar. La especialidad tiene salas activas asignadas.'
            ], 409); // 409 Conflict
        }

        $especialidad->update(['estado' => false]);
        Log::warning('Especialidad desactivada (soft delete)', ['id' => $id]);

        return response()->json([
            'message' => 'Especialidad eliminada correctamente',
        ], 200);
    }
}
