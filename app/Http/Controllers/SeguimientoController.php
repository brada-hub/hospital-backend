<?php

namespace App\Http\Controllers;

use App\Models\Tratamiento;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SeguimientoController extends Controller
{
    public function getEstadoTratamiento($tratamiento_id)
    {
        $tratamiento = Tratamiento::with([
            'recetas.medicamento',
            'recetas.administras' => fn($q) => $q->orderBy('fecha', 'desc')->with('user:id,nombre')
        ])->findOrFail($tratamiento_id);

        $ahora = Carbon::now();

        foreach ($tratamiento->recetas as $receta) {
            $horaBase = Carbon::parse($receta->created_at);
            $fechaFin = $horaBase->copy()->addDays($receta->duracion_dias);
            $frecuenciaHoras = $receta->frecuencia_horas;

            if ($frecuenciaHoras <= 0) {
                $receta->tomas_hoy = [];
                $receta->historial_completo = [];
                continue;
            }

            $tomasConEstado = [];
            $horaIterada = $horaBase->copy();

            while ($horaIterada->lt($fechaFin)) {
                $fueAdministrada = $receta->administras->first(function ($admin) use ($horaIterada, $frecuenciaHoras) {
                    $horaAdmin = Carbon::parse($admin->fecha);
                    // ✅ CORRECCIÓN: Usar $horaIterada en lugar de la variable indefinida
                    return abs($horaAdmin->diffInMinutes($horaIterada)) < ($frecuenciaHoras * 60) / 2;
                });

                $status = 'Pendiente';
                if ($fueAdministrada) {
                    $status = 'Cumplida';
                } elseif ($horaIterada->isPast() && $ahora->diffInMinutes($horaIterada) > 15) {
                    $status = 'Omitida';
                }

                $tomasConEstado[] = [
                    'horaEsperada' => $horaIterada->toDateTimeString(),
                    'status' => $status,
                    'datosAdministracion' => $fueAdministrada
                ];
                $horaIterada->addHours($frecuenciaHoras);
            }

            $tomasHoy = collect($tomasConEstado)->filter(fn($t) => Carbon::parse($t['horaEsperada'])->isToday())->values();
            $ultimaPasadaHoy = $tomasHoy->filter(fn($t) => Carbon::parse($t['horaEsperada'])->isPast())->last();
            $proximaPendienteHoy = $tomasHoy->first(fn($t) => !Carbon::parse($t['horaEsperada'])->isPast());

            $tomasParaMostrarHoy = [];
            if ($ultimaPasadaHoy) $tomasParaMostrarHoy[] = $ultimaPasadaHoy;
            if ($proximaPendienteHoy && (!$ultimaPasadaHoy || $ultimaPasadaHoy['horaEsperada'] != $proximaPendienteHoy['horaEsperada'])) {
                $tomasParaMostrarHoy[] = $proximaPendienteHoy;
            }
            $receta->tomas_hoy = $tomasParaMostrarHoy;

            $historialCompleto = collect($tomasConEstado)
                ->filter(fn($t) => $t['status'] !== 'Pendiente')
                ->sortByDesc('horaEsperada')
                ->values()
                ->all();

            $receta->historial_completo = $historialCompleto;
        }

        return response()->json($tratamiento);
    }
}
