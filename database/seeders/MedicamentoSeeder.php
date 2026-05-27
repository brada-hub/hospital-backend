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
                'stock' => 120,
                'stock_critico' => 15,
                'estante' => 'Estante A / Nivel 1',
            ],
            [
                'categoria_id' => $analgesico->id,
                'nombre' => 'Ketorolaco',
                'descripcion' => 'AINE para dolor agudo de moderado a severo.',
                'stock' => 45,
                'stock_critico' => 10,
                'estante' => 'Estante A / Nivel 2',
            ],
            [
                'categoria_id' => $antibiotico->id,
                'nombre' => 'Amoxicilina con Ácido Clavulánico',
                'descripcion' => 'Antibiótico de amplio espectro.',
                'stock' => 8,
                'stock_critico' => 10,
                'estante' => 'Estante B / Nivel 1',
            ],
            [
                'categoria_id' => $fluidoterapia->id,
                'nombre' => 'Solución Salina 0.9%',
                'descripcion' => 'Suero fisiológico para hidratación.',
                'stock' => 200,
                'stock_critico' => 30,
                'estante' => 'Estante C / Base',
            ],
            [
                'categoria_id' => $gastro->id,
                'nombre' => 'Omeprazol',
                'descripcion' => 'Protector gástrico para prevenir úlceras.',
                'stock' => 60,
                'stock_critico' => 12,
                'estante' => 'Estante B / Nivel 2',
            ],
        ];

        foreach ($medicamentos as $medicamento) {
            Medicamento::firstOrCreate(['nombre' => $medicamento['nombre']], $medicamento);
        }
    }
}
