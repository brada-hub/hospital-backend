<?php

namespace App\Http\Controllers;

use App\Models\Internacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class InternacionController extends Controller
{
    public function index()
    {
        // Incluye paciente y médico
        return Internacion::with(['paciente', 'medico'])->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha_ingreso'  => 'required|date',
            'fecha_alta'     => 'nullable|date|after_or_equal:fecha_ingreso',
            'motivo'         => 'required|string|max:100',
            'diagnostico'    => 'required|string|max:255',
            'observaciones'  => 'nullable|string|max:255',
            'paciente_id'    => 'required|exists:pacientes,id',
            'user_id'        => 'required|exists:users,id', // médico que internó
        ]);

        $internacion = Internacion::create($data);
        Log::info('Internación registrada', ['id' => $internacion->id]);

        return response()->json($internacion->load(['paciente', 'medico']), 201);
    }
    public function getVistaCompleta(Internacion $internacion)
    {
        $internacion->load([
            'paciente',
            'tratamientos.medico',
            'tratamientos.recetas.medicamento',
            'ocupacionActiva.cama.sala.especialidad',

            // ✅ ASEGÚRATE DE QUE ESTA SECCIÓN ESTÉ ASÍ:
            // Carga los controles, el usuario que los escribió,
            // los valores de esos controles y la info del signo vital.
            'controls.user:id,nombre,apellidos',
            'controls.valores.signo'
        ]);

        return response()->json($internacion);
    }
    public function darDeAlta(Request $request, Internacion $internacion)
    {
        if ($internacion->fecha_alta) {
            return response()->json(['message' => 'Este paciente ya fue dado de alta.'], 400);
        }

        // Usamos una transacción para asegurar la integridad de los datos
        try {
            DB::transaction(function () use ($internacion) {
                // 1. Damos de alta al paciente en su internación
                $internacion->fecha_alta = Carbon::now();
                $internacion->save();

                // 2. Finalizamos todos los tratamientos que estaban activos (estado 0 -> 2)
                $internacion->tratamientos()->where('estado', 0)->update(['estado' => 2]);

                // 3. Liberamos la cama
                if ($ocupacion = $internacion->ocupacionActiva) {
                    // Finalizamos la ocupación
                    $ocupacion->fecha_desocupacion = Carbon::now();
                    $ocupacion->save();

                    // Dejamos la cama como 'disponible' (estado 1)
                    if ($cama = $ocupacion->cama) {
                        $cama->disponibilidad = 1; // ✅ CORREGIDO: Usamos el número 1
                        $cama->save();
                    }
                }
            });
        } catch (\Exception $e) {
            Log::error('Error al dar de alta al paciente:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error interno al procesar el alta.'], 500);
        }

        return response()->json(['message' => 'Paciente dado de alta exitosamente.']);
    }
    // En app/Http/Controllers/InternacionController.php

    public function getPacientesParaEnfermeria()
    {
        $internaciones = Internacion::whereNull('fecha_alta')
            ->with([
                'paciente:id,nombre,apellidos,ci',
                'ocupacionActiva.cama.sala:id,nombre',
                'tratamientos' => fn($q) => $q->where('estado', 0)->with([
                    'recetas.medicamento:id,nombre,presentacion',
                    'recetas.administras' => fn($q) => $q->orderBy('fecha', 'desc')->with('user:id,nombre,apellidos'),
                    'medico:id,nombre,apellidos',
                ]),
                'cuidados.cuidadosAplicados.user:id,nombre,apellidos',
                // Carga los controles con sus valores y el signo asociado a cada valor
                'controls' => fn($q) => $q->latest('fecha_control')->with(['user:id,nombre,apellidos', 'valores.signo']),
            ])
            ->latest('fecha_ingreso')
            ->get();

        $internaciones->each(function ($internacion) {
            // --- Lógica para separar cuidados (esta parte está bien) ---
            if ($internacion->cuidados->isNotEmpty()) {
                $partitions = $internacion->cuidados->partition(function ($cuidado) {
                    return $cuidado->estado === 0;
                });
                $internacion->plan_de_cuidados = $partitions[0]->values();
                $internacion->evolucion_enfermeria = $partitions[1]->sortByDesc(function ($cuidado) {
                    return $cuidado->cuidadosAplicados->first()->fecha_aplicacion ?? $cuidado->created_at;
                })->values();
                unset($internacion->cuidados);
            } else {
                $internacion->plan_de_cuidados = [];
                $internacion->evolucion_enfermeria = [];
            }

            // ✅ **LÓGICA ANTROPOMÉTRICA RESTAURADA Y CORREGIDA**
            $datosAntropometricos = [
                'peso'   => 'No registrado',
                'altura' => 'No registrada',
                'imc'    => null,
            ];

            // 1. Ordena los controles por fecha, del más antiguo al más reciente.
            // 2. Busca el PRIMER control que tenga un valor para 'Peso' o 'Altura'.
            $controlDeIngreso = $internacion->controls->sortBy('fecha_control')->first(function ($control) {
                return $control->valores->contains(function ($valor) {
                    // Asegúrate que los nombres 'Peso' y 'Altura' coincidan con los de tu tabla 'signos'
                    return in_array($valor->signo->nombre, ['Peso', 'Altura']);
                });
            });

            if ($controlDeIngreso) {
                $pesoValor = $controlDeIngreso->valores->firstWhere('signo.nombre', 'Peso');
                $alturaValor = $controlDeIngreso->valores->firstWhere('signo.nombre', 'Altura');

                if ($pesoValor) {
                    $datosAntropometricos['peso'] = $pesoValor->medida . ' ' . $pesoValor->signo->unidad;
                }
                if ($alturaValor) {
                    $datosAntropometricos['altura'] = $alturaValor->medida . ' ' . $alturaValor->signo->unidad;
                }

                // Calcular IMC solo si tenemos valores numéricos para ambos
                if ($pesoValor && $alturaValor && is_numeric($pesoValor->medida) && is_numeric($alturaValor->medida)) {
                    $pesoKg = (float) $pesoValor->medida;
                    $alturaCm = (float) $alturaValor->medida;
                    if ($alturaCm > 0) {
                        $alturaM = $alturaCm / 100;
                        $datosAntropometricos['imc'] = round($pesoKg / ($alturaM ** 2), 1);
                    }
                }
            }

            $internacion->datos_antropometricos = $datosAntropometricos;
            // ✅ **FIN DE LA LÓGICA CORREGIDA**
        });

        return response()->json($internaciones);
    }

    public function getMisPacientes(Request $request)
    {
        $medicoId = Auth::id();

        $internacionesActivas = Internacion::where('user_id', $medicoId) // Filtra por el médico logueado
            ->whereNull('fecha_alta') // Solo las que están activas
            ->with([
                'paciente', // Carga los datos del paciente
                'ocupacionActiva.cama.sala' // Carga la ubicación
            ])
            ->latest('fecha_ingreso') // Las más recientes primero
            ->get();

        return response()->json($internacionesActivas);
    }
    public function getDashboardData($id)
    {
        try {
            $internacion = Internacion::with([
                // Cargar datos del paciente
                'paciente',
                // Cargar el médico que lo internó
                'medico',
                // Cargar la ubicación actual
                'ocupaciones' => function ($query) {
                    // Solo nos interesa la ocupación actual, no traslados pasados
                    $query->whereNull('fecha_desocupacion')->with(['cama.sala.especialidad']);
                },
                // Cargar TODOS los tratamientos de esta internación
                'tratamientos' => function ($query) {
                    $query->with([
                        // Para cada tratamiento, cargar sus recetas
                        'recetas' => function ($query) {
                            $query->with([
                                // Para cada receta, cargar el nombre del medicamento
                                'medicamento',
                                // Y también cargar el historial de administraciones
                                'administras.user' // 'usuario' es el enfermero/a que administró
                            ]);
                        },
                        // Cargar el médico que prescribió el tratamiento
                        'medico'
                    ])->orderBy('fecha_inicio', 'desc'); // Ordenar por más reciente
                }
            ])->findOrFail($id);

            return response()->json($internacion);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Internación no encontrada.'], 404);
        } catch (\Exception $e) {
            Log::error('Error al obtener datos del dashboard clínico:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error interno del servidor.'], 500);
        }
    }

    public function show($id)
    {
        return Internacion::with(['paciente', 'medico'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $internacion = Internacion::findOrFail($id);

        $data = $request->validate([
            'fecha_ingreso'  => 'required|date',
            'fecha_alta'     => 'nullable|date|after_or_equal:fecha_ingreso',
            'motivo'         => 'required|string|max:100',
            'diagnostico'    => 'required|string|max:255',
            'observaciones'  => 'nullable|string|max:255',
            'paciente_id'    => 'required|exists:pacientes,id',
            'user_id'        => 'required|exists:users,id', // médico que internó
        ]);

        $internacion->update($data);
        Log::info('Internación actualizada', ['id' => $internacion->id]);

        return response()->json($internacion->load(['paciente', 'medico']), 200);
    }

    public function destroy($id)
    {
        $internacion = Internacion::findOrFail($id);
        $internacion->delete();

        Log::warning('Internación eliminada', ['id' => $id]);
        return response()->noContent();
    }
}
