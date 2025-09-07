<?php

namespace App\Http\Controllers;

use App\Models\Sala;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SalaController extends Controller
{
    public function index()
    {
        return Sala::with('especialidad')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'           => 'required|string|max:100|unique:salas,nombre',
            'tipo'             => 'required|string|max:50',
            'estado'           => 'required|string|max:50',
            'especialidad_id'  => 'required|exists:especialidads,id',
        ]);

        $sala = Sala::create($data);
        Log::info('Sala registrada', ['id' => $sala->id]);

        return response()->json($sala, 201);
    }

    public function show($id)
    {
        return Sala::with('especialidad')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $sala = Sala::findOrFail($id);

        $data = $request->validate([
            'nombre'           => 'required|string|max:100|unique:salas,nombre,' . $sala->id,
            'tipo'             => 'required|string|max:50',
            'estado'           => 'required|string|max:50',
            'especialidad_id'  => 'required|exists:especialidads,id',
        ]);

        $sala->update($data);
        Log::info('Sala actualizada', ['id' => $sala->id]);

        return response()->json($sala, 200);
    }

    public function destroy($id)
    {
        $sala = Sala::findOrFail($id);
        $sala->delete();

        Log::warning('Sala eliminada', ['id' => $id]);
        return response()->noContent();
    }
}
