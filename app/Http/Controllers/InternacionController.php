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
    /**
     * Muestra una lista de todas las internaciones.
     */
    public function index()
    {
        return Internacion::with(['paciente', 'medico'])->latest('fecha_ingreso')->get();
    }

    /**
     * Almacena una nueva internación en la base de datos.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha_ingreso'  => 'required|date',
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

    /**
     * Muestra una internación específica.
     */
    public function show($id)
    {
        return Internacion::with(['paciente', 'medico'])->findOrFail($id);
    }

    /**
     * Actualiza una internación específica.
     */
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
            'user_id'        => 'required|exists:users,id',
        ]);

        $internacion->update($data);
        Log::info('Internación actualizada', ['id' => $internacion->id]);

        return response()->json($internacion->load(['paciente', 'medico']), 200);
    }

    /**
     * Elimina una internación.
     */
    public function destroy($id)
    {
        $internacion = Internacion::findOrFail($id);
        $internacion->delete();

        Log::warning('Internación eliminada', ['id' => $id]);
        return response()->noContent();
    }

    // --- MÉTODOS DE LÓGICA DE NEGOCIO ---

    /**
     * ✅ MÉTODO PRINCIPAL PARA EL PANEL DEL PACIENTE
     * Devuelve una vista completa de la internación con todas las relaciones necesarias para el frontend.
     */
    /**
     * ✅ MÉTODO PARA EL PANEL DEL MÉDICO
     */
    public function getVistaCompleta(Internacion $internacion)
    {
        $internacion->load([
            'paciente',
            'medico',
            'ocupacionActiva.cama.sala',
            'tratamientos.medico',
            'alimentaciones.tipoDieta',
            'controls.user.rol',
            'controls.valores.signo',
            'cuidados.cuidadosAplicados.user:id,nombre,apellidos',
        ]);

        $this->transformarDatosParaFrontend($internacion);
        return response()->json($internacion);
    }

    /**
     * ✅ MÉTODO PARA LA ESTACIÓN DE ENFERMERÍA
     */
    public function getPacientesParaEnfermeria()
    {
        $internaciones = Internacion::whereNull('fecha_alta')
            ->with([
                'paciente:id,nombre,apellidos,ci',
                'ocupacionActiva.cama.sala:id,nombre',
                'tratamientos' => fn($q) => $q->where('estado', 0)->with([
                    'recetas.medicamento:id,nombre',
                    'recetas.administras' => fn($q) => $q->orderBy('fecha', 'desc')->with('user:id,nombre,apellidos'),
                    'medico:id,nombre,apellidos',
                ]),
                'controls.user.rol',
                'controls.valores.signo',
                'cuidados.cuidadosAplicados.user:id,nombre,apellidos',
            ])
            ->latest('fecha_ingreso')
            ->get();

        $internaciones->each(function ($internacion) {
            $this->transformarDatosParaFrontend($internacion);
        });

        return response()->json($internaciones);
    }

    /**
     * ✅ NUEVA FUNCIÓN PRIVADA PARA TRANSFORMAR DATOS
     * Centraliza la lógica para ordenar y separar datos.
     */
    private function transformarDatosParaFrontend(Internacion $internacion)
    {
        $internacion->setRelation('controls', $internacion->controls->sortByDesc('fecha_control')->values());

        if ($internacion->relationLoaded('cuidados') && $internacion->cuidados->isNotEmpty()) {
            $partitions = $internacion->cuidados->partition(fn($cuidado) => $cuidado->estado === 0);
            $internacion->plan_de_cuidados = $partitions[0]->values();
            $internacion->evolucion_enfermeria = $partitions[1]->sortByDesc(
                fn($c) => $c->cuidadosAplicados->first()->fecha_aplicacion ?? $c->created_at
            )->values();
        } else {
            $internacion->plan_de_cuidados = [];
            $internacion->evolucion_enfermeria = [];
        }
        unset($internacion->cuidados);

        $this->calcularDatosAntropometricos($internacion);
    }
    /**
     * Obtiene los pacientes activos para el médico autenticado.
     */
    public function getMisPacientes(Request $request)
    {
        $medicoId = Auth::id();

        $internacionesActivas = Internacion::where('user_id', $medicoId)
            ->whereNull('fecha_alta')
            ->with(['paciente', 'ocupacionActiva.cama.sala'])
            ->latest('fecha_ingreso')
            ->get();

        return response()->json($internacionesActivas);
    }

    /**
     * Marca la fecha de alta de un paciente y libera los recursos asociados.
     */
    public function darDeAlta(Request $request, Internacion $internacion)
    {
        if ($internacion->fecha_alta) {
            return response()->json(['message' => 'Este paciente ya fue dado de alta.'], 400);
        }

        try {
            DB::transaction(function () use ($internacion) {
                // 1. Damos de alta al paciente
                $internacion->fecha_alta = Carbon::now();
                $internacion->save();

                // 2. Finalizamos tratamientos activos (estado 0 -> 2)
                $internacion->tratamientos()->where('estado', 0)->update(['estado' => 2]);

                // 3. Liberamos la cama
                if ($ocupacion = $internacion->ocupacionActiva) {
                    $ocupacion->fecha_desocupacion = Carbon::now();
                    $ocupacion->save();

                    if ($cama = $ocupacion->cama) {
                        $cama->disponibilidad = 1; // 1 = Disponible
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

    /**
     * Obtiene los datos necesarios para la estación de enfermería.
     */


    /**
     * Función auxiliar para calcular peso, altura e IMC.
     */
    private function calcularDatosAntropometricos(Internacion $internacion)
    {
        $datos = ['peso' => 'No registrado', 'altura' => 'No registrada', 'imc' => null];

        $controlDeIngreso = $internacion->controls->sortBy('fecha_control')->first(function ($control) {
            return $control->valores->contains(fn($v) => in_array($v->signo->nombre, ['Peso', 'Altura']));
        });

        if ($controlDeIngreso) {
            $pesoValor = $controlDeIngreso->valores->firstWhere('signo.nombre', 'Peso');
            $alturaValor = $controlDeIngreso->valores->firstWhere('signo.nombre', 'Altura');

            if ($pesoValor) {
                $datos['peso'] = $pesoValor->medida . ' ' . $pesoValor->signo->unidad;
            }
            if ($alturaValor) {
                $datos['altura'] = $alturaValor->medida . ' ' . $alturaValor->signo->unidad;
            }

            if ($pesoValor && $alturaValor && is_numeric($pesoValor->medida) && is_numeric($alturaValor->medida) && $alturaValor->medida > 0) {
                $alturaM = (float) $alturaValor->medida / 100;
                $datos['imc'] = round((float) $pesoValor->medida / ($alturaM ** 2), 1);
            }
        }
        $internacion->datos_antropometricos = $datos;
    }
}
