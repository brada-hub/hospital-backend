<?php

namespace App\Http\Controllers;

use App\Models\Tratamiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TratamientoController extends Controller
{
    public function index()
    {
        return Tratamiento::with('internacion')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo'           => 'required|string|max:100',
            'descripcion'    => 'required|string|max:255',
            'fecha_inicio'   => 'required|date',
            'fecha_fin'      => 'required|date|after_or_equal:fecha_inicio',
            'observaciones'  => 'nullable|string|max:255',
            'internacion_id' => 'required|exists:internacions,id',
        ]);

        $tratamiento = Tratamiento::create($data);
        Log::info('Tratamiento registrado', ['id' => $tratamiento->id]);

        return response()->json($tratamiento, 201);
    }

    public function show($id)
    {
        return Tratamiento::with('internacion')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $tratamiento = Tratamiento::findOrFail($id);

        $data = $request->validate([
            'tipo'           => 'required|string|max:100',
            'descripcion'    => 'required|string|max:255',
            'fecha_inicio'   => 'required|date',
            'fecha_fin'      => 'required|date|after_or_equal:fecha_inicio',
            'observaciones'  => 'nullable|string|max:255',
            'internacion_id' => 'required|exists:internacions,id',
        ]);

        $tratamiento->update($data);
        Log::info('Tratamiento actualizado', ['id' => $tratamiento->id]);

        return response()->json($tratamiento, 200);
    }

    public function destroy($id)
    {
        $tratamiento = Tratamiento::findOrFail($id);
        $tratamiento->delete();

        Log::warning('Tratamiento eliminado', ['id' => $id]);
        return response()->noContent();
    }
}
