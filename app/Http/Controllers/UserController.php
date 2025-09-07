<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;
class UserController extends Controller
{
    public function index()
    {
        // Lista paginada de usuarios con su rol
        $users = User::with('rol')->paginate(10);
        return response()->json($users, 200);
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
        ]);

        // Encriptar contraseña
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        Log::info("Usuario creado", ['user' => $user]);
        return response()->json($user, 201);
    }

    public function show($id)
    {
        $user = User::with('rol')->findOrFail($id);
        return response()->json($user, 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'nombre'    => 'required|string|max:50',
            'apellidos' => 'required|string|max:100',
            'telefono'  => 'nullable|numeric',
            'email'     => 'required|string|email|max:100|unique:users,email,' . $user->id,
            'password'  => 'nullable|string|min:8',
            'rol_id'    => 'required|exists:rols,id',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        Log::info("Usuario actualizado", ['user' => $user]);
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
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    $key = 'login-attempts:' . $request->ip();

    if (RateLimiter::tooManyAttempts($key, 5)) {
        return response()->json([
            'message' => 'Demasiados intentos fallidos. Intenta de nuevo en ' . RateLimiter::availableIn($key) . ' segundos.'
        ], 429);
    }

    if (!Auth::attempt($request->only('email','password'))) {
        RateLimiter::hit($key, 60); // bloquea 60s
        return response()->json(['message' => 'Credenciales incorrectas'], 401);
    }

    RateLimiter::clear($key);

    $user = Auth::user();
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'access_token' => $token,
        'token_type' => 'Bearer',
        'user' => $user
    ]);
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
