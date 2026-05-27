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
            'categoria_id'       => 'nullable|exists:medicamento_categorias,id',
            'stock'              => 'nullable|integer|min:0',
            'stock_critico'      => 'nullable|integer|min:0',
            'estante'            => 'nullable|string|max:100',
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
            'categoria_id'       => 'nullable|exists:medicamento_categorias,id',
            'stock'              => 'nullable|integer|min:0',
            'stock_critico'      => 'nullable|integer|min:0',
            'estante'            => 'nullable|string|max:100',
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

    /**
     * Dispensar medicamento decrementando stock en caliente.
     */
    public function dispensar(Request $request, $id)
    {
        $medicamento = Medicamento::findOrFail($id);
        $data = $request->validate([
            'cantidad' => 'required|integer|min:1',
        ]);

        if ($medicamento->stock < $data['cantidad']) {
            return response()->json([
                'message' => 'Stock insuficiente para dispensar este fármaco.'
            ], 422);
        }

        $medicamento->decrement('stock', $data['cantidad']);
        Log::info('Medicamento dispensado', ['id' => $id, 'cantidad' => $data['cantidad']]);

        return response()->json($medicamento->load('categoria'), 200);
    }
}
