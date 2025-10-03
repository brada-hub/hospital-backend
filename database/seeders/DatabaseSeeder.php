<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;
use App\Models\User;
use App\Models\Hospital;
use App\Models\Permission;
use App\Models\Especialidad;
use App\Models\Sala;
use App\Models\Cama;
use App\Models\Paciente;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ... (La sección de limpieza de tablas está bien)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('user_permissions')->truncate();
        DB::table('role_permissions')->truncate();
        DB::table('permissions')->truncate();
        DB::table('users')->truncate();
        DB::table('rols')->truncate();
        DB::table('camas')->truncate();
        DB::table('salas')->truncate();
        DB::table('especialidads')->truncate();
        DB::table('pacientes')->truncate();
        DB::table('hospitals')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // --- Creación de Roles ---
        $adminRol = Rol::firstOrCreate(['nombre' => 'ADMINISTRADOR'], ['descripcion' => 'Usuario con todos los permisos', 'estado' => true]);
        $medicoRol = Rol::firstOrCreate(['nombre' => 'MÉDICO'], ['descripcion' => 'Acceso a gestión de pacientes y tratamientos', 'estado' => true]);
        $enfermeraRol = Rol::firstOrCreate(['nombre' => 'ENFERMERA'], ['descripcion' => 'Acceso a gestión de cuidados y administración', 'estado' => true]);

        // --- Creación de Hospital ---
        $hospital = Hospital::firstOrCreate(
            ['nombre' => 'Hospital Central'],
            ['departamento' => 'Cochabamba', 'direccion' => 'Calle Ayacucho 123', 'nivel' => 'Nivel 3', 'tipo' => 'Público', 'telefono' => 70012345]
        );

        // --- Creación de Infraestructura de Ejemplo ---
        $especialidadCardiologia = Especialidad::firstOrCreate(
            ['nombre' => 'Cardiología', 'hospital_id' => $hospital->id],
            ['descripcion' => 'Especialidad dedicada al estudio y tratamiento de las enfermedades del corazón.', 'estado' => true]
        );
        $salaA = Sala::firstOrCreate(
            ['nombre' => 'Sala A', 'especialidad_id' => $especialidadCardiologia->id],
            ['tipo' => 'Sala de Internación General', 'estado' => true]
        );

        // --- Creación de Camas (con la corrección) ---
        foreach (['Cama 101', 'Cama 102', 'Cama 103'] as $nombreCama) {
            Cama::firstOrCreate(
                ['nombre' => $nombreCama, 'sala_id' => $salaA->id],
                [
                    'tipo' => 'Estándar',
                    'estado' => true,
                    // ✅ CORREGIDO: Usamos 'true' en lugar del texto 'disponible'
                    'disponibilidad' => true
                ]
            );
        }

        // --- Creación de Pacientes de Ejemplo ---
        Paciente::firstOrCreate(
            ['ci' => '1234567'],
            ['nombre' => 'Juan', 'apellidos' => 'Perez Soliz', 'fecha_nacimiento' => '1985-05-20', 'genero' => 'masculino', 'telefono' => '70711111', 'direccion' => 'Av. Heroínas #456', 'estado' => true]
        );
        Paciente::firstOrCreate(
            ['ci' => '7654321'],
            ['nombre' => 'Ana', 'apellidos' => 'Gomez Lopez', 'fecha_nacimiento' => '1992-11-15', 'genero' => 'femenino', 'telefono' => '60322222', 'direccion' => 'Calle Nataniel Aguirre #123', 'estado' => true]
        );
        Paciente::firstOrCreate(
            ['ci' => '9876543'],
            ['nombre' => 'Carlos', 'apellidos' => 'Vaca Guzman', 'fecha_nacimiento' => '1978-01-30', 'genero' => 'masculino', 'telefono' => '79733333', 'direccion' => 'Plaza Colón, Acera Norte', 'estado' => true]
        );

        // --- Definición y Asignación de Permisos ---
        $permissions = [
            ['nombre' => 'acceso.dashboard', 'descripcion' => 'Acceso a la página principal del dashboard'],
            ['nombre' => 'acceso.gestion-hospital', 'descripcion' => 'Acceso a la página de gestión del hospital'],
            ['nombre' => 'acceso.pacientes', 'descripcion' => 'Acceso a la página de gestión de pacientes'],
            ['nombre' => 'acceso.admision', 'descripcion' => 'Acceso al módulo de admisión e internación'],
            ['nombre' => 'acceso.medicamentos', 'descripcion' => 'Acceso a la gestión de medicamentos y categorías'],
            ['nombre' => 'acceso.usuarios-y-roles', 'descripcion' => 'Acceso a la página de gestión de usuarios y roles'],
            ['nombre' => 'acceso.panel-internacion', 'descripcion' => 'Acceso al panel de control de un paciente internado'],

            ['nombre' => 'acceso.mis-pacientes', 'descripcion' => 'Acceso a la vista de pacientes asignados al médico'],
            ['nombre' => 'acceso.estacion-enfermeria', 'descripcion' => 'Acceso a la Estación de Enfermería'],
        ];
        $permissions = collect($permissions)->map(fn($p) => Permission::firstOrCreate($p));
        $adminRol->permissions()->sync($permissions->pluck('id'));
        $medicoRol->permissions()->sync($permissions->whereIn('nombre', ['acceso.dashboard', 'acceso.pacientes', 'acceso.admision', 'acceso.panel-internacion', 'acceso.mis-pacientes'])->pluck('id'));
        $enfermeraRol->permissions()->sync($permissions->whereIn('nombre', ['acceso.dashboard', 'acceso.pacientes', 'acceso.panel-internacion', 'acceso.estacion-enfermeria'])->pluck('id'));

        // --- Creación de Usuarios ---
        User::firstOrCreate(
            ['email' => 'admin@hospital.com'],
            ['nombre' => 'Admin', 'apellidos' => 'Principal', 'telefono' => 77777777, 'password' => Hash::make('12345678'), 'rol_id' => $adminRol->id, 'hospital_id' => $hospital->id]
        );
        User::firstOrCreate(
            ['email' => 'medico@hospital.com'],
            ['nombre' => 'Doctor', 'apellidos' => 'Smith', 'telefono' => 60001111, 'password' => Hash::make('12345678'), 'rol_id' => $medicoRol->id, 'hospital_id' => $hospital->id]
        );
        User::firstOrCreate(
            ['email' => 'enfermera@hospital.com'],
            ['nombre' => 'Enfermera', 'apellidos' => 'Jane', 'telefono' => 70002222, 'password' => Hash::make('12345678'), 'rol_id' => $enfermeraRol->id, 'hospital_id' => $hospital->id]
        );

        // --- Llamada a otros Seeders ---
        $this->call([
            MedicamentoSeeder::class,
            SignoSeeder::class,
        ]);
    }
}
