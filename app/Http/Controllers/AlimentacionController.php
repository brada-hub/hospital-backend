<?php

namespace App\Http\Controllers;

use App\Models\Alimentacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AlimentacionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'internacion_id' => 'required|exists:internacions,id',
            'tipo_dieta_id' => 'required|exists:tipos_dieta,id',
            'via_administracion' => 'required|in:Oral,Enteral,Parenteral',
            'frecuencia_tiempos' => 'required|integer|min:1|max:5',
            'tiempos' => 'required|array|min:1',
            'tiempos.*.tiempo_comida' => 'required|in:Desayuno,Merienda AM,Almuerzo,Merienda PM,Cena',
            'tiempos.*.descripcion' => 'nullable|string',
            'restricciones' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
        ]);

        $alimentacion = Alimentacion::create([
            'internacion_id' => $data['internacion_id'],
            'tipo_dieta_id' => $data['tipo_dieta_id'],
            'via_administracion' => $data['via_administracion'],
            'frecuencia_tiempos' => $data['frecuencia_tiempos'],
            'restricciones' => $data['restricciones'],
            'descripcion' => $data['descripcion'],
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
            'estado' => 0,
        ]);

        foreach ($data['tiempos'] as $index => $tiempo) {
            $alimentacion->tiempos()->create([
                'tiempo_comida' => $tiempo['tiempo_comida'],
                'descripcion' => $tiempo['descripcion'] ?? null,
                'orden' => $index + 1,
            ]);
        }

        return response()->json($alimentacion->load('tiempos'), 201);
    }

    public function index()
    {
        $alimentaciones = Alimentacion::with(['internacion', 'tipoDieta', 'tiempos'])
            ->get();
        return response()->json($alimentaciones);
    }

    public function show($id)
    {
        return response()->json(
            Alimentacion::with(['internacion', 'tipoDieta', 'tiempos', 'consumes.registradoPor'])
                ->findOrFail($id)
        );
    }

    // <CHANGE> Corregir método update para manejar tiempos correctamente
    public function update(Request $request, $id)
    {
        $alimentacion = Alimentacion::findOrFail($id);

        $data = $request->validate([
            'internacion_id' => 'required|exists:internacions,id',
            'tipo_dieta_id' => 'required|exists:tipos_dieta,id',
            'via_administracion' => 'required|in:Oral,Enteral,Parenteral',
            'frecuencia_tiempos' => 'required|integer|min:1|max:5',
            'tiempos' => 'required|array|min:1',
            'tiempos.*.tiempo_comida' => 'required|in:Desayuno,Merienda AM,Almuerzo,Merienda PM,Cena',
            'tiempos.*.descripcion' => 'nullable|string',
            'restricciones' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
        ]);

        $alimentacion->update([
            'internacion_id' => $data['internacion_id'],
            'tipo_dieta_id' => $data['tipo_dieta_id'],
            'via_administracion' => $data['via_administracion'],
            'frecuencia_tiempos' => $data['frecuencia_tiempos'],
            'restricciones' => $data['restricciones'],
            'descripcion' => $data['descripcion'],
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
        ]);

        // Eliminar tiempos existentes y crear nuevos
        $alimentacion->tiempos()->delete();

        foreach ($data['tiempos'] as $index => $tiempo) {
            $alimentacion->tiempos()->create([
                'tiempo_comida' => $tiempo['tiempo_comida'],
                'descripcion' => $tiempo['descripcion'] ?? null,
                'orden' => $index + 1,
            ]);
        }

        Log::info('Alimentación actualizada', ['id' => $alimentacion->id]);

        return response()->json($alimentacion->load(['internacion', 'tipoDieta', 'tiempos']));
    }

    public function destroy($id)
    {
        Alimentacion::findOrFail($id)->delete();
        Log::warning('Alimentación eliminada', ['id' => $id]);
        return response()->noContent();
    }

    public function suspender(Request $request, $id)
    {
        $alimentacion = Alimentacion::findOrFail($id);

        $data = $request->validate([
            'motivo_suspension' => 'required|string',
        ]);

        $alimentacion->suspender($data['motivo_suspension']);
        Log::info('Alimentación suspendida', ['id' => $alimentacion->id]);

        return response()->json($alimentacion);
    }

    public function porInternacion($internacionId)
    {
        // Devuelve TODAS las dietas (activas y suspendidas) para mostrar historial
        return response()->json(
            Alimentacion::with(['tipoDieta', 'tiempos', 'consumes'])
                ->where('internacion_id', $internacionId)
                ->orderByDesc('fecha_inicio')
                ->get()
        );
    }
}
