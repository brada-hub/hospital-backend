<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;
use App\Models\User;
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

        // Crear usuario admin si no existe
        User::firstOrCreate(
            ['email' => 'admin@hospital.com'],
            [
                'nombre'    => 'Admin',
                'apellidos' => 'Principal',
                'telefono'  => 77777777,
                'password'  => Hash::make('12345678'), // <-- contraseña segura
                'rol_id'    => $adminRol->id,
            ]
        );
    }
}
