<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class HospitalController extends Controller
{
    /**
     * Display the authenticated user's hospital.
     */
    public function index()
    {
        return Auth::user()->hospital;
    }

    /**
     * Store a newly created hospital in storage and assign it to the user.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // VALIDACIÓN CORREGIDA: AHORA ESPERA MAYÚSCULAS
        $data = $request->validate([
            'nombre'       => 'required|string|max:100|unique:hospitals,nombre|regex:/^[a-zA-Z\s]+$/',
            'departamento' => ['required', 'string', Rule::in(['LA PAZ', 'COCHABAMBA', 'SANTA CRUZ'])],
            'direccion'    => 'required|string|max:255|regex:/^[a-zA-Z0-9\s#.,-]+$/',
            'nivel'        => ['required', 'string', Rule::in(['NIVEL 1', 'NIVEL 2', 'NIVEL 3'])],
            'tipo'         => ['required', 'string', Rule::in(['PÚBLICO', 'PRIVADO'])],
            'telefono'     => 'required|numeric|digits:8|min:60000000|max:79999999|unique:hospitals,telefono',
        ]);

        $hospital = Hospital::create($data);
        $user->hospital_id = $hospital->id;
        $user->save();

        Log::info('Hospital registrado para el usuario', ['user_id' => $user->id, 'hospital_id' => $hospital->id]);
        return response()->json($hospital, 201);
    }

    /**
     * Display the specified hospital.
     */
    public function show($id)
    {
        return Hospital::findOrFail($id);
    }

    /**
     * Update the specified hospital in storage.
     */
    public function update(Request $request, $id)
    {
        $hospital = Hospital::findOrFail($id);

        // VALIDACIÓN CORREGIDA: AHORA ESPERA MAYÚSCULAS
        $data = $request->validate([
            'nombre'       => ['required', 'string', 'max:100', Rule::unique('hospitals')->ignore($hospital->id), 'regex:/^[a-zA-Z\s]+$/'],
            'departamento' => ['required', 'string', Rule::in(['LA PAZ', 'COCHABAMBA', 'SANTA CRUZ'])],
            'direccion'    => 'required|string|max:255|regex:/^[a-zA-Z0-9\s#.,-]+$/',
            'nivel'        => ['required', 'string', Rule::in(['NIVEL 1', 'NIVEL 2', 'NIVEL 3'])],
            'tipo'         => ['required', 'string', Rule::in(['PÚBLICO', 'PRIVADO'])],
            'telefono'     => [
                'required',
                'numeric',
                'digits:8',
                'min:60000000',
                'max:79999999',
                Rule::unique('hospitals')->ignore($hospital->id),
            ],
        ]);

        $hospital->update($data);
        Log::info('Hospital actualizado', ['id' => $hospital->id]);
        return response()->json($hospital, 200);
    }

    /**
     * Remove the specified hospital from storage.
     */
    public function destroy($id)
    {
        $hospital = Hospital::findOrFail($id);
        $hospital->delete();
        Log::warning('Hospital eliminado', ['id' => $id]);
        return response()->noContent();
    }

    /**
     * Get details for a specific hospital by ID.
     */
    public function getHospitalDetails($id)
    {
        $hospital = Hospital::find($id);
        if (!$hospital) {
            return response()->json(['message' => 'Hospital no encontrado'], 404);
        }
        return response()->json($hospital);
    }
}
