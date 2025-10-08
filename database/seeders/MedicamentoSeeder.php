<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Medicamento;
use App\Models\MedicamentoCategoria; // <-- Importar el nuevo modelo

class MedicamentoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear las categorías primero
        $analgesico = MedicamentoCategoria::firstOrCreate(['nombre' => 'Analgésicos']);
        $antibiotico = MedicamentoCategoria::firstOrCreate(['nombre' => 'Antibióticos']);
        $fluidoterapia = MedicamentoCategoria::firstOrCreate(['nombre' => 'Fluidoterapia y Soporte']);
        $gastro = MedicamentoCategoria::firstOrCreate(['nombre' => 'Gastrointestinales']);

        // 2. Definir los medicamentos con su ID de categoría
        $medicamentos = [
            [
                'categoria_id' => $analgesico->id,
                'nombre' => 'Paracetamol',
                'descripcion' => 'Analgésico y antipirético para fiebre y dolor leve a moderado.',

            ],
            [
                'categoria_id' => $analgesico->id,
                'nombre' => 'Ketorolaco',
                'descripcion' => 'AINE para dolor agudo de moderado a severo.',

            ],
            [
                'categoria_id' => $antibiotico->id,
                'nombre' => 'Amoxicilina con Ácido Clavulánico',
                'descripcion' => 'Antibiótico de amplio espectro.',

            ],
            [
                'categoria_id' => $fluidoterapia->id,
                'nombre' => 'Solución Salina 0.9%',
                'descripcion' => 'Suero fisiológico para hidratación.',

            ],
            [
                'categoria_id' => $gastro->id,
                'nombre' => 'Omeprazol',
                'descripcion' => 'Protector gástrico para prevenir úlceras.',

            ],
        ];

        foreach ($medicamentos as $medicamento) {
            Medicamento::firstOrCreate(['nombre' => $medicamento['nombre']], $medicamento);
        }
    }
}
