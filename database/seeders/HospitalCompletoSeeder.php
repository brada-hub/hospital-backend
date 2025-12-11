<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paciente;
use App\Models\User;
use App\Models\Rol;
use App\Models\Hospital;
use App\Models\Especialidad;
use App\Models\Sala;
use App\Models\Cama;
use App\Models\Internacion;
use App\Models\Ocupacion;
use App\Models\Control;
use App\Models\Signo;
use App\Models\Valor;
use App\Models\Tratamiento;
use App\Models\Medicamento;
use App\Models\Receta;
use App\Models\Administra;
use App\Models\Alimentacion;
use App\Models\TipoDieta;
use App\Models\Consume;
use App\Models\Cuidado;
use App\Models\CuidadoAplicado;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class HospitalCompletoSeeder extends Seeder
{
    private $hospital;
    private $medicos = [];
    private $enfermeras = [];
    private $signos;
    private $medicamentos;
    private $tiposDieta;
    private $pacienteRol;

    public function run(): void
    {
        // Limpiamos tablas relacionadas
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        CuidadoAplicado::truncate();
        Consume::truncate();
        Alimentacion::truncate();
        Administra::truncate();
        Receta::truncate();
        Tratamiento::truncate();
        Cuidado::truncate();
        Valor::truncate();
        Control::truncate();
        Ocupacion::truncate();
        Internacion::truncate();

        // ✅ LIMPIAR PACIENTES Y SUS USUARIOS
        $pacienteRolTemp = Rol::where('nombre', 'PACIENTE')->first();
        if ($pacienteRolTemp) {
            $usuariosPacientes = User::where('rol_id', $pacienteRolTemp->id)->pluck('id');
            Paciente::whereIn('user_id', $usuariosPacientes)->delete();
            User::whereIn('id', $usuariosPacientes)->delete();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ✅ RESETEAR DISPONIBILIDAD DE TODAS LAS CAMAS
        Cama::query()->update(['disponibilidad' => true]);

        $this->hospital = Hospital::first();
        $this->pacienteRol = Rol::where('nombre', 'PACIENTE')->first();
        $this->signos = Signo::all()->keyBy('nombre');
        $this->medicamentos = Medicamento::all();
        $this->tiposDieta = TipoDieta::all();

        // Crear médicos y enfermeras adicionales
        $this->crearPersonalMedico();

        // Crear especialidades, salas y camas
        $especialidades = $this->crearEspecialidades();

        // Crear y registrar pacientes
        $this->crearPacientesCompletos($especialidades);

        $this->command->info('¡Seeder completo ejecutado con éxito!');
    }

    private function crearPersonalMedico()
    {
        $medicoRol = Rol::where('nombre', 'MÉDICO')->first();
        $enfermeraRol = Rol::where('nombre', 'ENFERMERA')->first();

        // Crear 5 médicos adicionales
        $nombresMedicos = [
            ['nombre' => 'Carlos', 'apellidos' => 'Rodríguez García', 'email' => 'carlos.rodriguez@hospital.com'],
            ['nombre' => 'Ana', 'apellidos' => 'Martínez López', 'email' => 'ana.martinez@hospital.com'],
            ['nombre' => 'Roberto', 'apellidos' => 'Fernández Silva', 'email' => 'roberto.fernandez@hospital.com'],
            ['nombre' => 'Laura', 'apellidos' => 'González Morales', 'email' => 'laura.gonzalez@hospital.com'],
            ['nombre' => 'Miguel', 'apellidos' => 'Sánchez Pérez', 'email' => 'miguel.sanchez@hospital.com'],
        ];

        foreach ($nombresMedicos as $index => $data) {
            $this->medicos[] = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'nombre' => $data['nombre'],
                    'apellidos' => $data['apellidos'],
                    'telefono' => 60001000 + $index,
                    'password' => Hash::make('12345678'),
                    'rol_id' => $medicoRol->id,
                    'hospital_id' => $this->hospital->id
                ]
            );
        }

        // Crear 5 enfermeras adicionales
        $nombresEnfermeras = [
            ['nombre' => 'María', 'apellidos' => 'López Vargas', 'email' => 'maria.lopez@hospital.com'],
            ['nombre' => 'Carmen', 'apellidos' => 'Díaz Rojas', 'email' => 'carmen.diaz@hospital.com'],
            ['nombre' => 'Patricia', 'apellidos' => 'Ruiz Castro', 'email' => 'patricia.ruiz@hospital.com'],
            ['nombre' => 'Isabel', 'apellidos' => 'Torres Mendoza', 'email' => 'isabel.torres@hospital.com'],
            ['nombre' => 'Rosa', 'apellidos' => 'Ramírez Flores', 'email' => 'rosa.ramirez@hospital.com'],
        ];

        foreach ($nombresEnfermeras as $index => $data) {
            $this->enfermeras[] = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'nombre' => $data['nombre'],
                    'apellidos' => $data['apellidos'],
                    'telefono' => 70002000 + $index,
                    'password' => Hash::make('12345678'),
                    'rol_id' => $enfermeraRol->id,
                    'hospital_id' => $this->hospital->id
                ]
            );
        }
    }

    private function crearEspecialidades()
    {
        $especialidadesData = [
            ['nombre' => 'Medicina Interna', 'descripcion' => 'Diagnóstico y tratamiento de enfermedades de adultos'],
            ['nombre' => 'Cirugía General', 'descripcion' => 'Procedimientos quirúrgicos generales'],
            ['nombre' => 'Pediatría', 'descripcion' => 'Atención médica de niños y adolescentes'],
            ['nombre' => 'Ginecología y Obstetricia', 'descripcion' => 'Salud reproductiva y embarazo'],
            ['nombre' => 'Traumatología', 'descripcion' => 'Tratamiento de lesiones del sistema musculoesquelético'],
            ['nombre' => 'Neurología', 'descripcion' => 'Enfermedades del sistema nervioso'],
            ['nombre' => 'Neumología', 'descripcion' => 'Enfermedades del sistema respiratorio'],
            ['nombre' => 'Nefrología', 'descripcion' => 'Enfermedades renales y del sistema urinario'],
        ];

        $especialidades = [];
        foreach ($especialidadesData as $espData) {
            $especialidad = Especialidad::firstOrCreate(
                ['nombre' => $espData['nombre'], 'hospital_id' => $this->hospital->id],
                ['descripcion' => $espData['descripcion'], 'estado' => true]
            );

            // Crear 2-3 salas por especialidad
            $numSalas = rand(2, 3);
            for ($i = 1; $i <= $numSalas; $i++) {
                $sala = Sala::firstOrCreate(
                    ['nombre' => "Sala {$espData['nombre']} {$i}", 'especialidad_id' => $especialidad->id],
                    ['tipo' => 'Sala de Internación', 'estado' => true]
                );

                // Crear 4-6 camas por sala
                $numCamas = rand(4, 6);
                for ($j = 1; $j <= $numCamas; $j++) {
                    Cama::firstOrCreate(
                        ['nombre' => "Cama {$i}{$j}", 'sala_id' => $sala->id],
                        ['tipo' => 'Estándar', 'estado' => true, 'disponibilidad' => true]
                    );
                }
            }

            $especialidades[] = $especialidad;
        }

        return $especialidades;
    }

    private function crearPacientesCompletos($especialidades)
    {
        $pacientesData = [
            ['ci' => '7654321', 'nombre' => 'María', 'apellidos' => 'Gonzales Pérez', 'fecha_nacimiento' => '1990-03-15', 'genero' => 'femenino', 'telefono' => '71234567', 'diagnostico' => 'Neumonía bacteriana', 'especialidad' => 'Neumología'],
            ['ci' => '8765432', 'nombre' => 'Pedro', 'apellidos' => 'Mamani Quispe', 'fecha_nacimiento' => '1978-07-22', 'genero' => 'masculino', 'telefono' => '72345678', 'diagnostico' => 'Fractura de fémur', 'especialidad' => 'Traumatología'],
            ['ci' => '9876543', 'nombre' => 'Ana', 'apellidos' => 'Flores Rojas', 'fecha_nacimiento' => '1985-11-08', 'genero' => 'femenino', 'telefono' => '73456789', 'diagnostico' => 'Diabetes descompensada', 'especialidad' => 'Medicina Interna'],
            ['ci' => '6543210', 'nombre' => 'Luis', 'apellidos' => 'Vargas Morales', 'fecha_nacimiento' => '1995-01-30', 'genero' => 'masculino', 'telefono' => '74567890', 'diagnostico' => 'Apendicitis aguda', 'especialidad' => 'Cirugía General'],
            ['ci' => '5432109', 'nombre' => 'Carmen', 'apellidos' => 'López Castro', 'fecha_nacimiento' => '1982-09-12', 'genero' => 'femenino', 'telefono' => '75678901', 'diagnostico' => 'Insuficiencia renal crónica', 'especialidad' => 'Nefrología'],
            ['ci' => '4321098', 'nombre' => 'Jorge', 'apellidos' => 'Sánchez Díaz', 'fecha_nacimiento' => '1970-05-25', 'genero' => 'masculino', 'telefono' => '76789012', 'diagnostico' => 'Accidente cerebrovascular', 'especialidad' => 'Neurología'],
            ['ci' => '3210987', 'nombre' => 'Rosa', 'apellidos' => 'Mendoza Torres', 'fecha_nacimiento' => '1988-12-03', 'genero' => 'femenino', 'telefono' => '77890123', 'diagnostico' => 'Embarazo de alto riesgo', 'especialidad' => 'Ginecología y Obstetricia'],
            ['ci' => '2109876', 'nombre' => 'Ricardo', 'apellidos' => 'Fernández Silva', 'fecha_nacimiento' => '1992-06-18', 'genero' => 'masculino', 'telefono' => '78901234', 'diagnostico' => 'Bronquitis aguda', 'especialidad' => 'Neumología'],
            ['ci' => '1098765', 'nombre' => 'Patricia', 'apellidos' => 'Ramírez Gutiérrez', 'fecha_nacimiento' => '1975-02-28', 'genero' => 'femenino', 'telefono' => '79012345', 'diagnostico' => 'Hipertensión arterial severa', 'especialidad' => 'Medicina Interna'],
            ['ci' => '9087654', 'nombre' => 'Miguel', 'apellidos' => 'Cruz Herrera', 'fecha_nacimiento' => '2010-08-14', 'genero' => 'masculino', 'telefono' => '71111111', 'diagnostico' => 'Gastroenteritis aguda', 'especialidad' => 'Pediatría'],
            ['ci' => '8976543', 'nombre' => 'Elena', 'apellidos' => 'Ortiz Vega', 'fecha_nacimiento' => '1980-04-07', 'genero' => 'femenino', 'telefono' => '72222222', 'diagnostico' => 'Colecistitis aguda', 'especialidad' => 'Cirugía General'],
            ['ci' => '7865432', 'nombre' => 'Fernando', 'apellidos' => 'Molina Paredes', 'fecha_nacimiento' => '1968-10-19', 'genero' => 'masculino', 'telefono' => '73333333', 'diagnostico' => 'Insuficiencia cardíaca', 'especialidad' => 'Cardiología'],
            ['ci' => '6754321', 'nombre' => 'Silvia', 'apellidos' => 'Ríos Campos', 'fecha_nacimiento' => '1993-07-11', 'genero' => 'femenino', 'telefono' => '74444444', 'diagnostico' => 'Pielonefritis aguda', 'especialidad' => 'Nefrología'],
            ['ci' => '5643210', 'nombre' => 'Alberto', 'apellidos' => 'Navarro Soto', 'fecha_nacimiento' => '1987-01-23', 'genero' => 'masculino', 'telefono' => '75555555', 'diagnostico' => 'Traumatismo craneoencefálico', 'especialidad' => 'Neurología'],
            ['ci' => '4532109', 'nombre' => 'Gabriela', 'apellidos' => 'Chávez Luna', 'fecha_nacimiento' => '1991-09-05', 'genero' => 'femenino', 'telefono' => '76666666', 'diagnostico' => 'Asma bronquial severa', 'especialidad' => 'Neumología'],
            ['ci' => '3421098', 'nombre' => 'Daniel', 'apellidos' => 'Medina Ramos', 'fecha_nacimiento' => '2008-03-27', 'genero' => 'masculino', 'telefono' => '77777777', 'diagnostico' => 'Bronquiolitis', 'especialidad' => 'Pediatría'],
            ['ci' => '2310987', 'nombre' => 'Verónica', 'apellidos' => 'Salazar Ibáñez', 'fecha_nacimiento' => '1984-11-16', 'genero' => 'femenino', 'telefono' => '78888888', 'diagnostico' => 'Hernia inguinal', 'especialidad' => 'Cirugía General'],
            ['ci' => '1209876', 'nombre' => 'Andrés', 'apellidos' => 'Cortez Aguilar', 'fecha_nacimiento' => '1972-05-09', 'genero' => 'masculino', 'telefono' => '79999999', 'diagnostico' => 'Úlcera péptica perforada', 'especialidad' => 'Cirugía General'],
        ];

        foreach ($pacientesData as $index => $pData) {
            // Buscar la especialidad correspondiente
            $especialidad = collect($especialidades)->firstWhere('nombre', $pData['especialidad']);
            if (!$especialidad) {
                $especialidad = $especialidades[0]; // Fallback a la primera especialidad
            }

            // Crear paciente SIN usuario
            $paciente = Paciente::firstOrCreate(
                ['ci' => $pData['ci']],
                [
                    'nombre' => $pData['nombre'],
                    'apellidos' => $pData['apellidos'],
                    'fecha_nacimiento' => $pData['fecha_nacimiento'],
                    'genero' => $pData['genero'],
                    'telefono' => $pData['telefono'],
                    'direccion' => 'Zona ' . chr(65 + ($index % 26)) . ' Calle ' . rand(1, 100),
                    'nombre_referencia' => 'Familiar',
                    'apellidos_referencia' => $pData['apellidos'],
                    'celular_referencia' => '7' . rand(0000000, 9999999),
                    // 'user_id' => null,  // ✅ SIN USUARIO
                    'estado' => true
                ]
            );

            // Buscar una cama disponible en la especialidad
            $cama = Cama::whereHas('sala', function($q) use ($especialidad) {
                $q->where('especialidad_id', $especialidad->id);
            })->where('disponibilidad', true)->first();

            if (!$cama) {
                $this->command->warn("No hay camas disponibles para {$pData['nombre']}");
                continue;
            }

            // Crear internación
            $medico = $this->medicos[array_rand($this->medicos)];
            $fechaIngreso = Carbon::now()->subDays(rand(1, 7))->subHours(rand(0, 23));

            $internacion = Internacion::create([
                'fecha_ingreso' => $fechaIngreso,
                'motivo' => 'Ingreso por ' . strtolower($pData['diagnostico']),
                'diagnostico' => $pData['diagnostico'],
                'paciente_id' => $paciente->id,
                'user_id' => $medico->id,
            ]);

            // Ocupar la cama
            Ocupacion::create([
                'fecha_ocupacion' => $fechaIngreso,
                'cama_id' => $cama->id,
                'internacion_id' => $internacion->id,
            ]);
            $cama->update(['disponibilidad' => false]);

            // Registrar signos vitales (4-5 controles)
            $this->registrarSignosVitales($internacion, $fechaIngreso);

            // Crear tratamiento con medicamentos
            $tratamiento = $this->crearTratamiento($internacion, $medico, $fechaIngreso);

            // Crear plan de alimentación
            $this->crearPlanAlimentacion($internacion, $fechaIngreso, $tratamiento);

            // Crear plan de cuidados
            $this->crearPlanCuidados($internacion, $fechaIngreso);
        }
    }

    private function registrarSignosVitales($internacion, $fechaIngreso)
    {
        $numControles = rand(4, 5);

        for ($i = 0; $i < $numControles; $i++) {
            $enfermera = $this->enfermeras[array_rand($this->enfermeras)];
            $fechaControl = $fechaIngreso->copy()->addHours($i * 6);

            $control = Control::create([
                'tipo' => $i === 0 ? 'Control de Ingreso' : 'Control de Rutina',
                'internacion_id' => $internacion->id,
                'user_id' => $enfermera->id,
                'fecha_control' => $fechaControl,
                'observaciones' => $i === 0 ? 'Registro inicial de signos vitales' : 'Control de seguimiento',
            ]);

            // Valores realistas de signos vitales
            $valores = [
                'Altura' => 160 + rand(0, 30),
                'Peso' => 50 + rand(0, 50),
                'Frecuencia Cardíaca' => 60 + rand(0, 40),
                'Frecuencia Respiratoria' => 12 + rand(0, 12),
                'Glucosa Capilar' => 80 + rand(0, 60),
                'Presión Arterial' => 100 + rand(0, 40),
                'Saturación de Oxígeno' => 92 + rand(0, 8),
                'Temperatura' => 36.0 + (rand(0, 30) / 10)
            ];

            foreach ($valores as $nombre => $medida) {
                if (isset($this->signos[$nombre])) {
                    $control->valores()->create([
                        'signo_id' => $this->signos[$nombre]->id,
                        'medida' => $medida,
                    ]);
                }
            }
        }
    }

    private function crearTratamiento($internacion, $medico, $fechaIngreso)
    {
        $tratamiento = Tratamiento::create([
            'internacion_id' => $internacion->id,
            'user_id' => $medico->id,
            'tipo' => 'Tratamiento farmacológico',
            'descripcion' => 'Manejo médico integral del paciente',
            'fecha_inicio' => $fechaIngreso,
            'fecha_fin' => $fechaIngreso->copy()->addDays(rand(3, 7)),
            'estado' => 0,
        ]);

        // Agregar 2-3 medicamentos al tratamiento
        $numMedicamentos = rand(2, 3);
        $medicamentosSeleccionados = $this->medicamentos->random($numMedicamentos);

        foreach ($medicamentosSeleccionados as $medicamento) {
            $frecuenciaHoras = [4, 6, 8, 12, 24][array_rand([4, 6, 8, 12, 24])];
            $vias = ['Oral', 'Intravenosa', 'Intramuscular', 'Subcutánea'];

            $receta = $tratamiento->recetas()->create([
                'medicamento_id' => $medicamento->id,
                'dosis' => rand(5, 50) . ['mg', 'ml', 'g'][array_rand(['mg', 'ml', 'g'])],
                'frecuencia_horas' => $frecuenciaHoras,
                'duracion_dias' => rand(3, 7),
                'via_administracion' => $vias[array_rand($vias)],
                'indicaciones' => 'Administrar según indicación médica',
            ]);

            // Registrar administraciones de medicamentos
            $this->registrarAdministraciones($receta, $fechaIngreso, $frecuenciaHoras);
        }

        return $tratamiento;
    }

    private function registrarAdministraciones($receta, $fechaIngreso, $frecuenciaHoras)
    {
        $numAdministraciones = rand(3, 6);

        for ($i = 0; $i < $numAdministraciones; $i++) {
            $enfermera = $this->enfermeras[array_rand($this->enfermeras)];
            $horaProgramada = $fechaIngreso->copy()->addHours($i * $frecuenciaHoras);

            // Algunas administraciones pueden tener un pequeño retraso
            $estado = rand(0, 10) > 2 ? 1 : 2; // 80% cumplida, 20% con retraso

            Administra::create([
                'receta_id' => $receta->id,
                'hora_programada' => $horaProgramada,
                'user_id' => $enfermera->id,
                'fecha' => $estado === 2 ? $horaProgramada->copy()->addMinutes(rand(10, 60)) : $horaProgramada,
                'estado' => $estado,
                'observaciones' => $estado === 1 ? 'Administrado correctamente' : 'Administrado con retraso',
            ]);
        }
    }

    private function crearPlanAlimentacion($internacion, $fechaIngreso, $tratamiento)
    {
        if ($this->tiposDieta->isEmpty()) {
            return;
        }

        $tipoDieta = $this->tiposDieta->random();

        $alimentacion = Alimentacion::create([
            'internacion_id' => $internacion->id,
            'tipo_dieta_id' => $tipoDieta->id,
            'via_administracion' => rand(0, 10) > 8 ? 'Enteral' : 'Oral',
            'frecuencia_tiempos' => 3,
            'restricciones' => 'Sin sal, bajo en grasas',
            'descripcion' => 'Plan de alimentación hospitalaria',
            'fecha_inicio' => $fechaIngreso,
            'fecha_fin' => $fechaIngreso->copy()->addDays(rand(3, 7)),
            'estado' => 0,
        ]);

        // ✅ CREAR TIEMPOS DE COMIDA (esto faltaba!)
        $tiemposComida = [
            ['tiempo_comida' => 'Desayuno', 'descripcion' => 'Desayuno hospitalario según dieta prescrita', 'orden' => 1],
            ['tiempo_comida' => 'Almuerzo', 'descripcion' => 'Almuerzo hospitalario según dieta prescrita', 'orden' => 2],
            ['tiempo_comida' => 'Cena', 'descripcion' => 'Cena hospitalaria según dieta prescrita', 'orden' => 3],
        ];

        foreach ($tiemposComida as $tiempo) {
            $alimentacion->tiempos()->create($tiempo);
        }

        // Registrar consumos de alimentos
        $this->registrarConsumos($alimentacion, $fechaIngreso, $tratamiento);
    }

    private function registrarConsumos($alimentacion, $fechaIngreso, $tratamiento)
    {
        $tiemposComida = ['Desayuno', 'Almuerzo', 'Cena'];
        $horasComida = [8, 13, 19];
        $numDias = rand(2, 4);

        // ✅ REGISTRAR CONSUMOS DE DÍAS PASADOS
        for ($dia = 0; $dia < $numDias; $dia++) {
            foreach ($tiemposComida as $index => $tiempo) {
                $enfermera = $this->enfermeras[array_rand($this->enfermeras)];
                $fechaConsumo = $fechaIngreso->copy()->addDays($dia)->setHour($horasComida[$index])->setMinute(0);

                Consume::create([
                    'tratamiento_id' => $tratamiento->id,
                    'alimentacion_id' => $alimentacion->id,
                    'tiempo_comida' => $tiempo,
                    'fecha' => $fechaConsumo,
                    'porcentaje_consumido' => rand(50, 100),
                    'observaciones' => 'Paciente tolera bien la dieta',
                    'registrado_por' => $enfermera->id,
                ]);
            }
        }

        // ✅ REGISTRAR CONSUMOS DE HOY (para que aparezcan en el frontend)
        $hoy = Carbon::today();
        foreach ($tiemposComida as $index => $tiempo) {
            // Solo registrar si la hora ya pasó
            $horaActual = Carbon::now()->hour;
            if ($horaActual >= $horasComida[$index]) {
                $enfermera = $this->enfermeras[array_rand($this->enfermeras)];
                $fechaConsumo = $hoy->copy()->setHour($horasComida[$index])->setMinute(rand(0, 59));

                Consume::create([
                    'tratamiento_id' => $tratamiento->id,
                    'alimentacion_id' => $alimentacion->id,
                    'tiempo_comida' => $tiempo,
                    'fecha' => $fechaConsumo,
                    'porcentaje_consumido' => rand(25, 100),
                    'observaciones' => rand(0, 10) > 7 ? 'Paciente refiere náuseas' : 'Consumo normal',
                    'registrado_por' => $enfermera->id,
                ]);
            }
        }
    }

    private function crearPlanCuidados($internacion, $fechaIngreso)
    {
        $cuidadosGenerales = [
            ['tipo' => 'Cuidado General', 'descripcion' => 'Control de signos vitales', 'frecuencia' => 'Cada 6 horas'],
            ['tipo' => 'Cuidado General', 'descripcion' => 'Movilización y cambios de posición', 'frecuencia' => 'Cada 4 horas'],
            ['tipo' => 'Cuidado Específico', 'descripcion' => 'Curación de heridas', 'frecuencia' => 'Cada 12 horas'],
            ['tipo' => 'Cuidado General', 'descripcion' => 'Higiene y confort del paciente', 'frecuencia' => 'Cada 8 horas'],
        ];

        foreach ($cuidadosGenerales as $cuidadoData) {
            $cuidado = Cuidado::create([
                'internacion_id' => $internacion->id,
                'tipo' => $cuidadoData['tipo'],
                'descripcion' => $cuidadoData['descripcion'],
                'frecuencia' => $cuidadoData['frecuencia'],
                'estado' => 0,
                'fecha_inicio' => $fechaIngreso,
                'fecha_fin' => $fechaIngreso->copy()->addDays(rand(3, 7)),
            ]);

            // Registrar aplicaciones de cuidados
            $this->registrarAplicacionesCuidados($cuidado, $fechaIngreso);
        }
    }

    private function registrarAplicacionesCuidados($cuidado, $fechaIngreso)
    {
        $numAplicaciones = rand(3, 6);

        for ($i = 0; $i < $numAplicaciones; $i++) {
            $enfermera = $this->enfermeras[array_rand($this->enfermeras)];
            $fechaAplicacion = $fechaIngreso->copy()->addHours($i * 6);

            CuidadoAplicado::create([
                'user_id' => $enfermera->id,
                'cuidado_id' => $cuidado->id,
                'fecha_aplicacion' => $fechaAplicacion,
                'observaciones' => 'Cuidado aplicado correctamente',
            ]);
        }
    }
}
