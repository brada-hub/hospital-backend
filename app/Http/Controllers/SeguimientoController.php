<?php

namespace App\Http\Controllers;

use App\Models\Tratamiento;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SeguimientoController extends Controller
{
    /**
     * Obtiene el estado detallado de un tratamiento para una fecha específica,
     * mostrando las tomas de medicamentos programadas y su estado.
     */
    public function getEstadoTratamiento($id, Request $request)
    {
        $tratamiento = Tratamiento::with([
            // Filtra para cargar solo las recetas activas (estado 0) del tratamiento
            'recetas' => function ($query) {
                $query->where('estado', 0);
            },
            'recetas.medicamento',
            // Carga las administraciones de cada receta, ordenadas y con el usuario que la aplicó
            'recetas.administras' => function ($query) {
                $query->orderBy('hora_programada', 'asc')
                    ->with('user:id,nombre,apellidos');
            },
            // ✅ CORRECCIÓN CLAVE: Se carga la relación 'medico' en lugar de 'user'
            'medico:id,nombre,apellidos'
        ])->findOrFail($id);

        // Determina la fecha para el filtro, usando la fecha de la URL o el día actual
        $fechaFiltro = $request->query('fecha');
        $hoy = $fechaFiltro
            ? Carbon::parse($fechaFiltro, config('app.timezone'))
            : Carbon::now(config('app.timezone'));

        $inicioVentana = $hoy->copy()->startOfDay();
        $finVentana = $hoy->copy()->endOfDay();

        // Procesa cada receta para determinar las tomas del día
        foreach ($tratamiento->recetas as $receta) {
            $tomasDelDia = [];
            $administraciones = $receta->administras;

            // Si no hay ninguna administración, es la primera dosis
            if ($administraciones->isEmpty()) {
                if ($fechaFiltro === null || $hoy->isToday()) {
                    $tomasDelDia[] = [
                        'id' => null,
                        'horaReal' => $hoy->toDateTimeString(),
                        'status' => 'Sin iniciar',
                        'datosAdministracion' => null,
                        'esPrimeraDosis' => true,
                    ];
                }
            } else {
                // Filtra las administraciones que corresponden al día visualizado
                $administracionesDelDia = $administraciones->filter(function ($admin) use ($inicioVentana, $finVentana) {
                    $hora = Carbon::parse($admin->hora_programada);
                    return $hora->between($inicioVentana, $finVentana);
                });

                foreach ($administracionesDelDia as $admin) {
                    $status = $this->calcularStatusVisual($admin, $hoy);

                    $tomasDelDia[] = [
                        'id' => $admin->id,
                        'horaReal' => $admin->hora_programada,
                        'status' => $status,
                        'datosAdministracion' => ($admin->estado == 1 || $admin->estado == 2) ? $admin : null,
                        'esPrimeraDosis' => false,
                    ];
                }
            }

            // Añade los datos procesados a cada objeto de receta
            $receta->tomas_hoy = $tomasDelDia;
            $receta->fecha_visualizada = $hoy->toDateString();
        }

        return response()->json($tratamiento);
    }

    /**
     * Calcula el estado visual de una administración (Ej: 'Cumplida', 'Pendiente', '¡ATRASADA!').
     */
    private function calcularStatusVisual($admin, Carbon $ahora)
    {
        if ($admin->estado == 1) {
            return 'Cumplida';
        }

        if ($admin->estado == 2) {
            return 'Cumplida (Retrasada)';
        }

        $horaProgramada = Carbon::parse($admin->hora_programada);

        // El estado 'ATRASADA' solo aplica para el día de hoy
        if ($ahora->isToday()) {
            $minutosTranscurridos = $ahora->diffInMinutes($horaProgramada, false);
            // Si han pasado más de 30 minutos desde la hora programada
            if ($minutosTranscurridos < -30) {
                return '¡ATRASADA!';
            }
        }

        return 'Pendiente';
    }
}
