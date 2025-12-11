<?php

namespace App\Http\Controllers;

use App\Models\Cama;
use App\Models\Internacion;
use App\Models\Ocupacion;
use App\Models\Cuidado; // Importar el modelo Cuidado
use App\Models\Notificacion; // Importar Notificacion
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AdmisionController extends Controller
{
    /**
     * Procesa una nueva admisión completa con signos vitales y plan de cuidados.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            // --- Datos de Admisión ---
            'admision.paciente_id'   => 'required|exists:pacientes,id',
            'admision.cama_id'       => 'required|exists:camas,id',
            'admision.medico_id'     => 'required|exists:users,id',
            'admision.motivo'        => 'required|string|max:191',
            'admision.diagnostico'   => 'required|string|max:191',
            'admision.observaciones' => 'nullable|string|max:191',

            // --- Signos Vitales Iniciales ---
            'signos_vitales'                => 'present|array',
            'signos_vitales.*.signo_id'     => 'required_with:signos_vitales|exists:signos,id',
            'signos_vitales.*.medida'       => 'required_with:signos_vitales|string|max:50',
            'signos_vitales.*.medida_baja'  => 'nullable|string|max:50',

            // --- Plan de Cuidados Inicial ---
            'cuidados'                   => 'present|array',
            'cuidados.*.tipo'            => 'required_with:cuidados|string|max:191',
            'cuidados.*.descripcion'     => 'required_with:cuidados|string|max:191',
            'cuidados.*.frecuencia'      => 'required_with:cuidados|string|max:191',
        ]);

        try {
            $resultado = DB::transaction(function () use ($data) {
                $admisionData = $data['admision'];
                $signosVitalesData = $data['signos_vitales'];
                $cuidadosData = $data['cuidados'];

                // --- Parte 1: Lógica de Admisión ---
                $cama = Cama::lockForUpdate()->findOrFail($admisionData['cama_id']);
                if ($cama->disponibilidad !== 1) { // 1 = Disponible
                    throw new \Exception('La cama seleccionada ya no está disponible.');
                }

                if (Internacion::where('paciente_id', $admisionData['paciente_id'])->whereNull('fecha_alta')->exists()) {
                    throw new \Exception('Este paciente ya tiene una internación activa.');
                }

                $internacion = Internacion::create([
                    'paciente_id'   => $admisionData['paciente_id'],
                    'user_id'       => $admisionData['medico_id'],
                    'fecha_ingreso' => Carbon::now(),
                    'motivo'        => $admisionData['motivo'],
                    'diagnostico'   => $admisionData['diagnostico'],
                    'observaciones' => $admisionData['observaciones'],
                ]);

                $cama->update(['disponibilidad' => 0]); // 0 = Ocupada

                Ocupacion::create([
                    'internacion_id' => $internacion->id,
                    'cama_id'        => $cama->id,
                    'fecha_ocupacion' => Carbon::now(),
                ]);

                // --- Parte 2: Guardar Signos Vitales Iniciales ---
                if (!empty($signosVitalesData)) {
                    $control = $internacion->controles()->create([
                        'user_id'       => Auth::id(),
                        'fecha_control' => Carbon::now(),
                        'tipo'          => 'Control de Ingreso',
                        'observaciones' => 'Registro inicial de signos vitales.'
                    ]);

                    $control->valores()->createMany($signosVitalesData);
                }

                // --- Parte 3: Guardar Plan de Cuidados Inicial ---
                if (!empty($cuidadosData)) {
                    foreach ($cuidadosData as $cuidadoInfo) {
                        $internacion->cuidados()->create([
                            'tipo'         => $cuidadoInfo['tipo'],
                            'descripcion'  => $cuidadoInfo['descripcion'],
                            'frecuencia'   => $cuidadoInfo['frecuencia'],
                            'fecha_inicio' => Carbon::now(),
                            'estado'       => 0, // 0 = Activo
                        ]);
                    }
                }

                // --- Parte 4: Notificar al Médico ---
                Notificacion::create([
                    'user_id' => $admisionData['medico_id'],
                    'internacion_id' => $internacion->id,
                    'tipo' => 'informacion', // O 'recordatorio'
                    'titulo' => "Nuevo Paciente Asignado",
                    'mensaje' => "Se le ha asignado el paciente {$internacion->paciente->nombre} {$internacion->paciente->apellidos} en la cama {$cama->nombre} (Sala {$cama->sala->nombre}).",
                    'leida' => false,
                ]);

                Log::info('Proceso de admisión registrado.', ['internacion_id' => $internacion->id, 'user_id' => Auth::id()]);
                return $internacion;
            });

            return response()->json([
                'message' => 'Paciente admitido y plan de cuidados iniciado con éxito.',
                'data'    => $resultado->load('paciente'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error en el proceso de admisión:', ['error' => $e->getMessage()]);
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }
}
