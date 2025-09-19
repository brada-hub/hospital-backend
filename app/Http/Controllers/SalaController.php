<?php

namespace App\Http\Controllers;

use App\Models\Sala;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SalaController extends Controller
{
    public function index(Request $request)
    {
        $hospitalId = $request->user()->hospital_id;
        return Sala::with('especialidad')
            ->whereHas('especialidad', fn($query) => $query->where('hospital_id', $hospitalId))
            ->orderBy('nombre')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'          => [
                'required',
                'string',
                'max:100',
                Rule::unique('salas')->where('especialidad_id', $request->especialidad_id),
                'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s-]+$/'
            ],
            'tipo'            => [
                'required',
                'string',
                Rule::in(['SALA COMÚN', 'QUIRÓFANO', 'CONSULTORIO', 'TERAPIA INTENSIVA (UTI)', 'LABORATORIO', 'SALA DE ESPERA'])
            ],
            'especialidad_id' => 'required|exists:especialidads,id',
        ]);

        $data['estado'] = true;
        $sala = Sala::create($data);
        return response()->json($sala->load('especialidad'), 201);
    }

    public function show($id)
    {
        return Sala::with(['especialidad', 'camas'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $sala = Sala::findOrFail($id);
        $data = $request->validate([
            'nombre'          => [
                'required',
                'string',
                'max:100',
                Rule::unique('salas')->where('especialidad_id', $request->especialidad_id)->ignore($sala->id),
                'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s-]+$/'
            ],
            'tipo'            => [
                'required',
                'string',
                Rule::in(['SALA COMÚN', 'QUIRÓFANO', 'CONSULTORIO', 'TERAPIA INTENSIVA (UTI)', 'LABORATORIO', 'SALA DE ESPERA'])
            ],
            // 'estado' => 'required|boolean', // <-- SE QUITA DE AQUÍ
            'especialidad_id' => 'required|exists:especialidads,id',
        ]);

        $sala->update($data);
        return response()->json($sala->load('especialidad'), 200);
    }

    public function destroy(Sala $sala)
    {
        if ($sala->camas()->where('estado', 1)->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar. La sala tiene camas activas asignadas.'
            ], 409);
        }

        $sala->update(['estado' => false]);
        return response()->json(['message' => 'Sala eliminada correctamente'], 200);
    }
}
