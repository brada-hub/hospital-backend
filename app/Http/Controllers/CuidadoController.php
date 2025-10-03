<?php
// app/Http/Controllers/CuidadoController.php

namespace App\Http\Controllers;

use App\Models\Cuidado;
use App\Models\CuidadoAplicado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CuidadoController extends Controller
{
    // ... (Mantén tus otros métodos como index, show, etc.)

    /**
     * Permite a la Enfermera registrar un Cuidado NUEVO y Aplicarlo inmediatamente.
     * Es un cuidado "A Demanda" que se marca como FINALIZADO inmediatamente.
     */
    public function storeAplicadoDirecto(Request $request)
    {
        $data = $request->validate([
            'internacion_id' => 'required|exists:internacions,id',
            // Solo pedimos un campo de texto que usaremos para ambas tablas.
            'descripcion_completa' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // 1. Crear el nuevo Cuidado (El Plan). Se marca como FINALIZADO (estado=1)
            // para que NO aparezca en el dashboard de tareas pendientes.
            $cuidado = Cuidado::create([
                'internacion_id' => $data['internacion_id'],
                'tipo'           => 'A Demanda - Enfermería',
                'descripcion'    => $data['descripcion_completa'], // Descripción del plan
                'fecha_inicio'   => Carbon::now(),
                'frecuencia'     => 'Única Vez',
                'estado'         => 1, // Finalizado inmediatamente.
            ]);

            // 2. Aplicar el Cuidado inmediatamente
            CuidadoAplicado::create([
                'cuidado_id'       => $cuidado->id,
                'user_id'          => Auth::id(),
                'fecha_aplicacion' => Carbon::now(),
                'observaciones'    => $data['descripcion_completa'], // Observación de la aplicación
            ]);

            DB::commit();

            return response()->json(['message' => 'Cuidado A Demanda - Enfermería registrado correctamente.', 'cuidado_id' => $cuidado->id], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            // Asegúrate de usar Log::error para registrar el problema.
            \Illuminate\Support\Facades\Log::error('Error al crear y aplicar cuidado directo:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error interno al registrar el cuidado urgente.'], 500);
        }
    }
}
