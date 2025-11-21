<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Signo; // Importa el modelo
use Illuminate\Support\Facades\DB;

class SignoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {



        $signos = [
            // No rutinarios — antropométricos
            ['nombre' => 'Peso', 'unidad' => 'kg', 'es_rutinario' => false],
            ['nombre' => 'Altura', 'unidad' => 'cm', 'es_rutinario' => false],

            // Rutinarios
            ['nombre' => 'Presión Arterial', 'unidad' => 'mmHg', 'es_rutinario' => true],
            ['nombre' => 'Frecuencia Cardíaca', 'unidad' => 'lpm', 'es_rutinario' => true],
            ['nombre' => 'Frecuencia Respiratoria', 'unidad' => 'rpm', 'es_rutinario' => true],
            ['nombre' => 'Temperatura', 'unidad' => '°C', 'es_rutinario' => true],
            ['nombre' => 'Saturación de Oxígeno', 'unidad' => '%', 'es_rutinario' => true],
            ['nombre' => 'Glucosa Capilar', 'unidad' => 'mg/dL', 'es_rutinario' => true],
        ];
        // Inserta los datos en la tabla
        foreach ($signos as $signo) {
            Signo::create($signo);
        }
    }
}
