<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class HospitalController extends Controller
{
    public function index()
    {
      return Auth::user()->hospital;
    }

    public function store(Request $request)
    {
        $user = Auth::user(); // Obtener el usuario autenticado

        // Validación de datos de hospital
        $data = $request->validate([
            'nombre'       => 'required|string|max:100|unique:hospitals,nombre',
            'departamento' => 'required|string|max:100',
            'direccion'    => 'required|string|max:255',
            'nivel'        => 'required|string|max:50',
            'tipo'         => 'required|string|max:50',
            'telefono'     => 'required|digits_between:7,15',
        ]);

        // Crear el nuevo hospital
        $hospital = Hospital::create($data);

        // Asociar este hospital al usuario autenticado
        $user->hospital_id = $hospital->id;
        $user->save(); // Guardar el hospital asignado al usuario

        // Loguear la acción
        Log::info('Hospital registrado para el usuario', ['user_id' => $user->id, 'hospital_id' => $hospital->id]);

        return response()->json($hospital, 201); // Retorna el hospital recién creado
    }

    public function show($id)
    {
        // Retorna el hospital con el ID proporcionado
        return Hospital::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        // Verificar que el hospital existe
        $hospital = Hospital::findOrFail($id);

        // Validación de datos
        $data = $request->validate([
            'nombre'       => 'required|string|max:100|unique:hospitals,nombre,' . $hospital->id,
            'departamento' => 'required|string|max:100',
            'direccion'    => 'required|string|max:255',
            'nivel'        => 'required|string|max:50',
            'tipo'         => 'required|string|max:50',
            'telefono'     => 'required|digits_between:7,15',
        ]);

        // Actualizar los datos del hospital
        $hospital->update($data);

        // Loguear la actualización
        Log::info('Hospital actualizado manualmente', ['id' => $hospital->id]);

        return response()->json($hospital, 200); // Retorna el hospital actualizado
    }

    public function destroy($id)
    {
        // Eliminar el hospital con el ID proporcionado
        $hospital = Hospital::findOrFail($id);
        $hospital->delete();

        // Loguear la eliminación
        Log::warning('Hospital eliminado', ['id' => $id]);

        return response()->noContent(); // Retorna 204 si se eliminó correctamente
    }
     public function getHospitalDetails($id)
    {
        // Obtener el hospital con el id proporcionado
        $hospital = Hospital::find($id);

        // Si el hospital no existe
        if (!$hospital) {
            return response()->json(['message' => 'Hospital no encontrado'], 404);
        }

        // Retornar los detalles del hospital
        return response()->json($hospital);
    }
}
