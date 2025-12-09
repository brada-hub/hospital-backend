<?php

namespace App\Http\Controllers;

use App\Models\Cama;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CamaController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }
            $hospitalId = $user->hospital_id;

            return Cama::with('sala.especialidad')
                ->whereHas('sala.especialidad', function ($query) use ($hospitalId) {
                    $query->where('hospital_id', $hospitalId);
                })
                ->orderBy('nombre')
                ->get();
        } catch (\Throwable $e) {
            Log::error('Error en CamaController@index: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Error interno al cargar camas: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'         => [
                'required',
                'string',
                'max:100',
                Rule::unique('camas')->where('sala_id', $request->sala_id),
                'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s-]+$/'
            ],
            'tipo'           => [
                'required',
                'string',
                Rule::in(['ESTÁNDAR', 'PEDIÁTRICA', 'CUNA', 'INCUBADORA', 'CAMA UCI', 'CAMA QUIRÚRGICA'])
            ],
            'disponibilidad' => 'required|integer|in:0,1,2',
            'sala_id'        => 'required|exists:salas,id',
        ]);

        $data['estado'] = true;
        $cama = Cama::create($data);
        return response()->json($cama->load('sala'), 201);
    }

    public function show($id)
    {
        return Cama::with('sala')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $cama = Cama::findOrFail($id);
        $data = $request->validate([
            'nombre'         => [
                'required',
                'string',
                'max:100',
                Rule::unique('camas')->where('sala_id', $request->sala_id)->ignore($cama->id),
                'regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s-]+$/'
            ],
            'tipo'           => [
                'required',
                'string',
                Rule::in(['ESTÁNDAR', 'PEDIÁTRICA', 'CUNA', 'INCUBADORA', 'CAMA UCI', 'CAMA QUIRÚRGICA'])
            ],
            'disponibilidad' => 'required|integer|in:0,1,2',
            'sala_id'        => 'required|exists:salas,id',
            // 'estado' => 'required|boolean', // <-- SE QUITA DE AQUÍ
        ]);

        $cama->update($data);
        return response()->json($cama->load('sala'), 200);
    }

    public function destroy($id)
    {
        $cama = Cama::findOrFail($id);
        if ($cama->disponibilidad === 0) {
            return response()->json([
                'message' => 'No se puede eliminar. La cama está actualmente ocupada.'
            ], 409);
        }
        $cama->update(['estado' => false]);
        return response()->json(['message' => 'Cama eliminada correctamente'], 200);
    }

    public function getDisponibles(Request $request)
    {
        try {
            $request->validate(['sala_id' => 'nullable|integer|exists:salas,id']);
            $query = Cama::query();
            $query->where('estado', 1)->where('disponibilidad', 1);
            if ($request->filled('sala_id')) {
                $query->where('sala_id', $request->sala_id);
            }
            return $query->with('sala')->get();
        } catch (\Throwable $e) {
            Log::error('Error en CamaController@getDisponibles: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Error al obtener camas disponibles: ' . $e->getMessage(),
                 'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}
