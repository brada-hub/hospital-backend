<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PacienteController extends Controller
{
    /**
     * ✅ MODIFICADO: Ahora devuelve los pacientes junto con el ID de su internación activa.
     */
    public function index()
    {
        // 1. Carga todos los pacientes y, en una sola consulta extra,
        //    trae su 'internacionActiva' si existe (Eager Loading).
        $pacientes = Paciente::with('internacionActiva')->latest()->get();

        // 2. Mapeamos el resultado para crear el campo 'internacion_activa_id'
        //    que el frontend necesita.
        return $pacientes->map(function ($paciente) {
            // Si existe una internación activa, asigna su ID. Si no, asigna null.
            $paciente->internacion_activa_id = $paciente->internacionActiva?->id;
            // Limpiamos la relación completa para no enviar datos de más al frontend.
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
            'estado' => 'required|boolean'
        ]);

        $paciente = Paciente::create($data);
        Log::info('Paciente registrado', ['id' => $paciente->id]);

        return response()->json($paciente, 201);
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
        // Para un solo paciente, también podemos cargar la internación activa
        $paciente = Paciente::with('internacionActiva')->findOrFail($id);
        return $paciente;
    }

    public function update(Request $request, $id)
    {
        $paciente = Paciente::findOrFail($id);

        $data = $request->validate([
            'ci' => 'required|string|max:20|unique:pacientes,ci,' . $paciente->id,
            'nombre' => 'required|string|max:50',
            'apellidos' => 'required|string|max:50',
            'fecha_nacimiento' => 'required|date',
            'genero' => 'required|in:masculino,femenino,otro',
            'telefono' => 'required|digits_between:7,15',
            'direccion' => 'required|string|max:255',
            'estado' => 'required|boolean'
        ]);

        $paciente->update($data);
        Log::info('Paciente actualizado', ['id' => $paciente->id]);

        return response()->json($paciente, 200);
    }

    public function destroy($id)
    {
        $paciente = Paciente::findOrFail($id);
        $paciente->update(['estado' => !$paciente->estado]);
        Log::warning('Estado del paciente actualizado', ['id' => $id]);

        return response()->json([
            'message' => 'Estado del paciente actualizado',
            'paciente' => $paciente
        ], 200);
    }
}
