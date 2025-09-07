<?php

namespace App\Http\Controllers;

use App\Models\Valor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ValorController extends Controller
{
    public function index()
    {
        return Valor::with(['control', 'signo'])->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'control_id' => 'required|exists:controls,id',
            'signo_id'   => 'required|exists:signos,id',
            'medida'     => 'required|numeric',
        ]);

        $valor = Valor::create($data);
        Log::info('Valor creado', ['id' => $valor->id]);

        return response()->json($valor, 201);
    }

    public function show($id)
    {
        return Valor::with(['control', 'signo'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $valor = Valor::findOrFail($id);

        $data = $request->validate([
            'control_id' => 'required|exists:controls,id',
            'signo_id'   => 'required|exists:signos,id',
            'medida'     => 'required|numeric',
        ]);

        $valor->update($data);
        Log::info('Valor actualizado', ['id' => $valor->id]);

        return response()->json($valor, 200);
    }

    public function destroy($id)
    {
        $valor = Valor::findOrFail($id);
        $valor->delete();

        Log::warning('Valor eliminado', ['id' => $id]);
        return response()->noContent();
    }
}
