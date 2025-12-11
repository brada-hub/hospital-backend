<?php

namespace App\Http\Controllers;

use App\Models\User;

use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        // Lista paginada de usuarios con su rol
        // 📌 CORRECCIÓN: Se carga la relación 'permissions'
        $users = User::with('rol.permissions', 'permissions')->paginate(10);
        return response()->json($users, 200);
    }
    public function me(Request $request)
    {
        $user = $request->user(); // usuario autenticado vía Sanctum

        // Cargar relaciones necesarias
        $user->load('rol.permissions', 'permissions', 'hospital');

        return response()->json([
            'user' => $user
        ], 200);
    }
    public function getMedicosActivos()
    {
        // Buscamos el ID del rol 'MÉDICO'
        $rolMedicoId = Rol::where('nombre', 'MÉDICO')->value('id');

        if (!$rolMedicoId) {
            return response()->json(['message' => 'Rol de Médico no encontrado.'], 404);
        }

        $medicos = User::where('rol_id', $rolMedicoId)
            ->where('estado', 1) // Solo médicos activos
            ->withCount(['internaciones as pacientes_activos_count' => function ($query) {
                // Contamos solo las internaciones que no tienen fecha de alta
                $query->whereNull('fecha_alta');
            }])
            ->get(['id', 'nombre', 'apellidos']);

        return response()->json($medicos);
    }
    // Nuevo método para sincronizar permisos individuales
    public function syncPermissions(Request $request, User $user)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*.id' => 'required|exists:permissions,id',
            'permissions.*.estado' => 'required|in:permitido,denegado',
        ]);

        // Formatear los datos de forma correcta para sync()
        $permissionsToSync = collect($request->permissions)->mapWithKeys(function ($item) {
            return [$item['id'] => ['estado' => $item['estado']]];
        })->toArray();

        $user->permissions()->sync($permissionsToSync);
        Log::info("Permisos individuales del usuario '{$user->email}' sincronizados.");

        return response()->json($user->load('permissions'), 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'    => 'required|string|max:50',
            'apellidos' => 'required|string|max:100',
            'telefono'  => 'nullable|numeric',
            'email'     => 'required|string|email|max:100|unique:users',
            'password'  => 'required|string|min:8',
            'rol_id'    => 'required|exists:rols,id',
            'hospital_id' => 'required|exists:hospitals,id',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['must_change_password'] = true; // ✅ Obligar cambio de contraseña

        $user = User::create($data);

        // 🚀 Asignar automáticamente los permisos del rol
        if ($user->rol) {
            $permIds = $user->rol->permissions->pluck('id')->toArray();
            $user->permissions()->syncWithPivotValues($permIds, ['estado' => 'permitido']);
        }

        Log::info("Usuario creado con permisos del rol", ['user' => $user]);

        return response()->json($user->load('rol.permissions', 'permissions'), 201);
    }


    public function show($id)
    {
        // 📌 CORRECCIÓN: Se carga la relación 'permissions'
        $user = User::with('rol.permissions', 'permissions')->findOrFail($id);
        return response()->json($user, 200);
    }
    public function toggleEstado(Request $request, User $user)
    {
        $data = $request->validate([
            'estado' => 'required|in:0,1'
        ]);

        $user->estado = $data['estado'];
        $user->save();

        return response()->json($user, 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'nombre'    => 'sometimes|required|string|max:50',
            'apellidos' => 'sometimes|required|string|max:100',
            'telefono'  => 'nullable|numeric',
            'email'     => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:100',
                Rule::unique('users', 'email')->ignore($user->id)
            ],
            'password'  => 'nullable|string|min:8',
            'rol_id'    => 'sometimes|required|exists:rols,id',
            'estado'    => 'sometimes|in:0,1',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']); // evita sobreescribir con null
        }

        $user->update($data);

        return response()->json($user, 200);
    }
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        Log::warning("Usuario eliminado", ['id' => $id]);
        return response()->noContent(); // 204
    }
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $key = 'login-attempts:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'message' => 'Demasiados intentos fallidos. Intenta de nuevo en ' . RateLimiter::availableIn($key) . ' segundos.'
            ], 429);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            RateLimiter::hit($key, 60);
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        RateLimiter::clear($key);

        $user = User::find(Auth::id());

        // 🚨 Validación extra: usuario desactivado
        if ($user->estado == 0) {
            Auth::logout();
            return response()->json([
                'message' => 'Tu cuenta está desactivada. Contacta al administrador.'
            ], 403);
        }

        // 📌 Cargamos las relaciones
        $user->load('rol.permissions', 'permissions', 'hospital');

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $user
        ]);
    }
    // Agrégalo dentro de la clase UserController

    public function updateProfile(Request $request)
    {
        // Obtenemos el usuario que está haciendo la petición (el autenticado)
        $user = $request->user();

        $data = $request->validate([
            'nombre'    => 'required|string|max:50',
            'apellidos' => 'required|string|max:100',
            'telefono'  => 'nullable|numeric',
            // No permitimos cambiar el email desde aquí para mantenerlo simple y seguro
        ]);

        $user->update($data);
        $user->load('rol.permissions', 'permissions', 'hospital');
        // Devolvemos el usuario actualizado para que el frontend refresque los datos
        return response()->json($user, 200);
    }
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        // Reglas básicas
        $rules = [
            'password' => 'required|string|min:8|confirmed',
        ];

        // Validar contraseña actual SOLO si no está en modo "cambio obligatorio"
        // O si el usuario quiere cambiarla voluntariamente (must_change_password == false)
        // PERO el requerimiento es que si es must_change_password, se salte este paso.
        if (!$user->must_change_password) {
            $rules['current_password'] = 'required|string';
        }

        $request->validate($rules);

        // Verificamos la contraseña actual SOLO si no es obligatorio el cambio
        if (!$user->must_change_password) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['message' => 'La contraseña actual es incorrecta.'], 422);
            }
        }

        // Actualizamos con la nueva contraseña
        $user->password = Hash::make($request->password);
        $user->must_change_password = false; // ✅ Desbloquear cuenta
        $user->save();

        // Opcional pero recomendado: Desloguear de otras sesiones
        // Auth::logoutOtherDevices($request->password);

        return response()->json(['message' => 'Contraseña actualizada correctamente.']);
    }
    public function logout(Request $request)
    {
        // Revocar el token actual
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ], 200);
    }
}
