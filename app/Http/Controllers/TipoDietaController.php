<?php

namespace App\Http\Controllers;

use App\Models\TipoDieta;
use Illuminate\Http\Request;

class TipoDietaController extends Controller
{
    public function index()
    {
        return TipoDieta::all();
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

    // Aquí puedes agregar los métodos show, update y destroy si los necesitas
}
