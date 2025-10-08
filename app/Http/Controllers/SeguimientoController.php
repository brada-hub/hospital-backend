<?php

namespace App\Http\Controllers;

use App\Models\Tratamiento;
use Carbon\Carbon;

class SeguimientoController extends Controller
{
    // Sin constructor ni middleware - la protección está en las rutas
    /**
     * RF-06, RF-07: Obtener estado visual del tratamiento
     *
     * ⚠️ IMPORTANTE: Este endpoint NO modifica la BD, solo calcula estados visuales
     */
    public function getEstadoTratamiento($id)
    {
        $tratamiento = Tratamiento::with([
            'recetas.medicamento',
            'recetas.administras' => function ($query) {
                $query->orderBy('hora_programada', 'asc')
                    ->with('user:id,nombre,apellidos');
            }
        ])->findOrFail($id);

        $hoy = Carbon::now(config('app.timezone'));
        $inicioVentana = $hoy->copy()->startOfDay();
        $finVentana = $hoy->copy()->endOfDay();

        foreach ($tratamiento->recetas as $receta) {
            $tomasHoy = [];
            $administraciones = $receta->administras;

            if ($administraciones->isEmpty()) {
                // 🔥 NO HAY CRONOGRAMA → Mostrar PRIMERA DOSIS PENDIENTE siempre
                $fechaInicio = Carbon::parse($tratamiento->fecha_inicio, config('app.timezone'));
                $primeraDosis = $fechaInicio->copy();
                $frecuenciaHoras = $receta->frecuencia_horas;

                if ($primeraDosis->isPast() && $frecuenciaHoras > 0) {
                    $horasPasadas = $hoy->diffInHours($primeraDosis);
                    $ciclosPasados = floor($horasPasadas / $frecuenciaHoras);
                    $primeraDosis->addHours(($ciclosPasados + 1) * $frecuenciaHoras);
                }

                $tomasHoy[] = [
                    'id' => null,
                    'horaReal' => $primeraDosis->toDateTimeString(),
                    'status' => $primeraDosis->isPast() ? '¡ATRASADA!' : 'Pendiente',
                    'datosAdministracion' => null,
                ];
            } else {
                // RF-06, RF-07: Filtrar administraciones del día de hoy
                $administracionesHoy = $administraciones->filter(function ($admin) use ($inicioVentana, $finVentana) {
                    $hora = Carbon::parse($admin->hora_programada);
                    return $hora->between($inicioVentana, $finVentana);
                });

                foreach ($administracionesHoy as $admin) {
                    $status = $this->calcularStatusVisual($admin, $hoy);

                    $tomasHoy[] = [
                        'id' => $admin->id,
                        'horaReal' => $admin->hora_programada,
                        'status' => $status,
                        'datosAdministracion' => ($admin->estado == 1 || $admin->estado == 2) ? $admin : null,
                    ];
                }
            }

            $receta->tomas_hoy = $tomasHoy;

            // 🔥 IMPORTANTE: Mantener administras en la respuesta para el temporizador
            // NO eliminar esta relación porque el frontend la necesita
        }

        return response()->json($tratamiento);
    }

    /**
     * RF-06: Calcular estado visual de una dosis (sin modificar BD)
     *
     * Estados en BD:
     * - 0 = Pendiente (no administrada)
     * - 1 = Cumplida (a tiempo)
     * - 2 = Cumplida con retraso
     *
     * Estados visuales:
     * - "Pendiente" → aún no llega la hora o está en ventana
     * - "¡ATRASADA!" → pasó la hora y sigue sin administrar
     * - "Cumplida" → administrada a tiempo
     * - "Cumplida (Retrasada)" → administrada tarde
     */
    private function calcularStatusVisual($admin, Carbon $ahora)
    {
        // RF-05: Si ya fue administrada, mostrar el estado definitivo
        if ($admin->estado == 1) {
            return 'Cumplida';
        }

        if ($admin->estado == 2) {
            return 'Cumplida (Retrasada)';
        }

        // RF-06: Dosis pendiente (estado=0) → evaluar si está atrasada visualmente
        $horaProgramada = Carbon::parse($admin->hora_programada);

        // Si la hora programada ya pasó hace más de 30 minutos → visualmente atrasada
        if ($ahora->greaterThan($horaProgramada->copy()->addMinutes(30))) {
            return '¡ATRASADA!';
        }

        // Aún está pendiente dentro de la ventana
        return 'Pendiente';
    }
}
