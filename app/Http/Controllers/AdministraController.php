<?php

namespace App\Http\Controllers;

use App\Models\Administra;
use App\Models\Receta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdministraController extends Controller
{
    private const TOLERANCIA_MIN = 30;

    public function store(Request $request)
    {
        $data = $request->validate([
            'administracion_id' => 'nullable|exists:administras,id',
            'receta_id'         => 'nullable|exists:recetas,id',
            'hora_programada'   => 'nullable|date',
            'observaciones'     => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        $now = Carbon::now(config('app.timezone'));

        if (!empty($data['administracion_id'])) {
            return $this->administrarDosisExistente($data['administracion_id'], $user, $now, $data['observaciones'] ?? null);
        }

        if (empty($data['receta_id'])) {
            return response()->json([
                'message' => 'receta_id es requerido para la primera administración.'
            ], 422);
        }

        $receta = Receta::with('tratamiento')->findOrFail($data['receta_id']);
        $esLaPrimera = !Administra::where('receta_id', $receta->id)->exists();

        if ($esLaPrimera) {
            return $this->registrarPrimeraAdministracion($receta, $data, $user, $now);
        }

        return $this->buscarYActualizarDosisPendiente($receta, $data, $user, $now);
    }

    private function administrarDosisExistente($administracionId, $user, Carbon $now, $observaciones)
    {
        $admin = Administra::findOrFail($administracionId);

        if ($admin->estado != 0) {
            return response()->json([
                'message' => 'Esta dosis ya fue administrada previamente.',
                'estado_actual' => $admin->estado
            ], 409);
        }

        $horaProgramada = Carbon::parse($admin->hora_programada, config('app.timezone'));
        $minutosRetraso = $now->diffInMinutes($horaProgramada, false);
        $esRetrasada = $minutosRetraso < -self::TOLERANCIA_MIN;
        $nuevoEstado = $esRetrasada ? 2 : 1;

        $admin->update([
            'user_id'       => $user->id,
            'fecha'         => $now,
            'estado'        => $nuevoEstado,
            'observaciones' => $observaciones ?? $admin->observaciones,
        ]);

        Log::info('Dosis administrada', [
            'administracion_id' => $admin->id,
            'receta_id' => $admin->receta_id,
            'estado' => $nuevoEstado,
            'retraso_minutos' => abs($minutosRetraso),
            'usuario' => $user->nombre ?? 'N/A'
        ]);

        return response()->json($admin->load('user:id,nombre,apellidos'), 200);
    }

    private function registrarPrimeraAdministracion(Receta $receta, array $data, $user, Carbon $now)
    {
        $primeraDosis = Administra::create([
            'receta_id'       => $receta->id,
            'user_id'         => $user->id,
            'hora_programada' => $now,
            'fecha'           => $now,
            'estado'          => 1,
            'observaciones'   => $data['observaciones'] ?? null,
        ]);

        Log::info('Primera dosis administrada (ANCLA)', [
            'administracion_id' => $primeraDosis->id,
            'receta_id' => $receta->id,
            'hora_ancla' => $now->toDateTimeString(),
            'usuario' => $user->nombre ?? 'N/A'
        ]);

        $this->generarCronogramaFuturo($receta, $now);

        return response()->json($primeraDosis->load('user:id,nombre,apellidos'), 201);
    }

    private function buscarYActualizarDosisPendiente(Receta $receta, array $data, $user, Carbon $now)
    {
        $horaProgramada = Carbon::parse($data['hora_programada'], config('app.timezone'));

        $dosisPendiente = Administra::where('receta_id', $receta->id)
            ->where('hora_programada', $horaProgramada)
            ->where('estado', 0)
            ->first();

        if (!$dosisPendiente) {
            $toleranciaBusqueda = 30;
            $inicio = $horaProgramada->copy()->subMinutes($toleranciaBusqueda);
            $fin = $horaProgramada->copy()->addMinutes($toleranciaBusqueda);

            $dosisPendiente = Administra::where('receta_id', $receta->id)
                ->where('estado', 0)
                ->whereBetween('hora_programada', [$inicio, $fin])
                ->orderBy('hora_programada', 'asc')
                ->first();
        }

        if (!$dosisPendiente) {
            return response()->json([
                'message' => 'No se encontró una dosis pendiente para esta hora.'
            ], 404);
        }

        $minutosRetraso = $now->diffInMinutes($dosisPendiente->hora_programada, false);
        $esRetrasada = $minutosRetraso < -self::TOLERANCIA_MIN;
        $nuevoEstado = $esRetrasada ? 2 : 1;

        $dosisPendiente->update([
            'user_id'       => $user->id,
            'fecha'         => $now,
            'estado'        => $nuevoEstado,
            'observaciones' => $data['observaciones'] ?? $dosisPendiente->observaciones,
        ]);

        Log::info('Dosis pendiente administrada', [
            'administracion_id' => $dosisPendiente->id,
            'receta_id' => $receta->id,
            'estado' => $nuevoEstado
        ]);

        return response()->json($dosisPendiente->load('user:id,nombre,apellidos'), 200);
    }

    private function generarCronogramaFuturo(Receta $receta, Carbon $ancla)
    {
        $schedule = [];
        $now = Carbon::now(config('app.timezone'));
        $fechaFin = $ancla->copy()->addDays($receta->duracion_dias);
        $totalDosis = (int) ceil(($receta->duracion_dias * 24) / max(1, $receta->frecuencia_horas));

        for ($i = 1; $i < $totalDosis; $i++) {
            $nextHoraProgramada = $ancla->copy()->addHours($i * $receta->frecuencia_horas);

            if ($nextHoraProgramada->isAfter($fechaFin)) {
                break;
            }

            $exists = Administra::where('receta_id', $receta->id)
                ->where('hora_programada', $nextHoraProgramada)
                ->exists();

            if ($exists) {
                continue;
            }

            $schedule[] = [
                'receta_id'       => $receta->id,
                'user_id'         => null,
                'hora_programada' => $nextHoraProgramada,
                'fecha'           => null,
                'estado'          => 0,
                'observaciones'   => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
        }

        if (!empty($schedule)) {
            Administra::insert($schedule);
            Log::info('Cronograma generado', [
                'receta_id' => $receta->id,
                'total_dosis_futuras' => count($schedule),
                'desde' => $schedule[0]['hora_programada'],
                'hasta' => end($schedule)['hora_programada']
            ]);
        }
    }

    public function index(Request $request)
    {
        $administraciones = Administra::with(['receta.medicamento', 'user'])
            ->orderBy('hora_programada', 'desc')
            ->paginate(50);

        return response()->json($administraciones);
    }

    public function show($id)
    {
        $administracion = Administra::with(['receta.medicamento', 'user'])
            ->findOrFail($id);

        return response()->json($administracion);
    }

    public function update(Request $request, $id)
    {
        $administracion = Administra::findOrFail($id);

        $data = $request->validate([
            'observaciones' => 'nullable|string|max:500',
        ]);

        $administracion->update($data);

        return response()->json($administracion);
    }

    public function destroy($id)
    {
        $administracion = Administra::findOrFail($id);

        if ($administracion->estado != 0) {
            return response()->json([
                'message' => 'No se puede eliminar una dosis ya administrada.'
            ], 422);
        }

        $administracion->delete();

        return response()->json(['message' => 'Administración eliminada.'], 200);
    }
}
