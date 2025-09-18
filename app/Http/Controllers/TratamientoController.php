<?php

namespace App\Http\Controllers;

use App\Models\Tratamiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TratamientoController extends Controller
{
    // El método index ahora debe cargar las recetas también
    public function index(Request $request)
    {
        $query = Tratamiento::with(['internacion.paciente', 'recetas.medicamento', 'medico']);

        // Permitir filtrar por internación, que será lo más común
        if ($request->has('internacion_id')) {
            $query->where('internacion_id', $request->internacion_id);
        }

        return $query->latest()->get();
    }

    /**
     * Almacena un nuevo tratamiento junto con todas sus recetas en una sola transacción.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            // Datos del Tratamiento
            'internacion_id' => 'required|exists:internacions,id',
            'tipo'           => 'required|string|max:100',
            'descripcion'    => 'required|string',
            'fecha_inicio'   => 'required|date',
            'fecha_fin'      => 'required|date|after_or_equal:fecha_inicio',
            'observaciones'  => 'nullable|string',

            // Array de Recetas
            'recetas'              => 'required|array|min:1',
            'recetas.*.medicamento_id' => 'required|exists:medicamentos,id',
            'recetas.*.frecuencia_medica' => 'required|string|max:100',
            'recetas.*.concentracion'  => 'required|string|max:100',
            // Puedes quitar las fechas de la receta si son las mismas del tratamiento
        ]);

        try {
            $tratamiento = DB::transaction(function () use ($data) {
                // 1. Crear el Tratamiento "padre"
                $tratamiento = Tratamiento::create([
                    'internacion_id' => $data['internacion_id'],
                    'user_id'        => Auth::id(), // ID del médico autenticado
                    'tipo'           => $data['tipo'],
                    'descripcion'    => $data['descripcion'],
                    'fecha_inicio'   => $data['fecha_inicio'],
                    'fecha_fin'      => $data['fecha_fin'],
                    'observaciones'  => $data['observaciones'],
                ]);

                // 2. Iterar y crear cada Receta "hija"
                foreach ($data['recetas'] as $recetaData) {
                    $tratamiento->recetas()->create([
                        'medicamento_id'   => $recetaData['medicamento_id'],
                        'frecuencia_medica' => $recetaData['frecuencia_medica'],
                        'concentracion'    => $recetaData['concentracion'],
                        // Usamos las fechas del tratamiento principal para consistencia
                        'fecha_inicio'     => $tratamiento->fecha_inicio,
                        'fecha_fin'        => $tratamiento->fecha_fin,
                    ]);
                }

                return $tratamiento;
            });

            Log::info('Tratamiento y recetas creados con éxito', ['tratamiento_id' => $tratamiento->id]);

            // Cargar las relaciones para devolver el objeto completo
            $tratamiento->load(['recetas.medicamento', 'medico']);

            return response()->json([
                'message' => 'Tratamiento prescrito con éxito.',
                'data' => $tratamiento
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error al crear el tratamiento:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error interno al procesar la prescripción.'], 500);
        }
    }

    // El método show ahora debe cargar las relaciones
    public function show($id)
    {
        return Tratamiento::with(['internacion.paciente', 'recetas.medicamento', 'medico'])->findOrFail($id);
    }

    // Los métodos update y destroy se mantienen similares, pero podrías necesitar
    // lógica adicional para actualizar/eliminar recetas asociadas si es necesario.

    public function update(Request $request, $id)
    {
        // ... (La lógica de actualización puede volverse compleja si permites editar recetas aquí)
        // Por ahora, lo mantenemos simple para actualizar solo los datos del tratamiento principal.
        $tratamiento = Tratamiento::findOrFail($id);

        $data = $request->validate([
            'tipo'           => 'sometimes|required|string|max:100',
            'descripcion'    => 'sometimes|required|string',
            'fecha_fin'      => 'sometimes|required|date|after_or_equal:fecha_inicio',
            'observaciones'  => 'nullable|string',
        ]);

        $tratamiento->update($data);
        return response()->json($tratamiento, 200);
    }

    public function destroy($id)
    {
        $tratamiento = Tratamiento::findOrFail($id);
        // Gracias al onDelete('cascade'), al eliminar el tratamiento se eliminarán sus recetas.
        $tratamiento->delete();
        return response()->noContent();
    }
}
