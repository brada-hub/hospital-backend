<?php

namespace App\Http\Controllers;

use App\Models\Control;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ControlController extends Controller
{
    public function index()
    {
        return Control::with('internacion')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'internacion_id' => 'required|exists:internacions,id',
            'fecha_control'  => 'required|date',
            'observaciones'  => 'nullable|string|max:255',
        ]);

        $control = Control::create($data);
        Log::info('Control registrado', ['id' => $control->id]);

        return response()->json($control, 201);
    }

    public function show($id)
    {
        return Control::with('internacion')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $control = Control::findOrFail($id);

        $data = $request->validate([
            'internacion_id' => 'required|exists:internacions,id',
            'fecha_control'  => 'required|date',
            'observaciones'  => 'nullable|string|max:255',
        ]);

        $control->update($data);
        Log::info('Control actualizado', ['id' => $control->id]);

        return response()->json($control, 200);
    }

    public function destroy($id)
    {
        $control = Control::findOrFail($id);
        $control->delete();

        Log::warning('Control eliminado', ['id' => $id]);
        return response()->noContent();
    }
}
