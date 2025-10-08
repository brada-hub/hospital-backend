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
    // Tolerancia para considerar "retraso" al MOMENTO DE ADMINISTRAR
    private const TOLERANCIA_MIN = 30;

    /**
     * RF-02, RF-03, RF-04: Registrar administración de medicamento
     */
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

        // 🔥 QUITADA LA VALIDACIÓN DE ROL - Cualquier usuario autenticado puede administrar
        // if (isset($user->rol) && $user->rol !== 'enfermera') {
        //     return response()->json(['message' => 'No autorizado.'], 403);
        // }

        $now = Carbon::now(config('app.timezone'));

        // CASO 1: Actualizar dosis pendiente existente (subsecuente)
        if (!empty($data['administracion_id'])) {
            return $this->administrarDosisExistente($data['administracion_id'], $user, $now, $data['observaciones'] ?? null);
        }

        // CASO 2: Primera administración (ancla) - requiere receta_id
        if (empty($data['receta_id']) || empty($data['hora_programada'])) {
            return response()->json([
                'message' => 'receta_id y hora_programada son requeridos para la primera administración.'
            ], 422);
        }

        $receta = Receta::with('tratamiento')->findOrFail($data['receta_id']);

        // Verificar si es realmente la primera
        $esLaPrimera = !Administra::where('receta_id', $receta->id)->exists();

        if ($esLaPrimera) {
            return $this->registrarPrimeraAdministracion($receta, $data, $user, $now);
        }

        // CASO 3: Fallback - buscar dosis pendiente cercana
        return $this->buscarYActualizarDosisPendiente($receta, $data, $user, $now);
    }

    /**
     * RF-03, RF-04: Actualizar dosis existente
     */
    private function administrarDosisExistente($administracionId, $user, Carbon $now, $observaciones)
    {
        $admin = Administra::findOrFail($administracionId);

        // Validar que esté pendiente
        if ($admin->estado != 0) {
            return response()->json([
                'message' => 'Esta dosis ya fue administrada previamente.',
                'estado_actual' => $admin->estado
            ], 409);
        }

        // RF-04: Determinar si hay retraso SOLO al momento de administrar
        $horaProgramada = Carbon::parse($admin->hora_programada, config('app.timezone'));
        $minutosRetraso = $now->diffInMinutes($horaProgramada, false);
        $esRetrasada = $minutosRetraso < -self::TOLERANCIA_MIN;

        $nuevoEstado = $esRetrasada ? 2 : 1;

        // RF-03: Registrar usuario, hora real y observaciones
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
            'enfermera' => $user->nombre
        ]);

        return response()->json($admin->load('user:id,nombre,apellidos'), 200);
    }

    /**
     * RF-01: Registrar primera administración y generar cronograma
     */
    private function registrarPrimeraAdministracion(Receta $receta, array $data, $user, Carbon $now)
    {
        // 🔥 LA PRIMERA DOSIS SIEMPRE ES "CUMPLIDA" (estado=1)
        // No importa cuándo se administre, porque es el punto de partida (ANCLA)

        // Crear registro de la primera dosis (ANCLA)
        $primeraDosis = Administra::create([
            'receta_id'       => $receta->id,
            'user_id'         => $user->id,
            'hora_programada' => $now, // 🔥 USAR LA HORA REAL como hora programada
            'fecha'           => $now,
            'estado'          => 1, // 🔥 SIEMPRE CUMPLIDA (no calcular retraso)
            'observaciones'   => $data['observaciones'] ?? null,
        ]);

        Log::info('Primera dosis administrada (ANCLA)', [
            'administracion_id' => $primeraDosis->id,
            'receta_id' => $receta->id,
            'estado' => 1,
            'hora_real' => $now->toDateTimeString(),
            'enfermera' => $user->nombre ?? 'N/A'
        ]);

        // RF-01: Generar cronograma de dosis futuras desde AHORA
        $this->generarCronogramaFuturo($receta, $now);

        return response()->json($primeraDosis->load('user:id,nombre,apellidos'), 201);
    }

    /**
     * Buscar dosis pendiente cercana (fallback para clientes desactualizados)
     */
    private function buscarYActualizarDosisPendiente(Receta $receta, array $data, $user, Carbon $now)
    {
        $horaProgramada = Carbon::parse($data['hora_programada'], config('app.timezone'));

        // Buscar exacta
        $dosisPendiente = Administra::where('receta_id', $receta->id)
            ->where('hora_programada', $horaProgramada)
            ->where('estado', 0)
            ->first();

        // Si no existe, buscar con tolerancia de ±30 min
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
                'message' => 'No se encontró una dosis pendiente para esta hora. Verifique el cronograma.'
            ], 404);
        }

        // Actualizar dosis encontrada
        $minutosRetraso = $now->diffInMinutes($dosisPendiente->hora_programada, false);
        $esRetrasada = $minutosRetraso < -self::TOLERANCIA_MIN;
        $nuevoEstado = $esRetrasada ? 2 : 1;

        $dosisPendiente->update([
            'user_id'       => $user->id,
            'fecha'         => $now,
            'estado'        => $nuevoEstado,
            'observaciones' => $data['observaciones'] ?? $dosisPendiente->observaciones,
        ]);

        Log::info('Dosis pendiente administrada (fallback)', [
            'administracion_id' => $dosisPendiente->id,
            'receta_id' => $receta->id,
            'estado' => $nuevoEstado
        ]);

        return response()->json($dosisPendiente->load('user:id,nombre,apellidos'), 200);
    }

    /**
     * RF-01: Generar cronograma completo de dosis futuras (TODAS PENDIENTES)
     */
    private function generarCronogramaFuturo(Receta $receta, Carbon $puntoDePartidaReal)
    {
        $schedule = [];
        $now = Carbon::now(config('app.timezone'));

        $fechaFinTratamiento = Carbon::parse($receta->tratamiento->fecha_inicio, config('app.timezone'))
            ->addDays($receta->duracion_dias);

        // Calcular total de dosis
        $totalDosis = (int) ceil(($receta->duracion_dias * 24) / max(1, $receta->frecuencia_horas));

        // Generar SOLO las dosis futuras (i=1 porque la primera ya fue creada)
        for ($i = 1; $i < $totalDosis; $i++) {
            $nextHoraProgramada = $puntoDePartidaReal->copy()->addHours($i * $receta->frecuencia_horas);

            // No crear dosis fuera del rango del tratamiento
            if ($nextHoraProgramada->isAfter($fechaFinTratamiento)) {
                break;
            }

            // Evitar duplicados (por si acaso)
            $exists = Administra::where('receta_id', $receta->id)
                ->where('hora_programada', $nextHoraProgramada)
                ->exists();

            if ($exists) {
                continue;
            }

            // RF-01: TODAS las dosis futuras se crean como PENDIENTES (estado=0)
            $schedule[] = [
                'receta_id'       => $receta->id,
                'user_id'         => null,
                'hora_programada' => $nextHoraProgramada,
                'fecha'           => null,
                'estado'          => 0, // PENDIENTE
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
                'rango' => [
                    'desde' => $schedule[0]['hora_programada'],
                    'hasta' => end($schedule)['hora_programada']
                ]
            ]);
        }
    }

    /**
     * Endpoint para regenerar cronograma manualmente (opcional)
     */
    public function generarCronograma(Request $request, $recetaId)
    {
        $receta = Receta::with('tratamiento')->findOrFail($recetaId);

        // Buscar la primera administración existente
        $primeraDosis = Administra::where('receta_id', $receta->id)
            ->whereNotNull('fecha')
            ->orderBy('hora_programada', 'asc')
            ->first();

        if (!$primeraDosis) {
            return response()->json([
                'message' => 'No existe una primera dosis administrada. Administre la primera dosis antes de generar el cronograma.'
            ], 422);
        }

        $this->generarCronogramaFuturo($receta, Carbon::parse($primeraDosis->hora_programada));

        return response()->json(['message' => 'Cronograma regenerado exitosamente.'], 200);
    }

    /**
     * Métodos del Resource Controller (apiResource)
     * Estos son necesarios porque en tus rutas tienes: 'administraciones' => AdministraController::class
     */

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

        // Solo permitir eliminar si está pendiente
        if ($administracion->estado != 0) {
            return response()->json([
                'message' => 'No se puede eliminar una dosis ya administrada.'
            ], 422);
        }

        $administracion->delete();

        return response()->json(['message' => 'Administración eliminada.'], 200);
    }
}
