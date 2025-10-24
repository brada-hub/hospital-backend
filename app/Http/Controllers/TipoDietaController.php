<?php

namespace App\Http\Controllers;

use App\Models\TipoDieta;
use Illuminate\Http\Request;

class TipoDietaController extends Controller
{
    public function index()
    {
        return response()->json(TipoDieta::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|unique:tipos_dieta,nombre|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $tipoDieta = TipoDieta::create($data);
        return response()->json($tipoDieta, 201);
    }

    public function show($id)
    {
        return response()->json(TipoDieta::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $tipoDieta = TipoDieta::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'required|string|max:255|unique:tipos_dieta,nombre,' . $id,
            'descripcion' => 'nullable|string',
        ]);

        $tipoDieta->update($data);
        return response()->json($tipoDieta);
    }

    public function destroy($id)
    {
        TipoDieta::findOrFail($id)->delete();
        return response()->noContent();
    }
}
