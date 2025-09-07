<?php

namespace App\Http\Controllers;

use App\Models\Alimentacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AlimentacionController extends Controller
{
    public function index()
    {
        return Alimentacion::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo_dieta'   => 'required|string|max:100',
            'frecuencia'   => 'required|string|max:100',
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
            'descripcion'  => 'required|string|max:255',
        ]);

        $alimentacion = Alimentacion::create($data);
        Log::info('Alimentación registrada', ['id' => $alimentacion->id]);

        return response()->json($alimentacion, 201);
    }

    public function show($id)
    {
        return Alimentacion::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $alimentacion = Alimentacion::findOrFail($id);

        $data = $request->validate([
            'tipo_dieta'   => 'required|string|max:100',
            'frecuencia'   => 'required|string|max:100',
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
            'descripcion'  => 'required|string|max:255',
        ]);

        $alimentacion->update($data);
        Log::info('Alimentación actualizada', ['id' => $alimentacion->id]);

        return response()->json($alimentacion, 200);
    }

    public function destroy($id)
    {
        $alimentacion = Alimentacion::findOrFail($id);
        $alimentacion->delete();

        Log::warning('Alimentación eliminada', ['id' => $id]);
        return response()->noContent();
    }
}
