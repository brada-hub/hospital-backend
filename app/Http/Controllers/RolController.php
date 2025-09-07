<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RolController extends Controller
{
    public function index()
    {
        return response()->json(Rol::all(), 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:50|unique:rols,nombre',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $rol = Rol::create($data);

        Log::info("Rol creado", ['rol' => $rol]);
        return response()->json($rol, 201);
    }

    public function show($id)
    {
        $rol = Rol::findOrFail($id);
        return response()->json($rol, 200);
    }

    public function update(Request $request, $id)
    {
        $rol = Rol::findOrFail($id);

        $data = $request->validate([
            'nombre'      => 'required|string|max:50|unique:rols,nombre,' . $rol->id,
            'descripcion' => 'nullable|string|max:255',
        ]);

        $rol->update($data);

        Log::info("Rol actualizado", ['rol' => $rol]);
        return response()->json($rol, 200);
    }

    public function destroy($id)
    {
        $rol = Rol::findOrFail($id);
        $rol->delete();

        Log::warning("Rol eliminado", ['id' => $id]);
        return response()->noContent(); // 204
    }
}
