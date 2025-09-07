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
            'nombre'   => 'required|string|max:100|unique:camas,nombre',
            'tipo'     => 'required|string|max:50',
            'estado'   => 'required|string|max:50',
            'sala_id'  => 'required|exists:salas,id',
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
            'nombre'   => 'required|string|max:100|unique:camas,nombre,' . $cama->id,
            'tipo'     => 'required|string|max:50',
            'estado'   => 'required|string|max:50',
            'sala_id'  => 'required|exists:salas,id',
        ]);

        $cama->update($data);
        Log::info('Cama actualizada', ['id' => $cama->id]);

        return response()->json($cama, 200);
    }

    public function destroy($id)
    {
        $cama = Cama::findOrFail($id);
        $cama->delete();

        Log::warning('Cama eliminada', ['id' => $id]);
        return response()->noContent();
    }
}
