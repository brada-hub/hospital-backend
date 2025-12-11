<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PacienteController extends Controller
{
    public function index()
    {
        $pacientes = Paciente::with(['internacionActiva', 'user'])->latest()->get();

        return $pacientes->map(function ($paciente) {
            $paciente->internacion_activa_id = $paciente->internacionActiva?->id;
            unset($paciente->internacionActiva);
            return $paciente;
        });
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ci' => 'required|string|max:20|unique:pacientes,ci',
            'nombre' => 'required|string|max:50',
            'apellidos' => 'required|string|max:50',
            'fecha_nacimiento' => 'required|date',
            'genero' => 'required|in:masculino,femenino,otro',
            'telefono' => 'required|digits_between:7,15',
            'direccion' => 'required|string|max:255',
            'nombre_referencia' => 'nullable|string|max:50',
            'apellidos_referencia' => 'nullable|string|max:50',
            'celular_referencia' => 'nullable|digits_between:7,15',
            'estado' => 'required|boolean'
        ]);

        try {
            // Eliminar lógica de creación de usuario
            // $data['user_id'] = null; // Asumimos que es nullable o no se envía

            $paciente = Paciente::create($data);

            Log::info('Paciente creado', ['paciente_id' => $paciente->id]);

            return response()->json([
                'paciente' => $paciente,
                'message' => 'Paciente registrado correctamente'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error al crear paciente', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error al crear paciente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ NUEVO: Obtener todos los pacientes sin filtro (para el select inicial)
     * Excluye pacientes que ya tienen internación activa
     */
    public function todos()
    {
        $pacientes = Paciente::where('estado', true)
            ->whereDoesntHave('internacionActiva')
            ->select('id', 'ci', 'nombre', 'apellidos', 'telefono')
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->get();

        return response()->json($pacientes);
    }

    /**
     * Buscar pacientes por término (requiere mínimo 2 caracteres)
     * Excluye pacientes que ya tienen internación activa
     */
    public function buscar(Request $request)
    {
        // ✅ MEJORADO: Hacer el parámetro opcional
        $request->validate([
            'termino' => 'nullable|string|min:2'
        ]);

        $termino = $request->input('termino');

        // Query base: solo pacientes activos sin internación activa
        $query = Paciente::where('estado', true)
            ->whereDoesntHave('internacionActiva')
            ->select('id', 'ci', 'nombre', 'apellidos', 'telefono');

        // Si no hay término, devolver todos los pacientes disponibles
        if (empty($termino)) {
            $pacientes = $query->orderBy('nombre')
                ->orderBy('apellidos')
                ->take(50)
                ->get();
        } else {
            // Si hay término, filtrar
            $pacientes = $query->where(function ($q) use ($termino) {
                $q->where('ci', 'LIKE', "%{$termino}%")
                    ->orWhere('nombre', 'LIKE', "%{$termino}%")
                    ->orWhere('apellidos', 'LIKE', "%{$termino}%")
                    ->orWhere('telefono', 'LIKE', "%{$termino}%");
            })
                ->orderBy('nombre')
                ->orderBy('apellidos')
                ->take(20)
                ->get();
        }

        return response()->json($pacientes);
    }

    public function show($id)
    {
        $paciente = Paciente::with(['internacionActiva', 'user'])->findOrFail($id);
        return $paciente;
    }

    public function update(Request $request, $id)
    {
        /** @var Paciente $paciente */
        $paciente = Paciente::findOrFail($id);

        $data = $request->validate([
            'ci' => 'required|string|max:20|unique:pacientes,ci,' . $paciente->id,
            'nombre' => 'required|string|max:50',
            'apellidos' => 'required|string|max:50',
            'fecha_nacimiento' => 'required|date',
            'genero' => 'required|in:masculino,femenino,otro',
            'telefono' => 'required|digits_between:7,15',
            'direccion' => 'required|string|max:255',
            'nombre_referencia' => 'nullable|string|max:50',
            'apellidos_referencia' => 'nullable|string|max:50',
            'celular_referencia' => 'nullable|digits_between:7,15',
            'estado' => 'required|boolean'
        ]);

        // $paciente->load('user');
        // if ($paciente->user) { ... }

        $paciente->update($data);
        Log::info('Paciente actualizado', ['id' => $paciente->id]);

        return response()->json($paciente, 200);
    }

    public function destroy($id)
    {
        /** @var Paciente $paciente */
        $paciente = Paciente::findOrFail($id);
        // $paciente->load('user');
        // if ($paciente->user) { ... }

        $paciente->update(['estado' => !$paciente->estado]);
        Log::warning('Estado del paciente actualizado', ['id' => $id]);

        return response()->json([
            'message' => 'Estado del paciente actualizado',
            'paciente' => $paciente
        ], 200);
    }

    public function miInternacion(Request $request)
    {
        $user = $request->user();

        if (!$user->paciente) {
            return response()->json(['message' => 'Usuario no es un paciente'], 403);
        }

        $internacion = $user->paciente->internacionActiva()
            ->with([
                'paciente:id,nombre,apellidos,ci,fecha_nacimiento,genero',
                'medico:id,nombre,apellidos',
                'ocupacionActiva.cama.sala:id,nombre',
                'tratamientos' => function ($query) {
                    $query->where('estado', 0)
                        ->with([
                            'medico:id,nombre,apellidos',
                            'recetas' => function ($q) {
                                $q->where('estado', 0);
                            },
                            'recetas.medicamento:id,nombre',
                        ]);
                },
                'controles' => function ($query) {
                    $query->orderBy('fecha_control', 'desc')
                        ->with(['user:id,nombre,apellidos', 'valores.signo']);
                },
                'alimentaciones' => function ($query) {
                    $query->where('estado', 0)
                        ->with([
                            'tipoDieta:id,nombre,descripcion',
                            'tiempos',
                            'consumes' => function ($q_consumo) {
                                $q_consumo->whereDate('fecha', today())
                                    ->orderBy('created_at', 'desc');
                            }
                        ]);
                },
                'cuidados' => function ($query) {
                    $query->with([
                        'cuidadosAplicados' => function ($q) {
                            $q->orderBy('fecha_aplicacion', 'desc')
                                ->with('user:id,nombre,apellidos');
                        }
                    ]);
                },
            ])
            ->first();

        if (!$internacion) {
            return response()->json([
                'message' => 'No hay internación activa',
                'has_internacion' => false
            ], 200);
        }

        Log::info('Paciente consultó su internación', [
            'paciente_id' => $user->paciente->id,
            'internacion_id' => $internacion->id
        ]);

        return response()->json([
            'has_internacion' => true,
            'internacion' => $internacion
        ]);
    }
}
