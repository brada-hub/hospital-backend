<?php

namespace App\Http\Controllers;

use App\Models\Medicamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MedicamentoController extends Controller
{
    public function index()
    {
        // MEJORA: Usamos with('categoria') para cargar la relación y ser más eficientes.
        return Medicamento::with('categoria')->orderBy('nombre')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'             => 'required|string|max:100|unique:medicamentos,nombre',
            'descripcion'        => 'required|string|max:255',

            // MEJORA: Se añade la validación para que la categoría exista.
            'categoria_id'       => 'nullable|exists:medicamento_categorias,id',
        ]);

        $medicamento = Medicamento::create($data);
        Log::info('Medicamento registrado', ['id' => $medicamento->id]);

        return response()->json($medicamento->load('categoria'), 201);
    }

    public function show($id)
    {
        // MEJORA: Usamos with('categoria') también aquí.
        return Medicamento::with('categoria')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $medicamento = Medicamento::findOrFail($id);

        $data = $request->validate([
            'nombre'             => 'required|string|max:100|unique:medicamentos,nombre,' . $medicamento->id,
            'descripcion'        => 'required|string|max:255',

            // MEJORA: Se añade la validación para que la categoría exista.
            'categoria_id'       => 'nullable|exists:medicamento_categorias,id',
        ]);

        $medicamento->update($data);
        Log::info('Medicamento actualizado', ['id' => $medicamento->id]);

        return response()->json($medicamento->load('categoria'), 200);
    }

    public function destroy($id)
    {
        $medicamento = Medicamento::findOrFail($id);
        $medicamento->delete();

        Log::warning('Medicamento eliminado', ['id' => $id]);
        return response()->noContent();
    }
}
