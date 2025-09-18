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
                'presentacion' => 'Comprimido 500mg',
                'via_administracion' => 'Oral'
            ],
            [
                'categoria_id' => $analgesico->id,
                'nombre' => 'Ketorolaco',
                'descripcion' => 'AINE para dolor agudo de moderado a severo.',
                'presentacion' => 'Solución Inyectable 30mg/mL',
                'via_administracion' => 'Intravenosa'
            ],
            [
                'categoria_id' => $antibiotico->id,
                'nombre' => 'Amoxicilina con Ácido Clavulánico',
                'descripcion' => 'Antibiótico de amplio espectro.',
                'presentacion' => 'Comprimido 875mg/125mg',
                'via_administracion' => 'Oral'
            ],
            [
                'categoria_id' => $fluidoterapia->id,
                'nombre' => 'Solución Salina 0.9%',
                'descripcion' => 'Suero fisiológico para hidratación.',
                'presentacion' => 'Bolsa 1000mL',
                'via_administracion' => 'Intravenosa'
            ],
            [
                'categoria_id' => $gastro->id,
                'nombre' => 'Omeprazol',
                'descripcion' => 'Protector gástrico para prevenir úlceras.',
                'presentacion' => 'Solución Inyectable 40mg',
                'via_administracion' => 'Intravenosa'
            ],
        ];

        foreach ($medicamentos as $medicamento) {
            Medicamento::firstOrCreate(['nombre' => $medicamento['nombre']], $medicamento);
        }
    }
}
