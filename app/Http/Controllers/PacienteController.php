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
            'ci'              => 'required|string|max:20|unique:pacientes,ci',
            'nombre'          => 'required|string|max:50',
            'apellidos'       => 'required|string|max:50',
            'fecha_nacimiento'=> 'required|date',
            'genero'          => 'required|in:masculino,femenino,otro',
            'telefono'        => 'required|digits_between:7,15',
            'direccion'       => 'required|string|max:255',
            'estado'          => 'required|string|max:20'
        ]);

        $paciente = Paciente::create($data);
        Log::info('Paciente registrado', ['id' => $paciente->id]);

        return response()->json($paciente, 201);
    }

    public function show($id)
    {
        return Paciente::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $paciente = Paciente::findOrFail($id);

        $data = $request->validate([
            'ci'              => 'required|string|max:20|unique:pacientes,ci,' . $paciente->id,
            'nombre'          => 'required|string|max:50',
            'apellidos'       => 'required|string|max:50',
            'fecha_nacimiento'=> 'required|date',
            'genero'          => 'required|in:masculino,femenino,otro',
            'telefono'        => 'required|digits_between:7,15',
            'direccion'       => 'required|string|max:255',
            'estado'          => 'required|string|max:20'
        ]);

        $paciente->update($data);
        Log::info('Paciente actualizado manualmente', ['id' => $paciente->id]);

        return response()->json($paciente, 200);
    }

    public function destroy($id)
    {
        $paciente = Paciente::findOrFail($id);
        $paciente->delete();

        Log::warning('Paciente eliminado', ['id' => $id]);
        return response()->noContent();
    }
}
