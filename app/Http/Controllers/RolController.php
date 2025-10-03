<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule; // No olvides importar Rule

class RolController extends Controller
{
    /**
     * Muestra solo los roles activos.
     */
    public function index()
    {
        // Esto está bien: solo trae los roles con estado = 1.
        $rolesActivos = Rol::with('permissions')->where('estado', 1)->get();
        return response()->json($rolesActivos, 200);
    }

    /**
     * Guarda un nuevo rol.
     */
    public function store(Request $request)
    {
        // 🚨 CORRECCIÓN: Se añaden las validaciones que faltaban.
        $data = $request->validate([
            'nombre'      => 'required|string|max:50|unique:rols,nombre|regex:/^[\pL\s\-]+$/u',
            'descripcion' => 'nullable|string|max:255|regex:/^[\pL\s\-]+$/u',
            'estado'      => 'nullable|boolean',
        ]);

        // Se asigna el estado por defecto si no viene en la petición.
        if (!isset($data['estado'])) {
            $data['estado'] = 1; // Activo
        }

        $rol = Rol::create($data);

        Log::info("Rol creado", ['rol' => $rol]);
        return response()->json($rol, 201);
    }

    /**
     * Muestra un rol específico por su ID.
     */
    public function show($id)
    {
        // Se busca el rol, incluso si está inactivo, para poder verlo individualmente.
        $rol = Rol::with('permissions')->findOrFail($id);
        return response()->json($rol, 200);
    }

    /**
     * Actualiza un rol existente.
     */
    public function update(Request $request, $id)
    {
        $rol = Rol::findOrFail($id);

        // 🚨 CORRECCIÓN: Se añaden las validaciones que faltaban.
        $data = $request->validate([
            'nombre'      => ['required', 'string', 'max:50', Rule::unique('rols')->ignore($rol->id), 'regex:/^[\pL\s\-]+$/u'],
            'descripcion' => 'nullable|string|max:255|regex:/^[\pL\s\-]+$/u',
            'estado'      => 'sometimes|boolean',
        ]);

        $rol->update($data);

        Log::info("Rol actualizado", ['rol' => $rol]);
        return response()->json($rol, 200);
    }

    /**
     * Sincroniza los permisos para un rol.
     */
    public function syncPermissions(Request $request, Rol $rol)
    {
        $request->validate(['permissions' => 'array']);
        $rol->permissions()->sync($request->permissions);
        Log::info("Permisos del rol '{$rol->nombre}' sincronizados.");
        return response()->json($rol->load('permissions'), 200);
    }

    /**
     * Desactiva un rol (cambia su estado a 0).
     */
    public function destroy($id)
    {
        // 🚨 CORRECCIÓN: Esta es la lógica correcta para "desactivar".
        $rol = Rol::withCount('usuarios')->findOrFail($id);

        // Se verifica si hay usuarios usando este rol.
        if ($rol->usuarios_count > 0) {
            return response()->json([
                'message' => 'No se puede desactivar el rol porque está asignado a ' . $rol->usuarios_count . ' usuario(s).'
            ], 409); // 409 Conflict
        }

        $rol->estado = 0; // Cambia el estado a Inactivo
        $rol->save();

        Log::warning("Rol desactivado (cambio de estado)", ['id' => $id]);

        return response()->json([
            'message' => 'Rol desactivado correctamente.'
        ], 200);
    }
}
