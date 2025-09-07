<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HospitalController extends Controller
{
    public function index()
    {
        return Hospital::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'       => 'required|string|max:100|unique:hospitals,nombre',
            'departamento' => 'required|string|max:100',
            'direccion'    => 'required|string|max:255',
            'nivel'        => 'required|string|max:50',
            'tipo'         => 'required|string|max:50',
            'telefono'     => 'required|digits_between:7,15',
        ]);

        $hospital = Hospital::create($data);
        Log::info('Hospital registrado', ['id' => $hospital->id]);

        return response()->json($hospital, 201);
    }

    public function show($id)
    {
        return Hospital::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $hospital = Hospital::findOrFail($id);

        $data = $request->validate([
            'nombre'       => 'required|string|max:100|unique:hospitals,nombre,' . $hospital->id,
            'departamento' => 'required|string|max:100',
            'direccion'    => 'required|string|max:255',
            'nivel'        => 'required|string|max:50',
            'tipo'         => 'required|string|max:50',
            'telefono'     => 'required|digits_between:7,15',
        ]);

        $hospital->update($data);
        Log::info('Hospital actualizado manualmente', ['id' => $hospital->id]);

        return response()->json($hospital, 200);
    }

    public function destroy($id)
    {
        $hospital = Hospital::findOrFail($id);
        $hospital->delete();

        Log::warning('Hospital eliminado', ['id' => $id]);
        return response()->noContent();
    }
}
