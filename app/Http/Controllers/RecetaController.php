<?php

namespace App\Http\Controllers;

use App\Models\Receta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class RecetaController extends Controller
{
    public function index()
    {
        return Receta::with(['tratamiento', 'medicamento'])->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tratamiento_id'    => 'required|exists:tratamientos,id',
            'medicamento_id'    => 'required|exists:medicamentos,id',
            'dosis'             => 'required|string|max:100',
            'frecuencia_horas'  => 'required|integer|min:1',
            'duracion_dias'     => 'required|integer|min:1',
            'indicaciones'      => 'nullable|string',
        ]);

        $receta = Receta::create($data);
        Log::info('Receta registrada', ['id' => $receta->id]);

        return response()->json($receta->load('medicamento'), 201);
    }

    public function show($id)
    {
        return Receta::with(['tratamiento', 'medicamento'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $receta = Receta::findOrFail($id);

        $data = $request->validate([
            'dosis'             => 'sometimes|required|string|max:100',
            'frecuencia_horas'  => 'sometimes|required|integer|min:1',
            'duracion_dias'     => 'sometimes|required|integer|min:1',
            'indicaciones'      => 'nullable|string',
        ]);

        $receta->update($data);
        Log::info('Receta actualizada', ['id' => $receta->id]);

        return response()->json($receta, 200);
    }

    public function destroy($id)
    {
        $receta = Receta::findOrFail($id);
        $receta->delete();

        Log::warning('Receta eliminada', ['id' => $id]);
        return response()->noContent();
    }
    public function suspender(Request $request, Receta $receta)
    {
        if ($receta->estado !== 0) {
            return response()->json(['message' => 'Esta receta no está activa.'], 409);
        }

        $data = $request->validate([
            'motivo' => 'required|string|min:10|max:255'
        ]);

        $nuevasIndicaciones = trim($receta->indicaciones . "\n\nSUSPENDIDO (" . now()->format('d/m/Y') . "): " . $data['motivo']);

        $receta->update([
            'estado' => 1, // 1 = Suspendido
            'indicaciones' => $nuevasIndicaciones
        ]);

        Log::warning('Receta suspendida', [
            'id' => $receta->id,
            'motivo' => $data['motivo'],
            'user_id' => Auth::id() // Ahora PHP sabrá qué es "Auth"
        ]);

        return response()->json([
            'message' => 'Medicamento suspendido con éxito.',
            'data' => $receta
        ]);
    }
}
