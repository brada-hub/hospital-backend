<?php

namespace App\Http\Controllers;

use App\Models\Internacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InternacionController extends Controller
{
    /**
     * 📋 Muestra todas las internaciones con paciente y médico.
     */
    public function index()
    {
        return response()->json(
            Internacion::with(['paciente', 'medico'])
                ->latest('fecha_ingreso')
                ->get()
        );
    }

    /**
     * ➕ Crea una nueva internación.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha_ingreso'  => 'required|date',
            'motivo'         => 'required|string|max:100',
            'diagnostico'    => 'required|string|max:255',
            'observaciones'  => 'nullable|string|max:255',
            'paciente_id'    => 'required|exists:pacientes,id',
            'user_id'        => 'required|exists:users,id',
        ]);

        $internacion = Internacion::create($data);

        Log::info('Internación registrada', ['id' => $internacion->id]);

        return response()->json($internacion->load(['paciente', 'medico']), 201);
    }

    /**
     * 👁️ Muestra los datos de una internación específica.
     */
    public function show(Internacion $internacion)
    {
        return response()->json($internacion->load(['paciente', 'medico']));
    }

    /**
     * ✏️ Actualiza una internación.
     */
    public function update(Request $request, Internacion $internacion)
    {
        $data = $request->validate([
            'fecha_ingreso'  => 'required|date',
            'fecha_alta'     => 'nullable|date|after_or_equal:fecha_ingreso',
            'motivo'         => 'required|string|max:100',
            'diagnostico'    => 'required|string|max:255',
            'observaciones'  => 'nullable|string|max:255',
            'paciente_id'    => 'required|exists:pacientes,id',
            'user_id'        => 'required|exists:users,id',
        ]);

        $internacion->update($data);

        Log::info('Internación actualizada', ['id' => $internacion->id]);

        return response()->json($internacion->load(['paciente', 'medico']));
    }

    /**
     * 🗑️ Elimina una internación.
     */
    public function destroy(Internacion $internacion)
    {
        $internacion->delete();
        Log::warning('Internación eliminada', ['id' => $internacion->id]);

        return response()->noContent();
    }

    // ------------------------------------------------------------------------
    // 🧠 MÉTODOS DE NEGOCIO PERSONALIZADOS
    // ------------------------------------------------------------------------

    /**
     * 🧾 Vista completa de la internación para panel médico o enfermería.
     */
    public function getVistaCompleta(Internacion $internacion)
    {
        $internacion->load([
            'paciente',
            'medico',
            'ocupacionActiva.cama.sala',

            'tratamientos' => function ($query) {
                // Cargar TODOS los tratamientos (activos e inactivos)
                $query->orderBy('created_at', 'desc')->with([
                    'medico:id,nombre,apellidos',
                    'recetas' => function ($q_receta) {
                        // Cargar TODAS las recetas
                        $q_receta->with('medicamento:id,nombre')
                             ->with(['administras' => fn($q) => $q->latest('fecha')->with('user:id,nombre,apellidos')]);
                    },
                ]);
            },

            'alimentaciones' => function ($q) {
                $q->with([
                    'tipoDieta:id,nombre',
                    'tiempos',
                    'consumes' => function ($q_consumo) {
                        $q_consumo->with('registradoPor:id,nombre,apellidos')
                            ->latest('created_at');
                    }
                ])->latest('fecha_inicio');
            },

            // ✅ CRÍTICO: Cargar controles con TODAS sus relaciones
            'controles' => function ($query) {
                $query->orderBy('fecha_control', 'desc')
                    ->with(['user.rol', 'valores.signo']);
            },

            'cuidados.cuidadosAplicados.user:id,nombre,apellidos',
        ]);

        $this->transformarDatosParaFrontend($internacion);

        // Asegurar formato de array para controles
        if ($internacion->controles instanceof Collection) {
            $internacion->setRelation('controles', $internacion->controles->values());
        }

        return response()->json($internacion);
    }

    /**
     * 👩‍⚕️ Pacientes activos del médico autenticado.
     */
    public function getMisPacientes()
    {
        $medicoId = Auth::id();

        return response()->json(
            Internacion::delMedico($medicoId)
                ->activas()
                ->with(['paciente', 'ocupacionActiva.cama.sala'])
                ->latest('fecha_ingreso')
                ->get()
        );
    }

    /**
     * 🏥 Pacientes activos para estación de enfermería.
     */
    public function getPacientesParaEnfermeria()
    {
        Log::info('Iniciando carga de pacientes para Estación de Enfermería...');

        $internaciones = Internacion::activas()
            ->with([
                'paciente:id,nombre,apellidos,ci',
                'medico:id,nombre,apellidos',
                'ocupacionActiva.cama.sala:id,nombre',
                'tratamientos' => function ($query) {
                    $query->where('estado', 0)->with([
                        'medico:id,nombre,apellidos',
                        'recetas' => function ($q_receta) {
                            $q_receta->where('estado', 0);
                        },
                        'recetas.medicamento:id,nombre',
                        'recetas.administras' => fn($q) => $q->latest('fecha')->with('user:id,nombre,apellidos'),
                    ]);
                },
                'controles' => function ($query) {
                    $query->orderBy('fecha_control', 'desc')
                        ->with(['user.rol', 'valores.signo']);
                },
                'cuidados.cuidadosAplicados.user:id,nombre,apellidos',
                // ✅ CARGAR ALIMENTACIONES CON TIEMPOS Y CONSUMOS
                'alimentaciones' => function ($q) {
                    $q->where('estado', 0)->with([
                        'tipoDieta:id,nombre',
                        'tiempos',  // ✅ ESTO ES LO QUE FALTABA!
                        'consumes' => function ($q_consumo) {
                            $q_consumo->whereDate('fecha', Carbon::today())
                                ->with('registradoPor:id,nombre,apellidos')
                                ->latest('created_at');
                        }
                    ])->latest('fecha_inicio');
                }
            ])
            ->latest('fecha_ingreso')
            ->get();

        Log::info(count($internaciones) . ' internaciones activas encontradas. Procesando datos...');

        // Procesa cada internación para preparar los datos para el frontend
        $internaciones->each(function ($internacion) {
            Log::info('Procesando Internacion ID: ' . $internacion->id . '. Controles encontrados: ' . $internacion->controles->count());

            // 1. Aplica las transformaciones generales (separa plan de cuidados, calcula IMC, etc.)
            $this->transformarDatosParaFrontend($internacion);

            // 2. ✅ SOLUCIÓN CLAVE: Nos aseguramos de que la colección 'controles' tenga índices numéricos
            // consecutivos (0, 1, 2...). Esto garantiza que PHP lo convierta a un array JSON `[...]`
            // en lugar de un objeto JSON `{...}`.
            if ($internacion->controles instanceof Collection) {
                $internacion->setRelation('controles', $internacion->controles->values());
            }
        });

        Log::info('Procesamiento completo. Enviando respuesta final al frontend.');

        return response()->json($internaciones);
    }

    /**
     * 🥗 Internaciones activas para el Panel de Nutrición.
     * Incluye datos clínicos necesarios para la toma de decisiones del nutricionista.
     */
    public function getInternacionesActivas()
    {
        $internaciones = Internacion::activas()
            ->with([
                'paciente:id,nombre,apellidos,ci,fecha_nacimiento',
                'ocupacionActiva.cama.sala:id,nombre',
                'alimentaciones' => function ($q) {
                    $q->where('estado', 0)->with(['tipoDieta:id,nombre', 'tiempos'])->latest('fecha_inicio');
                },
                // Tratamientos activos para ver medicamentos
                'tratamientos' => function ($q) {
                    $q->where('estado', 0)->with(['recetas' => function ($qr) {
                        $qr->where('estado', 0)->with('medicamento:id,nombre');
                    }]);
                },
                // Controles para peso y altura
                'controles' => function ($q) {
                    $q->orderBy('fecha_control', 'asc')->with('valores.signo:id,nombre,unidad');
                }
            ])
            ->latest('fecha_ingreso')
            ->get();

        // Procesar datos clínicos para el nutricionista
        $internaciones->each(function ($internacion) {
            // Alimentación activa
            $internacion->alimentacion_activa = $internacion->alimentaciones->first();

            // Calcular edad
            if ($internacion->paciente && $internacion->paciente->fecha_nacimiento) {
                $internacion->paciente->edad = Carbon::parse($internacion->paciente->fecha_nacimiento)->age;
            }

            // Obtener peso, altura e IMC del primer control
            $datos = ['peso' => null, 'altura' => null, 'imc' => null];
            $controlDeIngreso = $internacion->controles->first(function ($c) {
                return $c->valores->contains(fn($v) => in_array($v->signo->nombre ?? '', ['Peso', 'Altura']));
            });

            if ($controlDeIngreso) {
                $peso = $controlDeIngreso->valores->firstWhere('signo.nombre', 'Peso');
                $altura = $controlDeIngreso->valores->firstWhere('signo.nombre', 'Altura');

                if ($peso) $datos['peso'] = floatval($peso->medida);
                if ($altura) $datos['altura'] = floatval($altura->medida);

                if ($datos['peso'] && $datos['altura'] && $datos['altura'] > 0) {
                    $alturaM = $datos['altura'] / 100;
                    $datos['imc'] = round($datos['peso'] / ($alturaM ** 2), 1);
                }
            }
            $internacion->datos_antropometricos = $datos;

            // Medicamentos activos (para considerar interacciones)
            $medicamentos = [];
            foreach ($internacion->tratamientos as $tratamiento) {
                foreach ($tratamiento->recetas as $receta) {
                    if ($receta->medicamento) {
                        $medicamentos[] = $receta->medicamento->nombre;
                    }
                }
            }
            $internacion->medicamentos_activos = array_unique($medicamentos);

            // Limpiar relaciones no necesarias en la respuesta
            unset($internacion->controles);
            unset($internacion->tratamientos);
        });

        return response()->json($internaciones);
    }

    /**
     * ✅ Da de alta al paciente y libera la cama.
     */
    public function darDeAlta(Internacion $internacion)
    {
        if ($internacion->fecha_alta) {
            return response()->json(['message' => 'El paciente ya fue dado de alta.'], 400);
        }

        try {
            DB::transaction(function () use ($internacion) {
                $internacion->update(['fecha_alta' => now()]);

                $internacion->tratamientos()->where('estado', 0)->update(['estado' => 2]);

                if ($ocupacion = $internacion->ocupacionActiva) {
                    $ocupacion->update(['fecha_desocupacion' => now()]);
                    $ocupacion->cama?->update(['disponibilidad' => 1]);
                }
            });

            Log::info('Paciente dado de alta', ['id' => $internacion->id]);

            try {
                $reporteController = new \App\Http\Controllers\ReporteController();
                $pdf = $reporteController->generarEpicrisis($internacion->id);

                // Guardar el PDF en storage para acceso posterior
                $nombreArchivo = "epicrisis_{$internacion->id}_" . now()->format('Y-m-d_His') . ".pdf";
                $rutaArchivo = storage_path("app/public/reportes/{$nombreArchivo}");

                if (!file_exists(storage_path('app/public/reportes'))) {
                    mkdir(storage_path('app/public/reportes'), 0755, true);
                }

                file_put_contents($rutaArchivo, $pdf->output());

                Log::info('Epicrisis generada automáticamente', [
                    'internacion_id' => $internacion->id,
                    'archivo' => $nombreArchivo
                ]);
            } catch (\Exception $e) {
                Log::error('Error al generar epicrisis automática', [
                    'error' => $e->getMessage(),
                    'internacion_id' => $internacion->id
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Error al dar de alta', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error interno al procesar el alta.'], 500);
        }

        return response()->json([
            'message' => 'Paciente dado de alta exitosamente.',
            'epicrisis_generada' => true
        ]);
    }

    // ------------------------------------------------------------------------
    // 🔧 MÉTODOS PRIVADOS AUXILIARES
    // ------------------------------------------------------------------------

    private function transformarDatosParaFrontend(Internacion $internacion)
    {
        $internacion->setRelation('controles', $internacion->controles->sortByDesc('fecha_control')->values());

        if ($internacion->relationLoaded('cuidados') && $internacion->cuidados->isNotEmpty()) {
            [$plan, $evolucion] = $internacion->cuidados->partition(fn($c) => $c->estado === 0);

            $internacion->plan_de_cuidados = $plan->values();
            $internacion->evolucion_enfermeria = $evolucion
                ->sortByDesc(fn($c) => $c->cuidadosAplicados->first()->fecha_aplicacion ?? $c->created_at)
                ->values();
        } else {
            $internacion->plan_de_cuidados = [];
            $internacion->evolucion_enfermeria = [];
        }

        unset($internacion->cuidados);

        $this->calcularDatosAntropometricos($internacion);
    }

    private function calcularDatosAntropometricos(Internacion $internacion)
    {
        $datos = ['peso' => 'No registrado', 'altura' => 'No registrada', 'imc' => null];

        $controlDeIngreso = $internacion->controles
            ->sortBy('fecha_control')
            ->first(fn($c) => $c->valores->contains(fn($v) => in_array($v->signo->nombre, ['Peso', 'Altura'])));

        if ($controlDeIngreso) {
            $peso = $controlDeIngreso->valores->firstWhere('signo.nombre', 'Peso');
            $altura = $controlDeIngreso->valores->firstWhere('signo.nombre', 'Altura');

            if ($peso) $datos['peso'] = $peso->medida . ' ' . $peso->signo->unidad;
            if ($altura) $datos['altura'] = $altura->medida . ' ' . $altura->signo->unidad;

            if ($peso && $altura && is_numeric($peso->medida) && is_numeric($altura->medida) && $altura->medida > 0) {
                $alturaM = $altura->medida / 100;
                $datos['imc'] = round($peso->medida / ($alturaM ** 2), 1);
            }
        }

        $internacion->datos_antropometricos = $datos;
    }
}
