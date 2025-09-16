<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PacienteController extends Controller
{
    public function index()
    {
        return Paciente::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ci'               => 'required|string|max:20|unique:pacientes,ci',
            'nombre'           => 'required|string|max:50',
            'apellidos'        => 'required|string|max:50',
            'fecha_nacimiento' => 'required|date',
            'genero'           => 'required|in:masculino,femenino,otro',
            'telefono'         => 'required|digits_between:7,15',
            'direccion'        => 'required|string|max:255',
            // CAMBIADO: La validación ahora es para un booleano.
            'estado'           => 'required|boolean'
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
        return Paciente::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $paciente = Paciente::findOrFail($id);

        $data = $request->validate([
            'ci'               => 'required|string|max:20|unique:pacientes,ci,' . $paciente->id,
            'nombre'           => 'required|string|max:50',
            'apellidos'        => 'required|string|max:50',
            'fecha_nacimiento' => 'required|date',
            'genero'           => 'required|in:masculino,femenino,otro',
            'telefono'         => 'required|digits_between:7,15',
            'direccion'        => 'required|string|max:255',
            // CAMBIADO: La validación ahora es para un booleano.
            'estado'           => 'required|boolean'
        ]);

        $paciente->update($data);
        Log::info('Paciente actualizado', ['id' => $paciente->id]);

        return response()->json($paciente, 200);
    }

    public function destroy($id)
    {
        $paciente = Paciente::findOrFail($id);

        // CAMBIADO: En lugar de borrar, ahora alterna el estado (activo/inactivo).
        $paciente->update(['estado' => !$paciente->estado]);

        Log::warning('Estado del paciente actualizado', ['id' => $id]);

        return response()->json([
            'message' => 'Estado del paciente actualizado',
            'paciente' => $paciente
        ], 200);
    }
}
