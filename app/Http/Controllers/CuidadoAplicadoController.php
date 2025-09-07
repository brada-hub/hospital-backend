<?php

namespace App\Http\Controllers;

use App\Models\CuidadoAplicado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CuidadoAplicadoController extends Controller
{
    public function index()
    {
        return CuidadoAplicado::with(['usuario', 'cuidado'])->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'usuario_id'       => 'required|exists:usuarios,id',
            'cuidado_id'       => 'required|exists:cuidados,id',
            'fecha_aplicacion' => 'required|date',
            'estado'           => 'required|string|max:50',
            'observaciones'    => 'nullable|string|max:255',
        ]);

        $registro = CuidadoAplicado::create($data);
        Log::info('Cuidado aplicado registrado', ['id' => $registro->id]);

        return response()->json($registro, 201);
    }

    public function show($id)
    {
        return CuidadoAplicado::with(['usuario', 'cuidado'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $registro = CuidadoAplicado::findOrFail($id);

        $data = $request->validate([
            'usuario_id'       => 'required|exists:usuarios,id',
            'cuidado_id'       => 'required|exists:cuidados,id',
            'fecha_aplicacion' => 'required|date',
            'estado'           => 'required|string|max:50',
            'observaciones'    => 'nullable|string|max:255',
        ]);

        $registro->update($data);
        Log::info('Cuidado aplicado actualizado', ['id' => $registro->id]);

        return response()->json($registro, 200);
    }

    public function destroy($id)
    {
        $registro = CuidadoAplicado::findOrFail($id);
        $registro->delete();

        Log::warning('Cuidado aplicado eliminado', ['id' => $id]);
        return response()->noContent();
    }
}
