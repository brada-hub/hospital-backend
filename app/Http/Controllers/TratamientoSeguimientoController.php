<?php

namespace App\Http\Controllers;

use App\Models\Tratamiento;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TratamientoSeguimientoController extends Controller
{
    public function getEstadoActual($tratamiento_id)
    {
        $tratamiento = Tratamiento::with([
            'recetas.medicamento',
            'recetas.administras' => fn($q) => $q->orderBy('fecha', 'desc')->with('user:id,nombre')
        ])->findOrFail($tratamiento_id);

        $ahora = Carbon::now();

        foreach ($tratamiento->recetas as $receta) {

            // 1. Generar todas las tomas teóricas
            $tomasTeoricas = [];
            $horaIterada = Carbon::parse($receta->created_at);
            $fechaFin = Carbon::parse($receta->created_at)->addDays($receta->duracion_dias);

            while ($horaIterada < $fechaFin) {
                $tomasTeoricas[] = $horaIterada->copy();
                $horaIterada->addHours($receta->frecuencia_horas);
            }

            // 2. Asignar estado a cada toma
            $tomasConEstado = [];
            foreach ($tomasTeoricas as $horaEsperada) {
                $fueAdministrada = $receta->administras->first(function ($admin) use ($horaEsperada, $receta) {
                    $horaAdmin = Carbon::parse($admin->fecha);
                    $ventanaMinutos = ($receta->frecuencia_horas * 60) / 2;
                    return $horaAdmin->diffInMinutes($horaEsperada) < $ventanaMinutos;
                });

                $status = 'Pendiente';
                if ($fueAdministrada) {
                    $status = 'Cumplida';
                } elseif ($horaEsperada->isPast() && $ahora->diffInMinutes($horaEsperada) > 15) { // Omitida si pasaron 15 min
                    $status = 'Omitida';
                }

                $tomasConEstado[] = [
                    'horaEsperada' => $horaEsperada->toDateTimeString(),
                    'status' => $status,
                    'datosAdministracion' => $fueAdministrada
                ];
            }

            // 3. Filtrar para mostrar solo lo relevante
            $tomasHoy = array_filter($tomasConEstado, fn($t) => Carbon::parse($t['horaEsperada'])->isToday());

            $ultimaTomaPasada = collect($tomasHoy)->filter(fn($t) => Carbon::parse($t['horaEsperada'])->isPast())->last();
            $proximaTomaFutura = collect($tomasHoy)->first(fn($t) => !Carbon::parse($t['horaEsperada'])->isPast());

            $tomasParaMostrar = [];
            if ($ultimaTomaPasada) $tomasParaMostrar[] = $ultimaTomaPasada;
            if ($proximaTomaFutura) $tomasParaMostrar[] = $proximaTomaFutura;

            // Añadimos el array de tomas al objeto receta
            $receta->tomas_hoy = array_values(collect($tomasParaMostrar)->unique('horaEsperada')->sortBy('horaEsperada')->toArray());
        }

        return response()->json($tratamiento);
    }
}
