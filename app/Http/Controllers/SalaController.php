<?php

namespace App\Http\Controllers;

use App\Models\Sala;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class SalaController extends Controller
{
    public function index()
    {
        // ESTA ES LA VERSIÓN A PRUEBA DE BALAS
        // Se asegura de traer las salas con su especialidad y filtra solo las activas.
        return Sala::with('especialidad')
            ->where('estado', 1) // Filtra explícitamente solo las salas activas
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'          => 'required|string|max:100|unique:salas,nombre',
            'tipo'            => 'required|string|max:50',
            // CAMBIADO: La validación ahora es para un booleano (acepta 1, 0, true, false).
            'estado'          => 'required|boolean',
            'especialidad_id' => 'required|exists:especialidads,id',
        ]);

        $sala = Sala::create($data);
        Log::info('Sala registrada', ['id' => $sala->id]);

        return response()->json($sala, 201);
    }

    public function show($id)
    {
        return Sala::with(['especialidad', 'camas'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $sala = Sala::findOrFail($id);

        $data = $request->validate([
            'nombre'          => 'required|string|max:100|unique:salas,nombre,' . $sala->id,
            'tipo'            => 'required|string|max:50',
            // CAMBIADO: La validación ahora es para un booleano.
            'estado'          => 'required|boolean',
            'especialidad_id' => 'required|exists:especialidads,id',
        ]);

        $sala->update($data);
        Log::info('Sala actualizada', ['id' => $sala->id]);

        return response()->json($sala, 200);
    }

    public function destroy(Sala $sala)
    {
        // CAMBIADO: La lógica para alternar el estado es ahora súper simple.
        $sala->update(['estado' => !$sala->estado]);

        return response()->json([
            'message' => 'Estado de la sala actualizado',
            'sala'    => $sala
        ], 200);
    }
}
