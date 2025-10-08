<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paciente;
use App\Models\User;
use App\Models\Cama;
use App\Models\Internacion;
use App\Models\Ocupacion;
use App\Models\Control;
use App\Models\Signo;
use App\Models\Cuidado;
use App\Models\Tratamiento;
use App\Models\Medicamento;
use App\Models\Receta;
use App\Models\Valor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InternacionCompletaSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiamos tablas relacionadas para evitar conflictos
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Ocupacion::truncate();
        Receta::truncate();
        Tratamiento::truncate();
        Cuidado::truncate();
        Valor::truncate();
        Control::truncate();
        Internacion::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // --- 1. BUSCAMOS LOS DATOS NECESARIOS ---
        $paciente = Paciente::where('ci', '1234567')->first();
        $medico = User::where('email', 'medico@hospital.com')->first();
        $enfermera = User::where('email', 'enfermera@hospital.com')->first();
        $cama = Cama::where('disponibilidad', true)->first();
        $signos = Signo::all()->keyBy('nombre');
        $ketorolaco = Medicamento::where('nombre', 'like', '%Ketorolaco%')->first();

        if (!$paciente || !$medico || !$cama || $signos->isEmpty() || !$ketorolaco || !$enfermera) {
            $this->command->error('No se encontraron los datos base (paciente, medico, cama, signos, etc.). Asegúrate de ejecutar el DatabaseSeeder principal primero.');
            return;
        }

        // --- 2. CREAMOS LA INTERNACIÓN ---
        $internacion = Internacion::create([
            'fecha_ingreso' => Carbon::now()->subHours(2),
            'motivo' => 'Dolor abdominal agudo',
            'diagnostico' => 'Apendicitis aguda',
            'paciente_id' => $paciente->id,
            'user_id' => $medico->id,
        ]);

        // --- 3. OCUPAMOS LA CAMA ---
        Ocupacion::create([
            'fecha_ocupacion' => $internacion->fecha_ingreso,
            'cama_id' => $cama->id,
            'internacion_id' => $internacion->id,
        ]);
        $cama->update(['disponibilidad' => false]);

        // --- 4. REGISTRAMOS SIGNOS VITALES DE INGRESO ---
        $controlIngreso = Control::create([
            'tipo' => 'Control de Ingreso',
            'internacion_id' => $internacion->id,
            'user_id' => $enfermera->id,
            'fecha_control' => $internacion->fecha_ingreso,
            'observaciones' => 'Registro inicial de signos vitales.',
        ]);

        $valoresIngreso = [
            'Altura' => 175,
            'Peso' => 78,
            'Frecuencia Cardíaca' => 95,
            'Frecuencia Respiratoria' => 22,
            'Glucosa Capilar' => 110,
            'Presión Arterial' => 130,
            'Saturación de Oxígeno' => 97,
            'Temperatura' => 38.2
        ];

        foreach ($valoresIngreso as $nombre => $medida) {
            if (isset($signos[$nombre])) {
                $controlIngreso->valores()->create([
                    'signo_id' => $signos[$nombre]->id,
                    'medida' => $medida,
                ]);
            }
        }

        // --- 5. CREAMOS UN PLAN DE CUIDADOS INICIAL ---
        Cuidado::create([
            'internacion_id' => $internacion->id,
            'tipo' => 'Cuidado General',
            'descripcion' => 'Control de signos vitales y valoración del dolor.',
            'frecuencia' => 'Cada 4 horas',
            'estado' => 0,
            'fecha_inicio' => Carbon::now(),
            'fecha_fin' => Carbon::now()->addDays(2),
        ]);
        Cuidado::create([
            'internacion_id' => $internacion->id,
            'tipo' => 'Cuidado Específico',
            'descripcion' => 'Mantener al paciente en ayunas para posible cirugía.',
            'frecuencia' => 'Continuo',
            'estado' => 0,
            'fecha_inicio' => Carbon::now(),
            'fecha_fin' => Carbon::now()->addDays(1),
        ]);

        // --- 6. CREAMOS UN TRATAMIENTO CON MEDICAMENTOS ---
        $tratamiento = Tratamiento::create([
            'internacion_id' => $internacion->id,
            'user_id' => $medico->id,
            'tipo' => 'Tratamiento analgésico',
            'descripcion' => 'Manejo del dolor preoperatorio.',
            'fecha_inicio' => Carbon::now()->subHour(),
            'fecha_fin' => Carbon::now()->addDays(2),
            'estado' => 0,
        ]);

        $tratamiento->recetas()->create([
            'medicamento_id' => $ketorolaco->id,
            'dosis' => '30mg',
            'frecuencia_horas' => 8,
            'duracion_dias' => 2,
            'via_administracion' => 'Intravenosa',
            'indicaciones' => 'Administrar diluido lentamente.',

        ]);

        $this->command->info('¡Seeder de internación completa ejecutado con éxito!');
    }
}
