<?php

namespace App\Http\Controllers;

use App\Models\Medicamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MedicamentoController extends Controller
{
    public function index()
    {
        return Medicamento::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'             => 'required|string|max:100|unique:medicamentos,nombre',
            'descripcion'        => 'required|string|max:255',
            'presentacion'       => 'required|string|max:100',
            'via_administracion' => 'required|string|max:100',
        ]);

        $medicamento = Medicamento::create($data);
        Log::info('Medicamento registrado', ['id' => $medicamento->id]);

        return response()->json($medicamento, 201);
    }

    public function show($id)
    {
        return Medicamento::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $medicamento = Medicamento::findOrFail($id);

        $data = $request->validate([
            'nombre'             => 'required|string|max:100|unique:medicamentos,nombre,' . $medicamento->id,
            'descripcion'        => 'required|string|max:255',
            'presentacion'       => 'required|string|max:100',
            'via_administracion' => 'required|string|max:100',
        ]);

        $medicamento->update($data);
        Log::info('Medicamento actualizado manualmente', ['id' => $medicamento->id]);

        return response()->json($medicamento, 200);
    }

    public function destroy($id)
    {
        $medicamento = Medicamento::findOrFail($id);
        $medicamento->delete();

        Log::warning('Medicamento eliminado', ['id' => $id]);
        return response()->noContent();
    }
}
