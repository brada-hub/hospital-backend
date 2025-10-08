<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Receta;
use App\Models\Administra;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ActualizarDosisOmitidas extends Command
{
    protected $signature = 'dosis:marcar-omitidas';
    protected $description = 'Crea registros de "Omitida" para dosis que pasaron su tiempo de tolerancia.';

    public function handle()
    {
        $this->info('Iniciando verificación de dosis omitidas...');
        $ahora = Carbon::now();
        $recetasActivas = Receta::whereHas('tratamiento', fn($q) => $q->where('estado', 0))->get();
        $dosisOmitidasCreadas = 0;

        foreach ($recetasActivas as $receta) {
            $horaBase = Carbon::parse($receta->created_at);
            $fechaFin = $horaBase->copy()->addDays($receta->duracion_dias);
            $frecuenciaHoras = $receta->frecuencia_horas;
            if ($frecuenciaHoras <= 0) continue;

            $horaIterada = $horaBase->copy();
            // Recorremos todas las horas teóricas que ya deberían haber pasado
            while ($horaIterada->lt($fechaFin) && $horaIterada->lt($ahora)) {

                // La dosis se considera "perdida" si ya pasó su ventana de tolerancia (ej. 15 min)
                $horaLimite = $horaIterada->copy()->addMinutes(15);

                if ($ahora->gt($horaLimite)) {
                    // Verificamos si YA existe una administración (Cumplida u Omitida) en la ventana de esta dosis
                    $existeRegistro = $receta->administras()
                        ->where('created_at', '>=', $horaIterada)
                        ->where('created_at', '<', $horaIterada->copy()->addHours($frecuenciaHoras))
                        ->exists();

                    if (!$existeRegistro) {
                        // NO EXISTE REGISTRO: Creamos uno nuevo como OMITIDA (estado = 2)
                        Administra::create([
                            'receta_id' => $receta->id,
                            'user_id' => 1, // ID de un usuario "Sistema" o el admin por defecto
                            'fecha' => $horaIterada->toDateTimeString(),
                            'estado' => 2, // 2 = Omitida (o el número que uses en tu sistema)
                            'observaciones' => 'Dosis marcada como omitida automáticamente por el sistema.'
                        ]);
                        $dosisOmitidasCreadas++;
                        Log::info("Dosis omitida creada para receta #{$receta->id} a las {$horaIterada}");
                    }
                }
                $horaIterada->addHours($frecuenciaHoras);
            }
        }
        $this->info("Proceso finalizado. Se crearon {$dosisOmitidasCreadas} registros de dosis omitidas.");
        return 0;
    }
}
