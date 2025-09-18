<?php

namespace App\Http\Controllers;

use App\Models\Cama;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CamaController extends Controller
{
    public function index()
    {
        return Cama::with('sala')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'         => 'required|string|max:100|unique:camas,nombre',
            'tipo'           => 'required|string|max:50',
            // CAMBIADO: Validación para un booleano (0 o 1).
            'estado'         => 'required|boolean',
            // AÑADIDO: Validación para 'disponibilidad', solo acepta 0, 1 o 2.
            'disponibilidad' => 'required|integer|in:0,1,2',
            'sala_id'        => 'required|exists:salas,id',
        ]);

        $cama = Cama::create($data);
        Log::info('Cama registrada', ['id' => $cama->id]);

        return response()->json($cama, 201);
    }

    public function show($id)
    {
        return Cama::with('sala')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $cama = Cama::findOrFail($id);

        $data = $request->validate([
            'nombre'         => 'required|string|max:100|unique:camas,nombre,' . $cama->id,
            'tipo'           => 'required|string|max:50',
            // CAMBIADO: Validación para un booleano.
            'estado'         => 'required|boolean',
            // AÑADIDO: Validación para 'disponibilidad'.
            'disponibilidad' => 'required|integer|in:0,1,2',
            'sala_id'        => 'required|exists:salas,id',
        ]);

        $cama->update($data);
        Log::info('Cama actualizada', ['id' => $cama->id]);

        return response()->json($cama, 200);
    }

    public function destroy($id)
    {
        $cama = Cama::findOrFail($id);

        // CAMBIADO: En lugar de borrar, ahora alterna el estado (activo/inactivo).
        $cama->update(['estado' => !$cama->estado]);

        Log::warning('Estado de la cama actualizado', ['id' => $id]);

        return response()->json([
            'message' => 'Estado de la cama actualizado',
            'cama' => $cama
        ], 200);
    }
    public function getDisponibles(Request $request)
    {
        $request->validate([
            // La validación ahora es sobre el ID de la sala
            'sala_id' => 'nullable|integer|exists:salas,id'
        ]);

        $query = Cama::query();
        $query->where('estado', 1)->where('disponibilidad', 1);

        // --- CORRECCIÓN CLAVE ---
        // Si la petición incluye una sala_id, filtramos las camas por esa sala.
        if ($request->has('sala_id')) {
            $query->where('sala_id', $request->sala_id);
        }

        // Ya no necesitamos cargar la especialidad aquí, es más simple
        return $query->with('sala')->get();
    }
}
