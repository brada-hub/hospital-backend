<?php

namespace App\Http\Controllers;

use App\Models\Tratamiento;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TratamientoController extends Controller
{
    public function index(Request $request)
    {
        $query = Tratamiento::with(['internacion.paciente', 'recetas.medicamento', 'medico']);

        if ($request->has('internacion_id')) {
            $query->where('internacion_id', $request->internacion_id);
        }

        return $query->latest()->get();
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'internacion_id' => 'required|exists:internacions,id',
            'tipo' => 'required|string|max:100',
            'descripcion' => 'required|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'observaciones' => 'nullable|string',
            // ✅ CORREGIDO: Ahora se espera un número entero
            'estado' => 'sometimes|integer|in:0,1,2,3',
            'recetas' => 'required|array|min:1',
            'recetas.*.medicamento_id' => 'required|exists:medicamentos,id',
            'recetas.*.dosis' => 'required|string|max:100',
            'recetas.*.via_administracion' => 'required|string|max:100',
            'recetas.*.frecuencia_horas' => 'required|integer|min:1',
            'recetas.*.duracion_dias' => 'required|integer|min:1',
            'recetas.*.indicaciones' => 'nullable|string',
        ]);

        try {
            $tratamiento = DB::transaction(function () use ($data) {
                $tratamientoData = collect($data)->except('recetas')->toArray();
                $tratamientoData['user_id'] = Auth::id();

                $tratamiento = Tratamiento::create($tratamientoData);
                $tratamiento->recetas()->createMany($data['recetas']);

                return $tratamiento;
            });

            Log::info('Tratamiento creado', ['id' => $tratamiento->id, 'user_id' => Auth::id()]);
            return response()->json($tratamiento->load(['recetas.medicamento', 'medico']), 201);
        } catch (\Exception $e) {
            Log::error('Error al crear tratamiento:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error interno al procesar la prescripción.'], 500);
        }
    }

    public function show(Tratamiento $tratamiento)
    {
        return $tratamiento->load(['internacion.paciente', 'recetas.medicamento', 'medico']);
    }

    public function update(Request $request, Tratamiento $tratamiento)
    {
        $data = $request->validate([
            'tipo' => 'sometimes|required|string|max:100',
            'descripcion' => 'sometimes|required|string',
            'fecha_fin' => 'sometimes|required|date',
            'observaciones' => 'nullable|string',
            'estado' => 'sometimes|integer|in:0,1,2,3',
            'recetas' => 'present|array',
            'recetas.*.id' => 'nullable|integer',
            'recetas.*.medicamento_id' => 'required|exists:medicamentos,id',
            'recetas.*.dosis' => 'required|string|max:100',
            'recetas.*.via_administracion' => 'required|string|max:100',
            'recetas.*.frecuencia_horas' => 'required|integer|min:1',
            'recetas.*.duracion_dias' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($tratamiento, $data) {
                $tratamiento->update(collect($data)->except('recetas')->toArray());

                $recetasIdsEntrantes = collect($data['recetas'])->pluck('id')->filter();
                $recetasIdsActuales = $tratamiento->recetas()->pluck('id');

                $idsParaBorrar = $recetasIdsActuales->diff($recetasIdsEntrantes);
                if ($idsParaBorrar->isNotEmpty()) {
                    $tratamiento->recetas()->whereIn('id', $idsParaBorrar)->delete();
                }

                foreach ($data['recetas'] as $recetaData) {
                    if (isset($recetaData['id']) && $recetasIdsActuales->contains($recetaData['id'])) {
                        $receta = $tratamiento->recetas()->find($recetaData['id']);
                        $receta->update($recetaData);
                    } else {
                        $tratamiento->recetas()->create($recetaData);
                    }
                }
            });

            Log::info('Tratamiento actualizado', ['id' => $tratamiento->id, 'user_id' => Auth::id()]);
            return response()->json($tratamiento->fresh()->load('recetas.medicamento'));
        } catch (\Exception $e) {
            Log::error('Error al actualizar tratamiento:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error interno al actualizar.'], 500);
        }
    }

    public function suspender(Request $request, Tratamiento $tratamiento)
    {
        if ($tratamiento->estado !== 0) {
            return response()->json(['message' => 'Este tratamiento no está activo.'], 409);
        }

        $data = $request->validate(['motivo' => 'required|string|min:10']);
        $nuevasObservaciones = trim($tratamiento->observaciones . "\n\nSUSPENDIDO (" . now()->format('d/m/Y') . "): " . $data['motivo']);

        $tratamiento->update([
            'estado' => 1, // Usamos el número 1 para 'suspendido'
            'observaciones' => $nuevasObservaciones
        ]);

        // ✅ CORREGIDO: Usamos Auth::id() en lugar de auth()->id()
        Log::warning('Tratamiento suspendido', ['id' => $tratamiento->id, 'motivo' => $data['motivo'], 'user_id' => Auth::id()]);

        return response()->json(['message' => 'Tratamiento suspendido con éxito.', 'data' => $tratamiento->fresh()]);
    }
    public function destroy(Tratamiento $tratamiento)
    {
        $tratamiento->delete();
        Log::warning('Tratamiento eliminado', ['id' => $tratamiento->id, 'user_id' => Auth::id()]);
        return response()->noContent();
    }
}
