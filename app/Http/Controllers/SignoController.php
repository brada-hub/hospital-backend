<?php

namespace App\Http\Controllers;

use App\Models\Signo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SignoController extends Controller
{
    public function index()
    {
        return Signo::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100|unique:signos,nombre',
            'unidad' => 'required|string|max:50',
        ]);

        $signo = Signo::create($data);
        Log::info('Signo registrado', ['id' => $signo->id]);

        return response()->json($signo, 201);
    }

    public function show($id)
    {
        return Signo::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $signo = Signo::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'required|string|max:100|unique:signos,nombre,' . $signo->id,
            'unidad' => 'required|string|max:50',
        ]);

        $signo->update($data);
        Log::info('Signo actualizado', ['id' => $signo->id]);

        return response()->json($signo, 200);
    }

    public function destroy($id)
    {
        $signo = Signo::findOrFail($id);
        $signo->delete();

        Log::warning('Signo eliminado', ['id' => $id]);
        return response()->noContent();
    }
}
