<?php

namespace App\Http\Controllers;

use App\Models\Consume;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ConsumeController extends Controller
{
    public function index()
    {
        return response()->json(
            Consume::with(['tratamiento', 'alimentacion.tipoDieta', 'registradoPor'])
                ->orderBy('fecha', 'desc')
                ->orderBy('tiempo_comida')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tratamiento_id' => 'required|exists:tratamientos,id',
            'alimentacion_id' => 'required|exists:alimentacions,id',
            'tiempo_comida' => 'required|in:Desayuno,Merienda AM,Almuerzo,Merienda PM,Cena',
            'fecha' => 'required|date',
            'porcentaje_consumido' => 'required|integer|min:0|max:100',
            'observaciones' => 'nullable|string',
        ]);

        $data['registrado_por'] = Auth::id();

        $consume = Consume::create($data);
        Log::info('Consumo registrado', ['id' => $consume->id]);

        return response()->json(
            $consume->load(['tratamiento', 'alimentacion', 'registradoPor']),
            201
        );
    }

    public function show($id)
    {
        return response()->json(
            Consume::with(['tratamiento', 'alimentacion.tipoDieta', 'registradoPor'])
                ->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $consume = Consume::findOrFail($id);

        $data = $request->validate([
            'tratamiento_id' => 'required|exists:tratamientos,id',
            'alimentacion_id' => 'required|exists:alimentacions,id',
            'tiempo_comida' => 'required|in:Desayuno,Merienda AM,Almuerzo,Merienda PM,Cena',
            'fecha' => 'required|date',
            'porcentaje_consumido' => 'required|integer|min:0|max:100',
            'observaciones' => 'nullable|string',
        ]);

        $consume->update($data);
        Log::info('Consumo actualizado', ['id' => $consume->id]);

        return response()->json($consume->load(['tratamiento', 'alimentacion', 'registradoPor']));
    }

    public function destroy($id)
    {
        Consume::findOrFail($id)->delete();
        Log::warning('Consumo eliminado', ['id' => $id]);
        return response()->noContent();
    }

    public function porAlimentacionYFecha($alimentacionId, $fecha)
    {
        return response()->json(
            Consume::with(['registradoPor'])
                ->where('alimentacion_id', $alimentacionId)
                ->whereDate('fecha', $fecha)
                // CAMBIO CLAVE: Ordenar por 'created_at' descendente
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    public function registrarDia(Request $request)
    {
        $data = $request->validate([
            'tratamiento_id' => 'required|exists:tratamientos,id',
            'alimentacion_id' => 'required|exists:alimentacions,id',
            'fecha' => 'required|date',
            'consumos' => 'required|array',
            'consumos.*.tiempo_comida' => 'required|in:Desayuno,Merienda AM,Almuerzo,Merienda PM,Cena',
            'consumos.*.porcentaje_consumido' => 'required|integer|min:0|max:100',
            'consumos.*.observaciones' => 'nullable|string',
        ]);

        $consumosCreados = [];

        foreach ($data['consumos'] as $consumoData) {
            $consume = Consume::updateOrCreate(
                [
                    'alimentacion_id' => $data['alimentacion_id'],
                    'fecha' => $data['fecha'],
                    'tiempo_comida' => $consumoData['tiempo_comida'],
                ],
                [
                    'tratamiento_id' => $data['tratamiento_id'],
                    'porcentaje_consumido' => $consumoData['porcentaje_consumido'],
                    'observaciones' => $consumoData['observaciones'] ?? null,
                    'registrado_por' => Auth::id(),
                ]
            );

            $consumosCreados[] = $consume;
        }

        Log::info('Consumos del día registrados', ['cantidad' => count($consumosCreados)]);

        return response()->json($consumosCreados, 201);
    }

    /**
     * Registra o actualiza un único tiempo de comida para un día.
     */
    public function registrarTiempoUnico(Request $request)
    {
        $data = $request->validate([
            'tratamiento_id' => 'required|exists:tratamientos,id',
            'alimentacion_id' => 'required|exists:alimentacions,id',
            'fecha' => 'required|date',
            'tiempo_comida' => 'required|in:Desayuno,Merienda AM,Almuerzo,Merienda PM,Cena',
            'porcentaje_consumido' => 'required|integer|min:0|max:100',
            'observaciones' => 'nullable|string',
        ]);

        $data['registrado_por'] = Auth::id();

        // CAMBIO CLAVE: Siempre creamos un nuevo registro
        $consume = Consume::create($data);

        Log::info('Consumo de tiempo único CREADO', ['id' => $consume->id]);

        // Devolvemos el registro nuevo con el usuario
        return response()->json($consume->load('registradoPor'), 201); // 201 = Created
    }
}
