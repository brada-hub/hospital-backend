<?php

namespace App\Http\Controllers;

use App\Models\Ocupacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OcupacionController extends Controller
{
    public function index()
    {
        return Ocupacion::with(['internacion', 'cama'])->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'internacion_id'     => 'required|exists:internacions,id',
            'cama_id'            => 'required|exists:camas,id',
            'fecha_ocupacion'    => 'required|date',
            'fecha_desocupacion' => 'nullable|date|after_or_equal:fecha_ocupacion',
            'observaciones'      => 'nullable|string|max:255',
        ]);

        $ocupacion = Ocupacion::create($data);
        Log::info('Ocupación creada', ['id' => $ocupacion->id]);

        return response()->json($ocupacion, 201);
    }

    public function show($id)
    {
        return Ocupacion::with(['internacion', 'cama'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $ocupacion = Ocupacion::findOrFail($id);

        $data = $request->validate([
            'internacion_id'     => 'required|exists:internacions,id',
            'cama_id'            => 'required|exists:camas,id',
            'fecha_ocupacion'    => 'required|date',
            'fecha_desocupacion' => 'nullable|date|after_or_equal:fecha_ocupacion',
            'observaciones'      => 'nullable|string|max:255',
        ]);

        $ocupacion->update($data);
        Log::info('Ocupación actualizada', ['id' => $ocupacion->id]);

        return response()->json($ocupacion, 200);
    }

    public function destroy($id)
    {
        $ocupacion = Ocupacion::findOrFail($id);
        $ocupacion->delete();

        Log::warning('Ocupación eliminada', ['id' => $id]);
        return response()->noContent();
    }
}
