<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Signo;
use Illuminate\Support\Facades\DB;

class SignoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $signos = [
            // ✅ No rutinarios — antropométricos
            ['nombre' => 'Peso', 'unidad' => 'kg', 'es_rutinario' => false, 'requiere_valores_duales' => false],
            ['nombre' => 'Altura', 'unidad' => 'cm', 'es_rutinario' => false, 'requiere_valores_duales' => false],

            // ✅ Rutinarios
            // SOLO Presión Arterial tiene valores duales (Sistólica/Diastólica)
            ['nombre' => 'Presión Arterial', 'unidad' => 'mmHg', 'es_rutinario' => true, 'requiere_valores_duales' => true],

            // Los demás son valores simples
            ['nombre' => 'Frecuencia Cardíaca', 'unidad' => 'lpm', 'es_rutinario' => true, 'requiere_valores_duales' => true],
            ['nombre' => 'Frecuencia Respiratoria', 'unidad' => 'rpm', 'es_rutinario' => true, 'requiere_valores_duales' => false],
            ['nombre' => 'Temperatura', 'unidad' => '°C', 'es_rutinario' => true, 'requiere_valores_duales' => false],
            ['nombre' => 'Saturación de Oxígeno', 'unidad' => '%', 'es_rutinario' => true, 'requiere_valores_duales' => false],
            ['nombre' => 'Glucosa Capilar', 'unidad' => 'mg/dL', 'es_rutinario' => true, 'requiere_valores_duales' => false],
        ];

        // Inserta los datos en la tabla
        foreach ($signos as $signo) {
            Signo::updateOrCreate(
                ['nombre' => $signo['nombre']], // Buscar por nombre
                $signo // Actualizar o crear con estos datos
            );
        }
    }
}
