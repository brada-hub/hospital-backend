<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // <-- Asegúrate de importar DB

class RangosNormalesSeeder extends Seeder
{
    public function run(): void
    {
        // Pega aquí los datos que quitaste de la migración
        DB::table('rangos_normales')->insert([
            // Peso (NO tiene rango normal definido)
            // Altura (NO tiene rango normal definido)

            ['signo_id' => 3, 'valor_minimo' => 90,   'valor_maximo' => 120,  'created_at' => now(), 'updated_at' => now()], // Presión Arterial
            ['signo_id' => 4, 'valor_minimo' => 60,   'valor_maximo' => 100,  'created_at' => now(), 'updated_at' => now()], // Frecuencia Cardíaca
            ['signo_id' => 5, 'valor_minimo' => 12,   'valor_maximo' => 20,   'created_at' => now(), 'updated_at' => now()], // Frecuencia Respiratoria
            ['signo_id' => 6, 'valor_minimo' => 36.0, 'valor_maximo' => 37.5, 'created_at' => now(), 'updated_at' => now()], // Temperatura
            ['signo_id' => 7, 'valor_minimo' => 95,   'valor_maximo' => 100,  'created_at' => now(), 'updated_at' => now()], // Saturación
            ['signo_id' => 8, 'valor_minimo' => 70,   'valor_maximo' => 140,  'created_at' => now(), 'updated_at' => now()], // Glucosa
        ]);
    }
}
