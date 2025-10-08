<?php

namespace App\Http\Controllers;

use App\Models\Consume;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConsumeController extends Controller
{
    public function index()
    {
        return Consume::with(['tratamiento', 'alimentacion.tipoDieta'])->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tratamiento_id'  => 'required|exists:tratamientos,id',
            'alimentacion_id' => 'required|exists:alimentacions,id',
            'observaciones'   => 'nullable|string|max:255',
            'fecha'           => 'required|date',
            // CAMBIO: Se valida el porcentaje en lugar de un booleano
            'porcentaje_consumido' => 'required|integer|min:0|max:100',
        ]);

        $consume = Consume::create($data);
        Log::info('Consume creado', ['id' => $consume->id]);
        return response()->json($consume, 201);
    }

    public function show($id)
    {
        return Consume::with(['tratamiento', 'alimentacion.tipoDieta'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $consume = Consume::findOrFail($id);
        $data = $request->validate([
            'tratamiento_id'  => 'required|exists:tratamientos,id',
            'alimentacion_id' => 'required|exists:alimentacions,id',
            'observaciones'   => 'nullable|string|max:255',
            'fecha'           => 'required|date',
            'porcentaje_consumido' => 'required|integer|min:0|max:100',
        ]);

        $consume->update($data);
        Log::info('Consume actualizado', ['id' => $consume->id]);
        return response()->json($consume, 200);
    }

    public function destroy($id)
    {
        Consume::findOrFail($id)->delete();
        Log::warning('Consume eliminado', ['id' => $id]);
        return response()->noContent();
    }
}
