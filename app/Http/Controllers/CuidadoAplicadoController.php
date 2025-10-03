<?php

namespace App\Http\Controllers;

use App\Models\CuidadoAplicado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CuidadoAplicadoController extends Controller
{
    public function index()
    {
        // ✅ CORRECCIÓN 1: La relación en el modelo se llama 'user', no 'usuario'.
        return CuidadoAplicado::with(['user', 'cuidado'])->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cuidado_id'    => 'required|exists:cuidados,id',
            'observaciones' => 'nullable|string|max:255',
            // ❌ CORRECCIÓN 2: Eliminamos la validación para 'estado'.
            // La existencia del registro en la DB ya implica 'Realizado'.
        ]);

        // El user_id es el ID de la enfermera logueada
        $data['user_id'] = Auth::id();
        // La fecha de aplicación es el momento actual
        $data['fecha_aplicacion'] = Carbon::now();

        // ❌ NO pasamos 'estado' al create, ya que no existe en el modelo $fillable
        // (y no existe en la tabla tras la última migración).
        $cuidadoAplicado = CuidadoAplicado::create($data);

        return response()->json($cuidadoAplicado, 201);
    }
}
