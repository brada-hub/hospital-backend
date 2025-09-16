<?php

// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers; // <-- AJUSTE: Se elimina '\Api' del namespace

use App\Http\Controllers\Controller;
use App\Models\Cama;
use App\Models\Especialidad;
use App\Models\Internacion;
use App\Models\Ocupacion;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
   public function getKpis(): JsonResponse
    {
        $pacientesInternados = Internacion::whereNull('fecha_alta')->count();

        // CORREGIDO: Contamos solo las camas activas para las estadísticas.
        $totalCamasActivas = Cama::where('estado', 1)->count();

        // CORREGIDO: Las camas ocupadas ahora se cuentan directamente desde el campo 'disponibilidad'. Es más eficiente.
        $camasOcupadas = Cama::where('estado', 1)->where('disponibilidad', 0)->count(); // 0 = Ocupada

        $tasaOcupacion = ($totalCamasActivas > 0) ? round(($camasOcupadas / $totalCamasActivas) * 100, 1) : 0;

        $ingresosHoy = Internacion::whereDate('fecha_ingreso', Carbon::today())->count();
        $altasHoy = Internacion::whereDate('fecha_alta', Carbon::today())->count();

        return response()->json([
            'pacientesInternados' => $pacientesInternados,
            'tasaOcupacion' => $tasaOcupacion,
            'ingresosHoy' => $ingresosHoy,
            'altasHoy' => $altasHoy,
        ]);
    }

    public function getOcupacionPorEspecialidad(): JsonResponse
    {
        // Esta función no cambia, ya que mide pacientes por especialidad, lo cual sigue siendo válido.
        $data = Especialidad::select('nombre as especialidad')
            ->withCount(['camas as ocupadas' => function ($query) {
                $query->whereHas('ocupaciones', function ($subQuery) {
                    $subQuery->whereNull('fecha_desocupacion');
                });
            }])
            ->having('ocupadas', '>', 0)
            ->orderBy('ocupadas', 'desc')
            ->get();

        return response()->json($data);
    }

    /**
     * CORREGIDO: Esta función ha sido reescrita completamente
     * para usar el nuevo campo 'disponibilidad'.
     */
    public function getEstadoCamas(): JsonResponse
    {
        // Contamos las camas AGRUPANDO por el campo 'disponibilidad'
        // para obtener todos los totales en una sola consulta.
        // Solo contamos las camas que están activas (estado = 1).
        $counts = Cama::where('estado', 1)
                      ->select('disponibilidad', DB::raw('count(*) as total'))
                      ->groupBy('disponibilidad')
                      ->pluck('total', 'disponibilidad');

        // Asignamos los valores, usando 0 como valor por defecto si una categoría no tiene camas.
        $camasDisponibles = $counts->get(1, 0);    // disponibilidad = 1 es 'Disponible'
        $camasOcupadas = $counts->get(0, 0);       // disponibilidad = 0 es 'Ocupada'
        $camasEnMantenimiento = $counts->get(2, 0); // disponibilidad = 2 es 'Mantenimiento'

        return response()->json([
            'disponibles' => $camasDisponibles,
            'ocupadas' => $camasOcupadas,
            'mantenimiento' => $camasEnMantenimiento,
        ]);
    }

    public function getUltimosIngresos(): JsonResponse
    {
        // Esta función no necesita cambios.
        $ultimasInternaciones = Internacion::with([
            'paciente',
            'ocupaciones' => function ($query) {
                $query->with('cama.sala')->latest('fecha_ocupacion')->limit(1);
            }
        ])
        ->whereNull('fecha_alta')
        ->orderBy('fecha_ingreso', 'desc')
        ->limit(5)
        ->get();

        $data = $ultimasInternaciones->map(function ($internacion) {
            $ocupacion = $internacion->ocupaciones->first();
            $camaInfo = 'No asignada';
            if ($ocupacion && $ocupacion->cama) {
                $camaInfo = ($ocupacion->cama->sala->nombre ?? 'Sala S/N') . ', ' . $ocupacion->cama->nombre;
            }

            return [
                'paciente' => $internacion->paciente->nombre . ' ' . $internacion->paciente->apellidos,
                'ci' => $internacion->paciente->ci,
                'motivo' => $internacion->motivo,
                'diagnostico' => $internacion->diagnostico,
                'cama' => $camaInfo,
            ];
        });

        return response()->json($data);
    }
}
