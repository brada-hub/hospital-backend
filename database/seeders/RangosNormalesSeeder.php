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
            ['signo_id' => 1, 'valor_minimo' => 60, 'valor_maximo' => 100, 'created_at' => now(), 'updated_at' => now()],
            ['signo_id' => 2, 'valor_minimo' => 90, 'valor_maximo' => 120, 'created_at' => now(), 'updated_at' => now()],
            ['signo_id' => 3, 'valor_minimo' => 60, 'valor_maximo' => 80, 'created_at' => now(), 'updated_at' => now()],
            ['signo_id' => 4, 'valor_minimo' => 12, 'valor_maximo' => 20, 'created_at' => now(), 'updated_at' => now()],
            ['signo_id' => 5, 'valor_minimo' => 36.0, 'valor_maximo' => 37.5, 'created_at' => now(), 'updated_at' => now()],
            ['signo_id' => 6, 'valor_minimo' => 95, 'valor_maximo' => 100, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
