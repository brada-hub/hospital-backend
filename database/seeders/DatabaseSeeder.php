<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;
use App\Models\User;
use App\Models\Hospital; // Importar el modelo de Hospital
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear rol admin si no existe
        $adminRol = Rol::firstOrCreate(
            ['nombre' => 'Administrador'],
            ['descripcion' => 'Usuario con todos los permisos']
        );

        // Crear un hospital si no existe (puedes modificar estos datos)
        $hospital = Hospital::firstOrCreate(
            ['nombre' => 'Hospital Principal'],
            [
                'departamento' => 'Departamento Central',
                'direccion'    => 'Calle Principal 123',
                'nivel'        => 'Nivel 1',
                'tipo'         => 'General',
                'telefono'     => 123456789
            ]
        );

        // Crear usuario admin y asociarlo al hospital
        User::firstOrCreate(
            ['email' => 'admin@hospital.com'],
            [
                'nombre'    => 'Admin',
                'apellidos' => 'Principal',
                'telefono'  => 77777777,
                'password'  => Hash::make('12345678'), // <-- contraseña segura
                'rol_id'    => $adminRol->id,
                'hospital_id' => $hospital->id, // Asociar el hospital al usuario
            ]
        );
    }
}
