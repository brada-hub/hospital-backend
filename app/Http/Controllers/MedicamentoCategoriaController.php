<?php

namespace App\Http\Controllers;

use App\Models\MedicamentoCategoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MedicamentoCategoriaController extends Controller
{
    /**
     * Muestra una lista de todas las categorías.
     */
    public function index()
    {
        return MedicamentoCategoria::orderBy('nombre')->get();
    }

    /**
     * Guarda una nueva categoría en la base de datos.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100|unique:medicamento_categorias,nombre',
        ]);

        $categoria = MedicamentoCategoria::create($data);
        Log::info('Categoría de medicamento creada', ['id' => $categoria->id]);

        return response()->json($categoria, 201);
    }

    /**
     * Muestra una categoría específica, incluyendo los medicamentos que pertenecen a ella.
     */
    public function show(MedicamentoCategoria $medicamentoCategoria)
    {
        return $medicamentoCategoria->load('medicamentos');
    }

    /**
     * Actualiza una categoría existente.
     */
    public function update(Request $request, MedicamentoCategoria $medicamentoCategoria)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100|unique:medicamento_categorias,nombre,' . $medicamentoCategoria->id,
        ]);

        $medicamentoCategoria->update($data);
        Log::info('Categoría de medicamento actualizada', ['id' => $medicamentoCategoria->id]);

        return response()->json($medicamentoCategoria);
    }

    /**
     * Elimina una categoría.
     */
    public function destroy(MedicamentoCategoria $medicamentoCategoria)
    {
        // Opcional: Añadir lógica para verificar si la categoría está en uso antes de borrar.
        if ($medicamentoCategoria->medicamentos()->count() > 0) {
            return response()->json(['message' => 'No se puede eliminar la categoría porque tiene medicamentos asociados.'], 409); // 409 Conflict
        }

        $medicamentoCategoria->delete();
        Log::warning('Categoría de medicamento eliminada', ['id' => $medicamentoCategoria->id]);

        return response()->noContent(); // 204 No Content
    }
}
