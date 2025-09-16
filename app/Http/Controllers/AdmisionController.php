<?php

namespace App\Http\Controllers;

use App\Models\Cama;
use App\Models\Internacion;
use App\Models\Ocupacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdmisionController extends Controller
{
    /**
     * Procesa una nueva admisión, creando la internación y ocupando la cama.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'paciente_id'   => 'required|exists:pacientes,id',
            'cama_id'       => 'required|exists:camas,id',
            'motivo'        => 'required|string|max:191',
            'diagnostico'   => 'required|string|max:191',
            'observaciones' => 'nullable|string|max:191',
        ]);

        try {
            // DB::transaction asegura que todas las operaciones se hagan con éxito, o ninguna se hace.
            $resultado = DB::transaction(function () use ($data) {

                // Criterio: No se asignan camas ocupadas.
                // Se bloquea la fila de la cama para evitar que dos personas la asignen al mismo tiempo.
                $cama = Cama::lockForUpdate()->findOrFail($data['cama_id']);
                if ($cama->disponibilidad !== 1) { // 1 = Disponible
                    throw new \Exception('La cama seleccionada ya no está disponible.');
                }

                // Criterio: Un paciente no puede ser asignado a más de una cama al mismo tiempo.
                $internacionActiva = Internacion::where('paciente_id', $data['paciente_id'])
                                                ->whereNull('fecha_alta')
                                                ->exists();
                if ($internacionActiva) {
                    throw new \Exception('Este paciente ya tiene una internación activa.');
                }

                // Paso 1: Crear el registro de la internación.
                $internacion = Internacion::create([
                    'paciente_id'   => $data['paciente_id'],
                    'fecha_ingreso' => Carbon::now(),
                    'motivo'        => $data['motivo'],
                    'diagnostico'   => $data['diagnostico'],
                    'observaciones' => $data['observaciones'],
                ]);

                // Paso 2: Actualizar la disponibilidad de la cama a 'Ocupada'.
                $cama->update(['disponibilidad' => 0]);

                // Paso 3: Crear el registro de la ocupación para vincular la internación y la cama.
                $ocupacion = Ocupacion::create([
                    'internacion_id' => $internacion->id,
                    'cama_id'        => $cama->id,
                    'fecha_ocupacion'=> Carbon::now(),
                ]);

                Log::info('Nueva admisión registrada.', ['internacion_id' => $internacion->id]);

                return ['internacion' => $internacion, 'ocupacion' => $ocupacion];
            });

            return response()->json([
                'message' => 'Paciente admitido e internado con éxito.',
                'data'    => $resultado,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error en el proceso de admisión:', ['error' => $e->getMessage()]);
            return response()->json(['message' => $e->getMessage()], 409); // 409 Conflict (buen código para este caso)
        }
    }
}
