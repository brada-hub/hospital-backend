<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;
use App\Models\User;
use App\Models\Hospital;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Desactivar temporalmente las restricciones de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Limpiar las tablas para evitar duplicados en cada ejecución
        DB::table('user_permissions')->truncate();
        DB::table('role_permissions')->truncate();
        DB::table('permissions')->truncate();
        DB::table('users')->truncate();
        DB::table('rols')->truncate();
        DB::table('hospitals')->truncate();

        // Reactivar las restricciones
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');


        // Crear roles
        $adminRol = Rol::firstOrCreate(
            ['nombre' => 'Administrador'],
            ['descripcion' => 'Usuario con todos los permisos']
        );
        $medicoRol = Rol::firstOrCreate(
            ['nombre' => 'Médico'],
            ['descripcion' => 'Acceso a gestión de pacientes y tratamientos']
        );
        $enfermeraRol = Rol::firstOrCreate(
            ['nombre' => 'Enfermera'],
            ['descripcion' => 'Acceso a gestión de cuidados y administración']
        );

        // Crear un hospital si no existe
        $hospital = Hospital::firstOrCreate(
            ['nombre' => 'Hospital Central'],
            [
                'departamento' => 'Cochabamba',
                'direccion'    => 'Calle Ayacucho 123',
                'nivel'        => 'Nivel 3',
                'tipo'         => 'Público',
                'telefono'     => 70012345
            ]
        );

        // 📌 MODIFICACIÓN: Definir permisos del sistema con nombres claros
        $permissions = [
            ['nombre' => 'acceso.dashboard', 'descripcion' => 'Acceso a la página principal del dashboard'],
            ['nombre' => 'acceso.gestion-hospital', 'descripcion' => 'Acceso a la página de gestión del hospital'],
            ['nombre' => 'acceso.pacientes', 'descripcion' => 'Acceso a la página de gestión de pacientes'],
            ['nombre' => 'acceso.admision', 'descripcion' => 'Acceso al módulo de admisión e internación'],
            ['nombre' => 'acceso.medicamentos', 'descripcion' => 'Acceso a la gestión de medicamentos y categorías'], // <-- AÑADE ESTA LÍNEA
            ['nombre' => 'acceso.usuarios-y-roles', 'descripcion' => 'Acceso a la página de gestión de usuarios y roles'],
            ['nombre' => 'acceso.hospital', 'descripcion' => 'Acceso a la página de hospital'],
        ];

        // Guardar permisos en la base de datos
        $permissions = collect($permissions)->map(function ($permission) {
            return Permission::firstOrCreate($permission);
        });

        // Asignar permisos a roles según las nuevas reglas
        // El rol de Administrador tiene todos los permisos
        $adminRol->permissions()->sync($permissions->pluck('id'));

        // 📌 MODIFICACIÓN: El rol de Médico tiene permisos específicos
        $medicoRol->permissions()->sync([
            $permissions->firstWhere('nombre', 'acceso.dashboard')->id,
            $permissions->firstWhere('nombre', 'acceso.gestion-hospital')->id,
        ]);

        // 📌 MODIFICACIÓN: El rol de Enfermera tiene permisos específicos
        $enfermeraRol->permissions()->sync([
            $permissions->firstWhere('nombre', 'acceso.usuarios-y-roles')->id,
        ]);

        // Crear usuarios iniciales y asociarlos a los roles y hospital
        User::firstOrCreate(
            ['email' => 'admin@hospital.com'],
            [
                'nombre'    => 'Admin',
                'apellidos' => 'Principal',
                'telefono'  => 77777777,
                'password'  => Hash::make('12345678'),
                'rol_id'    => $adminRol->id,
                'hospital_id' => $hospital->id,
            ]
        );
        User::firstOrCreate(
            ['email' => 'medico@hospital.com'],
            [
                'nombre'    => 'Doctor',
                'apellidos' => 'Smith',
                'telefono'  => 60001111,
                'password'  => Hash::make('12345678'),
                'rol_id'    => $medicoRol->id,
                'hospital_id' => $hospital->id,
            ]
        );
        User::firstOrCreate(
            ['email' => 'enfermera@hospital.com'],
            [
                'nombre'    => 'Enfermera',
                'apellidos' => 'Jane',
                'telefono'  => 70002222,
                'password'  => Hash::make('12345678'),
                'rol_id'    => $enfermeraRol->id,
                'hospital_id' => $hospital->id,
            ]
        );
        $this->call([
            MedicamentoSeeder::class,
            // Aquí puedes añadir otros seeders si los tienes
        ]);
    }
}
