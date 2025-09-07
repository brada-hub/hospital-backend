<?php

namespace App\Http\Controllers;

use App\Models\Cuidado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CuidadoController extends Controller
{
    public function index()
    {
        return Cuidado::with('internacion')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'internacion_id' => 'required|exists:internacions,id',
            'tipo'           => 'required|string|max:100',
            'descripcion'    => 'required|string|max:255',
            'fecha_inicio'   => 'required|date',
            'fecha_fin'      => 'required|date|after_or_equal:fecha_inicio',
            'frecuencia'     => 'required|string|max:100',
            'estado'         => 'required|string|max:50',
        ]);

        $cuidado = Cuidado::create($data);
        Log::info('Cuidado registrado', ['id' => $cuidado->id]);

        return response()->json($cuidado, 201);
    }

    public function show($id)
    {
        return Cuidado::with('internacion')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $cuidado = Cuidado::findOrFail($id);

        $data = $request->validate([
            'internacion_id' => 'required|exists:internacions,id',
            'tipo'           => 'required|string|max:100',
            'descripcion'    => 'required|string|max:255',
            'fecha_inicio'   => 'required|date',
            'fecha_fin'      => 'required|date|after_or_equal:fecha_inicio',
            'frecuencia'     => 'required|string|max:100',
            'estado'         => 'required|string|max:50',
        ]);

        $cuidado->update($data);
        Log::info('Cuidado actualizado', ['id' => $cuidado->id]);

        return response()->json($cuidado, 200);
    }

    public function destroy($id)
    {
        $cuidado = Cuidado::findOrFail($id);
        $cuidado->delete();

        Log::warning('Cuidado eliminado', ['id' => $id]);
        return response()->noContent();
    }
}
