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

        DB::beginTransaction();
        try {
            $rolPaciente = Rol::where('nombre', 'PACIENTE')->first();

            if (!$rolPaciente) {
                throw new \Exception('El rol PACIENTE no existe. Ejecuta el seeder primero.');
            }

            $usuarioAutenticado = Auth::user();
            if (!$usuarioAutenticado) {
                throw new \Exception('Usuario no autenticado.');
            }

            /** @var User $user */
            $user = User::create([
                'nombre' => $data['nombre'],
                'apellidos' => $data['apellidos'],
                'email' => strtolower(str_replace(['-', ' '], '', $data['ci'])) . '@paciente.local',
                'telefono' => $data['telefono'],
                'password' => Hash::make($data['ci']),
                'rol_id' => $rolPaciente->id,
                'hospital_id' => $usuarioAutenticado->hospital_id,
            ]);

            $data['user_id'] = $user->id;
            $paciente = Paciente::create($data);

            DB::commit();
            Log::info('Paciente y usuario creados', [
                'paciente_id' => $paciente->id,
                'user_id' => $user->id
            ]);

            return response()->json([
                'paciente' => $paciente,
                'credenciales' => [
                    'email' => $user->email,
                    'password' => $data['ci'],
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear paciente y usuario', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error al crear paciente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function buscar(Request $request)
    {
        $request->validate(['termino' => 'required|string|min:2']);
        $termino = $request->termino;

        $pacientes = Paciente::where(function ($query) use ($termino) {
            $query->where('ci', 'LIKE', "%{$termino}%")
                ->orWhere('nombre', 'LIKE', "%{$termino}%")
                ->orWhere('apellidos', 'LIKE', "%{$termino}%")
                ->orWhere('telefono', 'LIKE', "%{$termino}%");
        })
            ->take(10)
            ->get();

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

        $paciente->load('user');

        if ($paciente->user) {
            $paciente->user->update([
                'nombre' => $data['nombre'],
                'apellidos' => $data['apellidos'],
                'telefono' => $data['telefono'],
            ]);
        }

        $paciente->update($data);
        Log::info('Paciente actualizado', ['id' => $paciente->id]);

        return response()->json($paciente, 200);
    }

    public function destroy($id)
    {
        /** @var Paciente $paciente */
        $paciente = Paciente::findOrFail($id);
        $paciente->load('user');

        if ($paciente->user) {
            // $paciente->user->update(['activo' => false]);
        }

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
                // 👇 AGREGAR ESTA LÍNEA - Carga los cuidados con sus aplicaciones y usuario
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
