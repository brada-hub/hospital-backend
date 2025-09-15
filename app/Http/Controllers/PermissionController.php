<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PermissionController extends Controller
{
    public function index()
    {
        return response()->json(Permission::all(), 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:50|unique:permissions,nombre',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $permission = Permission::create($data);

        Log::info("Permiso creado", ['permiso' => $permission]);
        return response()->json($permission, 201);
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $data = $request->validate([
            'nombre'      => 'required|string|max:50|unique:permissions,nombre,' . $permission->id,
            'descripcion' => 'nullable|string|max:255',
        ]);

        $permission->update($data);

        Log::info("Permiso actualizado", ['permiso' => $permission]);
        return response()->json($permission, 200);
    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        Log::warning("Permiso eliminado", ['id' => $id]);
        return response()->noContent(); // 204
    }
}
