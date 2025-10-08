<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\TipoDieta; // Importa el modelo

class TipoDietaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Desactiva la revisión de claves foráneas para evitar problemas al truncar
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        TipoDieta::truncate(); // Limpia la tabla para evitar duplicados
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $dietas = [
            // Dietas Basales
            ['nombre' => 'Dieta Basal', 'descripcion' => 'Dieta estándar para pacientes sin modificaciones específicas.'],
            ['nombre' => 'Dieta Basal Pediátrica', 'descripcion' => 'Dieta basal adaptada para niños.'],

            // Dietas Progresivas
            ['nombre' => 'Dieta Líquida Clara', 'descripcion' => 'Líquidos claros para hidratación con mínima estimulación digestiva (caldos, infusiones).'],
            ['nombre' => 'Dieta Líquida Completa', 'descripcion' => 'Incluye alimentos líquidos como leche, yogur y sopas crema.'],
            ['nombre' => 'Dieta Semiblanda', 'descripcion' => 'Alimentos en puré o de textura muy suave (papillas, compotas).'],
            ['nombre' => 'Dieta Blanda', 'descripcion' => 'Alimentos de fácil digestión, cocidos y sin condimentos fuertes.'],

            // Dietas Terapéuticas
            ['nombre' => 'Dieta Hipocalórica', 'descripcion' => 'Restringida en calorías para pacientes con sobrepeso.'],
            ['nombre' => 'Dieta Hipercalórica', 'descripcion' => 'Enriquecida en calorías para favorecer la recuperación.'],
            ['nombre' => 'Dieta Hiperproteica', 'descripcion' => 'Enriquecida en proteínas para recuperación y cicatrización.'],
            ['nombre' => 'Dieta Hipoglúcida (Diabetes)', 'descripcion' => 'Controla los carbohidratos para pacientes con diabetes.'],
            ['nombre' => 'Dieta Hipoproteica (Renal)', 'descripcion' => 'Restringe las proteínas, indicada para enfermedades renales.'],
            ['nombre' => 'Dieta Hipograsa (Hepática)', 'descripcion' => 'Baja en grasas para problemas hepáticos, biliares o pancreáticos.'],
            ['nombre' => 'Dieta Hiposódica (Hipertensión)', 'descripcion' => 'Restringe la sal para pacientes con hipertensión o insuficiencia cardíaca/renal.'],
            ['nombre' => 'Dieta Astringente', 'descripcion' => 'Baja en fibra y alimentos irritantes para controlar la diarrea.'],
            ['nombre' => 'Dieta Rica en Fibra', 'descripcion' => 'Para tratar el estreñimiento.'],

            // Dietas con Consistencia Modificada
            ['nombre' => 'Dieta de Fácil Masticación', 'descripcion' => 'Alimentos de textura blanda y jugosa para minimizar el esfuerzo al masticar.'],
            ['nombre' => 'Dieta Triturada (Puré)', 'descripcion' => 'Todos los alimentos se procesan hasta obtener una consistencia de puré.'],
        ];

        // Inserta los datos en la base de datos
        foreach ($dietas as $dieta) {
            TipoDieta::create($dieta);
        }
    }
}
