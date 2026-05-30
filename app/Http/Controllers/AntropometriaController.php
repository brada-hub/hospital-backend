<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Antropometria;
use App\Models\Internacion;
use Illuminate\Support\Facades\Validator;

class AntropometriaController extends Controller
{
    /**
     * Obtener el registro antropométrico de una internación.
     */
    public function show($internacionId)
    {
        $antropometria = Antropometria::where('internacion_id', $internacionId)->first();

        if (!$antropometria) {
            return response()->json(['message' => 'No se encontró registro antropométrico.'], 404);
        }

        return response()->json($antropometria);
    }

    /**
     * Guardar o actualizar el registro antropométrico de una internación.
     */
    public function store(Request $request, $internacionId)
    {
        $internacion = Internacion::find($internacionId);
        if (!$internacion) {
            return response()->json(['message' => 'Expediente de internación no encontrado.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'peso' => 'required|numeric|min:0.5|max:500',
            'altura' => 'required|numeric|min:10|max:300',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $antropometria = Antropometria::updateOrCreate(
            ['internacion_id' => $internacionId],
            [
                'peso' => $request->peso,
                'altura' => $request->altura,
                'observaciones' => $request->observaciones,
            ]
        );

        return response()->json([
            'message' => 'Datos antropométricos registrados exitosamente.',
            'antropometria' => $antropometria
        ]);
    }
}
