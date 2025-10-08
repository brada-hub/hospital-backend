<?php

namespace App\Http\Controllers;

use App\Models\Alimentacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AlimentacionController extends Controller
{
    /**
     * Muestra una lista de todos los planes de alimentación.
     */
    public function index()
    {
        // Carga los planes junto con su tipo de dieta asociado
        return Alimentacion::with('tipoDieta')->latest()->get();
    }

    /**
     * Guarda un nuevo plan de alimentación en la base de datos.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'internacion_id' => 'required|exists:internacions,id',
            'tipo_dieta_id'  => 'required|exists:tipos_dieta,id',
            'frecuencia'     => 'required|string|max:100',
            'fecha_inicio'   => 'required|date',
            'fecha_fin'      => 'required|date|after_or_equal:fecha_inicio',
            'descripcion'    => 'required|string|max:255',
        ]);
        $dietaActivaExistente = Alimentacion::where('internacion_id', $data['internacion_id'])
            ->where('estado', 0) // 0 = Activo
            ->exists();
        if ($dietaActivaExistente) {
            // Error 409 Conflict: La solicitud no se pudo completar debido a un conflicto
            return response()->json([
                'message' => 'Ya existe un plan de alimentación activo para este paciente. Suspenda el actual antes de crear uno nuevo.'
            ], 409);
        }

        // Si pasamos la validación, creamos la dieta (por defecto el estado es 0)
        $alimentacion = Alimentacion::create($data);
        // ... Log y resto del código ...
        return response()->json($alimentacion->load('tipoDieta'), 201);
    }

    public function suspender(Request $request, Alimentacion $alimentacion)
    {
        $data = $request->validate([
            'motivo' => 'required|string|min:10',
        ]);

        if ($alimentacion->estado !== 0) {
            return response()->json(['message' => 'Este plan ya no está activo.'], 400);
        }

        $alimentacion->estado = 1; // 1 = Suspendido
        $alimentacion->motivo_suspension = $data['motivo'];
        $alimentacion->save();

        Log::info('Plan de alimentación suspendido', ['id' => $alimentacion->id, 'motivo' => $data['motivo']]);

        return response()->json($alimentacion->load('tipoDieta'), 200);
    }

    /**
     * Muestra un plan de alimentación específico.
     */
    public function show(Alimentacion $alimentacion)
    {
        // Carga la relación 'tipoDieta' antes de devolver la respuesta
        return response()->json($alimentacion->load('tipoDieta'));
    }

    /**
     * Actualiza un plan de alimentación existente.
     */
    public function update(Request $request, Alimentacion $alimentacion)
    {
        $data = $request->validate([
            'internacion_id' => 'required|exists:internacions,id',
            'tipo_dieta_id'  => 'required|exists:tipos_dieta,id',
            'frecuencia'     => 'required|string|max:100',
            'fecha_inicio'   => 'required|date',
            'fecha_fin'      => 'required|date|after_or_equal:fecha_inicio',
            'descripcion'    => 'required|string|max:255',
        ]);

        $alimentacion->update($data);
        Log::info('Plan de alimentación actualizado', ['id' => $alimentacion->id]);

        return response()->json($alimentacion->load('tipoDieta'), 200);
    }

    /**
     * Elimina un plan de alimentación.
     */
    public function destroy(Alimentacion $alimentacion)
    {
        $alimentacion->delete();
        Log::warning('Plan de alimentación eliminado', ['id' => $alimentacion->id]);

        // Devuelve una respuesta vacía con código 204 (No Content)
        return response()->noContent();
    }
}
