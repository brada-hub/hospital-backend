<?php

namespace App\Http\Controllers;

use App\Models\Administra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdministraController extends Controller
{
    public function index()
    {
        // ✅ CORRECCIÓN: Usar 'user' en lugar de 'usuario'
        return Administra::with(['receta', 'user'])->get();
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'receta_id'     => 'required|exists:recetas,id',
            'observaciones' => 'nullable|string|max:255',
        ]);

        $data['user_id'] = Auth::id();
        $data['fecha']   = Carbon::now();
        $data['estado']  = 1; // Marcamos como cumplida

        $administra = Administra::create($data);
        Log::info('Administración de medicamento registrada', ['id' => $administra->id, 'user_id' => Auth::id()]);

        return response()->json($administra->load('user'), 201);
    }

    public function update(Request $request, $id)
    {
        // ... (La lógica de update se mantiene, aunque raramente se usa para administraciones)
        $administra = Administra::findOrFail($id);
        $data = $request->validate([
            'receta_id'     => 'sometimes|required|exists:recetas,id',
            'observaciones' => 'nullable|string|max:255',
        ]);
        $administra->update($data);
        Log::info('Administración actualizada', ['id' => $administra->id]);
        return response()->json($administra, 200);
    }
}
