<?php

namespace App\Http\Controllers;

use App\Models\Internacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReporteController extends Controller
{
    /**
     * 📄 Genera el informe de alta (Epicrisis) de una internación
     */
    public function generarEpicrisis($internacionId)
    {
        $internacion = Internacion::with([
            'paciente',
            'medico',
            'ocupacionActiva.cama.sala.especialidad',
            'tratamientos.recetas.medicamento',
            'tratamientos.recetas.administras',
            'alimentaciones.tipoDieta',
            'alimentaciones.tiempos',
            'alimentaciones.consumes',
            'controles.user',
            'controles.valores.signo',
            'cuidados.cuidadosAplicados.user',
        ])->findOrFail($internacionId);

        if (!$internacion->fecha_alta) {
            return response()->json([
                'message' => 'No se puede generar epicrisis de una internación activa.'
            ], 400);
        }

        // Calcular días de estancia
        $diasEstancia = Carbon::parse($internacion->fecha_ingreso)
            ->diffInDays(Carbon::parse($internacion->fecha_alta));

        // Obtener signos vitales de ingreso y egreso
        $signosIngreso = $this->obtenerSignosVitales($internacion, 'ingreso');
        $signosEgreso = $this->obtenerSignosVitales($internacion, 'egreso');

        // Resumen de medicamentos administrados
        $resumenMedicamentos = $this->obtenerResumenMedicamentos($internacion);

        // Resumen de alimentación
        $resumenAlimentacion = $this->obtenerResumenAlimentacion($internacion);

        // Evolución clínica resumida
        $evolucionClinica = $internacion->controles
            ->where('tipo', 'Evolución Médica')
            ->sortBy('fecha_control')
            ->values();

        $data = [
            'internacion' => $internacion,
            'diasEstancia' => $diasEstancia,
            'signosIngreso' => $signosIngreso,
            'signosEgreso' => $signosEgreso,
            'resumenMedicamentos' => $resumenMedicamentos,
            'resumenAlimentacion' => $resumenAlimentacion,
            'evolucionClinica' => $evolucionClinica,
            'fechaGeneracion' => Carbon::now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('reportes.epicrisis', $data);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->download("Epicrisis_{$internacion->paciente->nombre}_{$internacion->paciente->apellidos}_{$internacionId}.pdf");
    }

    /**
     * 📊 Genera reporte de evolución clínica completa
     */
    public function generarEvolucionClinica($internacionId)
    {
        $internacion = Internacion::with([
            'paciente',
            'medico',
            'controles.user',
            'controles.valores.signo',
        ])->findOrFail($internacionId);

        // Agrupar controles por fecha
        $controlesPorFecha = $internacion->controles
            ->groupBy(function ($control) {
                return Carbon::parse($control->fecha_control)->format('Y-m-d');
            })
            ->sortKeys();

        $data = [
            'internacion' => $internacion,
            'controlesPorFecha' => $controlesPorFecha,
            'fechaGeneracion' => Carbon::now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('reportes.evolucion-clinica', $data);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->download("Evolucion_Clinica_{$internacion->paciente->nombre}_{$internacion->paciente->apellidos}.pdf");
    }

    /**
     * 📈 Genera reportes estadísticos del hospital
     */
    public function generarEstadisticas(Request $request)
    {
        $data = $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $fechaInicio = Carbon::parse($data['fecha_inicio']);
        $fechaFin = Carbon::parse($data['fecha_fin']);

        // 1. Estancia media
        $estanciaMedia = Internacion::whereBetween('fecha_ingreso', [$fechaInicio, $fechaFin])
            ->whereNotNull('fecha_alta')
            ->get()
            ->map(function ($internacion) {
                return Carbon::parse($internacion->fecha_ingreso)
                    ->diffInDays(Carbon::parse($internacion->fecha_alta));
            })
            ->avg();

        // 2. Total de internaciones
        $totalInternaciones = Internacion::whereBetween('fecha_ingreso', [$fechaInicio, $fechaFin])->count();

        // 3. Internaciones activas
        $internacionesActivas = Internacion::whereNull('fecha_alta')->count();

        // 4. Medicamentos más utilizados
        $medicamentosMasUsados = DB::table('recetas')
            ->join('medicamentos', 'recetas.medicamento_id', '=', 'medicamentos.id')
            ->join('tratamientos', 'recetas.tratamiento_id', '=', 'tratamientos.id')
            ->join('internacions', 'tratamientos.internacion_id', '=', 'internacions.id')
            ->whereBetween('internacions.fecha_ingreso', [$fechaInicio, $fechaFin])
            ->select('medicamentos.nombre', DB::raw('COUNT(*) as total_prescripciones'))
            ->groupBy('medicamentos.id', 'medicamentos.nombre')
            ->orderByDesc('total_prescripciones')
            ->limit(10)
            ->get();

        // 5. Diagnósticos más frecuentes
        $diagnosticosFrecuentes = Internacion::whereBetween('fecha_ingreso', [$fechaInicio, $fechaFin])
            ->select('diagnostico', DB::raw('COUNT(*) as total'))
            ->groupBy('diagnostico')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // 6. Ocupación de camas por sala
        $ocupacionSalas = DB::table('ocupacions')
            ->join('camas', 'ocupacions.cama_id', '=', 'camas.id')
            ->join('salas', 'camas.sala_id', '=', 'salas.id')
            ->join('internacions', 'ocupacions.internacion_id', '=', 'internacions.id')
            ->whereBetween('internacions.fecha_ingreso', [$fechaInicio, $fechaFin])
            ->select('salas.nombre', DB::raw('COUNT(*) as total_ocupaciones'))
            ->groupBy('salas.id', 'salas.nombre')
            ->orderByDesc('total_ocupaciones')
            ->get();

        // 7. Efectividad de tratamientos (tratamientos finalizados vs suspendidos)
        $efectividadTratamientos = DB::table('tratamientos')
            ->join('internacions', 'tratamientos.internacion_id', '=', 'internacions.id')
            ->whereBetween('internacions.fecha_ingreso', [$fechaInicio, $fechaFin])
            ->select(
                DB::raw('SUM(CASE WHEN tratamientos.estado = 2 THEN 1 ELSE 0 END) as finalizados'),
                DB::raw('SUM(CASE WHEN tratamientos.estado = 1 THEN 1 ELSE 0 END) as suspendidos'),
                DB::raw('SUM(CASE WHEN tratamientos.estado = 0 THEN 1 ELSE 0 END) as activos')
            )
            ->first();

        $porcentajeEfectividad = $efectividadTratamientos->finalizados > 0
            ? round(($efectividadTratamientos->finalizados / ($efectividadTratamientos->finalizados + $efectividadTratamientos->suspendidos)) * 100, 2)
            : 0;

        $estadisticas = [
            'periodo' => [
                'inicio' => $fechaInicio->format('d/m/Y'),
                'fin' => $fechaFin->format('d/m/Y'),
            ],
            'estancia_media_dias' => round($estanciaMedia, 1),
            'total_internaciones' => $totalInternaciones,
            'internaciones_activas' => $internacionesActivas,
            'medicamentos_mas_usados' => $medicamentosMasUsados,
            'diagnosticos_frecuentes' => $diagnosticosFrecuentes,
            'ocupacion_salas' => $ocupacionSalas,
            'efectividad_tratamientos' => [
                'finalizados' => $efectividadTratamientos->finalizados,
                'suspendidos' => $efectividadTratamientos->suspendidos,
                'activos' => $efectividadTratamientos->activos,
                'porcentaje_efectividad' => $porcentajeEfectividad,
            ],
            'fecha_generacion' => Carbon::now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('reportes.estadisticas', $estadisticas);
        $pdf->setPaper('letter', 'landscape');

        return $pdf->download("Estadisticas_Hospital_{$fechaInicio->format('Y-m-d')}_a_{$fechaFin->format('Y-m-d')}.pdf");
    }

    /**
     * 🔍 Obtiene signos vitales de ingreso o egreso
     */
    private function obtenerSignosVitales($internacion, $tipo = 'ingreso')
    {
        $control = $tipo === 'ingreso'
            ? $internacion->controles->sortBy('fecha_control')->first()
            : $internacion->controles->sortByDesc('fecha_control')->first();

        if (!$control || !$control->valores) {
            return [];
        }

        return $control->valores->map(function ($valor) {
            return [
                'signo' => $valor->signo->nombre,
                'medida' => $valor->medida,
                'unidad' => $valor->signo->unidad,
            ];
        });
    }

    /**
     * 💊 Obtiene resumen de medicamentos administrados
     */
    private function obtenerResumenMedicamentos($internacion)
    {
        $resumen = [];

        foreach ($internacion->tratamientos as $tratamiento) {
            foreach ($tratamiento->recetas as $receta) {
                $totalDosis = $receta->administras->count();
                $dosisAdministradas = $receta->administras->where('estado', 1)->count();
                $dosisRetrasadas = $receta->administras->where('estado', 2)->count();

                $resumen[] = [
                    'medicamento' => $receta->medicamento->nombre,
                    'dosis' => $receta->dosis,
                    'via' => $receta->via_administracion,
                    'frecuencia' => "Cada {$receta->frecuencia_horas} horas",
                    'duracion' => "{$receta->duracion_dias} días",
                    'total_dosis' => $totalDosis,
                    'dosis_administradas' => $dosisAdministradas,
                    'dosis_retrasadas' => $dosisRetrasadas,
                    'adherencia' => $totalDosis > 0 ? round(($dosisAdministradas / $totalDosis) * 100, 1) : 0,
                ];
            }
        }

        return $resumen;
    }

    /**
     * 🍽️ Obtiene resumen de alimentación
     */
    private function obtenerResumenAlimentacion($internacion)
    {
        $resumen = [];

        foreach ($internacion->alimentaciones as $alimentacion) {
            $consumoPromedio = $alimentacion->consumes->avg('porcentaje_consumido');

            $resumen[] = [
                'tipo_dieta' => $alimentacion->tipoDieta->nombre ?? 'N/A',
                'via' => $alimentacion->via_administracion,
                'fecha_inicio' => Carbon::parse($alimentacion->fecha_inicio)->format('d/m/Y'),
                'fecha_fin' => Carbon::parse($alimentacion->fecha_fin)->format('d/m/Y'),
                'consumo_promedio' => round($consumoPromedio, 1) . '%',
                'estado' => $this->traducirEstadoAlimentacion($alimentacion->estado),
            ];
        }

        return $resumen;
    }

    private function traducirEstadoAlimentacion($estado)
    {
        $estados = [0 => 'Activa', 1 => 'Suspendida', 2 => 'Finalizada', 3 => 'Cancelada'];
        return $estados[$estado] ?? 'Desconocido';
    }
}
