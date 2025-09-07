<?php

namespace App\Http\Controllers;

use App\Models\Administra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdministraController extends Controller
{
    public function index()
    {
        return Administra::with(['receta', 'usuario'])->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'receta_id'    => 'required|exists:recetas,id',
            'usuario_id'   => 'required|exists:usuarios,id',
            'fecha'        => 'required|date',
            'dosis'        => 'required|string|max:50',
            'estado'       => 'required|string|max:50',
            'observaciones'=> 'nullable|string|max:255',
        ]);

        $administra = Administra::create($data);
        Log::info('Dosis administrada registrada', ['id' => $administra->id]);

        return response()->json($administra, 201);
    }

    public function show($id)
    {
        return Administra::with(['receta', 'usuario'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $administra = Administra::findOrFail($id);

        $data = $request->validate([
            'receta_id'    => 'required|exists:recetas,id',
            'usuario_id'   => 'required|exists:usuarios,id',
            'fecha'        => 'required|date',
            'dosis'        => 'required|string|max:50',
            'estado'       => 'required|string|max:50',
            'observaciones'=> 'nullable|string|max:255',
        ]);

        $administra->update($data);
        Log::info('Administración actualizada', ['id' => $administra->id]);

        return response()->json($administra, 200);
    }

    public function destroy($id)
    {
        $administra = Administra::findOrFail($id);
        $administra->delete();

        Log::warning('Administración eliminada', ['id' => $id]);
        return response()->noContent();
    }
}
