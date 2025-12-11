<?php

namespace App\Http\Controllers;

use App\Models\Control;
use App\Models\Valor;
use App\Models\RangoNormal;
use App\Models\Signo;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ControlController extends Controller
{
    /**
     * Guarda una nueva nota de evolución.
     */
    public function index(Request $request)
    {
        $request->validate(['internacion_id' => 'sometimes|integer|exists:internacions,id']);
        $query = Control::with('user:id,nombre,apellidos');
        if ($request->has('internacion_id')) {
            $query->where('internacion_id', $request->internacion_id);
        }
        return $query->latest('fecha_control')->get();
    }

    public function store(Request $request)
    {
        // CASO 1: REGISTRO DE SIGNOS VITALES
        if ($request->has('valores')) {
            $data = $request->validate([
                'internacion_id' => 'required|exists:internacions,id',
                'observaciones'  => 'nullable|string',
                'valores'        => 'required|array|min:1',
                'valores.*.signo_id' => 'required|exists:signos,id',
                'valores.*.medida'   => 'required|numeric',
                'valores.*.medida_baja' => 'nullable',
            ]);

            try {
                return DB::transaction(function () use ($data) {
                    $control = Control::create([
                        'internacion_id' => $data['internacion_id'],
                        'user_id'        => Auth::id(),
                        'fecha_control'  => Carbon::now(),
                        'tipo'           => 'Control de Signos Vitales',
                        'observaciones'  => $data['observaciones'] ?? null,
                    ]);

                    $alertasGeneradas = [];

                    foreach ($data['valores'] as $valor) {
                        // Guardar el valor
                        Valor::create([
                            'control_id' => $control->id,
                            'signo_id' => $valor['signo_id'],
                            'medida' => $valor['medida'],
                            'medida_baja' => $valor['medida_baja'] ?? null,
                        ]);

                        // Verificar si está fuera de rango
                        $rango = RangoNormal::where('signo_id', $valor['signo_id'])->first();

                        if ($rango) {
                            $medida = (float) $valor['medida'];

                            if ($rango->estaFueraDeRango($medida)) {
                                $signo = Signo::find($valor['signo_id']);
                                $alertasGeneradas[] = [
                                    'signo' => $signo->nombre,
                                    'valor' => $medida,
                                    'unidad' => $signo->unidad,
                                    'rango' => "{$rango->valor_minimo} - {$rango->valor_maximo}",
                                ];
                            }
                        }
                    }

                    // Si hay alertas, notificar al doctor
                    if (!empty($alertasGeneradas)) {
                        $this->enviarAlertaADoctor($control, $alertasGeneradas);
                    }

                    Log::info('Control de signos vitales registrado', [
                        'id' => $control->id,
                        'alertas' => count($alertasGeneradas)
                    ]);

                    return response()->json([
                        'message' => 'Signos vitales registrados correctamente',
                        'control' => $control->load('valores.signo'),
                        'alertas_generadas' => count($alertasGeneradas),
                    ], 201);
                });
            } catch (\Exception $e) {
                Log::error('Error al registrar control', ['error' => $e->getMessage()]);
                return response()->json(['message' => 'Error interno al registrar el control.'], 500);
            }
        }

        // CASO 2: REGISTRO DE NOTA DE EVOLUCIÓN
        else {
            $data = $request->validate([
                'internacion_id' => 'required|exists:internacions,id',
                'observaciones'  => 'required|string',
                'tipo'           => 'required|string|in:Evolución Médica,Nota de Enfermería,Interconsulta,Informe de Laboratorio',
            ]);

            $data['user_id'] = Auth::id();
            $data['fecha_control'] = Carbon::now();
            $control = Control::create($data);
            Log::info('Nota de evolución registrada', ['id' => $control->id]);
            return response()->json($control->load('user:id,nombre,apellidos'), 201);
        }
    }

    private function enviarAlertaADoctor($control, $alertas)
    {
        $internacion = $control->internacion()->with('paciente')->first();
        $doctorId = $internacion->user_id;

        $pacienteNombre = "{$internacion->paciente->nombre} {$internacion->paciente->apellidos}";

        // Construir mensaje
        $mensajeDetalle = "Se han detectado valores fuera de rango:\n";
        foreach ($alertas as $alerta) {
            $mensajeDetalle .= "• {$alerta['signo']}: {$alerta['valor']} {$alerta['unidad']} ";
            $mensajeDetalle .= "(Normal: {$alerta['rango']})\n";
        }

        // Crear notificación
        Notificacion::create([
            'user_id' => $doctorId,
            'internacion_id' => $internacion->id,
            'control_id' => $control->id,
            'tipo' => 'critica',
            'titulo' => "Signos Vitales - {$pacienteNombre}",
            'mensaje' => $mensajeDetalle,
            'leida' => false,
        ]);
    }

    /**
     * Muestra una nota de evolución específica.
     */
    public function show(Control $control)
    {
        return $control->load('user:id,nombre,apellidos');
    }

    /**
     * Actualiza una nota de evolución.
     */
    public function update(Request $request, Control $control)
    {
        $data = $request->validate([
            'observaciones'  => 'sometimes|required|string',
            'tipo'           => 'sometimes|required|string|in:Evolución Médica,Nota de Enfermería,Interconsulta,Informe de Laboratorio',
        ]);

        $control->update($data);
        Log::info('Control actualizado', ['id' => $control->id]);

        return response()->json($control->load('user:id,nombre,apellidos'), 200);
    }

    /**
     * Elimina una nota de evolución.
     */
    public function destroy(Control $control)
    {
        Log::warning('Control eliminado', ['id' => $control->id]);
        $control->delete();
        return response()->noContent();
    }
}
